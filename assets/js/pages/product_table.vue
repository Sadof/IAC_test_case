<template>
  <div class="product_table_page" id="product_table_page">
    <div class="buttons_row">
      <Button v-if="userCanAdd" :url="'/product/create'">Добавить</Button>
      <Button
        v-if="userCanEdit"
        :emit="'updateButtonClick'"
        :url="updateButtonUrl"
        >Редактировать</Button
      >
      <Button
        v-if="userCanDelete"
        :disabled="disableButtons"
        :emit="'deleteButtonClick'"
        v-on:deleteButtonClick="deleteButtonClick"
        >Удалить</Button
      >
    </div>

    <template v-if="userCanView">
      <ProductTableSearchForm
        :table_fields="table_fields"
        v-on:search="search"
      ></ProductTableSearchForm>
      <div
        class="product_table"
        :class="userCanEdit || userCanDelete ? 'clickable' : ''"
      >
        <div class="product_table_header">
          <div
            class="product_table_header_item"
            v-for="(item, field) in table_fields"
            :key="'header_' + field"
            @click="sortBy(field)"
          >
            {{ item.translastion }}
          </div>
        </div>
        <ProductTableRow
          v-for="product in products"
          :item="product"
          :index="product.id"
          :key="product.id"
          :highlightedRow="highlightedRow"
          v-on:setSelectedRow="setSelectedRow"
        >
        </ProductTableRow>
      </div>
      <Pagination
        :page="page"
        :max_entities="max_entities"
        :per_page="per_page"
        v-on:changePage="changePage"
      ></Pagination>
    </template>
    <template v-else>
      <div>У пользователя нету прав для просмотра таблицы</div>
    </template>
  </div>
</template>

<script>
import axios from "axios";
import ProductTableRow from "../components/ProductTableRow.vue";
import ProductTableSearchForm from "../components/ProductTableSearchForm.vue";
import Pagination from "../components/Pagination.vue";
import Button from "../components/Button.vue";

export default {
  components: {
    ProductTableRow,
    ProductTableSearchForm,
    Pagination,
    Button,
  },
  data: () => ({
    user_roles: [],
    products: {},
    table_fields: {
      short_description: {
        translastion: "Краткое описание",
        search_type: "string",
      },
      description: {
        translastion: "Описание",
        search_type: "string",
      },
      amount: {
        translastion: "Количество",
        search_type: "int",
      },
      weight: {
        translastion: "Вес",
        search_type: "float",
      },
      added_to_store: {
        translastion: "Добавлено в магазин",
        search_type: "date",
      },
      updated: {
        translastion: "Обновлено",
        search_type: "date",
      },
      product_color: {
        translastion: "Цвет продукта",
        search_type: "select",
        select_values: {},
      },
      product_category: {
        translastion: "Категория",
        search_type: "select",
        select_values: {},
      },
    },
    table_loading: false,
    page: 1,
    max_entities: 0,
    per_page: 0,
    selected_row: null,
    csrf: "",
  }),

  mounted() {
    this.csrf = document.getElementById("_csrf_token").value;
    axios
      .get("/api/data_list_additional_data")
      .then((response) => {
        let data = JSON.parse(response.data);
        this.user_roles = data.user_roles;
        this.table_fields.product_color.select_values = data.product_color;
        this.table_fields.product_category.select_values =
          data.product_category;
      })
      .then(() => {
        if (this.userCanView) {
          this.getDataList();
        }
      });
    this.getPage();
  },

  computed: {
    userCanView() {
      if (this.user_roles.includes("ROLE_LIST_VIEW")) return true;
      return false;
    },
    userCanAdd() {
      if (this.user_roles.includes("ROLE_ADD")) return true;
      return false;
    },
    userCanEdit() {
      if (this.user_roles.includes("ROLE_EDIT")) return true;
      return false;
    },
    userCanDelete() {
      if (this.user_roles.includes("ROLE_DELETE")) return true;
      return false;
    },
    disableButtons() {
      return this.selected_row ? false : true;
    },
    highlightedRow() {
      return this.selected_row && (this.userCanDelete || this.userCanEdit)
        ? this.selected_row
        : 0;
    },
    updateButtonUrl(){
      return "/product/edit/" + this.selected_row;
    }
  },
  methods: {
    getDataList() {
      this.selected_row = null;
      const params = new URLSearchParams(window.location.search);

      axios.get("/api/data_list", { params }).then((response) => {
        var json = JSON.parse(response.data);
        this.products = json.data;
        this.max_entities = json.max_entities;
        this.per_page = json.per_page;
      });
    },
    sortBy(field) {
      var url = new URL(window.location);

      if (
        url.searchParams.has("sort_field") &&
        url.searchParams.get("sort_field") == field
      ) {
        if (url.searchParams.get("order") == 1) {
          url.searchParams.set("sort_field", field);
          url.searchParams.set("order", 0);
        } else {
          url.searchParams.delete("sort_field");
          url.searchParams.delete("order");
        }
      } else {
        url.searchParams.set("sort_field", field);
        url.searchParams.set("order", 1);
      }
      url.searchParams.delete("page");

      window.history.pushState({}, "", url);

      this.getDataList();
    },

    search(data) {
      var url = new URL(window.location);
      url.searchParams.set("search_field", data.search_field);
      url.searchParams.set("search_value", data.search_value);
      window.history.pushState({}, "", url);

      this.getDataList();
    },

    changePage(page) {
      var url = new URL(window.location);
      url.searchParams.set("page", page);
      window.history.pushState({}, "", url);
      this.page = page;
      this.getDataList();
    },

    getPage() {
      var url = new URL(window.location);
      this.page = url.searchParams.get("page")
        ? parseInt(url.searchParams.get("page"))
        : 1;
    },
    setSelectedRow(id) {
      if (this.selected_row == id) {
        this.selected_row = null;
      } else {
        this.selected_row = id;
      }
    },
    deleteButtonClick(e) {
      e.preventDefault();
      if (!this.selected_row) return;
      // TODO компонент с модалкой
      if (!confirm("Удалить выбранную запись?")) return;
      axios
        .post("/api/delete_data", {
          csrf: this.csrf,
          product_id: this.selected_row,
        })
        .then(() => {
          this.getDataList();
          this.selected_row = null;
        })
        .catch(() => {
          // TODO компонент с сайд оповещениями
          alert("Ошибка при удалении");
        });
    },
  },
};
</script>

<style scoped>
.product_table {
  display: table;
}
.product_table_header {
  display: table-row;
}
.product_table_header_item {
  display: table-cell;
  padding: 10px;
  cursor: pointer;
}
</style>

<style>
.product_table.clickable .product-table-row {
  cursor: pointer;
}
</style>