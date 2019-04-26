<?php

namespace services\modules\v1\models;

use yii\base\ErrorException;
use yii\base\Exception;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

class Reminder_subscribers extends Base
{

    public static function tableName()
    {
        return '{{%reminder_subscribers}}';
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

    public function add_newReminder($param){
         
         $insert = self::getDb()->createCommand()->insert('reminder_subscribers', [
                        'user_id' => $param['userid'],
                        'match_id' => $param['matchid'],
                        'preferred_date' => $param['matchdate'],
                        'status' => $param['status'],
                        'created_at' => new Expression('NOW()'),
                        'updated_at' => new Expression('NOW()'),
                    ])->execute();

         return ['reminder'=>'true','message' => 'Reminder add successfully.'];
    }

    public function check_Reminder($param){
         
         $result = (new \yii\db\Query())->select(['id'])->from('reminder_subscribers')->where(['user_id' => $param['userId'],'match_id' => $param['matchId']])->one();
         if($result == ''){
            return ['subscribe'=>'true','message' => 'Reminder not set'];
         }else{
            return ['subscribe'=>'false','message' => 'Reminder already set'];
         }
    }

    public function reminder_users($param){

        $where = 'reminder_subscribers.match_id = '.$param['matchid'];
        $result = self::find()
            ->select('reminder_subscribers.id AS subscriber_id,reminder_subscribers.user_id,user.device_id AS device')
            ->leftJoin('users user', 'reminder_subscribers.user_id = user.id')
            ->where($where)
            ->asArray()
            ->all();
        return $result;
    }
}