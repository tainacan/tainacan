<template>
    <div
            v-if="processes.length > 0 && !isLoading"
            class="table-container">
        <div class="table-wrapper">
            <table
                    id="processes-list-results"
                    class="tainacan-table is-narrow">
                <thead>
                    <tr>
                        <th class="status-cell">
                            <span class="sr-only">
                                {{ $i18n.get('label_view_details') }}
                            </span>
                        </th>
                        <th>
                            <div class="th-wrap">
                                {{ $i18n.get('label_process_type') }}
                            </div>
                        </th>
                        <th>
                            <div class="th-wrap">
                                {{ $i18n.get('label_status') }}
                            </div>
                        </th>
                        <th class="column-align-right">
                            <div class="th-wrap">
                                {{ $i18n.get('label_progress') }}
                            </div>
                        </th>
                        <th>
                            <div class="th-wrap">
                                {{ $i18n.get('label_queued_on') }}
                            </div>
                        </th>
                        <th>
                            <div class="th-wrap">
                                {{ $i18n.get('label_last_processed_on') }}
                            </div>
                        </th>
                        <th class="actions-header">
                            <span class="sr-only">
                                {{ $i18n.get('label_actions') }}
                            </span>
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
                            <td class="status-cell">
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
                                        role="button"
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
                                    class="column-default-width column-main-content"
                                    :label="$i18n.get('label_process_type')"
                                    :aria-label="$i18n.get('label_process_type') + ': ' + (bgProcess.name ? bgProcess.name : $i18n.get('label_unnamed_process'))">
                                <p
                                        v-tooltip="{
                                            delay: { show: 500, hide: 300 },
                                            content: bgProcess.name ? bgProcess.name : $i18n.get('label_unnamed_process'),
                                            autoHide: false,
                                            popperClass: ['tainacan-tooltip', 'tooltip', 'tainacan-repository-tooltip'],
                                            placement: 'auto-start'
                                        }">
                                    {{ bgProcess.name ? bgProcess.name : $i18n.get('label_unnamed_process') }}
                                </p>
                            </td>

                            <!-- Status -->
                            <td
                                    class="column-large-width column-needed-width"
                                    :label="$i18n.get('label_status')"
                                    :aria-label="$i18n.get('label_status') + ': ' + getStatusLabel(bgProcess)">
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

                            <!-- Progress: percentage, plus a spinner while the process is running -->
                            <td
                                    class="column-small-width column-align-right"
                                    :label="$i18n.get('label_progress')"
                                    :aria-label="$i18n.get('label_progress') + ': ' + (bgProcess.progress_value ? bgProcess.progress_value : 0) + '%'">
                                <span class="progress-cell">
                                    <span
                                            v-if="bgProcess.status === 'running'"
                                            class="progress-spinner"
                                            aria-hidden="true">
                                        <i class="tainacan-icon tainacan-icon-updating tainacan-icon-spin" />
                                    </span>
                                    <span class="progress-value-text">{{ bgProcess.progress_value ? bgProcess.progress_value : 0 }}%</span>
                                </span>
                            </td>

                            <!-- Created Date -->
                            <td
                                    class="table-creation column-default-width"
                                    :label="$i18n.get('label_queued_on')"
                                    :aria-label="$i18n.get('label_queued_on') + ': ' + getDate(bgProcess.queued_on)">
                                <p
                                        v-tooltip="{
                                            delay: { show: 500, hide: 300 },
                                            content: getDate(bgProcess.queued_on),
                                            autoHide: false,
                                            popperClass: ['tainacan-tooltip', 'tooltip', 'tainacan-repository-tooltip'],
                                            placement: 'auto-start'
                                        }">
                                    {{ getDate(bgProcess.queued_on) }}
                                </p>
                            </td>

                            <!-- Execute Date -->
                            <td
                                    class="table-modification column-default-width"
                                    :label="$i18n.get('label_last_processed_on')"
                                    :aria-label="$i18n.get('label_last_processed_on') + ': ' + getDate(bgProcess.processed_last)">
                                <p
                                        v-tooltip="{
                                            delay: { show: 500, hide: 300 },
                                            content: getDate(bgProcess.processed_last),
                                            autoHide: false,
                                            popperClass: ['tainacan-tooltip', 'tooltip', 'tainacan-repository-tooltip'],
                                            placement: 'auto-start'
                                        }"
                                        :class="{ 'is-not-processed': !hasValidDate(bgProcess.processed_last) }">
                                    {{ getDate(bgProcess.processed_last) }}
                                </p>
                            </td>

                            <!-- Actions -->
                            <td
                                    class="actions-cell column-default-width"
                                    :label="$i18n.get('label_actions')">
                                <div class="actions-container">
                                    <!-- Stop (running only) -->
                                    <a
                                            v-if="bgProcess.status === 'running'"
                                            :id="'button-stop-' + bgProcess.ID"
                                            class="button-stop"
                                            role="button"
                                            tabindex="0"
                                            :aria-label="$i18n.get('label_stop_process')"
                                            @click.prevent.stop="pauseProcess(index)"
                                            @keydown.enter.prevent="pauseProcess(index)"
                                            @keydown.space.prevent="pauseProcess(index)">
                                        <span
                                                v-tooltip="{
                                                    delay: { show: 500, hide: 300 },
                                                    content: $i18n.get('label_stop_process'),
                                                    autoHide: true,
                                                    popperClass: ['tainacan-tooltip', 'tooltip', 'tainacan-repository-tooltip'],
                                                    placement: 'auto'
                                                }"
                                                class="icon"
                                                aria-hidden="true">
                                            <i class="has-text-secondary tainacan-icon tainacan-icon-1-25em tainacan-icon-stop" />
                                        </span>
                                    </a>

                                    <!-- Delete (available for any process) -->
                                    <a
                                            :id="'button-delete-' + bgProcess.ID"
                                            class="button-delete"
                                            role="button"
                                            tabindex="0"
                                            :aria-label="$i18n.get('label_delete_process')"
                                            @click.prevent.stop="deleteOneProcess(index)"
                                            @keydown.enter.prevent="deleteOneProcess(index)"
                                            @keydown.space.prevent="deleteOneProcess(index)">
                                        <span
                                                v-tooltip="{
                                                    delay: { show: 500, hide: 300 },
                                                    content: $i18n.get('label_delete_process'),
                                                    autoHide: true,
                                                    popperClass: ['tainacan-tooltip', 'tooltip', 'tainacan-repository-tooltip'],
                                                    placement: 'auto'
                                                }"
                                                class="icon"
                                                aria-hidden="true">
                                            <i class="has-text-secondary tainacan-icon tainacan-icon-1-25em tainacan-icon-delete" />
                                        </span>
                                    </a>
                                </div>
                            </td>
                        </tr>

                        <!-- Expandable detail row: log files and summary follow the columns above -->
                        <tr
                                v-if="collapses[index]"
                                class="process-detail-row">
                            <td
                                    colspan="2"
                                    class="process-detail-cell">
                                <div class="output-card logs-card">
                                    <span class="output-card-label">
                                        {{ $i18n.get('label_process_log_files') }}
                                    </span>
                                    <div class="output-card-body">
                                        <div
                                                v-if="bgProcess.log || bgProcess.error_log"
                                                class="process-log-links">
                                            <a
                                                    v-if="bgProcess.log"
                                                    class="process-log-link"
                                                    target="_blank"
                                                    :href="bgProcess.log"
                                                    @click.stop>
                                                <span
                                                        aria-hidden="true"
                                                        class="icon is-small">
                                                    <i class="tainacan-icon tainacan-icon-18px tainacan-icon-openurl" />
                                                </span>
                                                {{ $i18n.get('label_log_file') }}
                                            </a>
                                            <a
                                                    v-if="bgProcess.error_log"
                                                    class="process-log-link is-error"
                                                    target="_blank"
                                                    :href="bgProcess.error_log"
                                                    @click.stop>
                                                <span
                                                        aria-hidden="true"
                                                        class="icon is-small">
                                                    <i class="tainacan-icon tainacan-icon-18px tainacan-icon-openurl" />
                                                </span>
                                                {{ $i18n.get('label_error_log_file') }}
                                            </a>
                                        </div>
                                        <span
                                                v-else
                                                class="has-text-dark is-italic">
                                            {{ $i18n.get('label_no_log_info') }}
                                        </span>
                                    </div>
                                </div>
                            </td>
                            <td
                                    colspan="5"
                                    class="process-detail-cell">
                                <div class="output-card">
                                    <span class="output-card-label">
                                        {{ $i18n.get('label_process_summary') }}
                                    </span>
                                    <div
                                            class="output-card-body"
                                            v-html="bgProcess.output ? bgProcess.output : $i18n.get('label_no_process_summary')" />
                                </div>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>

    <div v-else-if="isLoading">
        <section class="section">
            <div class="content has-text-dark has-text-centered">
                <p>{{ $i18n.get('loading_processes') }}</p>
            </div>
        </section>
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

    @use "../../scss/_tables.scss";

    @keyframes highlight {
        from {
            background-color: var(--tainacan-blue1);
        }
        to {
            background-color: var(--tainacan-white);
        }
    }

    .table-container .table-wrapper table.tainacan-table {
        tbody tr {
            &.opened-process {
                background-color: var(--tainacan-item-hover-background-color);

                .actions-cell {
                    background-color: var(--tainacan-item-heading-hover-background-color);
                }

                .actions-container {
                    background-color: var(--tainacan-item-heading-hover-background-color);
                }
            }

            &.highlighted-process {
                animation-name: highlight;
                animation-duration: 1s;
                animation-iteration-count: 2;

                .actions-container {
                    animation-name: highlight;
                    animation-duration: 1s;
                    animation-iteration-count: 2;
                }
            }

            &.process-detail-row {
                cursor: default;

                &:hover,
                &:focus,
                &:focus-visible,
                &:focus-within {
                    background-color: var(--tainacan-item-background-color) !important;
                    cursor: default;
                }

                td.process-detail-cell {
                    height: auto;
                    max-height: none;
                    padding: 0.5em 0.35em 0.85em;
                    vertical-align: top;
                    line-height: normal;

                    .output-card {
                        height: 100%;
                    }
                }
            }

            td.status-cell,
            td:has(.process-status-tag) {
                line-height: normal;
            }

            td:has(.progress-cell) {
                line-height: normal;
            }

            td.status-cell .toggle-icon {
                cursor: pointer;
                border-radius: var(--tainacan-button-border-radius);
            }

            td.column-default-width > p.is-not-processed {
                color: var(--tainacan-gray4);
            }
        }
    }

    .process-status-tag {
        display: inline-flex;
        align-items: center;
        gap: 0.35em;
        white-space: nowrap;
        height: auto;
        line-height: 1.75;
        font-size: 0.75em;

    }

    .progress-cell {
        display: inline-flex;
        align-items: center;
        gap: 0.35em;
        white-space: nowrap;

        .progress-spinner {
            display: inline-flex;
            font-size: 1em;
            line-height: 1;
            color: var(--tainacan-success);
        }

        .progress-value-text {
            font-size: 0.75em;
            line-height: 1;
        }
    }

    .process-detail-cell {
        .output-card {
            width: 100%;
            background: var(--tainacan-white, #fff);
            border-radius: var(--tainacan-button-border-radius, 4px);
            overflow: hidden;

            .output-card-label {
                display: block;
                padding: 0.5em 0.85em;
                border-bottom: 1px solid var(--tainacan-lists-separator-color, var(--tainacan-item-hover-background-color));
                color: var(--tainacan-gray5);
                font-size: 0.75em;
                font-weight: 600;
                line-height: 1.25;
            }

            .output-card-body {
                padding: 0.85em 1em;
                color: var(--tainacan-info-color);
                font-size: 0.75em;
                line-height: 1.5;
                word-break: break-word;

                p {
                    margin-bottom: 0.4em;
                    font-size: inherit;
                    line-height: inherit;
                    white-space: normal;
                    max-height: none;

                    &:last-child {
                        margin-bottom: 0;
                    }
                }
            }
        }

        .process-log-links {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            gap: 0.65em;
        }
    }

    .table-container .table-wrapper table.tainacan-table a.process-log-link {
        display: inline-flex;
        align-items: center;
        gap: 0.35em;
        line-height: 1.4;
        text-decoration: underline !important;

        .icon {
            color: inherit;
        }

        &.is-error {
            color: var(--tainacan-danger) !important;
        }
    }

</style>
