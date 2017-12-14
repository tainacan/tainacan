<template>
    <div class="component">
        <p>{{ name }}</p>
        <input type="number" @blur="changeValue($event)">
    </div>
</template>

<script>
    export default {
        props: {
            name: { type: String },
            item_id: { type: Number },
            metadata_id: { type: Number },
            value: { type: [ String,Number ]  },
        },
        methods: {
            changeValue( event ){}
        }

    }
</script>

<style scoped>
    input[type="text"] {
        display: block;
        margin: 0;
        width: 100%;
        border-radius: 6px;
        font-family: sans-serif;
        font-size: 18px;
        appearance: none;
        box-shadow: none;
        color:green;
    }
    input[type="text"]:focus {
        outline: none;
    }
</style>