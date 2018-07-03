<?php

namespace KamilPietrzkiewicz\Software\Web\SeoTulip\Models;

use KamilPietrzkiewicz\Software\Web\SeoTulip\Resources\SearchEnignesVendorsResource;

use KamilPietrzkiewicz\Software\Web\SeoTulip\Collections\SearchEnignesVendorsCollection;

class SearchEnignesVendorsModel 
{
	const NO_LIMIT = 0;

	public function createSearchEnignesVendorsResource(SearchEnignesVendorsResource $searchEnignesVendors) : int
	{
		$sql = "INSERT INTO search_enignes_vendors(search_engines_vendor_id, vendor_name)";
		$sql .= " VALUES(:search_engines_vendor_id, :vendor_name)";

		$this->db->query($sql);
		$this->db->bind(':search_engines_vendor_id', $searchEnignesVendors->getSearchEnginesVendorId(), PDO::PARAM_INT);
		$this->db->bind(':vendor_name', $searchEnignesVendors->getVendorName(), PDO::PARAM_STR);

		$this->exec();

		return $this->db->lastInsertId();	}

	public function readSearchEnignesVendorsCollection(int $page, int $pageLimit) : SearchEnignesVendorsCollection
	{
		$sql = "SELECT search_engines_vendor_id, vendor_name FROM search_enignes_vendors";

		$count = "SELECT count(*) as records_count FROM search_enignes_vendors";

		if($pageLimit == 0) {
			$currentRow = ($page - 1) * $pageLimit;
			$sql .= " LIMIT " . $currentRow;
		}

		if($pageLimit > 0) {
			$currentRow = ($page - 1) * $pageLimit;
			$sql .= " LIMIT " . $currentRow .  ", " . $pageLimit;
		}

		$this->db->query($sql);
		$results = $this->fetch();

		$this->db->query($count);
		$countResult = $this->fetchOne();

		$searchEnignesVendorsCollection = new SearchEnignesVendorsCollection();

		if (empty($results)) {
			return $searchEnignesVendorsCollection;
		}

		foreach ($results as $searchEnignesVendorsRecord) {
			$searchEnignesVendorsResource = $this->mapDbRowToSearchEnignesVendorsResource($searchEnignesVendorsRecord);

			$searchEnignesVendorsCollection->append($searchEnignesVendorsResource);
		}

		$searchEnignesVendorsCollection->rewind();

		$searchEnignesVendorsCollection->setTotal($countResult['records_count']);

		return $searchEnignesVendorsCollection;
	}

	public function readSearchEnignesVendorsById(int $searchEnginesVendorId) : SearchEnignesVendorsResource
	{
		$sql = "SELECT search_engines_vendor_id, vendor_name FROM search_enignes_vendors WHERE search_engines_vendor_id = :search_engines_vendor_id";

		$this->db->query($sql);

		$this->db->bind(':search_engines_vendor_id', $searchEnginesVendorId, PDO::PARAM_INT);

		$result = $this->fetchOne();

		$searchEnignesVendorsCollection = new SearchEnignesVendorsCollection();

		if (empty($result)) {
			return $searchEnignesVendorsCollection;
		}

		$searchEnignesVendorsResource = $this->mapDbRowToSearchEnignesVendorsResource($result);
		$searchEnignesVendorsCollection->append($searchEnignesVendorsResource);

		$searchEnignesVendorsCollection->rewind();

		$searchEnignesVendorsCollection->setTotal(1);

		return $searchEnignesVendorsCollection;
	}

	private function mapDbRowToSearchEnignesVendorsResource(array $dbRecord) : SearchEnignesVendorsResource
	{
		$searchEnignesVendorsResource = new SearchEnignesVendorsResource();
		$searchEnignesVendorsResource->setSearchEnginesVendorId($searchEnignesVendorsRecord['search_engines_vendor_id']);
		$searchEnignesVendorsResource->setVendorName($searchEnignesVendorsRecord['vendor_name']);
		return $searchEnignesVendorsResource;
	}

	public function updateSearchEnignesVendorsCollectionByCollection(SearchEnignesVendorsCollection $searchEnignesVendors)
	{
		$sqlTempTable = "CREATE OR REPLACE TEMPORARY TABLE data_update AS SELECT * FROM search_enignes_vendors WHERE 1=0";

		$this->db->query($sqlTempTable);

		$this->insertTempSearchEnignesVendorsDataIntoTempDbTable($searchEnignesVendors);

		$sql = "UPDATE search_enignes_vendors JOIN data_update ON search_enignes_vendors.search_engines_vendor_id = data_update.search_engines_vendor_id SET "
			."search_enignes_vendors.vendor_name = data_update.vendor_name";

		$this->db->query($sql);

		$this->db->exec();
	}

	private function insertTempSearchEnignesVendorsDataIntoTempDbTable(SearchEnignesVendorsCollection $searchEnignesVendors)
	{
		$searchEnignesVendors->rewind();

		$sql = "";

		$countResources = 0;

		$insertData = [];

		while ($searchEnignesVendors->valid()) {
			$currentRecord = $searchEnignesVendors->current();

			$countResources++;

			if ($countResources == 10) {
				$countResources = 0;

				$this->db->query($sql);

				$this->getBindParamsDeclarationForUpdate($insertData);

				$this->db->exec();

				$insertData = [];

				$sql = "";
			}

			$sql .= "INSERT INTO data_update(search_engines_vendor_id, vendor_name) VALUES(:search_engines_vendor_id_" . $countResources . ", :vendor_name_" . $countResources . "); ";

			$insertData[$countResources] = [
				'search_engines_vendor_id' => $currentRecord->getSearchEnginesVendorId(),
				'vendor_name' => $currentRecord->getVendorName(),
			];

			$searchEnignesVendors->next();

		}

		if ($sql != "") {
			$this->db->query($sql);

			$this->getBindParamsDeclarationForUpdate($insertData);

			$this->db->exec();
		}
	}

	private function getBindParamsDeclarationForUpdateSearchEnignesVendorsCollection(array $searchEnignesVendorsCollectionContainer)
	{
		foreach ($searchEnignesVendorsCollectionContainer as $index => $values) {
			$this->db->bind(":search_engines_vendor_id_" . $index, $values['search_engines_vendor_id'], PDO::PARAM_INT);
			$this->db->bind(":vendor_name_" . $index, $values['vendor_name'], PDO::PARAM_STRING);
		}
	}

	public function updateSearchEnignesVendorsResourceByResource(SearchEnignesVendorsResource $searchEnignesVendors)
	{
		$sql = "UPDATE search_enignes_vendors SET "
			. "vendor_name = : vendor_name"
			" WHERE search_enignes_vendors.search_engines_vendor_id = :search_engines_vendor_id";

		$this->db->query($sql);

		$this->db->bind(':search_engines_vendor_id', $searchEnignesVendors->getSearchEnginesVendorId(), PDO::PARAM_INT);
		$this->db->bind(':vendor_name', $searchEnignesVendors->getVendorName(), PDO::PARAM_STRING);
		$this->db->exec();
	}

	public function deleteSearchEnignesVendorsResourceById(SearchEnignesVendorsResource $searchEnignesVendors)
	{
		$sql = "delete search_enignes_vendors where search_engines_vendor_id = :search_engines_vendor_id";

		$this->db->query($sql);

		$this->db->bind(':search_engines_vendor_id', $searchEnignesVendors->getSearchEnginesVendorId());
		$this->db->exec();
	}
}
