<?php
namespace RepositoryQuery;

use Closure, Schema, DB ;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

use RepositoryQuery\Query;
/**
 * Repository base class,we use Dependency injection to in inject an Query instance.
 * We can bulid our query like this '&filters[username][like]=abc&filters[gender][equal]=1' in Get request
 *
 * @author guosheng <guosheng1987@126.com>
 */

class Repository {
	
	/*
	 *  Query instance 
	 */
	protected $query;
	
	/**
     * Create a new repository instance.
     *
     * @param  Query  $query
     * @return void
     */
    public function __construct(Query $query)
    {
        $this->query = $query;
    }
	
	/**
     * Create a new repository instance.
     *
     * @param  Illuminate\Database\Eloquent\Builder $builder
	 *
     * @return Array 
     */
	private function _getColumns(Builder $builder)
	{
		static $table_columns;
		
		$query = $builder->getQuery();
		$tables = [$query->from];
		if (!empty($query->joins))
			foreach ($query->joins as $v)
				$tables[] = $v->table;
		
		$_columns = [];
		foreach ($tables as &$v)
		{
			list($table, $alias) = strpos(strtolower($v), ' as ') !== false ? explode(' as ', $v) : [$v, $v];

			if (!isset($table_columns[$table]))
				$table_columns[$table] = Schema::getColumnListing($table);
				//$table_columns[$table] = $query->getConnection()->getDoctrineSchemaManager()->listTableColumns($table);
			
			foreach ($table_columns[$table] as $key/* => $value*/)
				$_columns[$key] = isset($_columns[$key]) ? $_columns[$key] : $alias.'.'.$key;
		}
		return $_columns;
	}

	/**
	 * 给Builder绑定where条件
	 * 注意：参数的值为空字符串，则会忽略该条件
	 * 
	 * @param  Array $filters 
	 * @param  Builder $builder 
	 * @param  Array $columns
	 *
	 * @return array   返回筛选(搜索)的参数
	 */
	private function _doFilter($filters, Builder $builder, $columns = [])
	{
		$operators = [
			'in' => 'in', 'not_in' => 'not in', 'is' => 'is', 'min' => '>=', 'greater_equal' => '>=', 'max' => '<=', 'less_equal' => '<=', 'between' => 'between', 'not_between' => 'not between', 'greater' => '>', 'less' => '<', 'not_equal' => '<>', 'inequal' => '<>', 'equal' => '=',
			'like' => 'like', 'left_like' => 'like', 'right_like' => 'like', 'rlike' => 'rlike', 'ilike' => 'ilike', 'like_binary' => 'like binary', 'left_like_binary' => 'like binary', 'right_like_binary' => 'like binary', 'not_like' => 'not like', 'not_left_like' => 'not like', 'not_right_like' => 'not like',
			'and' => '&', 'or' => '|', 'xor' => '^', 'left_shift' => '<<', 'right_shift' => '>>', 'bitwise_not' => '~', 'bitwise_not_any' => '~*', 'not_bitwise_not' => '!~', 'not_bitwise_not_any' => '!~*',
			'regexp' => 'regexp', 'not_regexp' => 'not regexp', 'similar_to' => 'similar to', 'not_similar_to' => 'not similar to',
		];

		array_walk($filters, function($v, $key) use ($builder, $operators, $columns) {
			$key = !empty($columns[$key]) ? $columns[$key] : $key;
			array_walk($v, function($value, $method) use ($builder, $key, $operators){
				if (empty($value) && $value !== '0') return; //''不做匹配
				else if (in_array($method, ['like', 'like_binary', 'not_like'])) $value = '%'.$value.'%';
				else if (in_array($method, ['left_like', 'left_like_binary', 'not_left_like'])) $value = $value.'%';
				else if (in_array($method, ['right_like', 'right_like_binary', 'not_right_like'])) $value = '%'.$value;
				if ($operators[$method] == 'in') 
					is_array($value) ? $builder->whereIn($key, $value) : $builder->whereIn($key, explode(',',$value));					
				else if ($operators[$method] == 'not in')
					is_array($value) ? $builder->whereNotIn($key, $value) : $builder->whereNotIn($key, explode(',',$value));					
				else
					$builder->where($key, $operators[$method] ?: '=' , $value);
			});
		});
		return $filters;
	}
	
	/**
	 * 给Builder绑定order by 条件
	 * 注意：参数的值为空字符串，则会忽略该条件
	 * 
	 * @param  Array $orders 
	 * @param  Builder $builder 
	 * @param  Array $columns
	 *
	 * @return array   返回筛选(搜索)的参数
	 */
	 
	private function _doOrder($orders, Builder $builder, $columns = [])
	{
		if(!empty($orders)) 
			foreach ($orders as $k => $v)
				$builder->orderBy($columns[$k] ?: $k, $v);
		else		
			$orders = [$builder->getModel()->getKeyName() => 'desc'];
		
				
		return $orders;
	}
	
	/**
	 * 根据url中的参数，获取查询后的分页对象
	 * 
	 * @param Illuminate\Database\Eloquent\Builder $builder 
	 * @param Array $columns 
	 * @param Array $extra_query 
	 *
	 * @return Illuminate\Pagination\LengthAwarePaginator 
	 */
	public function getPaginate(Builder $builder, array $columns = ['*'], array $extra_query = [])
	{	
		$tables_columns = $this->_getColumns($builder);
		
		$filters = $this->_doFilter($this->query->filters, $builder, $tables_columns);
		$orders = $this->_doOrder($this->query->orders, $builder, $tables_columns);
		
		$paginate = $builder->paginate($this->query->pagesize, $columns, 'page', $this->query->page);
		
		$query = compact('filters') + $extra_query;
		array_walk($query, function($v, $k) use($paginate) {
			$paginate->addQuery($k, $v);
		});
		$paginate->filters = $filters;
		$paginate->orders = $orders;
		
		return $paginate;
	}
	
	/**
	 * 根据url中的参数，获取查询后的分页数组
	 * 
	 * @param Illuminate\Database\Eloquent\Builder $builder 
	 * @param Array $columns 
	 * @param Closure $callback 
	 *
	 * @return Array 
	 */
	public function getData(Builder $builder, array $columns = ['*'], Closure $callback = NULL)
	{
		$paginate = $this->getPaginate($builder, $columns);
		
		if (!empty($callback) && is_callable($callback))
			foreach ($paginate as $key => $value)
				call_user_func_array($callback, [&$value, $key]);
		
		return $paginate->toArray() + ['filters' => $paginate->filters, 'orders' => $paginate->orders];
	}
	
	/**
	 * 根据url中的参数，获取查询的数组总数
	 * 
	 * @param Illuminate\Database\Eloquent\Builder $builder 
	 * @param Bool enable_filters
	 *
	 * @return int 
	 */
	public function getCount(Builder $builder, $enable_filters = TRUE)
	{
		$_b = clone $builder;
		if ($enable_filters)
		{
			$tables_columns = $this->_getColumns($builder);
			
			$this->_doFilter($this->query->filters, $_b, $tables_columns);
		}
		$query = $_b->getQuery();
		if (!empty($query->groups)) //group by
		{
			return DB::table( DB::raw("({$_b->toSql()}) as sub") )
			->mergeBindings($_b->getQuery()) // you need to get underlying Query Builder
			->count();
		} else
			return $_b->count();
	}

	/*
	public function _getExport(Request $request, Builder $builder, Closure $callback = NULL, array $columns = ['*']) {
		set_time_limit(600); //10min

		$pagesize = $request->input('pagesize') ?: config('site.pagesize.export', 1000);
		$tables_columns = $this->_getColumns($builder);
		$this->_doFilter($request, $builder, $tables_columns);
		$paginate = $builder->orderBy($builder->getModel()->getKeyName(),'DESC')->paginate($pagesize, $columns);
		if (!empty($callback) && is_callable($callback))
			foreach ($paginate as $key => $value)
				call_user_func_array($callback, [&$value, $key]);
		$data = $paginate->toArray();
		!empty($data['data']) && is_assoc($data['data'][0]) && array_unshift($data['data'], array_keys($data['data'][0]));
		array_unshift($data['data'], [$builder->getModel()->getTable(), $data['from']. '-'. $data['to'].'/'. $data['total'], date('Y-m-d h:i:s')]);
		return $data['data'];
	}
	*/
}