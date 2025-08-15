<?php

namespace App\Controllers;

use App\Helpers\MailSender;
use App\Models\BlogPost;
use App\Models\Message;
use App\Models\SiteStat;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Views\Twig;
use Valitron\Validator;

class SiteController
{
    public function index(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $view = Twig::fromRequest($request);

        return $view->render(
            $response,
            'admin/dashboard.twig',
            [
                'title' => 'Dashboard',
                'message' => 'Welcome to the admin dashboard!',
                'visitors' => SiteStat::all()->count(),
                'posts' => BlogPost::all()->count(),
                'messages' => Message::all()->count(),
            ]
        );
    }

    /**
     * Handle message submission from contact form
     */
    public function saveMessage(ServerRequestInterface $request, ResponseInterface $response)
    {
        $data = $request->getParsedBody() ?? [];

        // Validation
        $v = new Validator($data);
        $v->rule('required', ['name', 'email', 'message']);
        $v->rule('email', 'email');
        $v->rule('lengthMax', 'name', 100);
        $v->rule('lengthMax', 'email', 100);
        $v->rule('lengthMax', 'subject', 200);

        if (!$v->validate()) {
            return [
                'success' => false,
                'errors' => $v->errors()
            ];
        }

        try {
            // Save message
            $message = Message::create([
                'name'        => $data['name'],
                'email'       => $data['email'],
                'subject'     => $data['subject'] ?? null,
                'message'     => $data['message'],
                'ip_address'  => $request->getServerParams()['REMOTE_ADDR'] ?? null,
                'user_agent'  => $request->getHeaderLine('User-Agent'),
            ]);

            // Optional: Send email notification
            $this->sendNewMessageNotification($message);

            return json_encode([
                'success' => true,
                'message' => 'Thank you! Your message has been sent.'
            ]);
        } catch (\Exception $e) {
            // Optionally log: $this->logger->error('Message save error: ' . $e->getMessage());

            return json_encode([
                'success' => false,
                'message' => 'Sorry, something went wrong. Please try again later.'
            ]);
        }
    }


    /**
     * Send email notification about new message
     */
    protected function sendNewMessageNotification(Message $message): void
    {
        $body = $this->buildNewMessageBody($message);

        $mailer = new MailSender();
        $mailer->send(
            $_ENV['ADMIN_EMAIL'], // Primary recipient
            'New Message Received: ' . ($message->subject ?? 'No Subject'),
            $body,
            explode(',', $_ENV['EMAIL_CC'] ?? ''),  // CC (comma-separated string to array)
            explode(',', $_ENV['EMAIL_BCC'] ?? '')  // BCC
        );
    }

    /**
     * Generate beautiful HTML email body for a new message notification
     */
    private function buildNewMessageBody(Message $message): string
    {
        $subject = htmlspecialchars($message->subject ?? 'No Subject');
        $sender = htmlspecialchars($message->sender_name ?? 'Unknown Sender');
        $content = nl2br(htmlspecialchars($message->content ?? ''));

        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Message Notification</title>
    <style type="text/css">
        /* Base styles */
        body {
            font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            line-height: 1.6;
            color: #333333;
            background-color: #f7f7f7;
            margin: 0;
            padding: 0;
        }
        
        /* Email container */
        .email-container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        
        /* Header */
        .email-header {
            background-color: #4a6fa5;
            color: white;
            padding: 25px;
            text-align: center;
            border-radius: 5px 5px 0 0;
        }
        
        .email-header h1 {
            margin: 0;
            font-size: 24px;
        }
        
        /* Content card */
        .email-content {
            background-color: #ffffff;
            padding: 30px;
            border-radius: 0 0 5px 5px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        /* Message details */
        .message-detail {
            margin-bottom: 20px;
            padding-bottom: 20px;
            border-bottom: 1px solid #eeeeee;
        }
        
        .message-detail:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }
        
        .detail-label {
            font-weight: 600;
            color: #4a6fa5;
            display: block;
            margin-bottom: 5px;
        }
        
        /* Message content */
        .message-content {
            background-color: #f9f9f9;
            padding: 15px;
            border-radius: 4px;
            border-left: 3px solid #4a6fa5;
        }
        
        /* Footer */
        .email-footer {
            text-align: center;
            padding: 20px;
            color: #999999;
            font-size: 12px;
        }
        
        /* Responsive adjustments */
        @media screen and (max-width: 480px) {
            .email-container {
                padding: 10px;
            }
            
            .email-content {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="email-header">
            <h1>You've Received a New Message</h1>
        </div>
        
        <div class="email-content">
            <div class="message-detail">
                <span class="detail-label">Subject</span>
                <div>{$subject}</div>
            </div>
            
            <div class="message-detail">
                <span class="detail-label">From</span>
                <div>{$sender}</div>
            </div>
            
            <div class="message-detail">
                <span class="detail-label">Message</span>
                <div class="message-content">{$content}</div>
            </div>
        </div>
        
        <div class="email-footer">
            <p>This message was sent to you via 4ly.me messaging system</p>
            <p>&copy; " . date('Y') . " 4ly.me. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
HTML;
    }
    
    /**
     * Handle newsletter subscription
     */
    public function subscribe(ServerRequestInterface $request, ResponseInterface $response)
    {
        $data = $request->getParsedBody() ?? [];
        
        // For now, just return a success response
        // In a real application, you would save the email to a database
        $response->getBody()->write(json_encode([
            'success' => true,
            'message' => 'Thank you for subscribing to our newsletter!'
        ]));
        
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(200);
    }
    
    /**
     * Handle contact form submission
     */
    public function saveContact(ServerRequestInterface $request, ResponseInterface $response)
    {
        return $this->saveMessage($request, $response);
    }
}
