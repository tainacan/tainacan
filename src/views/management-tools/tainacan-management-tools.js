/**
 * Management Tools page.
 *
 * The command cards and their forms are rendered in PHP. This script sends the
 * form values to the management-tools endpoint, which starts a background process,
 * and then polls the bg-processes endpoint to display its status and progress.
 *
 * The id of the last process started by each command is kept in localStorage, so
 * the status is still displayed after the page is reloaded.
 */
'use strict';

const user = window.tainacan_user || {};
const apiUrl = (user.tainacan_api_url || '').replace(/\/$/, '');
const nonce = user.nonce || '';
const i18n = (window.tainacan_management_tools && window.tainacan_management_tools.i18n) || {};

const POLLING_INTERVAL = 3000;
const STORAGE_PREFIX = 'tainacan_management_tools_';

function onDocumentLoaded(callback) {
	if (/comp|inter|loaded/.test(document.readyState))
		callback();
	else
		document.addEventListener('DOMContentLoaded', callback, false);
}

function request(method, path, body) {
	const options = {
		method: method,
		headers: {
			'Content-Type': 'application/json',
			'X-WP-Nonce': nonce
		}
	};
	if (body !== undefined)
		options.body = JSON.stringify(body);

	return fetch(apiUrl + path, options).then(function (response) {
		return response.json().catch(function () {
			return {};
		}).then(function (data) {
			if (!response.ok) {
				const error = new Error((data && (data.message || data.error_message)) || i18n.request_failed || 'The request failed.');
				error.status = response.status;
				throw error;
			}
			return data;
		});
	});
}

/**
 * localStorage may be unavailable (private mode, blocked storage), so every access
 * is guarded: without it, the page still works, it only forgets the last process.
 *
 * @param {Function} callback Receives the localStorage object.
 * @returns {*} The callback result, or null when localStorage is unavailable.
 */
function withStorage(callback) {
	try {
		return callback(window.localStorage);
	} catch {
		return null;
	}
}

function storeProcessId(command, id) {
	withStorage(function (storage) {
		storage.setItem(STORAGE_PREFIX + command, String(id));
	});
}

function getStoredProcessId(command) {
	return withStorage(function (storage) {
		return storage.getItem(STORAGE_PREFIX + command);
	});
}

function forgetProcessId(command) {
	withStorage(function (storage) {
		storage.removeItem(STORAGE_PREFIX + command);
	});
}

/**
 * Reads the form values, converting them according to the input type.
 *
 * @param {HTMLFormElement} form
 * @returns {Object}
 */
function getFormValues(form) {
	const values = {};
	form.querySelectorAll('input[name], select[name], textarea[name]').forEach(function (input) {
		if (input.type === 'checkbox')
			values[input.name] = input.checked;
		else if (input.type === 'number')
			values[input.name] = input.value === '' ? null : parseInt(input.value, 10);
		else
			values[input.name] = input.value;
	});
	return values;
}

function hasDestructiveOptionChecked(form) {
	return Array.prototype.some.call(form.querySelectorAll('input[data-destructive="1"]'), function (input) {
		return input.checked;
	});
}

function isProcessDone(process) {
	return process && (process.done === true || process.done === 1 || process.done === '1');
}

/**
 * Controls one command card: form submission, status rendering and polling.
 *
 * @param {HTMLElement} card
 */
function ManagementToolCard(card) {
	this.card = card;
	this.command = card.dataset.command;
	this.form = card.querySelector('.tainacan-management-tool__form');
	this.button = card.querySelector('.tainacan-management-tool__run');
	this.status = card.querySelector('.tainacan-management-tool__status');
	this.badge = card.querySelector('.tainacan-management-tool__status-badge');
	this.processIdLabel = card.querySelector('.tainacan-management-tool__status-id');
	this.label = card.querySelector('.tainacan-management-tool__status-label');
	this.progress = card.querySelector('.tainacan-management-tool__progress');
	this.progressBar = this.progress.querySelector('progress');
	this.progressValue = card.querySelector('.tainacan-management-tool__progress-value');
	this.output = card.querySelector('.tainacan-management-tool__status-output');
	this.links = card.querySelector('.tainacan-management-tool__status-links');
	this.timer = null;

	this.form.addEventListener('submit', this.onSubmit.bind(this));

	const storedId = getStoredProcessId(this.command);
	if (storedId)
		this.watch(storedId);
}

ManagementToolCard.prototype.setRunning = function (running, text) {
	this.button.disabled = running;
	this.button.textContent = running ? (text || i18n.running || 'Running…') : (i18n.run || 'Run');
};

ManagementToolCard.prototype.onSubmit = function (event) {
	event.preventDefault();

	if (!this.form.reportValidity())
		return;

	if (hasDestructiveOptionChecked(this.form) && !window.confirm(i18n.confirm_destructive || 'This operation cannot be undone. Continue?'))
		return;

	this.stopPolling();
	this.setRunning(true, i18n.starting || 'Starting…');
	this.renderError(null);

	request('POST', '/management-tools/' + encodeURIComponent(this.command), getFormValues(this.form))
		.then(function (data) {
			storeProcessId(this.command, data.bg_process_id);
			this.watch(data.bg_process_id);
		}.bind(this))
		.catch(function (error) {
			this.setRunning(false);
			this.renderError(error.message);
		}.bind(this));
};

ManagementToolCard.prototype.watch = function (processId) {
	this.processId = processId;
	this.setRunning(true);
	this.poll();
};

ManagementToolCard.prototype.poll = function () {
	request('GET', '/bg-processes/' + encodeURIComponent(this.processId))
		.then(function (process) {
			this.renderError(null);
			this.renderProcess(process);
			if (isProcessDone(process))
				this.setRunning(false);
			else
				this.timer = window.setTimeout(this.poll.bind(this), POLLING_INTERVAL);
		}.bind(this))
		.catch(function (error) {
			// The process was deleted (or belongs to another user): forget it.
			if (error.status === 404) {
				forgetProcessId(this.command);
				this.status.hidden = true;
				this.setRunning(false);
				return;
			}
			this.renderError(error.message);
			this.timer = window.setTimeout(this.poll.bind(this), POLLING_INTERVAL * 2);
		}.bind(this));
};

ManagementToolCard.prototype.stopPolling = function () {
	if (this.timer)
		window.clearTimeout(this.timer);
	this.timer = null;
};

ManagementToolCard.prototype.renderProcess = function (process) {
	const statusKey = process.status || 'waiting';
	const statusLabels = i18n.status || {};

	this.status.hidden = false;
	this.status.dataset.status = statusKey;
	this.badge.textContent = statusLabels[statusKey] || statusKey;
	this.processIdLabel.textContent = (i18n.process_id || 'Process ID') + ': ' + process.ID;
	this.label.textContent = isProcessDone(process) ? '' : (process.progress_label || '');

	const progressValue = parseInt(process.progress_value, 10);
	if (!isProcessDone(process) && progressValue >= 0) {
		this.progress.hidden = false;
		this.progressBar.value = progressValue;
		this.progressValue.textContent = progressValue + '%';
	} else {
		this.progress.hidden = true;
	}

	// The output is displayed as text: it is a plain message for the management tools.
	this.output.textContent = isProcessDone(process) ? (process.output || '') : '';

	this.links.textContent = '';
	this.appendLink(process.log, i18n.see_log || 'See log');
	this.appendLink(process.error_log, i18n.see_error_log || 'See error log');
};

ManagementToolCard.prototype.appendLink = function (url, text) {
	if (!url)
		return;
	const link = document.createElement('a');
	link.href = url;
	link.target = '_blank';
	link.rel = 'noopener noreferrer';
	link.textContent = text;
	this.links.appendChild(link);
};

ManagementToolCard.prototype.renderError = function (message) {
	let error = this.card.querySelector('.tainacan-management-tool__error');
	if (!message) {
		if (error)
			error.remove();
		return;
	}
	if (!error) {
		error = document.createElement('div');
		error.className = 'notice notice-error inline tainacan-management-tool__error';
		error.setAttribute('role', 'alert');
		this.form.parentNode.insertBefore(error, this.form);
	}
	error.innerHTML = '';
	const paragraph = document.createElement('p');
	paragraph.textContent = message;
	error.appendChild(paragraph);
};

onDocumentLoaded(function () {
	if (!apiUrl)
		return;
	document.querySelectorAll('.tainacan-management-tool').forEach(function (card) {
		new ManagementToolCard(card);
	});
});
