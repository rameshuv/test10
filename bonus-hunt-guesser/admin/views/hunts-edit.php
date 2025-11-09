<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( ! current_user_can( 'manage_options' ) ) {
	wp_die( esc_html( bhg_t( 'you_do_not_have_sufficient_permissions_to_access_this_page', 'You do not have sufficient permissions to access this page.' ) ) );
}

$hunt_id = isset( $_GET['id'] ) ? absint( wp_unslash( $_GET['id'] ) ) : 0;
if ( ! $hunt_id ) {
	wp_die( esc_html( bhg_t( 'missing_hunt_id', 'Missing hunt id' ) ) );
}

check_admin_referer( 'bhg_edit_hunt_' . $hunt_id, 'bhg_nonce' );

// Handle delete guess action
if ( isset( $_POST['bhg_remove_guess'] ) ) {
	check_admin_referer( 'bhg_remove_guess_action', 'bhg_remove_guess_nonce' );
		$guess_id = isset( $_POST['guess_id'] ) ? absint( wp_unslash( $_POST['guess_id'] ) ) : 0;
	if ( $guess_id > 0 && function_exists( 'bhg_remove_guess' ) ) {
		bhg_remove_guess( $guess_id );
		echo '<div class="notice notice-success is-dismissible"><p>' . esc_html( bhg_t( 'guess_removed', 'Guess removed.' ) ) . '</p></div>';
	}
}

if ( ! function_exists( 'bhg_get_hunt' ) || ! function_exists( 'bhg_get_hunt_participants' ) ) {
	wp_die( esc_html( bhg_t( 'missing_helper_functions_please_include_classbhgbonushuntshelpersphp', 'Missing helper functions. Please include class-bhg-bonus-hunts-helpers.php.' ) ) );
}

$hunt = bhg_get_hunt( $hunt_id );
if ( ! $hunt ) {
	wp_die( esc_html( bhg_t( 'hunt_not_found', 'Hunt not found' ) ) );
}

$paged    = max( 1, isset( $_GET['ppaged'] ) ? absint( wp_unslash( $_GET['ppaged'] ) ) : 1 );
$per_page = 30;
$data     = bhg_get_hunt_participants( $hunt_id, $paged, $per_page );
$rows     = $data['rows'];
$total    = (int) $data['total'];
$pages    = max( 1, (int) ceil( $total / $per_page ) );
?>
<div class="wrap">
	<h1><?php echo esc_html( sprintf( bhg_t( 'edit_hunt_s', 'Edit Hunt — %s' ), $hunt->title ) ); ?></h1>

	<!-- Your existing edit form for the hunt would be above this line -->

	<h2 style="margin-top:2em;">
	<?php
	echo esc_html( bhg_t( 'participants', 'Participants' ) );
	?>
</h2>
		<p>
		<?php
		/* translators: %s: number of participants */
		echo esc_html( sprintf( _n( '%s participant', '%s participants', $total, 'bonus-hunt-guesser' ), number_format_i18n( $total ) ) );
		?>
		</p>

	<table class="widefat striped">
	<thead>
		<tr>
		<th>
		<?php
		echo esc_html( bhg_t( 'sc_user', 'User' ) );
		?>
</th>
		<th>
		<?php
		echo esc_html( bhg_t( 'sc_guess', 'Guess' ) );
		?>
</th>
		<th>
		<?php
		echo esc_html( bhg_t( 'date', 'Date' ) );
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
				$u    = get_userdata( (int) $r->user_id );
				$name = $u ? $u->user_login : ( '#' . $r->user_id );
				?>
		<tr>
			<td><a href="<?php echo esc_url( admin_url( 'user-edit.php?user_id=' . (int) $r->user_id ) ); ?>"><?php echo esc_html( $name ); ?></a></td>
			<td><?php echo esc_html( number_format_i18n( (float) $r->guess, 2 ) ); ?></td>
					<td><?php echo $r->created_at ? esc_html( date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $r->created_at ) ) ) : esc_html( bhg_t( 'label_emdash', '—' ) ); ?></td>
			<td>
			<form method="post" style="display:inline">
				<?php wp_nonce_field( 'bhg_remove_guess_action', 'bhg_remove_guess_nonce' ); ?>
				<input type="hidden" name="guess_id" value="<?php echo (int) $r->id; ?>">
				<button type="submit" name="bhg_remove_guess" class="button button-link-delete" onclick="return confirm('<?php echo esc_js( bhg_t( 'remove_this_guess', 'Remove this guess?' ) ); ?>');">
				<?php
				echo esc_html( bhg_t( 'remove', 'Remove' ) );
				?>
				</button>
			</form>
			</td>
		</tr>
						<?php endforeach; else : ?>
		<tr><td colspan="4">
							<?php
							echo esc_html( bhg_t( 'no_participants_yet', 'No participants yet.' ) );
							?>
</td></tr>
		<?php endif; ?>
	</tbody>
	</table>

	<?php if ( $pages > 1 ) : ?>
	<div class="tablenav">
		<div class="tablenav-pages">
		<?php
						$base = remove_query_arg( 'ppaged' );
		for ( $i = 1; $i <= $pages; $i++ ) {
				$url   = add_query_arg( 'ppaged', $i, $base );
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
