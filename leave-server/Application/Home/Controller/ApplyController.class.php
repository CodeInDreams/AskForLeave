<?php
namespace Home\Controller;
use Think\Controller;
class ApplyController extends Controller {

    public function a() {
        echo floor(date('t')*0.3);
    }
	
    public function test($id) {//得到某员工这个月的加班天数
        $working_days = 0;
        $sql = "SELECT SUM(datediff(vacation_enddate,vacation_startdate)) FROM `vacation` WHERE 
        vacation_startdate BETWEEN date_add(curdate(), interval - day(curdate()) + 1 day) and 
        last_day(curdate()) and vacation_enddate BETWEEN date_add(curdate(), interval - day(curdate()) + 1 day) 
        and last_day(curdate()) and vacation_type = 9 and employee_id = ".$id;//.$employee_id;
        $ans = M('vacation')->query($sql);
        if ($ans && isset($ans[0]['sum(datediff(vacation_enddate,vacation_startdate))'])) {
            $working_days = $working_days + $ans[0]['sum(datediff(vacation_enddate,vacation_startdate))'];
        }
        $sql = "SELECT SUM(datediff(vacation_enddate,date_add(curdate(), interval - day(curdate()) + 1 day))+1) FROM `vacation` 
        WHERE vacation_startdate < date_add(curdate(), interval - day(curdate()) + 1 day) 
        AND vacation_enddate >date_add(curdate(), interval - day(curdate()) + 1 day) 
        AND vacation_enddate<date_add(curdate()-day(curdate())+1,interval 1 month) 
        and vacation_type = 9 and employee_id = ".$id;//.$employee_id;
        $ans1 = M('vacation')->query($sql);
        if ($ans1 && isset($ans1[0]['sum(datediff(vacation_enddate,date_add(curdate(), interval - day(curdate()) + 1 day))+1)'])) {
            $working_days = $working_days + $ans1[0]['sum(datediff(vacation_enddate,date_add(curdate(), interval - day(curdate()) + 1 day))+1)'];
        }

        $sql = "SELECT SUM(datediff(last_day(CURRENT_DATE),vacation_startdate)+1) FROM `vacation` 
        WHERE vacation_startdate < last_day(CURRENT_DATE) AND vacation_enddate > last_day(CURRENT_DATE) 
        and vacation_type = 9 and employee_id = ".$id;//.$employee_id;
        $ans2 = M('vacation')->query($sql);
        if ($ans2 && isset($ans2[0]['sum(datediff(last_day(current_date),vacation_startdate)+1)'])) {
            $working_days = $working_days + $ans2[0]['sum(datediff(last_day(current_date),vacation_startdate)+1)'];
        }
    
		return $working_days;
    }

    function yearMonthDays($year,$month){        
        if (in_array($month, array(1, 3, 5, 7, 8, 01, 03, 05, 07, 08, 10, 12))) {  
            return 31;  
        }elseif ($month == 2){  
            if ($year % 400 == 0 || ($year % 4 == 0 && $year % 100 !== 0)) { //判断是否是闰年  
                return 29;  
            }else{  
                return 28;  
            }  
        } else {  
            return 30;  
        }
    }

    private function numofboss($id) {
        $num_boss = 0;
        $employee_path = M('employee')->where(array('employee_id' => $id))->find()['employee_path'];
        $approver1 = implode('/', explode('/', $employee_path,-1)) ;
        $approver2 = implode('/', explode('/', $approver1,-1)) ;
        $approver3 = implode('/', explode('/', $approver2,-1)) ;
        $has_boss1 = M('employee')->where(array('employee_path' => $approver1))->find();
        $has_boss2 = M('employee')->where(array('employee_path' => $approver2))->find();
        $has_boss3 = M('employee')->where(array('employee_path' => $approver3))->find();
        if ($has_boss1 !== false && $has_boss1 > 0) {
            $num_boss = $num_boss + 1;
            if ($has_boss2 !== false && $has_boss2 > 0) {
                $num_boss = $num_boss + 1;
                if ($has_boss3 !== false && $has_boss3 > 0) {
                    $num_boss = $num_boss + 1;
                }
            }
        }
        return $num_boss;
    }
	
    function getvacationlength($start,$end) {
        return floor((strtotime($end) - strtotime($start))/86400)+1;
    }

    function getemployeeallyearvacation($id) {
        $employee_id = $id;
        $allvacation = 0;
        $sql = 'select vacation_startdate,vacation_enddate from vacation where vacation_type = 1 and employee_id = '.$employee_id;
        $ans = M('vacation')->query($sql);
        if($ans) {
            foreach ($ans as &$value) {
                $allvacation = $allvacation + getvacationlength($value['vacation_startdate'],$value['vacation_enddate']);
            }
        }
        return $allvacation;
    }

    public function showyear() {
        $employee_id = I('employee_id');
        $employee_entrydate = M('employee')->where(array('employee_id' => $employee_id))->find()['employee_entrydate'];
        $current = strtotime(date("Y-m-d",time()));
        $entry_length = floor(floor((time() - strtotime($employee_entrydate))/86400)/365);//入职年数
        var_dump($entry_length);
    }

    public function askforleave() {//传参 type startdate enddate reason
        $result = array(
            'isSuccess' => false,
            'error_message' => '' );
        $vaild_vacation = false;//判断一个假期申请是否有效
        $token = I('token');
        $ans = M('employee')->where(array('employee_token' => $token))->find();
        if($ans !== false && $ans > 0) {
            if(I('vacation_reason') && I('vacation_type') && I('vacation_startdate') && I('vacation_enddate')) {
                $new_vacation['employee_id'] = $ans['employee_id'];
                $id = $ans['employee_id'];
                $new_vacation['employee_name'] = $ans['employee_name'];
                $new_vacation['vacation_type'] = I('vacation_type');
                $new_vacation['vacation_startdate'] = I('vacation_startdate');
                $new_vacation['vacation_enddate'] = I('vacation_enddate');
                $new_vacation['vacation_reason'] = I('vacation_reason');
                //$new_vacation['vacation_proveduetime'] = I('vacation_proveduetime');
                $employee_boss_num = $this->numofboss($ans['employee_id']);
                $employee_id = $ans['employee_id'];
                //通过员工的级别来判定需要审核人的个数
                switch ($employee_boss_num) {
                    case 0:
                        $new_vacation['vacation_approver1'] = 1;
                        $new_vacation['vacation_approver2'] = 1;
                        $new_vacation['vacation_approver3'] = 1;
                        break;

                    case 1:
                        $new_vacation['vacation_approver1'] = 0;
                        $new_vacation['vacation_approver2'] = 1;
                        $new_vacation['vacation_approver3'] = 1;
                        break;

                    case 2:
                        $new_vacation['vacation_approver1'] = 0;
                        $new_vacation['vacation_approver2'] = 0;
                        $new_vacation['vacation_approver3'] = 1;
                        break;

                    case 3:
                        $new_vacation['vacation_approver1'] = 0;
                        $new_vacation['vacation_approver2'] = 0;
                        $new_vacation['vacation_approver3'] = 0;
                        break;
                    
                    default:
                        $result['error_message'] = '员工path有问题';
                        $vaild_vacation = false;
                        break;
                }
                //根据假期类型判断合法性
                switch ($new_vacation['vacation_type']) {
                    case 1://年假：若申请天数大于剩余天数则假期无效$vaild_vacation=false
                    $employee_id = $ans['employee_id'];
                    $employee_entrydate = $ans['employee_entrydate'];
                    $entry_length = floor(floor((time() - strtotime($employee_entrydate))/86400)/365);//入职年数
                    if ($entry_length < 3) {
                        $employee_should_have_yearvacation = 7;
                    }
                    else {
                        $employee_should_have_yearvacation = 14;
                    }
                    $employee_used_yearvacation = $this->getemployeeallyearvacation($employee_id);//员工已经用的年假天数  
                    $day_legth = $this->getvacationlength(I('vacation_startdate'),I('vacation_enddate'));//假期长短
                    if (($day_legth + $employee_used_yearvacation) > $employee_should_have_yearvacation) {//假期天数不够请假不成功
                        $vaild_vacation = false;
                        $result['error_message'] = '年假天数不足';
                    }
                    else {
                        $vaild_vacation = true;
                        $new_vacation['vacation_submitprove'] = 2;//年假不需要证明材料所以改为2
                    }                        
                        break;
                    case 7://出差
                    $new_vacation['vacation_submitprove'] = 2;//出差不需要证明材料所以改为2
                    $vaild_vacation = true;
                        break;
                    case 9:
                    $new_vacation['vacation_submitprove'] = 2;//加班不需要证明材料所以改为
                    $day_legth = $this->getvacationlength(I('vacation_startdate'),I('vacation_enddate'));//假期长短
                    $employee_worked_days = $this->test($employee_id);
                    if (($employee_worked_days + $day_legth) < floor(date('t')*0.3)) {
                        $vaild_vacation = true;
                        $new_vacation['vacation_submitprove'] = 2;
                        //年假不需要证明材料所以改为2
                    }
                    else {
                        $result['error_message'] = '不能再加班了';
                        $vaild_vacation = false;
                    }
                        break;
                    
                    default:
                        $new_vacation['vacation_proveduetime'] = I('vacation_proveduetime');
                        $vaild_vacation = true;
                        break;
                }
                if ($vaild_vacation) {
                    $ans = M('vacation')->add($new_vacation);
                    if ($ans !== false) {
                        $result['isSuccess'] = true;
                    }
                }

            }
            else {
                $result['error_message'] = '参数缺少';
            }
        }
        else {
            $result['error_message'] = '未登录';
        }
        header("Access-Control-Allow-Origin:*");
        echo json_encode($result);
    }

    public function showRemainYearvacation () {
        $result = array(
            'isSuccess' => false,
            'remainday' => '' );
        $token = I('token');
        $ans = M('employee')->where(array('employee_token' => $token))->find();
        if($ans !== false && $ans > 0) {
            $employee_id = $ans['employee_id'];
            $employee_entrydate = $ans['employee_entrydate'];
            $current = strtotime(date("Y-m-d",time()));
            $entry_length = floor(floor((time() - strtotime($employee_entrydate))/86400)/365);//入职年数
            if ($entry_length < 3) {
                $employee_should_have_yearvacation = 10;
            }
            else {
                $employee_should_have_yearvacation = 20;
            }
            $employee_used_yearvacation = $this->getemployeeallyearvacation($employee_id);//员工已经用的年假天数            
            $result['remainday'] = $employee_should_have_yearvacation - $employee_used_yearvacation;
            $result['isSuccess'] = true;
        }
        header("Access-Control-Allow-Origin:*");
        echo json_encode($result);        
    }

    public function showAllow() {//显示当前用户有权限批准的假期
        $result = array(
            'isSuccess' => false,
            'vacation_info' => '',
		);        
        $token = I('token');
        $ans = M('employee')->where(array('employee_token' => $token))->find();
        if ($ans !== false && $ans > 0) {
            $employee_id = $ans['employee_id'];
            $user = $ans;
            if ($user !== false && $user >0) {
                $user_path = $user['employee_path'];
                $condition1 = 'vacation.vacation_approver1 = 0 and employee.employee_path regexp '.'\''.$user_path.'/[0-9]+$\'';
                $condition2 = 'vacation.vacation_approver1 > 0 and vacation.vacation_approver2 = 0 and employee.employee_path regexp '.'\''.$user_path.'/[0-9]+/[0-9]+$\'';
                $condition3 = 'vacation.vacation_approver1 > 0 and vacation.vacation_approver2 > 0 and vacation.vacation_approver3 = 0 and employee.employee_path regexp '.'\''.$user_path.'/[0-9]+/[0-9]+/[0-9]+$\'';
                $sql = 'SELECT * FROM'.'('.' vacation left join employee on vacation.employee_id = employee.employee_id'.')'.'left join vacation_type on vacation.vacation_type = vacation_type.vacation_type_id'.' where '.'('.$condition1.')'.'or ('.$condition2.')'.'or ('.$condition3.')'.' '; //vacation.vacation_approver1 = 0 and employee.employee_path regexp '.'\''.$user_path.'/[0-9]+$\''  ;
                $result['isSuccess'] = true;
                $vacation_need_permission = M('vacation')->query($sql);
                if ($vacation_need_permission !== false) {                    
                    foreach ($vacation_need_permission as $key => &$value) {
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
                        $data['employee_name'] = $value['employee_name'];
                        $data['vacation_startdate'] = $value['vacation_startdate'];
                        $data['vacation_enddate'] = $value['vacation_enddate'];
                        $data['vacation_reason'] = $value['vacation_reason'];
						$data['vacation_proveduetime'] = $value['vacation_proveduetime'];
                        $data['vacation_status'] = $value['vacation_status'];
                        $data['vacation_name'] = $value['vacation_name'];
                        $value = $data;
                    }
                    $result['vacation_info'] = $vacation_need_permission;
                }
            }   
        }
        header("Access-Control-Allow-Origin:*");
        echo json_encode($result);
    }

    public function allow() {
        $result = array(
            'isSuccess' => false );
        $vacation_id = I('vacation_id');
        $token = I('token');
        $ans = M('employee')->where(array('employee_token' => $token))->find();
        if ($ans !== false && $ans > 0) {
            $employee_id = $ans['employee_id'];
            $applicant_path = M('vacation')->join('employee on vacation.employee_id = employee.employee_id')->where(array('vacation_id' => $vacation_id))->find()['employee_path'];
            $boss_path = M('employee')->where(array('employee_id' => $employee_id))->find()['employee_path'];
            $which_approver = substr_count($applicant_path, '/') - substr_count($boss_path, '/');
            if (strpos($applicant_path, $boss_path) !== false && $which_approver < 4 && $which_approver > 0) {
                $sql = 'update vacation set vacation_approver'.$which_approver.' = '.$employee_id.' where vacation_approver'.$which_approver.' = 0 and vacation_id = '.$vacation_id;
                $ans1 = M('vacation')->execute($sql);
                if($ans1 !== false) {
                    $result['isSuccess'] = true;
                }                
            }
        }
        header("Access-Control-Allow-Origin:*");
        echo json_encode($result);
    }

    public function forbid() {
        $result = array(
            'isSuccess' => false );
        $vacation_id = I('vacation_id');        
        $token = I('token');
        $ans = M('employee')->where(array('employee_token' => $token))->find();
        if ($ans !== false && $ans > 0) {
            $employee_id = $ans['employee_id'];
            $applicant_path = M('vacation')->join('employee on vacation.employee_id = employee.employee_id')->where(array('vacation_id' => $vacation_id))->find()['employee_path'];
            $boss_path = M('employee')->where(array('employee_id' => $employee_id))->find()['employee_path'];
            $which_approver = substr_count($applicant_path, '/') - substr_count($boss_path, '/');
            if (strpos($applicant_path, $boss_path) !== false && $which_approver < 4 && $which_approver > 0) {
                $sql = 'update vacation set vacation_approver'.$which_approver.' = -'.$employee_id.' where vacation_approver'.$which_approver.' = 0 and vacation_id = '.$vacation_id;
                $ans1 = M('vacation')->execute($sql);
                if($ans1 !== false) {
                    $result['isSuccess'] = true;
                }                
            }
        }   
		header("Access-Control-Allow-Origin:*");
		echo json_encode($result);
    }
}