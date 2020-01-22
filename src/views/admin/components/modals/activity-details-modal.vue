<template>
    <div 
            autofocus
            role="dialog"
            class="tainacan-modal-content"
            tabindex="-1"
            aria-modal
            ref="activityDetailsModal">
        <header 
                v-if="!isLoadingActivity"
                class="tainacan-modal-title">
            <h2>{{ activity.title ? activity.title : $i18n.get('activity') }}</h2>
            <hr>
            <p>{{ activityCreationDate + ', ' + $i18n.get('info_by_inner') }} <strong> {{ activity.user_name }}</strong></p>
        </header>
        <b-loading 
                :is-full-page="false"
                :active.sync="isLoadingActivity" 
                :can-cancel="false"/>
        <div 
                v-if="!isLoadingActivity"
                class="modal-card-body">
            <div class="content">
                <p v-if="activity.description"><strong>{{ $i18n.get('label_activity_description') }}:</strong> {{ activity.description }}</p>
                <p v-if="activity.object">
                    <strong>{{ $i18n.get('label_related_to') }}: </strong>
                    <span v-html="relatedToLink" />
                </p>
            </div>

            <!-- LEGACY LOG API RETURN -->
            <div v-if="activity.legacy != undefined && activity.legacy == true">
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
                                <p class="is-capitalized has-text-blue5 has-text-weight-bold">
                                    {{ attributeName }}
                                    <small class="has-text-gray4 has-text-weight-normal"> {{ `(${$i18n.get('info_logs_before')})` }}</small>
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
                                <p class="is-capitalized has-text-blue5 has-text-weight-bold">
                                    {{ attributeName }}
                                    <small class="has-text-gray4 has-text-weight-normal"> {{ `(${$i18n.get('info_logs_before')})` }}</small>
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
                                                    title: attachment.title ,
                                                    thumbnails: { 'tainacan-medium': [ attachment.url ] },
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
                                <p class="is-capitalized has-text-blue5 has-text-weight-bold">
                                    {{ attributeName.replace(/_/g, ' ') }}
                                    <small class="has-text-gray4 has-text-weight-normal"> {{ `(${$i18n.get('info_logs_before')})` }}</small>
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
                                <p class="is-capitalized has-text-blue5 has-text-weight-bold">
                                    {{ attributeName }}
                                    <small class="has-text-gray4 has-text-weight-normal"> {{ `(${$i18n.get('info_logs_after')})` }}</small>
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
                                <p class="is-capitalized has-text-blue5 has-text-weight-bold">
                                    {{ attributeName }}
                                    <small class="has-text-gray4 has-text-weight-normal"> {{ `(${$i18n.get('info_logs_after')})` }}</small>
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
                                                    title: attachment.title,
                                                    thumbnails: { 'tainacan-medium': [ attachment.url ] },
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
                                <p class="is-capitalized has-text-blue5 has-text-weight-bold">
                                    {{ attributeName.replace(/_/g, ' ') }}
                                    <small class="has-text-gray4 has-text-weight-normal"> {{ `(${$i18n.get('info_logs_after')})` }}</small>
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

            <!-- NEW LOG API RETURN -->
            <div v-else>
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
                                            title: activity.old_value.title,
                                            thumbnails: { 'tainacan-medium': [ activity.old_value.url ] },
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
                                <small class="has-text-gray4 has-text-weight-normal"> {{ `(${$i18n.get('info_logs_before')})` }}</small>
                            </p>
                            <div
                                    :key="index"
                                    v-for="(attributeValue, attributeName, index) of activity.old_value">
                                <p 
                                        v-if="attributeName == 'thumb' && attributeValue[0]"
                                        class="tainacan-p-break">                                                          
                                    <img 
                                            style="margin: 12px 0; max-width: 150px;"
                                            :alt="$i18n.get('label_document')"
                                            :src="attributeValue[0]" >
                                </p>
                                <p 
                                        v-else
                                        v-html="`<strong>` + attributeName + `: </strong>` + (attributeValue ? attributeValue : infoEmpty)"
                                        class="tainacan-p-break" />
                            </div>
                        </div>

                        <div
                                class="content"
                                v-if="activity.action == 'update-metadata-order' || activity.action == 'update-filters-order'">
                            <div 
                                    :key="index"
                                    v-for="(attributeValue, attributeName, index) in activity.old_value">
                                <p class="is-capitalized has-text-blue5 has-text-weight-bold">
                                    {{ attributeName }}
                                    <small class="has-text-gray4 has-text-weight-normal"> {{ `(${$i18n.get('info_logs_before')})` }}</small>
                                </p>

                                <div class="content">
                                    <p
                                            class="tainacan-p-break"
                                            v-for="(diffContent, diffTitle) in attributeValue"
                                            :key="diffTitle"
                                            v-html="attributeValue ? `ID: ${diffContent.id} <span class='is-italic'>(${diffContent.enabled ? $i18n.get('label_enabled') : $i18n.get('label_disabled')})</span>` : infoEmpty " />
                                </div>
                            </div>
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
                                v-html="!activity.old_value ? infoEmpty : (activity.old_value instanceof Array ? activity.old_value.join(`<span class='multivalue-separator'>|</span>`) : activity.old_value)" />
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
                                            title: activity.new_value.title,
                                            thumbnails: { 'tainacan-medium': [ activity.new_value.url ] },
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
                            <div
                                    :key="index"
                                    v-for="(attributeValue, attributeName, index) of activity.new_value">
                                <p 
                                        v-if="attributeName == 'thumb' && attributeValue[0]"
                                        class="tainacan-p-break">                                                          
                                    <img 
                                            style="margin: 12px 0; max-width: 150px;"
                                            :alt="$i18n.get('label_document')"
                                            :src="attributeValue[0]" >
                                </p>
                                <p 
                                        v-else
                                        v-html="`<strong>` + attributeName + `: </strong>` + (attributeValue ? attributeValue : infoEmpty)"
                                        class="tainacan-p-break" />
                            </div>
                        </div>

                        <div
                                class="content"
                                v-if="activity.action == 'update-metadata-order' || activity.action == 'update-filters-order'">
                            <div 
                                    :key="index"
                                    v-for="(attributeValue, attributeName, index) in activity.new_value">
                                <p class="is-capitalized has-text-blue5 has-text-weight-bold">
                                    {{ attributeName }}
                                    <small class="has-text-gray4 has-text-weight-normal"> {{ `(${$i18n.get('info_logs_after')})` }}</small>
                                </p>

                                <div class="content">
                                    <p
                                            class="tainacan-p-break"
                                            v-for="(diffContent, diffTitle) in attributeValue"
                                            :key="diffTitle"
                                            v-html="attributeValue ? `ID: ${diffContent.id} <span class='is-italic'>(${diffContent.enabled ? $i18n.get('label_enabled') : $i18n.get('label_disabled')})</span>` : infoEmpty " />
                                </div>
                            </div>
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
                                        v-if="attributeName == 'metadata_type_options'"
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
                                v-html="!activity.new_value ? infoEmpty : (activity.new_value instanceof Array ? activity.new_value.join(`<span class='multivalue-separator'>|</span>`) : activity.new_value)" />
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
    import FileItem from '../other/file-item.vue';

    export default {
        name: "ActivityDetailsModal",
        components: {
            FileItem
        },
        props: {
            activityId: String
        },
        data() {
            return {
                infoEmpty: `[ ${this.$i18n.get('info_empty').toLowerCase()} ]`,
                dateFormat: '',
                activityCreationDate: '',
                placeholderSquareImage: `${tainacan_plugin.base_url}/assets/images/placeholder_square.png`,
                isLoadingActivity: false,
                adminFullURL: tainacan_plugin.admin_url + 'admin.php?page=tainacan_admin#', 
            }
        },
        computed: {
            activity() {
                return this.getActivity();
            },
            relatedToLink() {
                switch(this.activity.object_type) {
                    case 'Tainacan\\Entities\\Collection':
                        return `${ this.$i18n.get('collection') } 
                                <a href="${ this.adminFullURL + this.$routerHelper.getCollectionPath(this.activity.object_id) }">${ this.activity.object.name }</a>
                                <span class="icon has-text-gray3">&nbsp;<i class="tainacan-icon tainacan-icon-20px tainacan-icon-collections"/></span>`;
                    case 'Tainacan\\Entities\\Taxonomy':
                        return `${ this.$i18n.get('taxonomy') } 
                                <a href="${ this.adminFullURL + this.$routerHelper.getTaxonomyPath(this.activity.object_id) }">${ this.activity.object.name }</a>
                                <span class="icon has-text-gray3">&nbsp;<i class="tainacan-icon tainacan-icon-20px tainacan-icon-taxonomies"/></span>`;
                    case 'Tainacan\\Entities\\Metadatum':
                        return `${ this.$i18n.get('metadatum') } 
                                <a href="${ this.adminFullURL + (this.activity.object.collection_id == 'default' ? this.$routerHelper.getMetadataEditPath(this.activity.object_id) : this.$routerHelper.getCollectionMetadataEditPath(this.activity.object.collection_id, this.activity.object_id)) }">${ this.activity.object.name }</a>
                                <span class="icon has-text-gray3">&nbsp;<i class="tainacan-icon tainacan-icon-20px tainacan-icon-metadata"/></span>`;
                    case 'Tainacan\\Entities\\Filter':
                        return `${ this.$i18n.get('filter') } 
                                <a href="${ this.adminFullURL + (this.activity.object.collection_id == 'default' ? this.$routerHelper.getFilterEditPath(this.activity.object_id) : this.$routerHelper.getCollectionFilterEditPath(this.activity.object.collection_id, this.activity.object_id)) }">${ this.activity.object.name }</a>
                                <span class="icon has-text-gray3">&nbsp;<i class="tainacan-icon tainacan-icon-20px tainacan-icon-filters"/></span>`;
                    case 'Tainacan\\Entities\\Term':
                        return `${ this.$i18n.get('term') } 
                                <a href="${ this.adminFullURL + this.$routerHelper.getTermEditPath(this.activity.object.taxonomy.replace( /^\D+/g, ''), this.activity.object_id) }">${ this.activity.object.name }</a>
                                <span class="icon has-text-gray3">&nbsp;<i class="tainacan-icon tainacan-icon-20px tainacan-icon-terms"/></span>`;
                    case 'Tainacan\\Entities\\Item':
                        return `${ this.$i18n.get('item') } 
                                <a href="${ this.adminFullURL + this.$routerHelper.getItemEditPath(this.activity.object.collection_id, this.activity.object_id) }">${ this.activity.object.title }</a>
                                <span class="icon has-text-gray3">&nbsp;<i class="tainacan-icon tainacan-icon-20px tainacan-icon-items"/></span>`;
                    case 'Tainacan\\Entities\\Item_Metadata_Entity':
                        return `${ this.$i18n.get('item') } 
                                <a href="${ this.adminFullURL + this.$routerHelper.getItemEditPath(this.activity.object.collection_id, this.activity.item.id) }">${ this.activity.item.title }</a>
                                <span class="icon has-text-gray3">&nbsp;<i class="tainacan-icon tainacan-icon-20px tainacan-icon-items"/></span>`;
                }
            }
        },
        watch: {
            '$route' (to, from) {
                if (to !== from)
                    this.$parent.close();
            }
        },
        created() {
            this.loadActivity();
        },
        mounted() {
            if (this.$refs.activityDetailsModal)
                this.$refs.activityDetailsModal.focus()
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

        p {
            margin-right: auto;
        }
    }

    .tainacan-modal-content {
        width: auto;
        min-height: 500px;
        
        p {
            font-size: 0.875rem;
        }
    }

    .modal-card-body {
        min-height: 300px;
        padding: 0;
        .columns {
            margin: 6px $page-side-padding 0 $page-side-padding;
        }
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

    p.is-capitalized {
        margin-bottom: 0.125rem;
    }
    .tainacan-p-break {
        word-break: break-word;
        margin-bottom: 0.5rem;
    }

    .tainacan-figure {
        width: 150px;
        height: 150px;
        overflow: auto;
    }
</style>