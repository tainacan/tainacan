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
        <div class="columns">
            <div class="column is-5-5">
                <div class="column is-12">
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

                    <br>
                    <br>

                    <!-- Status -------------------------------- -->
                    <div class="section-label">
                        <label>{{ $i18n.get('label_status') }}</label>
                    </div>
                    <div>
                        <p>{{ item.status }}</p>
                    </div>
                    <br>
                </div>

                <div class="column is-12">

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
                    </div>
                </div>

                <div class="column is-12">

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

                </div>

            </div>
            <div 
                    v-show="!isMetadataColumnCompressed"
                    class="column is-4-5">
                <label class="section-label">{{ $i18n.get('fields') }}</label>
                <br>
                <a
                        class="collapse-all"
                        @click="open = !open">
                    {{ open ? $i18n.get('label_collapse_all') : $i18n.get('label_expand_all') }}
                    <b-icon
                            type="is-secondary"
                            :icon=" open ? 'menu-down' : 'menu-right'"/>
                </a>

                <!-- Fields -------------------------------- -->
                <div>
                    <div
                            v-for="(field, index) of fieldList"
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
                                {{ field.field.name }}
                            </label>
                            <div
                                    v-if="field.date_i18n"
                                    class="content">
                                <p v-html="field.date_i18n"/>
                            </div>
                            <div
                                    v-else
                                    class="content">
                                <p v-html="field.value_as_html"/>
                            </div>
                        </b-collapse>
                    </div>
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
                isMetadataColumnCompressed: false,
                open: false,
            }
        },
        components: {
            FileItem
        },
        methods: {
            ...mapActions('item', [
                'fetchItem',
                'fetchAttachments',
                'fetchFields',
            ]),
            ...mapGetters('item', [
                'getItem',
                'getFields',
                'getAttachments'
            ]),
            loadMetadata() {
                // Obtains Item Field
                this.fetchFields(this.itemId).then(() => {
                    this.isLoading = false;
                });
            },
        },
        computed: {
            item() {
                return this.getItem();
            },
            fieldList() {
                return JSON.parse(JSON.stringify(this.getFields()));
            },
            attachmentsList() {
                return this.getAttachments();
            }
        },
        created() {
            // Obtains item and collection ID
            this.collectionId = this.$route.params.collectionId;
            this.itemId = this.$route.params.itemId;

            // Puts loading on Item Loading
            this.isLoading = true;

            // Obtains Item
            this.fetchItem(this.itemId).then(() => {
                this.loadMetadata();
            });

            // Get attachments
            this.fetchAttachments(this.itemId);
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
        background-color: $tainacan-input-background;
        color: $secondary;
        padding: 0px;
        border-top-left-radius: 2px;
        border-bottom-left-radius: 2px;
        cursor: pointer;

        .icon {
            margin-top: 2px;
            margin-right: 8px;
        }
    }

    .page-container {
        padding: 25px 0px;

        .tainacan-page-title {
            padding-left: $page-side-padding;
            padding-right: $page-side-padding;
        }

        .column {
            padding-top: 0px;
            padding-bottom: 0px;
        }
        .column.is-5-5 {
            width: 45.833333333%;
            padding-left: $page-side-padding;
            padding-right: $page-side-padding;
            transition: width 0.6s;
        }
        .column.is-4-5 {
            width: 37.5%;
            padding-left: $page-side-padding;
            padding-right: $page-side-padding;
            transition: all 0.6s;
        }

    }

    .field {
        border-bottom: 1px solid $draggable-border-color;
        padding: 10px 25px;

        .label {
            font-size: 14px;
            font-weight: 500;
            margin-bottom: 0.5em;

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
            color: $tertiary !important;
            line-height: 1.2em;
        }
    }

    .collapse-all {
        font-size: 12px;
        .icon {
            vertical-align: bottom;
        }
    }

    .section-box {
        border: 1px solid $draggable-border-color;
        padding: 30px;
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
                    background-color: $tainacan-input-background;
                    color: $secondary;
                    margin-bottom: 6px;
                    &:hover {
                        background-color: $primary-light;
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
        width: 174px;        
    }
    .section-thumbnail {
        width: 174px;
        padding-top: 0;
        padding-bottom: 0;
    }
    .section-attachments {
        height: 250px;
        max-width: 100%;
        resize: vertical;
        overflow: auto;
        padding: 15px;

        p { margin: 4px 15px }
    }

    .uploaded-files {
        display: flex;
        flex-flow: wrap;
        margin-left: -15px;
        margin-right: -15px;
    }
</style>

