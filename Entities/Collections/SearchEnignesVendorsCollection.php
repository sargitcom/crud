<?php

namespace KamilPietrzkiewicz\Software\Web\SeoTulip\Collections;

class SearchEnignesVendorsCollection 
{
	public function current() : SearchEnignesVendors
	{
		return $this->array[$this->position];
	}

	public function append(SearchEnignesVendors $searchEnignesVendors)
	{
		$this->array[$this->position++] = $databaseSchema;
	}

}
