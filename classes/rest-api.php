<?php

/**
 * This class handles RESTful API enpoint processing for building forms.
 *
 * @package     Ninja Forms
 * @subpackage  Classes/Admin
 * @copyright   Copyright (c) 2014, WPNINJAS
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
*/

class NF_Admin_Rest_API
{
 /**
     * Property: method
     * The HTTP method this request was made in, either GET, POST, PUT or DELETE
     */
    protected $method = '';

    /**
     * Property: endpoint
     * The Model requested in the URI. eg: /files
     */
    protected $endpoint = '';

    /**
     * Property: file
     * Stores the input of the PUT request
     */
     protected $file = null;

    /**
     * Constructor: __construct
     * Allow for CORS, assemble and pre-process the data
     */
    public function __construct() {
         // Bail if we aren't in the admin.
        if ( ! is_admin() )
            return false;

        global $pagenow;

        add_action( 'wp_ajax_nf_rest', array( $this, 'check_rest_api' ) );
    }

    public function check_rest_api() {
        $capabilities = 'manage_options';
        $capabilities = apply_filters( 'ninja_forms_admin_menu_capabilities', $capabilities );
        if ( current_user_can( $capabilities ) ) {
            // Requests from the same server don't have a HTTP_ORIGIN header
            if ( !array_key_exists('HTTP_ORIGIN', $_SERVER ) ) {
                $_SERVER['HTTP_ORIGIN'] = $_SERVER['SERVER_NAME'];
            }
            $request = $_REQUEST['nf_rest'];
            try {
                $this->initiate_api_request( $request, $_SERVER['HTTP_ORIGIN'] );
                echo $this->processAPI();
            } catch ( Exception $e ) {
                echo json_encode( Array ( 'error' => $e->getMessage() ) );
            }
            die();  
        }
    }

    public function initiate_api_request($request) {
        header("Content-Type: application/json");
        $this->args = explode( '/', rtrim($request, '/' ) );
        $this->endpoint = array_shift( $this->args );
        if ( array_key_exists( 0, $this->args ) && !is_numeric( $this->args[0] ) ) {
            $this->verb = array_shift($this->args);
        }
        $this->method = $_SERVER['REQUEST_METHOD'];
        if ($this->method == 'POST' && array_key_exists('HTTP_X_HTTP_METHOD', $_SERVER)) {
            if ($_SERVER['HTTP_X_HTTP_METHOD'] == 'DELETE') {
                $this->method = 'DELETE';
            } else if ($_SERVER['HTTP_X_HTTP_METHOD'] == 'PUT') {
                $this->method = 'PUT';
            } else {
                throw new Exception("Unexpected Header");
            }
        }
        switch($this->method) {
        case 'DELETE':
        case 'POST':
        	$this->file = file_get_contents( "php://input" );
            $this->request = $this->_cleanInputs( $_POST );
            break;
        case 'GET':
            $this->request = $this->_cleanInputs( $_GET );
            break;
        case 'PUT':
            $this->request = $this->_cleanInputs( $_GET );
            $this->file = file_get_contents ("php://input" );
            break;
        default:
            $this->_response( 'Invalid Method', 405 );
            break;
        }
    }

    public function processAPI() {
        if ( ( int )method_exists( $this, $this->endpoint ) > 0) {
            return $this->_response( $this->{ $this->endpoint } ($this->args ) );
        }
        return $this->_response('', 400);
    }

    private function _response($data, $status = 200) {
        header("HTTP/1.1 " . $status . " " . $this->_requestStatus($status));
        return json_encode($data);
    }

    private function _cleanInputs($data) {
        $clean_input = array();
        if (is_array($data)) {
            foreach ($data as $k => $v) {
                $clean_input[$k] = $this->_cleanInputs($v);
            }
        } else {
            $clean_input = trim(strip_tags($data));
        }
        return $clean_input;
    }

    private function _requestStatus($code) {
        $status = array( 
            100 => 'Continue',   
            101 => 'Switching Protocols',   
            200 => 'OK', 
            201 => 'Created',   
            202 => 'Accepted',   
            203 => 'Non-Authoritative Information',   
            204 => 'No Content',   
            205 => 'Reset Content',   
            206 => 'Partial Content',   
            300 => 'Multiple Choices',   
            301 => 'Moved Permanently',   
            302 => 'Found',   
            303 => 'See Other',   
            304 => 'Not Modified',   
            305 => 'Use Proxy',   
            306 => '(Unused)',   
            307 => 'Temporary Redirect',   
            400 => 'Bad Request',   
            401 => 'Unauthorized',   
            402 => 'Payment Required',   
            403 => 'Forbidden',   
            404 => 'Not Found',   
            405 => 'Method Not Allowed',   
            406 => 'Not Acceptable',   
            407 => 'Proxy Authentication Required',   
            408 => 'Request Timeout',   
            409 => 'Conflict',   
            410 => 'Gone',   
            411 => 'Length Required',   
            412 => 'Precondition Failed',   
            413 => 'Request Entity Too Large',   
            414 => 'Request-URI Too Long',   
            415 => 'Unsupported Media Type',   
            416 => 'Requested Range Not Satisfiable',   
            417 => 'Expectation Failed',   
            500 => 'Internal Server Error',   
            501 => 'Not Implemented',   
            502 => 'Bad Gateway',   
            503 => 'Service Unavailable',   
            504 => 'Gateway Timeout',   
            505 => 'HTTP Version Not Supported'); 
        return ($status[$code])?$status[$code]:$status[500]; 
    }

    protected function rest_api() {
        switch( $this->method ) {
            case 'GET':
                if ( 'fields' == $this->request['collection'] ) {
                    // $object_id = $this->request['object_id'];
                    $data = array(
                        array( 
                            'type'          => 'text',
                            'id'            => 10,
                            'label'         => 'First Name',
                        ),
                        array( 
                            'type'          => 'text',
                            'id'            => 12,
                            'label'         => 'Last Name',
                        ),
                        array(
                            'type'          => 'textarea',
                            'id'            => 13,
                            'label'         => __( 'Your Message', 'ninja-forms' ),
                        ),
                        array(
                            'type'          => 'radio',
                            'id'            => 14,
                            'label'         => __( 'Pick One', 'ninja-forms' ),
                        ),
                        array( 
                            'type'          => 'checkbox',
                            'id'            => 15,
                            'label'         => 'Do You Agree?',
                        ),
                        array( 
                            'type'          => 'submit',
                            'id'            => 16,
                            'label'         => 'Submit',
                        ),
                    );
                } else if ( 'field_types' == $this->request['collection'] ) {
                    $data = array(
                        array(
                            'id'                            => 'text',
                            'name'                          => __( 'Text', 'ninja-forms' ),
                            'data'                          => array(
                                'sidebars'                  => array(
                                    'basic'                 => __( 'Basic', 'ninja-forms' ),
                                    'calc'                  => __( 'Calculations', 'ninja-forms' ),
                                    'advanced'              => __( 'Advanced', 'ninja-forms' ),
                                    'cl'                    => __( 'Conditional Logic', 'ninja-forms' ),
                                    // 'styles'                => __( 'Styles', 'ninja-forms' ),
                                ),
                                'settings'                  => array(
                                    'basic'                 => array(
                                        'label'             => array(
                                            'type'          => 'text',
                                            'label'         => __( 'Label', 'ninja-forms' ),
                                            'placeholder'   => __( 'My Field', 'ninja-forms' ),
                                            'desc'          => __( 'The text that identifies the field for your user.', 'ninja-forms' ),
                                        ),
                                       'label_pos'          => array(
                                            'type'          => 'select',
                                            'label'         => __( 'Label Position', 'ninja-forms' ),
                                            'desc'          => __( 'This is where the label is displayed in relation to the element.', 'ninja-forms' ),
                                        ),
                                       'placeholder'        => array(
                                            'type'          => 'text',
                                            'label'         => __( 'Placeholder', 'ninja-forms' ),
                                            'desc'          => __( 'This text will be placed inside the field by default.', 'ninja-forms' ),

                                        ),
                                    ),
                                    'calc'                  => array(
                                        'auto_total'        => array(
                                            'type'          => 'checkbox',
                                            'label'         => __( 'Include in auto-total', 'ninja-forms' ),
                                            'desc'          => __( 'If this box is checked, this value will be added to the auto calculation', 'ninja-forms' ),
                                        ),
                                    ),
                                ),
                            ),
                        ),
                        array(
                            'id'                            => 'checkbox',
                            'name'                          => __( 'Checkbox', 'ninja-forms' ),
                            'data'                          => array(
                                'sidebars'                  => array(
                                    'basic'                 => __( 'Basic', 'ninja-forms' ),
                                    'calc'                  => __( 'Calculations', 'ninja-forms' ),
                                    'advanced'              => __( 'Advanced', 'ninja-forms' ),
                                    'cl'                    => __( 'Conditional Logic', 'ninja-forms' ),
                                    'styles'                => __( 'Styles', 'ninja-forms' ),                                   
                                ),
                                'settings'                  => array(
                                    'basic'                 => array(
                                        'label'             => array(
                                            'type'          => 'text',
                                            'label'         => __( 'Label', 'ninja-forms' ),
                                            'placeholder'   => __( 'My Field', 'ninja-forms' ),
                                            'desc'          => __( 'The text that identifies the field for your user.', 'ninja-forms' ),
                                        ),
                                       'label_pos'          => array(
                                            'type'          => 'select',
                                            'label'         => __( 'Label Position', 'ninja-forms' ),
                                            'desc'          => __( 'This is where the label is displayed in relation to the element.', 'ninja-forms' ),
                                        ),
                                    ),
                                    'calc'                  => array(
                                        'checked_value'     => array(
                                            'type'          => 'text',
                                            'label'         => __( 'Checked Calculation Value', 'ninja-forms' ),
                                            'desc'          => __( 'This is the value that will be used if Checked', 'ninja-forms' ),
                                        ), 
                                        'unchecked_value'     => array(
                                            'type'          => 'text',
                                            'label'         => __( 'Unchecked Calculation Value', 'ninja-forms' ),
                                            'desc'          => __( 'This is the value that will be used if Unchecked', 'ninja-forms' ),
                                        ),
                                        'auto_total'        => array(
                                            'type'          => 'checkbox',
                                            'label'         => __( 'Include in auto-total', 'ninja-forms' ),
                                            'desc'          => __( 'If this box is checked, this value will be added to the auto calculation', 'ninja-forms' ),
                                        ),        

                                    ),
                                ),
                            ),
                        ),
                        array(
                            'id'                            => 'textarea',
                            'name'                          => __( 'Textarea', 'ninja-forms' ),
                            'data'                          => array(
                                'sidebars'                  => array(
                                    'basic'                 => __( 'Basic', 'ninja-forms' ),
                                    'calc'                  => __( 'Calculations', 'ninja-forms' ),
                                    'advanced'              => __( 'Advanced', 'ninja-forms' ),
                                    'cl'                    => __( 'Conditional Logic', 'ninja-forms' ),
                                    'styles'                => __( 'Styles', 'ninja-forms' ),                                   
                                ),
                                'settings'                  => array(
                                    'basic'                 => array(
                                        'label'             => array(
                                            'type'          => 'text',
                                            'label'         => __( 'Label', 'ninja-forms' ),
                                            'placeholder'   => __( 'My Field', 'ninja-forms' ),
                                            'desc'          => __( 'The text that identifies the field for your user.', 'ninja-forms' ),
                                        ),
                                       'label_pos'          => array(
                                            'type'          => 'select',
                                            'label'         => __( 'Label Position', 'ninja-forms' ),
                                            'desc'          => __( 'This is where the label is displayed in relation to the element.', 'ninja-forms' ),
                                        ),
                                    ),
                                    'calc'                  => array(
                                        'auto_total'        => array(
                                            'type'          => 'checkbox',
                                            'label'         => __( 'Include in auto-total', 'ninja-forms' ),
                                            'desc'          => __( 'If this box is checked, this value will be added to the auto calculation', 'ninja-forms' ),
                                        ),        

                                    ),
                                ),
                            ),
                        ),
                        array(
                            'id'                            => 'radio',
                            'name'                          => __( 'Radio Buttons', 'ninja-forms' ),
                            'data'                          => array(
                                'sidebars'                  => array(
                                    'basic'                 => __( 'Basic', 'ninja-forms' ),
                                    'calc'                  => __( 'Calculations', 'ninja-forms' ),
                                    'advanced'              => __( 'Advanced', 'ninja-forms' ),
                                    'cl'                    => __( 'Conditional Logic', 'ninja-forms' ),
                                    'styles'                => __( 'Styles', 'ninja-forms' ),                                   
                                ),
                                'settings'                  => array(
                                    'basic'                 => array(
                                        'label'             => array(
                                            'type'          => 'text',
                                            'label'         => __( 'Label', 'ninja-forms' ),
                                            'placeholder'   => __( 'My Field', 'ninja-forms' ),
                                            'desc'          => __( 'The text that identifies the field for your user.', 'ninja-forms' ),
                                        ),
                                       'label_pos'          => array(
                                            'type'          => 'select',
                                            'label'         => __( 'Label Position', 'ninja-forms' ),
                                            'desc'          => __( 'This is where the label is displayed in relation to the element.', 'ninja-forms' ),
                                        ),
                                    ),
                                    'calc'                  => array(
                                        'auto_total'        => array(
                                            'type'          => 'checkbox',
                                            'label'         => __( 'Include in auto-total', 'ninja-forms' ),
                                            'desc'          => __( 'If this box is checked, this value will be added to the auto calculation', 'ninja-forms' ),
                                        ),        

                                    ),
                                ),
                            ),
                        ),
                        array(
                            'id'                            => 'submit',
                            'name'                          => __( 'Submit Button', 'ninja-forms' ),
                            'data'                          => array(
                                'sidebars'                  => array(
                                    'basic'                 => __( 'Basic', 'ninja-forms' ),
                                    'advanced'              => __( 'Advanced', 'ninja-forms' ),
                                    'cl'                    => __( 'Conditional Logic', 'ninja-forms' ),
                                    'styles'                => __( 'Styles', 'ninja-forms' ),                                   
                                ),
                                'settings'                  => array(
                                    'basic'                 => array(
                                        'label'             => array(
                                            'type'          => 'text',
                                            'label'         => __( 'Label', 'ninja-forms' ),
                                            'placeholder'   => __( 'My Field', 'ninja-forms' ),
                                            'desc'          => __( 'The text that identifies the field for your user.', 'ninja-forms' ),
                                        ),
                                    ),
                                ),
                            ),
                        ),
                    );
                }

                return $data;
                break;
            case 'PUT':
                $data = json_decode( $this->file );
                $current_value = $data->current_value;
                $object_id = $data->object_id;
                $meta_key = $data->meta_key;
                nf_update_meta( $object_id, $meta_key, $current_value );
                return $data;
                break;
            case 'DELETE':
                if ( !isset ( $_REQUEST['del'] ) )
                    return false;
                $object_id = str_replace( '/', '', $_REQUEST['del'] );
                return true;
                break;
            case 'POST':
                $data = json_decode( $this->file, true );
               
                return $data;
                break;
        }
    }
}

return new NF_Admin_Rest_API();