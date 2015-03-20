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
			border: 1px solid #f1f1f1;			
			background: #f1f1f1;
			border-right: 1px solid #ccc;
			margin: 0;
			padding: 8px 20px;
			border-left: 0;
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
		.nf-field-sidebar li a {
			display: block;
			box-shadow: 0 0 0 0; 
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
		.nf-field-header input[type="text"], 
		.nf-field-header textarea {
			width: 400px;
		}
		.nf-field-header textarea {
			height: 100px;
		}
	</style>
	<div class="nf-form-builder-bar">
		<a href="#" class="button-primary nf-item">Save</a>
		<div class="button-secondary nf-field-selector nf-item" style="float: right;"><a href="#">Add New Field</a><a class="dashicons dashicons-arrow-down"></a></div>
		<a href="#" class="button-secondary nf-item" style="float: right;"><span class="dashicons dashicons-sort"></span>Expand Fields</a>

	</div>
	<div class="nf-form-builder">
		


<!-- 	<div class="nf-field active">
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
		</div> -->
		
	</div>

	<script type="text/html" id="tmpl-nf-field">
		<div class="nf-field">
			<div class="nf-field-header"></div>
		</div>
	</script>

	<script type="text/html" id="tmpl-nf-field-header">
		<#
		switch ( field.get( 'type' ) ) {
			case 'text':
				#>
				<input type="text" placeholder="<#= field.get( 'label' ) #>" disabled>
				<#
				break;
			case 'checkbox':
				#>
				<input type="checkbox" checked="checked" disabled> <#= field.get( 'label' ) #>
				<#
				break;
			case 'textarea':
				#>
				<textarea disabled><#= field.get( 'label' ) #></textarea>
				<#
				break;
			case 'radio':
				#>
				<#= field.get( 'label' ) #>:
				<ul>
					<li>
						<input type="radio" disabled> Option 1
					</li>
					<li>
						<input type="radio" checked="checked" disabled> Option 2
					</li>
					<li>
						<input type="radio" disabled> Option 3
					</li>
				</ul>
				<#
				break;
			case 'submit':
				#>
				<input type="submit" disabled value="<#= field.get( 'label' ) #>">
				<#
		}
		#>
		
		<span class="dashicons dashicons-arrow-down toggle"></span>
	</script>

	<script type="text/html" id="tmpl-nf-field-body">
		<div class="nf-field-body"></div>
	</script>

	<script type="text/html" id="tmpl-nf-field-sidebar">
		<div class="nf-field-sidebar"></div>
	</script>

	<script type="text/html" id="tmpl-nf-field-content">
		<div class="nf-field-content">
			<div class="inside"></div>
		</div>
	</script>

	<script type="text/html" id="tmpl-nf-field-tabs">
		
			<ul class="nf-field-tabs">
				<li>
					<label>Select Field Type</label>
					<select>
						<#
						_.each( fieldTypes, function( type ) {
							#>
							<option value="<#= type.get( 'id' ) #>" <# if ( type.get( 'id' ) == field.get( 'type' ) ) { #> selected="selected" <# } #>><#= type.get( 'name' ) #></option>
							<#
						} );
						#>
					</select>
				</li>
				<#
				_.each( sidebars, function( nicename, slug ) {
					#>
					<li class="nf-field-tab <# if ( slug == section ) { #> active <# } #>"><a href="#" data-section="<#= slug #>" ><#= nicename #></a></li>
					<#
				} );

				#>
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
