<template>
    <div>
        <apexchart
                v-if="!isBuildingChart && chartData && Object.values(chartData).length"
                height="380px"
                class="postbox"
                :series="chartSeries"
                :options="chartOptions" />
        <div 
                v-else
                style="min-height=380px"
                class="skeleton postbox" />
    </div>
</template>

<script>
import { mapGetters } from 'vuex';
import { reportsChartMixin } from '../js/reports-mixin';

export default {
    mixins: [ reportsChartMixin ],
    computed: {
        ...mapGetters('report', {
            stackedBarChartOptions: 'getStackedBarChartOptions',
        }),
    },
    watch: {
        chartData: {
            handler() {
                this.buildCollectionsList();
            },
            immediate: true
        }
    },
    methods: {
        buildCollectionsList() {

            this.isBuildingChart = true;

            // Building Collections items chart
            const orderedCollections = Object.values(this.chartData).sort((a, b) =>  b.items.total - a.items.total);
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

            this.chartSeries = [
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
            
            this.chartOptions = {
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

            this.isBuildingChart = false;
        }
    }
}
</script>