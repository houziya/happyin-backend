<?php echo $header; ?><?php echo $column_left; ?>

<div id="content">
  <div class="page-header">
    <div class="container-fluid">
      <div class="pull-right">
          <a href="<?php echo $add; ?>" data-toggle="tooltip" title="<?php echo $button_add; ?>" class="btn btn-primary"><i class="fa fa-plus"></i></a>
        <button type="button" data-toggle="tooltip" title="<?php echo $button_delete; ?>" class="btn btn-danger" onclick="confirm('<?php echo $text_confirm; ?>') ? $('#form-product').submit() : false;"><i class="fa fa-trash-o"></i></button>
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
    <?php if ($success) { ?>
    <div class="alert alert-success"><i class="fa fa-check-circle"></i> <?php echo $success; ?>
      <button type="button" class="close" data-dismiss="alert">&times;</button>
    </div>
    <?php } ?>
    <div class="panel panel-default">
      <div class="panel-heading">
        <h3 class="panel-title"><i class="fa fa-list"></i> <?php echo $text_list; ?></h3>
      </div>
      <div class="panel-body">
          <div class="table-responsive">
            <table class="table table-bordered table-hover">
              <thead>
                  <tr>
                      <td class="text-center" colspan="12">山东</td>
                  </tr>
              </thead>
              <tbody>
                <tr>
                  <td class="text-center"><?php echo $column_model; ?></td>
                  <td class="text-center"><?php echo $column_name; ?></td>
                  <td class="text-center"><?php echo $column_code; ?></td>
                  <td class="text-center"><?php echo $column_standard; ?></td>
                  <td class="text-center"><?php echo $column_print; ?></td>
                  <td class="text-center"><?php echo $column_produce; ?></td>
                  <td class="text-center"><?php echo $column_overpack; ?></td>
                  <td class="text-center"><?php echo $column_inner_pack; ?></td>
                  <td class="text-center"><?php echo $column_seal_sticker; ?></td>
                  <td class="text-center"><?php echo $column_fitting; ?></td>
                  <td class="text-center"><?php echo $column_total; ?></td>
                  <td class="text-center"><?php echo $column_action; ?></td>
                </tr>
                <?php if ($products_sd) { ?>
                <?php foreach ($products_sd as $product_sd) { ?>

                <tr>
                  <td class="text-center"><?php echo $product_sd['name']; ?></td>
                  <td class="text-center"><?php echo $product_sd['model']; ?></td>
                  <td class="text-center"><?php echo $product_sd['mpn']; ?></td>
                  <td class="text-center"><?php echo $product_sd['format']?></td>
                  <td class="text-center"><?php echo $product_sd['print']; ?></td>
                  <td class="text-center"><?php echo $product_sd['produce']; ?></td>
                  <td class="text-center"><?php echo $product_sd['overpack']; ?></td>
                  <td class="text-center"><?php echo $product_sd['inner_pack']; ?></td>
                  <td class="text-center"><?php echo $product_sd['seal_sticker']; ?></td>
                  <td class="text-center"><?php echo $product_sd['fitting']; ?></td>
                  <td class="text-center"><?php echo $product_sd['total']; ?></td>
                  <td class="text-center">
                      <a href="<?php echo $product_sd['edit']; ?>" data-toggle="tooltip" title="<?php echo $button_edit; ?>" class="btn btn-primary">
                          <i class="fa fa-pencil"></i>
                      </a>
                      <a href="<?php echo $product_sd['del']; ?>" data-toggle="tooltip" title="<?php echo $button_delete; ?>" class="btn btn-danger">
                          <i class="fa fa-trash-o"></i>
                      </a>
                  </td>
                </tr>
                <?php } ?>
                <?php } else { ?>
                <tr>
                  <td class="text-center" colspan="12"><?php echo $text_no_results; ?></td>
                </tr>
                <?php } ?>
              </tbody>
            </table>
          </div>

      </div>

      <div class="panel-body">
          <div class="table-responsive">
            <table class="table table-bordered table-hover">
              <thead>
                  <tr>
                      <td class="text-center" colspan="12">杭州</td>
                  </tr>
              </thead>
              <tbody>
                <tr>
                  <td class="text-center"><?php echo $column_model; ?></td>
                  <td class="text-center"><?php echo $column_name; ?></td>
                  <td class="text-center"><?php echo $column_code; ?></td>
                  <td class="text-center"><?php echo $column_standard; ?></td>
                  <td class="text-center"><?php echo $column_print; ?></td>
                  <td class="text-center"><?php echo $column_produce; ?></td>
                  <td class="text-center"><?php echo $column_overpack; ?></td>
                  <td class="text-center"><?php echo $column_inner_pack; ?></td>
                  <td class="text-center"><?php echo $column_seal_sticker; ?></td>
                  <td class="text-center"><?php echo $column_fitting; ?></td>
                  <td class="text-center"><?php echo $column_total; ?></td>
                  <td class="text-center"><?php echo $column_action; ?></td>
                </tr>
                <?php if ($products_zj) { ?>
                <?php foreach ($products_zj as $product_zj) { ?>
                <tr>
                  <td class="text-center"><?php echo $product_zj['name']; ?></td>
                  <td class="text-center"><?php echo $product_zj['model']; ?></td>
                  <td class="text-center"><?php echo $product_zj['mpn']; ?></td>
                  <td class="text-center"><?php echo $product_sd['format']?></td>
                  <td class="text-center"><?php echo $product_zj['print']; ?></td>
                  <td class="text-center"><?php echo $product_zj['produce']; ?></td>
                  <td class="text-center"><?php echo $product_zj['overpack']; ?></td>
                  <td class="text-center"><?php echo $product_zj['inner_pack']; ?></td>
                  <td class="text-center"><?php echo $product_zj['seal_sticker']; ?></td>
                  <td class="text-center"><?php echo $product_zj['fitting']; ?></td>
                  <td class="text-center"><?php echo $product_zj['total']; ?></td>
                  <td class="text-center">
                      <a href="<?php echo $product_zj['edit']; ?>" data-toggle="tooltip" title="<?php echo $button_edit; ?>" class="btn btn-primary">
                          <i class="fa fa-pencil"></i>
                      </a>
                      <a href="<?php echo $product_zj['del']; ?>" data-toggle="tooltip" title="<?php echo $button_delete; ?>" class="btn btn-danger">
                          <i class="fa fa-trash-o"></i>
                      </a>
                  </td>
                </tr>
                <?php } ?>
                <?php } else { ?>
                <tr>
                  <td class="text-center" colspan="12"><?php echo $text_no_results; ?></td>
                </tr>
                <?php } ?>
              </tbody>
            </table>
          </div>
      </div>
    </div>
  </div>
  <script type="text/javascript"><!--
$('#button-filter').on('click', function() {
    var url = 'index.php?route=catalog/product&token=<?php echo $token; ?>';

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
  <script type="text/javascript"><!--
$('input[name=\'filter_name\']').autocomplete({
    'source': function(request, response) {
        $.ajax({
            url: 'index.php?route=catalog/product/autocomplete&token=<?php echo $token; ?>&filter_name=' +  encodeURIComponent(request),
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
            url: 'index.php?route=catalog/product/autocomplete&token=<?php echo $token; ?>&filter_model=' +  encodeURIComponent(request),
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
