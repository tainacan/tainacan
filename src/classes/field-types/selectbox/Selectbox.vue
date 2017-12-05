<template>
    <div class="component">
        <p>{{ name }}</p>
        <select class="form-control" v-model="selected">
            <option v-for="option in getOptions" :value="option">
                {{ option }}
            </option>
        </select>
    </div>
</template>

<script>
    export default {
        data(){
            return {
                selected:'Selecionar...'
            }
        },
        props: {
            name: {
                type: String
            },
            options: {
                type: String
            }
        },
        computed: {
            getOptions(){
                const values = this.options.split("\n");
                return values;
            }
        }
    }
</script>

<style scoped>
</style>