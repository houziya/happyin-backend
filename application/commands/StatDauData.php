<?php
use yii\db\Query;

class StatDauDataCommand
{
    const DAU_FILE_PATH = 'hi-api/';
    const UID_FILE_PATH = 'uid-list/';
    
    public static function main ()
    {
        self::statDau();
    }    
    private static function doSerializeUrl($url)
    {
        if (empty($url)) {
            return false;
        }
        $urlArray = parse_url($url);
        $tmp = explode(" ", @$urlArray['query']);
        $params = $tmp[0];
        $paramArray = explode("&", $params);
        foreach ($paramArray as $param) {
            $targetArray = explode("=", $param);
            if ($targetArray[0] == 'login_uid') {
                return $targetArray[1];
            }
        }
        return false;
    }
    
    public static function statDau()
    {
        $statDate = date("Y-m-d", strtotime("-1 day"));
        Execution::withFallback(
        function () use($statDate){
            self::doCreateStatDau($statDate);
            $source = self::readLog($statDate);
            $drData = self::getDRData($statDate, $source);
            if (self::doUpdateDRData($statDate, $drData)) {
                echo "Mission Completed!\n";
            }
            else {
                echo "Fail!\n";
            }
        }
        );
    }
    
    private static function doStoreUidInTXT($statDate, $uidList)
    {
        if (Predicates::isEmpty($uidList) || Predicates::isEmpty($statDate)) {
            return false;
        }
        $myfile = fopen(HI\Path\READ_LOG.self::UID_FILE_PATH.$statDate.".txt", "w") or die("Unable to open file!");
        fwrite($myfile, json_encode($uidList));
        fclose($myfile);
    }
    
    private static function doUpdateDRData($statDate, $drData)
    {
        if (Predicates::isEmpty($statDate) || Predicates::isEmpty($drData)) {
            return false;
        }
        $ren = self::doQueryStatDR($statDate);
        $resultD = self::doUpdateDauData($statDate, $drData['d'], $ren);
        return $resultD;
    }
    
    private static function doUpdateDauData($statDate, $dau, $ren)
    {
        if (Predicates::isEmpty($statDate) || Predicates::isEmpty($dau)) {
            return false;
        }
        $response = $ren;
        $response["d"] = $dau;
        $connection = Yii::$app->db;
        $result = $connection->createCommand()->update(HI\TableName\STAT, ['data' => json_encode($response)], ['stat_date' => date("Ymd", strtotime($statDate)), 'type' => 1])->execute();
        return $result;
    }
    
    private static function doQueryStatDR($statDate)
    {
        $query = new Query;
        $drData = $query->select('data') ->from(HI\TableName\STAT) ->where(['type' => 1, 'stat_date' => date("Ymd", strtotime($statDate))])->one();
        return json_decode($drData['data'], true);
    }
    
    private static function getDRData($statDate, $source)
    {
        //self::doStoreUidInTXT($statDate, $source);
        $userData = self::doQueryDRUserData($statDate, $source);
        $dau = self::doSerializeDauData($userData);
        return ["d" => $dau];
    }
    
    private static function doSerializeDauData($userData)
    {
        if (Predicates::isEmpty($userData)) {
            return false;
        }
       $response = self::doInitDauResponse();
       foreach ($userData as $data) {
            if (Predicates::isEmpty($data['g']) || Predicates::isEmpty($data['p'])) {
                continue;
            }
            //统计性别数据
            $response['g'][self::doSerializeNewUserGenderData($data['g'])]++;
            //统计渠道数据
            $response['p'][self::doSerializeDauUserPlatformData($data['p'])]++;
        }
        return $response;
    }
    
    private static function doSerializeNewUserGenderData($gender)
    {
        switch ($gender) {
        	case 0:
        	    return 0;
        	    break;
        	case 1:
        	    return 1;
        	    break;
        	default:
        	    throw new InvalidArgumentException('Invalid registration type '. $gender);
        }
        return 0;
    }
    
    private static function doInitDauResponse()
    {
        $response = [
        'g' => [
                0 => 0, 
                1 => 0
            ],
        'p' => [
                HI\User\REGISTER_PLATFORM_IOS => 0, 
                HI\User\REGISTER_PLATFORM_ANDROID => 0,
                HI\User\REGISTER_PLATFORM_H5_IOS => 0
            ]
        ];
        return $response;
    }
    
    private static function doSerializeDauUserPlatformData($platform)
    {
        switch ($platform) {
        	case HI\User\REGISTER_PLATFORM_IOS:
        	    return HI\User\REGISTER_PLATFORM_IOS;
        	    break;
        	case HI\User\REGISTER_PLATFORM_ANDROID:
        	    return HI\User\REGISTER_PLATFORM_ANDROID;
        	    break;
        	case HI\User\REGISTER_PLATFORM_H5_IOS:
        	    return HI\User\REGISTER_PLATFORM_H5_IOS;
        	    break;
        	case HI\User\REGISTER_PLATFORM_H5_ANDROID:
        	    return HI\User\REGISTER_PLATFORM_H5_IOS;
        	    break;
        	case HI\User\REGISTER_PLATFORM_H5_OTHERS:
        	    return HI\User\REGISTER_PLATFORM_H5_IOS;
        	    break;
        	default:
        	    throw new InvalidArgumentException('Invalid registration type '. $platform);
        }
        return 0;
    }
    
    private static function doQueryDRUserData($statDate, $userList)
    {
        if (Predicates::isEmpty($userList) || Predicates::isEmpty($statDate)) {
            return false;
        }
        $uidList = [];
        foreach ($userList as $uid => $val) {
            $uidList[] = $uid;
        }
        $query = new Query;
        $query->select('customer.customer_id as u, customer.date_added as r, customer_device.platform as p, customer.address_id as g')
        ->from(HI\TableName\CUSTOMER)
        ->innerJoin('customer_device', 'customer.customer_id = customer_device.customer_id')
        ->where(['in', 'customer.customer_id', $uidList, ['and', 'customer.status' => HI\User\STATUS_NORMAL]])
        ->orderBy(['customer.customer_id' => SORT_ASC]);
        $userData = $query->all();
        if (Predicates::isEmpty($userData)) {
            return 0;
        }
        $response = [];
        foreach ($userData as $user) {
            $response[] = $user;
        }
        return $response;
    }
    
    private static function readLog($statDate)
    {
        if (Predicates::isEmpty($statDate)) {
            return false;
        }
        return self::doGetDRUser($statDate, self::doGetStatPath($statDate));
    }
    
    private static function doGetDRUser($statDate, $path)
    {
        if (Predicates::isEmpty($path) || Predicates::isEmpty($statDate)) {
            return false;
        }
        $hashUid = [];
        foreach (scandir($path) as $file) {
            if (Predicates::equals($file, '.') || Predicates::equals($file, '..')) {
                continue;
            }
            $content = fopen(self::doGetStatPath($statDate).$file, "r");
            while (!feof($content)) {
                $line = fgets($content);
                if (strpos($line, "login_uid")!==false) {
                    $uid = self::doSerializeUrl($line);
                    if (!$uid) {
                        continue;
                    }
                    $hashUid[$uid] = 1;
                }
            }
        }
        return $hashUid;
    }
    
    private static function doGetStatPath($statDate)
    {
        if (Predicates::isEmpty($statDate)) {
            return false;
        }
        return HI\Path\READ_LOG.self::DAU_FILE_PATH.date('Ym', strtotime($statDate)).'/'.date('d', strtotime($statDate)).'/';
    }
    
    private static function doCreateStatDau($statDate)
    {
        if (Predicates::isEmpty($statDate)) {
            return false;
        }
        $response = [
        'd' => [       //日活跃
        "g" => [0 => 0, 1 => 0],               //性别0-female1-male
        "p" => [0 => 0, 1 => 0, 2 => 0],        //平台0-ios1-android2-web
        ],
        ];
//         for ($i=0; $i<60; $i++) {
//             $response["r"][$i]["p"] = [0, 0, 0];
//         }
        $connection = Yii::$app->db;
        $res = $connection->createCommand()->insert(HI\TableName\STAT, [
                'stat_date' => date("Ymd", strtotime($statDate)),
                'create_time' => date("Y-m-d H:i:s"),
                'type' => 1,
                'data' => json_encode($response),
                ])->execute();
        return $res;
    }
}
