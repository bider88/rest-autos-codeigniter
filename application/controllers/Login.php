<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once( APPPATH.'/libraries/REST_Controller.php' );
use Restserver\Libraries\REST_Controller;

class Login extends REST_Controller {

	public function __construct() {

		header("Access-Control-Allow-Methods: PUT, GET, POST, DELETE, OPTIONS");
		header("Access-Control-Allow-Headers: Content-Type, Content-Length, Accept-Encoding");
		header("Access-Control-Allow-Origin: *");
		
		parent::__construct();
		$this->load->database();

	}

	public function index_post() {

		$data = $this->post();

		if (!isset($data['email']) || !isset($data['password'])) {
			$response = ['ok'=> false, 'error'=>['message' => 'email and password are required']];
			$this->response( $response, REST_Controller::HTTP_BAD_REQUEST );
			return;
		}

		$query = $this->db->get_where('login', [ 'correo'=> $data['email'], 'contrasena'=> $data['password'] ] );
		$user = $query->row();

		if ( !isset($user) ) {
			$response = ['ok'=> false, 'error'=>['message' => 'user and/or password are invalids']];
			$this->response( $response );
			return;
		}

		$token = bin2hex( openssl_random_pseudo_bytes(20) );
		$token = hash( 'ripemd160', $data['email'] );

		$this->db->reset_query();
		$update_token = ['token'=> $token];
		$this->db->where('id', $user->id);

		$done = $this->db->update('login', $update_token);

		$this->response([
			'ok'=> true,
			'token'=> $token,
			'data'=> [
				'id' => $user->id,
				'email' => $user->correo
			]
		]);
	}
}
