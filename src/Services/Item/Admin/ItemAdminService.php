<?php

namespace DaydreamLab\Cms\Services\Item\Admin;

use DaydreamLab\Cms\Repositories\Item\Admin\ItemAdminRepository;
use DaydreamLab\Cms\Services\Tag\Admin\TagAdminService;
use DaydreamLab\JJAJ\Helpers\Helper;
use DaydreamLab\JJAJ\Helpers\InputHelper;
use DaydreamLab\Cms\Services\Item\ItemService;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class ItemAdminService extends ItemService
{
    protected $type = 'ItemAdmin';

    protected $tagAdminService;

    protected $itemTagMapAdminService;

    public function __construct(ItemAdminRepository $repo,
                                TagAdminService $tagAdminService,
                                ItemTagMapAdminService $itemTagMapAdminService)
    {
        $this->tagAdminService = $tagAdminService;
        $this->itemTagMapAdminService = $itemTagMapAdminService;
        parent::__construct($repo);
    }


    public function findOtherFeatured($id = null)
    {
        return $this->repo->findOtherFeatured($id);
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



    public function featuredOrdering($other)
    {
        return $this->repo->featuredOrdering($other);
    }



    public function store(Collection $input)
    {
        if (InputHelper::null($input, 'alias'))
        {
            $input->forget('alias');
            $input->put('alias', now()->format('Y-m-d-H-i-s'). '-'.Str::random(4));
        }

        if (InputHelper::null($input, 'category_id'))
        {
            $input->forget('category_id');
            $input->put('category_id', 1);
        }

        if (InputHelper::null($input, 'hits'))
        {
            $input->forget('hits');
        }

        if (InputHelper::null($input, 'access'))
        {
            $input->forget('access');
        }


        // featured = 0 or null
        if (InputHelper::null($input, 'featured'))
        {
            $input->forget('featured');
        }
        else // featured = 1
        {
            $input->forget('featured_ordering');
            $input->put('featured_ordering', 1);


            // 編輯文章
            if (InputHelper::null($input, 'id'))
            {
                $other = $this->findOtherFeatured();
            }
            else
            {
                $other = $this->findOtherFeatured($input->id);
            }

            $this->featuredOrdering($other, 'add');
        }

        if (InputHelper::null($input, 'language'))
        {
            $input->forget('language');
            $input->put('language', 'All');
        }
        $tags = $input->get('tags') ? $input->get('tags') : [];
        $input->forget('tags');

        $extrafields = $input->get('extrafields') ? $input->get('extrafields') : [];
        $input->extrafields = json_encode($extrafields);


        $result    =  parent::store($input);
        if (gettype($result) == 'boolean')
        {
            if ($result === true)
            {
                $item      = $this->find($input->id);
            }
            else
            {
                return $result;
            }
        }
        else
        {
            $item      = $this->find($result->id);
        }


        $tag_ids = [];
        foreach ($tags  as $tag)
        {
            if (!array_key_exists('id',$tag) || $tag['id'] == '')
            {
                $tag = $this->tagAdminService->store(Helper::collect($tag));
            }
            $tag_ids[] = $tag['id'];
        }

        $this->itemTagMapAdminService->storeKeysMap(Helper::collect([
            'item_id'   => $item->id,
            'tag_ids'   => $tag_ids
        ]));


        return $item;
    }
}
