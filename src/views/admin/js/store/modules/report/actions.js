import axios from '../../../axios';

export const fetchSummary = ({ commit }, { collectionId, force } ) => {

    let endpoint = '/reports';
    
    if (collectionId && collectionId != 'default')
        endpoint += '/collection/' + collectionId + '/summary';
    else
        endpoint += '/repository/summary';

    if (force && force === true)
        endpoint += '?force=yes'

    return new Promise((resolve, reject) => {
        axios.tainacan.get(endpoint)
            .then(res => {
                let summary = res.data;

                commit('setSummary', summary);
                commit('setReportLatestCachedOn', { report: 'summary-' + (collectionId ? collectionId : 'default'), reportLatestCachedOn: res.data.report_cached_on });
                resolve(summary);
            })
            .catch(error => reject(error));
    });
};

export const fetchMetadata = ({ commit }, { collectionId, force } ) => {

    let endpoint = '/reports';
    
    if (collectionId && collectionId != 'default')
        endpoint += '/collection/' + collectionId + '/metadata';
    else
        endpoint += '/metadata';

    if (force && force === true)
        endpoint += '?force=yes';

    return new Promise((resolve, reject) => {
        axios.tainacan.get(endpoint)
            .then(res => {
                let metadata = res.data;

                commit('setMetadata', metadata);
                commit('setReportLatestCachedOn', { report: 'metadata-' + (collectionId ? collectionId : 'default'), reportLatestCachedOn: res.data.report_cached_on });
                resolve(metadata);
            })
            .catch(error => reject(error));
    });
};

export const fetchMetadataList = ({ commit }, { collectionId, onlyTaxonomies } ) => {

    let endpoint = '';
    
    if (collectionId && collectionId != 'default')
        endpoint += '/collection/' + collectionId + '/metadata/?nopaging=1';
    else
        endpoint += '/metadata/?nopaging=1';

    if (onlyTaxonomies)
        endpoint += '&metaquery[0][key]=metadata_type&metaquery[0][value]=Tainacan\\Metadata_Types\\Taxonomy';

    return new Promise((resolve, reject) => {
        axios.tainacan.get(endpoint)
            .then(res => {
                let metadataList = res.data;

                commit('setMetadataList', metadataList);
                resolve(metadataList);
            })
            .catch(error => reject(error));
    });
};

export const fetchCollectionsList = ({ commit }, force) => {

    let endpoint = '/reports/collection';
    
    if (force && force === true)
        endpoint += '?force=yes';

    return new Promise((resolve, reject) => {
        axios.tainacan.get(endpoint)
            .then(res => {
                let collectionsList = res.data.list ? res.data.list : {};

                commit('setCollectionsList', collectionsList);
                commit('setReportLatestCachedOn', { report: 'collections', reportLatestCachedOn: res.data.report_cached_on });
                resolve(collectionsList);
            })
            .catch(error => reject(error));
    });
};

export const fetchTaxonomiesList = ({ commit }, force) => {

    let endpoint = '/reports/taxonomy';

    if (force && force === true)
        endpoint += '?force=yes';

    return new Promise((resolve, reject) => {
        axios.tainacan.get(endpoint)
            .then(res => {
                let taxonomiesList = res.data.list ? res.data.list : {};

                commit('setTaxonomiesList', taxonomiesList);
                commit('setReportLatestCachedOn', { report: 'taxonomies', reportLatestCachedOn: res.data.report_cached_on });
                resolve(taxonomiesList);
            })
            .catch(error => reject(error));
    });
};

export const fetchTaxonomyTerms = ({ commit }, { taxonomyId, collectionId, parentTerm, isChildChart, force }) => {

    let endpoint = '/reports';
    
    if (collectionId && collectionId != 'default')
        endpoint += '/collection/' + collectionId + '/metadata/' + taxonomyId + '?';
    else
        endpoint += '/taxonomy/' + taxonomyId + '?';

    if (force)
        endpoint += '&force=yes';

    if (parentTerm)
        endpoint += '&parent=' + parentTerm;

    return new Promise((resolve, reject) => {
        axios.tainacan.get(endpoint)
            .then(res => {
                let taxonomyTerms = {};
                if (collectionId && collectionId != 'default')
                    taxonomyTerms = res.data.list ? res.data.list : [];
                else
                    taxonomyTerms = res.data.terms ? Object.values(res.data.terms) : [];

                if (isChildChart) {
                    commit('setTaxonomyChildTerms', taxonomyTerms);
                    commit('setReportLatestCachedOn', { 
                        report: 'taxonomy-terms-' + (collectionId ? collectionId : 'default') + '-' + taxonomyId + (parentTerm ? '-' + parentTerm : '') + '-is-child-chart',
                        reportLatestCachedOn: res.data.report_cached_on
                    });
                } else {
                    commit('setTaxonomyTerms', taxonomyTerms);
                    commit('setReportLatestCachedOn', { 
                        report: 'taxonomy-terms-' + (collectionId ? collectionId : 'default') + '-' + taxonomyId + (parentTerm ? '-' + parentTerm : ''),
                        reportLatestCachedOn: res.data.report_cached_on
                    });
                }
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

    if (force)
        endpoint += '&force=yes';

    return new Promise((resolve, reject) => {
        axios.tainacan.get(endpoint)
            .then(res => {
                let activities = res.data;

                commit('setActivities', activities);
                commit('setReportLatestCachedOn', { report: 'activities-' + (collectionId ? collectionId : 'default') + (startDate ? '-' + startDate : ''), reportLatestCachedOn: res.data.report_cached_on });
                resolve(activities);
            })
            .catch(error => reject(error));
    });
};