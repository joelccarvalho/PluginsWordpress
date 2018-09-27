<?php if ( ! defined( 'ABSPATH' ) ) exit;

    register_activation_hook( __FILE__, 'add_member_groups');

    global $jal_db_version;
    $jal_db_version = '1.0';

    if (!current_user_can('manage_options'))
    {
      wp_die( __('Não tem permissões para aceder a esta página.') );
    }

    // variaveis para o campo e as opções
    $hidden_field_name = 'mt_submit_hidden';
    if( isset($_POST[ $hidden_field_name ]) && $_POST[ $hidden_field_name ] == 'Add' ) {
        $opt_group     = check_club_id($_POST['group']);
        $opt_member_id = check_user($_POST['member']);

        if (!empty($opt_group) && !empty($opt_member_id)) {

            global $wpdb;
            $table = GLOBAL_PREFIX.'groups';
            $wpdb->get_results("SELECT admin FROM $table WHERE admin = $opt_member_id");

            // Verificar se o utilizador escolhido é o admin do grupo escolhido
            if (empty($wpdb->last_result)) {
                $table = GLOBAL_PREFIX.'groups_members';
                $wpdb->get_results("SELECT member_id FROM $table WHERE member_id = $opt_member_id");

                // Verificar se o utilizador já pertence ao grupo
                if(empty($wpdb->last_result))
                {
                    /*$table = GLOBAL_PREFIX.'groups_users_details';

                    // Se já efetuou compras
                    $wpdb->get_results("SELECT * FROM $table WHERE user_id = $opt_member_id");

                    if (empty($wpdb->last_result)) {
                        insert_data($opt_group, $opt_member_id);
                    }
                    else {
                        ?><div class="error"><p><strong><?php _e('Erro.', 'menu-test' ); ?></strong></p></div><?php
                    }*/
                    insert_data($opt_group, $opt_member_id);
                }
                else
                {
                    ?><div class="error"><p><strong><?php _e('Esse jogador já pertence a um clube.', 'menu-test' ); ?></strong></p></div><?php
                }
            }
            else
            {
                ?><div class="error"><p><strong><?php _e('Esse jogador já é o administrador de um clube.', 'menu-test' ); ?></strong></p></div><?php
            }
        }
        else
        {
            ?><div class="error"><p><strong><?php _e('Todos os campos são obrigatórios.', 'menu-test' ); ?></strong></p></div><?php
        }
    }

    // Pesquisar os clubes
    global $wpdb;
    $table = GLOBAL_PREFIX.'groups';
    ?>

    <div class="cat_mode">
        <h2>Email do jogador: </h2>
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
        <h2>Nome do clube: </h2>
        <div class="dropdown">
            <button onclick="showFunctionClub()" class="button-primary btn_search">Pesquisar por nome...</button>
            <div id="dropdownClubs" class="dropdown-content">
              <input type="text" placeholder="Nome" id="inputClub" onkeyup="searchFunctionClub()">

              <?php $wpdb->get_results("SELECT * FROM $table ORDER BY name");
              $exists_club = false;
              foreach ($wpdb->last_result as $value):
                $exists_club = true;?>
                <a href="#"><?php echo $value->name ?></a>
              <?php  endforeach; ?>
                <div class="without_club" style="display: none !important">Nenhum resultado encontrado.</div>
            </div>
        </div>
        <hr>
        <form name="form_remove" method="post" action="">
            <br>
            <h4>Informação a adicionar: </h4>
            <input type="hidden" name="<?php echo $hidden_field_name; ?>" value="Add">
            <input type="text" name="member" id="email_admin" size="30"></td><br>
            <input type="text" name="group" id="club_name" size="30"></td>
            <hr>
            <p class="submit">
                <input type="submit" name="Submit" class="button-primary" <?php echo (!$exists_club ? 'Disabled' : '') ?> value="<?php esc_attr_e('Adicionar') ?>" />
            </p>
        </form>
    </div>

<?php

/** Inserir novo membro **/
function insert_data($group_id, $member_id){
    global $wpdb;
    $table = GLOBAL_PREFIX.'groups_members';

    $wpdb->query( $wpdb->prepare(
            "
                INSERT INTO $table
                (`group_id`, `member_id`)
                VALUES (%d, %d);
            ",
                array(
                $group_id,
                $member_id
            )
        ) );

    if ($wpdb->rows_affected == 1) {
        ?><div class="updated"><p><strong><?php _e('Adicionado ao clube.', 'menu-test' ); ?></strong></p></div><?php
    }
    else {
        ?><div class="error"><p><strong><?php _e('Algo correu mal.', 'menu-test' ); ?></strong></p></div><?php
    }

    insert_player_details($member_id, $group_id);
}

function insert_player_details($id, $group_id){
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
                    'player'
                )
            )
        );
    }
    else { // Atualizar dados do novo admin e apagar os pontos
        $wpdb->get_results("UPDATE $table SET group_id = $group_id, perfil = 'player', total_pts = 0 WHERE user_id = $id");
    }
}

function check_user($opt_email) {
    $all_users = get_users();

    if (!is_null($opt_email)) {
        foreach ($all_users as $key => $user){
            if($user->user_email === $opt_email){
                return $user->ID;
            }
        }
    }
    return '';
}

function check_club_id($name) {
    global $wpdb;
    $table = GLOBAL_PREFIX.'groups';

    $info = $wpdb->get_results("SELECT id FROM $table WHERE name = '$name'");

    if (!is_null($name) && !empty($wpdb->last_result)) {
        return $info[0]->id;
    }
    return '';
}
