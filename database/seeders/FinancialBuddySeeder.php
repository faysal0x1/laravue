<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\BudgetLimit;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Income;
use App\Models\IncomeCategory;
use App\Models\User;
use Illuminate\Database\Seeder;

class FinancialBuddySeeder extends Seeder
{
    public function run(): void
    {
$user = User::whereId(1)->first();
        // Create accounts
        $accounts = [
            [
                'name' => 'Cash Wallet',
                'type' => 'cash',
                'initial_balance' => 50000,
                'current_balance' => 50000,
                'description' => 'Daily cash pocket',
            ],
            [
                'name' => 'Main Bank Account',
                'type' => 'bank',
                'initial_balance' => 200000,
                'current_balance' => 200000,
                'description' => 'Primary savings account',
            ],
            [
                'name' => 'Credit Card',
                'type' => 'card',
                'initial_balance' => 0,
                'current_balance' => 0,
                'description' => 'Shopping card',
            ],
        ];

        foreach ($accounts as $account) {
            Account::create(array_merge($account, ['user_id' => $user->id]));
        }

        // Create income categories
        $incomeCategories = [
            [
                'name' => 'Salary',
                'description' => 'Monthly salary',
                'color' => '#10B981',
                'icon' => 'salary',
            ],
            [
                'name' => 'Freelance',
                'description' => 'Freelance projects',
                'color' => '#3B82F6',
                'icon' => 'freelance',
            ],
            [
                'name' => 'Investment Returns',
                'description' => 'Investment income',
                'color' => '#F59E0B',
                'icon' => 'investment',
            ],
            [
                'name' => 'Gift/Bonus',
                'description' => 'Gifts and bonuses',
                'color' => '#8B5CF6',
                'icon' => 'gift',
            ],
        ];

        $incomeCategoryModels = [];
        foreach ($incomeCategories as $category) {
            $incomeCategoryModels[] = IncomeCategory::create(
                array_merge($category)
            );
        }

        // Create expense categories
        $expenseCategories = [
            [
                'name' => 'Food & Dining',
                'description' => 'Groceries and restaurants',
                'color' => '#EF4444',
                'icon' => 'food',
            ],
            [
                'name' => 'Transport',
                'description' => 'Rickshaw, bus, car fuel',
                'color' => '#F97316',
                'icon' => 'transport',
            ],
            [
                'name' => 'Utilities',
                'description' => 'Electricity, water, internet',
                'color' => '#06B6D4',
                'icon' => 'utilities',
            ],
            [
                'name' => 'Entertainment',
                'description' => 'Movies, gaming, hobbies',
                'color' => '#EC4899',
                'icon' => 'entertainment',
            ],
            [
                'name' => 'Shopping',
                'description' => 'Clothing and accessories',
                'color' => '#8B5CF6',
                'icon' => 'shopping',
            ],
            [
                'name' => 'Education',
                'description' => 'Books, courses, tuition',
                'color' => '#3B82F6',
                'icon' => 'education',
            ],
            [
                'name' => 'Medical',
                'description' => 'Healthcare and medicine',
                'color' => '#06B6D4',
                'icon' => 'medical',
            ],
            [
                'name' => 'Miscellaneous',
                'description' => 'Other expenses',
                'color' => '#6B7280',
                'icon' => 'misc',
            ],
        ];

        $expenseCategoryModels = [];
        foreach ($expenseCategories as $category) {
            $expenseCategoryModels[] = ExpenseCategory::create(
                array_merge($category)
            );
        }

        // Get account references
        $cashAccount = Account::where('user_id', $user->id)->where('type', 'cash')->first();
        $bankAccount = Account::where('user_id', $user->id)->where('type', 'bank')->first();

        // Create sample income transactions
        Income::create([
            'user_id' => $user->id,
            'account_id' => $bankAccount->id,
            'income_category_id' => $incomeCategoryModels[0]->id, // Salary
            'amount' => 150000,
            'description' => 'Monthly Salary - May 2024',
            'transaction_date' => now()->startOfMonth(),
            'status' => 'completed',
            'reference_no' => 'SAL-2024-05',
        ]);

        Income::create([
            'user_id' => $user->id,
            'account_id' => $bankAccount->id,
            'income_category_id' => $incomeCategoryModels[1]->id, // Freelance
            'amount' => 45000,
            'description' => 'Web development project',
            'transaction_date' => now()->subDays(5),
            'status' => 'completed',
            'reference_no' => 'FREELANCE-001',
        ]);

        // Create sample expense transactions
        Expense::create([
            'user_id' => $user->id,
            'account_id' => $cashAccount->id,
            'expense_category_id' => $expenseCategoryModels[0]->id, // Food
            'amount' => 2500,
            'description' => 'Groceries from market',
            'transaction_date' => now(),
            'status' => 'completed',
            'payment_method' => 'cash',
        ]);

        Expense::create([
            'user_id' => $user->id,
            'account_id' => $cashAccount->id,
            'expense_category_id' => $expenseCategoryModels[1]->id, // Transport
            'amount' => 500,
            'description' => 'Rickshaw to office',
            'transaction_date' => now(),
            'status' => 'completed',
            'payment_method' => 'cash',
        ]);

        Expense::create([
            'user_id' => $user->id,
            'account_id' => $bankAccount->id,
            'expense_category_id' => $expenseCategoryModels[2]->id, // Utilities
            'amount' => 1200,
            'description' => 'Electricity bill',
            'transaction_date' => now()->subDays(2),
            'status' => 'completed',
            'payment_method' => 'online',
            'reference_no' => 'ELEC-202405-001',
        ]);

        Expense::create([
            'user_id' => $user->id,
            'account_id' => $cashAccount->id,
            'expense_category_id' => $expenseCategoryModels[3]->id, // Entertainment
            'amount' => 1500,
            'description' => 'Movie ticket and snacks',
            'transaction_date' => now()->subDays(1),
            'status' => 'completed',
            'payment_method' => 'cash',
        ]);

        // Create budget limits for current month
        BudgetLimit::create([
            'user_id' => $user->id,
            'expense_category_id' => $expenseCategoryModels[0]->id, // Food budget
            'period' => 'monthly',
            'limit_amount' => 15000,
            'spent_amount' => 2500,
            'period_start_date' => now()->startOfMonth(),
            'period_end_date' => now()->endOfMonth(),
            'status' => 'active',
            'notes' => 'Weekly groceries budget',
        ]);

        BudgetLimit::create([
            'user_id' => $user->id,
            'expense_category_id' => $expenseCategoryModels[1]->id, // Transport budget
            'period' => 'monthly',
            'limit_amount' => 5000,
            'spent_amount' => 500,
            'period_start_date' => now()->startOfMonth(),
            'period_end_date' => now()->endOfMonth(),
            'status' => 'active',
            'notes' => 'Daily transport',
        ]);

        BudgetLimit::create([
            'user_id' => $user->id,
            'expense_category_id' => null, // Overall budget
            'period' => 'monthly',
            'limit_amount' => 50000,
            'spent_amount' => 5700,
            'period_start_date' => now()->startOfMonth(),
            'period_end_date' => now()->endOfMonth(),
            'status' => 'active',
            'notes' => 'Overall monthly expense limit',
        ]);

        $this->command->info('Financial Buddy database seeded successfully!');
        $this->command->info("Test user created: {$user->email}");
        $this->command->info('Password: password');
    }
}
