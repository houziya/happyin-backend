<?php
class ControllerReportAccountingSd extends Controller {
	private $error = array();
	
	public function index()
	{
	    $this->language->load('report/accounting_sd');
	    $this->document->setTitle($this->language->get('heading_title'));
	    $this->load->model('report/accounting_sd');
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
	            'text' => '山东成本核算',
	            'href' => $this->url->link('report/accounting_sd', 'token=' . $this->session->data['token'] . $url, 'SSL')
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
	    $this->response->setOutput($this->load->view('report/accounting_sd_list.tpl', $data));
	}
	
	public function getData ()
	{
	    $this->load->model('report/accounting');
	    $start_date = date('Ym01', strtotime(date("Ymd")));
	    $end_date =  date('Ymd', strtotime("$start_date +1 month -1 day"));
	    $cost_logs = $this->model_report_accounting->getCost($start_date, $end_date);
	    foreach ($cost_logs as $cost_log) {
	        if ($cost_log['origin'] == 1365) {
	            $products[$cost_log['stat_date']][$cost_log['product_id']]['cost'][] = $cost_log['cost'];
	            $products[$cost_log['stat_date']][$cost_log['product_id']]['quantity'][] = $cost_log['quantity'];
	            $products[$cost_log['stat_date']][$cost_log['product_id']]['number'][] = $cost_log['number'];
	             
	            $orders[$cost_log['stat_date']][$cost_log['order_child_id']]['origin'] =  $cost_log['origin'];
	            $orders[$cost_log['stat_date']][$cost_log['order_child_id']]['shipping'] =  $cost_log['shipping'];
	            $orders[$cost_log['stat_date']][$cost_log['order_child_id']]['product'][] =  $cost_log;
	            $order_count[$cost_log['stat_date']][$cost_log['order_id']] = 1;
	        }
	    }
	    $express = [];
	    foreach ($orders as $date => $order) {
	        foreach ($order as $order_products) {
	            foreach ($order_products['product'] as $order_product) {
                    if (!in_array($order_product['product_id'], [HI\Config\Product\FIVE_INCH, HI\Config\Product\SIX_INCH, HI\Config\Product\PICTURE_ALBUM])) {
                        $price = 8;
                    } else {
                        $price = 6;
                    }
	            }
	            $origin_cost = @$origin_cost + $price;
	        }
	        $data['data'][$date]['express_cost'] = $origin_cost;
	        foreach ($order_count[$date] as $origin => $item) {
	            $order_sum[$origin] = array_sum($item);
	        }
	        $data['data'][$date]['order_count'] = $order_sum;
           foreach ($products[$date] as $product_id => $product_info) {
               if (@$product_info[$order_products['origin']]) {
                   $products_cost[$product_id][$order_products['origin']]['cost'] = array_sum($product_info[$order_products['origin']]['cost']);
                   $products_cost[$product_id][$order_products['origin']]['quantity'] = array_sum($product_info[$order_products['origin']]['quantity']);
                   $products_cost[$product_id][$order_products['origin']]['number'] = array_sum($product_info[$order_products['origin']]['number']);
               }
           }
           $data['data'][$date]['products_cost'] = $products_cost;
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
