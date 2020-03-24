<?php

// Passer à y pour afficher des infos de debugage
$debug_edt="n";

// Pouvoir régler l'opacité des couleurs de cours
$opacity_couleur=0.5;

$couleur_remplacement="red";
$couleur_remplaced="grey";

$tab_couleur_edt[1]="blue";
$tab_couleur_edt[2]="fuchsia";
$tab_couleur_edt[3]="lime";
$tab_couleur_edt[4]="maroon";
$tab_couleur_edt[5]="purple";
$tab_couleur_edt[6]="red";
$tab_couleur_edt[7]="white";
$tab_couleur_edt[8]="yellow";
$tab_couleur_edt[9]="aqua";
$tab_couleur_edt[10]="grey";
$tab_couleur_edt[11]="green";
$tab_couleur_edt[12]="olive";
$tab_couleur_edt[13]="teal";
$tab_couleur_edt[14]="#799C13";
$tab_couleur_edt[15]="#4BA829";
$tab_couleur_edt[16]="#D4D600";
$tab_couleur_edt[17]="#FFEC00";
$tab_couleur_edt[18]="#FCC300";
$tab_couleur_edt[19]="#DBAA73";
$tab_couleur_edt[20]="#745A32";
$tab_couleur_edt[21]="#E95D0F";
$tab_couleur_edt[22]="#99141B";
$tab_couleur_edt[23]="#009EE0";
$tab_couleur_edt[24]="#C19CC4";
// Les couleurs sont dans l'EDT classique Gepi définies dans templates/DefaultEDT/css/style_edt.css avec des .cadreCouleur1, .cadreCouleur2,...

$tab_liste_couleurs=array("aliceblue",
"antiquewhite",
"aquamarine",
"azure",
"beige",
"bisque",
"blanchedalmond",
"blueviolet",
"brown",
"burlywood",
"cadetblue",
"chartreuse",
"chocolate",
"coral",
"cornflowerblue",
"cornsilk",
"crimson",
"cyan",
"darkcyan",
"darkgoldenrod",
"darkgray",
"darkgreen",
"darkkhaki",
"darkmagenta",
"darkolivegreen",
"darkorange",
"darkorchid",
"darkred",
"darksalmon",
"darkseagreen",
"darkslateblue",
"darkslategray",
"darkturquoise",
"darkviolet",
"deeppink",
"deepskyblue",
"dimgray",
"dodgerblue",
"firebrick",
"floralwhite",
"forestgreen",
"gainsboro",
"ghostwhite",
"gold",
"goldenrod",
"greenyellow",
"honeydew",
"hotpink",
"indianred",
"indigo",
"ivory",
"khaki",
"lavender",
"lavenderblush",
"lawngreen",
"lemonchiffon",
"lightblue",
"lightcoral",
"lightcyan",
"lightgoldenrodyellow",
"lightgreen",
"lightgrey",
"lightpink",
"lightsalmon",
"lightseagreen",
"lightskyblue",
"lightslategray",
"lightsteelblue",
"lightyellow",
"limegreen",
"linen",
"magenta",
"mediumaquamarine",
"mediumblue",
"mediumorchid",
"mediumpurple",
"mediumseagreen",
"mediumslateblue",
"mediumspringgreen",
"mediumturquoise",
"mediumvioletred",
"mintcream",
"mistyrose",
"moccasin",
"navajowhite",
"oldlace",
"olivedrab",
"orange",
"orangered",
"orchid",
"palegoldenrod",
"palegreen",
"paleturquoise",
"palevioletred",
"papayawhip",
"peachpuff",
"peru",
"pink",
"plum",
"powderblue",
"rosybrown",
"royalblue",
"saddlebrown",
"salmon",
"sandybrown",
"seagreen",
"seashell",
"sienna",
"silver",
"skyblue",
"slateblue",
"slategray",
"snow",
"springgreen",
"steelblue",
"tan",
"thistle",
"tomato",
"turquoise",
"violet",
"wheat",
"whitesmoke");
$rang_courant=count($tab_couleur_edt)+1;
for($loop=0;$loop<count($tab_liste_couleurs);$loop++) {
	$tab_couleur_edt[$rang_courant]=$tab_liste_couleurs[$loop];
	$rang_courant++;
}
/*
echo "<pre>";
print_r($tab_couleur_edt);
echo "</pre>";
*/

/*
// Couleurs pour les onglets dans visu_eleve.inc.php:
$tab_couleur_onglet['eleve']="moccasin";
$tab_couleur_onglet['responsables']="mintcream";
$tab_couleur_onglet['enseignements']="whitesmoke";
$tab_couleur_onglet['bulletins']="lightyellow";
$tab_couleur_onglet['bulletin']="lemonchiffon";
$tab_couleur_onglet['releves']="papayawhip";
$tab_couleur_onglet['releve']="seashell";
$tab_couleur_onglet['cdt']="linen";
$tab_couleur_onglet['anna']="blanchedalmond";
$tab_couleur_onglet['absences']="azure";
$tab_couleur_onglet['discipline']="salmon";
$tab_couleur_onglet['fp']="linen";
*/

$tab_couleur_onglet['releves']="papayawhip";
$tab_couleur_onglet['releve']="seashell";
$tab_couleur_onglet['cdt']="linen";
$tab_couleur_onglet['absences']="azure";
$tab_couleur_onglet['discipline']="salmon";

$tab_couleur_onglet['edt']="moccasin";
$tab_couleur_onglet['indication']="linen";

function get_days_from_week_number($num_semaine ,$annee) {
	$tab=array();

	for($num_jour=1; $num_jour<=7; $num_jour++) {
		//$ts=strtotime($annee."W".$num_semaine.$num_jour);
		//$ts=strtotime($annee."W".$num_semaine."-".$num_jour);
		$ts=strtotime($annee."-W".sprintf("%02d",$num_semaine)."-".$num_jour);
		$tab['num_jour'][$num_jour]['nom_jour']=french_strftime("%A", $ts);
		$tab['num_jour'][$num_jour]['timestamp']=$ts;
		$tab['num_jour'][$num_jour]['jj']=date('d', $ts);
		$tab['num_jour'][$num_jour]['mm']=date('m', $ts);
		$tab['num_jour'][$num_jour]['aaaa']=date('Y', $ts);
		$tab['num_jour'][$num_jour]['jjmmaaaa']=date('d/m/Y', $ts);
		$tab['num_jour'][$num_jour]['aaaammjj']=date('Ymd', $ts);
	}

	return $tab;
}

function get_dernier_dimanche_du_mois($mois, $annee) {
	// Fonction utilisée pour les mois de mars et octobre (31 jours)
	for($i=31;$i>1;$i--) {
		$ts=mktime(0, 0, 0, $mois , $i, $annee);
		if(id_j_semaine($ts)==7) {
			break;
		}
	}
	return $i;
}

function get_nom_matiere_gepi_pour_matiere_ics($matiere_ics) {
	$retour=$matiere_ics;

	$sql="SELECT matiere FROM edt_ics_matiere WHERE matiere_ics='$matiere_ics';";
	//$html.="$sql<br />";
	$res=mysqli_query($GLOBALS["mysqli"], $sql);
	if(mysqli_num_rows($res)>0) {
		$lig=mysqli_fetch_object($res);
		$retour=$lig->matiere;
	}

	return $retour;
}

function get_tab_matiere_gepi_pour_matiere_ics($matiere_ics) {
	$retour['matiere']=$matiere_ics;
	$retour['nom_complet']=$matiere_ics;
	$retour['association_faite']="n";

	$sql="SELECT m.* FROM matieres m, edt_ics_matiere eim WHERE eim.matiere_ics='$matiere_ics' AND eim.matiere=m.matiere;";
	//$html.="$sql<br />";
	$res=mysqli_query($GLOBALS["mysqli"], $sql);
	if(mysqli_num_rows($res)>0) {
		$lig=mysqli_fetch_object($res);
		$retour['matiere']=$lig->matiere;
		$retour['nom_complet']=$lig->nom_complet;
		$retour['association_faite']="y";
	}

	return $retour;
}

function get_nom_prof_gepi_pour_prof_ics($prof_ics) {
	$retour=$prof_ics;

	$sql="SELECT u.civilite, u.nom, u.prenom FROM utilisateurs u, edt_ics_prof eip WHERE eip.prof_ics='$prof_ics' AND eip.login=u.login;";
	//$html.="$sql<br />";
	$res=mysqli_query($GLOBALS["mysqli"], $sql);
	if(mysqli_num_rows($res)>0) {
		$lig=mysqli_fetch_object($res);
		$retour=$lig->nom;
	}

	return $retour;
}

function get_tab_prof_gepi_pour_prof_ics($prof_ics) {
	global $debug_edt;

	$retour['nom']=$prof_ics;
	$retour['designation']=$prof_ics;

	$sql="SELECT u.civilite, u.nom, u.prenom FROM utilisateurs u, edt_ics_prof eip WHERE eip.prof_ics='$prof_ics' AND eip.login_prof=u.login;";
	//$html.="$sql<br />";
	$res=mysqli_query($GLOBALS["mysqli"], $sql);
	if(mysqli_num_rows($res)>0) {
		$lig=mysqli_fetch_object($res);
		$retour['nom']=$lig->nom;
		$retour['prenom']=$lig->prenom;
		$retour['civilite']=$lig->civilite;
		$retour['designation']=casse_mot($lig->civilite." ".$lig->nom." ".mb_substr($lig->prenom,0,1),'maj');
	}

	if($debug_edt=="y") {
		echo "<pre>";
		print_r($retour);
		echo "</pre>";
	}

	return $retour;
}

function get_couleur_edt_matiere($matiere) {
	global $tab_couleur_edt;
	//$retour="white";
	$retour="azure";

	$sql="SELECT valeur FROM edt_setting WHERE reglage='M_".$matiere."';";
	//echo "$sql<br />";
	$res=mysqli_query($GLOBALS["mysqli"], $sql);
	if(mysqli_num_rows($res)>0) {
		$lig=mysqli_fetch_object($res);
		//echo "\$tab_couleur_edt[".$lig->valeur."]=".$tab_couleur_edt[$lig->valeur]."<br />";
		if(isset($tab_couleur_edt[$lig->valeur])) {
			$retour=$tab_couleur_edt[$lig->valeur];
		}
	}
	return $retour;
}

// 20160919
$tab_couleur_edt_grp=array();
function get_couleur_edt_prof($id_groupe) {
	global $tab_couleur_edt;
	global $tab_couleur_edt_grp;
	//$retour="white";
	$retour="azure";

	if(!isset($tab_couleur_edt_grp[$id_groupe])) {
		$couleur_tmp=getPref($_SESSION['login'], "edt2_couleur_grp_".$id_groupe, "");
		if($couleur_tmp!="") {
			$retour=$couleur_tmp;
		}
		else {
			if(isset($tab_couleur_edt[count($tab_couleur_edt_grp)+1])) {
				$tab_couleur_edt_grp[$id_groupe]=$tab_couleur_edt[count($tab_couleur_edt_grp)+1];
				$retour=$tab_couleur_edt_grp[$id_groupe];
			}
		}
	}
	else {
		$retour=$tab_couleur_edt_grp[$id_groupe];
	}

	return $retour;
}

function check_pas_de_collision($x1, $y1, $x2, $y2) {
	global $tab_coord_prises;
	$retour=true;

	// A VOIR : Si on a des problèmes de collisions mal identifiées,
	//          on peut peut-être jouer sur $marge_secu pour réduire les dimensions à droite et en bas lors des tests.
	//          Si il se produit alors un chevauchement, il ne sera que léger,
	//          limité à $marge_secu
	for($loop=0;$loop<count($tab_coord_prises);$loop++) {
		$tab=explode(",", $tab_coord_prises[$loop]);
		$x1b=$tab[0];
		$y1b=$tab[1];
		$x2b=$tab[2];
		$y2b=$tab[3];

		if(($x1<=$x1b)&&($x2>$x1b)) {
			if(($y1b>=$y1)&&($y1b<=$y2)) {
				$retour=false;
			}
			elseif(($y1>=$y1b)&&($y1<=$y2b)) {
				$retour=false;
			}
		}
		elseif(($x1>=$x1b)&&($x1<$x2b)) {
			if(($y1b>=$y1)&&($y1b<=$y2)) {
				$retour=false;
			}
			elseif(($y1>=$y1b)&&($y1<=$y2b)) {
				$retour=false;
			}
		}
	}

	return $retour;
}

function acces_depos_message() {
	// A REVOIR EN GERANT PLUS FINEMENT LES DROITS (pouvoir uploader un fichier ICAL sans pouvoir déposer de message)
	// On se contente pour le moment du filtrage préalable EdtIcalUploadScolarite, EdtIcalUploadCpe
	if($_SESSION['statut']=="administrateur") {
		return true;
	}
	elseif($_SESSION['statut']=="scolarite") {
		return true;
	}
	elseif($_SESSION['statut']=="cpe") {
		return true;
	}
	else {
		return false;
	}
}

//function affiche_edt_ics($num_semaine_annee, $type_edt, $id_classe="", $login_prof="") {
function affiche_edt_ics($num_semaine_annee, $type_edt, $id_classe="", $login_prof="", $largeur_edt=800, $x0=50, $y0=60, $hauteur_une_heure=60, $hauteur_titre=10, $hauteur_entete=40) {
	global $debug_edt, $opacity_couleur;
	global $mode_infobulle;
	global $tab_coord_prises;

	$tab_coord_prises=array();

	if($opacity_couleur=="") {
		$opacity_couleur=0.5;
	}

	$html="";

	$tab=explode("|", $num_semaine_annee);

	$num_semaine=$tab[0];
	$annee=$tab[1];

	$_SESSION['edt_ics_num_semaine']=$num_semaine;
	$_SESSION['edt_ics_annee']=$annee;

	$jours=get_days_from_week_number($num_semaine, $annee);

	if($type_edt=="classe") {
		$_SESSION['edt_ics_id_classe']=$id_classe;

		$titre_edt="EDT de ".get_nom_classe($id_classe);
		$param_lien_edt="type_edt=classe&amp;id_classe=$id_classe";

		$sql_lien_semaine_precedente="SELECT num_semaine FROM edt_ics WHERE id_classe='$id_classe' AND num_semaine!='$num_semaine' AND date_debut<'".strftime("%Y-%m-%d %H:%M:%S", $jours['num_jour'][1]['timestamp'])."' ORDER BY date_debut DESC LIMIT 1;";

		$sql_lien_semaine_suivante="SELECT num_semaine FROM edt_ics WHERE id_classe='$id_classe' AND num_semaine!='$num_semaine' AND date_debut>'".strftime("%Y-%m-%d %H:%M:%S", $jours['num_jour'][7]['timestamp'])."' ORDER BY date_debut ASC LIMIT 1";

		$sql_cours_de_la_semaine="SELECT * FROM edt_ics WHERE id_classe='$id_classe' AND num_semaine='$num_semaine' ORDER BY date_debut;";
	}
	elseif($type_edt=="prof") {
		$_SESSION['edt_ics_login_prof']=$login_prof;

		// Désignation du prof à changer:
		$titre_edt="EDT de ".civ_nom_prenom($login_prof);
		$param_lien_edt="type_edt=prof&amp;login_prof=$login_prof";

		$sql_lien_semaine_precedente="SELECT num_semaine FROM edt_ics ei, edt_ics_prof eip WHERE ei.prof_ics=eip.prof_ics AND eip.login_prof='$login_prof' AND num_semaine!='$num_semaine' AND date_debut<'".strftime("%Y-%m-%d %H:%M:%S", $jours['num_jour'][1]['timestamp'])."' ORDER BY date_debut DESC LIMIT 1;";

		$sql_lien_semaine_suivante="SELECT num_semaine FROM edt_ics ei, edt_ics_prof eip WHERE ei.prof_ics=eip.prof_ics AND eip.login_prof='$login_prof' AND num_semaine!='$num_semaine' AND date_debut>'".strftime("%Y-%m-%d %H:%M:%S", $jours['num_jour'][7]['timestamp'])."' ORDER BY date_debut ASC LIMIT 1";

		$sql_cours_de_la_semaine="SELECT ei.* FROM edt_ics ei, edt_ics_prof eip WHERE ei.prof_ics=eip.prof_ics AND eip.login_prof='$login_prof' AND num_semaine='$num_semaine' ORDER BY date_debut;";
	}

	//$html.="<h2>".$titre_edt."</h2>";

	$lien_semaine_prec="";
	//$html.="$sql_lien_semaine_precedente<br />";
	$res=mysqli_query($GLOBALS["mysqli"], $sql_lien_semaine_precedente);
	if(mysqli_num_rows($res)>0) {
		$lig=mysqli_fetch_object($res);
		$annee_b=$annee;
		if($lig->num_semaine>$num_semaine) {
			$annee_b=$annee-1;
		}
		$lien_semaine_prec="<a href='".$_SERVER['PHP_SELF']."?$param_lien_edt&amp;num_semaine_annee=".$lig->num_semaine."|$annee_b&amp;mode=afficher_edt' title=\"Semaine précédente\"";
		if($mode_infobulle=="y") {
			$lien_semaine_prec.=" onclick=\"edt_semaine_suivante('$lig->num_semaine', '$annee_b', '$id_classe', '$login_prof'); return false;\"";
		}
		$lien_semaine_prec.="><img src='../images/arrow_left.png' class='icone16' alt='Semaine précédente'></a> - ";
	}

	$lien_semaine_suiv="";
	//$html.="$sql_lien_semaine_suivante<br />";
	$res=mysqli_query($GLOBALS["mysqli"], $sql_lien_semaine_suivante);
	if(mysqli_num_rows($res)>0) {
		$lig=mysqli_fetch_object($res);
		$annee_b=$annee;
		if($lig->num_semaine<$num_semaine) {
			$annee_b=$annee+1;
		}
		$lien_semaine_suiv=" - <a href='".$_SERVER['PHP_SELF']."?$param_lien_edt&amp;num_semaine_annee=".$lig->num_semaine."|$annee_b&amp;mode=afficher_edt' title=\"Semaine suivante\"";
		if($mode_infobulle=="y") {
			$lien_semaine_suiv.=" onclick=\"edt_semaine_suivante('$lig->num_semaine', '$annee_b', '$id_classe', '$login_prof'); return false;\"";
		}
		$lien_semaine_suiv.="><img src='../images/arrow_right.png' class='icone16' alt='Semaine suivante'></a>";
	}

	//$html.="<h3>".$lien_semaine_prec."Semaine $num_semaine (".$jours['num_jour'][1]['jjmmaaaa']." - ".$jours['num_jour'][7]['jjmmaaaa'].")".$lien_semaine_suiv."</h3>";

	//$html.="$sql_cours_de_la_semaine<br />";
	$res_cours_de_la_semaine=mysqli_query($GLOBALS["mysqli"], $sql_cours_de_la_semaine);
	if(mysqli_num_rows($res_cours_de_la_semaine)==0) {
		$html.="<p>Aucun EDT n'est enregistré.</p>";
	}
	else {
		$chaine_alea=remplace_accents(rand(1, 1000000)."_".microtime(),"all");

		$html.="<script type='text/javascript'>
	//alert('plop');
	function edt_semaine_suivante(num_semaine, annee, id_classe, login_prof) {
		//alert('plop');
		new Ajax.Updater($('div_edt_".$chaine_alea."'),'../edt/index.php?num_semaine_annee='+num_semaine+'|'+annee+'&id_classe='+id_classe+'&login_prof='+login_prof+'&type_edt=$type_edt&mode=afficher_edt_js',{method: 'get'});
	}
</script>

<div id='div_edt_".$chaine_alea."'>";

		if($debug_edt=="y") {
			echo "<pre>";
			print_r($jours);
			echo "</pre>";
		}

		// L'exclusion ne fonctionne que si toutes les matières des ICS ont bien été associées à des matières Gepi.
		// Cette exclusion ne permet pas pour autant de distinguer à quel groupe de telle matière correspond une entrée dans edt_ics
		$tab_matieres_eleve=array();
		$tab_id_cours_exclu=array();
		if($_SESSION['statut']=='eleve') {
			$sql_matieres_eleve="SELECT DISTINCT jgm.id_matiere FROM j_eleves_groupes jeg, j_groupes_matieres jgm WHERE jeg.id_groupe=jgm.id_groupe AND jeg.login='".$_SESSION['login']."';";
			//$html.="$sql_matieres_eleve<br />";
			$res_matieres_eleve=mysqli_query($GLOBALS["mysqli"], $sql_matieres_eleve);
			if(mysqli_num_rows($res_matieres_eleve)>0) {
				while($lig_matieres_eleve=mysqli_fetch_object($res_matieres_eleve)) {
					$tab_matieres_eleve[]=$lig_matieres_eleve->id_matiere;
				}
			}
		}

		// Récupération de la liste des jours d'ouverture de l'établissement
		$tab_jour=get_tab_jour_ouverture_etab();

		/*
		// 60px pour 1h
		$hauteur_une_heure=60;

		$hauteur_titre=10;
		$hauteur_entete=40;

		$x0=500;
		$y0=200;

		$largeur_edt=800;
		*/
		$largeur_jour=$largeur_edt/count($tab_jour);

		// Titre au-dessus de l'EDT
		$html.="<div style='position:absolute; top:".($y0-$hauteur_entete-$hauteur_titre)."px; left:".$x0."px; width:".$largeur_edt."px; height:".$hauteur_titre."px; text-align:center;'><h3>".$lien_semaine_prec.$titre_edt." - "."Semaine $num_semaine (".$jours['num_jour'][1]['jjmmaaaa']." - ".$jours['num_jour'][7]['jjmmaaaa'].")".$lien_semaine_suiv."</h3></div>";


		//=================================================================================
		$premiere_heure=8;
		//$derniere_heure=16.5;
		$derniere_heure=17;

		// Récupérer les horaires de début et de fin de journée dans le module EDT
		$sql="SELECT * FROM edt_creneaux ORDER BY heuredebut_definie_periode ASC LIMIT 1;";
		$res_premiere_heure=mysqli_query($GLOBALS["mysqli"], $sql);
		if(mysqli_num_rows($res_premiere_heure)>0) {
			$lig_premiere_heure=mysqli_fetch_object($res_premiere_heure);
			$tmp_tab=explode(":", $lig_premiere_heure->heuredebut_definie_periode);
			$premiere_heure=$tmp_tab[0]+$tmp_tab[1]/60;
		}

		$sql="SELECT * FROM edt_creneaux ORDER BY heuredebut_definie_periode DESC LIMIT 1;";
		$res_derniere_heure=mysqli_query($GLOBALS["mysqli"], $sql);
		if(mysqli_num_rows($res_derniere_heure)>0) {
			$lig_derniere_heure=mysqli_fetch_object($res_derniere_heure);
			$tmp_tab=explode(":", $lig_derniere_heure->heurefin_definie_periode);
			$derniere_heure=$tmp_tab[0]+$tmp_tab[1]/60;
		}
		//=================================================================================


		$tmp_tab=explode(".", $premiere_heure);
		$heure_debut_jour=$tmp_tab[0];
		$min_debut_jour=0;
		if(isset($tmp_tab[1])) {
			$min_debut_jour=floor($tmp_tab[1]*60);
		}
		$sec_debut_jour=0;

		$hauteur_jour=($derniere_heure-$premiere_heure)*$hauteur_une_heure;

		$y_max=$y0+$hauteur_entete+$hauteur_jour;

		$x_jour=array();
		for($i=0;$i<count($tab_jour);$i++) {
			$x_courant=$x0+$i*$largeur_jour;
			$x_jour[$i]=$x_courant;

			// Abscisse du jour au-dessus de l'entête (pour debug)
			if($debug_edt=="y") {
				$html.="<div style='position:absolute; top:".($y0-$hauteur_entete-2)."px; left:".$x_courant."px; width:".$largeur_jour."px; height:".$hauteur_entete."px;'>".$x_jour[$i]."</div>";
			}

			// Nom du jour en entête
			$html.="<div style='position:absolute; top:".$y0."px; left:".$x_courant."px; width:".$largeur_jour."px; height:".$hauteur_entete."px; border:1px solid black; text-align:center; background-color:silver;' title=\"".$jours['num_jour'][$i+1]['jjmmaaaa']."\">".ucfirst($tab_jour[$i])."<br /><span style='font-size:x-small'>".$jours['num_jour'][$i+1]['jjmmaaaa']."</span></div>";

			// Bande verticale de la journée
			$y_courant=$y0+$hauteur_entete;
			$html.="<div style='position:absolute; top:".$y_courant."px; left:".$x_courant."px; width:".$largeur_jour."px; height:".$hauteur_jour."px; border:1px solid black; background-color:white;z-index:1;'></div>";
			// Pour avoir les traits verticaux
			$html.="<div style='position:absolute; top:".$y_courant."px; left:".$x_courant."px; width:".$largeur_jour."px; height:".$hauteur_jour."px; border:1px solid black;z-index:4;'></div>";
		}

		/*
		$cpt=0;
		$y_courant=$y0;
		while($y_courant<$y_max) {
			$y_courant=$y0+$hauteur_entete+$cpt*$hauteur_une_heure;
			// Lignes horizontales des heures:
			//$html.="<div style='position:absolute; top:".$y_courant."px; left:".$x0."px; width:".$largeur_edt."px; height:".$hauteur_une_heure."px; border:1px solid grey; z-index:2;'></div>";

			// Affichage des heures sur la gauche:
			//$html.="<div style='position:absolute; top:".($y_courant)."px; left:".($x0-35)."px; width:20px; height:".$hauteur_une_heure."px; text-align:right;'>".($premiere_heure+$cpt)."H</div>";
			// Affichage des heures sur la droite:
			$html.="<div style='position:absolute; top:".($y_courant)."px; left:".($x0+$largeur_edt)."px; width:30px; height:".$hauteur_une_heure."px; text-align:center;'>".($premiere_heure+$cpt)."H</div>";
			// Si la première heure tombe sur une demi-heure, on va avoir des 8.5H, 9.5H,...

			$cpt++;
		}
		*/

		$heure_ronde_debut_jour=floor($premiere_heure);
		$heure_courante=$heure_ronde_debut_jour;
		$heure_ronde_debut_jour=floor($derniere_heure);
		$hauteur_texte=12; // A la louche
		$hauteur_demi_texte=ceil($hauteur_texte/2);
		while($heure_courante<$heure_ronde_debut_jour) {
			$y_courant=$y0+$hauteur_entete+($heure_courante-$premiere_heure)*$hauteur_une_heure-$hauteur_demi_texte;

			$html.="<div style='position:absolute; top:".($y_courant)."px; left:".($x0+$largeur_edt)."px; width:30px; height:".$hauteur_une_heure."px; text-align:center;'>".$heure_courante."H</div>";

			$heure_courante++;
		}

		// CRENEAUX
		$sql="SELECT * FROM edt_creneaux ORDER BY heuredebut_definie_periode;";
		$res_creneaux=mysqli_query($GLOBALS["mysqli"], $sql);
		if(mysqli_num_rows($res_creneaux)>0) {
			while($lig=mysqli_fetch_object($res_creneaux)) {
				$tab_h=explode(":", $lig->heuredebut_definie_periode);
				$c=$tab_h[0]+$tab_h[1]/60-$premiere_heure;
				$y1_courant=$y0+$hauteur_entete+round($c*$hauteur_une_heure);

				$tab_h=explode(":", $lig->heurefin_definie_periode);
				$c=$tab_h[0]+$tab_h[1]/60-$premiere_heure;
				$y2_courant=$y0+$hauteur_entete+round($c*$hauteur_une_heure);

				$hauteur_courante=$y2_courant-$y1_courant;

				$style_fond_creneau="background-color:white;";
				if($lig->type_creneaux=="pause") {
					$style_fond_creneau="background-color:silver;";
				}
				elseif($lig->type_creneaux=="repas") {
					$style_fond_creneau="background-color:grey;";
				}

				// Nom du créneau sur la gauche
				$html.="<div style='position:absolute; top:".($y1_courant)."px; left:".($x0-32)."px; width:30px; height:".$hauteur_courante."px; text-align:center; border:1px solid black; vertical-align:middle;".$style_fond_creneau."' title=\"Créneau $lig->nom_definie_periode\nDe $lig->heuredebut_definie_periode à $lig->heurefin_definie_periode.\"><div style='position:relative; width:2em; height:1em;'>".$lig->nom_definie_periode."</div></div>";

				// Bandes horizontales du créneau
				$html.="<div style='position:absolute; top:".($y1_courant)."px; left:".$x0."px; width:".$largeur_edt."px; height:".$hauteur_courante."px; border:1px solid silver; z-index:2;".$style_fond_creneau."'></div>";

				// Debug
				if($debug_edt=="y") {
					$html.="<div style='position:absolute; top:".($y1_courant)."px; left:".($x0+$largeur_edt+30)."px; width:".$largeur_edt."px; height:".$hauteur_courante."px; color:red; z-index:2;'>$y1_courant</div>";
				}

			}
		}

		// Pour avoir une marge en bas sous l'EDT:
		$html.="<div style='position:absolute; top:".($y0+$hauteur_entete+$hauteur_jour+10)."px; left:".$x0."px; height:1em; width:1em;'>&nbsp;</div>";

		$marge_secu=6;

		$tab_cours=array();
		$tab_nom_classe=array();

		while($lig=mysqli_fetch_object($res_cours_de_la_semaine)) {
			if($debug_edt=="y") {
				echo "<pre style='border:1px solid red; margin:0.5em;'>";
				print_r($lig);
				echo "</pre>";
			}

			$ts_debut=mysql_date_to_unix_timestamp($lig->date_debut);
			$horaire_debut=strftime("%H:%M", $ts_debut);
			$ts_fin=mysql_date_to_unix_timestamp($lig->date_fin);
			$horaire_fin=strftime("%H:%M", $ts_fin);

			$num_jour=id_j_semaine($ts_debut)-1;

			$jour_debut_jour=strftime("%d", $ts_debut);
			$mois_debut_jour=strftime("%m", $ts_debut);
			$annee_debut_jour=strftime("%Y", $ts_debut);
			$ts_debut_jour=mktime($heure_debut_jour,$min_debut_jour,$sec_debut_jour,$mois_debut_jour,$jour_debut_jour,$annee_debut_jour);

			$duree_en_min=floor(($ts_fin-$ts_debut)/60);
			$hauteur_courante=floor($duree_en_min*$hauteur_une_heure/60);
			//$hauteur_courante=floor($duree_en_min*$hauteur_une_heure/60)-ceil($marge_secu/2);

			//$duree_depuis_debut_journee=floor(($ts_debut-$ts_debut_jour)/3600);
			$duree_depuis_debut_journee=floor(10*($ts_debut-$ts_debut_jour)/3600)/10;
			//$y_courant=$y0+$hauteur_entete+$duree_depuis_debut_journee*$hauteur_une_heure;
			$y_courant=$y0+$hauteur_entete+$duree_depuis_debut_journee*$hauteur_une_heure+ceil($marge_secu/2);

			if($debug_edt=="y") {
				$html.="\$jour_debut_jour=$jour_debut_jour<br />";
				$html.="\$ts_debut_jour=$ts_debut_jour<br />";
				$html.="\$ts_debut=$ts_debut<br />";
				$html.="\$duree_depuis_debut_journee=$duree_depuis_debut_journee<br />";
				$html.="y_courant=$y_courant<br />";
			}

			$cpt_courant=0;
			if(isset($tab_cours[$num_jour]['y'][$y_courant])) {
				$cpt_courant=count($tab_cours[$num_jour]['y'][$y_courant]);
			}
			$tab_cours[$num_jour]['y'][$y_courant][$cpt_courant]['hauteur']=$hauteur_courante;
			// A FAIRE : Stocker dans des tableaux les retours de fonction qui suivent pour ne pas faire plusieurs fois les mêmes appels
			$tab_cours[$num_jour]['y'][$y_courant][$cpt_courant]['matiere']=get_tab_matiere_gepi_pour_matiere_ics($lig->matiere_ics);
			$tab_cours[$num_jour]['y'][$y_courant][$cpt_courant]['prof']=get_tab_prof_gepi_pour_prof_ics($lig->prof_ics);
			$tab_cours[$num_jour]['y'][$y_courant][$cpt_courant]['salle']=$lig->salle_ics;
			$tab_cours[$num_jour]['y'][$y_courant][$cpt_courant]['id_cours']=$lig->id;
			$tab_cours[$num_jour]['y'][$y_courant][$cpt_courant]['id_classe']=$lig->id_classe;
			if(!array_key_exists($lig->id_classe, $tab_nom_classe)) {
				$tab_nom_classe[$lig->id_classe]=get_nom_classe($lig->id_classe);
			}
			$tab_cours[$num_jour]['y'][$y_courant][$cpt_courant]['classe']=$tab_nom_classe[$lig->id_classe];
			$tab_cours[$num_jour]['y'][$y_courant][$cpt_courant]['horaire_debut']=$horaire_debut;
			$tab_cours[$num_jour]['y'][$y_courant][$cpt_courant]['horaire_fin']=$horaire_fin;

			// Stockage des identifiants de cours que n'ont pas les élèves faute de suivre la matière
			if(($_SESSION['statut']=='eleve')&&
			($tab_cours[$num_jour]['y'][$y_courant][$cpt_courant]['matiere']['association_faite']=="y")&&
			(!in_array($tab_cours[$num_jour]['y'][$y_courant][$cpt_courant]['matiere']['matiere'], $tab_matieres_eleve))) {
				$tab_id_cours_exclu[]=$tab_cours[$num_jour]['y'][$y_courant][$cpt_courant]['id_cours'];
			}

		}

		if($debug_edt=="y") {
			echo "\$tab_cours<pre>";
			print_r($tab_cours);
			echo "</pre>";
		}

		if($type_edt=="classe") {

			$tab_collisions=array();

			$tab_collisions2=array();

			foreach($tab_cours as $num_jour => $tab) {

				/*
				if($num_jour==0) {
					echo "\$tab_cours[$num_jour]<pre>";
					print_r($tab_cours[$num_jour]);
					echo "</pre>";
				}
				*/

				foreach($tab['y'] as $y_courant => $tab2) {
					for($loop=0;$loop<count($tab2);$loop++) {
						$hauteur_courante=$tab2[$loop]['hauteur'];
						$y_courant_fin=$y_courant+$hauteur_courante;

						$id_cours_courant=$tab2[$loop]['id_cours'];

						foreach($tab_cours[$num_jour]['y'] as $y3=> $tab3_cours) {
							/*
							echo "y3=$y3<pre>";
							print_r($tab3_cours);
							echo "</pre>";
							*/

							for($loop3=0;$loop3<count($tab3_cours);$loop3++) {
								if($tab3_cours[$loop3]['id_cours']!=$id_cours_courant) {
									$y_test_debut=$y3;
									$y_test_fin=$y3+$tab3_cours[$loop3]['hauteur'];
									$id_cours_test=$tab3_cours[$loop3]['id_cours'];

									if(($y_test_debut>=$y_courant)&&($y_test_debut<$y_courant_fin)) {
										if((!isset($tab_collisions[$id_cours_courant]))||(!in_array($id_cours_test, $tab_collisions[$id_cours_courant]))) {
											$tab_collisions[$id_cours_courant][]=$id_cours_test;
										}
									}
									elseif(($y_courant>=$y_test_debut)&&($y_courant<$y_test_fin)) {
										if((!isset($tab_collisions[$id_cours_courant]))||(!in_array($id_cours_test, $tab_collisions[$id_cours_courant]))) {
											$tab_collisions[$id_cours_courant][]=$id_cours_test;
										}
									}

								}
							}

						}
					}
				}
			}

			/*
			//$tab_aff=array(7352,7367,7359);
			$tab_aff=array(7396,7402,7394);
			for($loop=0;$loop<count($tab_aff);$loop++) {
				echo "tab_collisions[".$tab_aff[$loop]."]=<pre>";
				print_r($tab_collisions[$tab_aff[$loop]]);
				echo "</pre>";
			}

			echo "<pre>";
			print_r($tab_collisions);
			echo "</pre>";
			*/

			$tab_coord_prises=array();
			foreach($tab_cours as $num_jour => $tab) {
				foreach($tab['y'] as $y_courant => $tab2) {
					for($loop=0;$loop<count($tab2);$loop++) {
						//$hauteur_courante=$tab2[$loop]['hauteur'];
						$hauteur_courante=$tab2[$loop]['hauteur']-$marge_secu;

						$title="".$tab2[$loop]['matiere']['nom_complet'];
						if($tab2[$loop]['prof']['designation']!="") {
							$title.=" avec ".$tab2[$loop]['prof']['designation'];
						}
						if($tab2[$loop]['salle']!="") {
							$title.=" en salle ".$tab2[$loop]['salle'];
						}
						$title.="\nDe ".$tab2[$loop]['horaire_debut']." à ".$tab2[$loop]['horaire_fin'].".";

						$id_cours_courant=$tab2[$loop]['id_cours'];

						$x_courant=$x_jour[$num_jour]+$marge_secu;
						$largeur_courante=$largeur_jour-2*$marge_secu;
						$text_color="";
						$font_size="";

						$style_font_size1=" style='font-size:normal;'";
						$style_font_size2=" style='font-size:x-small;'";
						$contenu_courant_ajout="";
						if(isset($tab_collisions[$id_cours_courant])) {

							$style_font_size1=" style='font-size:x-small;'";
							$style_font_size2=" style='font-size:xx-small;'";

							if($debug_edt=="y") {
								$contenu_courant_ajout.="<br />nb_col=".count($tab_collisions[$id_cours_courant]);
							}

							// Compter les collisions effectives
							$nb=count($tab_collisions[$id_cours_courant]);
							foreach($tab_collisions[$id_cours_courant] as $id_cours_test) {
								$nb=min($nb,count($tab_collisions[$id_cours_test]));
							}
							if($debug_edt=="y") {
								$contenu_courant_ajout.="<br />nb_reel=".$nb;
							}
							// Largeur du div de ce cours
							//$largeur_courante=floor($largeur_jour/($nb+1))-2*$marge_secu;
							// On donne au moins 1px de large... par sécurité
							$largeur_courante=max(floor($largeur_jour/($nb+1))-2*$marge_secu,1);


							//$font_size="font-size:x-small;";
							//$font_size="font-size:smaller;";
							$tmp_tab=array();
							$tmp_tab[]=$id_cours_courant;
							//foreach($tab_collisions as $tmp_current_id_cours => $tmp_current_id_cours_collision) {
							foreach($tab_collisions[$id_cours_courant] as $tmp_current_id_cours_collision) {
								$tmp_tab[]=$tmp_current_id_cours_collision;
							}
							sort($tmp_tab);

							$chaine="";
							for($loop2=0;$loop2<count($tmp_tab);$loop2++) {
								if($chaine!="") {
									$chaine.="|";
								}
								$chaine.=$tmp_tab[$loop2];
							}

							while($x_courant<$x_jour[$num_jour]+$largeur_jour) {
								if(check_pas_de_collision($x_courant,$y_courant,$x_courant+$largeur_courante,$y_courant+$hauteur_courante)) {
									$text_color="";
									break;
								}
								else {
									//$x_courant+=$largeur_courante;
									//$x_courant+=floor($largeur_jour/($nb+1))+$marge_secu;
									$x_courant+=floor($largeur_jour/($nb+1));
									$text_color="color:red;";
								}
							}

							if($text_color!="") {
								$x_courant=$x_jour[$num_jour];
							}

							$tab_coord_prises[]=$x_courant.",".$y_courant.",".($x_courant+$largeur_courante).",".($y_courant+$hauteur_courante);
							if($debug_edt=="y") {
								$title.="\nCoordonnées : ".$x_courant.",".$y_courant.",".($x_courant+$largeur_courante).",".($y_courant+$hauteur_courante);
							}
						}

						$contenu_courant="<span title=\"$title\"$style_font_size1>".$tab2[$loop]['matiere']['matiere']."</span>";

						// Ne pas inclure ce qui suit pour l'emploi du temps du prof
						if($type_edt!="prof") {
							$contenu_courant.="<br /><span$style_font_size2 title=\"".$tab2[$loop]['prof']['designation']."\">".$tab2[$loop]['prof']['nom']."</span>";
						}
						else {
							$contenu_courant.="<br /><span$style_font_size2 title=\"".$tab2[$loop]['classe']."\">".$tab2[$loop]['classe']."</span>";
						}
						// Ne pas inclure ce qui suit pour l'emploi du temps d'une salle
						$contenu_courant.="<br /><span$style_font_size2 title=\"Salle ".$tab2[$loop]['salle']."\">".$tab2[$loop]['salle']."</span>";

						if($debug_edt=="y") {
							$contenu_courant.="<br />id_cours=".$id_cours_courant;
						}

						$contenu_courant.=$contenu_courant_ajout;

						// Fond blanc pour masquer les lignes d'heures
						$html.="<div id='div_fond_masque_cours_".$tab2[$loop]['id_cours']."' style='position:absolute; top:".$y_courant."px; left:".$x_courant."px; width:".$largeur_courante."px; height:".$hauteur_courante."px; background-color:white; z-index:18; '></div>";

						// Cadre de couleur avec une opacité réglable
						if(!isset($tab_couleur_matiere[$tab2[$loop]['matiere']['matiere']])) {
							$tab_couleur_matiere[$tab2[$loop]['matiere']['matiere']]=get_couleur_edt_matiere($tab2[$loop]['matiere']['matiere']);
						}
						$couleur_courante=$tab_couleur_matiere[$tab2[$loop]['matiere']['matiere']];
						$html.="<div id='div_fond_couleur_cours_".$tab2[$loop]['id_cours']."' style='position:absolute; top:".$y_courant."px; left:".$x_courant."px; width:".$largeur_courante."px; height:".$hauteur_courante."px; border:1px solid black; background-color:".$couleur_courante."; opacity:$opacity_couleur; z-index:19; text-align:center;".$text_color.$font_size."' title='$title'></div>";

						// Cadre du contenu de la cellule
						$html.="<div id='div_texte_cours_".$tab2[$loop]['id_cours']."' style='position:absolute; top:".$y_courant."px; left:".$x_courant."px; width:".$largeur_courante."px; height:".$hauteur_courante."px; border:1px solid black; z-index:20; text-align:center; overflow:hidden; ".$text_color.$font_size."' title='$title'>".$contenu_courant."</div>";
					}

				}
			}
		}
		else {
			// EDT PROF

			foreach($tab_cours as $num_jour => $tab) {
				foreach($tab['y'] as $y_courant => $tab2) {
					/*
					if($num_jour==2) {
						echo "\$y_courant=$y_courant \$tab2<pre>";
						print_r($tab2);
						echo "</pre>";
					}
					*/
					$liste_classe="";
					for($loop=0;$loop<count($tab2);$loop++) {
						if($liste_classe!="") {
							$liste_classe.=", ";
						}
						$liste_classe.=$tab2[$loop]['classe'];
					}

					//for($loop=0;$loop<count($tab2);$loop++) {
					$loop=0;

						//$hauteur_courante=$tab2[$loop]['hauteur'];
						$hauteur_courante=$tab2[$loop]['hauteur']-$marge_secu;

						$title="Enseignement de ".$tab2[$loop]['matiere']['nom_complet']."\n";
						if($liste_classe!="") {
							if(count($tab2)>1) {
								$title.="Classe(s) : ".$liste_classe."\n";
							}
							else {
								$title.="Classe : ".$liste_classe."\n";
							}
						}
						if($tab2[$loop]['prof']['designation']!="") {
							$title.="Professeur : ".$tab2[$loop]['prof']['designation']."\n";
						}
						if($tab2[$loop]['salle']!="") {
							$title.="En salle ".$tab2[$loop]['salle'];
						}
						$title.="\nDe ".$tab2[$loop]['horaire_debut']." à ".$tab2[$loop]['horaire_fin'].".";

						$id_cours_courant=$tab2[$loop]['id_cours'];

						$x_courant=$x_jour[$num_jour]+$marge_secu;
						$largeur_courante=$largeur_jour-2*$marge_secu;
						$text_color="";
						$font_size="";

						$style_font_size1=" style='font-size:normal;'";
						$style_font_size2=" style='font-size:x-small;'";

						$contenu_courant_ajout="";

						$contenu_courant="<span title=\"$title\"$style_font_size1>".$tab2[$loop]['matiere']['matiere']."</span>";

						// Ne pas inclure ce qui suit pour l'emploi du temps du prof
						if($type_edt!="prof") {
							$contenu_courant.="<br /><span$style_font_size2 title=\"".$tab2[$loop]['prof']['designation']."\">".$tab2[$loop]['prof']['nom']."</span>";
						}
						else {
							$contenu_courant.="<br /><span$style_font_size2 title=\"".$liste_classe."\">".$liste_classe."</span>";
						}
						// Ne pas inclure ce qui suit pour l'emploi du temps d'une salle
						$contenu_courant.="<br /><span$style_font_size2 title=\"Salle ".$tab2[$loop]['salle']."\">".$tab2[$loop]['salle']."</span>";

						if($debug_edt=="y") {
							$contenu_courant.="<br />id_cours=".$id_cours_courant;
						}

						$contenu_courant.=$contenu_courant_ajout;

						// Fond blanc pour masquer les lignes d'heures/créneaux
						$html.="<div style='position:absolute; top:".$y_courant."px; left:".$x_courant."px; width:".$largeur_courante."px; height:".$hauteur_courante."px; background-color:white; z-index:18; '></div>";
						// Cadre de couleur avec une opacité réglable
						if(!isset($tab_couleur_matiere[$tab2[$loop]['matiere']['matiere']])) {
							$tab_couleur_matiere[$tab2[$loop]['matiere']['matiere']]=get_couleur_edt_matiere($tab2[$loop]['matiere']['matiere']);
						}
						$couleur_courante=$tab_couleur_matiere[$tab2[$loop]['matiere']['matiere']];
						$html.="<div style='position:absolute; top:".$y_courant."px; left:".$x_courant."px; width:".$largeur_courante."px; height:".$hauteur_courante."px; border:1px solid black; background-color:".$couleur_courante."; opacity:$opacity_couleur; z-index:19; text-align:center;".$text_color.$font_size."' title='$title'></div>";
						// Cadre du contenu de la cellule
						$html.="<div style='position:absolute; top:".$y_courant."px; left:".$x_courant."px; width:".$largeur_courante."px; height:".$hauteur_courante."px; border:1px solid black; z-index:20; text-align:center; overflow:hidden; ".$text_color.$font_size."' title='$title'>".$contenu_courant."</div>";
					//}

				}
			}


		}

		if(($_SESSION['statut']=='eleve')&&(count($tab_id_cours_exclu)>0)) {
			$html.="<div id='div_affichage_masquage' title=\"Afficher/masquer les cours dans les matières
que vous ne suivez pas.

NOTE: Cela ne permet pas de masquer les groupes
      ne vous concernant pas dans une matière
      que vous suivez.\" style='position:absolute; top:".$y0."px; left:".($x0-20)."px; width:16px; display:none;'>
		<a href='javascript:masquer_cours_matieres_non_suivies()' id='lien_masquer_cours_matieres_non_suivies'><img src='../images/icons/visible.png' class='icone16' /></a>
		<a href='javascript:afficher_cours_matieres_non_suivies()' id='lien_afficher_cours_matieres_non_suivies'><img src='../images/icons/invisible.png' class='icone16' /></a>
	</div>

	<script type='text/javascript'>
		document.getElementById('div_affichage_masquage').style.display='';
		document.getElementById('lien_afficher_cours_matieres_non_suivies').style.display='none';

		function masquer_cours_matieres_non_suivies() {
			document.getElementById('lien_afficher_cours_matieres_non_suivies').style.display='';
			document.getElementById('lien_masquer_cours_matieres_non_suivies').style.display='none';";
			for($loop=0;$loop<count($tab_id_cours_exclu);$loop++) {
				$html.="
			document.getElementById('div_fond_masque_cours_".$tab_id_cours_exclu[$loop]."').style.display='none';
			document.getElementById('div_fond_couleur_cours_".$tab_id_cours_exclu[$loop]."').style.display='none';
			document.getElementById('div_texte_cours_".$tab_id_cours_exclu[$loop]."').style.display='none';";
			}
			$html.="
		}

		function afficher_cours_matieres_non_suivies() {
			document.getElementById('lien_afficher_cours_matieres_non_suivies').style.display='none';
			document.getElementById('lien_masquer_cours_matieres_non_suivies').style.display='';";
			for($loop=0;$loop<count($tab_id_cours_exclu);$loop++) {
				$html.="
			document.getElementById('div_fond_masque_cours_".$tab_id_cours_exclu[$loop]."').style.display='';
			document.getElementById('div_fond_couleur_cours_".$tab_id_cours_exclu[$loop]."').style.display='';
			document.getElementById('div_texte_cours_".$tab_id_cours_exclu[$loop]."').style.display='';";
			}
			$html.="
		}
	</script>";
		}

		$html.="<div style='position:absolute; top:".($y0+$hauteur_jour+50)."px; left:".$x0."px; width:".$largeur_edt."px; height:4em; border:1px solid black; z-index:1000; text-align:center; background-color:white;'>L'emploi du temps affiché concerne la semaine indiquée en entête.<br />Les indications de semaines A et B n'y sont pas affichées.<br />Passez à la semaine suivante pour voir les différences.</div>";

		//=================================================================================
		// Ancien essai, affiché plus bas pour faciliter le debug de l'affichage au-dessus
		if($debug_edt=="y") {
			foreach($tab_cours as $num_jour => $tab) {
				foreach($tab['y'] as $y_courant => $tab2) {
					$y_courant=$y_courant+$hauteur_jour+100;

					if(count($tab2)==1) {
						$hauteur_courante=$tab2[0]['hauteur'];
						$contenu_courant=$tab2[0]['matiere']['matiere'];
						//$contenu_courant.="<br />".$hauteur_courante;

						$html.="<div style='position:absolute; top:".$y_courant."px; left:".$x_jour[$num_jour]."px; width:".$largeur_jour."px; height:".$hauteur_courante."px; border:1px solid black; background-color:orange; z-index:1000; opacity:0.5;text-align:center;'>".$contenu_courant."</div>";
					}
					else {
						for($loop=0;$loop<count($tab2);$loop++) {
							$hauteur_courante=$tab2[$loop]['hauteur'];
							$contenu_courant=$tab2[$loop]['matiere']['matiere'];
							//$contenu_courant.="<br />".$hauteur_courante;

							$largeur_courante=floor($largeur_jour/count($tab2));
							$x_courant=$x_jour[$num_jour]+$loop*$largeur_courante;

						$html.="<div style='position:absolute; top:".$y_courant."px; left:".$x_courant."px; width:".$largeur_courante."px; height:".$hauteur_courante."px; border:1px solid black; background-color:orange; z-index:1000; opacity:0.5; font-size:x-small;text-align:center;'>".$contenu_courant."</div>";
						}
					}
				}
			}
		}
		//=================================================================================


		$html.="</div>";
	}

	return $html;
}

function check_ts_vacances($ts, $id_classe) {
	$jour_courant=strftime("%Y-%m-%d", $ts);
	$heure_courante=strftime("%H:%M:%S", $ts);

	$sql="SELECT * FROM edt_calendrier WHERE etabvacances_calendrier!='0' AND 
			(classe_concerne_calendrier LIKE '$id_classe;%' OR classe_concerne_calendrier LIKE '%;$id_classe;%' OR classe_concerne_calendrier LIKE '%;$id_classe') AND 
			jourdebut_calendrier<='".$jour_courant."' AND 
			heuredebut_calendrier<='".$heure_courante."' AND 
			jourfin_calendrier>='".$jour_courant."' AND 
			heurefin_calendrier>='".$heure_courante."';";
	//echo "$sql<br />";
	$res=mysqli_query($GLOBALS["mysqli"], $sql);
	if(mysqli_num_rows($res)>0) {
		return true;
	}
	else {
		return false;
	}
}

function nom_ts_vacances($ts, $id_classe) {
	$jour_courant=strftime("%Y-%m-%d", $ts);
	$heure_courante=strftime("%H:%M:%S", $ts);

	$sql="SELECT * FROM edt_calendrier WHERE etabvacances_calendrier!='0' AND 
			(classe_concerne_calendrier LIKE '$id_classe;%' OR classe_concerne_calendrier LIKE '%;$id_classe;%' OR classe_concerne_calendrier LIKE '%;$id_classe') AND 
			jourdebut_calendrier<='".$jour_courant."' AND 
			heuredebut_calendrier<='".$heure_courante."' AND 
			jourfin_calendrier>='".$jour_courant."' AND 
			heurefin_calendrier>='".$heure_courante."';";
	//echo "$sql<br />";
	$res=mysqli_query($GLOBALS["mysqli"], $sql);
	if(mysqli_num_rows($res)>0) {
		$lig=mysqli_fetch_object($res);
		return $lig->nom_calendrier;
	}
	else {
		return "";
	}
}

function travaux_a_faire_cdt_jour($login_eleve, $id_classe) {
	global $ts_debut_jour, $ts_debut_jour_suivant, $ts_display_date, $display_date, $tab_group_edt, $tab_couleur_matiere, $CDTPeutPointerTravailFait;

	global $tab_etat_travail_fait,
	$image_etat,
	$texte_etat_travail,
	$class_color_fond_notice,
	$gepiPath;

	$html="";

	$url_cdt="";
	$url_cdt_prefixe="";
	if($_SESSION['statut']=="responsable") {
		$url_cdt="../cahier_texte/consultation.php?year=".strftime("%Y", $ts_display_date)."&month=".strftime("%m", $ts_display_date)."&day=".strftime("%d", $ts_display_date)."&login_eleve=".$login_eleve;
		//https://127.0.0.1/steph/gepi_git_trunk/cahier_texte_2/see_all.php?&year=2014&month=11&day=20&login_eleve=XXXXX&id_groupe=Toutes_matieres
		$url_cdt_prefixe="../cahier_texte/consultation.php?login_eleve=".$login_eleve;
	}
	elseif($_SESSION['statut']=="eleve") {
		$url_cdt="../cahier_texte/consultation.php?year=".strftime("%Y", $ts_display_date)."&month=".strftime("%m", $ts_display_date)."&day=".strftime("%d", $ts_display_date)."&login_eleve=".$login_eleve;
		$url_cdt_prefixe="../cahier_texte/consultation.php?login_eleve=".$login_eleve;
	}
	elseif($_SESSION['statut']=="professeur") {
		if (getSettingValue("GepiCahierTexteVersion") == '2') {
			$url_cdt="../cahier_texte_2/see_all.php?year=".strftime("%Y", $ts_display_date)."&month=".strftime("%m", $ts_display_date)."&day=".strftime("%d", $ts_display_date)."&id_classe=".$id_classe."&id_groupe=Toutes_matieres";
		}
		else {
			$url_cdt="../cahier_texte/see_all.php?year=".strftime("%Y", $ts_display_date)."&month=".strftime("%m", $ts_display_date)."&day=".strftime("%d", $ts_display_date)."&id_classe=".$id_classe;
		}
	}
	elseif($_SESSION['statut']=="scolarite") {
		if (getSettingValue("GepiCahierTexteVersion") == '2') {
			$url_cdt="../cahier_texte_2/see_all.php?year=".strftime("%Y", $ts_display_date)."&month=".strftime("%m", $ts_display_date)."&day=".strftime("%d", $ts_display_date)."&id_classe=".$id_classe."&id_groupe=Toutes_matieres";
		}
		else {
			$url_cdt="../cahier_texte/see_all.php?year=".strftime("%Y", $ts_display_date)."&month=".strftime("%m", $ts_display_date)."&day=".strftime("%d", $ts_display_date)."&id_classe=".$id_classe;
		}
	}
	elseif($_SESSION['statut']=="cpe") {
		if (getSettingValue("GepiCahierTexteVersion") == '2') {
			$url_cdt="../cahier_texte_2/see_all.php?year=".strftime("%Y", $ts_display_date)."&month=".strftime("%m", $ts_display_date)."&day=".strftime("%d", $ts_display_date)."&id_classe=".$id_classe."&id_groupe=Toutes_matieres";
		}
		else {
			$url_cdt="../cahier_texte/see_all.php?year=".strftime("%Y", $ts_display_date)."&month=".strftime("%m", $ts_display_date)."&day=".strftime("%d", $ts_display_date)."&id_classe=".$id_classe;
		}
	}

	if($url_cdt!="") {
		$html.="<div style='float:right; width:16px; font-size:x-small; text-align:right; margin: 3px;'><a href='".$url_cdt."' title=\"Consulter l'ensemble du cahier de textes\"><img src='../images/icons/chercher.png' class='icone16' alt='Tout voir' /></a></div>";
	}

	// 20150327
	$delai = getSettingValue("delai_devoirs");
	if(($delai=="")||($delai==0)||(!preg_match("/^[0-9]{1,}$/", $delai))) {
		$html.="<p style='margin-left:4em; text-indent:-4em; color:red'>Erreur&nbsp;: Délai de visualisation du travail personnel non défini.<br />Contactez l'administrateur de GEPI de votre établissement.</p>";
		$delai=1;
	}

	//$html.="delai=$delai<br />";

	$ts_max=time()+3600*24*$delai;

	$temoin_rss_ele=retourne_temoin_ou_lien_rss($login_eleve);
	if($temoin_rss_ele!="") {
		$html.="<div style='float:right; margin-right:0.5em; margin-top:3px; '>".$temoin_rss_ele."</div>\n";
	}

	$html.="<div style='font-weight:bold; font-size: large;' class='fieldset_opacite50'>Cahier de textes</div>";

	$sql="SELECT DISTINCT cde.* FROM ct_devoirs_entry cde, 
				j_eleves_groupes jeg, 
				j_eleves_classes jec, 
				j_groupes_matieres jgm
			WHERE jeg.login='".$login_eleve."' AND 
				jeg.id_groupe=cde.id_groupe AND 
				jec.login=jeg.login AND 
				jec.periode=jeg.periode AND 
				jec.id_classe='".$id_classe."' AND 
				cde.contenu!='' AND 
				cde.date_ct>='".$ts_debut_jour."' AND 
				cde.date_ct<'".min($ts_debut_jour_suivant,$ts_max)."' AND 
				cde.date_visibilite_eleve<='".strftime("%Y-%m-%d %H:%M:%S")."' AND
				jgm.id_groupe=jeg.id_groupe
				ORDER BY jgm.id_matiere;";
	//$html.="$sql<br />";
	$res=mysqli_query($GLOBALS["mysqli"], $sql);
	if(mysqli_num_rows($res)==0) {
		$html.="Aucun travail à faire pour le $display_date.";

		// On affiche aussi le nombre de travaux pour les jours suivants
		$sql="SELECT DISTINCT cde.date_ct, cde.id_groupe, cde.special FROM ct_devoirs_entry cde, 
					j_eleves_groupes jeg, 
					j_eleves_classes jec, 
					j_groupes_matieres jgm
				WHERE jeg.login='".$login_eleve."' AND 
					jeg.id_groupe=cde.id_groupe AND 
					jec.login=jeg.login AND 
					jec.periode=jeg.periode AND 
					jec.id_classe='".$id_classe."' AND 
					cde.contenu!='' AND 
					cde.date_ct>='".($ts_debut_jour+3600*24)."' AND 
					cde.date_ct<'".$ts_max."' AND 
					cde.date_visibilite_eleve<='".strftime("%Y-%m-%d %H:%M:%S")."' AND
					jgm.id_groupe=jeg.id_groupe
					ORDER BY cde.date_ct, jgm.id_matiere;";
		//$html.="$sql<br />";
		$res=mysqli_query($GLOBALS["mysqli"], $sql);
		$nb_jours_travaux=mysqli_num_rows($res);
		if($nb_jours_travaux>0) {
			if($nb_jours_travaux==1) {
				$html.="<hr />Mais ".$nb_jours_travaux." travail à faire pour les jours qui suivent en ";
			}
			else {
				$html.="<hr />Mais ".$nb_jours_travaux." travaux à faire pour les jours qui suivent en ";
			}

			$cpt_tmp=0;
			while($lig=mysqli_fetch_object($res)) {
				if(!isset($tab_group_edt[$lig->id_groupe])) {
					$tab_group_edt[$lig->id_groupe]=get_group($lig->id_groupe, array('matieres', 'classes', 'profs'));
				}
				$current_matiere_cdt=$tab_group_edt[$lig->id_groupe]['matiere']['nom_complet'];

				/*
				if(!isset($tab_couleur_matiere[$tab_group_edt[$lig->id_groupe]['matiere']['matiere']])) {
					$tab_couleur_matiere[$tab_group_edt[$lig->id_groupe]['matiere']['matiere']]=get_couleur_edt_matiere($tab_group_edt[$lig->id_groupe]['matiere']['matiere']);
				}
				*/
				if($cpt_tmp>0) {
					$html.=", ";
				}

				$date_courante=french_strftime("%A %d/%m/%Y", $lig->date_ct);
				if(($url_cdt_prefixe!="")&&($lig->id_groupe!="")&&($lig->id_groupe!=0)) {
					$html.="<a href=\"".$url_cdt_prefixe."&id_groupe=".$lig->id_groupe."\" title=\"Travail à faire pour le ".$date_courante.".\n\nCliquer pour voir le cahier de textes de cet enseignement.\">".$current_matiere_cdt."</a>";
				}
				else {
					$html.="<span title=\"Travail à faire pour le ".$date_courante."\">".$current_matiere_cdt."</span>";
				}
				//$html.="<span title=\"Pour le ".$date_courante."\">".$current_matiere_cdt."</span>";
				if($lig->special=="controle") {
					$html.="<img src='$gepiPath/images/icons/flag2.gif' class='icone16' alt='Contrôle' title=\"Un contrôle/évaluation est programmé pour le $date_courante\" />";
				}

				$cpt_tmp++;
			}
			$html.=".<br />";
		}
	}
	else {
		$html.="<p>Travaux personnels pour le ".french_strftime("%a %d %b", $ts_display_date)."</p>";
		while($lig=mysqli_fetch_object($res)) {
			if(!isset($tab_group_edt[$lig->id_groupe])) {
				$tab_group_edt[$lig->id_groupe]=get_group($lig->id_groupe, array('matieres', 'classes', 'profs'));
			}
			$current_matiere_cdt=$tab_group_edt[$lig->id_groupe]['matiere']['nom_complet'];

			if(!isset($tab_couleur_matiere[$tab_group_edt[$lig->id_groupe]['matiere']['matiere']])) {
				$tab_couleur_matiere[$tab_group_edt[$lig->id_groupe]['matiere']['matiere']]=get_couleur_edt_matiere($tab_group_edt[$lig->id_groupe]['matiere']['matiere']);
			}

			$class_color_fond_notice="";
			$temoin_travail_fait_ou_non="";
			if($CDTPeutPointerTravailFait) {
				get_etat_et_img_cdt_travail_fait($lig->id_ct);
				// La fonction renseigne les variables $tab_etat_travail_fait, $image_etat, $texte_etat_travail, $class_color_fond_notice;

				$temoin_travail_fait_ou_non="<div id='div_etat_travail_".$lig->id_ct."' style='float:right; width: 16px; margin: 2px; text-align: center;'><a href=\"javascript:cdt_modif_etat_travail('".$login_eleve."', '".$lig->id_ct."')\" title=\"$texte_etat_travail\"><img src='$image_etat' class='icone16' /></a></div>\n";
			}

			$lien_cdt_tel_enseignement="";
			if(($url_cdt_prefixe!="")&&($lig->id_groupe!="")&&($lig->id_groupe!=0)) {
				$lien_cdt_tel_enseignement="<div style='float:right; width: 16px; margin: 2px; text-align: center;'><a href=\"".$url_cdt_prefixe."&id_groupe=".$lig->id_groupe."\" title=\"Voir le cahier de textes de cet enseignement.\"><img src='../images/icons/chercher.png' class='icone16' /></a></div>\n";
			}

			$temoin_controle="";
			if($lig->special=="controle") {
				$temoin_controle="<div style='float:right; width:16px;'><img src='$gepiPath/images/icons/flag2.gif' class='icone16' alt='Contrôle' title=\"Un contrôle/évaluation est programmé pour le ".french_strftime("%A %d/%m/%Y", $lig->date_ct)."\" /></div>";
			}

			//background-color:".$tab_couleur_matiere[$tab_group_edt[$lig->id_groupe]['matiere']['matiere']].";
			$html.="
		<div style='border:1px solid black; margin:3px; background-color:".$tab_couleur_matiere[$tab_group_edt[$lig->id_groupe]['matiere']['matiere']].";' class='fieldset_opacite50'>
		<div id='div_travail_".$lig->id_ct."' style='padding:2px;' class='$class_color_fond_notice'>".$temoin_travail_fait_ou_non."
			".$lien_cdt_tel_enseignement."
			".$temoin_controle."
			<p class=\"bold\">".$current_matiere_cdt."</p>
			".$lig->contenu."
			".affiche_docs_joints($lig->id_ct,"t")."
		</div>
	</div>";

		}

		// 20150327
		// On affiche aussi le nombre de travaux pour les jours suivants
		$sql="SELECT DISTINCT cde.date_ct, cde.id_groupe, cde.special FROM ct_devoirs_entry cde, 
					j_eleves_groupes jeg, 
					j_eleves_classes jec, 
					j_groupes_matieres jgm
				WHERE jeg.login='".$login_eleve."' AND 
					jeg.id_groupe=cde.id_groupe AND 
					jec.login=jeg.login AND 
					jec.periode=jeg.periode AND 
					jec.id_classe='".$id_classe."' AND 
					cde.contenu!='' AND 
					cde.date_ct>='".($ts_debut_jour+3600*24)."' AND 
					cde.date_ct<'".$ts_max."' AND 
					cde.date_visibilite_eleve<='".strftime("%Y-%m-%d %H:%M:%S")."' AND
					jgm.id_groupe=jeg.id_groupe
					ORDER BY cde.date_ct, jgm.id_matiere;";
		//$html.="$sql<br />";
		$res=mysqli_query($GLOBALS["mysqli"], $sql);
		$nb_jours_travaux=mysqli_num_rows($res);
		if($nb_jours_travaux>0) {
			if($nb_jours_travaux==1) {
				$html.="<hr />Et ".$nb_jours_travaux." travail à faire pour les jours qui suivent en ";
			}
			else {
				$html.="<hr />Et ".$nb_jours_travaux." travaux à faire pour les jours qui suivent en ";
			}

			$cpt_tmp=0;
			while($lig=mysqli_fetch_object($res)) {
				if(!isset($tab_group_edt[$lig->id_groupe])) {
					$tab_group_edt[$lig->id_groupe]=get_group($lig->id_groupe, array('matieres', 'classes', 'profs'));
				}
				$current_matiere_cdt=$tab_group_edt[$lig->id_groupe]['matiere']['nom_complet'];

				/*
				if(!isset($tab_couleur_matiere[$tab_group_edt[$lig->id_groupe]['matiere']['matiere']])) {
					$tab_couleur_matiere[$tab_group_edt[$lig->id_groupe]['matiere']['matiere']]=get_couleur_edt_matiere($tab_group_edt[$lig->id_groupe]['matiere']['matiere']);
				}
				*/
				if($cpt_tmp>0) {
					$html.=", ";
				}

				$date_courante=french_strftime("%A %d/%m/%Y", $lig->date_ct);
				if(($url_cdt_prefixe!="")&&($lig->id_groupe!="")&&($lig->id_groupe!=0)) {
					$html.="<a href=\"".$url_cdt_prefixe."&id_groupe=".$lig->id_groupe."\" title=\"Travail à faire pour le ".$date_courante.".\n\nCliquer pour voir le cahier de textes de cet enseignement.\">".$current_matiere_cdt."</a>";
				}
				else {
					$html.="<span title=\"Travail à faire pour le ".$date_courante."\">".$current_matiere_cdt."</span>";
				}
				if($lig->special=="controle") {
					$html.="<img src='$gepiPath/images/icons/flag2.gif' class='icone16' alt='Contrôle' title=\"Un contrôle/évaluation est programmé pour le $date_courante\" />";
				}

				$cpt_tmp++;
			}
			$html.=".<br />";
		}
	}

	return $html;
}


function travaux_a_faire_cdt_cours($id_cours, $login_eleve, $id_classe) {
	global $ts_debut_jour, $ts_debut_jour_suivant, $ts_display_date, $display_date, $tab_group_edt, $tab_couleur_matiere, $CDTPeutPointerTravailFait;

	global $tab_etat_travail_fait,
	$image_etat,
	$texte_etat_travail,
	$class_color_fond_notice;

	$html="";

	$sql="SELECT * FROM edt_cours WHERE id_cours='".$_GET['id_cours']."';";
	//$html.="$sql<br />";
	$res=mysqli_query($GLOBALS["mysqli"], $sql);
	if(mysqli_num_rows($res)==0) {
		$html.="Cours n°".$_GET['id_cours']." non trouvé.";
	}
	else {
		$lig_cours=mysqli_fetch_object($res);

		$tmp_tab_display_date=explode("/", $display_date);
		$jour_display_date=$tmp_tab_display_date[0];
		$mois_display_date=$tmp_tab_display_date[1];
		$annee_display_date=$tmp_tab_display_date[2];

		if (getSettingValue("GepiCahierTexteVersion") == '2') {
			if(in_array($_SESSION['statut'], array('eleve', 'responsable'))) {
				$url_cdt="../cahier_texte_2/consultation.php?year=$annee_display_date&month=$mois_display_date&day=$jour_display_date&login_eleve=$login_eleve&id_groupe=".$lig_cours->id_groupe;
			}
			else {
				$url_cdt="../cahier_texte_2/see_all.php?year=$annee_display_date&month=$mois_display_date&day=$jour_display_date&id_classe=$id_classe&id_groupe=".$lig_cours->id_groupe;
			}
		}
		else {
			if(in_array($_SESSION['statut'], array('eleve', 'responsable'))) {
				$url_cdt="../cahier_texte/consultation.php?year=$annee_display_date&month=$mois_display_date&day=$jour_display_date&login_eleve=$login_eleve&id_groupe=".$lig_cours->id_groupe;
			}
			else {
				$url_cdt="../cahier_texte/see_all.php?year=$annee_display_date&month=$mois_display_date&day=$jour_display_date&id_classe=$id_classe&id_groupe=".$lig_cours->id_groupe;
			}
		}

		if($url_cdt!="") {
			$html.="<div style='float:right; width:4em; font-size:x-small; text-align:right; margin: 3px;'><a href='$url_cdt' title=\"Consulter l'ensemble du cahier de textes pour l'enseignement choisi.\"><img src='../images/icons/chercher.png' class='icone16' alt='Tout voir' /></a></div>";
		}

		$temoin_rss_ele=retourne_temoin_ou_lien_rss($login_eleve);
		if($temoin_rss_ele!="") {
			$html.="<div style='float:right; margin-right:0.5em; '>".$temoin_rss_ele."</div>\n";
		}
		$html.="<div style='font-weight:bold; font-size: large;' class='fieldset_opacite50'>Cahier de textes</div>";


		if(!isset($tab_group_edt[$lig_cours->id_groupe])) {
			$tab_group_edt[$lig_cours->id_groupe]=get_group($lig_cours->id_groupe, array('matieres', 'classes', 'profs'));
		}
		$current_matiere_cdt=$tab_group_edt[$lig_cours->id_groupe]['matiere']['nom_complet'];

		if(!isset($tab_couleur_matiere[$tab_group_edt[$lig_cours->id_groupe]['matiere']['matiere']])) {
			$tab_couleur_matiere[$tab_group_edt[$lig_cours->id_groupe]['matiere']['matiere']]=get_couleur_edt_matiere($tab_group_edt[$lig_cours->id_groupe]['matiere']['matiere']);
		}

		$html.="<p class='bold'>$current_matiere_cdt</p>";


		$delai = getSettingValue("delai_devoirs");
		if(($delai=="")||($delai==0)||(!preg_match("/^[0-9]{1,}$/", $delai))) {
			$html.="<p style='margin-left:4em; text-indent:-4em; color:red'>Erreur&nbsp;: Délai de visualisation du travail personnel non défini.<br />Contactez l'administrateur de GEPI de votre établissement.</p>";
			$delai=1;
		}

		$ts_max=time()+3600*24*$delai;

		$sql="SELECT DISTINCT cde.* FROM ct_devoirs_entry cde
				WHERE cde.id_groupe='".$lig_cours->id_groupe."' AND 
					cde.contenu!='' AND 
					cde.date_ct>='".$ts_debut_jour."' AND 
					cde.date_ct<'".$ts_max."' AND 
					cde.date_visibilite_eleve<='".strftime("%Y-%m-%d %H:%M:%S")."'
					ORDER BY date_ct;";
		//$html.="$sql<br />";
		$res=mysqli_query($GLOBALS["mysqli"], $sql);
		if(mysqli_num_rows($res)==0) {
			$html.="Aucun travail à faire donné à ce jour pour les $delai jour(s) qui vien(nen)t.";
		}
		else {
			while($lig=mysqli_fetch_object($res)) {
				$html.="<p>Travaux personnels pour le ".french_strftime("%a %d %b", $lig->date_ct)."</p>";

				$class_color_fond_notice="";
				$temoin_travail_fait_ou_non="";
				if($CDTPeutPointerTravailFait) {
					//$html.="plop";
					get_etat_et_img_cdt_travail_fait($lig->id_ct);
					// La fonction renseigne les variables $tab_etat_travail_fait, $image_etat, $texte_etat_travail, $class_color_fond_notice;

					$temoin_travail_fait_ou_non="<div id='div_etat_travail_".$lig->id_ct."' style='float:right; width: 16px; margin: 2px; text-align: center;'><a href=\"javascript:cdt_modif_etat_travail('".$login_eleve."', '".$lig->id_ct."')\" title=\"$texte_etat_travail\"><img src='$image_etat' class='icone16' /></a></div>\n";
				}

				$temoin_controle="";
				if($lig->special=="controle") {
					$temoin_controle="<div style='float:right; width:16px;'><img src='$gepiPath/images/icons/flag2.gif' class='icone16' alt='Contrôle' title=\"Un contrôle/évaluation est programmé pour le ".french_strftime("%A %d/%m/%Y", $lig->date_ct)."\" /></div>";
				}

				//background-color:".$tab_couleur_matiere[$tab_group_edt[$lig->id_groupe]['matiere']['matiere']].";
				$html.="
			<div style='border:1px solid black; margin:3px; background-color:".$tab_couleur_matiere[$tab_group_edt[$lig_cours->id_groupe]['matiere']['matiere']].";' class='fieldset_opacite50'>
			<div id='div_travail_".$lig->id_ct."' style='padding:2px;' class='$class_color_fond_notice'>".$temoin_travail_fait_ou_non.$temoin_controle."
				".$lig->contenu."
				".affiche_docs_joints($lig->id_ct,"t")."
			</div>
		</div>";

			}

		}
	}

	return $html;
}

//function affiche_edt_ics($num_semaine_annee, $type_edt, $id_classe="", $login_prof="", $largeur_edt=800, $x0=50, $y0=60, $hauteur_une_heure=60, $hauteur_titre=10, $hauteur_entete=40) {
function affiche_edt2($login_eleve, $id_classe, $login_prof, $type_affichage, $ts_display_date, $affichage="semaine", $x0=350, $y0=150, $largeur_edt=800, $hauteur_une_heure=60) {
	//echo "y0=$y0<br />";
	global $couleur_remplacement;
	global $couleur_remplaced;
	global $debug_edt;
	global $hauteur_jour, $hauteur_entete;
	global $tab_group_edt;

	//+++++++++++++++++++++
	// 20150622
	/*
	global $tab_coord_creneaux;
	if((!isset($tab_coord_creneaux))||(!is_array($tab_coord_creneaux))) {
		$tab_coord_creneaux=array();
	}
	*/
	global $jours;
	global $largeur_jour;
	global $x_jour;
	global $premiere_heure;
	global $marge_secu;
	global $hauteur_titre;
	global $affichage_complementaire_sur_edt;
	//+++++++++++++++++++++
	global $complement_liens_edt;
	//+++++++++++++++++++++

	global $tab_coord_prises;
	$tab_coord_prises=array();

	$param_lien_edt="";
	if($login_eleve!="") {
		$param_lien_edt.="login_eleve=$login_eleve&";
	}
	elseif($id_classe!="") {
		$param_lien_edt.="id_classe=$id_classe&";
	}
	elseif($login_prof!="") {
		$param_lien_edt.="login_prof=$login_prof&";
	}
	$param_lien_edt.="type_affichage=$type_affichage&";

	if((isset($complement_liens_edt))&&($complement_liens_edt!="")) {
		$param_lien_edt.=$complement_liens_edt."&";
	}

	if($param_lien_edt=="") {
		$param_lien_edt.="x0=$x0&y0=$y0&";
	}
	else {
		$param_lien_edt.="&x0=$x0&y0=$y0&";
	}

	$html="";

	global $mode_infobulle;
	$chaine_alea=remplace_accents(rand(1, 1000000)."_".microtime(),"all");
	$html.="<script type='text/javascript'>
	function edt_semaine_suivante(num_semaine_annee) {
		//alert('plop');
		new Ajax.Updater($('div_edt_".$chaine_alea."'),'../edt/index2.php?num_semaine_annee='+num_semaine_annee+'&mode=afficher_edt_js&$param_lien_edt&largeur_edt=$largeur_edt&y0=$y0&hauteur_une_heure=$hauteur_une_heure&hauteur_jour=$hauteur_jour',{method: 'get'});
	}
</script>

<div id='div_edt_".$chaine_alea."'>";

	$ts_debut_annee=getSettingValue('begin_bookings');
	$ts_fin_annee=getSettingValue('end_bookings');

	$display_date=strftime("%d/%m/%Y", $ts_display_date);
	//echo strftime("%a %d/%m/%Y à %H:%M:%S", $ts_display_date)."<br />";
	//$num_semaine=strftime("%V", $ts_display_date);
	$num_semaine=id_num_semaine($ts_display_date);
	//echo "\$num_semaine=$num_semaine<br />";
	if($num_semaine<10) {
		$tmp_annee_test=strftime("%Y", $ts_display_date+6*25*3600);
		//echo strftime("%a %d/%m/%Y à %H:%M:%S", $ts_display_date+6*24*3600)."<br />";
		if($tmp_annee_test!=strftime("%Y", $ts_display_date)) {
			$num_semaine_annee="0".$num_semaine."|".$tmp_annee_test;
		}
		else {
			$num_semaine_annee="0".$num_semaine."|".strftime("%Y", $ts_display_date);
		}
		// Le %V a l'air de déjà renvoyer le mois sur 2 chiffres
		$num_semaine_annee=preg_replace("/^[0]{2,}/","0", $num_semaine_annee);
	}
	else {
		$num_semaine_annee=$num_semaine."|".strftime("%Y", $ts_display_date);
	}
	//echo "\$num_semaine_annee=$num_semaine_annee<br />";

	$tab_jour=get_tab_jour_ouverture_etab();

	if($affichage=="semaine") {
		$largeur_jour=$largeur_edt/count($tab_jour);

		$tab_jours_aff=array();
		if(in_array("lundi", $tab_jour)) {
			$tab_jours_aff[]=1;
		}
		if(in_array("mardi", $tab_jour)) {
			$tab_jours_aff[]=2;
		}
		if(in_array("mercredi", $tab_jour)) {
			$tab_jours_aff[]=3;
		}
		if(in_array("jeudi", $tab_jour)) {
			$tab_jours_aff[]=4;
		}
		if(in_array("vendredi", $tab_jour)) {
			$tab_jours_aff[]=5;
		}
		if(in_array("samedi", $tab_jour)) {
			$tab_jours_aff[]=6;
		}
		if(in_array("dimanche", $tab_jour)) {
			$tab_jours_aff[]=7;
		}
	}
	else {
		$largeur_jour=$largeur_edt;

		$tab_jours_aff=array($affichage);
	}


	$hauteur_titre=10;
	$hauteur_entete=40;
	$opacity_couleur=0.5;

	$marge_secu=6;

	$font_size=ceil($hauteur_une_heure/5);
	$font_size2=ceil($hauteur_une_heure/8);
	$font_size3=ceil($hauteur_une_heure/10);

	$tab_group_edt=array();
	$tab_aid_edt=array();
	$tab_couleur_matiere=array();
	$tab_prof=array();

	$tab_salle=get_tab_salle_cours();

	//$html="";

	//$html="\$num_semaine_annee=$num_semaine_annee<br />";

	$tab=explode("|", $num_semaine_annee);

	$num_semaine=$tab[0];
	$annee=$tab[1];

	/*
	$_SESSION['edt_ics_num_semaine']=$num_semaine;
	$_SESSION['edt_ics_annee']=$annee;
	*/

	$jours=get_days_from_week_number($num_semaine, $annee);

	/*
	echo "<pre>";
	print_r($jours);
	echo "</pre>";
	*/

	$info_type_semaine="";
	$info_type_semaine_html="";
	$type_semaine=get_type_semaine($num_semaine);
	//echo "\$type_semaine=$type_semaine<br />";
	if($type_semaine!="") {
		$info_type_semaine=" - Semaine $type_semaine";
		$info_type_semaine_html=" <span style='font-size:".$font_size2."pt;' title=\"Semaine $type_semaine\">($type_semaine)</span>";
	}
	//=================================================================================
	$premiere_heure=8;
	//$derniere_heure=16.5;
	$derniere_heure=17;

	// Récupérer les horaires de début et de fin de journée dans le module EDT
	$sql="SELECT * FROM edt_creneaux ORDER BY heuredebut_definie_periode ASC LIMIT 1;";
	if($debug_edt=="y") {
		echo "$sql<br />";
	}
	$res_premiere_heure=mysqli_query($GLOBALS["mysqli"], $sql);
	if(mysqli_num_rows($res_premiere_heure)>0) {
		$lig_premiere_heure=mysqli_fetch_object($res_premiere_heure);
		if($debug_edt=="y") {
			echo "Première heure du jour&nbsp;: ".$lig_premiere_heure->heuredebut_definie_periode;
		}
		$tmp_tab=explode(":", $lig_premiere_heure->heuredebut_definie_periode);
		$premiere_heure=$tmp_tab[0]+$tmp_tab[1]/60;
		if($debug_edt=="y") {
			echo " soit ".$premiere_heure."<br />";
		}
	}

	$sql="SELECT * FROM edt_creneaux ORDER BY heuredebut_definie_periode DESC LIMIT 1;";
	if($debug_edt=="y") {
		echo "$sql<br />";
	}
	$res_derniere_heure=mysqli_query($GLOBALS["mysqli"], $sql);
	if(mysqli_num_rows($res_derniere_heure)>0) {
		$lig_derniere_heure=mysqli_fetch_object($res_derniere_heure);
		if($debug_edt=="y") {
			echo "Dernière heure du jour&nbsp;: ".$lig_derniere_heure->heurefin_definie_periode;
		}
		$tmp_tab=explode(":", $lig_derniere_heure->heurefin_definie_periode);
		$derniere_heure=$tmp_tab[0]+$tmp_tab[1]/60;
		if($debug_edt=="y") {
			echo " soit ".$derniere_heure."<br />";
		}
	}
	//=================================================================================


	$tmp_tab=explode(".", $premiere_heure);
	$heure_debut_jour=$tmp_tab[0];
	$min_debut_jour=0;
	if(isset($tmp_tab[1])) {
		$min_debut_jour=floor(($premiere_heure-$tmp_tab[0])*60);
	}
	$sec_debut_jour=0;

	// Hauteur du DIV de la journée
	$hauteur_jour=($derniere_heure-$premiere_heure)*$hauteur_une_heure;

	$y_max=$y0+$hauteur_entete+$hauteur_jour;

	//==================================================================
	$x_jour=array();
	if($affichage=="semaine") {
		// Affichage des N jours de la semaine
		//====================================
		// Recherche du numéro de semaine précédente
		$num_semaine_annee_precedente="";
		// 20171102
		//if(strftime("%V", $jours['num_jour'][1]['timestamp'])<=strftime("%V", $ts_fin_annee)) {
		if(id_num_semaine($jours['num_jour'][1]['timestamp'])<=id_num_semaine($ts_fin_annee)) {
			// 20171102
			//if(strftime("%V", $jours['num_jour'][7]['timestamp'])>1) {
			if(id_num_semaine($jours['num_jour'][7]['timestamp'])>1) {
				if($num_semaine-1>9) {
					$num_semaine_annee_precedente=($num_semaine-1)."|".$annee;
				}
				else {
					$num_semaine_annee_precedente="0".($num_semaine-1)."|".$annee;
				}
			}
			else {
				$num_semaine_annee_precedente="52|".($annee-1);
			}
		}
		// 20171102
		//elseif(strftime("%V", $jours['num_jour'][1]['timestamp'])>strftime("%V", $ts_debut_annee)) {
		elseif(id_num_semaine($jours['num_jour'][1]['timestamp'])>id_num_semaine($ts_debut_annee)) {
			if($num_semaine-1>9) {
				$num_semaine_annee_precedente=($num_semaine-1)."|".$annee;
			}
			else {
				$num_semaine_annee_precedente="0".($num_semaine-1)."|".$annee;
			}
		}

		// Semaine précédente
		if($num_semaine_annee_precedente!="") {
			//background-color:silver;
			$x_courant=$x0-32;
			$html.="<div style='position:absolute; top:".($y0+floor(($hauteur_entete-16)/2))."px; left:".$x_courant."px; width:30px; height:".$hauteur_entete."px; text-align:center;' title=\"Semaine précédente\"><a href='".$_SERVER['PHP_SELF']."?".$param_lien_edt."num_semaine_annee=".$num_semaine_annee_precedente."'><img src='../images/arrow_left.png' class='icone16' alt='Précédent' /></a></div>";
		}
		//====================================

		//====================================
		// Bandeaux verticaux des jours
		for($i=0;$i<count($tab_jour);$i++) {
			$x_courant=$x0+$i*$largeur_jour;
			$x_jour[$i]=$x_courant;

			// Abscisse du jour au-dessus de l'entête (pour debug)
			if($debug_edt=="y") {
				$html.="<div style='position:absolute; top:".($y0-$hauteur_entete-2)."px; left:".$x_courant."px; width:".$largeur_jour."px; height:".$hauteur_entete."px;'>".$x_jour[$i]."</div>";
			}

			// Nom du jour en entête
			$html.="<div style='position:absolute; top:".$y0."px; left:".$x_courant."px; width:".$largeur_jour."px; height:".$hauteur_entete."px; border:1px solid black; text-align:center; background-color:silver;' title=\"".$jours['num_jour'][$i+1]['jjmmaaaa']."\">".ucfirst($tab_jour[$i])."<br /><span style='font-size:x-small'>".$jours['num_jour'][$i+1]['jjmmaaaa']."</span></div>";

			// Bande verticale de la journée
			$y_courant=$y0+$hauteur_entete;
			$html.="<div style='position:absolute; top:".$y_courant."px; left:".$x_courant."px; width:".$largeur_jour."px; height:".$hauteur_jour."px; border:1px solid black; background-color:white;z-index:1;'></div>";
			// Pour avoir les traits verticaux
			$html.="<div style='position:absolute; top:".$y_courant."px; left:".$x_courant."px; width:".$largeur_jour."px; height:".$hauteur_jour."px; border:1px solid black;z-index:4;'></div>";
		}
		//====================================

		//====================================
		// Semaine suivante
		$num_semaine_annee_suivante="";
		// 20171102
		//if(strftime("%V", $jours['num_jour'][1]['timestamp'])>=strftime("%V", $ts_debut_annee)) {
		if(id_num_semaine($jours['num_jour'][1]['timestamp'])>=id_num_semaine($ts_debut_annee)) {
			// 20171102
			//if(strftime("%V", $jours['num_jour'][7]['timestamp'])>=52) {
			if(id_num_semaine($jours['num_jour'][7]['timestamp'])>=52) {
				$num_semaine_annee_suivante="01|".($annee+1);
			}
			else {
				$num_semaine_annee_suivante=($num_semaine+1)."|".$annee;
			}

		}
		// 20171102
		//elseif(strftime("%V", $jours['num_jour'][1]['timestamp'])<strftime("%V", $ts_fin_annee)) {
		elseif(id_num_semaine($jours['num_jour'][1]['timestamp'])<id_num_semaine($ts_fin_annee)) {
			if($num_semaine-1>9) {
				$num_semaine_annee_suivante=($num_semaine+1)."|".$annee;
			}
			else {
				$num_semaine_annee_suivante="0".($num_semaine+1)."|".$annee;
			}
		}

		if($num_semaine_annee_suivante!="") {
			//background-color:silver;
			$x_courant=$x0+$largeur_edt;
			$html.="<div style='position:absolute; top:".($y0+floor(($hauteur_entete-16)/2))."px; left:".$x_courant."px; width:30px; height:".$hauteur_entete."px; text-align:center; z-index:20;' title=\"Semaine suivante\"><a href='".$_SERVER['PHP_SELF']."?".$param_lien_edt."num_semaine_annee=".$num_semaine_annee_suivante."'";
			if($mode_infobulle=="y") {
				$html.=" onclick=\"edt_semaine_suivante('$num_semaine_annee_suivante'); return false;\"";
			}
			$html.="><img src='../images/arrow_right.png' class='icone16' alt='Suivant' /></a></div>";
		}
		//====================================

	}
	else {
		//====================================
		// Jour précédent
		// Boucler sur 7 jours pour trouver le précédent jour ouvré
		$display_date_precedente="";
		$display_date_precedente_num_jour="";
		$ts_test=$ts_display_date;
		$cpt=0;
		while(($cpt<7)&&($ts_test>$ts_debut_annee)) {
			$ts_test-=3600*24;
			if(in_array(french_strftime("%A", $ts_test), $tab_jour)) {
				$display_date_precedente=strftime("%d/%m/%Y", $ts_test);
				$display_date_precedente_num_jour=id_j_semaine($ts_test);
				break;
			}
			$cpt++;
		}

		if($display_date_precedente!="") {
			//background-color:silver;
			$x_courant=$x0-32;
			$html.="<div style='position:absolute; top:".($y0+floor(($hauteur_entete-16)/2))."px; left:".$x_courant."px; width:30px; height:".$hauteur_entete."px; text-align:center;' title=\"Jour précédent\"><a href='".$_SERVER['PHP_SELF']."?".$param_lien_edt."affichage=".$display_date_precedente_num_jour."&amp;display_date=".$display_date_precedente."'><img src='../images/arrow_left.png' class='icone16' alt='Précédent' /></a></div>";
		}
		//====================================

		//====================================
		// Colonne du jour
		$x_courant=$x0;
		$x_jour[0]=$x_courant;

		// Abscisse du jour au-dessus de l'entête (pour debug)
		if($debug_edt=="y") {
			$html.="<div style='position:absolute; top:".($y0-$hauteur_entete-2)."px; left:".$x_courant."px; width:".$largeur_jour."px; height:".$hauteur_entete."px;'>".$x_jour[0]."</div>";
		}

		// Nom du jour en entête
		$html.="
	<form action='".$_SERVER['PHP_SELF']."' id='form_chgt_date' method='post'>
		<input type='hidden' name='affichage' value='jour'>
		<input type='hidden' name='display_date' id='display_date' value='' onchange=\"document.getElementById('form_chgt_date').submit();\">
		<!--input type='text' name='display_date' id='display_date' value=''-->
	</form>

	<div style='position:absolute; top:".$y0."px; left:".$x_courant."px; width:".$largeur_jour."px; height:".$hauteur_entete."px; border:1px solid black; text-align:center; background-color:silver;' title=\"".$jours['num_jour'][$affichage]['jjmmaaaa'].$info_type_semaine."\">

		<div style='float:right; width:16px;'>".img_calendrier_js("display_date", "img_bouton_display_date")."</div>

		<span onclick=\"action_edt_cours('', '')\" title=\"Cliquez...\">".ucfirst($jours['num_jour'][$affichage]['nom_jour'])."</span><br />
		<span style='font-size:x-small'>".$jours['num_jour'][$affichage]['jjmmaaaa']."</span>$info_type_semaine_html
	</div>";

		// Bande verticale de la journée
		$y_courant=$y0+$hauteur_entete;
		$html.="<div style='position:absolute; top:".$y_courant."px; left:".$x_courant."px; width:".$largeur_jour."px; height:".$hauteur_jour."px; border:1px solid black; background-color:white;z-index:1;'></div>";
		// Pour avoir les traits verticaux
		$html.="<div style='position:absolute; top:".$y_courant."px; left:".$x_courant."px; width:".$largeur_jour."px; height:".$hauteur_jour."px; border:1px solid black;z-index:4;'></div>";
		//====================================

		//====================================
		// Jour suivant
		// Boucler sur 7 jours pour trouver le jour ouvré suivant
		$display_date_suivante="";
		$display_date_suivante_num_jour="";
		$ts_test=$ts_display_date;
		$cpt=0;
		while(($cpt<7)&&($ts_test<$ts_fin_annee)) {
			$ts_test+=3600*24;
			if(in_array(french_strftime("%A", $ts_test), $tab_jour)) {
				$display_date_suivante=strftime("%d/%m/%Y", $ts_test);
				$display_date_suivante_num_jour=id_j_semaine($ts_test);
				break;
			}
			$cpt++;
		}

		if($display_date_suivante!="") {
			//background-color:silver;
			$x_courant=$x0+$largeur_jour;
			$html.="<div style='position:absolute; top:".($y0+floor(($hauteur_entete-16)/2))."px; left:".$x_courant."px; width:30px; height:".$hauteur_entete."px; text-align:center; z-index:20;' title=\"Jour suivant\"><a href='".$_SERVER['PHP_SELF']."?".$param_lien_edt."affichage=".$display_date_suivante_num_jour."&amp;display_date=".$display_date_suivante."'><img src='../images/arrow_right.png' class='icone16' alt='Suivant' /></a></div>";
		}
		//====================================
	}


	//==================================================================
	// Affichage des heures sur la droite
	$heure_ronde_debut_jour=floor($premiere_heure);
	$heure_courante=$heure_ronde_debut_jour;
	$heure_ronde_debut_jour=ceil($derniere_heure);
	if($debug_edt=="y") {
		echo "\$derniere_heure=$derniere_heure<br />";
	}
	$hauteur_texte=12; // A la louche
	$hauteur_demi_texte=ceil($hauteur_texte/2);
	while($heure_courante<$heure_ronde_debut_jour) {
		$y_courant=$y0+$hauteur_entete+($heure_courante-$premiere_heure)*$hauteur_une_heure-$hauteur_demi_texte;

		$html.="<div style='position:absolute; top:".($y_courant)."px; left:".($x0+$largeur_edt)."px; width:30px; height:".$hauteur_une_heure."px; text-align:center;'>".$heure_courante."H</div>";

		$heure_courante++;
	}
	//==================================================================

	//==================================================================
	// Affichage des noms de créneaux sur la gauche
	$sql="SELECT * FROM edt_creneaux ORDER BY heuredebut_definie_periode;";
	$res_creneaux=mysqli_query($GLOBALS["mysqli"], $sql);
	if(mysqli_num_rows($res_creneaux)>0) {
		while($lig=mysqli_fetch_object($res_creneaux)) {
			$tab_h=explode(":", $lig->heuredebut_definie_periode);
			$c=$tab_h[0]+$tab_h[1]/60-$premiere_heure;
			$y1_courant=$y0+$hauteur_entete+round($c*$hauteur_une_heure);

			$tab_h=explode(":", $lig->heurefin_definie_periode);
			$c=$tab_h[0]+$tab_h[1]/60-$premiere_heure;
			$y2_courant=$y0+$hauteur_entete+round($c*$hauteur_une_heure);

			$hauteur_courante=$y2_courant-$y1_courant;

			/*
			// 20150622
			$tab_coord_creneaux['creneau'][$lig->id_definie_periode]['ordonnees'][0]=$y1_courant;
			$tab_coord_creneaux['creneau'][$lig->id_definie_periode]['ordonnees'][1]=$y2_courant;
			*/

			$style_fond_creneau="background-color:white;";
			if($lig->type_creneaux=="pause") {
				$style_fond_creneau="background-color:silver;";
			}
			elseif($lig->type_creneaux=="repas") {
				$style_fond_creneau="background-color:grey;";
			}

			// Nom du créneau sur la gauche
			$html.="<div style='position:absolute; top:".($y1_courant)."px; left:".($x0-32)."px; width:30px; height:".$hauteur_courante."px; text-align:center; border:1px solid black; vertical-align:middle;".$style_fond_creneau."' title=\"Créneau $lig->nom_definie_periode\nDe $lig->heuredebut_definie_periode à $lig->heurefin_definie_periode.\"><div style='position:relative; width:2em; height:1em;'>".$lig->nom_definie_periode."</div></div>";

			// Bandes horizontales du créneau
			$html.="<div style='position:absolute; top:".($y1_courant)."px; left:".$x0."px; width:".$largeur_edt."px; height:".$hauteur_courante."px; border:1px solid silver; z-index:2;".$style_fond_creneau."'></div>";

			// Debug
			if($debug_edt=="y") {
				$html.="<div style='position:absolute; top:".($y1_courant)."px; left:".($x0+$largeur_edt+30)."px; width:".$largeur_edt."px; height:".$hauteur_courante."px; color:red; z-index:2;'>$y1_courant</div>";
			}

		}
	}
	//==================================================================

	$tab_cours=array();
	$tab_nom_classe=array();

	/*
	$sql="SELECT * FROM edt_cours ec, edt_creneaux ecr WHERE
				ec.id_groupe IN (SELECT id_groupe from j_eleves_groupes WHERE login = '".$login_eleve."') AND
				(ec.id_semaine='' OR ec.id_semaine='0' OR ec.id_semaine='$type_semaine') AND 
				ec.id_definie_periode=ecr.id_definie_periode 
			ORDER BY heuredebut_definie_periode;";
	//echo "$sql<br />";
	$res_cours_de_la_semaine=mysqli_query($GLOBALS["mysqli"], $sql);
	while($lig=mysqli_fetch_object($res_cours_de_la_semaine)) {
		if($debug_edt=="y") {
			echo "<pre style='border:1px solid red; margin:0.5em;'>";
			print_r($lig);
			echo "</pre>";
		}

		$ts_debut=mysql_date_to_unix_timestamp($lig->date_debut);
		$horaire_debut=strftime("%H:%M", $ts_debut);
		$ts_fin=mysql_date_to_unix_timestamp($lig->date_fin);
		$horaire_fin=strftime("%H:%M", $ts_fin);

		$num_jour=id_j_semaine($ts_debut)-1;

		$jour_debut_jour=strftime("%d", $ts_debut);
		$mois_debut_jour=strftime("%m", $ts_debut);
		$annee_debut_jour=strftime("%Y", $ts_debut);
		$ts_debut_jour=mktime($heure_debut_jour,$min_debut_jour,$sec_debut_jour,$mois_debut_jour,$jour_debut_jour,$annee_debut_jour);

		$duree_en_min=floor(($ts_fin-$ts_debut)/60);
		$hauteur_courante=floor($duree_en_min*$hauteur_une_heure/60);
		//$hauteur_courante=floor($duree_en_min*$hauteur_une_heure/60)-ceil($marge_secu/2);

		//$duree_depuis_debut_journee=floor(($ts_debut-$ts_debut_jour)/3600);
		$duree_depuis_debut_journee=floor(10*($ts_debut-$ts_debut_jour)/3600)/10;
		//$y_courant=$y0+$hauteur_entete+$duree_depuis_debut_journee*$hauteur_une_heure;
		$y_courant=$y0+$hauteur_entete+$duree_depuis_debut_journee*$hauteur_une_heure+ceil($marge_secu/2);

		if($debug_edt=="y") {
			$html.="\$jour_debut_jour=$jour_debut_jour<br />";
			$html.="\$ts_debut_jour=$ts_debut_jour<br />";
			$html.="\$ts_debut=$ts_debut<br />";
			$html.="\$duree_depuis_debut_journee=$duree_depuis_debut_journee<br />";
			$html.="y_courant=$y_courant<br />";
		}

		$cpt_courant=0;
		if(isset($tab_cours[$num_jour]['y'][$y_courant])) {
			$cpt_courant=count($tab_cours[$num_jour]['y'][$y_courant]);
		}
		$tab_cours[$num_jour]['y'][$y_courant][$cpt_courant]['hauteur']=$hauteur_courante;
		// A FAIRE : Stocker dans des tableaux les retours de fonction qui suivent pour ne pas faire plusieurs fois les mêmes appels
		$tab_cours[$num_jour]['y'][$y_courant][$cpt_courant]['matiere']=get_tab_matiere_gepi_pour_matiere_ics($lig->matiere_ics);
		$tab_cours[$num_jour]['y'][$y_courant][$cpt_courant]['prof']=get_tab_prof_gepi_pour_prof_ics($lig->prof_ics);
		$tab_cours[$num_jour]['y'][$y_courant][$cpt_courant]['salle']=$lig->salle_ics;
		$tab_cours[$num_jour]['y'][$y_courant][$cpt_courant]['id_cours']=$lig->id;
		$tab_cours[$num_jour]['y'][$y_courant][$cpt_courant]['id_classe']=$lig->id_classe;
		if(!array_key_exists($lig->id_classe, $tab_nom_classe)) {
			$tab_nom_classe[$lig->id_classe]=get_nom_classe($lig->id_classe);
		}
		$tab_cours[$num_jour]['y'][$y_courant][$cpt_courant]['classe']=$tab_nom_classe[$lig->id_classe];
		$tab_cours[$num_jour]['y'][$y_courant][$cpt_courant]['horaire_debut']=$horaire_debut;
		$tab_cours[$num_jour]['y'][$y_courant][$cpt_courant]['horaire_fin']=$horaire_fin;

		// Stockage des identifiants de cours que n'ont pas les élèves faute de suivre la matière
		if(($_SESSION['statut']=='eleve')&&
		($tab_cours[$num_jour]['y'][$y_courant][$cpt_courant]['matiere']['association_faite']=="y")&&
		(!in_array($tab_cours[$num_jour]['y'][$y_courant][$cpt_courant]['matiere']['matiere'], $tab_matieres_eleve))) {
			$tab_id_cours_exclu[]=$tab_cours[$num_jour]['y'][$y_courant][$cpt_courant]['id_cours'];
		}

	}
	*/

	$afficher_remplacements="n";
	if((getSettingAOui("active_mod_abs_prof"))&&(getSettingAOui("AbsProfAfficherSurEDT2"))) {
		$afficher_remplacements="y";
	}

	$signaler_cours_non_remplaces="n";
	if((getSettingAOui("active_mod_abs_prof"))&&(getSettingAOui("AbsProfSignalerNonRemplaceSurEDT2"))) {
		$signaler_cours_non_remplaces="y";
	}

	//==================================================================
	// On passe à l'affichage du contenu du ou des jours
	//for($num_jour=1;$num_jour<=count($tab_jour);$num_jour++) {
	for($loop_jour=0;$loop_jour<count($tab_jours_aff);$loop_jour++) {
		$num_jour=$tab_jours_aff[$loop_jour];

		$jour_sem=$jours['num_jour'][$num_jour]['nom_jour'];
		$jour_debut_jour=$jours['num_jour'][$num_jour]['jj'];
		$mois_debut_jour=$jours['num_jour'][$num_jour]['mm'];
		$annee_debut_jour=$jours['num_jour'][$num_jour]['aaaa'];

		$ts_debut_jour=mktime($heure_debut_jour,$min_debut_jour,$sec_debut_jour,$mois_debut_jour,$jour_debut_jour,$annee_debut_jour);

		if($debug_edt=="y") {
			echo "num_jour=$num_jour<br />";
			echo "heure_debut_jour=$heure_debut_jour<br />";
			echo "min_debut_jour=$min_debut_jour<br />";
			echo "ts_debut_jour=$ts_debut_jour<br />";
		}

		$tab_mes_cours_remplaces_par_d_autres=array();
		$tab_abs_prof=array();
		//if(($afficher_remplacements=="y")&&($login_prof!="")&&($login_prof==$_SESSION['login'])) {
		// Pour les parents et élèves, il faut prendre en compte un paramètre "famille avertie" avant de permettre l'affichage
		if(($afficher_remplacements=="y")&&($login_prof!="")&&(in_array($_SESSION['statut'], array('professeur', 'cpe', 'scolarite', 'administrateur')))) {
			// Récupérer les cours remplacés par d'autres

			// id_j_semaine() : 	ISO-8601 numeric representation of the day of the week 	1 (for Monday) through 7 (for Sunday)
			$tmp_tab_jour=array("", "lundi", "mardi", "mercredi", "jeudi", "vendredi", "samedi", "dimanche");
			$nom_jour=$tmp_tab_jour[id_j_semaine($ts_debut_jour)];

			$sql="SELECT apr.id_cours_remplaced, apr.login_user, apr.date_debut_r, apr.date_fin_r FROM abs_prof_remplacement apr, edt_cours ec WHERE apr.jour='".$annee_debut_jour.$mois_debut_jour.$jour_debut_jour."' AND ec.id_cours=apr.id_cours_remplaced AND ec.jour_semaine='".$nom_jour."' AND login_prof='".$login_prof."' ORDER BY apr.date_debut_r;";
			//echo "$sql<br />";
			$res_mes_cours_remplaced=mysqli_query($GLOBALS["mysqli"], $sql);
			while($lig_mes_cours_remplaced=mysqli_fetch_assoc($res_mes_cours_remplaced)) {
				$tab_mes_cours_remplaces_par_d_autres[$lig_mes_cours_remplaced['id_cours_remplaced']][]=$lig_mes_cours_remplaced;
			}

			$sql="SELECT ec.* FROM abs_prof ap, 
							edt_cours ec, 
							edt_creneaux ecr 
					WHERE ap.date_debut<='".$annee_debut_jour."-".$mois_debut_jour."-".$jour_debut_jour." 23:59:00' AND 
						ap.date_fin>='".$annee_debut_jour."-".$mois_debut_jour."-".$jour_debut_jour." 00:00:00' AND 
						ec.id_definie_periode=ecr.id_definie_periode AND 
						CONCAT('".$annee_debut_jour."-".$mois_debut_jour."-".$jour_debut_jour." ', ecr.heuredebut_definie_periode)>=ap.date_debut AND 
						CONCAT('".$annee_debut_jour."-".$mois_debut_jour."-".$jour_debut_jour." ', ecr.heurefin_definie_periode)<=ap.date_fin AND 
						ec.jour_semaine='".$nom_jour."' AND 
						ap.login_user=ec.login_prof AND 
						ap.login_user='".$login_prof."';";
			//echo "$sql<br />";
			$res_abs_prof=mysqli_query($GLOBALS["mysqli"], $sql);
			while($lig_abs_prof=mysqli_fetch_assoc($res_abs_prof)) {
				$tab_abs_prof[]=$lig_abs_prof['id_cours'];
			}

			// Il va manquer aussi les cours en AID
			// A FAIRE : Compléter $tab_abs_prof avec les cours en AID
			//           Et distinguer les AID avec un seul prof et avec plusieurs profs?
		}
		elseif(($afficher_remplacements=="y")&&($id_classe!="")&&(in_array($_SESSION['statut'], array('professeur', 'cpe', 'scolarite', 'administrateur')))) {
			// id_j_semaine() : 	ISO-8601 numeric representation of the day of the week 	1 (for Monday) through 7 (for Sunday)
			$tmp_tab_jour=array("", "lundi", "mardi", "mercredi", "jeudi", "vendredi", "samedi", "dimanche");
			$nom_jour=$tmp_tab_jour[id_j_semaine($ts_debut_jour)];

			$sql="SELECT ec.* FROM abs_prof ap, 
							edt_cours ec, 
							edt_creneaux ecr, 
							j_groupes_classes jgc, 
							j_groupes_professeurs jgp 
					WHERE ap.date_debut<='".$annee_debut_jour."-".$mois_debut_jour."-".$jour_debut_jour." 23:59:00' AND 
						ap.date_fin>='".$annee_debut_jour."-".$mois_debut_jour."-".$jour_debut_jour." 00:00:00' AND 
						ec.id_definie_periode=ecr.id_definie_periode AND 
						CONCAT('".$annee_debut_jour."-".$mois_debut_jour."-".$jour_debut_jour." ', ecr.heuredebut_definie_periode)>=ap.date_debut AND 
						CONCAT('".$annee_debut_jour."-".$mois_debut_jour."-".$jour_debut_jour." ', ecr.heurefin_definie_periode)<=ap.date_fin AND 
						ec.jour_semaine='".$nom_jour."' AND 
						ap.login_user=ec.login_prof AND 
						jgc.id_classe='".$id_classe."' AND 
						jgc.id_groupe=jgp.id_groupe AND 
						ap.login_user=jgp.login;";
			//echo "$sql<br />";
			$res_abs_prof=mysqli_query($GLOBALS["mysqli"], $sql);
			while($lig_abs_prof=mysqli_fetch_assoc($res_abs_prof)) {
				$tab_abs_prof[]=$lig_abs_prof['id_cours'];
			}

			// Il va manquer aussi les cours en AID
			// A FAIRE : Compléter $tab_abs_prof avec les cours en AID
			//           Et distinguer les AID avec un seul prof et avec plusieurs profs?
		}

		$tab_cours_non_remplaces=array();
		if($signaler_cours_non_remplaces=="y") {
			// Il faut récupérer les id_cours pris dans l'amplitude d'absence et exclure ceux qui sont remplacés.

/*
mysql> select * from abs_prof;
+----+------------+---------------------+---------------------+------------------+------------------------------------------------------------+
| id | login_user | date_debut          | date_fin            | titre            | description                                                |
+----+------------+---------------------+---------------------+------------------+------------------------------------------------------------+
|  1 | JOSSETF    | 2016-10-14 07:58:00 | 2016-10-14 17:29:00 | Stage            | <p>
	Stage</p>
                                            |
|  2 | TOESCAV    | 2017-01-24 13:27:00 | 2017-01-24 14:25:00 | Musée Bernay 6C  | <p>
	Musée Bernay 6C avec M.Pauly de 13h30 à 16h00.</p>
   |
|  3 | paulya     | 2017-01-30 07:58:00 | 2017-01-31 17:29:00 | Absence Pauly    | <p>
	Absence  d'Aurélien</p>
                              |
+----+------------+---------------------+---------------------+------------------+------------------------------------------------------------+
3 rows in set (0.00 sec)

mysql> 

mysql> select * from abs_prof_remplacement where validation_remplacement='oui';
+----+------------+-----------+--------+-----------+----------+------------+---------------------+---------------------+---------+---------------------+------------+------------------+-------------------------+------------------------+----------+----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+--------------+-------+--------------+--------------+--------------------+
| id | id_absence | id_groupe | id_aid | id_classe | jour     | id_creneau | date_debut_r        | date_fin_r          | reponse | date_reponse        | login_user | commentaire_prof | validation_remplacement | commentaire_validation | salle    | texte_famille                                                                                                                                                                                | info_famille | duree | heuredeb_dec | jour_semaine | id_cours_remplaced |
+----+------------+-----------+--------+-----------+----------+------------+---------------------+---------------------+---------+---------------------+------------+------------------+-------------------------+------------------------+----------+----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+--------------+-------+--------------+--------------+--------------------+
|  1 |          1 |      4044 |      0 |        35 | 20161014 |          3 | 2016-10-14 10:04:00 | 2016-10-14 11:02:00 | oui     | 2016-10-05 08:37:27 | BOIREAUS   | Blabla
Blibloblu | oui                     | blabla
                | 14       |                                                                                                                                                                                              |              | 0     | 0            |              |                  0 |
|  2 |          2 |      3825 |      0 |        34 | 20170124 |          5 | 2017-01-24 13:27:00 | 2017-01-24 14:25:00 | oui     | 2017-01-16 13:10:44 | BOIREAUS   | Avec joie.       | oui                     |                        | 14       | En raison de la sortie scolaire des 6C au Musée de Bernay, le cours __COURS__ de __PROF_ABSENT__ du __DATE_HEURE__ sera remplacé par un cours avec __PROF_REMPLACANT__ en salle __SALLE__.   | oui          | 0     | 0            |              |                  0 |
| 15 |          3 |      4059 |      0 |        35 | 20170130 |          2 | 2017-01-30 08:55:00 | 2017-01-30 09:52:00 | oui     | 2017-01-27 21:07:52 | BOIREAUS   |                  | oui                     | Salle info réservée    | Salle 14 |                                                                                                                                                                                              |              | 2     | 0            | lundi        |              24106 |
+----+------------+-----------+--------+-----------+----------+------------+---------------------+---------------------+---------+---------------------+------------+------------------+-------------------------+------------------------+----------+----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+--------------+-------+--------------+--------------+--------------------+
3 rows in set (0.01 sec)

mysql> 

*/




			$sql="SELECT * FROM abs_prof WHERE DATE(date_debut)<='".$annee_debut_jour."-".$mois_debut_jour."-".$jour_debut_jour."' AND 
									DATE(date_fin)>='".$annee_debut_jour."-".$mois_debut_jour."-".$jour_debut_jour."';";
			$res_cours_non_remplaces=mysqli_query($GLOBALS["mysqli"], $sql);
			while($lig_cours_non_remplaces=mysqli_fetch_object($res_cours_non_remplaces)) {
				// Parcourir les cours du prof pour ce jour.







			}
		}

		// A REVOIR On suppose là qu'il n'y a qu'un id_calendrier.
		//          A revoir quand on enregistrera des id_calendrier autres
		// id_groupe, id_aid, duree, heuredeb_dec, id_semaine, id_cours
		$ajout_sql="";
		$ajout_sql2="";
		if($login_eleve!="") {
			//$ajout_sql.="ec.id_groupe IN (SELECT id_groupe from j_eleves_groupes WHERE login = '".$login_eleve."') AND ";
			$ajout_sql.="(ec.id_groupe IN (SELECT id_groupe from j_eleves_groupes WHERE login = '".$login_eleve."') OR ec.id_aid IN (SELECT DISTINCT id_aid FROM j_aid_eleves WHERE login = '".$login_eleve."' AND id_aid!='')) AND ";

			if($afficher_remplacements=="y") {
				$ajout_sql2.="(apr.id_groupe IN (SELECT id_groupe from j_eleves_groupes WHERE login = '".$login_eleve."') OR apr.id_aid IN (SELECT DISTINCT id_aid FROM j_aid_eleves WHERE login = '".$login_eleve."' AND id_aid!='')) AND ";

				$ajout_sql.="(CONCAT(ec.id_cours,'|',ec.jour_semaine,'|',ec.id_definie_periode) NOT IN (SELECT CONCAT(id_cours_remplaced,'|',jour_semaine,'|',id_creneau) FROM abs_prof_remplacement WHERE jour='".$annee_debut_jour.$mois_debut_jour.$jour_debut_jour."')) AND ";
			}

		}
		if($id_classe!="") {
			//$ajout_sql.="ec.id_groupe IN (SELECT id_groupe from j_groupes_classes WHERE id_classe = '".$id_classe."') AND ";
			$ajout_sql.="(ec.id_groupe IN (SELECT id_groupe from j_groupes_classes WHERE id_classe = '".$id_classe."') OR 
					ec.id_aid IN (SELECT DISTINCT id_aid FROM j_aid_eleves jae, j_eleves_classes jec 
											WHERE jae.login=jec.login AND jec.id_classe='".$id_classe."' AND jae.id_aid!='')) AND ";

			if($afficher_remplacements=="y") {
				$ajout_sql2.="(apr.id_groupe IN (SELECT id_groupe from j_groupes_classes WHERE id_classe = '".$id_classe."') OR 
						apr.id_aid IN (SELECT DISTINCT id_aid FROM j_aid_eleves jae, j_eleves_classes jec 
												WHERE jae.login=jec.login AND jec.id_classe='".$id_classe."' AND jae.id_aid!='')) AND ";

				$ajout_sql.="(CONCAT(ec.id_cours,'|',ec.jour_semaine,'|',ec.id_definie_periode) NOT IN (SELECT CONCAT(id_cours_remplaced,'|',jour_semaine,'|',id_creneau) FROM abs_prof_remplacement WHERE jour='".$annee_debut_jour.$mois_debut_jour.$jour_debut_jour."')) AND ";
			}
		}
		if($login_prof!="") {
			//$ajout_sql.="ec.id_groupe IN (SELECT id_groupe from j_groupes_professeurs WHERE login = '".$login_prof."') AND ";
			$ajout_sql.="ec.login_prof = '".$login_prof."' AND ";

			if($afficher_remplacements=="y") {
				$ajout_sql2.="apr.login_user = '".$login_prof."' AND ";

				// Pour éviter que le prof ne voie plus du tout ses cours (remplacés par d'autres)
				if($login_prof!=$_SESSION['login']) {
					$ajout_sql.="(CONCAT(ec.id_cours,'|',ec.jour_semaine,'|',ec.id_definie_periode) NOT IN (SELECT CONCAT(id_cours_remplaced,'|',jour_semaine,'|',id_creneau) FROM abs_prof_remplacement WHERE jour='".$annee_debut_jour.$mois_debut_jour.$jour_debut_jour."')) AND ";
				}
			}
		}

		//20150206
		// A FAIRE : Prendre en compte un id_calendrier en fonction de la semaine choisie
		//           A VERIFIER : Est-ce id_calendar ou id_calendrier (il y a les 2 dans la table)
		//           Pour un emploi du temps élève, recuperer la classe associée à la semaine
		//           puis tester l'id_calendrier dans edt_cours
		//           Pour un emploi du temps prof, problème si ce n'est pas la même période EDT (id_calendrier) selon les classes
		//           Cela dit le prof n'étant pas sensé être avec 2 classes différentes à un même instant,
		//           il ne devrait pas y avoir de collision (sauf cas de l'EDT mal rempli avec un même enseignement regroupement de deux classes, inscrit à deux moments)
		// POUR LE MOMENT : Si il y a plusieurs id_calendrier remplis dans edt_cours la requête ci-dessous va cumuler les EDT.

		//20150205
		$afficher_sem_AB=isset($_POST['afficher_sem_AB']) ? $_POST['afficher_sem_AB'] : (isset($_GET['afficher_sem_AB']) ? $_GET['afficher_sem_AB'] : "n");
		if($afficher_sem_AB=="y") {
			$avec_contrainte_semaine="";
		}
		else {
			$avec_contrainte_semaine="(ec.id_semaine='' OR ec.id_semaine='0' OR ec.id_semaine='$type_semaine') AND ";
		}
		/*
		$sql="SELECT DISTINCT * FROM edt_cours ec, edt_creneaux ecr WHERE
						ec.jour_semaine = '".$jour_sem."' AND
						$ajout_sql
						$avec_contrainte_semaine
						ec.id_definie_periode=ecr.id_definie_periode 
					ORDER BY heuredebut_definie_periode, id_semaine;";

		$jour_debut_jour=$jours['num_jour'][$num_jour]['jj'];
		$mois_debut_jour=$jours['num_jour'][$num_jour]['mm'];
		$annee_debut_jour=$jours['num_jour'][$num_jour]['aaaa'];
		
id
id_groupe
salle
jour
id_creneau

date_debut_r
date_fin_r

id_classe
info_famille
id_absence

login_user
id_aid

mysql> show fields from edt_cours;
+--------------------+-------------+------+-----+---------+----------------+
| Field              | Type        | Null | Key | Default | Extra          |
+--------------------+-------------+------+-----+---------+----------------+
| id_cours           | int(11)     | NO   | PRI | NULL    | auto_increment |
| id_groupe          | varchar(10) | NO   |     |         |                |
| id_salle           | char(3)     | NO   |     |         |                |
| jour_semaine       | varchar(10) | NO   |     |         |                |
| id_definie_periode | char(3)     | NO   |     |         |                |
| duree              | varchar(10) | NO   |     | 2       |                |
| heuredeb_dec       | char(3)     | NO   |     | 0       |                |
| id_semaine         | varchar(10) | NO   |     | 0       |                |
| id_calendrier      | char(3)     | NO   |     | 0       |                |
| modif_edt          | char(3)     | NO   |     | 0       |                |
| login_prof         | varchar(50) | NO   |     |         |                |
| id_aid             | char(10)    | YES  |     |         |                |
+--------------------+-------------+------+-----+---------+----------------+
12 rows in set (0.06 sec)

mysql> 

							,id_definie_periode
							id_creneau AS id_definie_periode,
*/

		if($afficher_remplacements=="y") {
			$sql="(SELECT DISTINCT id_cours,id_groupe,id_salle,jour_semaine,duree,heuredeb_dec,id_semaine,id_calendrier,modif_edt,login_prof,id_aid,
							'standard' AS type_cours,
							ecr.*
						 FROM edt_cours ec, edt_creneaux ecr WHERE
							ec.jour_semaine = '".$jour_sem."' AND
							$ajout_sql
							ec.id_definie_periode=ecr.id_definie_periode)
				UNION 
				(SELECT DISTINCT id,
							id_groupe,
							salle AS id_salle,
							jour_semaine,
							duree, 
							heuredeb_dec, 
							'0' AS id_semaine, 
							'0' AS id_calendrier, 
							id_absence AS modif_edt, 
							login_user AS login_prof,
							id_aid,
							'remplacement' AS type_cours,
							ecr.* 
						FROM abs_prof_remplacement apr, edt_creneaux ecr WHERE
							apr.jour = '".$annee_debut_jour.$mois_debut_jour.$jour_debut_jour."' AND 
							apr.validation_remplacement='oui' AND 
							apr.duree!='0' AND 
							$ajout_sql2
							apr.id_creneau=ecr.id_definie_periode) 
						ORDER BY heuredebut_definie_periode, id_semaine;";
		}
		else {
			$sql="SELECT DISTINCT *,
							'standard' AS type_cours,
							ecr.*
						 FROM edt_cours ec, edt_creneaux ecr WHERE
							ec.jour_semaine = '".$jour_sem."' AND
							$ajout_sql
							ec.id_definie_periode=ecr.id_definie_periode 
						ORDER BY heuredebut_definie_periode, id_semaine;";
		}

		if($debug_edt=="y") {
			echo "<div style='margin-left:1000px'>";
			echo "$sql<br />";
			echo "</div>";
		}

/*

stdClass Object
(
    [id_cours] => 24627
    [id_groupe] => 4086
    [id_salle] => 4
    [jour_semaine] => lundi
    [id_definie_periode] => 6
    [duree] => 2
    [heuredeb_dec] => 0
    [id_semaine] => B
    [id_calendrier] => 0
    [modif_edt] => 0
    [login_prof] => BOIREAUS
    [id_aid] => 
    [nom_definie_periode] => S2
    [heuredebut_definie_periode] => 14:25:00
    [heurefin_definie_periode] => 15:22:00
    [suivi_definie_periode] => 1
    [type_creneaux] => cours
    [jour_creneau] => 
    [type_cours] => standard
)

stdClass Object
(
    [id_cours] => 15
    [id_groupe] => 4059
    [id_salle] => Salle 14
    [jour_semaine] => lundi
    [id_definie_periode] => remplacement			???????????
    [duree] => 2
    [heuredeb_dec] => 0
    [id_semaine] => 0
    [id_calendrier] => 0
    [modif_edt] => 3
    [login_prof] => BOIREAUS
    [id_aid] => 0
    [nom_definie_periode] => 2			???????????
    [heuredebut_definie_periode] => M2			???????????
    [heurefin_definie_periode] => 08:55:00
    [suivi_definie_periode] => 09:52:00			???????????
    [type_creneaux] => 1			???????????
    [jour_creneau] => cours			???????????
    [type_cours] => 
)

mysql> show fields from abs_prof_remplacement
    -> ;
+-------------------------+--------------+------+-----+---------+----------------+
| Field                   | Type         | Null | Key | Default | Extra          |
+-------------------------+--------------+------+-----+---------+----------------+
| id                      | int(11)      | NO   | PRI | NULL    | auto_increment |
| id_absence              | int(11)      | NO   |     | NULL    |                |
| id_groupe               | int(11)      | NO   |     | NULL    |                |
| id_aid                  | int(11)      | NO   |     | NULL    |                |
| id_classe               | int(11)      | NO   |     | NULL    |                |
| jour                    | char(8)      | NO   |     | NULL    |                |
| id_creneau              | int(11)      | NO   |     | NULL    |                |
| date_debut_r            | datetime     | NO   |     | NULL    |                |
| date_fin_r              | datetime     | NO   |     | NULL    |                |
| reponse                 | varchar(30)  | NO   |     | NULL    |                |
| date_reponse            | datetime     | NO   |     | NULL    |                |
| login_user              | varchar(50)  | NO   |     | NULL    |                |
| commentaire_prof        | text         | NO   |     | NULL    |                |
| validation_remplacement | varchar(30)  | NO   |     | NULL    |                |
| commentaire_validation  | text         | NO   |     | NULL    |                |
| salle                   | varchar(100) | NO   |     | NULL    |                |
| texte_famille           | text         | NO   |     | NULL    |                |
| info_famille            | varchar(10)  | NO   |     | NULL    |                |
| duree                   | varchar(10)  | NO   |     | 0       |                |
| heuredeb_dec            | varchar(3)   | NO   |     | 0       |                |
| jour_semaine            | varchar(10)  | NO   |     | NULL    |                |
| id_cours_remplaced      | int(11)      | NO   |     | NULL    |                |
+-------------------------+--------------+------+-----+---------+----------------+
22 rows in set (0.01 sec)

mysql> show fields from edt_cours;
+--------------------+-------------+------+-----+---------+----------------+
| Field              | Type        | Null | Key | Default | Extra          |
+--------------------+-------------+------+-----+---------+----------------+
| id_cours           | int(11)     | NO   | PRI | NULL    | auto_increment |
| id_groupe          | varchar(10) | NO   |     |         |                |
| id_salle           | char(3)     | NO   |     |         |                |
| jour_semaine       | varchar(10) | NO   |     |         |                |
| id_definie_periode | char(3)     | NO   |     |         |                |
| duree              | varchar(10) | NO   |     | 2       |                |
| heuredeb_dec       | char(3)     | NO   |     | 0       |                |
| id_semaine         | varchar(10) | NO   |     | 0       |                |
| id_calendrier      | char(3)     | NO   |     | 0       |                |
| modif_edt          | char(3)     | NO   |     | 0       |                |
| login_prof         | varchar(50) | NO   |     |         |                |
| id_aid             | char(10)    | YES  |     |         |                |
+--------------------+-------------+------+-----+---------+----------------+
12 rows in set (0.00 sec)

mysql> 

*/

		$res=mysqli_query($GLOBALS["mysqli"], $sql);
		while($lig=mysqli_fetch_object($res)) {
			if($debug_edt=="y") {
				echo "<div style='margin-left:1000px;color:orange;'><pre>";
				print_r($lig);
				echo "</pre></div>";
			}
			$tab_debut=explode(":", $lig->heuredebut_definie_periode);
			$heure_debut=$tab_debut[0];
			$min_debut=$tab_debut[1];
			$sec_debut=0;
			$ts_debut=mktime($heure_debut,$min_debut,$sec_debut,$mois_debut_jour,$jour_debut_jour,$annee_debut_jour);
			$horaire_debut=$heure_debut.":".$min_debut;

			$tab_fin=explode(":", $lig->heurefin_definie_periode);
			$heure_fin=$tab_fin[0];
			$min_fin=$tab_fin[1];
			$sec_fin=0;
			$ts_fin=mktime($heure_fin,$min_fin,$sec_fin,$mois_debut_jour,$jour_debut_jour,$annee_debut_jour);

			// Problème avec les cours à cheval sur les créneaux de 1/2h du midi.
			//$duree_courante=(($ts_fin-$ts_debut)/60)*($lig->duree/2);
			$duree_courante=60*($lig->duree/2);

			if($lig->heuredeb_dec=="0.5") {
				$ts_debut+=ceil(($ts_fin-$ts_debut)/2);
			}

			$horaire_cours_courant="\nDébut du cours : ".strftime("%H:%M", $ts_debut)."";
			$horaire_cours_courant.="\nDurée du cours : ".$duree_courante."minutes";

			$duree_depuis_debut_journee=floor(10*($ts_debut-$ts_debut_jour)/3600)/10;
			$y_courant=$y0+$hauteur_entete+$duree_depuis_debut_journee*$hauteur_une_heure+ceil($marge_secu/2);

			//$hauteur_courante=$hauteur_une_heure*floor(10*($ts_fin-$ts_debut)/3600)/10-ceil($marge_secu/2);
			$hauteur_courante=floor($hauteur_une_heure*$lig->duree/2)-$marge_secu;

			$largeur_courante=$largeur_jour-$marge_secu;

			if($affichage=="semaine") {
				$x_courant=$x0+$largeur_jour*($num_jour-1)+ceil($marge_secu/2);
			}
			else {
				$x_courant=$x0+ceil($marge_secu/2);
			}

			if($debug_edt=="y") {
				$html.="---<br />";
				$html.="\$jour_debut_jour=$jour_debut_jour<br />";
				$html.="\$ts_debut_jour=$ts_debut_jour<br />";
				$html.="\$ts_debut=$ts_debut<br />";
				$html.="\$duree_depuis_debut_journee=$duree_depuis_debut_journee<br />";
				$html.="y_courant=$y_courant<br />";


				if($lig->id_cours==24608) {
					echo "<div style='margin-left:500px'>";
					echo "<pre>";
					print_r($lig);
					echo "</pre>";
					echo "id_classe=$id_classe et check_ts_vacances($ts_debut+600,$id_classe)=".check_ts_vacances($ts_debut+600,$id_classe)."<br />";
					echo "</div>";
				}
			}

			$cpt_courant=0;
			if(isset($tab_cours[$num_jour]['y'][$y_courant])) {
				$cpt_courant=count($tab_cours[$num_jour]['y'][$y_courant]);
			}
			// On ne l'affecte que plus loin si on affiche le div
			//$tab_cours[$num_jour]['y'][$y_courant][$cpt_courant]['hauteur']=$hauteur_courante;

			if(($ts_debut+600>$ts_fin_annee)||($ts_debut-600<$ts_debut_annee)) {
				if($debug_edt=="y") {
					echo "<p style='color:red'>Hors année scolaire</p>";
				}
				$tab_cours[$num_jour]['y'][$y_courant][$cpt_courant]['hauteur']=$hauteur_courante;

				$bgcolor_courant="silver";

				$contenu_cellule="Hors année scolaire";

				$tab_cours[$num_jour]['y'][$y_courant][$cpt_courant]['contenu_cellule']=$contenu_cellule;
				$tab_cours[$num_jour]['y'][$y_courant][$cpt_courant]['bgcolor_cellule']=$bgcolor_courant;
			}
			elseif(($id_classe!="")&&(check_ts_vacances($ts_debut+600,$id_classe))) {
				if($debug_edt=="y") {
					echo "<p style='color:red'>Classe définie $id_classe et en période de vacances</p>";
				}
				$tab_cours[$num_jour]['y'][$y_courant][$cpt_courant]['hauteur']=$hauteur_courante;

				$bgcolor_courant="silver";

				$contenu_cellule=nom_ts_vacances($ts_debut+600,$id_classe);

				$tab_cours[$num_jour]['y'][$y_courant][$cpt_courant]['contenu_cellule']=$contenu_cellule;
				$tab_cours[$num_jour]['y'][$y_courant][$cpt_courant]['bgcolor_cellule']=$bgcolor_courant;
			}
			else {
				$afficher_l_enseignement="y";

				$chaine_nom_enseignement="";
				$chaine_matiere="";
				$chaine_liste_classes="";

				$chaine_noms_profs="";
				$chaine_proflist_string="";

				$chaine_salle_courante="";
				$chaine_salle_courante_span_title="";

				$chaine_type_semaine_du_cours_courant="";
				if(("$lig->id_semaine"!="")&&("$lig->id_semaine"!="0")) {
					$chaine_type_semaine_du_cours_courant="\nSemaine : ".$lig->id_semaine;

					if(($avec_contrainte_semaine!="")&&("$lig->id_semaine"!="$type_semaine")) {
						$afficher_l_enseignement="n";
					}
				}
				/*
				else {
					$chaine_type_semaine_du_cours_courant="\nSemaine : standard";
				}
				*/

				$chaine_texte_ligne_1="";
				//if($afficher_l_enseignement=="y") {
					//$tab_cours[$num_jour]['y'][$y_courant][$cpt_courant]['hauteur']=$hauteur_courante;

					//DEBUG:
					if($debug_edt=="y") {
						$html.="\$lig->id_cours=$lig->id_cours<br />";
						$html.="\$lig->id_groupe=$lig->id_groupe<br />";
						$html.="\$avec_contrainte_semaine=$avec_contrainte_semaine<br />";
						$html.="\$lig->id_semaine=$lig->id_semaine et \$type_semaine=$type_semaine<br />";
					}

					if(($lig->id_groupe!="")&&($lig->id_groupe!="0")) {
						if(!isset($tab_group_edt[$lig->id_groupe])) {
							$tab_group_edt[$lig->id_groupe]=get_group($lig->id_groupe, array('matieres', 'classes', 'profs'));
						}

						$id_classe_1ere_du_groupe="";
						if(isset($tab_group_edt[$lig->id_groupe]["classes"]["list"][0])) {
							$id_classe_1ere_du_groupe=$tab_group_edt[$lig->id_groupe]["classes"]["list"][0];
						}

						if(($id_classe_1ere_du_groupe!="")&&(check_ts_vacances($ts_debut+600,$id_classe_1ere_du_groupe))) {
							$tab_cours[$num_jour]['y'][$y_courant][$cpt_courant]['hauteur']=$hauteur_courante;

							$bgcolor_courant="silver";

							$contenu_cellule=nom_ts_vacances($ts_debut+600,$id_classe_1ere_du_groupe);

							$tab_cours[$num_jour]['y'][$y_courant][$cpt_courant]['contenu_cellule']=$contenu_cellule;
							$tab_cours[$num_jour]['y'][$y_courant][$cpt_courant]['bgcolor_cellule']=$bgcolor_courant;
						}
						else {

							$chaine_type_semaine_du_cours_courant="";
							if(("$lig->id_semaine"!="")&&("$lig->id_semaine"!="0")) {
								$chaine_type_semaine_du_cours_courant="\nSemaine : ".$lig->id_semaine;

								if(($avec_contrainte_semaine!="")&&("$lig->id_semaine"!="$type_semaine")) {
									$afficher_l_enseignement="n";
								}
							}

							//DEBUG:
							if($debug_edt=="y") {
								$html.="\$afficher_l_enseignement=$afficher_l_enseignement<br />";
							}

							if($afficher_l_enseignement=="y") {
								// 20170724
								$tab_cours[$num_jour]['y'][$y_courant][$cpt_courant]['ts_debut']=$ts_debut;
								if($debug_edt=="y") {
									$html.="\$tab_cours[$num_jour]['y'][$y_courant][$cpt_courant]['ts_debut']=".$tab_cours[$num_jour]['y'][$y_courant][$cpt_courant]['ts_debut']."<br />";
								}

								$tab_cours[$num_jour]['y'][$y_courant][$cpt_courant]['hauteur']=$hauteur_courante;

								$current_group=$tab_group_edt[$lig->id_groupe];

								$chaine_nom_enseignement="Inconnu ".$lig->id_groupe;
								$chaine_matiere="Matière Inconnue";
								if((isset($current_group['name']))&&(isset($current_group['description']))) {
									$chaine_nom_enseignement=$current_group['name']." (".$current_group['description'].")";
								}

								if(isset($current_group['classlist_string'])) {
									$chaine_nom_enseignement.=" en ".$current_group['classlist_string'];
								}
								if(isset($current_group['proflist_string'])) {
									$chaine_nom_enseignement.=" avec ".$current_group['profs']['proflist_string'];
								}

								if(isset($current_group['matiere']['matiere'])) {
									$chaine_matiere=$current_group['matiere']['matiere'];
								}

								// 20160919
								//$chaine_texte_ligne_1=$chaine_matiere;
								//$chaine_texte_ligne_1=$current_group['name'];

								// 20170128
								if((!isset($lig->type_cours))||($lig->type_cours!="remplacement")) {
									$tab_cours[$num_jour]['y'][$y_courant][$cpt_courant]['type_cours']="standard";

									$chaine_texte_ligne_1="Inconnu";
									if(isset($current_group['name'])) {
										$chaine_texte_ligne_1=preg_replace("/[_.]/"," ",$current_group['name']);
									}

									$chaine_proflist_string="";
									if(isset($current_group['profs']['proflist_string'])) {
										$chaine_proflist_string=$current_group['profs']['proflist_string'];
									}

									if(isset($current_group['matiere']['matiere'])) {
										if(!isset($tab_couleur_matiere[$current_group['matiere']['matiere']])) {
											$tab_couleur_matiere[$current_group['matiere']['matiere']]=get_couleur_edt_matiere($current_group['matiere']['matiere']);
										}
									}
									// 20160919
									if($type_affichage=="prof") {
										$bgcolor_courant=get_couleur_edt_prof($lig->id_groupe);
									}
									else {
										$bgcolor_courant=$tab_couleur_matiere[$current_group['matiere']['matiere']];
									}

									$cpt_prof=0;
									if(isset($current_group['profs']['users'])) {
										foreach($current_group['profs']['users'] as $current_prof_login => $current_prof) {
											if($cpt_prof>0) {
												$chaine_noms_profs.=", ";
											}
											$chaine_noms_profs.=$current_prof['nom'];

											$cpt_prof++;
										}
									}
									//DEBUG: 20170228
									//$chaine_texte_ligne_1.=" ".$lig->id_cours;
									if(array_key_exists($lig->id_cours,$tab_mes_cours_remplaces_par_d_autres)) {
										$chaine_infos_remplacement='';
										foreach($tab_mes_cours_remplaces_par_d_autres[$lig->id_cours] as $current_id_cours => $current_remplacement) {
											if(($current_remplacement['login_user']!='')&&(!isset($tab_prof[$current_remplacement['login_user']]))) {
												$sql="SELECT * FROM utilisateurs WHERE login='".$current_remplacement['login_user']."';";
												$res_prof=mysqli_query($GLOBALS["mysqli"], $sql);
												if(mysqli_num_rows($res_prof)>0) {
													$lig_prof=mysqli_fetch_object($res_prof);
													$tab_prof[$current_remplacement['login_user']]['nom']=$lig_prof->nom;
													$tab_prof[$current_remplacement['login_user']]['designation']=$lig_prof->civilite." ".$lig_prof->nom." ".mb_substr($lig_prof->prenom,0,1);
												}
												else {
													$tab_prof[$current_remplacement['login_user']]['nom']="...";
													$tab_prof[$current_remplacement['login_user']]['designation']="...";
												}
											}

											$chaine_infos_remplacement.="Remplacé par ".$tab_prof[$current_remplacement['login_user']]['designation']."\nentre ".formate_date($current_remplacement['date_debut_r'], 'y2')."\net ".formate_date($current_remplacement['date_fin_r'], 'y2').".\n";
										}

										$chaine_texte_ligne_1="<span title=\"".$chaine_infos_remplacement."\">Remplacé</span>";

										if(!isset($tab_prof[$lig->login_prof])) {
											$sql="SELECT * FROM utilisateurs WHERE login='".$lig->login_prof."';";
											$res_prof=mysqli_query($GLOBALS["mysqli"], $sql);
											if(mysqli_num_rows($res_prof)>0) {
												$lig_prof=mysqli_fetch_object($res_prof);
												$tab_prof[$lig->login_prof]['nom']=$lig_prof->nom;
												$tab_prof[$lig->login_prof]['designation']=$lig_prof->civilite." ".$lig_prof->nom." ".mb_substr($lig_prof->prenom,0,1);
											}
											else {
												$tab_prof[$lig->login_prof]['nom']="...";
												$tab_prof[$lig->login_prof]['designation']="...";
											}
										}
										$chaine_noms_profs=$tab_prof[$lig->login_prof]['nom'];
										$chaine_proflist_string=$chaine_noms_profs;

										$bgcolor_courant=$couleur_remplaced;
									}
									elseif(in_array($lig->id_cours, $tab_abs_prof)) {
										$chaine_texte_ligne_1="<span title=\"Le professeur est absent.\">Absence</span>";

										if(!isset($tab_prof[$lig->login_prof])) {
											$sql="SELECT * FROM utilisateurs WHERE login='".$lig->login_prof."';";
											$res_prof=mysqli_query($GLOBALS["mysqli"], $sql);
											if(mysqli_num_rows($res_prof)>0) {
												$lig_prof=mysqli_fetch_object($res_prof);
												$tab_prof[$lig->login_prof]['nom']=$lig_prof->nom;
												$tab_prof[$lig->login_prof]['designation']=$lig_prof->civilite." ".$lig_prof->nom." ".mb_substr($lig_prof->prenom,0,1);
											}
											else {
												$tab_prof[$lig->login_prof]['nom']="...";
												$tab_prof[$lig->login_prof]['designation']="...";
											}
										}
										$chaine_noms_profs=$tab_prof[$lig->login_prof]['nom'];
										$chaine_proflist_string=$chaine_noms_profs;

										$bgcolor_courant=$couleur_remplaced;
									}
								}
								else {
									$bgcolor_courant=$couleur_remplacement;

									$tab_cours[$num_jour]['y'][$y_courant][$cpt_courant]['type_cours']="remplacement";

									// RCD : Remplacement de Courte Durée
									$chaine_texte_ligne_1="<span title='Remplacement ponctuel'>Remplacement</span>";

									if(!isset($tab_prof[$lig->login_prof])) {
										$sql="SELECT * FROM utilisateurs WHERE login='".$lig->login_prof."';";
										$res_prof=mysqli_query($GLOBALS["mysqli"], $sql);
										if(mysqli_num_rows($res_prof)>0) {
											$lig_prof=mysqli_fetch_object($res_prof);
											$tab_prof[$lig->login_prof]['nom']=$lig_prof->nom;
											$tab_prof[$lig->login_prof]['designation']=$lig_prof->civilite." ".$lig_prof->nom." ".mb_substr($lig_prof->prenom,0,1);
										}
										else {
											$tab_prof[$lig->login_prof]['nom']="...";
											$tab_prof[$lig->login_prof]['designation']="...";
										}
									}
									$chaine_noms_profs=$tab_prof[$lig->login_prof]['nom'];
									$chaine_proflist_string=$chaine_noms_profs;

									//$chaine_proflist_string=get_valeur_champ("utilisateurs", "login='".$lig->login_prof."'", "nom");
								}

								if(isset($current_group['classlist_string'])) {
									$chaine_liste_classes=$current_group['classlist_string'];
								}

							}
						}
					}
					elseif($lig->id_aid!="") {
						// A FAIRE Remplir un $tab_edt_aid pour ne pas faire plusieurs fois les mêmes requêtes:

						$id_classe_1ere_de_l_aid="";
						$sql="SELECT DISTINCT c.* FROM classes c, 
											j_eleves_classes jec, 
											j_aid_eleves jae 
										WHERE c.id=jec.id_classe AND 
											jec.login=jae.login AND 
											jae.id_aid='".$lig->id_aid."';";
						$res_aid_classe=mysqli_query($GLOBALS["mysqli"], $sql);
						if(mysqli_num_rows($res_aid_classe)>0) {
							$lig_aid_classe=mysqli_fetch_object($res_aid_classe);
							$id_classe_1ere_de_l_aid=$lig_aid_classe->id;
						}

						if(($id_classe_1ere_de_l_aid!="")&&(check_ts_vacances($ts_debut+600,$id_classe_1ere_de_l_aid))) {
							$tab_cours[$num_jour]['y'][$y_courant][$cpt_courant]['hauteur']=$hauteur_courante;

							$bgcolor_courant="silver";

							$contenu_cellule=nom_ts_vacances($ts_debut+600,$id_classe_1ere_de_l_aid);

							$tab_cours[$num_jour]['y'][$y_courant][$cpt_courant]['contenu_cellule']=$contenu_cellule;
							$tab_cours[$num_jour]['y'][$y_courant][$cpt_courant]['bgcolor_cellule']=$bgcolor_courant;
						}
						else {

							$chaine_type_semaine_du_cours_courant="";
							if(("$lig->id_semaine"!="")&&("$lig->id_semaine"!="0")) {
								$chaine_type_semaine_du_cours_courant="\nSemaine : ".$lig->id_semaine;

								if(($avec_contrainte_semaine!="")&&("$lig->id_semaine"!="$type_semaine")) {
									$afficher_l_enseignement="n";
								}
							}

							if($afficher_l_enseignement=="y") {
								$tab_cours[$num_jour]['y'][$y_courant][$cpt_courant]['hauteur']=$hauteur_courante;

								if(!isset($tab_aid_edt[$lig->id_aid])) {
									$sql="SELECT a.nom AS nom_aid, ac.nom, ac.nom_complet FROM aid a, 
																			aid_config ac 
																		WHERE a.indice_aid=ac.indice_aid AND 
																			a.id='".$lig->id_aid."';";
									$res_aid=mysqli_query($GLOBALS["mysqli"], $sql);
									if(mysqli_num_rows($res_aid)==0) {
										$tab_aid_edt[$lig->id_aid]['nom_general_court']="AID";
										$tab_aid_edt[$lig->id_aid]['nom_general_complet']="AID";
										$tab_aid_edt[$lig->id_aid]['nom_aid']="AID";
										$tab_aid_edt[$lig->id_aid]['proflist_string']="...";
									}
									else {
										$lig_aid=mysqli_fetch_object($res_aid);

										$tab_aid_edt[$lig->id_aid]['nom_general_court']=$lig_aid->nom;
										$tab_aid_edt[$lig->id_aid]['nom_general_complet']=$lig_aid->nom_complet;
										$tab_aid_edt[$lig->id_aid]['nom_aid']=$lig_aid->nom_aid;

										$sql="SELECT u.civilite, u.nom, u.prenom FROM utilisateurs u, j_aid_utilisateurs jau 
																			WHERE u.login=jau.id_utilisateur AND 
																				jau.id_aid='".$lig->id_aid."'
																			ORDER BY u.nom, u.prenom;";
										$res_aid_prof=mysqli_query($GLOBALS["mysqli"], $sql);
										if(mysqli_num_rows($res_aid_prof)==0) {
											$tab_aid_edt[$lig->id_aid]['proflist_string']="...";
										}
										else {
											$tab_aid_edt[$lig->id_aid]['proflist_string']="";
											$cpt_aid_prof=0;
											while($lig_aid_prof=mysqli_fetch_object($res_aid_prof)) {
												if($cpt_aid_prof>0) {
													$tab_aid_edt[$lig->id_aid]['proflist_string'].=", ";
												}
												$tab_aid_edt[$lig->id_aid]['proflist_string'].=$lig_aid_prof->civilite." ".$lig_aid_prof->nom." ".mb_substr($lig_aid_prof->prenom,0,1);
												$cpt_aid_prof++;
											}
										}
									}
								}

								if(!isset($tab_prof[$lig->login_prof])) {
									$sql="SELECT * FROM utilisateurs WHERE login='".$lig->login_prof."';";
									$res_prof=mysqli_query($GLOBALS["mysqli"], $sql);
									if(mysqli_num_rows($res_prof)>0) {
										$lig_prof=mysqli_fetch_object($res_prof);
										$tab_prof[$lig->login_prof]['nom']=$lig_prof->nom;
										$tab_prof[$lig->login_prof]['designation']=$lig_prof->civilite." ".$lig_prof->nom." ".mb_substr($lig_prof->prenom,0,1);
									}
									else {
										$tab_prof[$lig->login_prof]['nom']="...";
										$tab_prof[$lig->login_prof]['designation']="...";
									}
								}
								$chaine_noms_profs=$tab_prof[$lig->login_prof]['nom'];
								$current_aid=$tab_aid_edt[$lig->id_aid];
								$chaine_proflist_string=$current_aid['proflist_string'];

								// 20170128
								if((!isset($lig->type_cours))||($lig->type_cours!="remplacement")) {
									$tab_cours[$num_jour]['y'][$y_courant][$cpt_courant]['type_cours']="standard";

									$bgcolor_courant="azure";

									//$current_aid=$tab_aid_edt[$lig->id_aid];

									$chaine_nom_enseignement=$current_aid['nom_aid']." (".$current_aid['nom_general_court'].") (".$current_aid['nom_general_complet'].") avec ".$current_aid['proflist_string'];

									$chaine_matiere=$current_aid['nom_aid'];
									// 20160919
									$chaine_texte_ligne_1=$chaine_matiere;

									if(array_key_exists($lig->id_cours,$tab_mes_cours_remplaces_par_d_autres)) {
										$chaine_infos_remplacement='';
										foreach($tab_mes_cours_remplaces_par_d_autres[$lig->id_cours] as $current_id_cours => $current_remplacement) {
											if(($current_remplacement['login_user']!='')&&(!isset($tab_prof[$current_remplacement['login_user']]))) {
												$sql="SELECT * FROM utilisateurs WHERE login='".$current_remplacement['login_user']."';";
												$res_prof=mysqli_query($GLOBALS["mysqli"], $sql);
												if(mysqli_num_rows($res_prof)>0) {
													$lig_prof=mysqli_fetch_object($res_prof);
													$tab_prof[$current_remplacement['login_user']]['nom']=$lig_prof->nom;
													$tab_prof[$current_remplacement['login_user']]['designation']=$lig_prof->civilite." ".$lig_prof->nom." ".mb_substr($lig_prof->prenom,0,1);
												}
												else {
													$tab_prof[$current_remplacement['login_user']]['nom']="...";
													$tab_prof[$current_remplacement['login_user']]['designation']="...";
												}
											}

											$chaine_infos_remplacement.="Remplacé par ".$tab_prof[$current_remplacement['login_user']]['designation']."\nentre ".formate_date($current_remplacement['date_debut_r'], 'y2')."\net ".formate_date($current_remplacement['date_fin_r'], 'y2').".\n";
										}

										if(!isset($tab_prof[$lig->login_prof])) {
											$sql="SELECT * FROM utilisateurs WHERE login='".$lig->login_prof."';";
											$res_prof=mysqli_query($GLOBALS["mysqli"], $sql);
											if(mysqli_num_rows($res_prof)>0) {
												$lig_prof=mysqli_fetch_object($res_prof);
												$tab_prof[$lig->login_prof]['nom']=$lig_prof->nom;
												$tab_prof[$lig->login_prof]['designation']=$lig_prof->civilite." ".$lig_prof->nom." ".mb_substr($lig_prof->prenom,0,1);
											}
											else {
												$tab_prof[$lig->login_prof]['nom']="...";
												$tab_prof[$lig->login_prof]['designation']="...";
											}
										}
										$chaine_noms_profs=$tab_prof[$lig->login_prof]['nom'];
										$chaine_proflist_string=$chaine_noms_profs;

										$bgcolor_courant=$couleur_remplaced;
									}
								}
								else {
									$tab_cours[$num_jour]['y'][$y_courant][$cpt_courant]['type_cours']="remplacement";

									$bgcolor_courant=$couleur_remplacement;

									$chaine_matiere="";
									// RCD : Remplacement de Courte Durée
									$chaine_texte_ligne_1="<span title='Remplacement ponctuel'>Remplacement</span>";

									//$chaine_proflist_string=get_valeur_champ("utilisateurs", "login='".$lig->login_prof."'", "nom");
									//$chaine_noms_profs=$chaine_proflist_string;
								}
							}
						}
					}
					else {
						// On ne devrait pas passer là

						$tab_cours[$num_jour]['y'][$y_courant][$cpt_courant]['hauteur']=$hauteur_courante;

						if(!isset($tab_prof[$lig->login_prof])) {
							$sql="SELECT * FROM utilisateurs WHERE login='".$lig->login_prof."';";
							$res_prof=mysqli_query($GLOBALS["mysqli"], $sql);
							if(mysqli_num_rows($res_prof)>0) {
								$lig_prof=mysqli_fetch_object($res_prof);
								$tab_prof[$lig->login_prof]['nom']=$lig_prof->nom;
								$tab_prof[$lig->login_prof]['designation']=$lig_prof->civilite." ".$lig_prof->nom." ".mb_substr($$lig_prof->prenom,0,1);
							}
							else {
								$tab_prof[$lig->login_prof]['nom']="...";
								$tab_prof[$lig->login_prof]['designation']="...";
							}
						}
						$chaine_noms_profs=$tab_prof[$lig->login_prof]['nom'];
						$chaine_proflist_string=$tab_prof[$lig->login_prof]['designation'];

						// 20170128
						if((!isset($lig->type_cours))||($lig->type_cours!="remplacement")) {
							$tab_cours[$num_jour]['y'][$y_courant][$cpt_courant]['type_cours']="bizarre";

							$bgcolor_courant="white";

							$chaine_nom_enseignement="Cours...";

							$chaine_matiere="Matière";
							// 20160919
							$chaine_texte_ligne_1=$chaine_matiere;

							if(array_key_exists($lig->id_cours,$tab_mes_cours_remplaces_par_d_autres)) {

								$chaine_infos_remplacement='';
								foreach($tab_mes_cours_remplaces_par_d_autres[$lig->id_cours] as $current_id_cours => $current_remplacement) {
									if(($current_remplacement['login_user']!='')&&(!isset($tab_prof[$current_remplacement['login_user']]))) {
										$sql="SELECT * FROM utilisateurs WHERE login='".$current_remplacement['login_user']."';";
										$res_prof=mysqli_query($GLOBALS["mysqli"], $sql);
										if(mysqli_num_rows($res_prof)>0) {
											$lig_prof=mysqli_fetch_object($res_prof);
											$tab_prof[$current_remplacement['login_user']]['nom']=$lig_prof->nom;
											$tab_prof[$current_remplacement['login_user']]['designation']=$lig_prof->civilite." ".$lig_prof->nom." ".mb_substr($lig_prof->prenom,0,1);
										}
										else {
											$tab_prof[$current_remplacement['login_user']]['nom']="...";
											$tab_prof[$current_remplacement['login_user']]['designation']="...";
										}
									}

									$chaine_infos_remplacement.="Remplacé par ".$tab_prof[$current_remplacement['login_user']]['designation']."\nentre ".formate_date($current_remplacement['date_debut_r'], 'y2')."\net ".formate_date($current_remplacement['date_fin_r'], 'y2').".\n";
								}

								if(!isset($tab_prof[$lig->login_prof])) {
									$sql="SELECT * FROM utilisateurs WHERE login='".$lig->login_prof."';";
									$res_prof=mysqli_query($GLOBALS["mysqli"], $sql);
									if(mysqli_num_rows($res_prof)>0) {
										$lig_prof=mysqli_fetch_object($res_prof);
										$tab_prof[$lig->login_prof]['nom']=$lig_prof->nom;
										$tab_prof[$lig->login_prof]['designation']=$lig_prof->civilite." ".$lig_prof->nom." ".mb_substr($lig_prof->prenom,0,1);
									}
									else {
										$tab_prof[$lig->login_prof]['nom']="...";
										$tab_prof[$lig->login_prof]['designation']="...";
									}
								}
								$chaine_noms_profs=$tab_prof[$lig->login_prof]['nom'];
								$chaine_proflist_string=$chaine_noms_profs;

								$bgcolor_courant=$couleur_remplaced;
							}

						}
						else {
							$tab_cours[$num_jour]['y'][$y_courant][$cpt_courant]['type_cours']="remplacement";

							$bgcolor_courant=$couleur_remplacement;

							$chaine_matiere="";
							// RCD : Remplacement de Courte Durée
							$chaine_texte_ligne_1="<span title='Remplacement ponctuel'>Remplacement</span>";

							//$chaine_proflist_string=get_valeur_champ("utilisateurs", "login='".$lig->login_prof."'", "nom");
							//$chaine_noms_profs=$chaine_proflist_string;
						}
					}
				//}

				if($chaine_texte_ligne_1!="") {
					//DEBUG:
					//echo "\$chaine_texte_ligne_1=$chaine_texte_ligne_1<br />";

					if(isset($tab_salle['indice'][$lig->id_salle])) {
						$chaine_salle_courante_span_title=" en salle ".$tab_salle['indice'][$lig->id_salle]['designation_complete'];
						$chaine_salle_courante="<br /><span style='font-size:".$font_size3."pt;' title=\"Salle ".$tab_salle['indice'][$lig->id_salle]['designation_complete']."\">".$tab_salle['indice'][$lig->id_salle]['designation_courte']."</span>";
					}

					if($type_affichage=="prof") {
						$liste_classes="";
						if($chaine_liste_classes!="") {
							$liste_classes="<br />".$chaine_liste_classes;
						}
	// 20160919: permettre d'afficher le nom de groupe (au moins le début)
						//$contenu_cellule="<span style='font-size:".$font_size."pt;' title=\"".$chaine_nom_enseignement.$chaine_salle_courante_span_title.$chaine_type_semaine_du_cours_courant.$horaire_cours_courant."\">".$chaine_matiere."</span>".$liste_classes.$chaine_salle_courante;
						$contenu_cellule="<span style='font-size:".$font_size."pt;' title=\"".$chaine_nom_enseignement.$chaine_salle_courante_span_title.$chaine_type_semaine_du_cours_courant.$horaire_cours_courant."\">".$chaine_texte_ligne_1."</span>".$liste_classes.$chaine_salle_courante;
					}
					else {
						/*
						$contenu_cellule="<span style='font-size:".$font_size."pt;' title=\"".$chaine_nom_enseignement.$chaine_salle_courante_span_title.$chaine_type_semaine_du_cours_courant.$horaire_cours_courant."\">".$chaine_matiere."</span><br />
					<span style='font-size:".$font_size2."pt;' title=\"".$chaine_proflist_string."\">".$chaine_noms_profs."</span>".$chaine_salle_courante;
						*/
						$contenu_cellule="<span style='font-size:".$font_size."pt;' title=\"".$chaine_nom_enseignement.$chaine_salle_courante_span_title.$chaine_type_semaine_du_cours_courant.$horaire_cours_courant."\">".$chaine_texte_ligne_1."</span><br />
					<span style='font-size:".$font_size2."pt;' title=\"".$chaine_proflist_string."\">".$chaine_noms_profs."</span>".$chaine_salle_courante;
					}

					if(($lig->id_semaine!='0')&&($lig->id_semaine!='')) {
						$contenu_cellule.=" <span class='fieldset_opacite50' style='float:right; font-size:".$font_size2."pt;' title=\"Semaine ".$lig->id_semaine."\">".$lig->id_semaine."</span>";
					}



					$tab_cours[$num_jour]['y'][$y_courant][$cpt_courant]['matiere']=$chaine_matiere;
					$tab_cours[$num_jour]['y'][$y_courant][$cpt_courant]['prof']=$chaine_noms_profs;
					$tab_cours[$num_jour]['y'][$y_courant][$cpt_courant]['salle']=$chaine_salle_courante;
					$tab_cours[$num_jour]['y'][$y_courant][$cpt_courant]['id_cours']=$lig->id_cours;
					$tab_cours[$num_jour]['y'][$y_courant][$cpt_courant]['id_classe']=$id_classe;
					if(!array_key_exists($id_classe, $tab_nom_classe)) {
						$tab_nom_classe[$id_classe]=get_nom_classe($id_classe);
					}
					$tab_cours[$num_jour]['y'][$y_courant][$cpt_courant]['classe']=$tab_nom_classe[$id_classe];
					$tab_cours[$num_jour]['y'][$y_courant][$cpt_courant]['horaire_debut']=$horaire_debut;
					// Problème avec l'heure de fin calculée avec les créneaux.
					//$tab_cours[$num_jour]['y'][$y_courant][$cpt_courant]['horaire_fin']=$horaire_fin;


					//$contenu_cellule.=" ".$hauteur_courante;
					//$contenu_cellule.=" ".$font_size;

					$tab_cours[$num_jour]['y'][$y_courant][$cpt_courant]['contenu_cellule']=$contenu_cellule;
					$tab_cours[$num_jour]['y'][$y_courant][$cpt_courant]['bgcolor_cellule']=$bgcolor_courant;
				}
			}
		}

		if(isset($tab_cours[$num_jour])) {
			if($debug_edt=="y") {
				echo "<br />---<p>\$tab_cours[$num_jour]</p><pre>";
				print_r($tab_cours[$num_jour]);
				echo "</pre>";
			}

			// +++++++++++++++++++++++++++
			// +++++++++++++++++++++++++++
			// On force ça pour le moment:
			//$type_edt="classe";
			// +++++++++++++++++++++++++++
			// +++++++++++++++++++++++++++
			//if($type_edt=="classe") {

				$tab_collisions=array();

				$tab_collisions2=array();

				//foreach($tab_cours as $num_jour => $tab) {
				$tab=$tab_cours[$num_jour];
					/*
					if($num_jour==0) {
						echo "\$tab_cours[$num_jour]<pre>";
						print_r($tab_cours[$num_jour]);
						echo "</pre>";
					}
					*/

					foreach($tab['y'] as $y_courant => $tab2) {
						for($loop=0;$loop<count($tab2);$loop++) {
							$hauteur_courante=$tab2[$loop]['hauteur'];
							$y_courant_fin=$y_courant+$hauteur_courante;

							if(isset($tab2[$loop]['id_cours'])) {
								$id_cours_courant=$tab2[$loop]['id_cours'];

								foreach($tab_cours[$num_jour]['y'] as $y3=> $tab3_cours) {
									/*
									echo "y3=$y3<pre>";
									print_r($tab3_cours);
									echo "</pre>";
									*/

									for($loop3=0;$loop3<count($tab3_cours);$loop3++) {
										if((isset($tab3_cours[$loop3]['id_cours']))&&($tab3_cours[$loop3]['id_cours']!=$id_cours_courant)) {
											$y_test_debut=$y3;
											$y_test_fin=$y3+$tab3_cours[$loop3]['hauteur'];
											$id_cours_test=$tab3_cours[$loop3]['id_cours'];

											if(($y_test_debut>=$y_courant)&&($y_test_debut<$y_courant_fin)) {
												if((!isset($tab_collisions[$id_cours_courant]))||(!in_array($id_cours_test, $tab_collisions[$id_cours_courant]))) {
													$tab_collisions[$id_cours_courant][]=$id_cours_test;
												}
											}
											elseif(($y_courant>=$y_test_debut)&&($y_courant<$y_test_fin)) {
												if((!isset($tab_collisions[$id_cours_courant]))||(!in_array($id_cours_test, $tab_collisions[$id_cours_courant]))) {
													$tab_collisions[$id_cours_courant][]=$id_cours_test;
												}
											}

										}
									}

								}
							}

							/*
							// DEBUG
							if($id_cours_courant==602) {
								echo "<div style='position:absolute; top:800px; left:1000px;'>";
								echo "\$tab_collisions[$id_cours_courant]<pre>";
								print_r($tab_collisions[$id_cours_courant]);
								echo "</pre>";
								echo "</div>";
							}
							if($id_cours_courant==603) {
								echo "<div style='position:absolute; top:900px; left:1000px;'>";
								echo "\$tab_collisions[$id_cours_courant]<pre>";
								print_r($tab_collisions[$id_cours_courant]);
								echo "</pre>";
								echo "</div>";
							}
							*/

						}
					}
				//}
						
				$tab_coord_prises=array();
				//foreach($tab_cours as $num_jour => $tab) {
				$tab=$tab_cours[$num_jour];
					foreach($tab['y'] as $y_courant => $tab2) {
						for($loop=0;$loop<count($tab2);$loop++) {
							//$hauteur_courante=$tab2[$loop]['hauteur'];
							$hauteur_courante=$tab2[$loop]['hauteur']-floor($marge_secu/2);

							/*
							$title="".$tab2[$loop]['matiere']['nom_complet'];
							if($tab2[$loop]['prof']['designation']!="") {
								$title.=" avec ".$tab2[$loop]['prof']['designation'];
							}
							if($tab2[$loop]['salle']!="") {
								$title.=" en salle ".$tab2[$loop]['salle'];
							}
							$title.="\nDe ".$tab2[$loop]['horaire_debut']." à ".$tab2[$loop]['horaire_fin'].".";
							*/

							$contenu_courant_ajout="";
							$text_color="";
							if(isset($tab2[$loop]['id_cours'])) {
								$id_cours_courant=$tab2[$loop]['id_cours'];

								//$x_courant=$x_jour[$num_jour]+$marge_secu;
								$x_courant=$x_jour[$loop_jour]+$marge_secu;
								$largeur_courante=$largeur_jour-2*$marge_secu;
								//$text_color="";
								//$font_size="";

								$style_font_size1=" style='font-size:normal;'";
								$style_font_size2=" style='font-size:x-small;'";
								//$contenu_courant_ajout="";
								if(isset($tab_collisions[$id_cours_courant])) {
									/*
									// DEBUG
									if($id_cours_courant==602) {
										echo "<div style='position:absolute; top:1000px; left:1000px;'>";
										echo "\$id_cours_courant=$id_cours_courant";
										echo "</div>";
									}
									if($id_cours_courant==603) {
										echo "<div style='position:absolute; top:1100px; left:1000px;'>";
										echo "\$id_cours_courant=$id_cours_courant";
										echo "</div>";
									}
									*/

									$style_font_size1=" style='font-size:x-small;'";
									$style_font_size2=" style='font-size:xx-small;'";

									if($debug_edt=="y") {
										$contenu_courant_ajout.="<br />nb_col=".count($tab_collisions[$id_cours_courant]);
									}

									// Compter les collisions effectives
									$nb=count($tab_collisions[$id_cours_courant]);
									foreach($tab_collisions[$id_cours_courant] as $id_cours_test) {
										$nb=min($nb,count($tab_collisions[$id_cours_test]));
									}
									// DEBUG
									if($debug_edt=="y") {
										$contenu_courant_ajout.="<br />nb_reel=".$nb;
									}
									// Largeur du div de ce cours
									//$largeur_courante=floor($largeur_jour/($nb+1))-2*$marge_secu;
									// On donne au moins 1px de large... par sécurité
									//$largeur_courante=max(floor($largeur_jour/($nb+1))-2*$marge_secu,1);
									$largeur_courante=max(floor($largeur_jour/($nb+1))-1*$marge_secu,1);


									//$font_size="font-size:x-small;";
									//$font_size="font-size:smaller;";
									$tmp_tab=array();
									$tmp_tab[]=$id_cours_courant;
									//foreach($tab_collisions as $tmp_current_id_cours => $tmp_current_id_cours_collision) {
									foreach($tab_collisions[$id_cours_courant] as $tmp_current_id_cours_collision) {
										$tmp_tab[]=$tmp_current_id_cours_collision;
									}
									sort($tmp_tab);

									$chaine="";
									for($loop2=0;$loop2<count($tmp_tab);$loop2++) {
										if($chaine!="") {
											$chaine.="|";
										}
										$chaine.=$tmp_tab[$loop2];
									}

									//while($x_courant<$x_jour[$num_jour]+$largeur_jour) {
									while($x_courant<$x_jour[$loop_jour]+$largeur_jour) {
										/*
										if(($id_cours_courant==602)||($id_cours_courant==603)) {
											$contenu_courant_ajout.="\$id_cours_courant=$id_cours_courant, \$x_courant=$x_courant et \$x_jour[$loop_jour]+$largeur_jour=".$x_jour[$loop_jour]."+".$largeur_jour."<br />\n";
										}
										*/
										if(check_pas_de_collision($x_courant,$y_courant,$x_courant+$largeur_courante,$y_courant+$hauteur_courante)) {
											$text_color="";
											break;
										}
										else {
											$x_courant+=$largeur_courante+floor($marge_secu/2);
											//$x_courant+=floor($largeur_jour/($nb+1))+$marge_secu;
											//$x_courant+=floor($largeur_jour/($nb+1));
											$text_color="color:red;";
										}
									}

									if($text_color!="") {
										//$x_courant=$x_jour[$num_jour];
										$x_courant=$x_jour[$loop_jour];
									}

									$tab_coord_prises[]=$x_courant.",".$y_courant.",".($x_courant+$largeur_courante).",".($y_courant+$hauteur_courante);
									if($debug_edt=="y") {
										$title.="\nCoordonnées : ".$x_courant.",".$y_courant.",".($x_courant+$largeur_courante).",".($y_courant+$hauteur_courante);
									}
								}



							}




								/*
								$contenu_courant="<span title=\"$title\"$style_font_size1>".$tab2[$loop]['matiere']['matiere']."</span>";

								// Ne pas inclure ce qui suit pour l'emploi du temps du prof
								if($type_edt!="prof") {
									$contenu_courant.="<br /><span$style_font_size2 title=\"".$tab2[$loop]['prof']['designation']."\">".$tab2[$loop]['prof']['nom']."</span>";
								}
								else {
									$contenu_courant.="<br /><span$style_font_size2 title=\"".$tab2[$loop]['classe']."\">".$tab2[$loop]['classe']."</span>";
								}
								// Ne pas inclure ce qui suit pour l'emploi du temps d'une salle
								$contenu_courant.="<br /><span$style_font_size2 title=\"Salle ".$tab2[$loop]['salle']."\">".$tab2[$loop]['salle']."</span>";

								if($debug_edt=="y") {
									$contenu_courant.="<br />id_cours=".$id_cours_courant;
								}

								$contenu_courant.=$contenu_courant_ajout;

								// Fond blanc pour masquer les lignes d'heures
								$html.="<div id='div_fond_masque_cours_".$tab2[$loop]['id_cours']."' style='position:absolute; top:".$y_courant."px; left:".$x_courant."px; width:".$largeur_courante."px; height:".$hauteur_courante."px; background-color:white; z-index:18; '></div>";

								// Cadre de couleur avec une opacité réglable
								if(!isset($tab_couleur_matiere[$tab2[$loop]['matiere']['matiere']])) {
									$tab_couleur_matiere[$tab2[$loop]['matiere']['matiere']]=get_couleur_edt_matiere($tab2[$loop]['matiere']['matiere']);
								}
								$couleur_courante=$tab_couleur_matiere[$tab2[$loop]['matiere']['matiere']];
								$html.="<div id='div_fond_couleur_cours_".$tab2[$loop]['id_cours']."' style='position:absolute; top:".$y_courant."px; left:".$x_courant."px; width:".$largeur_courante."px; height:".$hauteur_courante."px; border:1px solid black; background-color:".$couleur_courante."; opacity:$opacity_couleur; z-index:19; text-align:center;".$text_color.$font_size."' title='$title'></div>";

								// Cadre du contenu de la cellule
								$html.="<div id='div_texte_cours_".$tab2[$loop]['id_cours']."' style='position:absolute; top:".$y_courant."px; left:".$x_courant."px; width:".$largeur_courante."px; height:".$hauteur_courante."px; border:1px solid black; z-index:20; text-align:center; overflow:hidden; ".$text_color.$font_size."' title='$title'>".$contenu_courant."</div>";
								*/

								/*
								//DEBUG:
								if(!isset($tab2[$loop]['bgcolor_cellule'])) {
								echo "<pre style='color:red'>";
								print_r($tab2[$loop]);
								echo "</pre>";
								}
								*/

								$html.="\n";
								$html.="\n";
								$html.="<!-- ======================================================= -->\n";
								// Cadre de couleur avec une opacité réglable
								$html.="<div style='position:absolute; 
										top:".$y_courant."px; 
										left:".$x_courant."px; 
										width:".$largeur_courante."px; 
										height:".$hauteur_courante."px; 
										text-align:center; 
										border:1px solid black; 
										background-color:".$tab2[$loop]['bgcolor_cellule'].";
										opacity:$opacity_couleur; 
										z-index:19;'></div>";
								$html.="\n";
								// Cadre du contour de la cellule
								$html.="<div style='position:absolute; 
										top:".$y_courant."px; 
										left:".$x_courant."px; 
										width:".$largeur_courante."px; 
										height:".$hauteur_courante."px; 
										text-align:center; 
										border:1px solid black; 
										line-height:".$font_size."pt;
										z-index:20;'>"."</div>";
								// Cadre du contenu de la cellule
								$decalage_vertical=floor($marge_secu/2);
								if($hauteur_courante>$hauteur_une_heure) {
									$decalage_vertical=floor(($hauteur_courante-$hauteur_une_heure)/2);
								}
								$html.="\n";
								$html.="<div style='position:absolute; 
										top:".($y_courant+$decalage_vertical)."px; 
										left:".$x_courant."px; 
										width:".$largeur_courante."px; 
										height:".($hauteur_courante-$decalage_vertical)."px; 
										text-align:center; 
										line-height:".$font_size."pt;
										overflow: hidden;
										z-index:21;'";
								if((isset($tab2[$loop]['id_cours']))&&((!isset($tab2[$loop]['type_cours']))||($tab2[$loop]['type_cours']!="remplacement"))) {
									//alert('".$tab2[$loop]['type_cours']."');
									$html.="
										onclick=\"action_edt_cours('".$tab2[$loop]['id_cours']."', '".(isset($tab2[$loop]['ts_debut']) ? $tab2[$loop]['ts_debut'] : "")."')\"";
								}
								elseif((isset($tab2[$loop]['type_cours']))&&($tab2[$loop]['type_cours']=="remplacement")) {
									$html.="
										onclick=\"cacher_div('infobulle_action_edt')\"";
									/*
									$html.="
										title=\"\"";
									*/
								}

								if($debug_edt=="y") {
									$html.="
										title=\"";
									foreach($tab2[$loop] as $key => $value) {
										$html.="\$tab2[$loop][$key]=".(htmlentities($value))."\n";
									}
									$html.="\"";
								}

								$html.=">";
								// DEBUG:
								$chaine_debug="";
								//$chaine_debug="<span style='font-size:xx-small;color:lime;'>".$y0."+".$hauteur_entete."+".$y_courant."=".($y0+$hauteur_entete+$y_courant)."</span> ";
								$html.=$chaine_debug;
								$html.=$tab2[$loop]['contenu_cellule']." ".$contenu_courant_ajout." ".$text_color."</div>";

							//}

						}

					}
				//}

			//}
		}
	}

	/*
	echo "<div style='position:absolute; top:1500px;'>";
	echo "\$tab_cours[5]<pre>";
	print_r($tab_cours[5]);
	echo "</pre>";
	echo "</div>";
	*/

	//==================================================================

	if((isset($affichage_complementaire_sur_edt))&&($affichage_complementaire_sur_edt=="absences2")) {
		$html.=affiche_abs2_sur_edt2();
	}

	$html.="</div>";

	return $html;
}

function affiche_abs2_sur_edt2() {
	global $login_eleve;
	global $jours;
	global $largeur_jour;
	global $hauteur_jour;
	global $x_jour;
	global $premiere_heure;
	global $marge_secu;
	global $hauteur_titre;
	global $debug_edt;
	global $tabdiv_infobulle, $tabid_infobulle;

	global $x0, $y0, $largeur_edt, $hauteur_une_heure;
	global $hauteur_entete;

	$html="";

	if(isset($login_eleve)) {
		foreach($jours['num_jour'] as $num_jour => $current_jour) {
			if(!isset($ts_1er_jour)) {
				$ts_1er_jour=mktime(0, 0, 0, $current_jour['mm'], $current_jour['jj'], $current_jour['aaaa']);
				$mysqldate_1er_jour=$current_jour['aaaa']."-".$current_jour['mm']."-".$current_jour['jj']." 00:00:00";
			}
			$ts_dernier_jour=mktime(23, 59, 59, $current_jour['mm'], $current_jour['jj'], $current_jour['aaaa']);
			$mysqldate_dernier_jour=$current_jour['aaaa']."-".$current_jour['mm']."-".$current_jour['jj']." 23:59:59";
		}

		$tab_abs=array();
		$sql="SELECT * FROM a_saisies a, eleves e 
					WHERE e.id_eleve=a.eleve_id AND 
						e.login='".$login_eleve."' AND 
						((a.debut_abs>='".$mysqldate_1er_jour."' AND a.debut_abs<='".$mysqldate_dernier_jour."') OR 
						(a.fin_abs>='".$mysqldate_1er_jour."' AND a.fin_abs<='".$mysqldate_dernier_jour."') OR 
						(a.debut_abs<='".$mysqldate_1er_jour."' AND a.fin_abs>='".$mysqldate_dernier_jour."'));";
		//$html.="$sql<br />";
		$res=mysqli_query($GLOBALS["mysqli"], $sql);
		if(mysqli_num_rows($res)==0) {
			$html.="<p style='color:red'>Aucune saisie d'absence trouvée.</p>";
		}
		else {
			$cpt=0;
			while($lig=mysqli_fetch_assoc($res)) {
				// *******************************************************
				// *******************************************************
				// A FAIRE : Il faudrait récupérer l'info englobée ou non.
				// *******************************************************
				// *******************************************************
				$tab_abs[$cpt]=$lig;
				$id_saisie=$lig['id'];

				$tab_abs[$cpt]['englobee']="n";

				$saisie = AbsenceEleveSaisieQuery::create()->includeDeleted()->findPk($id_saisie);
				if ($saisie == null) {
					$tab_abs[$cpt]['trouvee']="n";
				}
				else {
					$tab_abs[$cpt]['trouvee']="y";

					$saisies_englobante_col = $saisie->getAbsenceEleveSaisiesEnglobantes();
					if(!$saisies_englobante_col->isEmpty()) {
						$tab_abs[$cpt]['englobee']="y";
					}
				}
				$cpt++;
			}
		}
		/*
		echo "tab_abs<pre>";
		print_r($tab_abs);
		echo "</pre>";
		*/

		// Afficher ou non les saisies englobées.
		$afficher_englobee="y";

		//$y_div_conteneur=$y1;
		$y_div_conteneur=$y0;
		//$y_div_conteneur=$y_decalage_2_js;
		//$html.="\$y_div_conteneur=$y_div_conteneur<br />";

		$cpt_abs=0;
		foreach($tab_abs as $cpt => $current_abs) {

			$ts_debut_abs=mysql_date_to_unix_timestamp($current_abs['debut_abs']);
			$ts_fin_abs=mysql_date_to_unix_timestamp($current_abs['fin_abs']);
			//$aaaammjj_debut_abs=strftime("%Y%m%d", $ts_debut_abs);

			// Il faut boucler sur les jours inclus dans l'absence
			// ERREUR : Si l'absence dure plus d'une semaine... SAUF QU'ON N'AFFICHE QU'UNE SEMAINE A LA FOIS SUR L'EDT
			foreach($jours['num_jour'] as $num_jour => $current_jour) {
				if(($ts_fin_abs>$current_jour['timestamp'])&&($ts_debut_abs<=$current_jour['timestamp']+3600*24-1)) {
					//$x=$x1+$x0+($num_jour-1)*$largeur_jour+$marge_secu/2;
					$x=$x0+($num_jour-1)*$largeur_jour+$marge_secu/2;

					//$ts_debut_1er_jour_absence=mktime(0,0,0,strftime("%m", $ts_debut_abs),strftime("%d", $ts_debut_abs),strftime("%Y", $ts_debut_abs));
					$ts_debut_jour=mktime(0,0,0,strftime("%m", $current_jour['timestamp']),strftime("%d", $current_jour['timestamp']),strftime("%Y", $current_jour['timestamp']));

					if($ts_debut_abs<$current_jour['timestamp']) {
						// L'absence débute avant ce jour
						// On va prendre l'heure de début de journée
						//$y=$y_div_conteneur+$hauteur_entete+$hauteur_titre;
						$y=$y_div_conteneur+$hauteur_entete;
					}
					elseif($ts_debut_abs<$current_jour['timestamp']+3600*24-1) {
						// L'absence débute dans la journée
						$delta=($ts_debut_abs-$ts_debut_jour-3600*$premiere_heure)/3600;

						//$y=$y_div_conteneur+$hauteur_entete+$hauteur_titre+$delta*$hauteur_une_heure;
						$y=$y_div_conteneur+$hauteur_entete+$delta*$hauteur_une_heure;
					}

					// Fin du div
					if($ts_fin_abs>$current_jour['timestamp']+3600*24-1) {
						//$y_fin=$y_div_conteneur+$hauteur_entete+$hauteur_titre+$hauteur_jour;
						$y_fin=$y_div_conteneur+$hauteur_entete+$hauteur_jour;

						if((isset($tab_jour[$num_jour-1]))&&(isset($tab_horaire_jour[$tab_jour[$num_jour-1]]))) {
							$tmp_tab=explode(":", $tab_horaire_jour[$tab_jour[$num_jour-1]]['fermeture_horaire_etablissement']);
							$heure=$tmp_tab[0];
							$minute=$tmp_tab[1];
							$seconde=$tmp_tab[2];
							$ts_fin_journee_cours=mktime($heure,$minute,$seconde,strftime("%m", $ts_debut_jour),strftime("%d", $ts_debut_jour),strftime("%Y", $ts_debut_jour));
							$delta=($ts_fin_journee_cours-$ts_debut_jour-3600*$premiere_heure)/3600;

							//$y_fin=$y_div_conteneur+$hauteur_entete+$hauteur_titre+$delta*$hauteur_une_heure;
							$y_fin=$y_div_conteneur+$hauteur_entete+$delta*$hauteur_une_heure;
						}
					}
					else {
						// L'absence finit dans la journée
						$delta=($ts_fin_abs-$ts_debut_jour-3600*$premiere_heure)/3600;

						//$y_fin=$y_div_conteneur+$hauteur_entete+$hauteur_titre+$delta*$hauteur_une_heure;
						$y_fin=$y_div_conteneur+$hauteur_entete+$delta*$hauteur_une_heure;
					}

					// Pour éviter des collisions d'affichage lors du debug
					$decalage_x=0;
					$chaine_debug="";
					//$decalage_x=$largeur_jour/2;
					//$chaine_debug="<span style='font-size:xx-small; color:red'>".ceil($y)."</span>";

					// Pour les saisies englobées, il faudrait juste afficher un texte avec un lien/action ouvrant la saisie.
					if($current_abs['englobee']=='n') {
						$bgcolor="background-color:red; ";

						$sql="SELECT DISTINCT aty.* FROM a_types aty, 
											a_traitements atr, 
											j_traitements_saisies jts 
										WHERE aty.id=atr.a_type_id AND 
											atr.id=jts.a_traitement_id AND 
											jts.a_saisie_id='".$current_abs['id']."' 
										ORDER BY atr.updated_at DESC LIMIT 1;";
						$res_type_abs=mysqli_query($GLOBALS["mysqli"], $sql);
						if(mysqli_num_rows($res_type_abs)>0) {
							while($lig_type_abs=mysqli_fetch_object($res_type_abs)) {
								if($lig_type_abs->retard_bulletin=="VRAI") {
									$bgcolor="background-color:orange; ";
								}
								elseif(($lig_type_abs->sous_responsabilite_etablissement=="VRAI")&&($lig_type_abs->manquement_obligation_presence=="FAUX")) {
									$bgcolor="background-color:yellow; ";
								}
								elseif($lig_type_abs->manquement_obligation_presence=="FAUX") {
									$bgcolor="background-color:blue; ";
								}
							}
						}

						// Défaut: pour une absence courant sur plusieurs jours, on n'entoure que le div de la journée de début de la saisie englobante
						$chaine_mise_en_exergue="";
						$chaine_mise_en_exergue="onmouseover=\"document.getElementById('div_fond_abs_".$cpt_abs."').style.border='2px solid lime'\" onmouseout=\"document.getElementById('div_fond_abs_".$cpt_abs."').style.border='0px solid red'\" ";

						// Fond : Opacité 80
						$html.="<div id='div_fond_abs_".$cpt_abs."' style='position:absolute; top:".ceil($y)."px; left:".ceil($x)."px; width:".($largeur_jour-$marge_secu)."px; height:".floor($y_fin-$y)."px; opacity:0.8; ".$bgcolor." border:1px solid red; z-index:3000;' ".$chaine_mise_en_exergue." title=\"Saisie n°".$current_abs['id']."
	Du ".formate_date($current_abs['debut_abs'],"y","court")." au ".formate_date($current_abs['fin_abs'],"y","court")."\"></div>";
						//<a href='../mod_abs2/visu_saisie.php?id_saisie=".$current_abs['id']."' target='_blank'>".$current_abs['id']."</a>

						// Texte
						$html.="<div id='div_saisie_abs_".$cpt_abs."' style='position:absolute; top:".ceil($y)."px; left:".ceil($x+$decalage_x)."px; width:16px; height:16px; z-index:3001;'><a href='../mod_abs2/visu_saisie.php?id_saisie=".$current_abs['id']."' onclick=\"visu_saisie_abs_en_infobulle(".$current_abs['id'].");return false;\" target='_blank' title=\"Voir la saisie n°".$current_abs['id']."
	Du ".formate_date($current_abs['debut_abs'],"y","court")." au ".formate_date($current_abs['fin_abs'],"y","court")."\"><img src='../images/icons/saisie_1.png' class='icone16' alt='Saisie' /></a>".$chaine_debug."</div>";
					}
					else {
						$bgcolor="";

						if($afficher_englobee=="y") {
							// Texte
							$html.="<div id='div_saisie_abs_".$cpt_abs."' style='position:absolute; top:".ceil($y)."px; left:".ceil($x+$decalage_x)."px; width:".($largeur_jour-$marge_secu)."px; height:".floor($y_fin-$y)."px; ".$bgcolor."z-index:3001;'><a href='../mod_abs2/visu_saisie.php?id_saisie=".$current_abs['id']."' onclick=\"visu_saisie_abs_en_infobulle(".$current_abs['id'].");return false;\" target='_blank' title=\"Voir la saisie (englobée) n°".$current_abs['id']."
	Du ".formate_date($current_abs['debut_abs'],"y","court")." au ".formate_date($current_abs['fin_abs'],"y","court")."\"><img src='../images/icons/saisie.png' class='icone16' alt='Saisie' /></a>".$chaine_debug."</div>";
						}
					}

					$cpt_abs++;
				}
			}

		}

		/*
		echo "<div style='clear:both;'></div>";

		echo "<pre>";
		print_r($jours);
		echo "</pre>";

		*/

		$titre_infobulle="Saisie Absence";
		$texte_infobulle="<div id='div_visu_saisie_abs'></div>";
		$tabdiv_infobulle[]=creer_div_infobulle('infobulle_visu_saisie_abs',$titre_infobulle,"",$texte_infobulle,"",40,0,'y','y','n','n',4000);

		$html.="<script type='text/javascript'>
		function visu_saisie_abs_en_infobulle(id_saisie) {
			//alert('plop');
			new Ajax.Updater($('div_visu_saisie_abs'),'../lib/ajax_action.php?mode=visu_abs&id_saisie='+id_saisie,{method: 'get'});
			afficher_div('infobulle_visu_saisie_abs', 'y', 10,10);
		}

		function permuter_display_div_abs(mode) {
			if(mode=='') {
				if(document.getElementById('lien_permuter_display_abs_visible')) {
					document.getElementById('lien_permuter_display_abs_visible').style.display='none';
				}
				else {
					document.getElementById('lien_permuter_display_abs_visible').style.display='';
				}

				if(document.getElementById('lien_permuter_display_abs_invisible')) {
					document.getElementById('lien_permuter_display_abs_invisible').style.display='';
				}
				else {
					document.getElementById('lien_permuter_display_abs_invisible').style.display='none';
				}
			}
			else {
				if(document.getElementById('lien_permuter_display_abs_visible')) {
					document.getElementById('lien_permuter_display_abs_visible').style.display='';
				}
				else {
					document.getElementById('lien_permuter_display_abs_visible').style.display='none';
				}

				if(document.getElementById('lien_permuter_display_abs_invisible')) {
					document.getElementById('lien_permuter_display_abs_invisible').style.display='none';
				}
				else {
					document.getElementById('lien_permuter_display_abs_invisible').style.display='';
				}
			}

			for(i=0;i<$cpt_abs;i++) {
				if(document.getElementById('div_fond_abs_'+i)) {
					document.getElementById('div_fond_abs_'+i).style.display=mode;
				}
				if(document.getElementById('div_saisie_abs_'+i)) {
					document.getElementById('div_saisie_abs_'+i).style.display=mode;
				}
			}
		}
		permuter_display_div_abs('');
	</script>";
	}

	return $html;
}

function DimanchePaques ($annee) {
	//Calcul de la date de Pâques grégorienne en calendrier grégorien (1583-) (Algorithme de Butcher)'
	// $annee=2006;
	
	$n = $annee % 19; // Reste Année / 19
	// return $n ;
	$c = (int)($annee / 100); // Année \ 100
	// return $c ;
	$u = $annee % 100; // Reste Année / 100
	// return $u ;
	$s = (int)($c / 4); // Entier C / 4
	// return $s ;
	$t = $c % 4 ; // reste C / 4
	// return $t ;
	$p = (int)(($c + 8)/25); // Entier (C + 8)/25
	// return $p ;
	$q = (int)(($c-$p+1)/3); // Entier (c - p + 1)/3
	// return $q ;
	$e = (19*$n + $c - $s - $q + 15) % 30; // Reste (19 n + c - s - q + 15) / 30
	// return $e ;
	$b = (int)($u / 4); // entier u/4
	// return $b ;
	$d = $u % 4; // reste u/4
	// return $d ;
	$L = (32 + 2*$t + 2*$b - $e - $d) % 7; // Reste (32 + 2 t + 2 b - e – d)/7
	// return $L ;
	$h = (int)(($n + 11*$e + 22*$L)/451); // Entier (n + 11 e + 22 L)/451
	// return $h ;
	$mois = (int)(($e + $L - 7*$h +114)/31); // Entier (e + L - 7 h +114)/31
	$samedi = ($e + $L - 7*$h +114) % 31; // Reste (e + L - 7 h +114)/31
	$dimanche = mktime(0,0,0,$mois,$samedi+1,$annee);
	return ($dimanche);
}

function LundiPaques ($annee) {
	$lundi = DimanchePaques ($annee) + (24*3600);
	return $lundi ; 
}

function Ascension($annee) {
	$ascension = DimanchePaques ($annee) + (24*3600)*39;
	return $ascension ; 
}

function VendrediAscension($annee) {
	$vendrediAscension = Ascension($annee) + (24*3600);
	return $vendrediAscension ; 
}

function Pentecote($annee) {
	$pentecote = DimanchePaques ($annee) + (24*3600)*50;
	return $pentecote ;
}
function JourSuivant($jour) {
	$jour = $jour + (24*3600);
	return $jour ;
}

?>
