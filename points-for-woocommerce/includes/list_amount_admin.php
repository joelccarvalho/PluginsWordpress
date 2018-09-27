<?php if ( ! defined( 'ABSPATH' ) ) exit;

// $current_user = wp_get_current_user();
// if ($current_user->roles[0] == 'editor') {
//     wp_die( __('Não tem permissões para aceder a esta página.') );
// }

global $wpdb;
$curr  = get_woocommerce_currency_symbol();

$table_gud = GLOBAL_PREFIX.'groups_users_details';
$table_g   = GLOBAL_PREFIX.'groups';
$table_gm  = GLOBAL_PREFIX.'groups_members';

if (isset($_POST) && isset($_POST['update'])) {

    $ta = str_replace(',', '.', $_POST['total_amount']);

    if (is_numeric($ta)) {
        require_once(PHOEN_REWPTSPLUGPATH.'phoen_reward_points.php');
        updateTotalAmountClubs($_POST['id_user'], $ta);
    }
    else
    {
        ?><div class="error"><p><strong><?php _e('Atenção: O valor acumulado é um campo numérico.', 'menu-test' ); ?></strong></p></div><?php
    }
}

$tab = (isset($_GET['tab']))?$_GET['tab']:'';

function pagination_query(){
    global $wpdb, $paged, $max_num_pages;
    $table_gud = GLOBAL_PREFIX.'groups_users_details';
    $table_g   = GLOBAL_PREFIX.'groups';

    $paged = (isset($_GET['paged']) ? $_GET['paged'] : 1);
    $post_per_page = POST_PER_PAGE;
    $offset = ($paged - 1)*$post_per_page;

    $permission = check_permissions_user();
    if ($permission == 'admin') {
        $sql = "SELECT * FROM $table_gud WHERE perfil = 'admin' LIMIT ".$offset.", ".$post_per_page."; ";

        $sql_result = $wpdb->get_results( $sql, OBJECT);

        /* Determinar o total de resultados encontrados e calcular numero de paginas para a navegação*/
        $sql_posts_total = $wpdb->get_results("SELECT * FROM $table_gud WHERE perfil = 'admin'");
    }
    else {
        $sql = "SELECT $table_g.name, $table_gud.* FROM $table_g JOIN $table_gud ON $table_gud.group_id = $table_g.id WHERE $table_g.site_id = $permission AND $table_gud.perfil = 'admin' LIMIT ".$offset.", ".$post_per_page."; ";

        $sql_result = $wpdb->get_results( $sql, OBJECT);

        /* Determinar o total de resultados encontrados e calcular numero de paginas para a navegação*/
        $sql_posts_total = $wpdb->get_results("SELECT $table_g.name, $table_gud.* FROM $table_g JOIN $table_gud ON $table_gud.group_id = $table_g.id WHERE $table_g.site_id = $permission AND $table_gud.perfil = 'admin'");
    }

    $max_num_pages = ceil(count($sql_posts_total) / $post_per_page);

    $array = array('sql_result' => $sql_result, 'max_num_pages' => $max_num_pages, 'paged' => $paged);

    return $array;
}

$all_clubs = pagination_query();?>

 <!-- TABELA PARA CLUBES -->
<div class="phoen_rewpts_order_report_table_div">Clubes</div>
<table class="wp-list-table widefat fixed table table-striped customers">
    <thead>
        <tr class="phoen_rewpts_user_reward_point_tr">
            <th scope="col"><span><?php _e('Clube','phoen-rewpts'); ?></span></th>
            <th class="column-customer_name " scope="col"><span><?php _e('Email(clube)','phoen-rewpts'); ?></span></th>
            <th class="column-email" scope="col"><span><?php _e('Encomendas Completas ','phoen-rewpts'); ?></span></th>
            <th><span><?php _e('Valor Acumulado(€)','phoen-rewpts'); ?></span></th>
            <th>Editar</th>
        </tr>
    </thead>
    <tbody>
    <?php
        if (count($all_clubs['sql_result']) > 0):
            // Percorrer dos clubes
            foreach ($all_clubs['sql_result'] as $key => $value):
                $email_admin = get_user_by('ID', $value->user_id);
                $name_club   = $wpdb->get_results("SELECT name FROM $table_g WHERE id = $value->group_id");

                if ($tab === 'edit'):?>
                <form name="form_remove" method="post" action="">
                    <input type="hidden" name="update" value="update">
                    <input type="hidden" name="id_user" value="<?php echo $value->user_id ?>">
                    <tr>
                        <td><?php echo $name_club[0]->name?></td>
                        <td class="customer_name"><?php echo $email_admin->data->user_email; ?></td>
                        <td class="customer_name"><?php echo $value->total_orders ?></td>
                        <td><input name="total_amount" value="<?php echo $value->total_amount ?>" /></td>
                        <td><input type="submit" class="button button-primary" value="Atualizar"></td>
                    </tr>
                </form>

                <?php else: ?>
                    <tr>
                        <td><?php echo  $name_club[0]->name?></td>
                        <td class="customer_name"><?php echo $email_admin->data->user_email; ?></td>
                        <td class="customer_name"><?php echo $value->total_orders ?></td>
                        <td><?php echo $value->total_amount.$curr ?></td>
                        <!-- Se não estiver na 1ª página, edita na paǵina atual -->
                        <?php $p = (isset($_GET['paged']) ? "&amp;paged=".$_GET['paged'] : '');?>
                        <td><a href="?page=amount_admin&amp;tab=edit&user_id=<?php echo $value->user_id . $p ?>"><span class="dashicons dashicons-edit"></span></a></td>
                    </tr>
                <?php endif; ?>
            <?php endforeach;
        else:  ?>
            <tr><td><p> Nenhum clube associado.</p></td></tr> <?php
        endif; ?>
    </tbody>
</table>
<br>
<center class="nav_products">
   <?php previous_posts_link('&laquo; Mostrar menos',$all_clubs['max_num_pages']) ?> <?php echo $all_clubs['paged']?>/<?php echo $all_clubs['max_num_pages']?>
   <?php next_posts_link('Mostrar mais &raquo;',$all_clubs['max_num_pages']) ?>
</center>

<?php
function check_permissions_user(){
    global $wpdb;

    $current_user = wp_get_current_user();
    $site         = get_current_blog_id();
    $roles        = $current_user->roles;

    if ($roles[0] == 'administrator') {
        return 'admin';
    }
    else {
        return $site;
    }
}
