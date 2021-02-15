import axios from '../../../axios';

export const fetchReports = ({ commit }, {} ) => {

    let endpoint = `/reports`;

    return new Promise((resolve, reject) => {
        axios.tainacan.get(endpoint)
            .then(res => {
                let reports = res.data;

                commit('setReports', reports);

                resolve({
                    reports: reports,
                    total: res.headers['x-wp-total']
                });
            })
            .catch(error => reject(error));
    });
};
