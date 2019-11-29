import Vue from 'vue';

// Roles
export const addCapabilityToRole = (state, {capabilityKey, role}) => {
    if (state.capabilities[capabilityKey] && state.capabilities[capabilityKey].roles[role.slug] == undefined) {
        let updateRoles = state.capabilities[capabilityKey].roles.length ? state.capabilities[capabilityKey].roles : {};
        updateRoles[role.slug] = role;
        Vue.set(state.capabilities[capabilityKey], 'roles', updateRoles)
    }
    if (state.role && state.role.slug && state.role.slug == role.slug)
        state.role = role;
};

export const removeCapabilityFromRole = (state, {capabilityKey, role}) => {
    if (state.capabilities[capabilityKey]) {
        let updateRoles = state.capabilities[capabilityKey].roles;
        delete updateRoles[role.slug];
        Vue.set(state.capabilities[capabilityKey], 'roles', updateRoles)
    }
    if (state.role && state.role.slug && state.role.slug == role.slug)
        state.role = role;
};

export const setRoles = (state, roles) => {
    state.roles = roles;
};

export const setRole = (state, role) => {
    state.role = role;
};

export const deleteRole = (state, role) => {
    const existingIndex = state.roles.findIndex((aRole) => role.slug == aRole.slug);
    if (existingIndex >= 0)
        state.roles.splice(existingIndex, 1)

};

// CAPABILITIES
export const setCapabilities = (state, capabilities) => {
    state.capabilities = capabilities;
};

export const setCapability = (state, capability) => {
    state.capability = capability;
};
