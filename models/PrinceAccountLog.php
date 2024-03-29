<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%prince_account_log}}".
 *
 * @property integer $id
 * @property integer $store_id
 * @property string $price
 * @property integer $type
 * @property string $desc
 * @property integer $addtime
 */
class PrinceAccountLog extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%prince_account_log}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['store_id', 'price', 'type', 'addtime'], 'required'],
            [['store_id', 'type', 'addtime'], 'integer'],
            [['price'], 'number'],
            [['desc'], 'string'],
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
            'price' => '金额',
            'type' => '类型：1=收入，2=支出',
            'desc' => 'Desc',
            'addtime' => 'Addtime',
        ];
    }
}
