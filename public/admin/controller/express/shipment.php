<?php
class ControllerExpressShipment extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('express/shipment');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('express/shipment');

		$this->getList();
	}
	
	public function edit() {
	    $this->load->language('express/shipment');
	
	    $this->document->setTitle($this->language->get('heading_title'));
	
	    $this->load->model('express/shipment');
	
	    if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm($this->request->get['sid'])) {
	        $this->model_express_shipment->editShipment($this->request->get['sid'], $this->request->post);
	
	        $this->session->data['success'] = $this->language->get('text_success');
	
	        $url = '';
	          
	        if (isset($this->request->get['sort'])) {
	            $url .= '&sort=' . $this->request->get['sort'];
	        }
	        
	        if (isset($this->request->get['order'])) {
	            $url .= '&order=' . $this->request->get['order'];
	        }
	        
	        if (isset($this->request->get['page'])) {
	            $url .= '&page=' . $this->request->get['page'];
	        }
	
	        $this->response->redirect($this->url->link('express/shipment', 'token=' . $this->session->data['token'] . $url, 'SSL'));
	    }
	
	    $this->getForm();
	}
	
	public function add() {
	    $this->load->language('express/shipment');
	
	    $this->document->setTitle($this->language->get('heading_title'));
	
	    $this->load->model('express/shipment');
	
	    if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {

	        $this->session->data['success'] = $this->language->get('text_success');
	
	        $url = '';
	        
	        if (isset($this->request->get['sort'])) {
	            $url .= '&sort=' . $this->request->get['sort'];
	        }
	        
	        if (isset($this->request->get['order'])) {
	            $url .= '&order=' . $this->request->get['order'];
	        }
	        
	        if (isset($this->request->get['page'])) {
	            $url .= '&page=' . $this->request->get['page'];
	        }
	
	        $this->response->redirect($this->url->link('express/shipment', 'token=' . $this->session->data['token'] . $url, 'SSL'));
	    }
	
	    $this->getForm();
	}
	
	public function delete() {
	    $this->load->language('express/shipment');
	
	    $this->document->setTitle($this->language->get('heading_title'));
	
	    $this->load->model('express/shipment');
	    
	    if (isset($this->request->post['selected']) && $this->validateDelete()) {
	        
	        foreach ($this->request->post['selected'] as $sid) {
	            $this->model_express_shipment->deleteShipment($sid);
	        }
	
	        $this->session->data['success'] = $this->language->get('text_success');
	
	        $url = '';
	
	        if (isset($this->request->get['sort'])) {
	            $url .= '&sort=' . $this->request->get['sort'];
	        }
	
	        if (isset($this->request->get['order'])) {
	            $url .= '&order=' . $this->request->get['order'];
	        }
	
	        if (isset($this->request->get['page'])) {
	            $url .= '&page=' . $this->request->get['page'];
	        }
	
	        $this->response->redirect($this->url->link('express/shipment', 'token=' . $this->session->data['token'] . $url, 'SSL'));
	    }
	
	    $this->getList();
	}
	
	protected function validateDelete() {
	    if (!$this->user->hasPermission('modify', 'express/shipment')) {
	        $this->error['warning'] = $this->language->get('error_permission');
	    }
	    return !$this->error;
	}

	protected function getList() {
	    if (isset($this->request->get['sort'])) {
	        $sort = $this->request->get['sort'];
	    } else {
	        $sort = 'id';
	    }
	    
	    if (isset($this->request->get['order'])) {
	        $order = $this->request->get['order'];
	    } else {
	        $order = 'ASC';
	    }
	
	    if (isset($this->request->get['page'])) {
	        $page = $this->request->get['page'];
	    } else {
	        $page = 1;
	    }
	
	    $url = '';
	
	    if (isset($this->request->get['sort'])) {
	        $url .= '&sort=' . $this->request->get['sort'];
	    }
	    
	    if (isset($this->request->get['order'])) {
	        $url .= '&order=' . $this->request->get['order'];
	    }
	
	    if (isset($this->request->get['page'])) {
	        $url .= '&page=' . $this->request->get['page'];
	    }
	
	    $data['breadcrumbs'] = array();
	
	    $data['breadcrumbs'][] = array(
	        'text' => $this->language->get('text_home'),
	        'href' => $this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL')
	    );
	
	    $data['breadcrumbs'][] = array(
	        'text' => $this->language->get('heading_title'),
	        'href' => $this->url->link('express/shipment', 'token=' . $this->session->data['token'] . $url, 'SSL')
	    );
	
	    $data['add'] = $this->url->link('express/shipment/add', 'token=' . $this->session->data['token'] . $url, 'SSL');
	    $data['delete'] = $this->url->link('express/shipment/delete', 'token=' . $this->session->data['token'] . $url, 'SSL');
	
	    $data['coupons'] = array();
	
	    $filter_data = array(
	        'sort'  => $sort,
	        'order' => $order,
	        'start' => ($page - 1) * $this->config->get('config_limit_admin'),
	        'limit' => $this->config->get('config_limit_admin')
	    );
	
	    $shipment_total = $this->model_express_shipment->getTotalShipment();
	
	    $results = $this->model_express_shipment->getShipments($filter_data);
	    foreach ($results as $result) {
	        $data['shipment'][] = array(
	            'id'  => $result['id'],
	            'delivery_place' => Yii::$app->redis->hget(HI\User\CITY_CODE, $result['delivery_place']),
	            'receipt' => Yii::$app->redis->hget(HI\User\CITY_CODE, $result['receipt']),
	            'shipping' => $result['shipping'],
	            'edit'       => $this->url->link('express/shipment/edit', 'token=' . $this->session->data['token'] . '&sid=' . $result['id'] . $url, 'SSL')
	        );
	    }
	
	    $data['heading_title'] = $this->language->get('heading_title');
	
	    $data['text_list'] = $this->language->get('text_list');
	    $data['text_no_results'] = $this->language->get('text_no_results');
	    $data['text_confirm'] = $this->language->get('text_confirm');
	
	    $data['column_name'] = $this->language->get('column_name');
	    $data['column_code'] = $this->language->get('column_code');
	    $data['column_cost'] = $this->language->get('column_cost');
	    $data['column_action'] = $this->language->get('column_action');
	    $data['column_province'] = $this->language->get('column_province');
	    
	    $data['entry_momeny'] = $this->language->get('entry_momeny');
	    
	    $data['button_add'] = $this->language->get('button_add');
	    $data['button_edit'] = $this->language->get('button_edit');
	    $data['button_delete'] = $this->language->get('button_delete');
	
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
	
	    $url = '';
	    
	    if ($order == 'ASC') {
	        $url .= '&order=DESC';
	    } else {
	        $url .= '&order=ASC';
	    }
	
	    if (isset($this->request->get['page'])) {
	        $url .= '&page=' . $this->request->get['page'];
	    }
	
	    $data['sort_cost'] = $this->url->link('express/shipment', 'token=' . $this->session->data['token'] . '&sort=shipping' . $url, 'SSL');
	    $url = '';
	
	    if (isset($this->request->get['sort'])) {
	        $url .= '&sort=' . $this->request->get['sort'];
	    }
	    if (isset($this->request->get['order'])) {
	        $url .= '&order=' . $this->request->get['order'];
	    }
	
	
	    $pagination = new Pagination();
	    $pagination->total = $shipment_total;
	    $pagination->page = $page;
	    $pagination->limit = $this->config->get('config_limit_admin');
	    $pagination->url = $this->url->link('express/shipment', 'token=' . $this->session->data['token'] . $url . '&page={page}', 'SSL');
	
	    $data['pagination'] = $pagination->render();
	
	    $data['results'] = sprintf($this->language->get('text_pagination'), ($shipment_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($shipment_total - $this->config->get('config_limit_admin'))) ? $shipment_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $shipment_total, ceil($shipment_total / $this->config->get('config_limit_admin')));
	
	    $data['sort'] = $sort;
	    $data['order'] = $order;
	
	    $data['header'] = $this->load->controller('common/header');
	    $data['column_left'] = $this->load->controller('common/column_left');
	    $data['footer'] = $this->load->controller('common/footer');
	
	    $this->response->setOutput($this->load->view('express/shipment_list.tpl', $data));
	}
	
	
	protected function validateForm($id = null) {
	    if (!$this->user->hasPermission('modify', 'localisation/stock_warning')) {
	        $this->error['warning'] = $this->language->get('error_permission');
	        return !$this->error;
	    }

	    $data = $this->request->post;
	    if(empty($data['receipt']) || empty($data['shipping']) || empty($data['delivery_place']) ) {
	        $this->error['warning'] = $this->language->get('error_empty');
	        return !$this->error;
	    }
	    if (!$id) {
            if ($this->doQueryExists($data['receipt'], $data['delivery_place'])) {
                $this->error['warning'] = $this->language->get('error_empty');
                return !$this->error;
            }
	       $this->model_express_shipment->addShipment($this->request->post);
	    }
	    return !$this->error;
	}
	
	private function doQueryExists($receipt, $delivery_place)
	{
	    $sql = "select * from shipment where shipment.receipt = " . $receipt . " and shipment.delivery_place = " . $delivery_place;
	    $query = $this->db->query($sql);
	    return $query->row;
	}
	
	protected function getForm() {
	    $data['heading_title'] = $this->language->get('heading_title');
	
	    $data['text_form'] = !isset($this->request->get['sid']) ? $this->language->get('text_add') : $this->language->get('text_edit');
	
	    $data['column_name'] = $this->language->get('column_name');
	    $data['column_code'] = $this->language->get('column_code');
	    $data['column_cost'] = $this->language->get('column_cost');
	    $data['column_action'] = $this->language->get('column_action');
	    $data['column_province'] = $this->language->get('column_province');
	    $data['entry_province'] = $this->language->get('entry_province');
	
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
	        'href' => $this->url->link('common/dashboard', 'token=' . $this->  session->data['token'], 'SSL')
	    );
	
	    $data['breadcrumbs'][] = array(
	        'text' => $this->language->get('heading_title'),
	        'href' => $this->url->link('express/shipment', 'token=' . $this->session->data['token'] . $url, 'SSL')
	    );
	
	    if (!isset($this->request->get['sid'])) {
	        $data['action'] = $this->url->link('express/shipment/add', 'token=' . $this->session->data['token'] . $url, 'SSL');
	    } else {
	        $data['action'] = $this->url->link('express/shipment/edit', 'token=' . $this->session->data['token'] . '&sid=' . $this->request->get['sid'] . $url, 'SSL');
	    }
	
	    $data['cancel'] = $this->url->link('express/shipment', 'token=' . $this->session->data['token'] . $url, 'SSL');
	
	    $this->load->model('localisation/language');
	    
	    if (isset($this->request->post['sid'])) {
	        $shipment = $this->request->post['sid'];
	    } elseif (isset($this->request->get['sid'])) {
	        $shipment = $this->model_express_shipment->getShipment($this->request->get['sid']);
	    } else {
	        $shipment = array();
	    }
	    if (isset($this->request->post['delivery_place'])) {
	        $data['delivery_place'] = $this->request->post['delivery_place'];
	    } elseif (isset($this->request->get['sid'])) {
	        $data['delivery_place'] = Yii::$app->redis->hget(HI\User\CITY_CODE, $shipment['delivery_place']);
	    } else {
	        $data['delivery_place'] = '';
	    }
	    
	    if (isset($this->request->post['shipping'])) {
	        $data['shipping'] = $this->request->post['shipping'];
	    } elseif (isset($this->request->get['sid'])) {
	        $data['shipping'] = $shipment['shipping'];
	    } else {
	        $data['shipping'] = '';
	    }

	    if (isset($this->request->post['receipt'])) {
	        $data['receipt'] = $this->request->post['receipt'];
	    } elseif (isset($this->request->get['sid'])) {
	        $data['receipt'] = Yii::$app->redis->hget(HI\User\CITY_CODE, $shipment['receipt']);
	    } else {
	        $data['receipt'] = '';
	    }
	    $data['address_list'] = $this->model_express_shipment->getAddressList();
	    $data['header'] = $this->load->controller('common/header');
	    $data['column_left'] = $this->load->controller('common/column_left');
	    $data['footer'] = $this->load->controller('common/footer');
	    $this->response->setOutput($this->load->view('express/shipment_form.tpl', $data));
	}
}