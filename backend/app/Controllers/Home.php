<?php

namespace App\Controllers;
use CodeIgniter\RESTful\ResourceController;
use App\Models\SalesModel;
use App\Models\ProductModel;
use App\Models\AuditModel;
use CodeIgniter\API\ResponseTrait;

class Home extends ResourceController
{
  use ResponseTrait;

    public function index()
    {
        return view('welcome_message');
    }
    public function updateProduct($id)
    {
      $data = $this->request->getJSON(); 
       $model = new ProductModel();
       $model->update($id, $data);

       return $this->respond(['status' => 'success', 'message' => 'Product updated successfully']);
    }
    public function newproduct()
    {
      $data = $this->request->getJSON();
        $model = new ProductModel();
        $model->insert($data);
    }
    public function getSales($id)
    {
      $sales = new SalesModel();
      $data = $sales->select('name as name, sales.price as price, sales.quantity as quantity')->join('products', 'sales.productID=products.id')->where('orderID', $id)->findAll();
      return $this->respond($data, 200);
    }
    public function audit($id)
    {
      $audit = new AuditModel();
      $data = $audit->select('products.upc as upc, products.name as name, products.description as description, audit.oldQuantity as oldQuantity, audit.quantity as quantity, audit.type as type')->join('products', 'audit.productID=products.id')->where('products.upc', $id)->findAll();
      return $this->respond($data,200);
    }
    public function updateQuantity()
    {
      $upc = $this->request->getVar('upc');
      $quantity = $this->request->getVar('quantity');
      $product = new ProductModel();
      $audit = new AuditModel();
      $pr = $product->where('upc', $upc)->first();
      if($pr){
        $nq = $pr['quantity'] + $quantity;
        $product->set('quantity', $nq)->where('upc', $upc)->update();
        $data = [
          'productID' =>$pr['id'],
          'oldQuantity' =>$pr['quantity'],
          'quantity' => $quantity,
          'type' => 'inbound'
        ];
        $audit->save($data);
      }

    }
    public function getProducts()
    {
      $product  = new ProductModel();
      $data = $product->findAll();
      return   $this->respond($data, 200);
    }
    public function setsales($id)
    {
      $sales =new SalesModel();
      $product  = new ProductModel();
      $audit = new AuditModel();
      // $sales->set('status', 'transacted')->where('orderID', $id)->update();
      $d = $sales->where('orderID', $id)->findAll();
      foreach ($d as $v) {
        $pid = $v['productID'];
        $quantity = $v['quantity'];
        $h = $product->where('id', $pid)->first();
        $sets = [
          'quantity' => $h['quantity'] - $quantity
        ];
        $data = [
          'productID' => $pid,
          'oldQuantity' => $h['quantity'],
          'quantity' => $quantity,
          'type' =>'sales'
        ];
        $product->set($sets)->where('id', $pid)->update();
        $audit->save($data);
      }
      $sales->set('status', 'transacted')->where('orderID', $id)->update();
    }
    public function isales()
    {
      $upc = $this->request->getVar('upc');
      $qty = $this->request->getVar('quantity');
      $orderID = $this->request->getVar('orderID');
      $product = new ProductModel();
      $pr = $product->where('upc', $upc)->first();
      $sales = new SalesModel();
      if($pr){
        $data = [
          'orderID' =>$orderID,
          'productID' => $pr['id'],
          'price' => $pr['price'],
          'quantity' =>$qty,
          'status' => 'added'
        ];
        $d = $sales->save($data);
        $message = "added";
      }else{
        $message = "No product found";
      }
      //
      return $this->respond($message, 200);
    }
}
