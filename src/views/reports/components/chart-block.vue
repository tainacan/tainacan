<template>
    <apexchart
            height="380px"
            :options="chartOptions" 
            :series="chartSeries" />
</template>

<script>
export default {
    props: {
        chartTitle: String,
        chartOptions: Object,
        chartSeries: Array
    }
}
</script>