<?php

 if ($req[0]=='login') {
    echo json_encode($auth->login($data_input));
    return;
}
if ($req[0]=='register') {
    echo json_encode($auth->register($data_input));
    return;
}
if ($req[0]=='logout') {
    echo json_encode($auth->logout());
    return;
}
