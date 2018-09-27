<?php

// Pesquisar produto por id
if (isset($_POST['search_by_id'])) {
  if (!empty($_POST['id']) && is_numeric($_POST['id'])) {
    $id = $_POST['id'];
    $newURL = '?page=warehouse_stocks&tab=search&id='.$id;
    header('Location: '.$newURL);
  }
  else {
    ?><div class="error"><p><strong><?php _e('Para pesquisar, o ID é obrigatório.', 'menu-test' ); ?></strong></p></div><?php
  }
}

$tab = (isset($_GET['tab'])) ? $_GET['tab'] : '';

if ($tab == 'search') {
  include_once(PATH.'includes/edit_product_by_id.php');
}
else
{?>
  <form style="float: right;" class="form_search" name="form_search" method="post" action="">
    <input type="hidden" name="search_by_id" value="search_by_id">
    <div class="input-group">
      <span class="input-group-btn">
        <input class="box_search" type="text" placeholder="Pesquisar por ID..." name="id">
      </span>
      <input type="submit" name="Submit" class="button-primary" value="<?php esc_attr_e('Procurar') ?>" />
    </div>
  </form>

  <div class="dropdown">
    <button onclick="myFunction()" class="button-primary btn_search">Pesquisar por nome...</button>
    <div id="myDropdown" class="dropdown-content">
      <input type="text" placeholder="Nome" id="myInput" onkeyup="filterFunction()">

      <?php
      $products = get_all_products();
      foreach ($products as $info_product):?>
        <a href="?page=warehouse_stocks&amp;tab=search&id=<?php echo $info_product->product_id; ?>"><?php echo $info_product->name.' ('.$info_product->product_id.')' ?></a>
      <?php  endforeach; ?>
        <div class="without_product" style="display: none !important">Nenhum resultado encontrado.</div>
    </div>
  </div>
  <?php

  function pagination_query(){
    global $wpdb, $paged, $max_num_pages;

    $paged         = (isset($_GET['paged']) ? $_GET['paged'] : 1);
    $post_per_page = 15;
    $offset        = ($paged - 1) * $post_per_page;

    $table_wp = GLOBAL_PREFIX.'warehouses_products';
    $table_w  = GLOBAL_PREFIX.'warehouses';
    $table_ws = GLOBAL_PREFIX.'warehouses_stock';

    $sql = "SELECT $table_wp.product_id as product_id, $table_wp.name as p_name, $table_w.name as w_name,$table_w.id as w_id, $table_ws.stock FROM $table_ws JOIN $table_wp ON $table_wp.id = $table_ws.product_id JOIN $table_w ON $table_w.id = $table_ws.warehouse_id ORDER BY $table_wp.product_id DESC LIMIT ".$offset.", ".$post_per_page."; ";

    $sql_result = $wpdb->get_results( $sql, OBJECT);

    /* Determinar o total de resultados encontrados e calcular numero de paginas para a navegação*/
    $sql_posts_total = $wpdb->get_results( "SELECT $table_wp.product_id as product_id, $table_w.name as p_name, $table_w.name as w_name, $table_ws.stock FROM $table_ws JOIN $table_wp ON $table_wp.id = $table_ws.product_id JOIN $table_w ON $table_w.id = $table_ws.warehouse_id ORDER BY $table_wp.product_id DESC");

    $max_num_pages = ceil(count($sql_posts_total) / $post_per_page);
    $array = array('sql_result' => $sql_result, 'max_num_pages' => $max_num_pages, 'paged' => $paged);
    return $array;
  }

  $all_products = pagination_query();

  /** Listagem dos armazéns **/

  $site = get_current_blog_id();

  if ($site == 1) {
    if(isset($_POST['reloadProducts'])){
      require_once('find_products.php');
    }
  }

  ?>
 <!--  <form name="reloadProducts" method="post" action="">
    <input type="hidden" name="reloadProducts">
    <h3>Stocks</h3>
    <button <?php echo ($site != 1 ? 'disabled="disabled"': ''); ?> class="tooltip"><span class="tooltiptext">Atualizar produtos. ATENÇÃO: Clicar apenas quando não aparece algum produto.</span><span class="dashicons dashicons-image-rotate"></span></button>
  </form>
 -->
  <?php
  if(!empty($all_products['sql_result'])):?>
    <table class="widefat">
        <thead class="style-thead">
          <tr>
            <th width="80px">ID</th>
            <th width="80px">Produto</th>
            <th width="80px">Armazém</th>
            <th width="80px">Stock</th>
            <th width="80px"></th>
          </tr>
        </thead>
        <?php foreach ($all_products['sql_result'] as $key => $value):?>
          <form name="form_remove" method="post" action="">
            <input type="hidden" name="update" value="update">
            <input type="hidden" name="id" value="<?php echo $value->id; ?>">
              <tbody>
                <tr>
                  <td><a href="?page=warehouse_stocks&amp;tab=search&id=<?php echo $value->product_id; ?>"><?php echo $value->product_id ?></a></td>
                  <td><?php echo $value->p_name ?></td>
                  <td><a href="?page=list-warehouses&amp;tab=details&id=<?php echo $value->w_id; ?>"><?php echo $value->w_name ?></a></td>
                  <td><?php echo $value->stock ?></td>
                  <td><a href="?page=warehouse_stocks&amp;tab=search&id=<?php echo $value->product_id; ?>"><span class="dashicons dashicons-edit"></span></a></td>
                </tr>
              </tbody>
          </form>
        <?php endforeach; ?>
    </table>
    <br>

    <div class="nav_products">
      <div class="inline_pagination">
       <?php previous_posts_link('&laquo;',$all_products['max_num_pages']) ?>
       <form name="pagination_update" method="get" action="">
        <input type="hidden" name="page" value="warehouse_stocks">
        <input class="input_pagination" type="text" name="paged" value="<?php echo $all_products['paged']?>"> de <?php echo $all_products['max_num_pages']?>
       </form>
       <?php next_posts_link('&raquo;',$all_products['max_num_pages']) ?>
     </div>
    </div>

  <?php else:?>
    <h4>Sem stocks.</h4>
  <?php endif;
}

function get_all_products(){
  global $wpdb;
  $table_wp = GLOBAL_PREFIX.'warehouses_products';

  $products = $wpdb->get_results( "SELECT name, product_id FROM $table_wp ORDER BY $table_wp.name");

  return $products;
}

?>
