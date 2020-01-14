<template>
    <div class="repository-level-page page-container">
        <tainacan-title 
                :bread-crumb-items="[{ path: '', label: this.$i18n.get('metadata') }]"/>

        <metadata-list/>
    </div>
</template>

<script>
import MetadataList from '../../components/lists/metadata-list.vue';

export default {
    name: 'MetadataPage',
    components: {
        MetadataList
    },
    data(){
        return {
        }
    }
}
</script>

<style scoped>

</style>


