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
class OSSEmployees_Module_Model extends Vtiger_Module_Model {
	/**
	 * Function to get the Quick Links for the module
	 * @param <Array> $linkParams
	 * @return <Array> List of Vtiger_Link_Model instances
	 */
	public function getSideBarLinks($linkParams) {
		$parentQuickLinks = parent::getSideBarLinks($linkParams);
                $quickLink = array();
                
                if(Vtiger_DashBoard_Model::verifyDashboard($this->getName())){
                    $quickLink = array(
                            'linktype' => 'SIDEBARLINK',
                            'linklabel' => 'LBL_DASHBOARD',
                            'linkurl' => $this->getDashBoardUrl(),
                            'linkicon' => '',
                    );
                }
		//Check profile permissions for Dashboards
		$moduleModel = Vtiger_Module_Model::getInstance('Dashboard');
		$userPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		$permission = $userPrivilegesModel->hasModulePermission($moduleModel->getId());
		if($permission && $quickLink) {
			$parentQuickLinks['SIDEBARLINK'][] = Vtiger_Link_Model::getInstanceFromValues($quickLink);
		}
	
		return $parentQuickLinks;
	}
	/**
	 * Function to get list view query for popup window
	 * @param <String> $sourceModule Parent module
	 * @param <String> $field parent fieldname
	 * @param <Integer> $record parent id
	 * @param <String> $listQuery
	 * @return <String> Listview Query
	 */
	public function getQueryByModuleField($sourceModule, $field, $record, $listQuery) {
		return $listQuery." AND vtiger_ossemployees.employee_status = 'Employee'";
	}
	
	function getWorkingDays($startDate, $endDate){
		$begin = strtotime($startDate);
		$end   = strtotime($endDate);
		if ($begin > $end) {
			return 0;
		} else {
			$no_days  = 0;
			$weekends = 0;
			while ($begin <= $end) {
				$no_days++; // no of days in the given interval
				$what_day = date("N", $begin);
				if ($what_day > 5) { // 6 and 7 are weekend days
					$weekends++;
				};
				$begin += 86400; // +1 day
			};
			$working_days = $no_days - $weekends;
			return $working_days;
		}
	}

	function getBarChartColors($chartData){
		$numSelectedTimeTypes = count($chartData);
		$i = 0;
		$colors = array( '#4bb2c5', '#EAA228', '#c5b47f');
		foreach ($chartData as $key => $value) {
			$result[$key] = $colors[$i];
			$i++;
		}
		
		return $result;
	}
}