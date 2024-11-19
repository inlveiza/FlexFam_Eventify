<?php
   if($req[0] == 'displayevent'){
   	$response = $display->EventDisplay();
        echo json_encode($response);
   }