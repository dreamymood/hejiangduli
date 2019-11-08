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
                <label class="col-form-label ">价格倍率</label>
            </div>
            <div class="col-sm-6">
                <div class="input-group">
               	<input class="form-control" name="price_rate" value="<?= isset($model['price_rate']) ? $model['price_rate']: '1.00' ?>">
                </div>
				<div class="text-muted fs-sm">例如：填1代表售价与云库售价一致，填1.5代表本店售价是云库售价的1.5倍。</div>
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
