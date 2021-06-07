<template>
    <div>
        <div 
                v-if="!isFetchingData && chartData.totals && chartData.totals.metadata && !isBuildingChart"
                :style="{
                    maxHeight: ((170 + (chartData.totals.metadata.total * 36)) <= 690 ? (170 + (chartData.totals.metadata.total * 36)) : 690) + 'px'
                }"
                class="postbox metadata-distribution-box">
            <apexchart
                    :height="100 + (chartData.totals.metadata.total * 36) > 630 ? 100 + (chartData.totals.metadata.total * 36) : 630"
                    :series="chartSeries"
                    :options="chartOptions" />
        </div>
        <div 
                v-else
                style="min-height:740px"
                class="skeleton postbox metadata-distribution-box" />
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
            horizontalBarChartOptions: 'getHorizontalBarChartOptions',
        })
    },
    watch: {
        chartData: {
            handler() {
                this.buildMetadataDistributionChart();
            },
            immediate: true,
        }
    },
    methods: {
        buildMetadataDistributionChart() {
            this.isBuildingChart = true;

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
                this.chartOptions = {
                    ...this.horizontalBarChartOptions,
                    ...{
                        chart: {
                            type: 'bar',
                            height: metadataCount,
                            stacked: true,
                            stackType: '100%',
                            toolbar: {
                                show: true
                            },
                            zoom: {
                                type: 'y',
                                enabled: true,
                                autoScaleYaxis: true,
                            }
                        },
                        title: {
                            text: this.$i18n.get('label_metadata_fill_distribution')
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
                        colors: ['#298596', '#dbdbdb'],
                        fill: {
                            colors: ['#298596', '#dbdbdb']
                        },
                        dataLabels: {
                            style: {
                                colors: ['#ffffff', '#454647']
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
            }

            setTimeout(() => this.isBuildingChart = false, 300);
        }
    }
}
</script>

<style lang="scss" scoped>
.postbox.metadata-distribution-box {
    margin: 0px 0px 0.75rem 1.5rem !important;
    padding-bottom: 2rem;
    overflow-y: auto;
    min-height: 690px !important;
}
</style>