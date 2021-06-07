<template>
    <div v-if="chartData != undefined">
        <apexchart
                v-if="!isFetchingData && taxonomiesListArray && taxonomiesListArray.length && !isBuildingChart"
                height="380px"
                class="postbox"
                :series="chartSeries"
                :options="chartOptions" />
        <div 
                v-if="!isFetchingData && !isBuildingChart && (!taxonomiesListArray || !taxonomiesListArray.length)"
                style="min-height:380px"
                class="postbox">
            <div class="empty-postbox-placeholder">
                <p class="title is-4">
                    <span class="icon has-text-gray">
                        <i class="tainacan-icon tainacan-icon-taxonomies tainacan-icon-1-125em" />
                    </span>
                    &nbsp;{{ $i18n.get('taxonomies') }}
                </p>
                <br>
                <p class="subtitle is-6">{{ $i18n.get('info_no_taxonomy_created') }}</p>
            </div>
        </div>
        <div 
                v-if="isBuildingChart || isFetchingData"
                style="min-height:380px"
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
                    },
                    colors: ['#298596', '#dbdbdb'],
                    fill: {
                        colors: ['#298596', '#dbdbdb']
                    },
                    dataLabels: {
                        style: {
                            colors: ['#ffffff', '#454647']
                        }
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
                                value: 0.9,
                            }
                        },
                    }
                }
            }

            setTimeout(() => this.isBuildingChart = false, 500);
        }
    }
}
</script>