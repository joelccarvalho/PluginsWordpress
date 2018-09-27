
<?php

global $wpdb;
$id = $_GET['id'];
$site = get_current_blog_id();

$table_w  = GLOBAL_PREFIX.'warehouses';
$table_wp = GLOBAL_PREFIX.'warehouses_products';
$table_ws = GLOBAL_PREFIX.'warehouses_stock';

$wpdb->get_results("SELECT * FROM $table_w ORDER BY id");
$list_warehouses[] = $wpdb->last_result;

// chamar ficheiro para verificar se tem permissões
require_once('check_permissions.php');

if(isset($_POST['update'])){
    $s            = '_stock';
    $ss           = '_stock_status';
    $product_id   = $_POST['product_id'];

    $product_stock = $wpdb->get_results("SELECT stock FROM $table_ws WHERE product_id = $product_id");
    $total_stock = 0;
    foreach ($product_stock as $k => $v) {
        $total_stock = bcadd($total_stock, $v->stock, 0);
    }

    foreach ($list_warehouses[0] as $k => $warehouse) {

        // Percorrer os armazéns e atualizar com os valores vindos do POST
        if (isset($_POST['warehouse_id_'.$warehouse->id])){
            $warehouse_id = $_POST['warehouse_id_'.$warehouse->id];
            $stock        = $_POST['new_stock_'.$warehouse->id];

            // Verificar se tem permissões
            $user_permissions = check_user_warehouse_bool($warehouse_id);

            if ($user_permissions) {
                if(is_numeric($stock))
                {
                    $stock = intval($stock);
                    $table = GLOBAL_PREFIX.'warehouses_stock';

                    $wpdb->get_results("UPDATE $table SET stock = $stock WHERE product_id = $product_id AND warehouse_id = $warehouse_id");

                    file_put_contents('/home/joel/work/wpandebol7/log_stock.txt', date('c')." ".$stock." ".$_POST['new_stock_'.$warehouse->id]." ".$warehouse_id." ".$product_id."\n", FILE_APPEND);

                    // Colocar stock na tabela ajuda
                    insert_help_stock($stock, $product_id, $warehouse_id);

                    // Atualizar stock total pelos armazens existentes
                    $wpdb->get_results("SELECT stock FROM $table WHERE product_id = $product_id");
                    $ts = 0;
                    foreach ($wpdb->last_result as $key => $value) {
                        $ts = bcadd($ts, $value->stock, 0);
                    }

                    // Se está em stock
                    if ($ts > 0) {
                        $in_stock = true;
                    }
                    elseif ($ts == 0) {
                        $in_stock = false;
                    }

                    $current_site = get_current_blog_id();
                    if ($current_site != 1) {
                        $table_pm = GLOBAL_PREFIX.'postmeta';

                        foreach (get_sites() as $key => $value) {
                            $meta_key = 'wmpcs_synced_post_id_on_site_'.$value->blog_id;
                            $data = $wpdb->get_results("SELECT * FROM $table_pm WHERE post_id = $id AND meta_key = '$meta_key'");
                            if (!empty($data)) {
                                $id_current_site[] = $data;
                            }
                        }

                        foreach ($id_current_site as $key => $value) {
                            foreach (get_sites() as $k => $v) {
                                if ($value[0]->meta_key == 'wmpcs_synced_post_id_on_site_'.$v->blog_id) {
                                    $table = GLOBAL_PREFIX.$v->blog_id.'_postmeta';
                                    $table = ($v->blog_id == 1 ? GLOBAL_PREFIX.'postmeta' : $table);
                                    $id_update = $value[0]->meta_value;
                                    $meta_key  = $value[0]->meta_key;

                                    $wpdb->get_results("UPDATE $table SET meta_value = $ts WHERE post_id = $id_update AND meta_key = '$s'");

                                    // Mudar stock das traduções
                                    stock_wpml($id_update, $v->blog_id, $ts, $s, $in_stock, $ss);

                                    // Mudar estado do stock consoante o valor de stock
                                    if ($in_stock) {
                                        $wpdb->get_results("UPDATE $table SET meta_value = 'instock' WHERE post_id = $id_update AND meta_key = '$ss'");
                                    }
                                    else {
                                        $wpdb->get_results("UPDATE $table SET meta_value = 'outofstock' WHERE post_id = $id_update AND meta_key = '$ss'");
                                    }
                                }
                            }
                        }

                        $table = GLOBAL_PREFIX.'postmeta';
                        $wpdb->get_results("UPDATE $table SET meta_value = $ts WHERE post_id = $id AND meta_key = '$s'");

                        // Mudar stock das traduções
                        stock_wpml($id, null, $ts, $s, $in_stock, $ss);

                        // Mudar estado do stock consoante o valor de stock
                        if ($in_stock) {
                            $wpdb->get_results("UPDATE $table SET meta_value = 'instock' WHERE post_id = $id AND meta_key = '$ss'");
                        }
                        else {
                            $wpdb->get_results("UPDATE $table SET meta_value = 'outofstock' WHERE post_id = $id AND meta_key = '$ss'");
                        }
                    }
                    else
                    {
                        // Atualizar nos restantes sites
                        foreach (get_sites() as $key => $value) {
                            $id_synced = get_post_meta($id, 'wmpcs_synced_post_id_on_site_'.$value->blog_id, TRUE);
                            if (!empty($id_synced)) {
                                $table     = GLOBAL_PREFIX.$value->blog_id.'_postmeta';
                                $table     = ($value->blog_id == 1 ? GLOBAL_PREFIX.'postmeta' : $table);

                                $wpdb->get_results("UPDATE $table SET meta_value = $ts WHERE post_id = $id_synced AND meta_key = '$s'");

                                // Mudar stock das traduções
                                stock_wpml($id_synced, $value->blog_id, $ts, $s, $in_stock, $ss);

                                // Mudar estado do stock consoante o valor de stock
                                if ($in_stock) {
                                    $wpdb->get_results("UPDATE $table SET meta_value = 'instock' WHERE post_id = $id_synced AND meta_key = '$ss'");
                                }
                                else {
                                    $wpdb->get_results("UPDATE $table SET meta_value = 'outofstock' WHERE post_id = $id_synced AND meta_key = '$ss'");
                                }
                            }
                        }

                        // Mudar stock das traduções do principal
                        stock_wpml($id, null, $ts, $s, $in_stock, $ss);
                    }

                    wc_update_product_stock($id, $ts);

                    // Forçar a voltar ao id vindo do get
                    $id = $_GET['id'];
                }
                else
                {
                    ?><div class="error"><p><strong><?php _e('O Stock tem de ser um número inteiro.', 'menu-test' ); ?></strong></p></div><?php
                }
            }
        }
    }

    // Se houve atualização de stock
    $product_stock = $wpdb->get_results("SELECT stock FROM $table_ws WHERE product_id = $product_id");
    $current_ts = 0;
    foreach ($product_stock as $key => $value) {
        $current_ts = bcadd($current_ts, $value->stock, 0);
    }

    if ($total_stock != $current_ts) {
        ?><div class="updated"><p><strong><?php _e('Stock total atualizado para '. $current_ts . ' unidade(s).', 'menu-test' ); ?></strong></p></div><?php
    }
    else {
        ?><div class="error"><p><strong><?php _e('Stock total não alterado, verifique se alterou algum valor.', 'menu-test' ); ?></strong></p></div><?php
    }
}

$wpdb->get_results("SELECT * FROM $table_wp WHERE product_id = $id");

// Se o id existir
if (!empty($wpdb->last_result)):
    $id_product   = $wpdb->last_result[0]->id;
    $name_product = $wpdb->last_result[0]->name; ?>

    <h3 class="name_product">Stock produto: <?php echo $name_product ?></h3>
        <table class="widefat">
            <thead class="style-thead">
              <tr>
                <th width="80px" class="style_th_table">ID</th>
                <th width="80px" class="style_th_table">Nome do produto</th>
                <th width="80px" class="style_th_table">Lojas</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td><?php echo $id ?></td>
                <td><?php echo $wpdb->last_result[0]->name ?></td>

                <td>
                    <table>
                    <thead class="style-thead">
                      <tr>
                        <?php foreach ($list_warehouses[0] as $key => $value): ?>
                            <th class="subtitle_extras"><?php echo $value->name ?></th>
                        <?php endforeach;?>
                        <th></th>
                      </tr>
                    </thead>

                        <?php $s = $wpdb->get_results("SELECT $table_ws.product_id as product_id, $table_w.id as warehouse_id, $table_ws.stock, $table_w.name FROM $table_ws JOIN $table_wp ON $table_wp.id = $table_ws.product_id JOIN $table_w ON $table_w.id = $table_ws.warehouse_id WHERE $table_ws.product_id = $id_product ORDER BY warehouse_id");

                            foreach ($s as $stock_value):
                                $user_permissions = check_user_warehouse_bool($stock_value->warehouse_id);?>

                                <form name="form_update" method="post" action="">
                                    <input type="hidden" name="update" value="update">
                                    <?php if($user_permissions): ?>
                                        <td><input type="text" name="new_stock_<?php echo $stock_value->warehouse_id ?>" value="<?php echo $stock_value->stock ?>"></td>
                                    <?php else: ?>
                                        <td><?php echo $stock_value->stock ?></td>
                                    <?php endif; ?>

                                    <input type="hidden" name="product_id" value="<?php echo $stock_value->product_id; ?>">
                                    <input type="hidden" name="warehouse_id_<?php echo $stock_value->warehouse_id; ?>" value="<?php echo $stock_value->warehouse_id; ?>">

                            <?php endforeach; ?>
                                    <td><input type="submit" name="submit" class="button-primary" value="<?php esc_attr_e('Atualizar') ?>" /></td>
                                </form>
                    </table>
                </td>
              </tr>
            </tbody>
        </table>
        <?php

        // Mostrar extras do produto
        $children = search_children($table_wp, $table_ws, $table_w, $id);

        if (!empty($children['id_product_children'])): ?>
            <table class="widefat">
                <thead class="style-thead">
                  <tr>
                    <th class="title_extras">Extras</th>
                  </tr>
                  <tr>
                    <th class="subtitle_extras">Extra</th>
                    <?php foreach ($list_warehouses[0] as $key => $value): ?>
                        <th class="subtitle_extras"><?php echo $value->name ?></th>
                    <?php endforeach;?>
                    <th class="subtitle_extras">Editar</th>
                  </tr>
                </thead>
                <tbody>
                    <!-- PAI -->
                    <?php $id_parent = $children['id_parent'];
                    if($id_parent > 0):
                        $name_parent = $wpdb->get_results("SELECT name FROM $table_wp WHERE product_id = $id_parent");
                        $name_parent = $name_parent[0]->name; ?>
                        <tr>
                            <td><a href="?page=warehouse_stocks&amp;tab=search&id=<?php echo $id_parent; ?>"><?php echo $name_parent .' (Produto Original)' ?></a></td>

                            <?php $stock = $wpdb->get_results("SELECT stock FROM $table_ws JOIN $table_wp ON $table_wp.id = $table_ws.product_id WHERE $table_wp.product_id = $id_parent ORDER BY warehouse_id");

                            foreach ($stock as $id => $stock_value): ?>
                                <td <?php echo ($stock_value->stock == '0' ? 'class="without_stock"' : ($stock_value->stock < '5' ? 'class="danger_stock"' : 'class="with_stock"')) ?>  ><?php echo $stock_value->stock ?></td>
                            <?php endforeach; ?>

                            <td><a href="?page=warehouse_stocks&amp;tab=search&id=<?php echo $id_parent; ?>"><span class="dashicons dashicons-edit"></span></a></td>
                        </tr>
                    <?php else:
                        $name_parent = $name_product;
                    endif; ?>

                    <!-- FILHOS -->
                    <?php foreach ($children['id_product_children'] as $key => $value):
                        $value_id = $value["id_children"];
                        $value_id_original = $value["id_children_original_id"]; ?>
                        <tr>
                            <?php $name = $wpdb->get_results("SELECT name FROM $table_wp WHERE id = $value_id"); ?>
                            <td> <a href="?page=warehouse_stocks&amp;tab=search&id=<?php echo $value_id_original; ?>"><?php echo str_replace($name_parent.' -', "", $name[0]->name);?> </a></td>

                            <?php $stock = $wpdb->get_results("SELECT stock FROM $table_ws WHERE product_id = $value_id ORDER BY warehouse_id");
                            foreach ($stock as $id => $stock_value): ?>
                                <td <?php echo ($stock_value->stock == '0' ? 'class="without_stock"' : ($stock_value->stock < '5' ? 'class="danger_stock"' : 'class="with_stock"')) ?>  ><?php echo $stock_value->stock ?></td>
                            <?php endforeach; ?>

                            <td><a href="?page=warehouse_stocks&amp;tab=search&id=<?php echo $value_id_original; ?>"><span class="dashicons dashicons-edit"></span></a></td>
                        </tr>
                    <?php endforeach; ?>

                </tbody>
            </table>
        <?php elseif ($children === 'Id not sync'):
            ?><div class="error"><p><strong><?php _e('ATENÇÃO: Este produto não está sincronizado com este site. Logo a sua gestão de stock não está disponível.', 'menu-test' ); ?></strong></p></div>
        <?php endif; ?>
<?php else:
    ?><div class="error"><p><strong><?php _e('ID inexistente.', 'menu-test' ); ?></strong></p></div><?php
endif;


function search_children($table_wp, $table_ws, $table_w, $id){
    global $wpdb;
    $id_product_children = [];
    $site = get_current_blog_id();
    $original = $id;

    // Procurar id do site andebol7
    if ($site != 1) {
        $table_pm = GLOBAL_PREFIX.'postmeta';

        $meta_key = 'wmpcs_synced_post_id_on_site_'.$site;
        $data = $wpdb->get_results("SELECT * FROM $table_pm WHERE post_id = $id AND meta_key = '$meta_key'");
        $id   =  $data[0]->meta_value;
    }

    if (is_null($id)) {
        // return porque nao está sync
        return 'Id not sync';
    }

    $id_parent = get_post($id);
    $id_parent = $id_parent->post_parent;

    if ($id_parent > 0)
    {
        $args = array(
            'post_parent' => $id_parent,
            'post_type'   => 'product_variation',
            'numberposts' => -1
        );
    }
    else
    {
        $args = array(
            'post_parent' => $id,
            'post_type'   => 'product_variation',
            'numberposts' => -1
        );
    }

    $variations_array = get_children($args);

    foreach ($variations_array as $key => $value) {

        // Procurar id do site andebol7
        if ($site != 1) {
            $data        =  $wpdb->get_results("SELECT * FROM $table_pm WHERE meta_value = $value->ID AND meta_key = '$meta_key'");
            $value->ID   =  $data[0]->post_id;
        }

        $id_children             = $wpdb->get_results("SELECT id, product_id FROM $table_wp WHERE product_id = $value->ID");
        $id_children_original_id = $id_children[0]->product_id;
        $id_children             = $id_children[0]->id;

        $wpdb->get_results("SELECT * FROM $table_ws JOIN $table_wp ON $table_wp.id = $table_ws.product_id WHERE $table_ws.product_id = $id_children");

        $id_product_children[] = array('id_children' => $id_children, 'id_children_original_id' => $id_children_original_id);
    }

    if ($site != 1) {
        $data        = $wpdb->get_results("SELECT * FROM $table_pm WHERE meta_value = $id_parent AND meta_key = '$meta_key'");
        $id_parent   =  $data[0]->post_id;
    }

    $all_data = array('id_product_children' => $id_product_children, 'id_parent' => $id_parent );

    return $all_data;
}


/**
 * Mudar stock das traduções
 */
function stock_wpml($id_wpml, $blog_id, $ts, $s, $in_stock, $ss)
{
    global $wpdb;

    if (!is_null($blog_id)) {
        $table_it  = GLOBAL_PREFIX.$blog_id.'_icl_translations';
        $table_pm  = GLOBAL_PREFIX.$blog_id.'_postmeta';
    }
    else
    {
        $table_it  = GLOBAL_PREFIX.'icl_translations';
        $table_pm  = GLOBAL_PREFIX.'postmeta';
    }

    $info          = $wpdb->get_results("SELECT * FROM $table_it WHERE element_id = $id_wpml");
    $id_sync_trid  = $info[0]->trid;
    $id_traduction = $wpdb->get_results("SELECT * FROM $table_it WHERE trid = $id_sync_trid and element_id <> $id_wpml");
    foreach ($id_traduction as $k => $v) {
        $wpdb->get_results("UPDATE $table_pm SET meta_value = $ts WHERE post_id = $v->element_id AND meta_key = '$s'");

        if ($in_stock) {
            $wpdb->get_results("UPDATE $table_pm SET meta_value = 'instock' WHERE post_id = $v->element_id AND meta_key = '$ss'");
        }
        else {
            $wpdb->get_results("UPDATE $table_pm SET meta_value = 'outofstock' WHERE post_id = $v->element_id AND meta_key = '$ss'");
        }
    }
}

function insert_help_stock($stock, $id, $warehouse_id)
{
    global $wpdb;

    $table_help = GLOBAL_PREFIX.'warehouses_help_stock';
    $exists     = $wpdb->get_results("SELECT stock FROM $table_help WHERE product_id = $id AND warehouse_id = $warehouse_id");

    if (empty($exists)) {
        $wpdb->query($wpdb->prepare
        (
          "
            INSERT INTO $table_help
            (`product_id`, `warehouse_id`, `stock`)
            VALUES (%d, %d, %d);
          ",
          array
          (
            $id,
            $warehouse_id,
            $stock
          )
        )
      );
    }
    else {
        $wpdb->get_results("UPDATE $table_help SET stock = $stock WHERE product_id = $id AND warehouse_id = $warehouse_id");
    }
}
