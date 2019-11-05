import Vue from 'vue';

// CAPABILITIES
export const setCapability = (state, capability) => {
    state.capability = capability;
};

export const setCapabilities = (state, capabilities) => {
    state.capabilities = capabilities;
};

export const setCapabilityName = (state, name) => {
    state.capabilityName = name;
};