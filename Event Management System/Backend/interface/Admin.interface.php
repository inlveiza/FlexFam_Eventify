<?php

interface AdminInterface{
	public function AddEvent($data);
	public function UpdateEvent($id,$data);
	public function GrantAdmin($id);
}