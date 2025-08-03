<?php
// Archivo modules\mail\mail.php

// Incluir funciones de firma
require_once get_template_directory() . '/app_yacht/modules/mail/signature/signature-functions.php';

// Cargar todas las funciones de Outlook a través del cargador centralizado
require_once get_template_directory() . '/app_yacht/modules/mail/outlook/outlook-loader.php';

// Generar la URL para autenticar en Outlook
$login_url = function_exists( 'pb_outlook_get_login_url' ) ? pb_outlook_get_login_url() : '#';

// Chequear si el usuario tiene un email de Outlook en user_meta
$user_email = get_user_meta( get_current_user_id(), 'outlook_email', true );
$user_email = ! empty( $user_email ) ? esc_html( $user_email ) : '';

// Verificar si el usuario está conectado a Outlook
$is_connected = function_exists( 'pb_outlook_is_connected' ) ? pb_outlook_is_connected( get_current_user_id() ) : false;
?>
<div class="container mail-container">
	<h2 class="text-left mb-3">Mail</h2>

	<div class="d-flex justify-content-left align-items-center mb-3">
		<?php if ( $is_connected ) : ?>
		<!-- Botón para desconectar Outlook -->
		<a class="btn btn-danger btn-sm outlook-auth-button" href="#" data-action="disconnect">
			Disconnect my Outlook account
		</a>
		<?php else : ?>
		<!-- Botón para conectar Outlook -->
		<a class="btn btn-primary btn-sm outlook-auth-button" href="<?php echo esc_url( $login_url ); ?>">
			Connect my Outlook account
		</a>
		<?php endif; ?>
	</div>

	<!-- Mensaje de cuenta conectada -->
	<?php if ( isset( $_GET['outlook'] ) && $_GET['outlook'] === 'success' ) : ?>
		<p style="color:green; font-weight: 600;">
			Your account <?php echo ( ! empty( $user_email ) ? '<strong>' . $user_email . '</strong>' : 'Outlook' ); ?> has been successfully connected!
		</p>
	<?php endif; ?>

	<!-- Formulario para enviar (Outlook en este ejemplo) -->
	<div class="mail-send-form mt-4">
		<form id="form-outlook-mail" class="mail-form">
			<div class="row">
				<div class="col-md-12">
					<label for="correo-destino">To: <small>(separate multiple emails with commas)</small></label>
					<input type="text" id="correo-destino" name="correo-destino" class="form-control" required>
				</div>
			</div>
			<div class="row mt-2">
				<div class="col-md-6">
					<label for="correo-cc">CC: <small>(optional)</small></label>
					<input type="text" id="correo-cc" name="correo-cc" class="form-control">
				</div>
				<div class="col-md-6">
					<label for="correo-bcc">BCC: <small>(optional)</small></label>
					<input type="text" id="correo-bcc" name="correo-bcc" class="form-control">
				</div>
			</div>
			<div class="row mt-2">
				<div class="col-md-12">
					<label for="asunto">Subject:</label>
					<input type="text" id="asunto" name="asunto" class="form-control" required>
				</div>
			</div>

			<p id="contentLabel" class="m-0 mt-2">Content:</p>
			<div class="toolbar my-0" role="toolbar" aria-label="Editor toolbar">
				<!-- Formato de texto -->
				<button id="boldBtn" type="button" title="Negrita" aria-label="Bold"><b>B</b></button>
				<button id="italicBtn" type="button" title="Cursiva" aria-label="Italic"><i>I</i></button>
				<button id="underlineBtn" type="button" title="Subrayado" aria-label="Underline"><u>U</u></button>
				
				<div class="toolbar-divider"></div>
				
				<!-- Alineación -->
				<button id="alignLeftBtn" type="button" title="Alinear a la izquierda" aria-label="Align left"><i class="fas fa-align-left"></i></button>
				<button id="alignCenterBtn" type="button" title="Centrar" aria-label="Align center">≡</button>
				<button id="alignRightBtn" type="button" title="Alinear a la derecha" aria-label="Align right"><i class="fas fa-align-right"></i></button>
				
				<div class="toolbar-divider"></div>
				
				<!-- Listas -->
				<button id="bulletListBtn" type="button" title="Lista con viñetas" aria-label="Bulleted list">•</button>
				<button id="numberedListBtn" type="button" title="Lista numerada" aria-label="Numbered list">1.</button>
				<button id="indentBtn" type="button" title="Aumentar sangría" aria-label="Increase indent">→</button>
				<button id="outdentBtn" type="button" title="Disminuir sangría" aria-label="Decrease indent">←</button>
				
				<div class="toolbar-divider"></div>
				
				<!-- Fuentes seguras para email -->
				<select id="fontSelect" title="Fuente" class="email-fonts-select">
					<option value="Arial" selected>Arial</option>
					<option value="Times New Roman">Times New Roman</option>
					<option value="Courier New">Courier New</option>
					<option value="Verdana">Verdana</option>
					<option value="Georgia">Georgia</option>
					<option value="Tahoma">Tahoma</option>
					<option value="Helvetica">Helvetica</option>
				</select>
				
				<select id="fontSizeSelect" title="Tamaño de fuente">
					<option value="1">Pequeño</option>
					<option value="3" selected>Normal</option>
					<option value="5">Grande</option>
					<option value="7">Muy grande</option>
				</select>
				
				<div class="toolbar-divider"></div>
				
				<!-- Colores -->
				<label for="textColorBtn" class="visually-hidden">Text color</label> <!-- Hidden label for accessibility -->
				<input type="color" id="textColorBtn" title="Color de texto" aria-label="Text color" value="#000000">
				 <label for="bgColorBtn" class="visually-hidden">Background color</label> <!-- Hidden label for accessibility -->
				<input type="color" id="bgColorBtn" title="Color de fondo" aria-label="Background color" value="#ffffff">
				
				<div class="toolbar-divider"></div>
				
				<!-- Enlaces e imágenes -->
				<button id="linkBtn" type="button" title="Insertar enlace" aria-label="Insert link">🔗</button>
				<button id="imageBtn" type="button" title="Insertar imagen" aria-label="Insert image">🖼️</button>
			</div>

			<div class="form-group mt-0">
				<div id="contenido" class="email-content form-control" contenteditable="true" role="textbox" aria-multiline="true" aria-labelledby="contentLabel"></div>
			</div>

			<div class="row">
				<div class="col-12">
					<button type="submit" id="outlook-send-mail" class="btn btn-primary w-100 mt-3">Send Email</button>
				</div>
				<div class="col-12">
					<?php 
					// Llamar al shortcode [outlook_signature]
					// Esto requiere que el plugin "Mail Signature Pro" (o como lo hayas llamado) esté activo.
					// De ese modo, do_shortcode('[outlook_signature]') ejecuta el contenido definido en el plugin.
					echo do_shortcode( '[outlook_signature]' );
					?>
				</div>
			</div>

			
				
		</form>

		<div id="mail-message-container"></div>
	</div>
</div>

<!-- Campo oculto para almacenar la fuente seleccionada (usado por storage.js) -->
<input type="hidden" id="selected_font" name="selected_font" value="Arial">
