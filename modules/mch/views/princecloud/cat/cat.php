<?php
defined('YII_ENV') or exit('Access Denied');

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/6/29
 * Time: 9:50
 */

use yii\widgets\LinkPager;

$urlManager = Yii::$app->urlManager;
$imgurl = Yii::$app->request->baseUrl;
$this->title = '分类列表';
$this->params['active_nav_group'] = 2;
$urlStr = get_plugin_url();
$show = true;
if (in_array(get_plugin_type(), [0])) {
    $show = true;
} else {
    $show = false;
}
?>
<style>
    .modal-dialog {
        position: fixed;
        top: 20%;
        left: 45%;
        width: 240px;
    }

    .modal-content {
        width: 240px;
    }

    .modal-body {
        /*height:200px;*/
    }

    table {
        table-layout: fixed;
    }

    th {
        text-align: center;
        white-space: nowrap;
        text-overflow: ellipsis;
        overflow: hidden;
    }

    td {
        text-align: center;
        line-height: 30px;
    }

    .ellipsis {
        white-space: nowrap;
        text-overflow: ellipsis;
        overflow: hidden;
    }

    td.nowrap {
        white-space: nowrap;
        overflow: hidden;
    }

    .goods-pic {
        margin: 0 auto;
        width: 3rem;
        height: 3rem;
        background-color: #ddd;
        background-size: cover;
        background-position: center;
    }
</style>

<div class="panel mb-3">
    <!--div class="panel-header"><?= $this->title ?></div-->
    <div class="panel-body">
        <div class="mb-3 clearfix">
            <div class="float-left">
                <div class="dropdown float-right ml-2">
                    <a href="javascript:" class="btn btn-primary batch "
                           data-url="<?= $urlManager->createUrl([$urlStr . '/batch']) ?>" data-content="是否批量采集">批量采集</a>
  
                </div>
            </div>
        </div>
        <table class="table table-bordered bg-white table-hover">
            <thead>
            <tr>
                <th style="text-align: center;text-overflow:clip;">
                    <label class="checkbox-label" style="margin-right: 0px;">
                        <input type="checkbox" class="goods-all">
                        <span class="label-icon"></span>
                    </label>
                </th>
                <th>ID</th>
                <th>分类名称</th>
                <th>图标</th>
            </tr>
            </thead>
            <col style="width: 2.5%">
            <col style="width: 6%">
            <col style="width: 7%">
            <col style="width: 17%">
            <tbody>
            <?php foreach ($cat_list as $index => $cat) : ?>
                <tr>
                    <td class="nowrap" style="text-align: center;">
                        <label class="checkbox-label" style="margin-right: 0px;">
                            <input data-num="0" type="checkbox"
                                   class="goods-one"
                                   value="<?= $cat['id'] ?>">
                            <span class="label-icon"></span>
                        </label>
                    </td>
                    <td>
                        <span><?= $cat['id']?></span>              
                    </td>
                    <td><?= $cat['name'] ?></td>
                    <td>
                        <?php if (!empty($cat['pic_url'])) : ?>
                            <img src="<?= $cat['pic_url'] ?>"
                                 style="width: 20px;height: 20px;">
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>

        </table>
    </div>
</div>





<script>
    $("#myModal").modal({backdrop: "static", keyboard: false});

    $("#closeModel").click(function () {
        $("#goods_qrcode_wx").attr("src", '');
        $("#goods_qrcode_my").attr("src", '');
    });


    function upDown(id, type) {
        var text = '';
        if (type == 'up') {
            text = "上架";
        } else if (type == 'start') {
            text = "加入快速购买";
        } else if (type == 'close') {
            text = "关闭快速购买";
        } else {
            text = '下架';
        }

        var url = "<?= $urlManager->createUrl([$urlStr . '/goods-up-down']) ?>";
        layer.confirm("是否" + text + "？", {
            btn: [text, '取消'] //按钮
        }, function () {
            layer.msg('加载中', {
                icon: 16
                , shade: 0.01
            });
            $.ajax({
                url: url,
                type: 'get',
                dataType: 'json',
                data: {id: id, type: type},
                success: function (res) {
                    if (res.code == 0) {
                        window.location.reload();
                    }
                    if (res.code == 1) {
                        layer.alert(res.msg, {
                            skin: 'layui-layer-molv'
                            , closeBtn: 0
                            , anim: 4 //动画类型
                        });
                        if (res.return_url) {
                            location.href = res.return_url;
                        }
                    }
                }
            });
        });
        return false;
    }

    $(document).on('click', '.goods-all', function () {
        var checked = $(this).prop('checked');
        $('.goods-one').prop('checked', checked);
        if (checked) {
            $('.batch').addClass('is_use');
        } else {
            $('.batch').removeClass('is_use');
        }
    });
    $(document).on('click', '.goods-one', function () {
        var checked = $(this).prop('checked');
        var all = $('.goods-one');
        var is_all = true;//只要有一个没选中，全选按钮就不选中
        var is_use = false;//只要有一个选中，批量按妞就可以使用
        all.each(function (i) {
            if ($(all[i]).prop('checked')) {
                is_use = true;
            } else {
                is_all = false;
            }
        });
        if (is_all) {
            $('.goods-all').prop('checked', true);
        } else {
            $('.goods-all').prop('checked', false);
        }
        if (is_use) {
            $('.batch').addClass('is_use');
        } else {
            $('.batch').removeClass('is_use');
        }
    });
    $(document).on('click', '.batch', function () {
        var all = $('.goods-one');
        var is_all = true;//只要有一个没选中，全选按钮就不选中
        all.each(function (i) {
            if ($(all[i]).prop('checked')) {
                is_all = false;
            }
        });
        if (is_all) {
            $.myAlert({
                content: "请先勾选商品"
            });
        }
    });
    // 批量采集
    $(document).on('click', '.batch-copy-btn', function () {
        var cat_id = $('select[name^="copy[cat_id]"]').val();
        var all = $('.goods-one');
        var is_all = true;//只要有一个没选中，全选按钮就不选中
        all.each(function (i) {
            if ($(all[i]).prop('checked')) {
                is_all = false;
            }
        });
        if (is_all) {
            $.myAlert({
                content: "请先勾选商品"
            });
            return;
        }
        var a = $(this);
        var cat_group = [];
        all.each(function (i) {
            if ($(all[i]).prop('checked')) {
                var goods = {};
                cat_group.push($(all[i]).val());
            }
        });

        $.ajax({
            url: "<?= Yii::$app->urlManager->createUrl([$urlStr . '/batch-copy']) ?>",
            type: 'get',
            dataType: 'json',
            data: {
                cat_group: cat_group,
                cat_id: cat_id,
            },
            success: function (res) {
                if (res.code == 0) {
                            $.myAlert({
                                content: res.msg,
                                confirm: function () {
                                    window.location.reload();
                                }
                            });
                } else {
                    $('.modelError').text(res.msg);
                    $('.modelError').css('display', 'block');
                }
            },
//            complete: function () {
//                $.myLoadingHide();
//            }
        });


    });
    $(document).on('click', '.is_use', function () {
        var a = $(this);
        var cat_group = [];
        var all = $('.goods-one');
        all.each(function (i) {
            if ($(all[i]).prop('checked')) {
                var goods = {};
                goods.id = $(all[i]).val();
                goods.num = $(all[i]).data('num');
                cat_group.push(goods);
            }
        });
        $.myConfirm({
            content: a.data('content'),
            confirm: function () {
                $.myLoading();
                $.ajax({
                    url: a.data('url'),
                    type: 'get',
                    dataType: 'json',
                    data: {
                        cat_group: cat_group,
                        type: a.data('type'),
                    },
                    success: function (res) {
                        if (res.code == 0) {
                            $.myAlert({
                                content: res.msg,
                                confirm: function () {
                                    window.location.reload();
                                }
                            });
                        } else {
                            $.myAlert({
                                content: res.msg
                            });
                        }
                    },
                    complete: function () {
                        $.myLoadingHide();
                    }
                });
            }
        })
    });
</script>
