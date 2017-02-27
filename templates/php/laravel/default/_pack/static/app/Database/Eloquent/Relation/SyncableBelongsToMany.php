<?php

namespace App\Database\Eloquent\Relation;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class SyncableBelongsToMany extends BelongsToMany implements Syncable
{
	public function syncRecords($data)
	{
		$this->sync($data);
	}
}