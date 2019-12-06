<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-quiz-builder
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

class TQB_Shortcodes {

	protected static $quizzes = array();

	public static function init() {
		add_shortcode( 'tqb_quiz', array( 'TQB_Shortcodes', 'render_quiz_shortcode' ) );
		add_shortcode( 'tqb_quiz_options', array( 'TQB_Shortcodes', 'tqb_quiz_options' ) );
		add_shortcode( 'tqb_quiz_result', array( 'TQB_Shortcodes', 'render_quiz_result' ) );
	}

	/**
	 * Render quiz result content
	 *
	 * @param array $attributes
	 *
	 * @return int|string
	 */
	public static function render_quiz_result( $attributes ) {

		if ( ! isset( $attributes['result_type'] ) ) {
			return '';
		}
		$result = '';

		if ( isset( $attributes['points'] ) ) {

			$tqb_db = new TQB_Database();
			$points = json_decode( $attributes['points'], true );
			$score  = $tqb_db->get_explicit_result( $points );

			switch ( $attributes['result_type'] ) {

				case 'whole_number':
					$result = round( $score ) . '%';
					break;

				case 'one_decimal':
					$result = round( $score, 1 ) . '%';

					break;

				case 'two_decimal':
					$result = round( $score, 2 ) . '%';

					break;

				case 'default':
					$result = $score;

					break;
			}
		}

		return $result;
	}

	public static function render_quiz_shortcode( $attributes ) {

		add_action( 'wp_print_footer_scripts', array( 'TQB_Shortcodes', 'render_backbone_templates' ) );
		add_action( 'wp_footer', 'tqb_add_frontend_svg_file' );

		$quiz_id   = $attributes['id'];
		$unique_id = 'tqb-' . uniqid();

		tqb_enqueue_script( 'tqb-frontend', tqb()->plugin_url( 'assets/js/dist/tqb-frontend.min.js' ), array(
			'backbone',
		) );

		// Enqueue html2canvas script
		wp_enqueue_script( 'tqb-html2canvas', tie()->url( 'assets/js/html2canvas/html2canvas.js' ) );

		tqb_enqueue_default_scripts();
		TCB_Icon_Manager::enqueue_icon_pack(); // Include Thrive Icon pack

		self::$quizzes[ $quiz_id ] = array(
			'feedback_settings'  => TQB_Post_meta::get_feedback_settings_meta( $quiz_id ),
			'highlight_settings' => TQB_Post_meta::get_highlight_settings_meta( $quiz_id ),
		);

		wp_localize_script( 'tqb-frontend', 'TQB_Front', array(
			'nonce'        => wp_create_nonce( 'tqb_frontend_ajax_request' ),
			'ajax_url'     => admin_url( 'admin-ajax.php' ) . '?action=tqb_frontend_ajax_controller',
			'is_preview'   => isset( $_REQUEST['tve'] ) && $_REQUEST['tve'] || isset( $_REQUEST['preview'] ) && $_REQUEST['preview'],
			'post_id'      => get_the_ID(),
			'settings'     => tqb_get_option( Thrive_Quiz_Builder::PLUGIN_SETTINGS, tqb_get_default_values( Thrive_Quiz_Builder::PLUGIN_SETTINGS ) ),
			'quiz_options' => self::$quizzes,
		) );
		$style = TQB_Post_meta::get_quiz_style_meta( $quiz_id );
		$html  = '<div class="tve_flt" id="tve_editor">
			<div class="tqb-shortcode-wrapper" id="tqb-shortcode-wrapper-' . $quiz_id . '-' . $unique_id . '" data-quiz-id="' . $quiz_id . '" data-unique="' . $unique_id . '" >
				<div class="tqb-loading-overlay tqb-template-overlay-style-' . $style . '"><div class="tqb-loading-bullets"></div></div><div class="tqb-frontend-error-message"></div>
				<div class="tqb-shortcode-old-content"></div>
				<div class="tqb-shortcode-new-content tqb-template-style-' . $style . '"></div>
			</div></div>';

		TQB_Quiz_Manager::run_shortcodes_on_quiz_content( $quiz_id );

		if ( is_editor_page() || ( defined( 'DOING_AJAX' ) && DOING_AJAX && ! empty( $_REQUEST['tqb_in_tcb_editor'] ) ) ) {
			$html = str_replace( array( 'id="tve_editor"' ), '', $html );
			$html = '<div class="thrive-shortcode-html"><div>' . $html . '</div><style>.tqb-shortcode-wrapper{pointer-events: none;}</style></div>';
		}

		return $html;
	}

	public static function tqb_quiz_options( $args ) {
		return '#';
	}

	/**
	 * Render backbone templates
	 */
	public static function render_backbone_templates() {
		$templates = tve_dash_get_backbone_templates( tqb()->plugin_path( 'includes/frontend/views/templates' ), 'templates' );
		tve_dash_output_backbone_templates( $templates );
	}
}

TQB_Shortcodes::init();

