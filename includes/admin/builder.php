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
	
}