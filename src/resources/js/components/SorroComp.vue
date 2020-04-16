<template>
  <div class="container" style="boder:1px blue solid">
    Isso é um componente sorro
  </div>
</template>

<script>
import Swal from "sweetalert2";

export default {
  data: () => ({
    teste: "asasas",
  }),
  mounted() {
    console.log("Component mounted.");
    console.log(Swal);
    Swal.fire("Oops...", "Something went wrong!", "error");
  },
};
</script>
