/**
 * FILE shared/js/ui.js
 * UI interaction utilities for the app_yacht application
 */

/**
 * Toggles the visibility of a container and optionally clears its values when hidden.
 *
 * @param {HTMLElement} container - The container to toggle.
 * @param {Function|null} clearCallback - Optional function to clear container values.
 * @param {string} [displayType='flex'] - Display type when shown ('flex' or 'block').
 * @returns {boolean} - True if the container is now visible, false if hidden.
 */
function toggleContainer(container, clearCallback = null, displayType = 'flex') {
	if ( ! container) {
		return false;
	}

	const isVisibleNow      = (container.style.display === 'flex' || container.style.display === 'block');
	container.style.display = isVisibleNow ? 'none' : displayType;

	if (isVisibleNow && clearCallback) {
		clearCallback( container );
	}

	return ! isVisibleNow;
}

/**
 * Toggles the visibility of a field by its ID.
 *
 * @param {string} fieldId - The ID of the field to toggle.
 * @param {Function|null} callback - Optional function to execute after toggling.
 * @returns {boolean} - True if the field is now visible, false if hidden.
 */
function toggleField(fieldId, callback = null) {
	const field = document.getElementById( fieldId );
	if ( ! field) {
		return false;
	}

	const displayType   = 'block';
	const isVisibleNow  = (field.style.display === 'none' || field.style.display === '');
	field.style.display = isVisibleNow ? displayType : 'none';

	if (callback) {
		callback( isVisibleNow, field );
	}

	return isVisibleNow;
}

/**
 * Adds a dynamic field to a container.
 *
 * @param {string} containerId - The ID of the container to add the field to.
 * @param {string} templateHTML - HTML for the new field.
 * @param {Function|null} afterAddCallback - Optional function to execute after adding.
 * @returns {HTMLElement|null} - The added element, or null if failed.
 */
function addDynamicField(containerId, templateHTML, afterAddCallback = null) {
	const container = document.getElementById( containerId );
	if ( ! container) {
		return null;
	}

	const tempDiv     = document.createElement( 'div' );
	tempDiv.innerHTML = templateHTML.trim();
	const newField    = tempDiv.firstChild;

	container.appendChild( newField );

	if (afterAddCallback && typeof afterAddCallback === 'function') {
		afterAddCallback( newField, container );
	}

	return newField;
}

/**
 * Removes a dynamic field.
 *
 * @param {HTMLElement} button - The button that triggered the removal.
 * @param {string} fieldSelector - CSS selector for the field to remove.
 * @param {Function|null} beforeRemoveCallback - Optional function to execute before removal.
 * @param {boolean} [keepOne=false] - If true, keeps at least one field.
 * @returns {boolean} - True if the field was removed, false otherwise.
 */
function removeDynamicField(button, fieldSelector, beforeRemoveCallback = null, keepOne = false) {
	const fieldToRemove = button.closest( fieldSelector );
	if ( ! fieldToRemove) {
		return false;
	}

	if (keepOne) {
		const container = fieldToRemove.parentElement;
		const siblings  = container.querySelectorAll( fieldSelector );
		if (siblings.length <= 1) {
			return false;
		}
	}

	if (beforeRemoveCallback && typeof beforeRemoveCallback === 'function') {
		beforeRemoveCallback( fieldToRemove );
	}

	fieldToRemove.remove();
	return true;
}

// --- UI status helpers (loading, notifications) ---
function setLoading(isLoading) {
	try {
		const btn = document.getElementById('calculateButton');
		if (!btn) return;
		if (isLoading) {
			btn.dataset.prevText = btn.dataset.prevText || btn.textContent || 'Calculate';
			btn.textContent = 'Calculating...';
			btn.disabled = true;
			btn.classList.add('loading');
		} else {
			btn.textContent = btn.dataset.prevText || 'Calculate';
			btn.disabled = false;
			btn.classList.remove('loading');
		}
	} catch (e) {
		(console && console.warn) ? console.warn('setLoading error:', e) : null;
	}
}

function showMessage(type, message) {
	const el = document.getElementById('errorMessage');
	if (el) {
		el.textContent = message || '';
		el.style.display = message ? 'block' : 'none';
		el.classList.remove('text-danger', 'text-warning', 'text-success');
		if (type === 'error') el.classList.add('text-danger');
		if (type === 'warning') el.classList.add('text-warning');
		if (type === 'success') el.classList.add('text-success');
	} else if (typeof alert !== 'undefined' && message) {
		alert(message);
	}
}

function notifyError(message) {
	showMessage('error', message || 'An unexpected error occurred');
}

function notifyWarning(message) {
	showMessage('warning', message || 'Please review the highlighted fields');
}

function notifySuccess(message) {
	showMessage('success', message || 'Operation completed successfully');
}

// Export functions for module use
if (typeof module !== 'undefined' && module.exports) {
	module.exports = {
		toggleContainer,
		toggleField,
		addDynamicField,
		removeDynamicField,
		setLoading,
		notifyError,
		notifyWarning,
		notifySuccess
	};
}

// Make functions globally available for browser use
if (typeof window !== 'undefined') {
	window.toggleContainer    = toggleContainer;
	window.toggleField        = toggleField;
	window.addDynamicField    = addDynamicField;
	window.removeDynamicField = removeDynamicField;

	// Expose AppYacht.ui helpers
	window.AppYacht = window.AppYacht || {};
	window.AppYacht.ui = window.AppYacht.ui || {};
	window.AppYacht.ui.setLoading    = setLoading;
	window.AppYacht.ui.notifyError   = notifyError;
	window.AppYacht.ui.notifyWarning = notifyWarning;
	window.AppYacht.ui.notifySuccess = notifySuccess;
}
