<?php
define('APP_NAME', 'child-support');

$host	= 	gethostname();
$ip 	= 	gethostbyname($host);
// Set environment detect variable
$environment_identifier = (isset($_SERVER['SERVER_NAME'])) ? $_SERVER['SERVER_NAME'] : $ip;

// Environment Specific Configurations
switch($environment_identifier)
{
	// Development
	case 'localhost':
		$path = "services/config/main-local.php";
		$env = 'local';
		define('API_DOMAIN_NAME', 'http://localhost/yii-app');
		define('BASE_URL', 'http://localhost/delivery-chacha/back-office');
		break;
    case '192.168.1.70': //development
		$path = "services/config/main-dev.php";
		$env = 'qa';
		define('API_DOMAIN_NAME', 'http://192.168.1.70/deliverychacha');
		define('BASE_URL', 'http://192.168.1.70/deliverychacha/back-office/');
		break;
	default:
		$path = "services/config/main-local.php";
		$env = 'local';
		define('API_DOMAIN_NAME', 'http://localhost/yii-app');
		define('BASE_URL', 'http://localhost/deliverychacha/back-office');
		break;
}