<?php

namespace KamilPietrzkiewicz\Software\Web\SeoTulip\Models;

use KamilPietrzkiewicz\Software\Web\SeoTulip\Resources\PagesResource;

use KamilPietrzkiewicz\Software\Web\SeoTulip\Collections\PagesCollection;

class PagesModel 
{
	const NO_LIMIT = 0;

	public function createPagesResource(PagesResource $pages) : int
	{
		$sql = "INSERT INTO pages(page_id, user_id, domain, description, added, deleted)";
		$sql .= " VALUES(:page_id, :user_id, :domain, :description, :added, :deleted)";

		$this->db->query($sql);
		$this->db->bind(':page_id', $pages->getPageId(), PDO::PARAM_INT);
		$this->db->bind(':user_id', $pages->getUserId(), PDO::PARAM_INT);
		$this->db->bind(':domain', $pages->getDomain(), PDO::PARAM_STR);
		$this->db->bind(':description', $pages->getDescription(), PDO::PARAM_STR);
		$this->db->bind(':added', $pages->getAdded()->getTimestamp(), PDO::PARAM_STR);
		$this->db->bind(':deleted', $pages->getDeleted()->getTimestamp(), PDO::PARAM_STR);

		$this->exec();

		return $this->db->lastInsertId();	}

	public function readPagesCollection(int $page, int $pageLimit) : PagesCollection
	{
		$sql = "SELECT page_id, user_id, domain, description, added, deleted FROM pages";

		$count = "SELECT count(*) as records_count FROM pages";

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

		$pagesCollection = new PagesCollection();

		if (empty($results)) {
			return $pagesCollection;
		}

		foreach ($results as $pagesRecord) {
			$pagesResource = $this->mapDbRowToPagesResource($pagesRecord);

			$pagesCollection->append($pagesResource);
		}

		$pagesCollection->rewind();

		$pagesCollection->setTotal($countResult['records_count']);

		return $pagesCollection;
	}

	public function readPagesById(int $pageId) : PagesResource
	{
		$sql = "SELECT page_id, user_id, domain, description, added, deleted FROM pages WHERE page_id = :page_id";

		$this->db->query($sql);

		$this->db->bind(':page_id', $pageId, PDO::PARAM_INT);

		$result = $this->fetchOne();

		$pagesCollection = new PagesCollection();

		if (empty($result)) {
			return $pagesCollection;
		}

		$pagesResource = $this->mapDbRowToPagesResource($result);
		$pagesCollection->append($pagesResource);

		$pagesCollection->rewind();

		$pagesCollection->setTotal(1);

		return $pagesCollection;
	}

	private function mapDbRowToPagesResource(array $dbRecord) : PagesResource
	{
		$pagesResource = new PagesResource();
		$pagesResource->setPageId($pagesRecord['page_id']);
		$pagesResource->setUserId($pagesRecord['user_id']);
		$pagesResource->setDomain($pagesRecord['domain']);
		$pagesResource->setDescription($pagesRecord['description']);
		$pagesResource->setAdded(new \DateTime($pagesRecord['added']));
		$pagesResource->setDeleted(new \DateTime($pagesRecord['deleted']));
		return $pagesResource;
	}

	public function updatePagesCollectionByCollection(PagesCollection $pages)
	{
		$sqlTempTable = "CREATE OR REPLACE TEMPORARY TABLE data_update AS SELECT * FROM pages WHERE 1=0";

		$this->db->query($sqlTempTable);

		$this->insertTempPagesDataIntoTempDbTable($pages);

		$sql = "UPDATE pages JOIN data_update ON pages.page_id = data_update.page_id SET "
			."pages.user_id = data_update.user_id"
			.", pages.domain = data_update.domain"
			.", pages.description = data_update.description"
			.", pages.added = data_update.added"
			.", pages.deleted = data_update.deleted";

		$this->db->query($sql);

		$this->db->exec();
	}

	private function insertTempPagesDataIntoTempDbTable(PagesCollection $pages)
	{
		$pages->rewind();

		$sql = "";

		$countResources = 0;

		$insertData = [];

		while ($pages->valid()) {
			$currentRecord = $pages->current();

			$countResources++;

			if ($countResources == 10) {
				$countResources = 0;

				$this->db->query($sql);

				$this->getBindParamsDeclarationForUpdate($insertData);

				$this->db->exec();

				$insertData = [];

				$sql = "";
			}

			$sql .= "INSERT INTO data_update(page_id, user_id, domain, description, added, deleted) VALUES(:page_id_" . $countResources . ", :user_id_" . $countResources . ", :domain_" . $countResources . ", :description_" . $countResources . ", :added_" . $countResources . ", :deleted_" . $countResources . "); ";

			$insertData[$countResources] = [
				'page_id' => $currentRecord->getPageId(),
				'user_id' => $currentRecord->getUserId(),
				'domain' => $currentRecord->getDomain(),
				'description' => $currentRecord->getDescription(),
				'added' => $currentRecord->getAdded(),
				'deleted' => $currentRecord->getDeleted(),
			];

			$pages->next();

		}

		if ($sql != "") {
			$this->db->query($sql);

			$this->getBindParamsDeclarationForUpdate($insertData);

			$this->db->exec();
		}
	}

	private function getBindParamsDeclarationForUpdatePagesCollection(array $pagesCollectionContainer)
	{
		foreach ($pagesCollectionContainer as $index => $values) {
			$this->db->bind(":page_id_" . $index, $values['page_id'], PDO::PARAM_INT);
			$this->db->bind(":user_id_" . $index, $values['user_id'], PDO::PARAM_INT);
			$this->db->bind(":domain_" . $index, $values['domain'], PDO::PARAM_STRING);
			$this->db->bind(":description_" . $index, $values['description'], PDO::PARAM_STRING);
			$this->db->bind(":added_" . $index, $values['added'], PDO::PARAM_STRING);
			$this->db->bind(":deleted_" . $index, $values['deleted'], PDO::PARAM_STRING);
		}
	}

	public function updatePagesResourceByResource(PagesResource $pages)
	{
		$sql = "UPDATE pages SET "
			. "user_id = : user_id"
			. " ,domain = : domain"
			. " ,description = : description"
			. " ,added = : added"
			. " ,deleted = : deleted"
			" WHERE pages.page_id = :page_id";

		$this->db->query($sql);

		$this->db->bind(':page_id', $pages->getPageId(), PDO::PARAM_INT);
		$this->db->bind(':user_id', $pages->getUserId(), PDO::PARAM_INT);
		$this->db->bind(':domain', $pages->getDomain(), PDO::PARAM_STRING);
		$this->db->bind(':description', $pages->getDescription(), PDO::PARAM_STRING);
		$this->db->bind(':added', $pages->getAdded(), PDO::PARAM_STRING);
		$this->db->bind(':deleted', $pages->getDeleted(), PDO::PARAM_STRING);
		$this->db->exec();
	}

	public function deletePagesResourceById(PagesResource $pages)
	{
		$sql = "delete pages where page_id = :page_id";

		$this->db->query($sql);

		$this->db->bind(':page_id', $pages->getPageId());
		$this->db->exec();
	}
}
