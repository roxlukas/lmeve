<?
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
		
		function filtruj($zlec_wyk,$filter,$zlec_typ,$zlec_wyk,$data,$zlec_data,$zlec_data2,$zlec_help) {
			if ($filter==-1) unset($filter);
			if ((isset($data))&&($data!='')) {
				$tmpdata=explode(' ',$zlec_data);
				$tmpdata2=explode(' ',$zlec_data2);
				$data1=($tmpdata[0]==$data);
				$data2=($tmpdata2[0]==$data);
				return (($zlec_help==1)||(($data1)||($data2))&&((($zlec_wyk==1)&&($filter==254))||($zlec_typ==$filter)||(!isset($filter))||(($filter==255)&&($zlec_wyk==0))));
			} else {
				return (($zlec_help==1)||(($zlec_wyk==1)&&($filter==254))||($zlec_typ==$filter)||(!isset($filter))||(($filter==255)&&($zlec_wyk==0)));
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
