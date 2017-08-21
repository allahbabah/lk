<?php
require_once('DBapi.php');
require_once('./view/templateClass.php');
use model\dbapi\DBapi;

class userPanel{


	private $dbName;
	private $dbuser;
	private $dbPass;
	function __construct(){
		require('./config.php');
		$this->dbName = $dbName;
		$this->dbUser = $dbUser;
		$this->dbPass = $dbPass;
	}
	function saveAnkerta($id,$data){
		$db = new DBapi('mysql', 'localhost', $this->dbName, $this->dbName, $this->dbPass);
		$id = $db->insert('user_info', ['user_id'=>$id]);
		foreach ($data as $key=>$field){
			$db->update('user_info', ['id'=>$id, $key=>$field]);
		}
	}
	function getUserSettings($id, $error = false){
		$db = new DBapi('mysql', 'localhost', $this->dbName, $this->dbName, $this->dbPass);
		$info = end($db->get('users', ['id'=>$id]));
    	$template = new template('./view/settings.html');
    	$template->replace('img', $info['avatar']);
    	$template->replace('email', $info['email']);
    	$template->replace('tel', $info['telephone']);
    	$template->replace('vk', $info['vk']);
    	$template->replace('fb', $info['facebook']);
    	$template->replace('od', $info['od']);
    	$template->replace('inst', $info['instagram']);
    	if ($error){
    		$template->replace('message', $error);
    	}
    	else{
    		$template->replace('message', '');
    	}
   	 	return $template->getTemplate();	
	}
	function saveUserSettings($id, $data, $file){
		$db = new DBapi('mysql', 'localhost', $this->dbName, $this->dbName, $this->dbPass);
		if (!empty($file)){
			$uploaddir = './imgs/';
			$fileName = explode('.', $file['avatar']['name'])[1];
			$newFileName = md5(time()) . '.' . $fileName;
			$path = $uploaddir . $newFileName;
			move_uploaded_file($file['avatar']['tmp_name'], $path);
			
			$db->update('users',['id'=>$id, 'avatar'=>$newFileName]);
		}	
		foreach ($data as $key=>$field){
			$db->update('users',['id'=>$id, $key=>$field]);
		}
		return $this->getUserSettings($id);
	}
	function changePass($id, $data){
		$db = new DBapi('mysql', 'localhost', $this->dbName, $this->dbName, $this->dbPass);
		$info = end($db->get('users',['id'=>$id]));
		if ($data['newPass'] != $data['newPass1']){
			return $this->getUserSettings($id, 'Пароли не совпадают');
		}
		elseif (md5($data['newPass'])!=$info['password']){
			return $this->getUserSettings($id, 'Введите корректный старый пароль');
		}
		else{
			
			$db->update('users',['id'=>$id, 'password'=>md5($data['newPass'])]);
			return $this->getUserSettings($id, 'Пароль успешно изменен');
		}
	}
	function getGroupsUser($id){
		$db = new DBapi('mysql', 'localhost', $this->dbName, $this->dbName, $this->dbPass);
		$data = $db->makeQuery('SELECT * FROM record_users LEFT JOIN groups ON groups.id = record_users.group_id WHERE record_users.user_id = ' . $id);
		$template = new template('./view/groupUser.html');
		$fld = '';
		foreach ($data as $gr){
			$template->replace('group_name', $gr['group_name']);
			$entData = end($db->makeQuery('SELECT abonement.name as aname FROM groups LEFT JOIN group_type ON group_type.id = groups.group_type_id LEFT JOIN abonement ON abonement.id = group_type.abonement_id  WHERE groups.id = ' . $gr['group_id']));
			$lessons = $db->makeQuery('SELECT lessons.time_start, lessons.time_end, hall.name as hname, filials.name as fname FROM lessons LEFT JOIN hall ON hall.id = lessons.hall_id LEFT JOIN filials ON filials.id = hall.filial_id WHERE group_id = ' . $gr['group_id']);
			uasort($lessons, function ($a, $b) { 
							if ($a['time_start'] == $b['time_start']){
								return 0;
							}
							 return ($a['time_start'] < $b['time_start']) ? -1 : 1;

				});
			$i = 1;
			foreach ($lessons as $lesson){
				if ($lesson['time_start'] > time()){
					$template->replace('near_lesson', date('d-m-Y H:i', $lesson['time_start']));
					$template->replace('near_lesson_addr', $lesson['fname'] . ' ' . $lesson['hname']);
					
					break;
				}
				elseif (count($lessons) == $i){
					$template->replace('near_lesson', '');
					$template->replace('near_lesson_addr', '');
				}
				$i++;
			}
			
			$miss = end($db->makeQuery('SELECT COUNT(*) as count FROM lessons LEFT JOIN visits ON visits.lesson_id = lessons.id WHERE visits.lesson_id IS NULL AND lessons.time_end <' . time()))['count'];
			$template->replace('miss',$miss);
			$template->replace('used', count($db->makeQuery('SELECT * FROM visits LEFT JOIN lessons ON lessons.id = visits.lesson_id LEFT JOIN groups ON groups.id = lessons.group_id WHERE groups.id = ' . $gr['group_id'] )));

			$template->replace('last_lesson', date('d-m-Y H:i',end($lessons)['time_end']));

			$template->replace('abonement_name', $entData['aname']);
			$template->replace('teacher', end($db->makeQuery('SELECT teachers.name as tname FROM teachers LEFT JOIN groups ON groups.teacher_id = teachers.id WHERE groups.id = ' . $gr['group_id']))['tname']);
			$template->replace('less_all', count($db->makeQuery('SELECT * FROM lessons LEFT JOIN groups ON lessons.group_id = groups.id WHERE groups.id = ' . $gr['group_id'])));
			$visits = $db->makeQuery('SELECT * FROM visits LEFT JOIN lessons ON visits.lesson_id = lessons.id WHERE lessons.group_id = ' . $gr['group_id'] );
			foreach ($visits as $visit){
				$vv .= 'Посещение ' . date('d.m.Y H:i',$visit['time_start']);
			}
			$template->replace('visits_list', $vv);
			$fld .= $template->getTemplate(true);

		}
		return $fld;
		
		
	}
	public function getNews($id){
		$db = new DBapi('mysql', 'localhost', $this->dbName, $this->dbName, $this->dbPass);
		$news = $db->makeQuery('SELECT groups.message, groups.group_name as name FROM record_users LEFT JOIN groups ON groups.id = record_users.group_id WHERE record_users.user_id = ' . $id);
		foreach ($news as $new){
			$data .= 'Сообщение в группе "' . $new['name'] . '": ' . $new['message'] . '<br>';
		}
		return $data;
	}


}