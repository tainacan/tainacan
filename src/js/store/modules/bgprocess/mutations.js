import Vue from 'vue';

export const setProcesses = ( state, processes ) => {
    state.bg_processes = processes;
}

export const cleanProcesses = ( state ) => {
    state.bg_processes = [];
}

export const setProcess = ( state, process ) => {
    let index = state.bg_processes.findIndex(newProcess => newProcess.id === process.id);
    if ( index >= 0){
        Vue.set( state.bg_processes, index, process );
    } else {
        state.bg_processes.push( process );
    }
    state.bg_process = process;
}

export const setProcessLog = ( state, log ) => {
    state.log = log;
}

export const setProcessErrorLog = ( state, errorLog ) => {
    state.error_log = errorLog;
}

