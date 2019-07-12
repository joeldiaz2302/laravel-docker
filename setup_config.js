require('dotenv').config();
var fs = require('fs');


module.exports.setupEnvFile = function(){
	let env = `${process.env.INSTALL_PROJ}/.env`;
	let example = `${process.env.INSTALL_PROJ}/.env.example`;
	let password = process.env.USER_PASSWORD;

	let parseContents = function(file, contents){
		let list = contents.split('\n');
		let hasEncoding = false;
		let connIndex = -1;
		list.forEach((item, index) => {
			let parts = item.split("=");
			let override = null;

			switch(parts[0]){
				case "DB_CONNECTION":
					connIndex = index + 1;
					override = "DB_CONNECTION=mysql";
					break;
				case "DB_CHARSET":
					hasEncoding = true;
					override = "DB_CHARSET=utf8";
					break;
				case "DB_HOST":
					override = "DB_HOST=db";
					break;
				case "DB_PORT":
					override = "DB_PORT=3306";
					break;
				case "DB_DATABASE":
					override = "DB_DATABASE=laravel";
					break;
				case "DB_USERNAME":
					override = "DB_USERNAME=laraveluser";
					break;
				case "DB_PASSWORD":
					override = `DB_PASSWORD=${password}`;
					break;
			}
			if(override){
				list[index] = override;
			}
		});
		if(!hasEncoding){
			list.splice(connIndex, 0, "DB_CHARSET=utf8");
		}
		console.log(`Writing to ${file}`);
		fs.writeFile(file, list.join('\n'), function (err) {
		  if (err) throw err;
		  console.log('Saved!');
		});
	}
	fs.readFile(env, 'utf8', function(err, contents){
		console.log("error", err, typeof err);
		if(err) {
			fs.readFile(`${process.env.INSTALL_PROJ}/.env.example`, 'utf8', function(err2, contents2){
				console.log("err2", err2);
				if(err2) {
					throw err2;
				}else{
					parseContents(env, contents2);
				}
			});
		}else{
			parseContents(env, contents);
		}
	});
}
//First read the .env from the project