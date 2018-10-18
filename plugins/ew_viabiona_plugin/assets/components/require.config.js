var components = {
    "packages": [
        {
            "name": "Hover",
            "main": "Hover-built.js"
        },
        {
            "name": "html5shiv",
            "main": "html5shiv-built.js"
        },
        {
            "name": "jquery-match-height",
            "main": "jquery-match-height-built.js"
        },
        {
            "name": "bootstrap",
            "main": "bootstrap-built.js"
        },
        {
            "name": "jquery",
            "main": "jquery-built.js"
        },
        {
            "name": "animate.css",
            "main": "animate.css-built.js"
        },
        {
            "name": "WOW",
            "main": "WOW-built.js"
        },
        {
            "name": "ladda-bootstrap",
            "main": "ladda-bootstrap-built.js"
        },
        {
            "name": "SocialSharePrivacy",
            "main": "SocialSharePrivacy-built.js"
        },
        {
            "name": "Respond",
            "main": "Respond-built.js"
        },
        {
            "name": "bootstrap-select",
            "main": "bootstrap-select-built.js"
        },
        {
            "name": "bootlint",
            "main": "bootlint-built.js"
        },
        {
            "name": "jquery-spinner",
            "main": "jquery-spinner-built.js"
        }
    ],
    "shim": {
        "bootstrap": {
            "deps": [
                "jquery"
            ]
        }
    },
    "baseUrl": "components"
};
if (typeof require !== "undefined" && require.config) {
    require.config(components);
} else {
    var require = components;
}
if (typeof exports !== "undefined" && typeof module !== "undefined") {
    module.exports = components;
}