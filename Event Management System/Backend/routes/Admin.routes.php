<?php
	if($req[0] == 'addevent'){
		$response = $admin->AddEvent($data_input);
        echo json_encode($response);
	}


