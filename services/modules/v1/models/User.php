<?php

namespace services\modules\v1\models;

use yii\base\ErrorException;
use yii\base\Exception;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

class User extends Base
{

    public static function tableName()
    {
        return '{{%users}}';
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

    public function alluser(){
        
        $result = self::find()
            ->select('first_name,last_name,email,phone')
            ->asArray()
            ->all();
        return $result;
    }

    public function getAgentId($user_id)
    {
        $result = (new \yii\db\Query())->select(['id','first_name','last_name','phone','email','pwd_txt'])->from('users')->where(['id' => $user_id])->one();
        return $result;
    }

    public function checkUserExist($param){
        $check_email = (new \yii\db\Query())->select(['email'])->from('users')->where(['email' => $param['email']])->one();
        if($check_email == ''){
            return ['result' => 'continuesignup'];
        }else{
            return ['result' => 'Email Already exist.'];
        }
    }

    public function sigUpUser($param)
    {          
        $insert = self::getDb()->createCommand()->insert('users', [
                        'first_name' => $param['fname'],
                        'last_name' => $param['lname'],
                        'email' => $param['email'],
                        'password' => MD5($param['password']),
                        'pwd_txt' => $param['password'],
                        'phone' => $param['phone'],
                        'device_id' => $param['devicename'],
                        'status' => $param['status'],
                        'created_at' => new Expression('NOW()'),
                        'updated_at' => new Expression('NOW()'),
                    ])->execute();

        $userId = (new \yii\db\Query())->select(['id'])->from('users')->where(['email' => $param['email']])->one();

        $insert_role = self::getDb()->createCommand()->insert('user_roles', [
                        'user_id' => $userId['id'],
                        'role_id' => 2,
                        'created_at' => new Expression('NOW()'),
                        'updated_at' => new Expression('NOW()'),
                    ])->execute();
        return ['result' => 'User Signup successfully.'];
    }

    public function userlogin($param){

        $userData = (new \yii\db\Query())->select(['id','email','password'])->from('users')->where(['email' => $param['email']])->one();
        if($userData == ''){
                return ['login'=>'false'];
        }else{
                if($userData['email'] == $param['email'] && $userData['password'] == MD5($param['password'])){
                        return ['login'=>'true','userid'=>$userData['id']];
                }else{
                        return ['login'=>'false'];
                }
        }
    }

    public function usersforget($param){
        $emailforget = (new \yii\db\Query())->select(['email','pwd_txt'])->from('users')->where(['email' => $param['email']])->one();    
         if($emailforget == ''){
                 return ['isEmail'=>'false'];
         }else{

         }       return ['isEmail'=>'true','email'=>$emailforget['email'],'pass'=>$emailforget['pwd_txt']];
    }

    public function updateuser($param){

            $old_password_match = (new \yii\db\Query())->select(['password'])->from('users')->where(['id' => $param['id']])->one();
            if($param['new_password'] != '' && MD5($param['password']) == $old_password_match['password']){
                $user=self::getDb()->createCommand()->update('users', ['first_name' => $param['fname'],'last_name' => $param['lname'],'phone' => $param['phone'],'password' => MD5($param['new_password']),'pwd_txt' => $param['new_password']], 'id = '.$param['id'])
             ->execute();
                    return ['update'=>'true','message'=>'profile update successfully'];
            }else if($param['new_password'] != '' && MD5($param['password']) != $old_password_match['password']){
                    return ['update'=>'false','message'=>'Password not match'];
            }else{
            $user=self::getDb()->createCommand()->update('users', ['first_name' => $param['fname'],'last_name' => $param['lname'],'phone' => $param['phone']], 'id = '.$param['id'])
             ->execute();    
             return ['update'=>'true','message'=>'profile update successfully'];
         }
    }

    public function insertdeviceid($param){

           $checkUser = (new \yii\db\Query())->select(['id'])->from('users')->where(['device_id' => $param['devicename']])->one();

         if($checkUser != ''){
                 return ['issign'=>'false','User Already Exist','userId'=>$checkUser['id']];
         }else{
                   $insert = self::getDb()->createCommand()->insert('users', [
                        'first_name' => '',
                        'last_name' => '',
                        'email' => '',
                        'password' => '',
                        'pwd_txt' => '',
                        'phone' => '',
                        'device_id' => $param['devicename'],
                        'status' => 1,
                        'created_at' => new Expression('NOW()'),
                        'updated_at' => new Expression('NOW()'),
                    ])->execute();
        $lastId = self::getDb()->getLastInsertID();
                   
        $userId = (new \yii\db\Query())->select(['id'])->from('users')->where(['device_id' => $param['devicename']])->one();

        $insert_role = self::getDb()->createCommand()->insert('user_roles', [
                        'user_id' => $userId['id'],
                        'role_id' => 2,
                        'created_at' => new Expression('NOW()'),
                        'updated_at' => new Expression('NOW()'),
                    ])->execute();
                return ['issign'=>'true','message'=>'User Signup successfully','userId'=>$lastId];
         } 
    }
}