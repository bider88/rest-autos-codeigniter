<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once( APPPATH.'/libraries/REST_Controller.php' );
use Restserver\Libraries\REST_Controller;

class Products extends REST_Controller {

	public function __construct() {

		header("Access-Control-Allow-Methods: PUT, GET, POST, DELETE, OPTIONS");
		header("Access-Control-Allow-Headers: Content-Type, Content-Length, Accept-Encoding");
		header("Access-Control-Allow-Origin: *");
		
		parent::__construct();
		$this->load->database();

	}

	public function all_get( $page = 0, $limit = 10 ) {

		if ($page < 0) { $page = 0; }
		if ($limit < 0) { $limit = 10; }

		$page = $page === 0 ? 0 : $page * 10 - 10;

		$query = $this->db->query("SELECT * FROM productos LIMIT $page, $limit");
		$count = $this->db->query("SELECT count(*) AS total_products FROM productos");

		$this->response([
			'ok'=> true,
			'count' => $count->result()[0],
			'data'=> $query->result_array()
		]);
	}

	public function by_type_get($type = 0, $page = 0, $limit = 10) {

		if ($type === 0) {
			$response = ['ok'=> false, 'error'=>['message' => 'type is required']];
			$this->response( $response, REST_Controller::HTTP_BAD_REQUEST );
			return;
		}

		if ($page < 0) { $page = 0; }
		if ($limit < 0) { $limit = 10; }

		$page = $page === 0 ? 0 : $page * 10 - 10;

		$query = $this->db->query("SELECT * FROM productos WHERE linea_id = $type LIMIT $page, $limit");
		$count = $this->db->query("SELECT count(*) AS total_products FROM productos WHERE linea_id = $type");

		$this->response([
			'ok'=> true,
			'count' => $count->result()[0],
			'data'=> $query->result_array()
		]);

	}

	public function search_get($term = '', $page = 0, $limit = 10) {

		if ($page < 0) { $page = 0; }
		if ($limit < 0) { $limit = 10; }

		$page = $page === 0 ? 0 : $page * 10 - 10;

		$query = $this->db->query("SELECT * FROM productos WHERE producto LIKE '%$term%' OR descripcion LIKE '%$term%' LIMIT $page, $limit");
		$count = $this->db->query("SELECT count(*) AS total_products FROM productos WHERE producto LIKE '%$term%' OR descripcion LIKE '%$term%' LIMIT $page, $limit");

		$this->response([
			'ok'=> true,
			'count' => $count->result()[0],
			'term'=> $term,
			'data'=> $query->result_array()
		]);
	}
}
