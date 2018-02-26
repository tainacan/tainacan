<template>
    <div>
        <h1 class="is-size-3">{{ pageTitle }}  <b-tag v-if="collection != null && collection != undefined" :type="'is-' + getStatusColor(collection.status)" v-text="collection.status"></b-tag></h1>
        <form label-width="120px">
            <b-field label="Título">
                <b-input
                    id="tainacan-text-name"
                    v-model="form.name">
                </b-input>
            </b-field>
            <b-field label="Descrição">
                <b-input
                        id="tainacan-text-description"
                        type="textarea"
                        v-model="form.description"
                        >
                </b-input>
            </b-field>
            <b-field label="Status">
                <b-select
                        id="tainacan-select-status"
                        v-model="form.status"
                        placeholder="Selecione um status">
                    <option
                            v-for="statusOption in statusOptions"
                            :key="statusOption.value"
                            :value="statusOption.value"
                            :disabled="statusOption.disabled">{{ statusOption.label }}
                    </option>
                </b-select>
            </b-field>
            <b-field
                    label="Imagem">
                <b-upload v-model="form.files"
                          multiple
                          drag-drop>
                    <section class="section">
                        <div class="content has-text-centered">
                            <p>
                                <b-icon
                                        icon="upload"
                                        size="is-large">
                                </b-icon>
                            </p>
                            <p>Arraste uma imagem aqui <em>ou clique para enviar</em></p>
                        </div>
                    </section>
                </b-upload>
            </b-field>
            <button
                id="button-cancel-collection-creation"
                class="button"
                type="button"
                @click="cancelBack">Cancelar</button>
            <a
                id="button-submit-collection-creation"
                @click="onSubmit"
                class="button is-success is-hovered">Salvar</a>
        </form>

        <b-loading :active.sync="isLoading" :canCancel="false">
    </div>
</template>

<script>
import { mapActions, mapGetters } from 'vuex'

export default {
    name: 'CollectionEditionPage',
    data(){
        return {
            pageTitle: '',
            collectionId: Number,
            collection: null,
            isLoading: false,
            form: {
                name: '',
                status: '',
                description: '',
                files:[]
            },
            // Can be obtained from api later
            statusOptions: [{ 
                value: 'publish',
                label: 'Publicado'
                }, {
                value: 'draft',
                label: 'Rascunho'
                }, {
                value: 'private',
                label: 'Privado'
                }, {
                value: 'trash',
                label: 'Lixo'
            }]
        }
    },
    methods: {
        ...mapActions('collection', [
            'sendCollection',
            'updateCollection',
            'fetchCollection'
        ]),
        ...mapGetters('collection',[
            'getCollection'
        ]),
        onSubmit() {
            // Puts loading on Draft Collection creation
            this.isLoading = true;

            let data = {collection_id: this.collectionId, name: this.form.name, description: this.form.description, status: this.form.status};
            this.updateCollection(data).then(updatedCollection => {    
                
                this.collection = updatedCollection;

                // Fill this.form data with current data.
                this.form.name = this.collection.name;
                this.form.description = this.collection.description;
                this.form.status = this.collection.status;

                this.isLoading = false;

                this.$router.push('/collections/' + this.collectionId);
            });
        },
        getStatusColor(status) {
            switch(status) {
                case 'publish': 
                    return 'success'
                case 'draft':
                    return 'info'
                case 'private': 
                    return 'warning'
                case 'trash':
                    return 'danger'
                default:
                    return 'info'
            }
        },
        createNewCollection() {
            // Puts loading on Draft Collection creation
            this.isLoading = true;

            // Creates draft Collection
            let data = { name: '', description: '', status: 'auto-draft'};
            this.sendCollection(data).then(res => {

                this.collectionId = res.id;
                this.collection = res;

                // Fill this.form data with current data.
                this.form.name = this.collection.name;
                this.form.description = this.collection.description;
                this.form.status = this.collection.status;

                this.isLoading = false;
                
            })
            .catch(error => console.log(error));
        },
        cancelBack(){
            this.$router.push('/collections/' + this.collectionId);
        }
    },
    created(){

        if (this.$route.fullPath.split("/").pop() == "new") {
            this.pageTitle = this.$i18n.get('title_create_collection');
            this.createNewCollection();
        } else if (this.$route.fullPath.split("/").pop() == "edit") {

            this.pageTitle = this.$i18n.get('title_collection_edition');
            this.isLoading = true;

            // Obtains current Collection ID from URL
            this.pathArray = this.$route.fullPath.split("/").reverse(); 
            this.collectionId = this.pathArray[1];

            this.fetchCollection(this.collectionId).then(res => {
                this.collection = res;

                // Fill this.form data with current data.
                this.form.name = this.collection.name;
                this.form.description = this.collection.description;
                this.form.status = this.collection.status;

                this.isLoading = false; 
            });
        } 
    }

}
</script>

