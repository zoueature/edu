<?php
// App\Http\Service/AdminService
// author: zoueature
// eamil: zoueature@gmail.com
// ------------------------------------


namespace App\Http\Service;


use App\Http\Constant\CacheKey;
use App\Http\Constant\Role;
use App\Jobs\EmailSender;
use App\SchoolTeacher;
use App\Student;
use App\Teacher;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AdminService extends Service
{
    /**
     * 生成邀请码
     * @return string
     */
    private function generateInviteCode() :string
    {
        $code = '';
        for ($i = 0; $i < 8; $i ++) {
            $code .= rand(0, 9);
        }
        return $code;
    }

    /**
     * 分发发送邮件队列
     * @param $email
     * @param $schoolId
     * @return bool
     */
    public function sendInviteEmail($email, $schoolId) :bool
    {
        $code = $this->generateInviteCode();
        Cache::put(CacheKey::INVITE_CODE_PREFIX.$email, $code, CacheKey::INVITE_CODE_TTL);
        $params = [
            'code' => $code,
            'email' => $email,
            'schoolId' => $schoolId,
        ];
        EmailSender::dispatch($params);
        return  true;
    }

    /**
     * 检查邀请码是否有效
     * @param $email
     * @param $code
     * @return bool
     */
    public function checkCodeValid($email, $code): bool
    {
        $storeCode = Cache::get(CacheKey::INVITE_CODE_PREFIX.$email);
        return $storeCode == $code;
    }

    /**
     * 创新新老师
     * @param $email
     * @param $code
     * @param $schoolId
     * @return bool
     */
    public function createNewTeacher($email, $code, $schoolId) :bool
    {
        $teacher = app(Teacher::class);
        $teacher->email = $email;
        // 邀请码为初始密码
        $teacher->password = bcrypt($code);
        $teacher->name = explode('@', $email)[0];
        $schoolTeacher = app(SchoolTeacher::class);
        $schoolTeacher->school_id = $schoolId;
        $schoolTeacher->role = Role::SCHOOL_ROLE_TEACHER;
        try {
            DB::transaction(function () use ($teacher, $schoolTeacher){
                $teacher->save();
                $schoolTeacher->teacher_id = $teacher->id;
                $schoolTeacher->save();
            });
        } catch (\Exception $e) {
            Log::error('create teacher error : ' . $e->getMessage());
            return false;
        }
        // 删除邀请码缓存
        Cache::delete(CacheKey::INVITE_CODE_PREFIX.$email);
        return true;
    }

    /**
     * 学生创建
     * @param $username
     * @param $password
     * @param $schoolId
     * @return bool
     */
    public function createStudent($username, $password, $schoolId) :bool
    {
        $student = new Student;

        $student->username = $username;
        $student->password = bcrypt($password);
        $student->school_id = $schoolId;
        $ok = $student->save();
        return $ok;
    }

}