<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class TCB_Post_Author_Name_Element
 */
class TCB_Post_Author_Name_Element extends TCB_Post_List_Sub_Element_Abstract {
	/**
	 * Name of the element
	 *
	 * @return string
	 */
	public function name() {
		return __( 'Author Name', 'thrive-cb' );
	}

	/**
	 * Return icon class needed for display in menu
	 *
	 * @return string
	 */
	public function icon() {
		return 'author-name';
	}

	/**
	 * Wordpress element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		return '.tcb-post-author';
	}

	/**
	 * Return the shortcode tag of the element.
	 *
	 * @return string
	 */
	public function shortcode() {
		return 'tcb_post_author_name';
	}

	/**
	 * Element HTML
	 *
	 * @return string
	 */
	public function html() {
		return TCB_Utils::wrap_content( '', 'span', '', 'tcb-post-author tcb-plain-text' . ' ' . THRIVE_WRAPPER_CLASS . ' ' . TCB_SHORTCODE_CLASS );
	}

	/**
	 * Component and control config.
	 *
	 * @return array
	 */
	public function own_components() {
		$components = parent::own_components();

		$components['text-type'] = parent::get_text_type_config();

		$components['typography']['config']['FontColor']['css_prefix'] = apply_filters( 'tcb_selection_root', '#tve_editor' ) . ' ';
		$components['typography']['config']['FontSize']['css_prefix']  = apply_filters( 'tcb_selection_root', '#tve_editor' ) . ' ';

		return $components;
	}
}
