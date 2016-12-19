<?php
namespace Home\Controller;
use Think\Controller;
use Think\Crypt;
class HrController extends Controller {

	public function showneedprovement() {
		$result = array(
			'isSuccess' => false,
			'vacation_info' => '');
		$token = I('token');
		$ans = M('employee')->where(array('employee_token' => $token))->find();
		if ($ans !== false && $ans > 0) {
			$employee_id = $ans['employee_id'];
			$sql = "SELECT * FROM `vacation`, vacation_type WHERE vacation_type=vacation_type_id AND vacation_submitprove != 2 AND employee_id = ".I('employee_id');
			$ans1 = M('vacation')->query($sql);
			if ($ans1) {
				foreach ($ans1 as $key => &$value) {
						$date['vacation_id'] = $value['vacation_id'];
                        $date['employee_name'] = $value['employee_name'];
                        $date['vacation_name'] = $value['vacation_name'];
                        $date['vacation_updatetime'] = $value['vacation_updatetime'];
                        $date['vacation_startdate'] = $value['vacation_startdate'];
                        $date['vacation_enddate'] = $value['vacation_enddate'];
                        $date['vacation_reason'] = $value['vacation_reason'];
                        $date['vacation_submitprove'] = $value['vacation_submitprove'];
                        $date['vacation_proveduetime'] = $value['vacation_proveduetime'];
                        $value = $date;
                    }
				$result['isSuccess'] = true;
				$result['vacation_info'] = $ans1;
			}
		}
		header("Access-Control-Allow-Origin:*");
		echo json_encode($result);
	}
	
	public function addEmployee () {
		$result = array(
			'isSuccess' => false,
			'error_message' => '' );
		$employee['employee_id'] = I('employee_id');
		$employee['employee_name'] = I('employee_name');
		$employee['employee_password'] = self::PASS(I('employee_password'));
		$employee['employee_gender'] = I('employee_gender');
		$employee['employee_birthday'] = I('employee_birthday');
		$employee['employee_entrydate'] = I('employee_entrydate');
		$employee['employee_department'] = I('employee_department');
		$employee['employee_path'] = I('employee_path');
		$token = I('token');
		$ans = M('employee')->where(array('employee_token' => $token))->find();

		if ($ans !== false && $ans > 0) {
			$employee_id = $ans['employee_id'];
			$employee_department = $ans['employee_department'];
			if($employee_department == '人事部') {
				$ans1 = M('employee')->add($employee);
				if ($ans1 !== false) {
					$result['isSuccess'] = true;
				}
				else {
					$result['error_message'] = '工号重复';
				}
			}
		}
		else {
			$result['error_message'] = '未登录';
		}

		header("Access-Control-Allow-Origin:*");
		echo json_encode($result);		
	}

	public function search () {
		$result = array(
			'isSuccess' => false,
			'find' => '');
		$token = I('token');
		$condition = I('condition');
		$ans = M('employee')->where(array('employee_token' => $token))->find();
		if ($ans !== false && $ans > 0) {
			$con1 = 'e.employee_id like '.'\'%'.$condition.'%\'';
			$con2 = 'e.employee_department like '.'\'%'.$condition.'%\'';
			$con3 = 'e.employee_name like '.'\'%'.$condition.'%\'';
			$sql = 'select e.employee_id, e.employee_name, e.employee_department from employee e where '.$con1.' or '.$con2.' or '.$con3;
			$ans = M('employee')->query($sql);
			if($ans) {
				$result['isSuccess'] = true;
				foreach ($ans as  &$value) {
					$sql = 'select * from vacation where employee_id = '.$value['employee_id'].' and vacation_startdate < curdate() and vacation_enddate > curdate()';
					$value['employee_atwork'] = true;
					$notAtWork = M('vacation')->query($sql);
					if ($notAtWork) {
						$value['employee_atwork'] = false;
					}
				}
			}
			$result['find'] = $ans;
			$result['haha'] = $sql;
		}
		header("Access-Control-Allow-Origin:*");
		echo json_encode($result);
	}

	public function allowProve () {
		$result = array(
			'isSuccess' => false );
		$token = I('token');
		$ans = M('employee')->where(array('employee_token' => $token))->find();
		$vacation_id = I('vacation_id');
		if ($ans !== false && $ans > 0) {
			$ans1 = M('vacation')->where(array('vacation_id' => $vacation_id,'vacation_submitprove' => 0))->save(array('vacation_submitprove' => 1));
			if ($ans1 !== false) {
				$result['isSuccess'] = true;
			}
		}
		header("Access-Control-Allow-Origin:*");
		echo json_encode($result);
	}
	
	public function extendduetime() {
		$result = array(
			'isSuccess' => false );
		$token = I('token');
		$ans = M('employee')->where(array('employee_token' => $token))->find();
		if ($ans !== false && $ans > 0) {
			$day_length = I('days');
			$vacation_id = I('vacation_id');
			$sql = "UPDATE `vacation` SET vacation_proveduetime = date_add(vacation_proveduetime, INTERVAL ".$day_length." day) WHERE vacation_id = ".$vacation_id;
			$ans1 = M('vacation')->execute($sql);
			if ($ans1) {
				$result['isSuccess'] = true;
			}
		}
		header("Access-Control-Allow-Origin:*");
		echo json_encode($result);		
	}

	private function PASS($str='',$de=false){
        $key=('qXs2PdAfKSnee60W');
        $char=('KoGQhtWVlHHw5CtK');
        if($str!=''){ 
            if($de){
                $str=$char.$str;
                $str=Crypt::decrypt($str,$key);
            }else{
                $str=Crypt::encrypt($str,$key);
                $str=str_replace($char, '',$str);
            }
        }
        return $str;
    }
}
