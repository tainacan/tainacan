<template>
    <div>
        <div
                style="position: relative;"
                class="table-container">
            <b-loading
                    is-full-page="false" 
                    :active.sync="isLoading" />
            <div
                    v-if="attachments.length > 0"
                    class="table-wrapper">
                <div class="uploaded-files">
                    <div
                            v-for="(attachment, index) in attachments"
                            :key="index"
                            class="file-item-container">
                        <file-item
                                :show-name="true"
                                :modal-on-click="true"
                                :file="attachment"/>
                        <span
                                v-if="isEditable"
                                class="file-item-control">
                            <a 
                                    @click="onDeleteAttachment(attachment)"
                                    v-tooltip="{
                                        content: $i18n.get('delete'),
                                        autoHide: true,
                                        placement: 'bottom'
                                    }"
                                    class="icon">
                                <i class="tainacan-icon tainacan-icon-20px tainacan-icon-delete"/>
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
                    $i18n.get('info_showing_attachments') +
                    (attachmentsPerPage * (attachmentsPage - 1) + 1) +
                    $i18n.get('info_to') +
                    getLastAttachmentsNumber() +
                    $i18n.get('info_of') + totalAttachments + '.'
                }}
            </div>
            <!-- <div class="items-per-page">
                <b-field 
                        horizontal 
                        :label="$i18n.get('label_attachments_per_page')">
                    <b-select
                            :value="attachmentsPerPage"
                            @input="onChangeAttachmentsPerPage"
                            :disabled="attachments.length <= 0">
                        <option value="12">12</option>
                        <option value="24">24</option>
                        <option value="48">48</option>
                        <option value="96">96</option>
                    </b-select>
                </b-field>
            </div> -->
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
            isLoading: Boolean,
            isEditable: Boolean,
        },
        data() {
            return {
                attachmentsPage: 1,
                attachmentsPerPage: 24
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
                this.$emit('isLoadingAttachments', true);

                this.fetchAttachments({
                    page: this.attachmentsPage,
                    attachmentsPerPage: this.attachmentsPerPage,
                    itemId: this.item.id,
                    documentId: this.item.document
                })
                    .then((response) => {
                        this.isLoading = false;
                        this.$emit('isLoadingAttachments', false);
                        this.totalAttachments = response.total;
                    })
                    .catch((error) => {
                        this.isLoading = false;
                        this.$emit('isLoadingAttachments', false);
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

    .uploaded-files {
        display: block;
        text-align: center;

        .file-item-container {
            display: inline-block;
            margin: 15px;
            position: relative;

            &:hover .file-item-control {
                display: block;
                visibility: visible;
                opacity: 1;
            }

            .file-item-control {
                position: absolute;
                background-color: #f2f2f2;
                width: 112px;
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
</style>
