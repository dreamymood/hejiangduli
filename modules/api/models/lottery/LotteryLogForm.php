<?php
namespace app\modules\api\models\lottery;

use app\opening\ApiCode;
use app\modules\api\models\ApiModel;
use app\models\LotteryGoods;

use app\models\LotteryLog;

class LotteryLogForm extends ApiModel
{
    public $store_id;
    public $lottery_id;
    public $id;
    public $user;
    public $status;
    public $form_id;
    public $page;
    public $limit;
    public $page_num;

    public function rules()
    {
        return [
            [['store_id', 'lottery_id', 'status', 'id', 'page_num', 'page', 'limit'], 'integer'],
            [['form_id'], 'string'],
            [['limit'], 'default', 'value' => 5],
            [['page'], 'default', 'value' => 1],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'store_id' => 'Store ID',
            'user_id' => '用户ID',
            'lottery_id' => 'Lottery ID',
            'addtime' => 'Addtime',
            'status' => '0待开奖 1未中奖 2中奖3已领取',
            'goods_id' => '商品id',
            'attr' => '规格',
            'raffle_time' => '领取时间',
            'order_id' => '订单ID',
            'obtain_time' => '获取时间',
            'form_id' => 'Form ID',
        ];
    }

    public function search(){
        if (!$this->validate()) {
            return $this->errorResponse;
        }

        if($this->status==2) {
            $status = [2,3];
        } else {
            $status = [$this->status];
        }

        $query = LotteryLog::find()->where([
                'store_id' => $this->store->id,
                'user_id' => $this->user->id,
            ])->andWhere(['in','status',$status])->with(['lottery'=>function ($query) {
                    $query->where([
                        'is_delete' => 0,
                        'store_id' => $this->store->id,
                    ]);
                }])->with(['gift'=>function ($query) {
                    $query->where([
                        'is_delete' => 0,
                        'store_id' => $this->store->id,
                    ]);
                }]);

        $offset = $this->limit * ($this->page - 1);

        $list = $query->limit($this->limit)->offset($offset)->asArray()->all();

        foreach($list as &$v){
            $v['time'] = date('Y.m.d H.i 开奖',$v['lottery']['end_time']);
        }
        unset($v);

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => [
                'list' => $list,
            ],
        ];
    }


    private function selectId($id){
            $list = LotteryLog::find()->where([
                'store_id' => $this->store->id,
                'id' => $id,
                'user_id' => $this->user->id,
            ])->with(['gift'=>function ($query) {
                    $query->select('name,attr,original_price,cover_pic')->where([
                        'is_delete' => 0,
                        'store_id' => $this->store_id,
                    ]);
            }])->with(['lottery'=>function ($query) {
                    $query->select('stock,end_time')->where([
                        'is_delete' => 0,
                        'store_id' => $this->store_id,
                    ]);
            }])->asArray()->one();

            $list['time'] = date('m月d日 H:i开奖',$list['lottery']['end_time']);

            $query = LotteryLog::find()->where(['store_id' => $this->store_id,'lottery_id' => $list['lottery_id']]);
            $num = $query->count();

            if($list['status']){
                $limit = 6;
                $offset = $limit * ($this->page_num -1);
                $query = $query->andWhere(['in','status',[2,3]])->offset($offset);

                $list['pe_num'] = $query->count();
                
            } else {
                $limit = 30;
            }

            $user_list = $query->with(['user'=>function ($query) {
                    $query->where([
                        'is_delete' => 0,
                        'store_id' => $this->store_id,
                    ]);
                }])->limit($limit)->asArray()->all();

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => [
                    'list' => $list,
                    'num' => $num,
                    'user_list' => $user_list,
                ],
            ];
    }


    public function save()
    {
        if (!$this->validate()) {
            return $this->errorResponse;
        };

        if($this->id){
            return $this->selectId($this->id);
        };
        if($this->lottery_id){
            $goods = LotteryGoods::find()->where([
                    'store_id' => $this->store_id,
                    'id' => $this->lottery_id
                ])->with(['log'=>function ($query) {
                    $query->where([
                        'store_id' => $this->store->id,
                        'user_id' => $this->user->id,
                    ]);
                 }])->asArray()->one();

            $log = $goods['log'];

            if(count($log)){
               return $this->selectId($log[0]['id']);
            } else {
                $model = new LotteryLog();

                $model->store_id = $this->store_id;
                $model->addtime = time();
                $model->user_id = $this->user->id;
                $model->status = 0;
                $model->form_id = $this->form_id;
                $model->attr = $goods['attr'];
                $model->lottery_id = $this->lottery_id;
                $model->goods_id = $goods['goods_id'];

                if($model->save()){
          
                    return $this->selectId($model->id);
                } else {
               
                    return $this->getErrorResponse($model);
                }
            }

        }
    }
}
