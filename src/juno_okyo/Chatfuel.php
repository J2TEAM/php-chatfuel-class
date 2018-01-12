<?php
/**
 * Copyright 2017 Juno_okyo <junookyo@gmail.com>
 *
 * Website: https://junookyo.blogspot.com/
 */
namespace juno_okyo;

class Chatfuel
{
  const VERSION = '1.0.1';

  const WV_SHARE_BUTTON_HIDE    = 'hide';
  const WV_HEIGHT_RATIO_COMPACT = 'compact';
  const WV_HEIGHT_RATIO_TALL    = 'tall';
  const WV_HEIGHT_RATIO_FULL    = 'full';

  protected $response = array();

  public function __construct($debug = FALSE)
  {
    if (( ! $debug) && ( ! isset($_SERVER['HTTP_USER_AGENT']) OR strpos($_SERVER['HTTP_USER_AGENT'], 'Apache-HttpAsyncClient') === FALSE)) {
      exit;
    }
  }

  public function __destruct()
  {
    if (count($this->response) > 0) {
      try {
        header('Content-Type: application/json');
        echo json_encode(array('messages' => $this->response));
        exit;
      } catch (Exception $e) {
        // noop
      }
    }
  }

  public function sendText($messages = null)
  {
    if (is_null($messages)) {
      throw new Exception('Invalid input', 1);
    }

    $type = gettype($messages);
    if ($type === 'string') {
      $this->response[] = array('text' => $messages);
    } elseif ($type === 'array' || is_array($messages)) {
      foreach ($messages as $message) {
        $this->response[] = array('text' => $message);
      }
    } else {
      $this->response[] = array('text' => 'Error!');
    }
  }

  public function sendImage($url)
  {
    if ($this->isURL($url)) {
      $this->sendAttachment('image', array('url' => $url));
    } else {
      $this->sendText('Error: Invalid URL!');
    }
  }

  public function sendVideo($url)
  {
    if ($this->isURL($url)) {
      $this->sendAttachment('video', array('url' => $url));
    } else {
      $this->sendText('Error: Invalid URL!');
    }
  }

  public function sendAudio($url)
  {
    if ($this->isURL($url)) {
      $this->sendAttachment('audio', array('url' => $url));
    } else {
      $this->sendText('Error: Invalid URL!');
    }
  }

  public function sendTextCard($text, $buttons)
  {
    if (is_array($buttons)) {
      $this->sendAttachment('template', array(
        'template_type' => 'button',
        'text'          => $text,
        'buttons'       => $buttons
      ));

      return TRUE;
    }

    return FALSE;
  }

  public function sendGallery($elements)
  {
    if (is_array($elements)) {
      $this->sendAttachment('template', array(
        'template_type' => 'generic',
        'elements'      => $elements
      ));

      return TRUE;
    }

    return FALSE;
  }

  public function createElement($title, $image, $subTitle, $buttons)
  {
    if ($this->isURL($image) && is_array($buttons)) {
      return array(
        'title'     => $title,
        'image_url' => $image,
        'subtitle'  => $subTitle,
        'buttons'   => $buttons
      );
    }

    return FALSE;
  }

  public function createButtonToBlock($title, $block, $setAttributes = NULL)
  {
    $button = array();
    $button['type'] = 'show_block';
    $button['title'] = $title;
    
    if (is_array($block)) {
      $button['block_names'] = $block;
    } else {
      $button['block_name'] = $block;
    }

    if ( ! is_null($setAttributes) && is_array($setAttributes)) {
      $button['set_attributes'] = $setAttributes;
    }

    return $button;
  }

  public function createButtonToURL($title, $url, $setAttributes = array(), $messengerExtensions = array())
  {
    if ($this->isURL($url)) {
      $button = array();
      $button['type'] = 'web_url';
      $button['url'] = $url;
      $button['title'] = $title;
      
      if ( ! is_null($setAttributes) && is_array($setAttributes)) {
        $button['set_attributes'] = $setAttributes;
      }

      if(!is_null($messengerExtensions) && is_array($messengerExtensions) && !empty($messengerExtensions)){
        $button['messengerExtensions'] = true;
        
        $allowedHeights = array(self::WV_HEIGHT_RATIO_COMPACT, self::WV_HEIGHT_RATIO_TALL, self::WV_HEIGHT_RATIO_FULL);
        if(isset($messengerExtensions['webview_height_ratio']) && in_array($messengerExtensions['webview_height_ratio'], $allowedHeights)){
          $button['webview_height_ratio'] = $messengerExtensions['webview_height_ratio'];
        }

        if(isset($messengerExtensions['webview_share_button'])){
          $button['webview_share_button'] = self::WV_SHARE_BUTTON_HIDE; 
        }

      }

      return $button;
    }

    return FALSE;
  }

  public function createPostBackButton($title, $url)
  {
    if ($this->isURL($url)) {
      return array(
        'url'   => $url,
        'type'  => 'json_plugin_url',
        'title' => $title
      );
    }

    return FALSE;
  }

  public function createCallButton($phoneNumber, $title = 'Call')
  {
    return array(
      'type'         => 'phone_number',
      'phone_number' => $phoneNumber,
      'title'        => $title
    );
  }

  public function createShareButton()
  {
    return array('type' => 'element_share');
  }

  public function createQuickReply($text, $quickReplies)
  {
    if (is_array($quickReplies)) {
      $this->response['text'] = $text;
      $this->response['quick_replies'] = $quickReplies;
      return TRUE;
    }

    return FALSE;
  }

  public function createQuickReplyButton($title, $block)
  {
    $button = array();
    $button['title'] = $title;

    if (is_array($block)) {
      $button['block_names'] = $block;
    } else {
      $button['block_name'] = $block;
    }

    return $button;
  }

  private function sendAttachment($type, $payload)
  {
    $type = strtolower($type);
    $validTypes = array('image', 'video', 'audio', 'template');

    if (in_array($type, $validTypes)) {
      $this->response[] = array(
        'attachment' => array(
          'type'    => $type,
          'payload' => $payload
        )
      );
    } else {
      $this->response[] = array('text' => 'Error: Invalid type!');
    }
  }

  private function isURL($url)
  {
    return filter_var($url, FILTER_VALIDATE_URL);
  }
}
