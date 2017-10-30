const elixir = require('laravel-elixir');
const path = require('path');
const fs = require('fs');
const glob = require('glob');

/*
 |--------------------------------------------------------------------------
 | Elixir Asset Management
 |--------------------------------------------------------------------------
 */
 
const Bundle = function(packages, dir) {
    this.pks = {};
    this.js = [];
    this.css = [];
    this.others = {};
    this.dir = dir || 'resources/assets';
    this.add(this.arr(packages));
}
Bundle.prototype.add = function(packages) {
    var self = this;
    packages.forEach(function(pk) {
        if (self.pks[pk] == undefined) {
            try {
                var base = path.join(self.dir, pk);
                var package = JSON.parse(fs.readFileSync(path.join(base, 'package.json'), 'utf-8'));
                self.pks[pk] = package;
                self.add(self.arr(package.peerDependencies || package.dependencies));
                self.glob(package.files || '*/**/*.*', base).forEach(function(file) {
                    var ext = path.extname(file).toLowerCase();
                    if ('.js' == ext) {
                        self.js.push(path.join(pk, file));
                    } else if ('.css' == ext) {
                        self.css.push(path.join(pk, file));
                    } else {
                        self.others[path.join(base, file)] = file;
                    }
                });
            } catch (e) {
                console.log('Error: broken package "' + pk + '"');
            }
        }
    });
}
Bundle.prototype.arr = function(data) {
    if (typeof data == 'string') {
        return [data];
    } else if (Array.isArray(data)) {
        return data;
    } else if (typeof data == 'object') {
        return Object.keys(data);
    } else {
        return [];
    }
}
Bundle.prototype.glob = function(patterns, base) {
    var files = [];
    this.arr(patterns).forEach(function(pattern) {
        glob.sync(pattern, {
            'cwd': base
        }).forEach(function(file) {
            files.push(file);
        });
    });
    return files;
}

elixir(function(mix) {
    var assets = 'resources/assets';
    var b = new Bundle('main', assets);
    mix.scripts(b.js, 'public/rc/js/scripts.js', assets);
    mix.styles(b.css, 'public/rc/css/styles.css', assets);
    for (var file in b.others) {
        mix.copy(file, path.join('public/rc', b.others[file]));
    }
});