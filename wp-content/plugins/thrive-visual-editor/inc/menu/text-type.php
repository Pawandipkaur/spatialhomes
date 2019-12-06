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
<div id="tve-text-type-component" class="tve-component" data-view="TextType">
	<div class="dropdown-header" data-prop="docked">
		<?php echo __( 'Text Type', 'thrive-cb' ); ?>
		<i></i>
	</div>
	<div class="dropdown-content">
		<div class="tve-control mb-10" data-view="TextTypeDropdown"></div>
	</div>
</div>
