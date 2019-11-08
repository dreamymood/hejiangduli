<?php

/**
 * Created by IntelliJ IDEA.
 * User: Prince QQ 1045395520
 * Date: 2018/7/20
 * Time: 16:53
 */

namespace app\modules\mch\controllers\princecloud;

use app\models\Goods;
use app\models\OrderComment;
use app\models\User;
use app\models\PrinceVirtualUser;
use app\models\PrinceReplaceRule;
use app\modules\mch\models\PrinceCollectCommentForm;
use app\modules\mch\models\PrinceVirtualUserForm;
use app\modules\mch\models\PrinceReplaceRuleForm;
use app\models\prince\PrinceConfigForm;
use yii\data\Pagination;
use yii\helpers\Html;
use app\modules\mch\controllers\Controller;

class SettingController extends Controller
{
	//采集设置
    public function actionIndex()
    {
        $form = PrinceConfigForm::get($this->store->id,'cloud');
        if (\Yii::$app->request->isPost) {
            $conf = new PrinceConfigForm();
            $conf->store_id = $this->store->id;
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
