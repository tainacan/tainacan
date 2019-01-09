<template>
    <div class="repository-level-page page-container">
        <tainacan-title
                :bread-crumb-items="[{ path: '', label: $i18n.get('importers') }]" />

        <h3>{{ $i18n.get('label_available_importers') }}</h3>
        <p>{{ $i18n.get('instruction_select_an_importer_type') }}</p>
        <div class="importer-types-container">
            <div
                    class="importer-type"
                    v-for="importerType in availableImporters"
                    :key="importerType.slug"
                    @click="onSelectImporter(importerType)">
                <h4>{{ importerType.name }}</h4>
                <p>{{ importerType.description }}</p>            
            </div>

        </div>
        
        <b-loading 
                :active.sync="isLoading" 
                :can-cancel="false"/>
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
    methods: {
        ...mapActions('importer', [
            'fetchAvailableImporters'
        ]),
        onSelectImporter(importerType) {
            this.$router.push(this.$routerHelper.getImporterEditionPath(importerType.slug));
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
    }

}
</script>

<style lang="scss" scoped>

    @import "../../scss/_variables.scss";

    .importer-types-container {
        display: flex;
        flex-wrap: wrap;

        .importer-type {
            border: 1px solid $gray2;
            padding: 15px;
            margin: 20px;
            cursor: pointer;
            max-width: 20%;
            flex-grow: 1;
            flex-basis: 20%;
        }
    }

</style>


