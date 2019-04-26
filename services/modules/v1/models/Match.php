<?php

namespace services\modules\v1\models;

use yii\base\ErrorException;
use yii\base\Exception;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

class Match extends Base
{

    public static function tableName()
    {
        return '{{%matches}}';
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

   public function add_newMatch($param){
         
         if($param['boxer_first'] == $param['boxer_second']){
            return ['result' => 'Select diffrenet boxers name'];
         }else{
         $sortorder = (new \yii\db\Query())->select(['id','sort_number'])->from('matches')->where(['sort_number' => $param['sortnumber']])->andwhere(['tournament_id' => $param['tournament']])->one();   
         if($sortorder['sort_number'] == '' ){
         $insert = self::getDb()->createCommand()->insert('matches', [
                        'first_boxer_id' => $param['boxer_first'],
                        'second_boxer_id' => $param['boxer_second'],
                        'tournament_id' => $param['tournament'],
                        'venue' => $param['venu'],
                        'start_date' => $param['start_date'],
                        'finish_date' => $param['end_date'],
                        'reminder_note' => $param['reminder_note'],
                        'pre_match_summary' => $param['pre_match_summary'],
                        'post_match_summary' => $param['post_match_summary'],
                        'match_winner' => $param['winner'],
                        'is_featured' => $param['featured_match'],
                        'sort_number' => $param['sortnumber'],
                        'created_at' => new Expression('NOW()'),
                        'updated_at' => new Expression('NOW()'),
                    ])->execute();

         $lastId = self::getDb()->getLastInsertID();
         $channeId = (new \yii\db\Query())->select(['channel_id'])->from('tournaments')->where(['id' => $param['tournament']])->one();
          $insert_channel = self::getDb()->createCommand()->insert('matche_channels', [
                        'match_id' => $lastId,
                        'channel_id' => $channeId['channel_id'],
                        'created_at' => new Expression('NOW()'),
                        'updated_at' => new Expression('NOW()'),
                    ])->execute();

           return ['result' => 'Match add successfully.'];   
         }else{
            $last_sortorder = (new \yii\db\Query())->select(['id','sort_number'])->from('matches')->where(['tournament_id' => $param['tournament']])->limit(1)->offset(0)->orderBy(['id' => SORT_DESC])->one();
            $assign_new_sort = $last_sortorder['sort_number'] + 1;

            $update_sort=self::getDb()->createCommand()->update('matches', ['sort_number' => $assign_new_sort], 'id = '.$sortorder['id'])->execute();

            $insert = self::getDb()->createCommand()->insert('matches', [
                        'first_boxer_id' => $param['boxer_first'],
                        'second_boxer_id' => $param['boxer_second'],
                        'tournament_id' => $param['tournament'],
                        'venue' => $param['venu'],
                        'start_date' => $param['start_date'],
                        'finish_date' => $param['end_date'],
                        'reminder_note' => $param['reminder_note'],
                        'pre_match_summary' => $param['pre_match_summary'],
                        'post_match_summary' => $param['post_match_summary'],
                        'match_winner' => $param['winner'],
                        'is_featured' => $param['featured_match'],
                        'sort_number' => $param['sortnumber'],
                        'created_at' => new Expression('NOW()'),
                        'updated_at' => new Expression('NOW()'),
                    ])->execute();

         $lastId = self::getDb()->getLastInsertID();
         $channeId = (new \yii\db\Query())->select(['channel_id'])->from('tournaments')->where(['id' => $param['tournament']])->one();
          $insert_channel = self::getDb()->createCommand()->insert('matche_channels', [
                        'match_id' => $lastId,
                        'channel_id' => $channeId['channel_id'],
                        'created_at' => new Expression('NOW()'),
                        'updated_at' => new Expression('NOW()'),
                    ])->execute();

           return ['result' => 'Match add successfully.'];

         }
        }
    }

    public function check_userReminder($param){
        
        $where1 = 'reminder_subscribers.user_id = '.$param['userId'];
        $where2 = 'matches.tournament_id = '.$param['tournamentId'];
        $result = self::find()
            ->select('matches.id AS matchId,reminder_subscribers.user_id')
            ->leftJoin('reminder_subscribers', 'reminder_subscribers.match_id = matches.id AND '.$where1)
            ->where($where2)
            ->asArray()
            ->all();
        return $result;
    }

    public function allmatches(){
        
        $result = self::find()
            ->select('matches.id AS matchId,boxers.name AS first_boxer,secondboxers.name AS second_boxer,matches.first_boxer_id,matches.second_boxer_id,matches.tournament_id,matches.venue,matches.start_date,matches.finish_date,matches.reminder_note,matches.pre_match_summary,matches.post_match_summary,matches.match_winner,matches.is_featured')
            ->leftJoin('boxers', 'boxers.id = matches.first_boxer_id')
            ->leftJoin('boxers secondboxers', 'secondboxers.id = matches.second_boxer_id')
            ->asArray()
            ->all();
        return $result;
    }

    public function get_match($id){ 

        $result = (new \yii\db\Query())->select(['id','first_boxer_id','second_boxer_id','tournament_id','venue','start_date','finish_date','reminder_note','pre_match_summary','post_match_summary','match_winner','is_featured','sort_number'])->from('matches')->where(['id' => $id])->one();
        return $result;
    }

     public function updatematch($param){
        $sortorder = (new \yii\db\Query())->select(['id','sort_number'])->from('matches')->where(['sort_number' => $param['sortnumber']])->andwhere(['tournament_id' => $param['tournament']])->one();
         if($param['boxer_first'] == $param['boxer_second']) {
            return ['update'=>'true','message'=>'name match'];
         }else if($sortorder['sort_number'] == '' ){
           $user=self::getDb()->createCommand()->update('matches', ['first_boxer_id' => $param['boxer_first'],'second_boxer_id' => $param['boxer_second'],'tournament_id' => $param['tournament'],'venue' => $param['venu'],'start_date' => $param['start_date'],'finish_date' => $param['end_date'],'reminder_note' => $param['reminder_note'],'pre_match_summary' => $param['pre_match_summary'],'post_match_summary' => $param['post_match_summary'],'match_winner' => $param['winner'],'is_featured' => $param['featured_match'],'sort_number' => $param['sortnumber']], 'id = '.$param['id'])
         ->execute();    
         return ['update'=>'true','message'=>'Match update successfully'];
         }else{
             $all_sortorder = (new \yii\db\Query())->select(['id' ,'sort_number'])->from('matches')->where(['tournament_id' => $param['tournament']])->all();
             foreach ($all_sortorder as $key => $value) {
                      $arr[$value['sort_number']] = $value['id'];
             }
             $replace_key = array_search($param['id'], $arr);
             $tmp = $arr[$replace_key];
             $arr[$replace_key] = $arr[$param['sortnumber']];
             $arr[$param['sortnumber']] = $tmp;
             $new_replace_key = array_search($param['id'], $arr);

             //echo $new_replace_key.' new id '.$param['id'];
             //echo $replace_key.' change'.$arr[$replace_key];

             $change_first=self::getDb()->createCommand()->update('matches', ['first_boxer_id' => $param['boxer_first'],'second_boxer_id' => $param['boxer_second'],'tournament_id' => $param['tournament'],'venue' => $param['venu'],'start_date' => $param['start_date'],'finish_date' => $param['end_date'],'reminder_note' => $param['reminder_note'],'pre_match_summary' => $param['pre_match_summary'],'post_match_summary' => $param['post_match_summary'],'match_winner' => $param['winner'],'is_featured' => $param['featured_match'],'sort_number' => $new_replace_key], 'id = '.$param['id'])
         ->execute();

         $change_second=self::getDb()->createCommand()->update('matches', ['sort_number' => $replace_key], 'id = '.$arr[$replace_key])
         ->execute();
          return ['update'=>'true','message'=>'Match update successfully'];
           //echo 'under construction';

         }

       // $user=self::getDb()->createCommand()->update('matches', ['first_boxer_id' => $param['boxer_first'],'second_boxer_id' => $param['boxer_second'],'tournament_id' => $param['tournament'],'venue' => $param['venu'],'start_date' => $param['start_date'],'finish_date' => $param['end_date'],'reminder_note' => $param['reminder_note'],'pre_match_summary' => $param['pre_match_summary'],'post_match_summary' => $param['post_match_summary'],'match_winner' => $param['winner'],'is_featured' => $param['featured_match']], 'id = '.$param['id'])
       //   ->execute();    
       //   return ['update'=>'true','message'=>'Match update successfully'];
    }

    public function get_winboxersnames($param){        
       $result = (new \yii\db\Query())->select(['id','name'])->from('boxers')->where(['id' => $param['boxerone']])->orwhere(['id' => $param['boxertwo']])->all();
        return $result;
    }

    public function get_matchbytournament($param){    

        $where = 'matches.tournament_id = '.$param['tid'];
        $result = self::find()
            ->select('matches.id, matches.first_boxer_id, matches.second_boxer_id,firstboxers.name AS FistBoxerName, secondboxers.name AS SecondBoxerName,sort_number')
            ->innerJoin('boxers firstboxers', 'firstboxers.id = matches.first_boxer_id')
            ->innerJoin('boxers secondboxers', 'secondboxers.id = matches.second_boxer_id')
            ->where($where)
            ->orderBy(['sort_number' => SORT_ASC])
            ->asArray()
            ->all();
        return $result;
    }

    public function delete_match($param){

        $dele_channel = self::getDb()->createCommand("DELETE FROM matche_channels WHERE match_id = '".$param['matchid']."'")->execute();
        $dele_subscriber = self::getDb()->createCommand("DELETE FROM reminder_subscribers WHERE match_id = '".$param['matchid']."'")->execute();
        $dele_match = self::getDb()->createCommand("DELETE FROM matches WHERE id = '".$param['matchid']."'")->execute();
        return $dele_match;
    }
}