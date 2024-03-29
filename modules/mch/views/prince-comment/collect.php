<?php
defined('YII_ENV') or exit('Access Denied');

$urlManager = Yii::$app->urlManager;
$this->title = '采集评论'; 
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
        <ul class="nav nav-right">
            <li class="nav-item">
                <a class="nav-link" href="<?= $urlManager->createUrl(['mch/comment/index']) ?>">查看评论</a>
            </li>
        </ul>
    </div>
    <div class="panel-body">
        <form class="form auto-form" method="post" return="<?= $urlManager->createUrl(['mch/prince-comment/collect']) ?>">
            <div class="form-group row" style="<?php if(!$model['id']){echo 'display:none';}else{echo 'display:display'; } ?>"" >
                <div class="form-group-label col-sm-2 text-right">
                    <label class="col-form-label">ID</label>
                </div>
                <div class="col-sm-6">
                 <div class="col-form-label required"><?= $model['id'] ?></div>
             </div>
        </div> 


 
        <div class="form-group row">
            <div class="form-group-label col-sm-2 text-right">
                <label class="col-form-label required">商品</label>
            </div>
            <div class="col-sm-6">
                <div class="input-group">             
                    <input class="form-control search-goods-name" value="<?= $model['name']?>" readonly>
                    <input class="search-goods-id" type="hidden" value="<?= $model['goods_id']?>" name="goods_id">
                    <span class="input-group-btn">
                        <a href="javascript:" class="btn btn-secondary search-goods" data-toggle="modal" data-target="#searchGoodsModal">选择商品</a>
                    </span>
                </div>
            </div>
        </div>


        <div class="form-group row">
            <div class="form-group-label col-sm-2 text-right">
                <label class="col-form-label required">被采集淘宝商品链接或ID</label>
            </div>
            <div class="col-sm-6">
                <div class="input-group">
               	<input class="form-control" name="url" value="">
                </div>
				<div class="text-muted fs-sm">例如：商品链接为:http://item.taobao.com/item.htm?id=522155891308 <br />或输入ID:522155891308</div>
            </div>
        </div> 
        
            <div class="form-group row">
                <div class="form-group-label col-sm-2 text-right">
                    <label class="col-form-label required">页码</label>
                </div>
                <div class="col-sm-6">
                    <div class="input-group">
                        <input class="form-control" type="number" name="page"
                               value="1" max="100000" min="1">
                    </div>
                    <div class="text-muted fs-sm">必须输入页码，请填被采集商品按时间排序的评论页码(若同一商品采集多次，建议先采页码大的)</div>
                </div>
            </div>
            
        <div class="form-group row">
            <div class="form-group-label col-sm-2 text-right">
                <label class="col-form-label ">采集关键词</label>
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
                <label class="col-form-label ">过滤关键词</label>
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
            <label class="col-form-label required">采集客户晒图</label>
        </div>
        <div class="col-sm-2">

            <label class="radio-label">
                <input  <?= !isset($model['get_pics']) || $model['get_pics']== 1 ? 'checked' : null ?>
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
        <div class="form-group-label col-sm-2 text-right">
            <label class="col-form-label required">采集店主回复</label>
        </div>
        <div class="col-sm-2">

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
            <label class="col-form-label required">过滤重复评论</label>
        </div>
        <div class="col-sm-2">

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
        <div class="form-group-label col-sm-2 text-right">
            <label class="col-form-label required">使用淘宝评论时间</label>
        </div>
        <div class="col-sm-2">

            <label class="radio-label">
                <input <?= !isset($model['time_type']) || $model['time_type']== 1 ? 'checked' : null ?>
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
        </div>
    </div>

    
    <div class="form-group row">
        <div class="form-group-label col-sm-2 text-right">
            <label class="col-form-label required">启用替换规则</label>
        </div>
        <div class="col-sm-2">

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
        <div class="form-group-label col-sm-2 text-right">
            <label class="col-form-label required">评论用户</label>
        </div>
        <div class="col-sm-6">

            <label class="radio-label">
                <input <?= isset($model['user_type']) && $model['user_type']== 1 ? 'checked' : null ?>
                value="1"
                name="user_type" type="radio" class="custom-control-input">
                <span class="label-icon"></span>
                <span class="label-text">淘宝用户</span>
            </label>
            <label class="radio-label">
                <input <?= !isset($model['user_type']) || $model['user_type']== 2 ? 'checked' : null ?>
                value="2"
                name="user_type" type="radio" class="custom-control-input">
                <span class="label-icon"></span>
                <span class="label-text">云端虚拟用户</span>
            </label>
            <label class="radio-label">
                <input <?= isset($model['user_type']) && $model['user_type']== 3 ? 'checked' : null ?>
                value="3"
                name="user_type" type="radio" class="custom-control-input">
                <span class="label-icon"></span>
                <span class="label-text">系统虚拟用户</span>
            </label>
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



        <!-- Modal -->
    <div class="modal fade" data-backdrop="static" id="searchGoodsModal" tabindex="-1" role="dialog"
         aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div id="app" class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">查找商品</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="<?= $urlManager->createUrl(['mch/comment/search-goods']) ?>"
                          class="input-group  goods-search-form" method="get">
                        <input name="keyword" class="form-control" placeholder="商品名称">
                        <span class="input-group-btn">
                    <button class="btn btn-secondary submit-btn">查找</button>
                </span>
                    </form>
                    <div v-if="goodsList==null" class="text-muted text-center p-5">请输入商品名称查找商品</div>
                    <template v-else>
                        <div v-if="goodsList.length==0" class="text-muted text-center p-5">未查找到相关商品</div>
                        <template v-else>
                            <div class="goods-item row mt-3 mb-3" v-for="(item,index) in goodsList">
                                <div class="col-8">
                                    <div style="white-space: nowrap;overflow: hidden;text-overflow: ellipsis">
                                        {{item.name}}
                                    </div>
                                </div>
                                <div class="col-2 text-right">￥{{item.price}}</div>
                                <div class="col-2 text-right">
                                    <a href="javascript:" class="goods-select" v-bind:index="index">选择</a>
                                </div>
                            </div>
                        </template>
                    </template>
                </div>
            </div>
        </div>
    </div>

</div>
</div>
<script>
    var app = new Vue({
        el: "#app",
        data: {
            goodsList: null,
        }
    });

    $(document).on("click", ".goods-select", function () {
        var index = $(this).attr("index");
        var goods = app.goodsList[index];
        $("#searchGoodsModal").modal("hide");
        $(".search-goods-name").val(goods.name);
        $(".search-goods-id").val(goods.id);
        for (var i in goods.attr) {
            goods.attr[i].miaosha_price = parseFloat(goods.attr[i].price == 0 ? goods.price : goods.attr[i].price);
            goods.attr[i].miaosha_num = goods.attr[i].num;
            goods.attr[i].sell_num = 0;
        }
        app.goods = goods;
    });

    $(document).on("submit", ".goods-search-form", function () {
        var form = $(this);
        var btn = form.find(".submit-btn");
        btn.btnLoading("正在查找");
        $.ajax({
            url: form.attr("action"),
            type: "get",
            dataType: "json",
            data: form.serialize(),
            success: function (res) {
                btn.btnReset();
                if (res.code == 0) {
                    app.goodsList = res.data.list;
                }
            }
        });
        return false;
    }); 
</script>
<script type="text/javascript">
  $(function() {
    $('#datetimepicker1').datetimepicker({
      collapse: false
    });
  });

      (function () {
        $.datetimepicker.setLocale('zh');
        $('#addtime').datetimepicker({
            format: 'Y-m-d H:i:s',
            timepicker:true,
            onShow: function (ct) {
                this.setOptions({
                    maxDate: $('#addtime').val() ? $('#addtime').val() : false
                })
            },
        });
    })();
</script>