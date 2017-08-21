<?php

require_once('DBapi.php');
require_once('./view/templateClass.php');
use model\dbapi\DBapi;

class adminPanel{


	private $dbName;
	private $dbuser;
	private $dbPass;
		function __construct(){
		require('./config.php');
		$this->dbName = $dbName;
		$this->dbUser = $dbUser;
		$this->dbPass = $dbPass;
	}
	public function getHistory(){
		$template = new template('./view/adminPanel.html');
		$historyTemplate = new template('./view/adminPanelHistory.html');
		$db = new DBapi('mysql', 'localhost', $this->dbName, $this->dbName, $this->dbPass);
		
		$historyArr = $db->getHIstory();
		$resultHistory = '';
		foreach ($historyArr as $his){
	
			$historyTemplate->replace('name', $his['name']);
			$historyTemplate->replace('date', date('d.m.Y H:i:s',$his['date']));
			$historyTemplate->replace('summ', $his['amount']);
			$resultHistory .= $historyTemplate->getTemplate(true);
		}
	
		$template->replace('payment_history', $resultHistory);
		return $template->getTemplate();
	}


	public function getUsersAll(){
		$template = new template('./view/adminPanelUsers.html');
		$usersTemplate = new template('./view/adminPanelUsersList.html');
		$db = new DBapi('mysql', 'localhost', $this->dbName, $this->dbName, $this->dbPass);
		$users = $db->get('users', ['is_admin'=>0]);
		$resultUsersList = '';
		foreach ($users as $user){
	
			$usersTemplate->replace('name', $user['name']);
			$usersTemplate->replace('email', $user['email']);
			$usersTemplate->replace('summ', $user['balance']);
			$usersTemplate->replace('href', $user['id']);
			$resultUsersList .= $usersTemplate->getTemplate(true);
		}
		$template->replace('users_list', $resultUsersList);
		return $template->getTemplate();
	}

	public function getTeachers(){
		$template = new template('./view/adminPanelTeachers.html');
		$usersTemplate = new template('./view/adminPanelTeachersList.html');
		$db = new DBapi('mysql', 'localhost', $this->dbName, $this->dbName, $this->dbPass);
		$users = $db->get('teachers', []);
		$resultUsersList = '';
		foreach ($users as $user){
	
			$usersTemplate->replace('name', $user['name']);
			$usersTemplate->replace('email', $user['email']);
			$usersTemplate->replace('summ', $user['balance']);
			$usersTemplate->replace('href', $user['id']);
			$resultUsersList .= $usersTemplate->getTemplate(true);
		}
		$template->replace('users_list', $resultUsersList);
		return $template->getTemplate();
	}



	public function getUserInfo($id){
		$db = new DBapi('mysql', 'localhost', $this->dbName, $this->dbName, $this->dbPass);
		$user = $db->get('users', ['id'=>$id]);

		$template = new template('./view/adminPanelUserEdit.html');
		$template->replace('name', $user[0]['name']);
		$template->replace('email', $user[0]['email']);
		$template->replace('login', $user[0]['login']);
		$template->replace('balance', $user[0]['balance']);
		$records = $db->makeQuery('SELECT record_users.id, groups.group_name FROM record_users LEFT JOIN groups ON record_users.group_id = groups.id WHERE record_users.user_id = ' . $id);
		$data = '';
		foreach ($records as $record){
			$recTempl = new template('./view/recordOne.html');
			$recTempl->replace('id', $record['id']);
			$recTempl->replace('group_name', $record['group_name']);
			$data .= $recTempl->getTemplate(true);
		}
		$records = $db->makeQuery('SELECT record_users.id, groups.group_name as fname FROM record_users LEFT JOIN groups ON record_users.group_id = groups.id WHERE record_users.user_id = ' . $id);



		$template->replace('groups_list', $data);

		$selectData = $this->getEntitySelect('groups','group_name');

		$template->replace('select_groups',$template->getOptionList('', 'group_id', $selectData));


		return $template->getTemplate();
	}

	public function saveUser($id, $data){
		$db = new DBapi('mysql', 'localhost', $this->dbName, $this->dbName, $this->dbPass);
		$db->update('users', ['id'=>$id, 'name'=>$data['name'], 'email'=>$data['email'], 'login'=>$data['login'], 'balance'=>$data['balance']]);
	}
	public function deleteUser($id){
		$db = new DBapi('mysql', 'localhost', $this->dbName, $this->dbName, $this->dbPass);
		$db->delete('users', $id);
	}
	public function getEntityList(){
		$template = new template('./view/adminPanelEntity.html');
		$db = new DBapi('mysql', 'localhost', $this->dbName, $this->dbName, $this->dbPass);
		$res = $db->makeQuery('SELECT group_type.id, abonement.name, group_type.group_type_name FROM group_type LEFT JOIN abonement ON abonement.id = group_type.abonement_id');
		$template->insertTable('group_type_list', $res);
		$res = $db->makeQuery('SELECT groups.id, teachers.name as tname, groups.group_name, group_type.group_type_name, groups.price_once, filials.name FROM groups LEFT JOIN teachers ON teachers.id = groups.teacher_id LEFT JOIN group_type ON group_type.id = groups.group_type_id LEFT JOIN filials ON filials.id = groups.filial_id');

		$template->insertTable('groups_list', $res, true);
		
		$res = $db->makeQuery('SELECT * FROM abonement');

		$template->insertTable('abonement_list', $res);

		$res = $db->makeQuery('SELECT * FROM filials');
		$template->insertTable('filial_list', $res);

		$res = $db->makeQuery('SELECT hall.id, hall.name as hname, filials.name FROM hall LEFT JOIN filials ON hall.filial_id = filials.id');
		$template->insertTable('zal_list', $res);
		$selectData = $this->getEntitySelect('abonement','name');

		$template->replace('abonement_select',$template->getOptionList('Абонементы', 'abonement_id', $selectData));


		$selectData = $this->getEntitySelect('group_type','group_type_name');
		$template->replace('group_type_select',$template->getOptionList('Тип группы', 'group_type_id', $selectData));

		$selectData = $this->getEntitySelect('teachers','name');
		$template->replace('teacher_select',$template->getOptionList('Преподаватель', 'teacher_id', $selectData));

		$selectFilialData = $this->getEntitySelect('filials','name');
		$template->replace('filial_select',$template->getOptionList('Филиал', 'filial_id', $selectFilialData));
		$template->replace('filial_hall_select',$template->getOptionList('Филиал', 'filial_id', $selectFilialData));



		return $template->getTemplate();




	}
	public function getEntitySelect($entity, $name){
		$db = new DBapi('mysql', 'localhost', $this->dbName, $this->dbName, $this->dbPass);
		
		$res = $db->makeQuery('SELECT id, ' . $name . ' as fname FROM ' . $entity);

		return $res;
	}

	public function saveEntityList($post){
		$db = new DBapi('mysql', 'localhost', $this->dbName, $this->dbName, $this->dbPass);
		$id = $db->insert($post['entity_name'], []);
		foreach ($post as $key=>$field){
			$db->update($post['entity_name'], ['id'=>$id, $key=>$field]);
		}
	}
	public function getGroupPanel($id){
		$db = new DBapi('mysql', 'localhost', $this->dbName, $this->dbName, $this->dbPass);

		$res = end($db->get('groups', ['id'=>$id]));
		$template = new template('./view/editGroupLessons.html');
		
		$template->replace('id', $res['id']);
		$template->replace('group_name', $res['group_name']);
		$template->replace('price_once', $res['price_once']);
		$template->replace('message', $res['message']);

		$selectData = $this->getEntitySelect('teachers','name');
		$template->replace('teacher',$template->getOptionList('', 'teacher_id', $selectData, $res['teacher_id']));


		$selectData = $this->getEntitySelect('group_type','group_type_name');
		$template->replace('group_select',$template->getOptionList('', 'group_type_id', $selectData, $res['group_type_id']));


		$selectFilialData = $this->getEntitySelect('filials','name');
		$template->replace('filial_select',$template->getOptionList('', 'filial_id', $selectFilialData, $res['filial_id']));

		$usersGroup = $db->makeQuery('SELECT users.login FROM record_users LEFT JOIN users ON record_users.user_id = users.id WHERE record_users.group_id =  ' . $id);
		foreach ($usersGroup as $gr){
			$dt[] = $gr['login'];
		}

		$hallList = $db->makeQuery('SELECT hall.id, hall.name as fname FROM groups LEFT JOIN filials ON groups.filial_id = filials.id LEFT JOIN hall ON hall.filial_id = filials.id WHERE groups.id = ' . $id);
		$template->replace('select_hall',$template->getOptionList('Зал', 'hall_id', $hallList));


		$template->replace('users_list', implode(', ', $dt));
		$lesList = $db->makeQuery('SELECT lessons.id, lessons.time_start, lessons.time_end, hall.name FROM lessons LEFT JOIN hall ON hall.id = lessons.hall_id WHERE group_id=' . $id);
		
		uasort($lesList, function ($a, $b) { 
							if ($a['time_start'] == $b['time_start']){
								return 0;
							}
							 return ($a['time_start'] < $b['time_start']) ? -1 : 1;

						});
		
		$template->replace('lessons_list', $template->getLessonsList($lesList));
		return $template->getTemplate();

	}

	public function saveGroup($id, $post){
		$db = new DBapi('mysql', 'localhost', $this->dbName, $this->dbName, $this->dbPass);
		foreach ($post as $key=>$field){
			$db->update($post['entity_type'], ['id'=>$id, $key=>$field]);
		}
	}

	public function deleteRecord($id){
		$db = new DBapi('mysql', 'localhost', $this->dbName, $this->dbName, $this->dbPass);
		$db->delete('record_users', $id);
	}
	public function addRecord($user_id, $group_id){
		$db = new DBapi('mysql', 'localhost', $this->dbName, $this->dbName, $this->dbPass);
		$db->insert('record_users', ['user_id'=>$user_id, 'group_id'=>$group_id]);

	}

	public function addLesson($idGroup, $data){
		$db = new DBapi('mysql', 'localhost', $this->dbName, $this->dbName, $this->dbPass);
		$d = new DateTime($data['time_start']);
		$e = new DateTime($data['time_end']);

		$db->makeQUery("INSERT INTO `lessons` (`id`, `group_id`, `time_start`, `time_end`, `hall_id`) VALUES (NULL, '" . $idGroup . "', '" . $d->format('U') . "', '" . $e->format('U') . "', '" . $data['hall_id'] . "');");

	}

	public function getEditLesson($id){
		$db = new DBapi('mysql', 'localhost', $this->dbName, $this->dbName, $this->dbPass);
		$template = new template('./view/editLesson.html');
		$data = end($db->get('lessons',['id'=>$id]));
		$hallList = $db->makeQuery('SELECT hall.id, hall.name as fname FROM filials LEFT JOIN hall ON filials.id = hall.filial_id LEFT JOIN groups ON groups.filial_id = filials.id WHERE groups.id = ' . $data['group_id']);

		$template->replace('select_hall',$template->getOptionList('Зал', 'hall_id', $hallList));
		$template->replace('start',date('d-m-Y H:i', $data['time_start']));
		$template->replace('end',date('d-m-Y H:i', $data['time_end']));
		$template->replace('lesson_id', $data['id']);
		return $template->getTemplate();


	}


	public function saveData($id, $data){
		$db = new DBapi('mysql', 'localhost', $this->dbName, $this->dbName, $this->dbPass);
		$d = new DateTime($data['time_start']);
		$e = new DateTime($data['time_end']);
		echo 'UPDATE lessons SET time_start = ' . $d->format('U') . ', time_end = '  . $e->format('U') . ', hall_id = ' . $data['hall_id'] . ' WHERE id = ' . $id;
		$db->makeQuery('UPDATE lessons SET time_start = ' . $d->format('U') . ', time_end = '  . $e->format('U'). ' WHERE id = ' . $id);

	}
		function addTeacher($data){
		$db = new DBapi('mysql', 'localhost', $this->dbName, $this->dbName, $this->dbPass);
		$id = $db->insert('teachers', ['balance'=>0]);
		foreach ($data as $key=>$field){
			if ($key == 'password'){
				$db->update('teachers', ['id'=>$id, $key=>md5($field)]);
			}
			else{
				$db->update('teachers', ['id'=>$id, $key=>$field]);
			}
			
		}
	}
}
?>