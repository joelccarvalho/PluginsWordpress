<?php defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

    register_activation_hook( __FILE__, 'groups_table');

    global $jal_db_version;
    $jal_db_version = '1.0';

    function groups_table() {
        global $jal_db_version;
        global $wpdb;

        $table_name = GLOBAL_PREFIX.'groups';

        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
            id int(9) NOT NULL AUTO_INCREMENT,
            name varchar(55) DEFAULT 'grupo sem nome' NOT NULL,
            admin int(9) NOT NULL,
            discount_user float(9) NOT NULL,
            margin float(9) NOT NULL,
            site_id int(9) NOT NULL,
            PRIMARY KEY  (id)
        ) $charset_collate;";

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);

        add_option('jal_db_version', $jal_db_version);
    }

    function add_member_groups() {
        global $jal_db_version;
        global $wpdb;

        $table_name = GLOBAL_PREFIX.'groups_members';

        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
            id int(9) NOT NULL AUTO_INCREMENT,
            group_id int(9) NOT NULL,
            member_id int(9) NOT NULL,
            PRIMARY KEY  (id)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);

        add_option('jal_db_version', $jal_db_version);
    }

    if (!current_user_can('manage_options'))
    {
      wp_die( __('Não tem permissões para aceder a esta página.') );
    }

     function groups_users_details() {
        global $jal_db_version;
        global $wpdb;

        $table_name = GLOBAL_PREFIX.'groups_users_details';

        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
            id int(9) NOT NULL AUTO_INCREMENT,
            user_id int(9) NOT NULL,
            group_id int(9) NULL,
            perfil enum('admin','player','simple') NOT NULL,
            total_pts INT(9) NULL DEFAULT 0,
            total_amount DECIMAL(9,2) NULL DEFAULT 0,
            total_orders INT(9) NULL DEFAULT 0,
            total_spent DECIMAL(9,2) NULL DEFAULT 0,
            PRIMARY KEY  (id)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);

         add_option('jal_db_version', $jal_db_version);
    }

    // Criar tabelas
    groups_table();
    add_member_groups();
    groups_users_details();

    // variables for the field and option names
    $hidden_field_name = 'mt_submit_hidden';

    if( isset($_POST['mt_submit_hidden']) && $_POST['mt_submit_hidden'] == 'submit_new_club' ) {

        // Read their posted value
        $opt_name     = $_POST['name'];
        $opt_discount = $_POST['discount'];
        $opt_margin   = $_POST['margin'];
        $opt_admin    = $_POST['admin'];
        $opt_site_id  = $_POST['site'];
        $opt_admin    = check_user($_POST['admin']);

        if (!empty($opt_name) && !empty($opt_discount) && !empty($opt_margin) && !empty($opt_admin)) {

            $opt_discount = str_replace(',', '.', $opt_discount);
            $opt_margin   = str_replace(',', '.', $opt_margin);

            if (is_numeric($opt_discount) && is_numeric($opt_margin)) {

                // Verificar se esse utilizador pertence a algum grupo
                $details = check_user_details($opt_admin);

                if (!$details) {
                    insert_data($opt_name, $opt_admin, $opt_discount, $opt_margin, $opt_site_id);
                    insert_admin($opt_admin);
                }
                else {
                    ?><div class="error"><p><strong><?php _e('Este utilizador já pertence a um grupo.', 'menu-test' ); ?></strong></p></div><?php
                }
            }
            else
            {
                ?><div class="error"><p><strong><?php _e('O campo desconto e margem devem ser númericos.', 'menu-test' ); ?></strong></p></div><?php
            }
        }
        else {
            ?><div class="error"><p><strong><?php _e('Todos os campos devem ser preenchidos.', 'menu-test' ); ?></strong></p></div><?php
        }
    }
    ?>

    <div class="cat_mode">
        <div class="dropdown">
            <button onclick="showFunction()" class="button-primary btn_search">Pesquisar por email...</button>
            <div id="dropdownUsers" class="dropdown-content">
              <input type="text" placeholder="Nome" id="inputEmail" onkeyup="searchFunction()">

              <?php
              $users = get_users();
              foreach ($users as $info_users):?>
                <a href="#"><?php echo $info_users->user_email ?></a>
              <?php  endforeach; ?>
                <div class="without_user" style="display: none !important">Nenhum resultado encontrado.</div>
            </div>
        </div>

        <br>
        <hr/>

        <table class="widefat">
            <form name="form_remove" method="post" action="">
                <input type="hidden" name="<?php echo $hidden_field_name; ?>" value="submit_new_club">
                <thead class="style-thead">
                  <tr>
                    <th>Email clube</th>
                    <th>Nome</th>
                    <th>Desconto por jogador(%)</th>
                    <th>Margem de lucro(%)</th>
                    <th>Site associado</th>
                    <th></th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td><input type="text" name="admin" id="email_admin" size="30"></td>
                    <td><input type="text" name="name" size="20"></td>
                    <td><input type="text" name="discount" size="20"></td>
                    <td><input type="text" name="margin" size="20"></td>
                    <td>
                        <select id="site_id" name="site" tabindex="3" class="form-control">
                            <?php
                                $table = GLOBAL_PREFIX.'blogs';
                                global $wpdb;
                                $wpdb->get_results("SELECT blog_id, domain, path FROM $table");
                                foreach ($wpdb->last_result as $key => $value):  ?>
                                    <option value="<?php echo $value->blog_id; ?>"><?php echo $value->domain .'('.$value->path .')' ?></option>
                            <?php endforeach;?>
                        </select>
                    </td>
                    <td><input type="submit" name="Submit" class="button-primary" value="<?php esc_attr_e('Criar') ?>"</td>
                  </tr>
                </tbody>
            </form>
        </table>
    </div>

<?php

/** Inserir dados do novo grupo **/
function insert_data($name, $admin, $discount, $margin, $site_id){
    global $wpdb;
    $table = GLOBAL_PREFIX.'groups';

    $wpdb->query( $wpdb->prepare(
            "
                INSERT INTO $table
                (`name`, `admin`, `discount_user`, `margin`, `site_id`)
                VALUES (%s, %d, %f, %f, %d);
            ",
                array(
                    $name,
                    $admin,
                    $discount,
                    $margin,
                    $site_id
                )
            ) );


    if ($wpdb->rows_affected == 1) {
        ?><div class="updated"><p><strong><?php _e('Clube criado.', 'menu-test' ); ?></strong></p></div><?php
    }
    else {
        ?><div class="error"><p><strong><?php _e('Algo correu mal.', 'menu-test' ); ?></strong></p></div><?php
    }
}

/* Inserir novo admin na tabela de detalhes */
function insert_admin($admin){
    global $wpdb;
    $table = GLOBAL_PREFIX.'groups';

    $wpdb->get_results("SELECT id FROM $table WHERE admin = $admin");
    $group_id = $wpdb->last_result[0]->id;

    insert_admin_details($admin, $group_id);
}

function insert_admin_details($id, $group_id){
    global $wpdb;
    $table = GLOBAL_PREFIX.'groups_users_details';

    // Verificar se já existe
    $wpdb->get_results("SELECT * FROM $table WHERE user_id = $id");

    if (empty($wpdb->last_result)) {
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
                    'admin'
                )
            )
        );
    }
    else { // Atualizar dados do novo admin e apagar os pontos
        $wpdb->get_results("UPDATE $table SET group_id = $group_id, perfil = 'admin', total_pts = 0 WHERE user_id = $id");
    }
}

function check_user_details($id_user){
    global $wpdb;
    $table = GLOBAL_PREFIX.'groups';
    $wpdb->get_results("SELECT admin FROM $table WHERE admin = $id_user");

    if (!empty($wpdb->last_result)) {
        return true;
    }
    else
    {
        $table = GLOBAL_PREFIX.'groups_members';
        $wpdb->get_results("SELECT member_id FROM $table WHERE member_id = $id_user");

        if(!empty($wpdb->last_result))
        {
          return true;
        }
    }
    return false;
}

function check_user($opt_email) {
    $all_users = get_users();

    foreach ($all_users as $key => $user){
        if($user->user_email === $opt_email){
            return $user->ID;
        }
    }
    return '';
}
