<?php
namespace Reposilib\Http;

use Illuminate\Http\Request;

use Reposilib\Contracts\UriParserInterface;

/**
 * Query set several parameters into arrays so that we can use it in Repository class
 *
 * @author guosheng <guosheng1987@126.com>
 */

class UriParser implements UriParserInterface {
	
	/**
     * @var The Illuminate\Http\Request
     */
	protected $request;
	
	/**
     * @var array
     */
	public $filters;
	
	/**
     * @var array
     */
	public $orders;
	
	/**
     * @var array
     */
	public $pagesize = 12;
	
	/**
     * @var array
     */
	public $page = 1;
	
	/**
     * Constructor.
	 *
     * @param Illuminate\Http\Request $request    The Request instance
     */
    public function __construct(Request $request)
    {
		$this->initialize($request);
    }
	
	/**
     * Sets the parameters 
	 *
     * @param Illuminate\Http\Request $request    The Request instance
     */
    public function initialize($request)
    {
        $this->request = $request;
		$this->filters = $this->getFilters();
		$this->orders = $this->getOrders();
		$this->pagesize = $this->getPagesize();
		$this->page = $this->getPage();
    }

	/**
	 * Get filters parameter in url and convert in into an array
	 *
	 * @return array 
	 */
	public function getFilters()
	{
		$filters = [];
		$inputs = $this->request->input('filters') ?: [];
		foreach ($inputs as $k => $v) {
			$filters[$k] = is_array($v) ? array_change_key_case($v) : ['equal' => $v] ;
		}
		
		return $filters;
	}
	
	/**
	 * Get orders parameter in url and convert in into an array
	 *
	 * @return array
	 */
	public function getOrders()
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
	 * Get defaut pagesize parameter in GET request url
	 *
	 * @return int 
	 */
	public function getPagesize()
	{
		$pagesize = $this->request->input('pagesize') ?: $this->pagesize;
		
		return $pagesize;
	}
	
	/**
	 * Get defaut page parameter in GET request url
	 *
	 * @return int 
	 */
	public function getPage()
	{
		$page = $this->request->input('page') ?: $this->page;
		
		return $page;
	}

	/**
	 * formvars convert the fitlers into an 'key value' array . And it's used to fill the html form
	 * 
	 * @param  Array $columns 
	 *
	 * @return array
	 */
	public function getFormvars(array $columns = [])
	{
		$formvars = [];
		
		foreach ($this->filters as $k => $v) {
			$formvars[$k] = is_array($v) ? end($v):'';
		}
		
		return $formvars;
	}

	
}