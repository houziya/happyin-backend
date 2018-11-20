<?php
use Yaf\Controller_Abstract;
use yii\db\Query;
class ControllerSaleHzOrder extends Controller {
	private $error = array();
	public function index() {
	    $this->load->language('sale/order');
	    
	    $this->document->setTitle($this->language->get('heading_title'));
        
	    $this->load->model('sale/order');
	    $this->load->model('user/user_group');
	    
	    $this->getList(["tpl" => "hz_order_list.tpl", "action" => "index"]);
	}
	
	/* 打单发货 */
	public function processing() {
	    $this->load->language('sale/order');
	     
	    $this->document->setTitle($this->language->get('heading_title'));
	    $this->load->model('sale/order');
	    $this->load->model('user/user_group');
	    $this->getList(["tpl" => "hz_order_processing.tpl", "action" => "processing"]);
	}
	
	public function info() {
	$this->load->model('sale/order');

		if (isset($this->request->get['order_id'])) {
			$order_id = $this->request->get['order_id'];
		} else {
			$order_id = 0;
		}

		$order_info = $this->model_sale_order->getHzOrder($order_id);

		if ($order_info) {
			$this->load->language('sale/order');

			$this->document->setTitle($this->language->get('heading_title'));

			$data['heading_title'] = $this->language->get('heading_title');

			$data['text_ip_add'] = sprintf($this->language->get('text_ip_add'), $this->request->server['REMOTE_ADDR']);
			$data['text_order_detail'] = $this->language->get('text_order_detail');
			$data['text_customer_detail'] = $this->language->get('text_customer_detail');
			$data['text_option'] = $this->language->get('text_option');
			$data['text_store'] = $this->language->get('text_store');
			$data['text_date_added'] = $this->language->get('text_date_added');
			$data['text_payment_method'] = $this->language->get('text_payment_method');
			$data['text_shipping_method'] = $this->language->get('text_shipping_method');
			$data['text_customer'] = $this->language->get('text_customer');
			$data['text_customer_group'] = $this->language->get('text_customer_group');
			$data['text_email'] = $this->language->get('text_email');
			$data['text_telephone'] = $this->language->get('text_telephone');
			$data['text_invoice'] = $this->language->get('text_invoice');
			$data['text_reward'] = $this->language->get('text_reward');
			$data['text_affiliate'] = $this->language->get('text_affiliate');
			$data['text_order'] = sprintf($this->language->get('text_order'), $this->request->get['order_id']);
			$data['text_payment_address'] = $this->language->get('text_payment_address');
			$data['text_shipping_address'] = $this->language->get('text_shipping_address');
			$data['text_comment'] = $this->language->get('text_comment');

			$data['text_account_custom_field'] = $this->language->get('text_account_custom_field');
			$data['text_payment_custom_field'] = $this->language->get('text_payment_custom_field');
			$data['text_shipping_custom_field'] = $this->language->get('text_shipping_custom_field');
			$data['text_browser'] = $this->language->get('text_browser');
			$data['text_ip'] = $this->language->get('text_ip');
			$data['text_forwarded_ip'] = $this->language->get('text_forwarded_ip');
			$data['text_user_agent'] = $this->language->get('text_user_agent');
			$data['text_accept_language'] = $this->language->get('text_accept_language');
			$data['text_history'] = $this->language->get('text_history');
			$data['text_history_add'] = $this->language->get('text_history_add');
			$data['text_loading'] = $this->language->get('text_loading');

			$data['column_product'] = $this->language->get('column_product');
			$data['column_model'] = $this->language->get('column_model');
			$data['column_quantity'] = $this->language->get('column_quantity');
			$data['column_price'] = $this->language->get('column_price');
			$data['column_total'] = $this->language->get('column_total');

			$data['entry_order_status'] = $this->language->get('entry_order_status');
			$data['entry_notify'] = $this->language->get('entry_notify');
			$data['entry_override'] = $this->language->get('entry_override');
			$data['entry_comment'] = $this->language->get('entry_comment');

			$data['help_override'] = $this->language->get('help_override');

			$data['button_invoice_print'] = $this->language->get('button_invoice_print');
			$data['button_shipping_print'] = $this->language->get('button_shipping_print');
			$data['button_edit'] = $this->language->get('button_edit');
			$data['button_cancel'] = $this->language->get('button_cancel');
			$data['button_generate'] = $this->language->get('button_generate');
			$data['button_reward_add'] = $this->language->get('button_reward_add');
			$data['button_reward_remove'] = $this->language->get('button_reward_remove');
			$data['button_commission_add'] = $this->language->get('button_commission_add');
			$data['button_commission_remove'] = $this->language->get('button_commission_remove');
			$data['button_history_add'] = $this->language->get('button_history_add');
			$data['button_ip_add'] = $this->language->get('button_ip_add');

			$data['tab_history'] = $this->language->get('tab_history');
			$data['tab_additional'] = $this->language->get('tab_additional');

			$data['token'] = $this->session->data['token'];

			$url = '';

			if (isset($this->request->get['filter_order_id'])) {
				$url .= '&filter_order_id=' . $this->request->get['filter_order_id'];
			}

			if (isset($this->request->get['filter_customer'])) {
				$url .= '&filter_customer=' . urlencode(html_entity_decode($this->request->get['filter_customer'], ENT_QUOTES, 'UTF-8'));
			}

			if (isset($this->request->get['filter_order_status'])) {
				$url .= '&filter_order_status=' . $this->request->get['filter_order_status'];
			}

			if (isset($this->request->get['filter_total'])) {
				$url .= '&filter_total=' . $this->request->get['filter_total'];
			}

			if (isset($this->request->get['filter_date_added'])) {
				$url .= '&filter_date_added=' . $this->request->get['filter_date_added'];
			}

			if (isset($this->request->get['filter_date_modified'])) {
				$url .= '&filter_date_modified=' . $this->request->get['filter_date_modified'];
			}

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
				'href' => $this->url->link('sale/hz_order', 'token=' . $this->session->data['token'] . $url, 'SSL')
			);

			$data['shipping'] = $this->url->link('sale/order/shipping', 'token=' . $this->session->data['token'] . '&order_id=' . (int)$this->request->get['order_id'], 'SSL');
			$data['invoice'] = $this->url->link('sale/order/invoice', 'token=' . $this->session->data['token'] . '&order_id=' . (int)$this->request->get['order_id'], 'SSL');
			$data['edit'] = $this->url->link('sale/order/edit', 'token=' . $this->session->data['token'] . '&order_id=' . (int)$this->request->get['order_id'], 'SSL');
			$data['cancel'] = $this->url->link('sale/order', 'token=' . $this->session->data['token'] . $url, 'SSL');

			$data['order_id'] = $this->request->get['order_id'];

			//$data['store_name'] = $order_info['store_name'];
			$data['order_number'] = $order_info['order_number'];
			//$data['store_url'] = $order_info['store_url'];
			$data['store_url'] = $_SERVER['HTTP_HOST'];

			$data['date_added'] = date($this->language->get('datetime_format'), strtotime($order_info['date_added']));

			$data['firstname'] = $order_info['firstname'];
			$data['lastname'] = $order_info['lastname'];

			if (isset($order_info['customer_id'])) {
				$data['customer'] = $this->url->link('customer/customer/edit', 'token=' . $this->session->data['token'] . '&customer_id=' . $order_info['customer_id'], 'SSL');
			} else {
				$data['customer'] = '';
			}

			$this->load->model('customer/customer_group');

			//$customer_group_info = $this->model_customer_customer_group->getCustomerGroup($order_info['customer_group_id']);

			if (isset($customer_group_info)) {
				$data['customer_group'] = $customer_group_info['name'];
			} else {
				$data['customer_group'] = '暂无分组';
			}

			$data['email'] = $order_info['email'];
			$data['telephone'] = $order_info['telephone'];

			$data['shipping_method'] = $order_info['shipping_method'];
			//$data['payment_method'] = $this->model_sale_order->getPaymentMethod($order_info['payment_method']);

			// Shipping Address
			if ($order_info['shipping_address_format']) {
				$format = $order_info['shipping_address_format'];
			} else {
				$format = '{country}' . "\n" . '{city} {postcode}' . "\n" . '{zone}' . "\n" . '{address_1}' . "\n" . '{address_2}';
			}

			$find = array(
				'{address_1}',
				'{address_2}',
				'{city}',
				'{postcode}',
				'{zone}',
				//'{zone_code}',
				'{country}'
			);

			$replace = array(
				'address_1' => $order_info['shipping_address_1'],
				'address_2' => $order_info['shipping_address_2'],
				'city'      => $order_info['shipping_city'],
				'postcode'  => $order_info['shipping_postcode'],
				'zone'      => $order_info['shipping_zone'],
				//'zone_code' => $order_info['shipping_zone_code'],
				'country'   => $order_info['shipping_country']
			);

			$data['shipping_id'] = $order_info['shipping_id'];
			$data['shipping_firstname'] = $order_info['shipping_firstname'];
			$data['shipping_address'] = str_replace(array("\r\n", "\r", "\n"), ' ', preg_replace(array("/\s\s+/", "/\r\r+/", "/\n\n+/"), ' ', trim(str_replace($find, $replace, $format))));

			// Uploaded files
			$this->load->model('tool/upload');

			$data['products'] = array();

			$products = $this->model_sale_order->getOrderProducts($this->request->get['order_id']);

			$order_total = 0;
			foreach ($products as $product) {
				$option_data = array();

				$options = $this->model_sale_order->getOrderOptions($this->request->get['order_id'], $product['order_product_id']);

				foreach ($options as $option) {
					if ($option['type'] != 'file') {
						$option_data[] = array(
							'name'  => $option['name'],
							'value' => $option['value'],
							'type'  => $option['type']
						);
					} else {
						$upload_info = $this->model_tool_upload->getUploadByCode($option['value']);

						if ($upload_info) {
							$option_data[] = array(
								'name'  => $option['name'],
								'value' => $upload_info['name'],
								'type'  => $option['type'],
								'href'  => $this->url->link('tool/upload/download', 'token=' . $this->session->data['token'] . '&code=' . $upload_info['code'], 'SSL')
							);
						}
					}
				}

				$data['products'][] = array(
					'order_product_id' => $product['order_product_id'],
					'product_id'       => $product['product_id'],
				    'location'       => Order::getProductLocation($product['location']),
					'name'    	 	   => $product['name'],
				    //'coupon_name'    	 	   => $product['coupon_name'],
				    //'coupon_price'    	 	   => $product['coupon_price'],
					'model'    		   => $product['model'],
					'option'   		   => $option_data,
					'quantity'		   => $product['quantity'],
				    'pay_total'    		   => $this->currency->format($product['pay_total'], $order_info['currency_code'] ? $order_info['currency_code'] : "RMB", $order_info['currency_value']),
				    'row_total'    		   => $this->currency->format($product['price'] * $product['quantity'], $order_info['currency_code'] ? $order_info['currency_code'] : "RMB", $order_info['currency_value']),
					'price'    		   => $this->currency->format($product['price'] + ($this->config->get('config_tax') ? $product['tax'] : 0), $order_info['currency_code'] ? $order_info['currency_code'] : "RMB", $order_info['currency_value']),
					'total'    		   => $this->currency->format($product['total'] + ($this->config->get('config_tax') ? ($product['tax'] * $product['quantity']) : 0), $order_info['currency_code'] ? $order_info['currency_code'] : "RMB", $order_info['currency_value']),
					'href'     		   => $this->url->link('catalog/product/edit', 'token=' . $this->session->data['token'] . '&product_id=' . $product['product_id'], 'SSL')
				);
				$order_total +=$product['total'];
			}

			$data['order_total'] = $order_total;
			
			$data['vouchers'] = array();

			$vouchers = $this->model_sale_order->getOrderVouchers($this->request->get['order_id']);

			foreach ($vouchers as $voucher) {
				$data['vouchers'][] = array(
					'description' => $voucher['description'],
					'amount'      => $this->currency->format($voucher['amount'], $order_info['currency_code'], $order_info['currency_value']),
					'href'        => $this->url->link('sale/voucher/edit', 'token=' . $this->session->data['token'] . '&voucher_id=' . $voucher['voucher_id'], 'SSL')
				);
			}

			$data['totals'] = array();

			$totals = $this->model_sale_order->getOrderTotals($this->request->get['order_id']);

			foreach ($totals as $total) {
				$data['totals'][] = array(
					'title' => $total['title'],
					'text'  => $this->currency->format($total['value'], $order_info['currency_code'], $order_info['currency_value']),
				);
			}

			$data['comment'] = nl2br($order_info['comment']);

			$this->load->model('customer/customer');

			$data['reward'] = $order_info['reward'];

			$data['reward_total'] = $this->model_customer_customer->getTotalCustomerRewardsByOrderId($this->request->get['order_id']);

			if ($order_info['affiliate_id']) {
				$data['affiliate'] = $this->url->link('marketing/affiliate/edit', 'token=' . $this->session->data['token'] . '&affiliate_id=' . $order_info['affiliate_id'], 'SSL');
			} else {
				$data['affiliate'] = '';
			}

			$data['commission'] = $this->currency->format($order_info['commission'], $order_info['currency_code'], $order_info['currency_value']);

			$this->load->model('marketing/affiliate');

			$data['commission_total'] = $this->model_marketing_affiliate->getTotalTransactionsByOrderId($this->request->get['order_id']);

			$this->load->model('localisation/order_status');

			$order_status_info = $this->model_localisation_order_status->getOrderStatus($order_info['order_status_id']);

			if ($order_status_info) {
				$data['order_status'] = $order_status_info['name'];
			} else {
				$data['order_status'] = '';
			}

			$data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

			$data['order_status_id'] = $order_info['order_status_id'];

			$data['account_custom_field'] = $order_info['custom_field'];

			// Custom Fields
			$this->load->model('customer/custom_field');

			$data['account_custom_fields'] = array();

			$filter_data = array(
				'sort'  => 'cf.sort_order',
				'order' => 'ASC',
			);

			$custom_fields = $this->model_customer_custom_field->getCustomFields($filter_data);

			foreach ($custom_fields as $custom_field) {
				if ($custom_field['location'] == 'account' && isset($order_info['custom_field'][$custom_field['custom_field_id']])) {
					if ($custom_field['type'] == 'select' || $custom_field['type'] == 'radio') {
						$custom_field_value_info = $this->model_customer_custom_field->getCustomFieldValue($order_info['custom_field'][$custom_field['custom_field_id']]);

						if ($custom_field_value_info) {
							$data['account_custom_fields'][] = array(
								'name'  => $custom_field['name'],
								'value' => $custom_field_value_info['name']
							);
						}
					}

					if ($custom_field['type'] == 'checkbox' && is_array($order_info['custom_field'][$custom_field['custom_field_id']])) {
						foreach ($order_info['custom_field'][$custom_field['custom_field_id']] as $custom_field_value_id) {
							$custom_field_value_info = $this->model_customer_custom_field->getCustomFieldValue($custom_field_value_id);

							if ($custom_field_value_info) {
								$data['account_custom_fields'][] = array(
									'name'  => $custom_field['name'],
									'value' => $custom_field_value_info['name']
								);
							}
						}
					}

					if ($custom_field['type'] == 'text' || $custom_field['type'] == 'textarea' || $custom_field['type'] == 'file' || $custom_field['type'] == 'date' || $custom_field['type'] == 'datetime' || $custom_field['type'] == 'time') {
						$data['account_custom_fields'][] = array(
							'name'  => $custom_field['name'],
							'value' => $order_info['custom_field'][$custom_field['custom_field_id']]
						);
					}

					if ($custom_field['type'] == 'file') {
						$upload_info = $this->model_tool_upload->getUploadByCode($order_info['custom_field'][$custom_field['custom_field_id']]);

						if ($upload_info) {
							$data['account_custom_fields'][] = array(
								'name'  => $custom_field['name'],
								'value' => $upload_info['name']
							);
						}
					}
				}
			}

			// Custom fields
			$data['payment_custom_fields'] = array();

			foreach ($custom_fields as $custom_field) {
				if ($custom_field['location'] == 'address' && isset($order_info['payment_custom_field'][$custom_field['custom_field_id']])) {
					if ($custom_field['type'] == 'select' || $custom_field['type'] == 'radio') {
						$custom_field_value_info = $this->model_customer_custom_field->getCustomFieldValue($order_info['payment_custom_field'][$custom_field['custom_field_id']]);

						if ($custom_field_value_info) {
							$data['payment_custom_fields'][] = array(
								'name'  => $custom_field['name'],
								'value' => $custom_field_value_info['name'],
								'sort_order' => $custom_field['sort_order']
							);
						}
					}

					if ($custom_field['type'] == 'checkbox' && is_array($order_info['payment_custom_field'][$custom_field['custom_field_id']])) {
						foreach ($order_info['payment_custom_field'][$custom_field['custom_field_id']] as $custom_field_value_id) {
							$custom_field_value_info = $this->model_customer_custom_field->getCustomFieldValue($custom_field_value_id);

							if ($custom_field_value_info) {
								$data['payment_custom_fields'][] = array(
									'name'  => $custom_field['name'],
									'value' => $custom_field_value_info['name'],
									'sort_order' => $custom_field['sort_order']
								);
							}
						}
					}

					if ($custom_field['type'] == 'text' || $custom_field['type'] == 'textarea' || $custom_field['type'] == 'file' || $custom_field['type'] == 'date' || $custom_field['type'] == 'datetime' || $custom_field['type'] == 'time') {
						$data['payment_custom_fields'][] = array(
							'name'  => $custom_field['name'],
							'value' => $order_info['payment_custom_field'][$custom_field['custom_field_id']],
							'sort_order' => $custom_field['sort_order']
						);
					}

					if ($custom_field['type'] == 'file') {
						$upload_info = $this->model_tool_upload->getUploadByCode($order_info['payment_custom_field'][$custom_field['custom_field_id']]);

						if ($upload_info) {
							$data['payment_custom_fields'][] = array(
								'name'  => $custom_field['name'],
								'value' => $upload_info['name'],
								'sort_order' => $custom_field['sort_order']
							);
						}
					}
				}
			}

			// Shipping
			$data['shipping_custom_fields'] = array();

			foreach ($custom_fields as $custom_field) {
				if ($custom_field['location'] == 'address' && isset($order_info['shipping_custom_field'][$custom_field['custom_field_id']])) {
					if ($custom_field['type'] == 'select' || $custom_field['type'] == 'radio') {
						$custom_field_value_info = $this->model_customer_custom_field->getCustomFieldValue($order_info['shipping_custom_field'][$custom_field['custom_field_id']]);

						if ($custom_field_value_info) {
							$data['shipping_custom_fields'][] = array(
								'name'  => $custom_field['name'],
								'value' => $custom_field_value_info['name'],
								'sort_order' => $custom_field['sort_order']
							);
						}
					}

					if ($custom_field['type'] == 'checkbox' && is_array($order_info['shipping_custom_field'][$custom_field['custom_field_id']])) {
						foreach ($order_info['shipping_custom_field'][$custom_field['custom_field_id']] as $custom_field_value_id) {
							$custom_field_value_info = $this->model_customer_custom_field->getCustomFieldValue($custom_field_value_id);

							if ($custom_field_value_info) {
								$data['shipping_custom_fields'][] = array(
									'name'  => $custom_field['name'],
									'value' => $custom_field_value_info['name'],
									'sort_order' => $custom_field['sort_order']
								);
							}
						}
					}

					if ($custom_field['type'] == 'text' || $custom_field['type'] == 'textarea' || $custom_field['type'] == 'file' || $custom_field['type'] == 'date' || $custom_field['type'] == 'datetime' || $custom_field['type'] == 'time') {
						$data['shipping_custom_fields'][] = array(
							'name'  => $custom_field['name'],
							'value' => $order_info['shipping_custom_field'][$custom_field['custom_field_id']],
							'sort_order' => $custom_field['sort_order']
						);
					}

					if ($custom_field['type'] == 'file') {
						$upload_info = $this->model_tool_upload->getUploadByCode($order_info['shipping_custom_field'][$custom_field['custom_field_id']]);

						if ($upload_info) {
							$data['shipping_custom_fields'][] = array(
								'name'  => $custom_field['name'],
								'value' => $upload_info['name'],
								'sort_order' => $custom_field['sort_order']
							);
						}
					}
				}
			}

			$data['ip'] = $order_info['ip'];
			$data['forwarded_ip'] = $order_info['forwarded_ip'];
			$data['user_agent'] = $order_info['user_agent'];
			$data['accept_language'] = $order_info['accept_language'];

			// Additional Tabs
			$data['tabs'] = array();

			$this->load->model('extension/extension');
			if(empty($order_info['payment_code'])) {
			    $order_info['payment_code'] = "paymate";
			}
			$content = $this->load->controller('payment/' . $order_info['payment_code'] . '/order');

			if ($content) {
				$this->load->language('payment/' . $order_info['payment_code']);

				$data['tabs'][] = array(
					'code'    => $order_info['payment_code'],
					'title'   => $this->language->get('heading_title'),
					'content' => $content
				);
			}

			$extensions = $this->model_extension_extension->getInstalled('fraud');

			foreach ($extensions as $extension) {
				if ($this->config->get($extension . '_status')) {
					$this->load->language('fraud/' . $extension);

					$content = $this->load->controller('fraud/' . $extension . '/order');

					if ($content) {
						$data['tabs'][] = array(
							'code'    => $extension,
							'title'   => $this->language->get('heading_title'),
							'content' => $content
						);
					}
				}
			}

			// API login
			$this->load->model('user/api');

			$api_info = $this->model_user_api->getApi($this->config->get('config_api_id'));

			if ($api_info) {
				$data['api_id'] = $api_info['api_id'];
				$data['api_key'] = $api_info['key'];
				$data['api_ip'] = $this->request->server['REMOTE_ADDR'];
			} else {
				$data['api_id'] = '';
				$data['api_key'] = '';
				$data['api_ip'] = '';
			}

			$data['header'] = $this->load->controller('common/header');
			$data['column_left'] = $this->load->controller('common/column_left');
			$data['footer'] = $this->load->controller('common/footer');
			$data['order_log'] = $this->model_sale_order->getOrderStatuslog((int)$this->request->get['order_id']);

			$this->response->setOutput($this->load->view('sale/hz_order_info.tpl', $data));
		} else {
			$this->load->language('error/not_found');

			$this->document->setTitle($this->language->get('heading_title'));

			$data['heading_title'] = $this->language->get('heading_title');

			$data['text_not_found'] = $this->language->get('text_not_found');

			$data['breadcrumbs'] = array();

			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('text_home'),
				'href' => $this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL')
			);

			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('heading_title'),
				'href' => $this->url->link('error/not_found', 'token=' . $this->session->data['token'], 'SSL')
			);

			$data['header'] = $this->load->controller('common/header');
			$data['column_left'] = $this->load->controller('common/column_left');
			$data['footer'] = $this->load->controller('common/footer');

			$this->response->setOutput($this->load->view('error/not_found.tpl', $data));
		}
	    //$this->response->setOutput($this->load->view('sale/hz_order_info.tpl', $data));
	}
	
    private function getList($parameter = '') {
        $this->config->set('config_limit_admin', Accessor::either(Order::getLimitAdmin(), 20));
        $user_group = $this->model_user_user_group -> getUserGroupByUserId($this->user->getId(), "g.name");
        
        if (isset($this->request->get['filter_order_id'])) {
            $filter_order_id = $this->request->get['filter_order_id'];
        } else {
            $filter_order_id = null;
        }
        
        if (isset($this->request->get['filter_order_number'])) {
            $filter_order_number = $this->request->get['filter_order_number'];
        } else {
            $filter_order_number = null;
        }
            
        if (isset($this->request->get['filter_shipping_firstname'])) {
            $filter_shipping_firstname = $this->request->get['filter_shipping_firstname'];
        } else {
            $filter_shipping_firstname = null;
        }
        
        if (isset($this->request->get['filter_customer'])) {
            $filter_customer = $this->request->get['filter_customer'];
        } else {
            $filter_customer = null;
        }
        
        if (isset($this->request->get['filter_order_status'])) {
            $filter_order_status = $this->request->get['filter_order_status'];
        } else {
            $filter_order_status = null;
        }
        
        if (isset($this->request->get['filter_total'])) {
            $filter_total = $this->request->get['filter_total'];
        } else {
            $filter_total = null;
        }
        
        if (isset($this->request->get['filter_date_added'])) {
            $filter_date_added = $this->request->get['filter_date_added'];
        } else {
            $filter_date_added = null;
        }
        
        if (isset($this->request->get['filter_date_modified'])) {
            $filter_date_modified = $this->request->get['filter_date_modified'];
        } else {
            $filter_date_modified = null;
        }
        
        if (isset($this->request->get['sort'])) {
            $sort = $this->request->get['sort'];
        } else {
            $sort = 'o.order_id';
        }
        
        if (isset($this->request->get['order'])) {
            $order = $this->request->get['order'];
        } else {
            $order = 'DESC';
        }
        
        if (isset($this->request->get['page'])) {
            $page = $this->request->get['page'];
        } else {
            $page = 1;
        }
        
        $url = '';
        
        if (isset($this->request->get['filter_order_id'])) {
            $url .= '&filter_order_id=' . $this->request->get['filter_order_id'];
        }
        
        if (isset($this->request->get['filter_shipping_firstname'])) {
            $url .= '&filter_shipping_firstname=' . urlencode(html_entity_decode($this->request->get['filter_shipping_firstname'], ENT_QUOTES, 'UTF-8'));
        }
        
        if (isset($this->request->get['filter_customer'])) {
            $url .= '&filter_customer=' . urlencode(html_entity_decode($this->request->get['filter_customer'], ENT_QUOTES, 'UTF-8'));
        }
        
        if (isset($this->request->get['filter_order_status'])) {
            $url .= '&filter_order_status=' . $this->request->get['filter_order_status'];
        }
        
        if (isset($this->request->get['filter_total'])) {
            $url .= '&filter_total=' . $this->request->get['filter_total'];
        }
        
        if (isset($this->request->get['filter_date_added'])) {
            $url .= '&filter_date_added=' . $this->request->get['filter_date_added'];
        }
        
        if (isset($this->request->get['filter_date_modified'])) {
            $url .= '&filter_date_modified=' . $this->request->get['filter_date_modified'];
        }
        
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
        
        /* $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL')
        ); */
        $data['breadcrumbs'][] = array(
            'text' => $parameter['action'] == "processing" ? "打单发货" : $this->language->get('heading_title'),
            'href' => $this->url->link("sale/hz_order/{$parameter['action']}", 'token=' . $this->session->data['token'] . $url, 'SSL')
        );
        
        $data['invoice'] = $this->url->link('sale/order/invoice', 'token=' . $this->session->data['token'], 'SSL');
        $data['shipping'] = $this->url->link('sale/order/shipping', 'token=' . $this->session->data['token'], 'SSL');
        $data['add'] = $this->url->link('sale/order/add', 'token=' . $this->session->data['token'], 'SSL');
        $data['action'] = $this->url->link('sale/hz_order/excel', 'token=' . $this->session->data['token'] . '&type=1', 'SSL');
        $data['shipping_action'] = $this->url->link('sale/hz_order/shipping', 'token=' . $this->session->data['token'], 'SSL');
        
        $data['orders'] = array();
        
        switch ($user_group['name']) {
            case "hangzhou":
                $filter_splitting_code = 1; 
                break;
            case "shandong":
                $filter_splitting_code = 0;
                break;
            default:
                $filter_splitting_code = $this->request->get['splitting_code'];
                //$this->response->redirect($this->url->link('common/login', '', 'SSL'));
                break;
        }
        $data['splitting_code'] = $filter_splitting_code;
        $filter_data = array(
            'filter_order_id'      => $filter_order_id,
            'filter_order_number'      => $filter_order_number,
            'filter_shipping_firstname'      => $filter_shipping_firstname,
            'filter_customer'	   => $filter_customer,
            'filter_order_status'  => $filter_order_status,
            'filter_total'         => $filter_total,
            'filter_date_added'    => $filter_date_added,
            'filter_date_modified' => $filter_date_modified,
            'filter_splitting_code' => $filter_splitting_code,
            'sort'                 => $sort,
            'order'                => $order,
            'start'                => ($page - 1) * $this->config->get('config_limit_admin'),
            'limit'                => $this->config->get('config_limit_admin')
        );
        
        $order_total = $this->model_sale_order->getHzTotalOrders($filter_data);
        
        $results = $this->model_sale_order->getHzOrders($filter_data);
        
        foreach ($results as $result) {
            $product = [];
            $payload = json_decode($result['payload'], true);
            if(!empty($payload)) {
                foreach ($payload as $info) {
                    $product[] = $info['pid'];
                }
            }
            $this->load->model('catalog/product');
            $products = $this->model_sale_order->getOrderProducts($result['order_id']);
            $product = $this->model_catalog_product->getProductsByProductIds($product, ['name']);
            $productName = "";
            $productIds = json_decode($result['product_ids'], true);
            $orderProduct = [];
            array_walk($products, function (&$value, $key) use (&$productName, $productIds, &$orderProduct) {
                if(in_array($value['product_id'], $productIds)) {
                    $productName .= $value['name'] . " " . $value['quantity'] . ",";
                    $orderProduct[] = $value;
                }
            });
                $data['orders'][] = array(
                    'order_numbering_id'        => $result['order_numbering_id'],
                    'order_id'      => $result['order_id'],
                    'customer'      => $result['customer'],
                    'splitting_company'      => $result['splitting_company'],
                    'parcle'      => $result['parcle'],
                    //'status'        => Accessor::either($result['status'] ? (($result['status'] == "照片已下载") ? "未导出地址" : ($result['status'] == "已发货") ? $result['status'] : "已发货") : null, "未下载照片"),
                    //'status'        => $result['status'] ? $result['status'] : DataBase::getTableFields('order_status', 'order_status_id = :s_id AND language_id = :l_id', [':s_id' => $result['order_status_id'], ":l_id" => 2], 'name'),
                    'status'        => Order::$hzSearchOrderStatus[$result['order_status_id'] ? $result['order_status_id'] == 18 ? 20 : $result['order_status_id'] : 15],
                    'order_number'        => $result['order_number'],
                    'shipping_firstname'        => mb_substr($result['shipping_firstname'], 0, 5),
                    'shipping_id'        => $result['shipping_id'],
                    'shipping_address'        => $result['shipping_firstname'] . " " . $result['telephone'] . " " . $result['shipping_country'] . " ". $result['shipping_city'] . " ". $result['shipping_zone'] . " ". $result['shipping_address_1'],
                    'order_product'        => mb_substr($productName, 0, -1),
                    'order_products'        => $orderProduct,
                    'order_product_count'        => count($orderProduct),
                    'total'         => $this->currency->format($result['total'], $result['currency_code'] ? $result['currency_code'] : 'RMB', $result['currency_value']),
                    'date_added'    => date($this->language->get('datetime_format'), strtotime($result['date_added'])),
                    'date_modified' => date($this->language->get('datetime_format'), strtotime($result['date_modified'])),
                    'shipping_code' => $result['shipping_code'],
                    'view'          => $this->url->link('sale/hz_order/info', 'token=' . $this->session->data['token'] . '&order_id=' . $result['order_id'] . $url, 'SSL'),
                    'edit'          => $this->url->link('sale/hz_order/edit', 'token=' . $this->session->data['token'] . '&order_id=' . $result['order_id'] . $url, 'SSL'),
                );
        }
        $data['heading_title'] = $this->language->get('heading_title');
        
        $data['text_list'] = $this->language->get('text_list');
        $data['text_no_results'] = $this->language->get('text_no_results');
        $data['text_confirm'] = $this->language->get('text_confirm');
        $data['text_missing'] = $this->language->get('text_missing');
        $data['text_loading'] = $this->language->get('text_loading');
        $data['text_consignee'] = $this->language->get('text_consignee');
        $data['text_order_product_info'] = $this->language->get('text_order_product_info');
        
        $data['column_order_id'] = $this->language->get('column_order_id');
        $data['column_customer'] = $this->language->get('column_customer');
        $data['column_status'] = $this->language->get('column_status');
        $data['column_total'] = $this->language->get('column_total');
        $data['column_date_added'] = $this->language->get('column_date_added');
        $data['column_date_modified'] = $this->language->get('column_date_modified');
        $data['column_action'] = $this->language->get('column_action');
        
        $data['entry_return_id'] = $this->language->get('entry_return_id');
        $data['entry_order_id'] = $this->language->get('entry_order_id');
        $data['entry_customer'] = $this->language->get('entry_customer');
        $data['entry_order_status'] = $this->language->get('entry_order_status');
        $data['entry_total'] = $this->language->get('entry_total');
        $data['entry_date_added'] = $this->language->get('entry_date_added');
        $data['entry_date_modified'] = $this->language->get('entry_date_modified');
        $data['entry_order_number'] = "订单号";
        $data['entry_shipping_firstname'] = "收货人";
        
        $data['button_invoice_print'] = $this->language->get('button_invoice_print');
        $data['button_shipping_print'] = $this->language->get('button_shipping_print');
        $data['button_add'] = $this->language->get('button_add');
        $data['button_edit'] = $this->language->get('button_edit');
        $data['button_delete'] = $this->language->get('button_delete');
        $data['button_filter'] = $this->language->get('button_filter');
        $data['button_view'] = $this->language->get('button_view');
        $data['button_ip_add'] = $this->language->get('button_ip_add');
        $data['error'] = Yii::$app->redis->get('error_input');
        $data['success'] = Yii::$app->redis->get('success_input');
        
        $data['token'] = $this->session->data['token'];
        
        if (isset($this->request->post['selected'])) {
            $data['selected'] = (array)$this->request->post['selected'];
        } else {
            $data['selected'] = array();
        }
        
        $url = '';
        
        if (isset($this->request->get['filter_order_id'])) {
            $url .= '&filter_order_id=' . $this->request->get['filter_order_id'];
        }
        
        if (isset($this->request->get['filter_order_number'])) {
            $url .= '&filter_order_number=' . $this->request->get['filter_order_number'];
        }
        
        if (isset($this->request->get['filter_shipping_firstname'])) {
            $url .= '&filter_shipping_firstname=' . $this->request->get['filter_shipping_firstname'];
        }
        
        if (isset($this->request->get['filter_customer'])) {
            $url .= '&filter_customer=' . urlencode(html_entity_decode($this->request->get['filter_customer'], ENT_QUOTES, 'UTF-8'));
        }
        
        if (isset($this->request->get['filter_order_status'])) {
            $url .= '&filter_order_status=' . $this->request->get['filter_order_status'];
        }
        
        if (isset($this->request->get['filter_total'])) {
            $url .= '&filter_total=' . $this->request->get['filter_total'];
        }
        
        if (isset($this->request->get['filter_date_added'])) {
            $url .= '&filter_date_added=' . $this->request->get['filter_date_added'];
        }
        
        if (isset($this->request->get['filter_date_modified'])) {
            $url .= '&filter_date_modified=' . $this->request->get['filter_date_modified'];
        }
        
        if ($order == 'ASC') {
            $url .= '&order=DESC';
        } else {
            $url .= '&order=ASC';
        }
        
        if (isset($this->request->get['page'])) {
            $url .= '&page=' . $this->request->get['page'];
        }
        
        $data['sort_order'] = $this->url->link('sale/hz_order', 'token=' . $this->session->data['token'] . '&sort=o.order_id' . $url, 'SSL');
        $data['sort_customer'] = $this->url->link('sale/hz_order', 'token=' . $this->session->data['token'] . '&sort=customer' . $url, 'SSL');
        $data['sort_status'] = $this->url->link('sale/hz_order', 'token=' . $this->session->data['token'] . '&sort=status' . $url, 'SSL');
        $data['sort_total'] = $this->url->link('sale/hz_order', 'token=' . $this->session->data['token'] . '&sort=o.total' . $url, 'SSL');
        $data['sort_date_added'] = $this->url->link('sale/hz_order', 'token=' . $this->session->data['token'] . '&sort=o.date_added' . $url, 'SSL');
        $data['sort_date_modified'] = $this->url->link('sale/hz_order', 'token=' . $this->session->data['token'] . '&sort=o.date_modified' . $url, 'SSL');
        
        $url = '';
        
        if (isset($this->request->get['filter_order_id'])) {
            $url .= '&filter_order_id=' . $this->request->get['filter_order_id'];
        }
        
        if (isset($this->request->get['filter_order_number'])) {
            $url .= '&filter_order_number=' . $this->request->get['filter_order_number'];
        }
        
        if (isset($this->request->get['filter_shipping_firstname'])) {
            $url .= '&filter_shipping_firstname=' . $this->request->get['filter_shipping_firstname'];
        }
        
        if (isset($this->request->get['filter_customer'])) {
            $url .= '&filter_customer=' . urlencode(html_entity_decode($this->request->get['filter_customer'], ENT_QUOTES, 'UTF-8'));
        }
        
        if (isset($this->request->get['filter_order_status'])) {
            $url .= '&filter_order_status=' . $this->request->get['filter_order_status'];
        }
        
        if (isset($this->request->get['filter_total'])) {
            $url .= '&filter_total=' . $this->request->get['filter_total'];
        }
        
        if (isset($this->request->get['filter_date_added'])) {
            $url .= '&filter_date_added=' . $this->request->get['filter_date_added'];
        }
        
        if (isset($this->request->get['filter_date_modified'])) {
            $url .= '&filter_date_modified=' . $this->request->get['filter_date_modified'];
        }
        
        if (isset($this->request->get['sort'])) {
            $url .= '&sort=' . $this->request->get['sort'];
        }
        
        if (isset($this->request->get['order'])) {
            $url .= '&order=' . $this->request->get['order'];
        }
        
        $pagination = new Pagination();
        $pagination->total = $order_total;
        $pagination->page = $page;
        $pagination->limit = $this->config->get('config_limit_admin');
        $pagination->url = $this->url->link("sale/hz_order/{$parameter['action']}", 'token=' . $this->session->data['token'] . "&splitting_code={$filter_splitting_code}" . $url . '&page={page}', 'SSL');
        
        $data['pagination'] = $pagination->render();
        
        $data['results'] = sprintf($this->language->get('text_pagination'), ($order_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($order_total - $this->config->get('config_limit_admin'))) ? $order_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $order_total, ceil($order_total / $this->config->get('config_limit_admin')));
        
        $data['filter_order_id'] = $filter_order_id;
        $data['filter_order_number'] = $filter_order_number;
        $data['filter_shipping_firstname'] = $filter_shipping_firstname;
        $data['filter_customer'] = $filter_customer;
        $data['filter_order_status'] = $filter_order_status;
        $data['filter_total'] = $filter_total;
        $data['filter_date_added'] = $filter_date_added;
        $data['filter_date_modified'] = $filter_date_modified;
        
        $this->load->model('localisation/order_status');
        
        //$data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();
        //$data['order_statuses'] = Order::getHzOrderStatus();
        $data['order_statuses'] = Order::$hzSearchOrderStatus;
        $data['sort'] = $sort;
        $data['order'] = $order;
        
        $data['store'] = HTTPS_CATALOG;
        
        // API login
        $this->load->model('user/api');
        
        $api_info = $this->model_user_api->getApi($this->config->get('config_api_id'));
        
        if ($api_info) {
            $data['api_id'] = $api_info['api_id'];
            $data['api_key'] = $api_info['key'];
            $data['api_ip'] = $this->request->server['REMOTE_ADDR'];
        } else {
            $data['api_id'] = '';
            $data['api_key'] = '';
            $data['api_ip'] = '';
        }
        
        $data['page_limit'] = Order::getLimitAdmin();
        $data['express'] = $this->model_sale_order->expressCompanys();
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('sale/'.$parameter['tpl'], $data));
    }
    
    public function edit() {
        try {
            $this->db->query("START TRANSACTION");
            $data = [];
            $data['header'] = $this->load->controller('common/header');
            $data['column_left'] = $this->load->controller('common/column_left');
            $data['footer'] = $this->load->controller('common/footer');
            $this->load->model('sale/order');
            
            if (isset($this->request->get['order_id'])) {
                $order_id = $this->request->get['order_id'];
            } else {
                $order_id = 0;
            }
            
            if(($this->request->server['REQUEST_METHOD'] == 'POST')) {
                try {
                    $this->model_sale_order->editHzOrder((int)$this->request->get['order_id'], $this->request->post);
                }catch (Exception $e) {
                    $this->db->query("ROLLBACK");
                    var_dump($e);
                }finally {
                    $this->db->query("COMMIT");
                }
                $this->response->redirect($this->url->link('sale/hz_order', 'token=' . $this->session->data['token'], 'SSL'));
            }
            $order_info = $this->model_sale_order->getHzOrder($order_id);
            if ($order_info) {
                $this->load->language('sale/order');
                $this->document->setTitle($this->language->get('heading_title'));
                
                $data['heading_title'] = $this->language->get('heading_title');
                $data['column_product'] = $this->language->get('column_product');
    			$data['column_model'] = $this->language->get('column_model');
    			$data['column_quantity'] = $this->language->get('column_quantity');
    			$data['column_price'] = $this->language->get('column_price');
    			$data['column_total'] = $this->language->get('column_total');
    			$data['email'] = $order_info['email'];
    			$data['telephone'] = $order_info['telephone'];
    			$data['order_number'] = $order_info['order_number'];
    			$data['shipping_country'] = $order_info['shipping_country'];
    			$data['shipping_city'] = $order_info['shipping_city'];
    			$data['shipping_zone'] = $order_info['shipping_zone'];
    			$data['shipping_address_1'] = $order_info['shipping_address_1'];
    			$data['shipping_id'] = $order_info['shipping_id'];
    			$data['splitting_company'] = $order_info['splitting_company'];
    			$data['date_added'] = date($this->language->get('datetime_format'), strtotime($order_info['date_added']));
    			$url = '';
    			if (isset($this->request->get['filter_order_id'])) {
    			    $url .= '&filter_order_id=' . $this->request->get['filter_order_id'];
    			}
    			
    			if (isset($this->request->get['filter_customer'])) {
    			    $url .= '&filter_customer=' . urlencode(html_entity_decode($this->request->get['filter_customer'], ENT_QUOTES, 'UTF-8'));
    			}
    			
    			if (isset($this->request->get['filter_order_status'])) {
    			    $url .= '&filter_order_status=' . $this->request->get['filter_order_status'];
    			}
    			
    			if (isset($this->request->get['filter_total'])) {
    			    $url .= '&filter_total=' . $this->request->get['filter_total'];
    			}
    			
    			if (isset($this->request->get['filter_date_added'])) {
    			    $url .= '&filter_date_added=' . $this->request->get['filter_date_added'];
    			}
    			
    			if (isset($this->request->get['filter_date_modified'])) {
    			    $url .= '&filter_date_modified=' . $this->request->get['filter_date_modified'];
    			}
    			
    			if (isset($this->request->get['sort'])) {
    			    $url .= '&sort=' . $this->request->get['sort'];
    			}
    			
    			if (isset($this->request->get['order'])) {
    			    $url .= '&order=' . $this->request->get['order'];
    			}
    			
    			if (isset($this->request->get['page'])) {
    			    $url .= '&page=' . $this->request->get['page'];
    			}
    			
    			if (isset($this->request->get['order_id'])) {
    			    $data['action'] = $this->url->link('sale/hz_order/edit', 'token=' . $this->session->data['token'] . '&order_id=' . $this->request->get['order_id'] . $url, 'SSL');
    			}
    			$data['breadcrumbs'] = array();
    			$data['breadcrumbs'][] = array(
    			    'text' => $this->language->get('text_home'),
    			    'href' => $this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL')
    			);
    			$data['breadcrumbs'][] = array(
    			    'text' => $this->language->get('heading_title'),
    			    'href' => $this->url->link('sale/hz_order', 'token=' . $this->session->data['token'] . $url, 'SSL')
    			);
    			// Shipping Address
    			if ($order_info['shipping_address_format']) {
    			    $format = $order_info['shipping_address_format'];
    			} else {
    			    $format = '{country}' . "\n" . '{city} {postcode}' . "\n" . '{zone}' . "\n" . '{address_1}' . "\n" . '{address_2}';
    			}
    			
    			$find = array(
    			    '{address_1}',
    			    '{address_2}',
    			    '{city}',
    			    '{postcode}',
    			    '{zone}',
    			    '{zone_code}',
    			    '{country}'
    			);
    			
    			$replace = array(
    			    'address_1' => $order_info['shipping_address_1'],
    			    'address_2' => $order_info['shipping_address_2'],
    			    'city'      => $order_info['shipping_city'],
    			    'postcode'  => $order_info['shipping_postcode'],
    			    'zone'      => $order_info['shipping_zone'],
    			    //'zone_code' => $order_info['shipping_zone_code'],
    			    'country'   => $order_info['shipping_country']
    			);
    			
    			$data['shipping_firstname'] = $order_info['shipping_firstname'];
    			$data['shipping_address'] = str_replace(array("\r\n", "\r", "\n"), ' ', preg_replace(array("/\s\s+/", "/\r\r+/", "/\n\n+/"), ' ', trim(str_replace($find, $replace, $format))));
    			
    			// Uploaded files
    			$this->load->model('tool/upload');
    			
    			$data['products'] = array();
    			
    			$products = $this->model_sale_order->getOrderProducts($this->request->get['order_id']);
    			
    			$order_total = 0;
    			foreach ($products as $product) {
    			    $option_data = array();
    			
    			    $options = $this->model_sale_order->getOrderOptions($this->request->get['order_id'], $product['order_product_id']);
    			
    			    foreach ($options as $option) {
    			        if ($option['type'] != 'file') {
    			            $option_data[] = array(
    			                'name'  => $option['name'],
    			                'value' => $option['value'],
    			                'type'  => $option['type']
    			            );
    			        } else {
    			            $upload_info = $this->model_tool_upload->getUploadByCode($option['value']);
    			
    			            if ($upload_info) {
    			                $option_data[] = array(
    			                    'name'  => $option['name'],
    			                    'value' => $upload_info['name'],
    			                    'type'  => $option['type'],
    			                    'href'  => $this->url->link('tool/upload/download', 'token=' . $this->session->data['token'] . '&code=' . $upload_info['code'], 'SSL')
    			                );
    			            }
    			        }
    			    }
    			
    			    $data['products'][] = array(
    			        'order_product_id' => $product['order_product_id'],
    			        'product_id'       => $product['product_id'],
    			        'location'       => $product['location'],
    			        'name'    	 	   => $product['name'],
    			        //'coupon_name'    	 	   => $product['coupon_name'],
    			        //'coupon_price'    	 	   => $product['coupon_price'],
    			        'model'    		   => $product['model'],
    			        'option'   		   => $option_data,
    			        'quantity'		   => $product['quantity'],
    			        'pay_total'    		   => $this->currency->format($product['pay_total'], $order_info['currency_code'] ? $order_info['currency_code'] : "RMB", $order_info['currency_value']),
    			        'row_total'    		   => $this->currency->format($product['price'] * $product['quantity'], $order_info['currency_code'] ? $order_info['currency_code'] : "RMB", $order_info['currency_value']),
    			        'price'    		   => $this->currency->format($product['price'] + ($this->config->get('config_tax') ? $product['tax'] : 0), $order_info['currency_code'] ? $order_info['currency_code'] : "RMB", $order_info['currency_value']),
    			        'total'    		   => $this->currency->format($product['total'] + ($this->config->get('config_tax') ? ($product['tax'] * $product['quantity']) : 0), $order_info['currency_code'] ? $order_info['currency_code'] : "RMB", $order_info['currency_value']),
    			        'href'     		   => $this->url->link('catalog/product/edit', 'token=' . $this->session->data['token'] . '&product_id=' . $product['product_id'], 'SSL')
    			    );
    			    $order_total +=$product['total'];
    			}
    			
    			$data['order_total'] = $order_total;
    			$data['express'] = $this->model_sale_order->expressCompanys();
    			$data['order_log'] = $this->model_sale_order->getOrderStatuslog((int)$this->request->get['order_id']);
                $this->response->setOutput($this->load->view('sale/hz_order_form.tpl', $data));
            }
        }catch (Exception $e) {
            $this->db->query("ROLLBACK");
            var_dump($e);
        }finally {
            $this->db->query("COMMIT");
        }
    }
    public function inputExcelAction() {
        Execution::autoTransaction(Yii::$app->db, function() {
            if($_FILES['inputExcel']) {
                $inputResult['error'] = $this->uploadFile($_FILES['inputExcel']['name'], $_FILES['inputExcel']['tmp_name']);
            }else {
                $inputResult['error'] = "请上传Excel文件";
            }
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode($inputResult));
        });
        /* $inputCount = Yii::$app->redis->get('input_error_count');
        $inputSuccess = Yii::$app->redis->get('input_success_count');
        if(Predicates::equals($status, false)) {
            //Yii::$app->redis->set('error_input', 'excel中有快递公司名称不合法的数据, 导入失败');
            //Yii::$app->redis->expire('error_input', 5);
            $json['error'] = "excel中有快递公司名称不合法的数据, 导入失败";
        } elseif (Predicates::equals($status, NULL)) {
            //Yii::$app->redis->set('error_input', '暂无可填写的快递信息');
            //Yii::$app->redis->expire('error_input', 5);
            $json['error'] = "暂无可填写的快递信息";
        } elseif(!empty($inputCount)){
            //Yii::$app->redis->set('error_input', 'excel中有' . $inputCount . '条数据异常！请重新核对');
            //Yii::$app->redis->expire('error_input', 5);
            $json['error'] = "条数据异常！请重新核对";
        } else {
            if (!empty($inputSuccess)) {
                Yii::$app->redis->set('success_input', '成功导入' . $inputSuccess . '条数据');
                $json['success'] = '成功导入' . $inputSuccess . '条数据';
            } else {
                Yii::$app->redis->set('success_input', '暂无可填写的快递信息');
                $json['error'] = "暂无可填写的快递信息";
            }
            Yii::$app->redis->expire('success_input', 5);
        } */
        //$this->response->redirect($this->url->link('sale/hz_order/processing', 'token=' . $_SESSION['default']['token'], 'SSL'));
    }
    
    private function uploadFile($file, $filetempname) {
        $filePath = APP_PATH . "/runtime/tmp/";
        createTempFile();
        //上传后的文件名
        if(!in_array($extend = strrchr ($file,'.'), ['.xlsx', '.xls'])) {
            return $json['error'] = "请上传正确Excel文件";
        }
        $name = date("Y-m-d H:i:s") . $extend;
        $uploadfile = $filePath . $name;//上传后的文件名地址
        //move_uploaded_file() 函数将上传的文件移动到新位置。若成功，则返回 true，否则返回 false。
        if(move_uploaded_file($filetempname , $uploadfile)){
            $objPHPExcel = new PHPExcel();
            if ($extend == '.xlsx') {
                $objReader = PHPExcel_IOFactory::createReader('Excel2007');//use excel2007 for 2007 format
            } else {
                $objReader = PHPExcel_IOFactory::createReader('Excel5');//use excel5
            }
            $objPHPExcel = $objReader->load($uploadfile); 
            $sheet = $objPHPExcel->getSheet(0);
            $highestRow = $sheet->getHighestRow();           //取得总行数 
            $highestColumn = $sheet->getHighestColumn(); //取得总列数
            $highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn);
            if($highestColumnIndex < HI\Config\EXPRESS\ADMIN_SPLITTING_COMPANY) {
                return $json['error'] = "请上传正确Excel文件";
            }
            $count = 0;
            $flag = 0;
            $orderIds = [];
            unlink($uploadfile);
            for ($row = 2; $row <= $highestRow; $row++) {
                $strs=array();
                //注意highestColumnIndex的列数索引从0开始
                for ($col = 0; $col < $highestColumnIndex; $col++) {
                    $strs[$col] = $sheet->getCellByColumnAndRow($col, $row)->getValue();
                }
                //得到数据 开始填物流号 和快递单号
                //0 订单号 1 物流号 2 物流公司(读配置)
                $orderNumber = HI\Config\EXPRESS\ADMIN_ORDER_NUMBER;
                $splittingId = HI\Config\EXPRESS\ADMIN_SPLITTING_ID;
                $company = HI\Config\EXPRESS\ADMIN_SPLITTING_COMPANY;
                $queryEms = (new Query())->select('code')
                    ->from('express_code')
                    ->where(['company' => $strs[$company]])
                    ->one()['code'];
                if(empty($queryEms) && !empty($strs[$orderNumber])) {
                    return $json['error'] = "excel中有快递公司名称不合法的数据, 导入失败";
                }
                if (!empty($strs[$orderNumber])) {
                    $handerOrderNumber = explode('_', $strs[$orderNumber])[0];
                } else {
                    $handerOrderNumber = '';
                }
                $query = (new Query())->select('os.order_child_id, o.order_id')
                    ->from(HI\TableName\ORDER . ' as o')
                    ->leftJoin(HI\TableName\ORDER_SPLITTING . ' as os', ' o.order_id = os.order_id ')
                    ->where(['o.order_number' => $handerOrderNumber, 'os.code' => $_SESSION['default']['splitting_code']])
                    ->all();
                if ($query) {
                    foreach ($query as $value) {
                        $condition = ['code' => $_SESSION['default']['splitting_code'], 'order_child_id' => $value['order_child_id']];
                        $inputExpress = ['splitting_company' => $queryEms, 'shipping_id' => $strs[$splittingId]];
                        if (DataBase::doTableUpdate(HI\TableName\ORDER_SPLITTING, $inputExpress, $condition)) {
                            $express = new Express();
                            $express -> subscribe($queryEms, [$strs[$splittingId]]);
                            $count++;
                            //Yii::$app->redis->set('input_error_count', $count);
                            //Yii::$app->redis->expire('input_error_count', 60);
                            $orderIds[] = $value['order_id'];
                        }
                        //$flag++;
                        //Yii::$app->redis->set('input_success_count', $flag);
                        //Yii::$app->redis->expire('input_success_count', 60);
                    }
                }
            }
            if (!empty($orderIds)) {
                /* 发货提醒 */
                Order::shipping($orderIds, $this->session->data['splitting_code'], $this->user->getId());
                return $json['success'] = '成功导入' . $count . '条数据';
            }
            return $json['error'] = '';
        }
    }

    private static function autoShipping($file,$filetempname) {
        
    }
    
    public function excel($order_ids) {
        try {
            $this->db->query("START TRANSACTION");
            $this->load->model('sale/order');
            $orders = $this->model_sale_order->getOrderByIds($order_ids);
            if(!$orders) {
                $json['error'] = "没有查到相应数据";
                $this->response->addHeader('Content-Type: application/json');
                $this->response->setOutput(json_encode($json));
                return;
            }
            
            // Create new PHPExcel object
            $objPHPExcel = new PHPExcel();
            // Set properties
            $objPHPExcel->getProperties()->setCreator("ctos")
            ->setLastModifiedBy("ctos")
            ->setTitle("Office 2007 XLSX Test Document")
            ->setSubject("Office 2007 XLSX Test Document")
            ->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.")
            ->setKeywords("office 2007 openxml php")
            ->setCategory("Test result file");
            
            // set width
            $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
            $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(30);
            $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
            $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
            $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
            $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
            $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(30);
            $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(20);
            $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(20);
            $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(20);
            $objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(20);
            $objPHPExcel->getActiveSheet()->getColumnDimension('L')->setWidth(20);
            $objPHPExcel->getActiveSheet()->getColumnDimension('M')->setWidth(20);
            $objPHPExcel->getActiveSheet()->getColumnDimension('N')->setWidth(20);
            $objPHPExcel->getActiveSheet()->getColumnDimension('O')->setWidth(20);
            $objPHPExcel->getActiveSheet()->getColumnDimension('P')->setWidth(20);
            $objPHPExcel->getActiveSheet()->getColumnDimension('Q')->setWidth(20);
            
            // 设置行高度
            $objPHPExcel->getActiveSheet()->getRowDimension('1')->setRowHeight(32);
            
            $objPHPExcel->getActiveSheet()->getRowDimension('2')->setRowHeight(25);
            
            // 字体和样式
            $objPHPExcel->getActiveSheet()->getDefaultStyle()->getFont()->setSize(12);
            $objPHPExcel->getActiveSheet()->getStyle('A1:Q1')->getFont()->setBold(true);
            
            $objPHPExcel->getActiveSheet()->getStyle('A1:Q1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $objPHPExcel->getActiveSheet()->getStyle('A1:Q1')->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
            
            // 设置水平居中
            $objPHPExcel->getActiveSheet()->getStyle('A')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $objPHPExcel->getActiveSheet()->getStyle('B')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $objPHPExcel->getActiveSheet()->getStyle('C')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $objPHPExcel->getActiveSheet()->getStyle('D')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $objPHPExcel->getActiveSheet()->getStyle('E')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $objPHPExcel->getActiveSheet()->getStyle('F')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $objPHPExcel->getActiveSheet()->getStyle('G')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $objPHPExcel->getActiveSheet()->getStyle('H')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $objPHPExcel->getActiveSheet()->getStyle('I')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $objPHPExcel->getActiveSheet()->getStyle('J')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $objPHPExcel->getActiveSheet()->getStyle('K')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $objPHPExcel->getActiveSheet()->getStyle('L')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $objPHPExcel->getActiveSheet()->getStyle('M')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $objPHPExcel->getActiveSheet()->getStyle('N')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $objPHPExcel->getActiveSheet()->getStyle('O')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $objPHPExcel->getActiveSheet()->getStyle('P')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $objPHPExcel->getActiveSheet()->getStyle('Q')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            //  合并
//             $objPHPExcel->getActiveSheet()->mergeCells('A2:Q2');
            
            // 表头
            $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A1', '订单编号')
            ->setCellValue('B1', '订单号')
            ->setCellValue('C1', '买家昵称')
            ->setCellValue('D1', '收货人姓名')
            ->setCellValue('E1', '收货人省份')
            ->setCellValue('F1', '收货人市')
            ->setCellValue('G1', '收货人区')
            ->setCellValue('H1', '收货人详细地址')
            ->setCellValue('I1', '收货人手机')
            ->setCellValue('J1', '产品名称')
            ->setCellValue('K1', '产品编码')
            ->setCellValue('L1', '产品数量')
            ->setCellValue('M1', '单价')
            ->setCellValue('N1', '总价')
            ->setCellValue('O1', '订单备注')
            ->setCellValue('P1', '物流公司')
            ->setCellValue('Q1', '物流号');
            // 内容
            $order_id = 0;
            for ($i = 0, $len = count($orders); $i < $len; $i++) {
                if(!in_array($orders[$i]['product_id'], json_decode($orders[$i]['product_ids'], true))) {
                    continue;
                }
                if($order_id != $orders[$i]['order_id']) {
                    $objPHPExcel->getActiveSheet(0)->setCellValue('A' . ($i + 2), $this->model_sale_order->getOrderNumberByCity($orders[$i]['order_child_id']));
                    $objPHPExcel->getActiveSheet(0)->setCellValue('B' . ($i + 2), $orders[$i]['order_number']);
                    $objPHPExcel->getActiveSheet(0)->setCellValue('C' . ($i + 2), $orders[$i]['customer']);
                    $objPHPExcel->getActiveSheet(0)->setCellValue('D' . ($i + 2), $orders[$i]['shipping_firstname']);
                    $objPHPExcel->getActiveSheet(0)->setCellValue('E' . ($i + 2), $orders[$i]['shipping_country']);
                    $objPHPExcel->getActiveSheet(0)->setCellValue('F' . ($i + 2), $orders[$i]['shipping_city']);
                    $objPHPExcel->getActiveSheet(0)->setCellValue('G' . ($i + 2), $orders[$i]['shipping_zone']);
                    $objPHPExcel->getActiveSheet(0)->setCellValue('H' . ($i + 2), $orders[$i]['shipping_address_1']);
                    $objPHPExcel->getActiveSheet(0)->setCellValue('I' . ($i + 2), $orders[$i]['telephone']);
                    $objPHPExcel->getActiveSheet(0)->setCellValue('P' . ($i + 2), $orders[$i]['splitting_company']);
                    $objPHPExcel->getActiveSheet(0)->setCellValue('Q' . ($i + 2), $orders[$i]['shipping_id']);
                }
                $objPHPExcel->getActiveSheet(0)->setCellValue('J' . ($i + 2), $orders[$i]['name']);
                $objPHPExcel->getActiveSheet(0)->setCellValue('K' . ($i + 2), strval($this->model_sale_order->doQueryProductCode($orders[$i]['product_id'])));
                $objPHPExcel->getActiveSheet(0)->setCellValue('L' . ($i + 2), $orders[$i]['quantity']);
                $objPHPExcel->getActiveSheet(0)->setCellValue('M' . ($i + 2), $orders[$i]['price']);
                $objPHPExcel->getActiveSheet(0)->setCellValue('N' . ($i + 2), $orders[$i]['quantity'] * $orders[$i]['price']);
                $objPHPExcel->getActiveSheet(0)->setCellValue('O' . ($i + 2), '');
                $objPHPExcel->getActiveSheet()->getStyle('A' . ($i + 2) . ':Q' . ($i + 2))->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $objPHPExcel->getActiveSheet()->getStyle('A' . ($i + 2) . ':Q' . ($i + 2))->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
                $objPHPExcel->getActiveSheet()->getRowDimension($i + 2)->setRowHeight(20);
                $order_id = $orders[$i]['order_id'];
            }
            
            // Rename sheet
//             $objPHPExcel->getActiveSheet()->setTitle('订单收货地址');
            
            // Set active sheet index to the first sheet, so Excel opens this as the first sheet
//             $objPHPExcel->setActiveSheetIndex(0);
            
            if($this->model_sale_order->updateOrderStatus($order_ids, 18, $this->session->data['splitting_code'])) {
                $orders = $this->model_sale_order->insertOrderlog($order_ids, 18, "收货地址导出");
            }
            if (isset($_GET['type'])) {
//              // 输出
                header('Content-Type: application/vnd.ms-excel');
                header('Content-Disposition: attachment;filename="' . date("Y-m-d H:i:s") . '.xls"');
                header('Cache-Control: max-age=0');
                
                $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
                $objWriter->save('php://output');
            }
            
            $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
            $path = APP_PATH . "/runtime/cache/order/".time().".xlsx";
            $objWriter->save($path);
        }catch (Exception $e) {
            $this->db->query("ROLLBACK");
            var_dump($e);
        }finally {
            $this->db->query("COMMIT");
        }
        return  $path;
        //$this->response->redirect($this->url->link('sale/hz_order', 'token=' . $this->session->data['token'], 'SSL'));
    }
    
    public function ajaxEdit() {
        $json = [];
        Execution::autoTransaction(Yii::$app->db, function() use (&$json) {
            if(!empty($this->request->get['shipping_id'])) {
                $this->load->model('sale/order');
                $this->model_sale_order->editHzOrder((int)$this->request->get['order_id'], $this->request->get);
                $json['success'] = "保存成功";
            }else{
                $json['error'] = "订单号不能为空";
            }
        });
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    
    public function shipping() {
        /* 接收参数 */
        $order_ids = $this->request->post['selected'];
        if(!isset($this->request->post['selected']) || Predicates::isEmpty($order_ids)) {
            $json['error'] = "订单号不能为空";
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode($json));
        }
        Execution::autoTransaction(Yii::$app->db, function() use ($order_ids) {
            $db = new DataBase();
            $result = $db->doTableUpdate('order', ['order_status_id' => 7], ['in', 'order_id', $order_ids]);
            $resultSplitting = $db->doTableUpdate('order_splitting', ['order_status_id' => 7], " order_id in (:o_id) AND code = :code", [':o_id' => $order_ids, ':code' => (int)$this->session->data['splitting_code']]);
            if($result && $resultSplitting) {
                foreach ($order_ids as $order_id) {
                    $orderInfo = $db->getTableFields('order', "order_id = :o_id", [":o_id" => $order_id], ['order_number', 'customer_id']);
                    $payload = [
                        'uid' => $orderInfo['customer_id'],
                        'message' => '您的订单已发货，点击查看详情',
                        'type' => MiPush::TO_EXPRESS,
                        'payload' => ['type' => 2, 'order_id' => $order_id]
                    ];
                    MiPush::submitWorks($payload);
                    Order::insertOrderLog($order_id, ['status' => 7, 'status_desc' => "已发货", "user_type" => 0], $this->user->getId());
                }
            }
        });
        $this->response->redirect($this->url->link('sale/hz_order', 'token=' . $this->session->data['token'], 'SSL'));
    }
    
    public function parcleComboAction() {
        Execution::autoUnlink(function($unlink) {
            if($this->request->post) {
                $order_ids = $this->request->post['selected'];
            }else {
                $order_ids = [$this->request->get['order_id']];
            }
            $excelPath = self::excel($order_ids);
            $orderId = array_reduce((new Query())->select("order_id, order_number")->from("order")->where(["order_id" => $order_ids])->all(), function($carry, $order) { 
                $carry[$order["order_id"]] = $order["order_number"]; 
                return $carry;
            }, []);
            $parcles = array_reduce((new Query())->select("parcle, order_id")->from("order_splitting")->where(["order_id" => array_keys($orderId), "code" => (int)$this->session->data['splitting_code']])->all(), function($carry, $parcle) use ($orderId) { 
                $carry[$orderId[$parcle["order_id"]]] = $parcle["parcle"];
                return $carry;
            }, []);
            $download = [];
            $output = [];
            array_walk($parcles, function(&$parcle, $orderNumber) use (&$output, &$download) {
                if (Predicates::isNotEmpty($parcle)) {
                    $download[$orderNumber] = "/order/parcel/$parcle.zip"; 
                } else {
                    $output["$orderNumber.zip"] = APP_PATH . "/resources/empty.dat";
                }
            });
            $download = ContentCache::loadAll($download);
            array_walk($download, function(&$file, $orderNumber) use (&$output, $unlink) {
                $unlink($file);
                $output["$orderNumber.zip"] = $file;
            });
            $output["excel.xlsx"] = $excelPath;
            Zipper::zip($output, NULL,  uuid_create());
            /*
            $targetFile = "/order/combo/" . date("Ymd", time()) . "/" . uuid_create() . ".zip";
            $result = CosFile::uploadTo($tmpFile, $targetFile);
             */
            if($this->model_sale_order->updateOrderStatus($order_ids, 20, $this->session->data['splitting_code'])) {
                $orders = $this->model_sale_order->insertOrderlog($order_ids, 20, "照片已下载");
            }
        });
    }
    
    public function setLimitAction(){
        if(Order::setLimitAdmin(Accessor::either($this->request->get['limit'], 20))) {
            $json['success'] = "保存成功";
        }else{
            $json['error'] = "订单号不能为空";
        }
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    } 
}
