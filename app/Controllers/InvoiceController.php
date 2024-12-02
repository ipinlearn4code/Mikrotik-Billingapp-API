<?php
// app/Controllers/InvoiceController.php
namespace App\Controllers;

use App\Models\InvoiceModel;
use App\Models\SubscriptionModel;
use DateTime;
use CodeIgniter\I18n\Time;
use CodeIgniter\RESTful\ResourceController;

class InvoiceController extends ResourceController
{
    protected $modelName = 'App\Models\InvoiceModel';
    protected $format = 'json';

    // Get all invoices
    public function index()
    {
        $invoices = $this->model->findAll();
        return $this->respond(['data' => $invoices]);
    }

    // Get an invoice by ID
    public function show($id = null)
    {
        $invoice = $this->model->find($id);
        if (!$invoice) {
            return $this->failNotFound("Invoice with ID $id not found");
        }

        // Prepare the response data with subscription details
        $invoiceData = [
            'invoice_id' => $invoice['invoice_id'],
            'invoice_date' => $invoice['invoice_date'],
            'due_date' => $invoice['due_date'],
            'total_amount' => $invoice['total_amount'],
            'invoice_status' => $invoice['invoice_status'],
            'payment_method' => $invoice['payment_method'],
            'payment_date' => $invoice['payment_date'],
            'subscription_id' => $invoice['subscription_id'],  // Subscription info included
        ];

        return $this->respond(['data' => $invoiceData]);
    }

    // Create a new invoice
    public function create()
    {
        $data = $this->request->getJSON();

        // Check conditional fields (only required if status is "paid")
        if (!$this->model->validateConditionalFields((array) $data)) {
            return $this->failValidationErrors($this->model->validationErrors());
        }

        // Validate the data for creating invoice
        if (!$this->validate($this->model->createValidationRules)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        // Insert the data into the invoice table
        if ($this->model->insert($data)) {
            return $this->respondCreated(['message' => 'Invoice created successfully']);
        }
        return $this->failValidationErrors($this->model->errors());
    }

    // Update an existing invoice
    public function update($id = null)
    {
        $data = $this->request->getJSON();

        if (!$this->model->find($id)) {
            return $this->failNotFound("Invoice with ID $id not found");
        }

        // Check conditional fields (only required if status is changed to "paid")
        if (!$this->model->validateConditionalFields((array) $data, true)) {
            return $this->failValidationErrors($this->model->validationErrors());
        }

        // Validate the data for updating invoice
        if (!$this->validate($this->model->updateValidationRules)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        // Update the invoice
        if ($this->model->update($id, $data)) {
            return $this->respond(['message' => 'Invoice updated successfully']);
        }
        return $this->failValidationErrors($this->model->errors());
    }

    // Delete an invoice
    public function delete($id = null)
    {
        if (!$this->model->find($id)) {
            return $this->failNotFound("Invoice with ID $id not found");
        }

        $this->model->delete($id);
        return $this->respondDeleted(['message' => 'Invoice deleted successfully']);
    }
    public function generateAutoInvoices()
    {
        // Get the current date and time
        $currentDate = new Time('now');

        // Retrieve all active subscriptions
        $subscriptionModel = new SubscriptionModel();
        $subscriptions = $subscriptionModel->where('status', 'active')->findAll();

        // Store created invoices to avoid duplicates
        $createdInvoices = [];

        foreach ($subscriptions as $subscription) {
            // Convert subscription end date to a Time object
            $endDate = new Time($subscription['end_date']);

            // Calculate the difference between current date and subscription's end date
            $dateDifference = $currentDate->diff($endDate);
            $diffInDays = $dateDifference->days; // Get the number of days difference

            // Check if the subscription's end date is within 2 days
            if ($diffInDays <= 2) {
                // Check if an invoice already exists for this subscription
                $existingInvoice = $this->model->where('subscription_id', $subscription['subscription_id'])->first();

                if (!$existingInvoice) {
                    // If no invoice exists, generate a new invoice
                    $invoiceData = [
                        'subscription_id' => $subscription['subscription_id'],
                        'invoice_date' => $currentDate->toDateString(),  // Today's date
                        'due_date' => $currentDate->addDays(2)->toDateString(),  // Due date in 2 days
                        'total_amount' => $subscription['price'],  // Assuming price is the total amount
                        'invoice_status' => 'pending',  // Default status
                    ];

                    // Create the new invoice
                    if ($this->model->insert($invoiceData)) {
                        $createdInvoices[] = $invoiceData;
                    } else {
                        return $this->failServerError("Error creating invoice for subscription ID {$subscription['subscription_id']}");
                    }
                }
            }
        }

        // Return the response
        if (count($createdInvoices) > 0) {
            return $this->respond([
                'message' => 'Invoices generated successfully for subscriptions with end dates within 2 days.',
                'data' => $createdInvoices
            ]);
        } else {
            return $this->respond([
                'message' => 'No invoices were generated. All subscriptions have end dates more than 2 days away or invoices already exist.',
                'data' => []
            ]);
        }
    }

}
