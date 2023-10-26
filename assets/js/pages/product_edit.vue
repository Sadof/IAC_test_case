<template>
  <div class="product_edit_page">
    <Header></Header>
    <form class="product_form" id="product_form" ref="product_form">
      <div v-if="product.id">Изименить продукт</div>
      <div v-else>Добавить новый продукт</div>
      <div :class="response_message_class" class="response_message">
        <div v-for="(item, index) in response_message" :key="index">
          {{ item }}
        </div>
      </div>
      <div class="product_form_item">
        <label for="short_description">Краткое описание</label>
        <textarea
          type="text"
          name="short_description"
          v-model="product.short_description"
        ></textarea>
      </div>

      <div class="product_form_item">
        <label for="description">Описание</label>
        <textarea name="description" v-model="product.description"></textarea>
      </div>

      <div class="product_form_item">
        <label for="amount"
          >Количество (Обязательное поле для заполнения)</label
        >
        <input
          type="number"
          name="amount"
          id="amount"
          v-model="product.amount"
          :class="{ error: errors.amount }"
        />
      </div>

      <div class="product_form_item">
        <label for="weight">Вес</label>
        <input type="number" name="weight" v-model="product.weight" />
      </div>

      <div class="product_form_item">
        <label for="added_to_store">Добавлено в магазин</label>
        <date-picker
          v-model="product.added_to_store"
          type="date"
          format="YYYY-MM-DD"
          value-type="format"
          :input-attr="{ name: 'added_to_store' }"
        ></date-picker>
      </div>

      <div class="product_form_item">
        <label for="updated"
          >Обновлено (Обязательное поле для заполнения)</label
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
      </div>

      <div class="product_form_item">
        <label for="product_color">Цвет продукта</label>
        <select
          v-model="product.product_color"
          name="product_color"
          id="product_color"
          value=""
        >
          <option value="" selected>Не выбрано</option>
          <option
            v-for="item in product_colors"
            :key="item.id"
            :value="item.id"
            :selected="item == product.product_color"
          >
            {{ item.name }}
          </option>
        </select>
      </div>

      <div class="product_form_item">
        <label for="product_category">Категория</label>
        <select
          v-model="product.product_category"
          name="product_category"
          id="product_category"
          value=""
        >
          <option value="" selected>Не выбрано</option>
          <option
            v-for="item in product_categories"
            :key="item.id"
            :selected="item == product.product_category"
            :value="item.id"
          >
            {{ item.name }}
          </option>
        </select>
      </div>
      <div class="product_form_item">
        <img
          v-if="product.image"
          :src="product.image"
          alt=""
          class="image_preview"
        />
        <label for="image"
          >Изображение(с расширением .png, .jpg, .jpeg и размером менее 2
          МБ)</label
        >
        <input
          type="file"
          name="image"
          id="image"
          ref="image_input"
          accept=".png, .jpg, .jpeg"
        />
      </div>
      <div class="product_form_item">
        <label for="blob">Блоб</label>
        <input type="text" name="blob" v-model="product.blob" />
      </div>

      <button
        v-if="product.id"
        type="submit"
        @click.prevent="sendRequest"
        ref="product_form_submit"
      >
        Изменить
      </button>
      <button
        v-else
        type="submit"
        @click.prevent="sendRequest"
        ref="product_form_submit"
      >
        Добавить
      </button>
    </form>
  </div>
</template>

<script>
import axios from "axios";
import DatePicker from "vue2-datepicker";
import "vue2-datepicker/index.css";
import Header from "../components/Header.vue";

export default {
  components: {
    DatePicker,
    Header,
  },

  data: () => ({
    product: {
      id: "",
      short_description: "",
      description: "",
      amount: "",
      weight: "",
      added_to_store: "",
      updated: "",
      product_color: "",
      product_category: "",
      image: "",
      blob: "",
    },
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
      this.product = {
        id: "",
        short_description: "",
        description: "",
        amount: "",
        weight: "",
        added_to_store: "",
        updated: "",
        product_color: "",
        product_category: "",
        image: "",
        blob: "",
      };
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
        product_color: data.product_color ? data.product_color.id : null,
        product_category: data.product_category
          ? data.product_category.id
          : null,
        image: data.image,
        blob: data.blob ? data.blob : "",
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

      if (this.response_message.length) return;
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
  width: 400px;
  margin: auto;
}

.product_form_item {
  display: flex;
  flex-direction: column;
  margin-bottom: 10px;
}
.response_message {
  font-size: 20px;
}
.response_message.success {
  color: green;
}
.response_message.error {
  color: red;
}

input.error {
  border: 1px solid red;
}
</style>

<style>
.error.mx-input {
  border: 1px solid red !important;
}
.product_form .mx-datepicker {
  width: 100% !important;
}
</style>
