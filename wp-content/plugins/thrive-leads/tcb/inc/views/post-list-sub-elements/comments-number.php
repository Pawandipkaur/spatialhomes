<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}
?>

<?php if ( empty( $data['url'] ) ) : ?>
	<?php echo get_comments_number(); ?>
<?php elseif ( $data['url'] == 1 ) : ?>
	<a href="<?php echo get_permalink( $data['post_url'] ); ?>" title="<?php echo __( 'Comments Number', 'thrive-cb' ); ?>"><?php echo get_comments_number(); ?></a>
<?php elseif ( $data['url'] == 2 ) : ?>
	<a href="<?php echo get_permalink( $data['post_url'] ); ?>#comments" title="<?php echo __( 'Comments Number', 'thrive-cb' ); ?>"><?php echo get_comments_number(); ?></a>
<?php endif; ?>
