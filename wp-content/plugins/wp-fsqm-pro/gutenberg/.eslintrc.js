const path = require('path');
module.exports = {
	extends: '@wpquark',
	globals: {
		eFormGTB: false,
		jQuery: false,
	},
	rules: {
		'react/react-in-jsx-scope': 'off',
		'camelcase': 'off',
		'react/prop-types': 'off',
	},
	settings: {
		'import/resolver': {
			webpack: {
				config: {
					resolve: {
						extensions: ['.js', '.jsx'],
						alias: {
							Blocks: path.join(__dirname, 'src/Blocks'),
							Components: path.join(__dirname, 'src/Components'),
							Utils: path.join(__dirname, 'src/Utils'),
							Edits: path.join(__dirname, 'src/Edits'),
						},
					},
				},
			},
		},
	},
};
