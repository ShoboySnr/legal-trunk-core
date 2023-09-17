<?php

namespace LegalTrunkCore\GravityForms;

class Form {
	
	public function __construct() {
		add_action( 'gform_enqueue_scripts', array($this, 'enqueue_scripts'), 10, 2 );
		add_filter( 'gform_progress_bar', array( $this, 'progress_bar_design'), 10, 3);
		add_action( 'gform_after_submission', array( $this, 'callback_after_submission'), 10, 2 );
		add_action( 'gform_incomplete_submission_post_save', array($this, 'handle_draft_submission'), 10, 4);
	}
	
	public function enqueue_scripts($form, $is_ajax) {
		wp_enqueue_style('gforms_legal_trunk_core_css', LOCAL_TRUNK_CORE_GRAVITY_FORMS_ASSETS_DIRECTORY.'/css/gravity-forms.css');
		wp_enqueue_script('gforms_legal_trunk_core_js', LOCAL_TRUNK_CORE_GRAVITY_FORMS_ASSETS_DIRECTORY.'/js/gravity-forms.js');
	}
	
	
	public function progress_bar_design($progress_bar, $form, $confirmation_message) {
		$form_id           = $form['id'];
		$progress_complete = false;
		$page_count        = self::get_max_page_number( $form );
		$current_page      = rgpost('gform_target_page_number_' . $form['id']) ? intval(rgpost('gform_target_page_number_' . $form['id'])) : 1;;
		$style             = $form['pagination']['style'];
		$color             = $style == 'custom' ? " color:{$form['pagination']['color']};" : '';
		$bgcolor           = $style == 'custom' ? " background-color:{$form['pagination']['backgroundColor']};" : '';
		$page_name         = rgars( $form['pagination'], sprintf( 'pages/%d', $current_page - 1 ) );
		$page_name         = ! empty( $page_name ) ?  $page_name : '';
		
		if ( ! empty( $confirmation_message ) ) {
			$progress_complete = true;
		}
		
		$progressbar_page_count = $current_page;
		$percent                = ! $progress_complete ? floor( ( ( $progressbar_page_count ) / $page_count ) * 100 ) . '%' : '100%';
		$percent_number         = ! $progress_complete ? floor( ( ( $progressbar_page_count ) / $page_count ) * 100 ) . '' : '100';
		
		
		$progress_bar = "
        <div id='gf_progressbar_wrapper_{$form_id}' class='gf_progressbar_wrapper'>";
		$progress_bar .= "<span class='gf_step_current_page'> {$page_name} </span>";
		$progress_bar .= "
            <div class='gf_progressbar gf_progressbar_{$style}' aria-hidden='true'>
                <div class='gf_progressbar_percentage percentbar_{$style} percentbar_{$percent_number}' style='width:{$percent};{$color}{$bgcolor}'></div>
            </div></div>";
		//close div for surrounding wrapper class when confirmation page
		$progress_bar .= $progress_complete ? $confirmation_message . '</div>' : '';
		
		return $progress_bar;
	}
	
	
	public function callback_after_submission($entry, $form_object) {
		if(!empty(rgpost( 'gform_resume_token' ))) {
			update_field('gform_custom_resume_token', sanitize_key(rgpost( 'gform_resume_token' )), $entry['id'] );
		}
		
		// check if entry id is set
		if(!empty($_REQUEST['entry-id']) && !empty($_REQUEST['type']) && $_REQUEST['type'] == 'payment' ) {
			$form_id = sanitize_text_field($_REQUEST['form-id']);
			$entry_id = sanitize_text_field($_REQUEST['entry-id']);
			$payment_entry_id = sanitize_text_field($entry['id']);
			$author_id = get_current_user_id();
			
			$args = array(
				'post_type' => 'company',
				'meta_key' => 'entry_id',
				'author'    => $author_id,
				'meta_query' => array(
					array(
						'key' => 'entry_id',
						'value' => $entry_id,
						'compare' => '=',
					)
				)
			);
			$query_existing_company = new \WP_Query($args);
			if($query_existing_company->have_posts()) {
				$companies = $query_existing_company->get_posts();
				if(!empty($companies[0])) {
					$company_id = $companies[0]->ID;
					
					// update the custom fields
					
					// update the registration payment status
					update_field('registration_payment_status', 'paid', $company_id );
					
					// update the entry & form id
					update_field('registration_payment_entry_id', $entry_id, $company_id );
					update_field('registration_payment_form_id', $form_object['id'], $company_id );
					
					update_field('user_id', $author_id, $company_id );
					
					// delete previously draft
					$resume_token = get_field('gform_custom_resume_token', $entry_id);
					$args = array(
						'post_type'     => 'company',
						'post_status'   => 'draft',
						'meta_key' => 'draft_token',
						'author'    => get_current_user_id(),
						'meta_query' => array (
							'relation'  => 'AND',
							array(
								'key' => 'form_id',
								'value' => sanitize_key($form_id),
								'compare' => '=',
							),
							array(
								'key' => 'draft_token',
								'value' => sanitize_key($resume_token),
								'compare' => '=',
							)
						)
					);
					
					$previous_draft_companies = new \WP_Query($args);
					if($previous_draft_companies->have_posts()) {
						foreach ($previous_draft_companies->get_posts() as $draft_company) {
							$company_id = $draft_company->ID;
							wp_delete_post($company_id);
						}
						
						delete_field('gform_custom_resume_token', $entry_id);
					}
				}
			}
		}
	}
	
	public function handle_draft_submission( $submission, $token, $form, $entry ) {
		$form_id = $form['id'];
		
		$feeds = \GFAPI::get_feeds(null, $form_id, 'gravityformsadvancedpostcreation');
		foreach ($feeds as $feed) {
			$current_feed_id = $feed['id'];
			$author_id = $this->get_post_author( null, $feed, $entry );
			
			// Prepare post object.
			$post = array(
				'post_status'    => 'draft', // always set to draft since it is an incomplete submission
				'post_type'      => $feed['meta']['postType'],
				'post_title'     => $this->get_post_title( $feed, $entry, $form ),
				'post_date_gmt'  => rgar( $entry, 'date_created' ),
				'comment_status' => rgars( $feed, 'meta/postComments' ) ? 'open' : 'closed',
				'ping_status'    => rgars( $feed, 'meta/postPingbacks' ) ? 'open' : 'closed',
			);
			
			// find post with the meta token and author id
			$args = array(
				'post_type' => $feed['meta']['postType'],
				'post_status'   => ['draft', 'publish'],
				'meta_key' => 'draft_token',
				'author'    => $author_id,
				'meta_query' => array(
					array(
						'key' => 'draft_token',
						'value' => $token,
						'compare' => '=',
					)
				)
			);
			
			$post_id = 0;
			$query_existing_company = new \WP_Query($args);
	
			if($query_existing_company->have_posts()) {
				$companies = $query_existing_company->get_posts();
				if(!empty($companies[0])) {
					$post_id = $companies[0]->ID;
				}
			}
			
			if(empty($post_id)) {
				// Create base post object.
				$post_id = wp_insert_post( $post, true );
			}
			
			// Add post ID to post object.
			$post['ID'] = $post_id;
			
			// Add the form ID so it is available for GFFormsModel::copy_post_image().
			update_post_meta( $post['ID'], '_gform-form-id', $form['id'] );
			
			// Set standard post data.
			$post = $this->set_post_data( $post, $feed, $entry, $form );
			
			//update the token field
			update_field('draft_token', $token, $post_id);
			
			// update the form id
			update_field('form_id', $form_id, $post_id);
			
			$post = gf_apply_filters( array( 'gform_advancedpostcreation_post', $form['id'] ), $post, $feed, $entry, $form );
			
			// Save full post object.
			$updated_post = wp_update_post( $post, true );
			
			if(is_wp_error($updated_post)) {
				return $entry;
			} else {
				// Add entry and feed ID to post meta.
				update_post_meta( $post['ID'], '_gravityformsadvancedpostcreation_entry_id', $entry['id'] );
				update_post_meta( $post['ID'],  '_gravityformsadvancedpostcreation_feed_id', $feed['id'] );
			}
			
			// Set post format.
			if ( rgars( $feed, 'meta/postFormat' ) ) {
				set_post_format( $post['ID'], rgars( $feed, 'meta/postFormat' ) );
			}
			
			
			\GF_Advanced_Post_Creation::get_instance()->maybe_set_post_meta( $post['ID'], $feed, $entry, $form );
			
			// Get entry post ID meta.
			$entry_post_ids = gform_get_meta( $entry['id'],  'gravityformsadvancedpostcreation_post_id' );
			
			// If entry post ID meta is not an array, set it to an array.
			if ( ! is_array( $entry_post_ids ) ) {
				$entry_post_ids = array();
			}
			
			// Add post ID to array.
			$entry_post_ids[] = array(
				'post_id' => $post['ID'],
				'feed_id' => $feed['id'],
			);
			
			// Save entry meta.
			gform_update_meta( $entry['id'],  'gravityformsadvancedpostcreation__post_id', $entry_post_ids );
		}
	}
	
	
	public function get_post_author( $id, $feed = array(), $entry = array() ) {
		if ( ! empty( $id ) ) {
			return $id;
		} elseif ( 'logged-in-user' === rgars( $feed, 'meta/postAuthor' ) ) {
			return rgar( $entry, 'created_by' );
		} else {
			return rgars( $feed, 'meta/postAuthor' );
		}
	}
	
	
	public function get_post_title( $feed, $entry, $form ) {
		if(empty($feed['meta']['postTitle'])) {
			return sprintf('Draft Company - %s', date('Y-m-d H:i:s') );
		}
		return \GFCommon::replace_variables( $feed['meta']['postTitle'], $form, $entry, false, false, false, 'text' );
	}
	
	
	public function set_post_data( $post, $feed, $entry, $form ) {
		// Set the post content.
		$post['post_content'] = \GF_Advanced_Post_Creation::get_instance()->prepare_post_content( $feed, $entry, $form, $post['ID'] );
		
		// Set the post password.
		if ( 'password-protected' === $feed['meta']['postVisibility'] ) {
			$post['post_password'] = \GFCommon::replace_variables( $feed['meta']['postPassword'], $form, $entry, false, false, false, 'text' );
		}
		
		// Set the post excerpt.
		if ( rgars( $feed, 'meta/postExcerpt' ) ) {
			$post['post_excerpt'] = \GFCommon::replace_variables( $feed['meta']['postExcerpt'], $form, $entry );
		}
		
		// Set the post author.
		$post['post_author'] = $this->get_post_author( null, $feed, $entry );
		
		// Set the post date.
		switch ( rgars( $feed, 'meta/postDate' ) ) {
			case 'custom':
				$post['post_date']     = $this->get_formatted_date( rgars( $feed, 'meta/postDateCustom' ) );
				$post['post_date_gmt'] = get_gmt_from_date( $post['post_date'] );
				break;
			case 'entry':
				$post['post_date_gmt'] = rgar( $entry, 'date_created' );
				break;
			case 'field':
				$date = \GFAddOn::get_field_value( $form, $entry, $feed['meta']['postDateFieldDate'] ) . ' ' . $this->get_field_value( $form, $entry, $feed['meta']['postDateFieldTime'] );
				
				$post['post_date']     = $this->get_formatted_date( $date );
				$post['post_date_gmt'] = get_gmt_from_date( $date );
				break;
		}
		
		return $post;
	}
	
	
	private function get_formatted_date( $date ) {
		require_once __DIR__ . '/includes/helpers/wp-timezone.php';
		
		try {
			if ( rgblank( trim( $date ) ) ) {
				$date = current_time( 'mysql' );
			}
			
			return ( new DateTime( $date, wp_timezone() ) )->format( 'Y-m-d H:i:s' );
		} catch ( Exception $e ) {
			// If we can't parse the date because it's invalid, set the post date to now.
			return ( new DateTime( 'now', wp_timezone() ) )->format( 'Y-m-d H:i:s' );
		}
	}
	
	
	
	public static function get_max_page_number( $form ) {
		$page_number = 0;
		foreach ( $form['fields'] as $field ) {
			if ( $field->type == 'page' ) {
				$page_number ++;
			}
		}
		
		return $page_number == 0 ? 0 : $page_number + 1;
	}
	
	
	/**
	 * @return Form
	 */
	public static function get_instance() {
		static $instance = null;
		
		if (is_null($instance)) {
			$instance = new self();
		}
		
		return $instance;
	}
}