/**
 * FILE shared/js/storage.js
 * Local storage utilities for the app_yacht application
 */

/**
 * Saves form data to localStorage.
 * @param {string} formId - Unique identifier of the form.
 * @param {Object} config - Configuration for fields to save.
 * @param {string[]} config.fields - Array of field IDs to save.
 * @param {Object} config.selectors - Custom selectors for fields.
 * @param {Function|null} config.beforeSave - Function to execute before saving.
 * @param {Function|null} config.afterSave - Function to execute after saving.
 * @param {Array} config.dynamicGroups - Definitions for dynamic field groups.
 * @param {string} config.prefix - Prefix for storage key.
 * @param {boolean} config.saveCheckbox - Whether to save checkbox states.
 * @returns {Object} - Saved data object.
 */
function saveFormData(formId, config = {}) {
    const defaults = {
        fields: [],
        selectors: {},
        dynamicGroups: [],
        prefix: 'pb_',
        beforeSave: null,
        afterSave: null,
        saveCheckbox: false
    };
    
    const options = { ...defaults, ...config };
    
    if (typeof options.beforeSave === 'function') {
        options.beforeSave(formId, options);
    }
    
    const data = {};
    
    options.fields.forEach(fieldId => {
        const selector = options.selectors[fieldId] || `#${fieldId}`;
        const element = document.querySelector(selector);
        
        if (element) {
            if (options.saveCheckbox && element.type === 'checkbox') {
                data[fieldId] = element.checked;
            } else if (element.tagName === 'INPUT' || element.tagName === 'TEXTAREA' || element.tagName === 'SELECT') {
                data[fieldId] = element.value;
            } else {
                data[fieldId] = element.innerHTML;
            }
        }
    });

    if (Array.isArray(options.dynamicGroups) && options.dynamicGroups.length > 0) {
        options.dynamicGroups.forEach(groupConfig => {
            if (!groupConfig.groupSelector || !groupConfig.key || !Array.isArray(groupConfig.fields)) {
                return;
            }

            data[groupConfig.key] = [];
            const groupElements = document.querySelectorAll(groupConfig.groupSelector);

            groupElements.forEach(groupElement => {
                const groupData = {};
                let hasData = false;
                groupConfig.fields.forEach(fieldName => {
                    const inputElement = groupElement.querySelector(`[name="${fieldName}"]`);
                    if (inputElement) {
                        if (options.saveCheckbox && inputElement.type === 'checkbox') {
                            groupData[fieldName] = inputElement.checked;
                            hasData = true;
                        } else if (inputElement.value !== undefined) {
                            groupData[fieldName] = inputElement.value;
                            if (inputElement.value !== '') hasData = true;
                        }
                    }
                });
                if (hasData) {
                    data[groupConfig.key].push(groupData);
                }
            });
        });
    }
    
    const storageKey = `${options.prefix}${formId}`;
    localStorage.setItem(storageKey, JSON.stringify(data));
    
    if (typeof options.afterSave === 'function') {
        options.afterSave(formId, data, options);
    }
    
    return data;
}

/**
 * Restores form data from localStorage.
 * @param {string} formId - Unique identifier of the form.
 * @param {Object} config - Configuration for fields to restore.
 * @param {string[]} config.fields - Array of field IDs to restore.
 * @param {Object} config.selectors - Custom selectors for fields.
 * @param {Function|null} config.beforeRestore - Function to execute before restoring.
 * @param {Function|null} config.afterRestore - Function to execute after restoring.
 * @param {Array} config.dynamicGroups - Definitions for dynamic field groups.
 * @param {string} config.prefix - Prefix for storage key.
 * @param {boolean} config.restoreCheckbox - Whether to restore checkbox states.
 * @param {Function|null} config.dynamicGroupFactory - Callback to recreate dynamic groups.
 * @returns {Object|null} - Restored data object or null if no data exists.
 */
function restoreFormData(formId, config = {}) {
    const defaults = {
        fields: [],
        selectors: {},
        dynamicGroups: [],
        prefix: 'pb_',
        beforeRestore: null,
        afterRestore: null,
        restoreCheckbox: false,
        dynamicGroupFactory: null
    };
    
    const options = { ...defaults, ...config };
    
    if (typeof options.beforeRestore === 'function') {
        options.beforeRestore(formId, options);
    }
    
    const storageKey = `${options.prefix}${formId}`;
    const savedDataJson = localStorage.getItem(storageKey);
    
    if (!savedDataJson) {
        return null;
    }
    
    const savedData = JSON.parse(savedDataJson);
    
    options.fields.forEach(fieldId => {
        if (savedData[fieldId] === undefined) return;
        
        const selector = options.selectors[fieldId] || `#${fieldId}`;
        const element = document.querySelector(selector);
        
        if (element) {
            if (options.restoreCheckbox && element.type === 'checkbox') {
                element.checked = savedData[fieldId];
            } else if (element.tagName === 'INPUT' || element.tagName === 'TEXTAREA' || element.tagName === 'SELECT') {
                element.value = savedData[fieldId];
            } else {
                element.innerHTML = savedData[fieldId];
            }
        }
    });

    const dynamicDataRestored = {};
    if (Array.isArray(options.dynamicGroups) && options.dynamicGroups.length > 0) {
        options.dynamicGroups.forEach(groupConfig => {
            if (groupConfig.key && Array.isArray(savedData[groupConfig.key])) {
                dynamicDataRestored[groupConfig.key] = savedData[groupConfig.key];
            }
        });
    }
    
    if (typeof options.afterRestore === 'function') {
        options.afterRestore(formId, savedData, dynamicDataRestored, options);
    }
    
    return savedData;
}

/**
 * Clears form data from localStorage.
 * @param {string} formId - Unique identifier of the form.
 * @param {Object} config - Optional configuration.
 * @param {string} config.prefix - Prefix for storage key.
 * @returns {boolean} - True if data was cleared, false if no data existed.
 */
function clearFormData(formId, config = {}) {
    const prefix = config.prefix || 'pb_';
    const storageKey = `${prefix}${formId}`;
    
    if (localStorage.getItem(storageKey)) {
        localStorage.removeItem(storageKey);
        return true;
    }
    
    return false;
}

// Export functions for module use
if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        saveFormData,
        restoreFormData,
        clearFormData
    };
}

// Make functions globally available for browser use
if (typeof window !== 'undefined') {
    window.saveFormData = saveFormData;
    window.restoreFormData = restoreFormData;
    window.clearFormData = clearFormData;
}