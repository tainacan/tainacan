<template>
    <div :class="{ 'repository-level-page page-container': isRepositoryLevel }">
        <tainacan-title :bread-crumb-items="[{ path: '', label: $i18n.get('mappers') }]"/>
        
        <template v-if="isRepositoryLevel">
            <p>{{ $i18n.get('info_repository_metadata_inheritance') }}</p>
        </template>

        <div>
            <b-loading
                    :is-full-page="true" 
                    :active.sync="isLoading" 
                    :can-cancel="false"/>

            <mappers-list
                    :is-repository-level="isRepositoryLevel"
                    :is-loading="isLoading"
                    :mappers="mappers" />

            <!-- Empty state -->
            <div v-if="mappers.length <= 0 && !isLoading && $userCaps.hasCapability('tnc_rep_edit_users')">
                <section class="section">
                    <div class="content has-text-grey has-text-centered">
                        <p>
                            <span class="icon is-medium">
                                <i class="tainacan-icon tainacan-icon-30px tainacan-icon-processes tainacan-icon-rotate-90"/>
                            </span>
                        </p>
                        <p>
                            {{ $i18n.get('info_no_mappers_found') }}
                        </p>
                    </div>
                </section>
            </div>

        </div>
    </div>
</template>

<script>
import MappersList from '../../components/lists/mappers-list.vue';
import { mapActions, mapGetters } from 'vuex';

export default {
    name: 'MappersPage',
    components: {
        MappersList,
    },
    data() {
        return {
            isRepositoryLevel: false,
            isLoading: false
        }
    },
    computed: {
        mappers() {
            return this.getMappers();
        },
        collection() {
            return this.getCollection();
        }
    },
    created() {
        this.isRepositoryLevel = (this.$route.params.collectionId === undefined);
    },
    mounted() {

        this.loadMappers();

        if (!this.isRepositoryLevel)
            this.$root.$emit('onCollectionBreadCrumbUpdate', [{ path: '', label: this.$i18n.get('mappers') }]);
    },
    methods: {
        ...mapGetters('metadata',[
            'getMappers',
        ]),
        ...mapActions('metadata', [
            'fetchMappers',
        ]),
        loadMappers() {
            this.isLoading = true;
            this.fetchMappers()
                .then(() => {
                    this.isLoading = false;
                })
                .catch(() => {
                    this.isLoading = false;
                });
        },
    }
}
</script>
