// Archivo modules\mail\outlook\outlook-ajax.js
(function($){
    $(document).ready(function() {
        
        // loadGoogleFontsAPI() eliminada - no usar Google Fonts en email.
        
        // Las funciones saveFormContent y restoreFormContent ahora están definidas en mail.js
        // y se asume que mail.js se carga antes que este script.

        // Verificar si pbOutlookData está definido
        if (typeof pbOutlookData === 'undefined') {
            (window.AppYacht?.error || console.error)('Error: La variable pbOutlookData no está definida. Asegúrate de que yacht-functions.php está cargando correctamente.');
        } else {
            
        }
        
        // Verificar si hay parámetro outlook=success en la URL y mostrar mensaje
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
                
                // Restaurar el contenido del formulario después de la conexión exitosa
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
            
            // Guardar el contenido del formulario antes de cualquier acción
            // Llamar a la función global definida en mail.js
            if (typeof saveFormContent === 'function') {
                 saveFormContent();
            } else {
                 (window.AppYacht?.error || console.error)('saveFormContent function not found. Ensure mail.js is loaded first.');
            }

            // Verificar si es acción de desconexión
            if ($(this).data('action') === 'disconnect') {
                // Confirmar antes de desconectar
                if (confirm('¿Estás seguro de que deseas desconectar tu cuenta de Outlook?')) {
                    // Mostrar indicador de carga
                    const $button = $(this);
                    const originalText = $button.text();
                    $button.text('Desconectando...').prop('disabled', true);
                    
                    // Verificar si pbOutlookData está definido
                    if (typeof pbOutlookData === 'undefined') {
                        alert('Error: No se puede desconectar porque faltan datos de configuración. Por favor, recarga la página e intenta de nuevo.');
                        $button.text(originalText).prop('disabled', false);
                        return;
                    }
                    
                    // Registrar datos para depuración
                (window.AppYacht?.log || console.log)('Enviando solicitud de desconexión con nonce:', pbOutlookData.nonce);
                (window.AppYacht?.log || console.log)('Timestamp del nonce:', pbOutlookData.timestamp);
                (window.AppYacht?.log || console.log)('Tiempo actual:', Math.floor(Date.now() / 1000));
                (window.AppYacht?.log || console.log)('Diferencia de tiempo:', Math.floor(Date.now() / 1000) - pbOutlookData.timestamp);
                    
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
                                alert(response.data);
                                // Recargar la página para actualizar el estado del botón
                                window.location.reload();
                            } else {
                                (window.AppYacht?.error || console.error)('Error en la respuesta:', response);
                                alert('Error: ' + (response.data || 'Error desconocido al desconectar'));
                                // Restaurar el botón
                                $button.text(originalText).prop('disabled', false);
                            }
                        },
                        error: function(xhr, status, error) {
                            (window.AppYacht?.error || console.error)('Error AJAX:', error);
                            // Extraer solo el mensaje de error, no todo el HTML
                            let errorMessage = 'Error en la conexión con el servidor';
                            try {
                                (window.AppYacht?.log || console.log)('Error AJAX completo:', xhr);
                                
                                // Intentar extraer mensaje de error de la respuesta JSON si existe
                                if (xhr.responseJSON && xhr.responseJSON.data) {
                                    errorMessage = xhr.responseJSON.data;
                                    (window.AppYacht?.log || console.log)('Error extraído de JSON:', errorMessage);
                                }
                                // Si no hay JSON, intentar extraer del HTML
                                else if (xhr.responseText) {
                                    const responseText = xhr.responseText;
                                    (window.AppYacht?.log || console.log)('Respuesta de texto completa:', responseText);
                                    
                                    // Verificar si es un error 400 (Bad Request) - Probablemente error de nonce
                                    if (xhr.status === 400) {
                                        (window.AppYacht?.log || console.log)('Error 400 detectado, respuesta completa:', responseText);
                                        errorMessage = 'Error de seguridad. Por favor, recarga la página e intenta de nuevo';
                                        
                                        // Intentar regenerar el nonce automáticamente
                                        if (typeof ajaxurl !== 'undefined') {
                                            (window.AppYacht?.log || console.log)('Intentando regenerar nonce...');
                                            // Esta parte requeriría un endpoint adicional en el servidor
                                        }
                                    }
                                    // Verificar si es un error 403 (Forbidden) - Error de nonce
                                    else if (xhr.status === 403) {
                                        (window.AppYacht?.log || console.log)('Error 403 detectado, respuesta completa:', responseText);
                                        errorMessage = 'Error de seguridad. Por favor, recarga la página para actualizar tu sesión';
                                    }
                                    // Verificar si es un error 500 con mensaje específico
                                    else if (xhr.status === 500) {
                                        (window.AppYacht?.log || console.log)('Error 500 detectado, respuesta completa:', responseText);
                                        errorMessage = 'Error interno del servidor al desconectar la cuenta';
                                    }
                                    // Intentar extraer mensaje de error del HTML
                                    else if (responseText.includes('<p>')) {
                                        const errorStart = responseText.indexOf('<p>') + 3;
                                        const errorEnd = responseText.indexOf('</p>', errorStart);
                                        if (errorEnd > errorStart) {
                                            errorMessage = responseText.substring(errorStart, errorEnd);
                                        }
                                    }
                                }
                            } catch (e) {
                                (window.AppYacht?.error || console.error)('Error al procesar respuesta:', e);
                            }
                            
                            // Mostrar mensaje de error más descriptivo
                            alert('No se pudo desconectar la cuenta: ' + errorMessage + '. Por favor, inténtalo de nuevo más tarde.');
                            // Restaurar el botón
                            $button.text(originalText).prop('disabled', false);
                        }
                    });
                }
            } else {
                // Acción de conexión (comportamiento original)
                let authUrl = $(this).attr('href');
                if (!authUrl) {
                    alert('Error: No se encontró la URL de autenticación de Outlook.');
                    return;
                }
                // Guardar el contenido del formulario antes de redirigir
                saveFormContent();
                window.location.href = authUrl;
            }
        });

        // Enviar correo al hacer clic en #outlook-send-mail
            $('#outlook-send-mail').on('click', function(e) {
                e.preventDefault();

                // (window.AppYacht?.log || console.log)('Send button clicked. pbOutlookData:', pbOutlookData); // DEBUG REMOVED

                // Basic check if pbOutlookData exists
                if (typeof pbOutlookData === 'undefined' || !pbOutlookData.nonce || !pbOutlookData.ajaxurl) {
                    alert('Error: Missing critical data for sending email. Please reload.');
                    (window.AppYacht?.error || console.error)('pbOutlookData is missing or incomplete:', pbOutlookData);
                    return; 
                }

                const data = {
                    action:   'pb_outlook_send_mail',
                    nonce:    pbOutlookData.nonce,
                to:       $('#correo-destino').val(),
                cc:       $('#correo-cc').val(),
                bcc:      $('#correo-bcc').val(),
                subject:  $('#asunto').val(),
                body:     $('#contenido').html() // Enviar HTML
            };

            $.post(pbOutlookData.ajaxurl, data)
            .done(function(response){
                if (response.success){
                    alert('Correo enviado: ' + response.data);
                } else {
                    (window.AppYacht?.error || console.error)('Error en la respuesta del servidor:', response.data);
                    alert('Error al enviar el correo: ' + response.data);
                }
            })
            .fail(function(jqXHR, textStatus, errorThrown){
                // Log genérico
                (window.AppYacht?.error || console.error)('Error AJAX al enviar correo:', textStatus, errorThrown, jqXHR.responseText); 
                // Alerta simple
                alert('Error en la conexión con el servidor al enviar el correo.'); 
            });
        });
    });
})(jQuery);
