<?php
/**
 * Gestion des bulletins
 * 
 * $_POST['activer'] activation/désactivation
 * $_POST['is_posted']
 * 
 *
 * @copyright Copyright 2001, 2013 Thomas Belliard, Laurent Delineau, Edouard Hue, Eric Lebrun
 * @license GNU/GPL, 
 * @package Carnet_de_notes
 * @subpackage administration
 * @see checkAccess()
 * @see saveSetting()
 * @see suivi_ariane()
 */

/* This file is part of GEPI.
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

$accessibilite="y";
$titre_page = "Gestion module bulletins";
$niveau_arbo = 1;
$gepiPathJava="./..";

/**
 * Fichiers d'initialisation
 */
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

// Check access
if (!checkAccess()) {
	header("Location: ../logout.php?auto=1");
	die();
}

/******************************************************************
 *    Enregistrement des variables passées en $_POST si besoin
 ******************************************************************/
$msg = '';
$post_reussi=FALSE;

if(isset($_POST['is_posted'])) {
	check_token();

	if (isset($_POST['activer'])) {
		if (!saveSetting("active_bulletins", $_POST['activer'])) $msg = "Erreur lors de l'enregistrement du paramètre activation/désactivation !";
	}

	if (isset($_POST['vider_absences_bulletins'])) {
		$sql="DELETE FROM absences;";
		$nettoyage=mysqli_query($GLOBALS["mysqli"], $sql);
		if (!$nettoyage) {$msg = "Erreur lors du \"vidage\" de la table 'absences'.";} else {$msg = "La table 'absences' a été vidée.";}
	}

	if($_POST['is_posted']=="param_divers") {
		if (isset($_POST['bullNoMoyGenParDefaut'])) {
			if(!saveSetting('bullNoMoyGenParDefaut', "yes")) {
				$msg.="Erreur lors de l'enregistrement de 'bullNoMoyGenParDefaut'.<br />";
			}
		}
		else {
			if(!saveSetting('bullNoMoyGenParDefaut', "no")) {
				$msg.="Erreur lors de l'enregistrement de 'bullNoMoyGenParDefaut'.<br />";
			}
		}

		if (isset($_POST['bullNoMoyCatParDefaut'])) {
			if(!saveSetting('bullNoMoyCatParDefaut', "yes")) {
				$msg.="Erreur lors de l'enregistrement de 'bullNoMoyCatParDefaut'.<br />";
			}
		}
		else {
			if(!saveSetting('bullNoMoyCatParDefaut', "no")) {
				$msg.="Erreur lors de l'enregistrement de 'bullNoMoyCatParDefaut'.<br />";
			}
		}

		if (isset($_POST['bullNoSaisieElementsProgrammes'])) {
			if(!saveSetting('bullNoSaisieElementsProgrammes', "yes")) {
				$msg.="Erreur lors de l'enregistrement de 'bullNoSaisieElementsProgrammes'.<br />";
			}
		}
		else {
			if(!saveSetting('bullNoSaisieElementsProgrammes', "no")) {
				$msg.="Erreur lors de l'enregistrement de 'bullNoSaisieElementsProgrammes'.<br />";
			}
		}


		if (isset($_POST['insert_mass_appreciation_type'])) {
			if(!saveSetting('insert_mass_appreciation_type', "y")) {
				$msg.="Erreur lors de l'enregistrement de 'insert_mass_appreciation_type'.<br />";
			}
		}
		else {
			if(!saveSetting('insert_mass_appreciation_type', "n")) {
				$msg.="Erreur lors de l'enregistrement de 'insert_mass_appreciation_type'.<br />";
			}
		}

		$sql="DELETE FROM b_droits_divers WHERE nom_droit='insert_mass_appreciation_type';";
		$del=mysqli_query($mysqli, $sql);
		$nb_mass=0;
		$login_user_mass_app=isset($_POST['login_user_mass_app']) ? $_POST['login_user_mass_app'] : array();
		for($loop=0;$loop<count($login_user_mass_app);$loop++) {
			$sql="INSERT INTO b_droits_divers SET nom_droit='insert_mass_appreciation_type', valeur_droit='y', login='".$login_user_mass_app[$loop]."';";
			$insert=mysqli_query($mysqli, $sql);
			if(!$insert) {
				$msg.="Erreur lors de l'enregistrement du droit insert_mass_appreciation_type pour ".civ_nom_prenom($login_user_mass_app[$loop]).".<br />";
			}
			else {
				$nb_mass++;
			}
		}
		if($nb_mass>0) {
			$msg.="$nb_mass droit(s) insert_mass_appreciation_type enregistré(s).<br />";
		}

		$sql="DELETE FROM b_droits_divers WHERE nom_droit='insert_mass_appreciation_type_d_apres_moyenne';";
		$del=mysqli_query($mysqli, $sql);
		$nb_mass=0;
		$login_user_mass_app_moy=isset($_POST['login_user_mass_app_moy']) ? $_POST['login_user_mass_app_moy'] : array();
		for($loop=0;$loop<count($login_user_mass_app_moy);$loop++) {
			$sql="INSERT INTO b_droits_divers SET nom_droit='insert_mass_appreciation_type_d_apres_moyenne', valeur_droit='y', login='".$login_user_mass_app_moy[$loop]."';";
			$insert=mysqli_query($mysqli, $sql);
			if(!$insert) {
				$msg.="Erreur lors de l'enregistrement du droit insert_mass_appreciation_type_d_apres_moyenne pour ".civ_nom_prenom($login_user_mass_app_moy[$loop]).".<br />";
			}
			else {
				$nb_mass++;
			}
		}
		if($nb_mass>0) {
			$msg.="$nb_mass droit(s) insert_mass_appreciation_type_d_apres_moyenne enregistré(s).<br />";
		}
	}
}

if (isset($_POST['acces_app_ele_resp'])) {
	$acces_app_ele_resp=$_POST['acces_app_ele_resp'];
	if (!saveSetting("acces_app_ele_resp", $acces_app_ele_resp)) {
		$msg .= "Erreur lors de l'enregistrement de 'acces_app_ele_resp' !<br />";
	}
	else {
		$msg .= "Enregistrement de 'acces_app_ele_resp' effectué.<br />";
	}
}
if (isset($_POST['acces_moy_ele_resp'])) {
	$acces_moy_ele_resp=$_POST['acces_moy_ele_resp'];
	if (!saveSetting("acces_moy_ele_resp", $acces_moy_ele_resp)) {
		$msg .= "Erreur lors de l'enregistrement de 'acces_moy_ele_resp' !<br />";
	}
	else {
		$msg .= "Enregistrement de 'acces_moy_ele_resp' effectué.<br />";
	}
}
if (isset($_POST['acces_moy_ele_resp_cn'])) {
	$acces_moy_ele_resp_cn=$_POST['acces_moy_ele_resp_cn'];
	if (!saveSetting("acces_moy_ele_resp_cn", $acces_moy_ele_resp_cn)) {
		$msg .= "Erreur lors de l'enregistrement de 'acces_moy_ele_resp_cn' !<br />";
	}
	else {
		$msg .= "Enregistrement de 'acces_moy_ele_resp_cn' effectué.<br />";
	}
}

if (isset($_POST['is_posted']) and ($msg=='')){
  $msg = "Les modifications ont été enregistrées (".strftime("le %d/%m/%Y à %H:%M:%S").") !";
  $post_reussi=TRUE;
}

// on demande une validation si on quitte sans enregistrer les changements
$messageEnregistrer="Des informations ont été modifiées. Voulez-vous vraiment quitter sans enregistrer ?";
/****************************************************************
                     HAUT DE PAGE
****************************************************************/

// ====== Inclusion des balises head et du bandeau =====
/**
 * Entête de la page
 */
include_once("../lib/header_template.inc.php");

/****************************************************************
			FIN HAUT DE PAGE
****************************************************************/

if (!suivi_ariane($_SERVER['PHP_SELF'],$titre_page))
		echo "erreur lors de la création du fil d'ariane";

/****************************************************************
			BAS DE PAGE
****************************************************************/
$tbs_microtime	="";
$tbs_pmv="";
require_once ("../lib/footer_template.inc.php");

/****************************************************************
			On s'assure que le nom du gabarit est bien renseigné
****************************************************************/
if ((!isset($_SESSION['rep_gabarits'])) || (empty($_SESSION['rep_gabarits']))) {
	$_SESSION['rep_gabarits']="origine";
}

//==================================
// Décommenter la ligne ci-dessous pour afficher les variables $_GET, $_POST, $_SESSION et $_SERVER pour DEBUG:
// $affiche_debug=debug_var();


$nom_gabarit = '../templates/'.$_SESSION['rep_gabarits'].'/bulletin/index_admin_template.php';

$tbs_last_connection=""; // On n'affiche pas les dernières connexions
/**
 * Inclusion du gabarit
 */
include($nom_gabarit);

// ------ on vide les tableaux -----
unset($menuAffiche);

?>
