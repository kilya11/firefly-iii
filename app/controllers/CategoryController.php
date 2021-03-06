<?php
use Carbon\Carbon;
use FireflyIII\Database\Category\Category as CategoryRepository;
use FireflyIII\Exception\FireflyException;

/**
 *
 * @SuppressWarnings("CamelCase") // I'm fine with this.
 *
 * Class CategoryController
 */
class CategoryController extends BaseController
{

    /** @var CategoryRepository */
    protected $_repository;

    /**
     * @param CategoryRepository $repository
     */
    public function __construct(CategoryRepository $repository)
    {
        View::share('title', 'Categories');
        View::share('mainTitleIcon', 'fa-bar-chart');

        $this->_repository = $repository;
    }

    /**
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return View::make('categories.create')->with('subTitle', 'Create a new category');
    }

    /**
     * @return \Illuminate\View\View
     */
    public function noCategory()
    {
        $start    = \Session::get('start', Carbon::now()->startOfMonth());
        $end      = \Session::get('end', Carbon::now()->startOfMonth());
        $list     = $this->_repository->journalsNoCategory($start, $end);
        $subTitle = 'Transactions without a category in ' . $start->format('F Y');

        return View::make('categories.noCategory', compact('list', 'subTitle'));
    }

    /**
     * @param Category $category
     *
     * @return $this
     */
    public function delete(Category $category)
    {
        return View::make('categories.delete')->with('category', $category)->with('subTitle', 'Delete category "' . e($category->name) . '"');
    }

    /**
     * @param Category $category
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Category $category)
    {
        Session::flash('success', 'Category "' . e($category->name) . '" was deleted.');
        $this->_repository->destroy($category);


        return Redirect::route('categories.index');
    }

    /**
     * @param Category $category
     *
     * @return $this
     */
    public function edit(Category $category)
    {
        return View::make('categories.edit')->with('category', $category)->with('subTitle', 'Edit category "' . e($category->name) . '"');
    }

    /**
     * @return $this
     */
    public function index()
    {
        $categories = $this->_repository->get();

        return View::make('categories.index', compact('categories'));
    }

    /**
     * @param Category $category
     *
     * @return $this
     */
    public function show(Category $category)
    {
        $hideCategory = true; // used in list.
        $journals     = $this->_repository->getTransactionJournals($category, 50);

        return View::make('categories.show', compact('category', 'journals', 'hideCategory'));
    }

    /**
     *
     * @return $this
     * @throws FireflyException
     */
    public function store()
    {
        $data            = Input::except('_token');
        $data['user_id'] = Auth::user()->id;

        // always validate:
        $messages = $this->_repository->validate($data);

        // flash messages:
        Session::flash('warnings', $messages['warnings']);
        Session::flash('successes', $messages['successes']);
        Session::flash('errors', $messages['errors']);
        if ($messages['errors']->count() > 0) {
            Session::flash('error', 'Could not store category: ' . $messages['errors']->first());
            return Redirect::route('categories.create')->withInput();
        }

        // return to create screen:
        if ($data['post_submit_action'] == 'validate_only') {
            return Redirect::route('categories.create')->withInput();
        }

        // store
        $this->_repository->store($data);
        Session::flash('success', 'Category "' . e($data['name']) . '" stored.');
        if ($data['post_submit_action'] == 'store') {
            return Redirect::route('categories.index');
        }

        return Redirect::route('categories.create')->withInput();
    }

    /**
     *
     * @param Category $category
     *
     * @return $this
     * @throws FireflyException
     */
    public function update(Category $category)
    {
        $data            = Input::except('_token');
        $data['user_id'] = Auth::user()->id;

        // always validate:
        $messages = $this->_repository->validate($data);

        // flash messages:
        Session::flash('warnings', $messages['warnings']);
        Session::flash('successes', $messages['successes']);
        Session::flash('errors', $messages['errors']);
        if ($messages['errors']->count() > 0) {
            Session::flash('error', 'Could not update category: ' . $messages['errors']->first());
            return Redirect::route('categories.edit', $category->id)->withInput();
        }

        // return to update screen:
        if ($data['post_submit_action'] == 'validate_only') {
            return Redirect::route('categories.edit', $category->id)->withInput();
        }

        // update
        $this->_repository->update($category, $data);
        Session::flash('success', 'Category "' . e($data['name']) . '" updated.');

        // go back to list
        if ($data['post_submit_action'] == 'update') {
            return Redirect::route('categories.index');
        }

        // go back to update screen.
        return Redirect::route('categories.edit', $category->id)->withInput(['post_submit_action' => 'return_to_edit']);


    }


}
