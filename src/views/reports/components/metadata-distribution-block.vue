<template>
    <div>
        <div 
                v-if="!isFetchingData && chartData.totals && chartData.totals.metadata && !isBuildingChart"
                :style="{
                    maxHeight: ((170 + (chartData.totals.metadata.total * 36)) <= 690 ? (170 + (chartData.totals.metadata.total * 36)) : 690) + 'px'
                }"
                class="postbox metadata-distribution-box">
            <apexchart
                    :height="100 + (chartData.totals.metadata.total * 36)"
                    :series="chartSeries"
                    :options="chartOptions" />
        </div>
        <div 
                v-else
                style="min-height=740px"
                class="skeleton postbox metadata-distribution-box" />
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
                        colors: ['#25a189', '#a23939']
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
    overflow-y: auto;
}
</style>