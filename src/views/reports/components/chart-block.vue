<template>
    <article class="tile is-child">
        <apexchart
                height="350" 
                :options="chartOptions" 
                :series="chartSeries" />
    </article>
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