<?php

namespace DaydreamLab\Cms\Services\Module\Admin;

use DaydreamLab\Cms\Repositories\Module\Admin\ModuleAdminRepository;
use DaydreamLab\Cms\Services\Module\ModuleService;

class ModuleAdminService extends ModuleService
{
    protected $type = 'ModuleAdmin';

    public function __construct(ModuleAdminRepository $repo)
    {
        parent::__construct($repo);
    }
}
