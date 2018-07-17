<template>
    <div class="processes-popup">
        <div class="popup-header">
            <span class="header-title">{{ getUnfinishedProcesses() + ' ' + $i18n.get('info_unfinished_processes') }}</span>
            <a @click="showProcessesList = !showProcessesList">
                <span class="icon has-text-tertiary">
                    <i 
                            :class="{ 'mdi-chevron-up': showProcessesList,  
                                      'mdi-chevron-down': !showProcessesList }"
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
                <li>
                    Listing 6 processes
                    <router-link 
                            tag="a"
                            :to="$routerHelper.getProcessesPage()"
                            class="is-secondary">
                        See all
                    </router-link>
                </li>
                <li     
                        :key="index"
                        v-for="(bgProcess, index) of bgProcesses.slice(0,6)">
                    <div class="process-item">
                        <div 
                                @click="toggleDetails(index)"
                                class="process-title">
                            <b-icon
                                    size="is-small"
                                    type="is-gray"
                                    :icon="processesColapses[index] ? 'menu-down' : 'menu-right'" />
                                Name of Process
                        </div>
                        <span 
                                v-if="bgProcess.done <= 0"
                                class="icon has-text-gray"
                                @click="pauseProcess(index)">
                            <i class="mdi mdi-24px mdi-pause"/>
                        </span>
                        <span 
                                v-if="bgProcess.done > 0"
                                class="icon has-text-success">
                            <i class="mdi mdi-24px mdi-checkbox-marked-circle"/>
                        </span>
                        <span 
                                v-if="bgProcess.done <= 0"
                                class="icon has-text-success">
                            <div class="control has-icons-right is-loading is-clearfix" />
                        </span>
                    </div>
                    <div 
                            v-if="processesColapses[index]"
                            class="process-label">
                        {{ bgProcess.progress_label ? bgProcess.progress_label : $i18n.get('label_no_details_of_process') }}
                    </div>
                </li>
            </ul>
        </div>
        <div   
                class="separator"
                v-if="!showProcessesList" />
        <div class="popup-footer">
            <span class="icon has-text-tertiary"><i class="mdi mdi-18px mdi-autorenew"/></span>
            <p class="footer-title">{{ $i18n.get('info_no_process') }}</p>
        </div>
    </div>
</template>

<script>
import { mapGetters, mapActions } from 'vuex';

export default {
    name: 'ProcessesList',
    data() {
        return {
            showProcessesList: false,
            processesColapses: []
        }
    },
    watch: {
        bgProcesses() {
            this.processesColapses = [];
            for (let i = 0; i < this.bgProcesses.length; i++)
                this.$set(this.processesColapses, i , false);
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
                if (this.bgProcesses[i].done > 0)
                    nUnfinishedProcesses++;
            }
            return nUnfinishedProcesses;
        },
        pauseProcess(index) {
            
        }
    },
    mounted() {    
        this.fetchProcesses();
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
            max-height: 800px; 
        }
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
        font-size: 0.875rem;

        .popup-header, .popup-footer {
            display: flex;
            align-items: center;
            color: $tertiary;
            .header-title, .footer-title {
                margin-right: auto;
            }
        }

        .popup-header { padding: 10px 12px 2px 12px; }
        .popup-footer { 
            font-size: 0.75rem;
            padding: 4px 10px 6px 10px; 
        }

        .popup-list {
            background-color: white;
            color: black;
            overflow: hidden;
            height: auto; 
            animation-name: expand;
            animation-duration: 0.3s;

            .process-item {
                padding: 6px 12px 2px 6px;
                display: flex;
                justify-content: space-between;
                cursor: pointer;

                .process-title {
                    margin-right: auto;
                    white-space: nowrap;
                    text-overflow: ellipsis;
                    overflow: hidden;
                    max-width: calc(100% - 40px);
                }

                .control.is-loading::after {
                    position: relative !important;
                    right: 0;
                    top: 0;
                }
            }
            .process-item:hover {
                background-color: $tainacan-input-background;
            }
            .process-label {
                padding: 2px 12px 6px 24px;
                margin-right: auto;
                white-space: nowrap;
                text-overflow: ellipsis;
                overflow: hidden;
                max-width: calc(100% - 40px);
                animation-name: expand;
                animation-duration: 0.3s;
            }
            .process-label {
                font-size: 0.75rem;
                color: $gray-light;
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