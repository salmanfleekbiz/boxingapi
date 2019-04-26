<?php
namespace services\modules\v1\controllers;

use Aws\Api\Service;
use Yii;
use services\modules\v1\models\User;
use yii\db\Expression;
use yii\web\NotFoundHttpException;


class UserController extends BaseController
{
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['authenticator']['except'] = ['allusers','usersignup','userlogin','updateuserdata','deviceid','contactus'];
        return $behaviors;
    }

    public function actionGetuser()
    {
        $id = Yii::$app->request->get('id');
        $model = new User();
        $order =  $model->getAgentId($id);
        return $order;
    }

    public function actionAllusers(){
        $all_users = new User();
        $result = $all_users->alluser();
        return $result;
    }

    public function actionUsersignup()
    {
        $param = Yii::$app->request->post();
        $insert_model = new User();
        $result = $insert_model->checkUserExist($param);
        if($result['result'] == 'continuesignup'){
            $success = $insert_model->sigUpUser($param);

            $to = 'info@clients3.5stardesigners.net';
            $subject = "Thank you for registering";
            $body   = '<div style="font-family: Arial, Helvetica, Sans-serif; font-size: 12px;">';
            $body .= '<p>Dear User,<br/>Thank you for registering to Ring Reminders. We look forward to providing you with best of our service.<br/><br/>Have a good day!<br/><br/> Thank you. </p>';
            
            $body .= '<table style="width: 50%;">';
            $body .= '<tr>';
            $body .= '<td style="padding:13px 5px;">User Email:</td><td style="padding:13px 5px;">'.$param['email'].'</td>';
            $body .= '</tr>';
            $body .= '<tr>';
            $body .= '<td style="padding:13px 5px;">Password:</td><td style="padding:13px 5px;">'.$param['password'].'</td>';
            $body .= '</tr>';
            $body .= '</table>';
            $headers  = 'MIME-Version: 1.0' . "\r\n";
            $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
            $headers .= 'From: sales@boxingapp.com.au' . "\r\n" .'Reply-To: sales@boxingapp.com.au';
            $sent = mail($to,$subject,$body,$headers);

            return ['signup'=>'true','message'=>'User Signup successfully'];
        }else{  

            return ['signup'=>'false','message'=>'Email Already Exist'];
        }
   }

   public function actionUserlogin(){

        $param = yii::$app->request->post();
        $checklogin = new User();
        $check_user_login = $checklogin->userlogin($param);
        return $check_user_login;
   }

   public function actionUserforget(){

        $param = yii::$app->request->post();
        $forgetemail = new User();
        $result = $forgetemail->usersforget($param);
        if($result['isEmail'] == 'true'){
            $to = 'info@clients3.5stardesigners.net';
            $subject = "Forgot your password";
            $body   = '<div style="font-family: Arial, Helvetica, Sans-serif; font-size: 12px;">';
            $body .= '<p>Dear User,<br/>Your password is given below.<br/> Contact us if further help is required. </p>';
            $body .= '<table style="width: 50%;">';
            $body .= '<tr>';
            $body .= '<td style="padding:13px 5px;">User Email:</td><td style="padding:13px 5px;">'.$result['email'].'</td>';
            $body .= '</tr>';
            $body .= '<tr>';
            $body .= '<td style="padding:13px 5px;">Password:</td><td style="padding:13px 5px;">'.$result['pass'].'</td>';
            $body .= '</tr>';
            $body .= '</table>';
            $headers  = 'MIME-Version: 1.0' . "\r\n";
            $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
            $headers .= 'From: sales@boxingapp.com.au' . "\r\n" .'Reply-To: sales@boxingapp.com.au';
            $sent = mail($to,$subject,$body,$headers);
            return $result;
        }else{
            return $result;
        }
        //return $result;

   }

   public function actionUpdateuserdata(){
    
        $param = yii::$app->request->post();
        $update_user = new User();
        $users_updates = $update_user->updateuser($param);
        return $users_updates;
   }

   public function actionDeviceid(){
        $param = yii::$app->request->post();
        $device = new User();
        $device = $device->insertdeviceid($param);
        return $device;
   }

   public function actionContactus(){
        $param = yii::$app->request->post();

        $to = 'info@clients3.5stardesigners.net';
        $subject = "Contact Us";
        $body   = '<div style="font-family: Arial, Helvetica, Sans-serif; font-size: 12px;">';
        $body .= '<p>Dear User,<br/> Thank you for contacting us. Your response is forwarded. Our representative will soon respond back to your query.<br/> Thank you. </p>';
        $body .= '<table style="width: 50%;">';
        $body .= '<tr>';
        $body .= '<td style="padding:13px 5px;">Full Name:</td><td style="padding:13px 5px;">'.$param['name'].'</td>';
        $body .= '</tr>';
        $body .= '<tr>';
        $body .= '<td style="padding:13px 5px;">Phone Number:</td><td style="padding:13px 5px;">'.$param['phone_number'].'</td>';
        $body .= '</tr>';
        $body .= '<tr>';
        $body .= '<td style="padding:13px 5px;">Email Address:</td><td style="padding:13px 5px;">'.$param['email_address'].'</td>';
        $body .= '</tr>';
        $body .= '<tr>';
        $body .= '<td style="padding:13px 5px;">Message:</td><td style="padding:13px 5px;">'.$param['message'].'</td>';
        $body .= '</tr>';
        $body .= '</table>';
        $headers  = 'MIME-Version: 1.0' . "\r\n";
        $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
        $headers .= 'From: sales@boxingapp.com.au' . "\r\n" .'Reply-To: sales@boxingapp.com.au';
        $sent = mail($to,$subject,$body,$headers);

        return ['contact'=>'true','message'=>'Thankyou for contacting us.We will be contact you shortly.'];
   }
}