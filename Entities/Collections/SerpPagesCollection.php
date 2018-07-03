<?php

namespace KamilPietrzkiewicz\Software\Web\SeoTulip\Collections;

class SerpPagesCollection 
{
	public function current() : SerpPages
	{
		return $this->array[$this->position];
	}

	public function append(SerpPages $serpPages)
	{
		$this->array[$this->position++] = $databaseSchema;
	}

}
