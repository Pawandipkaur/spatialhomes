<?php

class TVA_Labels_Controller extends TVA_REST_Controller {

	public $base = 'labels';

	/**
	 * Register Routes
	 */
	public function register_routes() {

		register_rest_route( self::$namespace . self::$version, '/' . $this->base, array(
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'save_labels' ),
				'permission_callback' => array( $this, 'labels_permissions_check' ),
				'args'                => array(),
			),
		) );

		register_rest_route( self::$namespace . self::$version, '/' . $this->base . '/(?P<ID>[\d]+)', array(
			array(
				'methods'             => WP_REST_Server::DELETABLE,
				'callback'            => array( $this, 'delete_labels' ),
				'permission_callback' => array( $this, 'labels_permissions_check' ),
				'args'                => array(),
			),
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => array( $this, 'edit_labels' ),
				'permission_callback' => array( $this, 'labels_permissions_check' ),
				'args'                => array(),
			),
		) );
	}

	/**
	 * Save labels
	 *
	 * @param $request
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function save_labels( $request ) {
		/**
		 * We should add the new label
		 */
		$labels = get_option( 'tva_filter_labels', array() );
		$id     = 0;
		if ( ! empty( $labels ) ) {
			/**
			 * Get the biggest ID so we can create our new one
			 */
			foreach ( $labels as $label ) {
				if ( $label['ID'] > $id ) {
					$id = $label['ID'];
				}
			}
		}

		$id ++;

		$model = array(
			'ID'    => $id,
			'color' => $request->get_param( 'color' ),
			'title' => $request->get_param( 'title' ),
		);

		$labels[] = $model;
		$result   = update_option( 'tva_filter_labels', $labels );

		if ( $result ) {
			return new WP_REST_Response( $model, 200 );
		}

		return new WP_Error( 'no-results', __( 'No label was updated!', TVA_Const::T ) );
	}

	/**
	 * Permissions check
	 *
	 * @param $request
	 *
	 * @return bool
	 */
	public function labels_permissions_check( $request ) {
		return TVA_Product::has_access();
	}

	/**
	 * Delete label by id
	 *
	 * @param $request
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function delete_labels( $request ) {
		$response = array();
		$id = $request->get_param( 'ID' );

		$labels = get_option( 'tva_filter_labels', array() );
		foreach ( $labels as $key => $label ) {
			if ( $label['ID'] == $id ) {
				unset( $labels[ $key ] );
			}
		}
		$labels = array_values( $labels );
		$result = update_option( 'tva_filter_labels', $labels );

		if ( $result ) {
			return new WP_REST_Response( $response, 200 );
		}

		return new WP_Error( 'no-results', __( 'No label was deleted!', TVA_Const::T ) );
	}

	/**
	 * Edit label by id
	 *
	 * @param $request
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function edit_labels( $request ) {

		$id = $request->get_param( 'ID' );

		$model = array(
			'ID'    => $id,
			'color' => $request->get_param( 'color' ),
			'title' => $request->get_param( 'title' ),
		);

		$labels = get_option( 'tva_filter_labels', array() );

		foreach ( $labels as $key => $label ) {
			if ( $label['ID'] == $id ) {
				$labels[ $key ] = $model;
			}
		}

		$result   = update_option( 'tva_filter_labels', $labels );

		if ( $result ) {
			return new WP_REST_Response( $model, 200 );
		}

		return new WP_Error( 'no-results', __( 'No label was updated!', TVA_Const::T ) );
	}
}
