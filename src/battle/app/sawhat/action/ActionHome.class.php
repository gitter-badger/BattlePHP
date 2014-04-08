<?php
require_once 'app/sawhat/config/config_sawhat.php';
require_once 'app/sawhat/model/CardFactory.class.php';
require_once 'app/sawhat/model/CardStore.class.php';
require_once 'core/storage/Uploader.class.php';
require_once 'core/auth/AuthHelper.class.php';
require_once 'core/model/AjaxResult.class.php';

// SAWHAT main controller
class ActionHome extends Controller{

	// Display the home page containing the home card if it exists, 
	// all the cards otherwise.
	// Treats any creation/update query submission.
	public function index(){
		$logged = AuthHelper::is_authenticated();

		// check if any form submission (save or login-to-see-the-private-card)
		if(isset($_POST['submit'])){
			$submit_action = $_POST['submit'];
			switch ($submit_action) {
				case 'save' : {
					$card_name = Request::isset_or($_POST['name'], ConfigurationSawhat::DEFAULT_CARD_NAME);
					$card_color = Request::isset_or($_POST['color'], Card::DEFAULT_COLOR);
					$card_lines = Request::isset_or($_POST['card'], "");
					$is_private = isset($_POST['is_private']);
					$card = CardStore::get($card_name);

					// do nothing if card is private and user is not authentified
					if($card === null || !$card->is_private || ($card->is_private && $logged)){
						$json = array(
							'is_saved' => CardStore::upsert($card_name,$card_lines,$card_color,$is_private),
							'return_url' => Request::get_application_virtual_root().$card_name
						);
						echo json_encode($json);
						exit(0);
					}
					break;
				}
				case 'login' : {
					$identity = new Identity();
					$identity->password = Request::isset_or($_POST['password'], "prout");
					$result_code = AuthHelper::authenticate(AuthHelper::AuthTypePassword,$identity);
					$logged = AuthHelper::is_authenticated();

					if($result_code > 0){
						// TODO Error::get_message($result_code);
						$this->add_error("error $result_code");
					}else{
						$this->assign("logged", true);
					}
					break;
				}
				case 'logout' : {
					AuthHelper::unauthenticate();
					break;
				}
				case 'search' : {
					header('location: '.Request::get_application_virtual_root().$_POST['search'].'/all_cards');
					break;
				}
			}
		}
		
		$params = Request::get_params("@card_name/@action");
		if($params){
			switch($params['action']){
				case 'edit':
					$ass_card = CardStore::get($params['card_name']);
					$this->assign('card',$ass_card);
					if(($ass_card->is_private && $logged) || !$ass_card->is_private){
						$ass_card->history = CardStore::get_card_history($params['card_name']);
						$this->display_page('section.card.update.tpl');
					}else{
						$this->display_page('section.card.tpl');
					}
					break;
				case 'as_code':
					$result = new AjaxResult();
					if($params['card_name'] == 'all_cards'){
						$result->body = "";
					}else{
						$card_version = Request::isset_or($_GET["card_version"],null);
						if($card_version !== null){
							$old_card = CardStore::get_card_version($params['card_name'],$card_version);
							$result->body = $old_card->text_code;
						}else{
							$card = CardStore::get($params['card_name']);
							$result->body = $card->text_code;
						}
					}
					echo $result->to_json();
					break;
				case 'as_html':
					$result = new AjaxResult();
					if($params['card_name'] == 'all_cards'){
						$result->body = "";
					}else{
						$ass_card = CardStore::get($params['card_name']);
						$this->assign('card',$ass_card);
						$this->assign('logged',$logged);
						$result->body = $this->fetch_view('element.card.v2.tpl');
					}
					echo $result->to_json();
					break;
				case 'all_cards':
					$ass_cards = CardStore::get_all($params['card_name']);
					if(!empty($ass_cards))
						$this->assign('cards',$ass_cards);
					$this->display_page('section.card.tpl');
					break;
			}
			return;
		}
		elseif($params = Request::get_params("@card_name")){
			if($params['card_name'] == 'all_cards'){
				$ass_cards = CardStore::get_all();
				if(!empty($ass_cards)){
					$this->assign('cards',$ass_cards);
					$this->display_page('section.card.tpl');
				}
			}else{
				// TEMP FIX for wrong get_params() behavior
				$card_name = (isset($_GET['controller']) || (array_key_exists('controller',$_GET) && $_GET['controller'] === null)) 
					? $params['card_name'] 
					: ConfigurationSawhat::DEFAULT_CARD_NAME;
				$ass_card = CardStore::get($card_name);
				$this->assign('card',$ass_card);
				$this->display_page('section.card.tpl');
			}
			return;
		}

		$ass_card = CardStore::get(ConfigurationSawhat::DEFAULT_CARD_NAME);
		if($ass_card->exists){
			$this->assign('card',$ass_card);
		}else{
			$ass_cards = CardStore::get_all();
			if(!empty($ass_cards))
				$this->assign('cards',$ass_cards);
		}
		$this->display_page('section.card.tpl');
	}

	// [sawhat/api] Submit a file to upload in AJAX.
	// returns a AjaxResult encoded in json containing the body of the response,
	// and the errors if any error occured
	public function api(){
		$result = new AjaxResult();
		// POST
		if(isset($_POST['submit'])) {
			$submit = $_POST['submit'];
			if($submit == "addfile"){
				$card_name = Request::isset_or($_POST['name'], "");
				if($card_name != "" && CardStore::exist($card_name)){
					$extensions = array(".jpg",".png",".jpeg",".JPG",".gif",".zip");
					try{
						$file = Uploader::process_form_file("file",CardStore::get_folder().$card_name,2000000,$extensions);
					}catch(Exception $e){ 
						$result->errors = "DON'T DO THAT"; 
						echo $result->to_json(); 
						return; 
					}

					// returns files
					$card = CardStore::get($card_name);
					$this->assign("card", $card);
					$body = $this->fetch_view("element.file_set.tpl");
					$result->body = $body;
				}
			}
		}
		echo $result->to_json();
	}
}
?>