<template>
    <div :class="{ 'repository-level-page page-container': isRepositoryLevel }">
        <tainacan-title 
                    :bread-crumb-items="[{ path: '', label: this.$i18n.get('metadata') }]"/>
        
        <template v-if="isRepositoryLevel">
            <p>{{ $i18n.get('info_repository_metadata_inheritance') }}</p>
            <br>
        </template>
        
        <div class="metadata-list-page">
            <b-tabs 
                    v-if="(isRepositoryLevel && $userCaps.hasCapability('tnc_rep_edit_metadata') || (!isRepositoryLevel && collection && collection.current_user_can_edit_metadata))"
                    v-model="activeTab">    
                <b-tab-item :label="$i18n.get('metadata')">
                    <metadata-list :is-repository-level="isRepositoryLevel"/>
                </b-tab-item>

                <!-- Mapping --------------- -->
                <b-tab-item :label="$i18n.get('mapping')">
                    <metadata-mapping-list :is-repository-level="isRepositoryLevel"/>
                </b-tab-item>
            </b-tabs>
            <section 
                    v-else
                    class="section">
                <div class="content has-text-grey has-text-centered">
                    <p>
                        <span class="icon">
                            <i class="tainacan-icon tainacan-icon-30px tainacan-icon-metadata"/>
                        </span>
                    </p>
                    <p>{{ $i18n.get('info_can_not_edit_metadata') }}</p>
                </div>
            </section>
            </div>
    </div>
</template>

<script>
import MetadataList from '../../components/lists/metadata-list.vue';
import MetadataMappingList from '../../components/lists/metadata-mapping-list.vue';
import { mapGetters } from 'vuex';

export default {
    name: 'MetadataPage',
    components: {
        MetadataList,
        MetadataMappingList
    },
    data() {
        return {
            isRepositoryLevel: false,
            activeTab: 0
        }
    },
    computed: {
        collection() {
            return this.getCollection();
        }
    },
    created() {
        this.isRepositoryLevel = (this.$route.params.collectionId === undefined);
    },
    methods: {
         ...mapGetters('collection', [
            'getCollection',
        ]),
    }
}
</script>

<style scoped>
.metadata-list-page {
        padding-bottom: 0;
}
</style>


