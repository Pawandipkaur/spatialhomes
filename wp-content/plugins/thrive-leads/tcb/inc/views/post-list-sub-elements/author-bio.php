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

$author             = $post->post_author;
$author_description = get_the_author_meta( 'description', $author );

echo $data['is_inner_frame'] && empty( $author_description ) ? __( 'No Author Description', 'thrive-cb' ) : $author_description;
