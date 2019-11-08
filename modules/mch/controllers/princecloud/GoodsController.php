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
 * Class GoodController
 * @package app\modules\mch\controllers\princecloud
 * 商品
 */
class GoodsController extends Controller
{



    /**
     * 商品管理
     * @return string
     */
    public function actionGoods($keyword = null, $status = null)
    {
        $form = new GoodsSearchForm();
		$config = PrinceConfig::findOne(['code' => 'cloud_store_id', 'store_id' =>0]);
		$cloud_store_id=$config->value;
		$form->cloud_store_id=$cloud_store_id;

        $form->store = $this->store;
        $form->keyword = $keyword;
        $form->status = $status;
        $form->plugin = get_plugin_type();
        $res = $form->getList();
        $newcatList = Cat::find()->where(['store_id' => $this->store->id, 'is_delete' => 0])->all();

        return $this->render('goods', [
            'list' => $res['list'],
            'pagination' => $res['pagination'],
            'cat_list' => $res['cat_list'],
            'new_cat_list' => $newcatList,
        ]);
    }


    /**
     * 商品修改
     * @param int $id
     * @return string
     */
    public function actionGoodsEdit($id = 0)
    {
        $goods = Goods::findOne(['id' => $id, 'store_id' => $this->store->id, 'mch_id' => 0]);
        if (!$goods) {
            $goods = new Goods();
        }
        $form = new GoodsForm();
        if (\Yii::$app->request->isPost) {
            $model = \Yii::$app->request->post('model');
            if ($model['quick_purchase'] == 0) {
                $model['hot_cakes'] = 0;
            }
            $model['store_id'] = $this->store->id;
            $form->attributes = $model;
            $form->attr = \Yii::$app->request->post('attr');
            $form->goods_card = \Yii::$app->request->post('goods_card');
            $form->full_cut = \Yii::$app->request->post('full_cut');
            $form->integral = \Yii::$app->request->post('integral');
            $form->goods = $goods;
            $form->plugins = \Yii::$app->request->post('plugins');
            return $form->save();
        }

        $searchForm = new GoodsSearchForm();
        $searchForm->goods = $goods;
        $searchForm->store = $this->store;
        $list = $searchForm->search();

        $args = new EventArgument();
        $args['goods'] = $goods;
        \Yii::$app->eventDispatcher->dispatch(new BaseAddGoodsEvent(),$args);
        $plugins = $args->getResults();

        return $this->render('goods-edit', [
            'goods' => $list['goods'],
            'cat_list' => $list['cat_list'],
            'postageRiles' => $list['postageRiles'],
            'card_list' => \Yii::$app->serializer->encode($list['card_list']),
            'goods_card_list' => \Yii::$app->serializer->encode($list['goods_card_list']),
            'goods_cat_list' => \Yii::$app->serializer->encode($list['goods_cat_list']),
            'plugins'=>$plugins
        ]);
    }


    /**
     * 批量采集
     */
    public function actionBatchCopy()
    {

        $get = \Yii::$app->request->get();
        $cat_id = $get['cat_id'] ?: 0;
		$config = PrinceConfig::findOne(['code' => 'cloud_store_id', 'store_id' =>0]);
		$cloud_store_id=$config->value;
		if($cloud_store_id==$this->store->id){
            return [
                'code' => 1,
                'msg' => '当前商城是云库商城，不可以采集商品',
            ];
		}
		
        if (empty($get['goods_group'])) {
            return [
                'code' => 1,
                'msg' => '请选择商品',
            ];
        }

		$goods_group=$get['goods_group'];
        foreach ($goods_group as $val) {
			$this->_goods_cats_copy($cat_id,$val,$cloud_store_id,0);
			
        }
        return [
            'code' => 0,
            'msg' => '操作完成',
        ];
    }




    /**
     * 采集商品数据
     */
    private function _goods_cats_copy($cat_id=0,$goods_id = 0,$cloud_store_id=0,$type=0)
    {
		$goods = PrinceGoodsRelation::findOne(['cloud_goods_id' => $goods_id, 'type' => $type, 'store_id' => $this->store->id]);
        if (!$goods) {
            //分类
			if($cat_id==0){
				//复制分类
				$cat_ids = GoodsCat::find()->select('cat_id')->andWhere(['goods_id' => $goods_id, 'is_delete' => 0])->asArray()->column();
				$new_cat_ids=array();
				if(count($cat_ids)>0 ){
					foreach ($cat_ids as $val) {
						$new_cat_id=$this->_cat_copy($cloud_store_id,$val,$type);
						array_push($new_cat_ids,$new_cat_id);
						
					}
				}
			}else{
				$new_cat_ids=array($cat_id);
			}
			//复制商品
			$this->_goods_copy($cloud_store_id,$goods_id,$new_cat_ids,$type=0);
        }
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
	
    /**
     * 采集商品数据
     */
    private function _goods_copy($cloud_store_id=0,$goods_id=0,$new_cat_ids=array(),$type=0)
    {
			$config = PrinceConfig::findOne(['code' => 'price_rate', 'store_id' => $this->store->id]);
			$price_rate=$config->value;
			$price_rate=$price_rate>0?$price_rate:1;
		
			$goods = new Goods();
			$parent_goods = Goods::findOne(['id' => $goods_id,'store_id' => $cloud_store_id]);
			$model = Goods::find()->where(['id' => $goods_id,'store_id' => $cloud_store_id])->asArray()->one();
			$goodsPic = GoodsPic::find()->select('pic_url')->andWhere(['goods_id' => $goods_id, 'is_delete' => 0])->asArray()->column();
			$form = new GoodsForm();
            $model['store_id'] = $this->store->id;
			if(count($new_cat_ids)>0){
            	$model['cat_id'] =$new_cat_ids;
			}
			$model['price'] =$model['price']*$price_rate;
            $form->attributes = $model;
            $form->goods_pic_list = $goodsPic;
            $form->full_cut = \Yii::$app->serializer->decode($model['full_cut']);
            $form->integral = \Yii::$app->serializer->decode($model['integral']);
            $form->attr =$parent_goods->getCheckedAttrData();

            //$form->goods_card = \Yii::$app->request->post('goods_card');
            //$form->plugins = \Yii::$app->request->post('plugins');
            $form->goods = $goods;
            $res=$form->save();//插入商品表
			$new_goods_id=$res['goods_id'];
			if($new_goods_id){
				      
				//插入关系表
				$prince_goods_relation = new PrinceGoodsRelation();
				$newform = new PrinceGoodsRelationForm();
				$model=array();
				$model['store_id']=$this->store->id;
				$model['goods_id']=$new_goods_id;
				$model['cloud_goods_id']=$goods_id;
				$model['type']=$type;
				$newform->attributes = $model;
				$newform->model = $prince_goods_relation;
				$newform->save();
			}
	}
	
}