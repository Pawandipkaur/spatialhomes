<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

defined( 'THRIVE_WRAPPER_CLASS' ) || define( 'THRIVE_WRAPPER_CLASS', 'thrv_wrapper' );
defined( 'TCB_POST_LIST_CLASS' ) || define( 'TCB_POST_LIST_CLASS', 'tcb-post-list' );

/* class that applies to ALL shortcodes ( the post-list shortcodes AND the TTB shortcodes ) */
defined( 'TCB_SHORTCODE_CLASS' ) || define( 'TCB_SHORTCODE_CLASS', 'tcb-shortcode' );

/* class that applies ONLY to the post-list shortcodes */
defined( 'TCB_DO_NOT_RENDER_POST_LIST' ) || define( 'TCB_DO_NOT_RENDER_POST_LIST', 'do-not-render-post-list' );

defined( 'TCB_POST_WRAPPER_CLASS' ) || define( 'TCB_POST_WRAPPER_CLASS', 'post-wrapper' );
defined( 'TCB_POST_LIST_LOCALIZE' ) || define( 'TCB_POST_LIST_LOCALIZE', 'tcb_post_list_localize' );

/* constants for the sub-element identifiers */
defined( 'TCB_POST_AUTHOR_IDENTIFIER' ) || define( 'TCB_POST_AUTHOR_IDENTIFIER', 'tcb-post-author' );
defined( 'TCB_POST_AUTHOR_BIO_IDENTIFIER' ) || define( 'TCB_POST_AUTHOR_BIO_IDENTIFIER', 'tcb-post-author-bio' );
defined( 'TCB_POST_AUTHOR_PICTURE_IDENTIFIER' ) || define( 'TCB_POST_AUTHOR_PICTURE_IDENTIFIER', 'tcb-post-author-picture' );
defined( 'TCB_POST_CATEGORIES_IDENTIFIER' ) || define( 'TCB_POST_CATEGORIES_IDENTIFIER', 'tcb-post-categories' );
defined( 'TCB_POST_COMMENTS_NUMBER_IDENTIFIER' ) || define( 'TCB_POST_COMMENTS_NUMBER_IDENTIFIER', 'tcb-post-comments-number' );
defined( 'TCB_POST_DATE' ) || define( 'TCB_POST_DATE', 'tcb-post-date' );
defined( 'TCB_POST_TAGS_IDENTIFIER' ) || define( 'TCB_POST_TAGS_IDENTIFIER', 'tcb-post-tags' );
defined( 'TCB_POST_THUMBNAIL_IDENTIFIER' ) || define( 'TCB_POST_THUMBNAIL_IDENTIFIER', 'tcb-post-thumbnail' );
defined( 'TCB_POST_TITLE_IDENTIFIER' ) || define( 'TCB_POST_TITLE_IDENTIFIER', 'tcb-post-title' );

/**
 * Class TCB_Post_List
 */
class TCB_Post_List {

	protected $css;
	protected $attr;
	protected $query;
	protected $article;
	protected $article_attr;

	/**
	 * TCB_Post_List constructor.
	 *
	 * @param array  $attr
	 * @param string $article
	 */
	public function __construct( $attr = array(), $article = '' ) {
		$this->attr = array_merge( static::default_args(), $attr );

		$this->attr['element-name'] = __( 'Post List', 'thrive-cb' );

		$this->article_attr = self::post_shortcode_data( $attr );
		$this->article      = unescape_invalid_shortcodes( $article );

		$this->css = empty( $attr['css'] ) ? substr( uniqid( 'tve-u-', true ), 0, 17 ) : $attr['css'];

		/* if the query attribute is not empty, store it */
		if ( ! empty( $attr['query'] ) ) {
			/* replace single quotes with double quotes */
			$decoded_string = str_replace( "'", '"', html_entity_decode( $attr['query'], ENT_QUOTES ) );

			/* replace newlines and tabs */
			$decoded_string = preg_replace( '/[\r\n]+/', ' ', $decoded_string );

			$this->query = json_decode( $decoded_string, true );
		}

		$this->hooks();
	}

	private function hooks() {
		add_filter( 'post_class', array( $this, 'article_class' ) );
		add_filter( 'tcb_post_attributes', array( $this, 'tcb_post_attributes' ), 10, 2 );
	}

	/**
	 * Render a custom post list
	 *
	 * @return string
	 */
	public function render() {
		global $post;
		/* save a reference to the current global $post so we can restore it at the end */
		$current_post = $post;

		$post_query = static::prepare_wp_query_args( $this->query );
		$query      = new WP_Query( $post_query );

		/* add the total post count for this query - we need this for pagination */
		$this->attr['total_post_count'] = $query->found_posts;

		$GLOBALS[ TCB_POST_LIST_LOCALIZE ][] = array(
			'identifier' => '[data-css="' . $this->css . '"]',
			'template'   => $this->css,
			'content'    => $this->article,
			'attr'       => $this->attr,
			'query'      => $post_query,
		);

		$class   = static::class_attr( $this->attr );
		$content = '';

		if ( empty( $query->posts ) ) {
			/* even if there are no posts, we still display the template because in case we modify the query we will have something to sync */
			$content = $this->article_content();

			/* hide everything inside */
			$class .= ' empty-list';

			/* text to display for no posts */
			$this->attr['no_posts_text'] = isset( $this->query['no_posts_text'] ) ? $this->query['no_posts_text'] : '';
		} else {
			foreach ( $query->posts as $post ) {
				$content .= $this->article_content();
			}
		}

		$post = $current_post;

		if ( is_editor_page() ) {
			$shared_styles = '';
		} else {
			/* add shared styles only once for the whole post list and only in front */
			$shared_styles = tve_get_shared_styles( $content );

			/* don't add anything if it's just an empty wrapper */
			$stripped = strip_tags( $shared_styles );
			if ( empty( $stripped ) ) {
				$shared_styles = '';
			}
		}

		/* render the shared styles just before the post list wrapper */
		$content = $shared_styles . TCB_Post_List_Shortcodes::before_wrap(
				array(
					'content' => $content,
					'tag'     => 'main',
					'id'      => empty( $this->attr['id'] ) ? '' : $this->attr['id'],
					'class'   => $class,
				), $this->attr );

		return $content;
	}

	/**
	 * Parse post list shortcode data and make sure we have some defaults in case of empty data
	 *
	 * @param array $attr
	 *
	 * @return array
	 */
	public static function post_shortcode_data( $attr = array() ) {
		$data = array();

		foreach ( $attr as $k => $v ) {
			if ( strpos( $k, 'article-' ) !== false ) {
				$data[ str_replace( 'article-', '', $k ) ] = $v;
			}
		}

		return $data;
	}

	/**
	 * Post list classes to be displayed depending on the attributes and screen
	 *
	 * @param array $attr
	 * @param bool  $is_main
	 *
	 * @return string
	 */
	public static function class_attr( $attr = array(), $is_main = false ) {
		$classes = array();

		if ( ! $is_main || ! is_singular() ) {
			$classes[] = TCB_POST_LIST_CLASS;
			if ( is_editor_page_raw() ) {
				$classes[] = 'tcb-compact-element';
				$classes[] = 'tcb-selector-no_save';
			}
		}

		if ( isset( $attr['type'] ) && $attr['type'] === 'masonry' ) {
			$classes[] = 'tve_post_grid_masonry';
		}

		return implode( ' ', $classes );
	}

	/**
	 * Render article content.
	 *
	 * @return mixed|string
	 */
	public function article_content() {
		$attributes = apply_filters( 'tcb_post_attributes', $this->article_attr, get_post() );

		$post_id = get_the_ID();

		if ( is_editor_page_raw() ) {
			/* in edit mode, add the post id to each article */
			$attributes['data-id'] = $post_id;
		}

		$content = empty( $this->article ) ? tcb_template( 'elements/post-list-article.php', null, true ) : $this->article;

		if ( static::has_read_more( $content ) ) {
			add_filter( 'the_content_more_link', array( 'TCB_Post_List_Content', 'the_content_more_link_filter' ) );
		}

		$content = TCB_Post_List_Shortcodes::do_shortcode( $content );

		if ( static::has_read_more( $content ) ) {
			remove_filter( 'the_content_more_link', array( 'TCB_Post_List_Content', 'the_content_more_link_filter' ) );
		}

		$content = TCB_Post_List_Shortcodes::before_wrap( array(
			'content' => apply_filters( 'tcb_post_list_article_content', $content ),
			'tag'     => 'article',
			'id'      => 'post-' . $post_id,
			'class'   => static::post_class(),
			'attr'    => $attributes + array( 'data-selector' => '.' . TCB_POST_WRAPPER_CLASS ),
		), $this->article_attr );

		return $content;
	}

	/**
	 * Check if the current content of the article already has a read more button/link
	 *
	 * @param $content
	 *
	 * @return bool
	 */
	public static function has_read_more( $content = '' ) {
		return stripos( $content, 'continue reading' ) !== false || stripos( $content, 'read-more' ) !== false || stripos( $content, 'read more' ) !== false;
	}

	/**
	 * Return an array with concentrated post information
	 *
	 * @param $order
	 *
	 * @return array
	 */
	public static function post_info( $order = 0 ) {
		$id = get_the_ID();

		if ( empty( $id ) ) {
			$post = array();
		} else {
			$post = array(
				'tcb_post_categories'      => TCB_Post_List_Shortcodes::the_category( array(
					'link' => 1,
				) ),
				'tcb_post_tags'            => TCB_Post_List_Shortcodes::the_tags( array(
					'link' => 1,
				) ),
				/* localize the timestamp of the post's date - in the editor, this is used by Moment JS. ! Do not wrap this in anything ! */
				'tcb_post_published_date'  => get_the_time( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ) ),
				'tcb_post_title'           => TCB_Post_List_Shortcodes::the_title(),
				'tcb_post_comments_number' => TCB_Post_List_Shortcodes::comments_number(),
				'tcb_post_featured_image'  => TCB_Post_List_Shortcodes::post_thumbnail( array(
					'type-url' => 'post_url',
					'size'     => 'full',
				) ),
				'featured_image_sizes'     => TCB_Post_List_Featured_Image::get_sizes( $id ),
				'tcb_post_author_picture'  => TCB_Post_List_Shortcodes::author_picture(),
				'tcb_post_author_bio'      => TCB_Post_List_Shortcodes::author_bio(),
				'tcb_post_author_name'     => TCB_Post_List_Shortcodes::the_author(),
				'tcb_post_content'         => TCB_Post_List_Shortcodes::the_content( array(
					'size'      => 'content',
					'read_more' => '',
				) ),
				'tcb_post_type'            => get_post_type(),
				'className'                => static::post_class(),
				'ID'                       => $id,
				'order'                    => empty( $order ) ? $id : $order,
				'tcb_post_excerpt'         => TCB_Post_List_Shortcodes::the_content( array(
					'size'      => 'excerpt',
					'read_more' => '',
				) ),
				'tcb_post_words'           => TCB_Post_List_Shortcodes::the_content( array(
					'size'      => 'words',
					'read_more' => '',
					'words'     => 500,
				) ),
				'author_picture'           => TCB_Post_List_Author_Image::get_default_url( $id ),
				'tcb_post_the_permalink'   => get_permalink( $id ),
				'tcb_post_archive_link'    => static::get_post_type_archive_link( $id ),
				'tcb_post_author_link'     => static::get_author_posts_url( $id ),
				'tcb_post_date_link'       => static::get_day_link(),
				'tcb_post_comments_link'   => static::comments_link( $id ),
			);

			$post = array_merge( $post, apply_filters( 'tcb_post_list_post_info', array(), $id ) );
		}

		return $post;
	}

	/**
	 * Callback to add the post class to the article.
	 *
	 * @return string
	 */
	public static function post_class() {
		return implode( ' ', get_post_class() );
	}

	/**
	 * Add attributes to the post wrapper
	 *
	 * @param array   $attributes
	 * @param WP_Post $post
	 *
	 * @return mixed
	 */
	public static function tcb_post_attributes( $attributes, $post ) {
		if ( TCB_Editor()->is_inner_frame() ) {
			$attributes['data-id'] = $post->ID;
		}

		return $attributes;
	}

	/**
	 * Add custom classes to the article wrapper
	 *
	 * @param array $post_class
	 *
	 * @return array
	 */
	public function article_class( $post_class = array() ) {
		$post_class[] = TCB_POST_WRAPPER_CLASS;
		$post_class[] = THRIVE_WRAPPER_CLASS;

		return $post_class;
	}

	/**
	 * Localize the post list in the main frame and in the inner frame.
	 */
	public static function wp_print_footer_scripts() {
		/* when we're on the frontend, localize these for infinite load / load more pagination */
		if ( ! TCB_Editor()->is_inner_frame() && ! is_editor_page_raw() ) {
			foreach ( $GLOBALS[ TCB_POST_LIST_LOCALIZE ] as $post_list ) {

				echo TCB_Utils::wrap_content(
					$post_list['content'],
					'script',
					'',
					'tcb-post-list-template',
					array(
						'type'            => 'text/template',
						'data-identifier' => $post_list['template'],
					)
				);
			}

			/* remove the post content before localizing the posts */
			$posts_localize = array_map(
				function ( $item ) {
					unset( $item['content'] );

					return $item;
				}, $GLOBALS[ TCB_POST_LIST_LOCALIZE ]
			);

			echo TCB_Utils::wrap_content( "var tcb_post_lists=JSON.parse('" . addslashes( json_encode( $posts_localize ) ) . "');", 'script', '', '', array( 'type' => 'text/javascript' ) );
		}
	}

	/**
	 * Prepare wp_query arguments
	 *
	 * @param array $args
	 *
	 * @return array
	 */
	public static function prepare_wp_query_args( $args = array() ) {
		if ( ! is_array( $args ) ) {
			$args = (array) $args;
		}

		/* allow only our query params to be used */
		$query_args = array_intersect_key( $args, array(
			'post_type'            => '',
			'numberposts'          => '',
			'orderby'              => '',
			'order'                => '',
			'offset'               => '',
			'posts_per_page'       => '',
			'tag__in'              => '',
			'category__in'         => '',
			'author__in'           => '',
			'paged'                => '',
			'tag'                  => '',
			'page'                 => '',
			'pagename'             => '',
			'author_name'          => '',
			'category_name'        => '',
			'exclude_current_post' => '',
		) );

		/* nothing for now */
		$defaults = array();

		$args = array_merge( $defaults, $args );
		/* check if we're in a rest request or not */
		$is_rest = defined( 'REST_REQUEST' ) && REST_REQUEST;

		/* Note: if at some point the queried object will require more arguments, remember to whitelist them in JS in getQueriedObject() */
		if ( $is_rest && isset( $args['queried_object'] ) ) {
			$queried_object = (object) $args['queried_object'];
		} else {
			$queried_object = get_queried_object();
		}

		/* in a related query, we get info from the current post or archive type and we display posts based on that */
		if ( isset( $args['filter'] ) && $args['filter'] === 'related' ) {

			/* on author page we get posts from the same author */
			if ( ! empty( $queried_object->data->ID ) ) {

				/* get posts from the same author we're on */
				$query_args['author__in'] = array( $queried_object->data->ID );

			} elseif ( ! empty( $queried_object->ID ) ) {

				/* on singular page we get the terms that the user asked and we get posts based on that */
				$query_args['tax_query'] = array( 'relation' => 'OR' );
				/* get posts that have at least one taxonomy term as the post we're on */

				if ( ! empty( $args['related'] ) && is_array( $args['related'] ) ) {

					foreach ( $args['related'] as $taxonomy ) {

						switch ( $taxonomy ) {

							case 'author':
								$query_args['author'] = $queried_object->post_author;
								break;

							case 'post_format':
								$format = get_post_format( $queried_object->ID );

								if ( $format ) {
									$query_args['tax_query'][] = array(
										'taxonomy' => 'post_format',
										'field'    => 'slug',
										'terms'    => array( 'post-format-' . $format ),
									);
								} else {
									/*
									 * If the post format it's not set, the post is actually a standard post.
									 * In order to take all the standard posts we query the db for all the posts that are not included in the other post formats
									 */
									$terms = array();

									foreach ( array( 'image', 'video', 'audio' ) as $post_format ) {
										$terms[] = 'post-format-' . $post_format;
									}
									$query_args['tax_query'][] = array(
										'taxonomy' => 'post_format',
										'field'    => 'slug',
										'terms'    => $terms,
										'operator' => 'NOT IN',
									);
								}
								break;

							default:
								$post_terms = wp_get_post_terms( $queried_object->ID, $taxonomy, array( 'fields' => 'ids' ) );
								if ( ! empty( $post_terms ) ) {
									$query_args['tax_query'][] = array(
										'taxonomy' => $taxonomy,
										'field'    => 'term_id',
										'terms'    => $post_terms,
									);
								}
						}
					}
				}

				$query_args['post_type'] = get_post_type( $queried_object );

			} elseif ( ! empty( $queried_object->taxonomy ) ) {

				/* on taxonomy page: tag, category... we display posts from the same taxonomy. */
				/* get posts based on the taxonomy we're on */
				$query_args['tax_query'] = array(
					array(
						'taxonomy' => $queried_object->taxonomy,
						'field'    => 'id',
						'terms'    => $queried_object->term_id,
					),
				);

				$taxonomy                = get_taxonomy( $queried_object->taxonomy );
				$query_args['post_type'] = $taxonomy->object_type;
			}

			/* if we have a custom query, we check the rules and display posts based on that */
		} elseif ( isset( $args['rules'] ) && is_array( $args['rules'] ) ) {

			$query_args['tax_query']      = array();
			$query_args['author__in']     = array();
			$query_args['author__not_in'] = array();
			$query_args['post__in']       = array();

			/* we always want to exclude sticky posts */
			$query_args['post__not_in'] = get_option( 'sticky_posts', array() );

			foreach ( $args['rules'] as $rule ) {

				if ( empty( $rule['terms'] ) ) {
					continue;
				}

				if ( $rule['taxonomy'] === 'author' ) {
					/* operator can be IN or NOT IN */
					$arg                = 'author__' . strtolower( str_replace( ' ', '_', $rule['operator'] ) );
					$query_args[ $arg ] = array_values( array_merge( $query_args[ $arg ], $rule['terms'] ) );
				} elseif ( post_type_exists( $rule['taxonomy'] ) ) {
					$arg = $rule['operator'] === 'IN' ? 'post__in' : 'post__not_in';
					/* for posts type, we don't use tax_query, we use include and exclude */
					$query_args[ $arg ] = array_values( array_merge( $query_args[ $arg ], $rule['terms'] ) );
				} else {
					$query_args['tax_query'][] = $rule;
				}
			}
		}

		/* inherit was added just so it will work with attachments also  */
		$query_args['post_status'] = array( 'publish', 'inherit' );

		/* the human mind will read much easier indexes that start from 1 and not from 0 */
		if ( isset( $query_args['offset'] ) && $query_args['offset'] > 0 ) {
			$query_args['offset'] = (int) $query_args['offset'] - 1;
		}

		/* exclude current post when on singular */
		if ( ! empty( $queried_object->ID ) && ! empty( $query_args['exclude_current_post'] ) ) {
			$query_args['post__not_in'][] = $queried_object->ID;
		}

		return $query_args;
	}

	/**
	 * Return the Post List specific elements label
	 *
	 * @return string
	 */
	public static function elements_group_label() {
		return __( 'Article Components', 'thrive-cb' );
	}

	/**
	 * Register REST Routes for the Post List
	 */
	public static function rest_api_init() {
		require_once TVE_TCB_ROOT_PATH . 'inc/classes/post-list/class-tcb-post-list-rest.php';
	}

	/**
	 * Default args
	 *
	 * @return array
	 */
	public static function default_args() {
		return array(
			'query'              => str_replace( '"', '\'', json_encode( static::get_default_query() ) ),
			'type'               => 'grid',
			'columns-d'          => 3,
			'columns-t'          => 2,
			'columns-m'          => 1,
			'vertical-space-d'   => 30,
			'horizontal-space-d' => 30,
			'ct'                 => 'post_list--1',
			'ct-name'            => 'Default Post List',
			'tcb-elem-type'      => 'post_list',
			'pagination-type'    => 'none',
		);
	}

	/**
	 * Get the default query of the post list.
	 *
	 * @return array
	 */
	public static function get_default_query() {
		return array(
			'filter'               => 'custom',
			'related'              => array(),
			'post_type'            => 'post',
			'orderby'              => 'date',
			'order'                => 'DESC',
			'posts_per_page'       => '6',
			'offset'               => '1',
			'no_posts_text'        => 'There are no posts to display.',
			'exclude_current_post' => array( '1' ),
			'rules'                => array(),
		);
	}

	/**
	 * Check if a post list is rendering right now.
	 *
	 * @return bool
	 */
	public static function is_outside_post_list_render() {
		return empty( $GLOBALS[ TCB_DO_NOT_RENDER_POST_LIST ] );
	}

	/**
	 * Mark that we started rendering a post list. This is done in order to avoid situations like rendering another post list inside an existing post list.
	 */
	public static function enter_post_list_render() {
		$GLOBALS[ TCB_DO_NOT_RENDER_POST_LIST ] = true;
	}

	/**
	 * Mark that we finished rendering a post list.
	 */
	public static function exit_post_list_render() {
		$GLOBALS[ TCB_DO_NOT_RENDER_POST_LIST ] = false;
	}

	/**
	 * Post type archive link
	 *
	 * @param $id
	 *
	 * @return string
	 */
	public static function get_post_type_archive_link( $id = 0 ) {
		$link = get_post_type_archive_link( get_post_type( $id ) );
		if ( empty( $link ) ) {
			$link = '#';
		}

		return $link;
	}

	/**
	 * Author archive link
	 *
	 * @param $id
	 *
	 * @return string
	 */
	public static function get_author_posts_url( $id = 0 ) {
		$post = get_post( $id );
		$link = get_author_posts_url( $post->post_author );
		if ( empty( $link ) ) {
			$link = '#';
		}

		return rtrim( $link, '/' );
	}

	/**
	 * Date link
	 *
	 *
	 * @return string
	 */
	public static function get_day_link() {
		$link = get_day_link( get_the_date( 'Y' ), get_the_date( 'm' ), get_the_date( 'd' ) );
		if ( empty( $link ) ) {
			$link = '#';
		}

		return rtrim( $link, '/' );
	}

	/**
	 * Comments link.
	 *
	 * @param $id
	 *
	 * @return string
	 */
	public static function comments_link( $id = 0 ) {
		$post = get_post( $id );

		return get_permalink( $post ) . '#comments';
	}
}
