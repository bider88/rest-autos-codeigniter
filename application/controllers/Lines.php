<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once( APPPATH.'/libraries/REST_Controller.php' );
use Restserver\Libraries\REST_Controller;

class Lines extends REST_Controller {

	public function __construct() {

		header("Access-Control-Allow-Methods: PUT, GET, POST, DELETE, OPTIONS");
		header("Access-Control-Allow-Headers: Content-Type, Content-Length, Accept-Encoding");
		header("Access-Control-Allow-Origin: *");
		
		parent::__construct();
		$this->load->database();

	}

	public function index_get() {

		$query = $this->db->query("SELECT * FROM lineas");

		$this->response([
			'ok'=> true,
			'data'=> $query->result_array()
		]);
	}
}
