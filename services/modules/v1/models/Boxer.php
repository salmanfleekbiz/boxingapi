<?php

namespace services\modules\v1\models;

use yii\base\ErrorException;
use yii\base\Exception;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

class Boxer extends Base
{

    public static function tableName()
    {
        return '{{%boxers}}';
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

    public function get_allboxers(){

        $result = (new \yii\db\Query())->select(['id','name','match_count','win_count','loss_count','nr_count','knockout_count','status'])->from('boxers')->where(['status' => 1])->all();
        return $result;
    }

    public function add_newboxer($param)
    {
        $check_name = (new \yii\db\Query())->select(['name'])->from('boxers')->where(['name' => $param['boxersName']])->one();
        if($check_name == ''){          
        $insert = self::getDb()->createCommand()->insert('boxers', [
                        'name' => $param['boxersName'],
                        'match_count' => $param['totalmatch'],
                        'win_count' => $param['winmatch'],
                        'loss_count' => $param['lossmatch'],
                        'nr_count' => $param['nrmatch'],
                        'knockout_count' => $param['knockoutmatch'],
                        'status' => $param['status'],
                        'created_at' => new Expression('NOW()'),
                        'updated_at' => new Expression('NOW()'),
                    ])->execute();

        return ['result' => 'Boxer add successfully.'];
        }else{
            return ['result' => 'Boxer Name Already exist.'];
        }
    }

    public function get_boxer($id){        
       $result = (new \yii\db\Query())->select(['id','name','match_count','win_count','loss_count','nr_count','knockout_count','status'])->from('boxers')->where(['id' => $id])->one();
        return $result;
    }

    public function updateboxer($param){

       $user=self::getDb()->createCommand()->update('boxers', ['name' => $param['name'],'match_count' => $param['matchcount'],'win_count' => $param['wincount'],'loss_count' => $param['losscount'],'nr_count' => $param['nrcount'],'knockout_count' => $param['knockoutcount']], 'id = '.$param['id'])
         ->execute();    
         return ['update'=>'true','message'=>'boxer update successfully'];
    }
}