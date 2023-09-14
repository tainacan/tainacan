<template>
    <div>
        <div class="repository-level-page page-container">
            <tainacan-title :bread-crumb-items="[{ path: '', label: $i18n.get('processes') }]"/>

            <div class="above-subheader">

                <b-loading
                        :is-full-page="false"
                        :active.sync="isLoading" 
                        :can-cancel="false"/>

                <div class="sub-header">
                    <b-field class="header-item">
                        <b-datepicker
                            ref="datepicker"
                            :placeholder="$i18n.get('instruction_filter_activities_date')"
                            range
                            icon="calendar-today"
                            v-model="searchDates"
                            @input="searchProcesses()"
                            :date-formatter="(date) => dateFormatter(date)"
                            :date-parser="(date) => dateParser(date)"
                            :years-range="[-50, 3]"
                            :day-names="[
                                $i18n.get('datepicker_short_sunday'),
                                $i18n.get('datepicker_short_monday'),
                                $i18n.get('datepicker_short_tuesday'),
                                $i18n.get('datepicker_short_wednesday'),
                                $i18n.get('datepicker_short_thursday'),
                                $i18n.get('datepicker_short_friday'),
                                $i18n.get('datepicker_short_saturday'),
                            ]"
                            :month-names="[
                                $i18n.get('datepicker_month_january'),
                                $i18n.get('datepicker_month_february'),
                                $i18n.get('datepicker_month_march'),
                                $i18n.get('datepicker_month_april'),
                                $i18n.get('datepicker_month_may'),
                                $i18n.get('datepicker_month_june'),
                                $i18n.get('datepicker_month_july'),
                                $i18n.get('datepicker_month_august'),
                                $i18n.get('datepicker_month_september'),
                                $i18n.get('datepicker_month_october'),
                                $i18n.get('datepicker_month_november'),
                                $i18n.get('datepicker_month_december'),
                            ]"
                        />
                        <p
                            class="control"
                            v-if="searchDates && searchDates.length != 0">
                            <button
                                class="button"
                                @click="clearSearchDates()">
                                <span class="icon"><i class="tainacan-icon tainacan-icon-close"/></span>
                            </button>
                        </p>
                    </b-field>

                    <b-field class="header-item">
                        <b-input
                            :placeholder="$i18n.get('instruction_search')"
                            type="search"
                            size="is-small"
                            :aria-label="$i18n.get('instruction_search') + ' ' + $i18n.get('processes')"
                            autocomplete="on"
                            v-model="searchQuery"
                            @keyup.enter.native="searchProcesses()"
                            icon-right="magnify"
                            icon-right-clickable
                            @icon-right-click="searchProcesses" />
                    </b-field>
                </div>

                <processes-list
                        :is-loading="isLoading"
                        :total="totalProcesses"
                        :page="processesPage"
                        :processes-per-page="processesPerPage"
                        :processes="processes"
                        @updateTotalProcesses="(total) => { totalProcesses = total; $console.log(totalProcesses);}" />

                <!-- Empty state processes image -->
                <div v-if="processes.length <= 0 && !isLoading">
                    <section class="section">
                        <div class="content has-text-grey has-text-centered">
                            <p>
                                <span class="icon">
                                    <i class="tainacan-icon tainacan-icon-30px tainacan-icon-processes"/>
                                </span>
                            </p>
                            <p v-if="status == undefined || status == ''">{{ $i18n.get('info_no_process') }}</p>
                        </div>
                    </section>
                </div>

                <!-- Footer -->
                <div 
                        class="pagination-area" 
                        v-if="processes.length > 0">
                    <div class="shown-items">
                        {{
                            $i18n.get('info_showing_processes') + ' ' +
                            (processesPerPage * (processesPage - 1) + 1) +
                            $i18n.get('info_to') +
                            getLastProcessesNumber() +
                            $i18n.get('info_of') + totalProcesses + '.'
                        }}
                    </div>
                    <div class="items-per-page">
                        <b-field 
                                horizontal 
                                :label="$i18n.get('label_processes_per_page')">
                            <b-select
                                    :value="processesPerPage"
                                    @input="onChangeProcessesPerPage"
                                    :disabled="processes.length <= 0">
                                <option value="12">12</option>
                                <option value="24">24</option>
                                <option value="48">48</option>
                                <option :value="maxProcessesPerPage">{{ maxProcessesPerPage }}</option>
                            </b-select>
                        </b-field>
                    </div>
                    <div class="pagination">
                        <b-pagination
                                @change="onPageChange"
                                :total="totalProcesses"
                                :current.sync="processesPage"
                                order="is-centered"
                                size="is-small"
                                :per-page="processesPerPage"
                                :aria-next-label="$i18n.get('label_next_page')"
                                :aria-previous-label="$i18n.get('label_previous_page')"
                                :aria-page-label="$i18n.get('label_page')"
                                :aria-current-label="$i18n.get('label_current_page')"/>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import ProcessesList from "../../components/lists/processes-list.vue";
    import { dateInter } from "../../js/mixins";
    import { mapActions, mapGetters } from 'vuex';
    import moment from 'moment'

    export default {
        name: 'ProcessesPage',
        components: {
            ProcessesList,
        },
        mixins: [ dateInter ],
        data() {
            return {
                isLoading: false,
                processesPage: 1,
                processesPerPage: 12,
                searchQuery: '',
                searchDates: [],
                totalUsers: 0,
                maxProcessesPerPage: tainacan_plugin.api_max_items_per_page ? Number(tainacan_plugin.api_max_items_per_page) : 96
            }
        },
        computed: {
            processes(){
                return this.getProcesses();
            }
        },
        created() {
            this.processesPerPage = this.$userPrefs.get('processes_per_page');
        },
        mounted() {
            if (this.processesPerPage != this.$userPrefs.get('processes_per_page'))
                this.processesPerPage = this.$userPrefs.get('processes_per_page');

            if (!this.processesPerPage) {
                this.processesPerPage = 12;
                this.$userPrefs.set('processes_per_page', 12);
            }
            this.loadProcesses();
        },
        methods: {
            ...mapActions('bgprocess', [
                'fetchProcesses',
            ]),
            ...mapGetters('bgprocess', [
                'getProcesses'
            ]),
            onChangeProcessesPerPage(value) {
                
                if (value != this.processesPerPage) {
                    this.$userPrefs.set('processes_per_page', value)
                    .then((newValue) => {
                        this.processesPerPage = newValue;
                    })
                    .catch(() => {
                        this.$console.log("Error settings user prefs for processes per page")
                    });
                }
                this.processesPerPage = value;
                this.loadProcesses();
            },
            onPageChange(page) {
                this.processesPage = page;
                this.loadProcesses();
            },
            searchProcesses() {
              this.loadProcesses();
            },
            clearSearchDates() {
              this.searchDates = null;
                this.loadProcesses();
            },
            loadProcesses() {
              this.isLoading = true;
              let dateFormat = 'YYYY-MM-DD';
              let fromDate = this.searchDates && this.searchDates[0] ? moment(this.searchDates[0]).format(dateFormat) : null;
              let toDate = this.searchDates && this.searchDates[1] ? moment(this.searchDates[1]).format(dateFormat) : null;

              this.fetchProcesses({
                page: this.processesPage,
                processesPerPage: this.processesPerPage,
                searchDates: [fromDate, toDate],
                search: this.searchQuery,
                shouldUpdateStore: true
              })
                  .then(res => {
                    this.isLoading = false;
                    this.totalProcesses = res.total;
                  })
                  .catch(() => {
                    this.isLoading = false;
                  });
            },
            getLastProcessesNumber() {
                let last = (Number(this.processesPerPage * (this.processesPage - 1)) + Number(this.processesPerPage));
                return last > this.totalProcesses ? this.totalProcesses : last;
            },
            dateFormatter(dateObject) {
                if (dateObject == null || dateObject.length == 0 || dateObject[0] == null || dateObject[1] == null)
                    return "";
                return moment(dateObject[0], moment.ISO_8601).format(this.dateFormat) + " - " + moment(dateObject[1], moment.ISO_8601).format(this.dateFormat);
            },
            dateParser(dateString) { 
                return [
                    moment(dateString[0], this.dateFormat).toDate(),
                    moment(dateString[1], this.dateFormat).toDate()
                ];
            }
        }
    }
</script>

<style lang="scss" scoped>
    @import '../../scss/_variables.scss';

    .sub-header {
        @include logs-container();

        .header-item {
            margin-bottom: 0 !important;
            min-height: 2em;
            display: flex;
            align-items: center;

            &:not(:last-child) {
                padding-right: 0.5em;
            }

            .label {
                font-size: 0.875em;
                font-weight: normal;
                margin-top: 3px;
                margin-bottom: 2px;
                cursor: default;
            }

            .button {
                display: flex;
                align-items: center;
                border-radius: 0px !important;
                height: 100% !important;
            }
            
            .field {
                align-items: center;
            }

            .gray-icon,
            .gray-icon .icon {
                color: var(--tainacan-info-color) !important;
                padding-right: 10px;
                height: 1.125em !important;
            }
            .gray-icon .icon i::before, 
            .gray-icon i::before {
                max-width: 1.25em;
            }

            .icon {
                pointer-events: all;
                cursor: pointer;
                color: var(--tainacan-blue5);
                height: 27px;
                font-size: 1.125em !important;
                height: 1.75em;
            }
        }

        @media screen and (max-width: 769px) {
            height: 60px;
            margin-top: -0.5em;
            padding-top: 0.9em;

            .header-item:not(:last-child) {
                padding-right: 0.2em;
            }
        }
    }
    .above-subheader {
        margin-bottom: 0;
        margin-top: 0;
        min-height: 100%;
        height: auto;
    }
</style>
