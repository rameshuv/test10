<?php
/**
 * Demo data tools view for Bonus Hunt Guesser.
 *
 * @package Bonus_Hunt_Guesser
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div class="wrap bhg-wrap">
	<h1><?php echo esc_html( bhg_t( 'demo_tools', 'Demo Tools' ) ); ?></h1>

	<p class="description" style="max-width:800px;">
		<?php echo esc_html( bhg_t( 'this_will_delete_all_demo_data_and_pages_then_recreate_fresh_demo_content', 'This will delete all demo data and pages, then recreate fresh demo content.' ) ); ?>
	</p>

	<div class="card" style="max-width:780px;padding:16px;margin-top:16px;">
		<h2><?php echo esc_html( bhg_t( 'summary', 'Summary' ) ); ?></h2>
		<p><?php echo esc_html( bhg_t( 'note_this_will_remove_any_demo_data_and_reset_tables_to_their_initial_state', 'Note: This will remove any demo data and reset tables to their initial state.' ) ); ?></p>

		<?php if ( ! empty( $counts ) ) : ?>
			<table class="widefat striped" style="max-width:100%;">
				<thead>
					<tr>
						<th scope="col"><?php echo esc_html( bhg_t( 'table_name', 'Table Name' ) ); ?></th>
						<th scope="col" style="width:140px;">
							<?php echo esc_html( bhg_t( 'rows', 'Rows' ) ); ?>
						</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $counts as $info ) : ?>
						<tr>
							<td><?php echo esc_html( $info['label'] ); ?></td>
							<td>
								<?php
								if ( null === $info['count'] ) {
									echo '&#8212;';
								} else {
									echo esc_html( number_format_i18n( (int) $info['count'] ) );
								}
								?>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		<?php endif; ?>
	</div>

	<form method="post" action="<?php echo esc_url( $action_url ); ?>" style="margin-top:24px;">
		<input type="hidden" name="action" value="bhg_demo_reset" />
		<?php
		if ( class_exists( 'BHG_Utils' ) ) {
			BHG_Utils::nonce_field( 'bhg_demo_reset' );
		} else {
			wp_nonce_field( 'bhg_demo_reset', 'bhg_demo_reset_nonce' );
		}
		?>

		<p class="description">
			<?php echo esc_html( bhg_t( 'reset_reseed_demo', 'Reset & Reseed Demo' ) ); ?>
		</p>

		<?php
		submit_button(
			bhg_t( 'reset_reseed_demo_data', 'Reset & Reseed Demo Data' ),
			'primary',
			'submit',
			false,
			array(
				'onclick' => "return confirm('" . esc_js( bhg_t( 'are_you_sure', 'Are you sure?' ) ) . "');",
			)
		);
		?>
	</form>
</div>
