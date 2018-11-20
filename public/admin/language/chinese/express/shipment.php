<?php
// Heading
$_['heading_title']    = '运费管理';

// Text
$_['text_success']     = '成功： 您已成功更新运费管理库！';
$_['text_list']        = '运费管理';
$_['text_edit']        = '编辑运费';
$_['text_value']       = '运费';
$_['text_add']         = '添加运费管理';

// Column
$_['column_code']      = '编码';
$_['column_name']    = '发货地';
$_['column_cost']     = '邮费';
$_['column_action']    = '管理';
$_['column_province'] = '收货地';

// Entry
$_['entry_name']       = '省份';
$_['entry_code']       = '编码';
$_['entry_cost']       = '新运费：';
$_['entry_momeny']     = '元';
for ($i = 1; $i <= 3232; $i++) {
    $city[$i] = Yii::$app->redis->hget(HI\User\CITY_CODE, $i);
}
$_['entry_province'] = $city;
// Error
$_['error_permission'] = '警告： 您没有权限 操作运费管理模块！';
$_['error_name']       = '警告 ： 添加运费管理出现异常, 稍后重试';
$_['error_product']    = '警告： 该库存状态不能被删除，因为它被绑定到 %s 商品！';
$_['error_address']       = '警告 ： 重复地址管理，再核对一下！';
$_['error_empty']       = '警告 ： 运费管理数据不能为空！或运费地址已存在';
