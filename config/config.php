<?php

/** Configuration Variables **/

define ('DEVELOPMENT_ENVIRONMENT', TRUE);

// database setup, currently only MySQL is supported
define('DB_NAME', '');
define('DB_USER', '');
define('DB_PASSWORD', '');
define('DB_HOST', 'localhost');
define('DB_TYPE', 'mysql');

// no trailing slash at the end!
define('BASE_URL','http://localhost');
define('PAGINATE_LIMIT', '5');

// Facebook configuration
define('FB_API_KEY', '');
define('FB_SECRET', '');
define('FB_APP_ID','');
// application URL with trailing slash
define('FB_APP_URL','http://apps.facebook.com/your_app_name/');
define('FB_CANCEL_URL','http://www.facebook.com/');
