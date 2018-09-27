<?php
/** Listagem dos armazéns */
global $wpdb;
$table = GLOBAL_PREFIX.'warehouses';
// Dar permissões aos admins
insert_admin();

// Se clicou nos detalhes muda de vista
$tab = (isset($_GET['tab']))?$_GET['tab']:'';
if ($tab == 'details') {
  include_once(PATH.'includes/warehouse_manage.php');
}
else if ($tab == 'edit_warehouse') {
    include_once(PATH.'includes/edit_warehouses.php');
}
else if ($tab == 'permissions') {
    include_once(PATH.'includes/permissions.php');
}
else if ($tab == 'warehouse_orders') {
    include_once(PATH.'includes/warehouse_orders.php');
}
else {

    // chamar ficheiro para ver que tipo de utilizador está logado
    require_once('check_permissions.php');
    $user_permissions = check_user();
    $check_is_admin   = check_user_admin_bool();

    $wpdb->get_results("SELECT * FROM $table ORDER BY name");?>

    <h3>Lista de armazéns</h3>
    <table class="widefat">
        <thead class="style-thead">
          <tr>
            <th>Nome</th>
            <th>Morada</th>
            <th>Contacto</th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
          </tr>
        </thead>
        <?php foreach ($wpdb->last_result as $key => $value):
            foreach ($user_permissions as $k => $up):
                if($up->warehouse_id == $value->id && $up->permission):?>
                    <tbody>
                      <tr>
                        <input type="hidden" name="id" value="<?php echo $value->id; ?>">
                        <td><?php echo $value->name ?></td>
                        <td><?php echo $value->address ?></td>
                        <td><?php echo $value->contact ?></td>
                        <td style="width: 15px"><a href="?page=list-warehouses&amp;tab=details&id=<?php echo $value->id; ?>"><button class="button button-primary">Stocks</button></a></td>
                        <?php if($check_is_admin):?>
                            <td style="width: 15px"><a href="?page=list-warehouses&amp;tab=edit_warehouse&id=<?php echo $value->id; ?>"><button class="my_button button-edit">Editar</button></a></td>
                            <td style="width: 15px"><a href="?page=list-warehouses&amp;tab=permissions&id=<?php echo $value->id; ?>"><button class="my_button button-remove">Permissões</button></a></td>
                            <td><a href="?page=list-warehouses&amp;tab=warehouse_orders&id=<?php echo $value->id; ?>"><button class=" button-secondary">Total Encomendas</button></a></td>
                        <?php endif; ?>
                      </tr>
                    </tbody>
                <?php endif; ?>
            <?php endforeach; ?>
        <?php endforeach; ?>
    </table>

<?php }

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
