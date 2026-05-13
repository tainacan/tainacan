/**
 * Reads derived state from `capability/getRoles`.
 * @param {Object} state - Module state.
 * @returns {*} Getter result.
 */
export const getRoles = state => {
  return state.roles;
};

/**
 * Reads derived state from `capability/getRole`.
 * @param {Object} state - Module state.
 * @returns {*} Getter result.
 */
export const getRole = state => {
  return state.role;
};

/**
 * Reads derived state from `capability/getCapability`.
 * @param {Object} state - Module state.
 * @returns {*} Getter result.
 */
export const getCapability = state => {
  return state.capability;
};

/**
 * Reads derived state from `capability/getCapabilities`.
 * @param {Object} state - Module state.
 * @returns {*} Getter result.
 */
export const getCapabilities = state => {
  return state.capabilities;
};

/**
 * Reads derived state from `capability/getAdminUIOptions`.
 * @param {Object} state - Module state.
 * @returns {*} Getter result.
 */
export const getAdminUIOptions = state => {
  return state.adminUIOptions;
}
