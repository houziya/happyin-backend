<?php
class ModelCatalogPrint extends Model
{
    public function getPrints($data = array())
    {
        $sql = "SELECT * FROM " . DB_PREFIX . "print p GROUP BY p.print_id";
        
        $sort_data = array(
                'p.print_id',
        );
    
        if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
            $sql .= " ORDER BY " . $data['sort'];
        } else {
            $sql .= " ORDER BY p.print_id";
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
    
    public function getTotalPrints($data = array())
    {
        $sql = "SELECT COUNT(DISTINCT p.product_id) AS total FROM " . DB_PREFIX . "product p ";
        $query = $this->db->query($sql);
        return $query->row['total'];
    }
    
    public function addPrint($data)
    {
        $preview = json_encode([
            'image' => $data['preview_image'],
            'area' => $data['preview_area'],
            'size' => $data['preview_size'],
        ]);
        $this->event->trigger('pre.admin.print.add', $data);
        $this->db->query(
                "INSERT INTO " . DB_PREFIX . "print SET 
                name = '" . $data['print_name'] . "', 
                quantity = '" . $data['print_quantity'] . "', 
                size = '" . $data['print_size'] . "', 
                preview = '" . $preview . "', 
                create_time = NOW()"
            );
        $print_id = $this->db->getLastId();
        if (isset($data['product_category'])) {
            foreach ($data['product_category'] as $product_id) {
                $this->db->query("INSERT INTO " . DB_PREFIX . "print_to_product SET print_id = '" . (int)$print_id . "', product_id = '" . (int)$product_id . "'");
            }
        }
        $this->cache->delete('print');
        $this->event->trigger('post.admin.print.add', $print_id);
        return $print_id;
    }
    
    public function editPrint($print_id, $data)
    {
        $this->event->trigger('pre.admin.print.edit', $data);
        $preview = json_encode([
                'image' => $data['preview_image'],
                'area' => $data['preview_area'],
                'size' => $data['preview_size'],
                ]);
        $this->db->query(
                "UPDATE " . DB_PREFIX . "print SET 
                name = '" . $data['print_name'] . "', 
                quantity = '" . $data['print_quantity'] . "', 
                size = '" . $data['print_size'] . "', 
                preview = '" . $preview . "' WHERE print_id = '" . (int)$print_id . "'");
        $this->db->query("DELETE FROM " . DB_PREFIX . "print_to_product WHERE print_id = '" . (int)$print_id . "'");
        foreach ($data['product_category'] as $product_id) {
            $this->db->query("INSERT INTO " . DB_PREFIX . "print_to_product SET print_id = '" . (int)$print_id . "', product_id = '" . (int)$product_id . "'");
        }
        $this->cache->delete('print');
        $this->event->trigger('post.admin.print.edit', $print_id);
    }
    
    public function getPrint($print_id)
    {
        $sql = "SELECT * FROM " . DB_PREFIX . "print p where print_id = ". $print_id;;
        $query = $this->db->query($sql);
        return $query->row;
    }
    
    public function getPrintProduct ($print_id)
    {
        $sql = "SELECT product_id FROM " . DB_PREFIX . "print_to_product p where print_id = ". $print_id;;
        $query = $this->db->query($sql);
        return $query->rows;
    }
    
    public function deletePrint($print_id) {
        $this->event->trigger('pre.admin.print.delete', $print_id);
        $this->db->query("DELETE FROM " . DB_PREFIX . "print WHERE print_id = '" . (int)$print_id . "'");
        $this->db->query("DELETE FROM " . DB_PREFIX . "print_to_product WHERE print_id = '" . (int)$print_id . "'");
        $this->cache->delete('print');
        $this->event->trigger('post.admin.print.delete', $print_id);
    }
}