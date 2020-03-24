<?php
/*
*
* Copyright 2001, 2017 Thomas Belliard, Laurent Delineau, Edouard Hue, Eric Lebrun, Stephane Boireau
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
//require_once("../lib/initialisations.inc.php");
$niveau_arbo = 0;
require_once("./lib/initialisations.inc.php");

// Resume session
$resultat_session = $session_gepi->security_check();
if ($resultat_session == 'c') {
	//header("Location: ../logout.php?auto=1");
	header("Location: ./logout.php?auto=1");
	die();
} else if ($resultat_session == '0') {
	//header("Location: ../logout.php?auto=1");
	header("Location: ./logout.php?auto=1");
	die();
}

// INSERT INTO `droits` VALUES ('/accueil_simpl_prof.php', 'F', 'V', 'F', 'F', 'F', 'F', 'F', 'Page d accueil simplifiée pour les profs', '');
if (!checkAccess()) {
	//header("Location: ../logout.php?auto=1");
	header("Location: ./logout.php?auto=1");
	die();
}

/*
//On vérifie si le module est activé
if (getSettingValue("active_carnets_notes")!='y') {
	die("Le module n'est pas activé.");
}
*/

$active_carnets_notes=getSettingValue("active_carnets_notes");
$active_cahiers_texte=(affiche_lien_cdt() ? "y" : "n");
//echo "\$active_cahiers_texte=$active_cahiers_texte<br />";
$active_module_trombinoscopes=getSettingValue("active_module_trombinoscopes");

if(($active_carnets_notes!='y')&&($active_cahiers_texte!='y')&&($active_module_trombinoscopes!='y')) {
	//die("Le module n'est pas activé.");
	$sql="UPDATE preferences SET value='n' WHERE name='accueil_simpl' AND login='".$_SESSION['login']."';";
	$res=mysqli_query($GLOBALS["mysqli"], $sql);

	header("Location: ./accueil.php");
	die();
}


// Préférences des profs à récupérer dans la table 'preferences':

$pref_accueil_ct=getPref($_SESSION['login'],'accueil_ct',"y");
$pref_accueil_trombino=getPref($_SESSION['login'],'accueil_trombino',"y");

// Préférences jouant sur les colspan de période:
$pref_accueil_cn=getPref($_SESSION['login'],'accueil_cn',"y");
$pref_accueil_bull=getPref($_SESSION['login'],'accueil_bull',"y");
$pref_accueil_visu=getPref($_SESSION['login'],'accueil_visu',"y");
$pref_accueil_liste_pdf=getPref($_SESSION['login'],'accueil_liste_pdf',"y");

$pref_accueil_infobulles=getPref($_SESSION['login'],'accueil_infobulles',"y");

if(!getSettingAOui('active_bulletins')) {
	$pref_accueil_bull="n";
	$pref_accueil_visu="n";
}

// On ne propose pas les colonnes si le module est désactivé
if($active_cahiers_texte=='n') {$pref_accueil_ct="n";}
if($active_carnets_notes=='n') {$pref_accueil_cn="n";}
if($active_module_trombinoscopes=='n') {$pref_accueil_trombino="n";}


// Calcul du colspan pour les colonnes Périodes
$colspan=0;
if($pref_accueil_cn=="y"){$colspan++;}
if($pref_accueil_bull=="y"){$colspan+=2;}
//if($pref_accueil_bullsimp=="y"){$colspan++;}

// Visualisation des graphes et des bulletins simplifiés:
if($pref_accueil_visu=="y") {
	$colspan+=2;
}

if($pref_accueil_liste_pdf=="y"){$colspan++;}

$tab_mes_groupes_grp_admin=get_tab_groupes_grp_groupes();

$afficher_col_notanet="n";
if (getSettingValue("active_notanet") == "y") {
	$tab_groupes_notanet=array();
	$sql="SELECT nv.*, jgc.id_groupe FROM notanet_verrou nv, 
						j_groupes_classes jgc, 
						j_groupes_professeurs jgp
					WHERE nv.id_classe=jgc.id_classe AND 
							jgc.id_groupe=jgp.id_groupe AND 
							jgp.login='".$_SESSION['login']."';";
	//echo "$sql<br />";
	$res_notanet=mysqli_query($GLOBALS["mysqli"], $sql);
	if(mysqli_num_rows($res_notanet)>0) {
		//$afficher_col_notanet="y";
		while($lig_notanet=mysqli_fetch_object($res_notanet)) {
			// On peut avoir plusieurs lignes retournées, s'il y a plusieurs types_brevet dans une classe/groupe
			if(isset($tab_groupes_notanet[$lig_notanet->id_groupe]['verrouillage'])) {
				if($lig_notanet->verrouillage=="N") {
					$tab_groupes_notanet[$lig_notanet->id_groupe]['verrouillage']=$lig_notanet->verrouillage;
				}
			}
			else {
				$tab_groupes_notanet[$lig_notanet->id_groupe]['verrouillage']=$lig_notanet->verrouillage;
			}
			//echo "\$tab_groupes_notanet[$lig_notanet->id_groupe]['verrouillage']=".$tab_groupes_notanet[$lig_notanet->id_groupe]['verrouillage']."<br />";

			if($lig_notanet->verrouillage=="N") {
				$afficher_col_notanet="y";
			}
			// mod_notanet/saisie_app.php?id_groupe=2253
		}
	}


	$tab_groupes_notanet_saisie_note=array();
	// Test sur le fait qu'il y a de telles notes à saisir pour le prof connecté
	$sql="SELECT DISTINCT jgp.id_groupe FROM notanet_ele_type net,
				j_eleves_groupes jeg,
				j_groupes_professeurs jgp,
				j_groupes_matieres jgm,
				notanet_corresp nc
			WHERE net.login=jeg.login AND
				jeg.id_groupe=jgp.id_groupe AND
				jgp.login='".$_SESSION['login']."' AND
				jeg.id_groupe=jgm.id_groupe AND
				jgm.id_matiere=nc.matiere AND
				nc.mode='saisie';";
	//echo "$sql<br />";
	$res_notanet=mysqli_query($GLOBALS["mysqli"], $sql);
	if(mysqli_num_rows($res_notanet)>0) {
		while($lig_notanet=mysqli_fetch_object($res_notanet)) {
			if(!getSettingAOui("notanet_saisie_note_ouverte")) {
				$tab_groupes_notanet_saisie_note[$lig_notanet->id_groupe]['verrouillage']="O";
				//echo $lig_notanet->id_groupe."<br />";
			}
			else {
				$tab_groupes_notanet_saisie_note[$lig_notanet->id_groupe]['verrouillage']="N";
				$afficher_col_notanet="y";
			}
		}
	}
}

//=================================================
$accueil_afficher_tous_les_groupes=isset($_POST['accueil_afficher_tous_les_groupes']) ? $_POST['accueil_afficher_tous_les_groupes'] : (isset($_GET['accueil_afficher_tous_les_groupes']) ? $_GET['accueil_afficher_tous_les_groupes'] : (isset($_SESSION['accueil_afficher_tous_les_groupes']) ? $_SESSION['accueil_afficher_tous_les_groupes'] : NULL));

if(!isset($accueil_afficher_tous_les_groupes)) {
	$accueil_afficher_tous_les_groupes="n";
}

$_SESSION['accueil_afficher_tous_les_groupes']=$accueil_afficher_tous_les_groupes;
//=================================================

/*
echo "<pre>";
print_r($tab_groupes_notanet);
echo "</pre>";
*/

// Préférences des profs à récupérer par la suite dans la table 'preferences':
// 1: icones
// 2: textes
// 3: icones et textes
//$accueil_aff_txt_icon=3;
//$accueil_aff_txt_icon=isset($_GET['txtico']) ? $_GET['txtico'] : 1;
//$accueil_aff_txt_icon=getPref($_SESSION['login'],'accueil_aff_txt_icon',"1");
$accueil_aff_txt_icon=1;
// CELA A ETE DESACTIVE... PARCE QUE LISIBLE UNIQUEMENT EN MODE icones seuls


// Styles specifiques à la page avec chemin relatif à la racine du Gepi:
//$style_specifique="accueil_simpl_prof.css";
$style_specifique="accueil_simpl_prof";


//**************** EN-TETE *****************
$titre_page = "Accueil GEPI";
require_once("./lib/header.inc.php");
//**************** FIN EN-TETE *************

/*
foreach($tmp_mes_classes as $current_id_classe => $current_classe) {
	echo "<hr />";
	echo affiche_choix_action_conseil_de_classe($current_id_classe, "_blank");
	echo "<hr />";
}
*/

//echo "\$colspan=$colspan<br />";

insere_lien_calendrier_crob("right");

echo "<div class='norme'><p class='bold'>\n";
//echo "<a href=\"../accueil.php\"><img src='../images/icons/back.png' alt='Retour' class='back_link'/> Retour accueil </a> | \n";
//echo "<a href=\"./accueil.php?accueil_simpl=n\"> Accès à l'interface complète </a>";
echo "<a href=\"./accueil.php?accueil_simpl=n\">Accès au menu d'accueil</a>";
//echo " | \n";
//echo "<a href='index.php'> Carnet de notes </a> | \n";
echo " | \n";
//echo "<a href='./gestion/config_prefs.php'> Paramétrer mon interface </a>\n";
echo "<a href='./utilisateurs/mon_compte.php#accueil_simpl_prof'> Paramétrer mon interface </a>\n";

if($accueil_afficher_tous_les_groupes=="n") {
	echo "| <a href='".$_SERVER['PHP_SELF']."?accueil_afficher_tous_les_groupes=y' title=\"Via Gérer mon compte (en haut à droite), vous pouvez paramétrer l'ordre d'affichage des enseignements.\nVous pouvez même masquer certains enseignements.\nEn cliquant ici, vous pourrez afficher tous les enseignements sans prendre en compte les ordres et masquages éventuels.\">Afficher tous les groupes sans tri</a>\n";
}
else {
	echo "| <a href='".$_SERVER['PHP_SELF']."?accueil_afficher_tous_les_groupes=n' title=\"Vous pouvez choisir les groupes à afficher en page d'accueil simplifiée.
Pour cela consultez la rubrique
    Page d'accueil simplifiée
de la page
    Gérer mon compte.\">Trier mes groupes</a>\n";
}

echo "</p>\n";
echo "</div>\n";

// Liste des Accès ouverts en consultation à vos CDT
affiche_acces_cdt();

if(in_array($_SESSION['statut'], array('professeur', 'cpe', 'scolarite'))) {
	//echo "<div align='center'>".afficher_les_evenements()."</div>";
	$liste_evenements=afficher_les_evenements();
}

echo "<center>\n";

//Affichage des messages
include("affichage_des_messages.inc.php");

//================================
$invisibilite_groupe=array();
$invisibilite_groupe['bulletins']=array();
$invisibilite_groupe['cahier_notes']=array();
$invisibilite_groupe['cahier_texte']=array();
$sql="SELECT jgv.* FROM j_groupes_visibilite jgv, j_groupes_professeurs jgp WHERE jgv.id_groupe=jgp.id_groupe AND jgp.login='".$_SESSION['login']."' AND jgv.visible='n';";
$res_jgv=mysqli_query($GLOBALS["mysqli"], $sql);
if(mysqli_num_rows($res_jgv)>0) {
	while($lig_jgv=mysqli_fetch_object($res_jgv)) {
		$invisibilite_groupe[$lig_jgv->domaine][]=$lig_jgv->id_groupe;
	}
}

//================================
if($accueil_afficher_tous_les_groupes=="n") {
	$tab_grp_order=array();
	$tab_grp_hidden=array();
	$sql="SELECT * FROM preferences WHERE login='".$_SESSION['login']."' AND name LIKE 'accueil_simpl_id_groupe_order_%' ORDER BY value;";
	$res_grp_order=mysqli_query($GLOBALS["mysqli"], $sql);
	if(mysqli_num_rows($res_grp_order)>0) {
		while($lig_grp_order=mysqli_fetch_object($res_grp_order)) {
			$tmp_id_groupe=preg_replace("/^accueil_simpl_id_groupe_order_/", "", $lig_grp_order->name);
			if($lig_grp_order->value=='-1') {
				$tab_grp_hidden[]=$tmp_id_groupe;
			}
			else {
				$tab_grp_order[]=$tmp_id_groupe;
			}
		}
	}

	// On passe en revue les groupes qui ont été triés dans Mon compte
	$groups=array();
	$tmp_groups=get_groups_for_prof($_SESSION["login"],NULL,array('classes', 'periodes', 'eleves', 'profs', 'visibilite')); // 'matieres', 'modalites_elect'
	for($loop=0;$loop<count($tab_grp_order);$loop++) {
		for($i=0;$i<count($tmp_groups);$i++) {
			if($tmp_groups[$i]['id']==$tab_grp_order[$loop]) {
				$groups[]=$tmp_groups[$i];
				break;
			}
		}
	}

	// Les groupes qui n'ont pas été triés dans Mon compte et pas cachés non plus
	for($i=0;$i<count($tmp_groups);$i++) {
		if((!in_array($tmp_groups[$i]['id'], $tab_grp_order))&&(!in_array($tmp_groups[$i]['id'], $tab_grp_hidden))) {
			$groups[]=$tmp_groups[$i];
		}
	}

}
else {
	//$groups=get_groups_for_prof($_SESSION["login"]);
	$groups=get_groups_for_prof($_SESSION["login"],NULL,array('classes', 'periodes', 'eleves', 'profs', 'visibilite')); //  'matieres', 'modalites_elect'
}
//================================


//================================

// Récupérer le nombre max de périodes
$maxper=0;
$tab_noms_periodes=array();
$tab_num_periodes_ouvertes=array();
for($i=0;$i<count($groups);$i++){
	if($maxper<count($groups[$i]["periodes"])) {
		$maxper=count($groups[$i]["periodes"]);
	}

	for($j=1;$j<=count($groups[$i]["periodes"]);$j++){
		if(!in_array($groups[$i]["periodes"][$j]["nom_periode"],$tab_noms_periodes)){
			$tab_noms_periodes[]=$groups[$i]["periodes"][$j]["nom_periode"];
		}
	}

	foreach($groups[$i]["classes"]["classes"] as $classe){
		for($j=1;$j<=count($groups[$i]["classe"]["ver_periode"][$classe['id']]);$j++){
			if($groups[$i]["classe"]["ver_periode"][$classe['id']][$j]=="N"){
				if(!in_array($j,$tab_num_periodes_ouvertes)) {
					$tab_num_periodes_ouvertes[]=$j;
				}
			}
		}
	}
}

if(count($tab_num_periodes_ouvertes)>0){
	sort($tab_num_periodes_ouvertes);

	$affiche_periode=array();
	for($i=1;$i<=$maxper;$i++){
		$affiche_periode[$i]="n";
	}

	for($i=0;$i<count($tab_num_periodes_ouvertes);$i++){
		//echo "\$tab_num_periodes_ouvertes[$i]=$tab_num_periodes_ouvertes[$i]<br />";
		$affiche_periode[$tab_num_periodes_ouvertes[$i]]="y";
	}
}
else{
	$affiche_periode=array();
	for($i=1;$i<=$maxper;$i++){
		$affiche_periode[$i]="y";
	}
}

/*
for($i=0;$i<count($tab_noms_periodes);$i++){
	echo "\$tab_noms_periodes[$i]=$tab_noms_periodes[$i]<br />";
}
*/

$nb_groupes=count($groups);

echo "<script type='text/javascript' src='lib/brainjar_drag.js'></script>\n";
echo "<script type='text/javascript' src='lib/position.js'></script>\n";

if($colspan>0){
	for($i=1;$i<=$maxper;$i++){
		$colspan_per[$i]=$colspan;

		if($pref_accueil_visu=='y') {
			$test_acces_bull_simp[$i]="n";
			for($k=0;$k<count($groups);$k++){
				if ((getSettingValue("GepiAccesBulletinSimpleProf") == "yes")||(getSettingValue("GepiAccesBulletinSimpleProfTousEleves") == "yes")) {
					$sql="SELECT 1=1 FROM j_eleves_groupes jeg,
											j_groupes_professeurs jgp
									WHERE jeg.id_groupe= jgp.id_groupe AND
											jeg.periode='$i' AND
											jgp.login='".$_SESSION['login']."' AND
											jeg.id_groupe='".$groups[$k]['id']."' LIMIT 1;";
					$res_test_acces_bull_simp=mysqli_num_rows(mysqli_query($GLOBALS["mysqli"], $sql));
					if($res_test_acces_bull_simp>0) {$test_acces_bull_simp[$i]="y";break;}
				}

				if((getSettingValue("GepiAccesBulletinSimplePP") == "yes")&&($test_acces_bull_simp[$i]=="n")) {
					$sql="SELECT 1=1 FROM j_eleves_groupes jeg,
													j_eleves_professeurs jep
											WHERE jep.login=jeg.login AND
													jeg.periode='$i' AND
													jep.professeur='".$_SESSION['login']."' AND
													jeg.id_groupe='".$groups[$k]['id']."' LIMIT 1;";
					//echo "$sql<br />";
					$res_test_acces_bull_simp=mysqli_num_rows(mysqli_query($GLOBALS["mysqli"], $sql));
					if($res_test_acces_bull_simp>0) {$test_acces_bull_simp[$i]="y";break;}
				}
			}

			if($test_acces_bull_simp[$i]=="n") {
				$colspan_per[$i]--;
			}
		}
		//echo "\$colspan_per[$i]=$colspan_per[$i]<br />";
	}
}


// Si on affiche les colonnes CN, Bull ou Visu (graphe et ou BullSimp), on a trois lignes de tableau à gérer pour la ligne d'entête:
$chaine_rowspan_ligne_entete="";
if(($pref_accueil_cn=="y")||($pref_accueil_bull=="y")||($pref_accueil_visu=="y")) {
	$chaine_rowspan_ligne_entete=" rowspan='3'";
}
elseif($colspan>0) {
	if((($active_carnets_notes=="y")&&($pref_accueil_cn=="y")&&($colspan>0))||
		($pref_accueil_bull=="y")||
		($pref_accueil_visu=="y")) {$chaine_rowspan_ligne_entete=" rowspan='3'";}
	else {$chaine_rowspan_ligne_entete=" rowspan='2'";}
}



//echo "<table border='1'>\n";
echo "<table class='contenu boireaus boireaus_alt' summary=\"Tableau de la liste des enseignements avec les liens vers le Carnet de notes, les bulletins, les graphes,...\">\n";
echo "<tr>\n";
echo "<th";
echo $chaine_rowspan_ligne_entete;
echo ">\n";
echo "Groupe</th>\n";

echo "<th";
echo $chaine_rowspan_ligne_entete;
echo ">\n";
echo "Classes</th>\n";

// mod_abs2
if ((getSettingValue("active_module_absence_professeur")=='y')&&(getSettingValue("active_module_absence")=='2')) {
	echo "<th";
	if($colspan>0) {
		if((($active_carnets_notes=="y")&&($pref_accueil_cn=="y")&&($colspan>0))||
			($pref_accueil_bull=="y")||
			($pref_accueil_visu=="y")) {echo " rowspan='3'";}
		else {echo " rowspan='2'";}
	}
	echo ">\n";
	echo "Absences\n";
	echo "</th>\n";
}

if($pref_accueil_ct=="y") {
	echo "<th";
	echo $chaine_rowspan_ligne_entete;
	echo ">\n";
	echo "Cahier de Textes\n";
	echo "</th>\n";
}


if($pref_accueil_trombino=="y") {
	echo "<th";
	echo $chaine_rowspan_ligne_entete;
	echo ">\n";
	echo "Trombino<br />scope\n";
	echo "</th>\n";
}

//echo "count(\$tab_noms_periodes)=".count($tab_noms_periodes)."<br />";
//echo "\$maxper=$maxper<br />";

if(($pref_accueil_cn=="y")||
($pref_accueil_bull=="y")||
($pref_accueil_visu=="y")||
($pref_accueil_liste_pdf=="y")) {
	if($colspan>0){
		if(count($tab_noms_periodes)!=$maxper) {
			for($i=1;$i<=$maxper;$i++){
				echo "<th colspan='".$colspan_per[$i]."'>";

				echo "<span id='h_lien_affiche_$i'>";
				echo "<a href='#' onClick=\"modif_col($i,'affiche');return false;\" title=\"Afficher les items de la période $i\">";
				echo "P$i";
				echo "</a>";
				echo "</span>\n";

				echo "<span id='h_lien_cache_$i'>";
				echo "<a href='#' onClick=\"modif_col($i,'cache');return false;\" title=\"Réduire/masquer les items de la période $i\">";
				echo "Période $i";
				echo "</a>";
				echo "</span>\n";

				echo "</th>\n";
			}
		}
		else{
			for($i=0;$i<count($tab_noms_periodes);$i++){
				$j=$i+1;
				echo "<th colspan='".$colspan_per[$j]."'>";

				echo "<span id='h_lien_affiche_$j'>";
				echo "<a href='#' onClick=\"modif_col($j,'affiche');return false;\" title=\"Afficher les items de la période $j\">";
				echo "P$j";
				echo "</a>";
				echo "</span>\n";

				echo "<span id='h_lien_cache_$j'>";
				echo "<a href='#' onClick=\"modif_col($j,'cache');return false;\" title=\"Réduire/masquer les items de la période $j\">";
				echo $tab_noms_periodes[$i];
				echo "</a>";
				echo "</span>\n";

				echo "</th>\n";
			}
		}
	}
}
if($afficher_col_notanet=="y") {
	echo "<th";
	if(($active_carnets_notes=="y")&&($pref_accueil_cn=="y")&&($colspan>0)){echo " rowspan='3'";}
	echo ">\n";
	echo "<span title=\"Remplissage des appréciations pour le brevet des collèges (DNB).

Saisie ou import des notes d'EPS.\">Brevet</span>\n";
	echo "</th>\n";
}
echo "</tr>\n";


if(($pref_accueil_cn=="y")||
($pref_accueil_bull=="y")||
($pref_accueil_visu=="y")||
($pref_accueil_liste_pdf=="y")) {
	if($colspan>0){
		echo "<tr>\n";
		for($i=1;$i<=$maxper;$i++){
			if($pref_accueil_cn=="y"){
				echo "<th rowspan='2'>\n";
				echo "<span id='h_cn_$i'>Carnet de notes</span>\n";
				echo "</th>\n";
			}
	
			if($pref_accueil_bull=="y"){
				echo "<th colspan='2'>\n";
				echo "<span id='h_b_$i'>Bulletin</span>\n";
				echo "</th>\n";
			}
	
			if($pref_accueil_visu=="y"){
				if($test_acces_bull_simp[$i]=="y") {
					echo "<th colspan='2'>\n";
				}
				else {
					echo "<th>\n";
				}
				echo "<span id='h_v_$i'>Visualisation</span>\n";
				echo "</th>\n";
			}
	
			if($pref_accueil_liste_pdf=="y"){
				echo "<th";
				if((($active_carnets_notes=="y")&&($pref_accueil_cn=="y"))||
						($pref_accueil_bull=="y")||
						($pref_accueil_visu=="y")
					) {echo " rowspan='2'";}
				echo ">\n";
				echo "<span id='h_liste_pdf_$i'>Liste PDF/CSV</span>\n";
				echo "</th>\n";
			}
		}
		echo "</tr>\n";
	}

	if($colspan>0){
		echo "<tr>\n";
		for($i=1;$i<=$maxper;$i++){
			if($pref_accueil_bull=="y"){
				echo "<th>\n";
				echo "<span id='h_bn_$i'>Notes</span>";
				echo "</th>\n";
	
				echo "<th>\n";
				echo "<span id='h_ba_$i'>Appr.</span>";
				echo "</th>\n";
			}
	
			if($pref_accueil_visu=="y"){
				echo "<th>\n";
				echo "<span id='h_g_$i'>Graphe</span>";
				echo "</th>\n";

				if($test_acces_bull_simp[$i]=="y") {
					echo "<th>\n";
					echo "<span id='h_bs_$i'>Bull.Simp</span>";
					echo "</th>\n";
				}
			}
		}
		echo "</tr>\n";
	}
}

// https://127.0.0.1/steph/gepi-trunk/cahier_texte/index.php?id_groupe=29&year=2007&month=6&day=30&edit_devoir=
$day=date("d");
$month=date("m");
$year=date("Y");

$date_courante_debut_journee_mysql=strftime("%Y-%m-%d 00:00:00");
$tab_conseils_de_classes=get_tab_date_prochain_evenement_telle_classe("", "conseil_de_classe", "y");

$tab_liste_infobulles=array();

for($i=0;$i<count($groups);$i++){

	echo "<tr valign='top'>\n";
	//if(count($groups[$i]["profs"]["list"])>1) {
		echo "<td title=\"".$groups[$i]["profs"]["proflist_string"]."\">";
	/*
	}
	else {
		echo "<td>";
	}
	*/

	//if(acces_modif_liste_eleves_grp_groupes($groups[$i]['id'])) {
	if(in_array($groups[$i]['id'],$tab_mes_groupes_grp_admin)) {
		echo "<div style='float:right;width:16px;'><a href='./groupes/grp_groupes_edit_eleves.php?id_groupe=".$groups[$i]['id']."' title=\"Si la liste des élèves du groupe n'est pas correcte, vous êtes autorisé à modifier la liste.\"><img src='./images/icons/edit_user.png' class='icone16' alt=\"Modifier.\" /></a></div>";
	}

	echo htmlspecialchars($groups[$i]['description'])."</td>\n";

	//echo "<td>".htmlspecialchars($groups[$i]['classlist_string'])."</td>\n";
	echo "<td>\n";
	$cpt=0;
	$liste_classes_du_groupe="";
	foreach($groups[$i]["classes"]["classes"] as $classe){
		if($cpt>0){
			echo ", ";
			$liste_classes_du_groupe.=", ";
		}
		echo "<a href='groupes/popup.php?id_groupe=".$groups[$i]['id']."&amp;id_classe=".$classe['id']."' onClick=\"ouvre_popup_visu_groupe('".$groups[$i]['id']."','".$classe['id']."','');return false;\" target='_blank'";

		if($pref_accueil_infobulles=="y"){
			echo " onmouseover=\"afficher_div('info_popup_".$i."_".$cpt."','y',10,10);\" onmouseout=\"cacher_div('info_popup_".$i."_".$cpt."');\"";
		}

		echo ">";
		echo " ".$classe['classe'];
		echo "</a>\n";
		$liste_classes_du_groupe.=" ".$classe['classe'];

		if($pref_accueil_infobulles=="y"){
			echo "<div id='info_popup_".$i."_".$cpt."' class='infobulle_corps' style='border: 1px solid #000000; color: #000000; padding: 0px; position: absolute; width: 18em;' onmouseout=\"cacher_div('info_popup_".$i."_".$cpt."');\">Liste des élèves de ".htmlspecialchars($groups[$i]['description'])." (<i>".preg_replace("/ /","&nbsp;",$classe['classe'])."</i>).</div>\n";

			$tab_liste_infobulles[]='info_popup_'.$i.'_'.$cpt;
		}
		$cpt++;
	}
	echo "</td>\n";

	//$liste_classes_du_groupe=trim($liste_classes_du_groupe);
	$liste_classes_du_groupe=preg_replace("/ /","&nbsp;",trim($liste_classes_du_groupe));

	// mod_abs2
	if ((getSettingValue("active_module_absence_professeur")=='y')&&(getSettingValue("active_module_absence")=='2')) {
		echo "<td>";
		echo "<a href='mod_abs2/index.php?type_selection=id_groupe&amp;id_groupe=".$groups[$i]['id']."'";
		if($pref_accueil_infobulles=="y"){
			echo " onmouseover=\"afficher_div('info_abs_$i','y',10,10);\" onmouseout=\"cacher_div('info_abs_$i');\"";
		}
		echo ">";
			echo "<img src='images/icons/absences.png' width='32' height='32' alt='Absences' border='0' />";
		echo "</a>";

		if($pref_accueil_infobulles=="y"){
			echo "<div id='info_abs_$i' class='infobulle_corps' style='border: 1px solid #000000; color: #000000; padding: 0px; position: absolute; width: 18em;' onmouseout=\"cacher_div('info_abs_$i');\">Absences de ".htmlspecialchars($groups[$i]['description'])." (<i>$liste_classes_du_groupe</i>).</div>\n";

			$tab_liste_infobulles[]='info_abs_'.$i;
		}

		echo "</td>\n";
	}

	if($pref_accueil_ct=="y") {
		// https://127.0.0.1/steph/gepi-trunk/cahier_texte/index.php?id_groupe=29&year=2007&month=6&day=30&edit_devoir=
		// Cahier de textes:
		echo "<td>";
		if(!in_array($groups[$i]['id'],$invisibilite_groupe['cahier_texte'])) {
			echo "<a href='cahier_texte/index.php?id_groupe=".$groups[$i]['id']."&amp;year=$year&amp;month=$month&amp;day=$day&amp;edit_devoir='";
			if($pref_accueil_infobulles=="y"){
				echo " onmouseover=\"afficher_div('info_ct_$i','y',10,10);\" onmouseout=\"cacher_div('info_ct_$i');\"";
			}
			echo ">";
			echo "<img src='images/icons/cahier_textes.png' width='32' height='32' alt='Cahier de textes' border='0' />";
			echo "</a>";

			if($pref_accueil_infobulles=="y"){
				echo "<div id='info_ct_$i' class='infobulle_corps' style='border: 1px solid #000000; color: #000000; padding: 0px; position: absolute; width: 18em;' onmouseout=\"cacher_div('info_ct_$i');\">Cahier de textes de ".htmlspecialchars($groups[$i]['description'])." (<i>$liste_classes_du_groupe</i>).</div>\n";

				$tab_liste_infobulles[]='info_ct_'.$i;
			}
		}
		echo "</td>\n";
	}

	if($pref_accueil_trombino=="y") {
		echo "<td>\n";
		echo "<a href='mod_trombinoscopes/trombinoscopes.php' onClick=\"valide_trombino('".$groups[$i]['id']."'); return false;\"";

		if($pref_accueil_infobulles=="y"){
			echo " onmouseover=\"afficher_div('info_trombino_$i','y',10,10);\" onmouseout=\"cacher_div('info_trombino_$i');\"";
		}
		echo ">";
		echo "<img src='images/icons/trombino.png' width='32' height='32' alt='Trombinoscope' border='0' />";
		echo "</a>\n";

		if($pref_accueil_infobulles=="y"){
			echo "<div id='info_trombino_$i' class='infobulle_corps' style='border: 1px solid #000000; color: #000000; padding: 0px; position: absolute; width: 18em;' onmouseout=\"cacher_div('info_trombino_$i');\">Trombinoscope de ".htmlspecialchars($groups[$i]['description'])." (<i>$liste_classes_du_groupe</i>).</div>\n";

			$tab_liste_infobulles[]='info_trombino_'.$i;
		}
		echo "</td>\n";
	}



	if(($pref_accueil_cn=="y")||
	($pref_accueil_bull=="y")||
	($pref_accueil_visu=="y")||
	($pref_accueil_liste_pdf=="y")) {
		if($colspan>0){
			for($j=1;$j<=count($groups[$i]["periodes"]);$j++){
					$statut_verrouillage=$groups[$i]["classe"]["ver_periode"]["all"][$j];
					if($statut_verrouillage==0){
						$class_style="verrouillagetot";
					}
					elseif($statut_verrouillage==3){
						$class_style="deverrouille";
					}
					else{
						$class_style="verrouillagepart";
					}
	
					// Saisie de notes dans le carnet de notes:
					if($pref_accueil_cn=="y") {
						if($class_style!="deverrouille") {
							if(acces_exceptionnel_saisie_cn_groupe_periode($groups[$i]['id'], $j)) {
								echo "<td style='background-color:orange;' title='Accès exceptionnellement ouvert'>\n";
							}
							else {
								echo "<td class='$class_style'>\n";
							}
						}
						else {
							//echo "<td class='$class_style'>\n";
							echo "<td>\n";
						}

						if(!in_array($groups[$i]['id'],$invisibilite_groupe['cahier_notes'])) {
							echo "<div id='h_cn_".$i."_".$j."'>";
							echo "<a href='cahier_notes/index.php?id_groupe=".$groups[$i]['id']."&amp;periode_num=".$groups[$i]['periodes'][$j]['num_periode']."'";
							if($pref_accueil_infobulles=="y") {
								echo " onmouseover=\"afficher_div('info_cn_".$i."_".$j."','y',10,10);\" onmouseout=\"cacher_div('info_cn_".$i."_".$j."');\"";
							}
							echo ">";
							echo "<img src='images/icons/carnet_notes.png' width='32' height='32' alt='Saisie de notes' border='0' />";
							echo "</a>";
		
							if($pref_accueil_infobulles=="y") {
								echo "<div id='info_cn_".$i."_".$j."' class='infobulle_corps' style='border: 1px solid #000000; color: #000000; padding: 0px; position: absolute; width: 18em;' onmouseout=\"cacher_div('info_cn_".$i."_".$j."');\">Carnet de notes de ".htmlspecialchars($groups[$i]['description'])." (<i>$liste_classes_du_groupe</i>)<br />".$groups[$i]["periodes"][$j]["nom_periode"].".</div>\n";
		
								$tab_liste_infobulles[]='info_cn_'.$i.'_'.$j;
							}

							/*
							echo "<pre>";
							print_r($groups[$i]['classe']['date_fin']);
							echo "</pre>";
							*/
							$chaine_date_fin_periode="";
							$tmp_tab_date_fin_periode=array();
							$tmp_tab_date_fin_periode_passee=array();
							foreach($groups[$i]['classe']['date_fin'] as $tmp_id_classe => $tmp_classe) {
								if($tmp_classe[$j]>=$date_courante_debut_journee_mysql) {
									$tmp_tab_date_fin_periode[$tmp_classe[$j]][]=$groups[$i]["classes"]["classes"][$tmp_id_classe]["classe"];
								}
								else {
									$tmp_tab_date_fin_periode_passee[$tmp_classe[$j]][]=$groups[$i]["classes"]["classes"][$tmp_id_classe]["classe"];
								}
							}
							foreach($tmp_tab_date_fin_periode as $current_mysql_date => $tmp_tab_classe) {
								$chaine_date_fin_periode.="<br /><span style='font-size:x-small;' title=\"Date de fin de période ";
								for($loop_clas=0;$loop_clas<count($tmp_tab_classe);$loop_clas++) {
									if($loop_clas>0) {
										$chaine_date_fin_periode.=", ";
									}
									$chaine_date_fin_periode.=$tmp_tab_classe[$loop_clas];
								}
								$chaine_date_fin_periode.=" : ".formate_date($current_mysql_date)."\">";
								$chaine_date_fin_periode.=formate_date($current_mysql_date);
								$chaine_date_fin_periode.="</span>";
							}
							foreach($tmp_tab_date_fin_periode_passee as $current_mysql_date => $tmp_tab_classe) {
								$chaine_date_fin_periode.="<br /><span style='font-size:x-small; color:red;' title=\"Date de fin de période dépassée ";
								for($loop_clas=0;$loop_clas<count($tmp_tab_classe);$loop_clas++) {
									if($loop_clas>0) {
										$chaine_date_fin_periode.=", ";
									}
									$chaine_date_fin_periode.=$tmp_tab_classe[$loop_clas];
								}
								$chaine_date_fin_periode.=" : ".formate_date($current_mysql_date)."\">";
								$chaine_date_fin_periode.=formate_date($current_mysql_date);
								$chaine_date_fin_periode.="</span>";
							}
							echo $chaine_date_fin_periode;

							echo "</div>\n";
						}
						else {echo "&nbsp;";}
						echo "</td>\n";
					}
	
	
					if($pref_accueil_bull=="y"){
						// Calcul du nombre de notes et du nombre d'appréciations présentes sur le bulletin
						$sql="SELECT 1=1 FROM matieres_notes WHERE id_groupe='".$groups[$i]['id']."' AND periode='".$groups[$i]['periodes'][$j]['num_periode']."';";
						// AND statut='' ?
						$test=mysqli_query($GLOBALS["mysqli"], $sql);
						$nb_notes_bulletin=mysqli_num_rows($test);
	
						$sql="SELECT 1=1 FROM matieres_appreciations WHERE id_groupe='".$groups[$i]['id']."' AND periode='".$groups[$i]['periodes'][$j]['num_periode']."';";
						// AND statut='' ?
						$test=mysqli_query($GLOBALS["mysqli"], $sql);
						$nb_app_bulletin=mysqli_num_rows($test);
	
						$effectif_groupe=count($groups[$i]["eleves"][$groups[$i]['periodes'][$j]['num_periode']]["users"]);
	
	
						// Note sur le bulletin:
						if($class_style!="deverrouille") {
							if(acces_exceptionnel_saisie_bull_note_groupe_periode($groups[$i]['id'], $j)) {
								echo "<td style='background-color:orange;' title='Accès exceptionnellement ouvert'>\n";
								$image="bulletin_saisie.png";
							}
							else {
								echo "<td class='$class_style'>\n";
								$image="bulletin_visu.png";
							}
						}
						else {
							//echo "<td class='$class_style'>\n";
							echo "<td>\n";
								$image="bulletin_saisie.png";
						}
						if(!in_array($groups[$i]['id'],$invisibilite_groupe['bulletins'])) {
							echo "<div id='h_bn_".$i."_".$j."'>";
							echo "<a href='saisie/saisie_notes.php?id_groupe=".$groups[$i]['id']."&amp;periode_cn=".$groups[$i]['periodes'][$j]['num_periode']."'";
							if($pref_accueil_infobulles=="y"){
								echo " onmouseover=\"afficher_div('info_bn_".$i."_".$j."','y',10,10);\" onmouseout=\"cacher_div('info_bn_".$i."_".$j."');\"";
							}
							echo ">";
							echo "<img src='images/icons/$image' width='32' height='34' alt='Notes' border='0' />";
							echo "</a>";

							echo "<br />\n";
							echo "<span style='font-size: xx-small;'>";
							if($nb_notes_bulletin==$effectif_groupe){echo "<span class='saisies_effectuees'>";}else{echo "<span class='saisies_manquantes'>";}
							echo "($nb_notes_bulletin/$effectif_groupe)";
							echo "</span>";
							echo "</span>";
		
							if($pref_accueil_infobulles=="y"){
								echo "<div id='info_bn_".$i."_".$j."' class='infobulle_corps' style='border: 1px solid #000000; color: #000000; padding: 0px; position: absolute; width: 15em;' onmouseout=\"cacher_div('info_bn_".$i."_".$j."');\">Saisie des moyennes ".htmlspecialchars($groups[$i]['description'])." (<i>$liste_classes_du_groupe</i>)<br />".$groups[$i]["periodes"][$j]["nom_periode"].".</div>\n";
		
								$tab_liste_infobulles[]='info_bn_'.$i.'_'.$j;
							}
							echo "</div>\n";
						}
						else {echo "&nbsp;";}
						echo "</td>\n";
	
	
						// Appréciation sur le bulletin:
						if($class_style!="deverrouille") {
							if(acces_exceptionnel_saisie_bull_app_groupe_periode($groups[$i]['id'], $j)) {
								echo "<td style='background-color:orange;' title='Accès exceptionnellement ouvert'>\n";
								$image="bulletin_saisie.png";
							}
							else {
								echo "<td class='$class_style'>\n";
								$image="bulletin_visu.png";
							}
						}
						else {
							echo "<td>\n";
								$image="bulletin_saisie.png";
						}
						echo "<div id='h_ba_".$i."_".$j."'>";
						if(!in_array($groups[$i]['id'],$invisibilite_groupe['bulletins'])) {
							echo "<a href='saisie/saisie_appreciations.php?id_groupe=".$groups[$i]['id']."&amp;periode_cn=".$groups[$i]['periodes'][$j]['num_periode']."'";
							if($pref_accueil_infobulles=="y"){
								echo " onmouseover=\"afficher_div('info_ba_".$i."_".$j."','y',10,10);\" onmouseout=\"cacher_div('info_ba_".$i."_".$j."');\"";
							}
							echo ">";
							echo "<img src='images/icons/$image' width='32' height='34' alt='Appréciations' border='0' />";
							echo "</a>";
							echo "<br />\n";
		
							echo "<span style='font-size: xx-small;'>";
							if($nb_app_bulletin==$effectif_groupe){echo "<span class='saisies_effectuees'>";}else{echo "<span class='saisies_manquantes'>";}
							echo "($nb_app_bulletin/$effectif_groupe)";
							echo "</span>";
							echo "</span>";
		
		
							if($pref_accueil_infobulles=="y"){
								echo "<div id='info_ba_".$i."_".$j."' class='infobulle_corps' style='border: 1px solid #000000; color: #000000; padding: 0px; position: absolute; width: 15em;' onmouseout=\"cacher_div('info_ba_".$i."_".$j."');\">Saisie des appréciations ".htmlspecialchars($groups[$i]['description'])." (<i>$liste_classes_du_groupe</i>)<br />".$groups[$i]["periodes"][$j]["nom_periode"].".</div>\n";
		
								$tab_liste_infobulles[]='info_ba_'.$i.'_'.$j;
							}
							echo "</div>\n";
						}
						else {echo "&nbsp;";}
						echo "</td>\n";
					}
	
	
					if($pref_accueil_visu=="y"){
						// Graphe:
						if($class_style!="deverrouille") {
							echo "<td class='$class_style'>\n";
						}
						else {
							echo "<td>\n";
						}
						echo "<div id='h_g_".$i."_".$j."'>";
						$cpt=0;
						foreach($groups[$i]["classes"]["classes"] as $classe){
							if($cpt>0){echo "<br />\n";}
							echo "<a href='visualisation/affiche_eleve.php?id_classe=".$classe['id']."&amp;num_periode_choisie=$j'";
							if($pref_accueil_infobulles=="y"){
								echo " onmouseover=\"afficher_div('info_graphe_".$i."_".$j."_".$cpt."','y',10,10);\" onmouseout=\"cacher_div('info_graphe_".$i."_".$j."_".$cpt."');\"";
							}
							echo ">";
							echo "<img src='images/icons/graphes.png' width='32' height='32' alt='Graphe' border='0' />";
							if(count($groups[$i]["classes"]["classes"])>1){echo " ".$classe['classe'];}
							echo "</a>\n";
	
	
							if($pref_accueil_infobulles=="y"){
								echo "<div id='info_graphe_".$i."_".$j."_".$cpt."' class='infobulle_corps' style='border: 1px solid #000000; color: #000000; padding: 0px; position: absolute; width: 10em;' onmouseout=\"cacher_div('info_graphe_".$i."_".$j."_".$cpt."');\">Outil graphique<br />".$classe['classe']."<br />".$groups[$i]["periodes"][$j]["nom_periode"].".</div>\n";
	
								$tab_liste_infobulles[]='info_graphe_'.$i.'_'.$j.'_'.$cpt;
							}
							$cpt++;
						}
						echo "</div>\n";
						echo "</td>\n";


						// Bulletin simplifié:
						// https://127.0.0.1/steph/gepi-trunk/prepa_conseil/index3.php?id_classe=4
						// https://127.0.0.1/steph/gepi-trunk/prepa_conseil/edit_limite.php
						// <input type=\"radio\" name=\"choix_edit\" value=\"1\" checked />
						// <select onchange=\"change_periode()\" size=1 name=\"periode1\">
						// <select size=1 name=\"periode2\">
						// <input type=hidden name=id_classe value=$id_classe />
	
						if($test_acces_bull_simp[$j]=="y") {
							if($class_style!="deverrouille") {
								echo "<td class='$class_style'>\n";
							}
							else {
								echo "<td>\n";
							}
							echo "<div id='h_bs_".$i."_".$j."'>";
							$cpt=0;
							foreach($groups[$i]["classes"]["classes"] as $classe){
								if($cpt>0){echo "<br />\n";}
	
								$affiche_bull_simp_cette_classe="n";
	
								if ((getSettingValue("GepiAccesBulletinSimpleProf") == "yes")||(getSettingValue("GepiAccesBulletinSimpleProfTousEleves") == "yes")) {
									$affiche_bull_simp_cette_classe="y";
								}
								elseif(getSettingValue("GepiAccesBulletinSimplePP") == "yes") {
									$sql="SELECT 1=1 FROM j_eleves_groupes jeg,
																j_eleves_professeurs jep,
																j_eleves_classes jec
															WHERE jep.login=jeg.login AND
																	jec.login=jeg.login AND
																	jec.periode=jeg.periode AND
																	jeg.periode='$j' AND
																	jec.id_classe='".$classe['id']."' AND
																	jep.professeur='".$_SESSION['login']."';";
									$res_test_affiche_bull_simp_cette_classe=mysqli_num_rows(mysqli_query($GLOBALS["mysqli"], $sql));
									//echo "$sql";
									if($res_test_affiche_bull_simp_cette_classe>0) {$affiche_bull_simp_cette_classe="y";}
								}
	
								if($affiche_bull_simp_cette_classe=="y") {
									echo "<a href='prepa_conseil/index3.php?id_classe=".$classe['id']."&amp;couleur_alterne=y' onClick=\"valide_bull_simpl('".$classe['id']."','".$j."'); return false;\"";
	
									if($pref_accueil_infobulles=="y"){
										echo " onmouseover=\"afficher_div('info_bs_".$i."_".$j."_".$cpt."','y',10,10);\" onmouseout=\"cacher_div('info_bs_".$i."_".$j."_".$cpt."');\"";
									}
									echo ">";
									echo "<img src='images/icons/bulletin_simp.png' width='34' height='34' alt='Bulletin simplifié' border='0' />";
									if(count($groups[$i]["classes"]["classes"])>1){echo " ".$classe['classe'];}
									echo "</a>\n";
	
									if($pref_accueil_infobulles=="y"){
										echo "<div id='info_bs_".$i."_".$j."_".$cpt."' class='infobulle_corps' style='border: 1px solid #000000; color: #000000; padding: 0px; position: absolute; width: 10em;' onmouseout=\"cacher_div('info_bs_".$i."_".$j."_".$cpt."');\">Bulletins simplifiés<br />".$classe['classe']."<br />".$groups[$i]["periodes"][$j]["nom_periode"].".</div>\n";
	
										$tab_liste_infobulles[]='info_bs_'.$i.'_'.$j.'_'.$cpt;
									}

									$cpt++;
								}
	
							}

							foreach($groups[$i]['classes']['classes'] as $tmp_id_classe => $tmp_classe) {
								if((isset($tab_conseils_de_classes[$tmp_id_classe]["date_debut"]))&&($tab_conseils_de_classes[$tmp_id_classe]["date_debut"]<=$date_courante_debut_journee_mysql)&&(isset($tab_conseils_de_classes[$tmp_id_classe]["statuts"]))&&(in_array("professeur", $tab_conseils_de_classes[$tmp_id_classe]["statuts"]))) {
									echo "<br /><span style='font-size:x-small;' title=\"Date du conseil de classe de ".$tmp_classe["classe"]." : ".$tab_conseils_de_classes[$tmp_id_classe]["slashdate_heure_ev"]."\">".$tab_conseils_de_classes[$tmp_id_classe]["slashdate_ev"]."</span>";
								}
							}

							echo "</div>\n";
							echo "</td>\n";
						}
					}
	
	
					if($pref_accueil_liste_pdf=="y"){
						if($class_style!="deverrouille") {
							echo "<td class='$class_style'>\n";
						}
						else {
							echo "<td>\n";
						}
						echo "<div id='h_listes_".$i."_".$j."'>";
						echo "<a href='impression/liste_pdf.php' onClick=\"valide_liste_pdf('".$groups[$i]['id']."','".$groups[$i]['periodes'][$j]['num_periode']."'); return false;\" target='_blank'";
						if($pref_accueil_infobulles=="y"){
							echo " onmouseover=\"afficher_div('info_liste_pdf_".$i."_".$j."','y',10,10);\" onmouseout=\"cacher_div('info_liste_pdf_".$i."_".$j."');\"";
						}
						echo ">";
						//echo "<img src='images/icons/bulletin_simp.png' width='34' height='34' alt='Listes PDF' border='0' />";
						echo "<img src='images/icons/pdf32.png' width='32' height='32' alt='PDF' />";
						echo "</a>";
	
						if($pref_accueil_infobulles=="y"){
							echo "<div id='info_liste_pdf_".$i."_".$j."' class='infobulle_corps' style='border: 1px solid #000000; color: #000000; padding: 0px; position: absolute; width: 12em;' onmouseout=\"cacher_div('info_liste_pdf_".$i."_".$j."');\">Listes PDF des élèves<br />".$classe['classe']."<br />".$groups[$i]["periodes"][$j]["nom_periode"].".</div>\n";
							$tab_liste_infobulles[]='info_liste_pdf_'.$i.'_'.$j;
						}
						echo "</div>\n";

						// CSV
						echo "<div id='h_listes_csv_".$i."_".$j."'>";
						echo "<a href='groupes/get_csv.php?id_groupe=".$groups[$i]['id']."&periode_num=".$groups[$i]['periodes'][$j]['num_periode']."' target='_blank'";
						// onClick=\"valide_liste_pdf('".$groups[$i]['id']."','".$groups[$i]['periodes'][$j]['num_periode']."'); return false;\"
						if($pref_accueil_infobulles=="y"){
							echo " onmouseover=\"afficher_div('info_liste_csv_".$i."_".$j."','y',10,10);\" onmouseout=\"cacher_div('info_liste_csv_".$i."_".$j."');\"";
						}
						echo ">";
						//echo "<img src='images/icons/bulletin_simp.png' width='34' height='34' alt='Listes PDF' border='0' />";
						echo "<img src='images/notes_app_csv.png' width='30' height='30' alt='CSV' />";
						echo "</a>";
	
						if($pref_accueil_infobulles=="y"){
							echo "<div id='info_liste_csv_".$i."_".$j."' class='infobulle_corps' style='border: 1px solid #000000; color: #000000; padding: 0px; position: absolute; width: 12em;' onmouseout=\"cacher_div('info_liste_csv_".$i."_".$j."');\">Listes CSV des élèves<br />".$classe['classe']."<br />".$groups[$i]["periodes"][$j]["nom_periode"].".</div>\n";
							$tab_liste_infobulles[]='info_liste_csv_'.$i.'_'.$j;
						}
						echo "</div>\n";

						echo "</td>\n";
					}
			}

			// On complète les colonnes à laisser vides si jamais, par exemple, on traite une ligne à deux périodes alors que d'autres groupes ont trois périodes donc trois colonnes.
			for($k=$j;$k<=$maxper;$k++){
				for($n=0;$n<$colspan;$n++){
					echo "<td>-</td>\n";
				}
			}
		}
	}

	if($afficher_col_notanet=="y") {
		if((isset($tab_groupes_notanet[$groups[$i]['id']]['verrouillage']))||(isset($tab_groupes_notanet_saisie_note[$groups[$i]['id']]['verrouillage']))) {

			//$sql="SELECT DISTINCT login FROM notanet_saisie ns, j_eleves_groupes jeg WHERE jeg.login=ns.login AND jeg.id_groupe='".$groups[$i]['id']."';";
			$sql_notes="SELECT DISTINCT ns.login FROM notanet_saisie ns, j_eleves_groupes jeg WHERE jeg.login=ns.login AND ns.note!='' AND jeg.id_groupe='".$groups[$i]['id']."';";
			$nb_note=mysqli_num_rows(mysqli_query($GLOBALS["mysqli"], $sql_notes));

			$sql_app="SELECT DISTINCT na.login FROM notanet_app na, j_eleves_groupes jeg WHERE jeg.login=na.login AND na.appreciation!='' AND jeg.id_groupe='".$groups[$i]['id']."';";
			$nb_app=mysqli_num_rows(mysqli_query($GLOBALS["mysqli"], $sql_app));

			$sql_ele="SELECT * FROM j_eleves_groupes WHERE id_groupe='".$groups[$i]['id']."' and periode=(SELECT max(periode) FROM j_eleves_groupes WHERE id_groupe='".$groups[$i]['id']."');";
			$nb_ele=mysqli_num_rows(mysqli_query($GLOBALS["mysqli"], $sql_ele));
			if($nb_note==$nb_ele) {
				$chaine_remplissage_note="<br /><span style='font-size:x-small'>($nb_note/$nb_ele)</span>";
			}
			else {
				$chaine_remplissage_note="<br /><span style='color:red;font-size:x-small'>($nb_note/$nb_ele)</span>";
			}
			if($nb_app==$nb_ele) {
				$chaine_remplissage_app="<br /><span style='font-size:x-small'>$nb_app/$nb_ele</span>";
			}
			else {
				$chaine_remplissage_app="<br /><span style='color:red;font-size:x-small'>$nb_app/$nb_ele</span>";
			}

			if(((isset($tab_groupes_notanet[$groups[$i]['id']]['verrouillage']))&&($tab_groupes_notanet[$groups[$i]['id']]['verrouillage']=="N"))||((isset($tab_groupes_notanet_saisie_note[$groups[$i]['id']]['verrouillage']))&&($tab_groupes_notanet_saisie_note[$groups[$i]['id']]['verrouillage']=="N"))) {
				echo "<td class='deverrouille'>";

				if(isset($tab_groupes_notanet[$groups[$i]['id']]['verrouillage'])) {
					if($tab_groupes_notanet[$groups[$i]['id']]['verrouillage']=="N") {
						echo "<div style='float:left;width:34px;'><a href='./mod_notanet/saisie_app.php?id_groupe=".$groups[$i]['id']."'><img src='./images/icons/bulletin_app_saisie.png' width='34' height='34' title=\"Saisir les appréciations pour les Fiches Brevet\" /></a>".$chaine_remplissage_app."</div>";
					}
					else {
						echo "<div style='float:left;width:34px;'><a href='./mod_notanet/saisie_app.php?id_groupe=".$groups[$i]['id']."'><img src='./images/icons/bulletin_app_visu.png' width='34' height='34' title=\"Consulter vos appréciations pour les Fiches Brevet\" /></a>\n".$chaine_remplissage_app."</div>";
					}
				}

				if(isset($tab_groupes_notanet_saisie_note[$groups[$i]['id']]['verrouillage'])) {
					if($tab_groupes_notanet_saisie_note[$groups[$i]['id']]['verrouillage']=="N") {
						echo "<div style='float:left;width:34px;'> <a href='./mod_notanet/saisie_notes.php?id_groupe=".$groups[$i]['id']."'><img src='./images/icons/bulletin_note_saisie.png' width='34' height='34' title=\"Saisir les notes pour les Notanet et les Fiches Brevet\" /></a>".$chaine_remplissage_note."</div>";
					}
					else {
						echo "<div style='float:left;width:34px;'> <a href='./mod_notanet/saisie_notes.php?id_groupe=".$groups[$i]['id']."'><img src='./images/icons/bulletin_note_visu.png' width='34' height='34' title=\"Consulter vos notes pour Notanet et les Fiches Brevet\" /></a>\n".$chaine_remplissage_note."</div>";
					}
				}

				echo "</td>\n";
			}
			else {
				echo "<td class='verrouillagepart'>";
				//echo "<a href='./mod_notanet/saisie_app.php?id_groupe=".$groups[$i]['id']."'><img src='./images/icons/chercher.png' width='34' height='34' title=\"Consulter vos appréciations pour les Fiches Brevet\" /></a>";

				if(isset($tab_groupes_notanet[$groups[$i]['id']]['verrouillage'])) {
					echo "<a href='./mod_notanet/saisie_app.php?id_groupe=".$groups[$i]['id']."'><img src='./images/icons/bulletin_app_visu.png' width='34' height='34' title=\"Consulter vos appréciations pour les Fiches Brevet\" /></a>\n".$chaine_remplissage_app;
				}

				if(isset($tab_groupes_notanet_saisie_note[$groups[$i]['id']]['verrouillage'])) {
					echo " <a href='./mod_notanet/saisie_notes.php?id_groupe=".$groups[$i]['id']."'><img src='./images/icons/bulletin_note_visu.png' width='34' height='34' title=\"Consulter vos notes pour Notanet et les Fiches Brevet\" /></a>\n".$chaine_remplissage_note;
				}

				echo "</td>\n";
			}
		}
		else {
			echo "<td>&nbsp;</td>\n";
		}
	}

	echo "</tr>\n";
	flush();
	/*
	echo "<p>\n";
	echo "\$groups[$i]['id']=".$groups[$i]['id']."<br />\n";
	echo "\$groups[$i]['name']=".$groups[$i]['name']."<br />\n";
	echo "\$groups[$i]['description']=".$groups[$i]['description']."<br />\n";
	echo "\$groups[$i]['matiere']['nom_complet']=".$groups[$i]['matiere']['nom_complet']."<br />\n";
	echo "\$groups[$i]['classlist_string']=".$groups[$i]['classlist_string']."<br />\n";
	echo "</p>\n";
	*/
}

//==================================================================
// AID
$ii=$i;

$sql="SELECT DISTINCT ac.* FROM aid_config ac, aid a
		WHERE ac.indice_aid=a.indice_aid 
		ORDER BY ac.order_display1, ac.order_display2, a.numero, ac.nom;";
//echo "$sql<br />";
$res_aid=mysqli_query($GLOBALS["mysqli"], $sql);
$i=0;
$tmp_nb_aid_a_afficher=0;
$nb_aid=0;
while ($lig_cat_aid=mysqli_fetch_object($res_aid)) {
	$tmp_indice_aid = $lig_cat_aid->indice_aid;
	$tmp_aid_display_begin = $lig_cat_aid->display_begin;
	$tmp_aid_display_end = $lig_cat_aid->display_end;
	$tmp_aid_display_bulletin = $lig_cat_aid->display_bulletin;
	$tmp_aid_bull_simplifie = $lig_cat_aid->bull_simplifie;
	$tmp_aid_type_note = $lig_cat_aid->type_note;
	$tmp_aid_outils_complementaires = $lig_cat_aid->outils_complementaires;
	$tmp_nom_aid = $lig_cat_aid->nom;

	/*
	echo "<pre>";
	print_r($lig_cat_aid);
	echo "</pre>";
	*/

	$sql="SELECT * FROM j_aid_utilisateurs
		WHERE (id_utilisateur = '".$_SESSION['login']."'
		AND indice_aid = '".$tmp_indice_aid."')";
	//echo "$sql<br />";
	$tmp_call_prof = mysqli_query($GLOBALS["mysqli"], $sql);
	$tmp_nb_result = mysqli_num_rows($tmp_call_prof);
	if (($tmp_nb_result != 0) or ($_SESSION['statut'] == 'secours')) {
		//$tmp_nom_aid = @old_mysql_result($tmp_call_data, $i, "nom");

		$sql="SELECT a.nom, a.id, a.numero FROM j_aid_utilisateurs j, aid a WHERE (j.id_utilisateur = '" . $_SESSION['login'] . "' and a.id = j.id_aid and a.indice_aid=j.indice_aid and j.indice_aid='$tmp_indice_aid') ORDER BY a.numero, a.nom;";
		//echo "$sql<br />";
		$tmp_call_prof_aid = mysqli_query($GLOBALS["mysqli"], $sql);
		$tmp_nombre_aid = mysqli_num_rows($tmp_call_prof_aid);
		//if ($tmp_nombre_aid>0) {
		while($lig_aid=mysqli_fetch_object($tmp_call_prof_aid)) {
			$tab_clas_aid=array();
			$cpt_clas_aid=0;
			$liste_classes_aid="";
			$sql="SELECT DISTINCT c.id, c.classe, c.nom_complet FROM j_aid_eleves jae, j_eleves_classes jec, classes c
					WHERE jae.login=jec.login AND
							jec.id_classe=c.id AND
							jae.id_aid='$lig_aid->id' AND
							jae.indice_aid='$tmp_indice_aid'
					ORDER BY c.classe, c.nom_complet;";
			//echo "$sql<br />";
			$res_clas_aid=mysqli_query($GLOBALS["mysqli"], $sql);
			$tmp_aid_max_per=0;
			while($lig_clas_aid=mysqli_fetch_object($res_clas_aid)) {
				$tab_clas_aid[$cpt_clas_aid]['id']=$lig_clas_aid->id;
				$tab_clas_aid[$cpt_clas_aid]['classe']=$lig_clas_aid->classe;
				$tab_clas_aid[$cpt_clas_aid]['nom_complet']=$lig_clas_aid->nom_complet;

				$sql="SELECT num_periode FROM periodes WHERE id_classe='$lig_clas_aid->id' ORDER BY num_periode DESC LIMIT 1;";
				$tmp_res_per_clas=mysqli_query($GLOBALS["mysqli"], $sql);
				if(mysqli_num_rows($tmp_res_per_clas)>0) {
					$lig_tmp_per_clas=mysqli_fetch_object($tmp_res_per_clas);
					if($lig_tmp_per_clas->num_periode>$tmp_aid_max_per) {$tmp_aid_max_per=$lig_tmp_per_clas->num_periode;}
				}
				$cpt_clas_aid++;
			}

			echo "<tr valign='top'>\n";
			echo "<!-- Colonne Nom de l'AID -->\n";
			echo "<td>";

// Si Gestionnaire, ou accès modif... ajout <div style='float:right'><a href=''></a></div>
			if(NiveauGestionAid($_SESSION['login'],$tmp_indice_aid,$lig_aid->id)>=5) {
				if ($tmp_aid_outils_complementaires=="y") {
					echo "<div style='float:right;width:16px;'><a href='./aid/modif_fiches.php?action=modif&aid_id=".$lig_aid->id."&indice_aid=".$tmp_indice_aid."' title='Éditer cet AID' onclick=\"return confirm_abandon (this, change, '$themessage')\"><img src='./images/edit16.png' class='icone16' alt='Éditer cet AID' /></a></div>";
				}
				else {
					echo "<div style='float:right;width:16px;'><a href='./aid/add_aid.php?action=modif_aid&aid_id=".$lig_aid->id."&indice_aid=".$tmp_indice_aid."' title='Éditer cet AID' onclick=\"return confirm_abandon (this, change, '$themessage')\"><img src='./images/edit16.png' class='icone16' alt='Éditer cet AID' /></a></div>";
				}
			}
			elseif(NiveauGestionAid($_SESSION['login'],$tmp_indice_aid,$lig_aid->id)>=1) {
				echo "<div style='float:right;width:16px;'><a href='./aid/modify_aid.php?flag=eleve&aid_id=".$lig_aid->id."&indice_aid=".$tmp_indice_aid."' title='Gérer la liste des élèves' onclick=\"return confirm_abandon (this, change, '$themessage')\"><img src='./images/edit16.png' class='icone16' alt='Éditer cet AID' /></a></div>";
			}

			echo $tmp_nom_aid;
			echo "<br />";
			echo "<span style='font-size:x-small'>".$lig_aid->nom."<span>";
			echo "</td>\n";

			//echo "<td>".htmlspecialchars($groups[$i]['classlist_string'])."</td>\n";
			echo "<!-- Colonne nom classe menant à la liste de élèves du 'groupe'... non réalisé pour les AID -->\n";
			echo "<td>\n";

			/*
			$tmp_sous_menu2[$cpt_sous_menu2]['lien']="/aid/popup.php?id_aid=".$lig_aid->id_aid;
			$tmp_sous_menu2[$cpt_sous_menu2]['texte']="Liste élèves";
			$tmp_sous_menu2[$cpt_sous_menu2]['target']="_blank";
			$tmp_sous_menu2[$cpt_sous_menu2]['js']=" onclick=\"ouvre_popup_visu_aid('".$lig_aid->id_aid."','".$lig_aid->display_end."');return false;\"";
			$cpt_sous_menu2++;
			*/
			//echo "<a href='$gepiPath/aid/popup.php?id_aid=".$lig_aid->id."' target='_blank' onclick=\"ouvre_popup_visu_aid('".$lig_aid->id."','".$lig_cat_aid->display_end."');return false;\">";
			echo "<a href='$gepiPath/aid/popup.php?id_aid=".$lig_aid->id."' target='_blank' onclick=\"ouvre_popup_visu_aid('".$lig_aid->id."','');return false;\">";
			for($loop=0;$loop<count($tab_clas_aid);$loop++) {
				if($loop>0) {
					echo ", ";
					$liste_classes_aid.=", ";
				}
				echo "<span title=\"".$tab_clas_aid[$loop]['nom_complet']."\">".$tab_clas_aid[$loop]['classe']."</span>";
				$liste_classes_aid.=$tab_clas_aid[$loop]['classe'];
			}
			echo "</a>";
			echo "</td>\n";

			// mod_abs2
			if ((getSettingValue("active_module_absence_professeur")=='y')&&(getSettingValue("active_module_absence")=='2')) {
				echo "<!-- Colonne absences -->\n";
				echo "<td>";
				echo "<a href='mod_abs2/index.php?type_selection=id_aid&amp;id_aid=".$lig_aid->id."'";
				if($pref_accueil_infobulles=="y"){
					echo " onmouseover=\"afficher_div('info_abs_$ii','y',10,10);\" onmouseout=\"cacher_div('info_abs_$ii');\"";
				}
				echo ">";
					echo "<img src='images/icons/absences.png' width='32' height='32' alt='Absences' border='0' />";
				echo "</a>";

				if($pref_accueil_infobulles=="y"){
					echo "<div id='info_abs_$ii' class='infobulle_corps' style='border: 1px solid #000000; color: #000000; padding: 0px; position: absolute; width: 18em;' onmouseout=\"cacher_div('info_abs_$ii');\">Absences de ".$tmp_nom_aid." (<i>$liste_classes_aid</i>).</div>\n";

					$tab_liste_infobulles[]='info_abs_'.$ii;
				}
				echo "</td>\n";
			}

			if($pref_accueil_ct=="y") {
				// https://127.0.0.1/steph/gepi-trunk/cahier_texte/index.php?id_groupe=29&year=2007&month=6&day=30&edit_devoir=
				// Cahier de textes:
				echo "<!-- Colonne CDT -->\n";
				echo "<td>";
				echo "</td>\n";
			}

			if($pref_accueil_trombino=="y") {
				echo "<!-- Colonne Trombino -->\n";
				echo "<td>";
				if($tmp_aid_outils_complementaires=="y") {
					echo "<a href='mod_trombinoscopes/trombinoscopes.php?aid=".$lig_aid->id."&etape=2'";
					//" onClick=\"valide_trombino_aid('".$lig_aid->id."'); return false;\"";

					if($pref_accueil_infobulles=="y"){
						echo " onmouseover=\"afficher_div('info_trombino_$ii','y',10,10);\" onmouseout=\"cacher_div('info_trombino_$ii');\"";
					}
					echo ">";
					echo "<img src='images/icons/trombino.png' width='32' height='32' alt='Trombinoscope' border='0' />";
					echo "</a>\n";

					if($pref_accueil_infobulles=="y"){
						echo "<div id='info_trombino_$ii' class='infobulle_corps' style='border: 1px solid #000000; color: #000000; padding: 0px; position: absolute; width: 18em;' onmouseout=\"cacher_div('info_trombino_$ii');\">Trombinoscope de ".$tmp_nom_aid." (<i>$liste_classes_aid</i>).</div>\n";

						$tab_liste_infobulles[]='info_trombino_'.$ii;
					}

				}
				echo "</td>\n";
			}


			if(($pref_accueil_cn=="y")||
			($pref_accueil_bull=="y")||
			($pref_accueil_visu=="y")||
			($pref_accueil_liste_pdf=="y")) {
				if($colspan>0){
					for($j=1;$j<=$tmp_aid_max_per;$j++){
						if(($j>=$tmp_aid_display_begin)&&($j<=$tmp_aid_display_end)) {
							$afficher_aid="y";
						}
						else {
							$afficher_aid="n";
						}
/*
echo "Periode $j<br />
\$tmp_aid_display_begin=$tmp_aid_display_begin<br />
\$tmp_aid_display_end=$tmp_aid_display_end<br />
\$afficher_aid=$afficher_aid<br />";
*/
							$class_style="";

							$nb_verrtot=0;
							$nb_verrpart=0;
							$nb_non_close=0;
							for($loop=0;$loop<count($tab_clas_aid);$loop++) {
								$sql="SELECT * FROM periodes WHERE num_periode='$j' AND id_classe='".$tab_clas_aid[$loop]['id']."';";
								//echo "$sql<br />";
								$res_ver=mysqli_query($GLOBALS["mysqli"], $sql);
								if(mysqli_num_rows($res_ver)>0) {
									$lig_ver=mysqli_fetch_object($res_ver);
									if($lig_ver->verouiller=='P') {$nb_verrpart++;}
									if($lig_ver->verouiller=='O') {$nb_verrtot++;}
									if($lig_ver->verouiller=='N') {$nb_non_close++;}
								}
								/*
								echo "\$nb_verrtot=$nb_verrtot<br />
								\$nb_verrpart=$nb_verrpart<br />
								\$nb_non_close=$nb_non_close<br />";
								*/
							}

							if($nb_verrtot==count($tab_clas_aid)) {
								$class_style="verrouillagetot";
							}
							elseif($nb_verrtot==count($tab_clas_aid)) {
								$class_style="verrouillagepart";
							}
							elseif($nb_non_close>0) {
								$class_style="deverrouille";
							}

							echo "<!-- Colonne CN -->\n";
							echo "<td class='$class_style'></td>\n";

							if($pref_accueil_bull=="y"){
								// Calcul du nombre de notes et du nombre d'appréciations présentes sur le bulletin
								$sql="SELECT 1=1 FROM aid_appreciations WHERE id_aid='$lig_aid->id' AND indice_aid='$tmp_indice_aid' AND statut!='other' AND periode='$j';";
								// AND statut='' ?
								$test=mysqli_query($GLOBALS["mysqli"], $sql);
								$nb_notes_bulletin=mysqli_num_rows($test);
	
								$sql="SELECT 1=1 FROM aid_appreciations WHERE id_aid='$lig_aid->id' AND indice_aid='$tmp_indice_aid' AND appreciation!='' AND periode='$j';";
								// AND statut='' ?
								$test=mysqli_query($GLOBALS["mysqli"], $sql);
								$nb_app_bulletin=mysqli_num_rows($test);
	
								$sql="SELECT 1=1 FROM j_aid_eleves WHERE id_aid='$lig_aid->id' AND indice_aid='$tmp_indice_aid';";
								// AND statut='' ?
								$test=mysqli_query($GLOBALS["mysqli"], $sql);
								$effectif_aid=mysqli_num_rows($test);

								// Note sur le bulletin:
								echo "<!-- Colonne Note Bulletin -->\n";
								echo "<td class='$class_style'>\n";
								if($afficher_aid=="y") {
									if(($tmp_aid_type_note=='every')||
									(($j==$tmp_aid_display_end)&&($tmp_aid_type_note=='last'))) {
										echo "<div id='h_bn_".$ii."_".$j."'>";
										//if($class_style=="deverrouille") {
											echo "<a href='saisie/saisie_aid.php?indice_aid=".$tmp_indice_aid."&amp;aid_id=".$lig_aid->id."'";
										/*
										}
										else {
											echo "<a href='prepa_conseil/visu_aid.php?indice_aid=".$tmp_indice_aid."&amp;aid_id=".$lig_aid->id."'";
										}
										*/
										if($pref_accueil_infobulles=="y"){
											echo " onmouseover=\"afficher_div('info_bn_".$ii."_".$j."','y',10,10);\" onmouseout=\"cacher_div('info_bn_".$ii."_".$j."');\"";
										}
										echo ">";
										echo "<img src='images/icons/bulletin.png' width='32' height='34' alt='Notes' border='0' />";
										echo "</a>";

										echo "<br />\n";
										echo "<span style='font-size: xx-small;'>";
										if($nb_notes_bulletin==$effectif_aid){echo "<span class='saisies_effectuees'>";}else{echo "<span class='saisies_manquantes'>";}
										echo "($nb_notes_bulletin/$effectif_aid)";
										echo "</span>";
										echo "</span>";
		
										if($pref_accueil_infobulles=="y"){
											echo "<div id='info_bn_".$ii."_".$j."' class='infobulle_corps' style='border: 1px solid #000000; color: #000000; padding: 0px; position: absolute; width: 15em;' onmouseout=\"cacher_div('info_bn_".$ii."_".$j."');\">Saisie des moyennes AID ".$tmp_nom_aid." (<i>$liste_classes_aid</i>).</div>\n";
		
											$tab_liste_infobulles[]='info_bn_'.$ii.'_'.$j;
										}
										echo "</div>\n";
									}
								}
								echo "</td>\n";
	
	
								// Appréciation sur le bulletin:
								echo "<!-- Colonne Appréciation Bulletin -->\n";
								echo "<td class='$class_style'>\n";
								if($afficher_aid=="y") {
									echo "<div id='h_ba_".$ii."_".$j."'>";
									echo "<a href='saisie/saisie_aid.php?indice_aid=".$tmp_indice_aid."&amp;aid_id=".$lig_aid->id."'";
									if($pref_accueil_infobulles=="y"){
										echo " onmouseover=\"afficher_div('info_ba_".$ii."_".$j."','y',10,10);\" onmouseout=\"cacher_div('info_ba_".$ii."_".$j."');\"";
									}
									echo ">";
									echo "<img src='images/icons/bulletin.png' width='32' height='34' alt='Appréciations' border='0' />";
									echo "</a>";
									echo "<br />\n";
		
									echo "<span style='font-size: xx-small;'>";
									if($nb_app_bulletin==$effectif_aid){echo "<span class='saisies_effectuees'>";}else{echo "<span class='saisies_manquantes'>";}
									echo "($nb_app_bulletin/$effectif_aid)";
									echo "</span>";
									echo "</span>";
		
		
									if($pref_accueil_infobulles=="y"){
										echo "<div id='info_ba_".$ii."_".$j."' class='infobulle_corps' style='border: 1px solid #000000; color: #000000; padding: 0px; position: absolute; width: 15em;' onmouseout=\"cacher_div('info_ba_".$ii."_".$j."');\">Saisie des appréciations AID ".$tmp_nom_aid." (<i>$liste_classes_aid</i>).</div>\n";
		
										$tab_liste_infobulles[]='info_ba_'.$ii.'_'.$j;
									}
									echo "</div>\n";
								}
								echo "</td>\n";
							}
	
	
							if($pref_accueil_visu=="y"){
								// Graphe:
								echo "<!-- Colonne Graphe -->\n";
								echo "<td class='$class_style'>\n";
								if($afficher_aid=="y") {
									if(($tmp_aid_type_note=='every')||
									(($j==$tmp_aid_display_end)&&($tmp_aid_type_note=='last'))) {
										echo "<div id='h_g_".$ii."_".$j."'>";
										$cpt=0;
										for($loop=0;$loop<count($tab_clas_aid);$loop++) {
											if($cpt>0){echo "<br />\n";}
											echo "<a href='visualisation/affiche_eleve.php?id_classe=".$tab_clas_aid[$loop]['id']."'";
											if($pref_accueil_infobulles=="y"){
												echo " onmouseover=\"afficher_div('info_graphe_".$ii."_".$j."_".$cpt."','y',10,10);\" onmouseout=\"cacher_div('info_graphe_".$ii."_".$j."_".$cpt."');\"";
											}
											echo ">";
											echo "<img src='images/icons/graphes.png' width='32' height='32' alt='Graphe' border='0' />";
											if(count($tab_clas_aid)>1){echo " ".$tab_clas_aid[$loop]['classe'];}
											echo "</a>\n";
	
	
											if($pref_accueil_infobulles=="y"){
												echo "<div id='info_graphe_".$ii."_".$j."_".$cpt."' class='infobulle_corps' style='border: 1px solid #000000; color: #000000; padding: 0px; position: absolute; width: 10em;' onmouseout=\"cacher_div('info_graphe_".$ii."_".$j."_".$cpt."');\">Outil graphique<br />".$tab_clas_aid[$loop]['classe'].".</div>\n";
	
												$tab_liste_infobulles[]='info_graphe_'.$ii.'_'.$j.'_'.$cpt;
											}
											$cpt++;
										}
										echo "</div>\n";
									}
								}
								echo "</td>\n";


								// Bulletin simplifié:
	
								if($test_acces_bull_simp[$j]=="y") {
									echo "<!-- Colonne Bulletin simplifié -->\n";
									echo "<td class='$class_style'>\n";
									if(($afficher_aid=="y")&&($tmp_aid_bull_simplifie=="y")) {

										echo "<div id='h_bs_".$ii."_".$j."'>";
										$cpt=0;
										for($loop=0;$loop<count($tab_clas_aid);$loop++) {
											if($cpt>0){echo "<br />\n";}
	
											$affiche_bull_simp_cette_classe="n";
	
											if ((getSettingValue("GepiAccesBulletinSimpleProf") == "yes")||(getSettingValue("GepiAccesBulletinSimpleProfTousEleves") == "yes")) {
												$affiche_bull_simp_cette_classe="y";
											}
											elseif(getSettingValue("GepiAccesBulletinSimplePP") == "yes") {
												$sql="SELECT 1=1 FROM j_eleves_groupes jeg,
																			j_eleves_professeurs jep,
																			j_eleves_classes jec
																		WHERE jep.login=jeg.login AND
																				jec.login=jeg.login AND
																				jec.periode=jeg.periode AND
																				jeg.periode='$j' AND
																				jec.id_classe='".$tab_clas_aid[$loop]['id']."' AND
																				jep.professeur='".$_SESSION['login']."';";
												$res_test_affiche_bull_simp_cette_classe=mysqli_num_rows(mysqli_query($GLOBALS["mysqli"], $sql));
												//echo "$sql";
												if($res_test_affiche_bull_simp_cette_classe>0) {$affiche_bull_simp_cette_classe="y";}
											}
	
											if($affiche_bull_simp_cette_classe=="y") {
												echo "<a href='prepa_conseil/index3.php?id_classe=".$tab_clas_aid[$loop]['id']."&amp;couleur_alterne=y' onClick=\"valide_bull_simpl('".$classe['id']."','".$j."'); return false;\"";
	
												if($pref_accueil_infobulles=="y"){
													echo " onmouseover=\"afficher_div('info_bs_".$ii."_".$j."_".$cpt."','y',10,10);\" onmouseout=\"cacher_div('info_bs_".$ii."_".$j."_".$cpt."');\"";
												}
												echo ">";
												echo "<img src='images/icons/bulletin_simp.png' width='34' height='34' alt='Bulletin simplifié' border='0' />";
												if(count($tab_clas_aid[$loop])>1){echo " ".$tab_clas_aid[$loop]['classe'];}
												echo "</a>\n";
	
												if($pref_accueil_infobulles=="y"){
													echo "<div id='info_bs_".$ii."_".$j."_".$cpt."' class='infobulle_corps' style='border: 1px solid #000000; color: #000000; padding: 0px; position: absolute; width: 10em;' onmouseout=\"cacher_div('info_bs_".$ii."_".$j."_".$cpt."');\">Bulletins simplifiés<br />".$tab_clas_aid[$loop]['classe'].".</div>\n";
	
													$tab_liste_infobulles[]='info_bs_'.$ii.'_'.$j.'_'.$cpt;
												}
												$cpt++;
											}
	
										}
										echo "</div>\n";
									}
									echo "</td>\n";
								}
							}
	
	
							if($pref_accueil_liste_pdf=="y"){
								echo "<!-- Colonne Liste PDF -->\n";
								echo "<td class='$class_style'>\n";
								if($afficher_aid=="y") {
									echo "<div id='h_listes_".$ii."_".$j."'>";
									echo "<a href='impression/liste_pdf.php?id_aid=".$lig_aid->id."&amp;periode_num=".$j."' target='_blank'><img src='images/icons/pdf32.png' width='32' height='32' alt='PDF' /></a>";
									echo "</div>";


									// 20170119
									// CSV
									echo "<div id='h_listes_csv_".$ii."_".$j."'>";
									echo "<a href='groupes/mes_listes.php#aid' target='_blank'";
									if($pref_accueil_infobulles=="y"){
										echo " onmouseover=\"afficher_div('info_liste_csv_".$ii."_".$j."','y',10,10);\" onmouseout=\"cacher_div('info_liste_csv_".$ii."_".$j."');\"";
									}
									echo ">";
									//echo "<img src='images/icons/bulletin_simp.png' width='34' height='34' alt='Listes PDF' border='0' />";
									echo "<img src='images/notes_app_csv.png' width='30' height='30' alt='CSV' />";
									echo "</a>";
	
									if($pref_accueil_infobulles=="y"){
										echo "<div id='info_liste_csv_".$ii."_".$j."' class='infobulle_corps' style='border: 1px solid #000000; color: #000000; padding: 0px; position: absolute; width: 12em;' onmouseout=\"cacher_div('info_liste_csv_".$ii."_".$j."');\">Listes CSV des élèves<br />".$tmp_nom_aid." (".$lig_aid->nom.")<br />Période $j.</div>\n";
										$tab_liste_infobulles[]='info_liste_csv_'.$ii.'_'.$j;
									}
									echo "</div>\n";


								}
								echo "</td>\n";
							}
					}

					// On complète les colonnes à laisser vides si jamais, par exemple, on traite une ligne à deux périodes alors que d'autres groupes ont trois périodes donc trois colonnes.
					for($k=$j;$k<=$maxper;$k++){
						for($n=0;$n<$colspan;$n++){
							echo "<td>-</td>\n";
						}
					}
				}
			}

			echo "</tr>\n";
			$ii++;
			$nb_aid++;
			flush();
		}
	}
	$i++;
}
//==================================================================

echo "</table>\n";



echo "<script type='text/javascript'>
	function valide_bull_simpl(id_classe,num_periode){
		document.getElementById('id_classe').value=id_classe;
		document.getElementById('periode1').value=num_periode;
		document.getElementById('periode2').value=num_periode;
		document.form_choix_edit.submit();
	}

	/*
	function valide_trombino(id_classe){
		document.getElementById('classe').value='c-'+id_classe;
		document.form_trombino.submit();
	}
	*/

	function valide_trombino(id_groupe){
		//document.getElementById('classe').value='g-'+id_groupe;
		document.getElementById('groupe').value=id_groupe;
		document.form_trombino.submit();
	}

	function valide_liste_pdf(id_groupe,num_periode){
		document.getElementById('id_groupes').value=id_groupe;
		document.getElementById('id_periode').value=num_periode;
		document.form_liste_pdf.submit();
	}



	function modif_col(num_periode,mode){
		if(mode=='affiche'){
			if(document.getElementById('h_lien_affiche_'+num_periode)){
				document.getElementById('h_lien_affiche_'+num_periode).style.display='none';
			}
			if(document.getElementById('h_lien_cache_'+num_periode)){
				document.getElementById('h_lien_cache_'+num_periode).style.display='';
			}

			if(document.getElementById('h_cn_'+num_periode)){
				document.getElementById('h_cn_'+num_periode).style.display='';
			}
			if(document.getElementById('h_b_'+num_periode)){
				document.getElementById('h_b_'+num_periode).style.display='';
			}
			if(document.getElementById('h_v_'+num_periode)){
				document.getElementById('h_v_'+num_periode).style.display='';
			}

			if(document.getElementById('h_bn_'+num_periode)){
				document.getElementById('h_bn_'+num_periode).style.display='';
			}
			if(document.getElementById('h_ba_'+num_periode)){
				document.getElementById('h_ba_'+num_periode).style.display='';
			}
			if(document.getElementById('h_g_'+num_periode)){
				document.getElementById('h_g_'+num_periode).style.display='';
			}
			if(document.getElementById('h_bs_'+num_periode)){
				document.getElementById('h_bs_'+num_periode).style.display='';
			}

			if(document.getElementById('h_liste_pdf_'+num_periode)){
				document.getElementById('h_liste_pdf_'+num_periode).style.display='';
			}

			// Pour afficher/cacher les lignes du tableau, évaluer count(\$groups)=\$nb_groupes
			for(i=0;i<=$nb_groupes+$nb_aid;i++){
				if(document.getElementById('h_cn_'+i+'_'+num_periode)){
					document.getElementById('h_cn_'+i+'_'+num_periode).style.display='';
				}
				if(document.getElementById('h_bn_'+i+'_'+num_periode)){
					document.getElementById('h_bn_'+i+'_'+num_periode).style.display='';
				}
				if(document.getElementById('h_ba_'+i+'_'+num_periode)){
					document.getElementById('h_ba_'+i+'_'+num_periode).style.display='';
				}
				if(document.getElementById('h_g_'+i+'_'+num_periode)){
					document.getElementById('h_g_'+i+'_'+num_periode).style.display='';
				}
				if(document.getElementById('h_bs_'+i+'_'+num_periode)){
					document.getElementById('h_bs_'+i+'_'+num_periode).style.display='';
				}
				if(document.getElementById('h_listes_'+i+'_'+num_periode)){
					document.getElementById('h_listes_'+i+'_'+num_periode).style.display='';
				}
				if(document.getElementById('h_listes_csv_'+i+'_'+num_periode)){
					document.getElementById('h_listes_csv_'+i+'_'+num_periode).style.display='';
				}
			}
		}
		else{
			if(document.getElementById('h_lien_affiche_'+num_periode)){
				document.getElementById('h_lien_affiche_'+num_periode).style.display='';
			}

			if(document.getElementById('h_lien_cache_'+num_periode)){
				document.getElementById('h_lien_cache_'+num_periode).style.display='none';
			}

			if(document.getElementById('h_cn_'+num_periode)){
				document.getElementById('h_cn_'+num_periode).style.display='none';
			}
			if(document.getElementById('h_b_'+num_periode)){
				document.getElementById('h_b_'+num_periode).style.display='none';
			}
			if(document.getElementById('h_v_'+num_periode)){
				document.getElementById('h_v_'+num_periode).style.display='none';
			}

			if(document.getElementById('h_bn_'+num_periode)){
				document.getElementById('h_bn_'+num_periode).style.display='none';
			}
			if(document.getElementById('h_ba_'+num_periode)){
				document.getElementById('h_ba_'+num_periode).style.display='none';
			}
			if(document.getElementById('h_g_'+num_periode)){
				document.getElementById('h_g_'+num_periode).style.display='none';
			}
			if(document.getElementById('h_bs_'+num_periode)){
				document.getElementById('h_bs_'+num_periode).style.display='none';
			}

			if(document.getElementById('h_liste_pdf_'+num_periode)){
				document.getElementById('h_liste_pdf_'+num_periode).style.display='none';
			}

			for(i=0;i<=$nb_groupes+$nb_aid;i++){
				if(document.getElementById('h_cn_'+i+'_'+num_periode)){
					document.getElementById('h_cn_'+i+'_'+num_periode).style.display='none';
				}
				if(document.getElementById('h_bn_'+i+'_'+num_periode)){
					document.getElementById('h_bn_'+i+'_'+num_periode).style.display='none';
				}
				if(document.getElementById('h_ba_'+i+'_'+num_periode)){
					document.getElementById('h_ba_'+i+'_'+num_periode).style.display='none';
				}
				if(document.getElementById('h_g_'+i+'_'+num_periode)){
					document.getElementById('h_g_'+i+'_'+num_periode).style.display='none';
				}
				if(document.getElementById('h_bs_'+i+'_'+num_periode)){
					document.getElementById('h_bs_'+i+'_'+num_periode).style.display='none';
				}
				if(document.getElementById('h_listes_'+i+'_'+num_periode)){
					document.getElementById('h_listes_'+i+'_'+num_periode).style.display='none';
				}
				if(document.getElementById('h_listes_csv_'+i+'_'+num_periode)){
					document.getElementById('h_listes_csv_'+i+'_'+num_periode).style.display='none';
				}
			}
		}
	}

	".ouvre_popup_visu_groupe_visu_aid("n")."

</script>\n";


// Formulaire validé via JavaScript pour afficher les bulletins simplifiés
//echo "<form enctype=\"multipart/form-data\" action=\"../prepa_conseil/edit_limite.php\" method=\"post\" name=\"form_choix_edit\" target=\"_blank\">";
echo "<form enctype=\"multipart/form-data\" action=\"prepa_conseil/edit_limite.php\" method=\"post\" name=\"form_choix_edit\" target=\"_blank\">\n";
echo "<input type=\"hidden\" name=\"choix_edit\" value=\"1\" />\n";
echo "<input type=\"hidden\" name=\"periode1\" id=\"periode1\" value='1' />\n";
echo "<input type=\"hidden\" name=\"periode2\" id=\"periode2\" value='1' />\n";
echo "<input type=\"hidden\" name=\"couleur_alterne\" value='y' />\n";
echo "<input type=\"hidden\" name=\"id_classe\" id=\"id_classe\" value='' />\n";
echo "</form>\n";


// Formulaire validé via JavaScript pour afficher le trombinoscope
//echo "<form enctype=\"multipart/form-data\" action=\"../mod_trombinoscopes/trombinoscopes.php\" method=\"post\" name=\"form_trombino\" target=\"_blank\">";
echo "<form enctype=\"multipart/form-data\" action=\"mod_trombinoscopes/trombinoscopes.php\" method=\"post\" name=\"form_trombino\" target=\"_blank\">\n";
echo "<input type=\"hidden\" name=\"etape\" value=\"2\" />\n";
//echo "<input type=\"hidden\" name=\"classe\" id=\"classe\" value='' />\n";
echo "<input type=\"hidden\" name=\"groupe\" id=\"groupe\" value='' />\n";
echo "</form>\n";

echo "<form enctype=\"multipart/form-data\" action=\"mod_trombinoscopes/trombinoscopes.php\" method=\"post\" name=\"form_trombino_aid\" target=\"_blank\">\n";
echo "<input type=\"hidden\" name=\"etape\" value=\"2\" />\n";
echo "<input type=\"hidden\" name=\"aid\" id=\"aid\" value='' />\n";
echo "</form>\n";


// Formulaire validé via JavaScript pour afficher le trombinoscope
//echo "<form enctype=\"multipart/form-data\" action=\"../impression/liste_pdf.php\" method=\"post\" name=\"form_liste_pdf\" target=\"_blank\">";
echo "<form enctype=\"multipart/form-data\" action=\"impression/liste_pdf.php\" method=\"post\" name=\"form_liste_pdf\" target=\"_blank\">\n";
echo "<input type=\"hidden\" name=\"id_periode\" id=\"id_periode\" value=\"\" />\n";
echo "<input type=\"hidden\" name=\"id_liste_groupes[]\" id=\"id_groupes\" value='' />\n";
echo "</form>\n";



echo "</center>\n";



echo "<script type='text/javascript'>
	temporisation_chargement='ok';
";

if($pref_accueil_infobulles=="y"){
	for($i=0;$i<count($tab_liste_infobulles);$i++){
		echo "cacher_div('".$tab_liste_infobulles[$i]."');\n";
	}
}
echo "</script>\n";



if(count($tab_num_periodes_ouvertes)>0){
	echo "<script type='text/javascript'>\n";
	for($i=1;$i<=$maxper;$i++){
		if($affiche_periode[$i]=="n"){
			echo "if(document.getElementById('h_lien_cache_'+$i)){
	document.getElementById('h_lien_cache_'+$i).style.display='none';
}

if(document.getElementById('h_cn_'+$i)){
	document.getElementById('h_cn_'+$i).style.display='none';
}
if(document.getElementById('h_b_'+$i)){
	document.getElementById('h_b_'+$i).style.display='none';
}
if(document.getElementById('h_v_'+$i)){
	document.getElementById('h_v_'+$i).style.display='none';
}

if(document.getElementById('h_bn_'+$i)){
	document.getElementById('h_bn_'+$i).style.display='none';
}
if(document.getElementById('h_ba_'+$i)){
	document.getElementById('h_ba_'+$i).style.display='none';
}
if(document.getElementById('h_g_'+$i)){
	document.getElementById('h_g_'+$i).style.display='none';
}
if(document.getElementById('h_bs_'+$i)){
	document.getElementById('h_bs_'+$i).style.display='none';
}

if(document.getElementById('h_liste_pdf_'+$i)){
	document.getElementById('h_liste_pdf_'+$i).style.display='none';
}\n";

			echo "for(i=0;i<=".($nb_groupes+$nb_aid).";i++){
				if(document.getElementById('h_cn_'+i+'_'+$i)){
					document.getElementById('h_cn_'+i+'_'+$i).style.display='none';
				}
				if(document.getElementById('h_bn_'+i+'_'+$i)){
					document.getElementById('h_bn_'+i+'_'+$i).style.display='none';
				}
				if(document.getElementById('h_ba_'+i+'_'+$i)){
					document.getElementById('h_ba_'+i+'_'+$i).style.display='none';
				}
				if(document.getElementById('h_g_'+i+'_'+$i)){
					document.getElementById('h_g_'+i+'_'+$i).style.display='none';
				}
				if(document.getElementById('h_bs_'+i+'_'+$i)){
					document.getElementById('h_bs_'+i+'_'+$i).style.display='none';
				}
				if(document.getElementById('h_listes_'+i+'_'+$i)){
					document.getElementById('h_listes_'+i+'_'+$i).style.display='none';
				}
				if(document.getElementById('h_listes_csv_'+i+'_'+$i)){
					document.getElementById('h_listes_csv_'+i+'_'+$i).style.display='none';
				}
			}\n";
		}
		else{
			echo "if(document.getElementById('h_lien_affiche_'+$i)){
	document.getElementById('h_lien_affiche_'+$i).style.display='none';
}\n";
		}
	}
	echo "</script>\n";
}

if(getSettingValue('affiche_vacances_prof')!="no") {
	$html_tab_vacances=affiche_tableau_vacances("", "y", "n");
	if($html_tab_vacances!="") {
		echo "<div align='center' style='margin-top:1em;'>
		<p class='bold'>Vacances et jours fériés à venir</p>
		".$html_tab_vacances."
	</div>";
	}
}

//require("../lib/footer.inc.php");
echo "<p><br /></p>\n";
require("lib/footer.inc.php");
?>
