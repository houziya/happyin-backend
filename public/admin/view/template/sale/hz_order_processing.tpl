<?php echo $header; ?><?php echo $column_left; ?>
<div id="content">
  <div class="page-header">
    <div class="container-fluid">
      <div class="pull-right">
        <button type="submit" id="button-parcel" form="form-order" data-toggle="tooltip" title="" class="btn btn-primary">下载文件</button>
        <button type="button" id="button-upload" data-toggle="tooltip" title="" class="btn btn-primary">导入快递</button>
        <!--
        <button type="submit" id="button-excel" form="form-order" formaction="<?php echo $action;?>" data-toggle="tooltip" title="" class="btn btn-info">导出收货人</button>
        <button type="submit" id="button-shipping" form="form-order" formaction="<?php echo $shipping_action;?>" data-toggle="tooltip" title="" class="btn btn-info">马上发货</button>
        <form action="/admin/index.php?route=sale/hz_order/inputExcelAction&token=<?php echo $token;?>" method="post" enctype="multipart/form-data">
        <input type="hidden" name="leadExcel" value="true">
	    <input type="file" name="inputExcel"><input type="submit" value="导入数据" class="btn btn-primary">
		</form>-->
        </div>
      <h1><?php echo $heading_title; ?></h1>
      <ul class="breadcrumb">
        <?php foreach ($breadcrumbs as $breadcrumb) { ?>
        <li><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a></li>
        <?php } ?>
      </ul>
    </div>
  </div>
  <?php if (!empty($error)) { ?>
    <div class="alert alert-danger"><i class="fa fa-check-circle"></i>
        <?php echo $error; ?>
    </div>
   <?php } ?>
  <?php if (!empty($success)) { ?>
    <div class="alert alert-success"><i class="fa fa-check-circle"></i>
        <?php echo $success; ?>
    </div>
   <?php } ?>

  <div class="container-fluid">
    <div class="panel panel-default">
      <div class="panel-heading">
        <h3 class="panel-title"><i class="fa fa-list"></i> <?php echo $text_list; ?></h3>
      </div>
      <div class="panel-body">
      	<div class="well">
        	<div class="row">
                <label class="control-label" for="input-order-status"><?php echo $entry_order_status; ?></label>
                <select name="filter_order_status" id="input-order-status" class="btn">
                  <option value="*">全部</option>
                  <!--
                  <?php if ($filter_order_status == '0') { ?>
                  <option value="0" selected="selected"><?php echo $text_missing; ?></option>
                  <?php } else { ?>
                  <option value="0"><?php echo $text_missing; ?></option>
                  <?php } ?>-->
                  <?php foreach ($order_statuses as $order_status_id => $order_status) { ?>
                  <?php if ($order_status_id == $filter_order_status) { ?>
                  <option value="<?php echo $order_status_id; ?>" selected="selected"><?php echo $order_status; ?></option>
                  <?php } else { ?>
                  <option value="<?php echo $order_status_id; ?>"><?php echo $order_status; ?></option>
                  <?php } ?>
                  <?php } ?>
                </select>
	              <button type="button" id="button-filter" class="btn btn-primary"><i class="fa fa-search"></i> <?php echo $button_filter; ?></button>
	      	</div>        
          </div>    
        <form method="post" enctype="multipart/form-data" target="" id="form-order" action="/admin/index.php?route=sale/hz_order/parcleComboAction&token=<?php echo $token;?>">
          <div class="table-responsive">
            <table class="table table-bordered table-hover">
              <thead>
                <tr>
                  <td style="width: 1px;" class="text-center"><input id="all" type="checkbox" onclick="$('input[name*=\'selected\']').prop('checked', this.checked);" /></td>
                  <td class="text-left">
                  	编号
                  </td>
                  <td class="text-right"><?php if ($sort == 'o.order_id') { ?>
                    <?php echo $column_order_id; ?>
                    <?php } else { ?>
                    <?php echo $column_order_id; ?>
                    <?php } ?></td>
                  <td class="text-left">
                  	件数
                  </td>
                  <td class="text-left">
                  	商品
                  </td>   
                  <td class="text-left" style="width: 5%;"><?php if ($sort == 'status') { ?>
                    <?php echo $column_status; ?>
                    <?php } else { ?>
                    <?php echo $column_status; ?>
                    <?php } ?></td>
                  <td class="text-left">
                  	收货地址
                  </td> 
                  <!--<td class="text-left">
                    <a href="">物流公司 / 物流单号</a>
                  </td>-->
                  <td class="text-left" style="width: 8%;"><?php if ($sort == 'o.date_added') { ?>
                    <?php echo $column_date_added; ?>
                    <?php } else { ?>
                    <?php echo $column_date_added; ?>
                    <?php } ?></td>
                  <td class="text-left" style="width: 6%;">照片文件</td>  
                  <td class="text-right"><?php echo $column_action; ?></td>
                </tr>
              </thead>
              <tbody>
                <?php if ($orders) { ?>
                <?php foreach ($orders as $order) { ?>
                <tr data-toggle="popover" class="<?php echo $order['status'] == "新订单" ? "info" : "";?>">
                  <td class="text-center"><?php if (in_array($order['order_id'], $selected)) { ?>
                    <input type="checkbox" name="selected[]" value="<?php echo $order['order_id']; ?>" checked="checked" />
                    <?php } else { ?>
                    <input type="checkbox" name="selected[]" value="<?php echo $order['order_id']; ?>" />
                    <?php } ?>
                    <input type="hidden" name="shipping_code[]" value="<?php echo $order['shipping_code']; ?>" /></td>
                  <td class="text-right"><?php echo $order['order_numbering_id']; ?></td>
                  <td class="text-right"><?php echo $order['order_number']; ?></td>
                  <td class="text-right" id="product" data-container="body" data-order="<?php echo $order['order_id']; ?>">
                  	<?php echo $order['order_product_count']; ?>
                  	<span id="order-products-<?php echo $order['order_id']; ?>" class="hide"><?php echo json_encode($order['order_products']); ?></span>
                  </td>
                  <td class="text-right"><?php echo $order['order_product']; ?></td>
                  <td class="text-left"><?php echo $order['status']; ?></td>
                  <!--<td class="text-left col-md-2 inline">
                  <div class="form-group inline-block">
                     <span class="input-group-btn">
                     <select class="form-control" name="splitting_company" id="input-meta-keyword2">
		        	<?php foreach($express as $expressName){ ?>
	                     <option value="<?php echo $expressName['code']; ?>" <?php echo ($expressName['code'] == $order['splitting_company']) ? "selected" : ""; ?>><?php echo $expressName['company']; ?></option>
					<?php } ?>
	                 </select>
                     <?php if(!empty($order['shipping_id'])){ ?>
                     <input readonly class="form-control" id="shipping_id" name="shipping_id" value="<?php echo $order['shipping_id']; ?>" type="text" placeholder="快递单号">
                     <button type="button" id="order-edit" class="btn btn-default" data-order="<?php echo $order['order_id']; ?>">编辑</button>
                     <button type="button" id="order-save" class="btn btn-default hide" data-order="<?php echo $order['order_id']; ?>">保存</button>
                     <?php }else{ ?>
                     <input class="form-control" id="shipping_id" name="shipping_id" value="<?php echo $order['shipping_id']; ?>" type="text" placeholder="快递单号">
                     <button type="button" id="order-edit" class="btn btn-default hide" data-order="<?php echo $order['order_id']; ?>">编辑</button>
					 <button type="button" id="order-save" class="btn btn-default" data-order="<?php echo $order['order_id']; ?>">保存</button>                     
                     <?php } ?>
                     </span>
              	  </div>
                  </td>-->
                  <td class="text-left"><?php echo $order['shipping_address']; ?></td>
                  
                  <td class="text-left"><?php echo date("Y-m-d", strtotime($order['date_added'])); ?></td>
                  <td class="text-left">
                  <?php if($order['parcle']) { ?>
                  <a target="left" href='<?php echo HI\Config\QCloud\IMAGE\DOMAIN . "/order/parcel/" . $order['parcle'] . ".zip";?>'>下载</a>
                  <!--<a target="left" href='/admin/index.php?route=sale/hz_order/parcleComboAction&token=<?php echo $token;?>&order_id=<?php echo $order['order_id'];?>'>下载</a>-->
                  <?php } ?>
                  </td>
                  <td class="text-right"><a href="<?php echo $order['edit']; ?>" data-toggle="tooltip" title="<?php echo $button_edit; ?>" class="btn btn-primary"><i class="fa fa-pencil"></i></a>
                    </td>
                </tr>
                <?php } ?>
                <?php } else { ?>
                <tr>
                  <td class="text-center" colspan="8"><?php echo $text_no_results; ?></td>
                </tr>
                <?php } ?>
              </tbody>
            </table>
          </div>
        </form>
        <div class="row">
          <div class="col-sm-7 text-left"><?php echo $pagination; ?></div>
          <div class="col-sm-3 text-right">
          <select id="page-total-select">
          	<option <?php echo $page_limit == 20 ? "selected" : ""; ?> value="20">20条</option>
          	<option <?php echo $page_limit == 50 ? "selected" : ""; ?> value="50">50条</option>
          	<option <?php echo $page_limit == 100 ? "selected" : ""; ?> value="100">100条</option>
          </select>
		  </div>
          <div class="col-sm-2 text-right"><?php echo $results; ?></div>
        </div>
      </div>
    </div>
  </div>
  <script type="text/javascript"><!--
$('#button-filter').on('click', function() {
	url = 'index.php?route=sale/hz_order/processing&token=<?php echo $token; ?>&splitting_code=<?php echo $splitting_code;?>';

	var filter_order_id = $('input[name=\'filter_order_id\']').val();

	if (filter_order_id) {
		url += '&filter_order_id=' + encodeURIComponent(filter_order_id);
	}
	
	var filter_order_number = $('input[name=\'filter_order_number\']').val();

	if (filter_order_number) {
		url += '&filter_order_number=' + encodeURIComponent(filter_order_number);
	}
	
	var filter_shipping_firstname = $('input[name=\'filter_shipping_firstname\']').val();

	if (filter_shipping_firstname) {
		url += '&filter_shipping_firstname=' + encodeURIComponent(filter_shipping_firstname);
	}

	var filter_customer = $('input[name=\'filter_customer\']').val();

	if (filter_customer) {
		url += '&filter_customer=' + encodeURIComponent(filter_customer);
	}

	var filter_order_status = $('select[name=\'filter_order_status\']').val();

	if (filter_order_status != '*') {
		url += '&filter_order_status=' + encodeURIComponent(filter_order_status);
	}

	var filter_total = $('input[name=\'filter_total\']').val();

	if (filter_total) {
		url += '&filter_total=' + encodeURIComponent(filter_total);
	}

	var filter_date_added = $('input[name=\'filter_date_added\']').val();

	if (filter_date_added) {
		url += '&filter_date_added=' + encodeURIComponent(filter_date_added);
	}

	var filter_date_modified = $('input[name=\'filter_date_modified\']').val();

	if (filter_date_modified) {
		url += '&filter_date_modified=' + encodeURIComponent(filter_date_modified);
	}

	location = url;
});
//--></script>
  <script type="text/javascript"><!--
$('input[name=\'filter_customer\']').autocomplete({
	'source': function(request, response) {
		$.ajax({
			url: 'index.php?route=customer/customer/autocomplete&token=<?php echo $token; ?>&filter_name=' +  encodeURIComponent(request),
			dataType: 'json',
			success: function(json) {
				response($.map(json, function(item) {
					return {
						label: item['name'],
						value: item['customer_id']
					}
				}));
			}
		});
	},
	'select': function(item) {
		$('input[name=\'filter_customer\']').val(item['label']);
	}
});
//--></script>
  <script type="text/javascript"><!--
  $('#all').on('click', function() {
  	if(!$(this).is(':checked')) {
		$('#button-parcel').prop('disabled', true);
	}
  })
$('input[type^=\'checkbox\']').on('change', function() {
	$('#button-parcel, #button-invoice').prop('disabled', true);
	$('#button-excel, #button-invoice').prop('disabled', true);
	$('#button-shipping, #button-invoice').prop('disabled', true);
	if($(this).is(':checked')) {
		$('#button-parcel').prop('disabled', false);
		$('#button-excel').prop('disabled', false);
		$('#button-shipping').prop('disabled', false);
	}
	
	if(!$(this).is(':checked')) {
		$('#button-shipping').prop('disabled', true);
		$('#button-excel').prop('disabled', true);
		$('#button-parcel').prop('disabled', true);
	}
	
	var selected = $('input[name^=\'selected\']:checked');

	if (selected.length) {
		$('#button-shipping').prop('disabled', false);
		$('#button-excel').prop('disabled', false);
		$('#button-parcel').prop('disabled', false);
	}

	for (i = 0; i < selected.length; i++) {
		if ($(selected[i]).parent().find('input[name^=\'shipping_code\']').val()) {
			$('#button-shipping').prop('disabled', false);
			$('#button-excel').prop('disabled', false);
			break;
		}
	}
});

$('input[name^=\'selected\']:first').trigger('change');

// Login to the API
var token = '';

$.ajax({
	url: '<?php echo $store; ?>catalog/index.php?route=api/login',
	type: 'post',
	data: 'key=<?php echo $api_key; ?>',
	dataType: 'json',
	crossDomain: true,
	success: function(json) {
	   setTimeout(function (){
	        $('.alert').remove();
	   },3000)
        if (json['error']) {
    		if (json['error']['key']) {
    			$('#content > .container-fluid').prepend('<div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> ' + json['error']['key'] + ' <button type="button" class="close" data-dismiss="alert">&times;</button></div>');
    		}

            //if (json['error']['ip']) {
    			//$('#content > .container-fluid').prepend('<div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> ' + json['error']['ip'] + ' <button type="button" id="button-ip-add" data-loading-text="<?php echo $text_loading; ?>" class="btn btn-danger btn-xs pull-right"><i class="fa fa-plus"></i> <?php echo $button_ip_add; ?></button></div>');
    		//}
        }

		if (json['token']) {
			token = json['token'];
		}
	},
	error: function(xhr, ajaxOptions, thrownError) {
		alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
	}
});

$(document).delegate('#page-total-select', 'change', function() {
	$.ajax({
		url: 'index.php?route=sale/hz_order/setLimitAction&token=<?php echo $token; ?>&limit=' + $(this).val(),
		type: 'post',
		data: 'ip=<?php echo $api_ip; ?>',
		dataType: 'json',
		beforeSend: function() {
			$('#button-ip-add').button('loading');
		},
		complete: function() {
			$('#button-ip-add').button('reset');
		},
		success: function(json) {
			location.reload();
		},
		error: function(xhr, ajaxOptions, thrownError) {
			alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
		}
	});
});

$(document).delegate('#button-ip-add', 'click', function() {
	$.ajax({
		url: 'index.php?route=user/api/addip&token=<?php echo $token; ?>&api_id=<?php echo $api_id; ?>',
		type: 'post',
		data: 'ip=<?php echo $api_ip; ?>',
		dataType: 'json',
		beforeSend: function() {
			$('#button-ip-add').button('loading');
		},
		complete: function() {
			$('#button-ip-add').button('reset');
		},
		success: function(json) {
			$('.alert').remove();

			if (json['error']) {
				$('#content > .container-fluid').prepend('<div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> ' + json['error'] + ' <button type="button" class="close" data-dismiss="alert">&times;</button></div>');
			}

			if (json['success']) {
				$('#content > .container-fluid').prepend('<div class="alert alert-success"><i class="fa fa-check-circle"></i> ' + json['success'] + ' <button type="button" class="close" data-dismiss="alert">&times;</button></div>');
			}
		},
		error: function(xhr, ajaxOptions, thrownError) {
			alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
		}
	});
});

$('input[id^=\'shipping_id\']').bind("input propertychange",function(){
	$(this).siblings("#order-edit").hide();
	$(this).siblings("#order-save").removeClass('hide');
});

$('button[id^=\'order-edit\']').on('click', function(e) {
	var order_id = $(this).attr('data-order');
	$(this).siblings("input").removeAttr("readonly");
	$(this).siblings("input").focus();
});

$('button[id^=\'order-save\']').on('click', function(e) {
	var order_id = $(this).attr('data-order');
	var shipping_id = $(this).siblings("input").val();
	var splitting_company = $(this).siblings("select").val();
	var $this = $(this);
	if(order_id && shipping_id){
		$.ajax({
			url: '/admin/index.php?route=sale/hz_order/ajaxEdit&token=<?php echo $token; ?>&order_id=' + order_id + '&shipping_id=' + shipping_id + '&splitting_company=' + splitting_company,
			dataType: 'json',
			crossDomain: true,
			beforeSend: function() {
				//$(this).button('loading');
			},
			complete: function() {
				//$(this).button('reset');
			},
			success: function(json) {
				$('.alert').remove();

				if (json['error']) {
					$('#content > .container-fluid').prepend('<div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> ' + json['error'] + ' <button type="button" class="close" data-dismiss="alert">&times;</button></div>');
				}

				if (json['success']) {
					$this.addClass('hide');
					$this.siblings("#shipping_id").attr("readonly","readonly");
					$this.siblings("#order-edit").show();
					$this.siblings("#order-edit").removeClass('hide');
					$('#content > .container-fluid').prepend('<div class="alert alert-success"><i class="fa fa-check-circle"></i> ' + json['success'] + ' <button type="button" class="close" data-dismiss="alert">&times;</button></div>');
				}
			},
			error: function(xhr, ajaxOptions, thrownError) {
				alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
			}
		});
	}
});

$('button[id^=\'button-delete\']').on('click', function(e) {
	if (confirm('<?php echo $text_confirm; ?>')) {
		var node = this;

		$.ajax({
			url: '<?php echo $store; ?>catalog/index.php?route=api/order/delete&token=' + token + '&order_id=' + $(node).val(),
			dataType: 'json',
			crossDomain: true,
			beforeSend: function() {
				$(node).button('loading');
			},
			complete: function() {
				$(node).button('reset');
			},
			success: function(json) {
				$('.alert').remove();

				if (json['error']) {
					$('#content > .container-fluid').prepend('<div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> ' + json['error'] + ' <button type="button" class="close" data-dismiss="alert">&times;</button></div>');
				}

				if (json['success']) {
					$('#content > .container-fluid').prepend('<div class="alert alert-success"><i class="fa fa-check-circle"></i> ' + json['success'] + ' <button type="button" class="close" data-dismiss="alert">&times;</button></div>');
				}
			},
			error: function(xhr, ajaxOptions, thrownError) {
				alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
			}
		});
	}
});
$('#button-upload').on('click', function() {
	$('#form-upload').remove();

	$('body').prepend('<form enctype="multipart/form-data" id="form-upload" style="display: none;"><input type="file" name="inputExcel" value="" /></form>');

	$('#form-upload input[name=\'inputExcel\']').trigger('click');

	if (typeof timer != 'undefined') {
    	clearInterval(timer);
	}

	timer = setInterval(function() {
		if ($('#form-upload input[name=\'inputExcel\']').val() != '') {
			clearInterval(timer);

			$.ajax({
				url: '/admin/index.php?route=sale/hz_order/inputExcelAction&token=<?php echo $token; ?>',
				type: 'post',
				dataType: 'json',
				data: new FormData($('#form-upload')[0]),
				cache: false,
				contentType: false,
				processData: false,
				beforeSend: function() {
					$('#button-upload i').replaceWith('<i class="fa fa-circle-o-notch fa-spin"></i>');
					$('#button-upload').prop('disabled', true);
				},
				complete: function() {
					$('#button-upload i').replaceWith('<i class="fa fa-upload"></i>');
					$('#button-upload').prop('disabled', false);
				},
				success: function(json) {
					if (json['error']) {
						alert(json['error']);
					}

					if (json['success']) {
						alert(json['success']);

						$('#button-refresh').trigger('click');
					}
					location.reload();
				},
				error: function(xhr, ajaxOptions, thrownError) {
					//alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
				}
			});
		}
	}, 500);
});
//--></script>
  <script src="view/javascript/jquery/datetimepicker/bootstrap-datetimepicker.min.js" type="text/javascript"></script>
  <link href="view/javascript/jquery/datetimepicker/bootstrap-datetimepicker.min.css" type="text/css" rel="stylesheet" media="screen" />
  <script type="text/javascript"><!--
$('.date').datetimepicker({
	pickTime: false
});
//--></script></div>
<script type="text/javascript">
	$(function () {
		$('[data-toggle="popover"]').each(function () {
        //var element = $(this).children("#product");
        var element = $(this).children("#product");
        var id = $(this).children("#product").attr('data-order');
        var products = $("#order-products-"+id).text();
        var obj = JSON.parse(products);
        //console.log(order);
        var txt = element.html();
        element.popover({
          trigger: 'manual',
          placement: 'bottom', //top, bottom, left or right
          title: txt,
          html: 'true',
          content: ContentMethod(txt, obj),
        }).on("mouseenter", function () {
          var _this = this;
          $(this).popover("show");
          $(this).siblings(".popover").on("mouseleave", function () {
            $(_this).popover('hide');
          });
        }).on("mouseleave", function () {
          $(this).popover("hide");
          var _this = this;
          setTimeout(function () {
            if (!$(".popover:hover").length) {
              $(_this).popover("hide")
            }
          }, 100);
        });
      });
    });
    function ContentMethod(txt, order) {
    	var products = '<table class="table table-bordered"><tr><td>商品名称</td><td>商品价格</td><td>商品数量</td><td>小计</td></tr>';
    	for(var o in order){
    		products += '<tr><td>' + order[o].name + '</td><td>' + order[o].price + '</td><td>' + order[o].quantity + '</td><td>' + order[o].total + '</td></tr>';
        }
        return products += '</table>';
    }
</script>
<?php echo $footer; ?>
