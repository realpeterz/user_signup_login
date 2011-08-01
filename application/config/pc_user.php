<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');
/*
| -------------------------------------------------------------------------
| pc_user
| -------------------------------------------------------------------------
|
| Configuration file for the pc_user authentication library
|
|
| Please see the user guide for complete details:
|
|	http://sorenvind.dk/development/pc_user
|
*/
$config['tbl'] = "pc_usr";
$config['mode'] = "username";
$config['requireEmail'] = TRUE;
$config['requireActivation'] = TRUE;
$config['uniqueEmail'] = TRUE;
$config['maxRetries'] = FALSE;
$config['banPeriod'] = TRUE;
$config['hashKey'] = "SECRET_HASH_KEY";

?>
