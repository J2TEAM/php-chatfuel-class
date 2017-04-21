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

    protected $response = array();

    public function __construct($debug = false)
    {
        if ((! $debug) && (! isset($_SERVER['HTTP_USER_AGENT']) || strpos($_SERVER['HTTP_USER_AGENT'], 'Apache-HttpAsyncClient') === false)) {
            exit;
        }
    }

    public function __destruct()
    {
        // never run when execute PHPUnit
        if (count($this->response) > 0 && ! $this->isCLI()) {
            try {
                header('Content-Type: application/json');
                echo json_encode(array('messages' => $this->response));

                exit;
            } catch (\Exception $e) {
                // nop
            }
        }
    }

    public function sendText($messages = null)
    {
        if (is_null($messages)) {
            throw new \Exception('Invalid input', 1);
        }

        if (! is_string($messages) && ! is_array($messages)) {
            return $this->response[] = array('text' => 'Error!');
        }

        if (is_array($messages)) {
            $arr = array();

            foreach ($messages as $message) {
                $arr[] = array('text' => $message);
            }

            $this->response = array_merge($this->response, $arr);

            return $arr;
        }

        return $this->response[] = array('text' => $messages);
    }

    public function sendImage($url)
    {
        if (! $this->isURL($url)) {
            return $this->sendText('Error: Invalid URL!');
        }

        return $this->sendAttachment('image', array('url' => $url));
    }

    public function sendVideo($url)
    {
        if (! $this->isURL($url)) {
            return $this->sendText('Error: Invalid URL!');
        }

        return $this->sendAttachment('video', array('url' => $url));
    }

    public function sendAudio($url)
    {
        if (! $this->isURL($url)) {
            return $this->sendText('Error: Invalid URL!');
        }

        return $this->sendAttachment('audio', array('url' => $url));
    }

    public function sendTextCard($text, $buttons)
    {
        if (! is_array($buttons)) {
            return;
        }

        return $this->sendAttachment('template', array(
            'template_type' => 'button',
            'text'          => $text,
            'buttons'       => $buttons
        ));
    }

    public function sendGallery($elements)
    {
        if (! is_array($elements)) {
            return;
        }

        return $this->sendAttachment('template', array(
            'template_type' => 'generic',
            'elements'      => $elements
        ));
    }

    public function createElement($title, $image, $subTitle, $buttons)
    {
        if (! $this->isURL($image) || ! is_array($buttons)) {
            return;
        }

        return array(
            'title'     => $title,
            'image_url' => $image,
            'subtitle'  => $subTitle,
            'buttons'   => $buttons
        );
    }

    public function createButtonToBlock($title, $block, $setAttributes = null)
    {
        $button = array();
        $button['type'] = 'show_block';
        $button['title'] = $title;

        if (is_array($block)) {
            $button['block_names'] = $block;
        }

        if (is_string($block)) {
            $button['block_name'] = $block;
        }

        if (! is_null($setAttributes) && is_array($setAttributes)) {
            $button['set_attributes'] = $setAttributes;
        }

        return $button;
    }

    public function createButtonToURL($title, $url, $setAttributes = null)
    {
        if (! $this->isURL($url)) {
            return;
        }

        $button = array();
        $button['type'] = 'web_url';
        $button['url'] = $url;
        $button['title'] = $title;

        if (! is_null($setAttributes) && is_array($setAttributes)) {
            $button['set_attributes'] = $setAttributes;
        }

        return $button;
    }

    public function createPostBackButton($title, $url)
    {
        if (! $this->isURL($url)) {
            return;
        }

        return array(
            'url'   => $url,
            'type'  => 'json_plugin_url',
            'title' => $title
        );
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
        if (! is_array($quickReplies)) {
            return;
        }

        $arr = array('text' => $text, 'quick_replies' => $quickReplies);

        $this->response = array_merge($this->response, $arr);

        return $arr;
    }

    public function createQuickReplyButton($title, $block)
    {
        $button = array();
        $button['title'] = $title;

        if (is_array($block)) {
            $button['block_names'] = $block;
        }

        if (is_string($block)) {
            $button['block_name'] = $block;
        }

        return $button;
    }

    private function sendAttachment($type, $payload)
    {
        $type = strtolower($type);
        $validTypes = array('image', 'video', 'audio', 'template');

        if (! in_array($type, $validTypes)) {
            return $this->response[] = array('text' => 'Error: Invalid type!');
        }

        return $this->response[] = array(
            'attachment' => array(
                'type'    => $type,
                'payload' => $payload
            )
        );
    }

    private function isCLI()
    {
        return php_sapi_name() === 'cli';
    }

    private function isURL($url)
    {
        return filter_var($url, FILTER_VALIDATE_URL);
    }
}
