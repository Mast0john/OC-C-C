<?php
namespace Elementor;

use \MetForm\Controls\Controls_Manager as MetForm_Controls_Manager;

defined( 'ABSPATH' ) || exit;

class Widget_Met_Form extends Widget_Base {
	use \MetForm\Widgets\Widget_Notice;

	public function get_name() {
		return 'metform';
    }
    
	public function get_title() {
		return esc_html__( 'MetForm', 'metform' );
	}

	public function show_in_panel() {
        return 'metform-form' != get_post_type();
	}

	public function get_categories() {
		return [ 'metform' ];
	}

	public function get_keywords() {
        return ['metform', 'form'];
	}

	
	protected function _register_controls() {
		
        $this->start_controls_section(
			'content_section',
			[
				'label' => esc_html__( 'Content', 'metform' ),
				'tab' => Controls_Manager::TAB_CONTENT,
			]
		);
		$this->add_control(
            'important_note',
            [
                'label' => '',
                'type' => \Elementor\Controls_Manager::RAW_HTML,
                'raw' => 'See this video tutorial how to use metform. <a href="https://youtu.be/8R4-Q14cu-w" target="_blank">Click here</a> <button class="mf-edit-form">Edit Form</button>',
            ]
		);
		

		$this->add_control(
			'mf_form_id',
			[
				'label' => esc_html__( 'Select Form: ', 'metform' ),
				'type' => MetForm_Controls_Manager::FORMPICKER,
				'default' => '',
				'description' => esc_html__( 'Click on the "red" edit icon to edit form content.', 'metform' )
			]
		);


		$this->end_controls_section();
		$this->insert_pro_message();
	}

	protected function render( $instance = [] ) {
		$settings = $this->get_settings_for_display();
		$nav = !isset($settings['mf_form_multistep_display_nav']) ? '' : ' mf-form-multistep-nav-'.$settings['mf_form_multistep_display_nav'];
		$direction = !isset($settings['mf_form_multistep_slide_direction']) ? '' : ' mf_slide_direction_'. $settings['mf_form_multistep_slide_direction'];
		$form_data = json_decode($settings['mf_form_id'], true);


		// take the value when metform-pro is activated
		if(in_array('metform-pro/metform-pro.php', apply_filters('active_plugins', get_option('active_plugins')))):
			$message_display_position	= isset($settings['mf_response_display_position']) ? $settings['mf_response_display_position'] : '';
			$message_success_icon		= isset($settings['mf_success_icon']['value']) ? $settings['mf_success_icon']['value'] : '';
			$message_error_icon			= isset($settings['mf_error_icon']['value']) ? $settings['mf_error_icon']['value'] : '';
			$message_edit_switch		= isset($settings['mf_success_controls']) ? $settings['mf_success_controls'] : '' ;
		
		// pass default value while metfomr-pro is not activated
		else:
			$message_display_position	= 'top';
			$message_success_icon		= 'fas fa-check';
			$message_error_icon			= 'fas fa-exclamation-triangle';
			$message_edit_switch		= false;

		endif;

		if(is_array($form_data) && isset($form_data['id'])){
			unset($settings['mf_form_id']);
			$form_id = explode('***', $form_data['id']);
			$form_id = $form_id[0];

			$ffarg = get_posts([
				'numberposts'	=> 1,
				'p'         	=> $form_id,
				'post_type' 	=> 'metform-form'
			]);

			$ffarg = (!empty($ffarg) ? $ffarg : get_posts([
				'numberposts'	=> 1,
				'post_type' 	=> 'metform-form',
				'meta_key'		=> '_metform_cloned_id',
				'meta_value'	=> 'template-' . $form_id,
			]));

			if(empty($ffarg) && isset($form_data['data'])){
				$form_id = \MetForm\Core\Forms\Builder::instance()->create_form('', $form_id, 
					(isset($form_data['data'][0]) ? $form_data['data'][0] : '')
				);
				unset($form_data);
			}else{
				$form_id = $ffarg[0]->ID;
			}
		}else{			

			$form_id = explode('***', $settings['mf_form_id']);
			$form_id = $form_id[0];
		}


		echo '<div id="mf-response-props-id-'. $form_id .'" data-editswitchopen="'. $message_edit_switch .'" data-erroricon="'. $message_error_icon .'" data-successicon="'. $message_success_icon .'" data-messageposition="'. $message_display_position .'" class=" ' . $direction .' '. (!isset($settings['mf_form_multistep_status']) ? '' : $settings['mf_form_multistep_status']) . $nav .' mf-scroll-top-'. esc_attr( (!empty($settings['mf_step_scroll_top']) && 'yes' == $settings['mf_step_scroll_top']) ? $settings['mf_step_scroll_top'] : 'no' ) .'">';
			echo \MetForm\Controls\Form_Picker_Utils::parse($form_id , $this->get_id());
		echo '</div>';
	}
}
