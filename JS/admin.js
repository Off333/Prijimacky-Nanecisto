$("#eventForm").click(function(){
    var d = new Date();
    var currentDate = d.getFullYear() + '-' + ('0' + (d.getMonth()+1)).slice(-2) + '-' + ('0' + d.getDate()).slice(-2);
    var StartRegDate = $("#inputStartRegDate").val();
    if(currentDate >= StartRegDate) {
        return confirm('Začátek registrace je stanoven na termín, který již proběhl.\\nPokud změníte akci, již ji nelze dále upravovat!\\nOpravdu chcete změnit akci?');
    } else {
        return true;
    }
});

function changeMin(e, elName) {
    var d = new Date((Date.parse(e.value)/1000 + (1 * 24 * 60 * 60))*1000);
    document.getElementsByName(elName)[0].min = d.getFullYear() + '-' + ('0' + (d.getMonth()+1)).slice(-2) + '-' + ('0' + d.getDate()).slice(-2);
}

function changeMax(e, elName) {
    var d = new Date((Date.parse(e.value)/1000 - (1 * 24 * 60 * 60))*1000);
    document.getElementsByName(elName)[0].max = d.getFullYear() + '-' + ('0' + (d.getMonth()+1)).slice(-2) + '-' + ('0' + d.getDate()).slice(-2);
}

function calculateReminder() {
    var EventDate = new Date(document.getElementsByName('EventDate')[0].value);
    var EndRegDate = new Date(document.getElementsByName('EndRegDate')[0].value);
    document.getElementsByName('ReminderEmail')[0].max = ((EventDate - EndRegDate) / (24 * 60 * 60 * 1000)) - 1;
}