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
                                guid: { rendered: item.thumbnail['tainacan-medium'] ? item.thumbnail['tainacan-medium'][0] : item.thumbnail.medium[0] },
                                title: { rendered: $i18n.get('label_thumbnail')},
                                description: { rendered: `<img alt='` + $i18n.get('label_thumbnail') + `' src='` + item.thumbnail.full[0] + `'/>` }}"/>
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

                    <!-- Comment Status ------------------------ -->
                    <b-field
                            :addons="true"
                            :label="$i18n.get('label_comment_status') + ': '"
                            v-if="collectionAllowComments == 'open'">
                        <!-- <b-switch
                                id="tainacan-checkbox-comment-status"
                                size="is-small"
                                true-value="open"
                                false-value="closed"
                                v-model="item.comment_status"
                                disabled/> -->
                        <span style="font-size: 0.875rem; top: -0.15rem; position: relative;">{{ item.comment_status == 'open' ? $i18n.get('label_yes') : $i18n.get('label_no') }}</span>
                    </b-field>
                    <br>

                    <!-- Attachments ------------------------------------------ -->
                    <div class="section-label">
                        <label>{{ $i18n.get('label_attachments') }}</label>
                    </div>
                    <div class="section-box section-attachments">
                        <div class="uploaded-files">
                            <file-item
                                    :style="{ margin: 15 + 'px'}"
                                    v-if="attachmentsList.length > 0"
                                    v-for="(attachment, index) in attachmentsList"
                                    :key="index"
                                    :show-name="true"
                                    :modal-on-click="true"
                                    :file="attachment"/>
                            <p v-if="attachmentsList.length <= 0"><br>{{
                                $i18n.get('info_no_attachments_on_item_yet') }}</p>
                        </div>
                    </div>
                    <!-- Exposers --------------------------------------------- -->
                    <!-- <div>
                        <b-loading :active.sync="isLoadingMetadatumMappers"/>
                        <div v-if="!isLoadingMetadatumMappers">
                            <b-collapse :open="false">
                                <div
                                        class="section-label"
                                        slot="trigger"
                                        slot-scope="session_props">
                                    <label>
                                        {{ $i18n.get('label_exposer_urls') }}
                                        <span class="icon">
                                    <i
                                            :class="{ 'tainacan-icon-arrowdown' : session_props.open, 'tainacan-icon-arrowright' : !session_props.open }"
                                            class="has-text-secondary tainacan-icon tainacan-icon-20px"/>
                                </span>
                                    </label>
                                </div>
                                <br>
                                <a
                                        class="collapse-all"
                                        @click="urls_open = !urls_open">
                                    {{ urls_open ? $i18n.get('label_collapse_all') :
                                    $i18n.get('label_expand_all') }}
                                    <span class="icon">
                                <i
                                        :class="{ 'tainacan-icon-arrowdown' : urls_open, 'tainacan-icon-arrowright' : !urls_open }"
                                        class="has-text-secondary tainacan-icon tainacan-icon-20px"/>
                            </span>
                                </a>
                                <div>
                                    <div
                                            v-for="(exposer, index) of item.exposer_urls"
                                            :key="index"
                                            class="field">
                                        <b-collapse :open="urls_open">
                                            <label
                                                    class="label"
                                                    slot="trigger"
                                                    slot-scope="props">
                                        <span class="icon">
                                            <i
                                                    :class="{ 'tainacan-icon-arrowdown' : props.open, 'tainacan-icon-arrowright' : !props.open }"
                                                    class="has-text-secondary tainacan-icon tainacan-icon-20px"/>
                                        </span>
                                                {{ index }}
                                            </label>
                                            <div
                                                    v-for="(url, index2) of exposer"
                                                    :key="index2">
                                                <div>
                                                    <a
                                                            :href="url"
                                                            target="_blank">
                                                        {{ extractExposerLabel(url, index) }}
                                                    </a>
                                                </div>
                                            </div>
                                        </b-collapse>
                                    </div>
                                </div>
                            </b-collapse>
                        </div>
                    </div> -->

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

                    <div class="columns">
                        <div class="column">
                            <!-- Visibility (status public or private) -------------------------------- -->
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
                        <div class="column">
                                <!-- Collection -------------------------------- -->
                            <div class="section-label">
                                <label>{{ $i18n.get('collection') }}</label>
                            </div>
                            <div class="section-status">
                                <div class="field has-addons">
                                    <span>
                                        {{ collectionName }}
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
                            <div class="section-label">
                                <label>{{ $i18n.get('metadata') }}</label>
                            </div>
                            <br>
                            <a
                                    class="collapse-all"
                                    @click="open = !open">
                                {{ open ? $i18n.get('label_collapse_all') : $i18n.get('label_expand_all') }}
                                <span class="icon">
                                    <i
                                            :class="{ 'tainacan-icon-arrowdown' : open, 'tainacan-icon-arrowright' : !open }"
                                            class="has-text-secondary tainacan-icon tainacan-icon-20px"/>
                                </span>
                            </a>
                            <div class="metadata-area">
                                <div
                                        v-for="(metadatum, index) of metadatumList"
                                        :key="index"
                                        class="field">
                                    <b-collapse
                                            :aria-id="'metadatum-collapse-' + metadatum.id" 
                                            animation="filter-item"
                                            :open="open">
                                        <label
                                                class="label"
                                                slot="trigger"
                                                slot-scope="props">
                                    <span class="icon">
                                            <i
                                                    :class="{ 'tainacan-icon-arrowdown' : props.open, 'tainacan-icon-arrowright' : !props.open }"
                                                    class="has-text-secondary tainacan-icon tainacan-icon-20px"/>
                                        </span>
                                            {{ metadatum.metadatum.name }}
                                        </label>
                                        <div
                                                :class="{ 'metadata-type-textarea': metadatum.metadatum.metadata_type_object.component == 'tainacan-textarea' }"
                                                class="content">
                                            <p v-html="metadatum.value_as_html != '' ? metadatum.value_as_html : `<span class='has-text-gray is-italic'>` + $i18n.get('label_value_not_informed') + `</span>`"/>
                                        </div>
                                    </b-collapse>
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
                                    <i class="tainacan-icon tainacan-icon-18px tainacan-icon-activities"/>
                                </span>
                                <span>{{ $i18n.get('activities') }}</span>
                            </template>
                            
                            <div class="section-label">
                                <label>{{ $i18n.get('activities') }}</label>
                            </div>
                            <br>
                            <activities-page />
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
                        {{ $i18n.get('label_create_another_item') }}
                    </router-link>
                    <router-link
                            v-if="item.current_user_can_edit"
                            class="button is-secondary"
                            :to="{ path: $routerHelper.getItemEditPath(collectionId, itemId)}">
                        {{ $i18n.getFrom('items','edit_item') }}
                    </router-link>
                    <a
                            target="_blank"
                            class="button is-success is-pulled-right"
                            :href="item.url">
                        {{ $i18n.getFrom('items', 'view_item') }}
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

    export default {
        name: 'ItemPage',
        mixins: [formHooks],
        data() {
            return {
                collectionId: Number,
                itemId: Number,
                isLoading: false,
                isLoadingMetadatumMappers: false,
                open: true,
                collectionName: '',
                thumbPlaceholderPath: tainacan_plugin.base_url + '/admin/images/placeholder_square.png',
                urls_open: false,
                collectionAllowComments: '',
                activeTab: 0,
            }
        },
        components: {
            FileItem,
            DocumentItem,
            ActivitiesPage
        },
        methods: {
            ...mapActions('item', [
                'fetchItem',
                'fetchAttachments',
                'fetchMetadata',
            ]),
            ...mapActions('collection', [
                'fetchCollectionName',
                'fetchCollectionAllowComments'
            ]),
            ...mapGetters('item', [
                'getItem',
                'getMetadata',
                'getAttachments'
            ]),
            ...mapGetters('metadata', [
                'getMetadatumMappers'
            ]),
            ...mapActions('metadata', [
                'fetchMetadatumMappers',
            ]),
            loadMetadata() {
                // Obtains Item Metadatum
                this.fetchMetadata(this.itemId).then(() => {
                    this.isLoading = false;
                });
            },
            extractExposerLabel(urlString, typeSlug) {
                let url = new URL(urlString);
                let mapperParam = url.searchParams.get(tainacan_plugin.exposer_mapper_param);
                if (mapperParam != 'undefined' && mapperParam != null) {
                    let mapper = this.metadatum_mappers.find(obj => {
                        return obj.slug === mapperParam;
                    });
                    if (mapper != 'undefined' && mapper != null) {
                        return this.$i18n.get('label_exposer') + ": " + typeSlug + ', ' + this.$i18n.get('label_mapper') + ": " + mapper.name;
                    } else {
                        if (mapperParam == 'value') {
                            return this.$i18n.get('label_exposer') + ": " + typeSlug + ', ' + this.$i18n.get('label_exposer_mapper_values');
                        }
                    }
                }
                return this.$i18n.get('label_exposer') + ": " + typeSlug;
            },

        },
        computed: {
            item() {
                // Fills hook forms with it's real values 
                this.updateExtraFormData(this.getItem());

                return this.getItem();
            },
            metadatumList() {
                return JSON.parse(JSON.stringify(this.getMetadata()));
            },
            attachmentsList() {
                return this.getAttachments();
            },
            metadatum_mappers: {
                get() {
                    return this.getMetadatumMappers();
                }
            },
        },
        created() {
            // Obtains item and collection ID
            this.collectionId = this.$route.params.collectionId;
            this.itemId = this.$route.params.itemId;

            // Puts loading on Item Loading
            this.isLoading = true;

            this.isLoadingMetadatumMappers = true;
            this.fetchMetadatumMappers()
                .then(() => {
                    this.isLoadingMetadatumMappers = false;
                })
                .catch(() => {
                    this.isLoadingMetadatumMappers = false;
                });

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

        // Obtains collection name
        if (!this.isRepositoryLevel) {
            this.fetchCollectionName(this.collectionId).then((collectionName) => {
                this.collectionName = collectionName;
            });
        }

            // Get attachments
            this.fetchAttachments(this.itemId);

            // Obtains collection Comment Status
            this.fetchCollectionAllowComments(this.collectionId).then((collectionAllowComments) => {
                this.collectionAllowComments = collectionAllowComments;
            });
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
                margin-bottom: 0.5rem;
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
                font-size: 20px;
                font-weight: 500;
                color: $gray5;
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
                color: white;
                background: $turquoise5;
                padding: 0.15rem 0.5rem;
                font-size: 0.75rem;
                margin: 0 1rem 0 0;
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
                background-color: $secondary;
                width: 100%;
            }
        }

        .tainacan-form > .columns {
            margin-bottom: 70px;
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

            .field {
                padding: 10px 0 14px 30px;
            }

            @media screen and (max-width: 769px) {
                padding-left: $page-side-padding;
                width: 100%;
            }
        }
        .collapse .collapse-content {
            margin-left: 30px;
        }
    }

    .metadata-area {
        .field {
            border-bottom: 1px solid $gray2;
            padding: 10px 25px;

            .label {
                font-size: 14px;
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
            font-size: 16px !important;
            font-weight: 500 !important;
            color: $gray5 !important;
            line-height: 1.2em;
        }
    }

    .collapse-all {
        font-size: 12px;
        display: inline-flex;
        align-items: center;

        .icon {
            vertical-align: bottom;
        }
    }

    .section-box {

        background-color: white;
        padding: 26px;
        margin-top: 16px;
        margin-bottom: 38px;

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
                    background-color: $gray2;
                    color: $secondary;
                    margin-bottom: 6px;
                    &:hover {
                        background-color: $turquoise2;
                        cursor: pointer;
                    }
                }
                p {
                    color: $secondary;
                }
            }
        }
    }

    .section-status {
        padding-bottom: 16px;
        font-size: 0.75rem;

        .field {
            border-bottom: none;

            .icon {
                font-size: 18px !important;
                color: $gray3;
            }
        }
    }

    .section-attachments {
        border: 1px solid $gray2;
        height: 250px;
        max-width: 100%;
        resize: vertical;
        overflow: auto;

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
            font-size: 0.8rem;
            font-weight: bold;
            z-index: 99;
            text-align: center;
            color: $gray4;
            top: 70px;
            max-width: 90px;
        }
    }

    .footer {
        padding: 18px $page-side-padding;
        position: absolute;
        bottom: 0;
        z-index: 999999;
        background-color: $gray1;
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
    }
</style>

