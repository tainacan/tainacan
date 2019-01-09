import axios from '../../../axios/axios'


export const fetchActivities = ({ commit }, { page, activitiesPerPage } ) => {
    return new Promise((resolve, reject) => {
        axios.tainacan.get(`/logs?paged=${page}&perpage=${activitiesPerPage}&context=edit`)
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

export const fetchCollectionActivities = ({ commit }, { page, activitiesPerPage, collectionId }) => {
    return new Promise((resolve, reject) => {
        axios.tainacan.get(`/collection/${collectionId}/logs?paged=${page}&perpage=${activitiesPerPage}&context=edit`)
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

export const fetchItemActivities = ({ commit }, { page, activitiesPerPage, itemId }) => {
    return new Promise((resolve, reject) => {
        axios.tainacan.get(`/item/${itemId}/logs?paged=${page}&perpage=${activitiesPerPage}&context=edit`)
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