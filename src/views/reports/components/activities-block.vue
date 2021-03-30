<template>
    <div>
        <div 
                v-if="!isFetchingData && chartData && !isBuildingChart && !isFetchingUsers"
                class="postbox">
            <div class="users-charts columns is-multiline">
                <div 
                        class="users-charts__card column is-one-third is-half-desktop"
                        v-for="(chartSeries, index) of chartSeriesByUser"
                        :key="index">
                    <div 
                            v-if="chartSeries[0].userId == 0"
                            class="users-charts__card--header">
                        <div class="anonymous-user-avatar" />
                        <div class="users-charts__card--header-text">
                            <p>{{ $i18n.get('label_anonymous_user') }}</p>
                            <span>{{ chartSeries[0].total }}</span>
                        </div>
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
            const startDate = new Date(this.chartData.totals.by_interval.start).getTime();
            const endDate = new Date(this.chartData.totals.by_interval.end).getTime();

            this.chartSeries = [{
                data: []
            }];

            let maximumOfActivitiesInADay = 0;
            daysWithActivities.forEach((activity) => {
                this.chartSeries[0].data.push({
                    x: new Date(activity.date).getTime(),
                    y: parseInt(activity.total)
                });
                if (maximumOfActivitiesInADay < parseInt(activity.total))
                    maximumOfActivitiesInADay = parseInt(activity.total)
            });

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
                        show: true,
                        tools: {
                            download: true,
                            selection: false,
                            zoom: true,
                            zoomin: true,
                            zoomout: true,
                            pan: true,
                        }
                    },
                },
                xaxis: {
                    type: 'datetime',
                    min: startDate,
                    max: endDate
                },
                yaxis: {
                    max: maximumOfActivitiesInADay,
                    tickAmount: 4,
                    labels: {
                        minWidth: 48
                    }
                },
                colors: ['#01295c'],
            };

            const daysWithActivitiesByUser = JSON.parse(JSON.stringify(this.chartData.totals.by_interval.by_user)).sort((a, b) => parseInt(b.total) - parseInt(a.total));
            
            daysWithActivitiesByUser.forEach((daysWithActivityByUser) => {
                this.chartSeriesByUser.push([{
                    total: daysWithActivityByUser.total,
                    userId: daysWithActivityByUser.user_id,
                    data: daysWithActivityByUser.by_date.map((activity) => { 
                        return {
                            x: new Date(activity.date).getTime(),
                            y: parseInt(activity.total)
                        } 
                    })
                }]);
                this.chartOptionsByUser.push({
                    ...this.areaChartOptions,
                    title: {
                        text: ''
                    },
                    chart: {
                        id: 'userschart-' + daysWithActivityByUser.user_id,
                        height: 160,
                        type: 'area',
                        group: 'activities',
                        toolbar: {
                            show: true,
                            tools: {
                                download: true,
                                selection: false,
                                zoom: true,
                                zoomin: true,
                                zoomout: true,
                                pan: true,
                            }
                        },
                    },
                    xaxis: {
                        type: 'datetime',
                        min: startDate,
                        max: endDate
                    },
                    yaxis: {
                        max: maximumOfActivitiesInADay,
                        tickAmount: 4,
                        labels: {
                            minWidth: 48
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
            padding: 6px 12px 2px 12px;

            img,
            .anonymous-user-avatar {
                margin-right: 0.75em;
                border-radius: 2px;
                width: 32px;
                height: 32px;
                background-color: var(--tainacan-gray2, #dbdbdb);
            }
            .anonymous-user-avatar:before {
                content: "?";
                color: var(--tainacan-gray5, #454647);
                font-size: 1.5em;
                font-weight: bold;
                display: flex;
                align-items: center;
                justify-content: center;
                width: 100%;
                height: 100%;
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