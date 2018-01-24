<template>
    <div>
        <h2>Item creation</h2>
        <el-form ref="form" :model="form" label-width="120px">
            <el-form-item label="Nome">
                <el-input v-model="form.name"></el-input>
            </el-form-item>
            <el-form-item label="Descrição">
                <el-input type="textarea" v-model="form.desc"></el-input>
            </el-form-item>
            <el-form-item label="Status">
                <el-select v-model="form.region" placeholder="Selecione um status">
                    <el-option label="Públicado" value="publish"></el-option>
                    <el-option label="Rascunho" value="draft"></el-option>
                    <el-option label="Privado" value="private"></el-option>
                    <el-option label="Lixo" value="trash"></el-option>
                </el-select>
            </el-form-item>
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
            <el-form-item>
                <el-button type="primary" @click="onSubmit">Criar</el-button>
                <el-button>Cancelar</el-button>
            </el-form-item>
        </el-form>
        <i class="el-icon-edit"></i>
    </div>
</template>

<script>
import { mapActions, mapGetters } from 'vuex'

export default {
    name: 'ItemCreationPage',
    data(){
        return {
            form: {
                name: '',
                status: '',
                desc: ''
            }
        }
    },
    components: {
    },
    methods: {
        ...mapActions('item', [
            'fetchItens'
        ]),
        ...mapGetters('item', [
            'getItens'
        ]),
        onSubmit() {
        }
    },
    computed: {
    },
    created(){
    }

}
</script>

<style scoped>

</style>


