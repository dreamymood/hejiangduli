<?php


namespace app\modules\mch\models\princeaccount;

use app\models\PrinceStoreAccount;
use app\modules\mch\models\MchModel;

class PrinceStoreAccountSettingForm extends MchModel
{
    public $model;
    public $wechat_name;
    public $user_id;

    public function rules()
    {
        $rules = [
			[['wechat_name'], 'trim'],
            [['user_id'], 'required'],
            [['user_id'], 'integer','min' => 1, 'max' => 100000000],
        ];

        return $rules;
    }

    public function attributeLabels()
    {
        return [
            'wechat_name' => '微信号',
            'user_id' => '收款用户',
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->errorResponse;
        }
        $this->model->user_id = $this->user_id;   
        $this->model->wechat_name = $this->wechat_name;

        if ($this->model->save()) {
            return [
                'code' => 0,
                'msg' => '操作成功',
            ];
        } else {
            return $this->getErrorResponse($this->model);
        }
    }

}
