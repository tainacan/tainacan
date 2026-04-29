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
                    @click="closeModal()">
                <span 
                        aria-hidden="true"
                        class="icon">
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
            <div>
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
                        <div v-if="attributeName == 'thumbnail'">
                            <p class="has-text-weight-bold">
                                {{ attributeName }}
                                <small class="has-text-dark has-text-weight-normal"> {{ `(${$i18n.get('info_logs_before')})` }}</small>
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

                        <div v-if="attributeName == 'attachments'">
                            <p class="has-text-weight-bold">
                                {{ attributeName }}
                                <small class="has-text-dark has-text-weight-normal"> {{ `(${$i18n.get('info_logs_before')})` }}</small>
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

                        <div v-if="!['thumbnail', 'attachments'].includes(attributeName)">
                            <p class="has-text-weight-bold">
                                {{ attributeName.replace(/_/g, ' ') }}
                                <small class="has-text-dark has-text-weight-normal"> {{ `(${$i18n.get('info_logs_before')})` }}</small>
                            </p>

                            <!-- Is array with length 1 -->
                            <p
                                    v-if="(diff.old instanceof Array) &&
                                        (diff.old.length == 1) &&
                                        !(diff.old[0] instanceof Object)"
                                    class="log-diff-content log-diff-content--before">
                                {{ diff.old.toString() }}
                            </p>

                            <div v-else-if="attributeName == 'metadata_order'">
                                <p
                                        v-for="(diffContent, diffTitle) in diff.old"
                                        :key="diffTitle"
                                        class="log-diff-content log-diff-content--before">
                                    {{ diff.old ? `ID: ${diffContent.id} | Enabled: ${diffContent.enabled}` : infoEmpty }}
                                </p>
                            </div>

                            <div v-else-if="attributeName == 'filters_order'">
                                <p
                                        v-for="(diffContent, diffTitle) in diff.old"
                                        :key="diffTitle"
                                        class="log-diff-content log-diff-content--before">
                                    {{ diff.old ? `ID: ${diffContent.id} | Enabled: ${diffContent.enabled}` : infoEmpty }}
                                </p>
                            </div>

                            <div v-else-if="attributeName == 'metadata_type_options'">
                                <p class="log-diff-content log-diff-content--before">
                                    {{ diff.old ?
                                        `Taxonomy ID: ${diff.old.taxonomy_id};
                                    Input type: ${diff.old.input_type};
                                    Allow new terms: ${diff.old.allow_new_terms}` : infoEmpty }}
                                </p>
                            </div>

                            <!--  -->
                            <p
                                    v-else
                                    class="log-diff-content log-diff-content--before">
                                {{ diff.old ? (diff.old instanceof Array && !diff.old.length) ? infoEmpty : diff.old.toString().replace(/,/g, ' ') : infoEmpty }}
                            </p>

                        </div>
                    </div>

                    <!-- NEW -->
                    <div class="column is-6">

                        <!-- Thumbnail -->
                        <div v-if="attributeName == 'thumbnail'">
                            <p class="has-text-weight-bold">
                                {{ attributeName }}
                                <small class="has-text-dark has-text-weight-normal"> {{ `(${$i18n.get('info_logs_after')})` }}</small>
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

                        <div v-if="attributeName == 'attachments'">
                            <p class="has-text-weight-bold">
                                {{ attributeName }}
                                <small class="has-text-dark has-text-weight-normal"> {{ `(${$i18n.get('info_logs_after')})` }}</small>
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

                        <div v-if="!['thumbnail', 'attachments'].includes(attributeName)">
                            <p class="has-text-weight-bold">
                                {{ attributeName.replace(/_/g, ' ') }}
                                <small class="has-text-dark has-text-weight-normal"> {{ `(${$i18n.get('info_logs_after')})` }}</small>
                            </p>

                            <!-- Is array with length 1 -->
                            <p
                                    v-if="(diff.new instanceof Array) &&
                                        (diff.new.length == 1) &&
                                        !(diff.new[0] instanceof Object)"
                                    class="log-diff-content log-diff-content--after">
                                {{ diff.new.toString() }}
                            </p>


                            <div v-else-if="attributeName == 'metadata_order'">
                                <p
                                        v-for="(diffContent, diffTitle) in diff.new"
                                        :key="diffTitle"
                                        class="log-diff-content log-diff-content--after">
                                    {{ `ID: ${diffContent.id} | Enabled: ${diffContent.enabled}` }}
                                </p>
                            </div>

                            <div v-else-if="attributeName == 'filters_order'">
                                <p
                                        v-for="(diffContent, diffTitle) in diff.new"
                                        :key="diffTitle"
                                        class="log-diff-content log-diff-content--after">
                                    {{ `ID: ${diffContent.id} | Enabled: ${diffContent.enabled}` }}
                                </p>
                            </div>

                            <div v-else-if="attributeName == 'metadata_type_options'">
                                <p class="log-diff-content log-diff-content--after">
                                    {{ `Taxonomy ID: ${diff.new.taxonomy_id};
                                        Input type: ${diff.new.input_type};
                                        Allow new terms: ${diff.new.allow_new_terms}` }}
                                </p>
                            </div>

                            <!-- -->
                            <p
                                    v-else
                                    class="log-diff-content log-diff-content--after">
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

                        <div v-if="activity.action == 'update-thumbnail'">
                            <p class="has-text-weight-bold">
                                {{ $i18n.get('label_thumbnail') }}
                                <small class="has-text-dark has-text-weight-normal"> {{ `(${$i18n.get('info_logs_before')})` }}</small>
                            </p>
                            <p 
                                    v-if="activity.old_value && activity.old_value.thumb && activity.old_value.thumb[0]"
                                    class="log-diff-content log-diff-content--before">
                                <img 
                                        style="max-width: 150px;"
                                        :alt="$i18n.get('label_thumbnail')"
                                        :src="activity.old_value.thumb[0]">
                            </p>
                            <p v-else>
                                {{ infoEmpty }}
                            </p>
                        </div>

                        <div v-if="activity.action == 'new-attachment'">
                            <p class="has-text-weight-bold">
                                {{ $i18n.get('label_attachment') }}
                                <small class="has-text-dark has-text-weight-normal"> {{ `(${$i18n.get('info_logs_before')})` }}</small>
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

                        <div v-if="activity.action == 'update-document'">
                            <p class="has-text-weight-bold">
                                {{ $i18n.get('label_document') }}
                                <small class="has-text-dark has-text-weight-normal"> {{ `(${$i18n.get('info_logs_before')})` }}</small>
                            </p>
                            <div
                                    v-for="(attributeValue, attributeName, index) of activity.old_value"
                                    :key="index">
                                <p 
                                        v-if="attributeName == 'thumb' && attributeValue[0]"
                                        class="log-diff-content log-diff-content--before">                                                          
                                    <img 
                                            style="max-width: 150px;"
                                            :alt="$i18n.get('label_document')"
                                            :src="attributeValue[0]">
                                </p>
                                <p 
                                        v-else
                                        class="log-diff-content log-diff-content--before"
                                        v-html="`<strong>` + attributeName + `: </strong>` + (attributeValue ? attributeValue : infoEmpty)" />
                            </div>
                        </div>

                        <div v-if="activity.action == 'update-metadata-order' || activity.action == 'update-filters-order'">
                            <div 
                                    v-for="(attributeValue, attributeName, index) in activity.old_value"
                                    :key="index">
                                <p class="has-text-weight-bold">
                                    {{ attributeName }}
                                    <small class="has-text-dark has-text-weight-normal"> {{ `(${$i18n.get('info_logs_before')})` }}</small>
                                </p>

                                <div>
                                    <p
                                            v-for="(diffContent, diffTitle) in attributeValue"
                                            :key="diffTitle"
                                            class="log-diff-content log-diff-content--before"
                                            v-html="attributeValue ? `ID: ${diffContent.id} <span class='is-italic'>(${diffContent.enabled ? $i18n.get('label_enabled') : $i18n.get('label_disabled')})</span>` : infoEmpty " />
                                </div>
                            </div>
                        </div>

                        <div v-if="activity.action == 'update'">
                            <div 
                                    v-for="(attributeValue, attributeName, index) in activity.old_value"
                                    :key="index">
                                <p class="has-text-weight-bold">
                                    {{ attributeName }}
                                    <small class="has-text-dark has-text-weight-normal"> {{ `(${$i18n.get('info_logs_before')})` }}</small>
                                </p>

                                <!-- Is array with length 1 -->
                                <p
                                        v-if="(attributeValue instanceof Array) &&
                                            (attributeValue.length == 1) &&
                                            !(attributeValue[0] instanceof Object)"
                                        class="log-diff-content log-diff-content--before">
                                    {{ attributeValue.toString() }}
                                </p>

                                <div v-else-if="attributeName == 'metadata_type_options'">
                                    <p 
                                            v-for="(innerValue, innerName, innerIndex) of attributeValue"
                                            :key="innerIndex"
                                            class="log-diff-content log-diff-content--before">
                                        <strong>{{ innerName + ': ' }}</strong>{{ innerValue ? innerValue : infoEmpty }}
                                        <br>
                                    </p>
                                </div>

                                <p
                                        v-else
                                        class="log-diff-content log-diff-content--before"
                                        v-html="(!attributeValue || (attributeValue instanceof Array && !attributeValue.length)) ? infoEmpty : (attributeValue instanceof Array ? attributeValue.join(`<span class='multivalue-separator'>|</span>`) : attributeValue)" />
                            </div>
                        </div>

                        <div v-if="activity.action == 'update-metadata-value'">
                            <p class="has-text-weight-bold">
                                {{ activity.object && activity.object.name ? activity.object.name : $i18n.get('metadatum') }}
                                <small class="has-text-dark has-text-weight-normal"> {{ `(${$i18n.get('info_logs_before')})` }}</small>
                            </p>
                            <p
                                    class="log-diff-content log-diff-content--before"
                                    v-html="!activity.old_value ? infoEmpty : (activity.old_value instanceof Array ? activity.old_value.join(`<span class='multivalue-separator'>|</span>`) : activity.old_value)" />
                        </div>
                    </div>

                    <!-- NEW -->
                    <div class="column is-6">

                        <div v-if="activity.action == 'update-thumbnail'">
                            <p class="has-text-weight-bold">
                                {{ $i18n.get('label_thumbnail') }}
                                <small class="has-text-dark has-text-weight-normal"> {{ `(${$i18n.get('info_logs_after')})` }}</small>
                            </p>
                            <p 
                                    v-if="activity.new_value && activity.new_value.thumb && activity.new_value.thumb[0]"
                                    class="log-diff-content log-diff-content--after">
                                <img 
                                        style="margin: 12px 0; max-width: 150px;"
                                        :alt="$i18n.get('label_thumbnail')"
                                        :src="activity.new_value.thumb[0]">
                            </p>
                            <p v-else>
                                {{ infoEmpty }}
                            </p>
                        </div>

                        <div v-if="activity.action == 'new-attachment'">
                            <p class="has-text-weight-bold">
                                {{ $i18n.get('label_attachment') }}
                                <small class="has-text-dark has-text-weight-normal"> {{ `(${$i18n.get('info_logs_after')})` }}</small>
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

                        <div v-if="activity.action == 'update-document'">
                            <p class="has-text-weight-bold">
                                {{ $i18n.get('label_document') }}
                                <small class="has-text-dark has-text-weight-normal"> {{ `(${$i18n.get('info_logs_after')})` }}</small>
                            </p>
                            <div
                                    v-for="(attributeValue, attributeName, index) of activity.new_value"
                                    :key="index">
                                <p 
                                        v-if="attributeName == 'thumb' && attributeValue[0]"
                                        class="log-diff-content log-diff-content--after">                                                          
                                    <img 
                                            style="margin: 12px 0; max-width: 150px;"
                                            :alt="$i18n.get('label_document')"
                                            :src="attributeValue[0]">
                                </p>
                                <p 
                                        v-else
                                        class="log-diff-content log-diff-content--after"
                                        v-html="`<strong>` + attributeName + `: </strong>` + (attributeValue ? attributeValue : infoEmpty)" />
                            </div>
                        </div>

                        <div v-if="activity.action == 'update-metadata-order' || activity.action == 'update-filters-order'">
                            <div 
                                    v-for="(attributeValue, attributeName, index) in activity.new_value"
                                    :key="index">
                                <p class="has-text-weight-bold">
                                    {{ attributeName }}
                                    <small class="has-text-dark has-text-weight-normal"> {{ `(${$i18n.get('info_logs_after')})` }}</small>
                                </p>

                                <div>
                                    <p
                                            v-for="(diffContent, diffTitle) in attributeValue"
                                            :key="diffTitle"
                                            class="log-diff-content log-diff-content--after"
                                            v-html="attributeValue ? `ID: ${diffContent.id} <span class='is-italic'>(${diffContent.enabled ? $i18n.get('label_enabled') : $i18n.get('label_disabled')})</span>` : infoEmpty " />
                                </div>
                            </div>
                        </div>

                        <div 
                                v-for="(attributeValue, attributeName, index) in activity.new_value"
                                :key="index">
                            <div v-if="activity.action == 'update'">
                                <p class="has-text-weight-bold">
                                    {{ attributeName }}
                                    <small class="has-text-dark has-text-weight-normal"> {{ `(${$i18n.get('info_logs_after')})` }}</small>
                                </p>

                                <div v-if="attributeName == 'metadata_type_options'">
                                    <p 
                                            v-for="(innerValue, innerName, innerIndex) of attributeValue"
                                            :key="innerIndex"
                                            class="log-diff-content log-diff-content--after">
                                        <strong>{{ innerName + ': ' }}</strong>{{ innerValue ? innerValue : infoEmpty }}
                                        <br>
                                    </p>
                                </div>

                                <div v-else-if="attributeName == 'header_image_id'">
                                    <p class="log-diff-content log-diff-content--after">
                                        {{ attributeValue ? attributeValue : infoEmpty }}
                                        <br>
                                        <img 
                                                v-if="activity.object && activity.object.header_image"
                                                style="max-width: 160px;"
                                                :alt="$i18n.get('label_header_image')"
                                                :src="activity.object.header_image">
                                    </p>
                                </div>

                                <p
                                        v-else
                                        class="log-diff-content log-diff-content--after"
                                        v-html="(!attributeValue || (attributeValue instanceof Array && !attributeValue.length)) ? infoEmpty : (attributeValue instanceof Array ? attributeValue.join(`<span class='multivalue-separator'>|</span>`) : attributeValue)" />
                            </div>
                        </div>
                        
                        <div v-if="activity.action == 'update-metadata-value'">
                            <p class="has-text-weight-bold">
                                {{ activity.object && activity.object.name ? activity.object.name : $i18n.get('metadatum') }}
                                <small class="has-text-dark has-text-weight-normal"> {{ `(${$i18n.get('info_logs_after')})` }}</small>
                            </p>
                            <p
                                    class="log-diff-content log-diff-content--after"
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
                        @click="closeModal()">
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
            'beforeClose',
            'close'
        ],
        data() {
            return {
                infoEmpty: this.$i18n.get('info_empty'),
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
                                <span aria-hidden="true" class="icon has-text-grey">&nbsp;<i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-collections"/></span>`;
                    case 'Tainacan\\Entities\\Taxonomy':
                        return `${ this.$i18n.get('taxonomy') } 
                                <a href="${ this.$routerHelper.getAbsoluteAdminPath() + this.$routerHelper.getTaxonomyPath(this.activity.object_id) }">${ this.activity.object.name }</a>
                                <span aria-hidden="true" class="icon has-text-grey">&nbsp;<i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-taxonomies"/></span>`;
                    case 'Tainacan\\Entities\\Metadatum':
                        return `${ this.$i18n.get('metadatum') } 
                                <a href="${ this.$routerHelper.getAbsoluteAdminPath() + (this.activity.object.collection_id == 'default' ? this.$routerHelper.getMetadataEditPath(this.activity.object_id) : this.$routerHelper.getCollectionMetadataEditPath(this.activity.object.collection_id, this.activity.object_id)) }">${ this.activity.object.name }</a>
                                <span aria-hidden="true" class="icon has-text-grey">&nbsp;<i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-metadata"/></span>`;
                    case 'Tainacan\\Entities\\Filter':
                        return `${ this.$i18n.get('filter') } 
                                <a href="${ this.$routerHelper.getAbsoluteAdminPath() + (this.activity.object.collection_id == 'default' ? this.$routerHelper.getFilterEditPath(this.activity.object_id) : this.$routerHelper.getCollectionFilterEditPath(this.activity.object.collection_id, this.activity.object_id)) }">${ this.activity.object.name }</a>
                                <span aria-hidden="true" class="icon has-text-grey">&nbsp;<i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-filters"/></span>`;
                    case 'Tainacan\\Entities\\Term':
                        return `${ this.$i18n.get('term') } 
                                <a href="${ this.$routerHelper.getAbsoluteAdminPath() + this.$routerHelper.getTermEditPath(this.activity.object.taxonomy.replace( /^\D+/g, ''), this.activity.object_id) }">${ this.activity.object.name }</a>
                                <span aria-hidden="true" class="icon has-text-grey">&nbsp;<i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-terms"/></span>`;
                    case 'Tainacan\\Entities\\Item':
                        return `${ this.$i18n.get('item') } 
                                <a href="${ this.$routerHelper.getAbsoluteAdminPath() + this.$routerHelper.getItemEditPath(this.activity.object.collection_id, this.activity.object_id) }">${ this.activity.object.title }</a>
                                <span aria-hidden="true" class="icon has-text-grey">&nbsp;<i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-items"/></span>`;
                    case 'Tainacan\\Entities\\Item_Metadata_Entity':
                        return `${ this.$i18n.get('item') } 
                                <a href="${ this.$routerHelper.getAbsoluteAdminPath() + this.$routerHelper.getItemEditPath(this.activity.item.collection_id, this.activity.item.id) }">${ this.activity.item.title }</a>
                                <span aria-hidden="true" class="icon has-text-grey">&nbsp;<i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-items"/></span>`;
                    default: 
                        return '';
                }
            }
        },
        watch: {
            '$route': {
                handler(to, from) {
                    if (to !== from)
                        this.closeModal();
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
            closeModal() {
                this.$emit('beforeClose');
                this.$emit('close');
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

                        let logDate = this.activity.date;

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


    .tainacan-modal-content {
        width: auto;
        min-height: 100px;
        
        p {
            font-size: 0.875em;
            margin-bottom: 0.5em;
        }
    }

    .modal-card-body {
        min-height: 42px;
        padding: 0;

        .columns {
            margin: 0 var(--tainacan-one-column) 0.5rem var(--tainacan-one-column);
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

    .log-diff-content {
        word-break: break-word;
        border-radius: var(--tainacan-input-border-radius);
        padding: 6px;
        max-height: 40vh;
        overflow-y: auto;

        &--before:not(:empty):not(:has(img)) {
            background-color: var(--tainacan-red1);
        }

        &--after:not(:empty):not(:has(img)) {
            background-color: var(--tainacan-green1);
        }
    }
</style>