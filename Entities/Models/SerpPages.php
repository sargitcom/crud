<?php

namespace KamilPietrzkiewicz\Software\Web\SeoTulip\Models;

use KamilPietrzkiewicz\Software\Web\SeoTulip\Resources\SerpPagesResource;

use KamilPietrzkiewicz\Software\Web\SeoTulip\Collections\SerpPagesCollection;

class SerpPagesModel 
{
	const NO_LIMIT = 0;

	public function createSerpPagesResource(SerpPagesResource $serpPages) : int
	{
		$sql = "INSERT INTO serp_pages(serp_page_id, user_id, title, description, serp_client_id, serp_domain_id, deleted)";
		$sql .= " VALUES(:serp_page_id, :user_id, :title, :description, :serp_client_id, :serp_domain_id, :deleted)";

		$this->db->query($sql);
		$this->db->bind(':serp_page_id', $serpPages->getSerpPageId(), PDO::PARAM_INT);
		$this->db->bind(':user_id', $serpPages->getUserId(), PDO::PARAM_INT);
		$this->db->bind(':title', $serpPages->getTitle(), PDO::PARAM_INT);
		$this->db->bind(':description', $serpPages->getDescription(), PDO::PARAM_STR);
		$this->db->bind(':serp_client_id', $serpPages->getSerpClientId(), PDO::PARAM_INT);
		$this->db->bind(':serp_domain_id', $serpPages->getSerpDomainId(), PDO::PARAM_INT);
		$this->db->bind(':deleted', $serpPages->getDeleted()->getTimestamp(), PDO::PARAM_STR);

		$this->exec();

		return $this->db->lastInsertId();	}

	public function readSerpPagesCollection(int $page, int $pageLimit) : SerpPagesCollection
	{
		$sql = "SELECT serp_page_id, user_id, title, description, serp_client_id, serp_domain_id, deleted FROM serp_pages";

		$count = "SELECT count(*) as records_count FROM serp_pages";

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

		$serpPagesCollection = new SerpPagesCollection();

		if (empty($results)) {
			return $serpPagesCollection;
		}

		foreach ($results as $serpPagesRecord) {
			$serpPagesResource = $this->mapDbRowToSerpPagesResource($serpPagesRecord);

			$serpPagesCollection->append($serpPagesResource);
		}

		$serpPagesCollection->rewind();

		$serpPagesCollection->setTotal($countResult['records_count']);

		return $serpPagesCollection;
	}

	public function readSerpPagesById(int $serpPageId) : SerpPagesResource
	{
		$sql = "SELECT serp_page_id, user_id, title, description, serp_client_id, serp_domain_id, deleted FROM serp_pages WHERE serp_page_id = :serp_page_id";

		$this->db->query($sql);

		$this->db->bind(':serp_page_id', $serpPageId, PDO::PARAM_INT);

		$result = $this->fetchOne();

		$serpPagesCollection = new SerpPagesCollection();

		if (empty($result)) {
			return $serpPagesCollection;
		}

		$serpPagesResource = $this->mapDbRowToSerpPagesResource($result);
		$serpPagesCollection->append($serpPagesResource);

		$serpPagesCollection->rewind();

		$serpPagesCollection->setTotal(1);

		return $serpPagesCollection;
	}

	private function mapDbRowToSerpPagesResource(array $dbRecord) : SerpPagesResource
	{
		$serpPagesResource = new SerpPagesResource();
		$serpPagesResource->setSerpPageId($serpPagesRecord['serp_page_id']);
		$serpPagesResource->setUserId($serpPagesRecord['user_id']);
		$serpPagesResource->setTitle($serpPagesRecord['title']);
		$serpPagesResource->setDescription($serpPagesRecord['description']);
		$serpPagesResource->setSerpClientId($serpPagesRecord['serp_client_id']);
		$serpPagesResource->setSerpDomainId($serpPagesRecord['serp_domain_id']);
		$serpPagesResource->setDeleted(new \DateTime($serpPagesRecord['deleted']));
		return $serpPagesResource;
	}

	public function updateSerpPagesCollectionByCollection(SerpPagesCollection $serpPages)
	{
		$sqlTempTable = "CREATE OR REPLACE TEMPORARY TABLE data_update AS SELECT * FROM serp_pages WHERE 1=0";

		$this->db->query($sqlTempTable);

		$this->insertTempSerpPagesDataIntoTempDbTable($serpPages);

		$sql = "UPDATE serp_pages JOIN data_update ON serp_pages.serp_page_id = data_update.serp_page_id SET "
			."serp_pages.user_id = data_update.user_id"
			.", serp_pages.title = data_update.title"
			.", serp_pages.description = data_update.description"
			.", serp_pages.serp_client_id = data_update.serp_client_id"
			.", serp_pages.serp_domain_id = data_update.serp_domain_id"
			.", serp_pages.deleted = data_update.deleted";

		$this->db->query($sql);

		$this->db->exec();
	}

	private function insertTempSerpPagesDataIntoTempDbTable(SerpPagesCollection $serpPages)
	{
		$serpPages->rewind();

		$sql = "";

		$countResources = 0;

		$insertData = [];

		while ($serpPages->valid()) {
			$currentRecord = $serpPages->current();

			$countResources++;

			if ($countResources == 10) {
				$countResources = 0;

				$this->db->query($sql);

				$this->getBindParamsDeclarationForUpdate($insertData);

				$this->db->exec();

				$insertData = [];

				$sql = "";
			}

			$sql .= "INSERT INTO data_update(serp_page_id, user_id, title, description, serp_client_id, serp_domain_id, deleted) VALUES(:serp_page_id_" . $countResources . ", :user_id_" . $countResources . ", :title_" . $countResources . ", :description_" . $countResources . ", :serp_client_id_" . $countResources . ", :serp_domain_id_" . $countResources . ", :deleted_" . $countResources . "); ";

			$insertData[$countResources] = [
				'serp_page_id' => $currentRecord->getSerpPageId(),
				'user_id' => $currentRecord->getUserId(),
				'title' => $currentRecord->getTitle(),
				'description' => $currentRecord->getDescription(),
				'serp_client_id' => $currentRecord->getSerpClientId(),
				'serp_domain_id' => $currentRecord->getSerpDomainId(),
				'deleted' => $currentRecord->getDeleted(),
			];

			$serpPages->next();

		}

		if ($sql != "") {
			$this->db->query($sql);

			$this->getBindParamsDeclarationForUpdate($insertData);

			$this->db->exec();
		}
	}

	private function getBindParamsDeclarationForUpdateSerpPagesCollection(array $serpPagesCollectionContainer)
	{
		foreach ($serpPagesCollectionContainer as $index => $values) {
			$this->db->bind(":serp_page_id_" . $index, $values['serp_page_id'], PDO::PARAM_INT);
			$this->db->bind(":user_id_" . $index, $values['user_id'], PDO::PARAM_INT);
			$this->db->bind(":title_" . $index, $values['title'], PDO::PARAM_INT);
			$this->db->bind(":description_" . $index, $values['description'], PDO::PARAM_STRING);
			$this->db->bind(":serp_client_id_" . $index, $values['serp_client_id'], PDO::PARAM_INT);
			$this->db->bind(":serp_domain_id_" . $index, $values['serp_domain_id'], PDO::PARAM_INT);
			$this->db->bind(":deleted_" . $index, $values['deleted'], PDO::PARAM_STRING);
		}
	}

	public function updateSerpPagesResourceByResource(SerpPagesResource $serpPages)
	{
		$sql = "UPDATE serp_pages SET "
			. "user_id = : user_id"
			. " ,title = : title"
			. " ,description = : description"
			. " ,serp_client_id = : serp_client_id"
			. " ,serp_domain_id = : serp_domain_id"
			. " ,deleted = : deleted"
			" WHERE serp_pages.serp_page_id = :serp_page_id";

		$this->db->query($sql);

		$this->db->bind(':serp_page_id', $serpPages->getSerpPageId(), PDO::PARAM_INT);
		$this->db->bind(':user_id', $serpPages->getUserId(), PDO::PARAM_INT);
		$this->db->bind(':title', $serpPages->getTitle(), PDO::PARAM_INT);
		$this->db->bind(':description', $serpPages->getDescription(), PDO::PARAM_STRING);
		$this->db->bind(':serp_client_id', $serpPages->getSerpClientId(), PDO::PARAM_INT);
		$this->db->bind(':serp_domain_id', $serpPages->getSerpDomainId(), PDO::PARAM_INT);
		$this->db->bind(':deleted', $serpPages->getDeleted(), PDO::PARAM_STRING);
		$this->db->exec();
	}

	public function deleteSerpPagesResourceById(SerpPagesResource $serpPages)
	{
		$sql = "delete serp_pages where serp_page_id = :serp_page_id";

		$this->db->query($sql);

		$this->db->bind(':serp_page_id', $serpPages->getSerpPageId());
		$this->db->exec();
	}
}
