
---
## 📦 POS-CAFE-UTS API Documentation

Sistem REST API untuk POS (Point of Sales) Caffe.

Base URL: `https://pos.cansyell.com/api/`

---
### CATATAN: 
1. Pertama Regis lalu Login terlebih dahulu.
2. Simpan token login kedalam authentication bearer token.
3. Pastikan headernya ada Accept Application/JSON.
---
### 🔐 Authentication
#### Login
- **Endpoint:** `POST /auth/login`
- **Body Example:**
```json
{
  "email": "lala@gmail.com",
  "password": "123123123123"
}
```
#### Register
- **Endpoint:** `POST /auth/register`
- **Body Example:**
```json
{
    "name":"admin1",
    "email":"lala@gmail.com",
    "password": "123123123123",
    "role": "admin",
    "is_active": 1
}
```
---

### 📁 Categories

#### Get All Categories
- `GET /categories`

#### Get Category by ID
- `GET /categories/{id}`

#### Create Category
- `POST /categories`
- **Body:**
```json
{
  "name": "Dessert",
  "description": "Manis",
  "is_active": 1
}
```

#### Update Category
- `PUT /categories/{id}`
- **Body:**
```json
{
  "name": "Ice Cream",
  "description": "dingin",
  "is_active": 0
}
```

#### Delete Category
- `DELETE /categories/{id}`

---

### 📦 Products

#### Get All Products
- `GET /products`

#### Get Product by ID
- `GET /products/{id}`

#### Get Products by Category
- `GET /products/category/{category_id}`

#### Get Featured Products
- `GET /products/featured`

#### Create Product
- `POST /products`
- **Body:**
```json
{
  "category_id": 1,
  "name": "Kue Raya",
  "description": "kuih",
  "price": "12000.00",
  "is_active": 1,
  "is_featured": 1
}
```

#### Update Product
- `PUT /products/{id}`

#### Delete Product
- `DELETE /products/{id}`

---

### 🧾 Orders

#### Get All Orders
- `GET /orders`

#### Get Order by ID
- `GET /orders/{id}`

#### Get Orders by Status
- `GET /orders/status/{status}`

#### Create Order
- `POST /orders`
- **Body:**
```json
{
  "user_id": 1,
  "table": "Table 1",
  "order_type": "dine_in",
  "status": "pending",
  "subtotal": "97000.00",
  "tax": "9700.00",
  "discount": "0.00",
  "total": "106700.00",
  "notes": null
}
```

#### Update Order
- `PUT /orders/{id}`

#### Update Order Status
- `PATCH /orders/{id}/status`
- **Body:**
```json
{
  "status": "preparing"
}
```

#### Cancel Order
- `PATCH /orders/{id}/cancel`

---

### 🧾 Order Items

#### Get All Order Items
- `GET /order-items`

#### Get Order Items by Order ID
- `GET /order-items/order/{order_id}`

#### Get Order Item by ID
- `GET /order-items/{id}`

#### Create Order Item
- `POST /order-items`
- **Body:**
```json
{
  "order_id": 1,
  "product_id": 2,
  "quantity": 4,
  "unit_price": "120000.00",
  "subtotal": "480000.00",
  "notes": "oke"
}
```

#### Update Order Item
- `PUT /order-items/{id}`

#### Delete Order Item
- `DELETE /order-items/{id}`

---

