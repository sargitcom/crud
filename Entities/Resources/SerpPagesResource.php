<?php

namespace KamilPietrzkiewicz\Software\Web\SeoTulip\Resources;

class SerpPagesResource 
{
	/**
	 * @var int
	 */
	private $serpPageId;


	/**
	 * @var int
	 */
	private $userId;


	/**
	 * @var int
	 */
	private $title;


	/**
	 * @var string
	 */
	private $description;


	/**
	 * @var int
	 */
	private $serpClientId;


	/**
	 * @var int
	 */
	private $serpDomainId;


	/**
	 * @var \DateTime
	 */
	private $deleted;

	public function setSerpPageId(int $serpPageId) 
	{
		$this->serpPageId = $serpPageId;
	}

	public function getSerpPageId() : int 
	{
		return $this->serpPageId;
	}

	public function setUserId(int $userId) 
	{
		$this->userId = $userId;
	}

	public function getUserId() : int 
	{
		return $this->userId;
	}

	public function setTitle(int $title) 
	{
		$this->title = $title;
	}

	public function getTitle() : int 
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

	public function setSerpClientId(int $serpClientId) 
	{
		$this->serpClientId = $serpClientId;
	}

	public function getSerpClientId() : int 
	{
		return $this->serpClientId;
	}

	public function setSerpDomainId(int $serpDomainId) 
	{
		$this->serpDomainId = $serpDomainId;
	}

	public function getSerpDomainId() : int 
	{
		return $this->serpDomainId;
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
