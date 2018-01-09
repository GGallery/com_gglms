<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_contact
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
require_once JPATH_COMPONENT . '/models/report.php';

/**
 * Controller for single contact view
 *
 * @since  1.5.19
 */
class gglmsControllerApi extends JControllerLegacy
{
    private $_japp;
    public  $_params;
    protected $_db;
    private $_filterparam;

    public function __construct($config = array())
    {
        parent::__construct($config);
        $this->_japp = JFactory::getApplication();
        $this->_params = $this->_japp->getParams();
        $this->_db = JFactory::getDbo();

        $this->_filterparam = new stdClass();

        $this->_filterparam->corso_id = JRequest::getVar('corso_id');
        $this->_filterparam->current = JRequest::getVar('current');
        $this->_filterparam->rowCount = JRequest::getVar('rowCount');
        $this->_filterparam->startdate= JRequest::getVar('startdate');
        $this->_filterparam->finishdate = JRequest::getVar('finishdate');
        $this->_filterparam->filterstato = JRequest::getVar('filterstato');
        $this->_filterparam->usergroups = JRequest::getVar('usergroups');
        $this->_filterparam->sort = JRequest::getVar('sort');
        $this->_filterparam->searchPhrase = JRequest::getVar('searchPhrase');
        $this->_filterparam->csvlimit = JRequest::getVar('csvlimit');
        $this->_filterparam->csvoffset=JRequest::getVar('csvoffset');
        $this->_filterparam->id_chiamata=JRequest::getVar('id_chiamata');
        $this->_filterparam->to=JRequest::getVar('to');
        $this->_filterparam->oggettomail=JRequest::getVar('oggettomail');
        $this->_filterparam->testomail=JRequest::getVar('testomail');


    }

    public function get_report(){

        $data = $this->get_data();
        echo  json_encode($data);
        $this->_japp->close();
    }



    private function get_data($offsetforcsv=null) {

        $this->_filterparam->task = JRequest::getVar('task');
        //FILTERSTATO: 2=TUTTI 1=COMPLETATI 0=SOLO NON COMPLETATI 3=IN SCADENZA
        $id_corso=explode('|', $this->_filterparam->corso_id)[0];
        $id_contenuto=explode('|', $this->_filterparam->corso_id)[1];
        $alert_days_before=$this->_params->get('alert_days_before');


        try {

            $query = $this->_db->getQuery(true);
            $countquery= $this->_db->getQuery(true);

            if($this->_filterparam->filterstato==1) {
                $query->select('r.id_utente , anagrafica.nome, anagrafica.cognome,anagrafica.fields, users.email');

            }else{
                $query->select('DISTINCT r.id_utente , anagrafica.nome, anagrafica.cognome,anagrafica.fields, users.email');
            }
            //SELECT COUNT PER LA COUNTQUERY
            if($this->_filterparam->filterstato==1) {
                $countquery = 'select count(*) ';
            }else{
                $countquery = 'SELECT count(DISTINCT r.id_utente , anagrafica.nome, anagrafica.cognome, anagrafica.fields,users.email) ';
            }

            //DISTINZIONE PER DEFINIZIONE VALORE DI STATO
            if($this->_filterparam->filterstato==1) {
                $query->select('1 as stato');
            }else{
                $query->select('COALESCE((select r2.stato from #__gg_report as r2 where r2.id_utente = r.id_utente and id_corso = '.$id_corso.' and id_contenuto= '.$id_contenuto.' and stato = 1 limit 1),0) 
                                as stato');
            }

            // SUBQUERY COMUNI A TUTTE E LE QUERY
            $query->select('(select r1.data from #__gg_report as r1 where r1.id_utente = r.id_utente and id_corso = '.$id_corso.' 
                ORDER BY r1.data  limit 1) as hainiziato,
                                (select r2.data from #__gg_report as r2 where r2.id_utente = r.id_utente and id_corso = '.$id_corso.' and 
                                id_contenuto= '. $id_contenuto. ' and stato = 1 ORDER BY r2.data limit 1) as hacompletato');

            //DISTINZIONE PER DEFINIZIONE VALORE DI ALERT
            if($this->_filterparam->filterstato==1){
                $query->select('0 as alert');
            }else{
                $query->select('IF(date(now())>DATE_ADD(un.data_fine, INTERVAL -'.$alert_days_before.' DAY),	IF((select r2.stato from #__gg_report as r2 where r2.id_utente = r.id_utente 
                                and id_contenuto='.$id_contenuto.' and stato = 1 limit 1),0,1),0) as alert');
            }

            // FINE DELLA SELECT INIZIO FROM - INNER JOIN

            //FROM E JOIN COMUNI A TUTTE LE QUERY
            $query->from('#__gg_report as r');
            $query->join('inner', '#__gg_report_users as anagrafica ON anagrafica.id = r.id_anagrafica');
            $query->join('inner','#__users as users on r.id_utente=users.id');

            //SE NON SONO COMPLETI ALLORA BISOGNA RECUPERARE LA DATA DI SCADENZA: JOIN
            if($this->_filterparam->filterstato != 1) {
                $query->join('inner', '#__gg_unit as un on r.id_corso=un.id');
            }

            //FINE FROM - JOIN INIZIO WHERE

            //WHERE COMUNE A TUTTE LE QUERY
            $query->where('r.id_corso = '.$id_corso);//id_corso, primo paramentro in combo
            if ($this->_filterparam->usergroups) {
                $query->join('inner', '#__user_usergroup_map as gruppo  ON gruppo.user_id = r.id_utente');
                $query->where('group_id = ' . $this->_filterparam->usergroups );
            }

            //WHERE DISTINTE IN BASE AI FILTERSTATE, PER TUTTI VA BENE COSI'
            if ($this->_filterparam->filterstato == 1)
                $query->where('r.stato = ' . $this->_filterparam->filterstato . ' and  r.id_contenuto='.$id_contenuto);

            if($this->_filterparam->filterstato == 3)
                $query->where('date(now())>DATE_ADD(un.data_fine, INTERVAL -'.$alert_days_before.' DAY)');
            //SOLO NON COMPLETATI O IN SCADENZA
            if ($this->_filterparam->filterstato == 0 || $this->_filterparam->filterstato == 3)
                $query->where('r.id_utente NOT IN (SELECT r.id_utente FROM #__gg_report as r 
                               INNER JOIN #__user_usergroup_map as gruppo  ON gruppo.user_id = r.id_utente
                               WHERE r.id_corso = '.$id_corso.' AND r.stato = 1 
                               and  r.id_contenuto='.$id_contenuto.' AND group_id = '.$this->_filterparam->usergroups.')');

            //FILTRI DA REPORT
            if ($this->_filterparam->startdate)
                $query->where(' (select r2.data from #__gg_report as r2 where r2.id_utente = r.id_utente and id_contenuto= '. $id_contenuto. ' and stato = 1 
                ORDER BY r2.data limit 1) >= "' . $this->_filterparam->startdate . '"');

            if ($this->_filterparam->finishdate)
                $query->where('(select r2.data from #__gg_report as r2 where r2.id_utente = r.id_utente and id_contenuto= '. $id_contenuto. ' and stato = 1 
                ORDER BY r2.data limit 1) <= "' . $this->_filterparam->finishdate . '"');

            if ($this->_filterparam->searchPhrase)
                $query->where('concat(nome,cognome,fields) like "%'. $this->_filterparam->searchPhrase .'%"');

            $offset=0;
            $csvoffset=$this->_filterparam->csvoffset;
            $csvlimit=$this->_filterparam->csvlimit;
            if($this->_filterparam->task != 'get_csv' ) {
                $offset = $this->_filterparam->rowCount * $this->_filterparam->current - $this->_filterparam->rowCount;
                $query->setLimit($this->_filterparam->rowCount, $offset);
            }else {
                if ($csvlimit==0){
                    $query->setLimit(1);
                }else
                {
                    $query->setLimit($csvoffset, $csvlimit - $csvoffset);
                }

            }

            $this->_db->setQuery($query);

            $this->_db->execute();
            $total=null;
            $countquery=$countquery.$query->from;
            $countquery=$countquery.(is_array($query->join)?implode($query->join):$query->join);
            $countquery=$countquery.(is_array($query->where)?implode($query->where):$query->where);

            if ($this->_filterparam->sort && $this->_filterparam->filterstato == 1) {
                foreach ($this->_filterparam->sort as $key => $value)
                    $query->order($key . " " . $value);

            }

            $this->_db->setQuery($query);
            $rows = $this->_db->loadAssocList();
            $this->_db->setQuery($countquery);
            $total=$this->_db->LoadResult();

        }catch (Exception $e){

            DEBUGG::log('ERRORE DA GETDATA','ERRORE DA GET DATA',1,1);
            //DEBUGG::error($e, 'error', 1);
        }

        $result['query']=(string)$query;
        $result['offset']=$offset;
        $result['current']=$this->_filterparam->current;
        $result['rowCount']=10;
        $result['rows']=$rows;
        $result['total']=$total;
        $result['totalquery']=$countquery;
        return $result;
    }


    public function get_csv()
    {
        //ini_set('max_execution_time', 600);
        $this->_japp = JFactory::getApplication();
        $csvlimit=$this->_filterparam->csvlimit;
        $id_chiamata=$this->_filterparam->id_chiamata;
        $data=$this->get_data($csvlimit);

        if($csvlimit>0) {
            foreach ($data['rows'] as $row) {

                try {

                    $insertquery = "INSERT INTO #__gg_csv_report VALUES (";
                    $insertquery = $insertquery . $id_chiamata . ",";
                    $insertquery = $insertquery . $row['id_utente'] . ",";
                    $insertquery = $insertquery . "'" . addslashes($row['nome']) . "',";
                    $insertquery = $insertquery . "'" . addslashes($row['cognome']) . "',";
                    $insertquery = $insertquery . "'" . addslashes($row['fields']) . "',";
                    $insertquery = $insertquery . "'" . $row['email'] . "',";
                    $insertquery = $insertquery . $row['stato'] . ",";
                    $insertquery = $insertquery . "'" . $row['hainiziato'] . "',";
                    $insertquery = $insertquery . "'" . $row['hacompletato'] . "',";
                    $insertquery = $insertquery . $row['alert'] . ")";


                    $this->_db->setQuery($insertquery);
                    $this->_db->execute();
                }catch (Exception $exception){
                    echo $exception->getMessage();
                }

            }

        }

        echo  json_encode($data);

        $this->_japp->close();
    }

    public function createCSV(){

        $id_chiamata=$this->_filterparam->id_chiamata;
        $corso_id=$this->_filterparam->corso_id;
        $query = $this->_db->getQuery(true);
        $query->select('id_chiamata, id_utente, nome, cognome,email, stato,hainiziato, hacompletato, alert');
        $query->from('#__gg_csv_report');
        $query->where('id_chiamata='.$id_chiamata);
        $this->_db->setQuery($query);
        $rows = $this->_db->loadAssocList();


try {
    if (!empty($rows)) {
        $comma = ';';
        $quote = '"';
        $CR = "\015\012";
        // Make csv rows for field name
        $i = 0;
        $fields = $rows[0];

        $cnt_fields = count($fields);
        $csv_fields = '';

        foreach ($fields as $name => $val) {
            $i++;
            if ($cnt_fields <= $i) $comma = '';
            $csv_fields .= $quote . $name . $quote . $comma;


        }

        // Make csv rows for data
        $csv_values = '';
        foreach ($rows as $row) {
            $i = 0;
            $comma = ';';
            foreach ($row as $name => $val) {
                $i++;
                if ($cnt_fields <= $i) $comma = '';
                $csv_values .= $quote . $val . $quote . $comma;
            }
            $csv_values .= $CR;
        }

        //echo ($csv_values);

        $csv_save = $csv_fields . $CR . $csv_values;
    }
    echo $csv_save;


    $filename = $this->get_CourseName($corso_id);
    $filename = preg_replace('~[^\\pL\d]+~u', '_', $filename);
    $filename = iconv('utf-8', 'us-ascii//TRANSLIT', $filename);
    $filename = strtolower($filename);
    $filename = trim($filename, '_');
    $filename = preg_replace('~[^-\w]+~', '', $filename);
    $filename .= "-" . date("d/m/Y");
    $filename = $filename . ".csv";


    header("Content-Type: text/plain");
    header("Content-disposition: attachment; filename=$filename");
    header("Content-Transfer-Encoding: binary");
    header("Pragma: no-cache");
    header("Expires: 0");
}catch (exceptions $exception){
    echo $exception->getMessage();
}
        $this->_japp->close();
    }

    private  function get_CourseName($corso_id){
        $query = $this->_db->getQuery(true);
        $query->select('titolo');
        $query->from('#__gg_unit as u');
        $query->where('u.id= '.$corso_id);
        $this->_db->setQuery($query);
        $titolo = $this->_db->loadResult();

        return $titolo;
    }

    public function buildDettaglioCorso(){

        $id_utente=(int)$_GET['id_utente'];
        $id_corso=(int)$_GET['id_corso'];
        $query = $this->_db->getQuery(true);
        $query->select('u.titolo as \'titolo unità\',c.titolo as \'titolo contenuto\', 
                      IF (r.stato=1, \'completato\',\'non completato\') as \'stato\', r.`data` as \'data\'');
        $query->from('#__gg_report as r');
        $query->join('inner','#__gg_unit as u on r.id_unita=u.id');
        $query->join('inner','#__gg_contenuti c on r.id_contenuto=c.id');
        $query->join('inner','#__gg_unit_map um on c.id=um.idcontenuto');
        $query->where('id_utente='.$id_utente);
        $query->where('id_corso='.$id_corso);
        $query=$query." order by u.ordinamento, u.id, um.ordinamento ,r.`data`";
        $this->_db->setQuery($query);
        $result = $this->_db->loadAssocList();
        echo  json_encode($result);
        $this->_japp->close();
    }

    public function sendMail(){
        try {
        $to=(string)$this->_filterparam->to;

        //$to="a.petruzzella71@gmail.com";
        $oggettomail=$this->_filterparam->oggettomail;
        $testomail=$this->_filterparam->testomail;
        $recipients=array();
        array_push($recipients,$to);
        $mailer = JFactory::getMailer();
        $config = JFactory::getConfig();
        $sender = array(
            $config->get( 'mailfrom' ),
            $config->get( 'fromname' )
        );

        $mailer->setSender($sender);

        $mailer->addRecipient($recipients);
        $mailer->setSubject($oggettomail);
        $mailer->setBody($testomail);

        $send = $mailer->Send();

        echo json_encode($send);
       }catch (exceptions $exception){
           echo $exception->getMessage();

       }
        $this->_japp->close();
    }

    public function sendAllMail(){
    try{
        $id_corso=explode('|', $this->_filterparam->corso_id)[0];
        $id_contenuto=explode('|', $this->_filterparam->corso_id)[1];
        $group_id=$this->_filterparam->usergroups;
        $alert_days_before=$this->_params->get('alert_days_before');
        $query = $this->_db->getQuery(true);
        $query->select('DISTINCT users.email');
        $query->from('#__gg_report as r');
        $query->join('inner','#__gg_report_users as anagrafica ON anagrafica.id = r.id_anagrafica');
        $query->join('inner','#__users as users on r.id_utente=users.id');
        $query->join('inner','#__gg_unit as un on r.id_corso=un.id');
        $query->join('inner','#__user_usergroup_map as gruppo  ON gruppo.user_id = r.id_utente');
        $query->where('id_corso='.$id_corso);
        $query->where('group_id='.$group_id);
        $query->where('IF(date(now())>DATE_ADD(un.data_fine, INTERVAL -'.$alert_days_before.' DAY),	IF((select r2.stato from #__gg_report as r2 where r2.id_utente = r.id_utente 
                                and id_contenuto='.$id_contenuto.' and stato = 1 limit 1),0,1),0)=1');
        $query->where('r.id_utente NOT IN (SELECT r.id_utente FROM #__gg_report as r INNER JOIN  #__user_usergroup_map as gruppo  ON gruppo.user_id = r.id_utente
                               WHERE r.id_corso = '.$id_corso.' AND r.stato = 1 and  r.id_contenuto='.$id_contenuto.' AND group_id = '.$group_id.')');



        $this->_db->setQuery($query);
        $rows = $this->_db->loadAssocList();

        $oggettomail=$this->_filterparam->oggettomail;
        $testomail=$this->_filterparam->testomail;
        $recipients=array_column($rows,'email');


        $mailer = JFactory::getMailer();
        $config = JFactory::getConfig();
        $sender = array(
            $config->get( 'mailfrom' ),
            $config->get( 'fromname' )
        );

        $mailer->setSender($sender);

        $mailer->addRecipient($recipients);
        $mailer->setSubject($oggettomail);
        $mailer->setBody($testomail);

        //$send = $mailer->Send();  ATTENZIONE IL VERO INVIO E' DISABILITATO IN PROVA

        //echo $send;
    }catch (exceptions $exception){
echo $exception->getMessage();

}
$this->_japp->close();

    }

//	INUTILIZZATO
//	public function getSummarizeCourse(){
//		$query = $this->_db->getQuery(true);
//		$query->select('stato, count(stato) as total ');
//		$query->from('#__gg_report AS r');
//		$query->where("id_contenuto =" . $this->_filterparam->corso_id);
//		$query->group('stato');
//		$this->_db->setQuery($query);
//		$summarize = $this->_db->loadAssocList('stato');
//		return $summarize;
//	}

}
