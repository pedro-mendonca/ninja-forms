<?php

function nf_register_tab_builder(){
	if(isset($_REQUEST['form_id'])){
		$form_id = absint( $_REQUEST['form_id'] );
	}else{
		$form_id = '';
	}

	$args = array(
		'name' => __( 'Build Your Form', 'ninja-forms' ),
		'page' => 'ninja-forms',
		'display_function' => 'nf_tab_builder',
		'disable_no_form_id' => true,
		'show_save' => false,
		'tab_reload' => false,
	);
	ninja_forms_register_tab( 'builder', $args );
}

add_action( 'admin_init', 'nf_register_tab_builder' );

function nf_tab_builder() {
	?>

	<style>
		.nf-form-builder {
			padding: 20px 0;
		}
		.nf-field {
			margin-bottom: 10px;
			opacity: 0.7;
		}
		.nf-field:hover {
			opacity: 1;
		}
		.nf-field.active {
			opacity: 1;
		}
		.nf-field-header {
			background: #fff;
			border: 1px solid #ccc;
			cursor: move;
			margin: 0;
			padding: 10px;
		}
		.nf-field-header .dashicons {
			float: right;
		}
		.nf-field-body {
			background: #fff;
			border: 1px solid #ccc;
			border-top: 0;
			/*margin-top: -5px;*/
			overflow: hidden;
			padding-left: 200px;
		}
		.nf-field-body a {
			text-decoration: none;
		}
		.nf-field-body:after {
			clear: both;
			content: "";
			display: block;
		}
		.nf-field.active .nf-field-header {

		}
		.nf-field-sidebar {
			float: left;
			width: 200px;
			margin-left: -200px;
		}
		.nf-field-sidebar ul {
			margin: 0;
		}
		.nf-field-sidebar li {
			background: #f1f1f1;
			border-right: 1px solid #ccc;
			margin: 0;
			padding: 8px 20px;
		}
		.nf-field-sidebar li:first-child {
			background: #ccc;
			padding: 8px;
		}
		.nf-field-sidebar li:first-child select {
			width: 100%;
		}
		.nf-field-sidebar li:first-child label {
			display: block;
			padding: 0 8px 8px;
		}
		.nf-field-sidebar li.active {
			background: #fff;
			border: 1px solid #ccc;
			border-right: 0;
			border-left: 0;
		}
		.nf-field-content {
			float: left;
			width: 100%;
		}
		.nf-field-content .inside {
			padding: 5px 20px;
		}

		.nf-form-builder label {
			display: block;
			/*vertical-align: middle;*/
		}
		.nf-form-builder .select label {
			display: block;
			padding-top: 8px;
		}
		.nf-form-builder .select .nf-desc {
			display: block;
		}
		.nf-form-builder .select select {
			margin-top: -3px;
		}
		dl.nf-field-settings {
			display: block;
			margin: 0 0 20px !important;
			overflow: hidden;
			padding-left: 18%;
		}
		.nf-field-settings dt {
			float: left;
			clear: both;
			width: 21.951%;
			padding: 0;
			margin-left: -21.951%;
			text-decoration: none;
		}
		.nf-field-settings dd {
			float: left;
			margin: 0;
			width: 100%;
		}
		.nf-desc {
			font-style: italic;
		}

		.nf-form-builder-bar {
			background: #333;
/*			border: 1px solid #fff;*/
			padding: 10px;
		}
		.nf-field-selector a {
			color: #424242;
			text-decoration: none;
		}
		.nf-form-builder-bar .nf-item {
			margin-left: 5px;
		}
		.nf-form-builder-bar .dashicons {
			padding: 2px 8px 4px 0;
		}
		.nf-field-selector a.dashicons-arrow-down {
			border-left: 1px solid #ccc;
			padding: 2px 0 4px 5px;
			margin-left: 8px;
		}
	</style>
	<div class="nf-form-builder-bar">
		<a href="#" class="button-primary nf-item">Save</a>
		<div class="button-secondary nf-field-selector nf-item" style="float: right;"><a href="#">Add New Field</a><a class="dashicons dashicons-arrow-down"></a></div>
		<a href="#" class="button-secondary nf-item" style="float: right;"><span class="dashicons dashicons-sort"></span>Expand Fields</a>

	</div>
	<div class="nf-form-builder">
		<div class="nf-field">
			<div class="nf-field-header">
				Last Name
				<span class="dashicons dashicons-arrow-down"></span>
			</div>
		</div>
		<div class="nf-field active">
			<div class="nf-field-header">
				Last Name
				<span class="dashicons dashicons-arrow-up"></span>
			</div>
			<div class="nf-field-body">
				<div class="nf-field-sidebar">

				</div>
				<div class="nf-field-content">
					<div class="inside">
						
					</div>
				</div>
			</div>
		</div>
	</div>

	<script type="text/html" id="tmpl-nf-field-header">
		<div class="nf-field-header">
			Middle Name
			<span class="dashicons dashicons-arrow-down"></span>
		</div>
	</script>

	<script type="text/html" id="tmpl-nf-field-sidebar">
		<ul class="nf-field-tabs">
			<li>
				<label>Select Field Type</label>
				<select>
					<option value="field-type">Single line Input</option>
					<option value="field-type">Multi-Line Textarea</option>
					<option value="field-type">Checkbox</option>
					<option value="field-type">Checkbox List</option>
					<option value="field-type">Dropdown List</option>
					<option value="field-type">Radio List</option>
				</select>
			</li>
			<?php
			$base_url = admin_url( 'admin.php?page=ninja-forms&tab=builder&form_id=1' );
			$basic_url = add_query_arg( array( 'section' => 'basic' ), $base_url );
			$res_url = add_query_arg( array( 'section' => 'restrictions' ), $base_url );
			$calc_url = add_query_arg( array( 'section' => 'calculations' ), $base_url );
			$adv_url = add_query_arg( array( 'section' => 'advanced' ), $base_url );
			$con_url = add_query_arg( array( 'section' => 'conditional_logic' ), $base_url );
			$style_url = add_query_arg( array( 'section' => 'style' ), $base_url );
			?>
			<li class="nf-field-tab <# if ( 'basic' == section ) { #> active <# } #>"><a href="<?php echo $basic_url; ?>" data-nf-backbone >Basic</a></li>
			<li class="nf-field-tab <# if ( 'restrictions' == section ) { #> active <# } #>"><a href="<?php echo $res_url; ?>" data-nf-backbone >Restrictions</a></li>
			<li class="nf-field-tab <# if ( 'calculations' == section ) { #> active <# } #>"><a href="<?php echo $calc_url; ?>" data-nf-backbone >Calculations</a></li>
			<li class="nf-field-tab <# if ( 'advanced' == section ) { #> active <# } #>"><a href="<?php echo $adv_url; ?>" data-nf-backbone >Advanced</a></li>
			<li class="nf-field-tab <# if ( 'conditional_logic' == section ) { #> active <# } #>"><a href="<?php echo $con_url; ?>" data-nf-backbone >Conditionals</a></li>
			<li class="nf-field-tab <# if ( 'style' == section ) { #> active <# } #>"><a href="<?php echo $style_url; ?>" data-nf-backbone >Styles</a></li>
		</ul>
	</script>

	<script type="text/html" id="tmpl-nf-field-checkbox">
		<dl class="nf-field-settings checkbox">
			<dt>
				<label for="nf-<#= setting.id #>"><#= setting.label #></label>
			</dt>
			<dd>
				<input type="checkbox" id="nf-<#= setting.id #>" value="1">
				<span class="nf-desc"><#= setting.desc #></span>
			</dd>
		</dl>
	</script>

	<script type="text/html" id="tmpl-nf-field-text">
		<dl class="nf-field-settings text">
			<dt>
				<label for="nf-<#= setting.id #>"><#= setting.label #></label>
			</dt>
			<dd>
				<input type="text" id="nf-<#= setting.id #>" value="" placeholder = "<#= setting.placeholder #>">
				<span class="nf-desc"><#= setting.desc #></span>
			</dd>
		</dl>
	</script>

	<script type="text/html" id="tmpl-nf-field-select">
		<dl class="nf-field-settings select">
			<dt>
				<label for="nf-<#= setting.id #>"><#= setting.label #></label>
			</dt>
			<dd>
				<select id="nf-<#= setting.id #>">
					<option value="">Test</option>
				</select>
				<span class="nf-desc"><#= setting.desc #></span>
			</dd>
		</dl>
	</script>

	<script type="text/html" id="tmpl-nf-field-select-detail">
		<dl class="nf-field-settings select-detail">
			<dt>
				<label for="nf-<#= setting.id #>"><#= setting.label #></label>
			</dt>
			<dd>
				<select id="nf-<#= setting.id #>">
					<option value="">Test</option>
				</select>
				<input type="text" id="nf-<#= setting.id #>">
				<span class="nf-desc"><#= setting.desc #></span>
			</dd>
		</dl>
	</script>
	<?
	Ninja_Forms()->field( 4 )->settings_template();
}
