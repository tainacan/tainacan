<template>
    <div
            :class="{ 'repository-level-page page-container': isRepositoryLevel }"
            style="padding-bottom: 60px">
        <tainacan-title 
                :bread-crumb-items="[
                    { path: $routerHelper.getMappersPath(), label: $i18n.get('mappers') },
                    { path: '', label: (mapper != null && mapper.name != undefined) ? mapper.name : $i18n.get('mapper') }
                ]" />
        <metadata-mapping-list
                v-if="(isRepositoryLevel && $userCaps.hasCapability('tnc_rep_edit_metadata') || (!isRepositoryLevel && collection && collection.current_user_can_edit_metadata))"
                :is-repository-level="isRepositoryLevel"
                :mapper="mapper" />
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
</template>

<script>
    import { wpAjax, formHooks } from "../../js/mixins";
    import { mapActions, mapGetters } from 'vuex';
    import MetadataMappingList from '../lists/metadata-mapping-list.vue';

    export default {
        name: 'MapperEditionForm',
        components: {
            MetadataMappingList
        },
        mixins: [ wpAjax, formHooks ],
        data(){
            return {
                isLoading: false,
                isUpdatingSlug: false,
                isRepositoryLevel: false,
                collectionId: null
            }
        },
        computed: {
            collection() {
                return this.getCollection();
            },
            mapper() {
                return this.getMapper();
            }
        },
        created() {
            this.isRepositoryLevel = (this.$route.params.collectionId === undefined);
            this.collectionId = this.$route.params.collectionId;
        },
        mounted() {

            this.isLoading = true;

            const mapperSlug = this.$route.params['mapperSlug'];
            
            this.fetchMapper({ collectionId: this.collectionId, mapperSlug: mapperSlug })
                .then(res => {
                    this.taxonomy = res.taxonomy;
                    this.isLoading = false;

                    if (!this.isRepositoryLevel)
                        this.$root.$emit('onCollectionBreadCrumbUpdate', [
                            { path: this.$routerHelper.getMappersPath(), label: this.$i18n.get('mappers') },
                            { path: '', label: (this.mapper != null && this.mapper.name != undefined) ? this.mapper.name : this.$i18n.get('mapper') }
                        ]);
                })
                .catch(() => this.isLoading = false);
        },
        methods: {
            ...mapActions('metadata', [
                'fetchMapper',
            ]),
            ...mapGetters('metadata',[
                'getMapper',
            ]),
            ...mapGetters('collection',[
                'getCollection',
            ]),
            
        }
    }
</script>
