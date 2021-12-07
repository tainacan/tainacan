<template>
    <div>
        <div 
                :class="{ 'skeleton': isFetchingData || !chartData || isBuildingChart }"
                class="postbox">
            <div 
                    v-if="currentStart && currentEnd"
                    class="box-header">
                <div class="box-header__item tablenav-pages">
                    <label for="start_year">
                        {{ $i18n.get('label_activities_during_year') }}&nbsp;
                    </label>
                    <span class="pagination-links">
                        <span
                                @click="(!isBuildingChart && currentStart.getFullYear() > (minYear + 1)) ? decreaseYear() : null"
                                :class="{'tablenav-pages-navspan disabled' : isBuildingChart || currentStart.getFullYear() <= (minYear + 1) }"
                                class="prev-page button"
                                aria-hidden="true">
                            ‹
                        </span>
                        <select
                                name="start_year"
                                id="start_year"
                                :placeholder="$i18n.get('label_select_a_year')"
                                :disabled="isBuildingChart"
                                :value="currentStart.getFullYear()"
                                @input="($event) => setStartYear($event.target.value)">
                            <option 
                                    v-for="index of (maxYear - minYear)"
                                    :key="index"
                                    :value="index + minYear">
                                {{ index + minYear }}
                            </option>
                        </select>
                        <span 
                                @click="(!isBuildingChart && currentStart.getFullYear() <= (maxYear - 1)) ? increaseYear() : null"
                                :class="{ 'tablenav-pages-navspan disabled': isBuildingChart || currentStart.getFullYear() > (maxYear - 1) }"
                                aria-hidden="true"
                                class="next-page button">
                            ›
                        </span>
                    </span>
                </div>
                <div class="box-header__item">
                    <label>{{ $i18n.get('instruction_filter_activities_date') + ': ' }}</label>
                    <span class="paging-input">
                        {{ currentStart.toDateString() }} - {{ currentEnd.toDateString() }}
                    </span>
                </div>
            </div>
            <template v-if="!isFetchingData && chartData && !isBuildingChart">
                <div class="users-charts columns is-multiline">
                    <div 
                            class="users-charts__card column is-full"
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
                                v-if="chartSeries[0].userId != 0 && chartSeries[0].userId"
                                class="users-charts__card--header">
                            <img :src="chartSeries[0].userImg">
                            <div class="users-charts__card--header-text">
                                <p>{{ chartSeries[0].userName }}</p>
                                <span>{{ chartSeries[0].total }}</span>
                            </div>
                        </div>
                        <apexchart
                                type="area"
                                height="140"
                                :series="chartSeries"
                                :options="chartOptionsByUser[index]" />
                    </div>
                </div>
                <apexchart
                        type="area"
                        height="200"
                        :series="chartSeries"
                        :options="chartOptions" />
            
            </template>
        </div>
        <slot />
    </div>
</template>

<script>
import { mapGetters, mapMutations } from 'vuex';
import { reportsChartMixin } from '../js/reports-mixin';

export default {
    mixins: [ reportsChartMixin ],
    data() {
        return {
            chartSeriesByUser: [],
            chartOptionsByUser: [],
            maxYear: new Date().getFullYear() - 1,
            minYear: 2017,
            currentStart: '',
            currentEnd: ''
        }
    },
    computed: {
        ...mapGetters('report', {
            areaChartOptions: 'getAreaChartOptions'
        })
    },
    watch: {
        chartData: {
            handler() {
                if (this.chartData && this.chartData.totals)
                    this.buildActivitiesChart();
            },
            immediate: true
        },
    },
    methods: {
        ...mapMutations('report', [
            'setStartDate'
        ]),
        increaseYear() {
            this.setStartYear(this.currentEnd.getFullYear());
        },
        decreaseYear() {
            let currentStartDate = new Date(this.currentStart.getTime());
            this.setStartYear(new Date(currentStartDate.setFullYear(currentStartDate.getFullYear() - 1)).getFullYear());
        },
        setStartYear(newStartYear) {
            let currentStartDate = new Date(this.currentStart.getTime());
            const newStart = new Date(currentStartDate.setFullYear(newStartYear));
            this.setStartDate(newStart.toISOString());
            this.$emit('time-range-update', newStart.toISOString());
        },
        getDaysArray(start, end) {
            let everyDay = [];
            for (let day = new Date(start); day <= end; day.setDate(day.getDate() + 1 ) )
                everyDay.push(new Date(day));
            return everyDay;
        },
        buildActivitiesChart() {
            this.isBuildingChart =  true;

            const daysWithActivities = (this.chartData.totals.by_interval && this.chartData.totals.by_interval.general) ? this.chartData.totals.by_interval.general : []; 
            this.currentStart = new Date(this.chartData.totals.by_interval.start);
            this.currentEnd = new Date(this.chartData.totals.by_interval.end);

            if (daysWithActivities.length)
                this.chartSeries = [{
                    name: this.$i18n.get('activities'),
                    data: []
                }];
            else
                this.chartSeries = [];

            let maximumOfActivitiesInADay = 0;
            let everyDay = this.getDaysArray(this.currentStart, this.currentEnd);

            everyDay.forEach((aDayInTheLifeTime) => {
                const aDayWithSomeActivityIndex = daysWithActivities.findIndex(activity => new Date(activity.date).toISOString().slice(0,10) == aDayInTheLifeTime.toISOString().slice(0,10));
                this.chartSeries[0].data.push({
                    x: aDayInTheLifeTime.getTime(),
                    y: aDayWithSomeActivityIndex >= 0 ? parseInt(daysWithActivities[aDayWithSomeActivityIndex].total) : 0
                });
                if (aDayWithSomeActivityIndex >= 0 && maximumOfActivitiesInADay < parseInt(daysWithActivities[aDayWithSomeActivityIndex].total))
                    maximumOfActivitiesInADay = parseInt(daysWithActivities[aDayWithSomeActivityIndex].total);
            });
            
            this.chartOptions = {
                ...this.areaChartOptions,
                title: {
                    text: this.$i18n.get('label_all_users'),
                    style: {
                        fontSize:  '13px'
                    }
                },
                noData: {
                    text: daysWithActivities.length ? this.$i18n.get('label_loading_report') : this.$i18n.get('info_no_activities')
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
                    min: this.currentStart.getTime(),
                    max: this.currentEnd.getTime()
                },
                yaxis: {
                    show: daysWithActivities.length,
                    max: maximumOfActivitiesInADay,
                    tickAmount: 4,
                    labels: {
                        minWidth: 48
                    }
                },
                colors: ['#01295c'],
            };
            const daysWithActivitiesByUser = JSON.parse(JSON.stringify(this.chartData.totals.by_interval.by_user)).sort((a, b) => parseInt(b.total) - parseInt(a.total));

            this.chartSeriesByUser = [];
            this.chartOptionsByUser = []

            daysWithActivitiesByUser.forEach((daysWithActivityByUser) => {

                let perUserSeries = [];
                everyDay.forEach((aDayInTheLifeTime) => {
                    const aDayWithSomeActivityIndex = daysWithActivityByUser.by_date.findIndex(activity => new Date(activity.date).toISOString().slice(0,10) == aDayInTheLifeTime.toISOString().slice(0,10));
                    perUserSeries.push({
                        x: aDayInTheLifeTime.getTime(),
                        y: aDayWithSomeActivityIndex >= 0 ? parseInt(daysWithActivityByUser.by_date[aDayWithSomeActivityIndex].total) : 0
                    });
                });

                this.chartSeriesByUser.push([{
                    total: daysWithActivityByUser.total,
                    userId: daysWithActivityByUser.user_id,
                    userName: daysWithActivityByUser.user.name,
                    userImg: daysWithActivityByUser.user.avatar_urls ? daysWithActivityByUser.user.avatar_urls['48'] : '',
                    name: this.$i18n.get('activities'),
                    data: perUserSeries
                }]);
                this.chartOptionsByUser.push({
                    ...this.areaChartOptions,
                    title: {
                        text: daysWithActivityByUser.user.name + ' (' + daysWithActivityByUser.total + ')'
                    },
                    chart: {
                        id: 'userschart-' + daysWithActivityByUser.user_id,
                        height: 140,
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
                        min: this.currentStart.getTime(),
                        max: this.currentEnd.getTime()
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
    flex-direction: column;
    justify-content: flex-start;
    min-height: 280px !important;
    
    .screen-per-page {
        width: 6em;
    }
}
.users-charts {
    order: 3;
    padding: 12px;

    @media only screen {
        /deep/ .apexcharts-title-text {
            display: none;
            visibility: hidden;
        }   
    }

    .users-charts__card {
        padding: 20px !important;

        .users-charts__card--header {
            display: flex;
            align-items: center;
            padding: 6px 12px 2px 12px;
            position: absolute;
            top: 2px;
            left: 26px;

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
                    max-width: 150px;
                    overflow: hidden;
                    text-overflow: ellipsis;
                    white-space: nowrap;
                }
                span {
                    color: var(--tainacan-secondary, #298596);
                }
            }
        }
    }
}
</style>