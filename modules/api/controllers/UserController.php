<?php
/**
 * Created by IntelliJ IDEA.
 * User: Administrator
 * Date: 2017/7/18
 * Time: 11:28
 */

namespace app\modules\api\controllers;

use app\opening\ApiResponse;
use app\opening\BaseApiResponse;
use app\models\Address;
use app\models\FormId;
use app\models\Level;
use app\models\Option;
use app\models\Order;
use app\models\Setting;
use app\models\Share;
use app\models\Store;
use app\models\User;
use app\models\UserAuthLogin;
use app\models\UserCard;
use app\models\UserCenterForm;
use app\models\UserCenterMenu;
use app\models\UserFormId;
use app\modules\api\behaviors\LoginBehavior;
use app\modules\api\models\AddressDeleteForm;
use app\modules\api\models\AddressSaveForm;
use app\modules\api\models\AddressSetDefaultForm;
use app\modules\api\models\AddWechatAddressForm;
use app\modules\api\models\AuthorizationBindForm;
use app\modules\api\models\CardListForm;
use app\modules\api\models\FavoriteAddForm;
use app\modules\api\models\FavoriteListForm;
use app\modules\api\models\FavoriteRemoveForm;
use app\modules\api\models\OrderListForm;
use app\modules\api\models\TopicFavoriteForm;
use app\modules\api\models\TopicFavoriteListForm;
use app\modules\api\models\WechatDistrictForm;
use app\modules\api\models\QrcodeForm;
use app\modules\api\models\OrderMemberForm;
use app\models\SmsSetting;
use app\modules\api\models\UserForm;
use app\utils\Sms;

class UserController extends Controller
{
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'login' => [
                'class' => LoginBehavior::className(),
            ],
        ]);
    }

    //个人中心
    public function actionIndex()
    {
        $order_count = OrderListForm::getCountData($this->store->id, \Yii::$app->user->id);
        $share_setting = Setting::find()->where(['store_id' => $this->store->id])->asArray()->one();
        $parent = User::findOne(\Yii::$app->user->identity->parent_id);
        $share = Share::findOne(['user_id' => \Yii::$app->user->identity->parent_id]);

        $user = User::findOne(['id' => \Yii::$app->user->identity->id]);
        $level = $user->level;


        $now_level = Level::findOne(['store_id' => $this->store->id, 'level' => $level, 'is_delete' => 0]);
        $user_info = [
            'nickname' => \Yii::$app->user->identity->nickname,
            'binding' => $user->binding,
            'avatar_url' => \Yii::$app->user->identity->avatar_url,
            'is_distributor' => \Yii::$app->user->identity->is_distributor,
//            'parent' => $share ? $share->name : ($parent ? $parent->nickname : '总店'),
            'parent' => $share ? ($share->name ? $share->name : $parent->nickname) : "总店",
            'id' => \Yii::$app->user->identity->id,
            'is_clerk' => \Yii::$app->user->identity->is_clerk,
            'level' => $level,
            'level_name' => $now_level ? $now_level->name : "普通用户",
            'integral' => \Yii::$app->user->identity->integral,
            'money' => \Yii::$app->user->identity->money,
            'blacklist' => \Yii::$app->user->identity->blacklist,
        ];
        $next_level = Level::find()->where(['store_id' => $this->store->id, 'is_delete' => 0, 'status' => 1])
            ->andWhere(['>', 'level', $level])->orderBy(['level' => SORT_ASC, 'id' => SORT_DESC])->asArray()->one();

        //余额功能配置
        $balance = Option::get('re_setting', $this->store->id, 'app');
        $balance = json_decode($balance, true);
        //我的钱包 选项
        $wallet['integral'] = 1;
        if ($balance) {
            $wallet['re'] = $balance['status'];
        }

        /* 旧版的菜单列表先保留以兼容旧版，后期去掉 */
        $user_center_menu = new UserCenterMenu();
        $user_center_menu->store_id = $this->store->id;

        $user_center_form = new UserCenterForm();
        $user_center_form->store_id = $this->store->id;
        $user_center_form->user_id = $user->id;
        $user_center_form->_platform =\Yii::$app->request->get('_platform');
        $user_center_data = $user_center_form->getData();
        $user_center_data = $user_center_data['data'];
        $wallet['is_wallet'] = $user_center_data['is_wallet'];
        if($user_center_data['copyright']['open_type'] == 'wxapp'){
            $url = $user_center_data['copyright']['url'];
            preg_match('/^[^\?+]\?([\w|\W]+)=([\w|\W]*?)&([\w|\W]+)=([\w|\W]*?)$/', $url, $res);
            $user_center_data['copyright']['appId'] = $res[2];
            $user_center_data['copyright']['path'] = urldecode($res[4]);
        }
        return new BaseApiResponse([
            'code' => 0,
            'msg' => 'success',
            'data' => [
                'order_count' => $order_count,
                'show_customer_service' => $this->store->show_customer_service,
                'contact_tel' => $this->store->contact_tel,
                'share_setting' => $share_setting,
                'user_info' => $user_info,
                'next_level' => $next_level,
                'menu_list' => $user_center_menu->getMenuList(),
                'user_center_bg' => $user_center_data['user_center_bg'],
                'orders' => $user_center_data['orders'],
                'wallets' => $user_center_data['wallets'],
                'menus' => $user_center_data['menus'],
                'copyright' => $user_center_data['copyright'],
                'wallet' => $wallet,
                'style' => [
                    'menu' => $user_center_data['menu_style'],
                    'top' => $user_center_data['top_style'],
                ],
                'setting' => [
                    'is_order' => $user_center_data['is_order']
                ]
            ],
        ]);
    }


    //    短信验证是否开启
    public function actionSmsSetting()
    {
        $option = Option::get('manual_mobile_auth', $this->store->id, 'admin');
        if ($option == 'on') {
            return new BaseApiResponse([
                'code' => 0,
                'data' => 1
            ]);
        } else {
            return new BaseApiResponse([
                'code' => 1,
                'data' => 0
            ]);
        }
    }

    //    绑定手机号
    public function actionUserBinding()
    {
        $form = new UserForm();
        $form->attributes = \Yii::$app->request->post();
        $form->wechat_app = $this->wechat_app;
        $form->user_id = \Yii::$app->user->identity->id;
        $form->store_id = $this->store->id;
        return new BaseApiResponse($form->binding());
    }

    //    短信验证
    public function actionUserHandBinding()
    {
        $form = new Sms();
        $form->attributes = \Yii::$app->request->post();
        $code = mt_rand(0, 999999);
        return new BaseApiResponse($form->send_text($this->store->id, $code, $form->attributes['content']));
    }

    // 授权手机号确认
    public function actionUserEmpower()
    {
        $form = new UserForm();
        $form->attributes = \Yii::$app->request->post();
        $form->user_id = \Yii::$app->user->identity->id;
        $form->store_id = $this->store->id;
        return new BaseApiResponse($form->userEmpower());
    }

    //收货地址列表
    public function actionAddressList()
    {
        $list = Address::find()->select('id,name,mobile,province_id,province,city_id,city,district_id,district,detail,is_default')->where([
            'store_id' => $this->store->id,
            'user_id' => \Yii::$app->user->id,
            'is_delete' => 0,
        ])->orderBy('is_default DESC,addtime DESC')->asArray()->all();
        foreach ($list as $i => $item) {
            $list[$i]['address'] = $item['province'] . $item['city'] . $item['district'] . $item['detail'];
        }
        return new BaseApiResponse((object)[
            'code' => 0,
            'msg' => 'success',
            'data' => [
                'list' => $list,
            ],
        ]);
    }

    /**
     * 会员支付
     */
    public function actionSubmitMember()
    {
        $form = new OrderMemberForm();
        $form->store_id = $this->store->id;
        $form->user = \Yii::$app->user->identity;
        $form->attributes = \Yii::$app->request->post();
        return new BaseApiResponse($form->save());
    }

    //收货地址详情
    public function actionAddressDetail()
    {
        $address = Address::find()->select('id,name,mobile,province_id,province,city_id,city,district_id,district,detail,is_default')->where([
            'store_id' => $this->store->id,
            'user_id' => \Yii::$app->user->id,
            'is_delete' => 0,
            'id' => \Yii::$app->request->get('id'),
        ])->one();
        if (!$address) {
            return new BaseApiResponse([
                'code' => 1,
                'msg' => '收货地址不存在',
            ]);
        }
        return new BaseApiResponse((object)[
            'code' => 0,
            'msg' => 'success',
            'data' => [
                'address_id' => $address->id,
                'name' => $address->name,
                'mobile' => $address->mobile,
                'district' => [
                    'province' => [
                        'id' => $address->province_id,
                        'name' => $address->province,
                    ],
                    'city' => [
                        'id' => $address->city_id,
                        'name' => $address->city,
                    ],
                    'district' => [
                        'id' => $address->district_id,
                        'name' => $address->district,
                    ],
                ],
                'detail' => $address->detail,
            ],
        ]);
    }

    //保存收货地址
    public function actionAddressSave()
    {
        $form = new AddressSaveForm();
        $form->attributes = \Yii::$app->request->post();
        $form->store_id = $this->store->id;
        $form->user_id = \Yii::$app->user->id;
        return new BaseApiResponse($form->save());
    }

    //设为默认收货地址
    public function actionAddressSetDefault()
    {
        $form = new AddressSetDefaultForm();
        $form->attributes = \Yii::$app->request->get();
        $form->store_id = $this->store->id;
        $form->user_id = \Yii::$app->user->id;
        return new BaseApiResponse($form->save());
    }

    //删除收货地址
    public function actionAddressDelete()
    {
        $form = new AddressDeleteForm();
        $form->attributes = \Yii::$app->request->get();
        $form->store_id = $this->store->id;
        $form->user_id = \Yii::$app->user->id;
        return new BaseApiResponse($form->save());
    }


    //保存用户的form id
    public function actionSaveFormId()
    {
        if (!\Yii::$app->user->isGuest) {
            FormId::addFormId([
                'store_id' => $this->store->id,
                'user_id' => \Yii::$app->user->id,
                'wechat_open_id' => \Yii::$app->user->identity->wechat_open_id,
                'form_id' => \Yii::$app->request->get('form_id'),
                'type' => 'form_id',
            ]);
            UserFormId::saveFormId([
                'user_id' => \Yii::$app->user->id,
                'form_id' => \Yii::$app->request->get('form_id'),
            ]);
        }
        return new ApiResponse(0);
    }

    //添加商品到我的喜欢
    public function actionFavoriteAdd()
    {
        $form = new FavoriteAddForm();
        $form->attributes = \Yii::$app->request->post();
        $form->store_id = $this->store->id;
        $form->user_id = \Yii::$app->user->id;
        return new BaseApiResponse($form->save());
    }

    //取消喜欢商品
    public function actionFavoriteRemove()
    {
        $form = new FavoriteRemoveForm();
        $form->attributes = \Yii::$app->request->post();
        $form->store_id = $this->store->id;
        $form->user_id = \Yii::$app->user->id;
        return new BaseApiResponse($form->save());
    }

    //喜欢的商品列表
    public function actionFavoriteList()
    {
        $form = new FavoriteListForm();
        $form->attributes = \Yii::$app->request->get();
        $form->user_id = \Yii::$app->user->id;
        return new BaseApiResponse($form->search());
    }

    //根据微信地址获取数据库省市区数据
    public function actionWechatDistrict()
    {
        $form = new WechatDistrictForm();
        $form->attributes = \Yii::$app->request->get();
        return new BaseApiResponse($form->search());
    }

    //添加微信获取的地址
    public function actionAddWechatAddress()
    {
        $form = new AddWechatAddressForm();
        $form->attributes = \Yii::$app->request->post();
        $form->store_id = $this->store->id;
        $form->user_id = \Yii::$app->user->id;
        return new BaseApiResponse($form->save());
    }

    //收藏|取消收藏专题
    public function actionTopicFavorite()
    {
        $form = new TopicFavoriteForm();
        $form->attributes = \Yii::$app->request->get();
        $form->store_id = $this->store->id;
        $form->user_id = \Yii::$app->user->id;
        return new BaseApiResponse($form->save());
    }

    //收藏专题列表
    public function actionTopicFavoriteList()
    {
        $form = new TopicFavoriteListForm();
        $form->attributes = \Yii::$app->request->get();
        $form->store_id = $this->store->id;
        $form->user_id = \Yii::$app->user->id;
        return new BaseApiResponse($form->search());
    }

    //会员权益
    public function actionMember()
    {
        $level = \Yii::$app->user->identity->level;
        $money = \Yii::$app->user->identity->money;


        $list = Level::find()->select(['id', 'image', 'level', 'name', 'price', 'buy_prompt', 'detail'])->where(['store_id' => $this->store->id, 'is_delete' => 0])->andWhere(['<>', 'price', '0'])->andWhere(['>', 'level', $level])->orderBy('level asc')->asArray()->all();

        $now_level = Level::find()->where(['store_id' => $this->store->id, 'level' => $level, 'is_delete' => 0])->asArray()->one();
        if($now_level && $now_level['synopsis']) {
            $synopsis = json_decode($now_level['synopsis'],true);
            $now_level['synopsis'] = array_chunk($synopsis,4);
        }else{
            $now_level['synopsis'] = [];
        }

        $user_info = [
            'nickname' => \Yii::$app->user->identity->nickname,
            'avatar_url' => \Yii::$app->user->identity->avatar_url,
            'id' => \Yii::$app->user->identity->id,
            'level' => $level,
            'level_name' => $now_level ? $now_level['name'] : "普通用户"
        ];
        $time = time();
        $store = $this->store;
        $sale_time = $time - ($store->after_sale_time * 86400);
        $next_level = Level::find()->where(['store_id' => $this->store->id, 'is_delete' => 0, 'status' => 1])
            ->andWhere(['>', 'level', $level])->andWhere(['>', 'price', 0])->orderBy(['level' => SORT_ASC, 'id' => SORT_DESC])->asArray()->one();
        $order_money = Order::find()->where(['store_id' => $this->store->id, 'user_id' => \Yii::$app->user->identity->id, 'is_delete' => 0])
            ->andWhere(['is_pay' => 1, 'is_confirm' => 1])->andWhere(['<=', 'confirm_time', $sale_time])->select([
                'sum(pay_price)'
            ])->scalar();
        $percent = 100;
        $s_money = 0;
        $order_money = $order_money ? $order_money : 0;
        if ($next_level) {
            if ($next_level['money'] != 0) {
                $percent = round($order_money / $next_level['money'] * 100, 2);
            }
            $s_money = round($next_level['money'] - $order_money, 2);
        }
        $content = $store->member_content;
        return new BaseApiResponse([
            'code' => 0,
            'msg' => 'success',
            'data' => [
                'user_info' => $user_info,
                'next_level' => $next_level,
                'now_level' => $now_level,
                'order_money' => $order_money,
                'percent' => $percent,
                's_money' => $s_money,
                'money' => $money,
                'content' => $content,
                'list' => $list,
            ],
        ]);
    }

    /**
     * 用户卡券
     */
    public function actionCard()
    {
        $form = new CardListForm();
        $form->store_id = $this->store->id;
        $form->user_id = \Yii::$app->user->identity->id;
        $form->attributes = \Yii::$app->request->get();
        return new BaseApiResponse($form->search());
    }

    /**
     * 卡券二维码
     */
    public function actionCardQrcode()
    {
        $user_card_id = \Yii::$app->request->get('user_card_id');
        $form = new QrcodeForm();
        $form->page = "pages/card-clerk/card-clerk";
        $form->width = 100;
        if(\Yii::$app->fromAlipayApp()){
            $form->scene = "user_card_id={$user_card_id}";
        }else{
            $form->scene = "{$user_card_id}";
        }
        $form->store = $this->store;
        $res = $form->getQrcode();
        return new BaseApiResponse($res);
    }

    /**
     * 卡券核销
     */
    public function actionCardClerk()
    {
        $user_card_id = \Yii::$app->request->get('user_card_id');
        if (\Yii::$app->cache->get('card_id_' . $user_card_id)) {
            return new BaseApiResponse([
                'code' => 1,
                'msg' => '卡券核销中，请稍后重试'
            ]);
        }
        \Yii::$app->cache->set('card_id_' . $user_card_id, true);
        $user_card = UserCard::findOne(['id' => $user_card_id]);
        if ($user_card->is_use != 0) {
            \Yii::$app->cache->set('card_id_' . $user_card_id, false);
            return new BaseApiResponse([
                'code' => 1,
                'msg' => '卡券已核销'
            ]);
        }
        $user = \Yii::$app->user->identity;
        if ($user->is_clerk != 1) {
            \Yii::$app->cache->set('card_id_' . $user_card_id, false);
            return new BaseApiResponse([
                'code' => 1,
                'msg' => '不是核销员禁止核销'
            ]);
        }
        $user_card->clerk_id = $user->id;
        $user_card->shop_id = $user->shop_id;
        $user_card->clerk_time = time();
        $user_card->is_use = 1;
        if ($user_card->save()) {
            \Yii::$app->cache->set('card_id_' . $user_card_id, false);
            return new BaseApiResponse([
                'code' => 0,
                'msg' => '核销成功'
            ]);
        } else {
            \Yii::$app->cache->set('card_id_' . $user_card_id, false);
            return new BaseApiResponse([
                'code' => 1,
                'msg' => '核销失败'
            ]);
        }
    }

    /**
     * 卡卷详情
     * @param  user_card_id
     * @return $list
     */
    public function actionCardDetail()
    {
        $form = new CardListForm();
        $form->store_id = $this->store->id;
        $form->user_id = \Yii::$app->user->identity->id;
        $form->user_card_id = \Yii::$app->request->get('user_card_id');
   
        return new BaseApiResponse($form->detail());
    }

    public function actionWebLogin($token)
    {
        $m = UserAuthLogin::findOne([
            'token' => $token,
            'store_id' => $this->store->id,
        ]);
        if (!$m) {
            return new BaseApiResponse([
                'code' => 1,
                'msg' => '错误的小程序码，请刷新网页重试'
            ]);
        }
        if ($m->is_pass != 0) {
            return new BaseApiResponse([
                'code' => 1,
                'msg' => '您已处理过，请勿重复提交'
            ]);
        }
        $m->user_id = \Yii::$app->user->id;
        $m->is_pass = 1;
        $m->save();
        return new BaseApiResponse([
            'code' => 0,
            'msg' => '已确认登录'
        ]);
    }

    /**
     * 微信公众号授权绑定
     */
    public function actionAuthorizationBind()
    {
        $model = new AuthorizationBindForm();
        $data = $model->bind();

        return new BaseApiResponse($data);
    }

    //检测是否绑定公众号
    public function actionCheckBind()
    {
        $model = new AuthorizationBindForm();
        $data = $model->checkBind();

        return new BaseApiResponse($data);
    }
}
