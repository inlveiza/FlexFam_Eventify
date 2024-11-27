<?php

  if($req[0] == 'schedule'){
  	$response = $user->EventTime($data_input);
      echo json_encode($response);
  }
  
  if($req[0] == 'register'){
  	$response = $user->EventRegister($data_input);
	  echo json_encode($response);
  }