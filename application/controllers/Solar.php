<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Solar extends MY_Controller {

    public function index()
    {
        $data['title'] = "Solar Roof Detail";
        $data['view'] = "solar";
        $this->load->view('master', $data);
    }
}