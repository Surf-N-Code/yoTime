<?php

namespace App\Tests\Security\Slack\EventListener;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;
use App\Entity\Slack\SlashCommand;
use App\Kernel;
use App\Security\Slack\EventListener\SecurityListener;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\Event\KernelEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class SecurityListenerTest extends ApiTestCase
{
    public function testSecurityListenerSlashCommand()
    {
        $data = [
            "team_id" => "THW253RMX",
            "team_domain" => "diltheymedia",
            "channel_id" => "GLH77MXNX",
            "channel_name" => "privategroup",
            "user_id" => "UHW253RU1",
            "user_name" => "ndilthey",
            "command" => "/work",
            "text" => "",
            "api_app_id" => "ALTNUDXE0",
            "response_url" => "",
            "trigger_id" => "1376434055859.608073127745.6b6bf1eec2610d38762c05c6f1decc7e",
        ];

        $response = static::createClient()->request(
            'POST',
            '/slack/slashcommand',
            [
                'json' => $data,
                'headers' => [
                    'x-slack-request-timestamp' => 1600676620,
                    'x-slack-signature' => 'v0=27e5426d075e6d68333fb2aaae9794310a2bde8eee2c551a16828b7eadb3da10',
                    'content-type' => 'application/x-www-form-urlencoded',
                    'accept' => 'application/json'
                ],
                'base_uri' => 'https://localhost:8443'
            ]
        );
        self::assertEquals(201, $response->getStatusCode());
    }

    public function testSecurityListenerSlashCommandInvalidSignature()
    {
        $data = [
            "team_id" => "something",
        ];

        $response = static::createClient()->request(
            'POST',
            '/slack/slashcommand',
            [
                'json' => $data,
                'headers' => [
                    'x-slack-request-timestamp' => 1600676620,
                    'x-slack-signature' => 'v0=27e5426d075e6d68333fb2aaae9794310a2bde8eee2c551a16828b7eadb3da10',
                    'content-type' => 'application/x-www-form-urlencoded',
                    'accept' => 'application/json'
                ],
                'base_uri' => 'https://localhost:8443'
            ]
        );
        self::assertEquals(401, $response->getStatusCode());
    }

    public function testSecurityListenerBotCommand()
    {
        $data = [
            "type" => "url_verification",
            "token" => "QbBtIJowqMvCl1NcYexCG7rN",
            "challenge" => "DxUwifpaOL5VikG3y0eFOr9k3fHj0A7cKNR7eguIrd8KIM9oVzsP"
        ];

        $response = static::createClient()->request(
            'POST',
            '/slack/event/bot',
            [
                'json' => $data,
                'headers' => [
                    'x-slack-request-timestamp' => 1600676620,
                    'x-slack-signature' => 'v0=23f5d7464a7e86ee76c37c3f71979a6e940bef2dece88996d1d3b684bd340991',
                    'content-type' => 'application/x-www-form-urlencoded',
                    'accept' => 'application/json'
                ],
                'base_uri' => 'https://localhost:8443'
            ]
        );
        self::assertEquals(200, $response->getStatusCode());
    }

    public function testSecurityListenerBotCommandInvalidSignature()
    {
        $data = [
            "type" => "url_verification",
            "challenge" => "DxUwifpaOL5VikG3y0eFOr9k3fHj0A7cKNR7eguIrd8KIM9oVzsP"
        ];

        $response = static::createClient()->request(
            'POST',
            '/slack/event/bot',
            [
                'json' => $data,
                'headers' => [
                    'x-slack-request-timestamp' => 1600676620,
                    'x-slack-signature' => 'v0=23f5d7464a7e86ee76c37c3f71979a6e940bef2dece88996d1d3b684bd340991',
                    'content-type' => 'application/x-www-form-urlencoded',
                    'accept' => 'application/json'
                ],
                'base_uri' => 'https://localhost:8443'
            ]
        );
        self::assertEquals(401, $response->getStatusCode());
    }

    public function testSecurityListenerInteractionEvent()
    {
        $response = static::createClient()->request(
            'POST',
            '/slack/event/interaction',
            [
                'json' => ['payload' => '{"type":"view_submission","team":{"id":"THW253RMX","domain":"diltheymedia"},"user":{"id":"UHW253RU1","username":"ndilthey","name":"ndilthey","team_id":"THW253RMX"},"api_app_id":"ALTNUDXE0","token":"QbBtIJowqMvCl1NcYexCG7rN","trigger_id":"1359827201715.608073127745.2f087f09e939a0cc814be02608b7dc6c","view":{"id":"V01AKJ7HBSN","team_id":"THW253RMX","type":"modal","blocks":[{"type":"input","block_id":"daily_summary_block","label":{"type":"plain_text","text":"Tasks","emoji":true},"optional":false,"element":{"type":"plain_text_input","action_id":"summary_block_input","placeholder":{"type":"plain_text","text":"Add the tasks your completed here...","emoji":true},"multiline":true}},{"type":"input","block_id":"mail_block","label":{"type":"plain_text","text":"Send E-Mail?","emoji":true},"optional":false,"element":{"type":"static_select","action_id":"mail_choice","placeholder":{"type":"plain_text","text":"Send E-Mail?","emoji":true},"initial_option":{"text":{"type":"plain_text","text":":heavy_check_mark: yes","emoji":true},"value":"true"},"options":[{"text":{"type":"plain_text","text":":heavy_check_mark: yes","emoji":true},"value":"true"},{"text":{"type":"plain_text","text":":x: no","emoji":true},"value":"false"}]}}],"private_metadata":"","callback_id":"ml_ds","state":{"values":{"daily_summary_block":{"summary_block_input":{"type":"plain_text_input","value":"sdf"}},"mail_block":{"mail_choice":{"type":"static_select","selected_option":{"text":{"type":"plain_text","text":":heavy_check_mark: yes","emoji":true},"value":"true"}}}}},"hash":"1599938164.Fi7N1PWX","title":{"type":"plain_text","text":"Daily Summary","emoji":true},"clear_on_close":false,"notify_on_close":false,"close":null,"submit":{"type":"plain_text","text":"Send","emoji":true},"previous_view_id":null,"root_view_id":"V01AKJ7HBSN","app_id":"ALTNUDXE0","external_id":"","app_installed_team_id":"THW253RMX","bot_id":"BLU73PDGQ"},"response_urls":[]}'],
                'headers' => [
                    'x-slack-request-timestamp' => 1600676620,
                    'x-slack-signature' => 'v0=f4318d6cb5d50689f80b4aecbb3a8a3ee0ed827cfe3cdab593aeb1143550b836',
                    'content-type' => 'application/json',
                    'accept' => 'application/json'
                ],
                'base_uri' => 'https://localhost:8443'
            ]
        );
        self::assertEquals(200, $response->getStatusCode());
    }

    public function testSecurityListenerInteractionEventInvalidSignature()
    {
        $response = static::createClient()->request(
            'POST',
            '/slack/event/interaction',
            [
                'json' => ['payload' => '{"type":"view_submission","team":{"id":"lkj","domain":"diltheymedia"},"user":{"id":"UHW253RU1","username":"ndilthey","name":"ndilthey","team_id":"THW253RMX"},"api_app_id":"ALTNUDXE0","token":"QbBtIJowqMvCl1NcYexCG7rN","trigger_id":"1359827201715.608073127745.2f087f09e939a0cc814be02608b7dc6c","view":{"id":"V01AKJ7HBSN","team_id":"THW253RMX","type":"modal","blocks":[{"type":"input","block_id":"daily_summary_block","label":{"type":"plain_text","text":"Tasks","emoji":true},"optional":false,"element":{"type":"plain_text_input","action_id":"summary_block_input","placeholder":{"type":"plain_text","text":"Add the tasks your completed here...","emoji":true},"multiline":true}},{"type":"input","block_id":"mail_block","label":{"type":"plain_text","text":"Send E-Mail?","emoji":true},"optional":false,"element":{"type":"static_select","action_id":"mail_choice","placeholder":{"type":"plain_text","text":"Send E-Mail?","emoji":true},"initial_option":{"text":{"type":"plain_text","text":":heavy_check_mark: yes","emoji":true},"value":"true"},"options":[{"text":{"type":"plain_text","text":":heavy_check_mark: yes","emoji":true},"value":"true"},{"text":{"type":"plain_text","text":":x: no","emoji":true},"value":"false"}]}}],"private_metadata":"","callback_id":"ml_ds","state":{"values":{"daily_summary_block":{"summary_block_input":{"type":"plain_text_input","value":"sdf"}},"mail_block":{"mail_choice":{"type":"static_select","selected_option":{"text":{"type":"plain_text","text":":heavy_check_mark: yes","emoji":true},"value":"true"}}}}},"hash":"1599938164.Fi7N1PWX","title":{"type":"plain_text","text":"Daily Summary","emoji":true},"clear_on_close":false,"notify_on_close":false,"close":null,"submit":{"type":"plain_text","text":"Send","emoji":true},"previous_view_id":null,"root_view_id":"V01AKJ7HBSN","app_id":"ALTNUDXE0","external_id":"","app_installed_team_id":"THW253RMX","bot_id":"BLU73PDGQ"},"response_urls":[]}'],
                'headers' => [
                    'x-slack-request-timestamp' => 1600676620,
                    'x-slack-signature' => 'v0=f4318d6cb5d50689f80b4aecbb3a8a3ee0ed827cfe3cdab593aeb1143550b836',
                    'content-type' => 'application/json',
                    'accept' => 'application/json'
                ],
                'base_uri' => 'https://localhost:8443'
            ]
        );
        self::assertEquals(401, $response->getStatusCode());
    }
}
