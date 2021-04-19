<?php

namespace EscolaLms\Auth\Repositories\Contracts;

use EscolaLms\Auth\Dtos\Contracts\ModelKeysDtoContract;
use EscolaLms\Core\Dtos\Contracts\DtoContract;
use Illuminate\Database\Eloquent\Model;

interface RepositoryPatchableUsingDtoContract
{
    public function patchUsingDto(DtoContract $dto, ModelKeysDtoContract $keysDto, int $id): Model;
}
