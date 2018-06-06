<?php
/**
*Plugin Name: WPKC x LINE x BOT
*Description: 同左
**/
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
require(dirname( __FILE__ ) . '/inc/LINEBotTiny.php');

add_action( 'admin_post_line_bot', 'wpkc_line_bot' );
add_action( 'admin_post_nopriv_line_bot', 'wpkc_line_bot' );

function wpkc_line_bot() {

$channelAccessToken = '填入AccessToken';
$channelSecret = '填入Secret';

$client = new LINEBotTiny($channelAccessToken, $channelSecret);
foreach ($client->parseEvents() as $event) {
    switch ($event['type']) {
        case 'message':
            $message = $event['message'];
            switch ($message['type']) {
                case 'text':
								$actions[] = $client->actionMessage('postback','菜單',"button=confirm&ans=yes");
								$actions[] = $client->actionMessage('postback','訂購',"button=confirm&ans=no");
								$messages[] = $client->confirmMessage('測試','您好 請問要使用什麼樣的服務呢?',$actions);
                    $client->replyMessage(array(
                        'replyToken' => $event['replyToken'],
                        'messages' => $messages,
                        )
                    );
                    break;
                default:
                    error_log("Unsupporeted message type: " . $message['type']);
                    break;
            }
            break;
				case 'postback':
						$postback = $event['postback'];
						$postback_data = $client->parsePostback($postback);
						switch ($postback_data['button']) {
							case 'confirm':
							if ($postback_data['ans']=='yes') {
								$messages[] = $client->textMessage("您選了菜單功能!!");
								$client->replyMessage(array(
										'replyToken' => $event['replyToken'],
										'messages' => $messages,
										)
								);
							}else {
								$messages[] = $client->textMessage("您選了訂購功能!!");
								$client->replyMessage(array(
										'replyToken' => $event['replyToken'],
										'messages' => $messages
										)
								);
							}
							break;
						}
					break;
				case 'follow':
				$messages[] = $client->textMessage('歡迎使用聊天機器人');
				$client->replyMessage(array(
						'replyToken' => $event['replyToken'],
						'messages' => $messages
						)
				);
					break;
        default:
            error_log("Unsupporeted event type: " . $event['type']);
            break;
    }
};
}
 ?>
