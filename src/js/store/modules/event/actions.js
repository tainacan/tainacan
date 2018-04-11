import axios from '../../../axios/axios'


export const fetchEvents = ({ commit }, { page, eventsPerPage } ) => {
    return new Promise((resolve, reject) => {
        axios.tainacan.get(`/logs?paged=${page}&perpage=${eventsPerPage}&context=edit`)
            .then(res => {
                let events = res.data;

                commit('setEvents', events);

                resolve({
                    events: events,
                    total: res.headers['x-wp-total']
                });
            })
            .catch(error => reject(error));
    });
};

export const fetchEvent = ({ commit }, eventId) => {
    return new Promise((resolve, reject) => {
       axios.tainacan.get(`/logs/${eventId}?context=edit`)
           .then(res => {
               let event = res.data;

               commit('setEvent', event);

               resolve({
                   event: event
               });
           })
           .catch(error => reject(error));
    });
};

export const fetchEventTitle = ({ commit }, eventId) => {
  return new Promise((resolve, reject) => {
      axios.tainacan.get(`/logs/${eventId}?fetch_only=title`)
          .then(res => {
              let eventTitle = res.data;

              commit('setEventTitle', eventTitle.title);

              resolve(eventTitle.title);
          })
          .catch(error => reject(error));
  })
};

export const approve = ({commit}, eventId) => {

};

export const notApprove = ({commit}, eventId) => {

};