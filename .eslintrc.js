module.exports = {
    extends: [
        // add more generic rulesets here, such as:
        'eslint:recommended',
        'plugin:vue/vue3-recommended'
    ],
    parserOptions: {
        'ecmaVersion': 2018
    },
    rules: {
        /* Override/add rules settings here, such as: */
        // Basic rules that we want to receive a warning instead of error
        'no-console': 'warn',
        'no-unused-vars': 'warn',
        'no-undef': 'warn',
        // Tainacan relies a lot in v-html and v-text, so we can't disable them
        'vue/no-v-html': 'off',
        'vue/no-v-text-v-html-on-component': 'off',
        // Formating that is hard to disable as would require significant refactoring. Autofix don't solve it well and it reflects stylistic decisions from the team.
        'vue/html-indent': [
            'warn', 4, { 
                'attribute': 2,
                'closeBracket': 1 
            }
        ],
        'vue/html-closing-bracket-newline': 'off',          
        'vue/multiline-html-element-content-newline': 'off',
        // This has impact on how some props are passed and we have mixed types, such as collectionID as a string or number... would require careful refactoring.
        'vue/require-prop-type-constructor': 'off',
        'vue/require-default-prop': 'off'
    },
    globals: {
        'wp': true,
        'tainacan_plugin': true,
        'tainacan_blocks': true,
        '_': true,
        'jQuery': true,
        'tainacan_extra_components': true,
        'tainacan_extra_plugins': true,
        'grecaptcha': true,
        'webkit': true
    }
}