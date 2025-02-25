

// Roles
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

export const setRoles = (state, roles) => {
    state.roles = roles;
};

export const setRole = (state, role) => {
    state.role = role;
};

export const deleteRole = (state, roleSlug) => {
    delete state.roles[roleSlug]
};

// CAPABILITIES
export const setCapabilities = (state, capabilities) => {
    state.capabilities = capabilities;
};

export const setCapability = (state, capability) => {
    state.capability = capability;
};


// ADMIN UI OPTIONS
export const setAdminUIOptions = (state, adminUIOptions) => {
    state.adminUIOptions = adminUIOptions;
};