if (typeof wx === 'undefined') var wx = getApp().core;
var utils = getApp().helper;
var videoContext = '';
var WxParse = require('../../wxParse/wxParse.js');
var lotteryInter;
Page({

    /**
     * 页面的初始数据
     */
    data: {
        hide: "hide",
        time_list: {
            day: 0,
            hour: '00',
            minute: '00',
            second: '00'
        },
        p: 1,
        user_index: 0,
        show_animate: true,
        animationTranspond:{},
        award_bg:false
    },

    /**
     * 生命周期函数--监听页面加载
     */
    onLoad: function(options) {
        getApp().page.onLoad(this, options);
        
        if (typeof my === 'undefined') {
            var scene = decodeURIComponent(options.scene);
            if (typeof scene !== 'undefined') {
                var scene_obj = utils.scene_decode(scene);
                if (scene_obj.gid) {
                    options.id = scene_obj.gid;
                }
            }
        } else {
            if (getApp().query !== null) {
                var query = app.query;
                getApp().query = null;
                options.id = query.gid;
            }
        }
        this.getGoods(options);
    },

    getGoods: function(options) {
        var self = this;
        var id = options.id;
        console.log(options);
        getApp().core.showLoading({
            title: '加载中',
        });
        var self = this;
        getApp().request({
            url: getApp().api.lottery.goods,
            data: {
                id: id,
            },
            success: function(res) {
                if (res.code == 0) {
                    var detail = res.data.goods.detail;
                    WxParse.wxParse("detail", "html", detail, self);
                    self.setData(res.data);
                } else {
                    getApp().core.showModal({
                        title: '提示',
                        content: res.msg,
                        showCancel: false,
                        success: function(e) {
                            if (e.confirm) {
                                getApp().core.navigateBack({
                                    delta: -1
                                })
                            }
                        }
                    })
                }
            },
            complete: function(res) {
                getApp().core.hideLoading();
            }
        });
    },


    buyZero: function() {
        var self = this;
        var award_bg = self.data.award_bg ? false : true;
        self.setData({
            award_bg: award_bg,
        }) 

        var animation = getApp().core.createAnimation({
          duration: 1000,
            timingFunction: 'linear',
            transformOrigin: '50% 50%',
        })
        
        if(self.data.award_bg){
            animation.width('360rpx').height('314rpx').step();         
        }else {
            animation.scale(0,0).opacity(0).step();  
        }

        self.setData({
            animationTranspond: animation.export()
        });

        var circleCount = 0;
        lotteryInter = setInterval(function () {
            if (circleCount % 2 == 0) {
                animation.scale(0.9).opacity(1).step();
            } else {
                animation.scale(1).opacity(1).step();
            }

            self.setData({
                animationTranspond: animation.export()
            });

            circleCount++;
            if (circleCount == 500) {
                circleCount = 0;
            }
        }, 500)
    },

    submitTime:function() {
        var self = this;
        var animation = getApp().core.createAnimation({
            duration: 500,
            transformOrigin: '50% 50%',
        });
        var self = this;

        var circleCount = 0;
        lotteryInter = setInterval(function() {
            if (circleCount % 2 == 0) {
                animation.scale(2.3,2.3).opacity(1).step();
            } else {
                animation.scale(2.5,2.5).opacity(1).step();
            }

            self.setData({
                animationTranspond: animation.export()
            });

            circleCount++;
            if (circleCount == 500) {
                circleCount = 0;
            }
        }, 500)
    },

    submit:function(e) {
        var self = this;
        var formId = e.detail.formId;
     
        var lottery_id = e.currentTarget.dataset.lottery_id;

        getApp().core.navigateTo({
            url: "/lottery/detail/detail?lottery_id=" + lottery_id + "&form_id=" + formId,
        });
        clearInterval(lotteryInter);
        self.setData({
            award_bg:false,
        }) 

    },
    /**
     * 生命周期函数--监听页面显示
     */
    onShow: function() {
       
    },

    play: function(e) {
        var url = e.target.dataset.url; //获取视频链接
        this.setData({
            url: url,
            hide: '',
            show: true,
        });
        videoContext = getApp().core.createVideoContext('video');
        videoContext.play();
    },

    close: function(e) {
        if (e.target.id == 'video') {
            return true;
        }
        this.setData({
            hide: "hide",
            show: false
        });
        videoContext.pause();
    },

    onGoodsImageClick: function(e) {
        var self = this;
        var urls = [];
        var index = e.currentTarget.dataset.index;
        for (var i in self.data.goods.pic_list) {
            urls.push(self.data.goods.pic_list[i].pic_url);
        }
        getApp().core.previewImage({
            urls: urls, // 需要预览的图片http链接列表
            current: urls[index],
        });
    },

    hide: function(e) {
        if (e.detail.current == 0) {
            this.setData({
                img_hide: ""
            });
        } else {
            this.setData({
                img_hide: "hide"
            });
        }
    },

    buyNow: function(e) {
        var self = this;
        var cart_list = [];
        let cart_list_goods = {
            goods_id: self.data.goods.id,
            num: 1,
            attr: JSON.parse(self.data.lottery_info.attr),
        }
        cart_list.push(cart_list_goods)

        var mch_list = [];
        mch_list.push({
            mch_id: 0,
            goods_list: cart_list
        });

        getApp().core.navigateTo({
            url: '/pages/new-order-submit/new-order-submit?mch_list=' + JSON.stringify(mch_list),
        });
    },

    /**
     * 用户点击右上角分享
     */
    onShareAppMessage: function () {
        getApp().page.onShareAppMessage(this);
        let user_info = getApp().getUser();
        let id = this.data.lottery_info.id;
        var res = {
            path: "/lottery/goods/goods?id=" + id + "&user_id=" + user_info.id,
        };
        return res;
    },

    /**
    *  海报
    */
    showShareModal:function(){
        this.setData({
            share_modal_active: "active",
        });
    },

    shareModalClose:function(){
        this.setData({
            share_modal_active:'',
        })
    },

    getGoodsQrcode: function() {
        var self = this;
        self.setData({
            qrcode_active: "active",
            share_modal_active: "",
        });
        if (self.data.goods_qrcode) return true

        getApp().request({
            url: getApp().api.lottery.qrcode,
            data: {
                goods_id: self.data.lottery_info.id,
            },
            success: function(res) {
                if (res.code == 0) {
                    self.setData({
                        goods_qrcode: res.data.pic_url,
                    });
                }
                if (res.code == 1) {
                    self.goodsQrcodeClose();
                    getApp().core.showModal({
                        title: "提示",
                        content: res.msg,
                        showCancel: false,
                        success: function(res) {
                            if (res.confirm) {
                            }
                        }
                    });
                }
            },
        });
    },  

    qrcodeClick: function(e) {
        var src = e.currentTarget.dataset.src;
        getApp().core.previewImage({
            urls: [src],
        });
    },
    qrcodeClose: function() {
        var self = this; 
        self.setData({
            qrcode_active: "",
        });
    },

    goodsQrcodeClose: function() {
        var self = this;
        self.setData({
            goods_qrcode_active: "",
            no_scroll: false,
        });
    },

    saveQrcode: function() {
        var self = this;
        if (!getApp().core.saveImageToPhotosAlbum) {
            getApp().core.showModal({
                title: '提示',
                content: '当前版本过低，无法使用该功能，请升级到最新版本后重试。',
                showCancel: false,
            });
            return;
        }

        getApp().core.showLoading({
            title: "正在保存图片",
            mask: false,
        });

        getApp().core.downloadFile({
            url: self.data.goods_qrcode,
            success: function(e) {
                getApp().core.showLoading({
                    title: "正在保存图片",
                    mask: false,
                });
                getApp().core.saveImageToPhotosAlbum({
                    filePath: e.tempFilePath,
                    success: function() {
                        getApp().core.showModal({
                            title: '提示',
                            content: '商品海报保存成功',
                            showCancel: false,
                        });
                    },
                    fail: function(e) {
                        getApp().core.showModal({
                            title: '图片保存失败',
                            content: e.errMsg,
                            showCancel: false,
                        });
                    },
                    complete: function(e) {
                        getApp().core.hideLoading();
                    }
                });
            },
            fail: function(e) {
                getApp().core.showModal({
                    title: '图片下载失败',
                    content: e.errMsg + ";" + self.data.goods_qrcode,
                    showCancel: false,
                });
            },
            complete: function(e) {
                getApp().core.hideLoading();
            }
        });
    },

})