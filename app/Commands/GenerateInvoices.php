<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\SubscriptionModel;
use App\Models\InvoiceModel;

class GenerateInvoices extends BaseCommand
{
    protected $group       = 'Billing';
    protected $name        = 'billing:generate_invoices';
    protected $description = 'Generates invoices for subscriptions ending in 4 days.';

    public function run(array $params)
    {
        $subscriptionModel = new SubscriptionModel();
        $invoiceModel = new InvoiceModel();

        $targetDate = date('Y-m-d', strtotime('+4 days'));
        $expiringSubscriptions = $subscriptionModel
            ->where('end_date', $targetDate)
            ->where('status', 'active')
            ->findAll();

        foreach ($expiringSubscriptions as $subscription) {
            $existingInvoice = $invoiceModel
                ->where('subscription_id', $subscription['subscription_id'])
                ->where('invoice_date', date('Y-m-d'))
                ->first();

            if (!$existingInvoice) {
                $invoiceData = [
                    'subscription_id' => $subscription['subscription_id'],
                    'invoice_date' => date('Y-m-d'),
                    'due_date' => date('Y-m-d', strtotime('+7 days')),
                    'total_amount' => $subscription['price'],
                    'invoice_status' => 'pending'
                ];

                $invoiceModel->insert($invoiceData);
                CLI::write("Invoice generated for subscription ID {$subscription['subscription_id']}", 'green');
            }
        }
        CLI::write("Invoice generation completed.", 'yellow');
    }
}
