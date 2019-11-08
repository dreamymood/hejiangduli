<?php
namespace app\modules\api\models\lottery;

use app\opening\ApiResponse;
use app\modules\api\models\ApiModel;
use yii\data\Pagination;
use app\utils\GetInfo;

use app\models\Banner;
use app\models\LotteryGoods;
use app\models\LotteryLog;
use app\models\Goods;

/**
 * @property \app\models\Store $store;
 */
class IndexForm extends ApiModel
{
    public $store_id;
    public $id;
    public $limit;
    public $page;
    public $user;

    public function rules()
    {
        return [
            [['limit', 'page', 'id'], 'integer'],
            [['limit'], 'default', 'value' => 5],
            [['page'], 'default', 'value' => 1]
        ];
    }

    public function search()
    {
        $all_list = $this->getGoodsList();
        $data = [
            'banner_list' => $this->getBanner(),
            'goods_list' => $all_list[0],
            'list' => $all_list[1],
        ];
        return new ApiResponse(0, '', $data);
    }

    private function getBanner()
    {
        $banner = Banner::find()->where(['store_id' => $this->store_id, 'type' => 5, 'is_delete' => 0])->orderBy('sort ASC')->asArray()->all();
        return $banner;
    }

    // 获得商品信息
    private function getGoodsList()
    {
        if (!$this->validate()) {
            return $this->errorResponse;
        }

        $query = LotteryGoods::find()->where([
                'store_id' => $this->store_id,
                'is_delete' => 0,
                'status' => 1,
                'type' => 0,
            ])->andWhere(['<=','start_time',time()])
                ->andWhere(['>=','end_time',time()])
                ->with(['goods'=>function ($query) {
                    $query->where([
                        'is_delete' => 0,
                        'store_id' => $this->store_id,
                    ]);
                }]);

        $offset = $this->limit * ($this->page - 1);
        $list = $query->orderBy('sort ASC,id ASC')->limit($this->limit)->offset($offset)->all();

        $other = [];

        foreach ($list as $k=>&$v){
            $other['num'][$k] = LotteryLog::find()->where(['store_id' => $this->store_id,'lottery_id' => $v->id])->count();
            $other['status'][$k] = LotteryLog::find()->where(['store_id' => $this->store_id,'lottery_id' => $v->id, 'user_id' => $this->user->id])->one() == null;

            $attr = json_decode($v->attr,true);
            $attr_id_list = array_reduce($attr, create_function('$v,$w', '$v[]=$w["attr_id"];return $v;'));
            $v->goods->original_price = $v->goods->getAttrInfo($attr_id_list)['price'];
            $time = $v['end_time'] - time();
            $v->end_time = [floor($time/86400),floor($time%86400/3600)];
        }
        unset($v);
        $new_list = array($this->simplifyData($list),$other);
        return $new_list;
    }

    private function simplifyData($data)
    {
        foreach ($data as $key => $val) {
            $newData[$key] = $val->attributes;
            if ($val->goods) {
                $newData[$key]['goods'] = $val->goods;
            }
        } 
        return $newData;
    }


    public function goods()
    { 
        $id = $this->id;
        $query = LotteryGoods::find()->where([
                'store_id' => $this->store_id,
                'is_delete' => 0,
                'status' => 1, 
                'id' => $id,
            ])->andWhere(['<=','start_time',time()])
                ->andWhere(['>=','end_time',time()])
                ->with(['goods'=>function ($query) {
                    $query->where([
                        'is_delete' => 0,
                        'store_id' => $this->store_id,
                    ]);
                }]);

        $lotteryGoods = $query->one();

        $goods = $lotteryGoods->goods;

        // 获取视频链接
        $resUrl = GetInfo::getVideoInfo($goods->video_url);

        //原价
        $attr = json_decode($lotteryGoods->attr,true);
        $attr_id_list = array_reduce($attr, create_function('$v,$w', '$v[]=$w["attr_id"];return $v;'));
        $goods->original_price = $lotteryGoods->goods->getAttrInfo($attr_id_list)['price'];

        $num = LotteryLog::find()->where(['store_id' => $this->store->id,'lottery_id' => $id])->count();
        $status = LotteryLog::find()->where(['store_id' => $this->store->id,'lottery_id' => $id, 'user_id' => $this->user->id])->one() ==null;
        $time = $lotteryGoods->end_time - time(); 
        $newGoods = [
           'pic_list' => $goods->goodsPicList,
           'video_url' => $resUrl['url'],
           'name' => $goods->name,
           'num' => $num,
           'status' => $status,
           'original_price' => $goods->original_price,
           'detail' => $goods->detail,
           'id' => $goods->id,
           'time' => floor($time/86400).'天'.floor($time%86400/3600).'小时'
        ];
        $data = [
            'goods' => $newGoods,
            'lottery_info' => $lotteryGoods
        ];
        return new ApiResponse(0, '', $data);
    }



    public function getSetting()
    {
        $bargainSetting = BargainSetting::find()->where(['store_id' => $this->store_id])->asArray()->one();
        return new ApiResponse(0, '', $bargainSetting);
    }


}