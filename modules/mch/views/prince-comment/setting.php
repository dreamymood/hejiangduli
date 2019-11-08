<?php
defined('YII_ENV') or exit('Access Denied');

$urlManager = Yii::$app->urlManager;
$this->title = '采集设置'; 
$this->params['active_nav_group'] = 8; 

use yii\widgets\ActiveForm;
use \app\models\Option;
?> 
<!-- 
<link href="<?= Yii::$app->request->baseUrl ?>/statics/mch/css/bootstrap-combined.min.css" rel="stylesheet">
<link href="<?= Yii::$app->request->baseUrl ?>/statics/mch/css/bootstrap-datetimepicker.min.css" rel="stylesheet">
<script src="<?= Yii::$app->request->baseUrl ?>/statics/mch/js/bootstrap-datetimepicker.js"></script>     -->

<div class="panel mb-3">
    <div class="panel-header">
        <span><?= $this->title ?></span>
    </div>
    <div class="panel-body">
        <form class="form auto-form" method="post" return="<?= $urlManager->createUrl(['mch/prince-comment/setting']) ?>">

        <div class="form-group row">
            <div class="form-group-label col-sm-2 text-right">
                <label class="col-form-label ">默认采集关键词</label>
            </div>
            <div class="col-sm-6">
                <div class="input-group">
               	<input class="form-control" name="need_key" value="<?= isset($model['need_key']) ? $model['need_key']: '' ?>">
                </div>
				<div class="text-muted fs-sm">仅采集包含此关键词的评论，例如：好,快,赞。多个请使用英文逗号<kbd>,</kbd>分隔，最多3个</div>
            </div>
        </div> 
       
         <div class="form-group row">
            <div class="form-group-label col-sm-2 text-right">
                <label class="col-form-label ">默认过滤关键词</label>
            </div>
            <div class="col-sm-6">
                <div class="input-group">
               	<input class="form-control" name="remove_key" value="<?= isset($model['remove_key']) ? $model['remove_key']: '' ?>">
                </div>
				<div class="text-muted fs-sm">不采集包含此关键词的评论，例如：差,慢,差评。多个请使用英文逗号<kbd>,</kbd>分隔，最多3个</div>
            </div>
        </div>      

    <div class="form-group row">
        <div class="form-group-label col-sm-2 text-right">
            <label class="col-form-label required">默认采集客户晒图</label>
        </div>
        <div class="col-sm-6">

            <label class="radio-label">
                <input <?= !isset($model['get_pics']) || $model['get_pics']== 1 ? 'checked' : null ?>
                value="1"
                name="get_pics" type="radio" class="custom-control-input">
                <span class="label-icon"></span>
                <span class="label-text">是</span>
            </label>
            <label class="radio-label">
                <input <?= isset($model['get_pics']) && $model['get_pics']== 0 ? 'checked' : null ?>
                value="0"
                name="get_pics" type="radio" class="custom-control-input">
                <span class="label-icon"></span>
                <span class="label-text">否</span>
            </label>
        </div>
    </div>
    
    <div class="form-group row">
        <div class="form-group-label col-sm-2 text-right">
            <label class="col-form-label required">默认采集店主回复</label>
        </div>
        <div class="col-sm-6">

            <label class="radio-label">
                <input <?= !isset($model['get_reply']) || $model['get_reply']== 1 ? 'checked' : null ?>
                value="1"
                name="get_reply" type="radio" class="custom-control-input">
                <span class="label-icon"></span>
                <span class="label-text">是</span>
            </label>
            <label class="radio-label">
                <input <?= isset($model['get_reply']) && $model['get_reply']== 0 ? 'checked' : null ?>
                value="0"
                name="get_reply" type="radio" class="custom-control-input">
                <span class="label-icon"></span>
                <span class="label-text">否</span>
            </label>
        </div>
    </div>
    
    <div class="form-group row">
        <div class="form-group-label col-sm-2 text-right">
            <label class="col-form-label required">默认过滤重复评论</label>
        </div>
        <div class="col-sm-6">

            <label class="radio-label">
                <input <?= !isset($model['no_repeat']) || $model['no_repeat']== 1 ? 'checked' : null ?>
                value="1"
                name="no_repeat" type="radio" class="custom-control-input">
                <span class="label-icon"></span>
                <span class="label-text">是</span>
            </label>
            <label class="radio-label">
                <input <?= isset($model['no_repeat']) && $model['no_repeat']== 0 ? 'checked' : null ?>
                value="0"
                name="no_repeat" type="radio" class="custom-control-input">
                <span class="label-icon"></span>
                <span class="label-text">否</span>
            </label>
        </div>
    </div>

    <div class="form-group row">
        <div class="form-group-label col-sm-2 text-right">
            <label class="col-form-label required">默认使用淘宝评论时间</label>
        </div>
        <div class="col-sm-6">

            <label class="radio-label">
                <input  <?= !isset($model['time_type']) || $model['time_type']== 1 ? 'checked' : null ?>
                value="1"
                name="time_type" type="radio" class="custom-control-input">
                <span class="label-icon"></span>
                <span class="label-text">是</span>
            </label>
            <label class="radio-label">
                <input <?= isset($model['time_type']) && $model['time_type']== 0 ? 'checked' : null ?>
                value="0"
                name="time_type" type="radio" class="custom-control-input">
                <span class="label-icon"></span>
                <span class="label-text">否</span>
            </label>
            <div class="text-muted fs-sm ">选择否，则使用系统时间作为评论时间</div>
        </div>
    </div> 
    
    <div class="form-group row">
        <div class="form-group-label col-sm-2 text-right">
            <label class="col-form-label required">启用替换规则</label>
        </div>
        <div class="col-sm-6">

            <label class="radio-label">
                <input  <?= !isset($model['use_rule']) || $model['use_rule']== 1 ? 'checked' : null ?>
                value="1"
                name="use_rule" type="radio" class="custom-control-input">
                <span class="label-icon"></span>
                <span class="label-text">是</span>
            </label>
            <label class="radio-label">
                <input <?= isset($model['use_rule']) && $model['use_rule']== 0 ? 'checked' : null ?>
                value="0"
                name="use_rule" type="radio" class="custom-control-input">
                <span class="label-icon"></span>
                <span class="label-text">否</span>
            </label>
        </div>
    </div> 

    <div class="form-group row">
        <div class="form-group-label col-sm-2 text-right">
            <label class="col-form-label required">评论用户</label>
        </div>
        <div class="col-sm-6">

            <label class="radio-label">
                <input <?= isset($model['user_type']) && $model['user_type']== 1 ? 'checked' : null ?>
                value="1"
                name="user_type" type="radio" class="custom-control-input">
                <span class="label-icon"></span>
                <span class="label-text">使用淘宝用户</span>
            </label>
            <label class="radio-label">
                <input <?= !isset($model['user_type']) || $model['user_type']== 2 ? 'checked' : null ?>
                value="2"
                name="user_type" type="radio" class="custom-control-input">
                <span class="label-icon"></span>
                <span class="label-text">使用云端虚拟用户</span>
            </label>
            <label class="radio-label">
                <input <?= isset($model['user_type']) && $model['user_type']== 3 ? 'checked' : null ?>
                value="3"
                name="user_type" type="radio" class="custom-control-input">
                <span class="label-icon"></span>
                <span class="label-text">使用系统虚拟用户</span>
            </label>
            <div class="text-muted fs-sm ">若使用系统虚拟用户，则需要先添加用户到虚拟用户库</div>
        </div>
    </div> 

    <div class="form-group row">
        <div class="form-group-label col-sm-2 text-right">
        </div>
        <div class="col-sm-6">
            <a class="btn btn-primary auto-form-btn" href="javascript:">保存</a>
        </div>
    </div>
</form>


</div>
</div>
