<?php
/**
 * Admin view: Hunts list.
 *
 * @package BonusHuntGuesser
 */

if ( ! defined( 'ABSPATH' ) ) {
		exit;
}

if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html( bhg_t( 'you_do_not_have_sufficient_permissions_to_access_this_page', 'You do not have sufficient permissions to access this page.' ) ) );
}

global $wpdb;
$t = esc_sql( $wpdb->prefix . 'bhg_bonus_hunts' );

$paged    = max( 1, isset( $_GET['paged'] ) ? absint( wp_unslash( $_GET['paged'] ) ) : 1 );
$per_page = 20;
$offset   = ( $paged - 1 ) * $per_page;

$rows  = $wpdb->get_results(
	$wpdb->prepare(
		"SELECT id, title, start_balance, final_balance, status, winners_count, closed_at FROM {$t} ORDER BY id DESC LIMIT %d OFFSET %d",
		$per_page,
		$offset
	)
);
$total = (int) $wpdb->get_var(
	"SELECT COUNT(*) FROM {$t}"
);
$pages = max( 1, (int) ceil( $total / $per_page ) );

?>
<div class="wrap">
	<h1>
	<?php
	echo esc_html( bhg_t( 'label_bonus_hunts', 'Bonus Hunts' ) );
	?>
</h1>
	<table class="widefat striped">
	<thead>
		<tr>
		<th>
		<?php
		echo esc_html( bhg_t( 'id', 'ID' ) );
		?>
</th>
		<th>
		<?php
		echo esc_html( bhg_t( 'sc_title', 'Title' ) );
		?>
</th>
		<th>
		<?php
		echo esc_html( bhg_t( 'sc_start_balance', 'Start Balance' ) );
		?>
</th>
		<th>
		<?php
		echo esc_html( bhg_t( 'sc_final_balance', 'Final Balance' ) );
		?>
</th>
		<th>
		<?php
		echo esc_html( bhg_t( 'sc_status', 'Status' ) );
		?>
</th>
		<th>
		<?php
		echo esc_html( bhg_t( 'winners', 'Winners' ) );
		?>
</th>
		<th>
		<?php
		echo esc_html( bhg_t( 'label_closed_at', 'Closed At' ) );
		?>
</th>
		<th>
		<?php
		echo esc_html( bhg_t( 'label_actions', 'Actions' ) );
		?>
</th>
		</tr>
	</thead>
	<tbody>
		<?php
		if ( $rows ) :
			foreach ( $rows as $r ) :
				?>
		<tr>
			<td><?php echo (int) $r->id; ?></td>
			<td><strong><a href="<?php echo esc_url( admin_url( 'admin.php?page=bhg-hunts-edit&id=' . (int) $r->id ) ); ?>"><?php echo esc_html( $r->title ); ?></a></strong></td>
						<td><?php echo esc_html( number_format_i18n( (float) $r->start_balance, 2 ) ); ?></td>
																				<td><?php echo ( null !== $r->final_balance ) ? esc_html( number_format_i18n( (float) $r->final_balance, 2 ) ) : esc_html( bhg_t( 'label_en_dash', '–' ) ); ?></td>
						<td><?php echo esc_html( bhg_t( $r->status, ucfirst( $r->status ) ) ); ?></td>
						<td><?php echo (int) $r->winners_count; ?></td>
										<td><?php echo $r->closed_at ? esc_html( date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $r->closed_at ) ) ) : esc_html( bhg_t( 'label_en_dash', '–' ) ); ?></td>
			<td>
							<?php
														$results_url = admin_url( 'admin.php?page=bhg-bonus-hunts-results&hunt_id=' . (int) $r->id );
							$edit_url                                = wp_nonce_url( admin_url( 'admin.php?page=bhg-hunts-edit&id=' . (int) $r->id ), 'bhg_edit_hunt_' . (int) $r->id, 'bhg_nonce' );
							?>
			<a class="button" href="<?php echo esc_url( $results_url ); ?>">
				<?php
				echo esc_html( bhg_t( 'button_results', 'Results' ) );
				?>
</a>
			<a class="button" href="<?php echo esc_url( $edit_url ); ?>">
				<?php
				echo esc_html( bhg_t( 'button_edit', 'Edit' ) );
				?>
</a>
			</td>
		</tr>
					<?php endforeach; else : ?>
		<tr><td colspan="8">
						<?php
						echo esc_html( bhg_t( 'notice_no_hunts_found', 'No hunts found.' ) );
						?>
</td></tr>
		<?php endif; ?>
	</tbody>
	</table>

	<?php if ( $pages > 1 ) : ?>
	<div class="tablenav">
		<div class="tablenav-pages">
		<?php
						$base = remove_query_arg( 'paged' );
		for ( $i = 1; $i <= $pages; $i++ ) {
				$url   = add_query_arg( 'paged', $i, $base );
				$class = 'page-numbers';
			if ( $i === $paged ) {
						$class .= ' current';
			}
				printf(
					'<a class="%1$s" href="%2$s">%3$d</a> ',
					esc_attr( $class ),
					esc_url( $url ),
					(int) $i
				);
		}
		?>
		</div>
	</div>
	<?php endif; ?>
</div>
