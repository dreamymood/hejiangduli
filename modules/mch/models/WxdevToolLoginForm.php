<?php
/**
 * Created by IntelliJ IDEA.
 * User: Administrator
 * Date: 2017/12/20
 * Time: 10:13
 */

namespace app\modules\mch\models;

use app\opening\Cloud;
use yii\helpers\VarDumper;

class WxdevToolLoginForm extends MchModel
{
    public $store_id;
    public $appid;
    public $branch;
    public function rules()
    {
        return [
            [['store_id', 'appid'], 'trim'],
            [['store_id', 'appid'], 'required'],
        ];
    }

    public function getResult()
    {
        $token = \Yii::$app->session->get('wxdev_token');
        if (!$token) {
            $token = \Yii::$app->security->generateRandomString();
            \Yii::$app->session->set('wxdev_token', $token);
        }
        $api_root = \Yii::$app->urlManager->scriptUrl . "?store_id={$this->store_id}&r=api/";
        $api_root = str_replace('role.php', 'index.php', $api_root);
        $url = Cloud::$cloud_server_prefix . Cloud::$cloud_server_host . '/xcx_login.php';
        $curl = Cloud::apiGet($url, [
            'api_root' => $api_root,
            'store_id' => $this->store_id,
            'appid' => $this->appid,
            'token' => $token,
            'branch' => $this->branch,
        ]);
        if ($curl->http_status_code != 200) {
            return [
                'code' => 1,
                'msg' => '请求出错：' . $curl->error_message,
            ];
        }
        return $curl->response;
    }
}
