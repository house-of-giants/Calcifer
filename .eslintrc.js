module.exports = {
	parser: '@babel/eslint-parser',
	env: {
		browser: true,
		es6: true,
	},
	plugins: ['react', 'prettier'],
	extends: ['eslint:recommended', 'plugin:react/recommended', 'prettier'],
	settings: {
		'import/resolver': {
			node: {
				extensions: ['.js', '.jsx', '.ts', '.tsx'],
			},
		},
		'import/extensions': ['.js', '.mjs', '.jsx', '.ts', '.tsx'],
		react: {
			version: 'detect',
		},
	},
	rules: {
		'class-methods-use-this': 0,
		'no-restricted-syntax': [
			'error',
			{
				selector: 'ForInStatement',
				message:
					'for..in loops iterate over the entire prototype chain, which is virtually never what you want. Use Object.{keys,values,entries}, and iterate over the resulting array.',
			},
			{
				selector: 'LabeledStatement',
				message:
					'Labels are a form of GOTO; using them makes code confusing and hard to maintain and understand.',
			},
			{
				selector: 'WithStatement',
				message:
					'`with` is disallowed in strict mode because it makes code impossible to predict and optimize.',
			},
		],
		'prefer-destructuring': [2, { array: false, object: true }],
		'react/jsx-uses-react': 0,
		'react/react-in-jsx-scope': 0,
		'react/forbid-prop-types': [
			'error',
			{
				forbid: ['any'],
				checkContextTypes: true,
				checkChildContextTypes: true,
			},
		],
	},
	parserOptions: {
		ecmaFeatures: {
			jsx: true,
		},
		requireConfigFile: false,
		babelOptions: {
			presets: ['@babel/preset-react'],
		},
	},
};
