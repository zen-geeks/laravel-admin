const fs = require('fs');
const path = require('path');

const dir = __dirname;

const jsFiles = [
    "AdminLTE/plugins/popper/popper.min.js",
    "AdminLTE/plugins/bootstrap5/bootstrap.min.js",
    // "AdminLTE/plugins/bootstrap/js/bootstrap.bundle.min.js",
    "AdminLTE/dist/js/adminlte.min.js",
    "jquery-pjax/jquery.pjax.js",
    "nprogress/nprogress.js",
    "nestable/jquery.nestable.js",
    "AdminLTE/plugins/toastr/toastr.min.js",
    "AdminLTE/plugins/sweetalert2/sweetalert2.min.js",
    "laravel-admin/laravel-admin.js",
    // "AdminLTE/plugins/bootstrap-colorpicker/js/bootstrap-colorpicker.min.js",
    "AdminLTE/plugins/inputmask/jquery.inputmask.js",
    "AdminLTE/plugins/moment/moment.min.js",
    "AdminLTE/plugins/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.min.js",
    "bootstrap-fileinput/js/plugins/filetype.min.js",
    "bootstrap-fileinput/js/plugins/piexif.min.js",
    "bootstrap-fileinput/js/plugins/buffer.min.js",
    "bootstrap-fileinput/js/fileinput.min.js",
    "bootstrap-fileinput/themes/fa5/theme.js",
    "AdminLTE/plugins/select2/js/select2.full.min.js",
    "number-input/bootstrap-number-input.js",
    // "AdminLTE/plugins/ion-rangeslider/js/ion.rangeSlider.min.js",
    "AdminLTE/plugins/bootstrap-switch/js/bootstrap-switch.min.js",
    "fontawesome-iconpicker/dist/js/fontawesome-iconpicker.min.js",
    "bootstrap-fileinput/js/plugins/sortable.min.js",
    "AdminLTE/plugins/bootstrap4-duallistbox/jquery.bootstrap-duallistbox.min.js",
];

const cssFiles = [
    "AdminLTE/plugins/icheck-bootstrap/icheck-bootstrap.min.css",
    // "AdminLTE/plugins/bootstrap-colorpicker/css/bootstrap-colorpicker.min.css",
    "AdminLTE/plugins/tempusdominus-bootstrap-4/css/tempusdominus-bootstrap-4.min.css",
    "bootstrap-fileinput/css/fileinput.min.css",
    "AdminLTE/plugins/select2/css/select2.min.css",
    // "AdminLTE/plugins/ion-rangeslider/css/ion.rangeSlider.min.css",
    "AdminLTE/plugins/bootstrap-switch/css/bootstrap3/bootstrap-switch.min.css",
    "fontawesome-iconpicker/dist/css/fontawesome-iconpicker.min.css",
    "AdminLTE/plugins/bootstrap4-duallistbox/bootstrap-duallistbox.min.css",
    "AdminLTE/plugins/icheck-bootstrap/icheck-bootstrap.css",
    "AdminLTE/plugins/fontawesome-free/css/all.min.css",
    "AdminLTE/plugins/fontawesome-free/css/brands.min.css",
    "nprogress/nprogress.css",
    "AdminLTE/plugins/sweetalert2-theme-bootstrap-4/bootstrap-4.min.css",
    "nestable/nestable.css",
    "AdminLTE/plugins/toastr/toastr.min.css",
    "google-fonts/fonts.css",
    "AdminLTE/dist/css/adminlte.min.css",
    "laravel-admin/laravel-admin.css",
    "laravel-admin/skin-blue-light.css",
];

function concat(files, output) {
    const content = files
        .map(file => {
            const filePath = path.join(dir, file);

            if (!fs.existsSync(filePath)) {
                throw new Error(`File not found: ${filePath}`);
            }

            return fs.readFileSync(filePath, 'utf8');
        })
        .join('\n');

    fs.writeFileSync(path.join(dir, output), content);

    console.log(`Generated: ${output}`);
}

function rewriteCssUrls(content, cssFile) {
    const cssDir = path.dirname(cssFile);

    return content.replace(
        /url\(\s*(['"]?)([^'")]+)\1\s*\)/g,
        (match, quote, url) => {
            if (
                url.startsWith('data:') ||
                url.startsWith('http://') ||
                url.startsWith('https://') ||
                url.startsWith('//') ||
                url.startsWith('/')
            ) {
                return match;
            }

            const absolutePath = path.normalize(
                path.join(cssDir, url)
            );

            const publicPath = '/vendor/laravel-admin/' + absolutePath
                .replaceAll(path.sep, '/');

            return `url(${quote}${publicPath}${quote})`;
        }
    );
}

function concatCss(files, output) {
    const content = files
        .map(file => {
            const filePath = path.join(dir, file);

            if (!fs.existsSync(filePath)) {
                throw new Error(`File not found: ${filePath}`);
            }

            const source = fs.readFileSync(filePath, 'utf8');

            return rewriteCssUrls(source, file);
        })
        .join('\n');

    fs.writeFileSync(path.join(dir, output), content);

    console.log(`Generated: ${output}`);
}

concat(jsFiles, 'app.min.js');
concatCss(cssFiles, 'app.min.css');
