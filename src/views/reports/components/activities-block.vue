<template>
    <div>
        <div 
                v-if="!isFetchingData && chartData && !isBuildingChart"
                class="postbox">
            <apexchart
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
import { reportsChartMixin } from '../js/reports-mixin';

export default {
    mixins: [ reportsChartMixin ],
    watch: {
        chartData: {
            handler() {
                if (this.chartData)
                    this.buildActivitiesChart();
            },
            immediate: true
        }
    },
    methods: {
        buildActivitiesChart() {
            this.isBuildingChart =  true;

            const daysWithActivities = this.chartData && this.chartData.totals && this.chartData.totals.last_year && this.chartData.totals.last_year.general ? this.chartData.totals.last_year.general : []; 
            if (daysWithActivities && daysWithActivities.length) {
                
                let everyDayOfTheYear = Array.from(new Array(7),
                    (val,index) => {
                        return {
                            name: (index + 1),
                            data: new Array(53).fill({ x: '', y: 0 })
                        }
                    }
                );
                
                const firstDayOfTheWeekWithActivity = parseInt(daysWithActivities[0].day_of_week);
                let dayWithActivityIndex = 0;
                let daysToSkip = 0;

                // Loop for each column (number of the week in the year)
                for (let column = 0; column < 53; column++) {

                    // Loop for each line (number of the day in the week)
                    for (let line = 0; line < 7; line++) {

                        // If there are no more days with chartData, get outta here
                        if (dayWithActivityIndex < daysWithActivities.length - 1) {
                                
                            // We should only begin inserting days from firstDayOfTheWeekWithActivity
                            if (column == 0 && line < firstDayOfTheWeekWithActivity - 1) {
                                continue;

                            // On the first day, we don't need to calculate distances, just set the value and save the date
                            } else if (column == 0 && line == firstDayOfTheWeekWithActivity - 1) {
                                everyDayOfTheYear[line].data[column] = {
                                    x: '',
                                    y: parseInt(daysWithActivities[dayWithActivityIndex].total)
                                };

                                const lastDayWithActivity = new Date(daysWithActivities[dayWithActivityIndex].date);
                                dayWithActivityIndex++;

                                const nextDayWithActivity = new Date(daysWithActivities[dayWithActivityIndex].date);

                                daysToSkip = Math.floor( (nextDayWithActivity - lastDayWithActivity) / (1000 * 60 * 60 * 24) );
                            } else {
                                daysToSkip--;

                                // If we don't have more days to skip, time to update values
                                if ( daysToSkip <= 0) {
                                    everyDayOfTheYear[line].data[column] = {
                                        x: '',
                                        y: parseInt(daysWithActivities[dayWithActivityIndex].total)
                                    };

                                    const lastDayWithActivity = new Date(daysWithActivities[dayWithActivityIndex].date);
                                    dayWithActivityIndex++;

                                    const nextDayWithActivity = new Date(daysWithActivities[dayWithActivityIndex].date);

                                    daysToSkip = Math.floor( (nextDayWithActivity - lastDayWithActivity) / (1000 * 60 * 60 * 24) );
                                }
                            }
                        }
                    }
                }
                this.chartSeries = everyDayOfTheYear;
                this.chartOptions = {
                        ...this.heatMapChartOptions,
                        title: {
                            text: this.$i18n.get('label_activities_last_year')
                        },
                };
            } else {
                this.chartSeries = [];
                this.chartOptions = {
                        ...this.heatMapChartOptions,
                        title: {
                            text: this.$i18n.get('label_activities_last_year')
                        },
                }
            }

            setTimeout(() => this.isBuildingChart = false, 500);
        }
    }
}
</script>