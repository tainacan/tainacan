<template>
<div>
    <div 
            @click="$emit('closeProcessesPopup')"
            class="processes-popup-backdrop" />
    <div class="processes-popup">
        <div class="popup-header">
            <span 
                    @click="bgProcesses.length > 0 ? showProcessesList = !showProcessesList : null"
                    class="header-title">{{ getUnfinishedProcesses() + ' ' + $i18n.get('info_unfinished_processes') }}</span>
            <a 
                    v-if="bgProcesses.length > 0"
                    @click="showProcessesList = !showProcessesList">
                <span class="icon has-text-blue5">
                    <i 
                            :class="{ 'tainacan-icon-arrowup': showProcessesList,  
                                      'tainacan-icon-arrowdown': !showProcessesList }"
                            class="tainacan-icon tainacan-icon-18px"/>
                </span>
            </a>    
            <a @click="$emit('closeProcessesPopup')">
                <span class="icon has-text-blue5">
                    <i class="tainacan-icon tainacan-icon-close"/>
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
                        {{ $i18n.get('label_view_more') }}
                    </router-link>
                </li>
                <li     
                        :key="index"
                        v-for="(bgProcess, index) of getAllProcesses">
                    <div class="process-item">
                        <div 
                                @click="toggleDetails(index)"
                                class="process-title">
                            <span class="icon has-text-gray">
                                <i 
                                        class="tainacan-icon tainacan-icon-18px"
                                        :class="{ 'tainacan-icon-arrowdown': processesCollapses[index], 'tainacan-icon-arrowright': !processesCollapses[index] }" />
                            </span>  
                            <p>{{ bgProcess.name ? bgProcess.name : $i18n.get('label_unamed_process') }}</p>
                        </div>
                        <!-- <span 
                                v-if="bgProcess.done <= 0 && bgProcess.status == 'closed'"
                                class="icon has-text-gray action-icon"
                                @click="resumeProcess(index)">
                            <i class="tainacan-icon tainacan-icon-18px tainacan-icon-play-circle"/>
                        </span> -->
                        <span 
                                v-if="bgProcess.status === 'running'"
                                class="icon has-text-gray action-icon"
                                @click="pauseProcess(index)">
                            <i class="tainacan-icon tainacan-icon-18px tainacan-icon-stop"/>
                        </span>
                        <span 
                                v-if="bgProcess.status === 'finished-errors'"
                                class="icon has-text-success">
                            <i
                                style="margin-right: -5px;"
                                class="tainacan-icon tainacan-icon-20px tainacan-icon-alert has-text-yellow2"/>
                            <i class="tainacan-icon tainacan-icon-18px tainacan-icon-approvedcircle"/>
                        </span>
                        <span 
                                v-if="bgProcess.status === 'finished' || bgProcess.status === null"
                                class="icon has-text-success">
                            <i class="tainacan-icon tainacan-icon-18px tainacan-icon-approvedcircle"/>
                        </span>
                        <span 
                                v-if="bgProcess.status === 'errored'"
                                class="icon has-text-danger">
                            <i class="tainacan-icon tainacan-icon-18px tainacan-icon-processerror" />
                        </span>
                        <span 
                                v-if="bgProcess.status === 'cancelled'"
                                class="icon has-text-danger">
                            <i class="tainacan-icon tainacan-icon-18px tainacan-icon-repprovedcircle" />
                        </span>
                        <span 
                                v-if="bgProcess.status === 'paused'"
                                class="icon has-text-gray">
                            <i class="tainacan-icon tainacan-icon-18px tainacan-icon-pause" />
                        </span>
                        <span 
                                v-if="bgProcess.status === 'waiting'"
                                class="icon has-text-gray">
                            <i class="tainacan-icon tainacan-icon-18px tainacan-icon-waiting" />
                        </span>
                        <span 
                                v-if="bgProcess.status === 'running'"
                                class="icon has-text-success loading-icon">
                            <!--<progress-->
                                    <!--:value="bgProcess.progress_value > 0 ? bgProcess.progress_value : 0"-->
                                    <!--max="100"-->
                                    <!--class="progress is-success is-small is-loading">-->
                                <!--{{ `(${ bgProcess.progress_value &lt;&equals; 0 ? 0 : bgProcess.progress_value }%)` }}-->
                            <!--</progress>-->
                            <div class="control has-icons-right is-loading is-clearfix" />
                        </span>
                    </div>
                    <div 
                            v-if="processesCollapses[index]"
                            class="process-label">
                        {{ bgProcess.progress_label ? bgProcess.progress_label : $i18n.get('label_no_details_of_process') }}
                        <span class="process-label-value">{{ (bgProcess.progress_value && bgProcess.progress_value >= 0) ? '(' + bgProcess.progress_value + '%)' : '' }}</span>
                        <br>
                        {{ $i18n.get('label_queued_on') + ' ' + getDate(bgProcess.queued_on) }}
                        <br>
                        {{ $i18n.get('label_last_processed_on') + ' ' + getDate(bgProcess.processed_last) }}
                        <br>
                        <a 
                                v-if="bgProcess.log"
                                :href="bgProcess.log">{{ $i18n.get('label_log_file') }}</a>
                        <span v-if="bgProcess.error_log"> | </span>
                        <a 
                                v-if="bgProcess.error_log"
                                class="has-text-danger"
                                :href="bgProcess.error_log">{{ $i18n.get('label_error_log_file') }}</a>
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
                    class="icon has-text-blue5"><i class="tainacan-icon tainacan-icon-18px tainacan-icon-updating"/></span>
            <p class="footer-title">    
                {{ hasAnyProcessExecuting ? 
                    (bgProcesses[0].progress_label ? bgProcesses[0].progress_label + ((bgProcesses[0].progress_value && bgProcesses[0].progress_value >= 0) ? ' - ' + bgProcesses[0].progress_value + '%' : '') : $i18n.get('label_no_details_of_process')):
                    $i18n.get('info_no_process') 
                }}
            </p>
        </div>
    </div>
</div>
</template>

<script>
import { mapActions } from 'vuex';
import moment from 'moment';

export default {
    name: 'ProcessesPopup',
    data() {
        return {
            showProcessesList: false,
            updatedProcesses: [],
            bgProcesses: [],
            processesCollapses: [],
            hasAnyProcessExecuting: false,
            dateFormat: ''
        }
    },
    watch: {
        bgProcesses(newBG) {
            this.hasAnyProcessExecuting = newBG.some((element) => element.done <= 0);
        }
    },
    computed: {
        getAllProcesses(){
            if (this.updatedProcesses.length !== 0) {
                for (let updatedProcess of this.updatedProcesses) {
                    let updatedProcessIndex = this.bgProcesses.findIndex((aProcess) => aProcess.ID == updatedProcess.ID);
                    if (updatedProcessIndex >= 0) {
                        this.$set(this.bgProcesses, updatedProcessIndex, updatedProcess);
                    }
                }
            }

            return this.bgProcesses;
        }
    },
    methods: {
        ...mapActions('bgprocess', [
            'fetchProcesses',
            'updateProcess'
        ]),
        toggleDetails(index) {
            this.$set(this.processesCollapses, index, !this.processesCollapses[index]);
        },
        getUnfinishedProcesses() {
            let nUnfinishedProcesses = 0;

            for(let i = 0; i < this.bgProcesses.length; i++) {
                if (this.bgProcesses[i].done <= 0){
                    nUnfinishedProcesses++;
                }
            }

            return nUnfinishedProcesses;
        },
        getDate(rawDate) {
            let date = moment(rawDate).format(this.dateFormat);

            if (date != 'Invalid date') {
                return date;
            } else {
                return this.$i18n.get('info_unknown_date');
            }
        },
        pauseProcess(index) {
            this.updateProcess({ id: this.bgProcesses[index].ID, status: 'closed' });
        },
        setProcesses(processes) {
            this.updatedProcesses = processes;
        }
    },
    created() {
        let locale = navigator.language;

        moment.locale(locale);

        let localeData = moment.localeData();
        this.dateFormat = localeData.longDateFormat('lll');

        this.fetchProcesses({
            page: 1,
            processesPerPage: 12,
            shouldUpdateStore: false
        }).then((response) => {
            this.bgProcesses = JSON.parse(JSON.stringify(response.processes));
        });

        this.showProcessesList = false;

        jQuery( document ).on( 'heartbeat-tick-popup',  ( event, data ) => {
            this.setProcesses(data.bg_process_feedback);
        });

        jQuery( document ).on( 'heartbeat-tick',  ( event, data ) => {
            jQuery( document ).trigger('heartbeat-tick-popup',data);
        });


    },
    beforeDestroy() {
        jQuery( document ).unbind( 'heartbeat-tick-popup')
    }
}
</script>

<style lang="scss">
    @import "../../scss/_variables.scss";

    .control.is-loading::after {
        border: 2px solid $success;
        border-right-color: $gray2;
        border-top-color: $gray2;
    }
    .processes-popup-backdrop {
        position: absolute;
        top: 0;
        right: 0;
        left: 0;
        border: 0;
        width: 100%;
        height: 100vh;
        right: 26px;
    }

    @media screen and (max-width: 768px) {
        .processes-popup {
            right: 27px !important;
        }
    }

    // For iPhone SE
    @media screen and (max-width: 320px) {
        .processes-popup {
            right: 20px !important;
            width: 280px !important;
        }
    }

    .processes-popup{
        background-color: $blue2;
        width: 320px;
        max-width: 100%;
        position: absolute;
        top: 48px;
        right: 40px;
        border-radius: 5px;
        animation-name: appear-from-top;
        animation-duration: 0.3s;
        font-size: 0.75rem;

        .popup-header, .popup-footer {
            display: flex;
            align-items: center;
            color: $blue5;
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
            min-height: 29px;
            .footer-title { 
                margin-right: auto;
                font-size: 0.625rem;
            }
        }

        .popup-list {
            background-color: white;
            color: black;
            overflow-y: auto;
            overflow-x: hidden;
            max-height: 222px; 
            animation-name: expand;
            animation-duration: 0.3s;

            .popup-list-subheader {
                background-color: white !important;
                padding: 6px 12px 12px 12px;
                color: $gray4;
                font-size: 0.625rem;
                a { float: right; }
            }

            li:hover {
                background-color: $gray0;

                .action-icon{
                    visibility: visible;
                    opacity: 1;
                    cursor: pointer;
                }
                /*.loading-icon {*/
                    /*display: none;*/
                /*}*/
                .process-item>.process-title .tainacan-arrowleft, .process-item>.process-title .tainacan-arrowright {
                    color: $gray3 !important;
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
                        top: 1px;
                    }
                    
                    .tainacan-arrowleft, .tainacan-arrowright {
                        color: $turquoise2;
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
                color: $gray4;
                animation-name: expand;
                animation-duration: 0.3s;
            }
            span.process-label-value {
                font-style: italic;
                font-weight: bold;
            }
            
        }

        &:before {
            content: "";
            display: block;
            position: absolute;
            right: 35px;
            width: 0;
            height: 0;
            border-style: solid;
            border-color: transparent transparent $blue2 transparent;
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