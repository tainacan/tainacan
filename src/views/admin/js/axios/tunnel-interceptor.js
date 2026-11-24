/**
 * Request interceptor for the Axios clients that send the logged-in user's REST nonce.
 * Sends PUT, PATCH and DELETE as POST with X-HTTP-Method-Override.
 * WordPress reads that header when the URL has no _method parameter.
 * Firewalls often block _method in the query string.
 */

const WRITE_METHODS = ['put', 'patch', 'delete'];

/**
 * @param {object} config Axios request config.
 * @returns {object}
 */
export function tunnelInterceptor(config) {
    if (!config || !config.method || !WRITE_METHODS.includes(String(config.method).toLowerCase()))
        return config;

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
