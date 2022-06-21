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
                            @click.native.prevent="selectAllItemsOnPage()"
                            :value="allItemsOnPageSelected">
                        {{ $i18n.get('label_select_all_items_page') }}
                    </b-checkbox>
                </span>
                
                <span
                        style="margin-left: 10px"
                        v-if="totalPages > 1 && allItemsOnPageSelected && items.length > 1">
                    <b-checkbox
                            v-model="isAllItemsSelected">
                        {{ $i18n.getWithVariables('label_select_all_%s_items', [totalItems]) }}
                    </b-checkbox>
                </span>
            </div>
            <span
                    class="selected-items-info"
                    v-if="selectedItems.length && items.length > 1 && !isAllItemsSelected">
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
                    class="selected-items-info"
                    v-if="isAllItemsSelected">
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
                        :mobile-modal="true"
                        position="is-bottom-left"
                        v-if="items.length > 0"
                        :disabled="selectedItems.length <= 1"
                        id="bulk-actions-dropdown"
                        aria-role="list"
                        trap-focus>
                    <button
                            class="button is-white"
                            slot="trigger">
                        <span>{{ $i18n.get('label_actions_for_the_selection') }}</span>
                        <span class="icon">
                            <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-arrowdown"/>
                        </span>
                    </button>

                    <b-dropdown-item
                            v-if="!isAllItemsSelected && selectedItems.length"
                            @click="filterBySelectedItems()"
                            aria-role="listitem">
                        {{ $i18n.get('label_view_only_selected_items') }}
                    </b-dropdown-item>
                    <b-dropdown-item
                            v-if="$route.params.collectionId && !isOnTrash"
                            @click="openBulkEditionModal()"
                            aria-role="listitem">
                        {{ $i18n.get('label_bulk_edit_selected_items') }}
                    </b-dropdown-item>
                    <b-dropdown-item
                            v-if="$route.params.collectionId && !isOnTrash"
                            @click="sequenceEditSelectedItems()"
                            aria-role="listitem">
                        {{ $i18n.get('label_sequence_edit_selected_items') }}
                    </b-dropdown-item>
                    <b-dropdown-item
                            v-if="collectionId && collection && collection.current_user_can_delete_items"
                            @click="deleteSelectedItems()"
                            id="item-delete-selected-items"
                            aria-role="listitem">
                        {{ isOnTrash ? $i18n.get('label_delete_permanently') : $i18n.get('label_send_to_trash') }}
                    </b-dropdown-item>
                    <b-dropdown-item
                            v-if="collectionId && isOnTrash"
                            @click="untrashSelectedItems();"
                            aria-role="listitem">
                        {{ $i18n.get('label_untrash_selected_items') }}
                    </b-dropdown-item>
                    <b-dropdown-item
                            :disabled="isAllItemsSelected"
                            @click="$parent.openExposersModal(selectedItems)"
                            aria-role="listitem">
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
                    @click.left="clearContextMenu()"
                    @click.right="clearContextMenu()"
                    class="context-menu-backdrop" />

                <b-dropdown
                        inline
                        :style="{ top: cursorPosY + 'px', left: cursorPosX + 'px' }"
                        trap-focus>
                    <b-dropdown-item
                            @click="openItem()"
                            v-if="!isOnTrash && !$adminOptions.hideItemsListContextMenuOpenItemOption">
                        {{ $i18n.getFrom('items','view_item') }}
                    </b-dropdown-item>
                    <b-dropdown-item
                            @click="openItemOnNewTab()"
                            v-if="!isOnTrash && !$adminOptions.hideItemsListContextMenuOpenItemOnNewTabOption">
                        {{ $i18n.get('label_open_item_new_tab') }}
                    </b-dropdown-item>
                    <b-dropdown-item
                            @click="selectItem()"
                            v-if="contextMenuItem != null">
                        {{ getSelectedItemChecked(contextMenuItem.id) == true ? $i18n.get('label_unselect_item') : $i18n.get('label_select_item') }}
                    </b-dropdown-item>
                    <b-dropdown-item
                            @click="goToItemEditPage(contextMenuItem)"
                            v-if="contextMenuItem != null && contextMenuItem.current_user_can_edit && !$adminOptions.hideItemsListContextMenuEditItemOption">
                        {{ $i18n.getFrom('items','edit_item') }}
                    </b-dropdown-item>
                    <b-dropdown-item
                            @click="makeCopiesOfOneItem(contextMenuItem.id)"
                            v-if="contextMenuItem != null && contextMenuItem.current_user_can_edit && !$adminOptions.hideItemsListContextMenuCopyItemOption">
                        {{ $i18n.get('label_make_copies_of_item') }}
                    </b-dropdown-item>
                    <b-dropdown-item
                            @click="deleteOneItem(contextMenuItem.id)"
                            v-if="contextMenuItem != null && contextMenuItem.current_user_can_edit && !$adminOptions.hideItemsListContextMenuDeleteItemOption">
                        {{ $i18n.get('label_delete_item') }}
                    </b-dropdown-item>
                </b-dropdown>
            </div>
            
            <!-- GRID (THUMBNAILS) VIEW MODE -->
            <div
                    role="list"
                    class="tainacan-grid-container"
                    :class="{ 'hide-items-selection': $adminOptions.hideItemsListSelection }"
                    v-if="viewMode == 'grid'">
                <div
                        role="listitem"
                        :key="index"
                        :data-tainacan-item-id="item.id"
                        v-for="(item, index) of items"
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
                                :value="getSelectedItemChecked(item.id)"
                                @input="setSelectedItemChecked(item.id)">
                            <span class="sr-only">{{ $i18n.get('label_select_item') }}</span>
                        </b-checkbox>
                        <b-radio
                                v-else
                                name="item-single-selection"
                                :native-value="item.id"
                                v-model="singleItemSelection"
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
                                        shown: 500,
                                        hide: 300,
                                    },
                                    content: item.title != undefined ? item.title : '',
                                    html: true,
                                    autoHide: false,
                                    placement: 'auto-start',
                                    popperClass: ['tainacan-tooltip', 'tooltip', isRepositoryLevel ? 'tainacan-repository-tooltip' : '']
                                }"
                                @click.left="onClickItem($event, item)"
                                @click.right="onRightClickItem($event, item)">
                            <span 
                                    v-if="isOnAllItemsTabs && $statusHelper.hasIcon(item.status)"
                                    class="icon has-text-gray"
                                    v-tooltip="{
                                        content: $i18n.get('status_' + item.status),
                                        autoHide: true,
                                        popperClass: ['tainacan-tooltip', 'tooltip', isRepositoryLevel ? 'tainacan-repository-tooltip' : ''],
                                        placement: 'auto-start'
                                    }">
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
                            @click.left="onClickItem($event, item)"
                            @click.right="onRightClickItem($event, item)"
                            class="grid-item-thumbnail">
                        <blur-hash-image
                                :width="$thumbHelper.getWidth(item['thumbnail'], 'tainacan-medium', 120)"
                                :height="$thumbHelper.getHeight(item['thumbnail'], 'tainacan-medium', 120)"
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
                                <i class="has-text-secondary tainacan-icon tainacan-icon-1-25em tainacan-icon-edit"/>
                            </span>
                        </a>
                        <a
                                :aria-lavel="$i18n.get('label_button_untrash')"
                                @click.prevent.stop="untrashOneItem(item.id)"
                                v-if="isOnTrash">
                            <span
                                    v-tooltip="{
                                        content: $i18n.get('label_recover_from_trash'),
                                        autoHide: true,
                                        placement: 'auto',
                                        popperClass: ['tainacan-tooltip', 'tooltip', isRepositoryLevel ? 'tainacan-repository-tooltip' : '']
                                    }"
                                    class="icon">
                                <i class="has-text-secondary tainacan-icon tainacan-icon-1-25em tainacan-icon-undo"/>
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
                                        class="has-text-secondary tainacan-icon tainacan-icon-1-25em"/>
                            </span>
                        </a>
                    </div>

                </div>
            </div>

            <!-- MASONRY VIEW MODE -->
            <ul
                    v-if="viewMode == 'masonry'"
                    :class="{ 'hide-items-selection': $adminOptions.hideItemsListSelection }"
                    class="tainacan-masonry-container">
                <li
                        :key="index"
                        :data-tainacan-item-id="item.id"
                        v-for="(item, index) of items"
                        :class="{
                            'tainacan-masonry-grid-sizer': index == 0 
                        }">
                    <div
                        :class="{
                            'selected-masonry-item': getSelectedItemChecked(item.id) == true
                        }"
                        class="tainacan-masonry-item">
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
                                        type="radio"
                                        name="item-single-selection"
                                        :value="item.id"
                                        v-model="singleItemSelection">
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
                                @click.left="onClickItem($event, item)"
                                @click.right="onRightClickItem($event, item)"
                                class="metadata-title">
                            <p>
                                <span 
                                        v-if="isOnAllItemsTabs && $statusHelper.hasIcon(item.status)"
                                        class="icon has-text-gray"
                                        v-tooltip="{
                                            content: $i18n.get('status_' + item.status),
                                            autoHide: true,
                                            popperClass: ['tainacan-tooltip', 'tooltip', isRepositoryLevel ? 'tainacan-repository-tooltip' : ''],
                                            placement: 'auto-start'
                                        }">
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
                                @click.left="onClickItem($event, item)"
                                @click.right="onRightClickItem($event, item)"
                                v-if="item.thumbnail != undefined"
                                class="tainacan-masonry-item-thumbnail"
                                :width="$thumbHelper.getWidth(item['thumbnail'], 'tainacan-large-full', 280)"
                                :height="$thumbHelper.getHeight(item['thumbnail'], 'tainacan-large-full', 280)"
                                :hash="$thumbHelper.getBlurhashString(item['thumbnail'], 'tainacan-large-full')"
                                :src="$thumbHelper.getSrc(item['thumbnail'], 'tainacan-large-full', item.document_mimetype)"
                                :srcset="$thumbHelper.getSrcSet(item['thumbnail'], 'tainacan-large-full', item.document_mimetype)"
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
                                    <i class="has-text-secondary tainacan-icon tainacan-icon-1-25em tainacan-icon-edit"/>
                                </span>
                            </a>
                            <a
                                    :aria-lavel="$i18n.get('label_button_untrash')"
                                    @click.prevent.stop="untrashOneItem(item.id)"
                                    v-if="isOnTrash">
                                <span
                                        v-tooltip="{
                                            content: $i18n.get('label_recover_from_trash'),
                                            autoHide: true,
                                            placement: 'auto',
                                            popperClass: ['tainacan-tooltip', 'tooltip', isRepositoryLevel ? 'tainacan-repository-tooltip' : '']
                                        }"
                                        class="icon">
                                    <i class="has-text-secondary tainacan-icon tainacan-icon-1-25em tainacan-icon-undo"/>
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
                                            class="has-text-secondary tainacan-icon tainacan-icon-1-25em"/>
                                </span>
                            </a>
                        </div>
                    </div>
                </li>
            </ul>

            <!-- CARDS VIEW MODE -->
            <div
                    role="list"
                    :class="{ 'hide-items-selection': $adminOptions.hideItemsListSelection }"
                    class="tainacan-cards-container"
                    v-if="viewMode == 'cards'">
                <div
                        role="listitem"
                        :key="index"
                        :data-tainacan-item-id="item.id"
                        v-for="(item, index) of items"
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
                                :value="getSelectedItemChecked(item.id)"
                                @input="setSelectedItemChecked(item.id)">
                            <span class="sr-only">{{ $i18n.get('label_select_item') }}</span>
                        </b-checkbox>
                        <b-radio
                                v-else
                                name="item-single-selection"
                                :native-value="item.id"
                                v-model="singleItemSelection">
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
                                        shown: 500,
                                        hide: 300,
                                    },
                                    content: item.title != undefined ? item.title : '',
                                    html: true,
                                    autoHide: false,
                                    placement: 'auto-start',
                                    popperClass: ['tainacan-tooltip', 'tooltip', isRepositoryLevel ? 'tainacan-repository-tooltip' : '']
                                }"
                                @click.left="onClickItem($event, item)"
                                @click.right="onRightClickItem($event, item)">
                            <span 
                                    v-if="isOnAllItemsTabs && $statusHelper.hasIcon(item.status)"
                                    class="icon has-text-gray"
                                    v-tooltip="{
                                        content: $i18n.get('status_' + item.status),
                                        autoHide: true,
                                        popperClass: ['tainacan-tooltip', 'tooltip', isRepositoryLevel ? 'tainacan-repository-tooltip' : ''],
                                        placement: 'auto-start'
                                    }">
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
                                <i class="has-text-secondary tainacan-icon tainacan-icon-1-25em tainacan-icon-edit"/>
                            </span>
                        </a>
                        <a
                                :aria-lavel="$i18n.get('label_button_untrash')"
                                @click.prevent.stop="untrashOneItem(item.id)"
                                v-if="isOnTrash">
                            <span
                                    v-tooltip="{
                                        content: $i18n.get('label_recover_from_trash'),
                                        autoHide: true,
                                        placement: 'auto',
                                        popperClass: ['tainacan-tooltip', 'tooltip', isRepositoryLevel ? 'tainacan-repository-tooltip' : '']
                                    }"
                                    class="icon">
                                <i class="has-text-secondary tainacan-icon tainacan-icon-1-25em tainacan-icon-undo"/>
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
                                        class="has-text-secondary tainacan-icon tainacan-icon-1-25em"/>
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
                                            shown: 500,
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
                                            shown: 500,
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
                                            shown: 500,
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
                    :class="{ 'hide-items-selection': $adminOptions.hideItemsListSelection }"
                    class="tainacan-records-container"
                    v-if="viewMode == 'records'">
                <li
                        :key="index"
                        :data-tainacan-item-id="item.id"
                        v-for="(item, index) of items"
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
                                        type="radio"
                                        name="item-single-selection"
                                        :value="item.id"
                                        v-model="singleItemSelection">
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
                                    class="icon has-text-gray"
                                    v-tooltip="{
                                        content: $i18n.get('status_' + item.status),
                                        autoHide: true,
                                        popperClass: ['tainacan-tooltip', 'tooltip', isRepositoryLevel ? 'tainacan-repository-tooltip' : ''],
                                        placement: 'auto-start'
                                    }">
                                <i 
                                        class="tainacan-icon tainacan-icon-1em"
                                        :class="$statusHelper.getIcon(item.status)"
                                        />
                            </span>
                            <p 
                                    v-tooltip="{
                                        delay: {
                                            shown: 500,
                                            hide: 300,
                                        },
                                        content: item.metadata != undefined ? renderMetadata(item.metadata, column) : '',
                                        html: true,
                                        autoHide: false,
                                        placement: 'auto-start',
                                        popperClass: ['tainacan-tooltip', 'tooltip', isRepositoryLevel ? 'tainacan-repository-tooltip' : '']
                                    }"
                                    v-for="(column, columnIndex) in displayedMetadata"
                                    :key="columnIndex"
                                    v-if="collectionId != undefined && column.display && column.metadata_type_object != undefined && (column.metadata_type_object.related_mapped_prop == 'title')"
                                    @click.left="onClickItem($event, item)"
                                    @click.right="onRightClickItem($event, item)"
                                    v-html="item.metadata != undefined ? renderMetadata(item.metadata, column) : ''" />
                            <p
                                    v-tooltip="{
                                        delay: {
                                            shown: 500,
                                            hide: 300,
                                        },
                                        content: item.title != undefined ? item.title : '',
                                        html: true,
                                        autoHide: false,
                                        placement: 'auto-start',
                                        popperClass: ['tainacan-tooltip', 'tooltip', isRepositoryLevel ? 'tainacan-repository-tooltip' : '']
                                    }"
                                    v-for="(column, columnIndex) in displayedMetadata"
                                    :key="columnIndex"
                                    v-if="collectionId == undefined && column.display && column.metadata_type_object != undefined && (column.metadata_type_object.related_mapped_prop == 'title')"
                                    @click.left="onClickItem($event, item)"
                                    @click.right="onRightClickItem($event, item)"
                                    v-html="item.title != undefined ? item.title : ''" />
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
                                    <i class="has-text-secondary tainacan-icon tainacan-icon-1-25em tainacan-icon-edit"/>
                                </span>
                            </a>
                            <a
                                    :aria-lavel="$i18n.get('label_button_untrash')"
                                    @click.prevent.stop="untrashOneItem(item.id)"
                                    v-if="isOnTrash">
                                <span
                                        v-tooltip="{
                                            content: $i18n.get('label_recover_from_trash'),
                                            autoHide: true,
                                            placement: 'auto',
                                            popperClass: ['tainacan-tooltip', 'tooltip', isRepositoryLevel ? 'tainacan-repository-tooltip' : '']
                                        }"
                                        class="icon">
                                    <i class="has-text-secondary tainacan-icon tainacan-icon-1-25em tainacan-icon-undo"/>
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
                                            class="has-text-secondary tainacan-icon tainacan-icon-1-25em"/>
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
                                            @click.left="onClickItem($event, item)"
                                            @click.right="onRightClickItem($event, item)"
                                            v-if="item.thumbnail != undefined"
                                            class="tainacan-record-item-thumbnail"
                                            :width="$thumbHelper.getWidth(item['thumbnail'], 'tainacan-medium-full', 120)"
                                            :height="$thumbHelper.getHeight(item['thumbnail'], 'tainacan-medium-full', 120)"
                                            :hash="$thumbHelper.getBlurhashString(item['thumbnail'], 'tainacan-medium-full')"
                                            :src="$thumbHelper.getSrc(item['thumbnail'], 'tainacan-medium-full', item.document_mimetype)"
                                            :srcset="$thumbHelper.getSrcSet(item['thumbnail'], 'tainacan-medium-full', item.document_mimetype)"
                                            :alt="item.thumbnail_alt ? item.thumbnail_alt : $i18n.get('label_thumbnail')"
                                            :transition-duration="500"
                                        />
                                </div>
                                <span
                                        v-for="(column, metadatumIndex) in displayedMetadata"
                                        :key="metadatumIndex"
                                        :class="{ 'metadata-type-textarea': column.metadata_type_object != undefined && column.metadata_type_object.component == 'tainacan-textarea' }"
                                        v-if="collectionId == undefined && column.display && column.metadata_type_object != undefined && (column.metadata_type_object.related_mapped_prop == 'description')">
                                    <h3 class="metadata-label">{{ $i18n.get('label_description') }}</h3>
                                    <p
                                            v-html="item.description != undefined ? item.description : ''"
                                            class="metadata-value"/>
                                </span>
                                <span
                                        v-for="(column, metadatumIndex) in displayedMetadata"
                                        :key="metadatumIndex"
                                        :class="{ 'metadata-type-textarea': column.metadata_type_object != undefined && column.metadata_type_object.component == 'tainacan-textarea' }"
                                        v-if="renderMetadata(item.metadata, column) != '' && column.display && column.slug != 'thumbnail' && column.metadata_type_object != undefined && (column.metadata_type_object.related_mapped_prop != 'title')">
                                    <h3 class="metadata-label">{{ column.name }}</h3>
                                    <p
                                            v-html="renderMetadata(item.metadata, column)"
                                            class="metadata-value"/>
                                </span>
                                <span
                                        v-for="(column, metadatumIndex) in displayedMetadata"
                                        :key="metadatumIndex"
                                        v-if="(column.metadatum == 'row_modification' || column.metadatum == 'row_creation' || column.metadatum == 'row_author') && item[column.slug] != undefined">
                                    <h3 class="metadata-label">{{ column.name }}</h3>
                                    <p
                                            v-html="(column.metadatum == 'row_creation' || column.metadatum == 'row_modification') ? parseDateToNavigatorLanguage(item[column.slug]) : item[column.slug]"
                                            class="metadata-value"/>
                                </span>
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
                        <th
                                v-for="(column, index) in displayedMetadata"
                                :key="index"
                                v-if="column.display"
                                class="column-default-width"
                                :class="{
                                        'thumbnail-cell': column.metadatum == 'row_thumbnail',
                                        'column-small-width' : column.metadata_type_object != undefined ? (column.metadata_type_object.primitive_type == 'date' ||
                                                                                                           column.metadata_type_object.primitive_type == 'float' ||
                                                                                                           column.metadata_type_object.primitive_type == 'int') : false,
                                        'column-medium-width' : column.metadata_type_object != undefined ? (column.metadata_type_object.primitive_type == 'term' ||
                                                                                                            column.metadata_type_object.primitive_type == 'item') : false,
                                        'column-large-width' : column.metadata_type_object != undefined ? (column.metadata_type_object.primitive_type == 'long_string' ||
                                                                                                           column.metadata_type_object.primitive_type == 'compound' ||
                                                                                                           column.metadata_type_object.related_mapped_prop == 'description') : false,
                                }">
                            <div class="th-wrap">{{ column.name }}</div>
                        </th>
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
                            :class="{
                                'selected-row': getSelectedItemChecked(item.id) == true,
                                'highlighted-item': highlightedItem == item.id
                            }"
                            role="listitem"
                            :key="index"
                            :data-tainacan-item-id="item.id"
                            v-for="(item, index) of items">
                        <!-- Checking list -->
                        <!-- TODO: Remove v-if="collectionId" from this element when the bulk edit in repository is done -->
                        <td
                                v-if="collectionId && !$adminOptions.hideItemsListSelection && ($adminOptions.itemsSingleSelectionMode || $adminOptions.itemsMultipleSelectionMode || (collection && collection.current_user_can_bulk_edit))"
                                :class="{ 'is-selecting': isSelectingItems }"
                                class="checkbox-cell">
                            <b-checkbox
                                    v-if="!$adminOptions.itemsSingleSelectionMode"
                                    :value="getSelectedItemChecked(item.id)"
                                    @input="setSelectedItemChecked(item.id)">
                                <span class="sr-only">{{ $i18n.get('label_select_item') }}</span>
                            </b-checkbox>
                            <b-radio
                                    v-else
                                    name="item-single-selection"
                                    :native-value="item.id"
                                    v-model="singleItemSelection">
                                <span class="sr-only">{{ $i18n.get('label_select_item') }}</span>
                            </b-radio>
                        </td>
                        <td 
                                v-if="isOnAllItemsTabs"
                                class="status-cell">
                            <span 
                                    v-if="$statusHelper.hasIcon(item.status)"
                                    class="icon has-text-gray"
                                    v-tooltip="{
                                        content: $i18n.get('status_' + item.status),
                                        autoHide: true,
                                        popperClass: ['tainacan-tooltip', 'tooltip', isRepositoryLevel ? 'tainacan-repository-tooltip' : ''],
                                        placement: 'auto-start'
                                    }">
                                <i 
                                        class="tainacan-icon tainacan-icon-1em"
                                        :class="$statusHelper.getIcon(item.status)"
                                        />
                            </span>
                        </td>
                        <!-- Item Displayed Metadata -->
                        <td
                                :key="columnIndex"
                                v-for="(column, columnIndex) in displayedMetadata"
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
                                    v-tooltip="{
                                        delay: {
                                            shown: 500,
                                            hide: 300,
                                        },
                                        content: item.title != undefined && item.title != '' ? item.title : `<span class='has-text-gray3 is-italic'>` + $i18n.get('label_value_not_provided') + `</span>`,
                                        html: true,
                                        autoHide: false,
                                        popperClass: ['tainacan-tooltip', 'tooltip', isRepositoryLevel ? 'tainacan-repository-tooltip' : ''],
                                        placement: 'auto-start'
                                    }"
                                    v-if="collectionId == undefined &&
                                          column.metadata_type_object != undefined &&
                                          column.metadata_type_object.related_mapped_prop == 'title'"
                                    v-html="`<span class='sr-only'>` + column.name + ': </span>' + ((item.title != undefined && item.title != '') ? item.title : `<span class='has-text-gray3 is-italic'>` + $i18n.get('label_value_not_provided') + `</span>`)"/>
                            <p
                                    v-tooltip="{
                                        delay: {
                                            shown: 500,
                                            hide: 300,
                                        },
                                        content: item.description != undefined && item.description != '' ? item.description : `<span class='has-text-gray3 is-italic'>` + $i18n.get('label_value_not_provided') + `</span>`,
                                        html: true,
                                        popperClass: ['tainacan-tooltip', 'tooltip', isRepositoryLevel ? 'tainacan-repository-tooltip' : ''],
                                        autoHide: false,
                                        placement: 'auto-start'
                                    }"
                                    v-if="collectionId == undefined &&
                                          column.metadata_type_object != undefined &&
                                          column.metadata_type_object.related_mapped_prop == 'description'"
                                    v-html="`<span class='sr-only'>` + column.name + ': </span>' + ((item.description != undefined && item.description) != '' ? item.description : `<span class='has-text-gray3 is-italic'>` + $i18n.get('label_value_not_provided') + `</span>`)"/>
                            <p
                                    v-tooltip="{
                                        delay: {
                                            shown: 500,
                                            hide: 300,
                                        },
                                        popperClass: [ 'tainacan-tooltip', 'tooltip', column.metadata_type_object != undefined && column.metadata_type_object.component == 'tainacan-textarea' ? 'metadata-type-textarea' : '', isRepositoryLevel ? 'tainacan-repository-tooltip' : ''],
                                        content: renderMetadata(item.metadata, column) != '' ? renderMetadata(item.metadata, column) : `<span class='has-text-gray3 is-italic'>` + $i18n.get('label_value_not_provided') + `</span>`,
                                        html: true,
                                        autoHide: false,
                                        placement: 'auto-start'
                                    }"
                                    v-if="item.metadata != undefined &&
                                          column.metadatum !== 'row_thumbnail' &&
                                          column.metadatum !== 'row_actions' &&
                                          column.metadatum !== 'row_creation' &&
                                          column.metadatum !== 'row_modification' &&
                                          column.metadatum !== 'row_author' &&
                                          column.metadatum !== 'row_title' &&
                                          column.metadatum !== 'row_description'"
                                    v-html="renderMetadata(item.metadata, column) != '' ? renderMetadata(item.metadata, column) : `<span class='has-text-gray3 is-italic'>` + $i18n.get('label_value_not_provided') + `</span>`"/>

                            <span 
                                    class="table-thumb"
                                    v-if="column.metadatum == 'row_thumbnail'">
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
                                    v-tooltip="{
                                        delay: {
                                            shown: 500,
                                            hide: 300,
                                        },
                                        content: item[column.slug],
                                        html: true,
                                        autoHide: false,
                                        placement: 'auto-start',
                                        popperClass: ['tainacan-tooltip', 'tooltip', isRepositoryLevel ? 'tainacan-repository-tooltip' : '']
                                    }"
                                    v-if="column.metadatum == 'row_author'">
                                    {{ item[column.slug] }}
                            </p>
                            <p
                                    v-tooltip="{
                                        delay: {
                                            shown: 500,
                                            hide: 300,
                                        },
                                        content: parseDateToNavigatorLanguage(item[column.slug]),
                                        html: true,
                                        autoHide: false,
                                        popperClass: ['tainacan-tooltip', 'tooltip', isRepositoryLevel ? 'tainacan-repository-tooltip' : ''],
                                        placement: 'auto-start'
                                    }"
                                    v-if="column.metadatum == 'row_modification'">
                                    {{ parseDateToNavigatorLanguage(item[column.slug]) }}
                            </p>
                            <p
                                    v-tooltip="{
                                        delay: {
                                            shown: 500,
                                            hide: 300,
                                        },
                                        content: parseDateToNavigatorLanguage(item[column.slug]),
                                        html: true,
                                        autoHide: false,
                                        popperClass: ['tainacan-tooltip', 'tooltip', isRepositoryLevel ? 'tainacan-repository-tooltip' : ''],
                                        placement: 'auto-start'
                                    }"
                                    v-if="column.metadatum == 'row_creation'">
                                    {{ parseDateToNavigatorLanguage(item[column.slug]) }}
                            </p>

                        </td>

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
                                        <i class="has-text-secondary tainacan-icon tainacan-icon-1-25em tainacan-icon-edit"/>
                                    </span>
                                </a>
                                <a
                                        :aria-lavel="$i18n.get('label_button_untrash')"
                                        @click.prevent.stop="untrashOneItem(item.id)"
                                        v-if="isOnTrash">
                                    <span
                                            v-tooltip="{
                                                content: $i18n.get('label_recover_from_trash'),
                                                autoHide: true,
                                                placement: 'auto',
                                                popperClass: ['tainacan-tooltip', 'tooltip', isRepositoryLevel ? 'tainacan-repository-tooltip' : '']
                                            }"
                                            class="icon">
                                        <i class="has-text-secondary tainacan-icon tainacan-icon-1-25em tainacan-icon-undo"/>
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
                                            class="has-text-secondary tainacan-icon tainacan-icon-1-25em"/>
                                </span>
                                </a>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>

            <!-- LIST VIEW MODE -->
            <div
                    role="list"
                    v-if="viewMode == 'list'"
                    :class="{ 'hide-items-selection': $adminOptions.hideItemsListSelection }"
                    class="tainacan-list-container">
                <div 
                        role="listitem"
                        :href="item.url"
                        :key="index"
                        :data-tainacan-item-id="item.id"
                        v-for="(item, index) of items"
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
                                    @input="setSelectedItemChecked(item.id)"
                                    :aria-label="$i18n.get('label_select_item')">
                            <input
                                    v-else
                                    type="radio"
                                    name="item-single-selection"
                                    :value="item.id"
                                    v-model="singleItemSelection"
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
                                class="icon has-text-gray"
                                v-tooltip="{
                                    content: $i18n.get('status_' + item.status),
                                    autoHide: true,
                                    popperClass: ['tainacan-tooltip', 'tooltip', isRepositoryLevel ? 'tainacan-repository-tooltip' : ''],
                                    placement: 'auto-start'
                                }">
                            <i 
                                    class="tainacan-icon tainacan-icon-1em"
                                    :class="$statusHelper.getIcon(item.status)"
                                    />
                        </span>
                        <p 
                                v-tooltip="{
                                    delay: {
                                        shown: 500,
                                        hide: 300,
                                    },
                                    content: item.metadata != undefined ? renderMetadata(item.metadata, column) : '',
                                    html: true,
                                    autoHide: false,
                                    placement: 'auto-start',
                                    popperClass: ['tainacan-tooltip', 'tooltip', isRepositoryLevel ? 'tainacan-repository-tooltip' : '']
                                }"
                                @click.left="onClickItem($event, item)"
                                @click.right="onRightClickItem($event, item)"
                                v-for="(column, metadatumIndex) in displayedMetadata"
                                :key="metadatumIndex"
                                v-if="column.display && column.metadata_type_object != undefined && (column.metadata_type_object.related_mapped_prop == 'title')"
                                v-html="item.metadata != undefined && collectionId ? renderMetadata(item.metadata, column) : (item.title ? item.title :`<span class='has-text-gray3 is-italic'>` + $i18n.get('label_value_not_provided') + `</span>`)" />                 
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
                                <i class="has-text-secondary tainacan-icon tainacan-icon-1-25em tainacan-icon-edit"/>
                            </span>
                        </a>
                        <a
                                :aria-lavel="$i18n.get('label_button_untrash')"
                                @click.prevent.stop="untrashOneItem(item.id)"
                                v-if="isOnTrash">
                            <span
                                    v-tooltip="{
                                        content: $i18n.get('label_recover_from_trash'),
                                        autoHide: true,
                                        placement: 'auto',
                                        popperClass: ['tainacan-tooltip', 'tooltip', isRepositoryLevel ? 'tainacan-repository-tooltip' : '']
                                    }"
                                    class="icon">
                                <i class="has-text-secondary tainacan-icon tainacan-icon-1-25em tainacan-icon-undo"/>
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
                                        class="has-text-secondary tainacan-icon tainacan-icon-1-25em"/>
                            </span>
                        </a>
                    </div>

                    <!-- Remaining metadata -->  
                    <div 
                            @click.left="onClickItem($event, item)"
                            @click.right="onRightClickItem($event, item)"
                            class="media">
                         <div 
                                class="tainacan-list-thumbnail"
                                v-if="item.thumbnail != undefined">
                                <blur-hash-image
                                    @click.left="onClickItem($event, item)"
                                    @click.right="onRightClickItem($event, item)"
                                    v-if="item.thumbnail != undefined"
                                    class="tainacan-list-item-thumbnail"
                                    :width="$thumbHelper.getWidth(item['thumbnail'], 'tainacan-medium-full', 120)"
                                    :height="$thumbHelper.getHeight(item['thumbnail'], 'tainacan-medium-full', 120)"
                                    :hash="$thumbHelper.getBlurhashString(item['thumbnail'], 'tainacan-medium-full')"
                                    :src="$thumbHelper.getSrc(item['thumbnail'], 'tainacan-medium-full', item.document_mimetype)"
                                    :srcset="$thumbHelper.getSrcSet(item['thumbnail'], 'tainacan-medium-full', item.document_mimetype)"
                                    :alt="item.thumbnail_alt ? item.thumbnail_alt : $i18n.get('label_thumbnail')"
                                    :transition-duration="500"
                                />
                        </div>
                        <div class="list-metadata media-body">
                            <span 
                                    v-for="(column, metadatumIndex) in displayedMetadata"
                                    :key="metadatumIndex"
                                    :class="{ 'metadata-type-textarea': column.metadata_type_object.component == 'tainacan-textarea' }"
                                    v-if="renderMetadata(item.metadata, column) != '' && column.display && column.slug != 'thumbnail' && column.metadata_type_object != undefined && (column.metadata_type_object.related_mapped_prop != 'title')">
                                <h3 class="metadata-label">{{ column.name }}</h3>
                                <p      
                                        v-html="renderMetadata(item.metadata, column)"
                                        class="metadata-value"/>
                            </span>
                            <span
                                    v-for="(column, metadatumIndex) in displayedMetadata"
                                    :key="metadatumIndex"
                                    v-if="(column.metadatum == 'row_modification' || column.metadatum == 'row_creation' || column.metadatum == 'row_author') && item[column.slug] != undefined">
                                <h3 class="metadata-label">{{ column.name }}</h3>
                                <p
                                        v-html="(column.metadatum == 'row_creation' || column.metadatum == 'row_modification') ? parseDateToNavigatorLanguage(item[column.slug]) : item[column.slug]"
                                        class="metadata-value"/>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</template>

<script>
import { mapActions, mapGetters } from 'vuex';
import CustomDialog from '../other/custom-dialog.vue';
import ItemCopyDialog from '../other/item-copy-dialog.vue';
import BulkEditionModal from '../modals/bulk-edition-modal.vue';
import Masonry from 'masonry-layout';
import { dateInter } from "../../js/mixins";

export default {
    name: 'ItemsList',
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
    data(){
        return {
            isAllItemsSelected: false,
            queryAllItemsSelected: {},
            cursorPosX: -1,
            cursorPosY: -1,
            contextMenuItem: null,
            singleItemSelection: false,
            masonry: false
        }
    },
    computed: {
        collection() {
            return this.getCollection();
        },
        highlightedItem () {
            return this.getHighlightedItem();
        },
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
            for (var i = 0; i < this.items.length; i++){
                if (this.selectedItems.indexOf(this.items[i].id) === -1)
                    return false;
            }
            return true;
        },
        itemsPerPage(){
            return this.getItemsPerPage();
        },
        totalPages(){
            return Math.ceil(Number(this.totalItems)/Number(this.itemsPerPage));
        },
        isOnAllItemsTabs() {
            const currentStatus = this.getStatus();
            return !currentStatus || (currentStatus.indexOf(',') > 0);
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
                    this.$nextTick(() => {
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
        }
    },
    mounted() {
        if (this.highlightsItem)
            setTimeout(() => this.$eventBusSearch.highlightsItem(null), 3000);
    },
    methods: {
        ...mapActions('collection', [
            'deleteItem',
        ]),
        ...mapGetters('collection', [
            'getCollection',
        ]),
        ...mapActions('bulkedition', [
            'createEditGroup',
            'createSequenceEditGroup',
            'trashItemsInBulk',
            'deleteItemsInBulk',
            'untrashItemsInBulk'
        ]),
        ...mapGetters('bulkedition', [
            'getGroupId'
        ]),
        ...mapActions('search', [
            'setSeletecItems',
            'cleanSelectedItems',
            'addSelectedItem',
            'removeSelectedItem'
        ]),
        ...mapGetters('search', [
            'getOrder',
            'getOrderBy',
            'getStatus',
            'getSelectedItems',
            'getHighlightedItem',
            'getItemsPerPage'
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
                component: BulkEditionModal,
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
                let sequenceId = this.getGroupId();
                this.$router.push(this.$routerHelper.getCollectionSequenceEditPath(this.collectionId, sequenceId, 1));
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
                component: ItemCopyDialog,
                canCancel: false,
                props: {
                    icon: 'items',
                    collectionId: this.collectionId,
                    itemId: itemId,
                    onConfirm: (newItems) => {
                        if (newItems != null && newItems != undefined && newItems.length > 0) {
                            this.$eventBusSearch.loadItems();
                        }
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
                        this.$emit('updateIsLoading', true);

                        this.createEditGroup({
                            collectionId: this.collectionId,
                            object: [itemId]
                        }).then(() => {
                            let groupId = this.getGroupId();

                            this.untrashItemsInBulk({
                                collectionId: this.collectionId,
                                groupId: groupId
                            }).then(() => {
                                this.$eventBusSearch.loadItems();
                            });
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
                        this.$emit('updateIsLoading', true);

                        this.deleteItem({
                            itemId: itemId,
                            isPermanently: this.isOnTrash
                        }).then(() => {
                            this.$eventBusSearch.loadItems();
                        });
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
                        this.$emit('updateIsLoading', true);

                        this.createEditGroup({
                            collectionId: this.collectionId,
                            object: Object.keys(this.queryAllItemsSelected).length ? this.queryAllItemsSelected : this.selectedItems
                        }).then(() => {
                            let groupId = this.getGroupId();

                            this.untrashItemsInBulk({
                                collectionId: this.collectionId,
                                groupId: groupId
                            }).then(() => {
                                this.$eventBusSearch.loadItems();
                                this.$root.$emit('openProcessesPopup');
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
                        this.$emit('updateIsLoading', true);

                        this.createEditGroup({
                            collectionId: this.collectionId,
                            object: Object.keys(this.queryAllItemsSelected).length ? this.queryAllItemsSelected : this.selectedItems
                        }).then(() => {
                            let groupId = this.getGroupId();

                            if (this.isOnTrash) {
                                this.deleteItemsInBulk({
                                    collectionId: this.collectionId,
                                    groupId: groupId
                                }).then(() => {
                                    this.$eventBusSearch.loadItems();
                                    this.$root.$emit('openProcessesPopup');
                                });
                            } else {
                                this.trashItemsInBulk({
                                    collectionId: this.collectionId,
                                    groupId: groupId
                                }).then(() => {
                                    this.$eventBusSearch.loadItems();
                                    this.$root.$emit('openProcessesPopup');
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
            this.$eventBusSearch.filterBySelectedItems(this.selectedItems);
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
            if (this.contextMenuItem != null) {
                this.setSelectedItemChecked(this.contextMenuItem.id);
            }
            this.clearContextMenu();
        },
        onClickItem($event, item) {
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
        renderMetadata(itemMetadata, column) {

            let metadata = (itemMetadata != undefined && itemMetadata[column.slug] != undefined) ? itemMetadata[column.slug] : false;

            if (!metadata || itemMetadata == undefined) {
                return '';
            } else {
                return this.viewMode == 'table' ? ('<span class="sr-only">' + column.name + ': </span>' + metadata.value_as_html) : metadata.value_as_html;
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
    @import "../../scss/_view-mode-cards.scss";
    @import "../../scss/_view-mode-masonry.scss";
    @import "../../scss/_view-mode-grid.scss";
    @import "../../scss/_view-mode-records.scss";
    @import "../../scss/_view-mode-list.scss";
    
    // Vue Blurhash transtition effect
    @import '../../../../../node_modules/vue-blurhash/dist/vue-blurhash.css';

    .selection-control {
        margin-bottom: 6px;
        padding: 6px 0px 0px 12px;
        background: var(--tainacan-white);
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


