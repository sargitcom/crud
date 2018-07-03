<?php

namespace KamilPietrzkiewicz\Software\Web\SeoTulip\Models;

use KamilPietrzkiewicz\Software\Web\SeoTulip\Resources\UsersResource;

use KamilPietrzkiewicz\Software\Web\SeoTulip\Collections\UsersCollection;

class UsersModel 
{
	const NO_LIMIT = 0;

	public function createUsersResource(UsersResource $users) : int
	{
		$sql = "INSERT INTO users(user_id, password, name, surname, email, added, deleted, registered, banned, ban_lasts, default_search_engine_domain_id)";
		$sql .= " VALUES(:user_id, :password, :name, :surname, :email, :added, :deleted, :registered, :banned, :ban_lasts, :default_search_engine_domain_id)";

		$this->db->query($sql);
		$this->db->bind(':user_id', $users->getUserId(), PDO::PARAM_INT);
		$this->db->bind(':password', $users->getPassword(), PDO::PARAM_STR);
		$this->db->bind(':name', $users->getName(), PDO::PARAM_STR);
		$this->db->bind(':surname', $users->getSurname(), PDO::PARAM_STR);
		$this->db->bind(':email', $users->getEmail(), PDO::PARAM_STR);
		$this->db->bind(':added', $users->getAdded()->getTimestamp(), PDO::PARAM_STR);
		$this->db->bind(':deleted', $users->getDeleted()->getTimestamp(), PDO::PARAM_STR);
		$this->db->bind(':registered', $users->getRegistered()->getTimestamp(), PDO::PARAM_STR);
		$this->db->bind(':banned', $users->getBanned()->getTimestamp(), PDO::PARAM_STR);
		$this->db->bind(':ban_lasts', $users->getBanLasts()->getTimestamp(), PDO::PARAM_STR);
		$this->db->bind(':default_search_engine_domain_id', $users->getDefaultSearchEngineDomainId(), PDO::PARAM_INT);

		$this->exec();

		return $this->db->lastInsertId();	}

	public function readUsersCollection(int $page, int $pageLimit) : UsersCollection
	{
		$sql = "SELECT user_id, password, name, surname, email, added, deleted, registered, banned, ban_lasts, default_search_engine_domain_id FROM users";

		$count = "SELECT count(*) as records_count FROM users";

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

		$usersCollection = new UsersCollection();

		if (empty($results)) {
			return $usersCollection;
		}

		foreach ($results as $usersRecord) {
			$usersResource = $this->mapDbRowToUsersResource($usersRecord);

			$usersCollection->append($usersResource);
		}

		$usersCollection->rewind();

		$usersCollection->setTotal($countResult['records_count']);

		return $usersCollection;
	}

	public function readUsersById(int $userId) : UsersResource
	{
		$sql = "SELECT user_id, password, name, surname, email, added, deleted, registered, banned, ban_lasts, default_search_engine_domain_id FROM users WHERE user_id = :user_id";

		$this->db->query($sql);

		$this->db->bind(':user_id', $userId, PDO::PARAM_INT);

		$result = $this->fetchOne();

		$usersCollection = new UsersCollection();

		if (empty($result)) {
			return $usersCollection;
		}

		$usersResource = $this->mapDbRowToUsersResource($result);
		$usersCollection->append($usersResource);

		$usersCollection->rewind();

		$usersCollection->setTotal(1);

		return $usersCollection;
	}

	private function mapDbRowToUsersResource(array $dbRecord) : UsersResource
	{
		$usersResource = new UsersResource();
		$usersResource->setUserId($usersRecord['user_id']);
		$usersResource->setPassword($usersRecord['password']);
		$usersResource->setName($usersRecord['name']);
		$usersResource->setSurname($usersRecord['surname']);
		$usersResource->setEmail($usersRecord['email']);
		$usersResource->setAdded(new \DateTime($usersRecord['added']));
		$usersResource->setDeleted(new \DateTime($usersRecord['deleted']));
		$usersResource->setRegistered(new \DateTime($usersRecord['registered']));
		$usersResource->setBanned(new \DateTime($usersRecord['banned']));
		$usersResource->setBanLasts(new \DateTime($usersRecord['ban_lasts']));
		$usersResource->setDefaultSearchEngineDomainId($usersRecord['default_search_engine_domain_id']);
		return $usersResource;
	}

	public function updateUsersCollectionByCollection(UsersCollection $users)
	{
		$sqlTempTable = "CREATE OR REPLACE TEMPORARY TABLE data_update AS SELECT * FROM users WHERE 1=0";

		$this->db->query($sqlTempTable);

		$this->insertTempUsersDataIntoTempDbTable($users);

		$sql = "UPDATE users JOIN data_update ON users.user_id = data_update.user_id SET "
			."users.password = data_update.password"
			.", users.name = data_update.name"
			.", users.surname = data_update.surname"
			.", users.email = data_update.email"
			.", users.added = data_update.added"
			.", users.deleted = data_update.deleted"
			.", users.registered = data_update.registered"
			.", users.banned = data_update.banned"
			.", users.ban_lasts = data_update.ban_lasts"
			.", users.default_search_engine_domain_id = data_update.default_search_engine_domain_id";

		$this->db->query($sql);

		$this->db->exec();
	}

	private function insertTempUsersDataIntoTempDbTable(UsersCollection $users)
	{
		$users->rewind();

		$sql = "";

		$countResources = 0;

		$insertData = [];

		while ($users->valid()) {
			$currentRecord = $users->current();

			$countResources++;

			if ($countResources == 10) {
				$countResources = 0;

				$this->db->query($sql);

				$this->getBindParamsDeclarationForUpdate($insertData);

				$this->db->exec();

				$insertData = [];

				$sql = "";
			}

			$sql .= "INSERT INTO data_update(user_id, password, name, surname, email, added, deleted, registered, banned, ban_lasts, default_search_engine_domain_id) VALUES(:user_id_" . $countResources . ", :password_" . $countResources . ", :name_" . $countResources . ", :surname_" . $countResources . ", :email_" . $countResources . ", :added_" . $countResources . ", :deleted_" . $countResources . ", :registered_" . $countResources . ", :banned_" . $countResources . ", :ban_lasts_" . $countResources . ", :default_search_engine_domain_id_" . $countResources . "); ";

			$insertData[$countResources] = [
				'user_id' => $currentRecord->getUserId(),
				'password' => $currentRecord->getPassword(),
				'name' => $currentRecord->getName(),
				'surname' => $currentRecord->getSurname(),
				'email' => $currentRecord->getEmail(),
				'added' => $currentRecord->getAdded(),
				'deleted' => $currentRecord->getDeleted(),
				'registered' => $currentRecord->getRegistered(),
				'banned' => $currentRecord->getBanned(),
				'ban_lasts' => $currentRecord->getBanLasts(),
				'default_search_engine_domain_id' => $currentRecord->getDefaultSearchEngineDomainId(),
			];

			$users->next();

		}

		if ($sql != "") {
			$this->db->query($sql);

			$this->getBindParamsDeclarationForUpdate($insertData);

			$this->db->exec();
		}
	}

	private function getBindParamsDeclarationForUpdateUsersCollection(array $usersCollectionContainer)
	{
		foreach ($usersCollectionContainer as $index => $values) {
			$this->db->bind(":user_id_" . $index, $values['user_id'], PDO::PARAM_INT);
			$this->db->bind(":password_" . $index, $values['password'], PDO::PARAM_STRING);
			$this->db->bind(":name_" . $index, $values['name'], PDO::PARAM_STRING);
			$this->db->bind(":surname_" . $index, $values['surname'], PDO::PARAM_STRING);
			$this->db->bind(":email_" . $index, $values['email'], PDO::PARAM_STRING);
			$this->db->bind(":added_" . $index, $values['added'], PDO::PARAM_STRING);
			$this->db->bind(":deleted_" . $index, $values['deleted'], PDO::PARAM_STRING);
			$this->db->bind(":registered_" . $index, $values['registered'], PDO::PARAM_STRING);
			$this->db->bind(":banned_" . $index, $values['banned'], PDO::PARAM_STRING);
			$this->db->bind(":ban_lasts_" . $index, $values['ban_lasts'], PDO::PARAM_STRING);
			$this->db->bind(":default_search_engine_domain_id_" . $index, $values['default_search_engine_domain_id'], PDO::PARAM_INT);
		}
	}

	public function updateUsersResourceByResource(UsersResource $users)
	{
		$sql = "UPDATE users SET "
			. "password = : password"
			. " ,name = : name"
			. " ,surname = : surname"
			. " ,email = : email"
			. " ,added = : added"
			. " ,deleted = : deleted"
			. " ,registered = : registered"
			. " ,banned = : banned"
			. " ,ban_lasts = : ban_lasts"
			. " ,default_search_engine_domain_id = : default_search_engine_domain_id"
			" WHERE users.user_id = :user_id";

		$this->db->query($sql);

		$this->db->bind(':user_id', $users->getUserId(), PDO::PARAM_INT);
		$this->db->bind(':password', $users->getPassword(), PDO::PARAM_STRING);
		$this->db->bind(':name', $users->getName(), PDO::PARAM_STRING);
		$this->db->bind(':surname', $users->getSurname(), PDO::PARAM_STRING);
		$this->db->bind(':email', $users->getEmail(), PDO::PARAM_STRING);
		$this->db->bind(':added', $users->getAdded(), PDO::PARAM_STRING);
		$this->db->bind(':deleted', $users->getDeleted(), PDO::PARAM_STRING);
		$this->db->bind(':registered', $users->getRegistered(), PDO::PARAM_STRING);
		$this->db->bind(':banned', $users->getBanned(), PDO::PARAM_STRING);
		$this->db->bind(':ban_lasts', $users->getBanLasts(), PDO::PARAM_STRING);
		$this->db->bind(':default_search_engine_domain_id', $users->getDefaultSearchEngineDomainId(), PDO::PARAM_INT);
		$this->db->exec();
	}

	public function deleteUsersResourceById(UsersResource $users)
	{
		$sql = "delete users where user_id = :user_id";

		$this->db->query($sql);

		$this->db->bind(':user_id', $users->getUserId());
		$this->db->exec();
	}
}
