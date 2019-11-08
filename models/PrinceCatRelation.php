<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%prince_cat_relation}}".
 *
 * @property integer $id
 * @property integer $store_id
 * @property integer $cat_id
 * @property integer $cloud_cat_id
 * @property integer $type
 */
class PrinceCatRelation extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%prince_cat_relation}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['store_id', 'cat_id', 'cloud_cat_id', 'type'], 'required'],
            [['store_id', 'cat_id', 'cloud_cat_id', 'type'], 'integer'],
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
            'cat_id' => 'Cat ID',
            'cloud_cat_id' => 'Parent Cat ID',
            'type' => 'Type',
        ]; 
    }

}
