<?php
include("../cahier_notes/visu_releve_notes_func.lib.php");

function fich_debug_bull($texte){
	$fichier_debug="/tmp/bulletin_pdf_2016.txt";

	// Passer la variable à "y" pour activer le remplissage du fichier de debug
	$local_debug="n";
	if($local_debug=="y") {
		$fich=fopen($fichier_debug,"a+");
		fwrite($fich,$texte);
		fclose($fich);
	}
}

function regime($id_reg) {
	switch($id_reg) {
		case "d/p":
			$regime="demi-pensionnaire";
			break;
		case "ext.":
			$regime="externe";
			break;
		case "int.":
			$regime="interne";
			break;
		case "i-e":
			$regime="interne-externé";
			break;
		default:
			$regime="Régime inconnu???";
			break;
	}

	return $regime;
}

function bulletin_pdf($tab_bull,$i,$tab_rel) {
	//echo "DEBUG";
	global
		//==============
		//Ajout J.Etheve
		//$coefficients_a_1,
		//==============
		//============================================
		// Paramètres généraux:
		// En admin, dans Gestion générale/Configuration générale
		//$gepi_prof_suivi,
		$gepi_cpe_suivi,
		$RneEtablissement,
		$gepiSchoolAcademie,
		$gepiSchoolName,
		$gepiSchoolAdress1,
		$gepiSchoolAdress2,
		$gepiSchoolZipCode,
		$gepiSchoolCity,
		$gepiSchoolPays,
		$gepiSchoolTel,
		$gepiSchoolFax,
		$gepiSchoolEmail,
		$gepiYear,

		$logo_etab,

		$bull_intitule_app,

		$bull_formule_bas,

		// Paramètre transmis depuis la page d'impression des bulletins
		$un_seul_bull_par_famille,
		$seulement_resp_2_autre_adresse,

		$compteur_bulletins,

		// Datation du bulletin (paramètre initié dans l'entête du bulletin PDF)
		$date_bulletin,

		// Paramètres du modèle PDF
		$tab_modele_pdf,

		$use_cell_ajustee,

		// Pour permettre de récupérer via global dans releve_pdf() le numéro du parent dont on imprime le bulletin avec au verso le relevé de notes:
		$num_resp_bull,

		// Pour imprimer le bulletin_pdf() de tel responsable uniquement
		// (pour l'envoi par mail avec la bonne adresse associée dans le cas de l'archivage des bulletins PDF)
		$tmp_num_resp_destinataire,

		// Pour repérer la dernière période quand on imprime plusieurs périodes d'un coup pour certains élèves
		// et n'insérer le bilan de fin de cycle que pour la dernière période
		$tab_ele_derniere_periode_imprimee,

		// Pour récupérer le 1 relevé par page en verso du bulletin... variable récupérée via 'global' dans la fonction releve_pdf()
		$nb_releve_par_page,

		//20100615
		//$moyennes_periodes_precedentes,
		//$evolution_moyenne_periode_precedente,

		//$avec_coches_mentions,
		$gepi_denom_mention,

		$signature_bull,

		$tab_engagements,

		// Objet PDF initié hors de la présente fonction donnant la page du bulletin pour un élève
		$pdf;

	global $intercaler_app_classe;

	global $avec_bilan_cycle;

	global $param_bull2016;
	global $gepiPath;
	global $tab_mef;

	/*
	echo "<pre>";
	print_r($tab_bull);
	echo "</pre>";
	*/

	//=========================================

	// Inutile dans le bulletin 2016
	if (isset($GLOBALS['multisite']) AND $GLOBALS['multisite'] == 'y') {
		// On récupère le RNE de l'établissement
		$rep_photos="../photos/".$_COOKIE['RNE']."/eleves/";
	}else{
		$rep_photos="../photos/eleves/";
	}

	//=====================================
	/*
	// NE PAS SUPPRIMER CETTE SECTION... c'est pour le debug

	// Règles en rouge:
	// Selon ce que l'on souhaite débugger, décommenter une des deux règles
	$pdf->SetDrawColor(255,0,0);
	//=====================================
	// Règle 1: horizontale
	$tmp_marge_gauche=5;
	$tmp_marge_haut=5;
	$x=$tmp_marge_gauche;
	$y=$tmp_marge_haut;

	$pdf->SetXY($x,$y);
	$pdf->Cell(200,1,'','T',0,'C',0);

	for($loop=0;$loop<19;$loop++) {
		$x=$tmp_marge_gauche+$loop*10;
		$pdf->SetXY($x,$y);
		$pdf->Cell(5,20,''.$loop,'',0,'L',0);
		$pdf->SetXY($x,$y);
		$pdf->Cell(10,270,'','L',0,'C',0);

		for($loop2=0;$loop2<10;$loop2++) {
			$pdf->SetXY($x+$loop2,$y);
			$pdf->Cell(10,5,'','L',0,'C',0);
		}
	}
	//=====================================
	// Règle 2: verticale
	$tmp_marge_gauche=1;
	$tmp_marge_haut=0;
	$x=$tmp_marge_gauche;
	$y=$tmp_marge_haut;

	$pdf->SetFont('DejaVu','',5);

	// Ligne verticale
	$pdf->SetXY($x,$y);
	$pdf->Cell(1,280,'','L',0,'C',0);

	for($loop=1;$loop<29;$loop++) {
		// Repère numérique en cm
		$y=$tmp_marge_haut+$loop*10-3;
		$pdf->SetXY($x,$y);
		$pdf->Cell(10,5,''.$loop,'',0,'L',0);

		// Ligne tous les centimètres
		$y=$tmp_marge_haut+$loop*10;
		$pdf->SetXY($x,$y);
		$pdf->Cell(200,10,'','T',0,'C',0);

		// Les millimètres
		for($loop2=0;$loop2<10;$loop2++) {
			$pdf->SetXY($x,$y-10+$loop2);
			$pdf->Cell(2,10,'','T',0,'C',0);
		}
	}
	//=====================================
	// Retour au noir pour les tracés qui suivent:
	$pdf->SetDrawColor(0,0,0);
	*/
	//=====================================
	$nb_bulletins=1;

	//=====================================
	// Préparation des lignes d'adresse

	/*
	echo "<hr />\n";
	echo "nb_bulletins=$nb_bulletins<br />\n";
	echo $tab_bull['eleve'][$i]['nom']." ".$tab_bull['eleve'][$i]['prenom']." (\$tab_bull['eleve'][$i]['resp'])";
	echo "<pre>";
	print_r($tab_bull['eleve'][$i]['resp']);
	echo "</pre>";
	*/

	// Cas standard d'impression
	$tab_adr_lignes=array();
	foreach($tab_bull['eleve'][$i]['resp']["adresses"]["adresse"] as $tmp_num_resp => $current_resp_adr) {
		$tab_adr_lignes[]=$current_resp_adr["adresse"];
	}
	$nb_bulletins=$tab_bull['eleve'][$i]['resp']["adresses"]["nb_adr"];
	//echo "nb_bulletins=$nb_bulletins<br />\n";

	// Impression avec désignation du responsable (archivage bulletins PDF ou autre)
	if((isset($tmp_num_resp_destinataire))&&(preg_match("/^[0-9]{1,}$/", $tmp_num_resp_destinataire))&&(isset($tab_bull['eleve'][$i]['resp']["adresses"]["adresse"][$tmp_num_resp_destinataire]))) {
		// On imprime pour un responsable particulier (éventuellement un couple à la même adresse)
		$tab_adr_lignes=array();
		$tab_adr_lignes[$tmp_num_resp_destinataire]=$tab_bull['eleve'][$i]['resp']["adresses"]["adresse"][$tmp_num_resp_destinataire]["adresse"];

		$nb_bulletins=1;
	}

	if((isset($seulement_resp_2_autre_adresse))&&($seulement_resp_2_autre_adresse=='oui')&&(isset($tab_bull['eleve'][$i]['resp']["adresses"]["adresse"][2]))) {
		// On imprime pour le responsable n°2 seulement
		$tab_adr_lignes=array();
		$tab_adr_lignes[2]=$tab_bull['eleve'][$i]['resp']["adresses"]["adresse"][2]["adresse"];
		$tmp_num_resp_destinataire=2;

		$nb_bulletins=1;
	}

	//echo "nb_bulletins=$nb_bulletins<br />\n";

	/*
	if(in_array($tab_bull['eleve'][$i]['login'] ,array("baillyc","bouyj2","caborete","barbatty"))) {
		echo "<p>bulletin_pdf(\$tab_bull,$i,\$tab_rel)<br />\$tmp_num_resp_destinataire=$tmp_num_resp_destinataire<br />".$tab_bull['nom_periode']."</p>";
		echo "<p>\$tab_adr_lignes"."</p>";
		echo "<pre>";
		print_r($tab_adr_lignes);
		echo "</pre>";
	}
	*/

	//=========================================
	//nombre de matieres à afficher
	$nb_matiere=0;
	//$fich=fopen("/tmp/infos_matieres_eleve.txt","a+");
	//fwrite($fich,"\$tab_bull['eleve'][$i]['nom']=".$tab_bull['eleve'][$i]['nom']."\n");
	//$tab_bull['eleve'][$i]['cat_id']=array();
	for($m=0;$m<count($tab_bull['groupe']);$m++) {
		//if(isset($tab_bull['note'][$m][$i])) {
		// On n'affiche pas ici les groupes correspondant à AP, EPI ou Parcours
		if((isset($tab_bull['note'][$m][$i]))&&
		((!isset($tab_bull['groupe'][$m]['type_grp'][0]))||($tab_bull['groupe'][$m]['type_grp'][0]['nom_court']=="local"))) {
			// Si l'élève suit l'option, sa note est affectée (éventuellement vide)
			//fwrite($fich,"\$tab_bull['groupe'][$m]['matiere']['matiere']=".$tab_bull['groupe'][$m]['matiere']['matiere']." ");
			//fwrite($fich,"\$tab_bull['note'][$m][$i]=".$tab_bull['note'][$m][$i]."\n");
			$nb_matiere++;

			/*
			if(isset($tab_bull['cat_id'][$m])) {
				if(!in_array($tab_bull['cat_id'][$m], $tab_bull['eleve'][$i]['cat_id'])) {
					$tab_bull['eleve'][$i]['cat_id'][]=$tab_bull['cat_id'][$m];
				}
			}
			*/
		}
	}
	//$nb_categories_eleve_courant=count($tab_bull['eleve'][$i]['cat_id']);

	// DEBUG 20160220
	/*
	echo "\$tab_bull['groupe'][0]<pre>";
	print_r($tab_bull['groupe'][0]);
	echo "</pre>";
	*/


	$nb_AID_b_non_AP_EPI_Parcours=0;
	$nb_AID_AP_EPI_Parcours=0;
	$indice_AID_b_AP=array();
	$indice_AID_b_EPI=array();
	$indice_AID_b_Parcours=array();
	$indice_AID_e_AP=array();
	$indice_AID_e_EPI=array();
	$indice_AID_e_Parcours=array();
	// A REVOIR : Certains AID devraient pouvoir être tagués pour apparaitre en page 2 dans les AP, EPI, Parcours personnalisés
	if(isset($tab_bull['eleve'][$i]['aid_b'])) {
		//$nb_matiere+=count($tab_bull['eleve'][$i]['aid_b']);
		for($loop=0;$loop<count($tab_bull['eleve'][$i]['aid_b']);$loop++) {
			// Repérer les AP, EPI,...
			if($tab_bull['eleve'][$i]['aid_b'][$loop]['type_aid']==0) {
				$nb_matiere++;
				$nb_AID_b_non_AP_EPI_Parcours++;
			}
			else {
				$nb_AID_AP_EPI_Parcours++;
				if($tab_bull['eleve'][$i]['aid_b'][$loop]['type_aid']==1) {
					$indice_AID_b_AP[]=$loop;
				}
				elseif($tab_bull['eleve'][$i]['aid_b'][$loop]['type_aid']==2) {
					$indice_AID_b_EPI[]=$loop;
				}
				elseif($tab_bull['eleve'][$i]['aid_b'][$loop]['type_aid']==3) {
					$indice_AID_b_Parcours[]=$loop;
				}
			}
		}
	}

	$nb_AID_e_non_AP_EPI_Parcours=0;
	if(isset($tab_bull['eleve'][$i]['aid_e'])) {
		//$nb_matiere+=count($tab_bull['eleve'][$i]['aid_e']);
		for($loop=0;$loop<count($tab_bull['eleve'][$i]['aid_e']);$loop++) {
			// Repérer les AP, EPI,...
			if($tab_bull['eleve'][$i]['aid_e'][$loop]['type_aid']==0) {
				$nb_matiere++;
				$nb_AID_e_non_AP_EPI_Parcours++;
			}
			else {
				$nb_AID_AP_EPI_Parcours++;
				if($tab_bull['eleve'][$i]['aid_e'][$loop]['type_aid']==1) {
					$indice_AID_e_AP[]=$loop;
				}
				elseif($tab_bull['eleve'][$i]['aid_e'][$loop]['type_aid']==2) {
					$indice_AID_e_EPI[]=$loop;
				}
				elseif($tab_bull['eleve'][$i]['aid_e'][$loop]['type_aid']==3) {
					$indice_AID_e_Parcours[]=$loop;
				}
			}
		}
	}
/*
echo "<pre>";
print_r($tab_bull['eleve'][$i]['aid_b']);
echo "</pre>";
die();
*/
	/*
	$nb_categories_select=0;
	//$categorie_passe_for='';

	if($tab_modele_pdf["active_regroupement_cote"][$classe_id]==='1' or $tab_modele_pdf["active_entete_regroupement"][$classe_id]==='1') {
		$nb_categories_select=count(array_count_values($tab_bull['cat_id']));
	}
	*/
	//=========================================

	/*
		$val_defaut_champ_bull_pdf["arrondie_choix"]=0.01;
		$val_defaut_champ_bull_pdf["nb_chiffre_virgule"]=2;
		$val_defaut_champ_bull_pdf["chiffre_avec_zero"]=0;
	*/

	/*
	$arrondi=0.01;
	$nb_chiffre_virgule=1;
	$chiffre_avec_zero=0;
	$evolution_moyenne_periode_precedente="y";
	*/

	$arrondi=$param_bull2016["bull2016_arrondi"];
	$nb_chiffre_virgule=$param_bull2016["bull2016_nb_chiffre_virgule"];
	$chiffre_avec_zero=$param_bull2016["bull2016_chiffre_avec_zero"];


	for($num_resp_bull=0;$num_resp_bull<$nb_bulletins;$num_resp_bull++) {
	//for($num_resp_bull=$num_resp_ini;$num_resp_bull<$num_resp_fin;$num_resp_bull++) {

		$pdf->AddPage(); //ajout d'une page au document
		$pdf->SetFont('DejaVu');

		//================================
		/*
		// On insère le footer dès que la page est créée:
		//Positionnement à 1 cm du bas et 0,5cm + 0,5cm du coté gauche
		$pdf->SetXY(5,-10);
		//Police DejaVu Gras 6
		$pdf->SetFont('DejaVu','B',8);
		// $fomule = 'Bulletin à conserver précieusement. Aucun duplicata ne sera délivré. - GEPI : solution libre de gestion et de suivi des résultats scolaires.'
		$pdf->Cell(0,4.5, ($bull_formule_bas),0,0,'C');
		*/
		//================================

		$hauteur_pris=0;

		//=========================================

		// Récupération de l'identifiant de la classe:
		$classe_id=$tab_bull['eleve'][$i]['id_classe'];

		//=========================================

		/*
		// FILIGRANE PERIODE NON CLOSE
		if($tab_bull['verouiller']=="N") {
			$pdf->SetFont('DejaVu','B',40);
			$pdf->SetTextColor(255,192,203);
			//$pdf->TextWithRotation(40,190,$texte_filigrame[$classe_id],45);
			$pdf->TextWithRotation(40,210,"ATTENTION : Période non close",45);
			$pdf->SetTextColor(0,0,0);
		}
		elseif($tab_modele_pdf["affiche_filigrame"][$classe_id]==='1'){
			$pdf->SetFont('DejaVu','B',50);
			$pdf->SetTextColor(255,192,203);
			//$pdf->TextWithRotation(40,190,$texte_filigrame[$classe_id],45);
			$pdf->TextWithRotation(40,190,$tab_modele_pdf["texte_filigrame"][$classe_id],45);
			$pdf->SetTextColor(0,0,0);
		}
		*/

		//=========================================

		// Définition du $cycle par défaut, mais il est modifié/adapté/identifié plus bas d'après le MEF de l'élève
		$cycle=3;

		//=========================================

		// Cadre Logo RF

		$pdf->Rect($param_bull2016["x_cadre_logo_RF"], $param_bull2016["y_cadre_logo_RF"], $param_bull2016["largeur_cadre_logo_RF"], $param_bull2016["hauteur_cadre_logo_RF"], 'D');

		//$logo_RF=$gepiPath."/images/logo_RF.jpg";
		$logo_RF="../images/logo_RF.jpg";
		$valeur=redimensionne_image($logo_RF, 18*3.2, 10*3.2);
		//$X_logo = $param_bull2016["x_logo_RF"];
		//$Y_logo = $param_bull2016["y_logo_RF"];
		$L_logo = $valeur[0];
		$H_logo = $valeur[1];

		$X_logo=$param_bull2016["x_cadre_logo_RF"]+($param_bull2016["largeur_cadre_logo_RF"]-$L_logo)/2;
		$Y_logo=$param_bull2016["y_cadre_logo_RF"]+($param_bull2016["hauteur_cadre_logo_RF"]-$H_logo)/2;

		// centrage du logo
		//$centre_du_logo = ( $H_logo / 2 );
		//$Y_logo = $tab_modele_pdf["Y_centre_logo"][$classe_id] - $centre_du_logo;

		//logo
		$tmp_dim_photo=getimagesize($logo_RF);
		if((isset($tmp_dim_photo[2]))&&($tmp_dim_photo[2]==2)) {
			$pdf->Image($logo_RF, $X_logo, $Y_logo, $L_logo, $H_logo);
		}

		//=========================================

		// Cadre EN

		$pdf->Rect($param_bull2016["x_cadre_EN"], $param_bull2016["y_cadre_EN"], $param_bull2016["largeur_cadre_logo_RF"], $param_bull2016["hauteur_cadre_logo_RF"], 'D');

		$pdf->SetXY($param_bull2016["x_cadre_EN"]+2.5, $param_bull2016["y_cadre_EN"]+1);
		$pdf->SetFont('DejaVu','',6);
		$pdf->SetTextColor(0,0,0);
		$texte="MINISTÈRE\nDE L'ÉDUCATION\nNATIONALE, DE\nL'ENSEIGNEMENT\nSUPÉRIEUR ET DE\nLA RECHERCHE";
		//$pdf->Cell($param_bull2016["largeur_cadre_logo_RF"]-8, $param_bull2016["hauteur_cadre_EN"]-6, $texte,0,2,'C');
		$pdf->drawTextBox($texte, $param_bull2016["largeur_cadre_logo_RF"]-5, $param_bull2016["hauteur_cadre_EN"]-4, 'C', 'M', 0);

		//=========================================

		// Section Académie, établissement, adresse étab

		$pdf->SetXY($param_bull2016["x_cadre_etab"], $param_bull2016["y_cadre_etab_academie"]);
		$pdf->SetFont('DejaVu','',9);
		$pdf->SetTextColor(0,0,0);
		$pdf->Cell($param_bull2016["largeur_cadre_etab"],7, "Académie de ".$gepiSchoolAcademie,0,2,'L');

		$pdf->SetXY($param_bull2016["x_cadre_etab"], $param_bull2016["y_cadre_etab_college"]);
		$pdf->SetFont('DejaVu','B',10);
		$pdf->SetTextColor(0,0,0);
		$pdf->Cell($param_bull2016["largeur_cadre_etab"],7, $gepiSchoolName,0,2,'L');

		$pdf->SetXY($param_bull2016["x_cadre_etab"], $param_bull2016["y_cadre_etab_adresse_college"]);
		$pdf->SetFont('DejaVu','',7);
		$pdf->SetTextColor(0,0,0);
		$adresse_etab="";
		if($gepiSchoolAdress1!="") {
			$adresse_etab=$gepiSchoolAdress1;
			if($gepiSchoolAdress2!="") {
				$adresse_etab.=", ".$gepiSchoolAdress2;
			}
		}
		elseif($gepiSchoolAdress2!="") {
			$adresse_etab=$gepiSchoolAdress2;
		}
		$pdf->Cell($param_bull2016["largeur_cadre_etab"], 7, $adresse_etab, 0, 2, 'L');

		$pdf->SetXY($param_bull2016["x_cadre_etab"], $param_bull2016["y_cadre_etab_cp_commune_college"]);
		$pdf->SetFont('DejaVu','',7);
		$pdf->SetTextColor(0,0,0);
		$cp_commune_etab="";
		if($gepiSchoolZipCode!="") {
			$cp_commune_etab=$gepiSchoolZipCode;
			if($gepiSchoolCity!="") {
				$cp_commune_etab.=" ".$gepiSchoolCity;
			}
		}
		elseif($gepiSchoolCity!="") {
			$cp_commune_etab=$gepiSchoolCity;
		}
		$pdf->Cell($param_bull2016["largeur_cadre_etab"], 7, $cp_commune_etab, 0, 2, 'L');

		$pdf->SetXY($param_bull2016["x_cadre_etab"], $param_bull2016["y_cadre_etab_tel_college"]);
		$pdf->SetFont('DejaVu','',7);
		$pdf->SetTextColor(0,0,0);
		$tel_etab="";
		if($gepiSchoolTel!="") {
			$tel_etab=$gepiSchoolTel;
			if($gepiSchoolFax!="") {
				$tel_etab.=" (fax:".$gepiSchoolFax.")";
			}
		}
		elseif($gepiSchoolFax!="") {
			$tel_etab="(fax:".$gepiSchoolFax.")";
		}
		$pdf->Cell($param_bull2016["largeur_cadre_etab"], 7, $tel_etab, 0, 2, 'L');

		$pdf->SetXY($param_bull2016["x_cadre_etab"], $param_bull2016["y_cadre_etab_email_college"]);
		$pdf->SetFont('DejaVu','',7);
		$pdf->SetTextColor(0,0,0);
		$pdf->Cell($param_bull2016["largeur_cadre_etab"], 7, $gepiSchoolEmail, 0, 2, 'L');

		//=========================================

		// Section cycle et niveau
		// Debug
		/*
		echo "<pre>";
		print_r($tab_mef);
		echo "</pre>";
		echo "\$tab_bull['eleve'][$i]['mef_code']=".$tab_bull['eleve'][$i]['mef_code']."<br />";
		*/
		/*
		$mef_code_ele=$tab_bull['eleve'][$i]['mef_code'];
		if((isset($tab_mef[$mef_code_ele]["mef_rattachement"]))&&($tab_mef[$mef_code_ele]["mef_rattachement"]!="")) {
			if($tab_mef[$mef_code_ele]["mef_rattachement"]=="10010012110") {
				// C'est une classe de 6ème
				$cycle=3;
				$niveau=6;
			}
			elseif($tab_mef[$mef_code_ele]["mef_rattachement"]=="10110001110") {
				$cycle=4;
				$niveau=5;
			}
			elseif($tab_mef[$mef_code_ele]["mef_rattachement"]=="10210001110") {
				$cycle=4;
				$niveau=4;
			}
			elseif($tab_mef[$mef_code_ele]["mef_rattachement"]=="10310019110") {
				$cycle=4;
				$niveau=3;
			}
			else {
				// Pour le moment, on suppose que c'est un cycle 4 et même un élève de 3ème
				// On verra plus tard le cas d'un Gepi en Lycée
				//$cycle=4;
				//$niveau=3;

				// Il vaut mieux ne rien mettre en couleur pour repérer que les cycle et niveau n'ont pas été identifiés
				$cycle="";
				$niveau="";
			}
		}
		elseif($mef_code_ele=="10010012110") {
				// C'est une classe de 6ème
				$cycle=3;
				$niveau=6;
		}
		elseif($mef_code_ele=="10110001110") {
			$cycle=4;
			$niveau=5;
		}
		elseif($mef_code_ele=="10210001110") {
			$cycle=4;
			$niveau=4;
		}
		elseif($mef_code_ele=="10310019110") {
			$cycle=4;
			$niveau=3;
		}
		else {
			// Pour le moment, on suppose que c'est un cycle 4 et même un élève de 3ème
			// On verra plus tard le cas d'un Gepi en Lycée
			//$cycle=4;
			//$niveau=3;

			// Il vaut mieux ne rien mettre en couleur pour repérer que les cycle et niveau n'ont pas été identifiés
			$cycle="";
			$niveau="";
		}
		*/
		$cycle=$tab_bull['eleve'][$i]['mef_cycle'];
		$niveau=$tab_bull['eleve'][$i]['mef_niveau'];
		// Debug
		//echo "cycle=$cycle et niveau=$niveau<br />";

		// Colonne cycle:
		for($loop_cycle=2;$loop_cycle<=4;$loop_cycle++) {
			if($loop_cycle==$cycle) {
				$pdf->SetFillColor($param_bull2016["couleur_cycle_courant"]["cycle"]["R"], $param_bull2016["couleur_cycle_courant"]["cycle"]["V"], $param_bull2016["couleur_cycle_courant"]["cycle"]["B"]);
			}
			else {
				$pdf->SetFillColor($param_bull2016["couleur_cycle_autre"]["R"], $param_bull2016["couleur_cycle_autre"]["V"], $param_bull2016["couleur_cycle_autre"]["B"]);
			}
			$pdf->Rect($param_bull2016["x_colonne_cycle"], $param_bull2016["y_colonne_cycle"]+($loop_cycle-2)*($param_bull2016["cote_carre_cycle"]+$param_bull2016["ecart_carres_cycle"]), $param_bull2016["cote_carre_cycle"], $param_bull2016["cote_carre_cycle"], 'F');
			$pdf->SetFillColor(0, 0, 0);

			$pdf->SetXY($param_bull2016["x_colonne_cycle"], $param_bull2016["y_colonne_cycle"]+($loop_cycle-2)*($param_bull2016["cote_carre_cycle"]+$param_bull2016["ecart_carres_cycle"]));
			$pdf->SetFont('DejaVu','',14);
			$pdf->SetTextColor(255,255,255);
			$pdf->Cell($param_bull2016["cote_carre_cycle"],$param_bull2016["cote_carre_cycle"], $loop_cycle,0,2,'C');

		}

		// Colonne correspondant à $loop_niveau et Ligne à $loop_cycle
		$tab_coord_niveau["cp"]=array("col"=>0, "lig"=>2);
		$tab_coord_niveau["ce1"]=array("col"=>1, "lig"=>2);
		$tab_coord_niveau["ce2"]=array("col"=>2, "lig"=>2);
		$tab_coord_niveau["cm1"]=array("col"=>0, "lig"=>3);
		$tab_coord_niveau["cm2"]=array("col"=>1, "lig"=>3);
		$tab_coord_niveau["6"]=array("col"=>2, "lig"=>3);
		$tab_coord_niveau["5"]=array("col"=>0, "lig"=>4);
		$tab_coord_niveau["4"]=array("col"=>1, "lig"=>4);
		$tab_coord_niveau["3"]=array("col"=>2, "lig"=>4);
/*
echo "<br /><p>DEBUG : ".$tab_bull['eleve'][$i]["login"]."<br />";
echo "Cycle=$cycle et niveau=$niveau<br />";
echo "<pre>";
print_r($tab_coord_niveau[$niveau]);
echo "</pre>";
*/
		// Colonnes niveau:
		for($loop_cycle=2;$loop_cycle<=4;$loop_cycle++) {
			for($loop_niveau=0;$loop_niveau<3;$loop_niveau++) {
				//if(($loop_cycle==$cycle)&&(isset($param_bull2016["cycles_et_niveaux"][$loop_cycle][$loop_niveau]["texte"]))&&($param_bull2016["cycles_et_niveaux"][$loop_cycle][$loop_niveau]["texte"]==$niveau)) {

//echo "loop_niveau=".$loop_niveau." et loop_cycle=".$loop_cycle."<br />";

				if((isset($tab_coord_niveau[$niveau]))&&
				($tab_coord_niveau[$niveau]["col"]==$loop_niveau)&&
				($tab_coord_niveau[$niveau]["lig"]==$loop_cycle)) {
//echo "On y est.<br />";

					$pdf->SetFillColor($param_bull2016["couleur_cycle_courant"]["niveau"]["R"], $param_bull2016["couleur_cycle_courant"]["niveau"]["V"], $param_bull2016["couleur_cycle_courant"]["niveau"]["B"]);
				}
				else {
					$pdf->SetFillColor($param_bull2016["couleur_cycle_autre"]["R"], $param_bull2016["couleur_cycle_autre"]["V"], $param_bull2016["couleur_cycle_autre"]["B"]);
				}
				$pdf->Rect($param_bull2016["x_colonne_cycle"]+($loop_niveau+1)*($param_bull2016["cote_carre_cycle"]+$param_bull2016["ecart_carres_cycle"]), $param_bull2016["y_colonne_cycle"]+($loop_cycle-2)*($param_bull2016["cote_carre_cycle"]+$param_bull2016["ecart_carres_cycle"]), $param_bull2016["cote_carre_cycle"], $param_bull2016["cote_carre_cycle"], 'F');
				$pdf->SetFillColor(0, 0, 0);

				$pdf->SetXY($param_bull2016["x_colonne_cycle"]+($loop_niveau+1)*($param_bull2016["cote_carre_cycle"]+$param_bull2016["ecart_carres_cycle"]), $param_bull2016["y_colonne_cycle"]+($loop_cycle-2)*($param_bull2016["cote_carre_cycle"]+$param_bull2016["ecart_carres_cycle"]));
				$pdf->SetFont('DejaVu','',14);
				$pdf->SetTextColor(255,255,255);
				$pdf->Cell($param_bull2016["cote_carre_cycle"],$param_bull2016["cote_carre_cycle"], $param_bull2016["cycles_et_niveaux"][$loop_cycle][$loop_niveau]["texte"],0,2,'C');
			}
		}


		//=========================================

		// Cadre Logo établissement

		//======================================
		// 20180124
		if ((isset($GLOBALS['multisite']))&&($GLOBALS['multisite'] == 'y')&&(isset($_COOKIE['RNE']))) {
			$chemin_logo = '../images/'.$_COOKIE['RNE'].'/';
		}
		else {
			$chemin_logo = '../images/';
		}
		$logo = $chemin_logo.getSettingValue('logo_etab');
		//======================================
		$format_du_logo = mb_strtolower(str_replace('.','',strstr(getSettingValue('logo_etab'), '.')));

		// Logo
		//if($tab_modele_pdf["affiche_logo_etab"][$classe_id]==='1' and file_exists($logo) and getSettingValue('logo_etab') != '' and ($format_du_logo==='jpg' or $format_du_logo==='png')) {
		if($param_bull2016["affiche_logo_etab"]==1 and file_exists($logo) and getSettingValue('logo_etab') != '' and ($format_du_logo==='jpg' or $format_du_logo==='png')) {
			$valeur=redimensionne_image($logo, ($param_bull2016["largeur_max_logo_etab"]*2.8), ($param_bull2016["hauteur_max_logo_etab"]*2.8));
			$X_logo = $param_bull2016["x_logo_etab"];
			$Y_logo = $param_bull2016["y_logo_etab"];
			$L_logo = $valeur[0];
			$H_logo = $valeur[1];
			//$X_etab = $X_logo + $L_logo + 1;
			//$Y_etab = $Y_logo;

			/*
			if ( !isset($tab_modele_pdf["centrage_logo"][$classe_id]) or empty($tab_modele_pdf["centrage_logo"][$classe_id]) ) {
				$tab_modele_pdf["centrage_logo"][$classe_id] = '0';
			}

			if ( $tab_modele_pdf["centrage_logo"][$classe_id] === '1' ) {
				// centrage du logo
				$centre_du_logo = ( $H_logo / 2 );
				$Y_logo = $tab_modele_pdf["Y_centre_logo"][$classe_id] - $centre_du_logo;
			}
			*/

			//logo
			$tmp_dim_photo=getimagesize($logo);
			if((isset($tmp_dim_photo[2]))&&($tmp_dim_photo[2]==2)) {
				$pdf->Image($logo, $X_logo, $Y_logo, $L_logo, $H_logo);
			}
		}

		//=========================================

		// Cadre Année, période, identité élève, pp et classe
		$pdf->SetFillColor($param_bull2016["couleur_cadre_identite"]["R"], $param_bull2016["couleur_cadre_identite"]["V"], $param_bull2016["couleur_cadre_identite"]["B"]);

		$pdf->Rect($param_bull2016["x_cadre_eleve"], $param_bull2016["y_cadre_eleve"], $param_bull2016["largeur_cadre_eleve"], $param_bull2016["hauteur_cadre_eleve"], 'F');

		if($param_bull2016["afficher_cadre_adresse_resp"]=="y") {
			$largeur_cadre_eleve=$param_bull2016["x_cadre_eleve"]+$param_bull2016["largeur_cadre_eleve"]-$param_bull2016["x_cadre_adresse_resp"];
		}
		else {
			$largeur_cadre_eleve=$param_bull2016["largeur_cadre_eleve"];
		}

		$pdf->SetFillColor(0, 0, 0);

		$pdf->SetXY($param_bull2016["x_cadre_eleve"], $param_bull2016["y_annee_scolaire"]);
		$pdf->SetFont('DejaVu','B',10);
		$pdf->SetTextColor(0,0,0);
		$pdf->Cell($largeur_cadre_eleve,7, "Année scolaire ".$gepiYear,0,2,'C');

		$pdf->SetXY($param_bull2016["x_cadre_eleve"], $param_bull2016["y_periode"]);
		$pdf->SetFont('DejaVu','',8);
		$pdf->SetTextColor(0,0,0);
		//$pdf->Cell($param_bull2016["largeur_cadre_eleve"],7, "Bilan trimestriel du cycle ".$num_cycle." - ".$tab_bull['num_periode']." trimestre",0,2,'C');
		if($tab_bull['nb_periodes']==2) {
			$trimestriel_ou_semestriel="semestriel";
		}
		else {
			$trimestriel_ou_semestriel="trimestriel";
		}
		$pdf->Cell($largeur_cadre_eleve,7, "Bilan ".$trimestriel_ou_semestriel." du cycle ".$cycle." - ".$tab_bull['nom_periode'],0,2,'C');

		$pdf->SetXY($param_bull2016["x_cadre_eleve"], $param_bull2016["y_nom_prenom_eleve"]);
		$pdf->SetFont('DejaVu','B',12);
		$pdf->SetTextColor(0,0,0);
		$pdf->Cell($largeur_cadre_eleve,7, $tab_bull['eleve'][$i]['prenom']." ".$tab_bull['eleve'][$i]['nom'],0,2,'C');

		$info_naissance="Né";
		if($tab_bull['eleve'][$i]['sexe']=="F") {$info_naissance.="e";}
		$info_naissance.=" le ".$tab_bull['eleve'][$i]['naissance'];
		/*
		if((getSettingValue('ele_lieu_naissance')=='y')&&($tab_modele_pdf["affiche_lieu_naissance"][$classe_id]==='1')) {
			$info_naissance.=" à ".$tab_bull['eleve'][$i]['lieu_naissance'];
		}
		*/

		$info_ligne_2_eleve=$info_naissance;
		if($param_bull2016["bull2016_INE"]=="y") {
			$info_ligne_2_eleve.=" - INE : ".$tab_bull['eleve'][$i]['no_gep'];
		}

		$pdf->SetXY($param_bull2016["x_cadre_eleve"], $param_bull2016["y_naissance_eleve"]);
		$pdf->SetFont('DejaVu','',8);
		$pdf->Cell($largeur_cadre_eleve,7, $info_ligne_2_eleve,0,2,'C');


		//if($tab_modele_pdf["afficher_tous_profprincipaux"][$classe_id]==1) {
			$index_pp='pp_classe';
		/*
		}
		else {
			$index_pp='pp';
		}
		*/
		if(isset($tab_bull['eleve'][$i][$index_pp][0]['login'])) {
			$pp_classe[$i]=ucfirst($tab_bull['gepi_prof_suivi'])." : ";
			$pp_classe[$i].=affiche_utilisateur($tab_bull['eleve'][$i][$index_pp][0]['login'],$tab_bull['eleve'][$i]['id_classe']);
			for($i_pp=1;$i_pp<count($tab_bull['eleve'][$i][$index_pp]);$i_pp++) {
				$pp_classe[$i].=", ";
				$pp_classe[$i].=affiche_utilisateur($tab_bull['eleve'][$i][$index_pp][$i_pp]['login'],$tab_bull['eleve'][$i]['id_classe']);
			}
		}
		else {
			$pp_classe[$i]="";
		}
		$pdf->SetXY($param_bull2016["x_cadre_eleve"], $param_bull2016["y_pp"]);
		$pdf->Cell($largeur_cadre_eleve,7, $pp_classe[$i],0,2,'C');

		$pdf->SetXY($param_bull2016["x_cadre_eleve"], $param_bull2016["y_classe"]);
		$pdf->SetFont('DejaVu','',11);
		$pdf->Cell($largeur_cadre_eleve,7, "Classe de ".unhtmlentities($tab_bull['eleve'][$i]['classe']),0,2,'C');

		//=========================================
		if($param_bull2016["afficher_cadre_adresse_resp"]=="y") {

			// 20161013

			$texte="Adresse manquante";
			if((isset($tmp_num_resp_destinataire))&&(preg_match("/^[0-9]{1,}$/", $tmp_num_resp_destinataire))&&(isset($tab_adr_lignes[$tmp_num_resp_destinataire]))) {
				$texte=$tab_adr_lignes[$tmp_num_resp_destinataire];
			}
			elseif(isset($tab_adr_lignes[$num_resp_bull])) {
				$texte=$tab_adr_lignes[$num_resp_bull];
			}
			$taille_max_police=10;
			//$taille_max_police=$tab_modele_pdf["adresse_resp_fontsize"][$classe_id];
			$taille_min_police=ceil($taille_max_police/3);

			$largeur_dispo=$param_bull2016["largeur_cadre_adresse_resp"];
			$h_cell=$param_bull2016["hauteur_cadre_adresse_resp"];

			cell_ajustee($texte, $param_bull2016["x_cadre_adresse_resp"], $param_bull2016["y_cadre_adresse_resp"], $largeur_dispo, $h_cell, $taille_max_police, $taille_min_police, $param_bull2016["bordure_cadre_adresse_resp"],'C','L',0.3,1);

		}
		//=========================================

		// Bandeau Suivi des acquis

		$pdf->SetFillColor($param_bull2016["couleur_bandeau_suivi_acquis"]["R"], $param_bull2016["couleur_bandeau_suivi_acquis"]["V"], $param_bull2016["couleur_bandeau_suivi_acquis"]["B"]);

		$pdf->Rect($param_bull2016["x_bandeau_suivi_acquis"], $param_bull2016["y_bandeau_suivi_acquis"], $param_bull2016["largeur_bandeau_suivi_acquis"], $param_bull2016["hauteur_bandeau_suivi_acquis"], 'F');

		$pdf->SetFillColor(0, 0, 0);
		$pdf->SetTextColor(255, 255, 255);
		$pdf->SetXY($param_bull2016["x_bandeau_suivi_acquis"], $param_bull2016["y_bandeau_suivi_acquis"]+1);
		$pdf->SetFont('DejaVu','B',12);
		$pdf->Cell($param_bull2016["largeur_bandeau_suivi_acquis"],7, "Suivi des acquis scolaires de l'élève",0,2,'C');

		//=========================================

		// Tableau des acquis
		// Ligne de titre

		// Colonne 2 : Éléments de programmes
		$pdf->SetFillColor($param_bull2016["couleur_acquis_ligne_entete"]["R"], $param_bull2016["couleur_acquis_ligne_entete"]["V"], $param_bull2016["couleur_acquis_ligne_entete"]["B"]);
		$pdf->Rect($param_bull2016["x_acquis_col_2"], $param_bull2016["y_acquis_ligne_entete"], $param_bull2016["largeur_acquis_col_2"], $param_bull2016["hauteur_acquis_ligne_entete"], 'F');
		$pdf->SetFillColor(0, 0, 0);

		$pdf->SetTextColor(0, 0, 0);
		$pdf->SetXY($param_bull2016["x_acquis_col_2"], $param_bull2016["y_acquis_ligne_entete"]);
		$pdf->SetFont('DejaVu','',7);
		//$pdf->Cell($param_bull2016["largeur_bandeau_suivi_acquis"],7, "Suivi des acquis scolaires de l'élève",0,2,'C');
		//$texte="Éléments du programme travaillés durant\nla période (connaissances/compétences)";
		$texte="Éléments du programme travaillés durant la période (connaissances/compétences)";
		//$pdf->Cell($param_bull2016["largeur_cadre_logo_RF"]-8, $param_bull2016["hauteur_cadre_EN"]-6, $texte,0,2,'C');
		//$pdf->drawTextBox($texte, $param_bull2016["largeur_acquis_col_2"], $param_bull2016["hauteur_acquis_ligne_entete"], 'L', 'M', 0);
		$taille_max_police=7;
		$cell_ajustee_texte_matiere_ratio_min_max=3;
		$taille_min_police=ceil($taille_max_police/$cell_ajustee_texte_matiere_ratio_min_max);
		$largeur_dispo=$param_bull2016["largeur_acquis_col_2"];
		$h_cell=$param_bull2016["hauteur_acquis_ligne_entete"];
		cell_ajustee($texte,$pdf->GetX(),$pdf->GetY(),$largeur_dispo,$h_cell,$taille_max_police,$taille_min_police,'');


		// Colonne 3 : Appréciation
		$pdf->SetFillColor($param_bull2016["couleur_acquis_ligne_entete"]["R"], $param_bull2016["couleur_acquis_ligne_entete"]["V"], $param_bull2016["couleur_acquis_ligne_entete"]["B"]);
		$pdf->Rect($param_bull2016["x_acquis_col_3"], $param_bull2016["y_acquis_ligne_entete"], $param_bull2016["largeur_acquis_col_3"], $param_bull2016["hauteur_acquis_ligne_entete"], 'F');
		$pdf->SetFillColor(0, 0, 0);

		$pdf->SetTextColor(0, 0, 0);
		$pdf->SetXY($param_bull2016["x_acquis_col_3"], $param_bull2016["y_acquis_ligne_entete"]);
		$pdf->SetFont('DejaVu','',7);
		//$pdf->Cell($param_bull2016["largeur_bandeau_suivi_acquis"],7, "Suivi des acquis scolaires de l'élève",0,2,'C');
		$texte="Acquisitions, progrès et difficultés éventuelles";
		//$pdf->Cell($param_bull2016["largeur_cadre_logo_RF"]-8, $param_bull2016["hauteur_cadre_EN"]-6, $texte,0,2,'C');
		//$pdf->drawTextBox($texte, $param_bull2016["largeur_acquis_col_3"], $param_bull2016["hauteur_acquis_ligne_entete"], 'L', 'M', 0);
		$taille_max_police=7;
		$cell_ajustee_texte_matiere_ratio_min_max=3;
		$taille_min_police=ceil($taille_max_police/$cell_ajustee_texte_matiere_ratio_min_max);
		$largeur_dispo=$param_bull2016["largeur_acquis_col_3"];
		$h_cell=$param_bull2016["hauteur_acquis_ligne_entete"];
		cell_ajustee($texte,$pdf->GetX(),$pdf->GetY(),$largeur_dispo,$h_cell,$taille_max_police,$taille_min_police,'');


		// Colonne 4 : Moyenne élève
		$pdf->SetFillColor($param_bull2016["couleur_acquis_colonne_moyenne_ligne_entete"]["R"], $param_bull2016["couleur_acquis_colonne_moyenne_ligne_entete"]["V"], $param_bull2016["couleur_acquis_colonne_moyenne_ligne_entete"]["B"]);
		$pdf->Rect($param_bull2016["x_acquis_col_moy"], $param_bull2016["y_acquis_ligne_entete"], $param_bull2016["largeur_acquis_col_moy"], $param_bull2016["hauteur_acquis_ligne_entete"], 'F');
		$pdf->SetFillColor(0, 0, 0);

		$pdf->SetTextColor(0, 0, 0);
		$pdf->SetXY($param_bull2016["x_acquis_col_moy"], $param_bull2016["y_acquis_ligne_entete"]);
		$pdf->SetFont('DejaVu','',7);
		//$pdf->Cell($param_bull2016["largeur_bandeau_suivi_acquis"],7, "Suivi des acquis scolaires de l'élève",0,2,'C');
		$texte="Moyenne\nde l'élève";
		//$pdf->Cell($param_bull2016["largeur_cadre_logo_RF"]-8, $param_bull2016["hauteur_cadre_EN"]-6, $texte,0,2,'C');
		//$pdf->drawTextBox($texte, $param_bull2016["largeur_acquis_col_moy"], $param_bull2016["hauteur_acquis_ligne_entete"], 'C', 'M', 0);
		$taille_max_police=7;
		$cell_ajustee_texte_matiere_ratio_min_max=3;
		$taille_min_police=ceil($taille_max_police/$cell_ajustee_texte_matiere_ratio_min_max);
		$largeur_dispo=$param_bull2016["largeur_acquis_col_moy"];
		$h_cell=$param_bull2016["hauteur_acquis_ligne_entete"];
		cell_ajustee($texte,$pdf->GetX(),$pdf->GetY(),$largeur_dispo,$h_cell,$taille_max_police,$taille_min_police,'','C','C');

		// Colonne 5 : Moyenne classe
		$pdf->SetFillColor($param_bull2016["couleur_acquis_ligne_entete"]["R"], $param_bull2016["couleur_acquis_ligne_entete"]["V"], $param_bull2016["couleur_acquis_ligne_entete"]["B"]);
		$pdf->Rect($param_bull2016["x_acquis_col_moyclasse"], $param_bull2016["y_acquis_ligne_entete"], $param_bull2016["largeur_acquis_col_moyclasse"], $param_bull2016["hauteur_acquis_ligne_entete"], 'F');
		$pdf->SetFillColor(0, 0, 0);

		$pdf->SetTextColor(0, 0, 0);
		$pdf->SetXY($param_bull2016["x_acquis_col_moyclasse"], $param_bull2016["y_acquis_ligne_entete"]);
		$pdf->SetFont('DejaVu','',7);
		//$pdf->Cell($param_bull2016["largeur_bandeau_suivi_acquis"],7, "Suivi des acquis scolaires de l'élève",0,2,'C');
		$texte="Moyenne\nde classe";
		//$pdf->Cell($param_bull2016["largeur_cadre_logo_RF"]-8, $param_bull2016["hauteur_cadre_EN"]-6, $texte,0,2,'C');
		//$pdf->drawTextBox($texte, $param_bull2016["largeur_acquis_col_moyclasse"], $param_bull2016["hauteur_acquis_ligne_entete"], 'C', 'M', 0);
		$taille_max_police=7;
		$cell_ajustee_texte_matiere_ratio_min_max=3;
		$taille_min_police=ceil($taille_max_police/$cell_ajustee_texte_matiere_ratio_min_max);
		$largeur_dispo=$param_bull2016["largeur_acquis_col_moyclasse"];
		$h_cell=$param_bull2016["hauteur_acquis_ligne_entete"];
		cell_ajustee($texte,$pdf->GetX(),$pdf->GetY(),$largeur_dispo,$h_cell,$taille_max_police,$taille_min_police,'','C','C');

		// +++++++++++++++++

		// Lignes matières

		// Initialisation
		$y0=$param_bull2016["y_acquis_ligne_entete_app_classe"]+$h_cell;

		// Calcul de la hauteur des lignes matières (18mm par défaut, mais si ça ne tient pas, on réduit
		if($nb_matiere>0) {
			$y0=$param_bull2016["y_acquis_ligne_entete"]+$param_bull2016["hauteur_acquis_ligne_entete"];
			$ymax=$y0+$nb_matiere*(18+0.5);
			if($ymax<HauteurPage-$y0-10) {
			//if($ymax<HauteurPage-$y0-5) {
				$hauteur_matiere=18;
			}
			else {
				//$hauteur_matiere=(HauteurPage-$y0-5-($nb_matiere-1)*0.5)/$nb_matiere;
				$hauteur_matiere=(HauteurPage-$y0-10-($nb_matiere-1)*0.5)/$nb_matiere;
			}
		}

		$cpt_matiere=0;
		$y_courant=$y0+0.5;

		// Début des AID_b
		if($nb_AID_b_non_AP_EPI_Parcours>0) {
			if(isset($tab_bull['eleve'][$i]['aid_b'])) {
				for($m=0;$m<count($tab_bull['eleve'][$i]['aid_b']);$m++) {
					if($tab_bull['eleve'][$i]['aid_b'][$m]["type_aid"]==0) {
						// Colonne 1 : Matière, prof

						$pdf->SetFillColor($param_bull2016["couleur_acquis_ligne_alt".($cpt_matiere%2+1)]["R"], $param_bull2016["couleur_acquis_ligne_alt".($cpt_matiere%2+1)]["V"], $param_bull2016["couleur_acquis_ligne_alt".($cpt_matiere%2+1)]["B"]);
						$pdf->Rect($param_bull2016["x_acquis_col_1"], $y_courant, $param_bull2016["largeur_acquis_col_1"], $hauteur_matiere, 'F');
						$pdf->SetFillColor(0, 0, 0);

						$pdf->SetTextColor(0, 0, 0);
						$pdf->SetXY($param_bull2016["x_acquis_col_1"], $y_courant);
						$pdf->SetFont('DejaVu','',8);
						/*
						//$pdf->Cell($param_bull2016["largeur_bandeau_suivi_acquis"],7, "Suivi des acquis scolaires de l'élève",0,2,'C');
						$texte="Matiere $m ".$hauteur_matiere;
						$pdf->drawTextBox($texte, $param_bull2016["largeur_acquis_col_1"], $hauteur_matiere, 'L', 'M', 0);
						*/

						if($tab_bull['eleve'][$i]['aid_b'][$m]['display_nom']=='z') {
							if((isset($tab_bull['eleve'][$i]['aid_b'][$m]['aid_nom']))&&($tab_bull['eleve'][$i]['aid_b'][$m]['aid_nom']!="")) {
								$info_nom_matiere=$tab_bull['eleve'][$i]['aid_b'][$m]['aid_nom'];
							}
							elseif((isset($tab_bull['eleve'][$i]['aid_b'][$m]['nom_complet']))&&($tab_bull['eleve'][$i]['aid_b'][$m]['nom_complet']!="")) {
								$info_nom_matiere=$tab_bull['eleve'][$i]['aid_b'][$m]['nom_complet'];
							}
							else {
								$info_nom_matiere=$tab_bull['eleve'][$i]['aid_b'][$m]['nom'];
							}
						}
						elseif($tab_bull['eleve'][$i]['aid_b'][$m]['display_nom']=='x') {
							if((isset($tab_bull['eleve'][$i]['aid_b'][$m]['nom']))&&($tab_bull['eleve'][$i]['aid_b'][$m]['nom']!="")) {
								$info_nom_matiere=$tab_bull['eleve'][$i]['aid_b'][$m]['nom'];
							}
							else {
								$info_nom_matiere=$tab_bull['eleve'][$i]['aid_b'][$m]['nom_complet'];
							}

							if((isset($tab_bull['eleve'][$i]['aid_b'][$m]['aid_nom']))&&($tab_bull['eleve'][$i]['aid_b'][$m]['aid_nom']!="")) {
								if($info_nom_matiere!="") {
									$info_nom_matiere.=": ";
								}
								$info_nom_matiere.=$tab_bull['eleve'][$i]['aid_b'][$m]['aid_nom'];
							}
						}
						else {
							if((isset($tab_bull['eleve'][$i]['aid_b'][$m]['nom_complet']))&&($tab_bull['eleve'][$i]['aid_b'][$m]['nom_complet']!="")) {
								$info_nom_matiere=$tab_bull['eleve'][$i]['aid_b'][$m]['nom_complet'];
							}
							else {
								$info_nom_matiere=$tab_bull['eleve'][$i]['aid_b'][$m]['nom'];
							}
						}
						if($info_nom_matiere=="") {
							$info_nom_matiere="AID";
						}

						$hauteur_caractere_matiere=8;
						$cell_ajustee_texte_matiere_ratio_min_max=3;

						// 20130927 : cell_ajustee() ou pas sur le nom de matière/enseignement
						$cell_ajustee_texte_matiere=1;
						if((isset($cell_ajustee_texte_matiere))&&($cell_ajustee_texte_matiere==1)) {
							// On met un décalage pour ne pas coller le texte à la bordure
							$Y_decal_cell_ajustee=2;
							$pdf->SetXY($param_bull2016["x_acquis_col_1"], $y_courant+$Y_decal_cell_ajustee);

							$texte=$info_nom_matiere;
							$taille_max_police=$hauteur_caractere_matiere;
							$taille_min_police=ceil($taille_max_police/$cell_ajustee_texte_matiere_ratio_min_max);

							$largeur_dispo=$param_bull2016["largeur_acquis_col_1"];
							$h_cell=$hauteur_matiere/2-$Y_decal_cell_ajustee;

							cell_ajustee($texte,$pdf->GetX(),$pdf->GetY(),$largeur_dispo,$h_cell,$taille_max_police,$taille_min_police,'');
				
						}
						else {
							$val = $pdf->GetStringWidth($info_nom_matiere);
							$taille_texte = $param_bull2016["largeur_acquis_col_1"]-2;
							$grandeur_texte='test';
							while($grandeur_texte!='ok') {
								if($taille_texte<$val)
								{
									$hauteur_caractere_matiere = $hauteur_caractere_matiere-0.3;
									$pdf->SetFont('DejaVu','B',$hauteur_caractere_matiere);
									$val = $pdf->GetStringWidth($info_nom_matiere);
								}
								else {
									$grandeur_texte='ok';
								}
							}
							$grandeur_texte='test';
							$Y_decal=$y_courant;
							$pdf->SetXY($param_bull2016["x_acquis_col_1"], $Y_decal);
							$pdf->Cell($param_bull2016["largeur_acquis_col_1"], $hauteur_matiere/2, ($info_nom_matiere),'',1,'L');
						}
				

						// On note l'ordonnée pour le nom des professeurs
						$Y_decal = $y_courant+($hauteur_matiere/2);
						$pdf->SetXY($param_bull2016["x_acquis_col_1"], $Y_decal);
						$pdf->SetFont('DejaVu','',8);

						// nom des professeurs
						if ( isset($tab_bull['eleve'][$i]['aid_b'][$m]['aid_prof_resp_login'][0]) )
						{

							// Présentation en ligne des profs
							// On n'a pas forcément le formatage choisi pour la classe...
							//$text_prof=$tab_bull['groupe'][$m]["profs"]["proflist_string"]."  ";
							$text_prof="";
							for($loop_prof_grp=0;$loop_prof_grp<count($tab_bull['eleve'][$i]['aid_b'][$m]['aid_prof_resp_login']);$loop_prof_grp++) {
								$tmp_login_prof=$tab_bull['eleve'][$i]['aid_b'][$m]['aid_prof_resp_login'][$loop_prof_grp];
								if($loop_prof_grp>0) {$text_prof.=", ";}
								$text_prof.=affiche_utilisateur($tmp_login_prof,$tab_bull['eleve'][$i]['id_classe']);
							}

							if($text_prof!="") {
								//$espace_matiere_prof = $espace_entre_matier/2;
								$espace_matiere_prof = $hauteur_matiere/2;
								$hauteur_caractere_prof = 7;

								$texte=$text_prof;
								$taille_max_police=$hauteur_caractere_prof;
								$taille_min_police=ceil($hauteur_caractere_prof/3);

								$largeur_dispo=$param_bull2016["largeur_acquis_col_1"];
								$h_cell=$espace_matiere_prof;

								$pdf->SetX($param_bull2016["x_acquis_col_1"]);

								cell_ajustee(($texte),$pdf->GetX(),$pdf->GetY(),$largeur_dispo,$h_cell,$taille_max_police,$taille_min_police,'');
							}
						}

						// Colonne 2 : Éléments de programmes
						$pdf->SetFillColor($param_bull2016["couleur_acquis_ligne_alt".($cpt_matiere%2+1)]["R"], $param_bull2016["couleur_acquis_ligne_alt".($cpt_matiere%2+1)]["V"], $param_bull2016["couleur_acquis_ligne_alt".($cpt_matiere%2+1)]["B"]);
						$pdf->Rect($param_bull2016["x_acquis_col_2"], $y_courant, $param_bull2016["largeur_acquis_col_2"], $hauteur_matiere, 'F');
						$pdf->SetFillColor(0, 0, 0);

						$pdf->SetTextColor(0, 0, 0);
						$pdf->SetXY($param_bull2016["x_acquis_col_2"], $y_courant);
						$pdf->SetFont('DejaVu','',7);

						// Pas d'éléments de programmes dans les AID pour le moment

						/*
						$hauteur_caractere_appreciation = 9;
						$pdf->SetFont('DejaVu','',$hauteur_caractere_appreciation);

						$texte_Elements_Programmes="";

						if((isset($tab_bull['ElementsProgrammes']['ele'][$tab_bull['eleve'][$i]['login']][$tab_bull['groupe'][$m]['id']]))&&(is_array($tab_bull['ElementsProgrammes']['ele'][$tab_bull['eleve'][$i]['login']][$tab_bull['groupe'][$m]['id']]))) {
							for($loop_mep=0;$loop_mep<count($tab_bull['ElementsProgrammes']['ele'][$tab_bull['eleve'][$i]['login']][$tab_bull['groupe'][$m]['id']]);$loop_mep++) {
								if($texte_Elements_Programmes!="") {
									$texte_Elements_Programmes.="\n";
								}
								$texte_Elements_Programmes.=$tab_bull['ElementsProgrammes']['ele'][$tab_bull['eleve'][$i]['login']][$tab_bull['groupe'][$m]['id']][$loop_mep];
							}
						}

						if($texte_Elements_Programmes=="") {
							//$pdf->Cell($tab_modele_pdf["largeur_Elements_Programmes"][$classe_id], $espace_entre_matier, ($texte_Elements_Programmes),'LRB',0,'C');
						}
						else {
							// DEBUT AJUSTEMENT TAILLE ELEMENTS PROGRAMME
							$taille_texte_total = $pdf->GetStringWidth($texte_Elements_Programmes);
							//$largeur_dispo=$tab_modele_pdf["largeur_Elements_Programmes"][$classe_id];
							$largeur_dispo=$param_bull2016["largeur_acquis_col_2"];

							if($use_cell_ajustee=="n") {
								$nb_ligne_app = '2.8';
								$taille_texte_max = $nb_ligne_app * ($largeur_dispo-4);
								$grandeur_texte='test';	

								while($grandeur_texte!='ok') {
									if($taille_texte_max < $taille_texte_total)
									{
										$hauteur_caractere_appreciation = $hauteur_caractere_appreciation-0.3;
										$pdf->SetFont('DejaVu','',$hauteur_caractere_appreciation);
										$taille_texte_total = $pdf->GetStringWidth($texte_Elements_Programmes);
									}
									else {
										$grandeur_texte='ok';
									}
								}
								$grandeur_texte='test';
								$pdf->drawTextBox(($texte_Elements_Programmes), $largeur_dispo, $hauteur_matiere, 'J', 'M', 1);
							}
							else {
								$texte=$texte_Elements_Programmes;
								$taille_max_police=$hauteur_caractere_appreciation;
								$taille_min_police=ceil($taille_max_police/3);

								$h_cell=$hauteur_matiere;

								if(getSettingValue('suppr_balises_app_prof')=='y') {$texte=preg_replace('/<(.*)>/U','',$texte);}
								cell_ajustee(($texte),$pdf->GetX(),$pdf->GetY(),$largeur_dispo,$h_cell,$taille_max_police,$taille_min_police,'');
							}

						}
						*/


						// Colonne 3 : Appréciation
						$pdf->SetFillColor($param_bull2016["couleur_acquis_ligne_alt".($cpt_matiere%2+1)]["R"], $param_bull2016["couleur_acquis_ligne_alt".($cpt_matiere%2+1)]["V"], $param_bull2016["couleur_acquis_ligne_alt".($cpt_matiere%2+1)]["B"]);
						$pdf->Rect($param_bull2016["x_acquis_col_3"], $y_courant, $param_bull2016["largeur_acquis_col_3"], $hauteur_matiere, 'F');

						$pdf->SetFillColor(0, 0, 0);
						$pdf->SetXY($param_bull2016["x_acquis_col_3"], $y_courant);

						// calcul de la taille du texte des appréciations
						$hauteur_caractere_appreciation = 8;
						$pdf->SetFont('DejaVu','',$hauteur_caractere_appreciation);

						//suppression des espaces en début et en fin
						//$app_aff = trim($tab_bull['app'][$m][$i]);
						$app_aff="";
						if($tab_bull['eleve'][$i]['aid_b'][$m]['message']!='') {
							$app_aff.=$tab_bull['eleve'][$i]['aid_b'][$m]['message'];
						}
						//if($app_aff!='') {$app_aff.=" ";}
						if(($tab_bull['eleve'][$i]['aid_b'][$m]['display_nom']=='y')&&($tab_bull['eleve'][$i]['aid_b'][$m]['aid_nom']!='')) {
							if($app_aff!='') {$app_aff.=" ";}
							$app_aff.=$tab_bull['eleve'][$i]['aid_b'][$m]['aid_nom'];
						}
						if($app_aff!='') {$app_aff.="\n";}
						$app_aff.=trim($tab_bull['eleve'][$i]['aid_b'][$m]['aid_appreciation']);

						fich_debug_bull("__________________________________________\n");
						fich_debug_bull("$app_aff\n");
						fich_debug_bull("__________________________________________\n");

						// DEBUT AJUSTEMENT TAILLE APPRECIATION
						$taille_texte_total = $pdf->GetStringWidth($app_aff);
						//$largeur_appreciation2 = $largeur_appreciation - $largeur_sous_matiere;
						$largeur_appreciation2=$param_bull2016["largeur_acquis_col_3"];

						if($use_cell_ajustee=="n") {
							//$taille_texte = (($espace_entre_matier/3)*$largeur_appreciation2);
							$nb_ligne_app = '2.8';
							//$nb_ligne_app = '3.8';
							//$nb_ligne_app = '4.8';
							$taille_texte_max = $nb_ligne_app * ($largeur_appreciation2-4);
							//$taille_texte_max = $nb_ligne_app * ($largeur_appreciation2);
							$grandeur_texte='test';

							fich_debug_bull("\$taille_texte_total=$taille_texte_total\n");
							fich_debug_bull("\$largeur_appreciation2=$largeur_appreciation2\n");
							fich_debug_bull("\$nb_ligne_app=$nb_ligne_app\n");
							//fich_debug_bull("\$taille_texte_max = \$nb_ligne_app * (\$largeur_appreciation2-4)=$nb_ligne_app * ($largeur_appreciation2-4)=$taille_texte_max\n");
							fich_debug_bull("\$taille_texte_max = \$nb_ligne_app * (\$largeur_appreciation2)=$nb_ligne_app * ($largeur_appreciation2)=$taille_texte_max\n");

							while($grandeur_texte!='ok') {
								if($taille_texte_max < $taille_texte_total)
								{
									$hauteur_caractere_appreciation = $hauteur_caractere_appreciation-0.3;
									//$hauteur_caractere_appreciation = $hauteur_caractere_appreciation-0.1;
									$pdf->SetFont('DejaVu','',$hauteur_caractere_appreciation);
									$taille_texte_total = $pdf->GetStringWidth($app_aff);
								}
								else {
									$grandeur_texte='ok';
								}
							}
							$grandeur_texte='test';
							$pdf->drawTextBox(($app_aff), $largeur_appreciation2, $hauteur_matiere, 'J', 'M', 1);
						}
						else {
							$texte=$app_aff;
							//$texte="Bla bla\nbli ".$app_aff;
							$taille_max_police=$hauteur_caractere_appreciation;
							$taille_min_police=ceil($taille_max_police/3);

							$largeur_dispo=$largeur_appreciation2;
							$h_cell=$hauteur_matiere;

							if(getSettingValue('suppr_balises_app_prof')=='y') {$texte=preg_replace('/<(.*)>/U','',$texte);}
							cell_ajustee(($texte),$pdf->GetX(),$pdf->GetY(),$largeur_dispo,$h_cell,$taille_max_police,$taille_min_police,'');
						}


						// Colonne 4 : Moyenne élève
						$pdf->SetFillColor($param_bull2016["couleur_acquis_colonne_moyenne_ligne_alt".($cpt_matiere%2+1)]["R"], $param_bull2016["couleur_acquis_colonne_moyenne_ligne_alt".($cpt_matiere%2+1)]["V"], $param_bull2016["couleur_acquis_colonne_moyenne_ligne_alt".($cpt_matiere%2+1)]["B"]);
						$pdf->Rect($param_bull2016["x_acquis_col_moy"], $y_courant, $param_bull2016["largeur_acquis_col_moy"], $hauteur_matiere, 'F');

						$pdf->SetTextColor(0, 0, 0);
						$pdf->SetXY($param_bull2016["x_acquis_col_moy"], $y_courant);
						$pdf->SetFont('DejaVu', 'B', 8);
						//$pdf->Cell($param_bull2016["largeur_acquis_col_moy"], $hauteur_matiere, $texte,0,2,'C');
						//$pdf->drawTextBox($texte, $param_bull2016["largeur_acquis_col_moy"], $param_bull2016["hauteur_acquis_ligne_entete"], 'C', 'M', 0);

						$fleche_evolution="";

						// On filtre si la moyenne est vide, on affiche seulement un tiret
						if ($tab_bull['eleve'][$i]['aid_b'][$m]['aid_note']=="-") {
							$valeur = "-";
						}
						elseif ($tab_bull['eleve'][$i]['aid_b'][$m]['aid_statut']!="") {
							if($tab_bull['eleve'][$i]['aid_b'][$m]['aid_statut']=="other") {
								$valeur = "-";
							}
							else {
								$valeur=$tab_bull['eleve'][$i]['aid_b'][$m]['aid_statut'];
							}
						}
						else {
							$valeur = present_nombre($tab_bull['eleve'][$i]['aid_b'][$m]['aid_note'], $arrondi, $nb_chiffre_virgule, $chiffre_avec_zero);
						}

						$pdf->Cell($param_bull2016["largeur_acquis_col_moy"], $hauteur_matiere, $valeur.$fleche_evolution, 0, 2, 'C', 1);
						$valeur = "";


						// Colonne 5 : Moyenne classe
						$pdf->SetFillColor($param_bull2016["couleur_acquis_ligne_alt".($cpt_matiere%2+1)]["R"], $param_bull2016["couleur_acquis_ligne_alt".($cpt_matiere%2+1)]["V"], $param_bull2016["couleur_acquis_ligne_alt".($cpt_matiere%2+1)]["B"]);
						$pdf->Rect($param_bull2016["x_acquis_col_moyclasse"], $y_courant, $param_bull2016["largeur_acquis_col_moyclasse"], $hauteur_matiere, 'F');

						if ($param_bull2016["bull2016_moyminclassemax"]=='y') {
							$chaine_minclassemax="";
							// Min
							if (($tab_bull['eleve'][$i]['aid_b'][$m]['aid_note_min']=="-")||($tab_bull['eleve'][$i]['aid_b'][$m]['aid_note_min']=="")) {
								$valeur = "-";
							} else {
								$valeur = present_nombre($tab_bull['eleve'][$i]['aid_b'][$m]['aid_note_min'], $arrondi, $nb_chiffre_virgule, $chiffre_avec_zero);
							}
							$chaine_minclassemax.=$valeur."\n";

							// Classe
							if (($tab_bull['eleve'][$i]['aid_b'][$m]['aid_note_moyenne']=="-")||($tab_bull['eleve'][$i]['aid_b'][$m]['aid_note_moyenne']=="")) {
								$valeur = "-";
							}
							else {
								$valeur = present_nombre($tab_bull['eleve'][$i]['aid_b'][$m]['aid_note_moyenne'], $arrondi, $nb_chiffre_virgule, $chiffre_avec_zero);
							}
							$chaine_minclassemax.=$valeur."\n";

							if (($tab_bull['eleve'][$i]['aid_b'][$m]['aid_note_max']=="-")||($tab_bull['eleve'][$i]['aid_b'][$m]['aid_note_max']=="")) {
								$valeur = "-";
							} else {
								$valeur = present_nombre($tab_bull['eleve'][$i]['aid_b'][$m]['aid_note_max'], $arrondi, $nb_chiffre_virgule, $chiffre_avec_zero);
							}
							$chaine_minclassemax.=$valeur;

							// Affichage min/classe/max avec cell_ajustee()
							$pdf->SetTextColor(0, 0, 0);
							$pdf->SetXY($param_bull2016["x_acquis_col_moyclasse"], $y_courant);
							$texte=$chaine_minclassemax;
							$taille_max_police=7;
							$taille_min_police=ceil($taille_max_police/3);

							$largeur_dispo=$param_bull2016["largeur_acquis_col_moyclasse"];
							$h_cell=$hauteur_matiere;

							//if(getSettingValue('suppr_balises_app_prof')=='y') {$texte=preg_replace('/<(.*)>/U','',$texte);}
							cell_ajustee(($texte),$pdf->GetX(),$pdf->GetY(),$largeur_dispo,$h_cell,$taille_max_police,$taille_min_police, '', 'C', 'C');
						}
						else {
							$pdf->SetTextColor(0, 0, 0);
							$pdf->SetXY($param_bull2016["x_acquis_col_moyclasse"], $y_courant);
							$pdf->SetFont('DejaVu','',7);

							if (($tab_bull['eleve'][$i]['aid_b'][$m]['aid_note_moyenne']=="-")||($tab_bull['eleve'][$i]['aid_b'][$m]['aid_note_moyenne']=="")) {
								$valeur = "-";
							}
							else {
								$valeur = present_nombre($tab_bull['eleve'][$i]['aid_b'][$m]['aid_note_moyenne'], $arrondi, $nb_chiffre_virgule, $chiffre_avec_zero);
							}

							$pdf->Cell($param_bull2016["largeur_acquis_col_moyclasse"], $hauteur_matiere, $valeur,'',0,'C');
						}

						$y_courant+=$hauteur_matiere+0.5;

						$cpt_matiere++;
					}
				}
			}
		}
		// Fin des AID_b

		// Début des enseignements de la table 'groupes'
		for($m=0;$m<count($tab_bull['groupe']);$m++) {
			//if(isset($tab_bull['note'][$m][$i])) {
			// On n'affiche pas ici les groupes correspondant à AP, EPI ou Parcours
			if((isset($tab_bull['note'][$m][$i]))&&
			((!isset($tab_bull['groupe'][$m]['type_grp'][0]))||($tab_bull['groupe'][$m]['type_grp'][0]['nom_court']=="local"))) {
				// Colonne 1 : Matière, prof

				$pdf->SetFillColor($param_bull2016["couleur_acquis_ligne_alt".($cpt_matiere%2+1)]["R"], $param_bull2016["couleur_acquis_ligne_alt".($cpt_matiere%2+1)]["V"], $param_bull2016["couleur_acquis_ligne_alt".($cpt_matiere%2+1)]["B"]);
				$pdf->Rect($param_bull2016["x_acquis_col_1"], $y_courant, $param_bull2016["largeur_acquis_col_1"], $hauteur_matiere, 'F');
				$pdf->SetFillColor(0, 0, 0);

				$pdf->SetTextColor(0, 0, 0);
				$pdf->SetXY($param_bull2016["x_acquis_col_1"], $y_courant);
				$pdf->SetFont('DejaVu','',8);
				/*
				//$pdf->Cell($param_bull2016["largeur_bandeau_suivi_acquis"],7, "Suivi des acquis scolaires de l'élève",0,2,'C');
				$texte="Matiere $m ".$hauteur_matiere;
				$pdf->drawTextBox($texte, $param_bull2016["largeur_acquis_col_1"], $hauteur_matiere, 'L', 'M', 0);
				*/

				if(getSettingValue('bul_rel_nom_matieres')=='nom_groupe') {
					$info_nom_matiere=$tab_bull['groupe'][$m]['name'];
				}
				elseif(getSettingValue('bul_rel_nom_matieres')=='description_groupe') {
					$info_nom_matiere=$tab_bull['groupe'][$m]['description'];
				}
				else {
					// Pour parer au bug sur la suppression de matière alors que des groupes sont conservés:
					if(isset($tab_bull['groupe'][$m]['matiere']['nom_complet'])) {
						$info_nom_matiere=$tab_bull['groupe'][$m]['matiere']['nom_complet'];
					}
					else {
						$info_nom_matiere=$tab_bull['groupe'][$m]['name']." (".$tab_bull['groupe'][$m]['id'].")";
					}
				}

				$hauteur_caractere_matiere=8;
				$cell_ajustee_texte_matiere_ratio_min_max=3;

				// 20130927 : cell_ajustee() ou pas sur le nom de matière/enseignement
				$cell_ajustee_texte_matiere=1;
				if((isset($cell_ajustee_texte_matiere))&&($cell_ajustee_texte_matiere==1)) {
					// Encadrement
					//$pdf->Cell($tab_modele_pdf["largeur_matiere"][$classe_id], $espace_entre_matier, "",'LRBT',1,'L');

					// cell_ajustee() ne centre pas verticalement le texte.
					// On met un décalage pour ne pas coller le texte à la bordure
					$Y_decal_cell_ajustee=2;
					// On repositionne et on inscrit le nom de matière sur la moitié de la hauteur de la cellule
					//$pdf->SetXY($X_bloc_matiere, $Y_decal+$Y_decal_cell_ajustee);
					//$pdf->SetXY($param_bull2016["x_acquis_col_1"], $y_courant);

					$pdf->SetXY($param_bull2016["x_acquis_col_1"], $y_courant+$Y_decal_cell_ajustee);

					$texte=$info_nom_matiere;
					$taille_max_police=$hauteur_caractere_matiere;
					//$taille_min_police=ceil($taille_max_police/$tab_modele_pdf["cell_ajustee_texte_matiere_ratio_min_max"][$classe_id]);
					$taille_min_police=ceil($taille_max_police/$cell_ajustee_texte_matiere_ratio_min_max);

					$largeur_dispo=$param_bull2016["largeur_acquis_col_1"];
					//$h_cell=$espace_entre_matier/2-$Y_decal_cell_ajustee;
					$h_cell=$hauteur_matiere/2-$Y_decal_cell_ajustee;

					//cell_ajustee("<b>".$texte."</b>",$pdf->GetX(),$pdf->GetY(),$largeur_dispo,$h_cell,$taille_max_police,$taille_min_police,'');
					cell_ajustee($texte,$pdf->GetX(),$pdf->GetY(),$largeur_dispo,$h_cell,$taille_max_police,$taille_min_police,'');
				
				}
				else {
					$val = $pdf->GetStringWidth($info_nom_matiere);
					$taille_texte = $param_bull2016["largeur_acquis_col_1"]-2;
					$grandeur_texte='test';
					while($grandeur_texte!='ok') {
						if($taille_texte<$val)
						{
							$hauteur_caractere_matiere = $hauteur_caractere_matiere-0.3;
							$pdf->SetFont('DejaVu','B',$hauteur_caractere_matiere);
							$val = $pdf->GetStringWidth($info_nom_matiere);
						}
						else {
							$grandeur_texte='ok';
						}
					}
					$grandeur_texte='test';
					$Y_decal=$y_courant;
					$pdf->SetXY($param_bull2016["x_acquis_col_1"], $Y_decal);
					$pdf->Cell($param_bull2016["largeur_acquis_col_1"], $hauteur_matiere/2, ($info_nom_matiere),'',1,'L');
				}


				// On note l'ordonnée pour le nom des professeurs
				$Y_decal = $y_courant+($hauteur_matiere/2);
				$pdf->SetXY($param_bull2016["x_acquis_col_1"], $Y_decal);
				$pdf->SetFont('DejaVu','',8);

				//fich_debug_bull("\$info_nom_matiere=$info_nom_matiere\n");
				//fich_debug_bull("Le nom de matière est écrit; on est à mi-hauteur de la cellule pour écrire le nom du prof:\n");
				//fich_debug_bull("\$Y_decal=$Y_decal\n");

				// nom des professeurs

				if (((!isset($tab_bull['groupe'][$m]['param']['nom_prof_sur_bulletin']))||($tab_bull['groupe'][$m]['param']['nom_prof_sur_bulletin']!='n'))
				&&( isset($tab_bull['groupe'][$m]["profs"]["list"])))
				{
					/*
					if($tab_modele_pdf["presentation_proflist"][$classe_id]!="2") {
						// Présentation en colonne des profs
						$nb_prof_matiere = count($tab_bull['groupe'][$m]["profs"]["list"]);
						$espace_matiere_prof = $espace_entre_matier/2;
						if($nb_prof_matiere>0){
							$espace_matiere_prof = $espace_matiere_prof/$nb_prof_matiere;
						}
						$nb_pass_count = '0';
						$text_prof = '';
						while ($nb_prof_matiere > $nb_pass_count)
						{
							$tmp_login_prof=$tab_bull['groupe'][$m]["profs"]["list"][$nb_pass_count];
							$text_prof=affiche_utilisateur($tmp_login_prof,$tab_bull['eleve'][$i]['id_classe']);

							if ( $nb_prof_matiere <= 2 ) { $hauteur_caractere_prof = 8; }
							elseif ( $nb_prof_matiere == 3) { $hauteur_caractere_prof = 5; }
							elseif ( $nb_prof_matiere > 3) { $hauteur_caractere_prof = 2; }
							$pdf->SetFont('DejaVu','',$hauteur_caractere_prof);
							$val = $pdf->GetStringWidth($text_prof);
							$taille_texte = ($tab_modele_pdf["largeur_matiere"][$classe_id]);
							$grandeur_texte='test';
							while($grandeur_texte!='ok') {
								if($taille_texte<$val)
								{
									$hauteur_caractere_prof = $hauteur_caractere_prof-0.3;
									$pdf->SetFont('DejaVu','',$hauteur_caractere_prof);
									$val = $pdf->GetStringWidth($text_prof);
								}
								else {
									$grandeur_texte='ok';
								}
							}
							$grandeur_texte='test';
							$pdf->SetX($X_bloc_matiere);
							if( empty($tab_bull['groupe'][$m]["profs"]["list"][$nb_pass_count+1]) ) {
								$pdf->Cell($tab_modele_pdf["largeur_matiere"][$classe_id], $espace_matiere_prof, ($text_prof),'LRB',1,'L');
							}
							if( !empty($tab_bull['groupe'][$m]["profs"]["list"][$nb_pass_count+1]) ) {
								$pdf->Cell($tab_modele_pdf["largeur_matiere"][$classe_id], $espace_matiere_prof, ($text_prof),'LR',1,'L');
							}
							$nb_pass_count = $nb_pass_count + 1;
						}
					}
					else {
					*/
						// Présentation en ligne des profs
						// On n'a pas forcément le formatage choisi pour la classe...
						//$text_prof=$tab_bull['groupe'][$m]["profs"]["proflist_string"]."  ";
						$text_prof="";
						for($loop_prof_grp=0;$loop_prof_grp<count($tab_bull['groupe'][$m]["profs"]["list"]);$loop_prof_grp++) {
							$tmp_login_prof=$tab_bull['groupe'][$m]["profs"]["list"][$loop_prof_grp];
							if($loop_prof_grp>0) {$text_prof.=", ";}
							$text_prof.=affiche_utilisateur($tmp_login_prof,$tab_bull['eleve'][$i]['id_classe']);
						}

						if($text_prof!="") {
							//$espace_matiere_prof = $espace_entre_matier/2;
							$espace_matiere_prof = $hauteur_matiere/2;
							$hauteur_caractere_prof = 7;

							/*
							if($use_cell_ajustee=="n") {
								$pdf->SetFont('DejaVu','',$hauteur_caractere_prof);
								$val = $pdf->GetStringWidth($text_prof);
								$taille_texte = ($tab_modele_pdf["largeur_matiere"][$classe_id]);
								$grandeur_texte='test';
								while($grandeur_texte!='ok') {
									if($taille_texte<$val)
									{
										$hauteur_caractere_prof = $hauteur_caractere_prof-0.3;
										$pdf->SetFont('DejaVu','',$hauteur_caractere_prof);
										$val = $pdf->GetStringWidth($text_prof);
									}
									else {
										$grandeur_texte='ok';
									}
								}
								$grandeur_texte='test';
								$pdf->SetX($X_bloc_matiere);
								$pdf->Cell($tab_modele_pdf["largeur_matiere"][$classe_id], $espace_matiere_prof, ($text_prof),'LR',1,'L');
							}
							else {
							*/
								$texte=$text_prof;
								$taille_max_police=$hauteur_caractere_prof;
								$taille_min_police=ceil($hauteur_caractere_prof/3);

								$largeur_dispo=$param_bull2016["largeur_acquis_col_1"];
								$h_cell=$espace_matiere_prof;

								$pdf->SetX($param_bull2016["x_acquis_col_1"]);

								cell_ajustee(($texte),$pdf->GetX(),$pdf->GetY(),$largeur_dispo,$h_cell,$taille_max_police,$taille_min_police,'');
							//}
						}
					//}
				}

				// Colonne 2 : Éléments de programmes
				$pdf->SetFillColor($param_bull2016["couleur_acquis_ligne_alt".($cpt_matiere%2+1)]["R"], $param_bull2016["couleur_acquis_ligne_alt".($cpt_matiere%2+1)]["V"], $param_bull2016["couleur_acquis_ligne_alt".($cpt_matiere%2+1)]["B"]);
				$pdf->Rect($param_bull2016["x_acquis_col_2"], $y_courant, $param_bull2016["largeur_acquis_col_2"], $hauteur_matiere, 'F');
				$pdf->SetFillColor(0, 0, 0);

				$pdf->SetTextColor(0, 0, 0);
				$pdf->SetXY($param_bull2016["x_acquis_col_2"], $y_courant);
				$pdf->SetFont('DejaVu','',7);
				/*
				//$pdf->Cell($param_bull2016["largeur_bandeau_suivi_acquis"],7, "Suivi des acquis scolaires de l'élève",0,2,'C');
				$texte="Éléments du programme travaillés durant\nla période (connaissances/compétences)";
				//$pdf->Cell($param_bull2016["largeur_cadre_logo_RF"]-8, $param_bull2016["hauteur_cadre_EN"]-6, $texte,0,2,'C');
				$pdf->drawTextBox($texte, $param_bull2016["largeur_acquis_col_2"], $param_bull2016["hauteur_acquis_ligne_entete"], 'L', 'M', 0);
				*/

				//if((!getSettingAOui('bullNoSaisieElementsProgrammes'))&&($tab_modele_pdf["active_colonne_Elements_Programmes"][$classe_id]==='1')) {
					//$pdf->SetXY($X_note_moy_app+$largeur_utilise, $Y_decal-($espace_entre_matier/2));

					$hauteur_caractere_appreciation = 9;
					$pdf->SetFont('DejaVu','',$hauteur_caractere_appreciation);

					$texte_Elements_Programmes="";

					if((isset($tab_bull['ElementsProgrammes']['ele'][$tab_bull['eleve'][$i]['login']][$tab_bull['groupe'][$m]['id']]))&&(is_array($tab_bull['ElementsProgrammes']['ele'][$tab_bull['eleve'][$i]['login']][$tab_bull['groupe'][$m]['id']]))) {
						for($loop_mep=0;$loop_mep<count($tab_bull['ElementsProgrammes']['ele'][$tab_bull['eleve'][$i]['login']][$tab_bull['groupe'][$m]['id']]);$loop_mep++) {
							if($texte_Elements_Programmes!="") {
								$texte_Elements_Programmes.="\n";
							}
							$texte_Elements_Programmes.=$tab_bull['ElementsProgrammes']['ele'][$tab_bull['eleve'][$i]['login']][$tab_bull['groupe'][$m]['id']][$loop_mep];
						}
					}

					if($texte_Elements_Programmes=="") {
						//$pdf->Cell($tab_modele_pdf["largeur_Elements_Programmes"][$classe_id], $espace_entre_matier, ($texte_Elements_Programmes),'LRB',0,'C');
					}
					else {
						// DEBUT AJUSTEMENT TAILLE ELEMENTS PROGRAMME
						$taille_texte_total = $pdf->GetStringWidth($texte_Elements_Programmes);
						//$largeur_dispo=$tab_modele_pdf["largeur_Elements_Programmes"][$classe_id];
						$largeur_dispo=$param_bull2016["largeur_acquis_col_2"];

						if($use_cell_ajustee=="n") {
							$nb_ligne_app = '2.8';
							$taille_texte_max = $nb_ligne_app * ($largeur_dispo-4);
							$grandeur_texte='test';	

							while($grandeur_texte!='ok') {
								if($taille_texte_max < $taille_texte_total)
								{
									$hauteur_caractere_appreciation = $hauteur_caractere_appreciation-0.3;
									$pdf->SetFont('DejaVu','',$hauteur_caractere_appreciation);
									$taille_texte_total = $pdf->GetStringWidth($texte_Elements_Programmes);
								}
								else {
									$grandeur_texte='ok';
								}
							}
							$grandeur_texte='test';
							$pdf->drawTextBox(($texte_Elements_Programmes), $largeur_dispo, $hauteur_matiere, 'J', 'M', 1);
						}
						else {
							$texte=$texte_Elements_Programmes;
							$taille_max_police=$hauteur_caractere_appreciation;
							$taille_min_police=ceil($taille_max_police/3);

							$h_cell=$hauteur_matiere;

							if(getSettingValue('suppr_balises_app_prof')=='y') {$texte=preg_replace('/<(.*)>/U','',$texte);}
							cell_ajustee(($texte),$pdf->GetX(),$pdf->GetY(),$largeur_dispo,$h_cell,$taille_max_police,$taille_min_police,'');
						}

					}

					//$largeur_utilise = $largeur_utilise + $tab_modele_pdf["largeur_Elements_Programmes"][$classe_id];
				//}



				// Colonne 3 : Appréciation
				$pdf->SetFillColor($param_bull2016["couleur_acquis_ligne_alt".($cpt_matiere%2+1)]["R"], $param_bull2016["couleur_acquis_ligne_alt".($cpt_matiere%2+1)]["V"], $param_bull2016["couleur_acquis_ligne_alt".($cpt_matiere%2+1)]["B"]);
				$pdf->Rect($param_bull2016["x_acquis_col_3"], $y_courant, $param_bull2016["largeur_acquis_col_3"], $hauteur_matiere, 'F');

				$pdf->SetFillColor(0, 0, 0);
				$pdf->SetXY($param_bull2016["x_acquis_col_3"], $y_courant);


				$largeur_appreciation2=$param_bull2016["largeur_acquis_col_3"];
				//+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
				// 20161123
				// si on autorise l'affichage des sous matière et s'il y en a alors on les affiche
				//$id_groupe_select = $tab_bull['groupe'][$m]['id'];
				//$pdf->SetXY($param_bull2016["x_acquis_col_3"], $Y_decal-($espace_entre_matier/2));
				$X_sous_matiere = 0; $largeur_sous_matiere=0;

				// A MODIFIER POUR POUVOIR LIMITER LA LARGEUR, par exemple à 20mm

				if((isset($param_bull2016["bull2016_autorise_sous_matiere"]))&&($param_bull2016["bull2016_autorise_sous_matiere"]=="y")&&(!empty($tab_bull['groupe'][$m][$i]['cn_nom']))) {

					// Bordure blanche pour les sous-matières
					$pdf->SetDrawColor(255, 255, 255);

					$X_sous_matiere = $param_bull2016["x_acquis_col_3"];
					//$Y_sous_matiere = $y_courant-$hauteur_matiere/2;
					$Y_sous_matiere = $y_courant;
					$n=0;
					$largeur_texte_sousmatiere=0;
					$largeur_sous_matiere=0;
					while( !empty($tab_bull['groupe'][$m][$i]['cn_nom'][$n]) )
					{
						$pdf->SetFont('DejaVu','',6);
						$largeur_texte_sousmatiere = $pdf->GetStringWidth($tab_bull['groupe'][$m][$i]['cn_nom'][$n].': '.$tab_bull['groupe'][$m][$i]['cn_note'][$n]);
						if($largeur_sous_matiere<$largeur_texte_sousmatiere) { $largeur_sous_matiere=$largeur_texte_sousmatiere; }
						$n = $n + 1;
					}
					if($largeur_sous_matiere!='0') { $largeur_sous_matiere = $largeur_sous_matiere + 2; }
					$n=0;
					while( !empty($tab_bull['groupe'][$m][$i]['cn_nom'][$n]) )
					{
						$pdf->SetXY($X_sous_matiere, $Y_sous_matiere);
						$pdf->SetFont('DejaVu','',6);
						$pdf->Cell($largeur_sous_matiere, $hauteur_matiere/count($tab_bull['groupe'][$m][$i]['cn_nom']), ($tab_bull['groupe'][$m][$i]['cn_nom'][$n].': '.$tab_bull['groupe'][$m][$i]['cn_note'][$n]),1,0,'L');
						$Y_sous_matiere = $Y_sous_matiere+$hauteur_matiere/count($tab_bull['groupe'][$m][$i]['cn_nom']);
						$n = $n + 1;
					}

					$largeur_appreciation2=$param_bull2016["largeur_acquis_col_3"]-$largeur_sous_matiere;

					// Retour aux bordures noires par défaut, mais non dessinées en principe sur les bulletins PDF 2016
					$pdf->SetDrawColor(0, 0, 0);
				}
				//$pdf->SetXY($X_note_moy_app+$largeur_utilise, $Y_decal-($espace_entre_matier/2));
				$pdf->SetXY($param_bull2016["x_acquis_col_3"]+$largeur_sous_matiere, $y_courant);
				//+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

				// calcul de la taille du texte des appréciations
				$hauteur_caractere_appreciation = 8;
				$pdf->SetFont('DejaVu','',$hauteur_caractere_appreciation);

				//suppression des espaces en début et en fin
				$app_aff = trim($tab_bull['app'][$m][$i]);

				fich_debug_bull("__________________________________________\n");
				fich_debug_bull("$app_aff\n");
				fich_debug_bull("__________________________________________\n");

				// DEBUT AJUSTEMENT TAILLE APPRECIATION
				$taille_texte_total = $pdf->GetStringWidth($app_aff);
				//$largeur_appreciation2 = $largeur_appreciation - $largeur_sous_matiere;
				//$largeur_appreciation2=$param_bull2016["largeur_acquis_col_3"];

				if($use_cell_ajustee=="n") {
					//$taille_texte = (($espace_entre_matier/3)*$largeur_appreciation2);
					$nb_ligne_app = '2.8';
					//$nb_ligne_app = '3.8';
					//$nb_ligne_app = '4.8';
					$taille_texte_max = $nb_ligne_app * ($largeur_appreciation2-4);
					//$taille_texte_max = $nb_ligne_app * ($largeur_appreciation2);
					$grandeur_texte='test';

					fich_debug_bull("\$taille_texte_total=$taille_texte_total\n");
					fich_debug_bull("\$largeur_appreciation2=$largeur_appreciation2\n");
					fich_debug_bull("\$nb_ligne_app=$nb_ligne_app\n");
					//fich_debug_bull("\$taille_texte_max = \$nb_ligne_app * (\$largeur_appreciation2-4)=$nb_ligne_app * ($largeur_appreciation2-4)=$taille_texte_max\n");
					fich_debug_bull("\$taille_texte_max = \$nb_ligne_app * (\$largeur_appreciation2)=$nb_ligne_app * ($largeur_appreciation2)=$taille_texte_max\n");

					while($grandeur_texte!='ok') {
						if($taille_texte_max < $taille_texte_total)
						{
							$hauteur_caractere_appreciation = $hauteur_caractere_appreciation-0.3;
							//$hauteur_caractere_appreciation = $hauteur_caractere_appreciation-0.1;
							$pdf->SetFont('DejaVu','',$hauteur_caractere_appreciation);
							$taille_texte_total = $pdf->GetStringWidth($app_aff);
						}
						else {
							$grandeur_texte='ok';
						}
					}
					$grandeur_texte='test';
					$pdf->drawTextBox(($app_aff), $largeur_appreciation2, $hauteur_matiere, 'J', 'M', 1);
				}
				else {
					$texte=$app_aff;
					//$texte="Bla bla\nbli ".$app_aff;
					$taille_max_police=$hauteur_caractere_appreciation;
					$taille_min_police=ceil($taille_max_police/3);

					$largeur_dispo=$largeur_appreciation2;
					$h_cell=$hauteur_matiere;

					if(getSettingValue('suppr_balises_app_prof')=='y') {$texte=preg_replace('/<(.*)>/U','',$texte);}
					cell_ajustee(($texte),$pdf->GetX(),$pdf->GetY(),$largeur_dispo,$h_cell,$taille_max_police,$taille_min_police,'');
				}


				// Colonne 4 : Moyenne élève
				$pdf->SetFillColor($param_bull2016["couleur_acquis_colonne_moyenne_ligne_alt".($cpt_matiere%2+1)]["R"], $param_bull2016["couleur_acquis_colonne_moyenne_ligne_alt".($cpt_matiere%2+1)]["V"], $param_bull2016["couleur_acquis_colonne_moyenne_ligne_alt".($cpt_matiere%2+1)]["B"]);
				$pdf->Rect($param_bull2016["x_acquis_col_moy"], $y_courant, $param_bull2016["largeur_acquis_col_moy"], $hauteur_matiere, 'F');

				$pdf->SetTextColor(0, 0, 0);
				$pdf->SetXY($param_bull2016["x_acquis_col_moy"], $y_courant);
				$pdf->SetFont('DejaVu', 'B', 8);
				//$pdf->Cell($param_bull2016["largeur_acquis_col_moy"], $hauteur_matiere, $texte,0,2,'C');
				//$pdf->drawTextBox($texte, $param_bull2016["largeur_acquis_col_moy"], $param_bull2016["hauteur_acquis_ligne_entete"], 'C', 'M', 0);

				$fleche_evolution="";

				// On filtre si la moyenne est vide, on affiche seulement un tiret
				if ($tab_bull['note'][$m][$i]=="-") {
					$valeur = "-";
				}
				elseif ($tab_bull['statut'][$m][$i]!="") {
					$valeur=$tab_bull['statut'][$m][$i];
				}
				else {
					// DEBUG: 20161024
					//echo "\$tab_bull['note'][$m][$i]=".$tab_bull['note'][$m][$i]."<br />";
					//echo "present_nombre(".$tab_bull['note'][$m][$i].", $arrondi, $nb_chiffre_virgule, $chiffre_avec_zero)<br />";
					$valeur = present_nombre($tab_bull['note'][$m][$i], $arrondi, $nb_chiffre_virgule, $chiffre_avec_zero);

					fich_debug_bull("\$valeur=$valeur\n");
					fich_debug_bull("\$param_bull2016[\"bull2016_evolution_moyenne_periode_precedente\"]=".$param_bull2016["bull2016_evolution_moyenne_periode_precedente"]."\n");

					//if((isset($evolution_moyenne_periode_precedente))&&($evolution_moyenne_periode_precedente=="y")) {
					if (($param_bull2016["bull2016_evolution_moyenne_periode_precedente"]=='y')&&(isset($tab_bull['login_prec']))) {
						//$fleche_evolution="";

						fich_debug_bull("count(\$tab_bull['login_prec'])=".count($tab_bull['login_prec'])."\n");


						foreach($tab_bull['login_prec'] as $key => $value) {
							// Il faut récupérer l'id_groupe et l'indice de l'élève... dans les tableaux récupérés de calcul_moy_gen.inc.php
							// Tableaux d'indices [$j][$i] (groupe, élève)
							$indice_eleve=-1;
							for($loop_l=0;$loop_l<count($tab_bull['login_prec'][$key]);$loop_l++) {
								fich_debug_bull("\$tab_bull['login_prec'][$key][$loop_l]=".$tab_bull['login_prec'][$key][$loop_l]." et \$tab_bull['eleve'][$i]['login']=".$tab_bull['eleve'][$i]['login']."\n");
								if($tab_bull['login_prec'][$key][$loop_l]==$tab_bull['eleve'][$i]['login']) {$indice_eleve=$loop_l;break;}
							}
							fich_debug_bull("\$indice_eleve=$indice_eleve\n");

							if($indice_eleve!=-1) {
								// Recherche du groupe
								$indice_grp=-1;
								for($loop_l=0;$loop_l<count($tab_bull['group_prec'][$key]);$loop_l++) {
									fich_debug_bull("\$tab_bull['group_prec'][$key][$loop_l]['id']=".$tab_bull['group_prec'][$key][$loop_l]['id']." et \$tab_bull['groupe'][$m]['id']=".$tab_bull['groupe'][$m]['id']."\n");
									if($tab_bull['group_prec'][$key][$loop_l]['id']==$tab_bull['groupe'][$m]['id']) {$indice_grp=$loop_l;break;}
								}
								fich_debug_bull("\$indice_grp=$indice_grp\n");

								if($indice_grp!=-1) {
									if(isset($tab_bull['statut_prec'][$key][$indice_grp][$indice_eleve])) {
										if ($tab_bull['statut_prec'][$key][$indice_grp][$indice_eleve]=="") {
											fich_debug_bull("\$tab_bull['note'][$m][$i]=".$tab_bull['note'][$m][$i]."\n");
											fich_debug_bull("\$tab_bull['note_prec'][$key][$indice_grp][$indice_eleve]=".$tab_bull['note_prec'][$key][$indice_grp][$indice_eleve]."\n");
											// 20151201: METTRE UN SEUIL POUR L'EVOLUTION DE LA MOYENNE
											if($tab_bull['note'][$m][$i]>=$tab_bull['note_prec'][$key][$indice_grp][$indice_eleve]+$param_bull2016["bull2016_evolution_moyenne_periode_precedente_seuil"]) {
												$fleche_evolution="+";
											}
											elseif($tab_bull['note'][$m][$i]<=$tab_bull['note_prec'][$key][$indice_grp][$indice_eleve]-$param_bull2016["bull2016_evolution_moyenne_periode_precedente_seuil"]) {
												$fleche_evolution="-";
											}
											else {
												$fleche_evolution="";
											}
											fich_debug_bull("\$fleche_evolution=".$fleche_evolution."\n");

											//$valeur = present_nombre($tab_bull['note_prec'][$key][$indice_grp][$indice_eleve], $tab_modele_pdf["arrondie_choix"][$classe_id], $tab_modele_pdf["nb_chiffre_virgule"][$classe_id], $tab_modele_pdf["chiffre_avec_zero"][$classe_id]);
										}

									}
								}
							}

						}


					}
				}
				if($fleche_evolution!="") {$fleche_evolution=" ".$fleche_evolution;}
				//$pdf->Cell($tab_modele_pdf["largeur_d_une_moyenne"][$classe_id], $hauteur_matiere/$nb_sousaffichage, $valeur.$fleche_evolution,1,2,'C',$tab_modele_pdf["active_reperage_eleve"][$classe_id]);
				$pdf->Cell($param_bull2016["largeur_acquis_col_moy"], $hauteur_matiere, $valeur.$fleche_evolution, 0, 2, 'C', 1);
				$valeur = "";


				// Colonne 5 : Moyenne classe
				$pdf->SetFillColor($param_bull2016["couleur_acquis_ligne_alt".($cpt_matiere%2+1)]["R"], $param_bull2016["couleur_acquis_ligne_alt".($cpt_matiere%2+1)]["V"], $param_bull2016["couleur_acquis_ligne_alt".($cpt_matiere%2+1)]["B"]);
				$pdf->Rect($param_bull2016["x_acquis_col_moyclasse"], $y_courant, $param_bull2016["largeur_acquis_col_moyclasse"], $hauteur_matiere, 'F');

				if ($param_bull2016["bull2016_moyminclassemax"]=='y') {
					$chaine_minclassemax="";
					// Min
					if (($tab_bull['moy_min_classe_grp'][$m]=="-")||($tab_bull['moy_min_classe_grp'][$m]=="")) {
						$valeur = "-";
					} else {
						$valeur = present_nombre($tab_bull['moy_min_classe_grp'][$m], $arrondi, $nb_chiffre_virgule, $chiffre_avec_zero);
					}
					$chaine_minclassemax.=$valeur."\n";

					// Classe
					if (($tab_bull['moy_classe_grp'][$m]=="-")||($tab_bull['moy_classe_grp'][$m]=="")) {
						$valeur = "-";
					}
					else {
						$valeur = present_nombre($tab_bull['moy_classe_grp'][$m], $arrondi, $nb_chiffre_virgule, $chiffre_avec_zero);
					}
					$chaine_minclassemax.=$valeur."\n";

					if (($tab_bull['moy_max_classe_grp'][$m]=="-")||($tab_bull['moy_max_classe_grp'][$m]=="")) {
						$valeur = "-";
					} else {
						$valeur = present_nombre($tab_bull['moy_max_classe_grp'][$m], $arrondi, $nb_chiffre_virgule, $chiffre_avec_zero);
					}
					$chaine_minclassemax.=$valeur;

					// Affichage min/classe/max avec cell_ajustee()
					$pdf->SetTextColor(0, 0, 0);
					$pdf->SetXY($param_bull2016["x_acquis_col_moyclasse"], $y_courant);
					$texte=$chaine_minclassemax;
					$taille_max_police=7;
					$taille_min_police=ceil($taille_max_police/3);

					$largeur_dispo=$param_bull2016["largeur_acquis_col_moyclasse"];
					$h_cell=$hauteur_matiere;

					//if(getSettingValue('suppr_balises_app_prof')=='y') {$texte=preg_replace('/<(.*)>/U','',$texte);}
					cell_ajustee(($texte),$pdf->GetX(),$pdf->GetY(),$largeur_dispo,$h_cell,$taille_max_police,$taille_min_police, '', 'C', 'C');
				}
				else {
					// Moyenne classe seulement
					$pdf->SetTextColor(0, 0, 0);
					$pdf->SetXY($param_bull2016["x_acquis_col_moyclasse"], $y_courant);
					$pdf->SetFont('DejaVu','',7);
					if (($tab_bull['moy_classe_grp'][$m]=="-")||($tab_bull['moy_classe_grp'][$m]=="")) {
						$valeur = "-";
					} else {
						$valeur = present_nombre($tab_bull['moy_classe_grp'][$m], $arrondi, $nb_chiffre_virgule, $chiffre_avec_zero);
					}
					$pdf->Cell($param_bull2016["largeur_acquis_col_moyclasse"], $hauteur_matiere, $valeur,'',0,'C');
				}

				$y_courant+=$hauteur_matiere+0.5;

				$cpt_matiere++;
			}
		}
		// Fin des enseignements de la table 'groupes'

		// Début des AID_e
		if($nb_AID_e_non_AP_EPI_Parcours>0) {
			if(isset($tab_bull['eleve'][$i]['aid_e'])) {
				for($m=0;$m<count($tab_bull['eleve'][$i]['aid_e']);$m++) {
					if($tab_bull['eleve'][$i]['aid_e'][$m]["type_aid"]==0) {
						// Colonne 1 : Matière, prof

						$pdf->SetFillColor($param_bull2016["couleur_acquis_ligne_alt".($cpt_matiere%2+1)]["R"], $param_bull2016["couleur_acquis_ligne_alt".($cpt_matiere%2+1)]["V"], $param_bull2016["couleur_acquis_ligne_alt".($cpt_matiere%2+1)]["B"]);
						$pdf->Rect($param_bull2016["x_acquis_col_1"], $y_courant, $param_bull2016["largeur_acquis_col_1"], $hauteur_matiere, 'F');
						$pdf->SetFillColor(0, 0, 0);

						$pdf->SetTextColor(0, 0, 0);
						$pdf->SetXY($param_bull2016["x_acquis_col_1"], $y_courant);
						$pdf->SetFont('DejaVu','',8);
						/*
						//$pdf->Cell($param_bull2016["largeur_bandeau_suivi_acquis"],7, "Suivi des acquis scolaires de l'élève",0,2,'C');
						$texte="Matiere $m ".$hauteur_matiere;
						$pdf->drawTextBox($texte, $param_bull2016["largeur_acquis_col_1"], $hauteur_matiere, 'L', 'M', 0);
						*/

						if($tab_bull['eleve'][$i]['aid_e'][$m]['display_nom']=='z') {
							if((isset($tab_bull['eleve'][$i]['aid_e'][$m]['aid_nom']))&&($tab_bull['eleve'][$i]['aid_e'][$m]['aid_nom']!="")) {
								$info_nom_matiere=$tab_bull['eleve'][$i]['aid_e'][$m]['aid_nom'];
							}
							elseif((isset($tab_bull['eleve'][$i]['aid_e'][$m]['nom_complet']))&&($tab_bull['eleve'][$i]['aid_e'][$m]['nom_complet']!="")) {
								$info_nom_matiere=$tab_bull['eleve'][$i]['aid_e'][$m]['nom_complet'];
							}
							else {
								$info_nom_matiere=$tab_bull['eleve'][$i]['aid_e'][$m]['nom'];
							}
						}
						elseif($tab_bull['eleve'][$i]['aid_e'][$m]['display_nom']=='x') {
							if((isset($tab_bull['eleve'][$i]['aid_e'][$m]['nom']))&&($tab_bull['eleve'][$i]['aid_e'][$m]['nom']!="")) {
								$info_nom_matiere=$tab_bull['eleve'][$i]['aid_e'][$m]['nom'];
							}
							else {
								$info_nom_matiere=$tab_bull['eleve'][$i]['aid_e'][$m]['nom_complet'];
							}

							if((isset($tab_bull['eleve'][$i]['aid_e'][$m]['aid_nom']))&&($tab_bull['eleve'][$i]['aid_e'][$m]['aid_nom']!="")) {
								if($info_nom_matiere!="") {
									$info_nom_matiere.=": ";
								}
								$info_nom_matiere.=$tab_bull['eleve'][$i]['aid_e'][$m]['aid_nom'];
							}
						}
						else {
							if((isset($tab_bull['eleve'][$i]['aid_e'][$m]['nom_complet']))&&($tab_bull['eleve'][$i]['aid_e'][$m]['nom_complet']!="")) {
								$info_nom_matiere=$tab_bull['eleve'][$i]['aid_e'][$m]['nom_complet'];
							}
							else {
								$info_nom_matiere=$tab_bull['eleve'][$i]['aid_e'][$m]['nom'];
							}
						}
						if($info_nom_matiere=="") {
							$info_nom_matiere="AID";
						}

						$hauteur_caractere_matiere=8;
						$cell_ajustee_texte_matiere_ratio_min_max=3;

						// 20130927 : cell_ajustee() ou pas sur le nom de matière/enseignement
						$cell_ajustee_texte_matiere=1;
						if((isset($cell_ajustee_texte_matiere))&&($cell_ajustee_texte_matiere==1)) {
							// On met un décalage pour ne pas coller le texte à la bordure
							$Y_decal_cell_ajustee=2;
							$pdf->SetXY($param_bull2016["x_acquis_col_1"], $y_courant+$Y_decal_cell_ajustee);

							$texte=$info_nom_matiere;
							$taille_max_police=$hauteur_caractere_matiere;
							$taille_min_police=ceil($taille_max_police/$cell_ajustee_texte_matiere_ratio_min_max);

							$largeur_dispo=$param_bull2016["largeur_acquis_col_1"];
							$h_cell=$hauteur_matiere/2-$Y_decal_cell_ajustee;

							cell_ajustee($texte,$pdf->GetX(),$pdf->GetY(),$largeur_dispo,$h_cell,$taille_max_police,$taille_min_police,'');
				
						}
						else {
							$val = $pdf->GetStringWidth($info_nom_matiere);
							$taille_texte = $param_bull2016["largeur_acquis_col_1"]-2;
							$grandeur_texte='test';
							while($grandeur_texte!='ok') {
								if($taille_texte<$val)
								{
									$hauteur_caractere_matiere = $hauteur_caractere_matiere-0.3;
									$pdf->SetFont('DejaVu','B',$hauteur_caractere_matiere);
									$val = $pdf->GetStringWidth($info_nom_matiere);
								}
								else {
									$grandeur_texte='ok';
								}
							}
							$grandeur_texte='test';
							$Y_decal=$y_courant;
							$pdf->SetXY($param_bull2016["x_acquis_col_1"], $Y_decal);
							$pdf->Cell($param_bull2016["largeur_acquis_col_1"], $hauteur_matiere/2, ($info_nom_matiere),'',1,'L');
						}
				

						// On note l'ordonnée pour le nom des professeurs
						$Y_decal = $y_courant+($hauteur_matiere/2);
						$pdf->SetXY($param_bull2016["x_acquis_col_1"], $Y_decal);
						$pdf->SetFont('DejaVu','',8);

						// nom des professeurs
						if ( isset($tab_bull['eleve'][$i]['aid_e'][$m]['aid_prof_resp_login'][0]) )
						{

							// Présentation en ligne des profs
							// On n'a pas forcément le formatage choisi pour la classe...
							//$text_prof=$tab_bull['groupe'][$m]["profs"]["proflist_string"]."  ";
							$text_prof="";
							for($loop_prof_grp=0;$loop_prof_grp<count($tab_bull['eleve'][$i]['aid_e'][$m]['aid_prof_resp_login']);$loop_prof_grp++) {
								$tmp_login_prof=$tab_bull['eleve'][$i]['aid_e'][$m]['aid_prof_resp_login'][$loop_prof_grp];
								if($loop_prof_grp>0) {$text_prof.=", ";}
								$text_prof.=affiche_utilisateur($tmp_login_prof,$tab_bull['eleve'][$i]['id_classe']);
							}

							if($text_prof!="") {
								//$espace_matiere_prof = $espace_entre_matier/2;
								$espace_matiere_prof = $hauteur_matiere/2;
								$hauteur_caractere_prof = 7;

								$texte=$text_prof;
								$taille_max_police=$hauteur_caractere_prof;
								$taille_min_police=ceil($hauteur_caractere_prof/3);

								$largeur_dispo=$param_bull2016["largeur_acquis_col_1"];
								$h_cell=$espace_matiere_prof;

								$pdf->SetX($param_bull2016["x_acquis_col_1"]);

								cell_ajustee(($texte),$pdf->GetX(),$pdf->GetY(),$largeur_dispo,$h_cell,$taille_max_police,$taille_min_police,'');
							}
						}

						// Colonne 2 : Éléments de programmes
						$pdf->SetFillColor($param_bull2016["couleur_acquis_ligne_alt".($cpt_matiere%2+1)]["R"], $param_bull2016["couleur_acquis_ligne_alt".($cpt_matiere%2+1)]["V"], $param_bull2016["couleur_acquis_ligne_alt".($cpt_matiere%2+1)]["B"]);
						$pdf->Rect($param_bull2016["x_acquis_col_2"], $y_courant, $param_bull2016["largeur_acquis_col_2"], $hauteur_matiere, 'F');
						$pdf->SetFillColor(0, 0, 0);

						$pdf->SetTextColor(0, 0, 0);
						$pdf->SetXY($param_bull2016["x_acquis_col_2"], $y_courant);
						$pdf->SetFont('DejaVu','',7);

						// Pas d'éléments de programmes dans les AID pour le moment

						/*
						$hauteur_caractere_appreciation = 9;
						$pdf->SetFont('DejaVu','',$hauteur_caractere_appreciation);

						$texte_Elements_Programmes="";

						if((isset($tab_bull['ElementsProgrammes']['ele'][$tab_bull['eleve'][$i]['login']][$tab_bull['groupe'][$m]['id']]))&&(is_array($tab_bull['ElementsProgrammes']['ele'][$tab_bull['eleve'][$i]['login']][$tab_bull['groupe'][$m]['id']]))) {
							for($loop_mep=0;$loop_mep<count($tab_bull['ElementsProgrammes']['ele'][$tab_bull['eleve'][$i]['login']][$tab_bull['groupe'][$m]['id']]);$loop_mep++) {
								if($texte_Elements_Programmes!="") {
									$texte_Elements_Programmes.="\n";
								}
								$texte_Elements_Programmes.=$tab_bull['ElementsProgrammes']['ele'][$tab_bull['eleve'][$i]['login']][$tab_bull['groupe'][$m]['id']][$loop_mep];
							}
						}

						if($texte_Elements_Programmes=="") {
							//$pdf->Cell($tab_modele_pdf["largeur_Elements_Programmes"][$classe_id], $espace_entre_matier, ($texte_Elements_Programmes),'LRB',0,'C');
						}
						else {
							// DEBUT AJUSTEMENT TAILLE ELEMENTS PROGRAMME
							$taille_texte_total = $pdf->GetStringWidth($texte_Elements_Programmes);
							//$largeur_dispo=$tab_modele_pdf["largeur_Elements_Programmes"][$classe_id];
							$largeur_dispo=$param_bull2016["largeur_acquis_col_2"];

							if($use_cell_ajustee=="n") {
								$nb_ligne_app = '2.8';
								$taille_texte_max = $nb_ligne_app * ($largeur_dispo-4);
								$grandeur_texte='test';	

								while($grandeur_texte!='ok') {
									if($taille_texte_max < $taille_texte_total)
									{
										$hauteur_caractere_appreciation = $hauteur_caractere_appreciation-0.3;
										$pdf->SetFont('DejaVu','',$hauteur_caractere_appreciation);
										$taille_texte_total = $pdf->GetStringWidth($texte_Elements_Programmes);
									}
									else {
										$grandeur_texte='ok';
									}
								}
								$grandeur_texte='test';
								$pdf->drawTextBox(($texte_Elements_Programmes), $largeur_dispo, $hauteur_matiere, 'J', 'M', 1);
							}
							else {
								$texte=$texte_Elements_Programmes;
								$taille_max_police=$hauteur_caractere_appreciation;
								$taille_min_police=ceil($taille_max_police/3);

								$h_cell=$hauteur_matiere;

								if(getSettingValue('suppr_balises_app_prof')=='y') {$texte=preg_replace('/<(.*)>/U','',$texte);}
								cell_ajustee(($texte),$pdf->GetX(),$pdf->GetY(),$largeur_dispo,$h_cell,$taille_max_police,$taille_min_police,'');
							}

						}
						*/


						// Colonne 3 : Appréciation
						$pdf->SetFillColor($param_bull2016["couleur_acquis_ligne_alt".($cpt_matiere%2+1)]["R"], $param_bull2016["couleur_acquis_ligne_alt".($cpt_matiere%2+1)]["V"], $param_bull2016["couleur_acquis_ligne_alt".($cpt_matiere%2+1)]["B"]);
						$pdf->Rect($param_bull2016["x_acquis_col_3"], $y_courant, $param_bull2016["largeur_acquis_col_3"], $hauteur_matiere, 'F');

						$pdf->SetFillColor(0, 0, 0);
						$pdf->SetXY($param_bull2016["x_acquis_col_3"], $y_courant);

						// calcul de la taille du texte des appréciations
						$hauteur_caractere_appreciation = 8;
						$pdf->SetFont('DejaVu','',$hauteur_caractere_appreciation);

						//suppression des espaces en début et en fin
						//$app_aff = trim($tab_bull['app'][$m][$i]);
						$app_aff="";
						if($tab_bull['eleve'][$i]['aid_e'][$m]['message']!='') {
							$app_aff.=$tab_bull['eleve'][$i]['aid_e'][$m]['message'];
						}
						//if($app_aff!='') {$app_aff.=" ";}
						if(($tab_bull['eleve'][$i]['aid_e'][$m]['display_nom']=='y')&&($tab_bull['eleve'][$i]['aid_e'][$m]['aid_nom']!='')) {
							if($app_aff!='') {$app_aff.=" ";}
							$app_aff.=$tab_bull['eleve'][$i]['aid_e'][$m]['aid_nom'];
						}
						if($app_aff!='') {$app_aff.="\n";}
						$app_aff.=trim($tab_bull['eleve'][$i]['aid_e'][$m]['aid_appreciation']);

						fich_debug_bull("__________________________________________\n");
						fich_debug_bull("$app_aff\n");
						fich_debug_bull("__________________________________________\n");

						// DEBUT AJUSTEMENT TAILLE APPRECIATION
						$taille_texte_total = $pdf->GetStringWidth($app_aff);
						//$largeur_appreciation2 = $largeur_appreciation - $largeur_sous_matiere;
						$largeur_appreciation2=$param_bull2016["largeur_acquis_col_3"];

						if($use_cell_ajustee=="n") {
							//$taille_texte = (($espace_entre_matier/3)*$largeur_appreciation2);
							$nb_ligne_app = '2.8';
							//$nb_ligne_app = '3.8';
							//$nb_ligne_app = '4.8';
							$taille_texte_max = $nb_ligne_app * ($largeur_appreciation2-4);
							//$taille_texte_max = $nb_ligne_app * ($largeur_appreciation2);
							$grandeur_texte='test';

							fich_debug_bull("\$taille_texte_total=$taille_texte_total\n");
							fich_debug_bull("\$largeur_appreciation2=$largeur_appreciation2\n");
							fich_debug_bull("\$nb_ligne_app=$nb_ligne_app\n");
							//fich_debug_bull("\$taille_texte_max = \$nb_ligne_app * (\$largeur_appreciation2-4)=$nb_ligne_app * ($largeur_appreciation2-4)=$taille_texte_max\n");
							fich_debug_bull("\$taille_texte_max = \$nb_ligne_app * (\$largeur_appreciation2)=$nb_ligne_app * ($largeur_appreciation2)=$taille_texte_max\n");

							while($grandeur_texte!='ok') {
								if($taille_texte_max < $taille_texte_total)
								{
									$hauteur_caractere_appreciation = $hauteur_caractere_appreciation-0.3;
									//$hauteur_caractere_appreciation = $hauteur_caractere_appreciation-0.1;
									$pdf->SetFont('DejaVu','',$hauteur_caractere_appreciation);
									$taille_texte_total = $pdf->GetStringWidth($app_aff);
								}
								else {
									$grandeur_texte='ok';
								}
							}
							$grandeur_texte='test';
							$pdf->drawTextBox(($app_aff), $largeur_appreciation2, $hauteur_matiere, 'J', 'M', 1);
						}
						else {
							$texte=$app_aff;
							//$texte="Bla bla\nbli ".$app_aff;
							$taille_max_police=$hauteur_caractere_appreciation;
							$taille_min_police=ceil($taille_max_police/3);

							$largeur_dispo=$largeur_appreciation2;
							$h_cell=$hauteur_matiere;

							if(getSettingValue('suppr_balises_app_prof')=='y') {$texte=preg_replace('/<(.*)>/U','',$texte);}
							cell_ajustee(($texte),$pdf->GetX(),$pdf->GetY(),$largeur_dispo,$h_cell,$taille_max_police,$taille_min_police,'');
						}


						// Colonne 4 : Moyenne élève
						$pdf->SetFillColor($param_bull2016["couleur_acquis_colonne_moyenne_ligne_alt".($cpt_matiere%2+1)]["R"], $param_bull2016["couleur_acquis_colonne_moyenne_ligne_alt".($cpt_matiere%2+1)]["V"], $param_bull2016["couleur_acquis_colonne_moyenne_ligne_alt".($cpt_matiere%2+1)]["B"]);
						$pdf->Rect($param_bull2016["x_acquis_col_moy"], $y_courant, $param_bull2016["largeur_acquis_col_moy"], $hauteur_matiere, 'F');

						$pdf->SetTextColor(0, 0, 0);
						$pdf->SetXY($param_bull2016["x_acquis_col_moy"], $y_courant);
						$pdf->SetFont('DejaVu', 'B', 8);
						//$pdf->Cell($param_bull2016["largeur_acquis_col_moy"], $hauteur_matiere, $texte,0,2,'C');
						//$pdf->drawTextBox($texte, $param_bull2016["largeur_acquis_col_moy"], $param_bull2016["hauteur_acquis_ligne_entete"], 'C', 'M', 0);

						$fleche_evolution="";

						// On filtre si la moyenne est vide, on affiche seulement un tiret
						if ($tab_bull['eleve'][$i]['aid_e'][$m]['aid_note']=="-") {
							$valeur = "-";
						}
						elseif ($tab_bull['eleve'][$i]['aid_e'][$m]['aid_statut']!="") {
							if($tab_bull['eleve'][$i]['aid_e'][$m]['aid_statut']=="other") {
								$valeur = "-";
							}
							else {
								$valeur=$tab_bull['eleve'][$i]['aid_e'][$m]['aid_statut'];
							}
						}
						else {
							$valeur = present_nombre($tab_bull['eleve'][$i]['aid_e'][$m]['aid_note'], $arrondi, $nb_chiffre_virgule, $chiffre_avec_zero);
						}

						$pdf->Cell($param_bull2016["largeur_acquis_col_moy"], $hauteur_matiere, $valeur.$fleche_evolution, 0, 2, 'C', 1);
						$valeur = "";


						// Colonne 5 : Moyenne classe
						$pdf->SetFillColor($param_bull2016["couleur_acquis_ligne_alt".($cpt_matiere%2+1)]["R"], $param_bull2016["couleur_acquis_ligne_alt".($cpt_matiere%2+1)]["V"], $param_bull2016["couleur_acquis_ligne_alt".($cpt_matiere%2+1)]["B"]);
						$pdf->Rect($param_bull2016["x_acquis_col_moyclasse"], $y_courant, $param_bull2016["largeur_acquis_col_moyclasse"], $hauteur_matiere, 'F');


						if ($param_bull2016["bull2016_moyminclassemax"]=='y') {
							$chaine_minclassemax="";
							// Min
							if (($tab_bull['eleve'][$i]['aid_e'][$m]['aid_note_min']=="-")||($tab_bull['eleve'][$i]['aid_e'][$m]['aid_note_min']=="")) {
								$valeur = "-";
							} else {
								$valeur = present_nombre($tab_bull['eleve'][$i]['aid_e'][$m]['aid_note_min'], $arrondi, $nb_chiffre_virgule, $chiffre_avec_zero);
							}
							$chaine_minclassemax.=$valeur."\n";

							// Classe
							if (($tab_bull['eleve'][$i]['aid_e'][$m]['aid_note_moyenne']=="-")||($tab_bull['eleve'][$i]['aid_e'][$m]['aid_note_moyenne']=="")) {
								$valeur = "-";
							}
							else {
								$valeur = present_nombre($tab_bull['eleve'][$i]['aid_e'][$m]['aid_note_moyenne'], $arrondi, $nb_chiffre_virgule, $chiffre_avec_zero);
							}
							$chaine_minclassemax.=$valeur."\n";

							if (($tab_bull['eleve'][$i]['aid_e'][$m]['aid_note_max']=="-")||($tab_bull['eleve'][$i]['aid_e'][$m]['aid_note_max']=="")) {
								$valeur = "-";
							} else {
								$valeur = present_nombre($tab_bull['eleve'][$i]['aid_e'][$m]['aid_note_max'], $arrondi, $nb_chiffre_virgule, $chiffre_avec_zero);
							}
							$chaine_minclassemax.=$valeur;

							// Affichage min/classe/max avec cell_ajustee()
							$pdf->SetTextColor(0, 0, 0);
							$pdf->SetXY($param_bull2016["x_acquis_col_moyclasse"], $y_courant);
							$texte=$chaine_minclassemax;
							$taille_max_police=7;
							$taille_min_police=ceil($taille_max_police/3);

							$largeur_dispo=$param_bull2016["largeur_acquis_col_moyclasse"];
							$h_cell=$hauteur_matiere;

							//if(getSettingValue('suppr_balises_app_prof')=='y') {$texte=preg_replace('/<(.*)>/U','',$texte);}
							cell_ajustee(($texte),$pdf->GetX(),$pdf->GetY(),$largeur_dispo,$h_cell,$taille_max_police,$taille_min_police, '', 'C', 'C');
						}
						else {
							$pdf->SetTextColor(0, 0, 0);
							$pdf->SetXY($param_bull2016["x_acquis_col_moyclasse"], $y_courant);
							$pdf->SetFont('DejaVu','',7);

							if (($tab_bull['eleve'][$i]['aid_e'][$m]['aid_note_moyenne']=="-")||($tab_bull['eleve'][$i]['aid_e'][$m]['aid_note_moyenne']=="")) {
								$valeur = "-";
							}
							else {
								$valeur = present_nombre($tab_bull['eleve'][$i]['aid_e'][$m]['aid_note_moyenne'], $arrondi, $nb_chiffre_virgule, $chiffre_avec_zero);
							}

							$pdf->Cell($param_bull2016["largeur_acquis_col_moyclasse"], $hauteur_matiere, $valeur,'',0,'C');
						}

						$y_courant+=$hauteur_matiere+0.5;

						$cpt_matiere++;
					}
				}
			}
		}
		// Fin des AID_e


		// FILIGRANE PERIODE NON CLOSE
		if($tab_bull['verouiller']=="N") {
			$pdf->SetFont('DejaVu','B',40);
			$pdf->SetTextColor(255,192,203);
			//$pdf->TextWithRotation(40,190,$texte_filigrame[$classe_id],45);
			$pdf->TextWithRotation(40,210,"ATTENTION : Période non close",45);
			$pdf->SetTextColor(0,0,0);
		}
		/*
		// $tab_modele_pdf n'est pas défini en bull2016
		elseif($tab_modele_pdf["affiche_filigrame"][$classe_id]==='1'){
			$pdf->SetFont('DejaVu','B',50);
			$pdf->SetTextColor(255,192,203);
			//$pdf->TextWithRotation(40,190,$texte_filigrame[$classe_id],45);
			$pdf->TextWithRotation(40,190,$tab_modele_pdf["texte_filigrame"][$classe_id],45);
			$pdf->SetTextColor(0,0,0);
		}
		*/

		//=========================================
		//=========================================
		//=========================================

		// Deuxième page:
		$pdf->AddPage(); //ajout d'une page au document
		// En fin de cycle (fin d'année de l'année de fin de cycle), il faut même une autre page... prévoir le recto/verso 

		// AP, EPI, Parcours... (ajouter un tag sur des enseignements pour ne pas les faire apparaitre dans le tableau précédent mais dans les AP, EPI,...) et permettre aussi une gestion des AP via des AID (ajouter un tag AP/EPI/Parcours aux AID pour les faire apparaitre ici)
		// Que fait-on des AID dans le nouveau bulletin s'il ne s'agit ni d'AP, ni d'EPI, ni d'un Parcours...

		if($cycle==4) {
			$y_bandeau_bilan_acquisitions=$param_bull2016["y_bandeau_bilan_acquisitions_cycle_4"];
			$y_bilan_acquisitions=$param_bull2016["y_bilan_acquisitions_cycle_4"];
			$hauteur_bilan_acquisitions=$param_bull2016["hauteur_bilan_acquisitions_cycle_4"];

			//=========================================

			$total_hauteur_EPI_AP_Parcours=$param_bull2016["y_bandeau_bilan_acquisitions_cycle_4"]-$param_bull2016["y_EPI_AP_Parcours"]-$param_bull2016["espace_vertical_avant_bandeau_Bilan"]-3*$param_bull2016["hauteur_ligne_titre_EPI_AP_Parcours"]-2*$param_bull2016["espace_vertical_entre_sections_EPI_AP_Parcours"];
			// $total_hauteur_EPI_AP_Parcours à diviser par le nombre d'EPI/AP/Parcours suivis par l'élève

			$nb_EPI_AP_Parcours=0;
			$nb_EPI=0;
			$nb_AP=0;
			$nb_Parcours=0;
			for($m=0;$m<count($tab_bull['groupe']);$m++) {
				//if(isset($tab_bull['note'][$m][$i])) {
				// On n'affiche pas ici les groupes correspondant à AP, EPI ou Parcours
				if((isset($tab_bull['note'][$m][$i]))&&
				(isset($tab_bull['groupe'][$m]['type_grp'][0]))) {
					if($tab_bull['groupe'][$m]['type_grp'][0]['nom_court']=="EPI") {
						$nb_EPI++;
					}
					elseif($tab_bull['groupe'][$m]['type_grp'][0]['nom_court']=="AP") {
						$nb_AP++;
					}
					elseif($tab_bull['groupe'][$m]['type_grp'][0]['nom_court']=="Parcours") {
						$nb_Parcours++;
					}
				}
			}
			$nb_EPI_AP_Parcours=$nb_EPI+$nb_AP+$nb_Parcours;

			// Il faudrait encore ajouter les AID tagués EPI/AP/Parcours

			//=========================================

		}
		else {
			$y_bandeau_bilan_acquisitions=$param_bull2016["y_bandeau_bilan_acquisitions_cycle_3"];
			$y_bilan_acquisitions=$param_bull2016["y_bilan_acquisitions_cycle_3"];
			$hauteur_bilan_acquisitions=$param_bull2016["hauteur_bilan_acquisitions_cycle_3"];

			//=========================================

			$total_hauteur_EPI_AP_Parcours=$param_bull2016["y_bandeau_bilan_acquisitions_cycle_3"]-$param_bull2016["y_EPI_AP_Parcours"]-$param_bull2016["espace_vertical_avant_bandeau_Bilan"]-3*$param_bull2016["hauteur_ligne_titre_EPI_AP_Parcours"]-2*$param_bull2016["espace_vertical_entre_sections_EPI_AP_Parcours"];
			// $total_hauteur_EPI_AP_Parcours à diviser par le nombre d'EPI/AP/Parcours suivis par l'élève

			$nb_EPI_AP_Parcours=0;
			$nb_EPI=0; // Ce nombre va rester à 0 pour le cycle 3
			$nb_AP=0;
			$nb_Parcours=0;
			for($m=0;$m<count($tab_bull['groupe']);$m++) {
				//if(isset($tab_bull['note'][$m][$i])) {
				// On n'affiche pas ici les groupes correspondant à AP, EPI ou Parcours
				if((isset($tab_bull['note'][$m][$i]))&&
				(isset($tab_bull['groupe'][$m]['type_grp'][0]))) {
					if($tab_bull['groupe'][$m]['type_grp'][0]['nom_court']=="AP") {
						$nb_AP++;
					}
					elseif($tab_bull['groupe'][$m]['type_grp'][0]['nom_court']=="Parcours") {
						$nb_Parcours++;
					}
				}
			}
			$nb_EPI_AP_Parcours=$nb_EPI+$nb_AP+$nb_Parcours;

			// Il faudrait encore ajouter les AID tagués EPI/AP/Parcours

			//=========================================

		}

		$nb_EPI_AP_Parcours+=$nb_AID_AP_EPI_Parcours;
		$nb_AP=$nb_AP+count($indice_AID_b_AP)+count($indice_AID_e_AP);
		$nb_EPI=$nb_EPI+count($indice_AID_b_EPI)+count($indice_AID_e_EPI);
		$nb_Parcours=$nb_Parcours+count($indice_AID_b_Parcours)+count($indice_AID_e_Parcours);

		$hauteur_prise_par_EPI_AP_Parcours_page_2=$param_bull2016["y_EPI_AP_Parcours"];

		if($nb_EPI_AP_Parcours>0) {
			$hauteur_EPI_AP_Parcours=$total_hauteur_EPI_AP_Parcours/$nb_EPI_AP_Parcours;
			// On limite la hauteur
			if($hauteur_EPI_AP_Parcours>30) {
				$hauteur_EPI_AP_Parcours=30;
			}

			$y_courant=$param_bull2016["y_EPI_AP_Parcours"];

			// EPI en cycle 4 seulement

			if($nb_EPI>0) {

				$pdf->SetFillColor($param_bull2016["couleur_bandeau_EPI"]["R"], $param_bull2016["couleur_bandeau_EPI"]["V"], $param_bull2016["couleur_bandeau_EPI"]["B"]);
				$pdf->Rect($param_bull2016["x_EPI_AP_Parcours"], $y_courant, $param_bull2016["largeur_EPI_AP_Parcours"], $param_bull2016["hauteur_ligne_titre_EPI_AP_Parcours"], 'F');

				$pdf->SetFillColor(0, 0, 0);
				//$pdf->SetTextColor(255, 255, 255);
				$pdf->SetTextColor(0, 0, 0);
				$pdf->SetXY($param_bull2016["x_EPI_AP_Parcours"], $y_courant);
				$pdf->SetFont('DejaVu','',8);
				$pdf->Cell($param_bull2016["largeur_EPI_AP_Parcours"],7, "Enseignements pratiques interdisciplinaires : projets réalisés et implication de l'élève",0,2,'L');
				//." nb_EPI=".$nb_EPI." nb_AP=".$nb_AP." nb_Parcours=".$nb_Parcours

				$cpt_matiere=0;
				$y_courant+=$param_bull2016["hauteur_ligne_titre_EPI_AP_Parcours"]+0.5;

				// AID_b de type EPI
				for($loop=0;$loop<count($indice_AID_b_EPI);$loop++) {
					$m=$indice_AID_b_EPI[$loop];

					// Colonne 1 : Matière, prof

					$pdf->SetFillColor($param_bull2016["couleur_EPI_alt".($cpt_matiere%2+1)]["R"], $param_bull2016["couleur_EPI_alt".($cpt_matiere%2+1)]["V"], $param_bull2016["couleur_EPI_alt".($cpt_matiere%2+1)]["B"]);
					$pdf->Rect($param_bull2016["x_EPI_AP_Parcours"], $y_courant, $param_bull2016["largeur_EPI_AP_Parcours_col_1"], $hauteur_EPI_AP_Parcours, 'F');
					$pdf->SetFillColor(0, 0, 0);

					$pdf->SetTextColor(0, 0, 0);
					$pdf->SetXY($param_bull2016["x_EPI_AP_Parcours"], $y_courant);
					$pdf->SetFont('DejaVu','',8);

					if($tab_bull['eleve'][$i]['aid_b'][$m]['display_nom']=='z') {
						if((isset($tab_bull['eleve'][$i]['aid_b'][$m]['aid_nom']))&&($tab_bull['eleve'][$i]['aid_b'][$m]['aid_nom']!="")) {
							$info_nom_matiere=$tab_bull['eleve'][$i]['aid_b'][$m]['aid_nom'];
						}
						elseif((isset($tab_bull['eleve'][$i]['aid_b'][$m]['nom_complet']))&&($tab_bull['eleve'][$i]['aid_b'][$m]['nom_complet']!="")) {
							$info_nom_matiere=$tab_bull['eleve'][$i]['aid_b'][$m]['nom_complet'];
						}
						else {
							$info_nom_matiere=$tab_bull['eleve'][$i]['aid_b'][$m]['nom'];
						}
					}
					elseif($tab_bull['eleve'][$i]['aid_b'][$m]['display_nom']=='x') {
						if((isset($tab_bull['eleve'][$i]['aid_b'][$m]['nom']))&&($tab_bull['eleve'][$i]['aid_b'][$m]['nom']!="")) {
							$info_nom_matiere=$tab_bull['eleve'][$i]['aid_b'][$m]['nom'];
						}
						else {
							$info_nom_matiere=$tab_bull['eleve'][$i]['aid_b'][$m]['nom_complet'];
						}

						if((isset($tab_bull['eleve'][$i]['aid_b'][$m]['aid_nom']))&&($tab_bull['eleve'][$i]['aid_b'][$m]['aid_nom']!="")) {
							if($info_nom_matiere!="") {
								$info_nom_matiere.=": ";
							}
							$info_nom_matiere.=$tab_bull['eleve'][$i]['aid_b'][$m]['aid_nom'];
						}
					}
					else {
						if((isset($tab_bull['eleve'][$i]['aid_b'][$m]['nom_complet']))&&($tab_bull['eleve'][$i]['aid_b'][$m]['nom_complet']!="")) {
							$info_nom_matiere=$tab_bull['eleve'][$i]['aid_b'][$m]['nom_complet'];
						}
						else {
							$info_nom_matiere=$tab_bull['eleve'][$i]['aid_b'][$m]['nom'];
						}
					}
					if($info_nom_matiere=="") {
						$info_nom_matiere="AID";
					}

					$hauteur_caractere_matiere=8;
					$cell_ajustee_texte_matiere_ratio_min_max=3;

					// Forcé pour le moment
					$cell_ajustee_texte_matiere=1;
					if((isset($cell_ajustee_texte_matiere))&&($cell_ajustee_texte_matiere==1)) {
						// On met un décalage pour ne pas coller le texte à la bordure
						$Y_decal_cell_ajustee=2;
						$pdf->SetXY($param_bull2016["x_EPI_AP_Parcours"], $y_courant+$Y_decal_cell_ajustee);

						$texte=$info_nom_matiere;
						$taille_max_police=$hauteur_caractere_matiere;
						$taille_min_police=ceil($taille_max_police/$cell_ajustee_texte_matiere_ratio_min_max);

						$largeur_dispo=$param_bull2016["largeur_EPI_AP_Parcours_col_1"];
						$h_cell=$hauteur_EPI_AP_Parcours/2-$Y_decal_cell_ajustee;

						cell_ajustee($texte,$pdf->GetX(),$pdf->GetY(),$largeur_dispo,$h_cell,$taille_max_police,$taille_min_police,'');
			
					}
					else {
						$val = $pdf->GetStringWidth($info_nom_matiere);
						$taille_texte = $param_bull2016["largeur_EPI_AP_Parcours_col_1"]-2;
						$grandeur_texte='test';
						while($grandeur_texte!='ok') {
							if($taille_texte<$val)
							{
								$hauteur_caractere_matiere = $hauteur_caractere_matiere-0.3;
								$pdf->SetFont('DejaVu','B',$hauteur_caractere_matiere);
								$val = $pdf->GetStringWidth($info_nom_matiere);
							}
							else {
								$grandeur_texte='ok';
							}
						}
						$grandeur_texte='test';
						$Y_decal=$y_courant;
						$pdf->SetXY($param_bull2016["x_EPI_AP_Parcours"], $Y_decal);
						$pdf->Cell($param_bull2016["largeur_EPI_AP_Parcours_col_1"], $hauteur_EPI_AP_Parcours/2, ($info_nom_matiere),'',1,'L');
					}


					// On note l'ordonnée pour le nom des professeurs
					$Y_decal = $y_courant+($hauteur_EPI_AP_Parcours/2);
					$pdf->SetXY($param_bull2016["x_EPI_AP_Parcours"], $Y_decal);
					$pdf->SetFont('DejaVu','',8);

					// nom des professeurs
					if ( isset($tab_bull['eleve'][$i]['aid_b'][$m]['aid_prof_resp_login'][0]) )
					{

						// Présentation en ligne des profs
						// On n'a pas forcément le formatage choisi pour la classe...
						//$text_prof=$tab_bull['groupe'][$m]["profs"]["proflist_string"]."  ";
						$text_prof="";
						for($loop_prof_grp=0;$loop_prof_grp<count($tab_bull['eleve'][$i]['aid_b'][$m]['aid_prof_resp_login']);$loop_prof_grp++) {
							$tmp_login_prof=$tab_bull['eleve'][$i]['aid_b'][$m]['aid_prof_resp_login'][$loop_prof_grp];
							if($loop_prof_grp>0) {$text_prof.=", ";}
							$text_prof.=affiche_utilisateur($tmp_login_prof,$tab_bull['eleve'][$i]['id_classe']);
						}

						if($text_prof!="") {
							//$espace_matiere_prof = $espace_entre_matier/2;
							$espace_matiere_prof = $hauteur_EPI_AP_Parcours/2;
							$hauteur_caractere_prof = 7;

							$texte=$text_prof;
							$taille_max_police=$hauteur_caractere_prof;
							$taille_min_police=ceil($hauteur_caractere_prof/3);

							$largeur_dispo=$param_bull2016["largeur_EPI_AP_Parcours_col_1"];
							$h_cell=$espace_matiere_prof;

							$pdf->SetX($param_bull2016["x_EPI_AP_Parcours"]);

							cell_ajustee(($texte),$pdf->GetX(),$pdf->GetY(),$largeur_dispo,$h_cell,$taille_max_police,$taille_min_police,'');
						}
					}


					// Colonne 2 : Appréciation
					$pdf->SetFillColor($param_bull2016["couleur_EPI_alt".($cpt_matiere%2+1)]["R"], $param_bull2016["couleur_EPI_alt".($cpt_matiere%2+1)]["V"], $param_bull2016["couleur_EPI_alt".($cpt_matiere%2+1)]["B"]);
					$pdf->Rect($param_bull2016["x_EPI_AP_Parcours_col_2"], $y_courant, $param_bull2016["largeur_EPI_AP_Parcours_col_2"], $hauteur_EPI_AP_Parcours, 'F');

					$pdf->SetFillColor(0, 0, 0);
					$pdf->SetXY($param_bull2016["x_EPI_AP_Parcours_col_2"], $y_courant);

					// calcul de la taille du texte des appréciations
					$hauteur_caractere_appreciation = 8;
					$pdf->SetFont('DejaVu','',$hauteur_caractere_appreciation);

					//suppression des espaces en début et en fin
					//$app_aff = trim($tab_bull['app'][$m][$i]);
					$app_aff="";
					if($tab_bull['eleve'][$i]['aid_b'][$m]['message']!='') {
						$app_aff.=$tab_bull['eleve'][$i]['aid_b'][$m]['message'];
					}
					//if($app_aff!='') {$app_aff.=" ";}
					if(($tab_bull['eleve'][$i]['aid_b'][$m]['display_nom']=='y')&&($tab_bull['eleve'][$i]['aid_b'][$m]['aid_nom']!='')) {
						if($app_aff!='') {$app_aff.=" ";}
						$app_aff.=$tab_bull['eleve'][$i]['aid_b'][$m]['aid_nom'];
					}
					if($app_aff!='') {$app_aff.="\n";}
					$app_aff.=trim($tab_bull['eleve'][$i]['aid_b'][$m]['aid_appreciation']);

					fich_debug_bull("__________________________________________\n");
					fich_debug_bull("$app_aff\n");
					fich_debug_bull("__________________________________________\n");

					// DEBUT AJUSTEMENT TAILLE APPRECIATION
					$taille_texte_total = $pdf->GetStringWidth($app_aff);
					//$largeur_appreciation2 = $largeur_appreciation - $largeur_sous_matiere;
					$largeur_appreciation2=$param_bull2016["largeur_EPI_AP_Parcours_col_2"];

					if($use_cell_ajustee=="n") {
						//$taille_texte = (($espace_entre_matier/3)*$largeur_appreciation2);
						$nb_ligne_app = '2.8';
						//$nb_ligne_app = '3.8';
						//$nb_ligne_app = '4.8';
						$taille_texte_max = $nb_ligne_app * ($largeur_appreciation2-4);
						//$taille_texte_max = $nb_ligne_app * ($largeur_appreciation2);
						$grandeur_texte='test';

						fich_debug_bull("\$taille_texte_total=$taille_texte_total\n");
						fich_debug_bull("\$largeur_appreciation2=$largeur_appreciation2\n");
						fich_debug_bull("\$nb_ligne_app=$nb_ligne_app\n");
						//fich_debug_bull("\$taille_texte_max = \$nb_ligne_app * (\$largeur_appreciation2-4)=$nb_ligne_app * ($largeur_appreciation2-4)=$taille_texte_max\n");
						fich_debug_bull("\$taille_texte_max = \$nb_ligne_app * (\$largeur_appreciation2)=$nb_ligne_app * ($largeur_appreciation2)=$taille_texte_max\n");

						while($grandeur_texte!='ok') {
							if($taille_texte_max < $taille_texte_total)
							{
								$hauteur_caractere_appreciation = $hauteur_caractere_appreciation-0.3;
								//$hauteur_caractere_appreciation = $hauteur_caractere_appreciation-0.1;
								$pdf->SetFont('DejaVu','',$hauteur_caractere_appreciation);
								$taille_texte_total = $pdf->GetStringWidth($app_aff);
							}
							else {
								$grandeur_texte='ok';
							}
						}
						$grandeur_texte='test';
						$pdf->drawTextBox(($app_aff), $largeur_appreciation2, $hauteur_EPI_AP_Parcours, 'J', 'M', 1);
					}
					else {
						$texte=$app_aff;
						//$texte="Bla bla\nbli ".$app_aff;
						$taille_max_police=$hauteur_caractere_appreciation;
						$taille_min_police=ceil($taille_max_police/3);

						$largeur_dispo=$largeur_appreciation2;
						$h_cell=$hauteur_EPI_AP_Parcours;

						if(getSettingValue('suppr_balises_app_prof')=='y') {$texte=preg_replace('/<(.*)>/U','',$texte);}
						cell_ajustee(($texte),$pdf->GetX(),$pdf->GetY(),$largeur_dispo,$h_cell,$taille_max_police,$taille_min_police,'');
					}

					$y_courant+=$hauteur_EPI_AP_Parcours+0.5;
					$cpt_matiere++;


				}
				// Fin des AID_b tagués EPI

				// Enseignements de la table 'groupes' tagués EPI
				for($m=0;$m<count($tab_bull['groupe']);$m++) {
					//if(isset($tab_bull['note'][$m][$i])) {
					// On n'affiche pas ici les groupes correspondant à AP, EPI ou Parcours
					if((isset($tab_bull['note'][$m][$i]))&&
					(isset($tab_bull['groupe'][$m]['type_grp'][0]))&&
					($tab_bull['groupe'][$m]['type_grp'][0]['nom_court']=="EPI")) {

						// Colonne 1 : Matière, prof

						$pdf->SetFillColor($param_bull2016["couleur_EPI_alt".($cpt_matiere%2+1)]["R"], $param_bull2016["couleur_EPI_alt".($cpt_matiere%2+1)]["V"], $param_bull2016["couleur_EPI_alt".($cpt_matiere%2+1)]["B"]);
						$pdf->Rect($param_bull2016["x_EPI_AP_Parcours"], $y_courant, $param_bull2016["largeur_EPI_AP_Parcours_col_1"], $hauteur_EPI_AP_Parcours, 'F');
						$pdf->SetFillColor(0, 0, 0);

						$pdf->SetTextColor(0, 0, 0);
						$pdf->SetXY($param_bull2016["x_EPI_AP_Parcours"], $y_courant);
						$pdf->SetFont('DejaVu','',8);

						if(getSettingValue('bul_rel_nom_matieres')=='nom_groupe') {
							$info_nom_matiere=$tab_bull['groupe'][$m]['name'];
						}
						elseif(getSettingValue('bul_rel_nom_matieres')=='description_groupe') {
							$info_nom_matiere=$tab_bull['groupe'][$m]['description'];
						}
						else {
							// Pour parer au bug sur la suppression de matière alors que des groupes sont conservés:
							if(isset($tab_bull['groupe'][$m]['matiere']['nom_complet'])) {
								$info_nom_matiere=$tab_bull['groupe'][$m]['matiere']['nom_complet'];
							}
							else {
								$info_nom_matiere=$tab_bull['groupe'][$m]['name']." (".$tab_bull['groupe'][$m]['id'].")";
							}
						}

						$hauteur_caractere_matiere=8;
						$cell_ajustee_texte_matiere_ratio_min_max=3;

						// 20130927 : cell_ajustee() ou pas sur le nom de matière/enseignement
						$cell_ajustee_texte_matiere=1;
						if((isset($cell_ajustee_texte_matiere))&&($cell_ajustee_texte_matiere==1)) {
							// Encadrement
							//$pdf->Cell($tab_modele_pdf["largeur_matiere"][$classe_id], $espace_entre_matier, "",'LRBT',1,'L');

							// cell_ajustee() ne centre pas verticalement le texte.
							// On met un décalage pour ne pas coller le texte à la bordure
							$Y_decal_cell_ajustee=2;
							// On repositionne et on inscrit le nom de matière sur la moitié de la hauteur de la cellule
							//$pdf->SetXY($X_bloc_matiere, $Y_decal+$Y_decal_cell_ajustee);
							//$pdf->SetXY($param_bull2016["x_acquis_col_1"], $y_courant);

							$pdf->SetXY($param_bull2016["x_EPI_AP_Parcours"], $y_courant+$Y_decal_cell_ajustee);

							$texte=$info_nom_matiere;
							$taille_max_police=$hauteur_caractere_matiere;
							//$taille_min_police=ceil($taille_max_police/$tab_modele_pdf["cell_ajustee_texte_matiere_ratio_min_max"][$classe_id]);
							$taille_min_police=ceil($taille_max_police/$cell_ajustee_texte_matiere_ratio_min_max);

							$largeur_dispo=$param_bull2016["largeur_EPI_AP_Parcours_col_1"];
							//$h_cell=$espace_entre_matier/2-$Y_decal_cell_ajustee;
							$h_cell=$hauteur_EPI_AP_Parcours/2-$Y_decal_cell_ajustee;

							//cell_ajustee("<b>".$texte."</b>",$pdf->GetX(),$pdf->GetY(),$largeur_dispo,$h_cell,$taille_max_police,$taille_min_police,'');
							cell_ajustee($texte,$pdf->GetX(),$pdf->GetY(),$largeur_dispo,$h_cell,$taille_max_police,$taille_min_police,'');
			
						}
						else {
							$val = $pdf->GetStringWidth($info_nom_matiere);
							$taille_texte = $param_bull2016["largeur_EPI_AP_Parcours_col_1"]-2;
							$grandeur_texte='test';
							while($grandeur_texte!='ok') {
								if($taille_texte<$val)
								{
									$hauteur_caractere_matiere = $hauteur_caractere_matiere-0.3;
									$pdf->SetFont('DejaVu','',$hauteur_caractere_matiere);
									$val = $pdf->GetStringWidth($info_nom_matiere);
								}
								else {
									$grandeur_texte='ok';
								}
							}
							$grandeur_texte='test';
							$Y_decal=$y_courant;
							$pdf->SetXY($param_bull2016["x_EPI_AP_Parcours"], $Y_decal);
							$pdf->Cell($param_bull2016["largeur_EPI_AP_Parcours_col_1"], $hauteur_EPI_AP_Parcours/2, ($info_nom_matiere),'',1,'L');
						}
			

						// On note l'ordonnée pour le nom des professeurs
						$Y_decal = $y_courant+($hauteur_EPI_AP_Parcours/2);
						$pdf->SetXY($param_bull2016["x_EPI_AP_Parcours"], $Y_decal);
						$pdf->SetFont('DejaVu','',8);

						//fich_debug_bull("\$info_nom_matiere=$info_nom_matiere\n");
						//fich_debug_bull("Le nom de matière est écrit; on est à mi-hauteur de la cellule pour écrire le nom du prof:\n");
						//fich_debug_bull("\$Y_decal=$Y_decal\n");

						// nom des professeurs

						if ( isset($tab_bull['groupe'][$m]["profs"]["list"]) )
						{

							// Présentation en ligne des profs
							// On n'a pas forcément le formatage choisi pour la classe...
							//$text_prof=$tab_bull['groupe'][$m]["profs"]["proflist_string"]."  ";
							$text_prof="";
							for($loop_prof_grp=0;$loop_prof_grp<count($tab_bull['groupe'][$m]["profs"]["list"]);$loop_prof_grp++) {
								$tmp_login_prof=$tab_bull['groupe'][$m]["profs"]["list"][$loop_prof_grp];
								if($loop_prof_grp>0) {$text_prof.=", ";}
								$text_prof.=affiche_utilisateur($tmp_login_prof,$tab_bull['eleve'][$i]['id_classe']);
							}

							if($text_prof!="") {
								//$espace_matiere_prof = $espace_entre_matier/2;
								$espace_matiere_prof = $hauteur_EPI_AP_Parcours/2;
								$hauteur_caractere_prof = 7;

								/*
								if($use_cell_ajustee=="n") {
									$pdf->SetFont('DejaVu','',$hauteur_caractere_prof);
									$val = $pdf->GetStringWidth($text_prof);
									$taille_texte = ($tab_modele_pdf["largeur_matiere"][$classe_id]);
									$grandeur_texte='test';
									while($grandeur_texte!='ok') {
										if($taille_texte<$val)
										{
											$hauteur_caractere_prof = $hauteur_caractere_prof-0.3;
											$pdf->SetFont('DejaVu','',$hauteur_caractere_prof);
											$val = $pdf->GetStringWidth($text_prof);
										}
										else {
											$grandeur_texte='ok';
										}
									}
									$grandeur_texte='test';
									$pdf->SetX($X_bloc_matiere);
									$pdf->Cell($tab_modele_pdf["largeur_matiere"][$classe_id], $espace_matiere_prof, ($text_prof),'LR',1,'L');
								}
								else {
								*/
									$texte=$text_prof;
									$taille_max_police=$hauteur_caractere_prof;
									$taille_min_police=ceil($hauteur_caractere_prof/3);

									$largeur_dispo=$param_bull2016["largeur_EPI_AP_Parcours_col_1"];
									$h_cell=$espace_matiere_prof;

									$pdf->SetX($param_bull2016["x_EPI_AP_Parcours"]);

									cell_ajustee(($texte),$pdf->GetX(),$pdf->GetY(),$largeur_dispo,$h_cell,$taille_max_police,$taille_min_police,'');
								//}
							}
						}


						// Colonne 2 : Appréciation
						$pdf->SetFillColor($param_bull2016["couleur_EPI_alt".($cpt_matiere%2+1)]["R"], $param_bull2016["couleur_EPI_alt".($cpt_matiere%2+1)]["V"], $param_bull2016["couleur_EPI_alt".($cpt_matiere%2+1)]["B"]);
						$pdf->Rect($param_bull2016["x_EPI_AP_Parcours_col_2"], $y_courant, $param_bull2016["largeur_EPI_AP_Parcours_col_2"], $hauteur_EPI_AP_Parcours, 'F');

						$pdf->SetFillColor(0, 0, 0);
						$pdf->SetXY($param_bull2016["x_EPI_AP_Parcours_col_2"], $y_courant);

						// calcul de la taille du texte des appréciations
						$hauteur_caractere_appreciation = 8;
						$pdf->SetFont('DejaVu','',$hauteur_caractere_appreciation);

						//suppression des espaces en début et en fin
						$app_aff = trim($tab_bull['app'][$m][$i]);

						fich_debug_bull("__________________________________________\n");
						fich_debug_bull("$app_aff\n");
						fich_debug_bull("__________________________________________\n");

						// DEBUT AJUSTEMENT TAILLE APPRECIATION
						$taille_texte_total = $pdf->GetStringWidth($app_aff);
						//$largeur_appreciation2 = $largeur_appreciation - $largeur_sous_matiere;
						$largeur_appreciation2=$param_bull2016["largeur_EPI_AP_Parcours_col_2"];

						if($use_cell_ajustee=="n") {
							//$taille_texte = (($espace_entre_matier/3)*$largeur_appreciation2);
							$nb_ligne_app = '2.8';
							//$nb_ligne_app = '3.8';
							//$nb_ligne_app = '4.8';
							$taille_texte_max = $nb_ligne_app * ($largeur_appreciation2-4);
							//$taille_texte_max = $nb_ligne_app * ($largeur_appreciation2);
							$grandeur_texte='test';

							fich_debug_bull("\$taille_texte_total=$taille_texte_total\n");
							fich_debug_bull("\$largeur_appreciation2=$largeur_appreciation2\n");
							fich_debug_bull("\$nb_ligne_app=$nb_ligne_app\n");
							//fich_debug_bull("\$taille_texte_max = \$nb_ligne_app * (\$largeur_appreciation2-4)=$nb_ligne_app * ($largeur_appreciation2-4)=$taille_texte_max\n");
							fich_debug_bull("\$taille_texte_max = \$nb_ligne_app * (\$largeur_appreciation2)=$nb_ligne_app * ($largeur_appreciation2)=$taille_texte_max\n");

							while($grandeur_texte!='ok') {
								if($taille_texte_max < $taille_texte_total)
								{
									$hauteur_caractere_appreciation = $hauteur_caractere_appreciation-0.3;
									//$hauteur_caractere_appreciation = $hauteur_caractere_appreciation-0.1;
									$pdf->SetFont('DejaVu','',$hauteur_caractere_appreciation);
									$taille_texte_total = $pdf->GetStringWidth($app_aff);
								}
								else {
									$grandeur_texte='ok';
								}
							}
							$grandeur_texte='test';
							$pdf->drawTextBox(($app_aff), $largeur_appreciation2, $hauteur_EPI_AP_Parcours, 'J', 'M', 1);
						}
						else {
							$texte=$app_aff;
							//$texte="Bla bla\nbli ".$app_aff;
							$taille_max_police=$hauteur_caractere_appreciation;
							$taille_min_police=ceil($taille_max_police/3);

							$largeur_dispo=$largeur_appreciation2;
							$h_cell=$hauteur_EPI_AP_Parcours;

							if(getSettingValue('suppr_balises_app_prof')=='y') {$texte=preg_replace('/<(.*)>/U','',$texte);}
							cell_ajustee(($texte),$pdf->GetX(),$pdf->GetY(),$largeur_dispo,$h_cell,$taille_max_police,$taille_min_police,'');
						}

						$y_courant+=$hauteur_EPI_AP_Parcours+0.5;
						$cpt_matiere++;

					}
				}
				// Fin des enseignements de la table 'groupes' tagués EPI


				// AID_e de type EPI
				for($loop=0;$loop<count($indice_AID_e_EPI);$loop++) {
					$m=$indice_AID_e_EPI[$loop];

					// Colonne 1 : Matière, prof

					$pdf->SetFillColor($param_bull2016["couleur_EPI_alt".($cpt_matiere%2+1)]["R"], $param_bull2016["couleur_EPI_alt".($cpt_matiere%2+1)]["V"], $param_bull2016["couleur_EPI_alt".($cpt_matiere%2+1)]["B"]);
					$pdf->Rect($param_bull2016["x_EPI_AP_Parcours"], $y_courant, $param_bull2016["largeur_EPI_AP_Parcours_col_1"], $hauteur_EPI_AP_Parcours, 'F');
					$pdf->SetFillColor(0, 0, 0);

					$pdf->SetTextColor(0, 0, 0);
					$pdf->SetXY($param_bull2016["x_EPI_AP_Parcours"], $y_courant);
					$pdf->SetFont('DejaVu','',8);

					if($tab_bull['eleve'][$i]['aid_e'][$m]['display_nom']=='z') {
						if((isset($tab_bull['eleve'][$i]['aid_e'][$m]['aid_nom']))&&($tab_bull['eleve'][$i]['aid_e'][$m]['aid_nom']!="")) {
							$info_nom_matiere=$tab_bull['eleve'][$i]['aid_e'][$m]['aid_nom'];
						}
						elseif((isset($tab_bull['eleve'][$i]['aid_e'][$m]['nom_complet']))&&($tab_bull['eleve'][$i]['aid_e'][$m]['nom_complet']!="")) {
							$info_nom_matiere=$tab_bull['eleve'][$i]['aid_e'][$m]['nom_complet'];
						}
						else {
							$info_nom_matiere=$tab_bull['eleve'][$i]['aid_e'][$m]['nom'];
						}
					}
					elseif($tab_bull['eleve'][$i]['aid_e'][$m]['display_nom']=='x') {
						if((isset($tab_bull['eleve'][$i]['aid_e'][$m]['nom']))&&($tab_bull['eleve'][$i]['aid_e'][$m]['nom']!="")) {
							$info_nom_matiere=$tab_bull['eleve'][$i]['aid_e'][$m]['nom'];
						}
						else {
							$info_nom_matiere=$tab_bull['eleve'][$i]['aid_e'][$m]['nom_complet'];
						}

						if((isset($tab_bull['eleve'][$i]['aid_e'][$m]['aid_nom']))&&($tab_bull['eleve'][$i]['aid_e'][$m]['aid_nom']!="")) {
							if($info_nom_matiere!="") {
								$info_nom_matiere.=": ";
							}
							$info_nom_matiere.=$tab_bull['eleve'][$i]['aid_e'][$m]['aid_nom'];
						}
					}
					else {
						if((isset($tab_bull['eleve'][$i]['aid_e'][$m]['nom_complet']))&&($tab_bull['eleve'][$i]['aid_e'][$m]['nom_complet']!="")) {
							$info_nom_matiere=$tab_bull['eleve'][$i]['aid_e'][$m]['nom_complet'];
						}
						else {
							$info_nom_matiere=$tab_bull['eleve'][$i]['aid_e'][$m]['nom'];
						}
					}
					if($info_nom_matiere=="") {
						$info_nom_matiere="AID";
					}

					$hauteur_caractere_matiere=8;
					$cell_ajustee_texte_matiere_ratio_min_max=3;

					// Forcé pour le moment
					$cell_ajustee_texte_matiere=1;
					if((isset($cell_ajustee_texte_matiere))&&($cell_ajustee_texte_matiere==1)) {
						// On met un décalage pour ne pas coller le texte à la bordure
						$Y_decal_cell_ajustee=2;
						$pdf->SetXY($param_bull2016["x_EPI_AP_Parcours"], $y_courant+$Y_decal_cell_ajustee);

						$texte=$info_nom_matiere;
						$taille_max_police=$hauteur_caractere_matiere;
						$taille_min_police=ceil($taille_max_police/$cell_ajustee_texte_matiere_ratio_min_max);

						$largeur_dispo=$param_bull2016["largeur_EPI_AP_Parcours_col_1"];
						$h_cell=$hauteur_EPI_AP_Parcours/2-$Y_decal_cell_ajustee;

						cell_ajustee($texte,$pdf->GetX(),$pdf->GetY(),$largeur_dispo,$h_cell,$taille_max_police,$taille_min_police,'');
			
					}
					else {
						$val = $pdf->GetStringWidth($info_nom_matiere);
						$taille_texte = $param_bull2016["largeur_EPI_AP_Parcours_col_1"]-2;
						$grandeur_texte='test';
						while($grandeur_texte!='ok') {
							if($taille_texte<$val)
							{
								$hauteur_caractere_matiere = $hauteur_caractere_matiere-0.3;
								$pdf->SetFont('DejaVu','B',$hauteur_caractere_matiere);
								$val = $pdf->GetStringWidth($info_nom_matiere);
							}
							else {
								$grandeur_texte='ok';
							}
						}
						$grandeur_texte='test';
						$Y_decal=$y_courant;
						$pdf->SetXY($param_bull2016["x_EPI_AP_Parcours"], $Y_decal);
						$pdf->Cell($param_bull2016["largeur_EPI_AP_Parcours_col_1"], $hauteur_EPI_AP_Parcours/2, ($info_nom_matiere),'',1,'L');
					}


					// On note l'ordonnée pour le nom des professeurs
					$Y_decal = $y_courant+($hauteur_EPI_AP_Parcours/2);
					$pdf->SetXY($param_bull2016["x_EPI_AP_Parcours"], $Y_decal);
					$pdf->SetFont('DejaVu','',8);

					// nom des professeurs
					if ( isset($tab_bull['eleve'][$i]['aid_e'][$m]['aid_prof_resp_login'][0]) )
					{

						// Présentation en ligne des profs
						// On n'a pas forcément le formatage choisi pour la classe...
						//$text_prof=$tab_bull['groupe'][$m]["profs"]["proflist_string"]."  ";
						$text_prof="";
						for($loop_prof_grp=0;$loop_prof_grp<count($tab_bull['eleve'][$i]['aid_e'][$m]['aid_prof_resp_login']);$loop_prof_grp++) {
							$tmp_login_prof=$tab_bull['eleve'][$i]['aid_e'][$m]['aid_prof_resp_login'][$loop_prof_grp];
							if($loop_prof_grp>0) {$text_prof.=", ";}
							$text_prof.=affiche_utilisateur($tmp_login_prof,$tab_bull['eleve'][$i]['id_classe']);
						}

						if($text_prof!="") {
							//$espace_matiere_prof = $espace_entre_matier/2;
							$espace_matiere_prof = $hauteur_EPI_AP_Parcours/2;
							$hauteur_caractere_prof = 7;

							$texte=$text_prof;
							$taille_max_police=$hauteur_caractere_prof;
							$taille_min_police=ceil($hauteur_caractere_prof/3);

							$largeur_dispo=$param_bull2016["largeur_EPI_AP_Parcours_col_1"];
							$h_cell=$espace_matiere_prof;

							$pdf->SetX($param_bull2016["x_EPI_AP_Parcours"]);

							cell_ajustee(($texte),$pdf->GetX(),$pdf->GetY(),$largeur_dispo,$h_cell,$taille_max_police,$taille_min_police,'');
						}
					}


					// Colonne 2 : Appréciation
					$pdf->SetFillColor($param_bull2016["couleur_EPI_alt".($cpt_matiere%2+1)]["R"], $param_bull2016["couleur_EPI_alt".($cpt_matiere%2+1)]["V"], $param_bull2016["couleur_EPI_alt".($cpt_matiere%2+1)]["B"]);
					$pdf->Rect($param_bull2016["x_EPI_AP_Parcours_col_2"], $y_courant, $param_bull2016["largeur_EPI_AP_Parcours_col_2"], $hauteur_EPI_AP_Parcours, 'F');

					$pdf->SetFillColor(0, 0, 0);
					$pdf->SetXY($param_bull2016["x_EPI_AP_Parcours_col_2"], $y_courant);

					// calcul de la taille du texte des appréciations
					$hauteur_caractere_appreciation = 8;
					$pdf->SetFont('DejaVu','',$hauteur_caractere_appreciation);

					//suppression des espaces en début et en fin
					//$app_aff = trim($tab_bull['app'][$m][$i]);
					$app_aff="";
					if($tab_bull['eleve'][$i]['aid_e'][$m]['message']!='') {
						$app_aff.=$tab_bull['eleve'][$i]['aid_e'][$m]['message'];
					}
					//if($app_aff!='') {$app_aff.=" ";}
					if(($tab_bull['eleve'][$i]['aid_e'][$m]['display_nom']=='y')&&($tab_bull['eleve'][$i]['aid_e'][$m]['aid_nom']!='')) {
						if($app_aff!='') {$app_aff.=" ";}
						$app_aff.=$tab_bull['eleve'][$i]['aid_e'][$m]['aid_nom'];
					}
					if($app_aff!='') {$app_aff.="\n";}
					$app_aff.=trim($tab_bull['eleve'][$i]['aid_e'][$m]['aid_appreciation']);

					fich_debug_bull("__________________________________________\n");
					fich_debug_bull("$app_aff\n");
					fich_debug_bull("__________________________________________\n");

					// DEBUT AJUSTEMENT TAILLE APPRECIATION
					$taille_texte_total = $pdf->GetStringWidth($app_aff);
					//$largeur_appreciation2 = $largeur_appreciation - $largeur_sous_matiere;
					$largeur_appreciation2=$param_bull2016["largeur_EPI_AP_Parcours_col_2"];

					if($use_cell_ajustee=="n") {
						//$taille_texte = (($espace_entre_matier/3)*$largeur_appreciation2);
						$nb_ligne_app = '2.8';
						//$nb_ligne_app = '3.8';
						//$nb_ligne_app = '4.8';
						$taille_texte_max = $nb_ligne_app * ($largeur_appreciation2-4);
						//$taille_texte_max = $nb_ligne_app * ($largeur_appreciation2);
						$grandeur_texte='test';

						fich_debug_bull("\$taille_texte_total=$taille_texte_total\n");
						fich_debug_bull("\$largeur_appreciation2=$largeur_appreciation2\n");
						fich_debug_bull("\$nb_ligne_app=$nb_ligne_app\n");
						//fich_debug_bull("\$taille_texte_max = \$nb_ligne_app * (\$largeur_appreciation2-4)=$nb_ligne_app * ($largeur_appreciation2-4)=$taille_texte_max\n");
						fich_debug_bull("\$taille_texte_max = \$nb_ligne_app * (\$largeur_appreciation2)=$nb_ligne_app * ($largeur_appreciation2)=$taille_texte_max\n");

						while($grandeur_texte!='ok') {
							if($taille_texte_max < $taille_texte_total)
							{
								$hauteur_caractere_appreciation = $hauteur_caractere_appreciation-0.3;
								//$hauteur_caractere_appreciation = $hauteur_caractere_appreciation-0.1;
								$pdf->SetFont('DejaVu','',$hauteur_caractere_appreciation);
								$taille_texte_total = $pdf->GetStringWidth($app_aff);
							}
							else {
								$grandeur_texte='ok';
							}
						}
						$grandeur_texte='test';
						$pdf->drawTextBox(($app_aff), $largeur_appreciation2, $hauteur_EPI_AP_Parcours, 'J', 'M', 1);
					}
					else {
						$texte=$app_aff;
						//$texte="Bla bla\nbli ".$app_aff;
						$taille_max_police=$hauteur_caractere_appreciation;
						$taille_min_police=ceil($taille_max_police/3);

						$largeur_dispo=$largeur_appreciation2;
						$h_cell=$hauteur_EPI_AP_Parcours;

						if(getSettingValue('suppr_balises_app_prof')=='y') {$texte=preg_replace('/<(.*)>/U','',$texte);}
						cell_ajustee(($texte),$pdf->GetX(),$pdf->GetY(),$largeur_dispo,$h_cell,$taille_max_police,$taille_min_police,'');
					}

					$y_courant+=$hauteur_EPI_AP_Parcours+0.5;
					$cpt_matiere++;


				}
				// Fin des AID_e tagués EPI

				$y_courant+=$param_bull2016["espace_vertical_entre_sections_EPI_AP_Parcours"];
			}

			//=========================================

			// AP

			if($nb_AP>0) {

				$pdf->SetFillColor($param_bull2016["couleur_bandeau_EPI"]["R"], $param_bull2016["couleur_bandeau_EPI"]["V"], $param_bull2016["couleur_bandeau_EPI"]["B"]);
				$pdf->Rect($param_bull2016["x_EPI_AP_Parcours"], $y_courant, $param_bull2016["largeur_EPI_AP_Parcours"], $param_bull2016["hauteur_ligne_titre_EPI_AP_Parcours"], 'F');

				$pdf->SetFillColor(0, 0, 0);
				//$pdf->SetTextColor(255, 255, 255);
				$pdf->SetTextColor(0, 0, 0);
				$pdf->SetXY($param_bull2016["x_EPI_AP_Parcours"], $y_courant);
				$pdf->SetFont('DejaVu','',8);
				$pdf->Cell($param_bull2016["largeur_EPI_AP_Parcours"],7, "Accompagnement personnalisé : actions réalisées et implication de l'élève",0,2,'L');

				$cpt_matiere=0;
				$y_courant+=$param_bull2016["hauteur_ligne_titre_EPI_AP_Parcours"]+0.5;


				// AID_b de type AP
				for($loop=0;$loop<count($indice_AID_b_AP);$loop++) {
					$m=$indice_AID_b_AP[$loop];

					// Colonne 1 : Matière, prof

					$pdf->SetFillColor($param_bull2016["couleur_EPI_alt".($cpt_matiere%2+1)]["R"], $param_bull2016["couleur_EPI_alt".($cpt_matiere%2+1)]["V"], $param_bull2016["couleur_EPI_alt".($cpt_matiere%2+1)]["B"]);
					$pdf->Rect($param_bull2016["x_EPI_AP_Parcours"], $y_courant, $param_bull2016["largeur_EPI_AP_Parcours_col_1"], $hauteur_EPI_AP_Parcours, 'F');
					$pdf->SetFillColor(0, 0, 0);

					$pdf->SetTextColor(0, 0, 0);
					$pdf->SetXY($param_bull2016["x_EPI_AP_Parcours"], $y_courant);
					$pdf->SetFont('DejaVu','',8);

					if($tab_bull['eleve'][$i]['aid_b'][$m]['display_nom']=='z') {
						if((isset($tab_bull['eleve'][$i]['aid_b'][$m]['aid_nom']))&&($tab_bull['eleve'][$i]['aid_b'][$m]['aid_nom']!="")) {
							$info_nom_matiere=$tab_bull['eleve'][$i]['aid_b'][$m]['aid_nom'];
						}
						elseif((isset($tab_bull['eleve'][$i]['aid_b'][$m]['nom_complet']))&&($tab_bull['eleve'][$i]['aid_b'][$m]['nom_complet']!="")) {
							$info_nom_matiere=$tab_bull['eleve'][$i]['aid_b'][$m]['nom_complet'];
						}
						else {
							$info_nom_matiere=$tab_bull['eleve'][$i]['aid_b'][$m]['nom'];
						}
					}
					elseif($tab_bull['eleve'][$i]['aid_b'][$m]['display_nom']=='x') {
						if((isset($tab_bull['eleve'][$i]['aid_b'][$m]['nom']))&&($tab_bull['eleve'][$i]['aid_b'][$m]['nom']!="")) {
							$info_nom_matiere=$tab_bull['eleve'][$i]['aid_b'][$m]['nom'];
						}
						else {
							$info_nom_matiere=$tab_bull['eleve'][$i]['aid_b'][$m]['nom_complet'];
						}

						if((isset($tab_bull['eleve'][$i]['aid_b'][$m]['aid_nom']))&&($tab_bull['eleve'][$i]['aid_b'][$m]['aid_nom']!="")) {
							if($info_nom_matiere!="") {
								$info_nom_matiere.=": ";
							}
							$info_nom_matiere.=$tab_bull['eleve'][$i]['aid_b'][$m]['aid_nom'];
						}
					}
					else {
						if((isset($tab_bull['eleve'][$i]['aid_b'][$m]['nom_complet']))&&($tab_bull['eleve'][$i]['aid_b'][$m]['nom_complet']!="")) {
							$info_nom_matiere=$tab_bull['eleve'][$i]['aid_b'][$m]['nom_complet'];
						}
						else {
							$info_nom_matiere=$tab_bull['eleve'][$i]['aid_b'][$m]['nom'];
						}
					}
					if($info_nom_matiere=="") {
						$info_nom_matiere="AID";
					}

					$hauteur_caractere_matiere=8;
					$cell_ajustee_texte_matiere_ratio_min_max=3;

					// Forcé pour le moment
					$cell_ajustee_texte_matiere=1;
					if((isset($cell_ajustee_texte_matiere))&&($cell_ajustee_texte_matiere==1)) {
						// On met un décalage pour ne pas coller le texte à la bordure
						$Y_decal_cell_ajustee=2;
						$pdf->SetXY($param_bull2016["x_EPI_AP_Parcours"], $y_courant+$Y_decal_cell_ajustee);

						$texte=$info_nom_matiere;
						$taille_max_police=$hauteur_caractere_matiere;
						$taille_min_police=ceil($taille_max_police/$cell_ajustee_texte_matiere_ratio_min_max);

						$largeur_dispo=$param_bull2016["largeur_EPI_AP_Parcours_col_1"];
						$h_cell=$hauteur_EPI_AP_Parcours/2-$Y_decal_cell_ajustee;

						cell_ajustee($texte,$pdf->GetX(),$pdf->GetY(),$largeur_dispo,$h_cell,$taille_max_police,$taille_min_police,'');
			
					}
					else {
						$val = $pdf->GetStringWidth($info_nom_matiere);
						$taille_texte = $param_bull2016["largeur_EPI_AP_Parcours_col_1"]-2;
						$grandeur_texte='test';
						while($grandeur_texte!='ok') {
							if($taille_texte<$val)
							{
								$hauteur_caractere_matiere = $hauteur_caractere_matiere-0.3;
								$pdf->SetFont('DejaVu','B',$hauteur_caractere_matiere);
								$val = $pdf->GetStringWidth($info_nom_matiere);
							}
							else {
								$grandeur_texte='ok';
							}
						}
						$grandeur_texte='test';
						$Y_decal=$y_courant;
						$pdf->SetXY($param_bull2016["x_EPI_AP_Parcours"], $Y_decal);
						$pdf->Cell($param_bull2016["largeur_EPI_AP_Parcours_col_1"], $hauteur_EPI_AP_Parcours/2, ($info_nom_matiere),'',1,'L');
					}


					// On note l'ordonnée pour le nom des professeurs
					$Y_decal = $y_courant+($hauteur_EPI_AP_Parcours/2);
					$pdf->SetXY($param_bull2016["x_EPI_AP_Parcours"], $Y_decal);
					$pdf->SetFont('DejaVu','',8);

					// nom des professeurs
					if ( isset($tab_bull['eleve'][$i]['aid_b'][$m]['aid_prof_resp_login'][0]) )
					{

						// Présentation en ligne des profs
						// On n'a pas forcément le formatage choisi pour la classe...
						//$text_prof=$tab_bull['groupe'][$m]["profs"]["proflist_string"]."  ";
						$text_prof="";
						for($loop_prof_grp=0;$loop_prof_grp<count($tab_bull['eleve'][$i]['aid_b'][$m]['aid_prof_resp_login']);$loop_prof_grp++) {
							$tmp_login_prof=$tab_bull['eleve'][$i]['aid_b'][$m]['aid_prof_resp_login'][$loop_prof_grp];
							if($loop_prof_grp>0) {$text_prof.=", ";}
							$text_prof.=affiche_utilisateur($tmp_login_prof,$tab_bull['eleve'][$i]['id_classe']);
						}

						if($text_prof!="") {
							//$espace_matiere_prof = $espace_entre_matier/2;
							$espace_matiere_prof = $hauteur_EPI_AP_Parcours/2;
							$hauteur_caractere_prof = 7;

							$texte=$text_prof;
							$taille_max_police=$hauteur_caractere_prof;
							$taille_min_police=ceil($hauteur_caractere_prof/3);

							$largeur_dispo=$param_bull2016["largeur_EPI_AP_Parcours_col_1"];
							$h_cell=$espace_matiere_prof;

							$pdf->SetX($param_bull2016["x_EPI_AP_Parcours"]);

							cell_ajustee(($texte),$pdf->GetX(),$pdf->GetY(),$largeur_dispo,$h_cell,$taille_max_police,$taille_min_police,'');
						}
					}


					// Colonne 2 : Appréciation
					$pdf->SetFillColor($param_bull2016["couleur_EPI_alt".($cpt_matiere%2+1)]["R"], $param_bull2016["couleur_EPI_alt".($cpt_matiere%2+1)]["V"], $param_bull2016["couleur_EPI_alt".($cpt_matiere%2+1)]["B"]);
					$pdf->Rect($param_bull2016["x_EPI_AP_Parcours_col_2"], $y_courant, $param_bull2016["largeur_EPI_AP_Parcours_col_2"], $hauteur_EPI_AP_Parcours, 'F');

					$pdf->SetFillColor(0, 0, 0);
					$pdf->SetXY($param_bull2016["x_EPI_AP_Parcours_col_2"], $y_courant);

					// calcul de la taille du texte des appréciations
					$hauteur_caractere_appreciation = 8;
					$pdf->SetFont('DejaVu','',$hauteur_caractere_appreciation);

					//suppression des espaces en début et en fin
					//$app_aff = trim($tab_bull['app'][$m][$i]);
					$app_aff="";
					if($tab_bull['eleve'][$i]['aid_b'][$m]['message']!='') {
						$app_aff.=$tab_bull['eleve'][$i]['aid_b'][$m]['message'];
					}
					//if($app_aff!='') {$app_aff.=" ";}
					if(($tab_bull['eleve'][$i]['aid_b'][$m]['display_nom']=='y')&&($tab_bull['eleve'][$i]['aid_b'][$m]['aid_nom']!='')) {
						if($app_aff!='') {$app_aff.=" ";}
						$app_aff.=$tab_bull['eleve'][$i]['aid_b'][$m]['aid_nom'];
					}
					if($app_aff!='') {$app_aff.="\n";}
					$app_aff.=trim($tab_bull['eleve'][$i]['aid_b'][$m]['aid_appreciation']);

					fich_debug_bull("__________________________________________\n");
					fich_debug_bull("$app_aff\n");
					fich_debug_bull("__________________________________________\n");

					// DEBUT AJUSTEMENT TAILLE APPRECIATION
					$taille_texte_total = $pdf->GetStringWidth($app_aff);
					//$largeur_appreciation2 = $largeur_appreciation - $largeur_sous_matiere;
					$largeur_appreciation2=$param_bull2016["largeur_EPI_AP_Parcours_col_2"];

					if($use_cell_ajustee=="n") {
						//$taille_texte = (($espace_entre_matier/3)*$largeur_appreciation2);
						$nb_ligne_app = '2.8';
						//$nb_ligne_app = '3.8';
						//$nb_ligne_app = '4.8';
						$taille_texte_max = $nb_ligne_app * ($largeur_appreciation2-4);
						//$taille_texte_max = $nb_ligne_app * ($largeur_appreciation2);
						$grandeur_texte='test';

						fich_debug_bull("\$taille_texte_total=$taille_texte_total\n");
						fich_debug_bull("\$largeur_appreciation2=$largeur_appreciation2\n");
						fich_debug_bull("\$nb_ligne_app=$nb_ligne_app\n");
						//fich_debug_bull("\$taille_texte_max = \$nb_ligne_app * (\$largeur_appreciation2-4)=$nb_ligne_app * ($largeur_appreciation2-4)=$taille_texte_max\n");
						fich_debug_bull("\$taille_texte_max = \$nb_ligne_app * (\$largeur_appreciation2)=$nb_ligne_app * ($largeur_appreciation2)=$taille_texte_max\n");

						while($grandeur_texte!='ok') {
							if($taille_texte_max < $taille_texte_total)
							{
								$hauteur_caractere_appreciation = $hauteur_caractere_appreciation-0.3;
								//$hauteur_caractere_appreciation = $hauteur_caractere_appreciation-0.1;
								$pdf->SetFont('DejaVu','',$hauteur_caractere_appreciation);
								$taille_texte_total = $pdf->GetStringWidth($app_aff);
							}
							else {
								$grandeur_texte='ok';
							}
						}
						$grandeur_texte='test';
						$pdf->drawTextBox(($app_aff), $largeur_appreciation2, $hauteur_EPI_AP_Parcours, 'J', 'M', 1);
					}
					else {
						$texte=$app_aff;
						//$texte="Bla bla\nbli ".$app_aff;
						$taille_max_police=$hauteur_caractere_appreciation;
						$taille_min_police=ceil($taille_max_police/3);

						$largeur_dispo=$largeur_appreciation2;
						$h_cell=$hauteur_EPI_AP_Parcours;

						if(getSettingValue('suppr_balises_app_prof')=='y') {$texte=preg_replace('/<(.*)>/U','',$texte);}
						cell_ajustee(($texte),$pdf->GetX(),$pdf->GetY(),$largeur_dispo,$h_cell,$taille_max_police,$taille_min_police,'');
					}

					$y_courant+=$hauteur_EPI_AP_Parcours+0.5;
					$cpt_matiere++;


				}
				// Fin des AID_b tagués AP


				// Enseignements de la table 'groupes' tagués AP
				for($m=0;$m<count($tab_bull['groupe']);$m++) {
					//if(isset($tab_bull['note'][$m][$i])) {
					// On n'affiche pas ici les groupes correspondant à AP, EPI ou Parcours
					if((isset($tab_bull['note'][$m][$i]))&&
					(isset($tab_bull['groupe'][$m]['type_grp'][0]))&&
					($tab_bull['groupe'][$m]['type_grp'][0]['nom_court']=="AP")) {

						// Colonne 1 : Matière, prof

						$pdf->SetFillColor($param_bull2016["couleur_EPI_alt".($cpt_matiere%2+1)]["R"], $param_bull2016["couleur_EPI_alt".($cpt_matiere%2+1)]["V"], $param_bull2016["couleur_EPI_alt".($cpt_matiere%2+1)]["B"]);
						$pdf->Rect($param_bull2016["x_EPI_AP_Parcours"], $y_courant, $param_bull2016["largeur_EPI_AP_Parcours_col_1"], $hauteur_EPI_AP_Parcours, 'F');
						$pdf->SetFillColor(0, 0, 0);

						$pdf->SetTextColor(0, 0, 0);
						$pdf->SetXY($param_bull2016["x_EPI_AP_Parcours"], $y_courant);
						$pdf->SetFont('DejaVu','',8);

						if(getSettingValue('bul_rel_nom_matieres')=='nom_groupe') {
							$info_nom_matiere=$tab_bull['groupe'][$m]['name'];
						}
						elseif(getSettingValue('bul_rel_nom_matieres')=='description_groupe') {
							$info_nom_matiere=$tab_bull['groupe'][$m]['description'];
						}
						else {
							// Pour parer au bug sur la suppression de matière alors que des groupes sont conservés:
							if(isset($tab_bull['groupe'][$m]['matiere']['nom_complet'])) {
								$info_nom_matiere=$tab_bull['groupe'][$m]['matiere']['nom_complet'];
							}
							else {
								$info_nom_matiere=$tab_bull['groupe'][$m]['name']." (".$tab_bull['groupe'][$m]['id'].")";
							}
						}

						$hauteur_caractere_matiere=8;
						$cell_ajustee_texte_matiere_ratio_min_max=3;

						// 20130927 : cell_ajustee() ou pas sur le nom de matière/enseignement
						$cell_ajustee_texte_matiere=1;
						if((isset($cell_ajustee_texte_matiere))&&($cell_ajustee_texte_matiere==1)) {
							// Encadrement
							//$pdf->Cell($tab_modele_pdf["largeur_matiere"][$classe_id], $espace_entre_matier, "",'LRBT',1,'L');

							// cell_ajustee() ne centre pas verticalement le texte.
							// On met un décalage pour ne pas coller le texte à la bordure
							$Y_decal_cell_ajustee=2;
							// On repositionne et on inscrit le nom de matière sur la moitié de la hauteur de la cellule
							//$pdf->SetXY($X_bloc_matiere, $Y_decal+$Y_decal_cell_ajustee);
							//$pdf->SetXY($param_bull2016["x_acquis_col_1"], $y_courant);

							$pdf->SetXY($param_bull2016["x_EPI_AP_Parcours"], $y_courant+$Y_decal_cell_ajustee);

							$texte=$info_nom_matiere;
							$taille_max_police=$hauteur_caractere_matiere;
							//$taille_min_police=ceil($taille_max_police/$tab_modele_pdf["cell_ajustee_texte_matiere_ratio_min_max"][$classe_id]);
							$taille_min_police=ceil($taille_max_police/$cell_ajustee_texte_matiere_ratio_min_max);

							$largeur_dispo=$param_bull2016["largeur_EPI_AP_Parcours_col_1"];
							//$h_cell=$espace_entre_matier/2-$Y_decal_cell_ajustee;
							$h_cell=$hauteur_EPI_AP_Parcours/2-$Y_decal_cell_ajustee;

							//cell_ajustee("<b>".$texte."</b>",$pdf->GetX(),$pdf->GetY(),$largeur_dispo,$h_cell,$taille_max_police,$taille_min_police,'');
							cell_ajustee($texte,$pdf->GetX(),$pdf->GetY(),$largeur_dispo,$h_cell,$taille_max_police,$taille_min_police,'');
			
						}
						else {
							$val = $pdf->GetStringWidth($info_nom_matiere);
							$taille_texte = $param_bull2016["largeur_EPI_AP_Parcours_col_1"]-2;
							$grandeur_texte='test';
							while($grandeur_texte!='ok') {
								if($taille_texte<$val)
								{
									$hauteur_caractere_matiere = $hauteur_caractere_matiere-0.3;
									$pdf->SetFont('DejaVu','',$hauteur_caractere_matiere);
									$val = $pdf->GetStringWidth($info_nom_matiere);
								}
								else {
									$grandeur_texte='ok';
								}
							}
							$grandeur_texte='test';
							$Y_decal=$y_courant;
							$pdf->SetXY($param_bull2016["x_EPI_AP_Parcours"], $Y_decal);
							$pdf->Cell($param_bull2016["largeur_EPI_AP_Parcours_col_1"], $hauteur_EPI_AP_Parcours/2, ($info_nom_matiere),'',1,'L');
						}
			

						// On note l'ordonnée pour le nom des professeurs
						$Y_decal = $y_courant+($hauteur_EPI_AP_Parcours/2);
						$pdf->SetXY($param_bull2016["x_EPI_AP_Parcours"], $Y_decal);
						$pdf->SetFont('DejaVu','',8);

						//fich_debug_bull("\$info_nom_matiere=$info_nom_matiere\n");
						//fich_debug_bull("Le nom de matière est écrit; on est à mi-hauteur de la cellule pour écrire le nom du prof:\n");
						//fich_debug_bull("\$Y_decal=$Y_decal\n");

						// nom des professeurs

						if ( isset($tab_bull['groupe'][$m]["profs"]["list"]) )
						{

							// Présentation en ligne des profs
							// On n'a pas forcément le formatage choisi pour la classe...
							//$text_prof=$tab_bull['groupe'][$m]["profs"]["proflist_string"]."  ";
							$text_prof="";
							for($loop_prof_grp=0;$loop_prof_grp<count($tab_bull['groupe'][$m]["profs"]["list"]);$loop_prof_grp++) {
								$tmp_login_prof=$tab_bull['groupe'][$m]["profs"]["list"][$loop_prof_grp];
								if($loop_prof_grp>0) {$text_prof.=", ";}
								$text_prof.=affiche_utilisateur($tmp_login_prof,$tab_bull['eleve'][$i]['id_classe']);
							}

							if($text_prof!="") {
								//$espace_matiere_prof = $espace_entre_matier/2;
								$espace_matiere_prof = $hauteur_EPI_AP_Parcours/2;
								$hauteur_caractere_prof = 7;

								/*
								if($use_cell_ajustee=="n") {
									$pdf->SetFont('DejaVu','',$hauteur_caractere_prof);
									$val = $pdf->GetStringWidth($text_prof);
									$taille_texte = ($tab_modele_pdf["largeur_matiere"][$classe_id]);
									$grandeur_texte='test';
									while($grandeur_texte!='ok') {
										if($taille_texte<$val)
										{
											$hauteur_caractere_prof = $hauteur_caractere_prof-0.3;
											$pdf->SetFont('DejaVu','',$hauteur_caractere_prof);
											$val = $pdf->GetStringWidth($text_prof);
										}
										else {
											$grandeur_texte='ok';
										}
									}
									$grandeur_texte='test';
									$pdf->SetX($X_bloc_matiere);
									$pdf->Cell($tab_modele_pdf["largeur_matiere"][$classe_id], $espace_matiere_prof, ($text_prof),'LR',1,'L');
								}
								else {
								*/
									$texte=$text_prof;
									$taille_max_police=$hauteur_caractere_prof;
									$taille_min_police=ceil($hauteur_caractere_prof/3);

									$largeur_dispo=$param_bull2016["largeur_EPI_AP_Parcours_col_1"];
									$h_cell=$espace_matiere_prof;

									$pdf->SetX($param_bull2016["x_EPI_AP_Parcours"]);

									cell_ajustee(($texte),$pdf->GetX(),$pdf->GetY(),$largeur_dispo,$h_cell,$taille_max_police,$taille_min_police,'');
								//}
							}
						}


						// Colonne 2 : Appréciation
						$pdf->SetFillColor($param_bull2016["couleur_EPI_alt".($cpt_matiere%2+1)]["R"], $param_bull2016["couleur_EPI_alt".($cpt_matiere%2+1)]["V"], $param_bull2016["couleur_EPI_alt".($cpt_matiere%2+1)]["B"]);
						$pdf->Rect($param_bull2016["x_EPI_AP_Parcours_col_2"], $y_courant, $param_bull2016["largeur_EPI_AP_Parcours_col_2"], $hauteur_EPI_AP_Parcours, 'F');

						$pdf->SetFillColor(0, 0, 0);
						$pdf->SetXY($param_bull2016["x_EPI_AP_Parcours_col_2"], $y_courant);

						// calcul de la taille du texte des appréciations
						$hauteur_caractere_appreciation = 8;
						$pdf->SetFont('DejaVu','',$hauteur_caractere_appreciation);

						//suppression des espaces en début et en fin
						$app_aff = trim($tab_bull['app'][$m][$i]);

						fich_debug_bull("__________________________________________\n");
						fich_debug_bull("$app_aff\n");
						fich_debug_bull("__________________________________________\n");

						// DEBUT AJUSTEMENT TAILLE APPRECIATION
						$taille_texte_total = $pdf->GetStringWidth($app_aff);
						//$largeur_appreciation2 = $largeur_appreciation - $largeur_sous_matiere;
						$largeur_appreciation2=$param_bull2016["largeur_EPI_AP_Parcours_col_2"];

						if($use_cell_ajustee=="n") {
							//$taille_texte = (($espace_entre_matier/3)*$largeur_appreciation2);
							$nb_ligne_app = '2.8';
							//$nb_ligne_app = '3.8';
							//$nb_ligne_app = '4.8';
							$taille_texte_max = $nb_ligne_app * ($largeur_appreciation2-4);
							//$taille_texte_max = $nb_ligne_app * ($largeur_appreciation2);
							$grandeur_texte='test';

							fich_debug_bull("\$taille_texte_total=$taille_texte_total\n");
							fich_debug_bull("\$largeur_appreciation2=$largeur_appreciation2\n");
							fich_debug_bull("\$nb_ligne_app=$nb_ligne_app\n");
							//fich_debug_bull("\$taille_texte_max = \$nb_ligne_app * (\$largeur_appreciation2-4)=$nb_ligne_app * ($largeur_appreciation2-4)=$taille_texte_max\n");
							fich_debug_bull("\$taille_texte_max = \$nb_ligne_app * (\$largeur_appreciation2)=$nb_ligne_app * ($largeur_appreciation2)=$taille_texte_max\n");

							while($grandeur_texte!='ok') {
								if($taille_texte_max < $taille_texte_total)
								{
									$hauteur_caractere_appreciation = $hauteur_caractere_appreciation-0.3;
									//$hauteur_caractere_appreciation = $hauteur_caractere_appreciation-0.1;
									$pdf->SetFont('DejaVu','',$hauteur_caractere_appreciation);
									$taille_texte_total = $pdf->GetStringWidth($app_aff);
								}
								else {
									$grandeur_texte='ok';
								}
							}
							$grandeur_texte='test';
							$pdf->drawTextBox(($app_aff), $largeur_appreciation2, $hauteur_EPI_AP_Parcours, 'J', 'M', 1);
						}
						else {
							$texte=$app_aff;
							//$texte="Bla bla\nbli ".$app_aff;
							$taille_max_police=$hauteur_caractere_appreciation;
							$taille_min_police=ceil($taille_max_police/3);

							$largeur_dispo=$largeur_appreciation2;
							$h_cell=$hauteur_EPI_AP_Parcours;

							if(getSettingValue('suppr_balises_app_prof')=='y') {$texte=preg_replace('/<(.*)>/U','',$texte);}
							cell_ajustee(($texte),$pdf->GetX(),$pdf->GetY(),$largeur_dispo,$h_cell,$taille_max_police,$taille_min_police,'');
						}

						$y_courant+=$hauteur_EPI_AP_Parcours+0.5;
						$cpt_matiere++;

					}
				}
				// Fin des enseignements de la table 'groupes' tagués AP


				// AID_e de type AP
				for($loop=0;$loop<count($indice_AID_e_AP);$loop++) {
					$m=$indice_AID_e_AP[$loop];

					// Colonne 1 : Matière, prof

					$pdf->SetFillColor($param_bull2016["couleur_EPI_alt".($cpt_matiere%2+1)]["R"], $param_bull2016["couleur_EPI_alt".($cpt_matiere%2+1)]["V"], $param_bull2016["couleur_EPI_alt".($cpt_matiere%2+1)]["B"]);
					$pdf->Rect($param_bull2016["x_EPI_AP_Parcours"], $y_courant, $param_bull2016["largeur_EPI_AP_Parcours_col_1"], $hauteur_EPI_AP_Parcours, 'F');
					$pdf->SetFillColor(0, 0, 0);

					$pdf->SetTextColor(0, 0, 0);
					$pdf->SetXY($param_bull2016["x_EPI_AP_Parcours"], $y_courant);
					$pdf->SetFont('DejaVu','',8);

					if($tab_bull['eleve'][$i]['aid_e'][$m]['display_nom']=='z') {
						if((isset($tab_bull['eleve'][$i]['aid_e'][$m]['aid_nom']))&&($tab_bull['eleve'][$i]['aid_e'][$m]['aid_nom']!="")) {
							$info_nom_matiere=$tab_bull['eleve'][$i]['aid_e'][$m]['aid_nom'];
						}
						elseif((isset($tab_bull['eleve'][$i]['aid_e'][$m]['nom_complet']))&&($tab_bull['eleve'][$i]['aid_e'][$m]['nom_complet']!="")) {
							$info_nom_matiere=$tab_bull['eleve'][$i]['aid_e'][$m]['nom_complet'];
						}
						else {
							$info_nom_matiere=$tab_bull['eleve'][$i]['aid_e'][$m]['nom'];
						}
					}
					elseif($tab_bull['eleve'][$i]['aid_e'][$m]['display_nom']=='x') {
						if((isset($tab_bull['eleve'][$i]['aid_e'][$m]['nom']))&&($tab_bull['eleve'][$i]['aid_e'][$m]['nom']!="")) {
							$info_nom_matiere=$tab_bull['eleve'][$i]['aid_e'][$m]['nom'];
						}
						else {
							$info_nom_matiere=$tab_bull['eleve'][$i]['aid_e'][$m]['nom_complet'];
						}

						if((isset($tab_bull['eleve'][$i]['aid_e'][$m]['aid_nom']))&&($tab_bull['eleve'][$i]['aid_e'][$m]['aid_nom']!="")) {
							if($info_nom_matiere!="") {
								$info_nom_matiere.=": ";
							}
							$info_nom_matiere.=$tab_bull['eleve'][$i]['aid_e'][$m]['aid_nom'];
						}
					}
					else {
						if((isset($tab_bull['eleve'][$i]['aid_e'][$m]['nom_complet']))&&($tab_bull['eleve'][$i]['aid_e'][$m]['nom_complet']!="")) {
							$info_nom_matiere=$tab_bull['eleve'][$i]['aid_e'][$m]['nom_complet'];
						}
						else {
							$info_nom_matiere=$tab_bull['eleve'][$i]['aid_e'][$m]['nom'];
						}
					}
					if($info_nom_matiere=="") {
						$info_nom_matiere="AID";
					}

					$hauteur_caractere_matiere=8;
					$cell_ajustee_texte_matiere_ratio_min_max=3;

					// Forcé pour le moment
					$cell_ajustee_texte_matiere=1;
					if((isset($cell_ajustee_texte_matiere))&&($cell_ajustee_texte_matiere==1)) {
						// On met un décalage pour ne pas coller le texte à la bordure
						$Y_decal_cell_ajustee=2;
						$pdf->SetXY($param_bull2016["x_EPI_AP_Parcours"], $y_courant+$Y_decal_cell_ajustee);

						$texte=$info_nom_matiere;
						$taille_max_police=$hauteur_caractere_matiere;
						$taille_min_police=ceil($taille_max_police/$cell_ajustee_texte_matiere_ratio_min_max);

						$largeur_dispo=$param_bull2016["largeur_EPI_AP_Parcours_col_1"];
						$h_cell=$hauteur_EPI_AP_Parcours/2-$Y_decal_cell_ajustee;

						cell_ajustee($texte,$pdf->GetX(),$pdf->GetY(),$largeur_dispo,$h_cell,$taille_max_police,$taille_min_police,'');
			
					}
					else {
						$val = $pdf->GetStringWidth($info_nom_matiere);
						$taille_texte = $param_bull2016["largeur_EPI_AP_Parcours_col_1"]-2;
						$grandeur_texte='test';
						while($grandeur_texte!='ok') {
							if($taille_texte<$val)
							{
								$hauteur_caractere_matiere = $hauteur_caractere_matiere-0.3;
								$pdf->SetFont('DejaVu','B',$hauteur_caractere_matiere);
								$val = $pdf->GetStringWidth($info_nom_matiere);
							}
							else {
								$grandeur_texte='ok';
							}
						}
						$grandeur_texte='test';
						$Y_decal=$y_courant;
						$pdf->SetXY($param_bull2016["x_EPI_AP_Parcours"], $Y_decal);
						$pdf->Cell($param_bull2016["largeur_EPI_AP_Parcours_col_1"], $hauteur_EPI_AP_Parcours/2, ($info_nom_matiere),'',1,'L');
					}


					// On note l'ordonnée pour le nom des professeurs
					$Y_decal = $y_courant+($hauteur_EPI_AP_Parcours/2);
					$pdf->SetXY($param_bull2016["x_EPI_AP_Parcours"], $Y_decal);
					$pdf->SetFont('DejaVu','',8);

					// nom des professeurs
					if ( isset($tab_bull['eleve'][$i]['aid_e'][$m]['aid_prof_resp_login'][0]) )
					{

						// Présentation en ligne des profs
						// On n'a pas forcément le formatage choisi pour la classe...
						//$text_prof=$tab_bull['groupe'][$m]["profs"]["proflist_string"]."  ";
						$text_prof="";
						for($loop_prof_grp=0;$loop_prof_grp<count($tab_bull['eleve'][$i]['aid_e'][$m]['aid_prof_resp_login']);$loop_prof_grp++) {
							$tmp_login_prof=$tab_bull['eleve'][$i]['aid_e'][$m]['aid_prof_resp_login'][$loop_prof_grp];
							if($loop_prof_grp>0) {$text_prof.=", ";}
							$text_prof.=affiche_utilisateur($tmp_login_prof,$tab_bull['eleve'][$i]['id_classe']);
						}

						if($text_prof!="") {
							//$espace_matiere_prof = $espace_entre_matier/2;
							$espace_matiere_prof = $hauteur_EPI_AP_Parcours/2;
							$hauteur_caractere_prof = 7;

							$texte=$text_prof;
							$taille_max_police=$hauteur_caractere_prof;
							$taille_min_police=ceil($hauteur_caractere_prof/3);

							$largeur_dispo=$param_bull2016["largeur_EPI_AP_Parcours_col_1"];
							$h_cell=$espace_matiere_prof;

							$pdf->SetX($param_bull2016["x_EPI_AP_Parcours"]);

							cell_ajustee(($texte),$pdf->GetX(),$pdf->GetY(),$largeur_dispo,$h_cell,$taille_max_police,$taille_min_police,'');
						}
					}


					// Colonne 2 : Appréciation
					$pdf->SetFillColor($param_bull2016["couleur_EPI_alt".($cpt_matiere%2+1)]["R"], $param_bull2016["couleur_EPI_alt".($cpt_matiere%2+1)]["V"], $param_bull2016["couleur_EPI_alt".($cpt_matiere%2+1)]["B"]);
					$pdf->Rect($param_bull2016["x_EPI_AP_Parcours_col_2"], $y_courant, $param_bull2016["largeur_EPI_AP_Parcours_col_2"], $hauteur_EPI_AP_Parcours, 'F');

					$pdf->SetFillColor(0, 0, 0);
					$pdf->SetXY($param_bull2016["x_EPI_AP_Parcours_col_2"], $y_courant);

					// calcul de la taille du texte des appréciations
					$hauteur_caractere_appreciation = 8;
					$pdf->SetFont('DejaVu','',$hauteur_caractere_appreciation);

					//suppression des espaces en début et en fin
					//$app_aff = trim($tab_bull['app'][$m][$i]);
					$app_aff="";
					if($tab_bull['eleve'][$i]['aid_e'][$m]['message']!='') {
						$app_aff.=$tab_bull['eleve'][$i]['aid_e'][$m]['message'];
					}
					//if($app_aff!='') {$app_aff.=" ";}
					if(($tab_bull['eleve'][$i]['aid_e'][$m]['display_nom']=='y')&&($tab_bull['eleve'][$i]['aid_e'][$m]['aid_nom']!='')) {
						if($app_aff!='') {$app_aff.=" ";}
						$app_aff.=$tab_bull['eleve'][$i]['aid_e'][$m]['aid_nom'];
					}
					if($app_aff!='') {$app_aff.="\n";}
					$app_aff.=trim($tab_bull['eleve'][$i]['aid_e'][$m]['aid_appreciation']);

					fich_debug_bull("__________________________________________\n");
					fich_debug_bull("$app_aff\n");
					fich_debug_bull("__________________________________________\n");

					// DEBUT AJUSTEMENT TAILLE APPRECIATION
					$taille_texte_total = $pdf->GetStringWidth($app_aff);
					//$largeur_appreciation2 = $largeur_appreciation - $largeur_sous_matiere;
					$largeur_appreciation2=$param_bull2016["largeur_EPI_AP_Parcours_col_2"];

					if($use_cell_ajustee=="n") {
						//$taille_texte = (($espace_entre_matier/3)*$largeur_appreciation2);
						$nb_ligne_app = '2.8';
						//$nb_ligne_app = '3.8';
						//$nb_ligne_app = '4.8';
						$taille_texte_max = $nb_ligne_app * ($largeur_appreciation2-4);
						//$taille_texte_max = $nb_ligne_app * ($largeur_appreciation2);
						$grandeur_texte='test';

						fich_debug_bull("\$taille_texte_total=$taille_texte_total\n");
						fich_debug_bull("\$largeur_appreciation2=$largeur_appreciation2\n");
						fich_debug_bull("\$nb_ligne_app=$nb_ligne_app\n");
						//fich_debug_bull("\$taille_texte_max = \$nb_ligne_app * (\$largeur_appreciation2-4)=$nb_ligne_app * ($largeur_appreciation2-4)=$taille_texte_max\n");
						fich_debug_bull("\$taille_texte_max = \$nb_ligne_app * (\$largeur_appreciation2)=$nb_ligne_app * ($largeur_appreciation2)=$taille_texte_max\n");

						while($grandeur_texte!='ok') {
							if($taille_texte_max < $taille_texte_total)
							{
								$hauteur_caractere_appreciation = $hauteur_caractere_appreciation-0.3;
								//$hauteur_caractere_appreciation = $hauteur_caractere_appreciation-0.1;
								$pdf->SetFont('DejaVu','',$hauteur_caractere_appreciation);
								$taille_texte_total = $pdf->GetStringWidth($app_aff);
							}
							else {
								$grandeur_texte='ok';
							}
						}
						$grandeur_texte='test';
						$pdf->drawTextBox(($app_aff), $largeur_appreciation2, $hauteur_EPI_AP_Parcours, 'J', 'M', 1);
					}
					else {
						$texte=$app_aff;
						//$texte="Bla bla\nbli ".$app_aff;
						$taille_max_police=$hauteur_caractere_appreciation;
						$taille_min_police=ceil($taille_max_police/3);

						$largeur_dispo=$largeur_appreciation2;
						$h_cell=$hauteur_EPI_AP_Parcours;

						if(getSettingValue('suppr_balises_app_prof')=='y') {$texte=preg_replace('/<(.*)>/U','',$texte);}
						cell_ajustee(($texte),$pdf->GetX(),$pdf->GetY(),$largeur_dispo,$h_cell,$taille_max_police,$taille_min_police,'');
					}

					$y_courant+=$hauteur_EPI_AP_Parcours+0.5;
					$cpt_matiere++;


				}
				// Fin des AID_e tagués AP

				$y_courant+=$param_bull2016["espace_vertical_entre_sections_EPI_AP_Parcours"];
			}

			//=========================================

			// Parcours éducatifs

			if($nb_Parcours>0) {

				$pdf->SetFillColor($param_bull2016["couleur_bandeau_EPI"]["R"], $param_bull2016["couleur_bandeau_EPI"]["V"], $param_bull2016["couleur_bandeau_EPI"]["B"]);
				$pdf->Rect($param_bull2016["x_EPI_AP_Parcours"], $y_courant, $param_bull2016["largeur_EPI_AP_Parcours"], $param_bull2016["hauteur_ligne_titre_EPI_AP_Parcours"], 'F');

				$pdf->SetFillColor(0, 0, 0);
				//$pdf->SetTextColor(255, 255, 255);
				$pdf->SetTextColor(0, 0, 0);
				$pdf->SetXY($param_bull2016["x_EPI_AP_Parcours"], $y_courant);
				$pdf->SetFont('DejaVu','',8);
				$pdf->Cell($param_bull2016["largeur_EPI_AP_Parcours"],7, "Parcours éducatifs : projet(s) mis en oeuvre et implication de l'élève",0,2,'L');

				$cpt_matiere=0;
				$y_courant+=$param_bull2016["hauteur_ligne_titre_EPI_AP_Parcours"]+0.5;


				// AID_b de type Parcours
				for($loop=0;$loop<count($indice_AID_b_Parcours);$loop++) {
					$m=$indice_AID_b_Parcours[$loop];

					// Colonne 1 : Matière, prof

					$pdf->SetFillColor($param_bull2016["couleur_EPI_alt".($cpt_matiere%2+1)]["R"], $param_bull2016["couleur_EPI_alt".($cpt_matiere%2+1)]["V"], $param_bull2016["couleur_EPI_alt".($cpt_matiere%2+1)]["B"]);
					$pdf->Rect($param_bull2016["x_EPI_AP_Parcours"], $y_courant, $param_bull2016["largeur_EPI_AP_Parcours_col_1"], $hauteur_EPI_AP_Parcours, 'F');
					$pdf->SetFillColor(0, 0, 0);

					$pdf->SetTextColor(0, 0, 0);
					$pdf->SetXY($param_bull2016["x_EPI_AP_Parcours"], $y_courant);
					$pdf->SetFont('DejaVu','',8);

					if($tab_bull['eleve'][$i]['aid_b'][$m]['display_nom']=='z') {
						if((isset($tab_bull['eleve'][$i]['aid_b'][$m]['aid_nom']))&&($tab_bull['eleve'][$i]['aid_b'][$m]['aid_nom']!="")) {
							$info_nom_matiere=$tab_bull['eleve'][$i]['aid_b'][$m]['aid_nom'];
						}
						elseif((isset($tab_bull['eleve'][$i]['aid_b'][$m]['nom_complet']))&&($tab_bull['eleve'][$i]['aid_b'][$m]['nom_complet']!="")) {
							$info_nom_matiere=$tab_bull['eleve'][$i]['aid_b'][$m]['nom_complet'];
						}
						else {
							$info_nom_matiere=$tab_bull['eleve'][$i]['aid_b'][$m]['nom'];
						}
					}
					elseif($tab_bull['eleve'][$i]['aid_b'][$m]['display_nom']=='x') {
						if((isset($tab_bull['eleve'][$i]['aid_b'][$m]['nom']))&&($tab_bull['eleve'][$i]['aid_b'][$m]['nom']!="")) {
							$info_nom_matiere=$tab_bull['eleve'][$i]['aid_b'][$m]['nom'];
						}
						else {
							$info_nom_matiere=$tab_bull['eleve'][$i]['aid_b'][$m]['nom_complet'];
						}

						if((isset($tab_bull['eleve'][$i]['aid_b'][$m]['aid_nom']))&&($tab_bull['eleve'][$i]['aid_b'][$m]['aid_nom']!="")) {
							if($info_nom_matiere!="") {
								$info_nom_matiere.=": ";
							}
							$info_nom_matiere.=$tab_bull['eleve'][$i]['aid_b'][$m]['aid_nom'];
						}
					}
					else {
						if((isset($tab_bull['eleve'][$i]['aid_b'][$m]['nom_complet']))&&($tab_bull['eleve'][$i]['aid_b'][$m]['nom_complet']!="")) {
							$info_nom_matiere=$tab_bull['eleve'][$i]['aid_b'][$m]['nom_complet'];
						}
						else {
							$info_nom_matiere=$tab_bull['eleve'][$i]['aid_b'][$m]['nom'];
						}
					}
					if($info_nom_matiere=="") {
						$info_nom_matiere="AID";
					}

					$hauteur_caractere_matiere=8;
					$cell_ajustee_texte_matiere_ratio_min_max=3;

					// Forcé pour le moment
					$cell_ajustee_texte_matiere=1;
					if((isset($cell_ajustee_texte_matiere))&&($cell_ajustee_texte_matiere==1)) {
						// On met un décalage pour ne pas coller le texte à la bordure
						$Y_decal_cell_ajustee=2;
						$pdf->SetXY($param_bull2016["x_EPI_AP_Parcours"], $y_courant+$Y_decal_cell_ajustee);

						$texte=$info_nom_matiere;
						$taille_max_police=$hauteur_caractere_matiere;
						$taille_min_police=ceil($taille_max_police/$cell_ajustee_texte_matiere_ratio_min_max);

						$largeur_dispo=$param_bull2016["largeur_EPI_AP_Parcours_col_1"];
						$h_cell=$hauteur_EPI_AP_Parcours/2-$Y_decal_cell_ajustee;

						cell_ajustee($texte,$pdf->GetX(),$pdf->GetY(),$largeur_dispo,$h_cell,$taille_max_police,$taille_min_police,'');
			
					}
					else {
						$val = $pdf->GetStringWidth($info_nom_matiere);
						$taille_texte = $param_bull2016["largeur_EPI_AP_Parcours_col_1"]-2;
						$grandeur_texte='test';
						while($grandeur_texte!='ok') {
							if($taille_texte<$val)
							{
								$hauteur_caractere_matiere = $hauteur_caractere_matiere-0.3;
								$pdf->SetFont('DejaVu','B',$hauteur_caractere_matiere);
								$val = $pdf->GetStringWidth($info_nom_matiere);
							}
							else {
								$grandeur_texte='ok';
							}
						}
						$grandeur_texte='test';
						$Y_decal=$y_courant;
						$pdf->SetXY($param_bull2016["x_EPI_AP_Parcours"], $Y_decal);
						$pdf->Cell($param_bull2016["largeur_EPI_AP_Parcours_col_1"], $hauteur_EPI_AP_Parcours/2, ($info_nom_matiere),'',1,'L');
					}


					// On note l'ordonnée pour le nom des professeurs
					$Y_decal = $y_courant+($hauteur_EPI_AP_Parcours/2);
					$pdf->SetXY($param_bull2016["x_EPI_AP_Parcours"], $Y_decal);
					$pdf->SetFont('DejaVu','',8);

					// nom des professeurs
					if ( isset($tab_bull['eleve'][$i]['aid_b'][$m]['aid_prof_resp_login'][0]) )
					{

						// Présentation en ligne des profs
						// On n'a pas forcément le formatage choisi pour la classe...
						//$text_prof=$tab_bull['groupe'][$m]["profs"]["proflist_string"]."  ";
						$text_prof="";
						for($loop_prof_grp=0;$loop_prof_grp<count($tab_bull['eleve'][$i]['aid_b'][$m]['aid_prof_resp_login']);$loop_prof_grp++) {
							$tmp_login_prof=$tab_bull['eleve'][$i]['aid_b'][$m]['aid_prof_resp_login'][$loop_prof_grp];
							if($loop_prof_grp>0) {$text_prof.=", ";}
							$text_prof.=affiche_utilisateur($tmp_login_prof,$tab_bull['eleve'][$i]['id_classe']);
						}

						if($text_prof!="") {
							//$espace_matiere_prof = $espace_entre_matier/2;
							$espace_matiere_prof = $hauteur_EPI_AP_Parcours/2;
							$hauteur_caractere_prof = 7;

							$texte=$text_prof;
							$taille_max_police=$hauteur_caractere_prof;
							$taille_min_police=ceil($hauteur_caractere_prof/3);

							$largeur_dispo=$param_bull2016["largeur_EPI_AP_Parcours_col_1"];
							$h_cell=$espace_matiere_prof;

							$pdf->SetX($param_bull2016["x_EPI_AP_Parcours"]);

							cell_ajustee(($texte),$pdf->GetX(),$pdf->GetY(),$largeur_dispo,$h_cell,$taille_max_police,$taille_min_police,'');
						}
					}


					// Colonne 2 : Appréciation
					$pdf->SetFillColor($param_bull2016["couleur_EPI_alt".($cpt_matiere%2+1)]["R"], $param_bull2016["couleur_EPI_alt".($cpt_matiere%2+1)]["V"], $param_bull2016["couleur_EPI_alt".($cpt_matiere%2+1)]["B"]);
					$pdf->Rect($param_bull2016["x_EPI_AP_Parcours_col_2"], $y_courant, $param_bull2016["largeur_EPI_AP_Parcours_col_2"], $hauteur_EPI_AP_Parcours, 'F');

					$pdf->SetFillColor(0, 0, 0);
					$pdf->SetXY($param_bull2016["x_EPI_AP_Parcours_col_2"], $y_courant);

					// calcul de la taille du texte des appréciations
					$hauteur_caractere_appreciation = 8;
					$pdf->SetFont('DejaVu','',$hauteur_caractere_appreciation);

					//suppression des espaces en début et en fin
					//$app_aff = trim($tab_bull['app'][$m][$i]);
					$app_aff="";
					if($tab_bull['eleve'][$i]['aid_b'][$m]['message']!='') {
						$app_aff.=$tab_bull['eleve'][$i]['aid_b'][$m]['message'];
					}
					//if($app_aff!='') {$app_aff.=" ";}
					if(($tab_bull['eleve'][$i]['aid_b'][$m]['display_nom']=='y')&&($tab_bull['eleve'][$i]['aid_b'][$m]['aid_nom']!='')) {
						if($app_aff!='') {$app_aff.=" ";}
						$app_aff.=$tab_bull['eleve'][$i]['aid_b'][$m]['aid_nom'];
					}
					if($app_aff!='') {$app_aff.="\n";}
					$app_aff.=trim($tab_bull['eleve'][$i]['aid_b'][$m]['aid_appreciation']);

					fich_debug_bull("__________________________________________\n");
					fich_debug_bull("$app_aff\n");
					fich_debug_bull("__________________________________________\n");

					// DEBUT AJUSTEMENT TAILLE APPRECIATION
					$taille_texte_total = $pdf->GetStringWidth($app_aff);
					//$largeur_appreciation2 = $largeur_appreciation - $largeur_sous_matiere;
					$largeur_appreciation2=$param_bull2016["largeur_EPI_AP_Parcours_col_2"];

					if($use_cell_ajustee=="n") {
						//$taille_texte = (($espace_entre_matier/3)*$largeur_appreciation2);
						$nb_ligne_app = '2.8';
						//$nb_ligne_app = '3.8';
						//$nb_ligne_app = '4.8';
						$taille_texte_max = $nb_ligne_app * ($largeur_appreciation2-4);
						//$taille_texte_max = $nb_ligne_app * ($largeur_appreciation2);
						$grandeur_texte='test';

						fich_debug_bull("\$taille_texte_total=$taille_texte_total\n");
						fich_debug_bull("\$largeur_appreciation2=$largeur_appreciation2\n");
						fich_debug_bull("\$nb_ligne_app=$nb_ligne_app\n");
						//fich_debug_bull("\$taille_texte_max = \$nb_ligne_app * (\$largeur_appreciation2-4)=$nb_ligne_app * ($largeur_appreciation2-4)=$taille_texte_max\n");
						fich_debug_bull("\$taille_texte_max = \$nb_ligne_app * (\$largeur_appreciation2)=$nb_ligne_app * ($largeur_appreciation2)=$taille_texte_max\n");

						while($grandeur_texte!='ok') {
							if($taille_texte_max < $taille_texte_total)
							{
								$hauteur_caractere_appreciation = $hauteur_caractere_appreciation-0.3;
								//$hauteur_caractere_appreciation = $hauteur_caractere_appreciation-0.1;
								$pdf->SetFont('DejaVu','',$hauteur_caractere_appreciation);
								$taille_texte_total = $pdf->GetStringWidth($app_aff);
							}
							else {
								$grandeur_texte='ok';
							}
						}
						$grandeur_texte='test';
						$pdf->drawTextBox(($app_aff), $largeur_appreciation2, $hauteur_EPI_AP_Parcours, 'J', 'M', 1);
					}
					else {
						$texte=$app_aff;
						//$texte="Bla bla\nbli ".$app_aff;
						$taille_max_police=$hauteur_caractere_appreciation;
						$taille_min_police=ceil($taille_max_police/3);

						$largeur_dispo=$largeur_appreciation2;
						$h_cell=$hauteur_EPI_AP_Parcours;

						if(getSettingValue('suppr_balises_app_prof')=='y') {$texte=preg_replace('/<(.*)>/U','',$texte);}
						cell_ajustee(($texte),$pdf->GetX(),$pdf->GetY(),$largeur_dispo,$h_cell,$taille_max_police,$taille_min_police,'');
					}

					$y_courant+=$hauteur_EPI_AP_Parcours+0.5;
					$cpt_matiere++;


				}
				// Fin des AID_b tagués Parcours


				// Enseignements de la table 'groupes' tagués Parcours
				for($m=0;$m<count($tab_bull['groupe']);$m++) {
					//if(isset($tab_bull['note'][$m][$i])) {
					// On n'affiche pas ici les groupes correspondant à AP, EPI ou Parcours
					if((isset($tab_bull['note'][$m][$i]))&&
					(isset($tab_bull['groupe'][$m]['type_grp'][0]))&&
					($tab_bull['groupe'][$m]['type_grp'][0]['nom_court']=="Parcours")) {

						// Colonne 1 : Matière, prof

						$pdf->SetFillColor($param_bull2016["couleur_EPI_alt".($cpt_matiere%2+1)]["R"], $param_bull2016["couleur_EPI_alt".($cpt_matiere%2+1)]["V"], $param_bull2016["couleur_EPI_alt".($cpt_matiere%2+1)]["B"]);
						$pdf->Rect($param_bull2016["x_EPI_AP_Parcours"], $y_courant, $param_bull2016["largeur_EPI_AP_Parcours_col_1"], $hauteur_EPI_AP_Parcours, 'F');
						$pdf->SetFillColor(0, 0, 0);

						$pdf->SetTextColor(0, 0, 0);
						$pdf->SetXY($param_bull2016["x_EPI_AP_Parcours"], $y_courant);
						$pdf->SetFont('DejaVu','',8);

						if(getSettingValue('bul_rel_nom_matieres')=='nom_groupe') {
							$info_nom_matiere=$tab_bull['groupe'][$m]['name'];
						}
						elseif(getSettingValue('bul_rel_nom_matieres')=='description_groupe') {
							$info_nom_matiere=$tab_bull['groupe'][$m]['description'];
						}
						else {
							// Pour parer au bug sur la suppression de matière alors que des groupes sont conservés:
							if(isset($tab_bull['groupe'][$m]['matiere']['nom_complet'])) {
								$info_nom_matiere=$tab_bull['groupe'][$m]['matiere']['nom_complet'];
							}
							else {
								$info_nom_matiere=$tab_bull['groupe'][$m]['name']." (".$tab_bull['groupe'][$m]['id'].")";
							}
						}

						$hauteur_caractere_matiere=8;
						$cell_ajustee_texte_matiere_ratio_min_max=3;

						// 20130927 : cell_ajustee() ou pas sur le nom de matière/enseignement
						$cell_ajustee_texte_matiere=1;
						if((isset($cell_ajustee_texte_matiere))&&($cell_ajustee_texte_matiere==1)) {
							// Encadrement
							//$pdf->Cell($tab_modele_pdf["largeur_matiere"][$classe_id], $espace_entre_matier, "",'LRBT',1,'L');

							// cell_ajustee() ne centre pas verticalement le texte.
							// On met un décalage pour ne pas coller le texte à la bordure
							$Y_decal_cell_ajustee=2;
							// On repositionne et on inscrit le nom de matière sur la moitié de la hauteur de la cellule
							//$pdf->SetXY($X_bloc_matiere, $Y_decal+$Y_decal_cell_ajustee);
							//$pdf->SetXY($param_bull2016["x_acquis_col_1"], $y_courant);

							$pdf->SetXY($param_bull2016["x_EPI_AP_Parcours"], $y_courant+$Y_decal_cell_ajustee);

							$texte=$info_nom_matiere;
							$taille_max_police=$hauteur_caractere_matiere;
							//$taille_min_police=ceil($taille_max_police/$tab_modele_pdf["cell_ajustee_texte_matiere_ratio_min_max"][$classe_id]);
							$taille_min_police=ceil($taille_max_police/$cell_ajustee_texte_matiere_ratio_min_max);

							$largeur_dispo=$param_bull2016["largeur_EPI_AP_Parcours_col_1"];
							//$h_cell=$espace_entre_matier/2-$Y_decal_cell_ajustee;
							$h_cell=$hauteur_EPI_AP_Parcours/2-$Y_decal_cell_ajustee;

							//cell_ajustee("<b>".$texte."</b>",$pdf->GetX(),$pdf->GetY(),$largeur_dispo,$h_cell,$taille_max_police,$taille_min_police,'');
							cell_ajustee($texte,$pdf->GetX(),$pdf->GetY(),$largeur_dispo,$h_cell,$taille_max_police,$taille_min_police,'');
			
						}
						else {
							$val = $pdf->GetStringWidth($info_nom_matiere);
							$taille_texte = $param_bull2016["largeur_EPI_AP_Parcours_col_1"]-2;
							$grandeur_texte='test';
							while($grandeur_texte!='ok') {
								if($taille_texte<$val)
								{
									$hauteur_caractere_matiere = $hauteur_caractere_matiere-0.3;
									$pdf->SetFont('DejaVu','',$hauteur_caractere_matiere);
									$val = $pdf->GetStringWidth($info_nom_matiere);
								}
								else {
									$grandeur_texte='ok';
								}
							}
							$grandeur_texte='test';
							$Y_decal=$y_courant;
							$pdf->SetXY($param_bull2016["x_EPI_AP_Parcours"], $Y_decal);
							$pdf->Cell($param_bull2016["largeur_EPI_AP_Parcours_col_1"], $hauteur_EPI_AP_Parcours/2, ($info_nom_matiere),'',1,'L');
						}
			

						// On note l'ordonnée pour le nom des professeurs
						$Y_decal = $y_courant+($hauteur_EPI_AP_Parcours/2);
						$pdf->SetXY($param_bull2016["x_EPI_AP_Parcours"], $Y_decal);
						$pdf->SetFont('DejaVu','',8);

						//fich_debug_bull("\$info_nom_matiere=$info_nom_matiere\n");
						//fich_debug_bull("Le nom de matière est écrit; on est à mi-hauteur de la cellule pour écrire le nom du prof:\n");
						//fich_debug_bull("\$Y_decal=$Y_decal\n");

						// nom des professeurs

						if ( isset($tab_bull['groupe'][$m]["profs"]["list"]) )
						{

							// Présentation en ligne des profs
							// On n'a pas forcément le formatage choisi pour la classe...
							//$text_prof=$tab_bull['groupe'][$m]["profs"]["proflist_string"]."  ";
							$text_prof="";
							for($loop_prof_grp=0;$loop_prof_grp<count($tab_bull['groupe'][$m]["profs"]["list"]);$loop_prof_grp++) {
								$tmp_login_prof=$tab_bull['groupe'][$m]["profs"]["list"][$loop_prof_grp];
								if($loop_prof_grp>0) {$text_prof.=", ";}
								$text_prof.=affiche_utilisateur($tmp_login_prof,$tab_bull['eleve'][$i]['id_classe']);
							}

							if($text_prof!="") {
								//$espace_matiere_prof = $espace_entre_matier/2;
								$espace_matiere_prof = $hauteur_EPI_AP_Parcours/2;
								$hauteur_caractere_prof = 7;

								/*
								if($use_cell_ajustee=="n") {
									$pdf->SetFont('DejaVu','',$hauteur_caractere_prof);
									$val = $pdf->GetStringWidth($text_prof);
									$taille_texte = ($tab_modele_pdf["largeur_matiere"][$classe_id]);
									$grandeur_texte='test';
									while($grandeur_texte!='ok') {
										if($taille_texte<$val)
										{
											$hauteur_caractere_prof = $hauteur_caractere_prof-0.3;
											$pdf->SetFont('DejaVu','',$hauteur_caractere_prof);
											$val = $pdf->GetStringWidth($text_prof);
										}
										else {
											$grandeur_texte='ok';
										}
									}
									$grandeur_texte='test';
									$pdf->SetX($X_bloc_matiere);
									$pdf->Cell($tab_modele_pdf["largeur_matiere"][$classe_id], $espace_matiere_prof, ($text_prof),'LR',1,'L');
								}
								else {
								*/
									$texte=$text_prof;
									$taille_max_police=$hauteur_caractere_prof;
									$taille_min_police=ceil($hauteur_caractere_prof/3);

									$largeur_dispo=$param_bull2016["largeur_EPI_AP_Parcours_col_1"];
									$h_cell=$espace_matiere_prof;

									$pdf->SetX($param_bull2016["x_EPI_AP_Parcours"]);

									cell_ajustee(($texte),$pdf->GetX(),$pdf->GetY(),$largeur_dispo,$h_cell,$taille_max_police,$taille_min_police,'');
								//}
							}
						}


						// Colonne 2 : Appréciation
						$pdf->SetFillColor($param_bull2016["couleur_EPI_alt".($cpt_matiere%2+1)]["R"], $param_bull2016["couleur_EPI_alt".($cpt_matiere%2+1)]["V"], $param_bull2016["couleur_EPI_alt".($cpt_matiere%2+1)]["B"]);
						$pdf->Rect($param_bull2016["x_EPI_AP_Parcours_col_2"], $y_courant, $param_bull2016["largeur_EPI_AP_Parcours_col_2"], $hauteur_EPI_AP_Parcours, 'F');

						$pdf->SetFillColor(0, 0, 0);
						$pdf->SetXY($param_bull2016["x_EPI_AP_Parcours_col_2"], $y_courant);

						// calcul de la taille du texte des appréciations
						$hauteur_caractere_appreciation = 8;
						$pdf->SetFont('DejaVu','',$hauteur_caractere_appreciation);

						//suppression des espaces en début et en fin
						$app_aff = trim($tab_bull['app'][$m][$i]);

						fich_debug_bull("__________________________________________\n");
						fich_debug_bull("$app_aff\n");
						fich_debug_bull("__________________________________________\n");

						// DEBUT AJUSTEMENT TAILLE APPRECIATION
						$taille_texte_total = $pdf->GetStringWidth($app_aff);
						//$largeur_appreciation2 = $largeur_appreciation - $largeur_sous_matiere;
						$largeur_appreciation2=$param_bull2016["largeur_EPI_AP_Parcours_col_2"];

						if($use_cell_ajustee=="n") {
							//$taille_texte = (($espace_entre_matier/3)*$largeur_appreciation2);
							$nb_ligne_app = '2.8';
							//$nb_ligne_app = '3.8';
							//$nb_ligne_app = '4.8';
							$taille_texte_max = $nb_ligne_app * ($largeur_appreciation2-4);
							//$taille_texte_max = $nb_ligne_app * ($largeur_appreciation2);
							$grandeur_texte='test';

							fich_debug_bull("\$taille_texte_total=$taille_texte_total\n");
							fich_debug_bull("\$largeur_appreciation2=$largeur_appreciation2\n");
							fich_debug_bull("\$nb_ligne_app=$nb_ligne_app\n");
							//fich_debug_bull("\$taille_texte_max = \$nb_ligne_app * (\$largeur_appreciation2-4)=$nb_ligne_app * ($largeur_appreciation2-4)=$taille_texte_max\n");
							fich_debug_bull("\$taille_texte_max = \$nb_ligne_app * (\$largeur_appreciation2)=$nb_ligne_app * ($largeur_appreciation2)=$taille_texte_max\n");

							while($grandeur_texte!='ok') {
								if($taille_texte_max < $taille_texte_total)
								{
									$hauteur_caractere_appreciation = $hauteur_caractere_appreciation-0.3;
									//$hauteur_caractere_appreciation = $hauteur_caractere_appreciation-0.1;
									$pdf->SetFont('DejaVu','',$hauteur_caractere_appreciation);
									$taille_texte_total = $pdf->GetStringWidth($app_aff);
								}
								else {
									$grandeur_texte='ok';
								}
							}
							$grandeur_texte='test';
							$pdf->drawTextBox(($app_aff), $largeur_appreciation2, $hauteur_EPI_AP_Parcours, 'J', 'M', 1);
						}
						else {
							$texte=$app_aff;
							//$texte="Bla bla\nbli ".$app_aff;
							$taille_max_police=$hauteur_caractere_appreciation;
							$taille_min_police=ceil($taille_max_police/3);

							$largeur_dispo=$largeur_appreciation2;
							$h_cell=$hauteur_EPI_AP_Parcours;

							if(getSettingValue('suppr_balises_app_prof')=='y') {$texte=preg_replace('/<(.*)>/U','',$texte);}
							cell_ajustee(($texte),$pdf->GetX(),$pdf->GetY(),$largeur_dispo,$h_cell,$taille_max_police,$taille_min_police,'');
						}

						$y_courant+=$hauteur_EPI_AP_Parcours+0.5;
						$cpt_matiere++;

					}
				}

				// Fin des enseignements de la table 'groupes' tagués AP


				// AID_e de type Parcours
				for($loop=0;$loop<count($indice_AID_e_Parcours);$loop++) {
					$m=$indice_AID_e_Parcours[$loop];

					// Colonne 1 : Matière, prof

					$pdf->SetFillColor($param_bull2016["couleur_EPI_alt".($cpt_matiere%2+1)]["R"], $param_bull2016["couleur_EPI_alt".($cpt_matiere%2+1)]["V"], $param_bull2016["couleur_EPI_alt".($cpt_matiere%2+1)]["B"]);
					$pdf->Rect($param_bull2016["x_EPI_AP_Parcours"], $y_courant, $param_bull2016["largeur_EPI_AP_Parcours_col_1"], $hauteur_EPI_AP_Parcours, 'F');
					$pdf->SetFillColor(0, 0, 0);

					$pdf->SetTextColor(0, 0, 0);
					$pdf->SetXY($param_bull2016["x_EPI_AP_Parcours"], $y_courant);
					$pdf->SetFont('DejaVu','',8);

					if($tab_bull['eleve'][$i]['aid_e'][$m]['display_nom']=='z') {
						if((isset($tab_bull['eleve'][$i]['aid_e'][$m]['aid_nom']))&&($tab_bull['eleve'][$i]['aid_e'][$m]['aid_nom']!="")) {
							$info_nom_matiere=$tab_bull['eleve'][$i]['aid_e'][$m]['aid_nom'];
						}
						elseif((isset($tab_bull['eleve'][$i]['aid_e'][$m]['nom_complet']))&&($tab_bull['eleve'][$i]['aid_e'][$m]['nom_complet']!="")) {
							$info_nom_matiere=$tab_bull['eleve'][$i]['aid_e'][$m]['nom_complet'];
						}
						else {
							$info_nom_matiere=$tab_bull['eleve'][$i]['aid_e'][$m]['nom'];
						}
					}
					elseif($tab_bull['eleve'][$i]['aid_e'][$m]['display_nom']=='x') {
						if((isset($tab_bull['eleve'][$i]['aid_e'][$m]['nom']))&&($tab_bull['eleve'][$i]['aid_e'][$m]['nom']!="")) {
							$info_nom_matiere=$tab_bull['eleve'][$i]['aid_e'][$m]['nom'];
						}
						else {
							$info_nom_matiere=$tab_bull['eleve'][$i]['aid_e'][$m]['nom_complet'];
						}

						if((isset($tab_bull['eleve'][$i]['aid_e'][$m]['aid_nom']))&&($tab_bull['eleve'][$i]['aid_e'][$m]['aid_nom']!="")) {
							if($info_nom_matiere!="") {
								$info_nom_matiere.=": ";
							}
							$info_nom_matiere.=$tab_bull['eleve'][$i]['aid_e'][$m]['aid_nom'];
						}
					}
					else {
						if((isset($tab_bull['eleve'][$i]['aid_e'][$m]['nom_complet']))&&($tab_bull['eleve'][$i]['aid_e'][$m]['nom_complet']!="")) {
							$info_nom_matiere=$tab_bull['eleve'][$i]['aid_e'][$m]['nom_complet'];
						}
						else {
							$info_nom_matiere=$tab_bull['eleve'][$i]['aid_e'][$m]['nom'];
						}
					}
					if($info_nom_matiere=="") {
						$info_nom_matiere="AID";
					}

					$hauteur_caractere_matiere=8;
					$cell_ajustee_texte_matiere_ratio_min_max=3;

					// Forcé pour le moment
					$cell_ajustee_texte_matiere=1;
					if((isset($cell_ajustee_texte_matiere))&&($cell_ajustee_texte_matiere==1)) {
						// On met un décalage pour ne pas coller le texte à la bordure
						$Y_decal_cell_ajustee=2;
						$pdf->SetXY($param_bull2016["x_EPI_AP_Parcours"], $y_courant+$Y_decal_cell_ajustee);

						$texte=$info_nom_matiere;
						$taille_max_police=$hauteur_caractere_matiere;
						$taille_min_police=ceil($taille_max_police/$cell_ajustee_texte_matiere_ratio_min_max);

						$largeur_dispo=$param_bull2016["largeur_EPI_AP_Parcours_col_1"];
						$h_cell=$hauteur_EPI_AP_Parcours/2-$Y_decal_cell_ajustee;

						cell_ajustee($texte,$pdf->GetX(),$pdf->GetY(),$largeur_dispo,$h_cell,$taille_max_police,$taille_min_police,'');
			
					}
					else {
						$val = $pdf->GetStringWidth($info_nom_matiere);
						$taille_texte = $param_bull2016["largeur_EPI_AP_Parcours_col_1"]-2;
						$grandeur_texte='test';
						while($grandeur_texte!='ok') {
							if($taille_texte<$val)
							{
								$hauteur_caractere_matiere = $hauteur_caractere_matiere-0.3;
								$pdf->SetFont('DejaVu','B',$hauteur_caractere_matiere);
								$val = $pdf->GetStringWidth($info_nom_matiere);
							}
							else {
								$grandeur_texte='ok';
							}
						}
						$grandeur_texte='test';
						$Y_decal=$y_courant;
						$pdf->SetXY($param_bull2016["x_EPI_AP_Parcours"], $Y_decal);
						$pdf->Cell($param_bull2016["largeur_EPI_AP_Parcours_col_1"], $hauteur_EPI_AP_Parcours/2, ($info_nom_matiere),'',1,'L');
					}


					// On note l'ordonnée pour le nom des professeurs
					$Y_decal = $y_courant+($hauteur_EPI_AP_Parcours/2);
					$pdf->SetXY($param_bull2016["x_EPI_AP_Parcours"], $Y_decal);
					$pdf->SetFont('DejaVu','',8);

					// nom des professeurs
					if ( isset($tab_bull['eleve'][$i]['aid_e'][$m]['aid_prof_resp_login'][0]) )
					{

						// Présentation en ligne des profs
						// On n'a pas forcément le formatage choisi pour la classe...
						//$text_prof=$tab_bull['groupe'][$m]["profs"]["proflist_string"]."  ";
						$text_prof="";
						for($loop_prof_grp=0;$loop_prof_grp<count($tab_bull['eleve'][$i]['aid_e'][$m]['aid_prof_resp_login']);$loop_prof_grp++) {
							$tmp_login_prof=$tab_bull['eleve'][$i]['aid_e'][$m]['aid_prof_resp_login'][$loop_prof_grp];
							if($loop_prof_grp>0) {$text_prof.=", ";}
							$text_prof.=affiche_utilisateur($tmp_login_prof,$tab_bull['eleve'][$i]['id_classe']);
						}

						if($text_prof!="") {
							//$espace_matiere_prof = $espace_entre_matier/2;
							$espace_matiere_prof = $hauteur_EPI_AP_Parcours/2;
							$hauteur_caractere_prof = 7;

							$texte=$text_prof;
							$taille_max_police=$hauteur_caractere_prof;
							$taille_min_police=ceil($hauteur_caractere_prof/3);

							$largeur_dispo=$param_bull2016["largeur_EPI_AP_Parcours_col_1"];
							$h_cell=$espace_matiere_prof;

							$pdf->SetX($param_bull2016["x_EPI_AP_Parcours"]);

							cell_ajustee(($texte),$pdf->GetX(),$pdf->GetY(),$largeur_dispo,$h_cell,$taille_max_police,$taille_min_police,'');
						}
					}


					// Colonne 2 : Appréciation
					$pdf->SetFillColor($param_bull2016["couleur_EPI_alt".($cpt_matiere%2+1)]["R"], $param_bull2016["couleur_EPI_alt".($cpt_matiere%2+1)]["V"], $param_bull2016["couleur_EPI_alt".($cpt_matiere%2+1)]["B"]);
					$pdf->Rect($param_bull2016["x_EPI_AP_Parcours_col_2"], $y_courant, $param_bull2016["largeur_EPI_AP_Parcours_col_2"], $hauteur_EPI_AP_Parcours, 'F');

					$pdf->SetFillColor(0, 0, 0);
					$pdf->SetXY($param_bull2016["x_EPI_AP_Parcours_col_2"], $y_courant);

					// calcul de la taille du texte des appréciations
					$hauteur_caractere_appreciation = 8;
					$pdf->SetFont('DejaVu','',$hauteur_caractere_appreciation);

					//suppression des espaces en début et en fin
					//$app_aff = trim($tab_bull['app'][$m][$i]);
					$app_aff="";
					if($tab_bull['eleve'][$i]['aid_e'][$m]['message']!='') {
						$app_aff.=$tab_bull['eleve'][$i]['aid_e'][$m]['message'];
					}
					//if($app_aff!='') {$app_aff.=" ";}
					if(($tab_bull['eleve'][$i]['aid_e'][$m]['display_nom']=='y')&&($tab_bull['eleve'][$i]['aid_e'][$m]['aid_nom']!='')) {
						if($app_aff!='') {$app_aff.=" ";}
						$app_aff.=$tab_bull['eleve'][$i]['aid_e'][$m]['aid_nom'];
					}
					if($app_aff!='') {$app_aff.="\n";}
					$app_aff.=trim($tab_bull['eleve'][$i]['aid_e'][$m]['aid_appreciation']);

					fich_debug_bull("__________________________________________\n");
					fich_debug_bull("$app_aff\n");
					fich_debug_bull("__________________________________________\n");

					// DEBUT AJUSTEMENT TAILLE APPRECIATION
					$taille_texte_total = $pdf->GetStringWidth($app_aff);
					//$largeur_appreciation2 = $largeur_appreciation - $largeur_sous_matiere;
					$largeur_appreciation2=$param_bull2016["largeur_EPI_AP_Parcours_col_2"];

					if($use_cell_ajustee=="n") {
						//$taille_texte = (($espace_entre_matier/3)*$largeur_appreciation2);
						$nb_ligne_app = '2.8';
						//$nb_ligne_app = '3.8';
						//$nb_ligne_app = '4.8';
						$taille_texte_max = $nb_ligne_app * ($largeur_appreciation2-4);
						//$taille_texte_max = $nb_ligne_app * ($largeur_appreciation2);
						$grandeur_texte='test';

						fich_debug_bull("\$taille_texte_total=$taille_texte_total\n");
						fich_debug_bull("\$largeur_appreciation2=$largeur_appreciation2\n");
						fich_debug_bull("\$nb_ligne_app=$nb_ligne_app\n");
						//fich_debug_bull("\$taille_texte_max = \$nb_ligne_app * (\$largeur_appreciation2-4)=$nb_ligne_app * ($largeur_appreciation2-4)=$taille_texte_max\n");
						fich_debug_bull("\$taille_texte_max = \$nb_ligne_app * (\$largeur_appreciation2)=$nb_ligne_app * ($largeur_appreciation2)=$taille_texte_max\n");

						while($grandeur_texte!='ok') {
							if($taille_texte_max < $taille_texte_total)
							{
								$hauteur_caractere_appreciation = $hauteur_caractere_appreciation-0.3;
								//$hauteur_caractere_appreciation = $hauteur_caractere_appreciation-0.1;
								$pdf->SetFont('DejaVu','',$hauteur_caractere_appreciation);
								$taille_texte_total = $pdf->GetStringWidth($app_aff);
							}
							else {
								$grandeur_texte='ok';
							}
						}
						$grandeur_texte='test';
						$pdf->drawTextBox(($app_aff), $largeur_appreciation2, $hauteur_EPI_AP_Parcours, 'J', 'M', 1);
					}
					else {
						$texte=$app_aff;
						//$texte="Bla bla\nbli ".$app_aff;
						$taille_max_police=$hauteur_caractere_appreciation;
						$taille_min_police=ceil($taille_max_police/3);

						$largeur_dispo=$largeur_appreciation2;
						$h_cell=$hauteur_EPI_AP_Parcours;

						if(getSettingValue('suppr_balises_app_prof')=='y') {$texte=preg_replace('/<(.*)>/U','',$texte);}
						cell_ajustee(($texte),$pdf->GetX(),$pdf->GetY(),$largeur_dispo,$h_cell,$taille_max_police,$taille_min_police,'');
					}

					$y_courant+=$hauteur_EPI_AP_Parcours+0.5;
					$cpt_matiere++;


				}
				// Fin des AID_e tagués Parcours

			}

			$hauteur_prise_par_EPI_AP_Parcours_page_2=$y_courant;
			//=========================================
		}

		// 20161030
		$y_bandeau_communication_famille=$param_bull2016["y_bandeau_communication_famille"];
		$y_communication_famille=$param_bull2016["y_communication_famille"];
		$y_signature_chef=$param_bull2016["y_signature_chef"];
		$y_visa_famille=$param_bull2016["y_visa_famille"];
		if((isset($param_bull2016["bull2016_pas_espace_reserve_EPI_AP_Parcours"]))&&($param_bull2016["bull2016_pas_espace_reserve_EPI_AP_Parcours"]=="y")) {
			// On ne se limite pas au cas où il n'y a pas du tout d'EPI, AP,...
//			if($nb_EPI_AP_Parcours==0) {
				// Recalculer les ordonnées
				$y_bilan_acquisitions-=$y_bandeau_bilan_acquisitions-$hauteur_prise_par_EPI_AP_Parcours_page_2;

				$y_bandeau_communication_famille-=$y_bandeau_bilan_acquisitions-$hauteur_prise_par_EPI_AP_Parcours_page_2;
				$y_communication_famille-=$y_bandeau_bilan_acquisitions-$hauteur_prise_par_EPI_AP_Parcours_page_2;
				$y_signature_chef-=$y_bandeau_bilan_acquisitions-$hauteur_prise_par_EPI_AP_Parcours_page_2;
				$y_visa_famille-=$y_bandeau_bilan_acquisitions-$hauteur_prise_par_EPI_AP_Parcours_page_2;

				// On ne modifie la valeur de $y_bandeau_bilan_acquisitions qu'à la fin pour ne pas perturber/fausser les calculs ci-dessus
				$y_bandeau_bilan_acquisitions=$hauteur_prise_par_EPI_AP_Parcours_page_2;
//			}
		}

		//=========================================

		// Bandeau Bilan de l'acquisition des connaissances et compétences

		$pdf->SetFillColor($param_bull2016["couleur_bandeau_bilan_acquisitions"]["R"], $param_bull2016["couleur_bandeau_bilan_acquisitions"]["V"], $param_bull2016["couleur_bandeau_bilan_acquisitions"]["B"]);

		$pdf->Rect($param_bull2016["x_bandeau_bilan_acquisitions"], $y_bandeau_bilan_acquisitions, $param_bull2016["largeur_bandeau_bilan_acquisitions"], $param_bull2016["hauteur_bandeau_bilan_acquisitions"], 'F');

		$pdf->SetFillColor(0, 0, 0);
		$pdf->SetTextColor(255, 255, 255);
		$pdf->SetXY($param_bull2016["x_bandeau_bilan_acquisitions"], $y_bandeau_bilan_acquisitions+1);
		$pdf->SetFont('DejaVu','B',12);
		$pdf->Cell($param_bull2016["largeur_bandeau_bilan_acquisitions"],7, "Bilan de l'acquisition des connaissances et compétences",0,2,'C');



		// Cadre synthèse de l'évolution des acquis...

		$pdf->SetFillColor($param_bull2016["couleur_bilan_acquisitions"]["R"], $param_bull2016["couleur_bilan_acquisitions"]["V"], $param_bull2016["couleur_bilan_acquisitions"]["B"]);
		$pdf->Rect($param_bull2016["x_bilan_acquisitions"], $y_bilan_acquisitions, $param_bull2016["largeur_bilan_acquisitions"], $hauteur_bilan_acquisitions, 'F');

		$pdf->SetFillColor(0, 0, 0);
		$pdf->SetTextColor(0, 0, 0);
		$pdf->SetXY($param_bull2016["x_bilan_acquisitions"], $y_bilan_acquisitions+1);
		$pdf->SetFont('DejaVu','',9);
		$pdf->Cell($param_bull2016["largeur_bilan_acquisitions"],7, "Synthèse de l'évolution des acquis scolaires et conseils pour progresser :",0,2,'L');


		// Avis du conseil de classe

		$pdf->SetXY($param_bull2016["x_bilan_acquisitions"]+2.5, $y_bilan_acquisitions+5);

		$marge_droite_avis_cons=5;
		$pdf->SetFont('DejaVu','',10);
		$texteavis = $tab_bull['avis'][$i];

		if(($param_bull2016["affich_mentions"]=="y")||($param_bull2016["avec_coches_mentions"]=="y")) {
			if((!isset($tableau_des_mentions_sur_le_bulletin))||(!is_array($tableau_des_mentions_sur_le_bulletin))||(count($tableau_des_mentions_sur_le_bulletin)==0)) {
				$tableau_des_mentions_sur_le_bulletin=get_mentions($classe_id);
			}
			if(isset($tableau_des_mentions_sur_le_bulletin[$tab_bull['id_mention'][$i]])) {
				$textmention=$tableau_des_mentions_sur_le_bulletin[$tab_bull['id_mention'][$i]];
			}
			else {$textmention="-";}

			if($param_bull2016["avec_coches_mentions"]=="y") {
				if(count($tableau_des_mentions_sur_le_bulletin)>0) {
					$marge_droite_avis_cons=40;
				}
				else {
					$marge_droite_avis_cons=5;
				}
			}
			else {
				$marge_droite_avis_cons=5;
				if(($param_bull2016["affich_mentions"]!="n")&&($textmention!="")&&($textmention!="-")) {
					if($use_cell_ajustee=="n") {
						if($param_bull2016["affich_intitule_mentions"]!="n") {
							$texteavis.="\n".ucfirst($gepi_denom_mention)." : ";
						}
						$texteavis.=$textmention;
					}
					else {
						if($param_bull2016["affich_intitule_mentions"]!="n") {
							$texteavis.="\n"."<b>".ucfirst($gepi_denom_mention)." :</b> ";
						}
						$texteavis.=$textmention;
					}
				}
			}
		}

		if($use_cell_ajustee=="n") {
			$pdf->drawTextBox(($texteavis), $param_bull2016["largeur_bilan_acquisitions"]-$marge_droite_avis_cons, $hauteur_bilan_acquisitions-10, 'J', 'M', 0);
		}
		else {
			$texte=$texteavis;
			$taille_max_police=10;
			$taille_min_police=ceil($taille_max_police/3);

			$largeur_dispo=$param_bull2016["largeur_bilan_acquisitions"]-$marge_droite_avis_cons;
			$h_cell=$hauteur_bilan_acquisitions-10;

			cell_ajustee(($texte),$pdf->GetX(),$pdf->GetY(),$largeur_dispo,$h_cell,$taille_max_police,$taille_min_police,'');
		}


		/*
		$X_pp_aff=$param_bull2016["x_bilan_acquisitions"];

		//$Y_pp_aff=$tab_modele_pdf["Y_avis_cons"][$classe_id]+$tab_modele_pdf["hauteur_avis_cons"][$classe_id]-5;
		$Y_pp_aff=$y_bilan_acquisitions+$hauteur_avis_cons_init-5;

		$pdf->SetXY($X_pp_aff,$Y_pp_aff);
		if ( $tab_modele_pdf["taille_profprincipal_bloc_avis_conseil"][$classe_id] != '' and is_numeric($tab_modele_pdf["taille_profprincipal_bloc_avis_conseil"][$classe_id]) and $tab_modele_pdf["taille_profprincipal_bloc_avis_conseil"][$classe_id]>0 and $tab_modele_pdf["taille_profprincipal_bloc_avis_conseil"][$classe_id] < '15' ) {
			$taille = $tab_modele_pdf["taille_profprincipal_bloc_avis_conseil"][$classe_id];
		} else {
			$taille = '10';
		}
		$pdf->SetFont('DejaVu','I',$taille);
		// Le nom du professeur principal
		$pp_classe[$i]="";
		//if(isset($tab_bull['eleve'][$i]['pp']['login'])) {
		if($tab_modele_pdf["afficher_tous_profprincipaux"][$classe_id]==1) {
			$index_pp='pp_classe';
		}
		else {
			$index_pp='pp';
		}
		if(isset($tab_bull['eleve'][$i][$index_pp][0]['login'])) {
			$pp_classe[$i]="<b>".ucfirst($tab_bull['gepi_prof_suivi'])."</b> : ";
			$pp_classe[$i].="<i>".affiche_utilisateur($tab_bull['eleve'][$i][$index_pp][0]['login'],$tab_bull['eleve'][$i]['id_classe'])."</i>";
			for($i_pp=1;$i_pp<count($tab_bull['eleve'][$i][$index_pp]);$i_pp++) {
				$pp_classe[$i].=", ";
				$pp_classe[$i].="<i>".affiche_utilisateur($tab_bull['eleve'][$i][$index_pp][$i_pp]['login'],$tab_bull['eleve'][$i]['id_classe'])."</i>";
			}
		}
		else {
			$pp_classe[$i]="";
		}
		//$pdf->MultiCellTag(200, 5, ($pp_classe[$i]), '', 'J', '');
		//$pdf->ext_MultiCellTag(200, 5, ($pp_classe[$i]), '', 'J', '');

		$taille_max_police=$taille;
		$taille_min_police=ceil($taille_max_police/3);
		//$largeur_dispo=200;
		$largeur_dispo=$tab_modele_pdf["longeur_avis_cons"][$classe_id];
		$h_cell=5;
		cell_ajustee($pp_classe[$i],$pdf->GetX(),$pdf->GetY(),$largeur_dispo,$h_cell,$taille_max_police,$taille_min_police,'');
		*/


		if($param_bull2016["avec_coches_mentions"]=="y") {
			$pdf->SetFont('DejaVu','',9);
			$X_pp_aff=$param_bull2016["x_bilan_acquisitions"]+$param_bull2016["largeur_bilan_acquisitions"]-35;
			$Y_pp_aff=$y_bilan_acquisitions+5;
			$pdf->SetXY($X_pp_aff,$Y_pp_aff);

			if((!isset($tableau_des_mentions_sur_le_bulletin))||(!is_array($tableau_des_mentions_sur_le_bulletin))||(count($tableau_des_mentions_sur_le_bulletin)==0)) {
				$tableau_des_mentions_sur_le_bulletin=get_mentions($classe_id);
			}

			$loop_mention=0;
			foreach($tableau_des_mentions_sur_le_bulletin as $key_mention => $value_mention) {
				$pdf->Cell(35,4, $value_mention,0,2,'L');
				$loop_mention++;
			}

			$loop_mention=0;
			foreach($tableau_des_mentions_sur_le_bulletin as $key_mention => $value_mention) {
				$pdf->Rect($X_pp_aff+30, $Y_pp_aff+4*$loop_mention+0.3, 2.4, 3);

				if($key_mention==$tab_bull['id_mention'][$i]) {
					$pdf->SetXY($X_pp_aff-1.73,$Y_pp_aff+$loop_mention*4);
					$pdf->Cell(35,4, 'X',0,2,'R');
				}
				$loop_mention++;
			}
			$pdf->Rect($X_pp_aff, $Y_pp_aff+0.1, 0.01, $loop_mention*4);
		}


		//=========================================

		// Bandeau Communication avec la famille

		$pdf->SetFillColor($param_bull2016["couleur_bandeau_communication_famille"]["R"], $param_bull2016["couleur_bandeau_communication_famille"]["V"], $param_bull2016["couleur_bandeau_communication_famille"]["B"]);

		$pdf->Rect($param_bull2016["x_bandeau_communication_famille"], $y_bandeau_communication_famille, $param_bull2016["largeur_bandeau_communication_famille"], $param_bull2016["hauteur_bandeau_communication_famille"], 'F');

		$pdf->SetFillColor(0, 0, 0);
		$pdf->SetTextColor(255, 255, 255);
		$pdf->SetXY($param_bull2016["x_bandeau_communication_famille"], $y_bandeau_communication_famille+1);
		$pdf->SetFont('DejaVu','B',12);
		$pdf->Cell($param_bull2016["largeur_bandeau_communication_famille"],7, "Communication avec la famille",0,2,'C');

		// Cadre Vie scolaire

		$pdf->SetFillColor($param_bull2016["couleur_communication_famille"]["R"], $param_bull2016["couleur_communication_famille"]["V"], $param_bull2016["couleur_communication_famille"]["B"]);

		$pdf->Rect($param_bull2016["x_communication_famille"], $y_communication_famille, $param_bull2016["largeur_communication_famille"], $param_bull2016["hauteur_communication_famille"], 'F');

		$pdf->SetFillColor(0, 0, 0);
		$pdf->SetTextColor(0, 0, 0);
		$pdf->SetXY($param_bull2016["x_communication_famille"], $y_communication_famille);
		$pdf->SetFont('DejaVu','',9);
		//$pdf->Cell($param_bull2016["largeur_bilan_acquisitions"],$param_bull2016["y_bilan_acquisitions_cycle_3"], "Synthèse de l'évolution des acquis scolaires et conseils pour progresser :",0,2,'L');
		//$pdf->Cell($param_bull2016["largeur_communication_famille"],10, "Vie scolaire (assiduité, ponctualité ; respect du règlement intérieur ; pariticipation à la vie de l'établissement) :",0,2,'L');
		//$pdf->Cell(90, 10, "Vie scolaire (assiduité, ponctualité ; respect du règlement intérieur ; participation à la vie de l'établissement) :",0,2,'L');
		$texte="Vie scolaire (assiduité, ponctualité ; respect du règlement intérieur ;\n participation à la vie de l'établissement) :";
		$taille_max_police=9;
		$taille_min_police=ceil($taille_max_police/3);
		$largeur_dispo=$param_bull2016["largeur_communication_famille"];
		$h_cell=10;
		//if(getSettingValue('suppr_balises_app_prof')=='y') {$texte=preg_replace('/<(.*)>/U','',$texte);}
		cell_ajustee($texte,$pdf->GetX(),$pdf->GetY(),$largeur_dispo,$h_cell,$taille_max_police,$taille_min_police,'');


		//++++++++++++++++++++++++++++++++++++++++++++
		// Vérifications/ajustements de hauteur

		$h_ligne_retard_abs=3.5;
		$hauteur_lignes_absences_retards=3.5;
		// Heures perdues
		if($param_bull2016["bull2016_afficher_nb_heures_perdues"]=="y") {
			$hauteur_lignes_absences_retards+=$h_ligne_retard_abs;
		}

		// Non justifiées
		if($param_bull2016["bull2016_aff_abs_nj"]=="y") {
			$hauteur_lignes_absences_retards+=$h_ligne_retard_abs;
		}

		// Justifiées
		if($param_bull2016["bull2016_aff_abs_justifiees"]=="y") {
			$hauteur_lignes_absences_retards+=$h_ligne_retard_abs;
		}
		//elseif($param_bull2016["bull2016_aff_total_abs"]=="y") {
		if($param_bull2016["bull2016_aff_total_abs"]=="y") {
			$hauteur_lignes_absences_retards+=$h_ligne_retard_abs;
		}

		if($param_bull2016["bull2016_aff_retards"]=="y") {
			$hauteur_lignes_absences_retards+=$h_ligne_retard_abs;
		}


		$hauteur_orientation=0;
		if((getSettingAOui('active_mod_orientation'))&&(mef_avec_proposition_orientation($classe_id))) {
			/*
			echo "\$tab_bull['eleve'][$i]['mef_code']=".$tab_bull['eleve'][$i]['mef_code']."<br />";
			echo "<pre>";
			print_r($tab_bull['orientation']['mef_avec_orientation']);
			echo "</pre>";
			*/
			$tmp_tab_periode_orientation=explode(";", $param_bull2016["bull2016_orientation_periodes"]);
			if((in_array($tab_bull['num_periode'], $tmp_tab_periode_orientation))&&(isset($tab_bull['orientation']['mef_avec_orientation']))&&(in_array($tab_bull['eleve'][$i]['mef_code'], $tab_bull['orientation']['mef_avec_orientation']))) {
				$hauteur_orientation=$param_bull2016["hauteur_cadre_orientation"];
			}
		}

		//if($tab_bull['eleve'][$i]['appreciation_absences'] != "") {
			$hauteur_restant_pour_appreciation_absences=$param_bull2016["hauteur_communication_famille"]-10-$hauteur_lignes_absences_retards-$hauteur_orientation;
			if($hauteur_restant_pour_appreciation_absences<10) {
				$hauteur_restant_pour_appreciation_absences=10;
				if($param_bull2016["hauteur_communication_famille"]-10-$hauteur_lignes_absences_retards-10>=10) {
					$hauteur_orientation=$param_bull2016["hauteur_communication_famille"]-10-$hauteur_lignes_absences_retards-10;
				}
				// Sinon, on va avoir un souci
			}
		//}

		$y_lignes_absences_et_retards=$y_communication_famille+$param_bull2016["hauteur_communication_famille"]-$hauteur_orientation-$hauteur_lignes_absences_retards;
		$y_cadre_orientation=$y_communication_famille+$param_bull2016["hauteur_communication_famille"]-$hauteur_orientation-1;
		$y_cadre_orientation-=$hauteur_lignes_absences_retards;
		//++++++++++++++++++++++++++++++++++++++++++++


		$largeur_dispo_app_abs=$param_bull2016["largeur_communication_famille"];
		if((isset($tab_bull['eleve'][$i]['engagements']["id_engagement"]))&&(count($tab_bull['eleve'][$i]['engagements']["id_engagement"])>0)) {

			$texte="";
			for($loop_eng=0;$loop_eng<count($tab_bull['eleve'][$i]['engagements']["indice"]);$loop_eng++) {
				// A paramétrer dans param_bull_pdf_2016.php
				// Tester si $tab_bull['eleve'][$i]['engagements']["indice"][$loop_eng][...] est dans la liste des engagements que l'on veut afficher sur les bulletins.
				if(in_array($tab_bull['eleve'][$i]['engagements']["indice"][$loop_eng]["id_engagement"], $param_bull2016["bull2016_afficher_engagements_id"])) {
					$texte.=$tab_bull['eleve'][$i]['engagements']["indice"][$loop_eng]["nom_engagement"]."\n";
				}
			}
			if($texte!="") {
				$texte="<b>Engagements :</b>\n".$texte;

				// A paramétrer dans param_bull_pdf_2016.php
				$largeur_engagement=30;

				$pdf->SetXY($param_bull2016["x_communication_famille"]+$largeur_dispo_app_abs-$largeur_engagement, $y_communication_famille+10);
				$pdf->SetFont('DejaVu','',8);

				/*
				$val = $pdf->GetStringWidth($info_absence_appreciation);
				// nombre de lignes que prend la remarque cpe
				//Arrondi à l'entier supérieur : ceil()
				$nb_ligne = 1;
				$nb_ligne = ceil($val / 200);
				$hauteur_pris = $nb_ligne * 3;
				*/

				$taille_max_police=8;
				$taille_min_police=ceil($taille_max_police/3);
				$largeur_dispo=$largeur_engagement;
				$h_cell=$hauteur_restant_pour_appreciation_absences;
				cell_ajustee($texte,$pdf->GetX(),$pdf->GetY(),$largeur_dispo,$h_cell,$taille_max_police,$taille_min_police,'');

				$largeur_dispo_app_abs-=$largeur_engagement;
				$largeur_dispo_app_abs-=3; // et 3mm de marge horizontale
			}
		}

		if($tab_bull['eleve'][$i]['appreciation_absences'] != "")
		{
			// supprimer les espaces
			//$text_absences_appreciation = trim(str_replace(array("\r\n","\r","\n"), ' ', unhtmlentities($tab_bull['eleve'][$i]['appreciation_absences'])));
			$text_absences_appreciation = trim(unhtmlentities($tab_bull['eleve'][$i]['appreciation_absences']));
			$info_absence_appreciation=$text_absences_appreciation;

			$pdf->SetXY($param_bull2016["x_communication_famille"], $y_communication_famille+10);
			$pdf->SetFont('DejaVu','',8);
			$val = $pdf->GetStringWidth($info_absence_appreciation);
			// nombre de lignes que prend la remarque cpe
			//Arrondi à l'entier supérieur : ceil()
			$nb_ligne = 1;
			$nb_ligne = ceil($val / 200);
			$hauteur_pris = $nb_ligne * 3;

			$taille_max_police=8;
			$taille_min_police=ceil($taille_max_police/3);
			$largeur_dispo=$largeur_dispo_app_abs;
			//$h_cell=22; // A ajuster selon ce qu'on affiche des retards, nj, j,...
			$h_cell=$hauteur_restant_pour_appreciation_absences;
			cell_ajustee($info_absence_appreciation,$pdf->GetX(),$pdf->GetY(),$largeur_dispo,$h_cell,$taille_max_police,$taille_min_police,'');
		}

		/*
		// Identité CPE
			if(($tab_modele_pdf["afficher_abs_cpe"][$classe_id]=='1')&&(isset($tab_bull['eleve'][$i]['cperesp_civilite']))&&(isset($tab_bull['eleve'][$i]['cperesp_login']))) {
				$pdf->SetFont('DejaVu','',8);
				// C.P.E.
				$info_absence = $info_absence." (".ucfirst($gepi_cpe_suivi)." chargé";
				if($tab_bull['eleve'][$i]['cperesp_civilite']!="M.") {
					$info_absence = $info_absence."e";
				}
				$info_absence = $info_absence." du suivi : <i>".affiche_utilisateur($tab_bull['eleve'][$i]['cperesp_login'],$tab_bull['id_classe'])."</i>)";
			}
			//$pdf->MultiCellTag($tab_modele_pdf["largeur_cadre_absences"][$classe_id], 5, ($info_absence), '', 'J', '');
			//$pdf->ext_MultiCellTag($tab_modele_pdf["largeur_cadre_absences"][$classe_id], 5, $info_absence, '', 'J', '');

			$taille_max_police=8;
			$taille_min_police=ceil($taille_max_police/3);
			$largeur_dispo=$tab_modele_pdf["largeur_cadre_absences"][$classe_id];
			$h_cell=5;
			cell_ajustee($info_absence,$pdf->GetX(),$pdf->GetY(),$largeur_dispo,$h_cell,$taille_max_police,$taille_min_police,'');
		*/



		if((getSettingAOui('active_mod_orientation'))&&(mef_avec_proposition_orientation($classe_id))) {

			$tmp_tab_periode_orientation=explode(";", $param_bull2016["bull2016_orientation_periodes"]);
			if((in_array($tab_bull['num_periode'], $tmp_tab_periode_orientation))&&(isset($tab_bull['orientation']['mef_avec_orientation']))&&(in_array($tab_bull['eleve'][$i]['mef_code'], $tab_bull['orientation']['mef_avec_orientation']))) {


				//$y_corrigee_cadre_orientation=$tab_modele_pdf["Y_cadre_orientation"][$classe_id]+$hauteur_pris_app_abs;
				//$y_corrigee_cadre_orientation=$param_bull2016["Y_cadre_orientation"];
				$y_corrigee_cadre_orientation=$y_cadre_orientation;

				// Pour avoir une petite marge haute sur les listes de voeux/orientations dans le cadre orientation
				$padding_haut_orientation=1;

				//$pdf->Rect($param_bull2016["X_cadre_orientation"], $y_corrigee_cadre_orientation, $param_bull2016["largeur_cadre_orientation"], $param_bull2016["hauteur_cadre_orientation"], 'D');
				$pdf->Rect($param_bull2016["X_cadre_orientation"], $y_corrigee_cadre_orientation, $param_bull2016["largeur_cadre_orientation"], $hauteur_orientation, 'D');

				if($param_bull2016["cadre_voeux_orientation"]!=0) {

					$largeur_cadre_voeux=$param_bull2016["largeur_cadre_orientation"];
					if($param_bull2016["cadre_orientation_proposee"]!=0) {

						if($param_bull2016["X_cadre_orientation_proposee"]>$param_bull2016["X_cadre_voeux_orientation"]) {
							$largeur_cadre_voeux=$param_bull2016["X_cadre_orientation_proposee"]-$param_bull2016["X_cadre_voeux_orientation"];
						}
						else {
							$largeur_cadre_voeux=$param_bull2016["largeur_cadre_orientation"]-($param_bull2016["X_cadre_voeux_orientation"]-$param_bull2016["X_cadre_orientation_proposee"]);
						}
					}

					//$pdf->Rect($param_bull2016["X_cadre_voeux_orientation"], $y_corrigee_cadre_orientation, $largeur_cadre_voeux, $param_bull2016["hauteur_cadre_orientation"], 'D');
					$pdf->Rect($param_bull2016["X_cadre_voeux_orientation"], $y_corrigee_cadre_orientation, $largeur_cadre_voeux, $hauteur_orientation, 'D');

					$pdf->SetXY($param_bull2016["X_cadre_voeux_orientation"],$y_corrigee_cadre_orientation);
					$pdf->SetFont('DejaVu','B', $param_bull2016["bull2016_orientation_taille_police"]);
					$chaine_titre_voeux=$param_bull2016["titre_voeux_orientation"]." : ";
					$largeur_chaine_titre_voeux=$pdf->GetStringWidth($chaine_titre_voeux);
					$pdf->Cell($largeur_chaine_titre_voeux,5, $chaine_titre_voeux,0,2,'');

					// Liste des voeux (pouvoir limiter aux N premiers voeux)
					$pdf->SetXY($param_bull2016["X_cadre_voeux_orientation"]+$largeur_chaine_titre_voeux, $y_corrigee_cadre_orientation+$padding_haut_orientation);

					if($use_cell_ajustee=="n") {
						$texte_voeux="";
						if(isset($tab_bull['orientation']['voeux'][$tab_bull['eleve'][$i]['login']])) {
							for($loop_voeu=1;$loop_voeu<=count($tab_bull['orientation']['voeux'][$tab_bull['eleve'][$i]['login']]);$loop_voeu++) {
								$texte_voeux.=$loop_voeu.". ".$tab_bull['orientation']['voeux'][$tab_bull['eleve'][$i]['login']][$loop_voeu]['designation'];
								if(($tab_bull['orientation']['voeux'][$tab_bull['eleve'][$i]['login']][$loop_voeu]['commentaire']!="")&&($tab_bull['orientation']['voeux'][$tab_bull['eleve'][$i]['login']][$loop_voeu]['commentaire']!=$tab_bull['orientation']['voeux'][$tab_bull['eleve'][$i]['login']][$loop_voeu]['designation'])) {
									$texte_voeux.=" (".$tab_bull['orientation']['voeux'][$tab_bull['eleve'][$i]['login']][$loop_voeu]['commentaire'].")";
								}
								$texte_voeux.="\n";
							}
						}

						//$pdf->drawTextBox(($texte_voeux), $largeur_cadre_voeux-$largeur_chaine_titre_voeux, $param_bull2016["hauteur_cadre_orientation"], 'J', 'M', 0);
						$pdf->drawTextBox(($texte_voeux), $largeur_cadre_voeux-$largeur_chaine_titre_voeux, $hauteur_orientation, 'J', 'M', 0);
					}
					else {
						$texte_voeux="";
						if(isset($tab_bull['orientation']['voeux'][$tab_bull['eleve'][$i]['login']])) {
							for($loop_voeu=1;$loop_voeu<=count($tab_bull['orientation']['voeux'][$tab_bull['eleve'][$i]['login']]);$loop_voeu++) {
								$texte_voeux.="<b>".$loop_voeu.".</b> ".$tab_bull['orientation']['voeux'][$tab_bull['eleve'][$i]['login']][$loop_voeu]['designation'];
								if(($tab_bull['orientation']['voeux'][$tab_bull['eleve'][$i]['login']][$loop_voeu]['commentaire']!="")&&($tab_bull['orientation']['voeux'][$tab_bull['eleve'][$i]['login']][$loop_voeu]['commentaire']!=$tab_bull['orientation']['voeux'][$tab_bull['eleve'][$i]['login']][$loop_voeu]['designation'])) {
									$texte_voeux.=" (".$tab_bull['orientation']['voeux'][$tab_bull['eleve'][$i]['login']][$loop_voeu]['commentaire'].")";
								}
								$texte_voeux.="\n";
							}
						}

						$texte=$texte_voeux;
						$taille_max_police=$param_bull2016["bull2016_orientation_taille_police"];
						$taille_min_police=ceil($taille_max_police/3);

						$largeur_dispo=$largeur_cadre_voeux-$largeur_chaine_titre_voeux;
						//$h_cell=$param_bull2016["hauteur_cadre_orientation"]-$padding_haut_orientation;
						$h_cell=$hauteur_orientation-$padding_haut_orientation;

						cell_ajustee(($texte),$pdf->GetX(),$pdf->GetY(),$largeur_dispo,$h_cell,$taille_max_police,$taille_min_police,'','T');
					}
				}

				// Orientations proposées
				if($param_bull2016["cadre_orientation_proposee"]!=0) {
					$largeur_cadre_orientation_proposee=$param_bull2016["largeur_cadre_orientation"];
					if($param_bull2016["cadre_voeux_orientation"]!=0) {

						if($param_bull2016["X_cadre_orientation_proposee"]>$param_bull2016["X_cadre_voeux_orientation"]) {
							$largeur_cadre_orientation_proposee=$param_bull2016["largeur_cadre_orientation"]-($param_bull2016["X_cadre_orientation_proposee"]-$param_bull2016["X_cadre_voeux_orientation"]);
						}
						else {
							$largeur_cadre_orientation_proposee=$param_bull2016["X_cadre_voeux_orientation"]-$param_bull2016["X_cadre_orientation_proposee"];
						}
					}

					//$pdf->Rect($param_bull2016["X_cadre_orientation_proposee"], $y_corrigee_cadre_orientation, $largeur_cadre_orientation_proposee, $param_bull2016["hauteur_cadre_orientation"], 'D');
					$pdf->Rect($param_bull2016["X_cadre_orientation_proposee"], $y_corrigee_cadre_orientation, $largeur_cadre_orientation_proposee, $hauteur_orientation, 'D');

					$pdf->SetXY($param_bull2016["X_cadre_orientation_proposee"],$y_corrigee_cadre_orientation);
					$pdf->SetFont('DejaVu','B',$param_bull2016["bull2016_orientation_taille_police"]);
					//$pdf->Cell(50,5, $param_bull2016["titre_orientation_proposee"]." : ",0,2,'');
					$chaine_titre_orientations_proposees=$param_bull2016["titre_orientation_proposee"]." : ";
					$largeur_chaine_titre_orientations_proposees=$pdf->GetStringWidth($chaine_titre_orientations_proposees);
					$pdf->Cell($chaine_titre_orientations_proposees,5, $chaine_titre_orientations_proposees,0,2,'');

					$chaine_titre_avis_orientations_proposees=$param_bull2016["titre_avis_orientation_proposee"];

					// Liste des orientations proposées (pouvoir limiter aux N premières)
					$pdf->SetXY($param_bull2016["X_cadre_orientation_proposee"]+$largeur_chaine_titre_orientations_proposees, $y_corrigee_cadre_orientation+$padding_haut_orientation);

					if($use_cell_ajustee=="n") {
						$texte_orientations_proposees="";
						if(isset($tab_bull['orientation']['orientation_proposee'][$tab_bull['eleve'][$i]['login']])) {
							for($loop_op=1;$loop_op<=count($tab_bull['orientation']['orientation_proposee'][$tab_bull['eleve'][$i]['login']]);$loop_op++) {
								$texte_orientations_proposees.=$loop_op.". ".$tab_bull['orientation']['orientation_proposee'][$tab_bull['eleve'][$i]['login']][$loop_op]['designation'];
								if(($tab_bull['orientation']['orientation_proposee'][$tab_bull['eleve'][$i]['login']][$loop_op]['commentaire']!="")&&($tab_bull['orientation']['orientation_proposee'][$tab_bull['eleve'][$i]['login']][$loop_op]['commentaire']!=$tab_bull['orientation']['orientation_proposee'][$tab_bull['eleve'][$i]['login']][$loop_op]['designation'])) {
									$texte_orientations_proposees.=" (".$tab_bull['orientation']['orientation_proposee'][$tab_bull['eleve'][$i]['login']][$loop_op]['commentaire'].")";
								}
								$texte_orientations_proposees.="\n";
							}
						}

						if((isset($tab_bull['orientation']['avis'][$tab_bull['eleve'][$i]['login']]))&&($tab_bull['orientation']['avis'][$tab_bull['eleve'][$i]['login']]!="")) {
							$texte_orientations_proposees.=$chaine_titre_avis_orientations_proposees." : ".preg_replace("#<br />#i", "", $tab_bull['orientation']['avis'][$tab_bull['eleve'][$i]['login']]);
						}

						//$pdf->drawTextBox(($texte_orientations_proposees), $largeur_cadre_orientation_proposee-$largeur_chaine_titre_orientations_proposees, $param_bull2016["hauteur_cadre_orientation"], 'J', 'M', 0);
						$pdf->drawTextBox(($texte_orientations_proposees), $largeur_cadre_orientation_proposee-$largeur_chaine_titre_orientations_proposees, $hauteur_orientation, 'J', 'M', 0);
					}
					else {
						$texte_orientations_proposees="";
						if(isset($tab_bull['orientation']['orientation_proposee'][$tab_bull['eleve'][$i]['login']])) {
							for($loop_op=1;$loop_op<=count($tab_bull['orientation']['orientation_proposee'][$tab_bull['eleve'][$i]['login']]);$loop_op++) {
								$texte_orientations_proposees.="<b>".$loop_op.".</b> ".$tab_bull['orientation']['orientation_proposee'][$tab_bull['eleve'][$i]['login']][$loop_op]['designation'];
								if(($tab_bull['orientation']['orientation_proposee'][$tab_bull['eleve'][$i]['login']][$loop_op]['commentaire']!="")&&($tab_bull['orientation']['orientation_proposee'][$tab_bull['eleve'][$i]['login']][$loop_op]['commentaire']!=$tab_bull['orientation']['orientation_proposee'][$tab_bull['eleve'][$i]['login']][$loop_op]['designation'])) {
									$texte_orientations_proposees.=" (".$tab_bull['orientation']['orientation_proposee'][$tab_bull['eleve'][$i]['login']][$loop_op]['commentaire'].")";
								}
								$texte_orientations_proposees.="\n";
							}
						}

						if((isset($tab_bull['orientation']['avis'][$tab_bull['eleve'][$i]['login']]))&&($tab_bull['orientation']['avis'][$tab_bull['eleve'][$i]['login']]!="")) {
							$texte_orientations_proposees.="<b>".$chaine_titre_avis_orientations_proposees." :</b> ".preg_replace("#<br />#i", "", $tab_bull['orientation']['avis'][$tab_bull['eleve'][$i]['login']]);
						}

						$texte=$texte_orientations_proposees;
						$taille_max_police=$param_bull2016["bull2016_orientation_taille_police"];
						$taille_min_police=ceil($taille_max_police/3);

						//$largeur_dispo=$largeur_cadre_orientation_proposee-($pdf->GetX()-$param_bull2016["X_cadre_orientation_proposee"]);
						$largeur_dispo=$largeur_cadre_orientation_proposee-$largeur_chaine_titre_orientations_proposees;
						//$h_cell=$param_bull2016["hauteur_cadre_orientation"]-$padding_haut_orientation;
						$h_cell=$hauteur_orientation-$padding_haut_orientation;

						cell_ajustee(($texte),$pdf->GetX(),$pdf->GetY(),$largeur_dispo,$h_cell,$taille_max_police,$taille_min_police,'','T');
					}

				}

			}

		}


		// On commence par la ligne le plus en bas parmi les lignes absences et on inscrit ensuite, si elles sont demandées à l'affichage, les lignes au-dessus une à une
		$h_ligne_retard_abs=3.5;
		$decal=3.5*3;

		//$y_lignes_absences_et_retards_2=$y_lignes_absences_et_retards;
		$y_lignes_absences_et_retards_2=$y_communication_famille+$param_bull2016["hauteur_communication_famille"]-5*$h_ligne_retard_abs;

//		$pdf->SetXY(5, $y_lignes_absences_et_retards+$decal);
//		$pdf->Cell(10,1, "",0,2,'L','LRBT');

		// Heures perdues
		if($param_bull2016["bull2016_afficher_nb_heures_perdues"]=="y") {
			$pdf->SetXY($param_bull2016["x_communication_famille"], $y_lignes_absences_et_retards_2+$decal);
			$pdf->SetFont('DejaVu','',8);
			$pdf->Cell($param_bull2016["largeur_communication_famille"],7, "Nombre d'heures de cours manquées du fait de ses absences, justifiées ou non justifiées : "."       heure(s)",0,2,'L');
			$decal-=3.5;
		}

//		$pdf->SetXY(20, $y_lignes_absences_et_retards+$decal);
//		$pdf->Cell(10,1, "",0,2,'L','LRBT');

		// Non justifiées
		if($param_bull2016["bull2016_aff_abs_nj"]=="y") {
			$nb_nj=$tab_bull['eleve'][$i]['eleve_nj'];
			$s="";
			if("$nb_nj"=="?") {
				$s="s";
			}
			elseif($nb_nj<=1) {
				$s="";
			}
			elseif($nb_nj>=1) {
				$s="s";
			}
			$pdf->SetXY($param_bull2016["x_communication_famille"], $y_lignes_absences_et_retards_2+$decal);
			$pdf->SetFont('DejaVu','',8);
			$pdf->Cell($param_bull2016["largeur_communication_famille"],7, "Absences non justifiées par les responsables légaux : ".$nb_nj." demi-journée".$s,0,2,'L');
			$decal-=3.5;
		}

//		$pdf->SetXY(35, $y_lignes_absences_et_retards+$decal);
//		$pdf->Cell(10,1, "",0,2,'L','LRBT');


		// Justifiées
		if($param_bull2016["bull2016_aff_abs_justifiees"]=="y") {
			if((is_int($tab_bull['eleve'][$i]['eleve_absences']))&&(is_int($tab_bull['eleve'][$i]['eleve_nj']))) {
				$nb_j=$tab_bull['eleve'][$i]['eleve_absences']-$tab_bull['eleve'][$i]['eleve_nj'];
				$s="";
				if($nb_j<=1) {
					$s="";
				}
				elseif($nb_j>=1) {
					$s="s";
				}
			}
			else {
				$nb_j='?';
				$s="s";
			}
			$pdf->SetXY($param_bull2016["x_communication_famille"], $y_lignes_absences_et_retards_2+$decal);
			$pdf->SetFont('DejaVu','',8);
			$pdf->Cell($param_bull2016["largeur_communication_famille"],7, "Absences justifiées par les responsables légaux : ".$nb_j." demi-journée".$s,0,2,'L');
			$decal-=3.5;
		}

//		$pdf->SetXY(50, $y_lignes_absences_et_retards+$decal);
//		$pdf->Cell(10,1, "",0,2,'L','LRBT');

		//elseif($param_bull2016["bull2016_aff_total_abs"]=="y") {
		if($param_bull2016["bull2016_aff_total_abs"]=="y") {
			$nb_j=$tab_bull['eleve'][$i]['eleve_absences'];
			$s="";
			if("$nb_j"=="?") {
				$s="s";
			}
			elseif($nb_j<=1) {
				$s="";
			}
			elseif($nb_j>=1) {
				$s="s";
			}
			$pdf->SetXY($param_bull2016["x_communication_famille"], $y_lignes_absences_et_retards_2+$decal);
			$pdf->SetFont('DejaVu','',8);
			$pdf->Cell($param_bull2016["largeur_communication_famille"],7, "Total des absences : ".$nb_j." demi-journée".$s,0,2,'L');
			$decal-=3.5;
		}

//		$pdf->SetXY(65, $y_lignes_absences_et_retards+$decal);
//		$pdf->Cell(10,1, "",0,2,'L','LRBT');

		if($param_bull2016["bull2016_aff_retards"]=="y") {
			$pdf->SetXY($param_bull2016["x_communication_famille"], $y_lignes_absences_et_retards_2+$decal);
			$pdf->SetFont('DejaVu','',8);
			$pdf->Cell($param_bull2016["largeur_communication_famille"],7, "Retards : ".$tab_bull['eleve'][$i]['eleve_retards'],0,2,'L');
		}


		// 20190701
		// Cadre chef étab

		$pdf->SetFillColor($param_bull2016["couleur_communication_famille"]["R"], $param_bull2016["couleur_communication_famille"]["V"], $param_bull2016["couleur_communication_famille"]["B"]);
		$pdf->Rect($param_bull2016["x_signature_chef"], $y_signature_chef, $param_bull2016["largeur_signature_chef"], $param_bull2016["hauteur_signature_chef"], 'F');

		$pdf->SetXY($param_bull2016["x_signature_chef"], $y_signature_chef);
		$pdf->SetFillColor(0, 0, 0);
		$pdf->SetTextColor(0, 0, 0);
		//$pdf->Cell($param_bull2016["largeur_signature_chef"],7, "Date, nom et signature du chef d'établissement",0,2,'L');
		$pdf->drawTextBox("Date, nom et signature\ndu chef de l'établissement", $param_bull2016["largeur_signature_chef"], $param_bull2016["hauteur_signature_chef"], 'R', 'T', 0);


		$pdf->SetXY($param_bull2016["x_signature_chef"], $y_signature_chef);

		if((isset($signature_bull[$tab_bull['id_classe']]))&&($signature_bull[$tab_bull['id_classe']]!="")&&(file_exists($signature_bull[$tab_bull['id_classe']]))) {
			$fich_sign=$signature_bull[$tab_bull['id_classe']];

			$X_sign = $param_bull2016["x_signature_chef"];
			$Y_sign = $y_signature_chef;

			$largeur_dispo=$param_bull2016["largeur_signature_chef"]-10;
			// On ajuste mieux la hauteur de l'image, quitte à ce que le tampon/signature soit en surimpression (ou plutôt sous-impression) avec le Nom du chef en première ligne du cadre.
			$hauteur_dispo=$param_bull2016["hauteur_signature_chef"]-2;

			$tmp_dim_photo=getimagesize($fich_sign);
			$ratio_l=$tmp_dim_photo[0]/$largeur_dispo;
			$ratio_h=$tmp_dim_photo[1]/$hauteur_dispo;
			if($ratio_l>$ratio_h) {
				$L_sign = $largeur_dispo;
				$H_sign = $largeur_dispo*$tmp_dim_photo[1]/$tmp_dim_photo[0];
			}
			else {
				$H_sign = $hauteur_dispo;
				$L_sign = $hauteur_dispo*$tmp_dim_photo[0]/$tmp_dim_photo[1];
			}

			/*
			echo "\$X_sign=$X_sign<br />\n";
			echo "\$Y_sign=$Y_sign<br />\n";
			echo "\$L_sign=$L_sign<br />\n";
			echo "\$H_sign=$H_sign<br />\n";
			*/

			$X_sign += ($param_bull2016["largeur_signature_chef"]-$L_sign) / 2;
			$Y_sign += ($param_bull2016["hauteur_signature_chef"]-$H_sign) / 2;

			$tmp_dim_photo=getimagesize($fich_sign);

			if((isset($tmp_dim_photo[2]))&&($tmp_dim_photo[2]==2)) {
				//$pdf->Image($fich_sign, $X_sign, $Y_sign, $L_sign, $H_sign);
				$pdf->Image($fich_sign, round($X_sign), round($Y_sign), round($L_sign), round($H_sign));
			}
		}

		$pdf->SetFont('DejaVu','',10);
		if($param_bull2016["affichage_haut_responsable"]=='y') {
			/*
			$pdf->SetXY($param_bull2016["x_signature_chef"], $y_signature_chef+7);
			if($param_bull2016["affiche_fonction_chef"]=='y') {
				if($param_bull2016["taille_texte_fonction_chef"]!= '' and $param_bull2016["taille_texte_fonction_chef"]!='0' and $param_bull2016["taille_texte_fonction_chef"]<'15') {
					$taille=$param_bull2016["taille_texte_fonction_chef"];
				} else {
					$taille='9';
				}
				$pdf->SetFont('DejaVu','B',$taille);
				$pdf->MultiCell($param_bull2016["largeur_signature_chef"], 5, ($tab_bull['formule']),0,2,'');
				$pdf->SetX($param_bull2016["x_signature_chef"]);
			}
			else {
				// Date seule
			}
			*/

			if($param_bull2016["taille_texte_identite_chef"]!='' and $param_bull2016["taille_texte_identite_chef"]!='0' and $param_bull2016["taille_texte_identite_chef"]<'15') {
				$taille = $param_bull2016["taille_texte_identite_chef"];
			} else {
				$taille='8';
			}
			$pdf->SetXY($param_bull2016["x_signature_chef"], $y_signature_chef+7);
			$pdf->SetFont('DejaVu','I',$taille);
			$pdf->MultiCell($param_bull2016["largeur_signature_chef"], 5, "Le ".strftime("%d/%m/%Y").", ".$tab_bull['suivi_par'], 0, 2, '');
		} else {
			$pdf->MultiCell($param_bull2016["largeur_signature_chef"], 5, ("Visa du Chef d'établissement\nou de son délégué"),0,2,'');
		}


		// Cadre Visa famille

		if($param_bull2016["bull2016_cadre_visa_famille"]=="y") {
			$pdf->SetFillColor($param_bull2016["couleur_communication_famille"]["R"], $param_bull2016["couleur_communication_famille"]["V"], $param_bull2016["couleur_communication_famille"]["B"]);
			$pdf->Rect($param_bull2016["x_visa_famille"], $y_visa_famille, $param_bull2016["largeur_visa_famille"], $param_bull2016["hauteur_visa_famille"], 'F');

			$pdf->SetXY($param_bull2016["x_visa_famille"], $y_visa_famille);
			$pdf->SetFillColor(0, 0, 0);
			$pdf->SetTextColor(0, 0, 0);
			$pdf->Cell($param_bull2016["largeur_visa_famille"],7, "Visa de la famille",0,2,'L');

			$pdf->SetXY($param_bull2016["x_visa_famille"], $y_visa_famille);
			$pdf->Cell($param_bull2016["largeur_visa_famille"], 7, "Date, nom et signature des responsables légaux",0,2,'R');
		}


		if(count($tab_rel)!=0) {
			$temoin_releve_trouve='n';
			if(isset($tab_rel['eleve'])) {
				//echo "\$tab_bull['eleve'][$i]['login']=".$tab_bull['eleve'][$i]['login']."<br />";
				for($k=0;$k<count($tab_rel['eleve']);$k++) {
					//echo "\$tab_rel['eleve'][$k]['login']=".$tab_rel['eleve'][$k]['login']."<br />";
					if(isset($tab_rel['eleve'][$k]['login'])) {
						if($tab_rel['eleve'][$k]['login']==$tab_bull['eleve'][$i]['login']) {
							releve_pdf($tab_rel,$k);
							$temoin_releve_trouve='y';
							break;
						}
					}
				}
			}
			/*
			else {
				echo "<p style='color:red;'>Il semble que le tableau des relevés de notes soit vide.</p>\n";
			}
			*/

			if($temoin_releve_trouve=='n') {
				$pdf->AddPage("P");
				$pdf->SetFontSize(10);
				$pdf->SetXY(20,20);
				$pdf->SetFont('DejaVu','B',14);
				$pdf->Cell(90,7,"Relevé de notes non trouvé pour ".my_strtoupper($tab_bull['eleve'][$i]['nom'])." ".casse_mot($tab_bull['eleve'][$i]['prenom'],'majf2'),0,2,'');

			}
		}

		if((isset($avec_bilan_cycle))&&($avec_bilan_cycle=="y")) {
			if((!isset($tab_ele_derniere_periode_imprimee[$tab_bull['eleve'][$i]['login']]))||
			($tab_ele_derniere_periode_imprimee[$tab_bull['eleve'][$i]['login']]==$tab_bull['num_periode'])) {
				// Pour les élèves de collège:
				if((($tab_bull['eleve'][$i]['mef_cycle']==4)&&($tab_bull['eleve'][$i]['mef_niveau']==3))||
				(($tab_bull['eleve'][$i]['mef_cycle']==3)&&($tab_bull['eleve'][$i]['mef_niveau']==6))) {
					bulletin_pdf_bilan_cycle($tab_bull, $i, $num_resp_bull);
				}
				// Pour les élèves de Segpa:
				elseif(($tab_bull['eleve'][$i]['mef_cycle']==3)&&($tab_bull['eleve'][$i]['mef_niveau']==3)) {
					// Le cycle 2 des 6e segpa n'est pas encore géré
					bulletin_pdf_bilan_cycle($tab_bull, $i, $num_resp_bull);
				}
			}
		}

		if((isset($intercaler_app_classe))&&($intercaler_app_classe=="y")) {
			bulletin_pdf_classe($tab_bull, $i);
		}

	}
}


// $tab_bull est le tableau pour telle classe et telle période
function bulletin_pdf_classe($tab_bull, $i="") {
	global $pdf,
		$affiche_deux_moy_gen,
		$tab_modele_pdf,
		$gepiSchoolAcademie,
		$gepiSchoolName,
		$gepiSchoolAdress1,
		$gepiSchoolAdress2,
		$gepiSchoolZipCode,
		$gepiSchoolCity,
		$gepiSchoolTel,
		$gepiSchoolFax,
		$gepiSchoolEmail,
		$gepiYear,
		$use_cell_ajustee,
		$date_bulletin,
		$bull_formule_bas;

	global $param_bull2016;

	//=========================================
	//nombre de matieres à afficher
	$nb_matiere=0;
	//$fich=fopen("/tmp/infos_matieres_eleve.txt","a+");
	//fwrite($fich,"\$tab_bull['eleve'][$i]['nom']=".$tab_bull['eleve'][$i]['nom']."\n");
	//$tab_bull['eleve'][$i]['cat_id']=array();
	for($m=0;$m<count($tab_bull['groupe']);$m++) {
		//if(isset($tab_bull['note'][$m][$i])) {
		// On n'affiche pas ici les groupes correspondant à AP, EPI ou Parcours

		if("$i"=="") {
			if((!isset($tab_bull['groupe'][$m]['type_grp'][0]))||($tab_bull['groupe'][$m]['type_grp'][0]['nom_court']=="local")) {
				//fwrite($fich,"\$tab_bull['groupe'][$m]['matiere']['matiere']=".$tab_bull['groupe'][$m]['matiere']['matiere']." ");
				$nb_matiere++;
			}
		}
		else {
			if((isset($tab_bull['note'][$m][$i]))&&
			((!isset($tab_bull['groupe'][$m]['type_grp'][0]))||($tab_bull['groupe'][$m]['type_grp'][0]['nom_court']=="local"))) {
				// Si l'élève suit l'option, sa note est affectée (éventuellement vide)
				//fwrite($fich,"\$tab_bull['groupe'][$m]['matiere']['matiere']=".$tab_bull['groupe'][$m]['matiere']['matiere']." ");
				//fwrite($fich,"\$tab_bull['note'][$m][$i]=".$tab_bull['note'][$m][$i]."\n");
				$nb_matiere++;
			}
		}
	}

	//$nb_categories_eleve_courant=count($tab_bull['eleve'][$i]['cat_id']);

	// DEBUG 20160220
	/*
	echo "\$tab_bull['groupe'][0]<pre>";
	print_r($tab_bull['groupe'][0]);
	echo "</pre>";
	*/


	$nb_AID_b_non_AP_EPI_Parcours=0;
	$nb_AID_e_non_AP_EPI_Parcours=0;
	$nb_AID_AP_EPI_Parcours=0;
	$indice_AID_b_AP=array();
	$indice_AID_b_EPI=array();
	$indice_AID_b_Parcours=array();
	$indice_AID_e_AP=array();
	$indice_AID_e_EPI=array();
	$indice_AID_e_Parcours=array();
	// A REVOIR : Certains AID devraient pouvoir être tagués pour apparaitre en page 2 dans les AP, EPI, Parcours personnalisés
	if("$i"=="") {
		if(isset($tab_bull['aid'])) {
			//$nb_matiere+=count($tab_bull['eleve'][$i]['aid_e']);
			for($loop=0;$loop<count($tab_bull['aid']);$loop++) {
				if($tab_bull['aid'][$loop]['order_display1']=="b") {
					// Repérer les AP, EPI,...
					if($tab_bull['aid'][$loop]['type_aid']==0) {
						$nb_matiere++;
						$nb_AID_b_non_AP_EPI_Parcours++;
					}
					else {
						$nb_AID_AP_EPI_Parcours++;
						if($tab_bull['aid'][$loop]['type_aid']==1) {
							$indice_AID_b_AP[]=$loop;
						}
						elseif($tab_bull['aid'][$loop]['type_aid']==2) {
							$indice_AID_b_EPI[]=$loop;
						}
						elseif($tab_bull['aid'][$loop]['type_aid']==3) {
							$indice_AID_b_Parcours[]=$loop;
						}
					}
				}
				else {
					// Repérer les AP, EPI,...
					if($tab_bull['aid'][$loop]['type_aid']==0) {
						$nb_matiere++;
						$nb_AID_e_non_AP_EPI_Parcours++;
					}
					else {
						$nb_AID_AP_EPI_Parcours++;
						if($tab_bull['aid'][$loop]['type_aid']==1) {
							$indice_AID_e_AP[]=$loop;
						}
						elseif($tab_bull['aid'][$loop]['type_aid']==2) {
							$indice_AID_e_EPI[]=$loop;
						}
						elseif($tab_bull['aid'][$loop]['type_aid']==3) {
							$indice_AID_e_Parcours[]=$loop;
						}
					}
				}
			}
		}
	}
	else {
		if(isset($tab_bull['eleve'][$i]['aid_b'])) {
			//$nb_matiere+=count($tab_bull['eleve'][$i]['aid_b']);
			for($loop=0;$loop<count($tab_bull['eleve'][$i]['aid_b']);$loop++) {
				// Repérer les AP, EPI,...
				if($tab_bull['eleve'][$i]['aid_b'][$loop]['type_aid']==0) {
					$nb_matiere++;
					$nb_AID_b_non_AP_EPI_Parcours++;
				}
				else {
					$nb_AID_AP_EPI_Parcours++;
					if($tab_bull['eleve'][$i]['aid_b'][$loop]['type_aid']==1) {
						$indice_AID_b_AP[]=$loop;
					}
					elseif($tab_bull['eleve'][$i]['aid_b'][$loop]['type_aid']==2) {
						$indice_AID_b_EPI[]=$loop;
					}
					elseif($tab_bull['eleve'][$i]['aid_b'][$loop]['type_aid']==3) {
						$indice_AID_b_Parcours[]=$loop;
					}
				}
			}
		}

		$nb_AID_e_non_AP_EPI_Parcours=0;
		if(isset($tab_bull['eleve'][$i]['aid_e'])) {
			//$nb_matiere+=count($tab_bull['eleve'][$i]['aid_e']);
			for($loop=0;$loop<count($tab_bull['eleve'][$i]['aid_e']);$loop++) {
				// Repérer les AP, EPI,...
				if($tab_bull['eleve'][$i]['aid_e'][$loop]['type_aid']==0) {
					$nb_matiere++;
					$nb_AID_e_non_AP_EPI_Parcours++;
				}
				else {
					$nb_AID_AP_EPI_Parcours++;
					if($tab_bull['eleve'][$i]['aid_e'][$loop]['type_aid']==1) {
						$indice_AID_e_AP[]=$loop;
					}
					elseif($tab_bull['eleve'][$i]['aid_e'][$loop]['type_aid']==2) {
						$indice_AID_e_EPI[]=$loop;
					}
					elseif($tab_bull['eleve'][$i]['aid_e'][$loop]['type_aid']==3) {
						$indice_AID_e_Parcours[]=$loop;
					}
				}
			}
		}
	}





	$pdf->AddPage(); //ajout d'une page au document
	$pdf->SetFont('DejaVu');

	$arrondi=$param_bull2016["bull2016_arrondi"];
	$nb_chiffre_virgule=$param_bull2016["bull2016_nb_chiffre_virgule"];
	$chiffre_avec_zero=$param_bull2016["bull2016_chiffre_avec_zero"];

	$hauteur_pris=0;

	// Récupération de l'identifiant de la classe:
	if("$i"=="") {
		$classe_id=$tab_bull['id_classe'];
		$classe_courante=$tab_bull['classe'];
	}
	else {
		$classe_id=$tab_bull['eleve'][$i]['id_classe'];
		$classe_courante=$tab_bull['eleve'][$i]['classe'];
	}

	//=========================================

	// Cadre Logo RF

	$pdf->Rect($param_bull2016["x_cadre_logo_RF"], $param_bull2016["y_cadre_logo_RF"], $param_bull2016["largeur_cadre_logo_RF"], $param_bull2016["hauteur_cadre_logo_RF"], 'D');

	//$logo_RF=$gepiPath."/images/logo_RF.jpg";
	$logo_RF="../images/logo_RF.jpg";
	$valeur=redimensionne_image($logo_RF, 18*3.2, 10*3.2);
	//$X_logo = $param_bull2016["x_logo_RF"];
	//$Y_logo = $param_bull2016["y_logo_RF"];
	$L_logo = $valeur[0];
	$H_logo = $valeur[1];

	$X_logo=$param_bull2016["x_cadre_logo_RF"]+($param_bull2016["largeur_cadre_logo_RF"]-$L_logo)/2;
	$Y_logo=$param_bull2016["y_cadre_logo_RF"]+($param_bull2016["hauteur_cadre_logo_RF"]-$H_logo)/2;

	// centrage du logo
	//$centre_du_logo = ( $H_logo / 2 );
	//$Y_logo = $tab_modele_pdf["Y_centre_logo"][$classe_id] - $centre_du_logo;

	//logo
	$tmp_dim_photo=getimagesize($logo_RF);
	if((isset($tmp_dim_photo[2]))&&($tmp_dim_photo[2]==2)) {
		$pdf->Image($logo_RF, $X_logo, $Y_logo, $L_logo, $H_logo);
	}

	//=========================================

	// Cadre EN

	$pdf->Rect($param_bull2016["x_cadre_EN"], $param_bull2016["y_cadre_EN"], $param_bull2016["largeur_cadre_logo_RF"], $param_bull2016["hauteur_cadre_logo_RF"], 'D');

	$pdf->SetXY($param_bull2016["x_cadre_EN"]+2.5, $param_bull2016["y_cadre_EN"]+1);
	$pdf->SetFont('DejaVu','',6);
	$pdf->SetTextColor(0,0,0);
	$texte="MINISTÈRE\nDE L'ÉDUCATION\nNATIONALE, DE\nL'ENSEIGNEMENT\nSUPÉRIEUR ET DE\nLA RECHERCHE";
	//$pdf->Cell($param_bull2016["largeur_cadre_logo_RF"]-8, $param_bull2016["hauteur_cadre_EN"]-6, $texte,0,2,'C');
	$pdf->drawTextBox($texte, $param_bull2016["largeur_cadre_logo_RF"]-5, $param_bull2016["hauteur_cadre_EN"]-4, 'C', 'M', 0);

	//=========================================

	// Section Académie, établissement, adresse étab

	$pdf->SetXY($param_bull2016["x_cadre_etab"], $param_bull2016["y_cadre_etab_academie"]);
	$pdf->SetFont('DejaVu','',9);
	$pdf->SetTextColor(0,0,0);
	$pdf->Cell($param_bull2016["largeur_cadre_etab"],7, "Académie de ".$gepiSchoolAcademie,0,2,'L');

	$pdf->SetXY($param_bull2016["x_cadre_etab"], $param_bull2016["y_cadre_etab_college"]);
	$pdf->SetFont('DejaVu','B',10);
	$pdf->SetTextColor(0,0,0);
	$pdf->Cell($param_bull2016["largeur_cadre_etab"],7, $gepiSchoolName,0,2,'L');

	$pdf->SetXY($param_bull2016["x_cadre_etab"], $param_bull2016["y_cadre_etab_adresse_college"]);
	$pdf->SetFont('DejaVu','',7);
	$pdf->SetTextColor(0,0,0);
	$adresse_etab="";
	if($gepiSchoolAdress1!="") {
		$adresse_etab=$gepiSchoolAdress1;
		if($gepiSchoolAdress2!="") {
			$adresse_etab.=", ".$gepiSchoolAdress2;
		}
	}
	elseif($gepiSchoolAdress2!="") {
		$adresse_etab=$gepiSchoolAdress2;
	}
	$pdf->Cell($param_bull2016["largeur_cadre_etab"], 7, $adresse_etab, 0, 2, 'L');

	$pdf->SetXY($param_bull2016["x_cadre_etab"], $param_bull2016["y_cadre_etab_cp_commune_college"]);
	$pdf->SetFont('DejaVu','',7);
	$pdf->SetTextColor(0,0,0);
	$cp_commune_etab="";
	if($gepiSchoolZipCode!="") {
		$cp_commune_etab=$gepiSchoolZipCode;
		if($gepiSchoolCity!="") {
			$cp_commune_etab.=" ".$gepiSchoolCity;
		}
	}
	elseif($gepiSchoolCity!="") {
		$cp_commune_etab=$gepiSchoolCity;
	}
	$pdf->Cell($param_bull2016["largeur_cadre_etab"], 7, $cp_commune_etab, 0, 2, 'L');

	$pdf->SetXY($param_bull2016["x_cadre_etab"], $param_bull2016["y_cadre_etab_tel_college"]);
	$pdf->SetFont('DejaVu','',7);
	$pdf->SetTextColor(0,0,0);
	$tel_etab="";
	if($gepiSchoolTel!="") {
		$tel_etab=$gepiSchoolTel;
		if($gepiSchoolFax!="") {
			$tel_etab.=" (fax:".$gepiSchoolFax.")";
		}
	}
	elseif($gepiSchoolFax!="") {
		$tel_etab="(fax:".$gepiSchoolFax.")";
	}
	$pdf->Cell($param_bull2016["largeur_cadre_etab"], 7, $tel_etab, 0, 2, 'L');

	$pdf->SetXY($param_bull2016["x_cadre_etab"], $param_bull2016["y_cadre_etab_email_college"]);
	$pdf->SetFont('DejaVu','',7);
	$pdf->SetTextColor(0,0,0);
	$pdf->Cell($param_bull2016["largeur_cadre_etab"], 7, $gepiSchoolEmail, 0, 2, 'L');

	//=========================================

	// Section cycle et niveau
	if(($i!="")&&(isset($tab_bull['eleve'][$i]['mef_cycle']))&&(isset($tab_bull['eleve'][$i]['mef_niveau']))) {
		$cycle=$tab_bull['eleve'][$i]['mef_cycle'];
		$niveau=$tab_bull['eleve'][$i]['mef_niveau'];
		// Debug
		//echo "cycle=$cycle et niveau=$niveau<br />";

		// Colonne cycle:
		for($loop_cycle=2;$loop_cycle<=4;$loop_cycle++) {
			if($loop_cycle==$cycle) {
				$pdf->SetFillColor($param_bull2016["couleur_cycle_courant"]["cycle"]["R"], $param_bull2016["couleur_cycle_courant"]["cycle"]["V"], $param_bull2016["couleur_cycle_courant"]["cycle"]["B"]);
			}
			else {
				$pdf->SetFillColor($param_bull2016["couleur_cycle_autre"]["R"], $param_bull2016["couleur_cycle_autre"]["V"], $param_bull2016["couleur_cycle_autre"]["B"]);
			}
			$pdf->Rect($param_bull2016["x_colonne_cycle"], $param_bull2016["y_colonne_cycle"]+($loop_cycle-2)*($param_bull2016["cote_carre_cycle"]+$param_bull2016["ecart_carres_cycle"]), $param_bull2016["cote_carre_cycle"], $param_bull2016["cote_carre_cycle"], 'F');
			$pdf->SetFillColor(0, 0, 0);

			$pdf->SetXY($param_bull2016["x_colonne_cycle"], $param_bull2016["y_colonne_cycle"]+($loop_cycle-2)*($param_bull2016["cote_carre_cycle"]+$param_bull2016["ecart_carres_cycle"]));
			$pdf->SetFont('DejaVu','',14);
			$pdf->SetTextColor(255,255,255);
			$pdf->Cell($param_bull2016["cote_carre_cycle"],$param_bull2016["cote_carre_cycle"], $loop_cycle,0,2,'C');

		}

		// Colonnes niveau:
		for($loop_cycle=2;$loop_cycle<=4;$loop_cycle++) {
			for($loop_niveau=0;$loop_niveau<3;$loop_niveau++) {
				if(($loop_cycle==$cycle)&&(isset($param_bull2016["cycles_et_niveaux"][$loop_cycle][$loop_niveau]["texte"]))&&($param_bull2016["cycles_et_niveaux"][$loop_cycle][$loop_niveau]["texte"]==$niveau)) {
					$pdf->SetFillColor($param_bull2016["couleur_cycle_courant"]["niveau"]["R"], $param_bull2016["couleur_cycle_courant"]["niveau"]["V"], $param_bull2016["couleur_cycle_courant"]["niveau"]["B"]);
				}
				else {
					$pdf->SetFillColor($param_bull2016["couleur_cycle_autre"]["R"], $param_bull2016["couleur_cycle_autre"]["V"], $param_bull2016["couleur_cycle_autre"]["B"]);
				}
				$pdf->Rect($param_bull2016["x_colonne_cycle"]+($loop_niveau+1)*($param_bull2016["cote_carre_cycle"]+$param_bull2016["ecart_carres_cycle"]), $param_bull2016["y_colonne_cycle"]+($loop_cycle-2)*($param_bull2016["cote_carre_cycle"]+$param_bull2016["ecart_carres_cycle"]), $param_bull2016["cote_carre_cycle"], $param_bull2016["cote_carre_cycle"], 'F');
				$pdf->SetFillColor(0, 0, 0);

				$pdf->SetXY($param_bull2016["x_colonne_cycle"]+($loop_niveau+1)*($param_bull2016["cote_carre_cycle"]+$param_bull2016["ecart_carres_cycle"]), $param_bull2016["y_colonne_cycle"]+($loop_cycle-2)*($param_bull2016["cote_carre_cycle"]+$param_bull2016["ecart_carres_cycle"]));
				$pdf->SetFont('DejaVu','',14);
				$pdf->SetTextColor(255,255,255);
				$pdf->Cell($param_bull2016["cote_carre_cycle"],$param_bull2016["cote_carre_cycle"], $param_bull2016["cycles_et_niveaux"][$loop_cycle][$loop_niveau]["texte"],0,2,'C');
			}
		}
	}
	else {
		// Colonne cycle:
		for($loop_cycle=2;$loop_cycle<=4;$loop_cycle++) {
			if(in_array($loop_cycle, $tab_bull["mef_cycle"])) {
				$pdf->SetFillColor($param_bull2016["couleur_cycle_courant"]["cycle"]["R"], $param_bull2016["couleur_cycle_courant"]["cycle"]["V"], $param_bull2016["couleur_cycle_courant"]["cycle"]["B"]);
			}
			else {
				$pdf->SetFillColor($param_bull2016["couleur_cycle_autre"]["R"], $param_bull2016["couleur_cycle_autre"]["V"], $param_bull2016["couleur_cycle_autre"]["B"]);
			}
			$pdf->Rect($param_bull2016["x_colonne_cycle"], $param_bull2016["y_colonne_cycle"]+($loop_cycle-2)*($param_bull2016["cote_carre_cycle"]+$param_bull2016["ecart_carres_cycle"]), $param_bull2016["cote_carre_cycle"], $param_bull2016["cote_carre_cycle"], 'F');
			$pdf->SetFillColor(0, 0, 0);

			$pdf->SetXY($param_bull2016["x_colonne_cycle"], $param_bull2016["y_colonne_cycle"]+($loop_cycle-2)*($param_bull2016["cote_carre_cycle"]+$param_bull2016["ecart_carres_cycle"]));
			$pdf->SetFont('DejaVu','',14);
			$pdf->SetTextColor(255,255,255);
			$pdf->Cell($param_bull2016["cote_carre_cycle"],$param_bull2016["cote_carre_cycle"], $loop_cycle,0,2,'C');

		}

		// Colonnes niveau:
		for($loop_cycle=2;$loop_cycle<=4;$loop_cycle++) {
			for($loop_niveau=0;$loop_niveau<3;$loop_niveau++) {
				if((in_array($loop_cycle, $tab_bull["mef_cycle"]))&&(isset($param_bull2016["cycles_et_niveaux"][$loop_cycle][$loop_niveau]["texte"]))&&(in_array($param_bull2016["cycles_et_niveaux"][$loop_cycle][$loop_niveau]["texte"], $tab_bull["mef_niveau"]))) {
					$pdf->SetFillColor($param_bull2016["couleur_cycle_courant"]["niveau"]["R"], $param_bull2016["couleur_cycle_courant"]["niveau"]["V"], $param_bull2016["couleur_cycle_courant"]["niveau"]["B"]);
				}
				else {
					$pdf->SetFillColor($param_bull2016["couleur_cycle_autre"]["R"], $param_bull2016["couleur_cycle_autre"]["V"], $param_bull2016["couleur_cycle_autre"]["B"]);
				}
				$pdf->Rect($param_bull2016["x_colonne_cycle"]+($loop_niveau+1)*($param_bull2016["cote_carre_cycle"]+$param_bull2016["ecart_carres_cycle"]), $param_bull2016["y_colonne_cycle"]+($loop_cycle-2)*($param_bull2016["cote_carre_cycle"]+$param_bull2016["ecart_carres_cycle"]), $param_bull2016["cote_carre_cycle"], $param_bull2016["cote_carre_cycle"], 'F');
				$pdf->SetFillColor(0, 0, 0);

				$pdf->SetXY($param_bull2016["x_colonne_cycle"]+($loop_niveau+1)*($param_bull2016["cote_carre_cycle"]+$param_bull2016["ecart_carres_cycle"]), $param_bull2016["y_colonne_cycle"]+($loop_cycle-2)*($param_bull2016["cote_carre_cycle"]+$param_bull2016["ecart_carres_cycle"]));
				$pdf->SetFont('DejaVu','',14);
				$pdf->SetTextColor(255,255,255);
				$pdf->Cell($param_bull2016["cote_carre_cycle"],$param_bull2016["cote_carre_cycle"], $param_bull2016["cycles_et_niveaux"][$loop_cycle][$loop_niveau]["texte"],0,2,'C');
			}
		}
	}

	//=========================================

	// Cadre Logo établissement

	//======================================
	// 20180124
	if ((isset($GLOBALS['multisite']))&&($GLOBALS['multisite'] == 'y')&&(isset($_COOKIE['RNE']))) {
		$chemin_logo = '../images/'.$_COOKIE['RNE'].'/';
	}
	else {
		$chemin_logo = '../images/';
	}
	$logo = $chemin_logo.getSettingValue('logo_etab');
	//======================================
	$format_du_logo = mb_strtolower(str_replace('.','',strstr(getSettingValue('logo_etab'), '.')));

	// Logo
	//if($tab_modele_pdf["affiche_logo_etab"][$classe_id]==='1' and file_exists($logo) and getSettingValue('logo_etab') != '' and ($format_du_logo==='jpg' or $format_du_logo==='png')) {
	if($param_bull2016["affiche_logo_etab"]==1 and file_exists($logo) and getSettingValue('logo_etab') != '' and ($format_du_logo==='jpg' or $format_du_logo==='png')) {
		$valeur=redimensionne_image($logo, ($param_bull2016["largeur_max_logo_etab"]*2.8), ($param_bull2016["hauteur_max_logo_etab"]*2.8));
		$X_logo = $param_bull2016["x_logo_etab"];
		$Y_logo = $param_bull2016["y_logo_etab"];
		$L_logo = $valeur[0];
		$H_logo = $valeur[1];
		//$X_etab = $X_logo + $L_logo + 1;
		//$Y_etab = $Y_logo;

		/*
		if ( !isset($tab_modele_pdf["centrage_logo"][$classe_id]) or empty($tab_modele_pdf["centrage_logo"][$classe_id]) ) {
			$tab_modele_pdf["centrage_logo"][$classe_id] = '0';
		}

		if ( $tab_modele_pdf["centrage_logo"][$classe_id] === '1' ) {
			// centrage du logo
			$centre_du_logo = ( $H_logo / 2 );
			$Y_logo = $tab_modele_pdf["Y_centre_logo"][$classe_id] - $centre_du_logo;
		}
		*/

		//logo
		$tmp_dim_photo=getimagesize($logo);
		if((isset($tmp_dim_photo[2]))&&($tmp_dim_photo[2]==2)) {
			$pdf->Image($logo, $X_logo, $Y_logo, $L_logo, $H_logo);
		}
	}



	// Cadre Année, période, identité élève, pp et classe
	$pdf->SetFillColor($param_bull2016["couleur_cadre_identite"]["R"], $param_bull2016["couleur_cadre_identite"]["V"], $param_bull2016["couleur_cadre_identite"]["B"]);

	$pdf->Rect($param_bull2016["x_cadre_eleve_app_classe"], $param_bull2016["y_cadre_eleve_app_classe"], $param_bull2016["largeur_cadre_eleve_app_classe"], $param_bull2016["hauteur_cadre_eleve_app_classe"], 'F');

	$largeur_cadre_eleve=$param_bull2016["largeur_cadre_eleve_app_classe"];

	$pdf->SetFillColor(0, 0, 0);

	$pdf->SetXY($param_bull2016["x_cadre_eleve_app_classe"], $param_bull2016["y_annee_scolaire_app_classe"]);
	$pdf->SetFont('DejaVu','B',10);
	$pdf->SetTextColor(0,0,0);
	$pdf->Cell($largeur_cadre_eleve,7, "Année scolaire ".$gepiYear,0,2,'C');

	$pdf->SetXY($param_bull2016["x_cadre_eleve_app_classe"], $param_bull2016["y_periode_app_classe"]);
	$pdf->SetFont('DejaVu','',8);
	$pdf->SetTextColor(0,0,0);
	if($tab_bull['nb_periodes']==2) {
		$trimestriel_ou_semestriel="semestriel";
	}
	else {
		$trimestriel_ou_semestriel="trimestriel";
	}
	$pdf->Cell($largeur_cadre_eleve,7, "Bilan ".$trimestriel_ou_semestriel." de la classe - ".$tab_bull['nom_periode'],0,2,'C');

	$index_pp='pp_classe';
	if(isset($tab_bull['eleve'][$i][$index_pp][0]['login'])) {
		$pp_classe[$i]=ucfirst($tab_bull['gepi_prof_suivi'])." : ";
		$pp_classe[$i].=affiche_utilisateur($tab_bull['eleve'][$i][$index_pp][0]['login'],$tab_bull['eleve'][$i]['id_classe']);
		for($i_pp=1;$i_pp<count($tab_bull['eleve'][$i][$index_pp]);$i_pp++) {
			$pp_classe[$i].=", ";
			$pp_classe[$i].=affiche_utilisateur($tab_bull['eleve'][$i][$index_pp][$i_pp]['login'],$tab_bull['eleve'][$i]['id_classe']);
		}
	}
	else {
		$pp_classe[$i]="";
	}
	$pdf->SetXY($param_bull2016["x_cadre_eleve_app_classe"], $param_bull2016["y_pp_app_classe"]);
	$pdf->Cell($largeur_cadre_eleve,7, $pp_classe[$i],0,2,'C');

	$pdf->SetXY($param_bull2016["x_cadre_eleve_app_classe"], $param_bull2016["y_classe_app_classe"]);
	$pdf->SetFont('DejaVu','',11);
	$pdf->Cell($largeur_cadre_eleve,7, "Classe de ".unhtmlentities($classe_courante),0,2,'C');


	// Bandeau Suivi des acquis

	$pdf->SetFillColor($param_bull2016["couleur_bandeau_suivi_acquis"]["R"], $param_bull2016["couleur_bandeau_suivi_acquis"]["V"], $param_bull2016["couleur_bandeau_suivi_acquis"]["B"]);

	$pdf->Rect($param_bull2016["x_bandeau_suivi_acquis"], $param_bull2016["y_bandeau_suivi_acquis_app_classe"], $param_bull2016["largeur_bandeau_suivi_acquis"], $param_bull2016["hauteur_bandeau_suivi_acquis"], 'F');

	$pdf->SetFillColor(0, 0, 0);
	$pdf->SetTextColor(255, 255, 255);
	$pdf->SetXY($param_bull2016["x_bandeau_suivi_acquis"], $param_bull2016["y_bandeau_suivi_acquis_app_classe"]+1);
	$pdf->SetFont('DejaVu','B',12);
	$pdf->Cell($param_bull2016["largeur_bandeau_suivi_acquis"],7, "Suivi des acquis scolaires",0,2,'C');

	//=========================================

	// Tableau des acquis
	// Ligne de titre

	// Colonne 2 : Éléments de programmes (supprimée)
/*
	$param_bull2016["x_acquis_col_appreciation_app_classe"]=$param_bull2016["x_acquis_col_2"];
	$param_bull2016["x_acquis_col_moyclasse_app_classe"]=$param_bull2016["x_acquis_col_moyclasse"];
*/

	// Colonne 2 : Appréciation
	$pdf->SetFillColor($param_bull2016["couleur_acquis_ligne_entete"]["R"], $param_bull2016["couleur_acquis_ligne_entete"]["V"], $param_bull2016["couleur_acquis_ligne_entete"]["B"]);
	$pdf->Rect($param_bull2016["x_acquis_col_appreciation_app_classe"], $param_bull2016["y_acquis_ligne_entete_app_classe"], $param_bull2016["largeur_acquis_col_appreciation_app_classe"], $param_bull2016["hauteur_acquis_ligne_entete"], 'F');
	$pdf->SetFillColor(0, 0, 0);

	$pdf->SetTextColor(0, 0, 0);
	$pdf->SetXY($param_bull2016["x_acquis_col_appreciation_app_classe"], $param_bull2016["y_acquis_ligne_entete_app_classe"]);
	$pdf->SetFont('DejaVu','',7);
	//$pdf->Cell($param_bull2016["largeur_bandeau_suivi_acquis"],7, "Suivi des acquis scolaires de l'élève",0,2,'C');
	$texte="Acquisitions, progrès et difficultés éventuelles";
	//$pdf->Cell($param_bull2016["largeur_cadre_logo_RF"]-8, $param_bull2016["hauteur_cadre_EN"]-6, $texte,0,2,'C');
	//$pdf->drawTextBox($texte, $param_bull2016["largeur_acquis_col_3"], $param_bull2016["hauteur_acquis_ligne_entete"], 'L', 'M', 0);
	$taille_max_police=7;
	$cell_ajustee_texte_matiere_ratio_min_max=3;
	$taille_min_police=ceil($taille_max_police/$cell_ajustee_texte_matiere_ratio_min_max);
	$largeur_dispo=$param_bull2016["largeur_acquis_col_appreciation_app_classe"];
	$h_cell=$param_bull2016["hauteur_acquis_ligne_entete"];
	cell_ajustee($texte,$pdf->GetX(),$pdf->GetY(),$largeur_dispo,$h_cell,$taille_max_police,$taille_min_police,'');


	// Colonne 4 : Moyenne élève (supprimée)

	// Colonne 3 : Moyenne classe
	$pdf->SetFillColor($param_bull2016["couleur_acquis_ligne_entete"]["R"], $param_bull2016["couleur_acquis_ligne_entete"]["V"], $param_bull2016["couleur_acquis_ligne_entete"]["B"]);
	$pdf->Rect($param_bull2016["x_acquis_col_moyclasse"], $param_bull2016["y_acquis_ligne_entete_app_classe"], $param_bull2016["largeur_acquis_col_moyclasse"], $param_bull2016["hauteur_acquis_ligne_entete"], 'F');
	$pdf->SetFillColor(0, 0, 0);

	$pdf->SetTextColor(0, 0, 0);
	$pdf->SetXY($param_bull2016["x_acquis_col_moyclasse"], $param_bull2016["y_acquis_ligne_entete_app_classe"]);
	$pdf->SetFont('DejaVu','',7);
	//$pdf->Cell($param_bull2016["largeur_bandeau_suivi_acquis"],7, "Suivi des acquis scolaires de l'élève",0,2,'C');
	$texte="Moyenne\nde classe";
	//$pdf->Cell($param_bull2016["largeur_cadre_logo_RF"]-8, $param_bull2016["hauteur_cadre_EN"]-6, $texte,0,2,'C');
	//$pdf->drawTextBox($texte, $param_bull2016["largeur_acquis_col_moyclasse"], $param_bull2016["hauteur_acquis_ligne_entete"], 'C', 'M', 0);
	$taille_max_police=7;
	$cell_ajustee_texte_matiere_ratio_min_max=3;
	$taille_min_police=ceil($taille_max_police/$cell_ajustee_texte_matiere_ratio_min_max);
	$largeur_dispo=$param_bull2016["largeur_acquis_col_moyclasse"];
	$h_cell=$param_bull2016["hauteur_acquis_ligne_entete"];
	cell_ajustee($texte,$pdf->GetX(),$pdf->GetY(),$largeur_dispo,$h_cell,$taille_max_police,$taille_min_police,'','C','C');



	// Lignes matières

	// Initialisation
	$y0=$param_bull2016["y_acquis_ligne_entete_app_classe"]+$h_cell;

	// 20161231
	// Calcul du nombre de matières, EPI, AP,... compris
	$nb_matiere_non_EPI_AP_Parcours=$nb_matiere;

	$nb_EPI_AP_Parcours=0;
	$nb_EPI=0;
	$nb_AP=0;
	$nb_Parcours=0;
	for($m=0;$m<count($tab_bull['groupe']);$m++) {
		//if(isset($tab_bull['note'][$m][$i])) {
		// On n'affiche pas ici les groupes correspondant à AP, EPI ou Parcours
		if((isset($tab_bull['note'][$m][$i]))&&
		(isset($tab_bull['groupe'][$m]['type_grp'][0]))) {
			if($tab_bull['groupe'][$m]['type_grp'][0]['nom_court']=="EPI") {
				$nb_EPI++;
			}
			elseif($tab_bull['groupe'][$m]['type_grp'][0]['nom_court']=="AP") {
				$nb_AP++;
			}
			elseif($tab_bull['groupe'][$m]['type_grp'][0]['nom_court']=="Parcours") {
				$nb_Parcours++;
			}
		}
	}

	$nb_AP=$nb_AP+count($indice_AID_b_AP)+count($indice_AID_e_AP);
	$nb_EPI=$nb_EPI+count($indice_AID_b_EPI)+count($indice_AID_e_EPI);
	$nb_Parcours=$nb_Parcours+count($indice_AID_b_Parcours)+count($indice_AID_e_Parcours);

	$nb_EPI_AP_Parcours=$nb_EPI+$nb_AP+$nb_Parcours;

	// Nombre de matières, EPI, AP,... compris
	$nb_matiere=$nb_matiere+$nb_AP+$nb_EPI+$nb_Parcours;

	$y0=$param_bull2016["y_acquis_ligne_entete_app_classe"]+$param_bull2016["hauteur_acquis_ligne_entete"];

	// Avec 10mm de marge basse
	// Et 5mm sous l'entête
	$hauteur_dispo_appreciations=HauteurPage-$y0-5-10-$nb_matiere*0.5;
	// On compte la ligne de titre AP, celle EPI,... plus les 5mm de marge qui précèdent et les 0.5mm d'espace entre les cadres/lignes
	if($nb_AP>0) {
		$hauteur_dispo_appreciations-=($param_bull2016["hauteur_ligne_titre_EPI_AP_Parcours"]+$param_bull2016["espace_vertical_entre_sections_EPI_AP_Parcours"]+0.5);
	}
	if($nb_EPI>0) {
		$hauteur_dispo_appreciations-=($param_bull2016["hauteur_ligne_titre_EPI_AP_Parcours"]+$param_bull2016["espace_vertical_entre_sections_EPI_AP_Parcours"]+0.5);
	}
	if($nb_Parcours>0) {
		$hauteur_dispo_appreciations-=($param_bull2016["hauteur_ligne_titre_EPI_AP_Parcours"]+$param_bull2016["espace_vertical_entre_sections_EPI_AP_Parcours"]+0.5);
	}

	if(($tab_bull['synthese_classe']!="")&&($tab_bull['synthese_classe']!="-")) {
		$hauteur_dispo_appreciations-=$param_bull2016["espace_vertical_entre_sections_EPI_AP_Parcours"];
		$nb_matiere++;
	}

	//$param_bull2016["hauteur_ligne_titre_EPI_AP_Parcours"]

	// Calcul de la hauteur des lignes matières (18mm par défaut, mais si ça ne tient pas, on réduit
	if($nb_matiere>0) {

		/*
		$ymax=$y0+$nb_matiere*(18+0.5);
		if($ymax<HauteurPage-$y0-10) {
		//if($ymax<HauteurPage-$y0-5) {
			$hauteur_matiere=18;
		}
		else {
			//$hauteur_matiere=(HauteurPage-$y0-5-($nb_matiere-1)*0.5)/$nb_matiere;
			$hauteur_matiere=(HauteurPage-$y0-10-($nb_matiere-1)*0.5)/$nb_matiere;
		}
		*/

		$hauteur_requise_pour_lignes_matieres=18.5*$nb_matiere+10;
		if($hauteur_requise_pour_lignes_matieres<=$hauteur_dispo_appreciations) {
			$hauteur_matiere=18;
		}
		else {
			$hauteur_matiere=$hauteur_dispo_appreciations/$nb_matiere;
		}
	}

	$cpt_matiere=0;
	$y_courant=$y0+0.5;

	//echo "\$i=$i\n";

	// Début des AID_b
	if($nb_AID_b_non_AP_EPI_Parcours>0) {
		if("$i"=="") {
			if(isset($tab_bull['aid'])) {
				for($m=0;$m<count($tab_bull['aid']);$m++) {
					if($tab_bull['aid'][$m]['order_display1']=="b") {
						if($tab_bull['aid'][$m]['type_aid']==0) {

							// Colonne 1 : Matière, prof

							$pdf->SetFillColor($param_bull2016["couleur_acquis_ligne_alt".($cpt_matiere%2+1)]["R"], $param_bull2016["couleur_acquis_ligne_alt".($cpt_matiere%2+1)]["V"], $param_bull2016["couleur_acquis_ligne_alt".($cpt_matiere%2+1)]["B"]);
							$pdf->Rect($param_bull2016["x_acquis_col_1"], $y_courant, $param_bull2016["largeur_acquis_col_1"], $hauteur_matiere, 'F');
							$pdf->SetFillColor(0, 0, 0);

							$pdf->SetTextColor(0, 0, 0);
							$pdf->SetXY($param_bull2016["x_acquis_col_1"], $y_courant);
							$pdf->SetFont('DejaVu','',8);
							/*
							//$pdf->Cell($param_bull2016["largeur_bandeau_suivi_acquis"],7, "Suivi des acquis scolaires de l'élève",0,2,'C');
							$texte="Matiere $m ".$hauteur_matiere;
							$pdf->drawTextBox($texte, $param_bull2016["largeur_acquis_col_1"], $hauteur_matiere, 'L', 'M', 0);
							*/

							if($tab_bull['aid'][$m]['display_nom']=='z') {
								if((isset($tab_bull['aid'][$m]['aid_nom']))&&($tab_bull['aid'][$m]['aid_nom']!="")) {
									$info_nom_matiere=$tab_bull['aid'][$m]['aid_nom'];
								}
								elseif((isset($tab_bull['aid'][$m]['nom_complet']))&&($tab_bull['aid'][$m]['nom_complet']!="")) {
									$info_nom_matiere=$tab_bull['aid'][$m]['nom_complet'];
								}
								else {
									$info_nom_matiere=$tab_bull['aid'][$m]['nom'];
								}
							}
							elseif($tab_bull['aid'][$m]['display_nom']=='x') {
								if((isset($tab_bull['aid'][$m]['nom']))&&($tab_bull['aid'][$m]['nom']!="")) {
									$info_nom_matiere=$tab_bull['aid'][$m]['nom'];
								}
								else {
									$info_nom_matiere=$tab_bull['aid'][$m]['nom_complet'];
								}

								if((isset($tab_bull['aid'][$m]['aid_nom']))&&($tab_bull['aid'][$m]['aid_nom']!="")) {
									if($info_nom_matiere!="") {
										$info_nom_matiere.=": ";
									}
									$info_nom_matiere.=$tab_bull['aid'][$m]['aid_nom'];
								}
							}
							else {
								// display_nom à y ou n: On met en 1ère colonne le nom complet de catégorie AID
								if((isset($tab_bull['aid'][$m]['nom_complet']))&&($tab_bull['aid'][$m]['nom_complet']!="")) {
									$info_nom_matiere=$tab_bull['aid'][$m]['nom_complet'];
								}
								else {
									$info_nom_matiere=$tab_bull['aid'][$m]['nom'];
								}
							}
							if($info_nom_matiere=="") {
								$info_nom_matiere="AID";
							}

							$hauteur_caractere_matiere=8;
							$cell_ajustee_texte_matiere_ratio_min_max=3;

							// 20130927 : cell_ajustee() ou pas sur le nom de matière/enseignement
							$cell_ajustee_texte_matiere=1;
							if((isset($cell_ajustee_texte_matiere))&&($cell_ajustee_texte_matiere==1)) {
								// On met un décalage pour ne pas coller le texte à la bordure
								$Y_decal_cell_ajustee=2;
								$pdf->SetXY($param_bull2016["x_acquis_col_1"], $y_courant+$Y_decal_cell_ajustee);

								$texte=$info_nom_matiere;
								$taille_max_police=$hauteur_caractere_matiere;
								$taille_min_police=ceil($taille_max_police/$cell_ajustee_texte_matiere_ratio_min_max);

								$largeur_dispo=$param_bull2016["largeur_acquis_col_1"];
								$h_cell=$hauteur_matiere/2-$Y_decal_cell_ajustee;

								cell_ajustee($texte,$pdf->GetX(),$pdf->GetY(),$largeur_dispo,$h_cell,$taille_max_police,$taille_min_police,'');
			
							}
							else {
								$val = $pdf->GetStringWidth($info_nom_matiere);
								$taille_texte = $param_bull2016["largeur_acquis_col_1"]-2;
								$grandeur_texte='test';
								while($grandeur_texte!='ok') {
									if($taille_texte<$val)
									{
										$hauteur_caractere_matiere = $hauteur_caractere_matiere-0.3;
										$pdf->SetFont('DejaVu','B',$hauteur_caractere_matiere);
										$val = $pdf->GetStringWidth($info_nom_matiere);
									}
									else {
										$grandeur_texte='ok';
									}
								}
								$grandeur_texte='test';
								$Y_decal=$y_courant;
								$pdf->SetXY($param_bull2016["x_acquis_col_1"], $Y_decal);
								$pdf->Cell($param_bull2016["largeur_acquis_col_1"], $hauteur_matiere/2, ($info_nom_matiere),'',1,'L');
							}
			

							// On note l'ordonnée pour le nom des professeurs
							$Y_decal = $y_courant+($hauteur_matiere/2);
							$pdf->SetXY($param_bull2016["x_acquis_col_1"], $Y_decal);
							$pdf->SetFont('DejaVu','',8);

							// nom des professeurs
							if ( isset($tab_bull['aid'][$m]['profs']['list'][0]) )
							{

								// Présentation en ligne des profs
								// On n'a pas forcément le formatage choisi pour la classe...
								//$text_prof=$tab_bull['groupe'][$m]["profs"]["proflist_string"]."  ";
								$text_prof="";
								for($loop_prof_grp=0;$loop_prof_grp<count($tab_bull['aid'][$m]['profs']['list']);$loop_prof_grp++) {
									$tmp_login_prof=$tab_bull['aid'][$m]['profs']['list'][$loop_prof_grp];
									if($loop_prof_grp>0) {$text_prof.=", ";}
									$text_prof.=affiche_utilisateur($tmp_login_prof,$tab_bull['id_classe']);
								}

								if($text_prof!="") {
									//$espace_matiere_prof = $espace_entre_matier/2;
									$espace_matiere_prof = $hauteur_matiere/2;
									$hauteur_caractere_prof = 7;

									$texte=$text_prof;
									$taille_max_police=$hauteur_caractere_prof;
									$taille_min_police=ceil($hauteur_caractere_prof/3);

									$largeur_dispo=$param_bull2016["largeur_acquis_col_1"];
									$h_cell=$espace_matiere_prof;

									$pdf->SetX($param_bull2016["x_acquis_col_1"]);

									cell_ajustee(($texte),$pdf->GetX(),$pdf->GetY(),$largeur_dispo,$h_cell,$taille_max_police,$taille_min_police,'');
								}
							}


							// Colonne 2 : Appréciation
							$pdf->SetFillColor($param_bull2016["couleur_acquis_ligne_alt".($cpt_matiere%2+1)]["R"], $param_bull2016["couleur_acquis_ligne_alt".($cpt_matiere%2+1)]["V"], $param_bull2016["couleur_acquis_ligne_alt".($cpt_matiere%2+1)]["B"]);
							$pdf->Rect($param_bull2016["x_acquis_col_appreciation_app_classe"], $y_courant, $param_bull2016["largeur_acquis_col_appreciation_app_classe"], $hauteur_matiere, 'F');

							$pdf->SetFillColor(0, 0, 0);
							$pdf->SetXY($param_bull2016["x_acquis_col_appreciation_app_classe"], $y_courant);

							// calcul de la taille du texte des appréciations
							$hauteur_caractere_appreciation = 8;
							$pdf->SetFont('DejaVu','',$hauteur_caractere_appreciation);

							//suppression des espaces en début et en fin
							//$app_aff = trim($tab_bull['app'][$m][$i]);
							$app_aff="";
							if($tab_bull['aid'][$m]['message']!='') {
								$app_aff.=$tab_bull['aid'][$m]['message'];
							}
							//if($app_aff!='') {$app_aff.=" ";}
							if(($tab_bull['aid'][$m]['display_nom']=='y')&&($tab_bull['aid'][$m]['aid_nom']!='')) {
								if($app_aff!='') {$app_aff.=" ";}
								$app_aff.=$tab_bull['aid'][$m]['aid_nom'];
							}
							if($app_aff!='') {$app_aff.="\n";}
							$app_aff.=trim($tab_bull['aid'][$m]['app_grp']);

							fich_debug_bull("__________________________________________\n");
							fich_debug_bull("$app_aff\n");
							fich_debug_bull("__________________________________________\n");

							// DEBUT AJUSTEMENT TAILLE APPRECIATION
							$taille_texte_total = $pdf->GetStringWidth($app_aff);
							//$largeur_appreciation2 = $largeur_appreciation - $largeur_sous_matiere;
							$largeur_appreciation2=$param_bull2016["largeur_acquis_col_appreciation_app_classe"];

							if($use_cell_ajustee=="n") {
								//$taille_texte = (($espace_entre_matier/3)*$largeur_appreciation2);
								$nb_ligne_app = '2.8';
								//$nb_ligne_app = '3.8';
								//$nb_ligne_app = '4.8';
								$taille_texte_max = $nb_ligne_app * ($largeur_appreciation2-4);
								//$taille_texte_max = $nb_ligne_app * ($largeur_appreciation2);
								$grandeur_texte='test';

								fich_debug_bull("\$taille_texte_total=$taille_texte_total\n");
								fich_debug_bull("\$largeur_appreciation2=$largeur_appreciation2\n");
								fich_debug_bull("\$nb_ligne_app=$nb_ligne_app\n");
								//fich_debug_bull("\$taille_texte_max = \$nb_ligne_app * (\$largeur_appreciation2-4)=$nb_ligne_app * ($largeur_appreciation2-4)=$taille_texte_max\n");
								fich_debug_bull("\$taille_texte_max = \$nb_ligne_app * (\$largeur_appreciation2)=$nb_ligne_app * ($largeur_appreciation2)=$taille_texte_max\n");

								while($grandeur_texte!='ok') {
									if($taille_texte_max < $taille_texte_total)
									{
										$hauteur_caractere_appreciation = $hauteur_caractere_appreciation-0.3;
										//$hauteur_caractere_appreciation = $hauteur_caractere_appreciation-0.1;
										$pdf->SetFont('DejaVu','',$hauteur_caractere_appreciation);
										$taille_texte_total = $pdf->GetStringWidth($app_aff);
									}
									else {
										$grandeur_texte='ok';
									}
								}
								$grandeur_texte='test';
								$pdf->drawTextBox(($app_aff), $largeur_appreciation2, $hauteur_matiere, 'J', 'M', 1);
							}
							else {
								$texte=$app_aff;
								//$texte="Bla bla\nbli ".$app_aff;
								$taille_max_police=$hauteur_caractere_appreciation;
								$taille_min_police=ceil($taille_max_police/3);

								$largeur_dispo=$largeur_appreciation2;
								$h_cell=$hauteur_matiere;

								if(getSettingValue('suppr_balises_app_prof')=='y') {$texte=preg_replace('/<(.*)>/U','',$texte);}
								cell_ajustee(($texte),$pdf->GetX(),$pdf->GetY(),$largeur_dispo,$h_cell,$taille_max_police,$taille_min_police,'');
							}


							// Colonne 3 : Moyenne classe
							$pdf->SetFillColor($param_bull2016["couleur_acquis_ligne_alt".($cpt_matiere%2+1)]["R"], $param_bull2016["couleur_acquis_ligne_alt".($cpt_matiere%2+1)]["V"], $param_bull2016["couleur_acquis_ligne_alt".($cpt_matiere%2+1)]["B"]);
							$pdf->Rect($param_bull2016["x_acquis_col_moyclasse"], $y_courant, $param_bull2016["largeur_acquis_col_moyclasse"], $hauteur_matiere, 'F');

							if ($param_bull2016["bull2016_moyminclassemax"]=='y') {
								$chaine_minclassemax="";
								// Min
								if (($tab_bull['aid'][$m]['aid_note_min']=="-")||($tab_bull['aid'][$m]['aid_note_min']=="")) {
									$valeur = "-";
								} else {
									$valeur = present_nombre($tab_bull['aid'][$m]['aid_note_min'], $arrondi, $nb_chiffre_virgule, $chiffre_avec_zero);
								}
								$chaine_minclassemax.=$valeur."\n";

								// Classe
								if (($tab_bull['aid'][$m]['aid_note_moyenne']=="-")||($tab_bull['aid'][$m]['aid_note_moyenne']=="")) {
									$valeur = "-";
								}
								else {
									$valeur = present_nombre($tab_bull['aid'][$m]['aid_note_moyenne'], $arrondi, $nb_chiffre_virgule, $chiffre_avec_zero);
								}
								$chaine_minclassemax.=$valeur."\n";

								if (($tab_bull['aid'][$m]['aid_note_max']=="-")||($tab_bull['aid'][$m]['aid_note_max']=="")) {
									$valeur = "-";
								} else {
									$valeur = present_nombre($tab_bull['aid'][$m]['aid_note_max'], $arrondi, $nb_chiffre_virgule, $chiffre_avec_zero);
								}
								$chaine_minclassemax.=$valeur;

								// Affichage min/classe/max avec cell_ajustee()
								$pdf->SetTextColor(0, 0, 0);
								$pdf->SetXY($param_bull2016["x_acquis_col_moyclasse"], $y_courant);
								$texte=$chaine_minclassemax;
								$taille_max_police=7;
								$taille_min_police=ceil($taille_max_police/3);

								$largeur_dispo=$param_bull2016["largeur_acquis_col_moyclasse"];
								$h_cell=$hauteur_matiere;

								//if(getSettingValue('suppr_balises_app_prof')=='y') {$texte=preg_replace('/<(.*)>/U','',$texte);}
								cell_ajustee(($texte),$pdf->GetX(),$pdf->GetY(),$largeur_dispo,$h_cell,$taille_max_police,$taille_min_police, '', 'C', 'C');
							}
							else {
								$pdf->SetTextColor(0, 0, 0);
								$pdf->SetXY($param_bull2016["x_acquis_col_moyclasse"], $y_courant);
								$pdf->SetFont('DejaVu','',7);

								if (($tab_bull['aid'][$m]['aid_note_moyenne']=="-")||($tab_bull['aid'][$m]['aid_note_moyenne']=="")) {
									$valeur = "-";
								}
								else {
									$valeur = present_nombre($tab_bull['aid'][$m]['aid_note_moyenne'], $arrondi, $nb_chiffre_virgule, $chiffre_avec_zero);
								}

								$pdf->Cell($param_bull2016["largeur_acquis_col_moyclasse"], $hauteur_matiere, $valeur,'',0,'C');
							}

							$y_courant+=$hauteur_matiere+0.5;

							$cpt_matiere++;

						}
					}
				}
			}
		}
		else {
			if(isset($tab_bull['eleve'][$i]['aid_b'])) {
				for($m=0;$m<count($tab_bull['eleve'][$i]['aid_b']);$m++) {
					if($tab_bull['eleve'][$i]['aid_b'][$m]["type_aid"]==0) {
						// Colonne 1 : Matière, prof

						$pdf->SetFillColor($param_bull2016["couleur_acquis_ligne_alt".($cpt_matiere%2+1)]["R"], $param_bull2016["couleur_acquis_ligne_alt".($cpt_matiere%2+1)]["V"], $param_bull2016["couleur_acquis_ligne_alt".($cpt_matiere%2+1)]["B"]);
						$pdf->Rect($param_bull2016["x_acquis_col_1"], $y_courant, $param_bull2016["largeur_acquis_col_1"], $hauteur_matiere, 'F');
						$pdf->SetFillColor(0, 0, 0);

						$pdf->SetTextColor(0, 0, 0);
						$pdf->SetXY($param_bull2016["x_acquis_col_1"], $y_courant);
						$pdf->SetFont('DejaVu','',8);
						/*
						//$pdf->Cell($param_bull2016["largeur_bandeau_suivi_acquis"],7, "Suivi des acquis scolaires de l'élève",0,2,'C');
						$texte="Matiere $m ".$hauteur_matiere;
						$pdf->drawTextBox($texte, $param_bull2016["largeur_acquis_col_1"], $hauteur_matiere, 'L', 'M', 0);
						*/

						if($tab_bull['eleve'][$i]['aid_b'][$m]['display_nom']=='z') {
							if((isset($tab_bull['eleve'][$i]['aid_b'][$m]['aid_nom']))&&($tab_bull['eleve'][$i]['aid_b'][$m]['aid_nom']!="")) {
								$info_nom_matiere=$tab_bull['eleve'][$i]['aid_b'][$m]['aid_nom'];
							}
							elseif((isset($tab_bull['eleve'][$i]['aid_b'][$m]['nom_complet']))&&($tab_bull['eleve'][$i]['aid_b'][$m]['nom_complet']!="")) {
								$info_nom_matiere=$tab_bull['eleve'][$i]['aid_b'][$m]['nom_complet'];
							}
							else {
								$info_nom_matiere=$tab_bull['eleve'][$i]['aid_b'][$m]['nom'];
							}
						}
						elseif($tab_bull['eleve'][$i]['aid_b'][$m]['display_nom']=='x') {
							if((isset($tab_bull['eleve'][$i]['aid_b'][$m]['nom']))&&($tab_bull['eleve'][$i]['aid_b'][$m]['nom']!="")) {
								$info_nom_matiere=$tab_bull['eleve'][$i]['aid_b'][$m]['nom'];
							}
							else {
								$info_nom_matiere=$tab_bull['eleve'][$i]['aid_b'][$m]['nom_complet'];
							}

							if((isset($tab_bull['eleve'][$i]['aid_b'][$m]['aid_nom']))&&($tab_bull['eleve'][$i]['aid_b'][$m]['aid_nom']!="")) {
								if($info_nom_matiere!="") {
									$info_nom_matiere.=": ";
								}
								$info_nom_matiere.=$tab_bull['eleve'][$i]['aid_b'][$m]['aid_nom'];
							}
						}
						else {
							if((isset($tab_bull['eleve'][$i]['aid_b'][$m]['nom_complet']))&&($tab_bull['eleve'][$i]['aid_b'][$m]['nom_complet']!="")) {
								$info_nom_matiere=$tab_bull['eleve'][$i]['aid_b'][$m]['nom_complet'];
							}
							else {
								$info_nom_matiere=$tab_bull['eleve'][$i]['aid_b'][$m]['nom'];
							}
						}
						if($info_nom_matiere=="") {
							$info_nom_matiere="AID";
						}

						$hauteur_caractere_matiere=8;
						$cell_ajustee_texte_matiere_ratio_min_max=3;

						// 20130927 : cell_ajustee() ou pas sur le nom de matière/enseignement
						$cell_ajustee_texte_matiere=1;
						if((isset($cell_ajustee_texte_matiere))&&($cell_ajustee_texte_matiere==1)) {
							// On met un décalage pour ne pas coller le texte à la bordure
							$Y_decal_cell_ajustee=2;
							$pdf->SetXY($param_bull2016["x_acquis_col_1"], $y_courant+$Y_decal_cell_ajustee);

							$texte=$info_nom_matiere;
							$taille_max_police=$hauteur_caractere_matiere;
							$taille_min_police=ceil($taille_max_police/$cell_ajustee_texte_matiere_ratio_min_max);

							$largeur_dispo=$param_bull2016["largeur_acquis_col_1"];
							$h_cell=$hauteur_matiere/2-$Y_decal_cell_ajustee;

							cell_ajustee($texte,$pdf->GetX(),$pdf->GetY(),$largeur_dispo,$h_cell,$taille_max_police,$taille_min_police,'');
			
						}
						else {
							$val = $pdf->GetStringWidth($info_nom_matiere);
							$taille_texte = $param_bull2016["largeur_acquis_col_1"]-2;
							$grandeur_texte='test';
							while($grandeur_texte!='ok') {
								if($taille_texte<$val)
								{
									$hauteur_caractere_matiere = $hauteur_caractere_matiere-0.3;
									$pdf->SetFont('DejaVu','B',$hauteur_caractere_matiere);
									$val = $pdf->GetStringWidth($info_nom_matiere);
								}
								else {
									$grandeur_texte='ok';
								}
							}
							$grandeur_texte='test';
							$Y_decal=$y_courant;
							$pdf->SetXY($param_bull2016["x_acquis_col_1"], $Y_decal);
							$pdf->Cell($param_bull2016["largeur_acquis_col_1"], $hauteur_matiere/2, ($info_nom_matiere),'',1,'L');
						}
			

						// On note l'ordonnée pour le nom des professeurs
						$Y_decal = $y_courant+($hauteur_matiere/2);
						$pdf->SetXY($param_bull2016["x_acquis_col_1"], $Y_decal);
						$pdf->SetFont('DejaVu','',8);

						// nom des professeurs
						if ( isset($tab_bull['eleve'][$i]['aid_b'][$m]['aid_prof_resp_login'][0]) )
						{

							// Présentation en ligne des profs
							// On n'a pas forcément le formatage choisi pour la classe...
							//$text_prof=$tab_bull['groupe'][$m]["profs"]["proflist_string"]."  ";
							$text_prof="";
							for($loop_prof_grp=0;$loop_prof_grp<count($tab_bull['eleve'][$i]['aid_b'][$m]['aid_prof_resp_login']);$loop_prof_grp++) {
								$tmp_login_prof=$tab_bull['eleve'][$i]['aid_b'][$m]['aid_prof_resp_login'][$loop_prof_grp];
								if($loop_prof_grp>0) {$text_prof.=", ";}
								$text_prof.=affiche_utilisateur($tmp_login_prof,$tab_bull['eleve'][$i]['id_classe']);
							}

							if($text_prof!="") {
								//$espace_matiere_prof = $espace_entre_matier/2;
								$espace_matiere_prof = $hauteur_matiere/2;
								$hauteur_caractere_prof = 7;

								$texte=$text_prof;
								$taille_max_police=$hauteur_caractere_prof;
								$taille_min_police=ceil($hauteur_caractere_prof/3);

								$largeur_dispo=$param_bull2016["largeur_acquis_col_1"];
								$h_cell=$espace_matiere_prof;

								$pdf->SetX($param_bull2016["x_acquis_col_1"]);

								cell_ajustee(($texte),$pdf->GetX(),$pdf->GetY(),$largeur_dispo,$h_cell,$taille_max_police,$taille_min_police,'');
							}
						}


						// Colonne 2 : Appréciation
						$pdf->SetFillColor($param_bull2016["couleur_acquis_ligne_alt".($cpt_matiere%2+1)]["R"], $param_bull2016["couleur_acquis_ligne_alt".($cpt_matiere%2+1)]["V"], $param_bull2016["couleur_acquis_ligne_alt".($cpt_matiere%2+1)]["B"]);
						$pdf->Rect($param_bull2016["x_acquis_col_appreciation_app_classe"], $y_courant, $param_bull2016["largeur_acquis_col_appreciation_app_classe"], $hauteur_matiere, 'F');

						$pdf->SetFillColor(0, 0, 0);
						$pdf->SetXY($param_bull2016["x_acquis_col_appreciation_app_classe"], $y_courant);

						// calcul de la taille du texte des appréciations
						$hauteur_caractere_appreciation = 8;
						$pdf->SetFont('DejaVu','',$hauteur_caractere_appreciation);

						//suppression des espaces en début et en fin
						//$app_aff = trim($tab_bull['app'][$m][$i]);
						$app_aff="";
						if($tab_bull['eleve'][$i]['aid_b'][$m]['message']!='') {
							$app_aff.=$tab_bull['eleve'][$i]['aid_b'][$m]['message'];
						}
						//if($app_aff!='') {$app_aff.=" ";}
						if(($tab_bull['eleve'][$i]['aid_b'][$m]['display_nom']=='y')&&($tab_bull['eleve'][$i]['aid_b'][$m]['aid_nom']!='')) {
							if($app_aff!='') {$app_aff.=" ";}
							$app_aff.=$tab_bull['eleve'][$i]['aid_b'][$m]['aid_nom'];
						}
						if($app_aff!='') {$app_aff.="\n";}
						$app_aff.=trim($tab_bull['aid'][$m]['app_grp']);

						fich_debug_bull("__________________________________________\n");
						fich_debug_bull("$app_aff\n");
						fich_debug_bull("__________________________________________\n");

						// DEBUT AJUSTEMENT TAILLE APPRECIATION
						$taille_texte_total = $pdf->GetStringWidth($app_aff);
						//$largeur_appreciation2 = $largeur_appreciation - $largeur_sous_matiere;
						$largeur_appreciation2=$param_bull2016["largeur_acquis_col_appreciation_app_classe"];

						if($use_cell_ajustee=="n") {
							//$taille_texte = (($espace_entre_matier/3)*$largeur_appreciation2);
							$nb_ligne_app = '2.8';
							//$nb_ligne_app = '3.8';
							//$nb_ligne_app = '4.8';
							$taille_texte_max = $nb_ligne_app * ($largeur_appreciation2-4);
							//$taille_texte_max = $nb_ligne_app * ($largeur_appreciation2);
							$grandeur_texte='test';

							fich_debug_bull("\$taille_texte_total=$taille_texte_total\n");
							fich_debug_bull("\$largeur_appreciation2=$largeur_appreciation2\n");
							fich_debug_bull("\$nb_ligne_app=$nb_ligne_app\n");
							//fich_debug_bull("\$taille_texte_max = \$nb_ligne_app * (\$largeur_appreciation2-4)=$nb_ligne_app * ($largeur_appreciation2-4)=$taille_texte_max\n");
							fich_debug_bull("\$taille_texte_max = \$nb_ligne_app * (\$largeur_appreciation2)=$nb_ligne_app * ($largeur_appreciation2)=$taille_texte_max\n");

							while($grandeur_texte!='ok') {
								if($taille_texte_max < $taille_texte_total)
								{
									$hauteur_caractere_appreciation = $hauteur_caractere_appreciation-0.3;
									//$hauteur_caractere_appreciation = $hauteur_caractere_appreciation-0.1;
									$pdf->SetFont('DejaVu','',$hauteur_caractere_appreciation);
									$taille_texte_total = $pdf->GetStringWidth($app_aff);
								}
								else {
									$grandeur_texte='ok';
								}
							}
							$grandeur_texte='test';
							$pdf->drawTextBox(($app_aff), $largeur_appreciation2, $hauteur_matiere, 'J', 'M', 1);
						}
						else {
							$texte=$app_aff;
							//$texte="Bla bla\nbli ".$app_aff;
							$taille_max_police=$hauteur_caractere_appreciation;
							$taille_min_police=ceil($taille_max_police/3);

							$largeur_dispo=$largeur_appreciation2;
							$h_cell=$hauteur_matiere;

							if(getSettingValue('suppr_balises_app_prof')=='y') {$texte=preg_replace('/<(.*)>/U','',$texte);}
							cell_ajustee(($texte),$pdf->GetX(),$pdf->GetY(),$largeur_dispo,$h_cell,$taille_max_police,$taille_min_police,'');
						}


						// Colonne 3 : Moyenne classe
						$pdf->SetFillColor($param_bull2016["couleur_acquis_ligne_alt".($cpt_matiere%2+1)]["R"], $param_bull2016["couleur_acquis_ligne_alt".($cpt_matiere%2+1)]["V"], $param_bull2016["couleur_acquis_ligne_alt".($cpt_matiere%2+1)]["B"]);
						$pdf->Rect($param_bull2016["x_acquis_col_moyclasse"], $y_courant, $param_bull2016["largeur_acquis_col_moyclasse"], $hauteur_matiere, 'F');

						if ($param_bull2016["bull2016_moyminclassemax"]=='y') {
							$chaine_minclassemax="";
							// Min
							if (($tab_bull['eleve'][$i]['aid_b'][$m]['aid_note_min']=="-")||($tab_bull['eleve'][$i]['aid_b'][$m]['aid_note_min']=="")) {
								$valeur = "-";
							} else {
								$valeur = present_nombre($tab_bull['eleve'][$i]['aid_b'][$m]['aid_note_min'], $arrondi, $nb_chiffre_virgule, $chiffre_avec_zero);
							}
							$chaine_minclassemax.=$valeur."\n";

							// Classe
							if (($tab_bull['eleve'][$i]['aid_b'][$m]['aid_note_moyenne']=="-")||($tab_bull['eleve'][$i]['aid_b'][$m]['aid_note_moyenne']=="")) {
								$valeur = "-";
							}
							else {
								$valeur = present_nombre($tab_bull['eleve'][$i]['aid_b'][$m]['aid_note_moyenne'], $arrondi, $nb_chiffre_virgule, $chiffre_avec_zero);
							}
							$chaine_minclassemax.=$valeur."\n";

							if (($tab_bull['eleve'][$i]['aid_b'][$m]['aid_note_max']=="-")||($tab_bull['eleve'][$i]['aid_b'][$m]['aid_note_max']=="")) {
								$valeur = "-";
							} else {
								$valeur = present_nombre($tab_bull['eleve'][$i]['aid_b'][$m]['aid_note_max'], $arrondi, $nb_chiffre_virgule, $chiffre_avec_zero);
							}
							$chaine_minclassemax.=$valeur;

							// Affichage min/classe/max avec cell_ajustee()
							$pdf->SetTextColor(0, 0, 0);
							$pdf->SetXY($param_bull2016["x_acquis_col_moyclasse"], $y_courant);
							$texte=$chaine_minclassemax;
							$taille_max_police=7;
							$taille_min_police=ceil($taille_max_police/3);

							$largeur_dispo=$param_bull2016["largeur_acquis_col_moyclasse"];
							$h_cell=$hauteur_matiere;

							//if(getSettingValue('suppr_balises_app_prof')=='y') {$texte=preg_replace('/<(.*)>/U','',$texte);}
							cell_ajustee(($texte),$pdf->GetX(),$pdf->GetY(),$largeur_dispo,$h_cell,$taille_max_police,$taille_min_police, '', 'C', 'C');
						}
						else {
							$pdf->SetTextColor(0, 0, 0);
							$pdf->SetXY($param_bull2016["x_acquis_col_moyclasse"], $y_courant);
							$pdf->SetFont('DejaVu','',7);

							if (($tab_bull['eleve'][$i]['aid_b'][$m]['aid_note_moyenne']=="-")||($tab_bull['eleve'][$i]['aid_b'][$m]['aid_note_moyenne']=="")) {
								$valeur = "-";
							}
							else {
								$valeur = present_nombre($tab_bull['eleve'][$i]['aid_b'][$m]['aid_note_moyenne'], $arrondi, $nb_chiffre_virgule, $chiffre_avec_zero);
							}

							$pdf->Cell($param_bull2016["largeur_acquis_col_moyclasse"], $hauteur_matiere, $valeur,'',0,'C');
						}

						$y_courant+=$hauteur_matiere+0.5;

						$cpt_matiere++;
					}
				}
			}
		}
	}
	// Fin des AID_b

	// Début des enseignements de la table 'groupes'
	for($m=0;$m<count($tab_bull['groupe']);$m++) {
		//echo "plop";
		if("$i"=="") {
			//echo "plip";
			if((!isset($tab_bull['groupe'][$m]['type_grp'][0]))||($tab_bull['groupe'][$m]['type_grp'][0]['nom_court']=="local")) {

				// Colonne 1 : Matière, prof

				$pdf->SetFillColor($param_bull2016["couleur_acquis_ligne_alt".($cpt_matiere%2+1)]["R"], $param_bull2016["couleur_acquis_ligne_alt".($cpt_matiere%2+1)]["V"], $param_bull2016["couleur_acquis_ligne_alt".($cpt_matiere%2+1)]["B"]);
				$pdf->Rect($param_bull2016["x_acquis_col_1"], $y_courant, $param_bull2016["largeur_acquis_col_1"], $hauteur_matiere, 'F');
				$pdf->SetFillColor(0, 0, 0);

				$pdf->SetTextColor(0, 0, 0);
				$pdf->SetXY($param_bull2016["x_acquis_col_1"], $y_courant);
				$pdf->SetFont('DejaVu','',8);
				/*
				//$pdf->Cell($param_bull2016["largeur_bandeau_suivi_acquis"],7, "Suivi des acquis scolaires de l'élève",0,2,'C');
				$texte="Matiere $m ".$hauteur_matiere;
				$pdf->drawTextBox($texte, $param_bull2016["largeur_acquis_col_1"], $hauteur_matiere, 'L', 'M', 0);
				*/

				if(getSettingValue('bul_rel_nom_matieres')=='nom_groupe') {
					$info_nom_matiere=$tab_bull['groupe'][$m]['name'];
				}
				elseif(getSettingValue('bul_rel_nom_matieres')=='description_groupe') {
					$info_nom_matiere=$tab_bull['groupe'][$m]['description'];
				}
				else {
					// Pour parer au bug sur la suppression de matière alors que des groupes sont conservés:
					if(isset($tab_bull['groupe'][$m]['matiere']['nom_complet'])) {
						$info_nom_matiere=$tab_bull['groupe'][$m]['matiere']['nom_complet'];
					}
					else {
						$info_nom_matiere=$tab_bull['groupe'][$m]['name']." (".$tab_bull['groupe'][$m]['id'].")";
					}
				}

				$hauteur_caractere_matiere=8;
				$cell_ajustee_texte_matiere_ratio_min_max=3;

				// 20130927 : cell_ajustee() ou pas sur le nom de matière/enseignement
				$cell_ajustee_texte_matiere=1;
				if((isset($cell_ajustee_texte_matiere))&&($cell_ajustee_texte_matiere==1)) {
					// Encadrement
					//$pdf->Cell($tab_modele_pdf["largeur_matiere"][$classe_id], $espace_entre_matier, "",'LRBT',1,'L');

					// cell_ajustee() ne centre pas verticalement le texte.
					// On met un décalage pour ne pas coller le texte à la bordure
					$Y_decal_cell_ajustee=2;
					// On repositionne et on inscrit le nom de matière sur la moitié de la hauteur de la cellule
					//$pdf->SetXY($X_bloc_matiere, $Y_decal+$Y_decal_cell_ajustee);
					//$pdf->SetXY($param_bull2016["x_acquis_col_1"], $y_courant);

					$pdf->SetXY($param_bull2016["x_acquis_col_1"], $y_courant+$Y_decal_cell_ajustee);

					$texte=$info_nom_matiere;
					$taille_max_police=$hauteur_caractere_matiere;
					//$taille_min_police=ceil($taille_max_police/$tab_modele_pdf["cell_ajustee_texte_matiere_ratio_min_max"][$classe_id]);
					$taille_min_police=ceil($taille_max_police/$cell_ajustee_texte_matiere_ratio_min_max);

					$largeur_dispo=$param_bull2016["largeur_acquis_col_1"];
					//$h_cell=$espace_entre_matier/2-$Y_decal_cell_ajustee;
					$h_cell=$hauteur_matiere/2-$Y_decal_cell_ajustee;

					//cell_ajustee("<b>".$texte."</b>",$pdf->GetX(),$pdf->GetY(),$largeur_dispo,$h_cell,$taille_max_police,$taille_min_police,'');
					cell_ajustee($texte,$pdf->GetX(),$pdf->GetY(),$largeur_dispo,$h_cell,$taille_max_police,$taille_min_police,'');
			
				}
				else {
					$val = $pdf->GetStringWidth($info_nom_matiere);
					$taille_texte = $param_bull2016["largeur_acquis_col_1"]-2;
					$grandeur_texte='test';
					while($grandeur_texte!='ok') {
						if($taille_texte<$val)
						{
							$hauteur_caractere_matiere = $hauteur_caractere_matiere-0.3;
							$pdf->SetFont('DejaVu','B',$hauteur_caractere_matiere);
							$val = $pdf->GetStringWidth($info_nom_matiere);
						}
						else {
							$grandeur_texte='ok';
						}
					}
					$grandeur_texte='test';
					$Y_decal=$y_courant;
					$pdf->SetXY($param_bull2016["x_acquis_col_1"], $Y_decal);
					$pdf->Cell($param_bull2016["largeur_acquis_col_1"], $hauteur_matiere/2, ($info_nom_matiere),'',1,'L');
				}


				// On note l'ordonnée pour le nom des professeurs
				$Y_decal = $y_courant+($hauteur_matiere/2);
				$pdf->SetXY($param_bull2016["x_acquis_col_1"], $Y_decal);
				$pdf->SetFont('DejaVu','',8);

				//fich_debug_bull("\$info_nom_matiere=$info_nom_matiere\n");
				//fich_debug_bull("Le nom de matière est écrit; on est à mi-hauteur de la cellule pour écrire le nom du prof:\n");
				//fich_debug_bull("\$Y_decal=$Y_decal\n");

				// nom des professeurs

				if ( isset($tab_bull['groupe'][$m]["profs"]["list"]) )
				{
					/*
					if($tab_modele_pdf["presentation_proflist"][$classe_id]!="2") {
						// Présentation en colonne des profs
						$nb_prof_matiere = count($tab_bull['groupe'][$m]["profs"]["list"]);
						$espace_matiere_prof = $espace_entre_matier/2;
						if($nb_prof_matiere>0){
							$espace_matiere_prof = $espace_matiere_prof/$nb_prof_matiere;
						}
						$nb_pass_count = '0';
						$text_prof = '';
						while ($nb_prof_matiere > $nb_pass_count)
						{
							$tmp_login_prof=$tab_bull['groupe'][$m]["profs"]["list"][$nb_pass_count];
							$text_prof=affiche_utilisateur($tmp_login_prof,$tab_bull['eleve'][$i]['id_classe']);

							if ( $nb_prof_matiere <= 2 ) { $hauteur_caractere_prof = 8; }
							elseif ( $nb_prof_matiere == 3) { $hauteur_caractere_prof = 5; }
							elseif ( $nb_prof_matiere > 3) { $hauteur_caractere_prof = 2; }
							$pdf->SetFont('DejaVu','',$hauteur_caractere_prof);
							$val = $pdf->GetStringWidth($text_prof);
							$taille_texte = ($tab_modele_pdf["largeur_matiere"][$classe_id]);
							$grandeur_texte='test';
							while($grandeur_texte!='ok') {
								if($taille_texte<$val)
								{
									$hauteur_caractere_prof = $hauteur_caractere_prof-0.3;
									$pdf->SetFont('DejaVu','',$hauteur_caractere_prof);
									$val = $pdf->GetStringWidth($text_prof);
								}
								else {
									$grandeur_texte='ok';
								}
							}
							$grandeur_texte='test';
							$pdf->SetX($X_bloc_matiere);
							if( empty($tab_bull['groupe'][$m]["profs"]["list"][$nb_pass_count+1]) ) {
								$pdf->Cell($tab_modele_pdf["largeur_matiere"][$classe_id], $espace_matiere_prof, ($text_prof),'LRB',1,'L');
							}
							if( !empty($tab_bull['groupe'][$m]["profs"]["list"][$nb_pass_count+1]) ) {
								$pdf->Cell($tab_modele_pdf["largeur_matiere"][$classe_id], $espace_matiere_prof, ($text_prof),'LR',1,'L');
							}
							$nb_pass_count = $nb_pass_count + 1;
						}
					}
					else {
					*/
						// Présentation en ligne des profs
						// On n'a pas forcément le formatage choisi pour la classe...
						//$text_prof=$tab_bull['groupe'][$m]["profs"]["proflist_string"]."  ";
						$text_prof="";
						for($loop_prof_grp=0;$loop_prof_grp<count($tab_bull['groupe'][$m]["profs"]["list"]);$loop_prof_grp++) {
							$tmp_login_prof=$tab_bull['groupe'][$m]["profs"]["list"][$loop_prof_grp];
							if($loop_prof_grp>0) {$text_prof.=", ";}
							$text_prof.=affiche_utilisateur($tmp_login_prof,$tab_bull['id_classe']);
						}

						if($text_prof!="") {
							//$espace_matiere_prof = $espace_entre_matier/2;
							$espace_matiere_prof = $hauteur_matiere/2;
							$hauteur_caractere_prof = 7;

							/*
							if($use_cell_ajustee=="n") {
								$pdf->SetFont('DejaVu','',$hauteur_caractere_prof);
								$val = $pdf->GetStringWidth($text_prof);
								$taille_texte = ($tab_modele_pdf["largeur_matiere"][$classe_id]);
								$grandeur_texte='test';
								while($grandeur_texte!='ok') {
									if($taille_texte<$val)
									{
										$hauteur_caractere_prof = $hauteur_caractere_prof-0.3;
										$pdf->SetFont('DejaVu','',$hauteur_caractere_prof);
										$val = $pdf->GetStringWidth($text_prof);
									}
									else {
										$grandeur_texte='ok';
									}
								}
								$grandeur_texte='test';
								$pdf->SetX($X_bloc_matiere);
								$pdf->Cell($tab_modele_pdf["largeur_matiere"][$classe_id], $espace_matiere_prof, ($text_prof),'LR',1,'L');
							}
							else {
							*/
								$texte=$text_prof;
								$taille_max_police=$hauteur_caractere_prof;
								$taille_min_police=ceil($hauteur_caractere_prof/3);

								$largeur_dispo=$param_bull2016["largeur_acquis_col_1"];
								$h_cell=$espace_matiere_prof;

								$pdf->SetX($param_bull2016["x_acquis_col_1"]);

								cell_ajustee(($texte),$pdf->GetX(),$pdf->GetY(),$largeur_dispo,$h_cell,$taille_max_police,$taille_min_police,'');
							//}
						}
					//}
				}


				// Colonne 2 : Appréciation
				$pdf->SetFillColor($param_bull2016["couleur_acquis_ligne_alt".($cpt_matiere%2+1)]["R"], $param_bull2016["couleur_acquis_ligne_alt".($cpt_matiere%2+1)]["V"], $param_bull2016["couleur_acquis_ligne_alt".($cpt_matiere%2+1)]["B"]);
				$pdf->Rect($param_bull2016["x_acquis_col_appreciation_app_classe"], $y_courant, $param_bull2016["largeur_acquis_col_appreciation_app_classe"], $hauteur_matiere, 'F');

				$pdf->SetFillColor(0, 0, 0);
				$pdf->SetXY($param_bull2016["x_acquis_col_appreciation_app_classe"], $y_courant);


				$largeur_appreciation2=$param_bull2016["largeur_acquis_col_appreciation_app_classe"];
				//+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
				// 20161123
				// si on autorise l'affichage des sous matière et s'il y en a alors on les affiche
				//$id_groupe_select = $tab_bull['groupe'][$m]['id'];
				//$pdf->SetXY($param_bull2016["x_acquis_col_appreciation_app_classe"], $Y_decal-($espace_entre_matier/2));
				/*
				$X_sous_matiere = 0; $largeur_sous_matiere=0;

				// A MODIFIER POUR POUVOIR LIMITER LA LARGEUR, par exemple à 20mm

				if((isset($param_bull2016["bull2016_autorise_sous_matiere"]))&&($param_bull2016["bull2016_autorise_sous_matiere"]=="y")&&(!empty($tab_bull['groupe'][$m][$i]['cn_nom']))) {

					// Bordure blanche pour les sous-matières
					$pdf->SetDrawColor(255, 255, 255);

					$X_sous_matiere = $param_bull2016["x_acquis_col_appreciation_app_classe"];
					//$Y_sous_matiere = $y_courant-$hauteur_matiere/2;
					$Y_sous_matiere = $y_courant;
					$n=0;
					$largeur_texte_sousmatiere=0;
					$largeur_sous_matiere=0;
					while( !empty($tab_bull['groupe'][$m][$i]['cn_nom'][$n]) )
					{
						$pdf->SetFont('DejaVu','',6);
						$largeur_texte_sousmatiere = $pdf->GetStringWidth($tab_bull['groupe'][$m][$i]['cn_nom'][$n].': '.$tab_bull['groupe'][$m][$i]['cn_note'][$n]);
						if($largeur_sous_matiere<$largeur_texte_sousmatiere) { $largeur_sous_matiere=$largeur_texte_sousmatiere; }
						$n = $n + 1;
					}
					if($largeur_sous_matiere!='0') { $largeur_sous_matiere = $largeur_sous_matiere + 2; }
					$n=0;
					while( !empty($tab_bull['groupe'][$m][$i]['cn_nom'][$n]) )
					{
						$pdf->SetXY($X_sous_matiere, $Y_sous_matiere);
						$pdf->SetFont('DejaVu','',6);
						$pdf->Cell($largeur_sous_matiere, $hauteur_matiere/count($tab_bull['groupe'][$m][$i]['cn_nom']), ($tab_bull['groupe'][$m][$i]['cn_nom'][$n].': '.$tab_bull['groupe'][$m][$i]['cn_note'][$n]),1,0,'L');
						$Y_sous_matiere = $Y_sous_matiere+$hauteur_matiere/count($tab_bull['groupe'][$m][$i]['cn_nom']);
						$n = $n + 1;
					}

					$largeur_appreciation2=$param_bull2016["largeur_acquis_col_appreciation_app_classe"]-$largeur_sous_matiere;

					// Retour aux bordures noires par défaut, mais non dessinées en principe sur les bulletins PDF 2016
					$pdf->SetDrawColor(0, 0, 0);
				}
				//$pdf->SetXY($X_note_moy_app+$largeur_utilise, $Y_decal-($espace_entre_matier/2));
				$pdf->SetXY($param_bull2016["x_acquis_col_appreciation_app_classe"]+$largeur_sous_matiere, $y_courant);
				*/
				//+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

				// calcul de la taille du texte des appréciations
				$hauteur_caractere_appreciation = 8;
				$pdf->SetFont('DejaVu','',$hauteur_caractere_appreciation);

				//suppression des espaces en début et en fin
				$app_aff = trim($tab_bull['app_grp'][$m]);

				fich_debug_bull("__________________________________________\n");
				fich_debug_bull("$app_aff\n");
				fich_debug_bull("__________________________________________\n");

				// DEBUT AJUSTEMENT TAILLE APPRECIATION
				$taille_texte_total = $pdf->GetStringWidth($app_aff);
				//$largeur_appreciation2 = $largeur_appreciation - $largeur_sous_matiere;
				//$largeur_appreciation2=$param_bull2016["largeur_acquis_col_appreciation_app_classe"];

				if($use_cell_ajustee=="n") {
					//$taille_texte = (($espace_entre_matier/3)*$largeur_appreciation2);
					$nb_ligne_app = '2.8';
					//$nb_ligne_app = '3.8';
					//$nb_ligne_app = '4.8';
					$taille_texte_max = $nb_ligne_app * ($largeur_appreciation2-4);
					//$taille_texte_max = $nb_ligne_app * ($largeur_appreciation2);
					$grandeur_texte='test';

					fich_debug_bull("\$taille_texte_total=$taille_texte_total\n");
					fich_debug_bull("\$largeur_appreciation2=$largeur_appreciation2\n");
					fich_debug_bull("\$nb_ligne_app=$nb_ligne_app\n");
					//fich_debug_bull("\$taille_texte_max = \$nb_ligne_app * (\$largeur_appreciation2-4)=$nb_ligne_app * ($largeur_appreciation2-4)=$taille_texte_max\n");
					fich_debug_bull("\$taille_texte_max = \$nb_ligne_app * (\$largeur_appreciation2)=$nb_ligne_app * ($largeur_appreciation2)=$taille_texte_max\n");

					while($grandeur_texte!='ok') {
						if($taille_texte_max < $taille_texte_total)
						{
							$hauteur_caractere_appreciation = $hauteur_caractere_appreciation-0.3;
							//$hauteur_caractere_appreciation = $hauteur_caractere_appreciation-0.1;
							$pdf->SetFont('DejaVu','',$hauteur_caractere_appreciation);
							$taille_texte_total = $pdf->GetStringWidth($app_aff);
						}
						else {
							$grandeur_texte='ok';
						}
					}
					$grandeur_texte='test';
					$pdf->drawTextBox(($app_aff), $largeur_appreciation2, $hauteur_matiere, 'J', 'M', 1);
				}
				else {
					$texte=$app_aff;
					//$texte="Bla bla\nbli ".$app_aff;
					$taille_max_police=$hauteur_caractere_appreciation;
					$taille_min_police=ceil($taille_max_police/3);

					$largeur_dispo=$largeur_appreciation2;
					$h_cell=$hauteur_matiere;

					if(getSettingValue('suppr_balises_app_prof')=='y') {$texte=preg_replace('/<(.*)>/U','',$texte);}
					cell_ajustee(($texte),$pdf->GetX(),$pdf->GetY(),$largeur_dispo,$h_cell,$taille_max_police,$taille_min_police,'');
				}


				// Colonne 3 : Moyenne classe
				$pdf->SetFillColor($param_bull2016["couleur_acquis_ligne_alt".($cpt_matiere%2+1)]["R"], $param_bull2016["couleur_acquis_ligne_alt".($cpt_matiere%2+1)]["V"], $param_bull2016["couleur_acquis_ligne_alt".($cpt_matiere%2+1)]["B"]);
				$pdf->Rect($param_bull2016["x_acquis_col_moyclasse"], $y_courant, $param_bull2016["largeur_acquis_col_moyclasse"], $hauteur_matiere, 'F');

				if ($param_bull2016["bull2016_moyminclassemax"]=='y') {
					$chaine_minclassemax="";
					// Min
					if (($tab_bull['moy_min_classe_grp'][$m]=="-")||($tab_bull['moy_min_classe_grp'][$m]=="")) {
						$valeur = "-";
					} else {
						$valeur = present_nombre($tab_bull['moy_min_classe_grp'][$m], $arrondi, $nb_chiffre_virgule, $chiffre_avec_zero);
					}
					$chaine_minclassemax.=$valeur."\n";

					// Classe
					if (($tab_bull['moy_classe_grp'][$m]=="-")||($tab_bull['moy_classe_grp'][$m]=="")) {
						$valeur = "-";
					}
					else {
						$valeur = present_nombre($tab_bull['moy_classe_grp'][$m], $arrondi, $nb_chiffre_virgule, $chiffre_avec_zero);
					}
					$chaine_minclassemax.=$valeur."\n";

					if (($tab_bull['moy_max_classe_grp'][$m]=="-")||($tab_bull['moy_max_classe_grp'][$m]=="")) {
						$valeur = "-";
					} else {
						$valeur = present_nombre($tab_bull['moy_max_classe_grp'][$m], $arrondi, $nb_chiffre_virgule, $chiffre_avec_zero);
					}
					$chaine_minclassemax.=$valeur;

					// Affichage min/classe/max avec cell_ajustee()
					$pdf->SetTextColor(0, 0, 0);
					$pdf->SetXY($param_bull2016["x_acquis_col_moyclasse"], $y_courant);
					$texte=$chaine_minclassemax;
					$taille_max_police=7;
					$taille_min_police=ceil($taille_max_police/3);

					$largeur_dispo=$param_bull2016["largeur_acquis_col_moyclasse"];
					$h_cell=$hauteur_matiere;

					//if(getSettingValue('suppr_balises_app_prof')=='y') {$texte=preg_replace('/<(.*)>/U','',$texte);}
					cell_ajustee(($texte),$pdf->GetX(),$pdf->GetY(),$largeur_dispo,$h_cell,$taille_max_police,$taille_min_police, '', 'C', 'C');
				}
				else {
					// Moyenne classe seulement
					$pdf->SetTextColor(0, 0, 0);
					$pdf->SetXY($param_bull2016["x_acquis_col_moyclasse"], $y_courant);
					$pdf->SetFont('DejaVu','',7);
					if (($tab_bull['moy_classe_grp'][$m]=="-")||($tab_bull['moy_classe_grp'][$m]=="")) {
						$valeur = "-";
					} else {
						$valeur = present_nombre($tab_bull['moy_classe_grp'][$m], $arrondi, $nb_chiffre_virgule, $chiffre_avec_zero);
					}
					$pdf->Cell($param_bull2016["largeur_acquis_col_moyclasse"], $hauteur_matiere, $valeur,'',0,'C');
				}

				$y_courant+=$hauteur_matiere+0.5;

				$cpt_matiere++;

			}
		}
		else {
			// On n'affiche pas ici les groupes correspondant à AP, EPI ou Parcours
			if((isset($tab_bull['note'][$m][$i]))&&
			((!isset($tab_bull['groupe'][$m]['type_grp'][0]))||($tab_bull['groupe'][$m]['type_grp'][0]['nom_court']=="local"))) {
				// Colonne 1 : Matière, prof

				$pdf->SetFillColor($param_bull2016["couleur_acquis_ligne_alt".($cpt_matiere%2+1)]["R"], $param_bull2016["couleur_acquis_ligne_alt".($cpt_matiere%2+1)]["V"], $param_bull2016["couleur_acquis_ligne_alt".($cpt_matiere%2+1)]["B"]);
				$pdf->Rect($param_bull2016["x_acquis_col_1"], $y_courant, $param_bull2016["largeur_acquis_col_1"], $hauteur_matiere, 'F');
				$pdf->SetFillColor(0, 0, 0);

				$pdf->SetTextColor(0, 0, 0);
				$pdf->SetXY($param_bull2016["x_acquis_col_1"], $y_courant);
				$pdf->SetFont('DejaVu','',8);
				/*
				//$pdf->Cell($param_bull2016["largeur_bandeau_suivi_acquis"],7, "Suivi des acquis scolaires de l'élève",0,2,'C');
				$texte="Matiere $m ".$hauteur_matiere;
				$pdf->drawTextBox($texte, $param_bull2016["largeur_acquis_col_1"], $hauteur_matiere, 'L', 'M', 0);
				*/

				if(getSettingValue('bul_rel_nom_matieres')=='nom_groupe') {
					$info_nom_matiere=$tab_bull['groupe'][$m]['name'];
				}
				elseif(getSettingValue('bul_rel_nom_matieres')=='description_groupe') {
					$info_nom_matiere=$tab_bull['groupe'][$m]['description'];
				}
				else {
					// Pour parer au bug sur la suppression de matière alors que des groupes sont conservés:
					if(isset($tab_bull['groupe'][$m]['matiere']['nom_complet'])) {
						$info_nom_matiere=$tab_bull['groupe'][$m]['matiere']['nom_complet'];
					}
					else {
						$info_nom_matiere=$tab_bull['groupe'][$m]['name']." (".$tab_bull['groupe'][$m]['id'].")";
					}
				}

				$hauteur_caractere_matiere=8;
				$cell_ajustee_texte_matiere_ratio_min_max=3;

				// 20130927 : cell_ajustee() ou pas sur le nom de matière/enseignement
				$cell_ajustee_texte_matiere=1;
				if((isset($cell_ajustee_texte_matiere))&&($cell_ajustee_texte_matiere==1)) {
					// Encadrement
					//$pdf->Cell($tab_modele_pdf["largeur_matiere"][$classe_id], $espace_entre_matier, "",'LRBT',1,'L');

					// cell_ajustee() ne centre pas verticalement le texte.
					// On met un décalage pour ne pas coller le texte à la bordure
					$Y_decal_cell_ajustee=2;
					// On repositionne et on inscrit le nom de matière sur la moitié de la hauteur de la cellule
					//$pdf->SetXY($X_bloc_matiere, $Y_decal+$Y_decal_cell_ajustee);
					//$pdf->SetXY($param_bull2016["x_acquis_col_1"], $y_courant);

					$pdf->SetXY($param_bull2016["x_acquis_col_1"], $y_courant+$Y_decal_cell_ajustee);

					$texte=$info_nom_matiere;
					$taille_max_police=$hauteur_caractere_matiere;
					//$taille_min_police=ceil($taille_max_police/$tab_modele_pdf["cell_ajustee_texte_matiere_ratio_min_max"][$classe_id]);
					$taille_min_police=ceil($taille_max_police/$cell_ajustee_texte_matiere_ratio_min_max);

					$largeur_dispo=$param_bull2016["largeur_acquis_col_1"];
					//$h_cell=$espace_entre_matier/2-$Y_decal_cell_ajustee;
					$h_cell=$hauteur_matiere/2-$Y_decal_cell_ajustee;

					//cell_ajustee("<b>".$texte."</b>",$pdf->GetX(),$pdf->GetY(),$largeur_dispo,$h_cell,$taille_max_police,$taille_min_police,'');
					cell_ajustee($texte,$pdf->GetX(),$pdf->GetY(),$largeur_dispo,$h_cell,$taille_max_police,$taille_min_police,'');
			
				}
				else {
					$val = $pdf->GetStringWidth($info_nom_matiere);
					$taille_texte = $param_bull2016["largeur_acquis_col_1"]-2;
					$grandeur_texte='test';
					while($grandeur_texte!='ok') {
						if($taille_texte<$val)
						{
							$hauteur_caractere_matiere = $hauteur_caractere_matiere-0.3;
							$pdf->SetFont('DejaVu','B',$hauteur_caractere_matiere);
							$val = $pdf->GetStringWidth($info_nom_matiere);
						}
						else {
							$grandeur_texte='ok';
						}
					}
					$grandeur_texte='test';
					$Y_decal=$y_courant;
					$pdf->SetXY($param_bull2016["x_acquis_col_1"], $Y_decal);
					$pdf->Cell($param_bull2016["largeur_acquis_col_1"], $hauteur_matiere/2, ($info_nom_matiere),'',1,'L');
				}


				// On note l'ordonnée pour le nom des professeurs
				$Y_decal = $y_courant+($hauteur_matiere/2);
				$pdf->SetXY($param_bull2016["x_acquis_col_1"], $Y_decal);
				$pdf->SetFont('DejaVu','',8);

				//fich_debug_bull("\$info_nom_matiere=$info_nom_matiere\n");
				//fich_debug_bull("Le nom de matière est écrit; on est à mi-hauteur de la cellule pour écrire le nom du prof:\n");
				//fich_debug_bull("\$Y_decal=$Y_decal\n");

				// nom des professeurs

				if ( isset($tab_bull['groupe'][$m]["profs"]["list"]) )
				{
					/*
					if($tab_modele_pdf["presentation_proflist"][$classe_id]!="2") {
						// Présentation en colonne des profs
						$nb_prof_matiere = count($tab_bull['groupe'][$m]["profs"]["list"]);
						$espace_matiere_prof = $espace_entre_matier/2;
						if($nb_prof_matiere>0){
							$espace_matiere_prof = $espace_matiere_prof/$nb_prof_matiere;
						}
						$nb_pass_count = '0';
						$text_prof = '';
						while ($nb_prof_matiere > $nb_pass_count)
						{
							$tmp_login_prof=$tab_bull['groupe'][$m]["profs"]["list"][$nb_pass_count];
							$text_prof=affiche_utilisateur($tmp_login_prof,$tab_bull['eleve'][$i]['id_classe']);

							if ( $nb_prof_matiere <= 2 ) { $hauteur_caractere_prof = 8; }
							elseif ( $nb_prof_matiere == 3) { $hauteur_caractere_prof = 5; }
							elseif ( $nb_prof_matiere > 3) { $hauteur_caractere_prof = 2; }
							$pdf->SetFont('DejaVu','',$hauteur_caractere_prof);
							$val = $pdf->GetStringWidth($text_prof);
							$taille_texte = ($tab_modele_pdf["largeur_matiere"][$classe_id]);
							$grandeur_texte='test';
							while($grandeur_texte!='ok') {
								if($taille_texte<$val)
								{
									$hauteur_caractere_prof = $hauteur_caractere_prof-0.3;
									$pdf->SetFont('DejaVu','',$hauteur_caractere_prof);
									$val = $pdf->GetStringWidth($text_prof);
								}
								else {
									$grandeur_texte='ok';
								}
							}
							$grandeur_texte='test';
							$pdf->SetX($X_bloc_matiere);
							if( empty($tab_bull['groupe'][$m]["profs"]["list"][$nb_pass_count+1]) ) {
								$pdf->Cell($tab_modele_pdf["largeur_matiere"][$classe_id], $espace_matiere_prof, ($text_prof),'LRB',1,'L');
							}
							if( !empty($tab_bull['groupe'][$m]["profs"]["list"][$nb_pass_count+1]) ) {
								$pdf->Cell($tab_modele_pdf["largeur_matiere"][$classe_id], $espace_matiere_prof, ($text_prof),'LR',1,'L');
							}
							$nb_pass_count = $nb_pass_count + 1;
						}
					}
					else {
					*/
						// Présentation en ligne des profs
						// On n'a pas forcément le formatage choisi pour la classe...
						//$text_prof=$tab_bull['groupe'][$m]["profs"]["proflist_string"]."  ";
						$text_prof="";
						for($loop_prof_grp=0;$loop_prof_grp<count($tab_bull['groupe'][$m]["profs"]["list"]);$loop_prof_grp++) {
							$tmp_login_prof=$tab_bull['groupe'][$m]["profs"]["list"][$loop_prof_grp];
							if($loop_prof_grp>0) {$text_prof.=", ";}
							$text_prof.=affiche_utilisateur($tmp_login_prof,$tab_bull['eleve'][$i]['id_classe']);
						}

						if($text_prof!="") {
							//$espace_matiere_prof = $espace_entre_matier/2;
							$espace_matiere_prof = $hauteur_matiere/2;
							$hauteur_caractere_prof = 7;

							/*
							if($use_cell_ajustee=="n") {
								$pdf->SetFont('DejaVu','',$hauteur_caractere_prof);
								$val = $pdf->GetStringWidth($text_prof);
								$taille_texte = ($tab_modele_pdf["largeur_matiere"][$classe_id]);
								$grandeur_texte='test';
								while($grandeur_texte!='ok') {
									if($taille_texte<$val)
									{
										$hauteur_caractere_prof = $hauteur_caractere_prof-0.3;
										$pdf->SetFont('DejaVu','',$hauteur_caractere_prof);
										$val = $pdf->GetStringWidth($text_prof);
									}
									else {
										$grandeur_texte='ok';
									}
								}
								$grandeur_texte='test';
								$pdf->SetX($X_bloc_matiere);
								$pdf->Cell($tab_modele_pdf["largeur_matiere"][$classe_id], $espace_matiere_prof, ($text_prof),'LR',1,'L');
							}
							else {
							*/
								$texte=$text_prof;
								$taille_max_police=$hauteur_caractere_prof;
								$taille_min_police=ceil($hauteur_caractere_prof/3);

								$largeur_dispo=$param_bull2016["largeur_acquis_col_1"];
								$h_cell=$espace_matiere_prof;

								$pdf->SetX($param_bull2016["x_acquis_col_1"]);

								cell_ajustee(($texte),$pdf->GetX(),$pdf->GetY(),$largeur_dispo,$h_cell,$taille_max_police,$taille_min_police,'');
							//}
						}
					//}
				}


				// Colonne 2 : Appréciation
				$pdf->SetFillColor($param_bull2016["couleur_acquis_ligne_alt".($cpt_matiere%2+1)]["R"], $param_bull2016["couleur_acquis_ligne_alt".($cpt_matiere%2+1)]["V"], $param_bull2016["couleur_acquis_ligne_alt".($cpt_matiere%2+1)]["B"]);
				$pdf->Rect($param_bull2016["x_acquis_col_appreciation_app_classe"], $y_courant, $param_bull2016["largeur_acquis_col_appreciation_app_classe"], $hauteur_matiere, 'F');

				$pdf->SetFillColor(0, 0, 0);
				$pdf->SetXY($param_bull2016["x_acquis_col_appreciation_app_classe"], $y_courant);


				$largeur_appreciation2=$param_bull2016["largeur_acquis_col_appreciation_app_classe"];
				//+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
				// 20161123
				// si on autorise l'affichage des sous matière et s'il y en a alors on les affiche
				//$id_groupe_select = $tab_bull['groupe'][$m]['id'];
				//$pdf->SetXY($param_bull2016["x_acquis_col_appreciation_app_classe"], $Y_decal-($espace_entre_matier/2));
				/*
				$X_sous_matiere = 0; $largeur_sous_matiere=0;

				// A MODIFIER POUR POUVOIR LIMITER LA LARGEUR, par exemple à 20mm

				if((isset($param_bull2016["bull2016_autorise_sous_matiere"]))&&($param_bull2016["bull2016_autorise_sous_matiere"]=="y")&&(!empty($tab_bull['groupe'][$m][$i]['cn_nom']))) {

					// Bordure blanche pour les sous-matières
					$pdf->SetDrawColor(255, 255, 255);

					$X_sous_matiere = $param_bull2016["x_acquis_col_appreciation_app_classe"];
					//$Y_sous_matiere = $y_courant-$hauteur_matiere/2;
					$Y_sous_matiere = $y_courant;
					$n=0;
					$largeur_texte_sousmatiere=0;
					$largeur_sous_matiere=0;
					while( !empty($tab_bull['groupe'][$m][$i]['cn_nom'][$n]) )
					{
						$pdf->SetFont('DejaVu','',6);
						$largeur_texte_sousmatiere = $pdf->GetStringWidth($tab_bull['groupe'][$m][$i]['cn_nom'][$n].': '.$tab_bull['groupe'][$m][$i]['cn_note'][$n]);
						if($largeur_sous_matiere<$largeur_texte_sousmatiere) { $largeur_sous_matiere=$largeur_texte_sousmatiere; }
						$n = $n + 1;
					}
					if($largeur_sous_matiere!='0') { $largeur_sous_matiere = $largeur_sous_matiere + 2; }
					$n=0;
					while( !empty($tab_bull['groupe'][$m][$i]['cn_nom'][$n]) )
					{
						$pdf->SetXY($X_sous_matiere, $Y_sous_matiere);
						$pdf->SetFont('DejaVu','',6);
						$pdf->Cell($largeur_sous_matiere, $hauteur_matiere/count($tab_bull['groupe'][$m][$i]['cn_nom']), ($tab_bull['groupe'][$m][$i]['cn_nom'][$n].': '.$tab_bull['groupe'][$m][$i]['cn_note'][$n]),1,0,'L');
						$Y_sous_matiere = $Y_sous_matiere+$hauteur_matiere/count($tab_bull['groupe'][$m][$i]['cn_nom']);
						$n = $n + 1;
					}

					$largeur_appreciation2=$param_bull2016["largeur_acquis_col_appreciation_app_classe"]-$largeur_sous_matiere;

					// Retour aux bordures noires par défaut, mais non dessinées en principe sur les bulletins PDF 2016
					$pdf->SetDrawColor(0, 0, 0);
				}
				//$pdf->SetXY($X_note_moy_app+$largeur_utilise, $Y_decal-($espace_entre_matier/2));
				$pdf->SetXY($param_bull2016["x_acquis_col_appreciation_app_classe"]+$largeur_sous_matiere, $y_courant);
				*/
				//+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

				// calcul de la taille du texte des appréciations
				$hauteur_caractere_appreciation = 8;
				$pdf->SetFont('DejaVu','',$hauteur_caractere_appreciation);

				//suppression des espaces en début et en fin
				$app_aff = trim($tab_bull['app_grp'][$m]);

				fich_debug_bull("__________________________________________\n");
				fich_debug_bull("$app_aff\n");
				fich_debug_bull("__________________________________________\n");

				// DEBUT AJUSTEMENT TAILLE APPRECIATION
				$taille_texte_total = $pdf->GetStringWidth($app_aff);
				//$largeur_appreciation2 = $largeur_appreciation - $largeur_sous_matiere;
				//$largeur_appreciation2=$param_bull2016["largeur_acquis_col_appreciation_app_classe"];

				if($use_cell_ajustee=="n") {
					//$taille_texte = (($espace_entre_matier/3)*$largeur_appreciation2);
					$nb_ligne_app = '2.8';
					//$nb_ligne_app = '3.8';
					//$nb_ligne_app = '4.8';
					$taille_texte_max = $nb_ligne_app * ($largeur_appreciation2-4);
					//$taille_texte_max = $nb_ligne_app * ($largeur_appreciation2);
					$grandeur_texte='test';

					fich_debug_bull("\$taille_texte_total=$taille_texte_total\n");
					fich_debug_bull("\$largeur_appreciation2=$largeur_appreciation2\n");
					fich_debug_bull("\$nb_ligne_app=$nb_ligne_app\n");
					//fich_debug_bull("\$taille_texte_max = \$nb_ligne_app * (\$largeur_appreciation2-4)=$nb_ligne_app * ($largeur_appreciation2-4)=$taille_texte_max\n");
					fich_debug_bull("\$taille_texte_max = \$nb_ligne_app * (\$largeur_appreciation2)=$nb_ligne_app * ($largeur_appreciation2)=$taille_texte_max\n");

					while($grandeur_texte!='ok') {
						if($taille_texte_max < $taille_texte_total)
						{
							$hauteur_caractere_appreciation = $hauteur_caractere_appreciation-0.3;
							//$hauteur_caractere_appreciation = $hauteur_caractere_appreciation-0.1;
							$pdf->SetFont('DejaVu','',$hauteur_caractere_appreciation);
							$taille_texte_total = $pdf->GetStringWidth($app_aff);
						}
						else {
							$grandeur_texte='ok';
						}
					}
					$grandeur_texte='test';
					$pdf->drawTextBox(($app_aff), $largeur_appreciation2, $hauteur_matiere, 'J', 'M', 1);
				}
				else {
					$texte=$app_aff;
					//$texte="Bla bla\nbli ".$app_aff;
					$taille_max_police=$hauteur_caractere_appreciation;
					$taille_min_police=ceil($taille_max_police/3);

					$largeur_dispo=$largeur_appreciation2;
					$h_cell=$hauteur_matiere;

					if(getSettingValue('suppr_balises_app_prof')=='y') {$texte=preg_replace('/<(.*)>/U','',$texte);}
					cell_ajustee(($texte),$pdf->GetX(),$pdf->GetY(),$largeur_dispo,$h_cell,$taille_max_police,$taille_min_police,'');
				}


				// Colonne 3 : Moyenne classe
				$pdf->SetFillColor($param_bull2016["couleur_acquis_ligne_alt".($cpt_matiere%2+1)]["R"], $param_bull2016["couleur_acquis_ligne_alt".($cpt_matiere%2+1)]["V"], $param_bull2016["couleur_acquis_ligne_alt".($cpt_matiere%2+1)]["B"]);
				$pdf->Rect($param_bull2016["x_acquis_col_moyclasse"], $y_courant, $param_bull2016["largeur_acquis_col_moyclasse"], $hauteur_matiere, 'F');

				if ($param_bull2016["bull2016_moyminclassemax"]=='y') {
					$chaine_minclassemax="";
					// Min
					if (($tab_bull['moy_min_classe_grp'][$m]=="-")||($tab_bull['moy_min_classe_grp'][$m]=="")) {
						$valeur = "-";
					} else {
						$valeur = present_nombre($tab_bull['moy_min_classe_grp'][$m], $arrondi, $nb_chiffre_virgule, $chiffre_avec_zero);
					}
					$chaine_minclassemax.=$valeur."\n";

					// Classe
					if (($tab_bull['moy_classe_grp'][$m]=="-")||($tab_bull['moy_classe_grp'][$m]=="")) {
						$valeur = "-";
					}
					else {
						$valeur = present_nombre($tab_bull['moy_classe_grp'][$m], $arrondi, $nb_chiffre_virgule, $chiffre_avec_zero);
					}
					$chaine_minclassemax.=$valeur."\n";

					if (($tab_bull['moy_max_classe_grp'][$m]=="-")||($tab_bull['moy_max_classe_grp'][$m]=="")) {
						$valeur = "-";
					} else {
						$valeur = present_nombre($tab_bull['moy_max_classe_grp'][$m], $arrondi, $nb_chiffre_virgule, $chiffre_avec_zero);
					}
					$chaine_minclassemax.=$valeur;

					// Affichage min/classe/max avec cell_ajustee()
					$pdf->SetTextColor(0, 0, 0);
					$pdf->SetXY($param_bull2016["x_acquis_col_moyclasse"], $y_courant);
					$texte=$chaine_minclassemax;
					$taille_max_police=7;
					$taille_min_police=ceil($taille_max_police/3);

					$largeur_dispo=$param_bull2016["largeur_acquis_col_moyclasse"];
					$h_cell=$hauteur_matiere;

					//if(getSettingValue('suppr_balises_app_prof')=='y') {$texte=preg_replace('/<(.*)>/U','',$texte);}
					cell_ajustee(($texte),$pdf->GetX(),$pdf->GetY(),$largeur_dispo,$h_cell,$taille_max_police,$taille_min_police, '', 'C', 'C');
				}
				else {
					// Moyenne classe seulement
					$pdf->SetTextColor(0, 0, 0);
					$pdf->SetXY($param_bull2016["x_acquis_col_moyclasse"], $y_courant);
					$pdf->SetFont('DejaVu','',7);
					if (($tab_bull['moy_classe_grp'][$m]=="-")||($tab_bull['moy_classe_grp'][$m]=="")) {
						$valeur = "-";
					} else {
						$valeur = present_nombre($tab_bull['moy_classe_grp'][$m], $arrondi, $nb_chiffre_virgule, $chiffre_avec_zero);
					}
					$pdf->Cell($param_bull2016["largeur_acquis_col_moyclasse"], $hauteur_matiere, $valeur,'',0,'C');
				}

				$y_courant+=$hauteur_matiere+0.5;

				$cpt_matiere++;
			}
		}
	}
	// Fin des enseignements de la table 'groupes'

	// Début des AID_e
	if($nb_AID_e_non_AP_EPI_Parcours>0) {
		if("$i"=="") {
			if(isset($tab_bull['aid'])) {
				for($m=0;$m<count($tab_bull['aid']);$m++) {
					if($tab_bull['aid'][$m]['order_display1']=="e") {
						if($tab_bull['aid'][$m]['type_aid']==0) {

							// Colonne 1 : Matière, prof

							$pdf->SetFillColor($param_bull2016["couleur_acquis_ligne_alt".($cpt_matiere%2+1)]["R"], $param_bull2016["couleur_acquis_ligne_alt".($cpt_matiere%2+1)]["V"], $param_bull2016["couleur_acquis_ligne_alt".($cpt_matiere%2+1)]["B"]);
							$pdf->Rect($param_bull2016["x_acquis_col_1"], $y_courant, $param_bull2016["largeur_acquis_col_1"], $hauteur_matiere, 'F');
							$pdf->SetFillColor(0, 0, 0);

							$pdf->SetTextColor(0, 0, 0);
							$pdf->SetXY($param_bull2016["x_acquis_col_1"], $y_courant);
							$pdf->SetFont('DejaVu','',8);
							/*
							//$pdf->Cell($param_bull2016["largeur_bandeau_suivi_acquis"],7, "Suivi des acquis scolaires de l'élève",0,2,'C');
							$texte="Matiere $m ".$hauteur_matiere;
							$pdf->drawTextBox($texte, $param_bull2016["largeur_acquis_col_1"], $hauteur_matiere, 'L', 'M', 0);
							*/

							if($tab_bull['aid'][$m]['display_nom']=='z') {
								if((isset($tab_bull['aid'][$m]['aid_nom']))&&($tab_bull['aid'][$m]['aid_nom']!="")) {
									$info_nom_matiere=$tab_bull['aid'][$m]['aid_nom'];
								}
								elseif((isset($tab_bull['aid'][$m]['nom_complet']))&&($tab_bull['aid'][$m]['nom_complet']!="")) {
									$info_nom_matiere=$tab_bull['aid'][$m]['nom_complet'];
								}
								else {
									$info_nom_matiere=$tab_bull['aid'][$m]['nom'];
								}
							}
							elseif($tab_bull['aid'][$m]['display_nom']=='x') {
								if((isset($tab_bull['aid'][$m]['nom']))&&($tab_bull['aid'][$m]['nom']!="")) {
									$info_nom_matiere=$tab_bull['aid'][$m]['nom'];
								}
								else {
									$info_nom_matiere=$tab_bull['aid'][$m]['nom_complet'];
								}

								if((isset($tab_bull['aid'][$m]['aid_nom']))&&($tab_bull['aid'][$m]['aid_nom']!="")) {
									if($info_nom_matiere!="") {
										$info_nom_matiere.=": ";
									}
									$info_nom_matiere.=$tab_bull['aid'][$m]['aid_nom'];
								}
							}
							else {
								// display_nom à y ou n: On met en 1ère colonne le nom complet de catégorie AID
								if((isset($tab_bull['aid'][$m]['nom_complet']))&&($tab_bull['aid'][$m]['nom_complet']!="")) {
									$info_nom_matiere=$tab_bull['aid'][$m]['nom_complet'];
								}
								else {
									$info_nom_matiere=$tab_bull['aid'][$m]['nom'];
								}
							}
							if($info_nom_matiere=="") {
								$info_nom_matiere="AID";
							}

							$hauteur_caractere_matiere=8;
							$cell_ajustee_texte_matiere_ratio_min_max=3;

							// 20130927 : cell_ajustee() ou pas sur le nom de matière/enseignement
							$cell_ajustee_texte_matiere=1;
							if((isset($cell_ajustee_texte_matiere))&&($cell_ajustee_texte_matiere==1)) {
								// On met un décalage pour ne pas coller le texte à la bordure
								$Y_decal_cell_ajustee=2;
								$pdf->SetXY($param_bull2016["x_acquis_col_1"], $y_courant+$Y_decal_cell_ajustee);

								$texte=$info_nom_matiere;
								$taille_max_police=$hauteur_caractere_matiere;
								$taille_min_police=ceil($taille_max_police/$cell_ajustee_texte_matiere_ratio_min_max);

								$largeur_dispo=$param_bull2016["largeur_acquis_col_1"];
								$h_cell=$hauteur_matiere/2-$Y_decal_cell_ajustee;

								cell_ajustee($texte,$pdf->GetX(),$pdf->GetY(),$largeur_dispo,$h_cell,$taille_max_police,$taille_min_police,'');
			
							}
							else {
								$val = $pdf->GetStringWidth($info_nom_matiere);
								$taille_texte = $param_bull2016["largeur_acquis_col_1"]-2;
								$grandeur_texte='test';
								while($grandeur_texte!='ok') {
									if($taille_texte<$val)
									{
										$hauteur_caractere_matiere = $hauteur_caractere_matiere-0.3;
										$pdf->SetFont('DejaVu','B',$hauteur_caractere_matiere);
										$val = $pdf->GetStringWidth($info_nom_matiere);
									}
									else {
										$grandeur_texte='ok';
									}
								}
								$grandeur_texte='test';
								$Y_decal=$y_courant;
								$pdf->SetXY($param_bull2016["x_acquis_col_1"], $Y_decal);
								$pdf->Cell($param_bull2016["largeur_acquis_col_1"], $hauteur_matiere/2, ($info_nom_matiere),'',1,'L');
							}
			

							// On note l'ordonnée pour le nom des professeurs
							$Y_decal = $y_courant+($hauteur_matiere/2);
							$pdf->SetXY($param_bull2016["x_acquis_col_1"], $Y_decal);
							$pdf->SetFont('DejaVu','',8);

							// nom des professeurs
							if ( isset($tab_bull['aid'][$m]['profs']['list'][0]) )
							{

								// Présentation en ligne des profs
								// On n'a pas forcément le formatage choisi pour la classe...
								//$text_prof=$tab_bull['groupe'][$m]["profs"]["proflist_string"]."  ";
								$text_prof="";
								for($loop_prof_grp=0;$loop_prof_grp<count($tab_bull['aid'][$m]['profs']['list']);$loop_prof_grp++) {
									$tmp_login_prof=$tab_bull['aid'][$m]['profs']['list'][$loop_prof_grp];
									if($loop_prof_grp>0) {$text_prof.=", ";}
									$text_prof.=affiche_utilisateur($tmp_login_prof,$tab_bull['id_classe']);
								}

								if($text_prof!="") {
									//$espace_matiere_prof = $espace_entre_matier/2;
									$espace_matiere_prof = $hauteur_matiere/2;
									$hauteur_caractere_prof = 7;

									$texte=$text_prof;
									$taille_max_police=$hauteur_caractere_prof;
									$taille_min_police=ceil($hauteur_caractere_prof/3);

									$largeur_dispo=$param_bull2016["largeur_acquis_col_1"];
									$h_cell=$espace_matiere_prof;

									$pdf->SetX($param_bull2016["x_acquis_col_1"]);

									cell_ajustee(($texte),$pdf->GetX(),$pdf->GetY(),$largeur_dispo,$h_cell,$taille_max_police,$taille_min_police,'');
								}
							}


							// Colonne 2 : Appréciation
							$pdf->SetFillColor($param_bull2016["couleur_acquis_ligne_alt".($cpt_matiere%2+1)]["R"], $param_bull2016["couleur_acquis_ligne_alt".($cpt_matiere%2+1)]["V"], $param_bull2016["couleur_acquis_ligne_alt".($cpt_matiere%2+1)]["B"]);
							$pdf->Rect($param_bull2016["x_acquis_col_appreciation_app_classe"], $y_courant, $param_bull2016["largeur_acquis_col_appreciation_app_classe"], $hauteur_matiere, 'F');

							$pdf->SetFillColor(0, 0, 0);
							$pdf->SetXY($param_bull2016["x_acquis_col_appreciation_app_classe"], $y_courant);

							// calcul de la taille du texte des appréciations
							$hauteur_caractere_appreciation = 8;
							$pdf->SetFont('DejaVu','',$hauteur_caractere_appreciation);

							//suppression des espaces en début et en fin
							//$app_aff = trim($tab_bull['app'][$m][$i]);
							$app_aff="";
							if($tab_bull['aid'][$m]['message']!='') {
								$app_aff.=$tab_bull['aid'][$m]['message'];
							}
							//if($app_aff!='') {$app_aff.=" ";}
							if(($tab_bull['aid'][$m]['display_nom']=='y')&&($tab_bull['aid'][$m]['aid_nom']!='')) {
								if($app_aff!='') {$app_aff.=" ";}
								$app_aff.=$tab_bull['aid'][$m]['aid_nom'];
							}
							if($app_aff!='') {$app_aff.="\n";}
							$app_aff.=trim($tab_bull['aid'][$m]['app_grp']);

							fich_debug_bull("__________________________________________\n");
							fich_debug_bull("$app_aff\n");
							fich_debug_bull("__________________________________________\n");

							// DEBUT AJUSTEMENT TAILLE APPRECIATION
							$taille_texte_total = $pdf->GetStringWidth($app_aff);
							//$largeur_appreciation2 = $largeur_appreciation - $largeur_sous_matiere;
							$largeur_appreciation2=$param_bull2016["largeur_acquis_col_appreciation_app_classe"];

							if($use_cell_ajustee=="n") {
								//$taille_texte = (($espace_entre_matier/3)*$largeur_appreciation2);
								$nb_ligne_app = '2.8';
								//$nb_ligne_app = '3.8';
								//$nb_ligne_app = '4.8';
								$taille_texte_max = $nb_ligne_app * ($largeur_appreciation2-4);
								//$taille_texte_max = $nb_ligne_app * ($largeur_appreciation2);
								$grandeur_texte='test';

								fich_debug_bull("\$taille_texte_total=$taille_texte_total\n");
								fich_debug_bull("\$largeur_appreciation2=$largeur_appreciation2\n");
								fich_debug_bull("\$nb_ligne_app=$nb_ligne_app\n");
								//fich_debug_bull("\$taille_texte_max = \$nb_ligne_app * (\$largeur_appreciation2-4)=$nb_ligne_app * ($largeur_appreciation2-4)=$taille_texte_max\n");
								fich_debug_bull("\$taille_texte_max = \$nb_ligne_app * (\$largeur_appreciation2)=$nb_ligne_app * ($largeur_appreciation2)=$taille_texte_max\n");

								while($grandeur_texte!='ok') {
									if($taille_texte_max < $taille_texte_total)
									{
										$hauteur_caractere_appreciation = $hauteur_caractere_appreciation-0.3;
										//$hauteur_caractere_appreciation = $hauteur_caractere_appreciation-0.1;
										$pdf->SetFont('DejaVu','',$hauteur_caractere_appreciation);
										$taille_texte_total = $pdf->GetStringWidth($app_aff);
									}
									else {
										$grandeur_texte='ok';
									}
								}
								$grandeur_texte='test';
								$pdf->drawTextBox(($app_aff), $largeur_appreciation2, $hauteur_matiere, 'J', 'M', 1);
							}
							else {
								$texte=$app_aff;
								//$texte="Bla bla\nbli ".$app_aff;
								$taille_max_police=$hauteur_caractere_appreciation;
								$taille_min_police=ceil($taille_max_police/3);

								$largeur_dispo=$largeur_appreciation2;
								$h_cell=$hauteur_matiere;

								if(getSettingValue('suppr_balises_app_prof')=='y') {$texte=preg_replace('/<(.*)>/U','',$texte);}
								cell_ajustee(($texte),$pdf->GetX(),$pdf->GetY(),$largeur_dispo,$h_cell,$taille_max_police,$taille_min_police,'');
							}


							// Colonne 3 : Moyenne classe
							$pdf->SetFillColor($param_bull2016["couleur_acquis_ligne_alt".($cpt_matiere%2+1)]["R"], $param_bull2016["couleur_acquis_ligne_alt".($cpt_matiere%2+1)]["V"], $param_bull2016["couleur_acquis_ligne_alt".($cpt_matiere%2+1)]["B"]);
							$pdf->Rect($param_bull2016["x_acquis_col_moyclasse"], $y_courant, $param_bull2016["largeur_acquis_col_moyclasse"], $hauteur_matiere, 'F');

							if ($param_bull2016["bull2016_moyminclassemax"]=='y') {
								$chaine_minclassemax="";
								// Min
								if (($tab_bull['aid'][$m]['aid_note_min']=="-")||($tab_bull['aid'][$m]['aid_note_min']=="")) {
									$valeur = "-";
								} else {
									$valeur = present_nombre($tab_bull['aid'][$m]['aid_note_min'], $arrondi, $nb_chiffre_virgule, $chiffre_avec_zero);
								}
								$chaine_minclassemax.=$valeur."\n";

								// Classe
								if (($tab_bull['aid'][$m]['aid_note_moyenne']=="-")||($tab_bull['aid'][$m]['aid_note_moyenne']=="")) {
									$valeur = "-";
								}
								else {
									$valeur = present_nombre($tab_bull['aid'][$m]['aid_note_moyenne'], $arrondi, $nb_chiffre_virgule, $chiffre_avec_zero);
								}
								$chaine_minclassemax.=$valeur."\n";

								if (($tab_bull['aid'][$m]['aid_note_max']=="-")||($tab_bull['aid'][$m]['aid_note_max']=="")) {
									$valeur = "-";
								} else {
									$valeur = present_nombre($tab_bull['aid'][$m]['aid_note_max'], $arrondi, $nb_chiffre_virgule, $chiffre_avec_zero);
								}
								$chaine_minclassemax.=$valeur;

								// Affichage min/classe/max avec cell_ajustee()
								$pdf->SetTextColor(0, 0, 0);
								$pdf->SetXY($param_bull2016["x_acquis_col_moyclasse"], $y_courant);
								$texte=$chaine_minclassemax;
								$taille_max_police=7;
								$taille_min_police=ceil($taille_max_police/3);

								$largeur_dispo=$param_bull2016["largeur_acquis_col_moyclasse"];
								$h_cell=$hauteur_matiere;

								//if(getSettingValue('suppr_balises_app_prof')=='y') {$texte=preg_replace('/<(.*)>/U','',$texte);}
								cell_ajustee(($texte),$pdf->GetX(),$pdf->GetY(),$largeur_dispo,$h_cell,$taille_max_police,$taille_min_police, '', 'C', 'C');
							}
							else {
								$pdf->SetTextColor(0, 0, 0);
								$pdf->SetXY($param_bull2016["x_acquis_col_moyclasse"], $y_courant);
								$pdf->SetFont('DejaVu','',7);

								if (($tab_bull['aid'][$m]['aid_note_moyenne']=="-")||($tab_bull['aid'][$m]['aid_note_moyenne']=="")) {
									$valeur = "-";
								}
								else {
									$valeur = present_nombre($tab_bull['aid'][$m]['aid_note_moyenne'], $arrondi, $nb_chiffre_virgule, $chiffre_avec_zero);
								}

								$pdf->Cell($param_bull2016["largeur_acquis_col_moyclasse"], $hauteur_matiere, $valeur,'',0,'C');
							}

							$y_courant+=$hauteur_matiere+0.5;

							$cpt_matiere++;

						}
					}
				}
			}
		}
		else {
			if(isset($tab_bull['eleve'][$i]['aid_e'])) {
				for($m=0;$m<count($tab_bull['eleve'][$i]['aid_e']);$m++) {
					if($tab_bull['eleve'][$i]['aid_e'][$m]["type_aid"]==0) {
						// Colonne 1 : Matière, prof

						$pdf->SetFillColor($param_bull2016["couleur_acquis_ligne_alt".($cpt_matiere%2+1)]["R"], $param_bull2016["couleur_acquis_ligne_alt".($cpt_matiere%2+1)]["V"], $param_bull2016["couleur_acquis_ligne_alt".($cpt_matiere%2+1)]["B"]);
						$pdf->Rect($param_bull2016["x_acquis_col_1"], $y_courant, $param_bull2016["largeur_acquis_col_1"], $hauteur_matiere, 'F');
						$pdf->SetFillColor(0, 0, 0);

						$pdf->SetTextColor(0, 0, 0);
						$pdf->SetXY($param_bull2016["x_acquis_col_1"], $y_courant);
						$pdf->SetFont('DejaVu','',8);
						/*
						//$pdf->Cell($param_bull2016["largeur_bandeau_suivi_acquis"],7, "Suivi des acquis scolaires de l'élève",0,2,'C');
						$texte="Matiere $m ".$hauteur_matiere;
						$pdf->drawTextBox($texte, $param_bull2016["largeur_acquis_col_1"], $hauteur_matiere, 'L', 'M', 0);
						*/

						if($tab_bull['eleve'][$i]['aid_e'][$m]['display_nom']=='z') {
							if((isset($tab_bull['eleve'][$i]['aid_e'][$m]['aid_nom']))&&($tab_bull['eleve'][$i]['aid_e'][$m]['aid_nom']!="")) {
								$info_nom_matiere=$tab_bull['eleve'][$i]['aid_e'][$m]['aid_nom'];
							}
							elseif((isset($tab_bull['eleve'][$i]['aid_e'][$m]['nom_complet']))&&($tab_bull['eleve'][$i]['aid_e'][$m]['nom_complet']!="")) {
								$info_nom_matiere=$tab_bull['eleve'][$i]['aid_e'][$m]['nom_complet'];
							}
							else {
								$info_nom_matiere=$tab_bull['eleve'][$i]['aid_e'][$m]['nom'];
							}
						}
						elseif($tab_bull['eleve'][$i]['aid_e'][$m]['display_nom']=='x') {
							if((isset($tab_bull['eleve'][$i]['aid_e'][$m]['nom']))&&($tab_bull['eleve'][$i]['aid_e'][$m]['nom']!="")) {
								$info_nom_matiere=$tab_bull['eleve'][$i]['aid_e'][$m]['nom'];
							}
							else {
								$info_nom_matiere=$tab_bull['eleve'][$i]['aid_e'][$m]['nom_complet'];
							}

							if((isset($tab_bull['eleve'][$i]['aid_e'][$m]['aid_nom']))&&($tab_bull['eleve'][$i]['aid_e'][$m]['aid_nom']!="")) {
								if($info_nom_matiere!="") {
									$info_nom_matiere.=": ";
								}
								$info_nom_matiere.=$tab_bull['eleve'][$i]['aid_e'][$m]['aid_nom'];
							}
						}
						else {
							if((isset($tab_bull['eleve'][$i]['aid_e'][$m]['nom_complet']))&&($tab_bull['eleve'][$i]['aid_e'][$m]['nom_complet']!="")) {
								$info_nom_matiere=$tab_bull['eleve'][$i]['aid_e'][$m]['nom_complet'];
							}
							else {
								$info_nom_matiere=$tab_bull['eleve'][$i]['aid_e'][$m]['nom'];
							}
						}
						if($info_nom_matiere=="") {
							$info_nom_matiere="AID";
						}

						$hauteur_caractere_matiere=8;
						$cell_ajustee_texte_matiere_ratio_min_max=3;

						// 20130927 : cell_ajustee() ou pas sur le nom de matière/enseignement
						$cell_ajustee_texte_matiere=1;
						if((isset($cell_ajustee_texte_matiere))&&($cell_ajustee_texte_matiere==1)) {
							// On met un décalage pour ne pas coller le texte à la bordure
							$Y_decal_cell_ajustee=2;
							$pdf->SetXY($param_bull2016["x_acquis_col_1"], $y_courant+$Y_decal_cell_ajustee);

							$texte=$info_nom_matiere;
							$taille_max_police=$hauteur_caractere_matiere;
							$taille_min_police=ceil($taille_max_police/$cell_ajustee_texte_matiere_ratio_min_max);

							$largeur_dispo=$param_bull2016["largeur_acquis_col_1"];
							$h_cell=$hauteur_matiere/2-$Y_decal_cell_ajustee;

							cell_ajustee($texte,$pdf->GetX(),$pdf->GetY(),$largeur_dispo,$h_cell,$taille_max_police,$taille_min_police,'');
			
						}
						else {
							$val = $pdf->GetStringWidth($info_nom_matiere);
							$taille_texte = $param_bull2016["largeur_acquis_col_1"]-2;
							$grandeur_texte='test';
							while($grandeur_texte!='ok') {
								if($taille_texte<$val)
								{
									$hauteur_caractere_matiere = $hauteur_caractere_matiere-0.3;
									$pdf->SetFont('DejaVu','B',$hauteur_caractere_matiere);
									$val = $pdf->GetStringWidth($info_nom_matiere);
								}
								else {
									$grandeur_texte='ok';
								}
							}
							$grandeur_texte='test';
							$Y_decal=$y_courant;
							$pdf->SetXY($param_bull2016["x_acquis_col_1"], $Y_decal);
							$pdf->Cell($param_bull2016["largeur_acquis_col_1"], $hauteur_matiere/2, ($info_nom_matiere),'',1,'L');
						}
			

						// On note l'ordonnée pour le nom des professeurs
						$Y_decal = $y_courant+($hauteur_matiere/2);
						$pdf->SetXY($param_bull2016["x_acquis_col_1"], $Y_decal);
						$pdf->SetFont('DejaVu','',8);

						// nom des professeurs
						if ( isset($tab_bull['eleve'][$i]['aid_e'][$m]['aid_prof_resp_login'][0]) )
						{

							// Présentation en ligne des profs
							// On n'a pas forcément le formatage choisi pour la classe...
							//$text_prof=$tab_bull['groupe'][$m]["profs"]["proflist_string"]."  ";
							$text_prof="";
							for($loop_prof_grp=0;$loop_prof_grp<count($tab_bull['eleve'][$i]['aid_e'][$m]['aid_prof_resp_login']);$loop_prof_grp++) {
								$tmp_login_prof=$tab_bull['eleve'][$i]['aid_e'][$m]['aid_prof_resp_login'][$loop_prof_grp];
								if($loop_prof_grp>0) {$text_prof.=", ";}
								$text_prof.=affiche_utilisateur($tmp_login_prof,$tab_bull['eleve'][$i]['id_classe']);
							}

							if($text_prof!="") {
								//$espace_matiere_prof = $espace_entre_matier/2;
								$espace_matiere_prof = $hauteur_matiere/2;
								$hauteur_caractere_prof = 7;

								$texte=$text_prof;
								$taille_max_police=$hauteur_caractere_prof;
								$taille_min_police=ceil($hauteur_caractere_prof/3);

								$largeur_dispo=$param_bull2016["largeur_acquis_col_1"];
								$h_cell=$espace_matiere_prof;

								$pdf->SetX($param_bull2016["x_acquis_col_1"]);

								cell_ajustee(($texte),$pdf->GetX(),$pdf->GetY(),$largeur_dispo,$h_cell,$taille_max_police,$taille_min_police,'');
							}
						}


						// Colonne 2 : Appréciation
						$pdf->SetFillColor($param_bull2016["couleur_acquis_ligne_alt".($cpt_matiere%2+1)]["R"], $param_bull2016["couleur_acquis_ligne_alt".($cpt_matiere%2+1)]["V"], $param_bull2016["couleur_acquis_ligne_alt".($cpt_matiere%2+1)]["B"]);
						$pdf->Rect($param_bull2016["x_acquis_col_appreciation_app_classe"], $y_courant, $param_bull2016["largeur_acquis_col_appreciation_app_classe"], $hauteur_matiere, 'F');

						$pdf->SetFillColor(0, 0, 0);
						$pdf->SetXY($param_bull2016["x_acquis_col_appreciation_app_classe"], $y_courant);

						// calcul de la taille du texte des appréciations
						$hauteur_caractere_appreciation = 8;
						$pdf->SetFont('DejaVu','',$hauteur_caractere_appreciation);

						//suppression des espaces en début et en fin
						//$app_aff = trim($tab_bull['app'][$m][$i]);
						$app_aff="";
						if($tab_bull['eleve'][$i]['aid_e'][$m]['message']!='') {
							$app_aff.=$tab_bull['eleve'][$i]['aid_e'][$m]['message'];
						}
						//if($app_aff!='') {$app_aff.=" ";}
						if(($tab_bull['eleve'][$i]['aid_e'][$m]['display_nom']=='y')&&($tab_bull['eleve'][$i]['aid_e'][$m]['aid_nom']!='')) {
							if($app_aff!='') {$app_aff.=" ";}
							$app_aff.=$tab_bull['eleve'][$i]['aid_e'][$m]['aid_nom'];
						}
						if($app_aff!='') {$app_aff.="\n";}
						$app_aff.=trim($tab_bull['eleve'][$i]['aid_e'][$m]['aid_appreciation']);

						fich_debug_bull("__________________________________________\n");
						fich_debug_bull("$app_aff\n");
						fich_debug_bull("__________________________________________\n");

						// DEBUT AJUSTEMENT TAILLE APPRECIATION
						$taille_texte_total = $pdf->GetStringWidth($app_aff);
						//$largeur_appreciation2 = $largeur_appreciation - $largeur_sous_matiere;
						$largeur_appreciation2=$param_bull2016["largeur_acquis_col_appreciation_app_classe"];

						if($use_cell_ajustee=="n") {
							//$taille_texte = (($espace_entre_matier/3)*$largeur_appreciation2);
							$nb_ligne_app = '2.8';
							//$nb_ligne_app = '3.8';
							//$nb_ligne_app = '4.8';
							$taille_texte_max = $nb_ligne_app * ($largeur_appreciation2-4);
							//$taille_texte_max = $nb_ligne_app * ($largeur_appreciation2);
							$grandeur_texte='test';

							fich_debug_bull("\$taille_texte_total=$taille_texte_total\n");
							fich_debug_bull("\$largeur_appreciation2=$largeur_appreciation2\n");
							fich_debug_bull("\$nb_ligne_app=$nb_ligne_app\n");
							//fich_debug_bull("\$taille_texte_max = \$nb_ligne_app * (\$largeur_appreciation2-4)=$nb_ligne_app * ($largeur_appreciation2-4)=$taille_texte_max\n");
							fich_debug_bull("\$taille_texte_max = \$nb_ligne_app * (\$largeur_appreciation2)=$nb_ligne_app * ($largeur_appreciation2)=$taille_texte_max\n");

							while($grandeur_texte!='ok') {
								if($taille_texte_max < $taille_texte_total)
								{
									$hauteur_caractere_appreciation = $hauteur_caractere_appreciation-0.3;
									//$hauteur_caractere_appreciation = $hauteur_caractere_appreciation-0.1;
									$pdf->SetFont('DejaVu','',$hauteur_caractere_appreciation);
									$taille_texte_total = $pdf->GetStringWidth($app_aff);
								}
								else {
									$grandeur_texte='ok';
								}
							}
							$grandeur_texte='test';
							$pdf->drawTextBox(($app_aff), $largeur_appreciation2, $hauteur_matiere, 'J', 'M', 1);
						}
						else {
							$texte=$app_aff;
							//$texte="Bla bla\nbli ".$app_aff;
							$taille_max_police=$hauteur_caractere_appreciation;
							$taille_min_police=ceil($taille_max_police/3);

							$largeur_dispo=$largeur_appreciation2;
							$h_cell=$hauteur_matiere;

							if(getSettingValue('suppr_balises_app_prof')=='y') {$texte=preg_replace('/<(.*)>/U','',$texte);}
							cell_ajustee(($texte),$pdf->GetX(),$pdf->GetY(),$largeur_dispo,$h_cell,$taille_max_police,$taille_min_police,'');
						}


						// Colonne 3 : Moyenne classe
						$pdf->SetFillColor($param_bull2016["couleur_acquis_ligne_alt".($cpt_matiere%2+1)]["R"], $param_bull2016["couleur_acquis_ligne_alt".($cpt_matiere%2+1)]["V"], $param_bull2016["couleur_acquis_ligne_alt".($cpt_matiere%2+1)]["B"]);
						$pdf->Rect($param_bull2016["x_acquis_col_moyclasse"], $y_courant, $param_bull2016["largeur_acquis_col_moyclasse"], $hauteur_matiere, 'F');

						if ($param_bull2016["bull2016_moyminclassemax"]=='y') {
							$chaine_minclassemax="";
							// Min
							if (($tab_bull['eleve'][$i]['aid_e'][$m]['aid_note_min']=="-")||($tab_bull['eleve'][$i]['aid_e'][$m]['aid_note_min']=="")) {
								$valeur = "-";
							} else {
								$valeur = present_nombre($tab_bull['eleve'][$i]['aid_e'][$m]['aid_note_min'], $arrondi, $nb_chiffre_virgule, $chiffre_avec_zero);
							}
							$chaine_minclassemax.=$valeur."\n";

							// Classe
							if (($tab_bull['eleve'][$i]['aid_e'][$m]['aid_note_moyenne']=="-")||($tab_bull['eleve'][$i]['aid_e'][$m]['aid_note_moyenne']=="")) {
								$valeur = "-";
							}
							else {
								$valeur = present_nombre($tab_bull['eleve'][$i]['aid_e'][$m]['aid_note_moyenne'], $arrondi, $nb_chiffre_virgule, $chiffre_avec_zero);
							}
							$chaine_minclassemax.=$valeur."\n";

							if (($tab_bull['eleve'][$i]['aid_e'][$m]['aid_note_max']=="-")||($tab_bull['eleve'][$i]['aid_e'][$m]['aid_note_max']=="")) {
								$valeur = "-";
							} else {
								$valeur = present_nombre($tab_bull['eleve'][$i]['aid_e'][$m]['aid_note_max'], $arrondi, $nb_chiffre_virgule, $chiffre_avec_zero);
							}
							$chaine_minclassemax.=$valeur;

							// Affichage min/classe/max avec cell_ajustee()
							$pdf->SetTextColor(0, 0, 0);
							$pdf->SetXY($param_bull2016["x_acquis_col_moyclasse"], $y_courant);
							$texte=$chaine_minclassemax;
							$taille_max_police=7;
							$taille_min_police=ceil($taille_max_police/3);

							$largeur_dispo=$param_bull2016["largeur_acquis_col_moyclasse"];
							$h_cell=$hauteur_matiere;

							//if(getSettingValue('suppr_balises_app_prof')=='y') {$texte=preg_replace('/<(.*)>/U','',$texte);}
							cell_ajustee(($texte),$pdf->GetX(),$pdf->GetY(),$largeur_dispo,$h_cell,$taille_max_police,$taille_min_police, '', 'C', 'C');
						}
						else {
							$pdf->SetTextColor(0, 0, 0);
							$pdf->SetXY($param_bull2016["x_acquis_col_moyclasse"], $y_courant);
							$pdf->SetFont('DejaVu','',7);

							if (($tab_bull['eleve'][$i]['aid_e'][$m]['aid_note_moyenne']=="-")||($tab_bull['eleve'][$i]['aid_e'][$m]['aid_note_moyenne']=="")) {
								$valeur = "-";
							}
							else {
								$valeur = present_nombre($tab_bull['eleve'][$i]['aid_e'][$m]['aid_note_moyenne'], $arrondi, $nb_chiffre_virgule, $chiffre_avec_zero);
							}

							$pdf->Cell($param_bull2016["largeur_acquis_col_moyclasse"], $hauteur_matiere, $valeur,'',0,'C');
						}

						$y_courant+=$hauteur_matiere+0.5;

						$cpt_matiere++;
					}
				}
			}
		}
	}
	// Fin des AID_e

	//++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	//++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	//++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

	// ++++++++++++++++++++++++++++
	// ++++++++++++++++++++++++++++
	// On passe aux AP/EPI/Parcours
	// ++++++++++++++++++++++++++++
	// ++++++++++++++++++++++++++++

	$hauteur_EPI_AP_Parcours=$hauteur_matiere;

	// 20161231
	// S'il y a des AP, afficher le bandeau AP puis les AP

	// S'il y a des EPI, afficher le bandeau EPI puis les EPI

	// S'il y a des Parcours, afficher le bandeau Parcours puis les Parcours

	$nb_EPI_AP_Parcours+=$nb_AID_AP_EPI_Parcours;
	$nb_AP=$nb_AP+count($indice_AID_b_AP)+count($indice_AID_e_AP);
	$nb_EPI=$nb_EPI+count($indice_AID_b_EPI)+count($indice_AID_e_EPI);
	$nb_Parcours=$nb_Parcours+count($indice_AID_b_Parcours)+count($indice_AID_e_Parcours);

	//$hauteur_prise_par_EPI_AP_Parcours_page_2=$param_bull2016["y_EPI_AP_Parcours"];

	if($nb_EPI_AP_Parcours>0) {
		/*
		$hauteur_EPI_AP_Parcours=$total_hauteur_EPI_AP_Parcours/$nb_EPI_AP_Parcours;
		// On limite la hauteur
		if($hauteur_EPI_AP_Parcours>30) {
			$hauteur_EPI_AP_Parcours=30;
		}

		$y_courant=$param_bull2016["y_EPI_AP_Parcours"];
		*/

		// EPI en cycle 4 seulement

		if($nb_EPI>0) {

			// On met un blanc de 5mm au-dessus de la ligne de titre EPI
			//$y_courant+=5;
			$y_courant+=$param_bull2016["espace_vertical_entre_sections_EPI_AP_Parcours"];

			$pdf->SetFillColor($param_bull2016["couleur_bandeau_EPI"]["R"], $param_bull2016["couleur_bandeau_EPI"]["V"], $param_bull2016["couleur_bandeau_EPI"]["B"]);
			$pdf->Rect($param_bull2016["x_EPI_AP_Parcours"], $y_courant, $param_bull2016["largeur_EPI_AP_Parcours"], $param_bull2016["hauteur_ligne_titre_EPI_AP_Parcours"], 'F');

			$pdf->SetFillColor(0, 0, 0);
			//$pdf->SetTextColor(255, 255, 255);
			$pdf->SetTextColor(0, 0, 0);
			$pdf->SetXY($param_bull2016["x_EPI_AP_Parcours"], $y_courant);
			$pdf->SetFont('DejaVu','',8);
			$pdf->Cell($param_bull2016["largeur_EPI_AP_Parcours"],7, "Enseignements pratiques interdisciplinaires : projets réalisés et implication de l'élève",0,2,'L');
			//." nb_EPI=".$nb_EPI." nb_AP=".$nb_AP." nb_Parcours=".$nb_Parcours

			$cpt_matiere=0;
			$y_courant+=$param_bull2016["hauteur_ligne_titre_EPI_AP_Parcours"]+0.5;

			if("$i"=="") {
				// AID_b de type EPI
				if(isset($tab_bull['aid'])) {
					for($m=0;$m<count($tab_bull['aid']);$m++) {
						if($tab_bull['aid'][$m]['order_display1']=="b") {
							if($tab_bull['aid'][$m]['type_aid']==2) {

								// Colonne 1 : Matière, prof

								$pdf->SetFillColor($param_bull2016["couleur_EPI_alt".($cpt_matiere%2+1)]["R"], $param_bull2016["couleur_EPI_alt".($cpt_matiere%2+1)]["V"], $param_bull2016["couleur_EPI_alt".($cpt_matiere%2+1)]["B"]);
								$pdf->Rect($param_bull2016["x_EPI_AP_Parcours"], $y_courant, $param_bull2016["largeur_EPI_AP_Parcours_col_1"], $hauteur_EPI_AP_Parcours, 'F');
								$pdf->SetFillColor(0, 0, 0);

								$pdf->SetTextColor(0, 0, 0);
								$pdf->SetXY($param_bull2016["x_EPI_AP_Parcours"], $y_courant);
								$pdf->SetFont('DejaVu','',8);

								if($tab_bull['aid'][$m]['display_nom']=='z') {
									if((isset($tab_bull['aid'][$m]['aid_nom']))&&($tab_bull['aid'][$m]['aid_nom']!="")) {
										$info_nom_matiere=$tab_bull['aid'][$m]['aid_nom'];
									}
									elseif((isset($tab_bull['aid'][$m]['nom_complet']))&&($tab_bull['aid'][$m]['nom_complet']!="")) {
										$info_nom_matiere=$tab_bull['aid'][$m]['nom_complet'];
									}
									else {
										$info_nom_matiere=$tab_bull['aid'][$m]['nom'];
									}
								}
								elseif($tab_bull['aid'][$m]['display_nom']=='x') {
									if((isset($tab_bull['aid'][$m]['nom']))&&($tab_bull['aid'][$m]['nom']!="")) {
										$info_nom_matiere=$tab_bull['aid'][$m]['nom'];
									}
									else {
										$info_nom_matiere=$tab_bull['aid'][$m]['nom_complet'];
									}

									if((isset($tab_bull['aid'][$m]['aid_nom']))&&($tab_bull['aid'][$m]['aid_nom']!="")) {
										if($info_nom_matiere!="") {
											$info_nom_matiere.=": ";
										}
										$info_nom_matiere.=$tab_bull['aid'][$m]['aid_nom'];
									}
								}
								else {
									if((isset($tab_bull['aid'][$m]['nom_complet']))&&($tab_bull['aid'][$m]['nom_complet']!="")) {
										$info_nom_matiere=$tab_bull['aid'][$m]['nom_complet'];
									}
									else {
										$info_nom_matiere=$tab_bull['aid'][$m]['nom'];
									}
								}
								if($info_nom_matiere=="") {
									$info_nom_matiere="AID";
								}

								$hauteur_caractere_matiere=8;
								$cell_ajustee_texte_matiere_ratio_min_max=3;

								// Forcé pour le moment
								$cell_ajustee_texte_matiere=1;
								if((isset($cell_ajustee_texte_matiere))&&($cell_ajustee_texte_matiere==1)) {
									// On met un décalage pour ne pas coller le texte à la bordure
									$Y_decal_cell_ajustee=2;
									$pdf->SetXY($param_bull2016["x_EPI_AP_Parcours"], $y_courant+$Y_decal_cell_ajustee);

									$texte=$info_nom_matiere;
									$taille_max_police=$hauteur_caractere_matiere;
									$taille_min_police=ceil($taille_max_police/$cell_ajustee_texte_matiere_ratio_min_max);

									$largeur_dispo=$param_bull2016["largeur_EPI_AP_Parcours_col_1"];
									$h_cell=$hauteur_EPI_AP_Parcours/2-$Y_decal_cell_ajustee;

									cell_ajustee($texte,$pdf->GetX(),$pdf->GetY(),$largeur_dispo,$h_cell,$taille_max_police,$taille_min_police,'');
		
								}
								else {
									$val = $pdf->GetStringWidth($info_nom_matiere);
									$taille_texte = $param_bull2016["largeur_EPI_AP_Parcours_col_1"]-2;
									$grandeur_texte='test';
									while($grandeur_texte!='ok') {
										if($taille_texte<$val)
										{
											$hauteur_caractere_matiere = $hauteur_caractere_matiere-0.3;
											$pdf->SetFont('DejaVu','B',$hauteur_caractere_matiere);
											$val = $pdf->GetStringWidth($info_nom_matiere);
										}
										else {
											$grandeur_texte='ok';
										}
									}
									$grandeur_texte='test';
									$Y_decal=$y_courant;
									$pdf->SetXY($param_bull2016["x_EPI_AP_Parcours"], $Y_decal);
									$pdf->Cell($param_bull2016["largeur_EPI_AP_Parcours_col_1"], $hauteur_EPI_AP_Parcours/2, ($info_nom_matiere),'',1,'L');
								}


								// On note l'ordonnée pour le nom des professeurs
								$Y_decal = $y_courant+($hauteur_EPI_AP_Parcours/2);
								$pdf->SetXY($param_bull2016["x_EPI_AP_Parcours"], $Y_decal);
								$pdf->SetFont('DejaVu','',8);

								// nom des professeurs
								if ( isset($tab_bull['aid'][$m]['aid_prof_resp_login'][0]) )
								{

									// Présentation en ligne des profs
									// On n'a pas forcément le formatage choisi pour la classe...
									//$text_prof=$tab_bull['groupe'][$m]["profs"]["proflist_string"]."  ";
									$text_prof="";
									for($loop_prof_grp=0;$loop_prof_grp<count($tab_bull['aid'][$m]['profs']['list']);$loop_prof_grp++) {
										$tmp_login_prof=$tab_bull['aid'][$m]['profs']['list'][$loop_prof_grp];
										if($loop_prof_grp>0) {$text_prof.=", ";}
										$text_prof.=affiche_utilisateur($tmp_login_prof,$tab_bull['id_classe']);
									}

									if($text_prof!="") {
										//$espace_matiere_prof = $espace_entre_matier/2;
										$espace_matiere_prof = $hauteur_EPI_AP_Parcours/2;
										$hauteur_caractere_prof = 7;

										$texte=$text_prof;
										$taille_max_police=$hauteur_caractere_prof;
										$taille_min_police=ceil($hauteur_caractere_prof/3);

										$largeur_dispo=$param_bull2016["largeur_EPI_AP_Parcours_col_1"];
										$h_cell=$espace_matiere_prof;

										$pdf->SetX($param_bull2016["x_EPI_AP_Parcours"]);

										cell_ajustee(($texte),$pdf->GetX(),$pdf->GetY(),$largeur_dispo,$h_cell,$taille_max_police,$taille_min_police,'');
									}
								}


								// Colonne 2 : Appréciation
								$pdf->SetFillColor($param_bull2016["couleur_EPI_alt".($cpt_matiere%2+1)]["R"], $param_bull2016["couleur_EPI_alt".($cpt_matiere%2+1)]["V"], $param_bull2016["couleur_EPI_alt".($cpt_matiere%2+1)]["B"]);
								$pdf->Rect($param_bull2016["x_EPI_AP_Parcours_col_2"], $y_courant, $param_bull2016["largeur_EPI_AP_Parcours_col_2"], $hauteur_EPI_AP_Parcours, 'F');

								$pdf->SetFillColor(0, 0, 0);
								$pdf->SetXY($param_bull2016["x_EPI_AP_Parcours_col_2"], $y_courant);

								// calcul de la taille du texte des appréciations
								$hauteur_caractere_appreciation = 8;
								$pdf->SetFont('DejaVu','',$hauteur_caractere_appreciation);

								//suppression des espaces en début et en fin
								//$app_aff = trim($tab_bull['app'][$m][$i]);
								$app_aff="";
								if($tab_bull['aid'][$m]['message']!='') {
									$app_aff.=$tab_bull['aid'][$m]['message'];
								}
								//if($app_aff!='') {$app_aff.=" ";}
								if(($tab_bull['aid'][$m]['display_nom']=='y')&&($tab_bull['aid'][$m]['aid_nom']!='')) {
									if($app_aff!='') {$app_aff.=" ";}
									$app_aff.=$tab_bull['aid'][$m]['aid_nom'];
								}
								if($app_aff!='') {$app_aff.="\n";}
								$app_aff.=trim($tab_bull['aid'][$m]['app_grp']);

								fich_debug_bull("__________________________________________\n");
								fich_debug_bull("$app_aff\n");
								fich_debug_bull("__________________________________________\n");

								// DEBUT AJUSTEMENT TAILLE APPRECIATION
								$taille_texte_total = $pdf->GetStringWidth($app_aff);
								//$largeur_appreciation2 = $largeur_appreciation - $largeur_sous_matiere;
								$largeur_appreciation2=$param_bull2016["largeur_EPI_AP_Parcours_col_2"];

								if($use_cell_ajustee=="n") {
									//$taille_texte = (($espace_entre_matier/3)*$largeur_appreciation2);
									$nb_ligne_app = '2.8';
									//$nb_ligne_app = '3.8';
									//$nb_ligne_app = '4.8';
									$taille_texte_max = $nb_ligne_app * ($largeur_appreciation2-4);
									//$taille_texte_max = $nb_ligne_app * ($largeur_appreciation2);
									$grandeur_texte='test';

									fich_debug_bull("\$taille_texte_total=$taille_texte_total\n");
									fich_debug_bull("\$largeur_appreciation2=$largeur_appreciation2\n");
									fich_debug_bull("\$nb_ligne_app=$nb_ligne_app\n");
									//fich_debug_bull("\$taille_texte_max = \$nb_ligne_app * (\$largeur_appreciation2-4)=$nb_ligne_app * ($largeur_appreciation2-4)=$taille_texte_max\n");
									fich_debug_bull("\$taille_texte_max = \$nb_ligne_app * (\$largeur_appreciation2)=$nb_ligne_app * ($largeur_appreciation2)=$taille_texte_max\n");

									while($grandeur_texte!='ok') {
										if($taille_texte_max < $taille_texte_total)
										{
											$hauteur_caractere_appreciation = $hauteur_caractere_appreciation-0.3;
											//$hauteur_caractere_appreciation = $hauteur_caractere_appreciation-0.1;
											$pdf->SetFont('DejaVu','',$hauteur_caractere_appreciation);
											$taille_texte_total = $pdf->GetStringWidth($app_aff);
										}
										else {
											$grandeur_texte='ok';
										}
									}
									$grandeur_texte='test';
									$pdf->drawTextBox(($app_aff), $largeur_appreciation2, $hauteur_EPI_AP_Parcours, 'J', 'M', 1);
								}
								else {
									$texte=$app_aff;
									//$texte="Bla bla\nbli ".$app_aff;
									$taille_max_police=$hauteur_caractere_appreciation;
									$taille_min_police=ceil($taille_max_police/3);

									$largeur_dispo=$largeur_appreciation2;
									$h_cell=$hauteur_EPI_AP_Parcours;

									if(getSettingValue('suppr_balises_app_prof')=='y') {$texte=preg_replace('/<(.*)>/U','',$texte);}
									cell_ajustee(($texte),$pdf->GetX(),$pdf->GetY(),$largeur_dispo,$h_cell,$taille_max_police,$taille_min_police,'');
								}

								$y_courant+=$hauteur_EPI_AP_Parcours+0.5;
								$cpt_matiere++;


							}
							// Fin des AID_b tagués EPI
						}
					}
				}

				// Enseignements de la table 'groupes' tagués EPI
				for($m=0;$m<count($tab_bull['groupe']);$m++) {
					// On n'affiche pas ici les groupes correspondant à AP, EPI ou Parcours
					if((isset($tab_bull['groupe'][$m]['type_grp'][0]))&&
					($tab_bull['groupe'][$m]['type_grp'][0]['nom_court']=="EPI")) {

						// Colonne 1 : Matière, prof

						$pdf->SetFillColor($param_bull2016["couleur_EPI_alt".($cpt_matiere%2+1)]["R"], $param_bull2016["couleur_EPI_alt".($cpt_matiere%2+1)]["V"], $param_bull2016["couleur_EPI_alt".($cpt_matiere%2+1)]["B"]);
						$pdf->Rect($param_bull2016["x_EPI_AP_Parcours"], $y_courant, $param_bull2016["largeur_EPI_AP_Parcours_col_1"], $hauteur_EPI_AP_Parcours, 'F');
						$pdf->SetFillColor(0, 0, 0);

						$pdf->SetTextColor(0, 0, 0);
						$pdf->SetXY($param_bull2016["x_EPI_AP_Parcours"], $y_courant);
						$pdf->SetFont('DejaVu','',8);

						if(getSettingValue('bul_rel_nom_matieres')=='nom_groupe') {
							$info_nom_matiere=$tab_bull['groupe'][$m]['name'];
						}
						elseif(getSettingValue('bul_rel_nom_matieres')=='description_groupe') {
							$info_nom_matiere=$tab_bull['groupe'][$m]['description'];
						}
						else {
							// Pour parer au bug sur la suppression de matière alors que des groupes sont conservés:
							if(isset($tab_bull['groupe'][$m]['matiere']['nom_complet'])) {
								$info_nom_matiere=$tab_bull['groupe'][$m]['matiere']['nom_complet'];
							}
							else {
								$info_nom_matiere=$tab_bull['groupe'][$m]['name']." (".$tab_bull['groupe'][$m]['id'].")";
							}
						}

						$hauteur_caractere_matiere=8;
						$cell_ajustee_texte_matiere_ratio_min_max=3;

						// 20130927 : cell_ajustee() ou pas sur le nom de matière/enseignement
						$cell_ajustee_texte_matiere=1;
						if((isset($cell_ajustee_texte_matiere))&&($cell_ajustee_texte_matiere==1)) {
							// Encadrement
							//$pdf->Cell($tab_modele_pdf["largeur_matiere"][$classe_id], $espace_entre_matier, "",'LRBT',1,'L');

							// cell_ajustee() ne centre pas verticalement le texte.
							// On met un décalage pour ne pas coller le texte à la bordure
							$Y_decal_cell_ajustee=2;
							// On repositionne et on inscrit le nom de matière sur la moitié de la hauteur de la cellule
							//$pdf->SetXY($X_bloc_matiere, $Y_decal+$Y_decal_cell_ajustee);
							//$pdf->SetXY($param_bull2016["x_acquis_col_1"], $y_courant);

							$pdf->SetXY($param_bull2016["x_EPI_AP_Parcours"], $y_courant+$Y_decal_cell_ajustee);

							$texte=$info_nom_matiere;
							$taille_max_police=$hauteur_caractere_matiere;
							//$taille_min_police=ceil($taille_max_police/$tab_modele_pdf["cell_ajustee_texte_matiere_ratio_min_max"][$classe_id]);
							$taille_min_police=ceil($taille_max_police/$cell_ajustee_texte_matiere_ratio_min_max);

							$largeur_dispo=$param_bull2016["largeur_EPI_AP_Parcours_col_1"];
							//$h_cell=$espace_entre_matier/2-$Y_decal_cell_ajustee;
							$h_cell=$hauteur_EPI_AP_Parcours/2-$Y_decal_cell_ajustee;

							//cell_ajustee("<b>".$texte."</b>",$pdf->GetX(),$pdf->GetY(),$largeur_dispo,$h_cell,$taille_max_police,$taille_min_police,'');
							cell_ajustee($texte,$pdf->GetX(),$pdf->GetY(),$largeur_dispo,$h_cell,$taille_max_police,$taille_min_police,'');
		
						}
						else {
							$val = $pdf->GetStringWidth($info_nom_matiere);
							$taille_texte = $param_bull2016["largeur_EPI_AP_Parcours_col_1"]-2;
							$grandeur_texte='test';
							while($grandeur_texte!='ok') {
								if($taille_texte<$val)
								{
									$hauteur_caractere_matiere = $hauteur_caractere_matiere-0.3;
									$pdf->SetFont('DejaVu','',$hauteur_caractere_matiere);
									$val = $pdf->GetStringWidth($info_nom_matiere);
								}
								else {
									$grandeur_texte='ok';
								}
							}
							$grandeur_texte='test';
							$Y_decal=$y_courant;
							$pdf->SetXY($param_bull2016["x_EPI_AP_Parcours"], $Y_decal);
							$pdf->Cell($param_bull2016["largeur_EPI_AP_Parcours_col_1"], $hauteur_EPI_AP_Parcours/2, ($info_nom_matiere),'',1,'L');
						}
		

						// On note l'ordonnée pour le nom des professeurs
						$Y_decal = $y_courant+($hauteur_EPI_AP_Parcours/2);
						$pdf->SetXY($param_bull2016["x_EPI_AP_Parcours"], $Y_decal);
						$pdf->SetFont('DejaVu','',8);

						//fich_debug_bull("\$info_nom_matiere=$info_nom_matiere\n");
						//fich_debug_bull("Le nom de matière est écrit; on est à mi-hauteur de la cellule pour écrire le nom du prof:\n");
						//fich_debug_bull("\$Y_decal=$Y_decal\n");

						// nom des professeurs

						if ( isset($tab_bull['groupe'][$m]["profs"]["list"]) )
						{

							// Présentation en ligne des profs
							// On n'a pas forcément le formatage choisi pour la classe...
							//$text_prof=$tab_bull['groupe'][$m]["profs"]["proflist_string"]."  ";
							$text_prof="";
							for($loop_prof_grp=0;$loop_prof_grp<count($tab_bull['groupe'][$m]["profs"]["list"]);$loop_prof_grp++) {
								$tmp_login_prof=$tab_bull['groupe'][$m]["profs"]["list"][$loop_prof_grp];
								if($loop_prof_grp>0) {$text_prof.=", ";}
								$text_prof.=affiche_utilisateur($tmp_login_prof,$tab_bull['id_classe']);
							}

							if($text_prof!="") {
								//$espace_matiere_prof = $espace_entre_matier/2;
								$espace_matiere_prof = $hauteur_EPI_AP_Parcours/2;
								$hauteur_caractere_prof = 7;

								/*
								if($use_cell_ajustee=="n") {
									$pdf->SetFont('DejaVu','',$hauteur_caractere_prof);
									$val = $pdf->GetStringWidth($text_prof);
									$taille_texte = ($tab_modele_pdf["largeur_matiere"][$classe_id]);
									$grandeur_texte='test';
									while($grandeur_texte!='ok') {
										if($taille_texte<$val)
										{
											$hauteur_caractere_prof = $hauteur_caractere_prof-0.3;
											$pdf->SetFont('DejaVu','',$hauteur_caractere_prof);
											$val = $pdf->GetStringWidth($text_prof);
										}
										else {
											$grandeur_texte='ok';
										}
									}
									$grandeur_texte='test';
									$pdf->SetX($X_bloc_matiere);
									$pdf->Cell($tab_modele_pdf["largeur_matiere"][$classe_id], $espace_matiere_prof, ($text_prof),'LR',1,'L');
								}
								else {
								*/
									$texte=$text_prof;
									$taille_max_police=$hauteur_caractere_prof;
									$taille_min_police=ceil($hauteur_caractere_prof/3);

									$largeur_dispo=$param_bull2016["largeur_EPI_AP_Parcours_col_1"];
									$h_cell=$espace_matiere_prof;

									$pdf->SetX($param_bull2016["x_EPI_AP_Parcours"]);

									cell_ajustee(($texte),$pdf->GetX(),$pdf->GetY(),$largeur_dispo,$h_cell,$taille_max_police,$taille_min_police,'');
								//}
							}
						}


						// Colonne 2 : Appréciation
						$pdf->SetFillColor($param_bull2016["couleur_EPI_alt".($cpt_matiere%2+1)]["R"], $param_bull2016["couleur_EPI_alt".($cpt_matiere%2+1)]["V"], $param_bull2016["couleur_EPI_alt".($cpt_matiere%2+1)]["B"]);
						$pdf->Rect($param_bull2016["x_EPI_AP_Parcours_col_2"], $y_courant, $param_bull2016["largeur_EPI_AP_Parcours_col_2"], $hauteur_EPI_AP_Parcours, 'F');

						$pdf->SetFillColor(0, 0, 0);
						$pdf->SetXY($param_bull2016["x_EPI_AP_Parcours_col_2"], $y_courant);

						// calcul de la taille du texte des appréciations
						$hauteur_caractere_appreciation = 8;
						$pdf->SetFont('DejaVu','',$hauteur_caractere_appreciation);

						//suppression des espaces en début et en fin
						$app_aff = trim($tab_bull['app_grp'][$m]);

						fich_debug_bull("__________________________________________\n");
						fich_debug_bull("$app_aff\n");
						fich_debug_bull("__________________________________________\n");

						// DEBUT AJUSTEMENT TAILLE APPRECIATION
						$taille_texte_total = $pdf->GetStringWidth($app_aff);
						//$largeur_appreciation2 = $largeur_appreciation - $largeur_sous_matiere;
						$largeur_appreciation2=$param_bull2016["largeur_EPI_AP_Parcours_col_2"];

						if($use_cell_ajustee=="n") {
							//$taille_texte = (($espace_entre_matier/3)*$largeur_appreciation2);
							$nb_ligne_app = '2.8';
							//$nb_ligne_app = '3.8';
							//$nb_ligne_app = '4.8';
							$taille_texte_max = $nb_ligne_app * ($largeur_appreciation2-4);
							//$taille_texte_max = $nb_ligne_app * ($largeur_appreciation2);
							$grandeur_texte='test';

							fich_debug_bull("\$taille_texte_total=$taille_texte_total\n");
							fich_debug_bull("\$largeur_appreciation2=$largeur_appreciation2\n");
							fich_debug_bull("\$nb_ligne_app=$nb_ligne_app\n");
							//fich_debug_bull("\$taille_texte_max = \$nb_ligne_app * (\$largeur_appreciation2-4)=$nb_ligne_app * ($largeur_appreciation2-4)=$taille_texte_max\n");
							fich_debug_bull("\$taille_texte_max = \$nb_ligne_app * (\$largeur_appreciation2)=$nb_ligne_app * ($largeur_appreciation2)=$taille_texte_max\n");

							while($grandeur_texte!='ok') {
								if($taille_texte_max < $taille_texte_total)
								{
									$hauteur_caractere_appreciation = $hauteur_caractere_appreciation-0.3;
									//$hauteur_caractere_appreciation = $hauteur_caractere_appreciation-0.1;
									$pdf->SetFont('DejaVu','',$hauteur_caractere_appreciation);
									$taille_texte_total = $pdf->GetStringWidth($app_aff);
								}
								else {
									$grandeur_texte='ok';
								}
							}
							$grandeur_texte='test';
							$pdf->drawTextBox(($app_aff), $largeur_appreciation2, $hauteur_EPI_AP_Parcours, 'J', 'M', 1);
						}
						else {
							$texte=$app_aff;
							//$texte="Bla bla\nbli ".$app_aff;
							$taille_max_police=$hauteur_caractere_appreciation;
							$taille_min_police=ceil($taille_max_police/3);

							$largeur_dispo=$largeur_appreciation2;
							$h_cell=$hauteur_EPI_AP_Parcours;

							if(getSettingValue('suppr_balises_app_prof')=='y') {$texte=preg_replace('/<(.*)>/U','',$texte);}
							cell_ajustee(($texte),$pdf->GetX(),$pdf->GetY(),$largeur_dispo,$h_cell,$taille_max_police,$taille_min_police,'');
						}

						$y_courant+=$hauteur_EPI_AP_Parcours+0.5;
						$cpt_matiere++;

					}
				}
				// Fin des enseignements de la table 'groupes' tagués EPI


				// AID_e de type EPI
				if(isset($tab_bull['aid'])) {
					for($m=0;$m<count($tab_bull['aid']);$m++) {
						if($tab_bull['aid'][$m]['order_display1']=="e") {
							if($tab_bull['aid'][$m]['type_aid']==2) {

								// Colonne 1 : Matière, prof

								$pdf->SetFillColor($param_bull2016["couleur_EPI_alt".($cpt_matiere%2+1)]["R"], $param_bull2016["couleur_EPI_alt".($cpt_matiere%2+1)]["V"], $param_bull2016["couleur_EPI_alt".($cpt_matiere%2+1)]["B"]);
								$pdf->Rect($param_bull2016["x_EPI_AP_Parcours"], $y_courant, $param_bull2016["largeur_EPI_AP_Parcours_col_1"], $hauteur_EPI_AP_Parcours, 'F');
								$pdf->SetFillColor(0, 0, 0);

								$pdf->SetTextColor(0, 0, 0);
								$pdf->SetXY($param_bull2016["x_EPI_AP_Parcours"], $y_courant);
								$pdf->SetFont('DejaVu','',8);

								if($tab_bull['aid'][$m]['display_nom']=='z') {
									if((isset($tab_bull['aid'][$m]['aid_nom']))&&($tab_bull['aid'][$m]['aid_nom']!="")) {
										$info_nom_matiere=$tab_bull['aid'][$m]['aid_nom'];
									}
									elseif((isset($tab_bull['aid'][$m]['nom_complet']))&&($tab_bull['aid'][$m]['nom_complet']!="")) {
										$info_nom_matiere=$tab_bull['aid'][$m]['nom_complet'];
									}
									else {
										$info_nom_matiere=$tab_bull['aid'][$m]['nom'];
									}
								}
								elseif($tab_bull['aid'][$m]['display_nom']=='x') {
									if((isset($tab_bull['aid'][$m]['nom']))&&($tab_bull['aid'][$m]['nom']!="")) {
										$info_nom_matiere=$tab_bull['aid'][$m]['nom'];
									}
									else {
										$info_nom_matiere=$tab_bull['aid'][$m]['nom_complet'];
									}

									if((isset($tab_bull['aid'][$m]['aid_nom']))&&($tab_bull['aid'][$m]['aid_nom']!="")) {
										if($info_nom_matiere!="") {
											$info_nom_matiere.=": ";
										}
										$info_nom_matiere.=$tab_bull['aid'][$m]['aid_nom'];
									}
								}
								else {
									if((isset($tab_bull['aid'][$m]['nom_complet']))&&($tab_bull['aid'][$m]['nom_complet']!="")) {
										$info_nom_matiere=$tab_bull['aid'][$m]['nom_complet'];
									}
									else {
										$info_nom_matiere=$tab_bull['aid'][$m]['nom'];
									}
								}
								if($info_nom_matiere=="") {
									$info_nom_matiere="AID";
								}

								$hauteur_caractere_matiere=8;
								$cell_ajustee_texte_matiere_ratio_min_max=3;

								// Forcé pour le moment
								$cell_ajustee_texte_matiere=1;
								if((isset($cell_ajustee_texte_matiere))&&($cell_ajustee_texte_matiere==1)) {
									// On met un décalage pour ne pas coller le texte à la bordure
									$Y_decal_cell_ajustee=2;
									$pdf->SetXY($param_bull2016["x_EPI_AP_Parcours"], $y_courant+$Y_decal_cell_ajustee);

									$texte=$info_nom_matiere;
									$taille_max_police=$hauteur_caractere_matiere;
									$taille_min_police=ceil($taille_max_police/$cell_ajustee_texte_matiere_ratio_min_max);

									$largeur_dispo=$param_bull2016["largeur_EPI_AP_Parcours_col_1"];
									$h_cell=$hauteur_EPI_AP_Parcours/2-$Y_decal_cell_ajustee;

									cell_ajustee($texte,$pdf->GetX(),$pdf->GetY(),$largeur_dispo,$h_cell,$taille_max_police,$taille_min_police,'');
		
								}
								else {
									$val = $pdf->GetStringWidth($info_nom_matiere);
									$taille_texte = $param_bull2016["largeur_EPI_AP_Parcours_col_1"]-2;
									$grandeur_texte='test';
									while($grandeur_texte!='ok') {
										if($taille_texte<$val)
										{
											$hauteur_caractere_matiere = $hauteur_caractere_matiere-0.3;
											$pdf->SetFont('DejaVu','B',$hauteur_caractere_matiere);
											$val = $pdf->GetStringWidth($info_nom_matiere);
										}
										else {
											$grandeur_texte='ok';
										}
									}
									$grandeur_texte='test';
									$Y_decal=$y_courant;
									$pdf->SetXY($param_bull2016["x_EPI_AP_Parcours"], $Y_decal);
									$pdf->Cell($param_bull2016["largeur_EPI_AP_Parcours_col_1"], $hauteur_EPI_AP_Parcours/2, ($info_nom_matiere),'',1,'L');
								}


								// On note l'ordonnée pour le nom des professeurs
								$Y_decal = $y_courant+($hauteur_EPI_AP_Parcours/2);
								$pdf->SetXY($param_bull2016["x_EPI_AP_Parcours"], $Y_decal);
								$pdf->SetFont('DejaVu','',8);

								// nom des professeurs
								if ( isset($tab_bull['aid'][$m]['aid_prof_resp_login'][0]) )
								{

									// Présentation en ligne des profs
									// On n'a pas forcément le formatage choisi pour la classe...
									//$text_prof=$tab_bull['groupe'][$m]["profs"]["proflist_string"]."  ";
									$text_prof="";
									for($loop_prof_grp=0;$loop_prof_grp<count($tab_bull['aid'][$m]['profs']['list']);$loop_prof_grp++) {
										$tmp_login_prof=$tab_bull['aid'][$m]['profs']['list'][$loop_prof_grp];
										if($loop_prof_grp>0) {$text_prof.=", ";}
										$text_prof.=affiche_utilisateur($tmp_login_prof,$tab_bull['id_classe']);
									}

									if($text_prof!="") {
										//$espace_matiere_prof = $espace_entre_matier/2;
										$espace_matiere_prof = $hauteur_EPI_AP_Parcours/2;
										$hauteur_caractere_prof = 7;

										$texte=$text_prof;
										$taille_max_police=$hauteur_caractere_prof;
										$taille_min_police=ceil($hauteur_caractere_prof/3);

										$largeur_dispo=$param_bull2016["largeur_EPI_AP_Parcours_col_1"];
										$h_cell=$espace_matiere_prof;

										$pdf->SetX($param_bull2016["x_EPI_AP_Parcours"]);

										cell_ajustee(($texte),$pdf->GetX(),$pdf->GetY(),$largeur_dispo,$h_cell,$taille_max_police,$taille_min_police,'');
									}
								}


								// Colonne 2 : Appréciation
								$pdf->SetFillColor($param_bull2016["couleur_EPI_alt".($cpt_matiere%2+1)]["R"], $param_bull2016["couleur_EPI_alt".($cpt_matiere%2+1)]["V"], $param_bull2016["couleur_EPI_alt".($cpt_matiere%2+1)]["B"]);
								$pdf->Rect($param_bull2016["x_EPI_AP_Parcours_col_2"], $y_courant, $param_bull2016["largeur_EPI_AP_Parcours_col_2"], $hauteur_EPI_AP_Parcours, 'F');

								$pdf->SetFillColor(0, 0, 0);
								$pdf->SetXY($param_bull2016["x_EPI_AP_Parcours_col_2"], $y_courant);

								// calcul de la taille du texte des appréciations
								$hauteur_caractere_appreciation = 8;
								$pdf->SetFont('DejaVu','',$hauteur_caractere_appreciation);

								//suppression des espaces en début et en fin
								//$app_aff = trim($tab_bull['app'][$m][$i]);
								$app_aff="";
								if($tab_bull['aid'][$m]['message']!='') {
									$app_aff.=$tab_bull['aid'][$m]['message'];
								}
								//if($app_aff!='') {$app_aff.=" ";}
								if(($tab_bull['aid'][$m]['display_nom']=='y')&&($tab_bull['aid'][$m]['aid_nom']!='')) {
									if($app_aff!='') {$app_aff.=" ";}
									$app_aff.=$tab_bull['aid'][$m]['aid_nom'];
								}
								if($app_aff!='') {$app_aff.="\n";}
								$app_aff.=trim($tab_bull['aid'][$m]['app_grp']);

								fich_debug_bull("__________________________________________\n");
								fich_debug_bull("$app_aff\n");
								fich_debug_bull("__________________________________________\n");

								// DEBUT AJUSTEMENT TAILLE APPRECIATION
								$taille_texte_total = $pdf->GetStringWidth($app_aff);
								//$largeur_appreciation2 = $largeur_appreciation - $largeur_sous_matiere;
								$largeur_appreciation2=$param_bull2016["largeur_EPI_AP_Parcours_col_2"];

								if($use_cell_ajustee=="n") {
									//$taille_texte = (($espace_entre_matier/3)*$largeur_appreciation2);
									$nb_ligne_app = '2.8';
									//$nb_ligne_app = '3.8';
									//$nb_ligne_app = '4.8';
									$taille_texte_max = $nb_ligne_app * ($largeur_appreciation2-4);
									//$taille_texte_max = $nb_ligne_app * ($largeur_appreciation2);
									$grandeur_texte='test';

									fich_debug_bull("\$taille_texte_total=$taille_texte_total\n");
									fich_debug_bull("\$largeur_appreciation2=$largeur_appreciation2\n");
									fich_debug_bull("\$nb_ligne_app=$nb_ligne_app\n");
									//fich_debug_bull("\$taille_texte_max = \$nb_ligne_app * (\$largeur_appreciation2-4)=$nb_ligne_app * ($largeur_appreciation2-4)=$taille_texte_max\n");
									fich_debug_bull("\$taille_texte_max = \$nb_ligne_app * (\$largeur_appreciation2)=$nb_ligne_app * ($largeur_appreciation2)=$taille_texte_max\n");

									while($grandeur_texte!='ok') {
										if($taille_texte_max < $taille_texte_total)
										{
											$hauteur_caractere_appreciation = $hauteur_caractere_appreciation-0.3;
											//$hauteur_caractere_appreciation = $hauteur_caractere_appreciation-0.1;
											$pdf->SetFont('DejaVu','',$hauteur_caractere_appreciation);
											$taille_texte_total = $pdf->GetStringWidth($app_aff);
										}
										else {
											$grandeur_texte='ok';
										}
									}
									$grandeur_texte='test';
									$pdf->drawTextBox(($app_aff), $largeur_appreciation2, $hauteur_EPI_AP_Parcours, 'J', 'M', 1);
								}
								else {
									$texte=$app_aff;
									//$texte="Bla bla\nbli ".$app_aff;
									$taille_max_police=$hauteur_caractere_appreciation;
									$taille_min_police=ceil($taille_max_police/3);

									$largeur_dispo=$largeur_appreciation2;
									$h_cell=$hauteur_EPI_AP_Parcours;

									if(getSettingValue('suppr_balises_app_prof')=='y') {$texte=preg_replace('/<(.*)>/U','',$texte);}
									cell_ajustee(($texte),$pdf->GetX(),$pdf->GetY(),$largeur_dispo,$h_cell,$taille_max_police,$taille_min_police,'');
								}

								$y_courant+=$hauteur_EPI_AP_Parcours+0.5;
								$cpt_matiere++;
							}
						}
					}
				}
				// Fin des AID_e tagués EPI
			}
			//++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
			else {
				// On est sur une page appréciation groupe liée à un élève $i!=""

				// AID_b de type EPI
				for($loop=0;$loop<count($indice_AID_b_EPI);$loop++) {
					$m=$indice_AID_b_EPI[$loop];

					// Colonne 1 : Matière, prof

					$pdf->SetFillColor($param_bull2016["couleur_EPI_alt".($cpt_matiere%2+1)]["R"], $param_bull2016["couleur_EPI_alt".($cpt_matiere%2+1)]["V"], $param_bull2016["couleur_EPI_alt".($cpt_matiere%2+1)]["B"]);
					$pdf->Rect($param_bull2016["x_EPI_AP_Parcours"], $y_courant, $param_bull2016["largeur_EPI_AP_Parcours_col_1"], $hauteur_EPI_AP_Parcours, 'F');
					$pdf->SetFillColor(0, 0, 0);

					$pdf->SetTextColor(0, 0, 0);
					$pdf->SetXY($param_bull2016["x_EPI_AP_Parcours"], $y_courant);
					$pdf->SetFont('DejaVu','',8);

					if($tab_bull['eleve'][$i]['aid_b'][$m]['display_nom']=='z') {
						if((isset($tab_bull['eleve'][$i]['aid_b'][$m]['aid_nom']))&&($tab_bull['eleve'][$i]['aid_b'][$m]['aid_nom']!="")) {
							$info_nom_matiere=$tab_bull['eleve'][$i]['aid_b'][$m]['aid_nom'];
						}
						elseif((isset($tab_bull['eleve'][$i]['aid_b'][$m]['nom_complet']))&&($tab_bull['eleve'][$i]['aid_b'][$m]['nom_complet']!="")) {
							$info_nom_matiere=$tab_bull['eleve'][$i]['aid_b'][$m]['nom_complet'];
						}
						else {
							$info_nom_matiere=$tab_bull['eleve'][$i]['aid_b'][$m]['nom'];
						}
					}
					elseif($tab_bull['eleve'][$i]['aid_b'][$m]['display_nom']=='x') {
						if((isset($tab_bull['eleve'][$i]['aid_b'][$m]['nom']))&&($tab_bull['eleve'][$i]['aid_b'][$m]['nom']!="")) {
							$info_nom_matiere=$tab_bull['eleve'][$i]['aid_b'][$m]['nom'];
						}
						else {
							$info_nom_matiere=$tab_bull['eleve'][$i]['aid_b'][$m]['nom_complet'];
						}

						if((isset($tab_bull['eleve'][$i]['aid_b'][$m]['aid_nom']))&&($tab_bull['eleve'][$i]['aid_b'][$m]['aid_nom']!="")) {
							if($info_nom_matiere!="") {
								$info_nom_matiere.=": ";
							}
							$info_nom_matiere.=$tab_bull['eleve'][$i]['aid_b'][$m]['aid_nom'];
						}
					}
					else {
						if((isset($tab_bull['eleve'][$i]['aid_b'][$m]['nom_complet']))&&($tab_bull['eleve'][$i]['aid_b'][$m]['nom_complet']!="")) {
							$info_nom_matiere=$tab_bull['eleve'][$i]['aid_b'][$m]['nom_complet'];
						}
						else {
							$info_nom_matiere=$tab_bull['eleve'][$i]['aid_b'][$m]['nom'];
						}
					}
					if($info_nom_matiere=="") {
						$info_nom_matiere="AID";
					}

					$hauteur_caractere_matiere=8;
					$cell_ajustee_texte_matiere_ratio_min_max=3;

					// Forcé pour le moment
					$cell_ajustee_texte_matiere=1;
					if((isset($cell_ajustee_texte_matiere))&&($cell_ajustee_texte_matiere==1)) {
						// On met un décalage pour ne pas coller le texte à la bordure
						$Y_decal_cell_ajustee=2;
						$pdf->SetXY($param_bull2016["x_EPI_AP_Parcours"], $y_courant+$Y_decal_cell_ajustee);

						$texte=$info_nom_matiere;
						$taille_max_police=$hauteur_caractere_matiere;
						$taille_min_police=ceil($taille_max_police/$cell_ajustee_texte_matiere_ratio_min_max);

						$largeur_dispo=$param_bull2016["largeur_EPI_AP_Parcours_col_1"];
						$h_cell=$hauteur_EPI_AP_Parcours/2-$Y_decal_cell_ajustee;

						cell_ajustee($texte,$pdf->GetX(),$pdf->GetY(),$largeur_dispo,$h_cell,$taille_max_police,$taille_min_police,'');
		
					}
					else {
						$val = $pdf->GetStringWidth($info_nom_matiere);
						$taille_texte = $param_bull2016["largeur_EPI_AP_Parcours_col_1"]-2;
						$grandeur_texte='test';
						while($grandeur_texte!='ok') {
							if($taille_texte<$val)
							{
								$hauteur_caractere_matiere = $hauteur_caractere_matiere-0.3;
								$pdf->SetFont('DejaVu','B',$hauteur_caractere_matiere);
								$val = $pdf->GetStringWidth($info_nom_matiere);
							}
							else {
								$grandeur_texte='ok';
							}
						}
						$grandeur_texte='test';
						$Y_decal=$y_courant;
						$pdf->SetXY($param_bull2016["x_EPI_AP_Parcours"], $Y_decal);
						$pdf->Cell($param_bull2016["largeur_EPI_AP_Parcours_col_1"], $hauteur_EPI_AP_Parcours/2, ($info_nom_matiere),'',1,'L');
					}


					// On note l'ordonnée pour le nom des professeurs
					$Y_decal = $y_courant+($hauteur_EPI_AP_Parcours/2);
					$pdf->SetXY($param_bull2016["x_EPI_AP_Parcours"], $Y_decal);
					$pdf->SetFont('DejaVu','',8);

					// nom des professeurs
					if ( isset($tab_bull['eleve'][$i]['aid_b'][$m]['aid_prof_resp_login'][0]) )
					{

						// Présentation en ligne des profs
						// On n'a pas forcément le formatage choisi pour la classe...
						//$text_prof=$tab_bull['groupe'][$m]["profs"]["proflist_string"]."  ";
						$text_prof="";
						for($loop_prof_grp=0;$loop_prof_grp<count($tab_bull['eleve'][$i]['aid_b'][$m]['aid_prof_resp_login']);$loop_prof_grp++) {
							$tmp_login_prof=$tab_bull['eleve'][$i]['aid_b'][$m]['aid_prof_resp_login'][$loop_prof_grp];
							if($loop_prof_grp>0) {$text_prof.=", ";}
							$text_prof.=affiche_utilisateur($tmp_login_prof,$tab_bull['eleve'][$i]['id_classe']);
						}

						if($text_prof!="") {
							//$espace_matiere_prof = $espace_entre_matier/2;
							$espace_matiere_prof = $hauteur_EPI_AP_Parcours/2;
							$hauteur_caractere_prof = 7;

							$texte=$text_prof;
							$taille_max_police=$hauteur_caractere_prof;
							$taille_min_police=ceil($hauteur_caractere_prof/3);

							$largeur_dispo=$param_bull2016["largeur_EPI_AP_Parcours_col_1"];
							$h_cell=$espace_matiere_prof;

							$pdf->SetX($param_bull2016["x_EPI_AP_Parcours"]);

							cell_ajustee(($texte),$pdf->GetX(),$pdf->GetY(),$largeur_dispo,$h_cell,$taille_max_police,$taille_min_police,'');
						}
					}


					// Colonne 2 : Appréciation
					$pdf->SetFillColor($param_bull2016["couleur_EPI_alt".($cpt_matiere%2+1)]["R"], $param_bull2016["couleur_EPI_alt".($cpt_matiere%2+1)]["V"], $param_bull2016["couleur_EPI_alt".($cpt_matiere%2+1)]["B"]);
					$pdf->Rect($param_bull2016["x_EPI_AP_Parcours_col_2"], $y_courant, $param_bull2016["largeur_EPI_AP_Parcours_col_2"], $hauteur_EPI_AP_Parcours, 'F');

					$pdf->SetFillColor(0, 0, 0);
					$pdf->SetXY($param_bull2016["x_EPI_AP_Parcours_col_2"], $y_courant);

					// calcul de la taille du texte des appréciations
					$hauteur_caractere_appreciation = 8;
					$pdf->SetFont('DejaVu','',$hauteur_caractere_appreciation);

					//suppression des espaces en début et en fin
					//$app_aff = trim($tab_bull['app'][$m][$i]);
					$app_aff="";
					if($tab_bull['eleve'][$i]['aid_b'][$m]['message']!='') {
						$app_aff.=$tab_bull['eleve'][$i]['aid_b'][$m]['message'];
					}
					//if($app_aff!='') {$app_aff.=" ";}
					if(($tab_bull['eleve'][$i]['aid_b'][$m]['display_nom']=='y')&&($tab_bull['eleve'][$i]['aid_b'][$m]['aid_nom']!='')) {
						if($app_aff!='') {$app_aff.=" ";}
						$app_aff.=$tab_bull['eleve'][$i]['aid_b'][$m]['aid_nom'];
					}
					if($app_aff!='') {$app_aff.="\n";}
					$app_aff.=trim($tab_bull['eleve'][$i]['aid_b'][$m]['aid_appreciation']);

					fich_debug_bull("__________________________________________\n");
					fich_debug_bull("$app_aff\n");
					fich_debug_bull("__________________________________________\n");

					// DEBUT AJUSTEMENT TAILLE APPRECIATION
					$taille_texte_total = $pdf->GetStringWidth($app_aff);
					//$largeur_appreciation2 = $largeur_appreciation - $largeur_sous_matiere;
					$largeur_appreciation2=$param_bull2016["largeur_EPI_AP_Parcours_col_2"];

					if($use_cell_ajustee=="n") {
						//$taille_texte = (($espace_entre_matier/3)*$largeur_appreciation2);
						$nb_ligne_app = '2.8';
						//$nb_ligne_app = '3.8';
						//$nb_ligne_app = '4.8';
						$taille_texte_max = $nb_ligne_app * ($largeur_appreciation2-4);
						//$taille_texte_max = $nb_ligne_app * ($largeur_appreciation2);
						$grandeur_texte='test';

						fich_debug_bull("\$taille_texte_total=$taille_texte_total\n");
						fich_debug_bull("\$largeur_appreciation2=$largeur_appreciation2\n");
						fich_debug_bull("\$nb_ligne_app=$nb_ligne_app\n");
						//fich_debug_bull("\$taille_texte_max = \$nb_ligne_app * (\$largeur_appreciation2-4)=$nb_ligne_app * ($largeur_appreciation2-4)=$taille_texte_max\n");
						fich_debug_bull("\$taille_texte_max = \$nb_ligne_app * (\$largeur_appreciation2)=$nb_ligne_app * ($largeur_appreciation2)=$taille_texte_max\n");

						while($grandeur_texte!='ok') {
							if($taille_texte_max < $taille_texte_total)
							{
								$hauteur_caractere_appreciation = $hauteur_caractere_appreciation-0.3;
								//$hauteur_caractere_appreciation = $hauteur_caractere_appreciation-0.1;
								$pdf->SetFont('DejaVu','',$hauteur_caractere_appreciation);
								$taille_texte_total = $pdf->GetStringWidth($app_aff);
							}
							else {
								$grandeur_texte='ok';
							}
						}
						$grandeur_texte='test';
						$pdf->drawTextBox(($app_aff), $largeur_appreciation2, $hauteur_EPI_AP_Parcours, 'J', 'M', 1);
					}
					else {
						$texte=$app_aff;
						//$texte="Bla bla\nbli ".$app_aff;
						$taille_max_police=$hauteur_caractere_appreciation;
						$taille_min_police=ceil($taille_max_police/3);

						$largeur_dispo=$largeur_appreciation2;
						$h_cell=$hauteur_EPI_AP_Parcours;

						if(getSettingValue('suppr_balises_app_prof')=='y') {$texte=preg_replace('/<(.*)>/U','',$texte);}
						cell_ajustee(($texte),$pdf->GetX(),$pdf->GetY(),$largeur_dispo,$h_cell,$taille_max_police,$taille_min_police,'');
					}

					$y_courant+=$hauteur_EPI_AP_Parcours+0.5;
					$cpt_matiere++;


				}
				// Fin des AID_b tagués EPI

				// Enseignements de la table 'groupes' tagués EPI
				for($m=0;$m<count($tab_bull['groupe']);$m++) {
					//if(isset($tab_bull['note'][$m][$i])) {
					// On n'affiche pas ici les groupes correspondant à AP, EPI ou Parcours
					if((isset($tab_bull['note'][$m][$i]))&&
					(isset($tab_bull['groupe'][$m]['type_grp'][0]))&&
					($tab_bull['groupe'][$m]['type_grp'][0]['nom_court']=="EPI")) {

						// Colonne 1 : Matière, prof

						$pdf->SetFillColor($param_bull2016["couleur_EPI_alt".($cpt_matiere%2+1)]["R"], $param_bull2016["couleur_EPI_alt".($cpt_matiere%2+1)]["V"], $param_bull2016["couleur_EPI_alt".($cpt_matiere%2+1)]["B"]);
						$pdf->Rect($param_bull2016["x_EPI_AP_Parcours"], $y_courant, $param_bull2016["largeur_EPI_AP_Parcours_col_1"], $hauteur_EPI_AP_Parcours, 'F');
						$pdf->SetFillColor(0, 0, 0);

						$pdf->SetTextColor(0, 0, 0);
						$pdf->SetXY($param_bull2016["x_EPI_AP_Parcours"], $y_courant);
						$pdf->SetFont('DejaVu','',8);

						if(getSettingValue('bul_rel_nom_matieres')=='nom_groupe') {
							$info_nom_matiere=$tab_bull['groupe'][$m]['name'];
						}
						elseif(getSettingValue('bul_rel_nom_matieres')=='description_groupe') {
							$info_nom_matiere=$tab_bull['groupe'][$m]['description'];
						}
						else {
							// Pour parer au bug sur la suppression de matière alors que des groupes sont conservés:
							if(isset($tab_bull['groupe'][$m]['matiere']['nom_complet'])) {
								$info_nom_matiere=$tab_bull['groupe'][$m]['matiere']['nom_complet'];
							}
							else {
								$info_nom_matiere=$tab_bull['groupe'][$m]['name']." (".$tab_bull['groupe'][$m]['id'].")";
							}
						}

						$hauteur_caractere_matiere=8;
						$cell_ajustee_texte_matiere_ratio_min_max=3;

						// 20130927 : cell_ajustee() ou pas sur le nom de matière/enseignement
						$cell_ajustee_texte_matiere=1;
						if((isset($cell_ajustee_texte_matiere))&&($cell_ajustee_texte_matiere==1)) {
							// Encadrement
							//$pdf->Cell($tab_modele_pdf["largeur_matiere"][$classe_id], $espace_entre_matier, "",'LRBT',1,'L');

							// cell_ajustee() ne centre pas verticalement le texte.
							// On met un décalage pour ne pas coller le texte à la bordure
							$Y_decal_cell_ajustee=2;
							// On repositionne et on inscrit le nom de matière sur la moitié de la hauteur de la cellule
							//$pdf->SetXY($X_bloc_matiere, $Y_decal+$Y_decal_cell_ajustee);
							//$pdf->SetXY($param_bull2016["x_acquis_col_1"], $y_courant);

							$pdf->SetXY($param_bull2016["x_EPI_AP_Parcours"], $y_courant+$Y_decal_cell_ajustee);

							$texte=$info_nom_matiere;
							$taille_max_police=$hauteur_caractere_matiere;
							//$taille_min_police=ceil($taille_max_police/$tab_modele_pdf["cell_ajustee_texte_matiere_ratio_min_max"][$classe_id]);
							$taille_min_police=ceil($taille_max_police/$cell_ajustee_texte_matiere_ratio_min_max);

							$largeur_dispo=$param_bull2016["largeur_EPI_AP_Parcours_col_1"];
							//$h_cell=$espace_entre_matier/2-$Y_decal_cell_ajustee;
							$h_cell=$hauteur_EPI_AP_Parcours/2-$Y_decal_cell_ajustee;

							//cell_ajustee("<b>".$texte."</b>",$pdf->GetX(),$pdf->GetY(),$largeur_dispo,$h_cell,$taille_max_police,$taille_min_police,'');
							cell_ajustee($texte,$pdf->GetX(),$pdf->GetY(),$largeur_dispo,$h_cell,$taille_max_police,$taille_min_police,'');
		
						}
						else {
							$val = $pdf->GetStringWidth($info_nom_matiere);
							$taille_texte = $param_bull2016["largeur_EPI_AP_Parcours_col_1"]-2;
							$grandeur_texte='test';
							while($grandeur_texte!='ok') {
								if($taille_texte<$val)
								{
									$hauteur_caractere_matiere = $hauteur_caractere_matiere-0.3;
									$pdf->SetFont('DejaVu','',$hauteur_caractere_matiere);
									$val = $pdf->GetStringWidth($info_nom_matiere);
								}
								else {
									$grandeur_texte='ok';
								}
							}
							$grandeur_texte='test';
							$Y_decal=$y_courant;
							$pdf->SetXY($param_bull2016["x_EPI_AP_Parcours"], $Y_decal);
							$pdf->Cell($param_bull2016["largeur_EPI_AP_Parcours_col_1"], $hauteur_EPI_AP_Parcours/2, ($info_nom_matiere),'',1,'L');
						}
		

						// On note l'ordonnée pour le nom des professeurs
						$Y_decal = $y_courant+($hauteur_EPI_AP_Parcours/2);
						$pdf->SetXY($param_bull2016["x_EPI_AP_Parcours"], $Y_decal);
						$pdf->SetFont('DejaVu','',8);

						//fich_debug_bull("\$info_nom_matiere=$info_nom_matiere\n");
						//fich_debug_bull("Le nom de matière est écrit; on est à mi-hauteur de la cellule pour écrire le nom du prof:\n");
						//fich_debug_bull("\$Y_decal=$Y_decal\n");

						// nom des professeurs

						if ( isset($tab_bull['groupe'][$m]["profs"]["list"]) )
						{

							// Présentation en ligne des profs
							// On n'a pas forcément le formatage choisi pour la classe...
							//$text_prof=$tab_bull['groupe'][$m]["profs"]["proflist_string"]."  ";
							$text_prof="";
							for($loop_prof_grp=0;$loop_prof_grp<count($tab_bull['groupe'][$m]["profs"]["list"]);$loop_prof_grp++) {
								$tmp_login_prof=$tab_bull['groupe'][$m]["profs"]["list"][$loop_prof_grp];
								if($loop_prof_grp>0) {$text_prof.=", ";}
								$text_prof.=affiche_utilisateur($tmp_login_prof,$tab_bull['eleve'][$i]['id_classe']);
							}

							if($text_prof!="") {
								//$espace_matiere_prof = $espace_entre_matier/2;
								$espace_matiere_prof = $hauteur_EPI_AP_Parcours/2;
								$hauteur_caractere_prof = 7;

								/*
								if($use_cell_ajustee=="n") {
									$pdf->SetFont('DejaVu','',$hauteur_caractere_prof);
									$val = $pdf->GetStringWidth($text_prof);
									$taille_texte = ($tab_modele_pdf["largeur_matiere"][$classe_id]);
									$grandeur_texte='test';
									while($grandeur_texte!='ok') {
										if($taille_texte<$val)
										{
											$hauteur_caractere_prof = $hauteur_caractere_prof-0.3;
											$pdf->SetFont('DejaVu','',$hauteur_caractere_prof);
											$val = $pdf->GetStringWidth($text_prof);
										}
										else {
											$grandeur_texte='ok';
										}
									}
									$grandeur_texte='test';
									$pdf->SetX($X_bloc_matiere);
									$pdf->Cell($tab_modele_pdf["largeur_matiere"][$classe_id], $espace_matiere_prof, ($text_prof),'LR',1,'L');
								}
								else {
								*/
									$texte=$text_prof;
									$taille_max_police=$hauteur_caractere_prof;
									$taille_min_police=ceil($hauteur_caractere_prof/3);

									$largeur_dispo=$param_bull2016["largeur_EPI_AP_Parcours_col_1"];
									$h_cell=$espace_matiere_prof;

									$pdf->SetX($param_bull2016["x_EPI_AP_Parcours"]);

									cell_ajustee(($texte),$pdf->GetX(),$pdf->GetY(),$largeur_dispo,$h_cell,$taille_max_police,$taille_min_police,'');
								//}
							}
						}


						// Colonne 2 : Appréciation
						$pdf->SetFillColor($param_bull2016["couleur_EPI_alt".($cpt_matiere%2+1)]["R"], $param_bull2016["couleur_EPI_alt".($cpt_matiere%2+1)]["V"], $param_bull2016["couleur_EPI_alt".($cpt_matiere%2+1)]["B"]);
						$pdf->Rect($param_bull2016["x_EPI_AP_Parcours_col_2"], $y_courant, $param_bull2016["largeur_EPI_AP_Parcours_col_2"], $hauteur_EPI_AP_Parcours, 'F');

						$pdf->SetFillColor(0, 0, 0);
						$pdf->SetXY($param_bull2016["x_EPI_AP_Parcours_col_2"], $y_courant);

						// calcul de la taille du texte des appréciations
						$hauteur_caractere_appreciation = 8;
						$pdf->SetFont('DejaVu','',$hauteur_caractere_appreciation);

						//suppression des espaces en début et en fin
						$app_aff = trim($tab_bull['app'][$m][$i]);

						fich_debug_bull("__________________________________________\n");
						fich_debug_bull("$app_aff\n");
						fich_debug_bull("__________________________________________\n");

						// DEBUT AJUSTEMENT TAILLE APPRECIATION
						$taille_texte_total = $pdf->GetStringWidth($app_aff);
						//$largeur_appreciation2 = $largeur_appreciation - $largeur_sous_matiere;
						$largeur_appreciation2=$param_bull2016["largeur_EPI_AP_Parcours_col_2"];

						if($use_cell_ajustee=="n") {
							//$taille_texte = (($espace_entre_matier/3)*$largeur_appreciation2);
							$nb_ligne_app = '2.8';
							//$nb_ligne_app = '3.8';
							//$nb_ligne_app = '4.8';
							$taille_texte_max = $nb_ligne_app * ($largeur_appreciation2-4);
							//$taille_texte_max = $nb_ligne_app * ($largeur_appreciation2);
							$grandeur_texte='test';

							fich_debug_bull("\$taille_texte_total=$taille_texte_total\n");
							fich_debug_bull("\$largeur_appreciation2=$largeur_appreciation2\n");
							fich_debug_bull("\$nb_ligne_app=$nb_ligne_app\n");
							//fich_debug_bull("\$taille_texte_max = \$nb_ligne_app * (\$largeur_appreciation2-4)=$nb_ligne_app * ($largeur_appreciation2-4)=$taille_texte_max\n");
							fich_debug_bull("\$taille_texte_max = \$nb_ligne_app * (\$largeur_appreciation2)=$nb_ligne_app * ($largeur_appreciation2)=$taille_texte_max\n");

							while($grandeur_texte!='ok') {
								if($taille_texte_max < $taille_texte_total)
								{
									$hauteur_caractere_appreciation = $hauteur_caractere_appreciation-0.3;
									//$hauteur_caractere_appreciation = $hauteur_caractere_appreciation-0.1;
									$pdf->SetFont('DejaVu','',$hauteur_caractere_appreciation);
									$taille_texte_total = $pdf->GetStringWidth($app_aff);
								}
								else {
									$grandeur_texte='ok';
								}
							}
							$grandeur_texte='test';
							$pdf->drawTextBox(($app_aff), $largeur_appreciation2, $hauteur_EPI_AP_Parcours, 'J', 'M', 1);
						}
						else {
							$texte=$app_aff;
							//$texte="Bla bla\nbli ".$app_aff;
							$taille_max_police=$hauteur_caractere_appreciation;
							$taille_min_police=ceil($taille_max_police/3);

							$largeur_dispo=$largeur_appreciation2;
							$h_cell=$hauteur_EPI_AP_Parcours;

							if(getSettingValue('suppr_balises_app_prof')=='y') {$texte=preg_replace('/<(.*)>/U','',$texte);}
							cell_ajustee(($texte),$pdf->GetX(),$pdf->GetY(),$largeur_dispo,$h_cell,$taille_max_police,$taille_min_police,'');
						}

						$y_courant+=$hauteur_EPI_AP_Parcours+0.5;
						$cpt_matiere++;

					}
				}
				// Fin des enseignements de la table 'groupes' tagués EPI


				// AID_e de type EPI
				for($loop=0;$loop<count($indice_AID_e_EPI);$loop++) {
					$m=$indice_AID_e_EPI[$loop];

					// Colonne 1 : Matière, prof

					$pdf->SetFillColor($param_bull2016["couleur_EPI_alt".($cpt_matiere%2+1)]["R"], $param_bull2016["couleur_EPI_alt".($cpt_matiere%2+1)]["V"], $param_bull2016["couleur_EPI_alt".($cpt_matiere%2+1)]["B"]);
					$pdf->Rect($param_bull2016["x_EPI_AP_Parcours"], $y_courant, $param_bull2016["largeur_EPI_AP_Parcours_col_1"], $hauteur_EPI_AP_Parcours, 'F');
					$pdf->SetFillColor(0, 0, 0);

					$pdf->SetTextColor(0, 0, 0);
					$pdf->SetXY($param_bull2016["x_EPI_AP_Parcours"], $y_courant);
					$pdf->SetFont('DejaVu','',8);

					if($tab_bull['eleve'][$i]['aid_e'][$m]['display_nom']=='z') {
						if((isset($tab_bull['eleve'][$i]['aid_e'][$m]['aid_nom']))&&($tab_bull['eleve'][$i]['aid_e'][$m]['aid_nom']!="")) {
							$info_nom_matiere=$tab_bull['eleve'][$i]['aid_e'][$m]['aid_nom'];
						}
						elseif((isset($tab_bull['eleve'][$i]['aid_e'][$m]['nom_complet']))&&($tab_bull['eleve'][$i]['aid_e'][$m]['nom_complet']!="")) {
							$info_nom_matiere=$tab_bull['eleve'][$i]['aid_e'][$m]['nom_complet'];
						}
						else {
							$info_nom_matiere=$tab_bull['eleve'][$i]['aid_e'][$m]['nom'];
						}
					}
					elseif($tab_bull['eleve'][$i]['aid_e'][$m]['display_nom']=='x') {
						if((isset($tab_bull['eleve'][$i]['aid_e'][$m]['nom']))&&($tab_bull['eleve'][$i]['aid_e'][$m]['nom']!="")) {
							$info_nom_matiere=$tab_bull['eleve'][$i]['aid_e'][$m]['nom'];
						}
						else {
							$info_nom_matiere=$tab_bull['eleve'][$i]['aid_e'][$m]['nom_complet'];
						}

						if((isset($tab_bull['eleve'][$i]['aid_e'][$m]['aid_nom']))&&($tab_bull['eleve'][$i]['aid_e'][$m]['aid_nom']!="")) {
							if($info_nom_matiere!="") {
								$info_nom_matiere.=": ";
							}
							$info_nom_matiere.=$tab_bull['eleve'][$i]['aid_e'][$m]['aid_nom'];
						}
					}
					else {
						if((isset($tab_bull['eleve'][$i]['aid_e'][$m]['nom_complet']))&&($tab_bull['eleve'][$i]['aid_e'][$m]['nom_complet']!="")) {
							$info_nom_matiere=$tab_bull['eleve'][$i]['aid_e'][$m]['nom_complet'];
						}
						else {
							$info_nom_matiere=$tab_bull['eleve'][$i]['aid_e'][$m]['nom'];
						}
					}
					if($info_nom_matiere=="") {
						$info_nom_matiere="AID";
					}

					$hauteur_caractere_matiere=8;
					$cell_ajustee_texte_matiere_ratio_min_max=3;

					// Forcé pour le moment
					$cell_ajustee_texte_matiere=1;
					if((isset($cell_ajustee_texte_matiere))&&($cell_ajustee_texte_matiere==1)) {
						// On met un décalage pour ne pas coller le texte à la bordure
						$Y_decal_cell_ajustee=2;
						$pdf->SetXY($param_bull2016["x_EPI_AP_Parcours"], $y_courant+$Y_decal_cell_ajustee);

						$texte=$info_nom_matiere;
						$taille_max_police=$hauteur_caractere_matiere;
						$taille_min_police=ceil($taille_max_police/$cell_ajustee_texte_matiere_ratio_min_max);

						$largeur_dispo=$param_bull2016["largeur_EPI_AP_Parcours_col_1"];
						$h_cell=$hauteur_EPI_AP_Parcours/2-$Y_decal_cell_ajustee;

						cell_ajustee($texte,$pdf->GetX(),$pdf->GetY(),$largeur_dispo,$h_cell,$taille_max_police,$taille_min_police,'');
		
					}
					else {
						$val = $pdf->GetStringWidth($info_nom_matiere);
						$taille_texte = $param_bull2016["largeur_EPI_AP_Parcours_col_1"]-2;
						$grandeur_texte='test';
						while($grandeur_texte!='ok') {
							if($taille_texte<$val)
							{
								$hauteur_caractere_matiere = $hauteur_caractere_matiere-0.3;
								$pdf->SetFont('DejaVu','B',$hauteur_caractere_matiere);
								$val = $pdf->GetStringWidth($info_nom_matiere);
							}
							else {
								$grandeur_texte='ok';
							}
						}
						$grandeur_texte='test';
						$Y_decal=$y_courant;
						$pdf->SetXY($param_bull2016["x_EPI_AP_Parcours"], $Y_decal);
						$pdf->Cell($param_bull2016["largeur_EPI_AP_Parcours_col_1"], $hauteur_EPI_AP_Parcours/2, ($info_nom_matiere),'',1,'L');
					}


					// On note l'ordonnée pour le nom des professeurs
					$Y_decal = $y_courant+($hauteur_EPI_AP_Parcours/2);
					$pdf->SetXY($param_bull2016["x_EPI_AP_Parcours"], $Y_decal);
					$pdf->SetFont('DejaVu','',8);

					// nom des professeurs
					if ( isset($tab_bull['eleve'][$i]['aid_e'][$m]['aid_prof_resp_login'][0]) )
					{

						// Présentation en ligne des profs
						// On n'a pas forcément le formatage choisi pour la classe...
						//$text_prof=$tab_bull['groupe'][$m]["profs"]["proflist_string"]."  ";
						$text_prof="";
						for($loop_prof_grp=0;$loop_prof_grp<count($tab_bull['eleve'][$i]['aid_e'][$m]['aid_prof_resp_login']);$loop_prof_grp++) {
							$tmp_login_prof=$tab_bull['eleve'][$i]['aid_e'][$m]['aid_prof_resp_login'][$loop_prof_grp];
							if($loop_prof_grp>0) {$text_prof.=", ";}
							$text_prof.=affiche_utilisateur($tmp_login_prof,$tab_bull['eleve'][$i]['id_classe']);
						}

						if($text_prof!="") {
							//$espace_matiere_prof = $espace_entre_matier/2;
							$espace_matiere_prof = $hauteur_EPI_AP_Parcours/2;
							$hauteur_caractere_prof = 7;

							$texte=$text_prof;
							$taille_max_police=$hauteur_caractere_prof;
							$taille_min_police=ceil($hauteur_caractere_prof/3);

							$largeur_dispo=$param_bull2016["largeur_EPI_AP_Parcours_col_1"];
							$h_cell=$espace_matiere_prof;

							$pdf->SetX($param_bull2016["x_EPI_AP_Parcours"]);

							cell_ajustee(($texte),$pdf->GetX(),$pdf->GetY(),$largeur_dispo,$h_cell,$taille_max_police,$taille_min_police,'');
						}
					}


					// Colonne 2 : Appréciation
					$pdf->SetFillColor($param_bull2016["couleur_EPI_alt".($cpt_matiere%2+1)]["R"], $param_bull2016["couleur_EPI_alt".($cpt_matiere%2+1)]["V"], $param_bull2016["couleur_EPI_alt".($cpt_matiere%2+1)]["B"]);
					$pdf->Rect($param_bull2016["x_EPI_AP_Parcours_col_2"], $y_courant, $param_bull2016["largeur_EPI_AP_Parcours_col_2"], $hauteur_EPI_AP_Parcours, 'F');

					$pdf->SetFillColor(0, 0, 0);
					$pdf->SetXY($param_bull2016["x_EPI_AP_Parcours_col_2"], $y_courant);

					// calcul de la taille du texte des appréciations
					$hauteur_caractere_appreciation = 8;
					$pdf->SetFont('DejaVu','',$hauteur_caractere_appreciation);

					//suppression des espaces en début et en fin
					//$app_aff = trim($tab_bull['app'][$m][$i]);
					$app_aff="";
					if($tab_bull['eleve'][$i]['aid_e'][$m]['message']!='') {
						$app_aff.=$tab_bull['eleve'][$i]['aid_e'][$m]['message'];
					}
					//if($app_aff!='') {$app_aff.=" ";}
					if(($tab_bull['eleve'][$i]['aid_e'][$m]['display_nom']=='y')&&($tab_bull['eleve'][$i]['aid_e'][$m]['aid_nom']!='')) {
						if($app_aff!='') {$app_aff.=" ";}
						$app_aff.=$tab_bull['eleve'][$i]['aid_e'][$m]['aid_nom'];
					}
					if($app_aff!='') {$app_aff.="\n";}
					$app_aff.=trim($tab_bull['eleve'][$i]['aid_e'][$m]['aid_appreciation']);

					fich_debug_bull("__________________________________________\n");
					fich_debug_bull("$app_aff\n");
					fich_debug_bull("__________________________________________\n");

					// DEBUT AJUSTEMENT TAILLE APPRECIATION
					$taille_texte_total = $pdf->GetStringWidth($app_aff);
					//$largeur_appreciation2 = $largeur_appreciation - $largeur_sous_matiere;
					$largeur_appreciation2=$param_bull2016["largeur_EPI_AP_Parcours_col_2"];

					if($use_cell_ajustee=="n") {
						//$taille_texte = (($espace_entre_matier/3)*$largeur_appreciation2);
						$nb_ligne_app = '2.8';
						//$nb_ligne_app = '3.8';
						//$nb_ligne_app = '4.8';
						$taille_texte_max = $nb_ligne_app * ($largeur_appreciation2-4);
						//$taille_texte_max = $nb_ligne_app * ($largeur_appreciation2);
						$grandeur_texte='test';

						fich_debug_bull("\$taille_texte_total=$taille_texte_total\n");
						fich_debug_bull("\$largeur_appreciation2=$largeur_appreciation2\n");
						fich_debug_bull("\$nb_ligne_app=$nb_ligne_app\n");
						//fich_debug_bull("\$taille_texte_max = \$nb_ligne_app * (\$largeur_appreciation2-4)=$nb_ligne_app * ($largeur_appreciation2-4)=$taille_texte_max\n");
						fich_debug_bull("\$taille_texte_max = \$nb_ligne_app * (\$largeur_appreciation2)=$nb_ligne_app * ($largeur_appreciation2)=$taille_texte_max\n");

						while($grandeur_texte!='ok') {
							if($taille_texte_max < $taille_texte_total)
							{
								$hauteur_caractere_appreciation = $hauteur_caractere_appreciation-0.3;
								//$hauteur_caractere_appreciation = $hauteur_caractere_appreciation-0.1;
								$pdf->SetFont('DejaVu','',$hauteur_caractere_appreciation);
								$taille_texte_total = $pdf->GetStringWidth($app_aff);
							}
							else {
								$grandeur_texte='ok';
							}
						}
						$grandeur_texte='test';
						$pdf->drawTextBox(($app_aff), $largeur_appreciation2, $hauteur_EPI_AP_Parcours, 'J', 'M', 1);
					}
					else {
						$texte=$app_aff;
						//$texte="Bla bla\nbli ".$app_aff;
						$taille_max_police=$hauteur_caractere_appreciation;
						$taille_min_police=ceil($taille_max_police/3);

						$largeur_dispo=$largeur_appreciation2;
						$h_cell=$hauteur_EPI_AP_Parcours;

						if(getSettingValue('suppr_balises_app_prof')=='y') {$texte=preg_replace('/<(.*)>/U','',$texte);}
						cell_ajustee(($texte),$pdf->GetX(),$pdf->GetY(),$largeur_dispo,$h_cell,$taille_max_police,$taille_min_police,'');
					}

					$y_courant+=$hauteur_EPI_AP_Parcours+0.5;
					$cpt_matiere++;


				}
				// Fin des AID_e tagués EPI
			}

			//$y_courant+=$param_bull2016["espace_vertical_entre_sections_EPI_AP_Parcours"];
		}

		//++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
		//++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
		//++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

		//=========================================

		// AP

		if($nb_AP>0) {
			// On met un blanc de 5mm au-dessus de la ligne de titre EPI
			//$y_courant+=5;
			$y_courant+=$param_bull2016["espace_vertical_entre_sections_EPI_AP_Parcours"];

			$pdf->SetFillColor($param_bull2016["couleur_bandeau_EPI"]["R"], $param_bull2016["couleur_bandeau_EPI"]["V"], $param_bull2016["couleur_bandeau_EPI"]["B"]);
			$pdf->Rect($param_bull2016["x_EPI_AP_Parcours"], $y_courant, $param_bull2016["largeur_EPI_AP_Parcours"], $param_bull2016["hauteur_ligne_titre_EPI_AP_Parcours"], 'F');

			$pdf->SetFillColor(0, 0, 0);
			//$pdf->SetTextColor(255, 255, 255);
			$pdf->SetTextColor(0, 0, 0);
			$pdf->SetXY($param_bull2016["x_EPI_AP_Parcours"], $y_courant);
			$pdf->SetFont('DejaVu','',8);
			$pdf->Cell($param_bull2016["largeur_EPI_AP_Parcours"],7, "Accompagnement personnalisé : actions réalisées et implication de l'élève",0,2,'L');

			$cpt_matiere=0;
			$y_courant+=$param_bull2016["hauteur_ligne_titre_EPI_AP_Parcours"]+0.5;


			if("$i"=="") {

				// AID_b de type AP
				if(isset($tab_bull['aid'])) {
					for($m=0;$m<count($tab_bull['aid']);$m++) {
						if($tab_bull['aid'][$m]['order_display1']=="b") {
							if($tab_bull['aid'][$m]['type_aid']==1) {
								for($loop=0;$loop<count($indice_AID_b_AP);$loop++) {
									$m=$indice_AID_b_AP[$loop];

									// Colonne 1 : Matière, prof

									$pdf->SetFillColor($param_bull2016["couleur_EPI_alt".($cpt_matiere%2+1)]["R"], $param_bull2016["couleur_EPI_alt".($cpt_matiere%2+1)]["V"], $param_bull2016["couleur_EPI_alt".($cpt_matiere%2+1)]["B"]);
									$pdf->Rect($param_bull2016["x_EPI_AP_Parcours"], $y_courant, $param_bull2016["largeur_EPI_AP_Parcours_col_1"], $hauteur_EPI_AP_Parcours, 'F');
									$pdf->SetFillColor(0, 0, 0);

									$pdf->SetTextColor(0, 0, 0);
									$pdf->SetXY($param_bull2016["x_EPI_AP_Parcours"], $y_courant);
									$pdf->SetFont('DejaVu','',8);

									if($tab_bull['aid'][$m]['display_nom']=='z') {
										if((isset($tab_bull['aid'][$m]['aid_nom']))&&($tab_bull['aid'][$m]['aid_nom']!="")) {
											$info_nom_matiere=$tab_bull['aid'][$m]['aid_nom'];
										}
										elseif((isset($tab_bull['aid'][$m]['nom_complet']))&&($tab_bull['aid'][$m]['nom_complet']!="")) {
											$info_nom_matiere=$tab_bull['aid'][$m]['nom_complet'];
										}
										else {
											$info_nom_matiere=$tab_bull['aid'][$m]['nom'];
										}
									}
									elseif($tab_bull['aid'][$m]['display_nom']=='x') {
										if((isset($tab_bull['aid'][$m]['nom']))&&($tab_bull['aid'][$m]['nom']!="")) {
											$info_nom_matiere=$tab_bull['aid'][$m]['nom'];
										}
										else {
											$info_nom_matiere=$tab_bull['aid'][$m]['nom_complet'];
										}

										if((isset($tab_bull['aid'][$m]['aid_nom']))&&($tab_bull['aid'][$m]['aid_nom']!="")) {
											if($info_nom_matiere!="") {
												$info_nom_matiere.=": ";
											}
											$info_nom_matiere.=$tab_bull['aid'][$m]['aid_nom'];
										}
									}
									else {
										if((isset($tab_bull['aid'][$m]['nom_complet']))&&($tab_bull['aid'][$m]['nom_complet']!="")) {
											$info_nom_matiere=$tab_bull['aid'][$m]['nom_complet'];
										}
										else {
											$info_nom_matiere=$tab_bull['aid'][$m]['nom'];
										}
									}
									if($info_nom_matiere=="") {
										$info_nom_matiere="AID";
									}

									$hauteur_caractere_matiere=8;
									$cell_ajustee_texte_matiere_ratio_min_max=3;

									// Forcé pour le moment
									$cell_ajustee_texte_matiere=1;
									if((isset($cell_ajustee_texte_matiere))&&($cell_ajustee_texte_matiere==1)) {
										// On met un décalage pour ne pas coller le texte à la bordure
										$Y_decal_cell_ajustee=2;
										$pdf->SetXY($param_bull2016["x_EPI_AP_Parcours"], $y_courant+$Y_decal_cell_ajustee);

										$texte=$info_nom_matiere;
										$taille_max_police=$hauteur_caractere_matiere;
										$taille_min_police=ceil($taille_max_police/$cell_ajustee_texte_matiere_ratio_min_max);

										$largeur_dispo=$param_bull2016["largeur_EPI_AP_Parcours_col_1"];
										$h_cell=$hauteur_EPI_AP_Parcours/2-$Y_decal_cell_ajustee;

										cell_ajustee($texte,$pdf->GetX(),$pdf->GetY(),$largeur_dispo,$h_cell,$taille_max_police,$taille_min_police,'');
		
									}
									else {
										$val = $pdf->GetStringWidth($info_nom_matiere);
										$taille_texte = $param_bull2016["largeur_EPI_AP_Parcours_col_1"]-2;
										$grandeur_texte='test';
										while($grandeur_texte!='ok') {
											if($taille_texte<$val)
											{
												$hauteur_caractere_matiere = $hauteur_caractere_matiere-0.3;
												$pdf->SetFont('DejaVu','B',$hauteur_caractere_matiere);
												$val = $pdf->GetStringWidth($info_nom_matiere);
											}
											else {
												$grandeur_texte='ok';
											}
										}
										$grandeur_texte='test';
										$Y_decal=$y_courant;
										$pdf->SetXY($param_bull2016["x_EPI_AP_Parcours"], $Y_decal);
										$pdf->Cell($param_bull2016["largeur_EPI_AP_Parcours_col_1"], $hauteur_EPI_AP_Parcours/2, ($info_nom_matiere),'',1,'L');
									}


									// On note l'ordonnée pour le nom des professeurs
									$Y_decal = $y_courant+($hauteur_EPI_AP_Parcours/2);
									$pdf->SetXY($param_bull2016["x_EPI_AP_Parcours"], $Y_decal);
									$pdf->SetFont('DejaVu','',8);

									// nom des professeurs
									if ( isset($tab_bull['aid'][$m]['profs']['list'][0]) )
									{

										// Présentation en ligne des profs
										// On n'a pas forcément le formatage choisi pour la classe...
										//$text_prof=$tab_bull['groupe'][$m]["profs"]["proflist_string"]."  ";
										$text_prof="";
										for($loop_prof_grp=0;$loop_prof_grp<count($tab_bull['aid'][$m]['profs']['list']);$loop_prof_grp++) {
											$tmp_login_prof=$tab_bull['eleve'][$i]['aid'][$m]['profs']['list'][$loop_prof_grp];
											if($loop_prof_grp>0) {$text_prof.=", ";}
											$text_prof.=affiche_utilisateur($tmp_login_prof,$tab_bull['eleve'][$i]['id_classe']);
										}

										if($text_prof!="") {
											//$espace_matiere_prof = $espace_entre_matier/2;
											$espace_matiere_prof = $hauteur_EPI_AP_Parcours/2;
											$hauteur_caractere_prof = 7;

											$texte=$text_prof;
											$taille_max_police=$hauteur_caractere_prof;
											$taille_min_police=ceil($hauteur_caractere_prof/3);

											$largeur_dispo=$param_bull2016["largeur_EPI_AP_Parcours_col_1"];
											$h_cell=$espace_matiere_prof;

											$pdf->SetX($param_bull2016["x_EPI_AP_Parcours"]);

											cell_ajustee(($texte),$pdf->GetX(),$pdf->GetY(),$largeur_dispo,$h_cell,$taille_max_police,$taille_min_police,'');
										}
									}


									// Colonne 2 : Appréciation
									$pdf->SetFillColor($param_bull2016["couleur_EPI_alt".($cpt_matiere%2+1)]["R"], $param_bull2016["couleur_EPI_alt".($cpt_matiere%2+1)]["V"], $param_bull2016["couleur_EPI_alt".($cpt_matiere%2+1)]["B"]);
									$pdf->Rect($param_bull2016["x_EPI_AP_Parcours_col_2"], $y_courant, $param_bull2016["largeur_EPI_AP_Parcours_col_2"], $hauteur_EPI_AP_Parcours, 'F');

									$pdf->SetFillColor(0, 0, 0);
									$pdf->SetXY($param_bull2016["x_EPI_AP_Parcours_col_2"], $y_courant);

									// calcul de la taille du texte des appréciations
									$hauteur_caractere_appreciation = 8;
									$pdf->SetFont('DejaVu','',$hauteur_caractere_appreciation);

									//suppression des espaces en début et en fin
									//$app_aff = trim($tab_bull['app'][$m][$i]);
									$app_aff="";
									if($tab_bull['aid'][$m]['message']!='') {
										$app_aff.=$tab_bull['aid'][$m]['message'];
									}
									//if($app_aff!='') {$app_aff.=" ";}
									if(($tab_bull['aid'][$m]['display_nom']=='y')&&($tab_bull['aid'][$m]['aid_nom']!='')) {
										if($app_aff!='') {$app_aff.=" ";}
										$app_aff.=$tab_bull['aid'][$m]['aid_nom'];
									}
									if($app_aff!='') {$app_aff.="\n";}
									$app_aff.=trim($tab_bull['aid'][$m]['app_grp']);

									fich_debug_bull("__________________________________________\n");
									fich_debug_bull("$app_aff\n");
									fich_debug_bull("__________________________________________\n");

									// DEBUT AJUSTEMENT TAILLE APPRECIATION
									$taille_texte_total = $pdf->GetStringWidth($app_aff);
									//$largeur_appreciation2 = $largeur_appreciation - $largeur_sous_matiere;
									$largeur_appreciation2=$param_bull2016["largeur_EPI_AP_Parcours_col_2"];

									if($use_cell_ajustee=="n") {
										//$taille_texte = (($espace_entre_matier/3)*$largeur_appreciation2);
										$nb_ligne_app = '2.8';
										//$nb_ligne_app = '3.8';
										//$nb_ligne_app = '4.8';
										$taille_texte_max = $nb_ligne_app * ($largeur_appreciation2-4);
										//$taille_texte_max = $nb_ligne_app * ($largeur_appreciation2);
										$grandeur_texte='test';

										fich_debug_bull("\$taille_texte_total=$taille_texte_total\n");
										fich_debug_bull("\$largeur_appreciation2=$largeur_appreciation2\n");
										fich_debug_bull("\$nb_ligne_app=$nb_ligne_app\n");
										//fich_debug_bull("\$taille_texte_max = \$nb_ligne_app * (\$largeur_appreciation2-4)=$nb_ligne_app * ($largeur_appreciation2-4)=$taille_texte_max\n");
										fich_debug_bull("\$taille_texte_max = \$nb_ligne_app * (\$largeur_appreciation2)=$nb_ligne_app * ($largeur_appreciation2)=$taille_texte_max\n");

										while($grandeur_texte!='ok') {
											if($taille_texte_max < $taille_texte_total)
											{
												$hauteur_caractere_appreciation = $hauteur_caractere_appreciation-0.3;
												//$hauteur_caractere_appreciation = $hauteur_caractere_appreciation-0.1;
												$pdf->SetFont('DejaVu','',$hauteur_caractere_appreciation);
												$taille_texte_total = $pdf->GetStringWidth($app_aff);
											}
											else {
												$grandeur_texte='ok';
											}
										}
										$grandeur_texte='test';
										$pdf->drawTextBox(($app_aff), $largeur_appreciation2, $hauteur_EPI_AP_Parcours, 'J', 'M', 1);
									}
									else {
										$texte=$app_aff;
										//$texte="Bla bla\nbli ".$app_aff;
										$taille_max_police=$hauteur_caractere_appreciation;
										$taille_min_police=ceil($taille_max_police/3);

										$largeur_dispo=$largeur_appreciation2;
										$h_cell=$hauteur_EPI_AP_Parcours;

										if(getSettingValue('suppr_balises_app_prof')=='y') {$texte=preg_replace('/<(.*)>/U','',$texte);}
										cell_ajustee(($texte),$pdf->GetX(),$pdf->GetY(),$largeur_dispo,$h_cell,$taille_max_police,$taille_min_police,'');
									}

									$y_courant+=$hauteur_EPI_AP_Parcours+0.5;
									$cpt_matiere++;
								}
							}
						}
					}
				}
				// Fin des AID_b tagués AP


				// Enseignements de la table 'groupes' tagués AP
				for($m=0;$m<count($tab_bull['groupe']);$m++) {
					//if(isset($tab_bull['note'][$m][$i])) {
					// On n'affiche pas ici les groupes correspondant à AP, EPI ou Parcours
					if((isset($tab_bull['note'][$m][$i]))&&
					(isset($tab_bull['groupe'][$m]['type_grp'][0]))&&
					($tab_bull['groupe'][$m]['type_grp'][0]['nom_court']=="AP")) {

						// Colonne 1 : Matière, prof

						$pdf->SetFillColor($param_bull2016["couleur_EPI_alt".($cpt_matiere%2+1)]["R"], $param_bull2016["couleur_EPI_alt".($cpt_matiere%2+1)]["V"], $param_bull2016["couleur_EPI_alt".($cpt_matiere%2+1)]["B"]);
						$pdf->Rect($param_bull2016["x_EPI_AP_Parcours"], $y_courant, $param_bull2016["largeur_EPI_AP_Parcours_col_1"], $hauteur_EPI_AP_Parcours, 'F');
						$pdf->SetFillColor(0, 0, 0);

						$pdf->SetTextColor(0, 0, 0);
						$pdf->SetXY($param_bull2016["x_EPI_AP_Parcours"], $y_courant);
						$pdf->SetFont('DejaVu','',8);

						if(getSettingValue('bul_rel_nom_matieres')=='nom_groupe') {
							$info_nom_matiere=$tab_bull['groupe'][$m]['name'];
						}
						elseif(getSettingValue('bul_rel_nom_matieres')=='description_groupe') {
							$info_nom_matiere=$tab_bull['groupe'][$m]['description'];
						}
						else {
							// Pour parer au bug sur la suppression de matière alors que des groupes sont conservés:
							if(isset($tab_bull['groupe'][$m]['matiere']['nom_complet'])) {
								$info_nom_matiere=$tab_bull['groupe'][$m]['matiere']['nom_complet'];
							}
							else {
								$info_nom_matiere=$tab_bull['groupe'][$m]['name']." (".$tab_bull['groupe'][$m]['id'].")";
							}
						}

						$hauteur_caractere_matiere=8;
						$cell_ajustee_texte_matiere_ratio_min_max=3;

						// 20130927 : cell_ajustee() ou pas sur le nom de matière/enseignement
						$cell_ajustee_texte_matiere=1;
						if((isset($cell_ajustee_texte_matiere))&&($cell_ajustee_texte_matiere==1)) {
							// Encadrement
							//$pdf->Cell($tab_modele_pdf["largeur_matiere"][$classe_id], $espace_entre_matier, "",'LRBT',1,'L');

							// cell_ajustee() ne centre pas verticalement le texte.
							// On met un décalage pour ne pas coller le texte à la bordure
							$Y_decal_cell_ajustee=2;
							// On repositionne et on inscrit le nom de matière sur la moitié de la hauteur de la cellule
							//$pdf->SetXY($X_bloc_matiere, $Y_decal+$Y_decal_cell_ajustee);
							//$pdf->SetXY($param_bull2016["x_acquis_col_1"], $y_courant);

							$pdf->SetXY($param_bull2016["x_EPI_AP_Parcours"], $y_courant+$Y_decal_cell_ajustee);

							$texte=$info_nom_matiere;
							$taille_max_police=$hauteur_caractere_matiere;
							//$taille_min_police=ceil($taille_max_police/$tab_modele_pdf["cell_ajustee_texte_matiere_ratio_min_max"][$classe_id]);
							$taille_min_police=ceil($taille_max_police/$cell_ajustee_texte_matiere_ratio_min_max);

							$largeur_dispo=$param_bull2016["largeur_EPI_AP_Parcours_col_1"];
							//$h_cell=$espace_entre_matier/2-$Y_decal_cell_ajustee;
							$h_cell=$hauteur_EPI_AP_Parcours/2-$Y_decal_cell_ajustee;

							//cell_ajustee("<b>".$texte."</b>",$pdf->GetX(),$pdf->GetY(),$largeur_dispo,$h_cell,$taille_max_police,$taille_min_police,'');
							cell_ajustee($texte,$pdf->GetX(),$pdf->GetY(),$largeur_dispo,$h_cell,$taille_max_police,$taille_min_police,'');
		
						}
						else {
							$val = $pdf->GetStringWidth($info_nom_matiere);
							$taille_texte = $param_bull2016["largeur_EPI_AP_Parcours_col_1"]-2;
							$grandeur_texte='test';
							while($grandeur_texte!='ok') {
								if($taille_texte<$val)
								{
									$hauteur_caractere_matiere = $hauteur_caractere_matiere-0.3;
									$pdf->SetFont('DejaVu','',$hauteur_caractere_matiere);
									$val = $pdf->GetStringWidth($info_nom_matiere);
								}
								else {
									$grandeur_texte='ok';
								}
							}
							$grandeur_texte='test';
							$Y_decal=$y_courant;
							$pdf->SetXY($param_bull2016["x_EPI_AP_Parcours"], $Y_decal);
							$pdf->Cell($param_bull2016["largeur_EPI_AP_Parcours_col_1"], $hauteur_EPI_AP_Parcours/2, ($info_nom_matiere),'',1,'L');
						}
		

						// On note l'ordonnée pour le nom des professeurs
						$Y_decal = $y_courant+($hauteur_EPI_AP_Parcours/2);
						$pdf->SetXY($param_bull2016["x_EPI_AP_Parcours"], $Y_decal);
						$pdf->SetFont('DejaVu','',8);

						//fich_debug_bull("\$info_nom_matiere=$info_nom_matiere\n");
						//fich_debug_bull("Le nom de matière est écrit; on est à mi-hauteur de la cellule pour écrire le nom du prof:\n");
						//fich_debug_bull("\$Y_decal=$Y_decal\n");

						// nom des professeurs

						if ( isset($tab_bull['groupe'][$m]["profs"]["list"]) )
						{

							// Présentation en ligne des profs
							// On n'a pas forcément le formatage choisi pour la classe...
							//$text_prof=$tab_bull['groupe'][$m]["profs"]["proflist_string"]."  ";
							$text_prof="";
							for($loop_prof_grp=0;$loop_prof_grp<count($tab_bull['groupe'][$m]["profs"]["list"]);$loop_prof_grp++) {
								$tmp_login_prof=$tab_bull['groupe'][$m]["profs"]["list"][$loop_prof_grp];
								if($loop_prof_grp>0) {$text_prof.=", ";}
								$text_prof.=affiche_utilisateur($tmp_login_prof,$tab_bull['eleve'][$i]['id_classe']);
							}

							if($text_prof!="") {
								//$espace_matiere_prof = $espace_entre_matier/2;
								$espace_matiere_prof = $hauteur_EPI_AP_Parcours/2;
								$hauteur_caractere_prof = 7;

								/*
								if($use_cell_ajustee=="n") {
									$pdf->SetFont('DejaVu','',$hauteur_caractere_prof);
									$val = $pdf->GetStringWidth($text_prof);
									$taille_texte = ($tab_modele_pdf["largeur_matiere"][$classe_id]);
									$grandeur_texte='test';
									while($grandeur_texte!='ok') {
										if($taille_texte<$val)
										{
											$hauteur_caractere_prof = $hauteur_caractere_prof-0.3;
											$pdf->SetFont('DejaVu','',$hauteur_caractere_prof);
											$val = $pdf->GetStringWidth($text_prof);
										}
										else {
											$grandeur_texte='ok';
										}
									}
									$grandeur_texte='test';
									$pdf->SetX($X_bloc_matiere);
									$pdf->Cell($tab_modele_pdf["largeur_matiere"][$classe_id], $espace_matiere_prof, ($text_prof),'LR',1,'L');
								}
								else {
								*/
									$texte=$text_prof;
									$taille_max_police=$hauteur_caractere_prof;
									$taille_min_police=ceil($hauteur_caractere_prof/3);

									$largeur_dispo=$param_bull2016["largeur_EPI_AP_Parcours_col_1"];
									$h_cell=$espace_matiere_prof;

									$pdf->SetX($param_bull2016["x_EPI_AP_Parcours"]);

									cell_ajustee(($texte),$pdf->GetX(),$pdf->GetY(),$largeur_dispo,$h_cell,$taille_max_police,$taille_min_police,'');
								//}
							}
						}


						// Colonne 2 : Appréciation
						$pdf->SetFillColor($param_bull2016["couleur_EPI_alt".($cpt_matiere%2+1)]["R"], $param_bull2016["couleur_EPI_alt".($cpt_matiere%2+1)]["V"], $param_bull2016["couleur_EPI_alt".($cpt_matiere%2+1)]["B"]);
						$pdf->Rect($param_bull2016["x_EPI_AP_Parcours_col_2"], $y_courant, $param_bull2016["largeur_EPI_AP_Parcours_col_2"], $hauteur_EPI_AP_Parcours, 'F');

						$pdf->SetFillColor(0, 0, 0);
						$pdf->SetXY($param_bull2016["x_EPI_AP_Parcours_col_2"], $y_courant);

						// calcul de la taille du texte des appréciations
						$hauteur_caractere_appreciation = 8;
						$pdf->SetFont('DejaVu','',$hauteur_caractere_appreciation);

						//suppression des espaces en début et en fin
						$app_aff = trim($tab_bull['app'][$m][$i]);

						fich_debug_bull("__________________________________________\n");
						fich_debug_bull("$app_aff\n");
						fich_debug_bull("__________________________________________\n");

						// DEBUT AJUSTEMENT TAILLE APPRECIATION
						$taille_texte_total = $pdf->GetStringWidth($app_aff);
						//$largeur_appreciation2 = $largeur_appreciation - $largeur_sous_matiere;
						$largeur_appreciation2=$param_bull2016["largeur_EPI_AP_Parcours_col_2"];

						if($use_cell_ajustee=="n") {
							//$taille_texte = (($espace_entre_matier/3)*$largeur_appreciation2);
							$nb_ligne_app = '2.8';
							//$nb_ligne_app = '3.8';
							//$nb_ligne_app = '4.8';
							$taille_texte_max = $nb_ligne_app * ($largeur_appreciation2-4);
							//$taille_texte_max = $nb_ligne_app * ($largeur_appreciation2);
							$grandeur_texte='test';

							fich_debug_bull("\$taille_texte_total=$taille_texte_total\n");
							fich_debug_bull("\$largeur_appreciation2=$largeur_appreciation2\n");
							fich_debug_bull("\$nb_ligne_app=$nb_ligne_app\n");
							//fich_debug_bull("\$taille_texte_max = \$nb_ligne_app * (\$largeur_appreciation2-4)=$nb_ligne_app * ($largeur_appreciation2-4)=$taille_texte_max\n");
							fich_debug_bull("\$taille_texte_max = \$nb_ligne_app * (\$largeur_appreciation2)=$nb_ligne_app * ($largeur_appreciation2)=$taille_texte_max\n");

							while($grandeur_texte!='ok') {
								if($taille_texte_max < $taille_texte_total)
								{
									$hauteur_caractere_appreciation = $hauteur_caractere_appreciation-0.3;
									//$hauteur_caractere_appreciation = $hauteur_caractere_appreciation-0.1;
									$pdf->SetFont('DejaVu','',$hauteur_caractere_appreciation);
									$taille_texte_total = $pdf->GetStringWidth($app_aff);
								}
								else {
									$grandeur_texte='ok';
								}
							}
							$grandeur_texte='test';
							$pdf->drawTextBox(($app_aff), $largeur_appreciation2, $hauteur_EPI_AP_Parcours, 'J', 'M', 1);
						}
						else {
							$texte=$app_aff;
							//$texte="Bla bla\nbli ".$app_aff;
							$taille_max_police=$hauteur_caractere_appreciation;
							$taille_min_police=ceil($taille_max_police/3);

							$largeur_dispo=$largeur_appreciation2;
							$h_cell=$hauteur_EPI_AP_Parcours;

							if(getSettingValue('suppr_balises_app_prof')=='y') {$texte=preg_replace('/<(.*)>/U','',$texte);}
							cell_ajustee(($texte),$pdf->GetX(),$pdf->GetY(),$largeur_dispo,$h_cell,$taille_max_police,$taille_min_police,'');
						}

						$y_courant+=$hauteur_EPI_AP_Parcours+0.5;
						$cpt_matiere++;

					}
				}
				// Fin des enseignements de la table 'groupes' tagués AP


				// AID_e de type AP
				if(isset($tab_bull['aid'])) {
					for($m=0;$m<count($tab_bull['aid']);$m++) {
						if($tab_bull['aid'][$m]['order_display1']=="e") {
							if($tab_bull['aid'][$m]['type_aid']==1) {
								for($loop=0;$loop<count($indice_AID_e_AP);$loop++) {
									$m=$indice_AID_e_AP[$loop];

									// Colonne 1 : Matière, prof

									$pdf->SetFillColor($param_bull2016["couleur_EPI_alt".($cpt_matiere%2+1)]["R"], $param_bull2016["couleur_EPI_alt".($cpt_matiere%2+1)]["V"], $param_bull2016["couleur_EPI_alt".($cpt_matiere%2+1)]["B"]);
									$pdf->Rect($param_bull2016["x_EPI_AP_Parcours"], $y_courant, $param_bull2016["largeur_EPI_AP_Parcours_col_1"], $hauteur_EPI_AP_Parcours, 'F');
									$pdf->SetFillColor(0, 0, 0);

									$pdf->SetTextColor(0, 0, 0);
									$pdf->SetXY($param_bull2016["x_EPI_AP_Parcours"], $y_courant);
									$pdf->SetFont('DejaVu','',8);

									if($tab_bull['aid'][$m]['display_nom']=='z') {
										if((isset($tab_bull['aid'][$m]['aid_nom']))&&($tab_bull['aid'][$m]['aid_nom']!="")) {
											$info_nom_matiere=$tab_bull['aid'][$m]['aid_nom'];
										}
										elseif((isset($tab_bull['aid'][$m]['nom_complet']))&&($tab_bull['aid'][$m]['nom_complet']!="")) {
											$info_nom_matiere=$tab_bull['aid'][$m]['nom_complet'];
										}
										else {
											$info_nom_matiere=$tab_bull['aid'][$m]['nom'];
										}
									}
									elseif($tab_bull['aid'][$m]['display_nom']=='x') {
										if((isset($tab_bull['aid'][$m]['nom']))&&($tab_bull['aid'][$m]['nom']!="")) {
											$info_nom_matiere=$tab_bull['aid'][$m]['nom'];
										}
										else {
											$info_nom_matiere=$tab_bull['aid'][$m]['nom_complet'];
										}

										if((isset($tab_bull['aid'][$m]['aid_nom']))&&($tab_bull['aid'][$m]['aid_nom']!="")) {
											if($info_nom_matiere!="") {
												$info_nom_matiere.=": ";
											}
											$info_nom_matiere.=$tab_bull['aid'][$m]['aid_nom'];
										}
									}
									else {
										if((isset($tab_bull['aid'][$m]['nom_complet']))&&($tab_bull['aid'][$m]['nom_complet']!="")) {
											$info_nom_matiere=$tab_bull['aid'][$m]['nom_complet'];
										}
										else {
											$info_nom_matiere=$tab_bull['aid'][$m]['nom'];
										}
									}
									if($info_nom_matiere=="") {
										$info_nom_matiere="AID";
									}

									$hauteur_caractere_matiere=8;
									$cell_ajustee_texte_matiere_ratio_min_max=3;

									// Forcé pour le moment
									$cell_ajustee_texte_matiere=1;
									if((isset($cell_ajustee_texte_matiere))&&($cell_ajustee_texte_matiere==1)) {
										// On met un décalage pour ne pas coller le texte à la bordure
										$Y_decal_cell_ajustee=2;
										$pdf->SetXY($param_bull2016["x_EPI_AP_Parcours"], $y_courant+$Y_decal_cell_ajustee);

										$texte=$info_nom_matiere;
										$taille_max_police=$hauteur_caractere_matiere;
										$taille_min_police=ceil($taille_max_police/$cell_ajustee_texte_matiere_ratio_min_max);

										$largeur_dispo=$param_bull2016["largeur_EPI_AP_Parcours_col_1"];
										$h_cell=$hauteur_EPI_AP_Parcours/2-$Y_decal_cell_ajustee;

										cell_ajustee($texte,$pdf->GetX(),$pdf->GetY(),$largeur_dispo,$h_cell,$taille_max_police,$taille_min_police,'');
		
									}
									else {
										$val = $pdf->GetStringWidth($info_nom_matiere);
										$taille_texte = $param_bull2016["largeur_EPI_AP_Parcours_col_1"]-2;
										$grandeur_texte='test';
										while($grandeur_texte!='ok') {
											if($taille_texte<$val)
											{
												$hauteur_caractere_matiere = $hauteur_caractere_matiere-0.3;
												$pdf->SetFont('DejaVu','B',$hauteur_caractere_matiere);
												$val = $pdf->GetStringWidth($info_nom_matiere);
											}
											else {
												$grandeur_texte='ok';
											}
										}
										$grandeur_texte='test';
										$Y_decal=$y_courant;
										$pdf->SetXY($param_bull2016["x_EPI_AP_Parcours"], $Y_decal);
										$pdf->Cell($param_bull2016["largeur_EPI_AP_Parcours_col_1"], $hauteur_EPI_AP_Parcours/2, ($info_nom_matiere),'',1,'L');
									}


									// On note l'ordonnée pour le nom des professeurs
									$Y_decal = $y_courant+($hauteur_EPI_AP_Parcours/2);
									$pdf->SetXY($param_bull2016["x_EPI_AP_Parcours"], $Y_decal);
									$pdf->SetFont('DejaVu','',8);

									// nom des professeurs
									if ( isset($tab_bull['aid'][$m]['profs']['list'][0]) )
									{

										// Présentation en ligne des profs
										// On n'a pas forcément le formatage choisi pour la classe...
										//$text_prof=$tab_bull['groupe'][$m]["profs"]["proflist_string"]."  ";
										$text_prof="";
										for($loop_prof_grp=0;$loop_prof_grp<count($tab_bull['aid'][$m]['profs']['list']);$loop_prof_grp++) {
											$tmp_login_prof=$tab_bull['aid'][$m]['profs']['list'][$loop_prof_grp];
											if($loop_prof_grp>0) {$text_prof.=", ";}
											$text_prof.=affiche_utilisateur($tmp_login_prof,$tab_bull['eleve'][$i]['id_classe']);
										}

										if($text_prof!="") {
											//$espace_matiere_prof = $espace_entre_matier/2;
											$espace_matiere_prof = $hauteur_EPI_AP_Parcours/2;
											$hauteur_caractere_prof = 7;

											$texte=$text_prof;
											$taille_max_police=$hauteur_caractere_prof;
											$taille_min_police=ceil($hauteur_caractere_prof/3);

											$largeur_dispo=$param_bull2016["largeur_EPI_AP_Parcours_col_1"];
											$h_cell=$espace_matiere_prof;

											$pdf->SetX($param_bull2016["x_EPI_AP_Parcours"]);

											cell_ajustee(($texte),$pdf->GetX(),$pdf->GetY(),$largeur_dispo,$h_cell,$taille_max_police,$taille_min_police,'');
										}
									}


									// Colonne 2 : Appréciation
									$pdf->SetFillColor($param_bull2016["couleur_EPI_alt".($cpt_matiere%2+1)]["R"], $param_bull2016["couleur_EPI_alt".($cpt_matiere%2+1)]["V"], $param_bull2016["couleur_EPI_alt".($cpt_matiere%2+1)]["B"]);
									$pdf->Rect($param_bull2016["x_EPI_AP_Parcours_col_2"], $y_courant, $param_bull2016["largeur_EPI_AP_Parcours_col_2"], $hauteur_EPI_AP_Parcours, 'F');

									$pdf->SetFillColor(0, 0, 0);
									$pdf->SetXY($param_bull2016["x_EPI_AP_Parcours_col_2"], $y_courant);

									// calcul de la taille du texte des appréciations
									$hauteur_caractere_appreciation = 8;
									$pdf->SetFont('DejaVu','',$hauteur_caractere_appreciation);

									//suppression des espaces en début et en fin
									//$app_aff = trim($tab_bull['app'][$m][$i]);
									$app_aff="";
									if($tab_bull['aid'][$m]['message']!='') {
										$app_aff.=$tab_bull['aid'][$m]['message'];
									}
									//if($app_aff!='') {$app_aff.=" ";}
									if(($tab_bull['aid'][$m]['display_nom']=='y')&&($tab_bull['aid'][$m]['aid_nom']!='')) {
										if($app_aff!='') {$app_aff.=" ";}
										$app_aff.=$tab_bull['aid'][$m]['aid_nom'];
									}
									if($app_aff!='') {$app_aff.="\n";}
									$app_aff.=trim($tab_bull['aid'][$m]['app_grp']);

									fich_debug_bull("__________________________________________\n");
									fich_debug_bull("$app_aff\n");
									fich_debug_bull("__________________________________________\n");

									// DEBUT AJUSTEMENT TAILLE APPRECIATION
									$taille_texte_total = $pdf->GetStringWidth($app_aff);
									//$largeur_appreciation2 = $largeur_appreciation - $largeur_sous_matiere;
									$largeur_appreciation2=$param_bull2016["largeur_EPI_AP_Parcours_col_2"];

									if($use_cell_ajustee=="n") {
										//$taille_texte = (($espace_entre_matier/3)*$largeur_appreciation2);
										$nb_ligne_app = '2.8';
										//$nb_ligne_app = '3.8';
										//$nb_ligne_app = '4.8';
										$taille_texte_max = $nb_ligne_app * ($largeur_appreciation2-4);
										//$taille_texte_max = $nb_ligne_app * ($largeur_appreciation2);
										$grandeur_texte='test';

										fich_debug_bull("\$taille_texte_total=$taille_texte_total\n");
										fich_debug_bull("\$largeur_appreciation2=$largeur_appreciation2\n");
										fich_debug_bull("\$nb_ligne_app=$nb_ligne_app\n");
										//fich_debug_bull("\$taille_texte_max = \$nb_ligne_app * (\$largeur_appreciation2-4)=$nb_ligne_app * ($largeur_appreciation2-4)=$taille_texte_max\n");
										fich_debug_bull("\$taille_texte_max = \$nb_ligne_app * (\$largeur_appreciation2)=$nb_ligne_app * ($largeur_appreciation2)=$taille_texte_max\n");

										while($grandeur_texte!='ok') {
											if($taille_texte_max < $taille_texte_total)
											{
												$hauteur_caractere_appreciation = $hauteur_caractere_appreciation-0.3;
												//$hauteur_caractere_appreciation = $hauteur_caractere_appreciation-0.1;
												$pdf->SetFont('DejaVu','',$hauteur_caractere_appreciation);
												$taille_texte_total = $pdf->GetStringWidth($app_aff);
											}
											else {
												$grandeur_texte='ok';
											}
										}
										$grandeur_texte='test';
										$pdf->drawTextBox(($app_aff), $largeur_appreciation2, $hauteur_EPI_AP_Parcours, 'J', 'M', 1);
									}
									else {
										$texte=$app_aff;
										//$texte="Bla bla\nbli ".$app_aff;
										$taille_max_police=$hauteur_caractere_appreciation;
										$taille_min_police=ceil($taille_max_police/3);

										$largeur_dispo=$largeur_appreciation2;
										$h_cell=$hauteur_EPI_AP_Parcours;

										if(getSettingValue('suppr_balises_app_prof')=='y') {$texte=preg_replace('/<(.*)>/U','',$texte);}
										cell_ajustee(($texte),$pdf->GetX(),$pdf->GetY(),$largeur_dispo,$h_cell,$taille_max_police,$taille_min_police,'');
									}

									$y_courant+=$hauteur_EPI_AP_Parcours+0.5;
									$cpt_matiere++;

								}
							}
						}
					}
				}
				// Fin des AID_e tagués AP
			}
			//++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
			else {
				// On est sur une page appréciation groupe liée à un élève $i!=""

				// AID_b de type AP
				for($loop=0;$loop<count($indice_AID_b_AP);$loop++) {
					$m=$indice_AID_b_AP[$loop];

					// Colonne 1 : Matière, prof

					$pdf->SetFillColor($param_bull2016["couleur_EPI_alt".($cpt_matiere%2+1)]["R"], $param_bull2016["couleur_EPI_alt".($cpt_matiere%2+1)]["V"], $param_bull2016["couleur_EPI_alt".($cpt_matiere%2+1)]["B"]);
					$pdf->Rect($param_bull2016["x_EPI_AP_Parcours"], $y_courant, $param_bull2016["largeur_EPI_AP_Parcours_col_1"], $hauteur_EPI_AP_Parcours, 'F');
					$pdf->SetFillColor(0, 0, 0);

					$pdf->SetTextColor(0, 0, 0);
					$pdf->SetXY($param_bull2016["x_EPI_AP_Parcours"], $y_courant);
					$pdf->SetFont('DejaVu','',8);

					if($tab_bull['eleve'][$i]['aid_b'][$m]['display_nom']=='z') {
						if((isset($tab_bull['eleve'][$i]['aid_b'][$m]['aid_nom']))&&($tab_bull['eleve'][$i]['aid_b'][$m]['aid_nom']!="")) {
							$info_nom_matiere=$tab_bull['eleve'][$i]['aid_b'][$m]['aid_nom'];
						}
						elseif((isset($tab_bull['eleve'][$i]['aid_b'][$m]['nom_complet']))&&($tab_bull['eleve'][$i]['aid_b'][$m]['nom_complet']!="")) {
							$info_nom_matiere=$tab_bull['eleve'][$i]['aid_b'][$m]['nom_complet'];
						}
						else {
							$info_nom_matiere=$tab_bull['eleve'][$i]['aid_b'][$m]['nom'];
						}
					}
					elseif($tab_bull['eleve'][$i]['aid_b'][$m]['display_nom']=='x') {
						if((isset($tab_bull['eleve'][$i]['aid_b'][$m]['nom']))&&($tab_bull['eleve'][$i]['aid_b'][$m]['nom']!="")) {
							$info_nom_matiere=$tab_bull['eleve'][$i]['aid_b'][$m]['nom'];
						}
						else {
							$info_nom_matiere=$tab_bull['eleve'][$i]['aid_b'][$m]['nom_complet'];
						}

						if((isset($tab_bull['eleve'][$i]['aid_b'][$m]['aid_nom']))&&($tab_bull['eleve'][$i]['aid_b'][$m]['aid_nom']!="")) {
							if($info_nom_matiere!="") {
								$info_nom_matiere.=": ";
							}
							$info_nom_matiere.=$tab_bull['eleve'][$i]['aid_b'][$m]['aid_nom'];
						}
					}
					else {
						if((isset($tab_bull['eleve'][$i]['aid_b'][$m]['nom_complet']))&&($tab_bull['eleve'][$i]['aid_b'][$m]['nom_complet']!="")) {
							$info_nom_matiere=$tab_bull['eleve'][$i]['aid_b'][$m]['nom_complet'];
						}
						else {
							$info_nom_matiere=$tab_bull['eleve'][$i]['aid_b'][$m]['nom'];
						}
					}
					if($info_nom_matiere=="") {
						$info_nom_matiere="AID";
					}

					$hauteur_caractere_matiere=8;
					$cell_ajustee_texte_matiere_ratio_min_max=3;

					// Forcé pour le moment
					$cell_ajustee_texte_matiere=1;
					if((isset($cell_ajustee_texte_matiere))&&($cell_ajustee_texte_matiere==1)) {
						// On met un décalage pour ne pas coller le texte à la bordure
						$Y_decal_cell_ajustee=2;
						$pdf->SetXY($param_bull2016["x_EPI_AP_Parcours"], $y_courant+$Y_decal_cell_ajustee);

						$texte=$info_nom_matiere;
						$taille_max_police=$hauteur_caractere_matiere;
						$taille_min_police=ceil($taille_max_police/$cell_ajustee_texte_matiere_ratio_min_max);

						$largeur_dispo=$param_bull2016["largeur_EPI_AP_Parcours_col_1"];
						$h_cell=$hauteur_EPI_AP_Parcours/2-$Y_decal_cell_ajustee;

						cell_ajustee($texte,$pdf->GetX(),$pdf->GetY(),$largeur_dispo,$h_cell,$taille_max_police,$taille_min_police,'');
		
					}
					else {
						$val = $pdf->GetStringWidth($info_nom_matiere);
						$taille_texte = $param_bull2016["largeur_EPI_AP_Parcours_col_1"]-2;
						$grandeur_texte='test';
						while($grandeur_texte!='ok') {
							if($taille_texte<$val)
							{
								$hauteur_caractere_matiere = $hauteur_caractere_matiere-0.3;
								$pdf->SetFont('DejaVu','B',$hauteur_caractere_matiere);
								$val = $pdf->GetStringWidth($info_nom_matiere);
							}
							else {
								$grandeur_texte='ok';
							}
						}
						$grandeur_texte='test';
						$Y_decal=$y_courant;
						$pdf->SetXY($param_bull2016["x_EPI_AP_Parcours"], $Y_decal);
						$pdf->Cell($param_bull2016["largeur_EPI_AP_Parcours_col_1"], $hauteur_EPI_AP_Parcours/2, ($info_nom_matiere),'',1,'L');
					}


					// On note l'ordonnée pour le nom des professeurs
					$Y_decal = $y_courant+($hauteur_EPI_AP_Parcours/2);
					$pdf->SetXY($param_bull2016["x_EPI_AP_Parcours"], $Y_decal);
					$pdf->SetFont('DejaVu','',8);

					// nom des professeurs
					if ( isset($tab_bull['eleve'][$i]['aid_b'][$m]['aid_prof_resp_login'][0]) )
					{

						// Présentation en ligne des profs
						// On n'a pas forcément le formatage choisi pour la classe...
						//$text_prof=$tab_bull['groupe'][$m]["profs"]["proflist_string"]."  ";
						$text_prof="";
						for($loop_prof_grp=0;$loop_prof_grp<count($tab_bull['eleve'][$i]['aid_b'][$m]['aid_prof_resp_login']);$loop_prof_grp++) {
							$tmp_login_prof=$tab_bull['eleve'][$i]['aid_b'][$m]['aid_prof_resp_login'][$loop_prof_grp];
							if($loop_prof_grp>0) {$text_prof.=", ";}
							$text_prof.=affiche_utilisateur($tmp_login_prof,$tab_bull['eleve'][$i]['id_classe']);
						}

						if($text_prof!="") {
							//$espace_matiere_prof = $espace_entre_matier/2;
							$espace_matiere_prof = $hauteur_EPI_AP_Parcours/2;
							$hauteur_caractere_prof = 7;

							$texte=$text_prof;
							$taille_max_police=$hauteur_caractere_prof;
							$taille_min_police=ceil($hauteur_caractere_prof/3);

							$largeur_dispo=$param_bull2016["largeur_EPI_AP_Parcours_col_1"];
							$h_cell=$espace_matiere_prof;

							$pdf->SetX($param_bull2016["x_EPI_AP_Parcours"]);

							cell_ajustee(($texte),$pdf->GetX(),$pdf->GetY(),$largeur_dispo,$h_cell,$taille_max_police,$taille_min_police,'');
						}
					}


					// Colonne 2 : Appréciation
					$pdf->SetFillColor($param_bull2016["couleur_EPI_alt".($cpt_matiere%2+1)]["R"], $param_bull2016["couleur_EPI_alt".($cpt_matiere%2+1)]["V"], $param_bull2016["couleur_EPI_alt".($cpt_matiere%2+1)]["B"]);
					$pdf->Rect($param_bull2016["x_EPI_AP_Parcours_col_2"], $y_courant, $param_bull2016["largeur_EPI_AP_Parcours_col_2"], $hauteur_EPI_AP_Parcours, 'F');

					$pdf->SetFillColor(0, 0, 0);
					$pdf->SetXY($param_bull2016["x_EPI_AP_Parcours_col_2"], $y_courant);

					// calcul de la taille du texte des appréciations
					$hauteur_caractere_appreciation = 8;
					$pdf->SetFont('DejaVu','',$hauteur_caractere_appreciation);

					//suppression des espaces en début et en fin
					//$app_aff = trim($tab_bull['app'][$m][$i]);
					$app_aff="";
					if($tab_bull['eleve'][$i]['aid_b'][$m]['message']!='') {
						$app_aff.=$tab_bull['eleve'][$i]['aid_b'][$m]['message'];
					}
					//if($app_aff!='') {$app_aff.=" ";}
					if(($tab_bull['eleve'][$i]['aid_b'][$m]['display_nom']=='y')&&($tab_bull['eleve'][$i]['aid_b'][$m]['aid_nom']!='')) {
						if($app_aff!='') {$app_aff.=" ";}
						$app_aff.=$tab_bull['eleve'][$i]['aid_b'][$m]['aid_nom'];
					}
					if($app_aff!='') {$app_aff.="\n";}
					$app_aff.=trim($tab_bull['eleve'][$i]['aid_b'][$m]['aid_appreciation']);

					fich_debug_bull("__________________________________________\n");
					fich_debug_bull("$app_aff\n");
					fich_debug_bull("__________________________________________\n");

					// DEBUT AJUSTEMENT TAILLE APPRECIATION
					$taille_texte_total = $pdf->GetStringWidth($app_aff);
					//$largeur_appreciation2 = $largeur_appreciation - $largeur_sous_matiere;
					$largeur_appreciation2=$param_bull2016["largeur_EPI_AP_Parcours_col_2"];

					if($use_cell_ajustee=="n") {
						//$taille_texte = (($espace_entre_matier/3)*$largeur_appreciation2);
						$nb_ligne_app = '2.8';
						//$nb_ligne_app = '3.8';
						//$nb_ligne_app = '4.8';
						$taille_texte_max = $nb_ligne_app * ($largeur_appreciation2-4);
						//$taille_texte_max = $nb_ligne_app * ($largeur_appreciation2);
						$grandeur_texte='test';

						fich_debug_bull("\$taille_texte_total=$taille_texte_total\n");
						fich_debug_bull("\$largeur_appreciation2=$largeur_appreciation2\n");
						fich_debug_bull("\$nb_ligne_app=$nb_ligne_app\n");
						//fich_debug_bull("\$taille_texte_max = \$nb_ligne_app * (\$largeur_appreciation2-4)=$nb_ligne_app * ($largeur_appreciation2-4)=$taille_texte_max\n");
						fich_debug_bull("\$taille_texte_max = \$nb_ligne_app * (\$largeur_appreciation2)=$nb_ligne_app * ($largeur_appreciation2)=$taille_texte_max\n");

						while($grandeur_texte!='ok') {
							if($taille_texte_max < $taille_texte_total)
							{
								$hauteur_caractere_appreciation = $hauteur_caractere_appreciation-0.3;
								//$hauteur_caractere_appreciation = $hauteur_caractere_appreciation-0.1;
								$pdf->SetFont('DejaVu','',$hauteur_caractere_appreciation);
								$taille_texte_total = $pdf->GetStringWidth($app_aff);
							}
							else {
								$grandeur_texte='ok';
							}
						}
						$grandeur_texte='test';
						$pdf->drawTextBox(($app_aff), $largeur_appreciation2, $hauteur_EPI_AP_Parcours, 'J', 'M', 1);
					}
					else {
						$texte=$app_aff;
						//$texte="Bla bla\nbli ".$app_aff;
						$taille_max_police=$hauteur_caractere_appreciation;
						$taille_min_police=ceil($taille_max_police/3);

						$largeur_dispo=$largeur_appreciation2;
						$h_cell=$hauteur_EPI_AP_Parcours;

						if(getSettingValue('suppr_balises_app_prof')=='y') {$texte=preg_replace('/<(.*)>/U','',$texte);}
						cell_ajustee(($texte),$pdf->GetX(),$pdf->GetY(),$largeur_dispo,$h_cell,$taille_max_police,$taille_min_police,'');
					}

					$y_courant+=$hauteur_EPI_AP_Parcours+0.5;
					$cpt_matiere++;


				}
				// Fin des AID_b tagués AP


				// Enseignements de la table 'groupes' tagués AP
				for($m=0;$m<count($tab_bull['groupe']);$m++) {
					//if(isset($tab_bull['note'][$m][$i])) {
					// On n'affiche pas ici les groupes correspondant à AP, EPI ou Parcours
					if((isset($tab_bull['note'][$m][$i]))&&
					(isset($tab_bull['groupe'][$m]['type_grp'][0]))&&
					($tab_bull['groupe'][$m]['type_grp'][0]['nom_court']=="AP")) {

						// Colonne 1 : Matière, prof

						$pdf->SetFillColor($param_bull2016["couleur_EPI_alt".($cpt_matiere%2+1)]["R"], $param_bull2016["couleur_EPI_alt".($cpt_matiere%2+1)]["V"], $param_bull2016["couleur_EPI_alt".($cpt_matiere%2+1)]["B"]);
						$pdf->Rect($param_bull2016["x_EPI_AP_Parcours"], $y_courant, $param_bull2016["largeur_EPI_AP_Parcours_col_1"], $hauteur_EPI_AP_Parcours, 'F');
						$pdf->SetFillColor(0, 0, 0);

						$pdf->SetTextColor(0, 0, 0);
						$pdf->SetXY($param_bull2016["x_EPI_AP_Parcours"], $y_courant);
						$pdf->SetFont('DejaVu','',8);

						if(getSettingValue('bul_rel_nom_matieres')=='nom_groupe') {
							$info_nom_matiere=$tab_bull['groupe'][$m]['name'];
						}
						elseif(getSettingValue('bul_rel_nom_matieres')=='description_groupe') {
							$info_nom_matiere=$tab_bull['groupe'][$m]['description'];
						}
						else {
							// Pour parer au bug sur la suppression de matière alors que des groupes sont conservés:
							if(isset($tab_bull['groupe'][$m]['matiere']['nom_complet'])) {
								$info_nom_matiere=$tab_bull['groupe'][$m]['matiere']['nom_complet'];
							}
							else {
								$info_nom_matiere=$tab_bull['groupe'][$m]['name']." (".$tab_bull['groupe'][$m]['id'].")";
							}
						}

						$hauteur_caractere_matiere=8;
						$cell_ajustee_texte_matiere_ratio_min_max=3;

						// 20130927 : cell_ajustee() ou pas sur le nom de matière/enseignement
						$cell_ajustee_texte_matiere=1;
						if((isset($cell_ajustee_texte_matiere))&&($cell_ajustee_texte_matiere==1)) {
							// Encadrement
							//$pdf->Cell($tab_modele_pdf["largeur_matiere"][$classe_id], $espace_entre_matier, "",'LRBT',1,'L');

							// cell_ajustee() ne centre pas verticalement le texte.
							// On met un décalage pour ne pas coller le texte à la bordure
							$Y_decal_cell_ajustee=2;
							// On repositionne et on inscrit le nom de matière sur la moitié de la hauteur de la cellule
							//$pdf->SetXY($X_bloc_matiere, $Y_decal+$Y_decal_cell_ajustee);
							//$pdf->SetXY($param_bull2016["x_acquis_col_1"], $y_courant);

							$pdf->SetXY($param_bull2016["x_EPI_AP_Parcours"], $y_courant+$Y_decal_cell_ajustee);

							$texte=$info_nom_matiere;
							$taille_max_police=$hauteur_caractere_matiere;
							//$taille_min_police=ceil($taille_max_police/$tab_modele_pdf["cell_ajustee_texte_matiere_ratio_min_max"][$classe_id]);
							$taille_min_police=ceil($taille_max_police/$cell_ajustee_texte_matiere_ratio_min_max);

							$largeur_dispo=$param_bull2016["largeur_EPI_AP_Parcours_col_1"];
							//$h_cell=$espace_entre_matier/2-$Y_decal_cell_ajustee;
							$h_cell=$hauteur_EPI_AP_Parcours/2-$Y_decal_cell_ajustee;

							//cell_ajustee("<b>".$texte."</b>",$pdf->GetX(),$pdf->GetY(),$largeur_dispo,$h_cell,$taille_max_police,$taille_min_police,'');
							cell_ajustee($texte,$pdf->GetX(),$pdf->GetY(),$largeur_dispo,$h_cell,$taille_max_police,$taille_min_police,'');
		
						}
						else {
							$val = $pdf->GetStringWidth($info_nom_matiere);
							$taille_texte = $param_bull2016["largeur_EPI_AP_Parcours_col_1"]-2;
							$grandeur_texte='test';
							while($grandeur_texte!='ok') {
								if($taille_texte<$val)
								{
									$hauteur_caractere_matiere = $hauteur_caractere_matiere-0.3;
									$pdf->SetFont('DejaVu','',$hauteur_caractere_matiere);
									$val = $pdf->GetStringWidth($info_nom_matiere);
								}
								else {
									$grandeur_texte='ok';
								}
							}
							$grandeur_texte='test';
							$Y_decal=$y_courant;
							$pdf->SetXY($param_bull2016["x_EPI_AP_Parcours"], $Y_decal);
							$pdf->Cell($param_bull2016["largeur_EPI_AP_Parcours_col_1"], $hauteur_EPI_AP_Parcours/2, ($info_nom_matiere),'',1,'L');
						}
		

						// On note l'ordonnée pour le nom des professeurs
						$Y_decal = $y_courant+($hauteur_EPI_AP_Parcours/2);
						$pdf->SetXY($param_bull2016["x_EPI_AP_Parcours"], $Y_decal);
						$pdf->SetFont('DejaVu','',8);

						//fich_debug_bull("\$info_nom_matiere=$info_nom_matiere\n");
						//fich_debug_bull("Le nom de matière est écrit; on est à mi-hauteur de la cellule pour écrire le nom du prof:\n");
						//fich_debug_bull("\$Y_decal=$Y_decal\n");

						// nom des professeurs

						if ( isset($tab_bull['groupe'][$m]["profs"]["list"]) )
						{

							// Présentation en ligne des profs
							// On n'a pas forcément le formatage choisi pour la classe...
							//$text_prof=$tab_bull['groupe'][$m]["profs"]["proflist_string"]."  ";
							$text_prof="";
							for($loop_prof_grp=0;$loop_prof_grp<count($tab_bull['groupe'][$m]["profs"]["list"]);$loop_prof_grp++) {
								$tmp_login_prof=$tab_bull['groupe'][$m]["profs"]["list"][$loop_prof_grp];
								if($loop_prof_grp>0) {$text_prof.=", ";}
								$text_prof.=affiche_utilisateur($tmp_login_prof,$tab_bull['eleve'][$i]['id_classe']);
							}

							if($text_prof!="") {
								//$espace_matiere_prof = $espace_entre_matier/2;
								$espace_matiere_prof = $hauteur_EPI_AP_Parcours/2;
								$hauteur_caractere_prof = 7;

								/*
								if($use_cell_ajustee=="n") {
									$pdf->SetFont('DejaVu','',$hauteur_caractere_prof);
									$val = $pdf->GetStringWidth($text_prof);
									$taille_texte = ($tab_modele_pdf["largeur_matiere"][$classe_id]);
									$grandeur_texte='test';
									while($grandeur_texte!='ok') {
										if($taille_texte<$val)
										{
											$hauteur_caractere_prof = $hauteur_caractere_prof-0.3;
											$pdf->SetFont('DejaVu','',$hauteur_caractere_prof);
											$val = $pdf->GetStringWidth($text_prof);
										}
										else {
											$grandeur_texte='ok';
										}
									}
									$grandeur_texte='test';
									$pdf->SetX($X_bloc_matiere);
									$pdf->Cell($tab_modele_pdf["largeur_matiere"][$classe_id], $espace_matiere_prof, ($text_prof),'LR',1,'L');
								}
								else {
								*/
									$texte=$text_prof;
									$taille_max_police=$hauteur_caractere_prof;
									$taille_min_police=ceil($hauteur_caractere_prof/3);

									$largeur_dispo=$param_bull2016["largeur_EPI_AP_Parcours_col_1"];
									$h_cell=$espace_matiere_prof;

									$pdf->SetX($param_bull2016["x_EPI_AP_Parcours"]);

									cell_ajustee(($texte),$pdf->GetX(),$pdf->GetY(),$largeur_dispo,$h_cell,$taille_max_police,$taille_min_police,'');
								//}
							}
						}


						// Colonne 2 : Appréciation
						$pdf->SetFillColor($param_bull2016["couleur_EPI_alt".($cpt_matiere%2+1)]["R"], $param_bull2016["couleur_EPI_alt".($cpt_matiere%2+1)]["V"], $param_bull2016["couleur_EPI_alt".($cpt_matiere%2+1)]["B"]);
						$pdf->Rect($param_bull2016["x_EPI_AP_Parcours_col_2"], $y_courant, $param_bull2016["largeur_EPI_AP_Parcours_col_2"], $hauteur_EPI_AP_Parcours, 'F');

						$pdf->SetFillColor(0, 0, 0);
						$pdf->SetXY($param_bull2016["x_EPI_AP_Parcours_col_2"], $y_courant);

						// calcul de la taille du texte des appréciations
						$hauteur_caractere_appreciation = 8;
						$pdf->SetFont('DejaVu','',$hauteur_caractere_appreciation);

						//suppression des espaces en début et en fin
						$app_aff = trim($tab_bull['app'][$m][$i]);

						fich_debug_bull("__________________________________________\n");
						fich_debug_bull("$app_aff\n");
						fich_debug_bull("__________________________________________\n");

						// DEBUT AJUSTEMENT TAILLE APPRECIATION
						$taille_texte_total = $pdf->GetStringWidth($app_aff);
						//$largeur_appreciation2 = $largeur_appreciation - $largeur_sous_matiere;
						$largeur_appreciation2=$param_bull2016["largeur_EPI_AP_Parcours_col_2"];

						if($use_cell_ajustee=="n") {
							//$taille_texte = (($espace_entre_matier/3)*$largeur_appreciation2);
							$nb_ligne_app = '2.8';
							//$nb_ligne_app = '3.8';
							//$nb_ligne_app = '4.8';
							$taille_texte_max = $nb_ligne_app * ($largeur_appreciation2-4);
							//$taille_texte_max = $nb_ligne_app * ($largeur_appreciation2);
							$grandeur_texte='test';

							fich_debug_bull("\$taille_texte_total=$taille_texte_total\n");
							fich_debug_bull("\$largeur_appreciation2=$largeur_appreciation2\n");
							fich_debug_bull("\$nb_ligne_app=$nb_ligne_app\n");
							//fich_debug_bull("\$taille_texte_max = \$nb_ligne_app * (\$largeur_appreciation2-4)=$nb_ligne_app * ($largeur_appreciation2-4)=$taille_texte_max\n");
							fich_debug_bull("\$taille_texte_max = \$nb_ligne_app * (\$largeur_appreciation2)=$nb_ligne_app * ($largeur_appreciation2)=$taille_texte_max\n");

							while($grandeur_texte!='ok') {
								if($taille_texte_max < $taille_texte_total)
								{
									$hauteur_caractere_appreciation = $hauteur_caractere_appreciation-0.3;
									//$hauteur_caractere_appreciation = $hauteur_caractere_appreciation-0.1;
									$pdf->SetFont('DejaVu','',$hauteur_caractere_appreciation);
									$taille_texte_total = $pdf->GetStringWidth($app_aff);
								}
								else {
									$grandeur_texte='ok';
								}
							}
							$grandeur_texte='test';
							$pdf->drawTextBox(($app_aff), $largeur_appreciation2, $hauteur_EPI_AP_Parcours, 'J', 'M', 1);
						}
						else {
							$texte=$app_aff;
							//$texte="Bla bla\nbli ".$app_aff;
							$taille_max_police=$hauteur_caractere_appreciation;
							$taille_min_police=ceil($taille_max_police/3);

							$largeur_dispo=$largeur_appreciation2;
							$h_cell=$hauteur_EPI_AP_Parcours;

							if(getSettingValue('suppr_balises_app_prof')=='y') {$texte=preg_replace('/<(.*)>/U','',$texte);}
							cell_ajustee(($texte),$pdf->GetX(),$pdf->GetY(),$largeur_dispo,$h_cell,$taille_max_police,$taille_min_police,'');
						}

						$y_courant+=$hauteur_EPI_AP_Parcours+0.5;
						$cpt_matiere++;

					}
				}
				// Fin des enseignements de la table 'groupes' tagués AP


				// AID_e de type AP
				for($loop=0;$loop<count($indice_AID_e_AP);$loop++) {
					$m=$indice_AID_e_AP[$loop];

					// Colonne 1 : Matière, prof

					$pdf->SetFillColor($param_bull2016["couleur_EPI_alt".($cpt_matiere%2+1)]["R"], $param_bull2016["couleur_EPI_alt".($cpt_matiere%2+1)]["V"], $param_bull2016["couleur_EPI_alt".($cpt_matiere%2+1)]["B"]);
					$pdf->Rect($param_bull2016["x_EPI_AP_Parcours"], $y_courant, $param_bull2016["largeur_EPI_AP_Parcours_col_1"], $hauteur_EPI_AP_Parcours, 'F');
					$pdf->SetFillColor(0, 0, 0);

					$pdf->SetTextColor(0, 0, 0);
					$pdf->SetXY($param_bull2016["x_EPI_AP_Parcours"], $y_courant);
					$pdf->SetFont('DejaVu','',8);

					if($tab_bull['eleve'][$i]['aid_e'][$m]['display_nom']=='z') {
						if((isset($tab_bull['eleve'][$i]['aid_e'][$m]['aid_nom']))&&($tab_bull['eleve'][$i]['aid_e'][$m]['aid_nom']!="")) {
							$info_nom_matiere=$tab_bull['eleve'][$i]['aid_e'][$m]['aid_nom'];
						}
						elseif((isset($tab_bull['eleve'][$i]['aid_e'][$m]['nom_complet']))&&($tab_bull['eleve'][$i]['aid_e'][$m]['nom_complet']!="")) {
							$info_nom_matiere=$tab_bull['eleve'][$i]['aid_e'][$m]['nom_complet'];
						}
						else {
							$info_nom_matiere=$tab_bull['eleve'][$i]['aid_e'][$m]['nom'];
						}
					}
					elseif($tab_bull['eleve'][$i]['aid_e'][$m]['display_nom']=='x') {
						if((isset($tab_bull['eleve'][$i]['aid_e'][$m]['nom']))&&($tab_bull['eleve'][$i]['aid_e'][$m]['nom']!="")) {
							$info_nom_matiere=$tab_bull['eleve'][$i]['aid_e'][$m]['nom'];
						}
						else {
							$info_nom_matiere=$tab_bull['eleve'][$i]['aid_e'][$m]['nom_complet'];
						}

						if((isset($tab_bull['eleve'][$i]['aid_e'][$m]['aid_nom']))&&($tab_bull['eleve'][$i]['aid_e'][$m]['aid_nom']!="")) {
							if($info_nom_matiere!="") {
								$info_nom_matiere.=": ";
							}
							$info_nom_matiere.=$tab_bull['eleve'][$i]['aid_e'][$m]['aid_nom'];
						}
					}
					else {
						if((isset($tab_bull['eleve'][$i]['aid_e'][$m]['nom_complet']))&&($tab_bull['eleve'][$i]['aid_e'][$m]['nom_complet']!="")) {
							$info_nom_matiere=$tab_bull['eleve'][$i]['aid_e'][$m]['nom_complet'];
						}
						else {
							$info_nom_matiere=$tab_bull['eleve'][$i]['aid_e'][$m]['nom'];
						}
					}
					if($info_nom_matiere=="") {
						$info_nom_matiere="AID";
					}

					$hauteur_caractere_matiere=8;
					$cell_ajustee_texte_matiere_ratio_min_max=3;

					// Forcé pour le moment
					$cell_ajustee_texte_matiere=1;
					if((isset($cell_ajustee_texte_matiere))&&($cell_ajustee_texte_matiere==1)) {
						// On met un décalage pour ne pas coller le texte à la bordure
						$Y_decal_cell_ajustee=2;
						$pdf->SetXY($param_bull2016["x_EPI_AP_Parcours"], $y_courant+$Y_decal_cell_ajustee);

						$texte=$info_nom_matiere;
						$taille_max_police=$hauteur_caractere_matiere;
						$taille_min_police=ceil($taille_max_police/$cell_ajustee_texte_matiere_ratio_min_max);

						$largeur_dispo=$param_bull2016["largeur_EPI_AP_Parcours_col_1"];
						$h_cell=$hauteur_EPI_AP_Parcours/2-$Y_decal_cell_ajustee;

						cell_ajustee($texte,$pdf->GetX(),$pdf->GetY(),$largeur_dispo,$h_cell,$taille_max_police,$taille_min_police,'');
		
					}
					else {
						$val = $pdf->GetStringWidth($info_nom_matiere);
						$taille_texte = $param_bull2016["largeur_EPI_AP_Parcours_col_1"]-2;
						$grandeur_texte='test';
						while($grandeur_texte!='ok') {
							if($taille_texte<$val)
							{
								$hauteur_caractere_matiere = $hauteur_caractere_matiere-0.3;
								$pdf->SetFont('DejaVu','B',$hauteur_caractere_matiere);
								$val = $pdf->GetStringWidth($info_nom_matiere);
							}
							else {
								$grandeur_texte='ok';
							}
						}
						$grandeur_texte='test';
						$Y_decal=$y_courant;
						$pdf->SetXY($param_bull2016["x_EPI_AP_Parcours"], $Y_decal);
						$pdf->Cell($param_bull2016["largeur_EPI_AP_Parcours_col_1"], $hauteur_EPI_AP_Parcours/2, ($info_nom_matiere),'',1,'L');
					}


					// On note l'ordonnée pour le nom des professeurs
					$Y_decal = $y_courant+($hauteur_EPI_AP_Parcours/2);
					$pdf->SetXY($param_bull2016["x_EPI_AP_Parcours"], $Y_decal);
					$pdf->SetFont('DejaVu','',8);

					// nom des professeurs
					if ( isset($tab_bull['eleve'][$i]['aid_e'][$m]['aid_prof_resp_login'][0]) )
					{

						// Présentation en ligne des profs
						// On n'a pas forcément le formatage choisi pour la classe...
						//$text_prof=$tab_bull['groupe'][$m]["profs"]["proflist_string"]."  ";
						$text_prof="";
						for($loop_prof_grp=0;$loop_prof_grp<count($tab_bull['eleve'][$i]['aid_e'][$m]['aid_prof_resp_login']);$loop_prof_grp++) {
							$tmp_login_prof=$tab_bull['eleve'][$i]['aid_e'][$m]['aid_prof_resp_login'][$loop_prof_grp];
							if($loop_prof_grp>0) {$text_prof.=", ";}
							$text_prof.=affiche_utilisateur($tmp_login_prof,$tab_bull['eleve'][$i]['id_classe']);
						}

						if($text_prof!="") {
							//$espace_matiere_prof = $espace_entre_matier/2;
							$espace_matiere_prof = $hauteur_EPI_AP_Parcours/2;
							$hauteur_caractere_prof = 7;

							$texte=$text_prof;
							$taille_max_police=$hauteur_caractere_prof;
							$taille_min_police=ceil($hauteur_caractere_prof/3);

							$largeur_dispo=$param_bull2016["largeur_EPI_AP_Parcours_col_1"];
							$h_cell=$espace_matiere_prof;

							$pdf->SetX($param_bull2016["x_EPI_AP_Parcours"]);

							cell_ajustee(($texte),$pdf->GetX(),$pdf->GetY(),$largeur_dispo,$h_cell,$taille_max_police,$taille_min_police,'');
						}
					}


					// Colonne 2 : Appréciation
					$pdf->SetFillColor($param_bull2016["couleur_EPI_alt".($cpt_matiere%2+1)]["R"], $param_bull2016["couleur_EPI_alt".($cpt_matiere%2+1)]["V"], $param_bull2016["couleur_EPI_alt".($cpt_matiere%2+1)]["B"]);
					$pdf->Rect($param_bull2016["x_EPI_AP_Parcours_col_2"], $y_courant, $param_bull2016["largeur_EPI_AP_Parcours_col_2"], $hauteur_EPI_AP_Parcours, 'F');

					$pdf->SetFillColor(0, 0, 0);
					$pdf->SetXY($param_bull2016["x_EPI_AP_Parcours_col_2"], $y_courant);

					// calcul de la taille du texte des appréciations
					$hauteur_caractere_appreciation = 8;
					$pdf->SetFont('DejaVu','',$hauteur_caractere_appreciation);

					//suppression des espaces en début et en fin
					//$app_aff = trim($tab_bull['app'][$m][$i]);
					$app_aff="";
					if($tab_bull['eleve'][$i]['aid_e'][$m]['message']!='') {
						$app_aff.=$tab_bull['eleve'][$i]['aid_e'][$m]['message'];
					}
					//if($app_aff!='') {$app_aff.=" ";}
					if(($tab_bull['eleve'][$i]['aid_e'][$m]['display_nom']=='y')&&($tab_bull['eleve'][$i]['aid_e'][$m]['aid_nom']!='')) {
						if($app_aff!='') {$app_aff.=" ";}
						$app_aff.=$tab_bull['eleve'][$i]['aid_e'][$m]['aid_nom'];
					}
					if($app_aff!='') {$app_aff.="\n";}
					$app_aff.=trim($tab_bull['eleve'][$i]['aid_e'][$m]['aid_appreciation']);

					fich_debug_bull("__________________________________________\n");
					fich_debug_bull("$app_aff\n");
					fich_debug_bull("__________________________________________\n");

					// DEBUT AJUSTEMENT TAILLE APPRECIATION
					$taille_texte_total = $pdf->GetStringWidth($app_aff);
					//$largeur_appreciation2 = $largeur_appreciation - $largeur_sous_matiere;
					$largeur_appreciation2=$param_bull2016["largeur_EPI_AP_Parcours_col_2"];

					if($use_cell_ajustee=="n") {
						//$taille_texte = (($espace_entre_matier/3)*$largeur_appreciation2);
						$nb_ligne_app = '2.8';
						//$nb_ligne_app = '3.8';
						//$nb_ligne_app = '4.8';
						$taille_texte_max = $nb_ligne_app * ($largeur_appreciation2-4);
						//$taille_texte_max = $nb_ligne_app * ($largeur_appreciation2);
						$grandeur_texte='test';

						fich_debug_bull("\$taille_texte_total=$taille_texte_total\n");
						fich_debug_bull("\$largeur_appreciation2=$largeur_appreciation2\n");
						fich_debug_bull("\$nb_ligne_app=$nb_ligne_app\n");
						//fich_debug_bull("\$taille_texte_max = \$nb_ligne_app * (\$largeur_appreciation2-4)=$nb_ligne_app * ($largeur_appreciation2-4)=$taille_texte_max\n");
						fich_debug_bull("\$taille_texte_max = \$nb_ligne_app * (\$largeur_appreciation2)=$nb_ligne_app * ($largeur_appreciation2)=$taille_texte_max\n");

						while($grandeur_texte!='ok') {
							if($taille_texte_max < $taille_texte_total)
							{
								$hauteur_caractere_appreciation = $hauteur_caractere_appreciation-0.3;
								//$hauteur_caractere_appreciation = $hauteur_caractere_appreciation-0.1;
								$pdf->SetFont('DejaVu','',$hauteur_caractere_appreciation);
								$taille_texte_total = $pdf->GetStringWidth($app_aff);
							}
							else {
								$grandeur_texte='ok';
							}
						}
						$grandeur_texte='test';
						$pdf->drawTextBox(($app_aff), $largeur_appreciation2, $hauteur_EPI_AP_Parcours, 'J', 'M', 1);
					}
					else {
						$texte=$app_aff;
						//$texte="Bla bla\nbli ".$app_aff;
						$taille_max_police=$hauteur_caractere_appreciation;
						$taille_min_police=ceil($taille_max_police/3);

						$largeur_dispo=$largeur_appreciation2;
						$h_cell=$hauteur_EPI_AP_Parcours;

						if(getSettingValue('suppr_balises_app_prof')=='y') {$texte=preg_replace('/<(.*)>/U','',$texte);}
						cell_ajustee(($texte),$pdf->GetX(),$pdf->GetY(),$largeur_dispo,$h_cell,$taille_max_police,$taille_min_police,'');
					}

					$y_courant+=$hauteur_EPI_AP_Parcours+0.5;
					$cpt_matiere++;


				}
				// Fin des AID_e tagués AP
			}

			$y_courant+=$param_bull2016["espace_vertical_entre_sections_EPI_AP_Parcours"];
		}



		//++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
		//++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
		//++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

		//=========================================

		// Parcours éducatifs

		if($nb_Parcours>0) {
			// On met un blanc de 5mm au-dessus de la ligne de titre EPI
			//$y_courant+=5;
			$y_courant+=$param_bull2016["espace_vertical_entre_sections_EPI_AP_Parcours"];

			$pdf->SetFillColor($param_bull2016["couleur_bandeau_EPI"]["R"], $param_bull2016["couleur_bandeau_EPI"]["V"], $param_bull2016["couleur_bandeau_EPI"]["B"]);
			$pdf->Rect($param_bull2016["x_EPI_AP_Parcours"], $y_courant, $param_bull2016["largeur_EPI_AP_Parcours"], $param_bull2016["hauteur_ligne_titre_EPI_AP_Parcours"], 'F');

			$pdf->SetFillColor(0, 0, 0);
			//$pdf->SetTextColor(255, 255, 255);
			$pdf->SetTextColor(0, 0, 0);
			$pdf->SetXY($param_bull2016["x_EPI_AP_Parcours"], $y_courant);
			$pdf->SetFont('DejaVu','',8);
			$pdf->Cell($param_bull2016["largeur_EPI_AP_Parcours"],7, "Parcours éducatifs : projet(s) mis en oeuvre et implication de l'élève",0,2,'L');

			$cpt_matiere=0;
			$y_courant+=$param_bull2016["hauteur_ligne_titre_EPI_AP_Parcours"]+0.5;

			if("$i"=="") {
				if(isset($tab_bull['aid'])) {
					for($m=0;$m<count($tab_bull['aid']);$m++) {
						if($tab_bull['aid'][$m]['order_display1']=="b") {
							if($tab_bull['aid'][$m]['type_aid']==3) {

								// Colonne 1 : Matière, prof

								$pdf->SetFillColor($param_bull2016["couleur_EPI_alt".($cpt_matiere%2+1)]["R"], $param_bull2016["couleur_EPI_alt".($cpt_matiere%2+1)]["V"], $param_bull2016["couleur_EPI_alt".($cpt_matiere%2+1)]["B"]);
								$pdf->Rect($param_bull2016["x_EPI_AP_Parcours"], $y_courant, $param_bull2016["largeur_EPI_AP_Parcours_col_1"], $hauteur_EPI_AP_Parcours, 'F');
								$pdf->SetFillColor(0, 0, 0);

								$pdf->SetTextColor(0, 0, 0);
								$pdf->SetXY($param_bull2016["x_EPI_AP_Parcours"], $y_courant);
								$pdf->SetFont('DejaVu','',8);

								if($tab_bull['aid'][$m]['display_nom']=='z') {
									if((isset($tab_bull['aid'][$m]['aid_nom']))&&($tab_bull['aid'][$m]['aid_nom']!="")) {
										$info_nom_matiere=$tab_bull['aid'][$m]['aid_nom'];
									}
									elseif((isset($tab_bull['aid'][$m]['nom_complet']))&&($tab_bull['aid'][$m]['nom_complet']!="")) {
										$info_nom_matiere=$tab_bull['aid'][$m]['nom_complet'];
									}
									else {
										$info_nom_matiere=$tab_bull['aid'][$m]['nom'];
									}
								}
								elseif($tab_bull['aid'][$m]['display_nom']=='x') {
									if((isset($tab_bull['aid'][$m]['nom']))&&($tab_bull['aid'][$m]['nom']!="")) {
										$info_nom_matiere=$tab_bull['aid'][$m]['nom'];
									}
									else {
										$info_nom_matiere=$tab_bull['aid'][$m]['nom_complet'];
									}

									if((isset($tab_bull['aid'][$m]['aid_nom']))&&($tab_bull['aid'][$m]['aid_nom']!="")) {
										if($info_nom_matiere!="") {
											$info_nom_matiere.=": ";
										}
										$info_nom_matiere.=$tab_bull['aid'][$m]['aid_nom'];
									}
								}
								else {
									if((isset($tab_bull['aid'][$m]['nom_complet']))&&($tab_bull['aid'][$m]['nom_complet']!="")) {
										$info_nom_matiere=$tab_bull['aid'][$m]['nom_complet'];
									}
									else {
										$info_nom_matiere=$tab_bull['aid'][$m]['nom'];
									}
								}
								if($info_nom_matiere=="") {
									$info_nom_matiere="AID";
								}

								$hauteur_caractere_matiere=8;
								$cell_ajustee_texte_matiere_ratio_min_max=3;

								// Forcé pour le moment
								$cell_ajustee_texte_matiere=1;
								if((isset($cell_ajustee_texte_matiere))&&($cell_ajustee_texte_matiere==1)) {
									// On met un décalage pour ne pas coller le texte à la bordure
									$Y_decal_cell_ajustee=2;
									$pdf->SetXY($param_bull2016["x_EPI_AP_Parcours"], $y_courant+$Y_decal_cell_ajustee);

									$texte=$info_nom_matiere;
									$taille_max_police=$hauteur_caractere_matiere;
									$taille_min_police=ceil($taille_max_police/$cell_ajustee_texte_matiere_ratio_min_max);

									$largeur_dispo=$param_bull2016["largeur_EPI_AP_Parcours_col_1"];
									$h_cell=$hauteur_EPI_AP_Parcours/2-$Y_decal_cell_ajustee;

									cell_ajustee($texte,$pdf->GetX(),$pdf->GetY(),$largeur_dispo,$h_cell,$taille_max_police,$taille_min_police,'');
		
								}
								else {
									$val = $pdf->GetStringWidth($info_nom_matiere);
									$taille_texte = $param_bull2016["largeur_EPI_AP_Parcours_col_1"]-2;
									$grandeur_texte='test';
									while($grandeur_texte!='ok') {
										if($taille_texte<$val)
										{
											$hauteur_caractere_matiere = $hauteur_caractere_matiere-0.3;
											$pdf->SetFont('DejaVu','B',$hauteur_caractere_matiere);
											$val = $pdf->GetStringWidth($info_nom_matiere);
										}
										else {
											$grandeur_texte='ok';
										}
									}
									$grandeur_texte='test';
									$Y_decal=$y_courant;
									$pdf->SetXY($param_bull2016["x_EPI_AP_Parcours"], $Y_decal);
									$pdf->Cell($param_bull2016["largeur_EPI_AP_Parcours_col_1"], $hauteur_EPI_AP_Parcours/2, ($info_nom_matiere),'',1,'L');
								}


								// On note l'ordonnée pour le nom des professeurs
								$Y_decal = $y_courant+($hauteur_EPI_AP_Parcours/2);
								$pdf->SetXY($param_bull2016["x_EPI_AP_Parcours"], $Y_decal);
								$pdf->SetFont('DejaVu','',8);

								// nom des professeurs
								if ( isset($tab_bull['aid'][$m]['aid_prof_resp_login'][0]) )
								{

									// Présentation en ligne des profs
									// On n'a pas forcément le formatage choisi pour la classe...
									//$text_prof=$tab_bull['groupe'][$m]["profs"]["proflist_string"]."  ";
									$text_prof="";
									for($loop_prof_grp=0;$loop_prof_grp<count($tab_bull['aid'][$m]['profs']['list']);$loop_prof_grp++) {
										$tmp_login_prof=$tab_bull['aid'][$m]['profs']['list'][$loop_prof_grp];
										if($loop_prof_grp>0) {$text_prof.=", ";}
										$text_prof.=affiche_utilisateur($tmp_login_prof,$tab_bull['id_classe']);
									}

									if($text_prof!="") {
										//$espace_matiere_prof = $espace_entre_matier/2;
										$espace_matiere_prof = $hauteur_EPI_AP_Parcours/2;
										$hauteur_caractere_prof = 7;

										$texte=$text_prof;
										$taille_max_police=$hauteur_caractere_prof;
										$taille_min_police=ceil($hauteur_caractere_prof/3);

										$largeur_dispo=$param_bull2016["largeur_EPI_AP_Parcours_col_1"];
										$h_cell=$espace_matiere_prof;

										$pdf->SetX($param_bull2016["x_EPI_AP_Parcours"]);

										cell_ajustee(($texte),$pdf->GetX(),$pdf->GetY(),$largeur_dispo,$h_cell,$taille_max_police,$taille_min_police,'');
									}
								}


								// Colonne 2 : Appréciation
								$pdf->SetFillColor($param_bull2016["couleur_EPI_alt".($cpt_matiere%2+1)]["R"], $param_bull2016["couleur_EPI_alt".($cpt_matiere%2+1)]["V"], $param_bull2016["couleur_EPI_alt".($cpt_matiere%2+1)]["B"]);
								$pdf->Rect($param_bull2016["x_EPI_AP_Parcours_col_2"], $y_courant, $param_bull2016["largeur_EPI_AP_Parcours_col_2"], $hauteur_EPI_AP_Parcours, 'F');

								$pdf->SetFillColor(0, 0, 0);
								$pdf->SetXY($param_bull2016["x_EPI_AP_Parcours_col_2"], $y_courant);

								// calcul de la taille du texte des appréciations
								$hauteur_caractere_appreciation = 8;
								$pdf->SetFont('DejaVu','',$hauteur_caractere_appreciation);

								//suppression des espaces en début et en fin
								//$app_aff = trim($tab_bull['app'][$m][$i]);
								$app_aff="";
								if($tab_bull['aid'][$m]['message']!='') {
									$app_aff.=$tab_bull['aid'][$m]['message'];
								}
								//if($app_aff!='') {$app_aff.=" ";}
								if(($tab_bull['aid'][$m]['display_nom']=='y')&&($tab_bull['aid'][$m]['aid_nom']!='')) {
									if($app_aff!='') {$app_aff.=" ";}
									$app_aff.=$tab_bull['aid'][$m]['aid_nom'];
								}
								if($app_aff!='') {$app_aff.="\n";}
								$app_aff.=trim($tab_bull['aid'][$m]['app_grp']);

								fich_debug_bull("__________________________________________\n");
								fich_debug_bull("$app_aff\n");
								fich_debug_bull("__________________________________________\n");

								// DEBUT AJUSTEMENT TAILLE APPRECIATION
								$taille_texte_total = $pdf->GetStringWidth($app_aff);
								//$largeur_appreciation2 = $largeur_appreciation - $largeur_sous_matiere;
								$largeur_appreciation2=$param_bull2016["largeur_EPI_AP_Parcours_col_2"];

								if($use_cell_ajustee=="n") {
									//$taille_texte = (($espace_entre_matier/3)*$largeur_appreciation2);
									$nb_ligne_app = '2.8';
									//$nb_ligne_app = '3.8';
									//$nb_ligne_app = '4.8';
									$taille_texte_max = $nb_ligne_app * ($largeur_appreciation2-4);
									//$taille_texte_max = $nb_ligne_app * ($largeur_appreciation2);
									$grandeur_texte='test';

									fich_debug_bull("\$taille_texte_total=$taille_texte_total\n");
									fich_debug_bull("\$largeur_appreciation2=$largeur_appreciation2\n");
									fich_debug_bull("\$nb_ligne_app=$nb_ligne_app\n");
									//fich_debug_bull("\$taille_texte_max = \$nb_ligne_app * (\$largeur_appreciation2-4)=$nb_ligne_app * ($largeur_appreciation2-4)=$taille_texte_max\n");
									fich_debug_bull("\$taille_texte_max = \$nb_ligne_app * (\$largeur_appreciation2)=$nb_ligne_app * ($largeur_appreciation2)=$taille_texte_max\n");

									while($grandeur_texte!='ok') {
										if($taille_texte_max < $taille_texte_total)
										{
											$hauteur_caractere_appreciation = $hauteur_caractere_appreciation-0.3;
											//$hauteur_caractere_appreciation = $hauteur_caractere_appreciation-0.1;
											$pdf->SetFont('DejaVu','',$hauteur_caractere_appreciation);
											$taille_texte_total = $pdf->GetStringWidth($app_aff);
										}
										else {
											$grandeur_texte='ok';
										}
									}
									$grandeur_texte='test';
									$pdf->drawTextBox(($app_aff), $largeur_appreciation2, $hauteur_EPI_AP_Parcours, 'J', 'M', 1);
								}
								else {
									$texte=$app_aff;
									//$texte="Bla bla\nbli ".$app_aff;
									$taille_max_police=$hauteur_caractere_appreciation;
									$taille_min_police=ceil($taille_max_police/3);

									$largeur_dispo=$largeur_appreciation2;
									$h_cell=$hauteur_EPI_AP_Parcours;

									if(getSettingValue('suppr_balises_app_prof')=='y') {$texte=preg_replace('/<(.*)>/U','',$texte);}
									cell_ajustee(($texte),$pdf->GetX(),$pdf->GetY(),$largeur_dispo,$h_cell,$taille_max_police,$taille_min_police,'');
								}

								$y_courant+=$hauteur_EPI_AP_Parcours+0.5;
								$cpt_matiere++;


							}
							// Fin des AID_b tagués Parcours
						}
					}
				}

				// Enseignements de la table 'groupes' tagués Parcours
				for($m=0;$m<count($tab_bull['groupe']);$m++) {
					// On n'affiche pas ici les groupes correspondant à AP, EPI ou Parcours
					if((isset($tab_bull['groupe'][$m]['type_grp'][0]))&&
					($tab_bull['groupe'][$m]['type_grp'][0]['nom_court']=="Parcours")) {

						// Colonne 1 : Matière, prof

						$pdf->SetFillColor($param_bull2016["couleur_EPI_alt".($cpt_matiere%2+1)]["R"], $param_bull2016["couleur_EPI_alt".($cpt_matiere%2+1)]["V"], $param_bull2016["couleur_EPI_alt".($cpt_matiere%2+1)]["B"]);
						$pdf->Rect($param_bull2016["x_EPI_AP_Parcours"], $y_courant, $param_bull2016["largeur_EPI_AP_Parcours_col_1"], $hauteur_EPI_AP_Parcours, 'F');
						$pdf->SetFillColor(0, 0, 0);

						$pdf->SetTextColor(0, 0, 0);
						$pdf->SetXY($param_bull2016["x_EPI_AP_Parcours"], $y_courant);
						$pdf->SetFont('DejaVu','',8);

						if(getSettingValue('bul_rel_nom_matieres')=='nom_groupe') {
							$info_nom_matiere=$tab_bull['groupe'][$m]['name'];
						}
						elseif(getSettingValue('bul_rel_nom_matieres')=='description_groupe') {
							$info_nom_matiere=$tab_bull['groupe'][$m]['description'];
						}
						else {
							// Pour parer au bug sur la suppression de matière alors que des groupes sont conservés:
							if(isset($tab_bull['groupe'][$m]['matiere']['nom_complet'])) {
								$info_nom_matiere=$tab_bull['groupe'][$m]['matiere']['nom_complet'];
							}
							else {
								$info_nom_matiere=$tab_bull['groupe'][$m]['name']." (".$tab_bull['groupe'][$m]['id'].")";
							}
						}

						$hauteur_caractere_matiere=8;
						$cell_ajustee_texte_matiere_ratio_min_max=3;

						// 20130927 : cell_ajustee() ou pas sur le nom de matière/enseignement
						$cell_ajustee_texte_matiere=1;
						if((isset($cell_ajustee_texte_matiere))&&($cell_ajustee_texte_matiere==1)) {
							// Encadrement
							//$pdf->Cell($tab_modele_pdf["largeur_matiere"][$classe_id], $espace_entre_matier, "",'LRBT',1,'L');

							// cell_ajustee() ne centre pas verticalement le texte.
							// On met un décalage pour ne pas coller le texte à la bordure
							$Y_decal_cell_ajustee=2;
							// On repositionne et on inscrit le nom de matière sur la moitié de la hauteur de la cellule
							//$pdf->SetXY($X_bloc_matiere, $Y_decal+$Y_decal_cell_ajustee);
							//$pdf->SetXY($param_bull2016["x_acquis_col_1"], $y_courant);

							$pdf->SetXY($param_bull2016["x_EPI_AP_Parcours"], $y_courant+$Y_decal_cell_ajustee);

							$texte=$info_nom_matiere;
							$taille_max_police=$hauteur_caractere_matiere;
							//$taille_min_police=ceil($taille_max_police/$tab_modele_pdf["cell_ajustee_texte_matiere_ratio_min_max"][$classe_id]);
							$taille_min_police=ceil($taille_max_police/$cell_ajustee_texte_matiere_ratio_min_max);

							$largeur_dispo=$param_bull2016["largeur_EPI_AP_Parcours_col_1"];
							//$h_cell=$espace_entre_matier/2-$Y_decal_cell_ajustee;
							$h_cell=$hauteur_EPI_AP_Parcours/2-$Y_decal_cell_ajustee;

							//cell_ajustee("<b>".$texte."</b>",$pdf->GetX(),$pdf->GetY(),$largeur_dispo,$h_cell,$taille_max_police,$taille_min_police,'');
							cell_ajustee($texte,$pdf->GetX(),$pdf->GetY(),$largeur_dispo,$h_cell,$taille_max_police,$taille_min_police,'');
		
						}
						else {
							$val = $pdf->GetStringWidth($info_nom_matiere);
							$taille_texte = $param_bull2016["largeur_EPI_AP_Parcours_col_1"]-2;
							$grandeur_texte='test';
							while($grandeur_texte!='ok') {
								if($taille_texte<$val)
								{
									$hauteur_caractere_matiere = $hauteur_caractere_matiere-0.3;
									$pdf->SetFont('DejaVu','',$hauteur_caractere_matiere);
									$val = $pdf->GetStringWidth($info_nom_matiere);
								}
								else {
									$grandeur_texte='ok';
								}
							}
							$grandeur_texte='test';
							$Y_decal=$y_courant;
							$pdf->SetXY($param_bull2016["x_EPI_AP_Parcours"], $Y_decal);
							$pdf->Cell($param_bull2016["largeur_EPI_AP_Parcours_col_1"], $hauteur_EPI_AP_Parcours/2, ($info_nom_matiere),'',1,'L');
						}
		

						// On note l'ordonnée pour le nom des professeurs
						$Y_decal = $y_courant+($hauteur_EPI_AP_Parcours/2);
						$pdf->SetXY($param_bull2016["x_EPI_AP_Parcours"], $Y_decal);
						$pdf->SetFont('DejaVu','',8);

						//fich_debug_bull("\$info_nom_matiere=$info_nom_matiere\n");
						//fich_debug_bull("Le nom de matière est écrit; on est à mi-hauteur de la cellule pour écrire le nom du prof:\n");
						//fich_debug_bull("\$Y_decal=$Y_decal\n");

						// nom des professeurs

						if ( isset($tab_bull['groupe'][$m]["profs"]["list"]) )
						{

							// Présentation en ligne des profs
							// On n'a pas forcément le formatage choisi pour la classe...
							//$text_prof=$tab_bull['groupe'][$m]["profs"]["proflist_string"]."  ";
							$text_prof="";
							for($loop_prof_grp=0;$loop_prof_grp<count($tab_bull['groupe'][$m]["profs"]["list"]);$loop_prof_grp++) {
								$tmp_login_prof=$tab_bull['groupe'][$m]["profs"]["list"][$loop_prof_grp];
								if($loop_prof_grp>0) {$text_prof.=", ";}
								$text_prof.=affiche_utilisateur($tmp_login_prof,$tab_bull['id_classe']);
							}

							if($text_prof!="") {
								//$espace_matiere_prof = $espace_entre_matier/2;
								$espace_matiere_prof = $hauteur_EPI_AP_Parcours/2;
								$hauteur_caractere_prof = 7;

								/*
								if($use_cell_ajustee=="n") {
									$pdf->SetFont('DejaVu','',$hauteur_caractere_prof);
									$val = $pdf->GetStringWidth($text_prof);
									$taille_texte = ($tab_modele_pdf["largeur_matiere"][$classe_id]);
									$grandeur_texte='test';
									while($grandeur_texte!='ok') {
										if($taille_texte<$val)
										{
											$hauteur_caractere_prof = $hauteur_caractere_prof-0.3;
											$pdf->SetFont('DejaVu','',$hauteur_caractere_prof);
											$val = $pdf->GetStringWidth($text_prof);
										}
										else {
											$grandeur_texte='ok';
										}
									}
									$grandeur_texte='test';
									$pdf->SetX($X_bloc_matiere);
									$pdf->Cell($tab_modele_pdf["largeur_matiere"][$classe_id], $espace_matiere_prof, ($text_prof),'LR',1,'L');
								}
								else {
								*/
									$texte=$text_prof;
									$taille_max_police=$hauteur_caractere_prof;
									$taille_min_police=ceil($hauteur_caractere_prof/3);

									$largeur_dispo=$param_bull2016["largeur_EPI_AP_Parcours_col_1"];
									$h_cell=$espace_matiere_prof;

									$pdf->SetX($param_bull2016["x_EPI_AP_Parcours"]);

									cell_ajustee(($texte),$pdf->GetX(),$pdf->GetY(),$largeur_dispo,$h_cell,$taille_max_police,$taille_min_police,'');
								//}
							}
						}


						// Colonne 2 : Appréciation
						$pdf->SetFillColor($param_bull2016["couleur_EPI_alt".($cpt_matiere%2+1)]["R"], $param_bull2016["couleur_EPI_alt".($cpt_matiere%2+1)]["V"], $param_bull2016["couleur_EPI_alt".($cpt_matiere%2+1)]["B"]);
						$pdf->Rect($param_bull2016["x_EPI_AP_Parcours_col_2"], $y_courant, $param_bull2016["largeur_EPI_AP_Parcours_col_2"], $hauteur_EPI_AP_Parcours, 'F');

						$pdf->SetFillColor(0, 0, 0);
						$pdf->SetXY($param_bull2016["x_EPI_AP_Parcours_col_2"], $y_courant);

						// calcul de la taille du texte des appréciations
						$hauteur_caractere_appreciation = 8;
						$pdf->SetFont('DejaVu','',$hauteur_caractere_appreciation);

						//suppression des espaces en début et en fin
						$app_aff = trim($tab_bull['app_grp'][$m]);

						fich_debug_bull("__________________________________________\n");
						fich_debug_bull("$app_aff\n");
						fich_debug_bull("__________________________________________\n");

						// DEBUT AJUSTEMENT TAILLE APPRECIATION
						$taille_texte_total = $pdf->GetStringWidth($app_aff);
						//$largeur_appreciation2 = $largeur_appreciation - $largeur_sous_matiere;
						$largeur_appreciation2=$param_bull2016["largeur_EPI_AP_Parcours_col_2"];

						if($use_cell_ajustee=="n") {
							//$taille_texte = (($espace_entre_matier/3)*$largeur_appreciation2);
							$nb_ligne_app = '2.8';
							//$nb_ligne_app = '3.8';
							//$nb_ligne_app = '4.8';
							$taille_texte_max = $nb_ligne_app * ($largeur_appreciation2-4);
							//$taille_texte_max = $nb_ligne_app * ($largeur_appreciation2);
							$grandeur_texte='test';

							fich_debug_bull("\$taille_texte_total=$taille_texte_total\n");
							fich_debug_bull("\$largeur_appreciation2=$largeur_appreciation2\n");
							fich_debug_bull("\$nb_ligne_app=$nb_ligne_app\n");
							//fich_debug_bull("\$taille_texte_max = \$nb_ligne_app * (\$largeur_appreciation2-4)=$nb_ligne_app * ($largeur_appreciation2-4)=$taille_texte_max\n");
							fich_debug_bull("\$taille_texte_max = \$nb_ligne_app * (\$largeur_appreciation2)=$nb_ligne_app * ($largeur_appreciation2)=$taille_texte_max\n");

							while($grandeur_texte!='ok') {
								if($taille_texte_max < $taille_texte_total)
								{
									$hauteur_caractere_appreciation = $hauteur_caractere_appreciation-0.3;
									//$hauteur_caractere_appreciation = $hauteur_caractere_appreciation-0.1;
									$pdf->SetFont('DejaVu','',$hauteur_caractere_appreciation);
									$taille_texte_total = $pdf->GetStringWidth($app_aff);
								}
								else {
									$grandeur_texte='ok';
								}
							}
							$grandeur_texte='test';
							$pdf->drawTextBox(($app_aff), $largeur_appreciation2, $hauteur_EPI_AP_Parcours, 'J', 'M', 1);
						}
						else {
							$texte=$app_aff;
							//$texte="Bla bla\nbli ".$app_aff;
							$taille_max_police=$hauteur_caractere_appreciation;
							$taille_min_police=ceil($taille_max_police/3);

							$largeur_dispo=$largeur_appreciation2;
							$h_cell=$hauteur_EPI_AP_Parcours;

							if(getSettingValue('suppr_balises_app_prof')=='y') {$texte=preg_replace('/<(.*)>/U','',$texte);}
							cell_ajustee(($texte),$pdf->GetX(),$pdf->GetY(),$largeur_dispo,$h_cell,$taille_max_police,$taille_min_police,'');
						}

						$y_courant+=$hauteur_EPI_AP_Parcours+0.5;
						$cpt_matiere++;

					}
				}
				// Fin des enseignements de la table 'groupes' tagués Parcours


				// AID_e de type Parcours
				if(isset($tab_bull['aid'])) {
					for($m=0;$m<count($tab_bull['aid']);$m++) {
						if($tab_bull['aid'][$m]['order_display1']=="e") {
							if($tab_bull['aid'][$m]['type_aid']==3) {

								// Colonne 1 : Matière, prof

								$pdf->SetFillColor($param_bull2016["couleur_EPI_alt".($cpt_matiere%2+1)]["R"], $param_bull2016["couleur_EPI_alt".($cpt_matiere%2+1)]["V"], $param_bull2016["couleur_EPI_alt".($cpt_matiere%2+1)]["B"]);
								$pdf->Rect($param_bull2016["x_EPI_AP_Parcours"], $y_courant, $param_bull2016["largeur_EPI_AP_Parcours_col_1"], $hauteur_EPI_AP_Parcours, 'F');
								$pdf->SetFillColor(0, 0, 0);

								$pdf->SetTextColor(0, 0, 0);
								$pdf->SetXY($param_bull2016["x_EPI_AP_Parcours"], $y_courant);
								$pdf->SetFont('DejaVu','',8);

								if($tab_bull['aid'][$m]['display_nom']=='z') {
									if((isset($tab_bull['aid'][$m]['aid_nom']))&&($tab_bull['aid'][$m]['aid_nom']!="")) {
										$info_nom_matiere=$tab_bull['aid'][$m]['aid_nom'];
									}
									elseif((isset($tab_bull['aid'][$m]['nom_complet']))&&($tab_bull['aid'][$m]['nom_complet']!="")) {
										$info_nom_matiere=$tab_bull['aid'][$m]['nom_complet'];
									}
									else {
										$info_nom_matiere=$tab_bull['aid'][$m]['nom'];
									}
								}
								elseif($tab_bull['aid'][$m]['display_nom']=='x') {
									if((isset($tab_bull['aid'][$m]['nom']))&&($tab_bull['aid'][$m]['nom']!="")) {
										$info_nom_matiere=$tab_bull['aid'][$m]['nom'];
									}
									else {
										$info_nom_matiere=$tab_bull['aid'][$m]['nom_complet'];
									}

									if((isset($tab_bull['aid'][$m]['aid_nom']))&&($tab_bull['aid'][$m]['aid_nom']!="")) {
										if($info_nom_matiere!="") {
											$info_nom_matiere.=": ";
										}
										$info_nom_matiere.=$tab_bull['aid'][$m]['aid_nom'];
									}
								}
								else {
									if((isset($tab_bull['aid'][$m]['nom_complet']))&&($tab_bull['aid'][$m]['nom_complet']!="")) {
										$info_nom_matiere=$tab_bull['aid'][$m]['nom_complet'];
									}
									else {
										$info_nom_matiere=$tab_bull['aid'][$m]['nom'];
									}
								}
								if($info_nom_matiere=="") {
									$info_nom_matiere="AID";
								}

								$hauteur_caractere_matiere=8;
								$cell_ajustee_texte_matiere_ratio_min_max=3;

								// Forcé pour le moment
								$cell_ajustee_texte_matiere=1;
								if((isset($cell_ajustee_texte_matiere))&&($cell_ajustee_texte_matiere==1)) {
									// On met un décalage pour ne pas coller le texte à la bordure
									$Y_decal_cell_ajustee=2;
									$pdf->SetXY($param_bull2016["x_EPI_AP_Parcours"], $y_courant+$Y_decal_cell_ajustee);

									$texte=$info_nom_matiere;
									$taille_max_police=$hauteur_caractere_matiere;
									$taille_min_police=ceil($taille_max_police/$cell_ajustee_texte_matiere_ratio_min_max);

									$largeur_dispo=$param_bull2016["largeur_EPI_AP_Parcours_col_1"];
									$h_cell=$hauteur_EPI_AP_Parcours/2-$Y_decal_cell_ajustee;

									cell_ajustee($texte,$pdf->GetX(),$pdf->GetY(),$largeur_dispo,$h_cell,$taille_max_police,$taille_min_police,'');
		
								}
								else {
									$val = $pdf->GetStringWidth($info_nom_matiere);
									$taille_texte = $param_bull2016["largeur_EPI_AP_Parcours_col_1"]-2;
									$grandeur_texte='test';
									while($grandeur_texte!='ok') {
										if($taille_texte<$val)
										{
											$hauteur_caractere_matiere = $hauteur_caractere_matiere-0.3;
											$pdf->SetFont('DejaVu','B',$hauteur_caractere_matiere);
											$val = $pdf->GetStringWidth($info_nom_matiere);
										}
										else {
											$grandeur_texte='ok';
										}
									}
									$grandeur_texte='test';
									$Y_decal=$y_courant;
									$pdf->SetXY($param_bull2016["x_EPI_AP_Parcours"], $Y_decal);
									$pdf->Cell($param_bull2016["largeur_EPI_AP_Parcours_col_1"], $hauteur_EPI_AP_Parcours/2, ($info_nom_matiere),'',1,'L');
								}


								// On note l'ordonnée pour le nom des professeurs
								$Y_decal = $y_courant+($hauteur_EPI_AP_Parcours/2);
								$pdf->SetXY($param_bull2016["x_EPI_AP_Parcours"], $Y_decal);
								$pdf->SetFont('DejaVu','',8);

								// nom des professeurs
								if ( isset($tab_bull['aid'][$m]['aid_prof_resp_login'][0]) )
								{

									// Présentation en ligne des profs
									// On n'a pas forcément le formatage choisi pour la classe...
									//$text_prof=$tab_bull['groupe'][$m]["profs"]["proflist_string"]."  ";
									$text_prof="";
									for($loop_prof_grp=0;$loop_prof_grp<count($tab_bull['aid'][$m]['profs']['list']);$loop_prof_grp++) {
										$tmp_login_prof=$tab_bull['aid'][$m]['profs']['list'][$loop_prof_grp];
										if($loop_prof_grp>0) {$text_prof.=", ";}
										$text_prof.=affiche_utilisateur($tmp_login_prof,$tab_bull['id_classe']);
									}

									if($text_prof!="") {
										//$espace_matiere_prof = $espace_entre_matier/2;
										$espace_matiere_prof = $hauteur_EPI_AP_Parcours/2;
										$hauteur_caractere_prof = 7;

										$texte=$text_prof;
										$taille_max_police=$hauteur_caractere_prof;
										$taille_min_police=ceil($hauteur_caractere_prof/3);

										$largeur_dispo=$param_bull2016["largeur_EPI_AP_Parcours_col_1"];
										$h_cell=$espace_matiere_prof;

										$pdf->SetX($param_bull2016["x_EPI_AP_Parcours"]);

										cell_ajustee(($texte),$pdf->GetX(),$pdf->GetY(),$largeur_dispo,$h_cell,$taille_max_police,$taille_min_police,'');
									}
								}


								// Colonne 2 : Appréciation
								$pdf->SetFillColor($param_bull2016["couleur_EPI_alt".($cpt_matiere%2+1)]["R"], $param_bull2016["couleur_EPI_alt".($cpt_matiere%2+1)]["V"], $param_bull2016["couleur_EPI_alt".($cpt_matiere%2+1)]["B"]);
								$pdf->Rect($param_bull2016["x_EPI_AP_Parcours_col_2"], $y_courant, $param_bull2016["largeur_EPI_AP_Parcours_col_2"], $hauteur_EPI_AP_Parcours, 'F');

								$pdf->SetFillColor(0, 0, 0);
								$pdf->SetXY($param_bull2016["x_EPI_AP_Parcours_col_2"], $y_courant);

								// calcul de la taille du texte des appréciations
								$hauteur_caractere_appreciation = 8;
								$pdf->SetFont('DejaVu','',$hauteur_caractere_appreciation);

								//suppression des espaces en début et en fin
								//$app_aff = trim($tab_bull['app'][$m][$i]);
								$app_aff="";
								if($tab_bull['aid'][$m]['message']!='') {
									$app_aff.=$tab_bull['aid'][$m]['message'];
								}
								//if($app_aff!='') {$app_aff.=" ";}
								if(($tab_bull['aid'][$m]['display_nom']=='y')&&($tab_bull['aid'][$m]['aid_nom']!='')) {
									if($app_aff!='') {$app_aff.=" ";}
									$app_aff.=$tab_bull['aid'][$m]['aid_nom'];
								}
								if($app_aff!='') {$app_aff.="\n";}
								$app_aff.=trim($tab_bull['aid'][$m]['app_grp']);

								fich_debug_bull("__________________________________________\n");
								fich_debug_bull("$app_aff\n");
								fich_debug_bull("__________________________________________\n");

								// DEBUT AJUSTEMENT TAILLE APPRECIATION
								$taille_texte_total = $pdf->GetStringWidth($app_aff);
								//$largeur_appreciation2 = $largeur_appreciation - $largeur_sous_matiere;
								$largeur_appreciation2=$param_bull2016["largeur_EPI_AP_Parcours_col_2"];

								if($use_cell_ajustee=="n") {
									//$taille_texte = (($espace_entre_matier/3)*$largeur_appreciation2);
									$nb_ligne_app = '2.8';
									//$nb_ligne_app = '3.8';
									//$nb_ligne_app = '4.8';
									$taille_texte_max = $nb_ligne_app * ($largeur_appreciation2-4);
									//$taille_texte_max = $nb_ligne_app * ($largeur_appreciation2);
									$grandeur_texte='test';

									fich_debug_bull("\$taille_texte_total=$taille_texte_total\n");
									fich_debug_bull("\$largeur_appreciation2=$largeur_appreciation2\n");
									fich_debug_bull("\$nb_ligne_app=$nb_ligne_app\n");
									//fich_debug_bull("\$taille_texte_max = \$nb_ligne_app * (\$largeur_appreciation2-4)=$nb_ligne_app * ($largeur_appreciation2-4)=$taille_texte_max\n");
									fich_debug_bull("\$taille_texte_max = \$nb_ligne_app * (\$largeur_appreciation2)=$nb_ligne_app * ($largeur_appreciation2)=$taille_texte_max\n");

									while($grandeur_texte!='ok') {
										if($taille_texte_max < $taille_texte_total)
										{
											$hauteur_caractere_appreciation = $hauteur_caractere_appreciation-0.3;
											//$hauteur_caractere_appreciation = $hauteur_caractere_appreciation-0.1;
											$pdf->SetFont('DejaVu','',$hauteur_caractere_appreciation);
											$taille_texte_total = $pdf->GetStringWidth($app_aff);
										}
										else {
											$grandeur_texte='ok';
										}
									}
									$grandeur_texte='test';
									$pdf->drawTextBox(($app_aff), $largeur_appreciation2, $hauteur_EPI_AP_Parcours, 'J', 'M', 1);
								}
								else {
									$texte=$app_aff;
									//$texte="Bla bla\nbli ".$app_aff;
									$taille_max_police=$hauteur_caractere_appreciation;
									$taille_min_police=ceil($taille_max_police/3);

									$largeur_dispo=$largeur_appreciation2;
									$h_cell=$hauteur_EPI_AP_Parcours;

									if(getSettingValue('suppr_balises_app_prof')=='y') {$texte=preg_replace('/<(.*)>/U','',$texte);}
									cell_ajustee(($texte),$pdf->GetX(),$pdf->GetY(),$largeur_dispo,$h_cell,$taille_max_police,$taille_min_police,'');
								}

								$y_courant+=$hauteur_EPI_AP_Parcours+0.5;
								$cpt_matiere++;
							}
						}
					}
				}
			}
			//++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
			else {
				// On est sur une page appréciation groupe liée à un élève $i!=""

				// AID_b de type Parcours
				for($loop=0;$loop<count($indice_AID_b_Parcours);$loop++) {
					$m=$indice_AID_b_Parcours[$loop];

					// Colonne 1 : Matière, prof

					$pdf->SetFillColor($param_bull2016["couleur_EPI_alt".($cpt_matiere%2+1)]["R"], $param_bull2016["couleur_EPI_alt".($cpt_matiere%2+1)]["V"], $param_bull2016["couleur_EPI_alt".($cpt_matiere%2+1)]["B"]);
					$pdf->Rect($param_bull2016["x_EPI_AP_Parcours"], $y_courant, $param_bull2016["largeur_EPI_AP_Parcours_col_1"], $hauteur_EPI_AP_Parcours, 'F');
					$pdf->SetFillColor(0, 0, 0);

					$pdf->SetTextColor(0, 0, 0);
					$pdf->SetXY($param_bull2016["x_EPI_AP_Parcours"], $y_courant);
					$pdf->SetFont('DejaVu','',8);

					if($tab_bull['eleve'][$i]['aid_b'][$m]['display_nom']=='z') {
						if((isset($tab_bull['eleve'][$i]['aid_b'][$m]['aid_nom']))&&($tab_bull['eleve'][$i]['aid_b'][$m]['aid_nom']!="")) {
							$info_nom_matiere=$tab_bull['eleve'][$i]['aid_b'][$m]['aid_nom'];
						}
						elseif((isset($tab_bull['eleve'][$i]['aid_b'][$m]['nom_complet']))&&($tab_bull['eleve'][$i]['aid_b'][$m]['nom_complet']!="")) {
							$info_nom_matiere=$tab_bull['eleve'][$i]['aid_b'][$m]['nom_complet'];
						}
						else {
							$info_nom_matiere=$tab_bull['eleve'][$i]['aid_b'][$m]['nom'];
						}
					}
					elseif($tab_bull['eleve'][$i]['aid_b'][$m]['display_nom']=='x') {
						if((isset($tab_bull['eleve'][$i]['aid_b'][$m]['nom']))&&($tab_bull['eleve'][$i]['aid_b'][$m]['nom']!="")) {
							$info_nom_matiere=$tab_bull['eleve'][$i]['aid_b'][$m]['nom'];
						}
						else {
							$info_nom_matiere=$tab_bull['eleve'][$i]['aid_b'][$m]['nom_complet'];
						}

						if((isset($tab_bull['eleve'][$i]['aid_b'][$m]['aid_nom']))&&($tab_bull['eleve'][$i]['aid_b'][$m]['aid_nom']!="")) {
							if($info_nom_matiere!="") {
								$info_nom_matiere.=": ";
							}
							$info_nom_matiere.=$tab_bull['eleve'][$i]['aid_b'][$m]['aid_nom'];
						}
					}
					else {
						if((isset($tab_bull['eleve'][$i]['aid_b'][$m]['nom_complet']))&&($tab_bull['eleve'][$i]['aid_b'][$m]['nom_complet']!="")) {
							$info_nom_matiere=$tab_bull['eleve'][$i]['aid_b'][$m]['nom_complet'];
						}
						else {
							$info_nom_matiere=$tab_bull['eleve'][$i]['aid_b'][$m]['nom'];
						}
					}
					if($info_nom_matiere=="") {
						$info_nom_matiere="AID";
					}

					$hauteur_caractere_matiere=8;
					$cell_ajustee_texte_matiere_ratio_min_max=3;

					// Forcé pour le moment
					$cell_ajustee_texte_matiere=1;
					if((isset($cell_ajustee_texte_matiere))&&($cell_ajustee_texte_matiere==1)) {
						// On met un décalage pour ne pas coller le texte à la bordure
						$Y_decal_cell_ajustee=2;
						$pdf->SetXY($param_bull2016["x_EPI_AP_Parcours"], $y_courant+$Y_decal_cell_ajustee);

						$texte=$info_nom_matiere;
						$taille_max_police=$hauteur_caractere_matiere;
						$taille_min_police=ceil($taille_max_police/$cell_ajustee_texte_matiere_ratio_min_max);

						$largeur_dispo=$param_bull2016["largeur_EPI_AP_Parcours_col_1"];
						$h_cell=$hauteur_EPI_AP_Parcours/2-$Y_decal_cell_ajustee;

						cell_ajustee($texte,$pdf->GetX(),$pdf->GetY(),$largeur_dispo,$h_cell,$taille_max_police,$taille_min_police,'');
		
					}
					else {
						$val = $pdf->GetStringWidth($info_nom_matiere);
						$taille_texte = $param_bull2016["largeur_EPI_AP_Parcours_col_1"]-2;
						$grandeur_texte='test';
						while($grandeur_texte!='ok') {
							if($taille_texte<$val)
							{
								$hauteur_caractere_matiere = $hauteur_caractere_matiere-0.3;
								$pdf->SetFont('DejaVu','B',$hauteur_caractere_matiere);
								$val = $pdf->GetStringWidth($info_nom_matiere);
							}
							else {
								$grandeur_texte='ok';
							}
						}
						$grandeur_texte='test';
						$Y_decal=$y_courant;
						$pdf->SetXY($param_bull2016["x_EPI_AP_Parcours"], $Y_decal);
						$pdf->Cell($param_bull2016["largeur_EPI_AP_Parcours_col_1"], $hauteur_EPI_AP_Parcours/2, ($info_nom_matiere),'',1,'L');
					}


					// On note l'ordonnée pour le nom des professeurs
					$Y_decal = $y_courant+($hauteur_EPI_AP_Parcours/2);
					$pdf->SetXY($param_bull2016["x_EPI_AP_Parcours"], $Y_decal);
					$pdf->SetFont('DejaVu','',8);

					// nom des professeurs
					if ( isset($tab_bull['eleve'][$i]['aid_b'][$m]['aid_prof_resp_login'][0]) )
					{

						// Présentation en ligne des profs
						// On n'a pas forcément le formatage choisi pour la classe...
						//$text_prof=$tab_bull['groupe'][$m]["profs"]["proflist_string"]."  ";
						$text_prof="";
						for($loop_prof_grp=0;$loop_prof_grp<count($tab_bull['eleve'][$i]['aid_b'][$m]['aid_prof_resp_login']);$loop_prof_grp++) {
							$tmp_login_prof=$tab_bull['eleve'][$i]['aid_b'][$m]['aid_prof_resp_login'][$loop_prof_grp];
							if($loop_prof_grp>0) {$text_prof.=", ";}
							$text_prof.=affiche_utilisateur($tmp_login_prof,$tab_bull['eleve'][$i]['id_classe']);
						}

						if($text_prof!="") {
							//$espace_matiere_prof = $espace_entre_matier/2;
							$espace_matiere_prof = $hauteur_EPI_AP_Parcours/2;
							$hauteur_caractere_prof = 7;

							$texte=$text_prof;
							$taille_max_police=$hauteur_caractere_prof;
							$taille_min_police=ceil($hauteur_caractere_prof/3);

							$largeur_dispo=$param_bull2016["largeur_EPI_AP_Parcours_col_1"];
							$h_cell=$espace_matiere_prof;

							$pdf->SetX($param_bull2016["x_EPI_AP_Parcours"]);

							cell_ajustee(($texte),$pdf->GetX(),$pdf->GetY(),$largeur_dispo,$h_cell,$taille_max_police,$taille_min_police,'');
						}
					}


					// Colonne 2 : Appréciation
					$pdf->SetFillColor($param_bull2016["couleur_EPI_alt".($cpt_matiere%2+1)]["R"], $param_bull2016["couleur_EPI_alt".($cpt_matiere%2+1)]["V"], $param_bull2016["couleur_EPI_alt".($cpt_matiere%2+1)]["B"]);
					$pdf->Rect($param_bull2016["x_EPI_AP_Parcours_col_2"], $y_courant, $param_bull2016["largeur_EPI_AP_Parcours_col_2"], $hauteur_EPI_AP_Parcours, 'F');

					$pdf->SetFillColor(0, 0, 0);
					$pdf->SetXY($param_bull2016["x_EPI_AP_Parcours_col_2"], $y_courant);

					// calcul de la taille du texte des appréciations
					$hauteur_caractere_appreciation = 8;
					$pdf->SetFont('DejaVu','',$hauteur_caractere_appreciation);

					//suppression des espaces en début et en fin
					//$app_aff = trim($tab_bull['app'][$m][$i]);
					$app_aff="";
					if($tab_bull['eleve'][$i]['aid_b'][$m]['message']!='') {
						$app_aff.=$tab_bull['eleve'][$i]['aid_b'][$m]['message'];
					}
					//if($app_aff!='') {$app_aff.=" ";}
					if(($tab_bull['eleve'][$i]['aid_b'][$m]['display_nom']=='y')&&($tab_bull['eleve'][$i]['aid_b'][$m]['aid_nom']!='')) {
						if($app_aff!='') {$app_aff.=" ";}
						$app_aff.=$tab_bull['eleve'][$i]['aid_b'][$m]['aid_nom'];
					}
					if($app_aff!='') {$app_aff.="\n";}
					$app_aff.=trim($tab_bull['eleve'][$i]['aid_b'][$m]['aid_appreciation']);

					fich_debug_bull("__________________________________________\n");
					fich_debug_bull("$app_aff\n");
					fich_debug_bull("__________________________________________\n");

					// DEBUT AJUSTEMENT TAILLE APPRECIATION
					$taille_texte_total = $pdf->GetStringWidth($app_aff);
					//$largeur_appreciation2 = $largeur_appreciation - $largeur_sous_matiere;
					$largeur_appreciation2=$param_bull2016["largeur_EPI_AP_Parcours_col_2"];

					if($use_cell_ajustee=="n") {
						//$taille_texte = (($espace_entre_matier/3)*$largeur_appreciation2);
						$nb_ligne_app = '2.8';
						//$nb_ligne_app = '3.8';
						//$nb_ligne_app = '4.8';
						$taille_texte_max = $nb_ligne_app * ($largeur_appreciation2-4);
						//$taille_texte_max = $nb_ligne_app * ($largeur_appreciation2);
						$grandeur_texte='test';

						fich_debug_bull("\$taille_texte_total=$taille_texte_total\n");
						fich_debug_bull("\$largeur_appreciation2=$largeur_appreciation2\n");
						fich_debug_bull("\$nb_ligne_app=$nb_ligne_app\n");
						//fich_debug_bull("\$taille_texte_max = \$nb_ligne_app * (\$largeur_appreciation2-4)=$nb_ligne_app * ($largeur_appreciation2-4)=$taille_texte_max\n");
						fich_debug_bull("\$taille_texte_max = \$nb_ligne_app * (\$largeur_appreciation2)=$nb_ligne_app * ($largeur_appreciation2)=$taille_texte_max\n");

						while($grandeur_texte!='ok') {
							if($taille_texte_max < $taille_texte_total)
							{
								$hauteur_caractere_appreciation = $hauteur_caractere_appreciation-0.3;
								//$hauteur_caractere_appreciation = $hauteur_caractere_appreciation-0.1;
								$pdf->SetFont('DejaVu','',$hauteur_caractere_appreciation);
								$taille_texte_total = $pdf->GetStringWidth($app_aff);
							}
							else {
								$grandeur_texte='ok';
							}
						}
						$grandeur_texte='test';
						$pdf->drawTextBox(($app_aff), $largeur_appreciation2, $hauteur_EPI_AP_Parcours, 'J', 'M', 1);
					}
					else {
						$texte=$app_aff;
						//$texte="Bla bla\nbli ".$app_aff;
						$taille_max_police=$hauteur_caractere_appreciation;
						$taille_min_police=ceil($taille_max_police/3);

						$largeur_dispo=$largeur_appreciation2;
						$h_cell=$hauteur_EPI_AP_Parcours;

						if(getSettingValue('suppr_balises_app_prof')=='y') {$texte=preg_replace('/<(.*)>/U','',$texte);}
						cell_ajustee(($texte),$pdf->GetX(),$pdf->GetY(),$largeur_dispo,$h_cell,$taille_max_police,$taille_min_police,'');
					}

					$y_courant+=$hauteur_EPI_AP_Parcours+0.5;
					$cpt_matiere++;


				}
				// Fin des AID_b tagués Parcours


				// Enseignements de la table 'groupes' tagués Parcours
				for($m=0;$m<count($tab_bull['groupe']);$m++) {
					//if(isset($tab_bull['note'][$m][$i])) {
					// On n'affiche pas ici les groupes correspondant à AP, EPI ou Parcours
					if((isset($tab_bull['note'][$m][$i]))&&
					(isset($tab_bull['groupe'][$m]['type_grp'][0]))&&
					($tab_bull['groupe'][$m]['type_grp'][0]['nom_court']=="Parcours")) {

						// Colonne 1 : Matière, prof

						$pdf->SetFillColor($param_bull2016["couleur_EPI_alt".($cpt_matiere%2+1)]["R"], $param_bull2016["couleur_EPI_alt".($cpt_matiere%2+1)]["V"], $param_bull2016["couleur_EPI_alt".($cpt_matiere%2+1)]["B"]);
						$pdf->Rect($param_bull2016["x_EPI_AP_Parcours"], $y_courant, $param_bull2016["largeur_EPI_AP_Parcours_col_1"], $hauteur_EPI_AP_Parcours, 'F');
						$pdf->SetFillColor(0, 0, 0);

						$pdf->SetTextColor(0, 0, 0);
						$pdf->SetXY($param_bull2016["x_EPI_AP_Parcours"], $y_courant);
						$pdf->SetFont('DejaVu','',8);

						if(getSettingValue('bul_rel_nom_matieres')=='nom_groupe') {
							$info_nom_matiere=$tab_bull['groupe'][$m]['name'];
						}
						elseif(getSettingValue('bul_rel_nom_matieres')=='description_groupe') {
							$info_nom_matiere=$tab_bull['groupe'][$m]['description'];
						}
						else {
							// Pour parer au bug sur la suppression de matière alors que des groupes sont conservés:
							if(isset($tab_bull['groupe'][$m]['matiere']['nom_complet'])) {
								$info_nom_matiere=$tab_bull['groupe'][$m]['matiere']['nom_complet'];
							}
							else {
								$info_nom_matiere=$tab_bull['groupe'][$m]['name']." (".$tab_bull['groupe'][$m]['id'].")";
							}
						}

						$hauteur_caractere_matiere=8;
						$cell_ajustee_texte_matiere_ratio_min_max=3;

						// 20130927 : cell_ajustee() ou pas sur le nom de matière/enseignement
						$cell_ajustee_texte_matiere=1;
						if((isset($cell_ajustee_texte_matiere))&&($cell_ajustee_texte_matiere==1)) {
							// Encadrement
							//$pdf->Cell($tab_modele_pdf["largeur_matiere"][$classe_id], $espace_entre_matier, "",'LRBT',1,'L');

							// cell_ajustee() ne centre pas verticalement le texte.
							// On met un décalage pour ne pas coller le texte à la bordure
							$Y_decal_cell_ajustee=2;
							// On repositionne et on inscrit le nom de matière sur la moitié de la hauteur de la cellule
							//$pdf->SetXY($X_bloc_matiere, $Y_decal+$Y_decal_cell_ajustee);
							//$pdf->SetXY($param_bull2016["x_acquis_col_1"], $y_courant);

							$pdf->SetXY($param_bull2016["x_EPI_AP_Parcours"], $y_courant+$Y_decal_cell_ajustee);

							$texte=$info_nom_matiere;
							$taille_max_police=$hauteur_caractere_matiere;
							//$taille_min_police=ceil($taille_max_police/$tab_modele_pdf["cell_ajustee_texte_matiere_ratio_min_max"][$classe_id]);
							$taille_min_police=ceil($taille_max_police/$cell_ajustee_texte_matiere_ratio_min_max);

							$largeur_dispo=$param_bull2016["largeur_EPI_AP_Parcours_col_1"];
							//$h_cell=$espace_entre_matier/2-$Y_decal_cell_ajustee;
							$h_cell=$hauteur_EPI_AP_Parcours/2-$Y_decal_cell_ajustee;

							//cell_ajustee("<b>".$texte."</b>",$pdf->GetX(),$pdf->GetY(),$largeur_dispo,$h_cell,$taille_max_police,$taille_min_police,'');
							cell_ajustee($texte,$pdf->GetX(),$pdf->GetY(),$largeur_dispo,$h_cell,$taille_max_police,$taille_min_police,'');
		
						}
						else {
							$val = $pdf->GetStringWidth($info_nom_matiere);
							$taille_texte = $param_bull2016["largeur_EPI_AP_Parcours_col_1"]-2;
							$grandeur_texte='test';
							while($grandeur_texte!='ok') {
								if($taille_texte<$val)
								{
									$hauteur_caractere_matiere = $hauteur_caractere_matiere-0.3;
									$pdf->SetFont('DejaVu','',$hauteur_caractere_matiere);
									$val = $pdf->GetStringWidth($info_nom_matiere);
								}
								else {
									$grandeur_texte='ok';
								}
							}
							$grandeur_texte='test';
							$Y_decal=$y_courant;
							$pdf->SetXY($param_bull2016["x_EPI_AP_Parcours"], $Y_decal);
							$pdf->Cell($param_bull2016["largeur_EPI_AP_Parcours_col_1"], $hauteur_EPI_AP_Parcours/2, ($info_nom_matiere),'',1,'L');
						}
		

						// On note l'ordonnée pour le nom des professeurs
						$Y_decal = $y_courant+($hauteur_EPI_AP_Parcours/2);
						$pdf->SetXY($param_bull2016["x_EPI_AP_Parcours"], $Y_decal);
						$pdf->SetFont('DejaVu','',8);

						//fich_debug_bull("\$info_nom_matiere=$info_nom_matiere\n");
						//fich_debug_bull("Le nom de matière est écrit; on est à mi-hauteur de la cellule pour écrire le nom du prof:\n");
						//fich_debug_bull("\$Y_decal=$Y_decal\n");

						// nom des professeurs

						if ( isset($tab_bull['groupe'][$m]["profs"]["list"]) )
						{

							// Présentation en ligne des profs
							// On n'a pas forcément le formatage choisi pour la classe...
							//$text_prof=$tab_bull['groupe'][$m]["profs"]["proflist_string"]."  ";
							$text_prof="";
							for($loop_prof_grp=0;$loop_prof_grp<count($tab_bull['groupe'][$m]["profs"]["list"]);$loop_prof_grp++) {
								$tmp_login_prof=$tab_bull['groupe'][$m]["profs"]["list"][$loop_prof_grp];
								if($loop_prof_grp>0) {$text_prof.=", ";}
								$text_prof.=affiche_utilisateur($tmp_login_prof,$tab_bull['eleve'][$i]['id_classe']);
							}

							if($text_prof!="") {
								//$espace_matiere_prof = $espace_entre_matier/2;
								$espace_matiere_prof = $hauteur_EPI_AP_Parcours/2;
								$hauteur_caractere_prof = 7;

								/*
								if($use_cell_ajustee=="n") {
									$pdf->SetFont('DejaVu','',$hauteur_caractere_prof);
									$val = $pdf->GetStringWidth($text_prof);
									$taille_texte = ($tab_modele_pdf["largeur_matiere"][$classe_id]);
									$grandeur_texte='test';
									while($grandeur_texte!='ok') {
										if($taille_texte<$val)
										{
											$hauteur_caractere_prof = $hauteur_caractere_prof-0.3;
											$pdf->SetFont('DejaVu','',$hauteur_caractere_prof);
											$val = $pdf->GetStringWidth($text_prof);
										}
										else {
											$grandeur_texte='ok';
										}
									}
									$grandeur_texte='test';
									$pdf->SetX($X_bloc_matiere);
									$pdf->Cell($tab_modele_pdf["largeur_matiere"][$classe_id], $espace_matiere_prof, ($text_prof),'LR',1,'L');
								}
								else {
								*/
									$texte=$text_prof;
									$taille_max_police=$hauteur_caractere_prof;
									$taille_min_police=ceil($hauteur_caractere_prof/3);

									$largeur_dispo=$param_bull2016["largeur_EPI_AP_Parcours_col_1"];
									$h_cell=$espace_matiere_prof;

									$pdf->SetX($param_bull2016["x_EPI_AP_Parcours"]);

									cell_ajustee(($texte),$pdf->GetX(),$pdf->GetY(),$largeur_dispo,$h_cell,$taille_max_police,$taille_min_police,'');
								//}
							}
						}


						// Colonne 2 : Appréciation
						$pdf->SetFillColor($param_bull2016["couleur_EPI_alt".($cpt_matiere%2+1)]["R"], $param_bull2016["couleur_EPI_alt".($cpt_matiere%2+1)]["V"], $param_bull2016["couleur_EPI_alt".($cpt_matiere%2+1)]["B"]);
						$pdf->Rect($param_bull2016["x_EPI_AP_Parcours_col_2"], $y_courant, $param_bull2016["largeur_EPI_AP_Parcours_col_2"], $hauteur_EPI_AP_Parcours, 'F');

						$pdf->SetFillColor(0, 0, 0);
						$pdf->SetXY($param_bull2016["x_EPI_AP_Parcours_col_2"], $y_courant);

						// calcul de la taille du texte des appréciations
						$hauteur_caractere_appreciation = 8;
						$pdf->SetFont('DejaVu','',$hauteur_caractere_appreciation);

						//suppression des espaces en début et en fin
						$app_aff = trim($tab_bull['app'][$m][$i]);

						fich_debug_bull("__________________________________________\n");
						fich_debug_bull("$app_aff\n");
						fich_debug_bull("__________________________________________\n");

						// DEBUT AJUSTEMENT TAILLE APPRECIATION
						$taille_texte_total = $pdf->GetStringWidth($app_aff);
						//$largeur_appreciation2 = $largeur_appreciation - $largeur_sous_matiere;
						$largeur_appreciation2=$param_bull2016["largeur_EPI_AP_Parcours_col_2"];

						if($use_cell_ajustee=="n") {
							//$taille_texte = (($espace_entre_matier/3)*$largeur_appreciation2);
							$nb_ligne_app = '2.8';
							//$nb_ligne_app = '3.8';
							//$nb_ligne_app = '4.8';
							$taille_texte_max = $nb_ligne_app * ($largeur_appreciation2-4);
							//$taille_texte_max = $nb_ligne_app * ($largeur_appreciation2);
							$grandeur_texte='test';

							fich_debug_bull("\$taille_texte_total=$taille_texte_total\n");
							fich_debug_bull("\$largeur_appreciation2=$largeur_appreciation2\n");
							fich_debug_bull("\$nb_ligne_app=$nb_ligne_app\n");
							//fich_debug_bull("\$taille_texte_max = \$nb_ligne_app * (\$largeur_appreciation2-4)=$nb_ligne_app * ($largeur_appreciation2-4)=$taille_texte_max\n");
							fich_debug_bull("\$taille_texte_max = \$nb_ligne_app * (\$largeur_appreciation2)=$nb_ligne_app * ($largeur_appreciation2)=$taille_texte_max\n");

							while($grandeur_texte!='ok') {
								if($taille_texte_max < $taille_texte_total)
								{
									$hauteur_caractere_appreciation = $hauteur_caractere_appreciation-0.3;
									//$hauteur_caractere_appreciation = $hauteur_caractere_appreciation-0.1;
									$pdf->SetFont('DejaVu','',$hauteur_caractere_appreciation);
									$taille_texte_total = $pdf->GetStringWidth($app_aff);
								}
								else {
									$grandeur_texte='ok';
								}
							}
							$grandeur_texte='test';
							$pdf->drawTextBox(($app_aff), $largeur_appreciation2, $hauteur_EPI_AP_Parcours, 'J', 'M', 1);
						}
						else {
							$texte=$app_aff;
							//$texte="Bla bla\nbli ".$app_aff;
							$taille_max_police=$hauteur_caractere_appreciation;
							$taille_min_police=ceil($taille_max_police/3);

							$largeur_dispo=$largeur_appreciation2;
							$h_cell=$hauteur_EPI_AP_Parcours;

							if(getSettingValue('suppr_balises_app_prof')=='y') {$texte=preg_replace('/<(.*)>/U','',$texte);}
							cell_ajustee(($texte),$pdf->GetX(),$pdf->GetY(),$largeur_dispo,$h_cell,$taille_max_police,$taille_min_police,'');
						}

						$y_courant+=$hauteur_EPI_AP_Parcours+0.5;
						$cpt_matiere++;

					}
				}

				// Fin des enseignements de la table 'groupes' tagués AP


				// AID_e de type Parcours
				for($loop=0;$loop<count($indice_AID_e_Parcours);$loop++) {
					$m=$indice_AID_e_Parcours[$loop];

					// Colonne 1 : Matière, prof

					$pdf->SetFillColor($param_bull2016["couleur_EPI_alt".($cpt_matiere%2+1)]["R"], $param_bull2016["couleur_EPI_alt".($cpt_matiere%2+1)]["V"], $param_bull2016["couleur_EPI_alt".($cpt_matiere%2+1)]["B"]);
					$pdf->Rect($param_bull2016["x_EPI_AP_Parcours"], $y_courant, $param_bull2016["largeur_EPI_AP_Parcours_col_1"], $hauteur_EPI_AP_Parcours, 'F');
					$pdf->SetFillColor(0, 0, 0);

					$pdf->SetTextColor(0, 0, 0);
					$pdf->SetXY($param_bull2016["x_EPI_AP_Parcours"], $y_courant);
					$pdf->SetFont('DejaVu','',8);

					if($tab_bull['eleve'][$i]['aid_e'][$m]['display_nom']=='z') {
						if((isset($tab_bull['eleve'][$i]['aid_e'][$m]['aid_nom']))&&($tab_bull['eleve'][$i]['aid_e'][$m]['aid_nom']!="")) {
							$info_nom_matiere=$tab_bull['eleve'][$i]['aid_e'][$m]['aid_nom'];
						}
						elseif((isset($tab_bull['eleve'][$i]['aid_e'][$m]['nom_complet']))&&($tab_bull['eleve'][$i]['aid_e'][$m]['nom_complet']!="")) {
							$info_nom_matiere=$tab_bull['eleve'][$i]['aid_e'][$m]['nom_complet'];
						}
						else {
							$info_nom_matiere=$tab_bull['eleve'][$i]['aid_e'][$m]['nom'];
						}
					}
					elseif($tab_bull['eleve'][$i]['aid_e'][$m]['display_nom']=='x') {
						if((isset($tab_bull['eleve'][$i]['aid_e'][$m]['nom']))&&($tab_bull['eleve'][$i]['aid_e'][$m]['nom']!="")) {
							$info_nom_matiere=$tab_bull['eleve'][$i]['aid_e'][$m]['nom'];
						}
						else {
							$info_nom_matiere=$tab_bull['eleve'][$i]['aid_e'][$m]['nom_complet'];
						}

						if((isset($tab_bull['eleve'][$i]['aid_e'][$m]['aid_nom']))&&($tab_bull['eleve'][$i]['aid_e'][$m]['aid_nom']!="")) {
							if($info_nom_matiere!="") {
								$info_nom_matiere.=": ";
							}
							$info_nom_matiere.=$tab_bull['eleve'][$i]['aid_e'][$m]['aid_nom'];
						}
					}
					else {
						if((isset($tab_bull['eleve'][$i]['aid_e'][$m]['nom_complet']))&&($tab_bull['eleve'][$i]['aid_e'][$m]['nom_complet']!="")) {
							$info_nom_matiere=$tab_bull['eleve'][$i]['aid_e'][$m]['nom_complet'];
						}
						else {
							$info_nom_matiere=$tab_bull['eleve'][$i]['aid_e'][$m]['nom'];
						}
					}
					if($info_nom_matiere=="") {
						$info_nom_matiere="AID";
					}

					$hauteur_caractere_matiere=8;
					$cell_ajustee_texte_matiere_ratio_min_max=3;

					// Forcé pour le moment
					$cell_ajustee_texte_matiere=1;
					if((isset($cell_ajustee_texte_matiere))&&($cell_ajustee_texte_matiere==1)) {
						// On met un décalage pour ne pas coller le texte à la bordure
						$Y_decal_cell_ajustee=2;
						$pdf->SetXY($param_bull2016["x_EPI_AP_Parcours"], $y_courant+$Y_decal_cell_ajustee);

						$texte=$info_nom_matiere;
						$taille_max_police=$hauteur_caractere_matiere;
						$taille_min_police=ceil($taille_max_police/$cell_ajustee_texte_matiere_ratio_min_max);

						$largeur_dispo=$param_bull2016["largeur_EPI_AP_Parcours_col_1"];
						$h_cell=$hauteur_EPI_AP_Parcours/2-$Y_decal_cell_ajustee;

						cell_ajustee($texte,$pdf->GetX(),$pdf->GetY(),$largeur_dispo,$h_cell,$taille_max_police,$taille_min_police,'');
		
					}
					else {
						$val = $pdf->GetStringWidth($info_nom_matiere);
						$taille_texte = $param_bull2016["largeur_EPI_AP_Parcours_col_1"]-2;
						$grandeur_texte='test';
						while($grandeur_texte!='ok') {
							if($taille_texte<$val)
							{
								$hauteur_caractere_matiere = $hauteur_caractere_matiere-0.3;
								$pdf->SetFont('DejaVu','B',$hauteur_caractere_matiere);
								$val = $pdf->GetStringWidth($info_nom_matiere);
							}
							else {
								$grandeur_texte='ok';
							}
						}
						$grandeur_texte='test';
						$Y_decal=$y_courant;
						$pdf->SetXY($param_bull2016["x_EPI_AP_Parcours"], $Y_decal);
						$pdf->Cell($param_bull2016["largeur_EPI_AP_Parcours_col_1"], $hauteur_EPI_AP_Parcours/2, ($info_nom_matiere),'',1,'L');
					}


					// On note l'ordonnée pour le nom des professeurs
					$Y_decal = $y_courant+($hauteur_EPI_AP_Parcours/2);
					$pdf->SetXY($param_bull2016["x_EPI_AP_Parcours"], $Y_decal);
					$pdf->SetFont('DejaVu','',8);

					// nom des professeurs
					if ( isset($tab_bull['eleve'][$i]['aid_e'][$m]['aid_prof_resp_login'][0]) )
					{

						// Présentation en ligne des profs
						// On n'a pas forcément le formatage choisi pour la classe...
						//$text_prof=$tab_bull['groupe'][$m]["profs"]["proflist_string"]."  ";
						$text_prof="";
						for($loop_prof_grp=0;$loop_prof_grp<count($tab_bull['eleve'][$i]['aid_e'][$m]['aid_prof_resp_login']);$loop_prof_grp++) {
							$tmp_login_prof=$tab_bull['eleve'][$i]['aid_e'][$m]['aid_prof_resp_login'][$loop_prof_grp];
							if($loop_prof_grp>0) {$text_prof.=", ";}
							$text_prof.=affiche_utilisateur($tmp_login_prof,$tab_bull['eleve'][$i]['id_classe']);
						}

						if($text_prof!="") {
							//$espace_matiere_prof = $espace_entre_matier/2;
							$espace_matiere_prof = $hauteur_EPI_AP_Parcours/2;
							$hauteur_caractere_prof = 7;

							$texte=$text_prof;
							$taille_max_police=$hauteur_caractere_prof;
							$taille_min_police=ceil($hauteur_caractere_prof/3);

							$largeur_dispo=$param_bull2016["largeur_EPI_AP_Parcours_col_1"];
							$h_cell=$espace_matiere_prof;

							$pdf->SetX($param_bull2016["x_EPI_AP_Parcours"]);

							cell_ajustee(($texte),$pdf->GetX(),$pdf->GetY(),$largeur_dispo,$h_cell,$taille_max_police,$taille_min_police,'');
						}
					}


					// Colonne 2 : Appréciation
					$pdf->SetFillColor($param_bull2016["couleur_EPI_alt".($cpt_matiere%2+1)]["R"], $param_bull2016["couleur_EPI_alt".($cpt_matiere%2+1)]["V"], $param_bull2016["couleur_EPI_alt".($cpt_matiere%2+1)]["B"]);
					$pdf->Rect($param_bull2016["x_EPI_AP_Parcours_col_2"], $y_courant, $param_bull2016["largeur_EPI_AP_Parcours_col_2"], $hauteur_EPI_AP_Parcours, 'F');

					$pdf->SetFillColor(0, 0, 0);
					$pdf->SetXY($param_bull2016["x_EPI_AP_Parcours_col_2"], $y_courant);

					// calcul de la taille du texte des appréciations
					$hauteur_caractere_appreciation = 8;
					$pdf->SetFont('DejaVu','',$hauteur_caractere_appreciation);

					//suppression des espaces en début et en fin
					//$app_aff = trim($tab_bull['app'][$m][$i]);
					$app_aff="";
					if($tab_bull['eleve'][$i]['aid_e'][$m]['message']!='') {
						$app_aff.=$tab_bull['eleve'][$i]['aid_e'][$m]['message'];
					}
					//if($app_aff!='') {$app_aff.=" ";}
					if(($tab_bull['eleve'][$i]['aid_e'][$m]['display_nom']=='y')&&($tab_bull['eleve'][$i]['aid_e'][$m]['aid_nom']!='')) {
						if($app_aff!='') {$app_aff.=" ";}
						$app_aff.=$tab_bull['eleve'][$i]['aid_e'][$m]['aid_nom'];
					}
					if($app_aff!='') {$app_aff.="\n";}
					$app_aff.=trim($tab_bull['eleve'][$i]['aid_e'][$m]['aid_appreciation']);

					fich_debug_bull("__________________________________________\n");
					fich_debug_bull("$app_aff\n");
					fich_debug_bull("__________________________________________\n");

					// DEBUT AJUSTEMENT TAILLE APPRECIATION
					$taille_texte_total = $pdf->GetStringWidth($app_aff);
					//$largeur_appreciation2 = $largeur_appreciation - $largeur_sous_matiere;
					$largeur_appreciation2=$param_bull2016["largeur_EPI_AP_Parcours_col_2"];

					if($use_cell_ajustee=="n") {
						//$taille_texte = (($espace_entre_matier/3)*$largeur_appreciation2);
						$nb_ligne_app = '2.8';
						//$nb_ligne_app = '3.8';
						//$nb_ligne_app = '4.8';
						$taille_texte_max = $nb_ligne_app * ($largeur_appreciation2-4);
						//$taille_texte_max = $nb_ligne_app * ($largeur_appreciation2);
						$grandeur_texte='test';

						fich_debug_bull("\$taille_texte_total=$taille_texte_total\n");
						fich_debug_bull("\$largeur_appreciation2=$largeur_appreciation2\n");
						fich_debug_bull("\$nb_ligne_app=$nb_ligne_app\n");
						//fich_debug_bull("\$taille_texte_max = \$nb_ligne_app * (\$largeur_appreciation2-4)=$nb_ligne_app * ($largeur_appreciation2-4)=$taille_texte_max\n");
						fich_debug_bull("\$taille_texte_max = \$nb_ligne_app * (\$largeur_appreciation2)=$nb_ligne_app * ($largeur_appreciation2)=$taille_texte_max\n");

						while($grandeur_texte!='ok') {
							if($taille_texte_max < $taille_texte_total)
							{
								$hauteur_caractere_appreciation = $hauteur_caractere_appreciation-0.3;
								//$hauteur_caractere_appreciation = $hauteur_caractere_appreciation-0.1;
								$pdf->SetFont('DejaVu','',$hauteur_caractere_appreciation);
								$taille_texte_total = $pdf->GetStringWidth($app_aff);
							}
							else {
								$grandeur_texte='ok';
							}
						}
						$grandeur_texte='test';
						$pdf->drawTextBox(($app_aff), $largeur_appreciation2, $hauteur_EPI_AP_Parcours, 'J', 'M', 1);
					}
					else {
						$texte=$app_aff;
						//$texte="Bla bla\nbli ".$app_aff;
						$taille_max_police=$hauteur_caractere_appreciation;
						$taille_min_police=ceil($taille_max_police/3);

						$largeur_dispo=$largeur_appreciation2;
						$h_cell=$hauteur_EPI_AP_Parcours;

						if(getSettingValue('suppr_balises_app_prof')=='y') {$texte=preg_replace('/<(.*)>/U','',$texte);}
						cell_ajustee(($texte),$pdf->GetX(),$pdf->GetY(),$largeur_dispo,$h_cell,$taille_max_police,$taille_min_police,'');
					}

					$y_courant+=$hauteur_EPI_AP_Parcours+0.5;
					$cpt_matiere++;


				}
				// Fin des AID_e tagués Parcours
			}
		}

		//$hauteur_prise_par_EPI_AP_Parcours_page_2=$y_courant;
		//=========================================




	}

	//++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	//++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	//++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

	// 20161231
	// Cadre synthèse de l'évolution des acquis...
	// Peut-être rendre optionnel l'affichage de la synthèse classe... si non vide?
	if(($tab_bull['synthese_classe']!="")&&($tab_bull['synthese_classe']!="-")) {
		// On met un blanc de 5mm au-dessus de la synthèse classe
		$y_courant+=$param_bull2016["espace_vertical_entre_sections_EPI_AP_Parcours"];

		$pdf->SetFillColor($param_bull2016["couleur_bilan_acquisitions"]["R"], $param_bull2016["couleur_bilan_acquisitions"]["V"], $param_bull2016["couleur_bilan_acquisitions"]["B"]);
		$pdf->Rect($param_bull2016["x_bilan_acquisitions"], $y_courant, $param_bull2016["largeur_bilan_acquisitions"], $hauteur_matiere, 'F');

		$pdf->SetFillColor(0, 0, 0);
		$pdf->SetTextColor(0, 0, 0);
		$pdf->SetXY($param_bull2016["x_bilan_acquisitions"], $y_courant);
		$pdf->SetFont('DejaVu','B',9);
		//$pdf->Cell($param_bull2016["largeur_bilan_acquisitions"],$hauteur_matiere, "Synthèse classe : ".$tab_bull['synthese_classe'],0,2,'L');
		$titre_synthese="Synthèse classe : ";
		$largeur_titre_synthese=$pdf->GetStringWidth($titre_synthese);
		$pdf->Cell($param_bull2016["largeur_bilan_acquisitions"],$hauteur_matiere, $titre_synthese,0,2,'L');

		//$texte="<b>Synthèse classe :</b> ".$tab_bull['synthese_classe'];
		$texte=$tab_bull['synthese_classe'];

		$taille_max_police=9;
		$taille_min_police=ceil($taille_max_police/3);

		$largeur_dispo=$param_bull2016["largeur_bilan_acquisitions"]-max($largeur_titre_synthese, $param_bull2016["x_acquis_col_appreciation_app_classe"]);
		$h_cell=$hauteur_matiere-2;

		$pdf->SetXY(max($param_bull2016["x_bilan_acquisitions"]+$largeur_titre_synthese, $param_bull2016["x_acquis_col_appreciation_app_classe"]), $y_courant+1);

		cell_ajustee(($texte),$pdf->GetX(),$pdf->GetY(),$largeur_dispo,$h_cell,$taille_max_police,$taille_min_police,'');
	}

}

// Par défaut, cycle courant de l'élève
function bulletin_pdf_bilan_cycle($tab_bull, $i, $num_resp_bull="", $cycle="") {
	//echo "DEBUG";
	global
		//==============
		//Ajout J.Etheve
		//$coefficients_a_1,
		//==============
		//============================================
		// Paramètres généraux:
		// En admin, dans Gestion générale/Configuration générale
		//$gepi_prof_suivi,
		$gepi_cpe_suivi,
		$RneEtablissement,
		$gepiSchoolAcademie,
		$gepiSchoolName,
		$gepiSchoolAdress1,
		$gepiSchoolAdress2,
		$gepiSchoolZipCode,
		$gepiSchoolCity,
		$gepiSchoolPays,
		$gepiSchoolTel,
		$gepiSchoolFax,
		$gepiSchoolEmail,
		$gepiYear,

		$logo_etab,

		$bull_intitule_app,

		$bull_formule_bas,

		// Paramètre transmis depuis la page d'impression des bulletins
		$un_seul_bull_par_famille,
		$seulement_resp_2_autre_adresse,

		$compteur_bulletins,

		// Datation du bulletin (paramètre initié dans l'entête du bulletin PDF)
		$date_bulletin,

		// Paramètres du modèle PDF
		$tab_modele_pdf,

		$use_cell_ajustee,

		// Pour permettre de récupérer via global dans releve_pdf() le numéro du parent dont on imprime le bulletin avec au verso le relevé de notes:
		//$num_resp_bull, // Cela pose pb...

		// Pour récupérer le 1 relevé par page en verso du bulletin... variable récupérée via 'global' dans la fonction releve_pdf()
		$nb_releve_par_page,

		//20100615
		//$moyennes_periodes_precedentes,
		//$evolution_moyenne_periode_precedente,

		//$avec_coches_mentions,
		$gepi_denom_mention,

		$signature_bull,

		//$tab_engagements,

		$tab_domaine_bilan_socle,
		$tab_domaine_bilan_socle_description,

		// Pour imprimer le bulletin_pdf() de tel responsable uniquement
		// (pour l'envoi par mail avec la bonne adresse associée dans le cas de l'archivage des bulletins PDF)
		$tmp_num_resp_destinataire,

		// Objet PDF initié hors de la présente fonction donnant la page du bulletin pour un élève
		$pdf;

	//global $intercaler_app_classe;

	global $param_bull2016;
	global $gepiPath;
	global $tab_mef;

	/*
	echo "<pre>";
	print_r($tab_bull);
	echo "</pre>";
	*/

	//=========================================

	// Inutile dans le bulletin 2016
	if (isset($GLOBALS['multisite']) AND $GLOBALS['multisite'] == 'y') {
		// On récupère le RNE de l'établissement
		$rep_photos="../photos/".$_COOKIE['RNE']."/eleves/";
	}else{
		$rep_photos="../photos/eleves/";
	}

	//=====================================
	/*
	// NE PAS SUPPRIMER CETTE SECTION... c'est pour le debug

	// Règles en rouge:
	// Selon ce que l'on souhaite débugger, décommenter une des deux règles
	$pdf->SetDrawColor(255,0,0);
	//=====================================
	// Règle 1: horizontale
	$tmp_marge_gauche=5;
	$tmp_marge_haut=5;
	$x=$tmp_marge_gauche;
	$y=$tmp_marge_haut;

	$pdf->SetXY($x,$y);
	$pdf->Cell(200,1,'','T',0,'C',0);

	for($loop=0;$loop<19;$loop++) {
		$x=$tmp_marge_gauche+$loop*10;
		$pdf->SetXY($x,$y);
		$pdf->Cell(5,20,''.$loop,'',0,'L',0);
		$pdf->SetXY($x,$y);
		$pdf->Cell(10,270,'','L',0,'C',0);

		for($loop2=0;$loop2<10;$loop2++) {
			$pdf->SetXY($x+$loop2,$y);
			$pdf->Cell(10,5,'','L',0,'C',0);
		}
	}
	//=====================================
	// Règle 2: verticale
	$tmp_marge_gauche=1;
	$tmp_marge_haut=0;
	$x=$tmp_marge_gauche;
	$y=$tmp_marge_haut;

	$pdf->SetFont('DejaVu','',5);

	// Ligne verticale
	$pdf->SetXY($x,$y);
	$pdf->Cell(1,280,'','L',0,'C',0);

	for($loop=1;$loop<29;$loop++) {
		// Repère numérique en cm
		$y=$tmp_marge_haut+$loop*10-3;
		$pdf->SetXY($x,$y);
		$pdf->Cell(10,5,''.$loop,'',0,'L',0);

		// Ligne tous les centimètres
		$y=$tmp_marge_haut+$loop*10;
		$pdf->SetXY($x,$y);
		$pdf->Cell(200,10,'','T',0,'C',0);

		// Les millimètres
		for($loop2=0;$loop2<10;$loop2++) {
			$pdf->SetXY($x,$y-10+$loop2);
			$pdf->Cell(2,10,'','T',0,'C',0);
		}
	}
	//=====================================
	// Retour au noir pour les tracés qui suivent:
	$pdf->SetDrawColor(0,0,0);
	*/
	//=====================================
	$nb_bulletins=1;

	//=====================================
	// Préparation des lignes d'adresse

	// 20170714
	// Cas standard d'impression
	$tab_adr_lignes=array();
	foreach($tab_bull['eleve'][$i]['resp']["adresses"]["adresse"] as $tmp_num_resp => $current_resp_adr) {
		$tab_adr_lignes[]=$current_resp_adr["adresse"];
	}
	$nb_bulletins=$tab_bull['eleve'][$i]['resp']["adresses"]["nb_adr"];

	// Impression avec désignation du responsable (archivage bulletins PDF ou autre)
	if((isset($tmp_num_resp_destinataire))&&(preg_match("/^[0-9]{1,}$/", $tmp_num_resp_destinataire))&&(isset($tab_bull['eleve'][$i]['resp']["adresses"]["adresse"][$tmp_num_resp_destinataire]))) {
		// On imprime pour un responsable particulier (éventuellement un couple à la même adresse)
		$tab_adr_lignes=array();
		$tab_adr_lignes[$tmp_num_resp_destinataire]=$tab_bull['eleve'][$i]['resp']["adresses"]["adresse"][$tmp_num_resp_destinataire]["adresse"];

		$nb_bulletins=1;
	}


	if((isset($seulement_resp_2_autre_adresse))&&($seulement_resp_2_autre_adresse=='oui')&&(isset($tab_bull['eleve'][$i]['resp']["adresses"]["adresse"][2]))) {
		// On imprime pour le responsable n°2 seulement
		$tab_adr_lignes=array();
		$tab_adr_lignes[2]=$tab_bull['eleve'][$i]['resp']["adresses"]["adresse"][2]["adresse"];
		$tmp_num_resp_destinataire=2;

		$nb_bulletins=1;
	}

	//=========================================

	// Dans le cas d'un recto/verso, il ne faut retenir que le bulletin correspondant au responsable courant, ne pas boucler sur les responsables.
	//$nb_bulletins=1;
	$num_resp_ini=0;
	$num_resp_fin=$nb_bulletins;
	if((isset($num_resp_bull))&&(preg_match("/^[0-9]{1,}$/", $num_resp_bull))) {
		$num_resp_ini=$num_resp_bull;
		$num_resp_fin=$num_resp_bull+1;
	}

	for($num_resp_bull=$num_resp_ini;$num_resp_bull<$num_resp_fin;$num_resp_bull++) {
		$pdf->AddPage(); //ajout d'une page au document
		$pdf->SetFont('DejaVu');


		$pdf->SetFillColor($param_bull2016["couleur_fond_bilan_cycle"]["R"], $param_bull2016["couleur_fond_bilan_cycle"]["V"], $param_bull2016["couleur_fond_bilan_cycle"]["B"]);
		$pdf->Rect(0, 0, LargeurPage, HauteurPage, 'F');
		$pdf->SetFillColor(0, 0, 0);


		$hauteur_pris=0;

		//=========================================
		// Récupération de l'identifiant de la classe:
		$classe_id=$tab_bull['eleve'][$i]['id_classe'];
		//=========================================

		//=========================================

		// Définition du $cycle par défaut, mais il est modifié/adapté/identifié plus bas d'après le MEF de l'élève
		$cycle=3;

		//=========================================

		// Cadre Logo RF

		$pdf->SetFillColor(255, 255, 255);
		//$pdf->Rect($param_bull2016["x_cadre_logo_RF"], $param_bull2016["y_cadre_logo_RF"], $param_bull2016["largeur_cadre_logo_RF"], $param_bull2016["hauteur_cadre_logo_RF"], 'D');
		$pdf->Rect($param_bull2016["x_cadre_logo_RF"], $param_bull2016["y_cadre_logo_RF"], $param_bull2016["largeur_cadre_logo_RF"], $param_bull2016["hauteur_cadre_logo_RF"], 'F');

		//$logo_RF=$gepiPath."/images/logo_RF.jpg";
		$logo_RF="../images/logo_RF.jpg";
		$valeur=redimensionne_image($logo_RF, 18*3.2, 10*3.2);
		//$X_logo = $param_bull2016["x_logo_RF"];
		//$Y_logo = $param_bull2016["y_logo_RF"];
		$L_logo = $valeur[0];
		$H_logo = $valeur[1];

		$X_logo=$param_bull2016["x_cadre_logo_RF"]+($param_bull2016["largeur_cadre_logo_RF"]-$L_logo)/2;
		$Y_logo=$param_bull2016["y_cadre_logo_RF"]+($param_bull2016["hauteur_cadre_logo_RF"]-$H_logo)/2;

		// centrage du logo
		//$centre_du_logo = ( $H_logo / 2 );
		//$Y_logo = $tab_modele_pdf["Y_centre_logo"][$classe_id] - $centre_du_logo;

		//logo
		$tmp_dim_photo=getimagesize($logo_RF);
		if((isset($tmp_dim_photo[2]))&&($tmp_dim_photo[2]==2)) {
			$pdf->Image($logo_RF, $X_logo, $Y_logo, $L_logo, $H_logo);
		}

		//=========================================

		// Cadre EN

		$pdf->SetFillColor(255, 255, 255);
		//$pdf->Rect($param_bull2016["x_cadre_EN"], $param_bull2016["y_cadre_EN"], $param_bull2016["largeur_cadre_logo_RF"], $param_bull2016["hauteur_cadre_logo_RF"], 'D');
		$pdf->Rect($param_bull2016["x_cadre_EN"], $param_bull2016["y_cadre_EN"], $param_bull2016["largeur_cadre_logo_RF"], $param_bull2016["hauteur_cadre_logo_RF"], 'F');

		$pdf->SetXY($param_bull2016["x_cadre_EN"]+2.5, $param_bull2016["y_cadre_EN"]+1);
		$pdf->SetFont('DejaVu','',6);
		$pdf->SetTextColor(0,0,0);
		$texte="MINISTÈRE\nDE L'ÉDUCATION\nNATIONALE, DE\nL'ENSEIGNEMENT\nSUPÉRIEUR ET DE\nLA RECHERCHE";
		//$pdf->Cell($param_bull2016["largeur_cadre_logo_RF"]-8, $param_bull2016["hauteur_cadre_EN"]-6, $texte,0,2,'C');
		$pdf->drawTextBox($texte, $param_bull2016["largeur_cadre_logo_RF"]-5, $param_bull2016["hauteur_cadre_EN"]-4, 'C', 'M', 0);

		//=========================================

		// Section Académie, établissement, adresse étab

		$pdf->SetXY($param_bull2016["x_cadre_etab"], $param_bull2016["y_cadre_etab_academie"]);
		$pdf->SetFont('DejaVu','',9);
		$pdf->SetTextColor(0,0,0);
		$pdf->Cell($param_bull2016["largeur_cadre_etab"],7, "Académie de ".$gepiSchoolAcademie,0,2,'L');

		$pdf->SetXY($param_bull2016["x_cadre_etab"], $param_bull2016["y_cadre_etab_college"]);
		$pdf->SetFont('DejaVu','B',10);
		$pdf->SetTextColor(0,0,0);
		$pdf->Cell($param_bull2016["largeur_cadre_etab"],7, $gepiSchoolName,0,2,'L');

		$pdf->SetXY($param_bull2016["x_cadre_etab"], $param_bull2016["y_cadre_etab_adresse_college"]);
		$pdf->SetFont('DejaVu','',7);
		$pdf->SetTextColor(0,0,0);
		$adresse_etab="";
		if($gepiSchoolAdress1!="") {
			$adresse_etab=$gepiSchoolAdress1;
			if($gepiSchoolAdress2!="") {
				$adresse_etab.=", ".$gepiSchoolAdress2;
			}
		}
		elseif($gepiSchoolAdress2!="") {
			$adresse_etab=$gepiSchoolAdress2;
		}
		$pdf->Cell($param_bull2016["largeur_cadre_etab"], 7, $adresse_etab, 0, 2, 'L');

		$pdf->SetXY($param_bull2016["x_cadre_etab"], $param_bull2016["y_cadre_etab_cp_commune_college"]);
		$pdf->SetFont('DejaVu','',7);
		$pdf->SetTextColor(0,0,0);
		$cp_commune_etab="";
		if($gepiSchoolZipCode!="") {
			$cp_commune_etab=$gepiSchoolZipCode;
			if($gepiSchoolCity!="") {
				$cp_commune_etab.=" ".$gepiSchoolCity;
			}
		}
		elseif($gepiSchoolCity!="") {
			$cp_commune_etab=$gepiSchoolCity;
		}
		$pdf->Cell($param_bull2016["largeur_cadre_etab"], 7, $cp_commune_etab, 0, 2, 'L');

		$pdf->SetXY($param_bull2016["x_cadre_etab"], $param_bull2016["y_cadre_etab_tel_college"]);
		$pdf->SetFont('DejaVu','',7);
		$pdf->SetTextColor(0,0,0);
		$tel_etab="";
		if($gepiSchoolTel!="") {
			$tel_etab=$gepiSchoolTel;
			if($gepiSchoolFax!="") {
				$tel_etab.=" (fax:".$gepiSchoolFax.")";
			}
		}
		elseif($gepiSchoolFax!="") {
			$tel_etab="(fax:".$gepiSchoolFax.")";
		}
		$pdf->Cell($param_bull2016["largeur_cadre_etab"], 7, $tel_etab, 0, 2, 'L');

		$pdf->SetXY($param_bull2016["x_cadre_etab"], $param_bull2016["y_cadre_etab_email_college"]);
		$pdf->SetFont('DejaVu','',7);
		$pdf->SetTextColor(0,0,0);
		$pdf->Cell($param_bull2016["largeur_cadre_etab"], 7, $gepiSchoolEmail, 0, 2, 'L');

		//=========================================

		// Section cycle et niveau
		// Debug
		/*
		echo "<pre>";
		print_r($tab_mef);
		echo "</pre>";
		echo "\$tab_bull['eleve'][$i]['mef_code']=".$tab_bull['eleve'][$i]['mef_code']."<br />";
		*/

		$cycle=$tab_bull['eleve'][$i]['mef_cycle'];
		$niveau=$tab_bull['eleve'][$i]['mef_niveau'];
		// Debug
		//echo "cycle=$cycle et niveau=$niveau<br />";

		// Colonne cycle:
		for($loop_cycle=2;$loop_cycle<=4;$loop_cycle++) {
			if($loop_cycle==$cycle) {
				$pdf->SetFillColor($param_bull2016["couleur_cycle_courant"]["cycle"]["R"], $param_bull2016["couleur_cycle_courant"]["cycle"]["V"], $param_bull2016["couleur_cycle_courant"]["cycle"]["B"]);
			}
			else {
				$pdf->SetFillColor($param_bull2016["couleur_cycle_autre"]["R"], $param_bull2016["couleur_cycle_autre"]["V"], $param_bull2016["couleur_cycle_autre"]["B"]);
			}
			$pdf->Rect($param_bull2016["x_colonne_cycle"], $param_bull2016["y_colonne_cycle"]+($loop_cycle-2)*($param_bull2016["cote_carre_cycle"]+$param_bull2016["ecart_carres_cycle"]), $param_bull2016["cote_carre_cycle"], $param_bull2016["cote_carre_cycle"], 'F');
			$pdf->SetFillColor(0, 0, 0);

			$pdf->SetXY($param_bull2016["x_colonne_cycle"], $param_bull2016["y_colonne_cycle"]+($loop_cycle-2)*($param_bull2016["cote_carre_cycle"]+$param_bull2016["ecart_carres_cycle"]));
			$pdf->SetFont('DejaVu','',14);
			$pdf->SetTextColor(255,255,255);
			$pdf->Cell($param_bull2016["cote_carre_cycle"],$param_bull2016["cote_carre_cycle"], $loop_cycle,0,2,'C');

		}

		// Colonne correspondant à $loop_niveau et Ligne à $loop_cycle
		$tab_coord_niveau["cp"]=array("col"=>0, "lig"=>2);
		$tab_coord_niveau["ce1"]=array("col"=>1, "lig"=>2);
		$tab_coord_niveau["ce2"]=array("col"=>2, "lig"=>2);
		$tab_coord_niveau["cm1"]=array("col"=>0, "lig"=>3);
		$tab_coord_niveau["cm2"]=array("col"=>1, "lig"=>3);
		$tab_coord_niveau["6"]=array("col"=>2, "lig"=>3);
		$tab_coord_niveau["5"]=array("col"=>0, "lig"=>4);
		$tab_coord_niveau["4"]=array("col"=>1, "lig"=>4);
		$tab_coord_niveau["3"]=array("col"=>2, "lig"=>4);
/*
echo "<br /><p>DEBUG : ".$tab_bull['eleve'][$i]["login"]."<br />";
echo "Cycle=$cycle et niveau=$niveau<br />";
echo "<pre>";
print_r($tab_coord_niveau[$niveau]);
echo "</pre>";
*/
		// Colonnes niveau:
		for($loop_cycle=2;$loop_cycle<=4;$loop_cycle++) {
			for($loop_niveau=0;$loop_niveau<3;$loop_niveau++) {
				//if(($loop_cycle==$cycle)&&(isset($param_bull2016["cycles_et_niveaux"][$loop_cycle][$loop_niveau]["texte"]))&&($param_bull2016["cycles_et_niveaux"][$loop_cycle][$loop_niveau]["texte"]==$niveau)) {

//echo "loop_niveau=".$loop_niveau." et loop_cycle=".$loop_cycle."<br />";

				if((isset($tab_coord_niveau[$niveau]))&&
				//($tab_coord_niveau[$niveau]["col"]==$loop_niveau)&&
				($tab_coord_niveau[$niveau]["lig"]==$loop_cycle)) {
//echo "On y est.<br />";

					$pdf->SetFillColor($param_bull2016["couleur_cycle_courant"]["niveau"]["R"], $param_bull2016["couleur_cycle_courant"]["niveau"]["V"], $param_bull2016["couleur_cycle_courant"]["niveau"]["B"]);
				}
				else {
					$pdf->SetFillColor($param_bull2016["couleur_cycle_autre"]["R"], $param_bull2016["couleur_cycle_autre"]["V"], $param_bull2016["couleur_cycle_autre"]["B"]);
				}
				$pdf->Rect($param_bull2016["x_colonne_cycle"]+($loop_niveau+1)*($param_bull2016["cote_carre_cycle"]+$param_bull2016["ecart_carres_cycle"]), $param_bull2016["y_colonne_cycle"]+($loop_cycle-2)*($param_bull2016["cote_carre_cycle"]+$param_bull2016["ecart_carres_cycle"]), $param_bull2016["cote_carre_cycle"], $param_bull2016["cote_carre_cycle"], 'F');
				$pdf->SetFillColor(0, 0, 0);

				$pdf->SetXY($param_bull2016["x_colonne_cycle"]+($loop_niveau+1)*($param_bull2016["cote_carre_cycle"]+$param_bull2016["ecart_carres_cycle"]), $param_bull2016["y_colonne_cycle"]+($loop_cycle-2)*($param_bull2016["cote_carre_cycle"]+$param_bull2016["ecart_carres_cycle"]));
				$pdf->SetFont('DejaVu','',14);
				$pdf->SetTextColor(255,255,255);
				$pdf->Cell($param_bull2016["cote_carre_cycle"],$param_bull2016["cote_carre_cycle"], $param_bull2016["cycles_et_niveaux"][$loop_cycle][$loop_niveau]["texte"],0,2,'C');
			}
		}


		//=========================================

		// Cadre Logo établissement

		$logo = '../images/'.getSettingValue('logo_etab');
		$format_du_logo = mb_strtolower(str_replace('.','',strstr(getSettingValue('logo_etab'), '.')));

		// Logo
		//if($tab_modele_pdf["affiche_logo_etab"][$classe_id]==='1' and file_exists($logo) and getSettingValue('logo_etab') != '' and ($format_du_logo==='jpg' or $format_du_logo==='png')) {
		if($param_bull2016["affiche_logo_etab"]==1 and file_exists($logo) and getSettingValue('logo_etab') != '' and ($format_du_logo==='jpg' or $format_du_logo==='png')) {
			$valeur=redimensionne_image($logo, ($param_bull2016["largeur_max_logo_etab"]*2.8), ($param_bull2016["hauteur_max_logo_etab"]*2.8));
			$X_logo = $param_bull2016["x_logo_etab"];
			$Y_logo = $param_bull2016["y_logo_etab"];
			$L_logo = $valeur[0];
			$H_logo = $valeur[1];
			//$X_etab = $X_logo + $L_logo + 1;
			//$Y_etab = $Y_logo;

			/*
			if ( !isset($tab_modele_pdf["centrage_logo"][$classe_id]) or empty($tab_modele_pdf["centrage_logo"][$classe_id]) ) {
				$tab_modele_pdf["centrage_logo"][$classe_id] = '0';
			}

			if ( $tab_modele_pdf["centrage_logo"][$classe_id] === '1' ) {
				// centrage du logo
				$centre_du_logo = ( $H_logo / 2 );
				$Y_logo = $tab_modele_pdf["Y_centre_logo"][$classe_id] - $centre_du_logo;
			}
			*/

			//logo
			$tmp_dim_photo=getimagesize($logo);
			if((isset($tmp_dim_photo[2]))&&($tmp_dim_photo[2]==2)) {
				$pdf->Image($logo, $X_logo, $Y_logo, $L_logo, $H_logo);
			}
		}

		//=========================================

		// Cadre Année, période, identité élève, pp et classe
		$pdf->SetFillColor($param_bull2016["couleur_cadre_identite"]["R"], $param_bull2016["couleur_cadre_identite"]["V"], $param_bull2016["couleur_cadre_identite"]["B"]);

		$pdf->Rect($param_bull2016["x_cadre_eleve"], $param_bull2016["y_cadre_eleve"], $param_bull2016["largeur_cadre_eleve"], $param_bull2016["hauteur_cadre_eleve"], 'F');

		if($param_bull2016["afficher_cadre_adresse_resp"]=="y") {
			$largeur_cadre_eleve=$param_bull2016["x_cadre_eleve"]+$param_bull2016["largeur_cadre_eleve"]-$param_bull2016["x_cadre_adresse_resp"];
		}
		else {
			$largeur_cadre_eleve=$param_bull2016["largeur_cadre_eleve"];
		}

		$pdf->SetFillColor(0, 0, 0);

		$pdf->SetXY($param_bull2016["x_cadre_eleve"], $param_bull2016["y_annee_scolaire"]);
		$pdf->SetFont('DejaVu','B',10);
		$pdf->SetTextColor(0,0,0);
		$pdf->Cell($largeur_cadre_eleve,7, "Année scolaire ".$gepiYear,0,2,'C');

		$pdf->SetXY($param_bull2016["x_cadre_eleve"], $param_bull2016["y_periode"]);
		$pdf->SetFont('DejaVu','',8);
		$pdf->SetTextColor(0,0,0);
		//$pdf->Cell($param_bull2016["largeur_cadre_eleve"],7, "Bilan trimestriel du cycle ".$num_cycle." - ".$tab_bull['num_periode']." trimestre",0,2,'C');
		if($tab_bull['nb_periodes']==2) {
			$trimestriel_ou_semestriel="semestriel";
		}
		else {
			$trimestriel_ou_semestriel="trimestriel";
		}
		$pdf->Cell($largeur_cadre_eleve,7, "Bilan ".$trimestriel_ou_semestriel." du cycle ".$cycle." - ".$tab_bull['nom_periode'],0,2,'C');

		$pdf->SetXY($param_bull2016["x_cadre_eleve"], $param_bull2016["y_nom_prenom_eleve"]);
		$pdf->SetFont('DejaVu','B',12);
		$pdf->SetTextColor(0,0,0);
		$pdf->Cell($largeur_cadre_eleve,7, $tab_bull['eleve'][$i]['prenom']." ".$tab_bull['eleve'][$i]['nom'],0,2,'C');

		$info_naissance="Né";
		if($tab_bull['eleve'][$i]['sexe']=="F") {$info_naissance.="e";}
		$info_naissance.=" le ".$tab_bull['eleve'][$i]['naissance'];
		/*
		if((getSettingValue('ele_lieu_naissance')=='y')&&($tab_modele_pdf["affiche_lieu_naissance"][$classe_id]==='1')) {
			$info_naissance.=" à ".$tab_bull['eleve'][$i]['lieu_naissance'];
		}
		*/

		$info_ligne_2_eleve=$info_naissance;
		if($param_bull2016["bull2016_INE"]=="y") {
			$info_ligne_2_eleve.=" - INE : ".$tab_bull['eleve'][$i]['no_gep'];
		}

		$pdf->SetXY($param_bull2016["x_cadre_eleve"], $param_bull2016["y_naissance_eleve"]);
		$pdf->SetFont('DejaVu','',8);
		$pdf->Cell($largeur_cadre_eleve,7, $info_ligne_2_eleve,0,2,'C');


		//if($tab_modele_pdf["afficher_tous_profprincipaux"][$classe_id]==1) {
			$index_pp='pp_classe';
		/*
		}
		else {
			$index_pp='pp';
		}
		*/
		if(isset($tab_bull['eleve'][$i][$index_pp][0]['login'])) {
			$pp_classe[$i]=ucfirst($tab_bull['gepi_prof_suivi'])." : ";
			$pp_classe[$i].=affiche_utilisateur($tab_bull['eleve'][$i][$index_pp][0]['login'],$tab_bull['eleve'][$i]['id_classe']);
			for($i_pp=1;$i_pp<count($tab_bull['eleve'][$i][$index_pp]);$i_pp++) {
				$pp_classe[$i].=", ";
				$pp_classe[$i].=affiche_utilisateur($tab_bull['eleve'][$i][$index_pp][$i_pp]['login'],$tab_bull['eleve'][$i]['id_classe']);
			}
		}
		else {
			$pp_classe[$i]="";
		}
		$pdf->SetXY($param_bull2016["x_cadre_eleve"], $param_bull2016["y_pp"]);
		$pdf->Cell($largeur_cadre_eleve,7, $pp_classe[$i],0,2,'C');

		$pdf->SetXY($param_bull2016["x_cadre_eleve"], $param_bull2016["y_classe"]);
		$pdf->SetFont('DejaVu','',11);
		$pdf->Cell($largeur_cadre_eleve,7, "Classe de ".unhtmlentities($tab_bull['eleve'][$i]['classe']),0,2,'C');

		//=========================================
		if($param_bull2016["afficher_cadre_adresse_resp"]=="y") {
			// 20161013

			$texte="Adresse manquante";
			if((isset($tmp_num_resp_destinataire))&&(preg_match("/^[0-9]{1,}$/", $tmp_num_resp_destinataire))&&(isset($tab_adr_lignes[$tmp_num_resp_destinataire]))) {
				$texte=$tab_adr_lignes[$tmp_num_resp_destinataire];
			}
			elseif(isset($tab_adr_lignes[$num_resp_bull])) {
				$texte=$tab_adr_lignes[$num_resp_bull];
			}
			$taille_max_police=10;
			//$taille_max_police=$tab_modele_pdf["adresse_resp_fontsize"][$classe_id];
			$taille_min_police=ceil($taille_max_police/3);

			$largeur_dispo=$param_bull2016["largeur_cadre_adresse_resp"];
			$h_cell=$param_bull2016["hauteur_cadre_adresse_resp"];

			cell_ajustee($texte, $param_bull2016["x_cadre_adresse_resp"], $param_bull2016["y_cadre_adresse_resp"], $largeur_dispo, $h_cell, $taille_max_police, $taille_min_police, $param_bull2016["bordure_cadre_adresse_resp"],'C','L',0.3,1);

		}
		//=========================================

		// Bandeau Maitrise des composantes

		$pdf->SetFillColor($param_bull2016["couleur_bandeau_maitrise_composantes_socle"]["R"], $param_bull2016["couleur_bandeau_maitrise_composantes_socle"]["V"], $param_bull2016["couleur_bandeau_maitrise_composantes_socle"]["B"]);

		$pdf->Rect($param_bull2016["x_bandeau_maitrise_composantes_socle"], $param_bull2016["y_bandeau_maitrise_composantes_socle"], $param_bull2016["largeur_bandeau_maitrise_composantes_socle"], $param_bull2016["hauteur_bandeau_maitrise_composantes_socle"], 'F');

		$pdf->SetFillColor(0, 0, 0);
		$pdf->SetTextColor(255, 255, 255);
		$pdf->SetXY($param_bull2016["x_bandeau_maitrise_composantes_socle"], $param_bull2016["y_bandeau_maitrise_composantes_socle"]+1);
		$pdf->SetFont('DejaVu','B',12);
		$pdf->Cell($param_bull2016["largeur_bandeau_maitrise_composantes_socle"],7, "Maîtrise des composantes du socle en fin de cycle ".$cycle,0,2,'C');

		//=========================================

		// Tableau Maîtrise des composantes du socle en fin de cycle N

		$y_tableau_maitrise=$param_bull2016["y_bandeau_maitrise_composantes_socle"]+$param_bull2016["hauteur_bandeau_maitrise_composantes_socle"]+5;
		$y_courant=$y_tableau_maitrise;


		// Ligne d'entête du tableau
		$cpt=0;

		$x_courant=$param_bull2016["x_MI_bilan_cycle"];
		$pdf->SetFillColor($param_bull2016["couleur_".($cpt%2+1)."_MI_bilan_cycle"]["R"], $param_bull2016["couleur_".($cpt%2+1)."_MI_bilan_cycle"]["V"], $param_bull2016["couleur_".($cpt%2+1)."_MI_bilan_cycle"]["B"]);
		$pdf->SetDrawColor(255, 255, 255);
		$pdf->Rect($x_courant, $y_courant, $param_bull2016["largeur_col_niveau_maitrise_composantes_socle"], $param_bull2016["hauteur_ligne_maitrise_bilan_cycle"], 'DF');
		$pdf->SetFillColor(0, 0, 0);
		$pdf->SetTextColor(0, 0, 0);
		$pdf->SetXY($x_courant, $y_courant);
		$pdf->SetFont('DejaVu','',7);
		$pdf->Cell($param_bull2016["largeur_col_niveau_maitrise_composantes_socle"],$param_bull2016["hauteur_ligne_maitrise_bilan_cycle"], "Maîtrise insuffisante",0,2,'C');

		$x_courant=$param_bull2016["x_MF_bilan_cycle"];
		$pdf->SetFillColor($param_bull2016["couleur_".($cpt%2+1)."_MF_bilan_cycle"]["R"], $param_bull2016["couleur_".($cpt%2+1)."_MF_bilan_cycle"]["V"], $param_bull2016["couleur_".($cpt%2+1)."_MF_bilan_cycle"]["B"]);
		$pdf->SetDrawColor(255, 255, 255);
		$pdf->Rect($x_courant, $y_courant, $param_bull2016["largeur_col_niveau_maitrise_composantes_socle"], $param_bull2016["hauteur_ligne_maitrise_bilan_cycle"], 'DF');
		$pdf->SetFillColor(0, 0, 0);
		$pdf->SetTextColor(0, 0, 0);
		$pdf->SetXY($x_courant, $y_courant);
		$pdf->Cell($param_bull2016["largeur_col_niveau_maitrise_composantes_socle"],$param_bull2016["hauteur_ligne_maitrise_bilan_cycle"], "Maîtrise fragile",0,2,'C');

		$x_courant=$param_bull2016["x_MS_bilan_cycle"];
		$pdf->SetFillColor($param_bull2016["couleur_".($cpt%2+1)."_MS_bilan_cycle"]["R"], $param_bull2016["couleur_".($cpt%2+1)."_MS_bilan_cycle"]["V"], $param_bull2016["couleur_".($cpt%2+1)."_MS_bilan_cycle"]["B"]);
		$pdf->SetDrawColor(255, 255, 255);
		$pdf->Rect($x_courant, $y_courant, $param_bull2016["largeur_col_niveau_maitrise_composantes_socle"], $param_bull2016["hauteur_ligne_maitrise_bilan_cycle"], 'DF');
		$pdf->SetFillColor(0, 0, 0);
		$pdf->SetTextColor(0, 0, 0);
		$pdf->SetXY($x_courant, $y_courant);
		$pdf->Cell($param_bull2016["largeur_col_niveau_maitrise_composantes_socle"],$param_bull2016["hauteur_ligne_maitrise_bilan_cycle"], "Maîtrise satisfaisante",0,2,'C');

		$x_courant=$param_bull2016["x_TBM_bilan_cycle"];
		$pdf->SetFillColor($param_bull2016["couleur_".($cpt%2+1)."_TBM_bilan_cycle"]["R"], $param_bull2016["couleur_".($cpt%2+1)."_TBM_bilan_cycle"]["V"], $param_bull2016["couleur_".($cpt%2+1)."_TBM_bilan_cycle"]["B"]);
		$pdf->SetDrawColor(255, 255, 255);
		$pdf->Rect($x_courant, $y_courant, $param_bull2016["largeur_col_niveau_maitrise_composantes_socle"], $param_bull2016["hauteur_ligne_maitrise_bilan_cycle"], 'DF');
		$pdf->SetFillColor(0, 0, 0);
		$pdf->SetTextColor(0, 0, 0);
		$pdf->SetXY($x_courant, $y_courant);
		$pdf->Cell($param_bull2016["largeur_col_niveau_maitrise_composantes_socle"],$param_bull2016["hauteur_ligne_maitrise_bilan_cycle"], "Très bonne maîtrise",0,2,'C');

		// Lignes domaines du tableau
		$tab_domaines=array("CPD_FRA", "CPD_ETR", "CPD_SCI", "CPD_ART", "MET_APP", "FRM_CIT", "SYS_NAT", "REP_MND");
		$tab_niveau_maitrise=array(0=>"", 1=>"MI", 2=>"MF", 3=>"MS", 4=>"TBM");
		foreach($tab_domaine_bilan_socle as $domaine => $intitule) {
			$cpt++;
			$y_courant+=$param_bull2016["hauteur_ligne_maitrise_bilan_cycle"]+1;

			//$tab_bilan_cycle_eleves[$lig_socle->login][$lig_socle->cycle][$lig_socle->code_composante]=$lig_socle->niveau_maitrise;
			for($loop_domaine=0;$loop_domaine<count($tab_domaines);$loop_domaine++) {
				$coche[$tab_domaines[$loop_domaine]][1]="";
				$coche[$tab_domaines[$loop_domaine]][2]="";
				$coche[$tab_domaines[$loop_domaine]][3]="";
				$coche[$tab_domaines[$loop_domaine]][4]="";
			}
			if(isset($tab_bull['eleve'][$i]['socle'][$cycle])) {
				for($loop_domaine=0;$loop_domaine<count($tab_domaines);$loop_domaine++) {
					if(isset($tab_bull['eleve'][$i]['socle'][$cycle][$tab_domaines[$loop_domaine]])) {
						$coche[$tab_domaines[$loop_domaine]][$tab_bull['eleve'][$i]['socle'][$cycle][$tab_domaines[$loop_domaine]]]="X";
					}
				}
			}

			$x_courant=$param_bull2016["x_col_domaine_maitrise_composantes_socle"];
			//$largeur_domaine_socle=$param_bull2016["x_MI_bilan_cycle"]-10-1;
			$largeur_domaine_socle=$param_bull2016["largeur_col_domaine_maitrise_composantes_socle"];

			$pdf->SetFillColor($param_bull2016["couleur_".($cpt%2+1)."_MI_bilan_cycle"]["R"], $param_bull2016["couleur_".($cpt%2+1)."_MI_bilan_cycle"]["V"], $param_bull2016["couleur_".($cpt%2+1)."_MI_bilan_cycle"]["B"]);
			$pdf->SetDrawColor(255, 255, 255);
			$pdf->Rect($x_courant, $y_courant, $largeur_domaine_socle, $param_bull2016["hauteur_ligne_maitrise_bilan_cycle"], 'DF');
			$pdf->SetFillColor(0, 0, 0);
			$pdf->SetTextColor(0, 0, 0);
			$pdf->SetXY($x_courant, $y_courant);
			$pdf->SetFont('DejaVu','',7);
			$pdf->Cell($param_bull2016["largeur_col_niveau_maitrise_composantes_socle"],$param_bull2016["hauteur_ligne_maitrise_bilan_cycle"], $intitule,0,2,'L');

			$x_courant=$param_bull2016["x_MI_bilan_cycle"];
			$contenu=$coche[$domaine][1];
			$pdf->SetFillColor($param_bull2016["couleur_".($cpt%2+1)."_MI_bilan_cycle"]["R"], $param_bull2016["couleur_".($cpt%2+1)."_MI_bilan_cycle"]["V"], $param_bull2016["couleur_".($cpt%2+1)."_MI_bilan_cycle"]["B"]);
			$pdf->SetDrawColor(255, 255, 255);
			$pdf->Rect($x_courant, $y_courant, $param_bull2016["largeur_col_niveau_maitrise_composantes_socle"], $param_bull2016["hauteur_ligne_maitrise_bilan_cycle"], 'DF');
			$pdf->SetFillColor(0, 0, 0);
			$pdf->SetTextColor(0, 0, 0);
			$pdf->SetXY($x_courant, $y_courant);
			$pdf->SetFont('DejaVu','',7);
			$pdf->Cell($param_bull2016["largeur_col_niveau_maitrise_composantes_socle"],$param_bull2016["hauteur_ligne_maitrise_bilan_cycle"], $contenu,0,2,'C');

			$x_courant=$param_bull2016["x_MF_bilan_cycle"];
			$contenu=$coche[$domaine][2];
			$pdf->SetFillColor($param_bull2016["couleur_".($cpt%2+1)."_MF_bilan_cycle"]["R"], $param_bull2016["couleur_".($cpt%2+1)."_MF_bilan_cycle"]["V"], $param_bull2016["couleur_".($cpt%2+1)."_MF_bilan_cycle"]["B"]);
			$pdf->SetDrawColor(255, 255, 255);
			$pdf->Rect($x_courant, $y_courant, $param_bull2016["largeur_col_niveau_maitrise_composantes_socle"], $param_bull2016["hauteur_ligne_maitrise_bilan_cycle"], 'DF');
			$pdf->SetFillColor(0, 0, 0);
			$pdf->SetTextColor(0, 0, 0);
			$pdf->SetXY($x_courant, $y_courant);
			//$pdf->SetFont('DejaVu','B',12);
			$pdf->Cell($param_bull2016["largeur_col_niveau_maitrise_composantes_socle"],$param_bull2016["hauteur_ligne_maitrise_bilan_cycle"], $contenu,0,2,'C');

			$x_courant=$param_bull2016["x_MS_bilan_cycle"];
			$contenu=$coche[$domaine][3];
			$pdf->SetFillColor($param_bull2016["couleur_".($cpt%2+1)."_MS_bilan_cycle"]["R"], $param_bull2016["couleur_".($cpt%2+1)."_MS_bilan_cycle"]["V"], $param_bull2016["couleur_".($cpt%2+1)."_MS_bilan_cycle"]["B"]);
			$pdf->SetDrawColor(255, 255, 255);
			$pdf->Rect($x_courant, $y_courant, $param_bull2016["largeur_col_niveau_maitrise_composantes_socle"], $param_bull2016["hauteur_ligne_maitrise_bilan_cycle"], 'DF');
			$pdf->SetFillColor(0, 0, 0);
			$pdf->SetTextColor(0, 0, 0);
			$pdf->SetXY($x_courant, $y_courant);
			//$pdf->SetFont('DejaVu','B',12);
			$pdf->Cell($param_bull2016["largeur_col_niveau_maitrise_composantes_socle"],$param_bull2016["hauteur_ligne_maitrise_bilan_cycle"], $contenu,0,2,'C');


			$x_courant=$param_bull2016["x_TBM_bilan_cycle"];
			$contenu=$coche[$domaine][4];
			$pdf->SetFillColor($param_bull2016["couleur_".($cpt%2+1)."_TBM_bilan_cycle"]["R"], $param_bull2016["couleur_".($cpt%2+1)."_TBM_bilan_cycle"]["V"], $param_bull2016["couleur_".($cpt%2+1)."_TBM_bilan_cycle"]["B"]);
			$pdf->SetDrawColor(255, 255, 255);
			$pdf->Rect($x_courant, $y_courant, $param_bull2016["largeur_col_niveau_maitrise_composantes_socle"], $param_bull2016["hauteur_ligne_maitrise_bilan_cycle"], 'DF');
			$pdf->SetFillColor(0, 0, 0);
			$pdf->SetTextColor(0, 0, 0);
			$pdf->SetXY($x_courant, $y_courant);
			//$pdf->SetFont('DejaVu','B',12);
			$pdf->Cell($param_bull2016["largeur_col_niveau_maitrise_composantes_socle"],$param_bull2016["hauteur_ligne_maitrise_bilan_cycle"], $contenu,0,2,'C');
		}

		// Explications sur le contenu des domaines du socle, sous le tableau des niveaux de maitrise

		$texte="";
		foreach($tab_domaine_bilan_socle_description as $domaine => $commentaire) {
			if($texte!="") {
				$texte.=" - ";
			}
			$texte.=$commentaire;
		}

		$x_courant=10;
		$y_courant+=7;
		$pdf->SetTextColor(0, 0, 0);
		$pdf->SetXY($x_courant, $y_courant);
		$pdf->SetFont('DejaVu','',7);
		//$pdf->Cell($param_bull2016["largeur_col_niveau_maitrise_composantes_socle"],$param_bull2016["hauteur_ligne_maitrise_bilan_cycle"], "Très bonne maîtrise",0,2,'C');

		$taille_max_police=7;
		$taille_min_police=ceil($taille_max_police/3);

		$largeur_dispo=$param_bull2016["largeur_bandeau_bilan_acquisitions"];
		$h_cell=10;

		cell_ajustee($texte, $x_courant, $y_courant, $largeur_dispo, $h_cell, $taille_max_police, $taille_min_police, "",'C','L',0.3,1);


		//=========================================

		// Bandeau Synthèse des acquis scolaires de l'élève en fin de cycle N

		$pdf->SetFillColor($param_bull2016["couleur_bandeau_synthese_acquis_fin_cycle"]["R"], $param_bull2016["couleur_bandeau_synthese_acquis_fin_cycle"]["V"], $param_bull2016["couleur_bandeau_synthese_acquis_fin_cycle"]["B"]);

		$pdf->Rect($param_bull2016["x_bandeau_synthese_acquis_fin_cycle"], $param_bull2016["y_bandeau_synthese_acquis_fin_cycle"], $param_bull2016["largeur_bandeau_synthese_acquis_fin_cycle"], $param_bull2016["hauteur_bandeau_synthese_acquis_fin_cycle"], 'F');

		$pdf->SetFillColor(0, 0, 0);
		$pdf->SetTextColor(255, 255, 255);
		$pdf->SetXY($param_bull2016["x_bandeau_synthese_acquis_fin_cycle"], $param_bull2016["y_bandeau_synthese_acquis_fin_cycle"]+1);
		$pdf->SetFont('DejaVu','B',12);
		$pdf->Cell($param_bull2016["largeur_bandeau_synthese_acquis_fin_cycle"],7, "Synthèse des acquis scolaires de l'élève en fin de cycle ".$cycle,0,2,'C');

		// Cadre synthèse des acquis...

		$y_courant=$param_bull2016["y_bandeau_synthese_acquis_fin_cycle"]+$param_bull2016["hauteur_bandeau_synthese_acquis_fin_cycle"]+5;

		$pdf->SetFillColor($param_bull2016["couleur_bilan_acquisitions"]["R"], $param_bull2016["couleur_bilan_acquisitions"]["V"], $param_bull2016["couleur_bilan_acquisitions"]["B"]);
		$pdf->SetDrawColor(255, 255, 255);
		$pdf->Rect($param_bull2016["x_bilan_acquisitions"], $y_courant, $param_bull2016["largeur_bilan_acquisitions"], $param_bull2016["hauteur_synthese_acquis_bilan_cycle"], 'DF');

		$texte="-";
		//"Synthèse à récupérer dans la base..."
		if(isset($tab_bull['eleve'][$i]['socle'][$cycle]["synthese"])) {
			$texte=$tab_bull['eleve'][$i]['socle'][$cycle]["synthese"];
		}
		$pdf->SetFillColor(0, 0, 0);
		$pdf->SetTextColor(0, 0, 0);
		$pdf->SetXY($param_bull2016["x_bilan_acquisitions"], $y_courant+1);
		$pdf->SetFont('DejaVu','',9);
		$pdf->Cell($param_bull2016["largeur_bilan_acquisitions"],7, $texte,0,2,'L');

/*
		if($use_cell_ajustee=="n") {
			$pdf->drawTextBox(($texteavis), $param_bull2016["largeur_bilan_acquisitions"]-$marge_droite_avis_cons, $hauteur_bilan_acquisitions-10, 'J', 'M', 0);
		}
		else {
			$texte=$texteavis;
			$taille_max_police=10;
			$taille_min_police=ceil($taille_max_police/3);

			$largeur_dispo=$param_bull2016["largeur_bilan_acquisitions"]-$marge_droite_avis_cons;
			$h_cell=$hauteur_bilan_acquisitions-10;

			cell_ajustee(($texte),$pdf->GetX(),$pdf->GetY(),$largeur_dispo,$h_cell,$taille_max_police,$taille_min_police,'');
		}
		//=========================================

*/


	//=========================================

		// Visa

		$x_courant=$param_bull2016["x_signature_PP_bilan_cycle"];
		//$y_visa=$param_bull2016["y_visa_bilan_cycle"];

		$y_visa=$param_bull2016["y_bandeau_synthese_acquis_fin_cycle"]+$param_bull2016["hauteur_bandeau_synthese_acquis_fin_cycle"]+5+$param_bull2016["hauteur_synthese_acquis_bilan_cycle"]+5;


		// Cadre Visa PP

		$pdf->SetFillColor($param_bull2016["couleur_communication_famille"]["R"], $param_bull2016["couleur_communication_famille"]["V"], $param_bull2016["couleur_communication_famille"]["B"]);
		$pdf->SetDrawColor(255, 255, 255);
		$pdf->Rect($param_bull2016["x_signature_PP_bilan_cycle"], $y_visa, $param_bull2016["largeur_signature_PP_bilan_cycle"], $param_bull2016["hauteur_signature_bilan_cycle"], 'DF');
		//$pdf->SetDrawColor(255, 255, 255);
		//$pdf->Rect($param_bull2016["x_signature_PP_bilan_cycle"], $y_visa, $param_bull2016["largeur_signature_PP_bilan_cycle"], $param_bull2016["hauteur_signature_bilan_cycle"], 'D');

		$texte="Visa du professeur principal";
		$y_courant=$y_visa;
		$pdf->SetXY($param_bull2016["x_signature_PP_bilan_cycle"], $y_courant);
		$pdf->SetFillColor(0, 0, 0);
		$pdf->SetTextColor(0, 0, 0);
		$pdf->SetFont('DejaVu','',7);
		$pdf->Cell($param_bull2016["largeur_signature_PP_bilan_cycle"],7, $texte,0,2,'L');

		$index_pp='pp_classe';
		if(isset($tab_bull['eleve'][$i][$index_pp][0]['login'])) {
			$texte=affiche_utilisateur($tab_bull['eleve'][$i][$index_pp][0]['login'],$tab_bull['eleve'][$i]['id_classe']);
			/*
			for($i_pp=1;$i_pp<count($tab_bull['eleve'][$i][$index_pp]);$i_pp++) {
				$texte.=", ";
				$texte.=affiche_utilisateur($tab_bull['eleve'][$i][$index_pp][$i_pp]['login'],$tab_bull['eleve'][$i]['id_classe']);
			}
			*/

			$y_courant+=4;
			$pdf->SetFont('DejaVu','',6);
			$pdf->SetXY($param_bull2016["x_signature_PP_bilan_cycle"], $y_courant);
			$pdf->Cell($param_bull2016["largeur_signature_PP_bilan_cycle"], 7, $texte,0,2,'L');
		}

		$texte=strftime("Le %d/%m/%Y");
		$y_courant+=3;
		$pdf->SetFont('DejaVu','',6);
		$pdf->SetXY($param_bull2016["x_signature_PP_bilan_cycle"], $y_courant);
		$pdf->Cell($param_bull2016["largeur_signature_PP_bilan_cycle"], 7, $texte,0,2,'L');

		$texte="Signature";
		$y_courant+=6;
		$pdf->SetFont('DejaVu','',7);
		$pdf->SetXY($param_bull2016["x_signature_PP_bilan_cycle"], $y_courant);
		$pdf->Cell($param_bull2016["largeur_signature_PP_bilan_cycle"], 7, $texte,0,2,'L');

		//$pdf->drawTextBox($texte, $param_bull2016["largeur_signature_PP_bilan_cycle"], $param_bull2016["hauteur_signature_bilan_cycle"], 'L', 'T', 0);



		// 20190701
		// Cadre Visa chef étab
		$x_courant=$param_bull2016["x_signature_PP_bilan_cycle"]+$param_bull2016["largeur_signature_PP_bilan_cycle"];

		$pdf->SetFillColor($param_bull2016["couleur_communication_famille"]["R"], $param_bull2016["couleur_communication_famille"]["V"], $param_bull2016["couleur_communication_famille"]["B"]);
		$pdf->SetDrawColor(255, 255, 255);
		$pdf->Rect($x_courant, $y_visa, $param_bull2016["largeur_signature_chef_bilan_cycle"], $param_bull2016["hauteur_signature_bilan_cycle"], 'DF');

		/*
		// Décalé plus bas pour écrire par dessus la signature
		$texte="Visa du principal du collège";
		$y_courant=$y_visa;
		$pdf->SetXY($x_courant, $y_courant);
		$pdf->SetFillColor(0, 0, 0);
		$pdf->SetTextColor(0, 0, 0);
		$pdf->SetFont('DejaVu','',7);
		$pdf->Cell($param_bull2016["largeur_signature_chef_bilan_cycle"],7, $texte,0,2,'L');
		*/

		$pdf->SetXY($x_courant, $y_visa);
		if((isset($signature_bull[$tab_bull['id_classe']]))&&($signature_bull[$tab_bull['id_classe']]!="")&&(file_exists($signature_bull[$tab_bull['id_classe']]))) {
			$fich_sign=$signature_bull[$tab_bull['id_classe']];

			//$X_sign = $param_bull2016["x_signature_chef"]; // C'est un positionnement pour la page 2 du bulletin, pas pour le socle.
			$X_sign = $x_courant;
			$Y_sign = $y_visa;

			//======================
			/*
			// DEBUG:
			$pdf->SetXY($x_courant+20, $y_visa+20);
			$texte="\$X_sign=".$X_sign;
			$pdf->Cell($param_bull2016["largeur_signature_chef_bilan_cycle"], 7, $texte,0,2,'L');
			*/
			//======================

			$largeur_dispo=$param_bull2016["largeur_signature_chef_bilan_cycle"]-10;
			// On ajuste mieux la hauteur de l'image, quitte à ce que le tampon/signature soit en surimpression (ou plutôt sous-impression) avec le Nom du chef en première ligne du cadre.
			$hauteur_dispo=$param_bull2016["hauteur_signature_bilan_cycle"]-2;

			$tmp_dim_photo=getimagesize($fich_sign);
			$ratio_l=$tmp_dim_photo[0]/$largeur_dispo;
			$ratio_h=$tmp_dim_photo[1]/$hauteur_dispo;
			if($ratio_l>$ratio_h) {
				$L_sign = $largeur_dispo;
				$H_sign = $largeur_dispo*$tmp_dim_photo[1]/$tmp_dim_photo[0];
			}
			else {
				$H_sign = $hauteur_dispo;
				$L_sign = $hauteur_dispo*$tmp_dim_photo[0]/$tmp_dim_photo[1];
			}

			/*
			echo "\$X_sign=$X_sign<br />\n";
			echo "\$Y_sign=$Y_sign<br />\n";
			echo "\$L_sign=$L_sign<br />\n";
			echo "\$H_sign=$H_sign<br />\n";
			*/

// A FAIRE : TESTER LA LARGEUR DE L'IMAGE... RATIO A REVOIR CI-DESSUS?
// OU ORDRE A MODIFIER POUR METTRE LE TEXTE PAR DESSUS L'IMAGE

			$X_sign += ($param_bull2016["largeur_signature_chef_bilan_cycle"]-$L_sign) / 2;
			$Y_sign += ($param_bull2016["hauteur_signature_bilan_cycle"]-$H_sign) / 2;

			$tmp_dim_photo=getimagesize($fich_sign);

			//======================
			/*
			// DEBUG:
			$pdf->SetXY($x_courant, $y_visa+20);
			//$texte="\$tmp_dim_photo[2]=".$tmp_dim_photo[2];
			$texte="\$x_courant=".$x_courant." \$y_visa=".$y_visa;
			$pdf->Cell($param_bull2016["largeur_signature_chef_bilan_cycle"], 7, $texte,0,2,'L');

			$pdf->SetXY($x_courant, $y_visa+30);
			//$texte="\$tmp_dim_photo[2]=".$tmp_dim_photo[2];
			$texte="\$X_sign=".$X_sign." \$Y_sign=".$Y_sign;
			$pdf->Cell($param_bull2016["largeur_signature_chef_bilan_cycle"], 7, $texte,0,2,'L');
			*/
			//======================

			if((isset($tmp_dim_photo[2]))&&($tmp_dim_photo[2]==2)) {
				//$pdf->Image($fich_sign, $X_sign, $Y_sign, $L_sign, $H_sign);
				//======================
				$pdf->Image($fich_sign, round($X_sign), round($Y_sign), round($L_sign), round($H_sign));
				// DEBUG:
				//$pdf->Image($fich_sign, $x_courant, $y_visa, round($L_sign), round($H_sign));
				//======================
			}
		}

		//$pdf->MultiCell($param_bull2016["largeur_signature_chef_bilan_cycle"], 5, "Le ".strftime("%d/%m/%Y").", ".$tab_bull['suivi_par'], 0, 2, '');

		//======================
		// On remet par dessus l'image:
		$x_courant=$param_bull2016["x_signature_PP_bilan_cycle"]+$param_bull2016["largeur_signature_PP_bilan_cycle"];
		$texte="Visa du principal du collège";
		$y_courant=$y_visa;
		$pdf->SetXY($x_courant, $y_courant);
		$pdf->SetFillColor(0, 0, 0);
		$pdf->SetTextColor(0, 0, 0);
		$pdf->SetFont('DejaVu','',7);
		$pdf->Cell($param_bull2016["largeur_signature_chef_bilan_cycle"],7, $texte,0,2,'L');
		//======================

		$texte=$tab_bull['suivi_par'];
		$y_courant+=4;
		$pdf->SetFont('DejaVu','',6);
		$pdf->SetXY($x_courant, $y_courant);
		$pdf->Cell($param_bull2016["largeur_signature_chef_bilan_cycle"], 7, $texte,0,2,'L');


		$texte=strftime("Le %d/%m/%Y");
		$y_courant+=3;
		$pdf->SetFont('DejaVu','',6);
		$pdf->SetXY($x_courant, $y_courant);
		$pdf->Cell($param_bull2016["largeur_signature_chef_bilan_cycle"], 7, $texte,0,2,'L');

		$y_courant+=6;
		$pdf->SetXY($x_courant, $y_courant);
		$pdf->SetFont('DejaVu','',7);
		$pdf->Cell($param_bull2016["largeur_signature_chef_bilan_cycle"], 7, "Signature",0,2,'L');



		// Cadre Visa famille

		//if($param_bull2016["bull2016_cadre_visa_famille"]=="y") {
			$x_courant=$param_bull2016["x_signature_PP_bilan_cycle"]+$param_bull2016["largeur_signature_PP_bilan_cycle"]+1;
			$x_courant+=$param_bull2016["largeur_signature_chef_bilan_cycle"];

			$pdf->SetFillColor($param_bull2016["couleur_communication_famille"]["R"], $param_bull2016["couleur_communication_famille"]["V"], $param_bull2016["couleur_communication_famille"]["B"]);
			$pdf->SetDrawColor(255, 255, 255);
			$pdf->Rect($x_courant, $y_visa, $param_bull2016["largeur_signature_parents_bilan_cycle"], $param_bull2016["hauteur_signature_bilan_cycle"], 'DF');

			$y_courant=$y_visa;
			//$pdf->SetXY($param_bull2016["x_visa_famille"], $y_visa);
			$pdf->SetXY($x_courant, $y_courant);
			$pdf->SetFillColor(0, 0, 0);
			$pdf->SetTextColor(0, 0, 0);
			$pdf->SetFont('DejaVu','',7);
			$pdf->Cell($param_bull2016["largeur_signature_parents_bilan_cycle"],7, "Visa des parents ou du responsable légal",0,2,'L');

			$y_courant+=4;
			$pdf->SetXY($x_courant, $y_courant);
			$pdf->SetFont('DejaVu','',6);
			$pdf->Cell($param_bull2016["largeur_signature_parents_bilan_cycle"], 7, "Pris connaissance le :",0,2,'L');

			$y_courant+=3;
			$y_courant+=6;
			$pdf->SetXY($x_courant, $y_courant);
			$pdf->SetFont('DejaVu','',7);
			$pdf->Cell($param_bull2016["largeur_signature_parents_bilan_cycle"], 7, "Signature",0,2,'L');
		//}

		$pdf->SetDrawColor(0, 0, 0);

	}
}
?>
