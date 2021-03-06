<?php

namespace DaydreamLab\Cms\Services\Category\Admin;

use DaydreamLab\Cms\Models\Category\Category;
use DaydreamLab\Cms\Repositories\Category\Admin\CategoryAdminRepository;
use DaydreamLab\Cms\Services\Category\CategoryService;
use DaydreamLab\JJAJ\Helpers\Helper;
use DaydreamLab\JJAJ\Helpers\InputHelper;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class CategoryAdminService extends CategoryService
{
    protected $type = 'CategoryAdmin';

    public function __construct(CategoryAdminRepository $repo)
    {
        parent::__construct($repo);
    }


    public function filterItems($items, $limit)
    {
        $user = Auth::guard('api')->user();
        $viewlevels = $user->viewlevels;

        // Super User
        if (!in_array(5, $viewlevels))
        {
            $items = $items->filter(function ($value, $key) use ($viewlevels){
                foreach ($viewlevels as $viewlevel)
                {
                    if(in_array($viewlevel, $value->viewlevels))
                    {
                        return $value;
                    }
                }
                return false;
            });
        }

        return $this->repo->paginate($items, $limit);
    }


    public function getItem($id)
    {
        $item = parent::getItem($id);

        if ($item->locked_by && $item->locked_by != $this->user->id)
        {
            $this->status   = Str::upper(Str::snake($this->type.'IsLocked'));
            $this->response = (object) $this->user->only('email', 'full_name', 'nickname');
            return false;
        }

        $item->locked_by = $this->user->id;
        $item->locked_at = now();

        return $item->save();
    }


    public function store(Collection $input)
    {
        if (InputHelper::null($input, 'alias')){
            $input->forget('alias');
            $input->put('alias', now()->format('Y-m-d-H-i-ss'));
        }


        if (InputHelper::null($input, 'parent_id')) {
            $input->put('path', '/'.$input->get('alias'));
        }
        else {
            $parent = $this->find($input->parent_id);
            $input->put('path', $parent->path . '/' .$input->get('alias'));
        }


        if (InputHelper::null($input, 'extension')){
            $input->forget('extension');
            $input->put('extension', 'item');
        }

        if (InputHelper::null($input, 'language')){
            $input->forget('language');
            $input->put('language', 'All');
        }

        return parent::storeNested($input);
    }


    public function search(Collection $input)
    {
        if (InputHelper::null($input, 'extension'))
        {
            $input->forget('extension');
            $input->put('extension', 'item');
        }
        return parent::search($input);
    }


}
