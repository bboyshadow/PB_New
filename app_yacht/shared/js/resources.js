// ARCHIVO shared/js/resources.js

// Funciones loadGoogleFonts y loadWebFontAPI eliminadas ya que no se usan.

/**
 * Función para cargar un script externo
 * 
 * @param {string} url - URL del script a cargar
 * @param {Object} config - Configuración adicional
 * @param {Function|null} config.onLoad - Función que se ejecuta cuando el script se carga correctamente
 * @param {Function|null} config.onError - Función que se ejecuta si hay un error al cargar el script
 * @param {boolean} config.async - Si el script debe cargarse de forma asíncrona
 * @param {boolean} config.defer - Si el script debe cargarse de forma diferida
 * @returns {HTMLScriptElement|null} - El elemento script creado o null si hay un error
 */
function loadScript(url, config = {}) {
    // Valores por defecto
    const defaults = {
        onLoad: null,
        onError: null,
        async: true,
        defer: false
    };
    
    // Combinar configuración con valores por defecto
    const options = { ...defaults, ...config };
    
    // Validar URL
    if (!url || url.trim() === '') {
        console.error('No se especificó URL para el script');
        if (options.onError) options.onError(new Error('URL no válida'));
        return null;
    }
    
    try {
        // Verificar si el script ya está cargado
        const existingScript = document.querySelector(`script[src="${url}"]`);
        if (existingScript) {
            console.log('El script ya está cargado:', url);
            if (options.onLoad) options.onLoad(existingScript);
            return existingScript;
        }
        
        const script = document.createElement('script');
        script.src = url;
        script.async = options.async;
        script.defer = options.defer;
        
        // Manejar eventos de carga y error
        if (options.onLoad) {
            script.onload = function() {
                console.log('Script cargado correctamente:', url);
                options.onLoad(script);
            };
        }
        
        if (options.onError) {
            script.onerror = function() {
                console.error('Error al cargar script:', url);
                options.onError(new Error(`Error al cargar script: ${url}`));
            };
        }
        
        document.head.appendChild(script);
        console.log('Script añadido al documento:', url);
        
        return script;
    } catch (error) {
        console.error('Error al crear el elemento script:', error);
        if (options.onError) options.onError(error);
        return null;
    }
}

/**
 * Función para cargar una hoja de estilos CSS externa
 * 
 * @param {string} url - URL de la hoja de estilos a cargar
 * @param {Object} config - Configuración adicional
 * @param {Function|null} config.onLoad - Función que se ejecuta cuando la hoja de estilos se carga correctamente
 * @param {Function|null} config.onError - Función que se ejecuta si hay un error al cargar la hoja de estilos
 * @returns {HTMLLinkElement|null} - El elemento link creado o null si hay un error
 */
function loadStylesheet(url, config = {}) {
    // Valores por defecto
    const defaults = {
        onLoad: null,
        onError: null
    };
    
    // Combinar configuración con valores por defecto
    const options = { ...defaults, ...config };
    
    // Validar URL
    if (!url || url.trim() === '') {
        console.error('No se especificó URL para la hoja de estilos');
        if (options.onError) options.onError(new Error('URL no válida'));
        return null;
    }
    
    try {
        // Verificar si la hoja de estilos ya está cargada
        const existingLink = document.querySelector(`link[href="${url}"]`);
        if (existingLink) {
            console.log('La hoja de estilos ya está cargada:', url);
            if (options.onLoad) options.onLoad(existingLink);
            return existingLink;
        }
        
        const link = document.createElement('link');
        link.rel = 'stylesheet';
        link.href = url;
        
        // Manejar eventos de carga y error
        if (options.onLoad) {
            link.onload = function() {
                console.log('Hoja de estilos cargada correctamente:', url);
                options.onLoad(link);
            };
        }
        
        if (options.onError) {
            link.onerror = function() {
                console.error('Error al cargar hoja de estilos:', url);
                options.onError(new Error(`Error al cargar hoja de estilos: ${url}`));
            };
        }
        
        document.head.appendChild(link);
        console.log('Hoja de estilos añadida al documento:', url);
        
        return link;
    } catch (error) {
        console.error('Error al crear el elemento link:', error);
        if (options.onError) options.onError(error);
        return null;
    }
}

// Exportar las funciones para que estén disponibles en otros archivos
if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        loadScript,
        loadStylesheet
    };
}

// Asegurar que las funciones estén disponibles globalmente para su uso en navegadores
if (typeof window !== 'undefined') {
    window.loadScript = loadScript;
    window.loadStylesheet = loadStylesheet;
}
