<?php

namespace App\Providers;

use Log;
use Config;
use Illuminate\Support\ServiceProvider;

class SimsServiceProvider extends ServiceProvider
{
    private static $oauth_ps = null;
    private static $oauth_js = null;
    private static $seme = null;
    private static $error = '';

    public function __construct()
    {
        if (is_null(self::$oauth_ps))
            self::$oauth_ps = new \GuzzleHttp\Client([
                'verify' => false,
                'base_uri' => Config::get('sims.ps.base_uri'),
            ]);
        self::$seme = $this->seme();
    }

    public function ps_send($url)
    {
        //AES-128-CBC
/*        $p = md5(Config::get('sims.ps.oauth_secret'), true);
        $m = 'aes-128-cbc';
        $iv = md5(Config::get('sims.ps.aes_iv') . date('YmdH'), true);
        $e = base64_encode(openssl_encrypt($url, $m, $p, OPENSSL_ZERO_PADDING, $iv));

        $response = self::$oauth_ps->request('GET', $url, [
            'headers' => [
                'Authorization' => 'Special key '.Config::get('sims.ps.oauth_id'),
                'SpecialVerify' => $e,
                'Accept' => 'application/json',
            ],
            'http_errors' => false,
        ]);
*/
        $response = self::$oauth_ps->request('GET', $url, [
            'headers' => [
                'Authorization' => 'Special ip '.Config::get('sims.ps.oauth_id'),
                'Accept' => 'application/json',
            ],
            'http_errors' => false,
        ]);
        return $response;
    }

    public function ps_error()
    {
        return self::$error;
    }

    public function ps_call($info, array $replacement)
    {
        if (!is_array($replacement)) return;
        $search = array();
        $values = array();
        foreach ($replacement as $key => $data) {
            $search[] = '{'.$key.'}';
            $values[] = $data;
        }
        $search[] = "{seme}";
        $values[] = self::$seme;
        $url = str_replace($search, $values, Config::get("sims.ps.$info"));
        $res = $this->ps_send($url);
        $json = json_decode((string) $res->getBody());
        if (isset($json->status) && $json->status == 'ok') {
            return $json->list;
        } else {
            self::$error = $json->error;
            if (Config::get('sims.ps.debug')) Log::debug('Oauth call:'.$url.' failed! Server response:'.$res->getBody());
            return false;
        }
    }

    public function getClasses($sid)
    {
        if (empty($sid)) return false;
        function cmp($a, $b) { return ($a->clsid < $b->clsid) ? -1 : 1; }
        $classes = $this->ps_call('classes_info', ["sid" => $sid]);
        $classes = usort($classes, 'cmp' );
        return $classes;
    }

    public function getSubjects($sid)
    {
        $subjects = array();
        $classes = $this->getClasses($sid);
		foreach ($classes as $class) {
			$data = $this->ps_call('subject_for_class', [ 'sid' => $sid, 'clsid' => $class->clsid ]);
			if (isset($data[0]->subjects)) {
				$class_subjects = $data[0]->subjects;
				foreach ($class_subjects as $subj) {
					$subj_name = array_keys((array)$subj)[0];
					if (!in_array($subj_name, $subjects)) $subjects[] = $subj_name;
				}
			} else {
                return false;
            }
        }
        return $subjects;
    }

    public function getTeachers($sid)
    {
        if (empty($sid)) return false;
        $teachers = $this->ps_call('teachers_info', ["sid" => $sid]);
        return $teachers;
    }

    public function getStudents($sid, $class = '')
    {
        if (empty($sid)) return false;
        $students = array();
        if (empty($class)) {
            $classes = $this->getClasses($sid);
            $classes = array_map(function($c) { return $c->clsid; }, $classes);
        } else {
            $classes[] = $class;
        }
        foreach ($classes as $c) {
            $stu = $this->ps_call('students_in_class', ["sid" => $sid, "clsid" => $c]);
            usleep(100);
            $students = array_merge($students, $stu[0]->students);
        }
        return $students;
    }

    public function getStudent($sid, $stdno)
    {
        if (empty($sid) || empty($stdno)) return false;
        $data1 = $this->ps_call('student_info', [ 'sid' => $sid, 'stdno' => $stdno ]);
        $data2 = $this->ps_call('student_detail', [ 'sid' => $sid, 'stdno' => $stdno ]);
        return array_merge((array)$data1[0], (array)$data2[0]);
    }

    private function seme() {
        if (date('m') > 7) {
          $year = date('Y') - 1911;
          $seme = 1;
        }
        elseif (date('m') < 2) {
          $year = date('Y') - 1912;
          $seme = 1;
        }
        else {
          $year = date('Y') - 1912;
          $seme = 2;
        }
        return $year.$seme;
    }
};
