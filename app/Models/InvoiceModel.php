<?php
// app/Models/InvoiceModel.php
namespace App\Models;

use CodeIgniter\Model;

class InvoiceModel extends Model
{
    protected $table = 'invoice';
    protected $primaryKey = 'invoice_id';
    protected $allowedFields = [
        'subscription_id', 'invoice_date', 'due_date', 'total_amount', 'invoice_status', 'payment_method', 'payment_date' 
    ];

    // Validation rules for creating an invoice
    protected $createValidationRules = [
        'invoice_date' => 'required|valid_date',
        'due_date' => 'required|valid_date',
        'total_amount' => 'required|decimal',
        'invoice_status' => 'required|in_list[pending,paid]',
        'subscription_id' => 'required|is_not_unique[subscription.subscription_id]', // Foreign key validation
        'payment_method' => 'permit_empty|string',  // Conditional
        'payment_date' => 'permit_empty|valid_date', // Conditional
    ];

    // Validation rules for updating an invoice
    protected $updateValidationRules = [
        'invoice_status' => 'in_list[pending,paid,overdue]',
        'subscription_id' => 'is_null', // Foreign key validation
        'payment_method' => 'permit_empty|string',  // Conditional
        'payment_date' => 'permit_empty|valid_date', // Conditional
    ];

    // Custom validation method to handle payment fields based on invoice status
    public function validateConditionalFields($data, $isUpdate = false)
    {
        // If status is "paid", make sure payment fields are filled
        if ($data['invoice_status'] === 'paid') {
            if (empty($data['payment_method'])) {
                $this->validationErrors['payment_method'] = 'Payment method is required when the invoice status is "paid".';
            }

            if (empty($data['payment_date'])) {
                $this->validationErrors['payment_date'] = 'Payment date is required when the invoice status is "paid".';
            }
        }

        return empty($this->validationErrors);
    }

}
