<?php
class ControllerMarketingCoupon extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('marketing/coupon');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('marketing/coupon');

		$this->getList();
	}

	public function add() {
		$this->load->language('marketing/coupon');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('marketing/coupon');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			$this->model_marketing_coupon->addCoupon($this->request->post);

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

			$this->response->redirect($this->url->link('marketing/coupon', 'token=' . $this->session->data['token'] . $url, 'SSL'));
		}
		$this->getForm();
	}

	public function edit() {
		$this->load->language('marketing/coupon');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('marketing/coupon');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			$this->model_marketing_coupon->editCoupon($this->request->get['coupon_id'], $this->request->post);

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

			$this->response->redirect($this->url->link('marketing/coupon', 'token=' . $this->session->data['token'] . $url, 'SSL'));
		}
		$this->getForm();
	}

	public function delete() {
		$this->load->language('marketing/coupon');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('marketing/coupon');

		if (isset($this->request->post['selected']) && $this->validateDelete()) {
			foreach ($this->request->post['selected'] as $coupon_id) {
				$this->model_marketing_coupon->deleteCoupon($coupon_id);
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

			$this->response->redirect($this->url->link('marketing/coupon', 'token=' . $this->session->data['token'] . $url, 'SSL'));
		}

		$this->getList();
	}

	protected function getList() {
		if (isset($this->request->get['sort'])) {
			$sort = $this->request->get['sort'];
		} else {
			$sort = 'name';
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
			'href' => $this->url->link('marketing/coupon', 'token=' . $this->session->data['token'] . $url, 'SSL')
		);

		$data['add'] = $this->url->link('marketing/coupon/add', 'token=' . $this->session->data['token'] . $url, 'SSL');
		$data['delete'] = $this->url->link('marketing/coupon/delete', 'token=' . $this->session->data['token'] . $url, 'SSL');

		$data['coupons'] = array();

		$filter_data = array(
			'sort'  => $sort,
			'order' => $order,
			'start' => ($page - 1) * $this->config->get('config_limit_admin'),
			'limit' => $this->config->get('config_limit_admin')
		);

		$coupon_total = $this->model_marketing_coupon->getTotalCoupons();

		$results = $this->model_marketing_coupon->getCoupons($filter_data);

		foreach ($results as $result) {
		    if($result['property'] == 1) {
		        $discount = $result['reduction'];
		    } elseif($result['property'] == 2) {
		        $discount = $result['reduction'];
		    } else {
		        $discount = $result['discount'];
		    }
		    if($result['logged']== 0) {
		         $currentNums = $this->model_marketing_coupon->doQueryCollectionTimes($result['coupon_id']);
		    } else {
		        $currentNums  = $this->doqueryCount($result['coupon_id']);
		    }
			$data['coupons'][] = array(
				'coupon_id'  => $result['coupon_id'],
				'name'       => $result['name'],
				'code'       => $result['code'],
			    'logged'     => $result['logged'],
			    'channel'    => $result['channel'],
			    'username'   => empty($result['uses_customer']) ? '' : $this->model_marketing_coupon->getUserName($result['uses_customer']),
			    'validity'   => $result['validity'],
			    'uses_customer' => $result['uses_customer'],
			    'use_type'   => $result['use_type'],
			    'use_start'  => $result['use_start'],
		        'use_end'    => $result['use_end'],
			    'city_code'  => $result['city_code'],
				'discount'   => $discount,
			    'property' => $result['property'],
			    'default_count' => $result['nums'],
			    'count' => $currentNums,
			    'type' => $result['type'],
				'date_start' => date($this->language->get('date_format_short'), strtotime($result['date_start'])),
				'date_end'   => date($this->language->get('date_format_short'), strtotime($result['date_end'])),
				'status'     => ($result['status'] ? $this->language->get('text_enabled') : $this->language->get('text_disabled')),
				'edit'       => $this->url->link('marketing/coupon/edit', 'token=' . $this->session->data['token'] . '&coupon_id=' . $result['coupon_id'] . $url, 'SSL')
			);
		}

		$data['heading_title'] = $this->language->get('heading_title');

		$data['text_list'] = $this->language->get('text_list');
		$data['text_no_results'] = $this->language->get('text_no_results');
		$data['text_confirm'] = $this->language->get('text_confirm');

		$data['column_name'] = $this->language->get('column_name');
		$data['column_code'] = $this->language->get('column_code');
		$data['column_discount'] = $this->language->get('column_discount');
		$data['column_date_start'] = $this->language->get('column_date_start');
		$data['column_date_end'] = $this->language->get('column_date_end');
		$data['column_status'] = $this->language->get('column_status');
		$data['column_action'] = $this->language->get('column_action');
		$data['column_count'] = $this->language->get('column_count');
        $data['help_sheets'] = $this->language->get('help_sheets');
        $data['help_number'] = $this->language->get('help_number');
        $data['help_momeny'] = $this->language->get('help_momeny');
        $data['help_percent'] = $this->language->get('help_percent');
        $data['column_channel'] = $this->language->get('column_channel');
        $data['help_channel'] = $this->language->get('help_channel');
        $data['help_days'] = $this->language->get('help_days');
        $data['help_unit'] = $this->language->get('help_unit');
        $data['help_no_start'] = $this->language->get('help_no_start');
        $data['entry_validity'] = $this->language->get('entry_validity');
        $data['entry_city_code'] = $this->language->get('entry_city_code');
        $data['help_city_shandong'] = $this->language->get('help_city_shandong');
        $data['help_city_hangzhou'] = $this->language->get('help_city_hangzhou');
        $data['help_city_none'] = $this->language->get('help_city_none');
        $data['entry_reviewer'] = $this->language->get('entry_reviewer');
        $data['entry_enable'] = $this->language->get('entry_enable');
        $data['entry_disabled'] = $this->language->get('entry_disabled');
        $data['entry_not_pass'] = $this->language->get('entry_not_pass');
        $data['column_scenes'] = $this->language->get('column_scenes');
        $data['text_returns'] = $this->language->get('text_returns');
        $data['text_normal'] = $this->language->get('text_normal');
        $data['text_share'] = $this->language->get('text_share');
        $data['entry_package_coupon'] = $this->language->get('entry_package_coupon');

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

		$data['sort_name'] = $this->url->link('marketing/coupon', 'token=' . $this->session->data['token'] . '&sort=name' . $url, 'SSL');
		$data['sort_validity'] = $this->url->link('marketing/coupon', 'token=' . $this->session->data['token'] . '&sort=validity' . $url, 'SSL');
		$data['sort_channel'] = $this->url->link('marketing/coupon', 'token=' . $this->session->data['token'] . '&sort=channel' . $url, 'SSL');
		$data['sort_code'] = $this->url->link('marketing/coupon', 'token=' . $this->session->data['token'] . '&sort=code' . $url, 'SSL');
		$data['sort_discount'] = $this->url->link('marketing/coupon', 'token=' . $this->session->data['token'] . '&sort=discount' . $url, 'SSL');
		$data['sort_date_start'] = $this->url->link('marketing/coupon', 'token=' . $this->session->data['token'] . '&sort=date_start' . $url, 'SSL');
		$data['sort_date_end'] = $this->url->link('marketing/coupon', 'token=' . $this->session->data['token'] . '&sort=date_end' . $url, 'SSL');
		$data['sort_status'] = $this->url->link('marketing/coupon', 'token=' . $this->session->data['token'] . '&sort=status' . $url, 'SSL');
		$data['sort_scenes'] = $this->url->link('marketing/coupon', 'token=' . $this->session->data['token'] . '&sort=logged' . $url, 'SSL');

		$url = '';

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		$pagination = new Pagination();
		$pagination->total = $coupon_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_limit_admin');
		$pagination->url = $this->url->link('marketing/coupon', 'token=' . $this->session->data['token'] . $url . '&page={page}', 'SSL');

		$data['pagination'] = $pagination->render();

		$data['results'] = sprintf($this->language->get('text_pagination'), ($coupon_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($coupon_total - $this->config->get('config_limit_admin'))) ? $coupon_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $coupon_total, ceil($coupon_total / $this->config->get('config_limit_admin')));

		$data['sort'] = $sort;
		$data['order'] = $order;

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('marketing/coupon_list.tpl', $data));
	}

	protected function getForm() {
		$data['heading_title'] = $this->language->get('heading_title');

		$data['text_form'] = !isset($this->request->get['coupon_id']) ? $this->language->get('text_add') : $this->language->get('text_edit');
		$data['text_enabled'] = $this->language->get('text_enabled');
		$data['text_disabled'] = $this->language->get('text_disabled');
		$data['text_yes'] = $this->language->get('text_yes');
		$data['text_no'] = $this->language->get('text_no');
		$data['text_percent'] = $this->language->get('text_percent');
		$data['text_amount'] = $this->language->get('text_amount');
		$data['text_prints'] = $this->language->get('text_prints');
		$data['text_quantity'] = $this->language->get('text_quantity');
		$data['text_validity'] = $this->language->get('text_validity');
		$data['text_define'] = $this->language->get('text_define');
		$data['text_returns'] = $this->language->get('text_returns');
		$data['text_normal'] = $this->language->get('text_normal');
		$data['column_scenes'] = $this->language->get('column_scenes');
		$data['column_user'] = $this->language->get('column_user');
		$data['text_share'] = $this->language->get('text_share');

		$data['entry_name'] = $this->language->get('entry_name');
		$data['entry_description'] = $this->language->get('entry_description');
		$data['entry_code'] = $this->language->get('entry_code');
		$data['entry_discount'] = $this->language->get('entry_discount');
		$data['entry_logged'] = $this->language->get('entry_logged');
		$data['entry_shipping'] = $this->language->get('entry_shipping');
		$data['entry_type'] = $this->language->get('entry_type');
		$data['entry_total'] = $this->language->get('entry_total');
		$data['entry_category'] = $this->language->get('entry_category');
		$data['entry_product'] = $this->language->get('entry_product');
		$data['entry_date_start'] = $this->language->get('entry_date_start');
		$data['entry_date_end'] = $this->language->get('entry_date_end');
		$data['entry_uses_total'] = $this->language->get('entry_uses_total');
		$data['entry_uses_customer'] = $this->language->get('entry_uses_customer');
		$data['entry_status'] = $this->language->get('entry_status');
		$data['entry_collection_times'] = $this->language->get('entry_collection_times');
		$data['entry_validity'] = $this->language->get('entry_validity');
		$data['entry_channel'] = $this->language->get('entry_channel');
		$data['entry_c_start'] = $this->language->get('entry_c_start');
		$data['entry_c_end'] = $this->language->get('entry_c_end');
		$data['entry_use_type'] = $this->language->get('entry_use_type');
		$data['entry_city_code'] = $this->language->get('entry_city_code');
		$data['entry_package_coupon'] = $this->language->get('entry_package_coupon');
		$data['total_amount'] = $this->language->get('total_amount');
		$data['min_value'] = $this->language->get('min_value');
		$data['max_value'] = $this->language->get('max_value');
		$data['entry_get_nums'] = $this->language->get('entry_get_nums');

		$data['help_code'] = $this->language->get('help_code');
		$data['help_type'] = $this->language->get('help_type');
		$data['help_logged'] = $this->language->get('help_logged');
		$data['help_total'] = $this->language->get('help_total');
		$data['help_category'] = $this->language->get('help_category');
		$data['help_product'] = $this->language->get('help_product');
		$data['help_uses_total'] = $this->language->get('help_uses_total');
		$data['help_uses_customer'] = $this->language->get('help_uses_customer');
		$data['help_collection_times'] = $this->language->get('help_collection_times');
		$data['help_channel'] = $this->language->get('help_channel');
		$data['help_city_code'] = $this->language->get('help_city_code');
		$data['help_max_value'] = $this->language->get('help_max_value');
		$data['help_total_amount'] = $this->language->get('help_total_amount');
		
		$data['button_save'] = $this->language->get('button_save');
		$data['button_cancel'] = $this->language->get('button_cancel');

		$data['tab_general'] = $this->language->get('tab_general');
		$data['tab_history'] = $this->language->get('tab_history');

		$data['token'] = $this->session->data['token'];

		if (isset($this->request->get['coupon_id'])) {
			$data['coupon_id'] = $this->request->get['coupon_id'];
		} else {
			$data['coupon_id'] = 0;
		}

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->error['name'])) {
			$data['error_name'] = $this->error['name'];
		} else {
			$data['error_name'] = '';
		}
		
		if (isset($this->error['discount'])) {
		    $data['error_discount'] = $this->error['discount'];
		} else {
		    $data['error_discount'] = '';
		}
		
		if (isset($this->error['error_max_value'])) {
		    $data['error_max_value'] = $this->error['error_max_value'];
		} else {
		    $data['error_max_value'] = '';
		}
		
		if (isset($this->error['user'])) {
		    $data['error_user'] = $this->error['user'];
		} else {
		    $data['error_user'] = '';
		}

		if (isset($this->error['error_total'])) {
		    $data['error_total'] = $this->error['error_total'];
		} else {
		    $data['error_total'] = '';
		}

		if (isset($this->error['error_total_amount'])) {
		    $data['error_total_amount'] = $this->error['error_total_amount'];
		} else {
		    $data['error_total_amount'] = '';
		}

		if (isset($this->error['coupon_product'])) {
		    $data['error_coupon_product'] = $this->error['coupon_product'];
		} else {
		    $data['error_coupon_product'] = '';
		}

		if (isset($this->error['code'])) {
			$data['error_code'] = $this->error['code'];
		} else {
			$data['error_code'] = '';
		}

		if (isset($this->error['date_start'])) {
			$data['error_date_start'] = $this->error['date_start'];
		} else {
			$data['error_date_start'] = '';
		}

		if (isset($this->error['date_end'])) {
			$data['error_date_end'] = $this->error['date_end'];
		} else {
			$data['error_date_end'] = '';
		}

		$url = '';

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('marketing/coupon', 'token=' . $this->session->data['token'] . $url, 'SSL')
		);

		if (!isset($this->request->get['coupon_id'])) {
			$data['action'] = $this->url->link('marketing/coupon/add', 'token=' . $this->session->data['token'] . $url, 'SSL');
		} else {
			$data['action'] = $this->url->link('marketing/coupon/edit', 'token=' . $this->session->data['token'] . '&coupon_id=' . $this->request->get['coupon_id'] . $url, 'SSL');
		}

		$data['cancel'] = $this->url->link('marketing/coupon', 'token=' . $this->session->data['token'] . $url, 'SSL');

		if (isset($this->request->get['coupon_id']) && (!$this->request->server['REQUEST_METHOD'] != 'POST')) {
			$coupon_info = $this->model_marketing_coupon->getCoupon($this->request->get['coupon_id']);
		}

		if (isset($this->request->post['name'])) {
			$data['name'] = $this->request->post['name'];
		} elseif (!empty($coupon_info)) {
			$data['name'] = $coupon_info['name'];
		} else {
			$data['name'] = '';
		}
		/* 渠道 */
		if (isset($this->request->post['channel'])) {
		    $data['channel'] = $this->request->post['channel'];
		} elseif (!empty($coupon_info)) {
		    $data['channel'] = $coupon_info['channel'];
		} else {
		    $data['channel'] = '';
		}
        /* 口令 */
		if (isset($this->request->post['code'])) {
			$data['code'] = $this->request->post['code'];
		} elseif (!empty($coupon_info)) {
			$data['code'] = $coupon_info['code'];
		} else {
			$data['code'] = '';
		}
        
		if (isset($this->request->post['type'])) {
			$data['type'] = $this->request->post['type'];
		} elseif (!empty($coupon_info)) {
			$data['type'] = $coupon_info['type'];
		} else {
			$data['type'] = '';
		}
		/* 对应发货地 */
		if (isset($this->request->post['city_code'])) {
		    $data['city_code'] = $this->request->post['city_code'];
		} elseif (!empty($coupon_info)) {
		    $data['city_code'] = $coupon_info['city_code'];
		} else {
		    $data['city_code'] = 2;
		}
		/* 1有效期 */
		if (isset($this->request->post['validity'])) {
		    $data['validity'] = $this->request->post['validity'];
		} elseif (!empty($coupon_info)) {
		    $data['validity'] = $coupon_info['validity'];
		} else {
		    $data['validity'] = '';
		}
		/* 优惠劵有效期类型 */
		if (isset($this->request->post['use_type'])) {
		    $data['use_type'] = $this->request->post['use_type'];
		} elseif (!empty($coupon_info)) {
		    $data['use_type'] = $coupon_info['use_type'];
		} else {
		    $data['use_type'] = '';
		}
        /* 优惠劵类型值 */
		if (isset($this->request->post['discount'])) {
			$data['discount'] = $this->request->post['discount'];
		} elseif (!empty($coupon_info)) {
		    if($data['type'] == 1 || $data['type'] == 2 ) {
			     $data['discount'] = $coupon_info['reduction'];
		    } else {
		        $data['discount'] = $coupon_info['discount'];
		    }
		} else {
			$data['discount'] = '';
		}
        /* 优惠劵使用场景 */
		if (isset($this->request->post['logged'])) {
			$data['logged'] = $this->request->post['logged'];
		} elseif (!empty($coupon_info)) {
			$data['logged'] = $coupon_info['logged'];
		} else {
			$data['logged'] = '';
		}
		
		/* 退换货绑定用户 */
		if (isset($this->request->post['blind_user'])) {
		    $data['blind_user'] = $this->request->post['blind_user'];
		} elseif (!empty($coupon_info)) {
		    if ($coupon_info['logged'] == 1) {
		      $data['blind_user'] = $this->model_marketing_coupon->getUserInfo($this->request->get['coupon_id']);
		    } else {
		      $data['blind_user'] = '';
		    }
		} else {
		    $data['blind_user'] = '';
		}
        
		if (isset($this->request->post['shipping'])) {
			$data['shipping'] = $this->request->post['shipping'];
		} elseif (!empty($coupon_info)) {
			$data['shipping'] = $coupon_info['shipping'];
		} else {
			$data['shipping'] = '';
		}

		if (isset($this->request->post['total'])) {
			$data['total'] = $this->request->post['total'];
		} elseif (!empty($coupon_info)) {
			$data['total'] = $coupon_info['total'];
		} else {
			$data['total'] = '';
		}
        /* 适用商品 */
		if (isset($this->request->post['coupon_product'])) {
			$products = $this->request->post['coupon_product'];
		} elseif (isset($this->request->get['coupon_id'])) {
			$products = $this->model_marketing_coupon->getCouponProducts($this->request->get['coupon_id']);
		} else {
			$products = array();
		}

		$this->load->model('catalog/product');

		$data['coupon_product'] = array();

		foreach ($products as $product_id) {
			$product_info = $this->model_catalog_product->getProduct($product_id, 1);

			if ($product_info) {
				$data['coupon_product'][] = array(
					'product_id' => $product_info['product_id'],
					'name'       => $product_info['name']
				);
			}
		}

		if (isset($this->request->post['coupon_category'])) {
			$categories = $this->request->post['coupon_category'];
		} elseif (isset($this->request->get['coupon_id'])) {
			$categories = $this->model_marketing_coupon->getCouponCategories($this->request->get['coupon_id']);
		} else {
			$categories = array();
		}

		$this->load->model('catalog/category');

		$data['coupon_category'] = array();

		foreach ($categories as $category_id) {
			$category_info = $this->model_catalog_category->getCategory($category_id);

			if ($category_info) {
				$data['coupon_category'][] = array(
					'category_id' => $category_info['category_id'],
					'name'        => ($category_info['path'] ? $category_info['path'] . ' &gt; ' : '') . $category_info['name']
				);
			}
		}

		if (isset($this->request->post['date_start'])) {
			$data['date_start'] = $this->request->post['date_start'];
		} elseif (!empty($coupon_info)) {
			$data['date_start'] = ($coupon_info['date_start'] != '0000-00-00' ? $coupon_info['date_start'] : '');
		} else {
			$data['date_start'] = date('Y-m-d', time());
		}
		
		if (isset($this->request->post['use_start'])) {
		    $data['use_start'] = $this->request->post['use_start'];
		} elseif (!empty($coupon_info)) {
		    $data['use_start'] = ($coupon_info['use_start'] != '0000-00-00' ? $coupon_info['use_start'] : '');
		} else {
		    $data['use_start'] = date('Y-m-d', strtotime('+1 month'));
		}
		
		if (isset($this->request->post['use_end'])) {
		    $data['use_end'] = $this->request->post['use_end'];
		} elseif (!empty($coupon_info)) {
		    $data['use_end'] = ($coupon_info['use_end'] != '0000-00-00' ? $coupon_info['use_end'] : '');
		} else {
		    $data['use_end'] = date('Y-m-d', strtotime('+2 month'));
		}

		if (isset($this->request->post['date_end'])) {
			$data['date_end'] = $this->request->post['date_end'];
		} elseif (!empty($coupon_info)) {
			$data['date_end'] = ($coupon_info['date_end'] != '0000-00-00' ? $coupon_info['date_end'] : '');
		} else {
			$data['date_end'] = date('Y-m-d', strtotime('+1 month'));
		}

		if (isset($this->request->post['uses_total'])) {
			$data['uses_total'] = $this->request->post['uses_total'];
		} elseif (!empty($coupon_info)) {
			$data['uses_total'] = $coupon_info['uses_total'];
		} else {
			$data['uses_total'] = 1;
		}
		
		if (isset($this->request->post['payload'])) {
		    $data['payload'] = $this->request->post['payload'];
		} elseif (!empty($coupon_info)) {
		    $data['payload'] = $coupon_info['payload'];
		} else {
		    $data['payload'] = 0;
		}
		
		if (isset($this->request->post['entry_total_amount'])) {
		    $data['entry_total_amount'] = $this->request->post['entry_total_amount'];
		} elseif (!empty($coupon_info)) {
		    if ($coupon_info['logged'] == 4) {
		      $data['entry_total_amount'] = json_decode($coupon_info['payload'], true)['amount'];
		    } else {
		        $data['entry_total_amount'] = '';
		    }
		} else {
		    $data['entry_total_amount'] = '';
		}
		
		if (isset($this->request->post['entry_max_value'])) {
		    $data['entry_max_value'] = $this->request->post['entry_max_value'];
		} elseif (!empty($coupon_info)) {
		    if ($coupon_info['logged'] == 4) {
		      $data['entry_max_value'] = json_decode($coupon_info['payload'], true)['value'];
		    } else {
		      $data['entry_max_value'] = '';
		    }
		} else {
		    $data['entry_max_value'] = '';
		}
		
		if (isset($this->request->post['nums'])) {
		    $data['nums'] = $this->request->post['nums'];
		} elseif (!empty($coupon_info)) {
		    $data['nums'] = $coupon_info['nums'];
		} else {
		    $data['nums'] = 0;
		}

		if (isset($this->request->post['uses_customer'])) {
			$data['uses_customer'] = $this->request->post['uses_customer'];
		} elseif (!empty($coupon_info)) {
			$data['uses_customer'] = $coupon_info['uses_customer'];
		} else {
			$data['uses_customer'] = '';
		}

		if (isset($this->request->post['status'])) {
			$data['status'] = $this->request->post['status'];
		} elseif (!empty($coupon_info)) {
			$data['status'] = $coupon_info['status'];
		} else {
			$data['status'] = 0;
		}

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('marketing/coupon_form.tpl', $data));
	}

	protected function validateForm() {
	    $rounte = substr(strrchr($_GET['route'], '/'), 1);
	    if ($rounte == 'edit') {
	        $groupId = $this->model_marketing_coupon->queryUserGroupId($this->session->data['user_id']);
	        if (!in_array($groupId, HI\Config\Console\PERMIT_EDIT)) {
	            $this->error['warning'] = $this->language->get('error_permission');
	        }
	    }
	    /* 检查权限问题 */
		if (!$this->user->hasPermission('modify', 'marketing/coupon')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}
        /* 合法名 */
		if ((utf8_strlen($this->request->post['name']) > 128)) {
			$this->error['name'] = $this->language->get('error_name');
		}
		if ($this->request->post['payload'] != 4) {
    		/* 折扣 */
    		if (empty($this->request->post['discount'])) {
    		    $this->error['discount'] = $this->language->get('error_discount');
    		}
		}
		/* 检查用户是否存在 */
	    if ($this->request->post['payload'] == 1) {
	        if(!$this->model_marketing_coupon->doQueryCustomerExist($this->request->post['blind_user'])) {
	            $this->error['user'] = $this->language->get('error_user');
	        }
	    } elseif ($this->request->post['payload'] == 4) {
	        /* 总金额为空 */
	        if (empty($this->request->post['entry_total_amount'])) {
	            $this->error['error_total_amount'] = $this->language->get('error_total_amount');
	        }
	        /* 最大值和最小为值为空 */
	        if (empty($this->request->post['entry_max_value'])) {
	            $this->error['error_max_value'] = $this->language->get('error_max_value');
	        }
	        $checkoutValue = explode(',', $this->request->post['entry_max_value']);
	        if ($checkoutValue[0] == $this->request->post['entry_max_value']) {
	            $this->error['error_max_value'] = $this->language->get('error_max_value');
	        } elseif($checkoutValue[0] >= $checkoutValue[1]) {
	            $this->error['error_max_value'] = $this->language->get('error_max_value');
	        } elseif ($this->request->post['nums'] * $checkoutValue[0] > $this->request->post['entry_total_amount']) {
	            $this->error['error_max_value'] = $this->language->get('error_max_value');
	        } elseif($this->request->post['nums'] * $checkoutValue[1] < $this->request->post['entry_total_amount']) {
	            $this->error['error_max_value'] = $this->language->get('error_max_value');
	        }
	    }
		
		if(empty($this->request->post['coupon_product'])) {
		    $this->error['coupon_product'] = $this->language->get('error_coupon_product');
		}
		if ($this->request->post['uses_total'] > 100 || $this->request->post['uses_total'] < 0 ) {
		    $this->error['error_total'] = $this->language->get('error_total');
		}

// 		if ((utf8_strlen($this->request->post['code']) < 3) || (utf8_strlen($this->request->post['code']) > 10)) {
// 			$this->error['code'] = $this->language->get('error_code');/* 这是验证code */
// 		}

		$coupon_info = $this->model_marketing_coupon->getCouponByCode($this->request->post['code']);
		if ($coupon_info) {
		    foreach ($coupon_info as $value) {
		        /* 不同渠道不能同时使用一个code */
		        if ($value['channel'] != $this->request->post['channel']) {
		            $this->error['warning'] = $this->language->get('error_exists');
		        }
// 		        $currentNums = $this->model_marketing_coupon->doQueryCollectionTimes($value['coupon_id']);
// 		        if ($value['nums']) {
// 		            if ($currentNums != $value['nums']) {
// 		                $this->error['warning'] = $this->language->get('error_exists');
// 		            }
// 		        } else {
// 		            $this->error['warning'] = $this->language->get('error_exists');
// 		        }
		    }
// 			if (!isset($this->request->get['coupon_id'])) {
// 			    $flag = true;
// 			} elseif ($value['coupon_id'] != $this->request->get['coupon_id']) {
// 				$flag = true;
// 			}/* 验证重复的code */

// 			if (isset($flag)) {
// 			    if ((strtotime($value['date_start']) <= strtotime(date('Y-m-d', time()))) && (strtotime($value['date_end']) >= strtotime(date('Y-m-d', time())))) {
// 			        if ($value['use_type'] == 0) {
// 			            if ((strtotime($value['use_start']) < strtotime(date('Y-m-d', time()))) &&  (strtotime($value['use_end']) > strtotime(date('Y-m-d', time())))) {
// 			                $this->error['warning'] = $this->language->get('error_exists');
// 			            }
// 			        } else {
// 			            $this->error['warning'] = $this->language->get('error_exists');
// 			        }
// 			    }
// 			    if (strtotime($value['date_start']) > strtotime(date('Y-m-d', time()))) {
// 			        if ($value['use_type'] == 0) {
// 			            if ((strtotime($value['use_start']) < strtotime(date('Y-m-d', time()))) &&  (strtotime($value['use_end']) > strtotime(date('Y-m-d', time())))) {
// 			                $this->error['warning'] = $this->language->get('error_exists');
// 			            }
// 			        } else {
// 			            $this->error['warning'] = $this->language->get('error_exists');
// 			        }
// 			    }
// 			}
		}
		return !$this->error;
	}

	protected function validateDelete() {
		if (!$this->user->hasPermission('modify', 'marketing/coupon')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}

	public function history() {
		$this->load->language('marketing/coupon');

		$this->load->model('marketing/coupon');

		$data['text_no_results'] = $this->language->get('text_no_results');

		$data['column_order_id'] = $this->language->get('column_order_id');
		$data['column_customer'] = $this->language->get('column_customer');
		$data['column_amount'] = $this->language->get('column_amount');
		$data['column_date_added'] = $this->language->get('column_date_added');

		if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else {
			$page = 1;
		}

		$data['histories'] = array();

		$results = $this->model_marketing_coupon->getCouponHistories($this->request->get['coupon_id'], ($page - 1) * 10, 10);

		foreach ($results as $result) {
			$data['histories'][] = array(
				'order_id'   => $result['order_id'],
				'customer'   => $result['customer'],
				'amount'     => $result['amount'],
				'date_added' => date($this->language->get('date_format_short'), strtotime($result['date_added']))
			);
		}

		$history_total = $this->model_marketing_coupon->getTotalCouponHistories($this->request->get['coupon_id']);

		$pagination = new Pagination();
		$pagination->total = $history_total;
		$pagination->page = $page;
		$pagination->limit = 10;
		$pagination->url = $this->url->link('marketing/coupon/history', 'token=' . $this->session->data['token'] . '&coupon_id=' . $this->request->get['coupon_id'] . '&page={page}', 'SSL');

		$data['pagination'] = $pagination->render();

		$data['results'] = sprintf($this->language->get('text_pagination'), ($history_total) ? (($page - 1) * 10) + 1 : 0, ((($page - 1) * 10) > ($history_total - 10)) ? $history_total : ((($page - 1) * 10) + 10), $history_total, ceil($history_total / 10));

		$this->response->setOutput($this->load->view('marketing/coupon_history.tpl', $data));
	}
	
	public function doqueryCount($couponId)
	{
	    $sql = "select count(*) as c from coupon where payload=" . $couponId;
	    $query = $this->db->query($sql);
	    if($query) {
	        return $query->row['c'];
	    }
	    return '';
	}
}