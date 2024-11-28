# Project: Mikronet API

## End-point: register
### Method: POST
>```
>localhost:8080/auth/register
>```
### Body (**raw**)

```json
{
    "username": "admin123",
    "password": "admin123" 
}
```

### Response: 201
```json
{
    "message": "User registered successfully",
    "user_id": 4
}
```


⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃

## End-point: login
### Method: POST
>```
>localhost:8080/auth/login
>```
### Body (**raw**)

```json
{
    "username": "admin123",
    "password": "admin123" 
}
```

### Response: 200
```json
{
    "message": "Login successful",
    "token": "eyJ1c2VyX2lkIjoiMyIsInVzZXJuYW1lIjoiYWRtaW4xMjMiLCJyb2xlIjoiYWRtaW4iLCJpc3N1ZWRfYXQiOjE3MzI4MzQ2NzcsImV4cGlyZXNfYXQiOjE3MzI4MzgyNzd9.8e2495755fcfbc558716aedf3d6f1327166024acf4154cdcf7093ea66ac8b176"
}
```

### Response: 401
```json
{
    "status": 401,
    "error": 401,
    "messages": {
        "error": "Invalid login credentials"
    }
}
```


⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃

## End-point: logout
### Method: POST
>```
>localhost:8080/auth/logout
>```
### Body (**raw**)

```json
{
    "username": "admin123",
    "password": "admin123"
}
```

### 🔑 Authentication inherit

|Param|value|Type|
|---|---|---|



⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃

## End-point: Client Dashboard
### Method: GET
>```
>localhost:8080/client
>```
### Headers

|Content-Type|Value|
|---|---|
|Authorization|Bearer {{auth_token}}|


### Body (**raw**)

```json

```

### 🔑 Authentication inherit

|Param|value|Type|
|---|---|---|


### Response: 200
```json
{
    "message": "Clients retrieved successfully",
    "data": [
        {
            "client_id": "1",
            "name": "janaaa",
            "phone_number": "0123456789",
            "address": "Some Address",
            "ppp_secret_name": "secret123"
        },
        {
            "client_id": "2",
            "name": "jane_smith",
            "phone_number": "08198765432",
            "address": "Jl. Sudirman No.2",
            "ppp_secret_name": "xyz789"
        },
        {
            "client_id": "4",
            "name": "ipincode",
            "phone_number": "12233333",
            "address": "any",
            "ppp_secret_name": "sjsjsj"
        }
    ]
}
```


⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃

## End-point: Client Details
### Method: GET
>```
>localhost:8080/client/1
>```
### Headers

|Content-Type|Value|
|---|---|
|Authorization|Bearer {{auth_token}}|


### Body (**raw**)

```json

```

### 🔑 Authentication inherit

|Param|value|Type|
|---|---|---|


### Response: 200
```json
{
    "data": {
        "client": {
            "client_id": "1",
            "name": "janaaa",
            "phone_number": "0123456789",
            "address": "Some Address",
            "ppp_secret_name": "secret123",
            "pppoe_status": "Enabled"
        },
        "subscriptions": [
            {
                "subscription_id": "1",
                "start_date": "2024-01-01",
                "end_date": "2024-01-31",
                "status": "active",
                "invoices": [
                    {
                        "invoice_id": "1",
                        "invoice_date": "2024-01-01",
                        "due_date": "2024-01-10",
                        "total_amount": "100000.00",
                        "invoice_status": "paid",
                        "payments": [
                            {
                                "payment_status": "success",
                                "payment_date": "2024-01-05",
                                "payment_method": ""
                            },
                            {
                                "payment_status": "success",
                                "payment_date": "2024-02-05",
                                "payment_method": "transfer"
                            }
                        ]
                    },
                    {
                        "invoice_id": "2",
                        "invoice_date": "2024-02-01",
                        "due_date": "2024-02-10",
                        "total_amount": "100000.00",
                        "invoice_status": "pending",
                        "payments": [
                            {
                                "payment_status": "success",
                                "payment_date": "2024-01-05",
                                "payment_method": ""
                            },
                            {
                                "payment_status": "success",
                                "payment_date": "2024-02-05",
                                "payment_method": "transfer"
                            }
                        ]
                    }
                ]
            },
            {
                "subscription_id": "2",
                "start_date": "2024-01-01",
                "end_date": "2024-12-31",
                "status": "active",
                "invoices": [
                    {
                        "invoice_id": "3",
                        "invoice_date": "2024-01-01",
                        "due_date": "2024-01-10",
                        "total_amount": "250000.00",
                        "invoice_status": "paid",
                        "payments": [
                            {
                                "payment_status": "success",
                                "payment_date": "2024-01-05",
                                "payment_method": "cash"
                            }
                        ]
                    }
                ]
            }
        ]
    }
}
```


⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃

## End-point: Client Register
### Method: POST
>```
>localhost:8080/client
>```
### Headers

|Content-Type|Value|
|---|---|
|Authorization|Bearer {{auth_token}}|


### Body (**raw**)

```json
{
  "name": "Johnoooooooooooooooo",
  "phone_number": "0123456789",
  "address": "Some Address",
  "ppp_secret_name": "secret1234",
  "password": "password123"
}
```

### 🔑 Authentication inherit

|Param|value|Type|
|---|---|---|


### Response: 201
```json
{
    "message": "Client created successfully",
    "data": {
        "name": "try",
        "phone_number": "0123456789",
        "address": "Some Address",
        "ppp_secret_name": "try"
    }
}
```


⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃

## End-point: Client Update
### Method: PUT
>```
>localhost:8080/client/1
>```
### Headers

|Content-Type|Value|
|---|---|
|Authorization|Bearer {{auth_token}}|


### Body (**raw**)

```json
{
  "name": "janaaa",
  "phone_number": "0123456789",
  "address": "Some Address",
  "password": "password123",
  "profile": "default"
}
```

### 🔑 Authentication inherit

|Param|value|Type|
|---|---|---|


### Response: 200
```json
{
    "message": "Client with ID 4 updated successfully",
    "data": {
        "name": "jani",
        "phone_number": "0123456789",
        "address": "Some Address"
    }
}
```


⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃

## End-point: Client Delete
### Method: DELETE
>```
>localhost:8080/client/5
>```
### Headers

|Content-Type|Value|
|---|---|
|Authorization|Bearer {{auth_token}}|


### Body (**raw**)

```json

```

### 🔑 Authentication inherit

|Param|value|Type|
|---|---|---|


### Response: 200
```json
{
    "message": "Client with ID 7 deleted successfully"
}
```


⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃

## End-point: Client disable/enable Connection
### Method: POST
>```
>localhost:8080/client/1/connection/enable
>```
### Headers

|Content-Type|Value|
|---|---|
|Authorization|Bearer {{auth_token}}|


### Body (**raw**)

```json

```

### 🔑 Authentication inherit

|Param|value|Type|
|---|---|---|


### Response: 200
```json
{
    "message": "PPP Secret 'secret123' disabled successfully."
}
```


⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃

## End-point: Client Search
### Method: GET
>```
>localhost:8080/client/search?query=some
>```
### Headers

|Content-Type|Value|
|---|---|
|Authorization|Bearer {{auth_token}}|


### Body (**raw**)

```json

```

### Query Params

|Param|value|
|---|---|
|query|some|


### 🔑 Authentication inherit

|Param|value|Type|
|---|---|---|


### Response: 200
```json
{
    "message": "Clients retrieved successfully",
    "data": [
        {
            "client_id": "1",
            "name": "janaaa",
            "phone_number": "0123456789",
            "address": "Some Address",
            "ppp_secret_name": "secret123"
        },
        {
            "client_id": "4",
            "name": "jani",
            "phone_number": "0123456789",
            "address": "Some Address",
            "ppp_secret_name": "secret_name"
        }
    ]
}
```


⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃
_________________________________________________
Powered By: [postman-to-markdown](https://github.com/bautistaj/postman-to-markdown/)
