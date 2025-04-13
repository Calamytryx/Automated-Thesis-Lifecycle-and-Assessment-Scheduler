<?php

// Include the database connection and functions
require_once 'db.inc.php';

// Define constants using database values, with fallback to default values
if (!defined('APP_NAME'))           define('APP_NAME', getEnvVariable('APP_NAME') ?? 'ATLAS');
if (!defined('APP_ORGANIZATION'))   define('APP_ORGANIZATION', getEnvVariable('APP_ORGANIZATION') ?? 'LPU-C CoECSA');
if (!defined('APP_OWNER'))          define('APP_OWNER', getEnvVariable('APP_OWNER') ?? '120ms');
if (!defined('APP_DESCRIPTION'))    define('APP_DESCRIPTION', getEnvVariable('APP_DESCRIPTION') ?? 'Advanced Thesis Logistics and AI System for LPU');
if (!defined('APP_LOGO_NAVBAR'))   define('APP_LOGO_NAVBAR', getEnvVariable('APP_LOGO_NAVBAR') ?? '../assets/images/logo_full_lightbg.png');
if (!defined('APP_LOGO_FOOTER'))  define('APP_LOGO_FOOTER', getEnvVariable('APP_LOGO_FOOTER') ?? '../assets/images/logowhite.png');

if (!defined('ALLOWED_INACTIVITY_TIME')) define('ALLOWED_INACTIVITY_TIME', time() + (int)(getEnvVariable('ALLOWED_INACTIVITY_TIME') ?? 1*60*60));

if (!defined('DB_DATABASE'))        define('DB_DATABASE', getEnvVariable('DB_DATABASE') ?? 'icei_38697196_coecsathesis');
if (!defined('DB_HOST'))            define('DB_HOST', getEnvVariable('DB_HOST') ?? '127.0.0.1');
if (!defined('DB_USERNAME'))        define('DB_USERNAME', getEnvVariable('DB_USERNAME') ?? 'root');
if (!defined('DB_PASSWORD'))        define('DB_PASSWORD', getEnvVariable('DB_PASSWORD') ?? '');
if (!defined('DB_PORT'))            define('DB_PORT', getEnvVariable('DB_PORT') ?? '3306');

if (!defined('MAIL_HOST'))          define('MAIL_HOST', getEnvVariable('MAIL_HOST') ?? 'smtp.gmail.com');
if (!defined('MAIL_USERNAME'))      define('MAIL_USERNAME', getEnvVariable('MAIL_USERNAME') ?? '');
if (!defined('MAIL_PASSWORD'))      define('MAIL_PASSWORD', getEnvVariable('MAIL_PASSWORD') ?? '');
if (!defined('MAIL_ENCRYPTION'))    define('MAIL_ENCRYPTION', getEnvVariable('MAIL_ENCRYPTION') ?? 'ssl');
if (!defined('MAIL_PORT'))          define('MAIL_PORT', getEnvVariable('MAIL_PORT') ?? 465);