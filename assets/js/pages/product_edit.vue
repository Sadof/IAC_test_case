<template>
  <div class="product_edit_page">
    <Header></Header>
    <b-form
      id="product_form"
      ref="product_form"
      class="product_form mb-5"
      @submit="sendRequest"
    >
      <div class="product_form_header mb-3 mt-3">
        {{ product.id ? "Изменить продукт" : "Добавить новый продукт" }}
      </div>

      <div
        :class="response_message_class"
        class="response_message"
        ref="response_message"
      >
        <div v-for="(item, index) in response_message" :key="index">
          {{ item }}
        </div>
      </div>
      <b-form-group label="Краткое описание" label-for="input-1" class="mb-3">
        <b-form-textarea
          id="short_description"
          v-model="product.short_description"
          placeholder=""
          rows="3"
          max-rows="6"
          name="short_description"
        ></b-form-textarea>
      </b-form-group>
      <b-form-group label="Описание" label-for="description" class="mb-3">
        <b-form-textarea
          id="description"
          v-model="product.description"
          placeholder=""
          rows="3"
          max-rows="6"
          name="description"
        ></b-form-textarea>
      </b-form-group>
      <b-form-group
        label="Количество (Обязательное поле для заполнения)"
        label-for="amount"
        class="mb-3"
      >
        <b-form-input
          v-model="product.amount"
          placeholder=""
          type="number"
          :class="{ error: errors.amount }"
          id="amount"
          name="amount"
        ></b-form-input>
      </b-form-group>

      <b-form-group label="Вес" label-for="weight">
        <b-form-input
          v-model="product.weight"
          placeholder=""
          type="number"
          id="weight"
          class="mb-3"
          name="weight"
        ></b-form-input>
      </b-form-group>

      <b-form-group
        label="Добавлено в магазин"
        label-for="added_to_store"
        class="mb-3"
      >
        <date-picker
          v-model="product.added_to_store"
          type="date"
          format="YYYY-MM-DD"
          value-type="format"
          :input-attr="{ name: 'added_to_store' }"
          id="added_to_store"
        ></date-picker>
      </b-form-group>

      <b-form-group
        label="Обновлено (Обязательное поле для заполнения)"
        label-for="updated"
        class="mb-3"
      >
        <date-picker
          v-model="product.updated"
          type="datetime"
          format="YYYY-MM-DD HH:mm:ss"
          value-type="format"
          id="updated"
          :input-class="[errors.updated ? 'mx-input error' : 'mx-input']"
          :input-attr="{ name: 'updated' }"
        ></date-picker>
      </b-form-group>

      <b-form-group
        label="Цвет продукта"
        label-for="product_color"
        class="mb-3"
      >
        <b-form-select
          v-model="product.product_color"
          class="mb-3"
          :options="product_colors"
          value-field="id"
          text-field="name"
          name="product_color"
        >
          <b-form-select-option :value="''">Не выбрано</b-form-select-option>
        </b-form-select>
      </b-form-group>

      <b-form-group label="Категория" label-for="product_category" class="mb-3">
        <b-form-select
          v-model="product.product_category"
          class="mb-3"
          :options="product_categories"
          value-field="id"
          text-field="name"
          name="product_category"
        >
          <b-form-select-option :value="''">Не выбрано</b-form-select-option>
        </b-form-select>
      </b-form-group>

      <b-form-group
        label="Превью"
        label-for="product_category"
        class="mb-3"
        v-if="product.image"
      >
        <b-img :src="product.image" fluid alt="Responsive image"></b-img>
      </b-form-group>

      <b-form-group
        label="Изображение(с расширением .png, .jpg, .jpeg и размером менее 2
          МБ)"
        label-for="product_category"
        class="mb-3"
      >
        <b-form-file
          ref="image_input"
          accept=".png, .jpg, .jpeg"
          name="image"
        ></b-form-file>
      </b-form-group>

      <b-form-group label="Вложение" label-for="product_category" class="mb-3">
        <div class="product_form_item_blob_download">
          <a :href="'/api/get_data_blob/' + product.id">Download</a>
        </div>
        <div class="product_form_item_blob_name">{{ product.blob_name }}</div>
        <b-form-file ref="blob_input" name="blob"></b-form-file>
      </b-form-group>

      <b-button
        type="submit"
        variant="success"
        @click.prevent="sendRequest"
        ref="product_form_submit"
        >{{ product.id ? "Изменить" : "Добавить" }}</b-button
      >
    </b-form>
  </div>
</template>

<script>
import axios from "axios";
import DatePicker from "vue2-datepicker";
import "vue2-datepicker/index.css";
import Header from "../components/Header.vue";
import ProductConstants from "../constants/products";

export default {
  components: {
    DatePicker,
    Header,
  },

  data: () => ({
    product: structuredClone(ProductConstants.empty_product),
    product_colors: [],
    product_categories: [],
    csrf: "",
    response_message: [],
    response_message_class: "",

    errors: {
      amount: false,
      updated: false,
    },
    loading: false,
  }),

  mounted() {
    this.loadProduct();
    axios
      .get("/api/data_list_additional_data")
      .then((response) => {
        let data = JSON.parse(response.data);
        this.product_colors = data.product_color;
        this.product_categories = data.product_category;
      })
      .then(() => {});
    this.csrf = document.getElementById("_csrf_token").value;
  },

  methods: {
    setEmptyProduct() {
      this.product = structuredClone(ProductConstants.empty_product);
    },
    setProduct(data) {
      this.product = {
        id: data.id,
        short_description: data.short_description,
        description: data.description,
        amount: data.amount,
        weight: data.weight,
        added_to_store: data.added_to_store,
        updated: data.updated,
        product_color: data.product_color ? data.product_color.id : "",
        product_category: data.product_category ? data.product_category.id : "",
        image: data.image,
        blob: data.blob,
        blob_name: data.blob_name,
      };
    },
    setEmptyErrors() {
      this.errors = {
        amount: false,
        updated: false,
      };
    },

    sendRequest() {
      if (this.loading) return;
      this.response_message = [];
      this.response_message_class = "";
      this.setEmptyErrors();
      this;
      if (!this.product.amount) {
        this.response_message.push(
          'Не заполнено обязательное поле "Количество"'
        );
        this.response_message_class = "error";
        this.errors.amount = true;
      }
      if (!this.product.updated) {
        this.response_message.push(
          'Не заполнено обязательное поле "Обновлено"'
        );
        this.response_message_class = "error";
        this.errors.updated = true;
      }
      if (
        this.$refs.image_input.files[0] &&
        !this.checkImageFile(this.$refs.image_input.files[0])
      ) {
        this.response_message.push("Изображение не соответствует требованиям");
        this.response_message_class = "error";
      }

      if (this.response_message.length) {
        this.$refs.response_message.scrollIntoView();
        return;
      }
      const formData = new FormData(
        this.$refs.product_form,
        this.$refs.product_form_submit
      );
      formData.append("csrf_token", this.csrf);
      formData.append("id", this.product.id);
      this.loading = true;
      axios
        .post("/api/data_edit", formData, {
          headers: {
            "Content-Type": "multipart/form-data",
          },
        })
        .then((response) => {
          if (response.status == "200") {
            this.response_message.push("Продукт создан");
            this.response_message_class = "success";
            if (!this.product.id) {
              this.setEmptyProduct();
              this.$refs.product_form.reset();
            } else {
              this.loadProduct();
            }
          } else {
            this.response_message.push("Ошибка создания продукта");
            this.response_message_class = "error";
          }
        })
        .catch((error) => {
          this.response_message.push("Ошибка создания продукта");
          this.response_message_class = "error";
        })
        .finally(() => {
          this.loading = false;
        });
      this.$refs.response_message.scrollIntoView();
    },

    loadProduct() {
      if (document.getElementById("product_id")) {
        let product_id = document.getElementById("product_id").value;
        axios
          .post("/api/get_data", { product_id: product_id })
          .then((response) => {
            let data = JSON.parse(response.data);
            this.setProduct(data);
          });
      }
    },
    checkImageFile(file) {
      var validImageTypes = ["image/jpg", "image/jpeg", "image/png"];
      return (
        file.size / 1024 / 1024 < 2 && validImageTypes.includes(file["type"])
      );
    },
  },
};
</script>

<style scoped>
.product_form {
  display: flex;
  flex-direction: column;
  width: 720px;
  margin: auto;
}

.product_form_header {
  margin: auto;
  font-weight: 700;
  font-size: 24px;
}

@media screen and (max-width: 720px) {
  .product_form {
    width: 100%;
  }
}
</style>
