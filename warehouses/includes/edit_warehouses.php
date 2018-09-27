<?php if ( ! defined( 'ABSPATH' ) ) exit;

// chamar ficheiro para verificar se tem permissões
require_once('check_permissions.php');
$user_permissions = check_user_admin();
$site             = get_current_blog_id();
$id = $_GET['id'];
global $wpdb;
$table = GLOBAL_PREFIX.'warehouses';

$wpdb->get_results("SELECT * FROM $table WHERE id = $id");

// Se o id existir
if (!empty($wpdb->last_result)):

    $action = (isset($_GET['action']))?$_GET['action']:'';

    if ($action == 'remove'):?>
        <div class="">
          <h4>Deseja apagar esta loja permanentemente?</h4>
              <form name="form_confirm_remove" method="post" action="">
                <input type="hidden" name="confirm_remove" value="confirm_remove">
                <div class="radio">
                    <label><input type="radio" name="optradio" value="1">Sim</label>
                </div>
                <div class="radio">
                    <label><input type="radio" name="optradio" value="0" checked="checked">Não</label>
                </div>
                <input type="hidden" name="id" value="<?php echo $id; ?>">
                <br><td class="btn-padding"><input type="submit" name="submit" class="button-primary" value="<?php esc_attr_e('Confirmar') ?>" /></td>
              </form>
        </div>
        <?php
    endif;

    // Confirmar a eliminação
    if (isset($_POST['confirm_remove'])){
        if($_POST['optradio'] == 1):
            $table_w  = GLOBAL_PREFIX.'warehouses';
            $table_wp = GLOBAL_PREFIX.'warehouses_permissions';
            $table_ws = GLOBAL_PREFIX.'warehouses_stock';
            $id       = $_POST['id'];

            $wpdb->delete($table_w, array('id' => $id));

            // Se obteve sucesso
            if ($wpdb->rows_affected > 0):

                $wpdb->delete($table_wp, array('warehouse_id' => $id));
                $wpdb->delete($table_ws, array('warehouse_id' => $id));

                ?><div class="updated"><p><strong><?php _e('Eliminado com sucesso.', 'menu-test'); ?></strong></p></div>
            <?php else:
                ?><div class="error"><p><strong><?php _e('Erro na eliminação.', 'menu-test'); ?></strong></p></div><?php
            endif;
        endif;

        $newURL = '?page=list-warehouses';
        header('Location: '.$newURL);
    }
    else if(isset($_POST['update'])){

        $name    = $_POST['name'];
        $address = $_POST['address'];
        $contact = $_POST['contact'];

        if(!empty($name) && !empty($contact) && !empty($address))
        {
            $current_visibility = $wpdb->get_results("SELECT hide_stock FROM $table WHERE id = $id");
            $current_visibility = $current_visibility[0]->hide_stock;

            if ($site == 1) {
                if (isset($_POST['hide_stock'])) { // Se ativar a checkbox
                    if (!$current_visibility) { // Se o valor na BD for 0
                        $wpdb->get_results("UPDATE $table SET hide_stock = 1 WHERE id = $id");
                        hide_stock_warehouse($id, 'disable');
                    }
                }
                elseif ($current_visibility) { // Se não ativar a checkbox e estiver 1 na BD
                    $wpdb->get_results("UPDATE $table SET hide_stock = 0 WHERE id = $id");
                    hide_stock_warehouse($id, 'enable');
                }
            }

            $wpdb->get_results("UPDATE $table SET name = '$name', address = '$address', contact = $contact WHERE id = $id");

            if ($wpdb->rows_affected != 0) {
                ?><div class="updated"><p><strong><?php _e('Armazém atualizado com sucesso.', 'menu-test' ); ?></strong></p></div><?php
            }
            else
            {
                ?><div class="error"><p><strong><?php _e('Erro na atualização, verifique se alterou algum dado.', 'menu-test' ); ?></strong></p></div><?php
            }
        }
        else
        {
            ?><div class="error"><p><strong><?php _e('Todos os campos devem ser preenchidos.', 'menu-test' ); ?></strong></p></div><?php
        }
    }

    $wpdb->get_results("SELECT * FROM $table WHERE id = $id"); ?>

    <h3><?php echo $wpdb->last_result[0]->name ?></h3>
    <table class="widefat">
        <thead class="style-thead">
          <tr>
           <th width="80px">Nome</th>
           <th width="80px">Morada</th>
           <th width="80px">Contacto</th>
           <?php echo ($site == 1 ? '<th width="80px">Desativar stock</th>' : '') ?>
           <th><a href="?page=list-warehouses&amp;tab=edit_warehouse&amp;action=remove&id=<?php echo $id; ?>"><button class="my_button button-remove"><span class="dashicons dashicons-trash"></span></button></a></th>
           <th></th>
          </tr>
        </thead>
        <tbody>
            <?php foreach ($wpdb->last_result as $key => $value):?>
                <form name="form_edit" method="post" action="">
                    <input type="hidden" name="update" value="update">
                    <tr>
                        <td><input type="text" name="name" value="<?php echo $value->name ?>"></td>
                        <td><input type="text" name="address" value="<?php echo $value->address ?>"></td>
                        <td><input type="text" name="contact" value="<?php echo $value->contact ?>"></td>
                        <?php if ($site == 1): ?>
                            <td><input type="checkbox" name="hide_stock" <?php echo ($value->hide_stock ? 'checked' : '') ?>></td>
                        <?php endif;?>
                        <td><input type="submit" class="button button-primary" value="Atualizar"></td>
                    </tr>
                </form>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else:
    ?><div class="error"><p><strong><?php _e('ID inexistente.', 'menu-test' ); ?></strong></p></div><?php
endif;

/**
 * Esconder o stock de um determinado armazém
 * Funciona ID do Andebol, assim como no sites sincronizados
 * Falta as traduções do Andebol e restantes sites
 **/
function hide_stock_warehouse($warehouse_id, $action)
{
    global $wpdb;
    $table_ws = GLOBAL_PREFIX.'warehouses_stock';
    $table_wp = GLOBAL_PREFIX.'warehouses_products';

    // Procurar produtos e variações
    $site = get_current_blog_id();
    if ($site == 1) {

        // Ver quais tem stock no warehouse id
        $product_with_stock = $wpdb->get_results("SELECT $table_wp.name, $table_wp.product_id, $table_ws.stock FROM $table_ws JOIN $table_wp ON $table_wp.id = $table_ws.product_id WHERE `stock` > 0 AND `warehouse_id` = $warehouse_id");

        foreach ($product_with_stock as $pws) {
            $stock = get_post_meta($pws->product_id, '_stock', true);

            // Ação a realizar, remover ou alterar esse stock
            if ($action == 'enable') {
                $stock = bcadd($stock, $pws->stock);
            }
            elseif ($action == 'disable') {
                $stock = bcsub($stock, $pws->stock);
            }

            // Atualizar
            wc_update_product_stock($pws->product_id, $stock);

            // Sites que estão sincronizados
            foreach (get_sites() as $key => $value) {

                if ($value->blog_id != 1) {
                    $id_synced = get_post_meta($pws->product_id, 'wmpcs_synced_post_id_on_site_'.$value->blog_id, TRUE);

                    if (!empty($id_synced)) {

                        switch_to_blog($value->blog_id);
                        wc_update_product_stock($id_synced, $stock);
                        restore_current_blog();

                        // TRADUÇÕES


                    }
                }
            }
            // TRADUÇÕES
        }
    }
}
