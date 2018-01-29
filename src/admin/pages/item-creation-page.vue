<template>
    <div>
        <h2>Item creation</h2>
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
                    v-for="item in statusOptions"
                    :key="item.value"
                    :label="item.label"
                    :value="item.value"
                    :disabled="item.disabled">
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
            <component  v-for="(metadata, index) in metadataList" v-bind:key="index" :label="metadata.metadata.name"
                        :is="extractFieldType(metadata.metadata.field_type)"
                        :name="metadata.metadata.name"
                        :item_id="metadata.item.id"
                        :metadata_id="metadata.metadata.id"
                        :value="metadata.value"></component>
            <el-form-item>
                <el-button type="primary" @click="onSubmit">Criar</el-button>
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
            'fetchMetadata',
            'sendMetadata'
        ]),
        ...mapGetters('item',[
            'getMetadata'
        ]),
        onSubmit() {
            let data = {item_id: this.itemId, title: this.form.title, description: this.form.description, status: this.form.status};
            this.updateItem(data);
        },
        extractFieldType(field_type) {
            let parts = field_type.split('\\');
            return 'tainacan-' + parts.pop().toLowerCase();
        }
    },
    computed: {
        metadataList(){
            return this.getMetadata();
        }
    },
    created(){
        // Obtains collection ID
        this.collectionId = this.$route.params.id;
        this.form.collectionId = this.collectionId;

        // Puts loading on Draft Item creation
        let loadingInstance = this.$loading({ text: 'Criando item rascunho...' });

        // Creates draft Item
        let data = {collection_id: this.form.collectionId, title: '', description: '', status: 'draft'};
        this.sendItem(data).then(res => {

            this.itemId = res.id;
            // Fill this.form data with current data.
            // TODO
            loadingInstance.text = 'Carregando metadados...';
            // Obtains Item Metadata
            this.fetchMetadata(this.itemId).then(res => {
                loadingInstance.close();
            });
            
        })
        .catch(error => console.log(error));
        
    }

}
</script>

<style scoped>

</style>


