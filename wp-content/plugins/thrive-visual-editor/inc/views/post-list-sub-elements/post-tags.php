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

$tags = get_the_tags( $post->ID );

if ( $data['is_inner_frame'] && empty( $tags ) ) {
	echo __( 'No Tags', 'thrive-cb' );
} elseif ( ! empty( $tags ) ) {
	foreach ( $tags as $key => $tag ) {
		$content = $tag->name;
		if ( ! empty( $data['link'] ) ) {
			/* if we want the tags to link to their respective archives, wrap them in <a>s and add the archive url. */
			$content = TCB_Utils::wrap_content( $content, 'a', '', '', array( 'href' => get_tag_link( $tag ) ) );
		}
		echo ( $key === 0 ? '' : ', ' ) . $content;
	}
}
