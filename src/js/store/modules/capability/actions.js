import axios from '../../../axios/axios'

// CAPABILITIES
export const updateCapability = ({ commit }, capability) => {
    return new Promise(( resolve, reject ) => {
        axios.tainacan.patch(`/roles/${capability.capabilityId}`, capability)
            .then( res => {
                let capability = res.data;
                commit('setCapability', capability);
                resolve( capability );
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