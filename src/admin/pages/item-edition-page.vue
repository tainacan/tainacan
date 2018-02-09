<template>
    <div>
        <h1 class="is-size-3">Item creation  <b-tag v-if="item != null && item != undefined" :type="'is-' + getStatusColor(item.status)" v-text="item.status"></b-tag></h1>
        <form label-width="120px">
            <b-field label="Título">
                <b-input
                    v-model="form.title"
                     >
                </b-input>
            </b-field>
            <b-field label="Descrição">
                <b-input
                        type="textarea"
                        v-model="form.description"
                        >
                </b-input>
            </b-field>
            <b-field label="Status">
                <b-select
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
            <tainacan-form-item
                    v-for="(field, index) in fieldList"
                    v-bind:key="index"
                    :field="field"></tainacan-form-item>
            <button
                class="button"
                type="button"
                @click="cancelBack">Cancelar</button>
            <a
                @click="onSubmit"
                class="button is-success is-hovered">Salvar</a>
        </form>

        <b-loading :active.sync="isLoading" :canCancel="false">
    </div>
</template>

<script>
import { mapActions, mapGetters } from 'vuex'

export default {
    name: 'ItemEditionPage',
    data(){
        return {
            itemId: Number,
            item: null,
            collectionId: Number,
            isLoading: false,
            form: {
                collectionId: Number,
                title: '',
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
        ...mapActions('item', [
            'sendItem',
            'updateItem',
            'fetchFields',
            'sendField',
            'fetchItem',
            'cleanFields'
        ]),
        ...mapGetters('item',[
            'getFields',
            'getItem'
        ]),
        onSubmit() {
            
            // Puts loading on Item edition
            this.isLoading = true;

            let data = {item_id: this.itemId, title: this.form.title, description: this.form.description, status: this.form.status};
            
            this.updateItem(data).then(updatedItem => {    
                
                this.item = updatedItem;

                // Fill this.form data with current data.
                this.form.title = this.item.title;
                this.form.description = this.item.description;
                this.form.status = this.item.status;

                this.isLoading = false;

                this.$router.push('/collections/' + this.form.collectionId + '/items/' + this.itemId);
            }).catch(error => {
                console.log(error);

                this.isLoading = false;
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
        createNewItem() {
            // Puts loading on Draft Item creation
            this.isLoading = true;

            // Creates draft Item
            let data = {collection_id: this.form.collectionId, title: '', description: '', status: 'auto-draft'}; 
            this.sendItem(data).then(res => {

                this.itemId = res.id;
                this.item = res;

                // Fill this.form data with current data.
                this.form.title = this.item.title;
                this.form.description = this.item.description;
                this.form.status = this.item.status;

                this.loadMetadata();
                
            })
            .catch(error => console.log(error));
        },
        loadMetadata() {
            // Obtains Item Field
            this.fetchFields(this.itemId).then(res => {
                this.isLoading = false;
            });
        },
        cancelBack(){
            this.$router.push('/collections/' + this.collectionId);
        }
    },
    computed: {
        fieldList(){
            return this.getFields();
        }   
    },
    created(){
        // Obtains collection ID
        this.cleanFields();
        this.collectionId = ( this.$route.params.collection_id ) ? this.$route.params.collection_id : this.$route.params.id;
        this.form.collectionId = this.collectionId;

        if (this.$route.fullPath.split("/").pop() == "new") {
            this.createNewItem();
        } else if (this.$route.fullPath.split("/").pop() == "edit") {

            this.isLoading = true;

            // Obtains current Item ID from URL
            this.pathArray = this.$route.fullPath.split("/").reverse(); 
            this.itemId = this.pathArray[1];

            this.fetchItem(this.itemId).then(res => {
                this.item = res;
                
                // Fill this.form data with current data.
                this.form.title = this.item.title;
                this.form.description = this.item.description;
                this.form.status = this.item.status;

                this.loadMetadata();
            });
        }
        
        
    }

}
</script>

<style scoped>

</style>


