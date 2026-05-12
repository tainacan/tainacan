import axios from '../../../axios';
import qs from 'qs';

// Actions related to background processes
/**
 * Dispatches `bgprocess/fetchProcesses`.
 * @returns {*} Action result.
 */
export const fetchProcesses = ({ commit }, {page, processesPerPage, shouldUpdateStore, searchDates, search}) => {
    return new Promise((resolve, reject) => {
        let endpoint = '/bg-processes?all_users=1';

        if (page != undefined)
            endpoint += '&paged=' + page;
        if (processesPerPage != undefined)
            endpoint += '&perpage=' + processesPerPage;

        if (searchDates && searchDates[0] != null && searchDates[1] != null) {
            let dateQuery = {
                datequery: [
                    {
                        'after': searchDates[0],
                        'before': searchDates[1],
                        'inclusive': true
                    }
                ]
            };
            endpoint += '&' + qs.stringify(dateQuery);
        }

        if (search != undefined && search != '') {
            endpoint += `&search=${search}`;
        }

        axios.tainacanApi.get(endpoint)
        .then( res => {
            let processes = res.data;
            /*
            if (shouldUpdateStore)
                commit('setProcesses', processes);
             */
            commit('setProcesses', processes);
            resolve({ 'processes': processes, 'total': res.headers['x-wp-total'] });
        })
        .catch( error => {
            reject(error);
        })
    });
};

/**
 * Dispatches `bgprocess/updateProcess`.
 * @returns {*} Action result.
 */
export const updateProcess = ({ commit }, { id, status }) => {
    return new Promise((resolve, reject) => {
        axios.tainacanApi.put(`/bg-processes/${id}/`, {
            status: status,
        })
            .then( res => {
                let aProcess = res.data;
                commit('setProcess', aProcess);
                resolve(aProcess)
            })
            .catch( error => {
                reject(error);
            })
    });
};

/**
 * Dispatches `bgprocess/heartBitUpdateProcess`.
 * @returns {*} Action result.
 */
export const heartBitUpdateProcess = ({ commit }, aProcess) => {
    commit('setProcess', aProcess);
};

/**
 * Dispatches `bgprocess/fetchProcess`.
 * @returns {*} Action result.
 */
export const fetchProcess = ({ commit }, id) => {
    return new Promise((resolve, reject) => {
        axios.tainacanApi.get(`/bg-processes/${id}/`)
        .then( res => {
            let aProcess = res.data;
            commit('setProcess', aProcess);
            
            resolve(aProcess)
        })
        .catch( error => {
            reject(error);
        })
    });
};

/**
 * Dispatches `bgprocess/fetchProcessErrorLog`.
 * @returns {*} Action result.
 */
export const fetchProcessErrorLog = ({ commit }, { id: id, isFull: isFull }) => {
    return new Promise((resolve, reject) => {
        axios.tainacanApi.get(`/bg-processes/${id}/log`)
        .then( res => {
            let errorLog = res.data;
            commit('setProcessErrorLog', errorLog);
            resolve(errorLog)
        })
        .catch( error => {
            reject(error);
        })
    });
};

/**
 * Dispatches `bgprocess/cleanProcesses`.
 * @returns {*} Action result.
 */
export const cleanProcesses = ({ commit }) => {
    commit('cleanProcesses');
};

/**
 * Dispatches `bgprocess/deleteProcess`.
 * @returns {*} Action result.
 */
export const deleteProcess = ({ commit }, id) => {
    return new Promise((resolve, reject) => {
        axios.tainacanApi.delete('/bg-processes/' + id).then( res => {
            commit('deleteProcess', { id: id });
            resolve( res );
        }).catch((error) => { 
            reject( error );
        });
    });
};
