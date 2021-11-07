<template>
    <div>
        <div 
                v-if="!isFetchingData && chartData.totals && chartData.totals.by_user && !isBuildingChart"
                :style="{
                    maxHeight: ((120 + (chartData.totals.by_user.length * 58)) <= 800 ? (120 + (chartData.totals.by_user.length * 58)) : 800) + 'px'
                }"
                class="postbox activities-per-user-box">
            <template v-if="chartData.totals && chartData.totals.by_user">
                <apexchart
                        ref="activities-per-user-chart"
                        :height="120 + (chartData.totals.by_user.length * 58)"
                        :series="chartSeries"
                        :options="chartOptions" />
            </template>
        </div>
        <div 
                v-else
                class="skeleton postbox activities-per-user-box" />
        <slot />
    </div>
</template>

<script>
import { mapGetters } from 'vuex';
import { reportsChartMixin } from '../js/reports-mixin';

export default {
    mixins: [ reportsChartMixin ],
    data() {
        return {
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
        })
    },
    watch: {
        chartData: {
            handler() {
                this.buildActivitiesPerUserChart();
            },
            immediate: true,
        }
    },
    methods: {
        buildActivitiesPerUserChart() {

            this.isBuildingChart = true;

            if (this.chartData.totals && this.chartData.totals.by_user) {

                // Building Activity Per User Bar chart
                const orderedActivitiesPerUsers = JSON.parse(JSON.stringify(this.chartData.totals.by_user)).sort((a, b) => b.total - a.total );
                let activityPerUserValues = [];
                let activityPerUserLabels = [];
                const userCount = 100 + (this.chartData.totals.by_user.length * 58);

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

                orderedActivitiesPerUsers.forEach(activityPerUser => {
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
                })
                
                this.chartSeries = activityPerUserValues;
   
                this.chartOptions = {
                    ...this.horizontalBarChartOptions,
                    ...{
                        chart: {
                            type: 'bar',
                            height: userCount,
                            stacked: true,
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
                            text: this.$i18n.get('label_activity_per_user')
                        },
                        labels: activityPerUserLabels,
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
                                        (orderedActivitiesPerUsers[dataPointIndex].user_id != 0 ? ('<img src="' + orderedActivitiesPerUsers[dataPointIndex].user.avatar_urls['24'] + '">&nbsp;') : '') + 
                                        "<span><strong>" + w.globals.labels[dataPointIndex] + '</strong></span></div><div class="tainacan-custom-tooltip__body">' +
                                        w.globals.seriesNames[seriesIndex] + ":&nbsp; <strong>" +
                                        series[seriesIndex][dataPointIndex] +
                                "</strong></div></div>"
                            }
                        }
                    }
                }
            }
            setTimeout(() => { 
                this.isBuildingChart = false;
                
                this.$nextTick(() => {
                    if (this.$refs && this.$refs['activities-per-user-chart'] && this.$refs['activities-per-user-chart'].chart) {
                        this.validActions.forEach((action) => {
                            if (action !== 'update-metadata-value')
                                this.$refs['activities-per-user-chart'].chart.toggleSeries(this.$i18n.get('action_' + action));
                        });
                        this.$refs['activities-per-user-chart'].chart.toggleSeries(this.$i18n.get('action_others'));
                    }
                });
            }, 300);
        }
    }
}
</script>

<style lang="scss" scoped>
.postbox.activities-per-user-box {
    margin: 0.75rem !important;  
    overflow-y: auto;
}
</style>
