<?php

namespace App\Database\Eloquent\Relation;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SyncableHasMany extends HasMany implements Syncable
{
	public function syncRecords($data)
	{
		if ($data instanceof Collection) {
			$items = $data;
		} else {
			$items = new Collection();
			foreach ($data as $item) {
				if ($item instanceof Model) {
					$items->add($item);
				} else {
					$items->add($this->related->newInstance($item));
				}
			}
		}
		
		if (!$this->parent->wasRecentlyCreated) {
			$except_delete = [];
			foreach ($items as $item) {
				if ($item->exists) {
					$except_delete[] = $item->getKey();
				}
			}
			$query = clone $this->query;
			if (count($except_delete)) {
				$query->whereNotIn($this->related->getKeyName(), $except_delete);
			}
			foreach ($query->cursor() as $item) {
				$item->delete();
			}
		}
		
		$this->saveMany($items);
	}
}