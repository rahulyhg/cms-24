<?php
namespace DaydreamLab\Cms\Models\Category;

use Carbon\Carbon;
use DaydreamLab\Cms\Models\Extrafield\Extrafield;
use DaydreamLab\JJAJ\Helpers\Helper;
use DaydreamLab\JJAJ\Models\BaseModel;
use DaydreamLab\User\Models\User\UserGroup;
use DaydreamLab\User\Models\Viewlevel\Viewlevel;
use Kalnoy\Nestedset\NodeTrait;

class Category extends BaseModel
{
    use NodeTrait;
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'categories';

    protected $order = 'asc';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title',
        'alias',
        'state',
        'path',
        'state',
        'introimage',
        'introtext',
        'image',
        'description',
        'extension',
        'hits',
        'access',
        'ordering',
        'language',
        'content_type',
        'template',
        'metadesc',
        'metakeywords',
        'params',
        'extrafields',
        'created_by',
        'updated_by',
        'locked_by',
        'locked_at',
        'publish_up',
        'publish_down',
    ];


    /**
     * The attributes that should be hidden for arrays
     *
     * @var array
     */
    protected $hidden = [
        '_lft',
        '_rgt',
        'ancestors'
    ];


    /**
     * The attributes that should be append for arrays
     *
     * @var array
     */
    protected $appends = [
        'creator',
        'updater',
        'locker',
        'tree_title',
        'tree_list_title'
        //'viewlevels',
    ];


    protected $casts = [
        'locked_at'     => 'datetime:Y-m-d H:i:s',
        'extrafields'   => 'array'
    ];


    public function viewlevel()
    {
        return $this->hasOne(Viewlevel::class, 'id', 'access');
    }


    public function getExtrafieldsAttribute($value)
    {
        $data = [];
        foreach (json_decode($value) as $extra_field)
        {
            $extra_field_data = Extrafield::find($extra_field->id);
            $extra_field_data->value = $extra_field->value ;
            $data[] = $extra_field_data->toArray();
        }

        return $data;
    }


    public function getViewlevelsAttribute()
    {
        $rules = $this->viewlevel()->first()->rules;
        $canAccess = [];
        foreach ($rules as $rule)
        {
            $group =   UserGroup::find($rule);
            if (!in_array($rule, $canAccess) && $group->title!= 'ROOT') {
                $canAccess[] = $rule;
            }

            foreach ($group->ancestors as $ancestor)
            {
                if (!in_array($ancestor->id, $canAccess) && $ancestor->title != 'ROOT') {
                    $canAccess[] = $ancestor->id;
                }
            }
        }
        return $canAccess;
    }
}