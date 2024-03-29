<?php
/**
 * Created by PhpStorm.
 * User: radu
 * Date: 05.08.2014
 * Time: 14:35
 */

if ( ! class_exists( 'Thrive_Leads_Two_Step_Action' ) ) {
	if ( ! class_exists( 'TCB_Thrive_Lightbox' ) ) {
		require_once TVE_TCB_ROOT_PATH . 'event-manager/classes/actions/TCB_Thrive_Lightbox.php';
	}

	/**
	 *
	 * handles the server-side logic for the Thrive Lightbox action = opens a lightbox on an Event Trigger
	 *
	 * Class TCB_Thrive_Lightbox
	 */
	class Thrive_Leads_Two_Step_Action extends TCB_Thrive_Lightbox {

		protected $key = 'thrive_leads_2_step';

		public static $trigger_id = '2st';

		/**
		 * holds all lightbox ids that have been parsed for events configuration - this is to not create an infinite loop in case of
		 * lightboxes used within lightboxes
		 *
		 * @var array
		 */
		private static $lightboxes_events_parsed = array();

		/**
		 * just a holder for the shortcode trigger
		 *
		 * @var string
		 */
		protected $shortcode_content = '';

		/**
		 * the shortcode id that this class is displaying
		 *
		 * @var string
		 */
		protected $shortcode_id = '';

		/**
		 * Should return the user-friendly name for this Action
		 *
		 * @return string
		 */
		public function getName() {
			return __( 'Open Thrive Leads ThriveBox', 'thrive-leads' );
		}

		/**
		 * Should output the settings needed for this Action when a user selects it from the list
		 *
		 * @param mixed $data
		 *
		 * @return string the full html for the settings view
		 */
		public function renderSettings( $data ) {
			$two_steps = tve_leads_get_two_step_lightboxes( array( 'active_test' => true ) );

			$lightboxes = array();
			foreach ( $two_steps as $l ) {
				$lightboxes[ $l->ID ] = $l;
			}

			$data['lightboxes'] = $lightboxes;
			$this->data         = $data;
			ob_start();
			include dirname( dirname( __FILE__ ) ) . '/views/lightbox-settings.php';
			$content = ob_get_contents();
			ob_end_clean();

			return $content;
		}

		/**
		 * output edit links for the lightbox
		 */
		public function getRowActions() {
			if ( empty( $this->config ) ) {
				return '';
			}
			$two_step = tve_leads_get_form_type( $this->config['l_id'], array( 'active_test' => true ) );
			if ( $two_step->active_test ) {
				return sprintf(
					'<br>%s - <a href="%s" target="_blank" class="tve_link_no_warning">%s</a>',
					__( 'A/B test in progress', 'thrive-leads' ),
					admin_url( 'admin.php?page=thrive_leads_dashboard' ) . '#test/' . $two_step->active_test->id,
					__( 'View test', 'thrive-leads' )
				);
			}

			return sprintf(
				'<br><a href="%s" target="_blank" class="tve_link_no_warning">%2$s</a>',
				admin_url( 'admin.php?page=thrive_leads_dashboard' ) . '#2step-lightbox/' . $this->config['l_id'],
				__( 'Edit ThriveBox', 'thrive-leads' )
			);
		}

		/**
		 * check if the associated lightbox exists and it's not trashed
		 *
		 * @return bool
		 */
		public function validateConfig() {
			$two_step_id = $this->config['l_id'];
			if ( empty( $two_step_id ) ) {
				return false;
			}

			$two_step = tve_leads_get_form_type( $two_step_id, array( 'get_variations' => false ) );
			if ( empty( $two_step ) || $two_step->post_status === 'trash' || $two_step->post_type != TVE_LEADS_POST_TWO_STEP_LIGHTBOX ) {
				return false;
			}

			return true;
		}

		/**
		 * this will just trigger a click on the container that holds the 2-step trigger
		 *
		 * @return string
		 */
		public function getJsActionCallback() {
			if ( ! self::$trigger_id ) {
				self::$trigger_id = uniqid( 'tl-' );
			}

			return 'function(t,a,c){var evt=ThriveGlobal.$j.Event("click");evt.tve_trigger=t;evt.tve_action=a;evt.tve_config=c;ThriveGlobal.$j("#tcb-evt-' . self::$trigger_id . '-"+c.l_id+" .tve-leads-two-step-trigger").first().trigger(evt);return false;}';
		}

		/**
		 * we just display a hidden element that acts as the trigger for the lightbox - it will be automatically triggered from javascript
		 *
		 * @param $data
		 *
		 * @return string
		 */
		public function applyContentFilter( $data ) {
			return '<span id="tcb-evt-' . self::$trigger_id . '-' . $this->shortcode_id . '" style="display:none">' . $this->shortcode_content . '</span>';
		}

		/**
		 * called inside the_content filter
		 * make sure that if custom icons are used, the CSS for that is included in the main page
		 * the same with Custom Fonts
		 *
		 * @param array $data configuration data
		 */
		public function mainPostCallback( $data ) {
			$two_step_id = empty( $data['config']['l_id'] ) ? 0 : $data['config']['l_id'];
			if ( isset( self::$lightboxes_events_parsed[ $two_step_id ] ) ) {
				return;
			}
			self::$lightboxes_events_parsed[ $two_step_id ] = true;

			// we can just call the shortcode render function
			$content                 = tve_leads_two_step_render( array( 'id' => $two_step_id ), '' );
			$this->shortcode_id      = $data['config']['l_id'];
			$this->shortcode_content = $content;
		}

		/**
		 * Get javascript options for the editor page
		 *
		 * @return array
		 */
		public function get_options() {
			return array(
				'labels'  => $this->getName(),
				'options' => self::thriveboxes(),
			);
		}

		/**
		 * return a list of all currently defined thrive boxes
		 *
		 * @return array
		 */
		public static function thriveboxes() {
			$data      = array();
			$two_steps = tve_leads_get_two_step_lightboxes( array( 'active_test' => true ) );

			foreach ( $two_steps as $l ) {
				$data[] = array(
					'id'       => intval( $l->ID ),
					'title'    => $l->post_title,
					'edit_url' => admin_url( 'admin.php?page=thrive_leads_dashboard#2step-lightbox/' . $l->ID ),
				);
			}

			return $data;
		}

		/**
		 * The Backbone view constructor
		 *
		 * @return string
		 */
		public function get_editor_js_view() {
			return 'TL_Editor.views.ThriveBoxAction';
		}

		public function render_editor_settings() {
			include dirname( dirname( __FILE__ ) ) . '/views/item-list.php';
		}
	}
}
