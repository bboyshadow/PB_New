// ARCHIVO shared/js/utils/debounce.js

/**
 * Utilidades para optimizar el rendimiento de eventos frecuentes
 * Estas funciones ayudan a limitar la frecuencia de ejecución de callbacks
 * para eventos como resize, scroll, input, etc.
 */

/**
 * Implementa la técnica de debounce para limitar la frecuencia de ejecución de una función
 * La función se ejecutará después de que haya pasado un tiempo desde la última invocación
 *
 * @param {Function} func - Función a ejecutar
 * @param {number} wait - Tiempo de espera en milisegundos
 * @param {boolean} immediate - Si es true, ejecuta la función al inicio en lugar de al final
 * @returns {Function} - Función con debounce aplicado
 */
function debounce(func, wait, immediate = false) {
	let timeout;

	return function executedFunction(...args) {
		const context = this;

		// Función a ejecutar cuando pase el tiempo de espera
		const later = function() {
			timeout = null;
			if ( ! immediate) {
				func.apply( context, args );
			}
		};

		// Si immediate es true y no estamos en timeout, ejecutar ahora
		const callNow = immediate && ! timeout;

		// Reiniciar el temporizador
		clearTimeout( timeout );
		timeout = setTimeout( later, wait );

		// Si es immediate, ejecutar ahora
		if (callNow) {
			func.apply( context, args );
		}
	};
}

/**
 * Implementa la técnica de throttle para limitar la frecuencia de ejecución de una función
 * La función se ejecutará como máximo una vez cada periodo de tiempo especificado
 *
 * @param {Function} func - Función a ejecutar
 * @param {number} limit - Tiempo mínimo entre ejecuciones en milisegundos
 * @returns {Function} - Función con throttle aplicado
 */
function throttle(func, limit) {
	let inThrottle;
	let lastFunc;
	let lastRan;

	return function executedFunction(...args) {
		const context = this;

		if ( ! inThrottle) {
			func.apply( context, args );
			lastRan    = Date.now();
			inThrottle = true;
		} else {
			clearTimeout( lastFunc );

			lastFunc = setTimeout(
				function() {
					if (Date.now() - lastRan >= limit) {
						func.apply( context, args );
						lastRan = Date.now();
					}
				},
				limit - (Date.now() - lastRan)
			);
		}
	};
}

/**
 * Implementa la técnica de requestAnimationFrame throttle
 * Optimiza funciones que modifican el DOM para ejecutarse en sincronía con el refresco de pantalla
 *
 * @param {Function} func - Función a ejecutar
 * @returns {Function} - Función optimizada con rAF
 */
function rafThrottle(func) {
	let ticking = false;

	return function executedFunction(...args) {
		const context = this;

		if ( ! ticking) {
			window.requestAnimationFrame(
				function() {
					func.apply( context, args );
					ticking = false;
				}
			);

			ticking = true;
		}
	};
}

// Exportar funciones para uso en módulos ES6
if (typeof module !== 'undefined' && module.exports) {
	module.exports = {
		debounce,
		throttle,
		rafThrottle
	};
}

// Hacer disponible globalmente para su uso en navegadores
if (typeof window !== 'undefined') {
	window.pbDebounce    = debounce;
	window.pbThrottle    = throttle;
	window.pbRafThrottle = rafThrottle;
}

/**
 * Ejemplo de uso:
 *
 * // Debounce para evento de resize
 * window.addEventListener('resize', debounce(function() {
 *     console.log('Ventana redimensionada');
 *     updateLayout();
 * }, 250));
 *
 * // Throttle para evento de scroll
 * window.addEventListener('scroll', throttle(function() {
 *     console.log('Scroll detectado');
 *     updateScrollIndicator();
 * }, 100));
 *
 * // RAF Throttle para animaciones
 * window.addEventListener('mousemove', rafThrottle(function(e) {
 *     updateCursorEffect(e.clientX, e.clientY);
 * }));
 */
