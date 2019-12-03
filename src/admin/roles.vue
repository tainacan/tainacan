<template>
    <div
            id="tainacan-roles-app"
            class="wrap">
        <router-view /> 
    </div> 
</template>

<script>
    export default { 
        name: "RolesPage"
    }
</script>

<style lang="scss">
    .tainacan_page_tainacan_roles #wpbody {
        overflow-x: hidden;
    }
    #tainacan-roles-app {
        a:hover {
            cursor: pointer;
        }
    }
</style>