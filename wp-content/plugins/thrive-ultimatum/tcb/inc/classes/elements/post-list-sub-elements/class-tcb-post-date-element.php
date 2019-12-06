<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class TCB_Post_Date_Element
 */
class TCB_Post_Date_Element extends TCB_Post_List_Sub_Element_Abstract {
	/**
	 * Name of the element
	 *
	 * @return string
	 */
	public function name() {
		return __( 'Post Date', 'thrive-cb' );
	}

	/**
	 * Return icon class needed for display in menu
	 *
	 * @return string
	 */
	public function icon() {
		return 'post-date';
	}

	/**
	 * WordPress element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		return '.tcb-post-date';
	}

	/**
	 * Return the shortcode tag of the element.
	 *
	 * @return string
	 */
	public function shortcode() {
		return 'tcb_post_published_date';
	}

	/**
	 * Element HTML
	 *
	 * @return string
	 */
	public function html() {
		$html = TCB_Utils::wrap_content( '', 'time', '', '', array( 'datetime' => '' ) );

		$classes = 'tcb-plain-text ' . TCB_POST_DATE . ' ' . THRIVE_WRAPPER_CLASS . ' ' . TCB_SHORTCODE_CLASS;

		return TCB_Utils::wrap_content( $html, 'span', '', $classes );
	}

	/**
	 * Component and control config
	 *
	 * @return array
	 */
	public function own_components() {
		$default = parent::own_components();

		$default['typography']['config']['FontColor']['css_prefix'] = apply_filters( 'tcb_selection_root', '#tve_editor' ) . ' ';
		$default['typography']['config']['FontSize']['css_prefix']  = apply_filters( 'tcb_selection_root', '#tve_editor' ) . ' ';

		return array_merge( $default, array(
			'post_date' => array(
				'config' => array(
					'Type'      => array(
						'config'  => array(
							'default' => 'list',
							'name'    => __( 'Post Date URL', 'thrive-cb' ),
							'buttons' => array(
								array(
									'icon'    => '',
									'text'    => 'None',
									'value'   => 'none',
									'default' => true,
								),
								array(
									'icon'  => '',
									'text'  => 'Monthly Archive',
									'value' => 'monthly',
								),
							),
						),
						'extends' => 'ButtonGroup',
					),
					'DateInput' => array(
						'config'  => array(
							'label'       => 'Date Format',
							'extra_attrs' => '',
							'label_col_x' => 4,
						),
						'extends' => 'LabelInput',
					),
				),
			),
			'text-type' => parent::get_text_type_config(),
		) );
	}
}
