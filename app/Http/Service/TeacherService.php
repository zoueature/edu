<?php
// App\Http\Service/TeacherService
// author: zoueature
// eamil: zoueature@gmail.com
// ------------------------------------


namespace App\Http\Service;


use App\Http\Constant\CacheKey;
use App\Http\Constant\Errcode;
use App\Http\Constant\Role;
use App\Jobs\EmailSender;
use App\Teacher;
use App\UserSchoolRole;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Mime\Email;

class TeacherService extends Service
{
    /**
     * 获取老师
     * @param $teacherId
     * @return Teacher|null
     */
    public function getTeacher($teacherId) :?Teacher
    {
       try {
          return Teacher::findOrFail($teacherId);
       } catch (ModelNotFoundException $e) {
           Log::warning($e->getMessage(), [$teacherId]);
           $this->responseJson(Errcode::BAD_REQUEST);
           return null;
       }
    }

}