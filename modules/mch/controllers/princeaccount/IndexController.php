<?php


namespace app\modules\mch\controllers\princeaccount;

use app\opening\ApiCode;
use app\models\DistrictArr;
use app\models\Mch;
use app\models\MchCommonCat;
use app\models\User;
use app\modules\mch\controllers\Controller;
use app\modules\mch\models\princeaccount\admin\CashConfirmForm;
use app\modules\mch\models\princeaccount\admin\CashListForm;
use app\modules\mch\models\mch\CommonCatEditForm;
use app\modules\mch\models\mch\MchAddForm;
use app\modules\mch\models\mch\MchEditForm;
use app\modules\mch\models\mch\MchListForm;
use app\modules\mch\models\mch\MchSettingForm;
use app\modules\mch\models\mch\OneMchSettingForm;
use app\modules\mch\models\mch\ReportFormsForm;
use app\models\prince\PrinceConfigForm;

class IndexController extends Controller
{

    public function actionCash()
    {
        $get = \Yii::$app->request->get();
        if (!isset($get['status']) || $get['status'] === null || $get['status'] === '') {
            $get['status'] = -1;
        }

        $form = new CashListForm();
        $form->attributes = $get;
        $res = $form->search();
        return $this->render('cash', [
            'get' => $get,
            'list' => isset($res['data']['list']) ? $res['data']['list'] : [],
            'pagination' => isset($res['data']['pagination']) ? $res['data']['pagination'] : null,
        ]);
    }

    public function actionCashSubmit()
    {
        $form = new CashConfirmForm();
        $form->attributes = \Yii::$app->request->get();

        return $form->save();
    }


    public function actionSetting()
    {
        $form = PrinceConfigForm::get(0,'cloud');
        if (\Yii::$app->request->isPost) {
            $conf = new PrinceConfigForm();
            $conf->store_id = 0;
            $conf->attributes = \Yii::$app->request->post();
            return $conf->save('cloud');
        } else {
            $newData = [];
            foreach ($form as $k => $item) {
                $newData[$k] = $item;
            }
            return $this->render('setting', [
                'model' => $newData,
            ]);
        }
    }
}
