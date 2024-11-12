<?php
// app/Models/InvoiceModel.php
namespace App\Models;

use CodeIgniter\Model;

class InvoiceModel extends Model
{
    protected $table = 'invoice';
    protected $primaryKey = 'invoice_id';
    protected $allowedFields = ['subscription_id', 'invoice_date', 'due_date', 'total_amount', 'invoice_status'];
}
