<?php

/**
 * author: wxf
 */

namespace app\models\common;


use app\models\MchPlugin;
use app\models\MchSetting;
use app\models\Setting;
use app\modules\mch\models\LevelListForm;

class CommonGoods
{
    /**
     * 获取当前规格的相应信息
     * @param $goods 商品信息
     * @param $currentGoodsAttr 当前选择的规格数据 例:[42,43]
     * @param array $otherData 特殊数据处理 阶梯团、秒杀
     * @return array
     */
    public static function currentGoodsAttr($goods, $currentGoodsAttr, $otherData = [])
    {
        $userLevel = \Yii::$app->user->identity->level;
        $goodsAttrs = \Yii::$app->serializer->decode($goods['attr']);

        foreach ($goodsAttrs as $goodsAttr) {
            $attrIdArr = [];
            foreach ($goodsAttr['attr_list'] as $item) {
                $attrIdArr[] = $item['attr_id'];
            }

            sort($attrIdArr);
            sort($currentGoodsAttr);

            if (implode($attrIdArr) === implode($currentGoodsAttr)) {
                // 可以传入指定用户等级
                $level = $otherData['user_level'] ? $otherData['user_level'] : $userLevel;
                $keyName = 'member' . $level;
                $data = [];

                // 特殊插件数据处理 秒杀表价格字段为 original_price
                if ($otherData['type'] === 'MIAOSHA') {
                    $price = $goodsAttr['miaosha_price'] > 0 ? $goodsAttr['miaosha_price'] : $otherData['original_price'];
                    $data['num'] = $goodsAttr['miaosha_num'];
                    $data['miaosha_num'] = $goodsAttr['miaosha_num'];
                    $data['sell_num'] = $goodsAttr['sell_num'];
                    $data['id'] = $goods->id;
                } else {
                    $price = $goodsAttr['price'] > 0 ? $goodsAttr['price'] : $goods['price'];
                    $data['num'] = $goodsAttr['num'];
                }

                if ($goodsAttr[$keyName] > 0) {
                    $price = $goodsAttr[$keyName];
                    $data['is_member_price'] = true;
                }

                $data['price'] = number_format($price, 2, '.', '');
                $data['attr_list'] = $goodsAttr['attr_list'];
                $data['pic'] = $goodsAttr['pic'];
                $data['no'] = $goodsAttr['no'];
                $data['share_commission_first'] = $goodsAttr['share_commission_first'];
                $data['share_commission_second'] = $goodsAttr['share_commission_second'];
                $data['share_commission_third'] = $goodsAttr['share_commission_third'];

                // 特殊插件数据处理 拼团有单买价
                if ($otherData['type'] === 'PINTUAN') {
                    $data['single_price'] = number_format($goodsAttr['single'], 2, '.', '');
                    $data['single'] = number_format($goodsAttr['single'], 2, '.', '');
                }

                // TODO 之前代码有用到该字段 用于秒杀插件
                $data['miaosha_price'] = $price;

                return $data;
            }
        }
    }


    /**
     * 获取当前商品的最高分销价、及最低会员价(根据用户等级)
     * @param $goods
     * @return array
     */
    public static function getMMPrice(array $goods)
    {
        $levelForm = new LevelListForm();
        $levels = $levelForm->getAllLevel();

        $attrs = \Yii::$app->serializer->decode($goods['attr']);
        $userLevel = \Yii::$app->user->identity->level;

        $shareSetting = Setting::findOne(['store_id' => \Yii::$app->store->id]);

        $maxSharePriceArr = [];
        $minMemberPriceArr = [];
        foreach ($attrs as $attr) {
            // 是否开启单独分销设置
            if ((int)$goods['individual_share'] === 1) {
                //分销价 普通设置
                // 固定金额分销价 直接显示
                // 百分比金额分销价 * 当前规格价(如果为空则 * 商品售价)
                if ((int)$goods['attr_setting_type'] === 0) {
                    if (isset($goods['share_type']) && (int)$goods['share_type'] === 0 && $goods['share_commission_first'] > 0) {
                        if ($attr['price'] > 0) {
                            $maxSharePriceArr[] = ($goods['share_commission_first'] * $attr['price']) / 100;
                        } else {
                            $maxSharePriceArr[] = ($goods['share_commission_first'] * $goods['price']) / 100;
                        }
                    } else {
                        $goods['share_commission_first'] > 0 ? $maxSharePriceArr[] = $goods['share_commission_first'] : '';
                    }

                } else {
                    // 分销价 详细设置(多规格分销价)
                    if (isset($goods['share_type']) && (int)$goods['share_type'] === 0 && $attr['share_commission_first'] > 0) {
                        if ($attr['price'] > 0) {
                            $maxSharePriceArr[] = ($attr['share_commission_first'] * $attr['price']) / 100;
                        } else {
                            $maxSharePriceArr[] = ($attr['share_commission_first'] * $goods['price']) / 100;
                        }
                    } else {
                        $attr['share_commission_first'] > 0 ? $maxSharePriceArr[] = $attr['share_commission_first'] : '';
                    }
                }

            } else {
                // 未开启单独分销设置则根据 全局分销佣金计算
                if (isset($shareSetting['price_type']) && (int)$shareSetting['price_type'] === 0 && $shareSetting['first'] > 0) {
                    if ($attr['price'] > 0) {
                        $maxSharePriceArr[] = ($shareSetting['first'] * $attr['price']) / 100;
                    } else {
                        $maxSharePriceArr[] = ($shareSetting['first'] * $goods['price']) / 100;
                    }
                } else {
                    $shareSetting['first'] > 0 ? $maxSharePriceArr[] = $shareSetting['first'] : '';
                }
            }

            // 普通会员显示下一级会员价
            if ($userLevel === -1) {
                $keyName = 'member' . $levels[0]['level'];
                if ($attr[$keyName]) {
                    $minMemberPriceArr[] = $attr[$keyName];
                }

            } else {
                // 会员显示当前会员价
                $keyName = 'member' . $userLevel;
                if ($attr[$keyName]) {
                    $minMemberPriceArr[] = $attr[$keyName];
                }

            }
        }

        $data['max_share_price'] = !empty($maxSharePriceArr) ? max($maxSharePriceArr) : 0;
        $data['min_member_price'] = !empty($minMemberPriceArr) ? min($minMemberPriceArr) : 0;
        $data['is_share'] = $shareSetting['level'] > 0 ? true : false;

        if (isset($goods['mch_id']) && $goods['mch_id'] > 0) {
            $mchPlugin = MchPlugin::findOne(['mch_id' => $goods['mch_id']]);
            $mchSetting = MchSetting::findOne(['mch_id' => $goods['mch_id']]);
            (int)$mchPlugin['is_share'] === 0 ? $data['is_share'] = false : '';
            (int)$mchSetting['is_share'] === 0 ? $data['is_share'] = false : '';
        }

        return $data;
    }
}
