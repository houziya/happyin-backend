<?php
class ModelExpressShipment extends Model 
{
	public function getShipment($sid) {
	    $query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . " shipment WHERE shipment.id = " . (int)$sid . "");
	    return $query->row;
	}
	
	public function getShipments($data = array())
	{
	    $sql = "SELECT * FROM " . DB_PREFIX . " shipment ";

	    $sort_data = array(
	        'id',
	    );
	
	    if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
	        $sql .= " ORDER BY " . $data['sort'];
	    } else {
	        $sql .= " ORDER BY id";
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
	
	public function getTotalShipment()
	{
	    $query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . " shipment");
	    return $query->row['total'];
	}
	
	public function editShipment($sid, $data)
	{
        $delivery_place = Yii::$app->redis->hget(HI\User\CITY_CODE, $data['delivery_place']);
	    $receipt = Yii::$app->redis->hget(HI\User\CITY_CODE, $data['receipt']);
	    $this->db->query("UPDATE " . DB_PREFIX . "shipment set shipment.shipping = '".$data['shipping']."', shipment.receipt = '" . $data['receipt'] . "', shipment.delivery_place = '" . $data['delivery_place'] . "'  WHERE shipment.id = " . (int)$sid . " ");
	    return true;
	}
	
	public function verifyAddress($data)
	{
	    $query = $this->db->query("SELECT VALUE FROM " . DB_PREFIX . "setting WHERE setting.key = 'config_express_shipment'")->rows;
	    foreach($query as $v) {
	        $info = json_decode($v['VALUE'], true);
	        if(($info['name'] == $data['name']) && ($info['end_address'] == $data['address'])) {
	            return false;
	        }
	        if(($info['name'] == $data['address']) && ($info['end_address'] == $data['name'])) {
	            return false;
	        }
	    }
	    return true;
	}
	
	public function addShipment($data)
	{
	    $this->db->query("INSERT INTO " . DB_PREFIX . " shipment SET shipment.delivery_place = '" . $data['delivery_place'] . "', shipment.receipt = '" . $data['receipt'] . "', shipment.shipping = '".$data['shipping']."'");
	    return $this->db->getLastId();
	}
	
	public function deleteShipment($sid)
	{
	    $this->db->query("DELETE FROM " . DB_PREFIX . " shipment WHERE shipment.id = " . (int)$sid . " ");
        $this->cache->delete('shipment');
        return true;
	}
	
	public function getAddressList()
	{
        for ($i = 1; $i <= 3232; $i++) {
            $name = Yii::$app->redis->hget(HI\User\CITY_CODE, $i);
            if (strstr($name, '省') && strlen($name) < 20) {
                $address[$i] = $name;
            }
            if(strstr($name, '自治区') && strlen($name) < 20) {
                $address[$i] = $name;
            }
            if (strstr($name, '新疆') || strstr($name, '宁夏') || strstr($name, '广西')) {
                $address[$i] = $name;
            }
        }
        $a = ['北京市', '天津市', '上海市', '重庆市'];
        foreach ($a as $v) {
            $ids = Yii::$app->redis->hget(HI\User\CITY_CODE, $v);
            $address[$ids] = $v;
        }
        $zhe = Yii::$app->redis->hget(HI\User\CITY_CODE, '浙江省');
        $shan = Yii::$app->redis->hget(HI\User\CITY_CODE, '山东省');
        return $address;
	}
}