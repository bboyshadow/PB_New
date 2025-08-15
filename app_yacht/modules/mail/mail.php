<?php



require_once get_template_directory() . '/app_yacht/modules/mail/signature/signature-functions.php';


require_once get_template_directory() . '/app_yacht/modules/mail/outlook/outlook-loader.php';


$login_url = function_exists( 'pb_outlook_get_login_url' ) ? pb_outlook_get_login_url() : '#';


$user_email = get_user_meta( get_current_user_id(), 'outlook_email', true );
$user_email = ! empty( $user_email ) ? esc_html( $user_email ) : '';


$is_connected = function_exists( 'pb_outlook_is_connected' ) ? pb_outlook_is_connected( get_current_user_id() ) : false;
?>
<div class="container mail-container">
	<h2 class="text-left mb-3">Mail</h2>

	<div class="d-flex justify-content-left align-items-center mb-3">
		<?php if ( $is_connected ) : ?>
		<!-- Button to disconnect Outlook -->
		<a class="btn btn-danger btn-sm outlook-auth-button" href="#" data-action="disconnect">
			Disconnect my Outlook account
		</a>
		<?php else : ?>
		<!-- Button to connect Outlook -->
		<a class="btn btn-primary btn-sm outlook-auth-button" href="<?php echo esc_url( $login_url ); ?>">
			Connect my Outlook account
		</a>
		<?php endif; ?>
	</div>

	<!-- Connected account message -->
	<?php if ( isset( $_GET['outlook'] ) && $_GET['outlook'] === 'success' ) : ?>
		<p style="color:green; font-weight: 600;">
			Your account <?php echo ( ! empty( $user_email ) ? '<strong>' . $user_email . '</strong>' : 'Outlook' ); ?> has been successfully connected!
		</p>
	<?php endif; ?>

	<!-- Form to send (Outlook in this example) -->
	<div class="mail-send-form mt-4">
		<form id="form-outlook-mail" class="mail-form">
			<div class="row">
				<div class="col-md-12">
					<label for="email-to">To: <small>(separate multiple emails with commas)</small></label>
					<input type="text" id="email-to" name="correo-destino" class="form-control" required>
				</div>
			</div>
			<div class="row mt-2">
				<div class="col-md-6">
					<label for="email-cc">CC: <small>(optional)</small></label>
					<input type="text" id="email-cc" name="correo-cc" class="form-control">
				</div>
				<div class="col-md-6">
					<label for="email-bcc">BCC: <small>(optional)</small></label>
					<input type="text" id="email-bcc" name="correo-bcc" class="form-control">
				</div>
			</div>
			<div class="row mt-2">
				<div class="col-md-12">
					<label for="email-subject">Subject:</label>
					<input type="text" id="email-subject" name="asunto" class="form-control" required>
				</div>
			</div>

			<p id="contentLabel" class="m-0 mt-2">Content:</p>
			<div class="toolbar my-0" role="toolbar" aria-label="Editor toolbar">
				<!-- Text formatting -->
				<button id="boldBtn" type="button" title="Bold" aria-label="Bold"><b>B</b></button>
				<button id="italicBtn" type="button" title="Italic" aria-label="Italic"><i>I</i></button>
				<button id="underlineBtn" type="button" title="Underline" aria-label="Underline"><u>U</u></button>
				
				<div class="toolbar-divider"></div>
				
				<!-- Alignment -->
				<button id="alignLeftBtn" type="button" title="Align left" aria-label="Align left"><i class="fas fa-align-left"></i></button>
				<button id="alignCenterBtn" type="button" title="Align center" aria-label="Align center">≡</button>
				<button id="alignRightBtn" type="button" title="Align right" aria-label="Align right"><i class="fas fa-align-right"></i></button>
				
				<div class="toolbar-divider"></div>
				
				<!-- Lists -->
				<button id="bulletListBtn" type="button" title="Bulleted list" aria-label="Bulleted list">•</button>
				<button id="numberedListBtn" type="button" title="Numbered list" aria-label="Numbered list">1.</button>
				<button id="indentBtn" type="button" title="Increase indent" aria-label="Increase indent">→</button>
				<button id="outdentBtn" type="button" title="Decrease indent" aria-label="Decrease indent">←</button>
				
				<div class="toolbar-divider"></div>
				
				<!-- Safe email fonts -->
				<select id="fontSelect" title="Font" class="email-fonts-select">
					<option value="Arial" selected>Arial</option>
					<option value="Times New Roman">Times New Roman</option>
					<option value="Courier New">Courier New</option>
					<option value="Verdana">Verdana</option>
					<option value="Georgia">Georgia</option>
					<option value="Tahoma">Tahoma</option>
					<option value="Helvetica">Helvetica</option>
				</select>
				
				<select id="fontSizeSelect" title="Font size">
					<option value="1">Small</option>
					<option value="3" selected>Normal</option>
					<option value="5">Large</option>
					<option value="7">Very large</option>
				</select>
				
				<div class="toolbar-divider"></div>
				
				<!-- Colors -->
				<label for="textColorBtn" class="visually-hidden">Text color</label> <!-- Hidden label for accessibility -->
				<input type="color" id="textColorBtn" title="Text color" aria-label="Text color" value="#000000">
				 <label for="bgColorBtn" class="visually-hidden">Background color</label> <!-- Hidden label for accessibility -->
				<input type="color" id="bgColorBtn" title="Background color" aria-label="Background color" value="#ffffff">
				
				<div class="toolbar-divider"></div>
				
				<!-- Links and images -->
				<button id="linkBtn" type="button" title="Insert link" aria-label="Insert link">🔗</button>
				<button id="imageBtn" type="button" title="Insert image" aria-label="Insert image">🖼️</button>
			</div>

			<div class="form-group mt-0">
				<div id="email-content" class="email-content form-control" contenteditable="true" role="textbox" aria-multiline="true" aria-labelledby="contentLabel"></div>
			</div>

			<div class="row">
				<div class="col-12">
					<button type="button" id="outlook-send-mail" class="btn btn-primary w-100 mt-3">Send Email</button>
				</div>
				<div class="col-12">
					<?php 
					
					
					echo do_shortcode( '[outlook_signature]' );
					?>
				</div>
			</div>

			
				
		</form>

		<div id="mail-message-container"></div>
	</div>
</div>

<!-- Hidden field to store selected font (used by storage.js) -->
<input type="hidden" id="selected_font" name="selected_font" value="Arial">
