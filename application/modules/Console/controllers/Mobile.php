<?php
use Yaf\Controller_Abstract;
use Yaf\Dispatcher;
use yii\db\Query;
use yii\db\Exception;
class MobileController extends Controller_Abstract
{
       public function MainAction()
       {
            CInit::config($this);
            $this->display('main');
       }
       
       public function SearchDateAction()
       {
            $data = Protocol::arguments();
            $date = date("Y-m-d");
            $getDate = empty($data['date']) ? $date : $data['date'];
            $costDate = explode("-",$getDate);
            $forCostDate = $costDate[0].$costDate[1].$costDate[2];
            $startTime = $getDate . " 00:00:00";
            $endTime = $getDate . " 23:59:59";
            $datas = (new Query())
            ->select("count(*) as order_nums, sum(total) as totals, date(o.date_added) as date_added, f.free_nums ")
            ->from('order as o')
            ->leftJoin("(select count(*) as free_nums, date(date_added) as date_added from `order` where CAST(total as SIGNED) = 8 AND date_added >= '{$startTime}' AND date_added <= '{$endTime}' AND order_status_id != 1 group by date(date_added)) as f", "f.date_added = date(o.date_added)")
            ->where("o.date_added >= '{$startTime}' AND o.date_added <= '{$endTime}' AND o.order_status_id != 1")
            ->groupBy(["date(o.date_added)"])
            ->all();
            
            $costDatas = (new Query())
            ->select("sum(cost) as costs")
            ->from('cost_log as cl')
            ->where("cl.stat_date = '{$forCostDate}'")
            ->all();
            
            if(empty($datas[0])){
                Protocol::ok(['hasdatas' => 'false', 'datas' => ['date_added' => $getDate]]);
            }else {
                $datas[0]['costs'] = empty($costDatas[0]['costs']) ? 0 : $costDatas[0]['costs'];
                Protocol::ok(['hasdatas' => 'true', 'datas' => $datas[0]]);
            }
            
       }
}