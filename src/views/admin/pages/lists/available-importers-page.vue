<template>
    <div class="repository-level-page page-container">
        <tainacan-title />

        <h3>{{ $i18n.get('label_available_importers') }}</h3>
        <p>{{ $i18n.get('instruction_select_an_importer_type') }}</p>
        <div class="importer-types-container">
            <div
                    v-for="importerType in availableImporters"
                    :key="importerType.slug"
                    class="importer-type"
                    @click="onSelectImporter(importerType)">
                <h4>{{ importerType.name }}</h4>
                <p>{{ importerType.description }}</p>            
            </div>

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
            availableImporters: [],
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
        ]),
        onSelectImporter(importerType) {
            this.$router.push(this.$routerHelper.getImporterEditionPath(importerType.slug));
        }
    }
}
</script>

<style lang="scss" scoped>

    .importer-types-container {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        grid-gap: 1rem;
        padding: 1.125em 0.75rem;

        .importer-type {
            border: 1px solid var(--tainacan-gray2);
            padding: 15px;
            cursor: pointer;
            border-radius: 4px;
            transition: border 0.3s ease;

            &:hover {
                border: 1px solid var(--tainacan-gray3);
            }
        }
    }

</style>


