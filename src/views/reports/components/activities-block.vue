<template>
    <div>
        <div 
                v-if="!isFetchingData && chartData && !isBuildingChart && !isFetchingUsers"
                class="postbox">
            <div class="users-charts columns is-multiline">
                <div 
                        class="users-charts__card column is-one-third is-half-tablet"
                        v-for="(chartSeries, index) of chartSeriesByUser"
                        :key="index">
                    <div 
                            v-if="chartSeries[0].userId == 0"
                            class="users-charts__card--header">
                        <div class="anonymous-user-avatar" />
                        <p>{{ $i18n.get('label_anonymous_user') }}</p>
                        <span>{{ chartSeries[0].total }}</span>
                    </div>
                    <div 
                            v-if="chartSeries[0].userId != 0 && users[chartSeries[0].userId]"
                            class="users-charts__card--header">
                        <img :src="users[chartSeries[0].userId].avatar_urls['48']">
                        <div class="users-charts__card--header-text">
                            <p>{{ users[chartSeries[0].userId].name }}</p>
                            <span>{{ chartSeries[0].total }}</span>
                        </div>
                    </div>
                    <apexchart
                            type="area"
                            height="160"
                            :series="chartSeries"
                            :options="chartOptionsByUser[index]" />
                </div>
            </div>
            <apexchart
                    type="area"
                    height="200"
                    :series="chartSeries"
                    :options="chartOptions" />
        </div>
        <div 
                v-else
                style="min-height=740px"
                class="skeleton postbox" />
    </div>
</template>

<script>
import { mapActions, mapGetters } from 'vuex';
import { reportsChartMixin } from '../js/reports-mixin';

export default {
    mixins: [ reportsChartMixin ],
    data() {
        return {
            isFetchingUsers: false,
            users: {},
            chartSeriesByUser: [],
            chartOptionsByUser: []
        }
    },
    computed: {
        ...mapGetters('report', {
            areaChartOptions: 'getAreaChartOptions',
        })
    },
    watch: {
        chartData: {
            handler() {
                if (this.chartData && this.chartData.totals)
                    this.buildActivitiesChart();
            },
            immediate: true
        }
    },
    created() {
        this.isFetchingUsers = true;
        this.fetchUsers({ search: '' })
            .then((resp) => {
                resp.users.forEach((user) => {
                    this.users[user.id] = user;
                });
                this.isFetchingUsers = false;
            })
            .catch(() => {
                this.isFetchingUsers = false;
            })
    },
    methods: {
        ...mapActions('activity', {
            fetchUsers: 'fetchUsers',
        }),
        buildActivitiesChart() {
            this.isBuildingChart =  true;

            const daysWithActivities = (this.chartData.totals.by_interval && this.chartData.totals.by_interval.general) ? this.chartData.totals.by_interval.general : []; 
           
           this.chartSeries = [{
               data: []
           }];
           let maximumOfActivitiesInADay = 0;
           daysWithActivities.forEach((activity) => {
                this.chartSeries[0].data.push({
                    x: new Date(activity.date).getTime(),
                    y: activity.total
                });
                if (maximumOfActivitiesInADay < activity.total)
                    maximumOfActivitiesInADay = activity.total
            });
            console.log(maximumOfActivitiesInADay);
            this.chartOptions = {
                ...this.areaChartOptions,
                title: {
                    text: this.$i18n.get('label_activities_last_year')
                },
                chart: {
                    id: 'generalchart',
                    height: 200,
                    type: 'area',
                    group: 'activities',
                    toolbar: {
                        autoSelected: 'selection',
                    },
                },
                colors: ['#01295c'],
            };

            const daysWithActivitiesByUser = this.chartData.totals.by_interval.by_user;
            const totalOfActivitiesByUser = this.chartData.totals.by_user.sort((a, b) => b.total - a.total);
            
            totalOfActivitiesByUser.forEach((totalActivityByUser) => {
                this.chartSeriesByUser.push([{
                    total: totalActivityByUser.total,
                    userId: totalActivityByUser.user_id,
                    data: daysWithActivitiesByUser[totalActivityByUser.user_id] ? daysWithActivitiesByUser[totalActivityByUser.user_id].map((activity) => { 
                        return {
                            x: new Date(activity.date).getTime(),
                            y: activity.total
                        } 
                    }) : [{
                        x: null,
                        y: 0
                    }]
                }]);
                this.chartOptionsByUser.push({
                    ...this.areaChartOptions,
                    title: {
                        text: ''
                    },
                    chart: {
                        id: 'userschart-' + totalActivityByUser.user_id,
                        height: 160,
                        type: 'area',
                        group: 'activities',
                        toolbar: {
                            show: false,
                            autoSelected: 'pan'
                        }
                    }
                });
            }); 
            
            setTimeout(() => this.isBuildingChart = false, 500);
        }
    }
}
</script>

<style lang="scss" scoped>
.postbox {
    display: flex;
    flex-direction: column-reverse;
}
.users-charts {
    padding: 12px;

    .users-charts__card {
        padding: 24px;

        .users-charts__card--header {
            display: flex;
            align-items: center;

            img,
            .anonymous-user-avatar {
                margin-right: 0.75em;
                border-radius: 2px;
                width: 32px;
                height: 32px;
                background-color: var(--tainacan-gray1, #f2f2f2);
            }

            .users-charts__card--header-text {
                display: flex;
                flex-direction: column;
                
                p {
                    font-weight: bold;
                    font-size: 1.0em;
                    margin: 0;
                }
                span {
                    color: var(--tainacan-secondary, #298596);
                }
            }
        }
    }
}
</style>