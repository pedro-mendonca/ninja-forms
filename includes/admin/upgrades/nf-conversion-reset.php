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

    public function get_starting_id()
    {
        global $wpdb;

        // Get the last ID from the ninja_forms (deprecated) table
        $last_old_row = $wpdb->get_results( "SELECT id FROM " . $wpdb->prefix . "ninja_forms ORDER BY id DESC LIMIT 1", ARRAY_A )[0];
        $last_old_id = $last_old_row['id'];

        // Get the last ID from the nf_objects table
        $last_new_row = $wpdb->get_results( "SELECT id FROM " . $wpdb->prefix . "nf_objects ORDER BY id DESC LIMIT 1", ARRAY_A )[0];
        $last_new_id = $last_new_row['id'];

        // Compare the last row IDs to determine which is higher
        $larger_id = max( $last_old_id, $last_new_id );

        // Return an ID that should not conflict with either table
        return $larger_id + 1;
    }


} // End Ninja_Forms_View_Admin Class

// Self Instantiate
new NF_Conversion_Reset();