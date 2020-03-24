<?php

/*
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

// On indique qu'il faut creer des variables non protégées (voir fonction cree_variables_non_protegees())
//$variables_non_protegees = 'yes';

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

$sql="SELECT 1=1 FROM droits WHERE id='/cahier_texte_admin/admin_tag.php';";
$test=mysqli_query($GLOBALS["mysqli"], $sql);
if(mysqli_num_rows($test)==0) {
$sql="INSERT INTO droits SET id='/cahier_texte_admin/admin_tag.php',
administrateur='V',
professeur='F',
cpe='V',
scolarite='V',
eleve='F',
responsable='F',
secours='F',
autre='F',
description='Définition des tags pour les notices de Cahiers de textes',
statut='';";
$insert=mysqli_query($GLOBALS["mysqli"], $sql);
}
if (!checkAccess()) {
	header("Location: ../logout.php?auto=1");
	die();
}

if((!getSettingAOui("active_cahiers_texte"))&&(!getSettingAOui('acces_cdt_prof'))) {
	$mess=rawurlencode("Vous tentez d accéder au module Cahiers de textes qui est désactivé !");
	header("Location: ../accueil.php?msg=$mess");
	die();
}

$acces_admin=false;
if($_SESSION['statut']=='administrateur') {
	$acces_admin=true;
}
elseif(($_SESSION['statut']!='cpe')&&
	($_SESSION['statut']!='scolarite')) {
	$msg="Vous n'avez pas le droit d'accéder aux Tags CDT.";
	header("Location: ./index.php?msg=$msg");
	die();
}

$msg="";

/*
$date_begin_bookings=strftime("%d/%m/%Y", getSettingValue('begin_bookings'));
$date_end_bookings=strftime("%d/%m/%Y", getSettingValue('end_bookings'));

$display_date_debut=isset($_POST['display_date_debut']) ? $_POST['display_date_debut'] : (isset($_GET['display_date_debut']) ? $_GET['display_date_debut'] : $date_begin_bookings);
$display_date_fin=isset($_POST['display_date_fin']) ? $_POST['display_date_fin'] : (isset($_GET['display_date_fin']) ? $_GET['display_date_fin'] : $date_end_bookings);

$tab_id_tag=isset($_POST['tab_id_tag']) ? $_POST['tab_id_tag'] : array();
*/

//debug_var();

//$cpt=isset($_POST['cpt']) ? $_POST['cpt'] : NULL;

if(($acces_admin)&&(isset($_POST['is_posted']))) {
	check_token();

	$sql="SELECT * FROM ct_tag_type ORDER BY nom_tag;";
	//echo "$sql<br />";
	$res_tag=mysqli_query($mysqli, $sql);
	while($lig_tag=mysqli_fetch_object($res_tag)) {
		$sql_update="";
		if((isset($_POST['tag_compte_rendu_'.$lig_tag->id]))&&($lig_tag->tag_compte_rendu!="y")) {
			if($sql_update!="") {$sql_update.=", ";}
			$sql_update.="tag_compte_rendu='y'";
		}
		elseif((!isset($_POST['tag_compte_rendu_'.$lig_tag->id]))&&($lig_tag->tag_compte_rendu=="y")) {
			if($sql_update!="") {$sql_update.=", ";}
			$sql_update.="tag_compte_rendu='n'";
		}

		if((isset($_POST['tag_devoir_'.$lig_tag->id]))&&($lig_tag->tag_devoir!="y")) {
			if($sql_update!="") {$sql_update.=", ";}
			$sql_update.="tag_devoir='y'";
		}
		elseif((!isset($_POST['tag_devoir_'.$lig_tag->id]))&&($lig_tag->tag_devoir=="y")) {
			if($sql_update!="") {$sql_update.=", ";}
			$sql_update.="tag_devoir='n'";
		}

		if((isset($_POST['tag_notice_privee_'.$lig_tag->id]))&&($lig_tag->tag_notice_privee!="y")) {
			if($sql_update!="") {$sql_update.=", ";}
			$sql_update.="tag_notice_privee='y'";
		}
		elseif((!isset($_POST['tag_notice_privee_'.$lig_tag->id]))&&($lig_tag->tag_notice_privee=="y")) {
			if($sql_update!="") {$sql_update.=", ";}
			$sql_update.="tag_notice_privee='n'";
		}

		//echo "\$lig_tag->drapeau=".$lig_tag->drapeau."<br />";
		//echo "\$_POST['drapeau_".$lig_tag->id."]=".$_POST['drapeau_'.$lig_tag->id]."<br />";
		if((isset($_POST['drapeau_'.$lig_tag->id]))&&($lig_tag->drapeau!=$_POST['drapeau_'.$lig_tag->id])&&(in_array($_POST['drapeau_'.$lig_tag->id], $tab_drapeaux_tag_cdt))) {
			if($sql_update!="") {$sql_update.=", ";}
			$sql_update.="drapeau='".$_POST['drapeau_'.$lig_tag->id]."'";
		}
		//echo "\$sql_update=$sql_update<br />";

		if($sql_update!="") {
			$sql="UPDATE ct_tag_type SET ".$sql_update." WHERE id='".$lig_tag->id."';";
			//echo "$sql<br />";
			$update=mysqli_query($mysqli, $sql);
			if($update) {
				$msg.="Tag '".$lig_tag->nom_tag."' mis à jour.<br />";
			}
			else {
				$msg.="Erreur lors de la mise à jour du tag '".$lig_tag->nom_tag."'.<br />";
			}
		}
	}

	$tab_tag=array();
	$tab_tag["indice"]=array();
	$tab_tag["id"]=array();
	$sql="SELECT * FROM ct_tag_type ORDER BY nom_tag;";
	$res_tag=mysqli_query($mysqli, $sql);
	$loop=0;
	//$loop_modif="";
	while($lig_tag=mysqli_fetch_object($res_tag)) {
		$tab_tag["indice"][$loop]['id']=$lig_tag->id;
		$tab_tag["indice"][$loop]['nom_tag']=$lig_tag->nom_tag;
		$tab_tag["indice"][$loop]['drapeau']=$lig_tag->drapeau;
		$tab_tag["indice"][$loop]['tag_compte_rendu']=$lig_tag->tag_compte_rendu;
		$tab_tag["indice"][$loop]['tag_devoir']=$lig_tag->tag_devoir;
		$tab_tag["indice"][$loop]['tag_notice_privee_rendu']=$lig_tag->tag_notice_privee;

		$tab_tag["id"][$lig_tag->id]['id']=$lig_tag->id;
		$tab_tag["id"][$lig_tag->id]['nom_tag']=$lig_tag->nom_tag;
		$tab_tag["id"][$lig_tag->id]['drapeau']=$lig_tag->drapeau;
		$tab_tag["id"][$lig_tag->id]['tag_compte_rendu']=$lig_tag->tag_compte_rendu;
		$tab_tag["id"][$lig_tag->id]['tag_devoir']=$lig_tag->tag_devoir;
		$tab_tag["id"][$lig_tag->id]['tag_notice_privee_rendu']=$lig_tag->tag_notice_privee;

		$tab_tag["nom_tag"][$lig_tag->nom_tag]["id"]=$lig_tag->id;
		$tab_tag["nom_tag"][$lig_tag->nom_tag]["indice"]=$loop;

		$loop++;
	}

	$cpt=count($tab_tag["indice"]);

	$cpt_suppr=0;
	foreach($tab_tag["id"] as $id_tag => $current_tag) {
		if(isset($_POST["suppr_tag_".$id_tag])) {
			$sql="SELECT 1=1 FROM ct_tag WHERE id_tag='".$_POST["suppr_tag_".$id_tag]."';";
			//echo "$sql<br />";
			$test=mysqli_query($mysqli, $sql);
			if(mysqli_num_rows($test)>0) {
				$msg.="Suppression impossible du tag '".$tab_tag["id"][$_POST["suppr_tag_".$id_tag]]['nom_tag']."' associé à ".mysqli_num_rows($test)." notices.<br />";
			}
			else {
				$sql="DELETE FROM ct_tag_type WHERE id='".$_POST["suppr_tag_".$id_tag]."';";
				//echo "$sql<br />";
				$del=mysqli_query($mysqli, $sql);
				if(!$del) {
					$msg.="Erreur lors de la suppression du tag '".$tab_tag["id"][$_POST["suppr_tag_".$id_tag]]['nom_tag']."'.<br />";
				}
				else {
					$msg.="Suppression du tag '".$tab_tag["id"][$_POST["suppr_tag_".$id_tag]]['nom_tag']."' effectuée.<br />";
					$cpt_suppr++;
				}
			}
		}
	}

	$cpt_insert=0;
	if((isset($_POST['nom_tag_nouveau']))&&($_POST['nom_tag_nouveau']!="")) {
		$sql="INSERT INTO ct_tag_type SET nom_tag='".$_POST['nom_tag_nouveau']."'";

		if(isset($_POST['tag_compte_rendu_nouveau'])) {
			$sql.=", tag_compte_rendu='y'";
		}
		else {
			$sql.=", tag_compte_rendu='n'";
		}

		if(isset($_POST['tag_devoir_nouveau'])) {
			$sql.=", tag_devoir='y'";
		}
		else {
			$sql.=", tag_devoir='n'";
		}

		if(isset($_POST['tag_notice_privee_nouveau'])) {
			$sql.=", tag_notice_privee='y'";
		}
		else {
			$sql.=", tag_notice_privee='n'";
		}

		if(in_array($_POST['drapeau_nouveau'], $tab_drapeaux_tag_cdt)) {
			$sql.=", drapeau='".$_POST['drapeau_nouveau']."'";
		}
		else {
			$sql.=", drapeau='".$tab_drapeaux_tag_cdt[0]."'";
		}

		//echo "$sql<br />";
		$insert=mysqli_query($mysqli, $sql);
		if(!$insert) {
			$msg.="Erreur lors de la définition d'un nouveau tag.<br />";
		}
		else {
			$msg.="Nouveau tag créé.<br />";
			$cpt_insert++;
		}
	}

	if(($cpt_suppr>0)||($cpt_insert>0)) {
		$tab_tag=array();
		$sql="SELECT * FROM ct_tag_type ORDER BY nom_tag;";
		$res_tag=mysqli_query($mysqli, $sql);
		$loop=0;
		//$loop_modif="";
		while($lig_tag=mysqli_fetch_object($res_tag)) {
			$tab_tag["indice"][$loop]['id']=$lig_tag->id;
			$tab_tag["indice"][$loop]['nom_tag']=$lig_tag->nom_tag;
			$tab_tag["indice"][$loop]['drapeau']=$lig_tag->drapeau;
			$tab_tag["indice"][$loop]['tag_compte_rendu']=$lig_tag->tag_compte_rendu;
			$tab_tag["indice"][$loop]['tag_devoir']=$lig_tag->tag_devoir;
			$tab_tag["indice"][$loop]['tag_notice_privee_rendu']=$lig_tag->tag_notice_privee;

			$tab_tag["id"][$lig_tag->id]['id']=$lig_tag->id;
			$tab_tag["id"][$lig_tag->id]['nom_tag']=$lig_tag->nom_tag;
			$tab_tag["id"][$lig_tag->id]['drapeau']=$lig_tag->drapeau;
			$tab_tag["id"][$lig_tag->id]['tag_compte_rendu']=$lig_tag->tag_compte_rendu;
			$tab_tag["id"][$lig_tag->id]['tag_devoir']=$lig_tag->tag_devoir;
			$tab_tag["id"][$lig_tag->id]['tag_notice_privee_rendu']=$lig_tag->tag_notice_privee;

			$tab_tag["nom_tag"][$lig_tag->nom_tag]["id"]=$lig_tag->id;
			$tab_tag["nom_tag"][$lig_tag->nom_tag]["indice"]=$loop;

			$loop++;
		}
	}

	/*
	$_POST['tag_compte_rendu_0']=	y
	$_POST['tag_devoir_0']=	y
	$_POST['tag_notice_privee_0']=	y
	$_POST['drapeau_0']=	images/icons/flag2.gif
	$_POST['nom_tag']=	EPI
	$_POST['tag_compte_rendu_1']=	y
	$_POST['tag_devoir_1']=	y
	$_POST['tag_notice_privee_1']=	y
	$_POST['drapeau_1']=	images/bulle_bleue.png
	$_POST['cpt']=	1
	*/

	/*
	echo "<pre>";
	print_r($tab_tag);
	echo "</pre>";
	*/

}

$style_specifique[] = "lib/DHTMLcalendar/calendarstyle";
$javascript_specifique[] = "lib/DHTMLcalendar/calendar";
$javascript_specifique[] = "lib/DHTMLcalendar/lang/calendar-fr";
$javascript_specifique[] = "lib/DHTMLcalendar/calendar-setup";

$themessage  = 'Des informations ont été modifiées. Voulez-vous vraiment quitter sans enregistrer ?';
//**************** EN-TETE *****************
$titre_page = "Cahiers de textes : Définition des tags";
require_once("../lib/header.inc.php");
//**************** FIN EN-TETE *****************

//debug_var();

echo "<p class='bold'><a href='index.php' onclick=\"return confirm_abandon (this, change, '$themessage')\"><img src='../images/icons/back.png' alt='Retour' class='back_link'/> Retour</a>\n";
echo " | <a href='../cahier_texte_2/extract_tag.php' onclick=\"return confirm_abandon (this, change, '$themessage')\">Extraction tag</a>";
echo "</p>\n";

echo "<form enctype='multipart/form-data' action='".$_SERVER['PHP_SELF']."' method='post' name='formulaire'>
<fieldset class='fieldset_opacite50'>\n";
echo add_token_field();

echo "<p class='bold'>Saisie de tags pour les notices de cahiers de textes&nbsp;:</p>\n";
echo "<blockquote>\n";

$cpt=0;
$sql="SELECT * FROM ct_tag_type ORDER BY nom_tag;";
$res=mysqli_query($GLOBALS["mysqli"], $sql);
if(mysqli_num_rows($res)==0) {
	echo "<p>Aucun tag n'est encore défini.</p>\n";
}
elseif($acces_admin) {
	echo "<p>Tags existants&nbsp;:</p>\n";
	echo "<a name='tab'></a><table class='boireaus' border='1' summary='Tableau des tags existants'>\n";
	echo "<tr>\n";
	echo "<th title='Identifiant' rowspan='2'>Id</th>\n";
	echo "<th rowspan='2'>Nom du tag</th>\n";
	echo "<th colspan='3'>Proposé sur les notices</th>\n";
	// colspan='".count($tab_drapeaux_tag_cdt)."'
	echo "<th rowspan='2' colspan='2'>Drapeau/icone<br />";
	for($loop=0;$loop<count($tab_drapeaux_tag_cdt);$loop++) {
		echo "<img src='../".$tab_drapeaux_tag_cdt[$loop]."' title='".$tab_drapeaux_tag_cdt[$loop]."' /> ";
	}
	echo "</th>\n";
	echo "<th rowspan='2'>Supprimer</th>\n";
	echo "</tr>\n";
	echo "<tr>\n";
	echo "<th>comptes-rendus</th>\n";
	echo "<th>devoirs</th>\n";
	echo "<th>privées</th>\n";
	echo "</tr>\n";
	$alt=1;
	$nb_sts=mysqli_num_rows($res);
	while($lig=mysqli_fetch_object($res)) {
		$alt=$alt*(-1);
		echo "<tr class='lig$alt' onmouseover=\"this.style.backgroundColor='white';\" onmouseout=\"this.style.backgroundColor='';\">\n";

		echo "<td>\n";
		echo "<label for='suppr_tag_".$lig->id."' style='cursor:pointer;'>";
		echo $lig->id;
		echo "</label>";
		echo "</td>\n";

		echo "<td>\n";
		echo "<label for='suppr_tag_".$lig->id."' style='cursor:pointer;'>";
		echo $lig->nom_tag;
		echo "</label>";
		echo "</td>\n";

		echo "<td>\n";
		echo "<input type='checkbox' name='tag_compte_rendu_".$lig->id."' id='tag_compte_rendu_".$lig->id."' value='y' ";
		if($lig->tag_compte_rendu=="y") {
			echo "checked ";
		}
		echo "/>";
		echo "</td>\n";

		echo "<td>\n";
		echo "<input type='checkbox' name='tag_devoir_".$lig->id."' id='tag_compte_rendu_".$lig->id."' value='y' ";
		if($lig->tag_devoir=="y") {
			echo "checked ";
		}
		echo "/>";
		echo "</td>\n";

		echo "<td>\n";
		echo "<input type='checkbox' name='tag_notice_privee_".$lig->id."' id='tag_compte_rendu_".$lig->id."' value='y' ";
		if($lig->tag_notice_privee=="y") {
			echo "checked ";
		}
		echo "/>";
		echo "</td>\n";

		echo "<td>\n";
		echo "<img src='../".$lig->drapeau."' title='Drapeau actuel : ".$lig->drapeau."' />";
		echo "</td>\n";

		echo "<td>\n";
		echo "<select name='drapeau_".$lig->id."' id='drapeau_".$lig->id."'>";
		for($loop=0;$loop<count($tab_drapeaux_tag_cdt);$loop++) {
			$selected="";
			if($tab_drapeaux_tag_cdt[$loop]==$lig->drapeau) {
				$selected=" selected='true'";
			}
			echo "
		<option value='".$tab_drapeaux_tag_cdt[$loop]."'".$selected.">".$tab_drapeaux_tag_cdt[$loop]."</option>";
		}
		echo "
		</select>";
		echo "</td>\n";

		echo "<td>";
		$sql="SELECT 1=1 FROM ct_tag WHERE id_tag='$lig->id';";
		//echo "$sql<br />";
		$test=mysqli_query($GLOBALS["mysqli"], $sql);
		if(mysqli_num_rows($test)==0) {
			echo "<input type='checkbox' name='suppr_tag_".$lig->id."' id='suppr_tag_".$lig->id."' value=\"$lig->id\" onchange='changement();' />";
		}
		else {
			echo "<span title='Cet tag est associé à ".mysqli_num_rows($test)." notice(s) saisie(s).'>Tag associé (".mysqli_num_rows($test).")</span>";
		}
		echo "</td>\n";
		echo "</tr>\n";

		$cpt++;
	}

	echo "</table>\n";
}
else {
	echo "<p>Tags existants&nbsp;:</p>\n";
	echo "<a name='tab'></a><table class='boireaus' border='1' summary='Tableau des tags existants'>\n";
	echo "<tr>\n";
	echo "<th title='Identifiant' rowspan='2'>Id</th>\n";
	echo "<th rowspan='2'>Nom du tag</th>\n";
	echo "<th colspan='3'>Proposé sur les notices</th>\n";
	// colspan='".count($tab_drapeaux_tag_cdt)."'
	echo "<th rowspan='2'>Drapeau/icone<br />";
	for($loop=0;$loop<count($tab_drapeaux_tag_cdt);$loop++) {
		echo "<img src='../".$tab_drapeaux_tag_cdt[$loop]."' title='".$tab_drapeaux_tag_cdt[$loop]."' /> ";
	}
	echo "</th>\n";
	echo "<th rowspan='2'>Associé</th>\n";
	echo "</tr>\n";
	echo "<tr>\n";
	echo "<th>comptes-rendus</th>\n";
	echo "<th>devoirs</th>\n";
	echo "<th>privées</th>\n";
	echo "</tr>\n";
	$alt=1;
	$nb_sts=mysqli_num_rows($res);
	while($lig=mysqli_fetch_object($res)) {
		$alt=$alt*(-1);
		echo "<tr class='lig$alt' onmouseover=\"this.style.backgroundColor='white';\" onmouseout=\"this.style.backgroundColor='';\">\n";

		echo "<td>\n";
		echo $lig->id;
		echo "</td>\n";

		echo "<td>\n";
		echo $lig->nom_tag;
		echo "</td>\n";

		echo "<td>\n";
		if($lig->tag_compte_rendu=="y") {
			echo "<img src='../images/enabled.png' class='icone16' title='Tag proposé sur les notices de compte-rendus de séances' />";
		}
		echo "</td>\n";

		echo "<td>\n";
		if($lig->tag_devoir=="y") {
			echo "<img src='../images/enabled.png' class='icone16' title='Tag proposé sur les notices de travaux à faire à la maison' />";
		}
		echo "</td>\n";

		echo "<td>\n";
		if($lig->tag_notice_privee=="y") {
			echo "<img src='../images/enabled.png' class='icone16' title='Tag proposé sur les notices privées' />";
		}
		echo "</td>\n";

		echo "<td>\n";
		echo "<img src='../".$lig->drapeau."' title='Drapeau actuel : ".$lig->drapeau."' />";
		echo "</td>\n";

		echo "<td>";
		$sql="SELECT 1=1 FROM ct_tag WHERE id_tag='$lig->id';";
		//echo "$sql<br />";
		$test=mysqli_query($GLOBALS["mysqli"], $sql);
		if(mysqli_num_rows($test)==0) {
			echo "<span title='Cet tag n'est associé à aucune notice.'>0</span>";
		}
		else {
			echo "<span title='Cet tag est associé à ".mysqli_num_rows($test)." notice(s) saisie(s).'>Tag associé (".mysqli_num_rows($test).")</span>";
		}
		echo "</td>\n";
		echo "</tr>\n";

		$cpt++;
	}

	echo "</table>\n";
}

if($acces_admin) {
	echo "<p style='margin-top:1em; margin-left:3em; text-indent:-3em;'>Définir un nouveau tag&nbsp;: <input type='text' name='nom_tag_nouveau' value='' onchange='changement();' /> proposé sur les notices<br />
	<input type='checkbox' name='tag_compte_rendu_nouveau' id='tag_compte_rendu_nouveau' value='y' /><label for='tag_compte_rendu_nouveau'> de compte-rendus de séances</label><br />
	<input type='checkbox' name='tag_devoir_nouveau' id='tag_devoir_nouveau' value='y' /><label for='tag_devoir_nouveau'> de devoirs à faire</label><br />
	<input type='checkbox' name='tag_notice_privee_nouveau' id='tag_notice_privee_nouveau' value='y' /><label for='tag_notice_privee_nouveau'> de notices privées</label><br />
	avec le drapeau/icone suivant <select name='drapeau_nouveau' id='drapeau_nouveau'>";
	for($loop=0;$loop<count($tab_drapeaux_tag_cdt);$loop++) {
		echo "
	<option value='".$tab_drapeaux_tag_cdt[$loop]."'>".$tab_drapeaux_tag_cdt[$loop]."</option>";
	}
	echo "
	</select></p>

	<input type='hidden' name='cpt' value='$cpt' />
	<input type='hidden' name='is_posted' value='y' />

	<p><input type='submit' name='valider' value='Valider' /></p>\n";
}

echo "</blockquote>\n";
echo "</fieldset>\n";
echo "</form>\n";
echo "<p><br /></p>\n";

//=============================================
/*
// Déjà implémenté dans cahier_texte_2/extract_tag.php

$tab_tag=array();
$tab_tag["indice"]=array();
$tab_tag["id"]=array();
$sql="SELECT * FROM ct_tag_type ORDER BY nom_tag;";
$res_tag=mysqli_query($mysqli, $sql);
$loop=0;
//$loop_modif="";
while($lig_tag=mysqli_fetch_assoc($res_tag)) {
	$tab_tag["indice"][$loop]=$lig_tag;

	$tab_tag["id"][$lig_tag['id']]=$lig_tag;

	$loop++;
}

echo "<form enctype='multipart/form-data' action='".$_SERVER['PHP_SELF']."' method='post' name='formulaire'>
	<fieldset class='fieldset_opacite50'>
		<p class='bold'>Rechercher les saisies avec tels tags dans les notices de cahiers de textes&nbsp;:</p>
		<blockquote>
			<p>Rechercher les notices de CDT de la date : 
				<input type='text' name = 'display_date_debut' id = 'display_date_debut' size='10' value = \"".$display_date_debut."\" onKeyDown=\"clavier_date(this.id,event);\" AutoComplete=\"off\" />".img_calendrier_js("display_date_debut", "img_bouton_display_date_debut")."
				&nbsp;à la date : 
				<input type='text' name = 'display_date_fin' id = 'display_date_fin' size='10' value = \"".$display_date_fin."\" onKeyDown=\"clavier_date(this.id,event);\" AutoComplete=\"off\" />".img_calendrier_js("display_date_fin", "img_bouton_display_date_fin")."<br />
				 (<i>Veillez à respecter le format jj/mm/aaaa</i>)
			</p>
			<br />
			<p>
				Restreindre l'extraction aux notices taguées avec le ou les tags suivants&nbsp;:<br />";
foreach($tab_tag["id"] as $id_tag => $tag) {
	$checked='';
	if(in_array($id_tag, $tab_id_tag)) {
		$checked=' checked';
	}
	echo "
				<input type='checkbox' name='tab_id_tag[]' id='id_tag_".$id_tag."' value='".$id_tag."' onchange=\"checkbox_change(this.id)\"".$checked." /><label for='id_tag_".$id_tag."' id='texte_id_tag_".$id_tag."'>".$tag['nom_tag']."</label><br />";
}
echo "
			</p>
			<input type='hidden' name='extraire_notices' value='y' />

			<p><input type='submit' name='valider' value='Valider' /></p>
		</blockquote>
	</fieldset>
</form>
<p><br /></p>
".js_checkbox_change_style('checkbox_change', 'texte_', 'y');

echo "<p><em>NOTES&nbsp;:</em></p>\n";
echo "<ul>\n";
echo "<!--li style='color:red'>Pour le moment le dispositif n'est implémenté que pour le CDT2.</li-->\n";
echo "<li>Il est prévu de permettre dans le futur d'extraire la liste des notices avec tel ou tel tag.</li>\n";
echo "</ul>\n";
echo "<p><br /></p>\n";
*/

require("../lib/footer.inc.php");
?>
