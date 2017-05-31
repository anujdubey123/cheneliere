<?php
ob_start();
/**
http://platform.cheneliere.ca/beta/ws/getActivityList.php?action=getActivityList&userid=48291
http://localhost:8012/fbook/ws/getActivityList.php?action=getActivityList&userid=37470&isbn=N782765037712&chapterid=63215
 * It provides Activity List 
 * @author Manickaraj TD
 *
 */
 //http://platform.cheneliere.ca/dev/ws/getActivityList.php?action=getActivityList&userid=37470&isbn=11111111111&chapterid=297387
include_once "../php/Services/DbConnect.php";
include_once "../php/Services/DbInst.php";
class getActivityList
{
	public function getActivityList()
	{
		$obj=new DbConnect();
		$this->dbCon=$obj->GetConnect();
	}
	public function isValidUser($userID)
	{
             $checkUsrStr="https://ws.cheneliere.ca/user/Info/Get/?userID=$userID";
			 $xml_Data_UserLogin = json_decode($this->Parse($checkUsrStr));
			 if($xml_Data_UserLogin->status == "success")
			 {		
			 $status=1;		
			 }
			 else
			 {
				 $sucess=0;	
			 }	
			 return	$status;			
	}
	public function CheckUser($user_id)
	{
		$get=new DbInst($this->dbCon, "user_master");
		$get->SetResultFld('session_id,userid,if(usertype="2","S","T") as usertype,refid');
		$get->CondField('session_id',$user_id);																	 
		$groupStuArr=$get->GetQuery();
		 
		 return $groupStuArr[0]['refid'];
	
	}
	public function Parse($url)
	{
			$fileContents= @file_get_contents($url);
			$fileContents = str_replace(array("\n", "\r", "\t"), '', $fileContents);
			$fileContents = trim(str_replace('"', "'", $fileContents));
			$simpleXml = simplexml_load_string($fileContents);
			$json = json_encode($simpleXml);
			return $json;
	}	
	public function getActivityListDet($userID,$ISBN,$chapterID,$user_type)
	{
 				$get = new DbInst($this->dbCon);
				$useridSql='SELECT refid FROM user_master WHERE userid='.$userID.'';
				$useridArr = $get->retresult($useridSql);
				$mCouUsrId= $useridArr[0]['refid'];
				//$mCouUsrId=$userID;
				$bookidSql='SELECT Id FROM book_details WHERE ISBN="'.$ISBN.'"';
				$bookidArr = $get->retresult($bookidSql);
                $mCouBokId=$bookidArr[0]['Id'];
				/*$bookVIdSql="SELECT id FROM `book_volume` WHERE `masid` = '".$mCouBokId."' ";
				$bookVIdArr = $get->retresult($bookVIdSql);*/
				$bookVIdSql='SELECT volume_id FROM  `book_chapter` WHERE  `master_id` ="'.$chapterID.'"';
				$bookVIdArr = $get->retresult($bookVIdSql);
                $mCouBokVId=$bookVIdArr[0]['volume_id'];
	            $sql ='CALL getActivityList("' .$user_type .'","' .($mCouUsrId == "DEMO"?-1:$mCouUsrId).'","'.$mCouBokVId.'","' . $chapterID .'","","")';
                
		        $ActivityListArr = $get->retresult($sql);
                //print_r($ActivityListArr);die();
				$cnt = count($ActivityListArr);
				header("Content-type: text/xml");
				$ActivityListXml='<?xml version="1.0" encoding="UTF-8"?><wsIplusPlatform><status>success</status>';
						for($i=0;$i<$cnt;$i++){ 
						$itemID=$i+1;
						//htmlspecialchars_decode($ActivityListArr[$i]['quiz_title'], ENT_NOQUOTES); 
								$ActivityListXml.='<item id="'.$itemID.'"><quizID><![CDATA['.$ActivityListArr[$i]['id'].']]></quizID><quizTitle><![CDATA['.htmlspecialchars_decode($ActivityListArr[$i]['quiz_title'], ENT_NOQUOTES).']]></quizTitle></item>';
						}
				$ActivityListXml.='<parameters><userID>'.$mCouUsrId.'</userID><chapterID>'.$chapterID.'</chapterID></parameters></wsIplusPlatform>';
		return $ActivityListXml;
	}
}
//start action here
$userID = (@$_REQUEST['userid']!='') ? @$_GET['userid'] : '' ;
$chapterID = (@$_REQUEST['chapterid']!='') ? @$_GET['chapterid'] : '' ;
$ISBN = (@$_REQUEST['isbn']!='') ? @$_GET['isbn'] : '' ;
$user_type = (@$_REQUEST['user_type']!='') ? @$_GET['user_type'] : '' ;

$objgetActivityList = new getActivityList();
if(isset($_REQUEST['action']))
	{	
	switch($_REQUEST['action'])
		{
			case 'getActivityList':	
					/*$validuser=$objgetActivityList->isValidUser($userID);
					if($validuser==1)
					{*/
						//echo "dsd";
						$ActivityListArr=$objgetActivityList->getActivityListDet($userID,$ISBN,$chapterID,$user_type);
						echo $ActivityListArr;
					/*}
					else
					{
						header("Content-type: text/xml");
						$NvalidUsr="Utilisateur invalide";
						$ActivityListXml='<?xml version="1.0" encoding="UTF-8"?><wsIplusPlatform><status>'.$NvalidUsr.'</status>';						 
						$ActivityListXml.='<parameters><userID>'.$userID.'</userID><chapterID>'.$chapterID.'</chapterID></parameters></wsIplusPlatform>';
						echo $ActivityListXml;
					}*/
			 break;
			
		}
	
	}
	else
	{
	$objgetActivityList->SetResult('ACTION');
	}
?>
