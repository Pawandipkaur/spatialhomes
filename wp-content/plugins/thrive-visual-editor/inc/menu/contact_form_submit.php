<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package TCB2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}
?>
<div id="tve-contact_form_submit-component" class="tve-component" data-view="ContactFormSubmit">
	<div class="dropdown-header" data-prop="docked">
		<?php echo __( 'Main Options', 'thrive-cb' ); ?>
		<i></i>
	</div>
	<div class="dropdown-content">
		<div class="tve-control tcb-icon-side-wrapper hide-states" data-key="icon_side" data-icon="true" data-view="ButtonGroup"></div>
		<div class="tcb-text-center mt-10 hide-states" data-icon="true">
			<span class="click tcb-text-uppercase clear-format" data-fn="remove_icon">
				<?php tcb_icon( 'close2' ) ?>&nbsp;<?php echo __( 'Remove Input Icon', 'thrive-cb' ) ?>
			</span>
		</div>
		<div class="tve-control gl-st-button-toggle-2" data-icon="false"  data-view="ModalPicker"></div>
		<hr class="hide-states" data-icon="true">
		<hr data-icon="false">
		<div class="tve-control gl-st-button-toggle-1" data-view="MasterColor"></div>
		<hr>
		<div class="tve-control gl-st-button-toggle-2" data-view="ButtonWidth"></div>
		<hr>
		<div class="tve-control gl-st-button-toggle-2" data-view="ButtonAlign"></div>
		<hr>
		<div class="tve-control tcb-hidden" data-key="style" data-initializer="button_style_control" style="display: none;"></div> <?php //TODO: change this ?>
	</div>
</div>
