<template>
    <div>
        <div
                :class="{
                    'primary-page': isRepositoryLevel,
                    'page-container': isRepositoryLevel
                }">
            <tainacan-title />
            <div :class="{ 'above-subheader': isRepositoryLevel }">
                <events-list
                        :is-loading="isLoading"
                        :total-events="totalEvents"
                        :page="page"
                        :events-per-page="eventsPerPage"
                        :events="events"/>
                <!-- Footer -->
                <div
                        class="pagination-area"
                        v-if="totalEvents > 0">
                    <div class="shown-items">
                        {{
                            $i18n.get('info_showing_events') +
                            (eventsPerPage * (page - 1) + 1) +
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
                                :current.sync="page"
                                order="is-centered"
                                size="is-small"
                                :per-page="eventsPerPage"/>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import EventsList from "../../components/lists/events-list.vue";
    import { mapActions, mapGetters } from 'vuex';
    import moment from 'moment'

    export default {
        name: 'EventsPage',
        data(){
            return {
                isLoading: false,
                totalEvents: 0,
                page: 1,
                eventsPerPage: 12,
                isRepositoryLevel: false
            }
        },
        components: {
            EventsList
        },
        methods: {
            ...mapActions('event', [
                'fetchEvents',
                'fetchCollectionEvents',
            ]),
            ...mapGetters('event', [
                'getEvents'
            ]),
            onChangeEventsPerPage(value) {
                let prevValue = this.eventsPerPage;
                this.eventsPerPage = value;
                this.$userPrefs.set('events_per_page', value,  prevValue);
                this.loadEvents();
            },
            onPageChange(page) {
                this.page = page;
                this.loadEvents();
            },
            loadEvents() {
                this.isLoading = true;

                if(this.isRepositoryLevel) {
                    this.fetchEvents({'page': this.page, 'eventsPerPage': this.eventsPerPage})
                        .then((res) => {
                            this.isLoading = false;
                            this.totalEvents = res.total;
                        })
                        .catch(() => {
                            this.isLoading = false;
                        });
                } else {
                    this.fetchCollectionEvents({'page': this.page, 'eventsPerPage': this.eventsPerPage, 'collectionId': this.$route.params.collectionId})
                        .then((res) => {
                            this.isLoading = false;
                            this.totalEvents = res.total;
                        })
                        .catch(() => {
                            this.isLoading = false;
                        });
                }
            },
            getLastEventNumber() {
                let last = (Number(this.eventsPerPage * (this.page - 1)) + Number(this.eventsPerPage));
                return last > this.totalEvents ? this.totalEvents : last;
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
            }
        },
        created() {
            this.isRepositoryLevel = (this.$route.params.collectionId === undefined);
            this.$userPrefs.get('events_per_page')
                .then((value) => {
                    this.eventsPerPage = value;
                })
                .catch(() => {
                    this.$userPrefs.set('events_per_page', 12, null);
                });
        },
        mounted(){
            this.loadEvents();

            if (!this.isRepositoryLevel) {
                document.getElementById('collection-page-container').addEventListener('scroll', ($event) => {
                    this.$emit('onShrinkHeader', ($event.originalTarget.scrollTop > 53)); 
                });
            }
        }
    }
</script>

<style lang="scss" scoped>
    @import '../../scss/_variables.scss';

    .sub-header {
        max-height: $header-height;
        height: $header-height;
        margin-left: -$page-small-side-padding;
        margin-right: -$page-small-side-padding;
        margin-top: -$page-small-top-padding;
        padding-top: $page-small-top-padding;
        padding-left: $page-small-side-padding;
        padding-right: $page-small-side-padding;
        border-bottom: 0.5px solid #ddd;

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
