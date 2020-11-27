var checkSubject = function (clickData) {

    var selectionText = clickData.selectionText

    if (clickData.menuItemId == "subjectSelectionText" && selectionText) {

        chrome.storage.sync.get('classValue', function(data){
            //alert('context menu');
            classValue = data.classValue.value;
            classDesc = data.classValue.desc;
            //alert('classDesc + ' + classDesc);
            
            //alert('subjectValue + ' + selectionText);

            var url = "http://157.245.121.226/api/valida";
            $.get(url, {cod_classe: classValue, cod_assunto: selectionText + '.0', limit: 1 }).done(function (response) {
                
                //alert("call server to find subject");
                //alert(response.length);
                
                var nyOption = {
                    type: "basic",
                    iconUrl: "images/icon48.png",
                    title: "Caçador de inconsistências",
                    message: "Não encontramos nenhuma incidência do assunto " + selectionText + " na classe " + classValue
                }

                if(response.length) {
                    
                    var respServer = response[0];
                    var inc = respServer.percentual.toFixed(2);
                
                    nyOption = {
                        type: "basic",
                        iconUrl: "images/icon48.png",
                        title: inc + "% de incidência",
                        message: "Assunto " + selectionText + " para a classe " + respServer.cod_classe +" tem " + inc + "% de incidência no " + respServer.tribunal.toUpperCase(),                        
                    }

                }

                //alert('notify now');
                var d = new Date();
                var notificationId = 'ci-notification-' + d.getMilliseconds();
                chrome.notifications.create(notificationId, nyOption);
                

            }).fail(function (error) {
                console.log('error notify:' + error);
            });


        });

        

    }

};

chrome.contextMenus.create({
    "id": "subjectSelectionText",
    "title": "Verificar incidência do assunto",
    "contexts": ["selection"],
});

chrome.contextMenus.onClicked.addListener(checkSubject);

function isSubject(value) {
    return isNaN(value);
    // && 
    // parseInt(Number(value)) == value &&
    // !isNaN (parseInt(value, 10));
}

chrome.runtime.onMessage.addListener(function (request, sender, sendResponse) {
    if (request.todo == "showPageAction") {
        chrome.tabs.query({ active: true, currentWindow: true }, function (tabs) {
            chrome.pageAction.show(tabs[0].id);
        });

        
    }
    console.log('backgroud');
});


