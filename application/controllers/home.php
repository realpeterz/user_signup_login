<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 

class Home extends CI_Controller {

	//php 5 constructor
	public function __construct() {
		parent::__construct();
		if (!$this->pc_user->isLogged())
		{
			redirect("/login");
		}
	}
	
	public function index()
	{
		$data["content"] = "home";
		$this->load->view("template", $data);
	}
	
	public function logout()
	{
	   $this->pc_user->logout(); 
	   redirect("/login");
	}

}    