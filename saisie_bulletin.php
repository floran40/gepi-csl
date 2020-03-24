<?php
/*
* $Id$
*
* Copyright 2001, 2018 Thomas Belliard, Laurent Delineau, Edouard Hue, Eric Lebrun, Stephane Boireau
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

$variables_non_protegees = 'yes';

// Initialisations files
require_once("../lib/initialisationsPropel.inc.php");
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

//On vérifie si le module est activé
if ((getSettingValue("active_module_absence")!='2')||(!getSettingAOui('active_bulletins'))) {
	die("Le module n'est pas activé.");
}

include_once 'lib/function.php';

// Si le témoin temoin_check_srv() doit être affiché, on l'affichera dans la page à côté de Enregistrer.
$aff_temoin_serveur_hors_entete="y";

$msg="";

$id_classe=isset($_POST['id_classe']) ? $_POST['id_classe'] : (isset($_GET['id_classe']) ? $_GET['id_classe'] : NULL);
$num_periode=isset($_POST['num_periode']) ? $_POST['num_periode'] : (isset($_GET['num_periode']) ? $_GET['num_periode'] : NULL);

//=====================================
/*
if((isset($id_classe))&&(preg_match("/^[0-9]{1,}$/", $id_classe))) {
	//include('../lib/periodes.inc.php');

	// Tableau pour les autorisations exceptionnelles de saisie
	// Il n'est pris en compte comme le getSettingValue('autoriser_correction_bulletin') que pour une période partiellement close
	$une_autorisation_exceptionnelle_de_saisie_au_moins='n';
	$tab_autorisation_exceptionnelle_de_saisie=array();
	$date_courante=time();
	//echo "\$date_courante=$date_courante<br />";
	$k=1;
	while ($k < $nb_periode) {
		$tab_autorisation_exceptionnelle_de_saisie[$k]['totaux']='n';
		$tab_autorisation_exceptionnelle_de_saisie[$k]['appreciation']='n';

		$sql="SELECT UNIX_TIMESTAMP(date_limite) AS date_limite, mode FROM abs_bull_delais WHERE id_classe='$id_classe' AND periode='$k';";
		$res=mysqli_query($GLOBALS["mysqli"], $sql);
		if(mysqli_num_rows($res)>0) {
			$lig=mysqli_fetch_object($res);
			$date_limite=$lig->date_limite;
			//echo "\$date_limite=$date_limite en période $k.<br />";
			//echo "\$date_courante=$date_courante.<br />";

			if($date_courante<$date_limite) {
				$tab_autorisation_exceptionnelle_de_saisie[$k]['totaux']=$lig->totaux;
				$tab_autorisation_exceptionnelle_de_saisie[$k]['appreciation']=$lig->appreciation;
				//if($lig->mode=='acces_complet') {
				//	$tab_autorisation_exceptionnelle_de_saisie[$k]='yy';
					$proposer_liens_enregistrement="y";
				//}
				$une_autorisation_exceptionnelle_de_saisie_au_moins='y';
			}
		}
		//echo "\$tab_autorisation_exceptionnelle_de_saisie[$k]=".$tab_autorisation_exceptionnelle_de_saisie[$k]."<br />";
		$k++;
	}
}
*/

// Tableau pour les autorisations exceptionnelles de saisie
$tab_autorisation_exceptionnelle_de_saisie=array();
$date_courante=time();
//echo "\$date_courante=$date_courante<br />";
$sql="SELECT * FROM abs_bull_delais WHERE UNIX_TIMESTAMP(date_limite)>'".time()."';";
$res=mysqli_query($GLOBALS["mysqli"], $sql);
if(mysqli_num_rows($res)>0) {
	while ($lig=mysqli_fetch_object($res)) {
		$tab_autorisation_exceptionnelle_de_saisie[$lig->id_classe][$lig->periode]['totaux']=$lig->totaux;
		$tab_autorisation_exceptionnelle_de_saisie[$lig->id_classe][$lig->periode]['appreciation']=$lig->appreciation;
	}
}
//=====================================

if((isset($id_classe))&&(preg_match("/^[0-9]{1,}$/", $id_classe))&&(isset($num_periode))&&(preg_match("/^[0-9]{1,}$/", $num_periode))&&(getSettingAOui("abs2_import_manuel_bulletin"))) {
	if(((isset($tab_autorisation_exceptionnelle_de_saisie[$id_classe][$num_periode]['totaux']))&&($tab_autorisation_exceptionnelle_de_saisie[$id_classe][$num_periode]['totaux']=='y'))||
	((isset($tab_autorisation_exceptionnelle_de_saisie[$id_classe][$num_periode]['appreciation']))&&($tab_autorisation_exceptionnelle_de_saisie[$id_classe][$num_periode]['appreciation']=='y'))||
	(etat_verrouillage_classe_periode($id_classe, $num_periode)=="N")) {
		header("Location: ../absences/saisie_absences.php?id_classe=$id_classe&periode_num=$num_periode");
	}
	else {
		header("Location: ../absences/consulter_absences.php?id_classe=$id_classe&periode_num=$num_periode");
	}
	die();
}

if(isset($_POST['enregistrement_saisie'])) {
	check_token();

	if((etat_verrouillage_classe_periode($id_classe, $num_periode)!="N")&&
	((!isset($tab_autorisation_exceptionnelle_de_saisie[$id_classe][$num_periode]['appreciation']))||($tab_autorisation_exceptionnelle_de_saisie[$id_classe][$num_periode]['appreciation']!='y'))) {
		$msg="La période est close.<br />";
	}
	else {
		$msg="";

		$nb_reg=0;
		$nb_err=0;

		if (isset($NON_PROTECT["app_grp"])){
			$ap = traitement_magic_quotes(corriger_caracteres($NON_PROTECT["app_grp"]));
		}
		else{
			$ap = "";
		}
		$ap=nettoyage_retours_ligne_surnumeraires($ap);

		$sql="SELECT * FROM absences_appreciations_grp WHERE (id_classe='".$id_classe."' AND periode='$num_periode')";
		$test=mysqli_query($GLOBALS["mysqli"], $sql);
		if(mysqli_num_rows($test)>0) {
			$sql="UPDATE absences_appreciations_grp SET appreciation='$ap' WHERE (id_classe='".$id_classe."' AND periode='$num_periode');";
		} else {
			$sql="INSERT INTO absences_appreciations_grp SET id_classe='".$id_classe."', periode='$num_periode', appreciation='$ap';";
		}
		//echo "$sql<br />";
		$register = mysqli_query($GLOBALS["mysqli"], $sql);
		if (!$register) {
			$nb_err++;
		}
		else {
			$nb_reg++;
		}

		$tab_login=array();
		if (getSettingValue('GepiAccesAbsTouteClasseCpe')=='yes') {
			$sql="SELECT login FROM j_eleves_classes jec WHERE jec.id_classe='$id_classe' AND periode='$num_periode';";
		} else {
			$sql="SELECT login FROM j_eleves_classes jec, j_eleves_cpe jecpe WHERE (jec.id_classe='$id_classe' AND periode='$num_periode' AND jecpe.e_login=jec.login AND jecpe.cpe_login = '".$_SESSION['login']."';";
		}
		//echo "$sql<br />";
		$res_ele = mysqli_query($GLOBALS["mysqli"], $sql);
		while($lig_ele=mysqli_fetch_object($res_ele)) {
			$tab_login[]=$lig_ele->login;
		}

		$login_eleve=isset($_POST['login_eleve']) ? $_POST['login_eleve'] : array();

		for($loop=0;$loop<count($login_eleve);$loop++) {
			if(!in_array($login_eleve[$loop], $tab_login)) {
				$msg.="Enregistrement non effectué pour l'élève ".get_nom_prenom_eleve($login_eleve[$loop]).".<br />";
			}
			else {
				$app_ele_courant="app_eleve_".$loop;
				//echo "\$app_ele_courant=$app_ele_courant<br />";
				if (isset($NON_PROTECT[$app_ele_courant])){
					$ap = traitement_magic_quotes(corriger_caracteres($NON_PROTECT[$app_ele_courant]));
				}
				else{
					$ap = "";
				}

				$ap=nettoyage_retours_ligne_surnumeraires($ap);

				$sql="SELECT * FROM absences WHERE (login='".$login_eleve[$loop]."' AND periode='$num_periode')";
				$test=mysqli_query($GLOBALS["mysqli"], $sql);
				if(mysqli_num_rows($test)>0) {
					$sql="UPDATE absences SET appreciation='$ap' WHERE (login='".$login_eleve[$loop]."' AND periode='$num_periode');";
				} else {
					$sql="INSERT INTO absences SET login='".$login_eleve[$loop]."', periode='$num_periode', appreciation='$ap';";
				}
				//echo "$sql<br />";
				$register = mysqli_query($GLOBALS["mysqli"], $sql);
				if (!$register) {
					$nb_err++;
				}
				else {
					$nb_reg++;
				}
			}
		}

		$msg.="$nb_reg appréciation(s) enregistrée(s) ou mise(s) à jour (".strftime("%d/%m/%Y à %H:%M:%S").").<br />";
		if($nb_err>0) {
			$msg.="$nb_err erreur(s) lors de l'opération.<br />";
		}
	}
}

$style_specifique[] = "edt_organisation/style_edt";
$style_specifique[] = "templates/DefaultEDT/css/small_edt";
$style_specifique[] = "mod_abs2/lib/abs_style";
//$javascript_specifique[] = "mod_abs2/lib/include";
$javascript_specifique[] = "edt_organisation/script/fonctions_edt";

$javascript_specifique[] = "saisie/scripts/js_saisie";

$javascript_specifique[] = "lib/tablekit";
//$dojo=true;
$utilisation_tablekit="ok";
//**************** EN-TETE *****************
$titre_page = "Bulletins : Saisie abs";
require_once("../lib/header.inc.php");
//**************** EN-TETE *****************
include('menu_abs2.inc.php');
include('menu_bilans.inc.php');

//debug_var();

?>
<div id="contain_div" class="css-panes">

<?php

if((!isset($id_classe))||(!isset($num_periode))) {


	if (getSettingValue('GepiAccesAbsTouteClasseCpe')=='yes') {
		$sql="SELECT DISTINCT c.* FROM classes c, periodes p WHERE p.id_classe = c.id  ORDER BY classe;";
	} else {
		$sql="SELECT DISTINCT c.* FROM classes c, j_eleves_cpe e, j_eleves_classes jc WHERE (e.cpe_login = '".$_SESSION['login']."' AND jc.login = e.e_login AND c.id = jc.id_classe)  ORDER BY classe;";
	}
	$calldata = mysqli_query($GLOBALS["mysqli"], $sql);
	$nombreligne = mysqli_num_rows($calldata);

	echo "<p>Total : $nombreligne classe";
	if($nombreligne>1){echo "s";}
	echo " - ";
	echo "Cliquez sur la classe pour laquelle vous souhaitez saisir les absences ou les appréciations Vie Scolaire&nbsp;:</p>\n";
	if (!getSettingAOui('GepiAccesAbsTouteClasseCpe')) {
		echo "<p><em>Remarque&nbsp;:</em> s'affichent toutes les classes pour lesquelles vous êtes responsable du suivi d'au moins un ".$gepiSettings['denomination_eleve']." de la classe.</p>\n";
	}

	while($lig_classe=mysqli_fetch_object($calldata)) {
		$tab_id_classe[]=$lig_classe->id;

		echo "<p onmouseover=\"this.style.backgroundColor='white'\" onmouseout=\"this.style.backgroundColor=''\"><span class='bold'>".$lig_classe->classe."&nbsp;:</span> ";
		$sql="SELECT * FROM periodes WHERE id_classe='".$lig_classe->id."' ORDER BY num_periode;";
		$res_per = mysqli_query($GLOBALS["mysqli"], $sql);
		$cpt=0;
		while($lig_per=mysqli_fetch_object($res_per)) {
			if($cpt>0) {
				echo " - ";
			}
			echo "<a href='".$_SERVER['PHP_SELF']."?id_classe=".$lig_classe->id."&amp;num_periode=".$lig_per->num_periode."'>";
			if($lig_per->verouiller=="N") {
				echo "<img src='../images/edit16.png' class='icone16' alt='Saisir' />&nbsp;";
				echo "<span style='color:".$couleur_verrouillage_periode[$lig_per->verouiller]."' title=\"Période ".$traduction_verrouillage_periode[$lig_per->verouiller]."
".$explication_verrouillage_periode[$lig_per->verouiller]."\">".$lig_per->nom_periode."</span></a>";
			}
			elseif(((isset($tab_autorisation_exceptionnelle_de_saisie[$lig_classe->id][$lig_per->num_periode]['totaux']))&&($tab_autorisation_exceptionnelle_de_saisie[$lig_classe->id][$lig_per->num_periode]['totaux']=='y'))||
			((isset($tab_autorisation_exceptionnelle_de_saisie[$lig_classe->id][$lig_per->num_periode]['appreciation']))&&($tab_autorisation_exceptionnelle_de_saisie[$lig_classe->id][$lig_per->num_periode]['appreciation']=='y'))) {
				echo "<img src='../images/edit16.png' class='icone16' alt='Saisir' />&nbsp;";
				echo "<span style='background-color:orange' title=\"Autorisation exceptionnelle de saisie.\">".$lig_per->nom_periode;
				echo "<img src='../images/icons/flag2.gif' class='icone16' alt='Attention' />";
				echo "</span></a>";
			}
			else {
				echo "<img src='../images/icons/chercher.png' class='icone16' alt='Consulter' />&nbsp;";
				echo "<span style='color:".$couleur_verrouillage_periode[$lig_per->verouiller]."' title=\"Période ".$traduction_verrouillage_periode[$lig_per->verouiller]."
".$explication_verrouillage_periode[$lig_per->verouiller]."\">".$lig_per->nom_periode."</span></a>";
			}
			$cpt++;
		}
		echo "</p>\n";
	}

	echo "</div>\n";
	require_once("../lib/footer.inc.php");
	die();
}

//=========================================================

// Classe et période sont choisies

$acces_saisie=false;
$etat_periode=etat_verrouillage_classe_periode($id_classe, $num_periode);
if($etat_periode=='N') {
	$acces_saisie=true;
}
elseif((isset($tab_autorisation_exceptionnelle_de_saisie[$id_classe][$num_periode]['appreciation']))&&($tab_autorisation_exceptionnelle_de_saisie[$id_classe][$num_periode]['appreciation']=='y')) {
	$acces_saisie=true;
}

echo "<p><a href='".$_SERVER['PHP_SELF']."'>Choisir une autre classe/période</a></p>";

if (!getSettingAOui('GepiAccesAbsTouteClasseCpe')) {
	$sql="SELECT 1=1 FROM classes c, j_eleves_cpe e, j_eleves_classes jc WHERE (e.cpe_login = '".$_SESSION['login']."' AND jc.login = e.e_login AND c.id = jc.id_classe AND c.id='$id_classe');";
}
else {
	$sql="SELECT 1=1 FROM classes c WHERE c.id='$id_classe';";
}
$test = mysqli_query($GLOBALS["mysqli"], $sql);
if(mysqli_num_rows($test)==0) {

	echo "<p style='color:red'>Vous n'avez pas accès à cette classe.</p>";

	echo "</div>\n";
	require_once("../lib/footer.inc.php");
	die();
}

if(!getSettingANon('active_recherche_lapsus')) {
	$tab_lapsus_et_correction=retourne_tableau_lapsus_et_correction();
}

$appreciation_absences_grp="";
$sql="SELECT * FROM absences_appreciations_grp WHERE id_classe='".$id_classe."' AND periode='".$num_periode."';";
$res_abs_grp_clas=mysqli_query($GLOBALS["mysqli"], $sql);
if(mysqli_num_rows($res_abs_grp_clas)>0) {
	$lig_abs_grp_clas=mysqli_fetch_object($res_abs_grp_clas);
	$appreciation_absences_grp=$lig_abs_grp_clas->appreciation;
}

$colspan_abs="";
$explication_remplissage_table_absences="";
if(getSettingAOui("abs2_import_manuel_bulletin")) {
	// Normalement on n'arrive pas sur cette page, mais sur /absences/saisie_absences.php quand on est en mode Import manuel.
	// Mais au cas où...

	$colspan_abs=" colspan='2'";
	$explication_remplissage_table_absences=".\nVous avez fait le choix dans le paramétrage du module Absences 2 de remplir manuellement (ou par import CSV) les totaux destinés aux bulletins.\nSi deux valeurs sont proposées ci-dessous, c'est peut-être pas que le remplissage n'est à jour.";
}

echo "<form action='".$_SERVER['PHP_SELF']."' method='post'>
	<fieldset class='fieldset_opacite50'>";

$insert_mass_appreciation_type=getSettingValue("insert_mass_appreciation_type");
if ($insert_mass_appreciation_type=="y") {
	// INSERT INTO setting SET name='insert_mass_appreciation_type', value='y';

	$sql="CREATE TABLE IF NOT EXISTS b_droits_divers (login varchar(50) NOT NULL default '', nom_droit varchar(50) NOT NULL default '', valeur_droit varchar(50) NOT NULL default '') ENGINE=MyISAM CHARACTER SET utf8 COLLATE utf8_general_ci;";
	$create_table=mysqli_query($GLOBALS["mysqli"], $sql);

	// Pour tester:
	// INSERT INTO b_droits_divers SET login='toto', nom_droit='insert_mass_appreciation_type', valeur_droit='y';

	if($_SESSION["statut"]=="secours") {
		$droit_insert_mass_appreciation_type="y";
	}
	else {
		$sql="SELECT 1=1 FROM b_droits_divers WHERE login='".$_SESSION['login']."' AND nom_droit='insert_mass_appreciation_type' AND valeur_droit='y';";
		$res_droit=mysqli_query($GLOBALS["mysqli"], $sql);
		if(mysqli_num_rows($res_droit)>0) {
			$droit_insert_mass_appreciation_type="y";
		}
		else {
			$droit_insert_mass_appreciation_type="n";
		}
	}

	if($droit_insert_mass_appreciation_type=="y") {
		echo "<div style='float:right; width:150px; border: 1px solid black; background-color: white; font-size: small; text-align:center;margin-left:0.5em;'>
	Insérer l'appréciation-type suivante pour toutes les appréciations vides: 
	<input type='text' name='ajout_a_textarea_vide' id='ajout_a_textarea_vide' value='-' size='10' /><br />
	<input type='button' name='ajouter_a_textarea_vide' value='Ajouter' onclick='ajoute_a_textarea_vide()' /><br />
</div>

<script type='text/javascript'>
	function ajoute_a_textarea_vide() {
		champs_textarea=document.getElementsByTagName('textarea');
		//alert('champs_textarea.length='+champs_textarea.length);
		for(i=0;i<champs_textarea.length;i++){
			if(champs_textarea[i].value=='') {
				champs_textarea[i].value=document.getElementById('ajout_a_textarea_vide').value;
			}
		}
	}
</script>\n";
	}
}

echo "
		<div style='float:right; width:16px'><a href='../impression/avis_pdf_absences.php?id_classe=$id_classe&periode_num=$num_periode' title=\"Imprimer les appréciations absences et nombre d'absences,... en PDF\" target='_blank'><img src='../images/icons/pdf.png' class='icone16' alt='Générer un PDF' /></a></div>
		<p class='bold'>Classe de ".get_nom_classe($id_classe)." en période ".$num_periode."</p>

		<div id='fixe'>";
if(getSettingAOui('aff_temoin_check_serveur')) {
	temoin_check_srv();
}
echo "
			<input type='submit' value='Enregistrer' /><br />";
include('../saisie/ctp.php');
echo "
			<!-- Champ destiné à recevoir la valeur du champ suivant celui qui a le focus pour redonner le focus à ce champ après une validation -->
			<input type='hidden' id='info_focus' name='champ_info_focus' value='' />
			<input type='hidden' id='focus_courant' name='focus_courant' value='' />
		</div>

		".add_token_field(true)."
		<input type='hidden' name='enregistrement_saisie' value='y' />
		<input type='hidden' name='id_classe' value='".$id_classe."' />
		<input type='hidden' name='num_periode' value='".$num_periode."' />";


if($acces_saisie) {
	echo "
		<p>
			Appréciation sur le groupe classe pour la période $num_periode&nbsp;:<br />
			<textarea id='n0' name='no_anti_inject_app_grp' rows='2' cols='80'  wrap=\"virtual\" 
							onKeyDown=\"clavier(this.id,event);\" 
							onchange=\"changement()\">$appreciation_absences_grp</textarea>
		</p>";
}
else {
	echo "
		<p>
			Appréciation sur le groupe classe pour la période $num_periode&nbsp;:<br />
			<div class='fieldset_opacite50'>".(trim($appreciation_absences_grp)!='' ? $appreciation_absences_grp : "(vide)")."</div>
		</p>";
}
echo "

		<div id='div_verif_grp".$num_periode."' style='color:red;'>";
		if(!getSettingANon('active_recherche_lapsus')) {
			echo teste_lapsus($appreciation_absences_grp);
		}
		echo "</div>

		<table class='boireaus boireaus_alt'>
			<thead>
				<tr>
					<th>Élève</th>
					<th".$colspan_abs." title=\"Nombre d'absences".$explication_remplissage_table_absences."\">Nb.abs</th>
					<th".$colspan_abs." title=\"Nombre d'absences non justifiées".$explication_remplissage_table_absences."\">Nb.nj</th>
					<th".$colspan_abs." title=\"Nombre de retards".$explication_remplissage_table_absences."\">Nb.ret</th>
					<th>Appréciation</th>
				</tr>
			</thead>
			<tbody>";

$cpt=0;
$num_id=10;
//$chaine_test_vocabulaire="";
$sql="SELECT e.nom, e.prenom, e.login FROM eleves e, j_eleves_classes jec WHERE jec.id_classe='$id_classe' AND jec.periode='$num_periode' AND jec.login=e.login ORDER BY e.nom, e.prenom;";
$res_ele=mysqli_query($GLOBALS["mysqli"], $sql);
$eff_ele=mysqli_num_rows($res_ele);
while($lig_ele=mysqli_fetch_object($res_ele)) {
	$eleve = EleveQuery::create()->findOneByLogin($lig_ele->login);
	if ($eleve != null) {
		$current_eleve_absences = strval($eleve->getDemiJourneesAbsenceParPeriode($num_periode)->count());
		$current_eleve_nj = strval($eleve->getDemiJourneesNonJustifieesAbsenceParPeriode($num_periode)->count());
		$current_eleve_retards = strval($eleve->getRetardsParPeriode($num_periode)->count());

		// Initialisation
		$current_eleve_nb_absences_table_absences="";
		$current_eleve_non_justifie_table_absences="";
		$current_eleve_nb_retards_table_absences="";

		$sql="SELECT * FROM absences WHERE (login='".$lig_ele->login."' AND periode='$num_periode');";
		//echo "$sql< br />";
		$current_eleve_absences_query = mysqli_query($GLOBALS["mysqli"], $sql);
		$current_eleve_appreciation_absences_objet = $current_eleve_absences_query->fetch_object();
		$current_eleve_appreciation_absences = '';
		if ($current_eleve_appreciation_absences_objet) { 
			$current_eleve_appreciation_absences = $current_eleve_appreciation_absences_objet->appreciation;
			$current_eleve_nb_absences_table_absences = $current_eleve_appreciation_absences_objet->nb_absences;
			$current_eleve_non_justifie_table_absences = $current_eleve_appreciation_absences_objet->non_justifie;
			$current_eleve_nb_retards_table_absences = $current_eleve_appreciation_absences_objet->nb_retards;
		}
	}

	if(getSettingAOui("abs2_import_manuel_bulletin")) {
		echo "
				<tr>
					<td>".casse_mot($lig_ele->nom, 'maj')." ".casse_mot($lig_ele->prenom, 'majf2')."</td>";

		if($current_eleve_absences==$current_eleve_nb_absences_table_absences) {
			echo "
					<td colspan='2'>$current_eleve_absences</td>";
		}
		else {
			echo "
					<td title=\"Enregistrement dans la table 'absences' remplie manuellement.\">$current_eleve_nb_absences_table_absences</td>
					<td title=\"Calcul d'après les saisies dans le module Absences 2\">$current_eleve_absences</td>";
		}

		if($current_eleve_nj==$current_eleve_non_justifie_table_absences) {
			echo "
					<td colspan='2'>$current_eleve_nj</td>";
		}
		else {
			echo "
					<td title=\"Enregistrement dans la table 'absences' remplie manuellement.\">$current_eleve_non_justifie_table_absences</td>
					<td title=\"Calcul d'après les saisies dans le module Absences 2\">$current_eleve_nj</td>";
		}

		if($current_eleve_retards==$current_eleve_nb_retards_table_absences) {
			echo "
					<td colspan='2'>$current_eleve_retards</td>";
		}
		else {
			echo "
					<td title=\"Enregistrement dans la table 'absences' remplie manuellement.\">$current_eleve_nb_retards_table_absences</td>
					<td title=\"Calcul d'après les saisies dans le module Absences 2\">$current_eleve_retards</td>";
		}

		echo "
					<td>";
	}
	else {
		echo "
				<tr>
					<td>".casse_mot($lig_ele->nom, 'maj')." ".casse_mot($lig_ele->prenom, 'majf2')."</td>
					<td>$current_eleve_absences</td>
					<td>$current_eleve_nj</td>
					<td>$current_eleve_retards</td>
					<td>";
	}

	//if($etat_periode=="N") {
	if($acces_saisie) {
		//$chaine_test_vocabulaire.="ajaxVerifAppreciations('".$lig_ele->login."', '".$id_classe."', 'n3".$num_id."');\n";

		echo "
						<input type='hidden' name='login_eleve[$cpt]' id='login_eleve_3".$num_id."' value='".$lig_ele->login."' />
						<textarea id=\"n3".$num_id."\" name='no_anti_inject_app_eleve_$cpt' rows='2' cols='50'  wrap=\"virtual\" 
											onKeyDown=\"clavier(this.id,event);\" 
											onchange=\"changement()\" 
											onfocus=\"focus_suivant(3".$num_id.");document.getElementById('focus_courant').value='3".$num_id."'; repositionner_commtype();\" 
											onblur=\"ajaxVerifAppreciations('".$lig_ele->login."_t".$num_periode."', '".$id_classe."', 'n3".$num_id."');\">$current_eleve_appreciation_absences</textarea>
						<div id='div_verif_n3".$num_id."' style='color:red;'>";
		if(!getSettingANon('active_recherche_lapsus')) {
			echo teste_lapsus($current_eleve_appreciation_absences);
		}
		echo "</div>";
	}
	else {
		echo "
					".nl2br($current_eleve_appreciation_absences);
	}
	echo "
					</td>
				</tr>";

	$cpt++;
	$num_id++;
}
echo "
			</tbody>
		</table>";

	//if($etat_periode=="N") {
	if($acces_saisie) {
		echo "
			<p><input type='submit' value='Enregistrer' /></p>";
	}
	echo "
	</fieldset>
</form>

<script type='text/javascript'>\n";

/*
if((isset($chaine_test_vocabulaire))&&($chaine_test_vocabulaire!="")) {
	echo $chaine_test_vocabulaire;
}
*/

echo "
// Pour éviter une erreur dans les commentaires-types:
id_groupe='';

function focus_suivant(num){
	temoin='';
	// La variable 'dernier' peut dépasser de l'effectif de la classe... mais cela n'est pas dramatique
	dernier=num+".$eff_ele."
	// On parcourt les champs à partir de celui de l'élève en cours jusqu'à rencontrer un champ existant
	// (pour réussir à passer un élève qui ne serait plus dans la période)
	// Après validation, c'est ce champ qui obtiendra le focus si on n'était pas à la fin de la liste.
	for(i=num;i<dernier;i++){
		suivant=i+1;
		if(temoin==''){
			if(document.getElementById('n'+suivant)){
				document.getElementById('info_focus').value=suivant;
				temoin=suivant;
			}
		}
	}

	document.getElementById('info_focus').value=temoin;
}

function repositionner_commtype() {
	if(document.getElementById('div_commtype')) {
		if(document.getElementById('div_commtype').style.display!='none') {
			x=document.getElementById('div_commtype').style.left;
			afficher_div('div_commtype','y',20,20);
			document.getElementById('div_commtype').style.left=x;
		}
	}
}

</script>\n";

echo "</div>\n";

require_once("../lib/footer.inc.php");
?>
