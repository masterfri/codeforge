<?php

namespace App\Database\Eloquent;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Builder as BaseBuilder;
use Illuminate\Pagination\Paginator;
use Illuminate\Pagination\LengthAwarePaginator;

class Builder extends BaseBuilder
{
    public function paginate($perPage = null, $columns = ['*'], $pageName = 'page', $page = null)
    {
        $page = $page ?: Paginator::resolveCurrentPage($pageName);
        $perPage = $perPage ?: $this->model->getPerPage();
        $query = $this->toBase();
        $total = $query->getCountForPagination();
        $page = max(1, min($page, ceil($total / $perPage)));
        $results = $total ? $this->forPage($page, $perPage)->get($columns) : new Collection;
        return new LengthAwarePaginator($results, $total, $perPage, $page, [
            'path' => Paginator::resolveCurrentPath(),
            'pageName' => $pageName,
        ]);
    }
}