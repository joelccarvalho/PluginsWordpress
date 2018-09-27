<?php if ( ! defined( 'ABSPATH' ) ) exit;

$id = $_GET['id'];

// chamar ficheiro para verificar se tem permissões
require_once('check_permissions.php');
$user_permissions = check_user_warehouse($id);

global $wpdb;

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

if ($tab == 'search'):
  include_once(PATH.'includes/edit_product_by_id.php');
else:?>
    <form style="float: right;" class="form_search" name="form_search" method="post" action="">
        <input type="hidden" name="search_by_id" value="search_by_id">
        <div class="input-group">
          <span class="input-group-btn">
            <input class="box_search" type="text" placeholder="Pesquisar por ID" name="id">
          </span>
          <input type="submit" name="Submit" class="button-primary" value="<?php esc_attr_e('Procurar') ?>" />
        </div>
    </form>

<?php endif;

$table_w  = GLOBAL_PREFIX.'warehouses';
$table_wp = GLOBAL_PREFIX.'warehouses_products';
$table_ws = GLOBAL_PREFIX.'warehouses_stock';

$wpdb->get_results("SELECT * FROM $table_w WHERE id = $id");?>
<h3><?php echo $wpdb->last_result[0]->name;?></h3>

<?php
function pagination_query(){
    global $wpdb, $paged, $max_num_pages, $current_date;
    $table_wp = GLOBAL_PREFIX.'warehouses_products';
    $table_ws = GLOBAL_PREFIX.'warehouses_stock';

    $paged = (isset($_GET['paged']) ? $_GET['paged'] : 1);
    $post_per_page = 15;
    $offset = ($paged - 1)*$post_per_page;

    $sql = "SELECT $table_wp.id, $table_wp.name, $table_wp.product_id FROM $table_wp ORDER BY $table_wp.product_id LIMIT ".$offset.", ".$post_per_page."; ";
    $sql_result = $wpdb->get_results( $sql, OBJECT);

    /* Determinar o total de resultados encontrados e calcular numero de paginas para a navegação*/
    $sql_posts_total = $wpdb->get_results( "SELECT $table_wp.id, $table_wp.name, $table_wp.product_id FROM $table_wp ORDER BY $table_wp.product_id" );

    $max_num_pages = ceil(count($sql_posts_total) / $post_per_page);
    $array = array('sql_result' => $sql_result, 'max_num_pages' => $max_num_pages, 'paged' => $paged);
    return $array;
}

$all_products = pagination_query();?>

<table class="widefat">
    <thead class="style-thead">
      <tr>
        <th>ID</th>
        <th>Produto</th>
        <th>Stock</th>
        <th></th>
      </tr>
    </thead>
    <?php foreach ($all_products['sql_result'] as $key => $value):?>
        <tbody>
          <tr>
            <td><?php echo $value->product_id ?></td>
            <td><?php echo $value->name ?></td>
            <?php $wpdb->get_results("SELECT stock FROM $table_ws WHERE product_id = $value->id AND warehouse_id = $id");
            $stock = (isset($wpdb->last_result[0]->stock))? $wpdb->last_result[0]->stock : '0'; ?>
            <td <?php echo ($stock == '0' ? 'class="without_stock"' : ($stock < '5' ? 'class="danger_stock"' : 'class="with_stock"')) ?>>
                <?php echo $stock ?>
            </td>
            <input type="hidden" name="product_id" value="<?php echo $value->product_id; ?>">
            <td><a href="?page=warehouse_stocks&amp;tab=search&id=<?php echo $value->product_id; ?>"><span class="dashicons dashicons-edit"></span></td>
          </tr>
        </tbody>
    <?php endforeach; ?>
</table>
<br>
<center class="nav_products">
   <?php previous_posts_link('&laquo;',$all_products['max_num_pages']) ?> <?php echo $all_products['paged']?>/<?php echo $all_products['max_num_pages']?>
   <?php next_posts_link('&raquo;',$all_products['max_num_pages']) ?>
</center>
