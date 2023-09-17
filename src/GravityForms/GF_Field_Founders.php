<?php

namespace LegalTrunkCore\GravityForms;

use GF_Field;
use GFCommon;
use GFFormsModel;

class GF_Field_Founders extends GF_Field {
	
	public $type = 'founders';
	
	public function get_form_editor_field_title() {
		return esc_attr__( 'Founders', 'legal-trunk-core' );
	}
	
	public function get_form_editor_button() {
		return array(
			'group' => 'advanced_fields',
			'text'  => $this->get_form_editor_field_title(),
		);
	}
	
	public function get_form_editor_field_settings() {
		return array(
			'conditional_logic_field_setting',
			'prepopulate_field_setting',
			'error_message_setting',
			'choices_setting',
			'label_setting',
			'admin_label_setting',
			'rules_setting',
			'duplicate_setting',
			'description_setting',
			'css_class_setting',
		);
	}
	
	public function is_value_submission_array() {
		return true;
	}
	
	public function is_conditional_logic_supported() {
		return false;
	}
	
	public function get_field_input( $form, $value = '', $entry = null ) {
		$is_entry_detail = $this->is_entry_detail();
		$is_form_editor  = $this->is_form_editor();
		
		$founder_positions = Fields::get_founders();
		
		$form_id  = $form['id'];
		$field_id = intval( $this->id );
		
		$fullname = $position = '';
		
		
		$disabled_text = $is_form_editor ? "disabled='disabled'" : '';
		$class_suffix  = $is_entry_detail ? '_admin' : '';
		
		$fullname_tabindex = GFCommon::get_tabindex();
		$position_tabindex  = GFCommon::get_tabindex();
//		$email_tabindex = GFCommon::get_tabindex();
//		$phone_tabindex = GFCommon::get_tabindex();
		
		$required_attribute = $this->isRequired ? 'aria-required="true"' : '';
		$invalid_attribute  = $this->failed_validation ? 'aria-invalid="true"' : 'aria-invalid="false"';
		
		if ( is_array( $value ) ) {
			$fullname = esc_attr( rgget( $this->id . '.1' , $value ) );
			$position = esc_attr( rgget( $this->id . '.2', $value ) );
		}
		
		
		$is_default = 'default';
		if(!empty(array_filter($value))) {
			$is_default = '';
		}
		
		$pre_markup = '<div class="legal-trunk-core-founders-grouped-custom-fields added '.$is_default.'". data-field-id="'.$field_id.'" data-form-id="'.$form_id.'">';
		
		$first_markup = '<div id="input_' . $field_id . '_' . $form_id . '.1_container" class="founders-field">';
		
		$first_markup .= '<label class="gfield_label gform-field-label legal_trunk_core_founder_name_label" for="legal_trunk_core_founder_name">'. __('Founder 1', 'legal-trunk-core') . '</label>';
		
		$first_markup .= '<div class="gfield_description">'. __('Enter full name of founder', 'legal-trunk-core'). '</div>';
		
		$first_markup .= '<div class="ginput_container ginput_container_text">';
		$first_markup .= '<input type="text" id="legal_trunk_core_founder_name" class="large legal_trunk_core_founder_name" value="'.$fullname.'" name="legal_trunk_core_founder_name" aria-invalid="false"/>';
		$first_markup .= '</div>';
		
		$first_markup .= '<input type="hidden" name="input_' . $field_id . '.1" id="input_' . $field_id . '_' . $form_id . '_1"  value="'.$fullname.'" class="legal-trunk-core-input-fields legal_trunk_core_founders_input_field" aria-label="Full Name" ' . $fullname_tabindex . ' ' . $disabled_text . ' ' . $required_attribute . ' ' . $invalid_attribute . ' >';
		
		$first_markup .= '</div>';
		
		
		$last_markup = '<div id="input_' . $field_id . '_' . $form_id . '.2_container" class="founders-field">';
		
		$last_markup .= '<label class="gfield_label gform-field-label legal_trunk_core_founder_position_label" for="legal_trunk_core_founder_position">'. __('Founder Position', 'legal-trunk-core') . '</label>';
		
		$last_markup .= '<div class="ginput_container ginput_container_select">';
		
		$last_markup .= '<select id="legal_trunk_core_founder_position" class="large gfield_select legal_trunk_core_founder_position" name="legal_trunk_core_founder_position" aria-invalid="false">';
		
		foreach ($founder_positions as $key => $founder_position) {
			$selected = $position == $key ? ' selected' : '';
			$last_markup .= '<option value="'. esc_html($key). '"'. $selected. '>'. esc_html($founder_position). '</option>';
		}
		
		$last_markup .= '</select>';
		
		$last_markup .= '</div>';
		
		$last_markup .= '<a href="#" class="remove" title="'. __('Remove this', 'legal-trunk-core').'" style="display:none;">'. __('Remove this', 'legal-trunk-core'). '</a>';
		
		$last_markup .= '<input type="hidden" name="input_' . $field_id . '.2" id="input_' . $field_id . '_' . $form_id . '_2" value="' . $position . '" class="legal-trunk-core-input-fields legal_trunk_core_founders_select_field" aria-label="Position" ' . $position_tabindex . ' ' . $disabled_text . ' ' . $required_attribute . ' ' . $invalid_attribute . '>';
		
		$last_markup .= '</div>';
		
		
//		}


//		$second_markup = '<div id="input_' . $field_id . '_' . $form_id . '.3_container" class="founders-field">';
//
//		$second_markup .= '<label class="gfield_label gform-field-label legal_trunk_core_founder_name_label" for="legal_trunk_core_founder_name">'. __('Founder 1', 'legal-trunk-core') . '</label>';
//
//		$second_markup .= '<div class="gfield_description">'. __('Enter full name of founder', 'legal-trunk-core'). '</div>';
//
//		$second_markup .= '<div class="ginput_container ginput_container_text">';
//		$second_markup .= '<input type="text" id="legal_trunk_core_founder_name" class="large legal_trunk_core_founder_name" value="" name="legal_trunk_core_founder_name" aria-invalid="false"/>';
//		$second_markup .= '</div>';
//
//		$second_markup .= '<input type="text" name="input_' . $field_id . '.3" id="input_' . $field_id . '_' . $form_id . '_3"  value="'.$email.'" class="legal_trunk_core_founders_input_field" aria-label="Full Name" ' . $email_tabindex . ' ' . $disabled_text . ' ' . $required_attribute . ' ' . $invalid_attribute . ' >';
//
//		$second_markup .= '</div>';

//		$first_markup = '<span id="input_' . $field_id . '_' . $form_id . '.1_container" class="attendees_first">';
//		$first_markup .= '<input type="text" name="input_' . $field_id . '.1" id="input_' . $field_id . '_' . $form_id . '_1" value="' . $first . '" aria-label="First Name" ' . $first_tabindex . ' ' . $disabled_text . ' ' . $required_attribute . ' ' . $invalid_attribute . '>';
//		$first_markup .= '<label for="input_' . $field_id . '_' . $form_id . '_1">First Name</label>';
//		$first_markup .= '</span>';

//		$last_markup = '<span id="input_' . $field_id . '_' . $form_id . '.2_container" class="attendees_last">';
//		$last_markup .= '<input type="text" name="input_' . $field_id . '.2" id="input_' . $field_id . '_' . $form_id . '_2" value="' . $last . '" aria-label="Last Name" ' . $last_tabindex . ' ' . $disabled_text . ' ' . $required_attribute . ' ' . $invalid_attribute . '>';
//		$last_markup .= '<label for="input_' . $field_id . '_' . $form_id . '_2">Last Name</label>';
//		$last_markup .= '</span>';
		
		$post_markup = '</div>';
		
		$button_markup = '<button type="button" class="theme-button secondary founders-name-add" name="founders-field-add"> '. __('Add New Founders', 'legal-trunk-core'). ' </button>';
		
		$css_class = $this->get_css_class();
		
		return "<div class='ginput_complex{$class_suffix} ginput_container {$css_class} gfield_trigger_change' id='{$field_id}'>
                	<div class='legal-trunk-core-gravity-founders-field'>
                	<div class='founders-field-container'>
		                {$pre_markup}
		                {$first_markup}
		                {$last_markup}
		                {$post_markup}
		                {$button_markup}
		            </div>
		            </div>
                <div class='gf_clear gf_clear_complex'></div>
            </div>";
	}
	
	public function get_css_class() {
		$fullname_input = GFFormsModel::get_input( $this, $this->id . '.1' );
		$position_input  = GFFormsModel::get_input( $this, $this->id . '.2' );
		
		$css_class           = '';
		$visible_input_count = 0;
		
		if ( $fullname_input && ! rgar( $fullname_input, 'isHidden' ) ) {
			$visible_input_count ++;
			$css_class .= 'has_fullname_name ';
		} else {
			$css_class .= 'no_first_name ';
		}
		
		if ( $position_input && ! rgar( $position_input, 'isHidden' ) ) {
			$visible_input_count ++;
			$css_class .= 'has_position ';
		} else {
			$css_class .= 'no_position ';
		}
		
		$css_class .= "gf_founders_has_{$visible_input_count} ginput_container_attendees ";
		
		return trim( $css_class );
	}
	
	public function get_form_editor_inline_script_on_page_render() {
		
		// set the default field label for the field
		$script = sprintf( "function SetDefaultValues_%s(field) {
    field.label = '%s';
    field.inputs = [new Input(field.id + '.1', '%s'), new Input(field.id + '.2', '%s')];
        }", $this->type, $this->get_form_editor_field_title(), 'Full Name', 'Position') . PHP_EOL;
		
		
		return $script;
	}
	
	public function get_value_entry_detail( $value, $currency = '', $use_text = false, $format = 'html', $media = 'screen' ) {
		if ( is_array( $value ) ) {
			$founder_positions = Fields::get_founders();
			
			$return = '<table class="legal-trunk-core-founders-entry-details">';
			
			$return .= '<thead>';
			
			$return .= '<th>'. __('Full Name', 'legal-trunk-core'). '</th>';
			$return .= '<th>'. __('Position', 'legal-trunk-core'). '</th>';
			
			$return .= '</thead>';
			
			$return .= '<tbody>';
			
			$fullname = rgget( $this->id . '.1', $value );
			$position = rgget( $this->id . '.2', $value );
			
			if(!empty($position)) {
				$position = $founder_positions[ $position ] ?? __( 'Not found', 'legal-trunk-core' );
			}
			
			$return .= '<tr>';
			
			$return .= '<td>'. $fullname. '</td>';
			$return .= '<td>'. $position. '</td>';
			
			$return .= '</tr>';
			
			
			$return .= '</tbody>';
			
			$return .= '</table>';
			
		} else {
			$return = '';
		}
		
//		if ( $format === 'html' ) {
//			$return = esc_html( $return );
//		}
		
		return $return;
	}
	
}