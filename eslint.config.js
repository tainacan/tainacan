const pluginVue = require('eslint-plugin-vue');
const globals = require('globals');
const js = require('@eslint/js');

module.exports = [
    {
        ignores: [
            'src/assets/**',
            'src/vendor/**',
            'node_modules/**',
            'tests/**'
        ]
    },
    js.configs.recommended,
    ...pluginVue.configs['flat/recommended'],
    {
        languageOptions: {
            ecmaVersion: 'latest',
            sourceType: 'module',
            parserOptions: {
                ecmaFeatures: {
                    jsx: true
                }
            },
            globals: {
                ...globals.browser,
                'React': true,
                'wp': true,
                'tainacan_plugin': true,
                'tainacan_blocks': true,
                'tainacan_user': true,
                'tainacan_commands': true,
                'tainacan_dashboard': true,
                '_': true,
                'jQuery': true,
                'tainacan_extra_components': true,
                'tainacan_extra_plugins': true,
                'grecaptcha': true,
                'webkit': true,
                '__webpack_public_path__': true,
            }
        },
        rules: {
            /* Override/add rules settings here, such as: */
            // Basic rules that we want to receive a warning instead of error.
            // console.log and console.info are traces. warn and error stay for failures worth keeping.
            'no-console': ['warn', { allow: ['warn', 'error'] }],
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
    },
    {
        // Vuex actions that ignore an unused store context still need the first argument (`{}`) so the payload stays the second argument.
        files: ['src/views/admin/js/store/**/actions.js'],
        rules: {
            'no-empty-pattern': 'off'
        }
    }
];