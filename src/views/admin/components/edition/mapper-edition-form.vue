<template>
    <div class="page-container repository-level-page">
        <tainacan-title 
                :bread-crumb-items="[
                    { path: $routerHelper.getMappersPath(), label: $i18n.get('mappers') },
                    { path: '', label: (mapper != null && mapper.name != undefined) ? mapper.name : $i18n.get('mapper') }
                ]"/>
        <metadata-mapping-list
                v-if="(isRepositoryLevel && $userCaps.hasCapability('tnc_rep_edit_metadata') || (!isRepositoryLevel && collection && collection.current_user_can_edit_metadata))"
                :is-repository-level="isRepositoryLevel"/>
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
                mapper: null,
                isLoading: false,
                isUpdatingSlug: false,
                isRepositoryLevel: false,
                collectionId: null
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
                })
                .catch(() => this.isLoading = false);
        },
        methods: {
            ...mapActions('metadata', [
                'updateMapper',
                'fetchMapper',
            ]),
            ...mapGetters('metadata',[
                'getMapper',
            ]),
            
        }
    }
</script>
<style lang="scss" scoped>

    .tab-content {
        overflow: visible !important;
    }
    .status-radios {
        display: flex;
    }
    .status-radios .control-lable {
        display: flex;
        align-items: center;
    }
    .tainacan-form>.columns {
        margin-bottom: 48px;
    }
    .tainacan-form .column:last-of-type {
        padding-left: var(--tainacan-one-column) !important;
    }
    .two-columns-fields {
        column-width: 180px;

        .field {
            margin-bottom: 0px;
        }
    }
    .form-submit {
        align-items: center;
    }
    .updated-at {
        margin: 0 1em 0 auto;
        color: var(--tainacan-info-color);
        font-style: italic;
    }

    .footer {
        padding: 14px var(--tainacan-one-column);
        position: fixed;
        bottom: 0;
        right: 0;
        z-index: 9999;
        background-color: var(--tainacan-gray1);
        width: calc(100% - var(--tainacan-sidebar-width, 3.25em));
        height: 60px;
        display: flex;
        justify-content: flex-end;
        align-items: center;
        transition: bottom 0.5s ease, width 0.2s linear;

        .footer-message {
            display: flex;
            align-items: center;
        }

        .update-info-section {
            color: var(--tainacan-info-color);
            margin-right: auto;
            display: flex;
            flex-wrap: nowrap;
        }

        .help {
            display: inline-flex;
            font-size: 1.0em;
            margin-top: 0;
            margin-left: 24px;

            .tainacan-help-tooltip-trigger {
                margin-left: 0.25em;
            }
        }

        .link-button {
            background-color: transparent;
            border: none;
        }

        @media screen and (max-width: 769px) {
            padding: 13px 0.5em;
            width: 100%;
            flex-wrap: wrap;
            height: auto;
            position: fixed;

            .update-info-section {
                margin-left: auto;margin-bottom: 0.75em;
                margin-top: -0.25em;
            }
        }
    }
</style>

