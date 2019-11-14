module.exports = function(api) {
	api.cache(true);

	const presets = [
		'@babel/react',
		// '@babel/preset-typescript',
		'@babel/preset-env',
	];

	const plugins = [
		'@babel/plugin-proposal-class-properties',
		'@babel/proposal-object-rest-spread',
	];

	return {
		presets,
		plugins,
	};
};
