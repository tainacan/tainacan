<template>
    <div>
        <div 
                v-if="!isFetchingData && chartData && !isBuildingChart && !isFetchingUsers"
                class="postbox">
            <div class="users-charts">
                <template v-if="!isFetchingUsers">
                    <div 
                            class="users-charts__card"
                            v-for="(user, index) of users"
                            :key="index">
                        <div class="users-charts__card--header">
                            <img :src="user.avatar_urls['48']">
                            <p>{{ user.name }}</p>
                        </div>
                        <apexchart
                                type="area"
                                height="160"
                                :series="chartSeriesByUser[user.id]"
                                :options="chartOptionsByUser" />
                    </div>
                </template>
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
            users: [],
            chartSeriesByUser: [],
            chartOptionsByUser: {}
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
                this.users = resp.users;
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
                data: daysWithActivities.map((activity) => { 
                    return {
                        x: new Date(activity.date).getTime(),
                        y: activity.total
                    } 
                })
            }];
            const daysWithActivitiesByUser = this.chartData.totals.by_interval.by_user;
            Object.keys(daysWithActivitiesByUser).forEach((userId) => {
                this.chartSeriesByUser[userId] = [{ 
                    data: daysWithActivitiesByUser[userId].map((activity) => { 
                        return {
                            x: new Date(activity.date).getTime(),
                            y: activity.total
                        } 
                    })
                }];
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
                        autoSelected: 'selection',
                    },
                },
                colors: ['#01295c'],
            };

            this.chartOptionsByUser = {
                ...this.areaChartOptions,
                title: {
                    text: ''
                },
                chart: {
                    id: 'userschart',
                    height: 160,
                    type: 'area',
                    group: 'activities',
                    toolbar: {
                        show: false,
                        autoSelected: 'pan'
                    }
                }
            }

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
    display: flex;
    flex-wrap: wrap;
    justify-content: space-evenly;

    .users-charts__card {
        width: calc(33.3333% - 48px);
        max-width: calc(33.3333% - 48px);
        padding: 24px;

        .users-charts__card--header {
            display: flex;
            align-items: center;

            img {
                margin-right: 0.75em;
                border-radius: 2px;
                width: 32px;
                height: 32px;
            }
            p {
                font-weight: bold;
                font-size: 1.125em;
            }
        }
    }
}
</style>