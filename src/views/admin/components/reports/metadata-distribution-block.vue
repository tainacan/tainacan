<template>
    <div 
            v-if="hasChartSeries"
            :style="{
                maxHeight: distributionChartMaxHeight + 'px'
            }"
            class="report-card metadata-distribution-box">
        <div
                class="report-chart"
                :class="{ 'is-reloading-cache': isReloadingChart }"
                :aria-busy="isReloadingChart ? 'true' : 'false'">
            <apexchart
                    ref="metadataDistributionChart"
                    type="bar"
                    :height="distributionChartHeight"
                    :series="chartSeries"
                    :options="chartOptions" />
        </div>
        <slot />
    </div>
    <div 
            v-else
            style="min-height:740px"
            class="skeleton report-card metadata-distribution-box" />
</template>

<script>
import { mapGetters } from 'vuex';
import { reportsChartMixin } from '../../js/mixins';

export default {
    mixins: [ reportsChartMixin ],
    computed: {
        ...mapGetters('report', {
            horizontalBarChartOptions: 'getHorizontalBarChartOptions',
        }),
        distributionChartHeight() {
            if (!this.chartData.totals?.metadata) return 630;
            const height = 100 + (this.chartData.totals.metadata.total * 36);
            return height > 630 ? height : 630;
        },
        distributionChartMaxHeight() {
            if (!this.chartData.totals?.metadata) return 660;
            const height = 170 + (this.chartData.totals.metadata.total * 36);
            return height <= 660 ? height : 660;
        }
    },
    watch: {
        chartData: {
            handler() {
                this.buildMetadataDistributionChart();
            },
            immediate: true,
            deep: true
        }
    },
    methods: {
        buildMetadataDistributionChart() {
            if (this.chartData.distribution) {
                // Building Metadata Distribution Bar chart
                const orderedMetadataDistributions = Object.values(this.chartData.distribution).sort((a, b) => b.fill_percentage - a.fill_percentage );
                let metadataDistributionValues = [];
                let metadataDistributionValuesInverted = [];
                let metadataDistributionLabels = [];
                const metadataCount = 100 + (this.chartData.totals.metadata.total * 36);

                orderedMetadataDistributions.forEach(metadataDistribution => {
                    metadataDistributionValues.push(parseFloat(metadataDistribution.fill_percentage));
                    metadataDistributionValuesInverted.push(100.0000 - parseFloat(metadataDistribution.fill_percentage).toFixed(4));
                    metadataDistributionLabels.push(metadataDistribution.name);
                })

                // Sets first metadatum as the selected one 
                if (orderedMetadataDistributions.length)
                    this.selectedMetadatum = orderedMetadataDistributions[0].id;

                this.chartSeries = [
                    { 
                        name: this.$i18n.get('label_filled'),
                        data: metadataDistributionValues
                    },
                    { 
                        name: this.$i18n.get('label_not_filled'),
                        data: metadataDistributionValuesInverted
                    }
                ];

                if (!this.chartOptions.chart) {
                    this.chartOptions = {
                        ...this.horizontalBarChartOptions,
                        ...{
                            chart: {
                                type: 'bar',
                                height: metadataCount,
                                stacked: true,
                                stackType: '100%',
                                toolbar: {
                                    show: true,
                                    export: {
                                        scale: 3
                                    }
                                },
                                zoom: {
                                    type: 'y',
                                    enabled: true,
                                    autoScaleYaxis: true,
                                }
                            },
                            title: {
                                text: this.$i18n.get('label_fill_distribution')
                            },
                            labels: metadataDistributionLabels,
                            tooltip: {
                                y: {
                                    formatter: (val) => val + "%"
                                }
                            },
                            yaxis: {
                                title: {
                                    text: ''
                                },
                                labels: {
                                    maxWidth: 110
                                },
                                tooltip: { enabled: true }
                            },
                            colors: ['#187181', '#dbdbdb'],
                            fill: {
                                colors: ['#187181', '#dbdbdb']
                            },
                            dataLabels: {
                                style: {
                                    colors: ['#ffffff', '#373839']
                                },
                                formatter(val) {
                                    return (!Number.isNaN(val) && val > 0) ? (val.toFixed(2) + '%') : ''
                                },
                            },
                            states: {
                                normal: {
                                    filter: {
                                        type: 'none',
                                        value: 0,
                                    }
                                },
                                hover: {
                                    filter: {
                                        type: 'darken',
                                        value: 0.85,
                                    }
                                },
                            }
                        }
                    }
                } else {
                    this.chartOptions.labels = metadataDistributionLabels;
                    if (this.chartOptions.chart)
                        this.chartOptions.chart.height = metadataCount;
                    this.$nextTick(() => {
                        if (this.$refs.metadataDistributionChart && this.$refs.metadataDistributionChart.updateOptions) {
                            this.$refs.metadataDistributionChart.updateOptions({
                                labels: metadataDistributionLabels,
                                chart: { height: metadataCount }
                            }, false, true);
                        }
                    });
                }
            } else {
                this.chartSeries = [];
            }
        }
    }
}
</script>

<style lang="scss" scoped>
.report-card.metadata-distribution-box {
    grid-row: span 2;
    padding-bottom: 2rem;
    overflow: hidden;
    min-height: 660px !important;
    display: flex;
    flex-direction: column;

    .report-chart {
        flex: 1 1 auto;
        min-height: 0;
        overflow-y: auto;
    }
}
</style>
