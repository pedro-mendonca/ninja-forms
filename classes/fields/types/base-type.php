<?php
/**
 * Base class for field types.
 * This is the parent class. it should be extended by specific field types
 *
 * @package     Ninja Forms
 * @subpackage  Classes/Fields
 * @copyright   Copyright (c) 2015, WPNINJAS
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
*/

abstract class NF_Field_Base_Type {
	
	function __construct() {
		// This space left intentionally blank
	}

	function render( $field_id ) {
		// This space left intentionally blank
	}

	function edit_field( $field_id ) {
		?>
		Hello World
		<?php
	}

}