<template>
  <!-- ... existing code ... -->
  <v-data-table
    :headers="headers"
    :items="desserts"
    :search="search"
  >
    <template v-slot:item="{ item }">
      <tr>
        <td>{{ item.upc }}</td>
        <td>{{ item.name }}</td>
        <td>{{ item.description }}</td>
        <td>{{ item.quantity }}</td>
        <td>{{ item.price }}</td>
        <td>{{ item.category }}</td>
        <td>
          <v-btn @click="openQuantityModal(item)">
            Add Quantity
          </v-btn>
          <br>
          <router-link :to="{ path: '/history/' + item.upc }" class="button">Audit</router-link>
        </td>
      </tr>
    </template>
  </v-data-table>

  <!-- Quantity Modal -->
  <v-dialog v-model="quantityModal" max-width="400">
    <v-card>
      <v-card-title>Add Quantity</v-card-title>
      <v-card-text>
        <!-- Your quantity input field and other necessary fields go here -->
        <v-text-field v-model="quantityToAdd" label="Quantity"></v-text-field>
      </v-card-text>
      <v-card-actions>
        <v-btn @click="addQuantity">Add</v-btn>
        <v-btn @click="closeQuantityModal">Close</v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

  <script>
  import axios from 'axios'
    export default {
      data () {
        return {
          quantityModal: false,
      selectedProduct: null,
      quantityToAdd: 0,
          search: '',
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
          ],
          desserts: [],
        }
      },
      created(){
        this.getProducts();
      },
      methods:{
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
      this.getProducts();
      this.closeQuantityModal();
    },
        async getProducts(){
          const data = await axios.get('api/getProducts');
          this.desserts = data.data;
        }
      }
    }
  </script>