<?php

namespace KamilPietrzkiewicz\Software\Web\SeoTulip\Collections;

class UsersCollection 
{
	public function current() : Users
	{
		return $this->array[$this->position];
	}

	public function append(Users $users)
	{
		$this->array[$this->position++] = $databaseSchema;
	}

}
