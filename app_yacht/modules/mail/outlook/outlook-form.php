<?php


if ( ! is_user_logged_in() ) {
	echo '<p>You must be logged into WordPress before connecting your Outlook account.</p>';
	return;
}


if ( ! function_exists( 'pb_outlook_get_login_url' ) ) {
	echo '<p style="color:red;">Error: The function pb_outlook_get_login_url is not available.</p>';
	return;
}


$login_url = pb_outlook_get_login_url();

if ( empty( $login_url ) ) {
	echo '<p style="color:red;">Error: Failed to generate the Outlook login URL.</p>';
} else {
	?>
	<div class="outlook-auth-container">
		<a class="btn btn-primary outlook-auth-button" href="<?php echo esc_url( $login_url ); ?>">
			Connect my Outlook account
		</a>
	</div>
	<?php
}


if ( isset( $_GET['outlook'] ) && $_GET['outlook'] === 'success' ) {
	$user_email    = get_user_meta( get_current_user_id(), 'outlook_email', true );
	$email_display = ! empty( $user_email ) ? '<strong>' . esc_html( $user_email ) . '</strong>' : 'Outlook';
	echo '<p style="color:green;">Your account ' . $email_display . ' has been successfully connected!</p>';
}
?>
