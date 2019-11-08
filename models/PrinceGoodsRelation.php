<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%prince_goods_relation}}".
 *
 * @property integer $id
 * @property integer $store_id
 * @property integer $goods_id
 * @property integer $cloud_goods_id
 * @property integer $type
 */
class PrinceGoodsRelation extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%prince_goods_relation}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['store_id', 'goods_id', 'cloud_goods_id', 'type'], 'required'],
            [['store_id', 'goods_id', 'cloud_goods_id', 'type'], 'integer'],
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
            'goods_id' => 'Goods ID',
            'cloud_goods_id' => 'Cloud Goods ID',
            'type' => 'Type',
        ]; 
    }

}
