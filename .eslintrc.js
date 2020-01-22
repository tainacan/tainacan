module.exports = {
    extends: [
        // add more generic rulesets here, such as:
        'eslint:recommended',
        'plugin:vue/recommended'
    ],
    parserOptions: {
        'ecmaVersion': 2017
    },
    rules: {
        // override/add rules settings here, such as:
        'vue/no-unused-vars': 'error',
        'no-console': 'warn',
        'no-unused-vars': 'warn',
        'no-undef': 'warn',
        'vue/html-indent': 'off',
        'vue/attributes-order': 'off',
        'vue/no-confusing-v-for-v-if': 'off',
        'vue/require-default-prop': 'off', // https://github.com/vuejs/eslint-plugin-vue/blob/master/docs/rules/require-default-prop.md
    },
    globals: {
        'tainacan_plugin': true,
        '_': true,
        'jQuery': true
    }
}