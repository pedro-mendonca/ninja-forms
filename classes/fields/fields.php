<?php
/**
 * Main Fields Class
 *
 * Includes all of our field types
 * 
 * @package     Ninja Forms
 * @subpackage  Classes/Fields
 * @copyright   Copyright (c) 2014, WPNINJAS
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
*/

class NF_Fields
{

	function __construct() {
		// Include our base field type
		require_once( NF_PLUGIN_DIR . 'classes/fields/types/base-type.php');

		// Register our field types
		Ninja_Forms()->field_types['text'] = require_once( NF_PLUGIN_DIR . 'classes/fields/types/text.php' );

		Ninja_Forms()->field_types = apply_filters( 'nf_field_types', Ninja_Forms()->field_types );		
	}

}


