<?php

namespace App\Database\Eloquent\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOneOrMany;
use Illuminate\Support\Str;
use LogicException;

trait HasRelationAttributes
{
    /**
     * Relations to be comitted after model is saved
     * 
     * @var array
     */ 
    protected $relationsToCommit = [];
    
    /**
     * Relations which cascade delete should be performed on
     * 
     * @var array
     */ 
    protected $cascadeDelete = [];

    /**
     * Set a given attribute on the model.
     *
     * @param  string  $key
     * @param  mixed  $value
     * @return $this
     */
    public function setAttribute($key, $value)
    {
        if ($this->hasSetMutator($key)) {
            $method = 'set'.Str::studly($key).'Attribute';
            return $this->{$method}($value);
        } elseif ($value && $this->isDateAttribute($key)) {
            $value = $this->fromDateTime($value);
        }

        if ($this->isJsonCastable($key) && ! is_null($value)) {
            $value = $this->castAttributeAsJson($key, $value);
        }
        
        if (Str::contains($key, '->')) {
            return $this->fillJsonAttribute($key, $value);
        }
        
        if (!method_exists(self::class, $key) && method_exists($this, $key)) {
            $this->makeRelationship($key, $value);
        } else {
            $this->attributes[$key] = $value;
        }

        return $this;
    }
    
    /**
     * Set relation value on model
     * 
     * @param string $key
     * @param mixed $value 
     * @return void
     */
    protected function makeRelationship($key, $value)
    {
        $relation = $this->$key();

        if (! $relation instanceof Relation) {
            throw new LogicException('Relationship method must return an object of type Illuminate\Database\Eloquent\Relations\Relation');
        }
        
        if ($relation instanceof BelongsTo) {
            $this->makeBelongsToRelationship($relation, $key, $value);
        } elseif ($relation instanceof BelongsToMany) {
            $this->makeBelongsToManyRelationship($relation, $key, $value);
        } elseif ($relation instanceof HasOne) {
            $this->makeHasOneRelationship($relation, $key, $value);
        } elseif ($relation instanceof HasMany) {
            $this->makeHasManyRelationship($relation, $key, $value);
        }
    }
    
    /**
     * Set value for "belongs to" relation on model
     * 
     * @param Illuminate\Database\Eloquent\Relations\Relation $relation
     * @param string $key
     * @param mixed $value 
     * @return void
     */
    protected function makeBelongsToRelationship($relation, $key, $value)
    {
        $value = $this->getSafeRelationValue($relation, $value);
        if ($value !== null) {
            $relation->associate($value);
        } else {
            $relation->dissociate();
        }
    }
    
    /**
     * Set value for "belongs to many" relation on model
     * 
     * @param Illuminate\Database\Eloquent\Relations\Relation $relation
     * @param string $key
     * @param mixed $value 
     * @return void
     */
    protected function makeBelongsToManyRelationship($relation, $key, $value)
    {
        $value = $this->getSafeRelationValues($relation, $value);
        $this->setRelation($key, $value);
        $this->relationsToCommit[$key] = $relation;
    }
    
    /**
     * Set value for "has one" relation on model
     * 
     * @param Illuminate\Database\Eloquent\Relations\Relation $relation
     * @param string $key
     * @param mixed $value 
     * @return void
     */
    protected function makeHasOneRelationship($relation, $key, $value)
    {
        $value = $this->getSafeRelationValue($relation, $value);
        $this->setRelation($key, $value);
        $this->relationsToCommit[$key] = $relation;
    }
    
    /**
     * Set value for "has many" relation on model
     * 
     * @param Illuminate\Database\Eloquent\Relations\Relation $relation
     * @param string $key
     * @param mixed $value 
     * @return void
     */
    protected function makeHasManyRelationship($relation, $key, $value)
    {
        $value = $this->getSafeRelationValues($relation, $value);
        $this->setRelation($key, $value);
        $this->relationsToCommit[$key] = $relation;
    }
    
    /**
     * Convert mixed value to model
     * 
     * @param Illuminate\Database\Eloquent\Relations\Relation $relation
     * @param mixed $value 
     * @return Illuminate\Database\Eloquent\Model
     */
    protected function getSafeRelationValue($relation, $value)
    {
        if (empty($value)) {
            return null;
        } elseif ($value instanceof Model) {
            return $value;
        } elseif (is_array($value)) {
            $pk = $relation->getRelated()->getKeyName();
            if (isset($value[$pk])) {
                $instance = $relation->getQuery()->findOrNew($value[$pk]);
                $instance->fill(array_except($value, [$pk]));
            } else {
                $instance = $relation->getQuery()->newModelInstance($value);
            }
            return $instance;
        } else {
            return $relation->getRelated()->find($value);
        }
    }
    
    /**
     * Convert mixed value to collection of models
     * 
     * @param Illuminate\Database\Eloquent\Relations\Relation $relation
     * @param mixed $value 
     * @return Illuminate\Database\Eloquent\Collection
     */
    protected function getSafeRelationValues($relation, $value)
    {
        if (empty($value)) {
            return new Collection();
        } elseif ($value instanceof Collection) {
            return $value;
        } else {
            $result = new Collection();
            foreach ($value as $item) {
                $v = $this->getSafeRelationValue($relation, $item);
                if ($v !== null) {
                    $result->push($v);
                }
            }
            return $result;
        }
    }

    /**
     * Commit relation values
     * 
     * @return void
     */ 
    protected function commitRelations()
    {
        foreach ($this->relationsToCommit as $key => $relation) {
            if ($this->relationLoaded($key)) {
                $related = $this->getRelation($key);
                if ($relation instanceof BelongsToMany) {
                    $this->commitBelongsToManyRelation($relation, $key, $related);
                } elseif ($relation instanceof HasOne) {
                    $this->commitHasOneRelation($relation, $key, $related);
                } elseif ($relation instanceof HasMany) {
                    $this->commitHasManyRelation($relation, $key, $related);
                }
            }
        }
        $this->relationsToCommit = [];
    }
    
    /**
     * Commit values for "belongs to many" relation
     * 
     * @return void
     */ 
    protected function commitBelongsToManyRelation($relation, $key, $related)
    {
        foreach ($related as $item) {
            if (!$item->exists) {
                $item->save();
            }
        }
        $relation->sync($related);
    }
    
    /**
     * Commit values for "has one" relation
     * 
     * @return void
     */ 
    protected function commitHasOneRelation($relation, $key, $related)
    {
        $old_related = $this->wasRecentlyCreated ? null : $relation->getQuery()->first();
        if ($related === null) {
            if ($old_related !== null) {
                $old_related->delete();
            }
        } else {
            if ($old_related !== null && $old_related->getKey() != $related->getKey()) {
                $old_related->delete();
            }
            $relation->save($related);
        }
    }
    
    /**
     * Commit values for "has many" relation
     * 
     * @return void
     */ 
    protected function commitHasManyRelation($relation, $key, $related)
    {
        if (!$this->wasRecentlyCreated) {
            $preserve_ids = [];
            foreach ($related as $item) {
                if ($item->exists) {
                    $preserve_ids[] = $item->getKey();
                }
            }
            $query = clone $relation->getQuery();
            if (count($preserve_ids)) {
                $query->whereNotIn($query->getModel()->getKeyName(), $preserve_ids);
            }
            foreach ($query->cursor() as $item) {
                $item->delete();
            }
        }
        $relation->saveMany($related);
    }
    
    /**
     * Perform cascade delete operation
     * 
     * @return void
     */ 
    protected function performCascadeDelete()
    {
        foreach ($this->cascadeDelete as $key) {
            $relation = $this->$key();
                
            if (! $relation instanceof Relation) {
                throw new LogicException('Relationship method must return an object of type Illuminate\Database\Eloquent\Relations\Relation');
            }
            
            if ($relation instanceof BelongsTo) {
                throw new LogicException('Cascade delete operation can not be performed on relations of type "belongs to"');
            } elseif ($relation instanceof BelongsToMany) {
                $relation->detach();
            } elseif ($relation instanceof HasOneOrMany) {
                $this->deleteChildrenRecords($relation);
            }
        }
    }
    
    /**
     * Delete children records on model
     * 
     * @param Illuminate\Database\Eloquent\Relations\HasOneOrMany $relation
     * @return void
     */ 
    protected function deleteChildrenRecords($relation)
    {
        foreach ($relation->getQuery()->cursor() as $item) {
            $item->delete();
        }
    }
}