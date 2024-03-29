<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

/**
 * Class TVD_Smart_Shortcodes
 */
final class TVD_Smart_Shortcodes {

	/**
	 * Database instance for Smart Site
	 *
	 * @var TVD_Smart_DB
	 */
	private $db;

	/**
	 * TVD_Smart_Shortcodes constructor.
	 */
	public function __construct() {
		$this->db = new TVD_Smart_DB();
		add_shortcode( TVD_Smart_Site::GLOBAL_FIELDS_SHORTCODE, array( $this, 'tvd_tss_smart_fields' ) );
		add_shortcode( TVD_Smart_Site::GLOBAL_FIELDS_SHORTCODE_URL, array( $this, 'tvd_tss_smart_url' ) );
	}

	/**
	 * Execute smart fields shortcode
	 *
	 * @param $args
	 *
	 * @return string
	 */
	public function tvd_tss_smart_fields( $args ) {
		$data = '';
		if ( $args['id'] ) {
			$field = $this->db->get_fields( array(), $args['id'] );

			if ( ! empty( $field ) ) {
				$groups = $this->db->get_groups( $field['group_id'], false );
				$group  = array_pop( $groups );

				$field['group_name'] = $group['name'];
				$field_data          = maybe_unserialize( $field['data'] );
				$data                = TVD_Smart_DB::format_field_data( $field_data, $field, $args );
			}

		}

		return $data;
	}

	/**
	 * Execute smart url shortcode
	 *
	 * @param $args
	 *
	 * @return string
	 */
	public function tvd_tss_smart_url( $args ) {
		$data = '';
		if ( $args['id'] ) {
			$field      = $this->db->get_fields( array(), $args['id'] );
			$field_data = maybe_unserialize( $field['data'] );
			$data       = $field_data['url'];
		}

		return $data;
	}

}
