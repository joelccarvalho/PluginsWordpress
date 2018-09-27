<?php if ( ! defined( 'ABSPATH' ) ) exit;

// Search all members
global $wpdb;
$table = GLOBAL_PREFIX.'groups';
$id = $_GET['id'];
$wpdb->get_results("SELECT * FROM $table WHERE id = $id ORDER BY name");

// Confirmar a eliminação
if (isset($_POST['confirm_remove'])):
    if($_POST['optradio'] == 1):
        global $wpdb;
        $table = GLOBAL_PREFIX.'groups_members';

        $wpdb->delete($table, array('member_id' => $_POST['user_id']));
        $user_id = $_POST['user_id'];

        // Se obteve sucesso
        if ($wpdb->rows_affected > 0):
            $table_gud = GLOBAL_PREFIX.'groups_users_details';

            // Alterar jogador para utilizador normal
            $wpdb->get_results("UPDATE $table_gud SET group_id = null, perfil = 'simple' WHERE user_id = $user_id");
            ?><div class="updated"><p><strong><?php _e('Eliminado com sucesso.', 'menu-test'); ?></strong></p></div><?php
            header("Refresh:0");
        else:
            ?><div class="error"><p><strong><?php _e('Erro na eliminação.', 'menu-test'); ?></strong></p></div><?php
        endif;
    endif;
endif;

// Perguntar eliminação
if(isset($_POST['remove'])) :
  $user_id = $_POST['user_id'];?>
  <div class="">
  <h5>Deseja apagar este jogador do clube?</h5>
      <form name="form_confirm_remove" method="post" action="">
        <input type="hidden" name="confirm_remove" value="confirm_remove">
        <div class="radio">
            <label><input type="radio" name="optradio" value="1">Sim</label>
        </div>
        <div class="radio">
            <label><input type="radio" name="optradio" value="0" checked="checked">Não</label>
        </div>
        <br>
        <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
        <td class="btn-padding"><input type="submit" name="submit" class="button-primary" value="<?php esc_attr_e('Confirmar') ?>" /></td>
      </form>
</div>
<br>
<?php endif;

function pagination_query($id){
    global $wpdb, $paged, $max_num_pages;
    $table = GLOBAL_PREFIX.'groups_members';

    $paged = (isset($_GET['paged']) ? $_GET['paged'] : 1);

    $post_per_page = 5;
    $offset = ($paged - 1)*$post_per_page;

    $sql = "SELECT member_id FROM $table WHERE group_id = $id LIMIT ".$offset.", ".$post_per_page."; ";

    $sql_result = $wpdb->get_results( $sql, OBJECT);

    /* Determinar o total de resultados encontrados e calcular numero de paginas para a navegação*/
    $sql_posts_total = $wpdb->get_results("SELECT member_id FROM $table WHERE group_id = $id");

    $max_num_pages = ceil(count($sql_posts_total) / $post_per_page);

    // QUANDO APAGA DEVE VOLTAR À 1ª PAGINA, SENAO FICA ONDE ESTAVA SEM JOGADORES
    if ($paged > $max_num_pages) {
        $id  = $_GET['id'];
        $url = $_SERVER['PHP_SELF'].'?page=Phoeniixx_reward_settings&tab=list_members&id='.$id.'&paged=1';
        header('Refresh:'.$url);
    }

    $array = array('sql_result' => $sql_result, 'max_num_pages' => $max_num_pages, 'paged' => $paged);

    return $array;
}

?>

<table class="widefat">
    <thead class="style-thead">
      <tr>
        <th>Editar</th>
        <th>Nome</th>
        <th>Administrador(Email)</th>
        <th>Desconto ao jogador</th>
        <th>Margem lucro</th>
        <th>Membros(Email)</th>
      </tr>
    </thead>
    <?php foreach ($wpdb->last_result as $key => $value):?>
        <tbody>
          <tr>
            <input type="hidden" name="id" value="<?php echo $value->id; ?>">
            <td><a href="?page=Phoeniixx_reward_settings&amp;tab=club_edit&id=<?php echo $value->id ?>"><span class="dashicons dashicons-edit"></span></a></td>
            <td><?php echo $value->name ?></td>
            <?php $user_admin = get_user_by('ID', $value->admin); ?>
            <td><?php echo $user_admin->user_login . ' ('.$user_admin->user_email.')' ?></td>
            <td><?php echo $value->discount_user.'%' ?></td>
            <td><?php echo $value->margin.'%' ?></td>
            <td >
                <table>
                    <?php $all_players = pagination_query($value->id);

                    if(!empty($all_players['sql_result'])):
                        foreach ($all_players['sql_result'] as $key => $value):
                            // Converter id dos membros em nomes
                            $name_member = get_user_by('ID', $value->member_id); ?>

                            <form name="form_remove" method="post" action="">
                                <input type="hidden" name="remove" value="remove">
                                <tr>
                                    <td><?php echo $name_member->user_login . ' ('.$name_member->user_email.')' ?></td>
                                    <td><input type="submit" name="submit" class="my_button button-remove" value="<?php esc_attr_e('Remover') ?>" /></td>
                                    <input type="hidden" name="user_id" value="<?php echo $value->member_id; ?>">
                                </tr>
                            </form>
                        <?php endforeach;
                    else:?>
                        <tr>Clube sem jogadores associados.</tr>
                    <?php endif;; ?>
                </table>
            </td>
          </tr>
        </tbody>
    <?php endforeach; ?>
</table>
<br>
<center class="nav_products">
   <?php previous_posts_link('&laquo; Mostrar menos',$all_players['max_num_pages']) ?> <?php echo $all_players['paged']?>/<?php echo $all_players['max_num_pages']?>
   <?php next_posts_link('Mostrar mais &raquo;',$all_players['max_num_pages']) ?>
</center>
