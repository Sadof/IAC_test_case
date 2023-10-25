<template>
  <div class="pagination">
      <div v-for="index in pages" :key="'pagination_key_' + index" class="pagination_item" :class="(index == page) ? 'pagination_item_current': ''" @click="changePage(index)">
        {{ index }}
    </div>
  </div>
</template>

<script >
export default {
  name: "Pagination",
  props: {
    page: Number,
    max_entities: Number,
    per_page: Number,
  },
  data: () => ({}),
  computed: {
    pages: function () {
      var pages = Math.ceil(this.max_entities / this.per_page);
      return pages >= 1 ? pages : 1;
    },
  },
  methods: {
    changePage(index) {
      if (index == this.page) return;

      this.$emit("changePage", index);
    },
  },
};
</script>

<style scoped>
.pagination {
  display: flex;
  justify-content: center;
}
.pagination_item {
  padding: 5px 10px;
  border: 1px solid black;
  margin: 0 5px;
  cursor: pointer;
  display: none;
}
.pagination_item.pagination_item_current {
  background-color: black;
  color: white;
}

.pagination_item:first-child,
.pagination_item.pagination_item_current,
.pagination_item:last-child,
.pagination_item:has(+ .pagination_item.pagination_item_current),
.pagination_item.pagination_item_current + .pagination_item {
  display: block;
}
</style>