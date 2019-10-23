<template>
    <div>
        <div
                :class="{
                    'repository-level-page': isRepositoryLevel,
                    'page-container': isRepositoryLevel
                }">
            <tainacan-title
                    v-if="!isItemLevel"
                    :bread-crumb-items="[{ path: '', label: this.$i18n.get('activities') }]"/>
            <div :class="{ 'above-subheader': isRepositoryLevel }">

                <div 
                        v-if="isRepositoryLevel"
                        class="tabs">
                    <ul>
                        <li
                                @click="onChangeTab('')"
                                :class="{ 'is-active': tab == undefined || tab == ''}"><a>{{ $i18n.get('activities') }}</a></li>
                        <li
                                @click="onChangeTab('processes')"
                                :class="{ 'is-active': tab == 'processes'}"><a>{{ $i18n.get('processes') }}</a></li>
                    </ul>
                </div>

                <b-loading
                        :is-full-page="false"
                        :active.sync="isLoading" 
                        :can-cancel="false"/>

                <div 
                        v-if="tab != 'processes'"
                        class="sub-header">

                    <b-field class="header-item">
                        <b-datepicker
                                ref="datepicker"
                                :placeholder="$i18n.get('instruction_filter_activities_date')"
                                v-model="searchDates"
                                range
                                @input="searchActivities()"
                                :date-formatter="(date) => dateFormatter(date)"
                                :date-parser="(date) => dateParser(date)"
                                size="is-small"
                                icon="calendar-today"
                                :years-range="[-50, 3]"
                                :day-names="[
                                    $i18n.get('datepicker_short_sunday'),
                                    $i18n.get('datepicker_short_monday'),
                                    $i18n.get('datepicker_short_tuesday'),
                                    $i18n.get('datepicker_short_wednesday'),
                                    $i18n.get('datepicker_short_thursday'),
                                    $i18n.get('datepicker_short_friday'),
                                    $i18n.get('datepicker_short_saturday')
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
                                    $i18n.get('datepicker_month_december')
                                ]"/>
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
                        <div class="control has-icons-right  is-small is-clearfix">
                            <input
                                    class="input is-small"
                                    :placeholder="$i18n.get('instruction_search')"
                                    type="search"
                                    :aria-label="$i18n.get('instruction_search') + ' ' + $i18n.get('activities')"
                                    autocomplete="on"
                                    v-model="searchQuery"
                                    @keyup.enter="searchActivities()">
                            <span
                                    @click="searchActivities()"
                                    class="icon is-right">
                                <i class="tainacan-icon tainacan-icon-search" />
                            </span>
                        </div>
                    </b-field>
                </div>

                <activities-list
                        v-if="tab != 'processes'"
                        :is-loading="isLoading"
                        :total-activities="totalActivities"
                        :page="activitiesPage"
                        :activities-per-page="activitiesPerPage"
                        :activities="activities"/>

                <processes-list
                        v-if="tab == 'processes'"
                        :is-loading="isLoading"
                        :total="total"
                        :page="processesPage"
                        :processes-per-page="processesPerPage"
                        :processes="processes"/>

                <!-- Empty state processes image -->
                <div v-if="tab == 'processes' && processes.length <= 0 && !isLoading">
                    <section class="section">
                        <div class="content has-text-grey has-text-centered">
                            <p>
                                <span class="icon">
                                    <i class="tainacan-icon tainacan-icon-activities"/>
                                </span>
                            </p>
                            <p v-if="status == undefined || status == ''">{{ $i18n.get('info_no_process') }}</p>
                        </div>
                    </section>
                </div>

                <!-- Footer -->
                <div
                        class="pagination-area"
                        v-if="tab != 'processes' && totalActivities > 0">
                    <div class="shown-items">
                        {{
                            $i18n.get('info_showing_activities') +
                            (activitiesPerPage * (activitiesPage - 1) + 1) +
                            $i18n.get('info_to') +
                            getLastActivityNumber() +
                            $i18n.get('info_of') + totalActivities + '.'
                        }}
                    </div>
                    <div class="items-per-page">
                        <b-field
                                horizontal
                                :label="$i18n.get('label_activities_per_page')">
                            <b-select
                                    :value="activitiesPerPage"
                                    @input="onChangeActivitiesPerPage"
                                    :disabled="activities.length <= 0">
                                <option value="12">12</option>
                                <option value="24">24</option>
                                <option value="48">48</option>
                                <option value="96">96</option>
                            </b-select>
                        </b-field>
                    </div>
                    <div class="pagination">
                        <b-pagination
                                @change="onPageChange"
                                :total="totalActivities"
                                :current.sync="activitiesPage"
                                order="is-centered"
                                size="is-small"
                                :per-page="activitiesPerPage"
                                :aria-next-label="$i18n.get('label_next_page')"
                                :aria-previous-label="$i18n.get('label_previous_page')"
                                :aria-page-label="$i18n.get('label_page')"
                                :aria-current-label="$i18n.get('label_current_page')"/>
                    </div>
                </div>
                <div 
                        class="pagination-area" 
                        v-if="tab == 'processes' && processes.length > 0">
                    <div class="shown-items">
                        {{
                            $i18n.get('info_showing_processes') +
                            (processesPerPage * (processesPage - 1) + 1) +
                            $i18n.get('info_to') +
                            getLastProcessesNumber() +
                            $i18n.get('info_of') + total + '.'
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
                                <option value="96">96</option>
                            </b-select>
                        </b-field>
                    </div>
                    <div class="pagination">
                        <b-pagination
                                @change="onPageChange"
                                :total="total"
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
    import ActivitiesList from "../../components/lists/activities-list.vue";
    import ProcessesList from "../../components/lists/processes-list.vue";
    import { dateInter } from "../../../admin/js/mixins";
    import { mapActions, mapGetters } from 'vuex';
    import moment from 'moment'

    export default {
        name: 'ActivitiesPage',
        mixins: [ dateInter ],
        data(){
            return {
                isLoading: false,
                totalActivities: 0,
                activitiesPage: 1,
                processesPage: 1,
                activitiesPerPage: 12,
                processesPerPage: 12,
                isRepositoryLevel: false,
                tab: '',
                isItemLevel: false,
                searchQuery: '',
                searchDates: []
            }
        },
        components: {
            ActivitiesList,
            ProcessesList,
        },
        methods: {
            ...mapActions('activity', [
                'fetchActivities',
                'fetchCollectionActivities',
                'fetchItemActivities'
            ]),
            ...mapGetters('activity', [
                'getActivities'
            ]),
            ...mapActions('bgprocess', [
                'fetchProcesses',
            ]),
            ...mapGetters('bgprocess', [
                'getProcesses'
            ]),
            onChangeTab(tab) {
                this.tab = tab;
                if (this.tab == 'processes') {
                    this.loadProcesses();
                    this.$router.push({query: {tab: 'processes'}});
                } else {
                    this.loadActivities();
                    this.$router.push({query: {}});
                }
            },
            onChangeActivitiesPerPage(value) {
                
                if (value != this.activitiesPerPage) {
                    this.$userPrefs.set('activities_per_page', value)
                        .then((newValue) => {
                            this.activitiesPerPage = newValue;
                        })
                        .catch(() => {
                            this.$console.log("Error settings user prefs for activities per page")
                        });
                }
                this.activitiesPerPage = value;
                this.loadActivities();
            },
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
                
                if (this.tab == 'processes') {
                    this.processesPage = page;
                    this.loadProcesses();
                } else {
                    this.activitiesPage = page;
                    this.loadActivities();
                }
            },
            loadActivities() {
                this.isLoading = true;

                let dataInit = this.searchDates && this.searchDates[0] ? moment(this.searchDates[0]).format('YYYY-MM-DD') : null; 
                let dataEnd = this.searchDates && this.searchDates[1] ? moment(this.searchDates[1]).format('YYYY-MM-DD') : null; 

                if(this.isRepositoryLevel) {
                    this.fetchActivities({
                        'page': this.activitiesPage,
                        'activitiesPerPage': this.activitiesPerPage,
                        'search': this.searchQuery,
                        'searchDates': [dataInit, dataEnd]
                    })
                        .then((res) => {
                            this.isLoading = false;
                            this.totalActivities = res.total;
                        })
                        .catch(() => {
                            this.isLoading = false;
                        });
                } else if (!this.isRepositoryLevel && !this.isItemLevel) {
                    this.fetchCollectionActivities({
                        'page': this.activitiesPage,
                        'activitiesPerPage': this.activitiesPerPage,
                        'collectionId': this.$route.params.collectionId,
                        'search': this.searchQuery,
                        'searchDates': [dataInit, dataEnd]
                    })
                        .then((res) => {
                            this.isLoading = false;
                            this.totalActivities = res.total;
                        })
                        .catch(() => {
                            this.isLoading = false;
                        });
                } else {
                    this.fetchItemActivities({
                        'page': this.activitiesPage,
                        'activitiesPerPage': this.activitiesPerPage,
                        'itemId': this.$route.params.itemId,
                        'search': this.searchQuery,
                        'searchDates': [dataInit, dataEnd]
                    })
                        .then((res) => {
                            this.isLoading = false;
                            this.totalActivities = res.total;
                        })
                        .catch(() => {
                            this.isLoading = false;
                        });
                }
            },
            loadProcesses() {
                this.isLoading = true;

                this.fetchProcesses({
                    page: this.processesPage,
                    processesPerPage: this.processesPerPage,
                    shouldUpdateStore: true
                })
                    .then((res) => {
                        this.isLoading = false;
                        this.total = res.total;
                    })
                    .catch(() => {
                        this.isLoading = false;
                    });
            },
            getLastActivityNumber() {
                let last = (Number(this.activitiesPerPage * (this.activitiesPage - 1)) + Number(this.activitiesPerPage));
                return last > this.totalActivities ? this.totalActivities : last;
            },
            getLastProcessesNumber() {
                let last = (Number(this.processesPerPage * (this.processesPage - 1)) + Number(this.processesPerPage));
                return last > this.total ? this.total : last;
            },
            searchActivities() {
                this.activitiesPage = 1;
                this.loadActivities();
            },
            clearSearchDates() {
                this.searchDates = null;
                this.searchActivities();
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
        },
        computed: {
            activities(){
                let activitiesList = this.getActivities();

                for (let activity of activitiesList)
                    activity['by'] = this.$i18n.get('info_by') +
                        activity['user_name'] + '<br>' + this.$i18n.get('info_date') +
                        moment(activity['log_date'], 'YYYY-MM-DD h:mm:ss').format('DD/MM/YYYY, hh:mm:ss');

                return activitiesList;
            },
            processes(){
                return this.getProcesses();
            }
        },
        created() {
            this.activitiesPerPage = this.$userPrefs.get('activities_per_page');
            this.processesPerPage = this.$userPrefs.get('processes_per_page');
            this.isRepositoryLevel = (this.$route.params.collectionId === undefined);
            this.isItemLevel = (!this.isRepositoryLevel && this.$route.params.itemId);
        },
        mounted(){
            if (!this.isRepositoryLevel)
                this.$root.$emit('onCollectionBreadCrumbUpdate', [{ path: '', label: this.$i18n.get('activities') }]);

            if (this.$route.query.tab == 'processes' && this.isRepositoryLevel)
                this.tab = 'processes';

            if (this.tab != 'processes') {
                if (this.activitiesPerPage != this.$userPrefs.get('activities_per_page'))
                    this.activitiesPerPage = this.$userPrefs.get('activities_per_page');

                if (!this.activitiesPerPage) {
                    this.activitiesPerPage = 12;
                    this.$userPrefs.set('activities_per_page', 12);
                }
                this.loadActivities();
            } else {
                if (this.processesPerPage != this.$userPrefs.get('processes_per_page'))
                    this.processesPerPage = this.$userPrefs.get('processes_per_page');

                if (!this.processesPerPage) {
                    this.processesPerPage = 12;
                    this.$userPrefs.set('processes_per_page', 12);
                }
                this.loadProcesses();
            }
        }
    }
</script>

<style lang="scss" scoped>
    @import '../../scss/_variables.scss';

    .sub-header {
        min-height: $subheader-height;
        height: $header-height;
        padding-left: 0;
        padding-right: 0;
        border-bottom: 1px solid #ddd;
        display: inline-flex;
        justify-content: space-between;
        align-items: center;
        width: 100%;

        .header-item {
            margin-bottom: 0 !important;

            &:not(:last-child) {
                padding-right: 0.5em;
            }

            .label {
                font-size: 0.875rem;
                font-weight: normal;
                margin-top: 3px;
                margin-bottom: 2px;
                cursor: default;
            }

            .button {
                display: flex;
                align-items: center;
                border-radius: 0 !important;
                height: 1.95rem !important;
            }
            
            .field {
                align-items: center;
            }

            .gray-icon, .gray-icon .icon {
                color: $gray4 !important;
                padding-right: 10px;
            }
            .gray-icon .icon i::before, 
            .gray-icon i::before {
                font-size: 1.3125rem !important;
                max-width: 26px;
            }

            .icon {
                pointer-events: all;
                cursor: pointer;
                color: $blue5;
                height: 27px;
                font-size: 18px !important;
                height: 1.75rem !important;
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
