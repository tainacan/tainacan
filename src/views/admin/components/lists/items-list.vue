<template>
    <div class="table-container">

        <!-- TODO: Remove v-if="collectionId" from this element when the bulk edit in repository is done -->
        <div
                v-if="collectionId && collection && collection.current_user_can_edit_items && collection.current_user_can_bulk_edit"
                class="selection-control">
            <div class="field select-all is-pulled-left">
                <span>
                    <b-checkbox
                            @click.native.prevent="selectAllItemsOnPage()"
                            :value="allItemsOnPageSelected">
                        {{ $i18n.get('label_select_all_items_page') }}
                    </b-checkbox>
                </span>

                <span
                        style="margin-left: 10px"
                        v-if="allItemsOnPageSelected && items.length > 1">
                    <b-checkbox
                            v-model="isAllItemsSelected">
                        {{ $i18n.getWithVariables('label_select_all_%s_items', [totalItems]) }}
                    </b-checkbox>
                </span>
            </div>

            <div class="field">
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
                        <span>{{ $i18n.get('label_bulk_actions') }}</span>
                        <span class="icon">
                            <i class="tainacan-icon tainacan-icon-20px tainacan-icon-arrowdown"/>
                        </span>
                    </button>

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
                            @click="untrashSelectedItems()"
                            aria-role="listitem">
                        {{ $i18n.get('label_untrash_selected_items') }}
                    </b-dropdown-item>
                </b-dropdown>
            </div>
        </div>

        <div class="table-wrapper">

            <!-- Context menu for right click selection -->
            <div
                    v-if="cursorPosY > 0 && cursorPosX > 0 && !$route.query.readmode"
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
                            v-if="!isOnTrash && !$route.query.iframemode">
                        {{ $i18n.getFrom('items','view_item') }}
                    </b-dropdown-item>
                    <b-dropdown-item
                            @click="openItemOnNewTab()"
                            v-if="!isOnTrash && !$route.query.iframemode">
                        {{ $i18n.get('label_open_item_new_tab') }}
                    </b-dropdown-item>
                    <b-dropdown-item
                            @click="selectItem()"
                            v-if="contextMenuItem != null">
                        {{ getSelectedItemChecked(contextMenuItem.id) == true ? $i18n.get('label_unselect_item') : $i18n.get('label_select_item') }}
                    </b-dropdown-item>
                    <b-dropdown-item
                            @click="goToItemEditPage(contextMenuItem)"
                            v-if="contextMenuItem != null && contextMenuItem.current_user_can_edit && !$route.query.iframemode">
                        {{ $i18n.getFrom('items','edit_item') }}
                    </b-dropdown-item>
                    <b-dropdown-item
                            @click="makeCopiesOfOneItem(contextMenuItem.id)"
                            v-if="contextMenuItem != null && contextMenuItem.current_user_can_edit && !$route.query.iframemode">
                        {{ $i18n.get('label_make_copies_of_item') }}
                    </b-dropdown-item>
                    <b-dropdown-item
                            @click="deleteOneItem(contextMenuItem.id)"
                            v-if="contextMenuItem != null && contextMenuItem.current_user_can_edit && !$route.query.iframemode">
                        {{ $i18n.get('label_delete_item') }}
                    </b-dropdown-item>
                </b-dropdown>
            </div>

            <!-- GRID (THUMBNAILS) VIEW MODE -->
            <div
                    role="list"
                    class="tainacan-grid-container"
                    v-if="viewMode == 'grid'">
                <div
                        role="listitem"
                        :key="index"
                        v-for="(item, index) of items"
                        :class="{ 'selected-grid-item': getSelectedItemChecked(item.id) == true }"
                        class="tainacan-grid-item">

                    <!-- Checkbox -->
                    <!-- TODO: Remove v-if="collectionId" from this element when the bulk edit in repository is done -->
                    <div
                            v-if="collectionId && !$route.query.readmode && ($route.query.iframemode || collection && collection.current_user_can_bulk_edit)"
                            :class="{ 'is-selecting': isSelectingItems }"
                            class="grid-item-checkbox">
                        <b-checkbox
                                size="is-small"
                                :value="getSelectedItemChecked(item.id)"
                                @input="setSelectedItemChecked(item.id)"/>
                    </div>

                    <!-- Title -->
                    <div
                            :style="{ 'padding-left': !collectionId || !($route.query.iframemode || collection && collection.current_user_can_bulk_edit) || $route.query.readmode ? '0.5rem !important' : '2.75rem' }"
                            class="metadata-title">
                        <p
                                v-tooltip="{
                                    delay: {
                                        show: 500,
                                        hide: 300,
                                    },
                                    content: item.title != undefined ? item.title : '',
                                    html: true,
                                    autoHide: false,
                                    placement: 'auto-start'
                                }"
                                @click.left="onClickItem($event, item)"
                                @click.right="onRightClickItem($event, item)">
                            {{ item.title != undefined ? item.title : '' }}
                        </p>
                    </div>

                    <!-- Thumbnail -->
                    <a
                            v-if="item.thumbnail != undefined"
                            @click.left="onClickItem($event, item)"
                            @click.right="onRightClickItem($event, item)"
                            class="grid-item-thumbnail"
                            :style="{ backgroundImage: 'url(' + (item['thumbnail']['tainacan-medium'] ? item['thumbnail']['tainacan-medium'][0] : (item['thumbnail'].medium ? item['thumbnail'].medium[0] : thumbPlaceholderPath)) + ')' }">
                        <img
                                :alt="$i18n.get('label_thumbnail')"
                                :src="item['thumbnail']['tainacan-medium'] ? item['thumbnail']['tainacan-medium'][0] : (item['thumbnail'].medium ? item['thumbnail'].medium[0] : thumbPlaceholderPath)">
                    </a>

                    <!-- Actions -->
                    <div
                            v-if="item.current_user_can_edit && !$route.query.iframemode"
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
                                        placement: 'auto'
                                    }"
                                    class="icon">
                                <i class="has-text-secondary tainacan-icon tainacan-icon-20px tainacan-icon-edit"/>
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
                                        placement: 'auto'
                                    }"
                                    class="icon">
                                <i class="has-text-secondary tainacan-icon tainacan-icon-20px tainacan-icon-undo"/>
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
                                        placement: 'auto'
                                    }"
                                    class="icon">
                                <i
                                        :class="{ 'tainacan-icon-delete': !isOnTrash, 'tainacan-icon-deleteforever': isOnTrash }"
                                        class="has-text-secondary tainacan-icon tainacan-icon-20px"/>
                            </span>
                        </a>
                    </div>

                </div>
            </div>

            <!-- MASONRY VIEW MODE -->
            <masonry
                    role="list"
                    v-if="viewMode == 'masonry'"
                    :cols="{default: 7, 1919: 6, 1407: 5, 1215: 4, 1023: 3, 767: 2, 343: 1}"
                    :gutter="25"
                    class="tainacan-masonry-container">
                <div
                        role="listitem"
                        :key="index"
                        v-for="(item, index) of items"
                        :class="{
                            'selected-masonry-item': getSelectedItemChecked(item.id) == true,
                        }"
                        class="tainacan-masonry-item">

                    <!-- Checkbox -->
                    <!-- TODO: Remove v-if="collectionId" from this element when the bulk edit in repository is done -->
                    <div
                            v-if="collectionId && !$route.query.readmode && ($route.query.iframemode || collection && collection.current_user_can_bulk_edit)"
                            :class="{ 'is-selecting': isSelectingItems }"
                            class="masonry-item-checkbox">
                        <label
                                tabindex="0"
                                class="b-checkbox checkbox is-small">
                            <input
                                    type="checkbox"
                                    :checked="getSelectedItemChecked(item.id)"
                                    @input="setSelectedItemChecked(item.id)">
                                <span class="check" />
                                <span class="control-label" />
                        </label>
                    </div>

                    <!-- Title -->
                    <div
                            :style="{
                                'padding-left': !collectionId || !($route.query.iframemode || collection && collection.current_user_can_bulk_edit) || $route.query.readmode ? '0 !important' : '1rem'
                            }"
                            @click.left="onClickItem($event, item)"
                            @click.right="onRightClickItem($event, item)"
                            class="metadata-title">
                        <p>{{ item.title != undefined ? item.title : '' }}</p>
                    </div>

                    <!-- Thumbnail -->
                    <div
                            @click.left="onClickItem($event, item)"
                            @click.right="onRightClickItem($event, item)"
                            v-if="item.thumbnail != undefined"
                            class="tainacan-masonry-item-thumbnail"
                            :style="{ backgroundImage: 'url(' + (item['thumbnail']['tainacan-medium-full'] ? item['thumbnail']['tainacan-medium-full'][0] : (item['thumbnail'].medium_large ? item['thumbnail'].medium_large[0] : thumbPlaceholderPath)) + ')' }">
                        <img
                                :alt="$i18n.get('label_thumbnail')"
                                :src="item['thumbnail']['tainacan-medium-full'] ? item['thumbnail']['tainacan-medium-full'][0] : (item['thumbnail'].medium_large ? item['thumbnail'].medium_large[0] : thumbPlaceholderPath)">
                    </div>

                    <!-- Actions -->
                    <div
                            v-if="item.current_user_can_edit && !$route.query.iframemode"
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
                                        placement: 'auto'
                                    }"
                                    class="icon">
                                <i class="has-text-secondary tainacan-icon tainacan-icon-20px tainacan-icon-edit"/>
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
                                        placement: 'auto'
                                    }"
                                    class="icon">
                                <i class="has-text-secondary tainacan-icon tainacan-icon-20px tainacan-icon-undo"/>
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
                                        placement: 'auto'
                                    }"
                                    class="icon">
                                <i
                                        :class="{ 'tainacan-icon-delete': !isOnTrash, 'tainacan-icon-deleteforever': isOnTrash }"
                                        class="has-text-secondary tainacan-icon tainacan-icon-20px"/>
                            </span>
                        </a>
                    </div>
                </div>
            </masonry>

            <!-- CARDS VIEW MODE -->
            <div
                    role="list"
                    class="tainacan-cards-container"
                    v-if="viewMode == 'cards'">
                <div
                        role="listitem"
                        :key="index"
                        v-for="(item, index) of items"
                        :class="{ 'selected-card': getSelectedItemChecked(item.id) == true }"
                        class="tainacan-card">

                    <!-- Checkbox -->
                    <!-- TODO: Remove v-if="collectionId" from this element when the bulk edit in repository is done -->
                    <div
                            v-if="collectionId && !$route.query.readmode && ($route.query.iframemode || collection && collection.current_user_can_bulk_edit)"
                            :class="{ 'is-selecting': isSelectingItems }"
                            class="card-checkbox">
                        <b-checkbox
                                size="is-small"
                                :value="getSelectedItemChecked(item.id)"
                                @input="setSelectedItemChecked(item.id)"/>
                    </div>

                    <!-- Title -->
                    <div
                            :style="{ 
                                'padding-left': !collectionId || $route.query.readmode || !($route.query.iframemode || collection && collection.current_user_can_bulk_edit) ? '0.5rem !important' : '2.75rem',
                                'margin-bottom': item.current_user_can_edit && !$route.query.iframemode ? '-26px' : '0px'
                            }"
                            class="metadata-title">
                        <p
                                v-tooltip="{
                                    delay: {
                                        show: 500,
                                        hide: 300,
                                    },
                                    content: item.title != undefined ? item.title : '',
                                    html: true,
                                    autoHide: false,
                                    placement: 'auto-start'
                                }"
                                @click.left="onClickItem($event, item)"
                                @click.right="onRightClickItem($event, item)">
                            {{ item.title != undefined ? item.title : '' }}
                        </p>
                    </div>
                    <!-- Actions -->
                    <div
                            v-if="item.current_user_can_edit && !$route.query.iframemode"
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
                                        placement: 'auto'
                                    }"
                                    class="icon">
                                <i class="has-text-secondary tainacan-icon tainacan-icon-20px tainacan-icon-edit"/>
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
                                        placement: 'auto'
                                    }"
                                    class="icon">
                                <i class="has-text-secondary tainacan-icon tainacan-icon-20px tainacan-icon-undo"/>
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
                                        placement: 'auto'
                                    }"
                                    class="icon">
                                <i
                                        :class="{ 'tainacan-icon-delete': !isOnTrash, 'tainacan-icon-deleteforever': isOnTrash }"
                                        class="has-text-secondary tainacan-icon tainacan-icon-20px"/>
                            </span>
                        </a>
                    </div>

                    <!-- Remaining metadata -->
                    <div
                            class="media"
                            @click.left="onClickItem($event, item)"
                            @click.right="onRightClickItem($event, item)">
                        <div
                                :style="{ backgroundImage: 'url(' + (item['thumbnail']['tainacan-medium'] ? item['thumbnail']['tainacan-medium'][0] : (item['thumbnail'].medium ? item['thumbnail'].medium[0] : thumbPlaceholderPath)) + ')' }"
                                class="card-thumbnail">
                            <img
                                    :alt="$i18n.get('label_thumbnail')"
                                    v-if="item.thumbnail != undefined"
                                    :src="item['thumbnail']['tainacan-medium'] ? item['thumbnail']['tainacan-medium'][0] : (item['thumbnail'].medium ? item['thumbnail'].medium[0] : thumbPlaceholderPath)">
                        </div>

                        <div class="list-metadata media-body">
                            <!-- Description -->
                            <p
                                    v-tooltip="{
                                        delay: {
                                            show: 500,
                                            hide: 300,
                                        },
                                        content: item.description != undefined && item.description != '' ? item.description : `<span class='has-text-gray3 is-italic'>` + $i18n.get('label_description_not_informed') + `</span>`,
                                        html: true,
                                        autoHide: false,
                                        placement: 'auto-start'
                                    }"
                                    class="metadata-description"
                                    v-html="item.description != undefined && item.description != '' ? getLimitedDescription(item.description) : `<span class='has-text-gray3 is-italic'>` + $i18n.get('label_description_not_informed') + `</span>`" />
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
                                        placement: 'auto-start'
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
                                        placement: 'auto-start'
                                    }"
                                    class="metadata-author-creation">
                                {{ $i18n.get('info_date') + ' ' + (item.creation_date != undefined ? parseDateToNavigatorLanguage(item.creation_date) : '') }}
                            </p>
                        </div>
                    </div>

                </div>
            </div>

            <!-- RECORDS VIEW MODE -->
            <masonry
                    role="list"
                    :cols="{default: 4, 1919: 3, 1407: 2, 1215: 2, 1023: 1, 767: 1, 343: 1}"
                    :gutter="30"
                    class="tainacan-records-container"
                    v-if="viewMode == 'records'">
                <div
                        role="listitem"
                        :key="index"
                        v-for="(item, index) of items"
                        :class="{ 'selected-record': getSelectedItemChecked(item.id) == true }"
                        class="tainacan-record">

                    <!-- Checkbox -->
                    <!-- TODO: Remove v-if="collectionId" from this element when the bulk edit in repository is done -->
                    <div
                            v-if="collectionId && !$route.query.readmode && ($route.query.iframemode || collection && collection.current_user_can_bulk_edit)"
                            :class="{ 'is-selecting': isSelectingItems }"
                            class="record-checkbox">
                        <label
                                tabindex="0"
                                class="b-checkbox checkbox is-small">
                            <input
                                    type="checkbox"
                                    :checked="getSelectedItemChecked(item.id)"
                                    @input="setSelectedItemChecked(item.id)">
                                <span class="check" />
                                <span class="control-label" />
                        </label>
                    </div>

                    <!-- Title -->
                    <div
                            class="metadata-title"
                            :style="{
                                'padding-left': !collectionId || !($route.query.iframemode || collection && collection.current_user_can_bulk_edit) || $route.query.readmode ? '1.5rem !important' : '2.75rem',    
                                'margin-bottom': item.current_user_can_edit || $route.query.iframemode ? '-27px' : '0px'
                            }">
                        <p 
                                v-tooltip="{
                                    delay: {
                                        show: 500,
                                        hide: 300,
                                    },
                                    content: item.metadata != undefined ? renderMetadata(item.metadata, column) : '',
                                    html: true,
                                    autoHide: false,
                                    placement: 'auto-start'
                                }"
                                v-for="(column, columnIndex) in tableMetadata"
                                :key="columnIndex"
                                v-if="collectionId != undefined && column.display && column.metadata_type_object != undefined && (column.metadata_type_object.related_mapped_prop == 'title')"
                                @click.left="onClickItem($event, item)"
                                @click.right="onRightClickItem($event, item)"
                                v-html="item.metadata != undefined ? renderMetadata(item.metadata, column) : ''" />
                        <p
                                v-tooltip="{
                                    delay: {
                                        show: 500,
                                        hide: 300,
                                    },
                                    content: item.title != undefined ? item.title : '',
                                    html: true,
                                    autoHide: false,
                                    placement: 'auto-start'
                                }"
                                v-for="(column, columnIndex) in tableMetadata"
                                :key="columnIndex"
                                v-if="collectionId == undefined && column.display && column.metadata_type_object != undefined && (column.metadata_type_object.related_mapped_prop == 'title')"
                                @click.left="onClickItem($event, item)"
                                @click.right="onRightClickItem($event, item)"
                                v-html="item.title != undefined ? item.title : ''" />
                    </div>
                    <!-- Actions -->
                    <div
                            v-if="item.current_user_can_edit && !$route.query.iframemode"
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
                                        placement: 'auto'
                                    }"
                                    class="icon">
                                <i class="has-text-secondary tainacan-icon tainacan-icon-20px tainacan-icon-edit"/>
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
                                        placement: 'auto'
                                    }"
                                    class="icon">
                                <i class="has-text-secondary tainacan-icon tainacan-icon-20px tainacan-icon-undo"/>
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
                                        placement: 'auto'
                                    }"
                                    class="icon">
                                <i
                                        :class="{ 'tainacan-icon-delete': !isOnTrash, 'tainacan-icon-deleteforever': isOnTrash }"
                                        class="has-text-secondary tainacan-icon tainacan-icon-20px"/>
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
                                <img
                                        :alt="$i18n.get('label_thumbnail')"
                                        v-if="item.thumbnail != undefined"
                                        :src="item['thumbnail']['tainacan-medium-full'] ? item['thumbnail']['tainacan-medium-full'][0] : (item['thumbnail'].medium_large ? item['thumbnail'].medium_large[0] : thumbPlaceholderPath)">
                            </div>
                            <span
                                    v-for="(column, index) in tableMetadata"
                                    :key="index"
                                    :class="{ 'metadata-type-textarea': column.metadata_type_object != undefined && column.metadata_type_object.component == 'tainacan-textarea' }"
                                    v-if="collectionId == undefined && column.display && column.metadata_type_object != undefined && (column.metadata_type_object.related_mapped_prop == 'description')">
                                <h3 class="metadata-label">{{ $i18n.get('label_description') }}</h3>
                                <p
                                        v-html="item.description != undefined ? item.description : ''"
                                        class="metadata-value"/>
                            </span>
                            <span
                                    v-for="(column, index) in tableMetadata"
                                    :key="index"
                                    :class="{ 'metadata-type-textarea': column.metadata_type_object != undefined && column.metadata_type_object.component == 'tainacan-textarea' }"
                                    v-if="renderMetadata(item.metadata, column) != '' && column.display && column.slug != 'thumbnail' && column.metadata_type_object != undefined && (column.metadata_type_object.related_mapped_prop != 'title')">
                                <h3 class="metadata-label">{{ column.name }}</h3>
                                <p
                                        v-html="renderMetadata(item.metadata, column)"
                                        class="metadata-value"/>
                            </span>
                            <span
                                    v-for="(column, index) in tableMetadata"
                                    :key="index"
                                    v-if="(column.metadatum == 'row_creation' || column.metadatum == 'row_author') && item[column.slug] != undefined">
                                <h3 class="metadata-label">{{ column.name }}</h3>
                                <p
                                        v-html="column.metadatum == 'row_creation' ? parseDateToNavigatorLanguage(item[column.slug]) : item[column.slug]"
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
                                v-if="collectionId && !$route.query.readmode && ($route.query.iframemode || collection && collection.current_user_can_bulk_edit)">
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
                <tbody>
                    <tr
                            :class="{
                                'selected-row': getSelectedItemChecked(item.id) == true,
                                'highlighted-item': highlightedItem == item.id
                            }"
                            :key="index"
                            v-for="(item, index) of items">
                        <!-- Checking list -->
                        <!-- TODO: Remove v-if="collectionId" from this element when the bulk edit in repository is done -->
                        <td
                                v-if="collectionId && !$route.query.readmode && ($route.query.iframemode || collection && collection.current_user_can_bulk_edit)"
                                :class="{ 'is-selecting': isSelectingItems }"
                                class="checkbox-cell">
                            <b-checkbox
                                    size="is-small"
                                    :value="getSelectedItemChecked(item.id)"
                                    @input="setSelectedItemChecked(item.id)"/>
                        </td>

                        <!-- Item Displayed Metadata -->
                        <td
                                :key="columnIndex"
                                v-for="(column, columnIndex) in tableMetadata"
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
                                                                                                            column.metadata_type_object.primitive_type == 'term' ||
                                                                                                            column.metadata_type_object.primitive_type == 'compound') : false,
                                        'column-large-width' : column.metadata_type_object != undefined ? (column.metadata_type_object.primitive_type == 'long_string' || column.metadata_type_object.related_mapped_prop == 'description') : false,
                                }"
                                @click.left="onClickItem($event, item)"
                                @click.right="onRightClickItem($event, item)">

                            <p
                                    v-tooltip="{
                                        delay: {
                                            show: 500,
                                            hide: 300,
                                        },
                                        content: item.title != undefined && item.title != '' ? item.title : `<span class='has-text-gray3 is-italic'>` + $i18n.get('label_value_not_informed') + `</span>`,
                                        html: true,
                                        autoHide: false,
                                        placement: 'auto-start'
                                    }"
                                    v-if="collectionId == undefined &&
                                          column.metadata_type_object != undefined &&
                                          column.metadata_type_object.related_mapped_prop == 'title'"
                                    v-html="`<span class='sr-only'>` + column.name + ': </span>' + ((item.title != undefined && item.title != '') ? item.title : `<span class='has-text-gray3 is-italic'>` + $i18n.get('label_value_not_informed') + `</span>`)"/>
                            <p
                                    v-tooltip="{
                                        delay: {
                                            show: 500,
                                            hide: 300,
                                        },
                                        content: item.description != undefined && item.description != '' ? item.description : `<span class='has-text-gray3 is-italic'>` + $i18n.get('label_value_not_informed') + `</span>`,
                                        html: true,
                                        autoHide: false,
                                        placement: 'auto-start'
                                    }"
                                    v-if="collectionId == undefined &&
                                          column.metadata_type_object != undefined &&
                                          column.metadata_type_object.related_mapped_prop == 'description'"
                                    v-html="`<span class='sr-only'>` + column.name + ': </span>' + ((item.description != undefined && item.description) != '' ? item.description : `<span class='has-text-gray3 is-italic'>` + $i18n.get('label_value_not_informed') + `</span>`)"/>
                            <p
                                    v-tooltip="{
                                        delay: {
                                            show: 500,
                                            hide: 300,
                                        },
                                        classes: [ column.metadata_type_object != undefined && column.metadata_type_object.component == 'tainacan-textarea' ? 'metadata-type-textarea' : '' ],
                                        content: renderMetadata(item.metadata, column) != '' ? renderMetadata(item.metadata, column) : `<span class='has-text-gray3 is-italic'>` + $i18n.get('label_value_not_informed') + `</span>`,
                                        html: true,
                                        autoHide: false,
                                        placement: 'auto-start'
                                    }"
                                    v-if="item.metadata != undefined &&
                                          column.metadatum !== 'row_thumbnail' &&
                                          column.metadatum !== 'row_actions' &&
                                          column.metadatum !== 'row_creation' &&
                                          column.metadatum !== 'row_author' &&
                                          column.metadatum !== 'row_title' &&
                                          column.metadatum !== 'row_description'"
                                    v-html="renderMetadata(item.metadata, column) != '' ? renderMetadata(item.metadata, column) : `<span class='has-text-gray3 is-italic'>` + $i18n.get('label_value_not_informed') + `</span>`"/>

                            <span v-if="column.metadatum == 'row_thumbnail'">
                                <img
                                        :alt="$i18n.get('label_thumbnail')"
                                        class="table-thumb"
                                        :src="item['thumbnail']['tainacan-small'] ? item['thumbnail']['tainacan-small'][0] : (item['thumbnail'].thumbnail ? item['thumbnail'].thumbnail[0] : thumbPlaceholderPath)">
                            </span>
                            <p
                                    v-tooltip="{
                                        delay: {
                                            show: 500,
                                            hide: 300,
                                        },
                                        content: item[column.slug],
                                        html: true,
                                        autoHide: false,
                                        placement: 'auto-start'
                                    }"
                                    v-if="column.metadatum == 'row_author'">
                                    {{ item[column.slug] }}
                            </p>
                            <p
                                    v-tooltip="{
                                        delay: {
                                            show: 500,
                                            hide: 300,
                                        },
                                        content: parseDateToNavigatorLanguage(item[column.slug]),
                                        html: true,
                                        autoHide: false,
                                        placement: 'auto-start'
                                    }"
                                    v-if="column.metadatum == 'row_creation'">
                                    {{ parseDateToNavigatorLanguage(item[column.slug]) }}
                            </p>

                        </td>

                        <!-- Actions -->
                        <td 
                                v-if="(item.current_user_can_edit || item.current_user_can_delete) && !$route.query.iframemode"
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
                                                placement: 'auto'
                                            }"
                                            class="icon">
                                        <i class="has-text-secondary tainacan-icon tainacan-icon-20px tainacan-icon-edit"/>
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
                                                placement: 'auto'
                                            }"
                                            class="icon">
                                        <i class="has-text-secondary tainacan-icon tainacan-icon-20px tainacan-icon-undo"/>
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
                                                placement: 'auto'
                                            }"
                                            class="icon">
                                    <i
                                            :class="{ 'tainacan-icon-delete': !isOnTrash, 'tainacan-icon-deleteforever': isOnTrash }"
                                            class="has-text-secondary tainacan-icon tainacan-icon-20px"/>
                                </span>
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
import ItemCopyDialog from '../other/item-copy-dialog.vue';
import BulkEditionModal from '../modals/bulk-edition-modal.vue';
import { dateInter } from "../../js/mixins";

export default {
    name: 'ItemsList',
    mixins: [ dateInter ],
    props: {
        collectionId: undefined,
        tableMetadata: Array,
        items: Array,
        isLoading: false,
        isOnTrash: false,
        totalItems: Number,
        viewMode: 'card'
    },
    data(){
        return {
            isAllItemsSelected: false,
            queryAllItemsSelected: {},
            thumbPlaceholderPath: tainacan_plugin.base_url + '/assets/images/placeholder_square.png',
            cursorPosX: -1,
            cursorPosY: -1,
            contextMenuItem: null
        }
    },
    computed: {
        collection() {
            return this.getCollection();
        },
        highlightedItem () {
            return this.getHighlightedItem();
        },
        selectedItemsFromStore() {
            return this.getSelectedItems();
        },
        selectedItems () {
            if (this.$route.query.iframemode)
                this.$eventBusSearch.setSelectedItemsForIframe(this.getSelectedItems());

            return this.getSelectedItems();
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
        }
    },
    mounted() {
        this.cleanSelectedItems();

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
            'getGroupID'
        ]),
        ...mapActions('item', [
            'fetchItem'
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
            'getSelectedItems',
            'getHighlightedItem'
        ]),
        setSelectedItemChecked(itemId) {
            if (this.selectedItems.find((item) => item == itemId) != undefined)
                this.removeSelectedItem(itemId);
            else
                this.addSelectedItem(itemId);
        },
        getSelectedItemChecked(itemId) {
            return this.selectedItems.find(item => item == itemId) != undefined;
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
                    collectionID: this.$route.params.collectionId,
                },
                width: 'calc(100% - 8.333333333%)',
                trapFocus: true
            });
        },
        sequenceEditSelectedItems() {
            this.createSequenceEditGroup({
                object: Object.keys(this.queryAllItemsSelected).length ? this.queryAllItemsSelected : this.selectedItems,
                collectionID: this.collectionId
            }).then(() => {
                let sequenceId = this.getGroupID();
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
                trapFocus: true
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
                        this.isLoading = true;
                        this.$emit('updateIsLoading', this.isLoading);

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
                },
                trapFocus: true
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
                        this.isLoading = true;
                        this.$emit('updateIsLoading', this.isLoading);

                        this.deleteItem({
                            itemId: itemId,
                            isPermanently: this.isOnTrash
                        }).then(() => {
                            this.$eventBusSearch.loadItems();
                        });
                    }
                },
                trapFocus: true
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
                        this.isLoading = true;
                        this.$emit('updateIsLoading', this.isLoading);

                        this.createEditGroup({
                            collectionID: this.collectionId,
                            object: Object.keys(this.queryAllItemsSelected).length ? this.queryAllItemsSelected : this.selectedItems
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
                },
                trapFocus: true
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
                        this.isLoading = true;
                        this.$emit('updateIsLoading', this.isLoading);

                        this.createEditGroup({
                            collectionID: this.collectionId,
                            object: Object.keys(this.queryAllItemsSelected).length ? this.queryAllItemsSelected : this.selectedItems
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
                },
                trapFocus: true
            });
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
            if ($event.ctrlKey || $event.shiftKey) {
                this.setSelectedItemChecked(item.id);
            } else {
                if (this.$route.query.iframemode && !this.$route.query.readmode) {
                    this.setSelectedItemChecked(item.id)
                } else if (!this.$route.query.iframemode && !this.$route.query.readmode) {
                    if(this.isOnTrash){
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
            if (!this.$route.query.readmode) {
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

    .selection-control {

        padding: 6px 0px 0px 12px;
        background: white;
        height: 40px;
        display: flex;

        .select-all {
            color: $gray4;
            font-size: 0.875rem;
            margin-right: auto;

            &:hover {
                color: $gray4;
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


