<?php

namespace KamilPietrzkiewicz\Software\Web\SeoTulip\Resources;

class PagesResource 
{
	/**
	 * @var int
	 */
	private $pageId;


	/**
	 * @var int
	 */
	private $userId;


	/**
	 * @var string
	 */
	private $domain;


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

	public function setPageId(int $pageId) 
	{
		$this->pageId = $pageId;
	}

	public function getPageId() : int 
	{
		return $this->pageId;
	}

	public function setUserId(int $userId) 
	{
		$this->userId = $userId;
	}

	public function getUserId() : int 
	{
		return $this->userId;
	}

	public function setDomain(string $domain) 
	{
		$this->domain = $domain;
	}

	public function getDomain() : string 
	{
		return $this->domain;
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
