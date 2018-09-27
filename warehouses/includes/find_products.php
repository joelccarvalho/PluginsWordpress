<?php

// Verificar se existe produtos criados que não têm gestão de stock e apagar aqueles que já foram apagados
$site       = get_current_blog_id();
$backoffice = preg_match("/\b(\w*wp-admin\w*)\b/", $_SERVER['REQUEST_URI']);

if ($site == 1 && $backoffice) {
  check_exists_product();
}

function check_exists_product(){
  global $wpdb;
  $site      = get_current_blog_id();
  $table_w   = GLOBAL_PREFIX.'warehouses';
  $table_ws  = GLOBAL_PREFIX.'warehouses_stock';

  if ($site == 1) {
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

    $table = GLOBAL_PREFIX.'warehouses_products';

    $wpdb->get_results("SELECT product_id, name FROM $table");

    // Guardar em array os produtos da tabela de gestão de stocks
    foreach ($wpdb->last_result as $key => $value) {
      $array[] = $value->product_id;
      $array_name[] = $value->name;
    }

    // Procurar produtos
    foreach ($all_products as $key => $value_all) {
        $not_translation = check_is_translation($value_all->ID);

        if ($not_translation) {
            if ($value_all->post_status == 'publish') {
              $ap[] = $value_all->ID;
              // Se não exitir na tabela de gestão de produtos, insere
              if(!in_array($value_all->ID, $array)) {
                // Se o nome for diferente de vazio insere
                if (!empty($value_all->post_name)) {
                  $wpdb->query($wpdb->prepare
                    (
                      "
                        INSERT INTO $table
                        (`product_id`, `name`)
                        VALUES (%d, %s);
                      ",
                      array
                      (
                        $value_all->ID,
                        $value_all->post_title
                      )
                    )
                  );

                  $table_pm = GLOBAL_PREFIX.'postmeta';

                  // Manusear stock
                  $ms = '_manage_stock';
                  $wpdb->get_results("UPDATE $table_pm SET meta_value = 'yes' WHERE post_id = $value_all->ID AND meta_key = '$ms'");
                }
              }
              else {
                // Ver qual o id correspondente
                $wpdb->get_results("SELECT id, name FROM $table WHERE product_id = $value_all->ID");
                $id   = $wpdb->last_result[0]->id;
                $name = $wpdb->last_result[0]->name;

                // Se o nome foi mudado
                if ($name != $value_all->post_title) {
                  $name = $value_all->post_title;
                  $wpdb->get_results("UPDATE $table SET name = '$name' WHERE product_id = $value_all->ID");
                }

                $wpdb->get_results("SELECT id FROM $table_w");
                foreach ($wpdb->last_result as $k => $warehouse) {
                  $wpdb->get_results("SELECT * FROM $table_ws WHERE product_id = $id AND warehouse_id = $warehouse->id");
                  // Se não existir o produto num armazéns, insere
                  if (empty($wpdb->last_result)) {
                    file_put_contents('/home/joel/work/wpandebol7/log.txt', date('c')." ".$id." ".$warehouse->id."\n", FILE_APPEND);
                    insert_stock_product($id, $warehouse->id);
                  }
                }
              }
            }
        }
    }

    // Procurar produtos variáveis
    foreach ($all_products_variation as $key => $value_all_av) {
        $not_translation = check_is_translation($value_all_av->ID);

        if ($not_translation) {
            if ($value_all_av->post_status == 'publish') {
              $apv[] = $value_all_av->ID;
              // Se não exitir na tabela de gestão de produtos, insere
              if(!in_array($value_all_av->ID, $array)) {
                // Se o nome for diferente de vazio insere
                if (!empty($value_all_av->post_name)) {
                  $wpdb->query($wpdb->prepare
                    (
                      "
                        INSERT INTO $table
                        (`product_id`, `name`)
                        VALUES (%d, %s);
                      ",
                      array
                      (
                        $value_all_av->ID,
                        $value_all_av->post_title
                      )
                    )
                  );

                  $table_pm = GLOBAL_PREFIX.'postmeta';

                  // Manusear stock
                  $ms = '_manage_stock';
                  $wpdb->get_results("UPDATE $table_pm SET meta_value = 'yes' WHERE post_id = $value_all_av->ID AND meta_key = '$ms'");
                }
              }
              else {
                // Ver qual o id correspondente
                $wpdb->get_results("SELECT id, name FROM $table WHERE product_id = $value_all_av->ID");
                $id   = $wpdb->last_result[0]->id;
                $name = $wpdb->last_result[0]->name;

                // Se o nome foi mudado
                if ($name != $value_all_av->post_title) {
                  $name = $value_all_av->post_title;
                  $wpdb->get_results("UPDATE $table SET name = '$name' WHERE product_id = $value_all_av->ID");
                }

                $wpdb->get_results("SELECT id FROM $table_w");
                foreach ($wpdb->last_result as $k => $warehouse) {
                  $wpdb->get_results("SELECT * FROM $table_ws WHERE product_id = $id AND warehouse_id = $warehouse->id");
                  // Se não existir o produto num armazéns, insere
                  if (empty($wpdb->last_result)) {
                    insert_stock_product($id, $warehouse->id);
                  }
                }
              }
            }
        }
    }
  }

  // Todos os produtos a manusear stock
  foreach (get_sites() as $key => $value) {
    if ($value->blog_id != 1) {
      $mk       = '_manage_stock';
      $table_p  = GLOBAL_PREFIX.$value->blog_id.'_posts';
      $table_pm = GLOBAL_PREFIX.$value->blog_id.'_postmeta';

      $ms_id = $wpdb->get_results("SELECT $table_pm.meta_value, $table_pm.post_id as id FROM $table_pm JOIN $table_p ON $table_p.ID = $table_pm.post_id WHERE ($table_p.post_type = 'product_variation' OR $table_p.post_type = 'product') AND $table_pm.meta_key = '$mk' AND $table_p.post_status = 'publish'");

      foreach ($ms_id as $key => $value) {
        if ($value->meta_value === 'no') {
          $ms = '_manage_stock';
          $wpdb->get_results("UPDATE $table_pm SET meta_value = 'yes' WHERE post_id = $value->id AND meta_key = '$ms'");
        }
      }
    }
  }

  // Apagar produtos que já não existem
  foreach ($array as $key => $v_array) {
    if(!in_array($v_array, $ap)){
      if(!in_array($v_array, $apv)){
        $table1 = GLOBAL_PREFIX.'warehouses_products';
        $table2 = GLOBAL_PREFIX.'warehouses_stock';

        $wpdb->get_results("SELECT id FROM $table1 WHERE product_id = $v_array");
        $id_remove = $wpdb->last_result[0]->id;
        $wpdb->get_results("DELETE FROM $table2 WHERE product_id = $id_remove");
        $wpdb->get_results("DELETE FROM $table1 WHERE product_id = $v_array");
        file_put_contents('/home/joel/work/wpandebol7/log_delete.txt', date('c')." ".$id_remove." ".$v_array."\n", FILE_APPEND);
      }
    }
  }
}

/**
 * Inserir todos os produtos exitentes em todos os armazéns
 */
function insert_stock_product($product_id, $warehouse_id)
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

/**
 * Verificar se o $id é uma tradução
 */
function check_is_translation($id) {
    global $wpdb;

    $table_tr       = GLOBAL_PREFIX.'icl_translations';
    $is_translation = $wpdb->get_results("SELECT source_language_code as lang FROM $table_tr WHERE element_id = $id");

    if (is_null($is_translation[0]->lang)) {
        return true;
    }
    else {
        return false;
    }
}
