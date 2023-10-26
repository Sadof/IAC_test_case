<template>
  <a v-if="emit" :href="url" class="button" :class="{ disabled: disabled }" @click="$emit(emit, $event)"
    ><slot></slot
  ></a>
  <a v-else :href="url" class="button" :class="{ disabled: disabled }"
    ><slot></slot
  ></a>
</template>

<script >
export default {
  name: "Button",
  props: {
    url: {
      type: String,
      default: "#",
    },
    disabled: {
      default: false,
    },
    emit: {
      default: false,
    },
  },
};
</script>

<style scoped>
.button {
  padding: 5px 10px;
  border: 1px solid grey;
  border-radius: 5px;
  background: rgb(182, 182, 182);
  text-decoration: none;
  color: white;
  margin: 5px;
  display: block;
  width: 125px;
  text-align: center;
}

a.disabled {
  pointer-events: none;
}
</style>