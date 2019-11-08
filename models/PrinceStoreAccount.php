<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%prince_store_account}}".
 *
 * @property string $id
 * @property integer $store_id
 * @property string $money
 * @property integer $user_id
 * @property string $wechat_name
 */
class PrinceStoreAccount extends \yii\db\ActiveRecord
{

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%prince_store_account}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['store_id'],'required'],
            [['store_id'], 'integer'],
            [['money'], 'number'],
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
            'money' => 'Money',
            'user_id' => 'User ID',
            'wechat_name' => 'Wechat Name',

        ];
    }

    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

}
