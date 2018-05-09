<template>
    <items-page
            class="theme-items-list" 
            :collection-id="$root.collectionId" />
</template>

<script>
export default {
    name: "ThemeItemsList",
}
</script>


<style lang="scss">
    .theme-items-list {
        .button {
            border: none;
            border-radius: 0;
            padding: 2px 14px;
            color: #1d1d1d;
            font-size: 1.0em;
        }
        .sub-header {
            
        }
        .filters-menu {
           
        }
        .table-container {
            
        }
    }
</style>
