<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%lottery_log}}".
 *
 * @property integer $id
 * @property integer $store_id
 * @property integer $user_id
 * @property integer $lottery_id
 * @property integer $addtime
 * @property integer $status
 * @property integer $goods_id
 * @property string $attr
 * @property integer $raffle_time
 * @property integer $order_id
 * @property integer $obtain_time
 * @property string $form_id
 */
class LotteryLog extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%lottery_log}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['store_id', 'user_id', 'lottery_id', 'addtime', 'status', 'goods_id', 'raffle_time', 'order_id', 'obtain_time'], 'integer'],
            [['attr'], 'required'],
            [['attr'], 'string'],
            [['form_id'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'store_id' => 'Store ID',
            'user_id' => '用户ID',
            'lottery_id' => 'Lottery ID',
            'addtime' => 'Addtime',
            'status' => '0待开奖 1未中奖 2中奖3已领取',
            'goods_id' => '商品id',
            'attr' => 'Attr',
            'raffle_time' => '领取时间',
            'order_id' => '订单ID',
            'obtain_time' => '获取时间',
            'form_id' => 'Form ID',
        ];
    }

    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    public function getLottery()
    {
        return $this->hasOne(LotteryGoods::className(), ['id' => 'lottery_id']);
    }


    public function getGift()
    {
        return $this->hasOne(Goods::className(), ['id' => 'goods_id']); 
    }
}
