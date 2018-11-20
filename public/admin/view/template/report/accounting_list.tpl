<?php echo $header; ?><?php echo $column_left; ?>

<div id="content">
<div class="page-header">
    <div class="container-fluid">
      <h1>成本核算</h1>
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
					<h3 class="panel-title"><i class="fa fa-list"></i> 订单成本核算</h3>
				</div>
				<div class="well">
					<div class="row">
						<div class="col-sm-4">
							<div class="form-group" style="position: relative">
								<label class="control-label" for="input-month-added">查询月份</label>
								<div class="input-group date">
									<input type="month" name="filter_month" value="" placeholder="查询月份" data-date-format="YYYY-MM" id="input-month-added" class="form-control">
								</div>
								<button type="button" id="search-by-ajax" class="btn btn-primary" style="position: absolute; left: 200px; top: 38px;"><i class="fa fa-search"></i> 搜索</button>
							</div>
						</div>
					</div>
				</div>
				<div class="panel-body" style="width: auto;">
					<form method="post" enctype="multipart/form-data" id="form-product">
						<div class="table-responsive">
							<table class="table table-bordered table-hover">
								<thead class="big-excel-thead">
								<tr>
									<td class="text-center" rowspan="3">日期</td>
									<td class="text-center" rowspan="2" colspan="2">订单数</td>
									<td class="text-center" rowspan="2" colspan="2">运费</td>
									<td class="text-center" colspan="6">5英寸商品</td>
									<td class="text-center" colspan="6">6英寸商品</td>
									<td class="text-center" colspan="3">LOMO卡</td>
									<td class="text-center" colspan="3">照片卡</td>
									<td class="text-center" colspan="6">方格1x1(黑色边框)</td>
									<td class="text-center" colspan="6">方格1x1(白色边框)</td>
									<td class="text-center" colspan="6">方格2x2(黑色边框)</td>
									<td class="text-center" colspan="6">方格2x2(白色边框)</td>
									<td class="text-center" colspan="6">方格3x3(黑色边框)</td>
									<td class="text-center" colspan="6">方格3x3(白色边框)</td>
									<td class="text-center" colspan="6">方格4x4(黑色边框)</td>
									<td class="text-center" colspan="6">方格4x4(白色边框)</td>
									<td class="text-center" colspan="6">海报</td>
									<td class="text-center" colspan="6">记忆盒子</td>
									<td class="text-center" colspan="6">相册2043</td>
									<td class="text-center" colspan="6">相册2045</td>
									<td class="text-center" colspan="6">相册2046</td>
									<td class="text-center" colspan="6">画册</td>
								</tr>
								<tr>
									<!--五英寸-->
									<td class="text-center" colspan="3">山东</td>
									<td class="text-center" colspan="3">杭州</td>
									<!--六英寸-->
									<td class="text-center" colspan="3">山东</td>
									<td class="text-center" colspan="3">杭州</td>
									<!--LOMO卡-->
									<td class="text-center" colspan="3">山东</td>
									<!--照片卡-->
									<td class="text-center" colspan="3">山东</td>
									<!--方格1x1黑-->
									<td class="text-center" colspan="3">山东</td>
									<td class="text-center" colspan="3">杭州</td>
									<!--方格1x1白-->
									<td class="text-center" colspan="3">山东</td>
									<td class="text-center" colspan="3">杭州</td>
									<!--方格2x2黑-->
									<td class="text-center" colspan="3">山东</td>
									<td class="text-center" colspan="3">杭州</td>
									<!--方格2x2白-->
									<td class="text-center" colspan="3">山东</td>
									<td class="text-center" colspan="3">杭州</td>
									<!--方格3x3黑-->
									<td class="text-center" colspan="3">山东</td>
									<td class="text-center" colspan="3">杭州</td>
									<!--方格3x3白-->
									<td class="text-center" colspan="3">山东</td>
									<td class="text-center" colspan="3">杭州</td>
									<!--方格4x4黑-->
									<td class="text-center" colspan="3">山东</td>
									<td class="text-center" colspan="3">杭州</td>
									<!--方格4x4白-->
									<td class="text-center" colspan="3">山东</td>
									<td class="text-center" colspan="3">杭州</td>
									<!--海报-->
									<td class="text-center" colspan="3">山东</td>
									<td class="text-center" colspan="3">杭州</td>
									<!--记忆盒子-->
									<td class="text-center" colspan="3">山东</td>
									<td class="text-center" colspan="3">杭州</td>
									<!--相册2043-->
									<td class="text-center" colspan="3">山东</td>
									<td class="text-center" colspan="3">杭州</td>
									<!--相册2045-->
									<td class="text-center" colspan="3">山东</td>
									<td class="text-center" colspan="3">杭州</td>
									<!--相册2046-->
									<td class="text-center" colspan="3">山东</td>
									<td class="text-center" colspan="3">杭州</td>
									<!--画册-->
									<td class="text-center" colspan="3">山东</td>
									<td class="text-center" colspan="3">杭州</td>
								</tr>
								<tr>
									<td class="text-center">山东</td>
									<td class="text-center">杭州</td>
									<td class="text-center">山东</td>
									<td class="text-center">杭州</td>
									<!--五英寸商品-->
									<td class="text-center">冲印数量</td>
									<td class="text-center">件数</td>
									<td class="text-center">合计成本</td>
									<td class="text-center">冲印数量</td>
									<td class="text-center">件数</td>
									<td class="text-center">合计成本</td>
									<!--六英寸商品-->
									<td class="text-center">冲印数量</td>
									<td class="text-center">件数</td>
									<td class="text-center">合计成本</td>
									<td class="text-center">冲印数量</td>
									<td class="text-center">件数</td>
									<td class="text-center">合计成本</td>
									<!--LOMO卡-->
									<td class="text-center">冲印数量</td>
									<td class="text-center">件数</td>
									<td class="text-center">合计成本</td>
									<!--照片卡-->
									<td class="text-center">冲印数量</td>
									<td class="text-center">件数</td>
									<td class="text-center">合计成本</td>
									<!--方格1x1黑-->
									<td class="text-center">冲印数量</td>
									<td class="text-center">件数</td>
									<td class="text-center">合计成本</td>
									<td class="text-center">冲印数量</td>
									<td class="text-center">件数</td>
									<td class="text-center">合计成本</td>
									<!--方格1x1白-->
									<td class="text-center">冲印数量</td>
									<td class="text-center">件数</td>
									<td class="text-center">合计成本</td>
									<td class="text-center">冲印数量</td>
									<td class="text-center">件数</td>
									<td class="text-center">合计成本</td>
									<!--方格2x2黑-->
									<td class="text-center">冲印数量</td>
									<td class="text-center">件数</td>
									<td class="text-center">合计成本</td>
									<td class="text-center">冲印数量</td>
									<td class="text-center">件数</td>
									<td class="text-center">合计成本</td>
									<!--方格2x2白-->
									<td class="text-center">冲印数量</td>
									<td class="text-center">件数</td>
									<td class="text-center">合计成本</td>
									<td class="text-center">冲印数量</td>
									<td class="text-center">件数</td>
									<td class="text-center">合计成本</td>
									<!--方格3x3黑-->
									<td class="text-center">冲印数量</td>
									<td class="text-center">件数</td>
									<td class="text-center">合计成本</td>
									<td class="text-center">冲印数量</td>
									<td class="text-center">件数</td>
									<td class="text-center">合计成本</td>
									<!--方格3x3白-->
									<td class="text-center">冲印数量</td>
									<td class="text-center">件数</td>
									<td class="text-center">合计成本</td>
									<td class="text-center">冲印数量</td>
									<td class="text-center">件数</td>
									<td class="text-center">合计成本</td>
									<!--方格4x4黑-->
									<td class="text-center">冲印数量</td>
									<td class="text-center">件数</td>
									<td class="text-center">合计成本</td>
									<td class="text-center">冲印数量</td>
									<td class="text-center">件数</td>
									<td class="text-center">合计成本</td>
									<!--方格4x4白-->
									<td class="text-center">冲印数量</td>
									<td class="text-center">件数</td>
									<td class="text-center">合计成本</td>
									<td class="text-center">冲印数量</td>
									<td class="text-center">件数</td>
									<td class="text-center">合计成本</td>
									<!--海报-->
									<td class="text-center">冲印数量</td>
									<td class="text-center">件数</td>
									<td class="text-center">合计成本</td>
									<td class="text-center">冲印数量</td>
									<td class="text-center">件数</td>
									<td class="text-center">合计成本</td>
									<!--记忆盒子-->
									<td class="text-center">冲印数量</td>
									<td class="text-center">件数</td>
									<td class="text-center">合计成本</td>
									<td class="text-center">冲印数量</td>
									<td class="text-center">件数</td>
									<td class="text-center">合计成本</td>
									<!--相册2043-->
									<td class="text-center">冲印数量</td>
									<td class="text-center">件数</td>
									<td class="text-center">合计成本</td>
									<td class="text-center">冲印数量</td>
									<td class="text-center">件数</td>
									<td class="text-center">合计成本</td>
									<!--相册2045-->
									<td class="text-center">冲印数量</td>
									<td class="text-center">件数</td>
									<td class="text-center">合计成本</td>
									<td class="text-center">冲印数量</td>
									<td class="text-center">件数</td>
									<td class="text-center">合计成本</td>
									<!--相册2046-->
									<td class="text-center">冲印数量</td>
									<td class="text-center">件数</td>
									<td class="text-center">合计成本</td>
									<td class="text-center">冲印数量</td>
									<td class="text-center">件数</td>
									<td class="text-center">合计成本</td>
									<!--画册-->
									<td class="text-center">冲印数量</td>
									<td class="text-center">件数</td>
									<td class="text-center">合计成本</td>
									<td class="text-center">冲印数量</td>
									<td class="text-center">件数</td>
									<td class="text-center">合计成本</td>
								</tr>
								</thead>
								<tbody class="big-excel-tbody">

								</tbody>
								<tfoot class="big-excel-tfoot">
								<tr>
									<td class="text-center">合计</td>
								</tr>
								</tfoot>
							</table>
						</div>
					</form>
				</div>
			</div>
		</div>
		<script type="text/javascript"><!--
		$('#button-filter').on('click', function() {
			var url = 'index.php?route=catalog/product&token=U8ltDpD5OKd6s57OTNzwes9ub0QW6tc6';

			var filter_name = $('input[name=\'filter_name\']').val();

			if (filter_name) {
				url += '&filter_name=' + encodeURIComponent(filter_name);
			}

			var filter_model = $('input[name=\'filter_model\']').val();

			if (filter_model) {
				url += '&filter_model=' + encodeURIComponent(filter_model);
			}

			var filter_price = $('input[name=\'filter_price\']').val();

			if (filter_price) {
				url += '&filter_price=' + encodeURIComponent(filter_price);
			}

			var filter_quantity = $('input[name=\'filter_quantity\']').val();

			if (filter_quantity) {
				url += '&filter_quantity=' + encodeURIComponent(filter_quantity);
			}

			var filter_status = $('select[name=\'filter_status\']').val();

			if (filter_status != '*') {
				url += '&filter_status=' + encodeURIComponent(filter_status);
			}

			location = url;
		});
		//--></script>
		<script type="text/javascript">
		var dataMainPathArr;
		var thisHost = location.host;
		if(thisHost.indexOf('dev') == 0){
			dataMainPathArr = [
				//订单数
				'order_count.1365',
				'order_count.933',
				//运费
				'express_cost.1365',
				'express_cost.933',
				//5英寸 53
				'products_cost.53.1365.number',
				'products_cost.53.1365.quantity',
				'products_cost.53.1365.cost',
				'products_cost.53.933.number',
				'products_cost.53.933.quantity',
				'products_cost.53.933.cost',
				//6英寸 54
				'products_cost.54.1365.number',
				'products_cost.54.1365.quantity',
				'products_cost.54.1365.cost',
				'products_cost.54.933.number',
				'products_cost.54.933.quantity',
				'products_cost.54.933.cost',
				//LOMO 68
				'products_cost.68.1365.number',
				'products_cost.68.1365.quantity',
				'products_cost.68.1365.cost',
				//照片卡 69
				'products_cost.69.1365.number',
				'products_cost.69.1365.quantity',
				'products_cost.69.1365.cost',
				//1x1黑 55
				'products_cost.55.1365.number',
				'products_cost.55.1365.quantity',
				'products_cost.55.1365.cost',
				'products_cost.55.933.number',
				'products_cost.55.933.quantity',
				'products_cost.55.933.cost',
				//1x1白 56
				'products_cost.56.1365.number',
				'products_cost.56.1365.quantity',
				'products_cost.56.1365.cost',
				'products_cost.56.933.number',
				'products_cost.56.933.quantity',
				'products_cost.56.933.cost',
				//2x2黑 71
				'products_cost.71.1365.number',
				'products_cost.71.1365.quantity',
				'products_cost.71.1365.cost',
				'products_cost.71.933.number',
				'products_cost.71.933.quantity',
				'products_cost.71.933.cost',
				//2x2白 72
				'products_cost.72.1365.number',
				'products_cost.72.1365.quantity',
				'products_cost.72.1365.cost',
				'products_cost.72.933.number',
				'products_cost.72.933.quantity',
				'products_cost.72.933.cost',
				//3x3黑 73
				'products_cost.73.1365.number',
				'products_cost.73.1365.quantity',
				'products_cost.73.1365.cost',
				'products_cost.73.933.number',
				'products_cost.73.933.quantity',
				'products_cost.73.933.cost',
				//3x3白 74
				'products_cost.74.1365.number',
				'products_cost.74.1365.quantity',
				'products_cost.74.1365.cost',
				'products_cost.74.933.number',
				'products_cost.74.933.quantity',
				'products_cost.74.933.cost',
				//4x4黑 75
				'products_cost.75.1365.number',
				'products_cost.75.1365.quantity',
				'products_cost.75.1365.cost',
				'products_cost.75.933.number',
				'products_cost.75.933.quantity',
				'products_cost.75.933.cost',
				//4x4白 76
				'products_cost.76.1365.number',
				'products_cost.76.1365.quantity',
				'products_cost.76.1365.cost',
				'products_cost.76.933.number',
				'products_cost.76.933.quantity',
				'products_cost.76.933.cost',
				//海报 58
				'products_cost.58.1365.number',
				'products_cost.58.1365.quantity',
				'products_cost.58.1365.cost',
				'products_cost.58.933.number',
				'products_cost.58.933.quantity',
				'products_cost.58.933.cost',
				//记忆盒子 57
				'products_cost.57.1365.number',
				'products_cost.57.1365.quantity',
				'products_cost.57.1365.cost',
				'products_cost.57.933.number',
				'products_cost.57.933.quantity',
				'products_cost.57.933.cost',
				//相册2043 77
				'products_cost.77.1365.number',
				'products_cost.77.1365.quantity',
				'products_cost.77.1365.cost',
				'products_cost.77.933.number',
				'products_cost.77.933.quantity',
				'products_cost.77.933.cost',
				//相册2045 60
				'products_cost.60.1365.number',
				'products_cost.60.1365.quantity',
				'products_cost.60.1365.cost',
				'products_cost.60.933.number',
				'products_cost.60.933.quantity',
				'products_cost.60.933.cost',
				//相册2046 79
				'products_cost.79.1365.number',
				'products_cost.79.1365.quantity',
				'products_cost.79.1365.cost',
				'products_cost.79.933.number',
				'products_cost.79.933.quantity',
				'products_cost.79.933.cost',
				//画册 81
				'products_cost.81.1365.number',
				'products_cost.81.1365.quantity',
				'products_cost.81.1365.cost',
				'products_cost.81.933.number',
				'products_cost.81.933.quantity',
				'products_cost.81.933.cost'
			];
		}else {
			dataMainPathArr = [
				//订单数
				'order_count.1365',
				'order_count.933',
				//运费
				'express_cost.1365',
				'express_cost.933',
				//5英寸 53
				'products_cost.53.1365.number',
				'products_cost.53.1365.quantity',
				'products_cost.53.1365.cost',
				'products_cost.53.933.number',
				'products_cost.53.933.quantity',
				'products_cost.53.933.cost',
				//6英寸 54
				'products_cost.54.1365.number',
				'products_cost.54.1365.quantity',
				'products_cost.54.1365.cost',
				'products_cost.54.933.number',
				'products_cost.54.933.quantity',
				'products_cost.54.933.cost',
				//LOMO 55
				'products_cost.55.1365.number',
				'products_cost.55.1365.quantity',
				'products_cost.55.1365.cost',
				//照片卡 61
				'products_cost.61.1365.number',
				'products_cost.61.1365.quantity',
				'products_cost.61.1365.cost',
				//1x1黑 68
				'products_cost.68.1365.number',
				'products_cost.68.1365.quantity',
				'products_cost.68.1365.cost',
				'products_cost.68.933.number',
				'products_cost.68.933.quantity',
				'products_cost.68.933.cost',
				//1x1白 63
				'products_cost.63.1365.number',
				'products_cost.63.1365.quantity',
				'products_cost.63.1365.cost',
				'products_cost.63.933.number',
				'products_cost.63.933.quantity',
				'products_cost.63.933.cost',
				//2x2黑 69
				'products_cost.69.1365.number',
				'products_cost.69.1365.quantity',
				'products_cost.69.1365.cost',
				'products_cost.69.933.number',
				'products_cost.69.933.quantity',
				'products_cost.69.933.cost',
				//2x2白 64
				'products_cost.64.1365.number',
				'products_cost.64.1365.quantity',
				'products_cost.64.1365.cost',
				'products_cost.64.933.number',
				'products_cost.64.933.quantity',
				'products_cost.64.933.cost',
				//3x3黑 70
				'products_cost.70.1365.number',
				'products_cost.70.1365.quantity',
				'products_cost.70.1365.cost',
				'products_cost.70.933.number',
				'products_cost.70.933.quantity',
				'products_cost.70.933.cost',
				//3x3白 65
				'products_cost.65.1365.number',
				'products_cost.65.1365.quantity',
				'products_cost.65.1365.cost',
				'products_cost.65.933.number',
				'products_cost.65.933.quantity',
				'products_cost.65.933.cost',
				//4x4黑 71
				'products_cost.71.1365.number',
				'products_cost.71.1365.quantity',
				'products_cost.71.1365.cost',
				'products_cost.71.933.number',
				'products_cost.71.933.quantity',
				'products_cost.71.933.cost',
				//4x4白 66
				'products_cost.66.1365.number',
				'products_cost.66.1365.quantity',
				'products_cost.66.1365.cost',
				'products_cost.66.933.number',
				'products_cost.66.933.quantity',
				'products_cost.66.933.cost',
				//海报 60
				'products_cost.60.1365.number',
				'products_cost.60.1365.quantity',
				'products_cost.60.1365.cost',
				'products_cost.60.933.number',
				'products_cost.60.933.quantity',
				'products_cost.60.933.cost',
				//记忆盒子 67
				'products_cost.67.1365.number',
				'products_cost.67.1365.quantity',
				'products_cost.67.1365.cost',
				'products_cost.67.933.number',
				'products_cost.67.933.quantity',
				'products_cost.67.933.cost',
				//相册2043 50
				'products_cost.50.1365.number',
				'products_cost.50.1365.quantity',
				'products_cost.50.1365.cost',
				'products_cost.50.933.number',
				'products_cost.50.933.quantity',
				'products_cost.50.933.cost',
				//相册2045 51
				'products_cost.51.1365.number',
				'products_cost.51.1365.quantity',
				'products_cost.51.1365.cost',
				'products_cost.51.933.number',
				'products_cost.51.933.quantity',
				'products_cost.51.933.cost',
				//相册2046 52
				'products_cost.52.1365.number',
				'products_cost.52.1365.quantity',
				'products_cost.52.1365.cost',
				'products_cost.52.933.number',
				'products_cost.52.933.quantity',
				'products_cost.52.933.cost',
				//画册 62
				'products_cost.62.1365.number',
				'products_cost.62.1365.quantity',
				'products_cost.62.1365.cost',
				'products_cost.62.933.number',
				'products_cost.62.933.quantity',
				'products_cost.62.933.cost'
			];
			
		}
		
			
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


			function appendChild(date,data) {
				if(data.toString().split('.').length != 1){
					data = data.toFixed(2);
				}
				$('tr[data-date=\"'+ date +'\"]').append('<td class="text-center">'+ data +'</td>');
			}
			
			function jsGetMonth() {
				var date = new Date;
				var year = date.getFullYear();
				var month = (date.getMonth()+1) < 10? '0' + (date.getMonth()+1):(date.getMonth()+1);
				return searchMonth = year + '-' + month;
			}

			$('#search-by-ajax').on('click',function(){
				$('.big-excel-tbody').html('');
				$('.big-excel-tfoot').html('<tr><td class="text-center">合计</td></tr>');
				var searchMonth = $('#input-month-added').val()? $('#input-month-added').val():jsGetMonth();
				buildExcel(searchMonth,dataMainPathArr);
			});

			function buildExcel(month,dataPathArr) {
				$.ajax({
					url: location.protocol + '//' + location.host + '/admin/index.php?route=report/accounting/getData',
					dataType: 'json',
					data: {
						token: getQueryStringArgs().token,
						month: month
					},
					success: function(d){
						//console.log(d);
						var dateArr = [];
						for(var o in d.data){
							dateArr.push(o);
							$('.big-excel-tbody').append('<tr data-date="'+ o +'" title="'+ o +'"><td class="text-center">'+ o +'</td></tr>');
						}
						for(var i=0; i<dateArr.length; i++) {	//循环时间（纵向表头）
							for(var j=0; j<dataPathArr.length; j++) {	//循环配置（横向表头）
								var dataPathSplitWithSignAndArr = dataPathArr[j].split('&');
								var data = 0;
								for(var k=0; k<dataPathSplitWithSignAndArr.length; k++) {	//如果单元格是多个数据求和，循环该配置
									var dataPathSplitWithSignDotArr = dataPathSplitWithSignAndArr[k].split('.');
									var data1 = 'd.data["'+dateArr[i]+'"]';
									for(var l=0; l<dataPathSplitWithSignDotArr.length; l++) {	//循环配置内字符，补全地址
										data1 += '["'+ dataPathSplitWithSignDotArr[l] +'"]';
									}
									try{
										var data2 = eval(data1);
										if(!data2){
											data2 = 0;
										}
										data += data2;
									}catch(e) {
										data += 0;
									}
								}
								appendChild(dateArr[i],data);
							}
						}


						for (var m=0; m<dataPathArr.length; m++) {
							var sum = 0;
							$('.big-excel-tbody tr').each(function(n,dom){
								var $dom = $(dom);
								sum += parseFloat($dom.find('td').eq(m+1).html());
							});
							if(sum.toString().split('.').length != 1){
								sum = sum.toFixed(2);
							}
							$('.big-excel-tfoot tr').append('<td class="text-center">'+ sum +'</td>');
						}


					},
					error: function(e,f) {
						console.log(e);
						console.log(f);
					}
				})
			}
			$(function(){
				buildExcel(jsGetMonth(),dataMainPathArr);
			})

		</script>
		<script type="text/javascript"><!--
		$('input[name=\'filter_name\']').autocomplete({
			'source': function(request, response) {
				$.ajax({
					url: 'index.php?route=catalog/product/autocomplete&token=U8ltDpD5OKd6s57OTNzwes9ub0QW6tc6&filter_name=' +  encodeURIComponent(request),
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
				$('input[name=\'filter_name\']').val(item['label']);
			}
		});

		$('input[name=\'filter_model\']').autocomplete({
			'source': function(request, response) {
				$.ajax({
					url: 'index.php?route=catalog/product/autocomplete&token=U8ltDpD5OKd6s57OTNzwes9ub0QW6tc6&filter_model=' +  encodeURIComponent(request),
					dataType: 'json',
					success: function(json) {
						response($.map(json, function(item) {
							return {
								label: item['model'],
								value: item['product_id']
							}
						}));
					}
				});
			},
			'select': function(item) {
				$('input[name=\'filter_model\']').val(item['label']);
			}
		});
		//--></script></div>
	<?php echo $footer; ?>
