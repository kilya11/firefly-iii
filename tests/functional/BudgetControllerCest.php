<?php

/**
 * @SuppressWarnings("CamelCase")
 * @SuppressWarnings("short")
 *
 * Class BudgetControllerCest
 */
class BudgetControllerCest
{
    /**
     * @param FunctionalTester $I
     */
    public function _after(FunctionalTester $I)
    {
    }

    /**
     * @param FunctionalTester $I
     */
    public function _before(FunctionalTester $I)
    {
        $I->amLoggedAs(['email' => 'thegrumpydictator@gmail.com', 'password' => 'james']);
    }

    /**
     * @param FunctionalTester $I
     */
    public function amount(FunctionalTester $I)
    {
        $I->wantTo('update the amount for a budget and limit repetition');
        $I->amOnPage('/budgets');

        ///budgets/income

        $I->sendAjaxPostRequest('/budgets/amount/1', ['amount' => 100]);
        $I->canSeeResponseCodeIs(200);
        $I->see('Groceries');
        $I->seeInDatabase('budgets', ['id' => 1]);
        #$I->seeInDatabase('budget_limits', ['budget_id' => 1, 'amount' => 100.00]);
    }

    /**
     * @param FunctionalTester $I
     */
    public function create(FunctionalTester $I)
    {
        $I->wantTo('create a budget');
        $I->amOnRoute('budgets.create');
        $I->see('Create a new budget');
    }

    /**
     * @param FunctionalTester $I
     */
    public function delete(FunctionalTester $I)
    {
        $I->wantTo('delete a budget');
        $I->amOnPage('/budgets/delete/3');
        $I->see('Delete budget "Delete me"');
    }

    /**
     * @param FunctionalTester $I
     */
    public function destroy(FunctionalTester $I)
    {
        $I->wantTo('destroy a budget');
        $I->amOnPage('/budgets/delete/3');
        $I->see('Delete budget "Delete me"');
        $I->submitForm('#destroy', []);
        $I->see('Budget &quot;Delete me&quot; was deleted.');
    }

    /**
     * @param FunctionalTester $I
     */
    public function edit(FunctionalTester $I)
    {
        $I->wantTo('edit a budget');
        $I->amOnPage('/budgets/edit/3');
        $I->see('Edit budget "Delete me"');
    }

    /**
     * @param FunctionalTester $I
     */
    public function failUpdate(FunctionalTester $I)
    {
        $I->wantTo('update a budget and fail');
        $I->amOnPage('/budgets/edit/3');
        $I->see('Edit budget "Delete me"');
        $I->submitForm('#update', ['name' => '', 'post_submit_action' => 'update']);
        $I->seeRecord('budgets', ['name' => 'Delete me']);

    }

    /**
     * @param FunctionalTester $I
     */
    public function index(FunctionalTester $I)
    {
        $I->wantTo('show all budgets');
        $I->amOnPage('/budgets');
        $I->see('Budgets');
    }

    /**
     * @param FunctionalTester $I
     */
    public function indexNoBudget(FunctionalTester $I)
    {
        $I->wantTo('see transactions without a budget');
        $I->amOnPage('/budgets/list/noBudget');
        $I->see('Transactions without a budget in');
    }

    /**
     * @param FunctionalTester $I
     */
    public function postUpdateIncome(FunctionalTester $I)
    {
        $date = date('FY');
        $I->wantTo('process the update to my monthly income');
        $I->amOnPage('/budgets/income');
        $I->see('Update (expected) income for');
        $I->submitForm('#income', ['amount' => 1200]);
        $I->seeRecord('preferences', ['name' => 'budgetIncomeTotal' . $date, 'data' => 1200]);
    }

    /**
     * @param FunctionalTester $I
     */
    public function show(FunctionalTester $I)
    {
        $I->wantTo('show a budget');
        $I->amOnPage('/budgets/show/3');
        $I->see('Delete me');
    }

    /**
     * @param FunctionalTester $I
     */
    public function showInvalidRepetition(FunctionalTester $I)
    {
        $I->wantTo('show a budget with a repetition that does not match the budget.');
        $I->amOnPage('/budgets/show/1/3');
        $I->see('Invalid selection');
    }

    /**
     * @param FunctionalTester $I
     */
    public function store(FunctionalTester $I)
    {
        $I->amOnPage('/budgets/create');
        $I->wantTo('store a new budget');
        $I->see('Create a new budget');
        $I->submitForm('#store', ['name' => 'New budget.', 'post_submit_action' => 'store']);
        $I->seeRecord('budgets', ['name' => 'New budget.']);
    }

    /**
     * @param FunctionalTester $I
     */
    public function storeAndCreateAnother(FunctionalTester $I)
    {
        $I->amOnPage('/budgets/create');
        $I->wantTo('store a new budget and create another');
        $I->see('Create a new budget');
        $I->submitForm('#store', ['name' => 'New budget.', 'post_submit_action' => 'create_another']);
        $I->seeRecord('budgets', ['name' => 'New budget.']);
    }

    /**
     * @param FunctionalTester $I
     */
    public function storeFail(FunctionalTester $I)
    {
        $I->amOnPage('/budgets/create');
        $I->wantTo('make storing a new budget fail.');
        $I->see('Create a new budget');
        $I->submitForm('#store', ['name' => null, 'post_submit_action' => 'store']);
        $I->dontSeeRecord('budgets', ['name' => 'New budget.']);
    }

    /**
     * @param FunctionalTester $I
     */
    public function storeValidateOnly(FunctionalTester $I)
    {
        $I->amOnPage('/budgets/create');
        $I->wantTo('validate a new budget');
        $I->see('Create a new budget');
        $I->submitForm('#store', ['name' => 'New budget.', 'post_submit_action' => 'validate_only']);
        $I->dontSeeRecord('budgets', ['name' => 'New budget.']);
    }

    /**
     * @param FunctionalTester $I
     */
    public function update(FunctionalTester $I)
    {
        $I->wantTo('update a budget');
        $I->amOnPage('/budgets/edit/3');
        $I->see('Edit budget "Delete me"');
        $I->submitForm('#update', ['name' => 'Update me', 'post_submit_action' => 'update']);
        $I->seeRecord('budgets', ['name' => 'Update me']);

    }

    /**
     * @param FunctionalTester $I
     */
    public function updateAndReturn(FunctionalTester $I)
    {
        $I->wantTo('update a budget and return to form');
        $I->amOnPage('/budgets/edit/3');
        $I->see('Edit budget "Delete me"');
        $I->submitForm(
            '#update', ['name' => 'Savings accountXX', 'post_submit_action' => 'return_to_edit']
        );
        $I->seeRecord('budgets', ['name' => 'Savings accountXX']);

    }

    /**
     * @param FunctionalTester $I
     */
    public function updateIncome(FunctionalTester $I)
    {
        $I->amOnPage('/budgets/income');
        $I->wantTo('update my monthly income');
        $I->see('Update (expected) income for ');
    }

    /**
     * @param FunctionalTester $I
     */
    public function validateUpdateOnly(FunctionalTester $I)
    {
        $I->wantTo('update a budget and validate only');
        $I->amOnPage('/budgets/edit/3');
        $I->see('Edit budget "Delete me"');
        $I->submitForm(
            '#update', ['name' => 'Validate Only', 'post_submit_action' => 'validate_only']
        );
        $I->dontSeeRecord('budgets', ['name' => 'Savings accountXX']);
        $I->seeRecord('budgets', ['name' => 'Delete me']);

    }
}
