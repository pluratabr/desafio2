getColors = function (incidence) {

    var color = '#3c763d';
    var bg = '#92a292';

    if (incidence < 30) {
        color = '#de3835';
        bg = '#f7c4c3';
    }

    if (incidence > 30 && incidence < 70) {
        color = '#e79a00';
        bg = '#ffd98d';
    }

    return { 'color': color, 'bg': bg };
}

/** UPDATE CABEÇALHO */
var updateHeaderProcess = function (color) {

    var headerProcessContainer = $('div#maisDetalhes').find('dl');
    if (headerProcessContainer.length) {
        console.log('updating header');

        var regExp = /\(([^)]*)\)[^(]*$/;
        var selectedClass = $(headerProcessContainer).find('dd').eq(0);
        var selectedSubjects = $(headerProcessContainer).find('dd').eq(1).find('ul li');
        var addToList = false;

        if (!subjectlist.length)
            addToList = true;

        selectedSubjects.each(function (i, e) {
            var subject = $(e);
            var code = null;
            var desc = $.trim(subject.html());

            // change color
            subject.css('color', color);

            var matches = regExp.exec(desc);
            code = matches[1];

            console.log('matches');
            console.log(matches);

            // if(code) {
            //     code = matches[2];
            // }

            chrome.storage.sync.set({ 'subjectValue': code });

            if (addToList)
                subjectlist.push({ id: code, description: desc, checked: false });

        });

        //save process class data
        var classDesc = $.trim($(selectedClass).html());
        var matches = regExp.exec(classDesc);
        var classCode = matches[1];

        chrome.storage.sync.set({ 'classValue': { 'value': classCode, 'desc': classDesc } });

        processClass.push({ id: classCode, description: classDesc })

        //change color
        selectedClass.css('color', color);
    }
}

/** PAGE TAB -  ASSUNTOS  */
var bindFieldAssuntos = function () {

    console.log('assuntos iniciais - associados : ' + $("a[title='Remover']").legth);
    console.log('assuntos iniciais - disponíveis : ' + $("a[id*='r_processoAssuntoListList']").legth);

    $("a[id*='l_processoAssuntoListList'], a[id*='r_processoAssuntoListList']").each(function (i, s) {
        console.log('adding to bind  ' + $(this).attr('id'));
        $(this).bind('click', function () {
            var $this = $(this);
            console.log('coletando o valor do campo ' + $(this).attr('id'));
            setTimeout(function () {
                console.log('updating subjects...');

                //chrome.runtime.sendMessage({todo:"removeSubjectList"});

                updateSubjectList('black');
                /** re-add to listener */
                bindFieldAssuntos('black')
            }, 1500);
        });
    });
}

/** PAGE TAB -  DADOS INICIAIS */

var bindFieldDadosIniciais = function (id = "processoTrfForm") {
    console.log(id);
    console.log('dados iniciais : ' + $("select[id*='"+ id +"']").length);

    $("select[id*='"+ id +"']").each(function (i, s) {
        console.log('each in id');
        console.log($(this).attr('id'));

        $(this).bind('change', function () {

            var $this = $(this);
            console.log('coletando o valor do campo ' + $(this).attr('id'));
            setTimeout(function () {
                /** GET JURI VALUE */
                
                var thisValue = $($this).val();
                var thisDesc = $('option:selected', $this).text();
            
                var thisId = $($this).attr('id');

                console.log('id = ' + thisId);
                console.log('value = ' + thisValue);
                console.log('desc = ' + thisDesc);

                var juriId = id + ":classeJudicial:jurisdicaoComboDecoration:jurisdicaoCombo";
                console.log('juriId : ' + juriId)
                if (thisId == juriId) {
                    console.log('*****atualizando juri!*****');
                    console.log('juriValue : ' + thisValue);
                    chrome.storage.sync.set({ 'juriValue': thisValue });
                }
                
                var classId = id + ":classeJudicial:classeJudicialComboDecoration:classeJudicialCombo";
                console.log('classId : ' + classId)

                if (thisId == classId) {

                    var regExp = /\(([^)]*)\)[^(]*$/;
                    var matches = regExp.exec($.trim(thisDesc));
    
                    console.log('matches do valor da classe');
                    console.log(matches);
    
                    thisValue = matches[1];
    
                    console.log('VALOR DA CLASSE');
                    console.log(thisValue);


                    chrome.storage.sync.set({ 'classValue': { 'value': thisValue, 'desc': thisDesc } });
                    
                    chrome.storage.sync.get('classValue', function(data){
                        console.log('*****atualizando a classe!*****');
                        console.log('thisValue : ' + thisValue);
                        console.log('desc : ' + thisDesc);
                    });
                }

                bindFieldDadosIniciais(id);

            }, 1500);
        });
    });
}

/** UPDATE ASSUNTOS */
var updateSubjectList = function (color = false) {
    console.log('updating list init');

    var subjectRows = $('table#l_processoAssuntoListList tr.rich-table-row');
    console.log(subjectRows);

    if (subjectRows.length) {

        console.log('updating subject list');

        subjectRows.each(function (i, r) {

            var code = $.trim($('td:nth-child(2) span div', r).html());
            var desc = $.trim($('td:nth-child(4) span div', r).html());
            var radio = $.trim($('td:nth-child(3) span div form a > img', r).attr('src'));
            var m = false;

            if (undefined != code && "" != code) {
                var incidence = 0;
                if (radio == "/pje/img/jbpm/radio-button_16x16.png") {

                    m = true;

                    incidence = Math.random() * 100;
                    var cArg = { 'color': color };
                    c = color ? cArg : getColors(incidence);

                    console.log('subject code');
                    console.log(code);

                    console.log('colors');
                    console.log(c);

                    $('td:nth-child(2) span div', r).css('color', c['color']);
                    $('td:nth-child(4) span div', r).css('color', c['color']);
                    console.log('**** Atualizando o VALOR DO ASSUNTO');
                    chrome.storage.sync.set({ 'subjectValue': code });


                    chrome.storage.sync.get('classValue', function(data){
                        //alert('get classValue');
                        classValue = data.classValue.value;
                        classDesc = data.classValue.desc;

                        console.log('get classValue');
                        console.log(classValue);
                        
                        var subjectValue = null;
                        chrome.storage.sync.get('subjectValue', function(data){
                            
                            subjectValue = data.subjectValue;
                            console.log('get subjectValue');
                            console.log(subjectValue);
                        });
                
                    });



                }

                subjectlist.push({ id: code, description: desc, checked: m, incidence: incidence });

            }

        });

        chrome.storage.sync.set({ 'sSubject': subjectlist });
    }

}

/** UPDATE PROTOCOLAR INICIAL */

var updateInitialProtocol = function (color = false) {

    var selectedClassCell = $('div#divInformativoProcesso').find('div#processoViewSdiv .rich-panel-body table > tbody tr')
        .eq(1).find('td:nth-child(2)');
    var selectedSubjectCell = $('div#divInformativoProcesso').find('div#pagina .rich-panel-body table > tbody tr')
        .eq(0).find('td:nth-child(1)');

    //decorar a td
    selectedClassCell.css('color', color);

    selectedSubjectCell.contents().filter(function () {
        return this.nodeType === 3;
    }).wrap("<p></p>").end().filter("br").remove();

    chrome.storage.sync.get('subjectValue', function (data) {
        subjectValue = data.subjectValue;
        selectedSubjectCell.children().each(function (i, e) {
            if ($(this).text().indexOf("(" + subjectValue + ")") != -1) {
                $(this).css('color', color);
            }
        });
    });
}


/** API REMOTE CLIENT */
var checkInServer = function (pClass, pSubject) {

    var url = "http://157.245.121.226/api/valida";

    $.get(url, { percentual: 0.0, cod_classe: pClass, cod_assunto: pSubject + '.0', limit: 1 }).done(function (response) {

        var incidence = 0;
        console.log('response geral')
        console.log(response.length);
        console.log(response);

        if (response.length) {

            console.log(' -------- BEGIN a análise de incidência -------- ');

            for (var k in response) {
                var data = response[k];
                var perc = data.percentual;
                incidence = perc.toFixed(2);
                console.log(k);
                console.log(data);
                console.log('data.percentual.toFixed(2)');
                console.log(data.percentual.toFixed(2));
            }
        }

        var c = getColors(incidence);

        $('[data-plugin="knob"]').each(function(idx, obj) {
            
            $(this).attr('data-fgColor', c['color'])
                .attr('data-bgColor', c['bg'])
                .attr('value', incidence);
        
            $(this).knob({
                'format' : function (value) {
                    return value.toFixed(2) + '%';
                }
            });

        });

        chrome.tabs.query({ active: true, currentWindow: true }, function (tabs) {
            chrome.tabs.sendMessage(tabs[0].id, { todo: "serverResponse", 'color': c });
        });

        console.log(' -------- END a análise de incidência -------- ');

        /**
         *  GET SUGESTIONS IN SERVER
         */
    
        var percBase = incidence;
        console.log('p > remote ');
        console.log(percBase);

        var sugestionsArray = []; 
        
       //('class : ' + pClass);

        $.get( url, {percentual: percBase, cod_classe: pClass, limit:100}).done(function(sugestionsServer) {
            console.log('--------- suggestions ---------');
            
            console.log('sugestionsServer')
            console.log(sugestionsServer.length);
            console.log(sugestionsServer);
            
            for(var i in sugestionsServer) {
                var sugestion = sugestionsServer[i];
                
                var percS = sugestion.percentual;

                var incidenceSugestion = percS.toFixed(2);
                
                console.log(i);
                console.log(sugestion);
                console.log('sugestion.percentual.toFixed(2)');
                console.log(incidenceSugestion);
                
                var typeAlert = 'success';
                if(incidenceSugestion <= 30.00) {
                    var typeAlert = 'danger';
                } else if(incidenceSugestion > 30.00 && incidenceSugestion < 70.00) {
                    var typeAlert = 'warning';
                }

                var subDesc = sugestion.descricao_assunto + ' (' + parseInt(sugestion.cod_assunto) + ')';

                sugestionsArray.push({'subject': subDesc, 'incidence': incidenceSugestion, 'type': typeAlert});
            }

            console.log('sugestionsArray.length');
            console.log(sugestionsArray.length);

            if(sugestionsArray.length){
                $('.ci-sugestoes').html(sugestionsArray.map(progressItem).join(''));
            } else {
                $('.ci-sugestoes').html(progressNotfound);
            }



        }).fail(function(error) {
            console.log('error sugestion analysis:' + error);
        });
        

    }).fail(function (error) {
        console.log('error main analysis:' + error);
    });
}

const progressItem = ({ subject, incidence, type }) => `
    <div class="entry">
        <p class="font-10 m-b-5"><span class="s-desc">${subject}</span>
            <span class="text-${type} pull-right">${incidence}%</span>
        </p>
        <div class="progress progress-bar-${type}-alt progress-sm m-b-20">
            <div class="progress-bar progress-bar-${type} progress-animated wow animated animated" role="progressbar"
                aria-valuenow="${incidence}" aria-valuemin="0" aria-valuemax="100"
                style="width: ${incidence}%; visibility: visible; animation-name: animationProgress;">
            </div><!-- /.progress-bar .progress-bar-${type} -->
        </div><!-- /.progress .no-rounded -->
    </div>`;

const progressNotfound = () => `
    <span class="ci-notfound">
        Não há!
    </div>`;