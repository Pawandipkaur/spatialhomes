<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class TCB_Post_Title_Element
 */
class TCB_Post_Title_Element extends TCB_Post_List_Sub_Element_Abstract {
	/**
	 * Name of the element
	 *
	 * @return string
	 */
	public function name() {
		return __( 'Post Title', 'thrive-cb' );
	}

	/**
	 * Return icon class needed for display in menu
	 *
	 * @return string
	 */
	public function icon() {
		return 'post-title';
	}

	/**
	 * WordPress element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		return '.' . TCB_POST_TITLE_IDENTIFIER;
	}

	/**
	 * Return the shortcode tag of the element.
	 *
	 * @return string
	 */
	public function shortcode() {
		return 'tcb_post_title';
	}


	/**
	 * Element HTML
	 *
	 * @return string
	 */
	public function html() {
		return TCB_Utils::wrap_content( '', 'h3', '', TCB_POST_TITLE_IDENTIFIER . ' ' . THRIVE_WRAPPER_CLASS . ' ' . TCB_SHORTCODE_CLASS );
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
			/* make sure typography elements apply also on the link inside the title */
			$components['typography']['config'][ $control ]['css_suffix'] = array( ' a', '' );
			$components['typography']['config'][ $control ]['css_prefix'] = '.' . TCB_POST_TITLE_IDENTIFIER;
		}

		$components['text-type'] = parent::get_text_type_config();

		return $components;
	}

	/**
	 * The post title should have hover state
	 *
	 * @return bool
	 */
	public function has_hover_state() {
		return true;
	}
}
