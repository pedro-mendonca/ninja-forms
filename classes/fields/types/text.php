<?php
/**
 * Class for our text input field type.
 *
 * @package     Ninja Forms
 * @subpackage  Classes/Fields
 * @copyright   Copyright (c) 2015, WPNINJAS
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
*/

class NF_Field_Text extends NF_Field_Base_Type
{
	function __construct() {
		parent::__construct();
	}

}

return new NF_Field_Text();