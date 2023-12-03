<template>
  <v-data-table
    :headers="headers"
    :items="desserts"
    :sort-by="[{ key: 'name', order: 'desc' }]"
  >
    <template v-slot:top>
      <v-toolbar
        flat
      >
        <v-toolbar-title>My CRUD</v-toolbar-title>
        <v-divider
          class="mx-4"
          inset
          vertical
        ></v-divider>
        <v-spacer></v-spacer>
        <v-dialog
          v-model="dialog"
          max-width="500px"
        >
          <template v-slot:activator="{ props }">
            <v-btn
              color="primary"
              dark
              class="mb-2"
              v-bind="props"
            >
              New Item
            </v-btn>
          </template>
          <v-card>
            <v-card-title>
              <span class="text-h5">{{ formTitle }}</span>
            </v-card-title>

            <v-card-text>
              <v-container>
                <v-row>
                  <v-col
                    cols="12"
                    sm="6"
                    md="4"
                  >
                    <v-text-field
                      v-model="editedItem.upc"
                      label="Universal Product Code"
                    ></v-text-field>
                  </v-col>
                  <v-col
                    cols="12"
                    sm="6"
                    md="4"
                  >
                    <v-text-field
                      v-model="editedItem.name"
                      label="Product Name"
                    ></v-text-field>
                  </v-col>
                  <v-col
                    cols="12"
                    sm="6"
                    md="4"
                  >
                    <v-text-field
                      v-model="editedItem.description"
                      label="Product Description"
                    ></v-text-field>
                  </v-col>
                  <v-col
                    cols="12"
                    sm="6"
                    md="4"
                  >
                    <v-text-field
                      v-model="editedItem.price"
                      label="Price"
                    ></v-text-field>
                  </v-col>
                  <v-col
                    cols="12"
                    sm="6"
                    md="4"
                  >
                    <v-text-field
                      v-model="editedItem.cateogry"
                      label="Category"
                    ></v-text-field>
                  </v-col>
                </v-row>
              </v-container>
            </v-card-text>

            <v-card-actions>
              <v-spacer></v-spacer>
              <v-btn
                color="blue-darken-1"
                variant="text"
                @click="close"
              >
                Cancel
              </v-btn>
              <v-btn
                color="blue-darken-1"
                variant="text"
                @click="save"
              >
                Save
              </v-btn>
            </v-card-actions>
          </v-card>
        </v-dialog>
        <v-dialog v-model="dialogDelete" max-width="500px">
          <v-card>
            <v-card-title class="text-h5">Are you sure you want to delete this item?</v-card-title>
            <v-card-actions>
              <v-spacer></v-spacer>
              <v-btn color="blue-darken-1" variant="text" @click="closeDelete">Cancel</v-btn>
              <v-btn color="blue-darken-1" variant="text" @click="deleteItemConfirm">OK</v-btn>
              <v-spacer></v-spacer>
            </v-card-actions>
          </v-card>
        </v-dialog>
      </v-toolbar>
    </template>
    <template v-slot:item.actions="{ item }">
      <v-icon
        size="small"
        class="me-2"
        @click="editItem(item)"
      >
        mdi-pencil
      </v-icon>
      <v-icon
        size="small"
        @click="openQuantityModal(item)"
      >
        mdi-plus
      </v-icon>
      
      <router-link :to="{ name: 'history', params: { upc: item.upc } }">
      <v-icon size="small">mdi-history</v-icon>
    </router-link>
      
    </template>
    <template v-slot:no-data>
      <v-btn
        color="primary"
        @click="initialize"
      >
        Reset
      </v-btn>
    </template>
  </v-data-table>
</template>
<script>
import axios from 'axios'
  export default {
    data: () => ({
      dialog: false,
      quantityModal: false,
      selectedProduct: null,
      quantityToAdd: 0,
      search: '',
      dialogDelete: false,
    headers: [
            {
              align: 'start',
              key: 'upc',
              sortable: false,
              title: 'UPC',
            },
            { key: 'name', title: 'Name' },
            { key: 'description', title: 'Description' },
            { key: 'quantity', title: 'Current Stocks' },
            { key: 'price', title: 'Price' },
            { key: 'cateogry', title: 'Category' },
            { title: 'Actions', key: 'actions', sortable: false },
          ],
      desserts: [],
      editedIndex: -1,
      editedItem: {
        upc: '',
        name: '',
        description: '',
        quantity: 0,
        price: 0,
        cateogry:''
      },
      defaultItem: {
        upc: '',
        name: '',
        description: '',
        quantity: 0,
        price: 0,
        cateogry:''
      },
    }),

    computed: {
      formTitle () {
        return this.editedIndex === -1 ? 'New Item' : 'Edit Item'
      },
    },

    watch: {
      dialog (val) {
        val || this.close()
      },
      dialogDelete (val) {
        val || this.closeDelete()
      },
    },

    created () {
      this.initialize()
    },

    methods: {
      openQuantityModal(item) {
      this.selectedProduct = item;
      this.quantityToAdd = 0;
      this.quantityModal = true;
    },
    closeQuantityModal() {
      this.selectedProduct = null;
      this.quantityToAdd = 0;
      this.quantityModal = false;
    },
    async addQuantity() {
      const updatedProduct = { ...this.selectedProduct };
      updatedProduct.quantity += parseInt(this.quantityToAdd);
      await axios.post('api/updateQuantity', {
        upc: updatedProduct.upc,
        quantity: this.quantityToAdd,
      });
      const index = this.desserts.findIndex((product) => product.upc === updatedProduct.upc);
      this.initialize();
      this.closeQuantityModal();
    },
        async  initialize () {
        const data = await axios.get('api/getProducts');
          this.desserts = data.data;
      },

      editItem (item) {
        this.editedIndex = this.desserts.indexOf(item)
        this.editedItem = Object.assign({}, item)
        this.dialog = true
      },

      deleteItem (item) {
        this.editedIndex = this.desserts.indexOf(item)
        this.editedItem = Object.assign({}, item)
        this.dialogDelete = true
      },

      deleteItemConfirm () {
        this.desserts.splice(this.editedIndex, 1)
        this.closeDelete()
      },

      close () {
        this.dialog = false
        this.$nextTick(() => {
          this.editedItem = Object.assign({}, this.defaultItem)
          this.editedIndex = -1
        })
      },

      closeDelete () {
        this.dialogDelete = false
        this.$nextTick(() => {
          this.editedItem = Object.assign({}, this.defaultItem)
          this.editedIndex = -1
        })
      },

      async save () {
        if (this.editedIndex > -1) {
          // edit
          const response = await axios.put(`api/updateproduct/${this.editedItem.id}`, this.editedItem);
          console.log(response.data);
          Object.assign(this.desserts[this.editedIndex], this.editedItem)
        } else {
          
          const response = await axios.post('api/newproduct', this.editedItem);
          console.log(response.data);
          this.desserts.push(this.editedItem)
        }
        this.close()
      },
    },
  }
</script>