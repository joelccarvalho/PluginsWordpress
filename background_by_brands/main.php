<?php defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
  /**
  * Plugin Name: Background brands
  * Description: Different backgrounds by brands
  * Version: 1.0
  * Author: Joel Carvalho
  **/


  // echo '<pre>'; var_dump(in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ), class_exists( 'WooCommerce' ));die;


  // Caminho
  define('URL_PATH', plugin_dir_url(__FILE__));

  // Se estiver em loja
  if (strpos($_SERVER["REQUEST_URI"],'shop') || strpos($_SERVER["REQUEST_URI"],'loja')) {

    // Se tiver filtrado uma marca
   //if (isset($_GET) && isset($_GET['product_tag'])) {

      add_action('wp_enqueue_scripts','call_js');

      // Chamar ficheiro js
      function call_js(){
        wp_enqueue_script('mysite_js', URL_PATH. 'js/mysite.js', array('jquery'));
         //wp_enqueue_script('woof_front', 'http://andebol7.wp.dev/wp-content/plugins/woocommerce-products-filter/js/front.js', array('jquery'));
        // wp_enqueue_script('aateste_js', WP_CONTENT_URL. '/plugins/woocommerce-products-filter/js/front.js', array('jquery'));
      }
    //}
  }





