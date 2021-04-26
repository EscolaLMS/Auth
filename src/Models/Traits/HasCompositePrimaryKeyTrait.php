<?php

namespace EscolaLms\Auth\Models\Traits;

use Illuminate\Database\Eloquent\Builder;

trait HasCompositePrimaryKeyTrait
{
    use ExtendableModelTrait;

    /**
     * Set the keys for a save update query.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function setKeysForSaveQuery($query)
    {
        $keys = $this->getTraitOwner()->getKeyName();

        if (!is_array($keys)) {
            return parent::setKeysForSaveQuery($query);
        }

        foreach ($keys as $keyName) {
            $query->where($keyName, '=', $this->getTraitOwner()->getKeyForSaveQuery($keyName));
        }

        return $query;
    }

    /**
     * Get the primary key value for a save query.
     *
     * @param mixed $keyName
     * @return mixed
     */
    protected function getKeyForSaveQuery($keyName = null)
    {
        if (is_null($keyName)) {
            $keyName = $this->getTraitOwner()->getKeyName();
        }

        if (isset($this->getTraitOwner()->original[$keyName])) {
            return $this->getTraitOwner()->original[$keyName];
        }

        return $this->getTraitOwner()->getAttribute($keyName);
    }

    /**
     * Set the keys for a select query.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function setKeysForSelectQuery($query)
    {
        $keys = $this->getTraitOwner()->getKeyName();

        if (!is_array($keys)) {
            return parent::setKeysForSelectQuery($query);
        }

        foreach ($keys as $keyName) {
            $query->where($keyName, '=', $this->getTraitOwner()->getKeyForSelectQuery($keyName));
        }

        return $query;
    }

    /**
     * Get the primary key value for a select query.
     *
     * @param mixed $keyName
     * @return mixed
     */
    protected function getKeyForSelectQuery($keyName = null)
    {
        if (is_null($keyName)) {
            $keyName = $this->getTraitOwner()->getKeyName();
        }

        if (isset($this->getTraitOwner()->original[$keyName])) {
            return $this->getTraitOwner()->original[$keyName];
        }

        return $this->getTraitOwner()->getAttribute($keyName);
    }
}
