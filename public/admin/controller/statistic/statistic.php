<?php
use Yaf\Controller_Abstract;
use yii\db\Query;
use yii\db\Exception;
class ControllerStatisticStatistic extends Controller {
    /* 营收统计 */
    public function sale() {
        $startTime = isset($this->request->post['filter_start_date']) ? date("Y-m-d H:i:s", strtotime($this->request->post['filter_start_date'])) : date("Y-m-d H:i:s", strtotime(date("Y-m-d")));
        $endTime = isset($this->request->post['filter_end_date']) ? date("Y-m-d H:i:s", strtotime($this->request->post['filter_end_date']) + 60*60*24) : date("Y-m-d H:i:s", strtotime(date("Y-m-d")) + 60*60*24);
        $datas = (new Query())
        ->select("count(*) as order_nums, sum(total) as totals, date(o.date_added) as date_added, f.free_nums, s.user_nums, c.day_cost")
        ->from('order as o')
        ->leftJoin("(select count(*) as free_nums, date(date_added) as date_added from `order` where CAST(total as SIGNED) = 8 AND date_added >= '{$startTime}' AND date_added < '{$endTime}' AND order_status_id != 1 group by date(date_added)) as f", "f.date_added = date(o.date_added)")
        ->leftJoin("(SELECT COUNT(*) AS user_nums, DATE(date_added) AS date_added FROM `customer` WHERE date_added >= '{$startTime}' AND date_added < '{$endTime}' GROUP BY DATE(date_added)) AS s ON s.date_added = DATE(o.date_added)")
        ->leftJoin("(SELECT SUM(cost) AS day_cost, DATE(stat_date) AS date_added FROM `cost_log` WHERE stat_date >= '" . date("Ymd", strtotime($startTime)) . "' AND stat_date < '" . date("Ymd", strtotime($endTime)) . "' GROUP BY DATE(stat_date)) AS c ON c.date_added = DATE(o.date_added)")
        ->where("o.date_added >= '{$startTime}' AND o.date_added < '{$endTime}' AND o.order_status_id > 1 AND o.order_status_id != 14")
        ->groupBy(["date(o.date_added)"])
        ->all();
        
        $dayActive = (new Query())
        ->select("stat_date, data")
        ->from('stat')
        ->where("stat_date >= '" . date("Ymd", strtotime($startTime)) . "' AND stat_date < ' " . date("Ymd", strtotime($endTime)) . " '")
        ->groupBy(["stat_date"])
        ->all();
        
        $dataDay = [];
        foreach ($dayActive as $k => $v) {
            $dataDay[mb_substr($v['stat_date'],0,4,'utf-8') .'-'. mb_substr($v['stat_date'],4,2,'utf-8') .'-'. mb_substr($v['stat_date'],6,2,'utf-8')] = json_decode($v['data'], true)['d']['g']['0'];
        }
        
        $orderStatusPay = [];
        $orderStatusPay = (new Query())
        ->select("count(*) as order_pay_nums, date_added")
        ->from('order')
        ->where("date_added >= '{$startTime}' AND date_added < '{$endTime}' AND order_status_id = 2")
        ->groupBy(["date(date_added)"])
        ->all();
                
        $dataPay = [];
        foreach ($orderStatusPay as $k => $v) {
            $dataPay[explode(' ', $v['date_added'])[0]] = $v['order_pay_nums'];
        }
        $order = [];
        $order = (new Query())
        ->select("o.order_id, o.order_number, o.total, o.shipping_firstname, o.date_added, o.comment, c.lastname, o.telephone")
        ->from(HI\TableName\ORDER ." as o")
        ->leftJoin(HI\TableName\CUSTOMER ." as c", "o.customer_id = c.customer_id")
        ->where("o.order_status_id = 2")
        ->orderBy(["order_id" => SORT_DESC])
        ->all();
        
        $orders = [];
        $this->load->model('report/accounting');
        $cost_logs = $this->model_report_accounting->getCost(date("Ymd", strtotime($startTime)), date("Ymd", strtotime($endTime)));
        foreach ($cost_logs as $cost_log) {
	        @$products[$cost_log['stat_date']][$cost_log['product_id']][$cost_log['origin']]['cost'] += $cost_log['cost'];
	        @$products[$cost_log['stat_date']][$cost_log['product_id']][$cost_log['origin']]['quantity'] += $cost_log['quantity'];
	        @$products[$cost_log['stat_date']][$cost_log['product_id']][$cost_log['origin']]['number'] += $cost_log['number'];
	    
	        $orders[$cost_log['stat_date']][$cost_log['order_child_id']]['origin'] =  $cost_log['origin'];
	        $orders[$cost_log['stat_date']][$cost_log['order_child_id']]['shipping'] =  $cost_log['shipping'];
	        $orders[$cost_log['stat_date']][$cost_log['order_child_id']]['product'][] =  $cost_log;
	        $order_count[$cost_log['stat_date']][$cost_log['origin']][$cost_log['order_id']] = 1;
	    }
	    $express = [];
	    foreach ($orders as $date => $order_child) {
	        foreach ($order_child as $order_child_item) {
	            $num = 0;
	            foreach ($order_child_item['product'] as $order_product) {
	                if ($order_child_item['origin'] == 1365) {
	                    if (!in_array($order_product['product_id'], [HI\Config\Product\FIVE_INCH, HI\Config\Product\SIX_INCH, HI\Config\Product\PICTURE_ALBUM, HI\Config\Product\LOMO_CARDS_PRODUCT_ID, HI\Config\Product\PHOTO_CARDS_PRODUCT_ID])) {
	                        $num += 1;
	                    }
	                } else {
	                    $price = $order_child_item['shipping'];
	                }
	            }
	            if ($order_child_item['origin'] == 1365) {
	                if ($num > 0) {
	                    $price = 8 * $num;
	                } else {
	                    $price = 6;
	                }
	            }
	            @$express[$date] += $price;
	        }
	    }
	    array_walk($datas, function (&$values, $key) use ($express) {
	        $expressKey = date('Ymd', strtotime($values['date_added']));
	        $values['day_cost'] += $express[$expressKey];     
	    });
        if (isset($this->request->get['page'])) {
            $page = $this->request->get['page'];
        } else {
            $page = 1;
        }
        $url = '';
        $data = [];
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');
        $data['breadcrumbs'] = array();
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL')
        );
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('sale/hz_order', 'token=' . $this->session->data['token'] . $url, 'SSL')
        );
        $this->document->setTitle($this->language->get('heading_title'));
        $data['heading_title'] = $this->language->get('heading_title');
        $data['text_no_results'] = $this->language->get('text_no_results');
        $arr = [];
        $data['list'] = $datas;
        $data['orderStatusPay'] = $dataPay;
        $data['dayActive'] = $dataDay;
        $data['order'] = $order;
        $pagination = new Pagination();
        $pagination->total = count($datas);
        $pagination->page = $page;
        $pagination->limit = $this->config->get('config_limit_admin');
        $pagination->url = $this->url->link('sale/hz_order', 'token=' . $this->session->data['token'] . $url . '&page={page}', 'SSL');
        $data['results'] = sprintf($this->language->get('text_pagination'), (count($datas)) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > (count($datas) - $this->config->get('config_limit_admin'))) ? count($datas) : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), count($datas), ceil(count($datas) / $this->config->get('config_limit_admin')));
        $data['pagination'] = $pagination->render();
        $this->response->setOutput($this->load->view('statistic/sale.tpl', $data));
    }
    
    public function orderCommentAdd()
    {
        if (($this->request->server['REQUEST_METHOD'] == 'GET')) {
            $this->db->query("update " . DB_PREFIX . "`order` set `comment`='" . $this->request->get['comment'] . "' WHERE order_number='" . $this->request->get['order_number'] . "'");
            $this->db->getLastId();;
            return "";
        }
    }
    public function coupon()
    {
        /* 渠道名称 */
        if(!empty($this->request->post['filter_channel_name'])) {
            $flag = 1;
            $conditionChannel = " channel like '{$this->request->post['filter_channel_name']}%' and ";
        } else {
            $conditionChannel = '';
        }
        /* 优惠劵名称 */
        if(!empty($this->request->post['filter_coupon_name'])) {
            $flag = 2;
            $conditionName = " name like '{$this->request->post['filter_coupon_name']}%' and ";
        } else {
            $conditionName = '';
        }
        /* 创建日期 */
        if(!empty($this->request->post['filter_start_date'])) {
            $flag = 3;
            $conditionDate = " date_added like '{$this->request->post['filter_start_date']}%' and ";
        } else {
            /* 测试用 */
//             $flag = true;
//             $conditionDate = " date_added > '2016-05-25' and ";
            $conditionDate = '';
        }
        if (isset($flag)) {
            $conditionsLogged = $conditionChannel . $conditionName . $conditionDate . " logged in (0,2,4) and status=1";
        } else {
            $conditionsLogged = " logged in (0,2,4) and status=1";
        }
        if (isset($this->request->get['sort'])) {
            $sort = $this->request->get['sort'];
        } else {
            $sort = " date_added ";
        }
        if (isset($this->request->get['page'])) {
            $page = $this->request->get['page'];
        } else {
            $page = 1;
        }
        if (isset($this->request->get['order'])) {
            $order = $this->request->get['order'];
        } else {
            $order = ' DESC ';
        }

        if (isset($this->request->get['order']) && ($this->request->get['order'] == 'DESC')) {
            $status = " ASC ";
        } else {
            $status = " DESC ";
        }
        $start = ($page - 1) * $this->config->get('config_limit_admin') . ',' . $this->config->get('config_limit_admin');
        $connection = Yii::$app->db;
        $sql = "select nums, coupon_id, name, date_added, channel, logged from coupon where $conditionsLogged order by $sort $status limit $start";
        $sql2 = "select * from coupon where $conditionsLogged order by $sort";
        $query = $connection->createCommand($sql)->queryAll();
        $query2 = $connection->createCommand($sql2)->queryAll();
        $receive  = 0;
        $used = 0;
        $free = 0;
        $pay = 0;
        $count = 0;
        $cost = 0;
        $costLogToal = 0;
        foreach ($query as $k => &$value) {
            $subCoupon = $this->queryCouponId($value['coupon_id']);
            if ($subCoupon) {
                foreach ($subCoupon as $m => $v) {
                    @$receive += $this->queryCouponCollectTimes($v['coupon_id'], 0); //领取
                    @$used += $this->queryCouponCollectTimes($v['coupon_id'], 1); //使用
                    @$free += $this->queryCouponOrder($v['coupon_id'], 1); //免费使用
                    @$pay += $this->queryCouponOrder($v['coupon_id'], 0); //付费
                    @$cost += $this->queryCouponAssocOrderCost($v['coupon_id']); //销售额
                    @$costLogToal += $this->queryCouponCostByLog($v['coupon_id']); //成本
                    @$currentDayReceive += $this->queryCouponCollectTimes($v['coupon_id'], 2); //当天领取
                }
                $query[$k]['receive'] = $receive;
                unset($receive);
                $query[$k]['used'] = $used;
                unset($used);
                $query[$k]['free'] = $free;
                unset($free);
                $query[$k]['pay'] = $pay;
                unset($pay);
                $query[$k]['cost'] = $cost;
                unset($cost);
                $query[$k]['costLogToal'] = $costLogToal;
                unset($costLogToal);
                $query[$k]['currentDay_receive'] = $currentDayReceive;
                unset($currentDayReceive);
            } else {
                $query[$k]['receive'] = $this->queryCouponCollectTimes($value['coupon_id'], 0); //领取
                $query[$k]['used'] = $this->queryCouponCollectTimes($value['coupon_id'], 1); //已使用
                $query[$k]['free'] = $this->queryCouponOrder($value['coupon_id'], 1); //免费
                $query[$k]['pay'] = $this->queryCouponOrder($value['coupon_id'], 0); //付费;
                $query[$k]['cost'] = $this->queryCouponAssocOrderCost($value['coupon_id']); //销售额;
                $query[$k]['costLogToal'] = $this->queryCouponCostByLog($value['coupon_id']); //成本;
                $query[$k]['currentDay_receive'] = $this->queryCouponCollectTimes($value['coupon_id'], 2); //当天领取
            }
        }
        $url = '';

        if ($order == 'ASC') {
            $url .= '&order=DESC';
        } else {
            $url .= '&order=ASC';
        }
        $data = [];
        /* 搜索 */
        if (isset($this->request->post['filter_start_date'])) {
            $data['date'] = $this->request->post['filter_start_date'];
        } else {
            $data['date'] = '';
        }
        if (isset($this->request->post['filter_channel_name'])) {
            $data['channel_name'] = $this->request->post['filter_channel_name'];
        } else {
            $data['channel_name'] = '';
        }
        if (isset($this->request->post['filter_coupon_name'])) {
            $data['coupon_name'] = $this->request->post['filter_coupon_name'];
        } else {
            $data['coupon_name'] = '';
        }
        $data['order'] = $order;
        $data['sort_channel'] = $this->url->link('statistic/statistic/coupon', 'token=' . $this->session->data['token'] . '&sort=channel' . $url, 'SSL');
        $data['sort_name'] = $this->url->link('statistic/statistic/coupon', 'token=' . $this->session->data['token'] . '&sort=name' . $url, 'SSL');
        $data['sort_date'] = $this->url->link('statistic/statistic/coupon', 'token=' . $this->session->data['token'] . '&sort=date_added' . $url, 'SSL');
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');
        $data['breadcrumbs'] = array();
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL')
        );
        $data['breadcrumbs'][] = array(
            'text' => '渠道优惠劵',
            'href' => $this->url->link('statistic/coupon', 'token=' . $this->session->data['token'] . $url, 'SSL')
        );
        $this->document->setTitle($this->language->get('heading_title'));
        $data['heading_title'] = '渠道数据统计';
        $data['text_no_results'] = $this->language->get('text_no_results');
        $data['list'] = $query;
        $pagination = new Pagination();
        $pagination->total = count($query2);
        $pagination->page = $page;
        $pagination->limit = $this->config->get('config_limit_admin');
        $pagination->url = $this->url->link('statistic/statistic/coupon', 'token=' . $this->session->data['token'] . $url . '&page={page}', 'SSL');
        $data['results'] = sprintf($this->language->get('text_pagination'), (count($query2)) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > (count($query2) - $this->config->get('config_limit_admin'))) ? count($query2) : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), count($query2), ceil(count($query2) / $this->config->get('config_limit_admin')));
        $data['pagination'] = $pagination->render();
        $this->response->setOutput($this->load->view('statistic/coupon.tpl', $data));
    }
    
    /* 优惠劵类型 */
    public function queryCouponId($couponId)
    {
        return (new Query())->select('coupon_id')
            ->from('coupon')
            ->where(['payload' => $couponId, 'status' => 1])
            ->all();
    }
    /* 领取人数 */
    public function queryCouponCollectTimes($couponId, $type)
    {
        switch ($type) {
            case 0 : //总领取
                $condition = " c.coupon_id = " . $couponId . " and c.status = 1 ";
            break;
            case 1 : //已使用
                $condition = " c.coupon_id = " . $couponId . " and c.status = 1 and cc.type = 1 ";
            break;
            case 2 : //当天领取
                $date = date('Y-m-d');
                $condition = " c.coupon_id = " . $couponId . " and c.status = 1 and cc.date_added like '{$date}%' ";
            break;
            default :
        }
        return (new Query())->from('customer_coupon as cc ')
            ->leftjoin('coupon as c', ' cc.coupon_id = c.coupon_id')
            ->andWhere($condition)
            ->count();
    }

    /* 免费使用  */
    public function queryCouponOrder($couponId, $type)
    {
        $shipping = HI\Config\Coupon\ORDER_SHIPPING;
        if ($type) {
            $value = " o.total = " . $shipping . ' and o.order_status_id > 1 and o.order_status_id != 14 ';
        } else {
            $value = " o.total != " . $shipping . ' and o.order_status_id > 1 and o.order_status_id != 14 ';
        }
        return (new Query())->from(HI\TableName\ORDER_COUPON . ' as oc')
            ->leftJoin(HI\TableName\ORDER . ' as o', ' oc.order_id = o.order_id')
            ->where(['oc.coupon_id' => $couponId])
            ->andWhere($value)
            ->count();
    }
    
    /* 销售额 */
    public function queryCouponAssocOrderCost($couponId)
    {
        $query = (new Query())->select('sum(o.total) as total')
            ->from(HI\TableName\ORDER_COUPON . ' as oc')
            ->leftJoin(HI\TableName\ORDER . ' as o', ' oc.order_id = o.order_id')
            ->where(['oc.coupon_id' => $couponId])
            ->andWhere('o.order_status_id > 1 and o.order_status_id != 14')
            ->one()['total'];
        return empty($query) ? 0 : $query;
    }
    
    /* 成本 */
    public function queryCouponCostByLog($couponId)
    {
       $query = (new Query())->select('sum(cl.cost) as total')
            ->from(HI\TableName\ORDER_COUPON . ' as oc')
            ->leftJoin(HI\TableName\ORDER . ' as o', ' oc.order_id = o.order_id')
            ->leftJoin(HI\TableName\COST_LOG . ' as cl', 'cl.order_id = o.order_id')
            ->where(['oc.coupon_id' => $couponId])
            ->andWhere('o.order_status_id > 1 and o.order_status_id != 14')
            ->one()['total'];
       return empty($query) ? 0 : $query;
    }
}
?>