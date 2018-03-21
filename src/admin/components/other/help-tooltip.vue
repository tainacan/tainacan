<template>
    <div class="help-tooltip">
        <div class="help-tooltip-header">
            <h5>{{ title }}</h5>
        </div>
        <div class="help-tooltip-body">
            <p>{{ message }}</p>
        </div>
    </div>
</template>

<script>
export default {
    name: 'HelpTooltip',
    props: {
        title: '',
        message: ''
    }
}
</script>

<style lang="scss">
    .help-tooltip {
        color: #000;
        background-color: #e8f9f5;
        border: 1px solid #338591;
        border-radius: 10px;
        margin: 0;
        position: absolute;
        z-index: 99999999999999;
        top: 0%;
        display: block;
        
        .help-tooltip-header {
            padding: 0.8em 1.2em 0.6em 1.2em;
            border-bottom: 1px solid #909293;
            font-size: 18px;
            font-weight: bold;
        }

        .help-tooltip-body {
            padding: 1.2em;
            font-size: 11px;
        }

        &:after,
        &:before {
            content: "";
            display: block;
            position: absolute;
            left: 10%;
            width: 0;
            height: 0;
            border-style: solid;
        }
        &:after {
            border-color:  #e8f9f5 transparent transparent transparent;
            border-right-width: 20px;
            border-top-width: 16px;
            border-left-width: 20px;
        }
        &:before {
            border-color: #338591 transparent transparent transparent;
            border-right-width: 20px;
            border-top-width: 16px;
            border-left-width: 20px;
            bottom: -20px;
        }
    }
</style>

