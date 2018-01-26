<template>
    <div>
        <h2>Item creation</h2>
        <el-form ref="form" :model="form" label-width="120px">
            <el-form-item label="Título">
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
            <el-form-item>
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
            collectionId: Number,
            form: {
                collectionId: Number,
                title: '',
                status: '',
                description: ''
            },
            // can be obtained from api later
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
            'sendItem'
        ]),
        onSubmit() {

            let data = {collection_id: this.form.collectionId, title: this.form.title, description: this.form.description, status: this.form.status};
            this.sendItem(data);
        }
    },
    computed: {
    },
    created(){
        this.collectionId = this.$route.params.id;
        this.form.collectionId = this.collectionId;
    }

}
</script>

<style scoped>

</style>


