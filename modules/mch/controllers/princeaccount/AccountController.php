<?php



namespace app\modules\mch\controllers\princeaccount;

use app\modules\mch\controllers\Controller;
use app\modules\mch\models\princeaccount\CashListForm;
use app\modules\mch\models\princeaccount\CashSubmitForm;
use app\modules\mch\models\princeaccount\LogListForm;
use app\modules\mch\models\princeaccount\PrinceStoreAccountSettingForm;
use app\models\PrinceStoreAccount;
use app\models\User;
use app\models\PrinceConfig;

class AccountController extends Controller
{
    public function actionCash()
    {
        if (\Yii::$app->request->isPost) {
            $form = new CashSubmitForm();
            $form->attributes = \Yii::$app->request->post();
            $form->store_id = $this->store->id;
            return $form->save();
        } else {
            $form = new CashListForm();
            $form->attributes = \Yii::$app->request->get();
            $form->store_id = $this->store->id;
            $res = $form->search();
			$account = PrinceStoreAccount::findOne(['store_id' =>$this->store->id]);
			
			$config = PrinceConfig::findOne(['code' => 'apply_rate', 'store_id' =>0]);
			$apply_rate=$config->value;
			$apply_rate=$apply_rate>0?$apply_rate:0;
			
            return $this->render('cash', [
                'list' => $res['data']['list'],
                'pagination' => $res['data']['pagination'],
                'account_money' => $account->money?$account->money:0,
                'apply_rate' => $apply_rate,
				'apply_fee' => $apply_rate,
				'apply_get' => 100-$apply_rate,
                'type_list'=>\Yii::$app->serializer->encode($form->getSetting())
            ]);
        }
    }

    public function actionLog()
    {
        $form = new LogListForm();
        $form->store_id = $this->store->id;
        $form->date_start = \Yii::$app->request->get('date_start');
		$form->date_end = \Yii::$app->request->get('date_end');
        $form->attributes = \Yii::$app->request->get();
        $arr = $form->search();
        return $this->render('log', [
            'list' => $arr['list'],
            'pagination' => $arr['pagination']
        ]);
    }
	
    public function actionSetting()
    {
        $model = PrinceStoreAccount::findOne([
            'store_id' => $this->store->id,
        ]);
        if (!$model) {
            $model=new PrinceStoreAccount();
			$model->store_id=$this->store->id;
        }
        if (\Yii::$app->request->isPost) {
            $form = new PrinceStoreAccountSettingForm();
            $form->model = $model;
            $form->attributes = \Yii::$app->request->post();
            return $form->save();
        } else {
            return $this->render('setting', [
                'model' => $model,
            ]);
        }
    }
	
    public function actionUser()
    {
                $keyword = trim(\Yii::$app->request->get('keyword'));
                $query = User::find()
                    ->alias('u')
                    ->where(['store_id' =>$this->store->id]);
                if ($keyword) {
                    $query->andWhere(['LIKE', 'u.nickname', $keyword]);
                }
                $list = $query->select('u.id,u.nickname,u.avatar_url')->asArray()
                    ->limit(20)->orderBy('u.nickname')->all();
                return [
                    'code' => 0,
                    'data' => $list,
                ];
    }
}
