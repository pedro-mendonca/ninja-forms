<?php if ( ! defined( 'ABSPATH' ) ) exit;

class NF_Conversion_Reset
{
    public function __construct()
    {
        add_action('admin_menu', array( $this, 'register_submenu'), 9001);
    }
    public function register_submenu()
    {
        add_submenu_page(
            NULL,                           // Parent Slug
            'Ninja Forms Conversion Reset', // Page Title
            'Ninja Forms Conversion Reset', // Menu Title
            'manage_options',               // Capability
            'ninja-forms-conversion-reset', // Menu Slug
            array( $this, 'process')        // Display Function
        );
    }
    public function process()
    {
        global $wpdb;

        // Get all of our forms from the old table.
        $forms = $wpdb->get_results( 'SELECT id FROM ' . $wpdb->prefix . 'ninja_forms', ARRAY_A );

        // Loop through our form ids and check to see if we have a form in the new database system with that ID
        foreach ( $forms as $form ) {
            $type = $wpdb->get_row( 'SELECT type FROM ' . $wpdb->prefix . 'nf_objects WHERE id = ' . $form['id'] );
            if ( $type && 'form' == $type->type ) { // We have a form in the new database system with this ID. Let's remove it.
                $wpdb->query( 'DELETE FROM ' . $wpdb->prefix .'nf_objects WHERE id = ' . $form['id'] );
                $wpdb->query( 'DELETE FROM ' . $wpdb->prefix .'nf_objectmeta WHERE object_id = ' . $form['id'] );
            }
        }

        // Remove our "converted" flags from the options table
        delete_option( 'nf_convert_forms_complete' );
        delete_option( 'nf_converted_forms' );

    }

} // End Ninja_Forms_View_Admin Class

// Self Instantiate
new NF_Conversion_Reset();