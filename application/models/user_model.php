<?php 

if (! defined('BASEPATH')) exit('No direct script access');

class User_model extends CI_Model {
	
	function __construct() {
		parent::__construct();
	}
	
	function getAllUsers() {
	   return $this->db->get('user')->result(); 
	}

}

