import axios from '../../../axios'

// ROLES
export const addCapabilityToRole = ({ commit }, { capabilityKey, role }) => {
    return new Promise(( resolve, reject ) => {
        axios.tainacan.patch('/roles/' + role + '?add_cap=' + capabilityKey)
            .then( res => {
                let role = res.data;
                commit('addCapabilityToRole', {capabilityKey, role });
                resolve(role);
            })
            .catch(error => {
                reject(error);
            });
    });
};

export const removeCapabilityFromRole = ({ commit }, { capabilityKey, role }) => {
    return new Promise(( resolve, reject ) => {
        axios.tainacan.patch('/roles/' + role + '?remove_cap=' + capabilityKey)
            .then( res => {
                let role = res.data;
                commit('removeCapabilityFromRole', {capabilityKey, role });
                resolve(role);
            })
            .catch(error => {
                reject(error);
            });
    });
};

export const fetchRoles = ({ commit }) => {
    return new Promise((resolve, reject) => {

        axios.tainacan.get('/roles')
            .then(res => {
                const roles = res.data
                commit('setRoles', roles);
                resolve(roles);
            })
            .catch(error => {
                reject(error);
            });
    });
};

export const fetchRole = ({ commit }, roleSlug) => {
    return new Promise((resolve, reject) => {

        axios.tainacan.get('/roles/' + roleSlug)
            .then(res => {
                const role = res.data
                commit('setRole', role);
                resolve(role);
            })
            .catch(error => {
                reject(error);
            });
    });
};

export const createRole = ({ commit }, role) => {
    return new Promise((resolve, reject) => {

        axios.tainacan.post('/roles/', role)
            .then(res => {
                const role = res.data
                commit('setRole', role);
                resolve(role);
            })
            .catch(error => {
                reject(error);
            });
    });
};

export const updateRole = ({ commit }, role) => {

    return new Promise((resolve, reject) => {

        axios.tainacan.patch('/roles/' + role.slug, role)
            .then(res => {
                const updatedRole = res.data
                commit('setRole', updatedRole);
                resolve(updatedRole);
            })
            .catch(error => {
                reject(error);
            });
    });
};


export const deleteRole = ({ commit }, roleSlug) => {
    return new Promise((resolve, reject) => {

        axios.tainacan.delete('/roles/' + roleSlug)
            .then(res => {
                const roleSlug = res.data
                commit('deleteRole', roleSlug);
                resolve(roleSlug);
            })
            .catch(error => {
                reject(error);
            });
    });
};

// CAPABILITIES
export const fetchCapabilities = ({ commit }, { collectionId } ) => {
    return new Promise((resolve, reject) => {
        const endpoint = collectionId != undefined ? `/collection/${collectionId}/capabilities` : `/capabilities`;
        axios.tainacan.get(endpoint)
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