<template>
    <div class="column is-full">
        <div 
                v-if="metadata && metadata.totals && !isBuildingMetadataTypeChart"
                class="postbox">
            <label>{{ $i18n.get('metadata_types') }}&nbsp;</label>
            <div class="graph-mode-switch">
                <button 
                        @click="metadataTypeChartMode = 'bar'"
                        :class="{ 'current': metadataTypeChartMode == 'bar' }">
                    <span class="screen-reader-text">
                        {{ $i18n.get('label_bar_chart') }}
                    </span>
                    <span class="icon">
                        <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-text tainacan-icon-rotate-270" />
                    </span>
                </button>
                <button 
                        @click="metadataTypeChartMode = 'circle'"
                        :class="{ 'current': metadataTypeChartMode == 'circle' }">
                    <span class="screen-reader-text">
                        {{ $i18n.get('label_pie_chart') }}
                    </span>
                    <span class="icon">
                        <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-waiting tainacan-icon-rotate-270" />
                    </span>
                </button>
            </div>
            <apexchart
                    height="380px"
                    :series="metadataTypeChartSeries"
                    :options="metadataTypeChartOptions" />
        </div>
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
            isBuildingMetadataTypeChart: false,
            metadataTypeChartMode: 'bar',
            metadataTypeChartSeries: [],
            metadataTypeChartOptions: {},
        }
    },
    computed: {
        ...mapGetters('report', {
            metadata: 'getMetadata',
            donutChartOptions: 'getDonutChartOptions',
            stackedBarChartOptions: 'getStackedBarChartOptions',
        })
    },
    watch: {
        metadataTypeChartMode() {
            this.buildMetadataTypeChart();
        },
        metadata() {
            this.buildMetadataTypeChart();
        }
    },
    methods: {
        buildMetadataTypeChart() {

            this.isBuildingMetadataTypeChart = true;

            // Building Metadata Type Donut Chart
            let metadataTypeValues = [];
            let metadataTypeLabels = [];
            
            const orderedMetadataPerType = Object.values(this.metadata.totals.metadata_per_type).sort((a, b) => b.count - a.count);
            orderedMetadataPerType.forEach((metadataPerType) => {
                metadataTypeValues.push(metadataPerType.count ? metadataPerType.count : 0);
                metadataTypeLabels.push(metadataPerType.name ? metadataPerType.name : '');
            });
            
            this.metadataTypeChartSeries = this.metadataTypeChartMode == 'circle' ? metadataTypeValues : [{ data: metadataTypeValues }];

            if (this.metadataTypeChartMode == 'circle') {
                this.metadataTypeChartOptions = JSON.parse(JSON.stringify({
                    ...this.donutChartOptions,
                    ...{
                        title: {},
                        labels: metadataTypeLabels,
                    }
                }));
            } else {
                this.metadataTypeChartOptions = JSON.parse(JSON.stringify({
                    ...this.stackedBarChartOptions,
                    ...{
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
                            tooltip: true
                        }
                    }
                }));
            }

            setTimeout(() => { this.isBuildingMetadataTypeChart = false; }, 500);
        },
    }
}
</script>