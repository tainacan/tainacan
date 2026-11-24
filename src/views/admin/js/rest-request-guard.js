/**
 * Request interceptor for the Axios clients that send the logged-in user's REST nonce.
 * It applies to every request those clients make, including routes added later.
 * A value concatenated into a path can otherwise open the query string, and WordPress
 * will honor rest_route and _method from that query string.
 */

// Query parameters WordPress uses to retarget a request. Not admin route names.
const OVERRIDE_QUERY_KEYS = new Set(['rest_route', '_method']);

/**
 * @param {string} message
 * @returns {Error}
 */
function unsafeRequestError(message) {
    const error = new Error(message);
    error.response = {
        status: 400,
        data: {
            error_message: message,
            errors: []
        }
    };
    return error;
}

function isAbsoluteURL(url) {
    return typeof url === 'string' && /^([a-z][a-z\d+\-.]*:)?\/\//i.test(url);
}

function combineURLs(baseURL, relativeURL) {
    if (!relativeURL)
        return baseURL;

    let end = baseURL.length;
    while (end > 0 && baseURL.charCodeAt(end - 1) === 47)
        end--;

    return baseURL.slice(0, end) + '/' + relativeURL.replace(/^\/+/, '');
}

function buildFullPath(baseURL, requestedURL) {
    if (baseURL && !isAbsoluteURL(requestedURL))
        return combineURLs(baseURL, requestedURL);

    return requestedURL || baseURL || '';
}

function prepareUrl(url) {
    return String(url).replace(/[\t\n\r]/g, '').replace(/\\/g, '/');
}

function normalizedPath(pathname) {
    const path = pathname.replace(/\/+$/, '');
    return path || '/';
}

function pathStaysUnder(targetPath, rootPath) {
    if (rootPath === '/')
        return targetPath === '/';

    return targetPath === rootPath || targetPath.startsWith(rootPath + '/');
}

function queryKeyIsOverride(key) {
    if (OVERRIDE_QUERY_KEYS.has(key))
        return true;

    return key.startsWith('rest_route[') || key.startsWith('_method[');
}

function restRouteStaysUnder(routeValue, prefix) {
    const routePath = String(routeValue).split('?')[0].split('#')[0];
    let pathname;

    try {
        pathname = new URL(routePath, 'http://rest-route.invalid').pathname;
    } catch {
        return false;
    }

    return pathStaysUnder(normalizedPath(pathname), normalizedPath(prefix));
}

function paramsRequestOverride(params) {
    if (!params)
        return false;

    if (typeof params.has === 'function' && typeof params.keys === 'function') {
        for (const key of params.keys()) {
            if (queryKeyIsOverride(key))
                return true;
        }
        return false;
    }

    if (typeof params !== 'object')
        return false;

    return Object.keys(params).some(queryKeyIsOverride);
}

/**
 * @param {object} config Axios request config.
 * @param {string} [locationHref] Base used to resolve relative URLs. Defaults to the current page.
 * @returns {{ ok: true } | { ok: false, message: string }}
 */
export function inspectRestRequest(config, locationHref) {
    const pageHref = locationHref || (typeof window !== 'undefined' ? window.location.href : 'http://localhost/');
    const requestedURL = config && config.url != null ? String(config.url) : '';
    const baseURL = config && config.baseURL ? String(config.baseURL) : '';

    if (paramsRequestOverride(config && config.params))
        return { ok: false, message: 'Blocked an unsafe REST request.' };

    let fullPath;
    try {
        fullPath = prepareUrl(buildFullPath(baseURL, requestedURL));
    } catch {
        return { ok: false, message: 'Blocked an unsafe REST request.' };
    }

    let target;
    let root = null;
    try {
        target = new URL(fullPath, pageHref);
        if (baseURL)
            root = new URL(prepareUrl(baseURL), pageHref);
    } catch {
        return { ok: false, message: 'Blocked an unsafe REST request.' };
    }

    if (root && target.origin !== root.origin)
        return { ok: false, message: 'Blocked an unsafe REST request.' };

    const allowedRestRoute = root ? root.searchParams.get('rest_route') : null;

    const tunneledMethod = config && config.tainacanTunneledMethod
        ? String(config.tainacanTunneledMethod).toUpperCase()
        : '';

    for (const [key, value] of target.searchParams.entries()) {
        if (key === '_method' || key.startsWith('_method[')) {
            if (!tunneledMethod || String(value).toUpperCase() !== tunneledMethod)
                return { ok: false, message: 'Blocked an unsafe REST request.' };
            continue;
        }

        if (key !== 'rest_route' && !key.startsWith('rest_route['))
            continue;

        if (!allowedRestRoute || !restRouteStaysUnder(value, allowedRestRoute))
            return { ok: false, message: 'Blocked an unsafe REST request.' };
    }

    if (/%2e%2e|%2f|%5c/i.test(target.pathname))
        return { ok: false, message: 'Blocked an unsafe REST request.' };

    if (root) {
        const rootPath = normalizedPath(root.pathname);
        const targetPath = normalizedPath(target.pathname);
        if (!pathStaysUnder(targetPath, rootPath))
            return { ok: false, message: 'Blocked an unsafe REST request.' };
    }

    return { ok: true };
}

/**
 * Axios request interceptor. A `_method` parameter is accepted only when this
 * client marked the request with tainacanTunneledMethod. That keeps PUT, PATCH
 * and DELETE working whichever request interceptor Axios runs first.
 *
 * @param {object} config
 * @returns {object|Promise<never>}
 */
export function guardRestRequest(config) {
    const inspection = inspectRestRequest(config);
    if (!inspection.ok)
        return Promise.reject(unsafeRequestError(inspection.message));

    return config;
}
