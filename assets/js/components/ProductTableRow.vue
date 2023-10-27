<template>
  <tr
    class="product-table-row"
    :data-product-id="item.id"
    @click="$emit('setSelectedRow', item.id)"
    :class="highlightedRow == item.id ? 'table-active' : ''"
  >
    <td class="product-table-row-item">
      {{ item.short_description }}
    </td>
    <td class="product-table-row-item">
      {{ item.description }}
    </td>
    <td class="product-table-row-item">
      {{ item.amount }}
    </td>
    <td class="product-table-row-item">
      {{ item.weight }}
    </td>
    <td class="product-table-row-item">
      {{ item.added_to_store }}
    </td>
    <td class="product-table-row-item">
      {{ item.updated }}
    </td>
    <td class="product-table-row-item">
      {{ item.product_color ? item.product_color.name : "" }}
    </td>
    <td class="product-table-row-item">
      {{ item.product_category ? item.product_category.name : "" }}
    </td>
  </tr>
</template>

<script setup>
const props = defineProps({
  item: Object,
  highlightedRow: Number,
});
</script>
