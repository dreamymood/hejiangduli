<?php
/**
 * Created by IntelliJ IDEA.
 * User: Administrator
 * Date: 2017/7/27
 * Time: 18:41
 */

namespace app\modules\api\models\group;

use app\models\common\CommonGoods;
use app\models\common\CommonGoodsAttr;
use app\models\Goods;
use app\models\MiaoshaGoods;
use app\models\PtGoods;
use app\models\PtGoodsDetail;
use app\modules\api\models\ApiModel;

class PtGoodsAttrInfoForm extends ApiModel
{
    public $goods_id;
    public $attr_list;
    public $group_id;

    public function rules()
    {
        return [
            [['goods_id', 'attr_list', 'group_id'], 'required'],
        ];
    }

    public function search()
    {
        if (!$this->validate()) {
            return $this->errorResponse;
        }
        $this->attr_list = json_decode($this->attr_list, true);
        $goods = PtGoods::findOne($this->goods_id);
        if (!$goods) {
            return [
                'code' => 1,
                'msg' => '商品不存在',
            ];
        }

        $original_price = $goods->original_price;

        if ($this->group_id) {
            $goods = PtGoodsDetail::find()->where(['store_id' => $this->getCurrentStoreId(), 'id' => $this->group_id])->one();
        }
        $res = CommonGoods::currentGoodsAttr($goods, $this->attr_list, ['type' => 'PINTUAN']);
        $res['single_price'] = $res['single_price'] > 0 ? $res['single_price'] : $original_price;

        return [
            'code' => 0,
            'msg' => 'success',
            'data' => $res,
        ];
    }
}
