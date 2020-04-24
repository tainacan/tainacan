<template>
    <div :class="{ 'is-flex': itemMetadatum.metadatum.multiple != 'yes' || maxtags != undefined }">
        <b-taginput
                expanded
                :disabled="disabled"
                :id="'tainacan-item-metadatum_id-' + itemMetadatum.metadatum.id + (itemMetadatum.parent_meta_id ? ('_parent_meta_id-' + itemMetadatum.parent_meta_id) : '')"
                size="is-small"
                icon="account"
                :value="selected"
                @input="onInput"
                @blur="onBlur"
                :data="options"
                :maxtags="maxtags != undefined ? maxtags : (itemMetadatum.metadatum.multiple == 'yes' || allowNew === true ? 100 : 1)"
                autocomplete
                attached
                :placeholder="$i18n.get('instruction_type_search_users')"
                keep-first
                open-on-focus
                :loading="isLoading || isLoading"
                :aria-close-label="$i18n.get('remove_value')"
                :class="{'has-selected': selected != undefined && selected != []}"
                field="name"
                @typing="search"
                check-infinite-scroll
                @infinite-scroll="searchMore">
            <template slot-scope="props">
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
                    v-if="!isLoading"
                    slot="empty">
                {{ $i18n.get('info_no_user_found') }}
            </template>
        </b-taginput>
    </div>
</template>

<script>
import { wp as wpAxios } from '../../../js/axios';
import { mapActions } from 'vuex';
import qs from 'qs';
    
export default {
    props: {
        itemMetadatum: Object,
        maxtags: undefined,
        disabled: false,
        allowNew: true,
    },
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
    created() {
        this.loadCurrentUsers();
    },
    methods: {
        ...mapActions('activity', [
            'fetchUsers'
        ]),
        onInput(newSelected) {
            this.selected = newSelected;
            this.$emit('input', newSelected.map((user) => user.id || user.value));
        },
        onBlur() {
            this.$emit('blur');
        },
        loadCurrentUsers() {
            if ((Array.isArray(this.itemMetadatum.value) && this.itemMetadatum.value.length) || (!Array.isArray(this.itemMetadatum.value) && this.itemMetadatum.value)) {
                
                this.isLoading = true;
                let query = qs.stringify({ include: this.itemMetadatum.value });
                let endpoint = '/users/';

                wpAxios.get(endpoint + '?' + query)
                    .then((res) => {
                        for (let user of res.data) {
                            this.selected.push({
                                name: user.name,
                                value: user.id,
                                img:  user.avatar_urls && user.avatar_urls['24'] ? user.avatar_urls['24'] : ''
                            }) ;
                        }   
                        
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
    }
}
</script>

<style scoped>
    .help.counter {
        display: none;
    }
    div.is-flex {
        justify-content: flex-start;
    }
</style>