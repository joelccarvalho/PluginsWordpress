<?php if ( ! defined( 'ABSPATH' ) ) exit;

/*if ( ! empty( $_POST ) && check_admin_referer( 'phoen_rewpts_form_action', 'phoen_rewpts_form_action_form_nonce_field' ) ) {

	if(sanitize_text_field( $_POST['rewpts_submit'] ) == 'Save'){

		$enable_plugin=sanitize_text_field($_POST['enable_plugin']);

		$reward_money=sanitize_text_field($_POST['reward_money']);

		$reedem_money=sanitize_text_field($_POST['reedem_money']);

		$reward_point=sanitize_text_field($_POST['reward_point']);

		$reedem_point=sanitize_text_field($_POST['reedem_point']);

		$phoe_rewpts_value = array(

			'enable_plugin'=>$enable_plugin,

			'reward_point'=>$reward_point,

			'reward_money'=>$reward_money,

			'reedem_point'=>$reedem_point,

			'reedem_money'=>$reedem_money

		);

		update_option('phoe_rewpts_value',$phoe_rewpts_value);

	}

}

	$gen_settings = get_option('phoe_rewpts_value');

	$enable_plugin=isset($gen_settings['enable_plugin'])?$gen_settings['enable_plugin']:'';

	$reward_point=isset($gen_settings['reward_point'])?$gen_settings['reward_point']:'';

	$reedem_point=isset($gen_settings['reedem_point'])?$gen_settings['reedem_point']:'';

	$reward_money=isset($gen_settings['reward_money'])?$gen_settings['reward_money']:'';

	$reedem_money=isset($gen_settings['reedem_money'])?$gen_settings['reedem_money']:'';

*/
 ?>

	<div id="phoeniixx_phoe_book_wrap_profile-page"  class=" phoeniixx_phoe_book_wrap_profile_div">

		<style>
		.phoe_video_main {
				padding: 20px;
				text-align: center;
			}

			.phoe_video_main h3 {
				color: #02c277;
				font-size: 28px;
				font-weight: bolder;
				margin: 20px 0;
				text-transform: capitalize
				display: inline-block;
			}
		</style>

		<form method="post" id="phoeniixx_phoe_book_wrap_profile_form" action="" >

			<?php wp_nonce_field( 'phoen_rewpts_form_action', 'phoen_rewpts_form_action_form_nonce_field' ); ?>

			<table class="form-table">

				<tbody>

					<tr class="phoeniixx_phoe_rewpts_wrap">

						<th>

							<label><?php _e('Ativar','phoen-rewpts'); ?> </label>

						</th>

						<td>

							<input type="checkbox"  name="enable_plugin" id="enable_plugin" value="1" <?php echo(isset($gen_settings['enable_plugin']) && $gen_settings['enable_plugin'] == '1')?'checked':'checked';?>>

						</td>
						<td></td>

					</tr>

					<tr class="phoeniixx_phoe_rewpts_wrap">

						<th>
						<?php  $curr=get_woocommerce_currency_symbol(); ?>


							<label>Em loja, cada: </label>

						</th>

						<td>

							<input type="number" step="any" class="reward_money"  name="reward_money" value="<?php echo (empty($reward_money) ? '1' : $reward_money); ?>"><?php echo $curr; ?> =

							<input type="number" step="any" class="reward_point" name="reward_point" value="<?php echo (empty($reward_point) ? '1' : $reward_point); ?>" >Pontos

						</td>

					</tr>
					<tr class="phoeniixx_phoe_rewpts_wrap">

						<th>
						<?php  $curr=get_woocommerce_currency_symbol(); ?>

						<label>Em backoffice, cada: </label>

						</th>
						<td><input type="number" step="any" class="reedem_point" name="reedem_point" value="<?php echo (empty($reedem_point) ? '100' : $reedem_point); ?>" > Pontos =

						<input type="number" step="any" name="reedem_money" class="reedem_money" value="<?php echo (empty($reedem_money) ? '1' : $reedem_money); ?>" >

						<?php echo $curr; ?>



						</td>

					</tr>

					<tr class="phoeniixx_phoe_rewpts_wrap">

						<td colspan="2">

							<input type="submit" value="Save" name="rewpts_submit" id="submit" class="button button-primary">

						</td>

					</tr>

				</tbody>

			</table>

		</form>

	</div>
