<?php if ( ! defined( 'ABSPATH' ) ) exit;

// Dados do clube a editar
global $wpdb;
$table = GLOBAL_PREFIX.'groups';
$id = $_GET['id'];

if (isset($_POST) && isset($_POST['edit']))
{
    $opt_name     = $_POST['name'];
    $opt_discount = $_POST['discount'];
    $opt_margin   = $_POST['margin'];
    $opt_site     = $_POST['site'];

    if (!empty($opt_name) && !empty($opt_discount) && !empty($opt_margin)) {

        $opt_discount = str_replace(',', '.', $opt_discount);
        $opt_margin   = str_replace(',', '.', $opt_margin);

        if (is_numeric($opt_discount) && is_numeric($opt_margin)) {
            $wpdb->get_results("UPDATE $table SET name = '$opt_name', margin = $opt_margin, discount_user = $opt_discount, site_id = $opt_site WHERE id = $id");

            if ($wpdb->rows_affected > 0) {
                ?><div class="updated"><p><strong><?php echo $opt_name ?> atualizado com sucesso!</strong></p></div><?php
            }
            else{
                ?><div class="error"><p><strong>Algo correu mal. Verifique se alterou algum campo.</strong></p></div><?php
            }
        }
    }
}

$wpdb->get_results("SELECT * FROM $table WHERE id = $id"); ?>

<h3>A editar: <?php echo $wpdb->last_result[0]->name ?></h3>
<table class="widefat">
    <thead class="style-thead">
      <tr>
        <th>Administrador(Email)</th>
        <th>Nome</th>
        <th>Desconto ao jogador</th>
        <th>Margem lucro</th>
        <th>Site associado</th>
        <th></th>
      </tr>
    </thead>
    <?php foreach ($wpdb->last_result as $key => $value):?>
        <tbody>
            <form name="form_edit" method="post" action="">
                <input type="hidden" name="edit" value="edit">
                    <tr>
                        <?php $user_admin = get_user_by('ID', $value->admin); ?>
                        <td><?php echo $user_admin->user_login . ' ('.$user_admin->user_email.')' ?></td>
                        <td><input name="name" value="<?php echo $value->name ?>" /></td>
                        <td><input name="discount" value="<?php echo $value->discount_user ?>" />%</td>
                        <td><input name="margin" value="<?php echo $value->margin ?>" />%</td>
                        <?php $current_blog_id = $value->site_id ?>
                        <td>
                            <select id="site_id" name="site" tabindex="3" class="form-control">
                                <?php
                                    $table = GLOBAL_PREFIX.'blogs';
                                    global $wpdb;
                                    $wpdb->get_results("SELECT blog_id, domain FROM $table");
                                    foreach ($wpdb->last_result as $key => $value):  ?>
                                        <option <?php echo ($current_blog_id == $value->blog_id ? 'selected' : '') ?>  value="<?php echo $value->blog_id; ?>"><?php echo $value->domain ?></option>
                                <?php endforeach;?>
                            </select>
                        </td>
                        <td><input type="submit" class="button button-primary" value="Editar"></td>
                    </tr>
            </form>
        </tbody>
    <?php endforeach; ?>
</table>
