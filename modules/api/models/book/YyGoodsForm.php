<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/11/27
 * Time: 9:32
 */

namespace app\modules\api\models\book;

use app\models\Article;
use app\models\common\CommonGoods;
use app\models\GoodsShare;
use app\models\Order;
use app\models\PtGoods;
use app\models\PtGoodsPic;
use app\models\PtOrder;
use app\models\PtOrderComment;
use app\models\PtOrderDetail;
use app\models\Shop;
use app\models\User;
use app\models\YyGoods;
use app\models\YyGoodsPic;
use app\models\YyOrderComment;
use app\modules\api\models\ApiModel;
use PHP_CodeSniffer\Util\Common;
use yii\data\Pagination;

class YyGoodsForm extends ApiModel
{
    public $page = 0;
    public $store_id;

    public $user_id;

    public $gid;

    public $limit;


    /**
     * @return array
     * 拼团商品列表
     */
    public function getList()
    {
        $page = \Yii::$app->request->get('page') ?: 1;
        $limit = (int)\Yii::$app->request->get('limit') ?: 6;
        $cid = \Yii::$app->request->get('cid');
        $query = YyGoods::find()
            ->andWhere(['is_delete' => 0, 'store_id' => $this->store_id, 'status' => 1]);
        if ((int)$cid) {
            // 分类
            $query->andWhere(['cat_id' => $cid]);
        }
        $count = $query->count();
        $p = new Pagination(['totalCount' => $count, 'pageSize' => $limit, 'page' => $page - 1]);
        $list = $query
            ->offset($p->offset)
            ->limit($p->limit)
            ->orderBy('sort ASC')
            ->asArray()
            ->all();

        return [
            'row_count' => intval($count),
            'page_count' => intval($p->pageCount),
            'page' => intval($page),
            'list' => $list,
        ];
    }

    /**
     * @return mixed|string
     * 拼团商品详情
     */
    public function getInfo()
    {
        $info = YyGoods::find()
            ->andWhere(['is_delete' => 0, 'store_id' => $this->store_id, 'status' => 1, 'id' => $this->gid])
            ->asArray()
            ->one();
        $goods = YyGoods::find()
            ->andWhere(['is_delete' => 0, 'store_id' => $this->store_id, 'status' => 1, 'id' => $this->gid])->one();
        if (!$info) {
            return [
                'code' => 1,
                'msg' => '商品不存在或已下架',
            ];
        }
        $info['pic_list'] = YyGoodsPic::find()
            ->select('pic_url')
            ->andWhere(['goods_id' => $this->gid, 'is_delete' => 0])
            ->column();

        $info['attr'] = json_decode($info['attr'], true);
        $info['service'] = explode(',', $info['service']);
        $attr_group_list = $goods->getAttrGroupList();

        if (!empty($info['shop_id']) && $info['shop_id'] != '-1') {
            $shopId = explode(',', trim($info['shop_id'], ','));
            $shopList = Shop::find()
                ->andWhere(['id' => $shopId])
                ->andWhere(['store_id' => $this->store_id, 'is_delete' => 0])
                ->asArray()
                ->all();
            $info['shopListNum'] = count($shopList);
        } elseif ($info['shop_id'] == '-1') {
            $info['shopListNum'] = 0;
            $shopList = [];
        } else {
            $shopList = Shop::find()
                ->andWhere(['store_id' => $this->store_id, 'is_delete' => 0])
                ->asArray()
                ->all();
            $info['shopListNum'] = count($shopList);
        }

        if($info['use_attr']){
            $num = 0;
            $price = [];
            foreach($info['attr'] as $v){
                $num += $v['num'];
                if($v['price'] > 0){
                    $price[] = $v['price'];
                }else{
                    $price[] = $info['price'];
                }
            }

            // 获取最高分销价 、最低会员价、当前会员价
            $goodsShare = GoodsShare::find()->where(['type' => GoodsShare::SHARE_GOODS_TYPE_YY, 'goods_id' => $goods->id])->one();
            $res = CommonGoods::getMMPrice([
                'attr' => $goods['attr'],
                'attr_setting_type' => $goodsShare['attr_setting_type'],
                'share_type' => $goodsShare['share_type'],
                'share_commission_first' => $goodsShare['share_commission_first'],
                'price' => $goods['price'],
                'individual_share' => $goodsShare['individual_share'],
            ]);

            $info['price'] = number_format(min($price), 2, '.', '');
            $info['stock'] = $num;
            $info['max_share_price'] = number_format($res['max_share_price'], 2, '.', '');
            $info['min_member_price'] = number_format($res['min_member_price'], 2, '.', '');
            $info['is_share'] = $res['is_share'];
        }
        return [
            'code' => 0,
            'msg' => 'success',
            'data' => [
                'attr_group_list' => $attr_group_list,
                'info' => $info,
                'shopList' => $shopList,
            ],
        ];
    }

    /**
     * @return array
     * 评论列表
     */
    public function comment()
    {
        $query = YyOrderComment::find()
            ->alias('c')
            ->select([
                'c.score', 'c.content', 'c.pic_list', 'c.addtime',
                'u.nickname', 'u.avatar_url',
                'od.attr'
            ])
            ->andWhere(['c.store_id' => $this->store_id, 'c.goods_id' => $this->gid, 'c.is_delete' => 0, 'c.is_hide' => 0])
            ->leftJoin(['u' => User::tableName()], 'u.id = c.user_id')
            ->leftJoin(['od' => PtOrderDetail::tableName()], 'od.id=c.order_detail_id');
        $count = $query->count();
        $pagination = new Pagination(['totalCount' => $count, 'page' => $this->page, 'pageSize' => 20]);

        $comment = $query
            ->limit($pagination->limit)
            ->offset($pagination->offset)
            ->orderBy('c.addtime DESC')
            ->asArray()
            ->all();
        foreach ($comment as $k => $v) {
            $comment[$k]['attr'] = json_decode($v['attr'], true);
            $comment[$k]['pic_list'] = json_decode($v['pic_list'], true);
            $comment[$k]['addtime'] = date('m月d日', $v['addtime']);
            $comment[$k]['nickname'] = $this->substr_cut($v['nickname']);
        }
        return [
            'code' => 0,
            'msg' => 'success',
            'data' => [
                'row_count' => $count,
                'page_count' => $pagination->pageCount,
                'comment' => $comment,
            ],
        ];
    }


    // 将用户名 做隐藏
    private function substr_cut($user_name)
    {
        $strlen = mb_strlen($user_name, 'utf-8');
        $firstStr = mb_substr($user_name, 0, 1, 'utf-8');
        $lastStr = mb_substr($user_name, -1, 1, 'utf-8');
        return $strlen == 2 ? $firstStr . str_repeat('*', mb_strlen($user_name, 'utf-8') - 1) : $firstStr . str_repeat("*", $strlen - 2) . $lastStr;
    }


    /**
     * @return array|object
     * 获取数量
     */
    public function countData()
    {
        if (!$this->validate()) {
            return $this->errorResponse;
        }
        $score_all = OrderComment::find()->alias('oc')
            ->where(['oc.goods_id' => $this->goods_id, 'oc.is_delete' => 0, 'oc.is_hide' => 0,])->count();
        $score_3 = OrderComment::find()->alias('oc')
            ->where(['oc.goods_id' => $this->goods_id, 'oc.is_delete' => 0, 'oc.is_hide' => 0, 'oc.score' => 3])->count();
        $score_2 = OrderComment::find()->alias('oc')
            ->where(['oc.goods_id' => $this->goods_id, 'oc.is_delete' => 0, 'oc.is_hide' => 0, 'oc.score' => 2])->count();
        $score_1 = OrderComment::find()->alias('oc')
            ->where(['oc.goods_id' => $this->goods_id, 'oc.is_delete' => 0, 'oc.is_hide' => 0, 'oc.score' => 1])->count();
        return (object)[
            'score_all' => $score_all ? $score_all : 0,
            'score_3' => $score_3 ? $score_3 : 0,
            'score_2' => $score_2 ? $score_2 : 0,
            'score_1' => $score_1 ? $score_1 : 0,
        ];
    }
}
