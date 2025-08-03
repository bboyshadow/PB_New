// ARCHIVO shared/js/utils/dom.js

/**
 * Utilidades para manipulación optimizada del DOM
 * Estas funciones ayudan a reducir reflows y repaints
 * mejorando el rendimiento de operaciones DOM intensivas
 */

/**
 * Crea y manipula elementos en un fragmento de documento antes de insertarlos en el DOM
 * Esto reduce significativamente los reflows y repaints
 * 
 * @param {Function} callback - Función que recibe el fragmento y realiza operaciones
 * @param {HTMLElement} container - Elemento donde se insertará el fragmento
 * @param {string} position - Posición de inserción ('append', 'prepend', 'replace')
 * @returns {DocumentFragment} - El fragmento creado
 */
function createWithFragment(callback, container, position = 'append') {
    // Crear un fragmento de documento
    const fragment = document.createDocumentFragment();
    
    // Ejecutar el callback pasando el fragmento
    callback(fragment);
    
    // Insertar el fragmento en el contenedor según la posición especificada
    if (position === 'append') {
        container.appendChild(fragment);
    } else if (position === 'prepend') {
        container.insertBefore(fragment, container.firstChild);
    } else if (position === 'replace') {
        container.innerHTML = '';
        container.appendChild(fragment);
    }
    
    return fragment;
}

/**
 * Crea múltiples elementos con la misma estructura de forma optimizada
 * 
 * @param {Array} items - Array de datos para crear los elementos
 * @param {Function} templateFn - Función que recibe un item y devuelve un elemento
 * @param {HTMLElement} container - Elemento donde se insertarán los elementos
 * @param {string} position - Posición de inserción ('append', 'prepend', 'replace')
 */
function createBatch(items, templateFn, container, position = 'append') {
    createWithFragment(fragment => {
        items.forEach(item => {
            const element = templateFn(item);
            fragment.appendChild(element);
        });
    }, container, position);
}

/**
 * Aplica cambios de estilo en lote para minimizar reflows
 * 
 * @param {HTMLElement} element - Elemento al que aplicar los estilos
 * @param {Object} styles - Objeto con los estilos a aplicar
 */
function batchStyles(element, styles) {
    // Guardar los estilos actuales para poder restaurarlos si es necesario
    const originalStyles = {};
    
    // Aplicar todos los estilos de una vez
    for (const property in styles) {
        if (styles.hasOwnProperty(property)) {
            originalStyles[property] = element.style[property];
            element.style[property] = styles[property];
        }
    }
    
    return originalStyles;
}

/**
 * Realiza mediciones de elementos sin causar reflows adicionales
 * 
 * @param {HTMLElement} element - Elemento a medir
 * @returns {Object} - Objeto con las dimensiones y posición
 */
function measureElement(element) {
    // Obtener todas las medidas de una vez para evitar múltiples reflows
    const rect = element.getBoundingClientRect();
    const computedStyle = window.getComputedStyle(element);
    
    return {
        width: rect.width,
        height: rect.height,
        top: rect.top + window.scrollY,
        left: rect.left + window.scrollX,
        marginTop: parseInt(computedStyle.marginTop, 10),
        marginRight: parseInt(computedStyle.marginRight, 10),
        marginBottom: parseInt(computedStyle.marginBottom, 10),
        marginLeft: parseInt(computedStyle.marginLeft, 10),
        paddingTop: parseInt(computedStyle.paddingTop, 10),
        paddingRight: parseInt(computedStyle.paddingRight, 10),
        paddingBottom: parseInt(computedStyle.paddingBottom, 10),
        paddingLeft: parseInt(computedStyle.paddingLeft, 10)
    };
}

/**
 * Crea un elemento con atributos y contenido en una sola operación
 * 
 * @param {string} tag - Etiqueta HTML del elemento
 * @param {Object} attributes - Atributos a aplicar al elemento
 * @param {string|HTMLElement|Array} content - Contenido a insertar en el elemento
 * @returns {HTMLElement} - El elemento creado
 */
function createElement(tag, attributes = {}, content = null) {
    const element = document.createElement(tag);
    
    // Aplicar atributos
    for (const attr in attributes) {
        if (attributes.hasOwnProperty(attr)) {
            if (attr === 'style' && typeof attributes[attr] === 'object') {
                batchStyles(element, attributes[attr]);
            } else if (attr === 'classList' && Array.isArray(attributes[attr])) {
                element.classList.add(...attributes[attr]);
            } else if (attr === 'dataset' && typeof attributes[attr] === 'object') {
                for (const dataAttr in attributes[attr]) {
                    element.dataset[dataAttr] = attributes[attr][dataAttr];
                }
            } else {
                element[attr] = attributes[attr];
            }
        }
    }
    
    // Insertar contenido
    if (content !== null) {
        if (typeof content === 'string') {
            element.innerHTML = content;
        } else if (content instanceof HTMLElement) {
            element.appendChild(content);
        } else if (Array.isArray(content)) {
            content.forEach(item => {
                if (typeof item === 'string') {
                    element.innerHTML += item;
                } else if (item instanceof HTMLElement) {
                    element.appendChild(item);
                }
            });
        }
    }
    
    return element;
}

// Exportar funciones para uso en módulos ES6
if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        createWithFragment,
        createBatch,
        batchStyles,
        measureElement,
        createElement
    };
}

// Hacer disponible globalmente para su uso en navegadores
if (typeof window !== 'undefined') {
    window.pbDom = {
        createWithFragment,
        createBatch,
        batchStyles,
        measureElement,
        createElement
    };
}

/**
 * Ejemplo de uso:
 * 
 * // Crear múltiples elementos de forma optimizada
 * const items = [
 *     { id: 1, name: 'Item 1' },
 *     { id: 2, name: 'Item 2' },
 *     { id: 3, name: 'Item 3' }
 * ];
 * 
 * createBatch(items, item => {
 *     return createElement('div', {
 *         className: 'item',
 *         dataset: { id: item.id }
 *     }, `<span>${item.name}</span>`);
 * }, document.getElementById('container'));
 * 
 * // Aplicar múltiples estilos de una vez
 * batchStyles(element, {
 *     width: '200px',
 *     height: '100px',
 *     backgroundColor: '#f0f0f0',
 *     border: '1px solid #ccc',
 *     borderRadius: '4px'
 * });
 */