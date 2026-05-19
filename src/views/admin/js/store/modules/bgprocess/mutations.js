

/**
 * Commits `bgprocess/setProcesses` state changes.
 * @param {Object} state - Module state.
 * @param {*} processes - Mutation payload.
 * @returns {void} No return value.
 */
export const setProcesses = ( state, processes ) => {
    state.bg_processes = processes;
}

/**
 * Commits `bgprocess/cleanProcesses` state changes.
 * @param {Object} state - Module state.
 * @returns {void} No return value.
 */
export const cleanProcesses = ( state ) => {
    state.bg_processes = [];
}

/**
 * Commits `bgprocess/setProcess` state changes.
 * @param {Object} state - Module state.
 * @param {*} bgProcess - Mutation payload.
 * @returns {void} No return value.
 */
export const setProcess = ( state, bgProcess ) => {
    let index = state.bg_processes.findIndex(newProcess => newProcess.ID == bgProcess.ID);
    if ( index >= 0){
        Object.assign(state.bg_processes, { [index]: bgProcess });
    } else {
        state.bg_processes.push(bgProcess);
    }
    state.bg_process = bgProcess;
}

/**
 * Commits `bgprocess/deleteProcess` state changes.
 * @param {Object} state - Module state.
 * @param {*} bgProcess - Mutation payload.
 * @returns {void} No return value.
 */
export const deleteProcess = ( state, bgProcess ) => {
    let index = state.bg_processes.findIndex(newProcess => newProcess.ID == bgProcess.id);
    if ( index >= 0){
        state.bg_processes.splice(index, 1);
    }
}

/**
 * Commits `bgprocess/setProcessLog` state changes.
 * @param {Object} state - Module state.
 * @param {*} log - Mutation payload.
 * @returns {void} No return value.
 */
export const setProcessLog = ( state, log ) => {
    state.log = log;
}

/**
 * Commits `bgprocess/setProcessErrorLog` state changes.
 * @param {Object} state - Module state.
 * @param {*} errorLog - Mutation payload.
 * @returns {void} No return value.
 */
export const setProcessErrorLog = ( state, errorLog ) => {
    state.error_log = errorLog;
}
