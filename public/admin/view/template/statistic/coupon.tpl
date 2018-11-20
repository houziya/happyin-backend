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
                    <label class="control-label" for="input-date-added">创建日期</label>
                    <div class="input-group date">
                      <input type="text" name="filter_start_date" value="<?php echo $date;?>" placeholder="创建日期" data-date-format="YYYY-MM-DD" id="input-date-added" class="form-control" />
                      <span class="input-group-btn">
                      <button type="button" class="btn btn-default"><i class="fa fa-calendar"></i></button>
                      </span></div>
                 </div>
            </div>
            <div class="col-sm-4">
                <div class="form-group">
                    <label class="control-label" for="input-customer">渠道名称</label>
                    <input type="text" name="filter_channel_name" value="<?php echo $channel_name; ?>" placeholder="要搜索的渠道名称" id="input-customer" class="form-control" />
                 </div>
            </div>
            <div class="col-sm-4">
                <div class="form-group">
                    <label class="control-label" for="input-customer">优惠券名称</label>
                    <input type="text" name="filter_coupon_name" value="<?php echo $coupon_name;?>" placeholder="要检索的优惠券名称" id="input-customer" class="form-control" />
                </div>
             </div>
             <button type="submit" form="form-order" id="button-filter" class="btn btn-primary pull-right"><i class="fa fa-search"></i>筛选</button>
          </div>
        </div>                    
        
          <div class="table-responsive">
            <table class="table table-bordered table-hover">
              <thead>
                <tr>
                  <td class="text-left">
                    <a href="<?php echo $sort_date; ?>">创建日期</a>
                  </td>
                  <td class="text-left">
                    <a href="<?php echo $sort_channel; ?>">渠道名称</a>
                  </td>    
                  <td class="text-left">
                    <a href="<?php echo $sort_name; ?>">优惠劵名称</a>
                  </td> 
                  <td class="text-left">
                    <a ">优惠劵数量</a>
                  </td> 
                  <td class="text-left">
                    <a >总领取</a>
                  </td> 
                  <td class="text-left">
                    <a >当天已领取</a>
                  </td> 
                  <td class="text-left">
                    <a>已使用</a>
                  </td> 
                  <td class="text-left">
                    <a>免费使用</a>
                  </td>
                  <td class="text-left">
                    <a >付费使用</a>
                  </td>
                  <td class="text-left">
                    <a>总成本</a>
                  </td>
                  <td class="text-left">
                    <a>总销售额</a>
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
                    <?php echo $statistic['channel']; ?>
                  </td>
                  <td class="text-left">
                    <?php echo $statistic['name']; ?>
                  </td>
                  <td class="text-left">
                    <?php echo empty($statistic['nums']) ? '+∞' : $statistic['nums']; ?>
                  </td>
                  <td class="text-left">
                    <?php echo $statistic['receive']; ?>
                  </td>
                  <td class="text-left">
                    <?php echo $statistic['currentDay_receive']; ?>
                  </td>
                  <td class="text-left">
                    <?php echo $statistic['used']; ?>
                  </td>
                  <td class="text-left">
                    <?php echo $statistic['free']; ?>
                  </td>
                  <td class="text-left">
                    <?php echo $statistic['pay']; ?>
                  </td>
                  <td class="text-left">
                    <?php echo $statistic['costLogToal']; ?>
                  </td>
                  <td class="text-left">
                    <?php echo  $statistic['cost']; ?>
                  </td>
                </tr>
                <?php } ?>
              <?php }else {?>
              <tr>
                  <td class="text-center" colspan="10"><?php echo $text_no_results; ?></td>
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
  <script src="view/javascript/jquery/datetimepicker/bootstrap-datetimepicker.min.js" type="text/javascript"></script>
  <link href="view/javascript/jquery/datetimepicker/bootstrap-datetimepicker.min.css" type="text/css" rel="stylesheet" media="screen" />
  <script type="text/javascript">
$('.date').datetimepicker({
    pickTime: false
});
</script>      
<?php echo $footer; ?>