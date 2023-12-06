<?php

declare(strict_types=1);

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Psr\Log\LoggerInterface;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Capsule\Manager;
use Slim\Routing\RouteContext;
use Slim\Flash\Messages;
use Psr\Http\Message\UploadedFileInterface;
use App\Auth\Auth;
use App\Validation\ValidatorFactory;
use Illuminate\Support\Facades\Validator;
use App\Models\Category;
use App\Models\Service;
use App\Models\Post;


class BaseController
{
	protected $logger;
	protected $twig;
	protected $db;
	protected $flash;
	protected $validator;
	public $categories;
	public $posts;

	public function __construct(LoggerInterface $logger, \Slim\Views\Twig $view, Manager $table, Messages $flash)
	{
		$this->logger = $logger;
		$this->twig = $view;
		$this->db = $table;
		$this->flash = $flash;
		/** Add Services globally. Service populate's the navbar */
		$environment = $this->twig->getEnvironment();
		//$navservices = Service::all();
		//$latestnews= Post::orderByDesc('created_at')->take(3)->get();
		//$environment->addGlobal('navservices',  $navservices);
		//$environment->addGlobal('latestnews',  $latestnews);
		// $this->validator = $validator;

	}

	protected function view($response, $view, $args = [])
	{

		/*//using Twig/Environment
        $response->getBody()->write(
            $this->twig->render($response,$view, $args)
        );
        return $response;*/
		//using Slim\View\Twig

		return $this->twig->render($response, $view, $args);
	}

	protected function fetch($response=null, $view, $args = [])
	{
		return $this->twig->fetch($view, $args);
	}


	/**
	 * Crud Partition. handle update, deletes, creation of child models
	 *
	 * @param $oldData
	 * @param $newData
	 * @return mixed
	 */

	public static function crudPartition($oldData, $newData)
	{
		// ids
		$oldIds = Arr::pluck($oldData, 'id');
		$newIds = array_filter(Arr::pluck($newData, 'id'), 'is_numeric');

		// groups
		$delete = collect($oldData)
			->filter(function ($model) use ($newIds) {
				return !in_array($model->id, $newIds);
			});

		$update = collect($newData)
			->filter(function ($model) use ($oldIds) {
				return property_exists($model, 'id') && in_array($model->id, $oldIds);
			});

		$create = collect($newData)
			->filter(function ($model) {
				return !property_exists($model, 'id');
			});

		// return
		return compact('delete', 'update', 'create');
	}


	/**
	 * Validate request
	 *
	 * @param $request
	 * @param $rules
	 * @return mixed
	 */
	public function validate($request, $rules)
	{
		if (is_array($request)) {
			$data = $request;
		} else {
			$data = $request->getParsedBody();
			if ($data == null) {
				$data = [];
			}
		}
		//return Validator::make($data, $rules);
		return $this->validator->make($data, $rules);
	}


	static function order($request, $columns)
	{
		$order = "";
		if (isset($request['order']) && count($request['order'])) {
			$orderBy = array();
			$dtColumns = self::pluck($columns, 'data');

			for ($i = 0, $ien = count($request['order']); $i < $ien; $i++) {
				// Convert the column index into the column data property
				$columnIdx = intval($request['order'][$i]['column']);
				$requestColumn = $request['columns'][$columnIdx];

				$columnIdx = array_search($requestColumn['data'], $dtColumns);
				$column = $columns[$columnIdx];

				if ($requestColumn['orderable'] == 'true') {
					$dir = $request['order'][$i]['dir'] === 'asc' ?
						'ASC' :
						'DESC';

					$orderBy[] = '`' . $column['data'] . '` ' . $dir;
				}
			}

			if (count($orderBy)) {
				$order = 'ORDER BY ' . implode(', ', $orderBy);
			}
		}

		return $order;
	}



	/**
	 * Searching / Filtering
	 *
	 * Construct the WHERE clause for server-side processing SQL query.
	 *
	 * NOTE this does not match the built-in DataTables filtering which does it
	 * word by word on any field. It's possible to do here performance on large
	 * databases would be very poor
	 *
	 *  @param  array $request Data sent to server by DataTables
	 *  @param  array $columns Column information array
	 *  @param  array $bindings Array of values for PDO bindings, used in the
	 *  R::getAll() function
	 *  @return string SQL where clause
	 */
	static function filter($request, $columns, &$bindings)
	{
		$globalSearch = array();
		$columnSearch = array();
		$dateQuery = '';
		$yearQuery = '';
		$monthQuery = '';
		$dayQuery = '';
		$dtColumns = self::pluck($columns, 'data');

		if (isset($request['search']) && $request['search']['value'] != '') {
			$str = $request['search']['value'];

			for ($i = 0, $ien = count($request['columns']); $i < $ien; $i++) {
				$requestColumn = $request['columns'][$i];
				$columnIdx = array_search($requestColumn['data'], $dtColumns);
				$column = $columns[$columnIdx];

				if ($requestColumn['searchable'] == 'true') {
					$binding = self::bind($bindings, '%' . $str . '%', \PDO::PARAM_STR);
					$globalSearch[] = "`" . $column['data'] . "` LIKE " . $binding;
				}
			}
		}

		// Individual column filtering
		if (isset($request['columns'])) {
			for ($i = 0, $ien = count($request['columns']); $i < $ien; $i++) {
				$requestColumn = $request['columns'][$i];
				$columnIdx = array_search($requestColumn['data'], $dtColumns);
				$column = $columns[$columnIdx];

				$str = $requestColumn['search']['value'];
				
				if (
					$requestColumn['searchable'] == 'true' &&
					$str != ''
				) {
					$binding = self::bind($bindings, '%' . $str . '%', \PDO::PARAM_STR);
					$columnSearch[] = "" . $column['data'] . " LIKE " . $binding;
				}
			}
		}

		if (!empty($request['startdate'])) {

			if (!empty($request['enddate'])) {
				$dateQuery = "(`flight_date` between '" . $request['startdate'] . "' and '" . $request['enddate'] . "')";
				$dateQuery =  $dateQuery . ' OR ' . "(`departure_date` between '" . $request['startdate'] . "' and '" . $request['enddate'] . "')";
			} else {
				$dateQuery = "`flight_date` > '" . $request['startdate'] . "'";
			}
		}

		if (!empty($request['year'])) {
			$dyear = $request["year"];
			$dateQuery = "(strftime('%Y',`flight_date`)) = '{$dyear}'";
			if (!empty($request['month'])) {
				$m = \DateTime::createFromFormat('F', $request['month'])->format('m');
				$dateQuery = "(strftime('%Y-%m',`flight_date`) )= '{$dyear}-{$m}'";

				if (!empty($request['day'])) {
					$day =  $request["day"];
					$d = \DateTime::createFromFormat('j', $request['day'])->format('d');
					$dateQuery = "(strftime('%Y-%m-%d',`flight_date`)) = '{$dyear}-{$m}-{$d}'";
				}
			}
		}

		// Combine the filters into a single string
		$where = '';

		if (count($globalSearch)) {
			$where = '(' . implode(' OR ', $globalSearch) . ')';
		}

		if (count($columnSearch)) {
			$where = $where === '' ?
				implode(' AND ', $columnSearch) :
				$where . ' AND ' . implode(' AND ', $columnSearch);
		}
		if ($dateQuery !== '') {
			$where = $where === '' ? $where . $dateQuery : ' AND ' . $dateQuery;
		}

		if ($where !== '') {
			$where = 'WHERE ' . $where;
		}

		return $where;
	}


	/**
	 * Pull a particular property from each assoc. array in a numeric array, 
	 * returning and array of the property values from each item.
	 *
	 *  @param  array  $a    Array to get data from
	 *  @param  string $prop Property to read
	 *  @return array        Array of property values
	 */
	static function pluck($a, $prop)
	{
		$out = array();

		for ($i = 0, $len = count($a); $i < $len; $i++) {
			$out[] = $a[$i][$prop];
		}

		return $out;
	}


	/**
	 * Create a PDO binding key which can be used for escaping variables safely
	 * when executing a query with sql_exec()
	 *
	 * @param  array &$a    Array of bindings
	 * @param  *      $val  Value to bind
	 * @param  int    $type PDO field type
	 * @return string       Bound key to be used in the SQL where this parameter
	 *   would be used.
	 */
	static function bind(&$a, $val, $type)
	{
		//$key = ':binding_'.count( $a );
		$key = "'$val'";

		$a[] = array(
			'key' => $key,
			'val' => $val,
			'type' => $type
		);

		return $key;
	}
}
