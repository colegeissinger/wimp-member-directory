<?php
global $wpdb, $pmpro_msg, $pmpro_msgt, $pmpro_levels, $current_user;
if ( $pmpro_msg ) {
	?>
	<div class="pmpro_message <?php echo esc_attr( $pmpro_msgt ); ?>"><?php echo esc_html( $pmpro_msg ); ?></div>
<?php
}
?>
<p>Interested in a WIMP+ membership? Here's some amazing reasons why you <strong>NEED</strong> this.</p>
<ul>
	<li><b>Membership Directory </b>Build your public-facing profile and portfolio and make an impression on new clients.</li>
	<li><b>Private Discussion Board</b> Get access to a secure discussion board where clients can’t hear you scream.</li>
	<li><b>Coworking Access</b> 4 days a month access at <a href="http://wimpspace.com">WIMPspace</a>, WIMP’s coworking and colearning space / clubhouse in downtown Santa Rosa.</li>
	<li><b>Discounts on Workshops</b> Discounts on select classes and workshops.</li>
	<li><b>Vendor Discounts</b> Discounts from a list of awesome companies relevant to you and your business.</li>
	<li><b>Swag &amp; Surprises</b> Discounts on swag and special surprise things!</li>
	<li><b>Direct Referral Program </b>Get added to our database of hand-picked referrals and pay just 5% commission on revenue earned.</li>
	<li><b>Support WIMP</b> Show your love and support to the community you just couldn’t live without.</li>
</ul>
<table id="pmpro_levels_table" class="pmpro_checkout">
	<thead>
	<tr>
		<th><?php esc_html_e( 'Level', 'pmpro' ); ?></th>
		<th><?php esc_html_e( 'Price', 'pmpro' ); ?></th>
		<th>&nbsp;</th>
	</tr>
	</thead>
	<tbody>
	<?php
	$count = 0;
	foreach ( $pmpro_levels as $level ) {
		if ( isset( $current_user->membership_level->ID ) ) {
			$current_level = ( $current_user->membership_level->ID == $level->id );
		} else {
			$current_level = false;
		}
		?>
		<tr class="<?php if ( $count ++ % 2 == 0 ) { ?>odd<?php } ?><?php if ( $current_level == $level ) { ?> active<?php } ?>">
			<td><?php echo $current_level ? '<strong>' . esc_html( $level->name ) . '</strong>' : esc_html( $level->name ); ?></td>
			<td>
				<?php
				if ( pmpro_isLevelFree( $level ) ) {
					$cost_text = '<strong>Free</strong>';
				} else {
					$cost_text = pmpro_getLevelCost( $level, true, true );
				}
				$expiration_text = pmpro_getLevelExpiration( $level );
				if ( ! empty( $cost_text ) && ! empty( $expiration_text ) ) {
					echo wp_kses_post( $cost_text . '<br />' . $expiration_text );
				} elseif ( ! empty( $cost_text ) ) {
					echo wp_kses_post( $cost_text );
				} elseif ( ! empty( $expiration_text ) ) {
					echo wp_kses_post( $expiration_text );
				}
				?>
			</td>
			<td>
				<?php if ( empty( $current_user->membership_level->ID ) ) { ?>
					<a class="pmpro_btn pmpro_btn-select" href="<?php echo esc_url( pmpro_url( 'checkout', '?level=' . urlencode( $level->id ), 'https' ) ); ?>"><?php esc_html_e( 'Select', 'pmpro' ); ?></a>
				<?php } elseif ( ! $current_level ) { ?>
					<a class="pmpro_btn pmpro_btn-select" href="<?php echo esc_url( pmpro_url( 'checkout', '?level=' . urlencode( $level->id ), 'https' ) ); ?>"><?php esc_html_e( 'Select', 'pmpro' ); ?></a>
				<?php } elseif ( $current_level ) { ?>

					<?php
					//if it's a one-time-payment level, offer a link to renew
					if ( ! pmpro_isLevelRecurring( $current_user->membership_level ) && ! empty( $current_user->membership_level->enddate ) ) {
						?>
						<a class="pmpro_btn pmpro_btn-select" href="<?php echo esc_url( pmpro_url( 'checkout', '?level=' . urlencode( $level->id ), 'https' ) ); ?>"><?php esc_html_e( 'Renew', 'pmpro' ); ?></a>
					<?php
					} else {
						?>
						<a class="pmpro_btn disabled" href="<?php echo esc_url( wmd_get_membership_url() ); ?>"><?php esc_html_e( 'Your Level', 'pmpro' ); ?></a>
					<?php
					}
					?>

				<?php } ?>
			</td>
		</tr>
	<?php
	}
	?>
	</tbody>
</table>
<nav id="nav-below" class="navigation" role="navigation">
	<div class="nav-previous alignleft">
		<?php if ( ! empty( $current_user->membership_level->ID ) ) { ?>
			<a href="<?php echo esc_url( wmd_get_membership_url() ); ?>"><?php esc_html_e( '&larr; Return to Your Account', 'pmpro' ); ?></a>
		<?php } ?>
	</div>
</nav>