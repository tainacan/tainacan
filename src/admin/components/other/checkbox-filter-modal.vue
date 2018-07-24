<template>
    <form action="">
        <div 
                class="tainacan-modal-content" 
                style="width: auto">
            <header class="tainacan-modal-title">
                <h2>{{ this.$i18n.get('filter') }} <em>{{ title }}</em></h2>
                <hr>
            </header>
            <section class="tainacan-form">


                <footer class="field is-grouped form-submit">
                    <div class="control">
                        <button 
                                class="button is-outlined" 
                                type="button" 
                                @click="$parent.close()">Close</button>
                    </div>
                    <div class="control">
                        <button class="button is-success">Confirm</button>
                    </div>
                </footer>
            </section>
        </div>
    </form>     
</template>

<script>
export default {
    name: 'CheckboxFilterModal',
    props: {
        title: ''
    }
}
</script>

<style lang="scss" scoped>

</style>


 
