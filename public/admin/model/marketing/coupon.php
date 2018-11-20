<?php
class ModelMarketingCoupon extends Model {
	public function addCoupon($data)
	{
	    /* 处理打包优惠劵 */
	    if (!empty($data['entry_total_amount']) && !empty($data['entry_max_value'])) {
	        $packageCoupon = json_encode(['amount' => $data['entry_total_amount'], 'value' => $data['entry_max_value']]);
	    } else {
	        $packageCoupon = '';
	    }
		$this->event->trigger('pre.admin.coupon.add', $data);
		/* 如果code 为空 */
		if (empty($data['code'])) {
		    $data['code'] = 'rd_' . $data['payload'] . '_' . substr(uuid_create(), 0, 5);
		}
        if ($data['type'] == 'P' || $data['type'] == 'F') {
            if ($data['use_type'] == 0) {
                $sql = "INSERT INTO " . DB_PREFIX . "coupon SET name = '" . $this->db->escape($data['name']) . "', payload='" . $packageCoupon . "', code = '" . $this->db->escape($data['code']) . "', discount = '" . (float)$data['discount'] . "', type = '" . $this->db->escape($data['type']) . "', property = 0,  total = '" . (float)$data['total'] . "', logged = '" . (int)$data['payload'] . "', shipping = '" . (int)$data['shipping'] . "', city_code = '" . (int)$data['city_code'] . "', date_start = '" . $this->db->escape($data['date_start']) . "', date_end = '" . $this->db->escape($data['date_end']) . "', uses_total = '" . (int)$data['uses_total'] . "', uses_customer = '', status = 0, date_added = NOW(), nums = ".$data['nums'].", use_type = '{$data['use_type']}', use_start = '".$this->db->escape($data['use_start'])."', use_end = '".$this->db->escape($data['use_end'])."', channel = '{$data['channel']}'";
            } else {
                $sql = "INSERT INTO " . DB_PREFIX . "coupon SET name = '" . $this->db->escape($data['name']) . "', payload='" . $packageCoupon . "', code = '" . $this->db->escape($data['code']) . "', discount = '" . (float)$data['discount'] . "', type = '" . $this->db->escape($data['type']) . "', property = 0,  total = '" . (float)$data['total'] . "', logged = '" . (int)$data['payload'] . "', shipping = '" . (int)$data['shipping'] . "', city_code = '" . (int)$data['city_code'] . "', date_start = '" . $this->db->escape($data['date_start']) . "', date_end = '" . $this->db->escape($data['date_end']) . "', uses_total = '" . (int)$data['uses_total'] . "', uses_customer = '', status = 0, date_added = NOW(), nums = ".$data['nums'].", validity = ".$data['validity'].", use_type = '{$data['use_type']}', channel = '{$data['channel']}'";
            }
        } else {
            if ($data['use_type'] == 0) {
                $sql = "INSERT INTO " . DB_PREFIX . "coupon SET name = '" . $this->db->escape($data['name']) . "', payload='" . $packageCoupon . "', code = '" . $this->db->escape($data['code']) . "', discount = 0, type = 0, reduction = '" . (int)$data['discount'] . "', property = '" . $this->db->escape($data['type']) . "', total = '" . (float)$data['total'] . "', logged = '" . (int)$data['payload'] . "', shipping = '" . (int)$data['shipping'] . "', city_code = '" . (int)$data['city_code'] . "', date_start = '" . $this->db->escape($data['date_start']) . "', date_end = '" . $this->db->escape($data['date_end']) . "', uses_total = '" . (int)$data['uses_total'] . "', uses_customer = '', status = 0, date_added = NOW(), nums = ".$data['nums'].", use_type = '".$data['use_type']."', use_start = '".$this->db->escape($data['use_start'])."', use_end = '".$this->db->escape($data['use_end'])."', channel = '{$data['channel']}'";
            } else {
                $sql = "INSERT INTO " . DB_PREFIX . "coupon SET name = '" . $this->db->escape($data['name']) . "', payload='" . $packageCoupon . "', code = '" . $this->db->escape($data['code']) . "', discount = 0, type = 0, reduction = '" . (int)$data['discount'] . "', property = '" . $this->db->escape($data['type']) . "', total = '" . (float)$data['total'] . "', logged = '" . (int)$data['payload'] . "', shipping = '" . (int)$data['shipping'] . "', city_code = '" . (int)$data['city_code'] . "', date_start = '" . $this->db->escape($data['date_start']) . "', date_end = '" . $this->db->escape($data['date_end']) . "', uses_total = '" . (int)$data['uses_total'] . "', uses_customer = '', status = 0, date_added = NOW(), nums = ".$data['nums'].", validity = ".$data['validity'].", use_type = '".$data['use_type']."', channel = '{$data['channel']}'";
            }
        }
        $this->db->query($sql);
		$coupon_id = $this->db->getLastId();
		if ($data['payload'] == 4) {
		    if (empty(Yii::$app->redis->LRANGE(HI\Coupon\AMOUNT . $coupon_id, 0, -1))) {
    		    $value = explode(',', $data['entry_max_value']);
    		    $couponArr = $this->getRandCoupon($data['entry_total_amount'], $data['nums'], $value[1], $value[0]);
    		    /* 存储 */
    		    $this->doStorageRedis($couponArr, $coupon_id, $data['date_start'], $data['date_end']);
		    }
		}
		if ($data['payload'] == 1) {
		    $this->db->query("INSERT INTO " . DB_PREFIX . "customer_coupon SET coupon_id = '" . (int)$coupon_id . "', date_added = '" . date('Y-m-d H:i:s') . "', customer_id = '" . (int)$data['blind_user'] . "'");
		    $customer_coupon_id = $this->db->getLastId();
		    /* 组合payload json串 */
		    $payload = json_encode(['customer_coupon_id' => $customer_coupon_id]);
		    $this->db->query("UPDATE " . DB_PREFIX . "coupon SET payload = '" . $payload . "' where coupon_id = " . $coupon_id);
		}
		if (isset($data['coupon_product'])) {
			foreach ($data['coupon_product'] as $product_id) {
			    $this->doQueryCatalog($product_id, $coupon_id);
				$this->db->query("INSERT INTO " . DB_PREFIX . "coupon_product SET coupon_id = '" . (int)$coupon_id . "', product_id = '" . (int)$product_id . "'");
			}
		}

		if (isset($data['coupon_category'])) {
			foreach ($data['coupon_category'] as $category_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "coupon_category SET coupon_id = '" . (int)$coupon_id . "', category_id = '" . (int)$category_id . "'");
			}
		}

		$this->event->trigger('post.admin.coupon.add', $coupon_id);

		return $coupon_id;
	}
	
	public function doQueryCatalog($productId, $couponId)
	{
	    $sql = "SELECT p.product_id FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "' and p.isbn=".$productId;
	    $query = $this->db->query($sql)->rows;
	    if ($query) {
	        foreach($query as $v) {
	            $this->db->query("INSERT INTO " . DB_PREFIX . "coupon_product SET coupon_id = '" . (int)$couponId . "', product_id = '" . (int)$v['product_id'] . "'");
	        }
	    }
	    return true;
	}
	/* 查询父类商品类别 */
	public function doQuerySameCatalog($productId, $couponId)
	{
	    $sql = "SELECT p.product_id FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "' and p.isbn != '' and p.product_id=" . $productId;
	    $query = $this->db->query($sql)->row;
	    if ($query) {
	        return true;
	    }
	    return $this->doQueryCatalog($productId, $couponId);
	}
	
	/*查询用户是否存在  */
	public function doQueryCustomerExist($uid)
	{
	    $query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "customer WHERE customer_id = '" . (int)$uid . "'");
	    $data = $query->row;
	    if (empty($data)) {
	        return false;
	    }
	    return true;
	}
	
	/* 用户信息 */
	public function getUserInfo($couponId)
	{
	    $query = $this->db->query("SELECT payload FROM " . DB_PREFIX ." coupon WHERE coupon_id = '" . (int)$couponId . "'");
	    $payload = json_decode($query->row['payload'], true);
	    if (empty($payload)) {
	        return false;
	    }
	    $queryUser = $this->db->query("SELECT customer_id FROM " . DB_PREFIX ." customer_coupon WHERE id = '" . $payload['customer_coupon_id'] . "'");
	    if ($queryUser->row) {
	        return $queryUser->row['customer_id'];
	    }
	    return false;
	}
	
	public function getUserName($userId)
	{
	    $query = $this->db->query("SELECT username FROM " . DB_PREFIX ." user WHERE user_id = '" . (int)$userId . "'")->row;
	    return $query['username'];
	}

	public function editCoupon($coupon_id, $data)
	{
	    $userId = $_SESSION['default']['user_id'];
		$this->event->trigger('pre.admin.coupon.edit', $data);
		/* payload 退换货给用户指定 优惠劵*/
		if ($data['payload'] == 1) {
		    $query = $this->db->query("SELECT payload FROM " . DB_PREFIX . "coupon WHERE coupon_id = '" . (int)$coupon_id . "'");
		    $payload = json_decode($query->row['payload'], true);
		    $this->db->query("UPDATE " . DB_PREFIX . "customer_coupon SET customer_id =" . $data['blind_user']." where id = " . $payload['customer_coupon_id']);
		}
		/* 负责打包优惠劵 */
		if (!empty($data['entry_total_amount']) && !empty($data['entry_max_value'])) {
		    if(Yii::$app->redis->LRANGE(HI\Coupon\AMOUNT . $coupon_id, 0, -1)) {
		        Yii::$app->redis->DEL(HI\Coupon\AMOUNT . $coupon_id);
		        $value = explode(',', $data['entry_max_value']);
		        $couponArr = $this->getRandCoupon($data['entry_total_amount'], $data['nums'], $value[1], $value[0]);
		        /* 存储 */
		        $this->doStorageRedis($couponArr, $coupon_id, $data['date_start'], $data['date_end']);
		    }
		    $packageCoupon = json_encode(['amount' => $data['entry_total_amount'], 'value' => $data['entry_max_value']]);
		} else {
		    $packageCoupon = '';
		}
		/* 如果code 为空 */
		if (empty($data['code'])) {
		    $data['code'] = 'rd_' . $data['payload'] . '_' . substr(uuid_create(), 0, 5);
		}
		/* 真区间 discount  */
		if($data['type'] == 'P' || $data['type'] == 'F') {
		    if ($data['use_type'] == 0) { //自定义日期
		        $sql = "UPDATE " . DB_PREFIX . "coupon SET name = '" . $this->db->escape($data['name']) . "', payload='" . $packageCoupon . "', reduction = 0, property = 0, code = '" . $this->db->escape($data['code']) . "', discount = '" . (float)$data['discount'] . "', type = '" . $this->db->escape($data['type']) . "', total = '" . (float)$data['total'] . "', logged = '" . (int)$data['payload'] . "', shipping = '" . (int)$data['shipping'] . "', city_code = '" . (int)$data['city_code'] . "', date_start = '" . $this->db->escape($data['date_start']) . "', date_end = '" . $this->db->escape($data['date_end']) . "', uses_total = '" . (int)$data['uses_total'] . "', uses_customer = '" . $userId . "', status = '" . (int)$data['status'] . "', nums= '" . $data['nums'] . "', validity = 0, use_type = '{$data['use_type']}', use_start = '".$this->db->escape($data['use_start'])."', use_end = '".$this->db->escape($data['use_end'])."', channel = '" . $data['channel'] . "' WHERE coupon_id = '" . (int)$coupon_id . "'";
		    } else { //有效期
		        $sql = "UPDATE " . DB_PREFIX . "coupon SET name = '" . $this->db->escape($data['name']) . "', payload='" . $packageCoupon . "', reduction = 0, property = 0, code = '" . $this->db->escape($data['code']) . "', discount = '" . (float)$data['discount'] . "', type = '" . $this->db->escape($data['type']) . "', total = '" . (float)$data['total'] . "', logged = '" . (int)$data['payload'] . "', shipping = '" . (int)$data['shipping'] . "', city_code = '" . (int)$data['city_code'] . "', date_start = '" . $this->db->escape($data['date_start']) . "', date_end = '" . $this->db->escape($data['date_end']) . "', uses_total = '" . (int)$data['uses_total'] . "', uses_customer = '" . $userId . "', status = '" . (int)$data['status'] . "', nums= '" . $data['nums'] . "', validity= '" . $data['validity'] . "', use_type = '{$data['use_type']}', use_start = '0000-00-00', use_end = '0000-00-00', channel = '" . $data['channel'] . "' WHERE coupon_id = '" . (int)$coupon_id . "'";
		    } 
	    } else {
	        if($data['use_type'] == 0) {
	           $sql = "UPDATE " . DB_PREFIX . "coupon SET name = '" . $this->db->escape($data['name']) . "', payload='" . $packageCoupon . "', type = 0, discount = 0, code = '" . $this->db->escape($data['code']) . "', reduction = '" . (float)$data['discount'] . "', property = '" . $this->db->escape($data['type']) . "', total = '" . (float)$data['total'] . "', logged = '" . (int)$data['payload'] . "', shipping = '" . (int)$data['shipping'] . "', city_code = '" . (int)$data['city_code'] . "', date_start = '" . $this->db->escape($data['date_start']) . "', date_end = '" . $this->db->escape($data['date_end']) . "', uses_total = '" . (int)$data['uses_total'] . "', uses_customer = '" . $userId . "', status = '" . (int)$data['status'] . "', nums= '" . $data['nums'] . "', validity = 0, use_type = '{$data['use_type']}', use_start = '".$this->db->escape($data['use_start'])."', use_end = '".$this->db->escape($data['use_end'])."', channel = '" . $data['channel'] . "' WHERE coupon_id = '" . (int)$coupon_id . "'";
	        } else {
	           $sql = "UPDATE " . DB_PREFIX . "coupon SET name = '" . $this->db->escape($data['name']) . "', payload='" . $packageCoupon . "', type = 0, discount = 0, code = '" . $this->db->escape($data['code']) . "', reduction = '" . (float)$data['discount'] . "', property = '" . $this->db->escape($data['type']) . "', total = '" . (float)$data['total'] . "', logged = '" . (int)$data['payload'] . "', shipping = '" . (int)$data['shipping'] . "', city_code = '" . (int)$data['city_code'] . "', date_start = '" . $this->db->escape($data['date_start']) . "', date_end = '" . $this->db->escape($data['date_end']) . "', uses_total = '" . (int)$data['uses_total'] . "', uses_customer = '" . $userId . "', status = '" . (int)$data['status'] . "', nums= '" . $data['nums'] . "', validity= '" . $data['validity'] . "', use_type = '{$data['use_type']}', use_start = '0000-00-00', use_end = '0000-00-00', channel = '" . $data['channel'] . "' WHERE coupon_id = '" . (int)$coupon_id . "'";
	        }
        }
		$this->db->query($sql);
		$this->db->query("DELETE FROM " . DB_PREFIX . "coupon_product WHERE coupon_id = '" . (int)$coupon_id . "'");

		if (isset($data['coupon_product'])) {
			foreach ($data['coupon_product'] as $product_id) {
			    $this->doQueryCatalog($product_id, $coupon_id);
				$this->db->query("INSERT INTO " . DB_PREFIX . "coupon_product SET coupon_id = '" . (int)$coupon_id . "', product_id = '" . (int)$product_id . "'");
			}
		}

		$this->db->query("DELETE FROM " . DB_PREFIX . "coupon_category WHERE coupon_id = '" . (int)$coupon_id . "'");

		if (isset($data['coupon_category'])) {
			foreach ($data['coupon_category'] as $category_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "coupon_category SET coupon_id = '" . (int)$coupon_id . "', category_id = '" . (int)$category_id . "'");
			}
		}

		$this->event->trigger('post.admin.coupon.edit', $coupon_id);
	}

	public function deleteCoupon($coupon_id) {
		$this->event->trigger('pre.admin.coupon.delete', $coupon_id);

		$this->db->query("DELETE FROM " . DB_PREFIX . "coupon WHERE coupon_id = '" . (int)$coupon_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "coupon_product WHERE coupon_id = '" . (int)$coupon_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "coupon_category WHERE coupon_id = '" . (int)$coupon_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "coupon_history WHERE coupon_id = '" . (int)$coupon_id . "'");

		$this->event->trigger('post.admin.coupon.delete', $coupon_id);
	}

	public function getCoupon($coupon_id) {
		$query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "coupon WHERE coupon_id = '" . (int)$coupon_id . "'");
		$data = $query->row;
		if(!$data['type']) {
		    if($data['property'] == 1) {
		        $data['type'] = 1;
		    } else {
		        $data['type'] = 2;
		    }
		}
		return $data;
	}

	public function getCouponByCode($code) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "coupon WHERE code = '" . $this->db->escape($code) . "'");
		return $query->rows;
	}

	public function getCoupons($data = array()) {
		$sql = "SELECT coupon_id, name, code, discount, date_start, date_end, status, property, reduction, type, channel, validity,nums,use_type, use_start, use_end, logged, city_code, uses_customer, payload FROM " . DB_PREFIX . "coupon where logged != 3";

		$sort_data = array(
			'name',
			'code',
			'discount',
			'date_start',
			'date_end',
			'status',
		    'channel',
		    'validity',
		    'logged'
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY name";
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

	public function getCouponProducts($coupon_id) {
		$coupon_product_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "coupon_product WHERE coupon_id = '" . (int)$coupon_id . "'");

		foreach ($query->rows as $result) {
			$coupon_product_data[] = $result['product_id'];
		}

		return $coupon_product_data;
	}

	public function getCouponCategories($coupon_id) {
		$coupon_category_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "coupon_category WHERE coupon_id = '" . (int)$coupon_id . "'");

		foreach ($query->rows as $result) {
			$coupon_category_data[] = $result['category_id'];
		}

		return $coupon_category_data;
	}

	public function getTotalCoupons() {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "coupon where logged !=3");

		return $query->row['total'];
	}

	public function getCouponHistories($coupon_id, $start = 0, $limit = 10) {
		if ($start < 0) {
			$start = 0;
		}

		if ($limit < 1) {
			$limit = 10;
		}

		$query = $this->db->query("SELECT ch.order_id, CONCAT(c.firstname, ' ', c.lastname) AS customer, ch.amount, ch.date_added FROM " . DB_PREFIX . "coupon_history ch LEFT JOIN " . DB_PREFIX . "customer c ON (ch.customer_id = c.customer_id) WHERE ch.coupon_id = '" . (int)$coupon_id . "' ORDER BY ch.date_added ASC LIMIT " . (int)$start . "," . (int)$limit);

		return $query->rows;
	}

	public function getTotalCouponHistories($coupon_id) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "coupon_history WHERE coupon_id = '" . (int)$coupon_id . "'");

		return $query->row['total'];
	}
	
	public function doQueryCollectionTimes($coupon_id)
	{
	    $query = $this->db->query("select count(*) as t from ". DB_PREFIX ."customer_coupon where coupon_id = '" . (int)$coupon_id . "'");
	    
	    return $query->row['t'];
	}
	
	public function doUpdateType($coupon_id, $status)
	{
	    $query = $this->db->query("update ". DB_PREFIX ."coupon set status=".$status." where coupon_id = '" . (int)$coupon_id . "'");
	    return $query;
	}
	
	/* 打包优惠劵  */
	public function sqr($n)
	{
	    return $n*$n;
	}

	/**
	 * 生产min和max之间的随机数，但是概率不是平均的，从min到max方向概率逐渐加大。
	 * 先平方，然后产生一个平方值范围内的随机数，再开方，这样就产生了一种“膨胀”再“收缩”的效果。
	 */
	public function xRandom($bonus_min, $bonus_max){
	    $sqr = intval($this->sqr($bonus_max-$bonus_min));
	    $rand_num = rand(0, ($sqr-1));
	    return intval(sqrt($rand_num));
	}
	
	/**
	 *
	 * @param $bonus_total 红包总额
	 * @param $bonus_count 红包个数
	 * @param $bonus_max 每个小红包的最大额
	 * @param $bonus_min 每个小红包的最小额
	 * @return 存放生成的每个小红包的值的一维数组
	 */
	public function getBonus($bonus_total, $bonus_count, $bonus_max, $bonus_min) {
	    $result = array();
	
	    $average = $bonus_total / $bonus_count;
	
	    $a = $average - $bonus_min;
	    $b = $bonus_max - $bonus_min;
	
	    //
	    //这样的随机数的概率实际改变了，产生大数的可能性要比产生小数的概率要小。
	    //这样就实现了大部分红包的值在平均数附近。大红包和小红包比较少。
	    $range1 = $this->sqr($average - $bonus_min);
	    $range2 = $this->sqr($bonus_max - $average);
	
	    for ($i = 0; $i < $bonus_count; $i++) {
	        //因为小红包的数量通常是要比大红包的数量要多的，因为这里的概率要调换过来。
	        //当随机数>平均值，则产生小红包
	        //当随机数<平均值，则产生大红包
	        if (rand($bonus_min, $bonus_max) > $average) {
	            // 在平均线上减钱
	            $temp = $bonus_min + $this->xRandom($bonus_min, $average);
	            $result[$i] = $temp;
	            $bonus_total -= $temp;
	        } else {
	            // 在平均线上加钱
	            $temp = $bonus_max - $this->xRandom($average, $bonus_max);
	            $result[$i] = $temp;
	            $bonus_total -= $temp;
	        }
	    }
	    // 如果还有余钱，则尝试加到小红包里，如果加不进去，则尝试下一个。
	    while ($bonus_total > 0) {
	        for ($i = 0; $i < $bonus_count; $i++) {
	            if ($bonus_total > 0 && $result[$i] < $bonus_max) {
	                $result[$i]++;
	                $bonus_total--;
	            }
	        }
	    }
	    // 如果钱是负数了，还得从已生成的小红包中抽取回来
	    while ($bonus_total < 0) {
	        for ($i = 0; $i < $bonus_count; $i++) {
	            if ($bonus_total < 0 && $result[$i] > $bonus_min) {
	                $result[$i]--;
	                $bonus_total++;
	            }
	        }
	    }
	    return $result;
	}
	
	public function getRandCoupon($bonus_total, $bonus_count, $bonus_max, $bonus_min)
	{
	    $result_bonus = $this->getBonus($bonus_total, $bonus_count, $bonus_max, $bonus_min);
	    $total_money = 0;
	    $arr = array();
	    foreach ($result_bonus as $key => $value) {
	        $total_money += $value;
	        if(isset($arr[$value])){
	            $arr[$value] += 1;
	        }else{
	            $arr[$value] = 1;
	        }
	    }
	    return $result_bonus;
	}
	
	public function doStorageRedis($list, $couponId, $start, $end)
	{
	    foreach ($list as $key => $v) {
	        Yii::$app->redis->RPUSH(HI\Coupon\AMOUNT.$couponId, $v);
	    }
	    Yii::$app->redis->expire(HI\Coupon\AMOUNT.$couponId, (strtotime($end) - strtotime($start)));
	    return true;
	}
	
	public function queryUserGroupId($userId)
	{
	    $query = $this->db->query("select user_group_id as g from ". DB_PREFIX ."user where user_id = '" . (int)$userId . "'");
	    return $query->row['g'];
	}
}