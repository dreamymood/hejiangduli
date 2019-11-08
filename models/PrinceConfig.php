<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%prince_config}}".
 *
 * @property integer $id
 * @property integer $store_id
 * @property string $code
 * @property string $value
 * @property string $codegroup
 */
class PrinceConfig extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%prince_config}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['store_id', 'code', 'value'], 'required'],
            [['store_id'], 'integer'],
            [['code'], 'string', 'max' => 32],
            [['value'], 'string'],//非常重要，否则不会过滤
            [['codegroup'], 'string', 'max' => 32],
            [['store_id', 'code'], 'unique', 'targetAttribute' => ['store_id', 'code'], 'message' => 'The combination of Store ID and Code has already been taken.'],
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
            'code' => 'Code',
            'value' => 'Value',
            'codegroup' => 'Code Group',
        ];
    }

}
