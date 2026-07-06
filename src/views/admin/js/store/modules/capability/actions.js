import axios from '../../../axios'

// ROLES
/**
 * Dispatches `capability/addCapabilityToRole`.
 * @returns {*} Action result.
 */
export const addCapabilityToRole = ({ commit }, { capabilityKey, role }) => {
    return new Promise(( resolve, reject ) => {
        axios.tainacanApi.put('/roles/' + role + '?add_cap=' + capabilityKey)
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

/**
 * Dispatches `capability/removeCapabilityFromRole`.
 * @returns {*} Action result.
 */
export const removeCapabilityFromRole = ({ commit }, { capabilityKey, role }) => {
    return new Promise(( resolve, reject ) => {
        axios.tainacanApi.put('/roles/' + role + '?remove_cap=' + capabilityKey)
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

/**
 * Dispatches `capability/fetchRoles`.
 * @returns {*} Action result.
 */
export const fetchRoles = ({ commit }) => {
    return new Promise((resolve, reject) => {

        axios.tainacanApi.get('/roles')
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

/**
 * Dispatches `capability/fetchRole`.
 * @returns {*} Action result.
 */
export const fetchRole = ({ commit }, roleSlug) => {
    return new Promise((resolve, reject) => {

        axios.tainacanApi.get('/roles/' + roleSlug)
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

/**
 * Dispatches `capability/createRole`.
 * @returns {*} Action result.
 */
export const createRole = ({ commit }, role) => {
    return new Promise((resolve, reject) => {

        axios.tainacanApi.post('/roles/', role)
            .then(res => {
                const role = res.data
                commit('setRole', role);
                resolve(role);
            })
            .catch((error) => {
                if (error.error && error.error.response && error.error.response.data)
                    reject(error.error.response.data);
            });
    });
};

/**
 * Dispatches `capability/updateRole`.
 * @returns {*} Action result.
 */
export const updateRole = ({ commit }, role) => {

    return new Promise((resolve, reject) => {

        axios.tainacanApi.put('/roles/' + role.slug, role)
            .then(res => {
                const updatedRole = res.data
                commit('setRole', updatedRole);
                resolve(updatedRole);
            })
            .catch(error => {
                if (error.error && error.error.response && error.error.response.data)
                    reject(error.error.response.data);
            });
    });
};


/**
 * Dispatches `capability/deleteRole`.
 * @returns {*} Action result.
 */
export const deleteRole = ({ commit }, roleSlug) => {
    return new Promise((resolve, reject) => {

        axios.tainacanApi.delete('/roles/' + roleSlug)
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
/**
 * Dispatches `capability/fetchCapabilities`.
 * @returns {*} Action result.
 */
export const fetchCapabilities = ({ commit }, { collectionId } ) => {
    return new Promise((resolve, reject) => {
        const endpoint = collectionId != undefined ? `/collection/${collectionId}/capabilities` : `/capabilities`;
        axios.tainacanApi.get(endpoint)
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

/**
 * Dispatches `capability/fetchCapability`.
 * @returns {*} Action result.
 */
export const fetchCapability = ({ commit }, capabilityId) => {
    return new Promise((resolve, reject) => {
       axios.tainacanApi.get(`/capabilities/${capabilityId}`)
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

// ADMIN UI OPTIONS
/**
 * Dispatches `capability/fetchAdminUIOptions`.
 * @returns {*} Action result.
 */
export const fetchAdminUIOptions = ({ commit }) => {
    return new Promise((resolve, reject) => {
        axios.tainacanApi.get('/admin-ui-options')
            .then(res => {
                let adminUIOptions = res.data && res.data['admin_ui_options'] ? res.data['admin_ui_options'] : {};
                commit('setAdminUIOptions', adminUIOptions);
                resolve(adminUIOptions);
            })
            .catch(error => {
                reject(error);
            });
    });
};

/**
 * Dispatches `capability/updateAdminUIOptions`.
 * @returns {*} Action result.
 */
export const updateAdminUIOptions = ({ commit }, adminUIOptions ) => {
    return new Promise((resolve, reject) => {
        axios.tainacanApi.put('/admin-ui-options/', { 'admin_ui_options': adminUIOptions })
            .then(res => {
                let adminUIOptions = res.data && res.data['admin_ui_options'] ? res.data['admin_ui_options'] : {};
                commit('setAdminUIOptions', adminUIOptions);
                resolve(adminUIOptions);
            })
            .catch(error => {
                reject(error);
            });
    });
};