<?php

namespace Packages\Http\Controllers\Admin;

use Illuminate\Routing\Controller;
use Packages\Http\Requests\Admin\Package\CategoryCommissionDeleteRequest;
use Packages\Http\Requests\Admin\Package\CategoryCommissionEditRequest;
use Packages\Http\Requests\Admin\Package\CategoryCreateRequest;
use Packages\Http\Requests\Admin\Package\CategoryDeleteRequest;
use Packages\Http\Requests\Admin\Package\CategoryEditRequest;
use Packages\Http\Resources\CategoryResource;
use Packages\Services\CategoryService;

class CategoryController extends Controller
{
    private $category_service;

    public function __construct(CategoryService $category_service)
    {
        $this->category_service = $category_service;
    }
    /**
     * Show All Category
     * @group
     * Admin User > Categories
     */
    public function index()
    {
        $categories = $this->category_service->getAll();
        return api()->success('packages.successfully-get-category',CategoryResource::collection($categories));
    }


    /**
     * Create Category
     * @group
     * Admin User > Categories
     */
    public function create(CategoryCreateRequest $request)
    {
        $this->category_service->create($request);
        return api()->success('packages.successfully-created-category');
    }


    /**
     * Edit Category
     * @group
     * Admin User > Categories
     */
    public function edit(CategoryEditRequest $request)
    {
        $this->category_service->edit($request);
        return api()->success('packages.successfully-edited-category');
    }

    /**
     * Delete Category
     * @group
     * Admin User > Categories
     */
    public function delete(CategoryDeleteRequest $request)
    {
        $this->category_service->delete($request->get('key'));
        return api()->success('packages.successfully-deleted-category');
    }
    /**
     * Add or Edit Category commission
     * @group
     * Admin User > Categories
     */
    public function editCommission(CategoryCommissionEditRequest $request)
    {
        $this->category_service->addOrEditCategoryCommission($request->get('key'),$request->get('level'),$request->get('percentage'));
        return api()->success('packages.successfully-edited-commission');
    }

    /**
     * Delete Category commission
     * @group
     * Admin User > Categories
     */
    public function deleteCommission(CategoryCommissionDeleteRequest $request)
    {
         $this->category_service->deleteCategoryCommission($request->get('key'),$request->get('level'));
        return api()->success('packages.successfully-deleted');
    }


}
