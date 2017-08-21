<?php

require_once('DBapi.php');

use model\dbapi\DBapi;

class auth{


	private $dbName;
	private $dbuser;
	private $dbPass;

	function __construct(){
		session_start();
		require('./config.php');
		$this->dbName = $dbName;
		$this->dbUser = $dbUser;
		$this->dbPass = $dbPass;
	}

	function authUser($login, $password, $admin = false){
		$api = new DBapi('mysql', 'localhost', $this->dbName, $this->dbName, $this->dbPass);
		
		$user = $api->get('users', ['login'=>$login, 'password'=>md5($password)]);
		


		if (isset($user[0]['id'])){
			$_SESSION['id'] = $user[0]['id'];
			if ($admin == true){
				if ($user[0]['is_admin'] == true){
					$_SESSION['admin'] = true;
					return TRUE;
				}
				else{
					return FALSE;
				}
				
			}
			
			return TRUE;
		}
		else{
			
			return FALSE;
		}
	}




	function checkAuth(){
		if (!empty($_SESSION['id'])){
			return TRUE;
		}
		else{
			return FALSE;
		}
	}

	function checkTeacher(){
		if (!empty($_SESSION['teacher'])){
			return TRUE;
		}
		else{
			return FALSE;
		}
	}


	function checkAdmin(){
		if (!empty($_SESSION['admin'])){
			return TRUE;
		}
		else{
			return FALSE;
		}
	}
	function registration($login, $password, $passwordConfirm, $email, $fullName){
		if ((empty($login)) or (empty($password)) or (empty($passwordConfirm)) or (empty($email)) or (empty($fullName)) ){
			return 'Все поля обязательны к заполнению';
		}
		if ($password !== $passwordConfirm){
			return 'Проверьте правильность введенного пароля';
		}
		$api = new DBapi('mysql', 'localhost', $this->dbName, $this->dbName, $this->dbPass);
		if (!empty($api->get('users', ['login'=>$login]))){
			return 'Пользователь с таким логином уже существует!';
		}
		if (!empty($api->get('users', ['login'=>$email]))){
			return 'Пользователь с таким email уже существует!';
		}		
		$id = $api->insert('users', ['login'=>$login, 'name'=>$fullName, 'password' => md5($password), 'email'=>$email]);
		$_SESSION['id'] = $id;
		return TRUE;
	}
	function getUserInfo($id){
		$api = new DBapi('mysql', 'localhost', $this->dbName, $this->dbName, $this->dbPass);
		$user = $api->get('users', ['id' => $id]);
		return $user[0];
	}
	function loginTeacher($login, $password){

		$api = new DBapi('mysql', 'localhost', $this->dbName, $this->dbName, $this->dbPass);
		
		$user = $api->get('teachers', ['login'=>$login, 'password'=>md5($password)]);
		


		if (isset($user[0]['id'])){
			$_SESSION['id'] = $user[0]['id'];
			$_SESSION['teacher'] = '1';
			
		}
		else{
			
			return FALSE;
		}


	}
}