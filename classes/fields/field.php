<?php
/**
 * Field
 * 
 * Single field object.
 * This object lets us make calls like: Ninja_Forms()->ield( 33 )->methods()
 *
 * @package     Ninja Forms
 * @subpackage  Classes/Fields
 * @copyright   Copyright (c) 2015, WPNINJAS
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
*/

class NF_Field
{

	/**
	 * var $settings
	 */
	var $settings = array();

	function __construct( $field_id = '' ) {
		if ( empty ( $field_id ) )
			return false;
		$this->id = $field_id;
		$this->type = 'text';
		$this->refresh_settings();
	}

	function refresh_settings() {
		global $wpdb;
		$settings = array();
		// Grab a new set of field settings.
		$results = $wpdb->get_results( 'SELECT meta_key, meta_value FROM ' . NF_FIELD_META_TABLE_NAME . ' WHERE field_id = ' . $this->id, ARRAY_A );	
		foreach ( $results as $meta ) {
			$settings[ $meta[ 'meta_key' ] ] = $meta['meta_value'];
		}
		$this->settings = $settings;
	}

	function get_setting( $key ) {
		return $this->settings[ $key ];
	}

	function get_settings() {
		return $this->settings;
	}

	function update_setting( $key, $value ) {
		$this->settings[ $key ] = $value;
		// Update the settings in the database
	}

	function render() {
		Ninja_Forms()->field_types[ $this->type ]->render( $this->id );
	}

	function settings_template() {
		Ninja_Forms()->field_types[ $this->type ]->settings_template( $this->id );
	}
}