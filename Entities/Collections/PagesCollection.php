<?php

namespace KamilPietrzkiewicz\Software\Web\SeoTulip\Collections;

class PagesCollection 
{
	public function current() : Pages
	{
		return $this->array[$this->position];
	}

	public function append(Pages $pages)
	{
		$this->array[$this->position++] = $databaseSchema;
	}

}
