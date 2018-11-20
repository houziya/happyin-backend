<?php
use Yaf\Controller_Abstract;
use yii\db\Query;

class SystemController extends Controller_Abstract
{
    CONST SPECIAL_CITY = ['北京市', '天津市', '上海市', '重庆市'];
    
    public function cityConfig()
    {
        ini_set('memory_limit','1024M');
        $city = $this->doReadJsonFile("/conf/city.json");
        $cityList = json_decode($city, true);
        $num = 1;
        $response = [];
        $hash = [];
        foreach ($cityList as $key => $list) {
            $provice = $list['name'];
            $hash[$provice] = $num++;
            foreach ($list['sub'] as $value) {
                if ($value['name']!='市辖区' && $value['name']!='县' && !is_array($value['name'])) {
                    $town = $value['name'];
                    $hash[$town] = $num++;
                } else {
                    $town = $provice;
                }
                foreach ($value['sub'] as $val) {
                    if ($val['name']=='市辖区') {
                        continue;
                    }
                    $district = $val['name'];
                    $hash[$town.$district] = $num++;
                }
            }
        }
         array_walk($hash, function($code, $cityName) {
             if(Yii::$app->redis->hget(HI\User\CITY_CODE, $code) === false) {
                Yii::$app->redis->hset(HI\User\CITY_CODE, $code, $cityName);
                Yii::$app->redis->hset(HI\User\CITY_CODE, $cityName, $code);
             }
         });
        foreach ($cityList as $n => $list) {
            $provice = $list['name'];
            $response[$n]['id'] = $hash[$provice];
            $response[$n]['name'] = $provice;
            $cnt = 0;
            $sum = 0;
            $flag = 0;
            foreach ($list['sub'] as $value) {
                if ($value['name']=='市辖区' || $value['name']=='县') {
                    $flag = 1;
                    $city = $provice;
                } else if (!is_array($value['name'])) {
                    $city = $value['name'];
                }
                if ($flag) {
                    $response[$n]['city']['id'] = $hash[$city];
                    $response[$n]['city']['name'] = $city;
                } else {
                    $response[$n]['city'][$sum]['id'] = $hash[$city];
                    $response[$n]['city'][$sum]['name'] = $city;
                }
                if (is_array($value)) {
                    foreach ($value['sub'] as $key => $val) {
                        if ($val['name']=='市辖区') {
                            continue;
                        }
                        $district = $val['name'];
                        if ($flag) {
                            $response[$n]['city']['district'][$cnt]['id'] = $hash[$city.$district];
                            $response[$n]['city']['district'][$cnt++]['name'] = $district;
                        } else {
                            $response[$n]['city'][$sum]['district'][$cnt]['id'] = $hash[$city.$district];
                            $response[$n]['city'][$sum]['district'][$cnt++]['name'] = $district;
                        }
                    }
                }
                $sum ++;
            }
        }
        foreach ($response as $key=>$temp) {
            if(in_array($temp['name'], self::SPECIAL_CITY)) {
                $response[$key]['city'] = [$temp['city']];
            } else {
                foreach($temp['city'] as $curr=>$vs) {
                   foreach($vs as $kk=>$flags) {
                       if(is_array($flags)) {
                           foreach ($flags as $g=>$ck) {
                              if(key($flags) != 0) {
                                 $response[$key]['city'][$curr]['district'] = array_values($flags);
                              } 
                           } 
                       }
                   }
                }
            }
        }
//         echo json_encode(['list'=> $response]);die();
        //$response['cfg'] = Push::zk("/moca/spread/subscription/cfg", json_encode(['list'=> $response]));
        return $response;//Protocol::ok(['list'=> $response]);
    }

    private function doReadJsonFile($fileName)
    {
        if (empty($fileName)) {
            return false;
        }
        $jsonFile = APP_PATH . $fileName;
        $template = trim(stripslashes(file_get_contents($jsonFile)));
        $template = preg_replace("/\s/","",$template);
        return $template;
    }
    
    
    public function getDomainInfoAction()
    {
        header('Access-Control-Allow-Origin:' . HI\APP_URL_PREFIX);
        $response = [
        'init_domain' => HI\Config\INIT_DOMAIN,
        'upload_domain' => HI\Config\UPLOAD_DOMAIN,
        'download_domain' => HI\Config\DOWNLOAD_DOMAIN,
        'spread_service' => explode(',', HI\Config\SPREAD_DOMAIN),
        'flags' => HI\Config\FLAG,
        'log_level' => HI\Config\LOG_LEVEL
        ];
//         if (Protocol::required('platform') == '0' && Protocol::required('version') > HI\Config\IOS_CURRENT_VERSION) {
//             $response["flags"] = HI\Config\REVIEW_FLAG;
//         }
         if (Protocol::required('platform') == '0' && Protocol::required('version') == 3) {
             $response["js_patch"] = HI\APP_URL_PREFIX . '/patch/patch_3.js';
         }
//         if (Protocol::required('version') == 14) {
//             $response["js_patch"] = Us\APP_URL_PREFIX . '/patch/patch_14.js';
//         }
//         if (Protocol::required('version') == 16) {
//             $response["js_patch"] = Us\APP_URL_PREFIX . '/patch/patch_16.js';
//         }
//         if (Protocol::optional('device_id', 0)) {
//             AdClick::activate(Protocol::optional('device_id'));
//         }
        Protocol::ok($response);
    }
    
    public function sysinfoAction ()
    {
        $file = __DIR__."/../../../../conf/sysinfo.json";
        $myfile = fopen($file, "w");
        $city = self::cityConfig();
        $clause = "
　 欢迎使用快乐印移动应用在线冲印照片，快乐印的个性化定制、所见即所得等多种特性将给您带来前所未有的体验，为了保证我们的服务质量，请仔细阅读下面的服务条款，在此条款中，您一方以“您”和“您的”表示，本应用则以“我们”、“我们的”表示。
一、服务条款的确认与接纳 
　　你需要定期仔细阅读《服务协议》，在使用本应用所提供服务前，您必须先接受和确认此协议。否则请勿使用本应用提供的服务。 
二、服务所需的设备和其他条件 
　　为了使用本应用提供的服务，您必须拥有接入互联网所需的硬件和软件，您还需支付所有互联网的连接费用。
三、用户个人资料的使用和填写 
　　当您在本应用上进行手机照片冲印、订制个性化文字影像产品以及参加网上支付等活动时，在您同意及确认下，本应用将通过订单形式要求您提供一些个人资料。这些个人资料包括：真实姓名、性别、电话、通信地址、电子邮件等情况，这些情况本应用仅用来确保您能够更好的享受本应用为您提供的服务，本应用保证任何情况下都不会主动将这些信息提供给第三方，除非是国家法律有要求的情况例外。 
　　基于我们所提供的网络服务的重要性，用户应同意： 
　　1、提供详细、真实的个人资料；
　　2、及时更新资料；
　　3、若用户代表一家公司或其他法律主体使用本应用提供的服务，则用户应声明和保证该公司或其他法律主体接受本服务“条款”的约束。
四、用户帐号及安全性 
　　用户激活本应用后，我们将提供给用户一个用户帐号。该用户帐号由用户负责保管。每个用户都要对以其用户帐号进行的所有活动和事件负法律责任。用户若发现任何非法使用用户帐号的行为或危害应用安全的情况，请立即通告本应用。
五、应用使用 
　　1、本应用会根据实际情况对现有服务项目、流程进行调整以及变更，本应用对此产生的问题不承担任何责任。
　　2、一切上传本应用的图片，您都负有安全保管原始存档的责任。本应用将不对任何原因产生的您原始存档丢失的情况负责。 
　　3、您已经设定为“共享”“条件共享”相册中的图片，您需自己承担可能被别人不正当使用的风险。因此引发争议，您应使本应用免受该问题或争议的损害。
　　4、如果您的相片（图片）内容违反国家法律或超过本条款规定的限制，我们会立即删除，相片（图片）也不会退还给您。如果您违反相关的法律法规，一经发现，我们有义务将上述情况上报至国家有关管理部门。
　　5、如果您通过本应用发送订单，您需了解并同意： 
　　 (1) 当您的冲印订单上传到应用并被确认后，订单不可取消。 
　　 (2) 如果您的订单相片（图片）含有违反国家法律、法规的内容，我们有权撤消您的订单。 
　　 (3) 如果您收到的相片或个性化文字影像产品出现质量问题，我们将尽快协助您解决问题。 
　　 (4) 如果因您的主观原因而造成误冲印，我们可以尽量与您协商解决，由此发生的费用需由您承担。 
　　 (5) 您上传到我们系统中的相册，在30-45天左右后，我们有权自动删除相册以及相册内相片（图片）。
　　 (6) 为保证我们为您提供优质的网上冲印服务，我们有权清空您上传到相册已经超过1个月的相片，保证网络服务的通畅。
六、服务条款的修改与完善 
　　在我们认为有必要的时候，有权修改并完善服务条款（包括但不限于本条款）和服务。
　　您在使用本应用的服务时，须受当时最新版本的服务条款所约束。您应该定期审阅本服务条款及刊登在本应用上的其他条款。如果您不同意更新过的条款，您有权放弃自己的使用资格。您在条款更新后继续使用我们的服务，将表示您已接受我们更新的条款。
　　本应用保留随时修改或中断服务而不需通知您的权利，本应用行使修改或中断服务的权利，不需对您或第三方负责。 
七、版　权 
　　1、经由我们向您提供的所有信息，包括但不限于文字、软件、声音、相片、录像、图表、商标和其它商业信息资料，均受中华人民共和国版权法、著作权法、商标法、专利法和其它财产所有权法保护。任何人只有在得到书面授权的情况下，方可使用这些信息。未得到我们事先的书面授权，您不得擅自使用、复制、发行这些信息、软件、代码、商标或资料，也不得修改、创造衍生的作品。
　　2、当您提交数码相片（图片）用于存储、分享和或冲印时，您应当确认您拥有所有相片（图片）及有关文字的著作所有权、资格与权益。我们声明对您相册中的任何相片均不享有所有权。

       3、快乐印提供了一个互联网平台服务于快乐印应用的使用用户，用户可以发布自己的设计销售商品。我们禁止使用该服务的用户销售的商品侵犯第三方的知识产权权利（如著作权，商标，贸易礼服和宣传权等），道德权利，或者是诽谤。如果他们认为我们服务的用户已经侵犯了他们的权利，我们鼓励权利人与我们联系。如果我们知道您的权利被侵犯，我们将对该侵犯进行裁量，禁止销售侵权产品或者禁止用户享受我们的服务。

       如果您认为您的知识产权或权益被我们服务的用户侵犯，请联系我们并提供相关的侵权证据，我们会尽快处理并回复您。

八、隐私权保护
　　我们尊重您个人的隐私权。未经您同意，我们不会向第三方透露您提交的个人信息和相片。但下列情况除外： 
　　 1、本应用完成用户订单后，需向配送公司提供这些信息，以进行配送服务。 
　　 2、用户授权本应用透露这些信息。 
　　 3、相应的法律及程序要求本应用提供用户的个人资料。 
　　 4、为维护本应用的合法利益。
九、免责声明 
　　1、拒绝提供担保 
　　您同意由您个人对使用本应用的服务承担风险。本应用明确表示不提供任何类型的担保，不论是明示的或暗示的，包括但不限于任何权利担保、商业性的隐含担保、特定目的担保以及不侵权的担保。 
　　本应用不做如下担保： 
　　 (1)本应用不担保服务一定能满足您的要求； 
　　 (2)本应用不担保服务不会中断，对服务的及时性、安全性和准确性也都不做担保。 
　　 (3)对于因不可抗力或本应用不可控制的原因造成的网络服务中断或缺陷，本应用不承担相应责任，但会尽量减少由此对用户造成的损失。 
　　2、责任有限 
　　本应用对任何直接或间接、有意或无意及其继起的损害不负任何责任。
十、相片内容的限制和禁止内容 
　　您明白、确认并且同意，您提交的用于存储、分享和或冲印的相片（图片）内容必须符合中华人民共和国适用法律的规定，您不可储存、刊登或分发任何非法禁止內容。 
　　这些内容包括（但不限于）： 
　　 (1) 侵犯或可能侵犯任何第三方知识产权或其他合法权益的内容； 
　　 (2) 构成或可能构成反社会或刑事违法（包括歧视、谋杀、虐待、人身伤害、跟踪、儿童色情及性骚扰）的內容； 
　　 (3) 违反或可能违反公众秩序、保密规定和道德标准的內容； 
　　 (4) 危害本应用或其他应用安全的病毒或工具； 
　　 (6) 煽惑、建议或鼓励任何违法活动的内容； 
　　 (7) 有关任何个人（除您以外）的个人资料（如姓名、电话号码、地址等）。
　　所有带有上述内容的相片（图片）和资料都不会退还给您。如果您违反相关的法律法规，一经发现，我们有义务将上述情况上报至国家有关管理部门。
十一、通告 
　　我们会根据需要，通过页面的公告、电子邮件或常规的信件向您通告信息，您在使用本应用时就已经默认接受此事。
十二、信息的储存及限制 
　　本应用有判定用户行为是否符合本应用服务条款要求的权利，如果用户违背本应用服务条款的规定，本应用有权中断其帐号。
十三、终止服务
　　本应用可根据实际情况中断一项或多项服务。本应用不需由于中断服务而对任何个人或第三方负责。用户若反对任何服务条款、对后来的条款修改有异议或对本应用服务不满，用户可以行使如下权利： 
　　 (1)不再使用本应用信息服务。 
　　 (2)通知本应用停止对该用户的服务。 
　　终止用户服务后，用户使用本应用服务的权利马上终止。从那时起，用户没有权利，本应用也没有义务传送任何未处理的信息或未完成的服务给用户或第三方。
十四、其它条款
　　本服务适用于中华人民共和国的法律和法规。您与我们一致同意所有由于使用本服务而产生的或与本服务有关的争议均提交中华人民共和国法院解决。若本服务的任何条款是无效的或不可执行的，则这些条款将尽可能的在与其原意匹配的情况下，用有效的和可执行的条款代替，而其它条款则继续有效。您还需同意，我们对本条款中任何权利的放弃并不构成对其它条款权利的放弃。
        ";
        $txt = ['city_list' => $city, 'clause' => $clause, 'feedback_service' => 0];
        fwrite($myfile, json_encode($txt));
        fclose($myfile);
        echo exec('/usr/bin/zktk '. HI\Config\ZK .' set '.HI\Config\ZK_PATH.'sysinfo '.$file);
        echo 'ok';
    }
    
    public function sysinfoAndroidAction ()
    {
        $file = __DIR__."/../../../../conf/sysinfo.android.json";
        $myfile = fopen($file, "w");
        $txt = [
            'app_info' => [
                    'app_url' => 'http://dev.happyin.com.cn:9962/apk/app-anzhi-debug-unaligned-1.0.apk',
                    'code' => 2,
                    'desc' => '版本更新测试',
                    'version' => '1.1.0',
                    'type' => 2,
                ],
            'launch_info' => [
                    'img_url' => "images/splash/05be17d4-5024-40be-aa6e-4ac2446ba0a4.jpg",
                ],
        ];
        fwrite($myfile, json_encode($txt));
        fclose($myfile);
        echo exec('/usr/bin/zktk '. HI\Config\ZK .' set '.HI\Config\ZK_PATH.' sysinfo.android '.$file);
        echo 'ok';
    }

    public function parcleComboAction()
    {
        Execution::autoUnlink(function($unlink) {
            $orderId = array_reduce((new Query())->select("order_id, order_number")->from("order")->where(["order_number" => json_decode(Protocol::required("order"), true)])->all(), function($carry, $order) { 
                $carry[$order["order_id"]] = $order["order_number"]; 
                return $carry;
            }, []);
            $parcles = array_reduce((new Query())->select("parcle, order_id")->from("order_splitting")->where(["order_id" => array_keys($orderId)])->all(), function($carry, $parcle) use ($orderId) { 
                $carry[$orderId[$parcle["order_id"]]] = $parcle["parcle"];
                return $carry;
            }, []);
            $download = [];
            $output = [];
            array_walk($parcles, function(&$parcle, $orderNumber) use (&$output, &$download) {
                if (Predicates::isNotEmpty($parcle)) {
                    $download[$orderNumber] = "/order/parcel/$parcle.zip"; 
                } else {
                    $output["$orderNumber.zip"] = APP_PATH . "/resources/empty.dat";
                }
            });
            $download = ContentCache::loadAll($download);
            array_walk($download, function(&$file, $orderNumber) use (&$output, $unlink) {
                $unlink($file);
                $output["$orderNumber.zip"] = $file;
            });
            $unlink($tmpFile = createTempFile());
            Zipper::zip($output, $tmpFile);
            $targetFile = "/order/combo/" . date("Ymd", time()) . "/" . uuid_create() . ".zip";
            $result = CosFile::uploadTo($tmpFile, $targetFile);
            header("Location: http://" . HI\Config\DOWNLOAD_DOMAIN . $targetFile);
        });
    }
}
