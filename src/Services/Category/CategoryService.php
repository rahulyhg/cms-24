<?php

namespace DaydreamLab\Cms\Services\Category;

use DaydreamLab\Cms\Repositories\Category\CategoryRepository;
use DaydreamLab\JJAJ\Helpers\Helper;
use DaydreamLab\JJAJ\Services\BaseService;
use DaydreamLab\JJAJ\Traits\NestedServiceTrait;
use Illuminate\Support\Collection;

class CategoryService extends BaseService
{
    use NestedServiceTrait;

    protected $type = 'Category';

    public function __construct(CategoryRepository $repo)
    {
        parent::__construct($repo);
    }


    public function remove(Collection $input)
    {
        return $this->removeNested($input);
    }


    public function store(Collection $input)
    {
        return $this->storeNested($input);
    }
}
