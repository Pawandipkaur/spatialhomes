<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class TCB_Post_Comments_Number_Element
 */
class TCB_Post_Comments_Number_Element extends TCB_Post_List_Sub_Element_Abstract {
	/**
	 * Name of the element
	 *
	 * @return string
	 */
	public function name() {
		return __( 'Comments Counter', 'thrive-cb' );
	}

	/**
	 * Return icon class needed for display in menu
	 *
	 * @return string
	 */
	public function icon() {
		return 'comments-number';
	}

	/**
	 * WordPress element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		return '.' . TCB_POST_COMMENTS_NUMBER_IDENTIFIER;
	}

	/**
	 * Return the shortcode tag of the element.
	 *
	 * @return string
	 */
	public function shortcode() {
		return 'tcb_post_comments_number';
	}

	/**
	 * Element HTML
	 *
	 * @return string
	 */
	public function html() {
		return TCB_Utils::wrap_content( '', 'span', '', 'tcb-post-comments-number tcb-plain-text' . ' ' . THRIVE_WRAPPER_CLASS . ' ' . TCB_SHORTCODE_CLASS );
	}

	/**
	 * Add/disable controls.
	 *
	 * @return array
	 */
	public function own_components() {
		$components = parent::own_components();

		$components['animation']['hidden']  = true;
		$components['post_comments_number'] = array(
			'config' => array(
				'Url' => array(
					'config'  => array(
						'default'     => '',
						'name'        => __( 'Comments Counter URL', 'thrive-cb' ),
						'label_col_x' => 6,
						'options'     => array(
							array(
								'name'  => __( 'None', 'thrive-cb' ),
								'value' => '',
							),
							array(
								'name'  => __( 'Post URL', 'thrive-cb' ),
								'value' => 1,
							),
							array(
								'name'  => __( 'Post Comments Section', 'thrive-cb' ),
								'value' => 2,
							),
						),
					),
					'extends' => 'Select',
				),
			),
		);

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
