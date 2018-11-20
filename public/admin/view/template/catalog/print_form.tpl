<?php echo $header; ?><?php echo $column_left; ?>
<div id="content">
  <div class="page-header">
    <div class="container-fluid">
      <div class="pull-right">
        <button type="submit" form="form-manufacturer" data-toggle="tooltip" title="<?php echo $button_save; ?>" class="btn btn-primary"><i class="fa fa-save"></i></button>
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
        <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form-manufacturer" class="form-horizontal">
          <div class="form-group required">
            <label class="col-sm-2 control-label" for="input-name"><?php echo $entry_name; ?></label>
            <div class="col-sm-4">
              <input type="text" name="print_name" value="<?php echo $print_name; ?>" placeholder="<?php echo $entry_name; ?>" id="input-name" class="form-control" />
              <?php if ($error_name) { ?>
              <div class="text-danger"><?php echo $error_name; ?></div>
              <?php } ?>
            </div>
          </div>
          <div class="form-group">
            <label class="col-sm-2 control-label" for="input-sort-order"><?php echo $entry_size; ?></label>
            <div class="col-sm-4">
              <input type="text" name="print_size" value="<?php echo $print_size; ?>" placeholder="<?php echo $entry_size; ?>" id="input-sort-order" class="form-control" />
            </div>
          </div>
          <div class="form-group">
            <label class="col-sm-2 control-label" for="input-image"><?php echo $entry_preview_image; ?></label>
            <div class="col-sm-10"> <a href="" id="thumb-image" data-toggle="image" class="img-thumbnail"><img src="<?php echo $thumb; ?>" alt="" title="" data-placeholder="<?php echo $placeholder; ?>" weight="200px" height="150px"/></a>
              <input type="hidden" name="preview_image" value="<?php echo $preview_image; ?>" id="input-image" />
            </div>
          </div>
          <div class="form-group">
            <label class="col-sm-2 control-label" for="input-sort-order"><?php echo $entry_preview_size; ?></label>
            <div class="col-sm-4">
              <input type="text" name="preview_size" value="<?php echo $preview_size; ?>" placeholder="<?php echo $entry_preview_size; ?>" id="input-sort-order" class="form-control" />
            </div>
          </div>
          <div class="form-group">
            <label class="col-sm-2 control-label" for="input-sort-order"><?php echo $entry_preview_area; ?></label>
            <div class="col-sm-4">
              <input type="text" name="preview_area" value="<?php echo $preview_area; ?>" placeholder="<?php echo $entry_preview_area; ?>" id="input-sort-order" class="form-control" />
            </div>
          </div>
          <div class="form-group">
            <label class="col-sm-2 control-label" for="input-sort-order"><?php echo $entry_quantity; ?></label>
            <div class="col-sm-4">
              <input type="text" name="print_quantity" value="<?php echo $print_quantity; ?>" placeholder="<?php echo $entry_quantity; ?>" id="input-sort-order" class="form-control" />
            </div>
          </div>

          <div class="form-group">
            <label class="col-sm-2 control-label" for="input-category"><?php echo $entry_relation; ?></label>
            <div class="col-sm-10">
              <input type="text" name="product" value="" placeholder="<?php echo $entry_relation; ?>" id="input-category" class="form-control" />
              <div id="product-category" class="well well-sm" style="height: 150px; overflow: auto;">
                <?php if (@$print_products) {?>
                <?php foreach ($print_products as $print_product) { ?>
                <div id="product-category<?php echo $print_products['product_id']; ?>">
                    <i class="fa fa-minus-circle"></i> <?php echo $print_product['name']; ?>
                  <input type="hidden" name="product_category[]" value="<?php echo $print_product['product_id']; ?>" />
                </div>
                <?php } ?>
                <?php } ?>
              </div>
            </div>
          </div>

          <!-- <div class="form-group">
            <label class="col-sm-2 control-label" for="input-keyword"><span data-toggle="tooltip" title="<?php echo $help_keyword; ?>"><?php echo $entry_keyword; ?></span></label>
            <div class="col-sm-10">
              <input type="text" name="keyword" value="<?php echo $keyword; ?>" placeholder="<?php echo $entry_keyword; ?>" id="input-keyword" class="form-control" />
              <?php if ($error_keyword) { ?>
              <div class="text-danger"><?php echo $error_keyword; ?></div>
              <?php } ?>
            </div>
          </div>
          <div class="form-group">
            <label class="col-sm-2 control-label" for="input-image"><?php echo $entry_image; ?></label>
            <div class="col-sm-10"> <a href="" id="thumb-image" data-toggle="image" class="img-thumbnail"><img src="<?php echo $thumb; ?>" alt="" title="" data-placeholder="<?php echo $placeholder; ?>" /></a>
              <input type="hidden" name="image" value="<?php echo $image; ?>" id="input-image" />
            </div>
          </div>
          <div class="form-group">
            <label class="col-sm-2 control-label" for="input-sort-order"><?php echo $entry_sort_order; ?></label>
            <div class="col-sm-10">
              <input type="text" name="sort_order" value="<?php echo $sort_order; ?>" placeholder="<?php echo $entry_sort_order; ?>" id="input-sort-order" class="form-control" />
            </div>
          </div> -->
        </form>
      </div>
    </div>
  </div>
</div>
 <script type="text/javascript">
 // Category
 $('input[name=\'product\']').autocomplete({
 	'source': function(request, response) {
 		$.ajax({
 			url: 'index.php?route=catalog/print/autocomplete&token=<?php echo $token; ?>&filter_name=' +  encodeURIComponent(request),
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
 		$('input[name=\'category\']').val('');

 		$('#product-category' + item['value']).remove();

 		$('#product-category').append('<div id="product-category' + item['value'] + '"><i class="fa fa-minus-circle"></i> ' + item['label'] + '<input type="hidden" name="product_category[]" value="' + item['value'] + '" /></div>');
 	}
 });

 $('#product-category').delegate('.fa-minus-circle', 'click', function() {
 	$(this).parent().remove();
 });
 </script>
<?php echo $footer; ?>
