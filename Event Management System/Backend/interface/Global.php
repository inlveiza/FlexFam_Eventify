<?php

interface GlobalInterface{
	public function responsePayload($payload,$remarks,$message,$code);
	public function notFound();
	
}
