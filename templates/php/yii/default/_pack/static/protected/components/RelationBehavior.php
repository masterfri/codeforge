<?php

class RelationBehavior extends CActiveRecordBehavior
{
	public $attributes;
	protected $commandBuilder;
	
	public function beforeSave()
	{
		foreach($this->getOwner()->relations() as $name => $relation) {
			if ($this->getOwner()->hasRelated($name)) {
				$params = $this->getAttributeRelationParams($name);
				if (false !== $params) {
					switch ($relation[0]) {
						case CActiveRecord::BELONGS_TO:
							$this->saveBelongsToRelation($name, $relation);
							break;
					}
				}
			}
		}
	}
	
	public function afterSave()
	{
		foreach($this->getOwner()->relations() as $name => $relation) {
			if ($this->getOwner()->hasRelated($name)) {
				$params = $this->getAttributeRelationParams($name);
				if (false !== $params) {
					switch ($relation[0]) {
						case CActiveRecord::MANY_MANY:
							$this->saveManyManyRelation($name, $relation, $params);
							break;
						case CActiveRecord::HAS_ONE:
							$this->saveHasOneRelation($name, $relation, $params);
							break;
						case CActiveRecord::HAS_MANY:
							$this->saveHasManyRelation($name, $relation, $params);
							break;
					}
				}
			}
		}
	}
	
	public function beforeDelete()
	{
		foreach($this->getOwner()->relations() as $name => $relation) {
			$params = $this->getAttributeRelationParams($name);
			if (false !== $params) {
				switch ($relation[0]) {
					case CActiveRecord::MANY_MANY:
						$this->deleteManyManyRelation($relation);
						break;
					case CActiveRecord::HAS_ONE:
						$this->deleteHasOneRelation($relation, $params);
						break;
					case CActiveRecord::HAS_MANY:
						$this->deleteHasManyRelation($relation, $params);
						break;
				}
			}
		}
	}
	
	protected function saveBelongsToRelation($name, $relation)
	{
		$related = $this->getOwner()->getRelated($name, false);
		if ($related instanceof CActiveRecord) {
			if ($related->getIsNewRecord()) {
				throw new Exception('Record must be saved before linking');
			}
			$this->getOwner()->{$relation[2]} = $related->getPrimaryKey();
		} elseif ($related) {
			$this->getOwner()->{$relation[2]} = $related;
		} else {
			$this->getOwner()->{$relation[2]} = new CDbExpression('NULL');
		}
	}
	
	protected function saveManyManyRelation($name, $relation, $params)
	{
		list($relationTable, $thisfk, $otherfk) = $this->parseManyManyRelation($relation);
		$new = $this->getNewManyManyKeys($name, isset($params['cascadeSave']) && $params['cascadeSave']);
		$old = $this->getOldManyManyKeys($relationTable, $thisfk, $otherfk);
		$this->deleteManyManyLinks($relationTable, $thisfk, $otherfk, array_intersect($new, $old));
		$this->addManyManyLinks($relationTable, $thisfk, $otherfk, array_diff($new, $old));
	}
	
	protected function deleteManyManyRelation($relation)
	{
		list($relationTable, $thisfk, $otherfk) = $this->parseManyManyRelation($relation);
		$this->deleteManyManyLinks($relationTable, $thisfk, $otherfk);
	}
	
	protected function saveHasOneRelation($name, $relation, $params)
	{
		$related = $this->getOwner()->getRelated($name, false);
		if ($related) {
			$except = $related->getIsNewRecord() ? array() : array($related->getPrimaryKey());
			$this->deleteChildrenRelation($relation, $params, $except);
			$this->saveChildrenRelation(array($related), $relation, $params);
		} else {
			$this->deleteChildrenRelation($relation, $params);
		}
	}
	
	protected function deleteHasOneRelation($relation, $params)
	{
		$this->deleteChildrenRelation($relation, $params);
	}
	
	protected function saveHasManyRelation($name, $relation, $params)
	{
		$related = $this->getOwner()->getRelated($name, false);
		if (is_array($related)) {
			$except = array();
			foreach ($related as $item) {
				if (!$item->getIsNewRecord()) {
					$except[] = $item->getPrimaryKey();
				}
			}
			if (isset($params['cascadeDelete']) && $params['cascadeDelete']) {
				$this->deleteChildrenRelation($relation, $params, $except);
			} else {
				$this->detachChildrenRelation($relation, $except);
			}
			$this->saveChildrenRelation($related, $relation, $params);
		} elseif (isset($params['cascadeDelete']) && $params['cascadeDelete']) {
			$this->deleteChildrenRelation($relation, $params);
		} else {
			$this->detachChildrenRelation($relation);
		}
	}
	
	protected function deleteHasManyRelation($relation, $params)
	{
		if (isset($params['cascadeDelete']) && $params['cascadeDelete']) {
			$this->deleteChildrenRelation($relation, $params);
		} else {
			$this->detachChildrenRelation($relation);
		}
	}
	
	protected function saveChildrenRelation($related, $relation, $params)
	{
		$pk = $this->getOwner()->getPrimaryKey();
		if (isset($params['cascadeSave']) && $params['cascadeSave']) {
			foreach ($related as $item) {
				$item->{$relation[2]} = $pk;
				if (!$item->save()) {
					throw new Exception('Can not save related record');
				}
			}
		} else {
			foreach ($related as $item) {
				if ($item->getIsNewRecord()) {
					throw new Exception('Record must be saved before linking');
				} elseif ($item->{$relation[2]} != $pk) {
					$item->updateByPk($item->getPrimaryKey(), array(
						$relation[2] => $pk,
					));
				}
			}
		}
	}
	
	protected function detachChildrenRelation($relation, $except=array())
	{
		$criteria = $this->getChildrenRelationCriteria($relation, $except);
		CActiveRecord::model($relation[1])->updateAll(array(
			$relation[2] => new CDbExpression('NULL'),
		), $criteria);
	}
	
	protected function deleteChildrenRelation($relation, $params, $except=array())
	{
		$criteria = $this->getChildrenRelationCriteria($relation, $except);
		if (isset($params['quickDelete']) && $params['quickDelete']) {
			CActiveRecord::model($relation[1])->deleteAll($criteria);
		} else {
			$model = CActiveRecord::model($relation[1]);
			$iterator = $this->getCommandBuilder()->createFindCommand($model->tableName(), $criteria)->query();
			while ($row = $iterator->read()) {
				$model->populateRecord($row)->delete();
			}
		}
	}
	
	protected function getChildrenRelationCriteria($relation, $except=array())
	{
		$criteria = new CDbCriteria(array_slice($relation, 3));
		$criteria->compare($relation[2], $this->owner->getPrimaryKey());
		$criteria->addNotInCondition(CActiveRecord::model($relation[1])->getTableSchema()->primaryKey, $except);
		return $criteria;
	}
	
	protected function parseManyManyRelation($relation)
	{
		if(!preg_match('/^\s*(.*?)\((.*)\)\s*$/', $relation[2], $matches)) {
			throw new CException('Invalid foreign key definition');
		}
		$foreignKeys = preg_split('/\s*,\s*/', $matches[2], -1, PREG_SPLIT_NO_EMPTY);
		if (count($foreignKeys) != 2) {
			throw new CException('Composite keys is not supportned yet');
		}
		return array($matches[1], $foreignKeys[0], $foreignKeys[1]);
	}
	
	protected function getNewManyManyKeys($name, $cascadeSave)
	{
		$result = array();
		$related = $this->getOwner()->getRelated($name, false);
		if (!empty($related)) {
			foreach ($related as $item) {
				if ($item instanceof CActiveRecord) {
					if ($item->getIsNewRecord()) {
						if ($cascadeSave) {
							if ($item->save()) {
								$result[] = $item->getPrimaryKey();
							} else {
								throw new Exception('Can not save related record');
							}
						} else {
							throw new Exception('Record must be saved before linking');
						}
					} else {
						$result[] = $item->getPrimaryKey();
					}
				} else {
					$result[] = $item;
				}
			}
		}
		return $result;
	}
	
	protected function getOldManyManyKeys($relationTable, $thisfk, $otherfk)
	{
		$criteria = new CDbCriteria();
		$criteria->compare($thisfk, $this->getOwner()->getPrimaryKey());
		$criteria->select = $otherfk;
		return $this->getCommandBuilder()->createFindCommand($relationTable, $criteria)->queryColumn();
	}
	
	protected function deleteManyManyLinks($relationTable, $thisfk, $otherfk, $exclude=array())
	{
		$criteria = new CDbCriteria();
		$criteria->compare($thisfk, $this->getOwner()->getPrimaryKey());
		$criteria->addNotInCondition($otherfk, $exclude);
		$this->getCommandBuilder()->createDeleteCommand($relationTable, $criteria)->execute();
	}
	
	protected function addManyManyLinks($relationTable, $thisfk, $otherfk, $items)
	{
		$commandBuilder = $this->getCommandBuilder();
		$pk = $this->getOwner()->getPrimaryKey();
		foreach ($items as $item) {
			$commandBuilder->createInsertCommand($relationTable, array(
				$thisfk => $pk,
				$otherfk => $item,
			))->execute();
		}
	}
	
	protected function getAttributeRelationParams($attribute)
	{
		if (isset($this->attributes[$attribute])) {
			return $this->attributes[$attribute];
		} elseif (false !== array_search($attribute, $this->attributes)) {
			return array();
		} else {
			return false;
		}
	}
	
	protected function getCommandBuilder()
	{
		if (null === $this->commandBuilder) {
			$this->commandBuilder = $this->getOwner()->dbConnection->commandBuilder;
		}
		return $this->commandBuilder;
	}
}
