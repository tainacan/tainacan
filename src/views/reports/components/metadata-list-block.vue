<template>
    <div
            v-if="metadataList != undefined"
            class="column is-full">
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
                    :series="metadataListChartSeries"
                    :options="metadataListChartOptions" />
            <div 
                v-else
                style="min-height=380px"
                class="skeleton postbox" />
        </div>
    </div>
</template>

<script>
import { mapGetters } from 'vuex';

export default {
    data() {
        return {
            metadataListChartSeries: [],
            metadataListChartOptions: {},
            isBuildingMetadataListArray: false
        }
    },
    computed: {
        ...mapGetters('report', {
            metadata: 'getMetadata',
            metadataList: 'getMetadataList',
        }),
        metadataListArray() {
            return this.metadata && this.metadata != undefined && this.metadata.distribution ? Object.values(this.metadata.distribution) : [];
        }
    },
    methods: {
        buildMetadataListChart() {

            this.isBuildingMetadataListArray = true;

            // Building Metadata term usage chart
            const orderedMetadata = Object.values(this.metadataList).sort((a, b) => b.total_items - a.total_items);
            let metadataItemValues = [];
            let metadataItemLabels = [];

            orderedMetadata.forEach(metadataItem => {
                metadataItemValues.push(metadataItem.total_items);
                metadataItemLabels.push(metadataItem.label);
            }); 

            this.metadataListChartSeries = [
                {
                    name: this.$i18n.get('label_items_with_this_metadum_value'),
                    data: metadataItemValues
                }
            ];
            
            this.metadataListChartOptions = {
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

            setTimeout(() => this.isBuildingMetadataListArray = false, 500);
        }
    }
}
</script>