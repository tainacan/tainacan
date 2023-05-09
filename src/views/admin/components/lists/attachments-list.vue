<template>
    <div>
        <div
                style="position: relative;"
                class="table-container">
            <b-loading
                    :is-full-page="false" 
                    :active.sync="isLoading" />
            <div
                    v-if="attachments.length > 0"
                    class="table-wrapper">
                <div class="uploaded-files">
                    <div
                            v-for="(attachment, index) in attachments"
                            :key="index"
                            class="file-item-container"
                            :class="{ 'is-file-document': form.document == attachment.id, 'is-file-thumbnail': item.thumbnail_id == attachment.id }">
                        <span 
                                v-if="form.document == attachment.id"
                                class="file-attachment-document-tag">
                            {{ $i18n.get('label_document') }}
                        </span>
                        <file-item
                                :show-name="true"
                                :modal-on-click="true"
                                :file="attachment"/>
                        <span
                                v-if="isEditable && form.document != attachment.id"
                                class="file-item-control">
                            <a 
                                    @click="onDeleteAttachment(attachment)"
                                    v-tooltip="{
                                        content: $i18n.get('delete'),
                                        autoHide: true,
                                        placement: 'bottom',
                                        popperClass: ['tainacan-tooltip', 'tooltip']
                                    }"
                                    class="icon">
                                <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-delete"/>
                            </a>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Empty state image -->
            <div v-if="(totalAttachments <= 0 || !totalAttachments) && !isLoading">
                <section class="section">
                    <div class="content has-text-grey has-text-centered">
                        <p>
                            <span class="icon">
                                <i class="tainacan-icon tainacan-icon-30px tainacan-icon-attachments"/>
                            </span>
                        </p>
                        <p>{{ $i18n.get('info_no_attachments_on_item_yet') }}</p>
                    </div>
                </section>
            </div>
        </div>

        <div 
                class="pagination-area" 
                v-if="attachments.length > 0">
            <div class="shown-items">
                {{
                    $i18n.get('info_showing_attachments') + ' ' +
                    (attachmentsPerPage * (attachmentsPage - 1) + 1) +
                    $i18n.get('info_to') +
                    getLastAttachmentsNumber() +
                    $i18n.get('info_of') + totalAttachments + '.'
                }}
            </div>
            <div class="pagination">
                <b-pagination
                        @change="onPageChange"
                        :total="totalAttachments"
                        :current.sync="attachmentsPage"
                        order="is-centered"
                        size="is-small"
                        :per-page="attachmentsPerPage"
                        :aria-next-label="$i18n.get('label_next_page')"
                        :aria-previous-label="$i18n.get('label_previous_page')"
                        :aria-page-label="$i18n.get('label_page')"
                        :aria-current-label="$i18n.get('label_current_page')"/>
            </div>
        </div>
    </div>
</template>

<script>
    import { mapActions, mapGetters } from 'vuex';
    import FileItem from '../../components/other/file-item.vue';

    export default {
        name: 'AttachmentsList',
        components: {
            FileItem
        },
        props: {
            item: Object,
            form: Object,
            shouldLoadAttachments: Boolean,
            isEditable: Boolean,
        },
        data() {
            return {
                attachmentsPage: 1,
                attachmentsPerPage: 12,
                isLoading: false
            }
        },
        computed: {
            attachments() {
                return this.getAttachments();
            },
            totalAttachments() {
                return this.getTotalAttachments();
            }
        },
        watch: {
            shouldLoadAttachments() {
                this.loadAttachments();
            }
        },
        created() {
            // Get attachments
            this.loadAttachments();
        },
        methods: {
            ...mapActions('item', [
                'fetchAttachments',
            ]),
            ...mapGetters('item', [
                'getAttachments',
                'getTotalAttachments'
            ]),
            onChangeAttachmentsPerPage(value) {
                
                if (value != this.attachmentsPerPage) {
                    this.$userPrefs.set('attachments_per_page', value)
                        .then((newValue) => {
                            this.attachmentsPerPage = newValue;
                        })
                        .catch(() => {
                            this.$console.log("Error settings user prefs for attachments per page")
                        });
                }
                this.attachmentsPerPage = value;
                this.loadAttachments();
            },
            onPageChange(page) {
                this.attachmentsPage = page;
                this.loadAttachments();
            },
            getLastAttachmentsNumber() {
                let last = (Number(this.attachmentsPerPage * (this.attachmentsPage - 1)) + Number(this.attachmentsPerPage));
                return last > this.totalAttachments ? this.totalAttachments : last;
            },
            loadAttachments() {
                this.isLoading = true;

                this.fetchAttachments({
                    page: this.attachmentsPage,
                    attachmentsPerPage: this.attachmentsPerPage,
                    itemId: this.item.id,
                    // excludeDocumentId: this.form.document,
                    // excludeThumbnailId: this.item.thumbnail_id
                })
                    .then((response) => {
                        this.isLoading = false;
                        this.totalAttachments = response.total;
                    })
                    .catch((error) => {
                        this.isLoading = false;
                        this.$console.error(error);
                    }) 
            },
            onDeleteAttachment(attachment) {
                this.$emit('onDeleteAttachment', attachment);
            }
        }
    }
</script>
        
<style lang="scss" scoped>

    .table-container {
        width: 100%;
    }
    .uploaded-files {
        display: flex;
        flex-wrap: wrap;

        .file-item-container {
            display: inline-block;
            margin: 10px 12px;
            position: relative;

            &:hover .file-item-control {
                display: block;
                visibility: visible;
                opacity: 1;
            }

            .file-attachment-document-tag {
                background-color: var(--tainacan-primary);
                color: var(--tainacan-secondary);
                display: block;
                position: absolute;
                z-index: 9;
                padding: 0.25em 0.5em;
                font-size: 0.6875em;
                border-radius: 3px;
                bottom: 10px;
                left: 4px;
                font-weight: 500;
                border: 1px solid var(--tainacan-secondary);
                opacity: 0.25;
                transition: opacity 0.2s ease;
            }
            &:hover .file-attachment-document-tag {
                opacity: 1.0;
            }

            .file-item-control {
                position: absolute;
                background-color: var(--tainacan-gray1);
                width: 94px;
                margin: 6px 0;
                bottom: 0px;
                padding: 2px 8px 4px 8px;
                text-align: right;
                display: none;
                visibility: hidden;
                opacity: 0;
                transition: opacity ease 0.2s, visibility ease 0.2s, display ease 0.2s;

                .icon {
                    cursor: pointer;
                }
            }
        }
    }

    @media screen and (max-width: 769px) {
        .table-container {
            padding-left: 1em;
            padding-right: 1em;
        }
        .pagination-area {
            margin-left: 0;
            margin-right: 0;
            justify-content: center;
        }
        .uploaded-files {
            justify-content: center;
            
            .file-item-container {
                margin: 5px 7px;
            }
        }
    }
</style>
