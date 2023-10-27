<template>
  <a
    v-if="emit"
    :href="url"
    class="btn btn-outline-secondary"
    :class="{ disabled: disabled }"
    @click="$emit(emit, $event)"
    ><slot></slot
  ></a>
  <a v-else :href="url"
    class="btn btn-outline-secondary"
    :class="{ disabled: disabled }"
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