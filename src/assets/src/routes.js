export default [
  {
    path: "/",
    name: "home",
    component: () => import("./../../Vue/Home"),
  },
  {
    path: "/contact",
    name: "contact",
    component: () => import("./../../Vue/Contact"),
  },
];
