<?php
namespace FireflyIII\Database\Category;

use Carbon\Carbon;
use FireflyIII\Database\CommonDatabaseCallsInterface;
use FireflyIII\Database\CUDInterface;
use FireflyIII\Database\SwitchUser;
use FireflyIII\Exception\FireflyException;
use FireflyIII\Exception\NotImplementedException;
use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Support\Collection;
use Illuminate\Support\MessageBag;

/**
 * Class Category
 *
 * @package FireflyIII\Database
 */
class Category implements CUDInterface, CommonDatabaseCallsInterface
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
        $category       = new \Category;
        $category->name = $data['name'];
        $category->user()->associate($this->getUser());
        if (!$category->isValid()) {
            \Log::error('Could not store category: ' . $category->getErrors()->toJson());
            throw new FireflyException($category->getErrors()->first());
        }
        $category->save();

        return $category;
    }

    /**
     * @param Eloquent $model
     * @param array    $data
     *
     * @return bool
     * @throws FireflyException
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
        $category  = new \Category($model);
        $category->isValid();
        $errors = $category->getErrors();

        if (!$errors->has('name')) {
            $successes->add('name', 'OK');
        }

        return ['errors' => $errors, 'warnings' => $warnings, 'successes' => $successes];
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     *
     * Returns an object with id $id.
     *
     * @param int $objectId
     *
     * @return \Eloquent
     * @throws NotImplementedException
     * @codeCoverageIgnore
     */
    public function find($objectId)
    {
        throw new NotImplementedException;
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
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     *
     * Returns all objects.
     *
     * @return Collection
     */
    public function get()
    {
        return $this->getUser()->categories()->orderBy('name', 'ASC')->get();
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
     * @param $name
     *
     * @return \Category
     */
    public function firstOrCreate($name)
    {
        return \Category::firstOrCreate(['user_id' => $this->getUser()->id, 'name' => $name]);
    }

    /**
     * @param \Category $category
     * @param int       $limit
     *
     * @return \Illuminate\Pagination\Paginator
     */
    public function getTransactionJournals(\Category $category, $limit = 50)
    {
        $offset = intval(\Input::get('page')) > 0 ? intval(\Input::get('page')) * $limit : 0;
        $set    = $category->transactionJournals()->withRelevantData()->take($limit)->offset($offset)->orderBy('date', 'DESC')->get(['transaction_journals.*']);
        $count  = $category->transactionJournals()->count();
        $items  = [];
        foreach ($set as $entry) {
            $items[] = $entry;
        }

        return \Paginator::make($items, $count, $limit);

    }

    /**
     * @param Carbon $start
     * @param Carbon $end
     *
     * @return Collection
     */
    public function journalsNoCategory(Carbon $start, Carbon $end)
    {
        $set = $this->getUser()
                    ->transactionjournals()
                    ->leftJoin('category_transaction_journal', 'category_transaction_journal.transaction_journal_id', '=', 'transaction_journals.id')
                    ->whereNull('category_transaction_journal.id')
                    ->before($end)
                    ->after($start)
                    ->orderBy('transaction_journals.date')
                    ->get(['transaction_journals.*']);

        return $set;
    }

    /**
     * @param \Category $category
     * @param Carbon    $date
     *
     * @return float
     */
    public function spentInMonth(\Category $category, Carbon $date)
    {
        $end = clone $date;
        $date->startOfMonth();
        $end->endOfMonth();
        $sum = floatval($category->transactionjournals()->before($end)->after($date)->lessThan(0)->sum('amount')) * -1;

        return $sum;
    }
}
