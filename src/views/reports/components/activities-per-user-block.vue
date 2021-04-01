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
                        :height="120 + (chartData.totals.by_user.length * 58)"
                        :series="chartSeries"
                        :options="chartOptions" />
            </template>
        </div>
        <div 
                v-else
                style="min-height=800px"
                class="skeleton postbox activities-per-user-box" />
    </div>
</template>

<script>
import { mapGetters } from 'vuex';
import { reportsChartMixin } from '../js/reports-mixin';

export default {
    mixins: [ reportsChartMixin ],
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
                const validActions = [
                    "update-metadata-value",
                    "update",
                    "create",
                    "trash",
                    "new-attachment",
                    "update-document",
                    "delete",
                    "delete-attachment",
                    "update-thumbnail"
                ];

                // Create empty series for each possible action
                validActions.forEach((action) => {
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
                    activityPerUserLabels.push(activityPerUser.user_id);
                    activityPerUserValues.forEach((activity) => {
                        activity.data.push( activityPerUser.by_action[activity.id] ? activityPerUser.by_action[activity.id] : 0 );
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
                            text: this.$i18n.get('label_activitiy_per_user')
                        },
                        labels: activityPerUserLabels
                    }
                }
            }

            setTimeout(() => this.isBuildingChart = false, 300);
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