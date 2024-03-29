<?php
/**
 * Created by IntelliJ IDEA.
 * User: Administrator
 * Date: 2017/8/10
 * Time: 22:53
 */

namespace app\modules\api\models\miaosha;

use app\models\Express;
use app\models\Goods;
use app\models\MsGoods;
use app\models\MsOrder;
use app\models\MsOrderRefund;
use app\models\OrderDetail;
use app\models\OrderRefund;
use app\models\RefundAddress;
use app\modules\api\models\ApiModel;

class OrderRefundDetailForm extends ApiModel
{
    public $store_id;
    public $user_id;
    public $order_refund_id;

    public function rules()
    {
        return [
            [['order_refund_id'], 'required'],
        ];
    }

    public function search()
    {
        if (!$this->validate()) {
            return $this->errorResponse;
        }
        $order_refund = MsOrderRefund::find()->alias('or')
            ->leftJoin(['o' => MsOrder::tableName()], 'or.order_id=o.id')
            ->leftJoin(['g' => MsGoods::tableName()], 'o.goods_id=g.id')
            ->leftJoin(['r' => RefundAddress::tableName()],'or.address_id=r.id')
            ->where([
                'or.id' => $this->order_refund_id,
                'or.is_delete' => 0,
            ])
            ->select('or.id order_refund_id,g.id goods_id,g.name,o.num,o.total_price,o.attr,or.desc refund_desc,or.type refund_type,or.status refund_status,or.pic_list refund_pic_list,or.refund_price,or.is_agree,or.is_user_send,or.user_send_express,or.user_send_express_no,r.name as re_name,r.mobile as re_mobile,r.address as re_address,g.cover_pic goods_pic')
            ->asArray()->one();
        if (!$order_refund) {
            return [
                'code' => 1,
                'msg' => '售后单不存在'
            ];
        }
        $order_refund['attr'] = json_decode($order_refund['attr']);
        $order_refund['refund_pic_list'] = json_decode($order_refund['refund_pic_list']);
        $order_refund['express_list'] = Express::getExpressList();
        $order_refund['order_refund_status_bg'] = \Yii::$app->request->hostInfo . \Yii::$app->request->baseUrl . '/statics/images/order-refund-status-bg.png';
        return [
            'code' => 0,
            'msg' => 'success',
            'data' => $order_refund,
        ];
    }
}
