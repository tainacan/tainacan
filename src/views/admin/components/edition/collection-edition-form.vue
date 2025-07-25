<template>
    <div 
            class="page-container"
            :class="{'tainacan-repository-level-colors' : isNewCollection }">

        <tainacan-external-link
                v-if="collection && collection.slug && collection.url"
                :link-label="$i18n.get('label_view_collection_on_website')"
                :link-url="collection.url" />

        <tainacan-title :is-sticky="true" />
        
        <form 
                v-if="collection != null && collection != undefined && ((isNewCollection && $userCaps.hasCapability('tnc_rep_edit_collections')) || (!isNewCollection && collection.current_user_can_edit))" 
                class="tainacan-form" 
                label-width="120px">
        
            <div class="columns is-multiline">
                <div class="column is-7-widescreen is-full-desktop">

                    <!-- Name -------------------------------- --> 
                    <b-field 
                            :addons="false"
                            :label="$i18n.get('label_name')"
                            :type="editFormErrors['name'] != undefined ? 'is-danger' : ''" 
                            :message="editFormErrors['name'] != undefined ? editFormErrors['name'] : ''">
                        <span class="required-metadatum-asterisk">*</span>
                        <help-button 
                                :title="$i18n.getHelperTitle('collections', 'name')" 
                                :message="$i18n.getHelperMessage('collections', 'name')" />
                        <b-input
                                id="tainacan-text-name"
                                v-model="form.name"
                                :placeholder="$i18n.get('instruction_collection_name')"
                                @blur="updateSlug"
                                @focus="clearErrors('name')" />
                    </b-field>

                    <!-- Hook for extra Form options -->
                    <template v-if="hasBeginLeftForm">  
                        <form
                                id="form-collection-begin-left" 
                                class="form-hook-region"
                                v-html="getBeginLeftForm" />
                    </template>

                    <!-- Description -------------------------------- --> 
                    <b-field
                            :addons="false" 
                            :label="$i18n.get('label_description')"
                            :type="editFormErrors['description'] != undefined ? 'is-danger' : ''" 
                            :message="editFormErrors['description'] != undefined ? editFormErrors['description'] : ''">
                        <help-button 
                                :title="$i18n.getHelperTitle('collections', 'description')" 
                                :message="$i18n.getHelperMessage('collections', 'description')" />
                        <b-input
                                id="tainacan-text-description"
                                v-model="form.description"
                                type="textarea"
                                rows="4"
                                :placeholder="$i18n.get('instruction_collection_description')"
                                @focus="clearErrors('description')" />
                    </b-field>

                    <!-- Collection Taxonomies options ------------------------ -->
                    <div 
                            v-if="!isLoading && !isLoadingCollectionTaxonomies && Object.values(getCollectionTaxonomies) && Object.values(getCollectionTaxonomies).length > 0"
                            class="collection-form-section"
                            @click="showCollectionsTaxonomiesOptions = !showCollectionsTaxonomiesOptions;">
                        <span class="icon">
                            <i 
                                    class="tainacan-icon"
                                    :class="showCollectionsTaxonomiesOptions ? 'tainacan-icon-arrowdown' : 'tainacan-icon-arrowright'" />
                        </span>
                        <strong>{{ $i18n.get('label_collections_taxonomies') }}</strong>
                        <hr>
                    </div>
                    <transition name="filter-item">
                        <div 
                                v-show="showCollectionsTaxonomiesOptions"
                                class="options-columns">
                            <template v-for="(collectionTaxonomy, taxonomySlug) in getCollectionTaxonomies">
                                <b-field 
                                        v-if="collectionTaxonomy['terms'] && form.collection_taxonomies && form.collection_taxonomies[taxonomySlug]"
                                        :key="taxonomySlug"
                                        :addons="false"
                                        :label="collectionTaxonomy['name']">
                                    <div class="options-checkboxes">
                                        <b-checkbox
                                                v-for="(collectionTaxonomyTerm, index) in collectionTaxonomy['terms']"
                                                :key="index"
                                                :model-value="form.collection_taxonomies[taxonomySlug]['terms'] && form.collection_taxonomies[taxonomySlug]['terms'][collectionTaxonomyTerm['slug']] ? true : false"
                                                @update:model-value="($event) => updateCollectionTaxonomyTerm(collectionTaxonomy['rest_base'], taxonomySlug, collectionTaxonomyTerm, $event)">
                                            {{ collectionTaxonomyTerm['name'] }}
                                        </b-checkbox>
                                    </div>
                                </b-field>
                            </template>
                        </div>
                    </transition>

                    <!-- Items list options ------------------------ -->
                    <div 
                            class="collection-form-section"
                            @click="showItemsListOptions = !showItemsListOptions;">
                        <span class="icon">
                            <i 
                                    class="tainacan-icon"
                                    :class="showItemsListOptions ? 'tainacan-icon-arrowdown' : 'tainacan-icon-arrowright'" />
                        </span>
                        <strong>{{ $i18n.get('label_items_list_options') }}</strong>
                        <hr>

                    </div>
                    <transition name="filter-item">
                        <div 
                                v-show="showItemsListOptions"
                                class="options-columns">

                            <!-- Change Default OrderBy Select and Order Button-->
                            <b-field
                                    :addons="false" 
                                    :label="$i18n.get('label_default_orderby')"
                                    :type="editFormErrors['default_orderby'] != undefined ? 'is-danger' : ''" 
                                    :message="editFormErrors['default_orderby'] != undefined ? editFormErrors['default_orderby'] : $i18n.get('info_default_orderby')">
                                <help-button 
                                        :title="$i18n.getHelperTitle('collections', 'default_orderby')" 
                                        :message="$i18n.getHelperMessage('collections', 'default_orderby')" />
                                <div class="control sorting-options">
                                    <label class="label">{{ $i18n.get('label_sort') }}&nbsp;</label>
                                    <b-select
                                            id="tainacan-select-default_order"
                                            v-model="form.default_order">
                                        <option
                                                role="button"
                                                :class="{ 'is-active': form.default_order == 'DESC' }"
                                                :value="'DESC'">
                                            {{ $i18n.get('label_descending') }}
                                        </option>
                                        <option
                                                role="button"
                                                :class="{ 'is-active': form.default_order == 'ASC' }"
                                                :value="'ASC'">
                                            {{ $i18n.get('label_ascending') }}
                                        </option>
                                    </b-select>
                                    <span
                                            class="label"
                                            style="padding: 0 0.65em;">
                                        {{ $i18n.get('info_by_inner') }}
                                    </span>
                                    <b-select
                                            id="tainacan-select-default_orderby"
                                            v-model="localDefaultOrderBy"
                                            expanded
                                            :loading="isLoadingMetadata">
                                        <option
                                                v-for="metadatum of sortingMetadata"
                                                :key="metadatum.id"
                                                :value="metadatum.id">
                                            {{ metadatum.name }}
                                        </option>
                                    </b-select>
                                </div>
                            </b-field>


                            <label class="label">{{ $i18n.get('label_view_modes_public_list') }}</label>
                            <div class="two-thirds-layout-options items-view-mode-options">

                                <!-- Enabled View Modes ------------------------------- --> 
                                <div class="field">
                                    <label class="label">{{ $i18n.get('label_view_modes_available') }}</label>
                                    <help-button 
                                            :title="$i18n.getHelperTitle('collections', 'enabled_view_modes')" 
                                            :message="$i18n.getHelperMessage('collections', 'enabled_view_modes')" />
                                    <div class="control">
                                        <b-dropdown
                                                ref="enabledViewModesDropdown"
                                                class="enabled-view-modes-dropdown"
                                                :mobile-modal="true"
                                                :disabled="Object.keys(registeredAndNotDisabledViewModes).length < 0"
                                                aria-role="list"
                                                trap-focus
                                                position="is-top-right">
                                            <template #trigger>
                                                <button
                                                        class="button is-white"
                                                        position="is-top-right"
                                                        type="button">
                                                    <span>{{ $i18n.get('label_enabled_view_modes') }}</span>
                                                    <span class="icon">
                                                        <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-arrowdown" />
                                                    </span>
                                                </button>
                                            </template>
                                            <b-dropdown-item
                                                    v-for="(viewMode, index) in Object.keys(registeredAndNotDisabledViewModes)"
                                                    :key="index"
                                                    custom
                                                    aria-role="listitem">
                                                <b-checkbox
                                                        v-if="registeredAndNotDisabledViewModes[viewMode] != undefined"
                                                        :model-value="checkIfViewModeEnabled(viewMode)"
                                                        :disabled="checkIfViewModeEnabled(viewMode) && form.enabled_view_modes.filter((aViewMode) => (registeredAndNotDisabledViewModes[aViewMode] && registeredAndNotDisabledViewModes[aViewMode].full_screen != true)).length <= 1"
                                                        @update:model-value="updateViewModeslist(viewMode)">
                                                    <p>
                                                        <strong>
                                                            <span 
                                                                    class="gray-icon"
                                                                    :class="{ 
                                                                        'has-text-secondary' : checkIfViewModeEnabled(viewMode),
                                                                        'has-text-gray4' : !checkIfViewModeEnabled(viewMode)  
                                                                    }"
                                                                    v-html="registeredAndNotDisabledViewModes[viewMode].icon" />
                                                            &nbsp;{{ registeredAndNotDisabledViewModes[viewMode].label }}
                                                        </strong>
                                                    </p>
                                                    <p v-if="registeredAndNotDisabledViewModes[viewMode].description">
                                                        {{ registeredAndNotDisabledViewModes[viewMode].description }}
                                                    </p>
                                                </b-checkbox>
                                            </b-dropdown-item>   
                                        </b-dropdown>
                                    </div>
                                </div>
                                
                                <!-- Default View Mode -------------------------------- --> 
                                <b-field
                                        v-if="form.enabled_view_modes.length > 0"
                                        :addons="false" 
                                        :label="$i18n.get('label_default')"
                                        :type="editFormErrors['default_view_mode'] != undefined ? 'is-danger' : ''" 
                                        :message="editFormErrors['default_view_mode'] != undefined ? editFormErrors['default_view_mode'] : ''">
                                    <help-button 
                                            :title="$i18n.getHelperTitle('collections', 'default_view_mode')" 
                                            :message="$i18n.getHelperMessage('collections', 'default_view_mode')" />
                                    <b-select
                                            id="tainacan-select-default_view_mode"
                                            v-model="form.default_view_mode"
                                            expanded
                                            @focus="clearErrors('default_view_mode')">
                                        <option
                                                v-for="(viewMode, index) of validDefaultViewModes"
                                                :key="index"
                                                :value="viewMode">
                                            {{ registeredAndNotDisabledViewModes[viewMode].label }}
                                        </option>
                                    </b-select>
                                </b-field>
                            </div>

                            <!-- Hide Items Thumbnail on Lists ------------------------ --> 
                            <b-field
                                    :addons="false" 
                                    :label="$i18n.getHelperTitle('collections', 'hide_items_thumbnail_on_lists')">
                                &nbsp;
                                <b-switch
                                        id="tainacan-checkbox-hide-items-thumbnail-on-lists"
                                        v-model="form.hide_items_thumbnail_on_lists"
                                        size="is-small" 
                                        true-value="yes"
                                        false-value="no" />
                                <help-button 
                                        :title="$i18n.getHelperTitle('collections', 'hide_items_thumbnail_on_lists')" 
                                        :message="$i18n.getHelperMessage('collections', 'hide_items_thumbnail_on_lists')" />
                            </b-field>

                        </div>
                    </transition>

                    <!-- Item edition form options ------------------------ -->
                    <div 
                            class="collection-form-section"
                            @click="showItemEditionFormOptions = !showItemEditionFormOptions;">
                        <span class="icon">
                            <i 
                                    class="tainacan-icon"
                                    :class="showItemEditionFormOptions ? 'tainacan-icon-arrowdown' : 'tainacan-icon-arrowright'" />
                        </span>
                        <strong>{{ $i18n.get('label_item_edition_form_options') }}</strong>
                        <hr>

                    </div>
                    <transition name="filter-item">
                        <div 
                                v-show="showItemEditionFormOptions"
                                class="options-columns">

                            <!-- Allowed types of main document -------------------------------- -->
                            <div> 
                                <b-field
                                        :addons="false" 
                                        :label="$i18n.getHelperTitle('collections', 'item_enabled_document_types')">
                                    <help-button 
                                            :title="$i18n.getHelperTitle('collections', 'item_enabled_document_types')" 
                                            :message="$i18n.getHelperMessage('collections', 'item_enabled_document_types')" />
                                    <div class="status-radios">
                                        <b-checkbox
                                                v-for="(documentType, slug) in form.item_enabled_document_types"
                                                :key="slug"
                                                v-model="documentType.enabled"
                                                true-value="yes"
                                                false-value="no">
                                            <span class="icon">
                                                <i :class="'tainacan-icon tainacan-icon-' + documentType.icon" />
                                            </span>
                                            {{ documentType.label }}
                                        </b-checkbox>
                                    </div>
                                </b-field>
                                <b-field
                                        v-if="Object.values(form.item_enabled_document_types).some((aDocumentType) => aDocumentType.enabled === 'yes')"
                                        :addons="false" 
                                        :label="$i18n.getHelperTitle('collections', 'item_document_label')">
                                    <help-button 
                                            :title="$i18n.getHelperTitle('collections', 'item_document_label')" 
                                            :message="$i18n.getHelperMessage('collections', 'item_document_label')" />
                                    <b-input
                                            id="tainacan-text-item-document-label"
                                            v-model="form.item_document_label" />
                                </b-field>
                            </div>

                            <!-- Thumbnail Label -------------------------------- -->
                            <div>
                                <b-field
                                        style="margin-top: 1.5rem; margin-bottom: 0rem;"
                                        :addons="false" 
                                        :label="$i18n.getHelperTitle('collections', 'item_enable_thumbnail')">
                                    &nbsp;
                                    <b-switch
                                            id="tainacan-checkbox-item-enable-thumbnail" 
                                            v-model="form.item_enable_thumbnail"
                                            size="is-small" 
                                            true-value="yes"
                                            false-value="no" />
                                    <help-button 
                                            :title="$i18n.getHelperTitle('collections', 'item_enable_thumbnail')" 
                                            :message="$i18n.getHelperMessage('collections', 'item_enable_thumbnail')" />
                                </b-field>

                                <b-field
                                        v-if="form.item_enable_thumbnail === 'yes'"
                                        :addons="false" 
                                        :label="$i18n.getHelperTitle('collections', 'item_thumbnail_label')">
                                    <help-button 
                                            :title="$i18n.getHelperTitle('collections', 'item_thumbnail_label')" 
                                            :message="$i18n.getHelperMessage('collections', 'item_thumbnail_label')" />
                                    <b-input
                                            id="tainacan-text-item-thumbnail-label"
                                            v-model="form.item_thumbnail_label" />
                                </b-field>
                            </div>

                            <!-- Allow attachments ------------------------ --> 
                            <div>
                                <b-field
                                        style="margin-top: 1.5rem; margin-bottom: 0rem;"
                                        :addons="false" 
                                        :label="$i18n.getHelperTitle('collections', 'item_enable_attachments')">
                                    &nbsp;
                                    <b-switch
                                            id="tainacan-checkbox-item-enable-attachments" 
                                            v-model="form.item_enable_attachments"
                                            size="is-small" 
                                            true-value="yes"
                                            false-value="no" />
                                    <help-button 
                                            :title="$i18n.getHelperTitle('collections', 'item_enable_attachments')" 
                                            :message="$i18n.getHelperMessage('collections', 'item_enable_attachments')" />
                                </b-field>

                                <!-- Attachments Label -------------------------------- -->
                                <b-field
                                        v-if="form.item_enable_attachments === 'yes'"
                                        :addons="false" 
                                        :label="$i18n.getHelperTitle('collections', 'item_attachment_label')">
                                    <help-button 
                                            :title="$i18n.getHelperTitle('collections', 'item_attachment_label')" 
                                            :message="$i18n.getHelperMessage('collections', 'item_attachment_label')" />
                                    <b-input
                                            id="tainacan-text-item-attachment-label-singular"
                                            v-model="form.item_attachment_label" />
                                </b-field>
                            </div>

                            <!-- Features related to how metadata are shown in the item edition form -------------------------------- --> 
                            <b-field
                                    :addons="false" 
                                    :label="$i18n.get('label_metadata_related_features')">
                                <div class="options-checkboxes">
                                    <b-checkbox
                                            v-model="form.item_enable_metadata_collapses"
                                            true-value="yes"
                                            false-value="no">
                                        {{ $i18n.getHelperTitle('collections', 'item_enable_metadata_collapses') }}
                                    </b-checkbox>
                                    <b-checkbox
                                            v-model="form.item_enable_metadata_focus_mode"
                                            true-value="yes"
                                            false-value="no">
                                        {{ $i18n.getHelperTitle('collections', 'item_enable_metadata_focus_mode') }}
                                    </b-checkbox>
                                    <b-checkbox
                                            v-model="form.item_enable_metadata_required_filter"
                                            true-value="yes"
                                            false-value="no">
                                        {{ $i18n.getHelperTitle('collections', 'item_enable_metadata_required_filter') }}
                                    </b-checkbox>
                                    <b-checkbox
                                            v-model="form.item_enable_metadata_searchbar"
                                            true-value="yes"
                                            false-value="no">
                                        {{ $i18n.getHelperTitle('collections', 'item_enable_metadata_searchbar') }}
                                    </b-checkbox>
                                    <b-checkbox
                                            v-model="form.item_enable_metadata_enumeration"
                                            true-value="yes"
                                            false-value="no">
                                        {{ $i18n.getHelperTitle('collections', 'item_enable_metadata_enumeration') }}
                                    </b-checkbox>
                                </div>
                            </b-field>

                            <!-- Publicationa area label -->
                            <b-field
                                    :addons="false" 
                                    :label="$i18n.getHelperTitle('collections', 'item_publication_label')">
                                <help-button 
                                        :title="$i18n.getHelperTitle('collections', 'item_publication_label')" 
                                        :message="$i18n.getHelperMessage('collections', 'item_publication_label')" />
                                <b-input
                                        id="tainacan-text-item-publication-label"
                                        v-model="form.item_publication_label" />
                            </b-field>

                            <!-- Slug editing ------------------------ --> 
                            <b-field
                                    :addons="false" 
                                    :label="$i18n.getHelperTitle('collections', 'allow_item_slug_editing')">
                                &nbsp;
                                <b-switch
                                        id="tainacan-checkbox-item-slug-editing" 
                                        v-model="form.allow_item_slug_editing"
                                        size="is-small" 
                                        true-value="yes"
                                        false-value="no" />
                                <help-button 
                                        :title="$i18n.getHelperTitle('collections', 'allow_item_slug_editing')" 
                                        :message="$i18n.getHelperMessage('collections', 'allow_item_slug_editing')" />
                            </b-field>

                            <!-- Author editing ------------------------ --> 
                            <b-field
                                    :addons="false" 
                                    :label="$i18n.getHelperTitle('collections', 'allow_item_author_editing')">
                                &nbsp;
                                <b-switch
                                        id="tainacan-checkbox-item-author-editing" 
                                        v-model="form.allow_item_author_editing"
                                        size="is-small" 
                                        true-value="yes"
                                        false-value="no" />
                                <help-button 
                                        :title="$i18n.getHelperTitle('collections', 'allow_item_author_editing')" 
                                        :message="$i18n.getHelperMessage('collections', 'allow_item_author_editing')" />
                            </b-field>

                            <!-- Comment Status ------------------------ --> 
                            <b-field
                                    :addons="false" 
                                    :label="$i18n.getHelperTitle('collections', 'allow_comments')">
                                &nbsp;
                                <b-switch
                                        id="tainacan-checkbox-comment-status" 
                                        v-model="form.allow_comments"
                                        size="is-small" 
                                        true-value="open"
                                        false-value="closed" />
                                <help-button 
                                        :title="$i18n.getHelperTitle('collections', 'allow_comments')" 
                                        :message="$i18n.getHelperMessage('collections', 'allow_comments')" />
                            </b-field>

                        </div>
                    </transition>

                    <!-- Item submission options ------------------------ -->
                    <div 
                            class="collection-form-section"
                            @click="showItemSubmissionOptions = !showItemSubmissionOptions;">
                        <span class="icon">
                            <i 
                                    class="tainacan-icon"
                                    :class="showItemSubmissionOptions ? 'tainacan-icon-arrowdown' : 'tainacan-icon-arrowright'" />
                        </span>
                        <strong>{{ $i18n.get('label_item_submission_options') }}</strong>
                        <hr>

                    </div>
                    <transition name="filter-item">
                        <div 
                                v-show="showItemSubmissionOptions"
                                class="options-columns">

                            <!-- Allows Submissions ------------------------ --> 
                            <b-field
                                    :addons="false" 
                                    :label="$i18n.getHelperTitle('collections', 'allows_submission')"
                                    :type="editFormErrors['allows_submission'] != undefined ? 'is-danger' : ''" 
                                    :message="editFormErrors['allows_submission'] != undefined ? editFormErrors['allows_submission'] : ''">
                                &nbsp;
                                <b-switch
                                        id="tainacan-checkbox-allow-submission" 
                                        v-model="form.allows_submission"
                                        size="is-small" 
                                        true-value="yes"
                                        false-value="no" />
                                <help-button 
                                        :title="$i18n.getHelperTitle('collections', 'allows_submission')" 
                                        :message="$i18n.getHelperMessage('collections', 'allows_submission')" />
                            </b-field>
                            
                            <transition name="filter-item">
                                <div 
                                        v-if="form.allows_submission === 'yes'"
                                        class="item-submission-options field">

                                    <!-- Allows Submissions by anonynmous user ------------------------ --> 
                                    <b-field
                                            :addons="false" 
                                            :label="$i18n.getHelperTitle('collections', 'submission_anonymous_user')"
                                            :type="editFormErrors['submission_anonymous_user'] != undefined ? 'is-danger' : ''" 
                                            :message="editFormErrors['submission_anonymous_user'] != undefined ? editFormErrors['submission_anonymous_user'] : ''">
                                        &nbsp;
                                        <b-switch
                                                id="tainacan-checkbox-allow-submission" 
                                                v-model="form.submission_anonymous_user"
                                                size="is-small" 
                                                true-value="yes"
                                                false-value="no" />
                                        <help-button 
                                                :title="$i18n.getHelperTitle('collections', 'submission_anonymous_user')" 
                                                :message="$i18n.getHelperMessage('collections', 'submission_anonymous_user')" />
                                    </b-field>

                                    <!-- Item submission default Status -------------------------------- --> 
                                    <b-field
                                            :addons="false" 
                                            :label="$i18n.getHelperTitle('collections', 'submission_default_status')"
                                            :type="editFormErrors['submission_default_status'] != undefined ? 'is-danger' : ''" 
                                            :message="editFormErrors['submission_default_status'] != undefined ? editFormErrors['submission_default_status'] : ''">
                                        <help-button 
                                                :title="$i18n.getHelperTitle('collections', 'submission_default_status')" 
                                                :message="$i18n.getHelperMessage('collections', 'submission_default_status')" />
                                        <div class="options-checkboxes">
                                            <b-radio
                                                    v-for="(statusOption, index) of $statusHelper.getStatuses().filter((status) => status.slug != 'trash')"
                                                    :key="index"
                                                    v-model="form.submission_default_status"
                                                    :native-value="statusOption.slug">
                                                <span class="icon has-text-gray">
                                                    <i 
                                                            class="tainacan-icon tainacan-icon-18px"
                                                            :class="$statusHelper.getIcon(statusOption.slug)" />
                                                </span>
                                                {{ statusOption.name }}
                                            </b-radio>
                                        </div>
                                        <transition name="filter-item">
                                            <p 
                                                    v-if="form.submission_default_status == 'draft'"
                                                    class="help">
                                                {{ $i18n.get('info_item_submission_draft_status') }}
                                            </p>
                                        </transition>
                                    </b-field>

                                    <!-- Submission process uses reCAPTCHA ------------------------ --> 
                                    <b-field
                                            :addons="false" 
                                            :label="$i18n.getHelperTitle('collections', 'submission_use_recaptcha')"
                                            :type="editFormErrors['submission_use_recaptcha'] != undefined ? 'is-danger' : ''" 
                                            :message="editFormErrors['submission_use_recaptcha'] != undefined ? editFormErrors['submission_use_recaptcha'] : ''">
                                        &nbsp;
                                        <b-switch
                                                id="tainacan-checkbox-submission-use-recaptcha" 
                                                v-model="form.submission_use_recaptcha"
                                                size="is-small" 
                                                true-value="yes"
                                                false-value="no" />
                                        <help-button 
                                                :title="$i18n.getHelperTitle('collections', 'submission_use_recaptcha')" 
                                                :message="$i18n.getHelperMessage('collections', 'submission_use_recaptcha')" />
                                        <p 
                                                v-if="form.submission_use_recaptcha == 'yes'" 
                                                v-html="$i18n.getWithVariables('info_recaptcha_link_%s', [ reCAPTCHASettingsPagePath ])" />        
                                    </b-field>
                                    

                                </div>
                            </transition>

                        </div>
                    </transition>

                    <!-- Hook for extra Form options -->
                    <template v-if="hasEndLeftForm">  
                        <form
                                id="form-collection-end-left" 
                                ref="form-collection-end-left"
                                class="form-hook-region"
                                v-html="getEndLeftForm" />
                    </template>

                </div>
                <div class="column is-5-widescreen is-full-desktop">

                    <div class="two-thirds-layout-options">

                        <!-- Slug -------------------------------- --> 
                        <b-field
                                :addons="false" 
                                :label="$i18n.get('label_slug')"
                                :type="editFormErrors['slug'] != undefined ? 'is-danger' : ''" 
                                :message="isUpdatingSlug ? $i18n.get('info_validating_slug') : (editFormErrors['slug'] != undefined ? editFormErrors['slug'] : '')">
                            <help-button 
                                    :title="$i18n.getHelperTitle('collections', 'slug')" 
                                    :message="$i18n.getHelperMessage('collections', 'slug')" />
                            <b-input
                                    id="tainacan-text-slug"
                                    v-model="form.slug"
                                    :disabled="isUpdatingSlug"
                                    :loading="isUpdatingSlug"
                                    @update:model-value="updateSlug"
                                    @focus="clearErrors('slug')" />
                        </b-field>

                        <!-- Status -------------------------------- --> 
                        <b-field
                                :addons="false" 
                                :label="$i18n.get('label_status')"
                                :type="editFormErrors['status'] != undefined ? 'is-danger' : ''" 
                                :message="editFormErrors['status'] != undefined ? editFormErrors['status'] : ''">
                            <help-button
                                    :title="$i18n.getHelperTitle('collections', 'status')"
                                    :message="$i18n.getHelperMessage('collections', 'status')" />
                            <b-dropdown
                                    ref="item-edition-status-dropdown"
                                    aria-role="list"
                                    class="item-edition-status-dropdown"
                                    position="is-bottom-left"
                                    :triggers="[ 'click' ]"
                                    :disabled="collection.status === 'auto-draft' || ( editFormErrors['status'] && (form.status == 'publish' || form.status == 'private' || form.status == 'pending' ) )"
                                    max-height="300px">
                                <template #trigger>
                                    <button 
                                            :disabled="collection.status === 'auto-draft' || ( editFormErrors['status'] && (form.status == 'publish' || form.status == 'private' || form.status == 'pending' ) )"
                                            type="button"
                                            class="button is-outlined"
                                            :class="{ 'disabled': collection.status === 'auto-draft' || ( editFormErrors['status'] && (form.status == 'publish' || form.status == 'private' || form.status == 'pending' ) ) }"
                                            style="width: auto">
                                        <span class="icon has-text-gray">
                                            <i 
                                                    class="tainacan-icon tainacan-icon-18px"
                                                    :class="$statusHelper.getIcon(form.status)" />
                                        </span>
                                        <template v-if="form.status !== 'auto-draft' && $statusHelper.getStatuses().find(aStatusObject => aStatusObject.slug == form.status)">
                                            {{ $statusHelper.getStatuses().find(aStatusObject => aStatusObject.slug == form.status).name }}
                                        </template>
                                        <template v-else-if="form.status === 'auto-draft'">
                                            {{ $i18n.get('status_auto-draft') }}
                                        </template>
                                        <span 
                                                style="margin-left: 0.5em;"
                                                class="icon is-small">
                                            <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-arrowdown" />
                                        </span>
                                    </button>
                                </template>
                                <b-dropdown-item 
                                        v-for="(statusOption, index) of $statusHelper.getStatuses().filter((status) => status.slug != 'draft')"
                                        :key="index"
                                        aria-role="listitem"
                                        @click="form.status = statusOption.slug">
                                    <span class="icon has-text-gray">
                                        <i 
                                                class="tainacan-icon tainacan-icon-18px"
                                                :class="$statusHelper.getIcon(statusOption.slug)" />
                                    </span>
                                    {{ statusOption.name }}
                                    <br>
                                    <small 
                                            v-if="$statusHelper.hasDescription(statusOption.slug)"
                                            class="is-small"
                                            style="margin-left: 2px;">
                                        {{ $statusHelper.getDescription(statusOption.slug) }}
                                    </small>
                                </b-dropdown-item>
                            </b-dropdown>
                        </b-field>

                    </div>  

                    <!-- Hook for extra Form options -->
                    <template v-if="hasBeginRightForm">  
                        <form 
                                id="form-collection-begin-right"
                                class="form-hook-region"
                                v-html="getBeginRightForm" />
                    </template>

                    <!-- Image thumbnail & Header Image -------------------------------- --> 
                    <b-field 
                            id="header-and-thumbnail-container"
                            :addons="false">
                        <label class="label">
                            {{ $i18n.get('label_thumbnail') }} & {{ $i18n.get('label_header_image') }}
                            <help-button 
                                    :title="$i18n.get('label_thumbnail') + ' & ' + $i18n.get('label_header_image')" 
                                    :message="$i18n.get('info_collection_thumbnail_and_header')" />
                        </label>

                        <!-- Header Image -------------------------------- --> 
                        <div class="header-field">
                            <figure class="image">
                                <span 
                                        v-if="collection.header_image == undefined || collection.header_image == false"
                                        class="image-placeholder">{{ $i18n.get('label_empty_header_image') }}</span>
                                <img  
                                        :alt="$i18n.get('label_thumbnail')" 
                                        :src="(collection.header_image == undefined || collection.header_image == false) ? headerPlaceholderPath : collection.header_image">
                            </figure>
                            <div class="header-buttons-row">
                                <a 
                                        id="button-edit-header-image"
                                        class="button is-rounded is-secondary" 
                                        :aria-label="$i18n.get('label_button_edit_header_image')"
                                        @click="headerImageMediaFrame.openFrame($event)">
                                    <span 
                                            v-tooltip="{
                                                content: $i18n.get('edit'),
                                                autoHide: true,
                                                placement: 'bottom',
                                                popperClass: ['tainacan-tooltip', 'tooltip']
                                            }"
                                            class="icon">
                                        <i class="tainacan-icon tainacan-icon-edit" />
                                    </span>
                                </a>
                                <a 
                                        id="button-delete-header-image"
                                        class="button is-rounded is-secondary" 
                                        :aria-label="$i18n.get('label_button_delete_thumb')" 
                                        @click="deleteHeaderImage()">
                                    <span 
                                            v-tooltip="{
                                                content: $i18n.get('delete'),
                                                autoHide: true,
                                                placement: 'bottom',
                                                popperClass: ['tainacan-tooltip', 'tooltip']
                                            }"
                                            class="icon">
                                        <i class="tainacan-icon tainacan-icon-delete" />
                                    </span>
                                </a>
                            </div>     
                        </div>

                        <!-- Thumbnail -------------------------------- --> 
                        <div class="thumbnail-field">
                            <file-item
                                    v-if="collection.thumbnail != undefined && ((collection.thumbnail['tainacan-medium'] != undefined && collection.thumbnail['tainacan-medium'] != false) || (collection.thumbnail.medium != undefined && collection.thumbnail.medium != false))"
                                    :show-name="false"
                                    :modal-on-click="true"
                                    :size="146"
                                    :file="{ 
                                        media_type: 'image', 
                                        thumbnails: { 'tainacan-medium': [ $thumbHelper.getSrc(collection['thumbnail'], 'tainacan-medium') ] },
                                        title: $i18n.get('label_thumbnail'),
                                        description: `<img alt='` + $i18n.get('label_thumbnail') + `' src='` + $thumbHelper.getSrc(collection['thumbnail'], 'full') + `'/>` 
                                    }" />
                            <figure 
                                    v-if="collection.thumbnail == undefined || ((collection.thumbnail.medium == undefined || collection.thumbnail.medium == false) && (collection.thumbnail['tainacan-medium'] == undefined || collection.thumbnail['tainacan-medium'] == false))"
                                    class="image">
                                <span class="image-placeholder">{{ $i18n.get('label_empty_thumbnail') }}</span>
                                <img  
                                        :alt="$i18n.get('label_thumbnail')" 
                                        :src="$thumbHelper.getEmptyThumbnailPlaceholder()">
                            </figure>
                            <div class="thumbnail-buttons-row">
                                <a 
                                        id="button-edit-thumbnail"
                                        class="button is-rounded is-secondary" 
                                        :aria-label="$i18n.get('label_button_edit_thumb')"
                                        @click.prevent="thumbnailMediaFrame.openFrame($event)">
                                    <span 
                                            v-tooltip="{
                                                content: $i18n.get('edit'),
                                                autoHide: true,
                                                placement: 'bottom',
                                                popperClass: ['tainacan-tooltip', 'tooltip']  
                                            }"
                                            class="icon">
                                        <i class="tainacan-icon tainacan-icon-edit" />
                                    </span>
                                </a>
                                <a 
                                        id="button-delete-header-image"
                                        class="button is-rounded is-secondary" 
                                        :aria-label="$i18n.get('label_button_delete_thumb')" 
                                        @click="deleteThumbnail()">
                                    <span 
                                            v-tooltip="{
                                                content: $i18n.get('delete'),
                                                autoHide: true,
                                                placement: 'bottom',
                                                popperClass: ['tainacan-tooltip', 'tooltip']  
                                            }"
                                            class="icon">
                                        <i class="tainacan-icon tainacan-icon-delete" />
                                    </span>
                                </a>
                            </div>
                        </div>
                    </b-field>

                    <!-- Cover Page -------------------------------- --> 
                    <b-field
                            :addons="false" 
                            :label="$i18n.get('label_cover_page')"
                            :type="editFormErrors['cover_page_id'] != undefined ? 'is-danger' : ''" 
                            :message="editFormErrors['cover_page_id'] != undefined ? editFormErrors['cover_page_id'] : ''">
                        &nbsp;
                        <b-switch
                                id="tainacan-checkbox-cover-page" 
                                v-model="form.enable_cover_page"
                                size="is-small" 
                                true-value="yes"
                                false-value="no" />
                        <help-button 
                                :title="$i18n.getHelperTitle('collections', 'cover_page_id')" 
                                :message="$i18n.getHelperMessage('collections', 'cover_page_id')" />
                        <template v-if="form.enable_cover_page == 'yes'">
                            <b-autocomplete
                                    v-if="coverPage == undefined || coverPage.title == undefined"
                                    id="tainacan-text-cover-page"
                                    v-model="coverPageTitle"
                                    :placeholder="$i18n.get('instruction_cover_page')"
                                    :data="coverPages"
                                    :loading="isFetchingPages"
                                    check-infinite-scroll
                                    @select="onSelectCoverPage($event)"
                                    @update:model-value="fecthCoverPages"
                                    @focus="clearErrors('cover_page_id')"
                                    @infinite-scroll="fetchMoreCoverPages">
                                <template #default="props">
                                    {{ props.option.title.rendered }}
                                </template>
                                <template #empty>
                                    {{ $i18n.get('info_no_page_found') }}
                                </template>
                            </b-autocomplete>
    
                            <div 
                                    v-if="coverPage != undefined && coverPage.title != undefined"
                                    class="control selected-cover-page">
                                <span v-html="coverPage.title.rendered" />
                                <span class="selected-cover-page-control">
                                    <a 
                                            target="_blank"
                                            @click.prevent="removeCoverPage()">
                                        <span 
                                                v-tooltip="{
                                                    content: $i18n.get('remove_value'),
                                                    autoHide: true,
                                                    placement: 'bottom',
                                                    popperClass: ['tainacan-tooltip', 'tooltip']  
                                                }"
                                                class="icon is-small">
                                            <i class="tainacan-icon tainacan-icon-close" />
                                        </span>
                                    </a>
                                </span>
                            </div>
                            <span 
                                    :class="{'disabled': form.enable_cover_page != 'yes' || coverPage == undefined || coverPage.title == undefined}"
                                    class="selected-cover-page-buttons">
                                <a 
                                        target="_blank" 
                                        :href="coverPage.link">
                                    <span 
                                            v-tooltip="{
                                                content: $i18n.get('see'),
                                                autoHide: true,
                                                placement: 'bottom',
                                                popperClass: ['tainacan-tooltip', 'tooltip']
                                            }"
                                            class="icon is-small">
                                        <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-openurl" />
                                    </span>
                                </a>
                                &nbsp;&nbsp;
                                <a 
                                        target="blank" 
                                        :href="coverPageEditPath">
                                    <span 
                                            v-tooltip="{
                                                content: $i18n.get('edit'),
                                                autoHide: true,
                                                placement: 'bottom',
                                                popperClass: ['tainacan-tooltip', 'tooltip']
                                            }"
                                            class="icon is-small">
                                        <i class="tainacan-icon tainacan-icon-edit" />
                                    </span>
                                </a>
                            </span>
                            <br>
                            <a
                                    class="add-link"  
                                    style="font-size: 0.875em;" 
                                    :class="{'disabled': form.enable_cover_page != 'yes'}"
                                    target="_blank"  
                                    :href="newPagePath">
                                <span class="icon is-small">
                                    <i class="tainacan-icon tainacan-icon-add" />
                                </span>
                                {{ $i18n.get('label_create_new_page') }}
                            </a>            
                        </template>            
                    </b-field>

                    <!-- Parent Collection -------------------------------- --> 
                    <!-- DISABLED IN 0.18 AS WE DISCUSS BETTER IMPLEMENTATION FOR COLLECTIONS HIERARCHY -->
                    <!-- <b-field
                            :addons="false" 
                            :label="$i18n.get('label_parent_collection')"
                            :type="editFormErrors['parent'] != undefined ? 'is-danger' : ''" 
                            :message="editFormErrors['parent'] != undefined ? editFormErrors['parent'] : ''">
                        <help-button 
                                :title="$i18n.getHelperTitle('collections', 'parent')" 
                                :message="$i18n.getHelperMessage('collections', 'parent')"/>
                        <b-select
                                expanded
                                id="tainacan-select-parent"
                                v-model="form.parent"
                                @focus="clearErrors('parent')"
                                :loading="isFetchingCollections"
                                :placeholder="$i18n.get('instruction_select_a_parent_collection')">
                            <option value="0">{{ $i18n.get('label_no_parent_collection') }}</option>
                            <option
                                    v-if="collection.id != anotherCollection.id"
                                    v-for="anotherCollection of collections"
                                    :key="anotherCollection.id"
                                    :value="anotherCollection.id">{{ anotherCollection.name }}
                            </option>
                        </b-select>
                    </b-field> -->


                    <!-- Hook for extra Form options -->
                    <template v-if="hasEndRightForm">  
                        <form 
                                id="form-collection-end-right"
                                class="form-hook-region"
                                v-html="getEndRightForm" />
                    </template>
                </div>

            </div>

            <!-- Form submit -------------------------------- --> 
            <footer class="footer field is-grouped form-submit">
                <div class="control">
                    <button
                            id="button-cancel-collection-creation"
                            class="button is-outlined"
                            type="button"
                            @click="cancelBack">{{ $i18n.get('cancel') }}</button>
                </div>
                <p class="help is-danger">
                    {{ formErrorMessage }}
                </p>
                <div 
                        style="margin-left: auto;"
                        class="control is-hidden-mobile">
                    <button
                            v-if="isNewCollection && $userCaps.hasCapability('tnc_rep_edit_metadata') && !fromImporter"
                            id="button-submit-goto-metadata"
                            class="button is-secondary"
                            @click.prevent="onSubmit('metadata')">{{ $i18n.get('label_save_goto_metadata') }}</button>
                </div>
                <div class="control is-hidden-mobile">
                    <button
                            v-if="isNewCollection && $userCaps.hasCapability('tnc_rep_edit_metadata') && !fromImporter"
                            id="button-submit-goto-filter"
                            class="button is-secondary"
                            @click.prevent="onSubmit('filters')">{{ $i18n.get('label_save_goto_filter') }}</button>
                </div>
                <div class="control">
                    <button
                            id="button-submit-collection-creation"
                            class="button is-success"
                            @click.prevent="onSubmit('items')">{{ $i18n.get('finish') }}</button>
                </div>
            </footer>
        </form>

        <div v-if="!isLoading && ((isNewCollection && !$userCaps.hasCapability('tnc_rep_edit_collections')) || (!isNewCollection && collection && collection.current_user_can_edit != undefined && collection.current_user_can_edit == false))">
            <section class="section">
                <div class="content has-text-grey has-text-centered">
                    <p>
                        <span class="icon">
                            <i class="tainacan-icon tainacan-icon-30px tainacan-icon-items" />
                        </span>
                    </p>
                    <p>{{ $i18n.get('info_can_not_edit_collection') }}</p>
                </div>
            </section>
        </div>

        <b-loading 
                v-model="isLoading" 
                :can-cancel="false" />
    </div>
</template>

<script>
import { nextTick } from 'vue';
import { mapGetters, mapActions } from 'vuex';
import wpMediaFrames from '../../js/wp-media-frames';
import FileItem from '../other/file-item.vue';
import { permalinkGetter, formHooks } from '../../js/mixins';

export default {
    name: 'CollectionEditionForm',
    components: {
        FileItem
    },
    mixins: [ permalinkGetter, formHooks ],
    data(){
        return {
            collectionId: [String, Number],
            collection: null,
            isLoading: false,
            form: {
                name: '',
                status: '',
                description: '',
                slug: '',
                enable_cover_page: '',	
                thumbnail: '',
                header_image: '',
                files:[],
                enabled_view_modes: [],
                default_view_mode: [],
                default_order: 'ASC',
                default_orderby: 'date',
                allow_comments: 'closed',
                allow_item_author_editing: 'no',
                allow_item_slug_editing: 'no',
                allows_submission: 'no',
                submission_default_status: 'draft',
                submission_anonymous_user: 'no',
                hide_items_thumbnail_on_lists: '',
                submission_use_recaptcha: 'no',
                item_enabled_document_types: {
                    attachment: {
                        enabled: 'yes',
                        label: this.$i18n.get( 'File', 'tainacan'),
                        icon: 'attachments'
                    },
                    url: {
                        enabled: 'yes',
                        label: this.$i18n.get( 'URL', 'tainacan'),
                        icon: 'url'
                    },
                    text: {
                        enabled: 'yes',
                        label: this.$i18n.get( 'Text', 'tainacan'),
                        icon: 'text'
                    }
                },
                item_publication_label: this.$i18n.get('label_publication_data'),
                item_document_label: this.$i18n.get( 'Document', 'tainacan' ),
                item_thumbnail_label: this.$i18n.get( 'Thumbnail', 'tainacan' ),
                item_enable_thumbnail: 'yes',
                item_attachment_label: this.$i18n.get( 'Attachments', 'tainacan' ),
                item_enable_attachments: 'yes',
                item_enable_metadata_focus_mode: 'yes',
                item_enable_metadata_required_filter: 'yes',
                item_enable_metadata_searchbar: 'yes',
                item_enable_metadata_collapses: 'yes',
                item_enable_metadata_enumeration: 'yes',
                collection_taxonomies: []
            },
            thumbnail: {},
            cover: {},
            isFetchingPages: false,
            coverPages: [],
            coverPagesSearchQuery: '',
            coverPagesSearchPage: 0,
            coverPage: '',
            coverPageTitle: '',
            coverPageEditPath: '',
            editFormErrors: {},
            formErrorMessage: '',
            isNewCollection: false,
            isMapped: false,
            mapper: false,
            headerPlaceholderPath: tainacan_plugin.base_url + '/assets/images/placeholder_rectangle.png',
            //collections: [],              DISABLED IN 0.18 AS WE DISCUSS BETTER IMPLEMENTATION FOR COLLECTIONS HIERARCHY
            //isFetchingCollections: true,  DISABLED IN 0.18 AS WE DISCUSS BETTER IMPLEMENTATION FOR COLLECTIONS HIERARCHY
            thumbnailMediaFrame: undefined,
            headerImageMediaFrame: undefined,
            viewModesList: [],
            fromImporter: '',
            repositoryEnabledViewModes: tainacan_plugin.enabled_view_modes,
            reCAPTCHASettingsPagePath: tainacan_plugin.admin_url + '?page=tainacan_item_submission',
            newPagePath: tainacan_plugin.admin_url + 'post-new.php?post_type=page',
            isUpdatingSlug: false,
            entityName: 'collection',
            metadataSearchCancel: undefined,
            isLoadingMetadata: true,
            sortingMetadata: [],
            localDefaultOrderBy: 'date',
            showCollectionsTaxonomiesOptions: false,
            showItemsListOptions: true,
            showItemEditionFormOptions: false,
            showItemSubmissionOptions: false,
            isLoadingCollectionTaxonomies: false
        }
    },
    computed: {
        ...mapGetters('metadata', {
            'metadata': 'getMetadata'
        }),
        ...mapGetters('collection', [
            'getCollectionTaxonomies'
        ]),
        validDefaultViewModes() {
            return Array.isArray(this.form.enabled_view_modes) ? this.form.enabled_view_modes.filter((aViewMode) => this.registeredAndNotDisabledViewModes[aViewMode] != undefined && this.registeredAndNotDisabledViewModes[aViewMode].full_screen == false ) : [];
        },
        registeredAndNotDisabledViewModes() {
            let registered = tainacan_plugin.registered_view_modes;
            for (let key in registered) {
                if ( tainacan_plugin.enabled_view_modes.indexOf(key) == -1 )
                    delete registered[key];
            }
            return registered;
        },
    },
    watch: {
        'form.hide_items_thumbnail_on_lists' (newValue) {
            if (newValue == 'yes') {
                const validViewModes = {};
                Object.keys(tainacan_plugin.registered_view_modes).forEach((viewModeKey) => {
                    if (!tainacan_plugin.registered_view_modes[viewModeKey]['requires_thumbnail']) 
                        validViewModes[viewModeKey] = tainacan_plugin.registered_view_modes[viewModeKey];
                });
                this.registeredAndNotDisabledViewModes = validViewModes;
                
                this.form.enabled_view_modes = this.form.enabled_view_modes.filter((aViewMode) => this.registeredAndNotDisabledViewModes[aViewMode] != undefined );

                this.updateDefaultViewModeBasedOnEnabled();           
                
                // Setting initial view mode
                if (this.$userPrefs.get('admin_view_mode_' + this.collectionId) == 'masonry' || this.$userPrefs.get('admin_view_mode_' + this.collectionId) == 'grid')
                    this.$userPrefs.set('admin_view_mode_' + this.collectionId, 'table');

            } else {
                this.registeredAndNotDisabledViewModes = tainacan_plugin.registered_view_modes;
            }
        },
        localDefaultOrderBy(newValue) {
            if (this.sortingMetadata && this.sortingMetadata.length && newValue) {
                let sortingMetadatumIndex = this.sortingMetadata.findIndex(aMetadatum => aMetadatum.id == newValue);
                if (sortingMetadatumIndex >= 0)
                    this.form.default_orderby = this.$orderByHelper.getOrderByForMetadatum(this.sortingMetadata[sortingMetadatumIndex].metadata_type ? this.sortingMetadata[sortingMetadatumIndex] : this.sortingMetadata[sortingMetadatumIndex].id);
            }
        }
    },
    mounted(){

        if (this.$route.query.fromImporter != undefined) 
            this.fromImporter = this.$route.query.fromImporter;

        if (this.$route.path.split("/").pop() == "new") {
            this.createNewCollection();
            this.isNewCollection = true;
        } else if (this.$route.path.split("/").pop() == "settings") {

            this.isLoading = true;

            // Obtains current Collection ID from URL
            this.pathArray = this.$route.path.split("/").reverse(); 

            this.collectionId = this.pathArray[1];

            this.fetchCollection(this.collectionId).then(res => {
                this.collection = res;

                // Initializes Media Frames now that collectonId exists
                this.initializeMediaFrames();
                nextTick()
                    .then(() => {
                        // Fills hook forms with it's real values 
                        this.updateExtraFormData(this.collection);
                    });
               
  
                // Fill this.form data with current data.
                this.form.name = this.collection.name;
                this.form.description = this.collection.description;
                this.form.slug = this.collection.slug;
                this.form.status = this.collection.status;
                this.form.enable_cover_page = this.collection.enable_cover_page;
                this.form.cover_page_id = this.collection.cover_page_id;
                this.form.parent = this.collection.parent;
                this.form.default_view_mode = this.collection.default_view_mode;
                this.form.enabled_view_modes = JSON.parse(JSON.stringify(this.collection.enabled_view_modes.reduce((result, viewMode) => { typeof viewMode == 'string' ? result.push(viewMode) : null; return result }, [])));
                this.form.default_order = this.collection.default_order;
                this.form.default_orderby = this.collection.default_orderby;
                this.form.allow_comments = this.collection.allow_comments;
                this.form.allow_item_slug_editing = this.collection.allow_item_slug_editing;
                this.form.allow_item_author_editing = this.collection.allow_item_author_editing;
                this.form.allows_submission = this.collection.allows_submission;
                this.form.submission_anonymous_user = this.collection.submission_anonymous_user;
                this.form.submission_default_status = this.collection.submission_default_status;
                this.form.submission_use_recaptcha = this.collection.submission_use_recaptcha;
                this.form.hide_items_thumbnail_on_lists = this.collection.hide_items_thumbnail_on_lists;
                this.form.item_enabled_document_types = this.collection.item_enabled_document_types;
                this.form.item_publication_label = this.collection.item_publication_label;
                this.form.item_document_label = this.collection.item_document_label;
                this.form.item_thumbnail_label = this.collection.item_thumbnail_label;
                this.form.item_enable_thumbnail = this.collection.item_enable_thumbnail;
                this.form.item_attachment_label = this.collection.item_attachment_label;
                this.form.item_enable_attachments = this.collection.item_enable_attachments;
                this.form.item_enable_metadata_focus_mode = this.collection.item_enable_metadata_focus_mode;
                this.form.item_enable_metadata_required_filter = this.collection.item_enable_metadata_required_filter;
                this.form.item_enable_metadata_searchbar = this.collection.item_enable_metadata_searchbar;
                this.form.item_enable_metadata_collapses = this.collection.item_enable_metadata_collapses;
                this.form.item_enable_metadata_enumeration = this.collection.item_enable_metadata_enumeration;
                this.form.collection_taxonomies = this.collection.collection_taxonomies;

                // Generates CoverPage from current cover_page_id info
                if (this.form.cover_page_id != undefined && this.form.cover_page_id != '') {
                    
                    this.isFetchingPages = true;
                    
                    this.fetchPage(this.form.cover_page_id)
                    .then((page) => {
                        this.coverPage = page;
                        this.coverPageTitle = this.coverPage.title.rendered;
                        this.coverPageEditPath = tainacan_plugin.admin_url + '/post.php?post=' + page.id + '&action=edit';
                        this.isFetchingPages = false;
                    })
                    .catch((error) => {
                        this.$console.error(error);
                        this.isFetchingPages = false;
                    }); 
                }

                // Generates options for parent collection
                // DISABLED IN 0.18 AS WE DISCUSS BETTER IMPLEMENTATION FOR COLLECTIONS HIERARCHY
                // this.isFetchingCollections = true;
                // this.fetchAllCollectionNames()
                //     .then((resp) => {
                //         resp.request.then((collections) => {
                //             this.collections = collections;
                //             this.isFetchingCollections = false;
                //         })
                //         .catch((error) => {
                //             this.$console.error(error);
                //             this.isFetchingCollections = false;
                //         }); 
                //     })
                //     .catch(() => {
                //         this.isFetchingCollections = false;
                //     }); 

                // Prepares list of metadata available for sorting
                this.getMetadataForSorting();

                this.isLoading = false; 
            });
        } else {
            let temporaryPath = this.$route.fullPath.split("/");
            let mapper = temporaryPath.pop();
            if ( temporaryPath.pop() == 'new' ) {
                this.isNewCollection = true;
                this.isMapped = true;
                this.mapper = mapper;
                this.createNewCollection();
            }
        }

        this.fetchCollectionTaxonomies({ termParent: 0, termPerPage: tainacan_plugin.api_max_items_per_page })
            .then(() => {
                this.isLoadingCollectionTaxonomies = false;
            })
            .catch(() => {
                this.isLoadingCollectionTaxonomies = false;
            });
    },
    methods: {
        ...mapActions('collection', [
            'sendCollection',
            'updateCollection',
            'fetchCollection',
            'sendAttachment',
            'updateThumbnail',
            'updateHeaderImage',
            'fetchPages',
            'fetchPage',
            'fetchAllCollectionNames',
            'fetchCollectionTaxonomies',
            'updateCollectionTaxonomyValues'
        ]),
        ...mapActions('metadata', [
            'fetchMetadata'
        ]),
        updateSlug: _.debounce(function() {
            if (!this.form.name || this.form.name.length <= 0)
                return;

            this.isUpdatingSlug = true;

            this.getSamplePermalink(this.collectionId, this.form.name, this.form.slug)
                .then((res) => {
                    this.form.slug = res.data.slug;

                    this.isUpdatingSlug = false;
                    this.formErrorMessage = '';
                    this.editFormErrors = {};
                })
                .catch(errors => {
                    this.$console.error(errors);

                    this.isUpdatingSlug = false;
                });
        }, 1000),
        onSubmit(goTo) {
           
            this.isLoading = true;

            let data = { 
                collection_id: this.collectionId, 
                name: this.form.name, 
                description: this.form.description,
                enable_cover_page: this.form.enable_cover_page, 
                cover_page_id: this.form.cover_page_id,
                slug: this.form.slug, 
                status: this.form.status,
                parent: this.form.parent,
                enabled_view_modes: this.form.enabled_view_modes,
                default_view_mode: this.form.default_view_mode,
                default_order: this.form.default_order,
                default_orderby: this.form.default_orderby,
                allows_submission: this.form.allows_submission,
                submission_anonymous_user: this.form.submission_anonymous_user,
                submission_default_status: this.form.submission_default_status,
                submission_use_recaptcha: this.form.submission_use_recaptcha,
                allow_comments: this.form.allow_comments,
                allow_item_slug_editing: this.form.allow_item_slug_editing,
                allow_item_author_editing: this.form.allow_item_author_editing,
                hide_items_thumbnail_on_lists: this.form.hide_items_thumbnail_on_lists,
                item_enabled_document_types: this.form.item_enabled_document_types,
                item_publication_label: this.form.item_publication_label,
                item_document_label: this.form.item_document_label,
                item_thumbnail_label: this.form.item_thumbnail_label,
                item_enable_thumbnail: this.form.item_enable_thumbnail,
                item_attachment_label: this.form.item_attachment_label,
                item_enable_attachments: this.form.item_enable_attachments,
                item_enable_metadata_focus_mode: this.form.item_enable_metadata_focus_mode,
                item_enable_metadata_required_filter: this.form.item_enable_metadata_required_filter,
                item_enable_metadata_searchbar: this.form.item_enable_metadata_searchbar,
                item_enable_metadata_collapses: this.form.item_enable_metadata_collapses,
                item_enable_metadata_enumeration: this.form.item_enable_metadata_enumeration,
            };
            this.fillExtraFormData(data);

            this.updateCollection({collection_id: this.collectionId, collection: data })
                .then(updatedCollection => {    
                    
                    this.collection = updatedCollection;

                    // Fills hook forms with it's real values 
                    this.updateExtraFormData(this.collection);
                    
                    // Fill this.form data with current data.
                    this.form.name = this.collection.name;
                    this.form.slug = this.collection.slug;
                    this.form.description = this.collection.description;
                    this.form.status = this.collection.status;
                    this.form.cover_page_id = this.collection.cover_page_id;
                    this.form.enable_cover_page = this.collection.enable_cover_page;
                    this.form.enabled_view_modes = this.collection.enabled_view_modes.map((viewMode) => viewMode.viewMode);
                    this.form.default_view_mode = this.collection.default_view_mode;
                    this.form.default_order = this.collection.default_order;
                    this.form.default_orderby = this.collection.default_orderby;
                    this.form.allow_comments = this.collection.allow_comments;
                    this.form.allow_item_slug_editing = this.collection.allow_item_slug_editing;
                    this.form.allow_item_author_editing = this.collection.allow_item_author_editing;
                    this.form.allows_submission = this.collection.allows_submission;
                    this.form.submission_anonymous_user = this.collection.submission_anonymous_user;
                    this.form.submission_default_status = this.collection.submission_default_status;
                    this.form.submission_use_recaptcha = this.collection.submission_use_recaptcha;
                    this.form.hide_items_thumbnail_on_lists = this.collection.hide_items_thumbnail_on_lists;
                    this.form.item_enabled_document_types = this.collection.item_enabled_document_types;
                    this.form.item_publication_label = this.collection.item_publication_label;
                    this.form.item_document_label = this.collection.item_document_label;
                    this.form.item_thumbnail_label = this.collection.item_thumbnail_label;
                    this.form.item_enable_thumbnail = this.collection.item_enable_thumbnail;
                    this.form.item_attachment_label = this.collection.item_attachment_label;
                    this.form.item_enable_attachments = this.collection.item_enable_attachments;
                    this.form.item_enable_metadata_focus_mode = this.collection.item_enable_metadata_focus_mode;
                    this.form.item_enable_metadata_required_filter = this.collection.item_enable_metadata_required_filter;
                    this.form.item_enable_metadata_searchbar = this.collection.item_enable_metadata_searchbar;
                    this.form.item_enable_metadata_collapses = this.collection.item_enable_metadata_collapses;
                    this.form.item_enable_metadata_enumeration = this.collection.item_enable_metadata_enumeration;
                    
                    this.isLoading = false;
                    this.formErrorMessage = '';
                    this.editFormErrors = {};

                    if (this.fromImporter) {
                        this.$router.go(-1);
                    } else {
                        if (goTo == 'metadata')
                            this.$router.push(this.$routerHelper.getCollectionMetadataPath(this.collectionId));
                        else if (goTo == 'filters')
                            this.$router.push(this.$routerHelper.getCollectionFiltersPath(this.collectionId));
                        else
                            this.$router.push(this.$routerHelper.getCollectionPath(this.collectionId));
                    }
                })
                .catch((errors) => {
                    
                    for (let error of errors.errors) {     
                        for (let attribute of Object.keys(error))
                            this.editFormErrors[attribute] = error[attribute];
                    }
                    this.formErrorMessage = errors.error_message;

                    this.isLoading = false;
                });     
        },
        createNewCollection() {
            // Puts loading on Draft Collection creation
            this.isLoading = true;

            // Creates draft Collection
            let data = { name: '', description: '', status: 'auto-draft', mapper: (this.isMapped && this.mapper != false ? this.mapper : false ) };
            this.fillExtraFormData(data);
            
            this.sendCollection(data).then(res => {

                this.collectionId = res.id;
                this.collection = res;

                // Initializes Media Frames now that collectonId exists
                this.initializeMediaFrames();
        
                // Fill this.form data with current data.
                this.form.name = this.collection.name;
                this.form.description = this.collection.description;
                this.form.enable_cover_page = this.collection.enable_cover_page;
                this.form.cover_page_id = this.collection.cover_page_id;
                this.form.slug = this.collection.slug;
                this.form.parent = this.collection.parent;
                this.form.default_view_mode = this.collection.default_view_mode;
                this.form.default_order = this.collection.default_order;
                this.form.default_orderby = this.collection.default_orderby;
                this.form.enabled_view_modes = this.collection.enabled_view_modes;
                this.form.allow_comments = this.collection.allow_comments;
                this.form.allow_item_slug_editing = this.collection.allow_item_slug_editing;
                this.form.allow_item_author_editing = this.collection.allow_item_author_editing;
                this.form.allows_submission = this.collection.allows_submission;
                this.form.submission_anonymous_user = this.collection.submission_anonymous_user;
                this.form.submission_default_status = this.collection.submission_default_status;
                this.form.submission_use_recaptcha = this.collection.submission_use_recaptcha;
                this.form.hide_items_thumbnail_on_lists = this.collection.hide_items_thumbnail_on_lists;
                this.form.item_enabled_document_types = this.collection.item_enabled_document_types;
                this.form.item_publication_label = this.collection.item_publication_label;
                this.form.item_document_label = this.collection.item_document_label;
                this.form.item_thumbnail_label = this.collection.item_thumbnail_label;
                this.form.item_enable_thumbnail = this.collection.item_enable_thumbnail;
                this.form.item_attachment_label = this.collection.item_attachment_label;
                this.form.item_enable_attachments = this.collection.item_enable_attachments;
                this.form.item_enable_metadata_focus_mode = this.collection.item_enable_metadata_focus_mode;
                this.form.item_enable_metadata_required_filter = this.collection.item_enable_metadata_required_filter;
                this.form.item_enable_metadata_searchbar = this.collection.item_enable_metadata_searchbar;
                this.form.item_enable_metadata_collapses = this.collection.item_enable_metadata_collapses;
                this.form.item_enable_metadata_enumeration = this.collection.item_enable_metadata_enumeration;

                // Pre-fill status with publish to incentivate it
                this.form.status = 'publish';

                // Generates options for parent collection
                // DISABLED IN 0.18 AS WE DISCUSS BETTER IMPLEMENTATION FOR COLLECTIONS HIERARCHY
                // this.isFetchingCollections = true;
                // this.fetchAllCollectionNames()
                //     .then((resp) => {
                //         resp.request.then((collections) => {
                //             this.collections = collections;
                //             this.isFetchingCollections = false;
                //         })
                //         .catch((error) => {
                //             this.$console.error(error);
                //             this.isFetchingCollections = false;
                //         });
                //     })
                //     .catch((error) => {
                //         this.$console.error(error);
                //         this.isFetchingCollections = false;
                //     });

                // Prepares list of metadata available for sorting
                this.getMetadataForSorting();

                this.isLoading = false;
                
            })
            .catch((error) => {
                this.$console.error(error);
                this.isLoading = false;
            });
        },
        clearErrors(attribute) {
            this.editFormErrors[attribute] = undefined;
        },
        cancelBack(){
            if (this.fromImporter)
                this.$router.go(-1);
            else
                this.$router.push(this.$routerHelper.getCollectionsPath());
        },
        updateViewModeslist(viewMode) {
        
            let index = this.form.enabled_view_modes.findIndex(aViewMode => aViewMode == viewMode);
            if (index > -1)
                this.form.enabled_view_modes.splice(index, 1);
            else    
                this.form.enabled_view_modes.push(viewMode);

            this.updateDefaultViewModeBasedOnEnabled();
        },
        updateDefaultViewModeBasedOnEnabled() {
            // Puts a valid view mode as default if the current one is not in the list anymore.
            if (!this.checkIfViewModeEnabled(this.form.default_view_mode)) {
                const validViewModeIndex = this.form.enabled_view_modes.findIndex((aViewMode) => (this.registeredAndNotDisabledViewModes[aViewMode] && !this.registeredAndNotDisabledViewModes[aViewMode].full_screen));
                if (validViewModeIndex >= 0)
                    this.form.default_view_mode = this.form.enabled_view_modes[validViewModeIndex];
            }
        },
        checkIfViewModeEnabled(viewMode) {
            return this.form.enabled_view_modes.includes(viewMode);
        },
        fecthCoverPages: _.debounce(function(search) {

            // String update
            if (search != this.coverPagesSearchQuery) {
                this.coverPagesSearchQuery = search;
                this.coverPages = [];
                this.coverPagesSearchPage = 1;
            } 
            
            // String cleared
            if (!search.length) {
                this.coverPagesSearchQuery = search;
                this.coverPages = [];
                this.coverPagesSearchPage = 1;
            }

            // No need to load more
            if (this.coverPagesSearchPage > 1 && this.coverPages.length > this.totalPages*12)
                return;

            this.isFetchingPages = true;
            this.fetchPages({ search: this.coverPagesSearchQuery, page: this.coverPagesSearchPage })
                .then((res) => {
                    if (res.pages) {
                        for (let page of res.pages)
                            this.coverPages.push(page); 
                    }
                    if (res.totalPages)
                        this.totalPages = res.totalPages;

                    this.coverPagesSearchPage++;
                    this.isFetchingPages = false;
                })
                .catch((error) => {
                    this.$console.error(error);
                    this.isFetchingPages = false;
                });
        }, 500),
        fetchMoreCoverPages: _.debounce(function () {
            this.fecthCoverPages(this.coverPagesSearchQuery)
        }, 250),
        onSelectCoverPage(selectedPage) { 
            this.form.cover_page_id = selectedPage.id; 
            this.coverPage = selectedPage;
            this.coverPageTitle = this.coverPage.title.rendered;
            this.coverPageEditPath = tainacan_plugin.admin_url + 'post.php?post=' + selectedPage.id + '&action=edit';
        },
        removeCoverPage() {
            this.coverPage = {};
            this.coverPageTitle = '';
            this.form.enable_cover_page = 'no';
            this.form.cover_page_id = '';
        },
        deleteThumbnail() {

            this.updateThumbnail({collectionId: this.collectionId, thumbnailId: 0})
                .then(() => {
                    this.collection.thumbnail = false;
                })
                .catch((error) => {
                    this.$console.error(error);
                });    
        },
        deleteHeaderImage() {

            this.updateHeaderImage({collectionId: this.collectionId, headerImageId: 0})
            .then(() => {
                this.collection.header_image = false;
            })
            .catch((error) => {
                this.$console.error(error);
            });    
        },
        initializeMediaFrames() {

            this.thumbnailMediaFrame = new wpMediaFrames.thumbnailControl(
                'my-thumbnail-media-frame', {
                    button_labels: {
                        frame_title: this.$i18n.get('instruction_select_collection_thumbnail'),
                        frame_button: this.$i18n.get('label_select_file'),
                    },
                    relatedPostId: this.collectionId,
                    onSave: (media) => {
                        this.updateThumbnail({
                            collectionId: this.collectionId, thumbnailId: media.id
                        })
                            .then((res) => {
                                this.collection.thumbnail = res.thumbnail;
                            })
                            .catch(error => this.$console.error(error));
                    }
                }
            );

            this.headerImageMediaFrame = new wpMediaFrames.headerImageControl(
                'my-header-image-media-frame', {
                    button_labels: {
                        frame_title: this.$i18n.get('instruction_select_collection_header_image'),
                        frame_button: this.$i18n.get('label_select_file'),
                    },
                    relatedPostId: this.collectionId,
                    onSave: (media) => {
                        this.updateHeaderImage({collectionId: this.collectionId, headerImageId: media.id})
                        .then((res) => {
                            this.collection.header_image = res.header_image;
                        })
                        .catch(error => this.$console.error(error));
                    }
                }
            );
        },
        getMetadataForSorting() {

            // Cancels previous Request
            if (this.metadataSearchCancel != undefined)
                this.metadataSearchCancel.cancel('Metadata search Canceled.');

            this.isLoadingMetadata = true;
            
            // Processing is done inside a local variable
            this.fetchMetadata({
                collectionId: this.collectionId,
                isContextEdit: false,
                includeControlMetadataTypes: true,
                metaquery: [{
                    key: 'metadata_type',
                    compare: 'NOT IN',
                    value: [ // Not every metadata can be used for sorting
                        'Tainacan\\Metadata_Types\\Core_Description',
                        'Tainacan\\Metadata_Types\\Taxonomy',
                        'Tainacan\\Metadata_Types\\Relationship',
                        'Tainacan\\Metadata_Types\\Compound',
                        'Tainacan\\Metadata_Types\\User',
                        'Tainacan\\Metadata_Types\\GeoCoordinate'
                    ]
                }]
            }).then((resp) => {
                    resp.request
                        .then(() => {
                            // Not every metadata can be used for sorting
                            this.sortingMetadata = JSON.parse(JSON.stringify(this.metadata));

                            // Adds creation date as it is the default
                            this.sortingMetadata.push({
                                name: this.$i18n.get('label_creation_date'),
                                metadata_type: undefined,
                                slug: 'date',
                                id: 'date'
                            });       
                            
                            // Updates localDefaultOrder variable that needs only the ID of the metadata
                            if (this.form.default_orderby.metakey)
                                this.localDefaultOrderBy =  this.form.default_orderby.metakey;
                            else {
                                if (this.form.default_orderby == 'title') {
                                    const localDefaultOrderByIndex = this.sortingMetadata.findIndex((aMetadatum) => aMetadatum.metadata_type == 'Tainacan\\Metadata_Types\\Core_Title');
                                    this.localDefaultOrderBy = localDefaultOrderByIndex >= 0 ? this.sortingMetadata[localDefaultOrderByIndex].id : 'title';
                                } else if (this.form.default_orderby == 'description') {
                                    const localDefaultOrderByIndex = this.sortingMetadata.findIndex((aMetadatum) => aMetadatum.metadata_type == 'Tainacan\\Metadata_Types\\Core_Description');
                                    this.localDefaultOrderBy = localDefaultOrderByIndex >= 0 ? this.sortingMetadata[localDefaultOrderByIndex].id : 'description';
                                } else {
                                    this.localDefaultOrderBy = this.form.default_orderby;
                                }
                            }

                            this.isLoadingMetadata = false;
                        })
                        .catch(() => {
                            this.isLoadingMetadata = false;
                        })
                    // Search Request Token for cancelling
                    this.metadataSearchCancel = resp.source;
                })
                .catch(() => this.isLoadingMetadata = false);  
        },
        updateCollectionTaxonomyTerm(taxonomyRestBase, taxonomySlug, term, isEnabled) {

            this.form.collection_taxonomies[taxonomySlug]['terms'] = this.form.collection_taxonomies[taxonomySlug]['terms'] || [];

            if ( isEnabled )
                this.form.collection_taxonomies[taxonomySlug]['terms'][term.slug] = term;
            else 
               delete this.form.collection_taxonomies[taxonomySlug]['terms'][term.slug];
           
            const taxonomyTermValues = Object.values(this.form.collection_taxonomies[taxonomySlug]['terms']).map(aTerm => aTerm.id || aTerm.slug || aTerm);

            this.updateCollectionTaxonomyValues({ collectionId: this.collection.id, taxonomyValues: { [taxonomyRestBase]: taxonomyTermValues } })
                .then(() => {
                    this.$console.log('Collection Taxonomy Values updated successfully');
                })
                .catch((error) => {
                    this.$console.error(error);
                });
        },
    }
}
</script>

<style lang="scss" scoped>

    @media screen and (min-width: 1025px) {
        .column:last-of-type {
            padding-left: var(--tainacan-one-column) !important;
        }
    }

    .field {
        position: relative;
    }

    .section-label {
        font-size: 1em !important;
        font-weight: 500 !important;
        color: var(--tainacan-blue5) !important;
        line-height: 1.2em;
    }

    #button-edit-thumbnail, 
    #button-edit-header-image,
    #button-delete-thumbnail, 
    #button-delete-header-image {
        border-radius: 100px !important;
        max-height: 2.125em !important;
        max-width: 2.125em !important;
        min-height: 2.125em !important;
        min-width: 2.125em !important;
        padding: 0 !important;
        z-index: 99;
        margin-left: 12px !important;
        
        .icon {
            display: inherit;
            padding: 0;
            margin: 0;
            margin-top: -2px;
            font-size: 1.125em;
            color: var(--tainacan-white) !important;
        }
    }

    .collection-form-section {
        margin: 1.5em 0 0.5em -0.5em;
        position: relative;
        cursor: pointer;

        .icon {
            background: var(--tainacan-background-color);
            z-index: 1;
            position: relative;
        }
        strong {
            background: var(--tainacan-background-color);
            color: var(--tainacan-gray4);
            font-size: 0.875em;
            z-index: 1;
            position: relative;
            padding-right: 12px;
        }
        hr {
            position: absolute;
            top: -0.75em;
            width: calc(100% - 42px);
            height: 1px;
            background-color: var(--tainacan-gray2);
            margin-left: 42px;
            transition: background-color 0.2s ease, height 0.2s ease;
        }
        
        &:hover {
            .icon,
            strong {
                color: var(--tainacan-secondary);
            }
            hr {
                background-color: var(--tainacan-primary );
                height: 2px;
            }
        }
    }

    .options-columns {
        margin-left: 0.25rem;
        padding-left: 1.25em;
        padding-right: 0.25em;
        padding-bottom: 1.25em;border-left: 1px solid var(--tainacan-gray2);

        & .field,
        &>div {
            break-inside: avoid;
        }

        &>div:not(.field) {
            -moz-column-count: 2;
            -moz-column-gap: 1.5em;
            -moz-column-rule: 1px solid var(--tainacan-gray1);
            -webkit-column-count: 2;
            -webkit-column-gap: 1.5em;
            -webkit-column-rule: 1px solid var(--tainacan-gray1);
            column-count: 2;
            column-rule: 1px solid var(--tainacan-gray1);
            margin-bottom: 1.125rem;

            @media screen and (max-width: 600px) {
                -moz-column-count: 1;
                -webkit-column-count: 1;
                column-count: 1;
            }
        }
    }

    #header-and-thumbnail-container {
        @supports (contain: inline-size) {
            container-type: inline-size;
            container-name: headerandthumbnailfield; 
        }

        @container headerandthumbnailfield (max-width: 395px) {
            .header-field .image-placeholder {
                right: 18%;
                top: 34%;
            }
            .thumbnail-field {
                margin: 0px;
                padding: 0px;

                img,
                :deep(.image-wrapper) {
                    border: none;
                }
                .thumbnail-buttons-row {
                    left: 61px;
                    bottom: calc(1em + 0px);
                }
            }
        }
    }

    .header-field {  
        padding-top: 1px;

        .image-placeholder {
            position: absolute;
            left: unset;
            right: 10%;
            top: 38%;
            font-size: 0.875em;
            font-weight: bold;
            z-index: 99;
            text-align: center;
            color: var(--tainacan-info-color);

            & + img {
                opacity: 0.5;
                border: 1px dashed var(--tainacan-info-color);
            }
        }
        .header-buttons-row {
            text-align: right;
            top: -15px;
            position: relative;
        }
        .image,
        img {
            border-radius: 3px;
        }
        &+.thumbnail-field {
            opacity: 1.0;
            transition: opacity 0.2s ease;
        }
        &:hover+.thumbnail-field {
            opacity: 0.3;
        }
    }

    .thumbnail-field {
        display: inline-block;  
        padding: 1rem;
        margin-top: -120px;
        margin-bottom: -30px;
        position: relative;
        z-index: 99;
        
        .content {
            padding: 10px;
            font-size: 0.8em;
        }
        .image {
            border-radius: 3px;

            &:has(.image-placeholder) img {
                opacity: 0.5;
                border: 1px dashed var(--tainacan-info-color) !important;
            }
            &:has(.image-placeholder) {
                border: 6px solid var(--tainacan-background-color);
                background: var(--tainacan-background-color);
            }
        }
        img,
        :deep(.image-wrapper) {
            height: 146px;
            width: 146px;
            border-radius: 3px;
            border: 6px solid var(--tainacan-background-color);
        }
        .image-placeholder {
            position: absolute;
            margin-left: 26px;
            margin-right: 26px;
            font-size: 0.8em;
            font-weight: bold;
            z-index: 99;
            text-align: center;
            color: var(--tainacan-info-color);
            top: 64px;
            max-width: 90px;
        }
        .thumbnail-buttons-row {
            position: relative;
            left: 52px;
            bottom: calc(1.0em + 12px);
        }
    }
    
    .switch {
        position: relative;
        top: -1px;
    }
    .selected-cover-page {
        border: 1px solid var(--tainacan-gray2);
        padding: calc(0.57em - 1px) 8px;
        font-size: .875em;
        height: auto;
        line-height: 1em;
        min-height: 32px;
        display: flex;
        justify-content: space-between;
        align-items: center;

        .span { vertical-align: middle;}

        .selected-cover-page-control {
            float: right;
        }
    }
    .selected-cover-page-buttons {
        float: right;
        padding: 4px 6px;  
        &.disabled {
            pointer-events: none;
            cursor: not-allowed;
           
           .icon { color: var(--tainacan-gray2); }
        }
    }
    .sorting-options {
        display: flex;
        align-items: center;
        width: 100%;

        .label {
            font-weight: normal;
            margin-bottom: 0;
        }
        .control.is-expanded {
            width: 100%;
        }
    }
    .sorting-options+.help {
        opacity: 0.7;
    }
    .status-radios {
        flex-wrap: wrap;
        display: flex;
        margin: 5px 0;

        .checkbox {
            width: auto;
        }
        .control-label {
            display: flex;
            align-items: center;
        }
    }
    .options-checkboxes {
        display: grid;
        margin: 5px 1px;
        grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
        gap: 0.5rem;
        justify-content: space-between;
        
        :deep(.b-checkbox.checkbox),
        :deep(.b-radio.radio) {
            width: auto;

            .control-label {
                display: flex;
                align-items: center;
                white-space: normal;
            }
        }
    }
    .two-thirds-layout-options {
        display: flex;
        column-gap: 1em !important;

        & > .field:first-child {
            flex-basis: 75%;
        }
        & > .field:last-child {
            flex-basis: 25%;
        }

        .dropdown-trigger>.button {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        @media screen and (max-width: 782px) {
            flex-wrap: wrap;
            margin-bottom: 1em;

            & > .field {
                flex-basis: 100% !important;
            }
            .dropdown-trigger>.button {
                min-height: 40px;
            }
        }
    }
    .item-submission-options {
        padding-left: 1em;
        padding-top: 1.0em;
        margin-top: -1.5em;
        border-left: 1px solid var(--tainacan-gray2);
    }
    .enabled-view-modes-dropdown {
        position: relative;
        z-index: 101;

        :deep(.dropdown-item) {
            display: flex !important;
        }
        p {
            white-space: normal;
        }
        :deep(svg) {
            margin-left: -2px;
            overflow: hidden;
            vertical-align: middle;
        }
    }

    .footer {
        padding: 10px var(--tainacan-one-column);
        position: absolute;
        bottom: 0;
        z-index: 9999;
        background-color: var(--tainacan-gray1);
        width: 100%;
        margin-left: calc(-1 * var(--tainacan-one-column));
        height: 3.5rem;
        display: flex;
        justify-content: flex-end;
        align-items: center;
        transition: bottom 0.5s ease, width 0.2s linear;
        box-shadow: 0px 0px 12px -8px var(--tainacan-black);

        &::after,
        &::before {
            height: 18px;
            width: 18px;
            background: transparent;
            display: block;
            content: '';
            position: absolute;
        }
        &::before {
            left: 0;
            top: -18px;
            border-bottom-left-radius: 9px;
            box-shadow: -9px 0px 0 0 var(--tainacan-gray1), inset 2px -2px 5px -3px var(--tainacan-gray2)
        }
        &::after {
            right: 0;
            top: -18px;
            border-bottom-right-radius: 9px;
            box-shadow: 9px 0px 0 0 var(--tainacan-gray1), inset -2px -2px 5px -3px var(--tainacan-gray2)
        }

        .footer-message {
            display: flex;
            align-items: center;
        }

        .update-info-section {
            color: var(--tainacan-info-color);
            margin-right: auto;
            display: flex;
            flex-wrap: nowrap;
        }

        .help {
            display: inline-flex;
            font-size: 1.0em;
            margin-top: 0;
            margin-left: 24px;

            .tainacan-help-tooltip-trigger {
                margin-left: 0.25em;
            }
        }

        .link-button {
            background-color: transparent;
            border: none;
        }
    }
    @media screen and (max-width: 768px) {
        .tainacan-form {
            padding-bottom: 3rem;
        }
        .footer {
            padding: 13px 0.5em;
            margin-left: calc(-1 * var(--tainacan-one-column) - var(--tainacan-page-container--inner-padding-x));
            width: 100%;
            flex-wrap: wrap;
            height: auto;
            position: fixed;

            .update-info-section {
                margin-left: auto;
                margin-bottom: 0.75em;
                margin-top: -0.25em;
            }
        }
    }

</style>


