<?php
class ControllerReportAccounting extends Controller {
	private $error = array();
	
	public function index()
	{
	    $this->language->load('report/accounting');
	    $this->document->setTitle($this->language->get('heading_title'));
	    $this->load->model('report/accounting');
	    $this->getList();
	}
	
	protected function getList()
	{
	    $url = '';
	
	    $data['breadcrumbs'] = array();
	    $data['breadcrumbs'][] = array(
	            'text' => $this->language->get('text_home'),
	            'href' => $this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL')
	    );
	    $data['breadcrumbs'][] = array(
	            'text' => '成本核算',
	            'href' => $this->url->link('report/accounting', 'token=' . $this->session->data['token'] . $url, 'SSL')
	    );
	    
	    
	    $data['token'] = $this->session->data['token'];
	    if (isset($this->error['warning'])) {
	        $data['error_warning'] = $this->error['warning'];
	    } else {
	        $data['error_warning'] = '';
	    }
	    if (isset($this->session->data['success'])) {
	        $data['success'] = $this->session->data['success'];
	        unset($this->session->data['success']);
	    } else {
	        $data['success'] = '';
	    }
	    $url = '';
	    $data['header'] = $this->load->controller('common/header');
	    $data['column_left'] = $this->load->controller('common/column_left');
	    $data['footer'] = $this->load->controller('common/footer');
	    
// 	    $pagination = new Pagination();
// 	    $pagination->total = $product_total;
// 	    $pagination->page = $page;
// 	    $pagination->limit = $this->config->get('config_limit_admin');
// 	    $pagination->url = $this->url->link('report/accounting', 'token=' . $this->session->data['token'] . $url . '&page={page}', 'SSL');
	    
// 	    $data['pagination'] = $pagination->render();
	    $this->response->setOutput($this->load->view('report/accounting_list.tpl', $data));
	}
	
	public function getData ()
	{
	    $this->load->model('report/accounting');
	    $month = $this->request->get['month'];
	    if (@$month) {
	        $start_date = date('Ym01',strtotime($month.'-01'));
	    } else {
	        $start_date = date('Ym01', strtotime(date("Ymd")));
	    }
        $end_date =  date('Ymd', strtotime("$start_date +1 month -1 day"));
        $cost_logs = $this->model_report_accounting->getCost($start_date, $end_date);
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
	             @$express[$date][$order_child_item['origin']] += $price;
	         }
	    }
        $count = [];
        foreach ($order_count as $date => $order_origin) {
            foreach ($order_origin as $origin => $order_id) {
               @$count[$date][$origin] += array_sum($order_id);
            }
        }
        foreach ($count as $date => $origin_count) {
           $data['data'][$date]['order_count'] =  $origin_count;
           $data['data'][$date]['express_cost'] = $express[$date];
           $data['data'][$date]['products_cost'] = $products[$date];
        }
        $products = $this->model_report_accounting->getProduct();
        foreach ($products as $product) {
            $product_map[$product['product_id']] = ['model' => $product['model'], 'isbn' => $product['isbn']];
        }
        $data['product_map'] = $product_map;
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($data));
    }
}
