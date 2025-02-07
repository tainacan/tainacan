<template>
    <div
            :class="{
                'repository-level-page': isRepositoryLevel,
                'page-container': isRepositoryLevel,
                'tainacan-modal-content': isItemLevel
            }">
        <tainacan-title v-if="!isItemLevel" />
        <header 
                v-else
                class="tainacan-modal-title">
            <h2>{{ $i18n.get('label_item_activities') }}</h2>
        </header>

        <div class="sub-header">

            <b-field 
                    class="header-item"
                    style="margin-right: auto; margin-left: 0;">
                <b-input 
                        v-model="searchQuery"
                        :placeholder="$i18n.get('instruction_search')"
                        type="search"
                        size="is-small"
                        :aria-label="$i18n.get('instruction_search') + ' ' + $i18n.get('activities')"
                        autocomplete="on"
                        icon-right="magnify"
                        icon-right-clickable
                        @keyup.enter="searchActivities()"
                        @icon-right-click="searchActivities()" />
            </b-field>

            <b-field class="header-item">
                <b-autocomplete
                        clearable
                        :data="users"
                        :placeholder="$i18n.get('instruction_type_search_users_filter')"
                        keep-first
                        open-on-focus
                        :loading="isFetchingUsers"
                        field="name"
                        icon="account"
                        check-infinite-scroll
                        @update:model-value="fetchUsersForFiltering"
                        @focus.once="($event) => fetchUsersForFiltering($event.target.value)"
                        @select="filterActivitiesByUser"
                        @infinite-scroll="fetchMoreUsersForFiltering">
                    <template #default="props">
                        <div class="media">
                            <div
                                    v-if="props.option.avatar_urls && props.option.avatar_urls['24']"
                                    class="media-left">
                                <img
                                        width="24"
                                        :src="props.option.avatar_urls['24']">
                            </div>
                            <div class="media-content">
                                {{ props.option.name }}
                            </div>
                        </div>
                    </template>
                    <template 
                            v-if="!isFetchingUsers"
                            #empty>
                        {{ $i18n.get('info_no_user_found') }}
                    </template>
                </b-autocomplete>
            </b-field>

            <b-field class="header-item">
                <b-datepicker
                        ref="datepicker"
                        v-model="searchDates"
                        :placeholder="$i18n.get('label_range_of_dates')"
                        range
                        :trap-focus="false"
                        :date-formatter="(date) => dateFormatter(date)"
                        :date-parser="(date) => dateParser(date)"
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
                        ]"
                        @update:model-value="searchActivities()" />
                <p
                        v-if="searchDates && searchDates.length != 0"
                        class="control">
                    <button 
                            class="button"
                            @click="clearSearchDates()">
                        <span class="icon"><i class="tainacan-icon tainacan-icon-close" /></span>
                    </button>
                </p>
            </b-field>

        </div>

        <div :class="{ 'above-subheader': isRepositoryLevel }">

            <b-loading
                    v-model="isLoading"
                    :is-full-page="false" 
                    :can-cancel="false" />

            <activities-list
                    v-if="$userCaps.hasCapability('tnc_rep_read_logs')"
                    :is-loading="isLoading"
                    :total-activities="totalActivities"
                    :page="activitiesPage"
                    :activities-per-page="activitiesPerPage"
                    :activities="activities" />
            <template v-if="!$userCaps.hasCapability('tnc_rep_read_logs')">
                <section class="section">
                    <div class="content has-text-grey has-text-centered">
                        <p>
                            <span class="icon">
                                <i class="tainacan-icon tainacan-icon-30px tainacan-icon-activities" />
                            </span>
                        </p>
                        <p>{{ $i18n.get('info_can_not_read_activities') }}</p>
                    </div>
                </section>
            </template>

            <!-- Footer -->
            <div
                    v-if="totalActivities > 0"
                    class="pagination-area">
                <div class="shown-items">
                    {{
                        $i18n.get('info_showing_activities') + ' ' +
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
                                :model-value="activitiesPerPage"
                                :disabled="activities.length <= 0"
                                @update:model-value="onChangeActivitiesPerPage">
                            <option value="12">
                                12
                            </option>
                            <option value="24">
                                24
                            </option>
                            <option value="48">
                                48
                            </option>
                            <option :value="maxActivitiesPerPage">
                                {{ maxActivitiesPerPage }}
                            </option>
                        </b-select>
                    </b-field>
                </div>
                <div class="pagination">
                    <b-pagination
                            v-model="activitiesPage"
                            :total="totalActivities"
                            order="is-centered"
                            size="is-small"
                            :per-page="activitiesPerPage"
                            :aria-next-label="$i18n.get('label_next_page')"
                            :aria-previous-label="$i18n.get('label_previous_page')"
                            :aria-page-label="$i18n.get('label_page')"
                            :aria-current-label="$i18n.get('label_current_page')"
                            @change="onPageChange" />
                </div>
            </div>

        </div>
    </div>
</template>

<script>
    import ActivitiesList from "../../components/lists/activities-list.vue";
    import { dateInter } from "../../js/mixins";
    import { mapActions, mapGetters } from 'vuex';
    import moment from 'moment'

    export default {
        name: 'ActivitiesPage',
        components: {
            ActivitiesList,
        },
        mixins: [ dateInter ],
        data() {
            return {
                isLoading: false,
                totalActivities: 0,
                activitiesPage: 1,
                activitiesPerPage: 12,
                isRepositoryLevel: false,
                isItemLevel: false,
                searchQuery: '',
                searchDates: [],
                users: [],
                isFetchingUsers: false,
                userIdForFiltering: null,
                usersForFilteringSearchQuery: '',
                usersForFilteringSearchPage: 1,
                totalUsers: 0,
                maxActivitiesPerPage: tainacan_plugin.api_max_items_per_page ? Number(tainacan_plugin.api_max_items_per_page) : 96
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
            }
        },
        created() {
            this.activitiesPerPage = this.$userPrefs.get('activities_per_page');
            this.isRepositoryLevel = (this.$route.params.collectionId === undefined);
            this.isItemLevel = (!this.isRepositoryLevel && this.$route.params.itemId);
        },
        mounted() {
            if (this.activitiesPerPage != this.$userPrefs.get('activities_per_page'))
                this.activitiesPerPage = this.$userPrefs.get('activities_per_page');

            if (!this.activitiesPerPage) {
                this.activitiesPerPage = 12;
                this.$userPrefs.set('activities_per_page', 12);
            }
            this.loadActivities();
        },
        methods: {
            ...mapActions('activity', [
                'fetchActivities',
                'fetchCollectionActivities',
                'fetchItemActivities',
                'fetchUsers'
            ]),
            ...mapGetters('activity', [
                'getActivities'
            ]),
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
            onPageChange(page) {
                this.activitiesPage = page;
                this.loadActivities();
            },
            loadActivities() {
                this.isLoading = true;

                let dataInit = this.searchDates && this.searchDates[0] ? moment(this.searchDates[0]).format('YYYY-MM-DD') : null; 
                let dataEnd = this.searchDates && this.searchDates[1] ? moment(this.searchDates[1]).format('YYYY-MM-DD') : null; 

                if(this.isRepositoryLevel) {
                    this.fetchActivities({
                        page: this.activitiesPage,
                        activitiesPerPage: this.activitiesPerPage,
                        search: this.searchQuery,
                        searchDates: [dataInit, dataEnd],
                        authorId: this.userIdForFiltering
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
                        page: this.activitiesPage,
                        activitiesPerPage: this.activitiesPerPage,
                        collectionId: this.$route.params.collectionId,
                        search: this.searchQuery,
                        searchDates: [dataInit, dataEnd],
                        authorId: this.userIdForFiltering
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
                        page: this.activitiesPage,
                        activitiesPerPage: this.activitiesPerPage,
                        itemId: this.$route.params.itemId,
                        search: this.searchQuery,
                        searchDates: [dataInit, dataEnd],
                        authorId: this.userIdForFiltering
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
            clearSearchDates() {
                this.searchDates = null;
                this.loadActivities();
            },
            getLastActivityNumber() {
                let last = (Number(this.activitiesPerPage * (this.activitiesPage - 1)) + Number(this.activitiesPerPage));
                return last > this.totalActivities ? this.totalActivities : last;
            },
            searchActivities() {
                this.activitiesPage = 1;
                this.loadActivities();
            },
            filterActivitiesByUser(user) {
                this.userIdForFiltering = user != null && user.id != undefined ? user.id : null;
                this.loadActivities();
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
            },
            fetchUsersForFiltering: _.debounce(function (search) {

                // String update
                if (search != this.usersForFilteringSearchQuery) {
                    this.usersForFilteringSearchQuery = search;
                    this.users = [];
                    this.usersForFilteringSearchPage = 1;
                } 
                
                // String cleared
                if (!search.length) {
                    this.usersForFilteringSearchQuery = search;
                    this.users = [];
                    this.usersForFilteringSearchPage = 1;
                }

                // No need to load more
                if (this.usersForFilteringSearchPage > 1 && this.users.length > this.totalUsers)
                    return;

                this.isFetchingUsers = true;

                this.fetchUsers({ search: this.usersForFilteringSearchQuery, page: this.usersForFilteringSearchPage })
                    .then((res) => {
                        if (res.users) {
                            for (let user of res.users)
                                this.users.push(user); 
                        }
                        
                        if (res.totalUsers)
                            this.totalUsers = res.totalUsers;

                        this.usersForFilteringSearchPage++;
                        
                        this.isFetchingUsers = false;
                    })
                    .catch((error) => {
                        this.$console.error(error);
                        this.isFetchingUsers = false;
                    });
            }, 500),
            fetchMoreUsersForFiltering: _.debounce(function () {
                this.fetchUsersForFiltering(this.usersForFilteringSearchQuery)
            }, 250),
        }
    }
</script>

<style lang="scss" scoped>
    @import '../../scss/_variables.scss';

    .sub-header {
        @include logs-container();

        .header-item {
            margin-bottom: 0 !important;
            min-height: 1.875em;
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
                border-radius: var(--tainacan-button-border-radius, 4px) !important;
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

    .tainacan-modal-content .table-container {
        max-height: calc(100vh - 412px);
        overflow-y: scroll;
        margin-bottom: 0;
        padding-bottom: 1.5rem;
    }

    .above-subheader {
        margin-bottom: 0;
        margin-top: 0;
        min-height: 100%;
        height: auto;
    }
</style>
