<?php

namespace services\modules\v1\models;

use yii\base\ErrorException;
use yii\base\Exception;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

class Channel extends Base
{

    public static function tableName()
    {
        return '{{%channels}}';
    }

    public static function primaryKey()
    {
        return ['id'];
    }

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'createdAtAttribute' => 'created',
                'updatedAtAttribute' => 'modified',
                'value' => new Expression('NOW()'),
            ],
        ];
    }

    public function get_allchannel(){

        $result = (new \yii\db\Query())->select(['id','name'])->from('ref_channels')->where(['is_active' => 1])->all();
        return $result;
    }

    public function channelAdd($param)
    {          
        $check_name = (new \yii\db\Query())->select(['name'])->from('ref_channels')->where(['name' => $param['channelname']])->one();
        if($check_name == ''){
        $insert = self::getDb()->createCommand()->insert('ref_channels', [
                        'name' => $param['channelname'],
                        'is_active' => $param['is_active'],
                        'created_at' => new Expression('NOW()'),
                        'updated_at' => new Expression('NOW()'),
                    ])->execute();

            return ['result' => 'Channel add successfully.'];

        }else{
            return ['result' => 'Name Already exist.'];
        }
    }

    public function get_channel($id){        
       $result = (new \yii\db\Query())->select(['id','name','is_active'])->from('ref_channels')->where(['id' => $id])->one();
        return $result;
    }

    public function updatechannel($param){

       $user=self::getDb()->createCommand()->update('ref_channels', ['name' => $param['name'],'is_active' => $param['active']], 'id = '.$param['id'])
         ->execute();    
         return ['update'=>'true','message'=>'channel update successfully'];
    }
}