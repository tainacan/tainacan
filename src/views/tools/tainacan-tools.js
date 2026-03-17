/**
 * Management Tools page: run commands and display status. Cards and forms are server-rendered.
 */
'use strict';

const user = window.tainacan_user || {};
const apiUrl = user.tainacan_api_url ? (user.tainacan_api_url + '/tools') : '';
const nonce = user.nonce || '';

const i18n = (window.tainacan_tools && window.tainacan_tools.i18n) || {};

function tainacanToolsPerformWhenDocumentIsLoaded(callback) {
	if (/comp|inter|loaded/.test(document.readyState)) {
		callback();
	} else {
		document.addEventListener('DOMContentLoaded', callback, false);
	}
}

function request(method, path, body) {
	const url = apiUrl.replace(/\/$/, '') + path;
	const opts = {
		method: method,
		headers: {
			'Content-Type': 'application/json',
			'X-WP-Nonce': nonce
		}
	};
	if (body && (method === 'POST' || method === 'PUT')) {
		opts.body = JSON.stringify(body);
	}
	return fetch(url, opts).then(function (res) {
		return res.json().then(function (data) {
			if (!res.ok) {
				const err = new Error(data.message || res.statusText);
				err.data = data;
				err.status = res.status;
				throw err;
			}
			return data;
		});
	});
}

function showRunningNotice(status) {
	const el = document.getElementById('tainacan-tools-running-notice');
	if (!el) return;
	if (status && status.running && status.tool_name) {
		el.textContent = status.tool_name + ' ' + (i18n.running || 'is running…');
		el.style.display = 'block';
	} else {
		el.style.display = 'none';
	}
}

function renderLogLine(entry) {
	const level = (entry.level || 'info').toLowerCase();
	const msg = (entry.message != null) ? String(entry.message) : '';
	const line = document.createElement('div');
	line.className = 'tainacan-tools-log-line tainacan-tools-log-line--' + level;
	line.textContent = msg;
	return line;
}

function renderOutput(logs) {
	const fragment = document.createDocumentFragment();
	if (!logs || !logs.length) {
		const placeholder = document.createElement('div');
		placeholder.className = 'tainacan-tools-card-output-placeholder';
		placeholder.textContent = i18n.no_output || 'No output yet.';
		fragment.appendChild(placeholder);
		return fragment;
	}
	logs.forEach(function (entry) {
		fragment.appendChild(renderLogLine(entry));
	});
	return fragment;
}

/**
 * Build request body from server-rendered form inputs in the card.
 *
 * @param {HTMLElement} card
 * @returns {Object}
 */
function getFormBodyFromCard(card) {
	const body = {};
	const inputs = card.querySelectorAll('input[name], select[name]');
	inputs.forEach(function (input) {
		const name = input.getAttribute('name');
		if (!name) return;
		if (input.type === 'checkbox') {
			body[name] = input.checked;
		} else if (input.type === 'number') {
			const val = input.value;
			body[name] = (val === '') ? undefined : parseInt(val, 10);
		} else if (input.value !== '') {
			body[name] = input.value;
		}
	});
	return body;
}

function bindRunButtons() {
	const cards = document.querySelectorAll('.tainacan-tools-card');
	cards.forEach(function (card) {
		const runBtn = card.querySelector('.tainacan-tools-run-btn');
		const output = card.querySelector('.tainacan-tools-card-output');
		if (!runBtn || !output) return;

		const toolId = card.dataset.toolId;
		const destructive = card.dataset.destructive === '1';
		let requiredParams = [];
		try {
			if (card.dataset.requiredParams) {
				requiredParams = JSON.parse(card.dataset.requiredParams);
			}
		} catch (e) {}

		runBtn.addEventListener('click', function () {
			if (destructive) {
				const msg = i18n.confirm_destructive || 'This action cannot be undone. Continue?';
				if (!confirm(msg)) return;
			}

			const body = getFormBodyFromCard(card);
			for (let i = 0; i < requiredParams.length; i++) {
				const val = body[requiredParams[i]];
				if (val === undefined || val === '' || (typeof val === 'string' && !val.trim())) {
					alert(i18n.required_field || 'Please fill required fields.');
					return;
				}
			}

			runBtn.disabled = true;
			runBtn.textContent = i18n.running || 'Running…';
			output.innerHTML = '';
			output.appendChild(renderOutput([{ level: 'info', message: i18n.running || 'Running…' }]));

			request('POST', '/' + toolId + '/run', body)
				.then(function (data) {
					output.innerHTML = '';
					output.appendChild(renderOutput(data.logs || []));
				})
				.catch(function (err) {
					const logs = (err.data && err.data.logs) || [];
					if (err.data && err.data.message) {
						logs.push({ level: 'error', message: err.data.message });
					} else {
						logs.push({ level: 'error', message: err.message || 'Request failed.' });
					}
					output.innerHTML = '';
					output.appendChild(renderOutput(logs));
				})
				.finally(function () {
					runBtn.disabled = false;
					runBtn.textContent = i18n.run || 'Run';
					request('GET', '/status').then(showRunningNotice).catch(function () { showRunningNotice({ running: false }); });
				});
		});
	});
}

function loadStatus() {
	request('GET', '/status')
		.then(showRunningNotice)
		.catch(function () { showRunningNotice({ running: false }); });
}

function init() {
	if (!apiUrl) return;
	loadStatus();
	bindRunButtons();
}

tainacanToolsPerformWhenDocumentIsLoaded(init);
