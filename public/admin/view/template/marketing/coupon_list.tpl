<?php echo $header; ?><?php echo $column_left; ?>
<div id="content">
  <div class="page-header">
    <div class="container-fluid">
      <div class="pull-right"><a href="<?php echo $add; ?>" data-toggle="tooltip" title="<?php echo $button_add; ?>" class="btn btn-primary"><i class="fa fa-plus"></i></a>
        <button type="button" data-toggle="tooltip" title="<?php echo $button_delete; ?>" class="btn btn-danger" onclick="confirm('<?php echo $text_confirm; ?>') ? $('#form-coupon').submit() : false;"><i class="fa fa-trash-o"></i></button>
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
        <form action="<?php echo $delete; ?>" method="post" enctype="multipart/form-data" id="form-coupon">
          <div class="table-responsive">
            <table class="table table-bordered table-hover">
              <thead>
                <tr>
                  <td style="width: 1px;" class="text-center"><input type="checkbox" onclick="$('input[name*=\'selected\']').prop('checked', this.checked);" /></td>
                  <td class="text-left"><?php if ($sort == 'cd.name') { ?>
                    <a href="<?php echo $sort_name; ?>" class="<?php echo strtolower($order); ?>"><?php echo $column_name; ?></a>
                    <?php } else { ?>
                    <a href="<?php echo $sort_name; ?>"><?php echo $column_name; ?></a>
                    <?php } ?></td>
                  <td class="text-left"><?php if ($sort == 'cd.logged') { ?>
                    <a href="<?php echo $sort_scenes; ?>" class="<?php echo strtolower($order); ?>"><?php echo $column_scenes; ?></a>
                    <?php } else { ?>
                    <a href="<?php echo $sort_scenes; ?>"><?php echo $column_scenes; ?></a>
                    <?php } ?></td>
                  <td class="text-left"><?php if ($sort == 'cd.channel') { ?>
                    <a href="<?php echo $sort_channel; ?>" class="<?php echo strtolower($order); ?>"><?php echo $column_channel; ?></a>
                    <?php } else { ?>
                    <a href="<?php echo $sort_channel; ?>"><?php echo $column_channel; ?></a>
                    <?php } ?></td>
                  <td class="text-left"><?php if ($sort == 'c.code') { ?>
                    <a href="<?php echo $sort_code; ?>" class="<?php echo strtolower($order); ?>"><?php echo $column_code; ?></a>
                    <?php } else { ?>
                    <a href="<?php echo $sort_code; ?>"><?php echo $column_code; ?></a>
                    <?php } ?></td>
                  <td class="text-right"><?php if ($sort == 'c.discount') { ?>
                    <a href="<?php echo $sort_discount; ?>" class="<?php echo strtolower($order); ?>"><?php echo $column_discount; ?></a>
                    <?php } else { ?>
                    <a href="<?php echo $sort_discount; ?>"><?php echo $column_discount; ?></a>
                    <?php } ?></td>
                  <td class="text-left"><?php if ($sort == 'c.date_start') { ?>
                    <a href="<?php echo $sort_date_start; ?>" class="<?php echo strtolower($order); ?>"><?php echo $column_date_start; ?></a>
                    <?php } else { ?>
                    <a href="<?php echo $sort_date_start; ?>"><?php echo $column_date_start; ?></a>
                    <?php } ?></td>
                  <td class="text-left"><?php if ($sort == 'c.date_end') { ?>
                    <a href="<?php echo $sort_date_end; ?>" class="<?php echo strtolower($order); ?>"><?php echo $column_date_end; ?></a>
                    <?php } else { ?>
                    <a href="<?php echo $sort_date_end; ?>"><?php echo $column_date_end; ?></a>
                    <?php } ?></td>
                  <td class="text-left"><?php if ($sort == 'c.validity') { ?>
                    <a href="<?php echo $sort_validity; ?>" class="<?php echo strtolower($order); ?>"><?php echo $entry_validity; ?></a>
                    <?php } else { ?>
                    <a href="<?php echo $sort_validity; ?>"><?php echo $entry_validity; ?></a>
                    <?php } ?></td>
                  <td class="text-left"><?php if ($sort == 'c.status') { ?>
                    <a href="<?php echo $sort_status; ?>" class="<?php echo strtolower($order); ?>"><?php echo $column_status; ?></a>
                    <?php } else { ?>
                    <a href="<?php echo $sort_status; ?>"><?php echo $column_status; ?></a>
                    <?php } ?></td>
                  <td class="text-left">
                    <a> <?php echo $column_count; ?> </a>
                  </td>
                  <td class="text-left">
                    <a> <?php echo $entry_city_code; ?> </a>
                  </td>
                  <td class="text-left">
                    <a><?php echo $entry_reviewer; ?> </a>
                  </td>
                  <td class="text-right"><?php echo $column_action; ?></td>
                </tr>
              </thead>
              <tbody>
                <?php if ($coupons) { ?>
                <?php foreach ($coupons as $coupon) { ?>
                <?php  if($coupon['logged'] == 2) {?>
                     <tr style="background-color:#f5f5dc">
                <?php } elseif($coupon['logged'] == 1) {?>
                      <tr style="background-color:#f5f5ac">
                <?php } elseif($coupon['logged'] == 4) {?>
                      <tr style="background-color:#f5e5ac">
                <?php } else {?>
                      <tr>
                <?php }?>
                  <td class="text-center"><?php if (in_array($coupon['coupon_id'], $selected)) { ?>
                    <input type="checkbox" name="selected[]" value="<?php echo $coupon['coupon_id']; ?>" checked="checked" />
                    <?php } else { ?>
                    <input type="checkbox" name="selected[]" value="<?php echo $coupon['coupon_id']; ?>" />
                    <?php } ?></td>
                  <td class="text-left"><?php echo $coupon['name']; ?></td>
                  <td class="text-left">
                        <?php 
                            if ($coupon['logged'] == 1) {
                                echo $text_returns;
                            } elseif ($coupon['logged'] == 2) {
                                echo $text_share;
                            } elseif ($coupon['logged'] == 4) {
                                echo $entry_package_coupon;
                            } else {
                                echo $text_normal;
                            }
                         ?>
                  </td>
                  <td class="text-left"><?php echo $coupon['channel']; ?></td>
                  <td class="text-left"><?php echo $coupon['code']; ?></td>
                  <td class="text-right">
                    <?php if($coupon['property'] == 1) {
                        echo $coupon['discount'].' '.$help_number;
                    } elseif ($coupon['property'] == 2) {
                        echo $coupon['discount'].' '.$help_sheets;
                    } else {
                        if($coupon['type'] == 'F') {
                            echo $coupon['discount'].' '.$help_momeny;
                        } else {
                            echo $coupon['discount'].' '.$help_percent;
                        }
                    }
                    ?>
                  </td>
                  <td class="text-left"><?php echo $coupon['date_start']; ?></td>
                  <td class="text-left"><?php echo $coupon['date_end']; ?></td>
                  <td class="text-left">
                  <?php 
                  if ($coupon['use_type'] == 1) {
                    echo $coupon['validity'].' '.$help_days; 
                  } else {
                    echo $coupon['use_start'].' / '.$coupon['use_end'];
                  }
                  ?>
                  </td>
                  <?php
                    if($coupon['status'] == '启用') {
                  ?>
                  <td class="text-left"><?php echo $entry_enable; ?></td>
                  <?php
                    } else {
                  ?>
                  <td class="text-left"><a class="label label-pill label-warning"><?php echo $entry_disabled; ?></a></td>
                  <?php
                    }
                  ?>
                  <?php
                    if ($coupon['default_count'] == $coupon['count']) {
                  ?>
                  <td class="text-left"><a class="label label-success"><?php echo (!$coupon['default_count'] ? '+∞': $coupon['default_count']).' / '.$coupon['count']; ?></a></td>
                  <?php
                    } else {
                  ?>
                  <td class="text-left"><a class="label label-info"><?php echo (!$coupon['default_count'] ? '+∞': $coupon['default_count']).' / '.$coupon['count']; ?></a></td>
                  <?php
                    }
                  ?>
                  <td class="text-left">
                        <?php if($coupon['city_code'] == 2) {
                            echo $help_city_none;
                        } elseif ($coupon['city_code'] == 1) {
                            echo $help_city_hangzhou;
                        } else {
                            echo $help_city_shandong;
                        }?>
                  </td>
                  <td class="text-left">
                        <?php
                            if (empty($coupon['uses_customer'])) {
                                echo $entry_not_pass;
                            } else {
                                echo $coupon['username'];
                            }
                        ?>
                  </td>
                  <td class="text-right"><a href="<?php echo $coupon['edit']; ?>" data-toggle="tooltip" title="<?php echo $button_edit; ?>" class="btn btn-primary"><i class="fa fa-pencil"></i></a></td>
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
</div>
<?php echo $footer; ?>