<?php

/**
 * Class TVA_Term
 * Wrapper over WP_Term
 */
class TVA_Term {
	/**
	 * @var WP_Term
	 */
	protected $term;

	/**
	 * @var
	 */
	protected $filters;

	/**
	 * @var
	 */
	protected $settings;

	/**
	 * @var array
	 */
	public $posts = array();

	/**
	 * @var int
	 */
	protected $lessons_counter = 0;

	/**
	 * Used to set the naming convention
	 *
	 * By default post type names are set as follow: tva_ . 'something'
	 * In order to keep a nice semantic we will need more representative names for items
	 *
	 * @var array
	 */
	protected $allowed_items
		= array(
			TVA_Const::LESSON_POST_TYPE  => 'lessons',
			TVA_Const::CHAPTER_POST_TYPE => 'chapters',
			TVA_Const::MODULE_POST_TYPE  => 'modules',
		);

	/**
	 * TVA_Term constructor.
	 *
	 * @param WP_Term|int $term
	 * @param array       $filters
	 */
	public function __construct( $term, $filters = array() ) {

		if ( ! $term instanceof WP_Term ) {
			$term = WP_Term::get_instance( $term );
		}

		$this->term    = $term;
		$this->filters = $filters;
		$this->get_settings();
		$this->get_term();
	}

	/**
	 * Get user settings
	 */
	protected function get_settings() {
		$user_settings = TVA_Settings::instance();

		$this->settings = $user_settings->get_settings();
	}

	/**
	 * @param $key
	 *
	 * @return bool|mixed
	 */
	protected function get_item_nice_name( $key ) {
		if ( ! array_key_exists( $key, $this->allowed_items ) ) {
			return false;
		}

		return $this->allowed_items[ $key ];
	}

	/**
	 * Get term data
	 *
	 * @return WP_Term
	 */
	public function get_term() {
		$this->set_term_meta();
		$this->set_hard_term_meta();
		$this->set_membership_ids();
		$this->set_bundles_ids();
		$this->set_content();
		$this->after_content_set();

		return $this->term;
	}

	/**
	 * Set any term data which can get directly from db or user settings
	 */
	public function set_term_meta() {
		$this->term->ID             = $this->term->term_id;
		$this->term->topic          = (int) get_term_meta( $this->term->ID, 'tva_topic', true );
		$this->term->order          = (int) get_term_meta( $this->term->ID, 'tva_order', true );
		$this->term->level          = (int) get_term_meta( $this->term->ID, 'tva_level', true );
		$this->term->logged_in      = (int) get_term_meta( $this->term->ID, 'tva_logged_in', true );
		$this->term->url            = get_term_link( $this->term->ID, TVA_Const::COURSE_TAXONOMY );
		$this->term->cover_image    = get_term_meta( $this->term->ID, 'tva_cover_image', true );
		$this->term->message        = get_term_meta( $this->term->ID, 'tva_message', true );
		$this->term->status         = get_term_meta( $this->term->ID, 'tva_status', true );
		$this->term->term_media     = get_term_meta( $this->term->ID, 'tva_term_media', true );
		$this->term->video_status   = get_term_meta( $this->term->ID, 'tva_video_status', true );
		$this->term->comment_status = get_term_meta( $this->term->ID, 'tva_comment_status', true );
		$this->term->description    = get_term_meta( $this->term->ID, 'tva_description', true );
		$this->term->author         = get_term_meta( $this->term->ID, 'tva_author', true );
		$this->term->lessons        = array();
		$this->term->chapters       = array();
		$this->term->modules        = array();
		$this->term->display        = 1;
		$this->term->state          = TVA_Const::NORMAL_STATE;
		$this->term->allowed_items  = array( 'modules', 'chapters', 'lessons' );
		$this->term->lesson_text    = $this->settings['template']['course_lesson'];
		$this->term->members_only   = ( isset( $this->settings['template']['members_only'] ) ) ? $this->settings['template']['members_only'] : '';
		$this->term->is_empty       = true;
	}

	/**
	 * Set term meta which need some logic behind
	 */
	public function set_hard_term_meta() {

		$roles             = get_term_meta( $this->term->ID, 'tva_roles', true );
		$conversions       = get_option( 'tva_conversions', array() );
		$enrolled_users    = get_option( 'tva_enrolled_users', array() );
		$excluded          = get_term_meta( $this->term->ID, 'tva_excluded', true );
		$label_id          = get_term_meta( $this->term->ID, 'tva_label', true );
		$default_label     = TVA_Const::default_label();
		$lessons_text      = isset( $this->settings['template']['course_lessons_plural'] ) ? $this->settings['template']['course_lessons_plural'] : TVA_Const::TVA_COURSE_LESSONS_TEXT;
		$lesson_template   = get_term_meta( $this->term->ID, 'tva_term_lesson_template', true );
		$conversions_count = array_key_exists( $this->term->ID, $conversions ) ? $conversions[ $this->term->ID ] : 0;
		$enrolled_count    = array_key_exists( $this->term->ID, $enrolled_users ) ? count( $enrolled_users[ $this->term->ID ] ) : 0;

		if ( isset( $lesson_template['post_media'] ) ) {
			$lesson_template['post_media']['media_url'] = ''; //reset media_url
		}

		$this->term->roles               = $roles ? $roles : new stdClass();
		$this->term->conversions         = $conversions_count;
		$this->term->enrolled_users      = $enrolled_count + $conversions_count;
		$this->term->label               = $label_id ? $label_id : 0;
		$this->term->excluded            = $excluded ? $excluded : 0;
		$this->term->label_default_color = $default_label['color'];
		$this->term->term_video_embed    = '';
		$this->term->lessons_text        = $lessons_text;
		$this->term->lesson_template     = $lesson_template
			? $lesson_template
			: array(
				'lesson_type'    => 'text',
				'comment_status' => $this->term->comment_status,
				'post_media'     => array(
					'media_extra_options' => array(),
					'media_type'          => '',
					'media_url'           => '',
				),
			);

		if ( $this->term->term_media ) {
			$fn                           = 'tva_get_' . $this->term->term_media['media_type'] . '_embed_code';
			$this->term->term_video_embed = $fn( $this->term->ID, 'term' );
		}
	}

	/**
	 * Set term membership ids
	 */
	public function set_membership_ids() {
		$membership_ids = get_term_meta( $this->term->ID, 'tva_membership_ids', true );

		/**
		 * Backwards compatibility for membership ids when membermouse was released and now we use multiple membership plugins for protection
		 */
		if ( $membership_ids && ! isset( $membership_ids['membermouse'] ) ) {
			$ids = array();
			/**
			 * We're now saving ids as values and not as keys because of some
			 * backbone issues, so for backwards compatibility we should inverse these
			 */
			foreach ( $membership_ids as $id => $v ) {
				//bug generator
				//$ids[] = $id;
			}
			$membership_ids = array(
				'membermouse' => $ids,
			);
		}

		if ( ! empty( $this->settings['membership_plugin'] ) ) {
			foreach ( $this->settings['membership_plugin'] as $membership ) {
				$membership_plugins[ $membership['tag'] ] = array();
			}

			$membership_ids = ! is_array( $membership_ids ) ? array() : $membership_ids;
			$membership_ids = array_merge( $membership_plugins, $membership_ids );
		}

		if ( $membership_ids ) {
			$this->term->membership_ids = $membership_ids;
		}
	}

	/**
	 * Set term bundles ids
	 */
	public function set_bundles_ids() {
		$bundle_ids = get_term_meta( $this->term->ID, 'tva_bundle_ids', true );

		if ( $bundle_ids && ! isset( $bundle_ids['sendowl'] ) ) {
			$bundle_ids['sendowl'] = array();
		}

		if ( $bundle_ids ) {
			$this->term->bundle_ids = $bundle_ids;
		}
	}

	/**
	 * Gert term posts
	 * - lazy initializer
	 */
	public function get_posts() {

		if ( ! empty( $this->posts ) ) {
			return $this->posts;
		}

		$args = array(
			'posts_per_page' => - 1,
			'post_type'      => array( TVA_Const::LESSON_POST_TYPE, TVA_Const::CHAPTER_POST_TYPE, TVA_Const::MODULE_POST_TYPE ),
			'post_status'    => array( 'publish', 'draft' ),
			'tax_query'      => array(
				array(
					'taxonomy' => TVA_Const::COURSE_TAXONOMY,
					'field'    => 'term_id',
					'terms'    => array( $this->term->ID ),
					'operator' => 'IN',
				),
			),
			'order'          => 'ASC',
		);

		if ( isset( $this->filters['published'] ) ) {
			$args['post_status'] = array( 'publish' );
		}

		return $this->posts = get_posts( $args );
	}

	/**
	 * Set content
	 */
	public function set_content() {

		$this->get_posts();

		if ( empty( $this->posts ) ) {
			return;
		}

		$this->set_term_content();
	}

	/**
	 * @param array $list
	 * @param int   $post_parent
	 *
	 * @return array
	 */
	public function _filter( $list = array(), $post_parent = 0 ) {
		$posts = wp_list_filter( $list, array( 'post_parent' => $post_parent ) );

		return array_values( $posts );
	}

	/**
	 * Set term content
	 */
	public function set_term_content() {

		$posts = $this->_filter( $this->posts );

		if ( empty( $posts ) ) {
			return;
		}

		$this->term->is_empty     = false;
		$this->term->content_type = $this->get_item_nice_name( $posts[0]->post_type );

		$posts     = $this->parse_posts( $posts );
		$item_name = $this->get_item_nice_name( $posts[0]->post_type );

		if ( $item_name != 'lessons' ) {
			foreach ( $posts as $key => $post ) {
				$nice_name     = $this->get_item_nice_name( $post->post_type );
				$posts[ $key ] = $this->set_post_content( $post, array( $nice_name => $key + 1 ) );
			}
		}

		$this->term->$item_name = $posts;
	}

	/**
	 * Set post content
	 *
	 * @param $post
	 *
	 * @return mixed
	 */
	public function set_post_content( $post, $parent_data = array() ) {
		$childs = $this->_filter( $this->posts, $post->ID );
		$childs = $this->parse_posts( $childs, $parent_data );

		if ( ! empty( $childs ) ) {
			$item_name = $this->get_item_nice_name( $childs[0]->post_type );

			//ensure recursive calls for nested levels of childs
			foreach ( $childs as $key => $child ) {
				$nice_name                 = $this->get_item_nice_name( $child->post_type );
				$parent_data[ $nice_name ] = $key + 1;

				$this->set_post_content( $child, $parent_data );
			}

			$post->allowed    = $childs[0]->allowed;
			$post->$item_name = $childs;
		}

		return $post;
	}

	/**
	 * @param array $posts
	 *
	 * @return array
	 */
	public function parse_posts( $posts = array(), $parent_data = array() ) {

		if ( empty( $posts ) ) {
			return $posts;
		}

		$item_name = $this->get_item_nice_name( $posts[0]->post_type );
		$fn        = 'parse_' . $item_name;
		$posts     = $this->$fn( $posts, $parent_data );
		usort( $posts, array( $this, 'sort_by_order' ) );

		return $posts;
	}

	/**
	 * @param array $posts
	 * @param array $parent_data
	 *
	 * @return array|mixed
	 */
	public function parse_lessons( $posts = array(), $parent_data = array() ) {
		foreach ( $posts as $key => $post ) {
			if ( ! isset( $post->location ) ) {
				$post->location = array();
			}

			$post            = tva_get_post_data( $post );
			$post->allowed   = true;
			$post->course_id = $this->term->ID;
			$posts[ $key ]   = $post;
		}

		usort( $posts, array( $this, 'sort_by_order' ) );

		foreach ( $posts as $key => $post ) {
			if ( $post->post_status === 'publish' ) {
				$post->course_order = $this->lessons_counter;
				$this->lessons_counter ++;
			}

			$post->location['lessons'] = $this->lessons_counter;
			$post->location            = array_merge( $post->location, $parent_data );

			if ( $this->term->logged_in && isset( $this->filters['protection'] ) && $this->filters['protection'] === true ) {
				//$post->allowed = TVA_Const::handle_post_protection( $post, $this->term->excluded );
				$post->allowed = false;
			}

			$posts[ $key ] = $post;
		}

		//$posts = $this->ensure_correct_order( $posts );

		return $posts;
	}

	/**
	 * @param array $posts
	 * @param array $parent_data
	 *
	 * @return array|mixed
	 */
	public function parse_chapters( $posts = array(), $parent_data = array() ) {
		foreach ( $posts as $key => $post ) {
			if ( ! isset( $post->location ) ) {
				$post->location = array();
			}

			if ( isset( $parent_data['nice_name'] ) && isset( $parent_data['parent_index'] ) ) {
				$post->location[ $parent_data['nice_name'] ] = $parent_data['parent_index'];
			}

			$post->lessons           = array();
			$post->order             = (int) get_post_meta( $post->ID, 'tva_chapter_order', true );
			$post->tva_chapter_order = get_post_meta( $post->ID, 'tva_chapter_order', true );
			$post->allowed           = true;
			$post->course_id         = $this->term->ID;
			$posts[ $key ]           = $post;
		}

		//$posts = $this->ensure_correct_order( $posts );

		return $posts;
	}

	/**
	 * @param array $posts
	 *
	 * @return array
	 */
	public function parse_modules( $posts = array(), $parent_index = array() ) {

		foreach ( $posts as $key => $post ) {
			$post->cover_image      = get_post_meta( $post->ID, 'tva_cover_image', true );
			$post->lessons          = array();
			$post->chapters         = array();
			$post->order            = (int) get_post_meta( $post->ID, 'tva_module_order', true );
			$post->tva_module_order = get_post_meta( $post->ID, 'tva_module_order', true );
			$post->allowed          = true;
			$post->course_id        = $this->term->ID;
		}

		//$posts = $this->ensure_correct_order( $posts );

		return $posts;
	}

	/**
	 * Make sure we have the correct order set
	 *
	 * @param $posts
	 *
	 * @return mixed
	 */
	public function ensure_correct_order( $posts ) {

		/**
		 * Make sure we only sort posts in admin
		 */
		if ( isset( $this->filters['published'] ) && true === $this->filters['published'] ) {
			return $posts;
		}

		usort( $posts, array( $this, 'sort_by_order' ) );

		foreach ( $posts as $index => $post ) {
			if ( $post->order !== $index ) {
				$post->order = $index;
				//overkill
				//update_post_meta( $post->ID, $post->post_type . '_order', $post->order );
			}
		}

		return $posts;
	}

	/**
	 * @param $a
	 * @param $b
	 *
	 * @return mixed
	 */
	public function sort_by_order( $a, $b ) {
		return $a->order - $b->order;
	}

	/**
	 * Get erm formats
	 *
	 * @return array
	 */
	public function get_formats() {
		$lessons = wp_list_filter( $this->posts, array( 'post_type' => TVA_Const::LESSON_POST_TYPE, 'post_status' => 'publish' ) );
		$formats = wp_list_pluck( $lessons, 'lesson_type' );

		return array_unique( array_values( $formats ) );
	}

	/**
	 * Set term formats
	 */
	public function set_formats() {
		$formats = $this->get_formats();

		if ( tva_is_course_guide( $this->term ) ) {
			$this->term->course_type       = $this->settings['template']['course_type_guide'];
			$this->term->course_type_name  = 'guide';
			$this->term->course_type_class = 'guide';
		} elseif ( in_array( 'text', $formats ) && in_array( 'audio', $formats ) && in_array( 'video', $formats ) ) {
			$this->term->course_type       = $this->settings['template']['course_type_big_mix'];
			$this->term->course_type_name  = 'big_mix';
			$this->term->course_type_class = 'big_mix';
		} elseif ( in_array( 'text', $formats ) && in_array( 'audio', $formats ) ) {
			$this->term->course_type       = $this->settings['template']['course_type_audio_text_mix'];
			$this->term->course_type_name  = 'audio_text_mix';
			$this->term->course_type_class = 'audio_text_mix';
		} elseif ( in_array( 'text', $formats ) && in_array( 'video', $formats ) ) {
			$this->term->course_type       = $this->settings['template']['course_type_video_text_mix'];
			$this->term->course_type_name  = 'video_text_mix';
			$this->term->course_type_class = 'video_text_mix';
		} elseif ( in_array( 'audio', $formats ) && in_array( 'video', $formats ) ) {
			$this->term->course_type       = $this->settings['template']['course_type_video_audio_mix'];
			$this->term->course_type_name  = 'video_audio_mix';
			$this->term->course_type_class = 'video_audio_mix';
		} elseif ( ! empty( $formats ) ) {
			$type                          = $formats[0];
			$this->term->course_type       = isset( $this->settings['template'][ 'course_type_' . $type ] ) ?
				$this->settings['template'][ 'course_type_' . $type ] : '';
			$this->term->course_type_name  = $type;
			$this->term->course_type_class = $type;
		}
	}

	/**
	 * Set course counters
	 */
	public function set_counters() {
		$lessons    = wp_list_filter( $this->posts, array( 'post_type' => TVA_Const::LESSON_POST_TYPE ) );
		$chapters   = wp_list_filter( $this->posts, array( 'post_type' => TVA_Const::CHAPTER_POST_TYPE ) );
		$modules    = wp_list_filter( $this->posts, array( 'post_type' => TVA_Const::MODULE_POST_TYPE ) );
		$pb_lessons = wp_list_filter( $lessons, array( 'post_status' => 'publish' ) );

		$this->term->lessons_count           = count( $lessons );
		$this->term->published_lessons_count = count( $pb_lessons );
		$this->term->chapters_count          = count( $chapters );
		$this->term->modules_count           = count( $modules );
	}

	/**
	 * Any actions required after course content is set will be executed here
	 */
	public function after_content_set() {
		if ( $this->term->is_empty ) {
			return;
		}

		$this->set_counters();
		$this->set_formats();
		$this->set_late_term_meta();
	}

	/**
	 * Here we set the data which depends by course content/type
	 */
	public function set_late_term_meta() {
		$allowed = wp_list_filter( $this->posts, array( 'allowed' => true ) );

		$this->term->can_comment = count( $allowed ) === count( $this->posts );
		$this->term->posts       = $this->posts;
		$this->term->allowed     = count( $allowed ) > 0;
		$this->term->more_class  = tva_is_course_guide( $this->term ) ? 'read' : 'details';
		$this->term->more_text   = ( isset( $this->settings['template']['course_more_read'] ) )
			? ( tva_is_course_guide( $this->term )
				? $this->settings['template']['course_more_read']
				: $this->settings['template']['course_more_details'] ) : '';
	}

	/**
	 * @param $id
	 *
	 * @return bool|WP_Term
	 */
	public static function tva_get_term_by_child_id( $id ) {
		$post  = get_post( $id );
		$terms = wp_get_object_terms( $post->ID, TVA_Const::COURSE_TAXONOMY );

		if ( ! isset( $terms[0] ) || ! $terms[0] instanceof WP_Term || TVA_Const::COURSE_TAXONOMY !== $terms[0]->taxonomy ) {
			return false;
		}

		return $terms[0];
	}
}
