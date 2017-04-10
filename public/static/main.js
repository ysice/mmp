/** Wefee */
require.config({
    baseUrl: "/static/js/",
    paths: {
        "jquery": "jquery.min",
        "bootstrap": "../bootstrap/js/bootstrap.min",
        "particleground": "particleground/jquery.particleground.min",
        "smvalidator": "SMValidator.min",
        "icheck": "icheck/icheck.min",
        "layer": "layer/layer",
        "bootstrapswitch": "bootstrap-switch/bootstrap-switch.min",
        "webuploader": "webuploader/webuploader.min",
        "flatpickr": "flatpickr/flatpickr.min",
        'flatpickrzh': "flatpickr/l10n/zh",
        'jscolor': "jscolor/jscolor.min",
        'ueditor': "ueditor/ueditor.all.min",
        'ueditor.config': "ueditor/ueditor.config",
        'sortable': "Sortable.min",
        'cxselect': 'cx-select/jquery.cxselect.min',
        'lazyload': 'jquery.lazyload/jquery.lazyload'
    },
    map: {
        '*': {
            'css': '../css.min'
        }
    },
    shim: {
        "particleground": {
            deps: ["jquery"],
            exports: "particleground"
        },
        "bootstrap": {
            deps: ["jquery"],
            exports: "bootstrap"
        },
        "icheck": {
            deps: ["jquery", "css!/static/js/icheck/minimal/green.css"],
            exports: "icheck"
        },
        "layer": {
            deps: ["jquery", "css!/static/js/layer/skin/default/layer.css"]
        },
        "bootstrapswitch": {
            deps: ["bootstrap"]
        },
        "flatpickr": {
            deps: ["css!/static/js/flatpickr/flatpickr.min.css"]
        },
        "ueditor": {
            deps: ["jquery", "ueditor.config", "css!/static/js/ueditor/themes/default/css/ueditor.min.css"]
        },
        "cxselect": {
            deps: ["jquery"]
        },
        "lazyload": {
            deps: ["jquery"]
        }
    }
});