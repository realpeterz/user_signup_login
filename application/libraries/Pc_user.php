<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
* Authentication library created for simplicity and security.
* Uses the cookie helper and the session class from CodeIgniter.
*
* @package     CodeIgniter
* @subpackage  Libraries
* @category    Authentication
* @author      Søren Vind
* @copyright   Copyright (c) 2008-2009, Søren Vind
* @license     http://www.codeigniter.com/user_guide/license.html
* @link        http://www.codeigniter.com
* @since       Codeigniter 1.5.4
* @version     1.1
*/

class Pc_user {

	var $tbl = 'pc_usr';
	var $mode = 'username';
	
	var $requireEmail=TRUE;
	var $uniqueEmail=TRUE;
	var $requireActivation=FALSE;
	var $maxRetries=FALSE;
	var $banPeriod=3600; // One Hour
	var $hashKey="SECRET_HASH_KEY";
	
	// No further changes should be necessary	
	
	var $ci;
	var $data;
	var $bycookie;
	var $err;
	var $banStart;
	var $retries = 0;

	// Initialization of the class
	function Pc_user($params=FALSE)
	{
		// Possible customization - used by pc_user.php config file or when loading the class
		if($params !== FALSE) {
			$available_modes = array('email', 'username');
		
			if(isset($params['tbl'])) $this->tbl = $params['tbl'];
			if(isset($params['mode']) && in_array($params['mode'], $available_modes)) $this->mode = $params['mode'];
			if(isset($params['requireEmail'])) $this->requireEmail = $params['requireEmail'];
			if(isset($params['requireActivation'])) $this->requireActivation = $params['requireActivation'];
			if(isset($params['maxRetries'])) $this->maxRetries = $params['maxRetries'];
			if(isset($params['uniqueEmail'])) $this->uniqueEmail = $params['uniqueEmail'];
			if(isset($params['banPeriod'])) $this->banPeriod = $params['banPeriod'];		
			if(isset($params['hashKey'])) $this->hashKey = $params['hashKey'];			
			
		}
	
		$this->ci =& get_instance();
		$this->data = $this->_getter('pc_usr_data');
		$this->retries = $this->_getter('pc_usr_retries');
		$this->bycookie = $this->_getter('pc_usr_bycookie');
	}
	
	/**
    * Create a user with the minimum data
    * The status parameter makes is possible to create access levels (using i.e. 1 = normal user, 2 = admin user)
    * ERRORS:
    *		USERNAME_EMPTY
    *		PASSWORD_EMPTY
    *		EMAIL_EMPTY
    *		USERNAME_EXISTS
    *		EMAIL_EXISTS
    */
	function create($username, $email, $password, $status=1)
	{
		if(empty($username) && ($this->mode == 'username')) {
			$this->err = 'USERNAME_EMPTY';
			return false;
		} elseif(empty($password)) {
			$this->err = 'PASSWORD_EMPTY';
			return false;	
		} elseif(empty($email) && $this->requireEmail) {
			$this->err = 'EMAIL_EMPTY';
			return false;	
		} elseif($this->exists('username', $username)) {
			$this->err = 'USERNAME_EXISTS';
			return false;
		} elseif($this->uniqueEmail && $this->exists('email', $email)) {
			$this->err = 'EMAIL_EXISTS';
			return false;
		} else {
		
			if(!$this->requireActivation) {
				$active = 1;
				$act_hash = "";
			} else {
				$this->ci->load->helper('string');
			
				$active = 0;
				$act_hash = random_string('alnum', 8);
			}
			
			$sql = sprintf("INSERT INTO %s (username, email, password, status, created_time, active, act_hash) VALUES ('%s', '%s', '%s', %u, %u, %u, '%s')", 
				$this->ci->db->dbprefix($this->tbl), mysql_real_escape_string($username), mysql_real_escape_string($email), sha1($password), mysql_real_escape_string($status), time(), $active, $act_hash);
			$this->ci->db->query($sql);
			return true;
		}
	}
	
	/**
    * Log the user in with an identifier and a password, the optional third parameter makes it possible to set a login cookie for future automatic login
    * ERRORS:
    *		TOO_MANY_RETRIES
    *		PASSWORD_INCORRECT
    *		TOO_LOW_STATUS
    *		+ Inherits possible errors from get()
    */
	function login($identify, $password, $status=FALSE, $setCookie=FALSE)
	{
		if($this->maxRetries != FALSE) {
			$this->retries++;
			
			if($this->retries > $this->maxRetries) {
				$this->banStart = time();
			} elseif ($this->_getcookie('banStart')) {
				$this->banStart = $this->_getcookie('banStart');
			}
					
			if(time() < $this->banStart+$this->banPeriod) {
				$this->err = 'TOO_MANY_RETRIES';
				return false;
			}
		}
		
		$this->_setter('pc_usr_retries', $this->retries+1);
		
		$user = $this->get($identify, TRUE);
		if(!$user) {
			return false;
		} elseif($user['password'] != sha1($password)) {
			$this->err = 'PASSWORD_INCORRECT';
			return false;
		} elseif($status && ($user['status'] < $status)) {
			$this->err = 'TOO_LOW_STATUS';
			return false;
		} else {
			$this->_postLogin($user, $setCookie);
			return true;
		}
	}
	
	/**
    * Log the user in from a cookie
    * If the user is logged in using a cookie, a special flag is set (can be checked using isLoggedByCookie())
    * ERRORS:
    *		NO_COOKIE_SET
    *		CORRUPT_COOKIE
    *		TOO_LOW_STATUS		
    */
	function login_bycookie($status=FALSE)
	{
		$cookie_hash = $this->_getcookie('login_cookie');
		if(!$cookie_hash) {
			$this->err = 'NO_COOKIE_SET';
			return false;
		} else {
			$this->ci->db->where('cookie_hash', $cookie_hash);
			$q = $this->ci->db->get($this->ci->db->dbprefix($this->tbl));
			if($q->num_rows() == 0) {
				$this->err = 'CORRUPT_COOKIE';
				return false;
			} else {
				$user = $q->row_array();
				if($status && ($user['status'] < $status)) {
					$this->err = 'TOO_LOW_STATUS';
					return false;
				} else {
					$this->_postLogin($user, TRUE);
					return true;
				}
			}
		}
	}
	
	/**
    * PRIVATE
    * Performs actions after the user have been logged in
    */
	function _postLogin($user, $setCookie=FALSE)
	{
		// Is the user logged in by cookie-flag
		$this->_setter('pc_usr_bycookie', $setCookie);
	
		// Set the internal and session variables to store the user data
		unset($user['password']);
		$this->data = $user;
		$this->data['user_hash'] = sha1($this->data['username'].$this->hashKey.$this->data['user_id']);
		$this->_setter('pc_usr_data', $this->data);
	
		// Update the database with the time for the last login
		// If $setCookie: Create a login cookie for automatic login using unique hash-key
		$update['lastlogin_time'] = time();
		if($setCookie) {
			$this->ci->load->helper('string');
			$update['cookie_hash'] = random_string('alnum', 16);
		
			$this->_setcookie('login_cookie', $update['cookie_hash']);
		}
		
		// Update the database using the new data
		$data = array();
		foreach($update as $key => $value) {
			$data[] = $key." = '".$value."'";
		}
		$str = implode(',', $data);
	
		$sql = sprintf("UPDATE %s SET %s WHERE user_id = '%s'", $this->ci->db->dbprefix($this->tbl), $str, $this->getUserId());
		$this->ci->db->query($sql);
	}

	/**
    * Activate a user from an identifier if a correct activation hash is supplied
    * The identifier must correspond to the login mode (username/email)
    * ERRORS:
    *		ACTIVATION_CODE_INCORRECT
    *		+ Inherits possible errors from get()
    */
	function activate($identify, $act_hash)
	{
		$user = $this->get($identify, FALSE, FALSE);
		if(!$user) {
			return false;
		} else {
			if($user['act_hash'] != $act_hash) {
				$this->err = 'ACTIVATION_CODE_INCORRECT';
				return false;
			} else {
				$sql = sprintf("UPDATE %s SET active = 1 WHERE user_id = '%s'", $this->ci->db->dbprefix($this->tbl), mysql_real_escape_string($user['user_id']));
				$this->ci->db->query($sql);
				return true;
			}
		}
	}
	
	/**
    * Logout the user
    */
	function logout()
	{
		$this->_setter('pc_usr_data', FALSE);
		$this->_setter('pc_usr_bycookie', FALSE);
		$this->data = FALSE;
		$this->bycookie = FALSE;
	}
	
	/**
    * Is the user logged in?
    */
	function isLogged()
	{
		return ($this->data !== FALSE);
	}
	
	/**
    * Is the user logged in using a cookie?
    */
	function isLoggedByCookie()
	{
		return $this->bycookie;
	}
	
	/**
    * Check if a record exists in the database. Checks a fieldname with a value
    * Usable in checking if a username is taken (ie: exists('username', 'JohnDoe')) 
    */	
	function exists($field, $data)
	{
		$sql = sprintf("SELECT * FROM %s WHERE %s = '%s'", $this->ci->db->dbprefix($this->tbl), mysql_real_escape_string($field), mysql_real_escape_string($data));
		$q = $this->ci->db->query($sql);
		if($q->num_rows > 0) {
			return true;
		}
		return false;
	}
	
	/**
    * Get user data from an identifier (the identifier must correspond to the login mode (email/username))
    * The $returnPass parameter decides if the password hash should be returned (not recommended - only usable during login)
    * ERRORS:
    *		IDENTIFY_EMPTY
    *		USER_NOT_FOUND
    *		USER_NOT_ACTIVE (user-data is still returned if requireActive is not true)
    */
	function get($identify, $returnPass=FALSE, $requireActive=TRUE)
	{	
		if(empty($identify)) {
			$this->err = 'IDENTIFY_EMPTY';
			return false;
		}

		$sql = sprintf("SELECT * FROM %s WHERE %s = '%s'", $this->ci->db->dbprefix($this->tbl), $this->mode, mysql_real_escape_string($identify));
		$q = $this->ci->db->query($sql);
		if($q->num_rows() == 0) {
			$this->err = 'USER_NOT_FOUND';
			return false;
		} else {
			$user = $q->row_array();
			if($user['active'] != 1 && $requireActive) {
				$this->err = 'USER_NOT_ACTIVE';
				if($requireActive) return false;
			}
			if(!$returnPass) unset($user['password']);
			return $user;
		}
	}
	
	/**
    * Get user data from an id
    * ERRORS:
    *		USER_ID_EMPTY
    *		USER_ID_DO_NOT_EXIST
    *		USER_NOT_ACTIVE (user-data is still returned if requireActive is not true)
    */
	function getFromId($user_id, $returnPass=FALSE, $requireActive=TRUE)
	{
		if(empty($user_id)) {
			$this->err = 'USER_ID_EMPTY';
			return false;
		}		
		
		$sql = sprintf("SELECT * FROM %s WHERE user_id = '%s'", $this->ci->db->dbprefix($this->tbl), mysql_real_escape_string($user_id));
		$q = $this->ci->db->query($sql);
		if($q->num_rows() == 0) {
			$this->err = 'USER_ID_DO_NOT_EXIST';
			return false;
		} else {
			$user = $q->row_array();
			if($user['active'] != 1) {
				$this->err = 'USER_NOT_ACTIVE';
				if($requireActive) return false;
			}
			if(!$returnPass) unset($user['password']);
			return $user;
		}
	}
	
	/**
    * Get the id of the user
    */
	function getUserId()
	{
		return $this->data['user_id'];
	}
	
	/**
    * Get the unique user hash
    * The hash is generated on login-time, and is not stored as a reference in the user table
    * This makes it usable in hiding what other tables the user has a reference to
    */
	function getUserHash()
	{
		return $this->data['user_hash'];
	}
	
	/**
    * Get the status of the user for access level handling
    */
	function getStatus()
	{
		return $this->data['status'];
	}
	
	/**
    * Get the activation hash (for use in an activation mail)
    */
	function getActHash()
	{
		return $this->data['act_hash'];
	}
	
	/**
    * Get data on the current user
    */
	function getData()
	{
		return $this->data;
	}
	
	/**
    * Get the error if an operation failed
    */
	function getError()
	{
		return $this->err;
	}
	
	/**
	* PRIVATE
    * The setter for setting session data
    */	
	function _setter($name, $data)
	{
		$this->ci->session->set_userdata($name, $data);
	}
	
	/**
		* PRIVATE
    * The getter for getting session data
    */
	function _getter($name)
	{
		return $this->ci->session->userdata($name);
	}
	
	/**
	* PRIVATE
    * Set a cookie with a name and a value
    * The cookie is valid for 1 year
    */
	function _setcookie($name, $value)
	{
		$this->ci->load->helper('cookie');
		
		set_cookie($name, $value, 60*60*24*365);
	}
	
	/**
	* PRIVATE
    * Get a cookie with a given name
    */
	function _getcookie($name)
	{
		$this->ci->load->helper('cookie');
		
		return get_cookie($name, TRUE);
	}
}
?>
