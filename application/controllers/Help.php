<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Help extends MY_Controller {

    public function index()
    {
        $data['title'] = "Help Page";
        $data['view'] = "help";
        $this->load->view('master', $data);
    }
}