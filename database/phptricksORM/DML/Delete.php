<?php

namespace PHPtricks\Orm\DML;

trait Delete
{
	/**
	 * delete from table
	 * @return bool
	 */
	public function delete()
	{
        $results = (array)$this->_results;

        // check if its empty
        if(!count($results))
        {
            // try to call select() method
            try
            {
                $results = (array)$this->select()->results();
                if(count($results) == 1) $results = $results[0];
            }
            catch (\Exception $e)
            {
                return false;
            }
        }

        if($this->count() == 1)
		{
			if(gettype($results) == "array") {
				$results = $results[0];
			}
			return $this->remove($results);
		}

		for($i = 0; $this->count() > $i; $i++)
		{
			$this->remove( $results[$i]);
		}

		return true;
	}

	private function remove($data)
	{
		$this->_where = "WHERE";
		$x = 1;

		foreach($data as $i => $row)
		{
			if(!is_numeric($row))
				$this->_where .= " {$i} = '{$row}'";
			else
				$this->_where .= " {$i} = {$row}";
			// add comma between values
			if($x < count((array)$data)) {
				$this->_where .= " AND";
			}
			$x++;
		}

		$sql = "DELETE FROM $this->_table " . $this->_where;
		return $this->query($sql);
	}
}
