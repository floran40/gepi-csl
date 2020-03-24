<?php
/*
*
*  Copyright 2001, 2018 Thomas Belliard, Laurent Delineau, Edouard Hue, Eric Lebrun, Stephane Boireau
*
* This file is part of GEPI.
*
* GEPI is free software; you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation; either version 2 of the License, or
* (at your option) any later version.
*
* GEPI is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
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

if (!checkAccess()) {
	header("Location: ../logout.php?auto=1");
	die();
}

$no_header=isset($_POST['no_header']) ? $_POST['no_header'] : (isset($_GET['no_header']) ? $_GET['no_header'] : 'n');

$ajout_href_1="";
$ajout_href_2="";
$ajout_form="";
if($no_header!='y') {
	$titre_page = "Equipe pédagogique";
}
else {
	$ajout_href_1="?no_header=y";
	$ajout_href_2="&amp;no_header=y";
	$ajout_form="<input type='hidden' name='no_header' value='y' />\n";

}

$id_classe=isset($_GET['id_classe']) ? $_GET["id_classe"] : (isset($_POST['id_classe']) ? $_POST["id_classe"] : NULL);
$export=isset($_GET['export']) ? $_GET["export"] : (isset($_POST['export']) ? $_POST["export"] : NULL);

$tabmail=array();
$acces_classe="n";
// Remplissage d'un tableau pour la classe choisie
if((isset($id_classe))&&(is_numeric($id_classe))) {
	$acces_classe="y";

	if(($_SESSION['statut']=='professeur')&&(getSettingValue("GepiAccesVisuToutesEquipProf")!="yes")){
		$test_prof_classe = sql_count(sql_query("SELECT login FROM j_groupes_classes jgc,j_groupes_professeurs jgp WHERE jgp.login = '".$_SESSION['login']."' AND jgc.id_groupe=jgp.id_groupe AND jgc.id_classe='$id_classe'"));
		if($test_prof_classe==0) {
			$acces_classe="n";
		}
	}
	// On vérifie les droits donnés par l'administrateur
	if((getSettingValue("GepiAccesVisuToutesEquipCpe") == "yes") AND $_SESSION['statut']=='cpe'){
		//echo '<p style="font-size: 0.7em; color: green;">L\'administrateur vous a donné l\'accès à toutes les classes.</p>';
		$acces_classe="y";
	}elseif($_SESSION['statut']=='cpe'){
		$test_cpe_classe = sql_count(sql_query("SELECT e_login FROM j_eleves_cpe jec,j_eleves_classes jecl WHERE jec.cpe_login = '".$_SESSION['login']."' AND jec.e_login=jecl.login AND jecl.id_classe='$id_classe'"));
		if($test_cpe_classe==0){
			$acces_classe="n";
		}
	}

	if($acces_classe=="y") {
		$classe=get_classe($id_classe);

		include("../lib/periodes.inc.php");

		$tab_enseignements=array();
		$tab_mail=array();
		$cpt=0;

		// Liste des CPE:
		$sql="SELECT DISTINCT u.nom,u.prenom,u.email,jec.cpe_login FROM utilisateurs u,j_eleves_cpe jec,j_eleves_classes jecl WHERE jec.e_login=jecl.login AND jecl.id_classe='$id_classe' AND u.login=jec.cpe_login ORDER BY u.nom, u.prenom, jec.cpe_login";
		$result_cpe=mysqli_query($GLOBALS["mysqli"], $sql);
		if(mysqli_num_rows($result_cpe)>0){
			$tab_enseignements[$cpt]['id_groupe']="VIE_SCOLAIRE";
			$tab_enseignements[$cpt]['grp_name']="VIE SCOLAIRE";
			$tab_enseignements[$cpt]['grp_description']="VIE SCOLAIRE";

			for($loop=0;$loop<count($nom_periode);$loop++) {
				$sql="SELECT DISTINCT nom,prenom FROM eleves e,j_eleves_cpe jec,j_eleves_classes jecl WHERE jec.e_login=jecl.login AND jec.e_login=e.login AND jecl.id_classe='$id_classe' AND jecl.periode='".($loop+1)."';";
				$result_eleve=mysqli_query($GLOBALS["mysqli"], $sql);
				$tab_enseignements[$cpt]['nb_eleves'][$loop+1]=mysqli_num_rows($result_eleve);
			}

			$cpt2=0;
			while($lig_cpe=mysqli_fetch_object($result_cpe)) {

				$tab_enseignements[$cpt]['prof'][$cpt2]['login']=$lig_cpe->cpe_login;
				$tab_enseignements[$cpt]['prof'][$cpt2]['statut']="cpe";
				$tab_enseignements[$cpt]['prof'][$cpt2]['designation_prof']=my_strtoupper($lig_cpe->nom)." ".casse_mot($lig_cpe->prenom,'majf2');
				if($lig_cpe->email!=""){
					$tab_enseignements[$cpt]['prof'][$cpt2]['designation_prof_mailto']="<a href='mailto:$lig_cpe->email?".urlencode("subject=".getSettingValue('gepiPrefixeSujetMail')."[GEPI] classe=".$classe['classe'])."' title=\"Envoyer un mail\">".my_strtoupper($lig_cpe->nom)." ".casse_mot($lig_cpe->prenom,'majf2')."</a>";
					$tab_enseignements[$cpt]['prof'][$cpt2]['mail']=$lig_cpe->email;
					$tabmail[]=$lig_cpe->email;
				}

				for($loop=0;$loop<count($nom_periode);$loop++) {
					$sql="SELECT DISTINCT nom,prenom FROM eleves e,j_eleves_cpe jec,j_eleves_classes jecl WHERE jec.e_login=jecl.login AND jec.e_login=e.login AND jecl.id_classe='$id_classe' AND jec.cpe_login='$lig_cpe->cpe_login' AND jecl.periode='".($loop+1)."';";
					$result_eleve=mysqli_query($GLOBALS["mysqli"], $sql);
					$tab_enseignements[$cpt]['prof'][$cpt2]['nb_eleves'][$loop+1]=mysqli_num_rows($result_eleve);
				}

				$cpt2++;
			}
			$cpt++;
		}

		// Liste des enseignements et professeurs:
		$sql="SELECT m.nom_complet,jgm.id_groupe, g.name, g.description FROM j_groupes_classes jgc, j_groupes_matieres jgm, matieres m, groupes g WHERE jgc.id_groupe=jgm.id_groupe AND m.matiere=jgm.id_matiere AND jgc.id_classe='$id_classe' AND g.id=jgc.id_groupe ORDER BY jgc.priorite, m.matiere";
		//echo "$sql<br />";
		$result_grp=mysqli_query($GLOBALS["mysqli"], $sql);
		while($lig_grp=mysqli_fetch_object($result_grp)){
			$tab_enseignements[$cpt]['id_groupe']=$lig_grp->id_groupe;
			$tab_enseignements[$cpt]['grp_name']=$lig_grp->name;
			$tab_enseignements[$cpt]['matiere_nom_complet']=$lig_grp->nom_complet;
			$tab_enseignements[$cpt]['grp_description']=$lig_grp->description;

			// Le groupe est-il composé uniquement d'élèves de la classe?
			$sql="SELECT * FROM j_groupes_classes jgc WHERE jgc.id_groupe='$lig_grp->id_groupe'";
			$res_nb_class_grp=mysqli_query($GLOBALS["mysqli"], $sql);
			$nb_class_grp=mysqli_num_rows($res_nb_class_grp);
			$tab_enseignements[$cpt]['nb_class_grp']=$nb_class_grp;

			for($loop=0;$loop<count($nom_periode);$loop++) {
				// Récupération des effectifs du groupe...
				// ... parmi les membres de la classe
				$sql="SELECT DISTINCT e.nom,e.prenom,c.classe FROM j_eleves_groupes jeg, 
																	eleves e, 
																	j_eleves_classes jec, 
																	j_groupes_classes jgc, 
																	classes c 
																WHERE jeg.login=e.login AND 
																	jeg.id_groupe='$lig_grp->id_groupe' AND 
																	jgc.id_classe=c.id AND 
																	jgc.id_groupe=jeg.id_groupe AND 
																	jec.id_classe=c.id AND 
																	jec.login=e.login AND 
																	c.id='$id_classe' AND 
																	jeg.periode=jec.periode AND 
																	jec.periode='".($loop+1)."' 
																ORDER BY e.nom,e.prenom";
				$res_eleves=mysqli_query($GLOBALS["mysqli"], $sql);
				$nb_eleves=mysqli_num_rows($res_eleves);
				$tab_enseignements[$cpt]['nb_eleves'][$loop+1]=$nb_eleves;

				if($nb_class_grp>1){
					// Effectif...
					// ... pour tout le groupe
					$sql="SELECT DISTINCT e.nom,e.prenom,c.classe FROM j_eleves_groupes jeg, 
																		eleves e, 
																		j_eleves_classes jec, 
																		j_groupes_classes jgc, 
																		classes c 
																	WHERE jeg.login=e.login AND 
																		jeg.id_groupe='$lig_grp->id_groupe' AND 
																		jgc.id_classe=c.id AND 
																		jgc.id_groupe=jeg.id_groupe AND 
																		jec.id_classe=c.id AND 
																		jeg.periode=jec.periode AND 
																		jec.periode='".($loop+1)."' AND 
																		jec.login=e.login 
																	ORDER BY e.nom,e.prenom";
					$res_tous_eleves_grp=mysqli_query($GLOBALS["mysqli"], $sql);
					$nb_tous_eleves_grp=mysqli_num_rows($res_tous_eleves_grp);

					$tab_enseignements[$cpt]['nb_tous_eleves_grp'][$loop+1]=$nb_tous_eleves_grp;
				}
			}


			// Professeurs
			$sql="SELECT jgp.login,u.nom,u.prenom,u.email FROM j_groupes_professeurs jgp,utilisateurs u WHERE jgp.id_groupe='$lig_grp->id_groupe' AND u.login=jgp.login";
			//echo "$sql<br />";
			$result_prof=mysqli_query($GLOBALS["mysqli"], $sql);
			$cpt2=0;
			while($lig_prof=mysqli_fetch_object($result_prof)){
				$tab_enseignements[$cpt]['prof'][$cpt2]['login']=$lig_prof->login;
				$tab_enseignements[$cpt]['prof'][$cpt2]['statut']="professeur";

				$tab_enseignements[$cpt]['prof'][$cpt2]['designation_prof']=my_strtoupper($lig_prof->nom)." ".casse_mot($lig_prof->prenom,'majf2');
				if($lig_prof->email!=""){
					$tab_enseignements[$cpt]['prof'][$cpt2]['designation_prof_mailto']="<a href='mailto:$lig_prof->email?".urlencode("subject=".getSettingValue('gepiPrefixeSujetMail')."[GEPI] classe=".$classe['classe'])."' title=\"Envoyer un mail\">".my_strtoupper($lig_prof->nom)." ".casse_mot($lig_prof->prenom,'majf2')."</a>";
					$tab_enseignements[$cpt]['prof'][$cpt2]['mail']=$lig_prof->email;
					$tabmail[]=$lig_prof->email;
				}

				// Le prof est-il PP d'au moins un élève de la classe?
				$tab_enseignements[$cpt]['prof'][$cpt2]['is_pp']="n";
				$sql="SELECT * FROM j_eleves_professeurs WHERE id_classe='$id_classe' AND professeur='$lig_prof->login'";
				//echo " (<i>$sql</i>)\n";
				$res_pp=mysqli_query($GLOBALS["mysqli"], $sql);
				if(mysqli_num_rows($res_pp)>0){
					$tab_enseignements[$cpt]['prof'][$cpt2]['is_pp']="y";
				}
				$cpt2++;
			}
			$cpt++;
		}
	}
}

// Export CSV: on utilise le tableau $tab_enseignements
if((isset($id_classe))&&(is_numeric($id_classe))&&(isset($export))&&($export=='csv')&&($acces_classe=="y")) {
	$msg="";

	$csv="Identifiant;";
	$csv.="Enseignement;";
	$csv.="Matière;";
	for($loop=0;$loop<count($nom_periode);$loop++) {
		$csv.="Eff.".$nom_periode[$loop+1].";";
	}
	$csv.="Enseignants;Mails;\r\n";

	for($i=0;$i<count($tab_enseignements);$i++) {
		$csv.=$tab_enseignements[$i]['id_groupe'].";";
		$csv.=$tab_enseignements[$i]['grp_name'].";";
		if(isset($tab_enseignements[$i]['matiere_nom_complet'])) {
			$csv.=$tab_enseignements[$i]['matiere_nom_complet'].";";
		}
		else {
			$csv.=";";
		}
		for($loop=0;$loop<count($nom_periode);$loop++) {
			$csv.=$tab_enseignements[$i]['nb_eleves'][$loop+1].";";
		}

		for($loop=0;$loop<count($tab_enseignements[$i]['prof']);$loop++) {
			if($loop>0) {$csv.=", ";}
			$csv.=$tab_enseignements[$i]['prof'][$loop]['designation_prof'];
		}
		$csv.=";";

		$nb_mail=0;
		for($loop=0;$loop<count($tab_enseignements[$i]['prof']);$loop++) {
			if($nb_mail>0) {$csv.=", ";}
			if(isset($tab_enseignements[$i]['prof'][$loop]['mail'])) {
				$csv.=$tab_enseignements[$i]['prof'][$loop]['mail'];
				$nb_mail++;
			}
		}
		$csv.=";\r\n";
	}

	$nom_fic=remplace_accents("Equipe_pedagogique_".$classe['classe'], "all").".csv";
	send_file_download_headers('text/x-csv',$nom_fic);
	echo echo_csv_encoded($csv);
	die();
}

if((isset($_GET['export_prof_suivi']))&&(isset($export))&&($export=='csv')) {
	$msg="";

	if($_SESSION['statut']=='scolarite'){
		$sql="SELECT DISTINCT c.id,c.classe, c.suivi_par FROM classes c, j_scol_classes jsc WHERE jsc.id_classe=c.id AND jsc.login='".$_SESSION['login']."' ORDER BY classe";
	}
	if($_SESSION['statut']=='professeur'){
		$sql="SELECT DISTINCT c.id,c.classe, c.suivi_par FROM classes c,j_groupes_classes jgc,j_groupes_professeurs jgp WHERE jgp.login = '".$_SESSION['login']."' AND jgc.id_groupe=jgp.id_groupe AND jgc.id_classe=c.id ORDER BY c.classe";
	}
	if($_SESSION['statut']=='cpe'){
		$sql="SELECT DISTINCT c.id,c.classe, c.suivi_par FROM classes c,j_eleves_cpe jec,j_eleves_classes jecl WHERE jec.cpe_login = '".$_SESSION['login']."' AND jec.e_login=jecl.login AND jecl.id_classe=c.id ORDER BY c.classe";
	}
	if($_SESSION['statut']=='administrateur'){
		$sql="SELECT DISTINCT c.id,c.classe, c.suivi_par FROM classes c ORDER BY c.classe";
	}

	if(($_SESSION['statut']=='scolarite')&&(getSettingValue("GepiAccesVisuToutesEquipScol") =="yes")){
		$sql="SELECT DISTINCT c.id,c.classe, c.suivi_par FROM classes c ORDER BY c.classe";
	}
	if(($_SESSION['statut']=='cpe')&&(getSettingValue("GepiAccesVisuToutesEquipCpe") =="yes")){
		$sql="SELECT DISTINCT c.id,c.classe, c.suivi_par FROM classes c ORDER BY c.classe";
	}
	if(($_SESSION['statut']=='professeur')&&(getSettingValue("GepiAccesVisuToutesEquipProf") =="yes")){
		$sql="SELECT DISTINCT c.id,c.classe, c.suivi_par FROM classes c ORDER BY c.classe";
	}

	if(($_SESSION['statut']=='autre')&&(acces('/groupes/visu_profs_class.php', 'autre'))) {
		$sql="SELECT DISTINCT c.id,c.classe, c.suivi_par FROM classes c ORDER BY c.classe";
	}

	$result_classes=mysqli_query($GLOBALS["mysqli"], $sql);
	$nb_classes = mysqli_num_rows($result_classes);
	$tab_classe=array();
	$tab_suivi_par=array();
	if(mysqli_num_rows($result_classes)==0){
		$msg="<p>Il semble qu'aucune classe n'ait encore été créée...<br />... ou alors aucune classe ne vous a été attribuée.<br />Contactez l'administrateur pour qu'il effectue le paramétrage approprié dans la Gestion des classes.</p>\n";
	}
	else {
		$nb_classes=mysqli_num_rows($result_classes);
		while($lig_class=mysqli_fetch_object($result_classes)){
			$tab_classe[$lig_class->id]=$lig_class->classe;
			$tab_suivi_par[$lig_class->id]=$lig_class->suivi_par;
		}

		$pp=ucfirst(getSettingValue('gepi_prof_suivi'));
		$csv="Classe;".$pp.";Mails;Classe suivie par;\r\n";

		$tab_pp=get_tab_prof_suivi();
		foreach($tab_classe as $current_id_classe => $current_classe) {
			if(isset($tab_pp[$current_id_classe])) {
				for($loop=0;$loop<count($tab_pp[$current_id_classe]);$loop++) {
					$csv.=$current_classe.";".civ_nom_prenom($tab_pp[$current_id_classe][$loop]).";".get_mail_user($tab_pp[$current_id_classe][$loop]).";".$tab_suivi_par[$current_id_classe].";\r\n";
				}
			}
		}

		$nom_fic=remplace_accents($pp, "all").".csv";
		send_file_download_headers('text/x-csv',$nom_fic);
		echo echo_csv_encoded($csv);
		die();
	}
}

$javascript_specifique[] = "lib/tablekit";
$utilisation_tablekit="ok";
//**************** EN-TETE **************************************
require_once("../lib/header.inc.php");
//**************** FIN EN-TETE **********************************

if(isset($id_classe)){
	echo "<form action='".$_SERVER['PHP_SELF']."' name='form1' method='post'>\n";

	echo "<p class='bold'>";
	echo "<a href='".$_SERVER['PHP_SELF'].$ajout_href_1."'><img src='../images/icons/back.png' alt='Retour' class='back_link'/> Retour</a>";

	if (!is_numeric($id_classe)){
		echo "</p>\n";

		echo "<p><b>ERREUR</b>: Le numéro de classe choisi n'est pas valide.</p>\n";
		echo "<p><a href='".$_SERVER['PHP_SELF'].$ajout_href_1."'>Retour</a></p>\n";
	}
	else{
		// =================================
		// AJOUT: boireaus
		//$sql="SELECT id, classe FROM classes ORDER BY classe";
		if($_SESSION['statut']=='scolarite'){
			//$sql="SELECT id,classe FROM classes ORDER BY classe";
			$sql="SELECT DISTINCT c.id,c.classe, c.suivi_par FROM classes c, j_scol_classes jsc WHERE jsc.id_classe=c.id AND jsc.login='".$_SESSION['login']."' ORDER BY classe";
		}
		if($_SESSION['statut']=='professeur'){
			$sql="SELECT DISTINCT c.id,c.classe, c.suivi_par FROM classes c,j_groupes_classes jgc,j_groupes_professeurs jgp WHERE jgp.login = '".$_SESSION['login']."' AND jgc.id_groupe=jgp.id_groupe AND jgc.id_classe=c.id ORDER BY c.classe";
		}
		if($_SESSION['statut']=='cpe'){
			$sql="SELECT DISTINCT c.id,c.classe, c.suivi_par FROM classes c,j_eleves_cpe jec,j_eleves_classes jecl WHERE jec.cpe_login = '".$_SESSION['login']."' AND jec.e_login=jecl.login AND jecl.id_classe=c.id ORDER BY c.classe";
		}
		if($_SESSION['statut']=='administrateur'){
			$sql="SELECT DISTINCT c.id,c.classe, c.suivi_par FROM classes c ORDER BY c.classe";
		}

		if(($_SESSION['statut']=='scolarite')&&(getSettingValue("GepiAccesVisuToutesEquipScol") =="yes")){
			$sql="SELECT DISTINCT c.id,c.classe, c.suivi_par FROM classes c ORDER BY c.classe";
		}
		if(($_SESSION['statut']=='cpe')&&(getSettingValue("GepiAccesVisuToutesEquipCpe") =="yes")){
			$sql="SELECT DISTINCT c.id,c.classe, c.suivi_par FROM classes c ORDER BY c.classe";
		}
		if(($_SESSION['statut']=='professeur')&&(getSettingValue("GepiAccesVisuToutesEquipProf") =="yes")){
			$sql="SELECT DISTINCT c.id,c.classe, c.suivi_par FROM classes c ORDER BY c.classe";
		}

		if(($_SESSION['statut']=='autre')&&(acces('/groupes/visu_profs_class.php', 'autre'))) {
			$sql="SELECT DISTINCT c.id,c.classe, c.suivi_par FROM classes c ORDER BY c.classe";
		}

		$chaine_options_classes="";

		$res_class_tmp=mysqli_query($GLOBALS["mysqli"], $sql);
		if(mysqli_num_rows($res_class_tmp)>0){
			$id_class_prec=0;
			$id_class_suiv=0;
			$temoin_tmp=0;
			while($lig_class_tmp=mysqli_fetch_object($res_class_tmp)){
				if($lig_class_tmp->id==$id_classe){
					$chaine_options_classes.="<option value='$lig_class_tmp->id' selected='true'>$lig_class_tmp->classe</option>\n";
					$temoin_tmp=1;
					if($lig_class_tmp=mysqli_fetch_object($res_class_tmp)){
						$chaine_options_classes.="<option value='$lig_class_tmp->id'>$lig_class_tmp->classe</option>\n";
						$id_class_suiv=$lig_class_tmp->id;
					}
					else{
						$id_class_suiv=0;
					}
				}
				else {
					$chaine_options_classes.="<option value='$lig_class_tmp->id'>$lig_class_tmp->classe</option>\n";
				}
				if($temoin_tmp==0){
					$id_class_prec=$lig_class_tmp->id;
				}
			}
		}
		// =================================

		if($id_class_prec!=0){echo " | <a href='".$_SERVER['PHP_SELF']."?id_classe=$id_class_prec".$ajout_href_2."'>Classe précédente</a>";}
		if($chaine_options_classes!="") {
			echo " | <select name='id_classe' onchange=\"document.forms['form1'].submit();\">\n";
			echo $chaine_options_classes;
			echo "</select>\n";
		}
		if($id_class_suiv!=0){echo " | <a href='".$_SERVER['PHP_SELF']."?id_classe=$id_class_suiv".$ajout_href_2."'>Classe suivante</a>";}

		echo $ajout_form;
		
		echo "</p>\n";
		echo "</form>\n";

		$classe=get_classe($id_classe);

		$gepi_prof_suivi=getParamClasse($id_classe, 'gepi_prof_suivi', getSettingValue('gepi_prof_suivi'));

		function accord_pluriel($nombre){
			if($nombre>1){
				return "s";
			}
		}

		if(($_SESSION['statut']=='professeur')&&(getSettingValue("GepiAccesVisuToutesEquipProf")!="yes")){
			$test_prof_classe = sql_count(sql_query("SELECT login FROM j_groupes_classes jgc,j_groupes_professeurs jgp WHERE jgp.login = '".$_SESSION['login']."' AND jgc.id_groupe=jgp.id_groupe AND jgc.id_classe='$id_classe'"));
			if($test_prof_classe==0){
				echo "<p>ERREUR: Vous n'avez pas accès à cette classe.</p>\n";
				echo "</body></html>\n";
				die();
			}
		}
		// On vérifie les droits donnés par l'administrateur
		if((getSettingValue("GepiAccesVisuToutesEquipCpe") == "yes") AND $_SESSION['statut']=='cpe'){
			echo '<p style="font-size: 0.7em; color: green;">L\'administrateur vous a donné l\'accès à toutes les classes.</p>';
		}elseif($_SESSION['statut']=='cpe'){
			$test_cpe_classe = sql_count(sql_query("SELECT e_login FROM j_eleves_cpe jec,j_eleves_classes jecl WHERE jec.cpe_login = '".$_SESSION['login']."' AND jec.e_login=jecl.login AND jecl.id_classe='$id_classe'"));
			if($test_cpe_classe==0){
				echo "<p>ERREUR: Vous n'avez pas accès à cette classe.</p>\n";
				echo "</body></html>\n";
				die();
			}
		}

		echo "<h3>Equipe pédagogique de la classe de <strong>".$classe["classe"]."</strong> <a href='".$_SERVER['PHP_SELF']."?id_classe=$id_classe&amp;export=csv' class='noprint' title=\"Exporter l'équipe au format CSV (tableur)\" target='_blank'><img src='../images/icons/csv.png' class='icone16' alt='CSV' /></a>";
		echo "<span id='span_mail'></span>";
		if(peut_poster_message($_SESSION["statut"])) {
			echo "<span id='span_mod_alerte'></span>";
		}
		echo "</h3>\n";

		$suivi_par=get_valeur_champ("classes", "id='$id_classe'", "suivi_par");
		if($suivi_par!="") {
			echo "<p>Classe suivie par&nbsp;: <strong>$suivi_par</strong></p>";
		}

		echo "<script type='text/javascript' language='JavaScript'>
	var fen;
	function ouvre_popup(id_groupe,id_classe){
		eval(\"fen=window.open('popup.php?id_groupe=\"+id_groupe+\"&id_classe=\"+id_classe+\"','','width=400,height=400,menubar=yes,scrollbars=yes')\");
		setTimeout('fen.focus()',500);
	}

	function ouvre_popup2(id_groupe,id_classe, periode_num){
		eval(\"fen=window.open('popup.php?id_groupe=\"+id_groupe+\"&id_classe=\"+id_classe+\"&periode_num=\"+periode_num+\"','','width=400,height=400,menubar=yes,scrollbars=yes')\");
		setTimeout('fen.focus()',500);
	}
</script>\n";

		$acces_visu_groupes_prof=acces("/groupes/visu_groupes_prof.php", $_SESSION['statut']);

		$sql="SELECT DISTINCT login FROM j_eleves_classes WHERE id_classe='$id_classe'";
		$res_eleves_classe=mysqli_query($GLOBALS["mysqli"], $sql);
		$nb_eleves_classe=mysqli_num_rows($res_eleves_classe);

		if(count($tab_enseignements)==0) {
			echo "<p style='color:red'>Aucun enseignement.</p>\n";
			echo "<p><br /></p>\n";
			require("../lib/footer.inc.php");
			die();
		}

		$acces_edit_group=acces("/groupes/edit_group.php", $_SESSION['statut']);

		$chaine_alerte="";
		//echo "<div style='float:right; width:45%'>";
		echo "<table class='boireaus boireaus_alt boireaus_white_hover' border='1' summary='Equipe'>
	<tr>
		<th rowspan='2'>Enseignement</th>
		<th colspan='".count($nom_periode)."'>Effectifs</th>
		<th rowspan='2'>Personnel</th>
	</tr>
	<tr>";
		for($loop=0;$loop<count($nom_periode);$loop++) {
			echo "
		<th>".$nom_periode[$loop+1]."</th>";
		}
		echo "
	</tr>";

		for($i=0;$i<count($tab_enseignements);$i++) {
			// Enseignements
			echo "
	<tr class='white_hover' onmouseover=\"this.style.backgroundColor='white'\" onmouseout=\"this.style.backgroundColor=''\">
	<!--tr-->
		<td>";
			// AJOUTER DES LIENS VERS L'ENSEIGNEMENT SI ON A LE DROIT
			if(($acces_edit_group)&&($tab_enseignements[$i]['id_groupe']!="")&&(is_numeric($tab_enseignements[$i]['id_groupe']))) {
				echo "<a href='edit_group.php?id_groupe=".$tab_enseignements[$i]['id_groupe']."' title=\"Editer cet enseignement.";
				if(isset($tab_enseignements[$i]['matiere_nom_complet'])) {
					echo "\nMatière : ".$tab_enseignements[$i]['matiere_nom_complet']."\">".htmlspecialchars($tab_enseignements[$i]['grp_name'])."<br /><span style='font-size: x-small;'>".htmlspecialchars($tab_enseignements[$i]['grp_description'])."</a>\n";
				}
				else {
					echo "\">";
					echo htmlspecialchars($tab_enseignements[$i]['grp_name']);
					echo "</a>";
				}
			}
			else {
				if(isset($tab_enseignements[$i]['matiere_nom_complet'])) {
					echo "<span title=\"Matière : ".$tab_enseignements[$i]['matiere_nom_complet']."\">".htmlspecialchars($tab_enseignements[$i]['grp_name'])."<br /><span style='font-size: x-small;'>".htmlspecialchars($tab_enseignements[$i]['grp_description'])."</span></span>\n";
				}
				else {
					echo htmlspecialchars($tab_enseignements[$i]['grp_name']);
				}
			}

			echo "</td>";

			// Effectifs
			for($loop=0;$loop<count($nom_periode);$loop++) {
				echo "
		<td>";

				echo "<a href='javascript:ouvre_popup2(\"".$tab_enseignements[$i]['id_groupe']."\",\"$id_classe\", \"".($loop+1)."\");' style='font-weight:bold' title=\"";
				if((isset($tab_enseignements[$i]['nb_class_grp']))&&($tab_enseignements[$i]['nb_class_grp']>1)) {
					echo "Dans ce groupe de ".$tab_enseignements[$i]['nb_tous_eleves_grp'][$loop+1]." élèves, ".$tab_enseignements[$i]['nb_eleves'][$loop+1]." élèves sont en ".$classe['classe'].".\n";
				}
				echo "Afficher un listing de l'enseignement\"> ".$tab_enseignements[$i]['nb_eleves'][$loop+1]." ";
				//if ($tab_enseignements[$i]['nb_eleves'][$loop+1] > 1) { echo $gepiSettings['denomination_eleves'];} else { echo $gepiSettings['denomination_eleve'];}
				echo " </a>";

				if((isset($tab_enseignements[$i]['nb_class_grp']))&&($tab_enseignements[$i]['nb_class_grp']>1)) {
					echo "<span style='font-size:x-small;'> sur <a href='javascript:ouvre_popup(\"".$tab_enseignements[$i]['id_groupe']."\",\"\");' title='Groupe de ".$tab_enseignements[$i]['nb_tous_eleves_grp'][$loop+1]." élèves'>".$tab_enseignements[$i]['nb_tous_eleves_grp'][$loop+1]."</a></span>";
				}

				echo "</td>";
			}

			// Professeurs
			echo "
		<td>";

			if(isset($tab_enseignements[$i]['prof'])) {
				for($loop=0;$loop<count($tab_enseignements[$i]['prof']);$loop++) {
					if($loop>0) {
						echo "
			<br />";
					}

					if(($acces_visu_groupes_prof)&&($tab_enseignements[$i]['prof'][$loop]['statut']=="professeur")) {
						echo "<div style='float:right;width:16px;'><a href='../groupes/visu_groupes_prof.php?login_prof=".$tab_enseignements[$i]['prof'][$loop]['login']."' title='Voir les enseignements du professeur'><img src='../images/icons/chercher.png' class='icone16' alt='Voir' /></a></div>";
					}

					if(isset($tab_enseignements[$i]['prof'][$loop]['designation_prof_mailto'])) {
						echo $tab_enseignements[$i]['prof'][$loop]['designation_prof_mailto'];
					}
					else {
						echo $tab_enseignements[$i]['prof'][$loop]['designation_prof'];
					}

					if((isset($tab_enseignements[$i]['prof'][$loop]['is_pp']))&&($tab_enseignements[$i]['prof'][$loop]['is_pp']=="y")) {
						echo " (<i>".$gepi_prof_suivi."</i>)";
					}
				}
			}

			echo "</td>
	</tr>";
		}
		echo "
</table>\n";

		$chaine_mail="";
		if(count($tabmail)>0){
			unset($tabmail2);
			$tabmail2=array();
			//$tabmail=array_unique($tabmail);
			//sort($tabmail);
			$chaine_mail=$tabmail[0];
			for ($i=1;$i<count($tabmail);$i++) {
				if((isset($tabmail[$i]))&&(!in_array($tabmail[$i],$tabmail2))) {
					$chaine_mail.=",".$tabmail[$i];
					$tabmail2[]=$tabmail[$i];
				}
			}
			echo "<p>Envoyer un <a href='mailto:$chaine_mail?".rawurlencode("subject=".getSettingValue('gepiPrefixeSujetMail')."[GEPI] classe ".$classe['classe'])."' target='_blank'>mail à tous les membres de l'équipe</a>.</p>
<script type='text/javascript'>
	if(document.getElementById('span_mail')) {
		document.getElementById('span_mail').innerHTML=\" <a href='mailto:$chaine_mail?".rawurlencode("subject=".getSettingValue('gepiPrefixeSujetMail')."[GEPI] classe ".$classe['classe'])."' title='Envoyer un mail à tous les membres de l équipe' target='_blank'><img src='../images/icons/courrier_envoi.png' class='icone16' alt='Mail' /></a>\";
	}
</script>\n";
			if(peut_poster_message($_SESSION["statut"])) {
				echo "<p>Déposer une <a href='../mod_alerte/form_message.php?equipe_dest=$id_classe&sujet=Classe ".$classe['classe']."' target='_blank'>alerte <em>(par le module Alertes)</em> à tous les membres de l'équipe</a>.</p>
<script type='text/javascript'>
	if(document.getElementById('span_mod_alerte')) {
		document.getElementById('span_mod_alerte').innerHTML=\" <a href='../mod_alerte/form_message.php?equipe_dest=$id_classe&sujet=Classe ".$classe['classe']."' title='Déposer une alerte pour les membres de l équipe' target='_blank'><img src='../images/icons/module_alerte32.png' class='icone16' alt='Mail' /></a>\";
	}
</script>\n";
			}

			$chaine_alerte_scol="";
			$sql="SELECT * FROM j_scol_classes jsc, utilisateurs u WHERE jsc.login=u.login AND u.etat='actif';";
			//echo "$sql<br />";
			$res_scol=mysqli_query($GLOBALS["mysqli"], $sql);
			if(mysqli_num_rows($res_scol)>0) {
				$cpt_scol=0;
				$chaine_title_scol="";
				while($lig_scol=mysqli_fetch_object($res_scol)) {
					$chaine_alerte_scol.="&login_dest[]=".$lig_scol->login;
					if(($lig_scol->email!="")&&(check_mail($lig_scol->email))&&(!in_array($lig_scol->email,$tabmail2))) {
						if($chaine_mail!="") {
							$chaine_mail.=",";
						}
						if($chaine_title_scol!="") {
							$chaine_title_scol.=", ";
						}
						$chaine_mail.=$lig_scol->email;
						$tabmail2[]=$lig_scol->email;
						$chaine_title_scol.=$lig_scol->civilite." ".$lig_scol->nom." ".$lig_scol->prenom;
						$cpt_scol++;
					}
				}
				if($cpt_scol>0) {
					echo "<p>Envoyer un <a href='mailto:$chaine_mail?".rawurlencode("subject=".getSettingValue('gepiPrefixeSujetMail')."[GEPI] classe ".$classe['classe'])."'>mail à tous les membres de l'équipe (<em title=\"".$chaine_title_scol."\">comptes scolarité inclus</em>)</a>.</p>
	<script type='text/javascript'>
		if(document.getElementById('span_mail')) {
			document.getElementById('span_mail').innerHTML=document.getElementById('span_mail').innerHTML+\" <a href='mailto:$chaine_mail?".rawurlencode("subject=".getSettingValue('gepiPrefixeSujetMail')."[GEPI] classe ".$classe['classe'])."' title='Envoyer un mail à tous les membres de l équipe, comptes scolarité inclus'><img src='../images/icons/courrier_envoi.png' class='icone16' alt='Mail' />(*)</a>\";
		}
	</script>\n";
					if(peut_poster_message($_SESSION["statut"])) {
						echo "<p>Déposer une <a href='../mod_alerte/form_message.php?equipe_dest=".$id_classe.$chaine_alerte_scol."&sujet=Classe ".$classe['classe']."' target='_blank'>alerte <em>(par le module Alertes)</em> à tous les membres de l'équipe (<em title=\"".$chaine_title_scol."\">comptes scolarité inclus</em>)</a>.</p>
		<script type='text/javascript'>
			if(document.getElementById('span_mod_alerte')) {
				document.getElementById('span_mod_alerte').innerHTML=\" <a href='../mod_alerte/form_message.php?equipe_dest=".$id_classe.$chaine_alerte_scol."&sujet=Classe ".$classe['classe']."' title='Déposer une alerte pour les membres de l équipe, comptes scolarité inclus' target='_blank'><img src='../images/icons/module_alerte32.png' class='icone16' alt='Mail' /></a>\";
			}
		</script>\n";
					}
				}
			}

		}

		//echo "</div>";

		$active_mod_engagements=getSettingAOui("active_mod_engagements");
		if($active_mod_engagements) {
			$tab_engagements=get_tab_engagements();
			$tab_engagements_visu_profs_class=get_tab_engagements_telle_page("visu_profs_class");

			if(count($tab_engagements_visu_profs_class)>0) {
				echo "<p style='margin-top:1em;' class='bold'>Engagements pour cette classe&nbsp;:</p>
<table class='boireaus boireaus_alt'>";
				for($loop=0;$loop<count($tab_engagements_visu_profs_class);$loop++) {
					$id_type_courant=$tab_engagements_visu_profs_class[$loop];
					echo "
	<tr>
		<th>".$tab_engagements['id_engagement'][$id_type_courant]['nom']."</th>
		<td>";

					$tmp_tab_login_ele=array();
					$tmp_tab_login_resp=array();
					if(($tab_engagements['id_engagement'][$id_type_courant]['ConcerneEleve']!="yes")&&($tab_engagements['id_engagement'][$id_type_courant]['ConcerneResponsable']=="yes")) {
						$tmp_tab_login_resp=get_tab_login_tel_engagement($id_type_courant, $id_classe, "responsable");
					}
					elseif(($tab_engagements['id_engagement'][$id_type_courant]['ConcerneEleve']=="yes")&&($tab_engagements['id_engagement'][$id_type_courant]['ConcerneResponsable']!="yes")) {
						$tmp_tab_login_ele=get_tab_login_tel_engagement($id_type_courant, $id_classe, "eleve");
					}
					else {
						$tmp_tab_login_resp=get_tab_login_tel_engagement($id_type_courant, $id_classe, "responsable");
						$tmp_tab_login_ele=get_tab_login_tel_engagement($id_type_courant, $id_classe, "eleve");
					}
					for($loop2=0;$loop2<count($tmp_tab_login_ele);$loop2++) {
						echo get_nom_prenom_eleve($tmp_tab_login_ele[$loop2])."<br />";
					}
					for($loop2=0;$loop2<count($tmp_tab_login_resp);$loop2++) {
						echo civ_nom_prenom($tmp_tab_login_resp[$loop2])."<br />";
					}
					echo "
		</td>
	</tr>";

				}
				echo "
</table>";
			}

		}
	}
}
else {

	echo "<p class='bold'>";
	echo "<a href='../accueil.php'><img src='../images/icons/back.png' alt='Retour' class='back_link'/> Retour</a>";
	echo "</p>\n";

	echo "<h3>Equipe pédagogique d'une classe</h3>\n";
	//echo "<form enctype='multipart/form-data' action='".$_SERVER['PHP_SELF']."' method='post'>\n";
	echo "<p>Choix de la classe:</p>\n";

	//$sql="SELECT id,classe FROM classes ORDER BY classe";
	if($_SESSION['statut']=='scolarite'){
		//$sql="SELECT id,classe FROM classes ORDER BY classe";
		$sql="SELECT DISTINCT c.id,c.classe, c.suivi_par FROM classes c, j_scol_classes jsc WHERE jsc.id_classe=c.id AND jsc.login='".$_SESSION['login']."' ORDER BY classe";
	}
	if($_SESSION['statut']=='professeur'){
		$sql="SELECT DISTINCT c.id,c.classe, c.suivi_par FROM classes c,j_groupes_classes jgc,j_groupes_professeurs jgp WHERE jgp.login = '".$_SESSION['login']."' AND jgc.id_groupe=jgp.id_groupe AND jgc.id_classe=c.id ORDER BY c.classe";
	}
	if($_SESSION['statut']=='cpe'){
		$sql="SELECT DISTINCT c.id,c.classe, c.suivi_par FROM classes c,j_eleves_cpe jec,j_eleves_classes jecl WHERE jec.cpe_login = '".$_SESSION['login']."' AND jec.e_login=jecl.login AND jecl.id_classe=c.id ORDER BY c.classe";
	}
	if($_SESSION['statut']=='administrateur'){
		$sql="SELECT DISTINCT c.id,c.classe, c.suivi_par FROM classes c ORDER BY c.classe";
	}

	if(($_SESSION['statut']=='scolarite')&&(getSettingValue("GepiAccesVisuToutesEquipScol") =="yes")){
		$sql="SELECT DISTINCT c.id,c.classe, c.suivi_par FROM classes c ORDER BY c.classe";
	}
	if(($_SESSION['statut']=='cpe')&&(getSettingValue("GepiAccesVisuToutesEquipCpe") =="yes")){
		$sql="SELECT DISTINCT c.id,c.classe, c.suivi_par FROM classes c ORDER BY c.classe";
	}
	if(($_SESSION['statut']=='professeur')&&(getSettingValue("GepiAccesVisuToutesEquipProf") =="yes")){
		$sql="SELECT DISTINCT c.id,c.classe, c.suivi_par FROM classes c ORDER BY c.classe";
	}

	if(($_SESSION['statut']=='autre')&&(acces('/groupes/visu_profs_class.php', 'autre'))) {
		$sql="SELECT DISTINCT c.id,c.classe, c.suivi_par FROM classes c ORDER BY c.classe";
	}

	$result_classes=mysqli_query($GLOBALS["mysqli"], $sql);
	$nb_classes = mysqli_num_rows($result_classes);
	//echo "<select name='id_classe' size='1'>\n";
	//echo "<option value='null'>-- Sélectionner la classe --</option>\n";
	/*
	for ($i=0;$i<$nb_classes;$i++) {
		$classe=old_mysql_result($query, $i, "classe");
		$id_classe=old_mysql_result($query, $i, "id");
		echo "<option value='$id_classe'>" . htmlspecialchars($classe) . "</option>\n";
	}
	*/
	$tab_classe=array();
	if(mysqli_num_rows($result_classes)==0){
		echo "<p>Il semble qu'aucune classe n'ait encore été créée...<br />... ou alors aucune classe ne vous a été attribuée.<br />Contactez l'administrateur pour qu'il effectue le paramétrage approprié dans la Gestion des classes.</p>\n";
	}
	else{
		$nb_classes=mysqli_num_rows($result_classes);
		$nb_class_par_colonne=round($nb_classes/3);
		$percent_colonne=floor(100/3);
		echo "<table width='100%' summary='Choix de la classe'>\n";
		echo "<tr valign='top' align='center'>\n";
		$cpt=0;
		//echo "<td style='padding: 0 10px 0 10px'>\n";
		echo "<td width='33%'>\n";
		while($lig_class=mysqli_fetch_object($result_classes)){
			if(($cpt>0)&&(round($cpt/$nb_class_par_colonne)==$cpt/$nb_class_par_colonne)){
				echo "</td>\n";
				//echo "<td style='padding: 0 10px 0 10px'>\n";
				echo "<td width='$percent_colonne%'>\n";
			}
			//echo "<option value='$lig_class->id'>" . htmlspecialchars("$lig_class->classe") . "</option>\n";
			echo "<a href='".$_SERVER['PHP_SELF']."?id_classe=$lig_class->id".$ajout_href_2."' title=\"Classe suivie par ".$lig_class->suivi_par."\" onmouseover=\"this.style.fontWeight='bold';this.style.fontSize='x-large'\" onmouseout=\"this.style.fontWeight='normal';this.style.fontSize='medium'\">".htmlspecialchars("$lig_class->classe") . "</a><br />\n";
			$tab_classe[$lig_class->id]=$lig_class->classe;
			$cpt++;
		}
		echo "</td>\n";
		echo "</tr>\n";
		echo "</table>\n";
	}

	$active_mod_engagements=getSettingAOui("active_mod_engagements");

	if($active_mod_engagements) {
		$tab_engagements=get_tab_engagements();
		$tab_engagements_visu_profs_class=get_tab_engagements_telle_page("visu_profs_class");
		/*
		echo "<div style='float:left;width:600px; margin:1em; background:white;'>
		<pre>";
		print_r($tab_engagements);
		echo "</pre>
		</div>";
		echo "<div style='float:left;width:600px; margin:1em; background:white;'>
		<pre>";
		print_r($tab_engagements_visu_profs_class);
		echo "</pre>
		</div>";
		echo "<div style='clear:both'></div>";
		*/
	}

	// Tableau des PP
	$gepi_prof_suivi=getSettingValue('gepi_prof_suivi');
	echo "<a name='liste_pp'></a>
<div align='center'>
	<table class='boireaus boireaus_alt resizable sortable'>
		<tr>
			<th>Classe</th>
			<th>
				<div style='float:right; width:16px; margin-left:3px;'>
					<a href='".$_SERVER['PHP_SELF']."?export_prof_suivi=y&amp;export=csv' class='noprint' title=\"Exporter la liste des ".$gepi_prof_suivi." au format CSV (tableur)\" target='_blank'><img src='../images/icons/csv.png' class='icone16' alt='CSV' /></a>
				</div>
				<div id='div_mailto_pp' style='float:right; width:16px'></div>
				".ucfirst(getSettingValue('gepi_prof_suivi'))."
			</th>";
	if($active_mod_engagements) {
		/*
		$indice_delegue_de_classe="";
		$indice_suppleant_delegue_de_classe="";
		$tab_engagements_ele=get_tab_engagements("eleve");
		for($loop=0;$loop<count($tab_engagements_ele['indice']);$loop++) {
			// A FAIRE: Pouvoir choisir les engagements à faire apparaitre ici
			//Délégué de classe
			//Suppléant délégué de classe
			if($tab_engagements_ele['indice'][$loop]['nom']=="Suppléant délégué de classe") {
				$indice_delegue_de_classe=$loop;
				echo "
			<th>".$tab_engagements_ele['indice'][$loop]['nom']."</th>";
			}
			elseif($tab_engagements_ele['indice'][$loop]['nom']=="Délégué de classe") {
				$indice_suppleant_delegue_de_classe=$loop;
				echo "
			<th>".$tab_engagements_ele['indice'][$loop]['nom']."</th>";
			}
		}
		*/

		for($loop=0;$loop<count($tab_engagements_visu_profs_class);$loop++) {
			$id_type_courant=$tab_engagements_visu_profs_class[$loop];
			echo "
			<th>".$tab_engagements['id_engagement'][$id_type_courant]['nom']."</th>";
		}
	}

	// 20180518
	$tab_suivi_par=array();
	$sql="SELECT id,suivi_par FROM classes;";
	$res_suivi=mysqli_query($mysqli, $sql);
	if(mysqli_num_rows($res_suivi)>0) {
		while($lig_suivi=mysqli_fetch_object($res_suivi)) {
			$tab_suivi_par[$lig_suivi->id]=$lig_suivi->suivi_par;
		}
	}
	echo "
		<th>Suivie par</th>";

	echo "
		</tr>";

	$acces_class_const=acces("/classes/classes_const.php", $_SESSION['statut']);
	$acces_modify_user=acces("/utilisateurs/modify_user.php", $_SESSION['statut']);
	$acces_visu_eleve=acces("/eleves/visu_eleve.php", $_SESSION['statut']);
	$acces_modify_resp=acces("/responsables/modify_resp.php", $_SESSION['statut']);
	$acces_trombi=(acces("/mod_trombinoscopes/trombinoscopes.php", $_SESSION['statut'])&&(getSettingAOui("active_module_trombinoscopes")));

	$liste_mailto_pp="";
	$tab_mailto_pp=array();
	$tab_pp=get_tab_prof_suivi();
	foreach($tab_classe as $current_id_classe => $current_classe) {
		$html_current_classe="";
		if($acces_trombi) {
			$html_current_classe="<div style='float:right; width:16px;'><a href='../mod_trombinoscopes/trombinoscopes.php?classe=$current_id_classe&etape=2' title='Voir le trombinoscope des élèves de la classe'><img src='../images/icons/trombinoscope.png' class='icone16' alt='Trombi' /></a></div>";
		}

		if($acces_class_const) {
			$html_current_classe.="<a href='../classes/classes_const.php?id_classe=$current_id_classe' title=\"Voir la composition de la classe, ses élèves, leurs régimes, PP et CPE.\">$current_classe</a>";
		}
		elseif($acces_visu_eleve) {
			$html_current_classe.="<a href='../eleves/visu_eleve.php?id_classe=$current_id_classe' title=\"Voir la liste des élèves de la classe.\">$current_classe</a>";
		}
		else {
			$html_current_classe.=$current_classe;
		}
		echo "
		<tr>
			<td>$html_current_classe</td>
			<td>";
		if(isset($tab_pp[$current_id_classe])) {
			for($loop=0;$loop<count($tab_pp[$current_id_classe]);$loop++) {
				if($loop>0) {echo "<br />";}
				$designation_user=civ_nom_prenom($tab_pp[$current_id_classe][$loop]);
				echo "<div style='float:right; width:16px'>".affiche_lien_mailto_si_mail_valide($tab_pp[$current_id_classe][$loop], $designation_user)."</div>";

				$current_mail=get_mail_user($tab_pp[$current_id_classe][$loop]);
				if((!in_array($current_mail, $tab_mailto_pp))&&(check_mail($current_mail))) {
					if($liste_mailto_pp!="") {
						$liste_mailto_pp.=',';
					}
					$liste_mailto_pp.=$current_mail;
					$tab_mailto_pp[]=$current_mail;
				}

				if($acces_modify_user) {
					echo "<a href='../utilisateurs/modify_user.php?user_login=".$tab_pp[$current_id_classe][$loop]."' title=\"Voir la fiche utilisateur.\">".$designation_user."</a>";
				}
				else {
					echo $designation_user;
				}
			}
		}
		echo "</td>";

		if($active_mod_engagements) {
			for($loop=0;$loop<count($tab_engagements_visu_profs_class);$loop++) {
				$id_type_courant=$tab_engagements_visu_profs_class[$loop];
				echo "
				<td>";
				$tmp_tab_login_ele=array();
				$tmp_tab_login_resp=array();
				if(($tab_engagements['id_engagement'][$id_type_courant]['ConcerneEleve']!="yes")&&($tab_engagements['id_engagement'][$id_type_courant]['ConcerneResponsable']=="yes")) {
					$tmp_tab_login_resp=get_tab_login_tel_engagement($id_type_courant, $current_id_classe, "responsable");
				}
				elseif(($tab_engagements['id_engagement'][$id_type_courant]['ConcerneEleve']=="yes")&&($tab_engagements['id_engagement'][$id_type_courant]['ConcerneResponsable']!="yes")) {
					$tmp_tab_login_ele=get_tab_login_tel_engagement($id_type_courant, $current_id_classe, "eleve");
				}
				else {
					$tmp_tab_login_resp=get_tab_login_tel_engagement($id_type_courant, $current_id_classe, "responsable");
					$tmp_tab_login_ele=get_tab_login_tel_engagement($id_type_courant, $current_id_classe, "eleve");
				}

				for($loop2=0;$loop2<count($tmp_tab_login_ele);$loop2++) {
					if($acces_visu_eleve) {
						echo "<a href='../eleves/visu_eleve.php?ele_login=".$tmp_tab_login_ele[$loop2]."' title=\"Voir cet élève.\">".get_nom_prenom_eleve($tmp_tab_login_ele[$loop2])."</a><br />";
					}
					else {
						echo get_nom_prenom_eleve($tmp_tab_login_ele[$loop2])."<br />";
					}
				}
				for($loop2=0;$loop2<count($tmp_tab_login_resp);$loop2++) {
					if($acces_modify_resp) {
						echo "<a href='../responsables/modify_resp.php?login_resp=".$tmp_tab_login_resp[$loop2]."' title=\"Voir la fiche responsable.\">".$designation_user."</a>";
						echo civ_nom_prenom($tmp_tab_login_resp[$loop2])."<br />";
					}
					else {
						echo civ_nom_prenom($tmp_tab_login_resp[$loop2])."<br />";
					}
				}
				echo "</td>";
			}
		}
/*
		if($indice_delegue_de_classe!="") {
			echo "
			<td>";
			for() {
			}
			echo "</td>";
		}
*/

		// 20180518
		echo "
			<td>".(isset($tab_suivi_par[$current_id_classe]) ? $tab_suivi_par[$current_id_classe] : '')."</td>";

		echo "
		</tr>";
	}
	echo "
	</table>";

	if($liste_mailto_pp!='') {
		echo "
	<script type='text/javascript'>

		document.getElementById('div_mailto_pp').innerHTML=\"<a href='mailto:$liste_mailto_pp?".urlencode("subject=".getSettingValue('gepiPrefixeSujetMail')."[GEPI] ")."' class='noprint' title='Envoyer un mail à la liste des ".$gepi_prof_suivi."' target='_blank'><img src='../images/icons/mail.png' class='icone16' alt='Mail' /></a>\";

	</script>";
	}

	echo "
</div>";

}
echo "<p><br /></p>\n";
require("../lib/footer.inc.php");
?>
