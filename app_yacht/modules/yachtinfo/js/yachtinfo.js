/**
 * Yacht Info Module JavaScript
 * Maneja la funcionalidad del botón "Get Info" para extraer información de yates
 */

// Función principal que se ejecuta cuando el DOM está listo
jQuery(document).ready(function($) {
    // Manejar el clic del botón "Get Info"
    $('#get-yacht-info').on('click', function(e) {
        e.preventDefault();
        
        // Limpiar mensajes de error anteriores
        $('.yacht-error-message').remove();
        
        var button = $(this);
        var urlInput = $('#yacht-url');
        var yachtUrl = urlInput.val().trim();
        var container = $('#yacht-info-container');
        
        // Validate that a URL has been entered
        if (!yachtUrl) {
            showError(urlInput, 'Please enter a yacht URL.');
            return;
        }
        
        // Validate URL format and allowed domains
        try {
            new URL(yachtUrl); // Verificar si es una URL válida en formato
            
            if (!isValidUrl(yachtUrl)) {
                // Extraer el dominio de la URL para mostrarlo en el mensaje de error
                var domain = yachtUrl.replace(/(https?:\/\/)?(www\.)?/i, "").split('/')[0].split(':')[0];
                
                showError(
                    urlInput, 
                    '<strong>Domain not supported:</strong> "' + domain + '" is not in our list of supported websites.<br>Please use a URL from the supported yacht charter website:<br><span class="supported-domains">cyaeb.com</span>'
                );
                return;
            }
        } catch (_) {
            showError(urlInput, 'Please enter a valid URL (e.g., https://www.charterworld.com/yacht/...)');
            return;
        }
        
        // Deshabilitar el botón y mostrar estado de carga
        button.prop('disabled', true);
        var originalText = button.text();
        button.html('<i class="fa fa-spinner fa-spin"></i> Loading...');
        
        // Limpiar contenedor de información anterior
        container.empty();
        
        // Realizar la petición AJAX
        $.ajax({
            url: yachtinfo_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'extract_yacht_info',
                yachtUrl: yachtUrl,
                nonce: yachtinfo_ajax.nonce
            },
            success: function(response) {
                if (response.success && response.data.html) {
                    // Mostrar el contenedor con la información del yate
                    container.html(response.data.html).show();
                    
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
                    var errorMsg = '';
                    if (response.data && typeof response.data === 'object' && response.data.message) {
                        errorMsg = response.data.message;
                    } else if (typeof response.data === 'string') {
                        errorMsg = response.data;
                    } else {
                        errorMsg = 'Unknown error extracting information.';
                    }
                    container.html('<div class="alert alert-danger"><i class="fa fa-exclamation-triangle"></i> Error: ' + errorMsg + '</div>').show();
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', error);
                container.html('<div class="alert alert-danger"><i class="fa fa-exclamation-triangle"></i> Connection error. Please try again.</div>').show();
            },
            complete: function() {
                // Restaurar el botón a su estado original
                button.prop('disabled', false).html(originalText);
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
        
        // Lista de dominios permitidos (debe coincidir con la lista en yacht-info-service.php)
        const allowedDomains = [
            'cyaeb.com'
        ];
        
        // Extraer dominio de la URL y verificar si está permitido
        const hostname = url.hostname.toLowerCase();
        return allowedDomains.some(domain => hostname === domain || hostname.endsWith('.' + domain));
    } catch (_) {
        return false;
    }
}