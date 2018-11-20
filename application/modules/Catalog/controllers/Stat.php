<?php
/**
 *
 * @explain 活动分享，邀请，添加 计划任务接口   line:514 methodName:eventStatData
 * 数据来源接口 line:44 methodName:uploadLog
 */
use Yaf\Controller_Abstract;
use yii\db\Query;

class StatController extends Controller_Abstract
{
    public function adClickAction()
    {
        AdClick::click(Protocol::required("d"), Protocol::required("t"));
        $this->redirect(HI\Config\IOS_DOWNLOAD_URL);
    }
    
    public function shareStatAction()
    {
        $data = Protocol::arguments();
        $result = CStat::shareStatLog($data->required('target'), $data->optional('stat'));
        Protocol::ok($result);
    }
}
