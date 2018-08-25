<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once( APPPATH.'/libraries/REST_Controller.php' );
use Restserver\Libraries\REST_Controller;

class Orders extends REST_Controller {

	public function __construct() {

		header("Access-Control-Allow-Methods: PUT, GET, POST, DELETE, OPTIONS");
		header("Access-Control-Allow-Headers: Content-Type, Content-Length, Accept-Encoding");
		header("Access-Control-Allow-Origin: *");
		
		parent::__construct();
		$this->load->database();

	}

	public function generate_order_post($token = 0, $id_user = 0) {
		$data = $this->post();

		if ($token === 0 || $id_user === 0) {
			$response = ['ok'=> false, 'error'=>['message' => 'Token and/or user are not valids']];
			$this->response( $response, REST_Controller::HTTP_BAD_REQUEST  );
			return;
		}

		if ( !isset( $data['items'] ) || strlen( $data['items'] ) === 0 ) {
			$response = ['ok'=> false, 'error'=>['message' => 'Items are required']];
			$this->response( $response, REST_Controller::HTTP_BAD_REQUEST  );
			return;
		}

		$conditions = [ 'id'=> $id_user, 'token'=> $token ];
		$this->db->where( $conditions );
		$query = $this->db->get('login');

		$exist = $query->row();

		if ( $exist ) {

			$this->db->reset_query();

			$insert = ['usuario_id'=> $id_user];
			$this->db->insert('ordenes', $insert);
			$order_id = $this->db->insert_id();

			$items = explode(',', $data['items']);

			$count_insert = 0;

			foreach( $items as &$product_id ) {

				$conditions = [ 'codigo'=> $product_id ];
				$this->db->where( $conditions );
				$query = $this->db->get('productos');
				$exist = $query->row();

				if ( $exist ) {
					$count_insert++;
					$insert_data = ['producto_id'=> $product_id, 'orden_id'=> $order_id];
					$this->db->insert('ordenes_detalle', $insert_data);
				}

				$this->response([
					'ok'=> true,
					'order_generates' => $count_insert . ' of ' . count($items ),
					'order_id'=> $order_id
				]);
			}

		} else {
			$response = ['ok'=> false, 'error'=>['message' => 'Token and/or user are not valids']];
			$this->response( $response, REST_Controller::HTTP_UNAUTHORIZED  );
			return;
		}
	}

	public function get_orders_get($token = 0, $id_user = 0) {
		if ($token === 0 || $id_user === 0) {
			$response = ['ok'=> false, 'error'=>['message' => 'Token and/or user are not valids']];
			$this->response( $response, REST_Controller::HTTP_BAD_REQUEST  );
			return;
		}

		$conditions = [ 'id'=> $id_user, 'token'=> $token ];
		$this->db->where( $conditions );
		$query = $this->db->get('login');
		$exist = $query->row();

		if ( $exist ) {

			$query = $this->db->query("SELECT * FROM ordenes WHERE usuario_id = $id_user ORDER BY creado_en DESC");

			$orders = [];

			foreach($query->result() as $row) {
				$query_detail = $this->db->query("SELECT a.orden_id, b.* FROM ordenes_detalle a INNER JOIN productos b ON a.producto_id = b.codigo WHERE orden_id = $row->id");
				$order = [
					'id'=> $row->id,
					'created_at'=> $row->creado_en,
					'detail'=> $query_detail->result()
				];

				array_push($orders, $order);
			}

			$this->response([
				'ok'=> true,
				'data'=> $orders
			]);

		} else {
			$response = ['ok'=> false, 'error'=>['message' => 'Token and/or user are not valids']];
			$this->response( $response, REST_Controller::HTTP_UNAUTHORIZED  );
			return;
		}


	}

	public function delete_orders_delete($token = 0, $id_user = 0, $order_id = 0) {
		if ($token === 0 || $id_user === 0 || $order_id === 0) {
			$response = ['ok'=> false, 'error'=>['message' => 'Token, user and/or order id are not valids']];
			$this->response( $response, REST_Controller::HTTP_BAD_REQUEST  );
			return;
		}

		$conditions = [ 'id'=> $id_user, 'token'=> $token ];
		$this->db->where( $conditions );
		$query = $this->db->get('login');
		$exist = $query->row();

		if ( $exist ) {

			$this->db->reset_query();
			$conditions = ['id'=> $order_id, 'usuario_id'=> $id_user];
			$this->db->where($conditions);
			$query = $this->db->get('ordenes');

			$exist = $query->row();

			if ( $exist ) {

				$conditions = ['id'=> $order_id];
				$this->db->delete('ordenes', $conditions);
				
				$conditions = ['orden_id'=> $order_id];
				$this->db->delete('ordenes_detalle', $conditions);

				$this->response([
					'ok'=> true,
					'data'=> [
						'message'=> 'Order was deleted successfully',
						'order_deleted' => $order_id
					]
				]);

			} else {
				$response = ['ok'=> false, 'error'=>['message' => 'This order can not be deleted']];
				$this->response( $response, REST_Controller::HTTP_UNAUTHORIZED  );
				return;
			}

		} else {
			$response = ['ok'=> false, 'error'=>['message' => 'Token and/or user are not valids']];
			$this->response( $response, REST_Controller::HTTP_UNAUTHORIZED  );
			return;
		}


	}

}
