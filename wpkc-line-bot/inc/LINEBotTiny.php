<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
if (!function_exists('hash_equals')) {
    defined('USE_MB_STRING') or define('USE_MB_STRING', function_exists('mb_strlen'));

    function hash_equals($knownString, $userString)
    {
        $strlen = function ($string) {
            if (USE_MB_STRING) {
                return mb_strlen($string, '8bit');
            }

            return strlen($string);
        };

        // Compare string lengths
        if (($length = $strlen($knownString)) !== $strlen($userString)) {
            return false;
        }

        $diff = 0;

        // Calculate differences
        for ($i = 0; $i < $length; $i++) {
            $diff |= ord($knownString[$i]) ^ ord($userString[$i]);
        }
        return $diff === 0;
    }
}

class LINEBotTiny
{
    public function __construct($channelAccessToken, $channelSecret)
    {
        $this->channelAccessToken = $channelAccessToken;
        $this->channelSecret = $channelSecret;
    }

    public function parseEvents()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            error_log("Method not allowed");
            exit();
        }

        $entityBody = file_get_contents('php://input');

        if (strlen($entityBody) === 0) {
            http_response_code(400);
            error_log("Missing request body");
            exit();
        }

        if (!hash_equals($this->sign($entityBody), $_SERVER['HTTP_X_LINE_SIGNATURE'])) {
            http_response_code(400);
            error_log("Invalid signature value");
            exit();
        }

        $data = json_decode($entityBody, true);
        if (!isset($data['events'])) {
            http_response_code(400);
            error_log("Invalid request body: missing events property");
            exit();
        }
        return $data['events'];
    }

    public function replyMessage($message)
    {
        $header = array(
            "Content-Type: application/json",
            'Authorization: Bearer ' . $this->channelAccessToken,
        );

        $context = stream_context_create(array(
            "http" => array(
                "method" => "POST",
                "header" => implode("\r\n", $header),
                "content" => json_encode($message),
            ),
        ));
				$ch = curl_init("https://api.line.me/v2/bot/message/reply");
				curl_setopt($ch, CURLOPT_POST, true);
				curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
				curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($message));
				curl_setopt($ch, CURLOPT_HTTPHEADER, array(
				    'Content-Type: application/json',
				    'Authorization: Bearer '. $this->channelAccessToken
				    //'Authorization: Bearer '. TOKEN
				));
				$result = curl_exec($ch);
				error_log("Request failed: " . $result);
				curl_close($ch);
    }

		public function parsePostback($postback)
		{
			$postback_data_encode = $postback['data'];
			$postback_data = array();
			$data_seper1 = explode("&",$postback_data_encode);
			foreach ($data_seper1 as $key => $value) {
				$data_seper2 = explode("=",$value);
				$postback_data[$data_seper2[0]] = $data_seper2[1];
			}
			return $postback_data;
		}
		/*
		================================
		================================

		textMessage

		ex. $client->textMessage($text);

		================================
		================================
		*/
		public function textMessage($text)
		{
			$message = array(
				"type"=> "text",
				"text"=> $text,
			);
			return $message;
		}
		/*
		================================
		================================

		stickerMessage

		ex. $client->stickerMessage($packageId,$stickerId);

		================================
		================================
		*/
		public function stickerMessage($packageId,$stickerId)
		{
			$message = array(
				"type"=> "sticker",
				"packageId"=> $packageId,
				"stickerId"=> $stickerId,
			);
			return $message;
		}
		/*
		================================
		================================

		imageMessage

		ex. $client->imageMessage($originalContentUrl,$previewImageUrl);

		================================
		================================
		*/
		public function imageMessage($originalContentUrl,$previewImageUrl)
		{
			$message = array(
				"type"=> "image",
				"originalContentUrl"=> $originalContentUrl,
				"previewImageUrl"=> $previewImageUrl,
			);
			return $message;
		}
		/*
		================================
		================================

		videoMessage

		ex. $client->videoMessage($originalContentUrl,$previewImageUrl);

		================================
		================================
		*/
		public function videoMessage($originalContentUrl,$previewImageUrl)
		{
			$message = array(
				"type"=> "video",
				"originalContentUrl"=> $originalContentUrl,
				"previewImageUrl"=> $previewImageUrl,
			);
			return $message;
		}
		/*
		================================
		================================

		audioMessage

		ex. $client->audioMessage($originalContentUrl,$duration);

		================================
		================================
		*/
		public function audioMessage($originalContentUrl,$duration)
		{
			$message = array(
				"type"=> "audio",
				"originalContentUrl"=> $originalContentUrl,
				"duration"=> $duration,
			);
			return $message;
		}
		/*
		================================
		================================

		locationMessage

		ex. $client->locationMessage($title,$address,$latitude,$longitude);

		================================
		================================
		*/
		public function locationMessage($title,$address,$latitude,$longitude)
		{
			$message = array(
				"type"=> "location",
				"title"=> $title,
				"address"=> $address,
				"latitude"=> $latitude,
				"longitude"=> $longitude,
			);
			return $message;
		}
		/*
		================================
		================================

		imagemapMessage

		ex.
		$area = array(
		"x"=>520,
   	"y"=>0,
   	"width"=>520,
   	"height"=>1040
 		);
		$actions[] = $client->actionMessage($type='message',$label,$text,$area);
		$actions[] = $client->actionMessage($type='url',$label,$linkUri,$area);
		$client->imagemapMessage($baseUrl,$altText,$baseSize,$actions);

		================================
		================================
		*/
		public function imagemapMessage($baseUrl,$altText,$baseSize,$actions)
		{
			$message = array(
				"type"=> "imagemap",
				"baseUrl"=> $baseUrl,
				"altText"=> $altText,
				"baseSize"=> $baseSize,
				"actions"=> $actions,
			);
			return $message;
		}
		/*
		================================
		================================

		buttonMessage

		ex.
		$defaultAction = $client->actionMessage($type,$label,$var_1,$var_2,$var_3,$var_4,$var_5);
		$actions[] = $client->actionMessage($type,$label,$var_1,$var_2,$var_3,$var_4,$var_5);
		$client->buttonMessage($altText,$thumbnailImageUrl,$title,$text,$defaultAction,$actions,$imageAspectRatio,$imageSize,$imageBackgroundColor);

		================================
		================================
		*/
		public function buttonMessage($altText,$thumbnailImageUrl,$title,$text,$defaultAction,$actions,$imageAspectRatio="rectangle",$imageSize="cover",$imageBackgroundColor="#FFFFFF")
		{
			$message = array(
				"type"=> "template",
				"altText"=> $altText,
				"template"=> array(
					"type"=> "buttons",
					"thumbnailImageUrl"=> $thumbnailImageUrl,
					"imageAspectRatio"=> $imageAspectRatio,
					"imageSize"=> $imageSize,
					"imageBackgroundColor"=> $imageBackgroundColor,
					"title"=> $title,
					"text"=> $text,
					"defaultAction"=> $defaultAction,
					"actions"=> $actions,
					),
			);
			return $message;
		}
		/*
		================================
		================================

		confirmMessage

		ex.
		$actions[] = $client->actionMessage($type,$label,$var_1,$var_2,$var_3,$var_4,$var_5);
		$client->confirmMessage($altText,$text,$actions);

		================================
		================================
		*/
		public function confirmMessage($altText,$text,$actions)
		{
			$message = array(
				"type"=> "template",
				"altText"=> $altText,
				"template"=> array(
					"type"=> "confirm",
					"text"=> $text,
					"actions"=> $actions,
					),
			);
			return $message;
		}
		/*
		================================
		================================

		carouselMessage

		ex.
		$defaultAction = $client->actionMessage($type,$label,$var_1,$var_2,$var_3,$var_4,$var_5);
		$actions[] = $client->actionMessage($type,$label,$var_1,$var_2,$var_3,$var_4,$var_5);
		$columns[] = $client->columnMessage($type='carousel',$thumbnailImageUrl,$actions,$title,$text,$defaultAction,$imageBackgroundColor);
		$client->carouselMessage($altText,$columns,$imageAspectRatio,$imageSize);

		================================
		================================
		*/
		public function carouselMessage($altText,$columns,$imageAspectRatio="rectangle",$imageSize="cover")
		{
			$message = array(
				"type"=> "template",
				"altText"=> $altText,
				"template"=> array(
					"type"=> "carousel",
					"columns"=> $columns,
					"imageAspectRatio"=> $imageAspectRatio,
					"imageSize"=> $imageSize,
					),
			);
			return $message;
		}
		/*
		================================
		================================

		image_carouselMessage

		ex.
		$action = $client->actionMessage($type,$label,$var_1,$var_2,$var_3,$var_4,$var_5);
		$columns[] = $client->columnMessage($type='image_carousel',$imageUrl,$action);
		$client->image_carouselMessage($altText,$columns);

		================================
		================================
		*/
		public function image_carouselMessage($altText,$columns)
		{
			$message = array(
				'type' => 'template',
				'altText' => $altText,
				"template" => array(
					"type"=> "image_carousel",
					"columns"=> $columns,
					)
			);
			return $message;
		}
		/*
		================================
		================================

		actionMessage

		ex.
		$client->actionMessage($type='postback',$label,$data,$text);

		$client->actionMessage($type='message',$label,$text,$area);

		$client->actionMessage($type='uri',$label,$linkUri,$area);

		$client->actionMessage($type='datetimepicker',$label,$data,$mode,$initial,$max,$min);

		================================
		================================
		*/
		public function actionMessage($type,$label,$var_1='',$var_2='',$var_3=null,$var_4=null,$var_5=null)
		{
			switch ($type) {
				case 'postback':
				if (!empty($var_2)) {
				$message = array(
					'type' => $type,
					'label' => $label,
					"data" => $var_1,
					"text" => $var_2,
				);
			}else {
				$message = array(
					'type' => $type,
					'label' => $label,
					"data" => $var_1,
				);
			}
					break;
				case 'message':
				if (!empty($var_2)) {
				$message = array(
					'type' => $type,
					'label' => $label,
					"text" => $var_1,
					"area" => $var_2,
				);
			}else {
				$message = array(
					'type' => $type,
					'label' => $label,
					"text" => $var_1,
				);
			}
					break;
				case 'uri':
				if (!empty($var_2)) {
				$message = array(
					'type' => $type,
					'label' => $label,
					"linkUri" => $var_1,
					"area" => $var_2,
				);
			}else {
				$message = array(
					'type' => $type,
					'label' => $label,
					"linkUri" => $var_1,
				);
			}
					break;
				case 'datetimepicker':
				$message = array(
					'type' => $type,
					'label' => $label,
					"data" => $var_1,
					"mode" => $var_2,
					'initial' => $var_3,
					"max" => $var_4,
					"min" => $var_5,
				);
					break;

			}
			return $message;
		}

		public function columnMessage($type,$imageUrl,$actions,$var_2='',$var_3='',$var_4='',$var_5=null)
		{
			switch ($type) {
				case 'carousel':
				$message = array(
					'thumbnailImageUrl' => $imageUrl,
					'imageBackgroundColor' => $var_5,
					'title' => $var_2,
					'text' => $var_3,
					'defaultAction' => $var_4,
					'actions' => $actions,
				);
					break;
				case 'image_carousel':
				$message = array(
					'imageUrl' => $imageUrl,
					'action' => $actions,
				);
					break;
			}
			return $message;
		}

    private function sign($body)
    {
        $hash = hash_hmac('sha256', $body, $this->channelSecret, true);
        $signature = base64_encode($hash);
        return $signature;
    }
}
?>
