<template>
    <div class="column is-full is-two-fifths-desktop">
        <div 
                v-if="metadata.totals && metadata.totals.metadata && !isBuildingMetadataDistributionChart"
                :style="{
                    maxHeight: ((170 + (metadata.totals.metadata.total * 36)) <= 690 ? (170 + (metadata.totals.metadata.total * 36)) : 690) + 'px'
                }"
                class="postbox metadata-distribution-box">
            <apexchart
                    :height="100 + (metadata.totals.metadata.total * 36)"
                    :series="metadataDistributionChartSeries"
                    :options="metadataDistributionChartOptions" />
        </div>
        <div 
                v-else
                style="min-height=740px"
                class="skeleton postbox metadata-distribution-box" />
    </div>
</template>

<script>
import { mapGetters } from 'vuex';

export default {
    data() {
        return {
            metadataDistributionChartSeries: [],
            metadataDistributionChartOptions: {},
            metadataDistributionChartHeight: 730,
            isBuildingMetadataDistributionChart: false
        }
    },
    computed: {
        ...mapGetters('report', {
            metadata: 'getMetadata',
            horizontalBarChartOptions: 'getHorizontalBarChartOptions',
        })
    },
    watch: {
        metadata() {
            this.buildMetadataDistributionChart();
        }
    },
    methods: {
        buildMetadataDistributionChart() {
            this.isBuildingMetadataDistributionChart = true;

            if (this.metadata.distribution) {
                // Building Metadata Distribution Bar chart
                const orderedMetadataDistributions = Object.values(this.metadata.distribution).sort((a, b) => b.fill_percentage - a.fill_percentage );
                let metadataDistributionValues = [];
                let metadataDistributionValuesInverted = [];
                let metadataDistributionLabels = [];
                const metadataCount = 100 + (this.metadata.totals.metadata.total * 36);

                orderedMetadataDistributions.forEach(metadataDistribution => {
                    metadataDistributionValues.push(parseFloat(metadataDistribution.fill_percentage));
                    metadataDistributionValuesInverted.push(100.0000 - parseFloat(metadataDistribution.fill_percentage).toFixed(4));
                    metadataDistributionLabels.push(metadataDistribution.name);
                })

                // Sets first metadatum as the selected one 
                if (orderedMetadataDistributions.length)
                    this.selectedMetadatum = orderedMetadataDistributions[0].id;

                this.metadataDistributionChartSeries = [
                    { 
                        name: this.$i18n.get('label_filled'),
                        data: metadataDistributionValues
                    },
                    { 
                        name: this.$i18n.get('label_not_filled'),
                        data: metadataDistributionValuesInverted
                    }
                ];
                this.metadataDistributionChartOptions = {
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

            setTimeout(() => this.isBuildingMetadataDistributionChart = false, 300);
        }
    }
}
</script>