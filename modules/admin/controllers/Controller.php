<?php
/**
 * Created by IntelliJ IDEA.
 * User: Administrator
 * Date: 2017/10/2
 * Time: 13:43
 */

namespace app\modules\admin\controllers;

use app\opening\Cloud;
use app\modules\admin\behaviors\AdminBehavior;
use app\modules\admin\behaviors\LoginBehavior;
use app\modules\mch\models\MchMenu;

class Controller extends \app\controllers\Controller
{
    public $layout = 'main';

    public $auth_info;

    public function init()
    {
        parent::init();
        Cloud::checkAuth();
        $this->auth_info = Cloud::getAuthInfo();
    }


    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'permission' => [
                'class' => AdminBehavior::className(),
            ],
            'login' => [
                'class' => LoginBehavior::className(),
            ],
        ]);
    }

    /**
     * 获取当前用户拥有的插件权限
     * @return mixed|null
     */
    public function getUserAuth()
    {
        $userAuth = json_decode(\Yii::$app->admin->identity->permission, true);

        return $userAuth;
    }
}
