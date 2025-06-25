<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Traits\TranslatableResponse;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Permission;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests, TranslatableResponse;

    public function fetch($model, $request, $customModel = null)
    {
        try {
            DB::statement("SET sql_mode = ''");

            // Build base query with all filters, search, and relations
            $baseQuery = $this->buildBaseQuery($model, $request, $customModel);

            // Generate pagination info using the base query
            $pagination = $this->generatePaginationFromQuery($baseQuery, $request);

            // Clone the base query for data retrieval and apply pagination
            $dataQuery = clone $baseQuery;
            $dataQuery->selectRaw($model::getTableName() . ".*");
            $this->applyPagination($dataQuery, $request);

            $data = $dataQuery->get();

            return $this->translateSuccessResponse(null, $data, $pagination);
        } catch (\Exception $e) {
            return $this->translateExceptionResponse($e);
        }
    }

    /**
     * Build base query with all filters, search, and relations applied
     */
    private function buildBaseQuery($model, $request, $customModel = null)
    {
        $query = $model::query();
        if ($customModel) {
            $query = $customModel;
        }

        // Apply relations for joins (affects both count and data queries)
        if (isset($request->with)) {
            foreach ($request->with as $relation) {
                if (is_array($relation) && count($relation) == 3) {
                    $query->leftJoin($relation[0], $relation[1], $relation[2])
                        ->groupBy($model::getTableName() . ".id");
                } else {
                    $query->with($relation);
                }
            }
        }

        // Apply search and filters
        $this->applySearch($query, $request, $request->searchFields);
        $this->applyFilters($query, $request, $model);

        return $query;
    }

    /**
     * Generate pagination info from pre-built query
     */
    public function generatePaginationFromQuery($baseQuery, $request)
    {
        // Parse pagination parameters
        $pagination = array();
        $keys = array();
        if (isset($request->pagination)) {
            $keys = array_keys($request->pagination);
        }

        foreach ($keys as $key) {
            if (in_array($key, ['sortBy'])) {
                $pagination[$key] = $request->pagination[$key];
                continue;
            }
            $pagination[$key] = json_decode($request->pagination[$key]);
        }

        // Get count from the base query (clone to avoid affecting original)
        $countQuery = clone $baseQuery;
        $pagination['rowsNumber'] = $countQuery->toBase()->getCountForPagination();

        // Calculate last page
        $rowsPerPage = isset($pagination['rowsPerPage']) ? $pagination['rowsPerPage'] : 15;
        $pagination['lastPage'] = ceil($pagination['rowsNumber'] / $rowsPerPage);
        if ($pagination['lastPage'] == 0) {
            $pagination['lastPage'] = 1;
        }

        return $pagination;
    }

    /**
     * Legacy method - kept for backward compatibility but now optimized
     */
    public function generatePagination($request, $model, $customModel = null)
    {
        $baseQuery = $this->buildBaseQuery($model, $request, $customModel);
        return $this->generatePaginationFromQuery($baseQuery, $request);
    }

    public function fetchOne($model, $request)
    {
        try {
            $query = $model;
            $data = $query;
            return $this->translateSuccessResponse(null, $data);
        } catch (\Exception $e) {
            return $this->translateExceptionResponse($e);
        }
    }

    /**
     * Apply relations for single record fetching
     */
    private function applyRelationsForSingle($query, $request, $model)
    {
        if (!isset($request->with)) return;

        foreach ($request->with as $relation) {
            if (is_array($relation) && count($relation) == 3) {
                $query->leftJoin($relation[0], $relation[1], $relation[2])
                    ->groupBy($model::getTableName() . ".id");
            } else {
                $query->load($relation);
            }
        }
    }

    /**
     * Paginate query builder
     */
    public function applyPagination($query, $request)
    {
        $pagination = $request->pagination;
        $page = isset($pagination['page']) ? $pagination['page'] : 1;
        $perPage = isset($pagination['rowsPerPage']) ? $pagination['rowsPerPage'] : 100;
        $offset = ($page - 1) * $perPage;

        $sortBy = isset($pagination['sortBy']) ? $pagination['sortBy'] : null;
        $sortDescending = isset($pagination['descending']) && $pagination['descending'] == 'true' ? 'DESC' : 'ASC';

        if ($sortBy && $sortDescending) {
            $query->orderBy($sortBy, $sortDescending);
        }

        $query->limit($perPage);
        $query->offset($offset);

        return $query;
    }

    /**
     * Apply search query using specified columns and search term
     */
    public function applySearch($query, $request, $columns = null)
    {
        $search = str_replace(["'", '"', "."], " ", $request->search);

        if (empty($search)) {
            return;
        }

        if ($columns) {
            $query->where(function ($q) use ($columns, $search) {
                foreach ($columns as $col) {
                    $q->orWhereRaw(trim($col) . " LIKE '%" . str_replace(' ', '%', $search) . "%'");
                    $q->orWhere(trim($col), $search);
                }
            });
        }
    }

    /**
     * Legacy method - simplified since relations are now handled in buildBaseQuery
     */
    public function applyRelations($query, $request, $model, $load = false)
    {
        if (!isset($request->with)) return;

        foreach ($request->with as $relation) {
            if (is_array($relation) && count($relation) == 3) {
                $query->leftJoin($relation[0], $relation[1], $relation[2])
                    ->groupBy($model::getTableName() . ".id");
            } else if ($load) {
                $query->load($relation);
            } else {
                $query->with($relation);
            }
        }
    }

    public function applyFilters($query, $request, $model)
    {
        if (!isset($request->filter) || empty($request->filter))
            return;

        foreach ($request->filter as $column => $value) {
            // If $column has no table prefixed to it, concat main table
            if (!str_contains($column, ".")) {
                $column = $model::getTableName() . "." . $column;
            }

            if (!is_array($value)) {
                // If the column name starts with 'date_', handle as date
                if (strpos($column, 'date_') === 0) {
                    $query->whereDate(str_replace('date_', '', $column), $value);
                } else {
                    $query->where($column, $value);
                }
            } else if (
                is_array($value) &&
                count($value) == 2 &&
                strtolower($value[0]) === 'in' &&
                is_array($value[1])
            ) {
                $query->whereIn($column, $value[1]);
            } else if (
                is_array($value) &&
                count($value) == 2 &&
                strtolower($value[0]) === 'json_contains'
            ) {
                $needle = $value[1];
                $query->whereRaw("JSON_CONTAINS($column, '$needle')");
            } else if (
                is_array($value) &&
                count($value) == 2 &&
                strtolower($value[0]) === 'is' &&
                strtolower($value[1]) === 'null'
            ) {
                $query->whereNull($column);
            } else if (is_array($value) && count($value) == 2) {
                $query->where($column, $value[0], $value[1]);
            }
        }
    }

    public function checkUserPermission(String $permission)
    {
        try {
            /**
             * Check if permission exists from database
             * 
             * If it does not exists, bypass the 
             * checking of permission
             */
            $permission_exists = Permission::where('name', $permission)->exists();
            if ($permission_exists) {
                $user = auth()->user();
                $has_permission = $user->can($permission);
                if (!$has_permission) {
                    throw new Exception('You don\'t have the permission to perform this action');
                }
            }
        } catch (\Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
}
