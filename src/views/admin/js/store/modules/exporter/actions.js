import { tainacanApi } from '../../../axios/axios';

/**
 * Builds a REST path from a template. Each inserted value must be hexadecimal,
 * the same shape the exporter session routes accept. The value is encoded
 * before it is joined to the path. Returns null when one is not.
 * @param {TemplateStringsArray} strings
 * @param {...unknown} values
 * @returns {string|null}
 */
function hexPath(strings, ...values) {
    let path = strings[0];

    for (let index = 0; index < values.length; index++) {
        const id = String(values[index] == null ? '' : values[index]);
        if (!/^[0-9a-f]+$/.test(id))
            return null;
        path += encodeURIComponent(id) + strings[index + 1];
    }

    return path;
}

/**
 * Dispatches `exporter/fetchAvailableExporters`.
 * @returns {*} Action result.
 */
export const fetchAvailableExporters = () => {

    return tainacanApi.get('/exporters/available')
        .then(response => {
            return response.data;
        })
};

/**
 * Dispatches `exporter/createExporterSession`.
 * @returns {*} Action result.
 */
export const createExporterSession = ({commit}, slug) => {

    return tainacanApi.post('/exporters/session', { exporter_slug: slug })
        .then(response => {
            commit('setExporterSession', response.data);

            return response.data;
        });
};

/**
 * Dispatches `exporter/updateExporterSession`.
 * @returns {*} Action result.
 */
export const updateExporterSession = ({commit}, exporterSessionUpdated) => {

    return new Promise(( resolve, reject ) => {
        const endpoint = hexPath`/exporters/session/${exporterSessionUpdated.id}`;
        if (endpoint == null) {
            reject(new Error('Invalid session id.'));
            return;
        }

        tainacanApi.put(endpoint, exporterSessionUpdated.body)
            .then(response => {
                commit('setExporterSession');
                resolve( response.data );
            })
            .catch(error => {
                reject(error);
            });
    });
};

/**
 * Dispatches `exporter/runExporterSession`.
 * @returns {*} Action result.
 */
export const runExporterSession = ({commit}, exporterSessionID) => {

    return new Promise(( resolve, reject ) => {
        const endpoint = hexPath`/exporters/session/${exporterSessionID}/run`;
        if (endpoint == null) {
            reject(new Error('Invalid session id.'));
            return;
        }

        tainacanApi.post(endpoint)
            .then(response => {
                commit('setBackGroundProcessID', response.data);
                resolve(response.data);
            })
            .catch(error => {
                reject(error);
            })
        });
};