<?php
class controller_service extends controller {
	public function __construct(){
		parent::__construct();
	}

	public function index($media = 'all') {
		$mmedia = new model_media();
		$tvs = $mmedia->getAll($media);
		$data['media'] = $tvs;
		header('content-type:application/json');
		echo json_encode($data);
		exit;
	}
} 