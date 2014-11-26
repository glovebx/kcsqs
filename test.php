<?php

//include_once('sqslog.php');

//echo sqslog::log('test', 'test_value');

include_once('kcsqs_client.php');
$kcsqs = new kcsqs();

$kcsqs->put('test', '你好test_value');
echo 'result='.$kcsqs->get('wxRev');
