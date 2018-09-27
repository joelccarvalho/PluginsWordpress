<?php

// chamar ficheiro para ver que tipo de utilizador está logado
require_once('check_permissions.php');
$check_is_admin = check_user_admin();

global $jal_db_version;
$jal_db_version = '1.0';

function warehouse_stock_table() {
    global $jal_db_version;
    global $wpdb;

    $table_name = GLOBAL_PREFIX.'warehouses_stock';

    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id int(9) NOT NULL AUTO_INCREMENT,
        product_id int(9) NOT NULL,
        warehouse_id int(9) NOT NULL,
        stock int(9) NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta($sql);

    add_option('jal_db_version', $jal_db_version);
}

function warehouse_table() {
    global $jal_db_version;
    global $wpdb;

    $table_name = GLOBAL_PREFIX.'warehouses';

    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id int(9) NOT NULL AUTO_INCREMENT,
        name varchar(55) NOT NULL,
        address varchar(55) NOT NULL,
        contact int(9) NOT NULL,
        hide_stock tinyint(1) DEFAULT 0 NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta($sql);

    add_option('jal_db_version', $jal_db_version);
}

function products() {
    global $jal_db_version;
    global $wpdb;

    $table_name = GLOBAL_PREFIX.'warehouses_products';

    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id int(9) NOT NULL AUTO_INCREMENT,
        product_id int(9) NOT NULL,
        name varchar(55) NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta($sql);

    add_option('jal_db_version', $jal_db_version);
}

function permissions() {
    global $jal_db_version;
    global $wpdb;

    $table_name = GLOBAL_PREFIX.'warehouses_permissions';

    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id int(9) NOT NULL AUTO_INCREMENT,
        user_id int(9) NOT NULL,
        warehouse_id int(9) NOT NULL,
        permission tinyint(1) DEFAULT 0 NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta($sql);

    add_option('jal_db_version', $jal_db_version);

    // Dar permissões aos admins
    insert_admin();
}

function stock_order() {
    global $jal_db_version;
    global $wpdb;

    $table_name = GLOBAL_PREFIX.'warehouses_stock_order';

    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id int(9) NOT NULL AUTO_INCREMENT,
        product_id int(9) NOT NULL,
        warehouse_id int(9) NOT NULL,
        order_id int(9) NOT NULL,
        qty int(9) NOT NULL,
        id_site int(9) NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta($sql);

    add_option('jal_db_version', $jal_db_version);
}

function help_stock() {
    global $jal_db_version;
    global $wpdb;

    $table_name = GLOBAL_PREFIX.'warehouses_help_stock';

    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id int(9) NOT NULL AUTO_INCREMENT,
        product_id int(9) NOT NULL,
        warehouse_id int(9) NOT NULL,
        stock int(9) NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta($sql);

    add_option('jal_db_version', $jal_db_version);
}

// Criar tabelas
warehouse_table();
warehouse_stock_table();
products();
permissions();
stock_order();
help_stock();

/** Layout novo armazém */
if (!current_user_can('manage_options'))
{
  wp_die( __('Não tens permissões para aceder a esta página.') );
}

if(isset($_POST['new_warehouse']) && $_POST['new_warehouse'] == 'Y') {
    $opt_name    = $_POST['name'];
    $opt_address = $_POST['address'];
    $opt_contact = $_POST['contact'];

    // criar novo armazém
    insert_warehouse($opt_name, $opt_address, $opt_contact);
}

// Now display the settings editing screen
echo '<div class="wrap">';

// header
echo "<h2>" . __( 'Novo Armazém', 'menu-test' ) . "</h2>";?>

<form class="" name="form1" method="post" action="">
    <input type="hidden" name="new_warehouse" value="Y">

    <p class="title_insert"><?php _e("Nome:", 'menu-test' ); ?></p>
    <input class="form_control" type="text" name="name" value="" size="20">

    <p class="title_insert"><?php _e("Morada:", 'menu-test' ); ?></p>
    <input class="form_control" type="text" name="address" value="" size="20">

    <p class="title_insert"><?php _e("Contacto:", 'menu-test' ); ?></p>
    <input class="form_control" type="text" name="contact" value="" size="20">

    <hr />

    <p class="submit">
        <input type="submit" name="Submit" class="button-primary" value="<?php esc_attr_e('Criar') ?>" />
    </p>
</form>
<?php

/** Inserir dados do novo armazém **/
function insert_warehouse($name, $address, $contact){
    global $wpdb;
    $table = GLOBAL_PREFIX.'warehouses';

    if (!empty($name) && !empty($address) && !empty($contact)) {
        if (is_numeric($contact)) {
            $wpdb->query( $wpdb->prepare(
                    "
                        INSERT INTO $table
                        (`name`, `address`, `contact`)
                        VALUES (%s, %s, %d);
                    ",
                        array(
                            $name,
                            $address,
                            $contact
                        )
                ) );

            if ($wpdb->rows_affected == 1) {
                ?><div class="updated"><p><strong><?php _e('Armazém criado.', 'menu-test' ); ?></strong></p></div><?php
            }
            else {
                ?><div class="error"><p><strong><?php _e('Algo correu mal.', 'menu-test' ); ?></strong></p></div><?php
            }
        }
        else {
            ?><div class="error"><p><strong><?php _e('O campo contacto deve ser numérico.', 'menu-test' ); ?></strong></p></div><?php
        }
    }
    else {
        ?><div class="error"><p><strong><?php _e('Todos os campos são obrigatórios.', 'menu-test' ); ?></strong></p></div><?php
    }
}

function insert_admin(){
    global $wpdb;
    $table_wp = GLOBAL_PREFIX.'warehouses_permissions';
    $table_w  = GLOBAL_PREFIX.'warehouses';
    $users    = get_users();

    $wpdb->get_results("SELECT * FROM $table_w");
    foreach ($wpdb->last_result as $k => $warehouses) {
        foreach ($users as $key => $value) {
            $wpdb->get_results("SELECT * FROM $table_wp WHERE user_id = $value->ID AND warehouse_id = $warehouses->id");
            if (empty($wpdb->last_result) && $value->roles[0] == 'administrator') {
                $wpdb->query($wpdb->prepare
                    (
                        "
                        INSERT INTO $table_wp
                        (`user_id`, `warehouse_id`, `permission`)
                        VALUES (%d, %d, %d);
                        ",
                        array
                        (
                          $value->ID,
                          $warehouses->id,
                          true
                        )
                    )
                );
            }
        }
    }
}

