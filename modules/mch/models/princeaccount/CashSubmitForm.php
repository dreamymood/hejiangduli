<?php


namespace app\modules\mch\models\princeaccount;

use app\models\PrinceAccountLog;
use app\models\PrinceStoreAccount;
use app\models\PrinceStoreCash;
use app\modules\user\models\UserModel;

class CashSubmitForm extends UserModel
{
    public $store_id;
    public $money;
    public $type;
    public $data;

    public function rules()
    {
        return [
            [['money'], 'required'],
            [['money'], 'number', 'min' => 1,],
            [['type'], 'integer'],
            [['type'], 'default', 'value' => 0],
            [['data'], 'safe']
        ];
    }

    public function attributeLabels()
    {
        return [
            'money' => '提现金额',
            'type' => '提现方式'
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->errorResponse;
        }
        $this->money = floatval(sprintf('%.2f', $this->money));
        $sa = PrinceStoreAccount::findOne(['store_id' =>$this->store_id]);
        if (!$sa) {
            return [
                'code' => 1,
                'msg' => '商户不存在。',
            ];
        }
        if (!$sa->user_id) {
            return [
                'code' => 1,
                'msg' => '请先设置收款账户。',
            ];
        }
        if ($this->money > $sa->money) {
            return [
                'code' => 1,
                'msg' => '账户余额不足。',
            ];
        }
        $sa->money = $sa->money - $this->money;
        $cash = new PrinceStoreCash();
        $cash->money = $this->money;
        $cash->store_id = $sa->store_id;
        $cash->user_id = $sa->user_id;
        $cash->addtime = time();
        $cash->status = 0;
        $cash->order_no = 'SC' . date('YmdHis') . mt_rand(1000, 9999);
        $cash->type = $this->type;
        $cash->type_data = $this->data ? \Yii::$app->serializer->encode($this->data) : \Yii::$app->serializer->encode(array("account"=> $sa->wechat_name)) ;
        if ($cash->save()) {
            $sa->save(false);
            $log = new PrinceAccountLog();
            $log->store_id = $sa->store_id;
            $log->type = 2;
            $log->desc = '提现';
            $log->price = $this->money;
            $log->addtime = time();
            $log->save();
            return [
                'code' => 0,
                'msg' => '提现申请已提交，请等待管理审核。'
            ];
        }
        return $this->getErrorResponse($cash);
    }
}
