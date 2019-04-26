<?php
namespace services\modules\v1\controllers;

use Aws\Api\Service;
use Yii;
use services\modules\v1\models\Tournament;
use yii\db\Expression;
use yii\web\NotFoundHttpException;


class TournamentController extends BaseController
{
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['authenticator']['except'] = ['addtournament','alltournament','upcommingevent', 'tournamentmatchlist','tournamentget','updatetournamentdata'];
        return $behaviors;
    }

   public function actionAddtournament(){

        $param = yii::$app->request->post();
        $insert_model = new Tournament();
        $result = $insert_model->add_newTournament($param);
        return $result;
   }

    public function actionAlltournament()
    {
        $model = new Tournament();
        $getall =  $model->get_alltournament();
        return $getall;
    }

    public function actionUpcommingevent(){

        $model = new Tournament();
        $getall =  $model->upComming_events();
        return $getall;    
    }

    public function actionTournamentmatchlist(){

        $param = yii::$app->request->post();
        $model = new Tournament();
        $matchlist =  $model->tournaments_match_list($param);
        return $matchlist;    
    }

    public function actionTournamentget(){
        $id = Yii::$app->request->get('id');
        $getdata = new Tournament();
        $gettournament =  $getdata->get_tournament($id);
        return $gettournament;
    }

     public function actionUpdatetournamentdata(){
    
        $param = yii::$app->request->post();
        $update_tournament = new Tournament();
        $tournament_updates = $update_tournament->updatetournament($param);
        return $tournament_updates;
   }
}