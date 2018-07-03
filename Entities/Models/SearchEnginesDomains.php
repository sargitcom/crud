<?php

namespace KamilPietrzkiewicz\Software\Web\SeoTulip\Models;

use KamilPietrzkiewicz\Software\Web\SeoTulip\Resources\SearchEnginesDomainsResource;

use KamilPietrzkiewicz\Software\Web\SeoTulip\Collections\SearchEnginesDomainsCollection;

class SearchEnginesDomainsModel 
{
	const NO_LIMIT = 0;

	public function createSearchEnginesDomainsResource(SearchEnginesDomainsResource $searchEnginesDomains) : int
	{
		$sql = "INSERT INTO search_engines_domains(search_engines_domain_id, description, domain, search_engines_vendor_id)";
		$sql .= " VALUES(:search_engines_domain_id, :description, :domain, :search_engines_vendor_id)";

		$this->db->query($sql);
		$this->db->bind(':search_engines_domain_id', $searchEnginesDomains->getSearchEnginesDomainId(), PDO::PARAM_INT);
		$this->db->bind(':description', $searchEnginesDomains->getDescription(), PDO::PARAM_STR);
		$this->db->bind(':domain', $searchEnginesDomains->getDomain(), PDO::PARAM_STR);
		$this->db->bind(':search_engines_vendor_id', $searchEnginesDomains->getSearchEnginesVendorId(), PDO::PARAM_INT);

		$this->exec();

		return $this->db->lastInsertId();	}

	public function readSearchEnginesDomainsCollection(int $page, int $pageLimit) : SearchEnginesDomainsCollection
	{
		$sql = "SELECT search_engines_domain_id, description, domain, search_engines_vendor_id FROM search_engines_domains";

		$count = "SELECT count(*) as records_count FROM search_engines_domains";

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

		$searchEnginesDomainsCollection = new SearchEnginesDomainsCollection();

		if (empty($results)) {
			return $searchEnginesDomainsCollection;
		}

		foreach ($results as $searchEnginesDomainsRecord) {
			$searchEnginesDomainsResource = $this->mapDbRowToSearchEnginesDomainsResource($searchEnginesDomainsRecord);

			$searchEnginesDomainsCollection->append($searchEnginesDomainsResource);
		}

		$searchEnginesDomainsCollection->rewind();

		$searchEnginesDomainsCollection->setTotal($countResult['records_count']);

		return $searchEnginesDomainsCollection;
	}

	public function readSearchEnginesDomainsById(int $searchEnginesDomainId) : SearchEnginesDomainsResource
	{
		$sql = "SELECT search_engines_domain_id, description, domain, search_engines_vendor_id FROM search_engines_domains WHERE search_engines_domain_id = :search_engines_domain_id";

		$this->db->query($sql);

		$this->db->bind(':search_engines_domain_id', $searchEnginesDomainId, PDO::PARAM_INT);

		$result = $this->fetchOne();

		$searchEnginesDomainsCollection = new SearchEnginesDomainsCollection();

		if (empty($result)) {
			return $searchEnginesDomainsCollection;
		}

		$searchEnginesDomainsResource = $this->mapDbRowToSearchEnginesDomainsResource($result);
		$searchEnginesDomainsCollection->append($searchEnginesDomainsResource);

		$searchEnginesDomainsCollection->rewind();

		$searchEnginesDomainsCollection->setTotal(1);

		return $searchEnginesDomainsCollection;
	}

	private function mapDbRowToSearchEnginesDomainsResource(array $dbRecord) : SearchEnginesDomainsResource
	{
		$searchEnginesDomainsResource = new SearchEnginesDomainsResource();
		$searchEnginesDomainsResource->setSearchEnginesDomainId($searchEnginesDomainsRecord['search_engines_domain_id']);
		$searchEnginesDomainsResource->setDescription($searchEnginesDomainsRecord['description']);
		$searchEnginesDomainsResource->setDomain($searchEnginesDomainsRecord['domain']);
		$searchEnginesDomainsResource->setSearchEnginesVendorId($searchEnginesDomainsRecord['search_engines_vendor_id']);
		return $searchEnginesDomainsResource;
	}

	public function updateSearchEnginesDomainsCollectionByCollection(SearchEnginesDomainsCollection $searchEnginesDomains)
	{
		$sqlTempTable = "CREATE OR REPLACE TEMPORARY TABLE data_update AS SELECT * FROM search_engines_domains WHERE 1=0";

		$this->db->query($sqlTempTable);

		$this->insertTempSearchEnginesDomainsDataIntoTempDbTable($searchEnginesDomains);

		$sql = "UPDATE search_engines_domains JOIN data_update ON search_engines_domains.search_engines_domain_id = data_update.search_engines_domain_id SET "
			."search_engines_domains.description = data_update.description"
			.", search_engines_domains.domain = data_update.domain"
			.", search_engines_domains.search_engines_vendor_id = data_update.search_engines_vendor_id";

		$this->db->query($sql);

		$this->db->exec();
	}

	private function insertTempSearchEnginesDomainsDataIntoTempDbTable(SearchEnginesDomainsCollection $searchEnginesDomains)
	{
		$searchEnginesDomains->rewind();

		$sql = "";

		$countResources = 0;

		$insertData = [];

		while ($searchEnginesDomains->valid()) {
			$currentRecord = $searchEnginesDomains->current();

			$countResources++;

			if ($countResources == 10) {
				$countResources = 0;

				$this->db->query($sql);

				$this->getBindParamsDeclarationForUpdate($insertData);

				$this->db->exec();

				$insertData = [];

				$sql = "";
			}

			$sql .= "INSERT INTO data_update(search_engines_domain_id, description, domain, search_engines_vendor_id) VALUES(:search_engines_domain_id_" . $countResources . ", :description_" . $countResources . ", :domain_" . $countResources . ", :search_engines_vendor_id_" . $countResources . "); ";

			$insertData[$countResources] = [
				'search_engines_domain_id' => $currentRecord->getSearchEnginesDomainId(),
				'description' => $currentRecord->getDescription(),
				'domain' => $currentRecord->getDomain(),
				'search_engines_vendor_id' => $currentRecord->getSearchEnginesVendorId(),
			];

			$searchEnginesDomains->next();

		}

		if ($sql != "") {
			$this->db->query($sql);

			$this->getBindParamsDeclarationForUpdate($insertData);

			$this->db->exec();
		}
	}

	private function getBindParamsDeclarationForUpdateSearchEnginesDomainsCollection(array $searchEnginesDomainsCollectionContainer)
	{
		foreach ($searchEnginesDomainsCollectionContainer as $index => $values) {
			$this->db->bind(":search_engines_domain_id_" . $index, $values['search_engines_domain_id'], PDO::PARAM_INT);
			$this->db->bind(":description_" . $index, $values['description'], PDO::PARAM_STRING);
			$this->db->bind(":domain_" . $index, $values['domain'], PDO::PARAM_STRING);
			$this->db->bind(":search_engines_vendor_id_" . $index, $values['search_engines_vendor_id'], PDO::PARAM_INT);
		}
	}

	public function updateSearchEnginesDomainsResourceByResource(SearchEnginesDomainsResource $searchEnginesDomains)
	{
		$sql = "UPDATE search_engines_domains SET "
			. "description = : description"
			. " ,domain = : domain"
			. " ,search_engines_vendor_id = : search_engines_vendor_id"
			" WHERE search_engines_domains.search_engines_domain_id = :search_engines_domain_id";

		$this->db->query($sql);

		$this->db->bind(':search_engines_domain_id', $searchEnginesDomains->getSearchEnginesDomainId(), PDO::PARAM_INT);
		$this->db->bind(':description', $searchEnginesDomains->getDescription(), PDO::PARAM_STRING);
		$this->db->bind(':domain', $searchEnginesDomains->getDomain(), PDO::PARAM_STRING);
		$this->db->bind(':search_engines_vendor_id', $searchEnginesDomains->getSearchEnginesVendorId(), PDO::PARAM_INT);
		$this->db->exec();
	}

	public function deleteSearchEnginesDomainsResourceById(SearchEnginesDomainsResource $searchEnginesDomains)
	{
		$sql = "delete search_engines_domains where search_engines_domain_id = :search_engines_domain_id";

		$this->db->query($sql);

		$this->db->bind(':search_engines_domain_id', $searchEnginesDomains->getSearchEnginesDomainId());
		$this->db->exec();
	}
}
