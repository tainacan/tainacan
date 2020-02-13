<template>
    <div>

        <b-loading
                :active.sync="isLoading"
                :can-cancel="false"/>

        <div class="tainacan-page-title">
            <h1>
            <span
                    v-if="(item != null && item != undefined && item.status != undefined && !isLoading)"
                    class="status-tag">
                {{ $i18n.get('status_' + item.status) }}
            </span>
                {{ $i18n.get('title_item_page') + ' ' }}
                <span style="font-weight: 600;">{{ (item != null && item != undefined) ? item.title : '' }}</span>
            </h1>
            <a
                    @click="$router.go(-1)"
                    class="back-link has-text-secondary">
                {{ $i18n.get('back') }}
            </a>
            <hr>
        </div>
        <div class="tainacan-form">
            <div class="columns">
                <div class="column is-5">

                    <!-- Hook for extra Form options -->
                    <template
                            v-if="formHooks != undefined &&
                        formHooks['view-item'] != undefined &&
                        formHooks['view-item']['begin-left'] != undefined">
                        <div
                                id="view-item-begin-left"
                                class="form-hook-region"
                                v-html="formHooks['view-item']['begin-left'].join('')"/>
                    </template>

                    <!-- Document -------------------------------- -->
                    <div class="section-label">
                        <label>{{ item.document !== undefined && item.document !== null && item.document !== ''
                            ?
                            $i18n.get('label_document') : $i18n.get('label_document_empty') }}</label>
                    </div>
                    <div class="section-box">
                        <div
                                v-if="item.document !== undefined && item.document !== null &&
                                item.document_type !== undefined && item.document_type !== null &&
                                item.document !== '' && item.document_type !== 'empty'">

                            <div v-if="item.document_type === 'attachment'">
                                <!-- <div v-html="item.document_as_html"/> -->
                                <document-item :document-html="item.document_as_html"/>
                            </div>

                            <div v-else-if="item.document_type === 'text'">
                                <div v-html="item.document_as_html"/>
                            </div>

                            <div v-else-if="item.document_type === 'url'">
                                <div v-html="item.document_as_html"/>
                            </div>
                        </div>
                        <div v-else>
                            <p>{{ $i18n.get('info_no_document_to_item') }}</p>
                        </div>
                    </div>

                    <!-- Thumbnail -------------------------------- -->
                    <div class="section-label">
                        <label>{{ $i18n.get('label_thumbnail') }}</label>
                    </div>
                    <div class="section-box section-thumbnail">
                        <div class="thumbnail-field">
                            <file-item
                                    v-if="item.thumbnail != undefined && ((item.thumbnail['tainacan-medium'] != undefined && item.thumbnail['tainacan-medium'] != false) || (item.thumbnail.medium != undefined && item.thumbnail.medium != false))"
                                    :show-name="false"
                                    :modal-on-click="false"
                                    :size="178"
                                    :file="{
                                media_type: 'image',
                                thumbnails: { 'tainacan-medium': [ item.thumbnail['tainacan-medium'] ? item.thumbnail['tainacan-medium'][0] : item.thumbnail.medium[0] ] },
                                title: $i18n.get('label_thumbnail'),
                                description: `<img alt='` + $i18n.get('label_thumbnail') + `' src='` + item.thumbnail.full[0] + `'/>` 
                            }"/>
                            <figure
                                    v-if="item.thumbnail == undefined || ((item.thumbnail.medium == undefined || item.thumbnail.medium == false) && (item.thumbnail['tainacan-medium'] == undefined || item.thumbnail['tainacan-medium'] == false))"
                                    class="image">
                                <span class="image-placeholder">{{ $i18n.get('label_empty_thumbnail') }}</span>
                                <img
                                        :alt="$i18n.get('label_thumbnail')"
                                        :src="thumbPlaceholderPath">
                            </figure>
                        </div>
                    </div>        

                    <!-- Hook for extra Form options -->
                    <template
                            v-if="formHooks != undefined &&
                        formHooks['view-item'] != undefined &&
                        formHooks['view-item']['end-left'] != undefined">
                        <div
                                id="view-item-end-left"
                                class="form-hook-region"
                                v-html="formHooks['view-item']['end-left'].join('')"/>
                    </template>

                </div>
                <div class="column is-7">

                    <!-- Hook for extra Form options -->
                    <template
                            v-if="formHooks != undefined &&
                        formHooks['view-item'] != undefined &&
                        formHooks['view-item']['begin-right'] != undefined">
                        <div
                                id="view-item-begin-right"
                                class="form-hook-region"
                                v-html="formHooks['view-item']['begin-right'].join('')"/>
                    </template>

                    <div
                            style="flex-wrap: wrap"
                            class="columns">

                        <!-- Collection -------------------------------- -->
                        <div class="column is-narrow">
                            <div class="section-label">
                                <label>{{ $i18n.get('collection') }}</label>
                            </div>
                            <div class="section-status">
                                <div class="field has-addons">
                                    <span>
                                        <span class="icon">
                                            <i class="tainacan-icon tainacan-icon-collection"/>
                                        </span>
                                        {{ collection && collection.name ? collection.name : '' }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Visibility (status public or private) -------------------------------- -->
                        <div class="column is-narrow">
                            
                            <div class="section-label">
                                <label>{{ $i18n.get('label_visibility') }}</label>
                            </div>
                            <div class="section-status">
                                <div class="field has-addons">
                                    <span v-if="item.status != 'private'">
                                        <span class="icon">
                                            <i class="tainacan-icon tainacan-icon-public"/>
                                        </span> {{ $i18n.get('publish_visibility') }}
                                    </span>
                                    <span v-if="item.status == 'private'">
                                        <span class="icon">
                                            <i class="tainacan-icon tainacan-icon-private"/>
                                        </span>  {{ $i18n.get('private_visibility') }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Comment Status ------------------------ -->
                        <div
                                v-if="collection && collection.allow_comments && collection.allow_comments == 'open'"
                                class="column is-narrow">
                            <div class="section-label">
                                <label>{{ $i18n.get('label_comments') }}</label>
                            </div>
                            <div class="section-status">
                                <div class="field has-addons">
                                    <span>
                                        <span
                                                v-if="item.comment_status != 'open'"
                                                class="icon">
                                            <i class="tainacan-icon tainacan-icon-close"/>
                                        </span>
                                        <span
                                                v-if="item.comment_status == 'open'"
                                                class="icon">
                                            <i class="tainacan-icon tainacan-icon-approved"/>
                                        </span>
                                        {{ item.comment_status == 'open' ? $i18n.get('label_allowed') : $i18n.get('label_not_allowed') }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <b-tabs v-model="activeTab">
                        <b-tab-item>
                            <template slot="header">
                                <span class="icon has-text-gray4">
                                    <i class="tainacan-icon tainacan-icon-18px tainacan-icon-metadata"/>
                                </span>
                                <span>{{ $i18n.get('metadata') }}</span>
                            </template>

                            <!-- Metadata -------------------------------- -->
                            <div class="metadata-area">
                                <div
                                        v-for="(metadatum, index) of metadatumList"
                                        :key="index"
                                        class="field">
                                    <label class="label">{{ metadatum.metadatum.name }}</label>
                                    <div
                                            :class="{ 'metadata-type-textarea': metadatum.metadatum.metadata_type_object.component == 'tainacan-textarea' }"
                                            class="content">
                                        <p v-html="metadatum.value_as_html != '' ? metadatum.value_as_html : `<span class='has-text-gray is-italic'>` + $i18n.get('label_value_not_informed') + `</span>`"/>
                                    </div>
                                </div>
                            </div>

                            <!-- Hook for extra Form options -->
                            <template
                                    v-if="formHooks != undefined &&
                                formHooks['view-item'] != undefined &&
                                formHooks['view-item']['end-right'] != undefined">
                                <div
                                        id="view-item-end-right"
                                        class="form-hook-region"
                                        v-html="formHooks['view-item']['end-right'].join('')"/>
                            </template>
                        </b-tab-item>

                        <b-tab-item>
                            <template slot="header">
                                <span class="icon has-text-gray4">
                                    <i class="tainacan-icon tainacan-icon-18px tainacan-icon-attachments"/>
                                </span>
                                <span>
                                    {{ $i18n.get('label_attachments') }}
                                    <span
                                            v-if="totalAttachments != null && totalAttachments != undefined"
                                            class="has-text-gray">
                                        ({{ totalAttachments }})
                                    </span>
                                </span>
                            </template>
                        
                            <attachments-list
                                    v-if="item != undefined && item.id != undefined"
                                    :item="item" />    
                        </b-tab-item>

                        <b-tab-item>
                            <template slot="header">
                                <span class="icon has-text-gray4">
                                    <i class="tainacan-icon tainacan-icon-18px tainacan-icon-activities"/>
                                </span>
                                <span>{{ $i18n.get('activities') }}</span>
                            </template>
                            
                            <activities-page
                                    :is-loading.sync="isLoadingAttachments"
                                    @isLoadingAttachments="(isLoading) => isLoadingAttachments = isLoading"/>
                        </b-tab-item>
                    </b-tabs>
                </div>
            </div>
            <div class="footer">
                <div class="form-submission-footer">
                    <router-link
                            v-if="item.current_user_can_edit && $route.query.recent == true"
                            class="button is-secondary"
                            style="margin-right: auto;"
                            :to="{ path: $routerHelper.getNewItemPath(collectionId)}">
                        <!-- <span class="icon is-large">
                            <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-add"/>
                        </span> -->
                        <span>{{ $i18n.get('label_create_another_item') }}</span>
                    </router-link>
                    <router-link
                            v-if="item.current_user_can_edit"
                            class="button sequence-button"
                            :to="{ path: $routerHelper.getItemEditPath(collectionId, itemId)}">
                        <span class="icon is-large">
                            <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-edit"/>
                        </span>
                        <span>{{ $i18n.getFrom('items','edit_item') }}</span>
                    </router-link>
                    <button 
                            class="button sequence-button"
                            :aria-label="$i18n.get('label_view_as')"
                            :disabled="isLoading"
                            @click="openExposersModal()">
                        <span class="icon is-large">
                            <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-url"/>
                        </span>
                        <span class="is-hidden-touch">{{ $i18n.get('label_view_as') }}</span>
                    </button>
                    <a
                            target="_blank"
                            class="button sequence-button is-pulled-right"
                            :href="item.url">
                        <span class="icon is-large">
                            <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-see"/>
                        </span>
                        <span>{{ $i18n.get('label_item_page_on_website') }}</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import {mapActions, mapGetters} from 'vuex';
    import FileItem from '../../components/other/file-item.vue';
    import DocumentItem from '../../components/other/document-item.vue';
    import {formHooks} from '../../js/mixins';
    import ActivitiesPage from '../lists/activities-page.vue';
    import ExposersModal from '../../components/modals/exposers-modal.vue';
    import AttachmentsList from '../../components/lists/attachments-list.vue';

    export default {
        name: 'ItemPage',
        components: {
            FileItem,
            DocumentItem,
            ActivitiesPage,
            ExposersModal,
            AttachmentsList
        },
        mixins: [formHooks],
        data() {
            return {
                collectionId: Number,
                itemId: Number,
                isLoading: false,
                open: true,
                thumbPlaceholderPath: tainacan_plugin.base_url + '/assets/images/placeholder_square.png',
                urls_open: false,
                activeTab: 0,
                isLoadingAttachments: false
            }
        },
        computed: {
            collection() {
                return this.getCollection();
            },
            item() {
                // Fills hook forms with it's real values 
                this.updateExtraFormData(this.getItem());

                return this.getItem();
            },
            metadatumList() {
                return JSON.parse(JSON.stringify(this.getMetadata()));
            },
            totalAttachments() {
                return this.getTotalAttachments();
            }
        },
        created() {
            // Obtains item and collection ID
            this.collectionId = this.$route.params.collectionId;
            this.itemId = this.$route.params.itemId;

            // Puts loading on Item Loading
            this.isLoading = true;

            // Obtains Item
            this.fetchItem({ 
                itemId: this.itemId,
                contextEdit: true,    
                fetchOnly: 'title,thumbnail,status,modification_date,document_type,document,comment_status,document_as_html'       
            })
            .then((item) => {
                this.$root.$emit('onCollectionBreadCrumbUpdate', [
                    {path: this.$routerHelper.getCollectionPath(this.collectionId), label: this.$i18n.get('items')},
                    {path: '', label: item.title}
                ]);
                this.loadMetadata();
            });

        },
        methods: {
            ...mapActions('item', [
                'fetchItem',
                'fetchMetadata',
            ]),
            ...mapGetters('item', [
                'getItem',
                'getMetadata',
                'getTotalAttachments'
            ]),
            ...mapGetters('collection', [
                'getCollection'
            ]),
            loadMetadata() {
                // Obtains Item Metadatum
                this.fetchMetadata(this.itemId).then(() => {
                    this.isLoading = false;
                });
            },
            openExposersModal() {
                this.$buefy.modal.open({
                    parent: this,
                    component: ExposersModal,
                    hasModalCard: true,
                    props: { 
                        collectionId: this.collectionId,
                        itemId: this.itemId,
                        itemURL: this.item.url,
                        totalItems: 1,
                    },
                    trapFocus: true
                });
            }
        }
    }
</script>

<style lang="scss" scoped>

    @import '../../scss/_variables.scss';

    .page-container {
        padding: 25px 0;

        & > .tainacan-form {
            margin-bottom: 110px;

            .field:not(:last-child) {
                margin-bottom: 0.5em;
            }
        }

        .tainacan-page-title {
            padding: 0 $page-side-padding;
            margin-bottom: 35px;
            display: flex;
            flex-wrap: wrap;
            align-items: flex-end;
            justify-content: space-between;

            h1, h2 {
                font-size: 1.25em;
                font-weight: 500;
                color: var(--tainacan-heading-color);
                display: inline-block;
                width: 80%;
                flex-shrink: 1;
                flex-grow: 1;
                text-overflow: ellipsis;
                white-space: nowrap;
                overflow: hidden;
                max-width: 80%;
            }
            .status-tag {
                color: var(--tainacan-white);
                background: var(--tainacan-turquoise5);
                padding: 0.15em 0.5em;
                font-size: 0.75em;
                margin: 0 1em 0 0;
                font-weight: 600;
                position: relative;
                top: -2px;
            }
            a.back-link {
                font-weight: 500;
                float: right;
                margin-top: 5px;
            }
            hr {
                margin: 3px 0px 4px 0px;
                height: 1px;
                background-color: var(--tainacan-secondary);
                width: 100%;
            }
        }

        .tainacan-form > .columns {
            margin-bottom: 70px;
            margin-left: $page-side-padding;
            margin-right: $page-side-padding;
        }

        .column.is-5 {
            padding-left: $page-side-padding;
            padding-right: $page-side-padding;

            @media screen and (max-width: 769px) {
                width: 100%;
            }
        }
        .column.is-7 {
            padding-left: 0;
            padding-right: $page-side-padding;

            .columns {
                flex-wrap: wrap;
                justify-content: space-between;

                .column {
                    padding: 1em 12px 0 12px;
                }
            }
            .field { 
                padding: 10px 0 14px 0px;
                margin-left: -3px;
            }

            @media screen and (max-width: 769px) {
                padding-left: $page-side-padding;
                width: 100%;
            }
        }
    
        .b-tabs {
            overflow: hidden !important;
            margin-top: -15px;
        }
    }

    .metadata-area {
        .field {
            border-bottom: 1px solid var(--tainacan-gray2);
            padding: 10px 25px;
            margin-left: 0px !important;

            .label {
                font-size: 0.875em;
                font-weight: 500;
                margin-bottom: 0.5em;
                display: inline-flex;
                align-items: center;

                span {
                    margin-right: 18px;
                }
            }
        } 
        
    }

    .section-label {
        position: relative;
        label {
            font-size: 1em !important;
            font-weight: 500 !important;
            color: var(--tainacan-gray5) !important;
            line-height: 1.2em;
        }
    }

    .section-box {
        background-color: var(--tainacan-white);
        padding: 0 $page-side-padding 0 0;
        margin-top: 18px;
        margin-bottom: 32px;

        ul {
            display: flex;
            justify-content: space-evenly;
            li {
                text-align: center;
                button {
                    border-radius: 50px;
                    height: 72px;
                    width: 72px;
                    border: none;
                    background-color: var(--tainacan-gray2);
                    color: var(--tainacan-secondary);
                    margin-bottom: 6px;
                    &:hover {
                        background-color: var(--tainacan-turquoise2);
                        cursor: pointer;
                    }
                }
                p {
                    color: var(--tainacan-secondary);
                }
            }
        }
    }

    .section-status {
        padding-bottom: 16px;
        font-size: 0.75em;

        .field {
            border-bottom: none;

            .icon {
                font-size: 1.125em !important;
                color: var(--tainacan-gray3);
            }
        }
    }

    .section-attachments {
        margin-top: 0;
        p {
            margin: 4px 15px
        }
    }

    .uploaded-files {
        display: flex;
        flex-flow: wrap;
        margin-left: -15px;
        margin-right: -15px;
    }

    .thumbnail-field {

        .content {
            padding: 10px;
            font-size: 0.8em;
        }
        img {
            height: 178px;
            width: 178px;
        }
        .image-placeholder {
            position: absolute;
            margin-left: 45px;
            margin-right: 45px;
            font-size: 0.8em;
            font-weight: bold;
            z-index: 99;
            text-align: center;
            color: var(--tainacan-info-color);
            top: 70px;
            max-width: 90px;
        }
    }

    .footer {
        padding: 18px $page-side-padding;
        position: absolute;
        bottom: 0;
        z-index: 999999;
        background-color: var(--tainacan-gray1);
        width: 100%;
        height: 65px;

        .form-submission-footer {
            width: 100%;
            display: flex;
            justify-content: flex-end;

            .button {
                margin-left: 16px;
                margin-right: 6px;
            }
            .button.is-outlined {
                margin-left: 0px;
                margin-right: auto;
            }
        }

        .sequence-button {
            background-color: transparent;
            color: var(--tainacan-turquoise5);
            border: none;

            .icon {
                margin-right: 5px !important;
            }
        }
    }
</style>

