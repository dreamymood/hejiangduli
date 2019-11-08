<?php

namespace app\models\prince;

use app\models\PrinceConfig;
use app\modules\mch\models\MchModel;

class PrinceConfigForm extends MchModel
{
    public $store_id;
	
    public $need_key;
    public $remove_key;
    public $get_pics;
    public $get_reply;
    public $no_repeat;
    public $time_type;
    public $use_rule;
    public $user_type;
    public $price_rate;
    public $apply_rate;
    public $cloud_store_id;


    public function getCodeNames()
    {

		$class = new \ReflectionClass($this);
        $props = array_filter($class->getProperties(), function (\ReflectionProperty $p) {
            return $p->class == __CLASS__;
        });
        $props = array_map(function (\ReflectionProperty $v) {
            return $v->getName();
        }, $props);
        $props = array_diff($props, ['store_id']);
        return $props;
    }

    public function rules()
    {
		return [
            [$this->getCodeNames(), 'trim'],
        ];
    }

    /**
     * 获取此实例
     *
     * @param int|string $store_id
     * @return static
     */
    public static function get($store_id,$codegroup)
    {
        $instance = new static();
        $instance->store_id = $store_id;

        $confs = PrinceConfig::findAll(
            ['store_id' => $store_id,'codegroup' => $codegroup]
        );

        foreach ($confs as $k => $conf) {
            $key = $conf->code;
            $value = $conf->value;
            $instance->$key = $value;
        }
        return $instance;
    }

    public function save($codegroup='')
    {
        $trans = \Yii::$app->db->beginTransaction();
        try {
            foreach ($this->attributes as $key => $value) {
                $conf = PrinceConfig::findOne(
                    ['store_id' => $this->store_id, 'code' => $key]
                );
                if ($conf == null) {
                    $conf = new PrinceConfig();
                }

                $conf->value = $value;
                $conf->code = $key;
                $conf->store_id = $this->store_id;
                $conf->codegroup = $codegroup;

                $conf->save();
            }
            $trans->commit();
            return [
                'code' => 0,
                'msg' => '保存成功！',
            ];
        } catch (\yii\db\Exception $ex) {
            $trans->rollBack();
            throw $ex;
        }
    }
}
