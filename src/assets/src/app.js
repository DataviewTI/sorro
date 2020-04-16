if (window.Vue === undefined) window.Vue = require("vue");
import vuetify from "./plugins/vuetify"; // path to vuetify export
import VueRouter from "vue-router";
import routes from "./routes";
const files = require.context("./../../Vue/", true, /\.vue$/i);
files.keys().map((key) =>
  Vue.component(
    key
      .split("/")
      .pop()
      .split(".")[0],
    files(key).default
  )
);

const router = new VueRouter({
  routes, // short for `routes: routes`
});

Vue.use(VueRouter);

new Vue({
  router,
  vuetify,
}).$mount("#app");

// const app = new Vue({
//   el: "#app",
// });
