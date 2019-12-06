<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

$queried_object = get_queried_object();

/* when the title is on the same page with its post, we don't display the link */
$no_link = ! empty( $queried_object ) && ! empty( $queried_object->ID ) && ( $queried_object->ID === get_the_ID() );

?>

<?php if ( $no_link ) : ?>
	<?php the_title(); ?>
<?php else : ?>
	<a href="<?php the_permalink(); ?>" title="<?php the_title(); ?>"><?php the_title(); ?></a>
<?php endif; ?>
