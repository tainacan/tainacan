import axios from '../../../axios';
import qs from 'qs';

/**
 * Dispatches `activity/fetchActivities`.
 * @returns {*} Action result.
 */
export const fetchActivities = ({ commit }, { page, activitiesPerPage, search, searchDates, authorId} ) => {

    let endpoint = `/logs?paged=${page}&perpage=${activitiesPerPage}&context=edit&orderby=id&order=desc`;

    if (search != undefined && search != '')
        endpoint += `&search=${search}`;
    
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

    if (authorId != undefined && authorId != null)
        endpoint += '&authorid=' + authorId;

    return new Promise((resolve, reject) => {
        axios.tainacanApi.get(endpoint)
            .then(res => {
                let activities = res.data;

                commit('setActivities', activities);

                resolve({
                    activities: activities,
                    total: res.headers['x-wp-total']
                });
            })
            .catch(error => reject(error));
    });
};

/**
 * Dispatches `activity/fetchCollectionActivities`.
 * @returns {*} Action result.
 */
export const fetchCollectionActivities = ({ commit }, { page, activitiesPerPage, collectionId, search, searchDates, authorId }) => {

    let endpoint = `/collection/${collectionId}/logs?paged=${page}&perpage=${activitiesPerPage}&context=edit&orderby=id&order=desc`;

    if (search != undefined && search != '')
        endpoint += `&search=${search}`;

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

    if (authorId != undefined && authorId != null)
        endpoint += '&authorid=' + authorId;

    return new Promise((resolve, reject) => {
        axios.tainacanApi.get(endpoint)
            .then(res => {
                let activities = res.data;

                commit('setActivities', activities);

                resolve({
                    activities: activities,
                    total: res.headers['x-wp-total']
                });
            })
            .catch(error => reject(error));
    });
};

/**
 * Dispatches `activity/fetchItemActivities`.
 * @returns {*} Action result.
 */
export const fetchItemActivities = ({ commit }, { page, activitiesPerPage, itemId, metadatumId, search, searchDates, authorId }) => {

    let endpoint = metadatumId
        ? `/item/${itemId}/metadata/${metadatumId}/logs?paged=${page}&perpage=${activitiesPerPage}&context=edit&orderby=id&order=desc&format_diffs=true`
        : `/item/${itemId}/logs?paged=${page}&perpage=${activitiesPerPage}&context=edit&orderby=id&order=desc`;

    if (search != undefined && search != '')
        endpoint += `&search=${search}`;

    if (authorId != undefined && authorId != null)
        endpoint += '&authorid=' + authorId;

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

    return new Promise((resolve, reject) => {
        axios.tainacanApi.get(endpoint)
            .then(res => {
                let activities = res.data;

                commit('setActivities', activities);

                resolve({
                    activities: activities,
                    total: res.headers['x-wp-total']
                });
            })
            .catch(error => reject(error));
    });
};

/**
 * Dispatches `activity/fetchActivity`.
 * @returns {*} Action result.
 */
export const fetchActivity = ({ commit }, activityId) => {
    commit('clearActivity');
    return new Promise((resolve, reject) => {
       axios.tainacanApi.get(`/logs/${activityId}?context=edit&format_diffs=true`)
           .then(res => {
               let activity = res.data;

               commit('setActivity', activity);

               resolve({
                   activity: activity
               });
           })
           .catch(error => reject(error));
    });
};


// Users for filtering and core author metadata
/**
 * Dispatches `activity/fetchUsers`.
 * @returns {*} Action result.
 */
export const fetchUsers = ({ commit }, { search, page, exclude }) => {
    let endpoint = '/users';
    let params = {
        search: search
    };

    if (page)
        params.page = page;

    if (exclude)
       params.exclude = exclude;
       
    return new Promise((resolve, reject) => {
        axios.wpApi.get(endpoint + '?' + qs.stringify(params))
            .then(res => {
                resolve({ users: res.data, totalUsers: res.headers['x-wp-total'] } );
            })
            .catch(error => {
                reject(error);
            });
    });
};

// Single user for core author metadata
/**
 * Dispatches `activity/fetchUser`.
 * @returns {*} Action result.
 */
export const fetchUser = ({ commit }, userId) => {
    return new Promise((resolve, reject) => {
        axios.wpApi.get('/users/' + userId)
            .then(res => {
                resolve({ user: res.data });
            })
            .catch(error => {
                reject(error);
            });
    });
};