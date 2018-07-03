<?php

namespace KamilPietrzkiewicz\Software\Web\SeoTulip\Collections;

class SearchEnginesDomainsCollection 
{
	public function current() : SearchEnginesDomains
	{
		return $this->array[$this->position];
	}

	public function append(SearchEnginesDomains $searchEnginesDomains)
	{
		$this->array[$this->position++] = $databaseSchema;
	}

}
