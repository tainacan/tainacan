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
    #tainacan-roles-app {
        a:hover {
            cursor: pointer;
        }
    }
</style>