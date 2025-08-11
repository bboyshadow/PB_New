/**
 * Yacht Info Module JavaScript
 * Maneja la funcionalidad del botón "Get Info" para extraer información de yates
 */

// Función principal que se ejecuta cuando el DOM está listo
jQuery(document).ready(function($) {
    let isFetching = false;
    let cooldownTimer = null;

    function startCooldown($button, seconds) {
        if (cooldownTimer) {
            clearInterval(cooldownTimer);
            cooldownTimer = null;
        }
        seconds = parseInt(seconds, 10);
        if (isNaN(seconds) || seconds < 0) seconds = 0;
        const originalText = $button.data('original-text') || $button.text();
        $button.data('original-text', originalText);
        $button.prop('disabled', true);
        const updateText = () => {
            const mm = String(Math.floor(seconds / 60)).padStart(2, '0');
            const ss = String(seconds % 60).padStart(2, '0');
            const tryAgainIn = (typeof yachtinfo_ajax !== 'undefined' && yachtinfo_ajax.strings && yachtinfo_ajax.strings.try_again_in) ? yachtinfo_ajax.strings.try_again_in : 'Try again in';
            $button.html(`<i class="fa fa-hourglass-half"></i> ${tryAgainIn} ${mm}:${ss}`);
            if (seconds <= 0) {
                clearInterval(cooldownTimer);
                cooldownTimer = null;
                $button.prop('disabled', false).html(originalText);
            }
            seconds -= 1;
        };
        updateText();
        cooldownTimer = setInterval(updateText, 1000);
    }

    // Manejar el clic del botón "Get Info"

    // Para evitar múltiples bindings cuando el script se carga más de una vez,
    // se elimina cualquier handler previo con el namespace ".yachtinfo" y se vuelve a registrar.
    $('#get-yacht-info')
        .off('click')
        .on('click.yachtinfo', function(e) {
            e.preventDefault();
            if (isFetching) {
                return;
            }
            isFetching = true;
            // Limpiar mensajes de error anteriores
        $('.yacht-error-message').remove();
        
        var button = $(this);
        var urlInput = $('#yacht-url');
        var yachtUrl = urlInput.val().trim();
        var container = $('#yacht-info-container');
        
        // Validate that a URL has been entered
        if (!yachtUrl) {
            var enterUrlStr = (typeof yachtinfo_ajax !== 'undefined' && yachtinfo_ajax.strings && yachtinfo_ajax.strings.enter_yacht_url) ? yachtinfo_ajax.strings.enter_yacht_url : 'Please enter a yacht URL.';
            showError(urlInput, enterUrlStr);
            isFetching = false;
            return;
        }
        
        // Validate URL format and allowed domains
        try {
            new URL(yachtUrl); // Verificar si es una URL válida en formato
            
            if (!isValidUrl(yachtUrl)) {
                // Extraer el dominio de la URL para mostrarlo en el mensaje de error
                var domain = yachtUrl.replace(/(https?:\/\/)?(www\.)?/i, "").split('/')[0].split(':')[0];
                
                var supported = (typeof yachtinfo_ajax !== 'undefined' && Array.isArray(yachtinfo_ajax.allowed_domains))
                    ? yachtinfo_ajax.allowed_domains.join(', ')
                    : 'cyaeb.com';

                var dnTitle = (yachtinfo_ajax?.strings?.domain_not_supported) || 'Domain not supported';
                var dnMsgTpl = (yachtinfo_ajax?.strings?.domain_error_msg) || '"%s" is not in our list of supported websites.';
                var dnUse = (yachtinfo_ajax?.strings?.use_supported_domain) || 'Please use a URL from the supported yacht charter website(s):';
                var dnMsg = dnMsgTpl.replace('%s', domain);
                
                showError(
                    urlInput, 
                    '<strong>' + dnTitle + ':</strong> ' + dnMsg + '<br>' + dnUse + '<br><span class="supported-domains">' + supported + '</span>'
                );
                isFetching = false;
                return;
            }
        } catch (_) {
            var invalidUrlStr = (yachtinfo_ajax?.strings?.invalid_url) || 'Please enter a valid URL (e.g., https://www.charterworld.com/yacht/...)';
            showError(urlInput, invalidUrlStr);
            isFetching = false;
            return;
        }
        
        // Deshabilitar el botón y mostrar estado de carga
        button.prop('disabled', true);
        var originalText = button.text();
        var loadingStr = (yachtinfo_ajax?.strings?.loading) || 'Loading...';
        button.html('<i class="fa fa-spinner fa-spin"></i> ' + loadingStr);
        button.data('original-text', originalText);
        
        // Limpiar contenedor de información anterior
        container.empty();
        
        // Realizar la petición AJAX
        $.ajax({
            url: yachtinfo_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'extract_yacht_info',
                yachtUrl: yachtUrl,
                nonce: yachtinfo_ajax.nonce,
                force_refresh: $('#force-refresh').is(':checked') ? 1 : 0
            },
            success: function(response) {
                if (response.success && response.data && response.data.html) {
                    // Mostrar el contenedor con la información del yate
                    container.html(response.data.html).show();
                    // Guardar datos del yate en una variable global para otros módulos (p. ej., calculadora de relocation)
                    window.yachtInfoData = response.data.data || {};
                    // Si la función de precarga de relocation está disponible, ejecutarla para actualizar campos automáticamente
                    if (typeof prefillRelocationFields === 'function') {
                        prefillRelocationFields();
                    }
                    
                    // Scroll suave hacia el contenedor
                    $('html, body').animate({
                        scrollTop: container.offset().top - 20
                    }, 500);
                    
                    // Activar pestañas si existen
                    if ($('.yacht-info-tabs').length) {
                        $('.yacht-info-tabs a').on('click', function(e) {
                            e.preventDefault();
                            $(this).tab('show');
                        });
                        // Activar primera pestaña
                        $('.yacht-info-tabs a:first').tab('show');
                    }
                } else {
                    // Soporte para caso de rate limit con 200 OK
                    if (response && response.data && response.data.code === 'rate_limit_exceeded') {
                        var seconds = parseInt(response.data.retry_after || 0, 10);
                        if (!seconds || seconds < 0) seconds = 60;
                        var rateLimitMsg = (yachtinfo_ajax?.strings?.rate_limit_msg) || 'You have reached the request limit. Please wait before trying again.';
                        container.html('<div class="alert alert-warning"><i class="fa fa-hourglass-half"></i> ' + rateLimitMsg + '</div>').show();
                        startCooldown($('#get-yacht-info'), seconds);
                        return;
                    }

                    var errorMsg = '';
                    if (response.data && typeof response.data === 'object' && response.data.message) {
                        errorMsg = response.data.message;
                    } else if (typeof response.data === 'string') {
                        errorMsg = response.data;
                    } else {
                        errorMsg = (yachtinfo_ajax?.strings?.unknown_error) || 'Unknown error extracting information.';
                    }
                    container.html('<div class="alert alert-danger"><i class="fa fa-exclamation-triangle"></i> Error: ' + errorMsg + '</div>').show();
                }
            },
            error: function(xhr, status, error) {
                (window.AppYacht?.error || console.error)('AJAX Error:', error);
                // Manejo de rate limiting (HTTP 429)
                var statusCode = xhr && xhr.status ? xhr.status : 0;
                if (statusCode === 429) {
                    var retryAfterHeader = xhr.getResponseHeader && xhr.getResponseHeader('Retry-After');
                    var seconds = parseInt(retryAfterHeader || 0, 10);
                    if (isNaN(seconds) || seconds <= 0) {
                        // Intentar obtener de cuerpo JSON si existe
                        try {
                            var resp = xhr.responseJSON || JSON.parse(xhr.responseText);
                            if (resp && resp.data && typeof resp.data.retry_after !== 'undefined') {
                                seconds = parseInt(resp.data.retry_after, 10);
                            }
                        } catch (e) {}
                    }
                    if (!seconds || seconds < 0) seconds = 60; // fallback razonable

                    var rateLimitMsg2 = (yachtinfo_ajax?.strings?.rate_limit_msg) || 'You have reached the request limit. Please wait before trying again.';
                    container.html('<div class="alert alert-warning"><i class="fa fa-hourglass-half"></i> ' + rateLimitMsg2 + '</div>').show();
                    startCooldown(button, seconds);
                } else {
                    var connErr = (yachtinfo_ajax?.strings?.connection_error) || 'Connection error. Please try again.';
                    container.html('<div class="alert alert-danger"><i class="fa fa-exclamation-triangle"></i> ' + connErr + '</div>').show();
                }
            },
            complete: function() {
                // Restaurar el botón a su estado original SOLO si no hay cooldown activo
                if (!cooldownTimer) {
                    button.prop('disabled', false).html(originalText);
                }
                isFetching = false;
            }
        });
    });
    
    // Función para mostrar mensajes de error
    function showError(element, message) {
        // Eliminar mensajes de error anteriores
        $('.yacht-error-message').remove();
        
        // Crear y mostrar nuevo mensaje de error
        var $error = $('<div class="yacht-error-message alert alert-danger mt-3"><i class="fa fa-exclamation-circle"></i> ' + message + '</div>');
        element.after($error);
        
        // Añadir clase de error al campo de entrada
        element.addClass('is-invalid').focus();
        
        // Desplazarse al mensaje de error
        $('html, body').animate({
            scrollTop: $error.offset().top - 80
        }, 300);
        
        // Eliminar la clase de error cuando el usuario comience a escribir
        element.one('input', function() {
            $(this).removeClass('is-invalid');
        });
    }
    
    // Permitir envío con Enter en el campo de URL
    $('#yacht-url').on('keypress', function(e) {
        if (e.which === 13) { // Enter key
            $('#get-yacht-info').click();
        }
    });
});

/**
 * Valida si una cadena es una URL válida y pertenece a un dominio permitido
 * @param {string} string - La cadena a validar
 * @returns {boolean} - True si es una URL válida
 */
function isValidUrl(string) {
    try {
        // Verificar si es una URL válida
        const url = new URL(string);
        
        // Obtener dominios permitidos desde la configuración del backend (pasada via wp_localize_script)
        const allowedDomains = (typeof yachtinfo_ajax !== 'undefined' && yachtinfo_ajax.allowed_domains) 
            ? yachtinfo_ajax.allowed_domains 
            : ['cyaeb.com']; // fallback por seguridad
        
        // Extraer dominio de la URL y verificar si está permitido
        const hostname = url.hostname.toLowerCase();
        return allowedDomains.some(domain => hostname === domain || hostname.endsWith('.' + domain));
    } catch (_) {
        return false;
    }
}