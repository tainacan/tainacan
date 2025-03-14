<template>
    <div 
            v-if="!isFetchingData && chartData && chartData.totals && chartData.totals.metadata_per_type && !isBuildingChart"
            class="report-card is-medium">
        <label>{{ $i18n.get('metadata_types') }}&nbsp;</label>
        <div class="graph-mode-switch">
            <button 
                    :class="{ 'current': metadataTypeChartMode == 'bar' }"
                    @click="metadataTypeChartMode = 'bar'">
                <span class="sr-only">
                    {{ $i18n.get('label_bar_chart') }}
                </span>
                <span class="icon">
                    <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-text tainacan-icon-rotate-270" />
                </span>
            </button>
            <button 
                    :class="{ 'current': metadataTypeChartMode == 'circle' }"
                    @click="metadataTypeChartMode = 'circle'">
                <span class="sr-only">
                    {{ $i18n.get('label_pie_chart') }}
                </span>
                <span class="icon">
                    <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-waiting tainacan-icon-rotate-270" />
                </span>
            </button>
        </div>
        <apexchart
                height="380px"
                :series="chartSeries"
                :options="chartOptions" />
        <slot />
    </div>
    <div 
            v-else
            style="min-height: 390px"
            class="skeleton report-card is-medium" />
</template>

<script>
import { mapGetters } from 'vuex';
import { reportsChartMixin } from '../../js/mixins';

export default {
    mixins: [ reportsChartMixin ],
    data() {
        return {
            metadataTypeChartMode: 'bar'
        }
    },
    computed: {
        ...mapGetters('report', {
            donutChartOptions: 'getDonutChartOptions',
            stackedBarChartOptions: 'getStackedBarChartOptions',
        })
    },
    watch: {
        metadataTypeChartMode() {
            this.buildMetadataTypeChart();
        },
        chartData: {
            handler() {
                if (this.chartData && this.chartData.totals)
                    this.buildMetadataTypeChart();
            },
            immediate: true,
            deep: true
        }
    },
    methods: {
        buildMetadataTypeChart() {
            
            this.isBuildingChart = true;

            // Building Metadata Type Donut Chart
            let metadataTypeValues = [];
            let metadataTypeLabels = [];
            
            const orderedMetadataPerType = Object.values(this.chartData.totals.metadata_per_type).sort((a, b) => b.count - a.count);
            orderedMetadataPerType.forEach((metadataPerType) => {
                metadataTypeValues.push(metadataPerType.count ? metadataPerType.count : 0);
                metadataTypeLabels.push(metadataPerType.name ? metadataPerType.name : '');
            });
            
            this.chartSeries = this.metadataTypeChartMode == 'circle' ? metadataTypeValues : [ { name: this.$i18n.get('label_amount_of_metadata_of_type'), data: metadataTypeValues } ];
            
            if (this.metadataTypeChartMode == 'circle') {
                this.chartOptions = JSON.parse(JSON.stringify({
                    ...this.donutChartOptions,
                    ...{
                        title: {},
                        labels: metadataTypeLabels,
                    }
                }));
            } else {
                this.chartOptions = JSON.parse(JSON.stringify({
                    ...this.stackedBarChartOptions,
                    ...{
                        chart: {
                            type: 'bar',
                            height: 350,
                            stacked: false,
                            toolbar: {
                                show: true
                            },
                            zoom: {
                                enabled: true,
                                autoScaleYaxis: true,
                            }
                        },
                        title: {},
                        xaxis: {
                            type: 'category',
                            tickPlacement: 'on',
                            categories: metadataTypeLabels,
                            labels: {
                                show: true,
                                trim: true,
                                hideOverlappingLabels: false
                            },
                            tooltip: { enabled: true }
                        },
                        yaxis: {
                            title: {
                                text: this.$i18n.get('label_number_of_metadata')
                            }
                        },
                    }
                }));
            }

            setTimeout(() => { this.isBuildingChart = false; }, 500);
        },
    }
}
</script>