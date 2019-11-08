<?php

namespace app\modules\mch\models\princeaccount\admin;

use app\models\PrinceStoreAccount;
use app\models\PrinceAccountLog;
use app\models\PrinceStoreCash;
use app\models\PrinceConfig;
use app\models\User;
use app\modules\mch\models\MchModel;
use luweiss\wechat\Wechat;
use app\models\WechatApp;
use app\models\Store;

class CashConfirmForm extends MchModel
{
    public $id;
    public $status;

    public $wechat;
    public $wechat_app;
	
    public function rules()
    {
        return [
            [['id', 'status'], 'required'],
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->errorResponse;
        }
        $cash = PrinceStoreCash::findOne([
            'id' => $this->id,
            'status' => 0,
        ]);
        if (!$cash) {
            return [
                'code' => 1,
                'msg' => '提现记录不存在。',
            ];
        }
        $sa = PrinceStoreAccount::findOne(['store_id' =>$cash->store_id]);

        if ($this->status == 2) {//拒绝，资金返回
            $cash->status = 2;
            $sa->money = floatval($sa->money) + floatval($cash->money);
            $sa->save(false);
            $cash->save(false);
            $log = new PrinceAccountLog();
            $log->store_id = $cash->store_id;
            $log->type = 1;
            $log->price = $cash->money;
            $log->desc = '提现被拒绝，资金返回账户';
            $log->addtime = time();
            $log->save();
            return [
                'code' => 0,
                'msg' => '操作成功。',
            ];
        }
        if ($this->status == 1) {//同意，微信支付到用户零钱
            $user = User::findOne($sa->user_id);
			$store= Store::findOne(['id' =>$cash->store_id]);
			
			$this->wechat_app =WechatApp::findOne(['id' => $store->wechat_app_id]);
	
			if (!is_dir(\Yii::$app->runtimePath . '/pem')) {
				mkdir(\Yii::$app->runtimePath . '/pem');
				file_put_contents(\Yii::$app->runtimePath . '/pem/index.html', '');
			}
			$cert_pem_file = null;
			if ($this->wechat_app->cert_pem) {
				$cert_pem_file = \Yii::$app->runtimePath . '/pem/' . md5($this->wechat_app->cert_pem);
				if (!file_exists($cert_pem_file)) {
					file_put_contents($cert_pem_file, $this->wechat_app->cert_pem);
				}
			}
			$key_pem_file = null;
			if ($this->wechat_app->key_pem) {
				$key_pem_file = \Yii::$app->runtimePath . '/pem/' . md5($this->wechat_app->key_pem);
				if (!file_exists($key_pem_file)) {
					file_put_contents($key_pem_file, $this->wechat_app->key_pem);
				}
			}
			
			$this->wechat = new Wechat([
				'appId' => $this->wechat_app->app_id,
				'appSecret' => $this->wechat_app->app_secret,
				'mchId' => $this->wechat_app->mch_id,
				'apiKey' => $this->wechat_app->key,
				'certPem' => $cert_pem_file,
				'keyPem' => $key_pem_file,
			]);
			
			$config = PrinceConfig::findOne(['code' => 'apply_rate', 'store_id' =>0]);
			$apply_rate=$config->value;
			$apply_rate=$apply_rate>0?$apply_rate:0;
			
            $wechat = $this->wechat;
            $res = $wechat->pay->transfers([
                'partner_trade_no' => $cash->order_no,
                'openid' => $user->wechat_open_id,
                'amount' => intval($cash->money * (100-$apply_rate)),
                'desc' => '商城提现',
            ]);
            if (!$res) {
                return [
                    'code' => 1,
                    'msg' => '转账失败，请检查微信配置是否正确。'
                ];
            }
            if ($res['return_code'] != 'SUCCESS') {
                return [
                    'code' => 1,
                    'msg' => '转账失败：' . $res['return_msg'],
                    'res' => $res,
                ];
            }
            if ($res['result_code'] != 'SUCCESS') {
                return [
                    'code' => 1,
                    'msg' => '转账失败：' . $res['err_code_des'],
                    'res' => $res,
                ];
            }
            if ($res['result_code'] == 'SUCCESS') {
                $cash->status = 1;
                $cash->virtual_type = 0;
                $cash->save(false);
                return [
                    'code' => 0,
                    'msg' => '转账成功。',
                    'res' => $res,
                ];
            }
        }
        if ($this->status == 3) {
            $cash->status = 1;
            $cash->virtual_type = 5;
            $cash->save(false);
            return [
                'code' => 0,
                'msg' => '操作成功。',
            ];
        }
    }
}
