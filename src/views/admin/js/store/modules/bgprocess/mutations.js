import Vue from 'vue';

export const setProcesses = ( state, processes ) => {
    state.bg_processes = processes;
}

export const cleanProcesses = ( state ) => {
    state.bg_processes = [];
}

export const setProcess = ( state, bgProcess ) => {
    let index = state.bg_processes.findIndex(newProcess => newProcess.ID == bgProcess.ID);
    if ( index >= 0){
        Vue.set(state.bg_processes, index, bgProcess);
    } else {
        state.bg_processes.push(bgProcess);
    }
    state.bg_process = bgProcess;
}

export const setProcessLog = ( state, log ) => {
    state.log = log;
}

export const setProcessErrorLog = ( state, errorLog ) => {
    state.error_log = errorLog;
}

