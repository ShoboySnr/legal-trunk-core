<?php

namespace LegalTrunkCore\UsersWP;

use Tzsk\Otp\Facades\Otp;
use Twilio\Rest\Client;


class Authentication {
  
  private $twillio_accounts_sid = 'AC53d0fa637d47e31d9f74e669a0260481';
  private $twillio_auth_token = '13e0035efcec799cb0adbbb9f1ccd14c';
  private $twillio_username = 'byteconceptng@gmail.com';
  private $twillio_password = 'Iamshoboy0779@@Iamshoboy0779@@Iamshoboy0779@@';
  private $twillio_phone = '+18159895002';
	
	/**
	 * Constructor
	 *
	 */
	public function __construct()
	{
		add_action('uwp_template_before', array( $this, 'header_element' ) );
		add_action('uwp_template_after', array( $this, 'footer_element' ));
		add_action('uwp_template_form_title_before', array( $this, 'insert_element_before_title' ));
    add_filter( 'uwp_get_field_placeholder', array( $this, 'change_input_fields_placeholder'), 10, 2 );
    add_filter( 'uwp_template_fields', array( $this, 'append_to_form_fields'), 99, 1 );
    add_filter( 'uwp_template_fields', array( $this, 'append_to_forget_password_fields'), 1, 1 );
    add_filter( 'uwp_validate_result', array( $this, 'check_validation_results'), 10, 3 );
    add_filter( 'uwp_social_login_button_html', array( $this, 'social_login_button_html'), 10, 3);
    
    add_action('uwp_after_validate', array( $this, 'handle_after_password_reset_validation' ), 10, 3);
//    add_action('wp_footer', array( $this, 'add_footer_scripts'), 9999999999, 1);
    
    // hook into after template part to add the scripts that handles the forget page dynamically
    add_action('uwp_after_template_part', array ( $this, 'forget_password_scripts' ), 10, 4 );
    
    uwp_update_option('login_link_title', 'I have an account. <strong>Log in</strong>');
	}
	
	
	public function header_element($form_type) {
		ob_start();
	  $custom_logo_id = get_theme_mod( 'custom_logo' );
    
    $title = '';
    $subtitle = '';
    
    if($form_type == 'login') {
	    $title = __('Login', 'legal-trunk-core');
	    $subtitle = __('Hello, welcome back.', 'legal-trunk-core');
    }
    
    if($form_type == 'register') {
	    $title = __('Create Account', 'legal-trunk-core');
	    $subtitle = __('Start using Legal Trunk, no credit card required.', 'legal-trunk-core');
    }
    
    if($form_type == 'forgot') {
	    $title = __('Forgot Password', 'legal-trunk-core');
	    $subtitle = __('Please select option to send reset OTP.', 'legal-trunk-core');
    }
	  
	  if($form_type == 'reset' && !empty($_REQUEST['login'])) {
	    $user = !empty(sanitize_text_field($_REQUEST['login'])) ? get_user_by('login', sanitize_text_field($_REQUEST['login'])) : [];
	    $title = __('Reset Password', 'legal-trunk-core');
	    $subtitle = !empty($user) ? sprintf(__('Enter OTP sent to %s', 'legal-trunk-core'), legal_trunk_hide_email(sanitize_email($user->user_email)) ) : '';
	  }
   
		?>
		<div class="legal-trunk-core-auth-pages">
      <?php
      $status = !empty($_REQUEST['status']) && sanitize_text_field($_REQUEST['status']) == 'success';
      
      if($status)
      ?>
      <div class="auth-wrapper-container">
        <div class="auth-wrapper">
        <a class="site-logo" href="<?php echo get_home_url(); ?>">
	        <?php echo wp_get_attachment_image( $custom_logo_id ) ? wp_get_attachment_image( $custom_logo_id, 'fullsize' ) : '<span class="text-dark font-300">' . get_bloginfo( 'name' ) . '</span>'; ?>
        </a>
        <div class="title">
          <h2><?php echo $title; ?></h2>
          <p><?php echo $subtitle; ?></p>
        </div>
		<?php
		
		echo ob_get_clean();
	}
	
	
	public function footer_element($form_type) {
  
		ob_start();
		?>
        </div>
      </div>
      <?php if ( in_array($form_type, ['register', 'login'] ) ) { ?>
      <div class="auth-information">
        <?php echo $this->slide_items(); ?>
      </div>
      <?php } ?>
    </div>
		<?php
		echo ob_get_clean();
	}
  
  public function slide_items() {
    global $post;
    
    if(isset($post->id)) return;
    
    $sliders = get_field('sliders');
    
    if(!empty($sliders['slide_items'])) {
      
      ?>
        <div class="swiper">
        <div class="swiper-wrapper legal-trunk-slide-items">
        
      <?php
      
      $slide_items = $sliders['slide_items'];
      
      
      foreach ($slide_items as $item) {
        $image = wp_get_attachment_image($item['featured_image']['id'], 'fullsize');
        $title = $item['title'];
        $sub_text = $item['sub_text'];
        
        ?>
          <div class="swiper-slide legal-trunk-slide-item">
            <?php echo $image; ?>
            <div class="text-wrapper">
              <h2><?php echo esc_attr($title); ?></h2>
              <p><?php echo esc_attr($sub_text); ?></p>
            </div>
          </div>
        <?php
      }
      ?>
        </div>
          <div class="swiper-pagination"></div>
        </div>
      <?php
      }
  }
  
  public function insert_element_before_title($form_type) {
    
    ob_start();
    $signup_text = '';
    
    if($form_type == 'login') {
	    $signup_text = __('Or Sign in with Email', 'legal-trunk-core');
    }
    
    if($form_type == 'register') {
	    $signup_text = __('Or Sign up with Details', 'legal-trunk-core');
    }
    
    if( !empty($signup_text) ) {
    ?>
      <div class="head-divider">
        <hr />
        <span><?php echo $signup_text; ?></span>
      </div>
    <?php
    }
    
    echo ob_get_clean();
  }
  
  
  public function change_input_fields_placeholder($placeholder, $field) {
	  if (isset($field) && isset( $field->form_type ) && $field->form_type == 'login' && $field->htmlvar_name == 'username' ) {
	    $placeholder = __( 'moe@legaltrunk.com', 'legal-trunk-core' );
    }
	
	  if (isset($field) && isset( $field->form_type ) && $field->form_type == 'account' && $field->htmlvar_name == 'email' ) {
		  $placeholder = __( 'moe@legaltrunk.com', 'legal-trunk-core' );
	  }
	
	  if (isset($field) && isset( $field->form_type ) && $field->htmlvar_name == 'password' ) {
		  $placeholder = __( 'Min 8 Character', 'legal-trunk-core' );
	  }
	
	  if (isset($field) && isset( $field->form_type ) && $field->htmlvar_name == 'display_name' ) {
		  $placeholder = __( 'Moe Moe', 'legal-trunk-core' );
	  }
   
	  return $placeholder;
  }
  
  
  public function append_to_form_fields($form_type) {
    ob_start();
	
	  if($form_type == 'login') {
    ?>
      <div class="legal-trunk-forgotpassword uwp-forgotpsw">
        <a rel="nofollow" href="<?php echo uwp_get_forgot_page_url(); ?>"><?php echo uwp_get_option("forgot_link_title") ? uwp_get_option("forgot_link_title") : __( 'Forgot password?', 'userswp' ); ?></a>
      </div>
    <?php
    }
    
    echo ob_get_clean();
  }
  
  public function append_to_forget_password_fields($form_type) {
    ob_start();
    
    if($form_type == 'forgot') {
    ?>
    <div class="legal-trunk-resetpassword-options">
      <div class="form-group">
        <div class="radio-field-group">
          <input type="radio" id="reset-pass-option-via-email" value="via-email" class="form-control" name="reset-pass-option">
          <label class="radio-input-field-group" for="reset-pass-option-via-email">
            <div class="icon-wrapper">
              <svg xmlns="http://www.w3.org/2000/svg" width="38" height="38" fill="none"><path fill="#66339D" d="M6.685 6.842a3.043 3.043 0 0 0-3.04 3.04v18.237a3.043 3.043 0 0 0 3.04 3.04H31a3.043 3.043 0 0 0 3.04-3.04V9.88A3.043 3.043 0 0 0 31 6.841H6.685Zm0 3.04H31v.008l-12.158 7.59L6.685 9.887v-.006Zm0 3.045 12.158 7.593L31 12.93l.003 15.189H6.684V12.927Z"/></svg>
            </div>
            <div class="details">
              <h3><?php echo __('Reset Password via Email', 'legal-trunk-core'); ?></h3>
              <p><?php echo __('OTP will be sent to the Email registered with your account.', 'legal-trunk-core'); ?></p>
            </div>
          </label>
        </div>
        <div class="radio-field-group">
          <input type="radio" id="reset-pass-option-via-phone" value="via-phone" class="form-control" name="reset-pass-option">
          <label class="radio-input-field-group" for="reset-pass-option-via-phone">
            <div class="icon-wrapper">
              <svg xmlns="http://www.w3.org/2000/svg" width="38" height="38" fill="none"><path fill="#66339D" d="M6.685 6.842a3.043 3.043 0 0 0-3.04 3.04v18.237a3.043 3.043 0 0 0 3.04 3.04H31a3.043 3.043 0 0 0 3.04-3.04V9.88A3.043 3.043 0 0 0 31 6.841H6.685Zm0 3.04H31v.008l-12.158 7.59L6.685 9.887v-.006Zm0 3.045 12.158 7.593L31 12.93l.003 15.189H6.684V12.927Z"/></svg>
            </div>
            <div class="details">
              <h3><?php echo __('Reset Password via Phone Number', 'legal-trunk-core'); ?></h3>
              <p><?php echo __('OTP will be sent to the Phone Number registered with your account.', 'legal-trunk-core'); ?></p>
            </div>
          </label>
        </div>
      </div>
    </div>
      <div data-argument="phone" class="form-group">
        <label for="phone" class="sr-only  "><?php echo __('Phone Number', 'legal-trunk-wp'); ?> <span class="text-danger">*</span></label>
        <input type="tel" name="phone" id="phone" placeholder="<?php echo __('Phone Number*', 'legal-trunk-wp'); ?>" title="<?php echo __('Phone Number*', 'legal-trunk-wp'); ?>" required="" class="form-control uwp_reset_phone" autocomplete="off">
      </div>
    <?php
    }
    
    if($form_type == 'reset') {
      ?>
      <div data-argument="phone" class="form-group">
        <label for="uwp_otp_code-1" class="sr-only  "><?php echo __('Enter OTP', 'legal-trunk-wp'); ?> <span class="text-danger">*</span></label><br />
        <input type="text" name="uwp_otp_code-1" id="uwp_otp_code-1" required="" class="form-control uwp_otp_code" maxlength="1" data-next="uwp_otp_code-2">
        <input type="text" name="uwp_otp_code-2" id="uwp_otp_code-2" required="" class="form-control uwp_otp_code" maxlength="1" data-previous="uwp_otp_code-1" data-next="uwp_otp_code-3">
        <input type="text" name="uwp_otp_code-3" id="uwp_otp_code-3" required="" class="form-control uwp_otp_code" maxlength="1" data-previous="uwp_otp_code-2" data-next="uwp_otp_code-4">
        <input type="text" name="uwp_otp_code-4" id="uwp_otp_code-4" required="" class="form-control uwp_otp_code" maxlength="1" data-previous="uwp_otp_code-3" data-next="uwp_otp_code-5">
        <input type="text" name="uwp_otp_code-5" id="uwp_otp_code-5" required="" class="form-control uwp_otp_code" maxlength="1" data-previous="uwp_otp_code-4">
      </div>
      <?php
    }
    
    echo ob_get_clean();
  }
  
  public function check_validation_results($result, $form_type, $data) {
    if($form_type == 'forgot') {
        $data['email'] = 'email@example.com';
        $data['phone'] = '0';
	    if(!empty($_POST['reset-pass-option']) && $_POST['reset-pass-option'] == 'via-email') {
		    $data['email'] = sanitize_email($_POST['email']);
        
        // get the user by email
	      $user_data = get_user_by( 'email', $data['email'] );
        if(!empty($user_data)) {
          $user_id = $user_data->ID;
          $phone = get_user_meta($user_id, 'user_phone', true);
          $data['phone'] = $phone;
        }
	    } else if((!empty($_POST['reset-pass-option']) && $_POST['reset-pass-option'] == 'via-phone')) {
	      $data['phone'] = sanitize_text_field($_POST['phone']);
        
        // get the user by the phone meta field
	      $args = array(
            'fields' => 'user_email',
            'meta_query' => array (
			      array(
				      'key' => 'user_phone',
				      'value' => $data['phone'],
			      ),
		      )
	      );
        
        $users_data = get_users($args);
        if(!empty($users_data[0])) {
          $data['email'] = $users_data[0];
        }
	    }
     
	    $result = uwp_validate_fields( $data, 'forgot' );
    }
    
    //validate password reset
    if($form_type == 'reset') {
      // verify the otp
      $otp_length = 1;
      $otp = '';
      while($otp_length <= 5) {
        if(isset($_POST['uwp_otp_code-'.$otp_length])) {
	        $otp .= sanitize_text_field($_POST['uwp_otp_code-'.$otp_length]);
        } else {
          return new \WP_Error('invalid_otp', 'Otp codes are not complete. Please enter the correct otp.');
        }
        $otp_length++;
      }
      
      if(!empty($otp)) {
        $user = get_user_by('login', $data['uwp_reset_username']);
        if(!empty($user)) {
          $email = $user->user_email;
          
          $check_otp_codes = $this->check_otp_codes($otp, $email);
          if(!$check_otp_codes) {
	          return new \WP_Error('invalid_otp', 'OTP is invalid. Please enter the correct otp.');
          }
        }
      }
    }
    
    return $result;
  }
  
  
  public function social_login_button_html($btn_output, $provider_id, $url) {
	  if ( 'google' == strtolower( $provider_id ) ) {
	    $btn_output = "<a href=\"$url\" class='uwp_social_login_icon_google'>";
	    $btn_output .= '<img src="' . esc_url( LOCAL_TRUNK_CORE_SYSTEM_ASSETS_IMG_URL . '/sign-in-with-google.png' ) . '" alt="Sign in with Google" style="width: 100%;">';
	    $btn_output .= "</a>";
    }
    
    return $btn_output;
  }
  
  
  public function handle_after_password_reset_validation($result, $form_type, $data) {
    if($form_type == 'forgot') {
	    if ((!empty($_POST['reset-pass-option']) && $_POST['reset-pass-option'] == 'via-email')) {
        $this->verify_by_email($data);
        return;
      } else if ((!empty($_POST['reset-pass-option']) && $_POST['reset-pass-option'] == 'via-phone')) {
        // to do verification by phone
          $this->verify_by_phone($data);
        return;
      }
    }
  }
  
  public function forget_password_scripts($template_name, $template_path, $located, $args) {
    // handles modal popup for forget password
    if($template_name == 'bootstrap/forgot.php') {
      ob_start();
      ?>
      <script type="text/javascript">
          let reset_pass_options = document.querySelectorAll('.modal-dialog input[name="reset-pass-option"]');

          reset_pass_options.forEach((element, index) => {
              let email = document.querySelector('.modal-dialog div[data-argument="email"] input[type="email"]');
              let phone = document.querySelector('.modal-dialog div[data-argument="phone"] input[type="tel"]');

              email.removeAttribute('required');
              phone.removeAttribute('required');
              element.addEventListener('change', (event) => {
                  let value = event.target.value;
                  let email_el = document.querySelector('.modal-dialog div[data-argument="email"]');
                  let phone_el = document.querySelector('.modal-dialog div[data-argument="phone"]');
                  let email_submit_el = document.querySelector('.modal-dialog button[name="uwp_forgot_submit"]');

                  phone_el.style.display = 'none';
                  email_el.style.display = 'none';
                  email_submit_el.style.display = 'none';
                  email.removeAttribute('required');
                  phone.removeAttribute('required');
                  if(value === 'via-email') {
                      email_el.style.display = 'block';
                      email_submit_el.style.display = 'block';
                      email.setAttribute('required', true);
                  } else if (value === 'via-phone') {
                      phone_el.style.display = 'block';
                      email_submit_el.style.display = 'block';
                      phone.setAttribute('required', true);
                  }
              })
          })
      </script>
      
      <?php
      
      echo ob_get_clean();
    }
  }
  
  
  public function verify_by_email($data) {
	  $user_data = get_user_by( 'email', $data['email'] );
    
    if( ! $user_data ) {
	    $args = apply_filters('uwp_forgot_error_message', array(
		    'type'    => 'error',
		    'content' => __( 'Invalid email or user doesn\'t exists.', 'legal-trunk-core' )
	    ));
	
	    $message = aui()->alert( $args );
	    if ( wp_doing_ajax() ) {
		    wp_send_json_success( $message );
	    } else {
		    $uwp_notices[] = array( 'forgot' => $message );
		
		    return;
	    }
    }
	
	  // make sure user account is active before account reset
	  $mod_value = get_user_meta( $user_data->ID, 'uwp_mod', true );
	
	  if ( $mod_value == 'email_unconfirmed' ) {
		  $message = aui()->alert( array(
				  'type'    => 'error',
				  'content' => __( 'Your account is not activated yet. Please activate your account first.', 'userswp' )
			  )
		  );
		  if ( wp_doing_ajax() ) {
			  wp_send_json_error( $message );
		  } else {
			  $uwp_notices[] = array( 'forgot' => $message );
			
			  return;
		  }
	  }
	
	  $user_data = get_userdata( $user_data->ID );
	
	  $allow = apply_filters( 'allow_password_reset', true, $user_data->ID );
	
	  if ( ! $allow ) {
		  return false;
	  } else if ( is_wp_error( $allow ) ) {
		  return false;
	  }
	
	  $as_password = apply_filters( 'uwp_forgot_message_as_password', false );
	
	  global $wpdb, $wp_hasher;
	  $reset_link = '';
	
	  $otp = $this->generate_otp_codes($user_data->user_email);
   
	  if ( $as_password ) {
		  $new_pass = wp_generate_password( 12, false );
		  wp_set_password( $new_pass, $user_data->ID );
		  if ( ! uwp_get_option( 'change_disable_password_nag' ) ) {
			  update_user_meta( $user_data->ID, 'default_password_nag', true ); //Set up the Password change nag.
		  }
		  $message = '<p><b>' . __( 'Your login Information :', 'userswp' ) . '</b></p>';
		  $message .= '<p>' . sprintf( __( 'Username: %s', 'userswp' ), $user_data->user_login ) . "</p>";
		  $message .= '<p>' . sprintf( __( 'Password: %s', 'userswp' ), $new_pass ) . "</p>";
		
	  } else {
	    $key = wp_generate_password( 20, false );
	    do_action( 'retrieve_password_key', $user_data->user_login, $key );
     
		  if ( empty( $wp_hasher ) ) {
			  require_once ABSPATH . 'wp-includes/class-phpass.php';
			  $wp_hasher = new \PasswordHash( 8, true );
		  }
		  $hashed = $wp_hasher->HashPassword( $key );
		  $wpdb->update( $wpdb->users, array( 'user_activation_key' => time() . ":" . $hashed ), array( 'user_login' => $user_data->user_login ) );
		  $message    = '<p>' . __( 'You have requested for an OTP from our website for the following account:', 'userswp' ) . "</p>";
		  $message    .= home_url( '/' ) . "</p>";
		  $message    .= '<p>' . sprintf( __( 'Username: %s', 'userswp' ), $user_data->user_login ) . "</p>";
		  $message    .= '<p>' . __( 'Enter the OTP below to verify your account.', 'userswp' ) . "</p>";
	    $reset_page = uwp_get_page_id( 'reset_page', false );
	    if ( $reset_page ) {
	      $reset_link = add_query_arg( array(
		      'key'   => $key,
		      'login' => rawurlencode( $user_data->user_login ),
	      ), get_permalink( $reset_page ) );
      } else {
	      $reset_link = home_url( "reset?key=$key&login=" . rawurlencode( $user_data->user_login ), 'login' );
      }
	    $message .= '<h3>'.esc_html($otp). '</h3>';
	    $message    .= '<p>' . __( 'If this was by mistake, just ignore this email and nothing will happen.', 'userswp' ) . "</p>";
	  }
	
	  $email_vars = array(
		  'user_id'       => $user_data->ID,
		  'login_details' => $message,
		  'reset_link'    => $reset_link,
	  );
   
	  \UsersWP_Mails::send( $user_data->user_email, 'forgot_password', $email_vars );
	
	
	
	  do_action( 'uwp_after_process_forgot', $data );
	
	  $message = aui()->alert( array(
			  'type'    => 'success',
			  'content' => apply_filters( 'uwp_change_password_success_message', __( 'Please check your email.', 'userswp' ), $data )
		  )
	  );
    $content = apply_filters( 'uwp_change_password_success_message', __( 'Please check your email.', 'userswp' ), $data );
	
	  $reset_link = add_query_arg([
	    'forgot'  => $content
    ], $reset_link);
	  if ( wp_doing_ajax() ) {
		  wp_send_json_success( $message );
	  } else {
		  $uwp_notices[] = array( 'forgot' => $message );
	    wp_redirect($reset_link);
	    exit;
	  }
  }
	
	public function verify_by_phone($data) {
	  // get the user by the phone meta field
	  $args = array(
		  'fields' => ['user_email', 'ID'],
		  'meta_query' => array (
			  array(
				  'key' => 'user_phone',
				  'value' => $data['phone'],
			  ),
		  )
	  );
	  
	  $user_data = get_users($args);
    
    if( empty($user_data[0] ) ) {
	    $args = apply_filters('uwp_forgot_error_message', array(
		    'type'    => 'error',
		    'content' => __( 'Invalid phone number or user doesn\'t exists.', 'legal-trunk-core' )
	    ));
	
	    $message = aui()->alert( $args );
	    if ( wp_doing_ajax() ) {
		    wp_send_json_success( $message );
	    } else {
		    $uwp_notices[] = array( 'forgot' => $message );
		
		    return;
	    }
    }
    
    $user_data = $user_data[0];
	  
	  $data['email'] = $user_data->user_email;
		
		// make sure user account is active before account reset
		$mod_value = get_user_meta( $user_data->ID, 'uwp_mod', true );
		
		if ( $mod_value == 'email_unconfirmed' ) {
			$message = aui()->alert( array(
					'type'    => 'error',
					'content' => __( 'Your account is not activated yet. Please activate your account first.', 'userswp' )
				)
			);
			if ( wp_doing_ajax() ) {
				wp_send_json_error( $message );
			} else {
				$uwp_notices[] = array( 'forgot' => $message );
				
				return;
			}
		}
		
		$user_data = get_userdata( $user_data->ID );
		
		$allow = apply_filters( 'allow_password_reset', true, $user_data->ID );
		
		if ( ! $allow ) {
			return false;
		} else if ( is_wp_error( $allow ) ) {
			return false;
		}
		
		$as_password = apply_filters( 'uwp_forgot_message_as_password', false );
		
		global $wpdb, $wp_hasher;
		$reset_link = '';
		
		$otp = $this->generate_otp_codes($user_data->user_email);
		
		if ( $as_password ) {
			$new_pass = wp_generate_password( 12, false );
			wp_set_password( $new_pass, $user_data->ID );
			if ( ! uwp_get_option( 'change_disable_password_nag' ) ) {
				update_user_meta( $user_data->ID, 'default_password_nag', true ); //Set up the Password change nag.
			}
			$message = '<p><b>' . __( 'Your login Information :', 'userswp' ) . '</b></p>';
			$message .= '<p>' . sprintf( __( 'Username: %s', 'userswp' ), $user_data->user_login ) . "</p>";
			$message .= '<p>' . sprintf( __( 'Password: %s', 'userswp' ), $new_pass ) . "</p>";
			
		} else {
			$key = wp_generate_password( 20, false );
			do_action( 'retrieve_password_key', $user_data->user_login, $key );
			
			if ( empty( $wp_hasher ) ) {
				require_once ABSPATH . 'wp-includes/class-phpass.php';
				$wp_hasher = new \PasswordHash( 8, true );
			}
   
			$hashed = $wp_hasher->HashPassword( $key );
			
      $wpdb->update( $wpdb->users, array( 'user_activation_key' => time() . ":" . $hashed ), array( 'user_login' => $user_data->user_login ) );
			
      $reset_page = uwp_get_page_id( 'reset_page', false );
			if ( $reset_page ) {
				$reset_link = add_query_arg( array(
					'key'   => $key,
					'login' => rawurlencode( $user_data->user_login ),
				), get_permalink( $reset_page ) );
			} else {
				$reset_link = home_url( "reset?key=$key&login=" . rawurlencode( $user_data->user_login ), 'login' );
			}
    }
	  
	  $twilio = new Client($this->twillio_accounts_sid, $this->twillio_auth_token);
	  $message = $twilio->messages->create(esc_attr($data['phone']), // to
			  [
				  "body" => 'Your Legal Trunk Verification code is '.$otp,
				  "from" => $this->twillio_phone
			  ]
    );
		
		do_action( 'uwp_after_process_forgot', $data );
		
		$message = aui()->alert( array(
				'type'    => 'success',
				'content' => apply_filters( 'uwp_change_password_success_message', __( 'Please check your email.', 'userswp' ), $data )
			)
		);
		$content = apply_filters( 'uwp_change_password_success_message', __( 'Please check your email.', 'userswp' ), $data );
		
		$reset_link = add_query_arg([
			'forgot'  => $content
		], $reset_link);
		if ( wp_doing_ajax() ) {
			wp_send_json_success( $message );
		} else {
			$uwp_notices[] = array( 'forgot' => $message );
			wp_redirect($reset_link);
			exit;
		}
	}
  
  public function generate_otp_codes($key) {
    $manager = Otp(LOCAL_TRUNK_CORE_SYSTEM_SRC_DIRECTORY.'/library/otp-tmp');
	   
    $manager->digits(5); // To change the number of OTP digits
    
    $manager->expiry(30); // To change the mins until expiry
    
    return $manager->generate($key);
  }
  
  public function check_otp_codes($otp, $unique_secret) {
	  $manager = Otp(LOCAL_TRUNK_CORE_SYSTEM_SRC_DIRECTORY.'/library/otp-tmp');
    
    return $manager->digits(5)->expiry(30)->check($otp, $unique_secret);
  }
  
  
	
	/**
	 * @return Authentication
	 */
	public static function get_instance()
	{
		static $instance = null;
		
		if (is_null($instance)) {
			$instance = new self();
		}
		
		return $instance;
	}
}