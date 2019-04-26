<?php
namespace services\modules\v1\controllers;

use Aws\Api\Service;
use Yii;
use services\modules\v1\models\Reminder_subscribers;
use services\modules\v1\models\Match;
use yii\db\Expression;
use yii\web\NotFoundHttpException;


class ReminderController extends BaseController
{
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['authenticator']['except'] = ['remindersubscriber','checkreminder','checkuserreminder','userdeviceids'];
        return $behaviors;
    }

   public function actionRemindersubscriber(){

        $param = yii::$app->request->post();
        //echo json_encode($param);
        $insert_model = new Reminder_subscribers();
        $result = $insert_model->add_newReminder($param);
        return $result;
   }

   public function actionCheckreminder(){
        
        $param = yii::$app->request->post();
         $insert_model = new Reminder_subscribers();
        $result = $insert_model->check_Reminder($param);
        return $result;
   }

   public function actionCheckuserreminder(){
        $param = yii::$app->request->post();
        $usercheck_model = new Match();
        $result = $usercheck_model->check_userReminder($param);
        return $result;
   }

   public function actionUserdeviceids(){
        $param = yii::$app->request->post();
        $user_deviceid = new Reminder_subscribers();
        $result = $user_deviceid->reminder_users($param);
        return $result;
   }
}