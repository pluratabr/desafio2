var pClass = null;
var pSubject  = null;
var pSugestions  = null;

//alert('app.js');

$(function () {

    chrome.runtime.sendMessage({todo:"serverResponse"});

    chrome.tabs.query({active: true, currentWindow: true}, function(tabs) {
        chrome.tabs.executeScript(
            tabs[0].id, 
            {code: 'console.log("teste");'}
        );
    });

    chrome.storage.sync.get('classValue', function(data){
        //alert('get classValue');
        classValue = data.classValue.value;
        classDesc = data.classValue.desc;
        
        console.log(classValue);
        console.log(classDesc);

        var subjectValue = null;
        chrome.storage.sync.get('subjectValue', function(data){
            console.log('get subjectValue');
            subjectValue = data.subjectValue;
            console.log(subjectValue);

            $('span.ci-subject').text(subjectValue);
            $('span.ci-class').text(classDesc);


            /** CHECK IN SERVER */
            checkInServer(classValue, subjectValue);

        });

    });
})