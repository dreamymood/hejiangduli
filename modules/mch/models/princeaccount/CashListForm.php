<?php



namespace app\modules\mch\models\princeaccount;

use app\models\PrinceStoreCash;
use app\models\Option;
use app\modules\user\models\UserModel;
use yii\data\Pagination;

class CashListForm extends UserModel
{
    public $status;
    public $year;
    public $month;
    public $store_id;

    public function rules()
    {
        return [
            [['status', 'year', 'month',], 'integer'],
        ];
    }

    public function search()
    {
        if (!$this->validate()) {
            return $this->errorResponse;
        }
        $query = PrinceStoreCash::find()->where([
            'store_id' => $this->store_id,
        ]);
        if ($this->status) {
            $query->andWhere(['status' => $this->status]);
        }
        $count = $query->count();
        $pagination = new Pagination(['totalCount' => $count,]);
        $list = $query->limit($pagination->limit)->offset($pagination->offset)->orderBy('addtime DESC')
            ->asArray()->all();
        foreach ($list as &$item) {
            $item['addtime'] = date('Y-m-d H:i:s', $item['addtime']);
        }
        return [
            'code' => 0,
            'data' => [
                'list' => $list,
                'pagination' => $pagination,
            ],
        ];
    }

    public function getSetting()
    {
        $default = [
            'entry_rules' => '',
            'type' => []
        ];
        $data = Option::get('mch_setting', $this->store_id, 'mch', $default);
        $newList = [];
        if (is_array($data['type'])) {
            foreach ($data['type'] as $item) {
                $newItem = [];
                switch ($item) {
                    case 1:
                        $newItem['id'] = 1;
                        $newItem['name'] = "微信线下转账";
                        break;
                    case 2:
                        $newItem['id'] = 2;
                        $newItem['name'] = "支付宝线下转账";
                        break;
                    case 3:
                        $newItem['id'] = 3;
                        $newItem['name'] = "银行卡转账";
                        break;
                    case 4:
                        $newItem['id'] = 4;
                        $newItem['name'] = "提现到余额";
                        break;
                    default:
                        $newItem = [];
                        break;
                }
                $newList[] = $newItem;
            }
        }
        return $newList;
    }
}
