<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class TCB_Utils
 */
class TCB_Utils {
	/**
	 * Wrap content in tag with id and/or class
	 *
	 * @param              $content
	 * @param string       $tag
	 * @param string       $id
	 * @param string|array $class
	 * @param array        $attr
	 *
	 * @return string
	 */
	public static function wrap_content( $content, $tag = '', $id = '', $class = '', $attr = array() ) {
		$class = is_array( $class ) ? implode( ' ', $class ) : $class;

		if ( empty( $tag ) && ! ( empty( $id ) && empty( $class ) ) ) {
			$tag = 'div';
		}

		$attributes = '';
		foreach ( $attr as $key => $value ) {
			/* if the value is null, only add the key ( this is used for attributes that have no value, such as 'disabled', 'checked', etc ) */
			if ( is_null( $value ) ) {
				$attributes .= ' ' . $key;
			} else {
				$attributes .= ' ' . $key . '="' . $value . '"';
			}
		}

		if ( ! empty( $tag ) ) {
			$content = '<' . $tag . ( empty( $id ) ? '' : ' id="' . $id . '"' ) . ( empty( $class ) ? '' : ' class="' . $class . '"' ) . $attributes . '>' . $content . '</' . $tag . '>';
		}

		return $content;
	}

	/**
	 * Get all the banned post types for the post list/grid.
	 *
	 * @return mixed|void
	 */
	public static function get_banned_post_types() {
		$banned_types = array(
			'attachment',
			'revision',
			'nav_menu_item',
			'custom_css',
			'customize_changeset',
			'oembed_cache',
			'project',
			'et_pb_layout',
			'tcb_lightbox',
			'focus_area',
			'thrive_optin',
			'thrive_ad_group',
			'thrive_ad',
			'thrive_slideshow',
			'thrive_slide_item',
			'tve_lead_shortcode',
			'tve_lead_2s_lightbox',
			'tve_form_type',
			'tve_lead_group',
			'tve_lead_1c_signup',
			TCB_CT_POST_TYPE,
			'tcb_symbol',
			'td_nm_notification',
		);

		/**
		 * Filter that other plugins can hook to add / remove ban types from post grid
		 */
		return apply_filters( 'tcb_post_grid_banned_types', $banned_types );
	}

	/**
	 * Get the image source for the id.
	 *
	 * @param        $image_id
	 * @param string $size
	 *
	 * @return mixed
	 */
	public static function get_image_src( $image_id, $size = 'full' ) {
		$image_info = wp_get_attachment_image_src( $image_id, $size );

		return empty( $image_info ) || empty( $image_info[0] ) ? '' : $image_info[0];
	}

	/**
	 * Get the pagination data that we want to localize.
	 *
	 * @return array
	 */
	public static function get_pagination_localized_data() {
		$localized_data = array();

		foreach ( static::get_pagination_instances() as $instance ) {
			$localized_data[ $instance->get_type() ] = $instance->get_content();
		}

		/* we need this when we add new post lists to the page and they need a pagination element wrapper */
		$localized_data['pagination_wrapper'] = tcb_pagination( TCB_Pagination::NONE )->render();

		return $localized_data;
	}

	/**
	 * Get the config for the 'pagination type' select control.
	 *
	 * @param string $current_element
	 *
	 * @return array
	 */
	public static function get_pagination_type_config( $current_element = 'pagination' ) {
		$options = array();

		/* for each pagination instance, add its label and type to the select control */
		foreach ( static::get_pagination_instances() as $instance ) {
			$options[] = array(
				'name'  => $instance->get_label(),
				'value' => $instance->get_type(),
			);
		}

		return array(
			'config'  => array(
				'default'     => TCB_Pagination::NONE,
				/* if this is the control from the post list, change the name a bit */
				'name'        => __( ( $current_element === 'pagination' ? 'Type' : 'Pagination Type' ), 'thrive-cb' ),
				'label_col_x' => 6,
				'options'     => $options,
			),
			'extends' => 'Select',
		);
	}

	/**
	 * Get a pagination instances for each type.
	 *
	 * @return array
	 */
	public static function get_pagination_instances() {
		/*  Apply a filter in case we want to add more pagination types from elsewhere. */
		$all_pagination_types = apply_filters( 'tcb_post_list_pagination_types', array( TCB_Pagination::LOAD_MORE, TCB_Pagination::NONE ) );

		$instances = array();

		foreach ( $all_pagination_types as $type ) {
			$instances[] = tcb_pagination( $type );
		}

		return $instances;
	}

}
