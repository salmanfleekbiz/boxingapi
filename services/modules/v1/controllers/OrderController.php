<?php
/**
 * Created by PhpStorm.
 * User: nzia
 * Date: 6/8/16
 * Time: 11:45 AM
 */

namespace services\modules\v1\controllers;

use Yii;

use services\modules\v1\models;
use yii\base\Exception;
use yii\db\Expression;

class OrderController extends BaseController
{
    public function actionCreate(){

        $model = new models\Order();
        $param = Yii::$app->request->post();
        $header = Yii::$app->request->getHeaders();
        $oauth_user_id = $header['user_id'];
        $result = $model->createOrder($param,$oauth_user_id);


        if(isset($result['error'])){
            $this->setError();
            $this->setStatusCode(400);
            return $result['result']->getFirstErrors();
        }
       return $result['result'];
    }

    public function actionTheme(){

        $model = new models\Theme();
        $result =$model->getTheme();
        return $result;
    }

    public function actionView(){

        if(isset($_REQUEST['id'])){
            $id= $_REQUEST['id'];
        $model = new models\Order();
        $order =  $model->getOrderDetail($id);
        return $order;
        }

        else{
        $params = Yii::$app->request->get();
        $searchStr = (isset($params['search']) && $params['search']['value'] !== null) ? $params['search']['value'] : null;
        $offset = (isset($params['start'])) ? $params['start'] : null;
        $limit = (isset($params['length'])) ? $params['length'] : null;
        $status = (isset($params['status'])) ? $params['status'] : null;
        $id = (isset($params['id'])) ? $params['id'] : null;
        $columns = array('job.id','cc_agent.first_name','job.created');
        $order = $this->getOrderAbleColumn($params, $columns);
        $order = ($order != null) ? $order : 'job.id';
        $model = new models\Job();
        $result = $model->getJobWithTasks($id, $status, $searchStr, $offset, $limit, $order);
        return $result;
        }
    }



}

