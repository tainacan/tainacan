export const reportsChartMixin = {
    props: {
        chartData: {},
        isFetchingData: false
    },
    data () {
        return {
           isBuildingChart: false,
           chartSeries: [],
           chartOptions: {}
        }
    }
};
