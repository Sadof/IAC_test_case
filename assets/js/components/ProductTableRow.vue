<template>
  <div class="product-table-row">
    <div class="product-table-row-item">
      {{ item.short_description }}
    </div>
    <div class="product-table-row-item">
      {{ item.description }}
    </div>
    <div class="product-table-row-item">
      {{ item.amount }}
    </div>
    <div class="product-table-row-item">
      {{ item.weight }}
    </div>
    <div class="product-table-row-item">
      {{ item.added_to_store }}
    </div>
    <div class="product-table-row-item">
      {{ item.updated }}
    </div>
    <div class="product-table-row-item">
      {{ item.product_color.name }}
    </div>
    <div class="product-table-row-item">
      {{ item.product_category.name }}
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
  item: Object,
});
</script>

<style scoped>
.product-table-row {
  display: table-row;
}
.product-table-row-item {
  display: table-cell;
  padding: 10px;
}
</style>