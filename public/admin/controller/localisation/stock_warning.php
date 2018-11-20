<?php
class ControllerLocalisationStockWarning extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('localisation/stock_warning');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('localisation/stock_warning');

		$this->getList();
	}
	
	public function add() {
	    $this->load->language('localisation/stock_warning');
	
	    $this->document->setTitle($this->language->get('heading_title'));
	
	    $this->load->model('localisation/stock_warning');
	
	    if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
	        $this->model_localisation_stock_warning->addStockWarning($this->request->post);

	
	        $this->session->data['success'] = $this->language->get('text_success');
	
	        $url = '';
	
	        $this->response->redirect($this->url->link('localisation/stock_warning', 'token=' . $this->session->data['token'] . $url, 'SSL'));
	    }
	
	    $this->getForm();
	}
	
	public function delete() {
	    $this->load->language('localisation/stock_warning');
	
	    $this->document->setTitle($this->language->get('heading_title'));
	
	    $this->load->model('localisation/stock_warning');
	
	    if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
	        $this->model_localisation_stock_warning->deleteStockWarning($this->request->POST['selected']);
	
	        $this->session->data['success'] = $this->language->get('text_success');
	
	        $url = '';
	
	        $this->response->redirect($this->url->link('localisation/stock_warning', 'token=' . $this->session->data['token'] . $url, 'SSL'));
	    }
	
	    $this->getForm();
	}
	
	public function edit() {
	    $this->load->language('localisation/stock_warning');
	
	    $this->document->setTitle($this->language->get('heading_title'));
	
	    $this->load->model('localisation/stock_warning');
	
	    if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm($this->request->get['stock_warning_id'])) {
	        $this->model_localisation_stock_warning->editStockWarning($this->request->get['stock_warning_id'], $this->request->post);
	
	        $this->session->data['success'] = $this->language->get('text_success');
	
	        $url = '';
	
	        $this->response->redirect($this->url->link('localisation/stock_warning', 'token=' . $this->session->data['token'] . $url, 'SSL'));
	    }
	
	    $this->getForm();
	}
	
	protected function getList() {
	
	    $data['breadcrumbs'] = array();
	
	    $data['breadcrumbs'][] = array(
	        'text' => $this->language->get('text_home'),
	        'href' => $this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL')
	    );
	    $url = '';
	    $data['breadcrumbs'][] = array(
	        'text' => $this->language->get('heading_title'),
	        'href' => $this->url->link('localisation/stock_warning', 'token=' . $this->session->data['token'] . $url, 'SSL')
	    );
	    
	    $data['add'] = $this->url->link('localisation/stock_warning/add', 'token=' . $this->session->data['token'] . $url, 'SSL');
	    $data['delete'] = $this->url->link('localisation/stock_warning/delete', 'token=' . $this->session->data['token'] . $url, 'SSL');
	    $data['delete'] = $this->url->link('localisation/stock_warning/delete', 'token=' . $this->session->data['token'] . $url, 'SSL');
	
	    $data['heading_title'] = $this->language->get('heading_title');
	
	    $data['text_list'] = $this->language->get('text_list');
	    $data['text_no_results'] = $this->language->get('text_no_results');
	    $data['text_confirm'] = $this->language->get('text_confirm');
	    $data['text_value'] = $this->language->get('text_value');
	
	    $data['column_name'] = $this->language->get('column_name');
	    $data['column_action'] = $this->language->get('column_action');
	
	    $data['button_edit'] = $this->language->get('button_edit');
	    $data['button_add'] = $this->language->get('button_add');
	    $data['button_delete'] = $this->language->get('button_delete');
	    
	    $data['warning_value'] = $this->model_localisation_stock_warning->getStockWarning();
	    $data['stock_warning_id'] = $this->model_localisation_stock_warning->getStockWarningId();
	
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
	
	    if (isset($this->request->post['selected'])) {
	        $data['selected'] = (array)$this->request->post['selected'];
	    } else {
	        $data['selected'] = array();
	    }
	    
	    $data['header'] = $this->load->controller('common/header');
	    $data['column_left'] = $this->load->controller('common/column_left');
	    $data['footer'] = $this->load->controller('common/footer');
	    $result['stock_status_id'] = 404;
	    $data['edit'] = $this->url->link('localisation/stock_warning/edit', 'token=' . $this->session->data['token'] . '&stock_warning_id=' . $result['stock_status_id'] . $url, 'SSL');
	
	    $this->response->setOutput($this->load->view('localisation/stock_warning_list.tpl', $data));
	}
	
	protected function validateForm($id = null) {
	    if (!$this->user->hasPermission('modify', 'localisation/stock_warning')) {
	        $this->error['warning'] = $this->language->get('error_permission');
	    }
	    if(!$id) {
    	    if(!$this->model_localisation_stock_warning->addStockWarning($this->request->post)) {
    	        $this->error['warning'] = $this->language->get('error_name');
    	    }
	    }
	    return !$this->error;
	}
	
	protected function getForm() {
	    $data['heading_title'] = $this->language->get('heading_title');
	
	    $data['text_form'] = !isset($this->request->get['stock_warning_id']) ? $this->language->get('text_add') : $this->language->get('text_edit');
	
	    $data['entry_name'] = $this->language->get('entry_name');
	
	    $data['button_save'] = $this->language->get('button_save');
	    $data['button_cancel'] = $this->language->get('button_cancel');
	
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
	
	    $url = '';
	
	    $data['breadcrumbs'] = array();
	
	    $data['breadcrumbs'][] = array(
	        'text' => $this->language->get('text_home'),
	        'href' => $this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL')
	    );
	
	    $data['breadcrumbs'][] = array(
	        'text' => $this->language->get('heading_title'),
	        'href' => $this->url->link('localisation/stock_warning', 'token=' . $this->session->data['token'] . $url, 'SSL')
	    );
	
	    if (!isset($this->request->get['stock_warning_id'])) {
	        $data['action'] = $this->url->link('localisation/stock_warning/add', 'token=' . $this->session->data['token'] . $url, 'SSL');
	    } else {
	        $data['action'] = $this->url->link('localisation/stock_warning/edit', 'token=' . $this->session->data['token'] . '&stock_warning_id=' . $this->request->get['stock_warning_id'] . $url, 'SSL');
	    }
	
	    $data['cancel'] = $this->url->link('localisation/stock_warning', 'token=' . $this->session->data['token'] . $url, 'SSL');
	
	    $this->load->model('localisation/language');
	
	    if (isset($this->request->post['stock_warning_id'])) {
	        $data['stock_warning_id'] = $this->request->post['stock_warning_id'];
	    } elseif (isset($this->request->get['stock_warning_id'])) {
	        $data['stock_warning_id'] = $this->model_localisation_stock_warning->getStockWarning($this->request->get['stock_warning_id']);
	    } else {
	        $data['stock_warning_id'] = array();
	    }
	
	    $data['header'] = $this->load->controller('common/header');
	    $data['column_left'] = $this->load->controller('common/column_left');
	    $data['footer'] = $this->load->controller('common/footer');
	    $this->response->setOutput($this->load->view('localisation/stock_warning_form.tpl', $data));
	}
}