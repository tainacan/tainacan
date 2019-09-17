<template>
    <div class="tainacan-modal-content">
        <header class="tainacan-modal-title">
            <h2>{{ activity.title ? activity.title : $i18n.get('activity') }}</h2>
            <hr>
        </header>
        <b-loading 
                :is-full-page="false"
                :active.sync="isLoadingActivity" 
                :can-cancel="false"/>
        <div
                v-if="!isLoadingActivity"
                class="modal-card-body">
            <div class="content">
                <p><strong>{{ $i18n.get('label_activity_description') }}:</strong> {{ activity.description ? activity.description : $i18n.get('info_no_description_provided') }}</p>
                <p><strong>{{ $i18n.get('label_activity_creation_date') }}:</strong> {{ activityCreationDate }}</p>
                <p><strong>{{ $i18n.get('label_activity_author') }}:</strong> {{ activity.user_name }}</p>
            </div>
            <div>
                <div class="columns">
                    <!-- OLD -->
                    <div class="column is-6">

                        <div
                                v-if="activity.action == 'update-thumbnail'"
                                class="content">
                             <p class="is-capitalized has-text-blue5 has-text-weight-bold">
                                {{ $i18n.get('label_thumbnail') }}
                                <small class="has-text-gray4 has-text-weight-normal"> {{ `(${$i18n.get('info_logs_before')})` }}</small>
                            </p>
                            <p 
                                    v-if="activity.old_value && activity.old_value.thumb && activity.old_value.thumb[0]"
                                    class="tainacan-p-break">
                                <img 
                                        style="margin: 12px 0; max-width: 150px;"
                                        :alt="$i18n.get('label_thumbnail')"
                                        :src="activity.old_value.thumb[0]" >
                            </p>
                            <p v-else>{{ infoEmpty }}</p>
                        </div>

                        <div
                                v-if="activity.action == 'new-attachment'"
                                class="content">
                            <p class="is-capitalized has-text-blue5 has-text-weight-bold">
                                {{ $i18n.get('label_attachment') }}
                                <small class="has-text-gray4 has-text-weight-normal"> {{ `(${$i18n.get('info_logs_before')})` }}</small>
                            </p>
                            <div v-if="activity.old_value.url">
                                <file-item 
                                        :modal-on-click="false"
                                        :show-name="true"
                                        :file="{ 
                                            title: { rendered: activity.old_value.title },
                                            guid: { rendered: activity.old_value.url }, 
                                            mime_type: activity.old_value.mime_type,
                                            media_type: activity.old_value.mime_type.includes('image') ? 'image' : 'other'
                                        }"/>
                            </div>
                            <div v-else>
                                <p>{{ infoEmpty }}</p>
                            </div>
                        </div>

                        <div
                                v-if="activity.action == 'update-document'"
                                class="content">
                            <p class="is-capitalized has-text-blue5 has-text-weight-bold">
                                {{ $i18n.get('label_document') }}
                                <small class="has-text-gray4 has-text-weight-normal"> {{ `(${$i18n.get('info_logs_after')})` }}</small>
                            </p>
                            <p 
                                    v-if="activity.old_value && activity.old_value.document_type == 'attachment'"
                                    v-html="activity.old_value.document ? $i18n.get('label_file') + ': ' + activity.old_value.document : infoEmpty"
                                    class="tainacan-p-break" />
                            <p 
                                    v-else-if="activity.old_value && activity.old_value.document_type == 'url'"
                                    v-html="activity.old_value.document ? (`<a href='` + activity.old_value.document + `'>` + activity.old_value.document + `</a>`) : infoEmpty"
                                    class="tainacan-p-break" />
                            <p 
                                    v-else
                                    v-html="activity.old_value.document ? activity.old_value.document : infoEmpty"
                                    class="tainacan-p-break" />
                        </div>

                        <div
                                class="content"
                                v-if="activity.action == 'update'">
                            <div 
                                    :key="index"
                                    v-for="(attributeValue, attributeName, index) in activity.old_value">
                                <p class="is-capitalized has-text-blue5 has-text-weight-bold">
                                    {{ attributeName }}
                                    <small class="has-text-gray4 has-text-weight-normal"> {{ `(${$i18n.get('info_logs_before')})` }}</small>
                                </p>

                                <!-- Is array with length 1 -->
                                <p
                                        class="tainacan-p-break"
                                        v-if="(attributeValue instanceof Array) &&
                                            (attributeValue.length == 1) &&
                                            !(attributeValue[0] instanceof Object)">
                                    {{ attributeValue.toString() }}
                                </p>

                                <div
                                        v-else-if="attributeName == 'metadata_order' || attributeName == 'filters_order'"
                                        class="content">
                                    <p
                                            class="tainacan-p-break"
                                            v-for="(diffContent, diffTitle) in attributeValue"
                                            :key="diffTitle"
                                            v-html="attributeValue ? `ID: ${diffContent.id}<span class='multivalue-separator'>|</span>${diffContent.enabled}` : infoEmpty " />
                                </div>

                                <div
                                        v-else-if="attributeName == 'metadata_type_options'"
                                        class="content">
                                    <p 
                                            :key="innerIndex"
                                            v-for="(innerValue, innerName, innerIndex) of attributeValue"
                                            class="tainacan-p-break">
                                        <strong>{{ innerName + ': ' }}</strong>{{ innerValue ? innerValue : infoEmpty }}
                                        <br>
                                    </p>
                                </div>

                                <p
                                        class="tainacan-p-break"
                                        v-else
                                        v-html="(!attributeValue || (attributeValue instanceof Array && !attributeValue.length)) ? infoEmpty : (attributeValue instanceof Array ? attributeValue.join(`<span class='multivalue-separator'>|</span>`) : attributeValue)" />
                            </div>
                        </div>

                        <div
                                class="content"
                                v-if="activity.action == 'update-metadata-value'">
                            <p class="is-capitalized has-text-blue5 has-text-weight-bold">
                                {{ activity.metadata && activity.metadata.name ? activity.metadata.name : $i18n.get('metadatum') }}
                                <small class="has-text-gray4 has-text-weight-normal"> {{ `(${$i18n.get('info_logs_before')})` }}</small>
                            </p>
                            <p
                                class="tainacan-p-break"
                                v-html="!activity.old_value ? infoEmpty : (activity.old_value.split(' | ') instanceof Array ? activity.old_value.split(' | ').join(`<span class='multivalue-separator'>|</span>`) : activity.old_value)" />
                        </div>
                    </div>

                    <!-- NEW -->
                    <div class="column is-6">

                        <div
                                v-if="activity.action == 'update-thumbnail'"
                                class="content">
                             <p class="is-capitalized has-text-blue5 has-text-weight-bold">
                                {{ $i18n.get('label_thumbnail') }}
                                <small class="has-text-gray4 has-text-weight-normal"> {{ `(${$i18n.get('info_logs_after')})` }}</small>
                            </p>
                            <p 
                                    v-if="activity.new_value && activity.new_value.thumb && activity.new_value.thumb[0]"
                                    class="tainacan-p-break">
                                <img 
                                        style="margin: 12px 0; max-width: 150px;"
                                        :alt="$i18n.get('label_thumbnail')"
                                        :src="activity.new_value.thumb[0]" >
                            </p>
                            <p v-else>{{ infoEmpty }}</p>
                        </div>

                        <div
                                v-if="activity.action == 'new-attachment'"
                                class="content">
                            <p class="is-capitalized has-text-blue5 has-text-weight-bold">
                                {{ $i18n.get('label_attachment') }}
                                <small class="has-text-gray4 has-text-weight-normal"> {{ `(${$i18n.get('info_logs_after')})` }}</small>
                            </p>
                            <div v-if="activity.new_value.url">
                                <file-item 
                                        :modal-on-click="false"
                                        :show-name="true"
                                        :file="{ 
                                            title: { rendered: activity.new_value.title },
                                            guid: { rendered: activity.new_value.url }, 
                                            mime_type: activity.new_value.mime_type,
                                            media_type: activity.new_value.mime_type.includes('image') ? 'image' : 'other'
                                        }"/>
                            </div>
                            <div v-else>
                                <p>{{ infoEmpty }}</p>
                            </div>
                        </div>

                        <div
                                v-if="activity.action == 'update-document'"
                                class="content">
                            <p class="is-capitalized has-text-blue5 has-text-weight-bold">
                                {{ $i18n.get('label_document') }}
                                <small class="has-text-gray4 has-text-weight-normal"> {{ `(${$i18n.get('info_logs_after')})` }}</small>
                            </p>
                            <p 
                                    v-if="activity.new_value && activity.new_value.document_type == 'attachment'"
                                    v-html="activity.new_value.document ? $i18n.get('label_file') + ': ' + activity.new_value.document : infoEmpty"
                                    class="tainacan-p-break" />
                            <p 
                                    v-else-if="activity.new_value && activity.new_value.document_type == 'url'"
                                    v-html="activity.new_value.document ? (`<a href='` + activity.new_value.document + `'>` + activity.new_value.document + `</a>`) : infoEmpty"
                                    class="tainacan-p-break" />
                            <p 
                                    v-else
                                    v-html="activity.new_value.document ? activity.new_value.document : infoEmpty"
                                    class="tainacan-p-break" />
                        </div>

                        <div 
                                :key="index"
                                v-for="(attributeValue, attributeName, index) in activity.new_value">
                            <div
                                    class="content"
                                    v-if="activity.action == 'update'">
                                <p class="is-capitalized has-text-blue5 has-text-weight-bold">
                                    {{ attributeName }}
                                    <small class="has-text-gray4 has-text-weight-normal"> {{ `(${$i18n.get('info_logs_after')})` }}</small>
                                </p>

                                <div
                                        v-if="attributeName == 'metadata_order' || attributeName == 'filters_order'"
                                        class="content">
                                    <p
                                            class="tainacan-p-break"
                                            v-for="(diffContent, diffTitle) in attributeValue"
                                            :key="diffTitle"
                                            v-html="attributeValue ? `ID: ${diffContent.id}<span class='multivalue-separator'>|</span>${diffContent.enabled}` : infoEmpty"/>
                                </div>

                                <div
                                        v-else-if="attributeName == 'metadata_type_options'"
                                        class="content">
                                    <p 
                                            :key="innerIndex"
                                            v-for="(innerValue, innerName, innerIndex) of attributeValue"
                                            class="tainacan-p-break">
                                        <strong>{{ innerName + ': ' }}</strong>{{ innerValue ? innerValue : infoEmpty }}
                                        <br>
                                    </p>
                                </div>

                                <div
                                        v-else-if="attributeName == 'header_image_id'"
                                        class="content">
                                    <p class="tainacan-p-break">
                                        {{ attributeValue ? attributeValue : infoEmpty }}
                                        <br>
                                        <img 
                                                style="margin: 12px 0; max-width: 160px;"
                                                v-if="activity.terms && activity.terms.header_image"
                                                :alt="$i18n.get('label_header_image')"
                                                :src="activity.terms.header_image" >
                                    </p>
                                </div>

                                <p
                                        class="tainacan-p-break"
                                        v-else
                                        v-html="(!attributeValue || (attributeValue instanceof Array && !attributeValue.length)) ? infoEmpty : (attributeValue instanceof Array ? attributeValue.join(`<span class='multivalue-separator'>|</span>`) : attributeValue)" />
                            </div>
                        </div>
                        
                        <div
                                class="content"
                                v-if="activity.action == 'update-metadata-value'">
                            <p class="is-capitalized has-text-blue5 has-text-weight-bold">
                                {{ activity.metadata && activity.metadata.name ? activity.metadata.name : $i18n.get('metadatum') }}
                                <small class="has-text-gray4 has-text-weight-normal"> {{ `(${$i18n.get('info_logs_after')})` }}</small>
                            </p>
                            <p
                                class="tainacan-p-break"
                                v-html="!activity.new_value ? infoEmpty : (activity.new_value.split(' | ') instanceof Array ? activity.new_value.split(' | ').join(`<span class='multivalue-separator'>|</span>`) : activity.new_value)" />
                        </div>
                    </div>
                </div>
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
    import { mapActions, mapGetters } from 'vuex';
    import moment from 'moment';
    import FileItem from '../file-item.vue';

    export default {
        name: "ActivityDetailsModal",
        props: {
            activityId: String
        },
        data() {
            return {
                infoEmpty: `[ ${this.$i18n.get('info_empty').toLowerCase()} ]`,
                dateFormat: '',
                activityCreationDate: '',
                placeholderSquareImage: `${tainacan_plugin.base_url}/admin/images/placeholder_square.png`,
                isLoadingActivity: false
            }
        },
        components: {
            FileItem
        },
        computed: {
            activity() {
                return this.getActivity();
            }
        },
        created() {
            this.loadActivity();
        },
        methods: {
            ...mapActions('activity', [
                'fetchActivity'
            ]),
            ...mapGetters('activity', [
                'getActivity'
            ]),
            approveActivity(){
                this.$emit('approveActivity', this.activity.id);
            },
            notApproveActivity(){
                this.$emit('notApproveActivity', this.activity.id);
            },
            undo(){

            },
            loadActivity() {
                this.isLoadingActivity = true;
                this.fetchActivity(this.activityId)
                    .then(() => {
                        this.isLoadingActivity = false;

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
                    })
                    .catch(() => this.isLoadingActivity = false);
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
        
        p {
            font-size: 0.875rem;
        }
    }

    .modal-card-body {
        min-height: 400px;
        padding: 0;
        .columns {
            margin: 12px $page-side-padding;
        }
    }

    .tainacan-p-overflow {
        text-overflow: ellipsis;
        white-space: nowrap;
        max-width: 150px;
        overflow: hidden;
    }

    p.is-capitalized {
        margin-bottom: 0.5rem;
    }
    .tainacan-p-break {
        word-break: break-word;
        margin-bottom: 1rem;
    }

    .tainacan-figure {
        width: 150px;
        height: 150px;
        overflow: auto;
    }
</style>