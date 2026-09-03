<template>
    <div class="table-container">
        <div
                v-if="processes.length > 0 && !isLoading"
                class="processes-table-wrapper">
            <table class="processes-table">
                <thead>
                    <tr>
                        <th
                                class="col-toggle"
                                :aria-label="$i18n.get('label_view_details')" />
                        <th class="col-type">
                            {{ $i18n.get('label_process_type') }}
                        </th>
                        <th class="col-status">
                            {{ $i18n.get('label_status') }}
                        </th>
                        <th class="col-progress">
                            {{ $i18n.get('label_progress') }}
                        </th>
                        <th class="col-created">
                            {{ $i18n.get('label_queued_on') }}
                        </th>
                        <th class="col-executed">
                            {{ $i18n.get('label_last_processed_on') }}
                        </th>
                        <th
                                class="col-actions"
                                :aria-label="$i18n.get('label_actions')">
                            {{ $i18n.get('label_actions') }}
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <template
                            v-for="(bgProcess, index) of processes"
                            :key="index">
                        <tr
                                :class="{
                                    'highlighted-process': highlightedProcess == bgProcess.ID,
                                    'opened-process': collapses[index]
                                }"
                                @click="Object.assign( collapses, { [index]: !collapses[index] })">
                            <!-- Expand / collapse arrow -->
                            <td class="col-toggle">
                                <span
                                        v-tooltip="{
                                            delay: { show: 500, hide: 300 },
                                            content: $i18n.get('label_view_details'),
                                            autoHide: false,
                                            popperClass: ['tainacan-tooltip', 'tooltip', 'tainacan-repository-tooltip'],
                                            placement: 'auto-start'
                                        }"
                                        class="icon has-text-dark toggle-icon"
                                        tabindex="0"
                                        aria-role="button"
                                        :aria-label="$i18n.get('label_view_details')"
                                        :aria-expanded="collapses[index] ? 'true' : 'false'"
                                        @click.prevent.stop="Object.assign( collapses, { [index]: !collapses[index] })"
                                        @keydown.enter.prevent="Object.assign( collapses, { [index]: !collapses[index] })"
                                        @keydown.space.prevent="Object.assign( collapses, { [index]: !collapses[index] })">
                                    <i
                                            aria-hidden="true"
                                            :class="{ 'tainacan-icon-arrowdown' : collapses[index], 'tainacan-icon-arrowright tainacan-icon-is-rtl-mirrored' : !collapses[index] }"
                                            class="tainacan-icon tainacan-icon-1-25em" />
                                </span>
                            </td>

                            <!-- Process Type -->
                            <td
                                    class="col-type"
                                    :data-label="$i18n.get('label_process_type')">
                                <span
                                        v-tooltip="{
                                            delay: { show: 500, hide: 300 },
                                            content: bgProcess.name ? bgProcess.name : $i18n.get('label_unnamed_process'),
                                            autoHide: false,
                                            popperClass: ['tainacan-tooltip', 'tooltip', 'tainacan-repository-tooltip'],
                                            placement: 'auto-start'
                                        }"
                                        class="process-name">
                                    {{ bgProcess.name ? bgProcess.name : $i18n.get('label_unnamed_process') }}
                                </span>
                            </td>

                            <!-- Status -->
                            <td
                                    class="col-status"
                                    :data-label="$i18n.get('label_status')">
                                <span
                                        class="tag process-status-tag"
                                        :class="getStatusTagClass(bgProcess)">
                                    <span
                                            class="icon is-small"
                                            aria-hidden="true">
                                        <i :class="getStatusIcon(bgProcess)" />
                                    </span>
                                    <span>{{ getStatusLabel(bgProcess) }}</span>
                                </span>
                            </td>

                            <!-- Progress (percentage only; the status label is shown in the Status column) -->
                            <td
                                    class="col-progress"
                                    :data-label="$i18n.get('label_progress')">
                                <div class="progress-cell">
                                    <span
                                            v-if="bgProcess.status === 'running'"
                                            class="icon has-text-success loading-icon"
                                            aria-hidden="true">
                                        <div class="control has-icons-right is-loading is-clearfix" />
                                    </span>
                                    <progress
                                            v-if="bgProcess.progress_value !== null && bgProcess.progress_value !== undefined && bgProcess.status !== 'finished' && bgProcess.status !== 'finished-errors' && bgProcess.status !== 'errored' && bgProcess.status !== 'cancelled'"
                                            class="progress is-small process-bar"
                                            :value="bgProcess.progress_value ? bgProcess.progress_value : 0"
                                            max="100">
                                        {{ bgProcess.progress_value ? bgProcess.progress_value : 0 }}%
                                    </progress>
                                    <span class="progress-value-text">{{ bgProcess.progress_value ? bgProcess.progress_value : 0 }}%</span>
                                </div>
                            </td>

                            <!-- Created Date -->
                            <td
                                    class="col-created"
                                    :data-label="$i18n.get('label_queued_on')">
                                <span
                                        v-tooltip="{
                                            delay: { show: 500, hide: 300 },
                                            content: getDate(bgProcess.queued_on),
                                            autoHide: false,
                                            popperClass: ['tainacan-tooltip', 'tooltip', 'tainacan-repository-tooltip'],
                                            placement: 'auto-start'
                                        }">
                                    {{ getDate(bgProcess.queued_on) }}
                                </span>
                            </td>

                            <!-- Execute Date -->
                            <td
                                    class="col-executed"
                                    :data-label="$i18n.get('label_last_processed_on')"
                                    :class="{ 'has-text-grey-dark': !hasValidDate(bgProcess.processed_last) }">
                                <span
                                        v-tooltip="{
                                            delay: { show: 500, hide: 300 },
                                            content: getDate(bgProcess.processed_last),
                                            autoHide: false,
                                            popperClass: ['tainacan-tooltip', 'tooltip', 'tainacan-repository-tooltip'],
                                            placement: 'auto-start'
                                        }">
                                    {{ getDate(bgProcess.processed_last) }}
                                </span>
                            </td>

                            <!-- Actions -->
                            <td
                                    class="col-actions"
                                    :data-label="$i18n.get('label_actions')">
                                <div class="actions-container">
                                    <!-- Stop (running only) -->
                                    <span
                                            v-if="bgProcess.status === 'running'"
                                            v-tooltip="{
                                                delay: { show: 500, hide: 300 },
                                                content: $i18n.get('label_stop_process'),
                                                autoHide: false,
                                                popperClass: ['tainacan-tooltip', 'tooltip', 'tainacan-repository-tooltip'],
                                                placement: 'auto-start'
                                            }"
                                            class="icon has-text-dark action-icon"
                                            tabindex="0"
                                            aria-role="button"
                                            :aria-label="$i18n.get('label_stop_process')"
                                            @click.prevent.stop="pauseProcess(index)"
                                            @keydown.enter.prevent="pauseProcess(index)"
                                            @keydown.space.prevent="pauseProcess(index)">
                                        <i
                                                aria-hidden="true"
                                                class="tainacan-icon tainacan-icon-1-25em tainacan-icon-stop" />
                                    </span>

                                    <!-- View log file -->
                                    <a
                                            v-if="bgProcess.log"
                                            v-tooltip="{
                                                delay: { show: 500, hide: 300 },
                                                content: $i18n.get('label_log_file'),
                                                autoHide: false,
                                                popperClass: ['tainacan-tooltip', 'tooltip', 'tainacan-repository-tooltip'],
                                                placement: 'auto-start'
                                            }"
                                            class="icon has-text-info action-icon"
                                            role="button"
                                            tabindex="0"
                                            :aria-label="$i18n.get('label_log_file')"
                                            :href="bgProcess.log">
                                        <i
                                                aria-hidden="true"
                                                class="tainacan-icon tainacan-icon-1-25em tainacan-icon-openurl" />
                                    </a>

                                    <!-- View error log file -->
                                    <a
                                            v-if="bgProcess.error_log"
                                            v-tooltip="{
                                                delay: { show: 500, hide: 300 },
                                                content: $i18n.get('label_error_log_file'),
                                                autoHide: false,
                                                popperClass: ['tainacan-tooltip', 'tooltip', 'tainacan-repository-tooltip'],
                                                placement: 'auto-start'
                                            }"
                                            class="icon has-text-danger action-icon"
                                            role="button"
                                            tabindex="0"
                                            :aria-label="$i18n.get('label_error_log_file')"
                                            :href="bgProcess.error_log">
                                        <i
                                                aria-hidden="true"
                                                class="tainacan-icon tainacan-icon-1-25em tainacan-icon-openurl" />
                                    </a>

                                    <!-- Delete (available for any process) -->
                                    <span
                                            v-tooltip="{
                                                delay: { show: 500, hide: 300 },
                                                content: $i18n.get('label_delete_process'),
                                                autoHide: false,
                                                popperClass: ['tainacan-tooltip', 'tooltip', 'tainacan-repository-tooltip'],
                                                placement: 'auto-start'
                                            }"
                                            class="icon has-text-dark action-icon"
                                            role="button"
                                            tabindex="0"
                                            :aria-label="$i18n.get('label_delete_process')"
                                            @click.prevent.stop="deleteOneProcess(index)"
                                            @keydown.enter.prevent="deleteOneProcess(index)"
                                            @keydown.space.prevent="deleteOneProcess(index)">
                                        <i
                                                aria-hidden="true"
                                                class="tainacan-icon tainacan-icon-1-25em tainacan-icon-delete" />
                                    </span>
                                </div>
                            </td>
                        </tr>

                        <!-- Expandable detail row (Output only; logs live in the Actions column) -->
                        <tr
                                v-if="collapses[index]"
                                class="process-detail-row">
                            <td colspan="7">
                                <div class="process-detail-content">
                                    <div class="output-card">
                                        <span class="output-card-label">
                                            <span
                                                    aria-hidden="true"
                                                    class="icon is-small">
                                                <i class="tainacan-icon tainacan-icon-18px tainacan-icon-info" />
                                            </span>
                                            {{ $i18n.get('label_output') }}
                                        </span>
                                        <div
                                                class="output-card-body"
                                                v-html="bgProcess.output ? bgProcess.output : $i18n.get('label_no_output_info')" />
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        <div v-else-if="isLoading">
            <section class="section">
                <div class="content has-text-dark has-text-centered">
                    <p>{{ $i18n.get('loading_processes') }}</p>
                </div>
            </section>
        </div>
    </div>

</template>

<script>
    import { mapActions } from 'vuex';
    import CustomDialog from '../other/custom-dialog.vue';
    import moment from 'moment'

    export default {
        name: 'ProcessesList',
        props: {
            isLoading: false,
            total: 0,
            page: 1,
            processesPerPage: 12,
            processes: Array
        },
        data() {
            return {
                selected: [],
                collapses: [],
                allOnPageSelected: false,
                isSelecting: false,
                highlightedProcess: '',
                dateFormat: '',
            }
        },
        watch: {
            processes: {
                handler() {
                    this.selected = [];
                    for (let i = 0; i < this.processes.length; i++)
                        this.selected.push(false);

                    this.collapses = [];
                    for (let i = 0; i < this.processes.length; i++)
                        this.collapses.push(false);
                },
                deep: true
            },
            selected: {
                handler() {
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
                },
                deep: true
            }
        },
        mounted() {
            let locale = navigator.language;

            moment.locale(locale);

            let localeData = moment.localeData();
            this.dateFormat = localeData.longDateFormat('LLL');

            if (this.$route.query.highlight) {
                this.highlightedProcess = this.$route.query.highlight;
            }

            if (jQuery && jQuery( document )) {
                jQuery( document ).on( 'heartbeat-tick', this.onHeartBitTickList);
            }
        },
        beforeUnmount() {
            if (jQuery && jQuery( document )) {
                jQuery( document ).off( 'heartbeat-tick', this.onHeartBitTickList)
            }
        },
        methods: {
            ...mapActions('bgprocess', [
                'deleteProcess',
                'updateProcess',
                'heartBitUpdateProcess',
                'fetchProcesses'
            ]),
            selectAllOnPage() {
                for (let i = 0; i < this.selected.length; i++)
                    this.selected.splice(i, 1, !this.allOnPageSelected);
            },
            deleteOneProcess(index) {
                const modalTrigger = this.$modalFocusA11y.captureTrigger();
                this.$buefy.modal.open({
                    component: CustomDialog,
                    props: {
                        icon: 'alert',
                        title: this.$i18n.get('label_warning'),
                        message: this.$i18n.get('info_warning_process_delete'),
                        onConfirm: () => {
                            const processId = this.processes[index].ID;
                            this.deleteProcess(processId);
                        }
                    },
                    trapFocus: true,
                    customClass: 'tainacan-modal',
                    canCancel: ['escape', 'outside'],
                    events: {
                        beforeClose: () => this.$modalFocusA11y.restoreFocus(modalTrigger, this)
                    }
                });
            },
            deleteSelected() {
                const modalTrigger = this.$modalFocusA11y.captureTrigger();
                this.$buefy.modal.open({
                    component: CustomDialog,
                    props: {
                        icon: 'alert',
                        title: this.$i18n.get('label_warning'),
                        message: this.$i18n.get('info_warning_selected_processes_delete'),
                        onConfirm: () => {

                            for (let i = 0; i < this.processes.length;  i++) {
                                if (this.selected[i]) {
                                    this.deleteProcess(this.processes[i].ID);
                                }
                            }
                            this.allOnPageSelected = false;
                        }
                    },
                    trapFocus: true,
                    customClass: 'tainacan-modal',
                    canCancel: ['escape', 'outside'],
                    events: {
                        beforeClose: () => this.$modalFocusA11y.restoreFocus(modalTrigger, this)
                    }
                });
            },
            getDate(rawDate) {
                // A process that has never been processed has a zero/empty
                // processed_last value (e.g. "0000-00-00 00:00:00" or null).
                // Show a friendly "not processed yet" message instead of
                // "Invalid date" from moment.js.
                if ( rawDate === null || rawDate === undefined || rawDate === '' || rawDate === '0000-00-00 00:00:00' || rawDate === '0000-00-00 00:00:00.000000' ) {
                    return this.$i18n.get('info_not_processed_yet');
                }

                let date = moment(rawDate).format(this.dateFormat);

                if (date != 'Invalid date') {
                    return date;
                } else {
                    return this.$i18n.get('info_unknown_date');
                }
            },
            hasValidDate(rawDate) {
                return rawDate !== null && rawDate !== undefined && rawDate !== '' && rawDate !== '0000-00-00 00:00:00' && rawDate !== '0000-00-00 00:00:00.000000' && moment(rawDate).isValid();
            },
            pauseProcess(index) {
                const modalTrigger = this.$modalFocusA11y.captureTrigger();
                this.$buefy.modal.open({
                    component: CustomDialog,
                    props: {
                        icon: 'alert',
                        title: this.$i18n.get('label_warning'),
                        message: this.$i18n.get('info_warning_process_cancelled'),
                        onConfirm: () => {
                            this.updateProcess({ id: this.processes[index].ID, status: 'closed' });
                        },
                    },
                    trapFocus: true,
                    customClass: 'tainacan-modal',
                    canCancel: ['escape', 'outside'],
                    events: {
                        beforeClose: () => this.$modalFocusA11y.restoreFocus(modalTrigger, this)
                    }
                });
            },
            getProcessTypeIcon(bgProcess) {
                // Choose an icon based on the process action/type.
                if ( bgProcess.action === 'import' || ( bgProcess.name && /import/i.test(bgProcess.name) ) )
                    return 'tainacan-icon tainacan-icon-1-25em tainacan-icon-importers';
                if ( bgProcess.action === 'exporter' || ( bgProcess.name && /export/i.test(bgProcess.name) ) )
                    return 'tainacan-icon tainacan-icon-1-25em tainacan-icon-export';
                if ( bgProcess.action === 'generic_process' || ( bgProcess.name && /bulk/i.test(bgProcess.name) ) )
                    return 'tainacan-icon tainacan-icon-1-25em tainacan-icon-edit';
                return 'tainacan-icon tainacan-icon-1-25em tainacan-icon-processes';
            },
            getStatusTagClass(bgProcess) {
                const status = bgProcess.status;
                if ( status === 'running' ) return 'is-success';
                if ( status === 'finished' && !bgProcess.error_log ) return 'is-success is-light';
                if ( status === 'finished-errors' || ( bgProcess.done > 0 && bgProcess.error_log && status === 'finished' ) ) return 'is-warning is-light';
                if ( status === 'errored' ) return 'is-danger';
                if ( status === 'cancelled' ) return 'is-danger is-light';
                if ( status === 'paused' ) return 'is-dark is-light';
                if ( status === 'waiting' ) return 'is-info is-light';
                return 'is-light';
            },
            getStatusIcon(bgProcess) {
                const status = bgProcess.status;
                if ( status === 'running' ) return 'tainacan-icon tainacan-icon-1-25em tainacan-icon-playfill';
                if ( ( status === 'finished' && !bgProcess.error_log ) || status === null ) return 'tainacan-icon tainacan-icon-1-25em tainacan-icon-approvedcircle';
                if ( status === 'finished-errors' || ( bgProcess.done > 0 && bgProcess.error_log && status === 'finished' ) ) return 'tainacan-icon tainacan-icon-1-25em tainacan-icon-alertcircle';
                if ( status === 'errored' ) return 'tainacan-icon tainacan-icon-1-25em tainacan-icon-processerror';
                if ( status === 'cancelled' ) return 'tainacan-icon tainacan-icon-1-25em tainacan-icon-repprovedcircle';
                if ( status === 'paused' ) return 'tainacan-icon tainacan-icon-1-25em tainacan-icon-pause';
                if ( status === 'waiting' ) return 'tainacan-icon tainacan-icon-1-25em tainacan-icon-waiting';
                return 'tainacan-icon tainacan-icon-1-25em tainacan-icon-processes';
            },
            getStatusLabel(bgProcess) {
                const status = bgProcess.status;
                if ( status === 'running' ) return this.$i18n.get('label_process_running');
                if ( status === 'finished' && !bgProcess.error_log ) return this.$i18n.get('label_process_completed');
                if ( status === 'finished-errors' || ( bgProcess.done > 0 && bgProcess.error_log && status === 'finished' ) ) return this.$i18n.get('label_process_completed_with_errors');
                if ( status === 'errored' ) return this.$i18n.get('label_process_failed');
                if ( status === 'cancelled' ) return this.$i18n.get('label_process_cancelled');
                if ( status === 'paused' ) return this.$i18n.get('label_process_paused');
                if ( status === 'waiting' ) return this.$i18n.get('label_process_waiting');
                return status;
            },
            onHeartBitTickList(event, data) {
                let updatedProcesses = data.bg_process_feedback;

                for (let updatedProcess of updatedProcesses) {
                    let updatedProcessIndex = this.processes.findIndex((aProcess) => aProcess.ID == updatedProcess.ID);
                    if (updatedProcessIndex >= 0) {
                        this.heartBitUpdateProcess(updatedProcess);
                    }
                }
            }
        }
    }
</script>

<style lang="scss" scoped>

    @keyframes highlight {
        from {
            background-color: var(--tainacan-blue1);
        }
        to {
            background-color: var(--tainacan-white);
        }
    }

    .table-container {
        padding: 0 var(--tainacan-one-column);
        position: relative;
        margin-top: 1rem;
        margin-bottom: 40px;
    }

    .processes-table-wrapper {
        overflow-x: auto;
    }

    .processes-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.875em;

        thead {
            tr {
                border-bottom: 2px solid var(--tainacan-lists-separator-color, var(--tainacan-item-hover-background-color));

                th {
                    text-align: start;
                    padding: 0.6em 0.75em;
                    color: var(--tainacan-gray5);
                    font-weight: 600;
                    white-space: nowrap;

                    &.col-actions {
                        text-align: end;
                    }
                }
            }
        }

        tbody {
            tr {
                border-bottom: 1px solid var(--tainacan-lists-separator-color, var(--tainacan-item-hover-background-color));
                cursor: pointer;
                transition: background-color 0.15s ease;

                &:hover {
                    background-color: var(--tainacan-gray1);
                }

                &.opened-process {
                    background-color: var(--tainacan-gray0);
                }

                &.highlighted-process {
                    transition: background-color 0.8s;
                    animation-name: highlight;
                    animation-duration: 1s;
                    animation-iteration-count: 2;
                }

                &.process-detail-row {
                    cursor: default;
                    background-color: var(--tainacan-gray0);

                    &:hover {
                        background-color: var(--tainacan-gray0);
                    }

                    td {
                        padding: 0;
                    }
                }

                td {
                    padding: 0.75em 0.75em;
                    vertical-align: middle;
                    color: var(--tainacan-info-color);

                    &.col-toggle {
                        width: 36px;
                        text-align: center;
                        padding: 0.5em;

                        .toggle-icon {
                            cursor: pointer;
                            border-radius: var(--tainacan-button-border-radius);
                            transition: background-color 0.15s ease;

                            &:hover {
                                background-color: var(--tainacan-gray2);
                            }
                        }
                    }

                    &.col-type {
                        .process-name {
                            color: var(--tainacan-black);
                            font-weight: 500;
                            white-space: nowrap;
                            text-overflow: ellipsis;
                            overflow: hidden;
                            display: inline-block;
                            max-width: 26ch;
                            vertical-align: middle;
                        }
                    }

                    &.col-status {
                        .process-status-tag {
                            display: inline-flex;
                            align-items: center;
                            gap: 0.35em;
                            white-space: nowrap;

                            .icon {
                                font-size: 0.9em;
                            }
                        }
                    }

                    &.col-progress {
                        .progress-cell {
                            display: flex;
                            align-items: center;
                            gap: 0.5em;

                            .progress-bar {
                                max-width: 120px;
                                min-width: 60px;
                            }

                            .progress-value-text {
                                font-size: 0.85em;
                                color: var(--tainacan-gray5);
                                white-space: nowrap;
                            }
                        }
                    }

                    &.col-created,
                    &.col-executed {
                        white-space: nowrap;
                        color: var(--tainacan-gray5);
                    }

                    &.col-actions {
                        text-align: end;

                        .actions-container {
                            display: inline-flex;
                            align-items: center;
                            justify-content: flex-end;
                            gap: 0.15em;

                            .action-icon {
                                cursor: pointer;
                                padding: 0.25em;
                                border-radius: var(--tainacan-button-border-radius);
                                transition: background-color 0.15s ease;

                                &:hover {
                                    background-color: var(--tainacan-gray2);
                                }
                            }

                            a.action-icon {
                                text-decoration: none;
                            }
                        }
                    }
                }
            }
        }
    }

    .process-detail-content {
        padding: 0.75em 1.25em 1.25em 1.25em;

        .output-card {
            background: var(--tainacan-white, #fff);
            border: 1px solid var(--tainacan-lists-separator-color, var(--tainacan-item-hover-background-color));
            border-radius: var(--tainacan-button-border-radius, 4px);
            overflow: hidden;

            .output-card-label {
                display: flex;
                align-items: center;
                gap: 0.4em;
                padding: 0.5em 0.85em;
                background: var(--tainacan-gray0, #fafafa);
                border-bottom: 1px solid var(--tainacan-lists-separator-color, var(--tainacan-item-hover-background-color));
                color: var(--tainacan-gray5);
                font-size: 0.8em;
                font-weight: 600;
                text-transform: uppercase;
                letter-spacing: 0.03em;

                .icon {
                    color: var(--tainacan-secondary);
                }
            }

            .output-card-body {
                padding: 0.85em 1em;
                color: var(--tainacan-info-color);
                font-size: 0.9em;
                line-height: 1.5;
                word-break: break-word;

                p {
                    margin-bottom: 0.4em;

                    &:last-child {
                        margin-bottom: 0;
                    }
                }
            }
        }
    }

    .loading-icon .control.is-loading::after {
        position: relative !important;
        right: 0;
        top: 0;
    }

    /* Responsive: stack cells on small screens */
    @media screen and (max-width: 768px) {
        .processes-table {
            thead {
                display: none;
            }
            tbody {
                tr {
                    display: block;
                    margin-bottom: 0.75em;
                    padding: 0.5em;

                    &.process-detail-row {
                        display: block;
                        padding: 0;
                    }

                    td {
                        display: flex;
                        justify-content: space-between;
                        align-items: center;
                        padding: 0.4em 0.5em;
                        text-align: end;

                        &::before {
                            content: attr(data-label);
                            font-weight: 600;
                            color: var(--tainacan-gray5);
                            text-align: start;
                            padding-inline-end: 1em;
                        }

                        /* The toggle column has no data-label; hide its empty prefix on mobile. */
                        &.col-toggle {
                            &::before {
                                content: '';
                            }
                        }

                        &.col-actions {
                            .actions-container {
                                justify-content: flex-end;
                            }
                        }
                    }
                }
            }
        }
    }

</style>
