<?php
class ModelReportPrice extends Model 
{
    public function getProducts($data = array())
    {
        $sql = "SELECT p.model, cp.cost_id, cp.product_id, cp.print, cp.produce, cp.overpack, cp.inner_pack, cp.seal_sticker, cp.fitting, cp.total, cp.type, cd.name, p.mpn, cp.format 
                FROM " . DB_PREFIX . "cost_price as cp 
                left join ".DB_PREFIX."product as p on p.product_id = cp.product_id
                left join ".DB_PREFIX."product_to_category as ptc on ptc.product_id = cp.product_id
                left join ".DB_PREFIX."category_description as cd on cd.category_id = ptc.category_id
                where cd.meta_keyword != 8 and cd.language_id = 2";
        $query = $this->db->query($sql);
        return $query->rows;
    }
    
    public function deletePrice ($cost_id)
    {
       $this->event->trigger('pre.admin.cost.delete', $cost_id);
       $this->db->query("DELETE FROM " . DB_PREFIX . "cost_price WHERE cost_id = '" . (int)$cost_id . "'");
       $this->cache->delete('cost');
       $this->event->trigger('post.admin.cost.delete', $cost_id);
    }
    
    public function autocomplete ()
    {
        $sql = "SELECT p.product_id, p.model as name
                FROM ".DB_PREFIX."product as p
                left join ".DB_PREFIX."product_description as pd on pd.product_id = p.product_id
                where pd.language_id = 2";
        $query = $this->db->query($sql);
        return $query->rows;
    }
    
    public function getPrice ($cost_id)
    {
        $sql = "select * from ".DB_PREFIX."cost_price as cp
                left join ".DB_PREFIX."product as p on p.product_id = cp.product_id
                where cost_id = ".intval($cost_id);
        $query = $this->db->query($sql);
        return $query->row;
    }
    
    public function editPrice($cost_id, $data) 
    {
        $this->event->trigger('pre.admin.report.edit', $cost_id);
        $sql = "update ".DB_PREFIX."cost_price set
                product_id = '".$data['product_id']."' ,
                print = '".$data['print']."' ,
                produce = '".$data['produce']."' ,
                overpack = '".$data['overpack']."' ,
                inner_pack = '".$data['inner_pack']."' ,
                seal_sticker = '".$data['seal_sticker']."' ,
                fitting = '".$data['fitting']."' ,
                total = '".$data['total']."' ,
                format = '".$data['format']."' ,
                type = '".$data['type']."' where cost_id = ".$cost_id;
        $this->db->query($sql);
        $this->cache->delete('report');
        $this->event->trigger('post.admin.report.edit', $cost_id);
    }
    
    public function addPrice($data)
    {
        $this->event->trigger('pre.admin.report.add', $data);
        $sql = "insert into ".DB_PREFIX."cost_price set
                product_id = '".$data['product_id']."' ,
                print = '".$data['print']."' ,
                produce = '".$data['produce']."' ,
                overpack = '".$data['overpack']."' ,
                inner_pack = '".$data['inner_pack']."' ,
                seal_sticker = '".$data['seal_sticker']."' ,
                fitting = '".$data['fitting']."' ,
                total = '".$data['total']."' ,
                format = '".$data['format']."' ,
                type = '".$data['type']."'";
        $this->db->query($sql);
        $this->cache->delete('report');
        $this->event->trigger('post.admin.report.add', $data);
    }
    
    public function costStatistics ($order_id)
    {
        //20,7
        $this->event->trigger('pre.admin.report.cost', $order_id);
        
        $sql = "select o.order_id, o.shipping_country_id, o.order_status_id, o.date_added, op.product_id, op.name, op.quantity, os.code 
                from ".DB_PREFIX."`order` as o 
                left join ".DB_PREFIX."order_product as op on op.order_id = o.order_id 
                left join ".DB_PREFIX."order_splitting as os on os.order_id = o.order_id 
                where o.order_id = '$order_id'";
        
        echo $sql;exit;
        $order_info = $this->db->query($sql)->rows;
        
        //var_dump($order_info);
        $this->event->trigger('post.admin.report.cost', $order_id);
        
    }
}