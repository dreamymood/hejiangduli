<?php
/**
 * Created by IntelliJ IDEA.
 * User: Administrator
 * Date: 2017/7/27
 * Time: 18:41
 */

namespace app\modules\api\models;

use app\opening\ApiResponse;
use app\models\common\CommonGoods;
use app\models\Goods;
use app\models\MiaoshaGoods;
use app\models\MsGoods;

class GoodsAttrInfoForm extends ApiModel
{
    public $goods_id;
    public $attr_list;
    public $type;

    public $miaosha_goods;

    public function rules()
    {
        return [
            [['goods_id', 'attr_list'], 'required'],
            [['type'], 'default', 'value' => 's']
        ];
    }

    public function search()
    {
        if (!$this->validate()) {
            return $this->errorResponse;
        }
        $this->attr_list = json_decode($this->attr_list, true);
        if ($this->type == 's') {
            $goods = Goods::findOne($this->goods_id);
        } elseif ($this->type == 'ms') {
            $miaosha_goods = MiaoshaGoods::findOne([
                'id' => $this->goods_id,
                'is_delete' => 0,
            ]);
            $goods = MsGoods::findOne($miaosha_goods->goods_id);
            $this->miaosha_goods = $miaosha_goods;
        }
        if (!$goods) {
            return new ApiResponse(1, '商品不存在');
        }
//        $res = $goods->getAttrInfo($this->attr_list);

        if ($this->type == 'ms') {
            $miaosha_data = CommonGoods::currentGoodsAttr($miaosha_goods, $this->attr_list, [
                'type' => 'MIAOSHA',
                'original_price' => $goods->original_price
            ]);
        } else {
            $res = CommonGoods::currentGoodsAttr($goods, $this->attr_list);
        }
//        $miaosha_data = $this->getMiaoshaData($goods, $this->attr_list);
        if ($miaosha_data) {
            $miaosha_data['miaosha_price'] = number_format($miaosha_data['miaosha_price'], 2, '.', '');
            $miaosha_data['rest_num'] = min((int)$res['num'], ((int)$miaosha_data['miaosha_num'] - $miaosha_data['sell_num']));
        }
        $res['miaosha'] = $miaosha_data;
        return new ApiResponse(0, 'success', $res);
    }

    /**
     * @param MsGoods $goods
     * @param array $attr_id_list eg.[12,34,22]
     * @return array ['attr_list'=>[],'miaosha_price'=>'秒杀价格','miaosha_num'=>'秒杀数量','sell_num'=>'已秒杀商品数量']
     */
    private function getMiaoshaData($goods, $attr_id_list = [])
    {
        $miaosha_goods = $this->miaosha_goods;
        if (!$miaosha_goods) {
            return null;
        }
        $attr_data = json_decode($miaosha_goods->attr, true);
        sort($attr_id_list);
        $miaosha_data = null;
        foreach ($attr_data as $i => $attr_data_item) {
            $_tmp_attr_id_list = [];
            foreach ($attr_data_item['attr_list'] as $item) {
                $_tmp_attr_id_list[] = $item['attr_id'];
            }
            sort($_tmp_attr_id_list);
            if ($attr_id_list == $_tmp_attr_id_list) {
                $miaosha_data = $attr_data_item;
                break;
            }
        }
        return $miaosha_data;
    }
}
