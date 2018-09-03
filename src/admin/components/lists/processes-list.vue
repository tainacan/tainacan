<template>
    <div 
            v-if="processes.length > 0 && !isLoading"
            class="table-container">
<!--
        <div class="selection-control">
            <div class="field select-all is-pulled-left">
                <span>
                    <b-checkbox 
                            @click.native="selectAllOnPage()" 
                            :value="allOnPageSelected">{{ $i18n.get('label_select_all_processes_page') }}</b-checkbox>
                </span>
            </div>
            <div class="field is-pulled-right">
                <b-dropdown
                        position="is-bottom-left"
                        :disabled="!isSelecting"
                        id="bulk-actions-dropdown">
                    <button
                            class="button is-white"
                            slot="trigger">
                        <span>{{ $i18n.get('label_bulk_actions') }}</span>
                        <b-icon icon="menu-down"/>
                    </button> 

                    <b-dropdown-item
                            id="item-delete-selected-items"
                            @click="deleteSelected()">
                        {{ $i18n.get('label_delete_selected_processes') }}
                    </b-dropdown-item>
                    <b-dropdown-item disabled>{{ $i18n.get('label_edit_selected_processes') + ' (Not ready)' }}
                    </b-dropdown-item>
                </b-dropdown>
            </div>
        </div>
-->
        <div class="table-wrapper">
            <table class="tainacan-table is-narrow">
                <thead>
                    <tr>
                        <!-- Checking list -->
                        <!-- <th> -->
                            <!-- &nbsp; -->
                            <!-- nothing to show on header -->
                        <!-- </th> -->
                        <!-- Name -->
                        <th>
                            <div class="th-wrap">{{ $i18n.get('label_name') }}</div>
                        </th>
                        <!-- Progress -->
                        <th>
                            <div class="th-wrap">{{ $i18n.get('label_progress') }}</div>
                        </th>
                        <!-- Queued on -->
                        <th>
                            <div class="th-wrap">{{ $i18n.get('label_queued_on') }}</div>
                        </th>
                        <!-- Last Processed on -->
                        <th>
                            <div class="th-wrap">{{ $i18n.get('label_last_processed_on') }}</div>
                        </th>
                        <!-- Logs -->
                        <th>
                            <div class="th-wrap">{{ $i18n.get('label_log_file') }}</div>
                        </th>
                        <!-- Status -->
                        <th>
                            <div class="th-wrap">{{ $i18n.get('label_status') }}</div>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr     
                            :class="{ 'selected-row': selected[index], 'highlighted-process': highlightedProcess == bgProcess.ID }"
                            :key="index"
                            v-for="(bgProcess, index) of processes">
                        <!-- Checking list -->
                        <!-- <td 
                                :class="{ 'is-selecting': isSelecting }"
                                class="checkbox-cell">
                            <b-checkbox 
                                    size="is-small"
                                    v-model="selected[index]"/> 
                        </td> -->
                        <!-- Name -->
                        <td 
                                class="column-default-width column-main-content"
                                :label="$i18n.get('label_name')" 
                                :aria-label="$i18n.get('label_name') + ': ' + bgProcess.name">
                            <p
                                    v-tooltip="{
                                        content: bgProcess.name ? bgProcess.name : $i18n.get('label_unamed_process'),
                                        autoHide: false,
                                        placement: 'auto-start'
                                    }">
                                {{ bgProcess.name ? bgProcess.name : $i18n.get('label_unamed_process') }}</p>
                        </td>
                        <!-- Progress -->
                        <td 
                                class="column-default-width"
                                :label="$i18n.get('label_progress')" 
                                :aria-label="$i18n.get('label_progress') + ': ' + bgProcess.progress_label ? bgProcess.progress_label + (bgProcess.progress_value ? ' (' + bgProcess.progress_value + '%)' : '') : $i18n.get('label_no_details_of_process')">
                            <p
                                    v-tooltip="{
                                        content: bgProcess.progress_label ? bgProcess.progress_label : $i18n.get('label_no_details_of_process'),
                                        autoHide: false,
                                        placement: 'auto-start'
                                    }">
                                <span :class="{'occluding-content': bgProcess.progress_value }">{{ bgProcess.progress_label ? bgProcess.progress_label : $i18n.get('label_no_details_of_process') }}</span>
                                <span>{{ bgProcess.progress_value ? ' (' + bgProcess.progress_value + '%)' : '' }}</span>
                            </p>
                        </td>
                        <!-- Queued on -->
                        <td 
                                class="column-small-width"
                                :label="$i18n.get('label_queued_on')" 
                                :aria-label="$i18n.get('label_queued_on') + ' ' + getDate(bgProcess.queued_on)">
                            <p
                                    v-tooltip="{
                                        content: getDate(bgProcess.queued_on),
                                        autoHide: false,
                                        placement: 'auto-start'
                                    }">
                                {{ getDate(bgProcess.queued_on) }}</p>
                        </td>
                        <!-- Last processed on -->
                        <td 
                                class="column-small-width"
                                :label="$i18n.get('label_last_processed_on')" 
                                :aria-label="$i18n.get('label_last_processed_on') + ' ' + getDate(bgProcess.processed_last)">
                            <p
                                    v-tooltip="{
                                        content: getDate(bgProcess.processed_last),
                                        autoHide: false,
                                        placement: 'auto-start'
                                    }">
                                {{ getDate(bgProcess.processed_last) }}</p>
                        </td>
                        <!-- Logs -->
                        <td 
                                class="column-needed-width"
                                :label="$i18n.get('label_log_file')" 
                                :aria-label="$i18n.get('label_log_gile')">
                            <p>
                                <a 
                                        v-if="bgProcess.log"
                                        :href="bgProcess.log">{{ $i18n.get('label_log_file') }}</a>
                                <span v-if="bgProcess.error_log"> | </span>
                                <a 
                                        v-if="bgProcess.error_log"
                                        class="has-text-danger"
                                        :href="bgProcess.error_log">{{ $i18n.get('label_error_log_file') }}</a>
                            </p>
                        </td>
                        <!-- Status-->
                        <td 
                                class="actions-cell column-small-width" 
                                :label="$i18n.get('label_status')">
                            <div class="actions-container">
                                <span 
                                        v-if="bgProcess.done <= 0"
                                        class="icon has-text-success loading-icon">
                                    <div class="control has-icons-right is-loading is-clearfix" />
                                </span>
                                <!-- <span
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
                                </span> -->
                                <span 
                                        v-tooltip="{
                                            content: $i18n.get('label_process_completed'),
                                            autoHide: false,
                                            placement: 'auto-start'
                                        }"
                                        v-if="bgProcess.done > 0 && !bgProcess.error_log"
                                        class="icon has-text-success">
                                    <i class="mdi mdi-18px mdi-checkbox-marked-circle"/>
                                </span>
                                <span 
                                        v-tooltip="{
                                            content: $i18n.get('label_process_failed'),
                                            autoHide: false,
                                            placement: 'auto-start'
                                        }"
                                        v-if="bgProcess.done > 0 && bgProcess.error_log"
                                        class="icon has-text-danger">
                                    <i class="mdi mdi-18px mdi-sync-alert" />
                                </span>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

<script>
    import { mapActions } from 'vuex';
    import CustomDialog from '../other/custom-dialog.vue';

    export default {
        name: 'List',
        data() {
            return {
                selected: [],
                allOnPageSelected: false,
                isSelecting: false,
                highlightedProcess: ''
            }
        },
        props: {
            isLoading: false,
            total: 0,
            page: 1,
            processesPerPage: 12,
            processes: Array
        },
        watch: {
            processes() {
                this.selected = [];
                for (let i = 0; i < this.processes.length; i++)
                    this.selected.push(false);    
            },
            selected() {
                let allSelected = true;
                let isSelecting = false;
                for (let i = 0; i < this.selected.length; i++) {
                    if (this.selected[i] == false) {
                        allSelected = false;
                    } else {
                        isSelecting = true;
                    }
                }
                this.allOnPageSelected = allSelected;
                this.isSelecting = isSelecting;
            }
        },
        methods: {
            ...mapActions('bgprocess', [
                'deleteProcess'
            ]),
            selectAllOnPage() {
                for (let i = 0; i < this.selected.length; i++) 
                    this.selected.splice(i, 1, !this.allOnPageSelected);
            },
            deleteOneProcess(processId) {
                this.$modal.open({
                    parent: this,
                    component: CustomDialog,
                    props: {
                        icon: 'alert',
                        title: this.$i18n.get('label_warning'),
                        message: this.$i18n.get('info_warning_Process_delete'),
                        onConfirm: () => {
                            this.deleteProcess({ processId: processId })
                                .then(() => {
                                    // this.$toast.open({
                                    //     duration: 3000,
                                    //     message: this.$i18n.get('info_taxonomy_deleted'),
                                    //     position: 'is-bottom',
                                    //     type: 'is-secondary',
                                    //     queue: true
                                    // });
                                    for (let i = 0; i < this.selected.length; i++) {
                                        if (this.selected[i].id === this.taxonomyId)
                                            this.selected.splice(i, 1);
                                    }
                                })
                                .catch(() => {
                                    // this.$toast.open({
                                    //     duration: 3000,
                                    //     message: this.$i18n.get('info_error_deleting_taxonomy'),
                                    //     position: 'is-bottom',
                                    //     type: 'is-danger',
                                    //     queue: true
                                    // });
                                });
                        }
                    }
                });
            },
            deleteSelected() {
                this.$modal.open({
                    parent: this,
                    component: CustomDialog,
                    props: {
                        icon: 'alert',
                        title: this.$i18n.get('label_warning'),
                        message: this.$i18n.get('info_warning_selected_processes_delete'),
                        onConfirm: () => {

                            for (let i = 0; i < this.processes.length;  i++) {
                                if (this.selected[i]) {
                                    this.deleteTaxonomy({ processId: this.processes[i].id })
                                        .then(() => {
                                            // this.load();
                                            // this.$toast.open({
                                            //     duration: 3000,
                                            //     message: this.$i18n.get('info_taxonomy_deleted'),
                                            //     position: 'is-bottom',
                                            //     type: 'is-secondary',
                                            //     queue: false
                                            // })
                                        }).catch(() => {
                                        // this.$toast.open({
                                        //     duration: 3000,
                                        //     message: this.$i18n.get('info_error_deleting_taxonomy'),
                                        //     position: 'is-bottom',
                                        //     type: 'is-danger',
                                        //     queue: false
                                        // });
                                    });
                                }
                            }
                            this.allOnPageSelected = false;
                        }
                    }
                });
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
            if (this.$route.query.highlight) {
                this.highlightedProcess = this.$route.query.highlight;
            }
        }
    }
</script>

<style lang="scss" scoped>

    @import "../../scss/_variables.scss";

    .selection-control {
        
        padding: 6px 0px 0px 12px;
        background: white;
        height: 40px;

        .select-all {
            color: $gray4;
            font-size: 14px;
            &:hover {
                color: $gray4;
            }
        }
    }

    .loading-icon .control.is-loading::after {
        position: relative !important;
        right: 0;
        top: 0;
    }

    @keyframes highlight {
        from {
            background-color: $blue1; 
        }
        to {
            background-color: $white; 
        }
    }

    .highlighted-process {
        td, .checkbox-cell .checkbox, .actions-cell .actions-container { 
            transition: background-color 0.5s; 
            animation-name: highlight;
            animation-duration: 1s;
            animation-iteration-count: 2;
        }
    }

    .occluding-content {
        width: calc(100% - 40px);
        display: inline-block;
        overflow: hidden;
        text-overflow: ellipsis;
        top: 4px;
        position: relative;
    }

</style>


