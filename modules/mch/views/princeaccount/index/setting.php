<?php
defined('YII_ENV') or exit('Access Denied');

$urlManager = Yii::$app->urlManager;
$this->title = '设置'; 
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
        <form class="form auto-form" method="post" >

        <div class="form-group row">
            <div class="form-group-label col-sm-2 text-right">
                <label class="col-form-label ">提现手续费率</label>
            </div>
            <div class="col-sm-6">
                <div class="input-group">
               	<input class="form-control" name="apply_rate" value="<?= isset($model['apply_rate']) ? $model['apply_rate']: '0' ?>">
                <span class="input-group-addon">%</span>
                </div>
                        <div class="text-muted fs-sm">0表示不设置提现手续费</div>
                        <div class="text-muted fs-sm">
                            <span class="text-danger">提现手续费额外从提现中扣除</span><br>
                            例如：<span class="text-danger">0.6%</span>的提现手续费：<br>
                            提现<span class="text-danger">100</span>元，扣除手续费<span class="text-danger">0.6</span>元，
                            实际到手<span class="text-danger">99.4</span>元
                        </div>
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
