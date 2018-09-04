<template>
    <div>
        <div
                :class="{
                    'repository-level-page': isRepositoryLevel,
                    'page-container': isRepositoryLevel
                }">
            <tainacan-title />
            <div :class="{ 'above-subheader': isRepositoryLevel }">

                <div 
                        v-if="isRepositoryLevel"
                        class="tabs">
                    <ul>
                        <li 
                                @click="onChangeTab('')"
                                :class="{ 'is-active': tab == undefined || tab == ''}"><a>{{ $i18n.get('events') }}</a></li>
                        <li 
                                @click="onChangeTab('processes')"
                                :class="{ 'is-active': tab == 'processes'}"><a>{{ $i18n.get('processes') }}</a></li>
                    </ul>
                </div>

                <b-loading 
                        :is-full-page="false"
                        :active.sync="isLoading" 
                        :can-cancel="false"/>
                <events-list
                        v-if="tab != 'processes'"
                        :is-loading="isLoading"
                        :total-events="totalEvents"
                        :page="eventsPage"
                        :events-per-page="eventsPerPage"
                        :events="events"/>
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
                                <activities-icon />
                            </p>
                            <p v-if="status == undefined || status == ''">{{ $i18n.get('info_no_process') }}</p>
                        </div>
                    </section>
                </div>

                <!-- Footer -->
                <div
                        class="pagination-area"
                        v-if="tab != 'processes' && totalEvents > 0">
                    <div class="shown-items">
                        {{
                            $i18n.get('info_showing_events') +
                            (eventsPerPage * (eventsPage - 1) + 1) +
                            $i18n.get('info_to') +
                            getLastEventNumber() +
                            $i18n.get('info_of') + totalEvents + '.'
                        }}
                    </div>
                    <div class="items-per-page">
                        <b-field
                                horizontal
                                :label="$i18n.get('label_events_per_page')">
                            <b-select
                                    :value="eventsPerPage"
                                    @input="onChangeEventsPerPage"
                                    :disabled="events.length <= 0">
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
                                :total="totalEvents"
                                :current.sync="eventsPage"
                                order="is-centered"
                                size="is-small"
                                :per-page="eventsPerPage"/>
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
                                :per-page="processesPerPage"/>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import EventsList from "../../components/lists/events-list.vue";
    import ProcessesList from "../../components/lists/processes-list.vue";
    import ActivitiesIcon from '../../components/other/activities-icon.vue';
    import { mapActions, mapGetters } from 'vuex';
    import moment from 'moment'

    export default {
        name: 'EventsPage',
        data(){
            return {
                isLoading: false,
                totalEvents: 0,
                eventsPage: 1,
                processesPage: 1,
                eventsPerPage: 12,
                processesPerPage: 12,
                isRepositoryLevel: false,
                tab: ''
            }
        },
        components: {
            EventsList,
            ProcessesList,
            ActivitiesIcon
        },
        methods: {
            ...mapActions('event', [
                'fetchEvents',
                'fetchCollectionEvents',
            ]),
            ...mapGetters('event', [
                'getEvents'
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
                    this.loadEvents();
                    this.$router.push({query: {}});
                }
            },
            onChangeEventsPerPage(value) {
                
                if (value != this.eventsPerPage) {
                    this.$userPrefs.set('events_per_page', value)
                        .then((newValue) => {
                            this.eventsPerPage = newValue;
                        })
                        .catch(() => {
                            this.$console.log("Error settings user prefs for events per page")
                        });
                }
                this.eventsPerPage = value;
                this.loadEvents();
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
                    this.eventsPage = page;
                    this.loadEvents();
                }
            },
            loadEvents() {
                this.isLoading = true;

                if(this.isRepositoryLevel) {
                    this.fetchEvents({'page': this.eventsPage, 'eventsPerPage': this.eventsPerPage})
                        .then((res) => {
                            this.isLoading = false;
                            this.totalEvents = res.total;
                        })
                        .catch(() => {
                            this.isLoading = false;
                        });
                } else {
                    this.fetchCollectionEvents({'page': this.eventsPage, 'eventsPerPage': this.eventsPerPage, 'collectionId': this.$route.params.collectionId})
                        .then((res) => {
                            this.isLoading = false;
                            this.totalEvents = res.total;
                        })
                        .catch(() => {
                            this.isLoading = false;
                        });
                }
            },
            loadProcesses() {
                this.isLoading = true;

                this.fetchProcesses({ 'page': this.processesPage, 'processesPerPage': this.processesPerPage })
                    .then((res) => {
                        this.isLoading = false;
                        this.total = res.total;
                    })
                    .catch(() => {
                        this.isLoading = false;
                    });
            },
            getLastEventNumber() {
                let last = (Number(this.eventsPerPage * (this.eventPage - 1)) + Number(this.eventsPerPage));
                return last > this.totalEvents ? this.totalEvents : last;
            },
            getLastProcessesNumber() {
                let last = (Number(this.processesPerPage * (this.processesPage - 1)) + Number(this.processesPerPage));
                return last > this.total ? this.total : last;
            }
        },
        computed: {
            events(){
                let eventsList = this.getEvents();

                for (let event of eventsList)
                    event['by'] = this.$i18n.get('info_by') +
                        event['user_name'] + '<br>' + this.$i18n.get('info_date') +
                        moment(event['log_date'], 'YYYY-MM-DD h:mm:ss').format('DD/MM/YYYY, hh:mm:ss');

                return eventsList;
            },
            processes(){
                return this.getProcesses();
            }
        },
        created() {
            this.eventsPerPage = this.$userPrefs.get('events_per_page');
            this.processesPerPage = this.$userPrefs.get('processes_per_page');
            this.isRepositoryLevel = (this.$route.params.collectionId === undefined);
        },
        mounted(){
            if (this.$route.query.tab == 'processes' && this.isRepositoryLevel)
                this.tab = 'processes';

            if (this.tab != 'processes') {
                if (this.eventsPerPage != this.$userPrefs.get('events_per_page'))
                    this.eventsPerPage = this.$userPrefs.get('events_per_page');

                if (!this.eventsPerPage) {
                    this.eventsPerPage = 12;
                    this.$userPrefs.set('events_per_page', 12);
                }
                this.loadEvents();
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

    .activities-icon {
        height: 24px;
        width: 24px;
    }

    .sub-header {
        max-height: $header-height;
        height: $header-height;
        margin-left: -$page-small-side-padding;
        margin-right: -$page-small-side-padding;
        padding-left: $page-small-side-padding;
        padding-right: $page-small-side-padding;
        border-bottom: 1px solid #ddd;

        .header-item {
            display: inline-block;
            padding-right: 8em;
        }

        @media screen and (max-width: 769px) {
            height: 60px;
            margin-top: -0.5em;
            padding-top: 0.9em;

            .header-item {
                padding-right: 0.5em;
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
