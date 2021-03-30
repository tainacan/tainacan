<template>
    <div class="column is-full">
        <apexchart
                v-if="!isBuildingCollectionsList && collectionsList && Object.values(collectionsList).length"
                height="380px"
                class="postbox"
                :series="collectionsListChartSeries"
                :options="collectionsListChartOptions" />
        <div 
                v-else
                style="min-height=380px"
                class="skeleton postbox" />
    </div>
</template>

<script>
import { mapGetters } from 'vuex';

export default {
    data() {
        return {
            isBuildingCollectionsList: false,
            metadataListChartSeries: [],
            metadataListChartOptions: {},
        }
    },
    computed: {
        ...mapGetters('report', {
            collectionsList: 'getCollectionsList',
            stackedBarChartOptions: 'getStackedBarChartOptions',
        }),
    },
    watch: {
        collectionsList: {
            handler() {
                this.buildCollectionsList();
            },
            immediate: true
        }
    },
    methods: {
        buildCollectionsList() {

            this.isBuildingCollectionsList = true;

            // Building Collections items chart
            const orderedCollections = Object.values(this.collectionsList).sort((a, b) =>  b.items.total - a.items.total);
            let privateItems = [];
            let publicItems = [];
            let trashItems = [];
            let draftItems = [];
            let collectionsLabels = [];

            orderedCollections.forEach(collection => {
                privateItems.push(collection.items.private);
                publicItems.push(collection.items.publish);
                draftItems.push(collection.items.draft);
                trashItems.push(collection.items.trash);
                collectionsLabels.push(collection.name);
            });

            this.collectionsListChartSeries = [
                {
                    name: this.$i18n.get('status_publish'),
                    data: publicItems
                },
                {
                    name: this.$i18n.get('status_private'),
                    data: privateItems
                },
                {
                    name: this.$i18n.get('status_draft'),
                    data: draftItems
                },
                {
                    name: this.$i18n.get('status_trash'),
                    data: trashItems
                }
            ];
            
            this.collectionsListChartOptions = {
                ...this.stackedBarChartOptions, 
                ...{
                    title: {
                        text: this.$i18n.get('label_items_per_collection')
                    },
                    xaxis: {
                        type: 'category',
                        tickPlacement: 'on',
                        categories: collectionsLabels,
                        labels: {
                            show: true,
                            trim: true,
                            hideOverlappingLabels: false
                        },
                        tooltip: { enabled: true }
                    },
                    yaxis: {
                        title: {
                            text: this.$i18n.get('items')
                        }
                    }
                }
            }

            this.isBuildingCollectionsList = false;
        }
    }
}
</script>