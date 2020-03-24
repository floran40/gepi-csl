<?php
/**
 *
 *
 * Copyright 2010-2018 Josselin Jacquard, Dupont G Ceeso
 *
 * This file and the mod_abs2 module is distributed under GPL version 3, or
 * (at your option) any later version.
 *
 * This file is part of GEPI.
 *
 * GEPI is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * GEPI is distributed in the hope that it will be useful,
 * but WIthOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with GEPI; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

// Initialisation des feuilles de style après modification pour améliorer l'accessibilité
$accessibilite="y";

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

//recherche de l'utilisateur avec propel
$utilisateur = UtilisateurProfessionnelPeer::getUtilisateursSessionEnCours();
if ($utilisateur == null) {
	header("Location: ../logout.php?auto=1");
	die();
}

//On vérifie si le module est activé
if (getSettingValue("active_module_absence")!='2') {
	die("Le module n'est pas activé.");
}

if(!getSettingAOui('abs2_liste_decompte_mariere')) {
	header("Location: ../logout.php?auto=1");
	die();
}

if ($utilisateur->getStatut()!="cpe" && $utilisateur->getStatut()!="scolarite") {
	die("acces interdit");
}

//debug_var();

//ajout gdu ++++++++
//initialisation des variables
$tblClasses=array();
$tblGroupes=array();
//ajout gdu --------

if (isset($_POST["creation_traitement"]) || isset($_POST["ajout_traitement"])) {
    include('creation_traitement.php');
}

if (isset($_POST["suppression_saisies"])) {
	AbsenceEleveSaisiePeer::disableVersioning();
	$select_saisie=isset($_POST["select_saisie"]) ? $_POST["select_saisie"] : NULL;
	$select_saisie2=array();
	if(isset($select_saisie)) {
		for($loop=0;$loop<count($select_saisie);$loop++) {
			$sql="SELECT a.*, e.login FROM a_saisies a, 
								eleves e 
							WHERE a.id='".$select_saisie[$loop]."' AND 
								a.eleve_id=e.id_eleve AND 
								a.id_groupe!='';";
			//echo "$sql<br />";
			$res=mysqli_query($GLOBALS['mysqli'], $sql);
			if(mysqli_num_rows($res)>0) {
				$lig=mysqli_fetch_object($res);

				$sql="SELECT 1=1 FROM j_eleves_groupes WHERE login='".$lig->login."' AND id_groupe='".$lig->id_groupe."';";
				//echo "$sql<br />";
				$res2=mysqli_query($GLOBALS['mysqli'], $sql);
				if(mysqli_num_rows($res2)==0) {
					$ts=strftime("%Y-%m-%d %H:%M:%S");
					$sql="UPDATE a_saisies SET deleted_by='".$_SESSION['login']."', 
										updated_at='".$ts."', 
										deleted_at='".$ts."' 
									WHERE id='".$lig->id."';";
					//echo "$sql<br />";
					$update=mysqli_query($GLOBALS['mysqli'], $sql);
				}
				else {
					$select_saisie2[$loop]=$select_saisie[$loop];
				}
			}
			else {
				$select_saisie2[$loop]=$select_saisie[$loop];
			}
		}
	}

    //$saisieCol = AbsenceEleveSaisieQuery::create()->filterByPrimaryKeys($_POST["select_saisie"])->find();
    $saisieCol = AbsenceEleveSaisieQuery::create()->filterByPrimaryKeys($select_saisie2)->find();
    foreach($saisieCol as $saisie) {
    	//echo "Suppression saisie n°".$saisie->getId()."<br />";
    	$saisie->delete();
    }
	AbsenceEleveSaisiePeer::enableVersioning();
} else if (isset($_POST["restauration_saisies"])) {
    AbsenceEleveSaisiePeer::disableVersioning();
    $saisieCol = AbsenceEleveSaisieQuery::create()->includeDeleted()->filterByPrimaryKeys($_POST["select_saisie"])->find();
    foreach($saisieCol as $saisie) {
    	$saisie->unDelete();
    }
    AbsenceEleveSaisiePeer::enableVersioning();
}

include('include_requetes_filtre_de_recherche.php');

include('include_pagination.php');


$rattachement_preselection=isset($_POST['rattachement_preselection']) ? $_POST['rattachement_preselection'] : (isset($_GET['rattachement_preselection']) ? $_GET['rattachement_preselection'] : NULL);
$id_eleve=isset($_POST['id_eleve']) ? $_POST['id_eleve'] : (isset($_GET['id_eleve']) ? $_GET['id_eleve'] : NULL);

$menu = isset($_POST["menu"]) ? $_POST["menu"] :(isset($_GET["menu"]) ? $_GET["menu"] : NULL);
//==============================================
$style_specifique[] = "mod_abs2/lib/abs_style";
$style_specifique[] = "lib/DHTMLcalendar/calendarstyle";
$javascript_specifique[] = "lib/DHTMLcalendar/calendar";
$javascript_specifique[] = "lib/DHTMLcalendar/lang/calendar-fr";
$javascript_specifique[] = "lib/DHTMLcalendar/calendar-setup";
$javascript_specifique[] = "mod_abs2/lib/include";
if(!$menu){
    $titre_page = "Les absences";
}
$utilisation_jsdivdrag = "non";
$_SESSION['cacher_header'] = "y";
$dojo = true;
require_once("../lib/header.inc.php");
//**************** FIN EN-TETE *****************

if(!$menu){
   include('menu_abs2.inc.php'); 
}

echo "<div class='css-panes' style='background-color:#cae7cb;' id='containDiv' style='overflow : none; float : left; margin-top : -1px; border-width : 1px;'>\n";


$query = AbsenceEleveSaisieQuery::create();
if(isset($_GET['saisies'])){
    $saisies=unserialize($_GET['saisies']);
    //on reinitialise les filtres au besoin
    $_SESSION['filtre_recherche'] = Array();
    $_SESSION['filtre_recherche']['order'] = 'des_id';
    $query->filterById($saisies);
}
//$query->leftJoin('AbsenceEleveSaisie.JTraitementSaisieEleve')->leftJoin('JTraitementSaisieEleve.AbsenceEleveTraitement')->with('AbsenceEleveTraitement');
if (isFiltreRechercheParam('filter_saisie_id')) {
    $query->filterById(getFiltreRechercheParam('filter_saisie_id'));    
}
if (isFiltreRechercheParam('filter_utilisateur')) {
    $query->useUtilisateurProfessionnelQuery()->filterByNom('%'.getFiltreRechercheParam('filter_utilisateur').'%', Criteria::LIKE)->endUse();
}
if (isFiltreRechercheParam('filter_eleve')) {
    $query->useEleveQuery()->filterByNomOrPrenomLike(getFiltreRechercheParam('filter_eleve'))->endUse();
}
if (isFiltreRechercheParam('filter_marqueur_appel')) {
    $query->filterByEleveId(null);
}
// filter_no_marqueur_appel
// Il faudrait un filterByEleveId is not null
if (isFiltreRechercheParam('filter_classe')) {
    $query->leftJoin('AbsenceEleveSaisie.Eleve');
    $query->leftJoin('Eleve.JEleveClasse');
    $query->condition('cond1', 'JEleveClasse.IdClasse = ?', getFiltreRechercheParam('filter_classe'));
    $query->condition('cond2', 'AbsenceEleveSaisie.IdClasse = ?', getFiltreRechercheParam('filter_classe'));
    $query->where(array('cond1', 'cond2'), 'or');
}
if (isFiltreRechercheParam('filter_groupe')) {
    $query->leftJoin('AbsenceEleveSaisie.Eleve');
    $query->leftJoin('Eleve.JEleveGroupe');
    $query->condition('cond1', 'JEleveGroupe.IdGroupe = ?', getFiltreRechercheParam('filter_groupe'));
    $query->condition('cond2', 'AbsenceEleveSaisie.IdGroupe = ?', getFiltreRechercheParam('filter_groupe'));
    $query->where(array('cond1', 'cond2'), 'or');
}
if (isFiltreRechercheParam('filter_aid')) {
    $query->leftJoin('AbsenceEleveSaisie.Eleve');
    $query->leftJoin('Eleve.JAidEleves');
    $query->condition('cond1', 'JAidEleves.IdAid = ?', getFiltreRechercheParam('filter_aid'));
    $query->condition('cond2', 'AbsenceEleveSaisie.IdAid = ?', getFiltreRechercheParam('filter_aid'));
    $query->where(array('cond1', 'cond2'), 'or');
}
if (isFiltreRechercheParam('filter_date_debut_saisie_debut_plage')) {
    $date_debut_saisie_debut_plage = new DateTime(str_replace("/",".",getFiltreRechercheParam('filter_date_debut_saisie_debut_plage')));
    $query->filterByDebutAbs($date_debut_saisie_debut_plage, Criteria::GREATER_EQUAL);
}
if (isFiltreRechercheParam('filter_date_debut_saisie_fin_plage')) {
    $date_debut_saisie_fin_plage = new DateTime(str_replace("/",".",getFiltreRechercheParam('filter_date_debut_saisie_fin_plage')));
    $query->filterByDebutAbs($date_debut_saisie_fin_plage, Criteria::LESS_EQUAL);
}
if (isFiltreRechercheParam('filter_date_fin_saisie_debut_plage')) {
    $date_fin_absence_debut_plage = new DateTime(str_replace("/",".",getFiltreRechercheParam('filter_date_fin_saisie_debut_plage')));
    $query->filterByFinAbs($date_fin_absence_debut_plage, Criteria::GREATER_EQUAL);
}
if (isFiltreRechercheParam('filter_date_fin_saisie_fin_plage')) {
    $date_fin_absence_fin_plage = new DateTime(str_replace("/",".",getFiltreRechercheParam('filter_date_fin_saisie_fin_plage')));
    $query->filterByFinAbs($date_fin_absence_fin_plage, Criteria::LESS_EQUAL);
}
if (isFiltreRechercheParam('filter_creneau')) {
    $query->filterByIdEdtCreneau(getFiltreRechercheParam('filter_creneau'));
}
if (isFiltreRechercheParam('filter_cours')) {
    $query->filterByIdEdtEmplacementCours(getFiltreRechercheParam('filter_cours'));
}
if (isFiltreRechercheParam('filter_date_creation_saisie_debut_plage')) {
    $date_creation_saisie_debut_plage = new DateTime(str_replace("/",".",getFiltreRechercheParam('filter_date_creation_saisie_debut_plage')));
    $query->filterByCreatedAt($date_creation_saisie_debut_plage, Criteria::GREATER_EQUAL);
}
if (isFiltreRechercheParam('filter_date_creation_saisie_fin_plage')) {
    $date_creation_saisie_fin_plage = new DateTime(str_replace("/",".",getFiltreRechercheParam('filter_date_creation_saisie_fin_plage')));
    $query->filterByCreatedAt($date_creation_saisie_fin_plage, Criteria::LESS_EQUAL);
}
if (isFiltreRechercheParam('filter_date_modification')) {
    $query->where('AbsenceEleveSaisie.CreatedAt != AbsenceEleveSaisie.UpdatedAt');
}
if (isFiltreRechercheParam('filter_date_traitement_absence_debut_plage')) {
    $date_traitement_absence_debut_plage = new DateTime(str_replace("/",".",getFiltreRechercheParam('filter_date_traitement_absence_debut_plage')));
    $query->leftJoin('AbsenceEleveSaisie.JTraitementSaisieEleve')->leftJoin('JTraitementSaisieEleve.AbsenceEleveTraitement')->where('AbsenceEleveTraitement.UpdatedAt >= ?', $date_traitement_absence_debut_plage);
}
if (isFiltreRechercheParam('filter_date_traitement_absence_fin_plage')) {
    $date_traitement_absence_fin_plage = new DateTime(str_replace("/",".",getFiltreRechercheParam('filter_date_traitement_absence_fin_plage')));
    $query->leftJoin('AbsenceEleveSaisie.JTraitementSaisieEleve')->leftJoin('JTraitementSaisieEleve.AbsenceEleveTraitement');
    $query->condition('trait1', 'AbsenceEleveTraitement.UpdatedAt <= ?', $date_traitement_absence_fin_plage);
    $query->condition('trait2', 'AbsenceEleveTraitement.UpdatedAt IS NULL');
    $query->where(array('trait1', 'trait2'), 'or');
}
if (isFiltreRechercheParam('filter_discipline')) {
    $query->filterByIdSIncidents(null, Criteria::NOT_EQUAL);
    $query->filterByIdSIncidents(-1, Criteria::NOT_EQUAL);
}
if (isFiltreRechercheParam('filter_type')) {
    if (getFiltreRechercheParam('filter_type') == 'SANS') {
	$query->groupById()
	    ->useJTraitementSaisieEleveQuery('', Criteria::LEFT_JOIN)
	    ->useAbsenceEleveTraitementQuery('', Criteria::LEFT_JOIN)
	    ->endUse()->endUse()
	    ->withColumn('group_concat(a_traitements.A_TYPE_ID)', 'a_types_id_concat');
	$criteria = new Criteria();
	$c = $criteria->getNewCriterion('a_types_id_concat', null, Criteria::ISNULL);
	$query->addHaving($c);
    } else {
	$query->useJTraitementSaisieEleveQuery()->useAbsenceEleveTraitementQuery()->filterByATypeId(getFiltreRechercheParam('filter_type'))->endUse()->endUse();
    }
}
if (isFiltreRechercheParam('filter_manqement_obligation')) {
    $query->filterByManquementObligationPresence(getFiltreRechercheParam('filter_manqement_obligation')=='y');
}
if (getFiltreRechercheParam('filter_saisies_supprimees')=='y') {
	$query->includeDeleted()->filterByDeletedAt(null,Criteria::ISNOTNULL);
	if (isFiltreRechercheParam('filter_date_suppression_saisie_debut_plage')) {
	    $date_suppression_saisie_debut_plage = new DateTime(str_replace("/",".",getFiltreRechercheParam('filter_date_suppression_saisie_debut_plage')));
	    $query->filterByDeletedAt($date_suppression_saisie_debut_plage, Criteria::GREATER_EQUAL);
	}
	if (isFiltreRechercheParam('filter_date_suppression_saisie_fin_plage')) {
	    $date_suppression_saisie_fin_plage = new DateTime(str_replace("/",".",getFiltreRechercheParam('filter_date_suppression_saisie_fin_plage')));
	    $query->filterByDeletedAt($date_suppression_saisie_fin_plage, Criteria::LESS_EQUAL);
	}
}
if (getFiltreRechercheParam('filter_saisies_globalement_non_justifiees')=='y') {
        $query->filterById(null, Criteria::ISNOTNULL);
	    //on va filtrer sur les 500 derniére saisies pour ne pas bloquer
	    $q_clone = clone $query;
	    $der_saisie = $q_clone->orderById(Criteria::DESC)->findOne();
	    if ($der_saisie != null) {
	        $limitId = $der_saisie->getId()-2000;
	    } else {
	        $limitId = 0;
	    }
	    
	    $q_clone = clone $query;
        $saisies = $q_clone->filterById($limitId,Criteria::GREATER_THAN)->distinct()->joinWith('Eleve')->leftJoinWith('AbsenceEleveSaisie.JTraitementSaisieEleve')->leftJoinWith('JTraitementSaisieEleve.AbsenceEleveTraitement')->find();
        //$saisies_2 = clone $saisies;
        $non_justif = Array();
        foreach ($saisies as $saisie) {//$Eleve = new Eleve();$Eleve->getAbsenceEleveSaisies()
            //$saisie->setAbsenceEleveSaisiesEnglobantes($saisie->filterAbsenceEleveSaisiesEnglobantes($saisie->getEleve()->getAbsenceEleveSaisies()));//on remplie déjà les saisies englobantes pour optimiser
            if ($saisie->getJustifieeEnglobante()) continue;
            $non_justif[] = $saisie->getId();
        }
        $query->filterById($non_justif);
}

if (getFiltreRechercheParam('filter_saisies_globalement_manquement')=='y') {
        $query->filterById(null, Criteria::ISNOTNULL);
	    //on va filtrer sur les 500 derniére saisies pour ne pas bloquer
	    $q_clone = clone $query;
	    $der_saisie = $q_clone->orderById(Criteria::DESC)->findOne();
	    if ($der_saisie != null) {
	        $limitId = $der_saisie->getId()-2000;
	    } else {
	        $limitId = 0;
	    }
	    
	    $q_clone = clone $query;
        $saisies = $q_clone->filterById($limitId,Criteria::GREATER_THAN)->distinct()->joinWith('Eleve')->leftJoinWith('AbsenceEleveSaisie.JTraitementSaisieEleve')->leftJoinWith('JTraitementSaisieEleve.AbsenceEleveTraitement')->find();
        //$saisies_2 = clone $saisies;
        $manque = Array();
        foreach ($saisies as $saisie) {//$Eleve = new Eleve();$Eleve->getAbsenceEleveSaisies()
            //$saisie->setAbsenceEleveSaisiesEnglobantes($saisie->filterAbsenceEleveSaisiesEnglobantes($saisie->getEleve()->getAbsenceEleveSaisies()));//on remplie déjà les saisies englobantes pour optimiser
            if (!$saisie->getManquementObligationPresenceEnglobante()) continue;
            $manque[] = $saisie->getId();
        }
        $query->filterById($manque);
}

if (getFiltreRechercheParam('filter_saisies_globalement_non_notifiees')=='y') {
        $query->filterById(null, Criteria::ISNOTNULL);
	    //on va filtrer sur les 500 derniére saisies pour ne pas bloquer
	    $q_clone = clone $query;
	    $der_saisie = $q_clone->orderById(Criteria::DESC)->findOne();
	    if ($der_saisie != null) {
	        $limitId = $der_saisie->getId()-2000;
	    } else {
	        $limitId = 0;
	    }
	    
	    $q_clone = clone $query;
        $saisies = $q_clone->filterById($limitId,Criteria::GREATER_THAN)->distinct()->joinWith('Eleve')->leftJoinWith('AbsenceEleveSaisie.JTraitementSaisieEleve')->leftJoinWith('JTraitementSaisieEleve.AbsenceEleveTraitement')->find();
        //$saisies_2 = clone $saisies;
        $manque = Array();
        foreach ($saisies as $saisie) {//$Eleve = new Eleve();$Eleve->getAbsenceEleveSaisies()
            //$saisie->setAbsenceEleveSaisiesEnglobantes($saisie->filterAbsenceEleveSaisiesEnglobantes($saisie->getEleve()->getAbsenceEleveSaisies()));//on remplie déjà les saisies englobantes pour optimiser
            if ($saisie->getNotifieeEnglobante()) continue;
            $manque[] = $saisie->getId();
        }
        $query->filterById($manque);
}

//on va filtrer sur les saisies possiblement rattachées à un traitement
$recherche_saisie_a_rattacher = getFiltreRechercheParam('filter_recherche_saisie_a_rattacher');
//récupération des paramètres de la requète
$id_traitement = isset($_POST["id_traitement"]) ? $_POST["id_traitement"] :(isset($_GET["id_traitement"]) ? $_GET["id_traitement"] :(isset($_SESSION["id_traitement"]) ? $_SESSION["id_traitement"] : NULL));
if (isset($id_traitement) && $id_traitement != null) $_SESSION['id_traitement'] = $id_traitement;
$traitement = AbsenceEleveTraitementQuery::create()->findPk($id_traitement);
if ($recherche_saisie_a_rattacher == 'oui' && $traitement != null) {

    // 20140429
    $traitement_recherche_saisie_a_rattacher=$traitement;

    $date_debut = null;
    $date_fin = null;
    $id_eleve_array = null;
    $id_saisie_array = null;
    foreach ($traitement->getAbsenceEleveSaisies() as $saisie) {//$saisie = new AbsenceEleveSaisie();
	if ($date_debut == null || $saisie->getDebutAbs('U') < $date_debut->format('U')) {
	    $date_debut = clone $saisie->getDebutAbs(null);
	}
	if ($date_fin == null || $saisie->getFinAbs('U') > $date_fin->format('U')) {
	    $date_fin = clone $saisie->getFinAbs(null);
	}
	$id_eleve_array[] = $saisie->getEleveId();
	$id_saisie_array[] = $saisie->getId();
    }
    if ($date_debut != null) date_date_set($date_debut, $date_debut->format('Y'), $date_debut->format('m'), $date_debut->format('d') - 1);
    if ($date_fin != null) date_date_set($date_fin, $date_fin->format('Y'), $date_fin->format('m'), $date_fin->format('d') + 1);
    $query->filterByPlageTemps($date_debut, $date_fin)->filterByEleveId($id_eleve_array)->filterById($id_saisie_array, Criteria::NOT_IN);
}


$order = getFiltreRechercheParam('order');
if ($order == "asc_id") {
    $query->orderBy('Id', Criteria::ASC);
} else if ($order == "des_id") {
    $query->orderBy('Id', Criteria::DESC);
} else if ($order == "asc_utilisateur") {
    $query->useUtilisateurProfessionnelQuery()->orderBy('Nom', Criteria::ASC)->endUse();
} else if ($order == "des_utilisateur") {
    $query->useUtilisateurProfessionnelQuery()->orderBy('Nom', Criteria::DESC)->endUse();
} else if ($order == "asc_eleve") {
    $query->useEleveQuery()->orderBy('Nom', Criteria::ASC)->orderBy('Prenom', Criteria::ASC)->endUse();
} else if ($order == "des_eleve") {
    $query->useEleveQuery()->orderBy('Nom', Criteria::DESC)->orderBy('Prenom', Criteria::DESC)->endUse();
} else if ($order == "asc_classe") {
    $query->useClasseQuery()->orderBy('NomComplet', Criteria::ASC)->endUse();
} else if ($order == "des_classe") {
    $query->useClasseQuery()->orderBy('NomComplet', Criteria::DESC)->endUse();
} else if ($order == "asc_groupe") {
    $query->useGroupeQuery()->orderBy('Name', Criteria::ASC)->endUse();
} else if ($order == "des_groupe") {
    $query->useGroupeQuery()->orderBy('Name', Criteria::DESC)->endUse();
} else if ($order == "asc_aid") {
    $query->useAidDetailsQuery()->orderBy('Nom', Criteria::ASC)->endUse();
} else if ($order == "des_aid") {
    $query->useAidDetailsQuery()->orderBy('Nom', Criteria::DESC)->endUse();
} else if ($order == "asc_date_debut") {
    $query->orderBy('DebutAbs', Criteria::ASC);
} else if ($order == "des_date_debut") {
    $query->orderBy('DebutAbs', Criteria::DESC);
} else if ($order == "asc_date_fin") {
    $query->orderBy('FinAbs', Criteria::ASC);
} else if ($order == "des_date_fin") {
    $query->orderBy('FinAbs', Criteria::DESC);
} else if ($order == "asc_creneau") {
    $query->useEdtCreneauQuery()->orderBy('HeuredebutDefiniePeriode', Criteria::ASC)->endUse();
} else if ($order == "des_creneau") {
    $query->useEdtCreneauQuery()->orderBy('HeuredebutDefiniePeriode', Criteria::DESC)->endUse();
} else if ($order == "asc_date_creation") {
    $query->orderBy('CreatedAt', Criteria::ASC);
} else if ($order == "des_date_creation") {
    $query->orderBy('CreatedAt', Criteria::DESC);
} else if ($order == "asc_date_modification") {
    $query->orderBy('UpdatedAt', Criteria::ASC);
} else if ($order == "des_date_modification") {
    $query->orderBy('UpdatedAt', Criteria::DESC);
} else if ($order == "asc_date_traitement") {
    $query->leftJoin('AbsenceEleveSaisie.JTraitementSaisieEleve')->leftJoin('JTraitementSaisieEleve.AbsenceEleveTraitement')->orderBy('AbsenceEleveTraitement.UpdatedAt', Criteria::ASC);
} else if ($order == "des_date_traitement") {
    $query->leftJoin('AbsenceEleveSaisie.JTraitementSaisieEleve')->leftJoin('JTraitementSaisieEleve.AbsenceEleveTraitement')->orderBy('AbsenceEleveTraitement.UpdatedAt', Criteria::DESC);
} else if ($order == "asc_dis") {
    $query->orderBy('IdSIncidents', Criteria::ASC);
} else if ($order == "des_dis") {
    $query->orderBy('IdSIncidents', Criteria::DESC);
} else if (getFiltreRechercheParam('filter_saisies_supprimees') == 'y') {
    if ($order == "asc_date_suppression") {
		$query->orderBy('DeletedAt', Criteria::ASC);
	} else if ($order == "des_date_suppression") {
		$query->orderBy('DeletedAt', Criteria::DESC);
	}
} else {
	$query->orderBy('Id', Criteria::DESC);
}
//$query->useAbsenceEleveSaisieQuery()->where('AbsenceEleveSaisie.Id in ?',saisie_col);
$query->distinct();
//echo "DISTINC => count query = ".$query->count()."<br>";
//pour etre sur que le calcul est fait sur toutes les saisies:
$item_per_page = $query->count();
$saisies_col = $query->paginate($page_number, $item_per_page);

$nb_pages = (floor($saisies_col->getNbResults() / $item_per_page) + 1);
if ($page_number > $nb_pages) {
    $page_number = $nb_pages;
}

if (isset($message_erreur_traitement)) {
    echo $message_erreur_traitement;
}
//echo "<table><tr><td>";
//echo var_dump($query);
//echo "</table></td></tr>";
echo '<form method="post" action="liste_saisies_selection_traitement_decompte_matiere.php" name="liste_saisies" id="liste_saisies">';
 echo '<input type="hidden" name="menu" value="'.$menu.'"/>';
?>    <div id="action_bouton" dojoType="dijit.form.DropDownButton" style="display: inline">
		<span>Action</span>
	<div dojoType="dijit.Menu" style="display: inline">
	    <button type="submit" dojoType="dijit.MenuItem" onClick="document.liste_saisies.submit();">
		Rechercher
	    </button>
	    <button type="submit" name="reinit_filtre" value="y" dojoType="dijit.MenuItem" onClick="
		//Create an input type dynamically.
		var element = document.createElement('input');
		element.setAttribute('type', 'hidden');
		element.setAttribute('name', 'reinit_filtre');
		element.setAttribute('value', 'y');
		document.liste_saisies.appendChild(element);
		document.liste_saisies.submit();
				">
		Réinitialiser les filtres
	    </button>
	    <button type="submit" name="creation_traitement" value="yes" dojoType="dijit.MenuItem" onClick="
		//Create an input type dynamically.
		var element = document.createElement('input');
		element.setAttribute('type', 'hidden');
		element.setAttribute('name', 'creation_traitement');
		element.setAttribute('value', 'yes');
		document.liste_saisies.appendChild(element);
		document.liste_saisies.submit();"
		<?php
                if (getFiltreRechercheParam('filter_saisies_supprimees')=='y') echo'disabled'
                ?> >
		Creer un nouveau traitement
	    </button>
	    <?php if ($traitement != null) { ?>
	    <button type="submit" name="creation_traitement" value="yes" dojoType="dijit.MenuItem" onClick="
		//Create an input type dynamically.
		var element = document.createElement('input');
		element.setAttribute('type', 'hidden');
		element.setAttribute('name', 'ajout_traitement');
		element.setAttribute('value', 'yes');
		document.liste_saisies.appendChild(element);
		var element = document.createElement('input');
		element.setAttribute('type', 'hidden');
		element.setAttribute('name', 'id_traitement');
		element.setAttribute('value', '<?php echo $id_traitement ?>');
		document.liste_saisies.appendChild(element);
		document.liste_saisies.submit();
				">
		Ajouter les saisies au traitement 
		<?php 
	    $desc = $traitement->getDescription();
	    if (mb_strlen($desc)>300) {
	    	echo mb_substr($desc,0,300).' ... ';
	    } else {
	    	echo $desc;
	    }
		?>
	    </button>
	    <?php } 
	    if (getFiltreRechercheParam('filter_saisies_supprimees') != 'y') {
	    ?>
	    <button type="submit" name="suppression_saisies" value="yes" dojoType="dijit.MenuItem" onClick="
		//Create an input type dynamically.
		var element = document.createElement('input');
		element.setAttribute('type', 'hidden');
		element.setAttribute('name', 'suppression_saisies');
		element.setAttribute('value', 'yes');
		document.liste_saisies.appendChild(element);
		document.liste_saisies.submit();
				">
		Supprimer la selection
	    </button>
	    <?php } else { ?>
	    <button type="submit" name="restauration_saisies" value="yes" dojoType="dijit.MenuItem" onClick="
		//Create an input type dynamically.
		var element = document.createElement('input');
		element.setAttribute('type', 'hidden');
		element.setAttribute('name', 'restauration_saisies');
		element.setAttribute('value', 'yes');
		document.liste_saisies.appendChild(element);
		document.liste_saisies.submit();
				">
		Restaurer la selection
	    </button>
	    <?php } ?>
	</div>
    </div>
<script language="javascript">
   //on cache les boutons pas très jolis en attendant le parsing dojo
   dojo.byId("action_bouton").hide();
</script>
<?php
if ($saisies_col->haveToPaginate()) {
    echo "Page ";
    echo '<input type="submit" name="page_deplacement" value="-"/>';
    echo '<input type="text" name="page_number" size="1" value="'.$page_number.'"/>';
    echo '<input type="submit" name="page_deplacement" value="+"/> ';
    echo "sur ".$nb_pages." ";
    echo "| ";
}
echo "Voir ";
echo '<input type="text" name="item_per_page" size="1" value="'.$item_per_page.'"/>';
echo "par page";
echo ' | <input type="checkbox" name="filter_saisies_supprimees" id="filter_saisies_supprimees" onchange="submit()" value="y"';
if (getFiltreRechercheParam('filter_saisies_supprimees') == 'y') {echo "checked='checked'";}
echo '/>';
if (getFiltreRechercheParam('filter_saisies_supprimees') == 'y') {echo '<font color="red">';}
echo '<label for="filter_saisies_supprimees">supprimées</label>';
if (getFiltreRechercheParam('filter_saisies_supprimees') == 'y') {echo '</font>';}

echo ' | <input type="checkbox" name="filter_saisies_globalement_manquement" id="filter_saisies_globalement_manquement" onchange="submit()" value="y"';
if (getFiltreRechercheParam('filter_saisies_globalement_manquement') == 'y') {echo "checked='checked'";}
echo '/>';
if (getFiltreRechercheParam('filter_saisies_globalement_manquement') == 'y') {echo '<font color="red">';}
echo '<label for="filter_saisies_globalement_manquement">manquement</label>';
if (getFiltreRechercheParam('filter_saisies_globalement_manquement') == 'y') {echo '</font>';}

echo ' | <input type="checkbox" name="filter_saisies_globalement_non_justifiees" id="filter_saisies_globalement_non_justifiees" onchange="submit()" value="y"';
if (getFiltreRechercheParam('filter_saisies_globalement_non_justifiees') == 'y') {echo "checked='checked'";}
echo '/>';
if (getFiltreRechercheParam('filter_saisies_globalement_non_justifiees') == 'y') {echo '<font color="red">';}
echo '<label for="filter_saisies_globalement_non_justifiees">non justifiées</label>';
if (getFiltreRechercheParam('filter_saisies_globalement_non_justifiees') == 'y') {echo '</font>';}

echo ' | <input type="checkbox" name="filter_saisies_globalement_non_notifiees" id="filter_saisies_globalement_non_notifiees" onchange="submit()" value="y"';
if (getFiltreRechercheParam('filter_saisies_globalement_non_notifiees') == 'y') {echo "checked='checked'";}
echo '/>';
if (getFiltreRechercheParam('filter_saisies_globalement_non_notifiees') == 'y') {echo '<font color="red">';}
echo '<label for="filter_saisies_globalement_non_notifiees">non notifiéés</label>';
if (getFiltreRechercheParam('filter_saisies_globalement_non_notifiees') == 'y') {echo '</font>';}
//ajout gdu +++++++++++++++
echo ' | <input type="checkbox" name"filter_saisies_cumul_matieres" id="filter_saisies_cumul_matieres" onchange="submit()" value="n"';
if (getFiltreRechercheParam('filter_saisies_cumul_matieres') == 'y') {echo "checked='checked'";}
echo '/>';
echo '<label for="filter_saisies_cumul_matieres">aff. par cumul matiere</label>';
//ajout gdu ---------------

if (getFiltreRechercheParam('filter_recherche_saisie_a_rattacher') == 'oui' && $traitement != null) {
    echo " | filtre actif : recherche de saisies a rattacher au traitement n° ";
    echo "<a href='./visu_traitement.php?id_traitement=".$traitement->getId()."";
    if($menu){
                echo"&menu=false";
            }
    echo "'>".$traitement->getId()."</a>";
}
echo '</p><p>';
if (isset($message_erreur_traitement)) {
    echo $message_erreur_traitement;
}
echo '</p>';
echo '<table id="table_liste_absents" class="tb_absences" style="border-spacing:0; width:100%; font-size:88%">';
echo '<thead>';
echo '<tr>';

echo '<th>';
echo '<div id="select_shortcut_buttons_container"/>';
echo '</th>';

//en tete filtre id
echo '<th>';
//echo '<nobr>';
echo '<input type="hidden" name="order" value="'.$order.'" />'; 
echo '<span style="white-space: nowrap;"> ';
echo '<input type="image" src="../images/up.png" title="monter" style="width:15px; height:15px;vertical-align: middle;';
if ($order == "asc_id") {echo "border-style: solid; border-color: red;";} else {echo "border-style: solid; border-color: silver;";}
echo 'border-width:1px;" alt="" name="order" value="asc_id" onclick="this.form.order.value = this.value"/>';
echo '<input type="image" src="../images/down.png" title="descendre" style="width:15px; height:15px;vertical-align: middle;';
if ($order == "des_id") {echo "border-style: solid; border-color: red;";} else {echo "border-style: solid; border-color: silver;";}
echo 'border-width:1px;" alt="" name="order" value="des_id" onclick="this.form.order.value = this.value"/>';
//echo '</nobr> ';
echo '</span>';
echo '<br/> ';
echo 'N°';
echo '<input type="text" name="filter_saisie_id" value="'.getFiltreRechercheParam('filter_saisie_id').'" size="3"/>';
echo '</th>';

if (getFiltreRechercheParam('filter_saisies_supprimees') == 'y') {
	//en tete filtre date suppression
	echo '<th>';
	//echo '<nobr>';
	echo '<span style="white-space: nowrap;"> ';
	echo 'Date suppression';
	echo '<input type="image" src="../images/up.png" title="monter" style="width:15px; height:15px;vertical-align: middle;';
	if ($order == "asc_date_suppression") {echo "border-style: solid; border-color: red;";} else {echo "border-style: solid; border-color: silver;";}
	echo 'border-width:1px;" alt="" name="order" value="asc_date_suppression"/ onclick="this.form.order.value = this.value">';
	echo '<input type="image" src="../images/down.png" title="descendre" style="width:15px; height:15px;vertical-align: middle;' ;
	if ($order == "des_date_suppression") {echo "border-style: solid; border-color: red;";} else {echo "border-style: solid; border-color: silver;";}
	echo 'border-width:1px;" alt="" name="order" value="des_date_suppression"/ onclick="this.form.order.value = this.value">';
	//echo '</nobr>';
	echo '</span>';
	echo '<br />';
	//echo '<nobr>';
	echo '<span style="white-space: nowrap;"> ';
	echo 'Entre : <input size="13" id="filter_date_suppression_saisie_debut_plage" name="filter_date_suppression_saisie_debut_plage" value="';
	if (isFiltreRechercheParam('filter_date_suppression_saisie_debut_plage')) {echo getFiltreRechercheParam('filter_date_suppression_saisie_debut_plage');}
	echo '" onKeyDown="clavier_date2(this.id,event);" AutoComplete="off" />&nbsp;';
	echo '<img id="trigger_filter_date_suppression_saisie_debut_plage" src="../images/icons/calendrier.gif" alt="" />';
	//echo '</nobr>';
	echo '</span>';
	echo '
	<script type="text/javascript">
	    Calendar.setup({
		inputField     :    "filter_date_suppression_saisie_debut_plage",     // id of the input field
		ifFormat       :    "%d/%m/%Y %H:%M",      // format of the input field
		button         :    "trigger_filter_date_suppression_saisie_debut_plage",  // trigger for the calendar (button ID)
		align          :    "Tl",           // alignment (defaults to "Bl")
		singleClick    :    true,
		showsTime	:   true
	    });
	</script>';
	echo '<br />';
	//echo '<nobr>';
	echo '<span style="white-space: nowrap;"> ';
	echo 'Et : <input size="13" id="filter_date_suppression_saisie_fin_plage" name="filter_date_suppression_saisie_fin_plage" value="';
	if (isFiltreRechercheParam('filter_date_suppression_saisie_fin_plage')) {echo getFiltreRechercheParam('filter_date_suppression_saisie_fin_plage');}
	echo '" onKeyDown="clavier_date2(this.id,event);" AutoComplete="off" />&nbsp;';
	echo '<img id="trigger_filter_date_suppression_saisie_fin_plage" src="../images/icons/calendrier.gif" alt="" />';
	//echo '</nobr>';
	echo '</span>';
	echo '
	<script type="text/javascript">
	    Calendar.setup({
		inputField     :    "filter_date_suppression_saisie_fin_plage",     // id of the input field
		ifFormat       :    "%d/%m/%Y %H:%M",      // format of the input field
		button         :    "trigger_filter_date_suppression_saisie_fin_plage",  // trigger for the calendar (button ID)
		align          :    "Tl",           // alignment (defaults to "Bl")
		singleClick    :    true,
		showsTime	:   true
	    });
	</script>';
	echo '</th>';
}

//en tete filtre utilisateur
echo '<th>';
//echo '<nobr>';
echo '<span style="white-space: nowrap;"> ';
echo '<input type="image" src="../images/up.png"  title="monter" style="width:15px; height:15px;vertical-align: middle;';
if ($order == "asc_utilisateur") {echo "border-style: solid; border-color: red;";} else {echo "border-style: solid; border-color: silver;";}
echo 'border-width:1px;" alt="" name="order" value="asc_utilisateur" onclick="this.form.order.value = this.value"/>';
echo '<input type="image" src="../images/down.png" title="descendre" style="width:15px; height:15px;vertical-align: middle;';
if ($order == "des_utilisateur") {echo "border-style: solid; border-color: red;";} else {echo "border-style: solid; border-color: silver;";}
echo 'border-width:1px;" alt="" name="order" value="des_utilisateur" onclick="this.form.order.value = this.value"/>';
//echo '</nobr>';
echo '</span>';
echo '<br />';
echo 'Utilisateur';
echo '<br /><input type="text" name="filter_utilisateur" value="'.getFiltreRechercheParam('filter_utilisateur').'" size="11"/>';
echo '</th>';

//en tete filtre eleve
echo '<th>';
//echo '<nobr>';
echo '<span style="white-space: nowrap;"> ';
echo '<input type="image" src="../images/up.png" title="monter" style="width:15px; height:15px;vertical-align: middle;';
if ($order == "asc_eleve") {echo "border-style: solid; border-color: red;";} else {echo "border-style: solid; border-color: silver;";}
echo 'border-width:1px;" alt="" name="order" value="asc_eleve" onclick="this.form.order.value = this.value"/>';
echo '<input type="image" src="../images/down.png" title="descendre" style="width:15px; height:15px;vertical-align: middle;';
if ($order == "des_eleve") {echo "border-style: solid; border-color: red;";} else {echo "border-style: solid; border-color: silver;";}
echo 'border-width:1px;" alt="" name="order" value="des_eleve" onclick="this.form.order.value = this.value"/>';
//echo '</nobr>';
echo '</span>';
echo 'Élève';
echo '<input type="hidden" value="y" name="filter_checkbox_posted"/>';echo '<br /><input type="text" name="filter_eleve" value="'.getFiltreRechercheParam('filter_eleve').'" size="11"/>';
echo '<br /><nobr><input type="checkbox" name="filter_marqueur_appel" id="filter_marqueur_appel" onchange="submit()" value="y"';
if (getFiltreRechercheParam('filter_marqueur_appel') == 'y') {echo "checked='checked'";}
echo '/><label for="filter_marqueur_appel">Marque d\'appel</label></nobr>';
echo '<br /><nobr><input type="checkbox" name="filter_no_marqueur_appel" id="filter_no_marqueur_appel" onchange="submit()" value="y"';
if (getFiltreRechercheParam('filter_no_marqueur_appel') == 'y') {echo "checked='checked'";}
echo '/><label for="filter_no_marqueur_appel">Exclure Marque appel</label></nobr>';
echo '</th>';

echo '<th>';
//en tete filtre classe
echo '<div>';
//echo '<nobr>';
echo '<span style="white-space: nowrap"> ';
echo 'Classe';
echo '<input type="image" src="../images/up.png" title="monter" style="width:15px; height:15px;vertical-align: middle;';
if ($order == "asc_classe") {echo "border-style: solid; border-color: red;";} else {echo "border-style: solid; border-color: silver;";}
echo 'border-width:1px;" alt="" name="order" value="asc_classe"/ onclick="this.form.order.value = this.value">';
echo '<input type="image" src="../images/down.png" title="descendre" style="width:15px; height:15px;vertical-align: middle;';
if ($order == "des_classe") {echo "border-style: solid; border-color: red;";} else {echo "border-style: solid; border-color: silver;";}
echo 'border-width:1px;" alt="" name="order" value="des_classe"/ onclick="this.form.order.value = this.value">';
//echo '</nobr>';
echo '</span>';
echo '<br />';
echo ("<select name=\"filter_classe\" onchange='submit()'>");
echo "<option value=''></option>\n";
foreach (ClasseQuery::create()->orderByNom()->orderByNomComplet()->find() as $classe) {
	echo "<option value='".$classe->getId()."'";
	if (getFiltreRechercheParam('filter_classe') === (string) $classe->getId()) echo " SELECTED ";
	echo ">";
	echo $classe->getNom();
	echo "</option>\n";
        //ajout gdu +++++++
        $tblClasses[$classe->getId()]=$classe->getNom();
        //ajout gdu -------
}
echo "</select>";
echo '</div>';

//en tete filtre groupe
echo '<div>';
//echo '<nobr>';
echo '<span style="white-space: nowrap;"> ';
echo 'Groupe';
echo '<input type="image" src="../images/up.png" title="monter" style="width:15px; height:15px;vertical-align: middle;';
if ($order == "asc_groupe") {echo "border-style: solid; border-color: red;";} else {echo "border-style: solid; border-color: silver;";}
echo 'border-width:1px;" alt="" name="order" value="asc_groupe"/ onclick="this.form.order.value = this.value">';
echo '<input type="image" src="../images/down.png" title="descendre" style="width:15px; height:15px;vertical-align: middle;';
if ($order == "des_groupe") {echo "border-style: solid; border-color: red;";} else {echo "border-style: solid; border-color: silver;";}
echo 'border-width:1px;" alt="" name="order" value="des_groupe"/ onclick="this.form.order.value = this.value">';
//echo '</nobr>';
echo '</span>';
echo '<br />';
echo ("<select name=\"filter_groupe\" onchange='submit()'>");
echo "<option value=''></option>\n";
foreach (GroupeQuery::create()->orderByName()->useJGroupesClassesQuery()->useClasseQuery()->orderByNom()->endUse()->endUse()
                                ->leftJoinWith('Groupe.JGroupesClasses')
                                ->leftJoinWith('JGroupesClasses.Classe')
                                ->find()  as $group) {
	echo "<option value='".$group->getId()."'";
	if (getFiltreRechercheParam('filter_groupe') === (string) $group->getId()) echo " SELECTED ";
	echo ">";
	echo $group->getNameAvecClasses();
	echo "</option>\n";
        //ajout gdu +++++++++++
        $tblGroupes[$group->getId()]=$group->getNameAvecClasses();
        //ajout gdu -----------
}
echo "</select>";
echo '</div>';

//en tete filtre aid
echo '<div>';
//echo '<nobr>';
echo '<span style="white-space: nowrap;"> ';
echo 'AID';
echo '<input type="image" src="../images/up.png" title="monter" style="width:15px; height:15px;vertical-align: middle;';
if ($order == "asc_aid") {echo "border-style: solid; border-color: red;";} else {echo "border-style: solid; border-color: silver;";}
echo 'border-width:1px;" alt="" name="order" value="asc_aid"/ onclick="this.form.order.value = this.value">';
echo '<input type="image" src="../images/down.png" title="descendre" style="width:15px; height:15px;vertical-align: middle;';
if ($order == "des_aid") {echo "border-style: solid; border-color: red;";} else {echo "border-style: solid; border-color: silver;";}
echo 'border-width:1px;" alt="" name="order" value="des_aid"/ onclick="this.form.order.value = this.value">';
//echo '</nobr>';
echo '</span>';
echo '<br />';
echo ("<select name=\"filter_aid\" onchange='submit()'>");
echo "<option value=''></option>\n";
//$temp_collection->add(AidDetailsQuery::create()->useJAidElevesQuery()->useEleveQuery()->useJEleveCpeQuery()->filterByUtilisateurProfessionnel($utilisateur)->endUse()->endUse()->endUse()->find());
foreach (AidDetailsQuery::create()->find() as $aid) {
	echo "<option value='".$aid->getId()."'";
	if (getFiltreRechercheParam('filter_aid') === (string) $aid->getId()) echo " SELECTED ";
	echo ">";
	echo $aid->getNom();
	echo "</option>\n";
}
echo "</select>";
echo '</div>';
echo '</th>';

//en tete filtre creneaux
echo '<th>';
//echo '<nobr>';
echo '<span style="white-space: nowrap;"> ';
echo 'Créneau';
echo '<input type="image" src="../images/up.png" title="monter" style="width:15px; height:15px;vertical-align: middle;';
if ($order == "asc_creneau") {echo "border-style: solid; border-color: red;";} else {echo "border-style: solid; border-color: silver;";}
echo 'border-width:1px;" alt="" name="order" value="asc_creneau"/ onclick="this.form.order.value = this.value">';
echo '<input type="image" src="../images/down.png" title="descendre" style="width:15px; height:15px;vertical-align: middle;';
if ($order == "des_creneau") {echo "border-style: solid; border-color: red;";} else {echo "border-style: solid; border-color: silver;";}
echo 'border-width:1px;" alt="" name="order" value="des_creneau"/ onclick="this.form.order.value = this.value">';
//echo '</nobr>';
echo '</span>';
echo '<br />';
echo ("<select name=\"filter_creneau\" onchange='submit()'>");
echo "<option value=''></option>\n";
foreach (EdtCreneauPeer::retrieveAllEdtCreneauxOrderByTime() as $edt_creneau) {
	echo "<option value='".$edt_creneau->getIdDefiniePeriode()."'";
	if (getFiltreRechercheParam('filter_creneau') === (string) $edt_creneau->getIdDefiniePeriode()) echo " SELECTED ";
	echo ">";
	echo $edt_creneau->getDescription();
	echo "</option>\n";
}
echo "</select>";
echo '</th>';

//en tete filtre date debut
echo '<th>';
//echo '<nobr>';
echo '<span style="white-space: nowrap;"> ';
echo 'Date début';
echo '<input type="image" src="../images/up.png" title="monter" style="width:15px; height:15px;vertical-align: middle;';
if ($order == "asc_date_debut") {echo "border-style: solid; border-color: red;";} else {echo "border-style: solid; border-color: silver;";}
echo 'border-width:1px;" alt="" name="order" value="asc_date_debut"/ onclick="this.form.order.value = this.value">';
echo '<input type="image" src="../images/down.png" title="descendre" style="width:15px; height:15px;vertical-align: middle;';
if ($order == "des_date_debut") {echo "border-style: solid; border-color: red;";} else {echo "border-style: solid; border-color: silver;";}
echo 'border-width:1px;" alt="" name="order" value="des_date_debut"/ onclick="this.form.order.value = this.value">';
//echo '</nobr>';
echo '</span>';
echo '<br />';
//echo '<nobr>';
echo '<span style="white-space: nowrap;"> ';
echo 'Entre : <input size="7" id="filter_date_debut_saisie_debut_plage" name="filter_date_debut_saisie_debut_plage" value="';
if (isFiltreRechercheParam('filter_date_debut_saisie_debut_plage')) {echo getFiltreRechercheParam('filter_date_debut_saisie_debut_plage');}
echo '" onKeyDown="clavier_date2(this.id,event);" AutoComplete="off" />&nbsp;';
echo '<img id="trigger_filter_date_debut_saisie_debut_plage" src="../images/icons/calendrier.gif" alt=""/>';
//echo '</nobr>';
echo '</span>';
echo '
<script type="text/javascript">
    Calendar.setup({
	inputField     :    "filter_date_debut_saisie_debut_plage",     // id of the input field
	ifFormat       :    "%d/%m/%Y %H:%M",      // format of the input field
	button         :    "trigger_filter_date_debut_saisie_debut_plage",  // trigger for the calendar (button ID)
	align          :    "Tl",           // alignment (defaults to "Bl")
	singleClick    :    true,
	showsTime	:   true
    });
</script>';
echo '<br />';
//echo '<nobr>';
echo '<span style="white-space: nowrap;"> ';
echo 'Et : <input size="7" id="filter_date_debut_saisie_fin_plage" name="filter_date_debut_saisie_fin_plage" value="';
if (isFiltreRechercheParam('filter_date_debut_saisie_fin_plage')) {echo getFiltreRechercheParam('filter_date_debut_saisie_fin_plage');}
echo '" onKeyDown="clavier_date2(this.id,event);" AutoComplete="off" />&nbsp;';
echo '<img id="trigger_filter_date_debut_saisie_fin_plage" src="../images/icons/calendrier.gif" alt="" />';
//echo '</nobr>';
echo '</span>';
echo '
<script type="text/javascript">
    Calendar.setup({
	inputField     :    "filter_date_debut_saisie_fin_plage",     // id of the input field
	ifFormat       :    "%d/%m/%Y %H:%M",      // format of the input field
	button         :    "trigger_filter_date_debut_saisie_fin_plage",  // trigger for the calendar (button ID)
	align          :    "Tl",           // alignment (defaults to "Bl")
	singleClick    :    true,
	showsTime	:   true
    });
</script>';
echo '</th>';

//en tete filtre date fin
echo '<th>';
//echo '<nobr>';
echo '<span style="white-space: nowrap;"> ';
echo 'Date fin';
echo '<input type="image" src="../images/up.png" title="monter" style="width:15px; height:15px;vertical-align: middle;';
if ($order == "asc_date_fin") {echo "border-style: solid; border-color: red;";} else {echo "border-style: solid; border-color: silver;";}
echo 'border-width:1px;" alt="" name="order" value="asc_date_fin"/ onclick="this.form.order.value = this.value">';
echo '<input type="image" src="../images/down.png" title="descendre" style="width:15px; height:15px;vertical-align: middle;';
if ($order == "des_date_fin") {echo "border-style: solid; border-color: red;";} else {echo "border-style: solid; border-color: silver;";}
echo 'border-width:1px;" alt="" name="order" value="des_date_fin"/ onclick="this.form.order.value = this.value">';
//echo '</nobr>';
echo '</span>';
echo '<br />';
//echo '<nobr>';
echo '<span style="white-space: nowrap;"> ';
echo 'Entre : <input size="7" id="filter_date_fin_saisie_debut_plage" name="filter_date_fin_saisie_debut_plage" value="';
if (isFiltreRechercheParam('filter_date_fin_saisie_debut_plage')) {echo getFiltreRechercheParam('filter_date_fin_saisie_debut_plage');}
echo '" onKeyDown="clavier_date2(this.id,event);" AutoComplete="off" />&nbsp;';
echo '<img id="trigger_filter_date_fin_saisie_debut_plage" src="../images/icons/calendrier.gif" alt="" />';
//echo '</nobr>';
echo '</span>';
echo '
<script type="text/javascript">
    Calendar.setup({
	inputField     :    "filter_date_fin_saisie_debut_plage",     // id of the input field
	ifFormat       :    "%d/%m/%Y %H:%M",      // format of the input field
	button         :    "trigger_filter_date_fin_saisie_debut_plage",  // trigger for the calendar (button ID)
	align          :    "Tl",           // alignment (defaults to "Bl")
	singleClick    :    true,
	showsTime	:   true
    });
</script>';
echo '<br />';
//echo '<nobr>';
echo '<span style="white-space: nowrap;"> ';
echo 'Et : <input size="7" id="filter_date_fin_saisie_fin_plage" name="filter_date_fin_saisie_fin_plage" value="';
if (isFiltreRechercheParam('filter_date_fin_saisie_fin_plage')) {echo getFiltreRechercheParam('filter_date_fin_saisie_fin_plage');}
echo '" onKeyDown="clavier_date2(this.id,event);" AutoComplete="off" />&nbsp;';
echo '<img id="trigger_filter_date_fin_saisie_fin_plage" src="../images/icons/calendrier.gif" alt="" />';
//echo '</nobr>';
echo '</span>';
echo '
<script type="text/javascript">
    Calendar.setup({
	inputField     :    "filter_date_fin_saisie_fin_plage",     // id of the input field
	ifFormat       :    "%d/%m/%Y %H:%M",      // format of the input field
	button         :    "trigger_filter_date_fin_saisie_fin_plage",  // trigger for the calendar (button ID)
	align          :    "Tl",           // alignment (defaults to "Bl")
	singleClick    :    true,
	showsTime	:   true
    });
</script>';
echo '</th>';

//en tete filtre emplacement de cours
echo '<th>';
//echo '<nobr>';
echo 'Cours';
//echo '</nobr>';
echo '</th>';

//en tete type d'absence
echo '<th>';
//echo '<nobr>';
echo 'Type';
//echo '</nobr>';
echo '<br />';
echo ("<select name=\"filter_type\" onchange='submit()'>");
echo "<option value=''></option>\n";
echo "<option value='SANS'";
if (getFiltreRechercheParam('filter_type') == 'SANS') echo " selected='selected' ";
echo ">SANS TYPE</option>\n";
foreach (AbsenceEleveTypeQuery::create()->orderBySortableRank()->find() as $type) {
	echo "<option value='".$type->getId()."'";
	if (getFiltreRechercheParam('filter_type') === (string) $type->getId()) echo " SELECTED ";
	echo ">";
	echo $type->getNom();
	echo "</option>\n";
}
echo "</select>";
echo '</th>';

//en tete filtre manqement_obligation
echo '<th>';
echo ("<select name=\"filter_manqement_obligation\" onchange='submit()'>");
echo "<option value=''";
if (!isFiltreRechercheParam('filter_manqement_obligation')) {echo "selected='selected'";}
echo "></option>\n";
echo "<option value='y' ";
if (getFiltreRechercheParam('filter_manqement_obligation') == 'y') {echo "selected='selected'";}
echo ">oui</option>\n";
echo "<option value='n' ";
if (getFiltreRechercheParam('filter_manqement_obligation') == 'n') {echo "selected='selected'";}
echo ">non</option>\n";
echo "</select>";
echo '<br/>Manque obligation présence';
echo '</th>';

//en tete filtre sous_responsabilite_etablissement
echo '<th>';
//echo '<input type="checkbox" value="y" name="filter_sous_responsabilite_etablissement" onchange="submit()"';
//if (isFiltreRechercheParam('filter_sous_responsabilite_etablissement') && getFiltreRechercheParam('filter_sous_responsabilite_etablissement') == 'y') {echo "checked='checked'";}
//echo '/><br/>sous resp. etab.';
echo 'Sous resp. étab.';
echo '</th>';

//en tete filtre date traitement
echo '<th>';
//echo '<nobr>';
echo '<span style="white-space: nowrap;"> ';
echo 'Date traitement';
echo '<input type="image" src="../images/up.png" title="monter" style="width:15px; height:15px;vertical-align: middle;';
if ($order == "asc_date_traitement") {echo "border-style: solid; border-color: red;";} else {echo "border-style: solid; border-color: silver;";}
echo 'border-width:1px;" alt="" name="order" value="asc_date_traitement"/ onclick="this.form.order.value = this.value">';
echo '<input type="image" src="../images/down.png" title="descendre" style="width:15px; height:15px;vertical-align: middle;';
if ($order == "des_date_traitement") {echo "border-style: solid; border-color: red;";} else {echo "border-style: solid; border-color: silver;";}
echo 'border-width:1px;" alt="" name="order" value="des_date_traitement"/ onclick="this.form.order.value = this.value">';
//echo '</nobr>';
echo '</span>';
echo '<br />';
//echo '<nobr>';
echo '<span style="white-space: nowrap;"> ';
echo 'Entre : <input size="7" id="filter_date_traitement_absence_debut_plage" name="filter_date_traitement_absence_debut_plage" value="';
if (isFiltreRechercheParam('filter_date_traitement_absence_debut_plage')) {echo getFiltreRechercheParam('filter_date_traitement_absence_debut_plage');}
echo '" onKeyDown="clavier_date2(this.id,event);" AutoComplete="off" />&nbsp;';
echo '<img id="trigger_filter_date_traitement_absence_debut_plage" src="../images/icons/calendrier.gif" alt="" />';
//echo '</nobr>';
echo '</span>';
echo '
<script type="text/javascript">
    Calendar.setup({
	inputField     :    "filter_date_traitement_absence_debut_plage",     // id of the input field
	ifFormat       :    "%d/%m/%Y %H:%M",      // format of the input field
	button         :    "trigger_filter_date_traitement_absence_debut_plage",  // trigger for the calendar (button ID)
	align          :    "Tl",           // alignment (defaults to "Bl")
	singleClick    :    true,
	showsTime	:   true
    });
</script>';
echo '<br />';
//echo '<nobr>';
echo '<span style="white-space: nowrap;"> ';
echo 'Et : <input size="7" id="filter_date_traitement_absence_fin_plage" name="filter_date_traitement_absence_fin_plage" value="';
if (isFiltreRechercheParam('filter_date_traitement_absence_fin_plage')) {echo getFiltreRechercheParam('filter_date_traitement_absence_fin_plage');}
echo '" onKeyDown="clavier_date2(this.id,event);" AutoComplete="off" />&nbsp;';
echo '<img id="trigger_filter_date_traitement_absence_fin_plage" src="../images/icons/calendrier.gif" alt="" />';
//echo '</nobr>';
echo '</span>';
echo '
<script type="text/javascript">
    Calendar.setup({
	inputField     :    "filter_date_traitement_absence_fin_plage",     // id of the input field
	ifFormat       :    "%d/%m/%Y %H:%M",      // format of the input field
	button         :    "trigger_filter_date_traitement_absence_fin_plage",  // trigger for the calendar (button ID)
	align          :    "Tl",           // alignment (defaults to "Bl")
	singleClick    :    true,
	showsTime	:   true
    });
</script>';
echo '</th>';

//en tete conflit
echo '<th>';
echo 'Conflit';
echo '</th>';

//en tete filtre date creation
echo '<th>';
//echo '<nobr>';
echo '<span style="white-space: nowrap;"> ';
echo 'Date création';
echo '<input type="image" src="../images/up.png" title="monter" style="width:15px; height:15px;vertical-align: middle;';
if ($order == "asc_date_creation") {echo "border-style: solid; border-color: red;";} else {echo "border-style: solid; border-color: silver;";}
echo 'border-width:1px;" alt="" name="order" value="asc_date_creation"/ onclick="this.form.order.value = this.value">';
echo '<input type="image" src="../images/down.png" title="descendre" style="width:15px; height:15px;vertical-align: middle;' ;
if ($order == "des_date_creation") {echo "border-style: solid; border-color: red;";} else {echo "border-style: solid; border-color: silver;";}
echo 'border-width:1px;" alt="" name="order" value="des_date_creation"/ onclick="this.form.order.value = this.value">';
//echo '</nobr>';
echo '</span>';
echo '<br />';
//echo '<nobr>';
echo '<span style="white-space: nowrap;"> ';
echo 'Entre : <input size="7" id="filter_date_creation_saisie_debut_plage" name="filter_date_creation_saisie_debut_plage" value="';
if (isFiltreRechercheParam('filter_date_creation_saisie_debut_plage')) {echo getFiltreRechercheParam('filter_date_creation_saisie_debut_plage');}
echo '" onKeyDown="clavier_date2(this.id,event);" AutoComplete="off" />&nbsp;';
echo '<img id="trigger_filter_date_creation_saisie_debut_plage" src="../images/icons/calendrier.gif" alt="" />';
//echo '</nobr>';
echo '</span>';
echo '
<script type="text/javascript">
    Calendar.setup({
	inputField     :    "filter_date_creation_saisie_debut_plage",     // id of the input field
	ifFormat       :    "%d/%m/%Y %H:%M",      // format of the input field
	button         :    "trigger_filter_date_creation_saisie_debut_plage",  // trigger for the calendar (button ID)
	align          :    "Tl",           // alignment (defaults to "Bl")
	singleClick    :    true,
	showsTime	:   true
    });
</script>';
echo '<br />';
//echo '<nobr>';
echo '<span style="white-space: nowrap;"> ';
echo 'Et : <input size="7" id="filter_date_creation_saisie_fin_plage" name="filter_date_creation_saisie_fin_plage" value="';
if (isFiltreRechercheParam('filter_date_creation_saisie_fin_plage')) {echo getFiltreRechercheParam('filter_date_creation_saisie_fin_plage');}
echo '" onKeyDown="clavier_date2(this.id,event);" AutoComplete="off" />&nbsp;';
echo '<img id="trigger_filter_date_creation_saisie_fin_plage" src="../images/icons/calendrier.gif" alt="" />';
//echo '</nobr>';
echo '</span>';
echo '
<script type="text/javascript">
    Calendar.setup({
	inputField     :    "filter_date_creation_saisie_fin_plage",     // id of the input field
	ifFormat       :    "%d/%m/%Y %H:%M",      // format of the input field
	button         :    "trigger_filter_date_creation_saisie_fin_plage",  // trigger for the calendar (button ID)
	align          :    "Tl",           // alignment (defaults to "Bl")
	singleClick    :    true,
	showsTime	:   true
    });
</script>';
echo '</th>';

//en tete filtre date modification
echo '<th>';
//echo '<nobr>';
echo '<span style="white-space: nowrap;"> ';
echo '';
echo '<input type="image" src="../images/up.png"  title="monter" style="width:15px; height:15px; vertical-align: middle;';
if ($order == "asc_date_modification") {echo "border-style: solid; border-color: red;";} else {echo "border-style: solid; border-color: silver;";}
echo 'border-width:1px;" alt="" name="order" value="asc_date_modification"/ onclick="this.form.order.value = this.value">';
echo '<input type="image" src="../images/down.png" title="descendre" style="width:15px; height:15px; vertical-align: middle;';
if ($order == "des_date_modification") {echo "border-style: solid; border-color: red;";} else {echo "border-style: solid; border-color: silver;";}
echo 'border-width:1px;" alt="" name="order" value="des_date_modification"/ onclick="this.form.order.value = this.value">';
echo '<br/> ';
echo '</span>';
echo '<span style="white-space: nowrap;"> ';
echo '<input type="checkbox" value="y" name="filter_date_modification" onchange="submit()"';
if (isFiltreRechercheParam('filter_date_modification') && getFiltreRechercheParam('filter_date_modification') == 'y') {echo "checked='checked'";}
echo '/></span><br/> Modifié';
echo '</th>';

//en tete commentaire
echo '<th>';
echo 'Com.';
echo '</th>';

//en tete discipline
echo '<th>';
//echo '<nobr>';
echo '<span style="white-space: nowrap;"> ';
echo '<input type="image" src="../images/up.png" title="monter" style="width:15px; height:15px; vertical-align: middle;';
if ($order == "asc_dis") {echo "border-style: solid; border-color: red;";} else {echo "border-style: solid; border-color: silver;";}
echo 'border-width:1px;" alt="" name="order" value="asc_dis"/ onclick="this.form.order.value = this.value">';
echo '<input type="image" src="../images/down.png" title="descendre" style="width:15px; height:15px; vertical-align: middle;';
if ($order == "des_dis") {echo "border-style: solid; border-color: red;";} else {echo "border-style: solid; border-color: silver;";}
echo 'border-width:1px;" alt="" name="order" value="des_dis"/ onclick="this.form.order.value = this.value">';
echo '</span>';
echo '<br/>';
//echo '<nobr>';
echo '<span style="white-space: nowrap;"> ';
echo '<input type="checkbox" value="y" name="filter_discipline" onchange="submit()"';
if (isFiltreRechercheParam('filter_discipline') && getFiltreRechercheParam('filter_discipline') == 'y') {echo "checked='checked'";}
echo '/></span><br/>Incident';
echo '</th>';

echo '</tr>';
echo '</thead>';

echo '<tbody>';
$results = $saisies_col->getResults();
if ($recherche_saisie_a_rattacher == 'oui' && $traitement != null) {
    if($results->count()==0){
        echo"<p class='red'>Aucune saisie (de + ou - 24 heures) à rattacher au traitement : ";
        echo "<a href='visu_traitement.php?id_traitement=".$traitement->getId()."";
        if($menu){
                echo"&menu=false";
            } 
        echo"'> ";
	    echo $traitement->getDescription();
	    echo "</a>";
    }
}

$hier='';
$numero_couleur=1;
$chaine_id_checkbox="";
//ajout gdu +++++++ du if.  Le premier foreach est celui
$affichageStandard = false;
//echo "<table>";
if($affichageStandard)
{//affichage original gepi
    echo "<tr><td>passe pour l'original</td></tr>";
    foreach ($results as $saisie) {
	if ((getFiltreRechercheParam('filter_no_marqueur_appel') == 'y')&&($saisie->getEleve() == null)) {
		//echo "<tr><td>Ligne exclue</td></tr>";
		continue;
		//echo "<tr><td>Après ligne exclue</td></tr>";
	}

        $aujourdhui=strftime("%d/%m/%Y", $saisie->getDebutAbs('U'));
        if (!isFiltreRechercheParam('filter_eleve')) {
            $numero_couleur = $results->getPosition();
        } else {
            if ($aujourdhui !== $hier)
                $numero_couleur++;
        }
        if ($numero_couleur %2 == '1') {
                $background_couleur="rgb(220, 220, 220);";
        } else {
                $background_couleur="rgb(210, 220, 230);";
        }
        echo "<tr style='background-color :$background_couleur'>\n";

        if ($saisie->getNotifiee()) {
            $prop = 'saisie_notifie';
        } elseif ($saisie->getTraitee()) {
            $prop = 'saisie_traite';
        } else {
            $prop = 'saisie_vierge';
        }
        $id_champ_checkbox_courant=$prop.'_'.$results->getPosition();
        echo '<td><input name="select_saisie[]" value="'.$saisie->getPrimaryKey().'" type="checkbox" id="'.$id_champ_checkbox_courant.'" ';
        if((isset($rattachement_preselection))&&($rattachement_preselection=="y")) { echo "checked ";}
        echo '/></td>';

            if($chaine_id_checkbox!="") {
                    $chaine_id_checkbox.=", ";
            }
            $chaine_id_checkbox.="'$id_champ_checkbox_courant'";

        echo '<td>';
        echo "<a href='visu_saisie.php?id_saisie=".$saisie->getPrimaryKey()."' style='display: block; height: 100%;'> ";
        echo $saisie->getId();
        echo "</a>";
        echo '</td>';

            if (getFiltreRechercheParam('filter_saisies_supprimees') == 'y') {
                echo '</td>';
                echo '<td>';
                echo "<a href='visu_saisie.php?id_saisie=".$saisie->getPrimaryKey()."' style='display: block; height: 100%; color: #330033'>\n";
                echo (strftime("%a %d/%m/%Y %H:%M", $saisie->getDeletedAt('U')));
            $suppr_utilisateur = UtilisateurProfessionnelQuery::create()->findPK($saisie->getDeletedBy());
            if ($suppr_utilisateur != null) {
                    echo ' par '.  $suppr_utilisateur->getCivilite().' '.$suppr_utilisateur->getNom().' '.mb_substr($suppr_utilisateur->getPrenom(), 0, 1).'.';;
            }
                echo "</a>";
                echo '</td>';
            }

        echo '<td>';
    //    echo "<a href='liste_saisies_selection_traitement.php?filter_utilisateur=".$saisie->getUtilisateurProfessionnel()->getNom()."' style='display: block; height: 100%; color: #330033'> ";
        if ($saisie->getUtilisateurProfessionnel() != null) {
        echo "<a href='liste_saisies_selection_traitement.php?filter_utilisateur=".$saisie->getUtilisateurProfessionnel()->getNom()."' style='display: block; height: 100%; color: #330033'> ";
            echo $saisie->getUtilisateurProfessionnel()->getCivilite().' '.$saisie->getUtilisateurProfessionnel()->getNom();
        echo "</a>";
        }
     //   echo "</a>";
        echo '</td>';

        echo '<td>';
        if ($saisie->getEleve() != null) {
            echo "<table style='border-spacing:0px; border-style : none; margin : 0px; padding : 0px; font-size:100%; width:100%;'>";
            echo "<tr style='border-spacing:0px; border-style : none; margin : 0px; padding : 0px; font-size:100%;'>";
            echo "<td style='border-spacing:0px; border-style : none; margin : 0px; padding : 0px; font-size:100%;'>";
            echo "<a href='liste_saisies_selection_traitement.php?filter_eleve=".$saisie->getEleve()->getNom()."&order=asc_eleve' style='display: block; height: 100%;'> ";
            echo ($saisie->getEleve()->getCivilite().' '.$saisie->getEleve()->getNom().' '.$saisie->getEleve()->getPrenom());
            echo "</a>";
            if ($utilisateur->getAccesFicheEleve($saisie->getEleve())) {
                //echo "<a href='../eleves/visu_eleve.php?ele_login=".$saisie->getEleve()->getLogin()."' target='_blank'>";
                echo "<a href='../eleves/visu_eleve.php?ele_login=".$saisie->getEleve()->getLogin()."&amp;onglet=responsables&amp;quitter_la_page=y' target='_blank' >";
                echo ' (voir fiche)';
                echo "</a>";
            }
            echo "</td>";
            echo "<td style='border-spacing:0px; border-style : none; margin : 0px; padding : 0px; font-size:100%;'>";
    //	echo "<a href='liste_saisies_selection_traitement.php?filter_eleve=".$saisie->getEleve()->getNom()."' style='display: block; height: 100%;'> ";
            if ((getSettingValue("active_module_trombinoscopes")=='y') && $saisie->getEleve() != null) {

            echo "<a href='liste_saisies_selection_traitement.php?filter_eleve=".$saisie->getEleve()->getNom()."&order=asc_eleve' style='display: block; height: 100%;'> ";
            $nom_photo = $saisie->getEleve()->getNomPhoto(1);
                $photos = $nom_photo;
                //if (($nom_photo != "") && (file_exists($photos))) {
                if (($nom_photo != NULL) && (file_exists($photos))) {
                    $valeur = redimensionne_image_petit($photos);
                    echo ' <img src="'.$photos.'" style ="width:'.$valeur[0].'px; height:'.$valeur[1].'px;align:right" alt="" title="" /> ';
                }
            echo "</a>";
            }
    //	echo "</a>";
            echo "</td></tr></table>";
        } else {
            echo "Marqueur d'appel effectué";
        }
        echo '</td>';

        echo '<td>';
        if ($saisie->getClasse() != null) {
            echo "<a href='liste_saisies_selection_traitement.php?filter_classe=".$saisie->getClasse()->getPrimaryKey()."' style='display: block; height: 100%; color: #330033'> ";
            echo $saisie->getClasse()->getNom();
            echo "</a>";
        }
        if ($saisie->getGroupe() != null) {
            echo "<a href='liste_saisies_selection_traitement.php?filter_groupe=".$saisie->getGroupe()->getPrimaryKey()."' style='display: block; height: 100%; color: #330033'> ";
            echo $saisie->getGroupe()->getNameAvecClasses();
        echo "</a>";
        }
        if ($saisie->getAidDetails() != null) {
            echo "<a href='liste_saisies_selection_traitement.php?filter_aid=".$saisie->getAidDetails()->getPrimaryKey()."' style='display: block; height: 100%; color: #330033'>\n";
            echo $saisie->getAidDetails()->getNom();
            echo "</a>";
        }
        echo '</td>';

        echo '<td>';
    //    echo "<a href='visu_saisie.php?id_saisie=".$saisie->getPrimaryKey()."' style='display: block; height: 100%; color: #330033'>\n";
        if ($saisie->getEdtCreneau() != null) {
            //$groupe = new Groupe();
        echo "<a href='visu_saisie.php?id_saisie=".$saisie->getPrimaryKey()."' style='display: block; height: 100%; color: #330033'>\n";
            echo $saisie->getEdtCreneau()->getDescription();
        echo "</a>";
        } else {
            echo "&nbsp;";
        }
        echo '</td>';

        echo '<td>';
        echo "<a href='visu_saisie.php?id_saisie=".$saisie->getPrimaryKey()."' style='display: block; height: 100%; color: #330033'>\n";
        echo (strftime("%a %d/%m/%Y %H:%M", $saisie->getDebutAbs('U')));
        echo "</a>";
        echo '</td>';

        echo '<td>';
        echo "<a href='visu_saisie.php?id_saisie=".$saisie->getPrimaryKey()."' style='display: block; height: 100%; color: #330033'>\n";
        echo (strftime("%a %d/%m/%Y %H:%M", $saisie->getFinAbs('U')));
        echo "</a>";
        echo '</td>';

        echo '<td>';
    //    echo "<a href='visu_saisie.php?id_saisie=".$saisie->getPrimaryKey()."' style='display: block; height: 100%; color: #330033'>\n";
        //echo '<nobr>';
        if ($saisie->getEdtEmplacementCours() != null) {
            //$groupe = new Groupe();
        echo "<a href='visu_saisie.php?id_saisie=".$saisie->getPrimaryKey()."' style='display: block; height: 100%; color: #330033'>\n";

            echo $saisie->getEdtEmplacementCours()->getDescription();
        echo "</a>";
        } else {
            echo "&nbsp;";
        }
        //echo '</nobr>';
        echo '</td>';

        echo '<td>';
        echo "<a href='visu_saisie.php?id_saisie=".$saisie->getPrimaryKey()."' style='display: block; height: 100%; color: #330033'>\n";
        foreach ($saisie->getAbsenceEleveTraitements() as $traitement) {
             if ($traitement->getAbsenceEleveType() != null) {
                echo $traitement->getAbsenceEleveType()->getNom();
                if (!$saisie->getAbsenceEleveTraitements()->isLast())
                echo ';<br/>';
             }
        }
        if ($saisie->getAbsenceEleveTraitements()->isEmpty()) {
            echo "&nbsp;";
        }
        echo "</a>";
        echo '</td>';

        echo '<td>';
        if ($saisie->getManquementObligationPresence()) {
            echo 'oui';
        } else {
            echo 'non';
        }
        echo '</td>';

        echo '<td>';
        if ($saisie->getSousResponsabiliteEtablissement()) {
            echo 'oui';
        } else {
            echo 'non';
        }
        echo '</td>';

        echo '<td>';
        foreach ($saisie->getAbsenceEleveTraitements() as $traitement) {
            echo "<table width='100%'><tr><td>";
            echo "<a href='visu_traitement.php?id_traitement=".$traitement->getPrimaryKey()."' style='display: block; height: 100%;'> ";
        $desc = $traitement->getDescription();
        if (mb_strlen($desc)>300) {
            echo mb_substr($desc,0,300).' ... ';
        } else {
            echo $desc;
        }
            echo "</a>";
            echo "</td></tr></table>";
        }
        if ($saisie->getAbsenceEleveTraitements()->isEmpty()) {
            echo "&nbsp;";
        }
        echo '</td>';

        echo '<td>';
        $saisies_conflit = $saisie->getSaisiesContradictoiresManquementObligation();
        foreach ($saisies_conflit as $saisie_conflit) {
            echo "<a href='visu_saisie.php?id_saisie=".$saisie_conflit->getPrimaryKey()."' style=''> ";
            echo $saisie_conflit->getId();
            echo "</a>";
            if (!$saisies_conflit->isLast()) {
                echo ' - ';
            }
        }
            echo '</td>';
        echo '<td>';
        echo "<a href='visu_saisie.php?id_saisie=".$saisie->getPrimaryKey()."' style='display: block; height: 100%; color: #330033'>\n";
            $all_version = $saisie->getAllVersions()->getFirst();
            if ($all_version != null) {
                    $created_at = $saisie->getAllVersions()->getFirst()->getVersionCreatedAt('U');
            echo (strftime("%a %d/%m/%Y %H:%M", $created_at));
            }else{
            $created_at = $saisie->getVersionCreatedAt('U');
            echo (strftime("%a %d/%m/%Y %H:%M", $created_at)); 
            }
        echo "</a>";
        echo '</td>';

        echo '<td>';
        echo "<a href='visu_saisie.php?id_saisie=".$saisie->getPrimaryKey()."' style='display: block; height: 100%; color: #330033'>\n";
        if ($created_at != $saisie->getVersionCreatedAt('U')) {
        echo "<a href='visu_saisie.php?id_saisie=".$saisie->getPrimaryKey()."' style='display: block; height: 100%; color: #330033'>\n";

            echo (strftime("%a %d/%m/%Y %H:%M", $saisie->getVersionCreatedAt('U')));
        echo "</a>";
        } else {
            echo "&nbsp;";
        }
        echo '</td>';

        echo '<td>';
        echo "<a href='visu_saisie.php?id_saisie=".$saisie->getPrimaryKey()."' style='display: block; height: 100%; color: #330033'>\n";
        echo ($saisie->getCommentaire());
        echo "&nbsp;";
        echo "</a>";
        echo '</td>';

        echo '<td>';
        if ($saisie->getIdSIncidents() !== null) {
            echo "<a href='../mod_discipline/saisie_incident.php?id_incident=".
            $saisie->getIdSIncidents()."&step=2&return_url=no_return'>Visualiser l'incident </a>";
        }
        echo '</td>';

        echo '</tr>';
        $hier=$aujourdhui;
    }
}
else
{//calcul le cumul des absences par eleve, matieres et par profs et affichage. 
//Bugs potentiels:
// - plusieurs saisies "contradictoires" qui indiquent le manquement. -> a traiter suivant les parametres de l'application
    //idealement, il faudrait faire le calcul du nombre d'appels effectués pour avoir un rapport absences/nb_appels_effectues
    
    //echo "<tr><td>fait le travail aussi</td></tr>";
    
    $tbl_cumuls = array(); //va recevoir les cumuls d'eleves par classe et par groupe (enseignements) -> serait mieux en objet mais... je ne sais pas faire en php
    $eleve_precedent = ""; //utile pour ne faire les tests une seule fois sur un eleve.
    $indexPourTest = 0; //pour test
    
    //$tblGroupes = array(); //trouver comment on recupere les groupes
    //$tblClasse = array(); //trouver comment on recupere les classes
    $iForBreak=0;
    foreach($results as $saisie)
    //foreach($query as $saisie)
    {
        //if($iForBreak==10){break;} // pour tests
        //$iForBreak++;            //pour tests
        
        if ($saisie->getEleve() != null){
            //if($eleve_precedent!=$saisie->getEleve()->getLogin())
            //if(!is_array($tbl_cumuls[$saisie->getEleve()->getLogin()]))
            //echo "<td>TEST sur getEleve() = ".$saisie->getEleve()->getLogin()."</td>";
            if(!array_key_exists($saisie->getEleve()->getLogin(), $tbl_cumuls))
            {//on ne parcourt qu'une seule fois cet eleve.
             
                $eleve_precedent=$saisie->getEleve()->getLogin();  //plus besoin?
                
                //if(!is_array($tbl_cumuls[$saisie->getEleve()->getLogin()]))
                if(!array_key_exists($saisie->getEleve()->getLogin(), $tbl_cumuls))
                {//si le tableau de l'eleve n'a pas encore ete cree alors on le creer.
                    $tbl_cumuls[$saisie->getEleve()->getLogin()]=array(); // pour avoir groupe, classe, eventuellement saisie_ids
                    // $tbl_cumuls[$saisie->getEleve()->getLogin()]["groupes"]=array(); // l'eleve a plusieurs groupes
                     $tbl_cumuls[$saisie->getEleve()->getLogin()]["classe"]=array(); // l'eleve n'a qu'une classe mais c'est plus simple -> pour le cumul total.
                     $tbl_cumuls[$saisie->getEleve()->getLogin()]["cumul_total"]=0;
                     $tbl_cumuls[$saisie->getEleve()->getLogin()]["saisies_contradictoires"]=array();
                     //$tbl_cumuls[$saisie->getEleve()->getLogin()]["id_saisies"]=array(); // l'eleve n'a qu'une classe mais c'est plus simple
                     //$tbl_cumuls[$saisie->getEleve()->getLogin()]["full_eleve"]=$saisie->getEleve();
                }

        /****
        //tmp ++
        //on devrait eviter de comptabiliser les saisies contradictoires suivant le choix fait dans les parametres
            $saisies_conflit = $saisie->getSaisiesContradictoiresManquementObligation();
            foreach ($saisies_conflit as $saisie_conflit) {
                echo "Saisie en conflit:<br>".$saisie->getEleve()->getLogin()." -> ".$saisie->getPrimaryKey()." VS <a href='visu_saisie.php?id_saisie=".$saisie_conflit->getPrimaryKey()."' style=''> " ;
                echo $saisie_conflit->getId();
                echo "</a>";
                if (!$saisies_conflit->isLast()) {
                    echo ' - <br>';
                }
            }
        //tmp --
        *****/       
                foreach($results as $saisie2)
                //foreach($query as $saisie2)
                {
                $indexPourTest++; //pour test
                    if ($saisie2->getEleve() != null)
                    {
                        //if($saisie->getEleve()->getLogin() == "alpettaz_o")
                        //{ //pour tests
                        
                            if($saisie->getEleve()->getLogin() == $saisie2->getEleve()->getLogin())
                            {//a partir du meme eleve, nous allons commencer le denombrage
                                if($saisie2->getManquementObligationPresence())
                                {//il faudrait voir s'il y a des saisies contradictoires!... et selon les parametres ajouter ou non le manquement.
                                    $NePossedePasUneSaisieContradictoire=true;
                                    $saisies2_conflit = $saisie2->getSaisiesContradictoiresManquementObligation();
                                    //echo "<tr>saisie2_conflit existe?".$saisies2_conflit."</tr>";
                                    if(!empty($saisies2_conflit))
                                    {
                                        //$NePossedePasUneSaisieContradictoire=false;// le test doit etre fait par le foreach plus bas.
                                        //tester sur la marque d'appel
                                        
                                        $isaisie2testeur=0;
                                        //on est obligé de garder ce test pour etre sûr qu'il y a bien un conflit...
                                        foreach ($saisies2_conflit as $saisie2_conflit) {
                                            $tbl_cumuls[$saisie->getEleve()->getLogin()]["saisies_contradictoires"][$saisie2->getPrimaryKey()][]=$saisie2_conflit->getId();
                                            //echo $isaisie2testeur." - Saisie en conflit:<br>".$saisie2->getEleve()->getLogin()." -> ".$saisie2->getPrimaryKey()." VS <a href='visu_saisie.php?id_saisie=".$saisie2_conflit->getPrimaryKey()."' style=''> " ;
                                            //echo $saisie2_conflit->getId();
                                            //echo "</a><br>$isaisie2testeur++++++++++++++++++++++++++++++++++++++++++++++<br>";
                                            /******
                                           echo $saisie2->getEleve()->getLogin()." -> ".$isaisie2testeur." - eleve de la saisie (".$saisie2_conflit->getId()."): <br>";
                                           //echo $saisie2_conflit->getEleve()."<br>";
                                           if($saisie2_conflit->getEleve()!=null)
                                           {
                                               echo "l eleve existe dans la saisie conflit<br>";
                                           }
                                           else{echo "l eleve n'EXISTE PAS dans la saisie conflit - donc c'est un marqueur d'appel<br>";}
                                             * 
                                             */
                                           if( $saisie2_conflit->getEleve()!=null)
                                            {//la saisie existe et est en lien avec l'eleve
                                                //echo $saisie2->getEleve()->getLogin()." -> ".$isaisie2testeur." - l'eleve existe pour saisie (".$saisie2_conflit->getId().") et vaut: ".$saisie2_conflit->getEleve()->getLogin()."<br>";
                                                //echo $saisie2->getEleve()->getLogin()." -> ".$isaisie2testeur." - ".$saisie2_conflit->getId()." (".$saisie2_conflit->getEleve()->getLogin().") => ".$saisie2_conflit."<br>";
                                                //echo $saisie2->getEleve()->getLogin()." -> ".$isaisie2testeur." - ".$saisie2->getPrimaryKey()." (".$saisie2->getEleve()->getLogin().")=> ".$saisie2."<br>----------------------------------<br>";
                                                $NePossedePasUneSaisieContradictoire=false;//donc il possede une saisie contradictoire
                                            }
                                            else
                                            {//c'est un marqueur d'appel
                                                //echo $saisie2->getEleve()->getLogin()." -> ".$isaisie2testeur." il n'y a pas d'eleve pour la saisie ".$saisie2_conflit->getId()." et eleve =";
                                                //echo "".$saisie2_conflit->getEleve()."<br>";
                                               
                                                $NePossedePasUneSaisieContradictoire=true;
                                            }
                                             
                                             //echo "------------------------------------<br>";
                                            if (!$saisies2_conflit->isLast()) {
                                                echo ' - ';
                                            }
                                            $isaisie2testeur++;
                                            //if($saisie2->getEleve())
                                            //$NePossedePasUneSaisieContradictoire=false;
                                            //break; //comme on sait qu'il y a une saisie contradictoire, on ne fait pas le decompte.
                                        }
                                        //echo "<br>";
                                    }
                                    
                                    if($NePossedePasUneSaisieContradictoire) 
                                    {//on teste une saisie avec manquement d'obligation de presence, si elle possede une saisie contradictoire, alors on considere que l'eleve n'a pas de manquement d'obligation de presence
                                        foreach($tblGroupes as $cegroupe => $cegroupeName)
                                        {//on cherche dans quel groupe ce coquin d'eleve a un manquement oblig presence
                                            //echo "<td>".$cegroupe."==".$saisie2->getGroupe()->getId()."</td>";
                                            //if($saisie2->getGroupe()->getId() == $cegroupe)
                                            if(($saisie2->getGroupe()!=null)&&($saisie2->getGroupe()->getId() == $cegroupe))
                                            {

                                                if(!array_key_exists("groupes", $tbl_cumuls[$saisie2->getEleve()->getLogin()]))
                                                {
                                                    //echo "<td>creation de l'array 'groupes'</td>";
                                                    //$tbl_cumuls[$saisie2->getEleve()->getLogin()]["groupes"]=array($cegroupe => array());
                                                    //$tbl_cumuls[$saisie2->getEleve()->getLogin()]["groupes"][$cegroupe]=array(); //ne cree pas ce que je veux
                                                    $tbl_cumuls[$saisie2->getEleve()->getLogin()]["groupes"]=array();

                                                    //faire le cumul par matieres (qui contient les groupes/enseignements)
                                                    //$tbl_cumuls["marqueurs_appels"]["matieres"][$idMatiere]["cumul"]=0
                                                    //$tbl_cumuls["marqueurs_appels"]["matieres"][$idMatiere]["groupes"][$cegroupe]["cumul"]=0
                                                    //$tbl_cumuls["marqueurs_appels"]["matieres"][$idMatiere]["groupes"][$cegroupe][$prof]=0
                                                }
                                                //echo "<td>".$saisie2->getEleve()->getLogin()." -> la cle pour le groupe [$cegroupe] ".(array_key_exists($cegroupe, $tbl_cumuls[$saisie2->getEleve()->getLogin()]["groupes"]) ? 'true' : 'false')."</td>";

                                                if(!array_key_exists($cegroupe, $tbl_cumuls[$saisie2->getEleve()->getLogin()]["groupes"]))
                                                {//la cle "$cegroupe" n'existe pas -> on creer le nom du groupe et le cumul dans ce cas
                                                     //echo "<td>creation du groupe pour ".$saisie2->getEleve()->getNom()." et groupe [".$cegroupe."]<br>";
                                                    //$tbl_cumuls[$saisie2->getEleve()->getLogin()]["groupes"]=array($cegroupe => 0);
                                                    //$tbl_cumuls[$saisie2->getEleve()->getLogin()]["groupes"][] = array($cegroupe => array("cumul" => 0, "id_saisies" => array()));
                                                    //$tbl_cumuls[$saisie2->getEleve()->getLogin()]["groupes"][$cegroupe] = array("cumul" => 0, "id_saisies" => array());

                                                    $tbl_cumuls[$saisie2->getEleve()->getLogin()]["groupes"][$cegroupe]=array();
                                                    $tbl_cumuls[$saisie2->getEleve()->getLogin()]["groupes"][$cegroupe]["cumul"]=0;
                                                    $tbl_cumuls[$saisie2->getEleve()->getLogin()]["groupes"][$cegroupe]["cumul_par_profs"]=array();
                                                    $tbl_cumuls[$saisie2->getEleve()->getLogin()]["groupes"][$cegroupe]["id_saisies"]=array();
                                                }
                                                else{
                                                    //echo "<td>le groupe [$cegroupe] existe<br>";var_dump($tbl_cumuls[$saisie2->getEleve()->getLogin()]);echo "</td>";
                                                }

                                                //echo "<td>$indexPourTest - tbl_cumuls[".$saisie2->getEleve()->getLogin()."][groupes][".$cegroupe."]['cumul'] = ".$tbl_cumuls[$saisie2->getEleve()->getLogin()]["groupes"][$cegroupe]['cumul']."</td>";
                                                //echo "<td>$indexPourTest - tbl_cumuls[".$saisie2->getEleve()->getLogin()."][groupes][".$cegroupe."]['cumul'] = ".$tbl_cumuls[$saisie2->getEleve()->getLogin()]["groupes"][$cegroupe]["cumul"]."</td>";
                                                //echo "<td>$indexPourTest - groupes pour l'eleve ".$saisie2->getEleve()->getNom()."<br>";
                                                //var_dump($tbl_cumuls[$saisie2->getEleve()->getLogin()]["groupes"]);
                                                //echo "</td>";

                                                $prof = ""; //rechercher le nom du profs dans le cours en question. 
                                                $utilisateurQuiASaisi = "";//verifier si ca le fait s'il y a d'autres profs
                                                if ($saisie2->getUtilisateurProfessionnel() != null) {
                                                            //on espere qu'il n'y a pas de personnel avec '
                                                            //pour le moment on n'utilise que l'utlisateur qui a fait la saisie.
                                                            $utilisateurQuiASaisi= $saisie2->getUtilisateurProfessionnel()->getCivilite().' '.$saisie2->getUtilisateurProfessionnel()->getNom();
                                                        }
                                                if(!array_key_exists($utilisateurQuiASaisi, $tbl_cumuls[$saisie2->getEleve()->getLogin()]["groupes"][$cegroupe]["cumul_par_profs"]))
                                                {
                                                    $tbl_cumuls[$saisie2->getEleve()->getLogin()]["groupes"][$cegroupe]["cumul_par_profs"][$utilisateurQuiASaisi]=0;
                                                }
                                                $tbl_cumuls[$saisie2->getEleve()->getLogin()]["groupes"][$cegroupe]["cumul_par_profs"][$utilisateurQuiASaisi]++;
                                                    //$tbl_cumuls[$saisie2->getEleve()->getLogin()]["groupes"][$cegroupe]["matiereOri"]=0;
                                                //if($tbl_cumuls[$saisie->getEleve()->getLogin()])
                                                $tbl_cumuls[$saisie2->getEleve()->getLogin()]["groupes"][$cegroupe]["cumul"]++;
                                                //FAUX, c'estait a cause du break - il faut prendre $saisie->getPrimaryKey parce que ce sont ces saisies qui sont parcourues pour faire le calcul du cumul
                                                //$tbl_cumuls[$saisie2->getEleve()->getLogin()]["id_saisies"][]=$saisie2->getPrimaryKey();//ajouter l'id de la saisie ici
                                                $tbl_cumuls[$saisie2->getEleve()->getLogin()]["groupes"][$cegroupe]["id_saisies"][]=$saisie2->getPrimaryKey();//ajouter l'id de la saisie ici
                                                $tbl_cumuls[$saisie2->getEleve()->getLogin()]["cumul_total"]++;
                                                //echo "<td>$indexPourTest - tbl pour l'eleve ".$saisie2->getEleve()->getNom()." apres insertion absence:<br>";
                                                //var_dump($tbl_cumuls[$saisie2->getEleve()->getLogin()]);
                                                //echo "</td>";
                                                //break; //on sort de la boucle pour eviter des calculs supplementaires
                                            }
                                        }
                                        foreach($tblClasses as $cetteClasse)
                                        {//manquement oblig presence au total mais pourra pourra etre controlé par la somme des cumuls des groupes
                                            if($saisie2->getClasse() == $cetteClasse)
                                            {// he bien, il va falloir ajouter cela au cumul
                                                if(!is_array($tbl_cumuls[$saisie->getEleve()->getLogin()]["classe"]))
                                                {
                                                    $tbl_cumuls[$saisie2->getEleve()->getLogin()]["classe"][$cetteClasse]=0;
                                                }
                                                //if($tbl_cumuls[$saisie->getEleve()->getLogin()])
                                                $tbl_cumuls[$saisie2->getEleve()->getLogin()]["classe"][$cetteClasse]++;
                                                break; //on sort de la boucle pour eviter des calculs supplementaires
                                            }
                                        }
                                    }
                                    //break; //on sort de l'ajout aux cumuls et passe à une saisie suivante.
                                }else
                                {
                                    //il n'y a pas de manquement d'obligation de presence.
                                }
                            }
                        //} 
                    }
                }
            }
            else{
                //eleve deja parcouru.
            }
        }//fin du check que l'eleve existe
        else
        {
            //l'eleve n'existe pas
        }
    }    
    
    //echo "<table><tr><td>tbl des cumuls</td></tr>";
    //echo "<tr><td colspan=3>Dump du tableau cree:<br>";
    //var_dump($tbl_cumuls);
    //echo "</td></tr>";
    //echo "</table>";
    echo "<tr>";
    echo "<td colspan='20'><table id='tbl_affichage_manqt'>";
    echo "<th>Eleve</th><th>nb total<br>manquements<br> oblig. pres.</th><th>Enseignement</th><th>nb manquement à <br>obligation de présence</th><th>saisies</th></td>";
    $numero_couleur=0; // pour l'alternance des couleurs dans le tableau
    
    foreach ($tbl_cumuls as $el => $tblEl)
    {
        $numero_couleur++;
        if ($numero_couleur %2 == '1') {
                $background_couleur="rgb(220, 220, 220);";
        } else {
                $background_couleur="rgb(210, 220, 230);";
        }
        echo "<tr style='background-color :$background_couleur'>\n";
        
        //echo "<tr>";
        
        echo "<td>$el</td>";
        //echo "<td>".$tbl_cumuls[$el]["full_eleve"]->getCivilite()." ".$tbl_cumuls[$el]["full_eleve"]->getNom()." ".$tbl_cumuls[$el]["full_eleve"]->getPrenom()."<br>$el</td>";
        echo "<td>".$tbl_cumuls[$el]["cumul_total"]."</td>";
        echo "<td colspan='3'><table id='tbl_cumul_abs_el_+$el'>";
        
        if(array_key_exists("groupes", $tbl_cumuls[$el]))
        {
            $numero_couleur_groupes=0;
            foreach($tbl_cumuls[$el]["groupes"] as $groupe => $tblGroupeVals)
            {
                $numero_couleur_groupes++;
                if ($numero_couleur_groupes %2 == '1') {
                    $background_couleur_groupes="rgb(220, 220, 220);";
                } else {
                    $background_couleur_groupes="rgb(210, 220, 220);";
                }
                //echo "<tr>";
                echo "<tr style='background-color :$background_couleur_groupes'>\n";
                echo "<td id='td_group'>".$tblGroupes[$groupe]."</td>";
                //echo "<td>".$tblGroupeVals["cumul"]."</td>";
                echo "<td id='td_cumul_pour_group'>".$tbl_cumuls[$el]["groupes"][$groupe]["cumul"]."</td>";
                
                //affichage des cumuls par profs
                echo "<td><table id='tbl_pour_saisie_prof'>";
               
                //$tbl_cumuls[$saisie2->getEleve()->getLogin()]["groupes"][$cegroupe]["cumul_par_profs"][$utilisateurQuiASaisi]
                foreach($tbl_cumuls[$el]["groupes"][$groupe]["cumul_par_profs"] as $i_prof => $nb_abs_donnee_par_prof)
                {
                   echo "<tr><td>";
                   echo "".$i_prof.": ".$nb_abs_donnee_par_prof;
                   echo "</td></tr>";
                }
                
                echo "</table></td>";
                
                //affichage des saisies
                echo "<td><table id='tbl_pour_saisies'>";
                echo "<tr><td>";
                foreach($tbl_cumuls[$el]["groupes"][$groupe]["id_saisies"] as $saisieMnqmtPres)
                {
                   //echo "<tr><td>".$saisieMnqmtPres."</td></tr>";
                   echo "".$saisieMnqmtPres.",";
                }
                echo "</tr></td>";
                echo "</table></td>";
                echo "</tr>";
            }
        }
        else{echo "<tr><td>Cet eleve n'a pas de manquement à obligation d'absence relevé.</td></tr>";}
        
        if (isset($tbl_cumuls[$el]["saisies_contradictoires"]) AND ($tbl_cumuls[$el]["saisies_contradictoires"]!=NULL))
        {
            echo "<tr style='background-color:rgb(210,210,210)'><td colspan='2'> Liste des saisies contradictoires</td></tr>";
            foreach($tbl_cumuls[$el]["saisies_contradictoires"] as $saisieId => $saisiesContradictoires)
            {
                echo "<tr><td>";
                    echo "<a href='visu_saisie.php?id_saisie=".$saisieId."' style=''>".$saisieId."</a>";
                echo "</td><td>";
                foreach($tbl_cumuls[$el]["saisies_contradictoires"][$saisieId] as $saisieContradictoire)
                {
                    echo $saisieContradictoire.", ";
                }
                echo "</td></tr>";
            }

        }
        echo "</table></td>";
        echo "</tr>";
    }
    echo "</table></td>";
    $afficheLesSaisies = false;
    if($afficheLesSaisies)
    {//on fait un affichage des saisies (Attention a la gestion de la pagination...)
    
    foreach ($results as $saisie) {
            if ((getFiltreRechercheParam('filter_no_marqueur_appel') == 'y')&&($saisie->getEleve() == null)) {
                    //echo "<tr><td>Ligne exclue</td></tr>";
                    continue;
                    //echo "<tr><td>Après ligne exclue</td></tr>";
            }

        $aujourdhui=strftime("%d/%m/%Y", $saisie->getDebutAbs('U'));
        if (!isFiltreRechercheParam('filter_eleve')) {
            $numero_couleur = $results->getPosition();
        } else {
            if ($aujourdhui !== $hier)
                $numero_couleur++;
        }
        if ($numero_couleur %2 == '1') {
                $background_couleur="rgb(220, 220, 220);";
        } else {
                $background_couleur="rgb(210, 220, 230);";
        }
        echo "<tr style='background-color :$background_couleur'>\n";

        if ($saisie->getNotifiee()) {
            $prop = 'saisie_notifie';
        } elseif ($saisie->getTraitee()) {
            $prop = 'saisie_traite';
        } else {
            $prop = 'saisie_vierge';
        }
        $id_champ_checkbox_courant=$prop.'_'.$results->getPosition();
        echo '<td><input name="select_saisie[]" value="'.$saisie->getPrimaryKey().'" type="checkbox" id="'.$id_champ_checkbox_courant.'" ';
        if((isset($rattachement_preselection))&&($rattachement_preselection=="y")) { echo "checked ";}
        echo '/></td>';

            if($chaine_id_checkbox!="") {
                    $chaine_id_checkbox.=", ";
            }
            $chaine_id_checkbox.="'$id_champ_checkbox_courant'";

        echo '<td>';
        echo "<a href='visu_saisie.php?id_saisie=".$saisie->getPrimaryKey()."' style='display: block; height: 100%;'> ";
        echo $saisie->getId();
        echo "</a>";
        echo '</td>';

            if (getFiltreRechercheParam('filter_saisies_supprimees') == 'y') {
                echo '</td>';
                echo '<td>';
                echo "<a href='visu_saisie.php?id_saisie=".$saisie->getPrimaryKey()."' style='display: block; height: 100%; color: #330033'>\n";
                echo (strftime("%a %d/%m/%Y %H:%M", $saisie->getDeletedAt('U')));
            $suppr_utilisateur = UtilisateurProfessionnelQuery::create()->findPK($saisie->getDeletedBy());
            if ($suppr_utilisateur != null) {
                    echo ' par '.  $suppr_utilisateur->getCivilite().' '.$suppr_utilisateur->getNom().' '.mb_substr($suppr_utilisateur->getPrenom(), 0, 1).'.';;
            }
                echo "</a>";
                echo '</td>';
            }

        echo '<td>';
    //    echo "<a href='liste_saisies_selection_traitement.php?filter_utilisateur=".$saisie->getUtilisateurProfessionnel()->getNom()."' style='display: block; height: 100%; color: #330033'> ";
        if ($saisie->getUtilisateurProfessionnel() != null) {
        echo "<a href='liste_saisies_selection_traitement.php?filter_utilisateur=".$saisie->getUtilisateurProfessionnel()->getNom()."' style='display: block; height: 100%; color: #330033'> ";
            echo $saisie->getUtilisateurProfessionnel()->getCivilite().' '.$saisie->getUtilisateurProfessionnel()->getNom();
        echo "</a>";
        }
     //   echo "</a>";
        echo '</td>';

        echo '<td>';
        if ($saisie->getEleve() != null) {
            echo "<table style='border-spacing:0px; border-style : none; margin : 0px; padding : 0px; font-size:100%; width:100%;'>";
            echo "<tr style='border-spacing:0px; border-style : none; margin : 0px; padding : 0px; font-size:100%;'>";
            echo "<td style='border-spacing:0px; border-style : none; margin : 0px; padding : 0px; font-size:100%;'>";
            echo "<a href='liste_saisies_selection_traitement.php?filter_eleve=".$saisie->getEleve()->getNom()."&order=asc_eleve' style='display: block; height: 100%;'> ";
            echo ($saisie->getEleve()->getCivilite().' '.$saisie->getEleve()->getNom().' '.$saisie->getEleve()->getPrenom());
            echo "</a>";
            if ($utilisateur->getAccesFicheEleve($saisie->getEleve())) {
                //echo "<a href='../eleves/visu_eleve.php?ele_login=".$saisie->getEleve()->getLogin()."' target='_blank'>";
                echo "<a href='../eleves/visu_eleve.php?ele_login=".$saisie->getEleve()->getLogin()."&amp;onglet=responsables&amp;quitter_la_page=y' target='_blank' >";
                echo ' (voir fiche)';
                echo "</a>";
            }
            echo "</td>";
            echo "<td style='border-spacing:0px; border-style : none; margin : 0px; padding : 0px; font-size:100%;'>";
    //	echo "<a href='liste_saisies_selection_traitement.php?filter_eleve=".$saisie->getEleve()->getNom()."' style='display: block; height: 100%;'> ";
            if ((getSettingValue("active_module_trombinoscopes")=='y') && $saisie->getEleve() != null) {

            echo "<a href='liste_saisies_selection_traitement.php?filter_eleve=".$saisie->getEleve()->getNom()."&order=asc_eleve' style='display: block; height: 100%;'> ";
            $nom_photo = $saisie->getEleve()->getNomPhoto(1);
                $photos = $nom_photo;
                //if (($nom_photo != "") && (file_exists($photos))) {
                if (($nom_photo != NULL) && (file_exists($photos))) {
                    $valeur = redimensionne_image_petit($photos);
                    echo ' <img src="'.$photos.'" style ="width:'.$valeur[0].'px; height:'.$valeur[1].'px;align:right" alt="" title="" /> ';
                }
            echo "</a>";
            }
    //	echo "</a>";
            echo "</td></tr></table>";
        } else {
            echo "Marqueur d'appel effectué";
        }
        echo '</td>';

        echo '<td>';
        if ($saisie->getClasse() != null) {
            echo "<a href='liste_saisies_selection_traitement.php?filter_classe=".$saisie->getClasse()->getPrimaryKey()."' style='display: block; height: 100%; color: #330033'> ";
            echo $saisie->getClasse()->getNom();
            echo "</a>";
        }
        if ($saisie->getGroupe() != null) {
            echo "<a href='liste_saisies_selection_traitement.php?filter_groupe=".$saisie->getGroupe()->getPrimaryKey()."' style='display: block; height: 100%; color: #330033'> ";
            echo $saisie->getGroupe()->getNameAvecClasses();
        echo "</a>";
        }
        if ($saisie->getAidDetails() != null) {
            echo "<a href='liste_saisies_selection_traitement.php?filter_aid=".$saisie->getAidDetails()->getPrimaryKey()."' style='display: block; height: 100%; color: #330033'>\n";
            echo $saisie->getAidDetails()->getNom();
            echo "</a>";
        }
        echo '</td>';

        echo '<td>';
    //    echo "<a href='visu_saisie.php?id_saisie=".$saisie->getPrimaryKey()."' style='display: block; height: 100%; color: #330033'>\n";
        if ($saisie->getEdtCreneau() != null) {
            //$groupe = new Groupe();
        echo "<a href='visu_saisie.php?id_saisie=".$saisie->getPrimaryKey()."' style='display: block; height: 100%; color: #330033'>\n";
            echo $saisie->getEdtCreneau()->getDescription();
        echo "</a>";
        } else {
            echo "&nbsp;";
        }
        echo '</td>';

        echo '<td>';
        echo "<a href='visu_saisie.php?id_saisie=".$saisie->getPrimaryKey()."' style='display: block; height: 100%; color: #330033'>\n";
        echo (strftime("%a %d/%m/%Y %H:%M", $saisie->getDebutAbs('U')));
        echo "</a>";
        echo '</td>';

        echo '<td>';
        echo "<a href='visu_saisie.php?id_saisie=".$saisie->getPrimaryKey()."' style='display: block; height: 100%; color: #330033'>\n";
        echo (strftime("%a %d/%m/%Y %H:%M", $saisie->getFinAbs('U')));
        echo "</a>";
        echo '</td>';

        echo '<td>';
    //    echo "<a href='visu_saisie.php?id_saisie=".$saisie->getPrimaryKey()."' style='display: block; height: 100%; color: #330033'>\n";
        //echo '<nobr>';
        if ($saisie->getEdtEmplacementCours() != null) {
            //$groupe = new Groupe();
        echo "<a href='visu_saisie.php?id_saisie=".$saisie->getPrimaryKey()."' style='display: block; height: 100%; color: #330033'>\n";

            echo $saisie->getEdtEmplacementCours()->getDescription();
        echo "</a>";
        } else {
            echo "&nbsp;";
        }
        //echo '</nobr>';
        echo '</td>';

        echo '<td>';
        echo "<a href='visu_saisie.php?id_saisie=".$saisie->getPrimaryKey()."' style='display: block; height: 100%; color: #330033'>\n";
        foreach ($saisie->getAbsenceEleveTraitements() as $traitement) {
             if ($traitement->getAbsenceEleveType() != null) {
                echo $traitement->getAbsenceEleveType()->getNom();
                if (!$saisie->getAbsenceEleveTraitements()->isLast())
                echo ';<br/>';
             }
        }
        if ($saisie->getAbsenceEleveTraitements()->isEmpty()) {
            echo "&nbsp;";
        }
        echo "</a>";
        echo '</td>';

        echo '<td>';
        if ($saisie->getManquementObligationPresence()) {
            echo 'oui';
        } else {
            echo 'non';
        }
        echo '</td>';

        echo '<td>';
        if ($saisie->getSousResponsabiliteEtablissement()) {
            echo 'oui';
        } else {
            echo 'non';
        }
        echo '</td>';

        echo '<td>';
        foreach ($saisie->getAbsenceEleveTraitements() as $traitement) {
            echo "<table width='100%'><tr><td>";
            echo "<a href='visu_traitement.php?id_traitement=".$traitement->getPrimaryKey()."' style='display: block; height: 100%;'> ";
        $desc = $traitement->getDescription();
        if (mb_strlen($desc)>300) {
            echo mb_substr($desc,0,300).' ... ';
        } else {
            echo $desc;
        }
            echo "</a>";
            echo "</td></tr></table>";
        }
        if ($saisie->getAbsenceEleveTraitements()->isEmpty()) {
            echo "&nbsp;";
        }
        echo '</td>';

        echo '<td>';
        $saisies_conflit = $saisie->getSaisiesContradictoiresManquementObligation();
        foreach ($saisies_conflit as $saisie_conflit) {
            echo "<a href='visu_saisie.php?id_saisie=".$saisie_conflit->getPrimaryKey()."' style=''> ";
            echo $saisie_conflit->getId();
            echo "</a>";
            if (!$saisies_conflit->isLast()) {
                echo ' - ';
            }
        }
            echo '</td>';
        echo '<td>';
        echo "<a href='visu_saisie.php?id_saisie=".$saisie->getPrimaryKey()."' style='display: block; height: 100%; color: #330033'>\n";
            $all_version = $saisie->getAllVersions()->getFirst();
            if ($all_version != null) {
                    $created_at = $saisie->getAllVersions()->getFirst()->getVersionCreatedAt('U');
            echo (strftime("%a %d/%m/%Y %H:%M", $created_at));
            }else{
            $created_at = $saisie->getVersionCreatedAt('U');
            echo (strftime("%a %d/%m/%Y %H:%M", $created_at)); 
            }
        echo "</a>";
        echo '</td>';

        echo '<td>';
        echo "<a href='visu_saisie.php?id_saisie=".$saisie->getPrimaryKey()."' style='display: block; height: 100%; color: #330033'>\n";
        if ($created_at != $saisie->getVersionCreatedAt('U')) {
        echo "<a href='visu_saisie.php?id_saisie=".$saisie->getPrimaryKey()."' style='display: block; height: 100%; color: #330033'>\n";

            echo (strftime("%a %d/%m/%Y %H:%M", $saisie->getVersionCreatedAt('U')));
        echo "</a>";
        } else {
            echo "&nbsp;";
        }
        echo '</td>';

        echo '<td>';
        echo "<a href='visu_saisie.php?id_saisie=".$saisie->getPrimaryKey()."' style='display: block; height: 100%; color: #330033'>\n";
        echo ($saisie->getCommentaire());
        echo "&nbsp;";
        echo "</a>";
        echo '</td>';

        echo '<td>';
        if ($saisie->getIdSIncidents() !== null) {
            echo "<a href='../mod_discipline/saisie_incident.php?id_incident=".
            $saisie->getIdSIncidents()."&step=2&return_url=no_return'>Visualiser l'incident </a>";
        }
        echo '</td>';

        echo '</tr>';
        $hier=$aujourdhui;
    }
    
    }
}
//echo "</table>";
//ajout gdu ------ de la fin du else
echo '</tbody>';
//echo '</tbody>';

echo '</table>';

/*
if((isset($rattachement_preselection))&&($rattachement_preselection=="y")&&(isset($traitement_recherche_saisie_a_rattacher))) {
	echo "<button type='submit' name='creation_traitement' value='yes' dojoType='dijit.MenuItem' onClick=\"
		//Create an input type dynamically.
		var element = document.createElement('input');
		element.setAttribute('type', 'hidden');
		element.setAttribute('name', 'ajout_traitement');
		element.setAttribute('value', 'yes');
		document.liste_saisies.appendChild(element);
		var element = document.createElement('input');
		element.setAttribute('type', 'hidden');
		element.setAttribute('name', 'id_traitement');
		element.setAttribute('value', '".$traitement_recherche_saisie_a_rattacher->getId()."');
		document.liste_saisies.appendChild(element);
		document.liste_saisies.submit();
				\">
		Ajouter les saisies au traitement ";
	$desc = $traitement_recherche_saisie_a_rattacher->getDescription();
	if (mb_strlen($desc)>300) {
		echo mb_substr($desc,0,300).' ... ';
	} else {
		echo $desc;
	}
	echo "</button>";
}
*/
    
    
echo '<p>';
if (isset($message_erreur_traitement)) {
    echo $message_erreur_traitement;
}
echo '</p>';
echo '</form>';

//if((isset($rattachement_preselection))&&($rattachement_preselection=="y")&&(isset($traitement_recherche_saisie_a_rattacher))) {
if(isset($traitement_recherche_saisie_a_rattacher)) {
	$texte_bouton="Ajouter les saisies sélectionnées au traitement ";
	$desc = $traitement_recherche_saisie_a_rattacher->getDescription();
	if (mb_strlen($desc)>300) {
		$texte_bouton.=mb_substr($desc,0,300).' ... ';
	} else {
		$texte_bouton.=$desc;
	}
	$texte_bouton.="<br />Et retourner aux Absences du jour";

	echo "<form action=\"".$_SERVER['PHP_SELF']."\" method='post' name='liste_saisies2' id='liste_saisies2'>

	<!--input type='hidden' name='creation_traitement' value='yes'-->
	<input type='hidden' name='ajout_traitement' value='yes'>
	<input type='hidden' name='retour_absences_du_jour' value='yes'>
	<input type='hidden' name='id_traitement' value='".$traitement_recherche_saisie_a_rattacher->getId()."'>
	<input type='hidden' name='id_eleve' value='$id_eleve'>

	<p style='text-align:center;'>
		<button type='button' dojoType='dijit.form.Button' name='valider_rattachement_saisies_au_traitement' onClick=\"copier_selection_et_valider_rattachement()\" 
			value=\"$texte_bouton\" 
			title=\"$texte_bouton\" >
			$texte_bouton
		</button>
	</p>
</form>

<script type='text/javascript'>
	var tab_checkbox=new Array($chaine_id_checkbox);

	function copier_selection_et_valider_rattachement() {
		for(i=0;i<tab_checkbox.length;i++) {
			if(document.getElementById(tab_checkbox[i])) {
				if(document.getElementById(tab_checkbox[i]).checked==true) {
					var element = document.createElement('input');
					element.setAttribute('type', 'hidden');
					element.setAttribute('name', 'select_saisie[]');
					element.setAttribute('value', document.getElementById(tab_checkbox[i]).value);
					document.liste_saisies2.appendChild(element);
				}
			}
		}
		document.liste_saisies2.submit();
	}
</script>
";

}



echo '</div>';

$javascript_footer_texte_specifique = '<script type="text/javascript">
    dojo.require("dijit.form.Button");
    dojo.require("dijit.Menu");
    dojo.require("dijit.form.Form");
    dojo.require("dijit.form.CheckBox");
    dojo.require("dijit.form.DateTextBox");

    dojo.addOnLoad(function() {
        var menu = new dijit.Menu({
            style: "display: none;"
        });

        var menuItem0 = new dijit.MenuItem({
            label: "Sélectionner tous",
            onClick: function() {
			SetAllCheckBoxes(\'liste_saisies\', \'select_saisie[]\', \'\', true);
	    }
        });
        menu.addChild(menuItem0);
	
        var menuItem1 = new dijit.MenuItem({
            label: "aucun",
            onClick: function() {
			SetAllCheckBoxes(\'liste_saisies\', \'select_saisie[]\', \'\', false);
        }
        });
        menu.addChild(menuItem1);

        var menuItem2 = new dijit.MenuItem({
            label: "non traitées",
            onClick: function() {
			SetAllCheckBoxes(\'liste_saisies\', \'select_saisie[]\', \'\', false);
			SetAllCheckBoxes(\'liste_saisies\', \'select_saisie[]\', \'saisie_vierge\', true);
	    }
        });
        menu.addChild(menuItem2);

        var menuItem3 = new dijit.MenuItem({
            label: "non notifiées",
            onClick: function() {
			SetAllCheckBoxes(\'liste_saisies\', \'select_saisie[]\', \'\', true);
			SetAllCheckBoxes(\'liste_saisies\', \'select_saisie[]\', \'saisie_notifie\', false);}
        });
        menu.addChild(menuItem3);
        
        //ajout gdu ++++++++++++
        var menuItem4 = new dijit.MenuItem({
            label: "affichage par cumuls",
            onClick: function() {
                        SetAllCheckBoxes(\'liste_saisies\', \'select_saisie[]\', \'\', true);
			SetAllCheckBoxes(\'liste_saisies\', \'select_saisie[]\', \'saisie_cumulmatiere\', false);}
        });
        menu.addChild(menuItem4);
        //ajout gdu ------------
        
        var button = new dijit.form.DropDownButton({
            label: "",
            name: "programmatic2",
            dropDown: menu,
            id: "progButton"
        });
        dojo.byId("select_shortcut_buttons_container").appendChild(button.domNode);

	//affichage des boutons d action
	dojo.query(\'[widgetid=action_bouton]\').style({ visibility:"visible" }).style({ display:"" });
    });
</script>';

require_once("../lib/footer.inc.php");

?>
