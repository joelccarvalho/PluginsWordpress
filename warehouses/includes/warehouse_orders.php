<?php if ( ! defined( 'ABSPATH' ) ) exit;

// chamar ficheiro para verificar se tem permissões
require_once('check_permissions.php');
$user_permissions = check_user_admin();

$id = $_GET['id'];
global $wpdb;

$table_w  = GLOBAL_PREFIX.'warehouses';

$wpdb->get_results("SELECT * FROM $table_w WHERE id = $id");?>
<h3><?php echo $wpdb->last_result[0]->name;?></h3>

<?php
function pagination_query(){
    global $wpdb, $paged, $max_num_pages, $current_date;
    $table_wo = GLOBAL_PREFIX.'warehouses_stock_order';

    $paged = (isset($_GET['paged']) ? $_GET['paged'] : 1);
    $post_per_page = 15;
    $offset = ($paged - 1)*$post_per_page;
    $id = $_GET['id'];

    $sql = "SELECT order_id, qty, id_site FROM $table_wo WHERE warehouse_id = $id ORDER BY order_id DESC LIMIT ".$offset.", ".$post_per_page."; ";
    $sql_result = $wpdb->get_results( $sql, OBJECT);

    /* Determinar o total de resultados encontrados e calcular numero de paginas para a navegação*/
    $sql_posts_total = $wpdb->get_results( "SELECT order_id, qty, id_site FROM $table_wo WHERE warehouse_id = $id ORDER BY order_id DESC " );

    $max_num_pages = ceil(count($sql_posts_total) / $post_per_page);
    $array = array('sql_result' => $sql_result, 'max_num_pages' => $max_num_pages, 'paged' => $paged);
    return $array;
}

$all_products = pagination_query();

if (empty($all_products['sql_result'])):
    ?><h4>Sem encomendas efetuadas!</h4><?php
else: ?>
    <table class="widefat">
        <thead class="style-thead">
          <tr>
            <th>ID Encomenda</th>
            <th>Quantidade</th>
            <th>Site</th>
          </tr>
        </thead>
        <?php foreach ($all_products['sql_result'] as $key => $value):?>
            <tbody>
              <tr>
                <?php $site_details =  get_blog_details($value->id_site);?>
                <td><a href="<?php echo $site_details->home ?>/wp-admin/post.php?post=<?php echo $value->order_id ?>&action=edit"><?php echo $value->order_id ?></a></td>
                <td><?php echo $value->qty ?></td>
                <td><?php echo $site_details->blogname ?></td>
              </tr>
            </tbody>
        <?php endforeach; ?>
    </table>
    <br>
    <center class="nav_products">
       <?php previous_posts_link('&laquo; Mostrar menos',$all_products['max_num_pages']) ?> <?php echo $all_products['paged']?>/<?php echo $all_products['max_num_pages']?>
       <?php next_posts_link('Mostrar mais &raquo;',$all_products['max_num_pages']) ?>
    </center>

    <h3>Visão Geral</h3>
    <table class="widefat">
        <thead class="style-thead">
          <tr>
            <th>Sites</th>
            <th>Total encomendas</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>Andebol7</td>
            <td><?php echo sum_orders(1); ?></td> <!--Id do Andebol7 = 1-->
          </tr>
          <tr>
            <td>Volei7</td>
            <td><?php echo sum_orders(2); ?></td> <!--Id do Volei7 = 2-->
          </tr>
          <tr>
            <td>Running7</td>
            <td><?php echo sum_orders(3); ?></td> <!--Id do Running7 = 3-->
          </tr>
          <tr>
            <td>Balonmano7</td>
            <td><?php echo sum_orders(4); ?></td> <!--Id do Balonmano7 = 4-->
          </tr>
        </tbody>
    </table> <?php
endif;

function sum_orders($id_site){

    global $wpdb;
    $table_wo = GLOBAL_PREFIX.'warehouses_stock_order';
    $id = $_GET['id'];


    $sql_info = $wpdb->get_results( "SELECT COUNT(id_site) as total FROM $table_wo WHERE id_site = $id_site and warehouse_id = $id" );
    $total = (empty($sql_info[0]->total) ? 0 : $sql_info[0]->total);

    return $total;
}


?>
