<template>
    <div class="columns is-fullheight">
        <div class="page-container repository-level-page">
            <div class="card">
                <div class="card-content">
                    <p class="title">
                        {{ taxonomy.name }}
                    </p>
                    <p class="subtitle">
                        {{ taxonomy.description }}
                    </p>
                </div>
                <footer class="card-footer">
                    <router-link
                            class="card-footer-item"
                            :to="{ path: $routerHelper.getTaxonomyEditPath(taxonomyId)}">
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
        name: 'TaxonomyPage',
        data(){
            return {
                taxonomyId: Number,
            }
        },
        methods: {
            ...mapActions('taxonomy', [
                'fetchTaxonomy'
            ]),
            ...mapGetters('taxonomy', [
                'getTaxonomy'
            ])
        },
        computed: {
          taxonomy(){
              return this.getTaxonomy();
          }
        },
        created(){
            this.taxonomyId = parseInt(this.$route.params.taxonomyId);

            this.fetchTaxonomy(this.taxonomyId);
        }

    }
</script>

<style scoped>

</style>