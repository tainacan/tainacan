<template>
    <div>
        <b-loading
                :active.sync="isLoading"
                :can-cancel="false"/>
        <button 
                id="metadata-column-compress-button"
                @click="isMetadataColumnCompressed = !isMetadataColumnCompressed">
            <b-icon :icon="isMetadataColumnCompressed ? 'menu-left' : 'menu-right'" />
        </button>
        <tainacan-title/>
        <div class="tainacan-form">
            <div class="columns">
                <div class="column is-5-5">

                    <!-- Document -------------------------------- -->
                    <div class="section-label">
                        <label>{{ item.document !== undefined && item.document !== null && item.document !== '' ?
                            $i18n.get('label_document') : $i18n.get('label_document_empty') }}</label>
                    </div>
                    <div class="section-box">
                        <div
                                v-if="item.document !== undefined && item.document !== null &&
                                        item.document_type !== undefined && item.document_type !== null &&
                                        item.document !== '' && item.document_type !== 'empty'">

                            <div v-if="item.document_type === 'attachment'">
                                <div v-html="item.document_as_html"/>
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
                                    v-if="item.thumbnail != undefined && ((item.thumbnail.tainacan_medium != undefined && item.thumbnail.tainacan_medium != false) || (item.thumbnail.medium != undefined && item.thumbnail.medium != false))"
                                    :show-name="false"
                                    :size="178"
                                    :file="{ 
                                        media_type: 'image', 
                                        guid: { rendered: item.thumbnail.tainacan_medium ? item.thumbnail.tainacan_medium : item.thumbnail.medium },
                                        title: { rendered: $i18n.get('label_thumbnail')},
                                        description: { rendered: `<img alt='Thumbnail' src='` + item.thumbnail.full + `'/>` }}"/>
                            <figure 
                                    v-if="item.thumbnail == undefined || ((item.thumbnail.medium == undefined || item.thumbnail.medium == false) && (item.thumbnail.tainacan_medium == undefined || item.thumbnail.tainacan_medium == false))"
                                    class="image">
                                <span class="image-placeholder">{{ $i18n.get('label_empty_thumbnail') }}</span>
                                <img
                                        :alt="$i18n.get('label_thumbnail')"
                                        :src="thumbPlaceholderPath">
                            </figure>
                        </div>
                    </div>

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
                                    :file="attachment"/>
                            <p v-if="attachmentsList.length <= 0"><br>{{ $i18n.get('info_no_attachments_on_item_yet') }}</p>
                        </div>
                    </div>
                    <!-- Comment Status ------------------------ --> 
                    <b-field
                            :addons="false" 
                            :label="$i18n.get('label_comment_status')"
                            v-if="collectionCommentStatus == 'open'">
                        <b-switch
                                id="tainacan-checkbox-comment-status" 
                                size="is-small"
                                true-value="open" 
                                false-value="closed"
                                v-model="item.comment_status"
                                disabled />
                    </b-field>
                    <!-- Exposers --------------------------------------------- -->
                    <div>
                        <b-loading :active.sync="isLoadingMetadatumMappers"/>
                        <div v-if="!isLoadingMetadatumMappers">
                            <b-collapse :open="false">
                                <div    
                                        class="section-label" 
                                        slot="trigger"
                                        slot-scope="session_props">
                                    <label>
                                        {{ $i18n.get('label_exposer_urls') }}
                                        <b-icon
                                                type="is-secondary"
                                                :icon="session_props.open ? 'menu-down' : 'menu-right'"
                                        />
                                    </label>
                                </div>
                                <br>
                                <a
                                        class="collapse-all"
                                        @click="urls_open = !urls_open">
                                    {{ urls_open ? $i18n.get('label_collapse_all') : $i18n.get('label_expand_all') }}
                                    <b-icon
                                            type="is-secondary"
                                            :icon=" urls_open ? 'menu-down' : 'menu-right'"/>
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
                                                <b-icon
                                                        type="is-secondary"
                                                        :icon="props.open ? 'menu-down' : 'menu-right'"
                                                />
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
                    </div>
                </div>
                <div 
                        v-show="!isMetadataColumnCompressed"
                        class="column is-4-5">
                    
                    <!-- Visibility (status public or private) -------------------------------- -->
                    <div class="section-label">
                        <label>{{ $i18n.get('label_visibility') }}</label>
                    </div>
                    <div class="section-status">
                        <div class="field has-addons">
                            <span v-if="item.status != 'private'">
                                <span class="icon">
                                    <i class="mdi mdi-earth"/>
                                </span> {{ $i18n.get('publish_visibility') }}
                            </span>
                            <span v-if="item.status == 'private'">
                                <span class="icon">
                                    <i class="mdi mdi-lock"/>
                                </span>  {{ $i18n.get('private_visibility') }}
                            </span>
                        </div>
                    </div>
                    
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


                    <!-- Metadata -------------------------------- -->
                    <div class="section-label">
                        <label>{{ $i18n.get('metadata') }}</label>
                    </div>
                    <br>
                    <a
                            class="collapse-all"
                            @click="open = !open">
                        {{ open ? $i18n.get('label_collapse_all') : $i18n.get('label_expand_all') }}
                        <b-icon
                                type="is-secondary"
                                :icon=" open ? 'menu-down' : 'menu-right'"/>
                    </a>
                    <div>
                        <div
                                v-for="(metadatum, index) of metadatumList"
                                :key="index"
                                class="field">
                            <b-collapse :open="open">
                                <label
                                        class="label"
                                        slot="trigger"
                                        slot-scope="props">
                                    <b-icon
                                            type="is-secondary"
                                            :icon="props.open ? 'menu-down' : 'menu-right'"
                                    />
                                    {{ metadatum.metadatum.name }}
                                </label>
                                <div
                                        v-if="metadatum.date_i18n"
                                        class="content">
                                    <p v-html="metadatum.date_i18n"/>
                                </div>
                                <div
                                        v-else
                                        class="content">
                                    <p v-html="metadatum.value_as_html"/>
                                </div>
                            </b-collapse>
                        </div>
                    </div>
                </div>
            </div>
            <div class="footer">
                <div class="form-submission-footer">
                    <router-link
                            class="button is-secondary"
                            :to="{ path: $routerHelper.getItemEditPath(collectionId, itemId)}">
                        {{ $i18n.getFrom('items','edit_item') }}
                    </router-link>
                    <a
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
    import {mapActions, mapGetters} from 'vuex'
    import FileItem from '../../components/other/file-item.vue';

    export default {
        name: 'ItemPage',
        data() {
            return {
                collectionId: Number,
                itemId: Number,
                isLoading: false,
                isLoadingMetadatumMappers: false,
                isMetadataColumnCompressed: false,
                open: false,
                collectionName: '',
                thumbPlaceholderPath: tainacan_plugin.base_url + '/admin/images/placeholder_square.png',
                urls_open: false,
                collectionCommentStatus: ''
            }
        },
        components: {
            FileItem
        },
        methods: {
            ...mapActions('item', [
                'fetchItem',
                'fetchAttachments',
                'fetchMetadata',
            ]),
            ...mapActions('collection', [
                'fetchCollectionName',
                'fetchCollectionCommentStatus'
            ]),
            ...mapGetters('item', [
                'getItem',
                'getMetadata',
                'getAttachments'
            ]),
            ...mapGetters('metadata',[
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
                var url = new URL(urlString);
                var mapperParam = url.searchParams.get(tainacan_plugin.exposer_mapper_param);
                if(mapperParam != 'undefined' && mapperParam != null) {
                    var mapper = this.metadatum_mappers.find(obj => {
                        return obj.slug === mapperParam;
                    });
                    if(mapper != 'undefined' && mapper != null) {
                        return this.$i18n.get('label_exposer')+": "+typeSlug+', '+this.$i18n.get('label_mapper')+": "+mapper.name;
                    } else {
                        if(mapperParam == 'value') {
                            return this.$i18n.get('label_exposer')+": "+typeSlug+', '+this.$i18n.get('label_exposer_mapper_values');
                        }
                    }
                }
                return this.$i18n.get('label_exposer')+": "+typeSlug;
            },
            
        },
        computed: {
            item() {
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
            this.fetchItem(this.itemId).then(() => {
                this.loadMetadata();
            });

            // Obtains collection name
            this.fetchCollectionName(this.collectionId).then((collectionName) => {
                this.collectionName = collectionName;
            });

            // Get attachments
            this.fetchAttachments(this.itemId);
            
            // Obtains collection Comment Status
            this.fetchCollectionCommentStatus(this.collectionId).then((collectionCommentStatus) => {
                this.collectionCommentStatus = collectionCommentStatus;
            });
        }

    }
</script>

<style lang="scss" scoped>

    @import '../../scss/_variables.scss';

    #metadata-column-compress-button {
        position: relative;
        z-index: 99;
        float: right;
        top: 70px;
        max-width: 36px;
        height: 36px;
        width: 36px;
        border: none;
        background-color: $gray2;
        color: $secondary;
        padding: 0;
        border-top-left-radius: 2px;
        border-bottom-left-radius: 2px;
        cursor: pointer;

        .icon {
            margin-top: 2px;
            margin-right: 8px;
        }
    }

    .page-container {
        padding: 25px 0;

        .tainacan-page-title {
            padding-left: $page-side-padding;
            padding-right: $page-side-padding;
        }

        .tainacan-form>.columns {
            margin-bottom: 70px;
        }

        .column.is-5-5 {
            width: 45.833333333%;
            padding-left: $page-side-padding;
            padding-right: $page-side-padding;
            transition: width 0.6s;

            @media screen and (max-width: 769px) {
                width: 100%;
            }
        }
        .column.is-4-5 {
            width: 37.5%;
            padding-left: $page-side-padding;
            padding-right: $page-side-padding;
            transition: all 0.6s;

            .field {
                padding: 10px 0 10px 30px;

            }

            @media screen and (max-width: 769px) {
                width: 100%;
            }
        }
            .collapse .collapse-content {
                margin-left: 30px; 
            }
    }

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

    .section-label {
        position: relative;
        label {
            font-size: 16px !important;
            font-weight: 500 !important;
            color: $blue5 !important;
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
    .section-status{
        padding-bottom: 16px;    
        font-size: 0.75rem; 

        .field {
            border-bottom: none;

            .icon  {
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

        p { margin: 4px 15px }
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

