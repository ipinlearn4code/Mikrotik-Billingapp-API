# MikroNet API - Client and Billing Management System

## Table of Contents
- [Introduction](#introduction)
- [Features](#features)
- [Project Structure](#project-structure)
- [Requirements](#requirements)
- [Installation](#installation)
- [Configuration](#configuration)
- [API Endpoints](#api-endpoints)
  - [Authentication](#authentication)
  - [Client Management](#client-management)
  - [Subscription and Billing](#subscription-and-billing)
- [Automatic Invoice Generation](#automatic-invoice-generation)
- [Usage Example](#usage-example)
- [Contributing](#contributing)
- [License](#license)

---

## Introduction

MikroNet API is a client and billing management system specifically designed for internet providers. It integrates with MikroTik APIs to manage client PPP secrets and includes billing functionalities. Invoices are generated automatically 4 days before the subscription end date, and payments can be tracked through the system.

## Features

- **Client Management**: Create, update, delete, and retrieve client information.
- **Subscription Tracking**: Manage client subscriptions with start and end dates.
- **Automatic Invoicing**: Generate invoices 4 days before the subscription end date.
- **Payment Tracking**: Record and view payment history for each invoice.
- **Integration with MikroTik API**: Enable or disable client access via MikroTik API based on PPP secret.

## Project Structure

```
project/
├── app/
│   ├── Commands/
│   │   └── GenerateInvoices.php      # CLI command to automate invoice creation
│   ├── Config/
│   │   └── MikroTikConfig.php        # MikroTik API configuration
│   ├── Controllers/
│   │   ├── AuthController.php        # Handles user authentication
│   │   ├── ClientController.php      # Manages clients and client details
│   │   └── InvoiceController.php     # Handles invoice-related operations
│   ├── Models/
│   │   ├── ClientModel.php           # Client data model
│   │   ├── SubscriptionModel.php     # Subscription data model
│   │   ├── InvoiceModel.php          # Invoice data model
│   │   └── PaymentModel.php          # Payment data model
└── README.md                         # Project documentation
```

## Requirements

- PHP 7.4 or higher
- CodeIgniter 4.x
- MySQL/MariaDB
- Composer
- MikroTik router with API enabled

## Installation

1. Clone this repository:
   ```bash
   git clone https://github.com/yourusername/mikronet-api.git
   cd mikronet-api
   ```

2. Install dependencies:
   ```bash
   composer install
   ```

3. Set up environment variables:
   - Copy `.env.example` to `.env` and update the database configuration.
   - Configure the MikroTik API key and URL in `app/Config/MikroTikConfig.php`.

4. Run migrations to create the necessary database tables:
   ```bash
   php spark migrate
   ```

## Configuration

Configure MikroTik API access in `app/Config/MikroTikConfig.php`:

```php
<?php

namespace App\Config;

class MikroTikConfig
{
    public $apiUrl = 'http://your-mikrotik-api-server';
    public $apiKey = 'your-api-key';
}
```

## API Endpoints

### Authentication

- **Register**: `POST /auth/register`
  - Registers a new user with a username and password.
- **Login**: `POST /auth/login`
  - Authenticates the user and returns a token for protected routes.
- **Logout**: `POST /auth/logout`
  - Logs out the user.

### Client Management

- **Get All Clients**: `GET /client`
  - Retrieves a list of all clients.
- **Get Client Details**: `GET /client/{id}/details`
  - Retrieves details of a specific client, including subscriptions, invoices, and payments.
- **Create Client**: `POST /client/register`
  - Creates a new client and registers their PPP secret in MikroTik.
- **Update Client**: `PUT /client/update/{id}`
  - Updates a client's information.
- **Delete Client**: `DELETE /client/delete/{id}`
  - Deletes a client and removes their PPP secret in MikroTik.
- **Toggle Client Status**: `POST /client/{id}/{enable|disable}`
  - Enables or disables the client’s access on MikroTik by toggling their PPP secret.

### Subscription and Billing

- **Generate Invoices for Expiring Subscriptions**: `GET /invoices/generate`
  - Manually triggers the invoice generation process for subscriptions ending in 4 days.
  
### Automatic Invoice Generation

Invoices are automatically generated 4 days before the end of a subscription. To automate this, set up a cron job to run daily, triggering the `GenerateInvoices` command:

```bash
0 0 * * * /usr/bin/php /path/to/project/spark billing:generate_invoices
```

## Usage Example

### Example JSON Responses

#### Retrieve All Clients

```json
{
  "message": "Clients retrieved successfully",
  "data": [
    {
      "client_id": 1,
      "name": "John Doe",
      "phone_number": "08123456789",
      "address": "Jl. Merdeka No.1",
      "ppp_secret_name": "abc123"
    },
    ...
  ]
}
```

#### Retrieve Client Details

```json
{
  "data": {
    "client": {
      "client_id": 1,
      "name": "John Doe",
      "phone_number": "08123456789",
      "address": "Jl. Merdeka No.1",
      "ppp_secret_name": "abc123"
    },
    "subscriptions": [
      {
        "subscription_id": 1,
        "start_date": "2024-01-01",
        "end_date": "2024-12-31",
        "status": "active",
        "invoices": [
          {
            "invoice_id": 1,
            "invoice_date": "2024-01-01",
            "due_date": "2024-01-10",
            "total_amount": 100000.00,
            "invoice_status": "pending",
            "payments": [
              {
                "payment_status": "success",
                "payment_date": "2024-01-05",
                "payment_method": "credit_card"
              }
            ]
          }
        ]
      }
    ]
  }
}
```

This README provides an organized and comprehensive overview of your project, covering all implemented features and setup instructions. You can expand on it as the project grows.
