<template>
    <div class="tainacan-modal-content">
        <header class="tainacan-modal-title">
            <h2>{{ $i18n.get('activity') }}</h2>
            <hr>
        </header>
        <div class="modal-card-body">
            <div class="content">
                <p><strong>{{ $i18n.get('label_activity_description') }}:</strong> {{ activity.description }}</p>
                <p><strong>{{ $i18n.get('label_activity_creation_date') }}:</strong> {{ activityCreationDate }}</p>
                <p><strong>{{ $i18n.get('label_activity_author') }}:</strong> {{ activity.user_name }}</p>
            </div>
            <div>
                <template v-for="(diff, attributeName, index) in activity.log_diffs">
                    <div 
                            :key="index"
                            class="columns">

                        <!-- OLD -->
                        <div class="column is-6">

                            <!-- Thumbnail -->
                            <div
                                    class="content"
                                    v-if="attributeName == 'thumbnail'">
                                <p class="is-capitalized has-text-blue5">
                                    {{ attributeName }}
                                    <small class="has-text-gray4"> {{ `(${$i18n.get('info_logs_before')})` }}</small>
                                </p>
                                <div>
                                    <picture>
                                        <img
                                                width="150px"
                                                :src="diff.old ? diff.old : placeholderSquareImage"
                                                :alt="attributeName">
                                    </picture>
                                </div>
                            </div>

                            <div
                                    v-if="attributeName == 'attachments'"
                                    class="content">
                                <p class="is-capitalized has-text-blue5">
                                    {{ attributeName }}
                                    <small class="has-text-gray4"> {{ `(${$i18n.get('info_logs_before')})` }}</small>
                                </p>
                                <div
                                        class="tainacan-attachments-in-modal"
                                        v-if="diff.old.length">
                                    <template v-for="(attachment, anotherIndex) in diff.old">
                                        <file-item 
                                                :key="anotherIndex"
                                                :modal-on-click="false"
                                                :show-name="true"
                                                :file="{ 
                                                    title: { rendered: attachment.title },
                                                    guid: { rendered: attachment.url }, 
                                                    mime_type: attachment.mime_type,
                                                    media_type: attachment.mime_type.includes('image') ? 'image' : 'other'
                                                }"/>
                                    </template>
                                </div>
                                <div v-else>
                                    <p>{{ infoEmpty }}</p>
                                </div>
                            </div>

                            <div
                                    class="content"
                                    v-if="!['thumbnail', 'attachments'].includes(attributeName)">
                                <p class="is-capitalized has-text-blue5">
                                    {{ attributeName.replace(/_/g, ' ') }}
                                    <small class="has-text-gray4"> {{ `(${$i18n.get('info_logs_before')})` }}</small>
                                </p>

                                <!-- Is array with length 1 -->
                                <p
                                        class="tainacan-p-break"
                                        v-if="(diff.old instanceof Array) &&
                                         (diff.old.length == 1) &&
                                          !(diff.old[0] instanceof Object)">
                                    {{ diff.old.toString() }}
                                </p>

                                <div
                                        v-else-if="attributeName == 'metadata_order'"
                                        class="content">
                                    <p
                                            class="tainacan-p-break"
                                            v-for="(diffContent, diffTitle) in diff.old"
                                            :key="diffTitle">
                                        {{ diff.old ? `ID: ${diffContent.id} | Enabled: ${diffContent.enabled}` : infoEmpty }}
                                    </p>
                                </div>

                                <div
                                        v-else-if="attributeName == 'filters_order'"
                                        class="content">
                                    <p
                                            class="tainacan-p-break"
                                            v-for="(diffContent, diffTitle) in diff.old"
                                            :key="diffTitle">
                                        {{ diff.old ? `ID: ${diffContent.id} | Enabled: ${diffContent.enabled}` : infoEmpty }}
                                    </p>
                                </div>

                                <div
                                        v-else-if="attributeName == 'metadata_type_options'"
                                        class="content">
                                    <p class="tainacan-p-break">
                                        {{ diff.old ?
                                        `Taxonomy ID: ${diff.old.taxonomy_id};
                                        Input type: ${diff.old.input_type};
                                        Allow new terms: ${diff.old.allow_new_terms}` : infoEmpty }}
                                    </p>
                                </div>

                                <!--  -->
                                <p
                                        class="tainacan-p-break"
                                        v-else>
                                    {{ diff.old ? (diff.old instanceof Array && !diff.old.length) ? infoEmpty : diff.old.toString().replace(/,/g, ' ') : infoEmpty }}
                                </p>

                            </div>
                        </div>

                        <!-- NEW -->
                        <div class="column is-6">

                            <!-- Thumbnail -->
                            <div
                                    class="content"
                                    v-if="attributeName == 'thumbnail'">
                                <p class="is-capitalized has-text-blue5">
                                    {{ attributeName }}
                                    <small class="has-text-gray4"> {{ `(${$i18n.get('info_logs_after')})` }}</small>
                                </p>
                                <div>
                                    <picture>
                                        <img
                                                width="150px"
                                                :src="diff.new ? diff.new : placeholderSquareImage"
                                                :alt="attributeName">
                                    </picture>
                                </div>
                            </div>

                            <div
                                    v-if="attributeName == 'attachments'"
                                    class="content">
                                <p class="is-capitalized has-text-blue5">
                                    {{ attributeName }}
                                    <small class="has-text-gray4"> {{ `(${$i18n.get('info_logs_after')})` }}</small>
                                </p>
                                <div
                                        class="tainacan-attachments-in-modal"
                                        v-if="diff.new.length">
                                    <template v-for="(attachment, index) in diff.new">
                                        <file-item 
                                                :key="index"
                                                :modal-on-click="false"
                                                :show-name="true"
                                                :file="{ 
                                                    title: { rendered: attachment.title },
                                                    guid: { rendered: attachment.url }, 
                                                    mime_type: attachment.mime_type,
                                                    media_type: attachment.mime_type.includes('image') ? 'image' : 'other'
                                                }"/>
                                    </template>
                                </div>
                                <div v-else>
                                    <p>{{ infoEmpty }}</p>
                                </div>
                            </div>

                            <div
                                    class="content"
                                    v-if="!['thumbnail', 'attachments'].includes(attributeName)">
                                <p class="is-capitalized has-text-blue5">
                                    {{ attributeName.replace(/_/g, ' ') }}
                                    <small class="has-text-gray4"> {{ `(${$i18n.get('info_logs_after')})` }}</small>
                                </p>

                                <!-- Is array with length 1 -->
                                <p
                                        class="tainacan-p-break"
                                        v-if="(diff.new instanceof Array) &&
                                         (diff.new.length == 1) &&
                                          !(diff.new[0] instanceof Object)">
                                    {{ diff.new.toString() }}
                                </p>


                                <div
                                        v-else-if="attributeName == 'metadata_order'"
                                        class="content">
                                    <p
                                            class="tainacan-p-break"
                                            v-for="(diffContent, diffTitle) in diff.new"
                                            :key="diffTitle">
                                        {{ `ID: ${diffContent.id} | Enabled: ${diffContent.enabled}` }}
                                    </p>
                                </div>

                                <div
                                        v-else-if="attributeName == 'filters_order'"
                                        class="content">
                                    <p
                                            class="tainacan-p-break"
                                            v-for="(diffContent, diffTitle) in diff.new"
                                            :key="diffTitle">
                                        {{ `ID: ${diffContent.id} | Enabled: ${diffContent.enabled}` }}
                                    </p>
                                </div>

                                <div
                                        v-else-if="attributeName == 'metadata_type_options'"
                                        class="content">
                                    <p class="tainacan-p-break">
                                        {{ `Taxonomy ID: ${diff.new.taxonomy_id};
                                            Input type: ${diff.new.input_type};
                                            Allow new terms: ${diff.new.allow_new_terms}` }}
                                    </p>
                                </div>

                                <!-- -->
                                <p
                                        class="tainacan-p-break"
                                        v-else>
                                    {{ diff.new ? (diff.new instanceof Array && !diff.new.length) ? infoEmpty : diff.new.toString().replace(/,/g, ' ') : infoEmpty }}
                                </p>

                            </div>
                        </div>
                    </div>
                </template>

            </div>
        </div>
        <footer>
            <div class="control">
                <button
                        class="button is-outlined"
                        type="button"
                        @click="$parent.close()">
                    {{ $i18n.get('close') }}
                </button>
                <div class="buttons is-pulled-right">
                    <!--<button-->
                            <!--v-if="activity.status != 'publish'"-->
                            <!--@click="notApproveActivity"-->
                            <!--type="button"-->
                            <!--class="button is-danger">-->
                        <!--<b-icon-->
                                <!--size="is-small"-->
                                <!--icon="close"/>-->
                        <!--<span>{{ $i18n.get('not_approve_item') }}</span>-->
                    <!--</button>-->
                    <!--<button-->
                            <!--v-if="activity.status != 'publish'"-->
                            <!--@click="approveActivity"-->
                            <!--type="button"-->
                            <!--class="button is-secondary">-->
                        <!--<b-icon-->
                                <!--size="is-small"-->
                                <!--icon="check"/>-->
                        <!--<span>{{ $i18n.get('approve_item') }}</span>-->
                    <!--</button>-->

                    <!--<button-->
                            <!--v-if="activity.status == 'publish'"-->
                            <!--@click="notApproveActivity"-->
                            <!--type="button"-->
                            <!--class="button is-blue5">-->
                        <!--<b-icon-->
                                <!--custom-class="mdi-flip-h"-->
                                <!--size="is-small"-->
                                <!--icon="share"/>-->
                        <!--<span>{{ $i18n.get('undo') }}</span>-->
                    <!--</button>-->

                    <!-- <button
                            v-if="activity.status == 'publish'"
                            @click="$parent.close()"
                            type="button"
                            class="button is-secondary">
                        <span>OK</span>
                    </button> -->
                </div>
            </div>
        </footer>
    </div>
</template>

<script>
    import moment from 'moment';
    import FileItem from '../file-item.vue';

    export default {
        name: "ActivityDetailsModal",
        props: {
            activity: Object
        },
        data() {
            return {
                infoEmpty: `[${this.$i18n.get('info_empty').toLowerCase()}]`,
                dateFormat: '',
                activityCreationDate: '',
                placeholderSquareImage: `${tainacan_plugin.base_url}/admin/images/placeholder_square.png`,
            }
        },
        components: {
            FileItem
        },
        created() {

            let locale = navigator.language;

            moment.locale(locale);

            let localeData = moment.localeData();
            this.dateFormat = localeData.longDateFormat('lll');

            let logDate = this.activity.log_date;

            let date = moment(logDate).format(this.dateFormat);

            if (date != 'Invalid date') {
                this.activityCreationDate = date;
            } else {
                this.activityCreationDate = this.$i18n.get('info_unknown_date');
            }
        },
        methods: {
            approveActivity(){
                this.$emit('approveActivity', this.activity.id);
            },
            notApproveActivity(){
                this.$emit('notApproveActivity', this.activity.id);
            },
            undo(){

            }
        }
    }
</script>

<style lang="scss" scoped>

    @import "../../scss/_variables.scss";

    .tainacan-modal-title {
        align-self: baseline;
        display: flex;
        flex-direction: column;
        width: 100%;
    }

    .tainacan-modal-content {
        width: auto;
        min-height: 600px;
    }

    .modal-card-body {
        min-height: 400px;
    }

    .tainacan-attachments-in-modal {
        display: flex;
        flex-wrap: wrap;
        flex-direction: row;
        align-content: baseline;
        resize: vertical;
        overflow-y: auto;
        overflow-x: hidden;
        height: 200px;
        border: 1px solid $gray3;

        &>div {
            margin: 0.5rem;
        }
    }

    .tainacan-p-overflow {
        text-overflow: ellipsis;
        white-space: nowrap;
        max-width: 150px;
        overflow: hidden;
    }

    .tainacan-p-break {
        word-break: break-word;
    }

    .tainacan-figure {
        width: 150px;
        height: 150px;
        overflow: auto;
    }
</style>