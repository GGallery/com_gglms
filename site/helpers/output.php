<?php
/**
 * @package		Joomla.Tutorials
 * @subpackage	Components
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		License GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access to this file
defined('_JEXEC') or die;

class outputHelper {

    public static function buildContentBreadcrumb($id){

        $breadcrumblist= array();
        try {
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);

            $query->select('c.*, u.idunita');
            $query->from('#__gg_unit_map AS u');
            $query->join('inner', '#__gg_contenuti as c on u.idcontenuto = c.id');
            $query->where("u.idcontenuto=" . $id);
            $query->setLimit(1);

            $db->setQuery($query);
            $content = $db->loadObject();

            $breadcrumblist[] = $content;

            $unitbreadcrumb = outputHelper::buildUnitBreadcrumb($content->idunita);

            $breadcrumblist = (array_merge(($unitbreadcrumb), $breadcrumblist));

            return $breadcrumblist;

        }catch (Exception $e){
            DEBUGG::log($e, "ERROR", 1);
        }

    }



    public static function buildUnitBreadcrumb($id){

        $currentid= $id;
        $breadcrumblist= array();

        while ($currentid > 0 ){
            $element = outputHelper::queryUnitDb($currentid);
            $breadcrumblist[]=$element;
            $currentid      = $element->unitapadre;
        }

        $breadcrumblist = array_reverse($breadcrumblist);

        return $breadcrumblist;

    }

    public  static function queryUnitDb($id){

        try {
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);

            $query->select('id, unitapadre, titolo, alias');
            $query->from('#__gg_unit AS u');
            $query->where("u.id=" . $id);

            $db->setQuery($query);
            $res = $db->loadObject();

            return $res;
        }
        catch (Exception $e){
            echo "";
            DEBUGG::log($e, "Problemi nel creare il brearcrumb - sono nel queryUnitDb", 1);
        }
    }








    public static function DISATTIVATOmenu($item = 2, $active = null) {

        $root = outputHelper::getUnitmenu($item);
        $out = '<nav>';
        $out.=outputHelper::buildmenu($root, 0, $active);
        $out.='</nav>';
        return $out;
    }

    public static function DISATTIVATObuildmenu($items, $level = 0, $active = null) {

        // FB::log($items, "items build menu") ;
        $classlevel = "level" . $level;
        $level++;
        $badge = "";
        $out = "";


        if (sizeof($items) > 0) {
            $out = "<ul class='$classlevel list-group'>";

            foreach ($items as $item) {
                if (isset($item->titolo)) {
                    // FB::log($active."-".$item->id, "active - item id");
                    $activeclass = ($active && $active == $item->id) ? " active " : "";

                    $out .="<li class='list-group-item" . $activeclass . "'>";

                    $subUnit = outputHelper::getUnitmenu($item->id);

                    // if (sizeof($subUnit) > 0)
                    //     $badge = ' <span class="badge">' . sizeof($subUnit) . '</span>';
                    $badge = ''; //Basta scommentare le righe sopra per riattivare il numero di sottounit nel menu.

                    $out.='<a class="link' . $activeclass . '" href="' . JURI::base() . "component/gglms/unita/" . $item->alias . '">' . $item->titolo . $badge . '</span></a>';
                    $out.=outputHelper::buildmenu($subUnit, $level, $active);

                    $out.="</li>";
                }
            }
            $out.="</ul>";
        }

        return $out;
    }

    public static function DISATTIVATOgetUnitmenu($item) {
        try {

            $db = JFactory::getDbo();
            $query = $db->getQuery(true);

            $query->select('*');
            $query->from('#__gg_unit AS u');
            $query->where("u.categoriapadre=" . $item);
            $query->where("u.tipologia != 110");
            $query->order("ordinamento");





            $db->setQuery($query);
            // Check for a database error.
            if ($db->getErrorNum()) {
                JError::raiseWarning(500, $db->getErrorMsg());
            }

            $res = $db->loadObjectList();

            foreach ($res as $key => $item) {
//                $sub_content = gglmsHelper::getTOTContenuti($item->id);
//                $sub_unit = gglmsHelper::getSubUnit($item->id);

//                if (!$sub_content && !$sub_unit)
//                    unset($res[$key]);
            }

            DEBUGG::log($res, " getUnitMenu");

            return $res;
        } catch (Exception $e) {

        }
    }

    public static function DISATTIVATOgetContentIconStatus($prerequisiti, $stato) {

        if (!$prerequisiti) {
            echo '<img class="img-rounded" title="Contenuto non ancora visionabile" src="components/com_gglms/images/state_red.jpg"> ';
        } else {
            if ($stato == "completed") {
                echo '<img class="img-rounded" title="Contenuto già visionato" src="components/com_gglms/images/state_green.jpg">';
            } else {
                echo '<img class="img-rounded" title="Contenuto da visionare" src="components/com_gglms/images/state_grey.jpg"> ';
            }
        }
    }

    public static function DISATTIVATOconvertiDurata($durata) {
        $m = floor(($durata % 3600) / 60);
        $s = ($durata % 3600) % 60;
        $result = sprintf('%02d:%02d', $m, $s);

        return $result;
    }

    public static function DISATTIVATOgetContent_Footer($item){

        DEBUGG::log($item, 'itemFooter');


        echo '<a href="component/gglms/contenuto/'. $item['alias'] . '"  title="'.htmlentities(utf8_decode($item['abstract'])).'" >';
        ?>
        <div class="boxContentFooter img-rounded">
            <div class="boxtitle">
                <?php
                $maxlengh = 80;
                if(strlen($item['titolo'])>$maxlengh)
                    $item['titolo'] = substr($item['titolo'], 0, $maxlengh)."...";
                echo $item['titolo'];
                ?>
            </div>

            <div class="boximg">

                <?php
                if(file_exists('../mediagg/contenuti/'.$item["id"].'/'.$item["id"].'.jpg'))
                    echo '<img class="img-responsive" src="../mediagg/contenuti/'.$item["id"].'/'.$item["id"].'.jpg">';
                else
                    echo '<img class="img-responsive" src="components/com_gglms/images/sample.jpg">';
                ?>
            </div>

            <div class="boxinfo">
                <table width="100%">
                    <tr>
                        <td rowspan="2" width="33%"><?php echo  outputHelper::getContentIconStatus($item); ?> </td>
                        <!--  <td width="33%">Durata</td>
                <td width="33%"><?php //echo outputHelper::convertiDurata($item["durata"]);   ?></td> -->
                    </tr>
                    <tr>
                        <!--  <td>Visite</td>
                <td><?php //echo $item["views"]; ?></td> -->
                    </tr>
                </table>
            </div>
        </div>
        </a>
        <?php
    }


    public static function output_select ($name, $items, $value, $text, $default=null, $class=null)
    {
        
        
        $html = '<select id="'.$name.'" name="'.$name.'" class="'.$class.'">';

        foreach ($items as $item)
        {
                $selected = ($item->$value == $default) ? 'selected="selected"' : '';

                $html .= "<option value=".$item->$value." $selected>".$item->$text."</option>";
        }
        $html .= "</select>";
        return $html;
    }

    public static function getDettaglioVisione($durata = 0,
                                               $tempo_visualizzato,
                                               $con_orari = false,
                                               $tempo_assenza = null) {

        $_html = "";
        // con orari customizzati
        if ($con_orari
            && !is_null($tempo_assenza)) {

            $_html = self::buildRowDettagliTemporali($durata, $tempo_visualizzato, $tempo_assenza);
HTML;
        } // calcolo la % completamento su progress bar
        else if ($durata > 0
            && $tempo_visualizzato <= $durata) {
            $perc_completamento = ($tempo_visualizzato/$durata)*100;
            // rendo int la %
            $perc_completamento = round($perc_completamento);
            // bg della barra in base a %
            $style_barra = self::setProgressBarStyle($perc_completamento);
            $_cell_title1 = JText::_('COM_GGLMS_CRUSCOTTO_ORE_STR4');
            $_cell_title2 = JText::_('COM_GGLMS_CRUSCOTTO_ORE_STR5');
            //$durata_ore = gmdate("H:i:s", $durata);
            $durata_ore = utilityHelper::sec_to_hr($durata);
            $_html = <<<HTML
            <div class="row">
                <div class="col-xs-6"><strong>{$_cell_title1}:</strong> {$durata_ore}</div>
            </div>
            <div class="row">
                <div class="col-xs-6"><strong>{$_cell_title2}</strong></div>
            </div>
            <div class="row">
                <div class="col-xs-10">
                    <div class="progress">
                        <div class="progress-bar progress-bar-striped {$style_barra}" 
                            role="progressbar" 
                            style="width: {$perc_completamento}%; height: 100% !important; color: black; font-weight: bold;" aria-valuenow="{$perc_completamento}" aria-valuemin="0" aria-valuemax="100">{$perc_completamento}%</div>
                    </div>
                </div>
            </div>
HTML;

        }
        // converto in ore i secondi
        else {
            //$ore_visualizzazione = gmdate("H:i:s", $tempo_visualizzato);
            $ore_visualizzazione = UtilityHelper::sec_to_hr($tempo_visualizzato);
            $_cell_title = JText::_('COM_GGLMS_CRUSCOTTO_ORE_STR2');
            $_html = <<<HTML
            <div class="row">
                <div class="col-xs-6">{$_cell_title}:</div>
                <div class="col-xs-3">{$ore_visualizzazione}</div>
            </div>
HTML;
        }

        return $_html;
    }

    private static function buildRowDettagliTemporali($durata, $visualizzazione, $assenza, $is_totale = false) {

        $_html = "";

        $ore_durata = utilityHelper::sec_to_hr($durata);
        $ore_visualizzazione = utilityHelper::sec_to_hr($visualizzazione);
        $ore_assenza = utilityHelper::sec_to_hr($assenza);
        $_cell_title1 = ($is_totale) ? JText::_('COM_GGLMS_CRUSCOTTO_ORE_STR10') . ' ' : "";
        $_cell_title1 .= JText::_('COM_GGLMS_CRUSCOTTO_ORE_STR6');
        $_cell_title2 = ($is_totale) ? JText::_('COM_GGLMS_CRUSCOTTO_ORE_STR10') . ' ' : "";
        $_cell_title2 .= JText::_('COM_GGLMS_CRUSCOTTO_ORE_STR7');
        $_cell_title3 = ($is_totale) ? JText::_('COM_GGLMS_CRUSCOTTO_ORE_STR10') . ' ' : "";
        $_cell_title3 .= JText::_('COM_GGLMS_CRUSCOTTO_ORE_STR8');
        $_row_style = "";

        if ($is_totale) {
            $_html .= <<<HTML
            <div class="row">
                &nbsp;
            </div>

HTML;
            $_row_style = 'style="background: #cce5ff; font-weight: bolder;"';
        }
        $_html .= <<<HTML
            <div class="row" {$_row_style}>
                <div class="col-xs-2">{$_cell_title1}:</div>
                <div class="col-xs-2">{$ore_durata}</div>
                <div class="col-xs-2">{$_cell_title2}:</div>
                <div class="col-xs-2">{$ore_visualizzazione}</div>
                <div class="col-xs-2">{$_cell_title3}:</div>
                <div class="col-xs-2">{$ore_assenza}</div>
            </div>
HTML;

        return $_html;

    }

    public static function getRowTotaleCorso($totale_durata,
                                             $totale_visualizzazione,
                                             $con_orari = false,
                                             $totale_assenza = 0) {

        $_html = "";
        if ($con_orari) {
            $_html = self::buildRowDettagliTemporali($totale_durata, $totale_visualizzazione, $totale_assenza, true);
        }
        else {
            $perc_completamento = 0;

            if ($totale_durata > 0
                && $totale_visualizzazione <= $totale_durata) {
                $perc_completamento = ($totale_visualizzazione / $totale_durata) * 100;
                // rendo int la %
                $perc_completamento = round($perc_completamento);
                // bg della barra in base a %
            }

            $style_barra = self::setProgressBarStyle($perc_completamento);

            $_cell_title = JText::_('COM_GGLMS_CRUSCOTTO_ORE_STR3');
            $_html .= <<<HTML
            <div class="row">
                <div class="col-xs-6">
                    <h5><strong>{$_cell_title}</strong></h5>
                </div>
            </div>
            <div class="row">
                <div class="col-xs-10">
                    <div class="progress" style="height: 25px;">
                        <div class="progress-bar {$style_barra}" 
                            role="progressbar" 
                            style="width: {$perc_completamento}%; height: 100%; color: black; font-weight: bold;" aria-valuenow="{$perc_completamento}" aria-valuemin="0" aria-valuemax="100">{$perc_completamento}%</div>
                    </div>
                </div>
            </div>
HTML;
        }

        return $_html;

    }

    public static function setProgressBarStyle($perc_bar) {

        switch ($perc_bar) {

            case ($perc_bar <= 10):
                return "bg-danger";

            case ($perc_bar > 10 && $perc_bar <= 50):
                return "bg-warning";

            case ($perc_bar > 50 && $perc_bar <= 75):
                return "bg-info";

            case ($perc_bar > 75 && $perc_bar <= 100):
                return "bg-success";

            default:
                return "";
        }

    }

    public static function buildRowsDettaglioCorsi($arr_corsi, $arr_dettaglio_corsi, $con_orari) {

        try {

            $cards = 0;
            $semaforo_totale = true;
            $totale_durata  = 0;
            $totale_visualizzazione = 0;
            $totale_assenza = 0;
            $corsi = 0;

            // se ci sono più corsi visualizzerò un riga in più con i totali delle durate dei singoli corsi e delle visualizzazioni
            if (count($arr_dettaglio_corsi) > 1) {
                $semaforo_totale = true;
            }

            $_html = <<<HTML
            <div id="accordion">
HTML;

            foreach ($arr_dettaglio_corsi as $id_padre => $sub_corso) {

                $titolo_padre = utilityHelper::getTitoloCorsoPadre($id_padre, $arr_corsi);

                $_html .= <<<HTML
                <div class="card">
                    <div class="card-header" id="heading-{$cards}">
                        <h5 class="mb-0">
                            <button class="btn btn-link" 
                                    data-toggle="collapse" 
                                    data-target="#collapse-{$cards}" 
                                    aria-expanded="true" 
                                    aria-controls="collapse-{$cards}" 
                                    style="background: #fff; color: red; line-height: inherit;">
                                <strong>{$titolo_padre}</strong>
                            </button>
                        </h5>
                    </div>
                    <div id="collapse-{$cards}" 
                         class="collapse show" 
                         aria-labelledby="heading-{$cards}" 
                         data-parent="#accordion">
                        <div class="card-body">
HTML;
                foreach ($sub_corso as $key => $corso) {

                    // se anche uno dei corsi ha durata 0 non visualizzo la barra dei totale
                    if ($corso['durata_evento'] == 0)
                        $semaforo_totale = false;

                    $tempo_assenza = (isset($corso['tempo_assenza']) && $con_orari) ? $corso['tempo_assenza'] : null;
                    $dettaglio_visione = self::getDettaglioVisione($corso['durata_evento'],
                                                                $corso['tempo_visualizzato'],
                                                                $con_orari,
                                                                $tempo_assenza);
                    $titolo_evento = (!$con_orari) ? $corso['titolo_evento'] : JText::_('COM_GGLMS_CRUSCOTTO_ORE_STR9') . ' ' . $corso['data_accesso'];
                    $_html .= <<<HTML
                       <div class="row">
                            <div class="col-xs-6">
                                <h6><strong>{$titolo_evento}</strong></h6>
                            </div>
                       </div>
                       {$dettaglio_visione}
HTML;
                    if (!$con_orari)
                        $totale_durata += $corso['durata_evento'];
                    else {
                        $totale_durata = $corso['totale_durata'];
                        $totale_assenza += $corso['tempo_assenza'];
                    }

                    $totale_visualizzazione += $corso['tempo_visualizzato'];
                    $corsi++;
                }

                if ($semaforo_totale
                    && $corsi > 1)
                    $_html .= self::getRowTotaleCorso($totale_durata,
                                                    $totale_visualizzazione,
                                                    $con_orari,
                                                    $totale_assenza);

                $_html .= <<<HTML
                        </div><!-- card-body -->
                    </div> <!-- collapse show -->
                </div> <!-- card -->
HTML;
                $cards++;
            }

            $_html .= <<<HTML
            </div> <!-- accordion -->
HTML;

            return $_html;

        } catch (Exception $e) {
            DEBUGG::error($e, __FUNCTION__);
        }

    }

    public static function buildListaAzienda($lista_azienda) {

        if (isset($lista_azienda)) {
            $_selected = "";
            $_default = "";
            if (count($lista_azienda) == 1)
                $_selected = "selected";
            else {
                $_option_label = JText::_('COM_GGLMS_GLOBAL_SCEGLI_AZIENDA');
                $_default = <<<HTML
                <option value="">{$_option_label}</option>
HTML;
            }
            $_company_label = JText::_('COM_GGLMS_GLOBAL_COMPANY');
            echo <<<HTML
                <div class="form-group row">
                    <label class="col-sm-2" for="id_azienda">{$_company_label}:</label>
                    <div class="col-sm-10">
                        <select required placeholder="Azienda" type="text" class="form-control cpn_opt"
                                id="id_azienda" name="id_azienda">
                            {$_default}
HTML;

                            foreach ($lista_azienda as $key => $az) {
                                echo <<<HTML
                                <option value="{$az['id_gruppo']}" {$_selected}>
                                    {$az['azienda']}
                                </option>
HTML;
                            }
            echo <<<HTML
                        </select>
                    </div>
                </div>
HTML;
        }

    }

    public static function buildFiltroAzienda($usergroups) {

        if (isset ($usergroups)) {
            if (count($usergroups) > 1) {
                $_select_output = outputHelper::output_select('usergroups', $usergroups, 'id', 'title', 2, 'refresh');
                $_company_label = JText::_('COM_GGLMS_GLOBAL_COMPANY');
                echo <<<HTML
                <div class="form-group">
                    <label for="usergroups">{$_company_label}</label><br>
                    {$_select_output}
                </div>
HTML;
            }
            else {
                echo <<<HTML
                <input type="hidden" name="usergroups" id="usergroups" value="{$usergroups[0]->id}"/>
HTML;
            }

        }

    }
}
