_prenotaCoupon = (function ($, my) {

        // todo piattaforma!!


        var widgets = {
            grid: null,
            dataSource: null,
            popup: {
                window: null,
                grid: null
            }
        };

        var raw_data;
        var info_piattaforma;

        function _init(data, piattaforma) {


            console.log('prenota coupon ready');

            _kendofix();


            raw_data = JSON.parse(data)[0];
            info_piattaforma = JSON.parse(piattaforma);


            $.each(raw_data, function (name, value) {
                raw_data[name] = parseInt(value) ? parseInt(value) : value;
            });

            $("#btn_calcola").click(function (e) {

                var is_associato = $('input[name=yes_no]:checked').val() === 'true';

                var qty = parseInt($("#qty").val());

                _getPrice(qty, is_associato);


            });

            _manageData(raw_data);

            createNotification('#notification', 5000, true);


        }

        function _manageData(data) {
            var final_data = [];


            console.log('manage data', data);
            // console.log(raw_data);
            // \u20AC
            var row1 = {
                range: "Da 1 a " + data['range1'],
                f: _calcRow(1, false),
                f_associato: _calcRow(1, true),
                p: data["p1"],
                p_associato: data["p1_associato"]


            };

            var row2 = {
                range: "Da " + (data["range1"] + 1) + " a " + data['range2'],
                ff: _calcRow(2, false),
                f_associato: _calcRow(2, true),
                p: data["p2"],
                p_associato: data["p2_associato"]
            };

            var row3 = {
                range: "Da " + (data["range2"] + 1) + " a " + data['range3'],
                f: _calcRow(3, false),
                f_associato: _calcRow(3, true),
                p: data["p3"],
                p_associato: data["p3_associato"]
            };

            var row4 = {
                range: "Oltre " + data["range3"],
                f: null,
                f_associato: null,
                p: info_piattaforma.email,
                p_associato: info_piattaforma.email
            };


            final_data.push(row1);
            final_data.push(row2);
            final_data.push(row3);
            final_data.push(row4);

            console.log(final_data);


            _createGrid(final_data);

        }

        function _getPrice(x, is_associato) {

            var price = 0;
            var formula = "";



            if (x <= raw_data["range1"]) {
                formula = _calcRow(1, is_associato);
            }

            else if (x <= raw_data["range2"]) {
                formula = _calcRow(2, is_associato);
            }

           else if (x <= raw_data["range3"]) {
                formula = _calcRow(3, is_associato);
            }

            var price = eval(formula);

            $("#price").text(price);
        }


        function _calcRow(row_number, is_associato) {

            var base = 0;


            if (row_number > 1) {
                for (i = 1; i < row_number; i++) {

                    var field_prezzo = "p" + i;
                    field_prezzo = field_prezzo + (is_associato ? "_associato" : "");
                    base = base + (raw_data["range" + i] - (raw_data["range" + (i - 1)] || 0)) * raw_data[field_prezzo];
                }


                var field_prezzo = "p" + row_number;
                field_prezzo = field_prezzo + (is_associato ? "_associato" : "");

                var re = base + " + (x - " + (raw_data["range" + (row_number - 1)] || 0) + " )* " + raw_data[field_prezzo];
            } else {
                var field_prezzo = "p1";
                field_prezzo = field_prezzo + (is_associato ? "_associato" : "");

                var re = raw_data[field_prezzo] + "*x"
            }


            return re;

        }


        function _createGrid(data) {


            $("#grid").kendoGrid({
                dataSource: {
                    data: data
                },
                // height: 300,
                groupable: false,
                sortable: false,
                pageable: false,
                scrollable: false,
                columns: [
                    {
                        field: "range",
                        title: "Numero persone da formare / coupon richiesti",
                        width: "30%"

                    }, {
                        field: "f1",
                        hidden: true
                    }, {
                        field: "f2",
                        hidden: true
                    },
                    {
                        field: "p",
                        title: "Aziende non associate",
                        width: "30%",
                        template: function (dataItem) {

                            if (!parseInt(dataItem.p)) {
                                return "Da valutare con la segreteria  <a href='" + info_piattaforma.email + " '>" + info_piattaforma.email + " </a>";
                            } else {

                                return "<span> " + '\u20AC' + " " +  dataItem.p +"</span>"
                            }

                        }
                    }, {
                        field: "p_associato",
                         title: "Aziende  associate a " + info_piattaforma.name,
                        width: "30%",
                        template: function (dataItem) {

                            if (!parseInt(dataItem.p_associato)) {
                                return "Da valutare con la segreteria  <a href='" + info_piattaforma.email + " '>" + info_piattaforma.email + " </a>";
                            } else {

                                return "<span> " + '\u20AC' + " " +  dataItem.p_associato +"</span>"
                            }

                        }
                    },

                ]

            });


        }


// fix per chrome perchè abbiamo una versione con un bug, mostra la  maniglia resize column
        function _kendofix() {
            kendo.ui.Grid.prototype._positionColumnResizeHandle = function () {
                var that = this,
                    indicatorWidth = that.options.columnResizeHandleWidth,
                    lockedHead = that.lockedHeader ? that.lockedHeader.find("thead:first") : $();

                that.thead.add(lockedHead).on("mousemove" + ".kendoGrid", "th", function (e) {
                    var th = $(this);
                    if (th.hasClass("k-group-cell") || th.hasClass("k-hierarchy-cell")) {
                        return;
                    }
                    that._createResizeHandle(th.closest("div"), th);
                });
            };

        }

        my.init = _init;

        return my;

    }

)
(jQuery, this);
