<?php

	function p ($array) {

		dump($array, 1, '<pre>', 0);
	}

    function getvacationlength($start,$end) {
        return floor((strtotime($end) - strtotime($start))/86400)+1;
    }


    function getvacationstatus ($name ,&$value) {

            if ($value[$name] == 0) {
                $value[$name.'_status'] = '审核中';
            }
            elseif ($value[$name] == 1) {
                $value[$name.'_status'] = '';
            }
            elseif ($value[$name] < 0) {
                $value[$name.'_status'] = M('employee')->where(array('employee_id' => abs($value[$name])))->find()['employee_name'].'不同意';
            }
            else {
                $value[$name.'_status'] = M('employee')->where(array('employee_id' => $value[$name]))->find()['employee_name'].'已同意';
            }

    }

    function getovertimeworklength ($id,$month) {
    	
    }




?>