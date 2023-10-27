<template>
  <div class="product_table_page" id="product_table_page">
    <Header></Header>
    <div class="buttons_row my-3">
      <Button v-if="userCanAdd" :url="'/product/create'">Добавить</Button>
      <Button
        v-if="userCanEdit"
        :disabled="disableButtons"
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
      <table
        class="table"
        :class="userCanEdit || userCanDelete ? 'clickable' : ''"
      >
        <thead>
          <tr class="product_table_header">
            <th
              class="product_table_header_item"
              v-for="(item, field) in table_fields"
              :key="'header_' + field"
              :class="field == sort_field ? (order == 1 ? 'down' : 'up') : ''"
              @click="sortBy(field, $event)"
            >
              {{ item.translastion }}
            </th>
          </tr>
        </thead>
        <tbody>
          <ProductTableRow
            v-for="product in products"
            :item="product"
            :index="product.id"
            :key="product.id"
            :highlightedRow="highlightedRow"
            v-on:setSelectedRow="setSelectedRow"
          >
          </ProductTableRow>
        </tbody>
      </table>
      <Pagination
        :page="page"
        :max_entities="max_entities"
        :per_page="per_page"
        v-on:changePage="changePage"
      ></Pagination>
    </template>
    <template v-else>
      <div class="no_rights">У пользователя нету прав для просмотра таблицы</div>
    </template>
  </div>
</template>

<script>
import axios from "axios";
import ProductTableRow from "../components/ProductTableRow.vue";
import ProductTableSearchForm from "../components/ProductTableSearchForm.vue";
import Pagination from "../components/Pagination.vue";
import Button from "../components/Button.vue";
import Header from "../components/Header.vue";
import ProductConstants from "../constants/products";

export default {
  components: {
    ProductTableRow,
    ProductTableSearchForm,
    Pagination,
    Button,
    Header,
  },
  data: () => ({
    user_roles: [],
    products: {},
    table_fields: structuredClone(ProductConstants.table_fields),
    table_loading: false,
    page: 1,
    max_entities: 0,
    per_page: 0,
    selected_row: null,
    csrf: "",
    sort_field: "",
    order: "",
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
    updateButtonUrl() {
      return "/product/edit/" + this.selected_row;
    },
  },
  methods: {
    getDataList() {
      this.selected_row = null;
      const params = new URLSearchParams(window.location.search);
      this.updateSortValues();
      axios.get("/api/data_list", { params }).then((response) => {
        var json = JSON.parse(response.data);
        this.products = json.data;
        this.max_entities = json.max_entities;
        this.per_page = json.per_page;
      }).catch(()=>{
        alert("Не удалось получить данные")
      });
    },
    sortBy(field, event) {
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

    updateSortValues() {
      var url = new URL(window.location);
      this.sort_field = url.searchParams.get("sort_field");
      this.order = url.searchParams.get("order");
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
        .post(
          "/api/delete_data",
          {
            csrf_token: this.csrf,
            product_id: this.selected_row,
          },
          {
            headers: {
              "Content-Type": "multipart/form-data",
            },
          }
        )
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
.product_table_header_item {
  cursor: pointer;
  text-wrap: nowrap;
}
.product_table_header_item.down::after {
  content: "\25bc";
  color: gray;
  position: absolute;
}
.product_table_header_item.up::after {
  content: "\25bc";
  color: gray;
  position: absolute;
  transform: rotate(180deg);
}
.product_table {
  margin-top: 20px;
}

.buttons_row {
  padding: 5px 20px;
}

.no_rights{
  margin-left: 20px;
  font-size: 20px;
  font-weight: 700;
}
</style>