<template>
        <div class="primary-page page-container">
            <tainacan-title />
            <metadata-list/>
        </div>
</template>

<script>
import MetadataList from '../../components/lists/metadata-list.vue';

export default {
    name: 'MetadataPage',
    data(){
        return {
        }
    },
    components: {
        MetadataList
    }
}
</script>

<style scoped>

</style>


