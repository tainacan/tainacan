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
            border-radius: 1;
            padding: 4px 16px;
            color: #1d1d1d;
            font-size: 1.0em;
            font-weight: normal;
            cursor: pointer;
            background-color: white;
        }
        .select select{
            border: none;
            border-radius: 1;
            padding: 4px 16px;
            color: #1d1d1d;
            font-size: 1.0em;
            font-weight: normal;
            cursor: pointer;
            background-color: white;
        }
        .sub-header {
           
        }
        .filters-menu {
           
        }
        .table-container {
            
        }
    }
</style>
