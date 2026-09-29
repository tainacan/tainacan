<template>
    <div>
        <div 
                v-if="!isStatusTheOnlyField && !$adminOptions.itemEditionPublicationSectionInsideTabs"
                class="section-label">
            <label>
                <span 
                        class="icon has-text-dark"
                        aria-hidden="true">
                    <i class="tainacan-icon tainacan-icon-item" />
                </span>
                {{ collection && collection.item_publication_label ? collection.item_publication_label : $i18n.get('label_publication_data') }}
            </label>
            <help-button
                    :title="collection && collection.item_publication_label ? collection.item_publication_label : $i18n.get('label_publication_data')"
                    :message="$i18n.get('info_publication_data') + (collection && collection.current_user_can_edit ? $i18n.get('info_publication_data_editing') : '')" />
        </div>
        <div 
                class="section-box publication-field"
                :class="{ 'has-only-status-field': isStatusTheOnlyField }">

            <!-- Authorship -->
            <div 
                    v-if="collection && collection.allow_item_author_editing && collection.allow_item_author_editing == 'yes'"
                    class="section-authorship">
                <div class="field is-horizontal has-addons">
                    <div class="field-label">
                        <label class="label">{{ $i18n.get('label_authorship') }}</label>
                    </div>
                    <div 
                            v-tooltip="{
                                content: item.status === 'auto-draft' ? $i18n.get('instruction_create_item_before_change_author') : '',
                                autoHide: true,
                                placement: 'auto',
                                popperClass: ['tainacan-tooltip', 'tooltip']
                            }"
                            class="field-body">
                        <div class="field has-addons">
                            <b-autocomplete
                                    v-model="authorSearch"
                                    v-a11y-autocomplete="{ appendToBody: true }"
                                    :clearable="item.status !== 'auto-draft'"
                                    :clear-on-select="false"
                                    :data="users"
                                    :placeholder="$i18n.get('instruction_type_search_users')"
                                    keep-first
                                    open-on-focus
                                    :loading="isFetchingUsers"
                                    field="name"
                                    icon="account"
                                    :disabled="item.status === 'auto-draft'"
                                    check-infinite-scroll
                                    @focus="browseUsersForAuthor"
                                    @active="onAuthorSuggestionsActive"
                                    @typing="fetchUsersForAuthor"
                                    @select="openAuthorEditingDialog"
                                    @infinite-scroll="fetchMoreUsersForAuthor">
                                <template #default="props">
                                    <div class="media">
                                        <div
                                                v-if="props.option.avatar_urls && props.option.avatar_urls['24']"
                                                class="media-left">
                                            <img
                                                    width="24"
                                                    :src="props.option.avatar_urls['24']">
                                        </div>
                                        <div class="media-content">
                                            {{ props.option.name }}
                                        </div>
                                    </div>
                                </template>
                                <template 
                                        v-if="!isFetchingUsers"
                                        #empty>
                                    {{ $i18n.get('info_no_user_found') }}
                                </template>
                            </b-autocomplete>
                            <help-button
                                    :title="$i18n.get('label_authorship')"
                                    :message="$i18n.get('info_authorship')" />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Slug -------------------------------- -->
            <div 
                    v-if="collection && collection.allow_item_slug_editing && collection.allow_item_slug_editing == 'yes'"
                    class="section-slug">
                <div class="field is-horizontal has-addons">
                    <div class="field-label">
                        <label class="label">{{ $i18n.get('label_slug') }}</label>
                    </div>
                    <div
                            v-tooltip="{
                                content: item.status === 'auto-draft' ? $i18n.get('instruction_create_item_before_change_slug') : '',
                                autoHide: true,
                                placement: 'auto',
                                popperClass: ['tainacan-tooltip', 'tooltip']
                            }"
                            class="field-body">
                        <div class="field has-addons">
                            <div 
                                    id="tainacan-text-slug"
                                    class="control has-icons-left is-expanded is-clearfix"
                                    :class="{ 'is-loading': isUpdatingSlug }">
                                <input
                                        v-model="currentSlug"
                                        type="text"
                                        class="input"
                                        expanded
                                        :disabled="isUpdatingSlug || item.status === 'auto-draft'"
                                        @update:model-value="updateSlug">
                                <span 
                                        id="url-prefix-indicator"
                                        v-tooltip="{
                                            content: item.status !== 'auto-draft' && item.url ? item.url : '',
                                            autoHide: true,
                                            placement: 'bottom',
                                            popperClass: ['tainacan-tooltip', 'tooltip']
                                        }"
                                        class="icon is-left">
                                    <i class="mdi mdi-24px" />
                                </span>
                            </div>
                        </div>
                    </div>
                    <help-button 
                            :title="$i18n.getHelperTitle('items', 'slug')" 
                            :message="$i18n.getHelperMessage('items', 'slug')" />
                </div>
            </div>

            <!-- Item Status -->
            <div 
                    v-if="!$adminOptions.hideItemEditionStatusOption && !$adminOptions.itemEditionStatusOptionOnFooterDropdown"
                    class="section-status">
                <div class="field is-horizontal has-addons">
                    <div :class="isStatusTheOnlyField ? 'section-label' : 'field-label'">
                        <label class="label">
                            <span 
                                    v-if="isStatusTheOnlyField"
                                    class="icon has-text-dark"
                                    aria-hidden="true">
                                <i class="tainacan-icon tainacan-icon-item" />
                            </span>
                            {{ $i18n.get('label_status') }}
                        </label>
                    </div>
                    <div
                            v-tooltip="{
                                content: item.status === 'auto-draft' ? $i18n.get('instruction_create_item_before_change_status') : '',
                                autoHide: true,
                                placement: 'auto',
                                popperClass: ['tainacan-tooltip', 'tooltip']
                            }"
                            class="field-body">
                        <div class="field has-addons">
                            <!-- Update dropdown with -->
                            <b-dropdown
                                    ref="item-edition-status-dropdown"
                                    v-a11y-dropdown="{ appendToBody: true }"
                                    :trigger-tabindex="-1"
                                    class="item-edition-status-dropdown"
                                    :triggers="[ 'click' ]"
                                    :append-to-body="true"
                                    :disabled="item.status === 'auto-draft' || ( hasSomeError && (form.status == 'publish' || form.status == 'private' || form.status == 'pending' ) )"
                                    style="width: auto;"
                                    max-height="300px">
                                <template #trigger>
                                    <button 
                                            :disabled="item.status === 'auto-draft' || ( hasSomeError && (form.status == 'publish' || form.status == 'private' || form.status == 'pending' ) )"
                                            type="button"
                                            class="button is-outlined"
                                            :class="{ 'disabled': item.status === 'auto-draft' || ( hasSomeError && (form.status == 'publish' || form.status == 'private' || form.status == 'pending' ) ) }"
                                            style="width: auto;">
                                        <span 
                                                aria-hidden="true"
                                                class="icon has-text-dark">
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
                                                style="margin-inline-start: 0.5em;"
                                                class="icon is-small"
                                                aria-hidden="true">
                                            <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-arrowdown" />
                                        </span>
                                    </button>
                                </template>
                                <b-dropdown-item 
                                        v-for="(statusOption, index) of getAvailableStatus()"
                                        :key="index"
                                        @click="$emit(
                                            'on-submit',
                                            statusOption.slug,
                                            'current'
                                        )"
                                        @keydown.enter.prevent="$emit(
                                            'on-submit',
                                            statusOption.slug,
                                            'current'
                                        )"
                                        @keydown.space.prevent="$emit(
                                            'on-submit',
                                            statusOption.slug,
                                            'current'
                                        )">
                                    <span 
                                            aria-hidden="true"
                                            class="icon has-text-dark">
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
                            <help-button
                                    :title="$i18n.getHelperTitle('items', 'status')"
                                    :message="$i18n.getHelperMessage('items', 'status')" />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Comment Status ------------------------ -->
            <div 
                    v-if="collection && collection.allow_comments && collection.allow_comments == 'open' && !$adminOptions.hideItemEditionCommentsToggle"
                    class="section-status">
                <div class="field is-horizontal has-addons">
                    <div class="field-label">
                        <label class="label">{{ $i18n.get('label_comments') }}</label>
                    </div>
                    <div class="field-body">
                        <div class="field has-addons">
                            <b-switch
                                    id="tainacan-checkbox-comment-status"
                                    size="is-small"
                                    true-value="open"
                                    false-value="closed"
                                    :model-value="form.comment_status"
                                    @update:model-value="$emit('on-update-comment-status', $event)">
                                {{ $i18n.get('label_allow_comments') }}
                                <help-button
                                        :title="$i18n.getHelperTitle('items', 'comment_status')"
                                        :message="$i18n.get('info_comment_status')" />
                            </b-switch>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { mapActions } from 'vuex';
import CustomDialog from '../other/custom-dialog.vue';

export default {
    props: {
        item: Object,
        form: Object,
        collection: Object,
        isLoading: Boolean,
        isUpdatingSlug: Boolean,
        hasSomeError: Boolean,
        currentUserCanDelete: Boolean,
        currentUserCanPublish: Boolean,
    },
    emits: [
        'on-update-comment-status',
        'on-submit',
        'on-update-item-slug',
        'on-update-item-author'
    ],
    data() {
        return {
            currentSlug: '',
            users: [],
            isFetchingUsers: false,
            usersPage: 1,
            usersPerPage: 12,
            usersTotal: 0,
            usersTotalPages: 0,
            usersSearch: '',
            authorSearch: '',
            committedAuthorName: '',
        };
    },
    computed: {
        isStatusTheOnlyField() {
            return !this.collection ||
                ( !this.collection.allow_item_slug_editing || this.collection.allow_item_slug_editing != 'yes' ) &&
                ( !this.collection.allow_item_author_editing || this.collection.allow_item_author_editing != 'yes' ) &&
                ( !this.collection.allow_comments || this.collection.allow_comments != 'open' );
        },
    },
    watch: {
        item: {
            handler() {
                this.currentSlug = this.item && this.item.slug ? JSON.parse(JSON.stringify(this.item.slug)) : '';
                const authorName = this.item && this.item.author_name ? this.item.author_name : '';
                if (authorName !== this.committedAuthorName) {
                    this.committedAuthorName = authorName;
                    this.authorSearch = authorName;
                }
            },
            deep: true,
            immediate: true
        }
    },
    methods: {
        ...mapActions('activity', [
            'fetchUsers'
        ]),
        openAuthorEditingDialog(nextAuthor) {
           if ( !nextAuthor || nextAuthor.id == this.item.author_id )
                return;

            const modalTrigger = this.$modalFocusA11y.captureTrigger();
            this.$buefy.modal.open({
                component: CustomDialog,
                props: {
                    icon: 'userfill',
                    title: this.$i18n.get('label_editing_publication_authorship'),
                    message: this.$i18n.get('info_editing_publication_authorship') + ' <br><br><strong>' + this.$i18n.getWithVariables( 'info_change_author_from_%s_to_%s', [ this.item.author_name,nextAuthor.name ] ) + '<strong>',
                    onConfirm: () => {
                        this.$emit('on-update-item-author', nextAuthor.id);
                        this.committedAuthorName = nextAuthor.name;
                        this.authorSearch = nextAuthor.name;
                    }
                },
                trapFocus: true,
                customClass: 'tainacan-modal authorship-modal',
                canCancel: ['escape', 'outside'],
                width: 620,
                events: {
                    beforeClose: () => this.$modalFocusA11y.restoreFocus(modalTrigger, this)
                }
            });   
        },
        browseUsersForAuthor() {
            this.usersSearch = '';
            this.users = [];
            this.usersPage = 1;
            this.totalUsers = 0;
            this.isFetchingUsers = true;
            this.requestUsersForAuthor('');
        },
        onAuthorSuggestionsActive(isOpen) {
            if (isOpen)
                return;

            if (this.committedAuthorName && this.authorSearch !== this.committedAuthorName)
                this.authorSearch = this.committedAuthorName;
        },
        fetchUsersForAuthor: _.debounce(function (search) {
            const query = search || '';

            if (this.committedAuthorName && query === this.committedAuthorName)
                return;

            if (query !== this.usersSearch) {
                this.usersSearch = query;
                this.users = [];
                this.usersPage = 1;
                this.totalUsers = 0;
            }

            if (this.usersPage > 1 && this.users.length >= Number(this.totalUsers))
                return;

            this.isFetchingUsers = true;
            this.requestUsersForAuthor(query);
        }, 500),
        requestUsersForAuthor(query) {
            this.fetchUsers({ search: query, page: this.usersPage, perPage: this.usersPerPage })
                .then((res) => {
                    const userList = res.users ? res.users : [];
                    if (this.usersPage === 1)
                        this.users = userList;
                    else {
                        for (let user of userList)
                            this.users.push(user);
                    }

                    this.totalUsers = res.totalUsers ? Number(res.totalUsers) : this.users.length;
                    this.usersPage++;
                    this.isFetchingUsers = false;
                })
                .catch((error) => {
                    this.$console.error(error);
                    this.isFetchingUsers = false;
                });
        },
        fetchMoreUsersForAuthor: _.debounce(function () {
            this.fetchUsersForAuthor(this.usersSearch)
        }, 250),
        updateSlug: _.debounce(function($event) {
                if ( !$event || this.form.slug == $event )
                    return;

                this.$emit('on-update-item-slug', $event)
            }, 800),
            getAvailableStatus() {
                return this.$statusHelper.getStatuses().filter(
                        (status) => {

                            if ( status.slug == 'trash' )
                                return false;

                            if ( status.slug == 'pending' && ( this.$adminOptions.hideItemEditionPendingOption ) )
                                return false;

                            if ( status.slug == 'publish' && ( this.$adminOptions.hideItemEditionStatusPublishOption || !this.currentUserCanPublish ) )
                                return false;

                            if ( status.slug == 'private' && ( this.$adminOptions.hideItemEditionStatusPrivateOption || !this.currentUserCanPublish ) )
                                return false;

                            return true;
                        });
            }
    }
}
</script>

<style scoped lang="scss">
    .publication-field {
        display: flex;
        flex-direction: column;
        gap: 0.5em;

        @supports (contain: inline-size) {
            container-type: inline-size;
            container-name: publicationfield; 
        }

        .field-label {
            white-space: nowrap;
            text-align: start;
            text-align: start;
            min-width: 9ch;
            max-width: 9ch;
            margin-inline-end: 1rem;
            margin-bottom: 0;
        }
        .field.has-addons {
            align-items: center;
        }
        .field.is-horizontal {
            @container publicationfield (max-width: 280px) {
                align-items: start;
                flex-wrap: wrap;

                .field-label {
                    max-width: 100%;
                    width: 100%;
                    min-width: 100%;
                }
            }
        }
        .tainacan-help-tooltip-trigger {
            margin-inline-start: 0.5rem;
        }
        #tainacan-text-slug #url-prefix-indicator {
            pointer-events: initial;
        }
        #tainacan-text-slug #url-prefix-indicator i::before {
            content: '.../';
            font-family: var(--tainacan-font-family);
            font-size: 1.125em;
            opacity: 0.5;
            margin-inline-end: -0.35em;
            color: var(--tainacan-info-color);
            cursor: pointer;
        }
        &.has-only-status-field {
            padding-left: 0 !important;
            padding-top: .75em !important;

            .dropdown,
            .field.has-addons {
                align-items: center;
            }
        } 
    }
    :deep(.authorship-modal) .dialog .modal-card {
        max-width: 100%;
    }
</style>