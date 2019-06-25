<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Console extends CI_Controller {

	public function index()
	{
		$this->load->view('console/index');
	}

	public function contacts(){
	    $this->load->view('console/contacts');
    }
}
