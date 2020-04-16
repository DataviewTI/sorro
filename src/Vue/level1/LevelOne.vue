<template>
  <div class="container" style="boder:1px blue solid">
    Componente Level 1
  </div>
</template>

<script>
import Swal from "sweetalert2";

export default {
  data: () => ({
    teste: "asasas",
  }),
  mounted() {
    console.log("montou e disparou levelone");
  },
};
</script>
