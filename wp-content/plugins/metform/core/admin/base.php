<?php
namespace MetForm\Core\Admin;
defined( 'ABSPATH' ) || exit;

/**
 * Metform settings related all functionalities.
 *
 * @version 1.1.8
 */
class Base {
    use \MetForm\Traits\Singleton;
    private $key_settings_option;

    public function __construct(){
        $this->key_settings_option = 'metform_option__settings';
    }

    public static function parent_slug(){
        return 'metform-menu';
    }

    public function init(){
        add_action('admin_menu', [$this, 'register_settings'], 999);
        add_action('admin_init', [$this, 'register_actions'], 999);
    }

    public function register_settings(){
        add_submenu_page( self::parent_slug(), esc_html__( 'Settings', 'metform' ), esc_html__( 'Settings', 'metform' ), 'manage_options', self::parent_slug().'-settings', [$this, 'register_settings_contents__settings'], 11);
    }

    public function register_settings_contents__settings(){

	    $code = '';
	    $disabledAttr = '';
	    $selectTheTab= false;

	    if(did_action('xpd_metform_pro/plugin_loaded')) {
	    	#Must be pro loaded....

		    if(!empty($_REQUEST['code'])) {

			    $code = $_REQUEST['code'];
			    $nonce = $_REQUEST['state'];
			    $option = get_option(\MetForm_Pro\Core\Integrations\Aweber::NONCE_VERIFICATION_KEY);

			    if($option == $nonce) {
				    update_option(\MetForm_Pro\Core\Integrations\Aweber::NONCE_VERIFICATION_KEY, '');
				    update_option(\MetForm_Pro\Core\Integrations\Aweber::AUTHORIZATION_CODE_KEY, $code);
			    }

			    $disabledAttr = 'disabled';
			    $selectTheTab = true;

		    } else {

			    $code = get_option(\MetForm_Pro\Core\Integrations\Aweber::AUTHORIZATION_CODE_KEY);

			    $disabledAttr = empty($code)? '': 'disabled';
		    }
	    }
    	#Let check if this is returned from aweber..
	    #Give state check

        include('views/settings.php');
    }

    public function get_settings_option($key = null , $default = null){
        if($key != null){
            $this->key_settings_option = $key;
        }
        return get_option($this->key_settings_option);
    }

    public function set_option($key, $default = null){
        
    }

    public function register_actions(){

        if(isset( $_POST['mf_settings_page_action'])) {
            // run a quick security check
            $request = $_POST;

            if( !check_admin_referer('metform-settings-page', 'metform-settings-page')){
                return;
            }

            $status = \MetForm\Core\Forms\Action::instance()->store( -1, $request);

            return $status;

        }
    }

}