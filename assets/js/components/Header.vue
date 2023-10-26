<template>
  <div class="header">
    <div class="header_menu">
      <Button :url="'/'">Список</Button>
    </div>
    <div class="header_actions">
      <Button :url="'/logout'">Logout</Button>
    </div>
  </div>
</template>
<script>
import Button from "../components/Button.vue";
export default {
  components: {
    Button,
  },
  setup() {},
};
</script>

<style scoped>
.header {
  display: flex;
  justify-content: space-between;
}
</style>