<?php
  /**
  * Plugin Name: EndPoint Voucher
  * Description: Show vouchers on my account
  * Version: 1.0
  * Author: Joel Carvalho
  **/

  define('PATH_CSS',plugins_url(  "/", __FILE__));

  add_action('wp_head','function_styles');

  function function_styles(){
    wp_enqueue_style('style', PATH_CSS. "css/styles.css");
  }

  /*
   * Registar endpoint
   */
  add_action( 'init', 'add_endpoint_voucher' );
  function add_endpoint_voucher() {
    add_rewrite_endpoint( 'vouchers', EP_ROOT | EP_PAGES );
  }

  /*
   * Adicionar tab sobre o clube nos detalhes de conta
   */
  add_filter ( 'woocommerce_account_menu_items', 'vouchers_link');
  function vouchers_link( $menu_links ){

    $menu_links = array_slice( $menu_links, 0, 2, true ) // Definir posição lateral
    + array( 'vouchers' => 'Vouchers' )
    + array_slice( $menu_links, 2, NULL, true );

    return $menu_links;
  }

  function pagination_vouchers($current_user, $info_user){

    $args = array(
      'posts_per_page'   => -1,
      'orderby'          => 'title',
      'order'            => 'asc',
      'post_type'        => 'shop_coupon',
      'post_status'      => 'publish',
    );

    $roles    = $info_user->roles;
    $vouchers = get_posts($args);

    foreach ($vouchers as $voucher) {
      $help_ids   = false;
      $help_roles = false;

      $check_voucher_by_user_ids = get_post_meta($voucher->ID, '_wjecf_customer_ids'); // Pegar todos os vouchers restritos por id

      if(!empty($check_voucher_by_user_ids)) {

        foreach ($check_voucher_by_user_ids as $cvbui) {

          $list_vouchers_ids = explode( ',', $cvbui); // Percorrer array vírgula a vírgula

          foreach ($list_vouchers_ids as $lvi) {

            if ($lvi == $current_user) {
              $check_if_was_used = get_post_meta($voucher->ID, '_used_by');
              $was_used          = false;

              foreach ($check_if_was_used as $k => $ciwu) {
                if ($ciwu == $current_user) {
                  $was_used = true;
                  break;
                }
              }
              $valid_vouchers[]  = array('name' => $voucher->post_title, 'used' => $was_used);
            }
          }
        }
      } else { // Vouchers livres
        $help_ids = true;
      }

      $check_voucher_by_user_roles = get_post_meta($voucher->ID, '_wjecf_customer_roles'); // Pegar todos os vouchers restritos por id

      if(!empty($check_voucher_by_user_roles)) {

        foreach ($check_voucher_by_user_roles[0] as $key => $cvbur) {
          foreach ($roles as $key_role => $role) {
            if ($cvbur == $role) {
              $check_if_was_used = get_post_meta($voucher->ID, '_used_by');
              $was_used          = false;

              foreach ($check_if_was_used as $k => $ciwu) {
                if ($ciwu == $current_user) {
                  $was_used = true;
                  break;
                }
              }
              $valid_vouchers[] = array('name' => $voucher->post_title, 'used' => $was_used);
            }
          }
        }
      } else { // Vouchers livres
        $help_roles = true;
      }

      if ($help_ids && $help_roles) {
        $check_if_was_used = get_post_meta($voucher->ID, '_used_by');
        $was_used          = false;

        foreach ($check_if_was_used as $k => $ciwu) {
          if ($ciwu == $current_user) {
            $was_used = true;
            break;
          }
        }
        $valid_vouchers[] = array('name' => $voucher->post_title, 'used' => $was_used);
      }
    }

    return $valid_vouchers;
  }

  /*
   * Conteúdo
   */
  add_action( 'woocommerce_account_vouchers_endpoint', 'content_vouchers' );
  function content_vouchers() {
    global $wpdb;

    $current_user = wp_get_current_user();

    $all_vouchers = pagination_vouchers($current_user->ID, get_userdata($current_user->ID));

    if(!empty($all_vouchers)): ?>
      <h1>Vouchers</h1>
      <table class="woocommerce-MyAccount-orders shop_table shop_table_responsive my_account_orders account-orders-table">
          <thead class="style-thead">
            <tr>
              <th><?php echo __('Nome', 'endpoint-vouchers'); ?></th>
              <th><?php echo __('Estado', 'endpoint-vouchers'); ?></th>
            </tr>
          </thead>
          <tbody>
              <?php foreach ($all_vouchers as $key => $value): ?>
                  <tr>
                    <td><?php echo $value['name'] ?></td>
                    <td><div <?php echo ($value['used'] ? 'class=voucher_was_used' : 'class=voucher_not_used'); ?>></div></td>
                  </tr>
              <?php endforeach; ?>
          </tbody>
      </table>
    <?php else: ?>
      <h3><?php echo __('Sem vouchers!', 'points-for-woocommerce'); ?></h3>
    <?php endif;
  }
