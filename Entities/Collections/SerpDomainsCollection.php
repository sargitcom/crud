<?php

namespace KamilPietrzkiewicz\Software\Web\SeoTulip\Collections;

class SerpDomainsCollection 
{
	public function current() : SerpDomains
	{
		return $this->array[$this->position];
	}

	public function append(SerpDomains $serpDomains)
	{
		$this->array[$this->position++] = $databaseSchema;
	}

}
