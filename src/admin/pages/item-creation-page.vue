<template>
    <div>
        <h2>Item creation</h2><el-tag v-if="item != null && item != undefined" :type="getStatusColor(item.status)" v-text="item.status"></el-tag>
        <el-form ref="form" :model="form" :rules="rules" label-width="120px">
            <el-form-item label="Título" prop="title">
                <el-input v-model="form.title"></el-input>
            </el-form-item>
            <el-form-item label="Descrição">
                <el-input type="textarea" v-model="form.description"></el-input>
            </el-form-item>
            <el-form-item label="Status">
                <el-select v-model="form.status" placeholder="Selecione um status">
                    <el-option
                    v-for="statusOption in statusOptions"
                    :key="statusOption.value"
                    :label="statusOption.label"
                    :value="statusOption.value"
                    :disabled="statusOption.disabled">
                    </el-option>
                </el-select>
            </el-form-item>
            <el-form-item label="Imagem">
                <el-upload
                    class="upload-demo"
                    drag
                    action="https://jsonplaceholder.typicode.com/posts/"
                    :on-preview="handlePreview"
                    :on-remove="handleRemove">
                    <i class="el-icon-upload"></i>
                    <div class="el-upload__text">Arraste uma imagem aqui <em>ou clique para enviar</em></div>
                    <div class="el-upload__tip" slot="tip">imagens em formato jpg/png</div>
                </el-upload>
            </el-form-item>
            <tainacan-form-item v-for="(field, index) in fieldList" v-bind:key="index" :field="field"></tainacan-form-item>
            <el-form-item>
                <el-button type="primary" @click="onSubmit">Salvar</el-button>
                <el-button>Cancelar</el-button>
            </el-form-item>
        </el-form>
    </div>
</template>

<script>
import { mapActions, mapGetters } from 'vuex'

export default {
    name: 'ItemCreationPage',
    data(){
        return {
            itemId: Number,
            item: null,
            collectionId: Number,
            form: {
                collectionId: Number,
                title: '',
                status: '',
                description: ''
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
            }],
            rules: {
                title: [
                    { required: true, message: 'Please input Activity name', trigger: 'blur' }
                ],
            }
        }
    },
    methods: {
        ...mapActions('item', [
            'sendItem',
            'updateItem',
            'fetchFields',
            'sendField',
            'fetchItem'
        ]),
        ...mapGetters('item',[
            'getFields',
            'getItem'
        ]),
        onSubmit() {
            // Puts loading on Draft Item creation
            let loadingInstance = this.$loading({ text: 'Salvando item ...' });

            let data = {item_id: this.itemId, title: this.form.title, description: this.form.description, status: this.form.status};
            this.updateItem(data).then(updatedItem => {    
                
                this.item = updatedItem;

                // Fill this.form data with current data.
                this.form.title = this.item.title;
                this.form.description = this.item.description;
                this.form.status = this.item.status;

                loadingInstance.close();
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
            let loadingInstance = this.$loading({ text: 'Criando item rascunho...' });

            // Creates draft Item
            let data = {collection_id: this.form.collectionId, title: '', description: '', status: 'draft'};
            this.sendItem(data).then(res => {

                this.itemId = res.id;
                this.item = res;

                // Fill this.form data with current data.
                this.form.title = this.item.title;
                this.form.description = this.item.description;
                this.form.status = this.item.status;

                this.loadMetadata(loadingInstance);
                
            })
            .catch(error => console.log(error));
        },
        loadExistingItem() {
            // Puts loading on Item Loading
            let loadingInstance = this.$loading({ text: 'Atualizando item...' });

            this.loadMetadata(loadingInstance);    
        
        },
        loadMetadata(loadingInstance) {
            loadingInstance = this.$loading({ text: 'Carregando metadados...'});
            // Obtains Item Field
            this.fetchFields(this.itemId).then(res => {
               loadingInstance.close();
            });
        }
    },
    computed: {
        fieldList(){
            return this.getFields();
        }
    },
    created(){
        // Obtains collection ID
        this.collectionId = this.$route.params.id;
        this.form.collectionId = this.collectionId;

        if (this.$route.fullPath.split("/").pop() == "new") {
            console.log("CRIANDO ITEM");
            this.createNewItem();
        } else if (this.$route.fullPath.split("/").pop() == "edit") {

            let loadingInstance = this.$loading({ text: 'Carregando item...'});

            this.pathArray = this.$route.fullPath.split("/").reverse(); 
            this.itemId = this.pathArray[1];

            this.fetchItem(this.itemId).then(res => {
                this.item = res;
                
                // Fill this.form data with current data.
                this.form.title = this.item.title;
                this.form.description = this.item.description;
                this.form.status = this.item.status;
                
                loadingInstance.close();
            });
        }
        
        
    }

}
</script>

<style scoped>

</style>


