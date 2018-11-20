<?php echo $header; ?><?php echo $column_left; ?>
<div id="content">
<div class="page-header">
    <div class="container-fluid">
      <div class="pull-right">
        <!--<button type="submit" id="button-shipping" form="form-order" formaction="<?php echo $shipping; ?>" data-toggle="tooltip" title="<?php echo $button_shipping_print; ?>" class="btn btn-info"><i class="fa fa-truck"></i></button>
        <button type="submit" id="button-invoice" form="form-order" formaction="<?php echo $invoice; ?>" data-toggle="tooltip" title="<?php echo $button_invoice_print; ?>" class="btn btn-info"><i class="fa fa-print"></i></button>
        <a href="<?php echo $add; ?>" data-toggle="tooltip" title="<?php echo $button_add; ?>" class="btn btn-primary"><i class="fa fa-plus"></i></a>
        <button type="submit" id="button-shipping" form="form-order" formaction="" data-toggle="tooltip" title="" class="btn btn-info">导出收货人</button>
        <button type="submit" id="button-shipping" form="form-order" formaction="" data-toggle="tooltip" title="" class="btn btn-info">下载照片</button>-->
     </div>
      <h1><?php echo $heading_title; ?></h1>
      <ul class="breadcrumb">
        <?php foreach ($breadcrumbs as $breadcrumb) { ?>
        <li><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a></li>
        <?php } ?>
      </ul>
    </div>
</div>	  
<div class="container-fluid">
	<div class="panel panel-default">
	<div class="panel-heading">
	        <h3 class="panel-title"><i class="fa fa-list"></i> 订单详情</h3>
	</div>
	<div class="panel-body">
	        <div class="col-md-3">
	            <span class="label label-default">订单时间:</span><?php echo $date_added; ?>
	        </div>
	        <div class="col-md-3">
	            <span class="label label-default">订单号:</span><?php echo $order_number; ?>
	        </div>
	        <div class="col-md-3">
	            <span class="label label-default">快递单号:</span><?php echo $shipping_id; ?>
	        </div>
	</div>
	<div class="panel-body">
		<table class="table table-bordered">
          <thead>
            <tr>
              <td style="width: 50%;" class="text-left">收货地址</td>
              
            </tr>
          </thead>
          <tbody>
            <tr>
              <td class="text-left">收货人 : <?php echo $shipping_firstname;?>   联系方式 : <?php echo $telephone;?><br>收货地址 : <?php echo $shipping_address;?></td>
            </tr>
          </tbody>
        </table>
        <table class="table table-bordered table-hover">
          <thead>
            <tr>
              <td class="text-left"><?php echo $column_product; ?></td>
              <td class="text-left">商品发货地</td>
              <td class="text-right"><?php echo $column_quantity; ?></td>
              <td class="text-right"><?php echo $column_price; ?></td>
              <td class="text-right">小计</td>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($products as $product) { ?>
            <tr>
              <td class="text-left"><a href="<?php echo $product['href']; ?>"><?php echo $product['name']; ?></a>
                <?php foreach ($product['option'] as $option) { ?>
                <br />
                <?php if ($option['type'] != 'file') { ?>
                &nbsp;<small> - <?php echo $option['name']; ?>: <?php echo $option['value']; ?></small>
                <?php } else { ?>
                &nbsp;<small> - <?php echo $option['name']; ?>: <a href="<?php echo $option['href']; ?>"><?php echo $option['value']; ?></a></small>
                <?php } ?>
                <?php } ?></td>
              <td class="text-left"><?php echo $product['location']; ?></td>
              <td class="text-right"><?php echo $product['quantity']; ?></td>
              <td class="text-right"><?php echo $product['price']; ?></td>
              <td class="text-right"><?php echo $product['row_total']; ?></td>
            </tr>
            <?php } ?>
            <tr>
              <td colspan="4" class="text-right"></td>
              <td class="text-right">合计 : <?php echo $order_total; ?></td>
            </tr>
          </tbody>
        </table>
        <!-- 
        <div class="row panel-body">
	        <div class="col-md-2">
	            商品总额:<?php echo $order_total; ?>
	        </div>
	        <div class="col-md-2">
	            优惠券:<?php echo $product['coupon_name']; ?>
	        </div>
	        <div class="col-md-2">
	            抵扣金额:<?php echo $product['coupon_price']; ?>
	        </div>
	        <div class="col-md-2">
	           运费:<?php echo (int)$product['pay_total'] > 50 ? "￥0.00" : "￥8.00"; ?> (满50包邮)
	        </div>
        </div>
        <div class="row panel-body">
	        <div class="col-md-4">
	            实付金额:<?php echo $product['pay_total']; ?>
	        </div>
        </div>
        -->
        <table class="table table-bordered">
          <thead>
            <tr>
              <td style="width: 50%;" class="text-left">订单记录</td>
            </tr>
          </thead>
          <tbody>
            <?php foreach($order_log as $log) { ?>
            <tr>  
              <td class="text-left"><?php echo $log['date_added'] . "    " . $log['status_desc']; ?></td>
            </tr>
            <?php } ?>
          </tbody>
        </table>
        </div>
    </div>
    </div>
</div>
<?php echo $footer; ?>