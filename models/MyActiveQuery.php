<?php
/**
 *
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/8/11
 * Time: 10:14
 */

namespace app\models;


use yii\db\ActiveQuery;

class MyActiveQuery extends ActiveQuery
{
    public $myCondition = [];

    public function where($condition, $params = [])
    {
        if (count($this->myCondition) != 0) {
            $newCondition = $this->unsetArray($this->myCondition, $condition);
            if (count($newCondition) != 0) {
                parent::where($newCondition);
                return parent::andWhere($condition, $params);
            } else {
                return parent::where($condition, $params);
            }
        } else {
            return parent::where($condition, $params);
        }
    }

    public function andWhere($condition, $params = [])
    {
        if($this->where === null){
            if (count($this->myCondition) != 0) {
                $newCondition = $this->unsetArray($this->myCondition, $condition);
                parent::andWhere($newCondition);
            }
        }else{
            $where = $this->where;
            if(isset($where[1])){
                $arr = $where[1];
            }else{
                $arr = $where;
            }
            $newCondition = $this->unsetArray($arr, $condition);
            parent::where($newCondition);
            foreach($where as $key=>$value){
                if($key <= 1){
                    continue;
                }
                parent::andWhere($value);
            }
        }
        return parent::andWhere($condition, $params); // TODO: Change the autogenerated stub
    }

    /**
     * @param $arr
     * @param $condition // 四种情况 1、'is_show = 1' 字符串 2、['is_show'=>1] 键值对数组  3、['!=','is_show',1]数组 4、['or',['is_show'=>1],'is_show = 0',['!=','is_show',1]];
     * @return array
     */
    private function unsetArray($arr, $condition)
    {
        $newCondition = [];
        foreach ($arr as $key => $item) {
            if (is_string($condition)) {
                if ($this->newStrstr($condition, $key) == false) {
                    $newCondition[$key] = $item;
                }
            } else if (is_array($condition)) {
                $arr = [$key, $item];
                $ok = $this->conditionArray($arr, $condition);
                if ($ok) {
                    $newCondition[$key] = $item;
                }
            } else {
                $newCondition[$key] = $item;
            }
        }
        return $newCondition;
    }

    private function conditionArray($arr, $condition)
    {
        $ok = true;
        $type = 0;
        foreach ($condition as $k => $v) {
            if (is_numeric($k)) {
                if ($k == 0) {
                    if (in_array($v, ['or', 'and', 'Or', 'OR', 'AND', 'And'])) {
                        $type = 0;
                    } else {
                        $type = 1;
                    }
                }
            } else {
                $type = 2;
                if ($this->newStrstr($k, $arr[0])) {
                    $ok = false;
                    break;
                }
            }
            if ($type == 0) {
                if ($k == 0) {
                    continue;
                }
                if (is_array($v)) {
                    $okA = $this->conditionArray($arr, $v);
                    if ($okA == false) {
                        $ok = false;
                        break;
                    }
                } else {
                    if ($k == 1 && $this->newStrstr($v, $arr[0])) {
                        $ok = false;
                        break;
                    }
                }
            } elseif ($type == 1) {
                if (isset($condition[1]) && is_string($condition[1])) {
                    if ($this->newStrstr($condition[1], $arr[0])) {
                        $ok = false;
                        break;
                    }
                }
            }
        }
        return $ok;
    }

    // 比较
    private function newStrstr($a, $b)
    {
        return strstr($a, $b);
    }
}