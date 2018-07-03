<?php

namespace KamilPietrzkiewicz\Software\Web\SeoTulip\Resources;

class SearchEnignesVendorsResource 
{
	/**
	 * @var int
	 */
	private $searchEnginesVendorId;


	/**
	 * @var string
	 */
	private $vendorName;

	public function setSearchEnginesVendorId(int $searchEnginesVendorId) 
	{
		$this->searchEnginesVendorId = $searchEnginesVendorId;
	}

	public function getSearchEnginesVendorId() : int 
	{
		return $this->searchEnginesVendorId;
	}

	public function setVendorName(string $vendorName) 
	{
		$this->vendorName = $vendorName;
	}

	public function getVendorName() : string 
	{
		return $this->vendorName;
	}

}
