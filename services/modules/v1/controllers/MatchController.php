<?php
namespace services\modules\v1\controllers;

use Aws\Api\Service;
use Yii;
use services\modules\v1\models\Match;
use yii\db\Expression;
use yii\web\NotFoundHttpException;


class MatchController extends BaseController
{
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['authenticator']['except'] = ['addmatch','allmatch','matchget','updatematchdata','winnernames','matchshowbytournamentid','matchdelet'];
        return $behaviors;
    }

    public function actionAddmatch(){
        $param = Yii::$app->request->post();
        $insert_model = new Match();
        $result = $insert_model->add_newMatch($param);
        return $result;
    }

    public function actionAllmatch(){
        $insert_model = new Match();
        $result = $insert_model->allmatches();
        return $result;
    }

    public function actionMatchget(){
        $id = Yii::$app->request->get('id');
        $getdata = new Match();
        $getmatch =  $getdata->get_match($id);
        return $getmatch;
    }

    public function actionUpdatematchdata(){
    
        $param = yii::$app->request->post();
        $update_match = new Match();
        $match_updates = $update_match->updatematch($param);
        return $match_updates;
   }

   public function actionWinnernames(){
        $param = yii::$app->request->post();
        $get_win = new Match();
        $match_winner = $get_win->get_winboxersnames($param);
        return $match_winner;
   }

   public function actionMatchshowbytournamentid(){
        $param = yii::$app->request->post();
        $get_matches = new Match();
        $matches_show = $get_matches->get_matchbytournament($param);
        return $matches_show;
   }

   public function actionMatchdelet(){
        $param = yii::$app->request->post();
        $get_matches = new Match();
        $matches_delete = $get_matches->delete_match($param);
        return $matches_delete;
   }
}