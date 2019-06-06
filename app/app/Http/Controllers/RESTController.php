<?php
/**
 * @author joel diaz
*/

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Admin\CampaignSurveySet;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request as HttpRequest;
use Response;

/**
 * Base controller provided by Laravel
 *
 * Methods all extended for general controller use
 */
class RESTController extends Controller
{
    /**
     * Relations for a model that can be loaded
     * @var array
     */
    private $relations = [];

    private $exceptions = [];

    private $loadQueryData = true;

    private $httpRequest = null;

    public function getHttpRequest()
    {
        return $this->httpRequest;
    }

    public function setRequestParams(HttpRequest $request)
    {
        if (!$this->httpRequest) {
            $this->httpRequest = $request;
        }
    }

    public function getFile($key)
    {
        $file = null;
        if ($this->httpRequest && $this->httpRequest->hasFile($key)) {
            $file = $this->httpRequest->file($key);
        }
        return $file;
    }

    public function getFromRequest($key, $value = null)
    {
        if ($this->httpRequest->has($key)) {
            $value = $this->httpRequest->input($key);
        }
        return $value;
    }

    public function getExceptRequest($keys)
    {
        return $this->httpRequest->except($keys);
    }

    public function addToRequest($valueArray)
    {
        $this->httpRequest->merge($valueArray);
    }

    public function getMockedObject($params = null)
    {
        return [];
    }

    public function getObjectIdField()
    {
        return "id";
    }

    public function formatCollectonArray($data)
    {
        return $data;
    }

    public function getMockedCollection($size = 1)
    {
        $data = [];
        for ($x = 0; $x < $size; $x++) {
            $data[] = $this->getMockedObject();
        }
        $data = $this->formatCollectonArray($data);
        return $data;
    }

    /**
     * The model class we need to run basic CRUD operations
     * @return string The \ escaped class string
     */
    public function getCrudModelClass()
    {
        return "App\\Models\\Model";
    }

    /**
     * Get the relations for the implemented class
     * @return array the array of relations
     */
    public function getCrudModelRelations()
    {
        return $this->relations;
    }

    /**
     * Special relations include relations with model methods
     * @return array  the array of special relations
     */
    public function getSpecialRelations()
    {
        return [];
    }

    /**
     * Get a curated list of all relations possible
     * @return array  the array of all relations
     */
    public function getAllRelations()
    {
        return [];
    }

    public function getInputExceptions()
    {
        return $this->exceptions;
    }

    public function setMiscellaneousFieldValues($model)
    {
        return $model;
    }

    public function getValidationRules()
    {
        return [];
    }

    /**
     * Remove all relations from the query params
     * @param  array  $params The processed query params
     * @return array          The query params with all relations pulled out
     */
    public function removeRelationFieldsFromParams($params)
    {
        $sanitizedParams = [];
        $relations       = $this->getAllRelations();
        foreach ($params["query"] as $key => $value) {
            if (!in_array($key, $relations)) {
                $sanitizedParams[$key] = $value;
            }
        }
        $params["query"] = $sanitizedParams;
        return $params;
    }

    /**
     * Parameter parsing method that removes filtering commands from query and special commands
     * @param  object   $query Query Builder object (Laravel) object we are pulling for
     * @param  string   $field The model parameter field name
     * @param  String   $value The value we are filtering the field by
     * @return object   Query Builder object (Laravel)
     */
    public function setQueryParam($query, $field, $value)
    {
        if ($value == "null") {
            $query->whereNull($field);
        } else if ($value == "notNull") {
            $query->whereNotNull($field);
        } else {
            switch ($field) {
                case "between":
                    $key = $value[0];
                    unset($value[0]);
                    $query->whereBetween($key, array_values($value));
                    break;
                case "notBetween":
                    $key = $value[0];
                    unset($value[0]);
                    $query->whereNotBetween($key, array_values($value));
                    break;
                case "in":
                    $key = $value[0];
                    unset($value[0]);
                    $query->whereIn($key, array_values($value));
                    break;
                case "notIn":
                    $key = $value[0];
                    unset($value[0]);
                    $query->whereNotIn($key, array_values($value));
                    break;
                case "after":
                case "greaterThan":
                    $query->where($value[0], ">", $value[1]);
                    break;
                case "before":
                case "lessThan":
                    $query->where($value[0], "<", $value[1]);
                    break;
                case "onOrAfter":
                case "greaterThanEqual":
                    $query->where($value[0], ">=", $value[1]);
                    break;
                case "onOrBefore":
                case "lessThanEqual":
                    $query->where($value[0], "<=", $value[1]);
                    break;
                case "select":
                    $query->select($value);
                    break;
                case "like":
                    $query->where($value[0], "LIKE", '%' . $value[1] . '%');
                    break;
                case "notLike":
                    $query->where($value[0], "NOT LIKE", '%' . $value[1] . '%');
                    break;
                default:
                    if (is_array($value)) {
                        $query->where(function ($q) use ($field, $value) {
                            $size = count($value);
                            for ($x = 0; $x < $size; $x++) {
                                if ($x == 0) {
                                    $q->where($field, '=', $value);
                                } else {
                                    $q->orWhere($field, '=', $value);
                                }
                            }
                        });
                    } else {
                        $query->where($field, '=', $value);
                    }
            }
        }
        return $query;
    }

    private function setControlParam($query, $control, $values)
    {
        if (is_array($values)) {
            foreach ($values as $value) {
                switch ($control) {
                    case "orderBy":
                        $query->orderBy($value, "ASC");
                        break;
                    case "orderByDesc":
                        $query->orderBy($value, "DESC");
                        break;
                    default:
                        $query->{$control}($value);
                }
            }
        } else {
            switch ($control) {
                case "orderBy":
                    $query->orderBy($values, "ASC");
                    break;
                case "orderByDesc":
                    $query->orderBy($values, "DESC");
                    break;
                default:
                    $query->{$control}($values);
            }
        }
        return $query;
    }

    /**
     * Parses Request input parameters into a queriable array of options and field -> value pairs
     * @param  boolean $sanitizeRelations Flag to determine if we need to do further cleanup on relations
     * @return array                      an array containing parameters separated by type (control, query and relation)
     */
    protected function getQueryParams($sanitizeRelations = false)
    {
        $params = ["query" => [], "control" => [], "ordinal" => []];
        foreach ($this->getExceptRequest($this->getInputExceptions()) as $key => $value) {
            switch ($key) {
                case "relations":
                    $this->relations = [];
                    if (!is_array($value)) {
                        if (isset($this->getSpecialRelations()[$value])) {
                            $this->relations[$value] = $this->getSpecialRelations()[$value];
                        } else {
                            $this->relations[] = $value;
                        }
                    } else {
                        foreach ($value as $relation) {
                            if (isset($this->getSpecialRelations()[$relation])) {
                                $this->relations[$relation] = $this->getSpecialRelations()[$relation];
                            } else {
                                $this->relations[] = $relation;
                            }
                        }
                    }
                    break;
                case "limit":
                case "size":
                case "take":
                    $params["control"]["take"] = $value;
                    break;
                case "offset":
                case "skip":
                    $params["control"]["skip"] = $value;
                    break;
                case "orderBy":
                case "orderByDesc":
                case "groupBy":
                    $params["ordinal"][$key] = $value;
                    break;
                default:
                    $params["query"][$key] = $value;
            }
        }
        if ($sanitizeRelations) {
            $params = $this->removeRelationFieldsFromParams($params);
        }
        return $params;
    }

    /**
     * Resource Controller method expected by laravel
     *
     * GET method for collection
     *
     * @param HttpRequest $request
     * @return Collection
     */
    public function index(HttpRequest $request)
    {
        $this->setRequestParams($request);
        if ($this->getFromRequest('mock')) {
            return $this->getMockedCollection($this->getFromRequest('mock'));
        }

        $modelClass    = $this->getCrudModelClass();
        $results       = null;
        $requestParams = $this->getQueryParams();
        $query         = $modelClass::with($this->getCrudModelRelations());

        try {
            foreach ($requestParams['query'] as $field => $value) {
                switch ($field) {
                    case "or":
                        $query->where(function ($q1) use ($value) {
                            $val = json_decode($value, true);
                            foreach ($val as $orField => $orParams) {
                                $q1->orWhere(function ($q) use ($orField, $orParams) {
                                    foreach ($orParams as $orParam) {
                                        $q = $this->setQueryParam($q, $orField, $orParam);
                                    }
                                });
                            }
                        });
                        break;
                    default:
                        $query = $this->setQueryParam($query, $field, $value);
                }
            }
            foreach ($requestParams['control'] as $control => $values) {
                $query = $this->setControlParam($query, $control, $values);
            }
            $this->defaultOrder($query, $requestParams['ordinal']);
            $results = $this->finalizeDataSet($query->get());
        } catch (QueryException $qe) {
            \Log::info($qe);
            $results = $this->jsonResponse("Unprocessable Entity", 422);
        } catch (Exception $e) {
            $results = $this->jsonResponse("Something went wrong", 400);
        }
        return $results;
    }

    public function defaultOrder($query, $ordinals)
    {
        foreach ($ordinals as $type => $value) {
            if (is_array($value)) {
                switch ($type) {
                    case "orderBy":
                        foreach ($value as $key) {
                            $query->orderBy($key);
                        }
                        break;
                    case "orderByDesc":
                        foreach ($value as $key) {
                            $query->orderBy($key, "DESC");
                        }
                        break;
                    case "groupBy":
                        foreach ($value as $key) {
                            $query->groupBy($key);
                        }
                        break;
                }
            } else {
                switch ($type) {
                    case "orderBy":
                        $query->orderBy($value);
                        break;
                    case "orderByDesc":
                        $query->orderBy($value, "DESC");
                        break;
                    case "groupBy":
                        $query->groupBy($value);
                }
            }
        }
    }

    public function finalizeDataSet($dataSet)
    {
        return $dataSet;
    }

    /**
     * Resource Controller method expected by laravel
     *
     * PUT method for creating a new record
     *
     * @return Collection
     */
    public function create(HttpRequest $request)
    {
        $this->setRequestParams($request);
        $this->validate($request, $this->getValidationRules());
        $requestParams = $this->getQueryParams(true);
        $modelClass    = $this->getCrudModelClass();
        $model           = $this->show($request, $request->input($this->getObjectIdField()));
            if(!$model)
            {
                $model = new $modelClass();
            }
        $model->fill($requestParams['query']);
        $model = $this->setMiscellaneousFieldValues($model);
        $model->save();
        return $model;
    }

    /**
     * Resource Controller method expected by laravel
     *
     * POST method for updating and creating a record
     *
     * @return Collection
     */
    public function store(HttpRequest $request)
    {
        $this->setRequestParams($request);
        $this->validate($request, $this->getValidationRules());
        $requestParams = $this->getQueryParams(true);
        $modelClass    = $this->getCrudModelClass();
        $model         = $this->show($request, $requestParams['query'][$this->getObjectIdField()]);
        $model->fill($requestParams['query']);
        $model->save();
        return $model;
    }

    /**
     * Resource Controller method expected by laravel
     *
     * GET method for a single record
     *
     * @return Collection
     */
    public function show(HttpRequest $request, $id)
    {
        $this->setRequestParams($request);
        $requestParams = $this->getQueryParams();
        if ($this->getFromRequest('mock')) {
            return $this->getMockedObject([$this->getObjectIdField() => $id]);
        }
        $modelClass = $this->getCrudModelClass();
        return $this->finalizeDataSet([$modelClass::with($this->getCrudModelRelations())->find($id)])[0];
    }

    /**
     * Resource Controller method expected by laravel
     *
     * POST method for collection for updating a record
     *
     * @return Collection
     */
    public function edit(HttpRequest $request, $id)
    {
        \Log::info("edit");
        $this->setRequestParams($request);
        $this->validate($request, $this->getValidationRules());
        $requestParams = $this->getQueryParams(true);
        $modelClass    = $this->getCrudModelClass();
        $model         = $modelClass::find($id);
        foreach ($requestParams['query'] as $key => $value) {
            $model->$key = $value;
        }
        $model->save();
        return $model;
    }

    /**
     * Resource Controller method expected by laravel
     *
     * POST method for collection for updating a record
     *
     * @return Collection
     */
    public function update(HttpRequest $request, $id)
    {
        \Log::info("update");
        $this->setRequestParams($request);
        $this->validate($request, $this->getValidationRules());
        $requestParams = $this->getQueryParams(true);
        $modelClass    = $this->getCrudModelClass();
        $model         = $modelClass::find($id);
        \Log::info($model);
        foreach ($requestParams['query'] as $key => $value) {
            \Log::info("updating $key => $value");
            $model->$key = $value;
        }
        $model->save();
        \Log::info($model);
        return $model;
    }

    /**
     * Resource Controller method expected by laravel
     *
     * DELETE method for deletion of a record
     * (By default we do not delete records)
     * @return Collection
     */
    public function destroy(HttpRequest $request, $id)
    {
        $this->setRequestParams($request);
    }

    public function setExceptions($exceptionField)
    {
        $this->exceptions[] = $exceptionField;
    }

    public function extractWorkDayInterval()
    {
        $timezone           = $this->getFromRequest('timezone', null) ? $this->getFromRequest('timezone') : "America/Chicago";
        $this->exceptions[] = 'timezone';
        $date               = null;
        if ($this->getFromRequest('date', null)) {
            try {
                $date               = Carbon::createFromFormat('Y-m-d', $this->getFromRequest('date'), $timezone);
                $this->exceptions[] = 'date';
            } catch (\Exception $e) {
                try {
                    $date               = Carbon::createFromFormat('Y-m-d H:i:s', $this->getFromRequest('date'), $timezone);
                    $this->exceptions[] = 'date';
                } catch (\Exception $e2) {}
            } finally {
                if (!$date) {
                    \Log::info("Date in request malformed or not supplied, using current date time");
                    $date = Carbon::now($timezone);
                }
            }
        } else {
            $date = Carbon::now($timezone);
        }

        $startTime = clone $date;
        $endTime   = clone $date;
        $start     = $startTime->startOfDay()->tz("UTC");
        $end       = $endTime->endOfDay()->tz("UTC");
        return ['timezone' => $timezone, "date" => $date, "dayStarts" => $start, "dayEnds" => $end];
    }

    public function jsonResponse($message = 'Data not found', $httpcode = 400, $data = null, $code = null, $status = "error")
    {
        return Response::json(compact('status', 'message', 'code', 'data'), $httpcode);
    }

}
