<?php
/**
 * Plugin Name: Make Santa Fe Reservations API
 * Plugin URI:https://mind.sh/are
 * Description: A plugin that adds endpoints to support a reservations app
 * Version: 0.0.1
 * Author: Mindshare Labs, Inc
 * Author URI: https://mind.sh/are
 */


 class makeReservations {
   private $userID = '';


   public function __construct() {
      if ( !defined( 'MAKERES_PLUGIN_FILE' ) ) {
      define( 'MAKERES_PLUGIN_FILE', __FILE__ );
      }
      //Define all the constants
      $this->define( 'MAKERES_ABSPATH', dirname( MAKERES_PLUGIN_FILE ) . '/' );
      $this->define( 'MAKERES_URL', plugin_dir_url( __FILE__ ));
      $this->define( 'MAKERES_PLUGIN_VERSION', '1.4.0');
      $this->define( 'PLUGIN_DIR', plugin_dir_url( __FILE__ ));


      $reservations_options = get_option( 'make_reservation_option' );
      $this->define( 'CLIENT_ID', (isset($reservations_options['client_id']) ? $reservations_options['client_id'] : null) );

      
      $this->includes();



 	}
  public static function get_instance() {
    if ( null === self::$instance ) {
      self::$instance = new self;
    }
    return self::$instance;
  }
  private function define( $name, $value ) {
      if ( ! defined( $name ) ) {
        define( $name, $value );
      }
    }
  private function includes() {
       include MAKERES_ABSPATH . 'inc/api-endpoints.php';
       include MAKERES_ABSPATH . 'inc/options.php';
       include MAKERES_ABSPATH . 'inc/utilities.php';
  }






}//end of class


new makeReservations();







register_activation_hook( __FILE__, function() {
  //TODO: Create userkey for all users
  return null;
});




register_deactivation_hook( __FILE__, function() {
  //TODO: Delete userkey for all users
  return null;
});
