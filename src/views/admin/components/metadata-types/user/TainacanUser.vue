<template>
    <div :class="{ 'is-flex': itemMetadatum.metadatum.multiple != 'yes' || maxtags != undefined }">
        <b-taginput
                :id="'tainacan-item-metadatum_id-' + itemMetadatum.metadatum.id + (itemMetadatum.parent_meta_id ? ('_parent_meta_id-' + itemMetadatum.parent_meta_id) : '')"
                expanded
                :disabled="disabled"
                size="is-small"
                icon="account"
                :model-value="JSON.parse(JSON.stringify(selected))"
                :data="options"
                :maxtags="maxtags != undefined ? Number(maxtags) : (itemMetadatum.metadatum.multiple == 'yes' || allowNew === true ? (maxMultipleValues !== undefined ? Number(maxMultipleValues) : null) : 1)"
                autocomplete
                attached
                :placeholder="itemMetadatum.metadatum.placeholder ? itemMetadatum.metadatum.placeholder : $i18n.get('instruction_type_search_users')"
                keep-first
                open-on-focus
                :remove-on-keys="[]"
                :dropdown-position="isLastMetadatum ? 'top' :'auto'"
                :loading="isLoading || isLoading"
                :aria-close-label="$i18n.get('remove_value')"
                :class="{'has-selected': selected != undefined && selected != []}"
                field="name"
                check-infinite-scroll
                :has-counter="false"
                @update:model-value="onInput"
                @blur="onBlur"
                @typing="search"
                @focus="onMobileSpecialFocus"
                @infinite-scroll="searchMore">
            <template #default="props">
                <div class="media">
                    <div
                            v-if="props.option.img"
                            class="media-left">
                        <img
                                width="24"
                                :src="props.option.img">
                    </div>
                    <div class="media-content">
                        {{ props.option.name }}
                    </div>
                </div>
            </template>
            <template #tag="props">
                {{ (props.tag && props.tag.name) ? props.tag.name : '' }}
            </template>
            <template 
                    v-if="!isLoading"
                    #empty>
                {{ $i18n.get('info_no_user_found') }}
            </template>
        </b-taginput>
    </div>
</template>

<script>
import { wpApi } from '../../../js/axios';
import { mapActions } from 'vuex';
import qs from 'qs';
    
export default {
    props: {
        itemMetadatum: Object,
        maxtags: undefined,
        disabled: false,
        allowNew: true,
        isLastMetadatum: false
    },
    emits: [
        'update:value',
        'blur',
        'mobile-special-focus'
    ],
    data() {
        return {
            selected:[],
            options: [],
            isLoading: false,
            usersSearchQuery: '',
            usersSearchPage: 1,
            totalUsers: 0
        }
    },
    computed: {
        maxMultipleValues() {
            return (
                this.itemMetadatum &&
                this.itemMetadatum.metadatum &&
                this.itemMetadatum.metadatum.cardinality &&
                !isNaN(this.itemMetadatum.metadatum.cardinality) &&
                this.itemMetadatum.metadatum.cardinality > 1
            ) ? this.itemMetadatum.metadatum.cardinality : undefined;
        }
    },
    created() {
        this.loadCurrentUsers();
    },
    methods: {
        ...mapActions('activity', [
            'fetchUsers'
        ]),
        onInput(newSelected) {
            this.selected = newSelected;
            this.$emit('update:value', this.selected.map((user) => user.id || user.value));
        },
        onBlur() {
            this.$emit('blur');
        },
        loadCurrentUsers() {
            if (
                (Array.isArray(this.itemMetadatum.value) && this.itemMetadatum.value.length) ||
                (!Array.isArray(this.itemMetadatum.value) && this.itemMetadatum.value)
            ) {
                
                this.isLoading = true;
                let perPage = isNaN(this.itemMetadatum.value) ? this.itemMetadatum.value.length + 1 : 100;
                let query = qs.stringify({ include: this.itemMetadatum.value, per_page: perPage });
                let endpoint = '/users/';
                
                wpApi.get(endpoint + '?' + query)
                    .then((res) => {
                        this.selected = res.data.map((user) => ({
                            name: user.name,
                            value: user.id,
                            img: user.avatar_urls && user.avatar_urls['24'] ? user.avatar_urls['24'] : ''
                        }));
                        this.isLoading = false;
                    })
                    .catch(() => this.isLoading = false );
            }
        },
        search: _.debounce(function (search) {
           
            // String update
            if (search != this.usersSearchQuery) {
                this.usersSearchQuery = search;
                this.options = [];
                this.usersSearchPage = 1;
            } 
            
            // String cleared
            if (!search.length) {
                this.usersSearchQuery = search;
                this.options = [];
                this.usersSearchPage = 1;
            }

            // No need to load more
            if (this.usersSearchPage > 1 && this.options.length > this.totalUsers)
                return;

            // There is already one value set and is not multiple
            if (this.selected.length > 0 && this.itemMetadatum.metadatum.multiple === 'no')
                return;

            if (this.usersSearchQuery !== '') {          
                this.isLoading = true;
                
                this.fetchUsers({ search: this.usersSearchQuery, page: this.usersSearchPage, exclude: this.selected.map((user) => user.value || user.id ) })
                    .then((res) => {
                        if (res.users) {
                            for (let user of res.users)
                                this.options.push({
                                    name: user.name,
                                    value: user.id,
                                    img:  user.avatar_urls && user.avatar_urls['24'] ? user.avatar_urls['24'] : ''
                                }); 
                        }
                        
                        if (res.totalUsers)
                            this.totalUsers = res.totalUsers;

                        this.usersSearchPage++;
                        
                        this.isLoading = false;
                    })
                    .catch((error) => {
                        this.$console.error(error);
                        this.isFetchingPages = false;
                    });
            }
        }, 500),
        searchMore: _.debounce(function () {
            this.search(this.usersSearchQuery)
        }, 250),
        onMobileSpecialFocus() {
            this.$emit('mobile-special-focus');
        }
    }
}
</script>

<style scoped>
    div.is-flex {
        justify-content: flex-start;
    }
</style>