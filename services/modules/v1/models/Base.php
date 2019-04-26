<?php
namespace services\modules\v1\models;
use yii;
use \yii\db\ActiveRecord;

/**
 * Brand Model
 * @authors <srizvi@csquareonline.com>, <srashid@csquareonline.com>, <sullah@csquareonline.com>
 *
 */
class Base extends ActiveRecord
{
    protected $current_user_id; // tenant_id
    protected $deleteStatus = -1;

    public function init()
    {
        parent::init();
		// this handle case when script executed from cron then set user_id 0 means created by automated process
        $this->current_user_id = (php_sapi_name() == "cli") ? 0 : Yii::$app->request->headers->get('user_id');
    }

    public function emptyObject(){
        return new \StdClass();
    }
}