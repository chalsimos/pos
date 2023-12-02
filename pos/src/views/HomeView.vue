<template>
  <v-app>
    <!-- Table above the input text -->
    <v-row>
      <v-col>
        <div v-if="salesTransactionNumber">
              Sales Transaction Number: {{ salesTransactionNumber }}
        </div>
        <v-simple-table>
          <template v-slot:default>
            <thead>
              <tr>
                <th class="text-left">Product Name</th>
                <th class="text-left">Price</th>
                <th class="text-left">Quantity</th>
                <th class="text-left">Subtotal</th>
                

                <!-- Add more headers as needed -->
              </tr>
            </thead>
            <tbody>
              <tr v-for="(item, index) in tableData" :key="index">
                <td>{{ item.name }}</td>
                <td>{{ item.price }}</td>
                <td>{{ item.quantity }}</td>
                <td>{{ item.quantity * item.price }}</td>
                <!-- Add more columns as needed -->
              </tr>
            </tbody>
          </template>
        </v-simple-table>
      </v-col>
    </v-row>

    <!-- Fixed input text at the bottom -->
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
  </v-app>
</template>

<script>
import axios from 'axios';

export default {
  data() {
    return {
      inputText: '',
      salesTransactionNumber: null,
      tableData: [],
      quantity:'',
      upc:'',
    };
  },
  created(){
    this.salesTransactionNumber = this.generateRandomKey();
  },
  methods: {
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
      this.tableData = sales.data;
    },
    async handleSales() {
      const sales = await axios.get(`api/setsales/${this.salesTransactionNumber}`);
      this.salesTransactionNumber = this.generateRandomKey();
      this.getSales();
      // this.tableData = sales.data;
      // console.log('afsdg');
    },
    async handleEnterKey() {
      if (this.inputText.includes('@')) {
        const parts = this.inputText.split('@');
        console.log('Split parts:', parts);
        this.quantity = parts[0];
        this.upc =parts[1];
      }else{
        this.upc = this.inputText;
        this.quantity = '1';
      }
      const st = await axios.post('api/isales',{
        quantity: this.quantity,
        upc: this.upc,
        orderID: this.salesTransactionNumber,
      });
      this.inputText='';
      this.getSales();
    },
  },
};
</script>
