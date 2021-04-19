<?php

namespace EscolaLms\Auth\Repositories\Contracts;

use EscolaLms\Core\Dtos\Contracts\DtoContract;
use Illuminate\Database\Eloquent\Model;

interface RepositoryPutableUsingDtoContract
{
    public function putUsingDto(DtoContract $dto, int $id): Model;
}
