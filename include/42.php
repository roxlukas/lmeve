<?php
//standard header for each included file
checksession(); //check if we are called by a valid session
/*if (!checkrights("Administrator,ViewTimesheet")) { //"Administrator,ViewOverview"
	global $LANGNORIGHTS;
	echo("<h2>$LANGNORIGHTS</h2>");
	return;
}*/
$MENUITEM=4; //Panel ID in menu. Used in hyperlinks
$PANELNAME='Messages'; //Panel name (optional)
//standard header ends here?>
		    <div class="tytul">
			Messages<br>
		    </div>
		<?php
                if (!token_verify()) die("Invalid or expired token.");
		$admini=getusers();
		//$i=$_GET['nr'];

		$tempto=secureGETnum('msgto');
		$tmpmsg=secureGETstr('msg',4096);
		$tmpmsg=str_replace("\r\n","<br>",$tmpmsg);
		//$tmpmsg=stripslashes($tmpmsg);
		$tmptopic=secureGETstr('msgtopic',128);
		$tmpdate=date('d.m.Y G:i');

		if ($tempto==-1) {	//jesli do wszystkich to wyslij w petli
		    foreach($admini as $row) {
				$msgto=$row['userID'];
				$msgfrom=$_SESSION['granted'];
				$msgdate=$tmpdate;
				$msgread=0;
				$msgtopic=$tmptopic;
				$msg=$tmpmsg;
				$sql=("INSERT INTO `message` VALUES (DEFAULT,$msgto,$msgfrom,'$msgdate',$msgread,'$msgtopic','$msg')");
				db_uquery($sql);
		    }
		} else {		//jesli do jednego to wyslij tylko do niego
			$msgto=$tempto;
			$msgfrom=$_SESSION['granted'];
			$msgdate=$tmpdate;
			$msgread=0;
			$msgtopic=$tmptopic;
			$msg=$tmpmsg;
			$sql=("INSERT INTO `message` VALUES (DEFAULT,$msgto,$msgfrom,'$msgdate',$msgread,'$msgtopic','$msg')");
			db_uquery($sql);
		}
		//I zapisz kopie w wys�anych
		$msgto=$tempto;
		$msgfrom=$_SESSION['granted'];
		$msgdate=$tmpdate;
		$msgread=1;
		$msgtopic=$tmptopic;
		$msg=$tmpmsg;
		$sql=("INSERT INTO `message_sent` VALUES (DEFAULT,$msgto,$msgfrom,'$msgdate',$msgread,'$msgtopic','$msg')");
		db_uquery($sql);
		//include('messagew.php'); //zapisz tablice

		?>
		Message sent.<br><br>
		<form method="get" action="">
		<input type="hidden" name="id" value="4">
		<input type="submit" value="OK">
		</form>
		<script type="text/javascript">location.href="index.php?id=4&id2=0";</script>
