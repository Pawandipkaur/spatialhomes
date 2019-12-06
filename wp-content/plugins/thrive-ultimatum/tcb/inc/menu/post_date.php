<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
} ?>

<div id="tve-post_date-component" class="tve-component" data-view="PostDate">
	<div class="dropdown-header" data-prop="docked">
		<?php echo __( 'Post Date Options', 'thrive-cb' ); ?>
		<i></i>
	</div>
	<div class="dropdown-content">
		<div class="tve-control" data-key="Type" data-view="Type"></div>
		<hr>
		<div class="tve-control" data-key="DateInput" data-view="DateInput"></div>
		<hr>
		<a href="https://wordpress.org/support/article/formatting-date-and-time/" class="info-text" target="_blank">
			<?php echo tcb_icon( 'info' ); ?>
			<?php echo __( 'Click here to read the Documentation on date and time formatting.', 'thrive-cb' ); ?>
		</a>
	</div>
</div>
