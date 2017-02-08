<?php
namespace Reposilib\Contracts;

use Illuminate\Http\Request;

/**
 * UriParserInterface Interface,defines method here.
 *
 * @author guosheng <guosheng1987@126.com>
 */

Interface UriParserInterface {
	
	/**
     * Sets the parameters 
	 *
     * @param Illuminate\Http\Request $request    The Request instance
     */
    public function initialize($request);


	/**
	 * Get filters parameter in url and convert in into an array
	 *
	 * @return array 
	 */
	public function getFilters();
	
	/**
	 * Get orders parameter in url and convert in into an array
	 *
	 * @return array
	 */
	public function getOrders();
	
	
	/**
	 * Get defaut pagesize parameter in GET request url
	 *
	 * @return int 
	 */
	public function getPagesize();
	
	
	/**
	 * Get defaut page parameter in GET request url
	 *
	 * @return int 
	 */
	public function getPage();
	
	/**
	 * formvars convert the fitlers into an 'key value' array . And it's used to fill the html form
	 * 
	 * @param  Array $columns 
	 *
	 * @return array
	 */
	public function getFormvars(array $columns);
	
}