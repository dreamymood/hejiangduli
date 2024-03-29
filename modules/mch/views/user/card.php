<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/11/29
 * Time: 13:58
 */
defined('YII_ENV') or exit('Access Denied');


use yii\widgets\LinkPager;

/**
 * @var \app\models\User $user ;
 * @var \app\models\User $clerk ;
 * @var \app\models\Shop $shop ;
 */
$urlManager = Yii::$app->urlManager;
$this->title = '卡券管理';
$this->params['active_nav_group'] = 4;
$status = Yii::$app->request->get('status');
$user_id = Yii::$app->request->get('user_id');
$condition = ['user_id' => $user_id];
if ($status === '' || $status === null || $status == -1) {
    $status = -1;
}
?>
<div class="panel mb-3">
    <div class="panel-header"><?= $this->title ?></div>
    <div class="panel-body">
        <div class="mb-3 clearfix">
            <div class="p-4 bg-shaixuan">
                <form method="get" style="max-width:50rem">
                    <?php $_s = ['keyword', 'add_time_begin', 'add_time_end', 'clerk_time_begin', 'clerk_time_end', 'shop_name', 'card_name'] ?>
                    <?php foreach ($_GET as $_gi => $_gv) :
                        if (in_array($_gi, $_s)) {
                            continue;
                        } ?>
                        <input type="hidden" name="<?= $_gi ?>" value="<?= $_gv ?>">
                    <?php endforeach; ?>
                    <div class="input-group mr-4 mb-4">
                        <span class="input-group-addon">发放时间</span>
                        <input class="form-control" name="add_time_begin" autocomplete="off"
                               value="<?= isset($_GET['add_time_begin']) ? $_GET['add_time_begin'] : "" ?>">
                        <span class="input-group-addon">至</span>
                        <input class="form-control" name="add_time_end" autocomplete="off"
                               value="<?= isset($_GET['add_time_end']) ? $_GET['add_time_end'] : "" ?>">
                    </div>
                    <div class="input-group mr-4 mb-4">
                        <span class="input-group-addon">核销时间</span>
                        <input class="form-control" name="clerk_time_begin" autocomplete="off"
                               value="<?= isset($_GET['clerk_time_begin']) ? $_GET['clerk_time_begin'] : "" ?>">
                        <span class="input-group-addon">至</span>
                        <input class="form-control" name="clerk_time_end" autocomplete="off"
                               value="<?= isset($_GET['clerk_time_end']) ? $_GET['clerk_time_end'] : "" ?>">
                    </div>
                    <div class="input-group mb-4">
                        <input class="form-control mr-4" placeholder="商家名称" name="shop_name" autocomplete="off"
                               value="<?= isset($_GET['shop_name']) ? trim($_GET['shop_name']) : null ?>">
                        <input class="form-control mr-4" placeholder="卡券名称" name="card_name" autocomplete="off"
                               value="<?= isset($_GET['card_name']) ? trim($_GET['card_name']) : null ?>">
                        <input class="form-control"
                               placeholder="昵称"
                               name="keyword"
                               autocomplete="off"
                               value="<?= isset($_GET['keyword']) ? trim($_GET['keyword']) : null ?>">
                    <span class="input-group-btn">
                    <button class="btn btn-primary">筛选</button>
                </span>
                    </div>
                </form>
                <?php if ($user) : ?>
                    <span class="status-item mr-3">会员：<?= $user->nickname ?>拥有的卡券</span>
                <?php endif; ?>
            </div>
        </div>
        <div class="mb-4">
            <?php if ($clerk || $shop) : ?>
                <?php if ($clerk) : ?>
                    <span class="status-item mr-3">核销员：<?= $clerk->nickname ?>核销的卡券</span>
                <?php endif; ?>
                <?php if ($shop) : ?>
                    <span class="status-item mr-3">门店：<?= $shop->name ?>核销的卡券</span>
                <?php endif; ?>
            <?php else : ?>
                <ul class="nav nav-tabs status">
                    <li class="nav-item">
                        <a class="status-item nav-link <?= $status == -1 ? 'active' : null ?>"
                           href="<?= $urlManager->createUrl(array_merge(['mch/user/card'], $condition)) ?>">全部</a>
                    </li>
                    <li class="nav-item">
                        <a class="status-item nav-link <?= $status == 0 ? 'active' : null ?>"
                           href="<?= $urlManager->createUrl(array_merge(['mch/user/card'], $condition, ['status' => 0])) ?>">未使用<?= $data['status_0'] ? '(' . $data['status_0'] . ')' : null ?></a>
                    </li>
                    <li class="nav-item">
                        <a class="status-item nav-link <?= $status == 1 ? 'active' : null ?>"
                           href="<?= $urlManager->createUrl(array_merge(['mch/user/card'], $condition, ['status' => 1])) ?>">已使用<?= $data['status_1'] ? '(' . $data['status_1'] . ')' : null ?></a>
                    </li>

                </ul>
            <?php endif; ?>
        </div>
        <table class="table table-bordered bg-white">
            <thead>
            <tr>
                <th>昵称</th>
                <th>卡券名称</th>
                <th>卡券信息</th>
                <th>发放时间</th>
                <th>状态</th>
                <th>核销商家</th>
                <th>核销时间</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($list as $index => $value) : ?>
                <tr>
                    <td><?= $value['nickname'] ?></td>
                    <td><?= $value['card_name'] ?></td>
                    <td>
                        <div class="info p-2" style="border: 1px solid #ddd;">
                            <div flex="dir:left box:first">
                                <div class="mr-4" data-responsive="88:88" style="width:44px;
                                    background-image: url(<?= $value['card_pic_url'] ?>);background-size: cover;
                                    background-position: center;border-radius: 88px;"></div>
                                <div flex="dir:left cross:center"><?= $value['card_content'] ?></div>
                            </div>
                        </div>
                    </td>
                    <td><?= date('Y-m-d H:i:s', $value['addtime']) ?></td>
                    <td><?= $value['is_use'] == 0 ? "未使用" : "已使用" ?></td>
                    <td><?= $value['shop_name'] ?></td>
                    <td><?= $value['clerk_time'] ? date('Y-m-d H:i:s', $value['clerk_time']) : ""; ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <div class="text-center">
            <?= \yii\widgets\LinkPager::widget(['pagination' => $pagination,]) ?>
            <div class="text-muted"><?= $row_count ?>条数据</div>
        </div>
    </div>
</div>
<?= $this->render('/layouts/ss') ?>
<script>
    (function () {
        $.datetimepicker.setLocale('zh');
        $("input[name='add_time_begin']").datetimepicker({
            format: 'Y-m-d',
            onShow: function (ct) {
                this.setOptions({
                    maxDate: $("input[name='add_time_end']").val() ? $("input[name='add_time_end']").val() : false
                })
            },
            timepicker: false,
        });
        $("input[name='add_time_end']").datetimepicker({
            format: 'Y-m-d',
            onShow: function (ct) {
                this.setOptions({
                    minDate: $("input[name='add_time_begin']").val() ? $("input[name='add_time_begin']").val() : false
                })
            },
            timepicker: false,
        });
        $("input[name='clerk_time_begin']").datetimepicker({
            format: 'Y-m-d',
            onShow: function (ct) {
                this.setOptions({
                    maxDate: $("input[name='clerk_time_end']").val() ? $("input[name='clerk_time_end']").val() : false
                })
            },
            timepicker: false,
        });
        $("input[name='clerk_time_end']").datetimepicker({
            format: 'Y-m-d',
            onShow: function (ct) {
                this.setOptions({
                    minDate: $("input[name='clerk_time_begin']").val() ? $("input[name='clerk_time_begin']").val() : false
                })
            },
            timepicker: false,
        });
    })();
</script>
<script>
    $(document).on('click', '.del', function () {
        var a = $(this);
        $.myConfirm({
            content: a.data('content'),
            confirm: function () {
                $.ajax({
                    url: a.data('url'),
                    type: 'get',
                    dataType: 'json',
                    success: function (res) {
                        if (res.code == 0) {
                            window.location.reload();
                        } else {
                            $.myAlert({
                                title: '提示',
                                content: res.msg
                            });
                        }
                    }
                });
            }
        });
        return false;
    });
</script>


