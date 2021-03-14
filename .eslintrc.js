module.exports = {
  root: true,
  parser: '@typescript-eslint/parser',
  plugins: ['@typescript-eslint', 'jest'],
  extends: ['eslint:recommended', 'plugin:node/recommended', 'plugin:jest/recommended'],
  rules: {
    'node/no-missing-import': 'off',
    'node/no-missing-require': 'off',
  },

  overrides: [
    {
      files: ['*.ts'],
      extends: ['plugin:@typescript-eslint/recommended'],
      rules: {
        '@typescript-eslint/explicit-member-accessibility': 'error',
        '@typescript-eslint/no-extra-semi': 'off',
        'node/no-unsupported-features/es-syntax': 'off',
      },
    },
  ],
}
