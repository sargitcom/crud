<?php

namespace KamilPietrzkiewicz\Software\Web\SeoTulip\Collections;

use KamilPietrzkiewicz\Sargit\Php\Database\Schema\Collection\Collection;

class PagesCollection extends Collection
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
