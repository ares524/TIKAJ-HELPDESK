<?php
namespace Reposilib\Contracts;

use Illuminate\Http\Request;

use Illuminate\Database\Eloquent\Builder;
/**
 * Repository Interface,defines method here.
 *
 * @author guosheng <guosheng1987@126.com>
 */

Interface RepositoryInterface{
	
	/**
	 * Get Paginate object according to the parameters of url
	 * 
	 * @param Illuminate\Database\Eloquent\Builder $builder 
	 * @param Array $columns 
	 * @param Array $extra_query 
	 *
	 * @return Illuminate\Pagination\LengthAwarePaginator 
	 */
	public function getPaginate(Builder $builder, array $columns, array $extra_query);
	
	/**
	 * Convert Paginate object to Array,see 'getPaginate'
	 * 
	 * @param Illuminate\Database\Eloquent\Builder $builder 
	 * @param Array $columns 
	 * @param Closure $callback 
	 *
	 * @return Array 
	 */
	public function getData(Builder $builder, array $columns, \Closure $callback);
		
	/**
	 * Get total records according to the parameters of url
	 * 
	 * @param Illuminate\Database\Eloquent\Builder $builder 
	 * @param Bool enable_filters
	 *
	 * @return int 
	 */
	public function getCount(Builder $builder, $enable_filters);

	
}