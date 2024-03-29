<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/12/7
 * Time: 20:05
 */

namespace app\modules\mch\models\princeorder\book;

use app\models\Shop;
use app\models\User;
use app\models\YyGoods;
use app\models\YyOrder;
use app\models\YyOrderForm;
use app\modules\mch\models\MchModel;
use app\modules\mch\models\ExportList;
use yii\data\Pagination;
use app\models\Store;

class OrderForm extends MchModel
{
    public $store_id;
    public $user_id;
    public $keyword;

    public $status;

    public $flag;//是否导出

    public $keyword_1;
    public $date_start;
    public $date_end;

    public $fields;
    public $platform;//所属平台

    public function rules()
    {
        return [
            [['keyword', 'date_start', 'date_end'], 'trim'],
            [['status', 'keyword_1'], 'integer'],
            [['status',], 'default', 'value' => -1],
            [['flag'], 'trim'],
            [['flag'], 'default', 'value' => 'no'],
            [['fields'], 'safe']
        ];
    }

    /**
     * 预约订单列表
     */
    public function getList()
    {
        if (!$this->validate()) {
            return $this->errorResponse;
        }

        $clerkQuery = User::find()->where()->andWhere('id = o.clerk_id')->select('nickname');
        $shopQuery = Shop::find()->where()->andWhere('id = o.shop_id')->select('name');
        $query = YyOrder::find()
            ->alias('o')
            ->select([
                'o.*',
				's.name AS store_name',
                'g.name AS goods_name', 'g.cover_pic',
                'u.nickname', 'u.platform',
                'clerk_name' => $clerkQuery,
                'shop_name' => $shopQuery
            ])
            ->andWhere(['o.is_delete' => 0])
            ->leftJoin(['g' => YyGoods::tableName()], 'g.id=o.goods_id')
			->leftJoin(['s' => Store::tableName()], 'o.store_id = s.id')
            ->leftJoin(['u' => User::tableName()], 'u.id=o.user_id');

        switch ($this->status) {
            case 0: //未付款
                $query->andWhere([
                    'o.is_pay' => 0,
                    'o.is_cancel' => 0,
                ]);
                break;
            case 1: //待使用
                $query->andWhere([
                    'o.is_pay' => 1,
                    'o.is_use' => 0,
                    'o.is_cancel' => 0,
                    'o.apply_delete' => 0,
                    'o.is_refund' => 0,
                ]);
                break;
            case 2: //待评价
                $query->andWhere([
                    'o.is_pay' => 1,
                    'o.is_use' => 1,
                ]);
                break;
            case 3: //退款
                $query->andWhere([
                    'o.is_pay' => 1,
                    'o.apply_delete' => 1,
                ]);
                break;
            case 4:
                break;
            case 5: //已取消
                $query->andWhere([
                    'o.is_pay' => 0,
                    'o.is_cancel' => 1,
                ]);
                break;
            default:
                break;
        }

        if($this->status == 8){
            $query->andWhere(['o.is_recycle'=>1]);
        }else{
            $query->andWhere(['o.is_recycle'=>0]);
        }

        if ($this->keyword) {//关键字查找
            if ($this->keyword_1 == 1) {
                $query->andWhere(['like', 'o.order_no', $this->keyword]);
            }
            if ($this->keyword_1 == 2) {
                $query->andWhere(['like', 'u.nickname', $this->keyword]);
            }
            if ($this->keyword_1 == 3) {
                $query->andWhere(['like', 'g.name', $this->keyword]);
            }
        }
        if ($this->date_start) {
            $query->andWhere(['>=', 'o.addtime', strtotime($this->date_start)]);
        }
        if ($this->date_end) {
            $query->andWhere(['<=', 'o.addtime', strtotime($this->date_end)]);
        }
        if (isset($this->platform)) {
            $query->andWhere(['platform' => $this->platform]);
        }

        $query1 = clone $query;
        if ($this->flag == "EXPORT") {
            $list_ex = $query1;
            $export = new ExportList();
            $export->order_type = 3;
            $export->fields = $this->fields;
            $export->is_offline = 1;
            $export->dataTransform_new($list_ex);
        }
        $count = $query->count();
        $p = new Pagination(['totalCount' => $count, 'pageSize' => 20]);

        $list = $query
            ->orderBy('o.addtime DESC')
            ->offset($p->offset)
            ->limit($p->limit)
            ->asArray()
            ->all();
        foreach ($list as $k => $v) {
            $orderForm = YyOrderForm::find()
                ->select([
                    'key', 'value', 'type'
                ])
                ->andWhere(['order_id' => $v['id'], 'goods_id' => $v['goods_id'], 'is_delete' => 0])
                ->all();
            $list[$k]['orderFrom'] = $orderForm;
        }
        return [
            'list' => $list,
            'p' => $p,
            'row_count' => $count,
        ];
    }


    /**
     * @param $order_id
     * @return array|\yii\db\ActiveRecord[]
     */
    public function getOrderGoodsList($goods_id, $order_id)
    {
        $order_list['form'] = YyOrderForm::find()->select(['key', 'value' ,'type'])->andWhere(['order_id' => $order_id, 'goods_id' => $goods_id])->asArray()->all();

        $order_list['name'] = YyGoods::find()->select('name')->andWhere(['id' => $goods_id])->scalar();

        return $order_list;
    }
}
