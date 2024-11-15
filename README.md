
## MikroNet API Documentation

Base URL: `http://your-api-server`


### 1. **Authentication Endpoints** (NOT USED YET/UNDONE)

#### **Admin Login**

- **Endpoint**: `POST /admin/login`
- **Description**: Authenticates admin users and returns an access token if successful.
- **Request Body**:
  ```json
  {
    "username": "admin",
    "password": "securepassword"
  }
  ```
- **Response**:
  - **Success**: Returns an access token.
    ```json
    {
      "message": "Login successful",
      "token": "your_access_token"
    }
    ```
  - **Failure**: Authentication error.

#### **Admin Logout** (NOT USED YET/UNDONE)

- **Endpoint**: `POST /admin/logout`
- **Description**: Invalidates the admin’s access token.

---

### 2. **User Management Endpoints**

#### **Create New User**

- **Endpoint**: `POST /users/create`
- **Description**: Creates a new user by interacting with both the database and the MikroTik API to create a PPP Secret.
- **Request Body**:
  ```json
  {
    "username": "john_doe",
    "phone_number": "08123456789",
    "address": "Jl. Merdeka No.1",
    "ppp_secret_name": "john_secret",
    "password": "securepassword"
  }
  ```
- **Response**:
  - **Success**: User created successfully.
    ```json
    {
      "message": "User created successfully",
      "data": {
        "username": "john_doe",
        "phone_number": "08123456789",
        "address": "Jl. Merdeka No.1",
        "ppp_secret_name": "john_secret"
      }
    }
    ```
  - **Failure**: Error creating the PPP Secret on MikroTik or database error.

#### **Get All Users**

- **Endpoint**: `GET /users`
- **Description**: Retrieves a list of all users.
- **Response**:
  ```json
  {
    "data": [
      {
        "user_id": 1,
        "username": "john_doe",
        "phone_number": "08123456789",
        "address": "Jl. Merdeka No.1",
        "ppp_secret_name": "john_secret"
      },
      ...
    ]
  }
  ```

#### **Search Users**

- **Endpoint**: `GET /users/search?query={keyword}`
- **Description**: Searches for users by keyword (in `username`, `email`, or `phone_number`).
- **Response**:
  ```json
  {
    "data": [
      {
        "user_id": 1,
        "username": "john_doe",
        "phone_number": "08123456789",
        "address": "Jl. Merdeka No.1",
        "ppp_secret_name": "john_secret"
      },
      ...
    ]
  }
  ```

#### **Get User Details (with Subscriptions, Invoices, Payments)**

- **Endpoint**: `GET /users/{id}/details`
- **Description**: Retrieves detailed user information, including associated subscriptions, invoices, and payments.
- **Response**:
  ```json
  {
    "data": {
      "user": {
        "user_id": 1,
        "username": "john_doe",
        "phone_number": "08123456789",
        "address": "Jl. Merdeka No.1",
        "ppp_secret_name": "john_secret"
      },
      "subscriptions": [
        {
          "subscription_id": 1,
          "plan_id": 1,
          "start_date": "2024-01-01",
          "end_date": "2024-01-31",
          "status": "active"
        },
        ...
      ],
      "invoices": [
        {
          "invoice_id": 1,
          "subscription_id": 1,
          "invoice_date": "2024-01-01",
          "due_date": "2024-01-10",
          "total_amount": 100000.00,
          "invoice_status": "paid"
        },
        ...
      ],
      "payments": [
        {
          "payment_id": 1,
          "subscription_id": 1,
          "payment_date": "2024-01-05",
          "amount": 100000.00,
          "payment_method": "credit_card",
          "payment_status": "success"
        },
        ...
      ]
    }
  }
  ```

#### **Enable/Disable User by PPP Secret**

- **Endpoint**: `POST /users/{id}/status/{action}`
  - `{action}` can be either `enable` or `disable`.
- **Description**: Enables or disables a user’s PPP Secret on MikroTik.
- **Response**:
  - **Success**:
    ```json
    {
      "message": "PPP Secret 'john_secret' enabled successfully."
    }
    ```
  - **Failure**: Error enabling/disabling the PPP Secret.

---

### 3. **Subscription Management Endpoints**

#### **Get User Subscriptions**

- **Endpoint**: `GET /users/{id}/subscriptions`
- **Description**: Retrieves subscriptions associated with the specified user.
- **Response**:
  ```json
  {
    "data": [
      {
        "subscription_id": 1,
        "plan_id": 1,
        "start_date": "2024-01-01",
        "end_date": "2024-01-31",
        "status": "active"
      },
      ...
    ]
  }
  ```

---

### 4. **Invoice Management Endpoints**

#### **Get User Invoices**

- **Endpoint**: `GET /users/{id}/invoices`
- **Description**: Retrieves invoices associated with the specified user’s subscriptions.
- **Response**:
  ```json
  {
    "data": [
      {
        "invoice_id": 1,
        "subscription_id": 1,
        "invoice_date": "2024-01-01",
        "due_date": "2024-01-10",
        "total_amount": 100000.00,
        "invoice_status": "paid"
      },
      ...
    ]
  }
  ```

---

### 5. **Payment Management Endpoints**

#### **Get User Payments**

- **Endpoint**: `GET /users/{id}/payments`
- **Description**: Retrieves payments associated with the specified user’s invoices.
- **Response**:
  ```json
  {
    "data": [
      {
        "payment_id": 1,
        "subscription_id": 1,
        "payment_date": "2024-01-05",
        "amount": 100000.00,
        "payment_method": "credit_card",
        "payment_status": "success"
      },
      ...
    ]
  }
  ```

---

## Summary

The **MikroNet API** provides endpoints for managing users, subscriptions, invoices, and payments within a billing application context. The API interacts with both a billing database and a MikroTik router management API to create, enable, or disable PPP Secrets. Only admins can access this API, with authentication required for all endpoints.

### Important Notes

- **Admin Authentication**: Admins must be authenticated to access these endpoints.
- **MikroTik Management API**: This API interacts with MikroTik’s PPP Secret management to enable or disable users based on `ppp_secret_name`.
- **Data Handling**: All user-specific data, including subscriptions, invoices, and payments, is consolidated in a single endpoint for easy retrieval.
