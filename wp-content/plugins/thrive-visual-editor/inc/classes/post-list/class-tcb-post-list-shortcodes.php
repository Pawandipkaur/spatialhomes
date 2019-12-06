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
 * Class TCB_Post_List_Shortcodes
 */
class TCB_Post_List_Shortcodes {

	private static $_instance = null;

	private $execution_stack = array();

	public static $dynamic_shortcodes = array(
		'tcb_post_content'            => 'the_content',
		'tcb_post_title'              => 'the_title',
		'tcb_post_featured_image'     => 'post_thumbnail',
		'tcb_post_author_picture'     => 'author_picture',
		'tcb_post_list'               => 'post_list',
		'tcb_post_published_date'     => 'post_date',
		'tcb_post_tags'               => 'the_tags',
		'tcb_post_categories'         => 'the_category',
		'tcb_post_author_name'        => 'the_author',
		'tcb_post_author_bio'         => 'author_bio',
		'tcb_post_comments_number'    => 'comments_number',
		'tcb_featured_image_url'      => 'the_post_thumbnail_url',
		'tcb_author_image_url'        => 'author_image_url',
		'tcb_the_id'                  => 'the_id',
		'tcb_post_list_dynamic_style' => 'tcb_post_list_dynamic_style',
		'tcb_pagination'              => 'pagination',
	);

	public function __construct() {
		add_action( 'init', array( $this, 'init' ) );

		add_action( 'wp_print_footer_scripts', array( 'TCB_Post_List', 'wp_print_footer_scripts' ) );

		add_filter( 'tcb_content_allowed_shortcodes', array( $this, 'tcb_content_allowed_shortcodes' ) );
	}

	/**
	 * Return shortcode execution stack
	 *
	 * @return array
	 */
	public function get_execution_stack() {
		return $this->execution_stack;
	}

	/**
	 * We need to add our shortcodes to this array in order for them to be processed in the editor.
	 * If we're on the frontend, we don't have to do this.
	 *
	 * @param $shortcodes
	 *
	 * @return array
	 */
	public function tcb_content_allowed_shortcodes( $shortcodes ) {
		if ( is_editor_page_raw() ) {
			$shortcodes = array_merge( $shortcodes, array_keys( TCB_Post_List_Shortcodes::$dynamic_shortcodes ) );
		}

		return $shortcodes;
	}

	/**
	 * TCB_Post_List_Shortcodes instance
	 *
	 * @return null|TCB_Post_List_Shortcodes
	 */
	public static function instance() {
		if ( empty( static::$_instance ) ) {
			static::$_instance = new self();
		}

		return static::$_instance;
	}

	/**
	 * Add all shortcodes and their callbacks
	 */
	public function init() {
		foreach ( static::$dynamic_shortcodes as $shortcode => $func ) {
			add_shortcode(
				$shortcode,
				function ( $attr, $content, $tag ) {
					$func   = TCB_Post_List_Shortcodes::$dynamic_shortcodes[ $tag ];
					$output = '';

					if ( method_exists( __CLASS__, $func ) ) {
						$attr = TCB_Post_List_Shortcodes::parse_attr( $attr, $tag );

						TCB_Post_List_Shortcodes()->execution_stack[] = array(
							'shortcode' => $tag,
							'attr'      => $attr,
						);

						$output = TCB_Post_List_Shortcodes::$func( $attr, $content, $tag );

						$output = apply_filters( 'tcb_render_shortcode_' . $tag, $output, $attr, $content );

						array_pop( TCB_Post_List_Shortcodes()->execution_stack );
					}

					return $output;
				}
			);
		}

		$GLOBALS[ TCB_POST_LIST_LOCALIZE ] = array();
	}

	/**
	 * @param array $wrap_args
	 * @param array $attr
	 *
	 * @return string
	 */
	public static function before_wrap( $wrap_args = array(), $attr = array() ) {

		/* attributes that have to be present also on front */
		$front_attr = array(
			'tcb-events',
			'css',
			'masonry',
			'type',
			'pagination-type',
			'no_posts_text',
			'layout',
			'total_post_count',
		);

		$wrap_args = array_merge(
			array(
				'content' => '',
				'tag'     => 'div',
				'id'      => '',
				'class'   => '',
				'attr'    => array(),
			),
			$wrap_args
		);

		/* extra classes that are sent through data attr */
		$wrap_args['class'] .= ' ' . ( strpos( $wrap_args['class'], THRIVE_WRAPPER_CLASS ) === false ? THRIVE_WRAPPER_CLASS : '' ) . ( empty( $attr['class'] ) ? '' : ' ' . $attr['class'] );

		/* attributes that come directly from the shortcode */
		foreach ( $attr as $k => $v ) {
			if ( is_editor_page_raw() || in_array( $k, $front_attr, true ) ) {
				$wrap_args['attr'][ 'data-' . $k ] = $v;
				unset( $wrap_args['attr'][ $k ] );
			}
		}

		/* during ajax we can't render shortcodes, so we add the shortcode tag and class so we can fix them in JS */
		if ( wp_doing_ajax() ) {
			$last_shortcode = end( TCB_Post_List_Shortcodes()->execution_stack );

			$wrap_args['attr']['data-shortcode'] = $last_shortcode['shortcode'];

			$wrap_args['class'] .= ' ' . TCB_SHORTCODE_CLASS;
		}

		return call_user_func_array( array( 'TCB_Utils', 'wrap_content' ), $wrap_args );
	}

	/**
	 * Render the post list element.
	 *
	 * @param array  $attr
	 * @param string $article_content
	 *
	 * @return string
	 */
	public static function post_list( $attr = array(), $article_content = '' ) {
		/* don't render a post list inside another post list ( in 'the_content' shortcode ). */
		if ( TCB_Post_List::is_outside_post_list_render() ) {
			TCB_Post_List::enter_post_list_render();

			$post_list = new TCB_Post_List( $attr, $article_content );

			$content = $post_list->render();

			/* parse the animations that are inside the post list */
			tve_parse_events( $content );

			TCB_Post_List::exit_post_list_render();
		} else {
			/* if the flag is not empty, it means we're already inside a post list shortcode */
			$content = '';
		}

		return $content;
	}

	/**
	 * Shortcode callback for the pagination element.
	 *
	 * @param array  $attr
	 * @param string $content
	 *
	 * @return string
	 */
	public function pagination( $attr = array(), $content = '' ) {
		$pagination = '';

		/* only render if we're outside the post list render */
		if ( TCB_Post_List::is_outside_post_list_render() ) {
			/* if no type is set, use the 'no pagination' type by default */
			$type = empty( $attr['data-type'] ) ? TCB_Pagination::NONE : $attr['data-type'];

			/* compatibility with the old load more setting */
			if ( $type === 'button' ) {
				$type = TCB_Pagination::LOAD_MORE;
			}

			/* call the render function for this pagination shortcode */
			$pagination = tcb_pagination( $type, $attr )->render( $content );
		}

		return $pagination;
	}

	/**
	 * The content shortcode.
	 *
	 * @param array $attr
	 *
	 * @return string
	 */
	public static function the_content( $attr = array() ) {
		return TCB_Post_List_Content::get_content( $attr );
	}

	/**
	 * Callback for the author bio shortcode.
	 *
	 * @param $attr
	 *
	 * @return string
	 */
	public static function author_bio( $attr = array() ) {
		$attr['is_inner_frame'] = TCB_Editor()->is_inner_frame();
		$content                = tcb_template( 'post-list-sub-elements/author-bio.php', $attr, true );
		unset( $attr['is_inner_frame'] );

		$tag     = empty( $attr['tag'] ) ? 'div' : $attr['tag'];
		$classes = TCB_POST_AUTHOR_BIO_IDENTIFIER . ' ' . TCB_SHORTCODE_CLASS;

		if ( $tag === 'span' ) {
			$classes .= ' tcb-plain-text';
		}

		return static::before_wrap( array(
			'content' => $content,
			'tag'     => $tag,
			'class'   => $classes,
		), $attr );
	}

	/**
	 * the_author
	 *
	 * @param $attr
	 *
	 * @return string
	 */
	public static function the_author( $attr = array() ) {
		$attr['is_inner_frame'] = TCB_Editor()->is_inner_frame();
		$content                = tcb_template( 'post-list-sub-elements/author-name.php', $attr, true );
		unset( $attr['is_inner_frame'] );

		$tag     = empty( $attr['tag'] ) ? 'p' : $attr['tag'];
		$classes = TCB_POST_AUTHOR_IDENTIFIER . ' ' . TCB_SHORTCODE_CLASS;

		if ( $tag === 'span' ) {
			$classes .= ' tcb-plain-text';
		}

		return static::before_wrap( array(
			'content' => $content,
			'tag'     => $tag,
			'class'   => $classes,
		), $attr );
	}

	/**
	 * Author Picture
	 *
	 * @param array $attr
	 *
	 * @return string
	 */
	public static function author_picture( $attr = array() ) {
		global $post;

		$content    = get_avatar( $post->post_author, 256 );
		$author_url = get_author_posts_url( $post->post_author );

		return static::before_wrap( array(
			'content' => $content,
			'tag'     => 'a',
			'class'   => TCB_POST_AUTHOR_PICTURE_IDENTIFIER . ' ' . TCB_SHORTCODE_CLASS,
			'attr'    => array(
				'href' => $author_url,
			),
		), $attr );
	}

	/**
	 * the_category
	 *
	 * @param $attr
	 *
	 * @return string
	 */
	public static function the_category( $attr = array() ) {
		$attr['is_inner_frame'] = TCB_Editor()->is_inner_frame();
		$content                = tcb_template( 'post-list-sub-elements/post-categories.php', $attr, true );
		unset( $attr['is_inner_frame'] );

		$tag     = empty( $attr['tag'] ) ? 'span' : $attr['tag'];
		$classes = TCB_POST_CATEGORIES_IDENTIFIER . ' ' . TCB_SHORTCODE_CLASS;

		if ( $tag === 'span' ) {
			$classes .= ' tcb-plain-text';
		}

		return static::before_wrap( array(
			'content' => $content,
			'tag'     => $tag,
			'class'   => $classes,
		), $attr );
	}

	/**
	 * Comments number.
	 *
	 * @param $attr
	 *
	 * @return string
	 */
	public static function comments_number( $attr = array() ) {
		if ( ! empty( $attr['url'] ) ) {
			global $post;
			$attr['post_url'] = get_permalink( $post );
		}

		$content = tcb_template( 'post-list-sub-elements/comments-number.php', $attr, true );

		$tag     = empty( $attr['tag'] ) ? 'span' : $attr['tag'];
		$classes = TCB_POST_COMMENTS_NUMBER_IDENTIFIER . ' ' . TCB_SHORTCODE_CLASS;

		if ( $tag === 'span' ) {
			$classes .= ' tcb-plain-text';
		}

		return static::before_wrap( array(
			'content' => $content,
			'tag'     => $tag,
			'class'   => $classes,
		), $attr );
	}

	/**
	 * The post_date shortcode callback.
	 *
	 * @param $attr
	 *
	 * @return string
	 */
	public static function post_date( $attr = array() ) {
		if ( empty( $attr['format'] ) ) {
			$format = get_option( 'date_format' ) . ' ' . get_option( 'time_format' );
		} else {
			$format = $attr['format'];
		}

		$attr['date_format'] = $format;
		$content             = tcb_template( 'post-list-sub-elements/post-date.php', $attr, true );
		unset( $attr['date_format'] );

		$tag     = empty( $attr['tag'] ) ? 'span' : $attr['tag'];
		$classes = TCB_POST_DATE . ' ' . TCB_SHORTCODE_CLASS;

		if ( $tag === 'span' ) {
			$classes .= ' tcb-plain-text';
		}

		return static::before_wrap( array(
			'content' => TCB_Utils::wrap_content( $content, 'time' ),
			'tag'     => $tag,
			'class'   => $classes,
		), $attr );
	}

	/**
	 * Post featured image
	 *
	 * @param array $attr
	 *
	 * @return string
	 */
	public static function post_thumbnail( $attr = array() ) {
		$image = '';

		if ( has_post_thumbnail() ) {
			$image = static::shortcode_function_content( 'the_post_thumbnail', array( $attr['size'] ) );
		} else {
			if ( TCB_Editor()->is_inner_frame() || ( ! empty( $attr['type-display'] ) && $attr['type-display'] === 'default_image' ) || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
				$image = TCB_Utils::wrap_content( '', 'img', '', '', array( 'src' => TCB_Post_List_Featured_Image::get_default_url() ) );
			}
			/* if we're not in the editor or the default display option is not selected, then don't display anything */
		}

		/* add the post url only when the post url option is selected */
		$url_attr = $attr['type-url'] === 'post_url' ?
			array(
				'href'  => get_permalink(),
				'title' => get_the_title(),
			) : array();

		$attr['post_id'] = get_the_ID();

		return static::before_wrap( array(
			'content' => $image,
			'tag'     => 'a',
			'class'   => TCB_POST_THUMBNAIL_IDENTIFIER . ' ' . TCB_SHORTCODE_CLASS,
			'attr'    => $url_attr,
		), $attr );
	}

	/**
	 * Post title shortcode.
	 *
	 * @param array $attr
	 *
	 * @return string
	 */
	public static function the_title( $attr = array() ) {
		$content = tcb_template( 'post-list-sub-elements/post-title.php', $attr, true );

		$tag     = empty( $attr['tag'] ) ? 'h2' : $attr['tag'];
		$classes = TCB_POST_TITLE_IDENTIFIER . ' ' . TCB_SHORTCODE_CLASS;

		if ( $tag === 'span' ) {
			$classes .= ' tcb-plain-text';
		}

		return static::before_wrap( array(
			'content' => $content,
			'tag'     => $tag,
			'class'   => $classes,
		), $attr );
	}

	/**
	 * the_tags
	 *
	 * @param $attr
	 *
	 * @return string
	 */
	public static function the_tags( $attr = array() ) {
		$attr['is_inner_frame'] = TCB_Editor()->is_inner_frame();
		$content                = tcb_template( 'post-list-sub-elements/post-tags.php', $attr, true );
		unset( $attr['is_inner_frame'] );

		$tag     = empty( $attr['tag'] ) ? 'div' : $attr['tag'];
		$classes = TCB_POST_TAGS_IDENTIFIER . ' ' . TCB_SHORTCODE_CLASS;

		if ( $tag === 'span' ) {
			$classes .= ' tcb-plain-text';
		}

		/* add an extra class so we can hide completely hide the container div on the frontend */
		if ( empty( $content ) ) {
			$classes .= ' no-tags';
		}

		return static::before_wrap( array(
			'content' => $content,
			'tag'     => $tag,
			'class'   => $classes,
		), $attr );
	}

	/**
	 * the_permalink
	 *
	 * @param $attr
	 *
	 * @return string
	 */
	public static function the_permalink( $attr = array() ) {
		return static::shortcode_function_content( 'the_permalink' );
	}

	/**
	 * Call do_shortcode() on the dynamic style from the saved content and wrap it in a <style> tag.
	 *
	 * @param array  $attr
	 * @param string $dynamic_style
	 *
	 * @return string
	 */
	public static function tcb_post_list_dynamic_style( $attr = array(), $dynamic_style = '' ) {
		return TCB_Utils::wrap_content( do_shortcode( $dynamic_style ), 'style', '', 'tcb-post-list-dynamic-style', array( 'type' => 'text/css' ) );
	}

	/**
	 * Return the post ID.
	 *
	 * @return string
	 */
	public static function the_id() {
		return get_the_ID();
	}

	/**
	 * Return the featured image url.
	 *
	 * @param array $data
	 *
	 * @return string
	 */
	public static function the_post_thumbnail_url( $data = array() ) {
		$size = empty( $data['size'] ) ? 'full' : $data['size'];

		if ( has_post_thumbnail() ) {
			$image_url = static::shortcode_function_content( 'the_post_thumbnail_url', array( $size ) );
		} else {
			$image_url = TCB_Post_List_Featured_Image::get_default_url();
		}
		/* if we're in the editor, append a dynamic flag at the end so we can recognize that the URL is dynamic in the editor */
		if ( is_editor_page_raw() ) {
			$image_url = add_query_arg( array(
				'dynamic_featured' => 1,
				'size'             => $size,
			), $image_url );
		}

		return $image_url;
	}

	/**
	 * Author image url
	 *
	 * @return string
	 */
	public static function author_image_url() {
		$avatar_url = TCB_Post_List_Author_Image::author_avatar();

		/* if we're in the editor, append a dynamic flag at the end so we can recognize that the URL is dynamic */
		if ( is_editor_page_raw() ) {
			$avatar_url = add_query_arg( array(
				'dynamic_author' => 1,
			), $avatar_url );
		}

		return $avatar_url;
	}

	/**
	 * Return the content of the shortcode function
	 *
	 * @param string $func
	 * @param array  $args
	 *
	 * @return string
	 */
	public static function shortcode_function_content( $func, $args = array() ) {
		ob_start();

		is_callable( $func ) && call_user_func_array( $func, $args );
		$content = ob_get_contents();

		ob_end_clean();

		return $content;
	}

	/**
	 * There are some cases when one do_shortcode is not enough
	 *
	 * @param      $content
	 * @param bool $ignore_html
	 *
	 * @return mixed|string
	 */
	public static function do_shortcode( $content, $ignore_html = false ) {
		$content = do_shortcode( $content, $ignore_html );

		/* in some cases, when this shortcode is in a attribute, it might not be replaced, so we do it manually */
		$content = str_replace( '[tcb_post_the_permalink]', static::the_permalink(), $content );

		$content = static::check_dynamic_links( $content );

		return $content;
	}

	/**
	 * Parse shortcode attributes before getting to the shortcode function
	 *
	 * @param $attr
	 * @param $tag
	 *
	 * @return array
	 */
	public static function parse_attr( $attr, $tag ) {
		if ( ! is_array( $attr ) ) {
			$attr = array();
		}

		/* set default values if available */
		$attr = array_merge( static::default_attr( $tag ), $attr );

		/* escape attributes and decode [ and ] -> mostly used for json_encode */
		$attr = array_map( function ( $v ) {
			$v = esc_attr( $v );

			return str_replace( array( '|{|', '|}|' ), array( '[', ']' ), $v );
		}, $attr );

		return $attr;
	}

	/**
	 * Default values for some shortcodes
	 *
	 * @param $tag
	 *
	 * @return array|mixed
	 */
	private static function default_attr( $tag ) {
		$default = array(
			'tcb_post_featured_image' => array(
				'type-url'     => 'post_url',
				'type-display' => 'default_image',
				'css'          => '',
				'size'         => 'full',
			),
			'tcb_post_tags'           => array(
				'link' => 1,
			),
			'tcb_post_categories'     => array(
				'link' => 1,
			),
		);

		return isset( $default[ $tag ] ) ? $default[ $tag ] : array();
	}

	/**
	 * For the dynamic links just replace the "shortcode" with the url so it wont mess with froala and element style
	 *
	 * @param $content
	 *
	 * @return mixed
	 */
	private static function check_dynamic_links( $content ) {
		global $post;
		$post_id = $post->ID;
		$content = str_replace( '[tcb_post_the_permalink]', get_permalink( $post_id ), $content );
		$content = str_replace( '[tcb_post_archive_link]', TCB_Post_List::get_post_type_archive_link( $post_id ), $content );
		$content = str_replace( '[tcb_post_author_link]', TCB_Post_List::get_author_posts_url( $post_id ), $content );
		$content = str_replace( '[tcb_post_date_link]', TCB_Post_List::get_day_link(), $content );
		$content = str_replace( '[tcb_post_comments_link]', TCB_Post_List::comments_link( $post_id ), $content );

		return $content;
	}
}

/**
 * Return TCB_Post_List_Shortcodes instance
 *
 * @return null|TCB_Post_List_Shortcodes
 */
function tcb_post_list_shortcodes() {
	return TCB_Post_List_Shortcodes::instance();
}

new TCB_Post_List_Shortcodes();
