<?php
class ModelLocalisationStockWarning extends Model {

	public function editStockWarning($stock_warning_id, $data) {
		$this->db->query("UPDATE " . DB_PREFIX . "setting set VALUE = '".(int)$data['warning']."' WHERE setting_id = " . (int)$stock_warning_id . "");
		
		$this->cache->delete('stock_warning');
		return true;
	}


	public function getStockWarning($stock_warning_id = NULL) {
	    if($stock_warning_id) {
	      $query = $this->db->query("SELECT VALUE as v FROM " . DB_PREFIX . "setting WHERE setting.setting_id = ".(int)$stock_warning_id." and VALUE != 0");
	    } else {
		  $query = $this->db->query("SELECT VALUE as v FROM " . DB_PREFIX . "setting WHERE setting.KEY = 'config_stock_warning' and VALUE != 0");
	    }
		return $query->row['v'];
	}
	
	public function addStockWarning($data) {
	    $query = $this->db->query("SELECT VALUE as v FROM " . DB_PREFIX . "setting WHERE setting.KEY = 'config_stock_warning' and VALUE != 0");
	    if(!$query) {
	        return false;
	    }
	    $this->db->query("INSERT INTO " . DB_PREFIX . "setting SET setting.key = 'config_stock_warning',  setting.value = '" . (int)$data['warning'] . "'");
	    $stock_warning_id = $this->db->getLastId();
	}
	
	public function deleteStockWarning($stock_warning_id) 
	{
	    $query = $this->db->query("SELECT VALUE as v , setting_id as s FROM " . DB_PREFIX . "setting WHERE setting.KEY = 'config_stock_warning' and VALUE != 0");
	    if($stock_warning_id == $query->row['s']) {
	       $this->db->query("DELETE FROM " . DB_PREFIX . "setting WHERE setting.setting_id = " . (int)$stock_warning_id . "");
	       $this->cache->delete('stock_warning');
	    }
	    return false;
	}
	
	public function getStockWarningId()
	{
	    $query = $this->db->query("SELECT setting_id as sid FROM " . DB_PREFIX . "setting WHERE setting.KEY = 'config_stock_warning' and VALUE != 0");
	    return $query->row['sid'];
	}
	
}