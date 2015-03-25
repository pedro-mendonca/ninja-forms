<?php if ( ! defined( 'ABSPATH' ) ) exit;
/**
 * Update form settings to the new storage system when the form is viewed for the first time.
 *
 * @since 2.9
 * @return void
 */
function nf_29_update_form_settings( $form_id ) {
	global $wpdb;

    $form = $wpdb->get_row( 'SELECT * FROM ' . NINJA_FORMS_TABLE_NAME . ' WHERE id = ' . $form_id, ARRAY_A );
    $settings = maybe_unserialize( $form['data'] );
    $settings['date_updated'] = $form['date_updated'];

	// Check to see if an object exists with our form id.
	$type = nf_get_object_type( $form_id );
	if ( $type && 'form' != $type ) {
		// We have an object with our form id.
        // Get a new form id.
        $f_id = nf_insert_object( 'form' );
	} else {
        // We do not have an object with out form id.
        // Maintain the for id.
        $f_id = nf_insert_object( 'form', $form['id'] );
    }

	foreach ( $settings as $meta_key => $value ) {
		nf_update_object_meta( $f_id, $meta_key, $value );
	}
	nf_update_object_meta( $f_id, 'status', '' );
}

/**
 * Check our option to see if we've updated all of our form settings.
 * If we haven't, then update the form currently being viewed.`
 * 
 * @since 2.9
 * @return void
 */
function nf_29_update_form_settings_check( $form_id ) {
	// Bail if we are in the admin
	if ( is_admin() )
		return false;

	// Bail if this form was created in 2.9 or higher.
	if ( 'form' == nf_get_object_type( $form_id ) )
		return false;

	// Get a list of forms that we've already converted.
	$completed_forms = get_option( 'nf_converted_forms', array() );

	if ( ! is_array( $completed_forms ) )
		$completed_forms = array();

	// Bail if we've already converted the db for this form.
	if ( in_array( $form_id, $completed_forms ) )
		return false;

	nf_29_update_form_settings( $form_id );

	$completed_forms[] = $form_id;
	update_option( 'nf_converted_forms', $completed_forms );
}

add_action( 'nf_before_display_loading', 'nf_29_update_form_settings_check' );