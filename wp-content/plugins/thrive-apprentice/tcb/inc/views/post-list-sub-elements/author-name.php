<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

global $post;

$author = $post->post_author;

if ( $data['is_inner_frame'] && empty( $author ) ) {
	echo __( 'No Author Name', 'thrive-cb' );
} else {
	the_author_meta( 'display_name', $author );
}
