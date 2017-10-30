<?php

namespace App\Database\Eloquent;

use Illuminate\Database\Eloquent\Model as BaseModel;
use App\Database\Eloquent\Concerns\HasRelationAttributes;

class Model extends BaseModel
{
    use HasRelationAttributes;

    /**
     * Create a new Eloquent query builder for the model.
     *
     * @param  Illuminate\Database\Query\Builder  $query
     * @return App\Database\Eloquent\Builder
     */
    public function newEloquentBuilder($query)
    {
        return new Builder($query);
    }

    /**
     * Save the model to the database.
     *
     * @param  array  $options
     * @return bool
     */
    public function save(array $options = [])
    {
        if (parent::save($options)) {
            $this->commitRelations();
            return true;
        }
        return false;
    }
    
    /**
     * Perform the actual delete query on this model instance.
     *
     * @return void
     */
    protected function performDeleteOnModel()
    {
        $this->performCascadeDelete();
        parent::performDeleteOnModel();
    }
}