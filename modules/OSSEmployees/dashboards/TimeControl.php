<?php
/*+***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 *************************************************************************************************************************************/
class OSSEmployees_TimeControl_Dashboard extends Vtiger_IndexAjax_View {

	function getSearchParams($assignedto = '',$date) {	
		$conditions = array();
		$listSearchParams = array();
		if($assignedto != '') array_push($conditions,array('assigned_user_id','e',getUserFullName($assignedto)));
		if(!empty($date)){
			array_push($conditions,array('due_date','bw',$date.','.$date.''));
		}
		$listSearchParams[] = $conditions;
		return '&search_params='. json_encode($listSearchParams);
	}

	public function getWidgetTimeControl($user, $time) {
		if(!$time){
			return array();
		}

		$currentUser = Users_Record_Model::getCurrentUserModel();
		$module = 'HelpDesk';
		$instance = CRMEntity::getInstance($module);
		$securityParameter = $instance->getUserAccessConditionsQuerySR($module, $currentUser);
		$db = PearDatabase::getInstance();
		$param = array('OSSTimeControl', $user, $time['start'], $time['end']);
		$sql = "SELECT sum_time AS daytime, due_date, timecontrol_type FROM vtiger_osstimecontrol
					INNER JOIN vtiger_crmentity ON vtiger_osstimecontrol.osstimecontrolid = vtiger_crmentity.crmid
					WHERE vtiger_crmentity.setype = ? AND vtiger_crmentity.smownerid = ? ";
		if ($securityParameter != '')
			$sql.= $securityParameter;
		$sql .= "AND (vtiger_osstimecontrol.date_start >= ? AND vtiger_osstimecontrol.due_date <= ?) AND vtiger_osstimecontrol.deleted = 0 ORDER BY due_date ";
		$result = $db->pquery($sql, $param);
		$data = array();
		$days = array();
		$timeTypes = [];
		$sumWorkTime = 0;
		$sumBreakTime = 0;
		$countDays = 0;
		$workedDaysAmount = [];
		$holidayDaysAmount = [];
		$response = [];

		$numRows = $db->num_rows($result);
		for($i=0;$i<$numRows;$i++){
			$row = $db->query_result_rowdata($result, $i);
			$workingTimeByType[vtranslate($row['timecontrol_type'], 'OSSTimeControl')] += $row['daytime'];
			$workingTime[$row['due_date']][$row['timecontrol_type']] += $row['daytime'];
			if(!array_key_exists($row['timecontrol_type'], $timeTypes)){
				$timeTypes[$row['timecontrol_type']] = $counter++;
			}
			if(!in_array($row['due_date'], $workedDaysAmount) && 'PLL_WORKING_TIME' == $row['timecontrol_type'])
				$workedDaysAmount[$row['due_date']] = 1;

			if (!in_array($row['due_date'], $holidayDaysAmount) && 'PLL_HOLIDAY' == $row['timecontrol_type'])
				$holidayDaysAmount[$row['due_date']] = 1;

			if('PLL_WORKING_TIME' == $row['timecontrol_type'])
				$sumWorkTime += $row['daytime']; 

			if('PLL_BREAK_TIME' == $row['timecontrol_type'])
				$sumBreakTime += $row['daytime']; 
				
			if(!in_array($row['due_date'], $days))
				$days[] = $row['due_date'];
		}	
		
		if($numRows > 0){
			$counter = 0;
			$result = array();
			foreach ($workingTime as $timeKey => $timeValue) {
				foreach ($timeTypes as $timeTypeKey => $timeTypeKey) {
					$result[$timeTypeKey]['data'][$counter][0] = $counter;
					$result[$timeTypeKey]['label'] = vtranslate($timeTypeKey, 'OSSTimeControl');
					if($timeValue[$timeTypeKey]){
						$result[$timeTypeKey]['data'][$counter][1] = $timeValue[$timeTypeKey];
					}else{
						$result[$timeTypeKey]['data'][$counter][1] = 0;
					}
				}
				$counter++;
			}

			$ticks = [];
			foreach ($days as $key => $value) {
				$value = substr($value, -2);
				$newArray = [$key, $value];
				array_push($ticks, $newArray);
			}

			$workedDaysAmount = count($workedDaysAmount);
			$holidayDaysAmount = count($holidayDaysAmount);
			$allDaysAndWeekends = $this->getDays($time['start'], $time['end']);
			$response['workingDays'] = $allDaysAndWeekends['workingDays'];

			if ($sumWorkTime > 0) {
				if (0 == $workedDaysAmount)
					$averageWorkingTime = $sumWorkTime;
				else
					$averageWorkingTime = $sumWorkTime / $workedDaysAmount;
			}

			if ($sumBreakTime > 0) {
				if (0 == $workedDaysAmount)
					$averageBreakTime = $sumBreakTime;
				else
					$averageBreakTime = $sumBreakTime / $workedDaysAmount;
			}

			$response['holiayDays'] = $holidayDaysAmount;
			$response['daysWorked'] = $workedDaysAmount;
			$response['workDays'] = $allDaysAndWeekends['workDays'];
			$response['allDays'] = $allDaysAndWeekends['days'];
			$response['weekends'] = $allDaysAndWeekends['weekends'];
			$response['coundDaysType'] = $coundDaysType;
			$response['averageWorkingTime'] = number_format($averageWorkingTime, 2, '.', ' ');
			$response['sumBreakTime'] = number_format($averageBreakTime, 2, '.', ' ');
			$response['legend'] = $workingTimeByType;
			$response['chart'] = $result;
			$response['ticks'] = $ticks;
			$response['days'] = $days;
		}

		return $response;
	}

	public function process(Vtiger_Request $request) {
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$loggedUserId = $currentUser->get('id');
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		$linkId = $request->get('linkid');
		$user = $request->get('user');
		$time = $request->get('time');
		if ($time == NULL) {
			$time['start'] = date('Y-m-d', strtotime("-1 week"));
			$time['end'] = date("Y-m-d");
		} else {
			// date parameters passed, convert them to YYYY-mm-dd
			$time['start'] = Vtiger_Functions::currentUserDisplayDate($time['start']);
			$time['end'] = Vtiger_Functions::currentUserDisplayDate($time['end']);
		}

		if ($user == NULL)
			$user = $loggedUserId;
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		$widget = Vtiger_Widget_Model::getInstance($linkId, $currentUser->getId());

		$data = $this->getWidgetTimeControl($user, $time);
		$daysAmount = count($data['ticks']);

		$listViewUrl = 'index.php?module=OSSTimeControl&view=List';
		for($i = 0;$i<$daysAmount;$i++){
			$data['links'][$i][0] = $i;
			$data['links'][$i][1] = $listViewUrl . $this->getSearchParams($user, $data['days'][$i]);
		}

		$publicHolidays = Settings_PublicHoliday_Module_Model::getHolidayGroupType([$time['start'], $time['end']]);
		if ($publicHolidays) {
			foreach ($publicHolidays as $key => $value) {
				$upperCase = strtoupper($key);
				$viewer->assign($upperCase, $value);
			}
		}
		$viewer->assign('USERID', $user);
		$viewer->assign('DTIME', $time);
		$viewer->assign('WORKDAYS', $data['workDays']);
		$viewer->assign('WORKEDDAYS', $data['daysWorked']);
		$viewer->assign('HOLIDAYDAYS', $data['holiayDays']);
		$viewer->assign('AVERAGEBREAKTIME', $data['sumBreakTime']);
		$viewer->assign('WORKINGDAYS', $data['workingDays']);
		$viewer->assign('WEEKENDDAYS', $data['weekends']);
		$viewer->assign('AVERAGEWORKTIME', $data['averageWorkingTime']);
		$viewer->assign('ALLDAYS', $data['allDays']);
		$viewer->assign('DATA', $data);
		$viewer->assign('WIDGET', $widget);
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('CURRENTUSER', $currentUser);
		$viewer->assign('LOGGEDUSERID', $loggedUserId);
		$content = $request->get('content');
		if (!empty($content)) {
			$viewer->view('dashboards/TimeControlContents.tpl', $moduleName);
		} else {
			$viewer->view('dashboards/TimeControl.tpl', $moduleName);
		}
	}

	public function getDays($startDate, $endDate) {
		$holidayDays = Settings_PublicHoliday_Module_Model::getHolidays([$startDate, $endDate]);
		$notWorkingDaysType = Settings_Calendar_Module_Model::getNotWorkingDays();
		$begin = strtotime($startDate);
		$end = strtotime($endDate);
		$workDays = 0;

		if ($begin > $end) {
			return 0;
		} else {
			$days = 0;
			$weekends = 0;
			while ($begin <= $end) {
				$days++;
				$whatDay = date("N", $begin);
				$day = date('Y-m-d', $begin);
				$isWorkDay = TRUE;
				$isHolidayNotInWeekend = TRUE;
				foreach ($holidayDays as $key => $value) {
					if ($day == $value['date']) {
						$isWorkDay = FALSE;
						if ($whatDay > 5) {
							$isHolidayNotInWeekend = FALSE;
						}
						unset($holidayDays[$key]);
					}
				}
				foreach ($notWorkingDaysType as $key => $value) {
					if ($whatDay == $value)
						$isWorkDay = FALSE;
				}

				if ($isWorkDay)
					$workDays++;

				if ($whatDay > 5 && !$isWorkDay && $notWorkingDaysType) {
					$weekends++;
				}
				$begin += 86400;
			};
			$workingDays = $days - $weekends;
			$result = ['workDays' => $workDays, 'weekends' => $weekends, 'days' => $days];
			return $result;
		}
	}

}
