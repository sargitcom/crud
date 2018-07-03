<?php

namespace KamilPietrzkiewicz\Software\Web\SeoTulip\Resources;

class SearchEnginesDomainsResource 
{
	/**
	 * @var int
	 */
	private $searchEnginesDomainId;


	/**
	 * @var string
	 */
	private $description;


	/**
	 * @var string
	 */
	private $domain;


	/**
	 * @var int
	 */
	private $searchEnginesVendorId;

	public function setSearchEnginesDomainId(int $searchEnginesDomainId) 
	{
		$this->searchEnginesDomainId = $searchEnginesDomainId;
	}

	public function getSearchEnginesDomainId() : int 
	{
		return $this->searchEnginesDomainId;
	}

	public function setDescription(string $description) 
	{
		$this->description = $description;
	}

	public function getDescription() : string 
	{
		return $this->description;
	}

	public function setDomain(string $domain) 
	{
		$this->domain = $domain;
	}

	public function getDomain() : string 
	{
		return $this->domain;
	}

	public function setSearchEnginesVendorId(int $searchEnginesVendorId) 
	{
		$this->searchEnginesVendorId = $searchEnginesVendorId;
	}

	public function getSearchEnginesVendorId() : int 
	{
		return $this->searchEnginesVendorId;
	}

}
