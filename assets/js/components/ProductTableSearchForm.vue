<template>
  <div class="product_table_search_form">
    <select
      v-model="selected_search_field"
      name="search_field"
      id="search_field"
      @change="selected_search_value = ''"
    >
      <option
        v-bind:value="field"
        v-for="(item, field) in table_fields"
        :key="'header_' + field"
      >
        {{ item.translastion }}
      </option>
    </select>
    <template v-if="table_fields">
      <template
        v-if="
          ['string'].includes(
            table_fields[selected_search_field]['search_type']
          )
        "
      >
        <input
          type="text"
          name="search_value"
          id="search_value"
          v-model="selected_search_value"
          :data-check="table_fields[selected_search_field]['search_type']"
        />
      </template>
      <template
        v-else-if="
          ['int', 'float'].includes(
            table_fields[selected_search_field]['search_type']
          )
        "
      >
        <input
          type="number"
          name="search_value"
          id="search_value"
          v-model="selected_search_value"
          :data-check="table_fields[selected_search_field]['search_type']"
        />
      </template>
      <template
        v-else-if="
          table_fields[selected_search_field]['search_type'] == 'select'
        "
      >
        <select
          v-model="selected_search_value"
          name="search_value"
          id="search_value"
        >
          <option
            v-bind:value="item.id"
            v-for="item in table_fields[selected_search_field]['select_values']"
            :key="item.id"
          >
            {{ item.name }}
          </option>
        </select>
      </template>
      <template
        v-else-if="table_fields[selected_search_field]['search_type'] == 'date'"
      >
        <date-picker
          v-model="selected_search_value"
          range
          type="date"
          format="YYYY-MM-DD"
          range-separator="-"
          value-type="format"
        ></date-picker>
      </template>

      <button @click="checkInput">Поиск</button>
    </template>
  </div>
</template>

<script >
import DatePicker from "vue2-datepicker";
import "vue2-datepicker/index.css";

export default {
  components: { DatePicker },
  props: {
    table_fields: Object,
  },
  data: () => ({
    selected_search_field: "",
    selected_search_value: "",
  }),
  created() {
    var url = new URL(window.location);
    if (
      url.searchParams.has("search_field") &&
      url.searchParams.has("search_value")
    ) {
      this.selected_search_field = url.searchParams.get("search_field");
      this.selected_search_value = url.searchParams.get("search_value");
    } else {
      this.selected_search_field = Object.keys(this.table_fields)[0];
    }
  },
  computed: {},
  methods: {
    checkInput() {
      this.$emit("search", {
        search_field: this.selected_search_field,
        search_value: this.selected_search_value,
      });
    },
  },
};
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