<template>
    <div 
            class="page-container"
            :class="{'repository-level-page' : isNewCollection }">
        <tainacan-title 
                :bread-crumb-items="[{ path: '', label: $i18n.get('collection') }]"/>
        
        <form 
                v-if="collection != null && collection != undefined && ((isNewCollection && $userCaps.hasCapability('tnc_rep_edit_collections')) || (!isNewCollection && collection.current_user_can_edit))" 
                class="tainacan-form" 
                label-width="120px">
        
            <div class="columns">
                <div class="column">

                    <!-- Name -------------------------------- --> 
                    <b-field 
                            :addons="false"
                            :label="$i18n.get('label_name')"
                            :type="editFormErrors['name'] != undefined ? 'is-danger' : ''" 
                            :message="editFormErrors['name'] != undefined ? editFormErrors['name'] : ''">
                        <span class="required-metadatum-asterisk">*</span>
                        <help-button 
                                :title="$i18n.getHelperTitle('collections', 'name')" 
                                :message="$i18n.getHelperMessage('collections', 'name')"/>
                        <b-input
                                id="tainacan-text-name"
                                :placeholder="$i18n.get('instruction_collection_name')"
                                v-model="form.name"
                                @blur="updateSlug"
                                @focus="clearErrors('name')"/>
                    </b-field>

                    <!-- Hook for extra Form options -->
                    <template v-if="hasBeginLeftForm">  
                        <form
                            class="form-hook-region" 
                            id="form-collection-begin-left"
                            v-html="getBeginLeftForm"/>
                    </template>

                    <!-- Description -------------------------------- --> 
                    <b-field
                            :addons="false" 
                            :label="$i18n.get('label_description')"
                            :type="editFormErrors['description'] != undefined ? 'is-danger' : ''" 
                            :message="editFormErrors['description'] != undefined ? editFormErrors['description'] : ''">
                        <help-button 
                                :title="$i18n.getHelperTitle('collections', 'description')" 
                                :message="$i18n.getHelperMessage('collections', 'description')"/>
                        <b-input
                                id="tainacan-text-description"
                                type="textarea"
                                rows="3"
                                :placeholder="$i18n.get('instruction_collection_description')"
                                v-model="form.description"
                                @focus="clearErrors('description')"/>
                    </b-field>

                    <!-- Slug -------------------------------- --> 
                    <b-field
                            :addons="false" 
                            :label="$i18n.get('label_slug')"
                            :type="editFormErrors['slug'] != undefined ? 'is-danger' : ''" 
                            :message="isUpdatingSlug ? $i18n.get('info_validating_slug') : (editFormErrors['slug'] != undefined ? editFormErrors['slug'] : '')">
                        <help-button 
                                :title="$i18n.getHelperTitle('collections', 'slug')" 
                                :message="$i18n.getHelperMessage('collections', 'slug')"/>
                        <b-input
                                id="tainacan-text-slug"
                                @input="updateSlug"
                                v-model="form.slug"
                                @focus="clearErrors('slug')"
                                :disabled="isUpdatingSlug"
                                :loading="isUpdatingSlug"/>
                    </b-field>

                    <!-- Change Default OrderBy Select and Order Button-->
                    <b-field
                            :addons="false" 
                            :label="$i18n.get('label_default_orderby')"
                            :type="editFormErrors['default_orderby'] != undefined ? 'is-danger' : ''" 
                            :message="editFormErrors['default_orderby'] != undefined ? editFormErrors['default_orderby'] : $i18n.get('info_default_orderby')">
                        <help-button 
                                :title="$i18n.getHelperTitle('collections', 'default_orderby')" 
                                :message="$i18n.getHelperMessage('collections', 'default_orderby')"/>
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
                                    expanded
                                    :loading="isLoadingMetadata"
                                    v-model="localDefaultOrderBy"
                                    id="tainacan-select-default_orderby">
                                <option
                                        v-for="metadatum of sortingMetadata"
                                        :value="metadatum.id"
                                        :key="metadatum.id">
                                    {{ metadatum.name }}
                                </option>
                            </b-select>
                        </div>
                    </b-field>


                    <label class="label">{{ $i18n.get('label_view_modes_public_list') }}</label>
                    <div class="items-view-mode-options">

                        <!-- Enabled View Modes ------------------------------- --> 
                        <div class="field">
                            <label class="label">{{ $i18n.get('label_view_modes_available') }}</label>
                            <help-button 
                                        :title="$i18n.getHelperTitle('collections', 'enabled_view_modes')" 
                                        :message="$i18n.getHelperMessage('collections', 'enabled_view_modes')"/>
                            <div class="control">
                                <b-dropdown
                                        class="enabled-view-modes-dropdown"
                                        ref="enabledViewModesDropdown"
                                        :mobile-modal="true"
                                        :disabled="Object.keys(registeredViewModes).length < 0"
                                        aria-role="list"
                                        trap-focus
                                        position="is-top-right">
                                    <button
                                            class="button is-white"
                                            slot="trigger"
                                            position="is-top-right"
                                            type="button">
                                        <span>{{ $i18n.get('label_enabled_view_modes') }}</span>
                                        <span class="icon">
                                            <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-arrowdown"/>
                                        </span>
                                    </button>
                                    <b-dropdown-item
                                            v-for="(viewMode, index) in Object.keys(registeredViewModes)"
                                            :key="index"
                                            custom
                                            aria-role="listitem">
                                        <b-checkbox
                                                v-if="registeredViewModes[viewMode] != undefined"
                                                @input="updateViewModeslist(viewMode)"
                                                :value="checkIfViewModeEnabled(viewMode)"
                                                :disabled="checkIfViewModeEnabled(viewMode) && form.enabled_view_modes.filter((aViewMode) => (registeredViewModes[aViewMode] && registeredViewModes[aViewMode].full_screen != true)).length <= 1">
                                            <p>
                                                <strong>
                                                    <span 
                                                            class="gray-icon"
                                                            :class="{ 
                                                                'has-text-secondary' : checkIfViewModeEnabled(viewMode),
                                                                'has-text-gray4' : !checkIfViewModeEnabled(viewMode)  
                                                            }"
                                                            v-html="registeredViewModes[viewMode].icon"/>
                                                    &nbsp;{{ registeredViewModes[viewMode].label }}
                                                </strong>
                                            </p>
                                            <p v-if="registeredViewModes[viewMode].description">{{ registeredViewModes[viewMode].description }}</p>
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
                                    :message="$i18n.getHelperMessage('collections', 'default_view_mode')"/>
                            <b-select
                                    expanded
                                    id="tainacan-select-default_view_mode"
                                    v-model="form.default_view_mode"
                                    @focus="clearErrors('default_view_mode')">
                                <option
                                        v-for="(viewMode, index) of form.enabled_view_modes"
                                        v-if="registeredViewModes[viewMode] != undefined && registeredViewModes[viewMode].full_screen != true"
                                        :key="index"
                                        :value="viewMode">
                                    {{ registeredViewModes[viewMode].label }}
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
                                size="is-small"
                                true-value="yes" 
                                false-value="no"
                                v-model="form.hide_items_thumbnail_on_lists" />
                        <help-button 
                                :title="$i18n.getHelperTitle('collections', 'hide_items_thumbnail_on_lists')" 
                                :message="$i18n.getHelperMessage('collections', 'hide_items_thumbnail_on_lists')"/>
                    </b-field>

                    <!-- Comment Status ------------------------ --> 
                    <b-field
                            :addons="false" 
                            :label="$i18n.getHelperTitle('collections', 'allow_comments')">
                        &nbsp;
                        <b-switch
                                id="tainacan-checkbox-comment-status" 
                                size="is-small"
                                true-value="open" 
                                false-value="closed"
                                v-model="form.allow_comments" />
                        <help-button 
                                :title="$i18n.getHelperTitle('collections', 'allow_comments')" 
                                :message="$i18n.getHelperMessage('collections', 'allow_comments')"/>
                    </b-field>

                    <!-- Allows Submissions ------------------------ --> 
                    <b-field
                            :addons="false" 
                            :label="$i18n.getHelperTitle('collections', 'allows_submission')"
                            :type="editFormErrors['allows_submission'] != undefined ? 'is-danger' : ''" 
                            :message="editFormErrors['allows_submission'] != undefined ? editFormErrors['allows_submission'] : ''">
                        &nbsp;
                        <b-switch
                                id="tainacan-checkbox-allow-submission" 
                                size="is-small"
                                true-value="yes" 
                                false-value="no"
                                v-model="form.allows_submission" />
                        <help-button 
                                :title="$i18n.getHelperTitle('collections', 'allows_submission')" 
                                :message="$i18n.getHelperMessage('collections', 'allows_submission')"/>
                    </b-field>
                        
                    <transition name="filter-item">
                        <div 
                                v-if="form.allows_submission === 'yes'"
                                class="item-submission-options">

                            <!-- Allows Submissions by anonynmous user ------------------------ --> 
                            <b-field
                                    :addons="false" 
                                    :label="$i18n.getHelperTitle('collections', 'submission_anonymous_user')"
                                    :type="editFormErrors['submission_anonymous_user'] != undefined ? 'is-danger' : ''" 
                                    :message="editFormErrors['submission_anonymous_user'] != undefined ? editFormErrors['submission_anonymous_user'] : ''">
                                &nbsp;
                                <b-switch
                                        id="tainacan-checkbox-allow-submission" 
                                        size="is-small"
                                        true-value="yes" 
                                        false-value="no"
                                        v-model="form.submission_anonymous_user" />
                                <help-button 
                                        :title="$i18n.getHelperTitle('collections', 'submission_anonymous_user')" 
                                        :message="$i18n.getHelperMessage('collections', 'submission_anonymous_user')"/>
                            </b-field>

                            <!-- Item submission default Status -------------------------------- --> 
                            <b-field
                                    :addons="false" 
                                    :label="$i18n.getHelperTitle('collections', 'submission_default_status')"
                                    :type="editFormErrors['submission_default_status'] != undefined ? 'is-danger' : ''" 
                                    :message="editFormErrors['submission_default_status'] != undefined ? editFormErrors['submission_default_status'] : ''">
                                <help-button 
                                        :title="$i18n.getHelperTitle('collections', 'submission_default_status')" 
                                        :message="$i18n.getHelperMessage('collections', 'submission_default_status')"/>
                                <div class="status-radios">
                                    <b-radio
                                            v-model="form.submission_default_status"
                                            v-for="(statusOption, index) of $statusHelper.getStatuses().filter((status) => status.slug != 'trash')"
                                            :key="index"
                                            :native-value="statusOption.slug">
                                        <span class="icon has-text-gray">
                                            <i 
                                                class="tainacan-icon tainacan-icon-18px"
                                                :class="$statusHelper.getIcon(statusOption.slug)"/>
                                        </span>
                                        {{ statusOption.name }}
                                    </b-radio>
                                </div>
                                <transition name="filter-item">
                                    <p 
                                            class="help"
                                            v-if="form.submission_default_status == 'draft'">
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
                                        size="is-small"
                                        true-value="yes" 
                                        false-value="no"
                                        v-model="form.submission_use_recaptcha" />
                                <help-button 
                                        :title="$i18n.getHelperTitle('collections', 'submission_use_recaptcha')" 
                                        :message="$i18n.getHelperMessage('collections', 'submission_use_recaptcha')"/>
                                <p 
                                        v-if="form.submission_use_recaptcha == 'yes'" 
                                        v-html="$i18n.getWithVariables('info_recaptcha_link_%s', [ reCAPTCHASettingsPagePath ])" />        
                            </b-field>
                            

                        </div>
                    </transition>

                    <!-- Hook for extra Form options -->
                    <template v-if="hasEndLeftForm">  
                        <form
                            ref="form-collection-end-left" 
                            id="form-collection-end-left"
                            class="form-hook-region"
                            v-html="getEndLeftForm"/>
                    </template>

                </div>
                <div class="column">

                    <!-- Status -------------------------------- --> 
                    <b-field
                            :addons="false" 
                            :label="$i18n.get('label_status')"
                            :type="editFormErrors['status'] != undefined ? 'is-danger' : ''" 
                            :message="editFormErrors['status'] != undefined ? editFormErrors['status'] : ''">
                        <help-button 
                                :title="$i18n.getHelperTitle('collections', 'status')" 
                                :message="$i18n.getHelperMessage('collections', 'status')"/>
                        <div class="status-radios">
                            <b-radio
                                    v-model="form.status"
                                    v-for="(statusOption, index) of $statusHelper.getStatuses().filter((status) => status.slug != 'draft')"
                                    :key="index"
                                    :native-value="statusOption.slug">
                                <span class="icon has-text-gray">
                                    <i 
                                        class="tainacan-icon tainacan-icon-18px"
                                        :class="$statusHelper.getIcon(statusOption.slug)"/>
                                </span>
                                {{ statusOption.name }}
                            </b-radio>
                        </div>
                    </b-field>

                    <!-- Hook for extra Form options -->
                    <template v-if="hasBeginRightForm">  
                        <form 
                            id="form-collection-begin-right"
                            class="form-hook-region"
                            v-html="getBeginRightForm"/>
                    </template>

                    <!-- Image thumbnail & Header Image -------------------------------- --> 
                    <b-field :addons="false">
                        <label class="label">
                            {{ $i18n.get('label_thumbnail') }} & {{ $i18n.get('label_header_image') }}
                            <help-button 
                                    :title="$i18n.get('label_thumbnail') + ' & ' + $i18n.get('label_header_image')" 
                                    :message="$i18n.get('info_collection_thumbnail_and_header')"/>
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
                                        class="button is-rounded is-secondary"
                                        id="button-edit-header-image" 
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
                                        <i class="tainacan-icon tainacan-icon-edit"/>
                                    </span>
                                </a>
                                <a 
                                        class="button is-rounded is-secondary"
                                        id="button-delete-header-image" 
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
                                        <i class="tainacan-icon tainacan-icon-delete"/>
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
                                    }"/>
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
                                        class="button is-rounded is-secondary"
                                        id="button-edit-thumbnail" 
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
                                        <i class="tainacan-icon tainacan-icon-edit"/>
                                    </span>
                                </a>
                                <a 
                                        class="button is-rounded is-secondary"
                                        id="button-delete-header-image" 
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
                                        <i class="tainacan-icon tainacan-icon-delete"/>
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
                                size="is-small"
                                true-value="yes" 
                                false-value="no"
                                v-model="form.enable_cover_page" />
                        <help-button 
                                :title="$i18n.getHelperTitle('collections', 'cover_page_id')" 
                                :message="$i18n.getHelperMessage('collections', 'cover_page_id')"/>
                        <b-autocomplete
                                id="tainacan-text-cover-page"
                                :placeholder="$i18n.get('instruction_cover_page')"
                                :data="coverPages"
                                v-model="coverPageTitle"
                                @select="onSelectCoverPage($event)"
                                :loading="isFetchingPages"
                                @input="fecthCoverPages"
                                @focus="clearErrors('cover_page_id')"
                                v-if="coverPage == undefined || coverPage.title == undefined"
                                :disabled="form.enable_cover_page != 'yes'"
                                check-infinite-scroll
                                @infinite-scroll="fetchMoreCoverPages">
                            <template slot-scope="props">
                                {{ props.option.title.rendered }}
                            </template>
                            <template slot="empty">{{ $i18n.get('info_no_page_found') }}</template>
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
                                        <i class="tainacan-icon tainacan-icon-close"/>
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
                                    <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-see"/>
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
                                    <i class="tainacan-icon tainacan-icon-edit"/>
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
                                <i class="tainacan-icon tainacan-icon-add"/>
                            </span>
                            {{ $i18n.get('label_create_new_page') }}</a>                        
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
                            v-html="getEndRightForm"/>
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
                <p class="help is-danger">{{ formErrorMessage }}</p>
                <div 
                        style="margin-left: auto;"
                        class="control">
                    <button
                            v-if="isNewCollection && $userCaps.hasCapability('tnc_rep_edit_metadata') && !fromImporter"
                            id="button-submit-goto-metadata"
                            @click.prevent="onSubmit('metadata')"
                            class="button is-secondary">{{ $i18n.get('label_save_goto_metadata') }}</button>
                </div>
                 <div class="control">
                    <button
                            v-if="isNewCollection && $userCaps.hasCapability('tnc_rep_edit_metadata') && !fromImporter"
                            id="button-submit-goto-filter"
                            @click.prevent="onSubmit('filters')"
                            class="button is-secondary">{{ $i18n.get('label_save_goto_filter') }}</button>
                </div>
                <div class="control">
                    <button
                            id="button-submit-collection-creation"
                            @click.prevent="onSubmit('items')"
                            class="button is-success">{{ $i18n.get('finish') }}</button>
                </div>
            </footer>
        </form>

        <div v-if="!isLoading && ((isNewCollection && !$userCaps.hasCapability('tnc_rep_edit_collections')) || (!isNewCollection && collection && collection.current_user_can_edit != undefined && collection.current_user_can_edit == false))">
            <section class="section">
                <div class="content has-text-grey has-text-centered">
                    <p>
                        <span class="icon">
                            <i class="tainacan-icon tainacan-icon-30px tainacan-icon-items"/>
                        </span>
                    </p>
                    <p>{{ $i18n.get('info_can_not_edit_collection') }}</p>
                </div>
            </section>
        </div>

        <b-loading 
                :active.sync="isLoading" 
                :can-cancel="false"/>
    </div>
</template>

<script>
import { mapGetters, mapActions } from 'vuex';
import wpMediaFrames from '../../js/wp-media-frames';
import FileItem from '../other/file-item.vue';
import { wpAjax, formHooks } from '../../js/mixins';

export default {
    name: 'CollectionEditionForm',
    components: {
        FileItem
    },
    mixins: [ wpAjax, formHooks ],
    data(){
        return {
            collectionId: Number,
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
                allows_submission: 'no',
                submission_default_status: 'draft',
                submission_anonymous_user: 'no',
                hide_items_thumbnail_on_lists: '',
                submission_use_recaptcha: 'no'
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
            registeredViewModes: tainacan_plugin.registered_view_modes,
            reCAPTCHASettingsPagePath: tainacan_plugin.admin_url + 'admin.php?page=tainacan_item_submission',
            newPagePath: tainacan_plugin.admin_url + 'post-new.php?post_type=page',
            isUpdatingSlug: false,
            entityName: 'collection',
            metadataSearchCancel: undefined,
            isLoadingMetadata: true,
            sortingMetadata: [],
            localDefaultOrderBy: 'date'
        }
    },
    computed: {
        ...mapGetters('metadata', {
            'metadata': 'getMetadata'
        })
    },
    watch: {
        'form.hide_items_thumbnail_on_lists' (newValue) {
            if (newValue == 'yes') {
                const validViewModes = {};
                Object.keys(tainacan_plugin.registered_view_modes).forEach((viewModeKey) => {
                    if (!tainacan_plugin.registered_view_modes[viewModeKey]['requires_thumbnail']) 
                        validViewModes[viewModeKey] = tainacan_plugin.registered_view_modes[viewModeKey];
                });
                this.registeredViewModes = validViewModes;
                
                this.form.enabled_view_modes = this.form.enabled_view_modes.filter((aViewMode) => this.registeredViewModes[aViewMode] != undefined );

                this.updateDefaultViewModeBasedOnEnabled();           
                
                // Setting initial view mode
                if (this.$userPrefs.get('admin_view_mode_' + this.collectionId) == 'masonry' || this.$userPrefs.get('admin_view_mode_' + this.collectionId) == 'grid')
                    this.$userPrefs.set('admin_view_mode_' + this.collectionId, 'table');

            } else {
                this.registeredViewModes = tainacan_plugin.registered_view_modes;
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
        this.$root.$emit('onCollectionBreadCrumbUpdate', [{ path: '', label: this.$i18n.get('settings') }]);


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
                this.$nextTick()
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
                this.form.allows_submission = this.collection.allows_submission;
                this.form.submission_anonymous_user = this.collection.submission_anonymous_user;
                this.form.submission_default_status = this.collection.submission_default_status;
                this.form.submission_use_recaptcha = this.collection.submission_use_recaptcha;
                this.form.hide_items_thumbnail_on_lists = this.collection.hide_items_thumbnail_on_lists;

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
            var tmppath = this.$route.fullPath.split("/");
            var mapper = tmppath.pop();
            if(tmppath.pop() == 'new') {
                this.isNewCollection = true;
                this.isMapped = true;
                this.mapper = mapper;
                this.createNewCollection();
            }
        }
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
            'fetchAllCollectionNames'
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
                hide_items_thumbnail_on_lists: this.form.hide_items_thumbnail_on_lists
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
                    this.form.allows_submission = this.collection.allows_submission;
                    this.form.submission_anonymous_user = this.collection.submission_anonymous_user;
                    this.form.submission_default_status = this.collection.submission_default_status;
                    this.form.submission_use_recaptcha = this.collection.submission_use_recaptcha;
                    this.form.hide_items_thumbnail_on_lists = this.collection.hide_items_thumbnail_on_lists;
                    
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
                this.form.enabled_view_modes = [];
                this.form.allow_comments = this.collection.allow_comments;
                this.form.allows_submission = this.collection.allows_submission;
                this.form.submission_anonymous_user = this.collection.submission_anonymous_user;
                this.form.submission_default_status = this.collection.submission_default_status;
                this.form.submission_use_recaptcha = this.collection.submission_use_recaptcha;
                this.form.hide_items_thumbnail_on_lists = this.collection.hide_items_thumbnail_on_lists;

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
                const validViewModeIndex = this.form.enabled_view_modes.findIndex((aViewMode) => (this.registeredViewModes[aViewMode] && !this.registeredViewModes[aViewMode].full_screen));
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
        }
    }
}
</script>

<style lang="scss" scoped>

    @media screen and (min-width: 1024px) {
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
    .header-field {  
        padding-top: 1px;

        .image-placeholder {
            position: absolute;
            left: 10%;
            right: 10%;
            top: 35%;
            font-size: 1em;
            font-weight: bold;
            z-index: 99;
            text-align: center;
            color: var(--tainacan-info-color);
            
            @media screen and (max-width: 1024px) {
                font-size: 1.2em;
            }
            
        }
        .header-buttons-row {
            text-align: right;
            top: -15px;
            position: relative;
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
        img,
        /deep/ .image-wrapper {
            height: 146px;
            width: 146px;
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
        padding: 3px 8px;
        font-size: .875em;
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
        display: flex;

        .control-lable {
            display: flex;
            align-items: center;
        }
    }
    .items-view-mode-options {
        display: flex;

        &>.field:first-child {
            width: 66.66%;
            margin-right: 12px;

            .dropdown-trigger>.button {
                min-height: 35px;
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
            }
        }
        &>.field:last-child {
            width: 33.33%;
        }

        @media screen and (min-width: 1024px) {
            .dropdown-trigger>.button {
                min-height: 40px;
            }
        }
    }
    .item-submission-options {
        padding-left: 1em;
        padding-top: 1.25em;
        margin-top: -1.5em;
        border-left: 1px solid var(--tainacan-gray2);
    }
    .enabled-view-modes-dropdown {
        position: relative;
        z-index: 101;

        /deep/ .dropdown-item {
            display: flex !important;
        }
        p {
            white-space: normal;
        }
        /deep/ svg {
            margin-left: -2px;
            overflow: hidden;
            vertical-align: middle;
        }
    }

    .tainacan-form {
        padding-bottom: 48px;
    }

    .footer {
        padding: 14px var(--tainacan-one-column);
        position: fixed;
        bottom: 0;
        right: 0;
        z-index: 9999;
        background-color: var(--tainacan-gray1);
        width: calc(100% - var(--tainacan-sidebar-width, 3.25em));
        height: 60px;
        display: flex;
        justify-content: flex-end;
        align-items: center;
        transition: bottom 0.5s ease, width 0.2s linear;

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

        @media screen and (max-width: 769px) {
            padding: 13px 0.5em;
            width: 100%;
            flex-wrap: wrap;
            height: auto;
            position: fixed;

            .update-info-section {
                margin-left: auto;margin-bottom: 0.75em;
                margin-top: -0.25em;
            }
        }
    }

</style>


