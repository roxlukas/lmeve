<?php
		function formatowanie($adm, $granted, $wyk, $zal) {
			$wyl=0;
			if ($adm==-1) {					//jesli nikt nie przyjal
			    if ($granted!=$zal) {			//jesli aktualny admin to NIE ten zalecany
			    	echo('<font color="green"><b>');	//ustaw zielony
			    } else {
				echo('<font color="blue"><b>');		//a jesli to ten - niebieski
			    }
			    $wyl=1;
			} else {
				if ($wyk==0) {				//a jesli ktos przyjal
					if ($adm==$granted) {		//i to wlasnie ten Aktualny
						echo('<font color="red"><b>'); //to kolor jest czerowny
					} else {
						echo('<font color="orange"><b>'); //a jesli nie aktualny admin - to pomarancz
					}
					$wyl=1;
				}
			}
			return $wyl;
		}

		function odformatowanie($wyl) {
		    if ($wyl) {
			    echo('</b></font>');
		    }
		}
		
		function szukanie($zlec, $szuk) {
			if ($szuk!='') {
				$szuk=strtoupper($szuk);	//zeby nie bylo wrazliwe na wielkosc liter
				$zlec=strtoupper($zlec);
				$tab=explode(' ',$szuk);	//rozbij na pojedyncze wyrazy
				$ile=count($tab);
				$tmp=0;
				for ($i=0; $i<$ile; $i++) {	//sprawdz kolejne wyrazy
					$tmp2=strstr($zlec,$tab[$i]);
					if ($tmp2!==FALSE) $tmp++;
				}
				if ($tmp==$ile) {	//jesli wszystkie wystepuja - zwroc true
					return true;
				} else {		//jesli chociaz jeden nie wystepuje - false
					return false;
				}
			} else {
				return true;
			}
		}
?>
