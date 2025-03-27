<template>
    <div 
            ref="activityDetailsModal"
            autofocus
            role="dialog"
            class="tainacan-modal-content"
            :class="{ 'tainacan-repository-level-colors': isRepositoryLevel }"
            tabindex="-1"
            aria-modal>
        <header 
                v-if="!isLoadingActivity"
                class="tainacan-modal-title">
            <h2>{{ activity.title ? activity.title : $i18n.get('activity') }}</h2>
            <button 
                    class="button is-medium is-white is-align-self-flex-start"
                    :aria-label="$i18n.get('close')"
                    @click="$emit('close')">
                <span class="icon">
                    <i class="tainacan-icon tainacan-icon-close tainacan-icon-1-125em" />
                </span>
            </button>
            <p>{{ activityCreationDate + ', ' + $i18n.get('info_by_inner') }} <strong> {{ activity.user_name }}</strong></p>
        </header>
        <b-loading 
                v-model="isLoadingActivity"
                :is-full-page="false" 
                :can-cancel="false" />
        <div 
                v-if="!isLoadingActivity"
                class="modal-card-body">
            <div class="content">
                <p v-if="activity.description">
                    <strong>{{ $i18n.get('label_activity_description') }}:</strong> {{ activity.description }}
                </p>
                <p v-if="activity.object">
                    <strong>{{ $i18n.get('label_related_to') }}: </strong>
                    <span v-html="relatedToLink" />
                </p>
            </div>

            <!-- LEGACY LOG API RETURN -->
            <div v-if="activity.legacy != undefined && activity.legacy == true">
                <div 
                        v-for="(diff, attributeName, index) in activity.log_diffs"
                        :key="index"
                        class="columns">
                    <!-- OLD -->
                    <div class="column is-6">

                        <!-- Thumbnail -->
                        <div
                                v-if="attributeName == 'thumbnail'"
                                class="content">
                            <p class="is-capitalized has-text-blue5 has-text-weight-bold">
                                {{ attributeName }}
                                <small class="has-text-gray4 has-text-weight-normal"> {{ `(${$i18n.get('info_logs_before')})` }}</small>
                            </p>
                            <div>
                                <picture>
                                    <img
                                            width="150px"
                                            :src="diff.old ? diff.old : $thumbHelper.getEmptyThumbnailPlaceholder()"
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
                                    v-if="diff.old.length"
                                    class="tainacan-attachments-in-modal">
                                <file-item
                                        v-for="(attachment, anotherIndex) in diff.old" 
                                        :key="anotherIndex"
                                        :modal-on-click="false"
                                        :show-name="true"
                                        :file="{ 
                                            title: attachment.title ,
                                            thumbnails: { 'tainacan-medium': [ attachment.url ] },
                                            mime_type: attachment.mime_type,
                                            media_type: attachment.mime_type.includes('image') ? 'image' : 'other'
                                        }" />
                            </div>
                            <div v-else>
                                <p>{{ infoEmpty }}</p>
                            </div>
                        </div>

                        <div
                                v-if="!['thumbnail', 'attachments'].includes(attributeName)"
                                class="content">
                            <p class="is-capitalized has-text-blue5 has-text-weight-bold">
                                {{ attributeName.replace(/_/g, ' ') }}
                                <small class="has-text-gray4 has-text-weight-normal"> {{ `(${$i18n.get('info_logs_before')})` }}</small>
                            </p>

                            <!-- Is array with length 1 -->
                            <p
                                    v-if="(diff.old instanceof Array) &&
                                        (diff.old.length == 1) &&
                                        !(diff.old[0] instanceof Object)"
                                    class="tainacan-p-break">
                                {{ diff.old.toString() }}
                            </p>

                            <div
                                    v-else-if="attributeName == 'metadata_order'"
                                    class="content">
                                <p
                                        v-for="(diffContent, diffTitle) in diff.old"
                                        :key="diffTitle"
                                        class="tainacan-p-break">
                                    {{ diff.old ? `ID: ${diffContent.id} | Enabled: ${diffContent.enabled}` : infoEmpty }}
                                </p>
                            </div>

                            <div
                                    v-else-if="attributeName == 'filters_order'"
                                    class="content">
                                <p
                                        v-for="(diffContent, diffTitle) in diff.old"
                                        :key="diffTitle"
                                        class="tainacan-p-break">
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
                                    v-else
                                    class="tainacan-p-break">
                                {{ diff.old ? (diff.old instanceof Array && !diff.old.length) ? infoEmpty : diff.old.toString().replace(/,/g, ' ') : infoEmpty }}
                            </p>

                        </div>
                    </div>

                    <!-- NEW -->
                    <div class="column is-6">

                        <!-- Thumbnail -->
                        <div
                                v-if="attributeName == 'thumbnail'"
                                class="content">
                            <p class="is-capitalized has-text-blue5 has-text-weight-bold">
                                {{ attributeName }}
                                <small class="has-text-gray4 has-text-weight-normal"> {{ `(${$i18n.get('info_logs_after')})` }}</small>
                            </p>
                            <div>
                                <picture>
                                    <img
                                            width="150px"
                                            :src="diff.new ? diff.new : $thumbHelper.getEmptyThumbnailPlaceholder()"
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
                                    v-if="diff.new.length"
                                    class="tainacan-attachments-in-modal">
                                <file-item
                                        v-for="(attachment, attachmentIndex) in diff.new" 
                                        :key="attachmentIndex"
                                        :modal-on-click="false"
                                        :show-name="true"
                                        :file="{ 
                                            title: attachment.title,
                                            thumbnails: { 'tainacan-medium': [ attachment.url ] },
                                            mime_type: attachment.mime_type,
                                            media_type: attachment.mime_type.includes('image') ? 'image' : 'other'
                                        }" />
                            </div>
                            <div v-else>
                                <p>{{ infoEmpty }}</p>
                            </div>
                        </div>

                        <div
                                v-if="!['thumbnail', 'attachments'].includes(attributeName)"
                                class="content">
                            <p class="is-capitalized has-text-blue5 has-text-weight-bold">
                                {{ attributeName.replace(/_/g, ' ') }}
                                <small class="has-text-gray4 has-text-weight-normal"> {{ `(${$i18n.get('info_logs_after')})` }}</small>
                            </p>

                            <!-- Is array with length 1 -->
                            <p
                                    v-if="(diff.new instanceof Array) &&
                                        (diff.new.length == 1) &&
                                        !(diff.new[0] instanceof Object)"
                                    class="tainacan-p-break">
                                {{ diff.new.toString() }}
                            </p>


                            <div
                                    v-else-if="attributeName == 'metadata_order'"
                                    class="content">
                                <p
                                        v-for="(diffContent, diffTitle) in diff.new"
                                        :key="diffTitle"
                                        class="tainacan-p-break">
                                    {{ `ID: ${diffContent.id} | Enabled: ${diffContent.enabled}` }}
                                </p>
                            </div>

                            <div
                                    v-else-if="attributeName == 'filters_order'"
                                    class="content">
                                <p
                                        v-for="(diffContent, diffTitle) in diff.new"
                                        :key="diffTitle"
                                        class="tainacan-p-break">
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
                                    v-else
                                    class="tainacan-p-break">
                                {{ diff.new ? (diff.new instanceof Array && !diff.new.length) ? infoEmpty : diff.new.toString().replace(/,/g, ' ') : infoEmpty }}
                            </p>

                        </div>
                    </div>
                </div>
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
                                        :src="activity.old_value.thumb[0]">
                            </p>
                            <p v-else>
                                {{ infoEmpty }}
                            </p>
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
                                        }" />
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
                                    v-for="(attributeValue, attributeName, index) of activity.old_value"
                                    :key="index">
                                <p 
                                        v-if="attributeName == 'thumb' && attributeValue[0]"
                                        class="tainacan-p-break">                                                          
                                    <img 
                                            style="margin: 12px 0; max-width: 150px;"
                                            :alt="$i18n.get('label_document')"
                                            :src="attributeValue[0]">
                                </p>
                                <p 
                                        v-else
                                        class="tainacan-p-break"
                                        v-html="`<strong>` + attributeName + `: </strong>` + (attributeValue ? attributeValue : infoEmpty)" />
                            </div>
                        </div>

                        <div
                                v-if="activity.action == 'update-metadata-order' || activity.action == 'update-filters-order'"
                                class="content">
                            <div 
                                    v-for="(attributeValue, attributeName, index) in activity.old_value"
                                    :key="index">
                                <p class="is-capitalized has-text-blue5 has-text-weight-bold">
                                    {{ attributeName }}
                                    <small class="has-text-gray4 has-text-weight-normal"> {{ `(${$i18n.get('info_logs_before')})` }}</small>
                                </p>

                                <div class="content">
                                    <p
                                            v-for="(diffContent, diffTitle) in attributeValue"
                                            :key="diffTitle"
                                            class="tainacan-p-break"
                                            v-html="attributeValue ? `ID: ${diffContent.id} <span class='is-italic'>(${diffContent.enabled ? $i18n.get('label_enabled') : $i18n.get('label_disabled')})</span>` : infoEmpty " />
                                </div>
                            </div>
                        </div>

                        <div
                                v-if="activity.action == 'update'"
                                class="content">
                            <div 
                                    v-for="(attributeValue, attributeName, index) in activity.old_value"
                                    :key="index">
                                <p class="is-capitalized has-text-blue5 has-text-weight-bold">
                                    {{ attributeName }}
                                    <small class="has-text-gray4 has-text-weight-normal"> {{ `(${$i18n.get('info_logs_before')})` }}</small>
                                </p>

                                <!-- Is array with length 1 -->
                                <p
                                        v-if="(attributeValue instanceof Array) &&
                                            (attributeValue.length == 1) &&
                                            !(attributeValue[0] instanceof Object)"
                                        class="tainacan-p-break">
                                    {{ attributeValue.toString() }}
                                </p>

                                <div
                                        v-else-if="attributeName == 'metadata_type_options'"
                                        class="content">
                                    <p 
                                            v-for="(innerValue, innerName, innerIndex) of attributeValue"
                                            :key="innerIndex"
                                            class="tainacan-p-break">
                                        <strong>{{ innerName + ': ' }}</strong>{{ innerValue ? innerValue : infoEmpty }}
                                        <br>
                                    </p>
                                </div>

                                <p
                                        v-else
                                        class="tainacan-p-break"
                                        v-html="(!attributeValue || (attributeValue instanceof Array && !attributeValue.length)) ? infoEmpty : (attributeValue instanceof Array ? attributeValue.join(`<span class='multivalue-separator'>|</span>`) : attributeValue)" />
                            </div>
                        </div>

                        <div
                                v-if="activity.action == 'update-metadata-value'"
                                class="content">
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
                                        :src="activity.new_value.thumb[0]">
                            </p>
                            <p v-else>
                                {{ infoEmpty }}
                            </p>
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
                                        }" />
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
                                    v-for="(attributeValue, attributeName, index) of activity.new_value"
                                    :key="index">
                                <p 
                                        v-if="attributeName == 'thumb' && attributeValue[0]"
                                        class="tainacan-p-break">                                                          
                                    <img 
                                            style="margin: 12px 0; max-width: 150px;"
                                            :alt="$i18n.get('label_document')"
                                            :src="attributeValue[0]">
                                </p>
                                <p 
                                        v-else
                                        class="tainacan-p-break"
                                        v-html="`<strong>` + attributeName + `: </strong>` + (attributeValue ? attributeValue : infoEmpty)" />
                            </div>
                        </div>

                        <div
                                v-if="activity.action == 'update-metadata-order' || activity.action == 'update-filters-order'"
                                class="content">
                            <div 
                                    v-for="(attributeValue, attributeName, index) in activity.new_value"
                                    :key="index">
                                <p class="is-capitalized has-text-blue5 has-text-weight-bold">
                                    {{ attributeName }}
                                    <small class="has-text-gray4 has-text-weight-normal"> {{ `(${$i18n.get('info_logs_after')})` }}</small>
                                </p>

                                <div class="content">
                                    <p
                                            v-for="(diffContent, diffTitle) in attributeValue"
                                            :key="diffTitle"
                                            class="tainacan-p-break"
                                            v-html="attributeValue ? `ID: ${diffContent.id} <span class='is-italic'>(${diffContent.enabled ? $i18n.get('label_enabled') : $i18n.get('label_disabled')})</span>` : infoEmpty " />
                                </div>
                            </div>
                        </div>

                        <div 
                                v-for="(attributeValue, attributeName, index) in activity.new_value"
                                :key="index">
                            <div
                                    v-if="activity.action == 'update'"
                                    class="content">
                                <p class="is-capitalized has-text-blue5 has-text-weight-bold">
                                    {{ attributeName }}
                                    <small class="has-text-gray4 has-text-weight-normal"> {{ `(${$i18n.get('info_logs_after')})` }}</small>
                                </p>

                                <div
                                        v-if="attributeName == 'metadata_type_options'"
                                        class="content">
                                    <p 
                                            v-for="(innerValue, innerName, innerIndex) of attributeValue"
                                            :key="innerIndex"
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
                                                v-if="activity.terms && activity.terms.header_image"
                                                style="margin: 12px 0; max-width: 160px;"
                                                :alt="$i18n.get('label_header_image')"
                                                :src="activity.terms.header_image">
                                    </p>
                                </div>

                                <p
                                        v-else
                                        class="tainacan-p-break"
                                        v-html="(!attributeValue || (attributeValue instanceof Array && !attributeValue.length)) ? infoEmpty : (attributeValue instanceof Array ? attributeValue.join(`<span class='multivalue-separator'>|</span>`) : attributeValue)" />
                            </div>
                        </div>
                        
                        <div
                                v-if="activity.action == 'update-metadata-value'"
                                class="content">
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
                        @click="$emit('close')">
                    {{ $i18n.get('close') }}
                </button>
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
            activityId: Number
        },
        emits: [
            'approveActivity',
            'notApproveActivity',
            'close'
        ],
        data() {
            return {
                infoEmpty: `[ ${this.$i18n.get('info_empty').toLowerCase()} ]`,
                dateFormat: '',
                activityCreationDate: '',
                isLoadingActivity: false,
                isRepositoryLevel: false
            }
        },
        computed: {
            ...mapGetters('activity', {
                'activity': 'getActivity'
            }),
            relatedToLink() {
                switch(this.activity.object_type) {
                    case 'Tainacan\\Entities\\Collection':
                        return `${ this.$i18n.get('collection') } 
                                <a href="${ this.$routerHelper.getAbsoluteAdminPath() + this.$routerHelper.getCollectionPath(this.activity.object_id) }">${ this.activity.object.name }</a>
                                <span class="icon has-text-gray3">&nbsp;<i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-collections"/></span>`;
                    case 'Tainacan\\Entities\\Taxonomy':
                        return `${ this.$i18n.get('taxonomy') } 
                                <a href="${ this.$routerHelper.getAbsoluteAdminPath() + this.$routerHelper.getTaxonomyPath(this.activity.object_id) }">${ this.activity.object.name }</a>
                                <span class="icon has-text-gray3">&nbsp;<i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-taxonomies"/></span>`;
                    case 'Tainacan\\Entities\\Metadatum':
                        return `${ this.$i18n.get('metadatum') } 
                                <a href="${ this.$routerHelper.getAbsoluteAdminPath() + (this.activity.object.collection_id == 'default' ? this.$routerHelper.getMetadataEditPath(this.activity.object_id) : this.$routerHelper.getCollectionMetadataEditPath(this.activity.object.collection_id, this.activity.object_id)) }">${ this.activity.object.name }</a>
                                <span class="icon has-text-gray3">&nbsp;<i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-metadata"/></span>`;
                    case 'Tainacan\\Entities\\Filter':
                        return `${ this.$i18n.get('filter') } 
                                <a href="${ this.$routerHelper.getAbsoluteAdminPath() + (this.activity.object.collection_id == 'default' ? this.$routerHelper.getFilterEditPath(this.activity.object_id) : this.$routerHelper.getCollectionFilterEditPath(this.activity.object.collection_id, this.activity.object_id)) }">${ this.activity.object.name }</a>
                                <span class="icon has-text-gray3">&nbsp;<i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-filters"/></span>`;
                    case 'Tainacan\\Entities\\Term':
                        return `${ this.$i18n.get('term') } 
                                <a href="${ this.$routerHelper.getAbsoluteAdminPath() + this.$routerHelper.getTermEditPath(this.activity.object.taxonomy.replace( /^\D+/g, ''), this.activity.object_id) }">${ this.activity.object.name }</a>
                                <span class="icon has-text-gray3">&nbsp;<i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-terms"/></span>`;
                    case 'Tainacan\\Entities\\Item':
                        return `${ this.$i18n.get('item') } 
                                <a href="${ this.$routerHelper.getAbsoluteAdminPath() + this.$routerHelper.getItemEditPath(this.activity.object.collection_id, this.activity.object_id) }">${ this.activity.object.title }</a>
                                <span class="icon has-text-gray3">&nbsp;<i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-items"/></span>`;
                    case 'Tainacan\\Entities\\Item_Metadata_Entity':
                        return `${ this.$i18n.get('item') } 
                                <a href="${ this.$routerHelper.getAbsoluteAdminPath() + this.$routerHelper.getItemEditPath(this.activity.item.collection_id, this.activity.item.id) }">${ this.activity.item.title }</a>
                                <span class="icon has-text-gray3">&nbsp;<i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-items"/></span>`;
                    default: 
                        return '';
                }
            }
        },
        watch: {
            '$route': {
                handler(to, from) {
                    if (to !== from)
                        this.$emit('close');
                },
                deep: true
            }
        },
        created() {
            this.loadActivity();
            this.isRepositoryLevel = (this.$route.params.collectionId === undefined);
        },
        mounted() {
            if (this.$refs.activityDetailsModal)
                this.$refs.activityDetailsModal.focus()
        },
        methods: {
            ...mapActions('activity', [
                'fetchActivity'
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
                        this.dateFormat = localeData.longDateFormat('LLL');

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

    .tainacan-modal-title {
        p {
            margin-right: auto;
        }
    }

    .tainacan-modal-content {
        width: auto;
        min-height: 100px;
        
        p {
            font-size: 0.875em;
        }
    }

    .modal-card-body {
        min-height: 42px;
        padding: 0;
        .columns {
            margin: 6px var(--tainacan-one-column) 0 var(--tainacan-one-column);
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
        border: 1px solid var(--tainacan-gray3);

        &>div {
            margin: 0.5em;
        }
    }

    .tainacan-p-overflow {
        text-overflow: ellipsis;
        white-space: nowrap;
        max-width: 150px;
        overflow: hidden;
    }

    p.is-capitalized {
        margin-bottom: 0.125em;
    }
    .tainacan-p-break {
        word-break: break-word;
        margin-bottom: 0.5em;
    }

    .tainacan-figure {
        width: 150px;
        height: 150px;
        overflow: auto;
    }
</style>