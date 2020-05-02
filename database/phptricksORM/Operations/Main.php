<?php

namespace PHPtricks\Orm\Operations;

trait Main
{
	use Where;
	use Cond;
	use parseWhere;
	use Other;

	/**
	 * get count of rows for last select query
	 * @return int
	 */
	public function count()
	{
		$results = (array)$this->results();
		return isset($results[0]) ? count($this->_results) : 0;
	}
}
