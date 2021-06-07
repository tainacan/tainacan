<template>
    <div>
        <apexchart
                v-if="!isFetchingData && !isBuildingChart && chartData && Object.values(chartData).length"
                height="380px"
                class="postbox"
                :series="chartSeries"
                :options="chartOptions" />
        <div 
                v-if="!isFetchingData && !isBuildingChart && (!chartData || !Object.values(chartData).length)"
                style="min-height:380px"
                class="postbox">
            <div class="empty-postbox-placeholder">
                <p class="title is-4">
                    <span class="icon has-text-gray">
                        <i class="tainacan-icon tainacan-icon-collections tainacan-icon-1-125em" />
                    </span>
                    &nbsp;{{ $i18n.get('collections') }}
                </p>
                <br>
                <p class="subtitle is-6">{{ $i18n.get('info_no_collection_created') }}</p>
            </div>
        </div>
        <div 
                v-if="isBuildingChart || isFetchingData"
                style="min-height:380px"
                class="skeleton postbox" />
        <slot />
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
                    name: 'public',//this.$i18n.get('status_publish'),
                    data: publicItems
                },
                {
                    name: 'private',//this.$i18n.get('status_private'),
                    data: privateItems
                },
                {
                    name: 'draft',//this.$i18n.get('status_draft'),
                    data: draftItems
                },
                {
                    name: 'trash',//this.$i18n.get('status_trash'),
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
                    },
                    yaxis: {
                        title: {
                            text: this.$i18n.get('items')
                        }
                    },
                    tooltip: {
                        custom: ({ series, seriesIndex, dataPointIndex, w }) => {
                            return `<div class="tainacan-custom-tooltip">
                                    <div class="tainacan-custom-tooltip__header">` + collectionsLabels[dataPointIndex] + `</div>
                                    <div class="tainacan-custom-tooltip__body">
                                        <span>` + this.$i18n.get('status_' + w.config.series[seriesIndex].name) + `: <strong>` + series[seriesIndex][dataPointIndex] + `</strong></span>` +
                                    `</div></div>`;
                        }
                    },
                    legend: {
                        position: 'right',
                        offsetY: 40,
                        formatter: (seriesName) => {
                            return ['<span class="icon"><i class="tainacan-icon tainacan-icon-' + (seriesName != 'trash' ? seriesName : 'delete') + '"></i></span>' + this.$i18n.get('status_' + seriesName) ]
                        }
                    }
                }
            }

            this.isBuildingChart = false;
        }
    }
}
</script>