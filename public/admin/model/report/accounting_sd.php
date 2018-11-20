<?php
class ModelReportAccountingSd extends Model 
{
    public function getCost($start_date, $end_date)
    {
        $sql = "select cl.order_id, cl.order_child_id, cl.product_id, cl.quantity, cl.name, cl.number, cl.origin, cl.cost, cl.stat_date , s.shipping 
                from ".DB_PREFIX."cost_log as cl 
                left join ".DB_PREFIX."shipment as s on s.delivery_place = cl.origin and s.receipt = cl.destination 
                where stat_date >= '$start_date' and stat_date <= '$end_date'";
        $query = $this->db->query($sql);
        return $query->rows;
    }
    
    public function getProduct ()
    {
        $sql = "select product_id, model, isbn from ".DB_PREFIX."product";
        $query = $this->db->query($sql);
        return $query->rows;
    }
}
