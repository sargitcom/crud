<?php

namespace KamilPietrzkiewicz\Software\Web\SeoTulip\Models;

use KamilPietrzkiewicz\Software\Web\SeoTulip\Resources\SerpClientsResource;

use KamilPietrzkiewicz\Software\Web\SeoTulip\Collections\SerpClientsCollection;

class SerpClientsModel 
{
	const NO_LIMIT = 0;

	public function createSerpClientsResource(SerpClientsResource $serpClients) : int
	{
		$sql = "INSERT INTO serp_clients(serp_client_id, user_id, title, description, added, deleted)";
		$sql .= " VALUES(:serp_client_id, :user_id, :title, :description, :added, :deleted)";

		$this->db->query($sql);
		$this->db->bind(':serp_client_id', $serpClients->getSerpClientId(), PDO::PARAM_INT);
		$this->db->bind(':user_id', $serpClients->getUserId(), PDO::PARAM_INT);
		$this->db->bind(':title', $serpClients->getTitle(), PDO::PARAM_STR);
		$this->db->bind(':description', $serpClients->getDescription(), PDO::PARAM_STR);
		$this->db->bind(':added', $serpClients->getAdded()->getTimestamp(), PDO::PARAM_STR);
		$this->db->bind(':deleted', $serpClients->getDeleted()->getTimestamp(), PDO::PARAM_STR);

		$this->exec();

		return $this->db->lastInsertId();	}

	public function readSerpClientsCollection(int $page, int $pageLimit) : SerpClientsCollection
	{
		$sql = "SELECT serp_client_id, user_id, title, description, added, deleted FROM serp_clients";

		$count = "SELECT count(*) as records_count FROM serp_clients";

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

		$serpClientsCollection = new SerpClientsCollection();

		if (empty($results)) {
			return $serpClientsCollection;
		}

		foreach ($results as $serpClientsRecord) {
			$serpClientsResource = $this->mapDbRowToSerpClientsResource($serpClientsRecord);

			$serpClientsCollection->append($serpClientsResource);
		}

		$serpClientsCollection->rewind();

		$serpClientsCollection->setTotal($countResult['records_count']);

		return $serpClientsCollection;
	}

	public function readSerpClientsById(int $serpClientId) : SerpClientsResource
	{
		$sql = "SELECT serp_client_id, user_id, title, description, added, deleted FROM serp_clients WHERE serp_client_id = :serp_client_id";

		$this->db->query($sql);

		$this->db->bind(':serp_client_id', $serpClientId, PDO::PARAM_INT);

		$result = $this->fetchOne();

		$serpClientsCollection = new SerpClientsCollection();

		if (empty($result)) {
			return $serpClientsCollection;
		}

		$serpClientsResource = $this->mapDbRowToSerpClientsResource($result);
		$serpClientsCollection->append($serpClientsResource);

		$serpClientsCollection->rewind();

		$serpClientsCollection->setTotal(1);

		return $serpClientsCollection;
	}

	private function mapDbRowToSerpClientsResource(array $dbRecord) : SerpClientsResource
	{
		$serpClientsResource = new SerpClientsResource();
		$serpClientsResource->setSerpClientId($serpClientsRecord['serp_client_id']);
		$serpClientsResource->setUserId($serpClientsRecord['user_id']);
		$serpClientsResource->setTitle($serpClientsRecord['title']);
		$serpClientsResource->setDescription($serpClientsRecord['description']);
		$serpClientsResource->setAdded(new \DateTime($serpClientsRecord['added']));
		$serpClientsResource->setDeleted(new \DateTime($serpClientsRecord['deleted']));
		return $serpClientsResource;
	}

	public function updateSerpClientsCollectionByCollection(SerpClientsCollection $serpClients)
	{
		$sqlTempTable = "CREATE OR REPLACE TEMPORARY TABLE data_update AS SELECT * FROM serp_clients WHERE 1=0";

		$this->db->query($sqlTempTable);

		$this->insertTempSerpClientsDataIntoTempDbTable($serpClients);

		$sql = "UPDATE serp_clients JOIN data_update ON serp_clients.serp_client_id = data_update.serp_client_id SET "
			."serp_clients.user_id = data_update.user_id"
			.", serp_clients.title = data_update.title"
			.", serp_clients.description = data_update.description"
			.", serp_clients.added = data_update.added"
			.", serp_clients.deleted = data_update.deleted";

		$this->db->query($sql);

		$this->db->exec();
	}

	private function insertTempSerpClientsDataIntoTempDbTable(SerpClientsCollection $serpClients)
	{
		$serpClients->rewind();

		$sql = "";

		$countResources = 0;

		$insertData = [];

		while ($serpClients->valid()) {
			$currentRecord = $serpClients->current();

			$countResources++;

			if ($countResources == 10) {
				$countResources = 0;

				$this->db->query($sql);

				$this->getBindParamsDeclarationForUpdate($insertData);

				$this->db->exec();

				$insertData = [];

				$sql = "";
			}

			$sql .= "INSERT INTO data_update(serp_client_id, user_id, title, description, added, deleted) VALUES(:serp_client_id_" . $countResources . ", :user_id_" . $countResources . ", :title_" . $countResources . ", :description_" . $countResources . ", :added_" . $countResources . ", :deleted_" . $countResources . "); ";

			$insertData[$countResources] = [
				'serp_client_id' => $currentRecord->getSerpClientId(),
				'user_id' => $currentRecord->getUserId(),
				'title' => $currentRecord->getTitle(),
				'description' => $currentRecord->getDescription(),
				'added' => $currentRecord->getAdded(),
				'deleted' => $currentRecord->getDeleted(),
			];

			$serpClients->next();

		}

		if ($sql != "") {
			$this->db->query($sql);

			$this->getBindParamsDeclarationForUpdate($insertData);

			$this->db->exec();
		}
	}

	private function getBindParamsDeclarationForUpdateSerpClientsCollection(array $serpClientsCollectionContainer)
	{
		foreach ($serpClientsCollectionContainer as $index => $values) {
			$this->db->bind(":serp_client_id_" . $index, $values['serp_client_id'], PDO::PARAM_INT);
			$this->db->bind(":user_id_" . $index, $values['user_id'], PDO::PARAM_INT);
			$this->db->bind(":title_" . $index, $values['title'], PDO::PARAM_STRING);
			$this->db->bind(":description_" . $index, $values['description'], PDO::PARAM_STRING);
			$this->db->bind(":added_" . $index, $values['added'], PDO::PARAM_STRING);
			$this->db->bind(":deleted_" . $index, $values['deleted'], PDO::PARAM_STRING);
		}
	}

	public function updateSerpClientsResourceByResource(SerpClientsResource $serpClients)
	{
		$sql = "UPDATE serp_clients SET "
			. "user_id = : user_id"
			. " ,title = : title"
			. " ,description = : description"
			. " ,added = : added"
			. " ,deleted = : deleted"
			" WHERE serp_clients.serp_client_id = :serp_client_id";

		$this->db->query($sql);

		$this->db->bind(':serp_client_id', $serpClients->getSerpClientId(), PDO::PARAM_INT);
		$this->db->bind(':user_id', $serpClients->getUserId(), PDO::PARAM_INT);
		$this->db->bind(':title', $serpClients->getTitle(), PDO::PARAM_STRING);
		$this->db->bind(':description', $serpClients->getDescription(), PDO::PARAM_STRING);
		$this->db->bind(':added', $serpClients->getAdded(), PDO::PARAM_STRING);
		$this->db->bind(':deleted', $serpClients->getDeleted(), PDO::PARAM_STRING);
		$this->db->exec();
	}

	public function deleteSerpClientsResourceById(SerpClientsResource $serpClients)
	{
		$sql = "delete serp_clients where serp_client_id = :serp_client_id";

		$this->db->query($sql);

		$this->db->bind(':serp_client_id', $serpClients->getSerpClientId());
		$this->db->exec();
	}
}
