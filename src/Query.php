<?php
namespace RepositoryQuery;

/*
|--------------------------------------------------------------------------
| Query Base Class
|--------------------------------------------------------------------------
|
| Query类. 直接从url获取参数，解析参数
|
*/

class Query {
	
	/**
     * The Illuminate\Http\Request implementation.
     *
     * @var Request
     */
	protected $request;
	
	/**
     * The filters param in the request url,converts it into an array
     *
     * @var filters
     */
	public $filters;
	
	/**
     * The orders param in the request url,converts it into an array
     *
     * @var
     */
	public $orders;
	
	/**
     * The pagesize param in the request url,by default is 12
     *
     * @var 
     */
	public $pagesize = 12;
	
	/**
     * The page param in the request url,by default is 1
     *
     * @var 
     */
	public $page = 1;
	
	/**
     * Create a new Query instance.
     *
     * @return void
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
		
		$this->filters = $this->_getFilters();
		
		$this->orders = $this->_getOrders();
				
		$this->pagesize = $this->_getPagesize();
		
		$this->page = $this->_getPage();
    }
	
	/**
	 * 获取筛选(搜索)的参数
	 * &filters[username][like]=abc&filters[gender][equal]=1
	 * 
	 * @param  Request $request 
	 *
	 * @return array  返回参数列表
	 */
	public function _getFilters()
	{
		$filters = [];
		$inputs = $this->request->input('filters') ?: [];
		foreach ($inputs as $k => $v) {
			$filters[$k] = is_array($v) ? array_change_key_case($v) : ['equal' => $v] ;
		}
		
		return $filters;
	}
	
	/**
	 * 获取筛选(搜索)的参数, 返回给列表页面 
	 * $columns 参数可以提供默认值给需要查询的控件 
	 * &filters[username][like]=abc&filters[gender][equal]=1
	 * 
	 * @param  Request $request 
	 * @param  Array $columns 
	 * @return array
	 */
	public function _getRenderFilters($columns = [] )
	{
		$render_filters = [];
		
		$filters = $this->_getFilters($request);
		
		if( !empty($columns) ) 
			foreach ($columns as $column) 
				$render_filters[$column] = '';
			
		foreach ($this->filters as $k => $v) {
			$render_filters[$k] = is_array($v) ? end($v):'';
		}
		
		return $render_filters;
	}
	
	/**
	 * 获取排序的参数
	 * order[id]=desc&order[created_at]=asc 类似这种方式、
	 *
	 * @return array 返回orders参数列表
	 */
	public function _getOrders()
	{
		$columns = $this->request->input('columns') ?: [];
		$inputs = $this->request->input('order') ?: [];

		$orders = [];
		if(!empty($columns))
			foreach ($inputs as $v)
				!empty($columns[$v['column']]['data']) && $orders[$columns[$v['column']]['data']] = strtolower($v['dir']); 
		else
			$orders = $inputs;
		
		return $orders;
	}
	
	/**
	 * 获取排序的参数
	 *
	 * @return int 
	 */
	public function _getPagesize()
	{
		$pagesize = $this->request->input('pagesize') ?: $this->pagesize;
		
		return $pagesize;
	}
	
	/**
	 * 获取排序的参数
	 *
	 * @return int 
	 */
	public function _getPage()
	{
		$page = $this->request->input('page') ?: $this->page;
		
		return $page;
	}
	
	
}