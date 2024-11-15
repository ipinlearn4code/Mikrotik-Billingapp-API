MikroNet Billing API
MikroNet Billing API is a RESTful API built with CodeIgniter 4 to manage client subscriptions, invoices, and payments for an internet provider. This API integrates with the MikroTik API for managing PPP Secrets, allowing easy administration of users, subscriptions, and billing.

Features
Client Management: Create, update, delete, and retrieve client details.
Subscription Management: Track client subscriptions with details about start/end dates and status.
Invoice Management: Automatically generate invoices 4 days before the subscription end date.
Payment Tracking: Monitor payment status, date, and method for each invoice.
Integration with MikroTik API: Manage PPP Secrets directly from the billing app.
Authorization: Uses token-based authentication for secure API access.
Technologies Used
CodeIgniter 4: PHP framework used for API development.
MikroTik API: For PPP Secret management.
MySQL: Database to store clients, subscriptions, invoices, and payment information.
JWT (optional): Simple token-based authorization for securing endpoints.
Getting Started
Prerequisites
PHP 7.4+
Composer
MySQL
MikroTik Router (for PPP Secret management)
Installation
Clone the repository:

bash
Salin kode
git clone https://github.com/your-username/mikronet-billing-api.git
cd mikronet-billing-api
Install dependencies:

bash
Salin kode
composer install
Copy the .env.example file to .env and configure your database settings:

bash
Salin kode
cp .env.example .env
Migrate and seed the database:

bash
Salin kode
php spark migrate
Run the development server:

bash
Salin kode
php spark serve
Set up a cron job for daily invoice generation (optional):

bash
Salin kode
crontab -e
Add the following line to run invoice generation at midnight daily:

bash
Salin kode
0 0 * * * /usr/bin/php /path/to/your/project/spark billing:generate_invoices
Configuration
Update the MikroTik API configuration in app/Config/MikroTikConfig.php:

php
Salin kode
public $apiUrl = 'http://your-mikrotik-api-server/ppp-secret';
public $apiKey = 'your-mikrotik-api-key';
API Endpoints
Authentication
Method	Endpoint	Description
POST	/auth/register	Register a new user
POST	/auth/login	Login and get a token
POST	/auth/logout	Logout a user (optional)
Client Management
Method	Endpoint	Description
GET	/client	Retrieve all clients
GET	/client/{id}	Retrieve a specific client
POST	/client/register	Register a new client
PUT	/client/update/{id}	Update client details
DELETE	/client/delete/{id}	Delete a client
GET	/client/search?query=	Search clients by name, phone number, or PPP secret name
Subscription and Invoice Management
Method	Endpoint	Description
GET	/client/{id}/details	Retrieve client details including subscriptions, invoices, and payments
GET	/invoices/generate	Generate invoices for expiring subscriptions (usually set up as a cron job)
Usage Examples
Register a New Client
http
Salin kode
POST /client/register
Content-Type: application/json

{
  "name": "John Doe",
  "phone_number": "08123456789",
  "address": "Jl. Merdeka No.1",
  "ppp_secret_name": "abc123",
  "password": "securepassword"
}
Get Client Details with Nested Subscriptions, Invoices, and Payments
http
Salin kode
GET /client/{id}/details
Authorization: Bearer <your_token>
Response:

json
Salin kode
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
Search for a Client
http
Salin kode
GET /client/search?query=john
Authorization: Bearer <your_token>
Response:

json
Salin kode
{
    "message": "Clients retrieved successfully",
    "data": [
        {
            "client_id": "1",
            "name": "John Doe",
            "phone_number": "08123456789",
            "address": "Jl. Merdeka No.1",
            "ppp_secret_name": "abc123"
        }
    ]
}
Additional Notes
Automatic Invoice Generation: Invoices are generated automatically 4 days before a subscription’s end date. This feature can be run manually via the /invoices/generate endpoint or automated with a cron job.
Token-Based Authentication: Authorization headers must be included with requests to protected endpoints. Tokens are base64 encoded but do not use JWT for simplicity.
Future Enhancements
Implement JWT for more secure token handling.
Add roles and permissions for finer-grained access control.
Enhance error handling and logging for better maintainability.
