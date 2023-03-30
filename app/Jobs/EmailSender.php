<?php

namespace App\Jobs;

use Encore\Admin\Form\Field\Email;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailer;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;


class EmailSender implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $param;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(array $data)
    {
        $this->param = $data;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $params = $this->param;
        Log::info("get queue job", [$params]);
        $email = $params['email'] ?? '';
        $code = $params['code'] ?? '';
        $schoolId = $params['schoolId'] ?? 0;
        if (empty($email) || empty($code) || empty($schoolId)) {
            return;
        }
        $domain = env('DOMAIN');
        $url = "$domain/api/teacher/join?email=$email&code=$code&schoolId=$schoolId";
        var_dump($url);
        Mail::raw("复制连接，加入学校, 登录名为邮箱号，密码是: $code\n$url", function ($message) {
           $message->from('kqxianren@gmail.com');
           $message->subject("Edu System: invite you to join school");
           $message->to('zoueature@qq.com');
        });
    }
}
