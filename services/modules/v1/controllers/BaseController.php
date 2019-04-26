<?php

namespace services\modules\v1\controllers;

use filsh\yii2\oauth2server\filters\auth\CompositeAuth;
use filsh\yii2\oauth2server\filters\ErrorToExceptionFilter;
use services\modules\v1\components\HeaderParamAuth;
use Yii;
use yii\base\Exception;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\auth\QueryParamAuth;
use yii\helpers\ArrayHelper;
use yii\rest\Controller;
use yii\web\UploadedFile;
use yii\base\ErrorException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use common\models\User;


/**
 * Base Controller API
 * @author <srizvi@csquareonline.com> <srashid@csquareonline.com>
 */
class BaseController extends Controller
{
	protected $user_id;
	protected $client_id;
    public $current_user_id;
    public $request;
/*
    public function behaviors()
    {
        $this->request = Yii::$app->request->post();
        return ArrayHelper::merge(parent::behaviors(), [
            'authenticator' => [
                'class' => CompositeAuth::className(),
                'except' => ['create', 'view', 'translate'],
                'authMethods' => [
                    //['class' => HttpBearerAuth::className()],
                    //['class' => QueryParamAuth::className(), 'tokenParam' => 'accessToken'],
                    ['class' => HeaderParamAuth::className()],
                ]
            ],
            'exceptionFilter' => [
                'class' => ErrorToExceptionFilter::className()
            ],
            'access' => [
            	'except' => ['view', 'translate', 'delete', 'getuser'],
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['token'],
                        'allow' => true,
                    ],
                    [
                        'actions' => ['create'],
                        'allow' => true,
                		'matchCallback' => function ($rule, $action) {
                        	return User::isUserAdmin($this->request['username'], $this->request['scope']);
                    	}
                    ],
                    [
                        'actions' => ['decode'],
                        'allow' => true,
                		'matchCallback' => function ($rule, $action) {
                        	return User::isUserAdmin($this->request['username'], $this->request['scope']);
                    	}
                    ]
                ]
            ]
        ]);
    }
	*/
	public function init()
	{
		parent::init();
        $this->current_user_id = (php_sapi_name() == "cli") ? 0 : Yii::$app->request->headers->get('user_id');
		// setting client_id for public methods as well
		/*$this->client_id = Yii::$app->request->headers->get('client_id');

		if(!$this->isActionAllowed(Yii::$app->urlManager->parseRequest(Yii::$app->request)))
		{
			$this->user_id = Yii::$app->request->headers->get('user_id');
			$this->access_token = Yii::$app->request->headers->get('access_token');

			if(!isset($this->user_id) || !isset($this->client_id) || !isset($this->access_token)) die(json_encode($this->sendResponseError(401, 'unauthorized request')));
			if(empty($this->user_id) || empty($this->client_id) || empty($this->access_token)) die(json_encode($this->sendResponseError(401, 'unauthorized request')));

			// finally check access_token validation
			if(!Yii::$app->restComponent->validateToken(['access_token'=>$this->access_token, 'client_id'=>$this->client_id, 'user_id'=>$this->user_id])) die(json_encode($this->sendResponseError(401, 'unauthorized request')));
		}*/
    }


	/**
	* Send Success Response (reference: Google JSON guide)
	*/
	protected function sendResponseSuccess($data, $format = 'standard')
	{
		// return with default 200 http status code
		if($format == 'standard') $data = ['data' => $data];

		// type casting
		array_walk_recursive($data,

			function (&$value, $key)
			{
				// define all rules here
				if (preg_match("/_id|id|status|total_|Total$|Filtered$|gender|_count/i", "$key")) {
					if($value === null || $value === '')  { $value = null; }
					else {	$value = (int)$value; }

				}
				elseif (preg_match("/is_/i", "$key")) {
					$value = (bool)$value;
				}
				elseif (preg_match("/_earning|_comission|_share|_sales|_revenue/i", "$key")) {
					$value = floatval($value);
				}
			}
		);

        return $data;
	}


	/**
	* Send Error Response (reference: Google JSON guide)
	*/
	protected function sendResponseError($code, $message = '')
	{
		if(is_string($code)) $code = (int)$code;

		$this->setHeader($code);
		$desc = $this->getStatusCodeDescription($code);

		if(empty($message)) {
			echo json_encode(['error' => ['code' => $code, 'description' => $desc]]);
			exit;
		}

		// convert message to string if its an object or array
		if(!is_string($message)) $message = preg_replace('/[\["]|\]/', '', json_encode(array_values($message)));

		//throw new \yii\web\HttpException(400, 'Wrong method', '');
		//  TODO: temporary fix need to re implement this
		echo json_encode(['error' => ['code' => $code, 'description' => $desc, 'message' => $message]]);
		exit;
	}

	/* Functions to set header with status code. eg: 200 OK ,400 Bad Request etc..*/
	private function setHeader($status)
	{
		$status_header = 'HTTP/1.1 ' . $status . ' ' . $this->getStatusCodeDescription($status);
		$content_type="application/json; charset=utf-8";

		header($status_header);
		header('Content-type: ' . $content_type);
		header('X-Powered-By: ' . "Moda <modaventures.com>");
	}

	private function getStatusCodeDescription($code)
	{
		// these could be stored in a .ini file and loaded
		// via parse_ini_file()... however, this will suffice
		// for an example
		$status = Array(
			200 => 'OK',
			400 => 'Bad Request',
			401 => 'Unauthorized',
			402 => 'Payment Required',
			403 => 'Forbidden',
			404 => 'Not Found',
			500 => 'Internal Server Error',
			501 => 'Not Implemented',
			204 => 'No content found for this request',
			410 => 'Resource expired'
		);
		return (isset($status[$code])) ? $status[$code] : '';
	}

    public function throwExeception($code){
        throw new Exception($this->getStatusCodeDescription($code), $code);
    }

	/* This will exclude all invalid fields from submitted data to avoid malicious activity*/
	protected function blackList($array,$excluded_feilds = [],$flag = 1)
	{
		$mandatory_exclude = ['status'];
		$excludes  = array_merge($mandatory_exclude,$excluded_feilds);

		foreach($excludes as $exclude)
		{
			unset($array[$exclude]);
		}

		return $array;
	}

	/* This will only return valide fields after filter */
	protected function whiteList($values,$includes,$flag = 1)
	{
		foreach($values as $key=>$value)
		{
			if (!in_array($key, $includes)) unset($values[$key]);
		}
		return $values;
	}

	/* This will validate application wide api mandatory fields */
	protected function mandatoryFields($values,$mandatory_feilds)
	{
		//type cast if provided value is not array
		if(is_array($values)) $values = (array) $values;

		$flag = 0;
		foreach($mandatory_feilds as $mandatory_feild)
		{
			if(!isset($values[$mandatory_feild]) || empty($values[$mandatory_feild])) $flag = 1;
		}

		if($flag == 1)
		{
			$feilds = implode(",",$mandatory_feilds);
			return $this->sendResponseError(400, "missing parameter: mandatory field(s) must be provided i.e. $feilds");
		}

		return true;
	}


    protected function getOrderAbleColumn($orderFilter, $columns)
    {

        if (!isset($orderFilter['order'])) {
            return null;
        }

        $orderFilter = $orderFilter['order'][0];

        $key = (int)$orderFilter['column'];
        $orderAbleColumn = isset($columns[$key]) ? $columns[$key] : '';
        if ($orderAbleColumn == '') {
            return null;
        }
        $orderType = $orderFilter['dir'];

        return $orderAbleColumn . ' ' . $orderType;
    }

	/**
	* Central Email Sending Functionality
	* Need to implement Remaining params as per YII2 documentation
	**/
	protected function sendMail($setFrom, $setTo, $setSubject, $view, $MailView = [], $bodyImages = [], $fileAttachment = [], $dynamicAttachment = [] )
	{
		// Static Images for each mail required
		$staticImages = [
							'fbLogo' 	=>	Yii::getAlias('@app/web/assets/images/email/fb.png'),
							'modaLogo' 	=>	Yii::getAlias('@app/web/assets/images/email/logo.jpg'),
							'piLogo' 	=>	Yii::getAlias('@app/web/assets/images/email/pi.png'),
							'twLogo' 	=>	Yii::getAlias('@app/web/assets/images/email/tw.png')
						];
		$mailImages = array_merge($staticImages, $bodyImages);

		// Body of the Email
		$mailContentImages = array_merge(['MailView' => $MailView] , $mailImages);

		// a view rendering result becomes the message body here @app/common/mail
		Yii::$app->mailer->compose($view,$mailContentImages)
		->setFrom($setFrom)
		->setTo($setTo)
		->setSubject($setSubject)
		->send();
	}


/* =========== My methods ============*/

    public function setAccountHandlerStandardFormat($accountHandler)
    {
        $number = str_replace('-', '', $accountHandler); //remove hypen from account_handler (mobile number)
        $number = str_replace(' ', '', $number); //remove white space from account_handler (mobile number)
        $newNumber = substr($number, 0, 1); //get first digit of the number
        if ($newNumber == 0) {
            $number = substr($number, 1); //if first digit it zero than remove it
        }
        return '92' . $number; //add 92 with number to make standard formate
    }

    public function setError(){
        $this->module->isSuccessfull = false;
    }

    public function setStatusCode($code){
        Yii::$app->response->statusCode = $code;
    }

    public function successResponse($message){
        return array('message' => $message, 'status'=>200, 'code' => 200);
    }


    /**
     * This will validate application wide api mandatory fields
     * @param $values
     * @param $mandatory_feilds
     * @return bool
     */
    protected function checkValidation($values, $mandatory_feilds)
    {
        //type cast if provided value is not array
        if (is_array($values)) $values = (array)$values;

        $flag = 0;
        foreach ($mandatory_feilds as $mandatory_feild) {
            if (trim($values[$mandatory_feild]) == '') {
                $flag = 1;
                $error_message[ucfirst($mandatory_feild)] = ucfirst($mandatory_feild) . ' cannot be blank.';
            }
        }

        if ($flag == 1) {
            $this->setError();
            return $error_message;
        }

        return true;
    }
}