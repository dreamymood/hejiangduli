if (typeof wx === 'undefined') var wx = getApp().core;
var is_loading = false;
var is_no_more = true;
var quickNavigation = require('../../components/quick-navigation/quick-navigation.js');
Page({

    /**
     * 页面的初始数据
     */
    data: {
        p: 1,
        naver:'index'
    },
    
    onLoad (options) {
        getApp().page.onLoad(this, options);
        quickNavigation.init(this);
    },

    onShow(){
        getApp().page.onShow(this);
        getApp().core.showLoading({
            title: '加载中',
        })
        var self = this;
        getApp().request({
            url: getApp().api.lottery.index,
            success: function (res) {
                if(res.code == 0){
                    self.setData(res.data);
                    if (res.data.goods_list != null && res.data.goods_list.length > 0) {
                        is_no_more = false;
                    }
                }
            },
            complete: function (res) {
                getApp().core.hideLoading();
            }
        });
    },
    submit:function(e) {
        var formId = e.detail.formId;
        var lottery_id = e.currentTarget.dataset.lottery_id;
        getApp().core.navigateTo({
            url: "/lottery/detail/detail?lottery_id=" + lottery_id + "&form_id=" + formId,
        });
    },
    /**
     * 页面上拉触底事件的处理函数
     */
    onReachBottom: function() {
        if (is_no_more) {
            return;
        }
        this.loadData();
    },

    // 上拉加载数据
    loadData: function() {
        if (is_loading) {
            return;
        }
        is_loading = true;
        getApp().core.showLoading({
            title: '加载中',
        });
        var self = this;
        var p = self.data.p + 1;
        getApp().request({
            url: getApp().api.lottery.index,
            data: {
                page: p
            },
            success: function(res) {
                if (res.code == 0) {
                    var goods_list = self.data.goods_list;
                    var list = self.data.list;
                    if (res.data.goods_list == null || res.data.goods_list.length == 0) {
                        is_no_more = true;
                        return;
                    }
                    list['num']  = list.num.concat(res.data.list.num);
                    list['status']  = list.status.concat(res.data.list.status);

                    goods_list = goods_list.concat(res.data.goods_list);
                    self.setData({
                        goods_list: goods_list,
                        list: list,
                        p: p
                    });
                } else {
                    self.showToast({
                        title: res.msg,
                    });
                }
            },
            complete: function (res) {
                getApp().core.hideLoading();
                is_loading = false;
            }
        });
    },
})
