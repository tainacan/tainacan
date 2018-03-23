module.exports = {
  extends: [
    // add more generic rulesets here, such as:
    'eslint:recommended',
    'plugin:vue/essential'
  ],
  rules: {
    // override/add rules settings here, such as:
    'vue/no-unused-vars': 'error',
    "no-console": "warn",
    "no-unused-vars": "warn",
    "no-undef": "warn",
    "vue/no-side-effects-in-computed-properties": "warn",
    "vue/return-in-computed-property": "warn"
  }
}