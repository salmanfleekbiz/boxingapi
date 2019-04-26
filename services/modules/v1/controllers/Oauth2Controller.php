<?php
namespace services\modules\v1\controllers;


//use GuzzleHttp\Psr7\Response;
use services\modules\v1\models\SignUp;
use services\modules\v1\models\User;
use common\models\User as Users;
use Yii;
use yii\helpers\ArrayHelper;
use filsh\yii2\oauth2server\filters\ErrorToExceptionFilter;
use yii\web\Response;
use yii\web\ForbiddenHttpException;
use yii\web\UnauthorizedHttpException;
use yii\web\HttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;

class Oauth2Controller extends BaseController
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['authenticator']['except'] = ['token', 'decode'];
        return $behaviors;
    }

    public function actionToken()
    {
        $request = Yii::$app->request->post();
        Yii::$app->response->format = Response::FORMAT_JSON;
        // error reporting (this is a demo, after all!)
        ini_set('display_errors',1);error_reporting(E_ALL);


        // Autoloading (composer is preferred, but for this example let's just do this)
        require_once('/var/www/html/yii-application1/vendor/bshaffer/oauth2-server-php/src/OAuth2/Autoloader.php');
        \OAuth2\Autoloader::register();

        // your public key strings can be passed in however you like
        // (there is a public/private key pair for testing already in the oauth library)
        $publicKey  = file_get_contents('/var/www/html/yii-application1/pubkey.pem');
        $privateKey = file_get_contents('/var/www/html/yii-application1/privkey.pem');
        $storage = new \OAuth2\Storage\Memory(array(
            'keys' => array(
                'public_key'  => $publicKey,
                'private_key' => $privateKey,
            ),
            // add a Client ID for testing
            'client_credentials' => array(
                'testclient' => array('client_secret' => 'testpass')
            ),
        ));
        $server = new \OAuth2\Server($storage, array(
            'use_jwt_access_tokens' => true,
        ));
        $server->addGrantType(new \OAuth2\GrantType\ClientCredentials($storage)); // minimum config

        $scope = $this->scope($server);
        // send the response
        $response = $server->handleTokenRequest(\OAuth2\Request::createFromGlobals());
        $data     = $response->getParameters();
        if (!isset($data['error'])) {
            User::insertToken($data, $request);
        }
        return ['result' => $data];

    }

    private function scope($server)
    {
        // user = 1
        // admin = 2
        // guest = 3
        $defaultScope = '1';
        $supportedScopes = array(
          '1',
          '2',
          '3'
        );
        $memory = new \OAuth2\Storage\Memory(array(
          'default_scope' => $defaultScope,
          'supported_scopes' => $supportedScopes
        ));
        $scopeUtil = new \OAuth2\Scope($memory);

        $server->setScopeUtil($scopeUtil);
        return $scopeUtil;
    }

    public function actionVerification()
    {
        die("here");
        $jwt_access_token = $token['access_token'];

        $separator = '.';

        if (2 !== substr_count($jwt_access_token, $separator)) {
            throw new Exception("Incorrect access token format");
        }

        list($header, $payload, $signature) = explode($separator, $jwt_access_token);

        $decoded_signature = base64_decode(str_replace(array('-', '_'), array('+', '/'), $signature));

        // The header and payload are signed together
        $payload_to_verify = utf8_decode($header . $separator . $payload);

        // however you want to load your public key
        $public_key = file_get_contents('/path/to/pubkey.pem');

        // default is SHA256
        $verified = openssl_verify($payload_to_verify, $decoded_signature, $public_key, OPENSSL_ALGO_SHA256);

        if ($verified !== 1) {
            throw new Exception("Cannot verify signature");
        }

        // output the JWT Access Token payload
        var_dump(base64_decode($payload));
    }
    //with scop
    // public function actionToken()
    // {
    //     //echo \Yii::$app->language; die;
    //    //  \Yii::$app->language = 'de-DE';
    //    // $title = \Yii::t('app', 'This adsais a string to translate!');
    //    //  echo $title; die;
    //     Yii::$app->response->format = Response::FORMAT_JSON;

    //     // configure your available scopes
    //     $defaultScope = 'user';
    //     $supportedScopes = array(
    //       'user',
    //       'admin',
    //       'driver',
    //       'cro',
    //       'jd'
    //     );
    //     $memory = new \OAuth2\Storage\Memory(array(
    //       'default_scope' => $defaultScope,
    //       'supported_scopes' => $supportedScopes
    //     ));
    //     $scopeUtil = new \OAuth2\Scope($memory);

    //     Yii::$app->getModule('oauth2')->getServer()->setScopeUtil($scopeUtil);
    //     ///// ---------- End Scope ----- //////////

    //     $response = Yii::$app->getModule('oauth2')->getServer()->handleTokenRequest();
    //     $data = $response->getParameters();
    //     if (isset($data['access_token']) && !empty($data['access_token'])) {
    //         $data['userDetail'] = User::getUserDetailByToken($data['access_token']);
    //     }
    //     return ['result' => $data];
    // }
}