<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class TCB_Post_List_Sub_Element_Abstract
 */
abstract class TCB_Post_List_Sub_Element_Abstract extends TCB_Element_Abstract {

	/**
	 * Thrive_Theme_Element_Abstract constructor.
	 *
	 * @param string $tag
	 */
	public function __construct( $tag = '' ) {
		parent::__construct( $tag );

		add_filter( 'tcb_element_' . $this->tag() . '_config', array( $this, 'add_config' ) );
	}

	public function add_config( $config ) {
		$config['shortcode'] = $this->shortcode();

		return $config;
	}

	/**
	 * If an element has a shortcode tag (empty by default, override by children who have shortcode tags).
	 *
	 * @return bool
	 */
	public function shortcode() {
		return '';
	}

	/**
	 * Element category that will be displayed in the sidebar
	 *
	 * @return string
	 */
	public function category() {
		return TCB_Post_List::elements_group_label();
	}

	/**
	 * Default components that most post list sub-elements use
	 *
	 * @return array
	 */
	public function own_components() {
		$prefix = apply_filters( 'tcb_selection_root', '#tve_editor' ) . ' ';

		return array(
			'styles-templates' => array( 'hidden' => true ),
			'animation'        => array( 'disabled_controls' => array( '.btn-inline.anim-link' ) ),
			'typography'       => array(
				'disabled_controls' => array(
					'.tve-advanced-controls',
					'p_spacing',
					'h1_spacing',
					'h2_spacing',
					'h3_spacing',
				),
				'config'            => array(
					'css_suffix'    => '',
					'css_prefix'    => '',
					'TextShadow'    => array(
						'css_suffix' => '',
						'css_prefix' => $prefix,
					),
					'FontColor'     => array(
						'css_suffix' => '',
						'css_prefix' => $prefix,
						'important'  => true,
					),
					'FontSize'      => array(
						'css_suffix' => '',
						'css_prefix' => $prefix,
						'important'  => true,
					),
					'TextStyle'     => array(
						'css_suffix' => '',
						'css_prefix' => $prefix,
					),
					'LineHeight'    => array(
						'css_suffix' => '',
						'css_prefix' => $prefix,
						'important'  => true,
					),
					'FontFace'      => array(
						'css_suffix' => '',
						'css_prefix' => $prefix,
						'important'  => true,
					),
					'LetterSpacing' => array(
						'css_suffix' => '',
						'css_prefix' => $prefix,
					),
					'TextAlign'     => array(
						'css_suffix' => '',
						'css_prefix' => $prefix,
					),
					'TextTransform' => array(
						'css_suffix' => '',
						'css_prefix' => $prefix,
					),
				),
			),
		);
	}

	/**
	 * Get the config for the text type control (right now, it's the same for all the post list sub-elements).
	 *
	 * @return array
	 */
	public static function get_text_type_config() {
		return array(
			'config' => array(
				'TextTypeDropdown' => array(
					'config'  => array(
						'default'     => 'none',
						'name'        => __( 'Change text type', 'thrive-cb' ),
						'label_col_x' => 6,
						'options'     => array(
							array(
								'name'  => __(
									'Heading 1', 'thrive-cb' ),
								'value' => 'h1',
							),
							array(
								'name'  => __(
									'Heading 2', 'thrive-cb' ),
								'value' => 'h2',
							),
							array(
								'name'  => __(
									'Heading 3', 'thrive-cb' ),
								'value' => 'h3',
							),
							array(
								'name'  => __(
									'Heading 4', 'thrive-cb' ),
								'value' => 'h4',
							),
							array(
								'name'  => __(
									'Heading 5', 'thrive-cb' ),
								'value' => 'h5',
							),
							array(
								'name'  => __(
									'Heading 6', 'thrive-cb' ),
								'value' => 'h6',
							),
							array(
								'name'  => __(
									'Paragraph', 'thrive-cb' ),
								'value' => 'p',
							),
							array(
								'name'  => __(
									'Blockquote', 'thrive-cb' ),
								'value' => 'blockquote',
							),
							array(
								'name'  => __(
									'Plain text', 'thrive-cb' ),
								'value' => 'span',
							),
						),
					),
					'extends' => 'Select',
				),
			),
		);
	}
}
