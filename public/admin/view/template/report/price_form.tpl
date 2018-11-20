<?php echo $header; ?><?php echo $column_left; ?>

<div id="content">
    <div class="page-header">
        <div class="container-fluid">
            <div class="pull-right">
                <button type="submit" form="form-category" data-toggle="tooltip" title="<?php echo $button_save; ?>" class="btn btn-primary"><i class="fa fa-save"></i></button>
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
                <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form-category" class="form-horizontal">
                    <div class="tab-content">
                        <div class="tab-pane active" id="tab-data">
                            <!--产品名称，自动生成编号和类型-->
                            <div class="form-group">
                                <label class="col-sm-2 control-label" for="input-name">产品名称：</label>
                                <div class="col-sm-4">
                                    <input type="text" name="product_name" value="<?php echo $product_name ?>" placeholder="产品名称：" id="input-name" class="form-control" />
                                    <input type="hidden" name="cost_id" value="<?php echo $cost_id?>" />
                                    <input type="hidden" name="product_id" value="<?php echo $product_id?>" />
                                </div>
                            </div>
                            <!--产品规格-->
                            <div class="form-group">
                                <label class="col-sm-2 control-label" for="input-spec">规格：</label>
                                <div class="col-sm-4">
                                    <input type="text" name="format" value="<?php echo $format?>" placeholder="规格：" id="input-spec" class="form-control" />
                                </div>
                            </div>
                            <!--冲印成本-->
                            <div class="form-group">
                                <label class="col-sm-2 control-label" for="input-pcosts">冲印成本：</label>
                                <div class="col-sm-4">
                                    <input type="text" name="print" value="<?php echo $print?>" placeholder="冲印成本：" id="input-pcosts" class="form-control price" />
                                </div>
                            </div>
                            <!--制造成本-->
                            <div class="form-group">
                                <label class="col-sm-2 control-label" for="input-mcosts">制造成本：</label>
                                <div class="col-sm-4">
                                    <input type="text" name="produce" value="<?php echo $produce ?>" placeholder="制造成本：" id="input-mcosts" class="form-control price" />
                                </div>
                            </div>
                            <!--外包装-->
                            <div class="form-group">
                                <label class="col-sm-2 control-label" for="input-overpack">外包装：</label>
                                <div class="col-sm-4">
                                    <input type="text" name="overpack" value="<?php echo $overpack?>" placeholder="外包装：" id="input-overpack" class="form-control price" />
                                </div>
                            </div>
                            <!--内包装-->
                            <div class="form-group">
                                <label class="col-sm-2 control-label" for="input-innerpack">内包装：</label>
                                <div class="col-sm-4">
                                    <input type="text" name="inner_pack" value="<?php echo $inner_pack?>" placeholder="内包装：" id="input-innerpack" class="form-control price" />
                                </div>
                            </div>
                            <!--封口贴-->
                            <div class="form-group">
                                <label class="col-sm-2 control-label" for="input-seal">封口贴：</label>
                                <div class="col-sm-4">
                                    <input type="text" name="seal_sticker" value="<?php echo $seal_sticker?>" placeholder="封口贴：" id="input-seal" class="form-control price" />
                                </div>
                            </div>
                            <!--配件-->
                            <div class="form-group">
                                <label class="col-sm-2 control-label" for="input-fitting">配件：</label>
                                <div class="col-sm-4">
                                    <input type="text" name="fitting" value="<?php echo $fitting?>" placeholder="配件：" id="input-fitting" class="form-control price" />
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-2 control-label" for="input-fitting">发货地：</label>
                                <div class="col-sm-4">
                                    <select class="form-control" name="type" id="input-type">
                                        <option value="0" <?php if($type == 0){echo 'selected='.'"selected"';}?>>山东</option>
                                        <option value="1" <?php if($type == 1){echo 'selected='.'"selected"';}?>>杭州</option>
                                    </select>
                                </div>
                            </div>
                            <!--合计-->
                            <div class="form-group">
                                <label class="col-sm-2 control-label" for="input-total">合计：</label>
                                <div class="col-sm-4">
                                    <input type="text" name="total" value="<?php echo $total?>" placeholder="合计：" id="input-total" class="form-control" readonly = "true"/>
                                </div>
                            </div>

                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script type="text/javascript"><!--
    $('#input-description2').summernote({
        height: 300
    });
    $('#input-description1').summernote({
        height: 300
    });
    //--></script>
    <script type="text/javascript"><!--
    $('input[name=\'product_name\']').autocomplete({
        'source': function(request, response) {
            $.ajax({
                url: 'index.php?route=report/price/autocomplete&token=<?php echo $token; ?>',//todo
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
            $('input[name=\'product_name\']').val(item['label']);
            $('input[name=\'product_id\']').val(item['value']);
        }
    });
    //--></script>
    <script type="text/javascript"><!--
    function total(){
        var total = 0;
        $('.price').each(function(index, domEle){
            var $dom = $(domEle);
            total += parseFloat(isNaN($dom.val()) || $dom.val() == '' ? 0 : $dom.val());
        });
        $('input[name=\'total\']').val(total);
    }
    $('.price').keyup(function(){
        total();
    }).on('blur',function(){
        total();
    });
    //--></script>
    <script type="text/javascript"><!--
    $('#language a:first').tab('show');
    //--></script></div>


<?php echo $footer; ?>
