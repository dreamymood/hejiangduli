<?php
namespace app\behaviors;

use app\models\LotteryReserve;
use app\models\LotteryGoods;
use app\models\LotteryLog;
use app\models\Goods;
use yii\base\Behavior;
use app\models\LoNoticeSender;

class LotteryBehavior extends BaseBehavior
{
    protected $only_routes = [
    'mch/store/index',
    'mch/lottery/*',
    'api/lottery/*'
    ];

    public $store_id;
    public $store;

    public function beforeAction($event)
    {
        \Yii::warning('----BARGAIN BEHAVIOR----');
        if (!empty($event->action->controller->store) && !empty($event->action->controller->store->id)) {
            $this->store = $event->action->controller->store;
        }
        try {
            $this->checkPrizeTimeout($event);
        } catch (\Exception $e) {

        }
    }


    /**
     * 处理中奖
     * @param $event 
     */
    private function checkPrizeTimeout($event)
    {
        /** @var Wechat $wechat */
        $wechat = isset($event->action->controller->wechat) ? $event->action->controller->wechat : null;
        $cacheKey = 'lottery_prize_timeout_checker';
        if (!$wechat) {
            \Yii::$app->cache->set($cache_key, false);
            return true;
        }

        if (\Yii::$app->cache->get($cacheKey)) {
            return true;
        }

        \Yii::$app->cache->set($cacheKey, true, 30);

        $lottery = LotteryGoods::find()->where([
            'store_id' => $this->store->id,
            'is_delete' => 0,
            'status' => 1,
            'type' => 0
            ])->andWhere(['<=','end_time',time()])->one();

        if($lottery) {
            try{
                $reserve = LotteryReserve::find()->select('user_id')->where([
                    'store_id' => $this->store->id,
                    'lottery_id' => $lottery->id,
                    ])->column();

                $query = LotteryLog::find()->select('id,user_id')->where([ 
                            'store_id' => $this->store->id,
                            'lottery_id' => $lottery->id,
                            'status' => 0,
                        ]);
                $count = $query->count();//参与人数

                $stock = $lottery->stock;//奖品数量


                if($count > $stock) {

                    $log = $query->asArray()->all();
                    $logs = array_column($log,'user_id', 'id'); //参与详情

                    $same = array_intersect($logs,$reserve); //中奖名单1
                    $ids_a = array_keys($same);

                    //$num = $lottery->stock-count($same); //剩余数量

                    if(count($same) >= $stock){
                        $num = count($same) - $stock;

                        $new_ids = array_splice($ids_a,0,$num);
                    } else if( count($same)+1 == $stock){
                        //随机值
                        $new_logs = array_diff($logs,$same);

                        $ids_b = array_rand($new_logs,1);
                        array_push($ids_a,$ids_b);

                        $new_ids = $ids_a;               
                    } else {
                        $num = $stock-count($same); 
                        //随机值
                        $new_logs = array_diff($logs,$same);
                        $ids_b = array_rand($new_logs,$num); 
                        $new_ids = array_merge($ids_a,$ids_b);

                    }

                    $cache_conduct = 'lottery_prize_conduct';
                    if (\Yii::$app->cache->get($cache_conduct)) {
                        return true;
                    };
                    \Yii::$app->cache->set($cache_conduct,$lottery->id,30);



                    $t = \Yii::$app->db->beginTransaction();  
                    //批量修改 
                    $idList = LotteryLog::find()->select('id')
                            ->where([
                                'AND',
                                ['store_id' => $this->store->id],
                                ['lottery_id' => $lottery->id],
                                ['status' => 0],
                                ['in','id',$new_ids],
                            ])->asArray()->all();

                    $idList = array_column($idList,'id');

                    LotteryLog::updateAll(['status' => 2,'obtain_time' => time(), ], [
                        'id' => $idList,
                    ]);

                    //无获奖
                    $idList = LotteryLog::find()->select('id')
                            ->where([
                                'AND',
                                ['store_id' => $this->store->id],
                                ['lottery_id' => $lottery->id],
                                ['status' => 0],
                            ])->asArray()->all();

                    $idList = array_column($idList,'id');

                    LotteryLog::updateAll(['status' => 1,'obtain_time' => time(), ], [
                        'id' => $idList,
                    ]);
                    $lottery->type = 1;
                    if($lottery->save()){
                        $t->commit();
                        $notice = new LoNoticeSender($wechat, $this->store->id);
                        $notice->sendSucNotice($idList);

                    } else {
                        $t->rollBack();                          
                    }
                } else {
                    $t = \Yii::$app->db->beginTransaction();  
                    $cache_conduct = 'lottery_prize_conduct';
                    if (\Yii::$app->cache->get($cache_conduct)) {
                        return true;
                    };
                    \Yii::$app->cache->set($cache_conduct,$lottery->id,30);


                    //批量修改
                    $idList = LotteryLog::find()->select('id')
                    ->where([
                        'AND',
                        ['store_id' => $this->store->id],
                        ['lottery_id' => $lottery->id],
                        ['status' => 0],
                    ])->asArray()->all();

                    $idList = array_column($idList,'id');
                    LotteryLog::updateAll(['status' => 2,'obtain_time' => time(), ], [
                        'id' => $idList,
                    ]);

                    $lottery->type = 1;
                    if($lottery->save()){
                        $t->commit();
                        $notice = new LoNoticeSender($wechat, $this->store->id);
                        $notice->sendSucNotice($idList);

                    } else {
                        $t->rollBack();                          
                    }
                    
                }

            }catch (\Exception $e) { 
                 \Yii::warning($e->getMessage());
            }

        } else {
            return true;
        }
    }
}