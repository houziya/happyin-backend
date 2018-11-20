<?php
use Yaf\Controller_Abstract;
use yii\db\Query;
use yii\web\Cookie;
use yii\web\Request;

class UserController extends Controller_Abstract
{
    /* 从随机分享库中抽取 优惠劵数目 */
    const RAND_COUNT = HI\Config\Coupon\RAND_COUNT;
    /* 领取次数限制 */
    const RECEIVE_COUNT_LIMIT = HI\Config\Coupon\RECEIVE_COUNT_LIMIT;
    const SEND_COUPON_ID = HI\Config\Coupon\SEND_COUPON_ID;
    /* 回调域名 */
    const REDIRECT_URI = HI\Config\Coupon\REDIRECT_URI;
    
    public function registerAction()
    {
        $data = Protocol::arguments();
        Execution::autoTransaction(Yii::$app->db, function() use($data) {
            //校验是否已注册，已有账号直接登录
            if ($uid = $this->doVeriftyRegister($data->required('device_id'), HI\User\REGISTER_TYPE_DEVICE)) {
                $userInfo = UserModel::getUserData($uid);
                $response = [
                    'uid' => $uid,
                    'avatar' => "profile/avatar/".$userInfo['avatar'].'.jpg',
                    'nickname' => $userInfo['lastname'],
                    'session_key' => Session::reset($uid, HI\Config\SESSION_EXPIRE),
                ];
                //清除mipush气泡计数
                Yii::$app->redis->set(HI\MI_PUSH\COUNT.$uid, 0, HI\User\MONTH);
                Protocol::ok($response);
                return ;
            }
            //防止并发
            if (!$this->doPreventConcurrent($data->required('device_id'))) {
                Protocol::badRequest(null, "正在注册请耐心等待");
                return ;
            }
            //注册
            if ($user = $this->doCreateUser($data)) {
                AdClick::register($data->required('device_id'));
                $response = [
                    'uid' => $user->customer_id,
                    'avatar' => HI\User\DEFAULT_AVATAR,
                    'nickname' => $user->firstname,
                    'session_key' => Session::reset($uid, HI\Config\SESSION_EXPIRE),
                ];
                Protocol::ok($response);
                return ;
            }
            Protocol::ok(NULL, NULL, 'Register Fail!');
        });
    }

    private function doPreventConcurrent($key)
    {
        $result = Yii::$app->redis->hset($key, 1, 1);
        Yii::$app->redis->expire($key, 5);
        return $result;
    }

    private function doVeriftyRegister($token, $type)
    {
        switch ($type) {
            case HI\User\REGISTER_TYPE_DEVICE:
                return UserModel::getAccountUid($token, $type);
                break;
            default:
                throw new InvalidArgumentException('Invalid registration type '. $type);
        }
        return false;
    }

    private function doCreateUser($user)
    {
        $model = $this->doPrepareModel($user);
        $userModel = $this->storeUserData($model);
        return $userModel;
    }

    private function doPrepareModel($user)
    {
        $model = new stdClass();
        $deviceInfo = json_decode(urldecode($user->required('device_info')), true);
        Accessor::wrap($deviceInfo)->copyRequired(['model' => 'phone_model', 'client_version', 'os_version', 'product' => 'distributor'], $model);
        $user->copyRequired([
            'platform', 'device_id' => 'token', 'distributor',
        ], $model);
        
        $model->os_version = Types::versionToLong($model->os_version);
        if (!Predicates::equals(strlen($model->client_version), 5)) {
            $model->os_version .= ".0";
        }
        $randName = HI\User\DEFAULT_NICKNAME.substr(strtoupper(uuid_create()), -5);
        $model->client_version = Types::versionToLong($model->client_version);
        $model->reg_ip = ip2long(Protocol::remoteAddress());
        $model->log_ip = ip2long(Protocol::remoteAddress());
        $model->firstname = $this->doVerifyNickname($randName);
        $model->avatar = HI\User\DEFAULT_AVATAR;
        $model->login_time = date("Y-m-d H:i:s");
        $model->date_added = date("Y-m-d H:i:s");
        $model->phone_model = $this->doGetPhoneModelCode($model->phone_model);
        $model->distributor = $this->doGetDistributorCode($model->distributor);
        return $model;
    }
    
    private function doVerifyNickname($model)
    {
        $flag = true;
        while($flag) {
           if (CouponModel::queryOneTableInfo('firstname', HI\TableName\CUSTOMER, ['firstname' => $model])) {
               $model = HI\User\DEFAULT_NICKNAME.substr(strtoupper(uuid_create()), 0, 5);
           } else {
               $flag = false;
           }
        }
        return $model;
    }

    private function doGetPhoneModelCode($phone_model)
    {
        $key = HI\User\PHONE_MODEL;
        $code = Yii::$app->redis->hget($key, $phone_model);
        if (!$code) {
            $connection = Yii::$app->db;
            $connection->createCommand()->insert(HI\TableName\SYSTEM_CODE, [
                'type' => 0,
                'name' => $phone_model,
            ])->execute();
            $code = $connection->getLastInsertID();
            Yii::$app->redis->hset($key, $phone_model, $code);
        }
        return $code;
    }
    
    private function doGetDistributorCode($distributor)
    {
        $key = Hi\User\DISTRIBUTOR;
        $code = Yii::$app->redis->hget($key, $distributor);
        if (!$code) {
            $connection = Yii::$app->db;
            $connection->createCommand()->insert(HI\TableName\SYSTEM_CODE, [
                'type' => 1,
                'name' => $distributor,
            ])->execute();
            $code = $connection->getLastInsertID();
            Yii::$app->redis->hset($key, $distributor, $code);
        }
        return $code;
    }

    private function storeUserData($user)
    {
        $user->customer_id = UserModel::addUserData($user);
        $user->session_key = Session::reset($user->customer_id, HI\Config\SESSION_EXPIRE);
        UserModel::addUserAccount($user->customer_id, $user->token, HI\User\REGISTER_TYPE_DEVICE);
        $this->doUserDeviceModel($user);
        return $user;
    }

    private function doUserDeviceModel($user){
        $this->doStoreRedisString(HI\User\US_DEVID.$user->customer_id, $user->token, 10);
        $connection = Yii::$app->db;
        $result = $connection->createCommand()->insert(HI\TableName\CUSTOMER_DEVICE, [
            'customer_id' => $user->customer_id,
            'reg_ip' => $user->reg_ip,
            'log_ip' => $user->log_ip,
            'reg_device_id' => $user->token,
            'log_device_id' => $user->token,
            'platform' => $user->platform,
            'login_time' => $user->login_time,
            'distributor' => $user->distributor,
            'client_version' => $user->client_version,
            'os_version' => $user->os_version,
        ])->execute();
    
        $connection = Yii::$app->db;
        $resultHistory = $connection->createCommand()->insert(HI\TableName\CUSTOMER_DEVICE_HISTORY, [
            'customer_id' => $user->customer_id,
            'log_ip' => $user->log_ip,
            'log_device_id' => $user->token,
            'platform' => $user->platform,
            'login_time' => $user->login_time,
            'distributor' => $user->distributor,
            'client_version' => $user->client_version,
            'os_version' => $user->os_version,
        ])->execute();
    
        if( $result && $resultHistory ){
            return true;
        }
        else{
            return false;
        }
    }

    private function doStoreRedisString($key, $value, $times)
    {
        for ($i=0; $i<$times; $i++) {
            $result = Yii::$app->redis->set($key, $value);
            if ($result) {
                return true;
            }
        }
        return false;
    }

    public function loginAction()
    {
        $data = Protocol::arguments();
        Execution::autoTransaction(Yii::$app->db, function() use($data) {
            if ($uid = UserModel::getAccountUid($data->token, $data->type)) {
                if (!$this->doVerifyCaptcha($data)) {
                    Protocol::ok(null, null, null, Notice::get()->invalidCaptcha());
                    return false;
                }
                $user = UserModel::getUserData($uid);
                if ($user['status'] == 0) {
                    Protocol::ok(NULL, "帐号已被冻结");
                    return ;
                }
                $result = $this->doUpdateUserDevice($uid, $data);
                $response = [
                    'uid' => $uid,
                    'avatar' => 'profile/avatar/'.$user['avatar'].".jpg",
                    'nickname' => $user['firstname'],
                    'session_key' => Session::reset($uid, HI\Config\SESSION_EXPIRE),
                ];
                Protocol::ok($response);
                return ;
            }
            Protocol::ok(null, null, null, Notice::get()->phoneNumberAlreadyExists());
            return false;
        });
    }

    private function doVerifyCaptcha($user)
    {
        if (HI\Captcha\VERIFY) {          //验证验证码开关
            $captcha = $user->required('captcha');
            $token = $user->token;
            $captchaKey = Us\User\CAPTCHA_PHONE.$token;
            $captchaCache = Yii::$app->redis->get($captchaKey);
            $attemptsKey = $token . '.attempts';
            if ($captcha != $captchaCache) {
                $attempts = Yii::$app->redis->get($attemptsKey);
                if ($attempts > Us\User\ATTEMPTS_TIMES) {
                    return false;
                }
                Yii::$app->redis->set($attemptsKey, $attempts + 1);
                Yii::$app->redis->expire($attemptsKey, Us\Config\CAPTCHA_ATTEMPTS_EXPIRE);
                return false;
            }
            Yii::$app->redis->del($attemptsKey);
            Yii::$app->redis->del($captchaKey);
        }
        return true;
    }

    private function doUpdateUserDevice($uid, $data)
    {
        $this->doStoreRedisString(Us\User\US_DEVID.$uid, $data->required('device_id'), 10);
        $connection = Yii::$app->db;
        $result = $connection->createCommand()->update(Us\TableName\USER_DEVICE,
            [
                'log_device_id' => $data->required('device_id'),
                'os_version' => Types::versionToLong($data->required('os_version')),
                'client_version' => Types::versionToLong($data->required('client_version')),
                'platform' => $data->required('platform'),
                'log_ip' => ip2long(Protocol::remoteAddress()),
                'login_time' => date("Y-m-d H:i:s"),
                'phone_model' => $this->doGetPhoneModelCode($data->required('phone_model')),
                'distributor' => $this->doGetPhoneModelCode($data->required('distributor')),
            ],
            ['uid' => $uid])->execute();
        if ($result) {
            $resultHistory = $connection->createCommand()->insert(Us\TableName\USER_DEVICE_HISTORY, [
                'uid' => $uid,
                'log_ip' => ip2long(Protocol::remoteAddress()),
                'log_device_id' => $data->required('device_id'),
                'platform' => $data->required('platform'),
                'login_time' => date("Y-m-d H:i:s"),
                'distributor' => $this->doGetPhoneModelCode($data->required('distributor')),
                'phone_model' => $this->doGetPhoneModelCode($data->required('phone_model')),
                'client_version' => Types::versionToLong($data->required('client_version')),
                'os_version' => Types::versionToLong($data->required('os_version')),
            ])->execute();
            return $resultHistory;
        }
        return false;
    }

    public function sendCaptchaAction()
    {
        if (HI\Captcha\SEND) {
            $data = Protocol::arguments();
            if ($this->doVerifySendCaptcha($data)) {
                if(SMS::sms_all_send("hoolai", $data->required('token'), HI\User\CAPTCHA_MESSAGE, '0', $data->required('type'))) {
                    $response = ['result' => true];
                    Protocol::ok($response);
                    return ;
                }
            }
            Protocol::ok(null, null, null, Notice::get()->tooManyRequest());
            return ;
        }
        Protocol::badRequest(NULL, NULL, '发送验证码已关闭');
        return ;
    }

    private function doVerifySendCaptcha($data)
    {
        if (!$this->doVerifySendCaptchaTimes($data)) {
            return false;
        }
        return true;
    }

    private function doVerifySendCaptchaTimes($data)
    {
        $phone = $data->required('token');
        $attempts = Yii::$app->redis->incr(HI\User\CAPTCHA_PHONE.$phone.'attempts');
        Yii::$app->redis->expire(HI\User\CAPTCHA_PHONE.$phone.'attempts', HI\Config\CAPTCHA_ATTEMPTS_EXPIRE);
        if( $attempts > HI\User\ATTEMPTS_TIMES ){
            return false;
        }
        return true;
    }

    public function updateProfileAction()
    {
        $data = Protocol::arguments();
        if ($this->verifyUserStatus($data->requiredInt('login_uid'))) {
            if($data->optional('nickname')) {
                if(CouponModel::queryOneTableInfo('customer_id', HI\TableName\CUSTOMER, ['lastname' => $data->optional('nickname')])) {
                    echo json_encode(['c' => 400, 'n' => '', 'm' => '', 'p' => '', 'h' => '该昵称已存在', "ts" => time()]);
                    return ;
                }
            }
            $nickname = Predicates::isNotEmpty($data->optional('nickname'))?$this->doUpdateUserNickname($data):"";
            $avatar = $_FILES?$this->doUpdateUserAvatar($data->requiredInt('login_uid'), $_FILES['file']):"";
            $response = [
                'avatar' => $avatar,
                'nickname' => $nickname,
            ];
            Protocol::ok($response);
            return ;
        }
    }

    private function doUpdateUserNickname($data)
    {
        if(Predicates::isNotEmpty($data->optional('nickname'))){
            UserModel::updateProfile($data->requiredInt('login_uid'), 'nickname', $data->optional('nickname'));
            return $data->optional('nickname');
        }
        return false;
    }

    private function doUpdateUserAvatar($uid, $file)
    {
        $avatarArray = $this->doUploadAvatar($file, $uid);
        UserModel::updateProfile($uid, 'avatar', $avatarArray['subUrlName'])?$avatarArray['subUrl']:new stdClass();
        return $avatarArray['subUrl'];
    }
    
    private function doUploadAvatar($file, $uid, $category=0, $pictureType=0, $filetype=0, $event_id=0, $moment_id = 0, $fileName='', $url='')
    {
        return CosFile::uploadFile($file, $uid, $category, $pictureType, $filetype, $event_id, $moment_id, $fileName, $url);
    }

    public function addAddressAction()
    {
        $data = Protocol::arguments();
        Execution::autoTransaction(Yii::$app->db, function() use($data) {
            if ($this->verifyUserStatus($data->requiredInt('login_uid'))) {
                if ($result = UserModel::addAddress($data)) {
                    Protocol::ok(['aid' => $result]);
                    return ;
                } else {
                    Protocol::badRequest(NULL, '添加失败');
                }
            }
        });
    }

    private function verifyUserStatus($uid)
    {
        $result = UserModel::getUserStatus($uid);
        if (Predicates::equals($result, false)) {
            Protocol::badRequest(null, "账号不存在");
            return false;
        }
        if (Predicates::equals($result, HI\User\STATUS_LOCK)) {
            Protocol::badRequest(null, "账号被冻结");
            return false;
        }
        return true;
    }

    public function addressListAction()
    {
        $data = Protocol::arguments();
        Execution::autoTransaction(Yii::$app->db, function() use($data) {
            Protocol::ok(['list' => UserModel::getAddressData($data->requiredInt('login_uid'))]);
        });
    }

    public function updateAddressAction()
    {
        $data = Protocol::arguments();
        Execution::autoTransaction(Yii::$app->db, function() use($data) {
            UserModel::updateUserSomeAddress($data->requiredInt('login_uid'), $data->requiredInt('aid'), json_decode($data->required('data'), true));
            Protocol::ok();
        });
    }

    public function deleteAddressAction()
    {
        $data = Protocol::arguments();
        Execution::autoTransaction(Yii::$app->db, function() use($data) {
            UserModel::deleteAddress($data->requiredInt('login_uid'), $data->requiredInt('aid'));
            Protocol::ok();
        });
    }

    //记录游客账号的设备信息
    public function recordPlatfromIdAction()
    {
        $transaction = Yii::$app->db->beginTransaction();
        $commit = false;
        try {
            $platfromid = Protocol::optional('udid'); //设备ID
            $client_version = Protocol::optional('client_version'); //客户端版本
            $phone_model = Protocol::optional('model'); //手机型号
            $client_version_code = Protocol::optional('client_version_code');
            $operator = Protocol::optional('operator'); //运营商
            $device = Protocol::optional('device'); //类型
            $os_version = Protocol::optional('os_version'); //ios 系统版本
            $ip = Protocol::optional('ip'); //内网
            $mi_regid = Protocol::optional('mi_regid'); //小米 token
            $jailbroken = Protocol::optional('jailbroken');  //是否越狱
            $idfa = Protocol::optional('idfa');  //广告标识
            $network = Protocol::optional('network');  //联网类型  WiFi 3G
            $platfrom_token = Protocol::optional('token');  //设备token
    
            if ($device == 'iOS') {
                $device = 1;
            } else {
                $device = 2;
            }
            if ($jailbroken == '0') {
                $jailbroken = 1;
            } else {
                $jailbroken = 2;
            }
    
            if (empty($platfromid)) {
                Protocol::badRequest();
                return false;
            }
            $create_time = date('Y-m-d H:i:s');
            $connection = Yii::$app->db;
            $recordInfo = self::getPlatfromId($platfromid, $device);
            if ($recordInfo) {
                self::updatePlatfromId($connection, $platfromid, $client_version, $phone_model, $operator, $client_version_code, $device, $os_version,$ip, $mi_regid, $jailbroken, $idfa, $network, $platfrom_token, $create_time) ;
            } else {
                self::insertPlatfromId($connection, $platfromid, $client_version, $phone_model, $operator, $client_version_code, $device, $os_version,$ip, $mi_regid, $jailbroken, $idfa, $network, $platfrom_token, $create_time);
            }
            $commit = true;
            Protocol::ok();
        }
        finally {
            if ($commit) {
                $transaction->commit();
            }
            else {
                $transaction->rollback();
            }
        }
    }
    
    //查询游客记录帐号
    private function getPlatfromId ($platfromid, $device)
    {
        $query = new Query;
        $select = HI\TableName\USER_RECORD_PLATFROMID.".id, ".HI\TableName\USER_RECORD_PLATFROMID.'.platfrom_id ';
        $where = HI\TableName\USER_RECORD_PLATFROMID.".platfrom_id = '$platfromid' and device = '$device'";
        $recordInfo = $query
        ->select($select)
        ->from(HI\TableName\USER_RECORD_PLATFROMID)
        ->where($where)
        ->one();
        return $recordInfo;
    }
    
    //更新游客记录帐号
    private function updatePlatfromId($connection, $platfromid, $client_version, $phone_model, $operator, $client_version_code, $device, $os_version,$ip, $mi_regid, $jailbroken, $idfa, $network, $platfrom_token, $create_time)
    {
        $result = $connection->createCommand()->update(HI\TableName\USER_RECORD_PLATFROMID,
                [
                'client_version' => $client_version,
                'model' => $phone_model,
                'client_version_code' => $client_version_code,
                'device' => $device,
                'os_version' => $os_version,
                'ip' => $ip,
                'mi_regid' => $mi_regid,
                'jailbroken' => $jailbroken,
                'idfa' => $idfa,
                'network' => $network,
                'token' => $platfrom_token,
                'create_time' =>$create_time
                ],
                ['platfrom_id' => $platfromid])->execute();
    }

    //写入游客记录帐号
    private  function insertPlatfromId($connection, $platfromid, $client_version, $phone_model, $operator, $client_version_code, $device, $os_version,$ip, $mi_regid, $jailbroken, $idfa, $network, $platfrom_token, $create_time)
    {
        $connection->createCommand()->insert(HI\TableName\USER_RECORD_PLATFROMID,
                [
                'platfrom_id' => $platfromid,
                'client_version' => $client_version,
                'model' => $phone_model,
                'operator' => $operator,
                'client_version_code' => $client_version_code,
                'device' => $device,
                'os_version' => $os_version,
                'ip' => $ip,
                'mi_regid' => $mi_regid,
                'jailbroken' => $jailbroken,
                'idfa' => $idfa,
                'network' => $network,
                'token' => $platfrom_token,
                'create_time' =>$create_time,
                ])->execute();
    }

    /* 验证分享 */
    public function verifyShareAction()
    {
        $data = Protocol::arguments();
        Execution::autoTransaction(Yii::$app->db, function() use($data) {
            if (Predicates::equals(UserModel::queryThisMonthShare($data->requiredInt('login_uid')), false)) {
                /* 修改用户vip状态 */
                $updateVip = DataBase::doTableUpdate(HI\TableName\CUSTOMER, ['approved' => 1], ['customer_id' => $data->requiredInt('login_uid')]);
                $updateShare = $this->doUpdateShare($data->required('ident'),$data->requiredInt('login_uid'));
                if ($updateVip && $updateShare) {
                    /* 标识 */
                    Yii::$app->redis->set(HI\SHARE\HIDE_SHARE . $data->requiredInt('login_uid'), 1);
                    Yii::$app->redis->expire(HI\SHARE\HIDE_SHARE . $data->requiredInt('login_uid'), $this->getMonth(date('Y-m-d')));
                    Protocol::ok(['content' => '恭喜您获得终身免费冲印资格，本月免费冲印券已放入您的账户', 'payload' => ['type' => 3]]);
                    return;
                }
            } elseif (Predicates::isNull(UserModel::queryThisMonthShare($data->requiredInt('login_uid')))) {
                if ($this->doUpdateShare($data->required('ident'),$data->requiredInt('login_uid'))) {
                    /* 标识第n个月 分享后隐藏 */
                    Yii::$app->redis->set(HI\SHARE\HIDE_SHARE . $data->requiredInt('login_uid'), 1);
                    Yii::$app->redis->expire(HI\SHARE\HIDE_SHARE . $data->requiredInt('login_uid'), $this->getMonth(date('Y-m-d')));
                    Protocol::ok(['content' => '本月免费冲印券已放入您的账户', 'payload' => ['type' => 3]]);
                    return;
                }
            }
            Protocol::ok(['content' => '您已领取了本月免费冲印券', 'payload' => ['type' => 3]]);
        });
    }

    private function getMonth($date)
    {
        $firstday = date("Y-m-01",strtotime($date));
        $lastday = date("Y-m-d", strtotime("$firstday +1 month"));
        return (strtotime($lastday) - time());
    }

    private function doUpdateShare($ident, $uid)
    {
        /* 修改本次分享状态 ：是否成功 */
        $updateShare = DataBase::doTableUpdate(CouponModel::$shareCoupon, ['status' => 1], ['code' => $ident]);
        /* 优惠券  */
        $couponData = UserModel::queryUserCouponNums(self::SEND_COUPON_ID);
        /* 新优惠券 */
        $couponInfo = CouponModel::doGetNewCoupon($couponData);
        /* 加一张优惠劵 */
        $coupon = [
            'coupon_id' => $couponInfo['coupon_id'],
            'customer_id' => $uid,
            'date_added' => date('Y-m-d H:i:s')
        ];
        $insert = DataBase::doTableInsert(CouponModel::$customerCoupon, $coupon, 1);
        if ($updateShare && $insert) {
           return true;
        }
        return false;
    }

    /* H5 授权登录 */
    public function webRegisterAction()
    {
        header('Access-Control-Allow-Origin:' . HI\APP_URL_PREFIX);
        $data = Protocol::arguments();
        Execution::autoTransaction(Yii::$app->db, function() use($data) {
            if (Protocol::getMethod() == 'GET') {
                CStat::shareStatLog($data->optional('target'), $data->optional('stat'));
                if (empty($data->optional('token'))) {
                    /* code 获取token */
                    $token = $this->doGetAccessToken($data->required('code'), $data->required('type'), $data->optional('redirect_uri'));
                    if (!$token) {
                        Protocol::ok('', '', '', 'get access_token fail');
                        return;
                    }
                    /* 获取用户数据 */
                    $user = $this->doGetUserData($token, $data->required('type'));
                    if (!$user) {
                        Protocol::ok('', '', '', 'failed to obtain user data');
                        return;
                    }
                } else {
                    $user['token'] = $data->optional('token');
                }
                /* 查询第三方用户信息  */
                if (!UserModel::queryUserInfo($user['token'])) {
                    if (empty($data->optional('token'))) {
                        DataBase::doTableInsert(CouponModel::$thirdUser, $user);
                    } else {
                        Protocol::ok('', '', '', 'token exception');
                        return;
                    }
                }
            } else {
                $user['token'] = $data->required('token');
            }

            /* 得到关联用户列表 */
            $userList = UserModel::assocQueryUser($data->required('ident'));
            $share = CouponModel::queryOneTableInfo('share_id', CouponModel::$shareCoupon, ['code' => $data->required('ident')]);
            /* 验证 ident */
            if (!$share) {
                Protocol::ok(['token' => $user['token'], 'list' => '', 'all_received' => true]);
                return;
            }

            /* 已经领过 */
            if ($list = UserModel::queryUserWhetherOver($data->required('ident'), $user['token'])) {
                Protocol::ok(['token' => $user['token'], 'list' => $userList, 'self_received' => $this->doReturnFinalData($list)]);
                return;
            }
            /* 单链接已经领完  */
            if (CouponModel::queryWhetherOverCount($data->required('ident')) >= self::RECEIVE_COUNT_LIMIT) {
                Protocol::ok(['token' => $user['token'], 'list' => $userList, 'all_received' => true]);
                return;
            }
            if (Protocol::getMethod() == 'POST') {
                /* 校验手机号合法性 */
                if (!preg_match("/1[345678]{1}\d{9}$/", $data->required('telephone'))) {
                    Protocol::ok(['token' => $user['token'], 'list' => $userList, 'phone_illegal' => true]);
                    return;
                }
                /* 校验手机号是否相同  */
                $phoneVerify = CouponModel::queryOneTableInfo('*', CouponModel::$userShare, ['share_id' => Preconditions::checkArgument($share['share_id']), 'phone' => $data->required('telephone')]);
                if ($phoneVerify) {
                    Protocol::ok(['token' => $user['token'], 'list' => $userList, 'phone_same' => true]);
                    return;
                }
                /* 写入分享数据  */
                $shareData = $this->doWriteToShareData($share['share_id'], $data->required('token'), $data->required('telephone'));
                Protocol::ok(['result' => $shareData, 'list' => UserModel::assocQueryUser($data->required('ident'))]);
                return;
            }
            Protocol::ok(['token' => $user['token'], 'list' => $userList]);
        });
    }
    
    private function doVerifyCouponNums($shareId, $randCount)
    {
        if (empty(Yii::$app->redis->LRANGE(HI\Coupon\RAND_RED_PACKET . $shareId, 0, -1))) {
            /* 取出优惠券数组 */
            Preconditions::checkArgument($couponZset = CouponModel::queryShareCouponList($shareId, $randCount));
        }

        /* 产生随机优惠券ID */
        $redisCouponId = Yii::$app->redis->LPOP(HI\Coupon\RAND_RED_PACKET . $shareId);
        $couponId = empty($redisCouponId) ? $this->getRandCoupon($couponZset) : $redisCouponId;

        /* 唯一性 */
        while(true) {
            /* 查看随机优惠劵是否被领完  */
            $couponData = UserModel::queryUserCouponNums($couponId);
            if (!$couponData) {
                $couponId = $this->getRandCoupon($couponZset);
            } else {
                break;
            }
        }

        return $couponData;
    }

    /* 写入分享数据 */
    private function doWriteToShareData($shareId, $uid, $phone)
    {
        $couponInfo = CouponModel::doGetNewCoupon($this->doVerifyCouponNums($shareId, self::RAND_COUNT));
        /* 手机号入库  */
        $userShare = [
            'share_id' => $shareId,
            'token' => $uid,
            'assoc_code' => $couponInfo['coupon_id'], //随机抽取优惠劵绑定用户
            'status' => 0,
            'phone' => $phone
        ];
        $couponInfo['phone'] = $phone;
        Preconditions::checkArgument(DataBase::doTableInsert(CouponModel::$userShare, $userShare));
        return $this->doReturnFinalData($couponInfo);
    }

    /* 得到最终数据 */
    private function doReturnFinalData($couponData)
    {
        Preconditions::checkArgument($couponData['coupon_id']);
        /* 查询优惠券 折扣数  */
        $discountData = Coupon::doGetResultByType($couponData['property'], $couponData['coupon_id']);
        $date = Coupon::classificationUsedType($couponData['use_type'], $couponData['validity'], $couponData['use_start'], $couponData['use_end'], date('Y-m-d'));
        /* H5 返回数据 */
        return [
            'title' => $couponData['name'],
            'code' => $couponData['phone'],
            'count' => $discountData['count'],
            'unit' => $discountData['unit'],
            'end_date' => $date['end_date'],
        ];
    }

    /* 根据概率生成随机优惠劵 */
    private function getRandCoupon($proArr)
    {
        $result = '';
        //概率数组的总概率精度
        $proSum = array_sum($proArr);
        //概率数组循环
        foreach ($proArr as $key => $proCur) {
            $randNum = mt_rand(1, $proSum);             //抽取随机数
            if ($randNum <= $proCur) {
                $result = $key;                         //得出结果
                break;
            } else {
                $proSum -= $proCur;
            }
        }
        unset ($proArr);
        return $result;
    }

    /*  第一步：得到token */
    private function doGetAccessToken($code, $type, $redirectUrl = NULL)
    {
        switch ($type) {
            /* 微信 */
            case 0 :
                $url = HI\Config\WECHAT_TOKEN."appid=".HI\Config\WECHAT_APPID."&secret=".HI\Config\WECHAT_SECRET."&grant_type=authorization_code&code=".$code;
                $payload = Http::sendGet($url);
                if (isset($payload['errcode'])) {
                    if ($payload['errcode'] == 40014) {
                        $access = $this->doRefreshAccessToken($payload['refresh_token']);
                        if (isset($payload['errcode'])) {
                            return false;
                        }
                        return $access;
                    }
                    return false;
                }
                return $payload;
                break;
            /* 微博 */
            case 1 :
                /* 注册应用里的回调地址一致 */
                $urlData['redirect_uri'] = self::REDIRECT_URI;
                $urlData['client_id'] = HI\Config\WEBO_APPID;
                $urlData['client_secret'] = HI\Config\WEBO_SECRET;
                $urlData['grant_type'] = 'authorization_code';
                $urlData['code'] = $code;
                $payload = Preconditions::checkArgument(json_decode(Http::sendPost(HI\Config\WEBO_TOKEN, http_build_query($urlData)),true));
                if (isset($payload['error_code'])) {
                    error_log("error:".$payload['error']." time:".date('Y-m-d H:i:s', time()));
                    return false;
                }
                return $payload;
            /* QQ */
            case 2 :
                /* 与上面一步中传入的redirect_uri保持一致  */
                $bashUrl = '&redirect_uri='.urlencode(self::REDIRECT_URI . "order/coupon.html");
                $url = HI\Config\QQ_TOKEN."grant_type=authorization_code&client_id=".HI\Config\QQ_APPID."&client_secret=".HI\Config\QQ_SECRET."&code=".$code.$bashUrl;
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_HEADER, 0);
                $output = curl_exec($ch);
                curl_close($ch);
                $result = explode('&', $output);
                if (isset($output['code'])) {
                    error_log("error:".$output['msg']." time:".date('Y-m-d H:i:s', time()));
                    return false;
                }
                $payload = explode('=', $result[0]);
                return $payload[1];
                break;
            default :
                throw new InvalidArgumentException('Invalid type oh my god');
        }
    }

    /* 第二步 ：得到用户数据 */
    private function doGetUserData($payload, $type)
    {
        $randName = HI\User\DEFAULT_NICKNAME.substr(strtoupper(uuid_create()), -5);
        switch ($type) {
            case 0 :
                $url = HI\Config\WECHAT_USER.$payload['access_token']."&openid=".$payload['openid']."&lang=zh_CN";
                $payload = Http::sendGet($url);
                if (isset($payload['errcode'])) {
                    error_log("error:".$payload['errmsg']." time:".date('Y-m-d H:i:s', time()));
                    return false;
                }
                return [
                        'name' => empty($payload['nickname']) ? $randName : $payload['nickname'],
                        'gender' => $payload['sex'], 'avatar' => $this->doHandleAvatarException($payload['headimgurl']), 'token'=> $payload['openid'],
                        'platform' => 0
                       ];
                break;
            case 1 :
                
                $url = HI\Config\WEBO_USER_INFO . 'access_token=' . $payload['access_token'].'&uid='.$payload['uid'];
                $payload = Http::sendGet($url);
                if (isset($payload['error_code'])) {
                    error_log("error:".$payload['error']." time:".date('Y-m-d H:i:s', time()));
                    return false;
                }
                return [
                        'name' => empty($payload['name']) ? $randName : $payload['name'],
                        'gender' => $payload['gender'] == 'm' ? 1 : 0, 'avatar' => $this->doHandleAvatarException($payload['profile_image_url']), 'token' => $payload['id'],
                        'platform' => 1
                       ];
                break;
            case 2 :
                /* 获取openid */
                $url = HI\Config\QQ_GET_OPENID.$payload;
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_HEADER, 0);
                $output = curl_exec($ch);
                curl_close($ch);
                $error = json_encode($output, true);
                $data = Preconditions::checkArgument(explode('"', $output));
                if (isset($error['code'])) {
                    error_log("error:".$error['msg']." time:".date('Y-m-d H:i:s', time()));
                    return false;
                }
                /* 获取用户信息 */
                $sUrl = HI\Config\QQ_USER_INFO.$payload.'&openid='.$data[7].'&oauth_consumer_key='.HI\Config\QQ_APPID;
                $payload = Http::sendGet($sUrl);
                if ($payload['ret'] != 0) {
                    error_log("error:".$payload['msg']." time:".date('Y-m-d H:i:s', time()));
                    return false;
                }
                return [
                        'name' => empty($payload['nickname']) ? $randName : $payload['nickname'],
                        'gender' => $payload['gender'] == '男' ? 1 : 0, 'avatar' => $this->doHandleAvatarException($payload['figureurl']), 'token' => $data[7],
                        'platform' => 2
                       ];
                break;
            default :
                throw new InvalidArgumentException('Invalid type oh my god');
        }
    }

    public function doHandleAvatarException($imageUrl)
    {
        if (@fopen($imageUrl, 'r' )) {
            $array = CosFile::uploadFile([], 0, 0, 0, 0, 0, 0, '', $imageUrl);
            $avatar = Preconditions::checkArgument($array['subUrlName']);
        } else {
            $avatar = 'default';
        }
        return $avatar;
    }

    private function doRefreshAccessToken($refreshToken)
    {
        $url = HI\Config\WECHAT_REFRESH_TOKEN."appid=".HI\Config\WECHAT_APPID."&grant_type=refresh_token&refresh_token=".$refreshToken;
        $payload = Http::sendGet($url);
        if (isset($payload['errcode'])) {
            error_log("error:".$payload['errmsg']." time:".date('Y-m-d H:i:s', time()));
            return false;
        }
        return $payload;
    }
    
    public function getJSSDKAction()
    {
       // header('Access-Control-Allow-Origin:' . HI\APP_URL_PREFIX);
        $data = Protocol::arguments();
        echo json_encode($this->doGetSignature($data->required('url')));
    }

    private function doGetSignature($url)
    {
        $value = 4;
        $flag = true;
        $noncestr = bin2hex(openssl_random_pseudo_bytes($value, $flag));
        $timestamp = time();
        $str = "jsapi_ticket=".$this->doGetApi_ticket()."&noncestr=".$noncestr."&timestamp=".$timestamp."&url=".$url;
        return ['noncestr' => $noncestr, 'timestamp' => $timestamp, 'signature' => sha1($str)];
    }

    private function doGetApi_ticket()
    {
        $ticketData = $this->doGetTicket($this->doGetWxAccessToken());
        if ($ticketData['errcode']) {
            return false;
        }
        return $ticketData['ticket'];
    }

    private function doGetTicket($accessToken)
    {
        $url = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?type=jsapi&access_token=".$accessToken;
        $payload = Http::sendGet($url);
        if ($payload['errcode']) {
            return $this->doReGetTicket();
        }
        if ($payload['errcode']) {
            return false;
        }
        return $payload;
    }
    
    private function doReGetTicket()
    {
        $url = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?type=jsapi&access_token=".$this->doSetUniqueAccessToken();
        $payload =  Http::sendGet($url);
        if ($payload['errcode']) {
            return false;
        }
        return $payload;
    }

    private function doGetWxAccessToken()
    {
        $token = Yii::$app->redis->get(HI\User\WECHAT_TOKEN);
        if (!$token) {
            $token = $this->doSetUniqueAccessToken();
        }
        return $token;
    }

    private function doSetUniqueAccessToken()
    {
        $token = $this->doGetUniqueAccessToken();
        $this->dostoreRedisString(HI\User\WECHAT_TOKEN, $token, 10);
        return $token;
    }

    private function doGetUniqueAccessToken()
    {
        $url = HI\Config\WECHAT_UNIQUE_TOKEN."appid=".HI\Config\WECHAT_APPID."&secret=".HI\Config\WECHAT_SECRET;
        $payload = Http::sendGet($url);
        if (isset($payload['errcode'])) {
            return false;
        }
        return $payload['access_token'];
    }
    
    public function testAction()
    {
    }
}
