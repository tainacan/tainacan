import Vue from 'vue';

// Roles
export const addCapabilityToRole = (state, {capabilityKey, role}) => {
    const existingRoleIndex = state.capabilities[capabilityKey].roles.findIndex((aRole) => role == aRole)

    if (existingRoleIndex < 0)
        state.capabilities[capabilityKey].roles.push(role)
};

export const removeCapabilityFromRole = (state, {capabilityKey, role}) => {
    const existingRoleIndex = state.capabilities[capabilityKey].roles.findIndex((aRole) => role == aRole)

    if (existingRoleIndex >= 0)
        state.capabilities[capabilityKey].splice(existingRoleIndex, 1)
};

export const setRoles = (state, roles) => {
    state.roles = roles;
};

// CAPABILITIES
export const setCapabilities = (state, capabilities) => {
    state.capabilities = capabilities;
};

export const setCapability = (state, capability) => {
    state.capability = capability;
};
