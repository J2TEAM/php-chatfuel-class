<?php

require __DIR__.'/../vendor/autoload.php';

use PHPUnit\Framework\TestCase;
use juno_okyo\Chatfuel;

class ChatfuelTest extends TestCase
{
    private $chatfuel;

    protected function setUp()
    {
        $this->chatfuel = new Chatfuel(true);
    }

    public function testSendTextException()
    {
        $this->expectException(\Exception::class);

        $this->chatfuel->sendText();
    }

    public function testSendTextError()
    {
        $this->assertSame(
            ['text' => 'Error!'],
            $this->chatfuel->sendText(1)
        );
    }

    public function testSendText()
    {
        $this->assertSame(
            ['text' => 'test'],
            $this->chatfuel->sendText('test')
        );
        $this->assertSame(
            [['text' => 'test 1'], ['text' => 'test 2']],
            $this->chatfuel->sendText(['test 1', 'test 2'])
        );
    }

    public function testSendAttachmentError()
    {
        $url = 'this-is-an-invalid-url.test.org/data.ext';

        $this->assertSame(
            ['text' => 'Error: Invalid URL!'],
            $this->chatfuel->sendImage($url)
        );
        $this->assertSame(
            ['text' => 'Error: Invalid URL!'],
            $this->chatfuel->sendAudio($url)
        );
        $this->assertSame(
            ['text' => 'Error: Invalid URL!'],
            $this->chatfuel->sendVideo($url)
        );
        $this->assertNull(
            $this->chatfuel->sendTextCard('test', null)
        );
        $this->assertNull(
            $this->chatfuel->sendGallery(null)
        );
    }

    public function testSendAttachment()
    {
        $url = 'http://this-is-a-valid-url.test.org/data.ext';

        $this->assertSame(
            ['attachment' => ['type' => 'image', 'payload' => ['url' => $url]]],
            $this->chatfuel->sendImage($url)
        );
        $this->assertSame(
            ['attachment' => ['type' => 'audio', 'payload' => ['url' => $url]]],
            $this->chatfuel->sendAudio($url)
        );
        $this->assertSame(
            ['attachment' => ['type' => 'video', 'payload' => ['url' => $url]]],
            $this->chatfuel->sendVideo($url)
        );
        $this->assertSame(
            ['attachment' => ['type' => 'template', 'payload' => [
                'template_type' => 'button',
                'text' => 'test',
                'buttons' => []
            ]]],
            $this->chatfuel->sendTextCard('test', [])
        );
        $this->assertSame(
            ['attachment' => ['type' => 'template', 'payload' => [
                'template_type' => 'generic',
                'elements' => []
            ]]],
            $this->chatfuel->sendGallery([])
        );
    }

    public function testCreateElementError()
    {
        $invalidUrl = 'this-is-an-invalid-url.test.org/data.ext';
        $validUrl = 'http://this-is-a-valid-url.test.org/data.ext';

        $this->assertNull(
            $this->chatfuel->createElement('test title', $invalidUrl, 'test subtitle', [])
        );
        $this->assertNull(
            $this->chatfuel->createElement('test title', $validUrl, 'test subtitle', null)
        );
        $this->assertNull(
            $this->chatfuel->createElement('test title', $invalidUrl, 'test subtitle', null)
        );
    }

    public function testCreateElement()
    {
        $url = 'http://this-is-a-valid-url.test.org/data.ext';

        $this->assertSame(
            [
                'title' => 'test',
                'image_url' => $url,
                'subtitle' => 'test subtitle',
                'buttons' => []
            ],
            $this->chatfuel->createElement('test', $url, 'test subtitle', [])
        );
    }

    public function testCreateElementButtonError()
    {
        $url = 'this-is-an-invalid-url.test.org/data.ext';

        $this->assertNull(
            $this->chatfuel->createButtonToURL('test', $url)
        );

        $this->assertNull(
            $this->chatfuel->createPostBackButton('test', $url)
        );
    }

    public function testCreateElementButton()
    {
        $title = 'test';
        $url = 'http://this-is-a-valid-url.test.org/data.ext';

        $this->assertSame(
            [
                'type' => 'show_block',
                'title' => $title,
                'block_names' => []
            ],
            $this->chatfuel->createButtonToBlock($title, [])
        );
        $this->assertSame(
            [
                'type' => 'show_block',
                'title' => $title,
                'block_name' => 'block'
            ],
            $this->chatfuel->createButtonToBlock($title, 'block')
        );
        $this->assertSame(
            [
                'type' => 'show_block',
                'title' => $title,
                'block_names' => [],
                'set_attributes' => []
            ],
            $this->chatfuel->createButtonToBlock($title, [], [])
        );

        $this->assertSame(
            [
                'type' => 'web_url',
                'url' => $url,
                'title' => $title,
            ],
            $this->chatfuel->createButtonToURL($title, $url)
        );
        $this->assertSame(
            [
                'type' => 'web_url',
                'url' => $url,
                'title' => $title,
                'set_attributes' => []
            ],
            $this->chatfuel->createButtonToURL($title, $url, [])
        );

        $this->assertSame(
            [
                'url' => $url,
                'type' => 'json_plugin_url',
                'title' => $title
            ],
            $this->chatfuel->createPostBackButton($title, $url)
        );

        $this->assertSame(
            [
                'type' => 'phone_number',
                'phone_number' => 01234567890,
                'title' => 'Call'
            ],
            $this->chatfuel->createCallButton(01234567890)
        );
        $this->assertSame(
            [
                'type' => 'phone_number',
                'phone_number' => 01234567890,
                'title' => $title
            ],
            $this->chatfuel->createCallButton(01234567890, $title)
        );

        $this->assertSame(
            ['type' => 'element_share'],
            $this->chatfuel->createShareButton()
        );
    }

    public function testCreateQuickReplyError()
    {
        $this->assertNull(
            $this->chatfuel->createQuickReply('test', null)
        );
    }

    public function testCreateQuickReply()
    {
        $this->assertSame(
            [
                'text' => 'test',
                'quick_replies' => []
            ],
            $this->chatfuel->createQuickReply('test', [])
        );
    }

    public function testCreateQuickReplyButton()
    {
        $title = 'test';

        $this->assertSame(
            [
                'title' => $title,
                'block_names' => []
            ],
            $this->chatfuel->createQuickReplyButton($title, [])
        );
        $this->assertSame(
            [
                'title' => $title,
                'block_name' => 'block'
            ],
            $this->chatfuel->createQuickReplyButton($title, 'block')
        );
    }
}
