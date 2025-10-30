const pluginVue = require('eslint-plugin-vue');
const globals = require('globals');
const js = require('@eslint/js');

module.exports = [
    js.configs.recommended,
    ...pluginVue.configs['flat/recommended'],
    {
        languageOptions: {
            globals: {
                ...globals.browser,
                'wp': true,
                'tainacan_plugin': true,
                'tainacan_blocks': true,
                'tainacan_user': true,
                '_': true,
                'jQuery': true,
                'tainacan_extra_components': true,
                'tainacan_extra_plugins': true,
                'grecaptcha': true,
                'webkit': true
            }
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
            'vue/multiline-html-element-content-newline': 'off', // Should we? It's a stylistic decision.
            // These have impact on how some props that are passed and we have mixed types, such as collectionId as a string or number... would require careful refactoring.
            'vue/require-prop-type-constructor': 'off',
            'vue/require-default-prop': 'off'
        }
    }
];