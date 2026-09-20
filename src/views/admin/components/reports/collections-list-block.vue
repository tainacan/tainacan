<template>
    <div 
            v-if="hasChartSeries || (!isFetchingData && chartData && Object.values(chartData).length)"
            class="report-card is-full">
        <div
                class="report-chart"
                :class="{ 'is-reloading-cache': isReloadingChart }"
                :aria-busy="isReloadingChart ? 'true' : 'false'">
            <apexchart
                    v-if="hasChartSeries"
                    ref="collectionsChart"
                    type="bar"
                    height="380px"
                    :series="chartSeries"
                    :options="chartOptions" />
        </div>
        <slot />
    </div>
    <div 
            v-else-if="!isFetchingData && (!chartData || !Object.values(chartData).length)"
            style="min-height:380px"
            class="report-card is-full">
        <div class="empty-report-card-placeholder">
            <p class="title is-4">
                <span class="icon has-text-dark">
                    <i class="tainacan-icon tainacan-icon-collections tainacan-icon-1em" />
                </span>
                &nbsp;{{ $i18n.get('collections') }}
            </p>
            <br>
            <p class="subtitle is-6">
                {{ $i18n.get('info_no_collection_created') }}
            </p>
        </div>
    </div>
    <div 
            v-else
            style="min-height:380px"
            class="skeleton report-card is-full" />
</template>

<script>
import { mapGetters } from 'vuex';
import { reportsChartMixin } from '../../js/mixins';

export default {
    mixins: [ reportsChartMixin ],
    data() {
        return {
            collectionLabels: []
        }
    },
    computed: {
        ...mapGetters('report', {
            stackedBarChartOptions: 'getStackedBarChartOptions',
        })
    },
    watch: {
        chartData: {
            handler() {
                this.buildCollectionsList();
            },
            immediate: true,
            deep: true
        }
    },
    methods: {
        buildCollectionsList() {
            if (!this.chartData || !Object.values(this.chartData).length) {
                this.chartSeries = [];
                return;
            }

            const orderedCollections = Object.values(this.chartData).sort((a, b) =>  b.items.total - a.items.total);
            let privateItems = [];
            let publicItems = [];
            let pendingItems = [];
            let trashItems = [];
            let draftItems = [];
            let collectionsLabels = [];

            orderedCollections.forEach(collection => {
                privateItems.push(collection.items.private);
                pendingItems.push(collection.items.pending);
                publicItems.push(collection.items.publish);
                draftItems.push(collection.items.draft);
                trashItems.push(collection.items.trash);
                collectionsLabels.push(collection.name);
            });

            this.collectionLabels = collectionsLabels;
            this.chartSeries = [
                {
                    name: 'public',
                    data: publicItems
                },
                {
                    name: 'private',
                    data: privateItems
                },
                {
                    name: 'pending',
                    data: pendingItems
                },
                {
                    name: 'draft',
                    data: draftItems
                },
                {
                    name: 'trash',
                    data: trashItems
                }
            ];

            if (!this.chartOptions.chart) {
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
                                        <div class="tainacan-custom-tooltip__header">` + this.collectionLabels[dataPointIndex] + `</div>
                                        <div class="tainacan-custom-tooltip__body">
                                            <span>` + this.$statusHelper.getStatusLabel(w.config.series[seriesIndex].name) + `: <strong>` + series[seriesIndex][dataPointIndex] + `</strong></span>` +
                                        `</div></div>`;
                            }
                        },
                        legend: {
                            position: 'right',
                            offsetY: 40,
                            formatter: (seriesName) => {
                                return ['<span class="icon"><i class="tainacan-icon tainacan-icon-' + (seriesName === 'trash' ? 'delete' : ( seriesName === 'pending' ? 'waiting' : seriesName ) ) + '"></i></span>' + this.$statusHelper.getStatusLabel(seriesName) ]
                            }
                        }
                    }
                }
            } else {
                this.$nextTick(() => {
                    if (this.$refs.collectionsChart && this.$refs.collectionsChart.updateOptions) {
                        this.$refs.collectionsChart.updateOptions({
                            xaxis: { categories: collectionsLabels }
                        }, false, true);
                    }
                });
            }
        }
    }
}
</script>
