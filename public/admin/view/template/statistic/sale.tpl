<?php echo $header; ?><?php echo $column_left; ?>
<div id="content">
  <div class="page-header">
    <div class="container-fluid">
      <div class="pull-right">
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
  	<form method="post" enctype="multipart/form-data" target="" id="form-order">
  	<div class="panel-body">
        <div class="well">
          <div class="row">
          	<div class="col-sm-4">
          		<div class="form-group">
	                <label class="control-label" for="input-date-added">开始日期</label>
	                <div class="input-group date">
	                  <input type="text" name="filter_start_date" value="" placeholder="开始日期" data-date-format="YYYY-MM-DD" id="input-date-added" class="form-control" />
	                  <span class="input-group-btn">
	                  <button type="button" class="btn btn-default"><i class="fa fa-calendar"></i></button>
	                  </span></div>
              	 </div>
			</div>
			<div class="col-sm-4">
          		<div class="form-group">
	                <label class="control-label" for="input-date-added">结束日期</label>
	                <div class="input-group date">
	                  <input type="text" name="filter_end_date" value="" placeholder="结束日期" data-date-format="YYYY-MM-DD" id="input-date-added" class="form-control" />
	                  <span class="input-group-btn">
	                  <button type="button" class="btn btn-default"><i class="fa fa-calendar"></i></button>
	                  </span></div>
              	 </div>
              	 <button type="submit" form="form-order" id="button-filter" class="btn btn-primary pull-right"><i class="fa fa-search"></i> 搜索</button>
			</div>
          </div>
        </div>		              
  		
          <div class="table-responsive">
            <table class="table table-bordered table-hover">
              <thead>
                <tr>
                  <td class="text-left">
                  	<a href="">日期</a>
                  </td>
                  <td class="text-left">
                  	<a href="">活跃用户</a>
                  </td>    
                  <td class="text-left">
                  	<a href="">新增用户</a>
                  </td> 
                  <td class="text-left">
                  	<a href="">免费订单数</a>
                  </td> 
                  <td class="text-left">
                  	<a href="">付费订单数</a>
                  </td> 
                  <td class="text-left">
                  	<a href="">订单付款总额</a>
                  </td> 
                  <td class="text-left">
                  	<a href="">订单成本总额</a>
                  </td> 
                  <td class="text-left">
                  	<a href="">已付款未上传</a>
                  </td> 
                </tr>
              </thead>
              <tbody>
              <?php if ($list) { ?>
              <?php foreach ($list as $statistic) { ?>
                <tr>
                  <td class="text-left">
                  	<?php echo $statistic['date_added']; ?>
                  </td>  
                  <td class="text-left">
                  	<?php 
                  		echo @$dayActive[$statistic['date_added']] ? $dayActive[$statistic['date_added']] : 0;
                  	 ?>
                  </td>
                  <td class="text-left">
                  	<?php echo Accessor::either($statistic['user_nums'], 0); ?>
                  </td>
                  <td class="text-left">
                  	<?php echo Accessor::either($statistic['free_nums'], 0); ?>
                  </td>
                  <td class="text-left">
                  	<?php echo Accessor::either($statistic['order_nums'], 0); ?>
                  </td>
                  <td class="text-left">
                  	<?php echo Accessor::either($statistic['totals'], 0); ?>
                  </td>
                  <td class="text-left">
                  	<?php echo Accessor::either($statistic['day_cost'], 0); ?>
                  </td> 
                  <td class="text-left">
                     <?php 
                     	echo @$orderStatusPay[$statistic['date_added']] ? $orderStatusPay[$statistic['date_added']] : 0;
                  	 ?>
                  </td> 
                </tr>
                <?php } ?>
              <?php }else {?>
              <tr>
                  <td class="text-center" colspan="8"><?php echo $text_no_results; ?></td>
                </tr>
                <?php } ?>  
              </tbody>
            </table>
          </div>
        </form>
      <div class="row">
          <div class="col-sm-6 text-left"><?php echo $pagination; ?></div>
          <div class="col-sm-6 text-right"><?php echo $results; ?></div>
        </div>
                  <div class="table-responsive">
            <table class="table table-bordered table-hover">
              <thead>
                <tr>
                  <td class="text-left">
                  	<a href="">日期</a>
                  </td>
                  <td class="text-left">
                  	<a href="">订单编号</a>
                  </td>    
                  <td class="text-left">
                  	<a href="">用户名称</a>
                  </td> 
                  <td class="text-left">
                  	<a href="">收货人</a>
                  </td> 
                  <td class="text-left">
                  	<a href="">收货电话</a>
                  </td> 
                  <td class="text-left">
                  	<a href="">订单创建时间</a>
                  </td> 
                  <td class="text-left">
                  	<a href="">备注</a>
                  </td> 
                </tr>
              </thead>
              <tbody>
              <?php if ($order) { ?>
              <?php foreach ($order as $v) { ?>
                <tr>
                  <td class="text-left"><?php echo $v['date_added']; ?></td>  
                  <td class="text-left"><?php echo $v['order_number']; ?></td>
                  <td class="text-left">
                  	<?php echo $v['lastname']; ?>
                  </td>
                  <td class="text-left">
                  	<?php echo $v['shipping_firstname']; ?>
                  </td>
                  <td class="text-left">
                  	<?php echo $v['telephone']; ?>
                  </td>
                  <td class="text-left">
                  	<?php echo $v['date_added']; ?>
                  </td>
                  <td class="text-left cyp-comment-btn" style="cursor:pointer;"><?php echo $v['comment']; ?></td> 
                </tr>
                <?php } ?>
              <?php }else {?>
              <tr>
                  <td class="text-center" colspan="8"><?php echo $text_no_results; ?></td>
                </tr>
                <?php } ?>  
              </tbody>
            </table>
          </div>
      </div>
    </div>  
</div>

<div class="cyp-comment-inputbox" style="display: none; width:500px;height:400px; border: 1px solid #000000; position: fixed; left: 50%; top: 50%; margin-left: -250px; margin-top: -200px; z-index: 9999; background: #fff;">
	<h5 style="font-size: 20px; font-weight: bold; text-align: center;line-height: 40px;">添加备注</h5>
	<p style="font-size: 16px; text-align: center; line-height: 20px;">订单编号：<i></i></p>
	<textarea style="width: 400px; height: 200px; margin: 30px 0 0 50px; resize: none;"></textarea>
	<div class="cyp-yes-btn" style="display: block; width: 150px; height: 40px; border: 1px solid #000000; border-radius: 5px; font-size: 16px; font-weight: bold; cursor: pointer; line-height: 40px; text-align: center;position: absolute; left: 70px; bottom: 30px; background: #1872a2; color: #fff;">确定</div>
	<div class="cyp-no-btn" style="display: block; width: 150px; height: 40px; border: 1px solid #000000; border-radius: 5px; font-size: 16px; font-weight: bold; cursor: pointer; line-height: 40px; text-align: center;position: absolute; right: 70px; bottom: 30px; color: #000; ">取消</div>
</div>
<div class="cyp-tip" style="display: none; width: 150px; height: 40px; border: 1px solid #000000; border-radius: 5px; color: #fff; font-size: 16px; font-weight: bold; line-height: 40px; text-align: center; position: fixed; left: 50%; top: 50%; margin-left: -75px; margin-top: -20px; z-index: 9999; background: rgba(0,0,0,0.7);">添加备注成功</div>

  <script src="view/javascript/jquery/datetimepicker/bootstrap-datetimepicker.min.js" type="text/javascript"></script>
  <link href="view/javascript/jquery/datetimepicker/bootstrap-datetimepicker.min.css" type="text/css" rel="stylesheet" media="screen" />
  <script type="text/javascript">
$('.date').datetimepicker({
	pickTime: false
});
</script>      
<script>
	$(function(){
		var order_number,comment,that;
		
		//获取查询字符串参数
			function getQueryStringArgs() {
				var qs = (location.search.length > 0 ? location.search.substring(1) : ""),
					args = {},
					items = qs.length ? qs.split("&") : [],
					item = null,
					name = null,
					value = null,
					i = 0,
					len = items.length;
				for (i = 0; i < len; i++) {
					item = items[i].split('=');
					name = decodeURIComponent(item[0]);
					value = decodeURIComponent(item[1]);
					if (name.length) {
						args[name] = value;
					}
				}
				return args;
			}
	
		$('.cyp-comment-btn').on('click',function(){
			that = $(this);
			order_number = $(this).siblings().eq(1).html();
			comment = $(this).html();
			$('.cyp-comment-inputbox').find('i').html(order_number);
			$('.cyp-comment-inputbox').find('textarea').val(comment);
			$('.cyp-comment-inputbox').css('display','block');
		});
		$('.cyp-yes-btn').on('click',function(){
			comment = $('.cyp-comment-inputbox').find('textarea').val();
			$.ajax({
				url: location.protocol + '//' + location.host + '/admin/index.php?route=statistic/statistic/orderCommentAdd',
				dataType: 'text',
				data: {
					token: getQueryStringArgs().token,
					order_number: order_number,
					comment: comment
				},
				success: function(d){
					that.html(comment);
					$('.cyp-comment-inputbox').find('i').html('');
					$('.cyp-comment-inputbox').find('textarea').val('');
					$('.cyp-comment-inputbox').css('display','none');
					$('.cyp-tip').css('display','block');
					setTimeout(function(){
						$('.cyp-tip').css('display','none');
					},1000);
					
				},
				error: function(e){
					//alert('提交失败，请重试。');
					console.log(e);
				}
			})
		});
		$('.cyp-no-btn').on('click',function(){
			$('.cyp-comment-inputbox').find('i').html('');
			$('.cyp-comment-inputbox').find('textarea').val('');
			$('.cyp-comment-inputbox').css('display','none');
		});
		
	})
</script>
<?php echo $footer; ?>