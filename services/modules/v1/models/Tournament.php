<?php

namespace services\modules\v1\models;

use yii\base\ErrorException;
use yii\base\Exception;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

class Tournament extends Base
{

    public static function tableName()
    {
        return '{{%tournaments}}';
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

    public function get_alltournament(){

        $result = (new \yii\db\Query())->select(['id','name','type','start_date','finish_date'])->from('tournaments')->where(['status' => 1])->all();
        return $result;
    }

    public function add_newTournament($param){
        $check_name = (new \yii\db\Query())->select(['name','start_date','finish_date','channel_id'])->from('tournaments')->where(['name' => $param['tournament_name']])->one();
        if($check_name['name'] != $param['tournament_name'] || $check_name['start_date'] != $param['start_date'] || $check_name['finish_date'] != $param['end_date'] || $check_name['channel_id'] != $param['channel']){
         
         $insert = self::getDb()->createCommand()->insert('tournaments', [
                        'name' => $param['tournament_name'],
                        'status' => $param['status'],
                        'start_date' => $param['start_date'],
                        'finish_date' => $param['end_date'],
                        'type' => $param['tournament_type'],
                        'channel_id' => $param['channel'],
                        'created_at' => new Expression('NOW()'),
                        'updated_at' => new Expression('NOW()'),
                    ])->execute();

         return ['result' => 'Tournament add successfully.'];
         }else{
            return ['result' => 'Tournament Already exist.'];
        }
    }

    public function upComming_events(){

        $where = 'matches.is_featured = 1';
        $where .= ' AND tournaments.finish_date >= CURDATE()';
        $result = self::find()
            ->select('tournaments.id, tournaments.name, tournaments.start_date, tournaments.finish_date, boxerone.name AS boxername_first, boxertwo.name AS boxername_second, channel.name AS channel_name')
            ->innerJoin('matches', 'tournaments.id = matches.tournament_id')
            ->innerJoin('boxers boxerone', 'boxerone.id = matches.first_boxer_id')
            ->innerJoin('boxers boxertwo', 'boxertwo.id = matches.second_boxer_id')
            ->innerJoin('ref_channels channel', 'tournaments.channel_id = channel.id')
            ->where($where)
            ->orderBy(['tournaments.start_date' => SORT_DESC])
            ->asArray()
            ->all();

        return $result;
    }

    public function tournaments_match_list($param){

        $where = 'tournaments.id = '.$param['tournament_id'];
        $result = self::find()
            ->select('tournaments.id AS TournamentId, matches.id AS MatchId, tournaments.name, tournaments.start_date, firstboxers.name AS FistBoxerName, secondboxers.name AS SecondBoxerName, winnerboxers.name AS WinnerName, ref_channels.name AS channelname, matches.is_featured, matches.status')
            ->innerJoin('ref_channels', 'tournaments.channel_id = ref_channels.id')
            ->innerJoin('matches', 'matches.tournament_id = tournaments.id')
            ->innerJoin('boxers firstboxers', 'firstboxers.id = matches.first_boxer_id')
            ->innerJoin('boxers secondboxers', 'secondboxers.id = matches.second_boxer_id')
            ->leftJoin('boxers winnerboxers', 'winnerboxers.id = matches.match_winner')
            ->where($where)
            ->orderBy(['matches.sort_number' => SORT_ASC])
            ->asArray()
            ->all();

        return $result;
    }

    public function get_tournament($id){        
       $result = (new \yii\db\Query())->select(['id','name','status','start_date','finish_date','type','channel_id'])->from('tournaments')->where(['id' => $id])->one();
        return $result;
    }

    public function updatetournament($param){

       $user=self::getDb()->createCommand()->update('tournaments', ['name' => $param['name'],'start_date' => $param['startdate'],'finish_date' => $param['finishdate'],'type' => $param['type'],'channel_id' => $param['channelid']], 'id = '.$param['id'])
         ->execute();    
         return ['update'=>'true','message'=>'Tournament update successfully'];
    }
}