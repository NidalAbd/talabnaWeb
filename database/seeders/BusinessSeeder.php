<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Investment;
use App\Models\InvestmentPayment;
use App\Models\BusinessExpense;
use App\Models\BusinessRevenue;
use App\Models\BusinessBudget;
use App\Models\User;
use Carbon\Carbon;

class BusinessSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create sample investments
        $investments = [
            [
                'investor_name' => 'Ahmed Al-Rashid',
                'investor_email' => 'ahmed@example.com',
                'investment_amount' => 50000,
                'currency' => 'USD',
                'investment_type' => 'equity',
                'investment_date' => '2024-01-15',
                'expected_return' => 75000,
                'return_percentage' => 50.00,
                'status' => 'active',
                'purpose' => 'Development and Marketing',
                'notes' => 'Initial investment for platform development',
                'total_paid' => 15000,
                'remaining_amount' => 35000,
                'next_payment_date' => '2024-12-15'
            ],
            [
                'investor_name' => 'Sarah Johnson',
                'investor_email' => 'sarah@example.com',
                'investment_amount' => 30000,
                'currency' => 'USD',
                'investment_type' => 'loan',
                'investment_date' => '2024-02-01',
                'expected_return' => 36000,
                'return_percentage' => 20.00,
                'status' => 'active',
                'purpose' => 'Server Infrastructure',
                'notes' => 'Loan for server upgrades and maintenance',
                'total_paid' => 12000,
                'remaining_amount' => 18000,
                'next_payment_date' => '2024-11-01'
            ],
            [
                'investor_name' => 'Mohammed Al-Zahra',
                'investor_email' => 'mohammed@example.com',
                'investment_amount' => 25000,
                'currency' => 'USD',
                'investment_type' => 'grant',
                'investment_date' => '2024-03-10',
                'expected_return' => 25000,
                'return_percentage' => 0.00,
                'status' => 'active',
                'purpose' => 'Research and Development',
                'notes' => 'Grant for R&D activities',
                'total_paid' => 0,
                'remaining_amount' => 25000,
                'next_payment_date' => null
            ]
        ];

        foreach ($investments as $investmentData) {
            Investment::create($investmentData);
        }

        // Create sample investment payments
        $payments = [
            [
                'investment_id' => 1,
                'payment_amount' => 10000,
                'payment_date' => '2024-06-15',
                'payment_type' => 'return',
                'payment_method' => 'bank_transfer',
                'reference_number' => 'PAY001',
                'status' => 'completed'
            ],
            [
                'investment_id' => 1,
                'payment_amount' => 5000,
                'payment_date' => '2024-09-15',
                'payment_type' => 'return',
                'payment_method' => 'bank_transfer',
                'reference_number' => 'PAY002',
                'status' => 'completed'
            ],
            [
                'investment_id' => 2,
                'payment_amount' => 8000,
                'payment_date' => '2024-05-01',
                'payment_type' => 'interest',
                'payment_method' => 'bank_transfer',
                'reference_number' => 'PAY003',
                'status' => 'completed'
            ],
            [
                'investment_id' => 2,
                'payment_amount' => 4000,
                'payment_date' => '2024-08-01',
                'payment_type' => 'interest',
                'payment_method' => 'bank_transfer',
                'reference_number' => 'PAY004',
                'status' => 'completed'
            ]
        ];

        foreach ($payments as $paymentData) {
            InvestmentPayment::create($paymentData);
        }

        // Create sample business budgets
        $budgets = [
            [
                'budget_title' => 'Q4 2024 Marketing Budget',
                'budget_description' => 'Marketing budget for Q4 2024 including digital ads and promotions',
                'total_budget' => 15000,
                'currency' => 'USD',
                'budget_period' => 'quarterly',
                'start_date' => '2024-10-01',
                'end_date' => '2024-12-31',
                'category' => 'marketing',
                'status' => 'active',
                'allocated_amount' => 15000,
                'spent_amount' => 8500,
                'remaining_amount' => 6500,
                'created_by' => 1
            ],
            [
                'budget_title' => '2024 Development Budget',
                'budget_description' => 'Annual budget for software development and maintenance',
                'total_budget' => 50000,
                'currency' => 'USD',
                'budget_period' => 'yearly',
                'start_date' => '2024-01-01',
                'end_date' => '2024-12-31',
                'category' => 'development',
                'status' => 'active',
                'allocated_amount' => 50000,
                'spent_amount' => 32000,
                'remaining_amount' => 18000,
                'created_by' => 1
            ]
        ];

        foreach ($budgets as $budgetData) {
            BusinessBudget::create($budgetData);
        }

        // Create sample business expenses
        $expenses = [
            [
                'expense_title' => 'Google Ads Campaign',
                'expense_description' => 'Digital advertising campaign for Q4',
                'amount' => 3000,
                'currency' => 'USD',
                'expense_category' => 'advertising',
                'expense_date' => '2024-10-15',
                'payment_method' => 'credit_card',
                'vendor_name' => 'Google',
                'invoice_number' => 'INV001',
                'status' => 'paid',
                'budget_id' => 1,
                'investment_id' => 1
            ],
            [
                'expense_title' => 'Facebook Ads',
                'expense_description' => 'Social media advertising',
                'amount' => 2500,
                'currency' => 'USD',
                'expense_category' => 'advertising',
                'expense_date' => '2024-10-20',
                'payment_method' => 'credit_card',
                'vendor_name' => 'Facebook',
                'invoice_number' => 'INV002',
                'status' => 'paid',
                'budget_id' => 1,
                'investment_id' => 1
            ],
            [
                'expense_title' => 'Server Hosting',
                'expense_description' => 'Monthly server hosting costs',
                'amount' => 800,
                'currency' => 'USD',
                'expense_category' => 'development',
                'expense_date' => '2024-10-01',
                'payment_method' => 'bank_transfer',
                'vendor_name' => 'AWS',
                'invoice_number' => 'INV003',
                'status' => 'paid',
                'budget_id' => 2,
                'investment_id' => 2
            ],
            [
                'expense_title' => 'Software Licenses',
                'expense_description' => 'Annual software licenses renewal',
                'amount' => 1200,
                'currency' => 'USD',
                'expense_category' => 'license',
                'expense_date' => '2024-10-10',
                'payment_method' => 'bank_transfer',
                'vendor_name' => 'Microsoft',
                'invoice_number' => 'INV004',
                'status' => 'paid',
                'budget_id' => 2,
                'investment_id' => 1
            ],
            [
                'expense_title' => 'Office Rent',
                'expense_description' => 'Monthly office rent',
                'amount' => 1500,
                'currency' => 'USD',
                'expense_category' => 'office',
                'expense_date' => '2024-10-01',
                'payment_method' => 'bank_transfer',
                'vendor_name' => 'Office Space LLC',
                'invoice_number' => 'INV005',
                'status' => 'paid',
                'recurring' => true,
                'next_due_date' => '2024-11-01'
            ]
        ];

        foreach ($expenses as $expenseData) {
            BusinessExpense::create($expenseData);
        }

        // Create sample business revenue
        $revenues = [
            [
                'revenue_title' => 'Point Sales - Premium Package',
                'revenue_description' => 'Revenue from premium point package sales',
                'amount' => 2500,
                'currency' => 'USD',
                'revenue_type' => 'point_sales',
                'revenue_date' => '2024-10-15',
                'payment_method' => 'online',
                'customer_name' => 'Premium User 1',
                'status' => 'received'
            ],
            [
                'revenue_title' => 'Point Sales - Basic Package',
                'revenue_description' => 'Revenue from basic point package sales',
                'amount' => 800,
                'currency' => 'USD',
                'revenue_type' => 'point_sales',
                'revenue_date' => '2024-10-18',
                'payment_method' => 'online',
                'customer_name' => 'Basic User 1',
                'status' => 'received'
            ],
            [
                'revenue_title' => 'Premium Features Revenue',
                'revenue_description' => 'Revenue from premium feature subscriptions',
                'amount' => 1200,
                'currency' => 'USD',
                'revenue_type' => 'premium_features',
                'revenue_date' => '2024-10-20',
                'payment_method' => 'online',
                'customer_name' => 'Premium Feature User',
                'status' => 'received'
            ],
            [
                'revenue_title' => 'Advertising Revenue',
                'revenue_description' => 'Revenue from platform advertising',
                'amount' => 3000,
                'currency' => 'USD',
                'revenue_type' => 'advertising',
                'revenue_date' => '2024-10-25',
                'payment_method' => 'bank_transfer',
                'customer_name' => 'Advertiser Corp',
                'status' => 'received'
            ]
        ];

        foreach ($revenues as $revenueData) {
            BusinessRevenue::create($revenueData);
        }
    }
} 