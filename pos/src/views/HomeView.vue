<template>
  <v-app>
    <v-row>
      <v-col>
        <div v-if="salesTransactionNumber">
              Sales Transaction Number: <b>{{ salesTransactionNumber }}</b>
        </div>
        <div>Total Price: <b>{{ totalSalesPrice.toFixed(2) }}</b></div>

      <v-data-table :items="tableData" :headers="tableHeaders" dense>
      <template v-slot:items="props">
        <td>{{ props.item.name }}</td>
        <td>{{ props.item.price }}</td>
        <td>{{ props.item.quantity }}</td>
        <td>{{ props.item.quantity * props.item.price }}</td>
      </template>
    </v-data-table>
      </v-col>
    </v-row>
    <v-footer app>
      <v-container>
        <v-row justify="center">
          <v-col cols="12" sm="6">
            <v-text-field
              v-model="inputText"
              label="Fixed Input Text"
              outlined
              @keydown.enter="handleEnterKey"
              @keydown.ctrl.enter.prevent="handleSales"
            ></v-text-field>
          </v-col>
        </v-row>
      </v-container>
    </v-footer>

    <v-dialog v-model="showProductModal" max-width="600" @click:outside="showProductModal = false">
      <v-card>
        <v-card-title class="headline">
          Products List
        </v-card-title>
        <v-card-text>
          <v-text-field
            v-model="productFilter"
            label="Filter Products"
            outlined
            solo-inverted
            @input="filterProducts"
            ref="productFilterInput"
          ></v-text-field>
          <v-list style="max-height: 300px; overflow-y: auto;">
            <v-list-item v-for="(product, index) in filteredProductList" :key="index">
              <v-list-item-content>
                <v-list-item-title>{{ product.name + "  -   " + product.price }}</v-list-item-title>
                <v-list-item-subtitle>{{ product.description }}</v-list-item-subtitle>
              </v-list-item-content>
              <v-list-item-action>
                <v-btn @click="selectProduct(product.upc)">Select</v-btn>
              </v-list-item-action>
            </v-list-item>
          </v-list>
        </v-card-text>
        <v-card-actions>
          <v-btn @click="showProductModal = false">Close</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </v-app>
</template>

<script>
import axios from 'axios';

export default {
  data() {
    return {
      productFilter: '',
      inputText: '',
      salesTransactionNumber: null,
      tableData: [],
      quantity:'',
      upc:'',
      showProductModal: false,
      productList: [],
    };
  },
  computed:{
    tableHeaders() {
      return [
        { text: 'Product Name', value: 'name' },
        { text: 'Price', value: 'price' },
        { text: 'Quantity', value: 'quantity' },
        { text: 'Subtotal', value: 'subtotal' }, 
      ];
    },
    filteredProductList() {
      const filterText = this.productFilter.toLowerCase();
      return this.productList.filter(product =>
        product.name.toLowerCase().includes(filterText)
      );
    },
    totalSalesPrice() {
      return this.tableData.reduce((total, item) => total + item.subtotal, 0);
    },
  },
  created(){
    this.salesTransactionNumber = this.generateRandomKey();
    this.getSales();
  },
  watch: {
    showProductModal: 'showProductModalChanged',
  },
  methods: {
    async showProductModalChanged() {
      if (this.showProductModal) {
        await this.$nextTick();
        this.$refs.productFilterInput.focus();
      }
    },


    selectProduct(upc) {
      this.inputText = upc;
      this.showProductModal = false;
    },
    generateRandomKey() {
      const characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
      const length = 8;
      let result = '';
      for (let i = 0; i < length; i++) {
        const randomIndex = Math.floor(Math.random() * characters.length);
        result += characters.charAt(randomIndex);
      }
      return result;
    },
    async getSales(){
      const sales = await axios.get(`api/sales/${this.salesTransactionNumber}`);
      this.tableData = sales.data.map(item => ({
        ...item,
        subtotal: item.quantity * item.price,
      }));
      

      
    },
    async handleSales() {
      const sales = await axios.get(`api/setsales/${this.salesTransactionNumber}`);
      this.salesTransactionNumber = this.generateRandomKey();
      this.getSales();
    },
    async handleEnterKey() {
      if (this.inputText.includes('@')) {
        const parts = this.inputText.split('@');
        console.log('Split parts:', parts);
        this.quantity = parts[0];
        this.upc = parts[1];
      } else {
        this.upc = this.inputText;
        this.quantity = '1';
      }
      const st = await axios.post('api/isales', {
        quantity: this.quantity,
        upc: this.upc,
        orderID: this.salesTransactionNumber,
      });
      if (st.data === 'no_result') {
        const d = await axios.get('api/products');
        this.productList= d.data;
        this.showProductModal = true;
        console.log(this.productList);
      } else {
        this.inputText = '';
        this.getSales();
      }
    },
  },
};
</script>
