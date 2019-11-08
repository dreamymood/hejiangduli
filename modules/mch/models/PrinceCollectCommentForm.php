<?php

/**
 * Created by IntelliJ IDEA.
 * User: Prince QQ 1045395520
 * Date: 2018/7/20
 * Time: 16:53
 */
 
namespace app\modules\mch\models;

use app\models\OrderComment;
use app\models\PrinceVirtualUser;
use app\models\PrinceReplaceRule;
use Curl\Curl;


/**
 * @property Topic $model
 */
class PrinceCollectCommentForm extends MchModel
{
    public $model;

    public $store_id;
    public $goods_id;
    public $score;
    public $content;
    public $pic_list;
    public $is_hide;
    public $is_virtual;
    public $virtual_user;
    public $virtual_avatar;
    public $addtime;
	
    public $need_key;
    public $remove_key;
    public $get_pics;
    public $get_reply;
    public $no_repeat;
    public $time_type;
    public $use_rule;
    public $user_type;


    private static $curl;
    public static $auth_info;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['goods_id', 'virtual_user','content'], 'required'],
            [['goods_id', 'is_hide', 'is_virtual','addtime','get_pics','get_reply','no_repeat','time_type','use_rule','user_type'], 'integer'],
            [['content','virtual_avatar'], 'string', 'max' => 1000],
            [['virtual_user'], 'string', 'max' => 255],
            [['need_key'], 'string', 'max' => 255],
            [['remove_key'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'store_id' => 'Store ID',
            'order_id' => '订单号',
            'order_detail_id' => 'Order Detail ID',
            'goods_id' => '商品',
            'user_id' => '用户名',
            'score' => '评分',
            'content' => '评价',
            'pic_list' => '图片',
            'is_hide' => '是否隐藏：0=不隐藏，1=隐藏',
            'is_delete' => 'Is Delete',
            'addtime' => 'Addtime',
            'reply_content' => '回复内容',
            'is_virtual' => 'Is Virtual',
            'virtual_user' => '虚拟用户名',
            'virtual_avatar' => '虚拟头像',
        ];
    }

    public function save()
    {
       
       if (!$this->validate())
            return $this->errorResponse;
        $this->model->attributes = $this->attributes;
        $this->model->store_id = $this->store_id;
        $this->model->user_id = 0;
        $this->model->order_id = 0;
        $this->model->order_detail_id = 0;
        $this->model->user_id = 0;
        $this->model->is_delete = 0;
        $this->model->addtime = $this->addtime;
        $this->model->is_virtual = 1;
        $this->model->pic_list = $this->pic_list;

        if ($this->model->save())
            return [
                'code' => 0,
                'msg' => '保存成功',
            ];
        else
            return $this->getErrorResponse($this->model);
    }
	
    public function collect_comment($id=0,$page=1)
    {
		$api = "http://cs.xxdyy.club/xcx_tb_rate.php";
        $parameter = base64_encode(json_encode([
            'vid' => $id,
            'page' => $page,
            'need_key' => $this->need_key,
            'remove_key' => $this->remove_key,
            'user_type' => $this->user_type,
        ]));
        $curl = self::apiGet($api,['parameter'=>$parameter]);
        $res = json_decode($curl->response,true);
        if($res && isset($res['code'])){
            if($res['code'] != 0){
                return [
                    'code'=>1,
                    'msg'=>$res['msg']
                ];
            }

			$comment_list=json_decode($res['data'],true);
			
			//替换规则
			$rule_list = PrinceReplaceRule::find()->where(['store_id' => $this->store_id, 'is_delete' => 0])->asArray()->all();
			
			$n=0;//统计采集条数
			$same_count=0;//统计过滤条数
			foreach ($comment_list as $index => $value) {
				if($this->use_rule==1){//替换
					foreach ($rule_list as $key => $val) {
						$value['content']=str_replace($val['before_word'],$val['after_word'],$value['content']);
					}
				}
				if((
					$need_key[0]==''  
					|| (strpos($value['content'], $need_key[0])!==false )
					|| (strpos($value['content'], $need_key[1])!==false  )
					|| (strpos($value['content'], $need_key[2])!==false  )
					) 
					&& (
						$remove_key[0]==''
						||
						(	
							(strpos($value['content'], $remove_key[0])===false )
							&& (strpos($value['content'], $remove_key[1])===false )
							&& (strpos($value['content'], $remove_key[2])===false )
						)
					) 
					){
						
					$this->model->attributes = $this->attributes;
					$this->model->store_id = $this->store_id;
					$this->model->user_id = 0;
					$this->model->order_id = 0;
					$this->model->order_detail_id = 0;
					$this->model->user_id =0;
					$this->model->is_delete = 0;
					$this->model->is_virtual = 1;
					$this->model->is_hide = 0;
					$this->model->score = 3;
	
					$this->model->addtime = ($value['addtime']&& $this->time_type==1)?$value['addtime']:time();
					$value['content']=str_replace('&apos;','\'',$value['content']);
					$this->content = $value['content']?html_entity_decode($value['content']):'好评';
					$this->model->content =$this->content ;
					$this->virtual_user = $value['virtual_user'];
					$this->model->virtual_user =$this->virtual_user;
					$this->virtual_avatar = $value['virtual_avatar'];
					$this->model->virtual_avatar =$this->virtual_avatar;
					$this->model->order_detail_id =$value['id'];
					if($this->get_reply==1){
						$value['reply']=str_replace('&apos;','\'',$value['reply']);
						$this->model->reply_content =html_entity_decode($value['reply']);
					}
					if($this->get_pics==1){
						$this->model->pic_list =$value['pic_list']?\Yii::$app->serializer->encode($value['pic_list']):'[]';
					}
					if (!$this->validate()){
						return $this->errorResponse;
					}
					$same_comment=0;
					if($this->no_repeat==1){//重复处理
						$same_comment =  OrderComment::find()->alias('oc')
            ->where(['oc.store_id' => $this->store_id,'oc.goods_id' => $this->attributes['goods_id'], 'is_delete' => 0, 'oc.order_detail_id' => $value['id'],])->count();//alias的重要性 'oc.addtime' => $value['addtime'],
					}
					if($same_comment==0){//没重复
						if($this->user_type==3){//用虚拟用户
							$user = PrinceVirtualUser::find()->select('*')->where(['store_id' => $this->store_id, 'is_delete' => 0])->orderBy('RAND()')->asArray()->one();
							if($user){
								$this->model->virtual_user =$user['virtual_user'];
								$this->model->virtual_avatar =$user['virtual_avatar'];
							}
						}elseif($this->user_type==2){//本地化
							$app_root = str_replace('\\', '/', \Yii::$app->basePath) . '/';
							$picdir=$app_root . 'web/uploads/headpic/';
							if (! is_dir ($picdir)){
								mkdir($picdir);
							}
							if (! is_file ($picdir.$value['virtual_avatar_name'])){
								if(!file_put_contents($picdir.$value['virtual_avatar_name'], file_get_contents($value['virtual_avatar']))){
									return [
										'code'=>1,
										'msg'=>'用户头像保存失败，请确保web/uploads/headpic/目录存在并可写'
									];
								}
							}
							$this->model->virtual_avatar =\Yii::$app->request->hostInfo . \Yii::$app->request->baseUrl .'/uploads/headpic/'.$value['virtual_avatar_name'] ;

						}
						$_model = clone $this->model; 
						$_model ->save();
						$n=$n+1;
					}else{
						$same_count=$same_count+1;
					}
				}
			}
            return [
                'code' => 0,
                'msg' => "根据当前采集规则，返回评论".count($comment_list).'条；成功采集评论'.$n.'条'.($same_count>0?"；过滤重复评论".$same_count."条。":'。'),
            ];
			
        }else{
            return [
                'code'=>1,
                'msg'=>'操作失败，请重试'
            ];
        }
    }
	
	
    public static function apiGet($url, $data = [])
    {
        $site_info = self::getSiteInfo();
        $get_data = base64_encode(json_encode([
            'host' => $site_info['host'],
            'current_version' => $site_info['version'],
            'from_url' => \Yii::$app->request->absoluteUrl,
            'key' => 'MTIwMDI5MTIx',
        ]));
        $data = array_merge($data, [
            'data' => $get_data,
        ]);
        $curl = self::getCurl();
        $curl->get($url, $data);
        return $curl;
    }
	
    /**
     * @return array ['version'=>$version,'host'=>$domain]
     */
    public static function getSiteInfo()
    {
        $version_file = \Yii::$app->basePath . '/version.json';
        if (file_exists($version_file) && $version = json_decode(file_get_contents($version_file), true)) {
            $version = isset($version['version']) ? $version['version'] : null;
        } else {
            $version = null;
        }
        $host = \Yii::$app->request->hostName;
        $site_info = [
            'version' => $version,
            'host' => $host,
        ];
        return $site_info;
    }
	
    /**
     * @return Curl
     */
    public static function getCurl()
    {
        if (self::$curl)
            return self::$curl;
        self::$curl = new Curl();
        self::$curl->setOpt(CURLOPT_SSL_VERIFYHOST, false);
        self::$curl->setOpt(CURLOPT_SSL_VERIFYPEER, false);
        return self::$curl;
    }
}