<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

abstract class TCB_Pagination {
	const IDENTIFIER = 'tcb-pagination';

	/* possible types for pagination */
	const LOAD_MORE = 'load_more';
	const NONE = 'none';

	/**
	 * The type of this pagination
	 *
	 * @var string
	 */
	private $type;
	/**
	 * Array of attributes for this pagination.
	 *
	 * @var array
	 */
	private $attr;

	/**
	 * TCB_Pagination constructor.
	 *
	 * @param $type
	 * @param $attr
	 */
	public function __construct( $type, $attr = array() ) {
		$this->type = empty( $type ) ? static::NONE : $type;
		$this->attr = $attr;
	}

	/**
	 * Render the pagination.
	 *
	 * @param $existing_content
	 *
	 * @return string
	 */
	public function render( $existing_content = '' ) {
		/* if there is no existing content, call the render function */
		if ( empty( $existing_content ) ) {
			$content = $this->get_content();
		} else {
			/* if the pagination content already exists, it means that this is a load more pagination that was saved statically, so we return that */
			$content = $existing_content;
		}

		return TCB_Utils::wrap_content( $content, 'div', '', $this->get_classes(), $this->get_attr() );
	}

	/**
	 * Get/generate the classes for this pagination element.
	 *
	 * @return string
	 */
	private function get_classes() {
		/* default classes */
		$classes = array( self::IDENTIFIER, THRIVE_WRAPPER_CLASS );

		$attr = $this->get_attr();

		/* add the responsive classes, if they are present */
		if ( ! empty( $attr['class'] ) ) {
			$classes[] = $attr['class'];
		}

		return implode( ' ', $classes );
	}

	/**
	 * Get the pagination content for the current type. Implemented in the classes that extend this.
	 *
	 * @return string|null
	 */
	abstract public function get_content();

	/**
	 * Get the label for this type. Implemented in the classes that extend this.
	 *
	 * @return string|void
	 */
	abstract public function get_label();

	/**
	 * @return string
	 */
	public function get_type() {
		return $this->type;
	}

	/**
	 * @return array
	 */
	public function get_attr() {
		return $this->attr;
	}

	/**
	 * @param $type
	 * @param $attr
	 *
	 * @return TCB_Pagination|null
	 */
	public static function factory( $type, $attr = array() ) {
		$class_name = "TCB_Pagination_{$type}";

		/* check if the class exists and return an instance */
		if ( ! empty( $type ) && class_exists( $class_name ) ) {
			return ( new $class_name( $type, $attr ) );
		}

		return null;
	}
}

/**
 * @param $type
 * @param $attr
 *
 * @return TCB_Pagination|null
 */
function tcb_pagination( $type, $attr = array() ) {
	return TCB_Pagination::factory( $type, $attr );
}
