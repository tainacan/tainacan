/**
 * Request interceptor for the Axios clients that send the logged-in user's REST nonce.
 * Sends PUT, PATCH and DELETE as POST with X-HTTP-Method-Override.
 * WordPress applies $_GET['_method'] before that header, so this removes any
 * _method query parameter from the request. Firewalls often block it in the URL.
 */

const WRITE_METHODS = ['put', 'patch', 'delete'];

function isMethodOverrideKey(key) {
    return key === '_method' || key.startsWith('_method[');
}

function stripMethodFromQuery(query) {
    const params = new URLSearchParams(query);
    let removed = false;

    for (const key of [...params.keys()]) {
        if (!isMethodOverrideKey(key))
            continue;
        params.delete(key);
        removed = true;
    }

    return removed ? params.toString() : null;
}

function stripMethodFromUrl(url) {
    const value = String(url);
    const hashIndex = value.indexOf('#');
    const hash = hashIndex === -1 ? '' : value.slice(hashIndex);
    const withoutHash = hashIndex === -1 ? value : value.slice(0, hashIndex);
    const queryIndex = withoutHash.indexOf('?');

    if (queryIndex === -1)
        return value;

    const stripped = stripMethodFromQuery(withoutHash.slice(queryIndex + 1));
    if (stripped == null)
        return value;

    const path = withoutHash.slice(0, queryIndex);
    return path + (stripped ? '?' + stripped : '') + hash;
}

function stripMethodFromParams(params) {
    if (!params)
        return;

    if (typeof params.keys === 'function' && typeof params.delete === 'function') {
        for (const key of [...params.keys()]) {
            if (isMethodOverrideKey(key))
                params.delete(key);
        }
        return;
    }

    if (typeof params !== 'object')
        return;

    for (const key of Object.keys(params)) {
        if (isMethodOverrideKey(key))
            delete params[key];
    }
}

/**
 * @param {object} config Axios request config.
 * @returns {object}
 */
export function tunnelInterceptor(config) {
    if (!config || !config.method || !WRITE_METHODS.includes(String(config.method).toLowerCase()))
        return config;

    if (config.url != null)
        config.url = stripMethodFromUrl(config.url);
    stripMethodFromParams(config.params);

    const originalMethod = String(config.method).toUpperCase();
    config.method = 'post';

    if (config.headers && typeof config.headers.set === 'function')
        config.headers.set('X-HTTP-Method-Override', originalMethod);
    else {
        config.headers = config.headers || {};
        config.headers['X-HTTP-Method-Override'] = originalMethod;
    }

    return config;
}
