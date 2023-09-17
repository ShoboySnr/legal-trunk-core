<?php

namespace LegalTrunkCore\GravityForms;

use GF_Field;

class GF_Field_CountrySelect extends GF_Field {
	
	public $type = 'country-select';
	
	public $choices = [
		[ 'text' => 'United States of America' ],
		[ 'text' => 'Nigeria' ],
	];
	
	public function get_form_editor_field_title() {
		return esc_attr__('Country Select', 'legal-trunk-core');
	}
	
	public function get_form_editor_button() {
		return [
			'group' => 'advanced_fields',
			'text'  => $this->get_form_editor_field_title(),
		];
	}
	
	public function get_form_editor_field_icon() {
		return 'gform-icon--check-box';
	}
	
	public function get_form_editor_field_description() {
		return esc_attr__( 'Add Country Select fields.', 'gravityforms' );
	}
	
	public function get_form_editor_field_settings() {
		return [
			'label_setting',
			'choices_setting',
			'description_setting',
			'rules_setting',
			'error_message_setting',
			'css_class_setting',
			'conditional_logic_field_setting'
		];
	}
	
	
	public function get_field_input($form, $value = '', $entry = null) {
		if ($this->is_form_editor() || is_admin()) {
			return 'Preview not available in editor mode, only in frontend';
		}
		
		$id = (int) $this->id;
		
		
		$label =  $this->label;
		
		$element = '<div class="legal-trunk-core-gravity-country-field">';
		
		if(!empty($this->choices)) {
			$element .= '<select class="legal-trunk-core-gravity-country-select-field" name="input_' . $id . '">';
			
			$count = 0;
			foreach ($this->choices as $choice) {
				$selected = !empty($choice['isSelected'] || $value == $choice['value']) ? 'selected' : '';
				if(empty($selected) && $count == 0) $selected = 'selected';
				$element .= '<option value="'. $choice['value']. '" '.$selected.'>'. $choice['text']. '</option>';
				$count++;
			}
			$element .= '</select>';
			
			$element .= '<div class="select-wrapper">';
			$element .= '<div class="select">';
			$element .= '<div class="select-trigger"><span class="label">'. $label. '</span>';
			$element .= '<div class="arrow"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="11" fill="none"><path fill="#534E6E" fill-opacity=".69" d="M2.406.65.992 2.062l8.004 8.004 8.006-7.898L15.598.745 9.004 7.247 2.406.65Z"/></svg></div>';
			
			$element .= '<div class="custom-options">';
			$count = 0;
			foreach ($this->choices as $choice) {
				$flag = get_the_country_flag($choice['value']);
				$selected = !empty($choice['isSelected']) || $choice['value'] == $value ? ' selected' : '';
				if(empty($selected) && $count == 0) $selected = ' selected';
				$element .= ' <div class="custom-option '.$selected.'" data-value="'.$choice['value'].'">'. $flag. '<span>'. $choice['text'] . '</span></div>';
				$count++;
			}
			$element .= '</div>';
			
			$element .= '</div>';
			$element .= '</div>';
			$element .= '</div>';
		}
		$element .= '</div>';
		
		return $element;
	}
	
	public function get_value_save_entry($value, $form, $input_name, $lead_id, $lead) {
		if ( rgblank( $value ) ) {
			
			return '';
			
		} elseif ( is_array( $value ) ) {
			foreach ( $value as &$v ) {
				
				if ( is_array( $v ) ) {
					$v = '';
				}
				
				$v = $this->sanitize_entry_value( $v, $form['id'] );
				
			}
			
			return implode( ',', $value );
		} else {
			return $this->sanitize_entry_value( $value, $form['id'] );
		}
	}
	
	public function get_value_entry_list( $value, $entry, $field_id, $columns, $form ) {
		return esc_html( $this->get_selected_choice_output( $value, '', true ) );
	}
	
	public function get_value_entry_detail( $value, $currency = '', $use_text = false, $format = 'html', $media = 'screen' ) {
		return esc_html( $this->get_selected_choice_output( $value, $currency, $use_text = true ) );
	}
	
	public function get_selected_choice_output($value, $currency = '', $use_text = '') {
		if ( is_array( $value ) ) {
			return '';
		}
		
		$choice = $this->get_selected_choice( $value );
		
		if ( $use_text && ! empty( $choice['text'] ) ) {
			$value = $choice['text'];
		}
		
		return empty( $choice ) ? wp_strip_all_tags( $value ) : wp_kses_post( $value );
	}
	
	
	public function get_selected_choice( $value ) {
		
		if ( rgblank( $value ) || is_array( $value ) || empty( $this->choices ) ) {
			return array();
		}
		
		foreach ( $this->choices as $choice ) {
			if ( \GFFormsModel::choice_value_match( $this, $choice, $value ) ) {
				return $choice;
			}
		}
		
		return array();
	}
	
	
	public function get_value_merge_tag( $value, $input_id, $entry, $form, $modifier, $raw_value, $url_encode, $esc_html, $format, $nl2br ) {
		return '';
	}
	
	public function is_value_submission_empty($form_id) {
		$value = rgpost('input_' . $this->id);
		
		if(strlen(trim($value)) > 0) {
			return false;
		}
		
		return true;
	}
	
}