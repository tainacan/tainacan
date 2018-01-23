<template>
    <div>
        <el-row>
            <el-col :span="8" v-for="(item, index) in itens" :key="item.id" :offset="index > 0 ? 2 : 0">
                <el-card :body-style="{ padding: '0px' }">
                    <img src="" class="image" :alt="item.name">
                    <div style="padding: 14px;">
                        <span>{{ item.name }}</span>
                    </div>
                </el-card>
            </el-col>
        </el-row>
    </div>
</template>

<script>
import { mapActions, mapGetters } from 'vuex'

export default {
    name: 'ItensList',
    data(){
        return {
        }
    },
    methods: {
        ...mapActions('item', [
            'fetchItens'
        ]),
        ...mapGetters('item', [
            'getItens'
        ])
    },
    computed: {
        collections(){
            return this.getItens();
        }
    },
    created(){
        this.fetchItens();
    }

}
</script>

<style scoped>

    .time {
        font-size: 13px;
        color: #999;
    }

    .bottom {
        margin-top: 13px;
        line-height: 12px;
    }

    .button {
        padding: 0;
        float: right;
    }

    .image {
        width: 100%;
        display: block;
    }

    .clearfix:before,
    .clearfix:after {
        display: table;
        content: "";
    }

    .clearfix:after {
        clear: both
    }
</style>


