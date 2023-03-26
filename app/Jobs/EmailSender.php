<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;

class EmailSender implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $param;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $params = $this->param;
        Log::info("get queue job", $params);
        $email = $params['email'] ?? '';
        $code = $params['code'] ?? '';
        $schoolId = $params['schoolId'] ?? 0;
        if (empty($email) || empty($code) || empty($schoolId)) {
            return;
        }
        $transport = Transport::fromDsn(env('MAIL'));
        $mailer = new Mailer($transport);
        $mail = (new Email())->from('kqxianren@gmail.com')
            ->to($email)
            ->subject("Edu System: invite you to join school")
            ->html("<p>Hello: $email</p>
        <p>你的邀请码是: <span style='color: #31b77a; font-size: 25px;'>$code</span>. 此邀请码在 60 分钟内有效.</p>
        <a href='/api/teacher/join?email=$email&code=$code&schoolId=$schoolId'>可以点击此处加入</a>
        <p>邀请码为你的初始密码， 登陆后请及时修改</p>
        <p>Eayang Team</p>");
        try {
            $mailer->send($mail);
        } catch (TransportExceptionInterface $e) {
            Log::error('send invite email error '.$e->getMessage());
            return  false;
        }
    }
}
