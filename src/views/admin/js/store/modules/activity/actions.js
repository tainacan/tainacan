import axios from '../../../axios';
import qs from 'qs';

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
        axios.tainacan.get(endpoint)
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
        axios.tainacan.get(endpoint)
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

export const fetchItemActivities = ({ commit }, { page, activitiesPerPage, itemId, search, searchDates, authorId }) => {

    let endpoint = `/item/${itemId}/logs?paged=${page}&perpage=${activitiesPerPage}&context=edit&orderby=id&order=desc`;

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
        axios.tainacan.get(endpoint)
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

export const fetchActivity = ({ commit }, activityId) => {
    commit('clearActivity');
    return new Promise((resolve, reject) => {
       axios.tainacan.get(`/logs/${activityId}?context=edit`)
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

export const fetchActivityTitle = ({ commit }, activityId) => {
  return new Promise((resolve, reject) => {
      axios.tainacan.get(`/logs/${activityId}?fetch_only=title`)
          .then(res => {
              let eventTitle = res.data;

              commit('setActivityTitle', eventTitle.title);

              resolve(eventTitle.title);
          })
          .catch(error => reject(error));
  })
};

export const approve = ({commit}, activityId) => {
    return new Promise((resolve, reject) => {
        axios.tainacan.post(`/logs/${activityId}/approve`)
            .then(res => {
                let activity = res.data;

                resolve(activity);
            })
            .catch(error => reject(error))
    });
};

export const notApprove = ({commit}, activityId) => {

};

// Users for filtering
export const fetchUsers = ({ commit }, { search }) => {
    return new Promise((resolve, reject) => {
        axios.wp.get('/users?search=' + search)
        .then(res => {
            resolve(res.data);
        })
        .catch(error => {
            reject(error);
        });
    });
};