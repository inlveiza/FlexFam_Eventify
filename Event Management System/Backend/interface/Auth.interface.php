<?php 

interface AuthInterface{
	public function login($data);
	public function register($data);
	public function logout();
}