<?php
Interface CacheInterface{
	/**
	 * Cache Record Object
	 * @param Record $record
	 * @param Int $ttl
	 * @return void
	 */
	public function cacheRecord($record,$ttl=null);
	/**
	 * Read record by id
	 * @param String $entityName
	 * @param String or Int $id
	 * @return Record Object
	 */
	public function readRecord($entityName,$id);
	/**
	 * Remove record
	 * @param Record $record
	 * @return void
	 */
	public function clearRecord($record);
	/**
	 * Remove record by id
	 * @param String $entityName
	 * @param String or Int $id
	 * @return void
	 */
	public function clearRecordById($entityName,$id);
}
?>