<template>
    <div class="table-container">

        <!-- TODO: Remove v-if="collectionId" from this element when the bulk edit in repository is done -->
        <div
                v-if="collectionId"
                class="selection-control">
            <div class="field select-all is-pulled-left">
                <span>
                    <b-checkbox
                            @click.native="selectAllItemsOnPage()"
                            :value="allItemsOnPageSelected">
                        {{ $i18n.get('label_select_all_items_page') }}
                    </b-checkbox>
                </span>

                <span
                        style="margin-left: 10px"
                        v-if="allItemsOnPageSelected && items.length > 1">
                    <b-checkbox
                            @click.native="selectAllItems()"
                            v-model="isAllItemsSelected">
                        {{ `${$i18n.get('label_select_all')} ${totalItems} ${$i18n.get('items').toLowerCase()}` }}
                    </b-checkbox>
                </span>
            </div>

            <div class="field is-pulled-right">
                <b-dropdown
                        :mobile-modal="true"
                        position="is-bottom-left"
                        v-if="items.length > 0 && items[0].current_user_can_edit"
                        :disabled="selectedItemsIDs.every(id => id === false) || this.selectedItemsIDs.filter(item => item !== false).length <= 1"
                        id="bulk-actions-dropdown">
                    <button
                            class="button is-white"
                            slot="trigger">
                        <span>{{ $i18n.get('label_bulk_actions') }}</span>
                        <b-icon icon="menu-down"/>
                    </button>

                    <b-dropdown-item
                            v-if="$route.params.collectionId && $userCaps.hasCapability('edit_others_posts') && !isOnTrash"
                            @click="openBulkEditionModal()">
                        {{ $i18n.get('label_bulk_edit_selected_items') }}
                    </b-dropdown-item>
                    <b-dropdown-item
                            v-if="$route.params.collectionId && $userCaps.hasCapability('edit_others_posts') && !isOnTrash"
                            @click="sequenceEditSelectedItems()">
                        {{ $i18n.get('label_sequence_edit_selected_items') }}
                    </b-dropdown-item>
                    <b-dropdown-item
                            v-if="collectionId"
                            @click="deleteSelectedItems()"
                            id="item-delete-selected-items">
                        {{ isOnTrash ? $i18n.get('label_delete_permanently') : $i18n.get('label_send_to_trash') }}
                    </b-dropdown-item>
                    <b-dropdown-item
                            v-if="collectionId && isOnTrash"
                            @click="untrashSelectedItems()">
                        {{ $i18n.get('label_untrash_selected_items') }}
                    </b-dropdown-item>
                </b-dropdown>
            </div>
        </div>

        <div class="table-wrapper">
            <div
                    v-show="isLoading"
                    class="loading-container">
                <b-loading
                        :is-full-page="false"
                        :active.sync="isLoading"/>
            </div>
            
            <!-- GRID (THUMBNAILS) VIEW MODE -->
            <div
                    class="tainacan-grid-container"
                    v-if="viewMode == 'grid'">
                <div 
                        :key="index"
                        v-for="(item, index) of items"
                        :class="{ 'selected-grid-item': selectedItems[index] }"
                        class="tainacan-grid-item">
                    
                    <!-- Checkbox -->
                    <!-- TODO: Remove v-if="collectionId" from this element when the bulk edit in repository is done -->
                    <div
                            v-if="collectionId"
                            :class="{ 'is-selecting': isSelectingItems }"
                            class="grid-item-checkbox">
                        <b-checkbox 
                                size="is-small"
                                v-model="selectedItems[index]"/> 
                    </div>

                    <!-- Title -->
                    <div
                            :style="{
                             'padding-left': !collectionId ? '0.5rem !important' : '2.75rem',
                             'margin-left': !collectionId ? '0 !important' : '24px'
                            }"
                            class="metadata-title">
                        <p 
                                v-tooltip="{
                                    content: item.title != undefined ? item.title : '',
                                    html: true,
                                    autoHide: false,
                                    placement: 'auto-start'
                                }"                               
                                @click="onClickItem($event, item, index)">
                            {{ item.title != undefined ? item.title : '' }}
                        </p>                            
                    </div>
                    <!-- Thumbnail -->
                    <a
                            v-if="item.thumbnail != undefined"
                            @click="onClickItem($event, item, index)">
                        <img :src="item['thumbnail'].tainacan_medium ? item['thumbnail'].tainacan_medium : (item['thumbnail'].medium ? item['thumbnail'].medium : thumbPlaceholderPath)">
                    </a>

                    <!-- Actions -->
                    <div 
                            v-if="item.current_user_can_edit"
                            class="actions-area"
                            :label="$i18n.get('label_actions')">
                        <a
                                v-if="!isOnTrash"
                                id="button-edit"   
                                :aria-label="$i18n.getFrom('items','edit_item')" 
                                @click.prevent.stop="goToItemEditPage(item)">
                            <b-icon
                                    type="is-secondary" 
                                    icon="pencil"/>
                        </a>
                        <a
                                :aria-lavel="$i18n.get('label_button_untrash')"
                                @click.prevent.stop="untrashOneItem(item.id)"
                                v-if="isOnTrash">
                            <b-icon
                                    type="is-secondary"
                                    icon="delete-restore"/>
                        </a>
                        <a
                                v-if="collectionId"
                                id="button-delete" 
                                :aria-label="$i18n.get('label_button_delete')" 
                                @click.prevent.stop="deleteOneItem(item.id)">
                            <b-icon 
                                    type="is-secondary" 
                                    :icon="!isOnTrash ? 'delete' : 'delete-forever'"/>
                        </a>
                    </div>
            
                </div>
            </div>

            <!-- MASONRY VIEW MODE -->
            <masonry 
                    v-if="viewMode == 'masonry'"
                    :cols="{default: 7, 1919: 6, 1407: 5, 1215: 4, 1023: 3, 767: 2, 343: 1}"
                    :gutter="25"
                    class="tainacan-masonry-container">
                <div
                        :key="index"
                        v-for="(item, index) of items"
                        :class="{ 'selected-masonry-item': selectedItems[index] }"
                        class="tainacan-masonry-item">

                    <!-- Checkbox -->
                    <!-- TODO: Remove v-if="collectionId" from this element when the bulk edit in repository is done -->
                    <div
                            v-if="collectionId"
                            :class="{ 'is-selecting': isSelectingItems }"
                            class="masonry-item-checkbox">
                        <label 
                                tabindex="0" 
                                class="b-checkbox checkbox is-small">
                            <input 
                                    type="checkbox"
                                    v-model="selectedItems[index]"> 
                                <span class="check" /> 
                                <span class="control-label" />
                        </label>
                    </div>

                    <!-- Title -->
                    <div
                            :style="{
                                'padding-left': !collectionId ? '0 !important' : '1rem'
                            }"
                            @click="onClickItem($event, item, index)"
                            class="metadata-title">
                        <p>{{ item.title != undefined ? item.title : '' }}</p>                             
                    </div>

                    <!-- Thumbnail -->  
                    <div 
                            @click="onClickItem($event, item, index)"
                            v-if="item.thumbnail != undefined"
                            class="thumbnail"
                            :style="{ backgroundImage: 'url(' + (item['thumbnail'].tainacan_medium_full ? item['thumbnail'].tainacan_medium_full : (item['thumbnail'].medium_large ? item['thumbnail'].medium_large : thumbPlaceholderPath)) + ')' }">
                        <img :src="item['thumbnail'].tainacan_medium_full ? item['thumbnail'].tainacan_medium_full : (item['thumbnail'].medium_large ? item['thumbnail'].medium_large : thumbPlaceholderPath)">
                    </div>
                    
                    <!-- Actions -->
                    <div 
                            v-if="item.current_user_can_edit"
                            class="actions-area"
                            :label="$i18n.get('label_actions')">
                        <a
                                v-if="!isOnTrash"
                                id="button-edit"   
                                :aria-label="$i18n.getFrom('items','edit_item')" 
                                @click.prevent.stop="goToItemEditPage(item)">
                            <b-icon
                                    type="is-secondary" 
                                    icon="pencil"/>
                        </a>
                        <a
                                :aria-lavel="$i18n.get('label_button_untrash')"
                                @click.prevent.stop="untrashOneItem(item.id)"
                                v-if="isOnTrash">
                            <b-icon
                                    type="is-secondary"
                                    icon="delete-restore"/>
                        </a>
                        <a
                                v-if="collectionId"
                                id="button-delete" 
                                :aria-label="$i18n.get('label_button_delete')" 
                                @click.prevent.stop="deleteOneItem(item.id)">
                            <b-icon 
                                    type="is-secondary" 
                                    :icon="!isOnTrash ? 'delete' : 'delete-forever'"/>
                        </a>
                    </div>
                </div>
            </masonry>

            <!-- CARDS VIEW MODE -->
            <div
                    class="tainacan-cards-container"
                    v-if="viewMode == 'cards'">
                <div 
                        :key="index"
                        v-for="(item, index) of items"
                        :class="{ 'selected-card': selectedItems[index] }"
                        class="tainacan-card">
                    
                    <!-- Checkbox -->
                    <!-- TODO: Remove v-if="collectionId" from this element when the bulk edit in repository is done -->
                    <div
                            v-if="collectionId"
                            :class="{ 'is-selecting': isSelectingItems }"
                            class="card-checkbox">
                        <b-checkbox 
                                size="is-small"
                                v-model="selectedItems[index]"/> 
                    </div>
                    
                    <!-- Title -->
                    <div
                            :style="{ 'padding-left': !collectionId ? '0.5rem !important' : '2.75rem' }"
                            class="metadata-title">
                        <p 
                                v-tooltip="{
                                    content: item.title != undefined ? item.title : '',
                                    html: true,
                                    autoHide: false,
                                    placement: 'auto-start'
                                }"                               
                                @click="onClickItem($event, item, index)">
                            {{ item.title != undefined ? item.title : '' }}
                        </p>                            
                    </div>
                    <!-- Actions -->
                    <div 
                            v-if="item.current_user_can_edit"
                            class="actions-area"
                            :label="$i18n.get('label_actions')">
                        <a
                                v-if="!isOnTrash"
                                id="button-edit"   
                                :aria-label="$i18n.getFrom('items','edit_item')" 
                                @click.prevent.stop="goToItemEditPage(item)">
                            <b-icon
                                    type="is-secondary" 
                                    icon="pencil"/>
                        </a>
                        <a
                                :aria-lavel="$i18n.get('label_button_untrash')"
                                @click.prevent.stop="untrashOneItem(item.id)"
                                v-if="isOnTrash">
                            <b-icon
                                    type="is-secondary"
                                    icon="delete-restore"/>
                        </a>
                        <a
                                v-if="collectionId"
                                id="button-delete" 
                                :aria-label="$i18n.get('label_button_delete')" 
                                @click.prevent.stop="deleteOneItem(item.id)">
                            <b-icon 
                                    type="is-secondary" 
                                    :icon="!isOnTrash ? 'delete' : 'delete-forever'"/>
                        </a>
                    </div>
                    
                    <!-- Remaining metadata -->  
                    <div    
                            class="media"
                            @click="onClickItem($event, item, index)">
                      
                        <img 
                                v-if="item.thumbnail != undefined"
                                :src="item['thumbnail'].tainacan_medium ? item['thumbnail'].tainacan_medium : (item['thumbnail'].medium ? item['thumbnail'].medium : thumbPlaceholderPath)">
                    
                        <div class="list-metadata media-body">
                            <!-- Description -->
                            <p 
                                    v-tooltip="{
                                        content: item.description != undefined && item.description != '' ? item.description : `<span class='has-text-gray is-italic'>` + $i18n.get('label_description_not_informed') + `</span>`,
                                        html: true,
                                        autoHide: false,
                                        placement: 'auto-start'
                                    }"   
                                    class="metadata-description"
                                    v-html="item.description != undefined && item.description != '' ? getLimitedDescription(item.description) : `<span class='has-text-gray is-italic'>` + $i18n.get('label_description_not_informed') + `</span>`" />
                            <!-- Author-->
                            <p 
                                    v-tooltip="{
                                        content: item.author_name != undefined ? item.author_name : '',
                                        html: false,
                                        autoHide: false,
                                        placement: 'auto-start'
                                    }"   
                                    class="metadata-author-creation">   
                                {{ $i18n.get('info_created_by') + ' ' + (item.author_name != undefined ? item.author_name : '') }}
                            </p>  
                            <!-- Creation Date-->
                            <p 
                                    v-tooltip="{
                                        content: item.creation_date != undefined ? item.creation_date : '',
                                        html: false,
                                        autoHide: false,
                                        placement: 'auto-start'
                                    }"   
                                    class="metadata-author-creation">   
                                {{ $i18n.get('info_date') + ' ' + (item.creation_date != undefined ? item.creation_date : '') }}
                            </p>   
                        </div>
                    </div>
               
                </div>
            </div>

            <!-- RECORDS VIEW MODE -->
            <masonry
                    :cols="{default: 4, 1919: 3, 1407: 2, 1215: 2, 1023: 1, 767: 1, 343: 1}"
                    :gutter="30" 
                    class="tainacan-records-container"
                    v-if="viewMode == 'records'">
                <div 
                        :key="index"
                        v-for="(item, index) of items"
                        :class="{ 'selected-record': selectedItems[index] }"
                        class="tainacan-record">
                    
                    <!-- Checkbox -->
                    <!-- TODO: Remove v-if="collectionId" from this element when the bulk edit in repository is done -->
                    <div
                            v-if="collectionId"
                            :class="{ 'is-selecting': isSelectingItems }"
                            class="record-checkbox">
                        <label
                                tabindex="0"
                                class="b-checkbox checkbox is-small">
                            <input
                                    type="checkbox"
                                    v-model="selectedItems[index]">
                                <span class="check" />
                                <span class="control-label" />
                        </label>
                    </div>
                    
                    <!-- Title -->
                    <div
                            class="metadata-title"
                            :style="{ 'padding-left': !collectionId ? '1.5rem !important' : '2.75rem' }">
                        <p 
                                v-tooltip="{
                                    content: item.metadata != undefined ? renderMetadata(item.metadata, column) : '',
                                    html: true,
                                    autoHide: false,
                                    placement: 'auto-start'
                                }"
                                v-for="(column, columnIndex) in tableMetadata"
                                :key="columnIndex"
                                v-if="collectionId != undefined && column.display && column.metadata_type_object != undefined && (column.metadata_type_object.related_mapped_prop == 'title')"
                                @click="onClickItem($event, item, index)"
                                v-html="item.metadata != undefined ? renderMetadata(item.metadata, column) : ''" />  
                        <p 
                                v-tooltip="{
                                    content: item.title != undefined ? item.title : '',
                                    html: true,
                                    autoHide: false,
                                    placement: 'auto-start'
                                }"
                                v-for="(column, columnIndex) in tableMetadata"
                                :key="columnIndex"
                                v-if="collectionId == undefined && column.display && column.metadata_type_object != undefined && (column.metadata_type_object.related_mapped_prop == 'title')"
                                @click="onClickItem($event, item, index)"
                                v-html="item.title != undefined ? item.title : ''" />                             
                    </div>
                    <!-- Actions -->
                    <div 
                            v-if="item.current_user_can_edit"
                            class="actions-area"
                            :label="$i18n.get('label_actions')">
                        <a
                                v-if="!isOnTrash"
                                id="button-edit"   
                                :aria-label="$i18n.getFrom('items','edit_item')" 
                                @click.prevent.stop="goToItemEditPage(item)">
                            <b-icon
                                    type="is-secondary" 
                                    icon="pencil"/>
                        </a>
                        <a
                                :aria-lavel="$i18n.get('label_button_untrash')"
                                @click.prevent.stop="untrashOneItem(item.id)"
                                v-if="isOnTrash">
                            <b-icon
                                    type="is-secondary"
                                    icon="delete-restore"/>
                        </a>
                        <a
                                v-if="collectionId"
                                id="button-delete" 
                                :aria-label="$i18n.get('label_button_delete')" 
                                @click.prevent.stop="deleteOneItem(item.id)">
                            <b-icon 
                                    type="is-secondary" 
                                    :icon="!isOnTrash ? 'delete' : 'delete-forever'"/>
                        </a>
                    </div>
                    
                    <!-- Remaining metadata -->  
                    <div    
                            class="media"
                            @click="onClickItem($event, item, index)">
                        <div class="list-metadata media-body">
                            <div class="thumbnail">
                                <img 
                                        v-if="item.thumbnail != undefined"
                                        :src="item['thumbnail'].tainacan_medium_full ? item['thumbnail'].tainacan_medium_full : (item['thumbnail'].medium_large ? item['thumbnail'].medium_large : thumbPlaceholderPath)"> 
                            </div>
                            <span 
                                    v-for="(column, index) in tableMetadata"
                                    :key="index"
                                    v-if="collectionId == undefined && column.display && column.metadata_type_object != undefined && (column.metadata_type_object.related_mapped_prop == 'description')">
                                <h3 class="metadata-label">{{ $i18n.get('label_description') }}</h3>
                                <p 
                                        v-html="item.description != undefined ? item.description : ''"
                                        class="metadata-value"/>
                            </span>
                            <span 
                                    v-for="(column, index) in tableMetadata"
                                    :key="index"
                                    v-if="renderMetadata(item.metadata, column) != '' && column.display && column.slug != 'thumbnail' && column.metadata_type_object != undefined && (column.metadata_type_object.related_mapped_prop != 'title')">
                                <h3 class="metadata-label">{{ column.name }}</h3>
                                <p 
                                        v-html="renderMetadata(item.metadata, column)"
                                        class="metadata-value"/>
                            </span>
                        </div>
                    </div>
               
                </div>
            </masonry>
            
            <!-- TABLE VIEW MODE -->
            <table 
                    v-if="viewMode == 'table'"
                    class="tainacan-table">
                <thead>
                    <tr>
                        <!-- Checking list -->
                        <th
                                v-if="collectionId">
                            &nbsp;
                            <!-- nothing to show on header for checkboxes -->
                        </th>
                        <!-- Displayed Metadata -->
                        <th 
                                v-for="(column, index) in tableMetadata"
                                :key="index"
                                v-if="column.display"
                                class="column-default-width"
                                :class="{
                                        'thumbnail-cell': column.metadatum == 'row_thumbnail',
                                        'column-small-width' : column.metadata_type_object != undefined ? (column.metadata_type_object.primitive_type == 'date' || 
                                                                                                           column.metadata_type_object.primitive_type == 'float' ||
                                                                                                           column.metadata_type_object.primitive_type == 'int') : false,
                                        'column-medium-width' : column.metadata_type_object != undefined ? (column.metadata_type_object.primitive_type == 'term' || 
                                                                                                            column.metadata_type_object.primitive_type == 'item' || 
                                                                                                            column.metadata_type_object.primitive_type == 'compound') : false,
                                        'column-large-width' : column.metadata_type_object != undefined ? (column.metadata_type_object.primitive_type == 'long_string' || column.metadata_type_object.related_mapped_prop == 'description') : false,
                                }"
                                :custom-key="column.slug">
                            <div class="th-wrap">{{ column.name }}</div>
                        </th>
                        <th class="actions-header">
                            &nbsp;
                            <!-- nothing to show on header for actions cell-->
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr     
                            :class="{ 'selected-row': selectedItems[index] }"
                            :key="index"
                            v-for="(item, index) of items">
                        <!-- Checking list -->
                        <!-- TODO: Remove v-if="collectionId" from this element when the bulk edit in repository is done -->
                        <td
                                v-if="collectionId"
                                :class="{ 'is-selecting': isSelectingItems }"
                                class="checkbox-cell">
                            <b-checkbox 
                                    size="is-small"
                                    v-model="selectedItems[index]"/> 
                        </td>

                        <!-- Item Displayed Metadata -->
                        <td 
                                :key="columnIndex"
                                v-for="(column, columnIndex) in tableMetadata"
                                v-if="column.display"
                                :label="column.name" 
                                class="column-default-width"
                                :class="{
                                        'thumbnail-cell': column.metadatum == 'row_thumbnail',
                                        'column-main-content' : column.metadata_type_object != undefined ? (column.metadata_type_object.related_mapped_prop == 'title') : false,
                                        'column-needed-width column-align-right' : column.metadata_type_object != undefined ? (column.metadata_type_object.primitive_type == 'float' || 
                                                                                                                               column.metadata_type_object.primitive_type == 'int' ) : false,
                                        'column-small-width' : column.metadata_type_object != undefined ? (column.metadata_type_object.primitive_type == 'date' || 
                                                                                                           column.metadata_type_object.primitive_type == 'int' ||
                                                                                                           column.metadata_type_object.primitive_type == 'float') : false,
                                        'column-medium-width' : column.metadata_type_object != undefined ? (column.metadata_type_object.primitive_type == 'item' || 
                                                                                                            column.metadata_type_object.primitive_type == 'term' || 
                                                                                                            column.metadata_type_object.primitive_type == 'compound') : false,
                                        'column-large-width' : column.metadata_type_object != undefined ? (column.metadata_type_object.primitive_type == 'long_string' || column.metadata_type_object.related_mapped_prop == 'description') : false,
                                }"
                                @click="onClickItem($event, item, index)">

                            <p
                                    v-tooltip="{
                                        content: item.title != undefined && item.title != '' ? item.title : `<span class='has-text-gray is-italic'>` + $i18n.get('label_value_not_informed') + `</span>`,
                                        html: true,
                                        autoHide: false,
                                        placement: 'auto-start'
                                    }"
                                    v-if="collectionId == undefined &&
                                          column.metadata_type_object != undefined && 
                                          column.metadata_type_object.related_mapped_prop == 'title'"
                                    v-html="item.title != undefined && item.title != '' ? item.title : `<span class='has-text-gray is-italic'>` + $i18n.get('label_value_not_informed') + `</span>`"/>
                            <p
                                    v-tooltip="{
                                        content: item.description != undefined && item.description != '' ? item.description : `<span class='has-text-gray is-italic'>` + $i18n.get('label_value_not_informed') + `</span>`,
                                        html: true,
                                        autoHide: false,
                                        placement: 'auto-start'
                                    }"
                                    v-if="collectionId == undefined &&
                                          column.metadata_type_object != undefined && 
                                          column.metadata_type_object.related_mapped_prop == 'description'"
                                    v-html="item.description != undefined && item.description != '' ? item.description : `<span class='has-text-gray is-italic'>` + $i18n.get('label_value_not_informed') + `</span>`"/>
                            <p
                                    v-tooltip="{
                                        content: renderMetadata(item.metadata, column) != '' ? renderMetadata(item.metadata, column) : `<span class='has-text-gray is-italic'>` + $i18n.get('label_value_not_informed') + `</span>`,
                                        html: true,
                                        autoHide: false,
                                        placement: 'auto-start'
                                    }"
                                    v-if="item.metadata != undefined &&
                                          column.metadatum !== 'row_thumbnail' &&
                                          column.metadatum !== 'row_actions' &&
                                          column.metadatum !== 'row_creation' &&
                                          column.metadatum !== 'row_author'"
                                    v-html="renderMetadata(item.metadata, column) != '' ? renderMetadata(item.metadata, column) : `<span class='has-text-gray is-italic'>` + $i18n.get('label_value_not_informed') + `</span>`"/>

                            <span v-if="column.metadatum == 'row_thumbnail'">
                                <img 
                                        class="table-thumb" 
                                        :src="item['thumbnail'].tainacan_small ? item['thumbnail'].tainacan_small : (item['thumbnail'].thumb ? item['thumbnail'].thumb : thumbPlaceholderPath)">
                            </span> 
                            <p 
                                    v-tooltip="{
                                        content: item[column.slug],
                                        html: true,
                                        autoHide: false,
                                        placement: 'auto-start'
                                    }"
                                    v-if="column.metadatum == 'row_author' || column.metadatum == 'row_creation'">
                                    {{ item[column.slug] }}
                            </p>

                        </td>

                        <!-- Actions -->
                        <td 
                                v-if="item.current_user_can_edit"
                                class="actions-cell"
                                :label="$i18n.get('label_actions')">
                            <div class="actions-container">
                                <a
                                        v-if="!isOnTrash"
                                        id="button-edit"   
                                        :aria-label="$i18n.getFrom('items','edit_item')" 
                                        @click.prevent.stop="goToItemEditPage(item)">
                                    <b-icon
                                            type="is-secondary" 
                                            icon="pencil"/>
                                </a>
                                <a
                                        :aria-lavel="$i18n.get('label_button_untrash')"
                                        @click.prevent.stop="untrashOneItem(item.id)"
                                        v-if="isOnTrash">
                                    <b-icon
                                            type="is-secondary"
                                            icon="delete-restore"/>
                                </a>
                                <a
                                        v-if="collectionId"
                                        id="button-delete" 
                                        :aria-label="$i18n.get('label_button_delete')" 
                                        @click.prevent.stop="deleteOneItem(item.id)">
                                    <b-icon 
                                            type="is-secondary" 
                                            :icon="!isOnTrash ? 'delete' : 'delete-forever'"/>
                                </a>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div> 
    </div>
</template>

<script>
import { mapActions, mapGetters } from 'vuex';
import CustomDialog from '../other/custom-dialog.vue';
import BulkEditionModal from '../bulk-edition/bulk-edition-modal.vue';

export default {
    name: 'ItemsList',
    data(){
        return {
            allItemsOnPageSelected: false,
            isAllItemsSelected: false,
            isSelectingItems: false,
            selectedItems: [],
            selectedItemsIDs: [],
            queryAllItemsSelected: {},
            thumbPlaceholderPath: tainacan_plugin.base_url + '/admin/images/placeholder_square.png',
        }
    },
    props: {
        collectionId: undefined,
        tableMetadata: Array,
        items: Array,
        isLoading: false,
        isOnTrash: false,
        totalItems: Number,
        viewMode: 'card'
    },
    mounted() {
        this.selectedItems = [];
        this.selectedItemsIDs = [];

        for (let i = 0; i < this.items.length; i++) {
            this.selectedItemsIDs.push(false);
            this.selectedItems.push(false);
        }
    },
    watch: {
        selectedItems() {
            let allSelected = true;
            let isSelecting = false;

            allSelected = !this.selectedItems.some(item => item === false);

            this.selectedItems.map((item, index) => {
                if(item === false){
                    this.selectedItemsIDs.splice(index, 1, false);
                    this.queryAllItemsSelected = {};
                } else if(item === true) {
                    isSelecting = true;

                    this.selectedItemsIDs.splice(index, 1, this.items[index].id);
                }
            });

            if(!allSelected) {
                this.isAllItemsSelected = allSelected;
            }

            this.allItemsOnPageSelected = allSelected;
            this.isSelectingItems = isSelecting;
        },
    },
    methods: {
        ...mapActions('collection', [
            'deleteItem',
        ]),
        ...mapActions('bulkedition', [
            'createEditGroup',
            'trashItemsInBulk',
            'deleteItemsInBulk',
            'untrashItemsInBulk',
        ]),
        ...mapGetters('bulkedition', [
            'getGroupID'
        ]),
        ...mapGetters('search', [
            'getOrder',
            'getOrderBy'
        ]),
        openBulkEditionModal(){
            this.$modal.open({
                parent: this,
                component: BulkEditionModal,
                props: {
                    modalTitle: this.$i18n.get('info_editing_items_in_bulk'),
                    totalItems: Object.keys(this.queryAllItemsSelected).length ? this.totalItems : this.selectedItemsIDs.filter(item => item !== false).length,
                    selectedForBulk: Object.keys(this.queryAllItemsSelected).length ? this.queryAllItemsSelected : this.selectedItemsIDs.filter(item => item !== false),
                    objectType: this.$i18n.get('items'),
                    collectionID: this.$route.params.collectionId,
                },
                width: 'calc(100% - 8.333333333%)',
            });
        },
        sequenceEditSelectedItems() {
            this.createEditGroup({
                object: Object.keys(this.queryAllItemsSelected).length ? this.queryAllItemsSelected : this.selectedItemsIDs.filter(item => item !== false),
                collectionID: this.collectionId,
                order: this.getOrder(),
                orderBy: this.getOrderBy()
            }).then(() => {
                let sequenceId = this.getGroupID();
                this.$router.push(this.$routerHelper.getCollectionSequenceEditPath(this.collectionId, sequenceId, 1));
            });
        },
        selectAllItemsOnPage() {
            for (let i = 0; i < this.selectedItems.length; i++) {
                this.selectedItems.splice(i, 1, !this.allItemsOnPageSelected);
            }
        },
        selectAllItems(){
            this.isAllItemsSelected = !this.isAllItemsSelected;
            this.queryAllItemsSelected = this.$route.query;

            for (let i = 0; i < this.selectedItems.length; i++) {
                this.selectedItems.splice(i, 1, !this.isAllItemsSelected);
            }
        },
        untrashOneItem(itemId) {
            this.$modal.open({
                parent: this,
                component: CustomDialog,
                props: {
                    icon: 'alert',
                    title: this.$i18n.get('label_warning'),
                    message: this.$i18n.get('info_warning_remove_item_from_trash'),
                    onConfirm: () => {
                        this.isLoading = true;

                        this.createEditGroup({
                            collectionID: this.collectionId,
                            object: [itemId]
                        }).then(() => {
                            let groupID = this.getGroupID();

                            this.untrashItemsInBulk({
                                collectionID: this.collectionId,
                                groupID: groupID
                            }).then(() => {
                                this.$eventBusSearch.loadItems();
                            });
                        });
                    }
                }
            });
        },
        deleteOneItem(itemId) {
            this.$modal.open({
                parent: this,
                component: CustomDialog,
                props: {
                    icon: 'alert',
                    title: this.$i18n.get('label_warning'),
                    message: this.isOnTrash ? this.$i18n.get('info_warning_item_delete') : this.$i18n.get('info_warning_item_trash'),
                    onConfirm: () => {
                        this.isLoading = true;

                        this.deleteItem({
                            itemId: itemId,
                            isPermanently: this.isOnTrash
                        }).then(() => {
                            this.$eventBusSearch.loadItems();
                        });
                    }
                }
            });
        },
        untrashSelectedItems(){
            this.$modal.open({
                parent: this,
                component: CustomDialog,
                props: {
                    icon: 'alert',
                    title: this.$i18n.get('label_warning'),
                    message: this.$i18n.get('info_warning_selected_items_remove_from_trash'),
                    onConfirm: () => {
                        this.isLoading = true;

                        this.createEditGroup({
                            collectionID: this.collectionId,
                            object: Object.keys(this.queryAllItemsSelected).length ? this.queryAllItemsSelected : this.selectedItemsIDs.filter(item => item !== false)
                        }).then(() => {
                            let groupID = this.getGroupID();

                            this.untrashItemsInBulk({
                                collectionID: this.collectionId,
                                groupID: groupID
                            }).then(() => {
                                this.$eventBusSearch.loadItems();
                            });
                        });
                    }
                }
            });
        },
        deleteSelectedItems() {
            this.$modal.open({
                parent: this,
                component: CustomDialog,
                props: {
                    icon: 'alert',
                    title: this.$i18n.get('label_warning'),
                    message: this.isOnTrash ? this.$i18n.get('info_warning_selected_items_delete') : this.$i18n.get('info_warning_selected_items_trash'),
                    onConfirm: () => {
                        this.isLoading = true;

                        this.createEditGroup({
                            collectionID: this.collectionId,
                            object: Object.keys(this.queryAllItemsSelected).length ? this.queryAllItemsSelected : this.selectedItemsIDs.filter(item => item !== false)
                        }).then(() => {
                            let groupID = this.getGroupID();

                            if (this.isOnTrash) {
                                this.deleteItemsInBulk({
                                    collectionID: this.collectionId,
                                    groupID: groupID
                                }).then(() => {
                                    this.$eventBusSearch.loadItems();
                                });
                            } else {
                                this.trashItemsInBulk({
                                    collectionID: this.collectionId,
                                    groupID: groupID
                                }).then(() => {
                                    this.$eventBusSearch.loadItems();
                                });
                            }
                        });
                    }
                }
            });
        },
        onClickItem($event, item, index) {
            if ($event.ctrlKey || $event.shiftKey) {
                this.$set(this.selectedItems, index, !this.selectedItems[index]);
            } else {
                if(this.isOnTrash){
                    this.$toast.open({
                        duration: 3000,
                        message: this.$i18n.get('info_warning_remove_from_trash_first'),
                        position: 'is-bottom',
                        type: 'is-warning'
                    });
                } else {
                    this.$router.push(this.$routerHelper.getItemPath(item.collection_id, item.id));
                }
            }
        },
        goToItemEditPage(item) {
            this.$router.push(this.$routerHelper.getItemEditPath(item.collection_id, item.id));
        },
        renderMetadata(itemMetadata, column) {

            let metadata = (itemMetadata != undefined && itemMetadata[column.slug] != undefined) ? itemMetadata[column.slug] : false;

            if (!metadata || itemMetadata == undefined) {
                return '';
            } else if (metadata.date_i18n) {
                return metadata.date_i18n;
            } else {
                return metadata.value_as_html;
            }
        },
        getLimitedDescription(description) {
            let maxCharacter = (window.innerWidth || document.documentElement.clientWidth || document.body.clientWidth) <= 480 ? 100 : 210;
            return description.length > maxCharacter ? description.substring(0, maxCharacter - 3) + '...' : description;
        }
    }
}
</script>

<style lang="scss" scoped>

    @import "../../scss/_variables.scss";
    @import "../../scss/_view-mode-masonry.scss";
    @import "../../scss/_view-mode-grid.scss";
    @import "../../scss/_view-mode-cards.scss";
    @import "../../scss/_view-mode-records.scss";

    .selection-control {
        
        padding: 6px 0px 0px 12px;
        background: white;
        height: 40px;

        .select-all {
            color: $gray4;
            font-size: 0.875rem;
            &:hover {
                color: $gray4;
            }
        }
    }

</style>


