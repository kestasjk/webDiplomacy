<?php
/*
    Copyright (C) 2025 Kestas J. Kuliukas

	This file is part of webDiplomacy.

    webDiplomacy is free software: you can redistribute it and/or modify
    it under the terms of the GNU Affero General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    webDiplomacy is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU Affero General Public License
    along with webDiplomacy.  If not, see <http://www.gnu.org/licenses/>.
 */

defined('IN_CODE') or die('This script can not be run by itself.');

require_once('database.php');

/**
 * A Database wrapper class that tracks metrics for API performance monitoring.
 * Extends the regular Database class to count operations and measure timing.
 *
 * @package Base
 */
class MetricsDatabase extends Database {

	/**
	 * @var int Number of fetch operations (sql_tabl, sql_row, sql_hash)
	 */
	public $metricsFetchCount = 0;

	/**
	 * @var int Number of put operations (sql_put)
	 */
	public $metricsPutCount = 0;

	/**
	 * @var float Total time spent on fetch operations in seconds
	 */
	public $metricsFetchTime = 0.0;

	/**
	 * @var float Total time spent on put operations in seconds
	 */
	public $metricsPutTime = 0.0;

	/**
	 * Reset all metrics to zero
	 */
	public function resetMetrics() {
		$this->metricsFetchCount = 0;
		$this->metricsPutCount = 0;
		$this->metricsFetchTime = 0.0;
		$this->metricsPutTime = 0.0;
	}

	/**
	 * Get current metrics as an array
	 * @return array Metrics with counts and times in milliseconds
	 */
	public function getMetrics() {
		return array(
			'db_get' => $this->metricsFetchCount,
			'db_put' => $this->metricsPutCount,
			'db_time_ms' => round(($this->metricsFetchTime + $this->metricsPutTime) * 1000)
		);
	}

	/**
	 * Override sql_tabl to track fetch operations
	 */
	public function sql_tabl($sql) {
		$startTime = microtime(true);
		$result = parent::sql_tabl($sql);
		$endTime = microtime(true);

		$this->metricsFetchCount++;
		$this->metricsFetchTime += ($endTime - $startTime);

		return $result;
	}

	/**
	 * Override sql_row to track fetch operations
	 */
	public function sql_row($sql) {
		$startTime = microtime(true);
		$result = parent::sql_row($sql);
		$endTime = microtime(true);

		$this->metricsFetchCount++;
		$this->metricsFetchTime += ($endTime - $startTime);

		return $result;
	}

	/**
	 * Override sql_hash to track fetch operations
	 */
	public function sql_hash($sql) {
		$startTime = microtime(true);
		$result = parent::sql_hash($sql);
		$endTime = microtime(true);

		$this->metricsFetchCount++;
		$this->metricsFetchTime += ($endTime - $startTime);

		return $result;
	}

	/**
	 * Override sql_put to track put operations
	 */
	public function sql_put($sql) {
		$startTime = microtime(true);
		parent::sql_put($sql);
		$endTime = microtime(true);

		$this->metricsPutCount++;
		$this->metricsPutTime += ($endTime - $startTime);
	}
}
?>