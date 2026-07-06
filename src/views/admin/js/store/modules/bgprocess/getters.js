/**
 * Reads derived state from `bgprocess/getProcesses`.
 * @param {Object} state - Module state.
 * @returns {*} Getter result.
 */
export const getProcesses =  state => {
    return state.bg_processes;
}

/**
 * Reads derived state from `bgprocess/getProcess`.
 * @param {Object} state - Module state.
 * @returns {*} Getter result.
 */
export const getProcess =  state => {
    return state.bg_process;
}

/**
 * Reads derived state from `bgprocess/getErrorLog`.
 * @param {Object} state - Module state.
 * @returns {*} Getter result.
 */
export const getErrorLog =  state => {
    return state.error_log;
}

/**
 * Reads derived state from `bgprocess/getLog`.
 * @param {Object} state - Module state.
 * @returns {*} Getter result.
 */
export const getLog =  state => {
    return state.log;
}


