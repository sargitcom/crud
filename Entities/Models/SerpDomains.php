<?php

namespace KamilPietrzkiewicz\Software\Web\SeoTulip\Models;

use KamilPietrzkiewicz\Software\Web\SeoTulip\Resources\SerpDomainsResource;

use KamilPietrzkiewicz\Software\Web\SeoTulip\Collections\SerpDomainsCollection;

class SerpDomainsModel 
{
	const NO_LIMIT = 0;

	public function createSerpDomainsResource(SerpDomainsResource $serpDomains) : int
	{
		$sql = "INSERT INTO serp_domains(serp_domain_id, user_id, title, description, added, deleted)";
		$sql .= " VALUES(:serp_domain_id, :user_id, :title, :description, :added, :deleted)";

		$this->db->query($sql);
		$this->db->bind(':serp_domain_id', $serpDomains->getSerpDomainId(), PDO::PARAM_INT);
		$this->db->bind(':user_id', $serpDomains->getUserId(), PDO::PARAM_INT);
		$this->db->bind(':title', $serpDomains->getTitle(), PDO::PARAM_STR);
		$this->db->bind(':description', $serpDomains->getDescription(), PDO::PARAM_STR);
		$this->db->bind(':added', $serpDomains->getAdded()->getTimestamp(), PDO::PARAM_STR);
		$this->db->bind(':deleted', $serpDomains->getDeleted()->getTimestamp(), PDO::PARAM_STR);

		$this->exec();

		return $this->db->lastInsertId();	}

	public function readSerpDomainsCollection(int $page, int $pageLimit) : SerpDomainsCollection
	{
		$sql = "SELECT serp_domain_id, user_id, title, description, added, deleted FROM serp_domains";

		$count = "SELECT count(*) as records_count FROM serp_domains";

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

		$serpDomainsCollection = new SerpDomainsCollection();

		if (empty($results)) {
			return $serpDomainsCollection;
		}

		foreach ($results as $serpDomainsRecord) {
			$serpDomainsResource = $this->mapDbRowToSerpDomainsResource($serpDomainsRecord);

			$serpDomainsCollection->append($serpDomainsResource);
		}

		$serpDomainsCollection->rewind();

		$serpDomainsCollection->setTotal($countResult['records_count']);

		return $serpDomainsCollection;
	}

	public function readSerpDomainsById(int $serpDomainId) : SerpDomainsResource
	{
		$sql = "SELECT serp_domain_id, user_id, title, description, added, deleted FROM serp_domains WHERE serp_domain_id = :serp_domain_id";

		$this->db->query($sql);

		$this->db->bind(':serp_domain_id', $serpDomainId, PDO::PARAM_INT);

		$result = $this->fetchOne();

		$serpDomainsCollection = new SerpDomainsCollection();

		if (empty($result)) {
			return $serpDomainsCollection;
		}

		$serpDomainsResource = $this->mapDbRowToSerpDomainsResource($result);
		$serpDomainsCollection->append($serpDomainsResource);

		$serpDomainsCollection->rewind();

		$serpDomainsCollection->setTotal(1);

		return $serpDomainsCollection;
	}

	private function mapDbRowToSerpDomainsResource(array $dbRecord) : SerpDomainsResource
	{
		$serpDomainsResource = new SerpDomainsResource();
		$serpDomainsResource->setSerpDomainId($serpDomainsRecord['serp_domain_id']);
		$serpDomainsResource->setUserId($serpDomainsRecord['user_id']);
		$serpDomainsResource->setTitle($serpDomainsRecord['title']);
		$serpDomainsResource->setDescription($serpDomainsRecord['description']);
		$serpDomainsResource->setAdded(new \DateTime($serpDomainsRecord['added']));
		$serpDomainsResource->setDeleted(new \DateTime($serpDomainsRecord['deleted']));
		return $serpDomainsResource;
	}

	public function updateSerpDomainsCollectionByCollection(SerpDomainsCollection $serpDomains)
	{
		$sqlTempTable = "CREATE OR REPLACE TEMPORARY TABLE data_update AS SELECT * FROM serp_domains WHERE 1=0";

		$this->db->query($sqlTempTable);

		$this->insertTempSerpDomainsDataIntoTempDbTable($serpDomains);

		$sql = "UPDATE serp_domains JOIN data_update ON serp_domains.serp_domain_id = data_update.serp_domain_id SET "
			."serp_domains.user_id = data_update.user_id"
			.", serp_domains.title = data_update.title"
			.", serp_domains.description = data_update.description"
			.", serp_domains.added = data_update.added"
			.", serp_domains.deleted = data_update.deleted";

		$this->db->query($sql);

		$this->db->exec();
	}

	private function insertTempSerpDomainsDataIntoTempDbTable(SerpDomainsCollection $serpDomains)
	{
		$serpDomains->rewind();

		$sql = "";

		$countResources = 0;

		$insertData = [];

		while ($serpDomains->valid()) {
			$currentRecord = $serpDomains->current();

			$countResources++;

			if ($countResources == 10) {
				$countResources = 0;

				$this->db->query($sql);

				$this->getBindParamsDeclarationForUpdate($insertData);

				$this->db->exec();

				$insertData = [];

				$sql = "";
			}

			$sql .= "INSERT INTO data_update(serp_domain_id, user_id, title, description, added, deleted) VALUES(:serp_domain_id_" . $countResources . ", :user_id_" . $countResources . ", :title_" . $countResources . ", :description_" . $countResources . ", :added_" . $countResources . ", :deleted_" . $countResources . "); ";

			$insertData[$countResources] = [
				'serp_domain_id' => $currentRecord->getSerpDomainId(),
				'user_id' => $currentRecord->getUserId(),
				'title' => $currentRecord->getTitle(),
				'description' => $currentRecord->getDescription(),
				'added' => $currentRecord->getAdded(),
				'deleted' => $currentRecord->getDeleted(),
			];

			$serpDomains->next();

		}

		if ($sql != "") {
			$this->db->query($sql);

			$this->getBindParamsDeclarationForUpdate($insertData);

			$this->db->exec();
		}
	}

	private function getBindParamsDeclarationForUpdateSerpDomainsCollection(array $serpDomainsCollectionContainer)
	{
		foreach ($serpDomainsCollectionContainer as $index => $values) {
			$this->db->bind(":serp_domain_id_" . $index, $values['serp_domain_id'], PDO::PARAM_INT);
			$this->db->bind(":user_id_" . $index, $values['user_id'], PDO::PARAM_INT);
			$this->db->bind(":title_" . $index, $values['title'], PDO::PARAM_STRING);
			$this->db->bind(":description_" . $index, $values['description'], PDO::PARAM_STRING);
			$this->db->bind(":added_" . $index, $values['added'], PDO::PARAM_STRING);
			$this->db->bind(":deleted_" . $index, $values['deleted'], PDO::PARAM_STRING);
		}
	}

	public function updateSerpDomainsResourceByResource(SerpDomainsResource $serpDomains)
	{
		$sql = "UPDATE serp_domains SET "
			. "user_id = : user_id"
			. " ,title = : title"
			. " ,description = : description"
			. " ,added = : added"
			. " ,deleted = : deleted"
			" WHERE serp_domains.serp_domain_id = :serp_domain_id";

		$this->db->query($sql);

		$this->db->bind(':serp_domain_id', $serpDomains->getSerpDomainId(), PDO::PARAM_INT);
		$this->db->bind(':user_id', $serpDomains->getUserId(), PDO::PARAM_INT);
		$this->db->bind(':title', $serpDomains->getTitle(), PDO::PARAM_STRING);
		$this->db->bind(':description', $serpDomains->getDescription(), PDO::PARAM_STRING);
		$this->db->bind(':added', $serpDomains->getAdded(), PDO::PARAM_STRING);
		$this->db->bind(':deleted', $serpDomains->getDeleted(), PDO::PARAM_STRING);
		$this->db->exec();
	}

	public function deleteSerpDomainsResourceById(SerpDomainsResource $serpDomains)
	{
		$sql = "delete serp_domains where serp_domain_id = :serp_domain_id";

		$this->db->query($sql);

		$this->db->bind(':serp_domain_id', $serpDomains->getSerpDomainId());
		$this->db->exec();
	}
}
