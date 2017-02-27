<?php

namespace App\Database\Eloquent\Relation;

use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Model;

class SyncableHasOne extends HasOne implements Syncable
{
	public function syncRecords($data)
	{
		if ($data == null) {
			if (!$this->parent->wasRecentlyCreated) {
				$model = $this->query->first();
				if ($model != null) {
					$model->delete();
				}
			}
		} else {
			if ($data instanceof Model) {
				$model = $data;
			} else {
				$model = $this->query->first();
				if ($model == null) {
					$model = $this->related->newInstance($data);
				} else {
					$model->fill($data);
				}
			}
			$this->save($model);
		}
	}
}