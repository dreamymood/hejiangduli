<?php

$this->title = '收款账户设置';
$url_manager = Yii::$app->urlManager;
?>
<style>
    .user-item {
        border-bottom: 1px solid #e3e3e3;
        padding: .5rem 0;
    }

    .user-item:first-child {
        border-top: 1px solid #e3e3e3;
    }
</style>
<div class="panel mb-3">
    <div class="panel-header">
        <span><?= $this->title ?></span>
    </div>
    <div class="panel-body" id="app">
        <form class="auto-form" method="post"
              return="<?= Yii::$app->request->referrer ? Yii::$app->request->referrer : '' ?>">
            <div class="form-group row">
                <div class="form-group-label col-sm-2 text-right required">
                    <label class="col-form-label required">收款用户</label>
                </div>
                <div class="col-sm-6">
                    <div class="input-group">
                        <input class="user-id" type="hidden" name="user_id"  value="<?= $model->user_id ?>"  readonly>
                        <input class="form-control user-nickname" value="<?= $model->user->nickname ?>" readonly>
                        <span class="input-group-btn">
                        <a href="javascript:" class="btn btn-secondary" data-toggle="modal"
                           data-target="#searchUserModal">查找</a>
                        </span>
                    </div>
                    <div class="text-danger text-muted">注：请选择正确的小程序用户用于接收提现款项，请认真核对头像和昵称，切勿选错。</div>
                </div>
            </div>
            <div class="form-group row">
                <div class="form-group-label col-sm-2 text-right">
                    <label class="col-form-label">微信号</label>
                </div>
                <div class="col-sm-6">
                    <input class="form-control" name="wechat_name" value="<?= $model->wechat_name ?>">
                    <div class="text-danger text-muted">注：请填写您的微信号，以便与您取得联系。</div>
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

        <!-- Search User Modal -->
        <div class="modal fade" id="searchUserModal" data-backdrop="static">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">查找用户</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form class="search-user-form">
                            <div class="input-group mb-3">
                                <input class="form-control">
                                <span class="input-group-btn">
                                    <button class="btn btn-secondary">查找</button>
                                </span>
                            </div>
                            <div>
                                <template v-if="user_list && user_list.length">
                                    <div v-for="(u,i) in user_list" class="user-item"
                                         flex="dir:left box:last cross:center">
                                        <div>
                                            <img :src="u.avatar_url"
                                                 style="width: 1.5rem;height: 1.5rem;border-radius: .15rem;">
                                            <span>{{u.nickname}}</span>
                                        </div>
                                        <div>
                                            <a class="btn btn-sm btn-secondary select-user" href="javascript:"
                                               :data-index="i">选择</a>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
<script>
    var app = new Vue({
        el: '#app',
        data: {
            user_list: [],
        },
    });


    $(document).on('submit', '.search-user-form', function () {
        var form = $(this);
        var btn = form.find('button');

        btn.btnLoading();
        $.ajax({
            url: '<?=Yii::$app->urlManager->createUrl(['mch/princeaccount/account/user'])?>',
            dataType: 'json',
            data: {
                keyword: form.find('input').val(),
            },
            success: function (res) {
                if (res.code == 0) {
                    app.user_list = res.data;
                }
            },
            complete: function () {
                btn.btnReset();
            }
        });
        return false;
    });

    $(document).on('click', '.select-user', function () {
        var index = $(this).attr('data-index');
        var user = app.user_list[index];
        $('.user-id').val(user.id);
        $('.user-nickname').val(user.nickname);
        $('#searchUserModal').modal('hide');
    });

</script>