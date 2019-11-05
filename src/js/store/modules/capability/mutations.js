import Vue from 'vue';

// CAPABILITIES
export const setCapability = (state, capability) => {
    state.capability = capability;
};

export const associateCapabilityWithRole = (state, {capabilityKey, role}) => {
    const existingRoleIndex = state.capabilities[capabilityKey].roles.findIndex((aRole) => role == aRole)

    if (existingRoleIndex < 0)
        state.capabilities[capabilityKey].roles.push(role)
};

export const disassociateCapabilityWithRole = (state, {capabilityKey, role}) => {
    const existingRoleIndex = state.capabilities[capabilityKey].roles.findIndex((aRole) => role == aRole)

    if (existingRoleIndex >= 0)
        state.capabilities[capabilityKey].splice(existingRoleIndex, 1)
};

export const setCapabilities = (state, capabilities) => {
    state.capabilities = capabilities;
};

export const setCapabilityName = (state, name) => {
    state.capabilityName = name;
};