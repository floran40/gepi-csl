<?php
/*
 *
 * Copyright 2001, 2019 Thomas Belliard, Laurent Delineau, Edouard Hue, Eric Lebrun, Stephane Boireau
 *
 * This file is part of GEPI.
 *
 * GEPI is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * GEPI is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the  warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with GEPI; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

// Initialisations files
require_once("../lib/initialisations.inc.php");

// Resume session
$resultat_session = $session_gepi->security_check();
if ($resultat_session == 'c') {
	header("Location: ../utilisateurs/mon_compte.php?change_mdp=yes");
	die();
} else if ($resultat_session == '0') {
	header("Location: ../logout.php?auto=1");
	die();
}


//INSERT INTO droits VALUES ('/groupes/get_csv.php', 'F', 'V', 'V', 'V', 'F', 'V', 'Génération de CSV élèves', '');
//INSERT INTO droits VALUES ('/groupes/get_csv.php', 'V', 'V', 'V', 'V', 'F', 'V', 'Génération de CSV élèves', '');
if (!checkAccess()) {
	header("Location: ../logout.php?auto=1");
	die();
}

/*
	get_acces_adresse_resp($login_ele, $id_classe='', $login_resp='') {
	get_acces_tel_resp($login_ele, $id_classe='', $login_resp='') {
	get_acces_mail_resp($login_ele, $id_classe='', $login_resp='') {
	get_acces_adresse_ele($login_ele, $id_classe='') {
	get_acces_tel_ele($login_ele, $id_classe='') {
	get_acces_mail_ele($login_ele, $id_classe='') {
*/

$id_groupe = isset($_POST['id_groupe']) ? $_POST['id_groupe'] : (isset($_GET['id_groupe']) ? $_GET['id_groupe'] : NULL);
$id_classe = isset($_POST['id_classe']) ? $_POST['id_classe'] : (isset($_GET['id_classe']) ? $_GET['id_classe'] : NULL);
$id_aid = isset($_POST['id_aid']) ? $_POST['id_aid'] : (isset($_GET['id_aid']) ? $_GET['id_aid'] : NULL);
$id_action = isset($_POST['id_action']) ? $_POST['id_action'] : (isset($_GET['id_action']) ? $_GET['id_action'] : NULL);

$mode = isset($_POST['mode']) ? $_POST['mode'] : (isset($_GET['mode']) ? $_GET['mode'] : NULL);

//$tab=array('avec_classe','avec_login','avec_nom','avec_prenom','avec_sexe','avec_naiss','avec_email','avec_statut','avec_ine','avec_elenoet','avec_ele_id','avec_prof');
if(in_array($_SESSION['statut'], array('administrateur', 'scolarite', 'cpe'))) {
	$tab=array('avec_classe','avec_login','avec_nom','avec_prenom','avec_sexe','avec_naiss','avec_lieu_naiss','avec_email','avec_statut','avec_elenoet','avec_ele_id','avec_no_gep','avec_prof', 'avec_doublant', 'avec_regime', 'avec_infos_resp');
}
else {
	$tab=array('avec_classe','avec_login','avec_nom','avec_prenom','avec_sexe','avec_naiss','avec_lieu_naiss','avec_email','avec_statut','avec_elenoet','avec_ele_id','avec_no_gep','avec_prof', 'avec_doublant', 'avec_regime');
}
for($i=0;$i<count($tab);$i++) {
	$champ=$tab[$i];
	$$champ = isset($_POST[$champ]) ? $_POST[$champ] : (isset($_GET[$champ]) ? $_GET[$champ] : NULL);

	if((isset($_POST[$champ]))||(isset($_GET[$champ]))) {
		$_SESSION['mes_listes_'.$tab[$i]]="y";
	}
	else {
		$_SESSION['mes_listes_'.$tab[$i]]="n";
	}
}

if((isset($avec_naiss))&&($avec_naiss=='y')) {
	$format_naiss=isset($_POST['format_naiss']) ? $_POST['format_naiss'] : 'aaaammjj';
	$_SESSION['mes_listes_format_naiss']=$format_naiss;
}

//echo "1 - \$id_groupe=$id_groupe<br />";

//$periode_num = isset($_GET['periode_num']) ? $_GET['periode_num'] : 0;
//$periode_num = isset($_POST['periode_num']) ? $_POST['periode_num'] : (isset($_GET['periode_num']) ? $_GET['periode_num'] : 1);
$periode_num = isset($_POST['periode_num']) ? $_POST['periode_num'] : (isset($_GET['periode_num']) ? $_GET['periode_num'] : NULL);

//if (!is_numeric($periode_num)) {$periode_num = 0;}
//if (!is_numeric($periode_num)) {$periode_num = 1;}

$_SESSION['mes_listes_periode_num']=$periode_num;

if (is_numeric($id_groupe) && $id_groupe > 0) {
	$current_group = get_group($id_groupe);
	//echo "2<br />";
} else {
	$current_group = false;
}

if ($current_group) {
	$nom_fic = $current_group["name"] . "-" . remplace_accents(preg_replace('/, /','~',$current_group["classlist_string"]),'all') . ".csv";
	if ((!isset($periode_num))||(!is_numeric($periode_num))) {$periode_num="all";}
} elseif(isset($id_aid)) {
	if(!preg_match("/^[0-9]{1,}$/", $id_aid)) {
		die("Indice AID '$id_aid' invalide.");
	}
	$tab_aid=get_tab_aid($id_aid);
	$complement="";
	if(isset($_GET['type_export'])) {
		$complement="_".$_GET['type_export'];
	}
	$nom_fic=remplace_accents($tab_aid['nom_aid']."_".$tab_aid['nom_general_complet'].$complement."_periode_".$periode_num,"all") . ".csv";
	if ((!isset($periode_num))||(!is_numeric($periode_num))) {$periode_num="all";}
} elseif(isset($id_action)) {

	if(preg_match('/^[0-9]{1,}$/', $id_action)) {
		$action=get_action($id_action);
		if(count($action)==0) {
			die('Action non valide.');
		}

		if(isset($mode)&&$mode=='presents') {
			$nom_fic=remplace_accents('action_'.$action['nom'].'_'.$action['date_action'].'_presents', 'all').".csv";
		}
		else {
			$nom_fic=remplace_accents('action_'.$action['nom'].'_'.$action['date_action'], 'all').".csv";
		}
	}
	else {
		die('Action non valide.');
	}
} else {
	if($id_classe=='toutes') {
		$classe = "Toutes_les_classes";
		$nom_fic = $classe.".csv";
	}
	else {
		$classe = get_nom_classe($id_classe);
		$nom_fic = remplace_accents($classe,"all") . ".csv";
	}

	if ((!isset($periode_num))||(!is_numeric($periode_num))) {$periode_num = 1;}
}

//debug_var();

send_file_download_headers('text/x-csv',$nom_fic);

if((!isset($id_classe))||($id_classe!="toutes")) {
	include "../lib/periodes.inc.php";
}

$fd = '';

//==============================================================================
if((isset($_GET['type_export']))&&($_GET['type_export']=="ariane")&&(isset($tab_aid))) {
	$fd.=";;;;;NOM DU PROJET;".$tab_aid['nom_aid'].";;;;\n";
	$fd.=";;;;;LISTE DES PARTICIPANTS;;;;;\n";
	$fd.=";;;;;;;;;;\n";
	$fd.="Classe;Nom de famille;Prénom;Date de naissance;Lieu naissance;Nom Responsable légal 1;Prénom responsable légal 1;Adresse;CP;Commune;Tel portable resp. légal 1\n";
	/*
	echo "<pre>";
	print_r($tab_aid);
	echo "</pre>";
	*/

	// Décommenter les lignes requises dans l'export:
	//$tab_acces_tel_ele=get_tab_acces_tel_ele();
	//$tab_acces_mail_ele=get_tab_acces_mail_ele();
	$tab_acces_adresse_resp=get_tab_acces_adresse_resp();
	$tab_acces_tel_resp=get_tab_acces_tel_resp();
	//$tab_acces_mail_resp=get_tab_acces_mail_resp();

	if(isset($tab_aid["eleves"][$periode_num]["users"])) {
		foreach($tab_aid["eleves"][$periode_num]["users"] as $current_eleve) {
			$eleve_login = $current_eleve["login"];
			$eleve_nom = $current_eleve["nom"];
			$eleve_prenom = $current_eleve["prenom"];

			//$eleve_classe = $current_eleve["classe"];
			$sql="SELECT classe FROM classes WHERE id='".$current_eleve["classe"]."'";
			$res_tmp=mysqli_query($GLOBALS["mysqli"], $sql);
			if(mysqli_num_rows($res_tmp)==0){
				die("$eleve_login ne serait dans aucune classe???</body></html>");
			}
			else{
				$lig_tmp=mysqli_fetch_object($res_tmp);
				$eleve_classe=$lig_tmp->classe;
			}

			// La fonction get_group() dans /lib/groupes.inc.php ne récupère pas le sexe et la date de naissance...
			// ... pourrait-on l'ajouter?
			$sql="SELECT sexe,naissance,lieu_naissance,email,no_gep,elenoet,ele_id FROM eleves WHERE login='$eleve_login'";
			$res_tmp=mysqli_query($GLOBALS["mysqli"], $sql);

			if(mysqli_num_rows($res_tmp)==0){
				die("Problème avec les infos (date de naissance, sexe,...) de $eleve_login</body></html>");
			}
			else{
				$lig_tmp=mysqli_fetch_object($res_tmp);
				$eleve_sexe=$lig_tmp->sexe;
				$eleve_naissance=formate_date($lig_tmp->naissance);
				/*
				if(($tab_acces_mail_ele['acces_global'])||(in_array($eleve_login, $tab_acces_mail_ele['login_ele']))) {
					$eleve_email=$lig_tmp->email;
				}
				else {
					$eleve_email='';
				}
				*/
				$eleve_no_gep=$lig_tmp->no_gep;
				$eleve_elenoet=$lig_tmp->elenoet;
				$eleve_ele_id=$lig_tmp->ele_id;

				$eleve_lieu_naissance=get_commune($lig_tmp->lieu_naissance,'2');
			}

			$ligne=$eleve_classe.";".$eleve_nom.";".$eleve_prenom.";".$eleve_naissance.";".$eleve_lieu_naissance.";";

			//$sql="SELECT rp.*, r.resp_legal FROM resp_pers rp, responsables2 r WHERE r.ele_id='$eleve_ele_id' AND r.pers_id=rp.pers_id AND (r.resp_legal='1' OR r.resp_legal='2' OR (r.pers_contact='1' AND (rp.tel_pers!='' OR rp.tel_prof!='' OR rp.tel_port!='')));";
			$sql="SELECT rp.*, r.resp_legal FROM resp_pers rp, responsables2 r WHERE r.ele_id='$eleve_ele_id' AND r.pers_id=rp.pers_id AND r.resp_legal='1';";
			$res_tmp=mysqli_query($GLOBALS["mysqli"], $sql);
			if(mysqli_num_rows($res_tmp)>0) {
				// Il n'y a qu'un resp_legal=1, donc on ne va faire qu'un tour dans la boucle.
				while($lig_tmp=mysqli_fetch_object($res_tmp)) {

					if(($tab_acces_adresse_resp['acces_global'])||(in_array($lig_tmp->pers_id, $tab_acces_adresse_resp['pers_id']))) {
						$tmp_tab_adr=get_adresse_responsable($lig_tmp->pers_id);

						$adresse=$tmp_tab_adr["adresse_sans_cp_commune"];

						if(($tmp_tab_adr['pays']!="")&&(casse_mot($tmp_tab_adr['pays'],"maj")!=casse_mot(getSettingValue("gepiSchoolPays")))) {
							$tmp_tab_adr['commune'].=" (".$tmp_tab_adr['pays'].")";
						}
						$ligne.=$lig_tmp->nom.";".$lig_tmp->prenom.";".$adresse.";".$tmp_tab_adr['cp'].";".$tmp_tab_adr['commune'].";";
					}
					else {
						$ligne.=$lig_tmp->nom.";".$lig_tmp->prenom.";;;;";
					}

					if(($tab_acces_tel_resp['acces_global'])||(in_array($lig_tmp->pers_id, $tab_acces_tel_resp['pers_id']))) {
						if($lig_tmp->tel_port!='') {
							$ligne.=affiche_numero_tel_sous_forme_classique($lig_tmp->tel_port);
						}
						elseif($lig_tmp->tel_pers!='') {
							$ligne.=affiche_numero_tel_sous_forme_classique($lig_tmp->tel_pers);
						}
						elseif($lig_tmp->tel_prof!='') {
							$ligne.=affiche_numero_tel_sous_forme_classique($lig_tmp->tel_prof);
						}
					}
					else {
						$ligne.=";";
					}
				}
			}
			else {
				$ligne.=";;;;;";
			}

			// Suppression du ; en fin de ligne
			//$ligne=preg_replace('/;$/','',$ligne);

			$fd.=$ligne."\n";
		}
	}

	echo echo_csv_encoded($fd);
	die();
}
elseif((isset($_GET['type_export']))&&($_GET['type_export']=="verdie")&&(isset($tab_aid))) {

	// Décommenter les lignes requises dans l'export:
	//$tab_acces_tel_ele=get_tab_acces_tel_ele();
	$tab_acces_mail_ele=get_tab_acces_mail_ele();
	//$tab_acces_adresse_resp=get_tab_acces_adresse_resp();
	//$tab_acces_tel_resp=get_tab_acces_tel_resp();
	$tab_acces_mail_resp=get_tab_acces_mail_resp();

	if((isset($_GET['entete_verdie']))&&($_GET['entete_verdie']=="2")) {
		$fd.="Liste type pour chargement des participants dans l'espace organisateur VERDIE.;;;;\n";
		$fd.="Conservez bien exactement le format de ce document, sans toucher les cellules grisées;;;;\n";
		$fd.=";;Saisir impérativement et exactement les termes A pour accompagnateur et E pour enfants participants au séjour;Saisir impérativement et exactement les termes M pour les participants de sexe masculin et F pour les participants de sexe féminin;\n";
	}
	$fd.="Nom;Prénom;Statut (A/E);Sexe (M/F);E-mail\n";

	$sql="SELECT u.login, u.nom, u.prenom, u.email, u.civilite, u.numind FROM utilisateurs u, j_aid_utilisateurs jau WHERE jau.id_utilisateur=u.login AND jau.id_aid='$id_aid';";
	//echo "$sql<br />\n";
	$res_prof=mysqli_query($GLOBALS["mysqli"], $sql);
	if(mysqli_num_rows($res_prof)>0) {
		while($lig=mysqli_fetch_object($res_prof)) {
			$ligne="$lig->nom;$lig->prenom;A;";
			if($lig->civilite=="Mme") {$ligne.="F;";}
			elseif($lig->civilite=="Mlle") {$ligne.="F;";}
			else {$ligne.="M;";}
			$ligne.="$lig->email";
	
			// Suppression du ; en fin de ligne
			//$ligne=preg_replace('/;$/','',$ligne);

			$fd.=$ligne."\n";
		}
	}

	/*
	echo "<pre>";
	print_r($tab_aid);
	echo "</pre>";
	*/

	if(isset($tab_aid["eleves"][$periode_num]["users"])) {
		foreach($tab_aid["eleves"][$periode_num]["users"] as $current_eleve) {
			$eleve_login = $current_eleve["login"];
			$eleve_nom = $current_eleve["nom"];
			$eleve_prenom = $current_eleve["prenom"];
			$eleve_sexe = $current_eleve["sexe"];
			$eleve_email='';
			if(($tab_acces_mail_ele['acces_global'])||(in_array($eleve_login, $tab_acces_mail_ele['login_ele']))) {
				$eleve_email = $current_eleve["email"];
				if($eleve_email=="") {
					$eleve_mail=get_valeur_champ("utilisateurs", "login='".$eleve_login."'", "email");
				}
			}
			if($eleve_email=="") {
				// Récupérer l'adresse mail parent
				//get_mail_user()

				$sql="SELECT rp.*, r.resp_legal FROM resp_pers rp, responsables2 r, eleves e WHERE e.login='$eleve_login' AND rp.pers_id=r.pers_id AND r.ele_id=e.ele_id AND (r.resp_legal='1' OR r.resp_legal='2') ORDER BY r.resp_legal;";
				//echo "$sql<br />";
				$res = mysqli_query($mysqli, $sql);
				if(mysqli_num_rows($res)>0) {
					while($lig=mysqli_fetch_object($res)) {
						if(($tab_acces_mail_resp['acces_global'])||(in_array($lig->pers_id, $tab_acces_mail_resp['pers_id']))) {
							if(check_mail($lig->mel)) {
								$eleve_email=$lig->mel;
								break;
							}
							elseif($lig->login!="") {
								$tmp_mail=get_mail_user($lig->login);
								if(check_mail($tmp_mail)) {
									$eleve_email=$tmp_mail;
									break;
								}
							}
						}
					}
				}
			}

			$ligne=$eleve_nom.";".$eleve_prenom.";E;".$eleve_sexe.";".$eleve_email;

			// Suppression du ; en fin de ligne
			//$ligne=preg_replace('/;$/','',$ligne);

			$fd.=$ligne."\n";
		}
	}

	echo echo_csv_encoded($fd);
	die();
}
elseif((isset($_GET['type_export']))&&($_GET['type_export']=="dareic")&&(isset($tab_aid))) {
	$fd.="N°;Titre de civilité;Prénom;Nom;Date de naissance;\n";

	if(isset($tab_aid["eleves"][$periode_num]["users"])) {
		$cpt_ele=1;
		foreach($tab_aid["eleves"][$periode_num]["users"] as $current_eleve) {
			$eleve_login = $current_eleve["login"];
			$eleve_nom = $current_eleve["nom"];
			$eleve_prenom = $current_eleve["prenom"];
			$eleve_sexe = $current_eleve["sexe"];
			$eleve_naissance = formate_date($current_eleve["naissance"]);
			$eleve_civilite = (($current_eleve["sexe"]=='F' || $current_eleve["sexe"]=='f') ? 'Mlle' : 'M');

			$ligne=$cpt_ele.';'.$eleve_civilite.';'.$eleve_prenom.";".$eleve_nom.";".$eleve_naissance;

			$fd.=$ligne."\n";
			$cpt_ele++;
		}
	}

	echo echo_csv_encoded($fd);
	die();
}
//==============================================================================

if((!isset($mode))||($mode=='presents')) {
	$fd.="CLASSE;LOGIN;NOM;PRENOM;SEXE;DATE_NAISS\n";
	$avec_classe="y";
	$avec_login="y";
	$avec_nom="y";
	$avec_prenom="y";
	$avec_sexe="y";
	$avec_naiss="y";
}
else {
	if((isset($avec_classe))&&($avec_classe=='y')) {$fd.="CLASSE;";}
	if((isset($avec_login))&&($avec_login=='y')) {$fd.="LOGIN;";}
	if((isset($avec_nom))&&($avec_nom=='y')) {$fd.="NOM;";}
	if((isset($avec_prenom))&&($avec_prenom=='y')) {$fd.="PRENOM;";}
	if((isset($avec_sexe))&&($avec_sexe=='y')) {$fd.="SEXE;";}
	if((isset($avec_naiss))&&($avec_naiss=='y')) {$fd.="DATE_NAISS;";}
	if((isset($avec_lieu_naiss))&&($avec_lieu_naiss=='y')) {$fd.="LIEU_NAISS;";}

	if((isset($avec_email))&&($avec_email=='y')) {
		$tab_acces_mail_ele=get_tab_acces_mail_ele();
		/*
		echo "<pre>";
		print_r($tab_acces_mail_ele);
		echo "</pre>";
		*/
		$fd.="EMAIL;";
	}
	if((isset($avec_statut))&&($avec_statut=='y')) {$fd.="STATUT;";}
	if($_SESSION['statut']!='professeur') {
		//if((isset($avec_ine))&&($avec_ine=='y')) {$fd.="INE;";}
		if((isset($avec_elenoet))&&($avec_elenoet=='y')) {$fd.="ELENOET;";}
		if((isset($avec_ele_id))&&($avec_ele_id=='y')) {$fd.="ELE_ID;";}
		if((isset($avec_no_gep))&&($avec_no_gep=='y')) {$fd.="INE;";}
	}

	if((isset($avec_doublant))&&($avec_doublant=='y')) {$fd.="REDOUBLANT;";}
	if((isset($avec_regime))&&($avec_regime=='y')) {$fd.="REGIME;";}
	if((isset($avec_infos_resp))&&($avec_infos_resp=='y')) {
		$fd.="RESP_LEGAL_1;TEL_PERS_1;TEL_PROF_1;TEL_PORT_1;RESP_LEGAL_2;TEL_PERS_2;TEL_PROF_2;TEL_PORT_2;RESP_LEGAL_0;TEL_PERS_0;TEL_PROF_0;TEL_PORT_0;RESP_LEGAL_0b;TEL_PERS_0b;TEL_PROF_0b;TEL_PORT_0b;";
		$tab_acces_tel_resp=get_tab_acces_tel_resp();
	}

	// Suppression du ; en fin de ligne
	$fd=preg_replace('/;$/','',$fd);
	$fd.="\n";
}

if($current_group) {
	//echo "\$current_group<br />\n";
	//echo "\$avec_prof=$avec_prof<br />\n";
	if($_SESSION['statut']!='professeur') {
		if((isset($avec_prof))&&($avec_prof=='y')) {
			$sql="SELECT u.login, u.nom, u.prenom, u.email, u.civilite, u.numind FROM utilisateurs u, j_groupes_professeurs jgp WHERE jgp.login=u.login AND jgp.id_groupe='$id_groupe';";
			//echo "$sql<br />\n";
			$res_prof=mysqli_query($GLOBALS["mysqli"], $sql);
			if(mysqli_num_rows($res_prof)>0) {
				while($lig=mysqli_fetch_object($res_prof)) {
					$ligne="";
					if((isset($avec_classe))&&($avec_classe=='y')) {$ligne.=";";}
					if((isset($avec_login))&&($avec_login=='y')) {$ligne.="$lig->login;";}
					if((isset($avec_nom))&&($avec_nom=='y')) {$ligne.="$lig->nom;";}
					if((isset($avec_prenom))&&($avec_prenom=='y')) {$ligne.="$lig->prenom;";}
					if((isset($avec_sexe))&&($avec_sexe=='y')) {$ligne.="$lig->civilite;";}
					if((isset($avec_naiss))&&($avec_naiss=='y')) {$ligne.=";";}
					if((isset($avec_lieu_naiss))&&($avec_lieu_naiss=='y')) {$ligne.=";";}

					if((isset($avec_email))&&($avec_email=='y')) {$ligne.="$lig->email;";}

					if((isset($avec_statut))&&($avec_statut=='y')) {$ligne.="professeur;";}
					if($_SESSION['statut']!='professeur') {
						//if((isset($avec_ine))&&($avec_ine=='y')) {$ligne.=";";}
						if((isset($avec_elenoet))&&($avec_elenoet=='y')) {$ligne.="$lig->numind;";}
						if((isset($avec_ele_id))&&($avec_ele_id=='y')) {$ligne.=";";}
						if((isset($avec_no_gep))&&($avec_no_gep=='y')) {$ligne.=";";}
					}
				
					// Suppression du ; en fin de ligne
					$ligne=preg_replace('/;$/','',$ligne);
			
					$fd.=$ligne."\n";
				}
			}
		}
	}

	/*
	echo "\$periode_num=$periode_num<br />\n";
	foreach($current_group["eleves"][$periode_num]["users"] as $current_eleve) {
		echo $current_eleve['login']."<br />\n";
	}
	echo "<br />\n";
	echo "<br />\n";
	*/

	foreach($current_group["eleves"][$periode_num]["users"] as $current_eleve) {
	//foreach($current_group["eleves"]["all"]["users"] as $current_eleve) {
		$eleve_login = $current_eleve["login"];
		$eleve_nom = $current_eleve["nom"];
		$eleve_prenom = $current_eleve["prenom"];

		//$eleve_classe = $current_eleve["classe"];
		$sql="SELECT classe FROM classes WHERE id='".$current_eleve["classe"]."'";
		$res_tmp=mysqli_query($GLOBALS["mysqli"], $sql);
		if(mysqli_num_rows($res_tmp)==0){
			die("$eleve_login ne serait dans aucune classe???</body></html>");
		}
		else{
			$lig_tmp=mysqli_fetch_object($res_tmp);
			$eleve_classe=$lig_tmp->classe;
		}

		// La fonction get_group() dans /lib/groupes.inc.php ne récupère pas le sexe et la date de naissance...
		// ... pourrait-on l'ajouter?
		$sql="SELECT sexe,naissance,lieu_naissance,email,no_gep,elenoet,ele_id FROM eleves WHERE login='$eleve_login'";
		$res_tmp=mysqli_query($GLOBALS["mysqli"], $sql);

		if(mysqli_num_rows($res_tmp)==0){
			die("Problème avec les infos (date de naissance, sexe,...) de $eleve_login</body></html>");
		}
		else{
			$lig_tmp=mysqli_fetch_object($res_tmp);
			$eleve_sexe=$lig_tmp->sexe;
			if((isset($format_naiss))&&($format_naiss=='jjmmaaaa')) {
				$eleve_naissance=formate_date($lig_tmp->naissance);
			}
			else {
				$eleve_naissance=$lig_tmp->naissance;
			}
			$eleve_email=$lig_tmp->email;
			$eleve_no_gep=$lig_tmp->no_gep;
			$eleve_elenoet=$lig_tmp->elenoet;
			$eleve_ele_id=$lig_tmp->ele_id;

			if($avec_lieu_naiss=='y') {
				$eleve_lieu_naissance=get_commune($lig_tmp->lieu_naissance,'2');
			}
		}

		if(((isset($avec_doublant))&&($avec_doublant=='y'))||
		((isset($avec_regime))&&($avec_regime=='y'))) {
			$sql="SELECT * FROM j_eleves_regime WHERE login='".$current_eleve["login"]."';";
			$res_tmp=mysqli_query($GLOBALS["mysqli"], $sql);
			if(mysqli_num_rows($res_tmp)==0) {
				//die("Problème avec les infos (régime, doublant) de $eleve_login</body></html>");
				$eleve_regime="X";
				$eleve_doublant="X";
			}
			else {
				while($lig_tmp=mysqli_fetch_object($res_tmp)) {
					$eleve_regime=$lig_tmp->regime;
					$eleve_doublant=$lig_tmp->doublant;
				}
			}
		}

		if((isset($avec_infos_resp))&&($avec_infos_resp=='y')) {
			$eleve_infos_resp_1="";
			$eleve_infos_resp_2="";
			$eleve_infos_resp_0="";
			
			$sql="SELECT rp.*, r.resp_legal FROM resp_pers rp, responsables2 r WHERE r.ele_id='$eleve_ele_id' AND r.pers_id=rp.pers_id AND (r.resp_legal='1' OR r.resp_legal='2' OR (r.pers_contact='1' AND (rp.tel_pers!='' OR rp.tel_prof!='' OR rp.tel_port!='')));";
			$res_tmp=mysqli_query($GLOBALS["mysqli"], $sql);
			if(mysqli_num_rows($res_tmp)>0) {
				while($lig_tmp=mysqli_fetch_object($res_tmp)) {
					if(($tab_acces_tel_resp['acces_global'])||(in_array($lig_tmp->pers_id, $tab_acces_tel_resp['pers_id']))) {
						if($lig_tmp->resp_legal=='1') {
							$eleve_infos_resp_1="$lig_tmp->civilite $lig_tmp->nom $lig_tmp->prenom;$lig_tmp->tel_pers;$lig_tmp->tel_prof;$lig_tmp->tel_port";
						}
						elseif($lig_tmp->resp_legal=='2') {
							$eleve_infos_resp_2="$lig_tmp->civilite $lig_tmp->nom $lig_tmp->prenom;$lig_tmp->tel_pers;$lig_tmp->tel_prof;$lig_tmp->tel_port";
						}
						else {
							if($eleve_infos_resp_0!="") {$eleve_infos_resp_0.=";";}
							$eleve_infos_resp_0.="$lig_tmp->civilite $lig_tmp->nom $lig_tmp->prenom;$lig_tmp->tel_pers;$lig_tmp->tel_prof;$lig_tmp->tel_port";
						}
					}
					else {
						if($lig_tmp->resp_legal=='1') {
							$eleve_infos_resp_1="$lig_tmp->civilite $lig_tmp->nom $lig_tmp->prenom;;;";
						}
						elseif($lig_tmp->resp_legal=='2') {
							$eleve_infos_resp_2="$lig_tmp->civilite $lig_tmp->nom $lig_tmp->prenom;;;";
						}
						else {
							if($eleve_infos_resp_0!="") {$eleve_infos_resp_0.=";";}
							$eleve_infos_resp_0.="$lig_tmp->civilite $lig_tmp->nom $lig_tmp->prenom;;;";
						}
					}
				}
			}
		}

		//$fd.="$eleve_classe;$eleve_login;$eleve_nom;$eleve_prenom;$eleve_sexe;$eleve_naissance\n";

		$ligne="";
		if((isset($avec_classe))&&($avec_classe=='y')) {$ligne.="$eleve_classe;";}
		if((isset($avec_login))&&($avec_login=='y')) {$ligne.="$eleve_login;";}
		if((isset($avec_nom))&&($avec_nom=='y')) {$ligne.="$eleve_nom;";}
		if((isset($avec_prenom))&&($avec_prenom=='y')) {$ligne.="$eleve_prenom;";}
		if((isset($avec_sexe))&&($avec_sexe=='y')) {$ligne.="$eleve_sexe;";}
		if((isset($avec_naiss))&&($avec_naiss=='y')) {$ligne.="$eleve_naissance;";}
		if($avec_lieu_naiss=='y') {$ligne.="$eleve_lieu_naissance;";}

		if((isset($avec_email))&&($avec_email=='y')) {
			if(($tab_acces_mail_ele['acces_global'])||(in_array($eleve_login, $tab_acces_mail_ele['login_ele']))) {
				$ligne.="$eleve_email;";
			}
			else {
				$ligne.=";";
			}
		}

		if((isset($avec_statut))&&($avec_statut=='y')) {$ligne.="eleve;";}
		if($_SESSION['statut']!='professeur') {
			//if((isset($avec_ine))&&($avec_ine=='y')) {$ligne.="$eleve_no_gep;";}
			if((isset($avec_elenoet))&&($avec_elenoet=='y')) {$ligne.="$eleve_elenoet;";}
			if((isset($avec_ele_id))&&($avec_ele_id=='y')) {$ligne.="$eleve_ele_id;";}
			if((isset($avec_no_gep))&&($avec_no_gep=='y')) {$ligne.="$eleve_no_gep;";}
		}
		if((isset($avec_doublant))&&($avec_doublant=='y')) {$ligne.="$eleve_doublant;";}
		if((isset($avec_regime))&&($avec_regime=='y')) {$ligne.="$eleve_regime;";}

		if((isset($avec_infos_resp))&&($avec_infos_resp=='y')) {$ligne.=$eleve_infos_resp_1.";".$eleve_infos_resp_2.";".$eleve_infos_resp_0.";";}

		// Suppression du ; en fin de ligne
		$ligne=preg_replace('/;$/','',$ligne);

		$fd.=$ligne."\n";
	}
} elseif(isset($tab_aid)) {
	if($_SESSION['statut']!='professeur') {
		if((isset($avec_prof))&&($avec_prof=='y')) {
			$sql="SELECT u.login, u.nom, u.prenom, u.email, u.civilite, u.numind FROM utilisateurs u, j_aid_utilisateurs jau WHERE jau.id_utilisateur=u.login AND jau.id_aid='$id_aid';";
			//echo "$sql<br />\n";
			$res_prof=mysqli_query($GLOBALS["mysqli"], $sql);
			if(mysqli_num_rows($res_prof)>0) {
				while($lig=mysqli_fetch_object($res_prof)) {
					$ligne="";
					if((isset($avec_classe))&&($avec_classe=='y')) {$ligne.=";";}
					if((isset($avec_login))&&($avec_login=='y')) {$ligne.="$lig->login;";}
					if((isset($avec_nom))&&($avec_nom=='y')) {$ligne.="$lig->nom;";}
					if((isset($avec_prenom))&&($avec_prenom=='y')) {$ligne.="$lig->prenom;";}
					if((isset($avec_sexe))&&($avec_sexe=='y')) {$ligne.="$lig->civilite;";}
					if((isset($avec_naiss))&&($avec_naiss=='y')) {$ligne.=";";}
					if((isset($avec_lieu_naiss))&&($avec_lieu_naiss=='y')) {$ligne.=";";}
					if((isset($avec_email))&&($avec_email=='y')) {$ligne.="$lig->email;";}
					if((isset($avec_statut))&&($avec_statut=='y')) {$ligne.="professeur;";}
					if($_SESSION['statut']!='professeur') {
						//if((isset($avec_ine))&&($avec_ine=='y')) {$ligne.=";";}
						if((isset($avec_elenoet))&&($avec_elenoet=='y')) {$ligne.="$lig->numind;";}
						if((isset($avec_ele_id))&&($avec_ele_id=='y')) {$ligne.=";";}
						if((isset($avec_no_gep))&&($avec_no_gep=='y')) {$ligne.=";";}
					}
				
					// Suppression du ; en fin de ligne
					$ligne=preg_replace('/;$/','',$ligne);
			
					$fd.=$ligne."\n";
				}
			}
		}
	}

	/*
	echo "<pre>";
	print_r($tab_aid);
	echo "</pre>";
	*/

	if(isset($tab_aid["eleves"][$periode_num]["users"])) {
		foreach($tab_aid["eleves"][$periode_num]["users"] as $current_eleve) {
			$eleve_login = $current_eleve["login"];
			$eleve_nom = $current_eleve["nom"];
			$eleve_prenom = $current_eleve["prenom"];

			//$eleve_classe = $current_eleve["classe"];
			$sql="SELECT classe FROM classes WHERE id='".$current_eleve["classe"]."'";
			$res_tmp=mysqli_query($GLOBALS["mysqli"], $sql);
			if(mysqli_num_rows($res_tmp)==0){
				die("$eleve_login ne serait dans aucune classe???</body></html>");
			}
			else{
				$lig_tmp=mysqli_fetch_object($res_tmp);
				$eleve_classe=$lig_tmp->classe;
			}

			// La fonction get_group() dans /lib/groupes.inc.php ne récupère pas le sexe et la date de naissance...
			// ... pourrait-on l'ajouter?
			$sql="SELECT sexe,naissance,lieu_naissance,email,no_gep,elenoet,ele_id FROM eleves WHERE login='$eleve_login'";
			$res_tmp=mysqli_query($GLOBALS["mysqli"], $sql);

			if(mysqli_num_rows($res_tmp)==0){
				die("Problème avec les infos (date de naissance, sexe,...) de $eleve_login</body></html>");
			}
			else{
				$lig_tmp=mysqli_fetch_object($res_tmp);
				$eleve_sexe=$lig_tmp->sexe;
				if((isset($format_naiss))&&($format_naiss=='jjmmaaaa')) {
					$eleve_naissance=formate_date($lig_tmp->naissance);
				}
				else {
					$eleve_naissance=$lig_tmp->naissance;
				}
				$eleve_email=$lig_tmp->email;
				$eleve_no_gep=$lig_tmp->no_gep;
				$eleve_elenoet=$lig_tmp->elenoet;
				$eleve_ele_id=$lig_tmp->ele_id;

				if($avec_lieu_naiss=='y') {
					$eleve_lieu_naissance=get_commune($lig_tmp->lieu_naissance,'2');
				}
			}

			if(((isset($avec_doublant))&&($avec_doublant=='y'))||
			((isset($avec_regime))&&($avec_regime=='y'))) {
				$sql="SELECT * FROM j_eleves_regime WHERE login='".$current_eleve["login"]."';";
				$res_tmp=mysqli_query($GLOBALS["mysqli"], $sql);
				if(mysqli_num_rows($res_tmp)==0) {
					//die("Problème avec les infos (régime, doublant) de $eleve_login</body></html>");
					$eleve_regime="X";
					$eleve_doublant="X";
				}
				else {
					while($lig_tmp=mysqli_fetch_object($res_tmp)) {
						$eleve_regime=$lig_tmp->regime;
						$eleve_doublant=$lig_tmp->doublant;
					}
				}
			}

			if((isset($avec_infos_resp))&&($avec_infos_resp=='y')) {
				$eleve_infos_resp_1="";
				$eleve_infos_resp_2="";
				$eleve_infos_resp_0="";
			
				$sql="SELECT rp.*, r.resp_legal FROM resp_pers rp, responsables2 r WHERE r.ele_id='$eleve_ele_id' AND r.pers_id=rp.pers_id AND (r.resp_legal='1' OR r.resp_legal='2' OR (r.pers_contact='1' AND (rp.tel_pers!='' OR rp.tel_prof!='' OR rp.tel_port!='')));";
				$res_tmp=mysqli_query($GLOBALS["mysqli"], $sql);
				if(mysqli_num_rows($res_tmp)>0) {
					while($lig_tmp=mysqli_fetch_object($res_tmp)) {
						if(($tab_acces_tel_resp['acces_global'])||(in_array($lig_tmp->pers_id, $tab_acces_tel_resp['pers_id']))) {
							if($lig_tmp->resp_legal=='1') {
								$eleve_infos_resp_1="$lig_tmp->civilite $lig_tmp->nom $lig_tmp->prenom;$lig_tmp->tel_pers;$lig_tmp->tel_prof;$lig_tmp->tel_port";
							}
							elseif($lig_tmp->resp_legal=='2') {
								$eleve_infos_resp_2="$lig_tmp->civilite $lig_tmp->nom $lig_tmp->prenom;$lig_tmp->tel_pers;$lig_tmp->tel_prof;$lig_tmp->tel_port";
							}
							else {
								if($eleve_infos_resp_0!="") {$eleve_infos_resp_0.=";";}
								$eleve_infos_resp_0.="$lig_tmp->civilite $lig_tmp->nom $lig_tmp->prenom;$lig_tmp->tel_pers;$lig_tmp->tel_prof;$lig_tmp->tel_port";
							}
						}
						else {
							if($lig_tmp->resp_legal=='1') {
								$eleve_infos_resp_1="$lig_tmp->civilite $lig_tmp->nom $lig_tmp->prenom;;;";
							}
							elseif($lig_tmp->resp_legal=='2') {
								$eleve_infos_resp_2="$lig_tmp->civilite $lig_tmp->nom $lig_tmp->prenom;;;";
							}
							else {
								if($eleve_infos_resp_0!="") {$eleve_infos_resp_0.=";";}
								$eleve_infos_resp_0.="$lig_tmp->civilite $lig_tmp->nom $lig_tmp->prenom;;;";
							}
						}
					}
				}
			}

			//$fd.="$eleve_classe;$eleve_login;$eleve_nom;$eleve_prenom;$eleve_sexe;$eleve_naissance\n";

			$ligne="";
			if((isset($avec_classe))&&($avec_classe=='y')) {$ligne.="$eleve_classe;";}
			if((isset($avec_login))&&($avec_login=='y')) {$ligne.="$eleve_login;";}
			if((isset($avec_nom))&&($avec_nom=='y')) {$ligne.="$eleve_nom;";}
			if((isset($avec_prenom))&&($avec_prenom=='y')) {$ligne.="$eleve_prenom;";}
			if((isset($avec_sexe))&&($avec_sexe=='y')) {$ligne.="$eleve_sexe;";}
			if((isset($avec_naiss))&&($avec_naiss=='y')) {$ligne.="$eleve_naissance;";}
			if($avec_lieu_naiss=='y') {$ligne.="$eleve_lieu_naissance;";}

			if((isset($avec_email))&&($avec_email=='y')) {
				if(($tab_acces_mail_ele['acces_global'])||(in_array($eleve_login, $tab_acces_mail_ele['login_ele']))) {
					$ligne.="$eleve_email;";
				}
				else {
					$ligne.=";";
				}
			}

			if((isset($avec_statut))&&($avec_statut=='y')) {$ligne.="eleve;";}
			if($_SESSION['statut']!='professeur') {
				//if((isset($avec_ine))&&($avec_ine=='y')) {$ligne.="$eleve_no_gep;";}
				if((isset($avec_elenoet))&&($avec_elenoet=='y')) {$ligne.="$eleve_elenoet;";}
				if((isset($avec_ele_id))&&($avec_ele_id=='y')) {$ligne.="$eleve_ele_id;";}
				if((isset($avec_no_gep))&&($avec_no_gep=='y')) {$ligne.="$eleve_no_gep;";}
			}
			if((isset($avec_doublant))&&($avec_doublant=='y')) {$ligne.="$eleve_doublant;";}
			if((isset($avec_regime))&&($avec_regime=='y')) {$ligne.="$eleve_regime;";}

			if((isset($avec_infos_resp))&&($avec_infos_resp=='y')) {$ligne.=$eleve_infos_resp_1.";".$eleve_infos_resp_2.";".$eleve_infos_resp_0.";";}

			// Suppression du ; en fin de ligne
			$ligne=preg_replace('/;$/','',$ligne);

			$fd.=$ligne."\n";
		}
	}
} elseif((getSettingAOui('active_mod_actions'))&&(isset($id_action))&&(preg_match('/^[0-9]{1,}$/', $id_action))) {
	// Contrôler si on a accès à l'action?
	if($_SESSION['statut']=='professeur') {
		$sql="SELECT 1=1 FROM mod_actions_gestionnaires mag, 
						mod_actions_action maa 
					WHERE maa.id_categorie=mag.id_categorie AND 
						mag.login_user='".$_SESSION['login']."' AND 
						maa.id='".$id_action."';";
		$test=mysqli_query($mysqli, $sql);
		if(mysqli_num_rows($test)==0) {
			$fd="Accès non autorisé.";
		}
		else {
			$sql="SELECT e.* FROM mod_actions_inscriptions mai, 
							eleves e 
						WHERE mai.login_ele=e.login AND 
							mai.id_action='".$id_action."'";
			if(isset($mode)&&$mode=='presents') {
				$sql.=" AND 
							mai.presence='y'";
			}
			$sql.=";";
			$test=mysqli_query($mysqli, $sql);
			if(mysqli_num_rows($test)>0) {
				$mysql_date=strftime('%Y-%m-%d %H:%M:%S');
				while($lig=mysqli_fetch_object($test)) {
					//$fd.="CLASSE;LOGIN;NOM;PRENOM;SEXE;DATE_NAISS\n";
					$classe='';
					$current_classe=get_clas_ele_telle_date($lig->login, $mysql_date);
					if(isset($current_classe['classe'])) {
						$classe=$current_classe['classe'];
					}
					$fd.=$classe.';'.$lig->login.';'.$lig->nom.';'.$lig->prenom.';'.$lig->sexe.';'.formate_date($lig->naissance).';'."\r\n";
				}
			}
		}
	}
	elseif(in_array($_SESSION['statut'], array('administrateur', 'scolarite', 'cpe'))) {
		$sql="SELECT e.* FROM mod_actions_inscriptions mai, 
						eleves e 
					WHERE mai.login_ele=e.login AND 
						mai.id_action='".$id_action."'";
		if(isset($mode)&&$mode=='presents') {
			$sql.=" AND 
						mai.presence='y'";
		}
		$sql.=";";
		$test=mysqli_query($mysqli, $sql);
		if(mysqli_num_rows($test)>0) {
			$mysql_date=strftime('%Y-%m-%d %H:%M:%S');
			while($lig=mysqli_fetch_object($test)) {
				//$fd.="CLASSE;LOGIN;NOM;PRENOM;SEXE;DATE_NAISS\n";
				$classe='';
				$current_classe=get_clas_ele_telle_date($lig->login, $mysql_date);
				if(isset($current_classe['classe'])) {
					$classe=$current_classe['classe'];
				}
				$fd.=$classe.';'.$lig->login.';'.$lig->nom.';'.$lig->prenom.';'.$lig->sexe.';'.formate_date($lig->naissance).';'."\r\n";
			}
		}

	}
	else {
		$fd="Accès non autorisé.";
	}
} else {
	$tab_classe=array();
	if($id_classe=="toutes") {
		// Faut-il restreindre à ses propres classes?
		//$sql="SELECT DISTINCT c.id,c.classe FROM classes c ORDER BY classe";
		$sql=retourne_sql_mes_classes();
		$result_classes=mysqli_query($GLOBALS["mysqli"], $sql);
		while($lig=mysqli_fetch_object($result_classes)) {
			$tab_classe[]=$lig->id;
		}
	}
	else {
		$tab_classe[]=$id_classe;
	}

	/*
	echo "<pre>";
	print_r($tab_classe);
	echo "</pre>";
	*/

	foreach($tab_classe as $id_classe) {
		if(((isset($avec_doublant))&&($avec_doublant=='y'))||
		((isset($avec_regime))&&($avec_regime=='y'))) {
			$sql="SELECT DISTINCT e.*, jer.doublant, jer.regime
				FROM eleves e, j_eleves_classes j, j_eleves_regime jer
				WHERE (
				j.id_classe='".$id_classe."' AND
				j.login = e.login AND
				periode='".$periode_num."' AND
				jer.login=e.login
				) ORDER BY nom, prenom";
		}
		else {
			$sql="SELECT DISTINCT e.*
				FROM eleves e, j_eleves_classes j
				WHERE (
				j.id_classe='".$id_classe."' AND
				j.login = e.login AND
				periode='".$periode_num."'
				) ORDER BY nom, prenom";
		}
		$appel_donnees_eleves = mysqli_query($GLOBALS["mysqli"], $sql);
		$nombre_lignes = mysqli_num_rows($appel_donnees_eleves);
		$i = 0;
		//while($i < $nombre_lignes) {
		while($lig_ele=mysqli_fetch_object($appel_donnees_eleves)) {
			$classe=get_nom_classe($id_classe);

			$eleve_login = $lig_ele->login;
			$eleve_nom = $lig_ele->nom;
			$eleve_prenom = $lig_ele->prenom;
			$eleve_sexe = $lig_ele->sexe;
			$eleve_naissance = $lig_ele->naissance;
			if((isset($format_naiss))&&($format_naiss=='jjmmaaaa')) {
				$eleve_naissance=formate_date($eleve_naissance);
			}
			if($avec_lieu_naiss=='y') {
				$eleve_lieu_naissance=get_commune($lig_ele->lieu_naissance,'2');
			}

			//$fd.="$classe;$eleve_login;$eleve_nom;$eleve_prenom;$eleve_sexe;$eleve_naissance\n";

			$eleve_email=$lig_ele->email;
			$eleve_no_gep=$lig_ele->no_gep;
			$eleve_elenoet=$lig_ele->elenoet;
			$eleve_ele_id=$lig_ele->ele_id;

			if(((isset($avec_doublant))&&($avec_doublant=='y'))||
			((isset($avec_regime))&&($avec_regime=='y'))) {
				$eleve_doublant=$lig_ele->doublant;
				$eleve_regime=$lig_ele->regime;
			}

			if((isset($avec_infos_resp))&&($avec_infos_resp=='y')) {
				$eleve_infos_resp_1="";
				$eleve_infos_resp_2="";
				$eleve_infos_resp_0="";
			
				$sql="SELECT rp.*, r.resp_legal FROM resp_pers rp, responsables2 r WHERE r.ele_id='$eleve_ele_id' AND r.pers_id=rp.pers_id AND (r.resp_legal='1' OR r.resp_legal='2' OR (r.pers_contact='1' AND (rp.tel_pers!='' OR rp.tel_prof!='' OR rp.tel_port!='')));";
				$res_tmp=mysqli_query($GLOBALS["mysqli"], $sql);
				if(mysqli_num_rows($res_tmp)>0) {
					while($lig_tmp=mysqli_fetch_object($res_tmp)) {
						if(($tab_acces_tel_resp['acces_global'])||(in_array($lig_tmp->pers_id, $tab_acces_tel_resp['pers_id']))) {
							if($lig_tmp->resp_legal=='1') {
								$eleve_infos_resp_1="$lig_tmp->civilite $lig_tmp->nom $lig_tmp->prenom;$lig_tmp->tel_pers;$lig_tmp->tel_prof;$lig_tmp->tel_port";
							}
							elseif($lig_tmp->resp_legal=='2') {
								$eleve_infos_resp_2="$lig_tmp->civilite $lig_tmp->nom $lig_tmp->prenom;$lig_tmp->tel_pers;$lig_tmp->tel_prof;$lig_tmp->tel_port";
							}
							else {
								if($eleve_infos_resp_0!="") {$eleve_infos_resp_0.=";";}
								$eleve_infos_resp_0.="$lig_tmp->civilite $lig_tmp->nom $lig_tmp->prenom;$lig_tmp->tel_pers;$lig_tmp->tel_prof;$lig_tmp->tel_port";
							}
						}
						else {
							if($lig_tmp->resp_legal=='1') {
								$eleve_infos_resp_1="$lig_tmp->civilite $lig_tmp->nom $lig_tmp->prenom;;;";
							}
							elseif($lig_tmp->resp_legal=='2') {
								$eleve_infos_resp_2="$lig_tmp->civilite $lig_tmp->nom $lig_tmp->prenom;;;";
							}
							else {
								if($eleve_infos_resp_0!="") {$eleve_infos_resp_0.=";";}
								$eleve_infos_resp_0.="$lig_tmp->civilite $lig_tmp->nom $lig_tmp->prenom;;;";
							}
						}
					}
				}
			}

			$ligne="";
			if((isset($avec_classe))&&($avec_classe=='y')) {$ligne.="$classe;";}
			if((isset($avec_login))&&($avec_login=='y')) {$ligne.="$eleve_login;";}
			if((isset($avec_nom))&&($avec_nom=='y')) {$ligne.="$eleve_nom;";}
			if((isset($avec_prenom))&&($avec_prenom=='y')) {$ligne.="$eleve_prenom;";}
			if((isset($avec_sexe))&&($avec_sexe=='y')) {$ligne.="$eleve_sexe;";}
			if((isset($avec_naiss))&&($avec_naiss=='y')) {$ligne.="$eleve_naissance;";}
			if((isset($avec_lieu_naiss))&&($avec_lieu_naiss=='y')) {$ligne.="$eleve_lieu_naissance;";}

			if((isset($avec_email))&&($avec_email=='y')) {
				if(($tab_acces_mail_ele['acces_global'])||(in_array($eleve_login, $tab_acces_mail_ele['login_ele']))) {
					$ligne.="$eleve_email;";
				}
				else {
					$ligne.=";";
				}
			}

			if((isset($avec_statut))&&($avec_statut=='y')) {$ligne.="eleve;";}
			if($_SESSION['statut']!='professeur') {
				//if((isset($avec_ine))&&($avec_ine=='y')) {$ligne.="$eleve_no_gep;";}
				if((isset($avec_elenoet))&&($avec_elenoet=='y')) {$ligne.="$eleve_elenoet;";}
				if((isset($avec_ele_id))&&($avec_ele_id=='y')) {$ligne.="$eleve_ele_id;";}
				if((isset($avec_no_gep))&&($avec_no_gep=='y')) {$ligne.="$eleve_no_gep;";}
			}

			if((isset($avec_doublant))&&($avec_doublant=='y')) {$ligne.="$eleve_doublant;";}
			if((isset($avec_regime))&&($avec_regime=='y')) {$ligne.="$eleve_regime;";}

			if((isset($avec_infos_resp))&&($avec_infos_resp=='y')) {$ligne.=$eleve_infos_resp_1.";".$eleve_infos_resp_2.";".$eleve_infos_resp_0.";";}

			// Suppression du ; en fin de ligne
			$ligne=preg_replace('/;$/','',$ligne);

			$fd.=$ligne."\n";

			$i++;
		}
	}
}
echo echo_csv_encoded($fd);

?>
