<?php
	if($req[0] == 'addevent'){
		$response = $admin->AddEvent($data_input);
        echo json_encode($response);
	}
	
	if ($req[0] == 'updateevent') {
         if(isset($req[1]) && !empty($data_input)){
            $id = (int)$req[1]; 
            $response = $admin->UpdateEvent($id, $data_input);
            echo json_encode($response);
         }
    }


