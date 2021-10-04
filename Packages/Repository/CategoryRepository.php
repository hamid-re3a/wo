<?php


namespace Packages\Repository;

use Packages\Models\Category;

class CategoryRepository
{
    protected $entity_name = Category::class;

    public function create(\Packages\Http\Requests\Admin\Package\CategoryCreateRequest $request)
    {
        return (new $this->entity_name)->create([
            "key" => $request->get('key'),
            "name" => $request->get('name'),
            "short_name" => $request->get('short_name'),
            "roi_percentage" => $request->get('roi_percentage'),
            "direct_percentage"=> $request->get('direct_percentage'),
            "binary_percentage"=> $request->get('binary_percentage'),
            "package_validity_in_days" => $request->get('validity_in_days'),
        ]);
    }

    public function edit(\Packages\Http\Requests\Admin\Package\CategoryEditRequest $request)
    {
        $category_entity = new $this->entity_name;
        $category_find = $category_entity->where('key',$request->get('key'))->firstOrFail();
        $category_find->name = $request->get('name');
        $category_find->short_name = $request->get('short_name');
        $category_find->roi_percentage = $request->get('roi_percentage');
        $category_find->direct_percentage = $request->get('direct_percentage');
        $category_find->binary_percentage = $request->get('binary_percentage');
        $category_find->package_validity_in_days = $request->get('validity_in_days');
        $category_find->save();
        return $category_find;
    }


    public function delete($key)
    {
        $category_entity = new $this->entity_name;
        $category_find = $category_entity->where('key',$key)->firstOrFail();
        $category_find->delete();
        return;

    }

    public function addOrEditCategoryCommission($key, $level, $percentage)
    {
        /** @var  $category_entity Category */
        $category_entity = new $this->entity_name;
        $category_indirect_commission_entity = $category_entity->where('key',$key)->firstOrFail()
            ->categoryIndirectCommission()->firstOrCreate(['level' => $level]);
        $category_indirect_commission_entity->update(['percentage' => $percentage]);
        return $category_indirect_commission_entity;
    }

    public function deleteCategoryCommission($key, $level)
    {
        /** @var  $category_entity Category */
        $category_entity = new $this->entity_name;
        $category_indirect_commission_entity = $category_entity->where('key',$key)->firstOrFail()
            ->categoryIndirectCommission()->firstOrCreate(['level' => $level]);
        $category_indirect_commission_entity->delete();

    }

    public function getAll()
    {
        /** @var  $category_entity Category */
        $category_entity = new $this->entity_name;
        return $category_entity->get();
    }


}
