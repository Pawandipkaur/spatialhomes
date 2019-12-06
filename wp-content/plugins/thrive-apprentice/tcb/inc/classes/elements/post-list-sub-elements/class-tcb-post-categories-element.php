<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class TCB_Post_Categories_Element
 */
class TCB_Post_Categories_Element extends TCB_Post_List_Sub_Element_Abstract {
	/**
	 * Name of the element
	 *
	 * @return string
	 */
	public function name() {
		return __( 'Post Categories', 'thrive-cb' );
	}

	/**
	 * Return icon class needed for display in menu
	 *
	 * @return string
	 */
	public function icon() {
		return 'post-categories';
	}

	/**
	 * Wordpress element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		return '.' . TCB_POST_CATEGORIES_IDENTIFIER;
	}

	/**
	 * Return the shortcode tag of the element.
	 *
	 * @return string
	 */
	public function shortcode() {
		return 'tcb_post_categories';
	}

	/**
	 * Element HTML
	 *
	 * @return string
	 */
	public function html() {
		$classes      = TCB_POST_CATEGORIES_IDENTIFIER . ' tcb-plain-text' . ' ' . THRIVE_WRAPPER_CLASS . ' ' . TCB_SHORTCODE_CLASS;
		$default_attr = array( 'data-link' => 1 );

		return TCB_Utils::wrap_content( '', 'span', '', $classes, $default_attr );
	}

	/**
	 * Add/disable controls.
	 *
	 * @return array
	 */
	public function own_components() {
		$components = parent::own_components();

		foreach ( $components['typography']['config'] as $control => $config ) {
			if ( in_array( $control, array( 'css_suffix', 'css_prefix' ) ) ) {
				continue;
			}
			/* make sure typography elements also apply on the link inside the category */
			$components['typography']['config'][ $control ]['css_suffix'] = array( ' a', '' );
		}

		return array_merge( $components, array(
			'post_categories' => array(
				'config' => array(
					'Type' => array(
						'config'  => array(
							'name'  => '',
							'label' => __( 'Category Links to Archive', 'thrive-cb' ),
						),
						'extends' => 'Switch',
					),
				),
			),
			'text-type'       => parent::get_text_type_config(),
		) );
	}

	/**
	 * The post categories should have hover state.
	 *
	 * @return bool
	 */
	public function has_hover_state() {
		return true;
	}
}
