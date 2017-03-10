<?php

require_once __DIR__ . DIRECTORY_SEPARATOR . 'vendor/autoload.php';

use juno_okyo\Chatfuel;

$chatfuel = new Chatfuel(TRUE);
$chatfuel->sendText('Hello, World!');

// $chatfuel->createQuickReply('Quick Replies', array(
//   $chatfuel->createQuickReplyButton('Test', ['block', 'block 2'])
// ));
