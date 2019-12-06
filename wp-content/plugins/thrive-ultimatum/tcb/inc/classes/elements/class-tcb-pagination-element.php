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
 * Class TCB_Pagination_Element
 */
class TCB_Pagination_Element extends TCB_Element_Abstract {
	/**
	 * Name of the element
	 *
	 * @return string
	 */
	public function name() {
		return __( 'Pagination', 'thrive-cb' );
	}

	/**
	 * WordPress element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		return '.' . TCB_Pagination::IDENTIFIER;
	}

	/**
	 * Hide this in the sidebar.
	 */
	public function hide() {
		return true;
	}

	/**
	 * Component and control config
	 *
	 * @return array
	 */
	public function own_components() {
		return array(
			'pagination'       => array(
				'config' => array(
					'Type' => TCB_Utils::get_pagination_type_config(),
				),
			),
			'animation'        => array( 'hidden' => true ),
			'styles-templates' => array( 'hidden' => true ),
			'scroll'           => array( 'hidden' => true ),
			'typography'       => array( 'hidden' => true ),
		);
	}
}
