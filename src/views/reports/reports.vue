<template>
    <div
            id="tainacan-reports-app"
            class="has-mounted wrap">
        <router-view /> 
    </div> 
</template>

<script>
export default { 
    name: "ReportsPage",
}
</script>

<style lang="scss">

    // TAINACAN Variables
    @import "../admin/scss/_animations.scss";
    @import "../admin/scss/_variables.scss";

    // Bulma imports
    @import "./scss/reports-basics.sass";

    .tainacan_page_tainacan_reports #wpbody {
        overflow-x: hidden;
    }
</style>