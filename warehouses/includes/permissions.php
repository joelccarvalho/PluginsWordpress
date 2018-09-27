<?php

$id = $_GET['id'];
global $wpdb;
$table = GLOBAL_PREFIX.'warehouses';
$wpdb->get_results("SELECT * FROM $table WHERE id = $id");

// Se o id existir
if (!empty($wpdb->last_result)):

    $table_wp      = GLOBAL_PREFIX.'warehouses_permissions';
    $table_w       = GLOBAL_PREFIX.'warehouses';

    // chamar ficheiro para ver que tipo de utilizador está logado
    require_once('check_permissions.php');
    $check_is_admin = check_user_admin();

    $id_warehouse   = (isset($_GET['id']) ? $_GET['id'] : '0');
    $users          = get_users();

    // Se clicar no botão de atualizar as permissões
    if (isset($_POST['change_roles'])) {
        foreach ($users as $key => $value) {
            if (isset($_POST['user_id_'.$value->ID])) {
                update_permissions_true($value->ID, $id_warehouse, $users);
            }
            else {
                update_permissions_false($value->ID, $id_warehouse, $value->roles[0]);
            }
        }
    }

    insert_permission_all_users($users, $id_warehouse);

    $pagination = pagination_query();

    $wpdb->get_results("SELECT name FROM $table_w WHERE id = $id_warehouse");?>

    <h3>Lista de permissões do armazém: <?php echo $wpdb->last_result[0]->name;?></h3>

    <table class="widefat">
        <thead class="style-thead">
          <tr>
            <th width="80px">ID</th>
            <th width="80px">Email</th>
            <th width="150px">Tipo de utilizador</th>
            <th width="80px">Permitido</th>
            <th></th>
          </tr>
        </thead>
        <?php foreach ($pagination['query'] as $key => $value): ?>

            <tbody>
              <tr>
                <td><?php echo $value->ID ?></td>
                <td><?php echo $value->data->user_email ?></td>
                <?php $role = roles_names($value->roles[0]); ?>
                <td><?php echo $role ?></td>
                <td>
                   <form name="change_roles" method="post" action="">
                    <input type="hidden" name="change_roles" value="change_roles">
                    <?php $wpdb->get_results("SELECT permission FROM $table_wp WHERE user_id = $value->ID AND warehouse_id = $id_warehouse");?>
                    <input <?php echo (isset($wpdb->last_result[0]->permission) && $wpdb->last_result[0]->permission ? 'checked' : '').' '.($value->roles[0] == 'administrator' ? 'Disabled' : '') ?> type="checkbox" name="user_id_<?php echo $value->ID ?>" value=<?php echo $value->ID ?>>
              </tr>
            </tbody>

        <?php endforeach; ?>
    </table>
                <br><center><input type="submit" name="change" value="Alterar permissões" class="button button-primary"></center>
              </form>
            </td>

    <br>
    <center class="nav_products">
       <?php previous_posts_link('&laquo; Mostrar menos',$pagination['tp']) ?> <?php echo $pagination['paged']?>/<?php echo $pagination['tp']?>
       <?php next_posts_link('Mostrar mais &raquo;',$pagination['tp']) ?>
    </center>

<?php else:
    ?><div class="error"><p><strong><?php _e('ID inexistente.', 'menu-test' ); ?></strong></p></div><?php
endif;

function update_permissions_true($user_id, $warehouse_id, $users)
{
    global $wpdb;
    $table_wp = GLOBAL_PREFIX.'warehouses_permissions';

    $wpdb->get_results("UPDATE $table_wp SET permission = true WHERE user_id = $user_id AND warehouse_id = $warehouse_id");
}

function update_permissions_false($user_id, $warehouse_id, $admin)
{
    global $wpdb;
    $table_wp = GLOBAL_PREFIX.'warehouses_permissions';

    // Se for admin não altera as permissões
    if ($admin != 'administrator') {
        $wpdb->get_results("UPDATE $table_wp SET permission = false WHERE user_id = $user_id AND warehouse_id = $warehouse_id");
    }
}

function insert_permission_all_users($users, $warehouse_id){
    global $wpdb;
    $table_wp = GLOBAL_PREFIX.'warehouses_permissions';

    foreach ($users as $key => $value) {
        $wpdb->get_results("SELECT * FROM $table_wp WHERE user_id = $value->ID AND warehouse_id = $warehouse_id");
        $permission = false;

        // Se for administrador a permissão por defeito está ativa
        if (empty($wpdb->last_result)) {
            if ($value->roles[0] == 'administrator') {
               $permission = true;
            }
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
                      $warehouse_id,
                      $permission
                    )
                )
            );
        }
    }
}

/**
 * Paginação
 */
function pagination_query(){
    global $paged, $max_num_pages;

    $paged         = (isset($_GET['paged']) ? $_GET['paged'] : 1);
    $post_per_page = 10;
    $offset        = ($paged - 1) * $post_per_page;
    // Procurar todos os utilizadores que não são clientes
    $args          = array('offset' => $offset, 'number' => $post_per_page, 'role__not_in' => array('customer'));
    $query         = get_users($args);
    $args          = array('role__not_in' => array('customer'));
    $users         = get_users($args);
    $total_query   = count($query);
    $tp            = ceil(count($users) / $post_per_page);

    $array = array('query' => $query, 'tp' => $tp, 'paged' => $paged);
    return $array;
}

/**
 * Nomes em português
 */
function roles_names($role) {

    switch ($role) {
        case 'administrator':
            $role = 'Administrador';
        break;

        case 'shop_manager':
            $role = 'Gestor de loja';
        break;

        case 'contributor':
            $role = 'Colaborador';
        break;

        case 'subscriber':
            $role = 'Subscritor';
        break;

        case 'editor':
            $role = 'Editor';
        break;

        default:
            $role = $role;
        break;
    }

    return $role;
}
