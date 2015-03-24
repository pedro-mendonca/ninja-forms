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

                $row = $wpdb->get_row( 'SELECT type FROM ' . $wpdb->prefix . 'nf_objects WHERE id = ' . $form['id'] );

                // We have a form in the new database system with this ID.
                if ( $row && 'form' == $row->type ) {

                    $starting_id = $this->get_starting_id();

                    // Get new form ID to propagate
                    $wpdb->query( "INSERT INTO ' . $wpdb->prefix . 'nf_objects ( id, type ) VALUES ( " . $starting_id . ", 'form' )" );

                    // Update object meta with new form ID
                    $wpdb->query( 'UPDATE ' . $wpdb->prefix . 'nf_objectmeta SET object_id = ' . $wpdb->insert_id . ' WHERE object_id = ' . $form['id'] );

                    // Update form fields with new form ID
                    $wpdb->query( 'UPDATE ' . $wpdb->prefix . 'ninja_forms_fields SET form_id = ' . $wpdb->insert_id . ' WHERE form_id = ' . $form['id'] );

                    // Get posts (submissions) for form ID
                    $row->posts = $wpdb->get_results( "SELECT post_id FROM " . $wpdb->prefix . "postmeta WHERE meta_key = '_form_id' AND meta_value = '" . $form['id'] . "'", ARRAY_A );

                    // Filter posts (submissions) and verify with field comparison
                    foreach( $row->posts as $post) {

                        // Get the first field meta_key from postmeta matching post ID
                        $field_meta_keys = $wpdb->get_results( "SELECT meta_key FROM " . $wpdb->prefix . "postmeta WHERE meta_key LIKE '_field_%' AND post_id = '" . $post['post_id'] . "' LIMIT 1", ARRAY_A);

                        // Get the field ID integer from the field meta_key string
                        $field_id = explode( "_", $field_meta_keys[0]['meta_key'])[2];

                        // Check if the field belongs to the form
                        $post['is_field_of_form'] = $wpdb->query( "SELECT * FROM " . $wpdb->prefix . "ninja_forms_fields WHERE id = '" . $field_id . "' AND form_id = '" . $form['id'] . "'" );

                        // If the field belongs to the form
                        if( $post['is_field_of_form'] ) {

                            // Update post (submission) form ID
                            $wpdb->query( "UPDATE " . $wpdb->prefix . "postmeta SET meta_value = '" . $wpdb->insert_id . "' WHERE meta_key = '_form_id' AND meta_value = '" . $form['id'] . "'" );
                        }
                    }

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