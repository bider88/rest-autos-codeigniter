<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once( APPPATH.'/libraries/REST_Controller.php' );
use Restserver\Libraries\REST_Controller;

class Test extends REST_Controller {

	public function __construct() {

		header("Access-Control-Allow-Methods: PUT, GET, POST, DELETE, OPTIONS");
		header("Access-Control-Allow-Headers: Content-Type, Content-Length, Accept-Encoding");
		header("Access-Control-Allow-Origin: *");
		
		parent::__construct();
		$this->load->database();

	}

	public function index_get()
	{
		$this->response([
			'ok'=> true,
			'data'=> 'Hola mundo'
		]);
	}
	
	public function get_array_get( $i = 0 ) {

		$array = ['Manzana', 'Pera', 'Piña'];

		if ($i >= count($array) || $i < 0) {

			$response = ['ok'=> false, 'error'=>['messaSe' => 'Index is not  valid']];
			$this->response( $response, REST_Controller::HTTP_BAD_REQUEST );
			return;
		}

		$this->response([
			'ok'=> true,
			'data'=> $array[$i]
		]);

	}

	public function get_product_get( $code ) {

		$query = $this->db->query("SELECT * FROM productos WHERE codigo='$code'");

		$this->response([
			'ok'=> true,
			'data'=> $query->result()
		]);

	}

}
