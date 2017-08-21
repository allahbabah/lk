<?php 

class template{
	private $templ;
	private $oldTempl;
	function __construct($file){
		$this->templ = file_get_contents($file);
		if (empty($this->templ)){
			throw new Exception('Template file doesnt exist or empty');
		}
		$this->oldTempl = $this->templ;
	}

	public function getTemplate($reset = false){

		$r = $this->templ;
		if ($reset == true){
			$this->templ = $this->oldTempl;
			return $r;
		}
		else{
			return $r;
		}
	}

	public function echoTemplate(){
		echo $this->templ;
	}
	public function replace($tag, $text){
		$this->templ =  str_replace("{{" . $tag . '}}', $text, $this->templ);
	}
	static function redirect($page){
		require('./config.php');
		header('Location: ' . $siteDomen . $page);
	}
	public function insertTable($tag, $data, $button = false){
		$table = '';
		foreach ($data as $rows){
			$table .= '<tr>';
			foreach($rows as $row){
				$table .= '<td>' . $row . '</td>';

			}
				if ($button == true){
					$table .= '<td><a href = "/editGroup/' . $rows['id'] . '"><button type="button" class="btn btn-primary">Редактировать</button></td></a>';
				}
			$table .= '</tr>';
		}
		$this->replace($tag, $table);


	}
	public function getOptionList($name, $opt, $data, $selected = false){
		$out = '<div class="form-group"><label for="exampleFormControlSelect1">' . $name . '</label><select class="form-control" id="exampleFormControlSelect1" name = "' . $opt . '">';
		foreach ($data as $select){
			if ($selected == $select['id']) {
				$sel = 'selected';
			}
			else{
				$sel = '';
			}
			$out .= '<option value="' . $select['id'] . '"' . $sel . '>' . $select['fname'] . '</option>';
		}
		$out .= '</select> </div>';
		return $out;
	}

	public function getLessonsList($data){
		$res = '';
		$i = 1;

		foreach ($data as $key=>$lesson){
			
			$res .= '<li class="list-group-item">Урок №' . $i . ' Начало: ' . date('d-m-Y H:i',$lesson['time_start']) . ' Конец: ' . date('d-m-Y H:i',$lesson['time_end']) . ' Зал: ' . $lesson['name'] . '<a href = "/admin/editLesson/' . $lesson['id'] . '"<button type="button" class="btn btn-primary">Редактировать</button></a></li>';
			$i++;
		}
		return $res;
	}


}