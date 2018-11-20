<?php echo $header; ?><?php echo $column_left; ?>
<div id="content">
  <div class="page-header">
    <div class="container-fluid">
      <div class="pull-right"><a href="<?php echo $add; ?>" data-toggle="tooltip" title="<?php echo $button_add; ?>" class="btn btn-primary"><i class="fa fa-plus"></i></a>
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
    <?php if ($error_warning) { ?>
    <div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> <?php echo $error_warning; ?>
      <button type="button" class="close" data-dismiss="alert">&times;</button>
    </div>
    <?php } ?>
    <?php if ($success) { ?>C"></i> <?php echo $success; ?>
      <button type="button" class="close" data-dismiss="alert">&times;</button>
    </div>
    <?php } ?>
    <div class="panel panel-default">
      <div class="panel-heading">
        <h3 class="panel-title"><i class="fa fa-list"></i> <?php echo $text_list; ?></h3>
      </div>
      <div class="panel-body">
        <div class="well">
          <div class="row">
            <div class="col-sm-4">
              <div class="form-group">
                <label class="control-label" for="input-name"><?php echo $entry_name; ?></label>
                <input type="text" name="filter_name" value="<?php echo $filter_name; ?>" placeholder="<?php echo $entry_name; ?>" id="input-name" class="form-control" />
              </div>
              <div class="form-group">
                <label class="control-label" for="input-model"><?php echo $entry_model; ?></label>
                <?php if (is_numeric($filter_model)) { 
                	$model = '';
                } else {
                	$model = $filter_model;
                } ?>
                <input type="text" name="filter_model" value="<?php echo $model; ?>" placeholder="<?php echo $entry_model; ?>" id="input-model" class="form-control" />
                <input type="hidden" name="filter_model_id" value="<?php ?>" placeholder="<?php echo $entry_model; ?>" id="input-model" class="form-control" />
              </div>
            </div>
            <div class="col-sm-4">
              <div class="form-group">
                <label class="control-label" for="input-price"><?php echo $entry_price; ?></label>
                <input type="text" name="filter_price" value="<?php echo $filter_price; ?>" placeholder="<?php echo $entry_price; ?>" id="input-price" class="form-control" />
              </div>
            </div>
            <div class="col-sm-4">
              <div class="form-group">
                <label class="control-label" for="input-status"><?php echo $entry_status; ?></label>
                <select name="filter_status" id="input-status" class="form-control">
                  <option value="*"></option>
                  <?php if ($filter_status) { ?>
                  <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                  <?php } else { ?>
                  <option value="1"><?php echo $text_enabled; ?></option>
                  <?php } ?>
                  <?php if (!$filter_status && !is_null($filter_status)) { ?>
                  <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                  <?php } else { ?>
                  <option value="0"><?php echo $text_disabled; ?></option>
                  <?php } ?>
                </select>
              </div>
              <button type="button" id="button-filter" class="btn btn-primary pull-right"><i class="fa fa-search"></i> <?php echo $button_filter; ?></button>
            </div>
          </div>
        </div>
        <form action="<?php echo $delete; ?>" method="post" enctype="multipart/form-data" id="form-product">
          <div class="table-responsive">
            <table class="table table-bordered table-hover">
              <thead>
                <tr>
                  <td style="width: 1px;" class="text-center"><input type="checkbox" onclick="$('input[name*=\'selected\']').prop('checked', this.checked);" /></td>
                  <td class="text-center"><?php echo $column_numbering; ?></td>
                  <td class="text-center"><?php echo $column_name; ?></td>
                  <td class="text-center"><?php echo $column_spec; ?></td>
                  <td class="text-right"><?php if ($sort == 'p.price') { ?>
                    <a href="<?php echo $sort_price; ?>" class="<?php echo strtolower($order); ?>"><?php echo $column_price; ?></a>
                    <?php } else { ?>
                    <a href="<?php echo $sort_price; ?>"><?php echo $column_price; ?></a>
                    <?php } ?></td>
                  <td class="text-right">
                      <?php if ($sort == 'p.quantity') { ?>
                            <a href="<?php echo $sort_quantity; ?>" class="<?php echo strtolower($order); ?>">
                            <?php echo $column_quantity; ?></a>
                        <?php } else { ?>
                            <a href="<?php echo $sort_quantity; ?>"><?php echo $column_quantity; ?></a>
                        <?php } ?>
                    </td>
                  <td class="text-left"><?php if ($sort == 'p.status') { ?>
                    <a href="<?php echo $sort_status; ?>" class="<?php echo strtolower($order); ?>"><?php echo $column_status; ?></a>
                    <?php } else { ?>
                    <a href="<?php echo $sort_status; ?>"><?php echo $column_status; ?></a>
                    <?php } ?></td>
                  <td class="text-right"><?php echo $column_action; ?></td>
                </tr>
              </thead>
              <tbody>
                <?php if ($parts) { ?>
                <?php foreach ($parts as $par) { ?>
                <tr>
                  <td class="text-center"><?php if (in_array($par['parts_id'], $selected)) { ?>
                    <input type="checkbox" name="selected[]" value="<?php echo $par['parts_id']; ?>" checked="checked" />
                    <?php } else { ?>
                    <input type="checkbox" name="selected[]" value="<?php echo $par['parts_id']; ?>" />
                    <?php } ?></td>
                  <td class="text-left"><?php echo $par['numbering']; ?></td>
                  <td class="text-left"><?php echo $par['name']; ?></td>
                  <td class="text-left"><?php echo $par['spec']; ?></td>
                  <td class="text-left"><?php echo $par['price']; ?></td>
                  <td class="text-right"><?php if ($par['quantity'] <= 0) { ?>
                    <span class="label label-warning"><?php echo $par['quantity']; ?></span>
                    <?php } elseif ($par['quantity'] <= $stockWarning) { ?>
                     <span class="label label-success">库存紧张 </span><span class="label label-danger"> <?php echo $par['quantity']; ?></span>
                    <?php } else { ?>
                    <span class="label label-success"><?php echo $par['quantity']; ?></span>
                    <?php } ?></td>
                  <td class="text-left"><?php echo $par['status']; ?></td>
                  <td class="text-right"><a href="<?php echo $par['edit']; ?>" data-toggle="tooltip" title="<?php echo $button_edit; ?>" class="btn btn-primary"><i class="fa fa-pencil"></i></a>
                      <class="text-right"><a href="<?php echo $par['delete']; ?>" data-toggle="tooltip" title="<?php echo $button_delete; ?>" class="btn btn-danger"><i class="fa fa-trash-o"></i></a></td>
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
          <div class="col-sm-6 text-left"><?php echo $pagination; ?></div>
          <div class="col-sm-6 text-right"><?php echo $results; ?></div>
        </div>
      </div>
    </div>
  </div>
  <script type="text/javascript"><!--
$('#button-filter').on('click', function() {
	var url = 'index.php?route=parts/parts&token=<?php echo $token; ?>';

	var filter_name = $('input[name=\'filter_name\']').val();

	if (filter_name) {
		url += '&filter_name=' + encodeURIComponent(filter_name);
	}

	var filter_model = $('input[name=\'filter_model_id\']').val();
	
	if (filter_model) {
		url += '&filter_model=' + encodeURIComponent(filter_model);
	}
	
	var filter_price = $('input[name=\'filter_price\']').val();

	if (filter_price) {
		url += '&filter_price=' + encodeURIComponent(filter_price);
	}

	var filter_status = $('select[name=\'filter_status\']').val();

	if (filter_status != '*') {
		url += '&filter_status=' + encodeURIComponent(filter_status);
	}

	location = url;
});
//--></script>
  <script type="text/javascript"><!--
$('input[name=\'filter_model\']').autocomplete({
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
		$('input[name=\'filter_model\']').val(item['label']);
		$('input[name=\'filter_model_id\']').val(item['value']);
	}
});

//--></script></div>
<?php echo $footer; ?>