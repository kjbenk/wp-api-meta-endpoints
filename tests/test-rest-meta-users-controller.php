<?php

/**
 * Unit tests covering WP_REST_Users meta functionality.
 *
 * @package WordPress
 * @subpackage JSON API
 */
class WP_Test_REST_Meta_Users_Controller extends WP_Test_REST_Controller_Testcase {
	public function setUp() {
		parent::setUp();

		$this->user = $this->factory->user->create();
		wp_set_current_user( $this->user );
		$this->user_obj = wp_get_current_user();
		$this->user_obj->add_role( 'adminstrator' );
		$this->user_obj->add_cap( 'edit_users' );
		$this->user_obj->add_cap( 'delete_users' );
	}

	public function test_register_routes() {
		$routes = $this->server->get_routes();

		$this->assertArrayHasKey( '/wp/v2/users/(?P<parent_id>[\d]+)/meta', $routes );
		$this->assertCount( 2, $routes['/wp/v2/users/(?P<parent_id>[\d]+)/meta'] );
		$this->assertArrayHasKey( '/wp/v2/users/(?P<parent_id>[\d]+)/meta/(?P<id>[\d]+)', $routes );
		$this->assertCount( 3, $routes['/wp/v2/users/(?P<parent_id>[\d]+)/meta/(?P<id>[\d]+)'] );
	}

	public function test_context_param() {
		$user_id = $this->factory->user->create();
		// Collection
		$request = new WP_REST_Request( 'OPTIONS', '/wp/v2/users/' . $user_id . '/meta' );
		$response = $this->server->dispatch( $request );
		$data = $response->get_data();
		$this->assertEquals( 'edit', $data['endpoints'][0]['args']['context']['default'] );
		$this->assertEquals( array( 'edit' ), $data['endpoints'][0]['args']['context']['enum'] );
		// Single
		$meta_id_basic = add_user_meta( $user_id, 'testkey', 'testvalue' );
		$request = new WP_REST_Request( 'OPTIONS', '/wp/v2/users/' . $user_id . '/meta/' . $meta_id_basic );
		$response = $this->server->dispatch( $request );
		$data = $response->get_data();
		$this->assertEquals( 'edit', $data['endpoints'][0]['args']['context']['default'] );
		$this->assertEquals( array( 'edit' ), $data['endpoints'][0]['args']['context']['enum'] );
	}

	public function test_get_items() {
		$user_id                    = $this->factory->user->create();
		$meta_id_serialized         = add_user_meta( $user_id, 'testkey_serialized', array( 'testvalue1', 'testvalue2' ) );
		$meta_id_serialized_object  = add_user_meta( $user_id, 'testkey_serialized_object', (object) array( 'testvalue' => 'test' ) );
		$meta_id_serialized_array   = add_user_meta( $user_id, 'testkey_serialized_array', serialize( array( 'testkey1' => 'testvalue1', 'testkey2' => 'testvalue2' ) ) );
		$meta_id_protected          = add_user_meta( $user_id, '_testkey', 'testvalue' );

		$request = new WP_REST_Request( 'GET', sprintf( '/wp/v2/users/%d/meta', $user_id ) );
		$response = $this->server->dispatch( $request );

		$this->assertEquals( 200, $response->get_status() );
		$data = $response->get_data();

		foreach ( $data as $row ) {
			$row = (array) $row;
			$this->assertArrayHasKey( 'id', $row );
			$this->assertArrayHasKey( 'key', $row );
			$this->assertArrayHasKey( 'value', $row );

			if ( $row['id'] === $meta_id_serialized ) {
				$this->assertEquals( 'testkey_serialized', $row['key'] );
				$this->assertEquals( array( 'testvalue1', 'testvalue2' ), $row['value'] );
			}

			if ( $row['id'] === $meta_id_serialized_object ) {
				$this->assertEquals( 'testkey_serialized_object', $row['key'] );
				$this->assertEquals( (object) array( 'testvalue' => 'test' ), $row['value'] );
			}

			if ( $row['id'] === $meta_id_serialized_array ) {
				$this->assertEquals( 'testkey_serialized_array', $row['key'] );
				$this->assertEquals( serialize( array( 'testkey1' => 'testvalue1', 'testkey2' => 'testvalue2' ) ), $row['value'] );
			}
		}
	}

	public function test_get_item() {
		// No op
	}

	public function test_create_item() {
		// No op
	}

	public function test_update_item() {
		// No op
	}

	public function test_delete_item() {
		// No op
	}

	public function test_prepare_item() {
		// No op
	}

	public function test_get_item_schema() {
		// No op
	}
}
