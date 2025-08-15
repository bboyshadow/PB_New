// Archivo modules\mail\outlook\outlook-ajax.js
(function($){
    $(document).ready(function() {
        
        // Inline message helpers (scoped inside IIFE to use the local $ alias)
        function getEmailInlineMsgContainer() {
            let $container = $('#outlook-send-mail-messages');
            if ($container.length === 0) {
                $container = $('<div id="outlook-send-mail-messages" class="email-inline-messages" aria-live="polite" aria-atomic="true" style="margin-top:10px;"></div>');
                const $btn = $('#outlook-send-mail');
                if ($btn.length) {
                    $btn.after($container);
                }
            }
            return $container;
        }

        function showEmailInlineMessage(type, text) {
            const $container = getEmailInlineMsgContainer();
            const cls = type === 'success' ? 'alert-success' : type === 'warning' ? 'alert-warning' : 'alert-danger';
            const $alert = $('<div role="alert" class="alert"></div>').addClass(cls).text(text);
            $container.empty().append($alert);
        }

        // loadGoogleFontsAPI() eliminada - no usar Google Fonts en email.
        
        // The saveFormContent and restoreFormContent functions are now defined in mail.js
        // and it's assumed that mail.js is loaded before this script.

        // Check if pbOutlookData is defined
        if (typeof pbOutlookData === 'undefined') {
            (window.AppYacht?.error || console.error)('Error: pbOutlookData variable is not defined. Make sure yacht-functions.php is loading correctly.');
        } else {
            
        }

        // --- Email Confirmation Modal Creation ---
        let emailConfirmModal = null;

        function createEmailConfirmModal() {
            if (emailConfirmModal) return;

            emailConfirmModal = document.createElement('div');
            emailConfirmModal.id = 'email-confirm-modal';
            emailConfirmModal.className = 'email-modal';
            emailConfirmModal.style.display = 'none';
            emailConfirmModal.setAttribute('role', 'dialog');
            emailConfirmModal.setAttribute('aria-modal', 'true');
            emailConfirmModal.setAttribute('aria-labelledby', 'email-confirm-title');

            emailConfirmModal.innerHTML = `
                <div class="modal-content">
                    <h3 id="email-confirm-title">Confirm Email Sending</h3>
                    <div class="modal-body">
                        <div class="email-preview">
                            <p><strong>To:</strong> <span id="preview-to"></span></p>
                            <div id="preview-cc-container" style="display: none;">
                                <p><strong>CC:</strong> <span id="preview-cc"></span></p>
                            </div>
                            <div id="preview-bcc-container" style="display: none;">
                                <p><strong>BCC:</strong> <span id="preview-bcc"></span></p>
                            </div>
                            <p><strong>Subject:</strong> <span id="preview-subject"></span></p>
                            <div class="email-content-preview">
                                <p><strong>Content:</strong></p>
                                <div id="preview-content" class="content-preview-box"></div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-actions">
                        <button type="button" id="email-confirm-btn" class="button btn-primary">Send</button>
                        <button type="button" id="email-cancel-btn" class="button btn-secondary">Cancel</button>
                    </div>
                </div>
            `;

            document.body.appendChild(emailConfirmModal);

            // Add event listeners
            document.getElementById('email-confirm-btn').addEventListener('click', handleEmailConfirmOk);
            document.getElementById('email-cancel-btn').addEventListener('click', hideEmailConfirmModal);

            // Close modal on Escape key
            emailConfirmModal.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') {
                    hideEmailConfirmModal();
                }
            });
        }

        function showEmailConfirmModal(emailData) {
            if (!emailConfirmModal) createEmailConfirmModal();

            // Populate preview data
            document.getElementById('preview-to').textContent = emailData.to;
            
            // Handle CC and BCC visibility
            const ccContainer = document.getElementById('preview-cc-container');
            const bccContainer = document.getElementById('preview-bcc-container');
            
            if (emailData.cc) {
                document.getElementById('preview-cc').textContent = emailData.cc;
                ccContainer.style.display = 'block';
            } else {
                ccContainer.style.display = 'none';
            }
            
            if (emailData.bcc) {
                document.getElementById('preview-bcc').textContent = emailData.bcc;
                bccContainer.style.display = 'block';
            } else {
                bccContainer.style.display = 'none';
            }
            
            document.getElementById('preview-subject').textContent = emailData.subject;
            document.getElementById('preview-content').innerHTML = emailData.body;

            // Store email data for sending
            emailConfirmModal.pendingEmailData = emailData;

            // Show modal centered
            emailConfirmModal.style.display = 'block';
            emailConfirmModal.style.position = 'fixed';
            emailConfirmModal.style.top = '50%';
            emailConfirmModal.style.left = '50%';
            emailConfirmModal.style.transform = 'translate(-50%, -50%)';
            emailConfirmModal.style.zIndex = '9999';

            // Focus on confirm button
            document.getElementById('email-confirm-btn').focus();
        }

        function hideEmailConfirmModal() {
            if (emailConfirmModal) {
                emailConfirmModal.style.display = 'none';
                emailConfirmModal.pendingEmailData = null;
            }
        }

        function handleEmailConfirmOk() {
            const emailData = emailConfirmModal.pendingEmailData;
            if (!emailData) return;

            hideEmailConfirmModal();
            sendEmailRequest(emailData);
        }

        // --- Email Content Validation Functions ---
        function validateEmailContent(bodyHtml, bodyText) {
            const errors = [];

            // 1. Empty content validation
            if (!bodyText || bodyText.length === 0) {
                errors.push("Email body cannot be empty. Please add some content before sending.");
                return errors;
            }

            // 2. Minimum content validation
            const minCharacters = 10;
            const minWords = 3;
            const wordCount = bodyText.split(/\s+/).filter(word => word.length > 0).length;

            if (bodyText.length < minCharacters || wordCount < minWords) {
                errors.push("Email content is too short. Please provide more details.");
            }

            return errors;
        }

        // --- Enhanced Email Sending Function ---
        function sendEmailRequest(emailData) {
            // Use loading state if available
            try {
                window.AppYacht?.ui?.setLoading?.(true);
            } catch (e) {}

            $.post(pbOutlookData.ajaxurl, emailData)
            .done(function(response){
                try {
                    window.AppYacht?.ui?.setLoading?.(false);
                } catch (e) {}

                if (response && response.success){
                    const message = 'Email sent: ' + (response.data || 'OK');
                    // Display success message only in email module container
                    showEmailInlineMessage('success', message);
                 } else {
                     const msg = (response && response.data) ? response.data : 'Server error while sending the email.';
                     (window.AppYacht?.error || console.error)('Server response error:', response);
                     // Display error message only in email module container
                     showEmailInlineMessage('error', msg);
                 }
            })
            .fail(function(jqXHR, textStatus, errorThrown){
                try {
                    window.AppYacht?.ui?.setLoading?.(false);
                } catch (e) {}

                (window.AppYacht?.error || console.error)('AJAX error sending email:', textStatus, errorThrown, jqXHR && jqXHR.responseText);
                
                let message = 'Server connection error while sending email.';
                try {
                    if (jqXHR && jqXHR.responseJSON && jqXHR.responseJSON.data) {
                        message = jqXHR.responseJSON.data;
                    } else if (jqXHR && jqXHR.responseText) {
                        const maybe = JSON.parse(jqXHR.responseText);
                        if (maybe && typeof maybe === 'object' && 'data' in maybe) {
                            message = maybe.data || message;
                        }
                    }
                } catch (err) {
                    (window.AppYacht?.log || console.log)('Error parsing error response:', err);
                }
                
                // Display error message only in email module container
                showEmailInlineMessage('error', message);
            });
        }
        
        // Check if there is an outlook=success parameter in the URL and show a message
        function checkOutlookSuccess() {
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('outlook') === 'success') {
                // Mostrar mensaje de éxito sin recargar
                const userEmail = $('.mail-container').data('user-email') || 'Outlook';
                const successMessage = $('<p style="color:green; font-weight: 600;">Your account <strong>' + userEmail + '</strong> has been successfully connected!</p>');
                
                // Eliminar mensaje existente si hay alguno
                $('.mail-container p[style*="color:green"]').remove();
                
                // Insertar mensaje después del botón
                $('.outlook-auth-button').parent().after(successMessage);
                
                // Actualizar la URL sin recargar la página (eliminar parámetro outlook=success)
                const newUrl = window.location.pathname + window.location.search.replace(/[?&]outlook=success/, '');
                window.history.replaceState({}, document.title, newUrl);
                
                // Actualizar el botón a "Disconnect"
                updateButtonToDisconnect();
                
                // Restore form content after successful connection
                restoreFormContent();
            }
        }
        
        // Función para actualizar el botón a "Disconnect"
        function updateButtonToDisconnect() {
            const $button = $('.outlook-auth-button');
            $button.text('Disconnect my Outlook account')
                  .removeClass('btn-primary')
                  .addClass('btn-danger')
                  .attr('href', '#')
                  .data('action', 'disconnect');
        }
        
        // Función para actualizar el botón a "Connect"
        function updateButtonToConnect() {
            const $button = $('.outlook-auth-button');
            const loginUrl = $button.data('login-url');
            $button.text('Connect my Outlook account')
                  .removeClass('btn-danger')
                  .addClass('btn-primary')
                  .attr('href', loginUrl)
                  .removeData('action');
        }
        
        // Verificar si hay parámetro outlook=success al cargar
        checkOutlookSuccess()

        // Botón "Connect/Disconnect my Outlook account"
        $('.outlook-auth-button').on('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            // Save form content before any action
            // Llamar a la función global definida en mail.js
            if (typeof saveFormContent === 'function') {
                 saveFormContent();
            } else {
                 (window.AppYacht?.error || console.error)('saveFormContent function not found. Ensure mail.js is loaded first.');
            }

            // Verificar si es acción de desconexión
            if ($(this).data('action') === 'disconnect') {
                // Confirmar antes de desconectar usando UI system
                const confirmDisconnect = () => {
                    // Show loading indicator
                    const $button = $(this);
                    const originalText = $button.text();
                    $button.text('Disconnecting...').prop('disabled', true);
                    
                    // Verificar si pbOutlookData está definido
                    if (typeof pbOutlookData === 'undefined') {
                        // Show error only in mail module
                        showEmailInlineMessage('error', 'Error: Cannot disconnect because configuration data is missing. Please reload the page and try again.');
                        $button.text(originalText).prop('disabled', false);
                        return;
                    }
                    
                    // Registrar datos para depuración
                (window.AppYacht?.log || console.log)('Sending disconnect request with nonce:', pbOutlookData.nonce);
                (window.AppYacht?.log || console.log)('Nonce timestamp:', pbOutlookData.timestamp);
                (window.AppYacht?.log || console.log)('Current time:', Math.floor(Date.now() / 1000));
                (window.AppYacht?.log || console.log)('Time difference:', Math.floor(Date.now() / 1000) - pbOutlookData.timestamp);
                    
                    // Enviar solicitud AJAX para desconectar
                    $.ajax({
                        url: pbOutlookData.ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'pb_outlook_disconnect',
                            nonce: pbOutlookData.nonce
                        },
                        success: function(response) {
                            if (response.success) {
                                // Show success only in mail module and reload to update state
                                showEmailInlineMessage('success', response.data || 'Account disconnected successfully.');
                                window.location.reload();
                            } else {
                                (window.AppYacht?.error || console.error)('Response error:', response);
                                const errorMsg = 'Error: ' + (response.data || 'Unknown error when disconnecting');
                                showEmailInlineMessage('error', errorMsg);
                                // Restore button
                                $button.text(originalText).prop('disabled', false);
                            }
                        },
                        error: function(xhr, status, error) {
                            (window.AppYacht?.error || console.error)('AJAX error:', error);
                            // Extract only the error message, not the full HTML
                            let errorMessage = 'Server connection error';
                            try {
                                (window.AppYacht?.log || console.log)('Full AJAX error:', xhr);
                                
                                // Try to extract error message from JSON response if exists
                                if (xhr.responseJSON && xhr.responseJSON.data) {
                                    errorMessage = xhr.responseJSON.data;
                                    (window.AppYacht?.log || console.log)('Error extracted from JSON:', errorMessage);
                                }
                                // If no JSON, try to extract from HTML
                                else if (xhr.responseText) {
                                    const responseText = xhr.responseText;
                                    (window.AppYacht?.log || console.log)('Full text response:', responseText);
                                    
                                    // Check for 400 (Bad Request) - likely nonce error
                                    if (xhr.status === 400) {
                                        (window.AppYacht?.log || console.log)('400 error detected, full response:', responseText);
                                        errorMessage = 'Security error. Please reload the page and try again';
                                        
                                        // Try to regenerate the nonce automatically
                                        if (typeof ajaxurl !== 'undefined') {
                                            (window.AppYacht?.log || console.log)('Attempting to regenerate nonce...');
                                            // This would require an additional server endpoint
                                        }
                                    }
                                    // Check for 403 (Forbidden) - nonce error
                                    else if (xhr.status === 403) {
                                        (window.AppYacht?.log || console.log)('403 error detected, full response:', responseText);
                                        errorMessage = 'Security error. Please reload the page to refresh your session';
                                    }
                                    // Check for 500 with specific message
                                    else if (xhr.status === 500) {
                                        (window.AppYacht?.log || console.log)('500 error detected, full response:', responseText);
                                        errorMessage = 'Internal server error when disconnecting the account';
                                    }
                                    // Try to extract error message from HTML
                                    else if (responseText.includes('<p>')) {
                                        const errorStart = responseText.indexOf('<p>') + 3;
                                        const errorEnd = responseText.indexOf('</p>', errorStart);
                                        if (errorEnd > errorStart) {
                                            errorMessage = responseText.substring(errorStart, errorEnd);
                                        }
                                    }
                                }
                            } catch (e) {
                                (window.AppYacht?.error || console.error)('Error processing response:', e);
                            }
                            
                            // Show a more descriptive error message in the email module only
                            const fullErrorMsg = 'Could not disconnect the account: ' + errorMessage + '. Please try again later.';
                            showEmailInlineMessage('error', fullErrorMsg);
                            // Restore button
                            $button.text(originalText).prop('disabled', false);
                        }
                    });
                };

                // Show confirmation modal instead of browser confirm()
                showDisconnectConfirmModal(confirmDisconnect);
            } else {
                // Acción de conexión (comportamiento original)
                let authUrl = $(this).attr('href');
                if (!authUrl) {
                    showEmailInlineMessage('error', 'Error: Outlook authentication URL not found.');
                    return;
                }
                // Save form content before redirecting
                saveFormContent();
                window.location.href = authUrl;
            }
        });

        // Enhanced email sending with validation and confirmation (only on Mail module)
        if ($('#form-outlook-mail').length) {
            $('#outlook-send-mail').on('click', function(e) {
                e.preventDefault();

            // Verificación básica de datos críticos de configuración
            if (typeof pbOutlookData === 'undefined' || !pbOutlookData.nonce || !pbOutlookData.ajaxurl) {
                // Show error only in mail module
                showEmailInlineMessage('error', 'Error: Missing critical data to send the email. Please reload the page.');
                (window.AppYacht?.error || console.error)('pbOutlookData is missing or incomplete:', pbOutlookData);
                return; 
            }

            // Obtener y limpiar valores
            const $toInput = $('#email-to');
            const $ccInput = $('#email-cc');
            const $bccInput = $('#email-bcc');
            const $subjectInput = $('#email-subject');
            const $bodyEl = $('#email-content');

            const to = ($toInput.val() || '').trim();
            const cc = ($ccInput.val() || '').trim();
            const bcc = ($bccInput.val() || '').trim();
            const subject = ($subjectInput.val() || '').trim();
            const bodyHtml = $bodyEl.html() || '';
            // Extraer texto plano del HTML para validar que haya contenido real
            const bodyText = $('<div>').html(bodyHtml).text().trim();

            // Resetear estados visuales de error
            $toInput.removeClass('is-invalid');
            $subjectInput.removeClass('is-invalid');
            $bodyEl.removeClass('border border-danger');

            // Basic field validation
            const missing = [];
            if (!to) { missing.push('To'); $toInput.addClass('is-invalid'); }
            if (!subject) { missing.push('Subject'); $subjectInput.addClass('is-invalid'); }
            if (!bodyText) { missing.push('Body'); $bodyEl.addClass('border border-danger'); }

            if (missing.length > 0) {
                const missingMsg = 'Please complete the required fields: ' + missing.join(', ') + '.';
                showEmailInlineMessage('warning', missingMsg);
                if (!to) { $toInput.trigger('focus'); }
                else if (!subject) { $subjectInput.trigger('focus'); }
                else { $bodyEl.trigger('focus'); }
                return;
            }

            // Advanced content validation
            const contentErrors = validateEmailContent(bodyHtml, bodyText);
            if (contentErrors.length > 0) {
                $bodyEl.addClass('border border-danger');
                showEmailInlineMessage('warning', contentErrors[0]);
                $bodyEl.trigger('focus');
                return;
            }

            // Prepare email data
            const emailData = {
                action:   'pb_outlook_send_mail',
                nonce:    pbOutlookData.nonce,
                to:       to,
                cc:       cc,
                bcc:      bcc,
                subject:  subject,
                body:     bodyHtml
            };

            // Show confirmation modal before sending
            showEmailConfirmModal(emailData);
            });
        }
    });
})(jQuery);

// Inline message helpers are scoped inside the IIFE above to ensure `$` is defined and to avoid leaking into other modules.

function showDisconnectConfirmModal(onConfirm) {
    // Always use a dedicated, temporary modal for disconnect confirmation
    let modal = document.getElementById('outlook-disconnect-confirm-modal');
    let createdTemp = false;

    if (!modal) {
        // Create a lightweight modal
        modal = document.createElement('div');
        modal.id = 'outlook-disconnect-confirm-modal';
        modal.style.display = 'none';
        modal.style.position = 'fixed';
        modal.style.top = '50%';
        modal.style.left = '50%';
        modal.style.transform = 'translate(-50%, -50%)';
        modal.style.zIndex = '9999';
        modal.style.background = '#fff';
        modal.style.padding = '16px';
        modal.style.border = '1px solid #ccc';
        modal.style.borderRadius = '8px';
        modal.style.boxShadow = '0 10px 25px rgba(0,0,0,0.2)';
        modal.innerHTML = `
            <div id="outlook-disconnect-confirm-content" style="margin-bottom:8px; font-size:14px;"></div>
            <div class="confirm-actions" style="display:flex; gap:8px; justify-content:flex-end; margin-top:12px;">
                <button id="outlook-disconnect-cancel-btn" type="button" class="btn btn-secondary btn-sm">Cancel</button>
                <button id="outlook-disconnect-confirm-btn" type="button" class="btn btn-danger btn-sm">Disconnect</button>
            </div>
        `;
        document.body.appendChild(modal);
        createdTemp = true;
    }

    // Set confirmation text
    const content = document.getElementById('outlook-disconnect-confirm-content');
    if (content) content.textContent = 'Are you sure you want to disconnect your Outlook account?';

    // Wire up buttons
    const cancelBtn = document.getElementById('outlook-disconnect-cancel-btn');
    const okBtn = document.getElementById('outlook-disconnect-confirm-btn');

    const cleanup = () => {
        modal.style.display = 'none';
        // Remove temp modal if it was created here
        if (createdTemp && modal && modal.parentNode) modal.parentNode.removeChild(modal);
        // Remove listeners
        cancelBtn?.removeEventListener('click', onCancel);
        okBtn?.removeEventListener('click', onOk);
        document.removeEventListener('keydown', onKeyDown);
    };

    const onCancel = () => cleanup();
    const onOk = () => { cleanup(); try { onConfirm && onConfirm(); } catch (e) { (window.AppYacht?.error||console.error)(e); } };
    const onKeyDown = (e) => { if (e.key === 'Escape') onCancel(); };

    cancelBtn?.addEventListener('click', onCancel);
    okBtn?.addEventListener('click', onOk);
    document.addEventListener('keydown', onKeyDown);

    // Show modal centered
    modal.style.display = 'block';
}
