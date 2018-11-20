<?php
class ControllerDashboardStockWarning extends Controller {
	public function index() {
		$this->load->language('dashboard/stock_warning');

		$data['heading_title'] = $this->language->get('heading_title');

		$data['text_view'] = $this->language->get('text_view');
		
		$data['warning_value'] = $this->language->get('warning_value');

		$data['token'] = $this->session->data['token'];

		// Total Orders
		$this->load->model('report/product');

		// Customers Online
		$data['total'] = $this->model_report_product->getStockWarningTotal();
		$data['v'] = $this->model_report_product->getStockWarningValue();
		if(!$data['total']) {
		    $data['total'] = 0;
		}
		$params = [
		    'token' => $this->session->data['token'],
		    'sort' => 'p.quantity',
		    'order' => 'ASC',
		];
		
		$data['online'] = $this->url->link('catalog/product', $params, 'SSL');

		return $this->load->view('dashboard/stock_warning.tpl', $data);
	}
}