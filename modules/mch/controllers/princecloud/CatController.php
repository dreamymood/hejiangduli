<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/6/27
 * Time: 10:56
 */

namespace app\modules\mch\controllers\princecloud;

use app\models\Attr;
use app\models\AttrGroup;
use app\models\Card;
use app\models\Cat;
use app\models\Goods;
use app\models\GoodsCat;
use app\models\GoodsPic;
use app\models\PostageRules;
use app\models\PrinceGoodsRelation;
use app\models\PrinceCatRelation;
use app\models\PrinceConfig;
use app\modules\mch\events\goods\BaseAddGoodsEvent;
use app\modules\mch\models\CopyForm;
use app\modules\mch\models\goods\Taobaocsv;
use app\modules\mch\models\GoodsQrcodeForm;
use app\modules\mch\models\princecloud\GoodsSearchForm;
use app\modules\mch\models\SetGoodsSortForm;
use app\modules\mch\models\princecloud\GoodsForm;
use app\modules\mch\models\princecloud\CatForm;
use app\modules\mch\models\princecloud\PrinceCatRelationForm;
use app\modules\mch\models\princecloud\PrinceGoodsRelationForm;
use Opening\Event\EventArgument;
use yii\data\Pagination;
use yii\web\HttpException;
use app\modules\mch\controllers\Controller;


/**
 * Class CatController
 * @package app\modules\mch\controllers\princecloud
 * 分类
 */
class CatController extends Controller
{



    /**
     * 分类列表
     * @return string
     */
    public function actionIndex($keyword = null, $status = null)
    {
		$config = PrinceConfig::findOne(['code' => 'cloud_store_id', 'store_id' =>0]);
		$cloud_store_id=$config->value;
        $cat_list = Cat::find()->where(['store_id' => $cloud_store_id, 'is_delete' => 0])->all();

        return $this->render('cat', [
            'cat_list' => $cat_list,
        ]);
    }


    /**
     * 批量采集
     */
    public function actionBatch()
    {

        $get = \Yii::$app->request->get();
		$config = PrinceConfig::findOne(['code' => 'cloud_store_id', 'store_id' =>0]);
		$cloud_store_id=$config->value;
		if($cloud_store_id==$this->store->id){
            return [
                'code' => 1,
                'msg' => '当前商城是云库商城，不可以采集分类',
            ];
		}
		
        if (empty($get['cat_group'])) {
            return [
                'code' => 1,
                'msg' => '请选择商品',
            ];
        }

		$cat_group=$get['cat_group'];
        foreach ($cat_group as $index => $value) {
			$this->_cat_copy($cloud_store_id,$value['id'],0);
        }
        return [
            'code' => 0,
            'msg' => '操作完成',
        ];
    }


	

    /**
     * 采集分类数据
     */
    private function _cat_copy($cloud_store_id=0,$cat_id=0,$type=0)
    {
			$cat = PrinceCatRelation::findOne(['cloud_cat_id' => $cat_id, 'type' => $type, 'store_id' => $this->store->id]);
			if (!$cat ) {
        		$cat = new Cat();
        		$model = Cat::find()->where(['id' => $cat_id,'store_id' => $cloud_store_id])->asArray()->one();
				if($model['parent_id']>0){
					$new_parent_id=$this->_cat_copy($cloud_store_id,$model['parent_id'],$type);
					$model['parent_id']=$new_parent_id;
				}
				$model['store_id']=$this->store->id;
				$form = new CatForm();
				$form->attributes = $model;
				$form->cat = $cat;
				$new_id=$form->save();//插入分类表
				if($new_id){
					//插入关系表
					$prince_cat_relation = new PrinceCatRelation();
					$newform = new PrinceCatRelationForm();
					$model=array();
					$model['store_id']=$this->store->id;
					$model['cat_id']=$new_id;
					$model['cloud_cat_id']=$cat_id;
					$model['type']=$type;
					$newform->attributes = $model;
					$newform->model = $prince_cat_relation;
					$newform->save();
				}
				return $new_id;
			}else{
				return $cat->cat_id;
			}
	}
	
}