const path = require( "path" );

module.exports = {
	entry: "./index.js",
	output: {
		filename: "muzaara-woopf-bing.js",
		path: path.resolve(__dirname,  "../asset/js" )
	},
    mode : "development",
    module : {
        rules : [
            {
                test: /\.js$/,
                exclude: /node_modules/,
                loader: "babel-loader",
                options: {
                    presets: [ "@babel/preset-env", "@babel/preset-react" ]
                }
            }
        ]
    }
}
