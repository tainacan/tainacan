<template>
    <div class="tainacan-repository-level-colors page-container">
        <tainacan-title :is-sticky="true" />

        <h2>{{ $i18n.get('label_available_importers') }}</h2>
        <p>{{ $i18n.get('instruction_select_an_importer_type') }}</p>
        <div 
                :role="Object.keys(availableImporters).length > 1 ? 'list' : undefined"
                class="importer-types-container tainacan-clickable-cards">
            <router-link
                    v-for="importerType in availableImporters"
                    :key="importerType.slug"
                    class="importer-type tainacan-clickable-card"
                    :role="Object.keys(availableImporters).length > 1 ? 'listitem' : undefined"
                    :to="$routerHelper.getImporterEditionPath(importerType.slug)">
                <dl class="importer-type-definition">
                    <dt class="importer-type-name">
                        {{ importerType.name }}
                    </dt>
                    <dd class="importer-type-description">
                        {{ importerType.description }}
                    </dd>
                </dl>
            </router-link>

        </div>
        
        <b-loading 
                v-model="isLoading" 
                :can-cancel="false" />
    </div>
</template>

<script>
import { mapActions } from 'vuex';

export default {
    name: 'ImporterEditionForm',
    data(){
        return {
            availableImporters: {},
            isLoading: false
        }
    },
    created() {
        this.isLoading = true;
        this.fetchAvailableImporters()
        .then((res) => {
            this.availableImporters = res;
            this.isLoading = false;
        }).catch((error) => {
            this.$console.log(error);
            this.isLoading = false;
        });
    },
    methods: {
        ...mapActions('importer', [
            'fetchAvailableImporters'
        ])
    }
}
</script>

<style lang="scss" scoped>

    @use '../../scss/_cards.scss';

</style>
