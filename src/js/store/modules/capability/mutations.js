import Vue from 'vue';

// Roles
export const addCapabilityToRole = (state, {capabilityKey, role}) => {
    if (state.capabilities[capabilityKey].roles[role.slug] == undefined) {
        let updateRoles = state.capabilities[capabilityKey].roles.length ? state.capabilities[capabilityKey].roles : {};
        updateRoles[role.slug] = role;
        Vue.set(state.capabilities[capabilityKey], 'roles', updateRoles)
    }

    if (state.role && state.role.slug && state.role.slug == role)
        Vue.set(state.role.capabilities, capabilityKey, true)
};

export const removeCapabilityFromRole = (state, {capabilityKey, role}) => {
    let updateRoles = state.capabilities[capabilityKey].roles;
    delete updateRoles[role.slug];
    Vue.set(state.capabilities[capabilityKey], 'roles', updateRoles)
    
    if (state.role && state.role.slug && state.role.slug == role)
        delete state.role.capabilities[capabilityKey]
};

export const setRoles = (state, roles) => {
    state.roles = roles;
};

export const setRole = (state, role) => {
    state.role = role;
};

// CAPABILITIES
export const setCapabilities = (state, capabilities) => {
    state.capabilities = capabilities;
};

export const setCapability = (state, capability) => {
    state.capability = capability;
};
