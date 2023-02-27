<template>
    <div class="tabs">
        <ul>               
            <li 
                    @click="onChangeTab('')"
                    :class="{ 'is-active': status == undefined || status == '' || status == 'publish,private,draft' }"
                    v-tooltip="{
                        content: $i18n.get('info_items_tab_all'),
                        autoHide: true,
                        placement: 'auto',
                    }">
                <a :style="{ fontWeight: 'bold', color: 'var(--tainacan-gray5) !important' }">
                    {{ $i18n.get('label_all_items') }}
                    <span 
                            v-if="!$adminOptions.hideItemsListStatusTabsTotalItems"
                            class="has-text-gray">
                        &nbsp;{{ (isRepositoryLevel && repositoryTotalItems) ? ` (${ repositoryTotalItems.private + repositoryTotalItems.publish + repositoryTotalItems.draft })` : (collection && collection.total_items ? ` (${Number(collection.total_items.private) + Number(collection.total_items.publish) + Number(collection.total_items.draft)})` : '') }}
                    </span>
                </a>
            </li>
            <li 
                    v-for="(statusOption, index) of $statusHelper.getStatuses()"
                    v-if="(isRepositoryLevel || statusOption.slug != 'private') || (statusOption.slug == 'private' && collection && collection.current_user_can_read_private_items)"
                    :key="index"
                    @click="onChangeTab(statusOption.slug)"
                    :class="{ 'is-active': status == statusOption.slug}"
                    :style="{ marginRight: statusOption.slug == 'draft' ? 'auto' : '', marginLeft: statusOption.slug == 'trash' ? 'auto' : '' }"
                    v-tooltip="{
                        content: $i18n.getWithVariables('info_%s_tab_' + statusOption.slug,[$i18n.get('items')]),
                        autoHide: true,
                        placement: 'auto',
                        popperClass: ['tainacan-tooltip', 'tooltip', isRepositoryLevel ? 'tainacan-repository-tooltip' : '']
                    }">
                <a>
                    <span 
                            v-if="$statusHelper.hasIcon(statusOption.slug)"
                            class="icon has-text-gray">
                        <i 
                                class="tainacan-icon tainacan-icon-1-125em"
                                :class="$statusHelper.getIcon(statusOption.slug)"
                                />
                    </span>
                    {{ statusOption.name }}
                    <span 
                            v-if="!$adminOptions.hideItemsListStatusTabsTotalItems"
                            class="has-text-gray">
                        &nbsp;{{ (isRepositoryLevel && repositoryTotalItems) ? ` (${ repositoryTotalItems[statusOption.slug] })` : (collection && collection.total_items ? ` (${collection.total_items[statusOption.slug]})` : '') }}
                    </span>
                </a>
            </li>
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
        status() {
            return this.getStatus();
        },
        collection() {
            return this.getCollection();
        },
        repositoryTotalItems() {

            if (!this.$adminOptions.hideItemsListStatusTabsTotalItems) {
                let collections = this.getCollections();

                let total_items = {
                    trash: 0,
                    publish: 0,
                    draft: 0,
                    private: 0
                };

                for(let collection of collections){
                    total_items.trash += Number(collection.total_items.trash);
                    total_items.draft += Number(collection.total_items.draft);
                    total_items.publish += Number(collection.total_items.publish);
                    total_items.private += Number(collection.total_items.private);
                }

                return total_items;
            } else {
                return '';
            }
        }
    },
    methods: {
        ...mapGetters('search', [
            'getStatus'
        ]),
        ...mapGetters('collection', [
            'getCollection',
            'getCollections'
        ]),
        onChangeTab(status) {
            this.$eventBusSearch.resetPageOnStore();
            this.$eventBusSearch.setStatus(status);
        }
    }
}
</script>