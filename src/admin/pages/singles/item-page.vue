<template>
    <div>
        <b-loading
                :active.sync="isLoading"
                :can-cancel="false"/>

        <tainacan-title/>

        <div class="columns">
            <div class="column is-5">
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

                    <!-- Attachments ----------------------------- -->
                    <div class="section-label">
                        <label>{{ $i18n.get('label_attachments') }}</label>
                    </div>
                    <div class="section-box">
                        <div class="uploaded-files">
                            <div
                                    v-for="(attachment, index) in attachmentsList"
                                    :key="index">
                                <span class="tag is-primary">
                                    {{ attachment.title.rendered }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
            <div class="column is-1" />
            <div class="column is-6">
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

    export default {
        name: 'ItemPage',
        data() {
            return {
                collectionId: Number,
                itemId: Number,
                isLoading: false,
                open: false,
            }
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

    .page-container {
        height: calc(100% - 82px);
    }

    .columns > .column {
        padding: 0;
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
</style>

