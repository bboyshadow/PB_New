// ARCHIVO shared/js/events.js

/**
 * Sistema de eventos centralizado para la comunicación entre módulos
 * Implementa el patrón Observer para desacoplar componentes
 */
class EventBus {
    constructor() {
        this.events = {};
        (window.AppYacht?.log || console.log)('EventBus inicializado');
    }
    
    /**
     * Suscribe una función callback a un evento específico
     * @param {string} event - Nombre del evento
     * @param {Function} callback - Función a ejecutar cuando se publique el evento
     * @returns {Function} - Función para cancelar la suscripción
     */
    subscribe(event, callback) {
        if (!this.events[event]) this.events[event] = [];
        this.events[event].push(callback);
        (window.AppYacht?.log || console.log)(`Suscripción a evento '${event}' registrada`);
        
        // Retornar función para cancelar la suscripción
        return () => this.unsubscribe(event, callback);
    }
    
    /**
     * Cancela la suscripción de una función callback a un evento
     * @param {string} event - Nombre del evento
     * @param {Function} callback - Función a eliminar de la lista de suscriptores
     */
    unsubscribe(event, callback) {
        if (this.events[event]) {
            this.events[event] = this.events[event].filter(cb => cb !== callback);
            (window.AppYacht?.log || console.log)(`Suscripción a evento '${event}' cancelada`);
        }
    }
    
    /**
     * Publica un evento con datos opcionales
     * @param {string} event - Nombre del evento a publicar
     * @param {*} data - Datos a pasar a los suscriptores
     */
    publish(event, data) {
        if (this.events[event]) {
            (window.AppYacht?.log || console.log)(`Publicando evento '${event}' con datos:`, data);
            this.events[event].forEach(callback => {
                try {
                    callback(data);
                } catch (error) {
                    (window.AppYacht?.error || console.error)(`Error en callback de evento '${event}':`, error);
                }
            });
        } else {
            (window.AppYacht?.warn || console.warn)(`Evento '${event}' publicado sin suscriptores`);
        }
    }
    
    /**
     * Elimina todos los suscriptores de un evento específico
     * @param {string} event - Nombre del evento a limpiar
     */
    clear(event) {
        if (event) {
            delete this.events[event];
            (window.AppYacht?.log || console.log)(`Todos los suscriptores del evento '${event}' eliminados`);
        } else {
            this.events = {};
            (window.AppYacht?.log || console.log)('Todos los eventos y suscriptores eliminados');
        }
    }
}

// Crear una instancia única (singleton) del EventBus
const eventBus = new EventBus();

// Hacer disponible globalmente para su uso en navegadores
if (typeof window !== 'undefined') {
    window.eventBus = eventBus;
}

// Exportar para uso en módulos ES6
if (typeof module !== 'undefined' && module.exports) {
    module.exports = eventBus;
}

/**
 * Ejemplo de uso:
 * 
 * // Suscribirse a un evento
 * const unsubscribe = eventBus.subscribe('calculationComplete', (result) => {
 *     console.log('Cálculo completado:', result);
 *     updateUI(result);
 * });
 * 
 * // Publicar un evento
 * eventBus.publish('calculationComplete', { total: 1500, currency: 'EUR' });
 * 
 * // Cancelar suscripción cuando ya no sea necesaria
 * unsubscribe();
 */