<?php

namespace App\Database\Eloquent;

use Illuminate\Database\Eloquent\Model as BaseModel;
use App\Database\Eloquent\Relation\Syncable;
use App\Database\Eloquent\Relation\SyncableHasOne;
use App\Database\Eloquent\Relation\SyncableHasMany;
use App\Database\Eloquent\Relation\SyncableBelongsToMany;

class Model extends BaseModel
{
	protected $late_binding = [];
	
	public function newEloquentBuilder($query)
	{
		return new Builder($query);
	}
	
	public function save(array $options = [])
    {
		$to_sync = [];
		
		foreach ($this->late_binding as $name => $data) {
			if (method_exists($this, $name)) {
				$relation = $this->$name();
				if ($relation instanceof Syncable) {
					$to_sync[$name] = $relation;
				}
			}
		}
		
		if (parent::save($options)) {
			foreach ($to_sync as $name => $relation) {
				$relation->syncRecords($this->late_binding[$name]);
			}
			$this->late_binding = [];
			return true;
		}
		
		return false;
	}
	
	public function addLateBinding($name, $data)
	{
		$this->late_binding[$name] = $data;
	}
	
	public function removeLateBinding($name)
	{
		unset($this->late_binding[$name]);
	}
	
	public function hasOne($related, $foreignKey = null, $localKey = null)
    {
        $foreignKey = $foreignKey ?: $this->getForeignKey();
        $instance = new $related;
        $localKey = $localKey ?: $this->getKeyName();
        
        return new SyncableHasOne($instance->newQuery(), $this, $instance->getTable().'.'.$foreignKey, $localKey);
    }
    
    public function hasMany($related, $foreignKey = null, $localKey = null)
    {
        $foreignKey = $foreignKey ?: $this->getForeignKey();
        $instance = new $related;
        $localKey = $localKey ?: $this->getKeyName();
        
        return new SyncableHasMany($instance->newQuery(), $this, $instance->getTable().'.'.$foreignKey, $localKey);
    }
    
    public function belongsToMany($related, $table = null, $foreignKey = null, $otherKey = null, $relation = null)
    {
        if (is_null($relation)) {
            $relation = $this->getBelongsToManyCaller();
        }
        
        $foreignKey = $foreignKey ?: $this->getForeignKey();
        $instance = new $related;
        $otherKey = $otherKey ?: $instance->getForeignKey();
        
        if (is_null($table)) {
            $table = $this->joiningTable($related);
        }
        
        $query = $instance->newQuery();
        
        return new SyncableBelongsToMany($query, $this, $table, $foreignKey, $otherKey, $relation);
    }
}