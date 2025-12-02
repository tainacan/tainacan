<template>
    <div class="tabs">
        <ul 
                v-a11y-tabs
                role="tablist"
                data-orientation="horizontal">               
            <li 
                    :tabindex="-1"
                    :class="{ 'is-active': status == undefined || status == '' || status == 'publish,private,pending,draft' }">
                <a
                        id="items-status-tab-all"
                        v-tooltip="{
                            content: $i18n.get('info_items_tab_all'),
                            autoHide: true,
                            placement: 'auto',
                            popperClass: ['tainacan-tooltip', 'tooltip', isRepositoryLevel ? 'tainacan-repository-tooltip' : '']
                        }"
                        role="tab"
                        :aria-selected="status == undefined || status == '' || status == 'publish,private,pending,draft'"
                        :tabindex="(status == undefined || status == '' || status == 'publish,private,pending,draft') ? 0 : -1"
                        style="font-weight: bold;"
                        @click="onChangeTab('')">
                    {{ $i18n.get('label_all_items') }}
                    <span 
                            v-if="!$adminOptions.hideItemsListStatusTabsTotalItems && !$route.query.authorid"
                            class="has-text-dark">
                        &nbsp;{{ (isRepositoryLevel && repositoryTotalItems) ? ` (${ repositoryTotalItems.private + repositoryTotalItems.pending + repositoryTotalItems.publish + repositoryTotalItems.draft })` : (collection && collection.total_items ? ` (${Number(collection.total_items.private) + Number(collection.total_items.pending) + Number(collection.total_items.publish) + Number(collection.total_items.draft)})` : '') }}
                    </span>
                </a>
            </li>
            <template
                    v-for="(statusOption, index) of $statusHelper.getStatuses()"
                    :key="index">
                <li 
                        :tabindex="-1"
                        :class="{ 'is-active': status == statusOption.slug}"
                        :style="{ marginRight: statusOption.slug == 'draft' ? 'auto' : '', marginLeft: statusOption.slug == 'trash' ? 'auto' : '' }">
                    <a
                            :id="'items-status-tab-' + statusOption.slug"
                            v-tooltip="{
                                content: $i18n.getWithVariables('info_%s_tab_' + statusOption.slug,[$i18n.get('items')]),
                                autoHide: true,
                                placement: 'auto',
                                popperClass: ['tainacan-tooltip', 'tooltip', isRepositoryLevel ? 'tainacan-repository-tooltip' : '']
                            }"
                            role="tab"
                            :aria-selected="status == statusOption.slug"
                            :tabindex="status == statusOption.slug ? 0 : -1"
                            @click="onChangeTab(statusOption.slug)">
                        <span 
                                v-if="$statusHelper.hasIcon(statusOption.slug)"
                                class="icon has-text-dark">
                            <i 
                                    class="tainacan-icon tainacan-icon-1-125em"
                                    :class="$statusHelper.getIcon(statusOption.slug)"
                                    aria-hidden="true" />
                        </span>
                        {{ statusOption.name }}
                        <span 
                                v-if="!$adminOptions.hideItemsListStatusTabsTotalItems && !$route.query.authorid"
                                class="has-text-dark">
                            &nbsp;{{ (isRepositoryLevel && repositoryTotalItems) ? ` (${ repositoryTotalItems[statusOption.slug] })` : (collection && collection.total_items ? ` (${collection.total_items[statusOption.slug]})` : '') }}
                        </span>
                    </a>
                </li>
            </template>
        </ul>
    </div>
</template>

<script>
import { mapGetters } from 'vuex';

export default {
    props: {
        isRepositoryLevel: Boolean
    },
    computed: {
        ...mapGetters('search', {
            'status': 'getStatus'
        }),
        ...mapGetters('collection', {
            'collection': 'getCollection'
        }),
        repositoryTotalItems() {

            if (!this.$adminOptions.hideItemsListStatusTabsTotalItems) {
                let collections = this.getCollections();

                let total_items = {
                    trash: 0,
                    publish: 0,
                    draft: 0,
                    private: 0,
                    pending: 0
                };

                for (let collection of collections) {
                    if ( collection.total_items ) {
                        total_items.trash += Number(collection.total_items.trash);
                        total_items.draft += Number(collection.total_items.draft);
                        total_items.publish += Number(collection.total_items.publish);
                        total_items.private += Number(collection.total_items.private);
                        total_items.pending += Number(collection.total_items.pending);
                    }
                }

                return total_items;
            } else {
                return '';
            }
        }
    },
    methods: {
        ...mapGetters('collection', [
            'getCollections'
        ]),
        onChangeTab(status) {
            this.$eventBusSearch.resetPageOnStore();
            this.$eventBusSearch.setStatus(status);
        }
    }
}
</script>