<?php
namespace services\modules\v1\components;

use filsh\yii2\oauth2server\models\OauthAccessTokens;
use yii\web\UnauthorizedHttpException;
use services\modules\v1\models\User;
class HeaderParamAuth extends \yii\filters\auth\AuthMethod
{
    /**
     * @var string the HTTP authentication realm
     */
    public $realm = 'api';


    /**
     * @inheritdoc
     */
    public function authenticate($user, $request, $response)
    {
        $authHeader = $request->getHeaders()->get('Authorization'); //get access token in authorization header request
        $client_id = $request->getHeaders()->get('client_id'); //get client id from header
        $user_id = $request->getHeaders()->get('user_id'); //get user_id from header

        /* Authenticate Access Token */
        if ($authHeader !== null && preg_match('/^Bearer\s+(.*?)$/', $authHeader, $matches)) {
            $identity = $user->loginByAccessToken($matches[1], get_class($this));
            if ($identity === null) {
                $this->handleFailure($response);
            }else{
                /* if access token validate than check user_id and client_id against this token to make sure access token belongs to right person*/
                $access_token = explode(' ',trim($authHeader));
                $OauthValidate = OauthAccessTokens::findOne(['access_token' => $access_token[1]]);
                if(trim($OauthValidate->user_id) !== trim($user_id) || trim($OauthValidate->client_id) !== trim($client_id)){
                    throw new UnauthorizedHttpException('You are requesting with an invalid credential.');
                }

               /* $userModel = new User();
                $userRole = $userModel->getUserRole($user_id);

                if ($userRole['roles']) {
                    $this->validateScope($userRole['roles'], $request->getUrl()); //if user scope defined than validate scope
                }*/
            }
            return $identity;
        }

        return null;
    }

    /**
     * @inheritdoc
     */
    public function challenge($response)
    {
        $response->getHeaders()->set('WWW-Authenticate', "Bearer realm=\"{$this->realm}\"");
    }


    public function validateScope($scope, $url)
    {
        $allowedAPIs = [
            'v1' => [
                'admin' => ['/delivery-chacha/api/v1/user/is-username-available'],
                'cro' => ['/delivery-chacha/api/v1/user/google','/delivery-chacha/api/v1/user/test','/delivery-chacha/api/v1/import/orders'],
                'rider' => [''],
                'job_dispatcher' => ['/delivery-chacha/api/v1/user/hello'],
                'b2b_customer' => [''],
                'consumer' => [''],
            ],
        ];
        $scopes = explode(',', trim($scope));

        foreach($allowedAPIs as $rows){
            foreach($scopes as $scop){
                $allowedUrls[] = $rows[$scop];
            }
        }


        function in_array_r($needle, $haystack, $strict = false)
        {
            foreach ($haystack as $item) {
                if (($strict ? $item === $needle : $item == $needle) || (is_array($item) && in_array_r($needle, $item, $strict))) {
                    return true;
                }
            }

            return false;
        }

        if( in_array_r($url, $allowedUrls)){
            return true;
        }

        throw new UnauthorizedHttpException('Your are requesting with an invlid scope.');
    }
}