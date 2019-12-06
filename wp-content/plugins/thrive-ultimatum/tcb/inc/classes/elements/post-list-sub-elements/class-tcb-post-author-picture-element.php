<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class TCB_Post_Author_Picture_Element
 */
class TCB_Post_Author_Picture_Element extends TCB_Post_List_Sub_Element_Abstract {
	/**
	 * Name of the element
	 *
	 * @return string
	 */
	public function name() {
		return __( 'Author Image', 'thrive-cb' );
	}

	/**
	 * Return icon class needed for display in menu
	 *
	 * @return string
	 */
	public function icon() {
		return 'author-picture';
	}

	/**
	 * Wordpress element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		return '.' . TCB_POST_AUTHOR_PICTURE_IDENTIFIER;
	}

	/**
	 * Return the shortcode tag of the element.
	 *
	 * @return string
	 */
	public function shortcode() {
		return 'tcb_post_author_picture';
	}

	/**
	 * Element HTML
	 *
	 * @return string
	 */
	public function html() {
		global $post;
		$content = get_avatar( $post->post_author, 256 );

		$content = TCB_Utils::wrap_content( $content, '', '', TCB_POST_AUTHOR_PICTURE_IDENTIFIER . ' ' . THRIVE_WRAPPER_CLASS . ' ' . TCB_SHORTCODE_CLASS );

		return $content;
	}

	/**
	 * Component and control config
	 *
	 * @return array
	 */
	public function own_components() {
		$components = parent::own_components();

		$components['animation']  = array( 'hidden' => true );
		$components['background'] = array( 'hidden' => true );
		$components['typography'] = array( 'hidden' => true );

		$components['shadow'] = array(
			'config' => array(
				/* apply shadows on the image and not on the container */
				'css_suffix'        => ' img',
				/* only the drop-shadow makes sense for images, disable the rest */
				'disabled_controls' => array( 'inner', 'text' ),
			),
		);

		$components['borders'] = array(
			'config' => array(
				/* apply borders on the image and not on the container */
				'css_suffix' => ' img',
			),
		);

		return $components;
	}
}
