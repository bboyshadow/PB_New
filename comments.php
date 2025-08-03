<?php



if ( post_password_required() ) {
	return;
}
?>

<div id="comments" class="comments-area">

	<?php
	
	if ( have_comments() ) :
		?>
		<h2 class="comments-title">
			<?php
			$creativoypunto_comment_count = get_comments_number();
			if ( '1' === $creativoypunto_comment_count ) {
				printf(
					
					esc_html__( 'One thought on &ldquo;%1$s&rdquo;', 'creativoypunto' ),
					'<span>' . wp_kses_post( get_the_title() ) . '</span>'
				);
			} else {
				printf(
					
					esc_html( _nx( '%1$s thought on &ldquo;%2$s&rdquo;', '%1$s thoughts on &ldquo;%2$s&rdquo;', $creativoypunto_comment_count, 'comments title', 'creativoypunto' ) ),
					number_format_i18n( $creativoypunto_comment_count ), 
					'<span>' . wp_kses_post( get_the_title() ) . '</span>'
				);
			}
			?>
		</h2><!-- .comments-title -->

		<?php the_comments_navigation(); ?>

		<ol class="comment-list">
			<?php
			wp_list_comments(
				array(
					'style'      => 'ol',
					'short_ping' => true,
				)
			);
			?>
		</ol><!-- .comment-list -->

		<?php
		the_comments_navigation();

		
		if ( ! comments_open() ) :
			?>
			<p class="no-comments"><?php esc_html_e( 'Comments are closed.', 'creativoypunto' ); ?></p>
			<?php
		endif;

	endif; 

	comment_form();
	?>

</div><!-- #comments -->
