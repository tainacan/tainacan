<template>
    <div class="repository-level-page page-container">
        <tainacan-title 
                :bread-crumb-items="[{ path: '', label: this.$i18n.get('metadata') }]"/>

        <template v-if="$userCaps.hasCapability('tnc_rep_edit_metadata')">
            <metadata-list/>
        </template>
        <template v-else>
            <section class="section">
                <div class="content has-text-grey has-text-centered">
                    <span class="icon">
                        <i class="tainacan-icon tainacan-icon-30px tainacan-icon-metadata"/>
                    </span>
                    <p>{{ $i18n.get('info_can_not_edit_metadata') }}</p>
                </div>
            </section>
        </template>
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


