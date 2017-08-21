<?php


require 'vendor/autoload.php';
require ('./view/templateClass.php');
require ('./model/authClass.php');
require ('./model/adminPanelClasses.php');
require ('./model/userPanelClasses.php');

require ('./model/teacherPanel.php');

require ('./config.php');

use Phroute\Phroute\RouteCollector;
use Phroute\Phroute\Dispatcher;

$collector = new RouteCollector();

$collector->get('/profile/', function(){
	

	$user = new auth();
	if ($user->checkAuth()){
		$userInfo = $user->getUserInfo($_SESSION['id']);

    	$template = new template('./view/lk.html');
    	$template->replace('img', '/imgs/' . $userInfo['avatar']);
   	 	$template->replace('name', $userInfo['name']);
   	 	$template->replace('id', $userInfo['id']);
   	 	$template->replace('balance', $userInfo['balance']);
   	 	$userPanel = new userPanel();
   	 	$template->replace('groups', $userPanel->getGroupsUser($_SESSION['id']));
   	 	$template->replace('news',$userPanel->getNews($_SESSION['id']));
   	 	
   	 	return $template->getTemplate();		
	}
	else{
		template::redirect( '/login');
	}

});



$collector->get('/profile/anketa', function(){
	

	$user = new auth();
	if ($user->checkAuth()){

    	$template = new template('./view/anketa.html');
   	 	return $template->getTemplate();		
	}
	else{
		template::redirect( '/login');
	}

});


$collector->post('/profile/anketa', function(){
	

	$user = new auth();
	if ($user->checkAuth()){
		$userPanel = new userPanel();
		$userPanel->saveAnkerta($_SESSION['id'], $_POST);
		

	}
	else{
		template::redirect( '/login');
	}

});








$collector->get('/admin', function(){
	$auth = new auth;
	if (!($auth->checkAdmin())){
		$template = new template('./view/admin.html');
    	return $template->getTemplate();
	}
	else{
		
		$historyPanel = new adminPanel();

    	return $historyPanel->getHistory();

	}

});




$collector->get('/admin/users/', function(){
	$auth = new auth;
	if (!($auth->checkAdmin())){
		$template = new template('./view/admin.html');
    	return $template->getTemplate();
	}
	else{
		
		$userPanel = new adminPanel();
		return $userPanel->getUsersAll();

	}

});
$collector->get('/admin/users/{id}', function($id){
	$auth = new auth;
	if (!($auth->checkAdmin())){
		$template = new template('./view/admin.html');
    	return $template->getTemplate();
	}
	else{

		
		$userPanel = new adminPanel();
    	return $userPanel->getUserInfo($id);
	}

});






$collector->post('/admin/users/{id}', function($id){
	$auth = new auth;
	if (!($auth->checkAdmin())){
		$template = new template('./view/admin.html');
    	return $template->getTemplate();
	}
	else{

		if ($_POST['action'] == 'save'){
			$userPanel = new adminPanel();
			$userPanel->saveUser($id, $_POST);
    		return $userPanel->getUserInfo($id);
		}
		elseif ($_POST['action'] == 'delete'){
			$userPanel = new adminPanel();
			$userPanel->deleteUser($id);
			$userPanel = new adminPanel();
			return $userPanel->getUsersAll();
		}
		elseif($_POST['action'] == 'deleteRecord'){

			$userPanel = new adminPanel();
			$userPanel->deleteRecord($_POST['id']);
			return $userPanel->getUserInfo($id);
		}
		elseif($_POST['action'] == 'addRecord'){
			$userPanel = new adminPanel();
			$userPanel->addRecord($id, $_POST['group_id']);
			return $userPanel->getUserInfo($id);
		}

	}

});




$collector->post('/admin', function(){
	$auth = new auth;
	if (!($auth->checkAdmin())){
		$status = $auth->authUser($_POST['lg_username'],$_POST['lg_password'], TRUE);
		$template = new template('./view/admin.html');
    	return $template->getTemplate();
	}
	else{
		$template = new template('./view/adminPanel.html');
    	return $template->getTemplate();
	}
});



$collector->get('/login', function(){
    $template = new template('./view/login.html');
    $template->replace('error', '');
    return $template->getTemplate();
});


$collector->get('/index.php', function(){
	$auth = new auth();
	if ($auth->checkAuth()){
		template::redirect( '/profile');
	}
	else{
		
		template::redirect('/login');
	}

});

$collector->post('/login', function(){
  	$auth = new auth();
  	$status = $auth->authUser($_POST['lg_username'],$_POST['lg_password']);
  	if ($status == FALSE){
  		$template = new template('./view/login.html');
  		$template->replace('error', 'Ошибка. Проверьте логин и пароль');
  		return $template->getTemplate();
  	}
  	else{
  		template::redirect( '/profile/');
  	}
});

$collector->get('/registration', function(){
  	$template = new template('./view/registration.html');
  	$template->replace('error', '');
    return $template->getTemplate();
});

$collector->post('/registration', function(){
	$auth = new auth();
	$status = $auth->registration($_POST['reg_username'],$_POST['reg_password'],$_POST['reg_password_confirm'],$_POST['reg_email'],$_POST['reg_fullname']);
	if ($status === TRUE){
		template::redirect( '/profile/');

	}
	else{
		$template = new template('./view/registration.html');
  		$template->replace('error', $status);
    	return $template->getTemplate();
	}

});







$collector->get('/profile/settings', function(){
	

	$user = new auth();
	if ($user->checkAuth()){

	
		$userPanel = new userPanel();
   	 	return $userPanel->getUserSettings($_SESSION['id']);	
	}
	else{
		template::redirect( '/login');
	}

});


$collector->post('/profile/settings', function(){
	

	$user = new auth();
	if ($user->checkAuth()){

		if (empty($_POST['oldPass'])){
			$userPanel = new userPanel();
   	 		return $userPanel->saveUserSettings($_SESSION['id'], $_POST, $_FILES);	
		}
		else{
			$userPanel = new userPanel();
   	 		return $userPanel->changePass($_SESSION['id'], $_POST);	
		}
		
	}
	else{
		template::redirect( '/login');
	}

});



$collector->get('/profile/feedback', function(){
	

	$user = new auth();
	if ($user->checkAuth()){

    	$template = new template('./view/feedback.html');
   	 	return $template->getTemplate();		
	}
	else{
		template::redirect( '/login');
	}

});








$collector->get('/paymentgate', function(){
	

	return 123;

});







$collector->get('/', function(){
	

	template::redirect( '/index.php');

});







$collector->get('/admin/entity', function(){
	$auth = new auth;
	if (!($auth->checkAdmin())){
		$status = $auth->authUser($_POST['lg_username'],$_POST['lg_password'], TRUE);
		$template = new template('./view/admin.html');
    	return $template->getTemplate();
	}
	else{

		$userPanel = new adminPanel();
		return $userPanel->getEntityList();

	}

});


$collector->get('/admin/editLesson/{id}', function($id){
	$auth = new auth;
	if (!($auth->checkAdmin())){
		$status = $auth->authUser($_POST['lg_username'],$_POST['lg_password'], TRUE);
		$template = new template('./view/admin.html');
    	return $template->getTemplate();
	}
	else{

		$userPanel = new adminPanel();
		return $userPanel->getEditLesson($id);

	}

});


$collector->post('/admin/editLesson/{id}', function($id){
	$auth = new auth;
	if (!($auth->checkAdmin())){
		$status = $auth->authUser($_POST['lg_username'],$_POST['lg_password'], TRUE);
		$template = new template('./view/admin.html');
    	return $template->getTemplate();
	}
	else{

		$userPanel = new adminPanel();
		$userPanel->saveData($id, $_POST);
		return $userPanel->getEditLesson($id);

	}

});





$collector->post('/admin/entity', function(){
	$auth = new auth;
	if (!($auth->checkAdmin())){
		$status = $auth->authUser($_POST['lg_username'],$_POST['lg_password'], TRUE);
		$template = new template('./view/admin.html');
    	return $template->getTemplate();
	}
	else{

		$userPanel = new adminPanel();
		$userPanel->saveEntityList($_POST);
		return $userPanel->getEntityList();

	}

});





$collector->get('/editGroup/{id}', function($id){
	$auth = new auth;
	if (!($auth->checkAdmin())){
		$status = $auth->authUser($_POST['lg_username'],$_POST['lg_password'], TRUE);
		$template = new template('./view/admin.html');
    	return $template->getTemplate();
	}
	else{

		$userPanel = new adminPanel();
		return $userPanel->getGroupPanel($id);

	}

});


$collector->get('/admin/phis/', function(){
	$auth = new auth;
	if (!($auth->checkAdmin())){
		$status = $auth->authUser($_POST['lg_username'],$_POST['lg_password'], TRUE);
		$template = new template('./view/admin.html');
    	return $template->getTemplate();
	}
	else{

		return 'Страница в разработке';

	}

});




$collector->get('/admin/teachers/', function(){
	$auth = new auth;
	if (!($auth->checkAdmin())){
		$status = $auth->authUser($_POST['lg_username'],$_POST['lg_password'], TRUE);
		$template = new template('./view/admin.html');
    	return $template->getTemplate();
	}
	else{

		$userPanel = new adminPanel();
		return $userPanel->getTeachers();

	}

});


$collector->get('/admin/teachers/{id}', function($id){
	$auth = new auth;
	if (!($auth->checkAdmin())){
		$status = $auth->authUser($_POST['lg_username'],$_POST['lg_password'], TRUE);
		$template = new template('./view/admin.html');
    	return $template->getTemplate();
	}
	else{

		$userPanel = new adminPanel();
		return $userPanel->getTeachers();

	}

});




$collector->post('/admin/teachers/', function(){
	$auth = new auth;
	if (!($auth->checkAdmin())){
		$status = $auth->authUser($_POST['lg_username'],$_POST['lg_password'], TRUE);
		$template = new template('./view/admin.html');
    	return $template->getTemplate();
	}
	else{


		$userPanel = new adminPanel();
		$userPanel->addTeacher($_POST);
		return $userPanel->getTeachers();

	}

});



$collector->post('/editGroup/{id}', function($id){
	$auth = new auth;
	if (!($auth->checkAdmin())){
		$status = $auth->authUser($_POST['lg_username'],$_POST['lg_password'], TRUE);
		$template = new template('./view/admin.html');
    	return $template->getTemplate();
	}
	else{

		$userPanel = new adminPanel();
		if ($_POST['action'] == 'saveGroup'){
			$userPanel->saveGroup($id, $_POST);
		}
		if ($_POST['action'] == 'addLesson'){
			$userPanel->addLesson($id, $_POST);
		}
		
		return $userPanel->getGroupPanel($id);

	}

});

$collector->get('/teacher/', function(){
	

	$user = new auth();
	if ($user->checkAuth()){
		$userInfo = $user->getUserInfo($_SESSION['id']);

    	$template = new template('./view/lk.html');
    	$template->replace('img', '/imgs/' . $userInfo['avatar']);
   	 	$template->replace('name', $userInfo['name']);
   	 	$template->replace('id', $userInfo['id']);
   	 	$template->replace('balance', $userInfo['balance']);
   	 	$userPanel = new userPanel();
   	 	$template->replace('groups', $userPanel->getGroupsUser($_SESSION['id']));
   	 	$template->replace('news',$userPanel->getNews($_SESSION['id']));
   	 	
   	 	return $template->getTemplate();		
	}
	else{
		template::redirect( '/login');
	}

});

$collector->get('/tlogin', function(){

    $template = new template('./view/tlogin.html');
    $template->replace('error', '');
    return $template->getTemplate();
});

$collector->post('/tlogin', function(){
	$user = new auth();
	$user->loginTeacher($_POST['lg_username'], $_POST['lg_password']);
	if (!($user->checkTeacher())){
		 $template = new template('./view/tlogin.html');
    	$template->replace('error', '');
    	return $template->getTemplate();
	}
	else{
		template::redirect( '/tpanel');
	}

});


$collector->get('/tpanel', function(){
	$user = new auth();
	$user->loginTeacher($_POST['lg_username'], $_POST['lg_password']);
	if (!($user->checkTeacher())){
		 $template = new template('./view/tlogin.html');
    	$template->replace('error', '');
    	return $template->getTemplate();
	}
	else{
		$tPanel = new teacherPanel();
		return $tPanel->getTpanel($_SESSION['id']);
		
		
	}

});


$collector->post('/tpanel', function(){
	$user = new auth();
	$user->loginTeacher($_POST['lg_username'], $_POST['lg_password']);
	if (!($user->checkTeacher())){
		 $template = new template('./view/tlogin.html');
    	$template->replace('error', 'Ошибка. Проверьте логин или пароль');
    	return $template->getTemplate();
	}
	else{


		$tPanel = new teacherPanel();
		$tPanel->saveData($_POST);
		return $tPanel->getTpanel($_SESSION['id']);
		
		
	}

});


$collector->get('/tpanel/settings', function(){
	

	$user = new auth();
	if ($user->checkAuth()){

	
		$userPanel = new teacherPanel();
   	 	return $userPanel->getUserSettings($_SESSION['id']);	
	}
	else{
		template::redirect( '/login');
	}

});


$collector->post('/tpanel/settings', function(){
	

	$user = new auth();
	if ($user->checkAuth()){

		if (empty($_POST['oldPass'])){
			$userPanel = new teacherPanel();
   	 		return $userPanel->saveUserSettings($_SESSION['id'], $_POST, $_FILES);	
		}
		else{
			$userPanel = new teacherPanel();
   	 		return $userPanel->changePass($_SESSION['id'], $_POST);	
		}
		
	}
	else{
		template::redirect( '/login');
	}

});

$collector->get('/tpanel/list/{id}', function($id){
	

	$user = new auth();
	if ($user->checkAuth()){

		
		
			$userPanel = new teacherPanel();
			return $userPanel->getList($id);
	
		
	}
	else{
		template::redirect( '/login');
	}

});

$collector->post('/tpanel/list/{id}', function($id){
	

	$user = new auth();
	if ($user->checkAuth()){

		
		
			$userPanel = new teacherPanel();
			return $userPanel->getList($id, $_POST);
	
		
	}
	else{
		template::redirect( '/login');
	}

});


$dispatcher =  new Dispatcher($collector->getData());
echo $dispatcher->dispatch($_SERVER['REQUEST_METHOD'], parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), "\n");   