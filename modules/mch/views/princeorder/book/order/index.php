<?php
defined('YII_ENV') or exit('Access Denied');

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/6/29
 * Time: 9:50
 */

use yii\widgets\LinkPager;

/* @var \app\models\User $user */

$urlManager = Yii::$app->urlManager;
$statics = Yii::$app->request->baseUrl . '/statics';
$this->title = '预约订单列表';
$this->params['active_nav_group'] = 10;
$this->params['is_book'] = 1;
$status = Yii::$app->request->get('status');
$is_offline = Yii::$app->request->get('is_offline');
$user_id = Yii::$app->request->get('user_id');
$condition = ['user_id' => $user_id, 'is_offline' => $is_offline, 'clerk_id' => $_GET['clerk_id'], 'shop_id' => $_GET['shop_id']];
if ($status === '' || $status === null || $status == -1) {
    $status = -1;
}
$urlStr = get_plugin_url();
$urlPlatform = Yii::$app->controller->route;
?>
<style>
    .titleColor{
        color: #888888;
    }

    .order-item {
        border: 1px solid transparent;
        margin-bottom: 1rem;
    }

    .order-item table {
        margin: 0;
    }

    .order-item:hover {
        border: 1px solid #3c8ee5;
    }

    .goods-item {
        /* margin-bottom: .75rem; */
        border: 1px solid #ECEEEF;
        padding: 10px;
        margin-top: -1px;
    }

    .table tbody tr td{
        vertical-align: middle;
    }

    .goods-item:last-child {
        margin-bottom: 0;
    }

    .goods-pic {
        width: 5.5rem;
        height: 5.5rem;
        display: inline-block;
        background-color: #ddd;
        background-size: cover;
        background-position: center;
        margin-right: 1rem;
    }

    .goods-name {
        white-space: nowrap;
        text-overflow: ellipsis;
        overflow: hidden;
    }

    .order-tab-1 {
        width: 40%;
    }

    .order-tab-2 {
        width: 20%;
        text-align: center;
    }

    .order-tab-3 {
        width: 10%;
        text-align: center;
    }

    .order-tab-4 {
        width: 20%;
        text-align: center;
    }

    .order-tab-5 {
        width: 10%;
        text-align: center;
    }

    .status-item.active {
        color: inherit;
    }
</style>
<div class="panel mb-3">
    <div class="panel-header"><?= $this->title ?></div>
    <div class="panel-body">
        <div class="mb-3 clearfix">
            <div class="p-4 bg-shaixuan">
                <form method="get">
                    <?php $_s = ['keyword', 'keyword_1', 'date_start', 'date_end'] ?>
                    <?php foreach ($_GET as $_gi => $_gv) :
                        if (in_array($_gi, $_s)) {
                            continue;
                        } ?>
                        <input type="hidden" name="<?= $_gi ?>" value="<?= $_gv ?>">
                    <?php endforeach; ?>
                    <div flex="dir:left">
                        <div class="ml-3 mr-4">
                            <div class="form-group row">
                                <div>
                                    <label class="col-form-label">下单时间：</label>
                                </div>
                                <div>
                                    <div class="input-group">
                                        <input class="form-control" id="date_start" name="date_start"
                                               autocomplete="off"
                                               value="<?= isset($_GET['date_start']) ? trim($_GET['date_start']) : '' ?>">
                                        <span class="input-group-btn">
                                            <a class="btn btn-secondary" id="show_date_start" href="javascript:">
                                                <span class="iconfont icon-daterange"></span>
                                            </a>
                                        </span>
                                        <span class="middle-center input-group-addon" style="padding:0 4px">至</span>
                                        <input class="form-control" id="date_end" name="date_end"
                                               autocomplete="off"
                                               value="<?= isset($_GET['date_end']) ? trim($_GET['date_end']) : '' ?>">
                                        <span class="input-group-btn">
                                            <a class="btn btn-secondary" id="show_date_end" href="javascript:">
                                                <span class="iconfont icon-daterange"></span>
                                            </a>
                                        </span>
                                    </div>
                                </div>
                                <div class="middle-center">
                                    <a href="javascript:" class="new-day btn btn-primary" data-index="7">近7天</a>
                                    <a href="javascript:" class="new-day btn btn-primary" data-index="30">近30天</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div flex="dir:left">
                        <div class="mr-4">
                            <div class="form-group row w-30">
                                <div class="col-4">
                                    <select class="form-control" name="keyword_1">
                                        <option value="1" <?= $_GET['keyword_1'] == 1 ? "selected" : "" ?>>订单号</option>
                                        <option value="2" <?= $_GET['keyword_1'] == 2 ? "selected" : "" ?>>用户</option>
                                        <option value="3" <?= $_GET['keyword_1'] == 3 ? "selected" : "" ?>>商品名</option>
                                    </select>
                                </div>
                                <div class="col-8">
                                    <input class="form-control"
                                           name="keyword"
                                           autocomplete="off"
                                           value="<?= isset($_GET['keyword']) ? trim($_GET['keyword']) : null ?>">
                                </div>
                            </div>
                        </div>
                        <div class="mr-4">
                            <div class="form-group">
                                <button class="btn btn-primary mr-4">筛选</button>
                                <a class="btn btn-secondary export-btn"
                                   href="javascript:">批量导出</a>
                                <a class="btn btn-secondary del"
                                   href="javascript:"
                                   data-url="<?= $urlManager->createUrl(['mch/princeorder/book/order/delete-all']) ?>"
                                   data-content="是否清空回收站？">清空回收站</a>
                            </div>
                        </div>
                    </div>
                    <div flex="dir:left">
                        <div class="mr-4">
                            <?php if ($user) : ?>
                                <span class="status-item mr-3">会员：<?= $user->nickname ?>的订单</span>
                            <?php endif; ?>
                            <?php if ($clerk) : ?>
                                <span class="status-item mr-3">核销员：<?= $clerk->nickname ?>的订单</span>
                            <?php endif; ?>
                            <?php if ($shop) : ?>
                                <span class="status-item mr-3">门店：<?= $shop->name ?>的订单</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div flex="dir:left">
                        <label class="col-form-label">来源平台：</label>
                        <div class="dropdown float-right">
                            <button class="btn btn-secondary dropdown-toggle" type="button"
                                    data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <?php if ($_GET['platform'] === '1') :
                                    ?>支付宝
                                <?php elseif ($_GET['platform'] === '0') :
                                    ?>微信
                                <?php elseif ($_GET['platform'] == '') :
                                    ?>全部订单
                                <?php else : ?>
                                <?php endif; ?>
                            </button>
                            <div class="dropdown-menu" style="min-width:8rem">
                                <a class="dropdown-item" href="<?= $urlManager->createUrl([$urlPlatform]) ?>">全部订单</a>
                                <a class="dropdown-item"
                                   href="<?= $urlManager->createUrl([$urlPlatform, 'platform' => 1]) ?>">支付宝</a>
                                <a class="dropdown-item"
                                   href="<?= $urlManager->createUrl([$urlPlatform, 'platform' => 0]) ?>">微信</a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <div class="mb-4">
            <ul class="nav nav-tabs status">
                <li class="nav-item">
                    <a class="status-item nav-link <?= $status == -1 ? 'active' : null ?>"
                       href="<?= $urlManager->createUrl(array_merge(['mch/princeorder/book/order/index'])) ?>">全部</a>
                </li>
                <li class="nav-item">
                    <a class="status-item nav-link <?= $status == 0 ? 'active' : null ?>"
                       href="<?= $urlManager->createUrl(array_merge(['mch/princeorder/book/order/index'], $condition, ['status' => 0])) ?>">待付款<?= $store_data['status_count']['status_0'] ? '(' . $store_data['status_count']['status_0'] . ')' : null ?></a>
                </li>
                <li class="nav-item">
                    <a class="status-item nav-link <?= $status == 1 ? 'active' : null ?>"
                       href="<?= $urlManager->createUrl(array_merge(['mch/princeorder/book/order/index'], $condition, ['status' => 1])) ?>">待使用<?= $store_data['status_count']['status_1'] ? '(' . $store_data['status_count']['status_1'] . ')' : null ?></a>
                </li>
                <li class="nav-item">
                    <a class="status-item nav-link <?= $status == 2 ? 'active' : null ?>"
                       href="<?= $urlManager->createUrl(array_merge(['mch/princeorder/book/order/index'], $condition, ['status' => 2])) ?>">已使用<?= $store_data['status_count']['status_2'] ? '(' . $store_data['status_count']['status_2'] . ')' : null ?></a>
                </li>
                <li class="nav-item">
                    <a class="status-item nav-link <?= $status == 3 ? 'active' : null ?>"
                       href="<?= $urlManager->createUrl(array_merge(['mch/princeorder/book/order/index'], $condition, ['status' => 3])) ?>">退款<?= $store_data['status_count']['status_6'] ? '(' . $store_data['status_count']['status_6'] . ')' : null ?></a>
                </li>
                <li class="nav-item">
                    <a class="status-item nav-link <?= $status == 5 ? 'active' : null ?>"
                       href="<?= $urlManager->createUrl(array_merge(['mch/princeorder/book/order/index'], $condition, ['status' => 5])) ?>">已取消<?= $store_data['status_count']['status_5'] ? '(' . $store_data['status_count']['status_5'] . ')' : null ?></a>
                </li>
                <li class="nav-item">
                    <a class="status-item  nav-link <?= $status == 8 ? 'active' : null ?>"
                       href="<?= $urlManager->createUrl(array_merge([$_GET['r']], $condition, ['status' => 8])) ?>">回收站</a>
                </li>
            </ul>
        </div>
        <table class="table table-bordered bg-white">
            <tr>
                <th class="order-tab-1">商品信息</th>
                <th class="order-tab-2">表单信息</th>
                <th class="order-tab-3">实际付款</th>
                <th class="order-tab-4">订单状态</th>
                <th class="order-tab-5">操作</th>
            </tr>
        </table>
        <?php foreach ($list as $order_item) : ?>
            <div class="order-item">
                <table class="table table-bordered bg-white">
                    <tr>
                        <td colspan="5">
                            <span class="mr-3">
                            <span class='titleColor'>商城：</span><?= $order_item['store_name'] ?></span>
                            <span class="titleColor">下单时间：</span><?= date('Y-m-d H:i:s', $order_item['addtime']) ?></span>
                            <span class="mr-1">
                                <?php if ($order_item['is_pay'] == 1) : ?>
                                    <span class="badge badge-success">已付款</span>
                                <?php else : ?>
                                    <span class="badge badge-default">未付款</span>
                                    <?php if ($order_item['is_cancel'] == 1) : ?>
                                        <span class="badge badge-warning">已取消</span>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </span>
                            <span class="mr-5"><span class="titleColor">订单号：</span><?= $order_item['order_no'] ?></span>
                            <span class="mr-5">
                                <span class="titleColor">用户：</span><?= $order_item['nickname'] ?>
                                <?php if (isset($order_item['platform']) && intval($order_item['platform']) === 0): ?>
                                    <span class="badge badge-success">微信</span>
                                <?php elseif (isset($order_item['platform']) && intval($order_item['platform']) === 1): ?>
                                    <span class="badge badge-primary">支付宝</span>
                                <?php else: ?>
                                    <span class="badge badge-default">未知</span>
                                <?php endif; ?>
                            </span>
                            <?php if ($order_item['apply_delete'] == 1) : ?>
                                <span class="mr-1">
                                    <span class="titleColor">退款状态：</span>
                                    <?php if ($order_item['is_refund'] == 1) : ?>
                                        <span class="badge badge-success">已退款</span>
                                    <?php elseif($order_item['is_refund'] == 2) : ?>
                                        <span class="badge badge-danger">拒绝退款</span>
                                    <?php else : ?>
                                        <span class="badge badge-warning">申请退款中</span>
                                    <?php endif; ?>
                                </span>
                            <?php endif; ?>
                            <span class="ml-1">
                                <?php if ($order_item['is_pay'] == 1 && $order_item['is_refund'] == 0 && $order_item['apply_delete'] == 1) : ?>
                                    <a class="btn btn-sm btn-info send-confirm-btn" href="javascript:"
                                        data-order-id="<?= $order_item['id'] ?>" data-order-type="1">
                                        同意退款
                                     </a>
                                <?php endif; ?>
                                <?php if ($order_item['is_pay'] == 1 && $order_item['is_refund'] == 0 && $order_item['apply_delete'] == 1) : ?>
                                    <a class="btn btn-sm btn-danger send-confirm-btn" href="javascript:"
                                       data-order-id="<?= $order_item['id'] ?>" data-order-type="2">
                                       拒绝退款
                                    </a>
                                <?php endif; ?>
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td class="order-tab-1" style="line-height: 100%;">
                            <div class="goods-item" flex="dir:left box:first">
                                <div class="fs-0">
                                    <div class="goods-pic"
                                         style="background-image: url('<?= $order_item['cover_pic'] ?>')"></div>
                                </div>
                                <div class="goods-info">
                                    <div class="goods-name"><?= $order_item['goods_name'] ?></div>
                                    <div class="mt-1">
                                        <div class="fs-sm">
                                            小计：
                                            <span class="text-danger"><?= $order_item['total_price'] ?>元</span>
                                </div>
                            </div>
                                </div>
                            </div>
                        </td>
                        <td class="order-tab-2"  id='form'>
                            <?php foreach ($order_item['orderFrom'] as $k => $v) : ?>
                                <?php if ($v->type == 'uploadImg') : ?>
                                    <div flex="main:center box:first">
                                        <div><?= $v->key ?>:</div>
                                        <?php if ($v->value) : ?>
                                            <a href="<?= $v->value ?>" target="_blank"
                                               style="width: 80px; height: 80px;">
                                                <img src="<?= $v->value ?>" style="width: auto; height: auto; max-width: 100%;max-height: 100%;">
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                <?php else : ?>
                                    <div><span class="titleColor" ><?= $v->key ?></span>：<?= $v->value ?></div>
                                <?php endif; ?>
                            <?php endforeach; ?>

                        </td>
                        <td class="order-tab-3">
                            <div><span style="color:blue;"><?= $order_item['pay_price'] ?></span>元</div>
                        </td>
                        <td class="order-tab-4">
                                <?php if ($order_item['is_pay'] == 1) : ?>
                                <?php if ($order_item['pay_type'] == 2) : ?>
                                    <div>
                                        支付方式：
                                        <span class="badge badge-success">余额支付</span>
                                    </div>
                                <?php else : ?>
                                    <div>
                                        支付方式：
                                        <span class="badge badge-success">线上支付</span>
                                    </div>
                                <?php endif; ?>
                            <?php endif; ?>

                            <?php if ($order_item['is_pay'] == 1) : ?>
                                <div>
                                    使用状态：
                                    <?php if ($order_item['is_use'] == 1) : ?>
                                        <span class="badge badge-success">已使用</span>
                                    <?php else : ?>
                                        <span class="badge badge-default">未使用</span>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                            <?php if ($order_item['is_use'] == 1) :?>
                                <div>
                                    <span>核销员：<?=$order_item['clerk_name']?></span>
                                </div>
                                <div>
                                    <span>核销门店：<?=$order_item['shop_name']?></span>
                                </div>
                            <?php endif;?>
                        </td>
                        <td class="order-tab-5">
                            <?php if (($order_item['is_pay'] == 1 || $order_item['pay_type'] == 2) && $order_item['is_use'] != 1 && $order_item['is_refund'] == 0) : ?>
                                <div class="mb-2">
                                    <a class="btn btn-sm btn-danger mt-2  del" href="javascript:"
                                       data-content="对不起，请到产生该订单的商城“应用专区->预约->订单管理”菜单进行操作。">核销</a>
                                </div>
                            <?php endif; ?>
                            <div class="mb-2">
                                <a class="btn btn-sm btn-primary"
                                   href="<?= $urlManager->createUrl([$urlStr . '/detail', 'order_id' => $order_item['id']]) ?>">详情</a>
                                <a class="btn btn-sm btn-primary about"
                                   href="javascript:" data-toggle="modal"
                                   data-target="#about" data-id="<?= $order_item['id'] ?>" data-remarks="<?= $order_item['seller_comments'] ?>" data-url="<?=$urlManager->createUrl([$urlStr . '/seller-comments', 'order_id' => $order_item['id']])?>">添加备注</a>
                            </div>
                            <div class="mb-2">
                            <?php if ($order_item['is_recycle'] == 1) : ?>
                                <div>
                                    <a class="btn btn-sm btn-primary del" href="javascript:"
                                   data-url="<?= $urlManager->createUrl(['mch/princeorder/book/order/recycle', 'order_id' => $order_item['id'], 'is_recycle' => 0]) ?>"
                                   data-content="是否移出回收站">移出回收站</a>
                                </div>
                                <div>
                                    <a class="btn btn-sm btn-danger del mt-2" href="javascript:"
                                   data-url="<?= $urlManager->createUrl(['mch/princeorder/book/order/delete', 'order_id' => $order_item['id']]) ?>"
                                       data-content="是否删除">删除订单</a>
                                </div>
                            <?php else : ?>
                                <div>
                                    <a class="btn btn-sm btn-danger del" href="javascript:"
                                   data-url="<?= $urlManager->createUrl(['mch/princeorder/book/order/recycle', 'order_id' => $order_item['id'], 'is_recycle' => 1]) ?>"
                                   data-content="是否移入回收站">移入回收站</a>
                                </div>
                            <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                </table>
            </div>
        <?php endforeach; ?>
        <div class="text-center">
            <nav aria-label="Page navigation example">
                <?php echo LinkPager::widget([
                    'pagination' => $pagination,
                    'prevPageLabel' => '上一页',
                    'nextPageLabel' => '下一页',
                    'firstPageLabel' => '首页',
                    'lastPageLabel' => '尾页',
                    'maxButtonCount' => 5,
                    'options' => [
                        'class' => 'pagination',
                    ],
                    'prevPageCssClass' => 'page-item',
                    'pageCssClass' => "page-item",
                    'nextPageCssClass' => 'page-item',
                    'firstPageCssClass' => 'page-item',
                    'lastPageCssClass' => 'page-item',
                    'linkOptions' => [
                        'class' => 'page-link',
                    ],
                    'disabledListItemSubTagOptions' => ['tag' => 'a', 'class' => 'page-link'],
                ])
                ?>
            </nav>
            <div class="text-muted">共<?= $row_count ?>条数据</div>
        </div>

    </div>
</div>

<!-- 添加备注 -->
<div class="modal fade" data-backdrop="static" id="about">
    <div class="modal-dialog modal-sm" role="document" style="max-width: 400px">
        <div class="modal-content">
            <div class="modal-header">
                <b class="modal-title">备注</b>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <input class="order-id" type="hidden">
                <input class="url" type="hidden">
                <div class="form-group row">
                    <label class="col-4 text-right col-form-label">添加备注信息：</label>
                    <div class="col-11" style="margin-left: 1rem;">
                        <textarea id="seller_comments" name="seller_comments" cols="90"
                                  rows="5"
                                  style="width: 100%;"></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">取消</button>
                <button type="button" class="btn btn-primary remarks">提交</button>
            </div>
        </div>
    </div>
</div>
<?= $this->render('/layouts/ss', [
    'exportList'=>$exportList
]) ?>
<script>
    $(document).on("click", ".apply-status-btn", function () {
        var url = $(this).attr("href");
        $.myConfirm({
            content: "确认“" + $(this).text() + "”？",
            confirm: function () {
                $.myLoading();
                $.ajax({
                    url: url,
                    dataType: "json",
                    success: function (res) {
                        $.myLoadingHide();
                        $.myAlert({
                            content: res.msg,
                            confirm: function () {
                                if (res.code == 0)
                                    location.reload();
                            }
                        });
                    }
                });
            }
        });
        return false;
    });


    //    $(document).on("click", ".send-btn", function () {
    //        var order_id = $(this).attr("data-order-id");
    //        $(".send-modal input[name=order_id]").val(order_id);
    //        $(".send-modal").modal("show");
    //    });
    $(document).on("click", ".send-confirm-btn", function () {

        var order_id = $(this).attr("data-order-id");
        var btn = $(this);
        var error = $(".send-form").find(".form-error");

        error.hide();
        let text = $(this)[0].innerText;
        let type = $(this).attr("data-order-type");

        $.myConfirm({
            content: "确认" + text + "？",
            confirm: function () {
                btn.btnLoading("正在提交");
                $.ajax({
                    url: "<?=$urlManager->createUrl(['mch/princeorder/book/order/refund'])?>",
                    type: "get",
                    data: {order_id: order_id, type: type},
                    dataType: "json",
                    success: function (res) {
                        $.myLoading();
                        $.myLoadingHide();
                        if (res.code == 0) {
                            btn.text(res.msg);
                            location.reload();
                            $(".send-modal").modal("hide");
                        }
                        if (res.code == 1) {
                            btn.btnReset();
                            error.html(res.msg).show();
                        }

                        $.myAlert({
                            content: res.msg,
                            confirm: function () {
                            if (res.code == 0)
                                location.reload();
                            }
                        });
                    }
                });

            }
        });
        return false;
    });
    $(document).on('click', '.del', function () {
        var a = $(this);
        $.myConfirm({
            content: a.data('content'),
            confirm: function () {
                $.myLoading();
                $.ajax({
                    url: a.data('url'),
                    type: 'get',
                    dataType: 'json',
                    success: function (res) {
                        if (res.code == 0) {
                            $.myAlert({
                                content:res.msg,
                                confirm:function(res){
                                    window.location.reload();
                                }
                            });
                        } else {
                            $.myAlert({
                                content: res.msg
                            });
                        }
                    },
                    complete:function(res){
                        $.myLoadingHide();
                    }
                });
            }
        });
        return false;
    });

    $(document).on('click', '.clerk-btn', function () {
        $('.order_id').val($(this).data('order-id'));
        $('.clerk-modal').modal('show');
    });


</script>

<script>
    $(document).on('click', '.about', function () {
        var order_id = $(this).data('id');
        $('.order-id').val(order_id);
        var url = $(this).data('url');
        $('.url').val(url);
        var remarks = $(this).data('remarks');
        $('#seller_comments').val(remarks);
    });

    $(document).on('click', '.remarks', function () {
        var seller_comments = $("#seller_comments").val();
        var btn = $(this);
        var order_id = $('.order-id').val();
        var url = $('.url').val();
        btn.btnLoading("正在提交");
        $.ajax({
            url: url,
            type: "get",
            data: {
                seller_comments: seller_comments,
            },
            dataType: "json",
            success: function (res) {
                if (res.code == 0) {
                    window.location.reload();
                } else {
                    error.html(res.msg).show()
                }
            }
        });
    });    

</script>