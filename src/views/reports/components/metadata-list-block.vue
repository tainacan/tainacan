<template>
    <div v-if="chartData != undefined">
        <div
                style="margin-top: 0px"
                class="postbox">
            <label>{{ $i18n.get('label_amount_of_items_per_metadatum_value') }}&nbsp;</label>
            <select 
                    v-if="!isFetchingMetadataList"
                    name="select_metadata"
                    id="select_metadata"
                    :placeholder="$i18n.get('label_select_a_metadatum')"
                    v-model="selectedMetadatum">
                <option 
                        v-for="(metadatum, index) of metadataListArray"
                        :key="index"
                        :value="metadatum.id">
                    {{ metadatum.name }} 
                </option>
            </select>
            <apexchart
                    v-if="!isFetchingMetadataList && selectedMetadatum"
                    height="380px"
                    :series="chartSeries"
                    :options="chartOptions" />
            <div 
                v-else
                style="min-height=380px"
                class="skeleton postbox" />
        </div>
    </div>
</template>

<script>
import { mapActions, mapGetters } from 'vuex';
import { reportsChartMixin } from '../js/reports-mixin';

export default {
    mixins: [ reportsChartMixin ],
    data() {
        return {
            selectedMetadatum: '',
            isFetchingMetadataList: false
        }
    },
    computed: {
        ...mapGetters('report', {
            metadata: 'getMetadata',
            stackedBarChartOptions: 'getStackedBarChartOptions',
        }),
        metadataListArray() {
            return this.metadata && this.metadata != undefined && this.metadata.distribution ? Object.values(this.metadata.distribution) : [];
        },
    },
    watch: {
        metadataListArray: {
            handler() {
                this.selectedMetadatum = (this.metadataListArray.length && this.metadataListArray[0].id) ? this.metadataListArray[0].id : '';
            },
            immediate: true
        },
        selectedMetadatum: {
            handler() {
                if (this.selectedMetadatum)
                    this.loadMetadataList();
            },
            immediate: true
        }
    },
    methods: {
        ...mapActions('report', [
            'fetchMetadataList'
        ]),
        loadMetadataList() {
            this.isFetchingMetadataList = true;
            this.fetchMetadataList({ collectionId: this.collectionId, metadatumId: this.selectedMetadatum })
                .then(() => {
                    this.buildMetadataListChart();
                    this.isFetchingMetadataList = false;
                })
                .catch(() => this.isFetchingMetadataList = false);
        },
        buildMetadataListChart() {

            this.isBuildingChart = true;

            // Building Metadata term usage chart
            const orderedMetadata = Object.values(this.chartData).sort((a, b) => b.total_items - a.total_items);
            let metadataItemValues = [];
            let metadataItemLabels = [];

            orderedMetadata.forEach(metadataItem => {
                metadataItemValues.push(metadataItem.total_items);
                metadataItemLabels.push(metadataItem.label);
            }); 

            this.chartSeries = [
                {
                    name: this.$i18n.get('label_items_with_this_metadum_value'),
                    data: metadataItemValues
                }
            ];
            
            this.chartOptions = {
                ...this.stackedBarChartOptions, 
                ...{
                    title: {},
                    xaxis: {
                        type: 'category',
                        tickPlacement: 'on',
                        categories: metadataItemLabels,
                    },
                    yaxis: {
                        title: {
                            text: this.$i18n.get('label_number_of_items')
                        }
                    }
                }
            }

            setTimeout(() => this.isBuildingChart = false, 500);
        }
    }
}
</script>