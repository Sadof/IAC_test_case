const table_fields = {
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
}

const empty_product = {
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
    blob_name: "",
}

export default {
    table_fields: table_fields,
    empty_product: empty_product,
}