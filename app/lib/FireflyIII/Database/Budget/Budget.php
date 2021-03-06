<?php
namespace FireflyIII\Database\Budget;

use Carbon\Carbon;
use FireflyIII\Database\CommonDatabaseCallsInterface;
use FireflyIII\Database\CUDInterface;
use FireflyIII\Database\SwitchUser;
use FireflyIII\Exception\FireflyException;
use FireflyIII\Exception\NotImplementedException;
use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Collection;
use Illuminate\Support\MessageBag;

/**
 * Class Budget
 *
 * @package FireflyIII\Database
 */
class Budget implements CUDInterface, CommonDatabaseCallsInterface, BudgetInterface
{
    use SwitchUser;

    /**
     *
     */
    public function __construct()
    {
        $this->setUser(\Auth::user());
    }

    /**
     * @param Eloquent $model
     *
     * @return bool
     */
    public function destroy(Eloquent $model)
    {
        $model->delete();

        return true;
    }

    /**
     * @param array $data
     *
     * @return \Eloquent
     * @throws FireflyException
     */
    public function store(array $data)
    {
        $budget = new \Budget($data);

        if (!$budget->isValid()) {
            \Log::error('Could not store budget: ' . $budget->getErrors()->toJson());
            throw new FireflyException($budget->getErrors()->first());
        }
        $budget->save();

        return $budget;
    }

    /**
     * @param Eloquent $model
     * @param array    $data
     *
     * @return bool
     */
    public function update(Eloquent $model, array $data)
    {
        $model->name = $data['name'];
        $model->save();

        return true;
    }

    /**
     * Validates an array. Returns an array containing MessageBags
     * errors/warnings/successes.
     *
     * @param array $model
     *
     * @return array
     */
    public function validate(array $model)
    {
        $warnings  = new MessageBag;
        $successes = new MessageBag;
        $budget    = new \Budget($model);
        $budget->isValid();
        $errors = $budget->getErrors();

        if (!$errors->has('name')) {
            $successes->add('name', 'OK');
        }

        return ['errors' => $errors, 'warnings' => $warnings, 'successes' => $successes];
    }

    /**
     * @param Carbon $start
     * @param Carbon $end
     *
     * @return Collection
     */
    public function expenseNoBudget(Carbon $start, Carbon $end)
    {
        // Add expenses that have no budget:
        return $this->getUser()
                    ->transactionjournals()
                    ->whereNotIn(
                        'transaction_journals.id', function (QueryBuilder $query) use ($start, $end) {
                        $query
                            ->select('transaction_journals.id')
                            ->from('transaction_journals')
                            ->leftJoin('budget_transaction_journal', 'budget_transaction_journal.transaction_journal_id', '=', 'transaction_journals.id')
                            ->where('transaction_journals.date', '>=', $start->format('Y-m-d 00:00:00'))
                            ->where('transaction_journals.date', '<=', $end->format('Y-m-d 00:00:00'));
                    }
                    )
                    ->before($end)
                    ->after($start)
                    ->lessThan(0)
                    ->transactionTypes(['Withdrawal'])
                    ->get();
    }

    /**
     * @param Carbon $start
     * @param Carbon $end
     *
     * @return Collection
     */
    public function journalsNoBudget(Carbon $start, Carbon $end)
    {
        $set = $this->getUser()
                    ->transactionjournals()
                    ->leftJoin('budget_transaction_journal', 'budget_transaction_journal.transaction_journal_id', '=', 'transaction_journals.id')
                    ->whereNull('budget_transaction_journal.id')
                    ->before($end)
                    ->after($start)
                    ->orderBy('transaction_journals.date')
                    ->get(['transaction_journals.*']);

        return $set;
    }

    /**
     * This method includes the time because otherwise, SQLite does not understand it.
     *
     * @param \Budget $budget
     * @param Carbon  $date
     *
     * @return \LimitRepetition|null
     */
    public function repetitionOnStartingOnDate(\Budget $budget, Carbon $date)
    {
        return \LimitRepetition::
        leftJoin('budget_limits', 'limit_repetitions.budget_limit_id', '=', 'budget_limits.id')
                               ->where('limit_repetitions.startdate', $date->format('Y-m-d 00:00:00'))
                               ->where('budget_limits.budget_id', $budget->id)
                               ->first(['limit_repetitions.*']);
    }

    /**
     * Returns an object with id $id.
     *
     * @param int $objectId
     *
     * @return \Eloquent
     */
    public function find($objectId)
    {
        return $this->getUser()->budgets()->find($objectId);
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     *
     * Finds an account type using one of the "$what"'s: expense, asset, revenue, opening, etc.
     *
     * @param $what
     *
     * @return \AccountType|null
     * @throws NotImplementedException
     * @codeCoverageIgnore
     */
    public function findByWhat($what)
    {
        throw new NotImplementedException;
    }

    /**
     * Returns all objects.
     *
     * @return Collection
     */
    public function get()
    {
        $budgets = $this->getUser()->budgets()->get();

        return $budgets;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     *
     * @param array $ids
     *
     * @return Collection
     * @throws NotImplementedException
     * @codeCoverageIgnore
     */
    public function getByIds(array $ids)
    {
        throw new NotImplementedException;
    }

    /**
     * Returns all the transaction journals for a limit, possibly limited by a limit repetition.
     *
     * @param \Budget          $budget
     * @param \LimitRepetition $repetition
     * @param int              $take
     *
     * @return \Illuminate\Pagination\Paginator
     */
    public function getJournals(\Budget $budget, \LimitRepetition $repetition = null, $take = 50)
    {
        $offset = intval(\Input::get('page')) > 0 ? intval(\Input::get('page')) * $take : 0;


        $setQuery   = $budget->transactionJournals()->withRelevantData()->take($take)->offset($offset)->orderBy('date', 'DESC');
        $countQuery = $budget->transactionJournals();


        if (!is_null($repetition)) {
            $setQuery->after($repetition->startdate)->before($repetition->enddate);
            $countQuery->after($repetition->startdate)->before($repetition->enddate);
        }


        $set   = $setQuery->get(['transaction_journals.*']);
        $count = $countQuery->count();
        $items = [];
        foreach ($set as $entry) {
            $items[] = $entry;
        }

        return \Paginator::make($items, $count, $take);
    }

    /**
     * @param \Budget $budget
     * @param Carbon  $date
     *
     * @return \LimitRepetition
     */
    public function getRepetitionByDate(\Budget $budget, Carbon $date)
    {
        return $budget->limitrepetitions()->where('limit_repetitions.startdate', $date)->first(['limit_repetitions.*']);
    }

    /**
     * @param \Budget $budget
     * @param Carbon  $date
     *
     * @return float
     */
    public function spentInMonth(\Budget $budget, Carbon $date)
    {
        $end = clone $date;
        $date->startOfMonth();
        $end->endOfMonth();
        $sum = floatval($budget->transactionjournals()->before($end)->after($date)->lessThan(0)->sum('amount')) * -1;

        return $sum;
    }

    /**
     * This method updates the amount (envelope) for the given date and budget. This results in a (new) limit (aka an envelope)
     * for that budget. Returned to the user is the new limit repetition.
     *
     * @param \Budget $budget
     * @param Carbon  $date
     * @param         $amount
     *
     * @return \LimitRepetition
     * @throws \Exception
     */
    public function updateLimitAmount(\Budget $budget, Carbon $date, $amount)
    {
        /** @var \BudgetLimit $limit */
        $limit = $this->limitOnStartingOnDate($budget, $date);
        if (!$limit) {
            // create one!
            $limit = new \BudgetLimit;
            $limit->budget()->associate($budget);
            $limit->startdate   = $date;
            $limit->amount      = $amount;
            $limit->repeat_freq = 'monthly';
            $limit->repeats     = 0;
            $limit->save();
            \Log::info('ID: ' . $limit->id);
            /*
             * A newly stored limit also created a limit repetition.
             */
            \Event::fire('limits.store', [$limit]);
        } else {
            if ($amount > 0) {
                $limit->amount = $amount;
                $limit->save();
                /*
                 * An updated limit also updates the associated limit repetitions.
                 */
                \Event::fire('limits.update', [$limit]);
            } else {
                $limit->delete();
            }
        }

        return $limit->limitrepetitions()->first();


    }

    /**
     * @param \Budget $budget
     * @param Carbon  $date
     *
     * @return \BudgetLimit
     */
    public function limitOnStartingOnDate(\Budget $budget, Carbon $date)
    {
        return $budget->budgetLimits()->where('startdate', $date->format('Y-m-d 00:00:00'))->first();


    }
}
