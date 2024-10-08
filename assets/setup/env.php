<?php

if (!defined('APP_NAME'))                       define('APP_NAME', 'ATLAS');
if (!defined('APP_ORGANIZATION'))               define('APP_ORGANIZATION', 'LPU-C CoECSA');
if (!defined('APP_OWNER'))                      define('APP_OWNER', '120ms');
if (!defined('APP_DESCRIPTION'))                define('APP_DESCRIPTION', 'Advanced Thesis Logistics and AI System for LPU');

if (!defined('ALLOWED_INACTIVITY_TIME'))        define('ALLOWED_INACTIVITY_TIME', time()+1*60*60);

if (!defined('DB_DATABASE'))                    define('DB_DATABASE', 'coecsa_thesis');
if (!defined('DB_HOST'))                        define('DB_HOST','127.0.0.1');
if (!defined('DB_USERNAME'))                    define('DB_USERNAME','root');
if (!defined('DB_PASSWORD'))                    define('DB_PASSWORD' ,'');
if (!defined('DB_PORT'))                        define('DB_PORT' ,'3306');

if (!defined('MAIL_HOST'))                      define('MAIL_HOST', 'smtp.gmail.com');
if (!defined('MAIL_USERNAME'))                  define('MAIL_USERNAME', 'ton.agustin09@gmail.com');
if (!defined('MAIL_PASSWORD'))                  define('MAIL_PASSWORD', 'rdrc cinf leli xdms');
if (!defined('MAIL_ENCRYPTION'))                define('MAIL_ENCRYPTION', 'ssl');
if (!defined('MAIL_PORT'))                      define('MAIL_PORT', 465);