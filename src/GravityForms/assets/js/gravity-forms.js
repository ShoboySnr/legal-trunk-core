jQuery(document).on('gform_post_render', function(event, form_id, current_page) {

    // toggle the country field to dropdown
    if(document.querySelector('#gform_fields_' + form_id  + ' .legal-trunk-core-gravity-country-field .select-wrapper'))  {
        document.querySelector('#gform_fields_' + form_id  + ' .legal-trunk-core-gravity-country-field .select-wrapper').addEventListener('click', function(e) {
            this.classList.toggle('open');
        });
    }

    for (const option of document.querySelectorAll('#gform_fields_' + form_id  +' .legal-trunk-core-gravity-country-field .custom-option')) {
        if (option.classList.contains('selected')) {
            const value = option.getAttribute('data-value');
            const wrapper_el = option.parentNode.parentNode.parentNode.parentNode.parentNode.parentNode.querySelector('select.legal-trunk-core-gravity-country-select-field');
            wrapper_el.value = value;
            option.closest('.select').querySelector('.select-trigger .custom-options').prepend(option);
        }
    }

    // select the country field value
    for (const option of document.querySelectorAll('#gform_fields_' + form_id  +' .legal-trunk-core-gravity-country-field .custom-option')) {
        option.addEventListener('click', function(event) {
            event.preventDefault();
            if (!this.classList.contains('selected')) {
                this.parentNode.querySelector('.custom-option.selected').classList.remove('selected');
                this.classList.add('selected');
                const value = this.getAttribute('data-value');
                const wrapper_el = this.parentNode.parentNode.parentNode.parentNode.parentNode.parentNode.querySelector('select.legal-trunk-core-gravity-country-select-field');
                wrapper_el.value = value;
                this.closest('.select').querySelector('.select-trigger .custom-options').prepend(this);
            }
        });
    }

    // when clicked outside the country select field, close the dropdown
    window.addEventListener('click', function(e) {
        const select_wrappers = document.querySelectorAll('.legal-trunk-core-gravity-country-field .select-wrapper');
        select_wrappers.forEach((element) => {
            if (!element.contains(e.target)) {
                element.classList.remove('open');
            }
        })
    });

    // action when the Add New Founders button is clicked
    for(const button of document.querySelectorAll('#gform_' + form_id  +' .legal-trunk-core-gravity-founders-field button.founders-name-add')) {
        button.addEventListener('click', function(event) {
            event.preventDefault();

            const parent_el = this.parentNode;
            console.log(parent_el);

            if(parent_el.querySelector('.legal-trunk-core-founders-grouped-custom-fields.added.default')) {
                parent_el.querySelector('.legal-trunk-core-founders-grouped-custom-fields.added.default').classList.remove('default');
                return;
            }

            const grouped_fields = parent_el.querySelectorAll('.legal-trunk-core-founders-grouped-custom-fields.added')[0];
            const cloned_grouped_fields = grouped_fields.cloneNode(true);

            //get existing added fields
            let fields_length = 0;
            let fields_input_length = 0;
            if(parent_el.querySelectorAll('.legal-trunk-core-founders-grouped-custom-fields.added')) {
                fields_length = parent_el.querySelectorAll('.legal-trunk-core-founders-grouped-custom-fields.added').length;
                fields_input_length = parent_el.querySelectorAll('.legal-trunk-core-input-fields').length;
            }

            fields_length++;
            fields_input_length++;

            if(fields_length >= 1) {
                alert('You can only add one founder.');
                return;
            }

            //dynamically change the input names and values
            cloned_grouped_fields.querySelector('input.legal_trunk_core_founder_name').setAttribute('name', 'legal_trunk_core_founder_name_' + fields_length);
            cloned_grouped_fields.querySelector('input.legal_trunk_core_founder_name').setAttribute('id', 'legal_trunk_core_founder_name_' + fields_length);
            cloned_grouped_fields.querySelector('label.legal_trunk_core_founder_name_label').setAttribute('for', 'legal_trunk_core_founder_name_' + fields_length);
            cloned_grouped_fields.querySelector('label.legal_trunk_core_founder_name_label').innerHTML = 'Founder ' + fields_length;

            cloned_grouped_fields.querySelector('select.legal_trunk_core_founder_position').setAttribute('name', 'legal_trunk_core_founder_position_' + fields_length);
            cloned_grouped_fields.querySelector('select.legal_trunk_core_founder_position').setAttribute('id', 'legal_trunk_core_founder_position_' + fields_length);
            cloned_grouped_fields.querySelector('label.legal_trunk_core_founder_position_label').setAttribute('for', 'legal_trunk_core_founder_position_' + fields_length);



            let field_id = cloned_grouped_fields.getAttribute('data-field-id');
            let form_id = cloned_grouped_fields.getAttribute('data-form-id');

            cloned_grouped_fields.querySelectorAll('.founders-field')[0].setAttribute('id', 'input_' + field_id + '_' + form_id + '.' + (fields_length + 1 ) + '_container' );
            cloned_grouped_fields.querySelectorAll('.founders-field')[1].setAttribute('id', 'input_' + field_id + '_' + form_id + '.' + ( fields_length + 2) + '_container' );
            cloned_grouped_fields.querySelector('input.legal_trunk_core_founders_input_field').setAttribute('name', 'input_' + field_id + '.' + fields_input_length );
            cloned_grouped_fields.querySelector('input.legal_trunk_core_founders_input_field').setAttribute('id', 'input_' + field_id + '_' + form_id + '_' + (fields_length + 1) );
            cloned_grouped_fields.querySelector('input.legal_trunk_core_founders_input_field').setAttribute('value', '' );
            cloned_grouped_fields.querySelector('input.legal_trunk_core_founders_select_field').setAttribute('name', 'input_' + field_id + '.' + (fields_input_length + 1 ) );
            cloned_grouped_fields.querySelector('input.legal_trunk_core_founders_select_field').setAttribute('id', 'input_' + field_id + '_' + form_id + '_' + (fields_length + 2));
            cloned_grouped_fields.querySelector('input.legal_trunk_core_founders_select_field').setAttribute('value', '');

            cloned_grouped_fields.classList.add('added');
            cloned_grouped_fields.classList.remove('default');

            // Add remove action when the remove button is clicked
            grouped_fields.querySelector('a.remove').addEventListener('click', function(event) {
                event.preventDefault();
                if(confirm('Are you sure you want to remove Founder ' + fields_length + ' Name and Position')) {
                    let selected_element = this.parentNode.parentNode;
                    if(selected_element) {
                        selected_element.remove();
                        let real_value = JSON.parse(parent_el.querySelector('input.legal_trunk_core_founders_input_field').value);
                        if(real_value[fields_length - 1]) {
                            real_value.splice((fields_length - 1), 1);
                        }
                        parent_el.querySelector('input.legal_trunk_core_founders_input_field').value = JSON.stringify(real_value);
                    }
                }
            });

            //update the input value of the field when changes are made to two grouped fields
            cloned_grouped_fields.querySelector('input.legal_trunk_core_founder_name').addEventListener('keyup', function(event) {
                let this_value = event.target.value;
                console.log(this.parentNode.parentNode.querySelector('input.legal_trunk_core_founders_input_field'));
                this.parentNode.parentNode.querySelector('input.legal_trunk_core_founders_input_field').value = this_value;
            });

            cloned_grouped_fields.querySelector('select.legal_trunk_core_founder_position').addEventListener('change', function(event) {
                let this_value = event.target.value;
                this.parentNode.parentNode.querySelector('input.legal_trunk_core_founders_select_field').value = this_value;
            })


            // prepend element
            parent_el.prepend(cloned_grouped_fields);

            cloned_grouped_fields.scrollIntoView({ behavior: 'smooth'});
        });

        //update the input value of the field when changes are made to two grouped fields
        document.querySelectorAll('.legal-trunk-core-founders-grouped-custom-fields.added input.legal_trunk_core_founder_name').forEach((element) => {
            element.addEventListener('keyup', function(event) {
                let this_value = event.target.value;
                console.log(this.parentNode.parentNode.querySelector('input.legal_trunk_core_founders_input_field'));
                this.parentNode.parentNode.querySelector('input.legal_trunk_core_founders_input_field').value = this_value;
            });
        });

        document.querySelectorAll('.legal-trunk-core-founders-grouped-custom-fields.added select.legal_trunk_core_founder_position').forEach((element) => {
            element.addEventListener('change', function(event) {
                let this_value = event.target.value;
                this.parentNode.parentNode.querySelector('input.legal_trunk_core_founders_select_field').value = this_value;
            })
        });
    }
});