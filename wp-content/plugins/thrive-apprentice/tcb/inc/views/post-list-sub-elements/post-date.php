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

<?php if ( isset( $data['link'] ) && 'monthly' === $data['link'] ) : ?>
	<a href="<?php echo get_month_link( get_the_date( 'Y' ), get_the_date( 'm' ) ); ?>"><?php the_time( $data['date_format'] ); ?></a>
<?php else : ?>
	<?php the_time( $data['date_format'] ); ?>
<?php endif; ?>
