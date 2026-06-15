<?php

// app/Http/Controllers/API/ExtendedCategoryController.php

namespace App\Http\Controllers\API;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;
use Webkul\Attribute\Repositories\AttributeRepository;
use Webkul\Category\Repositories\CategoryRepository;
use Webkul\Product\Models\ProductAttributeValue;
use Webkul\Product\Repositories\ProductRepository;
use Webkul\Shop\Http\Controllers\API\CategoryController as BaseController;
use Webkul\Shop\Http\Resources\AttributeResource;

class ExtendedCategoryController extends BaseController
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        protected AttributeRepository $attributeRepository,
        protected CategoryRepository $categoryRepository,
        protected ProductRepository $productRepository
    ) {
        parent::__construct($attributeRepository, $categoryRepository, $productRepository);
    }

    /**
     * Get filterable attributes for category.
     */
    public function getAttributes(): JsonResource
    {
        // If no category_id provided, return all filterable attributes
        if (! request('category_id')) {
            $filterableAttributes = $this->attributeRepository->getFilterableAttributes();

            return AttributeResource::collection($filterableAttributes);
        }

        $categoryId = request('category_id');
        $category = $this->categoryRepository->findOrFail($categoryId);

        // Get filterable attributes for the category
        if (empty($filterableAttributes = $category->filterableAttributes)) {
            $filterableAttributes = $this->attributeRepository->getFilterableAttributes();
        }

        // Get product IDs in this category
        $productIds = DB::table('product_categories')
            ->where('category_id', $categoryId)
            ->pluck('product_id');

        // If no products found in category, return original attributes
        if ($productIds->isEmpty()) {
            return AttributeResource::collection($filterableAttributes);
        }

        // Filter attributes that have at least one value set by products in the category
        $usedAttributes = collect();

        foreach ($filterableAttributes as $attribute) {
            $columnName = $attribute->attributeTypeFields[$attribute->type];

            // Get used values for this attribute in the category
            $attributeValues = ProductAttributeValue::where('attribute_id', $attribute->id)
                ->whereIn('product_id', $productIds)
                ->whereNotNull($columnName)
                ->where($columnName, '!=', '')
                ->select($columnName)
                ->distinct()
                ->get()
                ->pluck($columnName)
                ->filter()
                ->toArray();

            // Skip attributes with no values
            if (empty($attributeValues)) {
                continue;
            }

            // For select and multiselect types, filter the options
            if (in_array($attribute->type, ['select', 'multiselect'])) {
                // For multiselect, values are stored as comma-separated strings
                if ($attribute->type == 'multiselect') {
                    $usedOptionIds = [];

                    foreach ($attributeValues as $value) {
                        $valueIds = explode(',', $value);
                        $usedOptionIds = array_merge($usedOptionIds, $valueIds);
                    }

                    $usedOptionIds = array_unique(array_filter($usedOptionIds));
                } else {
                    // For select, values are stored as integers
                    $usedOptionIds = $attributeValues;
                }

                // Filter to include only used options
                $filteredOptions = $attribute->options()->whereIn('id', $usedOptionIds)->get();

                // Clone attribute to avoid modifying the original
                $attributeClone = clone $attribute;

                // Replace options with filtered ones
                $attributeClone->setRelation('options', $filteredOptions);

                $usedAttributes->push($attributeClone);
            } else {
                // For other attribute types, just add the attribute
                $usedAttributes->push($attribute);
            }
        }

        return AttributeResource::collection($usedAttributes);
    }
}
