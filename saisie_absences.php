<?php

/*
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
$variables_non_protegees = 'yes';

// Initialisations files
require_once("../lib/initialisations.inc.php");

extract($_GET, EXTR_OVERWRITE);
extract($_POST, EXTR_OVERWRITE);



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

// Vérifications
if((!isset($id_classe))||(!preg_match("/^[0-9]{1,}$/", $id_classe))||(!isset($periode_num))||(!preg_match("/^[0-9]{1,}$/", $periode_num))) {
	$msg="Il faut choisir une classe et une période.";
	header("Location:index.php?msg=$msg");
}

include "../lib/periodes.inc.php";

// Si le témoin temoin_check_srv() doit être affiché, on l'affichera dans la page à côté de Enregistrer.
$aff_temoin_serveur_hors_entete="y";

$acces="n";
if($ver_periode[$periode_num]=="N") {
	$acces="y";
}
elseif(($ver_periode[$periode_num]=="P")&&($_SESSION['statut']=='secours')) {
	$acces="y";
}

$saisie_totaux=false;
$saisie_app=false;
if($acces=='y') {
	$saisie_totaux=true;
	$saisie_app=true;
}
else {
	$date_courante=time();

	$sql="SELECT UNIX_TIMESTAMP(date_limite) AS date_limite, totaux, appreciation, mode FROM abs_bull_delais WHERE id_classe='$id_classe' AND periode='$periode_num';";
	//echo "$sql<br />";
	$res=mysqli_query($GLOBALS["mysqli"], $sql);
	if(mysqli_num_rows($res)>0) {
		$lig=mysqli_fetch_object($res);
		$date_limite=$lig->date_limite;
		//echo "\$date_limite=$date_limite en période $k.<br />";
		//echo "\$date_courante=$date_courante.<br />";

		if($date_courante<$date_limite) {
			if($lig->totaux=='y') {
				$saisie_totaux=true;
			}
			if($lig->appreciation=='y') {
				$saisie_app=true;
			}
		}
	}
}

//if($acces=="n") {
if((!$saisie_app)&&(!$saisie_totaux)) {
	$msg="La période $periode_num est close pour cette classe.";
	header("Location:index.php?id_classe=$id_classe&msg=$msg");
}

if (isset($_POST['is_posted']) and $_POST['is_posted'] == "yes") {
	check_token();

	$msg="";
	$nb_reg=0;
	$nb_err=0;
	if($saisie_app) {
		if (isset($NON_PROTECT["app_grp"])){
			$ap = traitement_magic_quotes(corriger_caracteres($NON_PROTECT["app_grp"]));
		}
		else{
			$ap = "";
		}
		$ap=nettoyage_retours_ligne_surnumeraires($ap);

		$sql="SELECT * FROM absences_appreciations_grp WHERE (id_classe='".$id_classe."' AND periode='$periode_num')";
		$test=mysqli_query($GLOBALS["mysqli"], $sql);
		if(mysqli_num_rows($test)>0) {
			$sql="UPDATE absences_appreciations_grp SET appreciation='$ap' WHERE (id_classe='".$id_classe."' AND periode='$periode_num');";
		} else {
			$sql="INSERT INTO absences_appreciations_grp SET id_classe='".$id_classe."', periode='$periode_num', appreciation='$ap';";
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

	if ((($_SESSION['statut']=="cpe")&&(getSettingValue('GepiAccesAbsTouteClasseCpe')=='yes'))||($_SESSION['statut']!="cpe")) {
		$sql="SELECT e.login FROM eleves e, j_eleves_classes c WHERE ( c.id_classe='$id_classe' AND c.login = e.login AND c.periode='$periode_num')";
	} else {
		$sql="SELECT e.login FROM eleves e, j_eleves_classes c, j_eleves_cpe j WHERE (c.id_classe='$id_classe' AND j.e_login = c.login AND e.login = j.e_login AND j.cpe_login = '".$_SESSION['login'] . "' AND c.periode = '$periode_num')";
	}
	$quels_eleves = mysqli_query($GLOBALS["mysqli"], $sql);

	//=========================
	// AJOUT: boireaus 20071010
	$log_eleve=$_POST['log_eleve'];
	$nb_abs_ele=$_POST['nb_abs_ele'];
	$nb_nj_ele=$_POST['nb_nj_ele'];
	$nb_retard_ele=$_POST['nb_retard_ele'];
	//$app_ele=$_POST['app_ele'];
	//=========================

	$quels_eleves = mysqli_query($GLOBALS["mysqli"], "SELECT e.login FROM eleves e, j_eleves_classes c WHERE (c.id_classe='$id_classe' AND e.login = c.login AND c.periode='$periode_num')");
	$lignes = mysqli_num_rows($quels_eleves);
	$j = '0';
	while($j < $lignes) {
		$reg_eleve_login = old_mysql_result($quels_eleves, $j, "login");

		//=========================
		// AJOUT: boireaus 20071007
		// Récupération du numéro de l'élève dans les saisies:
		$num_eleve=-1;
		for($i=0;$i<count($log_eleve);$i++){
			if($reg_eleve_login==$log_eleve[$i]){
				$num_eleve=$i;
				break;
			}
		}
		if($num_eleve!=-1){
			//=========================

			//=========================

			$nb_absences=$nb_abs_ele[$num_eleve];
			$nb_nj=$nb_nj_ele[$num_eleve];
			$nb_retard=$nb_retard_ele[$num_eleve];
			//$ap=$app_ele[$num_eleve];
			//$ap = traitement_magic_quotes(corriger_caracteres(html_entity_decode($ap)));


			$app_ele_courant="app_eleve_".$num_eleve;
			//echo "\$app_ele_courant=$app_ele_courant<br />";
			if (isset($NON_PROTECT[$app_ele_courant])){
				$ap = traitement_magic_quotes(corriger_caracteres($NON_PROTECT[$app_ele_courant]));
			}
			else{
				$ap = "";
			}
			//echo "\$ap=$ap<br />";

			// Contrôle des saisies pour supprimer les sauts de lignes surnuméraires.
			$ap=nettoyage_retours_ligne_surnumeraires($ap);
			//=========================

			if (!(preg_match ("/^[0-9]{1,}$/", $nb_absences))) {
				$nb_absences = '';
			}
			if (!(preg_match ("/^[0-9]{1,}$/", $nb_nj))) {
				$nb_nj = '';
			}
			if (!(preg_match ("/^[0-9]{1,}$/", $nb_retard))) {
				$nb_retard = '';
			}

			$sql_ajout='';
			if($saisie_totaux) {
				$sql_ajout.=" nb_absences='$nb_absences', non_justifie='$nb_nj', nb_retards='$nb_retard'";
			}
			if($saisie_app) {
				if($sql_ajout!='') {
					$sql_ajout.=', ';
				}
				$sql_ajout.=" appreciation='$ap'";
			}

			if($sql_ajout!='') {
				$test_eleve_nb_absences_query = mysqli_query($GLOBALS["mysqli"], "SELECT * FROM absences WHERE (login='$reg_eleve_login' AND periode='$periode_num')");
				$test_nb = mysqli_num_rows($test_eleve_nb_absences_query);
				if ($test_nb != "0") {
					$sql="UPDATE absences SET".$sql_ajout." WHERE (login='$reg_eleve_login' AND periode='$periode_num');";
				} else {
					$sql="INSERT INTO absences SET login='$reg_eleve_login', periode='$periode_num', ".$sql_ajout.";";
				}
				//echo "$sql<br />";
				$register = mysqli_query($GLOBALS["mysqli"], $sql);
				if (!$register) {
					$msg.="Erreur lors de l'enregistrement des données pour $reg_eleve_login.<br />";
					$nb_err++;
				}
				else {
					$nb_reg++;
				}
			}
		}
		$j++;
	}
	//$affiche_message = 'yes';
	if(!isset($msg)) {
		$msg='Les modifications ont été enregistrées ('.strftime("%d/%m/%Y à %H:%M:%S").') !<br />';
	}
	if($nb_reg>0) {
		$msg.=$nb_reg." enregistrement(s) effectué(s).<br />";
	}
}
$themessage  = 'Des champs ont été modifiés. Voulez-vous vraiment quitter sans enregistrer ?';
//$message_enregistrement = 'Les modifications ont été enregistrées !';

$javascript_specifique = "saisie/scripts/js_saisie";
//**************** EN-TETE *****************
$titre_page = "Saisie des absences";
require_once("../lib/header.inc.php");
//**************** FIN EN-TETE *****************
?>
<script type="text/javascript" language="javascript">
change = 'no';
</script>

<form enctype="multipart/form-data" action="saisie_absences.php" method="post">
<?php
echo add_token_field(true);
?>
<p class="bold">
<a href="index.php?id_classe=<?php echo $id_classe; ?>" onclick="return confirm_abandon (this, change, '<?php echo $themessage; ?>')"><img src='../images/icons/back.png' alt='Retour' class='back_link'/> Choisir une autre période</a> |
<a href="index.php" onclick="return confirm_abandon (this, change, '<?php echo $themessage; ?>')">Choisir une autre classe</a> | <input type="submit" value="Enregistrer" /> | <a href="<?php echo "consulter_absences.php?id_classe=$id_classe&amp;periode_num=$periode_num";?>">Consulter les absences de la classe</a></p>


<?php
$call_classe = mysqli_query($GLOBALS["mysqli"], "SELECT classe FROM classes WHERE id = '$id_classe'");
$classe = old_mysql_result($call_classe, "0", "classe");

$appreciation_absences_grp="";
$sql="SELECT * FROM absences_appreciations_grp WHERE id_classe='".$id_classe."' AND periode='".$periode_num."';";
$res_abs_grp_clas=mysqli_query($GLOBALS["mysqli"], $sql);
if(mysqli_num_rows($res_abs_grp_clas)>0) {
	$lig_abs_grp_clas=mysqli_fetch_object($res_abs_grp_clas);
	$appreciation_absences_grp=$lig_abs_grp_clas->appreciation;
}

$insert_mass_appreciation_type=getSettingValue("insert_mass_appreciation_type");
if (($saisie_app)&&($insert_mass_appreciation_type=="y")) {
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

echo "<div style='float:right; width:16px;'><a href='../impression/avis_pdf_absences.php?id_classe=$id_classe&periode_num=$periode_num' title=\"Imprimer les appréciations absences et nombre d'absences,... en PDF\" target='_blank'><img src='../images/icons/pdf.png' class='icone16' alt='Générer un PDF' /></a></div>";

?>
<p><b>Classe de <?php echo "$classe"; ?> - Saisie des absences : <?php $temp = my_strtolower($nom_periode[$periode_num]); echo "$temp"; ?></b></p>

<p style='margin-top:1em;'>
	<b>Appréciation sur le groupe classe pour la période <?php echo $periode_num;?>&nbsp;:</b><br />
	<textarea id='n0' name='no_anti_inject_app_grp' rows='2' cols='80'  wrap="virtual" 
					onKeyDown="clavier(this.id,event);" 
					onchange="changement()"><?php echo $appreciation_absences_grp;?></textarea>
</p>
<div id='div_verif_grp' style='color:red;'>
<?php
	if(!getSettingANon('active_recherche_lapsus')) {
		echo teste_lapsus($appreciation_absences_grp);
	}
?>
</div>

<!--table border=1 cellspacing=2 cellpadding=5-->
<table class='boireaus' cellspacing='2' cellpadding='5'>
<tr>
	<th align='center'><b>Nom Prénom</b></th>
	<th align='center'><b>Nb. total de 1/2 journées d'absence</b></th>
	<th align='center'><b>Nb. absences non justifiées</b></th>
	<th align='center'><b>Nb. de retard</b></th>
	<th align='center'><b>Observations</b></th>
</tr>
<?php
if ((($_SESSION['statut']=="cpe")&&(getSettingValue('GepiAccesAbsTouteClasseCpe')=='yes'))||($_SESSION['statut']!="cpe")) {
	$sql="SELECT e.* FROM eleves e, j_eleves_classes c WHERE ( c.id_classe='$id_classe' AND c.login = e.login AND c.periode='$periode_num') order by e.nom, e.prenom";
} else {
	$sql="SELECT e.* FROM eleves e, j_eleves_classes c, j_eleves_cpe j WHERE (c.id_classe='$id_classe' AND j.e_login = c.login AND e.login = j.e_login AND j.cpe_login = '".$_SESSION['login'] . "' AND c.periode = '$periode_num') order by e.nom, e.prenom";
}
$appel_donnees_eleves = mysqli_query($GLOBALS["mysqli"], $sql);

$nombre_lignes = mysqli_num_rows($appel_donnees_eleves);
$i = '0';
$num_id=10;
$alt=1;
$chaine_test_vocabulaire="";
while($i < $nombre_lignes) {
	$current_eleve_login = old_mysql_result($appel_donnees_eleves, $i, "login");
	$current_eleve_absences_query = mysqli_query($GLOBALS["mysqli"], "SELECT * FROM  absences WHERE (login='$current_eleve_login' AND periode='$periode_num')");
	$current_eleve_nb_absences = @old_mysql_result($current_eleve_absences_query, 0, "nb_absences");
	$current_eleve_nb_nj = @old_mysql_result($current_eleve_absences_query, 0, "non_justifie");
	$current_eleve_nb_retards = @old_mysql_result($current_eleve_absences_query, 0, "nb_retards");
	$current_eleve_ap_absences = @old_mysql_result($current_eleve_absences_query, 0, "appreciation");
	$current_eleve_nom = old_mysql_result($appel_donnees_eleves, $i, "nom");
	$current_eleve_prenom = old_mysql_result($appel_donnees_eleves, $i, "prenom");
	$current_eleve_login_nb = $current_eleve_login."_nb_abs";
	$current_eleve_login_nj = $current_eleve_login."_nb_nj";
	$current_eleve_login_retard = $current_eleve_login."_nb_retard";
	$current_eleve_login_ap = $current_eleve_login."_ap";

	$alt=$alt*(-1);
	echo "
	<tr class='lig$alt'>
		<td align='center'>
			".my_strtoupper($current_eleve_nom)." ".casse_mot($current_eleve_prenom,'majf2')."
			<input type='hidden' name='log_eleve[$i]' id='login_eleve_3$num_id' value='$current_eleve_login' />
		</td>
		<td align='center'>
			".($saisie_totaux ? "<input id=\"n".$num_id."\" onKeyDown=\"clavier(this.id,event);\" type='text' size='4' name='nb_abs_ele[$i]' value=\"".$current_eleve_nb_absences."\" onchange=\"changement()\" />" : $current_eleve_nb_absences)."
		</td>
		<td align='center'>
			".($saisie_totaux ? "<input id=\"n1".$num_id."\" onKeyDown=\"clavier(this.id,event);\" type='text' size='4' name='nb_nj_ele[$i]' value=\"".$current_eleve_nb_nj."\" onchange=\"changement()\" />" : $current_eleve_nb_nj)."
		</td>
		<td align='center'>
			".($saisie_totaux ? "<input id=\"n2".$num_id."\" onKeyDown=\"clavier(this.id,event);\" type='text' size='4' name='nb_retard_ele[$i]' value=\"".$current_eleve_nb_retards."\" onchange=\"changement()\" />" : $current_eleve_nb_retards)."
		</td>
		<td>
			".($saisie_app ? "<textarea id=\"n3".$num_id."\" 
				name='no_anti_inject_app_eleve_$i' rows='2' cols='50'  wrap=\"virtual\" 
				onKeyDown=\"clavier(this.id,event);\" 
				onchange=\"changement()\" 
				onfocus=\"focus_suivant(3".$num_id.");document.getElementById('focus_courant').value='3".$num_id."'; repositionner_commtype();\" 
				onblur=\"ajaxVerifAppreciations('".$current_eleve_login."_t".$periode_num."', '".$id_classe."', 'n3".$num_id."');\"
				>$current_eleve_ap_absences</textarea>" : nl2br($current_eleve_ap_absences))."
			<div id='div_verif_n3".$num_id."' style='color:red;'>";
	// Pour afficher au chargement de la page le résultat du test de lapsus sur ce qui a été précédemment enregistré:
	if(!getSettingANon('active_recherche_lapsus')) {
		echo teste_lapsus($current_eleve_ap_absences);
	}
	echo "</div>
		</td>
	</tr>\n";
	//=========================
	$i++;
	$num_id++;
}

?>
</table>
<input type="hidden" name="is_posted" value="yes" />
<input type="hidden" name="id_classe" value=<?php echo "$id_classe";?> />
<input type="hidden" name="periode_num" value=<?php echo "$periode_num";?> />
<center>
	<div id="fixe">
		<?php
			if(getSettingAOui('aff_temoin_check_serveur')) {
				temoin_check_srv();
			}
		?>

		<input type="submit" value="Enregistrer" /><br />

		<?php
			include('../saisie/ctp.php');
		?>

		<!-- Champ destiné à recevoir la valeur du champ suivant celui qui a le focus pour redonner le focus à ce champ après une validation -->
		<input type='hidden' id='info_focus' name='champ_info_focus' value='' />
		<input type='hidden' id='focus_courant' name='focus_courant' value='' />
	</div>
</center>
</form>

<?php

echo "<p>Il est impératif que vous ne laissiez pas de 'champ absence', 'absence_non_justifiee', 'retard' vide.<br />
Un champ retard vide n'est pas compris comme zéro retard, mais comme une absence de remplissage du champ.<br />
Si vous n'avez rempli que les champs non nuls, vous pouvez compléter d'un coup ci-dessous&nbsp;:<br />\n";
echo "<a href='javascript:complete_a_zero_champs_vides()'>Compléter les champs vides par des zéros</a>";
echo "</p>\n";

if(($saisie_app)||($saisie_totaux)) {
	echo "<br />
<p>Vous pouvez aussi vider les saisies, si vous voulez repartir à blanc.<br />
<span style='color:red'>Attention&nbsp;: L'opération est irréversible<br />
(<em>mais rien n'est pris en compte tant que vous ne cliquez pas sur Enregistrer</em>).</span></p>
<ul>
	".($saisie_totaux ? "<li><a href=\"javascript:vider_les_champs('');\">Vider la colonne Nombre de demi-journées d'absences</a></li>
	<li><a href=\"javascript:vider_les_champs('1');\">Vider la colonne Nombre d'absences non justifiées</a></li>
	<li><a href=\"javascript:vider_les_champs('2');\">Vider la colonne Nombre de retards</a></li>" : "")."
	".($saisie_app ? "<li><a href=\"javascript:vider_les_champs('3');\">Vider la colonne Observations</a></li>" : "")."
</ul>\n";
}
echo "<script type='text/javascript'>\n";

if((isset($chaine_test_vocabulaire))&&($chaine_test_vocabulaire!="")) {
	echo $chaine_test_vocabulaire;
}

echo "
function complete_a_zero_champs_vides() {
	for(i=10;i<$num_id;i++) {
		if(document.getElementById('n'+i)) {
			if(document.getElementById('n'+i).value=='') {
				document.getElementById('n'+i).value=0;
			}
		}

		if(document.getElementById('n1'+i)) {
			if(document.getElementById('n1'+i).value=='') {
				document.getElementById('n1'+i).value=0;
			}
		}

		if(document.getElementById('n2'+i)) {
			if(document.getElementById('n2'+i).value=='') {
				document.getElementById('n2'+i).value=0;
			}
		}
	}

	changement();
}

// prefixe='' 1/2 j abs
// prefixe='1' nbnj
// prefixe='2' retards
// prefixe='3' observations
function vider_les_champs(prefixe) {
	for(i=10;i<$num_id;i++) {
		if(document.getElementById('n'+prefixe+i)) {
			document.getElementById('n'+prefixe+i).value='';
		}
	}

	changement();
}

// Pour éviter une erreur dans les commentaires-types:
id_groupe='';

function focus_suivant(num){
	temoin='';
	// La variable 'dernier' peut dépasser de l'effectif de la classe... mais cela n'est pas dramatique
	dernier=num+".$nombre_lignes."
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
?>
<p><br /></p>
<?php require "../lib/footer.inc.php";?>
