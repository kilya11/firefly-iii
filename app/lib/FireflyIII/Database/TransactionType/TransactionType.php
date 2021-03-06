<?php

namespace FireflyIII\Database\TransactionType;


use FireflyIII\Database\CommonDatabaseCallsInterface;
use FireflyIII\Database\CUDInterface;
use FireflyIII\Exception\FireflyException;
use FireflyIII\Exception\NotImplementedException;
use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Support\Collection;


/**
 * Class TransactionType
 *
 * @package FireflyIII\Database
 */
class TransactionType implements CUDInterface, CommonDatabaseCallsInterface
{

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     *
     * @param Eloquent $model
     *
     * @return bool
     * @throws NotImplementedException
     * @codeCoverageIgnore
     */
    public function destroy(Eloquent $model)
    {
        throw new NotImplementedException;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     *
     * @param array $data
     *
     * @return \Eloquent
     * @throws NotImplementedException
     * @codeCoverageIgnore
     */
    public function store(array $data)
    {
        throw new NotImplementedException;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     *
     * @param Eloquent $model
     * @param array    $data
     *
     * @return bool
     * @throws NotImplementedException
     * @codeCoverageIgnore
     */
    public function update(Eloquent $model, array $data)
    {
        throw new NotImplementedException;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     *
     * Validates an array. Returns an array containing MessageBags
     * errors/warnings/successes.
     *
     * @param array $model
     *
     * @return array
     * @throws NotImplementedException
     * @codeCoverageIgnore
     */
    public function validate(array $model)
    {
        throw new NotImplementedException;
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
     * Finds an account type using one of the "$what"'s: expense, asset, revenue, opening, etc.
     *
     * @param $what
     *
     * @return \AccountType|null
     * @throws FireflyException
     */
    public function findByWhat($what)
    {
        $translation = [
            'opening'    => 'Opening balance',
            'transfer'   => 'Transfer',
            'withdrawal' => 'Withdrawal',
            'deposit'    => 'Deposit',
        ];
        if (!isset($translation[$what])) {
            throw new FireflyException('Cannot find transaction type described as "' . e($what) . '".');
        }

        return \TransactionType::whereType($translation[$what])->first();
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     *
     * Returns all objects.
     *
     * @return Collection
     * @throws NotImplementedException
     * @codeCoverageIgnore
     */
    public function get()
    {
        throw new NotImplementedException;
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
}
