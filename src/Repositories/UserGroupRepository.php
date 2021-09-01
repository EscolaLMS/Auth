<?php

namespace EscolaLms\Auth\Repositories;

use EscolaLms\Auth\Models\Group;
use EscolaLms\Auth\Repositories\Contracts\UserGroupRepositoryContract;
use EscolaLms\Core\Repositories\BaseRepository;

class UserGroupRepository extends BaseRepository implements UserGroupRepositoryContract
{
    /**
     * @var array
     */
    protected $fieldSearchable = [];

    /**
     * @return array
     */
    public function getFieldsSearchable()
    {
        return $this->fieldSearchable;
    }

    public function model()
    {
        return Group::class;
    }
}
