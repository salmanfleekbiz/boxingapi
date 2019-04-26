<?php
namespace services\modules\v1\controllers;

use Yii;
use services\modules\v1\models\Cro;
use services\modules\v1\models\User;
use services\modules\v1\models\Job;
use services\modules\v1\models;

class CroController extends BaseController
{

    /**
     * Create new CRO
     * @return array
     */
    public function actionCreate()
    {
        /*$saveCro = new SaveCro();
        $saveCro->attributes = Yii::$app->request->post();
        $result = $saveCro->addCro();
        if ($saveCro->getFirstErrors()) {
            $this->setError();
            return $saveCro->getFirstErrors();
        }

        return $result;*/
        //todo: impliment transaction query.
        //todo: removed not null location_id in customer and task table (put them agian).
        //todo: add call log revamp (create new model for call log and use that).
        $userModel = new User();
        $userModel->setScenario('create'); //setting validation rules for create scenerio
        if ($userModel->load(Yii::$app->request->post(), '')) {
            if ($userModel->save()) {
                if ($userModel->getId()) {
                    $param = Yii::$app->request->post();
                    $cro_model = new Cro();
                    $cro_model->created_by = $this->current_user_id;
                    $cro_model->first_name = $param['first_name'];
                    $cro_model->oauth_user_id = $userModel->getId();
                    if ($cro_model->save()) {
                        $result = array(
                            'user_id' => $userModel->getId(),
                            'agent_id' => $cro_model->id,
                            'username' => $userModel->account_handler,
                            'first_name' => $userModel->first_name,
                            'last_name' => $userModel->last_name,
                            'nic' => $cro_model->nic_number,
                        );
                        return $result;
                    }
                }
            }
        }

        $this->setError();
        return $userModel;
    }


    /**
     * List all CROs
     * @return array|\yii\db\ActiveRecord[]
     */
    public function actionView()
    {
        $param = Yii::$app->request->get('id');
        $model = new Cro();
        $result = $model->getCro($param);
        return $result;
    }


    /**
     * Get CRO stats (total calls, orders, tasks, complaints etc)
     * @param $id
     * @return array
     */
    public function actionGetStats($id)
    {
        $model = new Cro();
        $result = $model->getCroStats($id);
        return $result;
    }


    public function actionUpdateInfo()
    {
        $param = Yii::$app->request->post();
        $model = new Cro();
        $result = $model->updateCroInfo($param);
        if ($result['error']) {
            $this->setError();
            $this->setStatusCode(422);
            return $result['message'];
        }
        return $result['result'];

    }


    /**
     * Get all orders of a customer
     * @return array
     */
    public function actionGetOrders()
    {
        $params = Yii::$app->request->get();
        $searchStr = (isset($params['search']) && $params['search']['value'] !== null) ? $params['search']['value'] : null;
        $offset = (isset($params['start'])) ? $params['start'] : null;
        $limit = (isset($params['length'])) ? $params['length'] : null;
        $status = (isset($params['status'])) ? $params['status'] : null;
        $id = (isset($params['id'])) ? $params['id'] : null;
        $columns = array('job.id', 'cc_agent.first_name', 'job.created');
        $order = $this->getOrderAbleColumn($params, $columns);
        $order = ($order != null) ? $order : 'job.id';
        $model = new Job();
        $result = $model->getJobWithTasks($id, $status, $searchStr, $offset, $limit, $order);
        return $result;
    }


}