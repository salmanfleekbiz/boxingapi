<?php
namespace services\modules\v1\controllers;

use Aws\Api\Service;
use Yii;
use services\modules\v1\models\Boxer;
use yii\db\Expression;
use yii\web\NotFoundHttpException;


class BoxerController extends BaseController
{
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['authenticator']['except'] = ['addboxer','allboxers','boxerget','updateboxerdata'];
        return $behaviors;
    }

    public function actionAddboxer(){
        $param = Yii::$app->request->post();
        $insert_model = new Boxer();
        $result = $insert_model->add_newboxer($param);
        return $result;
    }

    public function actionAllboxers()
    {
        $model = new Boxer();
        $getall =  $model->get_allboxers();
        return $getall;
    }

    public function actionBoxerget(){
        $id = Yii::$app->request->get('id');
        $getdata = new Boxer();
        $getboxer =  $getdata->get_boxer($id);
        return $getboxer;
    }

    public function actionUpdateboxerdata(){
    
        $param = yii::$app->request->post();
        $update_boxer = new Boxer();
        $boxer_updates = $update_boxer->updateboxer($param);
        return $boxer_updates;
   }
}