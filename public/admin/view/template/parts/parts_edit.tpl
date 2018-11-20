<?php echo $header; ?><?php echo $column_left; ?>
<div id="content">
  <div class="page-header">
    <div class="container-fluid">
      <div class="pull-right">
        <button type="submit" form="form-product" data-toggle="tooltip" title="<?php echo $button_save; ?>" class="btn btn-primary"><i class="fa fa-save"></i></button>
        <a href="<?php echo $cancel;?>" data-toggle="tooltip" title="<?php echo $button_cancel; ?>" class="btn btn-default"><i class="fa fa-reply"></i></a></div>
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
					<form action="<?php echo $action ?>" method="post" enctype="multipart/form-data" id="form-category" class="form-horizontal">
						<div class="tab-content">
							<div class="tab-pane active" id="tab-data">
								<!--配件名称-->
								<div class="form-group">
									<label class="col-sm-2 control-label" for="input-name">配件名称：</label>
									<div class="col-sm-4">
										<input type="text" name="parts_name" value="<?php echo $data['parts_name']; ?>" placeholder="配件名称：" id="input-name" class="form-control" autocomplete="off">
									</div>
								</div>
								<!--配件编号-->
								<div class="form-group">
									<label class="col-sm-2 control-label" for="input-name">配件编号：</label>
									<div class="col-sm-4">
										<input type="text" name="numbering" value="<?php echo $data['numbering']; ?>" placeholder="配件编号：" id="input-name" class="form-control" autocomplete="off">
									</div>
								</div>
								<!--规格型号-->
								<div class="form-group">
									<label class="col-sm-2 control-label" for="input-spec">规格型号：</label>
									<div class="col-sm-4">
										<input type="text" name="spec" value="<?php echo $data['spec']; ?>" placeholder="规格型号：" id="input-spec" class="form-control" autocomplete="off">
									</div>
								</div>
								<!--成本价格-->
								<div class="form-group">
									<label class="col-sm-2 control-label" for="input-cost">成本价格：</label>
									<div class="col-sm-4">
										<input type="text" name="price" value="<?php echo $data['price']; ?>" placeholder="成本价格：" id="input-cost" class="form-control" autocomplete="off">
									</div>
								</div>
								<!--库存数量-->
								<div class="form-group">
									<label class="col-sm-2 control-label" for="input-number">库存数量：</label>
									<div class="col-sm-4">
										<input type="text" name="quantity" value="<?php echo $data['quantity']; ?>" placeholder="库存数量：" id="input-number" class="form-control" autocomplete="off">
									</div>
								</div>
								<!--关联商品列表-->
								<div class="link-product-list">
									<?php if (!empty($data['partsProduct'])) { 
									$i = 1;
									?>
										<?php foreach ($data['partsProduct'] as $k => $v) { ?>
												<div class="form-group" style="border-top: 1px solid #ededed;" data-num="<?php echo $i; ?>">
													<label class="col-sm-2 control-label" for="<?php echo 'input-link-' . $i; ?>"><?php echo '关联商品' . $i. '：'; ?></label>
													<div class="col-sm-3">
														<input type="text" name="<?php echo 'linkProduct' . $i; ?>" value="<?php echo $v['name']; ?>" placeholder="<?php echo '关联商品' . $i. '：'; ?>" id="input-link-1" class="form-control" autocomplete="off">
														<?php if ($v['product_id']) { ?>
															<input type="hidden" name="<?php echo 'linkProductId' . $i; ?>" value="<?php echo $v['product_id']; ?>" id="<?php echo 'input-linkId-' . $i; ?>" class="form-control" autocomplete="off">
														<?php } else { ?>
															<input type="hidden" name="<?php echo 'linkProductId' . $i; ?>" value="" id="<?php echo 'input-linkId-' . $i; ?>" class="form-control" autocomplete="off">
														<?php } ?>
													</div>
													<label class="col-sm-2 control-label" for="<?php echo 'input-del-' . $i; ?>">扣减数量：</label>
													<div class="col-sm-3">
														<input type="text" name="<?php echo 'delNum' . $i; ?>" value="<?php echo $v['deduction_number']; ?>" placeholder="扣减数量：" id="input-del-1" class="form-control" autocomplete="off">
													</div>
													<a href="javascript:void(0)" class="button-addButton" data-num="<?php echo $i; ?>" style="display:inline-block; padding: 0 30px; text-align: center; line-height: 30px; background: #fff; border: 1px solid #cccccc; border-radius: 5px; color: #555;">删除</a>
												</div>
										<?php $i++; } ?>
								    <?php } else { ?>
											   <div class="form-group" style="border-top: 1px solid #ededed;" data-num="1">
													<label class="col-sm-2 control-label" for="input-link-1">关联商品1：</label>
													<div class="col-sm-3">
														<input type="text" name="linkProduct1" value="" placeholder="关联商品1：" id="input-link-1" class="form-control" autocomplete="off">
														<input type="hidden" name="linkProductId1" value="" id="input-linkId-1" class="form-control" autocomplete="off">
													</div>
													<label class="col-sm-2 control-label" for="input-del-1">扣减数量：</label>
													<div class="col-sm-3">
														<input type="text" name="delNum1" value="" placeholder="扣减数量：" id="input-del-1" class="form-control" autocomplete="off">
													</div>
													<a href="javascript:void(0)" class="button-addButton" data-num="1" style="display:inline-block; padding: 0 30px; text-align: center; line-height: 30px; background: #fff; border: 1px solid #cccccc; border-radius: 5px; color: #555;">删除</a>
												</div>
								    <?php } ?>
							    </div>
								<!--添加按钮-->
								<div class="addButton-box">
									<a href="javascript:void(0)" id="button-addButton" style="display:inline-block; padding: 0 30px; text-align: center; line-height: 30px; margin: 10px 200px; background: #fff; border: 1px solid #cccccc; border-radius: 5px; color: #555;">+ 添加关联商品</a>
								</div>
								<!--配件状态-->
								<div class="form-group" style="border-top: 1px solid #ededed;">
									<label class="col-sm-2 control-label" for="input-partstate">配件状态：</label>
									<div class="col-sm-4">
										<select name="status" id="input-partstate" class="form-control" placeholder="配件状态：">
											<?php if ($data['status'] != '') {
												if ($data['status'] == 0) { ?>
												 	<option value="0">停用</option>
												 	<option value="1">启用</option>
												<?php } else { ?>
													<option value="1">启用</option>
													<option value="0">停用</option>
												<?php } ?>
											<?php } else { ?>
												<option value="1">启用</option>
											    <option value="0">停用</option>
											<?php } ?>
    									</select>
									</div>
								</div>

							</div>
						</div>
					</form>
				</div>
    </div>
  </div>
		<script type="text/javascript"><!--
		function deleteLinkList() {
			$('.button-addButton').on('click',function(){
				var num = $(this).attr('data-num');
				$('.form-group[data-num=\"'+ num +'\"]').remove();
			})
		}
		getProduct(1);
		deleteLinkList();
		$('#button-addButton').on('click',function(){
			var oldNum = parseInt($('.link-product-list').find('.form-group').last().attr('data-num'));
			var newNum = oldNum? (oldNum+1): 1;
			var appendChildDom = '' +
					'<div class="form-group" style="border-top: 1px solid #ededed;" data-num="'+ newNum +'">' +
						'<label class="col-sm-2 control-label" for="input-link-'+ newNum +'">关联商品'+ newNum +'：</label>' +
						'<div class="col-sm-3">' +
							'<input type="text" name="linkProduct'+ newNum +'" value="" placeholder="关联商品'+ newNum +'：" id="input-link-'+ newNum +'" class="form-control" autocomplete="off">' + 
							'<input type="hidden" name="linkProductId'+ newNum +'" value="" id="input-linkId-'+ newNum +'" class="form-control" autocomplete="off">' + 
						'</div>' +
						'<label class="col-sm-2 control-label" for="input-del-'+ newNum +'">扣减数量：</label>' +
						'<div class="col-sm-3">' +
							'<input type="text" name="delNum'+ newNum +'" value="" placeholder="扣减数量：" id="input-del-'+ newNum +'" class="form-control" autocomplete="off">' +
						'</div>' +
							'<a href="javascript:void(0)" class="button-addButton" data-num="'+ newNum +'" style="display:inline-block; padding: 0 30px; text-align: center; line-height: 30px; background: #fff; border: 1px solid #cccccc; border-radius: 5px; color: #555;">删除</a>' +
					'</div>';
			$('.link-product-list').append(appendChildDom);
			deleteLinkList();
			getProduct(newNum);
		});
  function getProduct(Num) {
  	var $Dom = $("#input-link-"+  Num);
	$Dom.autocomplete({
		'source': function(request, response) {
			$.ajax({
				url: 'index.php?route=parts/parts/autocomplete&token=<?php echo $token; ?>&filter_name=' +  encodeURIComponent(request),
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
			$Dom.val(item['label']);
			$("#input-linkId-" + Num).val(item['value']);
		}
	});
  }

//--></script>
  <script type="text/javascript"><!--
$('#language a:first').tab('show');
$('#option a:first').tab('show');
//--></script></div>
<?php echo $footer; ?>
