<template>
    <div 
            v-if="taxonomiesList != undefined"
            class="column is-full">
        <apexchart
                v-if="!isFetchingTaxonomiesList"
                height="380px"
                class="postbox"
                :series="taxonomiesListChartSeries"
                :options="taxonomiesListChartOptions" />
        <div 
                v-else
                style="min-height=380px"
                class="skeleton postbox" />
    </div>
</template>


<script>
import { mapActions, mapGetters } from 'vuex';

export default {
    data() {
        return {
            isFetchingTaxonomiesList: false,
            taxonomiesListChartSeries: [],
            taxonomiesListChartOptions: {},
        }
    },
    computed: {
        ...mapGetters('report', {
            taxonomiesList: 'getTaxonomiesList',
            stackedBarChartOptions: 'getStackedBarChartOptions',
            isFetchingTaxonomiesList: 'getIsFetchingTaxonomiesList',
        }),
        taxonomiesListArray() {
            return this.taxonomiesList && this.taxonomiesList != undefined ? Object.values(this.taxonomiesList) : [];
        },
    },
    created() {
        this.loadTaxonomiesList();
    },
    methods: {
         ...mapActions('report', [
            'fetchTaxonomiesList'
         ]),
         loadTaxonomiesList() {
            this.isFetchingTaxonomiesList = true;
            this.fetchTaxonomiesList()
                .then(() => {
                    // Building Taxonomy term usage chart
                    const orderedTaxonomies = this.taxonomiesListArray.sort((a, b) => b.total_terms - a.total_terms);
                    let termsUsed = [];
                    let termsNotUsed = [];
                    let taxonomiesLabels = [];

                    orderedTaxonomies.forEach(taxonomy => {
                        termsUsed.push(taxonomy.total_terms_used);
                        termsNotUsed.push(taxonomy.total_terms_not_used);
                        taxonomiesLabels.push(taxonomy.name);
                    });

                    this.taxonomiesListChartSeries = [
                        {
                            name: this.$i18n.get('label_terms_used'),
                            data: termsUsed
                        },
                        {
                            name: this.$i18n.get('label_terms_not_used'),
                            data: termsNotUsed
                        }
                    ];
                    
                    this.taxonomiesListChartOptions = {
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
                                tooltip: true
                            },
                            yaxis: {
                                title: {
                                    text: this.$i18n.get('label_number_of_terms')
                                }
                            }
                        }
                    }
                    this.isFetchingTaxonomiesList = false;
                })
                .catch(() => this.isFetchingTaxonomiesList = false);
        },
    }
}
</script>