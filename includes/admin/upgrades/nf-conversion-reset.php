<?php if ( ! defined( 'ABSPATH' ) ) exit;

class NF_Conversion_Reset
{
    public $forms;

    public $errors = array();

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
            array( $this, 'process')        // Function
        );
    }
    public function process()
    {
        global $wpdb;

        echo "<h2>Ninja Forms Conversion Reset</h2>";

        // Check if old table exists
        if( 0 == $wpdb->query( "SHOW TABLES LIKE '" . NINJA_FORMS_TABLE_NAME . "'" ) ){
            $this->errors[] = "No forms to be converted (table does not exist).";
        } else {

            // Get all of our forms from the old table.
            $this->forms = $wpdb->get_results( 'SELECT id FROM ' . $wpdb->prefix . 'ninja_forms ORDER BY id asc', ARRAY_A );

            // Loop through our form ids and check to see if we have a form in the new database system with that ID
            foreach ( $this->forms as $form ) {

                $type = $wpdb->get_row( 'SELECT type FROM ' . $wpdb->prefix . 'nf_objects WHERE id = ' . $form['id'] );

                // We have a form in the new database system with this ID.
                if ( $type && 'form' == $type->type ) {

                    $wpdb->query( 'INSERT INTO ' . $wpdb->prefix . 'nf_objects ( type ) VALUES ( "form" )' );
                    $wpdb->query( 'UPDATE ' . $wpdb->prefix . 'nf_objectmeta SET object_id = ' . $wpdb->insert_id . ' WHERE object_id = ' . $form['id'] );

                    $wpdb->query( 'DELETE FROM ' . $wpdb->prefix .'nf_objects WHERE id = ' . $form['id'] );
                    $wpdb->query( 'DELETE FROM ' . $wpdb->prefix .'nf_objectmeta WHERE object_id = ' . $form['id'] );
                }
            }

            // Remove our "converted" flags from the options table
            delete_option( 'nf_convert_forms_complete' );
            delete_option( 'nf_converted_forms' );
        }


        // Output Errors or Success
        if( $this->errors ) {
            foreach( $this->errors as $error) {
                ?>
                <div class="error">
                    <p><?php echo "$error"; ?></p>
                </div>
                <?php
            }
        } else {
            printf(
                '<div class="updated"><p>' . __( 'Ninja Forms needs to upgrade your form settings, click %shere%s to start the upgrade.', 'ninja-forms' ) . '</p></div>',
                '<a href="' . admin_url( 'index.php?page=nf-processing&action=convert_forms&title=Updating+Form+Database' ) . '">', '</a>'
            );
        }

    }


} // End Ninja_Forms_View_Admin Class

// Self Instantiate
new NF_Conversion_Reset();