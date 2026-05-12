

// Roles
/**
 * Commits `capability/addCapabilityToRole` state changes.
 * @param {Object} state - Module state.
 * @returns {void} No return value.
 */
export const addCapabilityToRole = (state, {capabilityKey, role}) => {
    if (state.capabilities[capabilityKey] && state.capabilities[capabilityKey].roles[role.slug] == undefined) {
        let updateRoles = state.capabilities[capabilityKey].roles ? state.capabilities[capabilityKey].roles : {};
        updateRoles[role.slug] = role;
        const updatedCapability = state.capabilities[capabilityKey];
        Object.assign(updatedCapability, { 'roles': updateRoles });
        Object.assign(state.capabilities, { [capabilityKey]: updatedCapability } );
    }
    if (state.role && state.role.slug && state.role.slug == role.slug) {
        state.role = role;
    }
    if ( state.roles[role.slug] ) {
        Object.assign(state.roles[role.slug], { 'capabilities': role.capabilities });
    }
};

/**
 * Commits `capability/removeCapabilityFromRole` state changes.
 * @param {Object} state - Module state.
 * @returns {void} No return value.
 */
export const removeCapabilityFromRole = (state, {capabilityKey, role}) => {
    if (state.capabilities[capabilityKey]) {
        let updateRoles = state.capabilities[capabilityKey].roles;
        delete updateRoles[role.slug];
        const updatedCapability = state.capabilities[capabilityKey];
        Object.assign(updatedCapability, { 'roles': updateRoles });
        Object.assign(state.capabilities, { [capabilityKey]: updatedCapability } );
    }
    if (state.role && state.role.slug && state.role.slug == role.slug) {
        state.role = role;
    }
    if ( state.roles[role.slug] ) {
        Object.assign(state.roles[role.slug], { 'capabilities': role.capabilities });
    }
};

/**
 * Commits `capability/setRoles` state changes.
 * @param {Object} state - Module state.
 * @param {*} roles - Mutation payload.
 * @returns {void} No return value.
 */
export const setRoles = (state, roles) => {
    state.roles = roles;
};

/**
 * Commits `capability/setRole` state changes.
 * @param {Object} state - Module state.
 * @param {*} role - Mutation payload.
 * @returns {void} No return value.
 */
export const setRole = (state, role) => {
    state.role = role;
};

/**
 * Commits `capability/deleteRole` state changes.
 * @param {Object} state - Module state.
 * @param {*} roleSlug - Mutation payload.
 * @returns {void} No return value.
 */
export const deleteRole = (state, roleSlug) => {
    delete state.roles[roleSlug]
};

// CAPABILITIES
/**
 * Commits `capability/setCapabilities` state changes.
 * @param {Object} state - Module state.
 * @param {*} capabilities - Mutation payload.
 * @returns {void} No return value.
 */
export const setCapabilities = (state, capabilities) => {
    state.capabilities = capabilities;
};

/**
 * Commits `capability/setCapability` state changes.
 * @param {Object} state - Module state.
 * @param {*} capability - Mutation payload.
 * @returns {void} No return value.
 */
export const setCapability = (state, capability) => {
    state.capability = capability;
};


// ADMIN UI OPTIONS
/**
 * Commits `capability/setAdminUIOptions` state changes.
 * @param {Object} state - Module state.
 * @param {*} adminUIOptions - Mutation payload.
 * @returns {void} No return value.
 */
export const setAdminUIOptions = (state, adminUIOptions) => {
    state.adminUIOptions = adminUIOptions;
};
