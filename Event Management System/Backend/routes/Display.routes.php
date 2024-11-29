<?php
   if($req[0] == 'displayevent'){
   	$response = $display->EventDisplay();
       echo json_encode($response);
   }
   if($req[0] == 'profile'){
   	$response = $display->ProfileDisplay();
       echo json_encode($response);
   }
   if($req[0] == 'reports'){
   	$response = $display->AudienceReport();
       echo json_encode($response);
   }