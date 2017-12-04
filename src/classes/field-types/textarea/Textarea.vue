<template>
    <div class="component">
        <p>{{ name }}</p>
        <textarea cols="30" rows="10"></textarea>
    </div>
</template>

<script>
    export default {
        props: {
            name: { type: String }
        }
    }
</script>

<style scoped>
    textarea {
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
    textarea:focus {
        outline: none;
    }
</style>