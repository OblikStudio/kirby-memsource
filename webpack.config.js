const path = require("path");
const VueLoaderPlugin = require("vue-loader/lib/plugin");
const MiniCssExtractPlugin = require("mini-css-extract-plugin");

module.exports = {
	output: {
		filename: "index.js",
		path: __dirname,
	},
	resolve: {
		alias: {
			"@": path.join(__dirname, "src"),
		},
	},
	plugins: [
		new MiniCssExtractPlugin({
			filename: "index.css",
		}),
		new VueLoaderPlugin(),
	],
	module: {
		rules: [
			{
				test: /\.scss$/,
				use: [
					{
						loader: MiniCssExtractPlugin.loader,
					},
					"css-loader",
					"sass-loader",
				],
			},
			{
				test: /\.js$/,
				exclude: /node_modules/,
				use: {
					loader: "babel-loader",
					options: {
						presets: ["@babel/preset-env"],
					},
				},
			},
			{
				test: /\.vue$/,
				use: "vue-loader",
			},
		],
	},
};
