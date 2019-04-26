<?php

namespace services\modules\v1\models;

use yii\base\ErrorException;
use yii\base\Exception;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

class Cro extends Base
{

    public static function tableName()
    {
        return '{{%cc_agent}}';
    }


    public static function primaryKey()
    {
        return ['id'];
    }

    /**
     * @inheritdoc
     */
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


    public function getAgentId($user_id)
    {
        $cro_id = (new \yii\db\Query())->select(['id'])->from('cc_agent')->where(['oauth_user_id' => $user_id])->one();
        if ($cro_id) {
            return $cro_id['id'];
        }
        return 0;
    }


    /**
     * Get CRO stats (total calls, orders, tasks, complaints etc)
     * @param $user_id
     * @return array
     */
    public function getCroStats($user_id)
    {
        $agent_id = $this->getAgentId($user_id);
        $callLogs = CallLog::find()->count();
        $Jobs = Job::find()->count();
        $tasks = UserTask::find()->leftJoin('user_task_assign','user_task.id = user_task_assign.user_task_id')->where('(user_task.oauth_user_id = '.$user_id.' OR user_task_assign.oauth_user_id = '.$user_id.') AND status != 1')->groupBy('user_task.id')->count();
        $complaints = Complaint::find()->leftJoin('complaint_assign','complaint.id = complaint_assign.complaint_id')->where('(complaint.cc_agent_id = '.$agent_id.' OR complaint_assign.cc_agent_id =  '.$agent_id.') AND status != 1')->groupBy('complaint.id')->count();


        $result = [
            'total_calls_taken' => $callLogs,
            'total_orders' => $Jobs,
            'total_tasks' => $tasks,
            'total_complaints' => $complaints,
        ];
       /* $command = self::getDb()->createCommand("SELECT  (
                SELECT COUNT(*)
                FROM   call_log
                -- WHERE cc_agent_id = " . $agent_id . "
                ) AS total_calls_taken,
                (
                SELECT COUNT(*)
                FROM   job
                -- WHERE cc_agent_id = " . $agent_id . "
                ) AS total_orders,
                (
                SELECT COUNT(*)
                FROM   user_task
                -- WHERE oauth_user_id = " . $user_id . " AND status != 1
                WHERE  status != 1
                ) AS total_tasks,
                (
                SELECT COUNT(*)
                FROM   complaint WHERE status != 1
                ) AS total_complaints
        FROM    DUAL;");

        $result = $command->queryAll();*/
        return ['result' => [$result]];
    }


    /**
     * Get all or specific CRO info
     * @param $id
     * @return array|\yii\db\ActiveRecord[]
     */
    public function getCro($id)
    {
        $where = '1 = 1';
        if (isset($id)) {
            $id = $this->getAgentId($id);
            $where .= ' AND cc_agent.id = ' . $id;
        }

        $result = self::find()
            ->select('cc_agent.id,cc_agent.oauth_user_id,cc_agent.first_name,cc_agent.last_name,oauth_users.account_handler,oauth_users.email')
            ->innerJoin('oauth_users', 'cc_agent.oauth_user_id = oauth_users.id')
            ->where($where)
            ->asArray()
            ->all();

        return $result;
    }


    //todo: refactor this code please
    /**
     * Update CRO info (first name or last name or password)
     * @param $param
     * @return array
     */
    public function updateCroInfo($param)
    {
        if (isset($param['oauth_user_id']) && isset($param['agent_id'])) {
            if ($param['field_name'] == 'pasw') {
                $length = strlen($param['password']);
                if ($length < 8) {
                    return ['error' => true, 'message' => 'Password should contain at least 8 characters.'];
                }
                try {
                    $data = [
                        'password' => \Yii::$app->security->generatePasswordHash($param['password']),
                    ];
                    self::getDb()->createCommand()->update('oauth_users', $data, ['id' => $param['oauth_user_id']])->execute();
                    return ['error' => false, 'result' => 'Password updated successfully.'];
                } catch (Exception $e) {
                    return ['error' => true, 'message' => 'Could not update Password try again.'];
                }
            }

            $field_name = ($param['field_name'] == 'fname') ? 'first_name' : 'last_name';
            $value = ($param['field_name'] == 'fname') ? $param['first_name'] : $param['last_name'];
            $fieldName = ucwords(str_replace('_', ' ', $field_name));

            if (!$value) {
                return ['error' => true, 'message' => $fieldName . ' cannot be blank.'];
            }
            $data = [
                $field_name => $value,
            ];

            try {
                self::getDb()->createCommand()->update('oauth_users', $data, ['id' => $param['oauth_user_id']])->execute();
                self::getDb()->createCommand()->update('cc_agent', $data, ['id' => $param['agent_id']])->execute();
                return ['error' => false, 'result' => $fieldName . ' updated successfully.'];
            } catch (Exception $e) {
                return ['error' => true, 'message' => 'Could not update ' . $fieldName . ' try again.'];
            }
        }

        return ['error' => true, 'message' => 'Could not update data try again.'];
    }
}