<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/5/5
 * Time: 11:45
 */

namespace app\modules\mch\models\princeaccount;

use app\models\PrinceAccountLog;
use app\modules\user\models\UserModel;
use yii\data\Pagination;

class LogListForm extends UserModel
{
    public $store_id;

    public $limit;
    public $page;
    public $type;
    public $date_start;
    public $date_end;

    public function rules()
    {
        return [
            [['limit', 'page', 'type'], 'integer'],
            [['limit'], 'default', 'value' => 20],
            [['date_start', 'date_end',], 'trim']
        ];
    }

    public function search()
    {
        if (!$this->validate()) {
            return $this->errorResponse;
        }
        $query = PrinceAccountLog::find()->where([
            'store_id' => $this->store_id
        ]);

        if ($this->type) {
            $query->andWhere(['type' => $this->type]);
        }

        if ($this->date_start) {
            $query->andWhere(['>=', 'addtime', strtotime($this->date_start)]);
        }
        if ($this->date_end) {
            $query->andWhere(['<=', 'addtime', strtotime($this->date_end) + 86400]);
        }


        $count = $query->count();
        $pagination = new Pagination(['totalCount' => $count, 'pageSize' => $this->limit]);
        $list = $query->limit($pagination->limit)->offset($pagination->offset)
            ->orderBy(['addtime' => SORT_DESC])->asArray()->all();

        return [
            'list' => $list,
            'pagination' => $pagination
        ];
    }
}
