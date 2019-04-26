<?php
namespace services\modules\v1\controllers;

use Aws\Api\Service;
use Yii;
use services\modules\v1\models\Channel;
use yii\db\Expression;
use yii\web\NotFoundHttpException;


class ChannelController extends BaseController
{
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['authenticator']['except'] = ['addchannel','allchannel','updatechaneldata'];
        return $behaviors;
    }

    public function actionAddchannel()
    {
        $param = Yii::$app->request->post();
         $insert_model = new Channel();
         $result = $insert_model->channelAdd($param);
         return $result;
    }

   public function actionAllchannel()
    {
        $model = new Channel();
        $getall =  $model->get_allchannel();
        return $getall;
    }

    public function actionChannelget(){
        $id = Yii::$app->request->get('id');
        $getdata = new Channel();
        $getchannel =  $getdata->get_channel($id);
        return $getchannel;
    }

    public function actionUpdatechaneldata(){
    
        $param = yii::$app->request->post();
        $update_channel = new Channel();
        $channel_updates = $update_channel->updatechannel($param);
        return $channel_updates;
   }
}