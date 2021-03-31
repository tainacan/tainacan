import axios from '../../../axios';

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

export const fetchMetadata = ({ commit }, { collectionId } ) => {

    let endpoint = '/reports';
    
    if (collectionId && collectionId != 'default')
        endpoint += '/collection/' + collectionId + '/metadata';
    else
        endpoint += '/metadata';

    return new Promise((resolve, reject) => {
        axios.tainacan.get(endpoint)
            .then(res => {
                let metadata = res.data;

                commit('setMetadata', metadata);
                resolve(metadata);
            })
            .catch(error => reject(error));
    });
};

export const fetchMetadataList = ({ commit }, { collectionId, metadatumId } ) => {

    let endpoint = '/reports';
    
    if (collectionId && collectionId != 'default')
        endpoint += '/collection/' + collectionId + '/metadata/' + metadatumId;
    else
        endpoint += '/metadata/' + metadatumId;

    return new Promise((resolve, reject) => {
        axios.tainacan.get(endpoint)
            .then(res => {
                let metadataList = res.data.list;

                commit('setMetadataList', metadataList);
                resolve(metadataList);
            })
            .catch(error => reject(error));
    });
};

export const fetchCollectionsList = ({ commit }) => {

    let endpoint = '/reports/collection'

    return new Promise((resolve, reject) => {
        axios.tainacan.get(endpoint)
            .then(res => {
                let collectionsList = res.data.list ? res.data.list : {};

                commit('setCollectionsList', collectionsList);
                resolve(collectionsList);
            })
            .catch(error => reject(error));
    });
};

export const fetchTaxonomiesList = ({ commit }) => {

    let endpoint = '/reports/taxonomy'

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

export const fetchTaxonomyTerms = ({ commit }, taxonomyId) => {

    let endpoint = '/reports/taxonomy/' + taxonomyId;

    return new Promise((resolve, reject) => {
        axios.tainacan.get(endpoint)
            .then(res => {
                let taxonomyTerms = res.data.terms ? res.data.terms : {};

                commit('setTaxonomyTerms', taxonomyTerms);
                resolve(taxonomyTerms);
            })
            .catch(error => reject(error));
    });
};

export const fetchActivities = ({ commit }, { collectionId, startDate, force } ) => {

    let endpoint = '/reports';
    
    if (collectionId && collectionId != 'default')
        endpoint += '/collection/' + collectionId + '/activities?';
    else
        endpoint += '/activities?';

    if (startDate)
        endpoint += '&start=' + startDate;

    if (startDate || force)
        endpoint += '&force=yes';

    return new Promise((resolve, reject) => {
        axios.tainacan.get(endpoint)
            .then(res => {
                let activities = res.data;

                commit('setActivities', activities);
                resolve(activities);
            })
            .catch(error => reject(error));
    });
};