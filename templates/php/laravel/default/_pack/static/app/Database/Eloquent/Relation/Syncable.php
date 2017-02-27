<?php

namespace App\Database\Eloquent\Relation;

interface Syncable
{
	public function syncRecords($data);
}