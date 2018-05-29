<?php

namespace PersonaClix\CMS;

class PersonaClixCMS {

	private $database;

	/**
	 *	Initializes the Content Management System (CMS).
	 *	@param \PersonaClix\Engine\Database - Instance of Persona Clix Engine's Database Class.
	 */
	public function __construct(\PersonaClix\Engine\Database $database) {
		// Save the database instance for later.
		$this->database = $database;

		// Fetch page info from the database and register it as a globals variable to make
		// it accessible inside our route's callable function.
		$GLOBALS['page_info'] = $this->getPageInfo();

		// Check if any info was retrieved, and register a route to match.
		if(!empty($GLOBALS['page_info'])) {
			\PersonaClix\Engine\Router::register('GET', $GLOBALS['page_info']['URI'], function() {
				echo $GLOBALS['page_info']['content'];
			},
			[
				'title' => $GLOBALS['page_info']['title'],
				'name' => str_replace('/', '-', strtolower($GLOBALS['page_info']['URI']))
			]);
		// No page info was retrieved, register a route to say nothing was found.
		} else {
			\PersonaClix\Engine\Router::register('GET', \PersonaClix\Engine\Helpers\Request::uri(), function() {
				echo "Sorry, the content you are looking for cannot be found.";
			},
			[
				'title' => 'Content Not Found',
				'name' => str_replace('/', '-', \PersonaClix\Engine\Helpers\Request::uri())
			]);
		}
	}

	/**
	 *	Retieves information about a page from the database from the provided URI or defaults to the current URI.
	 *	@param String (Optional) URI (e.g. /something)
	 */
	public function getPageInfo(String $URI = "") : array {
		// Check if no URI was supplied and use current URI instead.
		if(trim($URI) == "") $URI = \PersonaClix\Engine\Helpers\Request::uri();

		// Select the page from the database that matches the URI.
		$page = $this->database->select(['*'], 'pages', ['URI' => $URI]);

		// If a page was found, return page info, otherwise return empty array.
		return count($page) == 1 ? $page[0] : [];
	}

}