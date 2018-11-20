<?php
class ModelPartsParts extends Model {
	public function addParts($data) {
	    $products = [];
	    if (empty($data['parts_name'])) {
	        $data['status'] = 0;
	    }
		$this->db->query("INSERT INTO " . DB_PREFIX . "parts SET title = '" 
		    . $this->db->escape($data['parts_name']) 
		    . "', spec = '" . $this->db->escape($data['spec']) 
		    . "', price = '" . $this->db->escape($data['price']) 
		    . "', quantity = '" . $this->db->escape($data['quantity']) 
		    . "', status = '" . $this->db->escape($data['status']) 
		    . "', numbering = '" . $data['numbering']. "'"
		    );

		$parts_id = $this->db->getLastId();
		$i = 1;
		foreach ($data as $k => $v) {
		    if (strpos($k, "Id")) {
		        $products[][$v] = $data['delNum' . explode('Id', $k)[1]];
		        $i++;
		    }
		}
		foreach ($products as $k => $v) {
		    foreach ($v as $key => $val) {
		        $this->db->query("INSERT INTO " . DB_PREFIX . "parts_product SET 
		            parts_id = '" . $parts_id
		            . "', product_id = '" . $key
		            . "', deduction_number = '" . $val
		            . "'"
		            );
		        $parts_product_id = $this->db->getLastId();
		    }
		}
		return $parts_product_id;
	}

	public function editParts($parts_id, $data) {

	    $products = [];
	    if (empty($data['parts_name'])) {
	        $data['status'] = 0;
	    }
	    $this->db->query("DELETE FROM " . DB_PREFIX . "parts WHERE parts_id = '" . (int)$parts_id . "'");
	    $this->db->query("DELETE FROM " . DB_PREFIX . "parts_product WHERE parts_id = '" . (int)$parts_id . "'");
	    $this->db->getLastId();
	    $this->db->query("INSERT INTO " . DB_PREFIX . "parts SET title = '"
	        . $this->db->escape($data['parts_name'])
	        . "', spec = '" . $this->db->escape($data['spec'])
	        . "', price = '" . $this->db->escape($data['price'])
	        . "', quantity = '" . $this->db->escape($data['quantity'])
	        . "', status = '" . $this->db->escape($data['status'])
	        . "', numbering = '" . $data['numbering']. "'"
	        );
	    $parts_id = $this->db->getLastId();
	    $i = 1;
	    foreach ($data as $k => $v) {
	        if (strpos($k, "Id")) {
	            $products[][$v] = $data['delNum' . explode('Id', $k)[1]];
	            $i++;
	        }
	    }
	    foreach ($products as $k => $v) {
	        foreach ($v as $key => $val) {
	            if (!empty($val) && !empty($key)) {
	                $this->db->query("INSERT INTO " . DB_PREFIX . "parts_product SET
		            parts_id = '" . $parts_id
	                    . "', product_id = '" . $key
	                    . "', deduction_number = '" . $val
	                    . "'"
	                    );
	                $parts_product_id = $this->db->getLastId();
	            }
	        }
	    }
        return $parts_product_id;
	}

	public function copyProduct($product_id) {
		$query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) WHERE p.product_id = '" . (int)$product_id . "' AND pd.language_id = '" . (int)$this->config->get('config_language_id') . "'");

		if ($query->num_rows) {
			$data = $query->row;

			$data['sku'] = '';
			$data['upc'] = '';
			$data['viewed'] = '0';
			$data['keyword'] = '';
			$data['status'] = '0';

			$data['product_attribute'] = $this->getProductAttributes($product_id);
			$data['product_description'] = $this->getProductDescriptions($product_id);
			$data['product_discount'] = $this->getProductDiscounts($product_id);
			$data['product_filter'] = $this->getProductFilters($product_id);
			$data['product_image'] = $this->getProductImages($product_id);
			$data['product_option'] = $this->getProductOptions($product_id);
			$data['product_related'] = $this->getProductRelated($product_id);
			$data['product_reward'] = $this->getProductRewards($product_id);
			$data['product_special'] = $this->getProductSpecials($product_id);
			$data['product_category'] = $this->getProductCategories($product_id);
			$data['product_download'] = $this->getProductDownloads($product_id);
			$data['product_layout'] = $this->getProductLayouts($product_id);
			$data['product_store'] = $this->getProductStores($product_id);
			$data['product_recurrings'] = $this->getRecurrings($product_id);

			$this->addProduct($data);
		}
	}

	public function deleteParts($parts_id) {
		$this->db->query("DELETE FROM " . DB_PREFIX . "parts WHERE parts_id = '" . (int)$parts_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "parts_product WHERE parts_id = '" . (int)$parts_id . "'");
	}

	public function getProduct($product_id) {
		$query = $this->db->query("SELECT DISTINCT *, (SELECT keyword FROM " . DB_PREFIX . "url_alias WHERE query = 'product_id=" . (int)$product_id . "') AS keyword FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) WHERE p.product_id = '" . (int)$product_id . "' AND pd.language_id = '" . (int)$this->config->get('config_language_id') . "'");
		return $query->row;
	}
	
	public function getPartsInfo($parts_id) {
	    $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "parts p WHERE p.status = 1 AND p.parts_id =" . (int)$parts_id);
	    return $query->row;
	}
	
	public function getPartsproductInfo($parts_id) {
	    $query = $this->db->query("SELECT p.product_id, p.deduction_number, pd.name FROM " . DB_PREFIX . "parts_product p 
	        LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) WHERE pd.language_id = 2 AND p.parts_id =" . (int)$parts_id);
	    return $query->rows;
	}
	public function getProductsByProductIds($product_ids, $fields = "") {
	    if(empty($product_ids)) {
	        return [];
	    }
	    if(empty($fields)) {
	        $fields = "*";
	    }elseif(is_array($fields)) {
	        $fields = implode(",", $fields);
	    }
	    $product_ids = implode(',', $product_ids);
	    $query = $this->db->query("SELECT " . $fields . ", (SELECT keyword FROM " . DB_PREFIX . "url_alias WHERE query = 'product_id in (" . $product_ids . ")') AS keyword FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) WHERE p.product_id in (" . $product_ids . ") AND pd.language_id = '" . (int)$this->config->get('config_language_id') . "'");
	    return $query->rows;
	}
	public function getParts($data = array()) {
        $sql = "SELECT * FROM " . DB_PREFIX . "parts p LEFT JOIN " . DB_PREFIX . "parts_product pr ON (p.parts_id = pr.parts_id) 
            WHERE status in (1, 0)";
		if (!empty($data['filter_name'])) {
			$sql .= " AND p.title LIKE '" . $this->db->escape($data['filter_name']) . "%'";
		}

		if (!empty($data['filter_model'])) {
			$sql .= " AND pr.product_id LIKE '" . $this->db->escape($data['filter_model']) . "%'";
		}

		if (isset($data['filter_price']) && !is_null($data['filter_price'])) {
			$sql .= " AND p.price LIKE '" . $this->db->escape($data['filter_price']) . "%'";
		}

		if (isset($data['filter_quantity']) && !is_null($data['filter_quantity'])) {
			$sql .= " AND p.quantity = '" . (int)$data['filter_quantity'] . "'";
		}

		if (isset($data['filter_status']) && !is_null($data['filter_status'])) {
			$sql .= " AND p.status = '" . (int)$data['filter_status'] . "'";
		}

		$sql .= " GROUP BY p.parts_id";

		$sort_data = array(
			'title',
			'parts_id',
			'price',
			'quantity',
			'status',
			'sort_order'
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY p.parts_id";
		}

		if (isset($data['order']) && ($data['order'] == 'DESC')) {
			$sql .= " DESC";
		} else {
			$sql .= " DESC";
		}

		if (isset($data['start']) || isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}

			if ($data['limit'] < 1) {
				$data['limit'] = 20;
			}

			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}
		$query = $this->db->query($sql);

		return $query->rows;
	}

	public function getProductsByCategoryId($category_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) LEFT JOIN " . DB_PREFIX . "product_to_category p2c ON (p.product_id = p2c.product_id) WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND p2c.category_id = '" . (int)$category_id . "' ORDER BY pd.name ASC");

		return $query->rows;
	}

	public function getProductDescriptions($product_id) {
		$product_description_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_description WHERE product_id = '" . (int)$product_id . "'");

		foreach ($query->rows as $result) {
			$product_description_data[$result['language_id']] = array(
				'name'             => $result['name'],
				'description'      => $result['description'],
				'meta_title'       => $result['meta_title'],
				'meta_description' => $result['meta_description'],
				'meta_keyword'     => $result['meta_keyword'],
				'tag'              => $result['tag']
			);
		}

		return $product_description_data;
	}

	public function getProductCategories($product_id) {
		$product_category_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_to_category WHERE product_id = '" . (int)$product_id . "'");

		foreach ($query->rows as $result) {
			$product_category_data[] = $result['category_id'];
		}

		return $product_category_data;
	}

	public function getProductFilters($product_id) {
		$product_filter_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_filter WHERE product_id = '" . (int)$product_id . "'");

		foreach ($query->rows as $result) {
			$product_filter_data[] = $result['filter_id'];
		}

		return $product_filter_data;
	}

	public function getProductAttributes($product_id) {
		$product_attribute_data = array();

		$product_attribute_query = $this->db->query("SELECT attribute_id FROM " . DB_PREFIX . "product_attribute WHERE product_id = '" . (int)$product_id . "' GROUP BY attribute_id");

		foreach ($product_attribute_query->rows as $product_attribute) {
			$product_attribute_description_data = array();

			$product_attribute_description_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_attribute WHERE product_id = '" . (int)$product_id . "' AND attribute_id = '" . (int)$product_attribute['attribute_id'] . "'");

			foreach ($product_attribute_description_query->rows as $product_attribute_description) {
				$product_attribute_description_data[$product_attribute_description['language_id']] = array('text' => $product_attribute_description['text']);
			}

			$product_attribute_data[] = array(
				'attribute_id'                  => $product_attribute['attribute_id'],
				'product_attribute_description' => $product_attribute_description_data
			);
		}

		return $product_attribute_data;
	}

	public function getProductOptions($product_id) {
		$product_option_data = array();

		$product_option_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "product_option` po LEFT JOIN `" . DB_PREFIX . "option` o ON (po.option_id = o.option_id) LEFT JOIN `" . DB_PREFIX . "option_description` od ON (o.option_id = od.option_id) WHERE po.product_id = '" . (int)$product_id . "' AND od.language_id = '" . (int)$this->config->get('config_language_id') . "'");

		foreach ($product_option_query->rows as $product_option) {
			$product_option_value_data = array();

			$product_option_value_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_option_value WHERE product_option_id = '" . (int)$product_option['product_option_id'] . "'");

			foreach ($product_option_value_query->rows as $product_option_value) {
				$product_option_value_data[] = array(
					'product_option_value_id' => $product_option_value['product_option_value_id'],
					'option_value_id'         => $product_option_value['option_value_id'],
					'quantity'                => $product_option_value['quantity'],
					'subtract'                => $product_option_value['subtract'],
					'price'                   => $product_option_value['price'],
					'price_prefix'            => $product_option_value['price_prefix'],
					'points'                  => $product_option_value['points'],
					'points_prefix'           => $product_option_value['points_prefix'],
					'weight'                  => $product_option_value['weight'],
					'weight_prefix'           => $product_option_value['weight_prefix']
				);
			}

			$product_option_data[] = array(
				'product_option_id'    => $product_option['product_option_id'],
				'product_option_value' => $product_option_value_data,
				'option_id'            => $product_option['option_id'],
				'name'                 => $product_option['name'],
				'type'                 => $product_option['type'],
				'value'                => $product_option['value'],
				'required'             => $product_option['required']
			);
		}

		return $product_option_data;
	}

	public function getProductOptionValue($product_id, $product_option_value_id) {
		$query = $this->db->query("SELECT pov.option_value_id, ovd.name, pov.quantity, pov.subtract, pov.price, pov.price_prefix, pov.points, pov.points_prefix, pov.weight, pov.weight_prefix FROM " . DB_PREFIX . "product_option_value pov LEFT JOIN " . DB_PREFIX . "option_value ov ON (pov.option_value_id = ov.option_value_id) LEFT JOIN " . DB_PREFIX . "option_value_description ovd ON (ov.option_value_id = ovd.option_value_id) WHERE pov.product_id = '" . (int)$product_id . "' AND pov.product_option_value_id = '" . (int)$product_option_value_id . "' AND ovd.language_id = '" . (int)$this->config->get('config_language_id') . "'");

		return $query->row;
	}

	public function getProductImages($product_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_image WHERE product_id = '" . (int)$product_id . "' ORDER BY sort_order ASC");

		return $query->rows;
	}

	public function getProductDiscounts($product_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_discount WHERE product_id = '" . (int)$product_id . "' ORDER BY quantity, priority, price");

		return $query->rows;
	}

	public function getProductSpecials($product_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_special WHERE product_id = '" . (int)$product_id . "' ORDER BY priority, price");

		return $query->rows;
	}

	public function getProductRewards($product_id) {
		$product_reward_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_reward WHERE product_id = '" . (int)$product_id . "'");

		foreach ($query->rows as $result) {
			$product_reward_data[$result['customer_group_id']] = array('points' => $result['points']);
		}

		return $product_reward_data;
	}

	public function getProductDownloads($product_id) {
		$product_download_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_to_download WHERE product_id = '" . (int)$product_id . "'");

		foreach ($query->rows as $result) {
			$product_download_data[] = $result['download_id'];
		}

		return $product_download_data;
	}

	public function getProductStores($product_id) {
		$product_store_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_to_store WHERE product_id = '" . (int)$product_id . "'");

		foreach ($query->rows as $result) {
			$product_store_data[] = $result['store_id'];
		}

		return $product_store_data;
	}

	public function getProductLayouts($product_id) {
		$product_layout_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_to_layout WHERE product_id = '" . (int)$product_id . "'");

		foreach ($query->rows as $result) {
			$product_layout_data[$result['store_id']] = $result['layout_id'];
		}

		return $product_layout_data;
	}

	public function getProductRelated($product_id) {
		$product_related_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_related WHERE product_id = '" . (int)$product_id . "'");

		foreach ($query->rows as $result) {
			$product_related_data[] = $result['related_id'];
		}

		return $product_related_data;
	}

	public function getRecurrings($product_id) {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "product_recurring` WHERE product_id = '" . (int)$product_id . "'");
		return $query->rows;
	}

	public function getProducts($data = array()) {
	    $sql = "SELECT * FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "'";
	
	    if (!empty($data['filter_name'])) {
	        $sql .= " AND pd.name LIKE '" . $this->db->escape($data['filter_name']) . "%'";
	    }
	
	    if (!empty($data['filter_model'])) {
	        $sql .= " AND p.model LIKE '" . $this->db->escape($data['filter_model']) . "%'";
	    }
	
	    if (isset($data['filter_price']) && !is_null($data['filter_price'])) {
	        $sql .= " AND p.price LIKE '" . $this->db->escape($data['filter_price']) . "%'";
	    }
	
	    if (isset($data['filter_quantity']) && !is_null($data['filter_quantity'])) {
	        $sql .= " AND p.quantity = '" . (int)$data['filter_quantity'] . "'";
	    }
	
	    if (isset($data['filter_status']) && !is_null($data['filter_status'])) {
	        $sql .= " AND p.status = '" . (int)$data['filter_status'] . "'";
	    }
	
	    $sql .= " GROUP BY p.product_id";
	
	    $sort_data = array(
	        'pd.name',
	        'p.model',
	        'p.price',
	        'p.quantity',
	        'p.status',
	        'p.sort_order'
	    );
	
	    if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
	        $sql .= " ORDER BY " . $data['sort'];
	    } else {
	        $sql .= " ORDER BY pd.name";
	    }
	
	    if (isset($data['order']) && ($data['order'] == 'DESC')) {
	        $sql .= " DESC";
	    } else {
	        $sql .= " ASC";
	    }
	
	    if (isset($data['start']) || isset($data['limit'])) {
	        if ($data['start'] < 0) {
	            $data['start'] = 0;
	        }
	
	        if ($data['limit'] < 1) {
	            $data['limit'] = 20;
	        }
	
	        $sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
	    }
	    $query = $this->db->query($sql);
	
	    return $query->rows;
	}
	
	public function getTotalParts($data = array()) {
// 		$sql = "SELECT COUNT(DISTINCT p.product_id) AS total FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id)";
		$sql = "SELECT COUNT(DISTINCT parts_id) AS total FROM " . DB_PREFIX . "parts WHERE status = 1";
		if (!empty($data['filter_name'])) {
			$sql .= " AND title LIKE '" . $this->db->escape($data['filter_name']) . "%'";
		}
		
// 		if (!empty($data['filter_model'])) {
// 			$sql .= " AND p.model LIKE '" . $this->db->escape($data['filter_model']) . "%'";
// 		}

		if (isset($data['filter_price']) && !is_null($data['filter_price'])) {
			$sql .= " AND price LIKE '" . $this->db->escape($data['filter_price']) . "%'";
		}

		if (isset($data['filter_quantity']) && !is_null($data['filter_quantity'])) {
			$sql .= " AND quantity = '" . (int)$data['filter_quantity'] . "'";
		}

		if (isset($data['filter_status']) && !is_null($data['filter_status'])) {
			$sql .= " AND status = '" . (int)$data['filter_status'] . "'";
		}

		$query = $this->db->query($sql);

		return $query->row['total'];
	}

	public function getTotalProductsByTaxClassId($tax_class_id) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "product WHERE tax_class_id = '" . (int)$tax_class_id . "'");

		return $query->row['total'];
	}

	public function getTotalProductsByStockStatusId($stock_status_id) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "product WHERE stock_status_id = '" . (int)$stock_status_id . "'");

		return $query->row['total'];
	}

	public function getTotalProductsByWeightClassId($weight_class_id) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "product WHERE weight_class_id = '" . (int)$weight_class_id . "'");

		return $query->row['total'];
	}

	public function getTotalProductsByLengthClassId($length_class_id) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "product WHERE length_class_id = '" . (int)$length_class_id . "'");

		return $query->row['total'];
	}

	public function getTotalProductsByDownloadId($download_id) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "product_to_download WHERE download_id = '" . (int)$download_id . "'");

		return $query->row['total'];
	}

	public function getTotalProductsByManufacturerId($manufacturer_id) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "product WHERE manufacturer_id = '" . (int)$manufacturer_id . "'");

		return $query->row['total'];
	}

	public function getTotalProductsByAttributeId($attribute_id) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "product_attribute WHERE attribute_id = '" . (int)$attribute_id . "'");

		return $query->row['total'];
	}

	public function getTotalProductsByOptionId($option_id) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "product_option WHERE option_id = '" . (int)$option_id . "'");

		return $query->row['total'];
	}

	public function getTotalProductsByProfileId($recurring_id) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "product_recurring WHERE recurring_id = '" . (int)$recurring_id . "'");

		return $query->row['total'];
	}

	public function getTotalProductsByLayoutId($layout_id) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "product_to_layout WHERE layout_id = '" . (int)$layout_id . "'");

		return $query->row['total'];
	}
	
	public function getWarningProductNum($partsId)
	{
	    $sql= "SELECT VALUE as total FROM `".DB_PREFIX."setting` WHERE CODE='config' AND setting.key='config_stock_warning' AND VALUE !=0";
	    $query = $this->db->query($sql);
	    return intval($query->row['total']);
	}
	
	public function getProductParent($product_id = 0){
	    //$sql= "SELECT ep.parent_product_id,p.model FROM `".DB_PREFIX."extend_to_product` ep left join product p on p.product_id = ep.parent_product_id WHERE ep.product_id = '" . (int)$product_id . "'";
	    $sql = "SELECT product_id as parent_product_id, model FROM `".DB_PREFIX."product` where product_id = '" . (int)$product_id . "'";
	    $query = $this->db->query($sql);
	    return $query->rows;
	}
}
