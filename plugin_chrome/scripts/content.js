chrome.runtime.sendMessage({todo:"showPageAction"});

chrome.runtime.onMessage.addListener(function (request, sender, sendResponse) {
    if (request.todo == "serverResponse") {
        
        console.log('serverResponse');
        console.log(request.color);
        
        updateSubjectList(request.color.color);
        updateHeaderProcess(request.color.color);
        updateInitialProtocol(request.color.color);

    }
});

//alert('content.js');

var subjectValue = null;
var subjectlist = [];
var processClass = [];

var dIselectedClass = $('#formProcessoTrf:classeJudicial:classeJudicialComboDecoration:classeJudicialCombo');

/** UPDATING */
updateSubjectList('black');
updateHeaderProcess('black');
updateInitialProtocol('black');

console.log('lists:');
console.log(subjectlist);
console.log(processClass);

/** PAGE TAB -  DADOS INICIAIS */
$("select[id*='processoTrfForm']").each(function(i, s){
    
    console.log($(this).attr('id'));

    $(this).bind('change', function(){
        
        setTimeout(function(){
            bindFieldDadosIniciais();
        }, 1500);

    });

});

// $('#assunto_lbl').on('click', function(e){ 
    
//     console.log('clicou no assuntos');
//     console.log($(this).attr('id'));
//     bindFieldAssuntos();

// });


/** When button was clicked */
$('#form_lbl').on('click', function(e){ 

    console.log('clicou no dados iniciais');
    
    setTimeout(function(){
        
        $("input[id='formProcessoTrf:salvaProcessoButton']").on('click', function(e){ 
            
        });

        bindFieldDadosIniciais('formProcessoTrf');

    }, 2500);
    
});


/** PAGE TAB -  PROTOCOLAR INICIAL */
$('#informativo_lbl').on('click', function(e){ 
    console.log('clicou no protocolar inicial');
    setTimeout(function(){
        updateInitialProtocol('black');
    }, 1500);
});

/** PAGE TAB -  ASSUNTOS */
bindFieldAssuntos();