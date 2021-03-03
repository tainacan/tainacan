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

export const fetchSummary = ({ commit }, { collectionId } ) => {

    let endpoint = '/reports';
    
    if (collectionId && collectionId != 'default')
        endpoint += '/collection/' + collectionId + '/summary';
    else
        endpoint += '/repository/summary';

    return new Promise((resolve, reject) => {
        axios.tainacan.get(endpoint)
            .then(res => {
                let summary = res.data;

                commit('setSummary', summary);
                resolve(summary);
            })
            .catch(error => reject(error));
    });
};

export const fetchTaxonomiesList = ({ commit }, ) => {

    let endpoint = '/reports/taxonomies/list'

    return new Promise((resolve, reject) => {
        axios.tainacan.get(endpoint)
            .then(res => {
                let taxonomiesList = res.data.list ? res.data.list : {};

                commit('setTaxonomiesList', taxonomiesList);
                resolve(taxonomiesList);
            })
            .catch(error => reject(error));
    });
};