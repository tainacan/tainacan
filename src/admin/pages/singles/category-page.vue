<template>
    <div class="columns is-fullheight">
        <div class="page-container primary-page">
            <div class="card">
                <div class="card-content">
                    <p class="title">
                        {{ category.name }}
                    </p>
                    <p class="subtitle">
                        {{ category.description }}
                    </p>
                </div>
                <footer class="card-footer">
                    <router-link
                            class="card-footer-item"
                            :to="{ path: $routerHelper.getCategoryEditPath(categoryId)}">
                        {{ $i18n.getFrom('taxonomies','edit_item') }}
                    </router-link>
                    <a class="card-footer-item">
                        Edit terms
                    </a>
                </footer>
            </div>
        </div>
    </div>
</template>

<script>
    import { mapActions, mapGetters } from 'vuex';

    export default {
        name: 'CategoryPage',
        data(){
            return {
                categoryId: Number,
            }
        },
        methods: {
            ...mapActions('category', [
                'fetchCategory'
            ]),
            ...mapGetters('category', [
                'getCategory'
            ])
        },
        computed: {
          category(){
              return this.getCategory();
          }
        },
        created(){
            this.categoryId = parseInt(this.$route.params.categoryId);

            this.fetchCategory(this.categoryId);
        }

    }
</script>

<style scoped>

</style>