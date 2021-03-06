<?php
require_once 'app/sawhat/model/CardFactory.class.php';
require_once 'core/storage/FileSystemIO.class.php';

class CardStore{
	const DIR = "storage/";
	const EXT = ".txt";
	const MAX_RECURSIVE_LEVEL = 2;
	
	public static function get_folder(){
		return "app/sawhat/".self::DIR;
	}
	
	public static function get_all($filter = null){
		$result = array();
		// look for text files in folder sawhat
		$dir = self::get_folder()."*";
		foreach (glob($dir,GLOB_ONLYDIR) as $filename){
			$basename = basename($filename);
			if(
				is_null($filter)
			){
				$result[] = self::get($basename);
			}
			elseif(!empty($filter)) {
				$keywords = SearchHelper::explode_keywords($filter);
				$add_result = true;
				$filename = self::get_folder().$basename.'/'.$basename.'.txt';
				foreach($keywords['in'] as $keyword){
					if(!SearchHelper::keyword_in_file($keyword,$filename)){
						$add_result = false;
						break;
					}
				}
				if($add_result){
					foreach($keywords['out'] as $keyword){
						if(SearchHelper::keyword_in_file($keyword,$filename)){
							$add_result = false;
							break;
						}
					}
					if($add_result){
						$result[] = self::get($basename);
					}
				}
			}
		}
		return $result;
	}
	
	public static function exist($card_name){
		$folder = self::get_folder()."$card_name/";
		$filename = $folder.$card_name.".txt";
		return file_exists($filename);
	}

	public static function get($card_name, $recursive_level = 0){
		$folder = self::get_folder()."$card_name/";
		$filename = $folder.$card_name.".txt";
		$lines = (!file_exists($filename)) ? array() : file($filename);
		$card = new Card($card_name,$lines,$recursive_level);
		$card->files = FileSystemIO::get_files_in_dir($folder.'{*.jpg,*.jpeg,*.JPG,*.png,*.gif,*.zip}');
		foreach($card->files AS $key => $file){
			$file_type = preg_match('/^(.+)\.(zip)$/',$file->name) ? 'zip' : 'image';
			$card->files[$key]->type = $file_type;
		}
		return $card;
	}

	public static function upsert($card_name, $lines, $color, $is_private){
		if(empty($card_name) || empty($lines))
			return false;

		$last_edit = date("Ymd_Hm");
		$filenamenoext = self::get_folder()."".$card_name."/".$card_name;	
		$filename = self::get_folder()."".$card_name."/".$card_name.self::EXT;
		$is_private_as_string = ($is_private) ? "is_private\r\n" : "";
		if(file_exists($filename)){
			// UPDATE : save current first !
			copy($filename,$filenamenoext."_".$last_edit.self::EXT."old");
		}
		if(!is_dir(dirname($filename)))
			mkdir(dirname($filename));

		$lines = "$card_name\r\nlastedit: $last_edit\r\ncolor: $color\r\n$is_private_as_string\r\n".$lines;
		$lines = str_replace("\r\n", "\n", $lines);
		$lines = str_replace("\r", "\n", $lines);
		return file_put_contents($filename,htmlentities($lines,ENT_COMPAT,'UTF-8'));
	}

	// ---- History
	public static function get_card_history($card_name){
		// look for .txtold files in card folder 
		$folder = self::get_folder()."$card_name/*".self::EXT."old";
		return array_reverse(FileSystemIO::get_files_in_dir($folder));
	}

	public static function get_card_version($card_name,$version){
		// look for .txtold file in card folder 
		$folder = self::get_folder()."$card_name/";
		$filename = self::get_folder()."$card_name/$version";
		$lines = (!file_exists($filename)) ? array() : file($filename);
		$card = new Card($card_name,$lines,0); 
		$card->files = FileSystemIO::get_files_in_dir($folder.'{*.jpg,*.jpeg,*.JPG,*.png,*.gif}');
		return $card;
	}
}
?>