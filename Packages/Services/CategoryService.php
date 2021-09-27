<?php


namespace Packages\Services;

use Packages\Repository\CategoryRepository;

class CategoryService
{
    private $category_repository;

    public function __construct(CategoryRepository $category_repository)
    {
        $this->category_repository = $category_repository;
    }


    public function addOrEditCategoryCommission($key, $level, $percentage)
    {
        return $this->category_repository->addOrEditCategoryCommission($key, $level, $percentage);
    }

    public function deleteCategoryCommission($key, $level)
    {
        $this->category_repository->deleteCategoryCommission($key,$level);
    }

    public function create(\Packages\Http\Requests\Admin\Package\CategoryCreateRequest $request)
    {
        $this->category_repository->create($request);
    }

    public function edit(\Packages\Http\Requests\Admin\Package\CategoryEditRequest $request)
    {
        $this->category_repository->edit($request);
    }

    public function delete($key)
    {
        $this->category_repository->delete($key);
    }

    public function getAll()
    {
        return $this->category_repository->getAll();
    }


}
