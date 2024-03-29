<?php
namespace app\modules\mch\controllers\lottery;

use app\models\PostageRules;
use app\models\Banner;
use app\modules\mch\models\BannerForm;
use app\modules\mch\models\lottery\LotteryGoodsForm;
use app\modules\mch\models\lottery\LotteryReserveForm;
use app\models\LotteryGoods;
use app\models\Goods;
use app\models\AttrGroup;
use app\models\Attr;
use app\models\LotteryLog;
use yii\data\Pagination;
use app\models\LotteryReserve;
use app\models\User;
class DefaultController extends Controller
{

    //奖品列表
    public function actionGoods()
    {
        $form = new LotteryGoodsForm();
        $form->store_id = $this->store->id;
        $list = $form->search();
        return $this->render('goods',$list);
    }

    //删除
    public function actionGoodsDestroy($id)
    {
        $model = LotteryGoods::findOne([
            'store_id' => $this->store->id,
            'id' => $id
        ]);
        if ($model) {
            $model->is_delete = 1;
            $model->save();
        }
        return [
            'code' => 0,
            'msg' => '操作成功',
        ];
    }

     //修改库存
     public function actionEdit()
     {
  
        $post = \Yii::$app->request->post();
        $form = LotteryGoods::findOne([
            'store_id' => $this->store->id,
            'id' => $post['id'] 
        ]);
        if(!empty($form)){
            if($post['stock']!==null){
               $form->stock = $post['stock']; 
            }
            if($post['status']!==null){
                $form->status = $post['status']; 
            }
            $form->update_time = time();
            if($form->save()){
                return [
                    'code' => 0,
                    'msg' => '保存成功',
                ];
            }else{ 
                return (new Model())->getErrorResponse($form);
            }
        }
  
     }

    //
    public function actionDetail($id = null){
        $goods_list = LotteryGoods::find()
            ->where([
                'store_id' => $this->store->id,
                'is_delete' => 0,
                'id' =>$id
            ])->with(['goods' => function ($query) {
                $query->where([
                    'store_id' => $this->store->id,
                    'is_delete' => 0
                ]);
            }])->one();

        if (\Yii::$app->request->isPost) {

            $post = \Yii::$app->request->Post();
            $form = new LotteryReserveForm();
            $form->attributes = \Yii::$app->request->post();
            $form->store_id = $this->store->id;
            $form->lottery_id = $goods_list->id;
            return $form->save();
        }

        //获奖记录
            $user_list = LotteryReserve::find()->alias('l')
                ->select('l.*,u.id as uid,u.nickname,u.avatar_url')->where([
                    'l.store_id' => $this->store->id,
                    'l.lottery_id' => $id,
                ])->leftJoin(['u' => User::tableName()], 'l.user_id=u.id')->asArray()->all();
            $num = LotteryLog::find()->where([
                    'store_id' => $this->store->id,
                    'lottery_id' => $id,
                ])->count();
        return $this->render('detail', [
            'goods_list' => $goods_list,
            'user_list' => $user_list,
            'num' => $num,
        ]);
    }

    public function actionDeleteLog(){
        $log_id = \Yii::$app->request->get()['log_id'];
        $lottery_id = \Yii::$app->request->get()['lottery_id'];

        $data = LotteryGoods::findOne([
                'id' => $lottery_id,
                'store_id' => $this->store->id,
                'is_delete' => 0,
            ]);
        if (!$data) {
            \Yii::$app->response->redirect(\Yii::$app->request->referrer)->send(); //
            return;
        };

        $reserve = LotteryReserve::find()->where(['store_id' => $this->store->id,'id' => $log_id])->one();

        if ($reserve->delete()) {
            return [
                'code' => 0,
            ];
        } else {
            return [
                'code' => 1,
            ];
        }
    }

    //编辑
    public function actionGoodsEdit($id = null)
    {
         $query = LotteryGoods::find()
            ->where([
                'store_id' => $this->store->id,
                'is_delete' => 0,
                'id' => $id
            ])->with(['goods' => function ($query) {
                $query->where([
                    'store_id' => $this->store->id,
                    'is_delete' => 0
                ]);
            }]);
        $list = $query->asArray()->one();
        if (!$model) {
            $model = new LotteryGoods();
        }
        if (\Yii::$app->request->isPost) {
            $post = json_decode(\Yii::$app->request->post()['data'],true)['d'];
            $post['start_time'] = strtotime($post['start_time']);
            $post['end_time'] = strtotime($post['end_time']);
            $post['attr'] = \Yii::$app->serializer->encode($post['attr']);
            $form = new LotteryGoodsForm();
            $form->attributes = $post;
            $form->store_id = $this->store->id;
            $form->model = $model;

            return $form->save();

        }else{
            if($list) {
                $list['attr'] = json_decode($list['attr'],true);
                $attrs = $this->actionAttr($list['goods_id'])['data']['attr'];
                $list['goods_name'] = $list['goods']['name'];      
                $list['start_time'] = date('Y-m-d H:i',$list['start_time']);
                $list['end_time'] = date('Y-m-d H:i',$list['end_time']);         
            } else {
                $attrs = [];
                $list['attr'] = [];
            }
            return $this->render('goods-edit', [
                'list' => $list,
                'attrs' => $attrs
            ]);
        }
    }


    //查找用户
    public function actionSearchUser()
    {
        $get = \Yii::$app->request->get();
        $keyword = trim($get['keyword']);
        $lottery_id = trim($get['lottery_id']);

        $query = LotteryLog::find()->alias('l')->select('u.id,u.nickname,u.avatar_url')->where([
            'AND',
            ['or',['LIKE', 'u.nickname', $keyword],['u.id' => $keyword]],
            ['u.store_id' => $this->store->id, 'u.type' => 1],
            ['l.store_id' => $this->store->id],
            ['l.lottery_id' => $lottery_id],
            ])->leftJoin(['u' => User::tableName()], 'l.user_id=u.id');

        $list = $query->limit(30)->asArray()->all();
        return [
            'code' => 0,
            'msg' => 'success',
            'data' => (object)[
                'list' => $list,
            ],
        ];
    }

    ////规格查询
    public function actionAttr($id)
    {

        $goods = Goods::findOne(['id' => $id, 'store_id' => $this->store->id, 'mch_id' => 0]);

        if (!$goods->attr) {
            return [];
        }
 
        $attr_data = json_decode($goods->attr, true);

        foreach ($attr_data as $i => $attr_data_item) {
            if (!isset($attr_data[$i]['no'])) {
                $attr_data[$i]['no'] = '';
            }
            if (!isset($attr_data[$i]['pic'])) {
                $attr_data[$i]['pic'] = '';
            }
            foreach ($attr_data[$i]['attr_list'] as $j => $attr_list) {
                $attr_group = $this->getAttrGroupByAttId($attr_data[$i]['attr_list'][$j]['attr_id']);
                $t = $attr_data[$i]['attr_list'][$j]['attr_name'];
                unset($attr_data[$i]['attr_list'][$j]['attr_name']);

                $attr_data[$i]['attr_list'][$j]['attr_group_name'] = $attr_group ? $attr_group->attr_group_name : null;
                $attr_data[$i]['attr_list'][$j]['attr_name'] = $t;
            }
        }
         return [
            'code'=>0,
            'data' => [
                'attr' => $attr_data
            ]
         ];
    }
    private function getAttrGroupByAttId($att_id)
    {
        $cache_key = 'get_attr_group_by_attr_id_' . $att_id;
        $attr_group = \Yii::$app->cache->get($cache_key);
        if ($attr_group) {
            return $attr_group;
        }
        $attr_group = AttrGroup::find()->alias('ag')
            ->where(['ag.id' => Attr::find()->select('attr_group_id')->distinct()->where(['id' => $att_id])])
            ->limit(1)->one();
        if (!$attr_group) {
            return $attr_group;
        }
        \Yii::$app->cache->set($cache_key, $attr_group, 10);
        return $attr_group;
    }



    // 幻灯片列表
    public function actionSlide()
    {
        $form = new BannerForm();
        $form->type = 5;
        $res = $form->getList($this->store->id);
        return $this->render('slide', [
            'list' => $res[0],
            'pagination' => $res[1]
        ]);
    }

    // 幻灯片编辑
    public function actionSlideEdit($id = 0)
    {
        $banner = Banner::findOne(['id' => $id, 'type' => 5]);
        if (!$banner) {
            $banner = new Banner();
        }
        if (\Yii::$app->request->isPost) {
            $form = new BannerForm();
            $model = \Yii::$app->request->post('model');
            $form->attributes = $model;
            $form->store_id = $this->store->id;
            $form->banner = $banner;
            $form->type = 5;
            return $form->save();
        }
        foreach ($banner as $index => $value) {
            $banner[$index] = str_replace("\"", "&quot;", $value);
        }
        return $this->render('slide-edit', [
            'list' => $banner,
        ]);
    }

    // 幻灯片删除
    public function actionSlideDel($id = 0)
    {
        $banner = Banner::findOne(['id' => $id, 'is_delete' => 0, 'type' => 5]);
        if (!$banner) {
            return [
                'code' => 1,
                'msg' => '幻灯片不存在或已经删除',
            ];
        }
        $banner->is_delete = 1;
        if ($banner->save()) {
            return [
                'code' => 0,
                'msg' => '成功',
            ];
        } else {
            foreach ($banner->errors as $errors) {
                return [
                    'code' => 1,
                    'msg' => $errors[0],
                ];
            }
        }
    }
}