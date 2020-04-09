<template>
    <b-autocomplete
            clearable
            :disabled="disabled"
            :value="currentUser && currentUser.name ? currentUser.name : null"
            :data="users"
            :placeholder="$i18n.get('instruction_type_search_users')"
            keep-first
            open-on-focus
            @input="loadUsers"
            @focus.once="($event) => loadUsers($event.target.value)"
            @select="onSelect"
            :loading="isFetchingUsers || isLoadingCurrentUser"
            field="name"
            icon="account"
            check-infinite-scroll
            @infinite-scroll="fetchMoreUsers">
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
        <template slot="empty">{{ $i18n.get('info_no_user_found') }}</template>
    </b-autocomplete>
</template>

<script>
import { mapActions, mapGetters } from 'vuex';
    
export default {
    props: {
        metadatum: Object,
        value: [String, Number, Array],
        disabled: false
    },
    data() {
        return {
            users: [],
            isLoadingCurrentUser: false,
            isFetchingUsers: false,
            userId: null,
            usersSearchQuery: '',
            usersSearchPage: 1,
            totalUsers: 0,
            currentUser: {}
        }
    },
    created() {
        this.loadCurrentUser();
    },
    methods: {
        onSelect(value) {
            this.$emit('input', value.id);
        },
        ...mapActions('activity', [
            'fetchUsers',
            'fetchUser'
        ]),
        loadCurrentUser() {
            this.isLoadingCurrentUser = true;
            this.fetchUser(this.value)
                .then((res) => {
                    this.currentUser = res.user;
                    this.isLoadingCurrentUser = false;
                })
                .catch(() => this.isLoadingCurrentUser = false );
        },
        loadUsers: _.debounce(function (search) {
            console.log(search)
            // String update
            if (search != this.usersSearchQuery) {
                this.usersSearchQuery = search;
                this.users = [];
                this.usersSearchPage = 1;
            } 
            
            // String cleared
            if (!search.length) {
                this.usersSearchQuery = search;
                this.users = [];
                this.usersSearchPage = 1;
            }

            // No need to load more
            if (this.usersSearchPage > 1 && this.users.length > this.totalUsers)
                return;

            this.isFetchingUsers = true;

            this.fetchUsers({ search: this.usersSearchQuery, page: this.usersSearchPage })
                .then((res) => {
                    if (res.users) {
                        for (let user of res.users)
                            this.users.push(user); 
                    }
                    
                    if (res.totalUsers)
                        this.totalUsers = res.totalUsers;

                    this.usersSearchPage++;
                    
                    this.isFetchingUsers = false;
                })
                .catch((error) => {
                    this.$console.error(error);
                    this.isFetchingPages = false;
                });
        }, 500),
        fetchMoreUsers: _.debounce(function () {
            this.loadUsers(this.usersSearchQuery)
        }, 250),
    }
}
</script>