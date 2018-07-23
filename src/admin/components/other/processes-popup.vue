<template>
    <div class="processes-popup">
        <div class="popup-header">
            <span 
                    @click="showProcessesList = !showProcessesList"
                    class="header-title">{{ getUnfinishedProcesses() + ' ' + $i18n.get('info_unfinished_processes') }}</span>
            <a @click="showProcessesList = !showProcessesList">
                <span class="icon has-text-tertiary">
                    <i 
                            :class="{ 'mdi-menu-up': showProcessesList,  
                                      'mdi-menu-down': !showProcessesList }"
                            class="mdi mdi-18px"/>
                </span>
            </a>    
            <a @click="$emit('closeProcessesPopup')">
                <span class="icon has-text-tertiary">
                    <i class="mdi mdi-close"/>
                </span>
            </a>       
        </div>
        <div 
                v-if="showProcessesList"
                class="popup-list">
            <ul>
                <li class="popup-list-subheader">
                    {{ $i18n.get('label_last_processed_on') + ' ' + getDate(bgProcesses[0].processed_last) }}
                    <router-link 
                            tag="a"
                            :to="$routerHelper.getProcessesPage()"
                            class="is-secondary">
                        {{ $i18n.get('label_see_more') }}
                    </router-link>
                </li>
                <li     
                        :key="index"
                        v-for="(bgProcess, index) of bgProcesses">
                    <div class="process-item">
                        <div 
                                @click="toggleDetails(index)"
                                class="process-title">
                            <span class="icon has-text-gray">
                                <i 
                                        class="mdi mdi-18px"
                                        :class="{ 'mdi-menu-down': processesColapses[index], 'mdi-menu-right': !processesColapses[index] }" />
                            </span>  
                            <p>{{ bgProcess.name ? bgProcess.name : $i18n.get('label_unamed_process') }}</p>
                        </div>
                        <span 
                                v-if="bgProcess.done <= 0"
                                class="icon has-text-gray action-icon"
                                @click="pauseProcess(index)">
                            <i class="mdi mdi-18px mdi-pause-circle"/>
                        </span>
                        <span 
                                v-if="bgProcess.done <= 0"
                                class="icon has-text-gray action-icon"
                                @click="pauseProcess(index)">
                            <i class="mdi mdi-18px mdi-close-circle-outline"/>
                        </span>
                        <span 
                                v-if="bgProcess.done > 0"
                                class="icon has-text-success">
                            <i class="mdi mdi-18px mdi-checkbox-marked-circle"/>
                        </span>
                        <span 
                                v-if="bgProcess.done <= 0"
                                class="icon has-text-success loading-icon">
                            <div class="control has-icons-right is-loading is-clearfix" />
                        </span>
                    </div>
                    <div 
                            v-if="processesColapses[index]"
                            class="process-label">
                        {{ bgProcess.progress_label ? bgProcess.progress_label : $i18n.get('label_no_details_of_process') }}
                        <br>
                        {{ $i18n.get('label_queued_on') + ' ' + getDate(bgProcess.queued_on) }}
                        <br>
                        {{ $i18n.get('label_last_processed_on') + ' ' + getDate(bgProcess.processed_last) }}
                    </div>
                </li>
            </ul>
        </div>
        <div   
                class="separator"
                v-if="!showProcessesList" />
        <div class="popup-footer">
            <span 
                    v-if="hasAnyProcessExecuting"
                    class="icon has-text-tertiary"><i class="mdi mdi-18px mdi-autorenew"/></span>
            <p class="footer-title">    
                {{ hasAnyProcessExecuting ? 
                    (bgProcesses[0].progress_label ? bgProcesses[0].progress_label + ((bgProcesses[0].progress_value && bgProcesses[0].progress_value >= 0) ? ' - ' + bgProcesses[0].progress_value : '') : $i18n.get('label_no_details_of_process')): 
                    $i18n.get('info_no_process') 
                }}
            </p>
        </div>
    </div>
</template>

<script>
import { mapGetters, mapActions } from 'vuex';

export default {
    name: 'ProcessesPopup',
    data() {
        return {
            showProcessesList: false,
            processesColapses: [],
            hasAnyProcessExecuting: false
        }
    },
    watch: {
        bgProcesses() {
            this.processesColapses = [];
            this.hasAnyProcessExecuting = false;

            for (let i = 0; i < this.bgProcesses.length; i++) {
                this.$set(this.processesColapses, i , false);
                if (this.bgProcesses[i].done <= 0)
                    this.hasAnyProcessExecuting = true;
            }
        }
    },
    computed: {
        bgProcesses() {
            return this.getProcesses();
        }
    },
    methods: {
        ...mapActions('bgprocess', [
            'fetchProcesses'
        ]),
        ...mapGetters('bgprocess', [
            'getProcesses',
        ]),
        toggleDetails(index) {
            this.$set(this.processesColapses, index, !this.processesColapses[index]);
        },
        getUnfinishedProcesses() {
            let nUnfinishedProcesses = 0
            for(let i = 0; i < this.bgProcesses.length; i++) {
                if (this.bgProcesses[i].done <= 0)
                    nUnfinishedProcesses++;
            }
            return nUnfinishedProcesses;
        },
        getDate(rawDate) {
            let date = new Date(rawDate);

            if (date instanceof Date && !isNaN(date))
                return date.toLocaleString();
            else   
                return this.$i18n.get('info_unknown_date');
        },
        pauseProcess() { 
        }
    },
    mounted() {    
        this.fetchProcesses({ page: 1, processesPerPage: 12 });
        this.showProcessesList = false;
    }
}
</script>

<style lang="scss">
    @import "../../scss/_variables.scss";

    @keyframes appear {
        from { 
            top: 24px;
            opacity: 0; 
        }
        to { 
            top: 48px;
            opacity: 1; 
        }
    }

    @keyframes expand {
        from { 
            max-height: 0; 
        }
        to { 
            max-height: 222px; 
        }
    }

    .control.is-loading::after {
        border: 2px solid $success;
        border-right-color: $tainacan-input-background;
        border-top-color: $tainacan-input-background;
    }

    .processes-popup{
        background-color: #c1dae0;
        width: 320px;
        max-width: 100%;
        position: absolute;
        top: 48px;
        border-radius: 5px;
        animation-name: appear;
        animation-duration: 0.3s;
        font-size: 0.75rem;

        .popup-header, .popup-footer {
            display: flex;
            align-items: center;
            color: $tertiary;
            .header-title, .footer-title {
                margin-right: auto;
            }
        }

        .popup-header { 
            padding: 6px 12px 4px 12px; 
            .header-title {
                margin-right: auto;
                cursor: pointer;
            }
        }
        .popup-footer { 
            padding: 4px 12px 6px 10px; 
            .footer-title { 
                margin-right: auto;
                font-size: 0.625rem;
            }
        }

        .popup-list {
            background-color: white;
            color: black;
            overflow-y: scroll;
            overflow-x: hidden;
            max-height: 222px; 
            animation-name: expand;
            animation-duration: 0.3s;

            .popup-list-subheader {
                background-color: white !important;
                padding: 6px 12px 12px 12px;
                color: $gray-light;
                font-size: 0.625rem;
                a { float: right; }
            }

            li:hover {
                background-color: $tainacan-input-background;

                .action-icon{
                    visibility: visible;
                    opacity: 1;
                    cursor: pointer;
                }
                .loading-icon {
                    display: none;
                }
                .process-item>.process-title .mdi-menu-left, .process-item>.process-title .mdi-menu-right {
                    color: $gray !important;
                }
            }

            .process-item {
                padding: 5px 12px 5px 5px;
                display: flex;
                justify-content: space-between;
                width: 100%;

                .process-title {
                    cursor: pointer;
                    margin-right: auto;
                    white-space: nowrap;
                    text-overflow: ellipsis;
                    overflow: hidden;
                    max-width: calc(100% - 40px);

                    p {
                        display: inline-block;
                        position: relative;
                        top: -2px;
                    }
                    
                    .mdi-menu-left, .mdi-menu-right {
                        color: $primary-light;
                    }
                }
                .action-icon {
                    visibility: hidden;
                    opacity: 0;
                }
                .loading-icon .control.is-loading::after {
                    position: relative !important;
                    right: 0;
                    top: 0;
                }
            }
            .process-label {
                padding: 0px 12px 6px 32px;
                margin-right: auto;
                white-space: nowrap;
                text-overflow: ellipsis;
                overflow: hidden;
                max-width: calc(100% - 40px);
                font-size: 0.625rem;
                color: $gray-light;
                animation-name: expand;
                animation-duration: 0.3s;
            }
            
        }

        &:before {
            content: "";
            display: block;
            position: absolute;
            right: 47px;
            width: 0;
            height: 0;
            border-style: solid;
        }
        &:before {
            border-color: transparent transparent $primary-light transparent;
            border-right-width: 8px;
            border-bottom-width: 8px;
            border-left-width: 8px;
            top: -10px;
        }

        .separator {
            margin: 0px 10px;
            height: 1px;
            background-color: $secondary; 
        }
    }

</style>