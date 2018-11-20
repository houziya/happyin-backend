<?php
class ControllerCatalogPrint extends Controller
{
    private $error = array();
    
    public function index()
    {
        $this->language->load('catalog/print');
    
        $this->document->setTitle('冲印类型');
    
        $this->load->model('catalog/print');
    
        $this->getList();
    }
    
    public function add()
    {
        $this->language->load('catalog/print');
        $this->document->setTitle('冲印类型');
        $this->load->model('catalog/print');
        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
            $this->model_catalog_print->addPrint($this->request->post);
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
            $this->response->redirect($this->url->link('catalog/print', 'token=' . $this->session->data['token'] . $url, 'SSL'));
        }
        $this->getForm();
    }
    
    protected function validateForm()
    {
        if (!$this->user->hasPermission('modify', 'catalog/print')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }
        return !$this->error;
    }
    
    protected function getList()
    {
        if (isset($this->request->get['sort'])) {
            $sort = $this->request->get['sort'];
        } else {
            $sort = 'p.print_id';
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
                'text' => '冲印类型',
                'href' => $this->url->link('catalog/print', 'token=' . $this->session->data['token'] . $url, 'SSL')
        );
    
        $data['add'] = $this->url->link('catalog/print/add', 'token=' . $this->session->data['token'] . $url, 'SSL');
        $data['copy'] = $this->url->link('catalog/print/copy', 'token=' . $this->session->data['token'] . $url, 'SSL');
        $data['delete'] = $this->url->link('catalog/print/delete', 'token=' . $this->session->data['token'] . $url, 'SSL');
    
        $data['prints'] = array();
    
        $filter_data = array(
                'sort' => $sort,
                'order' => $order,
                'start' => ($page - 1) * $this->config->get('config_limit_admin'),
                'limit' => $this->config->get('config_limit_admin')
        );
    
        $this->load->model('tool/image');
    
        $product_total = $this->model_catalog_print->getTotalPrints($filter_data);
    
        $results = $this->model_catalog_print->getPrints($filter_data);
        
        foreach ($results as $result) {
            $data['prints'][] = array(
                    'print_id' => $result['print_id'],
                    'name'       => $result['name'],
                    'size'      => $result['size'],
                    'quantity'   => $result['quantity'],
                    'edit'       => $this->url->link('catalog/print/edit', 'token=' . $this->session->data['token'] . '&print_id=' . $result['print_id'] . $url, 'SSL')
            );
        }
    
        $data['heading_title'] = $this->language->get('heading_title');
    
        $data['text_list'] = $this->language->get('text_list');
        $data['text_enabled'] = $this->language->get('text_enabled');
        $data['text_disabled'] = $this->language->get('text_disabled');
        $data['text_no_results'] = $this->language->get('text_no_results');
        $data['text_confirm'] = $this->language->get('text_confirm');
    
        $data['column_name'] = $this->language->get('column_name');
        $data['column_size'] = $this->language->get('column_size');
        $data['column_quantity'] = $this->language->get('column_quantity');
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
    
        
        $data['sort_order'] = $this->url->link('catalog/print', 'token=' . $this->session->data['token'] . '&sort=p.sort_order' . $url, 'SSL');
    
        $url = '';
    
        if (isset($this->request->get['sort'])) {
            $url .= '&sort=' . $this->request->get['sort'];
        }
    
        if (isset($this->request->get['order'])) {
            $url .= '&order=' . $this->request->get['order'];
        }
    
        $pagination = new Pagination();
        $pagination->total = $product_total;
        $pagination->page = $page;
        $pagination->limit = $this->config->get('config_limit_admin');
        $pagination->url = $this->url->link('catalog/print', 'token=' . $this->session->data['token'] . $url . '&page={page}', 'SSL');
    
        $data['pagination'] = $pagination->render();
        $data['results'] = sprintf($this->language->get('text_pagination'), ($product_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($product_total - $this->config->get('config_limit_admin'))) ? $product_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $product_total, ceil($product_total / $this->config->get('config_limit_admin')));
        $data['sort'] = $sort;
        $data['order'] = $order;
    
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');
    
        $this->response->setOutput($this->load->view('catalog/print_list.tpl', $data));
    }
    
    
    
    
    protected function getForm() 
    {
        $data['heading_title'] = '冲印类型';
        $data['text_form'] = !isset($this->request->get['print_id']) ? $this->language->get('text_add') : $this->language->get('text_edit');
        $data['text_enabled'] = $this->language->get('text_enabled');
        $data['text_disabled'] = $this->language->get('text_disabled');
        $data['text_none'] = $this->language->get('text_none');
        $data['text_yes'] = $this->language->get('text_yes');
        $data['text_no'] = $this->language->get('text_no');
        $data['text_plus'] = $this->language->get('text_plus');
        $data['text_minus'] = $this->language->get('text_minus');
        $data['text_default'] = $this->language->get('text_default');
        $data['text_option'] = $this->language->get('text_option');
        $data['text_option_value'] = $this->language->get('text_option_value');
        $data['text_select'] = $this->language->get('text_select');
        $data['text_percent'] = $this->language->get('text_percent');
        $data['text_amount'] = $this->language->get('text_amount');
    
        $data['entry_name'] = $this->language->get('entry_name');
        $data['entry_size'] = $this->language->get('entry_size');
        $data['entry_quantity'] = $this->language->get('entry_quantity');
        $data['entry_preview_image'] = $this->language->get('entry_preview_image');
        $data['entry_preview_size'] = $this->language->get('entry_preview_size');
        $data['entry_preview_area'] = $this->language->get('entry_preview_area');
        $data['entry_relation'] = $this->language->get('entry_relation');
        
        $data['button_save'] = $this->language->get('button_save');
        $data['button_cancel'] = $this->language->get('button_cancel');
        $data['button_attribute_add'] = $this->language->get('button_attribute_add');
        $data['button_option_add'] = $this->language->get('button_option_add');
        $data['button_option_value_add'] = $this->language->get('button_option_value_add');
        $data['button_discount_add'] = $this->language->get('button_discount_add');
        $data['button_special_add'] = $this->language->get('button_special_add');
        $data['button_image_add'] = $this->language->get('button_image_add');
        $data['button_remove'] = $this->language->get('button_remove');
        $data['button_recurring_add'] = $this->language->get('button_recurring_add');
    
        $data['tab_general'] = $this->language->get('tab_general');
        
    
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
    
        if (isset($this->error['model'])) {
            $data['error_model'] = $this->error['model'];
        } else {
            $data['error_model'] = '';
        }
    
        if (isset($this->error['keyword'])) {
            $data['error_keyword'] = $this->error['keyword'];
        } else {
            $data['error_keyword'] = '';
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
                'href' => $this->url->link('catalog/print', 'token=' . $this->session->data['token'] . $url, 'SSL')
        );
        if (!isset($this->request->get['print_id'])) {
            $data['action'] = $this->url->link('catalog/print/add', 'token=' . $this->session->data['token'] . $url, 'SSL');
        } else {
            $data['action'] = $this->url->link('catalog/print/edit', 'token=' . $this->session->data['token'] . '&print_id=' . $this->request->get['print_id'] . $url, 'SSL');
        }
        $data['cancel'] = $this->url->link('catalog/print', 'token=' . $this->session->data['token'] . $url, 'SSL');
        if (isset($this->request->get['print_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
            $print_info = $this->model_catalog_print->getPrint($this->request->get['print_id']);
        }
        $data['token'] = $this->session->data['token'];
        $this->load->model('localisation/language');
        $data['languages'] = $this->model_localisation_language->getLanguages();
        if (isset($this->request->post['print_name'])) {
            $data['print_name'] = $this->request->post['print_name'];
        } elseif (!empty($print_info)) {
            $data['print_name'] = $print_info['name'];
        } else {
            $data['print_name'] = '';
        }
        if (isset($this->request->post['print_size'])) {
            $data['print_size'] = $this->request->post['print_size'];
        } elseif (!empty($print_info)) {
            $data['print_size'] = $print_info['size'];
        } else {
            $data['print_size'] = '';
        }
        
        if (isset($this->request->post['print_quantity'])) {
            $data['print_quantity'] = $this->request->post['print_quantity'];
        } elseif (!empty($print_info)) {
            $data['print_quantity'] = $print_info['quantity'];
        } else {
            $data['print_quantity'] = '';
        }
        if (isset($this->request->post['preview_image'])) {
            $data['preview_image'] = $this->request->post['preview_image'];
        } elseif (!empty($print_info)) {
            $preview_info = json_decode($print_info['preview'], true);
            $data['preview_image'] = $preview_info['image'];
        } else {
            $data['preview_image'] = '';
        }
        if (isset($this->request->post['preview_size'])) {
            $data['preview_size'] = $this->request->post['preview_size'];
        } elseif (!empty($print_info)) {
            $preview_info = json_decode($print_info['preview'], true);
            $data['preview_size'] = $preview_info['size'];
        } else {
            $data['preview_size'] = '';
        }
        if (isset($this->request->post['preview_area'])) {
            $data['preview_area'] = $this->request->post['preview_area'];
        } elseif (!empty($print_info)) {
            $preview_info = json_decode($print_info['preview'], true);
            $data['preview_area'] = $preview_info['area'];
        } else {
            $data['preview_area'] = '';
        }
        
        $this->load->model('tool/image');
        
        if (isset($this->request->post['preview_image']) && is_file(DIR_IMAGE . $this->request->post['preview_image'])) {
            $data['thumb'] = 'http://hipubdev-10006628.file.myqcloud.com/admin/images/'.$this->request->post['preview_image'].'jpg';//$this->model_tool_image->resize($this->request->post['image'], 100, 100);
        } elseif (!empty($preview_info['image'])) {
            $data['thumb'] = 'http://hipubdev-10006628.file.myqcloud.com/admin/images/'.$preview_info['image'].'.jpg';//$this->model_tool_image->resize($manufacturer_info['image'], 100, 100);
        } else {
            $data['thumb'] = $this->model_tool_image->resize('no_image.png', 100, 100);
        }
        
        $data['placeholder'] = $this->model_tool_image->resize('no_image.png', 100, 100);
        
        if (isset($this->request->post['product_category'])) {
            $print_products = $this->request->post['product_category'];
        } elseif (isset($this->request->get['print_id'])) {
            $print_products = $this->model_catalog_print->getPrintProduct($this->request->get['print_id']);
        } else {
            $print_products = array();
        }
        $data['product_category'] = array();
        $this->load->model('catalog/product');
        if ($print_products) {
            foreach ($print_products as $proudct_id) {
                $product_info = $this->model_catalog_product->getProduct($proudct_id['product_id']);
                if ($product_info) {
                    $data['print_products'][] = array(
                            'product_id' => $product_info['product_id'],
                            'name' => $product_info['name']
                    );
                }
            }
        }
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');
        $this->response->setOutput($this->load->view('catalog/print_form.tpl', $data));
    }
    
    public function autocomplete()
    {
        $json = array();
        if (isset($this->request->get['filter_name']) || isset($this->request->get['filter_model'])) {
            $this->load->model('catalog/product');
            if (isset($this->request->get['filter_name'])) {
                $filter_name = $this->request->get['filter_name'];
            } else {
                $filter_name = '';
            }
            if (isset($this->request->get['filter_model'])) {
                $filter_model = $this->request->get['filter_model'];
            } else {
                $filter_model = '';
            }
            if (isset($this->request->get['limit'])) {
                $limit = $this->request->get['limit'];
            } else {
                $limit = 100;
            }
            $filter_data = array(
                    'filter_name'  => $filter_name,
                    'filter_model' => $filter_model,
                    'start'        => 0,
                    'limit'        => $limit
            );
            $results = $this->model_catalog_product->getProducts($filter_data);
            foreach ($results as $result) {
                $json[] = array(
                        'product_id' => $result['product_id'],
                        'name'       => strip_tags(html_entity_decode($result['name'], ENT_QUOTES, 'UTF-8')),
                        'model'      => $result['model'],
                        'price'      => $result['price']
                );
            }
        }
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    
    
    public function edit()
    {
        $this->language->load('catalog/print');
        $this->document->setTitle($this->language->get('heading_title'));
        $this->load->model('catalog/print');
        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
            $this->model_catalog_print->editPrint($this->request->get['print_id'], $this->request->post);
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
    
            $this->response->redirect($this->url->link('catalog/print', 'token=' . $this->session->data['token'] . $url, 'SSL'));
        }
    
        $this->getForm();
    }
    
    
    public function delete() {
        $this->language->load('catalog/print');
        $this->document->setTitle($this->language->get('heading_title'));
        $this->load->model('catalog/print');
    
        if (isset($this->request->post['selected']) && $this->validateDelete()) {
            foreach ($this->request->post['selected'] as $print) {
                $this->model_catalog_print->deletePrint($print);
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
            $this->response->redirect($this->url->link('catalog/print', 'token=' . $this->session->data['token'] . $url, 'SSL'));
        }
    
        $this->getList();
    }
    
    protected function validateDelete()
    {
        if (!$this->user->hasPermission('modify', 'catalog/print')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }
        return !$this->error;
    }
}