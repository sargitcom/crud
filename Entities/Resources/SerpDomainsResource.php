<?php

namespace KamilPietrzkiewicz\Software\Web\SeoTulip\Resources;

class SerpDomainsResource 
{
	/**
	 * @var int
	 */
	private $serpDomainId;


	/**
	 * @var int
	 */
	private $userId;


	/**
	 * @var string
	 */
	private $title;


	/**
	 * @var string
	 */
	private $description;


	/**
	 * @var \DateTime
	 */
	private $added;


	/**
	 * @var \DateTime
	 */
	private $deleted;

	public function setSerpDomainId(int $serpDomainId) 
	{
		$this->serpDomainId = $serpDomainId;
	}

	public function getSerpDomainId() : int 
	{
		return $this->serpDomainId;
	}

	public function setUserId(int $userId) 
	{
		$this->userId = $userId;
	}

	public function getUserId() : int 
	{
		return $this->userId;
	}

	public function setTitle(string $title) 
	{
		$this->title = $title;
	}

	public function getTitle() : string 
	{
		return $this->title;
	}

	public function setDescription(string $description) 
	{
		$this->description = $description;
	}

	public function getDescription() : string 
	{
		return $this->description;
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

}
