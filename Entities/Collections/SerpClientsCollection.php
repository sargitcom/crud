<?php

namespace KamilPietrzkiewicz\Software\Web\SeoTulip\Collections;

class SerpClientsCollection 
{
	public function current() : SerpClients
	{
		return $this->array[$this->position];
	}

	public function append(SerpClients $serpClients)
	{
		$this->array[$this->position++] = $databaseSchema;
	}

}
