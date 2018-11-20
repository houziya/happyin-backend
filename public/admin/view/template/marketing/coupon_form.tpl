<?php echo $header; ?><?php echo $column_left; ?>
<div id="content">
  <div class="page-header">
    <div class="container-fluid">
      <div class="pull-right">
        <button type="submit" form="form-coupon" data-toggle="tooltip" title="<?php echo $button_save; ?>" class="btn btn-primary"><i class="fa fa-save"></i></button>
        <a href="<?php echo $cancel; ?>" data-toggle="tooltip" title="<?php echo $button_cancel; ?>" class="btn btn-default"><i class="fa fa-reply"></i></a></div>
      <h1><?php echo $heading_title; ?></h1>
      <ul class="breadcrumb">
        <?php foreach ($breadcrumbs as $breadcrumb) { ?>
        <li><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a></li>
        <?php } ?>
      </ul>
    </div>
  </div>
  <div class="container-fluid">
    <?php if ($error_warning) { ?>
    <div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> <?php echo $error_warning; ?>
      <button type="button" class="close" data-dismiss="alert">&times;</button>
    </div>
    <?php } ?>
    <div class="panel panel-default">
      <div class="panel-heading">
        <h3 class="panel-title"><i class="fa fa-pencil"></i> <?php echo $text_form; ?></h3>
      </div>
      <div class="panel-body">
        <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form-coupon" class="form-horizontal">
          <ul class="nav nav-tabs">
            <li class="active"><a href="#tab-general" data-toggle="tab"><?php echo $tab_general; ?></a></li>
            <?php if ($coupon_id) { ?>
            <li><a href="#tab-history" data-toggle="tab"><?php echo $tab_history; ?></a></li>
            <?php } ?>
          </ul>
          <div class="tab-content">
            <div class="tab-pane active" id="tab-general">
              <div class="form-group required">
                <label class="col-sm-2 control-label" for="input-name"><?php echo $entry_name; ?></label>
                <div class="col-sm-10">
                  <input type="text" name="name" value="<?php echo $name; ?>" placeholder="<?php echo $entry_name; ?>" id="input-name" class="form-control" />
                  <?php if ($error_name) { ?>
                  <div class="text-danger"><?php echo $error_name; ?></div>
                  <?php } ?>
                </div>
              </div>
              
              <div class="form-group required">
                <label class="col-sm-2 control-label" for="input-code"><span data-toggle="tooltip" title="<?php echo $help_channel; ?>"><?php echo $entry_channel; ?></span></label>
                <div class="col-sm-10">
                  <input type="text" name="channel" value="<?php echo $channel; ?>" placeholder="" id="input-code" class="form-control" />
                </div>
              </div>
              
              <div class="form-group required">
                <label class="col-sm-2 control-label" for="input-code"><span data-toggle="tooltip" title="<?php echo $help_code; ?>"><?php echo $entry_code; ?></span></label>
                <div class="col-sm-10">
                  <input type="text" name="code" value="<?php echo $code; ?>" placeholder="<?php echo $entry_code; ?>" id="input-code" class="form-control" />
                  <?php if ($error_code) { ?>
                  <div class="text-danger"><?php echo $error_code; ?></div>
                  <?php } ?>
                </div>
              </div>
              <div class="form-group">
                <label class="col-sm-2 control-label" for="input-type"><span data-toggle="tooltip" title="<?php echo $help_type; ?>"><?php echo $entry_type; ?></span></label>
                <div class="col-sm-10">
                  <select name="type" id="input-type" class="form-control">
                    <!--<?php if ($type == 'P') { ?>
                    <option value="P" selected="selected"><?php echo $text_percent; ?></option>
                    <?php } else { ?>
                    <option value="P"><?php echo $text_percent; ?></option>
                    <?php } ?> -->
                    <?php if ($type == 'F') { ?>
                    <option value="F" selected="selected"><?php echo $text_amount; ?></option>
                    <?php } else { ?>
                    <option value="F"><?php echo $text_amount; ?></option>
                    <?php } ?>
                    <?php if ($type == 1) { ?>
                    <option value="1" selected="selected"><?php echo $text_quantity; ?></option>
                    <?php } else { ?>
                    <option value="1"><?php echo $text_quantity; ?></option>
                    <?php } ?>
                    <?php if ($type == 2) { ?>
                    <option value="2" selected="selected"><?php echo $text_prints; ?></option>
                    <?php } else { ?>
                    <option value="2"><?php echo $text_prints; ?></option>
                    <?php } ?>
                  </select>
                </div>
              </div>
              <div class="form-group">
                <label class="col-sm-2 control-label" for="input-discount"><?php echo $entry_discount; ?></label>
                <div class="col-sm-10">
                  <input type="text" name="discount" value="<?php echo $discount; ?>" placeholder="<?php echo $entry_discount; ?>" id="input-discount" class="form-control" />
                  <?php if ($error_discount) { ?>
                  <div class="text-danger"><?php echo $error_discount; ?></div>
                  <?php } ?>
                </div>
              </div>
              
              <div class="form-group">
                <label class="col-sm-2 control-label" for="input-total"><span data-toggle="tooltip" title="<?php echo $help_total; ?>"><?php echo $entry_total; ?></span></label>
                <div class="col-sm-10">
                  <input type="text" name="total" value="<?php echo $total; ?>" placeholder="<?php echo $entry_total; ?>" id="input-total" class="form-control" readOnly/>
                </div>
              </div>
              <!--<div class="form-group">
                <label class="col-sm-2 control-label"><span data-toggle="tooltip" title="<?php echo $help_logged; ?>"><?php echo $entry_logged; ?></span></label>
                <div class="col-sm-10">
                  <label class="radio-inline">
                    <?php if ($logged) { ?>
                    <input type="radio" name="logged" value="1" checked="checked" />
                    <?php echo $text_yes; ?>
                    <?php } else { ?>
                    <input type="radio" name="logged" value="1" />
                    <?php echo $text_yes; ?>
                    <?php } ?>
                  </label>
                  <label class="radio-inline">
                    <?php if (!$logged) { ?>
                    <input type="radio" name="logged" value="0" checked="checked" />
                    <?php echo $text_no; ?>
                    <?php } else { ?>
                    <input type="radio" name="logged" value="0" />
                    <?php echo $text_no; ?>
                    <?php } ?>
                  </label>
                </div>
              </div>-->
              <div class="form-group">
                <label class="col-sm-2 control-label"><?php echo $entry_shipping; ?></label>
                <div class="col-sm-10">
                  <label class="radio-inline">
                    <?php if ($shipping) { ?>
                    <input type="radio" name="shipping" value="1" checked="checked" />
                    <?php echo $text_yes; ?>
                    <?php } else { ?>
                    <input type="radio" name="shipping" value="1" />
                    <?php echo $text_yes; ?>
                    <?php } ?>
                  </label>
                  <label class="radio-inline">
                    <?php if (!$shipping) { ?>
                    <input type="radio" name="shipping" value="0" checked="checked" />
                    <?php echo $text_no; ?>
                    <?php } else { ?>
                    <input type="radio" name="shipping" value="0" />
                    <?php echo $text_no; ?>
                    <?php } ?>
                  </label>
                </div>
              </div>
              <div class="form-group">
                <label class="col-sm-2 control-label" for="input-product"><span data-toggle="tooltip" title="<?php echo $help_product; ?>"><?php echo $entry_product; ?></span></label>
                <div class="col-sm-10">
                  <input type="text" name="product" value="" placeholder="<?php echo $entry_product; ?>" id="input-product" class="form-control" />
                  <div id="coupon-product" class="well well-sm" style="height: 150px; overflow: auto;">
                    <?php foreach ($coupon_product as $coupon_product) { ?>
                    <div id="coupon-product<?php echo $coupon_product['product_id']; ?>"><i class="fa fa-minus-circle"></i> <?php echo $coupon_product['name']; ?>
                      <input type="hidden" name="coupon_product[]" value="<?php echo $coupon_product['product_id']; ?>" />
                    </div>
                    <?php } ?>
                  </div>
                  <?php if ($error_coupon_product) { ?>
                        <div class="text-danger"><?php echo $error_coupon_product; ?></div>
                  <?php } ?>
                </div>
              </div>
              <div class="form-group">
                <label class="col-sm-2 control-label" for="input-category"><span data-toggle="tooltip" title="<?php echo $help_category; ?>"><?php echo $entry_category; ?></span></label>
                <div class="col-sm-10">
                  <input type="text" name="category" value="" placeholder="<?php echo $entry_category; ?>" id="input-category" class="form-control"/>
                  <div id="coupon-category" class="well well-sm" style="height: 150px; overflow: auto;">
                    <?php foreach ($coupon_category as $coupon_category) { ?>
                    <div id="coupon-category<?php echo $coupon_category['category_id']; ?>"><i class="fa fa-minus-circle"></i> <?php echo $coupon_category['name']; ?>
                      <input type="hidden" name="coupon_category[]" value="<?php echo $coupon_category['category_id']; ?>" />
                    </div>
                    <?php } ?>
                  </div>
                </div>
              </div>
              <div class="form-group">
                <label class="col-sm-2 control-label" for="input-date-start"><?php echo $entry_date_start; ?></label>
                <div class="col-sm-3">
                  <div class="input-group date">
                    <input type="text" name="date_start" value="<?php echo $date_start; ?>" placeholder="<?php echo $entry_date_start; ?>" data-date-format="YYYY-MM-DD" id="input-date-start" class="form-control" />
                    <span class="input-group-btn">
                    <button type="button" class="btn btn-default"><i class="fa fa-calendar"></i></button>
                    </span></div>
                </div>
              </div>
              <div class="form-group">
                <label class="col-sm-2 control-label" for="input-date-end"><?php echo $entry_date_end; ?></label>
                <div class="col-sm-3">
                  <div class="input-group date">
                    <input type="text" name="date_end" value="<?php echo $date_end; ?>" placeholder="<?php echo $entry_date_end; ?>" data-date-format="YYYY-MM-DD" id="input-date-end" class="form-control" />
                    <span class="input-group-btn">
                    <button type="button" class="btn btn-default"><i class="fa fa-calendar"></i></button>
                    </span></div>
                </div>
              </div>
              
            <div class="form-group">
                <label class="col-sm-2 control-label" for="input-type"><span data-toggle="tooltip" title=""><?php echo $entry_use_type; ?></span></label>
                <div class="col-sm-10">
                  <select name="use_type" id="input-type-date" class="form-control">
                  <?php 
                    if ($use_type == 0) {
                  ?>
                        <option value="0" selected><?php echo $text_define; ?></option>
                        <option value="1" ><?php echo $text_validity; ?></option>
                    <?php 
                        } else {
                    ?>
                        <option value="0" ><?php echo $text_define; ?></option>
                        <option value="1" selected><?php echo $text_validity; ?></option>
                    <?php
                        }
                    ?>
                  </select>
                </div>
              </div>
              
             <div class="form-group" id="use_start">
                <label class="col-sm-2 control-label" for="input-date-start"><?php echo $entry_c_start; ?></label>
                <div class="col-sm-3">
                  <div class="input-group date">
                    <input type="text" name="use_start" value="<?php echo $use_start;?>" placeholder="<?php echo $entry_c_start; ?>" data-date-format="YYYY-MM-DD" id="input-date-start" class="form-control" />
                    <span class="input-group-btn">
                    <button type="button" class="btn btn-default"><i class="fa fa-calendar"></i></button>
                    </span></div>
                </div>
              </div>
              <div class="form-group" id="use_end">
                <label class="col-sm-2 control-label" for="input-date-end"><?php echo $entry_c_end; ?></label>
                <div class="col-sm-3">
                  <div class="input-group date">
                    <input type="text" name="use_end" value="<?php echo $use_end;?>" placeholder="<?php echo $entry_c_end; ?>" data-date-format="YYYY-MM-DD" id="input-date-end" class="form-control" />
                    <span class="input-group-btn">
                    <button type="button" class="btn btn-default"><i class="fa fa-calendar"></i></button>
                    </span></div>
                </div>
              </div>
              
              <div class="form-group" id="validity">
                <label class="col-sm-2 control-label" for="input-uses-total"><span data-toggle="tooltip" title="<?php echo $help_validity; ?>"><?php echo $entry_validity; ?></span></label>
                <div class="col-sm-10">
                  <input type="text" name="validity" value="<?php echo $validity; ?>" placeholder="" id="input-uses-total" class="form-control" />
                </div>
              </div>

              <!--审核人-->
              <!--<div class="form-group">
                <label class="col-sm-2 control-label" for="input-uses-customer"><span data-toggle="tooltip" title="<?php echo $help_uses_customer; ?>"><?php echo $entry_uses_customer; ?></span></label>
                <div class="col-sm-10">
                  <input type="text" name="uses_customer" value="<?php echo $uses_customer; ?>" placeholder="<?php echo $entry_uses_customer; ?>" id="input-uses-customer" class="form-control" />
                </div>
              </div>-->

               <!--logged==0 普通优惠劵类型  logged==1 退换货  logged == 2分享优惠劵 logged == 4打包优惠劵 -->
              <div class="form-group">
                <label class="col-sm-2 control-label" for="input-status"><?php echo $column_scenes; ?></label>
                <div class="col-sm-10">
                  <select name="payload" id="input-scenes" class="form-control">
                    <?php if ($logged == 2) { ?>
                        <option value="1"><?php echo $text_returns; ?></option>
                        <option value="0"><?php echo $text_normal; ?></option>
                        <option value="2" selected><?php echo $text_share; ?></option>
                        <option value="4" ><?php echo $entry_package_coupon; ?></option>
                    <?php  } elseif ($logged == 1) { ?>
                        <option value="1" selected><?php echo $text_returns; ?></option>
                        <option value="0"><?php echo $text_normal; ?></option>
                        <option value="2"><?php echo $text_share; ?></option>
                        <option value="4" ><?php echo $entry_package_coupon; ?></option>
                    <?php } elseif ($logged == 4) {?>
                        <option value="1"><?php echo $text_returns; ?></option>
                        <option value="0"><?php echo $text_normal; ?></option>
                        <option value="2"><?php echo $text_share; ?></option>
                        <option value="4" selected><?php echo $entry_package_coupon; ?></option>
                    <?php } else {?>
                        <option value="1"><?php echo $text_returns; ?></option>
                        <option value="0" selected><?php echo $text_normal; ?></option>
                        <option value="2"><?php echo $text_share; ?></option>
                        <option value="4" ><?php echo $entry_package_coupon; ?></option>
                    <?php }?>
                  </select>
                </div>
              </div>

              <!--打包优惠劵的总金额-->
              <div class="form-group" id='package_amount'>
                <label class="col-sm-2 control-label" for="input-uses-customer"><span data-toggle="tooltip" title="<?php echo $help_total_amount; ?>"><?php echo $total_amount; ?></span></label>
                <div class="col-sm-10">
                  <input type="text" name="entry_total_amount" value="<?php echo $entry_total_amount; ?>" placeholder="<?php echo $entry_total_amount; ?>" id="input-uses-customer" class="form-control" />
                  <?php if ($error_total_amount) { ?>
                  <div class="text-danger"><?php echo $error_total_amount; ?></div>
                  <?php } ?>
                </div>
             </div>
             <!--打包优惠劵的最小值和最大值-->
            <div class="form-group" id='package_value' >
                <label class="col-sm-2 control-label" for="input-uses-customer"><span data-toggle="tooltip" title="<?php echo $help_max_value; ?>"><?php echo $max_value; ?></span></label>
                <div class="col-sm-10">
                  <input type="text" name="entry_max_value" value="<?php echo $entry_max_value; ?>" placeholder="<?php echo $help_max_value; ?>" id="input-uses-customer" class="form-control" />
                  <?php if ($error_max_value) { ?>
                  <div class="text-danger"><?php echo $error_max_value; ?></div>
                  <?php } ?>
                </div>
            </div>
            <!--打包优惠劵领取次数-->
              <div class="form-group">
                <label class="col-sm-2 control-label" for="input-uses-customer"><span data-toggle="tooltip" title="<?php echo $help_collection_times; ?>"><?php echo $entry_collection_times; ?></span></label>
                <div class="col-sm-10">
                  <input type="text" name="nums" value="<?php echo $nums; ?>" placeholder="<?php echo $entry_collection_times; ?>" id="input-uses-customer" class="form-control" />
                </div>
              </div>

            <!--指定优惠劵给用户-->
            <div class="form-group" id="input_user">
                <label class="col-sm-2 control-label" for="input-uses-customer"><span data-toggle="tooltip" title="<?php echo $column_user; ?>"><?php echo $column_user; ?></span></label>
                <div class="col-sm-10">
                  <input type="text" name="blind_user" value="<?php echo $blind_user; ?>" placeholder="<?php echo $column_user; ?>" id="input-uses-customer" class="form-control" />
                  <?php if ($error_user) { ?>
                  <div class="text-danger"><?php echo $error_user; ?></div>
                  <?php } ?>
                </div>
            </div>
            
             <div class="form-group" id='share_pro'>
                <label class="col-sm-2 control-label" for="input-uses-total"><span data-toggle="tooltip" title="<?php echo $help_uses_total; ?>"><?php echo $entry_uses_total; ?></span></label>
                <div class="col-sm-10">
                  <input type="text" name="uses_total" value="<?php echo $uses_total; ?>" placeholder="<?php echo $entry_uses_total; ?>" id="input-uses-total" class="form-control" />
                  <?php if ($error_total) { ?>
                  <div class="text-danger"><?php echo $error_total; ?></div>
                  <?php } ?>
                </div>
              </div>

            <!--优惠劵配置发货地-->
            <div class="form-group">
                <label class="col-sm-2 control-label" for="input-uses-customer"><span data-toggle="tooltip" title="<?php echo $help_city_code; ?>"><?php echo $entry_city_code; ?></span></label>
                <div class="col-sm-10">
                    <select name="city_code" id="input-type" class="form-control">
                        <?php if($city_code == 2) {?>
                            <option value="2" selected >暂无发货地要求</option>
                            <option value="1">杭州</option>
                            <option value="0">山东</option>
                        <?php  } elseif ($city_code == 1) {?>
                            <option value="2">暂无发货地要求</option>
                            <option value="1" selected>杭州</option>
                            <option value="0">山东</option>
                        <?php } else {?>
                            <option value="2">暂无发货地要求</option>
                            <option value="1">杭州</option>
                            <option value="0" selected>山东</option>
                        <?php }?>
                    </select>
                </div>
             </div>

            <!--优惠劵审核人修改优惠劵状态-->
              <?php if(isset($_GET['coupon_id'])) {?>
              <div class="form-group">
                <label class="col-sm-2 control-label" for="input-status"><?php echo $entry_status; ?></label>
                <div class="col-sm-10">
                  <select name="status" id="input-status" class="form-control">
                    <?php if ($status) { ?>
                    <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                    <option value="0"><?php echo $text_disabled; ?></option>
                    <?php } else { ?>
                    <option value="1"><?php echo $text_enabled; ?></option>
                    <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                    <?php } ?>
                  </select>
                </div>
              </div>
             <?php }?>
            </div>

            <?php if ($coupon_id) { ?>
            <div class="tab-pane" id="tab-history">
              <div id="history"></div>
            </div>
            <?php } ?>
          </div>
        </form>
      </div>
    </div>
  </div>
  <script type="text/javascript">
    var selValue = $("#input-scenes option:selected").val();
    if (selValue == 1) {
       $("#input_user").css("display","block");
       $("#package_amount").css("display","none");
       $("#package_value").css("display","none");
       $("#share_pro").css("display","none");
    } else if (selValue == 4) {
       $("#package_amount").css("display","block");
       $("#package_value").css("display","block");
       $("#input_user").css("display","none");
       $("#share_pro").css("display","none");
    } else if (selValue == 2) {
       $("#share_pro").css("display","block");
       $("#input_user").css("display","none");
       $("#package_amount").css("display","none");
       $("#package_value").css("display","none");
    } else {
       $("#input_user").css("display","none");
       $("#package_amount").css("display","none");
       $("#package_value").css("display","none");
       $("#share_pro").css("display","none");
    };
    var selValue = $("#input-type-date option:selected").val();
    if(selValue == 1) {
       $("#use_start").css("display","none");
       $("#use_end").css("display","none");
       $("#validity").css("display","block");
    } else {
       $("#use_start").css("display","block");
       $("#use_end").css("display","block");
       $("#validity").css("display","none");
    }
 $("#input-scenes").change(function(){
    var selValue = $("#input-scenes option:selected").val();
    if (selValue == 1) {
       $("#input_user").css("display","block");
       $("#package_amount").css("display","none");
       $("#package_value").css("display","none");
       $("#share_pro").css("display","none");
    } else if (selValue == 4) {
       $("#package_amount").css("display","block");
       $("#package_value").css("display","block");
       $("#input_user").css("display","none");
       $("#share_pro").css("display","none");
    } else if (selValue == 2) {
       $("#share_pro").css("display","block");
       $("#input_user").css("display","none");
       $("#package_amount").css("display","none");
       $("#package_value").css("display","none");
    } else {
       $("#input_user").css("display","none");
       $("#package_amount").css("display","none");
       $("#package_value").css("display","none");
       $("#share_pro").css("display","none");
    };
 });
  $("#input-type-date").change(function(){
    var selValue = $("#input-type-date option:selected").val();
    if(selValue == 1) {
       $("#use_start").css("display","none");
       $("#use_end").css("display","none");
       $("#validity").css("display","block");
    } else {
       $("#use_start").css("display","block");
       $("#use_end").css("display","block");
       $("#validity").css("display","none");
    }
 });
  <!--
$('input[name=\'product\']').autocomplete({
	'source': function(request, response) {
		$.ajax({
			url: 'index.php?route=catalog/product/autocomplete&token=<?php echo $token; ?>&type=1&filter_name=' +  encodeURIComponent(request),
			dataType: 'json',			
			success: function(json) {
				response($.map(json, function(item) {
					return {
						label: item['name'],
						value: item['product_id']
					}
				}));
			}
		});
	},
	'select': function(item) {
		$('input[name=\'product\']').val('');
		
		$('#coupon-product' + item['value']).remove();
		
		$('#coupon-product').append('<div id="coupon-product' + item['value'] + '"><i class="fa fa-minus-circle"></i> ' + item['label'] + '<input type="hidden" name="coupon_product[]" value="' + item['value'] + '" /></div>');	
	}
});

$('#coupon-product').delegate('.fa-minus-circle', 'click', function() {
	$(this).parent().remove();
});
// Category
$('input[name=\'category\']').autocomplete({
	'source': function(request, response) {
		$.ajax({
			url: 'index.php?route=catalog/category/autocomplete&token=<?php echo $token; ?>&filter_name=' +  encodeURIComponent(request),
			dataType: 'json',
			success: function(json) {
				response($.map(json, function(item) {
					return {
						label: item['name'],
						value: item['category_id']
					}
				}));
			}
		});
	},
	'select': function(item) {
		$('input[name=\'category\']').val('');
		
		$('#coupon-category' + item['value']).remove();
		
		$('#coupon-category').append('<div id="coupon-category' + item['value'] + '"><i class="fa fa-minus-circle"></i> ' + item['label'] + '<input type="hidden" name="coupon_category[]" value="' + item['value'] + '" /></div>');
	}	
});

$('#coupon-category').delegate('.fa-minus-circle', 'click', function() {
	$(this).parent().remove();
});
//--></script>
  <?php if ($coupon_id) { ?>
  <script type="text/javascript"><!--
$('#history').delegate('.pagination a', 'click', function(e) {
	e.preventDefault();
	
	$('#history').load(this.href);
});			

$('#history').load('index.php?route=marketing/coupon/history&token=<?php echo $token; ?>&coupon_id=<?php echo $coupon_id; ?>');
//--></script>
  <?php } ?>
  <script type="text/javascript"><!--
$('.date').datetimepicker({
	pickTime: false
});
//--></script></div>
<?php echo $footer; ?>