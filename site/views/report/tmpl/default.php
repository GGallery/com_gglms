<?php
/**
 * @package		Joomla.Tutorials
 * @subpackage	Component
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		License GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access to this file
defined('_JEXEC') or die;

JHtml::_('bootstrap.modal');



?>

<style>
.rotated{
    transform: rotate(90deg);
    width: 20px;
    top:0;
    font-size: 8px;


}


</style>
<div id="barrafiltri" class="span2">

    <form id="theform" class="form-inline" action="index.php">


        <h2>Corso</h2>
        <div class="form-group">

            <?php //echo outputHelper::output_select('corso_id', $this->corsi, 'id_contenuto_completamento', 'titolo', null, 'refresh'); ?>
            <select id="corso_id" name="corso_id" class="refresh">
                <?php
                foreach ($this->corsi as $corso){

                    echo '<option value="'.$corso->id.'|'.$corso->id_contenuto_completamento.'">'.$corso->titolo."</option>";
                }
                ?>
            </select>

        </div>

        <h2>Tipo Report</h2>
        <div class="form-group">

            <select id="tipo_report" name="tipo_report" class="refresh">
                <option value="0">per Corso</option>
                <option value="1">per Unità</option>
                <option value="2">per Contenuto</option>
            </select>

        </div>

        <h2>Filtri</h2>

        <div class="form-group">
            <label for="usergroups">Gruppo utenti</label>
            <?php echo outputHelper::output_select('usergroups', $this->usergroups, 'id', 'title', 2 , 'refresh'); ?>
        </div>
        <div class="form-group" id="searchPhrase_div">
            <label for="searchPhrase">Cerca Cognome:</label><br>
            <input type="text" id="searchPhrase">
        </div>

        <div class="form-group" id="filterstatodiv">
            <label for="filterstato">Stato corso</label>
            <select id="filterstato" name="filterstato" class="refresh">
                <option value="0">Qualisiasi stato</option>
                <option value="1">Solo completati</option>
                <option value="2">Solo NON compleati</option>
                <option value="3">In scadenza</option>
            </select>
        </div>


        <div class="form-group" id="calendar_startdate_div">
            <label for="startdate">Completato dal:</label><br>
            <?php echo JHTML::calendar('','startdate','startdate','%Y-%m-%d'); ?>
        </div>

        <div class="form-group" id="calendar_finishdate_div">
            <label for="finishdate">Completato al:</label><br>
            <?php echo JHTML::_( 'calendar','','finishdate','finishdate','%Y-%m-%d'); ?>


        </div>

        <input type="hidden" id="option" name="option" value="com_gglms">
        <input type="hidden" id="task" name="task" value="api.get_csv">

        <div class="form-group">
            <button type="button" id="update" class="btn btn-success btn-lg width100" onclick="reload()">AGGIORNA DATI</button>
        </div>
        <div class="form-group">
            <button type="button" id="get_csv" class="btn btn-warning btn-lg width100" onclick="sendAllMail()">INVIA MAIL IN SCADENZA</button>
        </div>
        <div class="form-group">
            <button type="button" id="get_csv" class="btn btn-success btn-lg width100" onclick="loadCsv()">SCARICA REPORT CSV</button>
        </div>
        <div>
            <button type="button" class="btn btn-info btn-lg width100" onclick="dataSyncUsers()">SINCRONIZZA TABELLA REPORT</button>
        </div>

    </form>

    <hr>


    <canvas id="myChart" width="100" height="100"></canvas>



</div>
<div id="contenitoreprincipale" class="span8">

    <div class="row">
        <div class="span12">

            <table id="grid-basic" class="table table-condensed table-hover table-striped ">

            </table>

            <div class="col-sm-6">
                <ul class="pagination">
                    <li class="first" aria-disabled="true">
                        <a data-page="first" class="button">«</a></li>
                    <li class="prev" aria-disabled="true">
                        <a data-page="prev" class="button">&lt;</a></li>
                    <li class="page-1" aria-disabled="false" aria-selected="false">
                        <a data-page="1" class="button">1</a></li>
                    <li class="page-2" aria-disabled="false" aria-selected="false">
                        <a data-page="2" class="button">2</a></li>
                    <li class="page-3" aria-disabled="false" aria-selected="false">
                        <a data-page="3" class="button">3</a></li>
                    <li class="page-4" aria-disabled="false" aria-selected="false">
                        <a data-page="4" class="button">4</a></li>
                    <li class="page-5" aria-disabled="false" aria-selected="false">
                        <a data-page="5" class="button">5</a></li>
                    <li class="next" aria-disabled="false">
                        <a data-page="next" class="button">&gt;</a></li>
                    <li class="last" aria-disabled="false">
                        <a data-page="last" class="button">»</a></li>
                </ul>
            </div>

        </div>
    </div>
</div>
</div>

<!-- Modal -->
<div id="details" class="modal fade" role="dialog">
    <div class="modal-dialog">

        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Dettagli utente</h4>
            </div>
            <div class="modal-body">
                <table id="details_table" class="table table-condensed table-hover table-striped ">
                    <thead> <tr> <th>Campo</th> <th>Valore</th> </tr> </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="tn btn-success btn-lg" onclick="loadLibretto()" style="font-size:12px;padding:4px;position:ABSOLUTE;left:4%;">Libretto Formativo</button>

                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>

    </div>
</div>

<!-- Modal Dettagli Corso-->
<div id="detailsCorso" class="modal fade " role="dialog">
    <div class="modal-dialog">

        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Dettagli del corso</h4>
            </div>
            <div class="modal-body">
                <table id="details_table_corso" class="table table-condensed table-hover table-striped ">
                    <thead> <tr> <th>Titolo unità</th> <th>Titolo contenuto</th>  <th>stato</th><th>data</th></tr></thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>

    </div>
</div>

<!-- Modal Dettagli Caricamento CSV-->
<div id="detailsCaricamentoCSV" class="modal fade " role="dialog">
    <div class="modal-dialog">

        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Dettagli del caricamento CSV</h4>
            </div>
            <div class="modal-body">
                <table id="details_table_caricamento_csv" class="table table-condensed table-hover table-striped ">
                    <tbody>
                    </tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>

    </div>
</div>

<!-- Modal Dettagli Caricamento Tabella Report-->
<div id="detailsCaricamentoReport" class="modal fade " role="dialog">
    <div class="modal-dialog">

        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Dettagli del caricamento Tabella Report</h4>
            </div>
            <div class="modal-body">
                <table id="details_table_caricamento_report" class="table table-condensed table-hover table-striped ">
                    <tbody>
                    </tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>

    </div>
</div>

<!-- Modal Dettagli Aggiornamento Report-->
<div id="aggiornamentoReport" class="modal fade " role="dialog">
    <div class="modal-dialog">

        <!-- Modal content-->
        <div class="modal-content">

            <div class="modal-body">
               caricamento dati...
            </div>

        </div>

    </div>
</div>

<!-- Modal Dettagli invio mail -->
<div id="detailsInvioMail" class="modal fade " role="dialog">
    <div class="modal-dialog">

        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Dettagli di invio della mail di avviso</h4>
            </div>
            <div class="modal-body">
                <table id="details_table_invio_mail" class="table table-condensed table-hover table-striped ">
                    <tbody>
                    </tbody>
                </table>
            </div>
            <div id="div_send_mail_textarea" class="modal-body">Confermi di inviare questa email?<br>
                oggetto:<input id="oggettomail" type="text" value="promemoria scadenza corso">
                <textarea   cols="50" rows="5" id="testomail" style="width: 560px;"></textarea>
            </div>
            <div class="modal-footer">
                <button id="sendmailbutton" type="button" class="btn btn-success btn-lg"  onclick="sendMail()">Invia</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>

    </div>
</div>


<?php
echo "Report aggiornato al :" .$this->state->get('params')->get('data_sync');
?>

<script type="text/javascript">


//MODIFICARE QUI QUANDO CI SARA' IL PARAMETRO
var testo_base_mail='<?php echo $this->state->get('params')->get('alert_mail_text'); ?>';
var loadreportlimit=0;
var loadreportoffset=10;
var actualminpage=1;
var maxNofpages;

    jQuery( document ).ready(function($) {

        jQuery('#filterstatodiv').show();
        jQuery('#calendar_startdate_div').hide();
        jQuery('#calendar_finishdate_div').hide();
        loadData();

//        TORTA
        var ctx = document.getElementById("myChart").getContext('2d');
        var notcompleted = 0;
        var completed = 0;
        var fields = new Array();

        var myChart = new Chart(ctx, {
            type: 'pie',
            data: {
                labels: ["Utenti che hanno completato", "Utenti che non hanno completato"],
                datasets: [{
                    label: '% corsi completati',
                    data: [completed , notcompleted ],
                    backgroundColor: [
                        'rgba(0, 123, 132, 0.2)',
                        'rgba(128, 162, 25, 0.2)'
                    ],
                    borderColor: [
                        'rgba(0,123,132,1)',
                        'rgba(128, 162, 25, 1)'

                    ],
                    borderWidth: 5
                }]
            },

        });




        //  TABELLA
        $(".refresh").change(function(){

            notcompleted = 0;
            completed = 0;
            loadData();
            //$("#grid-basic").bootgrid("reload");
        });

        $("#tipo_report").change(function(){

            if ($("#tipo_report option:selected").val() == 0) {
                $("#filterstatodiv").show();
                $("#calendar_startdate_div").show();
                $("#calendar_finishdate_div").show();
            } else {
                $("#filterstatodiv").hide();
                $("#calendar_startdate_div").hide();
                $("#calendar_finishdate_div").hide();
            }

        });

        $("#filterstato").change(function(){


            if($("#filterstato option:selected").val()==1){
                $("#calendar_startdate_div").show();
                $("#calendar_finishdate_div").show();
            }else{
                $("#calendar_startdate_div").hide();
                $("#calendar_finishdate_div").hide();
            };

        });

        $("#startdate").bind('change',function(){

            notcompleted = 0;
            completed = 0;
            loadData();
            //$("#grid-basic").bootgrid("reload");
        });

        $("#finishdate").change(function(){

            notcompleted = 0;
            completed = 0;
            loadData();
            //$("#grid-basic").bootgrid("reload");
        });

       /*var grid = $("#grid-basic").bootgrid({
            ajax: true,
            multiSort: true,
            requestHandler: function (request) {
                //Add your id property or anything else
                request.corso_id = $("#corso_id").val();
                request.startdate = $("#startdate").val();
                request.finishdate = $("#finishdate").val();
                request.filterstato = $("#filterstato").val();
                request.usergroups = $("#usergroups").val();
                return request;
            },
            url: "index.php?option=com_gglms&task=api.get_report",

            formatters: {
                "stato": function(column, row)
                {
                    if(row.stato == 1) {
                        completed++;
                        return "Completato";
                    }
                    else {
                        notcompleted++;
                        return "Non Completato";
                    }
                },
                "alert": function (column, row)
                {
                    if(row.alert == 1) {
                        fields[row.id_utente]=row.fields;
                        return '<span class="glyphicon glyphicon-alert" style="color:gold; font-size: 23px;" aria-hidden="true"></span>' +

                            '<button type="button" style="color:gold; font-size: 23px;    margin-left: 10px; margin-top: -10px;" title="email" class="btn btn-xs btn-default command-edit-sendMail" data-row-id=\"' + row.id_utente + '\"><span class="glyphicon glyphicon-envelope" aria-hidden="true" style="color:red; font-size:16px;"></span></button>';
                    }
                    else {

                        return null;//'<span class="glyphicon glyphicon-th-list" aria-hidden="true"></span>';
                    }
                },
                "commands": function(column, row)
                {
                    fields[row.id_utente]=row.fields;
                    return '<button type="button" title="anagrafica" class="btn btn-xs btn-default command-edit" data-row-id=\"' + row.id_utente + '\"><span class="glyphicon glyphicon-user" aria-hidden="true"></span></button>'+
                        '<button type="button" title="dettagli corso" class="btn btn-xs btn-default command-edit-dettagli" data-row-id=\"' + row.id_utente + '\"><span class="glyphicon glyphicon-th-list" aria-hidden="true"></span></button>'+
                        '<a href='+window.location+'../../libretto.html?user_id='+row.id_utente+' title="libretto formativo" class="btn btn-xs btn-default" \><span class="glyphicon glyphicon-book" aria-hidden="true"></span></a>';
                },
                "dettaglicorso": function(column, row)
                {
                    //fields[row.id_utente]=row.fields;
                    //return '<button type="button" class="btn btn-xs btn-default command-edit-dettagli" data-row-id=\"' + row.id_utente + '\"><span class="glyphicon glyphicon-th-list" aria-hidden="true"></span></button>';
                },
                "data_inizio": function(column, row)
                {
                    if (row.hainiziato == '0000-00-00') {
                        return "";
                    }else{
                        return row.hainiziato;
                    }
                }
            }

        }).on("loaded.rs.jquery.bootgrid", function(e)
        {
            myChart.data.datasets[0].data[0] = completed;
            myChart.data.datasets[0].data[1] = notcompleted;
            myChart.update();

            grid.find(".command-edit").on("click", function(e)
            {
                scelta = $(this).data("row-id");
                data= JSON.parse(fields[scelta]);
                $('#details_table tbody').empty();
                $.each(data, function (key, value) {
                    var eachrow = "<tr>" + "<td>" +  key + "</td>" + "<td>" +  value + "</td>" + "</tr>";
                    $('#details_table tbody').append(eachrow);

                });
                $("#details").append('<input id=modal_id_utente type=hidden value='+scelta+'>');
                $("#details").modal('show');

            }).end();

            grid.find(".command-edit-sendMail").on("click", function(e)
            {
                $('#testomail').empty();
                jQuery('#sendmailbutton').show();
                jQuery('#div_send_mail_textarea').show();
                scelta = $(this).data("row-id");
                data= JSON.parse(fields[scelta]);
                $('#details_table_invio_mail tbody').empty();

                    var eachrow = "<tr>" + "<td>Nome</td>" + "<td>" +  data['nome'] + "</td>" + "</tr>";
                    eachrow += "<tr>" + "<td>Cognome</td>" + "<td>" +  data['cognome'] + "</td>" + "</tr>";
                    eachrow += "<tr>" + "<td>Email</td>" + "<td id='to'>" +  data['email'] + "</td>" + "</tr>";

                    $('#details_table_invio_mail tbody').append(eachrow);
                    nome_corso=$('#corso_id option:selected').text();
                    $('#testomail').append(testo_base_mail+" "+nome_corso);


                $("#detailsInvioMail").modal('show');

            }).end();

            grid.find(".command-edit-dettagli").on("click", function(e)
            {
                scelta = $(this).data("row-id");
                var id_utente=scelta;
                var id_corso=$('#corso_id')[0]['value'].split('|')[0];

                jQuery.when(jQuery.get("index.php?option=com_gglms&task=api.buildDettaglioCorso&id_corso="+id_corso+"&id_utente="+id_utente))
                    .done(function(data){
                        data=JSON.parse(data);
                        $('#details_table_corso tbody').empty();
                        var eachrow;
                        $.each(data, function (key,value) {

                            eachrow=eachrow+"<tr><td>"+value['titolo unità']+"</td>"+
                                "<td>"+value['titolo contenuto']+"</td>"+
                                "<td>"+value['stato']+"</td>"+
                                "<td>"+value['data']+"</td></tr>";
                        });

                        $('#details_table_corso tbody').append(eachrow);
                        $("#detailsCorso").modal('show');

                    }).fail(function(data){

                });



            }).end();
        });*/
    });

    jQuery('.button').click(function () {

        switch (jQuery(this).attr('data-page')) {

            case 'first':
                jQuery("a[data-page='1']").html('1');
                jQuery("a[data-page='2']").html('2');
                jQuery("a[data-page='3']").html('3');
                jQuery("a[data-page='4']").html('4');
                jQuery("a[data-page='5']").html('5');
                actualminpage=1;
                break;

            case 'prev':
                if(actualminpage>1) {
                    jQuery("a[data-page='1']").html(parseInt(jQuery("a[data-page='1']").html()) - 1);
                    jQuery("a[data-page='2']").html(parseInt(jQuery("a[data-page='2']").html()) - 1);
                    jQuery("a[data-page='3']").html(parseInt(jQuery("a[data-page='3']").html()) - 1);
                    jQuery("a[data-page='4']").html(parseInt(jQuery("a[data-page='4']").html()) - 1);
                    jQuery("a[data-page='5']").html(parseInt(jQuery("a[data-page='5']").html()) - 1);
                    actualminpage--;
                }
                break;

            case 'next':

                //console.log(jQuery("a[data-page='1']").html());

                jQuery("a[data-page='1']").html(parseInt(jQuery("a[data-page='1']").html()) + 1);
                jQuery("a[data-page='2']").html(parseInt(jQuery("a[data-page='2']").html()) + 1);
                jQuery("a[data-page='3']").html(parseInt(jQuery("a[data-page='3']").html()) + 1);
                jQuery("a[data-page='4']").html(parseInt(jQuery("a[data-page='4']").html()) + 1);
                jQuery("a[data-page='5']").html(parseInt(jQuery("a[data-page='5']").html()) + 1);
                actualminpage++;
                break;

            case 'last':
console.log(maxNofpages);
                jQuery("a[data-page='1']").html(maxNofpages-4);
                jQuery("a[data-page='2']").html(maxNofpages-3);
                jQuery("a[data-page='3']").html(maxNofpages-2);
                jQuery("a[data-page='4']").html(maxNofpages-1);
                jQuery("a[data-page='5']").html(maxNofpages);
                actualminpage=maxNofpages-4
                break;

            default:
            loadreportlimit= (parseInt(jQuery(this).html()) * loadreportoffset) - loadreportoffset;
            loadData();
        }
    });

    function loadData(){


        var url="index.php?option=com_gglms&task=api.get_report&corso_id="+jQuery("#corso_id").val();
        url=url+"&startdate="+jQuery("#startdate").val();
        url=url+"&finishdate="+jQuery("#finishdate").val();
        url=url+"&filterstato="+jQuery("#filterstato").val();
        url=url+"&usergroups="+jQuery("#usergroups").val();
        url=url+"&tipo_report="+jQuery("#tipo_report").val();
        url=url+"&searchPhrase="+jQuery("#searchPhrase").val();
        url=url+"&limit="+loadreportlimit;
        url=url+"&offset="+loadreportoffset;
        jQuery("#aggiornamentoReport").modal('show');
        jQuery.when(jQuery.get(url))
            .done(function (data) {

            })
            .fail(function (data) {

            })
            .then(function (data) {

               data=JSON.parse(data);
               jQuery('#grid-basic').empty();
                maxNofpages=parseInt((data['rowCount']/loadreportoffset)+1);
                jQuery("#aggiornamentoReport").modal('hide');
               data['columns'].forEach(addColumn);
               for(i=0; i<data['rows'].length; i++){

                    jQuery('#grid-basic').append('<tr>');
                    var row=data['rows'][i];
                    for(ii=0; ii<data['columns'].length;ii++) {

                       // jQuery('#grid-basic tr:last').append('<td>'+row[data['columns'][ii]]+'</td>');
                       addRowElement(jQuery('#grid-basic tr:last'),row[data['columns'][ii]],ii, jQuery("#tipo_report").val(),data['columns'])
                    }
                    jQuery('#grid-basic').append('</tr>');
                }

            });

        /*jQuery.ajax(
            {
                xhr: function()
                {
                    var xhr = new window.XMLHttpRequest();
                    //Upload progress
                    xhr.upload.addEventListener("progress", function(evt){
                        if (evt.lengthComputable) {
                            var percentComplete = evt.loaded / evt.total;
                            //Do something with upload progress
                            console.log(percentComplete);
                        }
                    }, false);
                    //Download progress
                    xhr.addEventListener("progress", function(evt){
                        if (evt.lengthComputable) {
                            var percentComplete = evt.loaded / evt.total;
                            //Do something with download progress
                            console.log(percentComplete);
                        }
                    }, false);
                    return xhr;
                },
                type: 'GET',
                url: url,
                data: data,
                success: function(data){
                    data=JSON.parse(data);
                    jQuery('#grid-basic').empty();
                    maxNofpages=parseInt((data['rowCount']/loadreportoffset)+1);

                    data['columns'].forEach(addColumn);
                    for(i=0; i<=10; i++){

                        jQuery('#grid-basic').append('<tr>');
                        var row=data['rows'][i];
                        for(ii=0; ii<data['columns'].length;ii++) {

                            jQuery('#grid-basic tr:last').append('<td>'+row[data['columns'][ii]]+'</td>');
                        }
                        jQuery('#grid-basic').append('</tr>');
                    }
                }
            });*/


    }

    function addRowElement(table,rowCellData,columIndex,viewType,dataColumns) {

        //SET OF RULES

        if(dataColumns[columIndex]==="stato" && rowCellData==1){

            rowCellData="<span class='glyphicon glyphicon-check' style='color:green; font-size: 12px;'></span>"
        }

        if(dataColumns[columIndex]==="stato" && rowCellData==0){

            rowCellData="<span class='glyphicon glyphicon-pause' style='color:blue; font-size: 12px;'></span>"
        }

        if(dataColumns[columIndex]==="scadenza" ){//&& rowCellData==1){

            rowCellData="<span class='glyphicon glyphicon-alert' style='color:yellow; font-size: 12px;'></span>"
        }

        if(dataColumns[columIndex]==="scadenza" && rowCellData==0){

            rowCellData=""
        }
        stile='';
        switch (viewType){

            case '0':
                break;
            case '1':
            case '2':

                if(rowCellData==1){

                    rowCellData="<span class='glyphicon glyphicon-check' style='color:green; font-size: 12px;'></span>"
                }
                stile='border-left: 1px solid #ddd';
                //rowCellData="<span class='glyphicon glyphicon-check' style='color:green; font-size: 12px;'></span>"
                break;
        }

        table.append("<td style='border-left: 1px solid #ddd'>"+rowCellData+"</td>");
    }
    
    function addColumn(item, index) {

        switch ( jQuery("#tipo_report").val()){

            case '2':

                //classtouse="class=rotated";
                break;
            default:
                classtouse="";
                break;
        }

        jQuery('#grid-basic').append('<th '+classtouse+'>'+item.toString()+'</th>');
    }

    function dataSyncUsers() {//E' LA FUNZIONE CHE INIZIA LA PROCEDURA DI CARICAMENTO TABELLA REPORT

        console.log('dataSyncUsers');
        jQuery("#detailsCaricamentoReport").modal('show');
        jQuery('#details_table_caricamento_report').empty();
        jQuery('#details_table_caricamento_report').append('<tr><td>inizio caricamento</td></tr><tr><td>stiamo caricando i tuoi dati ti invitiamo ad attendere...</td></tr>');
        jQuery.when(jQuery.get("index.php?option=com_gglms&task=report.sync_report_users"))
            .done(function (data) {

            })
            .fail(function (data) {

            })
            .then(function (data) {
                dataSyncReportCount(loadreportlimit,loadreportoffset);

            });

    }

    function dataSyncReportCount(loadreportlimit,loadreportoffset) {

        console.log('dataSyncReportCount');
        jQuery.when(jQuery.get("index.php?option=com_gglms&task=report.sync_report_count"))
            .done(function (data) {
                data=JSON.parse(data);
                jQuery('#details_table_caricamento_report').append('<tr><td>Caricamento totale di  '+data+' records</td></tr>');

            })
            .fail(function (data) {

            })
            .then(function (data) {
                dataSyncReport(loadreportlimit,loadreportoffset);
            });

    }
    function dataSyncReport(loadreportlimit,loadreportoffset) {
        console.log('dataSyncReport');

        jQuery.when(jQuery.get("index.php?limit="+loadreportlimit+"&offset="+loadreportoffset+"&option=com_gglms&task=report.sync_report"))
            .done(function(data){
                data=JSON.parse(data);
                loadreportlimit+=loadreportoffset;

                console.log('data:'+data+' loadreportlimit a:'+loadreportlimit);
                if(data=='true') {
                    jQuery('#details_table_caricamento_report').append('<tr><td>caricamento fino a record n° '+loadreportlimit+'</td></tr>');
                    dataSyncReport(loadreportlimit, loadreportoffset);
                }else{
                    dataSyncReportComplete();
                }
            }).fail(function(data){
        })
            .then(function (data) {

            });
    }

    function dataSyncReportComplete() {
        console.log('dataSyncReportComplete');
        jQuery('#details_table_caricamento_report').append('<tr><td>caricamento record utenti che non hanno iniziato</td></tr>');
        jQuery.when(jQuery.get("index.php?option=com_gglms&task=report.sync_report_complete"))
            .done(function(data){

                dataUpdateConfig();

            }).fail(function(data){
        })
            .then(function (data) {

            });
    }

    function dataUpdateConfig() {
            console.log('dataUpdateConfig');
            jQuery.when(jQuery.get("index.php?option=com_gglms&task=report.updateconfig"))
                .done(function (data) {
                    jQuery('#details_table_caricamento_report').append('<tr><td>caricamento completato</td></tr>');

                })
                .fail(function (data) {

                });
        }

    function reload() {

        loadData();
        //jQuery("#grid-basic").bootgrid("reload");
    }

    function loadCsv() {
        var total;
        var id_chiamata=Math.floor(Math.random()*100000);
        var id_corso= jQuery('#corso_id')[0]['value'];
        var usergroups= jQuery('#usergroups')[0]['value'];
        var filterstato= jQuery('#filterstato')[0]['value'];
        var startdate= jQuery("#startdate")[0]['value'];
        var finishdate= jQuery("#finishdate")[0]['value'];
        jQuery('#details_table_caricamento_csv').empty();
        jQuery('#details_table_caricamento_csv').append('<tr><td>inizio caricamento</td></tr><tr><td>stiamo caricando i tuoi dati ti invitiamo ad attendere...</td></tr>');
        jQuery("#detailsCaricamentoCSV").modal('show');
        jQuery.when(jQuery.get("index.php?corso_id="+id_corso+"&usergroups="+usergroups+"&filterstato="+filterstato+
                                "&startdate="+startdate+"&finishdate="+finishdate+"&csvlimit=0$csvoffset=0&id_chiamata="+id_chiamata+"&option=com_gglms&task=api.get_csv"))
            .done(function(data){

            }).then(function (data) {

            data=JSON.parse(data);
            total=data['total'];
            jQuery('#details_table_caricamento_csv').append('<tr><td>caricamento di '+total+' records, attendere il completamento della procedura...</td></tr>');
            var csvoffset=100;
            var csvlimit=100;
            $datafromquery=LoadCSVDataFromJquery(id_corso,usergroups,filterstato,startdate,finishdate,csvoffset,csvlimit,total,id_chiamata);
        }).fail(function(data) {

        });

    }

    function LoadCSVDataFromJquery(id_corso,usergroups,filterstato,startdate,finishdate,csvoffset,csvlimit,total,id_chiamata) {

        var jqxhr=jQuery.get("index.php?corso_id=" + id_corso + "&usergroups=" + usergroups +
            "&filterstato=" + filterstato +"&startdate=" + startdate + "&finishdate=" + finishdate + "&csvlimit="
            + csvlimit +"&csvoffset="+csvoffset+"&id_chiamata="+id_chiamata+"&option=com_gglms&task=api.get_csv", function (data) {
            data=JSON.parse(data);
        })
            .done(function (data) {
                jQuery('#details_table_caricamento_csv').append('<tr><td>caricamento fino a record n° '+csvlimit+'</td></tr>');
                if(csvlimit<parseInt(total)){
                    csvlimit=csvlimit+csvoffset;
                    LoadCSVDataFromJquery(id_corso,usergroups,filterstato,startdate,finishdate,csvoffset,csvlimit,total,id_chiamata)
                }else {
                    jQuery('#details_table_caricamento_csv').append('<tr><td>caricamento completato</td></tr>');
                    location.href='index.php?option=com_gglms&id_chiamata='+id_chiamata+'&corso_id="'+id_corso.substr(0,id_corso.indexOf('|'))+'"&task=api.createCSV';
                }
            }).fail(function (data) {
                jQuery('#details_table_caricamento_csv').append('<tr><td>ERROR\! nel caricamento fino a record n° '+csvlimit+'</td></tr>');
            });
        jqxhr=null;
    }

    function sendMail() {

        oggettomail=jQuery('#oggettomail').val();
        testomail=jQuery('#testomail').val();
       //to=jQuery('#to').html(); ATTENZIONE QUESTA RIGA IN PRODUZIONE ANDRA' SCOMMENTATA
        to="a.petruzzella71@gmail.com";
       jQuery.when(jQuery.get("index.php?to="+to+"&oggettomail="+oggettomail+"&testomail="+testomail+"&option=com_gglms&task=api.sendMail"))

            .done(function(data){

                result=JSON.parse(data);

                if(result==true){
                    jQuery('#sendmailbutton').hide();
                    jQuery('#div_send_mail_textarea').hide();
                    jQuery('#details_table_invio_mail tbody').append('<tr><td>email inviata con successo, puoi chiudere questa finestra</td><tr>');
                }
            }).fail(function(data){

        });

    }

    function sendAllMail() {


        nome_corso=jQuery('#corso_id option:selected').text();
        oggettomail=jQuery('#oggettomail').val();
        testomail=testo_base_mail+nome_corso;
        var id_corso= jQuery('#corso_id')[0]['value'];
        var usergroups= jQuery('#usergroups')[0]['value'];
        jQuery.when(jQuery.get("index.php?corso_id="+id_corso+"&usergroups="+usergroups+"&oggettomail="+oggettomail+"&testomail="+testomail+"&option=com_gglms&task=api.sendAllMail"))

            .done(function(data){

            }).fail(function(data){

        });

    }

    function loadLibretto() {

        var user_id=jQuery('#modal_id_utente').val();


        location.href=window.location+'../../libretto.html?user_id='+user_id


    }


</script>