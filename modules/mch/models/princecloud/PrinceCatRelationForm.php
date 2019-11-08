<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/6/27
 * Time: 11:01
 */

namespace app\modules\mch\models\princecloud;

use app\models\PrinceCatRelation;
use yii\data\Pagination;
use app\modules\mch\models\MchModel;

class PrinceCatRelationForm extends MchModel
{
    public $model;

    public $store_id;
    public $cat_id;
    public $cloud_cat_id;
    public $type;

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


    /**
     * 编辑
     * @return array
     */
    public function save()
    {
        if ($this->validate()) {
            $this->model->attributes = $this->attributes;
            $this->model->save();
        } else {
            return $this->errorResponse;
        }
    }
}
