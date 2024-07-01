<template>
    <div class="table-container">

        <!-- TODO: Remove v-if="collectionId" from this element when the bulk edit in repository is done -->
        <div
                v-if="collectionId && collection && collection.current_user_can_edit_items && collection.current_user_can_bulk_edit"
                class="selection-control">
            <div 
                    v-if="!$adminOptions.hideItemsListMultipleSelection"
                    class="field select-all is-pulled-left">
                <span>
                    <b-checkbox
                            :model-value="allItemsOnPageSelected"
                            @click.prevent="selectAllItemsOnPage()">
                        {{ $i18n.get('label_select_all_items_page') }}
                    </b-checkbox>
                </span>
                
                <span
                        v-if="totalPages > 1 && allItemsOnPageSelected && Array.isArray(items) && items.length > 1"
                        style="margin-left: 10px">
                    <b-checkbox
                            v-model="isAllItemsSelected">
                        {{ $i18n.getWithVariables('label_select_all_%s_items', [totalItems]) }}
                    </b-checkbox>
                </span>
            </div>
            <span
                    v-if="selectedItems.length && items.length > 1 && !isAllItemsSelected"
                    class="selected-items-info">
                {{ selectedItems.length != 1 ? $i18n.getWithVariables('label_%s_selected_items', [selectedItems.length]) : $i18n.get('label_one_selected_item') }}<span v-if="selectedItems.length != amountOfSelectedItemsOnThisPage && amountOfSelectedItemsOnThisPage > 0">&nbsp;({{ $i18n.getWithVariables('label_%s_on_this_page', [ amountOfSelectedItemsOnThisPage ]) }})</span>
                <button
                        class="link-style"
                        @click="cleanSelectedItems()">
                    <span class="icon">
                        <i class="tainacan-icon tainacan-icon-close" />
                    </span>
                </button>
            </span>
            <span
                    v-if="isAllItemsSelected"
                    class="selected-items-info">
                {{ $i18n.get('label_all_items_selected') }}
                <button
                        class="link-style"
                        @click="cleanSelectedItems()">
                    <span class="icon">
                        <i class="tainacan-icon tainacan-icon-close" />
                    </span>
                </button>
            </span>
            <div 
                    v-if="!$adminOptions.hideItemsListBulkActionsButton"
                    style="margin-left: auto;"
                    class="field">
                <b-dropdown
                        v-if="Array.isArray(items) && items.length > 0"
                        id="bulk-actions-dropdown"
                        :mobile-modal="true"
                        position="is-bottom-left"
                        :disabled="selectedItems.length <= 1"
                        aria-role="list"
                        trap-focus>
                    <template #trigger>
                        <button class="button is-white">
                            <span>{{ $i18n.get('label_actions_for_the_selection') }}</span>
                            <span class="icon">
                                <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-arrowdown" />
                            </span>
                        </button>
                    </template>
                    <b-dropdown-item
                            v-if="!isAllItemsSelected && selectedItems.length"
                            aria-role="listitem"
                            @click="filterBySelectedItems()">
                        {{ $i18n.get('label_view_only_selected_items') }}
                    </b-dropdown-item>
                    <b-dropdown-item
                            v-if="$route.params.collectionId && !isOnTrash"
                            aria-role="listitem"
                            @click="openBulkEditionModal()">
                        {{ $i18n.get('label_bulk_edit_selected_items') }}
                    </b-dropdown-item>
                    <b-dropdown-item
                            v-if="$route.params.collectionId && !isOnTrash"
                            aria-role="listitem"
                            @click="sequenceEditSelectedItems()">
                        {{ $i18n.get('label_sequence_edit_selected_items') }}
                    </b-dropdown-item>
                    <b-dropdown-item
                            v-if="collectionId && collection && collection.current_user_can_delete_items"
                            id="item-delete-selected-items"
                            aria-role="listitem"
                            @click="deleteSelectedItems()">
                        {{ isOnTrash ? $i18n.get('label_delete_permanently') : $i18n.get('label_send_to_trash') }}
                    </b-dropdown-item>
                    <b-dropdown-item
                            v-if="collectionId && isOnTrash"
                            aria-role="listitem"
                            @click="untrashSelectedItems();">
                        {{ $i18n.get('label_untrash_selected_items') }}
                    </b-dropdown-item>
                    <b-dropdown-item
                            :disabled="isAllItemsSelected"
                            aria-role="listitem"
                            @click="$parent.openExposersModal(selectedItems)">
                        {{ $i18n.get('label_view_selected_items_as') }}
                    </b-dropdown-item>
                </b-dropdown>
            </div>
        </div>

        <div class="table-wrapper">

            <!-- Context menu for right click selection -->
            <div
                    v-if="cursorPosY > 0 && cursorPosX > 0 && !$adminOptions.hideItemsListContextMenu"
                    class="context-menu">

                <!-- Backdrop for escaping context menu -->
                <div
                        class="context-menu-backdrop"
                        @click.left="clearContextMenu()"
                        @click.right="clearContextMenu()" />

                <b-dropdown
                        inline
                        :style="{ top: cursorPosY + 'px', left: cursorPosX + 'px' }"
                        trap-focus>
                    <b-dropdown-item
                            v-if="!isOnTrash && !$adminOptions.hideItemsListContextMenuOpenItemOption"
                            @click="openItem()">
                        {{ $i18n.getFrom('items','view_item') }}
                    </b-dropdown-item>
                    <b-dropdown-item
                            v-if="!isOnTrash && !$adminOptions.hideItemsListContextMenuOpenItemOnNewTabOption"
                            @click="openItemOnNewTab()">
                        {{ $i18n.get('label_open_item_new_tab') }}
                    </b-dropdown-item>
                    <b-dropdown-item
                            v-if="contextMenuItem != null"
                            @click="selectItem()">
                        {{ getSelectedItemChecked(contextMenuItem.id) == true ? $i18n.get('label_unselect_item') : $i18n.get('label_select_item') }}
                    </b-dropdown-item>
                    <b-dropdown-item
                            v-if="contextMenuItem != null && contextMenuItem.current_user_can_edit && !$adminOptions.hideItemsListContextMenuEditItemOption"
                            @click="goToItemEditPage(contextMenuItem)">
                        {{ $i18n.getFrom('items','edit_item') }}
                    </b-dropdown-item>
                    <b-dropdown-item
                            v-if="contextMenuItem != null && contextMenuItem.current_user_can_edit && !$adminOptions.hideItemsListContextMenuCopyItemOption"
                            @click="makeCopiesOfOneItem(contextMenuItem.id)">
                        {{ $i18n.get('label_make_copies_of_item') }}
                    </b-dropdown-item>
                    <b-dropdown-item
                            v-if="contextMenuItem != null && contextMenuItem.current_user_can_edit && !$adminOptions.hideItemsListContextMenuDeleteItemOption"
                            @click="deleteOneItem(contextMenuItem.id)">
                        {{ $i18n.get('label_delete_item') }}
                    </b-dropdown-item>
                </b-dropdown>
            </div>
            
            <!-- GRID (THUMBNAILS) VIEW MODE -->
            <div
                    v-if="viewMode == 'grid'"
                    role="list"
                    class="tainacan-grid-container"
                    :class="{ 'hide-items-selection': $adminOptions.hideItemsListSelection }">
                <div
                        v-for="(item, index) of items"
                        :key="index"
                        role="listitem"
                        :data-tainacan-item-id="item.id"
                        :class="{ 'selected-grid-item': getSelectedItemChecked(item.id) == true }"
                        class="tainacan-grid-item">

                    <!-- Checkbox -->
                    <!-- TODO: Remove v-if="collectionId" from this element when the bulk edit for repository level is implemented -->
                    <div
                            v-if="collectionId && !$adminOptions.hideItemsListSelection && ($adminOptions.itemsSingleSelectionMode || $adminOptions.itemsMultipleSelectionMode || (collection && collection.current_user_can_bulk_edit))"
                            :class="{ 'is-selecting': isSelectingItems }"
                            class="grid-item-checkbox">
                        <b-checkbox
                                v-if="!$adminOptions.itemsSingleSelectionMode"
                                :model-value="getSelectedItemChecked(item.id)"
                                @update:model-value="setSelectedItemChecked(item.id)">
                            <span class="sr-only">{{ $i18n.get('label_select_item') }}</span>
                        </b-checkbox>
                        <b-radio
                                v-else
                                v-model="singleItemSelection"
                                name="item-single-selection"
                                :native-value="item.id"
                                :aria-label="$i18n.get('label_select_item')">
                            <span class="sr-only">{{ $i18n.get('label_select_item') }}</span>
                        </b-radio>
                    </div>

                    <!-- Title -->
                    <div
                            :style="{ 'padding-left': !collectionId || !($adminOptions.itemsSingleSelectionMode || $adminOptions.itemsMultipleSelectionMode || (collection && collection.current_user_can_bulk_edit)) || $adminOptions.itemsSearchSelectionMode ? '0.5em !important' : (isOnAllItemsTabs ? '1.875em' : '2.75em') }"
                            class="metadata-title">
                        <p
                                v-tooltip="{
                                    delay: {
                                        show: 500,
                                        hide: 300,
                                    },
                                    content: item.title != undefined ? item.title : (`<span class='has-text-gray3 is-italic'>` + $i18n.get('label_value_not_provided') + `</span>`),
                                    html: true,
                                    autoHide: false,
                                    placement: 'auto-start',
                                    popperClass: ['tainacan-tooltip', 'tooltip', isRepositoryLevel ? 'tainacan-repository-tooltip' : '']
                                }"
                                @click.left="onClickItem($event, item)"
                                @click.right="onRightClickItem($event, item)">
                            <span 
                                    v-if="isOnAllItemsTabs && $statusHelper.hasIcon(item.status)"
                                    v-tooltip="{
                                        content: $i18n.get('status_' + item.status),
                                        autoHide: true,
                                        popperClass: ['tainacan-tooltip', 'tooltip', isRepositoryLevel ? 'tainacan-repository-tooltip' : ''],
                                        placement: 'auto-start'
                                    }"
                                    class="icon has-text-gray">
                                <i 
                                        class="tainacan-icon tainacan-icon-1em"
                                        :class="$statusHelper.getIcon(item.status)"
                                    />
                            </span>
                            {{ item.title != undefined ? item.title : '' }}
                        </p>
                    </div>

                    <!-- Thumbnail -->
                    <a
                            v-if="item.thumbnail != undefined"
                            class="grid-item-thumbnail"
                            @click.left="onClickItem($event, item)"
                            @click.right="onRightClickItem($event, item)">
                        <blur-hash-image
                                :width="$thumbHelper.getWidth(item['thumbnail'], 'tainacan-medium', 255)"
                                :height="$thumbHelper.getHeight(item['thumbnail'], 'tainacan-medium', 255)"
                                :hash="$thumbHelper.getBlurhashString(item['thumbnail'], 'tainacan-medium')"
                                :src="$thumbHelper.getSrc(item['thumbnail'], 'tainacan-medium', item.document_mimetype)"
                                :alt="item.thumbnail_alt ? item.thumbnail_alt : $i18n.get('label_thumbnail')"
                                :transition-duration="500"
                            />
                    </a>

                    <!-- Actions -->
                    <div
                            v-if="item.current_user_can_edit && !$adminOptions.hideItemsListActionAreas"
                            class="actions-area"
                            :label="$i18n.get('label_actions')">
                        <a
                                v-if="!isOnTrash"
                                id="button-edit"
                                :aria-label="$i18n.getFrom('items','edit_item')"
                                @click.prevent.stop="goToItemEditPage(item)">
                            <span
                                    v-tooltip="{
                                        content: $i18n.get('edit'),
                                        autoHide: true,
                                        placement: 'auto',
                                        popperClass: ['tainacan-tooltip', 'tooltip', isRepositoryLevel ? 'tainacan-repository-tooltip' : '']
                                    }"
                                    class="icon">
                                <i class="has-text-secondary tainacan-icon tainacan-icon-1-25em tainacan-icon-edit" />
                            </span>
                        </a>
                        <a
                                v-if="isOnTrash"
                                :aria-lavel="$i18n.get('label_button_untrash')"
                                @click.prevent.stop="untrashOneItem(item.id)">
                            <span
                                    v-tooltip="{
                                        content: $i18n.get('label_recover_from_trash'),
                                        autoHide: true,
                                        placement: 'auto',
                                        popperClass: ['tainacan-tooltip', 'tooltip', isRepositoryLevel ? 'tainacan-repository-tooltip' : '']
                                    }"
                                    class="icon">
                                <i class="has-text-secondary tainacan-icon tainacan-icon-1-25em tainacan-icon-undo" />
                            </span>
                        </a>
                        <a
                                v-if="item.current_user_can_delete"
                                id="button-delete" 
                                :aria-label="$i18n.get('label_button_delete')" 
                                @click.prevent.stop="deleteOneItem(item.id)">
                            <span
                                    v-tooltip="{
                                        content: isOnTrash ? $i18n.get('label_delete_permanently') : $i18n.get('delete'),
                                        autoHide: true,
                                        placement: 'auto',
                                        popperClass: ['tainacan-tooltip', 'tooltip', isRepositoryLevel ? 'tainacan-repository-tooltip' : '']
                                    }"
                                    class="icon">
                                <i
                                        :class="{ 'tainacan-icon-delete': !isOnTrash, 'tainacan-icon-deleteforever': isOnTrash }"
                                        class="has-text-secondary tainacan-icon tainacan-icon-1-25em" />
                            </span>
                        </a>
                        <a 
                                v-if="!isOnTrash"
                                id="button-open-external" 
                                :aria-label="$i18n.getFrom('items','view_item')"
                                target="_blank" 
                                :href="item.url"
                                @click.stop="">                      
                            <span 
                                    v-tooltip="{
                                        content: $i18n.get('label_item_page_on_website'),
                                        autoHide: true,
                                        popperClass: ['tainacan-tooltip', 'tooltip', isRepositoryLevel ? 'tainacan-repository-tooltip' : ''],
                                        placement: 'auto',
                                        html: true
                                    }"
                                    class="icon">
                                <i class="tainacan-icon tainacan-icon-1-125em tainacan-icon-openurl" />
                            </span>
                        </a>
                    </div>

                </div>
            </div>

            <!-- MASONRY VIEW MODE -->
            <ul
                    v-if="viewMode == 'masonry'"
                    :class="{
                        'hide-items-selection': $adminOptions.hideItemsListSelection,
                        'tainacan-masonry-container--legacy': shouldUseLegacyMasonyCols
                    }"
                    class="tainacan-masonry-container">
                <li
                        v-for="(item, index) of items"
                        :key="index"
                        :data-tainacan-item-id="item.id"
                        :class="{
                            'tainacan-masonry-grid-sizer': index == 0
                        }">
                    <div
                            :class="{
                                'selected-masonry-item': getSelectedItemChecked(item.id) == true
                            }"
                            class="tainacan-masonry-item"
                            @click.left="onClickItem($event, item)"
                            @click.right="onRightClickItem($event, item)">
                        <!-- Checkbox -->
                        <!-- TODO: Remove v-if="collectionId" from this element when the bulk edit in repository is done -->
                        <div
                                v-if="collectionId && !$adminOptions.hideItemsListSelection && ($adminOptions.itemsSingleSelectionMode || $adminOptions.itemsMultipleSelectionMode || (collection && collection.current_user_can_bulk_edit))"
                                :class="{ 'is-selecting': isSelectingItems }"
                                class="masonry-item-checkbox">
                            <label
                                    tabindex="0"
                                    :class="(!$adminOptions.itemsSingleSelectionMode ? 'b-checkbox checkbox' : 'b-radio radio') + ' is-small'">
                                <input
                                        v-if="!$adminOptions.itemsSingleSelectionMode"
                                        type="checkbox"
                                        :checked="getSelectedItemChecked(item.id)"
                                        @input="setSelectedItemChecked(item.id)">
                                <input
                                        v-else
                                        v-model="singleItemSelection"
                                        type="radio"
                                        name="item-single-selection"
                                        :value="item.id">
                                <span class="check" />
                                <span class="control-label" />
                                <span class="sr-only">{{ $i18n.get('label_select_item') }}</span>
                            </label>
                        </div>

                        <!-- Title -->
                        <div
                                :style="{
                                    'padding-left': !collectionId || !($adminOptions.itemsSingleSelectionMode || $adminOptions.itemsMultipleSelectionMode || (collection && collection.current_user_can_bulk_edit)) || $adminOptions.itemsSearchSelectionMode ? '0 !important' : (isOnAllItemsTabs ? '0.5em' : '1em')
                                }"
                                class="metadata-title">
                            <p>
                                <span 
                                        v-if="isOnAllItemsTabs && $statusHelper.hasIcon(item.status)"
                                        v-tooltip="{
                                            content: $i18n.get('status_' + item.status),
                                            autoHide: true,
                                            popperClass: ['tainacan-tooltip', 'tooltip', isRepositoryLevel ? 'tainacan-repository-tooltip' : ''],
                                            placement: 'auto-start'
                                        }"
                                        class="icon has-text-gray">
                                    <i 
                                            class="tainacan-icon tainacan-icon-1em"
                                            :class="$statusHelper.getIcon(item.status)"
                                        />
                                </span>
                                {{ item.title != undefined ? item.title : '' }}
                            </p>
                        </div>

                        <!-- Thumbnail -->
                        <blur-hash-image
                                v-if="item.thumbnail != undefined"
                                class="tainacan-masonry-item-thumbnail"
                                :width="$thumbHelper.getWidth(item['thumbnail'], shouldUseLegacyMasonyCols ? 'tainacan-medium-full' : 'tainacan-large-full', 320)"
                                :height="$thumbHelper.getHeight(item['thumbnail'], shouldUseLegacyMasonyCols ? 'tainacan-medium-full' : 'tainacan-large-full', 320)"
                                :hash="$thumbHelper.getBlurhashString(item['thumbnail'], shouldUseLegacyMasonyCols ? 'tainacan-medium-full' : 'tainacan-large-full')"
                                :src="$thumbHelper.getSrc(item['thumbnail'], shouldUseLegacyMasonyCols ? 'tainacan-medium-full' : 'tainacan-large-full', item.document_mimetype)"
                                :srcset="$thumbHelper.getSrcSet(item['thumbnail'], shouldUseLegacyMasonyCols ? 'tainacan-medium-full' : 'tainacan-large-full', item.document_mimetype)"
                                :alt="item.thumbnail_alt ? item.thumbnail_alt : $i18n.get('label_thumbnail')"
                                :transition-duration="500"
                            />

                        <!-- Actions -->
                        <div
                                v-if="item.current_user_can_edit && !$adminOptions.hideItemsListActionAreas"
                                class="actions-area"
                                :label="$i18n.get('label_actions')">
                            <a
                                    v-if="!isOnTrash"
                                    id="button-edit"
                                    :aria-label="$i18n.getFrom('items','edit_item')"
                                    @click.prevent.stop="goToItemEditPage(item)">
                                <span
                                        v-tooltip="{
                                            content: $i18n.get('edit'),
                                            autoHide: true,
                                            placement: 'auto',
                                            popperClass: ['tainacan-tooltip', 'tooltip', isRepositoryLevel ? 'tainacan-repository-tooltip' : '']
                                        }"
                                        class="icon">
                                    <i class="has-text-secondary tainacan-icon tainacan-icon-1-25em tainacan-icon-edit" />
                                </span>
                            </a>
                            <a
                                    v-if="isOnTrash"
                                    :aria-lavel="$i18n.get('label_button_untrash')"
                                    @click.prevent.stop="untrashOneItem(item.id)">
                                <span
                                        v-tooltip="{
                                            content: $i18n.get('label_recover_from_trash'),
                                            autoHide: true,
                                            placement: 'auto',
                                            popperClass: ['tainacan-tooltip', 'tooltip', isRepositoryLevel ? 'tainacan-repository-tooltip' : '']
                                        }"
                                        class="icon">
                                    <i class="has-text-secondary tainacan-icon tainacan-icon-1-25em tainacan-icon-undo" />
                                </span>
                            </a>
                            <a
                                    v-if="item.current_user_can_delete"
                                    id="button-delete" 
                                    :aria-label="$i18n.get('label_button_delete')" 
                                    @click.prevent.stop="deleteOneItem(item.id)">
                                <span
                                        v-tooltip="{
                                            content: isOnTrash ? $i18n.get('label_delete_permanently') : $i18n.get('delete'),
                                            autoHide: true,
                                            placement: 'auto',
                                            popperClass: ['tainacan-tooltip', 'tooltip', isRepositoryLevel ? 'tainacan-repository-tooltip' : '']
                                        }"
                                        class="icon">
                                    <i
                                            :class="{ 'tainacan-icon-delete': !isOnTrash, 'tainacan-icon-deleteforever': isOnTrash }"
                                            class="has-text-secondary tainacan-icon tainacan-icon-1-25em" />
                                </span>
                            </a>
                            <a 
                                    v-if="!isOnTrash"
                                    id="button-open-external" 
                                    :aria-label="$i18n.getFrom('items','view_item')"
                                    target="_blank" 
                                    :href="item.url"
                                    @click.stop="">                      
                                <span 
                                        v-tooltip="{
                                            content: $i18n.get('label_item_page_on_website'),
                                            autoHide: true,
                                            popperClass: ['tainacan-tooltip', 'tooltip', isRepositoryLevel ? 'tainacan-repository-tooltip' : ''],
                                            placement: 'auto',
                                            html: true
                                        }"
                                        class="icon">
                                    <i class="tainacan-icon tainacan-icon-1-125em tainacan-icon-openurl" />
                                </span>
                            </a>
                        </div>
                    </div>
                </li>
            </ul>

            <!-- CARDS VIEW MODE -->
            <div
                    v-if="viewMode == 'cards'"
                    role="list"
                    :class="{ 'hide-items-selection': $adminOptions.hideItemsListSelection }"
                    class="tainacan-cards-container">
                <div
                        v-for="(item, index) of items"
                        :key="index"
                        role="listitem"
                        :data-tainacan-item-id="item.id"
                        :class="{ 'selected-card': getSelectedItemChecked(item.id) == true }"
                        class="tainacan-card">

                    <!-- Checkbox -->
                    <!-- TODO: Remove v-if="collectionId" from this element when the bulk edit in repository is done -->
                    <div
                            v-if="collectionId && !$adminOptions.hideItemsListSelection && ($adminOptions.itemsSingleSelectionMode || $adminOptions.itemsMultipleSelectionMode || (collection && collection.current_user_can_bulk_edit))"
                            :class="{ 'is-selecting': isSelectingItems }"
                            class="card-checkbox">
                        <b-checkbox
                                v-if="!$adminOptions.itemsSingleSelectionMode"
                                :model-value="getSelectedItemChecked(item.id)"
                                @update:model-value="setSelectedItemChecked(item.id)">
                            <span class="sr-only">{{ $i18n.get('label_select_item') }}</span>
                        </b-checkbox>
                        <b-radio
                                v-else
                                v-model="singleItemSelection"
                                name="item-single-selection"
                                :native-value="item.id">
                            <span class="sr-only">{{ $i18n.get('label_select_item') }}</span>
                        </b-radio>
                    </div>

                    <!-- Title -->
                    <div
                            :style="{
                                'padding-left': !collectionId || !($adminOptions.itemsSingleSelectionMode || $adminOptions.itemsMultipleSelectionMode || (collection && collection.current_user_can_bulk_edit)) || $adminOptions.itemsSearchSelectionMode ? '0.5em !important' : (isOnAllItemsTabs ? '2.125em' : '2.75em'),
                            }"
                            class="metadata-title">
                        <p
                                v-tooltip="{
                                    delay: {
                                        show: 500,
                                        hide: 300,
                                    },
                                    content: item.title != undefined ? item.title : (`<span class='has-text-gray3 is-italic'>` + $i18n.get('label_value_not_provided') + `</span>`),
                                    html: true,
                                    autoHide: false,
                                    placement: 'auto-start',
                                    popperClass: ['tainacan-tooltip', 'tooltip', isRepositoryLevel ? 'tainacan-repository-tooltip' : '']
                                }"
                                @click.left="onClickItem($event, item)"
                                @click.right="onRightClickItem($event, item)">
                            <span 
                                    v-if="isOnAllItemsTabs && $statusHelper.hasIcon(item.status)"
                                    v-tooltip="{
                                        content: $i18n.get('status_' + item.status),
                                        autoHide: true,
                                        popperClass: ['tainacan-tooltip', 'tooltip', isRepositoryLevel ? 'tainacan-repository-tooltip' : ''],
                                        placement: 'auto-start'
                                    }"
                                    class="icon has-text-gray">
                                <i 
                                        class="tainacan-icon tainacan-icon-1em"
                                        :class="$statusHelper.getIcon(item.status)"
                                    />
                            </span>
                            {{ item.title != undefined ? item.title : '' }}
                        </p>
                    </div>
                    <!-- Actions -->
                    <div
                            v-if="item.current_user_can_edit && !$adminOptions.hideItemsListActionAreas"
                            class="actions-area"
                            :label="$i18n.get('label_actions')">
                        <a
                                v-if="!isOnTrash"
                                id="button-edit"
                                :aria-label="$i18n.getFrom('items','edit_item')"
                                @click.prevent.stop="goToItemEditPage(item)">
                            <span
                                    v-tooltip="{
                                        content: $i18n.get('edit'),
                                        autoHide: true,
                                        placement: 'auto',
                                        popperClass: ['tainacan-tooltip', 'tooltip', isRepositoryLevel ? 'tainacan-repository-tooltip' : '']
                                    }"
                                    class="icon">
                                <i class="has-text-secondary tainacan-icon tainacan-icon-1-25em tainacan-icon-edit" />
                            </span>
                        </a>
                        <a
                                v-if="isOnTrash"
                                :aria-lavel="$i18n.get('label_button_untrash')"
                                @click.prevent.stop="untrashOneItem(item.id)">
                            <span
                                    v-tooltip="{
                                        content: $i18n.get('label_recover_from_trash'),
                                        autoHide: true,
                                        placement: 'auto',
                                        popperClass: ['tainacan-tooltip', 'tooltip', isRepositoryLevel ? 'tainacan-repository-tooltip' : '']
                                    }"
                                    class="icon">
                                <i class="has-text-secondary tainacan-icon tainacan-icon-1-25em tainacan-icon-undo" />
                            </span>
                        </a>
                        <a
                                v-if="item.current_user_can_delete"
                                id="button-delete" 
                                :aria-label="$i18n.get('label_button_delete')" 
                                @click.prevent.stop="deleteOneItem(item.id)">
                            <span
                                    v-tooltip="{
                                        content: isOnTrash ? $i18n.get('label_delete_permanently') : $i18n.get('delete'),
                                        autoHide: true,
                                        placement: 'auto',
                                        popperClass: ['tainacan-tooltip', 'tooltip', isRepositoryLevel ? 'tainacan-repository-tooltip' : '']
                                    }"
                                    class="icon">
                                <i
                                        :class="{ 'tainacan-icon-delete': !isOnTrash, 'tainacan-icon-deleteforever': isOnTrash }"
                                        class="has-text-secondary tainacan-icon tainacan-icon-1-25em" />
                            </span>
                        </a>
                        <a 
                                v-if="!isOnTrash"
                                id="button-open-external" 
                                :aria-label="$i18n.getFrom('items','view_item')"
                                target="_blank" 
                                :href="item.url"
                                @click.stop="">                      
                            <span 
                                    v-tooltip="{
                                        content: $i18n.get('label_item_page_on_website'),
                                        autoHide: true,
                                        popperClass: ['tainacan-tooltip', 'tooltip', isRepositoryLevel ? 'tainacan-repository-tooltip' : ''],
                                        placement: 'auto',
                                        html: true
                                    }"
                                    class="icon">
                                <i class="tainacan-icon tainacan-icon-1-125em tainacan-icon-openurl" />
                            </span>
                        </a>
                    </div>

                    <!-- Remaining metadata -->
                    <div
                            class="media"
                            @click.left="onClickItem($event, item)"
                            @click.right="onRightClickItem($event, item)">
                        <div
                                v-if="!collection || (collection && collection.hide_items_thumbnail_on_lists != 'yes')"
                                class="card-thumbnail">
                            <blur-hash-image
                                    v-if="item.thumbnail != undefined"
                                    class="tainacan-masonry-item-thumbnail"
                                    :width="$thumbHelper.getWidth(item['thumbnail'], 'tainacan-medium', 120)"
                                    :height="$thumbHelper.getHeight(item['thumbnail'], 'tainacan-medium', 120)"
                                    :hash="$thumbHelper.getBlurhashString(item['thumbnail'], 'tainacan-medium')"
                                    :src="$thumbHelper.getSrc(item['thumbnail'], 'tainacan-medium', item.document_mimetype)"
                                    :alt="item.thumbnail_alt ? item.thumbnail_alt : $i18n.get('label_thumbnail')"
                                    :transition-duration="500"
                                />
                        </div>
                        

                        <div class="list-metadata media-body">
                            <!-- Description -->
                            <p
                                    v-tooltip="{
                                        delay: {
                                            show: 500,
                                            hide: 300,
                                        },
                                        content: item.description != undefined && item.description != '' ? item.description : `<span class='has-text-gray3 is-italic'>` + $i18n.get('label_description_not_provided') + `</span>`,
                                        html: true,
                                        autoHide: false,
                                        placement: 'auto-start',
                                        popperClass: ['tainacan-tooltip', 'tooltip', isRepositoryLevel ? 'tainacan-repository-tooltip' : '']
                                    }"
                                    class="metadata-description"
                                    v-html="item.description != undefined && item.description != '' ? getLimitedDescription(item.description) : `<span class='has-text-gray3 is-italic'>` + $i18n.get('label_description_not_provided') + `</span>`" />
                            <!-- Author-->
                            <p
                                    v-tooltip="{
                                        delay: {
                                            show: 500,
                                            hide: 300,
                                        },
                                        content: item.author_name != undefined ? item.author_name : '',
                                        html: false,
                                        autoHide: false,
                                        placement: 'auto-start',
                                        popperClass: ['tainacan-tooltip', 'tooltip', isRepositoryLevel ? 'tainacan-repository-tooltip' : '']
                                    }"
                                    class="metadata-author-creation">
                                {{ $i18n.get('info_created_by') + ' ' + (item.author_name != undefined ? item.author_name : '') }}
                            </p>
                            <!-- Creation Date-->
                            <p
                                    v-tooltip="{
                                        delay: {
                                            show: 500,
                                            hide: 300,
                                        },
                                        content: item.creation_date != undefined ? parseDateToNavigatorLanguage(item.creation_date) : '',
                                        html: false,
                                        autoHide: false,
                                        placement: 'auto-start',
                                        popperClass: ['tainacan-tooltip', 'tooltip', isRepositoryLevel ? 'tainacan-repository-tooltip' : '']
                                    }"
                                    class="metadata-author-creation">
                                {{ $i18n.get('info_date') + ' ' + (item.creation_date != undefined ? parseDateToNavigatorLanguage(item.creation_date) : '') }}
                            </p>
                        </div>
                    </div>

                </div>
            </div>

            <!-- RECORDS VIEW MODE -->
            <ul
                    v-if="viewMode == 'records'"
                    :class="{ 'hide-items-selection': $adminOptions.hideItemsListSelection }"
                    class="tainacan-records-container">
                <li
                        v-for="(item, index) of items"
                        :key="index"
                        :data-tainacan-item-id="item.id"
                        :class="{ 'tainacan-records-grid-sizer': index == 0 }">
                    <div 
                            :class="{ 'selected-record': getSelectedItemChecked(item.id) == true }"
                            class="tainacan-record">
                        <!-- Checkbox -->
                        <!-- TODO: Remove v-if="collectionId" from this element when the bulk edit in repository is done -->
                        <div
                                v-if="collectionId && !$adminOptions.hideItemsListSelection && ($adminOptions.itemsSingleSelectionMode || $adminOptions.itemsMultipleSelectionMode || (collection && collection.current_user_can_bulk_edit))"
                                :class="{ 'is-selecting': isSelectingItems }"
                                class="record-checkbox">
                            <label
                                    tabindex="0"
                                    :class="(!$adminOptions.itemsSingleSelectionMode ? 'b-checkbox checkbox' : 'b-radio radio') + ' is-small'">
                                <input
                                        v-if="!$adminOptions.itemsSingleSelectionMode"
                                        type="checkbox"
                                        :checked="getSelectedItemChecked(item.id)"
                                        @input="setSelectedItemChecked(item.id)">
                                <input
                                        v-else
                                        v-model="singleItemSelection"
                                        type="radio"
                                        name="item-single-selection"
                                        :value="item.id">
                                <span class="check" />
                                <span class="control-label" />
                                <span class="sr-only">{{ $i18n.get('label_select_item') }}</span>
                            </label>
                        </div>

                        <!-- Title -->
                        <div
                                class="metadata-title"
                                :style="{
                                    'padding-left': !collectionId || !($adminOptions.itemsSingleSelectionMode || $adminOptions.itemsMultipleSelectionMode || (collection && collection.current_user_can_bulk_edit)) || $adminOptions.itemsSearchSelectionMode ? '1.5em !important' : '2.75em'
                                }">
                            <span 
                                    v-if="isOnAllItemsTabs && $statusHelper.hasIcon(item.status)"
                                    v-tooltip="{
                                        content: $i18n.get('status_' + item.status),
                                        autoHide: true,
                                        popperClass: ['tainacan-tooltip', 'tooltip', isRepositoryLevel ? 'tainacan-repository-tooltip' : ''],
                                        placement: 'auto-start'
                                    }"
                                    class="icon has-text-gray">
                                <i 
                                        class="tainacan-icon tainacan-icon-1em"
                                        :class="$statusHelper.getIcon(item.status)"
                                    />
                            </span>
                            <p 
                                    v-if="collectionId != undefined && titleItemMetadatum"
                                    v-tooltip="{
                                        delay: {
                                            show: 500,
                                            hide: 300,
                                        },
                                        content: item.metadata != undefined ? renderMetadata(item.metadata, titleItemMetadatum) : '',
                                        html: true,
                                        autoHide: false,
                                        placement: 'auto-start',
                                        popperClass: ['tainacan-tooltip', 'tooltip', isRepositoryLevel ? 'tainacan-repository-tooltip' : '']
                                    }"
                                    @click.left="onClickItem($event, item)"
                                    @click.right="onRightClickItem($event, item)"
                                    v-html="item.metadata != undefined ? renderMetadata(item.metadata, titleItemMetadatum) : ''" />
                            <p
                                    v-if="collectionId == undefined && titleItemMetadatum"
                                    v-tooltip="{
                                        delay: {
                                            show: 500,
                                            hide: 300,
                                        },
                                        content: item.title != undefined ? item.title : (`<span class='has-text-gray3 is-italic'>` + $i18n.get('label_value_not_provided') + `</span>`),
                                        html: true,
                                        autoHide: false,
                                        placement: 'auto-start',
                                        popperClass: ['tainacan-tooltip', 'tooltip', isRepositoryLevel ? 'tainacan-repository-tooltip' : '']
                                    }"
                                    @click.left="onClickItem($event, item)"
                                    @click.right="onRightClickItem($event, item)"
                                    v-html="item.title != undefined ? item.title : (`<span class='has-text-gray3 is-italic'>` + $i18n.get('label_value_not_provided') + `</span>`)" />
                        </div>
                        <!-- Actions -->
                        <div
                                v-if="item.current_user_can_edit && !$adminOptions.hideItemsListActionAreas"
                                class="actions-area"
                                :label="$i18n.get('label_actions')">
                            <a
                                    v-if="!isOnTrash"
                                    id="button-edit"
                                    :aria-label="$i18n.getFrom('items','edit_item')"
                                    @click.prevent.stop="goToItemEditPage(item)">
                                <span
                                        v-tooltip="{
                                            content: $i18n.get('edit'),
                                            autoHide: true,
                                            placement: 'auto',
                                            popperClass: ['tainacan-tooltip', 'tooltip', isRepositoryLevel ? 'tainacan-repository-tooltip' : '']
                                        }"
                                        class="icon">
                                    <i class="has-text-secondary tainacan-icon tainacan-icon-1-25em tainacan-icon-edit" />
                                </span>
                            </a>
                            <a
                                    v-if="isOnTrash"
                                    :aria-lavel="$i18n.get('label_button_untrash')"
                                    @click.prevent.stop="untrashOneItem(item.id)">
                                <span
                                        v-tooltip="{
                                            content: $i18n.get('label_recover_from_trash'),
                                            autoHide: true,
                                            placement: 'auto',
                                            popperClass: ['tainacan-tooltip', 'tooltip', isRepositoryLevel ? 'tainacan-repository-tooltip' : '']
                                        }"
                                        class="icon">
                                    <i class="has-text-secondary tainacan-icon tainacan-icon-1-25em tainacan-icon-undo" />
                                </span>
                            </a>
                            <a
                                    v-if="item.current_user_can_delete"
                                    id="button-delete" 
                                    :aria-label="$i18n.get('label_button_delete')" 
                                    @click.prevent.stop="deleteOneItem(item.id)">
                                <span
                                        v-tooltip="{
                                            content: isOnTrash ? $i18n.get('label_delete_permanently') : $i18n.get('delete'),
                                            autoHide: true,
                                            placement: 'auto',
                                            popperClass: ['tainacan-tooltip', 'tooltip', isRepositoryLevel ? 'tainacan-repository-tooltip' : '']
                                        }"
                                        class="icon">
                                    <i
                                            :class="{ 'tainacan-icon-delete': !isOnTrash, 'tainacan-icon-deleteforever': isOnTrash }"
                                            class="has-text-secondary tainacan-icon tainacan-icon-1-25em" />
                                </span>
                            </a>
                            <a 
                                    v-if="!isOnTrash"
                                    id="button-open-external" 
                                    :aria-label="$i18n.getFrom('items','view_item')"
                                    target="_blank" 
                                    :href="item.url"
                                    @click.stop="">                      
                                <span 
                                        v-tooltip="{
                                            content: $i18n.get('label_item_page_on_website'),
                                            autoHide: true,
                                            popperClass: ['tainacan-tooltip', 'tooltip', isRepositoryLevel ? 'tainacan-repository-tooltip' : ''],
                                            placement: 'auto',
                                            html: true
                                        }"
                                        class="icon">
                                    <i class="tainacan-icon tainacan-icon-1-125em tainacan-icon-openurl" />
                                </span>
                            </a>
                        </div>

                        <!-- Remaining metadata -->
                        <div
                                class="media"
                                @click.left="onClickItem($event, item)"
                                @click.right="onRightClickItem($event, item)">
                            <div class="list-metadata media-body">
                                <div class="tainacan-record-thumbnail">
                                    <blur-hash-image
                                            v-if="item.thumbnail != undefined"
                                            class="tainacan-record-item-thumbnail"
                                            :width="$thumbHelper.getWidth(item['thumbnail'], 'tainacan-medium-full', 120)"
                                            :height="$thumbHelper.getHeight(item['thumbnail'], 'tainacan-medium-full', 120)"
                                            :hash="$thumbHelper.getBlurhashString(item['thumbnail'], 'tainacan-medium-full')"
                                            :src="$thumbHelper.getSrc(item['thumbnail'], 'tainacan-medium-full', item.document_mimetype)"
                                            :srcset="$thumbHelper.getSrcSet(item['thumbnail'], 'tainacan-medium-full', item.document_mimetype)"
                                            :alt="item.thumbnail_alt ? item.thumbnail_alt : $i18n.get('label_thumbnail')"
                                            :transition-duration="500"
                                            @click.left="onClickItem($event, item)"
                                            @click.right="onRightClickItem($event, item)"
                                        />
                                </div>
                                <span
                                        v-if="collectionId == undefined && descriptionItemMetadatum && item.description"
                                        class="metadata-type-textarea">
                                    <h3 class="metadata-label">{{ $i18n.get('label_description') }}</h3>
                                    <p
                                            class="metadata-value"
                                            v-html="item.description" />
                                </span>
                                <template 
                                        v-for="(column, metadatumIndex) in displayedMetadata"
                                        :key="metadatumIndex">
                                    <span
                                            v-if="renderMetadata(item.metadata, column) != '' && column.display && column.slug != 'thumbnail' && (column.metadata_type_object != undefined && (column.metadata_type_object.related_mapped_prop != 'title'))"
                                            :class="{ 'metadata-type-textarea': column.metadata_type_object != undefined && column.metadata_type_object.component == 'tainacan-textarea' }">
                                        <h3 class="metadata-label">{{ column.name }}</h3>
                                        <p
                                                class="metadata-value"
                                                v-html="renderMetadata(item.metadata, column)" />
                                    </span>
                                    <span
                                            v-if="(column.metadatum == 'row_modification' || column.metadatum == 'row_creation' || column.metadatum == 'row_author') && item[column.slug] != undefined && column.display">
                                        <h3 class="metadata-label">{{ column.name }}</h3>
                                        <p
                                                class="metadata-value"
                                                v-html="(column.metadatum == 'row_creation' || column.metadatum == 'row_modification') ? parseDateToNavigatorLanguage(item[column.slug]) : item[column.slug]" />
                                    </span>
                                </template>
                            </div>
                        </div>
                    </div>
                </li>
            </ul>

            <!-- TABLE VIEW MODE -->
            <table
                    v-if="viewMode == 'table'"
                    :class="{ 'hide-items-selection': $adminOptions.hideItemsListSelection }"
                    class="tainacan-table">
                <thead>
                    <tr>
                        <!-- Checking list -->
                        <th
                                v-if="collectionId && !$adminOptions.hideItemsListSelection && ($adminOptions.itemsSingleSelectionMode || $adminOptions.itemsMultipleSelectionMode || (collection && collection.current_user_can_bulk_edit))">
                            &nbsp;
                        <!-- nothing to show on header for checkboxes -->
                        </th>

                        <!-- Status -->
                        <th v-if="isOnAllItemsTabs">
                            &nbsp;
                        </th>

                        <!-- Displayed Metadata -->
                        <template 
                                v-for="(column, index) in displayedMetadata"
                                :key="index">
                            <th
                                    v-if="column.display"
                                    class="column-default-width"
                                    :class="{
                                        'thumbnail-cell': column.metadatum == 'row_thumbnail',
                                        'column-needed-width column-align-right' : column.metadata_type_object != undefined ? (column.metadata_type_object.primitive_type == 'float' ||
                                            column.metadata_type_object.primitive_type == 'int' ) : false,
                                        'column-small-width' : column.metadata_type_object != undefined ? (column.metadata_type_object.primitive_type == 'date' ||
                                            column.metadata_type_object.primitive_type == 'float' ||
                                            column.metadata_type_object.primitive_type == 'int') : false,
                                        'column-medium-width' : column.metadata_type_object != undefined ? (column.metadata_type_object.primitive_type == 'term' ||
                                            column.metadata_type_object.primitive_type == 'item') : false,
                                        'column-large-width' : column.metadata_type_object != undefined ? (column.metadata_type_object.primitive_type == 'long_string' ||
                                            column.metadata_type_object.primitive_type == 'compound' ||
                                            column.metadata_type_object.related_mapped_prop == 'description') : false,
                                    }">
                                <div class="th-wrap">
                                    {{ column.name }}
                                </div>
                            </th>
                        </template>
                        <th
                                v-if="items.findIndex((item) => item.current_user_can_edit || item.current_user_can_delete) >= 0"
                                class="actions-header">
                            &nbsp;
                        <!-- nothing to show on header for actions cell-->
                        </th>
                    </tr>
                </thead>
                <tbody role="list">
                    <tr
                            v-for="(item, index) of items"
                            :key="index"
                            :class="{
                                'selected-row': getSelectedItemChecked(item.id) == true,
                                'highlighted-item': highlightedItem == item.id
                            }"
                            role="listitem"
                            :data-tainacan-item-id="item.id">
                        <!-- Checking list -->
                        <!-- TODO: Remove v-if="collectionId" from this element when the bulk edit in repository is done -->
                        <td
                                v-if="collectionId && !$adminOptions.hideItemsListSelection && ($adminOptions.itemsSingleSelectionMode || $adminOptions.itemsMultipleSelectionMode || (collection && collection.current_user_can_bulk_edit))"
                                :class="{ 'is-selecting': isSelectingItems }"
                                class="checkbox-cell">
                            <b-checkbox
                                    v-if="!$adminOptions.itemsSingleSelectionMode"
                                    :model-value="getSelectedItemChecked(item.id)"
                                    @update:model-value="setSelectedItemChecked(item.id)">
                                <span class="sr-only">{{ $i18n.get('label_select_item') }}</span>
                            </b-checkbox>
                            <b-radio
                                    v-else
                                    v-model="singleItemSelection"
                                    name="item-single-selection"
                                    :native-value="item.id">
                                <span class="sr-only">{{ $i18n.get('label_select_item') }}</span>
                            </b-radio>
                        </td>
                        <td 
                                v-if="isOnAllItemsTabs"
                                class="status-cell">
                            <span 
                                    v-if="$statusHelper.hasIcon(item.status)"
                                    v-tooltip="{
                                        content: $i18n.get('status_' + item.status),
                                        autoHide: true,
                                        popperClass: ['tainacan-tooltip', 'tooltip', isRepositoryLevel ? 'tainacan-repository-tooltip' : ''],
                                        placement: 'auto-start'
                                    }"
                                    class="icon has-text-gray">
                                <i 
                                        class="tainacan-icon tainacan-icon-1em"
                                        :class="$statusHelper.getIcon(item.status)"
                                    />
                            </span>
                        </td>
                        <!-- Item Displayed Metadata -->
                        <template 
                                v-for="(column, columnIndex) in displayedMetadata"
                                :key="columnIndex">
                            <td
                                    v-if="column.display"
                                    class="column-default-width"
                                    :class="{ 'metadata-type-textarea': column.metadata_type_object != undefined && column.metadata_type_object.component == 'tainacan-textarea',
                                              'thumbnail-cell': column.metadatum == 'row_thumbnail',
                                              'column-main-content' : column.metadata_type_object != undefined ? (column.metadata_type_object.related_mapped_prop == 'title') : false,
                                              'column-needed-width column-align-right' : column.metadata_type_object != undefined ? (column.metadata_type_object.primitive_type == 'float' ||
                                                  column.metadata_type_object.primitive_type == 'int' ) : false,
                                              'column-small-width' : column.metadata_type_object != undefined ? (column.metadata_type_object.primitive_type == 'date' ||
                                                  column.metadata_type_object.primitive_type == 'int' ||
                                                  column.metadata_type_object.primitive_type == 'float') : false,
                                              'column-medium-width' : column.metadata_type_object != undefined ? (column.metadata_type_object.primitive_type == 'item' ||
                                                  column.metadata_type_object.primitive_type == 'term') : false,
                                              'column-large-width' : column.metadata_type_object != undefined ? (column.metadata_type_object.primitive_type == 'long_string' ||
                                                  column.metadata_type_object.primitive_type == 'compound' ||
                                                  column.metadata_type_object.related_mapped_prop == 'description') : false,
                                    }"
                                    @click.left="onClickItem($event, item)"
                                    @click.right="onRightClickItem($event, item)">

                                <p
                                        v-if="collectionId == undefined &&
                                            column.metadata_type_object != undefined &&
                                            column.metadata_type_object.related_mapped_prop == 'title'"
                                        v-tooltip="{
                                            delay: {
                                                show: 500,
                                                hide: 300,
                                            },
                                            content: item.title != undefined && item.title != '' ? item.title : `<span class='has-text-gray3 is-italic'>` + $i18n.get('label_value_not_provided') + `</span>`,
                                            html: true,
                                            autoHide: false,
                                            popperClass: ['tainacan-tooltip', 'tooltip', isRepositoryLevel ? 'tainacan-repository-tooltip' : ''],
                                            placement: 'auto-start'
                                        }"
                                        v-html="`<span class='sr-only'>` + column.name + ': </span>' + ((item.title != undefined && item.title != '') ? item.title : `<span class='has-text-gray3 is-italic'>` + $i18n.get('label_value_not_provided') + `</span>`)" />
                                <p
                                        v-if="collectionId == undefined &&
                                            column.metadata_type_object != undefined &&
                                            column.metadata_type_object.related_mapped_prop == 'description'"
                                        v-tooltip="{
                                            delay: {
                                                show: 500,
                                                hide: 300,
                                            },
                                            content: item.description != undefined && item.description != '' ? item.description : `<span class='has-text-gray3 is-italic'>` + $i18n.get('label_value_not_provided') + `</span>`,
                                            html: true,
                                            popperClass: ['tainacan-tooltip', 'tooltip', isRepositoryLevel ? 'tainacan-repository-tooltip' : ''],
                                            autoHide: false,
                                            placement: 'auto-start'
                                        }"
                                        v-html="`<span class='sr-only'>` + column.name + ': </span>' + ((item.description != undefined && item.description) != '' ? item.description : `<span class='has-text-gray3 is-italic'>` + $i18n.get('label_value_not_provided') + `</span>`)" />
                                <p
                                        v-if="item.metadata != undefined &&
                                            column.metadatum !== 'row_thumbnail' &&
                                            column.metadatum !== 'row_actions' &&
                                            column.metadatum !== 'row_creation' &&
                                            column.metadatum !== 'row_modification' &&
                                            column.metadatum !== 'row_author' &&
                                            column.metadatum !== 'row_title' &&
                                            column.metadatum !== 'row_description'"
                                        v-tooltip="{
                                            delay: {
                                                show: 500,
                                                hide: 300,
                                            },
                                            popperClass: [ 'tainacan-tooltip', 'tooltip', column.metadata_type_object != undefined && column.metadata_type_object.component == 'tainacan-textarea' ? 'metadata-type-textarea' : '', isRepositoryLevel ? 'tainacan-repository-tooltip' : ''],
                                            content: renderMetadata(item.metadata, column) != '' ? renderMetadata(item.metadata, column) : `<span class='has-text-gray3 is-italic'>` + $i18n.get('label_value_not_provided') + `</span>`,
                                            html: true,
                                            autoHide: false,
                                            placement: 'auto-start'
                                        }"
                                        v-html="renderMetadata(item.metadata, column) != '' ? renderMetadata(item.metadata, column) : `<span class='has-text-gray3 is-italic'>` + $i18n.get('label_value_not_provided') + `</span>`" />

                                <span 
                                        v-if="column.metadatum == 'row_thumbnail'"
                                        class="table-thumb">
                                    <blur-hash-image
                                            :width="$thumbHelper.getWidth(item['thumbnail'], 'tainacan-small', 40)"
                                            :height="$thumbHelper.getHeight(item['thumbnail'], 'tainacan-small', 40)"
                                            :hash="$thumbHelper.getBlurhashString(item['thumbnail'], 'tainacan-small')"
                                            :src="$thumbHelper.getSrc(item['thumbnail'], 'tainacan-small', item.document_mimetype)"
                                            :alt="item.thumbnail_alt ? item.thumbnail_alt : $i18n.get('label_thumbnail')"
                                            :transition-duration="500"
                                        />
                                </span>
                                <p
                                        v-if="column.metadatum == 'row_author'"
                                        v-tooltip="{
                                            delay: {
                                                show: 500,
                                                hide: 300,
                                            },
                                            content: item[column.slug],
                                            html: true,
                                            autoHide: false,
                                            placement: 'auto-start',
                                            popperClass: ['tainacan-tooltip', 'tooltip', isRepositoryLevel ? 'tainacan-repository-tooltip' : '']
                                        }">
                                    {{ item[column.slug] }}
                                </p>
                                <p
                                        v-if="column.metadatum == 'row_modification'"
                                        v-tooltip="{
                                            delay: {
                                                show: 500,
                                                hide: 300,
                                            },
                                            content: parseDateToNavigatorLanguage(item[column.slug]),
                                            html: true,
                                            autoHide: false,
                                            popperClass: ['tainacan-tooltip', 'tooltip', isRepositoryLevel ? 'tainacan-repository-tooltip' : ''],
                                            placement: 'auto-start'
                                        }">
                                    {{ parseDateToNavigatorLanguage(item[column.slug]) }}
                                </p>
                                <p
                                        v-if="column.metadatum == 'row_creation'"
                                        v-tooltip="{
                                            delay: {
                                                show: 500,
                                                hide: 300,
                                            },
                                            content: parseDateToNavigatorLanguage(item[column.slug]),
                                            html: true,
                                            autoHide: false,
                                            popperClass: ['tainacan-tooltip', 'tooltip', isRepositoryLevel ? 'tainacan-repository-tooltip' : ''],
                                            placement: 'auto-start'
                                        }">
                                    {{ parseDateToNavigatorLanguage(item[column.slug]) }}
                                </p>

                            </td>
                        </template>

                        <!-- Actions -->
                        <td 
                                v-if="(item.current_user_can_edit || item.current_user_can_delete) && !$adminOptions.hideItemsListActionAreas"
                                class="actions-cell"
                                :label="$i18n.get('label_actions')">
                            <div class="actions-container">
                                <a
                                        v-if="!isOnTrash"
                                        id="button-edit"
                                        :aria-label="$i18n.getFrom('items','edit_item')"
                                        @click.prevent.stop="goToItemEditPage(item)">
                                    <span
                                            v-tooltip="{
                                                content: $i18n.get('edit'),
                                                autoHide: true,
                                                placement: 'auto',
                                                popperClass: ['tainacan-tooltip', 'tooltip', isRepositoryLevel ? 'tainacan-repository-tooltip' : '']
                                            }"
                                            class="icon">
                                        <i class="has-text-secondary tainacan-icon tainacan-icon-1-25em tainacan-icon-edit" />
                                    </span>
                                </a>
                                <a
                                        v-if="isOnTrash"
                                        :aria-lavel="$i18n.get('label_button_untrash')"
                                        @click.prevent.stop="untrashOneItem(item.id)">
                                    <span
                                            v-tooltip="{
                                                content: $i18n.get('label_recover_from_trash'),
                                                autoHide: true,
                                                placement: 'auto',
                                                popperClass: ['tainacan-tooltip', 'tooltip', isRepositoryLevel ? 'tainacan-repository-tooltip' : '']
                                            }"
                                            class="icon">
                                        <i class="has-text-secondary tainacan-icon tainacan-icon-1-25em tainacan-icon-undo" />
                                    </span>
                                </a>
                                <a
                                        v-if="item.current_user_can_delete"
                                        id="button-delete" 
                                        :aria-label="$i18n.get('label_button_delete')" 
                                        @click.prevent.stop="deleteOneItem(item.id)">
                                    <span
                                            v-tooltip="{
                                                content: isOnTrash ? $i18n.get('label_delete_permanently') : $i18n.get('delete'),
                                                autoHide: true,
                                                placement: 'auto',
                                                popperClass: ['tainacan-tooltip', 'tooltip', isRepositoryLevel ? 'tainacan-repository-tooltip' : '']
                                            }"
                                            class="icon">
                                        <i
                                                :class="{ 'tainacan-icon-delete': !isOnTrash, 'tainacan-icon-deleteforever': isOnTrash }"
                                                class="has-text-secondary tainacan-icon tainacan-icon-1-25em" />
                                    </span>
                                </a>
                                <a 
                                        v-if="!isOnTrash"
                                        id="button-open-external" 
                                        :aria-label="$i18n.getFrom('items','view_item')"
                                        target="_blank" 
                                        :href="item.url"
                                        @click.stop="">                      
                                    <span 
                                            v-tooltip="{
                                                content: $i18n.get('label_item_page_on_website'),
                                                autoHide: true,
                                                popperClass: ['tainacan-tooltip', 'tooltip', isRepositoryLevel ? 'tainacan-repository-tooltip' : ''],
                                                placement: 'auto',
                                                html: true
                                            }"
                                            class="icon">
                                        <i class="tainacan-icon tainacan-icon-1-125em tainacan-icon-openurl" />
                                    </span>
                                </a>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>

            <!-- LIST VIEW MODE -->
            <div
                    v-if="viewMode == 'list'"
                    role="list"
                    :class="{ 'hide-items-selection': $adminOptions.hideItemsListSelection }"
                    class="tainacan-list-container">
                <div 
                        v-for="(item, index) of items"
                        :key="index"
                        role="listitem"
                        :href="item.url"
                        :data-tainacan-item-id="item.id"
                        class="tainacan-list"
                        :class="{ 'selected-list-item': getSelectedItemChecked(item.id) == true }">

                    <div
                            v-if="collectionId && !$adminOptions.hideItemsListSelection && ($adminOptions.itemsSingleSelectionMode || $adminOptions.itemsMultipleSelectionMode || (collection && collection.current_user_can_bulk_edit))"
                            :class="{ 'is-selecting': isSelectingItems }"
                            class="list-checkbox">
                        <label
                                tabindex="0"
                                :class="(!$adminOptions.itemsSingleSelectionMode ? 'b-checkbox checkbox' : 'b-radio radio') + ' is-small'">
                            <input
                                    v-if="!$adminOptions.itemsSingleSelectionMode"
                                    type="checkbox"
                                    :checked="getSelectedItemChecked(item.id)"
                                    :aria-label="$i18n.get('label_select_item')"
                                    @input="setSelectedItemChecked(item.id)">
                            <input
                                    v-else
                                    v-model="singleItemSelection"
                                    type="radio"
                                    name="item-single-selection"
                                    :value="item.id"
                                    :aria-label="$i18n.get('label_select_item')">
                            <span class="check" />
                            <span class="control-label" />
                            <span class="sr-only">{{ $i18n.get('label_select_item') }}</span>
                        </label>
                    </div>

                    <!-- Title -->
                    <div 
                            :style="{
                                'padding-left': !collectionId || !($adminOptions.itemsSingleSelectionMode || $adminOptions.itemsMultipleSelectionMode || (collection && collection.current_user_can_bulk_edit)) || $route.query.itemsSearchSelectionMode ? '1.5em !important' : (isOnAllItemsTabs ? '2.0em' : '2.75em'),    
                            }"
                            class="metadata-title">
                        <span 
                                v-if="isOnAllItemsTabs && $statusHelper.hasIcon(item.status)"
                                v-tooltip="{
                                    content: $i18n.get('status_' + item.status),
                                    autoHide: true,
                                    popperClass: ['tainacan-tooltip', 'tooltip', isRepositoryLevel ? 'tainacan-repository-tooltip' : ''],
                                    placement: 'auto-start'
                                }"
                                class="icon has-text-gray">
                            <i 
                                    class="tainacan-icon tainacan-icon-1em"
                                    :class="$statusHelper.getIcon(item.status)"
                                />
                        </span>
                        <p 
                                v-if="collectionId != undefined && titleItemMetadatum"
                                v-tooltip="{
                                    delay: {
                                        show: 500,
                                        hide: 300,
                                    },
                                    content: item.metadata != undefined ? renderMetadata(item.metadata, titleItemMetadatum) : '',
                                    html: true,
                                    autoHide: false,
                                    placement: 'auto-start',
                                    popperClass: ['tainacan-tooltip', 'tooltip', isRepositoryLevel ? 'tainacan-repository-tooltip' : '']
                                }"
                                @click.left="onClickItem($event, item)"
                                @click.right="onRightClickItem($event, item)"
                                v-html="item.metadata != undefined ? renderMetadata(item.metadata, titleItemMetadatum) : ''" />                
                        <p 
                                v-if="collectionId == undefined && titleItemMetadatum"
                                v-tooltip="{
                                    delay: {
                                        show: 500,
                                        hide: 300,
                                    },
                                    content: item.title != undefined ? item.title : (`<span class='has-text-gray3 is-italic'>` + $i18n.get('label_value_not_provided') + `</span>`),
                                    html: true,
                                    autoHide: false,
                                    placement: 'auto-start',
                                    popperClass: ['tainacan-tooltip', 'tooltip', isRepositoryLevel ? 'tainacan-repository-tooltip' : '']
                                }"
                                @click.left="onClickItem($event, item)"
                                @click.right="onRightClickItem($event, item)"
                                v-html="item.title != undefined ? item.title : (`<span class='has-text-gray3 is-italic'>` + $i18n.get('label_value_not_provided') + `</span>`)" />                 
                    </div>

                    <!-- Actions -->
                    <div
                            v-if="item.current_user_can_edit && !$adminOptions.hideItemsListActionAreas"
                            class="actions-area"
                            :label="$i18n.get('label_actions')">
                        <a
                                v-if="!isOnTrash"
                                id="button-edit"
                                :aria-label="$i18n.getFrom('items','edit_item')"
                                @click.prevent.stop="goToItemEditPage(item)">
                            <span
                                    v-tooltip="{
                                        content: $i18n.get('edit'),
                                        autoHide: true,
                                        placement: 'auto',
                                        popperClass: ['tainacan-tooltip', 'tooltip', isRepositoryLevel ? 'tainacan-repository-tooltip' : '']
                                    }"
                                    class="icon">
                                <i class="has-text-secondary tainacan-icon tainacan-icon-1-25em tainacan-icon-edit" />
                            </span>
                        </a>
                        <a
                                v-if="isOnTrash"
                                :aria-lavel="$i18n.get('label_button_untrash')"
                                @click.prevent.stop="untrashOneItem(item.id)">
                            <span
                                    v-tooltip="{
                                        content: $i18n.get('label_recover_from_trash'),
                                        autoHide: true,
                                        placement: 'auto',
                                        popperClass: ['tainacan-tooltip', 'tooltip', isRepositoryLevel ? 'tainacan-repository-tooltip' : '']
                                    }"
                                    class="icon">
                                <i class="has-text-secondary tainacan-icon tainacan-icon-1-25em tainacan-icon-undo" />
                            </span>
                        </a>
                        <a
                                v-if="item.current_user_can_delete"
                                id="button-delete" 
                                :aria-label="$i18n.get('label_button_delete')" 
                                @click.prevent.stop="deleteOneItem(item.id)">
                            <span
                                    v-tooltip="{
                                        content: isOnTrash ? $i18n.get('label_delete_permanently') : $i18n.get('delete'),
                                        autoHide: true,
                                        placement: 'auto',
                                        popperClass: ['tainacan-tooltip', 'tooltip', isRepositoryLevel ? 'tainacan-repository-tooltip' : '']
                                    }"
                                    class="icon">
                                <i
                                        :class="{ 'tainacan-icon-delete': !isOnTrash, 'tainacan-icon-deleteforever': isOnTrash }"
                                        class="has-text-secondary tainacan-icon tainacan-icon-1-25em" />
                            </span>
                        </a>
                        <a 
                                v-if="!isOnTrash"
                                id="button-open-external" 
                                :aria-label="$i18n.getFrom('items','view_item')"
                                target="_blank" 
                                :href="item.url"
                                @click.stop="">                      
                            <span 
                                    v-tooltip="{
                                        content: $i18n.get('label_item_page_on_website'),
                                        autoHide: true,
                                        popperClass: ['tainacan-tooltip', 'tooltip', isRepositoryLevel ? 'tainacan-repository-tooltip' : ''],
                                        placement: 'auto',
                                        html: true
                                    }"
                                    class="icon">
                                <i class="tainacan-icon tainacan-icon-1-125em tainacan-icon-openurl" />
                            </span>
                        </a>
                    </div>

                    <!-- Remaining metadata -->  
                    <div 
                            class="media"
                            @click.left="onClickItem($event, item)"
                            @click.right="onRightClickItem($event, item)">
                        <div 
                                v-if="item.thumbnail != undefined"
                                class="tainacan-list-thumbnail">
                            <blur-hash-image
                                    v-if="item.thumbnail != undefined"
                                    class="tainacan-list-item-thumbnail"
                                    :width="$thumbHelper.getWidth(item['thumbnail'], 'tainacan-medium-full', 120)"
                                    :height="$thumbHelper.getHeight(item['thumbnail'], 'tainacan-medium-full', 120)"
                                    :hash="$thumbHelper.getBlurhashString(item['thumbnail'], 'tainacan-medium-full')"
                                    :src="$thumbHelper.getSrc(item['thumbnail'], 'tainacan-medium-full', item.document_mimetype)"
                                    :srcset="$thumbHelper.getSrcSet(item['thumbnail'], 'tainacan-medium-full', item.document_mimetype)"
                                    :alt="item.thumbnail_alt ? item.thumbnail_alt : $i18n.get('label_thumbnail')"
                                    :transition-duration="500"
                                    @click.left="onClickItem($event, item)"
                                    @click.right="onRightClickItem($event, item)"
                                />
                        </div>
                        <div class="list-metadata media-body">
                            <span
                                    v-if="collectionId == undefined && descriptionItemMetadatum && item.description"
                                    class="metadata-type-textarea">
                                <h3 class="metadata-label">{{ $i18n.get('label_description') }}</h3>
                                <p
                                        class="metadata-value"
                                        v-html="item.description" />
                            </span>
                            <template 
                                    v-for="(column, metadatumIndex) in displayedMetadata"
                                    :key="metadatumIndex">
                                <span 
                                        v-if="renderMetadata(item.metadata, column) != '' && column.display && column.slug != 'thumbnail' && (column.metadata_type_object != undefined && (column.metadata_type_object.related_mapped_prop != 'title'))"
                                        :class="{ 'metadata-type-textarea': column.metadata_type_object.component == 'tainacan-textarea' }">
                                    <h3 class="metadata-label">{{ column.name }}</h3>
                                    <p      
                                            class="metadata-value"
                                            v-html="renderMetadata(item.metadata, column)" /> 
                                </span>
                                <span v-if="(column.metadatum == 'row_modification' || column.metadatum == 'row_creation' || column.metadatum == 'row_author') && item[column.slug] != undefined && column.display">
                                    <h3 class="metadata-label">{{ column.name }}</h3>
                                    <p
                                            class="metadata-value"
                                            v-html="(column.metadatum == 'row_creation' || column.metadatum == 'row_modification') ? parseDateToNavigatorLanguage(item[column.slug]) : item[column.slug]" />
                                </span>
                            </template>
                        </div>
                    </div>
                </div>
            </div>

            <!-- MAP VIEW MODE -->
            <div 
                    v-if="viewMode == 'map'"
                    class="tainacan-leaflet-map-container">
                <ul
                        :class="{ 'hide-items-selection': $adminOptions.hideItemsListSelection }"
                        class="tainacan-map-cards-container">
                    <li
                            v-for="item of items"
                            :key="item.id"
                            :data-tainacan-item-id="item.id"
                            @mouseenter="hoveredMapCardItemId = item.id"
                            @mouseleave="hoveredMapCardItemId = false">
                        <div 
                                :class="{
                                    'selected-map-card': getSelectedItemChecked(item.id) == true,
                                    'clicked-map-card': mapSelectedItemId == item.id,
                                    'non-located-item': !itemsLocations.some(anItemLocation => anItemLocation.item.id == item.id)
                                }"
                                class="tainacan-map-card">
                            <!-- Checkbox -->
                            <!-- TODO: Remove v-if="collectionId" from this element when the bulk edit in repository is done -->
                            <div
                                    v-if="collectionId && !$adminOptions.hideItemsListSelection && ($adminOptions.itemsSingleSelectionMode || $adminOptions.itemsMultipleSelectionMode || (collection && collection.current_user_can_bulk_edit))"
                                    :class="{ 'is-selecting': isSelectingItems }"
                                    class="map-card-checkbox">
                                <label
                                        tabindex="0"
                                        :class="(!$adminOptions.itemsSingleSelectionMode ? 'b-checkbox checkbox' : 'b-radio radio') + ' is-small'">
                                    <input
                                            v-if="!$adminOptions.itemsSingleSelectionMode"
                                            type="checkbox"
                                            :checked="getSelectedItemChecked(item.id)"
                                            @input="setSelectedItemChecked(item.id)">
                                    <input
                                            v-else
                                            v-model="singleItemSelection"
                                            type="radio"
                                            name="item-single-selection"
                                            :value="item.id">
                                    <span class="check" />
                                    <span class="control-label" />
                                    <span class="sr-only">{{ $i18n.get('label_select_item') }}</span>
                                </label>
                            </div>

                            <!-- Title -->
                            <div
                                    class="metadata-title"
                                    :style="{
                                        'cursor': !itemsLocations.some(anItemLocation => anItemLocation.item.id == item.id) ? 'auto' : 'pointer',
                                        'padding-left': !collectionId || !($adminOptions.itemsSingleSelectionMode || $adminOptions.itemsMultipleSelectionMode || (collection && collection.current_user_can_bulk_edit)) || $adminOptions.itemsSearchSelectionMode ? '1.5em !important' : '2.75em'
                                    }"
                                    @click.prevent.stop.left="showLocationsByItem(item)"
                                    @click.right="onRightClickItem($event, item)">
                                <span 
                                        v-if="isOnAllItemsTabs && $statusHelper.hasIcon(item.status)"
                                        v-tooltip="{
                                            content: $i18n.get('status_' + item.status),
                                            autoHide: true,
                                            popperClass: ['tainacan-tooltip', 'tooltip', isRepositoryLevel ? 'tainacan-repository-tooltip' : ''],
                                            placement: 'auto-start'
                                        }"
                                        class="icon has-text-gray">
                                    <i 
                                            class="tainacan-icon tainacan-icon-1em"
                                            :class="$statusHelper.getIcon(item.status)"
                                        />
                                </span>
                                <p 
                                        v-if="collectionId != undefined && titleItemMetadatum"
                                        v-tooltip="{
                                            delay: {
                                                show: 500,
                                                hide: 300,
                                            },
                                            content: item.metadata != undefined ? renderMetadata(item.metadata, titleItemMetadatum) : '',
                                            html: true,
                                            autoHide: false,
                                            placement: 'auto-start',
                                            popperClass: ['tainacan-tooltip', 'tooltip', isRepositoryLevel ? 'tainacan-repository-tooltip' : '']
                                        }"
                                        v-html="item.metadata != undefined ? renderMetadata(item.metadata, titleItemMetadatum) : (`<span class='has-text-gray3 is-italic'>` + $i18n.get('label_value_not_provided') + `</span>`)" />
                                <p
                                        v-if="collectionId == undefined && titleItemMetadatum"
                                        v-tooltip="{
                                            delay: {
                                                show: 500,
                                                hide: 300,
                                            },
                                            content: item.title != undefined ? item.title : (`<span class='has-text-gray3 is-italic'>` + $i18n.get('label_value_not_provided') + `</span>`),
                                            html: true,
                                            autoHide: false,
                                            placement: 'auto-start',
                                            popperClass: ['tainacan-tooltip', 'tooltip', isRepositoryLevel ? 'tainacan-repository-tooltip' : '']
                                        }"
                                        v-html="item.title != undefined ? item.title : (`<span class='has-text-gray3 is-italic'>` + $i18n.get('label_value_not_provided') + `</span>`)" />
                                <div class="tainacan-map-card-thumbnail">
                                    <blur-hash-image
                                            v-if="item.thumbnail != undefined"
                                            class="tainacan-map-card-item-thumbnail"
                                            :width="$thumbHelper.getWidth(item['thumbnail'], 'tainacan-small', 40)"
                                            :height="$thumbHelper.getHeight(item['thumbnail'], 'tainacan-small', 40)"
                                            :hash="$thumbHelper.getBlurhashString(item['thumbnail'], 'tainacan-small')"
                                            :src="$thumbHelper.getSrc(item['thumbnail'], 'tainacan-small', item.document_mimetype)"
                                            :srcset="$thumbHelper.getSrcSet(item['thumbnail'], 'tainacan-small', item.document_mimetype)"
                                            :alt="item.thumbnail_alt ? item.thumbnail_alt : $i18n.get('label_thumbnail')"
                                            :transition-duration="500"
                                        />
                                </div>
                            </div>
                            <!-- Actions -->
                            <div
                                    v-if="!$adminOptions.hideItemsListActionAreas"
                                    class="actions-area"
                                    :label="$i18n.get('label_actions')">
                                <a
                                        v-if="!isOnTrash && item.current_user_can_edit"
                                        id="button-edit"
                                        :aria-label="$i18n.getFrom('items','edit_item')"
                                        @click.prevent.stop="goToItemEditPage(item)">
                                    <span
                                            v-tooltip="{
                                                content: $i18n.get('edit'),
                                                autoHide: true,
                                                placement: 'auto',
                                                popperClass: ['tainacan-tooltip', 'tooltip', isRepositoryLevel ? 'tainacan-repository-tooltip' : '']
                                            }"
                                            class="icon">
                                        <i class="has-text-secondary tainacan-icon tainacan-icon-1-25em tainacan-icon-edit" />
                                    </span>
                                </a>
                                <a
                                        v-if="isOnTrash && item.current_user_can_edit"
                                        :aria-lavel="$i18n.get('label_button_untrash')"
                                        @click.prevent.stop="untrashOneItem(item.id)">
                                    <span
                                            v-tooltip="{
                                                content: $i18n.get('label_recover_from_trash'),
                                                autoHide: true,
                                                placement: 'auto',
                                                popperClass: ['tainacan-tooltip', 'tooltip', isRepositoryLevel ? 'tainacan-repository-tooltip' : '']
                                            }"
                                            class="icon">
                                        <i class="has-text-secondary tainacan-icon tainacan-icon-1-25em tainacan-icon-undo" />
                                    </span>
                                </a>
                                <a
                                        v-if="item.current_user_can_delete && item.current_user_can_edit"
                                        id="button-delete" 
                                        :aria-label="$i18n.get('label_button_delete')" 
                                        @click.prevent.stop="deleteOneItem(item.id)">
                                    <span
                                            v-tooltip="{
                                                content: isOnTrash ? $i18n.get('label_delete_permanently') : $i18n.get('delete'),
                                                autoHide: true,
                                                placement: 'auto',
                                                popperClass: ['tainacan-tooltip', 'tooltip', isRepositoryLevel ? 'tainacan-repository-tooltip' : '']
                                            }"
                                            class="icon">
                                        <i
                                                :class="{ 'tainacan-icon-delete': !isOnTrash, 'tainacan-icon-deleteforever': isOnTrash }"
                                                class="has-text-secondary tainacan-icon tainacan-icon-1-25em" />
                                    </span>
                                </a>
                            </div>
                        </div>
                    </li>
                </ul>
                <l-map 
                        :id="'tainacan-admin-view-mode-map'"
                        :ref="'tainacan-admin-view-mode-map'"
                        style="height: 60vh; width: 100%;"
                        :zoom="5"
                        :center="[-14.4086569, -51.31668]"
                        :zoom-animation="true"
                        :options="{
                            name: 'tainacan-admin-view-mode-map',
                            zoomControl: false
                        }"
                        @ready="onMapReady()"
                        @click="clearSelectedMarkers()">
                    <l-tile-layer 
                            :url="mapTileUrl" 
                            :attribution="mapTileAttribution" />
                    <l-marker 
                            v-for="(itemLocation, index) of itemsLocations"
                            :key="index"
                            :lat-lng="itemLocation.location"
                            :opacity="(mapSelectedItemId && itemLocation.item.id != mapSelectedItemId) ? 0.25 : 1.0"
                            @click="showItemByLocation(index)">
                        <l-icon 
                                :icon-retina-url="mapIconRetinaUrl"
                                :icon-url="mapIconUrl"
                                :shadow-url="mapIconShadowUrl"
                                :icon-size="(itemLocation.item.id == hoveredMapCardItemId || itemLocation.item.id == mapSelectedItemId) ? [25, 41] : [16, 28]"
                                :shadow-size="(itemLocation.item.id == hoveredMapCardItemId || itemLocation.item.id == mapSelectedItemId) ? [41, 41] : [28, 28]"
                                :icon-anchor="(itemLocation.item.id == hoveredMapCardItemId || itemLocation.item.id == mapSelectedItemId) ? [12, 41] : [8, 28]"
                                :tooltip-anchor="(itemLocation.item.id == hoveredMapCardItemId || itemLocation.item.id == mapSelectedItemId) ? [16, -28] : [8, -21]"
                                :popup-anchor="(itemLocation.item.id == hoveredMapCardItemId || itemLocation.item.id == mapSelectedItemId) ? [1, -34] : [1, -25]" />
                        <l-tooltip>
                            <div
                                    v-for="(column, columnIndex) in displayedMetadata"
                                    :key="columnIndex">
                                <div 
                                        v-if="collectionId != undefined && column.display && column.metadata_type_object != undefined && (column.metadata_type_object.related_mapped_prop == 'title')"
                                        style="font-weight: bold;"
                                        v-html="(itemLocation.item.metadata != undefined ? renderMetadata(itemLocation.item.metadata, column) : '') + getMultivalueIndicator(itemLocation)" />
                                <div 
                                        v-if="collectionId != undefined && column.display && column.metadata_type == 'Tainacan\\Metadata_Types\\Compound' && selectedGeocoordinateMetadatum.parent == column.id"
                                        v-html="itemLocation.item.metadata != undefined ? renderMetadata(itemLocation.item.metadata, column, itemLocation.multivalueIndex) : ''" />
                            </div>
                        </l-tooltip>
                    </l-marker>
                    <l-control-zoom position="bottomright" />
                    <l-control
                            :disable-scroll-propagation="true"
                            :disable-click-propagation="true"
                            position="topleft">
                        <div class="geocoordinate-panel">
                            <div 
                                    v-if="Object.keys(geocoordinateMetadata).length"
                                    class="geocoordinate-panel--input">
                                <label>{{ $i18n.get('label_showing_locations_for') }}&nbsp;</label>
                                <div 
                                        id="tainacan-select-geocoordinate-metatum"
                                        class="control">
                                    <span class="select">
                                        <select
                                                v-model="selectedGeocoordinateMetadatumId"
                                                :placeholder="$i18n.get('instruction_select_geocoordinate_metadatum')">
                                            <option
                                                    v-for="(geocoordinateMetadatum, geocoordinateMetadatumId) in geocoordinateMetadata"
                                                    :key="geocoordinateMetadatum.id"
                                                    role="button"
                                                    :class="{ 'is-active': selectedGeocoordinateMetadatumId == geocoordinateMetadatumId }"
                                                    :value="geocoordinateMetadatumId"
                                                    @click="onChangeSelectedGeocoordinateMetadatum(geocoordinateMetadatumId)">
                                                {{ geocoordinateMetadatum.name }}
                                            </option>
                                        </select>
                                    </span>
                                </div>
                            </div>
                            <section 
                                    v-else
                                    class="section">
                                <div class="content has-text-grey has-text-centered">
                                    <p style="margin-bottom: 0px">
                                        <span class="icon is-large">
                                            <i>
                                                <svg
                                                        xmlns="http://www.w3.org/2000/svg"
                                                        viewBox="0 0 24 24"
                                                        fill="var(--tainacan-info-color, #505253)"
                                                        width="2.875em"
                                                        height="2.875em">
                                                    <path d="M15,19L9,16.89V5L15,7.11M20.5,3C20.44,3 20.39,3 20.34,3L15,5.1L9,3L3.36,4.9C3.15,4.97 3,5.15 3,5.38V20.5A0.5,0.5 0 0,0 3.5,21C3.55,21 3.61,21 3.66,20.97L9,18.9L15,21L20.64,19.1C20.85,19 21,18.85 21,18.62V3.5A0.5,0.5 0 0,0 20.5,3Z" />
                                                </svg>
                                            </i>
                                        </span>
                                    </p>
                                    <p>{{ $i18n.get('info_empty_geocoordinate_metadata_list') }}</p>
                                </div>
                            </section>
                        </div>
                    </l-control>
                    <l-control
                            v-if="selectedMarkerIndexes.length || mapSelectedItemId"
                            :disable-scroll-propagation="true"
                            :disable-click-propagation="true"
                            position="topleft"
                            class="tainacan-records-container tainacan-records-container--map">
                        <button 
                                v-tooltip="{
                                    content: $i18n.get('label_clean'),
                                    autoHide: true,
                                    popperClass: ['tainacan-tooltip', 'tooltip', isRepositoryLevel ? 'tainacan-repository-tooltip' : ''],
                                    placement: 'auto-start'
                                }"
                                :aria-label="$i18n.get('label_clean')"
                                class="tainacan-records-close-button"
                                @click="clearSelectedMarkers()">
                            <span class="icon">
                                <i class="tainacan-icon tainacan-icon-close" />
                            </span>
                        </button>
                        <transition-group 
                                tag="ul"
                                name="appear">
                            <li 
                                    v-for="item of items.filter(anItem => mapSelectedItemId == anItem.id)"
                                    :key="item.id"
                                    :data-tainacan-item-id="item.id">
                                <div class="tainacan-record">

                                    <!-- Title -->
                                    <div class="metadata-title">
                                        <span 
                                                v-if="isOnAllItemsTabs && $statusHelper.hasIcon(item.status)"
                                                v-tooltip="{
                                                    content: $i18n.get('status_' + item.status),
                                                    autoHide: true,
                                                    popperClass: ['tainacan-tooltip', 'tooltip', isRepositoryLevel ? 'tainacan-repository-tooltip' : ''],
                                                    placement: 'auto-start'
                                                }"
                                                class="icon has-text-gray">
                                            <i 
                                                    class="tainacan-icon tainacan-icon-1em"
                                                    :class="$statusHelper.getIcon(item.status)"
                                                />
                                        </span>
                                        <p 
                                                v-if="collectionId != undefined && titleItemMetadatum"
                                                v-tooltip="{
                                                    delay: {
                                                        show: 500,
                                                        hide: 300,
                                                    },
                                                    content: item.metadata != undefined ? renderMetadata(item.metadata, titleItemMetadatum) : '',
                                                    html: true,
                                                    autoHide: false,
                                                    placement: 'auto-start',
                                                    popperClass: ['tainacan-tooltip', 'tooltip', isRepositoryLevel ? 'tainacan-repository-tooltip' : '']
                                                }"
                                                @click.left="onClickItem($event, item)"
                                                @click.right="onRightClickItem($event, item)"
                                                v-html="item.metadata != undefined ? renderMetadata(item.metadata, titleItemMetadatum) : ''" />
                                        <p
                                                v-if="collectionId == undefined && titleItemMetadatum"
                                                v-tooltip="{
                                                    delay: {
                                                        show: 500,
                                                        hide: 300,
                                                    },
                                                    content: item.title != undefined ? item.title : (`<span class='has-text-gray3 is-italic'>` + $i18n.get('label_value_not_provided') + `</span>`),
                                                    html: true,
                                                    autoHide: false,
                                                    placement: 'auto-start',
                                                    popperClass: ['tainacan-tooltip', 'tooltip', isRepositoryLevel ? 'tainacan-repository-tooltip' : '']
                                                }"
                                                @click.left="onClickItem($event, item)"
                                                @click.right="onRightClickItem($event, item)"
                                                v-html="item.title != undefined ? item.title : (`<span class='has-text-gray3 is-italic'>` + $i18n.get('label_value_not_provided') + `</span>`)" />
                                    </div>

                                    <!-- Actions -->
                                    <div
                                            v-if="!$adminOptions.hideItemsListActionAreas"
                                            class="actions-area"
                                            :label="$i18n.get('label_actions')">
                                        <a
                                                v-if="itemsLocations.some(anItemLocation => anItemLocation.item.id == item.id)"
                                                id="button-show-location"
                                                :aria-label="$i18n.get('label_show_item_location_on_map')"
                                                @click.prevent.stop="showLocationsByItem(item)">
                                            <span
                                                    v-if="selectedGeocoordinateMetadatum.slug"
                                                    v-tooltip="{
                                                        content: $i18n.get('label_show_item_location_on_map'),
                                                        autoHide: true,
                                                        placement: 'auto',
                                                        popperClass: ['tainacan-tooltip', 'tooltip', isRepositoryLevel ? 'tainacan-repository-tooltip' : '']
                                                    }"
                                                    class="icon">
                                                <svg
                                                        style="width:24px;height:24px"
                                                        viewBox="0 0 24 24">
                                                    <path
                                                            fill="currentColor"
                                                            d="M12,8A4,4 0 0,1 16,12A4,4 0 0,1 12,16A4,4 0 0,1 8,12A4,4 0 0,1 12,8M3.05,13H1V11H3.05C3.5,6.83 6.83,3.5 11,3.05V1H13V3.05C17.17,3.5 20.5,6.83 20.95,11H23V13H20.95C20.5,17.17 17.17,20.5 13,20.95V23H11V20.95C6.83,20.5 3.5,17.17 3.05,13M12,5A7,7 0 0,0 5,12A7,7 0 0,0 12,19A7,7 0 0,0 19,12A7,7 0 0,0 12,5Z" />
                                                </svg>
                                            </span>
                                        </a>
                                        <a
                                                v-if="!isOnTrash && item.current_user_can_edit"
                                                id="button-edit"
                                                :aria-label="$i18n.getFrom('items','edit_item')"
                                                @click.prevent.stop="goToItemEditPage(item)">
                                            <span
                                                    v-tooltip="{
                                                        content: $i18n.get('edit'),
                                                        autoHide: true,
                                                        placement: 'auto',
                                                        popperClass: ['tainacan-tooltip', 'tooltip', isRepositoryLevel ? 'tainacan-repository-tooltip' : '']
                                                    }"
                                                    class="icon">
                                                <i class="has-text-secondary tainacan-icon tainacan-icon-1-25em tainacan-icon-edit" />
                                            </span>
                                        </a>
                                        <a
                                                v-if="isOnTrash && item.current_user_can_edit"
                                                :aria-lavel="$i18n.get('label_button_untrash')"
                                                @click.prevent.stop="untrashOneItem(item.id)">
                                            <span
                                                    v-tooltip="{
                                                        content: $i18n.get('label_recover_from_trash'),
                                                        autoHide: true,
                                                        placement: 'auto',
                                                        popperClass: ['tainacan-tooltip', 'tooltip', isRepositoryLevel ? 'tainacan-repository-tooltip' : '']
                                                    }"
                                                    class="icon">
                                                <i class="has-text-secondary tainacan-icon tainacan-icon-1-25em tainacan-icon-undo" />
                                            </span>
                                        </a>
                                        <a
                                                v-if="item.current_user_can_delete && item.current_user_can_edit"
                                                id="button-delete" 
                                                :aria-label="$i18n.get('label_button_delete')" 
                                                @click.prevent.stop="deleteOneItem(item.id)">
                                            <span
                                                    v-tooltip="{
                                                        content: isOnTrash ? $i18n.get('label_delete_permanently') : $i18n.get('delete'),
                                                        autoHide: true,
                                                        placement: 'auto',
                                                        popperClass: ['tainacan-tooltip', 'tooltip', isRepositoryLevel ? 'tainacan-repository-tooltip' : '']
                                                    }"
                                                    class="icon">
                                                <i
                                                        :class="{ 'tainacan-icon-delete': !isOnTrash, 'tainacan-icon-deleteforever': isOnTrash }"
                                                        class="has-text-secondary tainacan-icon tainacan-icon-1-25em" />
                                            </span>
                                        </a>
                                        <a 
                                                v-if="!isOnTrash"
                                                id="button-open-external" 
                                                :aria-label="$i18n.getFrom('items','view_item')"
                                                target="_blank" 
                                                :href="item.url"
                                                @click.stop="">                      
                                            <span 
                                                    v-tooltip="{
                                                        content: $i18n.get('label_item_page_on_website'),
                                                        autoHide: true,
                                                        popperClass: ['tainacan-tooltip', 'tooltip', isRepositoryLevel ? 'tainacan-repository-tooltip' : ''],
                                                        placement: 'auto',
                                                        html: true
                                                    }"
                                                    class="icon">
                                                <i class="tainacan-icon tainacan-icon-1-125em tainacan-icon-openurl" />
                                            </span>
                                        </a>
                                    </div>

                                    <!-- Remaining metadata -->
                                    <div
                                            class="media"
                                            @click.left="onClickItem($event, item)"
                                            @click.right="onRightClickItem($event, item)">
                                        <div class="list-metadata media-body">
                                            <div class="tainacan-record-thumbnail">
                                                <blur-hash-image
                                                        v-if="item.thumbnail != undefined"
                                                        class="tainacan-record-item-thumbnail"
                                                        :width="$thumbHelper.getWidth(item['thumbnail'], 'tainacan-medium-full', 120)"
                                                        :height="$thumbHelper.getHeight(item['thumbnail'], 'tainacan-medium-full', 120)"
                                                        :hash="$thumbHelper.getBlurhashString(item['thumbnail'], 'tainacan-medium-full')"
                                                        :src="$thumbHelper.getSrc(item['thumbnail'], 'tainacan-medium-full', item.document_mimetype)"
                                                        :srcset="$thumbHelper.getSrcSet(item['thumbnail'], 'tainacan-medium-full', item.document_mimetype)"
                                                        :alt="item.thumbnail_alt ? item.thumbnail_alt : $i18n.get('label_thumbnail')"
                                                        :transition-duration="500"
                                                        @click.left="onClickItem($event, item)"
                                                        @click.right="onRightClickItem($event, item)"
                                                    />
                                            </div>
                                            <span
                                                    v-if="collectionId == undefined"
                                                    class="metadata-type-textarea">
                                                <h3 class="metadata-label">{{ $i18n.get('label_description') }}</h3>
                                                <p
                                                        class="metadata-value"
                                                        v-html="item.description != undefined ? item.description : ''" />
                                            </span>
                                            <template 
                                                    v-for="(column, metadatumIndex) in displayedMetadata"
                                                    :key="metadatumIndex">
                                                <span
                                                        v-if="renderMetadata(item.metadata, column) != '' &&
                                                            column.display && column.slug != 'thumbnail' &&
                                                            column.metadata_type_object != undefined && 
                                                            (column.metadata_type_object.related_mapped_prop != 'title') &&
                                                            (column.metadata_type != 'Tainacan\\Metadata_Types\\GeoCoordinate')"
                                                        :class="{ 'metadata-type-textarea': column.metadata_type_object != undefined && column.metadata_type_object.component == 'tainacan-textarea' }">
                                                    <h3 class="metadata-label">{{ column.name }}</h3>
                                                    <p
                                                            class="metadata-value"
                                                            v-html="renderMetadata(item.metadata, column)" />
                                                </span>
                                                <span v-if="(column.metadatum == 'row_modification' || column.metadatum == 'row_creation' || column.metadatum == 'row_author') && item[column.slug] != undefined">
                                                    <h3 class="metadata-label">{{ column.name }}</h3>
                                                    <p
                                                            class="metadata-value"
                                                            v-html="(column.metadatum == 'row_creation' || column.metadatum == 'row_modification') ? parseDateToNavigatorLanguage(item[column.slug]) : item[column.slug]" />
                                                </span>
                                            </template>
                                        </div>
                                    </div>
                                </div>
                            </li>
                        </transition-group>
                    </l-control>
                </l-map>
            </div>

            <!-- MOSAIC VIEW MODE -->
            <ul 
                    v-if="viewMode == 'mosaic'"
                    :class="{
                        'hide-items-selection': $adminOptions.hideItemsListSelection
                    }"
                    class="tainacan-mosaic-container">
                <li
                        v-for="(item, index) of items"
                        :key="index"
                        :data-tainacan-item-id="item.id"
                        :style="{
                            '--tainacan-mosaic-item-width': getAcceptableWidthBasedOnRatio(item['thumbnail'], 'tainacan-large-full', 300),
                            '--tainacan-mosaic-item-height': $thumbHelper.getHeight(item['thumbnail'], 'tainacan-large-full', 300)
                        }">
                    <div 
                            :class="{
                                'selected-mosaic-item': getSelectedItemChecked(item.id) == true
                            }"
                            class="tainacan-mosaic-item" 
                            @click.left="onClickItem($event, item)"
                            @click.right="onRightClickItem($event, item)">

                        <!-- Thumbnail -->
                        <blur-hash-image
                                v-if="item.thumbnail != undefined"
                                class="tainacan-mosaic-item-thumbnail"
                                :width="$thumbHelper.getWidth(item['thumbnail'], 'tainacan-large-full', 320)"
                                :height="$thumbHelper.getHeight(item['thumbnail'], 'tainacan-large-full', 320)"
                                :hash="$thumbHelper.getBlurhashString(item['thumbnail'], 'tainacan-large-full')"
                                :src="$thumbHelper.getSrc(item['thumbnail'], 'tainacan-large-full', item.document_mimetype)"
                                :srcset="$thumbHelper.getSrcSet(item['thumbnail'], 'tainacan-large-full', item.document_mimetype)"
                                :alt="item.thumbnail_alt ? item.thumbnail_alt : $i18n.get('label_thumbnail')"
                                :transition-duration="500"
                            />

                        <!-- Title -->
                        <div
                                class="metadata-title"
                                :style="{ 'padding-left': collectionId && !$adminOptions.hideItemsListSelection && ($adminOptions.itemsSingleSelectionMode || $adminOptions.itemsMultipleSelectionMode || (collection && collection.current_user_can_bulk_edit)) ? '1.75em' : '1.0em' }">
                            <!-- Checkbox -->
                            <!-- TODO: Remove v-if="collectionId" from this element when the bulk edit in repository is done -->
                            <div
                                    v-if="collectionId && !$adminOptions.hideItemsListSelection && ($adminOptions.itemsSingleSelectionMode || $adminOptions.itemsMultipleSelectionMode || (collection && collection.current_user_can_bulk_edit))"
                                    :class="{ 'is-selecting': isSelectingItems }"
                                    class="mosaic-item-checkbox">
                                <label
                                        tabindex="0"
                                        :class="(!$adminOptions.itemsSingleSelectionMode ? 'b-checkbox checkbox' : 'b-radio radio') + ' is-small'">
                                    <input
                                            v-if="!$adminOptions.itemsSingleSelectionMode"
                                            type="checkbox"
                                            :checked="getSelectedItemChecked(item.id)"
                                            @input="setSelectedItemChecked(item.id)">
                                    <input
                                            v-else
                                            v-model="singleItemSelection"
                                            type="radio"
                                            name="item-single-selection"
                                            :value="item.id">
                                    <span class="check" />
                                    <span class="control-label" />
                                    <span class="sr-only">{{ $i18n.get('label_select_item') }}</span>
                                </label>
                            </div>
                            <p 
                                    v-tooltip="{
                                        delay: {
                                            show: 750,
                                            hide: 100,
                                        },
                                        content: item.title != undefined ? item.title : '',
                                        html: true,
                                        placement: 'auto-start',
                                        popperClass: ['tainacan-tooltip', 'tooltip', isRepositoryLevel ? 'tainacan-repository-tooltip' : '']
                                    }">
                                <span 
                                        v-if="isOnAllItemsTabs && $statusHelper.hasIcon(item.status)"
                                        v-tooltip="{
                                            content: $i18n.get('status_' + item.status),
                                            autoHide: true,
                                            popperClass: ['tainacan-tooltip', 'tooltip', isRepositoryLevel ? 'tainacan-repository-tooltip' : ''],
                                            placement: 'auto-start'
                                        }"
                                        class="icon has-text-gray">
                                    <i 
                                            class="tainacan-icon tainacan-icon-1em"
                                            :class="$statusHelper.getIcon(item.status)"
                                        />
                                </span>
                                {{ item.title != undefined ? item.title : '' }}
                            </p>
                        </div>

                        <!-- Actions -->
                        <div
                                v-if="item.current_user_can_edit && !$adminOptions.hideItemsListActionAreas"
                                class="actions-area"
                                :label="$i18n.get('label_actions')">
                            <a
                                    v-if="!isOnTrash"
                                    id="button-edit"
                                    :aria-label="$i18n.getFrom('items','edit_item')"
                                    @click.prevent.stop="goToItemEditPage(item)">
                                <span
                                        v-tooltip="{
                                            content: $i18n.get('edit'),
                                            autoHide: true,
                                            placement: 'auto',
                                            popperClass: ['tainacan-tooltip', 'tooltip', isRepositoryLevel ? 'tainacan-repository-tooltip' : '']
                                        }"
                                        class="icon">
                                    <i class="has-text-secondary tainacan-icon tainacan-icon-1-25em tainacan-icon-edit" />
                                </span>
                            </a>
                            <a
                                    v-if="isOnTrash"
                                    :aria-lavel="$i18n.get('label_button_untrash')"
                                    @click.prevent.stop="untrashOneItem(item.id)">
                                <span
                                        v-tooltip="{
                                            content: $i18n.get('label_recover_from_trash'),
                                            autoHide: true,
                                            placement: 'auto',
                                            popperClass: ['tainacan-tooltip', 'tooltip', isRepositoryLevel ? 'tainacan-repository-tooltip' : '']
                                        }"
                                        class="icon">
                                    <i class="has-text-secondary tainacan-icon tainacan-icon-1-25em tainacan-icon-undo" />
                                </span>
                            </a>
                            <a
                                    v-if="item.current_user_can_delete"
                                    id="button-delete" 
                                    :aria-label="$i18n.get('label_button_delete')" 
                                    @click.prevent.stop="deleteOneItem(item.id)">
                                <span
                                        v-tooltip="{
                                            content: isOnTrash ? $i18n.get('label_delete_permanently') : $i18n.get('delete'),
                                            autoHide: true,
                                            placement: 'auto',
                                            popperClass: ['tainacan-tooltip', 'tooltip', isRepositoryLevel ? 'tainacan-repository-tooltip' : '']
                                        }"
                                        class="icon">
                                    <i
                                            :class="{ 'tainacan-icon-delete': !isOnTrash, 'tainacan-icon-deleteforever': isOnTrash }"
                                            class="has-text-secondary tainacan-icon tainacan-icon-1-25em" />
                                </span>
                            </a>
                            <a 
                                    v-if="!isOnTrash"
                                    id="button-open-external" 
                                    :aria-label="$i18n.getFrom('items','view_item')"
                                    target="_blank" 
                                    :href="item.url"
                                    @click.stop="">                      
                                <span 
                                        v-tooltip="{
                                            content: $i18n.get('label_item_page_on_website'),
                                            autoHide: true,
                                            popperClass: ['tainacan-tooltip', 'tooltip', isRepositoryLevel ? 'tainacan-repository-tooltip' : ''],
                                            placement: 'auto',
                                            html: true
                                        }"
                                        class="icon">
                                    <i class="tainacan-icon tainacan-icon-1-125em tainacan-icon-openurl" />
                                </span>
                            </a>
                        </div>
                    </div>
                </li>
            </ul>
        </div>
    </div>
</template>

<script>
import { nextTick, defineAsyncComponent } from 'vue';
import { mapActions, mapGetters } from 'vuex';

import CustomDialog from '../other/custom-dialog.vue';

import Masonry from 'masonry-layout';
import { dateInter } from "../../js/mixins";

import { LMap, LIcon, LTooltip, LTileLayer, LMarker, LControl, LControlZoom } from '@vue-leaflet/vue-leaflet';
import 'leaflet/dist/leaflet.css';
import { latLng } from 'leaflet';
import iconUrl from 'leaflet/dist/images/marker-icon.png';
import iconRetinaUrl from 'leaflet/dist/images/marker-icon-2x.png';
import shadowUrl from 'leaflet/dist/images/marker-shadow.png';
import * as LeafletActiveArea from 'leaflet-active-area';

export default {
    name: 'ItemsList',
    components: {
        LMap,
        LIcon,
        LTooltip,
        LTileLayer,
        LMarker,
        LControl,
        LControlZoom
    },
    mixins: [ dateInter ],
    props: {
        collectionId: undefined,
        displayedMetadata: Array,
        items: Array,
        isLoading: false,
        isOnTrash: false,
        totalItems: Number,
        viewMode: 'card',
        isRepositoryLevel: false
    },
    emits: [
        'update-is-loading',
        'openProcessesPopup'
    ],
    data(){
        return {
            isAllItemsSelected: false,
            queryAllItemsSelected: {},
            cursorPosX: -1,
            cursorPosY: -1,
            contextMenuItem: null,
            singleItemSelection: false,
            masonry: false,
            shouldUseLegacyMasonyCols: false,
            latitude: -14.4086569,
            longitude: -51.31668,
            mapTileUrl: 'https://tile.openstreetmap.org/{z}/{x}/{y}.png',
            mapTileAttribution: '&copy; <a target="_blank" href="http://osm.org/copyright">OpenStreetMap</a> contributors',
            selectedMarkerIndexes: [],
            hoveredMapCardItemId: false,
            mapSelectedItemId: false,
            mapIconRetinaUrl: iconRetinaUrl,
            mapIconUrl: iconUrl,
            mapIconShadowUrl: shadowUrl,
            selectedGeocoordinateMetadatumId: false
        }
    },
    computed: {
        ...mapGetters('collection', {
            'collection': 'getCollection',
        }),
        ...mapGetters('bulkedition', {
            'groupId': 'getGroupId'
        }),
        ...mapGetters('search', {
            'highlightedItem': 'getHighlightedItem',
            'itemsPerPage': 'getItemsPerPage'
        }),
        selectedItems () {
            if (this.$adminOptions.itemsSingleSelectionMode || this.$adminOptions.itemsMultipleSelectionMode)
                this.$eventBusSearch.setSelectedItemsForIframe(this.getSelectedItems());

            return this.getSelectedItems();
        },
        amountOfSelectedItemsOnThisPage() {
            if (this.selectedItems.length) 
                return this.items.filter( anItem => this.selectedItems.includes(anItem.id) ).length;

            return 0;
        },
        firstSelectedIndex() {
            return (this.selectedItems && this.selectedItems.length) ? this.items.findIndex((anItem) => this.selectedItems[0] == anItem.id) : null;
        },
        isSelectingItems () {
            return this.selectedItems.length > 0;
        },
        allItemsOnPageSelected() {
            if ( this.items && Array.isArray(this.items) ) {
                for (let i = 0; i < this.items.length; i++){
                    if (this.selectedItems.indexOf(this.items[i].id) === -1)
                        return false;
                }
            }
            return true;
        },
        totalPages(){
            return Math.ceil(Number(this.totalItems)/Number(this.itemsPerPage));
        },
        isOnAllItemsTabs() {
            const currentStatus = this.getStatus();
            return !currentStatus || (currentStatus.indexOf(',') > 0);
        },
        /* 
         * This computed property only returns the metadatum object where the title is. 
         * In repository level, there is not "title metadatum", this information comes directly from the item.title.
         */
        titleItemMetadatum() {
            const possibleTitleItemMetadatum = this.displayedMetadata.find(aMetadatum => aMetadatum.display && aMetadatum.metadata_type_object && aMetadatum.metadata_type_object.related_mapped_prop == 'title');
            return possibleTitleItemMetadatum ? possibleTitleItemMetadatum : false;
        },
        /* 
         * This computed property only returns the metadatum object where the description is. 
         * In repository level, there is not "description metadatum", this information comes directly from the item.description.
         */
        descriptionItemMetadatum() {
            const possibleDescriptionItemMetadatum = this.displayedMetadata.find(aMetadatum => aMetadatum.display && aMetadatum.metadata_type_object && aMetadatum.metadata_type_object.related_mapped_prop == 'description');
            return possibleDescriptionItemMetadatum ? possibleDescriptionItemMetadatum : false;
        },
        itemsLocations() {
            let locations = [];
            
            if ( this.viewMode == 'map' && this.selectedGeocoordinateMetadatum.slug && this.items ) {
                for (let item of this.items) {
                    
                    if ( !item.metadata )
                        continue;
                    
                    let selectedItemMetadatum = item.metadata[this.selectedGeocoordinateMetadatum.slug];

                    // Handle compound metadata child first, as they will not appear in this list by default (they are inside their parents value)
                    if (!selectedItemMetadatum && this.selectedGeocoordinateMetadatum['parent']) {

                        const parentSlug = Object.keys(item.metadata).find(aMetadatumSlug => item.metadata[aMetadatumSlug].id == this.selectedGeocoordinateMetadatum['parent']);
                        if (parentSlug) {
                            item.metadata[parentSlug].value.forEach(aCompoundValue => {

                                const compoundValues = Array.isArray(aCompoundValue) ? aCompoundValue : [aCompoundValue];
                                compoundValues.forEach(aValue => {
                                    if ( aValue['metadatum_id'] == this.selectedGeocoordinateMetadatum['id'] ) {
                                        selectedItemMetadatum = {
                                            'metadatum_id': aValue['metadatum_id'],
                                            'parent_meta_id': aValue['parent_meta_id'],
                                            'value': selectedItemMetadatum && selectedItemMetadatum['value'] ? selectedItemMetadatum['value'] : [],
                                            'value_as_string': selectedItemMetadatum && selectedItemMetadatum['value_as_string'] ? selectedItemMetadatum['value_as_string'] : [],
                                            'value_as_html': selectedItemMetadatum && selectedItemMetadatum['value_as_html'] ? selectedItemMetadatum['value_as_html'] : []
                                        }
                                        selectedItemMetadatum['value'].push(aValue['value']);
                                        selectedItemMetadatum['value_as_string'].push(aValue['value_as_string']);
                                        selectedItemMetadatum['value_as_html'].push(aValue['value_as_html']);
                                    }
                                });
                            });
                        }
                    }

                    // Then check if has a single or multi value
                    if (
                        selectedItemMetadatum &&
                        Array.isArray(selectedItemMetadatum.value) 
                    ) {
                        for (let i = 0; i < selectedItemMetadatum.value.length; i++) {
                            if (selectedItemMetadatum.value[i].split(',').length == 2) {
                                locations.push({
                                    item: item,
                                    multivalueIndex: i,
                                    multivalueTotal: selectedItemMetadatum.value.length,
                                    location: latLng(selectedItemMetadatum.value[i].split(','))
                                });
                            }
                        }
                    } else if (
                        selectedItemMetadatum &&
                        typeof selectedItemMetadatum.value.split == 'function' &&
                        selectedItemMetadatum.value.split(',').length == 2
                    ) {
                        locations.push({
                            item: item,
                            location: latLng(selectedItemMetadatum.value.split(','))
                        });
                    }
                    
                }   
            }
            return locations;
        },
        geocoordinateMetadata() {
            let geoMetadata = {};

            this.displayedMetadata.forEach((aMetadatum) => {

                if ( aMetadatum['display'] && aMetadatum['metadata_type'] == 'Tainacan\\Metadata_Types\\GeoCoordinate' )
                    geoMetadata[aMetadatum.id] = aMetadatum;
                
                if ( aMetadatum['display'] && aMetadatum['metadata_type'] == 'Tainacan\\Metadata_Types\\Compound' &&
                    aMetadatum['metadata_type_options']['children_objects'] && aMetadatum['metadata_type_options']['children_objects'].length
                ) {
                    for ( let i = 0; i < aMetadatum['metadata_type_options']['children_objects'].length; i++ )
                        if ( aMetadatum['metadata_type_options']['children_objects'][i]['metadata_type'] == 'Tainacan\\Metadata_Types\\GeoCoordinate' ) {
                            let childMetadatum = JSON.parse(JSON.stringify(aMetadatum['metadata_type_options']['children_objects'][i]));
                            childMetadatum.name = childMetadatum.name + ' (' + aMetadatum.name + ')';
                            geoMetadata[aMetadatum.id] = childMetadatum;
                        }
                }
            });
            return geoMetadata;
        },
        selectedGeocoordinateMetadatum() {
            if (
                !Object.keys(this.geocoordinateMetadata).length ||
                !this.geocoordinateMetadata[this.selectedGeocoordinateMetadatumId]
            )
                return false;
            else 
                return this.geocoordinateMetadata[this.selectedGeocoordinateMetadatumId];
        }
    },
    watch: {
        isAllItemsSelected(value) {

            if (!value) {
                this.cleanSelectedItems();
                this.queryAllItemsSelected = {};
            } else {
                this.queryAllItemsSelected = this.$route.query;
                for (let item of this.items)
                    this.addSelectedItem(item.id);
            }
        },
        allItemsOnPageSelected(value) {
            if (!value)
                this.queryAllItemsSelected = {};
        },
        singleItemSelection() {
            if (this.$adminOptions.itemsSingleSelectionMode)
                this.$eventBusSearch.setSelectedItemsForIframe([this.singleItemSelection], true);
        },
        isLoading: {
             handler() {
                if (this.items && this.items.length > 0) {
                    nextTick(() => {
                        if (this.masonry !== false)
                            this.masonry.destroy();
                        
                        if (this.viewMode == 'masonry' || this.viewMode == 'records') {
                            this.masonry = new Masonry( '.tainacan-' + this.viewMode + '-container', {
                                itemSelector: 'li',
                                columnWidth: '.tainacan-' + this.viewMode + '-grid-sizer',
                                gutter: this.viewMode == 'masonry' ? 25 : 30,
                                percentPosition: true
                            });
                        }
                    });
                }
            },
            immediate: true
        },
        itemsLocations: {
            handler() {
                setTimeout(() => {
                    if ( this.itemsLocations.length && this.$refs['tainacan-admin-view-mode-map'] && this.$refs['tainacan-admin-view-mode-map'].leafletObject ) {
                        if (this.itemsLocations.length == 1)
                            this.$refs['tainacan-admin-view-mode-map'].leafletObject.panInsideBounds(this.itemsLocations.map((anItemLocation) => anItemLocation.location),  { animate: true, maxZoom: 16, paddingTopLeft: [48, 48], paddingTopRight: [48, 48] });
                        else
                            this.$refs['tainacan-admin-view-mode-map'].leafletObject.flyToBounds(this.itemsLocations.map((anItemLocation) => anItemLocation.location),  { animate: true, maxZoom: 16, paddingTopLeft: [48, 48], paddingTopRight: [48, 48] });
                    }
                }, 500);
            },
            deep: true
        },
        selectedGeocoordinateMetadatum() {
            this.clearSelectedMarkers();
        },
        geocoordinateMetadata: {
            handler() {
                // Setting default geocoordinate metadatum for map view mode
                const prefsGeocoordinateMetadatum = !this.isRepositoryLevel ? 'map_view_mode_selected_geocoordinate_metadatum_' + this.collectionId : 'map_view_mode_selected_geocoordinate_metadatum';
                const geocoordinateMetadataIds = Object.keys(this.geocoordinateMetadata);
                if (
                    !geocoordinateMetadataIds.length ||
                    !this.$userPrefs.get(prefsGeocoordinateMetadatum) ||
                    !this.geocoordinateMetadata[this.$userPrefs.get(prefsGeocoordinateMetadatum)]
                )
                    this.selectedGeocoordinateMetadatumId = geocoordinateMetadataIds.length ? geocoordinateMetadataIds[0] : false;
                else 
                    this.selectedGeocoordinateMetadatumId = this.$userPrefs.get(prefsGeocoordinateMetadatum);
            },
            immediate: true,
            deep: true
        }
    },
    created() {
        this.shouldUseLegacyMasonyCols = wp !== undefined && wp.hooks !== undefined && wp.hooks.hasFilter('tainacan_use_legacy_masonry_view_mode_cols') && wp.hooks.applyFilters('tainacan_use_legacy_masonry_view_mode_cols', false);
    },
    methods: {
        ...mapActions('collection', [
            'deleteItem',
        ]),
        ...mapActions('bulkedition', [
            'createEditGroup',
            'createSequenceEditGroup',
            'trashItemsInBulk',
            'deleteItemsInBulk',
            'untrashItemsInBulk'
        ]),
        ...mapActions('search', [
            'setSeletecItems',
            'cleanSelectedItems',
            'addSelectedItem',
            'removeSelectedItem'
        ]),
        ...mapGetters('search', [
            'getStatus',
            'getSelectedItems',
        ]),
        setSelectedItemChecked(itemId) {
            if (this.$adminOptions.itemsSingleSelectionMode) {
                this.singleItemSelection = itemId;
            } else {
                if (this.selectedItems.find((item) => item == itemId) != undefined)
                    this.removeSelectedItem(itemId);
                else {
                    this.addSelectedItem(itemId);
                }
            }
        },
        getSelectedItemChecked(itemId) {
            return this.$adminOptions.itemsSingleSelectionMode ? this.singleItemSelection == itemId : this.selectedItems.find(item => item == itemId) != undefined;
        },
        openBulkEditionModal(){
            this.$buefy.modal.open({
                parent: this,
                component: defineAsyncComponent(() => import('../modals/bulk-edition-modal.vue')),
                props: {
                    modalTitle: this.$i18n.get('info_editing_items_in_bulk'),
                    totalItems: Object.keys(this.queryAllItemsSelected).length ? this.totalItems : this.selectedItems.length,
                    selectedForBulk: Object.keys(this.queryAllItemsSelected).length ? this.queryAllItemsSelected : this.selectedItems,
                    objectType: this.$i18n.get('items'),
                    collectionId: this.$route.params.collectionId,
                },
                width: 'calc(100% - (2 * var(--tainacan-one-column)))',
                trapFocus: true,
                customClass: 'tainacan-modal',
                closeButtonAriaLabel: this.$i18n.get('close')
            });
        },
        sequenceEditSelectedItems() {
            this.createSequenceEditGroup({
                object: Object.keys(this.queryAllItemsSelected).length ? this.queryAllItemsSelected : this.selectedItems,
                collectionId: this.collectionId
            }).then(() => {
                this.$router.push(this.$routerHelper.getCollectionSequenceEditPath(this.collectionId, this.groupId, 1));
            });
        },
        selectAllItemsOnPage() {
            this.isAllItemsSelected = false;

            if (this.allItemsOnPageSelected)
                this.cleanSelectedItems();
            else {
                for (let item of this.items)
                    this.addSelectedItem(item.id);
            }
        },
        makeCopiesOfOneItem(itemId) {

            this.$buefy.modal.open({
                parent: this,
                component: defineAsyncComponent(() => import('../other/item-copy-dialog.vue')),
                canCancel: false,
                props: {
                    icon: 'items',
                    collectionId: this.collectionId,
                    itemId: itemId,
                    onConfirm: (newItems) => {
                        if (newItems != null && newItems != undefined && newItems.length > 0)
                            this.$eventBusSearch.loadItems();
                    }
                },
                trapFocus: true,
                customClass: 'tainacan-modal',
                closeButtonAriaLabel: this.$i18n.get('close')
            });

            this.clearContextMenu();
        },
        untrashOneItem(itemId) {
            this.$buefy.modal.open({
                parent: this,
                component: CustomDialog,
                props: {
                    icon: 'alert',
                    title: this.$i18n.get('label_warning'),
                    message: this.$i18n.get('info_warning_remove_item_from_trash'),
                    onConfirm: () => {
                        this.$emit('update-is-loading', true);

                        this.createEditGroup({
                            collectionId: this.collectionId,
                            object: [itemId]
                        }).then(() => {
                            this.untrashItemsInBulk({
                                collectionId: this.collectionId,
                                groupId: this.groupId
                            }).then(() => this.$eventBusSearch.loadItems() );
                        });
                    }
                },
                trapFocus: true,
                customClass: 'tainacan-modal',
                closeButtonAriaLabel: this.$i18n.get('close')
            });
        },
        deleteOneItem(itemId) {
            this.$buefy.modal.open({
                parent: this,
                component: CustomDialog,
                props: {
                    icon: 'alert',
                    title: this.$i18n.get('label_warning'),
                    message: this.isOnTrash ? this.$i18n.get('info_warning_item_delete') : this.$i18n.get('info_warning_item_trash'),
                    onConfirm: () => {
                        this.$emit('update-is-loading', true);

                        this.deleteItem({
                            itemId: itemId,
                            isPermanently: this.isOnTrash
                        }).then(() => this.$eventBusSearch.loadItems() );
                    }
                },
                trapFocus: true,
                customClass: 'tainacan-modal',
                closeButtonAriaLabel: this.$i18n.get('close')
            });
            this.clearContextMenu();
        },
        untrashSelectedItems(){
            this.$buefy.modal.open({
                parent: this,
                component: CustomDialog,
                props: {
                    icon: 'alert',
                    title: this.$i18n.get('label_warning'),
                    message: this.$i18n.get('info_warning_selected_items_remove_from_trash'),
                    onConfirm: () => {
                        this.$emit('update-is-loading', true);

                        this.createEditGroup({
                            collectionId: this.collectionId,
                            object: Object.keys(this.queryAllItemsSelected).length ? this.queryAllItemsSelected : this.selectedItems
                        }).then(() => {
                            this.untrashItemsInBulk({
                                collectionId: this.collectionId,
                                groupId: this.groupId
                            }).then(() => {
                                this.$eventBusSearch.loadItems();
                                this.$emitter.emit('openProcessesPopup');
                            });
                        });
                    }
                },
                trapFocus: true,
                customClass: 'tainacan-modal',
                closeButtonAriaLabel: this.$i18n.get('close')
            });
        },
        deleteSelectedItems() {
            this.$buefy.modal.open({
                parent: this,
                component: CustomDialog,
                props: {
                    icon: 'alert',
                    title: this.$i18n.get('label_warning'),
                    message: this.isOnTrash ? this.$i18n.get('info_warning_selected_items_delete') : this.$i18n.get('info_warning_selected_items_trash'),
                    onConfirm: () => {
                        this.$emit('update-is-loading', true);

                        this.createEditGroup({
                            collectionId: this.collectionId,
                            object: Object.keys(this.queryAllItemsSelected).length ? this.queryAllItemsSelected : this.selectedItems
                        }).then(() => {
                            if (this.isOnTrash) {
                                this.deleteItemsInBulk({
                                    collectionId: this.collectionId,
                                    groupId: this.groupId
                                }).then(() => {
                                    this.$eventBusSearch.loadItems();
                                    this.$emitter.emit('openProcessesPopup');
                                });
                            } else {
                                this.trashItemsInBulk({
                                    collectionId: this.collectionId,
                                    groupId: this.groupId
                                }).then(() => {
                                    this.$eventBusSearch.loadItems();
                                    this.$emitter.emit('openProcessesPopup');
                                });
                            }
                        });
                    }
                },
                trapFocus: true,
                customClass: 'tainacan-modal',
                closeButtonAriaLabel: this.$i18n.get('close')
            });
        },
        filterBySelectedItems() {
            let newQuery = {
                postin: JSON.parse(JSON.stringify(this.selectedItems)),
            }

            if ( this.$route.query['fetch_only'] )
                newQuery['fetch_only'] =  this.$route.query['fetch_only'];

            if ( this.$route.query['fetch_only_meta'] )
                newQuery['fetch_only_meta'] =  this.$route.query['fetch_only_meta'];

            this.$router.replace({ path: this.$route.path, query: newQuery });
        },
        openItem() {
            if (this.contextMenuItem != null) {
                this.$router.push(this.$routerHelper.getItemPath(this.contextMenuItem.collection_id, this.contextMenuItem.id));
            }
            this.clearContextMenu();
        },
        openItemOnNewTab() {
            if (this.contextMenuItem != null) {
                let routeData = this.$router.resolve(this.$routerHelper.getItemPath(this.contextMenuItem.collection_id, this.contextMenuItem.id));
                window.open(routeData.href, '_blank');
            }
            this.clearContextMenu();
        },
        selectItem() {
            if (this.contextMenuItem != null)
                this.setSelectedItemChecked(this.contextMenuItem.id);
    
            this.clearContextMenu();
        },
        onClickItem($event, item) {

            if ($event && $event.target && ($event.target.className == 'check' || $event.target.tagName == 'INPUT') )
                return;
            
            if ($event.ctrlKey) {
                this.setSelectedItemChecked(item.id);
            } else if ($event.shiftKey) {

                if (this.firstSelectedIndex != null) {
                    const lastFirstSelectedIndex = this.firstSelectedIndex;
                    const lastSelectedIndex = this.items.findIndex((anItem) => anItem.id == item.id);

                    this.cleanSelectedItems();
                    if (lastFirstSelectedIndex > lastSelectedIndex) {
                        for (let i = lastFirstSelectedIndex; i >= lastSelectedIndex; i--)
                            this.setSelectedItemChecked(this.items[i].id);
                    } else {
                        for (let i = lastFirstSelectedIndex; i <= lastSelectedIndex; i++)
                            this.setSelectedItemChecked(this.items[i].id);
                    }
                } else {
                    this.setSelectedItemChecked(item.id);
                }

            } else {
                if ((this.$adminOptions.itemsSingleSelectionMode || this.$adminOptions.itemsMultipleSelectionMode) && !this.$adminOptions.itemsSearchSelectionMode) {
                    this.setSelectedItemChecked(item.id)
                } else if (!this.$adminOptions.itemsSingleSelectionMode && !this.$adminOptions.itemsMultipleSelectionMode && !this.$adminOptions.itemsSearchSelectionMode) {
                    if (this.isOnTrash) {
                        this.$buefy.toast.open({
                            duration: 3000,
                            message: this.$i18n.get('info_warning_remove_from_trash_first'),
                            position: 'is-bottom',
                            type: 'is-warning'
                        });
                    } else {
                        this.$router.push(this.$routerHelper.getItemPath(item.collection_id, item.id));
                    }
                }
            }
        },
        onRightClickItem($event, item) {
            if (!this.$adminOptions.itemsSearchSelectionMode) {
                $event.preventDefault();

                this.cursorPosX = $event.clientX;
                this.cursorPosY = $event.clientY;
                this.contextMenuItem = item;
            }
        },
        clearContextMenu() {
            this.cursorPosX = -1;
            this.cursorPosY = -1;
            this.contextMenuItem = null;
        },
        goToItemEditPage(item) {
            this.$router.push(this.$routerHelper.getItemEditPath(item.collection_id, item.id));
        },
        renderMetadata(itemMetadata, column, multivalueIndex) {

            let metadata = (itemMetadata != undefined && itemMetadata[column.slug] != undefined) ? itemMetadata[column.slug] : false;

            if (!metadata || itemMetadata == undefined)
                return '';

            if ( multivalueIndex != undefined && metadata.value[multivalueIndex]) {
                
                if ( !Array.isArray(metadata.value[multivalueIndex]) && metadata.value[multivalueIndex].value_as_html)
                    return metadata.value[multivalueIndex].value_as_html;

                if ( Array.isArray(metadata.value[multivalueIndex]) ) {
                    let sumOfValuesAsHtml = '';

                    metadata.value[multivalueIndex].forEach(aValue => {
                        if (aValue.value_as_html)
                            sumOfValuesAsHtml += aValue.value_as_html + '<br>';
                    })

                    return sumOfValuesAsHtml;
                }
            }

            return this.viewMode == 'table' ? ('<span class="sr-only">' + column.name + ': </span>' + metadata.value_as_html) : metadata.value_as_html;
        },
        getMultivalueIndicator(itemLocation) {
            if ( itemLocation.multivalueTotal > 1 )
                return ' <em>(' + (itemLocation.multivalueIndex + 1) + ' of ' + itemLocation.multivalueTotal + ')</em>';
            else 
                return '';
        },
        getLimitedDescription(description) {
            let maxCharacter = (window.innerWidth || document.documentElement.clientWidth || document.body.clientWidth) <= 480 ? 100 : 210;
            return description.length > maxCharacter ? description.substring(0, maxCharacter - 3) + '...' : description;
        },
        onChangeSelectedGeocoordinateMetadatum(id) {
            // Setting default geocoordinate metadatum for map view mode
            const prefsGeocoordinateMetadatum = !this.isRepositoryLevel ? 'map_view_mode_selected_geocoordinate_metadatum_' + this.collectionId : 'map_view_mode_selected_geocoordinate_metadatum';
            this.$userPrefs.set(prefsGeocoordinateMetadatum, id);
        },
        onMapReady() {
            if ( LeafletActiveArea && this.$refs['tainacan-admin-view-mode-map'] && this.$refs['tainacan-admin-view-mode-map'].leafletObject )
                this.$refs['tainacan-admin-view-mode-map'].leafletObject.setActiveArea('leaflet-active-area');
        },
        clearSelectedMarkers() {
            this.mapSelectedItemId = false;
            this.selectedMarkerIndexes = [];
            if ( this.itemsLocations.length && this.$refs['tainacan-admin-view-mode-map'] && this.$refs['tainacan-admin-view-mode-map'].leafletObject ) {
                if (this.itemsLocations.length == 1)
                    this.$refs['tainacan-admin-view-mode-map'].leafletObject.panInsideBounds(this.itemsLocations.map((anItemLocation) => anItemLocation.location),  { animate: true, maxZoom: 16, paddingTopLeft: [48, 48], paddingTopRight: [48, 48] });
                else
                    this.$refs['tainacan-admin-view-mode-map'].leafletObject.flyToBounds(this.itemsLocations.map((anItemLocation) => anItemLocation.location),  { animate: true, maxZoom: 16, paddingTopLeft: [48, 48], paddingTopRight: [48, 48] });
            }
        },
        showItemByLocation(index) {
            this.mapSelectedItemId = this.itemsLocations[index].item.id;
            this.selectedMarkerIndexes = [];
            this.selectedMarkerIndexes.push(index);
            if ( this.itemsLocations.length && this.$refs['tainacan-admin-view-mode-map'] && this.$refs['tainacan-admin-view-mode-map'].leafletObject )
                this.$refs['tainacan-admin-view-mode-map'].leafletObject.panInsideBounds( [ this.itemsLocations[index].location ],  { animate: true, maxZoom: 16, paddingTopLeft: [48, 286], paddingTopRight: [48, 48] });
        },
        showLocationsByItem(item) {
            this.mapSelectedItemId = item.id;
            this.selectedMarkerIndexes = [];

            const selectedLocationsByItem = this.itemsLocations.filter((anItemLocation, index) => {
                if (anItemLocation.item.id == item.id)
                    this.selectedMarkerIndexes.push(index);
                return anItemLocation.item.id == item.id;
            })

            if ( selectedLocationsByItem.length) {
                if ( this.itemsLocations.length && this.$refs['tainacan-admin-view-mode-map'] && this.$refs['tainacan-admin-view-mode-map'].leafletObject ) {
                    if (selectedLocationsByItem.length > 1)
                        this.$refs['tainacan-admin-view-mode-map'].leafletObject.flyToBounds( selectedLocationsByItem.map((anItemLocation) => anItemLocation.location),  { animate: true, maxZoom: 16, paddingTopLeft: [48, 286], paddingTopRight: [48, 48] });
                    else
                        this.$refs['tainacan-admin-view-mode-map'].leafletObject.panInsideBounds( selectedLocationsByItem.map((anItemLocation) => anItemLocation.location),  { animate: true, maxZoom: 16, paddingTopLeft: [48, 286], paddingTopRight: [48, 48] });
                }
            } else {
                this.$buefy.snackbar.open({
                    message: this.$i18n.get('info_non_located_item'),
                    type: 'is-warning',
                    duration: 3000
                });
            }
        },
        getAcceptableWidthBasedOnRatio(thumbnail, size, defaultSize) {
            const width = this.$thumbHelper.getWidth(thumbnail, size, defaultSize);
            const height = this.$thumbHelper.getHeight(thumbnail, size, defaultSize);

            return (width / height) > 0.7 ? width : ( height * 0.7 );
        }
    }
}
</script>

<style lang="scss" scoped>

    @import "../../scss/_variables.scss";
    @import "../../scss/_tables.scss";
    @import "../../scss/_view-mode-cards.scss";
    @import "../../scss/_view-mode-masonry.scss";
    @import "../../scss/_view-mode-mosaic.scss";
    @import "../../scss/_view-mode-grid.scss";
    @import "../../scss/_view-mode-records.scss";
    @import "../../scss/_view-mode-list.scss";
    @import "../../scss/_view-mode-map.scss";
    
    // Vue Blurhash transtition effect
    @import '../../../../../node_modules/another-vue3-blurhash/dist/style.css';
    :deep(canvas.child) {
        max-width: 100%;
    }

    .selection-control {
        margin-bottom: 6px;
        padding: 6px 0px 0px 12px;
        background: var(--tainacan-background-color);
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: space-between;

        .select-all {
            color: var(--tainacan-info-color);
            font-size: 0.875em;
            margin-right: auto;
            margin-bottom: 0;

            &:hover {
                color: var(--tainacan-info-color);
            }
        }
    }

    .selected-items-info {
        margin: 0 auto 0 auto;
        font-size: 00.875em;
        color: var(--tainacan-info-color);

        .link-style {
            border-radius: 36px;
            &:hover {
                background-color: var(--tainacan-gray1) !important;
            }
        }
    }

    .context-menu {
        .dropdown {
            position: fixed;
            z-index: 99999999999;
        }
        .context-menu-backdrop {
            position: fixed;
            top: 0;
            right: 0;
            left: 0;
            border: 0;
            width: 100%;
            height: 100vh;
            z-index: 9999999;
        }
    }

</style>


