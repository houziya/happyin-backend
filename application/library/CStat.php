<?php
use	\yii\db\Query;

class CStat{
    const OFFSET=3;
    private static $table_name = 'stat';
    private static $columns = array('id', 'stat_date', 'create_time', 'type' , 'data');
    
    public static function getTableName()
    {
        return self::$table_name;
    }
    
    public static function addLog($user_name, $action, $class_name , $class_obj ,$result = "") 
    {
        $now_time=time();
        $insert_data = array ('user_name' => $user_name, 'action' => $action, 'class_name' => $class_name ,'class_obj' => $class_obj , 'result' => $result ,'op_time' => $now_time);
        $db = new Query;
        $id = $db -> createCommand() -> insert ( self::getTableName(), $insert_data ) -> execute();
        return $id;
    }
    
    public static function getDatas($start,$page_size,$start_time='',$end_time='',$type='') 
    {
        $db = new Query;
        $condition = array();
        if ($start_time != '') {
            $condition[]="stat_date>=$start_time";
        }
        if ($end_time !='') {
            $condition[]="stat_date<=$end_time";
        }
        if($type!=''){
            $condition[]=$type;
        }
        if (empty($condition)) {
            $condition=array();
        } else {
            $condition=implode(' AND ',$condition);
        }
        $list = $db -> select (self::$columns) -> from(self::getTableName()) -> where($condition)->OrderBy('stat_date desc')->limit($page_size)->offset($start) -> all();
        if (!empty($list)) {
            foreach ($list as &$item){
                $item['data']=json_decode($item['data'],true);
            }
        }
        
        if ($list) {
            return $list;
        }
        return array ();
    }
    
    public static function find($date,$type)
    {
        $db = new Query;
        $condition=array();
        if($date != '') {
        $condition[]="stat_date='$date'";
        }
        if ($type!='') {
            $condition[]=$type;
        }
        if (empty($condition)) {
            $condition=array();
        } else {
            $condition=implode(' AND ',$condition);
        }
//         var_dump($condition);die;
        $list = $db -> from(self::getTableName()) -> where($condition)-> one();
        if (!empty($list)) {
                $list['data']=json_decode($list['data'],true);
        }
        
        
        if ($list) {
            return $list;
        }
        return array();
    }
    
    public static function count($condition='') 
    {
        $db = new Query;
        $num = $db -> from(self::getTableName()) -> where($condition)-> count();
        return $num;
    }
    
    public static function search($start_date,$end_date)
    {
        $db=new Query;
        $condition = array();
        if ($start_date != '') {
            $condition[]="stat_date>='$start_date'";
        }
        if ($end_date !='') {
            $condition[]="stat_date<='$end_date'";
        }
        if (empty($condition)) {
            $condition=array();
        } else {
            $condition=implode(' AND ',$condition);
        }
        
        $list = $db->from(self::getTableName())->where($condition)->all();
        foreach ($list as &$v) {
            $v['data']=json_decode($v['data'],true);
        }
        if ($list) {
            return $list;
        }
        return array();
    }
    
    public static function getCountByDate($start_date,$end_date,$type) 
    {
        $db=new Query;
        $condition = array();
        if ($start_date != '') {
            $condition[]="stat_date>=$start_date";
        }
        if ($end_date !='') {
            $condition[]="stat_date<=$end_date";
        }
        if ($type!='') {
            $condition[]=$type;
        }
        if (empty($condition)) {
            $condition=array();
        } else {
            $condition=implode(' AND ',$condition);
        }
// 		var_dump($condition);die;
        $num = $db->from(self::getTableName())->where($condition)->count();
        return $num;
    }
    
    public static function getDateCount($start_date,$type)
    {
        $db=new Query;
        $condition = array();
        if ($start_date != '') {
            $condition[]="stat_date=$start_date";
        }
        if ($type!='') {
            $condition[]=$type;
        }
        if (empty($condition)) {
            $condition=array();
        } else {
            $condition=implode(' AND ',$condition);
        }
        $num = $db->from(self::getTableName())->where($condition)->count();
        return $num;
    }
    
    public static function getDateDatas($start,$page_size,$start_time='',$type='')
    {
        $db = new Query; 
        $condition = array();
        if ($start_time != '') {
            $condition[]="stat_date=$start_time";
        }
        if($type!=''){
            $condition[]=$type;
        }
        if (empty($condition)) {
            $condition=array();
        } else {
            $condition=implode(' AND ',$condition);
        }
        $list = $db -> select (self::$columns) -> from(self::getTableName()) -> where($condition)->OrderBy('stat_date desc')->limit($page_size)->offset($start) -> all();
        if (!empty($list)) {
            foreach ($list as &$item){
                $item['data']=json_decode($item['data'],true);
            }
        }
    
        if ($list) {
            return $list;
        }
        return array ();
    }
    
    public static function showPager($link,&$page_no,$page_size,$row_count)
    {
        $url="";
        $params="";
        if($link != ""){
            $pos = strpos($link,"?");
            if($pos ===false ){
                $url = $link;
            }else{
                $url=substr($link,0,$pos);
                $params=substr($link,$pos+1);
            }
        }
        	
        $navibar = "<div class=\"pagination\"><ul>";
        $offset=self::OFFSET;
        //$page_size=10;
        $total_page=$row_count%$page_size==0?$row_count/$page_size:ceil($row_count/$page_size);
        
        $page_no=$page_no<1?1:$page_no;
        $page_no=$page_no>($total_page)?($total_page):$page_no;
        if ($page_no > 1){
            $navibar .= "<li><a href=\"$url?page_no=1&$params\">首页</a></li>\n <li><a href=\"$url?page_no=".($page_no-1)."&$params \">上一页</a></li>\n";
        }
        /**** 显示页数 分页栏显示11页，前5条...当前页...后5条 *****/
        $start_page = $page_no -$offset;
        $end_page =$page_no+$offset;
        if($start_page<1){
            $start_page=1;
        }
        if($end_page>$total_page){
            $end_page=$total_page;
        }
        for($i=$start_page;$i<=$end_page;$i++){
            if($i==$page_no){
                $navibar.= "<li><span>$i</span></li>";
            }else{
                $navibar.= "<li><a href=\" $url?page_no=$i&$params \">$i</a></li>";
            }
        }
        
        if ($page_no < $total_page){
            $navibar .= "<li><a href=\"$url?page_no=".($page_no+1)."&$params\">下一页</a></li>\n <li><a href=\"$url?page_no=$total_page&$params\">末页</a></li>\n ";
        }
        if($total_page>0){
            $navibar.="<li><a>".$page_no ."/". $total_page."</a></li>";
        }
        $navibar.="<li><a>共".$row_count."条</a></li>";
        $jump ="";
        //$jump ="<li><form action='$url' method='GET' name='jumpForm'><input type='text' name='page_no' value='$page_no'></form></li>";
        
        $navibar.=$jump;
        $navibar.="</ul></div>";
        
        return $navibar;
    }

    /* 上个月 当前月 当天 总计  */
    public static function totalCost($code)
    {
        $currentDate = date('Y-m-d');
        $currentNextDay = date('Y-m-d', strtotime('+1 day'));
        $currentDay = date('Y-m-01');
        $lastMonth = date('Y-m-01', strtotime('-1 month'));
        $lastMonthLastDay =  date('Y-m-t', strtotime('-1 month'));
        $date1 = " and o.date_modified >= '{$lastMonth}' and o.date_modified <= '{$lastMonthLastDay}'"; //上个月
        $list['last_month'] = self::queryCouponAssocOrderCost($code, $date1);
        $date2 = " and o.date_modified >= '{$currentDay}' and o.date_modified <= '{$currentDate}'"; //当前月
        $list['current_month'] = self::queryCouponAssocOrderCost($code, $date2);
        $date3 = " and o.date_modified >= '{$currentDate}' and o.date_modified <= '{$currentNextDay}'";  //当天
        $list['current_day'] = self::queryCouponAssocOrderCost($code, $date3);
        $list['sum'] = self::queryCouponAssocOrderCost($code);

        return $list;
    }

    /* 销售额总计 */
    private static function queryCouponAssocOrderCost($code, $date = '', $type = NULL)
    {
        $query = (new Query())->select('sum(o.total) as total, count(distinct u.customer_id) as new_user')
            ->from(HI\TableName\ORDER_COUPON . ' as oc')
            ->leftJoin(HI\TableName\ORDER . ' as o', ' oc.order_id = o.order_id')
            ->leftJoin(HI\TableName\CUSTOMER . ' as u', ' u.customer_id = o.customer_id' )
            ->leftJoin(HI\TableName\COUPON . ' as c', ' c.coupon_id = oc.coupon_id')
            ->where(['c.code' => $code])
            ->andWhere("o.order_status_id > 1 and o.order_status_id != 14 $date ")
            ->one();

        return empty($query) ? 0 : $query;
    }
    
    /* order total >= 20 */
    private static function querySale($userId, $date = '', $type = NULL)
    {
        $query = (new Query())->select('sum(o.total) as total')
            ->from(HI\TableName\CUSTOMER_TO_CHANNEL . ' as cc')
            ->leftJoin(HI\TableName\ORDER . ' as o', ' oc.customer_id = o.customer_id')
            ->leftJoin(HI\TableName\ORDER_PRODUCT . ' as op', ' o.order_id = op.order_id')
            ->andWhere("o.order_status_id > 1 and o.order_status_id != 14 $date ")
            ->one();
        return empty($query) ? 0 : $query;
    }

    public static function test()
    {
        $lastMonth = date('Y-m-01', strtotime('-1 month'));
        $lastMonthLastDay =  date('Y-m-t', strtotime('-1 month'));
        $date1 = " and o.date_modified >= '{$lastMonth}' and o.date_modified <= '{$lastMonthLastDay}'"; //上个月
        $list = self::querySale('徐帅36', $date1);
    }
    
    /* 订单信息 */
    public static function queryOrderInfos($userId, $start = 1, $count =  PHP_INT_MAX)
    {
        $query = (new Query())->select('o.order_number, o.total, o.date_added')
            ->from(HI\TableName\CUSTOMER_TO_CHANNEL . ' as cc')
            ->leftJoin(HI\TableName\ORDER . ' as o', ' cc.customer_id = o.customer_id')
            ->where('o.total >= 20 and cc.channel_id = ' . $userId)
            ->andWhere("o.order_status_id > 1 and o.order_status_id != 14")
            ->offset($start)
            ->limit($count)
            ->orderBy('o.date_modified desc')
            ->all();

        return empty($query) ? 0 : $query;
    }

    /* 根据渠道ID 去查询 customer订单信息 */
    public static function queryOrderInfosByChannel($code, $start = 1, $count =  PHP_INT_MAX)
    {
        $query = (new Query())->select('o.order_number, o.total, o.date_added')
            ->from(HI\TableName\ORDER_COUPON . ' as oc')
            ->leftJoin(HI\TableName\ORDER . ' as o', ' oc.order_id = o.order_id')
            ->leftJoin(HI\TableName\COUPON . ' as c', ' c.coupon_id = oc.coupon_id')
            ->where(['c.code' => $code])
            ->andWhere("o.order_status_id > 1 and o.order_status_id != 14")
            ->offset($start)
            ->limit($count)
            ->orderBy('o.date_modified desc')
            ->all();
    
        return empty($query) ? 0 : $query;
    }

    /* 代理销售情况 0  1显示代理名字  */
    public static function doTheirAgentsInfo($parentId, $type = NULL)
    {
        $condition = '';
        if ($type) {
            $condition = ' and user_id !=' . $parentId;
        }
        $params = 'topic, coupon_code, user_name, create_time, user_id, parent_user_id';
        /* 管理员账号-- 所有数据 官方后台 */
        if ($parentId == HI\Config\Console\ADMIN_ID) {
            $query = (new Query)->select($params)
                ->from('c_user')
                ->andWhere('user_type = 1 ' . $condition)
                ->orderBy('user_id desc')
                ->all();
        } else {
            $query = self::returnSelfAndSubAgent($parentId, $params, $condition); //中上级代理销售信息
            $newList = [];
            if (is_array($query)) {
                foreach ($query as $temp) {
                    $newList = array_merge($query, self::returnSelfAndSubAgent($temp['user_id'], $params, $condition));
                }
            }
            if (!$type) {
                foreach($newList as &$v) $v = serialize($v);
                $array = array_unique($newList);
                foreach($array as &$v) $v = unserialize($v);
                $query = $array;
            }
            if (empty($query)) {   //最低层代理销售额
                $query = (new Query)->select($params)
                    ->from('c_user')
                    ->where(['user_id' => $parentId])
                    ->andWhere('user_type = 1 ' . $condition)
                    ->all();
            }
        }
        $agentSaleList = [];
        $detailInfos = [];
        if ($query) {
            foreach($query as $value) {
                if ($type) {
                    $detailInfos[$value['topic']][] = [
                        'total' => CStat::totalCost($value['coupon_code']), 
                        'user_name' => $value['user_name'], 'create_time' => $value['create_time'],
                        'sub_agent_nums' => empty(self::querySelfSubAgent($value['user_id'])) ? 0 : self::querySelfSubAgent($value['user_id']),
                        'parent_user_id' => $value['parent_user_id'],
                        'user_id' => $value['user_id']
                        ];
                } else {
                    $currentDayTotal = CStat::totalCost($value['coupon_code'])['current_day']['total'];
                    $lastMonthTotal = CStat::totalCost($value['coupon_code'])['last_month']['total'];
                    $currentMonthToal = CStat::totalCost($value['coupon_code'])['current_month']['total'];
                    $currentMonthUser = CStat::totalCost($value['coupon_code'])['current_month']['new_user'];
                    $lastMonthUser = CStat::totalCost($value['coupon_code'])['last_month']['new_user'];
                    $currentDayUser = CStat::totalCost($value['coupon_code'])['current_day']['new_user'];
                    @$total[$value['topic']]['last_month']['total'] += $lastMonthTotal;
                    @$total[$value['topic']]['current_month']['total'] += $currentMonthToal;
                    @$total[$value['topic']]['current_day']['total'] += $currentDayTotal;
                    @$total[$value['topic']]['last_month']['new_user'] += $lastMonthUser;
                    @$total[$value['topic']]['current_month']['new_user'] += $currentMonthUser;
                    @$total[$value['topic']]['current_day']['new_user'] += $currentDayUser;
                    @$allTotal['last_month']['total'] += $lastMonthTotal;
                    @$allTotal['current_month']['total'] += $currentMonthToal;
                    @$allTotal['current_day']['total'] += $currentDayTotal;
                    @$allTotal['last_month']['new_user'] += $lastMonthUser;
                    @$allTotal['current_month']['new_user'] += $currentMonthUser;
                    @$allTotal['current_day']['new_user'] += $currentDayUser;
                }
            }

            if ($type) {
                return $detailInfos;
            }
            return ['splitting' => $total, 'total' => $allTotal];
        }

        if ($type) {
            return false;
        }

        return ['splitting' => false, 'total' => false];
    }

    /* 自己旗下代理  */
    public static function querySelfSubAgent($userId)
    {
        $query = (new Query())->from('c_user')
            ->where(['parent_user_id' => $userId])
            ->andWhere('user_id !=' . $userId)
            ->count();

        return empty($query) ? 0 : $query;
    }

    /* 查询用户的上级代理ID 以及代理名称 */
    public static function queryUserInfo($parentId)
    {
        return (new Query())->select('user_name as p_name, user_id as p_id')
            ->from('c_user')
            ->where(['user_id' => $parentId])
            ->one();
    }

    /* 包括自己和旗下代理数据 */
    public static function returnSelfAndSubAgent($parentId, $params, $condition = '')
    {
        $query = (new Query)->select($params)
            ->from('c_user')
            ->where(['parent_user_id' => $parentId])
            ->andWhere('user_type = 1 ' . $condition)
            ->all();
        $query2 = (new Query)->select($params)
            ->from('c_user')
            ->where(['user_id' => $parentId])
            ->andWhere('user_type = 1 ' . $condition)
            ->all();
        if ($query && $query2) {
            return array_merge($query2, $query);
        } elseif (!$query) {
            return $query2;
        }
        return empty($query) ? [] : $query;
    }

    public static function returnAgentSaleInfo($couponArray, $start = '', $count = '')
    {
        if (!is_array($couponArray)) {
            return ['list' => '', 'count' => 0];
        }
        $list = [];
        $num = 0;
        foreach($couponArray as $k => $value) {
            $subOrderInfo = self::queryOrderInfos($value['coupon_code'], $start, $count);
            $num += count($subOrderInfo);
            if (empty($subOrderInfo)) {
                continue;
            } else {
                $list[$value['topic']][$value['user_name']] = $subOrderInfo;
            }
        }
        return ['list' => $list, 'count' => $num];
    }
    
    public static function tree($queryResult)
    {
        foreach ($queryResult as $value) {
            $list = self::queryAssocSubAgent($userId);
            if (empty($list)) {
                continue;
            } else {
                foreach ($list as $temp) {
                    $queryResult[] = $temp;
                }
            }
        }
        
        return $queryResult;
    }
    
    /* 下属信息 */
    public static function queryAssocSubAgent($userId)
    {
        $temp = (new Query())->select('user_id, user_name, topic, parent_user_id')
            ->from('c_user')
            ->where(['parent_user_id' => $userId])
            ->all();
        if (is_array($temp)) {
            foreach ($temp as $val) {
                $list[] = self::queryAssocSubAgent($val['use_id']);
            }
        } else {
            $list[] = $temp;
        }
        return $list;
    }

    public static function shareStatLog($target, $stat = NULL)
    {
          if (Predicates::equals($stat, 1)) {
              switch ($target) {
                  case 'bannerShare' :
                      self::returnRedisCount('bs_o_');
                      break;
                  case 'appShare' :
                      self::returnRedisCount('as_o_');
                      break;
                  case 'order' :
                      self::returnRedisCount('os_o_');
                      break;
                  case 'product' :
                      self::returnRedisCount('ps_o_');
                      break;
                  default :
              }
              return true;
          }
          return true;
    }

    public static function returnRedisCount($key = NULL)
    {
        if ($key) {
            if (Yii::$app->redis->get($key . date('Y-m-d')) === false) {
                Yii::$app->redis->set($key . date('Y-m-d'), 1);
            }
            Yii::$app->redis->incr($key . date('Y-m-d'));
            return true;
        }
        return true;
    }
}
?>