<?php
/*
** Plugin Name: Woocommerce: pontos e descontos
** Description: Acumula pontos em função da compras. Multisite e permite criação de grupos.
** Versao: 1.0
** Author: Joel Carvalho
**/

if ( ! defined( 'ABSPATH' ) ) exit;
    // Carregar todos os plugins
    // add_action('init','do_stuff');
    // function do_stuff(){
    //     // Verificar se o utilizador tem permissões para manusear os protocolos
    //     $current_user = wp_get_current_user();
    //     if ($current_user->roles[0] == 'editor') {
    //         // wp_die( __('Não tem permissões para aceder a esta página.') );
    //     }
    // }

    define('PHOEN_REWPTSPLUGURL',plugins_url(  "/", __FILE__));
    define('PHOEN_REWPTSPLUGPATH',plugin_dir_path(  __FILE__));
    define('POST_PER_PAGE', '15');
    define('GLOBAL_PREFIX', 'wp_');
    // define('GLOBAL_PREFIX', 'a7ptwp_');

    function phoe_rewpts_menu_booking() {
        add_menu_page('Protocolos',__( 'Gerir Procolos', 'phoen-rewpts') , 'nosuchcapability','Phoeniixx_reward_settings','Phoeniixx_reward_settings_func', 'dashicons-money' ,'57.1');
        add_submenu_page('Phoeniixx_reward_settings', 'Dinheiro Clubes', 'Dinheiro Clubes', 'manage_options', 'amount_admin', 'clubs_total_amount');
        add_submenu_page('Phoeniixx_reward_settings', 'Pontos Jogadores', 'Pontos Jogadores', 'manage_options', 'groups_user_pts', 'groups_user_pts');
        add_submenu_page('Phoeniixx_reward_settings', 'Pontos Utilizadores', 'Pontos Utilizadores', 'manage_options', 'simple_user_pts', 'simple_user_pts');
    }

    add_action('admin_menu', 'phoe_rewpts_menu_booking');
    add_action('wp_head','phoen_rewpts_frontend_func');
    add_action('admin_head','phoen_rewpts_backend_func');

    function phoen_rewpts_frontend_func(){
        include_once(PHOEN_REWPTSPLUGPATH.'includes/phoen_rewpts_frontend.php');
        wp_enqueue_style('phoen_rewpts_frontend', PHOEN_REWPTSPLUGURL. "assets/css/phoen_rewpts_frontend.css");
    }

    function phoen_rewpts_backend_func(){
        wp_enqueue_style('phoen_rewpts_backend_func_css', PHOEN_REWPTSPLUGURL. "assets/css/phoen_rewpts_backend.css");
        wp_enqueue_script('phoen_rewpts_backend_func_js', PHOEN_REWPTSPLUGURL. "assets/js/phoen_rewpts_backend.js");
        wp_enqueue_style('wp-color-picker');
        wp_enqueue_script('wp-color-picker');
        wp_enqueue_script('autocomplete_js', PHOEN_REWPTSPLUGURL. 'assets/js/jquery.auto-complete.js');
        wp_enqueue_script('mysite_js', PHOEN_REWPTSPLUGURL. 'assets/js/mysite.js');
    }

    function clubs_total_amount()
    {
        include_once(PHOEN_REWPTSPLUGPATH.'includes/list_amount_admin.php');
    }

    function simple_user_pts()
    {
        include_once(PHOEN_REWPTSPLUGPATH.'includes/list_pts_simple_user.php');
    }

    function groups_user_pts()
    {
        include_once(PHOEN_REWPTSPLUGPATH.'includes/list_pts_members_groups.php');
    }

    // Setting Tab
    function Phoeniixx_reward_settings_func()   { ?>
        <div id="profile-page" class="wrap">
            <?php
                if(isset($_GET['tab']))
                {
                    $tab = sanitize_text_field( $_GET['tab'] );
                }
                else
                {
                    $tab = "";
                }
            ?>
            <h2> <?php _e('Protocolos','phoen-rewpts'); ?></h2>

            <?php $tab = (isset($_GET['tab']))?$_GET['tab']:'';?>

            <h2 class="nav-tab-wrapper woo-nav-tab-wrapper">

                <!-- DESCOMENTAR PARA PERSONALIZAR BOTÕES DE APLICAR PONTOS NO CARRINHO -->
                <!-- <a class="nav-tab <?php if($tab == 'phoen_rewpts_styling'){ echo esc_html( "nav-tab-active" ); } ?>" href="?page=Phoeniixx_reward_settings&amp;tab=phoen_rewpts_styling"><?php _e('Styling','phoen-rewpts'); ?></a> -->

                <a class="nav-tab <?php if($tab == 'new_group' || $tab == ''){ echo esc_html( "nav-tab-active" ); } ?>" href="?page=Phoeniixx_reward_settings&amp;tab=new_group"><?php _e('Adicionar Clube','phoen-rewpts'); ?></a>

                <a class="nav-tab <?php if($tab == 'phoen_rewpts_list_groups'){ echo esc_html( "nav-tab-active" ); } ?>" href="?page=Phoeniixx_reward_settings&amp;tab=phoen_rewpts_list_groups"><?php _e('Lista dos clubes','phoen-rewpts'); ?></a>

                <a class="nav-tab <?php if($tab == 'phoen_rewpts_add_members_groups'){ echo esc_html( "nav-tab-active" ); } ?>" href="?page=Phoeniixx_reward_settings&amp;tab=phoen_rewpts_add_members_groups"><?php _e('Adicionar jogadores','phoen-rewpts'); ?></a>

            </h2>

        </div>

        <?php
        // if($tab == 'phoen_rewpts_styling'){
        //     include_once(PHOEN_REWPTSPLUGPATH.'includes/phoeniixx_reward_styling.php');
        // }
        if($tab == 'new_group' || $tab == ''){
            include_once(PHOEN_REWPTSPLUGPATH.'includes/phoeniixx_reward_groups.php');
        }
        if($tab == 'phoen_rewpts_list_groups'){
            include_once(PHOEN_REWPTSPLUGPATH.'includes/list_groups.php');
        }
        if($tab == 'phoen_rewpts_add_members_groups'){
            include_once(PHOEN_REWPTSPLUGPATH.'includes/add_members_group.php');
        }
        if($tab == 'list_members'){
            include_once(PHOEN_REWPTSPLUGPATH.'includes/list_members.php');
        }
        if($tab == 'club_edit'){
            include_once(PHOEN_REWPTSPLUGPATH.'includes/club_edit.php');
        }
    }

    // shows message on cart for apply or remove reward points
    function phoen_rewpts_action_woocommerce_before_cal_table() {
        global $woocommerce; global $wpdb;

        $current_user = wp_get_current_user();
        $user         = check_type_user($current_user->ID);
        $curr         = get_woocommerce_currency_symbol();

        // Verificar se é admin de um grupo
        if ($user === 'admin') {

            $table = GLOBAL_PREFIX.'groups_users_details';

            $wpdb->get_results("SELECT total_amount FROM $table WHERE user_id = $current_user->ID");
            $total_amount = $wpdb->last_result[0]->total_amount;

            // Se  tiver dinheiro acumulado
            if($total_amount > 0):?>
                <div class="phoen_rewpts_pts_link_div_main">
                <?php

                $current_lang = getCurrentLang();

                if ($current_lang === 'EN') {
                    echo "<div class='phoen_rewpts_redeem_message_on_cart text_discount'>Do you want to apply ".$total_amount.$curr." discount?</div>";
                    $btn_apply  = 'Apply discount';
                    $btn_remove = 'Remove discount';
                }
                elseif ($current_lang === 'PT') {
                    echo "<div class='phoen_rewpts_redeem_message_on_cart text_discount'>Deseja aplicar ".$total_amount.$curr." de desconto?</div>";
                    $btn_apply  = 'Aplicar desconto';
                    $btn_remove = 'Remover desconto';
                }

                $apply_btn_title    = $btn_apply;
                $remove_btn_title   = $btn_remove;

                ?>
                <div class="phoen_rewpts_pts_link_div">
                    <form method="post" action="">
                        <input type="submit" class="my_button"  value="<?php echo $apply_btn_title; ?>" name="apply_points">&nbsp;
                        <input type="submit" class="my_button"  value="<?php echo $remove_btn_title; ?>" name="remove_points">
                    </form>
                </div>
             </div>
            <?php endif;
        }
        elseif ($user === 'member')
        {
            global $wpdb;

            add_action('woocommerce_cart_calculate_fees', 'apply_discount_player', 10, 1);
        }
    }

    // save data in post meta when click on checkout in order page
    function phoen_rewpts_click_on_checkout_action($order_id){

        $current_user = wp_get_current_user();
        $user         = check_type_user($current_user->ID);

        // Administradores não ganham nada com as suas compras
        if($user === 'admin') {

            // Se aplicou os pontos para desconto, atualizar tabela dos pontos
            if(isset($_SESSION['action']) && $_SESSION['action'] == "apply"){
                update_used_amount($current_user->ID, $order_id);
            }
        }

        session_destroy();
    }

    // add and display reward points to total if click on remove points
    function phoen_rewpts_woo_add_cart_fee() {
       if(isset($_SESSION['action']) && $_SESSION['action'] == "apply") {

            global $woocommerce;
            global $wpdb;
            global $cart;

            $curr         = get_woocommerce_currency_symbol();
            $current_user = wp_get_current_user();
            $table        = GLOBAL_PREFIX.'groups_users_details';

            $wpdb->get_results("SELECT total_amount FROM $table WHERE user_id = $current_user->ID");
            $amount_total = $wpdb->last_result[0]->total_amount;

            $current_lang = getCurrentLang();

            if ($current_lang === 'EN') {
                $text = 'Discount';
            }
            elseif ($current_lang === 'PT') {
                $text = 'Desconto';
            }
            $woocommerce->cart->add_fee( __($text, 'woocommerce'), "-".$amount_total.$curr);
        }
    }

    // Aplicar desconto se for jogador
    function apply_discount_player(){

        $current_user = wp_get_current_user();
        $user         = check_type_user($current_user->ID);
        $curr         = get_woocommerce_currency_symbol();

        if ($user === 'member') {
            global $woocommerce;
            global $wpdb;
            $table_gm = GLOBAL_PREFIX.'groups_members';
            $table_g  = GLOBAL_PREFIX.'groups';

            $wpdb->get_results("SELECT group_id FROM $table_gm WHERE member_id = $current_user->ID");
            $group_id = $wpdb->last_result[0]->group_id;

            $wpdb->get_results("SELECT discount_user FROM $table_g WHERE id = $group_id");
            $discount_user = $wpdb->last_result[0]->discount_user;
            $discount_user = bcdiv($discount_user, 100, 4);
            $price_total = $woocommerce->cart->cart_contents_total;

            $woocommerce->cart->add_fee( __('Discount', 'woocommerce'), "-".bcmul($price_total, $discount_user, 4).$curr);
        }
    }

    // shows number of points to get on cart page
    function phoen_rewpts_action_get_reward_points() {
        global $woocommerce;

        $bill_price   = $woocommerce->cart->total;
        $shipping     = $woocommerce->cart->shipping_total;
        $current_user = wp_get_current_user();
        $user         = check_type_user($current_user->ID);
        $current_lang = getCurrentLang();

        // Só mostra a informação da quantidade de pontos a ganhar se for utilizador utilizador normal ou jogadores
        if($user != 'admin'){
            if($bill_price > 0) {
                if ($current_lang === 'EN') {
                    echo "<div class='phoen_rewpts_reward_message_on_cart'>You will get ".round(bcsub($bill_price, $shipping, 3))." points on completing this order.</div>";
                }
                elseif ($current_lang === 'PT') {
                    echo "<div class='phoen_rewpts_reward_message_on_cart'>Ao completar esta encomenda, acumulará ".round(bcsub($bill_price, $shipping, 3))." pontos.</div>";
                }
            }
        }
    }

    //remove reward points from total if click on remove points
    function phoeniixx_rewpts_remove_fee_from_cart()
    {
        if(isset($_POST['remove_points'])) {
            remove_action( 'woocommerce_cart_calculate_fees','phoen_rewpts_woo_add_cart_fee',10,1);

            $_SESSION['action'] = "remove";
        }
    }

    //add reward points to total if click on remove points
    function phoeniixx_rewpts_add_fee_from_cart()
    {
        if(isset($_POST['apply_points']))   {
            add_action( 'woocommerce_cart_calculate_fees', 'phoen_rewpts_woo_add_cart_fee', 10, 1);

            $_SESSION['action'] = "apply";
        }
    }

    session_start();

    if(isset($_SESSION['action']) && $_SESSION['action'] == "remove")
    {
        // remove reward points from  order or review order page when remove points click is done on cart page
        remove_action( 'woocommerce_cart_calculate_fees','phoen_rewpts_woo_add_cart_fee',10,1);
    }

    if(isset($_SESSION['action']) && $_SESSION['action'] == "apply")
    {
        // add reward points to  order or review order page when apply points click is done on cart page
        add_action( 'woocommerce_cart_calculate_fees', 'phoen_rewpts_woo_add_cart_fee', 10, 1);
    }

    add_action( 'woocommerce_cart_calculate_fees', 'apply_discount_player', 10, 1);
    // add reward to cart page
    add_action( 'init', 'phoeniixx_rewpts_add_fee_from_cart', 2);
    //remove rewards from cart
    add_action( 'init', 'phoeniixx_rewpts_remove_fee_from_cart', 2);
    // save data in post meta when click on checkout in order page
    add_action( 'woocommerce_checkout_order_processed', 'phoen_rewpts_click_on_checkout_action',  1, 1  );
    //show message to add or remove rewards descomentar para aparecer botoes de aplicar desconto
    add_action( 'woocommerce_before_cart', 'phoen_rewpts_action_woocommerce_before_cal_table', 10, 0);
    // shows number of points to get on cart page
    add_action( 'woocommerce_after_cart_table', 'phoen_rewpts_action_get_reward_points', 10, 0);

function total_pts_user($id) {
    global $wpdb;
    $table = GLOBAL_PREFIX.'groups_users_details';

    $t = $wpdb->get_results("SELECT total_pts FROM $table WHERE user_id = $id");

    return $t[0]->total_pts;
}

function update_used_amount($user_id, $order_id) {

    global $wpdb;
    $table          = GLOBAL_PREFIX.'groups_users_details';

    $order_details  = wc_get_order($order_id);

    foreach( $order_details->get_items('fee') as $item_id => $item_fee ){
        $fee_total  = $item_fee->get_total();
    }

    $total_amount   = $wpdb->get_results("SELECT total_amount FROM $table WHERE user_id = $user_id");
    $total_amount   = bcadd($total_amount[0]->total_amount, $fee_total, 3);
    $total_amount   = ($total_amount < 0 ? 0 : $total_amount);

    $wpdb->get_results("UPDATE $table SET total_amount = $total_amount WHERE user_id = $user_id");
}

function amount_total_admin($id)
{
    global $wpdb;
    $table = GLOBAL_PREFIX.'groups_users_details';

    $t = $wpdb->get_results("SELECT total_amount FROM $table WHERE user_id = $id");

    return $t[0]->total_amount;
}

// Mostrar nº de pts ou total acumulado nos detalhes de conta
function my_custom_endpoint_content() {
    $curr         = get_woocommerce_currency_symbol();
    $current_user = wp_get_current_user();
    $user         = check_type_user($current_user->ID);
    $current_lang = getCurrentLang();

    if ($user === 'admin') {
        $total = amount_total_admin($current_user->ID);

        if ($current_lang === 'EN') {
            echo '<h5>Total money won with players: '.$total.$curr.'</h5>';
        }
        elseif ($current_lang === 'PT') {
            echo '<h5>Dinheiro acumulado com os jogadores: '.$total.$curr.'</h5>';
        }
    }
    else
    {
        $pts = total_pts_user($current_user->ID);

        if ($current_lang === 'EN') {
            echo '<h5>You have '.(empty($pts) ? '0' : $pts).' points.</h5>';
        }
        elseif ($current_lang === 'PT') {
            echo '<h5>Tem um acumulado de '.(empty($pts) ? '0' : $pts).' pontos.</h5>';
        }
    }
}

add_action('woocommerce_account_players_endpoint', 'my_custom_endpoint_content');


add_action( 'save_post', 'save_order' );

function save_order($post_id) {

  $order = wc_get_order($post_id);

  global $wpdb;
  $table = GLOBAL_PREFIX.'groups_users_details';

  if ($order != false) {
    // Calcular informação da encomenda
    $total_spent        = bcsub($order->total, $order->shipping_total, 3);
    $points_earned      = $total_spent;
    $user_order         = $order->get_user();
    $user_id            = $user_order->data->ID;

    if (!is_null($user_id)) {
        $user_info  = check_type_user($user_id);

        // Se ainda não fez nenhuma encomenda
        $details = $wpdb->get_results("SELECT * FROM $table WHERE user_id = $user_id");

        if (empty($details)) {
            if ($user_info === 'without group') {
                insert_user($user_id, 'simple');
            }
            elseif ($user_info === 'admin'){
                insert_user($user_id, 'admin');
            }
            elseif ($user_info === 'member') {
                insert_user($user_id, 'player');
            }
        }

        // Encomendas concluídas
        if ($order->get_status() === 'completed') {
            // Cruzar dados da bd com os dados desta encomenda
            update_user_details($user_id, $total_spent, $points_earned, $user_info, 'completed');
        }
        // Encomendas canceladas
        elseif ($order->get_status() === 'cancelled') {
            // Cruzar dados da bd com os dados desta encomenda
            update_user_details($user_id, $total_spent, $points_earned, $user_info, 'cancelled');
        }
      }
    }
}

function insert_user($id, $type){
    global $wpdb;
    $table = GLOBAL_PREFIX.'groups_users_details';

    if ($type === 'simple') {
        $wpdb->query
        (
            $wpdb->prepare
            (
                "INSERT INTO $table
                (`user_id`, `perfil`)
                VALUES (%d, %s);
                ",
                array
                (
                    $id,
                    $type
                )
            )
        );
    }
    else {

        $group_id = check_group_id($id, $type);

        $wpdb->query
        (
            $wpdb->prepare
            (
                "INSERT INTO $table
                (`user_id`, `group_id`, `perfil`)
                VALUES (%d, %d, %s);
                ",
                array
                (
                    $id,
                    $group_id,
                    $type
                )
            )
        );
    }
}

function update_user_details($id, $total_spent, $points_earned, $type, $status){
    global $wpdb;
    $table   = GLOBAL_PREFIX.'groups_users_details';
    $table_g = GLOBAL_PREFIX.'groups';

    // Encomenda completa
    if ($status === 'completed') {
        $details      = $wpdb->get_results("SELECT * FROM $table WHERE user_id = $id");
        $total_orders = bcadd($details[0]->total_orders, 1, 2);

        if ($type === 'member') {
            // Encontrar id do grupo e atualizar o total de dinheiro acumulado do admin
            $group_id     = check_group_id($id, $type);
            $wpdb->get_results("SELECT margin FROM $table_g WHERE id = $group_id");
            $margin       = $wpdb->last_result[0]->margin;
            $margin       = bcdiv($margin, 100, 4);
            $total_amount = bcmul($total_spent, $margin, 4);
            $admin_id     = check_admin($group_id);

            // Ver o total acumulado do clube e adicionar montante desta encomenda
            $data         = $wpdb->get_results("SELECT * FROM $table WHERE user_id = $admin_id");
            $total_amount = bcadd($data[0]->total_amount, $total_amount, 4);

            $wpdb->get_results("UPDATE $table SET total_amount = $total_amount WHERE user_id = $admin_id");
        }

        $total_spent = bcadd($details[0]->total_spent, $total_spent, 2);

        if ($type === 'admin') {
            $wpdb->get_results("UPDATE $table SET total_orders = $total_orders, total_spent = $total_spent WHERE user_id = $id");
        }
        else {
            $total_pts = round(bcadd($details[0]->total_pts, $points_earned, 2));
            $wpdb->get_results("UPDATE $table SET total_pts = $total_pts, total_orders = $total_orders, total_spent = $total_spent WHERE user_id = $id");
        }
    }
    // Encomenda cancelada
    elseif ($status === 'cancelled') {
        $details      = $wpdb->get_results("SELECT * FROM $table WHERE user_id = $id");
        $total_orders = bcsub($details[0]->total_orders, 1, 2);

        if ($type === 'member') {

            // Encontrar id do grupo e atualizar o total de dinheiro acumulado do admin
            $group_id     = check_group_id($id, $type);
            $wpdb->get_results("SELECT margin FROM $table_g WHERE id = $group_id");
            $margin       = $wpdb->last_result[0]->margin;
            $margin       = bcdiv($margin, 100, 4);
            $total_amount = bcmul($total_spent, $margin, 4);
            $admin_id     = check_admin($group_id);

            // Ver o total acumulado do clube e adicionar montante desta encomenda
            $data         = $wpdb->get_results("SELECT * FROM $table WHERE user_id = $admin_id");
            $total_amount = bcsub($data[0]->total_amount, $total_amount, 4);
            $wpdb->get_results("UPDATE $table SET total_amount = $total_amount WHERE user_id = $admin_id");
        }

        $total_spent = bcsub($details[0]->total_spent, $total_spent, 2);

        if ($type === 'admin') {
            $wpdb->get_results("UPDATE $table SET total_orders = $total_orders, total_spent = $total_spent WHERE user_id = $id");
        }
        else {
            $total_pts = round(bcsub($details[0]->total_pts, $points_earned, 2));
            $wpdb->get_results("UPDATE $table SET total_pts = $total_pts, total_orders = $total_orders, total_spent = $total_spent WHERE user_id = $id");
        }
    }
}

function check_type_user($id) {
    global $wpdb;
    $table = GLOBAL_PREFIX.'groups';
    $wpdb->get_results("SELECT * FROM $table WHERE admin = $id");

    // Se existir admin para o grupo
    if(isset($wpdb->last_result[0]->id) && isset($wpdb->last_result[0]->admin)){
        if($wpdb->last_result[0]->admin === $wpdb->last_result[0]->admin)
        {
            return 'admin';
        }
    }
    else
    {
        $table = GLOBAL_PREFIX.'groups_members';
        $wpdb->get_results("SELECT * FROM $table WHERE member_id = $id");
        if(empty($wpdb->last_result)){
            return 'without group';
        }
        else
        {
            return 'member';
        }
    }
}

function check_group_id($id, $type) {
    global $wpdb;
    $table_g  = GLOBAL_PREFIX.'groups';
    $table_gm = GLOBAL_PREFIX.'groups_members';


    if ($type === 'admin')
    {
        $result = $wpdb->get_results("SELECT id as g_id FROM $table_g WHERE admin = $id");
    }
    else
    {
        $result = $wpdb->get_results("SELECT group_id as g_id FROM $table_gm WHERE member_id = $id");
    }

    return $result[0]->g_id;
}

function check_admin($group_id) {
    global $wpdb;
    $table = GLOBAL_PREFIX.'groups';

    $id = $wpdb->get_results("SELECT * FROM $table WHERE id = $group_id");

    return $id[0]->admin;
}

/**
 * Update total amount by clubs
 */
function updateTotalAmountClubs($id, $total_amount)
{
    global $wpdb;
    $table_gud  = GLOBAL_PREFIX.'groups_users_details';

    $wpdb->get_results("UPDATE $table_gud SET total_amount = $total_amount WHERE user_id = $id");

    if ($wpdb->rows_affected > 0) {
        ?><div class="updated"><p><strong><?php _e('Valor acumulado atualizado com sucesso.', 'menu-test' ); ?></strong></p></div><?php

        header('Location: admin.php?page=amount_admin');
    }
    else
    {
        ?><div class="error"><p><strong><?php _e('Algo correu mal. Verifique se alterou o valor acumulado.', 'menu-test' ); ?></strong></p></div><?php
    }
}

/**
 * Update total pts by players
 */
function updateTotalAmountPlayers($id, $total_pts)
{
    global $wpdb;
    $table_gud  = GLOBAL_PREFIX.'groups_users_details';

    $wpdb->get_results("UPDATE $table_gud SET total_pts = $total_pts WHERE user_id = $id");

    if ($wpdb->rows_affected > 0) {
        ?><div class="updated"><p><strong><?php _e('Total de pontos atualizado com sucesso.', 'menu-test' ); ?></strong></p></div><?php

        header('Location: admin.php?page=simple_user_pts');
    }
    else
    {
        ?><div class="error"><p><strong><?php _e('Algo correu mal. Verifique se alterou o número de pontos.', 'menu-test' ); ?></strong></p></div><?php
    }
}

/**
 * Update total pts by simple users
 */
function updateTotalAmountUsers($id, $total_pts)
{
    global $wpdb;
    $table_gud  = GLOBAL_PREFIX.'groups_users_details';

    $wpdb->get_results("UPDATE $table_gud SET total_pts = $total_pts WHERE user_id = $id");

    if ($wpdb->rows_affected > 0) {
        ?><div class="updated"><p><strong><?php _e('Total de pontos atualizado com sucesso.', 'menu-test' ); ?></strong></p></div><?php

        header('Location: admin.php?page=groups_user_pts');
    }
    else
    {
        ?><div class="error"><p><strong><?php _e('Algo correu mal. Verifique se alterou o número de pontos.', 'menu-test' ); ?></strong></p></div><?php
    }
}

/**
 * Procurar língua
 */
function getCurrentLang()
{
    $lang = 'PT';

    if ( defined( 'ICL_LANGUAGE_CODE' ) ) {
    // Guardar o valor
    $value = constant( 'ICL_LANGUAGE_CODE' );
        if ( !empty( $value ) ) {
            switch ($value) {
                case 'en':
                    $lang = 'EN';
                break;
                case 'pt-pt':
                    $lang = 'PT';
                break;
                case 'es':
                    $lang = 'ES';
                break;
                default:
                    $lang = 'PT'; // Depende da lingua pré-definida
                break;
            }
        }
    }

    return $lang;
}

/*
 * Adicionar tab sobre o clube nos detalhes de conta
 */

add_filter ( 'woocommerce_account_menu_items', 'players_link', 40 );
function players_link( $menu_links ){

    $lang = getCurrentLang();

    switch ($lang) {
        case 'PT':
            $endpoint_title = 'Jogadores';
            break;

        case 'EN':
            $endpoint_title = 'Players';
            break;

        case 'ES':
            $endpoint_title = 'Jugadores';
            break;

        default:
            $endpoint_title = 'Jogadores';
            break;
    }

    $menu_links = array_slice( $menu_links, 0, 1, true ) // Definir posição lateral
    + array( 'players' => $endpoint_title )
    + array_slice( $menu_links, 1, null, true );

    return $menu_links;
}

/*
 * Registar endpoint
 */
add_action( 'init', 'add_endpoint' );
function add_endpoint() {
    add_rewrite_endpoint( 'players', EP_PAGES );
}

function pagination_players($current_user){
    global $wpdb, $paged, $max_num_pages;

    $table_g   = GLOBAL_PREFIX.'groups';
    $table_gud = GLOBAL_PREFIX.'groups_members';
    $string    = $_SERVER['REQUEST_URI'];

    // Se o url tiver um número, pegar esse número
    $paged = (preg_match('~[0-9]~', $string) ? substr($string, -2, 1) : 1);
    $post_per_page = 10;
    $offset = ($paged - 1)*$post_per_page;

    $sql = "SELECT $table_gud.member_id FROM $table_g JOIN $table_gud ON $table_gud.group_id = $table_g.id WHERE admin = $current_user LIMIT ".$offset.", ".$post_per_page."; ";

    $sql_result = $wpdb->get_results( $sql, OBJECT);

    /* Determinar o total de resultados encontrados e calcular numero de paginas para a navegação*/
    $sql_posts_total = $wpdb->get_results("SELECT $table_gud.member_id FROM $table_g JOIN $table_gud ON $table_gud.group_id = $table_g.id WHERE admin = $current_user");

    $max_num_pages = ceil(count($sql_posts_total) / $post_per_page);

    $array = array('sql_result' => $sql_result, 'max_num_pages' => $max_num_pages, 'paged' => $paged);

    return $array;
}

/*
 * Conteúdo
 */
add_action( 'woocommerce_account_players_endpoint', 'players_my_account' );
function players_my_account() {
    $current_user = wp_get_current_user();
    $user         = check_type_user($current_user->ID);
    global $wpdb;
    $table_g   = GLOBAL_PREFIX.'groups';
    $table_gud = GLOBAL_PREFIX.'groups_members';

    if ($user == 'admin'):

        $all_players = pagination_players($current_user->ID);

        if(!empty($all_players['sql_result'])): ?>
            <h1><?php _e('List of players', 'points-for-woocommerce'); ?></h1>
            <table class="woocommerce-MyAccount-orders shop_table shop_table_responsive my_account_orders account-orders-table">
                <thead class="style-thead">
                  <tr>
                    <th><?php _e('Name', 'points-for-woocommerce'); ?></th>
                    <th><?php _e('Email', 'points-for-woocommerce'); ?></th>
                  </tr>
                </thead>
                <tbody>
                    <?php foreach ($all_players['sql_result'] as $value):?>
                        <tr>
                            <?php $data_player = get_user_by('ID', $value->member_id); ?>
                            <td><?php echo $data_player->data->user_login ?></td>
                            <td><?php echo $data_player->data->user_email ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <br>
            <center class="pag_players">
               <?php
                   // $show_less  = _e('Previous', 'points-for-woocommerce');
                   // $show_more  = _e('Next', 'points-for-woocommerce');
                   previous_posts_link('<<', $all_players['max_num_pages']);
                   next_posts_link('>>', $all_players['max_num_pages'])
                ?>
                <div class="pag_players_numbers"><?php echo $all_players['paged']?>/<?php echo $all_players['max_num_pages']?></div>
            </center>
        <?php else: ?>
            <!-- Sem jogadores -->
            <h3><?php _e('Without players!', 'points-for-woocommerce'); ?></h3>
        <?php endif;
    else: ?>
        <!-- <h5><?php _e('Without permissions!', 'points-for-woocommerce'); ?></h3> -->
    <?php endif;
} ?>

