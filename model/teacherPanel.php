<?php
require_once('DBapi.php');
require_once('./view/templateClass.php');
use model\dbapi\DBapi;

class teacherPanel{


	private $dbName;
	private $dbuser;
	private $dbPass;
	function __construct(){
		require('./config.php');
		$this->dbName = $dbName;
		$this->dbUser = $dbUser;
		$this->dbPass = $dbPass;
	}

	function getTpanel($id){
		$template = new template('./view/tpanel.html');
		$db = new DBapi('mysql', 'localhost', $this->dbName, $this->dbName, $this->dbPass);
		$user = end($db->get('teachers', ['id'=>$id]));
		$template->replace('name' , $user['name']);
		$template->replace('balance' , $user['balance']);
		$template->replace('id' , $user['id']);
		$template->replace('img' , '/imgs/' . $user['avatar']);
		$gr = new template('./view/myGroups.html');
		$groups = $db->makeQuery('SELECT * FROM groups WHERE teacher_id = ' . $id);
		foreach ($groups as $group){
			$lessons = $db->makeQuery('SELECT lessons.time_start, lessons.time_end, hall.name as hname, filials.name as fname, lessons.id FROM lessons LEFT JOIN hall ON hall.id = lessons.hall_id LEFT JOIN filials ON filials.id = hall.filial_id WHERE group_id = ' . $group['id']);
			uasort($lessons, function ($a, $b) { 
							if ($a['time_start'] == $b['time_start']){
								return 0;
							}
							 return ($a['time_start'] < $b['time_start']) ? -1 : 1;

						});
			$gr->replace('group_name' , $group['group_name']);
			$i = 1;
			foreach ($lessons as $lesson){

				if ($lesson['time_start'] > time()){
					$gr->replace('lessid', $lesson['id']);
					$gr->replace('near_lesson', date('d-m-Y H:i', $lesson['time_start']));
					$gr->replace('near_lesson_addr', $lesson['fname'] . ' ' . $lesson['hname']);
					$nowLesson = $lesson['id'];
					
					break;
				}
				elseif (count($lessons) == $i){
					$gr->replace('near_lesson', '');
					$gr->replace('near_lesson_addr', '');
				}
				$i++;
			}
			$students = $db->makeQuery('SELECT users.name as name, users.id as id FROM record_users LEFT JOIN users ON users.id = record_users.user_id WHERE record_users.group_id = ' . $group['id']);
			$sList = '';
			foreach ($students as $student){

				$res = $db->makeQuery('SELECT * FROM visits WHERE lesson_id = ' . $nowLesson  . ' AND user_id = ' . $student['id']);
				if (!empty(end($res)['id'])){
					$selected = 'checked';
				}
				else{
					$selected = '';
				}
				$sList .= '<div class="checkbox"> <label><input type="checkbox" ' . $selected . ' name = "students[]" value="' . $student['id'] . '">' . $student['name'] . '</label></div>';
			}
			$gr->replace('student_list', $sList);
			$gr->replace('id', $group['id']);
		}

		$template->replace('groups', $gr->getTemplate());
		return $template->getTemplate();
	}

	function inmultiarray($elem, $array)
	{
    while (current($array) !== false) {
        if (current($array) == $elem) {
            return true;
        } elseif (is_array(current($array))) {
            if ($this->inmultiarray($elem, current($array))) {
                return true;
            }
        }
        next($array);
    }
    return false;
	}

	function saveData($data){

	$db = new DBapi('mysql', 'localhost', $this->dbName, $this->dbName, $this->dbPass);
	$students = $data['students'];
	if (!(is_array($data['students']))){
		$students[] = $data['students'];
	}

	$db->makeQuery('SELECT * FROM visits WHERE lesson_id = ' . $data['lessonid']);
	$visits = $db->makeQuery('SELECT * FROM visits WHERE lesson_id = ' . $data['lessonid']);
	if (empty($students)){
		return 0;
	}

	foreach ($students as $student){
			
		if (!$this->inmultiarray($student, $visits)) {
			$db->insert('visits', ['user_id'=>$student, 'lesson_id'=>$data['lessonid']]);
		}

		
	}
	foreach ($visits as $visit){
		if (!in_array($visit['user_id'], $students)){
			$db->makeQuery('DELETE FROM visits WHERE user_id = ' . $visit['user_id'] . ' AND lesson_id = ' . $visit['lesson_id']);
			
		}
	}


	}

		function getUserSettings($id, $error = false){
		$db = new DBapi('mysql', 'localhost', $this->dbName, $this->dbName, $this->dbPass);
		$info = end($db->get('teachers', ['id'=>$id]));
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
			
			$db->update('teachers',['id'=>$id, 'avatar'=>$newFileName]);
		}	
		foreach ($data as $key=>$field){
			$db->update('teachers',['id'=>$id, $key=>$field]);
		}
		return $this->getUserSettings($id);
	}
	function changePass($id, $data){
		$db = new DBapi('mysql', 'localhost', $this->dbName, $this->dbName, $this->dbPass);
		$info = end($db->get('teachers',['id'=>$id]));
		if ($data['newPass'] != $data['newPass1']){
			return $this->getUserSettings($id, 'Пароли не совпадают');
		}
		elseif (md5($data['newPass'])!=$info['password']){
			return $this->getUserSettings($id, 'Введите корректный старый пароль');
		}
		else{
			
			$db->update('teachers',['id'=>$id, 'password'=>md5($data['newPass'])]);
			return $this->getUserSettings($id, 'Пароль успешно изменен');
		}
	}

	function getList($id, $post = false){
		$template = new template('./view/table.html');
		$db = new DBapi('mysql', 'localhost', $this->dbName, $this->dbName, $this->dbPass);
		if ($post == false){
			$lessons = $db->makeQuery('SELECT * FROM lessons WHERE group_id = ' . $id);
		}
		else{
			$tme = explode('/',$post['month']);
			$lessons = $db->makeQuery('SELECT * FROM lessons WHERE group_id = ' . $id . ' AND time_start > ' . $tme[0] . ' AND time_start < ' . $tme[1] );
		}

		$users = $db->makeQuery('SELECT record_users.user_id as id, users.name  FROM record_users LEFT JOIN groups ON record_users.group_id = groups.id LEFT JOIN lessons ON lessons.group_id = groups.id LEFT JOIN users ON users.id = record_users.user_id  WHERE groups.id = ' . $id . ' GROUP BY record_users.id ');
		


		uasort($lessons, function ($a, $b) { 
			if ($a['time_start'] == $b['time_start']){
				return 0;
			}

			return ($a['time_start'] < $b['time_start']) ? -1 : 1;

		});



		foreach ($lessons as $lesson){
			$days .= '<th>' . date('d.m.Y', $lesson['time_start']) . '</th>';
			$studList = '';
			foreach ($users as $user){
				$keys[] = $user['name'];
				$visit = $db->makeQuery('SELECT * FROM visits WHERE user_id = ' . $user['id'] . ' AND lesson_id = ' . $lesson['id']);
				if (empty($visit)){
					$studList[$user['name']] = 'Н';
				}
				else{
					$studList[$user['name']] = '+';
				}
				
			}
			$lestArr[] = $studList;


		}
		if (empty($keys)){
			$m = date('m', time());
		for($i=1;$i<=$m;$i++){
			$time = strtotime('1.' . $i . '.2017');
			$end = strtotime('31.' . $i . '.2017');
			$opt .= '<option value = "' . $time . '/' . $end . '">' . date('M', $time) . '</option>';
		}
		$template->replace('month', $opt);
			$template->replace('days', '');
			$template->replace('students', '');
			return $template->getTemplate();
		}
		$keys = array_unique($keys);
		foreach ($keys as $k=>$key){
			
			$dd .= '<tr>';
			$dd .= '<td>' . $key . '</td>';
				for($i=0;$i<count($lestArr);$i++){
					$dd .= '<td>' . $lestArr[$i][$key] . '</td>';
				}

			$dd .= '</tr>';
		}
		


		$template->replace('students', $dd);
		$template->replace('days', $days);
		$m = date('m', time());
		for($i=1;$i<=$m;$i++){
			$time = strtotime('1.' . $i . '.2017');
			$end = strtotime('31.' . $i . '.2017');
			$opt .= '<option value = "' . $time . '/' . $end . '">' . date('M', $time) . '</option>';
		}
		$template->replace('month', $opt);
		return $template->getTEmplate();
	}





}