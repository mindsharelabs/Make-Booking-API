<?php


add_action( 'rest_api_init',function () {

    register_rest_route('makesantafe/v1', '/login', [
      'methods' => WP_REST_Server::ALLMETHODS,
      'callback' => 'makesantafe_login',
      'permission_callback' => '__return_true',
    ]);
    register_rest_route('makesantafe/v1', '/userprofile', [
      'methods' => WP_REST_Server::ALLMETHODS,
      'callback' => 'makesantafe_user_profile',
      'permission_callback' => '__return_true',
    ]);
    register_rest_route('makesantafe/v1', '/userprofile_update', [
      'methods' => WP_REST_Server::ALLMETHODS,
      'callback' => 'makesantafe_update_profile',
      'permission_callback' => '__return_true',
      'args' => array(
        'options' => array(
          'description' => esc_html__( 'An array of user meta options where the key is a meta tag and the value to set the option.', 'mindshare' ),
          'type'        => 'Array',
          'required'    => true,
        ),
      )
    ]);

    register_rest_route('makesantafe/v1', '/create_reservation', [
      'methods' => WP_REST_Server::EDITABLE,
      'callback' => 'makesantafe_create_reservation',
      'permission_callback' => '__return_true',
      'args' => array(
        'product' => array(
          'description' => esc_html__( 'The ID of the bookable product', 'mindshare' ),
          'type'        => 'Int',
          'required'    => true,
        ),
        'start_date_time' => array(
          'description' => esc_html__( 'The specific start time for the reservation in Y-m-d H:i format', 'mindshare' ),
          'type'        => 'String',
          'required'    => true,
        ),
        'end_date_time' => array(
          'description' => esc_html__( 'The specific end time for the reservation in Y-m-d H:i format', 'mindshare' ),
          'type'        => 'String',
          'required'    => true,
        ),
      )
    ]);


});



/*
Using the email and password supplied, this function will attempt to log the user in. If the user is found and the password matches, the function will return a user_key. 
The user_key is a unique key that is used to identify the user in the API. The user_key is stored as user meta data and is used to authenticate the user in future requests.

The intent is that the user key is stored locally on the user's device and is used to authenticate the user in future requests. 
The user_key should be refreshed on a regular basis.

Request must include:
clientID: string, must match clientID in API
email: string, must be a valid email address
password: string, user supplied password

Return: array [(bool) success, (string) message];
*/
function makesantafe_login($request) {
  $params = $request->get_params();
  $email = $params['email'];
  $password = $params['password'];
  $clientID = $params['clientID'];
  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    wp_send_json_error( 'Invalid email address' );
  }

  $return = false;
  if(clientID === $clientID) :
    $user = get_user_by( 'email', $email );
    if(!$user) :
      $return = array(
        'success' => false,
        'message' => 'That email address could not be found.'
      );
    elseif ( $user && wp_check_password( $password, $user->data->user_pass, $user->ID) ) :
        if ( metadata_exists( 'user', $user->ID, 'user_key' ) ) {
            $user_key = get_user_meta( $user->ID, 'user_key', true);
        } else {
            $user_key = wp_generate_password( 32, true, false );
            add_user_meta( $user->ID, 'user_key', $user_key);
        }
       $return = array(
         'success' => true,
         'message' => 'Success!',
         'user_key' => $user_key
       );
    else :
       $return = array(
         'success' => false,
         'message' => 'Username and password do not match.'
       );
     endif;
  else :
    $return = array(
      'success' => false,
      'message'  => 'Client ID does not match.'
    );
  endif;
  wp_send_json($return);
}


/*
Gets the current user profile information by $userkey. The intent of this function is to return basic user information and user meta data. This function is intended to be used for a user profile page.

Request must include:
clientID: string, must match clientID in API
userKey: string, user meta established when account was created or when user logged in

Return (success): array [(int) userID, (bool) success, (array) user_meta];
Return (failure): array [(bool) success, (string) message];
user_meta: []
*/
function makesantafe_user_profile($request) {
  $params = $request->get_params();
  $clientID = (isset($params['clientID']) ? $params['clientID'] : false);
  $userKey = (isset($params['userKey']) ? $params['userKey'] : false);
  if(clientID === $clientID) :
    $return = false;
    $users = get_users(array(
      'meta_key' => 'user_key',
      'meta_value' => $userKey,
      'meta_compare' => '=',
    ));
    if(count($users) > 0) :
      if($users[0] && $userKey) :
        // $user_id = $users[0]->data->ID;
        $return = array(
          'userID' => $users[0]->data->ID,
          'success' => true,
          'name' => $users[0]->data->display_name,
          'user_info' => array(),
          'active_memberships' => array(),
          'billing_address' => array(),
          'public_profile' => array(),
          'reservations' => array(),
        );
      else :
        $return = array(
          'success' => false,
          'message' => 'No user found.'
        );
      endif;
    endif;
  else :
    $return = array(
      'success' => false,
      'message' => 'Client ID does not match.'
    );
  endif;
  wp_send_json($return);
}


/*
Updates the current user profile information by $userkey. The intent of this function is to update user meta data. This function is intended to be used to edit basic user information. 

Request must include:
clientID: string, must match clientID in API
userKey: string, user meta established when account was created or when user logged in

Return (success): array [changed_options];
Return (failure): array [(bool) success, (string) message];
user_meta: []
*/
function makesantafe_update_profile($request) {
  $params = $request->get_params();
  if(clientID === $params['clientID']) :
    $return = null;
    $userKey = $params['userKey'];
    $options = $params['options'];
    // mapi_write_log($options);
    $users = get_users(array(
      'meta_key' => 'user_key',
      'meta_value' => $userKey,
      'meta_compare' => '=',
    ));
    if(count($users) > 0) :
      if($users[0] && $userKey) :
        $return = array();
        $available_options = array(
            //TODO: Add available options
        );
        foreach ($options as $key => $value) :
          if($value === 'false') {
            $value = false;
          } elseif($value === 'true') {
            $value = true;
          }
          if(in_array($key, $available_options)) :
            // update_field($key, $value, 'user_' . $users[0]->data->ID);
            update_user_meta( $users[0]->data->ID, $key, $value);
            // $return[$key] = get_field($key, 'user_' . $users[0]->data->ID);
            $return[$key] = get_user_meta( $users[0]->data->ID, $key, true);
          else :
            $return[$key] = 'This is not an updatatable option';
          endif;
        endforeach;
      else :
        $return = array(
          'success' => false,
          'message' => 'No user found.'
        );
      endif;
    endif;
  else :
    $return = array(
      'success' => false,
      'message' => 'Client ID does not match.'
    );
  endif;
  wp_send_json($return);
}





function makesantafe_create_reservation($request) {
  $params = $request->get_params();
  if(clientID === $params['clientID']) :
    $return = null;
    $userKey = $params['userKey'];
    $product = $params['product'];
    $start_date_time = $params['start_date_time'];
    $end_date_time = $params['end_date_time'];
    // mapi_write_log($options);
    $users = get_users(array(
      'meta_key' => 'user_key',
      'meta_value' => $userKey,
      'meta_compare' => '=',
    ));
    if(count($users) > 0) :
      if($users[0] && $userKey) :
        $booking_data = [
          'product_id'   => $product,
          'start_date'   => strtotime($start_date_time),
          'end_date'     => strtotime($end_date_time),
          'customer_id'  => $users[0],
          'status'       => 'confirmed',
        ];
      
        $booking_id = wc_bookings_create_booking($booking_data);
        $return = array(
          'success' => true,
          'message' => 'Booking created.',
          'booking_id' => $booking_id
        );
      else :
        $return = array(
          'success' => false,
          'message' => 'No user found.'
        );
      endif;
    endif;
  else :
    $return = array(
      'success' => false,
      'message' => 'Client ID does not match.'
    );
  endif;
  wp_send_json($return);
}