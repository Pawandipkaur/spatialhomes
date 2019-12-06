<div class="cb-template-item click<#=selected ? ' active' : ''#><#=item.fav ? ' cb-fav' : ''#>" data-id="<#= item.id #>" data-fn="dom_select">
	<div class="cb-template-wrapper">
		<div class="cb-template-thumbnail">
			<div class="cb-thumb-overlay click" data-id="<#= item.id #>" data-fn="dom_preview"></div>
			<img src="<#= item.thumb #>"/>
			<a href="javascript:void(0);" class="click cb-preview" data-id="<#= item.id #>" data-fn="dom_preview"><span><?php echo __( 'Preview Block', 'thrive-cb' ) ?></span></a>
		</div>
		<div class="cb-actions">
			<a href="javascript:void(0);" class="click" data-id="<#= item.id #>" data-fn="dom_insert_into_content"><span id="cb-preview-light"><?php tcb_icon( 'arrow-alt-to-bottom-light' ); ?></span><span id="cb-preview-solid"><?php tcb_icon( 'arrow-alt-to-bottom-solid' ); ?></span><span><?php echo __( 'Insert Into Content', 'thrive-cb' ) ?></span></a>
			<div>
				<div class="cb-separator"></div>
				<a href="javascript:void(0);" class="click cb-favorite" data-id="<#= item.id #>" data-fn="dom_fav">
					<span data-tooltip="<#= favorite_data[item.fav].tooltip #>"><#= TVE.icon(favorite_data[item.fav].icon) #></span>
				</a>
			</div>
		</div>
		<div class="selected"></div>
	</div>
</div>
