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

$categories = get_the_category( $post->ID );

if ( $data['is_inner_frame'] && empty( $categories ) ) {
	echo __( 'No Categories', 'thrive-cb' );
} else {
	foreach ( $categories as $key => $category ) {
		$content = $category->name;
		if ( ! empty( $data['link'] ) ) {
			/* if we want the categories to link to their respective archives, wrap them in <a>s and add the archive url. */
			$content = TCB_Utils::wrap_content( $content, 'a', '', '', array( 'href' => get_category_link( $category ) ) );
		}
		echo ( $key === 0 ? '' : ', ' ) . $content;
	}
}
