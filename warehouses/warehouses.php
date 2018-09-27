<?php defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
/**
* Plugin Name: Warehouses
* Description: MultiWarehouse Woocommerce
* Version: 1.0
* Author: Joel Carvalho
**/

// Variveis globais
define('URL',plugins_url(  "/", __FILE__));
define('PATH',plugin_dir_path(  __FILE__));
define('GLOBAL_PREFIX', 'wp_');
// define('GLOBAL_PREFIX', 'a7ptwp_');

/** Menu e submenus. */
function my_plugin_menu() {
  add_menu_page('Opções Armazéns', 'Armazéns', 'manage_options', 'list-warehouses', 'list_warehouse', 'dashicons-store' );
  add_submenu_page('list-warehouses', 'Adicionar Armazém', 'Novo Armazém', 'manage_options', 'new_warehouse', 'new_warehouse');
  add_submenu_page('list-warehouses', 'Stock p/armazém', 'Stocks', 'manage_options', 'warehouse_stocks', 'warehouse_stocks');
}

/** Adicionar à dashboard */
// add_action( 'network_admin_menu', 'my_plugin_menu' );
add_action('admin_menu', 'my_plugin_menu' );
add_action('admin_head','resources_styles');


function resources_styles(){
  wp_enqueue_style('resources_styles_css', URL. "css/style.css");
  wp_enqueue_script('resources_files_js', URL. "js/master.js");
}

register_activation_hook( __FILE__, 'warehouse_table');

function list_warehouse()
{
  include_once(PATH.'includes/list_warehouse.php');
}

function new_warehouse()
{
  include_once(PATH.'includes/new_warehouse.php');
}

function warehouse_stocks()
{
  include_once(PATH.'includes/warehouse_stocks.php');
}

// Verificar se existe produtos criados que não têm gestão de stock e apagar aqueles que já foram apagados
$site       = get_current_blog_id();
$backoffice = preg_match("/\b(\w*wp-admin\w*)\b/", $_SERVER['REQUEST_URI']);

function action_woocommerce_new_product_variation() {
  global $wpdb;
  global $post;
  $site     = get_current_blog_id();
  $table_pm = GLOBAL_PREFIX.'postmeta';
  $ms       = '_manage_stock';
  $table    = GLOBAL_PREFIX.'warehouses_products';

  if ($site == 1) {
    if (isset($post->ID)) {
      $args = array(
        'post_parent' => $post->ID,
        'post_type'   => 'product_variation',
        'numberposts' => -1
      );
      $variations_array = get_children($args);

      if (!empty($variations_array)) {
        $parent_id = $post->ID;

        foreach($variations_array as $vars) {
          // Verificar se já existe na bd
          $id        = $vars->ID;
          $r         = search_product($id);

          // Se não existe os filhos
          if (empty($r)) {

            // Inserir variação
            $wpdb->query($wpdb->prepare
              (
                "
                  INSERT INTO $table
                  (`product_id`, `name`)
                  VALUES (%d, %s);
                ",
                array
                (
                  $id,
                  $vars->post_title
                )
              )
            );

            $wpdb->get_results("UPDATE $table_pm SET meta_value = 'yes' WHERE post_id = $id AND meta_key = '$ms'");

            insert_all_warehouses($id, $table);
          }
          else
          {
            $name = $vars->post_title;
            $wpdb->get_results("UPDATE $table SET name = '$name' WHERE product_id = $id");
          }
        }

        // Se não existe o pai
        $r        = search_product($parent_id);
        if (empty($r)) {

          // Inserir pai da variação
          $wpdb->query($wpdb->prepare
            (
              "
                INSERT INTO $table
                (`product_id`, `name`)
                VALUES (%d, %s);
              ",
              array
              (
                $parent_id,
                $post->post_title
              )
            )
          );

          $wpdb->get_results("UPDATE $table_pm SET meta_value = 'yes' WHERE post_id = $parent_id AND meta_key = '$ms'");

          insert_all_warehouses($parent_id, $table);
        }
        else
        {
          $name = $post->post_title;
          $wpdb->get_results("UPDATE $table SET name = '$name' WHERE product_id = $parent_id");
        }
      }
      else
      {
        if ($post->post_type === 'product') {
          $id = $post->ID;
          $r = search_product($id);

          // Se não existe
          if (empty($r)) {
            $wpdb->query($wpdb->prepare
              (
                "
                  INSERT INTO $table
                  (`product_id`, `name`)
                  VALUES (%d, %s);
                ",
                array
                (
                  $id,
                  $post->post_title
                )
              )
            );

            $wpdb->get_results("UPDATE $table_pm SET meta_value = 'yes' WHERE post_id = $id AND meta_key = '$ms'");

            insert_all_warehouses($id, $table);
          }
          else
          {
            $name = $post->post_title;
            $wpdb->get_results("UPDATE $table SET name = '$name' WHERE product_id = $id");
          }
        }
      }
    }
  }
}
add_action('wp_insert_post', 'action_woocommerce_new_product_variation');

// Procurar id produto na tabela de produtos
function search_product($id){
  global $wpdb;
  $table = GLOBAL_PREFIX.'warehouses_products';
  $wpdb->get_results("SELECT * FROM $table WHERE product_id = $id");

  return $wpdb->last_result;
}

// Verificar se os armazens já tem este produto
function insert_all_warehouses($id, $table) {
  global $wpdb;

  $table_w  = GLOBAL_PREFIX.'warehouses';
  $id_new   = $wpdb->get_results("SELECT id FROM $table WHERE product_id = $id");
  $wpdb->get_results("SELECT id FROM $table_w ORDER BY id");
  $list_warehouses = $wpdb->last_result;

  foreach ($list_warehouses as $warehouse_value) {
    $warehouses_products_id = search_product_stock($id_new[0]->id, $warehouse_value->id);
    if(empty($warehouses_products_id))
    {
      insert_stock_warehouse($id_new[0]->id, $warehouse_value->id);
    }
  }
}

// Procurar id produto na tabela de stocks
function search_product_stock($id, $wid){
  global $wpdb;
  $table = GLOBAL_PREFIX.'warehouses_stock';
  $wpdb->get_results("SELECT * FROM $table WHERE product_id = $id AND warehouse_id = $wid");

  return $wpdb->last_result;
}

// Inserir cada produto em casa armazém
function insert_stock_warehouse($product_id, $warehouse_id)
{
  global $wpdb;
  $table_ws = GLOBAL_PREFIX.'warehouses_stock';

  $wpdb->query($wpdb->prepare
    (
      "
        INSERT INTO $table_ws
        (`product_id`, `warehouse_id`, `stock`)
        VALUES (%d, %d, %d);
      ",
      array
      (
        $product_id,
        $warehouse_id,
        0
      )
    )
  );
}

function action_woocommerce_delete_product() {
  $site = get_current_blog_id();
  global $post;
  global $wpdb;

  if (($site == 1 && $post->post_status == 'trash') && $post->post_type == 'product')  {
    $id     = $post->ID;

    $table1 = GLOBAL_PREFIX.'warehouses_products';
    $table2 = GLOBAL_PREFIX.'warehouses_stock';

    // Apagar gestão de stock pai
    $wpdb->get_results("SELECT id FROM $table1 WHERE product_id = $id");
    $wpdb->delete($table2, array('product_id' => $wpdb->last_result[0]->id));
    $wpdb->delete($table1, array('product_id' => $id));

    // Apagar gestão de stock das variações
    $args = array(
      'posts_per_page'   => '-1',
      'post_type'        => 'product_variation',
      'post_status'      => 'trash',
      'post_parent'      => $id,
    );

    $post_variations = get_posts($args, ARRAY_A);

    if (!empty($post_variations)) {
      foreach ($post_variations as $key => $post_variation) {
        $wpdb->get_results("SELECT id FROM $table1 WHERE product_id = $post_variation->ID");
        $wpdb->delete($table2, array('product_id' => $wpdb->last_result[0]->id));
        $wpdb->delete($table1, array('product_id' => $post_variation->ID));
      }
    }

    // Apagar produtos que estão sincronizados
    foreach (get_sites() as $key => $value) {
      $meta_key = 'wmpcs_synced_post_id_on_site_1';
      if($value->blog_id != 1){
        $table_pm = GLOBAL_PREFIX.$value->blog_id.'_postmeta';
        $table_p  = GLOBAL_PREFIX.$value->blog_id.'_posts';
        $data     = $wpdb->get_results("SELECT * FROM $table_pm WHERE meta_value = $id AND meta_key = '$meta_key'");

        if (!empty($data)) {
          $delete_id = $data[0]->post_id;

          // Ver tipo de produto
          $variations_array = $wpdb->get_results("SELECT * FROM $table_p WHERE post_parent = $delete_id AND post_type = 'product_variation'");

          // Se tiver variações
          if (!empty($variations_array)) {
            foreach($variations_array as $vars){
              $id_var = $vars->ID;

              delete_translations_sites_sync($id_var, $value->blog_id);

              // Apagar variações
              switch_to_blog($value->blog_id);
              wp_delete_post($id_var);
              restore_current_blog();
            }
          }

          delete_translations_sites_sync($delete_id, $value->blog_id);

          // Apagar produto principal
          switch_to_blog($value->blog_id);
          wp_delete_post($delete_id);
          restore_current_blog();
        }
      }
    }
  }
}
add_action('delete_post', 'action_woocommerce_delete_product');

function delete_translations_sites_sync($id, $blog_id){
  global $wpdb;
  $table_it     = GLOBAL_PREFIX.$blog_id.'_icl_translations';
  $info         = $wpdb->get_results("SELECT * FROM $table_it WHERE element_id = $id");

  if (!empty($info)) {
    $id_trid    = $info[0]->trid;
    $traduction = $wpdb->get_results("SELECT * FROM $table_it WHERE trid = $id_trid AND element_id <> $id");

    // Apagar traduções
    if (!empty($traduction)) {
      foreach ($traduction as $traduction_value) {
        switch_to_blog($blog_id);
        wp_delete_post($traduction_value->element_id);
        restore_current_blog();
      }
    }
  }
}

// Adicionar lista de armazéns
add_action( 'add_meta_boxes', 'warehouses_list' );
function warehouses_list() {
  add_meta_box(
    'dropdown_warehouses_list', // ID
    'Atualizar stocks',  // Titulo
    'callback_warehouse_list',  // Callback - Preenchimento da caixa
    'shop_order', // Ecrã
    'side'  // Contexto
  );
}

// Callback - Informação a mostrar
function callback_warehouse_list( $post )
{
  global $wpdb;
  $value = get_post_meta( $post->ID, '_tracking_box', true );

  $table_w     = GLOBAL_PREFIX.'warehouses';
  $table_ws    = GLOBAL_PREFIX.'warehouses_stock';
  $table_wp    = GLOBAL_PREFIX.'warehouses_products';
  $table_wso   = GLOBAL_PREFIX.'warehouses_stock_order';

  $sql         = "SELECT * FROM $table_w";
  $sql_result  = $wpdb->get_results($sql, OBJECT);

  $order       = wc_get_order($post->ID);

  foreach ($order->get_items() as $item_key => $item_values):
    $item_data   = $item_values->get_data();
    $pid         = ($item_data['variation_id'] > 0 ? $item_data['variation_id'] : $item_data['product_id']);

    $site = get_current_blog_id();
    if ($site != 1) {
      $table_pm  = GLOBAL_PREFIX.'postmeta';
      $meta_key  = 'wmpcs_synced_post_id_on_site_'.$site;
      $table_icl = GLOBAL_PREFIX.$site.'_icl_translations';
      $id_synced = $wpdb->get_results("SELECT post_id FROM $table_pm WHERE meta_key = '$meta_key' AND meta_value = $pid");
      if (empty($id_synced)) {
        $final_id = get_post_meta($pid, '_wcml_duplicate_of_variation');
        $final_id = $final_id[0];
        $final_id = $wpdb->get_results("SELECT post_id FROM $table_pm WHERE meta_key = '$meta_key' AND meta_value = $final_id");
        $pid      = $final_id[0]->post_id;
      }
      else {
        $pid      = $id_synced[0]->post_id;
      }
    }
    else {
      $table_icl  = GLOBAL_PREFIX.'icl_translations';
      $id_tra     = $wpdb->get_results("SELECT source_language_code as lang, trid FROM $table_icl WHERE element_id = $pid");
      $trid       = $id_tra[0]->trid;
      $id_tra     = $id_tra[0]->lang;

      if (!is_null($id_tra)) {
        $pid      = $wpdb->get_results("SELECT element_id as id FROM $table_icl WHERE trid = $trid AND source_language_code is NULL");
        $pid      = $pid[0]->id;
      }
    }
    ?>

    <p>
      <label for="warehouses_meta_box_<?php echo $item_key ?>"><?php echo $item_data['name']; ?> </label>
      <select name='warehouses_meta_box_<?php echo $item_key ?>' id='warehouses_meta_box_<?php echo $item_key ?>'>

        <?php $check_was_reduce = $wpdb->get_results("SELECT * FROM $table_wso WHERE product_id = $pid AND order_id = $post->ID AND id_site = $site");

        if (!empty($check_was_reduce)) {
          $wid                   = $check_was_reduce[0]->warehouse_id;
          $name_warehouse_reduce = $wpdb->get_results("SELECT name FROM $table_w WHERE id = $wid");
        }
        else{
          $name_warehouse_reduce = NULL;
        }?>

        <option value="default"><?php echo (empty($name_warehouse_reduce) ? 'Escolha um armazém...' : 'Abatido em: '.$name_warehouse_reduce[0]->name) ?></option>

        <!-- Se já foi abatido a algum armaźem no lista os armazéns -->
        <?php
        if (empty($name_warehouse_reduce)):
          foreach ($sql_result as $warehouses):
            $sql_stock   = "SELECT $table_ws.stock FROM $table_ws JOIN $table_wp ON $table_wp.id = $table_ws.product_id WHERE $table_wp.product_id = $pid AND $table_ws.warehouse_id = $warehouses->id";
            $sql_r_stock = $wpdb->get_results($sql_stock, OBJECT); ?>
            <option <?php echo ($sql_r_stock[0]->stock == 0 ? 'Disabled' : '')?> value="<?php echo esc_attr($warehouses->id); ?>"><?php echo esc_html($warehouses->name).' ('.($sql_r_stock[0]->stock).')'; ?></option>
          <?php endforeach;
        endif;?>
      </select>
    </p>
  <?php endforeach;
}

// Guardar
add_action( 'save_post', 'save_id_warehouse' );

function save_id_warehouse($post_id) {
  global $wpdb;

  $order = wc_get_order($post_id);
  if ($order != false) {
    foreach ($order->get_items() as $item_key => $item_values):
      $item_data    = $item_values->get_data();
      $product_id   = $item_data['product_id'];
      $variation_id = $item_data['variation_id'];
      $quantity     = $item_data['quantity'];

      // Produto Variável
      if ($variation_id > 0) {
        $product_id = $variation_id;
      }

      $site = get_current_blog_id();
      if ($site != 1) {
        $table_pm     = GLOBAL_PREFIX.'postmeta';
        $meta_key     = 'wmpcs_synced_post_id_on_site_'.$site;
        $id_synced    = $wpdb->get_results("SELECT post_id FROM $table_pm WHERE meta_key = '$meta_key' AND meta_value = $product_id");
        if (empty($id_synced)) {
          $final_id   = get_post_meta($product_id, '_wcml_duplicate_of_variation');
          $final_id   = $final_id[0];
          $final_id   = $wpdb->get_results("SELECT post_id FROM $table_pm WHERE meta_key = '$meta_key' AND meta_value = $final_id");
          $product_id = $final_id[0]->post_id;
        }
        else {
          $product_id = $id_synced[0]->post_id;
        }
      }

      if ($_POST['warehouses_meta_box_'.$item_key] != 'default' && $order->get_status() === 'completed') {
        $id_warehouse = $_POST['warehouses_meta_box_'.$item_key];

        update_stock_order($id_warehouse, $product_id, $quantity, 'completed');

        // Guardar armazém onde o stock foi abatido
        save_warehouse_stock($id_warehouse, $product_id, $post_id, $quantity, $site);
      }
      elseif ($order->get_status() === 'cancelled') {
        $table_so = GLOBAL_PREFIX.'warehouses_stock_order';
        $if_exist = $wpdb->get_results("SELECT * FROM $table_so WHERE (id_site = $site AND order_id = $post_id) AND (product_id = $product_id AND qty = $quantity)");

        // Se alguma vez a encomenda foi concluída, remover esse registo
        if (!empty($if_exist)) {
          foreach ($if_exist as $key => $value) {
            $wpdb->delete($table_so, array('order_id' => $post_id, 'id_site' => $site, 'warehouse_id' => $value->warehouse_id));
            update_stock_order($value->warehouse_id, $product_id, $quantity, 'cancelled');
          }
        }
      }
    endforeach;
  }

  // Traduções
  $site = get_current_blog_id();
  if ($site == 1) {
    // Produto Tradução
    // manage_translations_site($post_id);
  }
}

function update_stock_order($id_warehouse, $original_id, $quantity, $status_order){
  global $wpdb;

  $table_ws   = GLOBAL_PREFIX.'warehouses_stock';
  $table_wp   = GLOBAL_PREFIX.'warehouses_products';
  $table_help = GLOBAL_PREFIX.'warehouses_help_stock';

  // Se não for encomenda do andebol7, vai buscar o id dos produtos à andebol7
  // $current_site = get_current_blog_id();
  // if ($current_site != 1) {
  //   $id_synced = get_post_meta($original_id, 'wmpcs_synced_post_id_on_site_1', TRUE);
  //   if (!empty($id_synced)) {
  //     $original_id = $id_synced;
  //   }
  //   else // Se não está sincronizado é porque é TRADUÇÃO
  //   {
  //     $meta_key = 'wmpcs_synced_post_id_on_site_'.$current_site;
  //     $table_pm = GLOBAL_PREFIX.'postmeta';
  //     $info     = $wpdb->get_results("SELECT post_id FROM $table_pm WHERE meta_value = $original_id AND meta_key = '$meta_key'");
  //     if (!empty($info)) {
  //       $original_id = $info[0]->post_id;
  //     }
  //   }
  // }

  // Procurar id da tabela w.stocks
  $product_id = $wpdb->get_results("SELECT id FROM $table_wp WHERE product_id = $original_id");
  $product_id = $product_id[0]->id;

  // Retirar quantidade ao stock existente
  $stock  = $wpdb->get_results("SELECT stock FROM $table_ws WHERE product_id = $product_id AND warehouse_id = $id_warehouse");
  $stock  = $stock[0]->stock;

  if ($status_order == 'completed') {
    $stock = bcsub($stock, $quantity, 0);
  }
  elseif ($status_order == 'cancelled') {
    $stock = bcadd($stock, $quantity, 0);
  }

  $wpdb->get_results("UPDATE $table_ws SET stock = $stock WHERE product_id = $product_id AND warehouse_id = $id_warehouse");

  // Colocar stock na tabela ajuda
  $wpdb->get_results("UPDATE $table_help SET stock = $stock WHERE product_id = $product_id AND warehouse_id = $id_warehouse");

  // Atualizar stock total pelos armazéns existentes
  $wpdb->get_results("SELECT stock FROM $table_ws WHERE product_id = $product_id");
  $ts = 0;
  $s  = '_stock';
  $ss = '_stock_status';
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
      $data = $wpdb->get_results("SELECT * FROM $table_pm WHERE post_id = $original_id AND meta_key = '$meta_key'");
      if (!empty($data)) {
        $id_current_site[] = $data;
      }
    }

    foreach ($id_current_site as $key => $value) {
      foreach (get_sites() as $k => $v) {
        if ($value[0]->meta_key == 'wmpcs_synced_post_id_on_site_'.$v->blog_id) {
          $table = GLOBAL_PREFIX.$v->blog_id.'_postmeta';
          $table = ($value->blog_id == 1 ? GLOBAL_PREFIX.'postmeta' : $table);
          $id_update = $value[0]->meta_value;
          $meta_key  = $value[0]->meta_key;

          $wpdb->get_results("UPDATE $table SET meta_value = $ts WHERE post_id = $id_update AND meta_key = '$s'");

          // Mudar stock das traduções
          stock_wpml_warehouse($id_update, $v->blog_id, $ts, $s, $in_stock, $ss);

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
    $wpdb->get_results("UPDATE $table SET meta_value = $ts WHERE post_id = $original_id AND meta_key = '$s'");

    // Mudar stock das traduções
    stock_wpml_warehouse($original_id, null, $ts, $s, $in_stock, $ss);

    if ($in_stock) {
      $wpdb->get_results("UPDATE $table SET meta_value = 'instock' WHERE post_id = $original_id AND meta_key = '$ss'");
    }
    else {
      $wpdb->get_results("UPDATE $table SET meta_value = 'outofstock' WHERE post_id = $original_id AND meta_key = '$ss'");
    }
  }
  else
  {
    // Atualizar nos restantes sites
    foreach (get_sites() as $key => $value) {
      $id_synced = get_post_meta($original_id, 'wmpcs_synced_post_id_on_site_'.$value->blog_id, TRUE);
      if (!empty($id_synced)) {
        $table = GLOBAL_PREFIX.$value->blog_id.'_postmeta';
        $table = ($value->blog_id == 1 ? GLOBAL_PREFIX.'postmeta' : $table);

        $wpdb->get_results("UPDATE $table SET meta_value = $ts WHERE post_id = $id_synced AND meta_key = '$s'");

        // Mudar stock das traduções
        stock_wpml_warehouse($id_synced, $value->blog_id, $ts, $s, $in_stock, $ss);

        if ($in_stock) {
          $wpdb->get_results("UPDATE $table SET meta_value = 'instock' WHERE post_id = $id_synced AND meta_key = '$ss'");
        }
        else {
          $wpdb->get_results("UPDATE $table SET meta_value = 'outofstock' WHERE post_id = $id_synced AND meta_key = '$ss'");
        }
      }
    }

    // Mudar stock das traduções
    stock_wpml_warehouse($original_id, null, $ts, $s, $in_stock, $ss);
  }

  wc_update_product_stock($original_id, $ts);
}

function save_warehouse_stock($id_warehouse, $id, $order_id, $quantity, $id_site)
{
  global $wpdb;
  $table_wso = GLOBAL_PREFIX.'warehouses_stock_order';

  $wpdb->query($wpdb->prepare
    (
      "
        INSERT INTO $table_wso
        (`product_id`, `warehouse_id`, `order_id`, `qty`, `id_site`)
        VALUES (%d, %d, %d, %d, %d);
      ",
      array
      (
        $id,
        $id_warehouse,
        $order_id,
        $quantity,
        $id_site
      )
    )
  );
}

/**
 * Mudar stock das traduções
 */
function stock_wpml_warehouse($id_wpml, $blog_id, $ts, $s, $in_stock, $ss)
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
  $id_traduction = $wpdb->get_results("SELECT * FROM $table_it WHERE trid = $id_sync_trid");
  foreach ($id_traduction as $k => $v) {
    if ($v->element_id != $id_wpml) {
      $wpdb->get_results("UPDATE $table_pm SET meta_value = $ts WHERE post_id = $v->element_id AND meta_key = '$s'");

      if ($in_stock) {
        $wpdb->get_results("UPDATE $table_pm SET meta_value = 'instock' WHERE post_id = $v->element_id AND meta_key = '$ss'");
      }
      else {
        $wpdb->get_results("UPDATE $table_pm SET meta_value = 'outofstock' WHERE post_id = $v->element_id AND meta_key = '$ss'");
      }
    }
  }
}

/* linhas 57
Caminho: woo-multisite-product-category-sync/inc/post-sync/class-wmpcs-post-sync
Dentro da função sync_post_to_site()
$post = get_post_meta($post_id, '_wcml_duplicate_of_variation');
// É tradução
if (!empty($post)) {
    $post      = get_post($post[0]);
    $post      = get_post($post->post_parent);
    $post_id   = $post->ID;
    $post_type = $post->post_type;

    // Se estiver sync é porque não está no id correto
    $new_id    = get_post_meta($post_id, WMPCS_Setting::$SYNCED_POST_ID_PREFIX . 1, TRUE);
    if (!empty($new_id)) {
        $post_id = $new_id;
    }
}

$post_pp = wp_get_post_parent_id($post_id);
if (!empty($post_pp)) {
    $new_id    = get_post_meta($post_pp, WMPCS_Setting::$SYNCED_POST_ID_PREFIX . 1, TRUE);
    $post_id   = $new_id;
    $post_type = get_post_type($post_pp);
}*/

/**
 * Sincronizar traduções
 */
function manage_translations_site($post_id)
{
  global $wpdb;

  $status_post = get_post_status($post_id);

  if ($status_post == 'publish') {
    foreach (get_sites() as $key => $value) {
      if ($value->blog_id != 1) {
        $id_synced = get_post_meta($post_id, 'wmpcs_synced_post_id_on_site_'.$value->blog_id, TRUE);

        // Se já estiver sincronizado
        if (!empty($id_synced)) {
          // Se tiver traduções
          $get_language_args = array('element_id' => $post_id, 'element_type' => 'post_product');
          $original_post_language_info = apply_filters( 'wpml_element_language_details', null, $get_language_args );
          $id_trid    = $original_post_language_info->trid;
          $lang       = $original_post_language_info->language_code;
          $table_it   = GLOBAL_PREFIX.'icl_translations';
          $traduction = $wpdb->get_results("SELECT * FROM $table_it WHERE trid = $id_trid AND element_id <> $post_id");

          // Há traduções
          if (!empty($traduction)) {
            foreach ($traduction as $traduction_value) {
              WMPCS_Post_Sync::sync_post_to_site($traduction_value->element_id, $value->blog_id);
              // testarInserir($traduction_value, $value->blog_id, $id_synced, $post_id, count($traduction));
            }

            WMPCS_Post_Sync::sync_post_to_site($post_id, $value->blog_id);

           add_translations($id_synced, $value->blog_id, $lang, $traduction, $post_id);
          }
        }
      }
    }
  }
}


function testarInserir($traduction_value, $blog_id, $id_synced, $post_id, $total_trad){
  global $wpdb;
  $table_it   = GLOBAL_PREFIX.$blog_id.'_icl_translations';

  // Ver se já está traduzido
  switch_to_blog($blog_id);
  $get_language_args = array('element_id' => $id_synced, 'element_type' => 'post_product');
  $original_post_language_info = apply_filters( 'wpml_element_language_details', null, $get_language_args );
  restore_current_blog();
  $id_trid        = $original_post_language_info->trid;
  $traduction     = $wpdb->get_results("SELECT * FROM $table_it WHERE trid = $id_trid AND element_id <> $id_synced");

  if (empty($traduction)) {
    // Pegar a informação do produto do $site_id
    switch_to_blog($blog_id);
    $post_details = get_post($id_synced);
    $post_details->ID = '';
    $new_post = wp_insert_post($post_details, true);
    restore_current_blog();

    $args = array(
      'post_parent' => $id_synced,
      'post_type'   => 'product_variation',
      'numberposts' => -1
    );
    switch_to_blog($blog_id);
    $variations_array = get_children($args);
    restore_current_blog();

    if (!empty($variations_array)) {
      foreach ($variations_array as $value) {
        $get_language_args_children = array('element_id' => $value->ID, 'element_type' => 'post_product_variation');
        $original_post_language_info_children = apply_filters( 'wpml_element_language_details', null, $get_language_args );
        $id_trid_children   = $original_post_language_info_children->trid;
        $lang_children      = $original_post_language_info_children->language_code;
        $value->ID          = '';
        $value->post_parent = $new_post;
        switch_to_blog($blog_id);
        $new_post_children  = wp_insert_post($value, true);
        restore_current_blog();
      }
    }

    if ($new_post) {
      insert_icl_translations($new_post, $table_it, $traduction_value->language_code, $traduction_value->source_language_code, 'post_product', $id_trid);
      if ($new_post_children) {
        insert_icl_translations($new_post_children, $table_it, $traduction_value->language_code, $traduction_value->source_language_code, 'post_product_variation', $id_trid_children);
      }
    }
  }
}

if ($backoffice) {
  //MOSTRAR LINGUA - TRADUÇÕES - Descomentar
  $args = array(
    'posts_per_page'   => '-1',
    'post_type'        => 'product',
    'post_status'      => 'publish'
  );

  $args_pv = array(
    'posts_per_page'   => '-1',
    'post_type'        => 'product_variation',
    'post_status'      => 'publish'
  );

  $all_products           = get_posts($args);
  // $all_products_variation = get_posts($args_pv);

  foreach ($all_products as $value_all) {
    if ($value_all->post_status == 'publish') {
      $id_synced = get_post_meta($value_all->ID, 'wmpcs_synced_post_id_on_site_1', TRUE);
      if($id_synced){
        $table_it   = GLOBAL_PREFIX.'icl_translations';
        $data       = $wpdb->get_results("SELECT * FROM $table_it WHERE element_id = $id_synced");
        $id_trid    = $data[0]->trid;
        $lang       = $data[0]->language_code;
        $site       = get_current_blog_id();
        add_translations($value_all->ID, $site, $lang, '', '');
      }
    }
  }
}
/**
 * Adicionar traduções na tabela 'icl_translations'
 */
function add_translations($id, $blog_id, $lang, $traduction, $post_id)
{
  global $wpdb;

  $table_it  = GLOBAL_PREFIX.'icl_translations';
  $table_icl = GLOBAL_PREFIX.$blog_id.'_icl_translations';
  $data      = $wpdb->get_results("SELECT * FROM $table_icl WHERE element_id = $id");

  // Produto Normal
  if (empty($data)) {
    $new_max_trid = $wpdb->get_results("SELECT max(trid) as new_max_trid FROM $table_icl");
    $new_max_trid = $new_max_trid[0]->new_max_trid + 1;

    insert_icl_translations($id, $table_icl, $lang, null, 'post_product', $new_max_trid);
  }

  $args = array(
    'post_parent' => $id,
    'post_type'   => 'product_variation',
    'numberposts' => -1
  );

  switch_to_blog($blog_id);
  $variations_array = get_children($args);
  restore_current_blog();

  // Produto Variável
  if (!empty($variations_array)) {
    $new_max_trid = $wpdb->get_results("SELECT max(trid) as new_max_trid FROM $table_icl");
    $new_max_trid = $new_max_trid[0]->new_max_trid + 1;

    foreach ($variations_array as $value_va) {
      $data_var = $wpdb->get_results("SELECT * FROM $table_icl WHERE element_id = $value_va->ID");
      if (empty($data_var)) {
        insert_icl_translations($value_va->ID, $table_icl, $lang, null, 'post_product_variation', $new_max_trid);
      }
    }
  }

  // Traduções Produto Normal
  foreach ($traduction as $traduction_value) {
    $language_code          = $traduction_value->language_code;
    $source_language_code   = $traduction_value->source_language_code;
    $id_traduction          = $traduction_value->element_id;

    $id_synced = get_post_meta($id_traduction, 'wmpcs_synced_post_id_on_site_'.$blog_id, TRUE);

    if (!empty($id_synced)) {
      $data_id_synced       = $wpdb->get_results("SELECT * FROM $table_icl WHERE element_id = $id_synced");
      if (empty($data_id_synced)) {
        $trid_traduction    = $wpdb->get_results("SELECT * FROM $table_icl WHERE element_id = $id");
        $trid_traduction    = $trid_traduction[0]->trid;
        insert_icl_translations($id_synced, $table_icl, $language_code, $source_language_code, 'post_product', $trid_traduction);
      }
    }

    // Traduções Produto Variável
    $args = array(
      'post_parent' => $id_synced,
      'post_type'   => 'product_variation',
      'numberposts' => -1
    );

    switch_to_blog($blog_id);
    $variations_array_tra = get_children($args);
    restore_current_blog();

    if (!empty($variations_array_tra)) {
      foreach ($variations_array_tra as $value_va_tra) {
        $data_var_tr = $wpdb->get_results("SELECT * FROM $table_icl WHERE element_id = $value_va_tra->ID");
        if (empty($data_var_tr)) {
          foreach ($variations_array as $value_va) {
            $data_var = $wpdb->get_results("SELECT * FROM $table_icl WHERE element_id = $value_va->ID");
            if (!empty($data_var)) {
              $trid_traduction_var = $data_var[0]->trid;
              insert_icl_translations($value_va_tra->ID, $table_icl, $language_code, $source_language_code, 'post_product_variation', $trid_traduction_var);
            }
          }
        }
      }
    }
  }
}


function insert_icl_translations($id, $table_icl, $lang, $source_lang, $type, $trid)
{
  global $wpdb;

  if (empty($source_lang)) {
   $wpdb->query($wpdb->prepare(" INSERT INTO $table_icl(`element_type`, `element_id`, `trid`, `language_code`) VALUES (%s, %d, %d, %s); ", array($type, $id, $trid, $lang))
    );
  }
  else{
    $wpdb->query($wpdb->prepare(" INSERT INTO $table_icl(`element_type`, `element_id`, `trid`, `language_code`, `source_language_code`) VALUES (%s, %d, %d, %s, %s); ", array($type, $id, $trid, $lang, $source_lang))
    );
  }
}

// SYNC PRODUTOS L.49 INC->POST-SYNC->CLASS-WMPCS-POST-SYNC-> FUNCTION sync_post_to_site

// Adicionar classes nos botões de navegação
add_filter('next_posts_link_attributes', 'posts_link_attributes');
add_filter('previous_posts_link_attributes', 'posts_link_attributes');

function posts_link_attributes() {
  return 'class="button_navigation"';
}

// Mudar a cor das variações que não tem stock
add_action( 'woocommerce_before_variations_form', 'changeColorBackgroundVariations', 5 );

function changeColorBackgroundVariations() {
  global $product;

  $variations      = $product->get_available_variations();
  foreach($variations as $variation){

    $variation_id  = $variation['variation_id'];
    $variation_obj = new WC_Product_variation($variation_id);
    $stock         = $variation_obj->get_stock_quantity();
    $result        = woocommerce_get_product_terms($variation_obj);

    if (isset($variation["attributes"]["attribute_pa_tamanhos-textil"])) {
      $attribute   = $variation["attributes"]["attribute_pa_tamanhos-textil"];
    }
    elseif (isset($variation["attributes"]["attribute_pa_tamanhos-calcado"])) {
      $attribute   = $variation["attributes"]["attribute_pa_tamanhos-calcado"];
    }
    elseif (isset($variation["attributes"]["attribute_pa_tamanhos-bolas"])) {
      $attribute   = $variation["attributes"]["attribute_pa_tamanhos-bolas"];
    }
    elseif (isset($variation["attributes"]["attribute_pa_tamanhos-meias"])) {
      $attribute   = $variation["attributes"]["attribute_pa_tamanhos-meias"];
    }
    elseif (isset($variation["attributes"]["attribute_pa_size"])) {
      $attribute   = $variation["attributes"]["attribute_pa_size"];
    }

    /* Para ser melhor --->>> Passar attribute_name para o lugar do $attribute em baixo e fica resolvido
    echo "
          <script type='text/javascript'>
            jQuery(document).ready(function() {
              var attribute_name = jQuery('.variations .attribute_item .wr-custom-attribute').attr('data-attribute');
            });
          </script>
    ";*/

    if ($stock > 0) {
      $array[]     = array('name' => $attribute, 'stock' => 'yes');
    }
    else {
      $array[]     = array('name' => $attribute, 'stock' => 'no');
    }
  }

  foreach ($array as $key => $value) {
    if ($value['stock'] == 'no') {
      $var_name = $value['name'];
      echo "
            <script type='text/javascript'>
              jQuery(document).ready(function() {
                var var_name = '$var_name';
                jQuery('.wr-custom-attribute li').find('[data-value='+var_name+']').addClass('change_color_without_stock');
              });
            </script>
      ";
    }
  }
}

/** Injetar javascript
  * Se tiver algum utilizador com o login efeutado mostra o seu nome
*/
function hook_javascript(){
  $current_user = wp_get_current_user();
  $name         = $current_user->user_login;

  if($name) {
    echo "
          <script type='text/javascript'>
            jQuery(document).ready(function() {
              var var_name = '$name';
              jQuery('#name_user_logged div p').text(var_name);
            });
          </script>
        ";
  }
}

add_action('wp_head','hook_javascript');

// Adicionar link para os armazéns do produto
add_action( 'add_meta_boxes', 'function_add_link_warehouse' );

function function_add_link_warehouse() {
  add_meta_box(
    'id_link_warehouse', // ID
    'Stock Armazéns',  // Titulo
    'add_link_warehouse',  // Callback - Preenchimento da caixa
    'product', // Ecrã
    'side',  // Contexto
    'high'
  );
}

// Callback - Informação a mostrar
function add_link_warehouse( $post )
{
  $id   = $post->ID;
  $site = get_current_blog_id();

  // Pesquisar id do Andebol7
  if ($site != 1) {
    $id = get_post_meta($id, 'wmpcs_synced_post_id_on_site_1');

    foreach ($id as $key => $value) {
      $id = $value;
    }
  }

  ?><a class="text_link_warehouse" href="admin.php?page=warehouse_stocks&amp;tab=search&id=<?php echo $id; ?>"><span class="dashicons dashicons-edit"></span> Gerir Stocks</a><?php
}

// Descomentar quando stock da loja e o dos armazéns forem diferentes
// check_stock_correct();

function check_stock_correct(){
  global $wpdb;
  $table_stock   = GLOBAL_PREFIX.'warehouses_stock';
  $table_product = GLOBAL_PREFIX.'warehouses_products';

  $args = array(
    'posts_per_page'   => '-1',
    'post_type'        => 'product',
    'post_status'      => 'publish'
  );

  $args_pv = array(
    'posts_per_page'   => '-1',
    'post_type'        => 'product_variation',
    'post_status'      => 'publish'
  );

  $all_products           = get_posts($args);
  $all_products_variation = get_posts($args_pv);

  foreach ($all_products as $key => $product) {
    $my_stock = $wpdb->get_results("SELECT SUM($table_stock.stock) as stock_total, $table_product.* FROM $table_product JOIN $table_stock ON $table_stock.product_id = $table_product.id WHERE $table_product.product_id = $product->ID");

    $store_stock = get_post_meta( $product->ID, '_stock', true );

    if (!is_null($my_stock[0]->stock_total) && ($my_stock[0]->stock_total != $store_stock)) {
      file_put_contents('/home/joel/work/wpandebol7/log_different_stock_product.txt', "Original:".$my_stock[0]->product_id." ID:".$my_stock[0]->id." Meu stock:".$my_stock[0]->stock_total." Stock Loja:".$store_stock."\n", FILE_APPEND);

      // PRODUÇÂO: /home/a7pt/public_html/nome.txt
    }
  }

  foreach ($all_products_variation as $key => $product_var) {
    $my_stock = $wpdb->get_results("SELECT SUM($table_stock.stock) as stock_total, $table_product.* FROM $table_product JOIN $table_stock ON $table_stock.product_id = $table_product.id WHERE $table_product.product_id = $product_var->ID");

    $store_stock = get_post_meta( $product_var->ID, '_stock', true );

    if (!is_null($my_stock[0]->stock_total) && ($my_stock[0]->stock_total != $store_stock)) {
      file_put_contents('/home/joel/work/wpandebol7/log_different_stock_variations.txt', "Original:".$my_stock[0]->product_id." ID:".$my_stock[0]->id." Meu stock:".$my_stock[0]->stock_total." Stock Loja:".$store_stock."\n", FILE_APPEND);
    }
  }
}


// fix_tags();

/**
*** Habitualmente comentado
*** Função: Resolver danos nas etiquetas, isto é, quando não aparece etiquetas ou aparece números
**/
function fix_tags() {
  global $wpdb;

  $site = get_current_blog_id();

  if ($site != 1) {
    $table_terms_taxonomy     = GLOBAL_PREFIX.$site.'_term_taxonomy';
    $table_terms              = GLOBAL_PREFIX.$site.'_terms';
    $table_term_relationships = GLOBAL_PREFIX.$site.'_term_relationships';
    $table_termmeta           = GLOBAL_PREFIX.$site.'_termmeta';

    $args = array(
      'posts_per_page'   => '-1',
      'post_type'        => 'product',
      'post_status'      => 'publish'
    );

    $all_products = get_posts($args);

    foreach ($all_products as $key => $product) {
      $tags = $wpdb->get_results("
        SELECT *
        FROM $table_term_relationships
        JOIN $table_terms
        ON $table_terms.term_id = $table_term_relationships.term_taxonomy_id
        WHERE $table_term_relationships.object_id = $product->ID "
      );

      if(!empty($tags)) {
        foreach ($tags as $key => $tag) {
          if(is_numeric($tag->name) && is_numeric($tag->slug)) { // Se o valor for um número

            $is_tag = $wpdb->get_results("
              SELECT *
              FROM $table_terms_taxonomy
              WHERE term_id = $tag->name AND taxonomy = 'product_tag'"
            );

            if(!empty($is_tag)) { // É etiqueta
              foreach ($is_tag as $key => $t) {
                $true_tag         = $t->term_id;

                $update_real_name = $wpdb->get_results("
                  UPDATE $table_term_relationships
                  SET term_taxonomy_id   = $true_tag
                  WHERE term_taxonomy_id = $tag->term_id AND object_id = $product->ID"
                );
              }
            }
          }
        }
      }
    }
  }
}
