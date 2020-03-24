<?php

/**
 * Fichier qui permet de construire la barre de menu scolarité
 * 
 *
 * Variables envoyées au gabarit : liens de la barre de menu scolarité
 * - $tbs_menu_admin = array(li)
 *
 * @license GNU/GPL v2
 * @package General
 * @subpackage Affichage
 * @see getSettingValue()
 * @see insert_confirm_abandon()
 * @see menu_plugins()
 * @todo Réécrire la barre administrateur, le principe des gabarits, c'est d'envoyer des variables aux gabarits, 
 * pas d'écrire du code html dans le constructeur
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
 *
 */
 
// ====== SECURITE =======

if (!$_SESSION["login"]) {
    header("Location: ../logout.php?auto=2");
    die();
}

// Fonction générant le menu Plugins
include("menu_plugins.inc.php");
$barre_plugin=menu_plugins();
if ($barre_plugin!="") {
	$barre_plugin = "<li class='li_inline'><a href=\"\">Plugins</a>"."\n"
					."	<ul class='niveau2'>\n"
					.$barre_plugin
					."	</ul>\n"
					."</li>\n";
}
// fin plugins

/*******************************************************************
 *
 *			Construction du menu horizontal de la page d'accueil 
 *			pour le profil administrateur
 *
 *******************************************************************/

	if ($_SESSION['statut'] == "scolarite") {
		$acces_saisie_modalites_accompagnement=acces_saisie_modalites_accompagnement();

		$tmp_liste_classes_scol=array();
		$sql="SELECT DISTINCT id, classe, nom_complet FROM classes ORDER BY classe;";

		$res_tmp_liste_classes_scol = mysqli_query($mysqli, $sql);
		if($res_tmp_liste_classes_scol->num_rows > 0) {
			$tmp_cpt_classes_scol=0;
			while($lig_tmp_liste_classes_scol = $res_tmp_liste_classes_scol->fetch_object()) {
				$tmp_liste_classes_scol[$tmp_cpt_classes_scol]=array();
				$tmp_liste_classes_scol[$tmp_cpt_classes_scol]['id']=$lig_tmp_liste_classes_scol->id;
				$tmp_liste_classes_scol[$tmp_cpt_classes_scol]['classe']=$lig_tmp_liste_classes_scol->classe;
				$tmp_liste_classes_scol[$tmp_cpt_classes_scol]['nom_complet']=$lig_tmp_liste_classes_scol->nom_complet;
				$tmp_cpt_classes_scol++;
			}
		} 

		$acces_saisie_engagement="n";
		if(getSettingAOui('active_mod_engagements')) {
			$tab_engagements_avec_droit_saisie=get_tab_engagements_droit_saisie_tel_user($_SESSION['login']);
			if(count($tab_engagements_avec_droit_saisie['indice'])>0) {
				$acces_saisie_engagement="y";
			}
		}

		$menus = null;

		//=======================================================
		// Module Absences
		if (getSettingValue("active_module_absence") == 'y') {
			$menus .= '<li class="li_inline"><a href="'.$gepiPath.'/mod_absences/gestion/voir_absences_viescolaire.php"'.insert_confirm_abandon().'>Absences</a></li>'."\n";
		}
		elseif (getSettingValue("active_module_absence") == '2') {
			$menus .= '<li class="li_inline"><a href="'.$gepiPath.'/mod_abs2/index.php"'.insert_confirm_abandon().'>&nbsp;Absences</a></li>'."\n";
		}

		if (getSettingAOui("active_mod_abs_prof")) {
			$menus .= '<li class="li_inline"><a href="'.$gepiPath.'/mod_abs_prof/index.php" '.insert_confirm_abandon().'>Abs.profs</a></li>'."\n";
		}

		//=======================================================
		// Module Cahier de textes
		if (getSettingValue("active_cahiers_texte") == 'y') {
			if((getSettingAOui('GepiAccesCdtScol'))||(getSettingAOui('GepiAccesCdtScolRestreint'))) {
				if(getSettingValue('GepiCahierTexteVersion')==2) {
					$menus .= '<li class="li_inline"><a href="'.$gepiPath.'/cahier_texte_2/see_all.php"'.insert_confirm_abandon().'>&nbsp;CDT</a>'."\n";
					$menus .= '   <ul class="niveau2">'."\n";
					$menus .= '     <li><a href="'.$gepiPath.'/cahier_texte_2/see_all.php"'.insert_confirm_abandon().'>Consultation CDT</a></li>'."\n";
				}
				else {
					$menus .= '<li class="li_inline"><a href="'.$gepiPath.'/cahier_texte/see_all.php"'.insert_confirm_abandon().'>&nbsp;CDT</a>'."\n";
					$menus .= '   <ul class="niveau2">'."\n";
					$menus .= '     <li><a href="'.$gepiPath.'/cahier_texte/see_all.php"'.insert_confirm_abandon().'>Consultation CDT</a></li>'."\n";
				}
				$menus .= '     <li><a href="'.$gepiPath.'/cahier_texte_2/extract_tag.php"'.insert_confirm_abandon().' title="Extraire les notices portant tel ou tel tag (contrôle, EPI, AP,...)">Extraction tag</a></li>'."\n";

				if(getSettingValue('GepiAccesCdtVisa')=='yes') {
					$menus .= '     <li><a href="'.$gepiPath.'/cahier_texte_admin/visa_ct.php"'.insert_confirm_abandon().'>Visa c. de textes</a></li>'."\n";
				}
				if(getSettingAOui('acces_archives_cdt')) {
					$menus .= '     <li><a href="'.$gepiPath.'/documents/archives/index.php"'.insert_confirm_abandon().'>Archives CDT</a></li>'."\n";
				}
				$menus .= '   </ul>'."\n";
				$menus .= '</li>'."\n";
			}
			elseif(getSettingValue('GepiAccesCdtVisa')=='yes') {
				$menus .= '<li class="li_inline">&nbsp;CDT'."\n";
				$menus .= '   <ul class="niveau2">'."\n";
				$menus .= '     <li><a href="'.$gepiPath.'/cahier_texte_admin/visa_ct.php"'.insert_confirm_abandon().'>Visa c. de textes</a></li>'."\n";
				$menus .= '   </ul>'."\n";
				$menus .= '</li>'."\n";
			}
		}
		//=======================================================

		if(getSettingValue("active_carnets_notes") == 'y'){
			//=======================================================
			// Bulletins
			if (getSettingValue("active_bulletins") == "y") {
				$menus .= '<li class="li_inline"><a href="'.$gepiPath.'/bulletin/bulletins_et_conseils_classes.php"'.insert_confirm_abandon().'>&nbsp;Bulletins</a>'."\n";
				$menus .= '   <ul class="niveau2">'."\n";
	
				$menus .= '     <li class="plus">Avis conseil classe'."\n";
				$menus .= '            <ul class="niveau3">'."\n";
				if(getSettingValue('GepiRubConseilScol')=='yes') {
					$menus .= '                <li><a href="'.$gepiPath.'/saisie/saisie_avis.php"'.insert_confirm_abandon().'>Saisie des avis Conseil</a></li>'."\n";
				}
				if(getSettingValue('CommentairesTypesScol')=='yes') {
					$menus .= '                <li><a href="'.$gepiPath.'/saisie/commentaires_types.php"'.insert_confirm_abandon().'>Commentaires-types</a></li>'."\n";
				}
				$menus .= '                <li><a href="'.$gepiPath.'/saisie/impression_avis.php"'.insert_confirm_abandon().'>Impression avis PDF</a></li>'."\n";
				$menus .= '                <li><a href="'.$gepiPath.'/bulletin/impression_avis_grp.php"'.insert_confirm_abandon().'>Avis groupes/classes</a></li>'."\n";
				$menus .= '                <li><a href="'.$gepiPath.'/classes/dates_classes.php"'.insert_confirm_abandon().' title="Faire apparaître des événements en page d\'accueil pour telle ou telle classe de telle à telle date,...
Vous pouvez notamment faire apparaître un tableau des dates de conseils de classe.">Dates événements</a></li>'."\n";
				if(getSettingAOui('active_mod_engagements')) {
					$menus .= '                <li><a href="'.$gepiPath.'/mod_engagements/extraction_engagements.php" '.insert_confirm_abandon().'>Extraction engagements</a></li>'."\n";
					$menus .= '                <li><a href="'.$gepiPath.'/mod_engagements/imprimer_documents.php" '.insert_confirm_abandon().'>Convocation conseil de classe,...</a></li>'."\n";
				}
				$menus .= '            </ul>'."\n";
				$menus .= '     </li>'."\n";
	
				$menus .= '     <li class="plus">Vérif. et accès'."\n";
				$menus .= '            <ul class="niveau3">'."\n";
				$menus .= '                <li><a href="'.$gepiPath.'/bulletin/verif_bulletins.php"'.insert_confirm_abandon().'>Vérif. remplissage bull</a></li>'."\n";
				$menus .= '                <li><a href="'.$gepiPath.'/bulletin/verrouillage.php"'.insert_confirm_abandon().'>Verrouillage périodes</a></li>'."\n";
				if(getSettingAOui('PeutDonnerAccesBullAppPeriodeCloseScol')) {
					$menus .= '                <li><a href="'.$gepiPath.'/bulletin/autorisation_exceptionnelle_saisie_app.php"'.insert_confirm_abandon().'>Autorisation exceptionnelle de remplissage des appréciations</a></li>'."\n";
				}
				if(getSettingAOui('PeutDonnerAccesBullNotePeriodeCloseScol')) {
					$menus .= '                <li><a href="'.$gepiPath.'/bulletin/autorisation_exceptionnelle_saisie_note.php"'.insert_confirm_abandon().'>Autorisation exceptionnelle de remplissage des notes</a></li>'."\n";
				}
				$menus .= '                <li><a href="'.$gepiPath.'/classes/acces_appreciations.php"'.insert_confirm_abandon().'>Accès resp/ele appréciations</a></li>'."\n";
				$menus .= '            </ul>'."\n";
				$menus .= '     </li>'."\n";
	
	
				$menus .= '     <li class="plus">Bulletins'."\n";
				$menus .= '            <ul class="niveau3">'."\n";
				if(getSettingValue('GepiScolImprBulSettings')=='yes') {
				if(getSettingValue('type_bulletin_par_defaut')=="pdf") {
					$menus .= '                <li><a href="'.$gepiPath.'/bulletin/param_bull_pdf.php" '.insert_confirm_abandon().'>Param. impr. bull</a></li>'."\n";
				}
				else {
					$menus .= '                <li><a href="'.$gepiPath.'/bulletin/param_bull.php" '.insert_confirm_abandon().'>Param. impression bull</a></li>'."\n";
				}
				}
				$menus .= '                <li><a href="'.$gepiPath.'/bulletin/bull_index.php"'.insert_confirm_abandon().'>Impression bulletins</a></li>'."\n";
				$menus .= '                <li><a href="'.$gepiPath.'/prepa_conseil/index3.php"'.insert_confirm_abandon().'>Bulletins simplifiés</a></li>'."\n";
				$menus .= '                <li><a href="'.$gepiPath.'/bulletin/impression_avis_grp.php"'.insert_confirm_abandon().'>Avis groupes/classes</a></li>'."\n";
				if(!getSettingAOui('bullNoSaisieElementsProgrammes')) {
					if((($_SESSION['statut']=='scolarite')&&(getSettingAOui("ScolGererMEP")))||
					($_SESSION['statut']=='administrateur')||
					($_SESSION['statut']=='professeur')) {
						$menus .= '                <li><a href="'.$gepiPath.'/saisie/gerer_mep.php" '.insert_confirm_abandon().'>Gérer les éléments de programmes</a></li>'."\n";
					}
				}
				$menus .= '            </ul>'."\n";
				$menus .= '     </li>'."\n";
	
				$menus .= '     <li class="plus">Moyennes'."\n";
				$menus .= '            <ul class="niveau3">'."\n";
				$menus .= '                <li><a href="'.$gepiPath.'/prepa_conseil/index1.php"'.insert_confirm_abandon().' title="Consulter les moyennes et appréciations d\'un professeur dans un enseignement en particulier.">Mes moy. et app.</a></li>'."\n";
				$menus .= '                <li><a href="'.$gepiPath.'/prepa_conseil/index2.php"'.insert_confirm_abandon().' title="Consulter le tableau des moyennes apparaissant sur les bulletins pour une classe en particulier.">Moy.d\'une classe</a></li>'."\n";
				$menus .= '            </ul>'."\n";
				$menus .= '     </li>'."\n";
	
				$menus .= '     <li class="plus"><a href="'.$gepiPath.'/visualisation/index.php"'.insert_confirm_abandon().'>Outils graphiques</a>'."\n";
				$menus .= '            <ul class="niveau3">'."\n";
				$menus .= '                <li><a href="'.$gepiPath.'/visualisation/affiche_eleve.php?type_graphe=courbe"'.insert_confirm_abandon().'>Courbe</a></li>'."\n";
				$menus .= '                <li><a href="'.$gepiPath.'/visualisation/affiche_eleve.php?type_graphe=etoile"'.insert_confirm_abandon().'>Etoile</a></li>'."\n";
				$menus .= '                <li><a href="'.$gepiPath.'/visualisation/eleve_classe.php"'.insert_confirm_abandon().'>Elève/classe</a></li>'."\n";
				$menus .= '                <li><a href="'.$gepiPath.'/visualisation/eleve_eleve.php"'.insert_confirm_abandon().'>Elève/élève</a></li>'."\n";
				$menus .= '                <li><a href="'.$gepiPath.'/visualisation/evol_eleve.php"'.insert_confirm_abandon().'>Evol. élève année</a></li>'."\n";
				$menus .= '                <li><a href="'.$gepiPath.'/visualisation/evol_eleve_classe.php"'.insert_confirm_abandon().'>Evol. élève/classe année</a></li>'."\n";
				$menus .= '                <li><a href="'.$gepiPath.'/visualisation/stats_classe.php"'.insert_confirm_abandon().'>Evol. moyennes classes</a></li>'."\n";
				$menus .= '                <li><a href="'.$gepiPath.'/visualisation/classe_classe.php"'.insert_confirm_abandon().'>Classe/classe</a></li>'."\n";
				$menus .= '            </ul>'."\n";
				$menus .= '     </li>'."\n";
	
				$menus .= '   </ul>'."\n";
				$menus .= '</li>'."\n";
			}
			//=======================================================

			//=======================================================
			// Carnets de notes
			$menus .= '<li class="li_inline"><a href="#"'.insert_confirm_abandon().'>&nbsp;Carnets de notes</a>'."\n";
			$menus .= '   <ul class="niveau2">'."\n";
			$menus .= '       <li><a href="'.$gepiPath.'/cahier_notes/visu_releve_notes_bis.php"'.insert_confirm_abandon().'>Relevés de notes</a></li>'."\n";
			$menus .= '       <li><a href="'.$gepiPath.'/cahier_notes/index2.php"'.insert_confirm_abandon().' title="Consulter le tableau des moyennes des Carnets de notes pour une classe en particulier.
Ces moyennes sont des moyennes à un instant T.
Elles peuvent évoluer avec l\'ajout de notes, la modification de coefficients,... par les professeurs.">Moyennes des CN</a></li>'."\n";
			if(getSettingAOui('PeutDonnerAccesCNPeriodeCloseScol')) {
				$menus .= '       <li><a href="'.$gepiPath.'/cahier_notes/autorisation_exceptionnelle_saisie.php"'.insert_confirm_abandon().'>Autorisation exceptionnelle de saisie de notes</a></li>'."\n";
			}
			$menus .= '       <li><a href="'.$gepiPath.'/cahier_notes/extraction_notes_cn.php"'.insert_confirm_abandon().'>Export CSV notes CN</a></li>'."\n";
			$menus .= '   </ul>'."\n";
			$menus .= '</li>'."\n";
			//=======================================================
		}

		if((getSettingAOui("active_mod_examen_blanc"))&&(getSettingAOui("active_mod_epreuve_blanche"))) {
			$menus .= '<li class="li_inline"><a href="#"'.insert_confirm_abandon().'>&nbsp;Ex/Ep.blancs</a>'."\n";
			$menus .= '   <ul class="niveau2">'."\n";
			$menus .= '      <li><a href="'.$gepiPath.'/mod_epreuve_blanche/index.php" '.insert_confirm_abandon().'>Epreuves blanches</a></li>'."\n";
			$menus .= '      <li><a href="'.$gepiPath.'/mod_examen_blanc/index.php" '.insert_confirm_abandon().'>Examens blancs</a></li>'."\n";
			$menus .= '   </ul>'."\n";
			$menus .= '</li>'."\n";
		}
		elseif(getSettingAOui("active_mod_examen_blanc")) {
			$menus .= '<li class="li_inline"><a href="'.$gepiPath.'/mod_examen_blanc/index.php" '.insert_confirm_abandon().'>Examens blancs</a></li>'."\n";
		}
		elseif(getSettingAOui("active_mod_epreuve_blanche")) {
			$menus .= '<li class="li_inline"><a href="'.$gepiPath.'/mod_examen_blanc/index.php" '.insert_confirm_abandon().'>Examens blancs</a></li>'."\n";
		}

		//=======================================================
		// Composantes du Socle

		if(getSettingAOui("SocleSaisieComposantes")) {
				$menus .= '<li class="li_inline"><a href="'.$gepiPath.'/saisie/socle_verif.php" '.insert_confirm_abandon().' title="Vérifier le remplissage des bilans de composantes du Socle">Socle</a>'."\n";
				$menus .= '   <ul class="niveau2">'."\n";

				if(getSettingAOui("SocleSaisieComposantes_".$_SESSION["statut"])) {
					$menus .= '      <a href="'.$gepiPath.'/saisie/saisie_socle.php" '.insert_confirm_abandon().' title="Saisir les bilans de composantes du Socle">Saisie&nbsp;Socle</a>'."\n";
				}
				if(getSettingAOui("SocleOuvertureSaisieComposantes_".$_SESSION["statut"])) {
					$menus .= '      <a href="'.$gepiPath.'/saisie/socle_verrouillage.php" '.insert_confirm_abandon().' title="Ouvrir/verrouiller la saisie des bilans de composantes du Socle">Verrouillage&nbsp;Socle</a>'."\n";
				}

				$menus .= '      <a href="'.$gepiPath.'/saisie/socle_verif.php" '.insert_confirm_abandon().' title="Vérifier le remplissage des bilans de composantes du Socle">Vérification&nbsp;remplissage</a>'."\n";

				if((getSettingAOui("SocleImportComposantes"))&&(getSettingAOui("SocleImportComposantes_".$_SESSION['statut']))) {
					$menus .= '      <a href="'.$gepiPath.'/saisie/socle_import.php" '.insert_confirm_abandon().' title="Importer les bilans de composantes du Socle d\'après SACoche">Import&nbsp;Socle</a>'."\n";
				}

				$menus .= '   </ul>'."\n";
				$menus .= '</li>'."\n";
		}

		//=======================================================
		// Module emploi du temps
		if (getSettingValue("autorise_edt_tous") == "y") {

			if(getSettingValue('edt_version_defaut')=="2") {
				$menus .= '<li class="li_inline"><a href="'.$gepiPath.'/edt/index2.php" '.insert_confirm_abandon().'>Emploi du temps</a>'."\n";

				$menus .= '   <ul class="niveau2">'."\n";
				$menus .= '       <li><a href="'.$gepiPath.'/edt/index2.php"'.insert_confirm_abandon().'>EDT prof/classe/élève</a></li>'."\n";
				$menus .= '       <li><a href="'.$gepiPath.'/edt_organisation/index_edt.php?visioedt=salle1"'.insert_confirm_abandon().'>EDT salle</a></li>'."\n";
				$menus .= '   </ul>'."\n";
				$menus .= '</li>'."\n";
			}
			else {
				$menus .= '<li class="li_inline"><a href="'.$gepiPath.'/edt_organisation/index_edt.php?visioedt=classe1"'.insert_confirm_abandon().'>Emploi du tps</a>'."\n";

				$menus .= '   <ul class="niveau2">'."\n";
				$menus .= '       <li><a href="'.$gepiPath.'/edt_organisation/index_edt.php?visioedt=classe1"'.insert_confirm_abandon().'>EDT classe</a></li>'."\n";
				$menus .= '       <li><a href="'.$gepiPath.'/edt_organisation/index_edt.php?visioedt=prof1"'.insert_confirm_abandon().'>EDT prof</a></li>'."\n";
				$menus .= '       <li><a href="'.$gepiPath.'/edt_organisation/index_edt.php?visioedt=salle1"'.insert_confirm_abandon().'>EDT salle</a></li>'."\n";
				$menus .= '       <li><a href="'.$gepiPath.'/edt_organisation/index_edt.php?visioedt=eleve1"'.insert_confirm_abandon().'>EDT élève</a></li>'."\n";
				$menus .= '   </ul>'."\n";
				$menus .= '</li>'."\n";
			}

		}

		if(getSettingAOui('active_edt_ical')) {
			$menus .= '<li class="li_inline"><a href="'.$gepiPath.'/edt/index.php" '.insert_confirm_abandon().' title="Emplois du temps importés à l\'aide de fichiers ICAL/ICS.">EDT Ical/Ics</a></li>'."\n";
		}
		//=======================================================

		//=======================================================
		// Module discipline
		if (getSettingValue("active_mod_discipline")=='y') {
			$temoin_disc="";
			if((getPref($_SESSION['login'], 'DiscTemoinIncidentScol', "n")=="y")||(getPref($_SESSION['login'], 'DiscTemoinIncidentScolTous', "n")=="y")) {
				$cpt_disc=get_temoin_discipline_personnel();
				if($cpt_disc>0) {
					$DiscTemoinIncidentTaille=getPref($_SESSION['login'], 'DiscTemoinIncidentTaille', 16);
					$temoin_disc=" <img src='$gepiPath/images/icons/flag2.gif' width='$DiscTemoinIncidentTaille' height='$DiscTemoinIncidentTaille' title=\"Un ou des ".$mod_disc_terme_incident."s ($cpt_disc) ont été saisis dans les dernières 24h ou depuis votre dernière connexion.\" />";
				}
			}
			$menus .= '<li class="li_inline"><a href="'.$gepiPath.'/mod_discipline/index.php"'.insert_confirm_abandon().'>Discipline</a>'.$temoin_disc.'</li>'."\n";
		}
		//=======================================================

		//=======================================================
		// Module Actions
		if(getSettingAOui('active_mod_actions')) {
			$tab_actions_categories=get_tab_actions_categories();
			if(count($tab_actions_categories)>0) {
				$terme_mod_action=getSettingValue('terme_mod_action');
				$menus .= '  <li class="li_inline"><a href="'.$gepiPath.'/mod_actions/index.php" '.insert_confirm_abandon().'>'.$terme_mod_action.'s</a></li>'."\n";
			}
		}
		//=======================================================

		//=======================================================
		// Gestion
		$menus .= '<li class="li_inline"><a href="#"'.insert_confirm_abandon().'>&nbsp;Gestion</a>'."\n";
		$menus .= '   <ul class="niveau2">'."\n";
		$menus .= '       <li class="niveau3"><a href="'.$gepiPath.'/eleves/recherche.php"'.insert_confirm_abandon().' title="Effectuer une recherche sur une personne (élève, responsable ou personnel)">Rechercher</a>'."</li>\n";
		$menus .= '       <li class="plus"><a href="'.$gepiPath.'/eleves/index.php"'.insert_confirm_abandon().'>Elèves</a>'."\n";
		$menus .= '           <ul class="niveau3">'."\n";
		$menus .= '                <li><a href="'.$gepiPath.'/eleves/index.php"'.insert_confirm_abandon().'>Gestion élèves</a></li>'."\n";
		//$menus .= '                <li><a href="'.$gepiPath.'/responsables/maj_import2.php"'.insert_confirm_abandon().'>Mise à jour Sconet</a></li>'."\n";
		if($acces_saisie_modalites_accompagnement) {
			$menus .= '                <li><a href="'.$gepiPath.'/gestion/saisie_modalites_accompagnement.php"'.insert_confirm_abandon().' title="Saisir les modalités d accompagnement des élèves (PPRE, SEGPA, ULIS,...).">Modalités d\'accompagnement</a></li>'."\n";
		}
		$menus .= '                <li><a href="'.$gepiPath.'/eleves/visu_eleve.php"'.insert_confirm_abandon().'>Consultation elève</a></li>'."\n";
		$menus .= '                <li><a href="'.$gepiPath.'/classes/acces_appreciations.php"'.insert_confirm_abandon().'>Accès appréciations</a></li>'."\n";
		$menus .= '       <li class="niveau3"><a href="'.$gepiPath.'/eleves/recherche.php"'.insert_confirm_abandon().' title="Effectuer une recherche sur une personne (élève, responsable ou personnel)">Rechercher</a>'."</li>\n";

		if(getSettingValue('active_module_trombinoscopes')=='y') {
			$menus .= '       <li class="plus"><a href="'.$gepiPath.'/mod_trombinoscopes/trombinoscopes.php"'.insert_confirm_abandon().'>Trombinoscopes</a>'."\n";
			$menus .= '            <ul class="niveau4">'."\n";
			for($loop=0;$loop<count($tmp_liste_classes_scol);$loop++) {
				$menus .= '                <li><a href="'.$gepiPath.'/mod_trombinoscopes/trombino_pdf.php?classe='.$tmp_liste_classes_scol[$loop]['id'].'&amp;groupe=&amp;equipepeda=&amp;discipline=&amp;statusgepi=&amp;affdiscipline="'.insert_confirm_abandon().' target="_blank">'.$tmp_liste_classes_scol[$loop]['classe'].' ('.$tmp_liste_classes_scol[$loop]['nom_complet'].')</a></li>'."\n";
			}
			$menus .= '            </ul>'."\n";
			$menus .= '       </li>'."\n";
		}

		$menus .= '            </ul>'."\n";
		$menus .= '       <li class="plus">Classes'."\n";
		$menus .= '           <ul class="niveau3">'."\n";
		$menus .= '               <li><a href="'.$gepiPath.'/groupes/visu_profs_class.php"'.insert_confirm_abandon().'>Visu. équipes péda</a></li>'."\n";
		$menus .= '               <li><a href="'.$gepiPath.'/groupes/visu_groupes_prof.php" '.insert_confirm_abandon().' title="Consulter les enseignements d\'un prof.">Enseign.tel prof</a></li>'."\n";
		if($acces_saisie_modalites_accompagnement) {
			$menus .= '               <li><a href="'.$gepiPath.'/gestion/saisie_modalites_accompagnement.php"'.insert_confirm_abandon().' title="Saisir les modalités d accompagnement des élèves (PPRE, SEGPA, ULIS,...).">Modalités d\'accompagnement</a></li>'."\n";
		}
		$menus .= '            </ul>'."\n";

		if(acces_modif_liste_eleves_grp_groupes()) {
			$groupe_de_groupes=getSettingValue('denom_groupe_de_groupes');
			if($groupe_de_groupes=="") {
				$groupe_de_groupes="groupe de groupes";
			}

			$groupes_de_groupes=getSettingValue('denom_groupes_de_groupes');
			if($groupes_de_groupes=="") {
				$groupes_de_groupes="groupes de groupes";
			}

			$menus .= '       <li class="plus"><a href="'.$gepiPath.'/groupes/grp_groupes_edit_eleves.php"'.insert_confirm_abandon().' title="Administrer les '.$groupes_de_groupes.' pour modifier les inscriptions élèves.">'.ucfirst($groupes_de_groupes).'</a>'."\n";

			$menus .= '       <ul class="niveau3">'."\n";
			$menus .= '           <li><a href="'.$gepiPath.'/groupes/grp_groupes_edit_eleves.php"'.insert_confirm_abandon().' title="Administrer les '.$groupes_de_groupes.' pour modifier les inscriptions élèves.">'.ucfirst($groupes_de_groupes).'</a></li>'."\n";
			$menus .= '           <li><a href="'.$gepiPath.'/groupes/repartition_ele_grp.php"'.insert_confirm_abandon().' title="Répartir les élèves des groupes d un '.$groupe_de_groupes.' entre les différents groupes/enseignements.">Répartir entre plusieurs groupes</a></li>'."\n";
			$menus .= '       </ul>'."\n";
			$menus .= '       </li>'."\n";
		}

		// AID
		/*
		$sql="SELECT ac.* FROM j_aid_utilisateurs_gest jaug, aid_config ac WHERE jaug.id_utilisateur='".$_SESSION['login']."' AND jaug.indice_aid=ac.indice_aid;";
		$test_aid1=mysqli_query($mysqli, $sql);
		$sql="SELECT * FROM j_aidcateg_super_gestionnaires WHERE id_utilisateur='".$_SESSION['login']."';";
		$test_aid2=mysqli_query($mysqli, $sql);
		if((mysqli_num_rows($test_aid1)>0)||(mysqli_num_rows($test_aid2)>0)) {
			$menus .= '       <li class="plus">AID'."\n";
			$menus .= '       <ul class="niveau3">'."\n";
			while() {
				$menus .= '           <li><a href="'.$gepiPath.'/groupes/grp_groupes_edit_eleves.php"'.insert_confirm_abandon().' title="Administrer les '.$groupes_de_groupes.' pour modifier les inscriptions élèves.">'.ucfirst($groupes_de_groupes).'</a></li>'."\n";
			}
			$menus .= '       </ul>'."\n";
			$menus .= '       </li>'."\n";

			//$nom_aid = @old_mysql_result($call_data, $i, "nom");
			$nom_aid = $obj->nom;
			if ($nb_result2 != 0)
			$this->creeNouveauItem("/aid/index2.php?indice_aid=".$indice_aid,
			$nom_aid,
		}
		*/

		$sql="(SELECT ac.* FROM j_aid_utilisateurs_gest jaug, aid_config ac WHERE jaug.id_utilisateur='".$_SESSION['login']."' AND jaug.indice_aid=ac.indice_aid)
		UNION (SELECT ac.* FROM j_aidcateg_super_gestionnaires jaug, aid_config ac WHERE jaug.id_utilisateur='".$_SESSION['login']."' AND jaug.indice_aid=ac.indice_aid);";
		$test_aid_tmp=mysqli_query($mysqli, $sql);
		if(mysqli_num_rows($test_aid_tmp)>0) {
			$menus .= '       <li class="plus">AID'."\n";
			$menus .= '       <ul class="niveau3">'."\n";
			$tmp_aid_deja=array();
			while($lig_aid_tmp=mysqli_fetch_object($test_aid_tmp)) {
				if(!in_array($lig_aid_tmp->indice_aid, $tmp_aid_deja)) {
					$menus .= '           <li><a href="'.$gepiPath.'/aid/index2.php?indice_aid='.$lig_aid_tmp->indice_aid.'"'.insert_confirm_abandon().' title="Gérer l AID.">'.$lig_aid_tmp->nom.'</a></li>'."\n";
					$tmp_aid_deja[]=$lig_aid_tmp->indice_aid;
				}
			}
			$menus .= '       </ul>'."\n";
			$menus .= '       </li>'."\n";
		}

		$menus .= '       </li>'."\n";
		$menus .= '       <li class="plus"><a href="'.$gepiPath.'/responsables/index.php"'.insert_confirm_abandon().'>Responsables</a>'."\n";
		$menus .= '           <ul class="niveau3">'."\n";
		$menus .= '                <li><a href="'.$gepiPath.'/responsables/index.php"'.insert_confirm_abandon().'>Gestion responsables</a></li>'."\n";
		//$menus .= '                <li><a href="'.$gepiPath.'/responsables/maj_import2.php"'.insert_confirm_abandon().'>Mise à jour Sconet</a></li>'."\n";
		$menus .= '                <li><a href="'.$gepiPath.'/classes/acces_appreciations.php"'.insert_confirm_abandon().'>Accès appréciations</a></li>'."\n";

		$menus .= '                <li><a href="'.$gepiPath.'/responsables/infos_parents.php" title="Extraire les informations parents/élèves au format CSV.">Infos ele/resp</a></li>'."\n";

		$sql="SELECT 1=1 FROM utilisateurs WHERE statut='responsable';";
		$test_resp=mysqli_query($GLOBALS["mysqli"], $sql);
		if(mysqli_num_rows($test_resp)>0) {
			$menus .= '                <li><a href="'.$gepiPath.'/responsables/synchro_mail.php" title="Les responsables peuvent avoir un mail défini dans Siècle/Sconet et un autre saisi par l\'utilisateur parent se connectant dans Gepi.">Synchro mail</a>'."\n";
		}

		$menus .= '            </ul>'."\n";
		$menus .= '       </li>'."\n";
		$menus .= '       <li><a href="'.$gepiPath.'/messagerie/index.php"'.insert_confirm_abandon().' title="Le Panneau d\'affichage permet de faire apparaître en page d\'accueil des messages destinés à certains utilisateurs ou catégories d\'utilisateurs à compter d\'une date à choisir et pour une durée à choisir également.">Panneau d\'affichage</a></li>'."\n";
		$menus .= '       <li><a href="'.$gepiPath.'/classes/dates_classes.php"'.insert_confirm_abandon().' title="Faire apparaître des événements en page d\'accueil pour telle ou telle classe de telle à telle date,...
Vous pouvez notamment faire apparaître un tableau des dates de conseils de classe.">Dates événements</a></li>'."\n";
		$menus .= '       <li><a href="'.$gepiPath.'/statistiques/index.php"'.insert_confirm_abandon().'>Statistiques</a></li>'."\n";

		if(getSettingAOui('active_mod_engagements')) {
			$menus .= '       <li class="plus"><a href="#">Engagements</a>'."\n";
			$menus .= '         <ul class="niveau3">'."\n";
			if($acces_saisie_engagement=="y") {
				$menus .= '           <li><a href="'.$gepiPath.'/mod_engagements/saisie_engagements.php" '.insert_confirm_abandon().' title="Saisir les engagements élèves/responsables.">Saisie engagements</a></li>'."\n";
			}

			$menus .= '           <li><a href="'.$gepiPath.'/mod_engagements/imprimer_documents.php" '.insert_confirm_abandon().'>Convocation conseil de classe,...</a></li>'."\n";
			$menus .= '           <li><a href="'.$gepiPath.'/mod_engagements/extraction_engagements.php" '.insert_confirm_abandon().' title="Extraire en CSV, envoyer par mail.">Extraction engagements</a></li>'."\n";
			$menus .= '         </ul>'."\n";
			$menus .= '       </li>'."\n";
		}

		if((getSettingAOui('active_mod_orientation'))&&((getSettingAOui('OrientationSaisieTypeScolarite'))||(getSettingAOui('OrientationSaisieOrientationScolarite'))||(getSettingAOui('OrientationSaisieVoeuxScolarite')))) {
			$menus .= '  <li><a href="'.$gepiPath.'/mod_orientation/index.php" '.insert_confirm_abandon().'>Orientation</a></li>'."\n";
		}

		if(!getSettingAOui('bullNoSaisieElementsProgrammes')) {
			if((($_SESSION['statut']=='scolarite')&&(getSettingAOui("ScolGererMEP")))||
			($_SESSION['statut']=='administrateur')||
			($_SESSION['statut']=='professeur')) {
				$menus .= '  <li><a href="'.$gepiPath.'/saisie/gerer_mep.php" '.insert_confirm_abandon().'>Gérer les éléments de programmes</a></li>'."\n";
			}
		}

		if ((getSettingAOui('active_mod_genese_classes'))&&(getSettingAOui('geneseClassesSaisieProfilsScol'))) {
			$menus .= '  <li><a href="'.$gepiPath.'/mod_genese_classes/saisie_profils_eleves.php"'.insert_confirm_abandon().'>Saisie profils élèves pour classes futures</a></li>'."\n";
		}

		$menus .= '   </ul>'."\n";
		$menus .= '</li>'."\n";
		//=======================================================

		//=======================================================
		$menus .= '<li class="li_inline"><a href="#"'.insert_confirm_abandon().'>&nbsp;Listes</a>'."\n";
		$menus .= '   <ul class="niveau2">'."\n";
		$menus .= '       <li><a href="'.$gepiPath.'/groupes/visu_profs_class.php"'.insert_confirm_abandon().'>Visu. équipes péda</a></li>'."\n";
		$menus .= '       <li><a href="'.$gepiPath.'/groupes/visu_groupes_prof.php" '.insert_confirm_abandon().' title="Consulter les enseignements d\'un prof.">Enseign.tel prof</a></li>'."\n";
		$menus .= '       <li><a href="'.$gepiPath.'/groupes/visu_mes_listes.php"'.insert_confirm_abandon().'>Visu. mes élèves</a></li>'."\n";
		$menus .= '       <li><a href="'.$gepiPath.'/mod_ooo/publipostage_ooo.php"'.insert_confirm_abandon().' title="Publipostage au format openDocument.org d\'après des données élèves">Publipostage OOo</a></li>'."\n";
		$menus .= '       <li><a href="'.$gepiPath.'/impression/impression_serie.php"'.insert_confirm_abandon().'>Impression PDF listes</a></li>'."\n";
		$menus .= '       <li><a href="'.$gepiPath.'/groupes/mes_listes.php"'.insert_confirm_abandon().'>Export CSV listes</a></li>'."\n";
		if (getSettingAOui("GepiListePersonnelles")) {
			$menus .= '       <li><a href="'.$gepiPath.'/mod_listes_perso/index.php"'.insert_confirm_abandon().' title=\"Créer et imprimer des listes personnelles\">Listes personnelles</a></li>'."\n";
		}
		if(getSettingValue('active_module_trombinoscopes')=='y') {
			$menus .= '       <li class="plus"><a href="'.$gepiPath.'/mod_trombinoscopes/trombinoscopes.php"'.insert_confirm_abandon().'>Trombinoscopes</a>'."\n";
			$menus .= '            <ul class="niveau3">'."\n";
			for($loop=0;$loop<count($tmp_liste_classes_scol);$loop++) {
				$menus .= '                <li><a href="'.$gepiPath.'/mod_trombinoscopes/trombino_pdf.php?classe='.$tmp_liste_classes_scol[$loop]['id'].'&amp;groupe=&amp;equipepeda=&amp;discipline=&amp;statusgepi=&amp;affdiscipline="'.insert_confirm_abandon().' target="_blank">'.$tmp_liste_classes_scol[$loop]['classe'].' ('.$tmp_liste_classes_scol[$loop]['nom_complet'].')</a></li>'."\n";
			}
			$menus .= '            </ul>'."\n";
			$menus .= '       </li>'."\n";
		}
		$menus .= '   </ul>'."\n";
		$menus .= '</li>'."\n";
		//=======================================================

		//$menus='<li class="li_inline"><a href="'.$gepiPath.'/accueil.php"'.insert_confirm_abandon().'>Accueil</a></li>'."\n".$menus;

		$menus .= $barre_plugin;

		$tbs_menu_scol[]=array("li"=> '<li class="li_inline"><a href="'.$gepiPath.'/accueil.php"'.insert_confirm_abandon().'>Accueil</a></li>'."\n");		
		$tbs_menu_scol[]=array("li"=> $menus);	

	}

?>
