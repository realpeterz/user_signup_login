<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 

class SignUp extends CI_Controller {

	//php 5 constructor
	public function __construct() {
		parent::__construct();
	}
	
	public function index()
	{
		
		if ( $this->input->post() )
		{
			$username = $this->input->post("username");
			$email = $this->input->post("email");
			$password = $this->input->post("password");
			$this->pc_user->requireActivation = FALSE;  	
			if ( $this->pc_user->create($username, $email, $password, $status=1) )
			{
				$identify = $username;
				$this->pc_user->login($identify, $password, $status=FALSE, $setCookie=FALSE);
				$this->session->set_flashdata('success', "user $username created!");
				redirect("/home");
			}                 
		}
		
		$data["content"] = "sign_up";
		$this->load->view("template", $data);
	}
	
	public function check_username_exist()
	{
	     echo $this->pc_user->exists("username", $this->input->post('username') ) ? 1:0;
	}
	
	public function check_email_exist()
	{
		echo $this->pc_user->exists("email", $this->input->post('email') ) ? 1:0;
	}

}    