import axios from '../../../axios/axios'

// CAPABILITIES
export const associateCapabilityWithRole = ({ commit }, { capabilityKey, role }) => {
    return new Promise(( resolve, reject ) => {
        axios.tainacan.patch('/roles/' + role + '?add_cap=' + capabilityKey)
            .then( res => {
                let role = res.data;
                commit('associateCapabilityWithRole', {capabilityKey, role });
                resolve(role);
            })
            .catch(error => {
                reject({ error_message: error['response']['data'].error_message, errors: error['response']['data'].errors });
            });
    });
};

export const disassociateCapabilityWithRole = ({ commit }, { capabilityKey, role }) => {
    return new Promise(( resolve, reject ) => {
        axios.tainacan.patch('/roles/' + role + '?remove_cap=' + capabilityKey)
            .then( res => {
                let role = res.data;
                commit('disassociateCapabilityWithRole', {capabilityKey, role });
                resolve(role);
            })
            .catch(error => {
                reject({ error_message: error['response']['data'].error_message, errors: error['response']['data'].errors });
            });
    });
};

export const fetchCapabilities = ({ commit }, { collectionId } ) => {
    return new Promise((resolve, reject) => {

        axios.tainacan.get(`/collection/${collectionId}/roles`)
            .then(res => {
                let capabilities = res.data.capabilities;

                commit('setCapabilities', capabilities);
                resolve(capabilities);
            })
            .catch(error => {
                reject(error);
            });
    });
};

export const fetchCapability = ({ commit }, capabilityId) => {
    return new Promise((resolve, reject) => {
       axios.tainacan.get(`/capabilities/${capabilityId}`)
           .then(res => {
               let capability = res.data;

               commit('setCapability', capability);

               resolve({
                   'capability': capability
               })
           })
           .catch(error => {
               reject(error);
           })
    });
};

export const fetchCapabilityName = ({ commit }, capabilityId) => {
    return new Promise((resolve, reject) => {
        axios.tainacan.get(`/capabilities/${capabilityId}?fetch_only=name`)
            .then(res => {
                let name = res.data;

                commit('setCapabilityName');
                resolve(name.name)
            })
            .catch(error => {
                reject(error)
            })
    });
};