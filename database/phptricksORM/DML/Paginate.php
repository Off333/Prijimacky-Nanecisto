<?php

namespace PHPtricks\Orm\DML;

trait Paginate
{
	/**
	 * @sense v.2.1.0
	 * pagination functionality
	 * @param int $recordsCount count records per page
	 * @return array
	 */
	/**
	 * How to Use:
	 *
	 * $db = PHPtricks\Orm\Database::connect();
	 * $results = $db->table("blog")->paginate(15);
	 *
	 * var_dump($results);
	 *
	 * now add to url this string query (?page=2 or 3 or 4 .. etc)
	 * see (link() method to know how to generate navigation automatically)
	 */
	public function paginate($recordsWhole, $recordsCount = 0, $last = false, $startFrom = 1)
	{
		if($recordsCount === true)
		{
			$last = true;
			$recordsCount = 0;
		}

		if($recordsCount === 0)
			$recordsCount = config("pagination.records_per_page");

		// this method accept one argument must be an integer number .
		if(!is_integer($recordsCount))
		{
			trigger_error("Oops, the records count must be an integer number"
				. "<br> <p><strong>paginate method</strong> accept one argument must be"
				." an <strong>Integer Number</strong> , " . gettype($recordsCount) . " given!</p>"
				. "<br><pre>any question? contact me on team@phptricks.org</pre>", E_USER_ERROR);
		}
		/* check current page
		$startFrom = isset($_GET[config("pagination.link_query_key")]) ?
			($_GET[config("pagination.link_query_key")] - 1) * $recordsCount : 0;
		*/

		// return query results
		$last ? $this->limit(1, $recordsWhole) : $this->limit($recordsCount, ($startFrom-1) * $recordsWhole);//->select(['*'], $last);

		// get pages count rounded up to the next highest integer
		$this->_colsCount = ceil($recordsWhole / $recordsCount);

		return $this;
	}

	/**
	 * check if we have a string query in current uri other (pagination key)
	 * if not so return (?) otherwise we want to reorder a string query to keep other keys
	 * @return string
	 */
	private function checkAndGetUriQuery()
	{
		$get = $_GET;
		// remove pagination key from query string
		unset($get[config("pagination.link_query_key")]);
		// init query string and set init value (?)
		$queryString = "?";
		// check if we have other pagination key in query string
		if(count($get))
		{
			// reorder query string to keep other keys
			foreach ($get as $key => $value)
			{
				if(!(gettype($value) == 'array')) {
					$queryString .= "{$key}" .
						(!empty($value) ? "=" : "") . $value . "&";
				}
			}
			return $queryString;
		}



		return "?";
	}

	/**
	 * @return int pages count when use paginate() method
	 */
	public function pagesCount()
	{
		if($this->_colsCount < 0)
			return null;

		return $this->_colsCount;
	}

	/**
	 * create pagination list to navigate between pages
	 * @return string (html)
	 */
	/**
	 * How to Use:
	 *
	 * $db = PHPtricks\Orm\Database::connect();
	 * $db->table("blog")->where("vote", ">", 2)->paginate(5);
	 * echo $db->link();
	 */
	public function link($selected, $currentPage)
	{
		// get current url
		$link = $_SERVER['PHP_SELF'];

		/* current page
		$currentPage =
			(isset($_GET[config("pagination.link_query_key")]) ?
				$_GET[config("pagination.link_query_key")]
				: 1);
		/**
		 * $html var to store <ul> tag
		 */
		$html = '';
		if($this->_colsCount > 0) // check if columns count is not 0 or less
		{
			//$operator = $this->checkAndGetUriQuery();

			$html = "<nav aria-label=\"pagination\"><ul class=\"pagination pagination-lg\">";

			$html .= "<li id=\"showAll\" class=\"page-item\"><a href=\"#\" class=\"page-link changeBtn pageNav\" data-loading=true name=\"showAllPag\">Zobrazit&nbsp;vše</a></li>";

			$S25 = '';
			$S50 = '';
			$S100 = '';
			$S200 = '';
			$Sdefault = 'hidden';
			switch($selected) {
				case("25"):
					$S25 = 'selected';
					break;
				case("50"):
					$S50 = 'selected';
					break;
				case("100"):
					$S100 = 'selected';
					break;
				case("200"):
					$S200 = 'selected';
					break;
				default: 
					$Sdefault = 'selected';
					break;
			}
			$html .= "<li class=\"page-item\"><label class=\"page-link\" for=\"colsCount\">Počet&nbsp;řádků
			<small><select id=\"colsCount\" class=\"changeSelect pageNav selectpicker\" name=\"rowsPerPage\">
				<option value=\"25\" {$S25}>25</option>
				<option value=\"50\" {$S50}>50</option>
				<option value=\"100\" {$S100}>100</option>
				<option value=\"200\" {$S200}>200</option>
				<option value=\"ALL\" {$Sdefault}>Všechno</option>
			</select></small></label></li>";
			if($Sdefault == 'selected') {
				$html .= "<li class=\"page-item disabled\"><a class=\"page-link\" href=\"#\" data-loading=true data-".config("pagination.link_query_key") ."=1 data-selectedPage=true>1</a></li>";
			} else {
				if($currentPage > 1) 
				{
					$html .= "<li class=\"page-item\"><a class=\"page-link changeBtn pageNav\" href=\"#\" name=\"changePage\" data-loading=true data-".config("pagination.link_query_key") ."=1><<<</a></li>";
					$html .= "<li class=\"page-item\"><a class=\"page-link changeBtn pageNav\" href=\"#\" name=\"changePage\" data-loading=true data-".config("pagination.link_query_key") ."=".($currentPage-1)."><</a></li>";
				}

				// loop to get all pages
				for ($i = 1; $i <= $this->_colsCount; $i++)
				{
					// we need other pages link only ..
					if($i == $currentPage)
					{
						$html .= "<li class=\"page-item disabled\"><a class=\"page-link\" href=\"#\" data-loading=true data-".config("pagination.link_query_key") ."={$i} data-selectedPage=true>{$i}</a></li>";
					}
					elseif(abs($i - $currentPage) < 4)
					{
						$html .= "<li class=\"page-item\"><a class=\"page-link changeBtn pageNav\" name=\"changePage\" href=\"#\" data-loading=true data-".config("pagination.link_query_key") ."={$i}>{$i}</a></li>";
					}
				}

				if($currentPage < $this->_colsCount) 
				{
					$html .= "<li class=\"page-item\"><a class=\"page-link changeBtn pageNav\" name=\"changePage\" href=\"#\" data-loading=true data-".config("pagination.link_query_key")."=".($currentPage+1).">></a></li>";
					$html .= "<li class=\"page-item\"><a class=\"page-link changeBtn pageNav\" name=\"changePage\" href=\"#\" data-loading=true data-".config("pagination.link_query_key")."=".($this->_colsCount).">>>></a></li>";
				}
			}
			$html .= "</ul></nav>";
		}

		return $html;
	}
}