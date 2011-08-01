<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 

class LogIn extends CI_Controller {

	//php 5 constructor
	public function __construct() {
		parent::__construct();
		if ( $this->pc_user->isLogged() )
		{ 
			redirect('/home');
		}
	}
	
	public function index()
	{
		if ( $this->input->post() )
		{
			$identify = $this->input->post('identity');
			$password = $this->input->post('password');
			$remember = $this->input->post('remember');
			$setCookie = ($remember == 1) ? TRUE:FALSE;
			if (preg_match('/@/', $identify)) { $this->pc_user->mode = "email"; }
			if ( $this->pc_user->login($identify, $password, $status=FALSE, $setCookie) )
			{
			   	//$user = $this->pc_user->getData();
				//$this->session->set_flashdata('success', "user ".$user['username']." logged in!");
				redirect("/home"); 
			}
		}
		
		$data["content"] = "log_in";
		$this->load->view('template', $data);
	}

}    