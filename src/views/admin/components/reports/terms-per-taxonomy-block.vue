<template>
    <div
            v-if="hasChartSeries || (!isFetchingData && taxonomiesListArray && taxonomiesListArray.length)"
            class="report-card is-full">
        <div
                class="report-chart"
                :class="{ 'is-reloading-cache': isReloadingChart }"
                :aria-busy="isReloadingChart ? 'true' : 'false'">
            <apexchart
                    v-if="hasChartSeries"
                    ref="taxonomiesChart"
                    type="bar"
                    height="380px"
                    :series="chartSeries"
                    :options="chartOptions" />
        </div>
        <slot />
    </div>
    <div 
            v-else-if="!isFetchingData && (!taxonomiesListArray || !taxonomiesListArray.length)"
            style="min-height: 380px"
            class="report-card is-full">
        <div class="empty-report-card-placeholder">
            <p class="title is-4">
                <span 
                        aria-hidden="true"
                        class="icon has-text-dark">
                    <i class="tainacan-icon tainacan-icon-taxonomies tainacan-icon-1em" />
                </span>
                &nbsp;{{ $i18n.get('taxonomies') }}
            </p>
            <br>
            <p class="subtitle is-6">
                {{ $i18n.get('info_no_taxonomy_created') }}
            </p>
        </div>
    </div>
    <div 
            v-else
            style="min-height: 380px"
            class="skeleton report-card is-full" />
</template>


<script>
import { mapGetters } from 'vuex';
import { reportsChartMixin } from '../../js/mixins';

export default {
    mixins: [ reportsChartMixin ],
    computed: {
        ...mapGetters('report', {
            stackedBarChartOptions: 'getStackedBarChartOptions',
        }),
        taxonomiesListArray() {
            return this.chartData && this.chartData != undefined ? Object.values(this.chartData) : [];
        }
    },
    watch: {
        taxonomiesListArray: {
            handler() {
                this.buildTaxonomiesList();
            },
            immediate: true,
            deep: true
        }
    },
    methods: {
         buildTaxonomiesList() {
            if (!this.taxonomiesListArray.length) {
                this.chartSeries = [];
                return;
            }

            const orderedTaxonomies = [...this.taxonomiesListArray].sort((a, b) => b.total_terms - a.total_terms);
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

            if (!this.chartOptions.chart) {
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
                        colors: ['#187181', '#dbdbdb'],
                        fill: {
                            colors: ['#187181', '#dbdbdb']
                        },
                        dataLabels: {
                            style: {
                                colors: ['#ffffff', '#373839']
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
            } else {
                this.$nextTick(() => {
                    if (this.$refs.taxonomiesChart && this.$refs.taxonomiesChart.updateOptions) {
                        this.$refs.taxonomiesChart.updateOptions({
                            xaxis: { categories: taxonomiesLabels }
                        }, false, true);
                    }
                });
            }
        }
    }
}
</script>
