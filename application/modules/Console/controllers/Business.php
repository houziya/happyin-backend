<?php
use Yaf\Controller_Abstract;
use Yaf\Dispatcher;
class BusinessController extends Controller_Abstract
{
    /* 官方后台列表 */
    public function listAction()
    {
        CInit::config($this);
        $this->display('list');
    }
    
    public function IndexAction()
    {
        CInit::config($this);
        $data = Protocol::arguments();
        $user_info = CUserSession::getSessionInfo();
        $switchId = $user_info['user_id'];
        /* 存在跳转参数  */
        if (Protocol::getMethod() == "GET") {
            $switchId = empty($data->optional('user_id')) ? $switchId : $data->optional('user_id');
            unset($user_info);
            $user_info = CUser::getUserById($switchId);
        }
        /* 父级信息  */
        $user_info['parent_info'] = CStat::queryUserInfo($user_info['parent_user_id']);
        $sidebar = CSideBar::getTree ();
        /* 订单信息 */
        if ($couponArray = CStat::returnSelfAndSubAgent($switchId, 'coupon_code, topic')) {
            $orderInfo = CStat::returnAgentSaleInfo($couponArray);
        } else {
            $orderInfo = ['list' => '', 'count' => 0];
        }
        $selfSaleInfo = CStat::doTheirAgentsInfo($switchId); //大列表
        $saleDetailInfo = CStat::doTheirAgentsInfo($switchId, 1);  //合计列表 带总计
        foreach($sidebar as $sideInfo) {
            foreach($sideInfo['menu_list'] as $infos) {
                if($infos['shortcut_allowed'] ==1) {
                    $newInfos[] = $infos['menu_id'];
                }
            }
        }
        if (!empty($newInfos)) {
            $newData = implode(',',$newInfos);
            $menus = CMenuUrl::getMenuByIds($newData);
        } else {
            $menus = '';
        }
        $userId = empty($data->optional('user_id')) ? $user_info['user_id'] : $data->optional('user_id');
        //START 数据库查询及分页数据
        $page_size = Console\ADMIN\PAGE_SIZE;
        $page_no = $data->optional('page_no', '') < 1 ? 1:$data->optional('page_no', '');
        $row_count = $orderInfo['count'];
        $total_page=$row_count%$page_size==0?$row_count/$page_size:ceil($row_count/$page_size);
        $total_page=$total_page<1?1:$total_page;
        $page_no=$page_no>($total_page)?($total_page):$page_no;
        $start=($page_no - 1) * $page_size;
        $orderInfo = CStat::returnAgentSaleInfo($couponArray, $start, $page_size);
        $page_html = CChannel::showPager("index?flag=1&user_id=" . $userId. "&search=".$data->optional('search'), $page_no, $page_size, $row_count);
        $this->getView()->assign('page_html', $page_html);

        $this->getView()->assign("user_info", CUserSession::getSessionInfo()); //登录者个人信息
        $this->getView()->assign('new_info', $user_info); //最新跳转用户信息
        $this->getView()->assign("menus",$menus);  //快捷菜单
        $this->getView()->assign('order', $orderInfo); //订单信息
        $this->getView()->assign('sale', $selfSaleInfo);
        $this->getView()->assign('detail', $saleDetailInfo); //分级代理详情
        $this->getView()->assign('flag', $data->optional('flag', '')); //跳转参数
        $this->display('index');
    }

    /* 官方后台基础数据接口  */
    public function statListAction()
    {
        echo json_encode(CStat::doTheirAgentsInfo(HI\Config\Console\ADMIN_ID, 1));
    }
    
    public function testAction()
    {
        $content  = '3级代理';
        echo substr($content, 0, 1);
    }

}