<?php

namespace FireflyIII\Report;

use Carbon\Carbon;
use FireflyIII\Database\Account\Account as AccountRepository;
use FireflyIII\Database\SwitchUser;
use FireflyIII\Database\TransactionJournal\TransactionJournal as JournalRepository;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;

/**
 * @SuppressWarnings("CamelCase") // I'm fine with this.
 *
 * Class Report
 *
 * @package FireflyIII\Report
 */
class Report implements ReportInterface
{

    use SwitchUser;

    /** @var AccountRepository */
    protected $_accounts;
    /** @var  \FireflyIII\Report\ReportHelperInterface */
    protected $_helper;
    /** @var JournalRepository */
    protected $_journals;
    /** @var  \FireflyIII\Report\ReportQueryInterface */
    protected $_queries;

    /**
     * @param AccountRepository $accounts
     * @param JournalRepository $journals
     */
    public function __construct(AccountRepository $accounts, JournalRepository $journals)
    {
        $this->_accounts = $accounts;
        $this->_journals = $journals;
        $this->_queries  = \App::make('FireflyIII\Report\ReportQueryInterface');
        $this->_helper   = \App::make('FireflyIII\Report\ReportHelperInterface');


    }

    /**
     * This methods fails to take in account transfers FROM shared accounts.
     *
     * @param Carbon $start
     * @param Carbon $end
     * @param int    $limit
     *
     * @return Collection
     */
    public function expensesGroupedByAccount(Carbon $start, Carbon $end, $limit = 15)
    {
        $result  = $this->_queries->journalsByExpenseAccount($start, $end);
        $array   = $this->_helper->makeArray($result);
        $limited = $this->_helper->limitArray($array, $limit);

        return $limited;

    }

    /**
     * Gets all the users shared and non-shared accounts combined with various meta-data
     * to display the amount of money spent that month compared to what's been spend within
     * budgets.
     *
     * @param Carbon $date
     *
     * @return Collection
     */
    public function getAccountListBudgetOverview(Carbon $date)
    {
        $start = clone $date;
        $start->startOfMonth();
        $end = clone $date;
        $end->endOfMonth();
        $start->subDay();
        $accounts = $this->_queries->getAllAccounts($start, $end);

        $accounts->each(
            function (\Account $account) use ($start, $end) {
                $budgets        = $this->_queries->getBudgetSummary($account, $start, $end);
                $balancedAmount = $this->_queries->balancedTransactionsSum($account, $start, $end);
                $array          = [];
                foreach ($budgets as $budget) {
                    $id         = intval($budget->id);
                    $data       = $budget->toArray();
                    $array[$id] = $data;
                }
                $account->budgetInformation = $array;
                $account->balancedAmount    = $balancedAmount;

            }
        );

        return $accounts;

    }

    /**
     * @param Carbon $date
     *
     * @return array
     */
    public function getAccountsForMonth(Carbon $date)
    {
        $start = clone $date;
        $start->startOfMonth()->subDay();
        $end = clone $date;
        $end->endOfMonth();
        \Log::debug('Monthly report account dates: start:[' . $start->format('Y-m-d') . '] and end:[' . $end->format('Y-m-d') . ']');
        $list     = $this->_queries->accountList();
        $accounts = [];
        /** @var \Account $account */
        foreach ($list as $account) {
            $id = intval($account->id);
            /** @noinspection PhpParamsInspection */
            $accounts[$id] = [
                'name'         => $account->name,
                'startBalance' => \Steam::balance($account, $start),
                'endBalance'   => \Steam::balance($account, $end)
            ];

            $accounts[$id]['difference'] = $accounts[$id]['endBalance'] - $accounts[$id]['startBalance'];
        }

        return $accounts;
    }

    /**
     * @param Carbon $date
     *
     * @return Collection
     */
    public function getBudgetsForMonth(Carbon $date)
    {
        $start = clone $date;
        $start->startOfMonth();
        $end = clone $date;
        $end->endOfMonth();
        // all budgets
        $set                   = $this->_queries->getAllBudgets($date);
        $budgets               = $this->_helper->makeArray($set);
        $amountSet             = $this->_queries->journalsByBudget($start, $end);
        $amounts               = $this->_helper->makeArray($amountSet);
        $combined              = $this->_helper->mergeArrays($budgets, $amounts);
        $combined[0]['spent']  = isset($combined[0]['spent']) ? $combined[0]['spent'] : 0.0;
        $combined[0]['amount'] = isset($combined[0]['amount']) ? $combined[0]['amount'] : 0.0;
        $combined[0]['name']   = 'No budget';

        // find transactions to shared expense accounts, which are without a budget by default:
        $transfers = $this->_queries->sharedExpenses($start, $end);
        foreach ($transfers as $transfer) {
            $combined[0]['spent'] += floatval($transfer->amount) * -1;
        }

        return $combined;
    }

    /**
     * @param Carbon $date
     * @param int    $limit
     *
     * @return array
     */
    public function getCategoriesForMonth(Carbon $date, $limit = 15)
    {
        $start = clone $date;
        $start->startOfMonth();
        $end = clone $date;
        $end->endOfMonth();
        // all categories.
        $result     = $this->_queries->journalsByCategory($start, $end);
        $categories = $this->_helper->makeArray($result);

        // all transfers
        $result    = $this->_queries->sharedExpensesByCategory($start, $end);
        $transfers = $this->_helper->makeArray($result);
        $merged    = $this->_helper->mergeArrays($categories, $transfers);

        // sort.
        $sorted = $this->_helper->sortNegativeArray($merged);

        // limit to $limit:
        $cut = $this->_helper->limitArray($sorted, $limit);

        return $cut;
    }

    /**
     * @param Carbon $date
     * @param int    $limit
     *
     * @return Collection
     */
    public function getExpenseGroupedForMonth(Carbon $date, $limit = 15)
    {
        $start = clone $date;
        $start->startOfMonth();
        $end = clone $date;
        $end->endOfMonth();

        $set      = $this->_queries->journalsByExpenseAccount($start, $end);
        $expenses = $this->_helper->makeArray($set);

//        $alt       = $this->_queries->sharedExpenses($start, $end);
//        $transfers = $this->_helper->makeArray($alt);
//
//        $expenses[-1] = [
//            'amount' => 0,
//            'name'   => 'Transfers to shared',
//            'spent'  => 0
//        ];
//
//        foreach ($transfers as $transfer) {
//            $expenses[-1]['amount'] += $transfer['amount'];
//        }

        $expenses = $this->_helper->sortArray($expenses);
        $limited  = $this->_helper->limitArray($expenses, $limit);

        return $limited;

    }

    /**
     * This method gets all incomes (journals) in a list.
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     *
     * @param Carbon $date
     * @param bool   $shared
     *
     * @return Collection
     */
    public function getIncomeForMonth(Carbon $date, $shared = false)
    {
        $start = clone $date;
        $start->startOfMonth();
        $end = clone $date;
        $end->endOfMonth();
        $userId = $this->_accounts->getUser()->id;

        return $this->_queries->incomeByPeriod($start, $end);

        //        $list = \TransactionJournal::leftJoin('transactions', 'transaction_journals.id', '=', 'transactions.transaction_journal_id')
        //                                   ->leftJoin('accounts', 'transactions.account_id', '=', 'accounts.id')
        //                                   ->leftJoin(
        //                                       'account_meta', function (JoinClause $join) {
        //                                       $join->on('account_meta.account_id', '=', 'accounts.id')->where('account_meta.name', '=', 'accountRole');
        //                                   }
        //                                   )
        //                                   ->transactionTypes(['Deposit'])
        //                                   ->where('transaction_journals.user_id', $userId)
        //                                   ->where('transactions.amount', '>', 0)
        //                                   ->where('transaction_journals.user_id', \Auth::user()->id)
        //                                   ->where('account_meta.data', '!=', '"sharedExpense"')
        //                                   ->orderBy('date', 'ASC')
        //                                   ->before($end)->after($start)->get(['transaction_journals.*']);
        //
        //        // incoming from a shared account: it's profit (income):
        //        $transfers = \TransactionJournal::withRelevantData()
        //                                        ->leftJoin('transactions', 'transaction_journals.id', '=', 'transactions.transaction_journal_id')
        //                                        ->leftJoin('accounts', 'transactions.account_id', '=', 'accounts.id')
        //                                        ->leftJoin(
        //                                            'account_meta', function (JoinClause $join) {
        //                                            $join->on('account_meta.account_id', '=', 'accounts.id')->where('account_meta.name', '=', 'accountRole');
        //                                        }
        //                                        )
        //                                        ->transactionTypes(['Transfer'])
        //                                        ->where('transaction_journals.user_id', $userId)
        //                                        ->where('transactions.amount', '<', 0)
        //                                        ->where('account_meta.data', '=', '"sharedExpense"')
        //                                        ->orderBy('date', 'ASC')
        //                                        ->before($end)->after($start)->get(['transaction_journals.*']);
        //
        //        $list = $list->merge($transfers);
        //        $list->sort(
        //            function (\TransactionJournal $journal) {
        //                return $journal->date->format('U');
        //            }
        //        );
        //
        //        return $list;

    }

    /**
     * @param Carbon $date
     *
     * @return Collection
     */
    public function getPiggyBanksForMonth(Carbon $date)
    {
        $start = clone $date;
        $start->startOfMonth();
        $end = clone $date;
        $end->endOfMonth();

        \PiggyBank::
        leftJoin('accounts', 'accounts.id', '=', 'piggy_banks.account_id')
                  ->where('accounts.user_id', \Auth::user()->id)
                  ->where('repeats', 0)
                  ->where(
                      function (Builder $query) use ($start, $end) {
                          $query->whereNull('piggy_banks.deleted_at');
                          $query->orWhere(
                              function (Builder $query) use ($start, $end) {
                                  $query->whereNotNull('piggy_banks.deleted_at');
                                  $query->where('piggy_banks.deleted_at', '>=', $start->format('Y-m-d 00:00:00'));
                                  $query->where('piggy_banks.deleted_at', '<=', $end->format('Y-m-d 00:00:00'));
                              }
                          );
                      }
                  )
                  ->get(['piggy_banks.*']);


    }

    /**
     * @param Carbon $start
     *
     * @return array
     */
    public function listOfMonths(Carbon $start)
    {
        $end    = Carbon::now();
        $months = [];
        while ($start <= $end) {
            $months[] = [
                'formatted' => $start->format('F Y'),
                'month'     => intval($start->format('m')),
                'year'      => intval($start->format('Y')),
            ];
            $start->addMonth();
        }

        return $months;
    }

    /**
     * @param Carbon $start
     *
     * @return array
     */
    public function listOfYears(Carbon $start)
    {
        $end   = Carbon::now();
        $years = [];
        while ($start <= $end) {
            $years[] = $start->format('Y');
            $start->addYear();
        }

        return $years;
    }

    /**
     *
     * @param Carbon $start
     * @param Carbon $end
     * @param int    $limit
     *
     * @return Collection
     */
    public function revenueGroupedByAccount(Carbon $start, Carbon $end, $limit = 15)
    {
        return $this->_queries->journalsByRevenueAccount($start, $end, $limit);


    }

    /**
     * @param Carbon $date
     *
     * @return array
     */
    public function yearBalanceReport(Carbon $date)
    {
        $start            = clone $date;
        $end              = clone $date;
        $sharedAccounts   = [];
        $sharedCollection = \Auth::user()->accounts()
                                 ->leftJoin('account_meta', 'account_meta.account_id', '=', 'accounts.id')
                                 ->where('account_meta.name', '=', 'accountRole')
                                 ->where('account_meta.data', '=', json_encode('sharedExpense'))
                                 ->get(['accounts.id']);

        foreach ($sharedCollection as $account) {
            $sharedAccounts[] = $account->id;
        }

        $accounts = $this->_accounts->getAccountsByType(['Default account', 'Asset account'])->filter(
            function (\Account $account) use ($sharedAccounts) {
                if (!in_array($account->id, $sharedAccounts)) {
                    return $account;
                }

                return null;
            }
        );
        $report   = [];
        $start->startOfYear()->subDay();
        $end->endOfYear();

        foreach ($accounts as $account) {
            $report[] = [
                'start'   => \Steam::balance($account, $start),
                'end'     => \Steam::balance($account, $end),
                'account' => $account,
                'shared'  => $account->accountRole == 'sharedExpense'
            ];
        }

        return $report;
    }

} 
