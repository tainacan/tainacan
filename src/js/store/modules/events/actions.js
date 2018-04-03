import axios from '../../../axios/axios'


export const fetchEvents = ({ commit }, { page, eventsPerPage } ) => {
    return new Promise((resolve, reject) => {
        axios.tainacan.get(`/logs?paged=${page}&perpage=${eventsPerPage}`)
            .then(res => {
                let events = res.data;

                commit('setEvents', events);

                resolve({
                    'events': events,
                    'total': res.headers['x-wp-total']
                });
            })
            .catch(error => {
                reject(error);
            });
    });
};

export const approve = ({commit}, eventId) => {

};

export const notApprove = ({commit}, eventId) => {

};