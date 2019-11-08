<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/6/27
 * Time: 11:01
 */

namespace app\modules\mch\models\princecloud;

use app\models\PrinceGoodsRelation;
use yii\data\Pagination;
use app\modules\mch\models\MchModel;

class PrinceGoodsRelationForm extends MchModel
{
    public $model;

    public $store_id;
    public $goods_id;
    public $cloud_goods_id;
    public $type;

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
