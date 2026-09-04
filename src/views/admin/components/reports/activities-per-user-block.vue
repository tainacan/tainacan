<template>
    <div 
            v-if="hasChartSeries"
            :style="{ maxHeight: activitiesPerUserChartMaxHeight + 'px' }"
            class="report-card activities-per-user-box">
        <div
                class="report-chart"
                :class="{ 'is-reloading-cache': isReloadingChart }"
                :aria-busy="isReloadingChart ? 'true' : 'false'">
            <apexchart
                    ref="activities-per-user-chart"
                    type="bar"
                    :height="activitiesPerUserChartHeight"
                    :series="chartSeries"
                    :options="chartOptions"
                    @mounted="collapseSecondarySeries" />
        </div>
        <slot />
    </div>
    <div 
            v-else
            class="skeleton report-card activities-per-user-box" />
</template>

<script>
import { nextTick } from 'vue';
import { mapGetters } from 'vuex';
import { reportsChartMixin } from '../../js/mixins';

export default {
    mixins: [ reportsChartMixin ],
    data() {
        return {
            orderedActivitiesPerUsers: [],
            validActions: [
                "update-metadata-value",
                "update",
                "create",
                "trash",
                "new-attachment",
                "update-document",
                "delete",
                "delete-attachment",
                "update-thumbnail"
            ]
        }
    },
    computed: {
        ...mapGetters('report', {
            horizontalBarChartOptions: 'getHorizontalBarChartOptions',
        }),
        activitiesPerUserChartHeight() {
            if (!this.chartData.totals?.by_user?.length) return 432;
            return Math.max(120 + (this.chartData.totals.by_user.length * 58), 432);
        },
        activitiesPerUserChartMaxHeight() {
            // Wrapper gets extra space so chart has padding; chart height stays activitiesPerUserChartHeight
            const chartH = this.activitiesPerUserChartHeight;
            const wrapperPadding = 60;
            return Math.min(chartH + wrapperPadding, 800);
        }
    },
    watch: {
        chartData: {
            handler() {
                this.buildActivitiesPerUserChart();
            },
            immediate: true,
            deep: true
        }
    },
    methods: {
        collapseSecondarySeries() {
            nextTick(() => {
                if (this.$refs && this.$refs['activities-per-user-chart'] && this.$refs['activities-per-user-chart'].chart) {
                    this.validActions.forEach((action) => {
                        if (action !== 'update-metadata-value')
                            this.$refs['activities-per-user-chart'].chart.toggleSeries(this.$i18n.get('action_' + action));
                    });
                    this.$refs['activities-per-user-chart'].chart.toggleSeries(this.$i18n.get('action_others'));
                }
            });
        },
        buildActivitiesPerUserChart() {
            if (this.chartData.totals && this.chartData.totals.by_user) {

                // Building Activity Per User Bar chart
                this.orderedActivitiesPerUsers = JSON.parse(JSON.stringify(this.chartData.totals.by_user)).sort((a, b) => b.total - a.total );
                let activityPerUserValues = [];
                let activityPerUserLabels = [];
                // Use a minimum height so few users (e.g. 2) still get readable bar thickness
                const chartHeight = Math.max(120 + (this.chartData.totals.by_user.length * 58), 432);

                // Create empty series for each possible action
                this.validActions.forEach((action) => {
                    activityPerUserValues.push({
                        id: action,
                        name: this.$i18n.get('action_' + action),
                        data: []
                    })
                });
                activityPerUserValues.push({
                    id: 'others',
                    name: this.$i18n.get('action_others'),
                    data: []
                });

                this.orderedActivitiesPerUsers.forEach(activityPerUser => {
                    activityPerUserLabels.push(activityPerUser.user_id == 0 ? this.$i18n.get('label_anonymous_user') : activityPerUser.user.name);
                    activityPerUserValues.forEach((activity) => {
                        if (activity.id == 'others') {
                            let otherActionsTotal = 0;
                            Object.keys(activityPerUser.by_action).forEach((action) => {
                                if (this.validActions.indexOf(action) < 0)
                                    otherActionsTotal += (activityPerUser.by_action[action] ? activityPerUser.by_action[action] : 0);
                            });
                            activity.data.push(otherActionsTotal);
                        } else {
                            activity.data.push( activityPerUser.by_action[activity.id] ? activityPerUser.by_action[activity.id] : 0 );
                        }
                    });
                });
                
                this.chartSeries = activityPerUserValues;

                if (!this.chartOptions.chart) {
                    this.chartOptions = {
                        ...this.horizontalBarChartOptions,
                        ...{
                            chart: {
                                type: 'bar',
                                height: chartHeight,
                                stacked: true,
                                toolbar: {
                                    show: true,
                                    export: {
                                        scale: 3
                                    },
                                },
                                zoom: {
                                    type: 'y',
                                    enabled: true,
                                    autoScaleYaxis: true,
                                }
                            },
                            title: {
                                text: this.$i18n.get('label_activity_per_user')
                            },
                            labels: activityPerUserLabels,
                            plotOptions: {
                                bar: {
                                    horizontal: true,
                                    barHeight: '78%'
                                }
                            },
                            yaxis: {
                                title: {
                                    text: ''
                                },
                                labels: {
                                    maxWidth: 100
                                },
                                tooltip: { enabled: true }
                            },
                            tooltip: {
                                custom: ({ series, seriesIndex, dataPointIndex, w }) => {
                                    return  '<div class="tainacan-custom-tooltip"><div class="tainacan-custom-tooltip__header">' +
                                            (this.orderedActivitiesPerUsers[dataPointIndex].user_id != 0 ? ('<img src="' + this.orderedActivitiesPerUsers[dataPointIndex].user.avatar_urls['24'] + '">&nbsp;') : '') + 
                                            "<span><strong>" + w.globals.labels[dataPointIndex] + '</strong></span></div><div class="tainacan-custom-tooltip__body">' +
                                            w.globals.seriesNames[seriesIndex] + ":&nbsp; <strong>" +
                                            series[seriesIndex][dataPointIndex] +
                                    "</strong></div></div>"
                                }
                            }
                        }
                    }
                } else {
                    this.chartOptions.labels = activityPerUserLabels;
                    if (this.chartOptions.chart)
                        this.chartOptions.chart.height = chartHeight;
                    this.$nextTick(() => {
                        if (this.$refs['activities-per-user-chart'] && this.$refs['activities-per-user-chart'].updateOptions) {
                            this.$refs['activities-per-user-chart'].updateOptions({
                                labels: activityPerUserLabels,
                                chart: { height: chartHeight }
                            }, false, true);
                        }
                    });
                }
            } else {
                this.chartSeries = [];
            }
        }
    }
}
</script>

<style lang="scss" scoped>
.report-card.activities-per-user-box {
    overflow-y: auto;
}
</style>
