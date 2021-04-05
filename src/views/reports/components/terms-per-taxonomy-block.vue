<template>
    <div v-if="chartData != undefined">
        <apexchart
                v-if="!isFetchingData && taxonomiesListArray && taxonomiesListArray.length && !isBuildingChart"
                height="380px"
                class="postbox"
                :series="chartSeries"
                :options="chartOptions" />
        <div 
                v-else
                style="min-height=380px"
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
        taxonomiesListArray() {
            return this.chartData && this.chartData != undefined ? Object.values(this.chartData) : [];
        },
    },
    watch: {
        taxonomiesListArray: {
            handler() {
                this.buildTaxonomiesList();
            },
            immediate: true
        }
    },
    methods: {
         buildTaxonomiesList() {
            // Building Taxonomy term usage chart
            this.isBuildingChart = true;

            const orderedTaxonomies = this.taxonomiesListArray.sort((a, b) => b.total_terms - a.total_terms);
            let termsUsed = [];
            let termsNotUsed = [];
            let taxonomiesLabels = [];

            orderedTaxonomies.forEach(taxonomy => {
                termsUsed.push(taxonomy.total_terms_used);
                termsNotUsed.push(taxonomy.total_terms_not_used);
                taxonomiesLabels.push(taxonomy.name);
            });

            this.chartSeries = [
                {
                    name: this.$i18n.get('label_terms_used'),
                    data: termsUsed
                },
                {
                    name: this.$i18n.get('label_terms_not_used'),
                    data: termsNotUsed
                }
            ];
            
            this.chartOptions = {
                ...this.stackedBarChartOptions, 
                ...{
                    title: {
                        text: this.$i18n.get('label_usage_of_terms_per_taxonomy')
                    },
                    xaxis: {
                        type: 'category',
                        tickPlacement: 'on',
                        categories: taxonomiesLabels,
                        labels: {
                            show: true,
                            trim: true,
                            hideOverlappingLabels: false
                        },
                        tooltip: { enabled: true }
                    },
                    yaxis: {
                        title: {
                            text: this.$i18n.get('label_number_of_terms')
                        }
                    }
                }
            }

            setTimeout(() => this.isBuildingChart = false, 500);
        }
    }
}
</script>