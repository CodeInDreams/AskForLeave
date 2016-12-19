<?php
namespace Home\Controller;
use Think\Controller;
use Think\Crypt;
//提供的api 登录 注销 显示用户信息 显示用户的假期信息
class IndexController extends Controller {

    /*public function search () {
        $result = array(
            'isSuccess' => false,
            'find' => '');
        $token = I('token');
        $condition = I('condition');
        $ans = M('employee')->where(array('employee_token' => $token))->find();
        if ($ans !== false && $ans > 0) {
            $con1 = 'e.employee_id = '.'\''.$condition.'\'';
            $con2 = 'e.employee_department = '.'\''.$condition.'\'';
            $con3 = 'e.employee_name = '.'\''.$condition.'\'';
            $sql = 'select e.employee_id, e.employee_name, e.employee_department from employee e where '.$con1.' or '.$con2.' or '.$con3;
            $ans1 = M('employee')->query($sql);
            $result['isSuccess'] = true;
            if($ans1) {
                foreach ($ans1 as &$value) {
                    $sql = 'select * from vacation where employee_id = '.$value['employee_id'].' and vacation_startdate < curdate() and vacation_enddate > curdate()';
                    $value['employee_atwork'] = true;
                    $notAtWork = M('vacation')->query($sql);
                    if ($notAtWork) {
                        $value['employee_atwork'] = false;
                    }
                }
            }
            $result['find'] = $ans1;
        }
        header("Access-Control-Allow-Origin:*");
        echo json_encode($result);
    }*/
	
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
        }
        header("Access-Control-Allow-Origin:*");
        echo json_encode($result);
    }

    public function verifycode () {

        import('ORG.Util.Image');
        Image::buildImageVerify(1, 1, 'png');
    }
    
    public function showvacationtype () {
        $result = array(
            'isSuccess' => false,
            'vacation_type' => '' );
        $token = I('token');
        $ans = M('employee')->where(array('employee_token' => $token))->find();
        if($ans !== false && $ans > 0) {
            $result['isSuccess'] = true;
            $employee_gender = $ans['employee_gender'];
            $result['vacation_type'] = M('vacation_type')->select();
            if ($employee_gender == 0) {
                unset($result['vacation_type'][1]);
            }
            else {
                unset($result['vacation_type'][2]);
            }
        }
        header("Access-Control-Allow-Origin:*");
        echo json_encode($result);
    }

    public function login () {
        $result = array(
            'isSuccess' => false,
            'token' => ''
        );
        $userInfo = M('employee');
        $condition['employee_id'] = I('employee_id');
        $condition['employee_password'] = self::PASS(I('employee_password'));
        $ans = $userInfo -> where($condition) -> find();
        if($ans !== false && $ans > 0) {            
            $token = md5(time()).mt_rand();
            $ans1 = M('employee')->where(array('employee_id' => I('employee_id')))->save(array('employee_token' => $token ));
            if($ans1 !== false) {
                $result['isSuccess'] = true;
                $result['token'] = $token;
            }
        }
        header("Access-Control-Allow-Origin:*");
        echo json_encode($result);  
    }

    public function logout () {
        $result = array(
            'isSuccess' => false
        );
        $token = I('token');
        $ans = M('employee')->where(array('employee_token' => $token))->save(array('employee_token' => 'nothing'));
        if ($ans !== false) {
            $result['isSuccess'] = true;    
        }
        header("Access-Control-Allow-Origin:*");
        echo json_encode($result);
    }

    public function showEmployee() {
        $result = array(
            'isSuccess' => false,
            'employee_info' => '',
            'isManager' => false
        );
        $token = I('token');
        $ans = M('employee')->where(array('employee_token' => $token))->find();
        if ($ans !== false && $ans > 0)
        {
            $employee_id = $ans['employee_id'];
            $result['employee_info'] = $ans;
            $result['isSuccess'] = true;
            $employee_path = $ans['employee_path'];
            $sql = 'select * from employee where employee_path regexp '.'\''.$employee_path.'.+'.'\'';
            $isManager = M('employee')->query($sql);
            if ($isManager) {
                $result['isManager'] = true;
            }
            $employee_department = $ans['employee_department'];
            if ($employee_department == '人事部') {
                $result['isHr'] = true;
            }
            $employee_entrydate = $ans['employee_entrydate'];
            $current = strtotime(date("Y-m-d",time()));
            $entry_length = floor(floor((time() - strtotime($employee_entrydate))/86400)/365);//入职年数
            $should_have_year_vacation = 0;
            if ($entry_length < 3) {
                $should_have_year_vacation = 10;
            }
            else {
                $should_have_year_vacation = 20;
            }
            $result['remainYearVacation'] = $should_have_year_vacation - $employee['employee_used_yearvacation'];
        }        
		header("Access-Control-Allow-Origin:*");
		echo json_encode($result);        
    }

    public function showVacation() {
        $result = array(
            'isSuccess' => false,
            'vacation_info' => '' );
        $token = I('token');
        $ans = M('employee')->where(array('employee_token' => $token))->find();//这里记得改回来
        if ($ans !== false && $ans > 0){
			$employee_id = $ans["employee_id"];
			$employee = M('employee')->where(array('employee_id' => $employee_id))->find();
			$vacation = M('vacation')->join('vacation_type on vacation.vacation_type = vacation_type.vacation_type_id')->where(array('vacation.employee_id' => $employee_id))->order('vacation.vacation_updatetime desc')->select();
			if ($vacation !== false) {
				foreach ($vacation as $key => &$value) {
					getvacationstatus('vacation_approver1',$value);
					getvacationstatus('vacation_approver2',$value);
					getvacationstatus('vacation_approver3',$value);
					
					if($value['vacation_approver1_status'] == '审核中' || $value['vacation_approver1_status'] == '不同意') {
						$value['vacation_approver2_status'] = '';
						$value['vacation_approver3_status'] = '';
					}
					else {
						if ($value['vacation_approver2_status'] == '审核中' || $value['vacation_approver2_status'] == '不同意') {
							$value['vacation_approver3_status'] = '';
						}
					}
					$value['vacation_processing'] = array($value['vacation_approver1_status'],$value['vacation_approver2_status'],$value['vacation_approver3_status']);

					if ($value['vacation_iscanceled'] == 1) {
						$value['vacation_status']['permit_status'] = '已取消';
						$value['vacation_status']['prove_status'] = '';
					}
					else {
						if ($value['vacation_approver1'] < 0 || $value['vacation_approver2'] < 0 || $value['vacation_approver3'] < 0) {
							$value['vacation_status']['permit_status'] = '已拒绝';
						}
						elseif ($value['vacation_approver1'] > 0 && $value['vacation_approver2'] > 0 && $value['vacation_approver3'] > 0) {
							$value['vacation_status']['permit_status'] = '已通过';
						}
						else {
							$value['vacation_status']['permit_status'] = '审核中';

						}
						if ($value['vacation_submitprove'] == 0) {
							$value['vacation_status']['prove_status'] = '材料未提交';
						}
						elseif ($value['vacation_submitprove'] == 1) {
							$value['vacation_status']['prove_status'] = '材料已提交';

						}
						else {
							$value['vacation_status']['prove_status'] = '';
						}
					}

					$data['vacation_id'] = $value['vacation_id'];
					$data['vacation_type'] = $value['vacation_type'];
					$data['vacation_reason'] = $value['vacation_reason'];
					$data['vacation_name'] = $value['vacation_name'];
					$data['vacation_updatetime'] = $value['vacation_updatetime'];
					$data['vacation_processing'] = $value['vacation_processing'];
					$data['vacation_status'] = $value['vacation_status'];
					$data['vacation_startdate'] = $value['vacation_startdate'];
					$data['vacation_enddate'] = $value['vacation_enddate'];
					$data['vacation_proveduetime'] = $value['vacation_proveduetime'];
					$data['vacation_iscanceled'] = $value['vacation_iscanceled'];
					$value = $data;

				}
				$result['isSuccess'] = true;
				$result['vacation_info'] = $vacation;
			}
		}
		header("Access-Control-Allow-Origin:*");
		echo json_encode($result);
    }

    function getvacationtype($id) {
        return M('vacation_type')->where(array('vacation_type_id' => $id))->find()['vacation_name'];
    }
	
    function cancelVacation() {
        $result = array(
            'isSuccess' => false );
        $token = I('token');
        $ans = M('employee')->where(array('employee_token' => $token))->find();
        //if ($and !== false && $ans > 0) {
            $employee_id = $ans['employee_id'];
            $id = I('vacation_id');
            $ans = M('vacation')->where(array('vacation_id' => $id))->save(array('vacation_iscanceled' => 1));
            if ($ans !== false) {
                $result['isSuccess'] = true;
            }
        //}
        else {
            $result['error_message'] = '未登录';
        }
        header("Access-Control-Allow-Origin:*");
        echo json_encode($result);

    }

    function updateVacation() {
        $result = array(
            'isSuccess' => false );
        $token = I('token');
        $new_vacation['vacation_type'] = I('vacation_type');
        $new_vacation['vacation_startdate'] = I('vacation_startdate');
        $new_vacation['vacation_enddate'] = I('vacation_enddate');
        $ans = M('employee')->where(array('employee_token' => $token))->find();
        if ($ans !== false && $ans > 0) {
            $employee_id = $ans['employee_id'];
            $id = I('vacation_id');
            $ans = M('vacation')->where(array('vacation_id'=>$id))->save($new_vacation);
            if ($ans !== false) {
                $result['isSuccess'] = true;
            }
        }
        header("Access-Control-Allow-Origin:*");
        echo json_encode($result);
    }

    function showVacationNeedProvement () {
        $result = array(
            'isSuccess' => false,
            'vacation' =>'' );
        $token = I('token');
        $ans = M('employee')->where(array('employee_token' => $token))->find();
        if ($ans !== false && $ans > 0) {
            $employee_id = $ans['employee_id'];
            $vacation = M('vacation')->join('vacation_type on vacation.vacation_type = vacation_type.vacation_type_id')->where(array('employee_id' => $employee_id , 'vacation_provement_required' =>1))->select();
            if($vacation !== false){
                $result['isSuccess'] = true;
                $result['vacation'] = $vacation;
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