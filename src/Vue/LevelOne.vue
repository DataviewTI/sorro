<template>
  <div class="container" style="boder:1px blue solid">
    Componente Level 1
  </div>
</template>

<script>
export default {
  data: () => ({
    teste: "asasas",
  }),
  mounted() {
    Swal.fire("Componente Level 1", "success");
  },
};
</script>
