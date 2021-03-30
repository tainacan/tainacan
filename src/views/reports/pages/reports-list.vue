<template>
    <div>
        <h1 class="wp-heading-inline">{{ $route.meta.title }}</h1>
        <select 
                name="select_collections"
                id="select_collections"
                @input="(inputEvent) => $router.push({ query: { collection: inputEvent.target.value } })"
                :value="selectedCollection">
            <option value="default">
                {{ $i18n.get('repository') }}
            </option>
            <option 
                    v-for="(collection, index) of collections"
                    :key="index"
                    :value="collection.id">
                {{ collection.name }}
            </option>
        </select>
        <div class="columns is-multiline">
            <div 
                    :class="{ 'is-three-fifths-desktop': !isRepositoryLevel }"
                    class="column is-full columns is-multiline">
                <div 
                        v-if="isRepositoryLevel"
                        class="column is-full is-one-third-tablet has-text-centered">
                    <number-block
                            :class="{ 'skeleton': isFetchingSummary }"
                            class="postbox"
                            :summary="summary"
                            entity-type="collections" />
                </div>
                <div
                        :class="{ 'is-one-third-tablet': isRepositoryLevel }"
                        class="column is-full is-half-tablet has-text-centered">
                    <number-block 
                            :class="{ 'skeleton': isFetchingSummary }"
                            class="postbox"
                            :summary="summary"
                            entity-type="items"/>
                </div>
                <div 
                        v-if="isRepositoryLevel"
                        class="column is-full is-one-third-tablet has-text-centered">
                    <number-block
                            :class="{ 'skeleton': isFetchingSummary }"
                            class="postbox"
                            :summary="summary"
                            entity-type="taxonomies" />
                </div>
                <div 
                        v-else
                        class="column is-full is-half-tablet has-text-centered">
                    <number-block
                            :class="{ 'skeleton': isFetchingMetadata }"
                            class="postbox"
                            :summary="metadata"
                            entity-type="metadata" />
                </div>
                <collections-list-block
                        v-if="isRepositoryLevel" />

                <terms-per-taxonomy-block 
                        v-if="isRepositoryLevel"/>
                
                <items-per-term-block 
                        v-if="isRepositoryLevel" />

                <metadata-types-block
                        v-if="!isRepositoryLevel" />
            </div>
            <metadata-distribution-block 
                    v-if="!isRepositoryLevel"/>
        </div>
        <div class="columns">
            <metadata-list-block 
                    v-if="!isRepositoryLevel" />
        </div>
    </div>
</template>

<script>
import { mapActions, mapGetters } from 'vuex';

export default { 
    name: "ReportsList",
    data() {
        return {
            selectedCollection: 'default',
            isFetchingCollections: false,
            isFetchingSummary: false,
            isFetchingCollectionsList: false,
            isFetchingMetadata: false,
            isFetchingActivities: false,
            activitiesChartSeries: [],
            activitiesChartOptions: {}
        }
    },
    computed: {
        ...mapGetters('collection', {
            collections: 'getCollections',
        }),
        ...mapGetters('report', {
            summary: 'getSummary',
            metadata: 'getMetadata',
            activities: 'getActivities',
            //heatMapChartOptions: 'getHeatMapChartOptions'
        }),
        isRepositoryLevel() {
            return !this.selectedCollection || this.selectedCollection == 'default';
        }
    },
    watch: {
        '$route.query': {
            handler(to) {
                this.selectedCollection = to['collection'] ? to['collection'] : 'default';
                this.loadSummary();
                this.loadMetadata();
                this.loadActivities();

                if (!this.selectedCollection || this.selectedCollection == 'default')
                    this.loadCollectionsList();
                
            },
            immediate: true
        }
    },
    created() {
        // Obtains colleciton id from query, if passed
        this.selectedCollection = this.$route.query['collection'] ? this.$route.query['collection'] : 'default';
        
        // Loads collection for the select input
        this.loadCollections();
    },
    methods: {
        ...mapActions('collection', [
            'fetchAllCollectionNames'
        ]),
        ...mapActions('report', [
            'fetchSummary',
            'fetchCollectionsList',
            'fetchMetadata',
            'fetchActivities'
        ]),
        loadCollections() {
            this.isFetchingCollections = true;
            this.fetchAllCollectionNames()
                .then(() => this.isFetchingCollections = false)
                .catch(() => this.isFetchingCollections = false);
        },
        loadSummary() {
            this.isFetchingSummary = true;
            this.fetchSummary({ collectionId: this.selectedCollection })
                .then(() => this.isFetchingSummary = false)
                .catch(() => this.isFetchingSummary = false);
        },
        loadMetadata() {
            this.isFetchingMetadata = true;
            this.fetchMetadata({ collectionId: this.selectedCollection })
                .then(() => this.isFetchingMetadata = false)
                .catch(() => this.isFetchingMetadata = false);
        },
        loadCollectionsList() {
            this.isFetchingCollectionsList = true;
            this.fetchCollectionsList()
                .then(() => this.isFetchingCollectionsList = false)
                .catch(() => this.isFetchingCollectionsList = false);
        },
        loadActivities() {
            this.isFetchingActivities = true;
            this.fetchActivities({ collectionId: this.selectedCollection })
                .then(() => {

                    const daysWithActivities = this.activities && this.activities.totals && this.activities.totals.last_year && this.activities.totals.last_year.general ? this.activities.totals.last_year.general : []; 
                    if (daysWithActivities && daysWithActivities.length) {
                        
                        let everyDayOfTheYear = Array.from(new Array(7),
                            (val,index) => {
                                return {
                                    name: (index + 1),
                                    data: new Array(53).fill({ x: '', y: 0 })
                                }
                            }
                        );
                        
                        const firstDayOfTheWeekWithActivity = parseInt(daysWithActivities[0].day_of_week);
                        let dayWithActivityIndex = 0;
                        let daysToSkip = 0;

                        // Loop for each column (number of the week in the year)
                        for (let column = 0; column < 53; column++) {

                            // Loop for each line (number of the day in the week)
                            for (let line = 0; line < 7; line++) {

                                // If there are no more days with activities, get outta here
                                if (dayWithActivityIndex < daysWithActivities.length - 1) {
                                        
                                    // We should only begin inserting days from firstDayOfTheWeekWithActivity
                                    if (column == 0 && line < firstDayOfTheWeekWithActivity - 1) {
                                        continue;

                                    // On the first day, we don't need to calculate distances, just set the value and save the date
                                    } else if (column == 0 && line == firstDayOfTheWeekWithActivity - 1) {
                                        everyDayOfTheYear[line].data[column] = {
                                            x: '',
                                            y: parseInt(daysWithActivities[dayWithActivityIndex].total)
                                        };

                                        const lastDayWithActivity = new Date(daysWithActivities[dayWithActivityIndex].date);
                                        dayWithActivityIndex++;

                                        const nextDayWithActivity = new Date(daysWithActivities[dayWithActivityIndex].date);

                                        daysToSkip = Math.floor( (nextDayWithActivity - lastDayWithActivity) / (1000 * 60 * 60 * 24) );
                                    } else {
                                        daysToSkip--;

                                        // If we don't have more days to skip, time to update values
                                        if ( daysToSkip <= 0) {
                                            everyDayOfTheYear[line].data[column] = {
                                                x: '',
                                                y: parseInt(daysWithActivities[dayWithActivityIndex].total)
                                            };

                                            const lastDayWithActivity = new Date(daysWithActivities[dayWithActivityIndex].date);
                                            dayWithActivityIndex++;

                                            const nextDayWithActivity = new Date(daysWithActivities[dayWithActivityIndex].date);

                                            daysToSkip = Math.floor( (nextDayWithActivity - lastDayWithActivity) / (1000 * 60 * 60 * 24) );
                                        }
                                    }
                                }
                            }
                        }
                        this.activitiesChartSeries = everyDayOfTheYear;
                        this.activitiesChartOptions = {
                             ...this.heatMapChartOptions,
                                title: {
                                    text: this.$i18n.get('label_activities_last_year')
                                },
                        };
                    } else {
                        this.activitiesChartSeries = [];
                        this.activitiesChartOptions = {
                             ...this.heatMapChartOptions,
                                title: {
                                    text: this.$i18n.get('label_activities_last_year')
                                },
                        }
                    }

                    this.isFetchingActivities = false;
                })
                .catch(() => this.isFetchingActivities = false);
        }
    }
}
</script>