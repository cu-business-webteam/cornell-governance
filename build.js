const fs = require('fs');

function deleteAllGitFiles(path) {
    if (fs.existsSync(path)) {
        if (fs.lstatSync(path).isDirectory()) {
            fs.readdirSync(path).forEach(function (file) {
                let curPath = path + '/' + file;

                if (curPath.includes('.git')) {
                    if (fs.lstatSync(curPath).isDirectory()) {
                        console.log(`Recursively removing "${curPath}" directory...`);
                        fs.rmdirSync(curPath, {recursive: true});
                    } else if (fs.lstatSync(curPath).isFile()) {
                        console.log(`Removing file "${curPath}"...`);
                        fs.rmSync(curPath);
                    }
                } else if (fs.lstatSync(curPath).isDirectory()) {
                    deleteAllGitFiles(curPath);
                }
            });
        } else if (fs.lstatSync(path).isFile()) {
            if (path.includes('.git')) {
                console.log(`Removing file "${path}"...`);
                fs.rmSync(path);
            }
        }
    }
}

console.log("Cleaning working tree...");

deleteAllGitFiles("./vendor");

console.log("Successfully cleaned working tree!");