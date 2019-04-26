<?php
namespace services\modules\v1;

use yii\web\Response;
use Yii;

/**
 *
 */
class Module extends \yii\base\Module
{
    public $controllerNamespace = 'services\modules\v1\controllers';
    public $isSuccessfull = true;

    public function init()
    {
        parent::init();

        Yii::$app->setComponents([
            'response' => [
                'class' => 'yii\web\Response',
                'on beforeSend' => array($this, 'on_before_send'),
                'format' => 'xml'
            ],
        ]);
    }

    public function setTypeCasting($data)
    {
        array_walk_recursive($data,
            function (&$value, $key) {
                // define all rules here
                if (preg_match("/_id|id|status|total_|Total$|Filtered$|gender|_count|_by|rating/i", "$key")) {
                    if ($value === null || $value === '') {
                        $value = null;
                    } else {
                        $value = (int)$value;
                    }

                } elseif (preg_match("/is_/i", "$key")) {
                    $value = (bool)$value;

                } elseif (preg_match("/amount|_distance|tax/i", "$key")) {
                    $value = floatval($value);
                }
            }
        );

        return $data;
    }

    public function on_before_send($event)
    {
        $response = $this->setTypeCasting($event->sender);
        if ($response->data !== null) {
            if ($response->statusCode != 200) {
                $this->isSuccessfull = false;
                //$response->data['code'] = $response->statusCode;
            }
            $response->data = [
                'success' => $this->isSuccessfull,
                ($this->isSuccessfull) ? 'data' : 'error' => isset($response->data['result']) ? $response->data['result'] : $response->data,
                'recordsTotal' => isset($response->data['recordsTotal']) ? $response->data['recordsTotal'] : null,
                'recordsFiltered' => isset($response->data['recordsFiltered']) ? $response->data['recordsFiltered'] : null
            ];

            if ($response->data['recordsTotal'] == null) unset($response->data['recordsTotal']);
            if ($response->data['recordsFiltered'] == null) unset($response->data['recordsFiltered']);

            $response->format = Response::FORMAT_JSON;
        }
        return true;
    }
}
