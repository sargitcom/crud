<?php

namespace KamilPietrzkiewicz\Software\Web\SeoTulip\Resources;

class UsersResource 
{
	/**
	 * @var int
	 */
	private $userId;


	/**
	 * @var string
	 */
	private $password;


	/**
	 * @var string
	 */
	private $name;


	/**
	 * @var string
	 */
	private $surname;


	/**
	 * @var string
	 */
	private $email;


	/**
	 * @var \DateTime
	 */
	private $added;


	/**
	 * @var \DateTime
	 */
	private $deleted;


	/**
	 * @var \DateTime
	 */
	private $registered;


	/**
	 * @var \DateTime
	 */
	private $banned;


	/**
	 * @var \DateTime
	 */
	private $banLasts;


	/**
	 * @var int
	 */
	private $defaultSearchEngineDomainId;

	public function setUserId(int $userId) 
	{
		$this->userId = $userId;
	}

	public function getUserId() : int 
	{
		return $this->userId;
	}

	public function setPassword(string $password) 
	{
		$this->password = $password;
	}

	public function getPassword() : string 
	{
		return $this->password;
	}

	public function setName(string $name) 
	{
		$this->name = $name;
	}

	public function getName() : string 
	{
		return $this->name;
	}

	public function setSurname(string $surname) 
	{
		$this->surname = $surname;
	}

	public function getSurname() : string 
	{
		return $this->surname;
	}

	public function setEmail(string $email) 
	{
		$this->email = $email;
	}

	public function getEmail() : string 
	{
		return $this->email;
	}

	public function setAdded(\DateTime $added) 
	{
		$this->added = $added;
	}

	public function getAdded() : \DateTime 
	{
		return $this->added;
	}

	public function setDeleted(\DateTime $deleted) 
	{
		$this->deleted = $deleted;
	}

	public function getDeleted() : \DateTime 
	{
		return $this->deleted;
	}

	public function setRegistered(\DateTime $registered) 
	{
		$this->registered = $registered;
	}

	public function getRegistered() : \DateTime 
	{
		return $this->registered;
	}

	public function setBanned(\DateTime $banned) 
	{
		$this->banned = $banned;
	}

	public function getBanned() : \DateTime 
	{
		return $this->banned;
	}

	public function setBanLasts(\DateTime $banLasts) 
	{
		$this->banLasts = $banLasts;
	}

	public function getBanLasts() : \DateTime 
	{
		return $this->banLasts;
	}

	public function setDefaultSearchEngineDomainId(int $defaultSearchEngineDomainId) 
	{
		$this->defaultSearchEngineDomainId = $defaultSearchEngineDomainId;
	}

	public function getDefaultSearchEngineDomainId() : int 
	{
		return $this->defaultSearchEngineDomainId;
	}

}
