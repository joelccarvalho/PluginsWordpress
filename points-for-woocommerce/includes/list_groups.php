<?php if ( ! defined( 'ABSPATH' ) ) exit;

register_activation_hook( __FILE__, 'groups_points_table');

global $wpdb;
$table = GLOBAL_PREFIX.'groups';

// Confirmar a eliminação
if (isset($_POST['confirm_remove'])):
    if($_POST['optradio'] == 1):
        $id = $_POST['id'];
        $wpdb->delete($table, array('id' => $id));

        // Se obteve sucesso
        if ($wpdb->rows_affected > 0):
            $table_gm  = GLOBAL_PREFIX.'groups_members';
            $table_gud = GLOBAL_PREFIX.'groups_users_details';

            $wpdb->delete($table_gm, array('group_id' => $id));
            $wpdb->delete($table_gud, array('group_id' => $id));

            ?><div class="updated"><p><strong><?php _e('Eliminado com sucesso.', 'menu-test'); ?></strong></p></div>
            <?php header("Refresh:0");
        else:
            ?><div class="error"><p><strong><?php _e('Erro na eliminação.', 'menu-test'); ?></strong></p></div><?php
        endif;
    endif;
endif;

// Perguntar eliminação
if(isset($_POST['remove'])) :
  $id = $_POST['id'];?>
  <div class="">
  <h5>Deseja apagar permanentemente este clube?</h5>
      <form name="form_confirm_remove" method="post" action="">
        <input type="hidden" name="confirm_remove" value="confirm_remove">
        <div class="radio">
            <label><input type="radio" name="optradio" value="1">Sim</label>
        </div>
        <div class="radio">
            <label><input type="radio" name="optradio" value="0" checked="checked">Não</label>
        </div>
        <br>
        <input type="hidden" name="id" value="<?php echo $id; ?>">
        <td class="btn-padding"><input type="submit" name="submit" class="button-primary" value="<?php esc_attr_e('Confirmar') ?>" /></td>
      </form>
</div>
<?php endif;

function pagination_query(){
    global $wpdb, $paged, $max_num_pages;
    $table_g = GLOBAL_PREFIX.'groups';

    $paged = (isset($_GET['paged']) ? $_GET['paged'] : 1);
    $post_per_page = POST_PER_PAGE;
    $offset = ($paged - 1)*$post_per_page;

    $sql = "SELECT * FROM $table_g ORDER BY name LIMIT ".$offset.", ".$post_per_page."; ";
    $sql_result = $wpdb->get_results( $sql, OBJECT);

    /* Determinar o total de resultados encontrados e calcular numero de paginas para a navegação*/
    $sql_posts_total = $wpdb->get_results("SELECT * FROM $table_g ");

    $max_num_pages = ceil(count($sql_posts_total) / $post_per_page);
    $array = array('sql_result' => $sql_result, 'max_num_pages' => $max_num_pages, 'paged' => $paged);
    return $array;
}

$all_clubs = pagination_query();?>

<h3>Lista de clubes</h3>
<table class="widefat">
    <thead class="style-thead">
      <tr>
        <th>Clube</th>
        <th>Nome responsável</th>
        <th>Site associado</th>
        <th></th>
        <th></th>
      </tr>
    </thead>
    <?php if(!empty($all_clubs['sql_result'])):?>
        <?php foreach ($all_clubs['sql_result'] as $key => $value):?>
            <tbody>
              <tr>
                <input type="hidden" name="id" value="<?php echo $value->id; ?>">
                <td><?php echo $value->name ?></td>

                <?php $user_admin = get_user_by('ID', $value->admin); ?>

                <td><?php echo $user_admin->user_login ?></td>
                <?php $name_blog = switch_site_name($value->site_id); ?>
                <td><?php echo $name_blog ?></td>
                <td style="width: 15px"><a href="?page=Phoeniixx_reward_settings&amp;tab=list_members&id=<?php echo $value->id; ?>"><button class="button-primary">Detalhes</button></a></td>
                <form name="form_remove" method="post" action="">
                    <input type="hidden" name="remove" value="remove">
                    <input type="hidden" name="id" value="<?php echo $value->id; ?>">
                    <td><input type="submit" name="submit" class="my_button button-remove" value="Apagar"></input></td>
                </form>
              </tr>
            </tbody>
        <?php endforeach;
    else:?>
    <tbody>
        <tr>
            <td><h3>Sem clubes.</h3></td>
        </tr>
    </tbody>
    <?php endif; ?>
</table>
<center class="nav_products">
   <?php previous_posts_link('&laquo; Mostrar menos',$all_clubs['max_num_pages']) ?> <?php echo $all_clubs['paged']?>/<?php echo $all_clubs['max_num_pages']?>
   <?php next_posts_link('Mostrar mais &raquo;',$all_clubs['max_num_pages']) ?>
</center>

<?php
function switch_site_name ($blog_id){

    switch ($blog_id) {
        case '1':
            $name = 'Andebol7';
            break;

        case '2':
            $name = 'Volei7';
            break;

        case '3':
            $name = 'Running7';
            break;


        case '4':
            $name = 'Balonmano7';
            break;

        default:
            $name = 'Erro! Contacte o administrador.';
            break;
    }

    return $name;
}
