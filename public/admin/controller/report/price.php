<?php
class ControllerReportPrice extends Controller {
	private $error = array();
	
	public function index()
	{
	    $this->language->load('report/price');
	    $this->document->setTitle($this->language->get('heading_title'));
	    $this->load->model('report/price');
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
	            'text' => $this->language->get('heading_title'),
	            'href' => $this->url->link('report/price', 'token=' . $this->session->data['token'] . $url, 'SSL')
	    );
	    $data['add'] = $this->url->link('report/price/add', 'token=' . $this->session->data['token'] . $url, 'SSL');
	    $data['delete'] = $this->url->link('report/price/delete', 'token=' . $this->session->data['token'] . $url, 'SSL');
	    $products = $this->model_report_price->getProducts();
	    $productSd = [];
	    $productZj = [];
	    if ($products) {
	       //地理位置分类
	       foreach ($products as $product) {
	           if ($product['type'] == 0) {
	               $product['edit'] = $this->url->link('report/price/edit', 'token=' . $this->session->data['token'] . '&cost_id=' . $product['cost_id'] . $url, 'SSL');
	               $product['del'] = $this->url->link('report/price/del', 'token=' . $this->session->data['token'] . '&cost_id=' . $product['cost_id'] . $url, 'SSL');
	               $productSd[] = $product;
	           }
	           if ($product['type'] == 1) {
	               $product['edit'] = $this->url->link('report/price/edit', 'token=' . $this->session->data['token'] . '&cost_id=' . $product['cost_id'] . $url, 'SSL');
	               $product['del'] = $this->url->link('report/price/del', 'token=' . $this->session->data['token'] . '&cost_id=' . $product['cost_id'] . $url, 'SSL');
	               $productZj[] = $product;
	           }
	           
	       }
	    }
	    $data['products_sd'] = $productSd;
	    $data['products_zj'] = $productZj;
	    
	    $data['heading_title'] = $this->language->get('heading_title');
	
	    $data['text_list'] = $this->language->get('text_list');
	    $data['text_enabled'] = $this->language->get('text_enabled');
	    $data['text_disabled'] = $this->language->get('text_disabled');
	    $data['text_no_results'] = $this->language->get('text_no_results');
	    $data['text_confirm'] = $this->language->get('text_confirm');
	
	    $data['column_name'] = $this->language->get('column_name');
	    $data['column_model'] = $this->language->get('column_model');
	    $data['column_code'] = $this->language->get('column_code');
	    
	    $data['column_standard'] = $this->language->get('column_standard');
	    $data['column_print'] = $this->language->get('column_print');
	    $data['column_produce'] = $this->language->get('column_produce');
	    $data['column_overpack'] = $this->language->get('column_overpack');
	    $data['column_inner_pack'] = $this->language->get('column_inner_pack');
	    $data['column_seal_sticker'] = $this->language->get('column_seal_sticker');
	    $data['column_fitting'] = $this->language->get('column_fitting');
	    $data['column_total'] = $this->language->get('column_total');
	    $data['column_action'] = $this->language->get('column_action');
	    $data['button_copy'] = $this->language->get('button_copy');
	    $data['button_add'] = $this->language->get('button_add');
	    $data['button_edit'] = $this->language->get('button_edit');
	    $data['button_delete'] = $this->language->get('button_delete');
	    $data['button_filter'] = $this->language->get('button_filter');
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
	    $this->response->setOutput($this->load->view('report/price_list.tpl', $data));
	}
	
	public function edit()
	{
        $this->language->load('report/price');
        $this->document->setTitle($this->language->get('heading_title'));
        $this->load->model('report/price');
        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()){
            $this->model_report_price->editPrice($this->request->post['cost_id'], $this->request->post);
            $this->session->data['success'] = $this->language->get('text_success');
            $url = '';
            $this->response->redirect($this->url->link('report/price', 'token=' . $this->session->data['token'] . $url, 'SSL'));
	     }
        $this->getForm();
	}
	
	
	public function add()
	{
	    $this->language->load('report/price');
	    $this->document->setTitle($this->language->get('heading_title'));
	    $this->load->model('report/price');
	    if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()){
	        $this->model_report_price->addPrice($this->request->post);
	        $this->session->data['success'] = $this->language->get('text_success');
	        $url = '';
	        $this->response->redirect($this->url->link('report/price', 'token=' . $this->session->data['token'] . $url, 'SSL'));
	    }
	    $this->getForm();
	}
	
	protected function validateForm()
	{
	    if (!$this->user->hasPermission('modify', 'report/price')) {
	        $this->error['warning'] = $this->language->get('error_permission');
	    }
	    if ($this->error && !isset($this->error['warning'])) {
	        $this->error['warning'] = $this->language->get('error_warning');
	    }
	    return !$this->error;
	}
	
	protected function getForm() {
	    $data['heading_title'] = $this->language->get('heading_title');
	
	    $data['text_form'] = !isset($this->request->get['cost_id']) ? $this->language->get('text_add') : $this->language->get('text_edit');
	    $data['text_none'] = $this->language->get('text_none');
	    $data['text_default'] = $this->language->get('text_default');
	    $data['text_enabled'] = $this->language->get('text_enabled');
	    $data['text_disabled'] = $this->language->get('text_disabled');
	
	    $data['entry_name'] = $this->language->get('entry_name');
	    $data['entry_model'] = $this->language->get('entry_model');
	    $data['entry_code'] = $this->language->get('entry_code');
	    $data['entry_standard'] = $this->language->get('entry_standard');
	    $data['entry_print'] = $this->language->get('entry_print');
	    $data['entry_produce'] = $this->language->get('entry_produce');
	    $data['entry_overpack'] = $this->language->get('entry_overpack');
	    $data['entry_inner_pack'] = $this->language->get('entry_inner_pack');
	    $data['entry_seal_sticker'] = $this->language->get('entry_seal_sticker');
	    $data['entry_fitting'] = $this->language->get('entry_fitting');
	    $data['entry_total'] = $this->language->get('entry_total');
	
	    $data['button_save'] = $this->language->get('button_save');
	    $data['button_cancel'] = $this->language->get('button_cancel');
	
	    $data['tab_general'] = $this->language->get('tab_general');
	    $data['tab_data'] = $this->language->get('tab_data');
	    $data['tab_design'] = $this->language->get('tab_design');
	    if (isset($this->error['warning'])) {
	        $data['error_warning'] = $this->error['warning'];
	    } else {
	        $data['error_warning'] = '';
	    }
	
	    if (isset($this->error['name'])) {
	        $data['error_name'] = $this->error['name'];
	    } else {
	        $data['error_name'] = array();
	    }
	
	    if (isset($this->error['meta_title'])) {
	        $data['error_meta_title'] = $this->error['meta_title'];
	    } else {
	        $data['error_meta_title'] = array();
	    }
	
	    if (isset($this->error['keyword'])) {
	        $data['error_keyword'] = $this->error['keyword'];
	    } else {
	        $data['error_keyword'] = '';
	    }
	
	    $url = '';
	
	    $data['breadcrumbs'] = array();
	
	    $data['breadcrumbs'][] = array(
	            'text' => $this->language->get('text_home'),
	            'href' => $this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL')
	    );
	
	    $data['breadcrumbs'][] = array(
	            'text' => $this->language->get('heading_title'),
	            'href' => $this->url->link('report/price', 'token=' . $this->session->data['token'] . $url, 'SSL')
	    );
	
	    if (!isset($this->request->get['cost_id'])) {
	        $data['action'] = $this->url->link('report/price/add', 'token=' . $this->session->data['token'] . $url, 'SSL');
	    } else {
	        $data['action'] = $this->url->link('report/price/edit', 'token=' . $this->session->data['token'] . '&cost_id=' . $this->request->get['cost_id'] . $url, 'SSL');
	    }
	
	    $data['cancel'] = $this->url->link('report/price', 'token=' . $this->session->data['token'] . $url, 'SSL');
	    if (isset($this->request->get['cost_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
	        $cost_info = $this->model_report_price->getPrice($this->request->get['cost_id']);
	    }
	    $data['token'] = $this->session->data['token'];
	    $this->load->model('localisation/language');
	    $data['languages'] = $this->model_localisation_language->getLanguages();
	    
	    if (isset($this->request->post['product_id'])) {
	        $data['product_id'] = $this->request->post['product_id'];
	        $data['product_name'] = $this->request->post['product_name'];
	    } elseif (!empty($cost_info)) {
	        $data['product_id'] = $cost_info['product_id'];
	        $data['product_name'] = $cost_info['model'];
	    } else {
	        $data['product_id'] = '';
	        $data['product_name'] = '';
	    }
	    
	    if (isset($this->request->post['cost_id'])) {
	        $data['cost_id'] = $this->request->post['cost_id'];
	    } elseif (!empty($cost_info)) {
	        $data['cost_id'] = $cost_info['cost_id'];
	    } else {
	        $data['cost_id'] = '';
	    }
	    
	    if (isset($this->request->post['print'])) {
	        $data['print'] = $this->request->post['print'];
	    } elseif (!empty($cost_info)) {
	        $data['print'] = $cost_info['print'];
	    } else {
	        $data['print'] = '';
	    }
	    
	    if (isset($this->request->post['format'])) {
	        $data['format'] = $this->request->post['format'];
	    } elseif (!empty($cost_info)) {
	        $data['format'] = $cost_info['format'];
	    } else {
	        $data['format'] = '';
	    }
	    if (isset($this->request->post['produce'])) {
	        $data['produce'] = $this->request->post['produce'];
	    } elseif (!empty($cost_info)) {
	        $data['produce'] = $cost_info['produce'];
	    } else {
	        $data['produce'] = '';
	    }
	    
	    if (isset($this->request->post['overpack'])) {
	        $data['overpack'] = $this->request->post['overpack'];
	    } elseif (!empty($cost_info)) {
	        $data['overpack'] = $cost_info['overpack'];
	    } else {
	        $data['overpack'] = '';
	    }
	    
	    if (isset($this->request->post['overpack'])) {
	        $data['overpack'] = $this->request->post['overpack'];
	    } elseif (!empty($cost_info)) {
	        $data['overpack'] = $cost_info['overpack'];
	    } else {
	        $data['overpack'] = '';
	    }
	    
	    if (isset($this->request->post['inner_pack'])) {
	        $data['inner_pack'] = $this->request->post['inner_pack'];
	    } elseif (!empty($cost_info)) {
	        $data['inner_pack'] = $cost_info['inner_pack'];
	    } else {
	        $data['inner_pack'] = 0;
	    }
	    
	    if (isset($this->request->post['seal_sticker'])) {
	        $data['seal_sticker'] = $this->request->post['seal_sticker'];
	    } elseif (!empty($cost_info)) {
	        $data['seal_sticker'] = $cost_info['seal_sticker'];
	    } else {
	        $data['seal_sticker'] = 0;
	    }
	    
	    if (isset($this->request->post['fitting'])) {
	        $data['fitting'] = $this->request->post['fitting'];
	    } elseif (!empty($cost_info)) {
	        $data['fitting'] = $cost_info['fitting'];
	    } else {
	        $data['fitting'] = 0;
	    }
	    
	    if (isset($this->request->post['type'])) {
	        $data['type'] = $this->request->post['type'];
	    } elseif (!empty($cost_info)) {
	        $data['type'] = $cost_info['type'];
	    } else {
	        $data['type'] = 0;
	    }
	    
	    if (isset($this->request->post['total'])) {
	        $data['total'] = $this->request->post['total'];
	    } elseif (!empty($cost_info)) {
	        $data['total'] = $cost_info['total'];
	    } else {
	        $data['total'] = 0;
	    }
	    
	    $this->load->model('design/layout');
	    $data['layouts'] = $this->model_design_layout->getLayouts();
	    $data['header'] = $this->load->controller('common/header');
	    $data['column_left'] = $this->load->controller('common/column_left');
	    $data['footer'] = $this->load->controller('common/footer');
	
	    $this->response->setOutput($this->load->view('report/price_form.tpl', $data));
	}	
	
	
	public function del()
	{
	    $this->language->load('report/price');
	    $this->document->setTitle($this->language->get('heading_title'));
	    $this->load->model('report/price');
	    if (isset($this->request->get['cost_id']) && $this->validateDelete()) {
	        $this->model_report_price->deletePrice($this->request->get['cost_id']);
	        $this->session->data['success'] = $this->language->get('text_success');
	        $url = '';
	        $this->response->redirect($this->url->link('report/price', 'token=' . $this->session->data['token'] . $url, 'SSL'));
	    }
	    $this->getList();
	}
	
	protected function validateDelete() {
	    if (!$this->user->hasPermission('modify', 'report/price')) {
	        $this->error['warning'] = $this->language->get('error_permission');
	    }
	    return !$this->error;
	}
	
	
	public function autocomplete() {
	    $json = array();
	
	    $this->load->model('report/price');
	    
	    $json = $this->model_report_price->autocomplete();
	    $this->response->addHeader('Content-Type: application/json');
	    $this->response->setOutput(json_encode($json));
	}
	
	
	public function test()
	{
	    $json = array();
	    $this->load->model('report/price');
	    $json = $this->model_report_price->costStatistics(4095);
	    $this->response->addHeader('Content-Type: application/json');
	    $this->response->setOutput(json_encode($json));
	}
}