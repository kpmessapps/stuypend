<?php
if(!function_exists('_kd')){
	function _kd($array,$isExit = false){
		echo '<pre>';
                print_r($array);
                echo '</pre>';
                if($isExit){
                    exit(0);
                }
	}
}

if(!function_exists('checkArr')){
	function checkArr($array){
		if(!empty($array) && is_array($array) && count($array) > 0){
			return true;
		}
		return false;
	}
}

if(!function_exists('checkObj')){
	function checkObj($object){
		if(!empty($object) && is_object($object) && count($object) > 0){
			return true;
		}
		return false;
	}
}

if(!function_exists('removeNull')){
	function removeNull($jsonResponse){
		array_walk_recursive($jsonResponse,function(&$item){$item=strval($item);});
		return $jsonResponse;
	}
}

if(!function_exists('objectToArray')){
    function objectToArray($d) {
        if (is_object($d))
            $d = get_object_vars($d);
        // This is for mongodb _id it is in BSON mongo object id and it will not conver directly so it is generate error. so we have
        // converted this to in string.
        if(isset($d['_id'])){
            $d['_id'] = (string) $d['_id'];
        }
        return is_array($d) ? array_map(__FUNCTION__, $d) :  $d;
    }
}
if(!function_exists('parseResponse')){
	function parseResponse($jsonResponse){
		// CONVERT IF ANY OBJECT THAN IN ARRAY 
                // This is shortest and fast method if mongoID not used.
		//$jsonResponse = json_decode(json_encode($jsonResponse), true);
                
                // for mongo id this should be needed.
                $jsonResponse = objectToArray($jsonResponse);
		// CONVERT ALL INTEGER TO STRING INTEGER AND NULL TO BLANK "" STRING
		array_walk_recursive($jsonResponse,function(&$item){
                    if(!is_bool($item))
                    $item=strval($item);
                    
                });
		
		
		return $jsonResponse;
	}
}

if(!function_exists('getUserImage')){
	function getUserImage($arr){
                $arr = unserialize($arr);
                    $imageList['main']['url']    = (isset($arr['main']['url']) && $arr['main']['url'] != "") ? $arr['main']['url'] : "";
                    $imageList['main']['width']  = (isset($arr['main']['width']) && $arr['main']['width'] != "") ? $arr['main']['width'] : "0";
                    $imageList['main']['height'] = (isset($arr['main']['height']) && $arr['main']['height'] != "") ? $arr['main']['height'] : "0";

                    $imageList['thumb']['url']    = (isset($arr['thumb']['url']) && $arr['thumb']['url'] != "") ? $arr['thumb']['url'] : "";
                    $imageList['thumb']['width']  = (isset($arr['thumb']['width']) && $arr['thumb']['width'] != "") ? $arr['thumb']['width'] : "0";
                    $imageList['thumb']['height'] = (isset($arr['thumb']['height']) && $arr['thumb']['height'] != "") ? $arr['thumb']['height'] : "0";
    		return $imageList;
	}
}

if(!function_exists('getUserReviewResp')){
	function getUserReviewResp($reviewList){
            $data = [];
            foreach ($reviewList as $key => $value){
                $data[] = [
                    'reviewTripId'  => isset($value['rvw_trip_id']) ? $value['rvw_trip_id'] : "",
                    'reviewUserId' => isset($value['rvw_u_id']) ? $value['rvw_u_id'] : "",
                    'reviewByUserId' => isset($value['rvw_by_u_id']) ? $value['rvw_by_u_id'] : "",
                    'reviewRateNo'  => isset($value['rvw_rate_no']) ? $value['rvw_rate_no'] : "",
                    'reviewDescription' => isset($value['rvw_description']) ? $value['rvw_description'] : "",
                    'reviewCreatedDate' => isset($value['rvw_created_date']) ? $value['rvw_created_date'] : "",
                ];
            }
            return $data;
	}
}

if(!function_exists('getUserResp')){
    function getUserResp($data,$isAll = 0){
        $obj = new stdClass();
        if(!empty($data)){
                if($isAll <= 9)
                    $obj->userId           = isset($data['u_id']) ? $data['u_id'] : "";
                if($isAll <= 9)
                    $obj->name          = (isset($data['u_name'])) ? $data['u_name'] : "";
                 if($isAll <= 9)
                    $obj->profileImage         = (isset($data['u_image_data'])) ? getUserImage($data['u_image_data']) : [];
                if($isAll <= 9)
                    $obj->description         = (isset($data['u_desc'])) ? $data['u_desc'] : "";
                if($isAll <= 9)
                    $obj->countryCOde         = (isset($data['u_phone_country_code'])) ? $data['u_phone_country_code'] : "0";
                if($isAll <= 9)
                    $obj->phone         = (isset($data['u_phone'])) ? $data['u_phone'] : "";
                if($isAll <= 1)
                    $obj->email         = (isset($data['u_email'])) ? $data['u_email'] : "";
                if($isAll <= 1)
                    $obj->userType         = (isset($data['u_type'])) ? $data['u_type'] : "4";
                if($isAll <= 1)
                    $obj->unverifiedCountryCode         = (isset($data['u_temp_phone_country_code'])) ? $data['u_temp_phone_country_code'] : "";
                if($isAll <= 1)
                    $obj->unverifiedPhone         = (isset($data['u_temp_phone'])) ? $data['u_temp_phone'] : "";
                if($isAll <= 1)
                    $obj->isNotificationOn         = (isset($data['u_is_push_notification'])) ? $data['u_is_push_notification'] : "";
                if($isAll <= 1)
                    $obj->alertBadge         = (isset($data['u_alert_badge'])) ? $data['u_alert_badge'] : 0;
                if($isAll <= 9)
                    $obj->userStatus         = (isset($data['u_status'])) ? $data['u_status'] : 1;
                if($isAll <= 1)
                    $obj->modifiedDate  = (isset($data['u_modified_date'])) ? $data['u_modified_date'] : "";
        }
        return $obj;
    }
}

if(!function_exists('getBusinessResp')){
    function getBusinessResp($data){
        $returnArr = [];
        if(!empty($data)){
                $returnArr['businessId']       = (isset($data['bus_id'])) ? $data['bus_id'] : "";
                $returnArr['businessName']     = (isset($data['bus_name'])) ? $data['bus_name'] : "";
                $returnArr['businessAddress']  = (isset($data['bus_address'])) ? $data['bus_address'] : "";
                $returnArr['businessLatitude']  = (isset($data['bus_latitude'])) ? $data['bus_latitude'] : "";
                $returnArr['businessLongitude']  = (isset($data['bus_longitude'])) ? $data['bus_longitude'] : "";
                $returnArr['businessImageUrl']  = (isset($data['bus_image_url'])) ? $data['bus_image_url'] : "";
                $returnArr['businessDescription']  = (isset($data['bus_desc'])) ? $data['bus_desc'] : "";
                $returnArr['businessYelpUrl']  = (isset($data['bus_yelp_url'])) ? $data['bus_yelp_url'] : "";
                $returnArr['businessBackgroundColor']     = (isset($data['bus_back_color'])) ? $data['bus_back_color'] : "";
          //      $returnArr['businessLongitude']  = (isset($data['bus_last_contest_id'])) ? $data['bus_last_contest_id'] : "";
                $returnArr['businessTotalUsers']  = (isset($data['bus_total_users'])) ? $data['bus_total_users'] : "";
          //      $returnArr['businessCouponId']  = (isset($data['bus_last_coupon_id'])) ? $data['bus_last_coupon_id'] : "";
        }
        return $returnArr;
    }
}

if(!function_exists('getBusinessContestResp')){
    function getBusinessContestResp($data){
        $returnArr = new stdClass();
        if(!empty($data) && isset($data['contest_id'])){
                $returnArr->contestId             = (isset($data['contest_id'])) ? $data['contest_id'] : "";
                $returnArr->contestBusinessId     = (isset($data['contest_bus_id'])) ? $data['contest_bus_id'] : "";
       //         $returnArr['contestStartDate']      = (isset($data['contest_start_date'])) ? $data['contest_start_date'] : "";
                $returnArr->contestEndDate        = (isset($data['contest_end_date'])) ? $data['contest_end_date'] : "";
        }
        return $returnArr;
    }
}

if(!function_exists('getJobResp')){
    function getJobResp($data){
        $returnArr = new stdClass();
        if(!empty($data) && isset($data['job_id'])){
                $returnArr->jobId             = isset($data['job_id']) ? $data['job_id'] : "";
                $returnArr->jobName     = (isset($data['job_name'])) ? $data['job_name'] : "";
                $returnArr->jobClientName    = (isset($data['clientName'])) ? $data['clientName'] : "";
                $returnArr->jobClientPhone    = (isset($data['clientPhone'])) ? $data['clientPhone'] : "";
                $returnArr->jobClientPhoneCountryCode    = (isset($data['clientPhoneCountryCode'])) ? $data['clientPhoneCountryCode'] : "";
                $returnArr->jobExecutiveUserId        = (isset($data['job_exe_u_id'])) ? $data['job_exe_u_id'] : "";
                $returnArr->jobExecutivePhone    = (isset($data['executivePhone'])) ? $data['executivePhone'] : "";
                $returnArr->jobExecutivePhoneCountryCode    = (isset($data['executivePhoneCountryCode'])) ? $data['executivePhoneCountryCode'] : "";
                $returnArr->jobExecutiveName        = (isset($data['executiveName'])) ? $data['executiveName'] : "";
                $returnArr->jobJobScopeOfWork        = (isset($data['job_scope_of_work'])) ? $data['job_scope_of_work'] : "";
                $returnArr->jobDescription        = (isset($data['job_description'])) ? $data['job_description'] : "";
                $returnArr->jobRate        = (isset($data['job_rate'])) ? $data['job_rate'] : "";
                $returnArr->jobSkills        = (isset($data['job_skills'])) ? $data['job_skills'] : "";
                $returnArr->jobHourWeek        = (isset($data['job_hour_week'])) ? $data['job_hour_week'] : "";
                $returnArr->jobAddress        = (isset($data['job_address'])) ? $data['job_address'] : "";
                $returnArr->jobLatitude        = (isset($data['job_latitude'])) ? $data['job_latitude'] : "";
                $returnArr->jobLongitude        = (isset($data['job_longitude'])) ? $data['job_longitude'] : "";
                $returnArr->jobPersonNeeded        = (isset($data['job_persons_needed'])) ? $data['job_persons_needed'] : "";
                $returnArr->jobPersonAssigned        = (isset($data['job_person_assigned'])) ? $data['job_person_assigned'] : "";
                $returnArr->jobCreatedDate        = (isset($data['job_created_date'])) ? $data['job_created_date'] : "";
                $returnArr->jobStatus        = (isset($data['job_status'])) ? $data['job_status'] : "";
        }
        return $returnArr;
    }
}


if(!function_exists('getSkillResp')){
    function getSkillResp($data){
        $returnArr = new stdClass();
        if(!empty($data) && isset($data['skill_id'])){
                $returnArr->skillId             = isset($data['skill_id']) ? $data['skill_id'] : "";
                $returnArr->skillName     = (isset($data['skill_name'])) ? $data['skill_name'] : "";
        }
        return $returnArr;
    }
}


if(!function_exists('getJobContractResp')){
    function getJobContractResp($data){
        $returnArr = new stdClass();
        if(!empty($data) && isset($data['job_con_id'])){
                $returnArr->contractId              = isset($data['job_con_id']) ? $data['job_con_id'] : "";
                $returnArr->contractJobId           = isset($data['job_con_job_id']) ? $data['job_con_job_id'] : "";
                $returnArr->contractUserId          = isset($data['job_con_u_id']) ? $data['job_con_u_id'] : "";
                $returnArr->contractClientStatus    = isset($data['job_client_status']) ? $data['job_client_status'] : "";
                $returnArr->contractExecutiveStatus = isset($data['job_exe_status']) ? $data['job_exe_status'] : "";
                $returnArr->contractStatus          = isset($data['job_con_status']) ? $data['job_con_status'] : "";
                $returnArr->contractAppliedTime        = isset($data['job_con_created_date']) ? $data['job_con_created_date'] : "";
        }
        return $returnArr;
    }
}

