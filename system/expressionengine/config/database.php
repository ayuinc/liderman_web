<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

$active_group = 'expressionengine';
$active_record = TRUE;

$db['expressionengine']['hostname'] = 'localhost';
$db['expressionengine']['username'] = 'root';
$db['expressionengine']['password'] = '';
$db['expressionengine']['database'] = 'liderman_web';
$db['expressionengine']['dbdriver'] = 'mysqli';
$db['expressionengine']['pconnect'] = FALSE;
$db['expressionengine']['dbprefix'] = 'exp_';
$db['expressionengine']['swap_pre'] = 'exp_';
$db['expressionengine']['db_debug'] = TRUE;
$db['expressionengine']['cache_on'] = FALSE;
$db['expressionengine']['autoinit'] = FALSE;
$db['expressionengine']['char_set'] = 'utf8';
$db['expressionengine']['dbcollat'] = 'utf8_general_ci';
<<<<<<< HEAD
$db['expressionengine']['cachedir'] = '/home/liderman_web/public_html/system/expressionengine/cache/db_cache/';
=======
$db['expressionengine']['cachedir'] = 'C:\xampp\htdocs\liderman_web\system\expressionengine\cache\db_cache';
>>>>>>> b3d3d423ca289c3dbf0f26b5359ad90c66091725

/* End of file database.php */
/* Location: ./system/expressionengine/config/database.php */