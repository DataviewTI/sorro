"use strict";

// window.Vue = require("vue");

function Sorro(p = {}) {
  p.mix.js("node_modules/sorro/src/app.js", "public/js/sorro.js");
}
module.exports = Sorro;
