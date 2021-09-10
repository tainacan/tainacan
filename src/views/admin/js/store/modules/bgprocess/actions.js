import axios from '../../../axios';
import qs from 'qs';

// Actions related to background processes
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

        axios.tainacan.get(endpoint)
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

export const updateProcess = ({ commit }, { id, status }) => {
    return new Promise((resolve, reject) => {
        axios.tainacan.patch(`/bg-processes/${id}/`, {
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

export const heartBitUpdateProcess = ({ commit }, aProcess) => {
    commit('setProcess', aProcess);
};

export const fetchProcess = ({ commit }, id) => {
    return new Promise((resolve, reject) => {
        axios.tainacan.get(`/bg-processes/${id}/`)
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

export const fetchProcessErrorLog = ({ commit }, { id: id, isFull: isFull }) => {
    return new Promise((resolve, reject) => {
        axios.tainacan.get(`/bg-processes/${id}/log`)
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

export const cleanProcesses = ({ commit }) => {
    commit('cleanProcesses');
};

export const deleteProcess = ({ commit }, id) => {
    return new Promise((resolve, reject) => {
        axios.tainacan.delete('/bg-processes/' + id).then( res => {
            commit('deleteProcess', { id: id });
            resolve( res );
        }).catch((error) => { 
            reject( error );
        });
    });
};
