import axios from '../../../axios/axios';

// Actions related to background processes
export const fetchProcesses = ({ commit }, {page, processesPerPage}) => {
    return new Promise((resolve, reject) => {
        let endpoint = '/bg-processes?all_users=1';

        if (page != undefined)
            endpoint += 'paged=' + page;
        if (processesPerPage != undefined)
            endpoint += '&perpage=' + processesPerPage;

        axios.tainacan.get(endpoint)
        .then( res => {
            let processes = res.data;
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
                let process = res.data;
                commit('setProcess', process);
                resolve(process)
            })
            .catch( error => {
                reject(error);
            })
    });
};

export const fetchProcess = ({ commit }, id) => {
    return new Promise((resolve, reject) => {
        axios.tainacan.get(`/bg-processes/${id}/`)
        .then( res => {
            let process = res.data;
            commit('setProcess', process);
            resolve(process)
        })
        .catch( error => {
            reject(error);
        })
    });
};

export const fetchProcessLog = ({ commit }, { id: id, isFull: isFull }) => {
    return new Promise((resolve, reject) => {
        axios.tainacan.get(`/bg-processes/${id}/log`)
        .then( res => {
            let log = res.data;
            commit('setProcessLog', log);
            resolve(log)
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
        axios.tainacan.delete('/bg-process/' + id)
        .then( res => {
            commit('deleteProcess', { id: id });
            resolve( res );
        }).catch((error) => { 
            reject( error );
        });
    });
};
